<?php

namespace EscolaLms\Recommender;

use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Recommender\Providers\AuthServiceProvider;
use EscolaLms\Recommender\Providers\EventServiceProvider;
use EscolaLms\Recommender\Providers\SettingsServiceProvider;
use EscolaLms\Recommender\Repositories\Contracts\TopicRepositoryContract;
use EscolaLms\Recommender\Repositories\TopicRepository;
use EscolaLms\Recommender\Services\Contracts\RecommenderServiceContract;
use EscolaLms\Recommender\Services\RecommenderService;
use EscolaLms\Settings\EscolaLmsSettingsServiceProvider;

use Illuminate\Support\ServiceProvider;

/**
 * SWAGGER_VERSION
 */
class EscolaLmsRecommenderServiceProvider extends ServiceProvider
{
    const CONFIG_KEY = 'escolalms_recommender';

    public const REPOSITORIES = [
        TopicRepositoryContract::class => TopicRepository::class
    ];

    public const SERVICES = [
        RecommenderServiceContract::class => RecommenderService::class
    ];

    public $singletons = self::SERVICES + self::REPOSITORIES;

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', self::CONFIG_KEY);

        $this->app->register(SettingsServiceProvider::class);
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(EscolaLmsSettingsServiceProvider::class);
        $this->app->register(EscolaLmsAuthServiceProvider::class);
    }

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function bootForConsole()
    {
        $this->publishes([
            __DIR__ . '/config.php' => config_path(self::CONFIG_KEY . '.php'),
        ], self::CONFIG_KEY . '.config');
    }
}
