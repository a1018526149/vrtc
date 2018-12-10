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
        $sheng=Db::name('city')->where('type',1)->select();//省级地区数据
        $area=Db::name('area')->where('user_id',$id)->find();
        $address=explode("|",$area['address']);
        $this->assign('address',$address);
        $this->assign('area',$area);
        $this->assign('sheng',$sheng);
        $this->assign('member',$member);
        return $this->fetch();
    }
    // 会员信息
    function info(){
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
            $updateData=[
                'user_name'=>$data['name'],
                'mobile'=>$data['mobile'],
                'birthday'=>$data['birthday'],
                'sex'=>$data['sex']
            ];
            if(Db::name('member')->where('id',$id)->update($updateData)){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='修改资料成功';
            }else{
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='修改失败';
            }
        }
        return json($this->returnMessage);
    }
    /**
     * 修改密码
     */
    function editPassword(){
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $id=Session::get('member')['id'];
            $data['old_password']=md5(md5($this->request->post('old_password')));
            $data['confirm_password']=md5(md5($this->request->post('confirm_password')));
            $member=Db::name('member')->where('id',$id)->find();
            if($member['user_password']!==$data['old_password']){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='原始密码不正确';
            }else{
                if(Db::name('member')->where('id',$id)->update(['user_password'=>$data['confirm_password']])==1){
                    Session::delete('member');
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='修改密码成功';
                }else{
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='修改失败，请重试';
                }
            }
        }
        return json($this->returnMessage);
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
        $setting=Db::name('setting')->field('recommend,intojj')->where('id',2)->find();
        $coupon=Db::name('coupon')->where('username',$member['user_number'])->paginate(10);
        $this->assign('coupon',$coupon);
        unset($member['user_password']);
        $this->assign('setting',$setting);
        $this->assign('member',$member);
        return $this->fetch();
    }
    // 购买Key值API
    function buyKApi(){
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $id=Session::get('member')['id'];
            $member=Db::name('member')->where('id',$id)->find();
            $data=$this->request->post();
            $set=Db::name('setting')->field("intojj,intocx,tax,fhrate,recommend")->where('id',2)->find();
            if(md5(md5($data['pay_password']))!=$member['pay_password']){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='支付密码错误!';
            }else{
                $time=time();
                $insertData=[
                    'username'=>$member['user_number'],
                    'realname'=>$member['user_name'],
                    'buyprice'=>$set['intocx']*$data['number'],
                    'create_time'=>$time,
                    'dayprice'=>$set['fhrate'],
                    'daynum'=>$set['tax'],
                    'jstime'=>$time+(86400*30)
                ];
                
                if($member['bonus']<$insertData['buyprice']){
                    $this->returnMessage['code']='error';
                    $this->reutrnMessage['message']='金币不足';
                    return json($this->returnMessage);die;
                }
                if($set['recommend']<1){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='当前K值不足，请等待排队';
                    return json($this->returnMessage);die;
                }
                if(Db::name('coupon')->insert($insertData)==1&&Db::name('setting')->where('id',2)->update(['recommend'=>$set['recommend']-1])==1&&Db::name('member')->where('id',$id)->update(['bonus'=>$member['bonus']-$insertData['buyprice']])==1){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='购买成功';
                }else{
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='购买失败，请重试';
                }
            }
        }
        return json($this->returnMessage);
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
    // 激活会员----操作接口
    function activing(){
        if(!Request::instance()->isAjax()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $uid=Session::get('member')['id'];
            $member=Db::name('member')->field('zbonus')->where('id',$uid)->find();
            if($member['zbonus']<1000){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='钻石不足，无法激活';
            }else{
                $id=$this->request->post('id');
                if(Db::name('member')->where('id',$id)->update(['status'=>1])==1&&Db::name('member')->where('id',$uid)->update(['zbonus'=>$member['zbonus']-1000])==1){
                    bonusIncrease();
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='激活成功';
                }else{
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='激活失败，请重试！';
                }
            }
        }
        return json($this->returnMessage);
    }
    // 奖金明细
    function detailed(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $detailed=Db::name('e')->where('username',$member['user_number'])->paginate(15);
        $this->assign('detailed',$detailed);
        $this->assign('member',$member);
        return $this->fetch();
    }
    // 转账记录
    function transferAcc(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $tranfers=Db::name('tranfer')->where('username',$member['user_number'])->paginate(15);
        $this->assign('tranfers',$tranfers);
        $this->assign('member',$member);
        return $this->fetch();
    }
    // 退出登录
    function outLogin(){
        if(!Request::instance()->isAjax()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            Session::delete('member');
            $this->returnMessage['code']='success';
            $this->returnMessage['message']='退出成功';
        }
        return json($this->returnMessage);
    }
    // 钱包
    function wallet(){
      return $this->info();
    }
    // 币币转换
    function transformation(){
      return $this->info();
    }
    // 三级图
    function sjt(){
        $num=number(1);
        $this->assign('num',$num);
        return $this->info();
    }
   // 获取市
    function getCity(){
        $city_id=$this->request->param('city_id');
        $city=Db::name('city')->where(['type'=>2,'pid'=>$city_id])->select();
        return json($city);
    }
    // 获取县
    function getXian(){
        $xian_id=$this->request->param('xian_id');
        $area=Db::name('city')->where(['type'=>3,'pid'=>$xian_id])->select();
        return json($area);
    }
    // 获取地址
    function getArea($address){
        $tempadd=explode("|",$address);
        $sheng=$tempadd[0];
        $shi=$tempadd[1];
        $xian=$tempadd[2];
        $shengarea=Db::name('city')->where('id',$sheng)->find();
        $shiarea=Db::name('city')->where('id',$shi)->find();
        $xianarea=Db::name('city')->where('id',$xian)->find();
        $area=$shengarea['cityname'].'|'.$shiarea['cityname'].'|'.$xianarea['cityname'];
        // dump($area);die;
        return $area;
    }
    // 修改收货地址
    function editAddress(){
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $data=$this->request->post();
            $updateData=[
                'name'=>$data['user_name'],
                'mobile'=>$data['mobile'],
                'area'=>$data['area'],
                'address'=>$this->getArea($data['address'])
            ];
            $id=Session::get('member')['id'];
            if(Db::name('area')->where('user_id',$id)->update($updateData)==1){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='修改成功';
            }else{
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='修改失败';
            }
        }
        return json($this->returnMessage);
    }
}