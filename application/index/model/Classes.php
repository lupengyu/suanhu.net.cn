<?php
namespace app\index\model;

use think\Model;

class Classes extends Model
{
    public function users()
    {
        return $this->hasMany('User');
    }
}