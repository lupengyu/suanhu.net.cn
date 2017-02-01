<?php
namespace app\index\controller;

use think\Paginator;
use think\Controller;
use think\Cookie;
use think\Db;
use think\paginator\Collection;
use think\paginator\driver\Bootstrap;
use think\Session;
use think\Route;
use think\Image;
use think\Request;
use app\index\model\User as UserModel;
use app\index\model\Activity as ActivityModel;
use app\index\model\Sign as SignModel;
use app\index\model\Classes as ClassesModel;
use app\index\model\Navigate as NavigateModel;
use app\index\model\School as SchoolModel;
use org\util\ArrayList;

/**
 * Class Index 前端类
 * @package app\index\controller
 */
class Index extends Controller
{

    /**
     * 主页自动跳转认证界面
     *
     * @return mixed 主页面
     */
    public function index()
    {
        Session::start();
        //include('_cas.php');
        Session::set('user_id',2015303135);

        //判断用户是否第一次登录
        $id = Session::get('user_id');
        Session::set('unknown_id',$id);
        $user = UserModel::where('code',$id)->find();
        if($user==null)
        {
            //数据库缺少信息，重定向用户注册界面
            Session::set('user_id',null);
            return $this->redirect('index/index/register');
        }
        //查询到相应信息，跳转主页
        Session::set('unknown_id',null);
        Session::set('user_name',$user->name);
        Session::set('id',$user->id);
        Session::pause();
        return $this->redirect('index/index/home');
    }

    /**
     * 用户注册界面
     *
     * 仅当用户是第一次登录时可以访问
     *
     * @return mixed|void
     */
    public function register()
    {
        Session::start();
        //判断是不是已有用户
        $id = Session::get('user_id');
        $user = UserModel::where('code',$id)->find();
        if($user!=null)
        {
            Session::set('unknown_id',null);
            Session::set('user_name',$user->name);
            Session::set('id',$user->id);
            return $this->redirect('index/index/home');
        }

        //判断是不是第一次注册用户
        $id = Session::get('unknown_id');
        $user = UserModel::where('code',$id)->find();
        if($user!=null)
        {
            Session::set('unknown_id',null);
            Session::set('user_name',$user->name);
            Session::set('id',$user->id);
            return $this->redirect('index/index/home');
        }
        //$this->assign('id',$id);
        return $this->fetch('index/register');
    }

    /**
     * 用户信息绑定
     *
     */
    public function userregister()
    {
        Session::start();
        $id = Session::get('unknown_id');
        if($id==null) {
            return $this->suces('请先登录！','index');
        }
        $user = UserModel::where('code',$id)->find();
        if($user!=null)
        {
            Session::set('unknown_id',null);
            Session::set('user_name',$user->name);
            Session::set('id',$user->id);
            Session::pause();
            return $this->redirect('index/index/home');
        }
        $user = new UserModel;
        $user->code = $id;
        $user->name = input('post.name');
        $user->school = input('post.school');
        $user->grade = input('post.grade');
        $user->class = input('post.class');
        if($user->name==null)
        {
            Session::set('name',$user->name);
            Session::set('class',$user->class);
            return $this->suces('姓名为必填！');
        }
        if($user->school==null)
        {
            Session::set('name',$user->name);
            Session::set('class',$user->class);
            return $this->suces('学院为必填！');
        }
        if($user->grade==null)
        {
            Session::set('name',$user->name);
            Session::set('class',$user->class);
            return $this->suces('年级为必填！');
        }
        if($user->class==null)
        {
            Session::set('name',$user->name);
            Session::set('class',$user->class);
            return $this->suces('班级为必填！');
        }
        if(!is_numeric($user->class))
        {
            Session::set('name',$user->name);
            Session::set('class',$user->class);
            return $this->suces('班级为一串数字编码！');
        }
        $class = ClassesModel::where('code',$user->class)->find();
        if($class==null)
        {
            $class = new ClassesModel();
            $class->code = $user->class;
            $class->school = $user->school;
            $class->grade = $user->grade;
            $class->save();
        }
        Session::set('user_name',$user->name);
        $user->save();
        Session::set('unknown_id',null);
        Session::set('user_id',$id);
        Session::set('name',null);
        Session::set('class',null);
        return $this->redirect('index/index/home');
    }

