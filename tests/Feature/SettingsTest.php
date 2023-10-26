<?php

namespace EscolaLms\Recommender\Tests\Feature;

use EscolaLms\Auth\Database\Seeders\AuthPermissionSeeder;
use EscolaLms\Recommender\EscolaLmsRecommenderServiceProvider;
use EscolaLms\Recommender\Tests\TestCase;
use EscolaLms\Settings\Database\Seeders\PermissionTableSeeder;
use EscolaLms\Settings\EscolaLmsSettingsServiceProvider;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Config;

class SettingsTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        if (!class_exists(EscolaLmsSettingsServiceProvider::class)) {
            $this->markTestSkipped('Settings package not installed');
        }

        $this->seed(PermissionTableSeeder::class);
        $this->seed(AuthPermissionSeeder::class);
        Config::set('escola_settings.use_database', true);
    }

    public function testAdministrableConfigApi(): void
    {
        $user = config('auth.providers.users.model')::factory()->create();
        $user->guard_name = 'api';
        $user->assignRole('admin');

        $configKey = EscolaLmsRecommenderServiceProvider::CONFIG_KEY;

        $apiUrl = $this->faker->url;

        $this->actingAs($user, 'api')
            ->postJson('/api/admin/config',
                [
                    'config' => [
                        [
                            'key' => $configKey . '.api_url',
                            'value' => $apiUrl,
                        ],
                    ]
                ]
            )
            ->assertOk();

        $this->actingAs($user, 'api')->getJson('/api/admin/config')
            ->assertOk()
            ->assertJsonFragment([
                $configKey => [
                    'api_url' => [
                        'full_key' => $configKey . '.api_url',
                        'key' => 'api_url',
                        'public' => false,
                        'rules' => [
                            'string'
                        ],
                        'value' => $apiUrl,
                        'readonly' => false,
                    ],
                ],
            ]);

        $this->getJson('/api/config')
            ->assertOk()
            ->assertJsonMissing([
                'api_url' => $apiUrl
            ]);
    }
}

