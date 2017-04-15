<?php

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Api\Transformers\ChunkTransformer;
use Orig as Chunk;

class ChunkController extends ApiController
{
    public function actionIndex($material_id)
    {
        $chunks = Chunk::model()
            ->with('trs', 'trs.user', 'trs.marks', 'comments:cleanOrder')
            ->findAllByAttributes(
                ['chap_id' => (int) $material_id],
                ['order' => 't.id ASC',]
            );

        $resource = new Collection($chunks, new ChunkTransformer());

        $this->response($this->fractal->createData($resource)->toJson());
    }

    public function actionShow($material_id, $chunk_id)
    {
        $chunk = Chunk::model()
            ->with('trs', 'trs.user', 'trs.marks', 'comments:cleanOrder')
            ->findByAttributes(
                ['id' => (int) $chunk_id, 'chap_id' => (int) $material_id],
                ['order' => 't.id ASC',]
            );

        $resource = new Item($chunk, new ChunkTransformer);

        $this->response($this->fractal->createData($resource)->toJson());
    }
}
