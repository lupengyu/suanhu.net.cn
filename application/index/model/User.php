<?php
namespace app\index\model;

use think\Model;

class User extends Model
{
    public function signs()
    {
        return $this->hasMany('Sign');
    }
}