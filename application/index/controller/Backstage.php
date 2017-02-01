<?php
namespace app\index\controller;

use think\Controller;
use think\Cookie;
use think\Db;
use think\Session;
use think\Route;
use think\Image;
use think\Request;
use app\index\model\User as UserModel;
use app\index\model\Activity as ActivityModel;
use app\index\model\Sign as SignModel;
use app\index\model\Classes as ClassesModel;
use app\index\model\Navigate as NavigateModel;
use app\index\model\Diary as DiaryModel;
use phpmailer;

/**
 * 后台操作类
 * Class Backstage
 * @package app\index\controller
 */
class Backstage extends Controller
{
    /**
     * 权限判断
     * 判断权限是否大于输入参数
     *
     * @param int $status 权限判断参数
     * @return array|bool|false|\PDOStatement|string|\think\Model 查询的用户数据
     */
    private function judge($status=0)
    {
        $id = Session::get('user_id');
        if($id==null) {
            Session::pause();
            $this->index();
        }
        $user = UserModel::where('code',$id)->where('status','>',$status)->find();
        if($user == null) {
            return false;
        }
        Session::set('user_name',$user->name);
        Session::set('user_status',$user->status);
        Session::set('user_code',$user->id);
        Session::pause();
        return $user;
    }

    /**
     * 日志写入
     * 访问或操作被拒后写入日志
     *
     * @param string $thing 具体行为
     */
    private function reject($thing='链接后台')
    {
        $diary = new DiaryModel();
        $diary->summary = '学号/工号为 '.Session::get('user_id').' 的用户尝试'.$thing.'被拒绝。';
        $diary->save();
        return $this->redirect('index/index/index');
    }

    /**
     * 日志写入
     * 写入具体操作
     *
     * @param string $summary 操作内容
     * @return false|int 操作成功判断
     */
    private function adddiary($summary='')
    {
        $diary = new DiaryModel();
        $diary->summary = '学号/工号为 '.Session::get('user_id').' 的用户'.$summary;
        return $diary->save();
    }

    /**
     * 首页
     *
     * 跳转登录界面，渲染home界面
     */
    public function index()
    {
        Session::start();
        //include('_cas.php');
        Session::set('user_id',2015303135);
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }
        //$status = $user->status;
        //Session::set('id',$user->id);
        //Session::set('user_status',$status);

