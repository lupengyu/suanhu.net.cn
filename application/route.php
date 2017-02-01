<?php

use think\Route;

Route::pattern([ 'id'=>'\d+',
]);

Route::rule('sign/:id','index/Index/sign');
Route::rule('backstage/classinformation/:id','index/Backstage/classinformation');
Route::rule('backstage/studentinformation/:id','index/Backstage/studentinformation');
Route::rule('backstage/activityinformation/:id','index/Backstage/activityinformation');
Route::rule('backstage/success/:id','index/Backstage/successs');
Route::rule('backstage/ban/:id','index/Backstage/ban');
Route::rule('backstage/disban/:id','index/Backstage/disban');
Route::rule('backstage/searchadmin/:id','index/Backstage/searchadmin');
Route::rule('backstage/addadmin/:id','index/Backstage/addadmin');
Route::rule('backstage/banadmin/:id','index/Backstage/banadmin');
Route::rule('backstage/signexcel/:id','index/Backstage/signexcel');
Route::rule('backstage/classinfoexcel/:id','index/Backstage/classinfoexcel');
Route::rule('backstage/studentinfoexcel/:id','index/Backstage/studentinfoexcel');

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
    'index'=>'index/index/index',
    'home'=>'index/index/home',
    'home_people'=>'index/index/home_people',
    'register'=>'index/index/register',
    'userregister'=>'index/index/userregister',
    'backstage' =>'index/backstage/index',
    'backstage/home' =>'index/backstage/home',
    'backstage/logout' =>'index/backstage/logout',
    'backstage/activity'=>'index/backstage/activity',
    'backstage/class'=>'index/backstage/classs',
    'backstage/student'=>'index/backstage/student',
    'backstage/addactivity'=>'index/backstage/addactivity',
    'backstage/searchactivity'=>'index/backstage/searchactivity',
    'backstage/searchclass'=>'index/backstage/searchclass',
    'backstage/searchstudent'=>'index/backstage/searchstudent',
    'backstage/gradeadmin'=>'index/backstage/gradeadmin',
    'backstage/collegeadmin'=>'index/backstage/collegeadmin',
    'backstage/schooladmin'=>'index/backstage/schooladmin',
    'backstage/diary'=>'index/backstage/diary',
    'backstage/actexcel'=>'index/backstage/actexcel',
    'backstage/classexcel'=>'index/backstage/classexcel',
    'backstage/studentexcel'=>'index/backstage/studentexcel',
];