<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthBaseModel extends Model
{


    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = bcrypt($value);
        }
    }

    public function login()
    {
        $token = $this->createToken('auth_token')->plainTextToken;
        return $token;
    }

    public function logout()
    {
        $this->tokens()->delete();
        return true;
    }
}
