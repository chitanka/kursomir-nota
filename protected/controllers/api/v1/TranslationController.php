<?php

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Api\Transformers\TranslationTransformer;
use Orig as Chunk;

class TranslationController extends ApiController
{
    public function actionIndex($material_id, $chunk_id)
    {
        $translations = Translation::model()
            ->with('user', 'marks')
            ->findAllByAttributes(
                ['chap_id' => (int) $material_id, 'orig_id' => (int) $chunk_id],
                ['order' => 't.id ASC']
            );

        $resource = new Collection($translations, new TranslationTransformer());

        $this->json($resource);
    }

    public function actionStore($material_id, $chunk_id)
    {
        $data = $this->getJsonRequest();
        $chunk = Chunk::model()->findByAttributes([
            'id' => (int) $chunk_id, 'chap_id' => (int) $material_id,
        ]);

        // validation
        if ( ! isset($data['body']) && empty($data['body'])) {
            $this->abort(400, "Field 'body' is requred.");
        }
        if ( ! $chunk) {
            $this->abort(404, "'Material' or 'Chunk' was not found.");
        }

        $translation = new Translation();
        $translation->orig_id = $chunk->id;
        $translation->chap_id = $chunk->chap->id;
        $translation->book_id = $chunk->chap->book->id;
        $translation->user_id = $this->user->id;
        $translation->orig = $chunk;
        $translation->chap = $chunk->chap;
        $translation->book = $chunk->chap->book;
        $translation->body = htmlentities($data['body']);

        if ( ! $translation->save()) {
            $this->abort(500, $translation->errorsString);
        }

        $this->updateSlice($chunk);

        $this->actionShow($material_id, $chunk_id, $translation->id);
    }

    public function actionUpdate($material_id, $chunk_id, $translation_id)
    {
        $data = $this->getJsonRequest();
        if ( ! isset($data['body']) && empty($data['body'])) {
            $this->abort(400, "Field 'body' is requred.");
        }

        $translation = Translation::model()
            ->findByAttributes([
                'id' => (int) $translation_id,
                'chap_id' => (int) $material_id,
                'orig_id' => (int) $chunk_id,
            ]);

        if ( ! $translation) {
            $this->abort(404, "'Material', 'Chunk' or 'Translation' was not found.");
        }

        if ($translation->orig->chap->book->membership->status != GroupMember::MODERATOR) {
            if ($translation->user_id == $this->user->id) {
                $translation->rating = 0;
                $translation->n_votes = 0;
                $translation->removeMarks();
            } else {
                $this->abort(403, "You can't update this translation");
            }
        }

        $translation->body = htmlentities($data['body']);

        if ( ! $translation->save()) {
            $this->abort(500, $translation->errorsString);
        }

        $this->actionShow($material_id, $chunk_id, $translation->id);
    }

    public function actionShow($material_id, $chunk_id, $translation_id)
    {
        $translation = Translation::model()
            ->with('user', 'marks')
            ->findByAttributes([
                'id' => (int) $translation_id,
                'chap_id' => (int) $material_id,
                'orig_id' => (int) $chunk_id,
            ]);

        if ( ! $translation) {
            $this->abort(404, "'Material', 'Chunk' or 'Translation' was not found.");
        }

        $resource = new Item($translation, new TranslationTransformer());

        $this->json($resource);
    }

    public function actionDestroy($material_id, $chunk_id, $translation_id)
    {
        $translation = Translation::model()
            ->findByAttributes([
                'id' => (int) $translation_id,
                'chap_id' => (int) $material_id,
                'orig_id' => (int) $chunk_id,
            ]);

        if ( ! $translation) {
            $this->abort(404, "'Material', 'Chunk' or 'Translation' was not found.");
        }

        if ($translation->user_id != $this->user->id && $translation->chap->book->membership->status != GroupMember::MODERATOR) {
            $this->abort(403, "You can't update this translation");
        }

        if ($translation->delete()) {
            $translation->chap->setModified();
        }

        $this->response('', 204);
    }

    public function actionRate($material_id, $chunk_id, $translation_id)
    {
        $data = $this->getJsonRequest();
        $translation = Translation::model()
            ->findByAttributes([
                'id' => (int) $translation_id,
                'chap_id' => (int) $material_id,
                'orig_id' => (int) $chunk_id,
            ]);

        if ( ! $translation) {
            $this->abort(404, "'Material', 'Chunk' or 'Translation' was not found.");
        }
        if ($translation->user_id == $this->user->id) {
            $this->abort(403, "You can't rate your own translation");
        }
        if ( ! isset($data['value']) && empty($data['value'])) {
            $this->abort(400, "Field 'value' is requred.");
        }
        $mark = (int) $data['value'];
        if ($mark != 1 && $mark != -1) {
            $this->abort(400, "Field 'value' can contain only 1 or -1");
        }

        $this->updateRating($translation, $mark);

        return $this->actionShow($material_id, $chunk_id, $translation_id);
    }

    private function updateRating($translation, $mark)
    {
        $sql = array();
        $sql_params = array(':user_id' => $this->user->id, ':id' => $translation->id);
        $d_rating = $d_n_votes = 0;

        if ($translation->mark) {
            // Я уже оценивал этот перевод
            $d_rating = $mark - $translation->mark->mark;
            if ($mark == 0) {
                $sql[] = 'DELETE FROM marks WHERE user_id = :user_id AND tr_id = :id;';
                $d_n_votes = -1;
                --$translation->n_votes;
            } else {
                if ($d_rating != 0) {
                    $sql[] = 'UPDATE marks SET mark = :mark WHERE user_id = :user_id AND tr_id = :id;';
                }
                $sql_params[':mark'] = $mark;
            }
        } else {
            // Новая оценка
            $d_rating = $mark;
            $d_n_votes = 1;
            $sql[] = 'INSERT INTO marks (user_id, tr_id, mark) VALUES (:user_id, :id, :mark);';
            $sql_params[':mark'] = $mark;
            ++$translation->n_votes;
        }

        if ($d_rating != 0) {
            $translation->rating += $d_rating;

            // Рейтинг перевода
            $sql[] = 'UPDATE translate SET rating = rating + :d_rating, n_votes = n_votes + :d_n_votes WHERE id = :id;';
            $sql_params[':d_rating'] = $d_rating;
            $sql_params[':d_n_votes'] = $d_n_votes;

            // Рейтинг автора перевода
            $sql[] = 'UPDATE users SET rate_t = rate_t + :d_rating WHERE id = :author_id;';
            $sql_params[':author_id'] = $translation->user_id;

            // Рейтинг автора в группе
            $sql[] = 'UPDATE groups SET rating = rating + :d_rating WHERE book_id = :book_id AND user_id = :author_id;';
            $sql_params[':book_id'] = $translation->chap->book_id;
        }

        if (count($sql)) {
            $sql_all = "BEGIN;\n".join("\n", $sql)."\nCOMMIT;";
            Yii::app()->db->createCommand($sql_all)->execute($sql_params);
            $translation->chap->setModified();
        }
    }

    private function updateSlice($chunk)
    {
        $chunk->chap->setModified();
        $book = $chunk->chap->book;

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