        return $this->redirect('index/backstage/home');
    }

    /**
     * 首页渲染
     *
     * @return mixed|void
     */
    public function home()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }
        //dump(Session::get('user_id'));
        //dump(Session::get('user_name'));
        //dump(Session::get('user_status'));
        //dump(Session::get('user_code'));

        //$status = $user->status;
        //Session::set('id',$user->id);
        //Session::set('user_status',$status);

        $act = ActivityModel::count();
        $sign = SignModel::count();
        $suces = SignModel::where('status',1)->count();
        $this->assign('a',$act);
        $this->assign('b',$sign);
        $this->assign('c',$suces);
        return $this->fetch('backstage/home');
    }

    /**
     * 登出操作
     */
    public function logout()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        Session::set('user_name',null);
        //casout();
        return $this->redirect('index/index/index');
    }

    /**
     * 活动管理界面渲染
     * @return mixed|void
     */
    public function activity()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }
        $status = $user->status;
        $school = $user->school;
        $grade = $user->grade;

        $list = ActivityModel::where('id','>',0)->order('id','desc')->paginate(10);
        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page', $page);
        $this->assign('status',$status);
        $this->assign('school',$school);
        $this->assign('grade',$grade);
        $this->assign('search',false);
        return $this->fetch('backstage/activity');
    }

    /**
     * 添加活动
     * @param Request $request
     */
    public function addactivity(Request $request)
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $title = input('post.title');
        $summary = input('post.summary');
        $body = input('post.body');
        $number = input('post.number');
        $school = input('post.school/a');
        $grade = input('post.grade/a');
        $school_count = sizeof($school);
        $grade_count = sizeof($grade);
        //dump($school);
        //dump($grade);
        if($title==null)
        {
            Session::set('title',$title);
            Session::set('summary',$summary);
            Session::set('body',$body);
            Session::set('number',$number);
            return $this->suces('标题为必填');
        }
        if($summary==null)
        {
            Session::set('title',$title);
            Session::set('summary',$summary);
            Session::set('body',$body);
            Session::set('number',$number);
            return $this->suces('简介为必填');
        }
        if($body==null)
        {
            Session::set('title',$title);
            Session::set('summary',$summary);
            Session::set('body',$body);
            Session::set('number',$number);
            return $this->suces('描述为必填');
        }
        if($school==null)
        {
            Session::set('title',$title);
            Session::set('summary',$summary);
            Session::set('body',$body);
            Session::set('number',$number);
            return $this->suces('面向学院为必填');
        }
        if($grade==null)
        {
            Session::set('title',$title);
            Session::set('summary',$summary);
            Session::set('body',$body);
            Session::set('number',$number);
            return $this->suces('面向年级为必填');
        }
        if($number==null)
        {
            Session::set('title',$title);
            Session::set('summary',$summary);
            Session::set('body',$body);
            Session::set('number',$number);
            return $this->suces('参与人数为必填');
        }
        if(!is_numeric($number))
        {
            Session::set('title',$title);
            Session::set('summary',$summary);
            Session::set('body',$body);
            Session::set('number',$number);
            return $this->suces('参与人数必须为数字');
        }
        $act = new ActivityModel();
        $act->title=$title;
        $act->summary=$summary;
        $act->body=$body;
        $act->number=$number;
        $file = $request->file('photo');
        if($file!=null)
        {
            if(true !== $this->validate(['image' => $file], ['image' => 'require|image']))
            {
                return $this->suces('请选择图像文件！');
            }
            $image = Image::open($file);
            // $image->thumb(105, 125, Image::THUMB_CENTER);
            $saveName = $request->time() . '.png';
            $image->save(ROOT_PATH . 'public/uploads/' . $saveName);
            $act->photo = $saveName;
        }

        $school_info = '';
        $grade_info = '';
        for($i=0;$i<$school_count;$i++)
        {
            if($school[$i]==-1)
            {
                $school_info = '所有学院';
                break;
            }
            else if($school[$i]==1)
            {
                $school_info = $school_info.'航空学院 ';
            }
            else if($school[$i]==14)
            {
                $school_info = $school_info.'软件与微电子学院 ';
            }
        }
        for($i=0;$i<$grade_count;$i++)
        {
            if($grade[$i]==-1)
            {
                $grade_info = '所有年级';
                break;
            }
            else if($grade[$i]==2016)
            {
                $grade_info = $grade_info.'2016级 ';
            }
            else if($grade[$i]==2015)
            {
                $grade_info = $grade_info.'2015级 ';
            }
            else if($grade[$i]==2014)
            {
                $grade_info = $grade_info.'2014级 ';
            }
            else if($grade[$i]==2013)
            {
                $grade_info = $grade_info.'2013级 ';
            }
        }

        $act->school = $school_info;
        $act->grade = $grade_info;
        $act->user_id = $user->id;
        $act->user_name = $user->name;
        $act->save();
        $act_id = $act->id;

        //dump($school_count);
        //dump($grade_count);
        if($school_count>=$grade_count)
        {
            //dump('左比右大');
            for($i=0;$i<$school_count;$i++)
            {
                for($j=0;$j<$grade_count;$j++)
                {
                    $navigate = new NavigateModel();
                    $navigate->activity_id = $act_id;
                    if((int)$school[$i]!=-1)
                    {
                        $navigate->school = $school[$i];
                    }
                    if((int)$grade[$j]!=-1)
                    {
                        $navigate->grade = $grade[$j];
                    }
                    $navigate->save();
                }
            }
        } else{
            //dump('右比左大');
            for($j=0;$j<$grade_count;$j++)
            {
                for($i=0;$i<$school_count;$i++)
                {
                    $navigate = new NavigateModel();
                    $navigate->activity_id = $act_id;
                    if((int)$school[$i]!=-1)
                    {
                        $navigate->school = $school[$i];
                    }
                    if((int)$grade[$j]!=-1)
                    {
                        $navigate->grade = $grade[$j];
                    }
                    $navigate->save();
                }
            }
        }
        Session::set('title',null);
        Session::set('summary',null);
        Session::set('body',null);
        Session::set('number',null);
        $this->adddiary('新增了一条活动信息，标题为 '.$act->title.'。');
        return $this->suces('活动添加成功');
    }

    /**
     * 活动高级检索
     * @param Request $request
     * @return mixed|void
     */
    public function searchactivity(Request $request)
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }
        $status = $user->status;
        $school = $user->school;
        $grade = $user->grade;

        $title = input('post.title');
        $summary = input('post.summary');
        $body = input('post.body');
        $number = input('post.number');
        $school_search = input('post.school');
        $grade_search = input('post.grade');
        $user_search = input('post.user');

        $list = ActivityModel::where('title','LIKE','%'.$title.'%')->where('summary','LIKE','%'.$summary.'%')
            ->where('body','LIKE','%'.$body.'%')->where('number','LIKE','%'.$number.'%')->where('user_name','LIKE','%'.$user_search.'%')
            ->order('id','desc')->paginate(10);
        $array = ActivityModel::where('title','LIKE','%'.$title.'%')->where('summary','LIKE','%'.$summary.'%')
            ->where('body','LIKE','%'.$body.'%')->where('number','LIKE','%'.$number.'%')->where('user_name','LIKE','%'.$user_search.'%')
            ->order('id','desc')->paginate()->toArray();

        $page = $list->render();
        //dump($array['data'][0]['summary']);
        Session::set('actarray',$array);
        //$this->assign('array',$array);
        $this->assign('list',$list);
        $this->assign('page', $page);
        $this->assign('status',$status);
        $this->assign('school',$school);
        $this->assign('grade',$grade);
        $this->assign('search',true);
        return $this->fetch('backstage/activity');
        //return $this->redirect('index/backstage/activity');
    }

    /**
     * 活动具体信息
     * @param string $id
     * @return mixed
     */
    public function activityinformation($id='')
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $act = ActivityModel::get($id);
        /**
         *

        $result = NavigateModel::where('activity_id',$act->id)->select();
        $school = '';
        $grade = '';
        $schoolarry = array();
        $gradearry = array();
        for($i = 0 , $cnt = sizeof($result) ; $i < $cnt ; $i++)
        {
            $navigate = $result[$i];
            $scl = $navigate->school;
            $gde = $navigate->grade;
            if($scl==null)
            {
                array_push($schoolarry,-1);
            } else if($scl==1)
            {
                if(!in_array($scl,$schoolarry))
                {
                    array_push($schoolarry,$scl);
                }
            } else if($scl==14)
            {
                if(!in_array($scl,$schoolarry))
                {
                    array_push($schoolarry,$scl);
                }
            }
            if($gde==null)
            {
                array_push($gradearry,-1);
            } else if($gde==2016)
            {
                if(!in_array($gde,$gradearry))
                {
                    array_push($gradearry,$gde);
                }
            } else if($gde==2015)
            {
                if(!in_array($gde,$gradearry))
                {
                    array_push($gradearry,$gde);
                }
            } else if($gde==2014)
            {
                if(!in_array($gde,$gradearry))
                {
                    array_push($gradearry,$gde);
                }
            } else if($gde==2013)
            {
                if(!in_array($gde,$gradearry))
                {
                    array_push($gradearry,$gde);
                }
            }
        }
        for($i = 0 , $cnt = sizeof($schoolarry) ; $i < $cnt ; $i++)
        {
            $item = $schoolarry[$i];
            if($item==-1)
            {
                $school='所有学院';
                break;
            } else if($item==1)
            {
                $school = $school.'航空学院 ';
            } else if($item==14)
            {
                $school = $school.'软件与微电子学院 ';
            }
        }
        for($i = 0 , $cnt = sizeof($gradearry) ; $i < $cnt ; $i++)
        {
            $item = $gradearry[$i];
            if($item==-1)
            {
                $grade='所有年级';
                break;
            } else if($item==2016)
            {
                $grade = $grade.'2016级 ';
            } else if($item==2015)
            {
                $grade = $grade.'2015级 ';
            } else if($item==2014)
            {
                $grade = $grade.'2014级 ';
            } else if($item==2013)
            {
                $grade = $grade.'2013级 ';
            }
        }
         * */

        $success = SignModel::where('activity_id',$id)->where('status',1)->order('id','desc')->paginate();
        $page2=$success->render();
        $list = SignModel::where('activity_id',$id)->order('id','desc')->paginate(10);
        $list_excel = SignModel::where('activity_id',$id)->order('id','desc')->paginate(0);
        Session::set('signarray_success',$success);
        Session::set('signarray',$list_excel);
        $page = $list->render();
        $this->assign('success', $success);
        $this->assign('page2', $page2);
        $this->assign('page', $page);
        $this->assign('list', $list);
        $this->assign('name', $act->title);
        $this->assign('activity', $act);
        return $this->fetch('backstage/activityinformation');
    }

    /**
     * 活动截止
     * @param string $id
     */
    public function successs($id='')
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $act = ActivityModel::get($id);
        if($act->user_id!=$user->id)
        {
            $this->adddiary('尝试操作id为 '.$id.' 标题为 '.$act->title.' 的活动。');
            return $this->suces('你无权操作该活动');
        }
        if($act->number>=$act->sign||$act->number==-1)
        {
            $list = SignModel::where('activity_id',$id)->select();
            for($i=0;$i<sizeof($list);$i++)
            {
                $list[$i]->status=1;
                $user_id=$list[$i]->user_id;
                $list[$i]->save();

                $user=UserModel::get($user_id);
                $user->success++;
                $class_code = $user->class;
                $user->save();

                $class = ClassesModel::where('code',$class_code)->find();
                $class->success++;
                $class->save();
            }

            $act->status=0;
            $act->save();
            $this->adddiary('截止了一条活动信息，标题为 '.$act->title.'。');
            return $this->suces('参与人员已经生成');
        }
        else {
            $list = SignModel::where('activity_id',$id)->select();
            shuffle($list);
            for($i=0;$i<$act->number;$i++)
            {
                $list[$i]->status=1;
                $user_id=$list[$i]->user_id;
                $list[$i]->save();

                $user=UserModel::get($user_id);
                $user->success++;
                $class_code = $user->class;
                $user->save();

                $class = ClassesModel::where('code',$class_code)->find();
                $class->success++;
                $class->save();
            }

            for($i=$act->number;$i<sizeof($list);$i++)
            {
                $list[$i]->status=-1;
                $list[$i]->save();
            }

            $act->status=0;
            $act->save();
            $this->adddiary('截止了一条活动信息，标题为 '.$act->title.'。');
            return $this->suces('参与人员已经生成');
        }
    }

    /**
     * 活动中止
     * @param string $id 活动id
     */
    public function ban($id='')
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $act = ActivityModel::get($id);
        if($act->user_id!=$user->id)
        {
            $this->adddiary('尝试操作id为 '.$id.' 标题为 '.$act->title.' 的活动。');
            return $this->suces('你无权操作该活动');
        }
        $act->status = -1;
        $act->save();
        $this->adddiary('中止了一条活动信息，标题为 '.$act->title.'。');
        return $this->suces('活动已中止');
    }

    /**
     * 活动恢复
     * @param string $id 活动id
     */
    public function disban($id='')
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $act = ActivityModel::get($id);
        if($act->user_id!=$user->id)
        {
            $this->adddiary('尝试操作id为 '.$id.' 标题为 '.$act->title.' 的活动。');
            return $this->suces('你无权操作该活动');
        }
        $act->status = 1;
        $act->save();
        $this->adddiary('恢复了一条活动信息，标题为 '.$act->title.'。');
        return $this->suces('活动已恢复');
    }

    /**
     * 班级界面渲染
     * @return mixed|void
     */
    public function classs()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $list = ClassesModel::where('id','>',0)->order('sign','desc')->paginate(10);
        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('search',false);
        return $this->fetch('backstage/class');
    }

    /**
     * 班级高级检索
     * @return mixed|void
     */
    public function searchclass()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $code = input('post.code');
        $school = input('post.school');
        $grade = input('post.grade');
        if($code==null)
        {
            if($school==0)
            {
                if($grade==0)
                {
                    $list = ClassesModel::where('id','>',0)->order('sign','desc')->paginate(10);
                    $array = ClassesModel::where('id','>',0)->order('sign','desc')->paginate();
                }
                else
                {
                    $list = ClassesModel::where('grade','LIKE','%'.$grade.'%')->order('sign','desc')->paginate(10);
                    $array = ClassesModel::where('grade','LIKE','%'.$grade.'%')->order('sign','desc')->paginate();
                }
            }
            else
            {
                if($grade==0)
                {
                    $list = ClassesModel::where('school',$school)->order('sign','desc')->paginate(10);
                    $array = ClassesModel::where('school',$school)->order('sign','desc')->paginate();
                }
                else
                {
                    $list = ClassesModel::where('school',$school)->where('grade',$grade)->order('sign','desc')->paginate(10);
                    $array = ClassesModel::where('school',$school)->where('grade',$grade)->order('sign','desc')->paginate();
                }
            }
        }
        else
        {
            if($school==0)
            {
                if($grade==0)
                {
                    $list = ClassesModel::where('code','LIKE','%'.$code.'%')->order('sign','desc')->paginate(10);
                    $array = ClassesModel::where('code','LIKE','%'.$code.'%')->order('sign','desc')->paginate();
                }
                else
                {
                    $list = ClassesModel::where('code','LIKE','%'.$code.'%')->where('grade',$grade)->order('sign','desc')->paginate(10);
                    $array = ClassesModel::where('code','LIKE','%'.$code.'%')->where('grade',$grade)->order('sign','desc')->paginate();
                }
            }
            else
            {
                if($grade==0)
                {
                    $list = ClassesModel::where('code','LIKE','%'.$code.'%')->where('school',$school)->order('sign','desc')->paginate(10);
                    $array = ClassesModel::where('code','LIKE','%'.$code.'%')->where('school',$school)->order('sign','desc')->paginate();
                }
                else
                {
                    $list = ClassesModel::where('code','LIKE','%'.$code.'%')->where('school',$school)->where('grade',$grade)->order('sign','desc')->paginate(10);
                    $array = ClassesModel::where('code','LIKE','%'.$code.'%')->where('school',$school)->where('grade',$grade)->order('sign','desc')->paginate();
                }
            }
        }
        $page = $list->render();
        Session::set('classarray',$array);
        $this->assign('list',$list);
        $this->assign('page',$page);
        $this->assign('search',true);
        return $this->fetch('backstage/class');
    }

    /**
     * 班级详细信息
     * @param string $id 班级id
     * @return mixed|void
     */
    public function classinformation($id='')
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $class = ClassesModel::where('code',$id)->find();
        //dump($class);
        $list=UserModel::where('class',$id)->order('sign','desc')->paginate(10);
        $array = UserModel::where('class',$id)->order('sign','desc')->paginate();
        Session::set('classinfoarray',$array);
        $page = $list->render();
        $this->assign('class',$class);
        $this->assign('list',$list);
        $this->assign('page', $page);
        $this->assign('code', $id);
        return $this->fetch('backstage/classinformation');
    }

    /**
     * 学生界面渲染
     * @return mixed|void
     */
    public function student()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $list=UserModel::where('class','>',0)->order('sign','desc')->paginate(10);
        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page', $page);
        $this->assign('search',false);
        return $this->fetch('backstage/student');
    }

    /**
     * 学生高级检索
     * @return mixed|void
     */
    public function searchstudent()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $code = input('post.code');
        $name = input('post.name');
        $school = input('post.school');
        $grade = input('post.grade');
        $class = input('post.class');
        if($school==0)
        {
            if($grade==0)
            {
                $list = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')->where('class','LIKE','%'.$class.'%')
                    ->order('sign','desc')->paginate(10);
                $array = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')->where('class','LIKE','%'.$class.'%')
                    ->order('sign','desc')->paginate();
            }
            else
            {
                $list = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')->where('class','LIKE','%'.$class.'%')
                    ->where('grade',$grade)->order('sign','desc')->paginate(10);
                $array = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')->where('class','LIKE','%'.$class.'%')
                    ->where('grade',$grade)->order('sign','desc')->paginate();
            }
        }
        else
        {
            if($grade==0)
            {
                $list = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')->where('class','LIKE','%'.$class.'%')
                    ->where('school',$school)->order('sign','desc')->paginate(10);
                $array = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')->where('class','LIKE','%'.$class.'%')
                    ->where('school',$school)->order('sign','desc')->paginate();
            }
            else
            {
                $list = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')->where('class','LIKE','%'.$class.'%')
                    ->where('grade',$grade)->where('school',$school)->order('sign','desc')->paginate(10);
                $array = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')->where('class','LIKE','%'.$class.'%')
                    ->where('grade',$grade)->where('school',$school)->order('sign','desc')->paginate();
            }
        }
        $page = $list->render();
        Session::set('studentarray',$array);
        $this->assign('list',$list);
        $this->assign('page', $page);
        $this->assign('search',true);
        return $this->fetch('backstage/student');
    }

    /**
     * 学生详细信息
     * @param string $id 学生id
     * @return mixed|void
     */
    public function studentinformation($id='')
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $user=UserModel::get($id);
        $list=SignModel::where('user_id',$id)->order('id','desc')->paginate(10);
        $array = SignModel::where('user_id',$id)->order('id','desc')->paginate();
        $page = $list->render();
        Session::set('studentinfoarray',$array);
        $this->assign('list',$list);
        $this->assign('page', $page);
        $this->assign('code', $user->name);
        $this->assign('user',$user);
        return $this->fetch('backstage/studentinformation');
    }

    /**
     * 操作日志界面渲染
     * @return mixed|void
     */
    public function diary()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge(3);
        if($user==false)
        {
            return $this->reject('查看操作日志');
        }

        $list = DiaryModel::where('id','>',0)->order('id','desc')->paginate(10);
        $page = $list->render();
        $this->assign('list',$list);
        $this->assign('page', $page);
        return $this->fetch('backstage/diary');
    }

    /**
     * 级负责人界面渲染
     * @return mixed|void
     */
    public function gradeadmin()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge(1);
        if($user==false)
        {
            return $this->reject('访问年级管理员管理');
        }

        $list = UserModel::where('status',1)->order('id','desc')->paginate(10);
        $page = $list->render();
        $this->assign('userstatus',$user->status);
        $this->assign('school',$user->school);
        //$this->assign('grade',$user->grade);
        $this->assign('list',$list);
        $this->assign('page', $page);
        $this->assign('search',false);
        $this->assign('status',1);
        $this->assign('type','级负责人管理');
        return $this->fetch('backstage/admin');
    }

    /**
     * 院负责人界面渲染
     * @return mixed|void
     */
    public function collegeadmin()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge(2);
        if($user==false)
        {
            return $this->reject('访问学院管理员管理');
        }

        $list = UserModel::where('status',2)->order('id','desc')->paginate(10);
        $page = $list->render();
        $this->assign('userstatus',$user->status);
        $this->assign('school',$user->school);
        $this->assign('list',$list);
        $this->assign('page', $page);
        $this->assign('search',false);
        $this->assign('status',2);
        $this->assign('type','院负责人管理');
        return $this->fetch('backstage/admin');
    }

    /**
     * 校负责人界面渲染
     * @return mixed|void
     */
    public function schooladmin()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge(3);
        if($user==false)
        {
            return $this->reject('访问学校管理员管理');
        }

        $list = UserModel::where('status',3)->order('id','desc')->paginate(10);
        $page = $list->render();
        $this->assign('userstatus',$user->status);
        $this->assign('school',$user->school);
        $this->assign('list',$list);
        $this->assign('page', $page);
        $this->assign('search',false);
        $this->assign('status',3);
        $this->assign('type','校负责人管理');
        return $this->fetch('backstage/admin');
    }

    /**
     * 负责人高级检索
     * @param string $id 检索负责人权限
     * @return mixed|void
     */
    public function searchadmin($id='')
    {
        if($id==1)
        {
            Session::start();
            //include('_cas.php');
            $user = $this->judge(1);
            if($user==false)
            {
                return $this->reject('访问年级管理员管理');
            }

            $code = input('post.code');
            $name = input('post.name');
            $school = input('post.school');
            $grade = input('post.grade');
            if($school==0)
            {
                if($grade==0)
                {
                    $list = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')->where('status',$id)->order('sign','desc')->paginate(10);
                }
                else
                {
                    $list = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')
                        ->where('grade',$grade)->where('status',$id)->order('id','desc')->paginate(10);
                }
            }
            else
            {
                if($grade==0)
                {
                    $list = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')
                        ->where('school',$school)->where('status',$id)->order('id','desc')->paginate(10);
                }
                else
                {
                    $list = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')
                        ->where('grade',$grade)->where('school',$school)->where('status',$id)->order('id','desc')->paginate(10);
                }
            }
            $page = $list->render();
            $this->assign('list',$list);
            $this->assign('page', $page);
            $this->assign('status',1);
            $this->assign('type','级负责人管理');
        }
        else if($id==2)
        {
            Session::start();
            //include('_cas.php');
            $user = $this->judge(2);
            if($user==false)
            {
                return $this->reject('访问学院管理员管理');
            }

            $code = input('post.code');
            $name = input('post.name');
            $school = input('post.school');
            if($school==0)
            {
                $list = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')->where('status',$id)->order('sign','desc')->paginate(10);
            }
            else
            {
                $list = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')
                    ->where('school',$school)->where('status',$id)->order('id','desc')->paginate(10);
            }
            $page = $list->render();
            $this->assign('list',$list);
            $this->assign('page', $page);
            $this->assign('status',2);
            $this->assign('type','院负责人管理');
        }
        else
        {
            Session::start();
            //include('_cas.php');
            $user = $this->judge(3);
            if($user==false)
            {
                return $this->reject('访问学校管理员管理');
            }

            $code = input('post.code');
            $name = input('post.name');
            $list = UserModel::where('code','LIKE','%'.$code.'%')->where('name','LIKE','%'.$name.'%')->where('status',$id)->order('sign','desc')->paginate(10);
            $page = $list->render();
            $this->assign('list',$list);
            $this->assign('page', $page);
            $this->assign('status',3);
            $this->assign('type','校负责人管理');
        }

        $this->assign('userstatus',$user->status);
        $this->assign('school',$user->school);
        $this->assign('search',true);
        return $this->fetch('backstage/admin');
    }

    /**
     * 添加负责人
     * @param string $id
     */
    public function addadmin($id='')
    {
        if($id==1)
        {
            Session::start();
            //include('_cas.php');
            $user = $this->judge(1);
            if($user==false)
            {
                return $this->reject('添加年级管理员');
            }

            $code = input('post.code');
            $name = input('post.name');
            $school = input('post.school');
            $grade = input('post.grade');
            if($name==null)
            {
                return $this->suces('姓名为必填');
            }
            if($code==null)
            {
                return $this->suces('学号/工号为必填');
            }
            if($school==null)
            {
                return $this->suces('学院为必填');
            }
            if($grade==null)
            {
                return $this->suces('年级为必填');
            }

            $add_status = $user->status;
            $user = UserModel::where('code',$code)->find();
            if($user==null)
            {
                $user = new UserModel();
                $user->name = $name;
                $user->code = $code;
                $user->school = $school;
                $user->grade = $grade;
                $user->status = $id;
                $user->save();
            }
            else if($name!=$user->name||$school!=$user->school||$grade!=$user->grade)
            {
                return $this->suces('信息错误');
            }
            else if($user->status>=$add_status)
            {
                return $this->suces('你没有操作该用户的权限');
            }
            else
            {
                $user->status = $id;
                $user->save();
            }
            $this->adddiary('设置学号/工号为 '.$user->code.' 的用户为级管理员。');
        }
        else if($id==2)
        {
            Session::start();
            //include('_cas.php');
            $user = $this->judge(2);
            if($user==false)
            {
                return $this->reject('添加学院管理员');
            }

            $code = input('post.code');
            $name = input('post.name');
            $school = input('post.school');
            if($name==null)
            {
                return $this->suces('姓名为必填');
            }
            if($code==null)
            {
                return $this->suces('学号/工号为必填');
            }
            if($school==null)
            {
                return $this->suces('学院为必填');
            }

            $add_status = $user->status;
            $user = UserModel::where('code',$code)->find();
            if($user==null)
            {
                $user = new UserModel();
                $user->name = $name;
                $user->code = $code;
                $user->school = $school;
                $user->status = $id;
                $user->save();
            }
            else if($name!=$user->name||$school!=$user->school)
            {
                return $this->suces('信息错误');
            }
            else if($user->status>=$add_status)
            {
                return $this->suces('你没有操作该用户的权限');
            }
            else
            {
                $user->status = $id;
                $user->save();
            }
            $this->adddiary('设置学号/工号为 '.$user->code.' 的用户为院管理员。');
        }
        else
        {
            Session::start();
            //include('_cas.php');
            $user = $this->judge(3);
            if($user==false)
            {
                return $this->reject('添加学校管理员');
            }

            $code = input('post.code');
            $name = input('post.name');
            if($name==null)
            {
                return $this->suces('姓名为必填');
            }
            if($code==null)
            {
                return $this->suces('学号/工号为必填');
            }

            $add_status = $user->status;
            $user = UserModel::where('code',$code)->find();
            if($user==null)
            {
                $user = new UserModel();
                $user->name = $name;
                $user->code = $code;
                $user->status = $id;
                $user->save();

            }
            else if($name!=$user->name)
            {
                return $this->suces('信息错误');
            }
            else if($user->status>=$add_status)
            {
                return $this->suces('你没有操作该用户的权限');
            }
            else
            {
                $user->status = $id;
                $user->save();
            }
            $this->adddiary('设置学号/工号为 '.$user->code.' 的用户为校管理员。');
        }
        return $this->suces('添加成功');
    }

    /**
     * 撤销负责人
     * @param string $id
     */
    public function banadmin($id='')
    {
        Session::start();
        //include('_cas.php');
        $admin = UserModel::get($id);
        $user = $this->judge($admin->status);
        if($user==false)
        {
            return $this->reject('撤销学号/工号为 '.$admin->code.' 的用户');
        }

        $admin->status = 0;
        $admin->save();
        $this->adddiary('撤销了学号/工号为 '.$admin->code.' 的用户权限。');
        return $this->suces('撤销成功');
    }

    /**
     * 将活动搜索结果导出至excel
     */
    public function actexcel()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $array = Session::get('actarray');
        vendor('PHPExcel.PHPExcel');
        $excel = new \PHPExcel();

        $excel->getProperties()->setCreator('卢鹏宇')
        ->setTitle('活动记录');

        $write = new \PHPExcel_Writer_Excel5($excel);

        $excel->getActiveSheet()->setCellValue('A1','活动编号');
        $excel->getActiveSheet()->setCellValue('B1','活动名称');
        $excel->getActiveSheet()->setCellValue('C1','活动简介');
        $excel->getActiveSheet()->setCellValue('D1','详细描述');
        $excel->getActiveSheet()->setCellValue('E1','面向学院');
        $excel->getActiveSheet()->setCellValue('F1','面向年级');
        $excel->getActiveSheet()->setCellValue('G1','报名人数');
        $excel->getActiveSheet()->setCellValue('H1','活动人数');
        $excel->getActiveSheet()->setCellValue('I1','活动状态');
        $excel->getActiveSheet()->setCellValue('J1','发布时间');
        $excel->getActiveSheet()->setCellValue('K1','浏览次数');
        $excel->getActiveSheet()->setCellValue('L1','发布人');

        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
        $excel->getActiveSheet()->getColumnDimension('K')->setWidth(10);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('L')->setWidth(10);

        for($i=0,$cnt = sizeof($array['data']);$i<$cnt;$i++)
        {
            $sum = $i + 2;
            $item = $array['data'][$i];
            $excel->getActiveSheet()->setCellValue('A'.$sum,  $item['id']);
            $excel->getActiveSheet()->getStyle('A'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->getActiveSheet()->setCellValue('B'.$sum,  $item['title']);
            $excel->getActiveSheet()->getStyle('B'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->getActiveSheet()->setCellValue('C'.$sum,  $item['summary']);
            $excel->getActiveSheet()->getStyle('C'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->getActiveSheet()->setCellValue('D'.$sum,  $item['body']);
            $excel->getActiveSheet()->getStyle('D'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->getActiveSheet()->setCellValue('E'.$sum,  $item['school']);
            $excel->getActiveSheet()->setCellValue('F'.$sum,  $item['grade']);
            $excel->getActiveSheet()->setCellValue('G'.$sum,  $item['sign']);
            $excel->getActiveSheet()->getStyle('G'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            if($array['data'][$i]['number']==-1)
            {
                $excel->getActiveSheet()->setCellValue('H'.$sum,'无人数限制');
            }
            else
            {
                $excel->getActiveSheet()->setCellValue('H'.$sum,  $item['number']);
                $excel->getActiveSheet()->getStyle('H'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            }
            if($array['data'][$i]['status']==1)
            {
                $excel->getActiveSheet()->setCellValue('I'.$sum,  '报名中');
            } else if($array['data'][$i]['status']==0)
            {
                $excel->getActiveSheet()->setCellValue('I'.$sum,  '已截止');
            } else {
                $excel->getActiveSheet()->setCellValue('I'.$sum,  '已中止');
            }
            $excel->getActiveSheet()->setCellValue('J'.$sum,  $item['time']);
            $excel->getActiveSheet()->getStyle('J'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->getActiveSheet()->setCellValue('K'.$sum,  $item['see']);
            $excel->getActiveSheet()->getStyle('K'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->getActiveSheet()->setCellValue('L'.$sum,  $item['user_name']);
            $excel->getActiveSheet()->getStyle('L'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="活动记录.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    /**
     * 将指定活动的详细信息导出至excel
     * @param string $id 活动id
     */
    public function signexcel($id='')
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $act = ActivityModel::get($id);
        $array1 = Session::get('signarray');
        $array2 = Session::get('signarray_success');
        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator('卢鹏宇')
            ->setTitle('活动信息');

        $write = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('活动信息');

        $objPHPExcel->getActiveSheet()->setCellValue('A1', '活动名称');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', $act->title);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', '活动简述');
        $objPHPExcel->getActiveSheet()->setCellValue('B2', $act->summary);
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->setCellValue('A3', '详细描述');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', $act->body);
        $objPHPExcel->getActiveSheet()->getStyle('B3')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->setCellValue('A4', '面向学院');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', $act->school);

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '面向年级');
        $objPHPExcel->getActiveSheet()->setCellValue('B5', $act->grade);

        $objPHPExcel->getActiveSheet()->setCellValue('A6', '活动人数');
        if($act->number!=-1)
        {
            $objPHPExcel->getActiveSheet()->setCellValue('B6', $act->number);
            $objPHPExcel->getActiveSheet()->getStyle('B6')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }
        else $objPHPExcel->getActiveSheet()->setCellValue('B6', '人数无限制');

        $objPHPExcel->getActiveSheet()->setCellValue('A7', '报名人数');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', $act->sign);
        $objPHPExcel->getActiveSheet()->getStyle('B7')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->setCellValue('A8', '发布时间');
        $objPHPExcel->getActiveSheet()->setCellValue('B8', $act->time);
        $objPHPExcel->getActiveSheet()->getStyle('B8')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->setCellValue('A9', '状态');
        if($act->status==1)
        {
            $objPHPExcel->getActiveSheet()->setCellValue('B9', '报名中');
        } else if ($act->status==0)
        {
            $objPHPExcel->getActiveSheet()->setCellValue('B9', '已截止');
        } else
        {
            $objPHPExcel->getActiveSheet()->setCellValue('B9', '已中止');
        }

        $objPHPExcel->getActiveSheet()->setCellValue('A10', '发布人');
        $objPHPExcel->getActiveSheet()->setCellValue('B10', $act->user_name);
        $objPHPExcel->getActiveSheet()->getStyle('B10')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);

        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle('报名列表');

        $objPHPExcel->getActiveSheet()->setCellValue('A1', '姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '学号');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '学院');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '年级');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '班级');

        for($i=0,$cnt = sizeof($array1);$i<$cnt;$i++)
        {
            $item = $array1[$i]->user;
            $sum = $i + 2;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$sum,  $item->name);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$sum,  $item->code);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            if($item->school==1)
            {
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$sum,'航空学院');
            }
            else if($item->school==14)
            {
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$sum,'软件与微电子学院');
            }
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$sum,  $item->grade.'级');
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$sum,  $item->class);
            $objPHPExcel->getActiveSheet()->getStyle('E'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);

        if($act->status==0)
        {
            $objPHPExcel->createSheet();
            $objPHPExcel->setActiveSheetIndex(2);
            $objPHPExcel->getActiveSheet()->setTitle('参与人员列表');

            $objPHPExcel->getActiveSheet()->setCellValue('A1', '姓名');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', '学号');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', '学院');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', '年级');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', '班级');
            for($i=0,$cnt = sizeof($array2);$i<$cnt;$i++)
            {
                $item = $array2[$i]->user;
                $sum = $i + 2;
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$sum,  $item->name);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$sum,  $item->code);
                $objPHPExcel->getActiveSheet()->getStyle('B'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                if($item->school==1)
                {
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$sum,'航空学院');
                }
                else if($item->school==14)
                {
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$sum,'软件与微电子学院');
                }
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$sum,  $item->grade.'级');
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$sum,  $item->class);
                $objPHPExcel->getActiveSheet()->getStyle('E'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="活动'.$act->title.'.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    /**
     * 将班级搜索结果导出至excel
     */
    public function classexcel()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $array = Session::get('classarray');
        vendor('PHPExcel.PHPExcel');
        $excel = new \PHPExcel();

        $excel->getProperties()->setCreator('卢鹏宇')
            ->setTitle('班级记录');
        $write = new \PHPExcel_Writer_Excel5($excel);

        $excel->getActiveSheet()->setCellValue('A1','班级编号');
        $excel->getActiveSheet()->setCellValue('B1','班号');
        $excel->getActiveSheet()->setCellValue('C1','所属学院');
        $excel->getActiveSheet()->setCellValue('D1','所属年级');
        $excel->getActiveSheet()->setCellValue('E1','报名总人次');
        $excel->getActiveSheet()->setCellValue('F1','选中总人次');

        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);

        for($i = 0,$cnt=sizeof($array);$i<$cnt;$i++)
        {
            $sum = $i + 2;
            $item = $array[$i];
            $excel->getActiveSheet()->setCellValue('A'.$sum,  $item->id);
            $excel->getActiveSheet()->getStyle('A'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->getActiveSheet()->setCellValue('B'.$sum,  $item->code);
            $excel->getActiveSheet()->getStyle('B'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            if($item->school==1)
            {
                $excel->getActiveSheet()->setCellValue('C'.$sum,  '航空学院');
            }
            else if($item->school==14)
            {
                $excel->getActiveSheet()->setCellValue('C'.$sum,  '软件与微电子学院');
            }
            $excel->getActiveSheet()->setCellValue('D'.$sum,  $item->grade.'级');
            $excel->getActiveSheet()->setCellValue('E'.$sum,  $item->sign);
            $excel->getActiveSheet()->getStyle('E'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->getActiveSheet()->setCellValue('F'.$sum,  $item->success);
            $excel->getActiveSheet()->getStyle('F'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="班级记录.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    /**
     * 将指定活动的详细信息导出至excel
     * @param string $id
     */
    public function classinfoexcel($id='')
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $class = ClassesModel::get($id);
        $array = Session::get('classinfoarray');

        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator('卢鹏宇')
            ->setTitle('班级信息');

        $write = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('班级信息');

        $objPHPExcel->getActiveSheet()->setCellValue('A1', '班号');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', $class->code);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->setCellValue('A2', '所属学院');
        if($class->school==1)
        {
            $objPHPExcel->getActiveSheet()->setCellValue('B2','航空学院');
        }
        else if($class->school==14)
        {
            $objPHPExcel->getActiveSheet()->setCellValue('B2', '软件与微电子学院');
        }


        $objPHPExcel->getActiveSheet()->setCellValue('A3', '所属年级');
        $objPHPExcel->getActiveSheet()->setCellValue('B3', $class->grade.'级');

        $objPHPExcel->getActiveSheet()->setCellValue('A4', '报名总人次');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', $class->sign);
        $objPHPExcel->getActiveSheet()->getStyle('B4')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '选中总人次');
        $objPHPExcel->getActiveSheet()->setCellValue('B5', $class->success);
        $objPHPExcel->getActiveSheet()->getStyle('B5')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);

        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle('班级学生列表');

        $objPHPExcel->getActiveSheet()->setCellValue('A1', '姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '学号');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '报名次数');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '选中次数');

        for($i=0,$cnt = sizeof($array);$i<$cnt;$i++)
        {
            $item = $array[$i];
            $sum = $i + 2;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$sum,  $item->name);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$sum,  $item->code);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$sum,  $item->sign);
            $objPHPExcel->getActiveSheet()->getStyle('C'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$sum,  $item->success);
            $objPHPExcel->getActiveSheet()->getStyle('D'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="班级'.$class->code.'.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    /**
     * 将学生搜索结果导出至excel
     */
    public function studentexcel()
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $array = Session::get('studentarray');
        vendor('PHPExcel.PHPExcel');
        $excel = new \PHPExcel();

        $excel->getProperties()->setCreator('卢鹏宇')
            ->setTitle('学生记录');
        $write = new \PHPExcel_Writer_Excel5($excel);

        $excel->getActiveSheet()->setCellValue('A1','学生编号');
        $excel->getActiveSheet()->setCellValue('B1','姓名');
        $excel->getActiveSheet()->setCellValue('C1','学号');
        $excel->getActiveSheet()->setCellValue('D1','学院');
        $excel->getActiveSheet()->setCellValue('E1','年级');
        $excel->getActiveSheet()->setCellValue('F1','班级');
        $excel->getActiveSheet()->setCellValue('G1','报名次数');
        $excel->getActiveSheet()->setCellValue('H1','选中次数');

        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);

        for($i = 0,$cnt=sizeof($array);$i<$cnt;$i++)
        {
            $sum = $i + 2;
            $item = $array[$i];
            $excel->getActiveSheet()->setCellValue('A'.$sum,  $item->id);
            $excel->getActiveSheet()->getStyle('A'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->getActiveSheet()->setCellValue('B'.$sum,  $item->name);
            $excel->getActiveSheet()->getStyle('B'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->getActiveSheet()->setCellValue('C'.$sum,  $item->code);
            $excel->getActiveSheet()->getStyle('C'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            if($item->school==1)
            {
                $excel->getActiveSheet()->setCellValue('D'.$sum,  '航空学院');
            }
            else if($item->school==14)
            {
                $excel->getActiveSheet()->setCellValue('D'.$sum,  '软件与微电子学院');
            }
            $excel->getActiveSheet()->setCellValue('E'.$sum,  $item->grade.'级');
            $excel->getActiveSheet()->setCellValue('F'.$sum,  $item->class);
            $excel->getActiveSheet()->getStyle('F'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->getActiveSheet()->setCellValue('G'.$sum,  $item->sign);
            $excel->getActiveSheet()->getStyle('G'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $excel->getActiveSheet()->setCellValue('H'.$sum,  $item->success);
            $excel->getActiveSheet()->getStyle('H'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="学生记录.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

    /**
     * 将指定学生的详细信息导出至excel
     * @param string $id
     */
    public function studentinfoexcel($id='')
    {
        Session::start();
        //include('_cas.php');
        $user = $this->judge();
        if($user==false)
        {
            return $this->reject();
        }

        $user = UserModel::get($id);
        $array = Session::get('studentinfoarray');

        vendor('PHPExcel.PHPExcel');
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties()->setCreator('卢鹏宇')
            ->setTitle('学生信息');

        $write = new \PHPExcel_Writer_Excel5($objPHPExcel);

        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('学生信息');

        $objPHPExcel->getActiveSheet()->setCellValue('A1', '姓名');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', $user->name);
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


        $objPHPExcel->getActiveSheet()->setCellValue('A2', '学号');
        $objPHPExcel->getActiveSheet()->setCellValue('B2', $user->code);
        $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->setCellValue('A3', '学院');
        if($user->school==1)
        {
            $objPHPExcel->getActiveSheet()->setCellValue('B3','航空学院');
        }
        else if($user->school==14)
        {
            $objPHPExcel->getActiveSheet()->setCellValue('B3', '软件与微电子学院');
        }


        $objPHPExcel->getActiveSheet()->setCellValue('A4', '年级');
        $objPHPExcel->getActiveSheet()->setCellValue('B4', $user->grade.'级');

        $objPHPExcel->getActiveSheet()->setCellValue('A5', '班级');
        $objPHPExcel->getActiveSheet()->setCellValue('B5', $user->class);
        $objPHPExcel->getActiveSheet()->getStyle('B5')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->setCellValue('A6', '报名次数');
        $objPHPExcel->getActiveSheet()->setCellValue('B6', $user->sign);
        $objPHPExcel->getActiveSheet()->getStyle('B6')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->setCellValue('A7', '选中次数');
        $objPHPExcel->getActiveSheet()->setCellValue('B7', $user->success);
        $objPHPExcel->getActiveSheet()->getStyle('B7')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);

        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->setTitle('报名活动列表');

        $objPHPExcel->getActiveSheet()->setCellValue('A1', '活动名称');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '活动简述');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '活动状态');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '报名状态');

        for($i=0,$cnt = sizeof($array);$i<$cnt;$i++)
        {
            $item = $array[$i]->activity;
            $sum = $i + 2;
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$sum,  $item->title);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$sum,  $item->summary);
            $objPHPExcel->getActiveSheet()->getStyle('B'.$sum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            if($item->status==1)
            {
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$sum, '报名中');
            }
            else if($item->status==0)
            {
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$sum,'已截止');
            }
            else if($item->status==-1)
            {
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$sum,'已中止');
            }

            if($array[$i]->status==0)
            {
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$sum, '已报名');
            }
            else if($array[$i]->status==1)
            {
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$sum, '已选中');
            }
            else if($array[$i]->status==-1)
            {
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$sum, '未选中');
            }

        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);

        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="学生'.$user->name.'.xls"');
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }
}