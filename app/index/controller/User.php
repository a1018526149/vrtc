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
    function index(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        $this->assign('member',$member);
        return $this->fetch();
    }
}