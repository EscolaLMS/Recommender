<?php

namespace EscolaLms\Recommender\Dto;

use EscolaLms\Core\Dtos\Contracts\DtoContract;
use EscolaLms\Core\Dtos\Contracts\InstantiateFromRequest;
use Illuminate\Http\Request;

class SatisfactionDto implements DtoContract, InstantiateFromRequest
{
    protected int $term_analytic_id;
    protected string $mean_predicted_rating;

    /**
     * @param int $term_analytic_id
     * @param string $mean_predicted_rating
     */
    public function __construct(int $term_analytic_id, string $mean_predicted_rating)
    {
        $this->term_analytic_id = $term_analytic_id;
        $this->mean_predicted_rating = $mean_predicted_rating;
    }

    public function toArray(): array
    {
        return [
            'term_analytic_id' => $this->term_analytic_id,
            'mean_predicted_rating' => $this->mean_predicted_rating,
        ];
    }

    public static function instantiateFromRequest(Request $request): self
    {
        return new self(
            $request->get('term_analytic_id'),
            $request->get('mean_predicted_rating'),
        );
    }

    public function getTermAnalyticId(): int
    {
        return $this->term_analytic_id;
    }

    public function getMeanPredictedRating(): string
    {
        return $this->mean_predicted_rating;
    }
}
