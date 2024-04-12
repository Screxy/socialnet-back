<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\User;

class UserRepository
{
    public static function getById(int $id): ?User
    {
        return User::getById($id);
    }
}
