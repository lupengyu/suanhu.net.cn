<?php
namespace app\index\model;

use think\Model;

class Grade extends Model
{
    public function classes()
    {
        return $this->hasMany('Classes');
    }
}