<?php

namespace App\Nova\Actions\Sport;

use App\Push\Pushers\Sport\MarketsPusher;
use App\Utilities\Arr;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

/**
 * Class MarketPushAction
 *
 * @package App\Nova\Actions\Racing
 */
class MarketPushAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * @var string $name
     */
    public $name = 'Push markets';

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
     * @inheritDoc
     */
    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $market = $models[0];
        /** @var MarketsPusher $pusher */
        $pusher = app(MarketsPusher::class);
        $pusher->setSportEventId($market->event->id)
            ->setMarketIds(Arr::pluck($models, 'id'))
            ->dispatch();

        return Action::message('Markets pushed');
    }
}
