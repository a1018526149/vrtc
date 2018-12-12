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
            $data['pay_password']=md5(md5($this->request->post('pay_password')));
            $data['new_pay_password']=md5(md5($this->request->post('new_pay_password')));
            $member=Db::name('member')->where('id',$id)->find();
            if($member['user_password']!==$data['old_password']||$member['pay_password']!==$data['pay_password']){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='原始密码不正确';
            }else{
                if(Db::name('member')->where('id',$id)->update(['user_password'=>$data['confirm_password'],'pay_password'=>$data['new_pay_password']])==1){
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
                    $this->returnMessage['message']='金币不足';
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
        $article=Db::name('article')->order(['update_time'=>'desc','id'=>'desc'])->paginate(10);
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
        $tranfers=Db::name('tranfer')->where('username|tousername',$member['user_number'])->order('id desc')->paginate(15);
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
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $this->assign('member',$member);
        $grade=Db::name('grade')->where('grade',$member['grade']+1)->find();
        $this->assign('grade',$grade);
        return $this->fetch();
    }
    // 升级钱包接口
    function upWallet(){
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $id=Session::get('member')['id'];
            $member=Db::name('member')->where('id',$id)->find();
            $grade=Db::name('grade')->where('grade',$member['grade']+1)->find();
            $data=$this->request->post();
            $insertData=[
                'username'=>$member['user_number'],
                'memo'=>17,
                'money'=>$grade['muber'],
                'type'=>2,
                'addtime'=>time(),
                'memo1'=>"升级钱包",
                'type1'=>7,
            ];
            if(md5(md5($data['pay_password']))!=$member['pay_password']){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='支付密码错误';
            }else{
                if($member['price']<$grade['muber']){
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='钻石不足';
                    return json($this->returnMessage);die;
                }
                if(Db::name('e')->insert($insertData)==1&&Db::name('member')->where('id',$id)->update(['price'=>$member['price']-$grade['muber'],'grade'=>$member['grade']+1])==1){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='升级成功';
                }else{
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='升级失败';    
                }
            }
        }
        return json($this->returnMessage);
    }
    // 转账接口
    function tranApi(){
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $id=Session::get('member')['id'];
            $member=Db::name('member')->where('id',$id)->find();
            $data=$this->request->post();
            $receive=Db::name('member')->where('user_number',$data['receive'])->find();
            switch($data['type']){
                case "bonus":
                $type=1;
                break;
                case "price":
                $type=2;
                break;
                case "vrtc":
                $type=3;
                break;
            }
            $insertData=[
                'username'=>$member['user_number'],
                'realname'=>$member['user_name'],
                'tousername'=>$data['receive'],
                'torealname'=>$receive['user_name'],
                'price'=>$data['money'],
                'type'=>$type,
                'addtime'=>time(),
                'state'=>1
            ];
            $insertDataOfE=[
                'username'=>$receive['user_number'],
                'memo'=>16,
                'type'=>1,
                'money'=>$data['money'],
                'memo1'=>$receive['user_number']."+".$data['money'],
                'addtime'=>time(),
                'type1'=>6
            ];
            $insertDataOfE2=[
                'username'=>$member['user_number'],
                'memo'=>16,
                'type'=>2,
                'money'=>$data['money'],
                'memo1'=>$member['user_number']."-".$data['money'],
                'addtime'=>time(),
                'type1'=>6
            ];
            if(md5(md5($data['password']))!=$member['pay_password']){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='支付密码错误!';
            }else{
                if($member[$data['type']]<$data['money']){
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='余额不足';
                    return json($this->returnMessage);
                }
                if(!$receive){
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='接收人不存在';
                    return json($this->returnMessage);die;
                }
                if(Db::name('tranfer')->insert($insertData)==1
                &&Db::name('e')->insert($insertDataOfE)==1
                &&Db::name('e')->insert($insertDataOfE2)==1
                &&Db::name('member')->where('id',$id)->update([$data['type']=>$member[$data['type']]-$data['money']])==1
                &&Db::name('member')->where('user_number',$data['receive'])->update([$data['type']=>$receive[$data['type']]+$data['money']])==1){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='转账成功';
                }else{
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='转账失败';
                }
            }
        }
        return json($this->returnMessage);
    }
    // 币币转换
    function transformation(){
      return $this->info();
    }
    // 币币转换接口
    function transformationApi(){
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $id=Session::get('member')['id'];
            $member=Db::name('member')->where('id',$id)->find();
            $data=$this->request->post();
            $insertData=[
                'username'=>$member['user_name'],
                'memo'=>18,
                'money'=>$data['tranMoney'],
                'type'=>2,
                'addtime'=>time(),
                'memo1'=>currencyName($data['type'])."-".$data['tranMoney'],
                'type1'=>8
            ];
            $insertData2=[
                'username'=>$member['user_name'],
                'memo'=>18,
                'money'=>$data['tranMoney'],
                'type'=>2,
                'addtime'=>time(),
                'memo1'=>currencyName($data['receive'])."+".$data['tranMoney'],
                'type1'=>8
            ];
            if($data['type']=='price'&&$data['receive']=='bonus'){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='钻石不能转换为金币！';
                return json($this->returnMessage);die;
            }
            if($data['type']=='zbonus'&&$data['receive']=='bonus'){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='钻石不能转换为金币！';
                return json($this->returnMessage);die;
            }
            if(Db::name('e')->insert($insertData)==1
            &&Db::name('e')->insert($insertData2)==1
            &&Db::name('member')->where('id',$id)->update([
                    $data['type']=>$member[$data['type']]-$data['tranMoney'],
                    $data['receive']=>$member[$data['receive']]+$data['tranMoney']
                    ]
                )==1
                ){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='转换成功';
            }else{
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='转换失败';
            }
        }
        return json($this->returnMessage);
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
    // 充值
    function recharge(){
        return $this->info();
    }
    // 提现
    function cashWithdrawa(){
        $set=Db::name('setting')->field('withdraw_low,withdraw_fee')->where('id',2)->find();
        $this->assign('set',$set);
        return $this->info();
    }
    // 提交提现申请
    function pushCashWithdrawa(){
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求';
        }else{
            $id=Session::get('member')['id'];
            $set=Db::name('setting')->field('withdraw_low,withdraw_fee')->where('id',2)->find();
            $member=Db::name('member')->where('id',$id)->find();
            $data=$this->request->post();
            $data['password']=md5(md5($this->request->post('password')));
            if($data['password']!=$member['pay_password']){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='支付密码错误';
            }else{
                if($member['bonus']<$data['money']){
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='金币不足，无法提现';
                    return json($this->returnMessage);die;
                }
                if($data['money']%100!=0){
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='提现金额必须是100的整数倍';
                    return json($this->returnMessage);die;
                }
                $cashMoney=$data['money']-($data['money']*($set['withdraw_fee']/100));
                $insertData=[
                    'username'=>$member['user_number'],
                    'realname'=>$member['user_name'],
                    'zhanghao'=>$data['address'],
                    'price'   =>$data['money'],
                    'addtime' =>time(),
                    'fee'     =>$data['money']*($set['withdraw_fee']/100)
                ];
                if(Db::name('cashes')->insert($insertData)==1&&Db::name('member')->where('id',$id)->update(['bonus'=>$member['bonus']-$data['money']])==1){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='提现申请已经提交';
                }else{
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='提现申请未能提交，请稍后重试';
                }

            }
        }
        return json($this->returnMessage);
    }
    // 提现记录
    function cashList(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $this->assign('member',$member);
        $cash=Db::name('cashes')->where('username',$member['user_number'])->order('id desc')->paginate(10);
        $this->assign('cash',$cash);
        return $this->fetch();
    }
}