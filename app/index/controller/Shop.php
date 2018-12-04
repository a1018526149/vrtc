<?php
/**
 * 商城
 * @author 李财 2018/12/3
 */
namespace app\index\controller;
use \think\Session;
use \think\Db;
class Shop extends Index
{
    // 商城主页
    function index(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        $this->assign('member',$member);
        return $this->fetch();
    }
}