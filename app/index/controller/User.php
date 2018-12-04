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
        // 通过Session获取用户信息
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
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
    // 二维码
    function qrCode(){
        $number=Session::get('member')['user_number'];
        $pos_l='1';
        $pos_r='2';
        $qrcode1="../../".createqrcode($number,$pos_l);
        $qrcode2="../../".createqrcode($number,$pos_r);
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $this->assign('member',$member);
        $this->assign('number',$number);
        $this->assign('qrcode1',$qrcode1);
        $this->assign('qrcode2',$qrcode2);
        return $this->fetch();
    }
    // 购买Key值
    function buyKey(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $this->assign('member',$member);
        return $this->fetch();
    }
    // 公司公告
    function notice(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $this->assign('member',$member);
        // 公告文章
        $article=Db::name('article')->order('id desc')->paginate(15);
        $this->assign('article',$article);
        return $this->fetch();
    }
    // 激活会员
    function activation(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $users=Db::name('member')->field('user_name,user_number,id,status')->where('refree',$member['user_number'])->paginate(15);
        $this->assign('users',$users);
        $this->assign('member',$member);
        return $this->fetch();
    }
    // 激活会员操作接口
    function activing(){
        if(!Request::instance()->isAjax()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $id=$this->request->post('id');
            if(Db::name('member')->where('id',$id)->update(['status'=>1])==1){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='激活成功';
            }else{
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='激活失败，请重试！';
            }
        }
        return json($this->returnMessage);
    }
    // 奖金明细
    function detailed(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $this->assign('member',$member);
        return $this->fetch();
    }
    // 转账记录
    function transferAcc(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $this->assign('member',$member);
        return $this->fetch();
    }
}