<?php

namespace EscolaLms\Recommender\Tests\Api;

use EscolaLms\Consultations\Database\Seeders\ConsultationsPermissionSeeder;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Recommender\Enum\MeetRecordingEnum;
use EscolaLms\Recommender\Models\MeetRecording;
use EscolaLms\Recommender\Tests\CreatesCourse;
use EscolaLms\Recommender\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class MeetRecordingTest extends TestCase
{
    use CreatesCourse, CreatesUsers, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ConsultationsPermissionSeeder::class);
    }

    public function testCreateMeetRecording(): void
    {
        $term = Carbon::now()->subMinutes(30);
        $this->actingAs($this->makeAdmin(), 'api')->postJson('api/recommender/meet-recordings', [
            'model_type' => 'consultation',
            'model_id' => 1,
            'term' => $term,
            'start_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'action' => MeetRecordingEnum::START_RECORDING,
        ])->assertCreated();

        $this->assertDatabaseHas('meet_recordings', [
            'model_type' => 'consultation',
            'model_id' => 1,
            'term' => $term,
            'start_at' => Carbon::now(),
            'end_at' => null,
            'url' => null,
            'url_expiration_time_millis' => null,
        ]);
    }

    public function testUpdateMeetRecording(): void
    {
        $term = Carbon::now()->subMinutes(30);
        $start = Carbon::now()->subMinutes(15);
        $meetRecording = MeetRecording::create([
            'model_type' => 'consultation',
            'model_id' => 1,
            'term' => $term,
            'start_at' => $start,
            'end_at' => null,
            'url' => null,
            'url_expiration_time_millis' => null,
        ]);

        $now = Carbon::now()->format('Y-m-d H:i:s');
        $this->actingAs($this->makeAdmin(), 'api')->postJson('api/recommender/meet-recordings', [
            'model_type' => 'consultation',
            'model_id' => 1,
            'term' => $term,
            'end_at' => $now,
            'action' => MeetRecordingEnum::END_RECORDING,
            'url' => 'http://test-recording.com',
            'url_expiration_time_millis' => 123456,
            'id' => $meetRecording->getKey(),
        ])->assertOk();

        $this->assertDatabaseHas('meet_recordings', [
            'id' => $meetRecording->getKey(),
            'model_type' => 'consultation',
            'model_id' => 1,
            'term' => $term,
            'start_at' => $start,
            'end_at' => $now,
            'url' => 'http://test-recording.com',
            'url_expiration_time_millis' => 123456,
        ]);
    }

    public function testMeetRecordingScreen(): void
    {
        $time = Carbon::now();
        $screenTime = Carbon::now()->addMinutes(10);
        $screenTime2 = Carbon::now()->addMinutes(15);
        Storage::fake();
        $this->postJson('api/recommender/meet-recordings/screens', [
            'model_type' => 'consultation',
            'model_id' => 1,
            'term' => $time->getTimestamp(),
            'files' => [
                [
                    'file' => UploadedFile::fake()->image('image.jpg'),
                    'timestamp' => $screenTime->format('Y-m-d H:i:s'),
                ],
                [
                    'file' => UploadedFile::fake()->image('image.jpg'),
                    'timestamp' => $screenTime2->format('Y-m-d H:i:s'),
                ]
            ]
        ])
            ->assertOk();

        $this->assertDatabaseHas('meet_recordings_screens', [
            'model_type' => 'consultation',
            'model_id' => 1,
            'term' => $time,
            'file_timestamp' => $screenTime->format('Y-m-d H:i:s'),
        ]);
    }
}
