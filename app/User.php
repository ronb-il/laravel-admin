<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /*
     * Stored as JSON, so we should decode it
     */
    protected $casts = [
        'permissions' => 'array'
    ];


    public function setPasswordAttribute($password) {
        // we could add a condition to check if it's already sha1 (40 characters)
        $this->attributes['password'] = (strlen($password) == 40) ? $password : sha1($password);
    }

    public function isAdmin() {
        return ($this->permissions['roles'][0] === 'admin');
    }

    public function getUserRoleId() {
        $rolesArray = config('app.roles');
        $roleName = $this->permissions['roles'][0];
        $index = array_search($roleName, array_keys($rolesArray));

        return $index;
    }
}
