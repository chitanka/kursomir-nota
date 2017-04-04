<?php

use League\Fractal\Resource\Collection;
use Api\Transformers\SliceTransformer;
use Orig as Slice;

class SliceController extends ApiController
{
    public function actionIndex($chapter_id)
    {
        $slices = Slice::model()
            ->with('trs', 'trs.user', 'comments:cleanOrder')
            ->findAllByAttributes(
                ['chap_id' => (int) $chapter_id],
                ['order' => 't.id ASC',]
            );

        $resource = new Collection($slices, new SliceTransformer());

        $this->response($this->fractal->createData($resource)->toJson());
    }
}
