<?php

namespace App\Nova\Cards;

use App\Repositories\SportsRepository;
use Laravel\Nova\Card;

/**
 * Class SportEventImport
 *
 * @package App\Nova\Cards
 */
class SportEventImport extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/3';

    /**
     * @var SportsRepository $sportsRepository
     */
    protected $sportsRepository;

    /**
     * @var bool $onlyOnDetail
     */
    public $onlyOnDetail = true;

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'sport-event-import';
    }

    /**
     * SportEventImport constructor.
     *
     * @param SportsRepository $sportsRepository
     * @param $component
     */
    public function __construct(SportsRepository $sportsRepository, $component = null)
    {
        $this->sportsRepository = $sportsRepository;

        parent::__construct($component);
    }

    /**
     * Set component sport value
     *
     * @param int $sportId
     * @return SportEventImport
     */
    public function setSport(int $sportId)
    {
        if (!$sportId) {
            return $this;
        }

        $sport = $this->sportsRepository->getById($sportId);
        if (!$sport || !$sport->isSSLNConnected()) {
            return $this;
        }

        return $this->withMeta([
            'active' => $sport->active,
            'feedName' => $sport->externalId
        ]);
    }
}
