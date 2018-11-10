<?php
namespace app\index\controller;

use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\Session;

class Index extends Controller
{
    //前置方法，防止直接登陆
    protected $beforeActionList = [
        'goSession' =>  ['except'=>'login,login_all'],    //tp前置方法，不管执行那个方法，都要先执行gosession ， 除了login,login_all方法
    ];

    //定义前置控制器
    public function goSession()
    {
        $id=Session::get('id');
        if(!$id)
        {
            $this->error('请先登录','login');
        }
    }

    //用户管理首页， 登录成功后的页面
    public function index()
    {
        $data= Db::name("user")->select();
        $this->assign("data",$data);
        return $this->fetch('index');
    }

    //添加用户页面
    public function add(){
        return $this->fetch('add');
    }

    //添加用户方法
    public function add_all(){
        $name = input("post.name");
        $password = input("post.password");
        $db = db('user');
        if(empty($name)){
            $this->error("用户名不能为空！");
        }
        if(empty($password)){
            $this->error("密码不能为空！");
        }
        if($db->where('name',$name)->find()){
            $this->error('用户名已存在，请换个用户名');
        }
        $result = $db->insert(['name'=>$name,'password'=>$password]);
        if($result){
            $this->success("添加成功!",'/index');
        }else {
            $this->error('添加失败!','/index');
        }
    }

    //登陆页面
    public function login(){
        return $this->fetch('login');
    }

    //登陆成功页面
    public function login_all(){
        $db = db('user');
        $name = input('post.name');
        $password = input('post.password');
        $yzm = input('post.yzm');
        // 检测输入的验证码是否正确，$value为用户输入的验证码字符串
        $captcha = new Captcha();
        if( !$captcha->check($yzm))
        {
           $this->error("验证码输入错误");
        }
        // 查询数据
        $list = $db->where(['name'=>$name,'password'=>$password])->find();

        //如果存在就存入session，并且跳转首页
        if($list)
        {
            Session::set('name',$name);
            Session::set('id',$list['id']);

            $db->where(['name'=>$name,'password'=>$password])->update(['loginTime'=>time()]);
            $this->redirect("/index");
        }else {
            $this->error('登录失败','login');
        }
    }

    //退出登陆
    public function login_out(){
        session::clear();
        $this->success('退出成功','login');
    }
    //修改页面
    public function update($id){
        $db = db('user');
        $data = $db->where(['id'=>$id])->find();
        return $this->fetch('update',['data'=>$data]);
    }

    //修改方法
    public function update_all($id){
        $name = input('post.name');
        $password = input ('post.password');
        if(empty($name)){
            $this->error("用户名不能为空！");
        }
        if(empty($password)){
            $this->error('密码不能为空');
        }
      /*  $id = input('post.id');*/
        $db = db('user');
        $data = $db->where(['id'=>$id])->find();
        if($password==$data['password']){
            $this->error("不能输入旧密码！");
        }
        $result = $db->where(['id'=>$id])->update(['name'=>$name,'password'=>$password]);
        if($result){
            $this->success('修改成功！','/index');
        }else{
            $this->error('修改失败');
        }
    }

    //删除
    public function delete($id){
        $db = db("user");
        $db->where(['id'=>$id])->delete();
        $this->redirect('/index');
    }
}

