<?php
namespace app\index\model;

use think\Model;

class Activity extends Model
{
    public function signs()
    {
        return $this->hasMany('Sign');
    }
    public function navigates()
    {
        return $this->hasMany('Navigate');
    }
}