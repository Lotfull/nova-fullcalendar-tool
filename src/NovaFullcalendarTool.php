<?php

namespace Lotfull\NovaFullcalendarTool;

use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class NovaFullcalendarTool extends Tool
{
    /**
     * Perform any tasks that need to happen when the tool is booted.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('calendar', __DIR__.'/../dist/js/tool.js');
        Nova::style('calendar', __DIR__.'/../dist/css/tool.css');
    }
}
