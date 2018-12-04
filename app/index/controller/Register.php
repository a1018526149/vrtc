<?php
/**
 * 注册、扫描二维码功能
 * @author 李财 2018/11/28
 */
namespace app\index\controller;

use \think\Db;
use \think\Request;
use \think\Session;

class Register  extends Index
{   
   public $c_str;
    // 注册主页
    function Index(){

        return $this->fetch();

    }
    // 注册页面

    function login(){
        $num=number(1);
        $this->assign('num',$num);
        return $this->fetch();
    }

    // 注册接口
    function Register(){
        global $c_str;
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $data=$this->request->post();
            if(Db::name('member')->where('user_number',$data['user_number'])->find()==1){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='用户名重复';
                return  json($this->returnMessage);
                die;
            }
            if(Db::name('member')->where('user_number',$data['refree'])->find()==false){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='推荐人不存在';
                return json($this->returnMessage);
                die;
            }
            if(empty($data['refree'])){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='推荐人不能为空';
                return  json($this->returnMessage);
                die;
            }
            if(empty($data['user_password'])){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='密码不能为空';
                return  json($this->returnMessage);
                die;
            }
            $data['user_password']=md5(md5($this->request->post('user_password')));
            $allowsFields=[
                'user_name',
				'mobile',
                'refree',
                'refree_node',
				'user_number',
				'user_password'
            ];
            $insertData=[];
            foreach($data as $k => $v ){
                if(in_array($k,$allowsFields)){
                    $insertData[$k]=$v;
                }
            }
            $insertData['register_time']=time();
            // 添加节点人
            $member_node=Db::name('member')->field('refree,refree_node,tjstr,tjdept,glstr,gldept')->where('user_number',$data['refree'])->find();
            // 推荐关系图
            if(empty($member_node['refree'])){
                $insertData['tjstr']=$data['refree'].',';
            }else{
                $insertData['tjstr']=$data['refree'].','.$member_node['tjstr'];
            }
            // 推荐深度
            $insertData['tjdept']=$member_node['tjdept']+1;
            
            $this->refreeNode($data['refree']);
            $newNode=explode(',',$c_str);
            $isback="";
            foreach($newNode as $key => $value){
                for($god=1;$god<=2;$god++){
                    $pesmember=Db::name('member')->where(['refree_node'=>$value,'pos'=>$god])->find();
                    if(empty($pesmember)){
                        $pos=$god;
                        $refree_node=$value;
                        $isback=1;
                    }
                    if($isback==1){
                        break;
                    }
                }
                if($isback==1){
                    break;
                }
            }
            $insertData['pos']=$pos;
            $insertData['refree_node']=$refree_node;
            
            $theMember=Db::name('member')->field('gldept,glstr')->where('user_number',$refree_node)->find();
            // 安置关系图
            $insertData['glstr']=$theMember['glstr'].$data['user_number'].',';
            // 安置深度
            $insertData['gldept']=$theMember['gldept']+1;


            if(Db::name('member')->where('mobile',$insertData['mobile'])->find()){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='手机号重复';
                return  json($this->returnMessage);
                die;
            }
            
            if(Db::name('member')->insert($insertData)==1){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='注册成功';
            }else{
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='注册失败，请重试';
            }
        }
        return json($this->returnMessage);
    }

    //选择产品
    function product(){
        $number=$this->request->param('number');
        $product=Db::name('product')->where('cateid',3)->select();
        $this->assign('product',$product);
        Session::set('user_number',$number);
        return $this->fetch();
    }
    function selectProduct(){
        $number=Session::get('user_number');
        if(!$number){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='未获取到注册信息，请核对账户后重试';
            return json($this->returnMessage);die;
        }
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $data=$this->request->post();
            $member=Db::name('member')->where('user_number',$number)->find();
            $product=Db::name('product')->where('id',$data['id'])->find();
            $time=time();
            $insertData=[
                'username'=>$number,
                'realname'=>$member['user_name'],
                'price'   =>$product['price'],
                'addtime' =>$time
            ];
            if(Db::name('orders')->insert($insertData)==1){
                $order=Db::name('orders')->where('addtime',$time)->find();
                $insertData2=[
                    'orderid'=>$order['id'],
                    'username'=>$number,
                    'realname'=>$member['user_name'],
                    'productid'=>$product['id'],
                    'productname'=>$product['name'],
                    'num' =>1,
                    'price'=>$order['price'],
                    'addtime'=>$time
                ];
                if(Db::name('orders1')->insert($insertData2)==1){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='提交成功';
                }else{
                    $this->returnMessage['code']='error';
                    $this->returnMessage['message']='提交失败';
                }
            }else{
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='提交失败';
            }
            // if(Db::name('member')->where('user_number',$number)->update(['product'=>$data['id']])==1){
            //     $this->returnMessage['code']='success';
            //     $this->returnMessage['message']='提交成功';
            // }else{
            //     $this->returnMessage['code']='error';
            //     $this->returnMessage['message']='提交失败';
            // }
        }
        return json($this->returnMessage);
    }
    // 选择提货方式
    function shop(){
        
        $sheng=Db::name('city')->where('type',1)->select();
        $this->assign('sheng',$sheng);
        return $this->fetch();
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
    // 提交提货方式
    function putShopData(){
        $number=Session::get('user_number');
        if(!Request::instance()->isPost()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $self=$this->request->post('self');
            if($self){
                if(Db::name('member')->where('user_number',$number)->update(['selfmention'=>0])==1){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='保存成功';
                }else{
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='保存失败';
                }
            }else{
                $allowsFields=[
                    'name',
                    'mobile',
                    'area',
                    'address'
                ];
                $data=$this->request->post();
                $updateData=[];
                foreach($data as $k => $v ){
                    if(in_array($k,$allowsFields)){
                        $updateData[$k]=$v;
                    }
                }
                $updateData['create_time']=time();
                $member=Db::name('member')->field('id')->where('user_number',$number)->find();
                $updateData['user_id']=$member['id'];
                if(Db::name('area')->insert($updateData)==1){
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='保存成功';
                }else{
                    $this->returnMessage['code']='success';
                    $this->returnMessage['message']='保存失败';
                }
            }
        }
        return json($this->returnMessage);
    }
    function getArea(){
        $sheng=$this->request->post('sheng');
        $shi=$this->request->post('shi');
        $xian=$this->request->post('xian');
        $shengarea=Db::name('city')->where('id',$sheng)->find();
        $shiarea=Db::name('city')->where('id',$shi)->find();
        $xianarea=Db::name('city')->where('id',$xian)->find();
        $area=$shengarea['cityname'].'|'.$shiarea['cityname'].'|'.$xianarea['cityname'];
        // dump($area);die;
        return $area;
    }
    // 注册成功页
    function thesuccess(){
        $number=Session::get('user_number');
        $member=Db::name('member')->where('user_number',$number)->find();
        if(!empty($member)){
            unset($member['user_password']);
            Session::set('member',$member);
        }
        return $this->fetch();
    }
    // 递归函数
    function refreeNode($refree){
        global $c_str;
        static $allMember;
        $member=Db::name('member')->where('find_in_set(refree_node,"'.$refree.'")>0')->order('id asc')->select();
        $refree_node="";
        // dump($member);

        if(!empty($member))
        {
            foreach($member as $k => $v ){
                if($refree_node!='') $refree_node.=',';
                $refree_node=$refree_node.$v['user_number'];
            }
        }

        if(!empty($refree_node))          
        {
            if($c_str!="") $c_str.=",";
            $c_str=$c_str.$refree_node;
            $this->refreeNode($refree_node);
        }
       
        // foreach($member as $v ){  
            
        //     $refree_node=$v['user_number'];
        // }
        // $this->refreeNode($v['user_number']);
        // dump($refree_node);
    }
}