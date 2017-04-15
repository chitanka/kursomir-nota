<?php

namespace Api\Transformers;

use League\Fractal\TransformerAbstract;
use Orig;

class ChunkTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'translations',
        'comments',
    ];

    public function transform(Orig $chunk)
    {
        return [
            'chunk_id' => (int) $chunk->id,
            'type' => [
                    'chunk_type_id' => '1',
                    'name' => 'text',
                    'display_name' => 'текст',
                ],
            'translatable' => 'true',
            'body' => $chunk->body,
            'translations' => $chunk->trs,
            'comments' => $chunk->comments,
            'created_at' => time(),
            'updated_at' => time(),
        ];
    }

    public function includeTranslations(Orig $chunk)
    {
        return $this->collection($chunk->trs, new TranslationTransformer());
    }

    public function includeComments(Orig $chunk)
    {
        return $this->collection($chunk->comments, new CommentTransformer());
    }
}
