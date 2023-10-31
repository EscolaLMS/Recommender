<?php

namespace EscolaLms\Recommender\Providers;

use EscolaLms\Recommender\Listeners\UpdateSettingsInExternalService;
use EscolaLms\Settings\Events\SettingPackageConfigUpdated;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    protected $listen = [
        SettingPackageConfigUpdated::class => [
            UpdateSettingsInExternalService::class,
        ],
    ];
}

