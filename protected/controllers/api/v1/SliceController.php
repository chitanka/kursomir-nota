<?php

use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Api\Transformers\SliceTransformer;
use Orig as Slice;

class SliceController extends ApiController
{
    public function actionIndex($material_id)
    {
        $slices = Slice::model()
            ->with('trs', 'trs.user', 'trs.marks', 'comments:cleanOrder')
            ->findAllByAttributes(
                ['chap_id' => (int) $material_id],
                ['order' => 't.id ASC',]
            );

        $resource = new Collection($slices, new SliceTransformer());

        $this->response($this->fractal->createData($resource)->toJson());
    }

    public function actionShow($material_id, $slice_id)
    {
        $slice = Slice::model()
            ->with('trs', 'trs.user', 'trs.marks', 'comments:cleanOrder')
            ->findByAttributes(
                ['id' => (int) $slice_id, 'chap_id' => (int) $material_id],
                ['order' => 't.id ASC',]
            );

        $resource = new Item($slice, new SliceTransformer);

        $this->response($this->fractal->createData($resource)->toJson());
    }
}
