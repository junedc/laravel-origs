<?php

namespace App\Nova\Actions\Sport;

use App\Push\Pushers\Sport\SportEventPusher;
use App\Repositories\SubscribersRepository;
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
 * Class EventPushAction
 *
 * @package App\Nova\Actions\Racing
 */
class EventPushAction extends Action
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
     * @var SubscribersRepository $subscribersRepository
     */
    protected $subscribersRepository;

    /**
     * @var Action $action
     */
    protected $action;

    /**
     * @var SubscriptionsRepository $subscriptionsRepository
     */
    protected $subscriptionsRepository;

    /**
     * EventPushAction constructor.
     *
     * @param SubscribersRepository $subscribersRepository
     * @param SubscriptionsRepository $subscriptionsRepository
     * @param Action $action
     */
    public function __construct(
        SubscribersRepository $subscribersRepository,
        SubscriptionsRepository $subscriptionsRepository,
        Action $action
    ) {
        $this->subscribersRepository = $subscribersRepository;
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
        /** @var SportEventPusher $pusher */
        $pusher = app(SportEventPusher::class);
        foreach ($models as $event) {
            $pusher->setSportEventId($event->id)
                ->dispatch();
        }

        return $this->action::message('Events pushed');
    }
}
