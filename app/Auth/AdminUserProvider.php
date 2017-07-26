<?php

namespace App\Auth;

use App\User;
use Illuminate\Contracts\Auth\User as UserContract;
// use Illuminate\Auth\GenericUser;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class AdminUserProvider extends EloquentUserProvider
{
    /**
     * Create a new database user provider.
     *
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param string                               $model
     */
    public function __construct($model)
    {
        $hasher = new BcryptHasher(); // default hasher
        parent::__construct($hasher, $model);
    }


    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];

        return sha1($plain) === $user->password;
    }
}
