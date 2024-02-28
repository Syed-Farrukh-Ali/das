<?php

namespace App\Models\Auth\Traits\Method;

trait UserMethod
{
    /**
     * @return mixed
     */
    public function isAdmin()
    {
        return $this->hasRole(config('access.users.super_admin'));
    }
}
