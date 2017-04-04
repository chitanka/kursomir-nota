<?php

namespace Api\Transformers;

use League\Fractal\TransformerAbstract;
use User as Commentator;

class CommentatorTransformer extends TransformerAbstract
{
    public function transform(Commentator $commentator)
    {
        $avatar_link = count($commentator->upic) ?
            "/i/upic/0/{$commentator->id}-{$commentator->upic[0]}.jpg" :
            null;

        return array_filter([
            'user_id' => $commentator->id,
            'nickname' => $commentator->login,
            'avatar' => $avatar_link,
            'created_at' => strtotime($commentator->cdate)?:time(),
            'updated_at' => strtotime($commentator->cdate)?:time(),
        ]);
    }
}
