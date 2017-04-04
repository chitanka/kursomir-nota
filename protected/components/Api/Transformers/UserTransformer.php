<?php

namespace Api\Transformers;

use League\Fractal\TransformerAbstract;
use User;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        $avatar_link = count($user->upic) ?
            "/i/upic/0/{$user->id}-{$user->upic[0]}.jpg" :
            null;

        return array_filter([
            'user_id' => $user->id,
            'nickname' => $user->login,
            'avatar' => $avatar_link,
            'created_at' => strtotime($user->cdate)?:time(),
            'updated_at' => strtotime($user->cdate)?:time(),
        ]);
    }
}
