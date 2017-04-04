<?php

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Api\Transformers\TranslationTransformer;

class TranslationController extends ApiController
{
    public function actionIndex($material_id, $slice_id)
    {
        $translations = Translation::model()
            ->with('user', 'marks')
            ->findAllByAttributes(
                ['chap_id' => (int) $material_id, 'orig_id' => (int) $slice_id],
                ['order' => 't.id ASC',]
            );

        $resource = new Collection($translations, new TranslationTransformer());

        $this->json($resource);
    }

    public function actionShow($material_id, $slice_id, $translation_id)
    {
        $slice = Translation::model()
            ->with('user', 'marks')
            ->findByAttributes([
                'id' => (int) $translation_id,
                'chap_id' => (int) $material_id,
                'orig_id' => (int) $slice_id,
            ]);

        $resource = new Item($slice, new TranslationTransformer);

        $this->json($resource);
    }
}
