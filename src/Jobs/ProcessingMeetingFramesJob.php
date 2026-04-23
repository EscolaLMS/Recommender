<?php

namespace EscolaLms\Recommender\Jobs;

use EscolaLms\Recommender\Models\MeetRecording;
use EscolaLms\Recommender\Models\MeetRecordingScreen;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessingMeetingFramesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;

    public function __construct(
        protected MeetRecording $meetRecording
    ) {
        $this->onQueue('video');
    }

    public function handle()
    {
        if (!$this->meetRecording->url) return;

        try {
            $html = Http::get($this->meetRecording->url)->body();
            preg_match('/DOWNLOAD_RECORDING_URLS = "\[(.*?)\]";/', $html, $matches);
            if (empty($matches[1])) return;
            $directVideoUrl = str_contains($matches[1], ',') ? explode(',', $matches[1])[0] : $matches[1];
        } catch (\Exception $e) {
            return;
        }

        $duration = (float) shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($directVideoUrl));
        if ($duration <= 0) return;

        $tempDir = storage_path('app/temp/f_' . $this->meetRecording->getKey() . '_' . Str::random(5));
        if (!file_exists($tempDir)) mkdir($tempDir, 0777, true);

        $outputPattern = $tempDir . '/f_%03d.jpg';
        $mainCommand = "ffmpeg -reconnect 1 -reconnect_at_eof 1 -reconnect_streamed 1 -i " .
            escapeshellarg($directVideoUrl) .
            " -vf \"select='not(mod(t,15))',setpts=N/FRAME_RATE/TB\" -vsync vfr -an -sn -t " . ($duration + 0.5) .
            " " . escapeshellarg($outputPattern) . " > /dev/null 2>&1";
        exec($mainCommand);

        $lastFrameFile = $tempDir . '/f_last.jpg';
        $lastCommand = "ffmpeg -reconnect 1 -reconnect_at_eof 1 -reconnect_streamed 1 -sseof -1 -i " .
            escapeshellarg($directVideoUrl) . " -update 1 -frames:v 1 -an -sn " .
            escapeshellarg($lastFrameFile) . " > /dev/null 2>&1";
        exec($lastCommand);

        $files = glob($tempDir . '/*.jpg');
        sort($files);

        $folder = "{$this->meetRecording->model_type}/{$this->meetRecording->model_id}/" .
            Carbon::make($this->meetRecording->term)->getTimestamp() . "/presentation";

        foreach ($files as $index => $file) {
            $name = basename($file);

            if ($name === 'f_last.jpg') {
                $offset = (int)$duration;
            } else {
                preg_match('/f_(\d+)\.jpg/', $name, $m);
                $idx = isset($m[1]) ? (int)$m[1] - 1 : 0;
                $offset = $idx * 15;
            }

            if ($name !== 'f_last.jpg' && $offset >= (int)$duration) {
                unlink($file);
                continue;
            }

            $currentFrameTimestamp = Carbon::parse($this->meetRecording->start_at)->addSeconds($offset);
            $fullS3Path = "{$folder}/" . $currentFrameTimestamp->getTimestamp() . ".jpg";

            try {
                DB::transaction(function () use ($file, $fullS3Path, $currentFrameTimestamp) {
                    Storage::put($fullS3Path, fopen($file, 'r+'));

                    MeetRecordingScreen::query()->updateOrCreate(
                        ['file_path' => $fullS3Path],
                        [
                            'model_type' => $this->meetRecording->model_type,
                            'model_id' => $this->meetRecording->model_id,
                            'term' => $this->meetRecording->term,
                            'file_timestamp' => $currentFrameTimestamp->utc(),
                            'meet_recording_id' => $this->meetRecording->getKey(),
                        ]
                    );
                });
            } catch (\Exception $e) {
                Log::error("S3 Error: " . $e->getMessage());
            }

            if (file_exists($file)) unlink($file);
        }

        if (is_dir($tempDir)) rmdir($tempDir);
    }
}
