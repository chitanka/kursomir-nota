<?php

namespace Api\Transformers;

use League\Fractal\TransformerAbstract;
use User as Translator;

class TranslatorTransformer extends TransformerAbstract
{
    public function transform(Translator $translator)
    {
        $avatar_link = count($translator->upic) ?
            "/i/upic/0/{$translator->id}-{$translator->upic[0]}.jpg" :
            null;

        return array_filter([
            'user_id' => $translator->id,
            'nickname' => $translator->login,
            'avatar' => $avatar_link,
            'created_at' => strtotime($translator->cdate),
            'updated_at' => strtotime($translator->cdate),
        ]);
    }
}
