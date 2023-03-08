<?php

namespace App\Nova\Actions\Racing;

use App\Models\Sport\Sport;
use App\Push\Pushers\Racing\RacePusher;
use App\Push\Pushers\Racing\RunnerPusher;
use App\Push\Pushers\Racing\SelectionPusher;
use App\Repositories\RacesRepository;
use App\Repositories\RunnersRepository;
use App\Repositories\SubscriptionsRepository;
use App\Utilities\Arr;
use App\Utilities\LocalCache;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BooleanGroup;

/**
 * Class RacePushAction
 *
 * @package App\Nova\Actions\Racing
 */
class RacePushAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * @var string $name
     */
    public $name = 'Push to Subscribers';

    /**
     * @var string $confirmButtonText
     */
    public $confirmButtonText = 'Send';

    /**
     * @var string $cancelButtonText
     */
    public $cancelButtonText = 'Cancel';

    /**
     * @var bool $onlyOnIndex
     */
    public $onlyOnIndex = true;

    /**
     * @var bool $onlyOnDetail
     */
    public $onlyOnDetail = true;

    /**
     * @var SubscriptionsRepository $subscriptionsRepository
     */
    protected $subscriptionsRepository;

    /**
     * @var RacesRepository $racesRepository
     */
    protected $racesRepository;

    /**
     * @var RunnersRepository $runnersRepository
     */
    protected $runnersRepository;

    /**
     * @var Action $action
     */
    protected $action;

    /**
     * @var int $raceId
     */
    private $raceId;

    /**
     * @var int $previousRaceId
     */
    private $previousRaceId;

    /**
     * List of selections
     *
     * @var array $selections
     */
    private $selections;

    /**
     * MeetingPush constructor.
     *
     * @param SubscriptionsRepository $subscriptionsRepository
     * @param RacesRepository $racesRepository
     * @param RunnersRepository $runnersRepository
     * @param Action $action
     */
    public function __construct(
        SubscriptionsRepository $subscriptionsRepository,
        RacesRepository $racesRepository,
        RunnersRepository $runnersRepository,
        Action $action
    ) {
        $this->subscriptionsRepository = $subscriptionsRepository;
        $this->racesRepository = $racesRepository;
        $this->runnersRepository = $runnersRepository;

        $this->action = $action;
        $this->selections = [];
    }

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $subscriptionIds = array_keys(
            array_filter($fields->get('subscribers'), function ($value) {
                return $value == 1;
            })
        );

        if (empty($subscriptionIds)) {
            return $this->action::danger('Please select at lease one subscriber.');
        }

        /** @var RacePusher $pusher */
        $pusher = app(RacePusher::class);

        foreach ($models as $race) {
            $this->raceId = $race->id;
            $pusher->push($race->id, $subscriptionIds);

            if ($fields->get('with_runners')) {
                $this->pushRunners($subscriptionIds);
            }

            if ($fields->get('with_selections')) {
                $this->pushSelections($subscriptionIds);
            }
        }

        return $this->action::message('Race data pushed to subscribers');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        $options = [];
        $subscriptions = LocalCache::get('RacingPusherAction:subscriptions', Sport::HORSE_RACING, function () {
            return $this->subscriptionsRepository
                ->getSubscriptionsBySportId(Sport::HORSE_RACING, false);
        });

        foreach ($subscriptions as $subscription) {
            $options[$subscription->id] = $subscription->subscriber;
        }

        return [
            BooleanGroup::make('Subscribers', 'subscribers')
                ->options($options)
                ->rules('required'),

            Boolean::make('With Runners?', 'with_runners'),

            Boolean::make('With Selections?', 'with_selections'),
        ];
    }

    /**
     * Push selection data to subscribers
     *
     * @param array $subscriptionIds
     * @return void
     */
    private function pushRunners(array $subscriptionIds)
    {
        $this->getRaceSelections();
        $runnersIds = collect($this->selections)
            ->pluck('runner_id')
            ->toArray();

        /** @var RunnerPusher $pusher */
        $pusher = app(RunnerPusher::class);
        $pusher->push($runnersIds, $subscriptionIds);
    }

    /**
     * Push selection data to subscribers
     *
     * @param array $subscriptionIds
     * @return void
     */
    private function pushSelections(array $subscriptionIds)
    {
        $this->getRaceSelections();

        /** @var SelectionPusher $pusher */
        $pusher = app(SelectionPusher::class);
        $selectionIds = Arr::pluck($this->selections, 'id');
        $pusher->push($selectionIds, $subscriptionIds);
    }

    /**
     * Get selections of a race
     */
    private function getRaceSelections()
    {
        if (empty($this->selections) || $this->raceId != $this->previousRaceId) {
            $this->selections = $this->racesRepository
                ->getSelectionsByRaceId($this->raceId);
            $this->previousRaceId = $this->raceId;
        }
    }
}
