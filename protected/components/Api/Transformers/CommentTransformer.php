<?php

namespace Api\Transformers;

use League\Fractal\TransformerAbstract;
use Comment;

class CommentTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'commentator',
    ];

    public function transform(Comment $comment)
    {
        return array_filter([
            'comment_id' => $comment->id,
            'commentator' => $comment->author,
            'body' => $comment->body,
            'parent_id' => $comment->pid,
            'created_at' => $comment->cdate,
            'updated_at' => $comment->cdate,
        ]);
    }

    public function includeCommentator(Comment $comment)
    {
        return $this->item($comment->author, new UserTransformer());
    }
}
