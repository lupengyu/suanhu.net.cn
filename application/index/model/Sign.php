<?php
namespace app\index\model;

use think\Model;

class Sign extends Model
{
    public function user()
    {
        return $this->belongsTo('User');
    }

    public function activity()
    {
        return $this->belongsTo('Activity');
    }
}