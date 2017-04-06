<?php

use League\Fractal\Manager;
use Api\Serializers\FlatJsonSerializer;
use Api\Jwt\Manager as JWTManager;

class ApiController extends CController
{
    protected $tokenName = 'token';
    protected $user;

    protected $fractal;
    protected $tokenManager;

    public function beforeAction($event)
    {
        if (! $this->user) {
            $this->abort(401);
        }

        return true;
    }

    public function init()
    {
        $this->tokenManager = new JWTManager(md5(Yii::app()->params->passwordSalt));
        $this->fractal = new Manager();
        $this->fractal->setSerializer(new FlatJsonSerializer());

        $this->user = $this->getUser();

        parent::init();
    }

    private function getUser()
    {
        if ( ! $token = $this->getToken()) {
            return null;
        }

        try {
            $token = $this->tokenManager->parse($token);
        } catch (\Exception $e) {
            $this->abort(401, 'token is not valid');
        }

        if ( ! $user = User::model()->findByPk($token->getClaim('user_id'))) {
            $this->abort(401);
        }

        return $user;
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
        if (strlen($message) == 0) {
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

    protected function getToken()
    {
        return Yii::app()->request->cookies[$this->tokenName]->value;
    }

    protected function getJsonRequest()
    {
        $post = file_get_contents('php://input');

        return CJSON::decode($post, true);
    }
}