    public function home()
    {
        Session::start();
        $id = Session::get('user_id');
        $user = UserModel::where('code',$id)->find();
        if($user==null)
        {
            return $this->redirect('index/index/index');
        }
        $school = $user->school;
        $grade = $user->grade;

        $act = ActivityModel::where('status',1)->order('id','desc')->select();

        $list = array();
        for($i=0,$cnt = sizeof($act);$i<$cnt;$i++)
        {
            $item = $act[$i];
            $id = $item->id;
            $result = NavigateModel::where('activity_id',$id)->select();
            for($j=0;$j<sizeof($result);$j++)
            {
                $navigate = $result[$j];
                if($navigate->school==$school||$navigate->school==null)
                {
                    if($navigate->grade==$grade||$navigate->grade==null)
                    {
                        array_push($list,$item);
                        //$collection->appends($list,$list);
                        break;
                    }
                }
            }
        }
        for($i=1;$i>0;$i++)
        {
            if(sizeof($list)>3)
            {
                array_pop($list);
            } else break;
        }
        //dump($list);
        //dump($list);
        //$count = count($list);
        //$paginate = new Bootstrap(null,3,1,sizeof($list));
        //$page = $paginate->render();
        //$arraylist = new Collection($list,$paginate);
        //$page = $arraylist->render();
        //dump($arraylist);
        //dump($page);
        //$arraylist->currentPage();
        //$list = $arraylist->paginate(3);
        //$page = $list->render();
        //$page = $arraylist->render();
        //dump($arraylist);
        //dump('---------------------------------------------');
        //$list = $this->array_page($list,3);
        //$page = $list->render();
        //dump($list);
        //dump('---------------------------------------------------');
        //$asd = Bootstrap::make();
        //$list = ActivityModel::where('status',1)->order('id','desc')->paginate(3);
        //$array = ActivityModel::where('status',1)->order('id','desc')->paginate()->toArray();
        //dump($array);
        //$array = $list->toArray();
        //dump($list);

        //dump($list->shift());
        //$sum = ActivityModel::where('status',1)->count();

        //$page = $list->render();
        //dump($list);
        //dump($page);

        $this->assign('list',$list);
        //$this->assign('page', $page);
        return $this->fetch('index/home');
    }

    public function home_people()
    {
        Session::start();
        $id = Session::get('user_id');
        $user = UserModel::where('code',$id)->find();
        if($user==null)
        {
            return $this->redirect('index/index/index');
        }
        $list = ActivityModel::where('status',1)->order('see','desc')->paginate(3);
        $sum = ActivityModel::where('status',1)->count();
        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page', $page);
        return $this->fetch('index/home');
    }

    public function sign($id='')
    {
        Session::start();
        $user_id = Session::get('user_id');
        $user = UserModel::where('code',$user_id)->find();
        if($user==null)
        {
            return $this->redirect('index/index/index');
        }
        $act = ActivityModel::get($id);
        $class = $user->class;
        $classjudge = ClassesModel::where('code',$class)->find();

        $sign = SignModel::where('user_id',$user->id)->where('activity_id',$id)->find();
        if($sign!=null)
        {
            return $this->suces('重复报名');
        } else{
            $sign = new SignModel();
            $sign->activity_id = $id;
            $sign->user_id = $user->id;
            $sign->name = $user->name;
            $sign->code = $user->code;
            $sign->class=$user->class;
            $sign->title=$act->title;
            $sign->body=$act->summary;
            $sign->save();

            $user->sign ++;
            $user->save();

            $classjudge->sign++;
            $classjudge->save();

            $act->sign++;
            $act->save();

            return $this->suces('报名成功');
        }
    }
}