<?php
/**
 * 登录以及忘记密码
 * @author 李财 2018/11/28
 */
namespace app\index\controller;

use \think\Db;
use \think\Request;
use \think\Session;
use \think\Controller;
class Login extends Controller
{
    function Index(){
        return $this->fetch();
    }
    function Login(){
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求!';
        }else{
            $name=$this->request->post('name');
            $pwd=md5(md5($this->request->post('pwd')));
            if(empty($pwd)||empty($name)){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='账号密码不能为空！';
            }else{
                $member=Db::name('member')
                ->where(['user_number|mobile'=>$name,'user_password'=>$pwd])
                ->find();
                if(empty($member)){
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='账号或密码不正确！';
                }else{
                    unset($member['user_password']);
                    Session::set('member',$member);
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='登录成功';
                }
            }
        }

        return json($this->returnMessage);
    }
    /**
     * 忘记密码
     */
    function forgetPassword(){
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $name=$this->request->post('name');
            $pwd=md5(md5($this->request->post('pwd')));
            if(empty($pwd)||empty($name)){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='账号密码不能为空！';
            }else{
                if(Db::name('member')->where('user_number|mobile',$name)->update(['user_password'=>$pwd])!==1){
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='修改密码失败！';
                }else{
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='修改成功';
                }
            }
        }
        return json($this->returnMessage);
    }


}