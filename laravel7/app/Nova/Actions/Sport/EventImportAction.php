<?php

namespace App\Nova\Actions\Sport;

use App\DataFeed\ImportManager;
use App\Models\Sport\SportEvent;
use App\Repositories\SubscribersRepository;
use App\Repositories\SubscriptionsRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

/**
 * Class EventImportAction
 *
 * @package App\Nova\Actions\Sport
 */
class EventImportAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * @var string $name
     */
    public $name = 'Import sport event';

    /**
     * @var string $confirmButtonText
     */
    public $confirmButtonText = 'Import';

    /**
     * @var string $cancelButtonText
     */
    public $cancelButtonText = 'Cancel';

    /**
     * @var bool $onlyOnIndex
     */
    public $onlyOnIndex = false;

    /**
     * @var bool $onlyOnDetail
     */
    public $onlyOnDetail = true;

    /**
     * Indicates if need to skip log action events for models.
     *
     * @var bool
     */
    public $withoutActionEvents = true;

    /**
     * @var Action $action
     */
    protected $action;

    /**
     * EventImportAction constructor.
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
        /** @var ImportManager $importer */
        $importer = resolve(ImportManager::class);
        foreach ($models as $event) {
            try {
                $importer->importSportEvent($event);
            } catch (\Exception $e) {
                return $this->action::danger('The source link is no longer available. ' . $e->getMessage());
            }
        }

        return $this->action::message('Imported successfully');
    }
}
