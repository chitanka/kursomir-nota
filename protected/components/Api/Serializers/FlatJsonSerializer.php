<?php

namespace Api\Serializers;

use League\Fractal\Serializer\DataArraySerializer;

class FlatJsonSerializer extends DataArraySerializer
{
    public function collection($resourceKey, array $data)
    {
        return ($resourceKey && $resourceKey !== 'data') ? array($resourceKey => $data) : $data;
    }

    public function item($resourceKey, array $data)
    {
        return ($resourceKey && $resourceKey !== 'data') ? array($resourceKey => $data) : $data;
    }
}
