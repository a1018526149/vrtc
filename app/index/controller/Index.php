<?php
/**
 * 主页
 * @author 李财 2018/11/28
 */
namespace app\index\controller;

use \think\Controller;
use \think\Db;
use \think\Session;
use \think\Request;
class Index extends Controller
{   
    /**
     * 检测用户是否登录
     * 未登录状态下跳转至登录页面
     */
    protected function _initialize(){
        // $member=Session::get('member');
        if(!Session::has('member')){
            $this->redirect('index/login/index');
        }
    }
    // 商城首页
    public function index()
    {   
        $product=Db::name('product')->limit(8)->select();
        $cateid=$this->request->param('cate');
        if($cateid){
            $product=Db::name('product')->where('cateid',$cateid)->select();
        }
        $cate=Db::name('product_cate')->select();
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $this->assign('member',$member);
        $this->assign('cate',$cate);
        $this->assign('product',$product);
        return $this->fetch();
    }

    // 商品详情页
    function proView(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $this->assign('member',$member);
        $pid=$this->request->param('id');
        $product=Db::name('product')->where('id',$pid)->find();
        $this->assign('product',$product);
        return $this->fetch();
    }
    // 分类商品页
    function cate(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $this->assign('member',$member);
        $cid=$this->request->param('id');
        $product=Db::name('product')->where('cateid',$cid)->select();
        $this->assign('product',$product);
        $cate=Db::name('product_cate')->where('id',$cid)->find();
        $this->assign('cate',$cate);
        return $this->fetch();
    }
}
