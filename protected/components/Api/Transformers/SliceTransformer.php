<?php

namespace Api\Transformers;

use League\Fractal\TransformerAbstract;
use Orig;

class SliceTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'translations',
        'comments',
    ];

    public function transform(Orig $slice)
    {
        return [
            'slice_id' => (int) $slice->id,
            'material_id' => (int) $slice->chap_id,
            'type' => 'text',
            'translatable' => 'true',
            'body' => $slice->body,
            'translations' => $slice->trs,
            'comments' => $slice->comments,
        ];
    }

    public function includeTranslations(Orig $slice)
    {
        return $this->collection($slice->trs, new TranslationTransformer());
    }

    public function includeComments(Orig $slice)
    {
        return $this->collection($slice->comments, new CommentTransformer());
    }
}
