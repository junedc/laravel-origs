<?php

namespace App\Nova\Actions\Sport;

use App\Models\Sport\SportEvent;
use App\Repositories\MySQL\ScrapedEventsRepository;
use App\Repositories\SportEventsRepository;
use App\Utilities\LocalCache;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\BooleanGroup;
use Laravel\Nova\Fields\Select;

/**
 * Class EventUnlinkAction
 *
 * @package App\Nova\Actions\Sport
 */
class EventUnlinkAction extends Action
{
    use InteractsWithQueue;

    /**
     * @var string $name
     */
    public $name = 'Unlink Scraped Event';

    /**
     * @var string $confirmButtonText
     */
    public $confirmButtonText = 'Unlink Event';

    /**
     * @var string $cancelButtonText
     */
    public $cancelButtonText = 'Cancel';

    /**
     * @var bool $onlyOnDetail
     */
    public $onlyOnDetail = true;

    /**
     * Perform the action on the given models.
     *
     * @param ActionFields $fields
     * @param Collection $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $linkedEvents = array_keys(
            array_filter($fields->get('linkedEvents'), function ($value) {
                return $value == 1;
            })
        );

        if (!$linkedEvents) {
            Action::danger('No scraped event has been unlinked.');
        }

        /** @var ScrapedEventsRepository $scrapedEventsRepository */
        $scrapedEventsRepository = app(ScrapedEventsRepository::class);
        $scrapedEventsRepository->unlinkedScrapedEvents($linkedEvents);

        Action::message('Scraped Event has been unlinked.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        $options = [];
        $eventId = request()->input('resources') ?? request()->input('resourceId');

        if (!$eventId) {
            return  $options;
        }

        if ($eventId) {
            $linkedEvents = LocalCache::get('EventUnlinkAction:scrapedEvents', $eventId, function () use ($eventId) {
                /** @var ScrapedEventsRepository $scrapedEventsRepository */
                $scrapedEventsRepository = app(ScrapedEventsRepository::class);
                return $scrapedEventsRepository->getLinkedScrapedEvents($eventId);
            });

            foreach ($linkedEvents as $linkedEvent) {
                $options[$linkedEvent->id] = $linkedEvent->name;
            }
        }

        return [
            BooleanGroup::make('Linked Events', 'linkedEvents')
                ->options($options)
        ];
    }
}
