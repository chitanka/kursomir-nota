<?php

namespace Api\Transformers;

use League\Fractal\TransformerAbstract;
use Translation;

class TranslationTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'rates',
        'translator',
    ];

    public function transform(Translation $translation)
    {
        return [
            'translation_id' => $translation->id,
            'translator' => $translation->user,
            'body' => $translation->body,
            'rating' => $translation->rating,
            'rates' => $translation->marks,
            'created_at' => strtotime($translation->cdate),
            'updated_at' => strtotime($translation->cdate),
        ];
    }

    public function includeRates(Translation $translation)
    {
        return $this->collection($translation->marks, new RateTransformer());
    }

    public function includeTranslator(Translation $translation)
    {
        return $this->item($translation->user, new TranslatorTransformer());
    }
}
