<?php

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Api\Transformers\TranslationTransformer;
use Orig as Slice;

class TranslationController extends ApiController
{
    public function actionIndex($material_id, $slice_id)
    {
        $translations = Translation::model()
            ->with('user', 'marks')
            ->findAllByAttributes(
                ['chap_id' => (int) $material_id, 'orig_id' => (int) $slice_id],
                ['order' => 't.id ASC']
            );

        $resource = new Collection($translations, new TranslationTransformer());

        $this->json($resource);
    }

    public function actionStore($material_id, $slice_id)
    {
        $data = $this->getJsonRequest();
        $slice = Slice::model()->findByAttributes([
            'id' => (int) $slice_id, 'chap_id' => (int) $material_id
        ]);

        // validation
        if ( ! isset($data['body']) && empty($data['body'])) $this->abort(400, "Field 'body' is requred.");
        if ( ! $slice) $this->abort(404, "'Material' or 'Slice' was not found.");

        $translation = new Translation;
        $translation->orig_id = $slice->id;
        $translation->chap_id = $slice->chap->id;
        $translation->book_id = $slice->chap->book->id;
        $translation->user_id = $this->user->id;
        $translation->orig = $slice;
        $translation->chap = $slice->chap;
        $translation->book = $slice->chap->book;
        $translation->body = htmlentities($data['body']);

        if (! $translation->save()) {
            $this->abort(500, $translation->errorsString);
        }

        $this->updateSlice($slice);

        $this->actionShow($material_id, $slice_id, $translation->id);
    }

    public function actionUpdate($material_id, $slice_id, $translation_id)
    {
        $data = $this->getJsonRequest();
        if ( ! isset($data['body']) && empty($data['body'])) $this->abort(400, "Field 'body' is requred.");

        $translation = Translation::model()
            ->findByAttributes([
                'id' => (int) $translation_id,
                'chap_id' => (int) $material_id,
                'orig_id' => (int) $slice_id,
            ]);

        if ( ! $translation) $this->abort(404, "'Material', 'Slice' or 'Translation' was not found.");

        if($translation->orig->chap->book->membership->status != GroupMember::MODERATOR) {
            if($translation->user_id == $this->user->id) {
                $translation->rating = 0;
                $translation->n_votes = 0;
                $translation->removeMarks();
            } else {
                $this->abort(403, "You can't update this translation");
            }
        }

        $translation->body = htmlentities($data['body']);

        if (! $translation->save()) {
            $this->abort(500, $translation->errorsString);
        }

        $this->actionShow($material_id, $slice_id, $translation->id);
    }

    public function actionShow($material_id, $slice_id, $translation_id)
    {
        $translation = Translation::model()
            ->with('user', 'marks')
            ->findByAttributes([
                'id' => (int) $translation_id,
                'chap_id' => (int) $material_id,
                'orig_id' => (int) $slice_id,
            ]);

        if ( ! $translation) $this->abort(404, "'Material', 'Slice' or 'Translation' was not found.");

        $resource = new Item($translation, new TranslationTransformer());

        $this->json($resource);
    }

    public function actionDestroy($material_id, $slice_id, $translation_id)
    {
        $translation = Translation::model()
            ->with('user', 'marks')
            ->findByAttributes([
                'id' => (int) $translation_id,
                'chap_id' => (int) $material_id,
                'orig_id' => (int) $slice_id,
            ]);

        if ( ! $translation) $this->abort(404, "'Material', 'Slice' or 'Translation' was not found.");

        if($translation->user_id != $this->user->id && $translation->chap->book->membership->status != GroupMember::MODERATOR) {
            $this->abort(403, "You can't update this translation");
        }

        if($translation->delete()) {
			$translation->chap->setModified();
		}

        $this->response('', 204);
    }

    private function updateSlice($slice)
    {
        $slice->chap->setModified();
        $book = $slice->chap->book;

        // Добавили новый перевод
        if ($book->membership === null || $book->membership->status === '') {
            // Вступаем в группу, раз нас там не было
            $book->membership = new GroupMember();
            $book->membership->book_id = $book->id;
            $book->membership->user_id = $this->user->user_id;
            $book->membership->status = GroupMember::CONTRIBUTOR;
            $book->membership->n_trs = 1;
            $book->membership->rating = 0;
        } else {
            // Обновляем статистику группы
            ++$book->membership->n_trs;
        }
        $book->membership->last_tr = new CDbExpression('now()');
        $book->membership->save(false);
    }
}
