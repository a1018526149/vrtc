<?php
/**
 * 用户中心
 * @author 李财 2018/11/28
 */
namespace app\index\controller;

use \think\Db;
use \think\Request;
use \think\Session;

class User extends Index
{   
    /**
     * 会员中心主页
     */
    function index(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        $this->assign('member',$member);
        return $this->fetch();
    }
    /**
     * 修改资料
     */
    function userEdit(){
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $id=Session::get('member')['id'];
            $data=$this->request->post();
            dump($data);
        }
        // return json($this->returnMessage);
    }
}