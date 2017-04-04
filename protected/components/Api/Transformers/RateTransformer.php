<?php

namespace Api\Transformers;

use League\Fractal\TransformerAbstract;
use Mark as Rate;

class RateTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'rater',
    ];

    public function transform(Rate $rate)
    {
        return array_filter([
            'rater' => $rate->user,
            'value' => $rate->mark,
            'created_at' => $comment->cdate,
            'updated_at' => $comment->cdate,
        ]);
    }

    public function includeRater(Rate $rate)
    {
        return $this->item($rate->user, new UserTransformer());
    }
}
