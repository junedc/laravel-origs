<?php

namespace App\Nova\Actions\Sport;

use App\Models\Sport\SportEvent;
use App\Repositories\ScrapedEventsRepository;
use App\Repositories\SportEventsRepository;
use App\Utilities\LocalCache;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;

/**
 * Class EventLinkAction
 *
 * @package App\Nova\Actions\Sport
 */
class EventLinkAction extends Action
{
    use InteractsWithQueue;

    /**
     * @var string $name
     */
    public $name = 'Link Scraped Event';

    /**
     * @var string $confirmButtonText
     */
    public $confirmButtonText = 'Link Event';

    /**
     * @var string $cancelButtonText
     */
    public $cancelButtonText = 'Cancel';

    /**
     * @var bool $onlyOnDetail
     */
    public $onlyOnDetail = true;

    /**
     * @var SportEventsRepository $sportEventsRepository
     */
    protected $sportEventsRepository;

    /**
     * @var ScrapedEventsRepository $scrapedEventsRepository
     */
    protected $scrapedEventsRepository;

    /**
     * EventPushAction constructor.
     *
     * @param SportEventsRepository $sportEventsRepository
     * @param ScrapedEventsRepository $scrapedEventsRepository
     */
    public function __construct(
        SportEventsRepository $sportEventsRepository,
        ScrapedEventsRepository $scrapedEventsRepository
    ) {
        $this->sportEventsRepository = $sportEventsRepository;
        $this->scrapedEventsRepository = $scrapedEventsRepository;
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
        $scrapedEventToLink = explode('|', $fields->get('event'));

        /** @var SportEvent $event */
        foreach ($models as $event) {
            $scrapedEvent = $this->scrapedEventsRepository
                ->getByEventAndProviderId($event->id, $scrapedEventToLink[0]);

            if ($scrapedEvent && $scrapedEventToLink[1] != $scrapedEvent->id) {
                return Action::danger('This event already linked to other event of the same provider.');
            }

            $this->scrapedEventsRepository->updateScrapedEvent($scrapedEventToLink[1], ['event_id' => $event->id]);
        }

        return Action::message('Event linked successfully.');
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

        // Return if index page
        if (!$eventId) {
            return  $options;
        }

        $scrapedEvents = LocalCache::get('EventLinkAction:scrapedEvents', $eventId, function () use ($eventId) {
            $event = $this->sportEventsRepository->getById($eventId);

            if ($event) {
                $eventStartTime = Carbon::parse($event->startTime);
                $start = $eventStartTime->startOfDay()
                    ->toDateTimeString();
                $end = $eventStartTime->endOfDay()
                    ->toDateTimeString();

                $eventDate = [$start, $end];
                return $this->scrapedEventsRepository->getByDate($eventDate, ['provider']);
            }

            return [];
        });

        foreach ($scrapedEvents as $scrapedEvent) {
            $id = "$scrapedEvent->providerId|$scrapedEvent->id";
            $options[$id] = [
                'label' => $scrapedEvent->name,
                'group' => $scrapedEvent->provider->name
            ];
        }

        return [
            Select::make('Scraped Events', 'event')
                ->options($options)
        ];
    }
}
