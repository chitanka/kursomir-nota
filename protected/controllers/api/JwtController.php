<?php

class JwtController extends ApiController
{
    public function beforeAction($event)
    {
        return true;
    }
    
    public function actionJwtize()
    {
        if (Yii::app()->user->isGuest) {
            return $this->redirect('/');
        }

        $user = Yii::app()->user->model;

        if ( ! $this->isJwtized()) {
            $token = $this->tokenManager->tokenize([
                'user_id' => $user->id,
            ]);

            $this->setToken($token);
        }

        $this->redirect('/');
    }

    private function setToken($token)
    {
        $cookie = new CHttpCookie($this->tokenName, (string) $token);

        Yii::app()->request->cookies[$this->tokenName] = $cookie;
    }

    private function isJwtized()
    {
        return ! is_null($this->getToken());
    }
}
