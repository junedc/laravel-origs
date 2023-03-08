<?php

namespace App\Nova\Pages;

use Illuminate\View\View;
use Laravel\Nova\Tool;

/**
 * Class Scraping
 *
 * @package App\Nova\Pages
 */
class Scraping extends Tool
{
    /**
     * Build the view that renders the navigation links for the tool.
     *
     * @return View
     */
    public function renderNavigation()
    {
        return view('nova::sidebar.scraping', [
            'group' => 'Scraping',
            'providers' => config('scraping.providers')
        ]);
    }
}
