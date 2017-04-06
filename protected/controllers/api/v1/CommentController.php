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
}
