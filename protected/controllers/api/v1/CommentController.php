<?php

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Api\Transformers\CommentTransformer;
use Orig as Slice;

class CommentController extends ApiController
{
    public function actionIndex($material_id, $slice_id)
    {
        $comments = Comment::model()
            ->with('author')
            ->findAllByAttributes(
                ['orig_id' => (int) $slice_id]
            );

        $resource = new Collection($comments, new CommentTransformer());

        $this->json($resource);
    }

    public function actionShow($material_id, $slice_id, $comment_id)
    {
        $comment = Comment::model()
            ->with('author')
            ->findByAttributes(
                ['id' => $comment_id, 'orig_id' => (int) $slice_id]
            );

        if ( ! $comment) {
            $this->abort(404, "'Material', 'Slice' or 'Comment' was not found.");
        }

        $resource = new Item($comment, new CommentTransformer());

        $this->json($resource);
    }
}
