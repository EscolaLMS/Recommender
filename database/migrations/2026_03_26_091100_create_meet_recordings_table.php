<?php

use EscolaLms\Recommender\Enum\MeetRecordingEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('meet_recordings', function (Blueprint $table) {
            $table->id();

            $table->string('model_type');
            $table->unsignedInteger('model_id');
            $table->dateTime('term');

            $table->dateTime('time');
            $table->enum('type', MeetRecordingEnum::getValues());
            $table->string('recording_url')->nullable();
            $table->unsignedBigInteger('url_expiration_time_millis')->nullable();

            $table->timestamps();

            $table->unique(['model_type', 'model_id', 'term']);
            $table->index(['model_type', 'model_id']);
            $table->index(['model_type', 'term']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('meet_recordings');
    }
};
