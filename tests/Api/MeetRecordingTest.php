<?php

namespace EscolaLms\Recommender\Tests\Api;

use EscolaLms\Consultations\Database\Seeders\ConsultationsPermissionSeeder;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Recommender\Enum\MeetRecordingEnum;
use EscolaLms\Recommender\Tests\CreatesCourse;
use EscolaLms\Recommender\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;

class MeetRecordingTest extends TestCase
{
    use CreatesCourse, CreatesUsers, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConsultationsPermissionSeeder::class);
    }

    public function testCreateMeetRecordingStart(): void
    {
        $term = Carbon::now()->subMinutes(30);
        $this->actingAs($this->makeAdmin())->postJson('api/recommender/meet-recordings', [
            'model_type' => 'consultation',
            'model_id' => 1,
            'term' => $term,
            'time' => Carbon::now(),
            'type' => MeetRecordingEnum::START_RECORDING,
        ])->assertCreated();

        $this->assertDatabaseHas('meet_recordings', [
            'model_type' => 'consultation',
            'model_id' => 1,
            'term' => $term,
            'time' => Carbon::now(),
            'type' => MeetRecordingEnum::START_RECORDING,
        ]);
    }
}
