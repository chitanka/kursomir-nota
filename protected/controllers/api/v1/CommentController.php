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

    public function actionStore($material_id, $slice_id)
    {
        $data = $this->getJsonRequest();
        $slice = Slice::model()
            ->findByAttributes([
                'id' => (int) $slice_id,
                'chap_id' => (int) $material_id,
            ]);

        if ( ! $slice) {
            $this->abort(404, "'Material' or 'Slice' was not found.");
        }
        if ( ! $slice->chap->can('comment')) {
            $this->abort(403, "You can't comment this slice. ".$slice->chap->getWhoCanDoIt('comment', false));
        }
        if ( ! isset($data['body']) && empty($data['body'])) {
            $this->abort(400, "Field 'body' is requred");
        }
        if (isset($data['parent_id'])) {
            $parent = Comment::model()->findByPk((int) $data['parent_id']);
            if ( ! $parent) {
                $this->abort(400, 'Parent comment does not exist.');
            }
        }

        $comment = new Comment();
        $comment->orig = $slice;
        $comment->orig_id = $slice->id;
        $comment->body = $data['body'];
        $comment->user_id = $this->user->id;

        if ($parent) {
            if ($parent->reply($comment)) {
                $parent->orig->afterCommentAdd($comment, $parent);
            } else {
                $this->abort(500, $parent->getErrorsString());
            }
        } else {
            if ( ! $comment->save()) {
                $this->abort(500, $comment->getErrorsString());
            }
        }

        $resource = new Item($comment, new CommentTransformer());

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

    public function actionUpdate($material_id, $slice_id, $comment_id)
    {
        $data = $this->getJsonRequest();
        $comment = Comment::model()
            ->with('author')
            ->findByAttributes(
                ['id' => $comment_id, 'orig_id' => (int) $slice_id]
            );

        if ( ! $comment) {
            $this->abort(404, "'Material', 'Slice' or 'Comment' was not found.");
        }
        if ($comment->author->id != $this->user->id) {
            $this->abort(403, "You can't edit this comment.");
        }
        if ( ! isset($data['body']) && empty($data['body'])) {
            $this->abort(400, "Field 'body' is requred");
        }

        $comment->body = $data['body'];
        $comment->save();

        $this->actionShow($material_id, $slice_id, $comment_id);
    }

    public function actionDestroy($material_id, $slice_id, $comment_id)
    {
        $comment = Comment::model()
            ->with('author')
            ->findByAttributes(
                ['id' => $comment_id, 'orig_id' => (int) $slice_id]
            );

        if ( ! $comment) {
            $this->abort(404, "'Material', 'Slice' or 'Comment' was not found.");
        }
        if ($comment->author->id != $this->user->id) {
            $this->abort(403, "You can't delete this comment.");
        }

        if ( ! $comment->delete()) {
            $this->abort(500, $comment->getErrorsString());
        }

        $comment->orig->afterCommentRm($comment);

        $this->response('', 204);
    }
}
