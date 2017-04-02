<?php

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Api\Transformers\SliceTransformer;
use Api\Serializers\FlatJsonSerializer;

class SliceController extends ApiController
{
    private $fractal;

    public function __construct()
    {
        $this->fractal = new Manager();
        $this->fractal->setSerializer(new FlatJsonSerializer());
    }

    public function actionIndex($chapter_id)
    {
        $material_id = (int) $material_id;
        $chapter_id = (int) $chapter_id;

        $slices = Orig::model()
            ->with('trs', 'trs.user')
            ->findAllByAttributes(['chap_id' => $chapter_id], ['order' => 't.id ASC']);

        $resource = new Collection($slices, new SliceTransformer());

        $this->response($this->fractal->createData($resource)->toJson());
    }
}
