<?php

namespace App\Nova\Actions\Racing;

use App\Models\Sport\Sport;
use App\Push\Pushers\Racing\MeetingPusher;
use App\Repositories\SubscriptionsRepository;
use App\Utilities\Arr;
use App\Utilities\LocalCache;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BooleanGroup;

/**
 * Class MeetingPush
 *
 * @package App\Nova\Actions\Racing
 */
class MeetingPushAction extends Action
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
     * @var Action $action
     */
    protected $action;

    /**
     * @var int $meetingId
     */
    private $meetingId;

    /**
     * MeetingPush constructor.
     *
     * @param SubscriptionsRepository $subscriptionsRepository
     * @param Action $action
     */
    public function __construct(
        SubscriptionsRepository $subscriptionsRepository,
        Action $action
    ) {
        $this->subscriptionsRepository = $subscriptionsRepository;
        $this->action = $action;
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

        /** @var MeetingPusher $pusher */
        $pusher = app(MeetingPusher::class);

        foreach ($models as $meeting) {
            $pusher->push($meeting->id, $subscriptionIds);
        }

        return $this->action::message('Meeting data pushed to subscribers');
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
            BooleanGroup::make('Subscribers')
                ->options($options)
                ->rules('required')
        ];
    }
}
