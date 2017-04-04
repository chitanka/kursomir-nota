<?php

use League\Fractal\Manager;
use Api\Serializers\FlatJsonSerializer;

class ApiController extends CController
{
    protected $fractal;

    public function __construct()
    {
        $this->fractal = new Manager();
        $this->fractal->setSerializer(new FlatJsonSerializer());
    }

    protected function response($body = '', $status = 200, $content_type = 'application/json')
    {
        $status_header = 'HTTP/1.1 '.$status.' '.$this->getStatusCodeMessage($status);
        header($status_header);
        header('Content-type: '.$content_type);

        echo $body;

        Yii::app()->end();
    }

    protected function abort($status = 400, $message = '')
    {
        if (strlen($body) == 0) {
            $message = $this->getStatusCodeMessage($status);
        }

        $body = json_encode([
            'error' => $message,
        ]);

        $this->response($body, $status);
    }

    private function getStatusCodeMessage($status)
    {
        $codes = array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );

        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    protected function json($resource)
    {
        $data = $this->fractal
            ->createData($resource)
            ->toJson();
            
        $this->response($data);
    }
}
