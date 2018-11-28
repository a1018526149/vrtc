<?php
/**
 * 商品页
 * @author 李财 2018/11/28
 */
namespace app\index\controller;

use \think\Db;
class Product extends Index
{
    function index(){
        $id=$this->request->param('id');
        $info=Db::name('product')->where('id',$id)->find();
        $this->assign('info',$info);
        return $this->fetch();
    }

}