<?php

namespace App\Nova\Actions\Sport;

use App\DataFeed\ImportManager;
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
class SportCastEventImportAction extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * @var string $name
     */
    public $name = 'Import SportCast markets';

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
    protected Action $action;


    public function __construct(

        Action $action
    )
    {

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
                $importer->importSportCastFixture($event);
            } catch (\Exception $e) {
                return $this->action::danger('The source link is no longer available. ' . $e->getMessage());
            }
        }

        return $this->action::message('Imported successfully');
    }
}
