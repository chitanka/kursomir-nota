<?php

namespace Api\Transformers;

use League\Fractal\TransformerAbstract;
use Translation;

class TranslationTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'translator',
    ];

    public function transform(Translation $translation)
    {
        return [
            'translation_id' => $translation->id,
            'material_id' => $translation->chap_id,
            'slice_id' => $translation->orig_id,
            'translator' => $translation->user,
            'body' => $translation->body,
            'rating' => $translation->rating,
            'created_at' => $translation->cdate,
            'updated_at' => $translation->cdate,
        ];
    }

    public function includeTranslator(Translation $translation)
    {
        return $this->item($translation->user, new TranslatorTransformer());
    }
}
