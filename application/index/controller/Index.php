<?php
namespace app\index\controller;

use think\Page;
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
use app\index\model\Grade as GradeModel;
use app\index\model\User as UserModel;
use app\index\model\Activity as ActivityModel;
use app\index\model\Sign as SignModel;
use app\index\model\Classes as ClassesModel;
use app\index\model\Navigate as NavigateModel;
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
     * @return mixed 主页面渲染
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
        if($class==null) {
            $class = new ClassesModel();
            $class->code = $user->class;
            $class->school = $user->school;
            $class->grade = $user->grade;
            $class->save();
        }
        $grade = GradeModel::where('grade',$user->grade)->where('school',$user->grade)->find();
        if($grade==null) {
            $grade = new GradeModel();
            $grade->school = $user->school;
            $grade->grade = $user->grade;
            $grade->save();
        }
        Session::set('user_name',$user->name);
        $user->save();
        Session::set('unknown_id',null);
        Session::set('user_id',$id);
        Session::set('name',null);
        Session::set('class',null);
        return $this->redirect('index/index/home');
    }

    /**
     * 首页
     * @return mixed|void 首页渲染
     */
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

        if($user->status==2) {
            $grade = 0;
        } else if($user->status>3) {
            $school = 0;
            $grade = 0;
        }

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
                if($navigate->school==$school||$navigate->school==null||$school==0)
                {
                    if($navigate->grade==$grade||$navigate->grade==null||$grade==0)
                    {
                        array_push($list,$item);
                        break;
                    }
                }
            }
        }

        for($i=1;$i>0;$i++)
        {
            if(sizeof($list)>15)
            {
                array_pop($list);
            } else break;
        }


        import('ORG.Util.Page');
        $count=count($list);
        $Page=new Page($count,3);
        $list=array_slice($list,$Page->firstRow,$Page->listRows);
        //dump($Page->nowPage);
        //dump($Page->totalPages);
        $page = $Page->show();

        $this->assign('status',$user->status);
        $this->assign('list',$list);
        $this->assign('page',$Page->nowPage);
        $this->assign('lastpage',$Page->totalPages);
        $this->assign('type','time');
        return $this->fetch('index/home');
    }

    /**
     * 首页-活动按人气排序
     * @return mixed|void 首页渲染
     */
    public function home_people()
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

        if($user->status==2) {
            $grade = 0;
        } else if($user->status>3) {
            $school = 0;
            $grade = 0;
        }

        $act = ActivityModel::where('status',1)->order('see','desc')->select();
        $list = array();
        for($i=0,$cnt = sizeof($act);$i<$cnt;$i++)
        {
            $item = $act[$i];
            $id = $item->id;
            $result = NavigateModel::where('activity_id',$id)->select();
            for($j=0;$j<sizeof($result);$j++)
            {
                $navigate = $result[$j];
                if($navigate->school==$school||$navigate->school==null||$school==0)
                {
                    if($navigate->grade==$grade||$navigate->grade==null||$grade==0)
                    {
                        array_push($list,$item);
                        break;
                    }
                }
            }
        }
        for($i=1;$i>0;$i++)
        {
            if(sizeof($list)>15)
            {
                array_pop($list);
            } else break;
        }


        import('ORG.Util.Page');
        $count=count($list);
        $Page=new Page($count,3);
        $list=array_slice($list,$Page->firstRow,$Page->listRows);
        $page = $Page->show();

        $this->assign('status',$user->status);
        $this->assign('list',$list);
        $this->assign('page',$Page->nowPage);
        $this->assign('lastpage',$Page->totalPages);
        $this->assign('type','people');
        return $this->fetch('index/home');
    }

    /**
     * 活动报名
     * @param string $id 报名活动id
     */
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
            $sign->school = $user->school;
            $sign->grade = $user->grade;
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

    /**
     * 个人主页
     * @return mixed|void
     */
    public function user_home()
    {
        Session::start();
        $id = Session::get('user_id');
        $user = UserModel::where('code',$id)->find();
        if($user==null)
        {
            return $this->redirect('index/index/index');
        }

        $list = SignModel::where('user_id',$user->id)->paginate(3);
        $page = $list->render();

        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('user',$user);
        return $this->fetch('index/user_home');
    }
}
