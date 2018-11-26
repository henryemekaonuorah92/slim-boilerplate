<?php

declare(strict_types=1);

namespace App\Transformers\Users;

use App\Transformers\AbstractTransformer;

class UserTransformer extends AbstractTransformer
{
    public function item($user): array
    {
        return [
                'name' => $user['name'],
                'surname' => $user['surname'],
                'email' => $user['email'],
        ];
    }
}
