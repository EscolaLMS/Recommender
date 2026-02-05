<?php

namespace EscolaLms\Recommender\Events;

use EscolaLms\Recommender\Models\AggregatedFrame;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AggregatedFrameStored
{
    use Dispatchable, SerializesModels;

    public function __construct(
        private AggregatedFrame $aggregatedFrame
    ) {}

    public function getAggregatedFrame(): AggregatedFrame
    {
        return $this->aggregatedFrame;
    }
}
