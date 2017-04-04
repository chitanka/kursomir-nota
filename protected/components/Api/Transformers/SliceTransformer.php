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
            'type' => [
                    'slice_type_id' => '1',
                    'name' => 'text',
                    'display_name' => 'текст',
                ],
            'translatable' => 'true',
            'body' => $slice->body,
            'translations' => $slice->trs,
            'comments' => $slice->comments,
            'created_at' => time(),
            'updated_at' => time(),
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
