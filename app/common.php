<?php
// +----------------------------------------------------------------------
// | Tplay [ WE ONLY DO WHAT IS NECESSARY ]
// +----------------------------------------------------------------------
// | Copyright (c) 2017 http://tplay.pengyichen.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 听雨 < 389625819@qq.com >
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 根据附件表的id返回url地址
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function geturl($id)
{
	if ($id) {
		$geturl = \think\Db::name("attachment")->where(['id' => $id])->find();
		if($geturl['status'] == 1) {
			//审核通过
			return $geturl['filepath'];
		} elseif($geturl['status'] == 0) {
			//待审核
			return '/uploads/xitong/beiyong1.jpg';
		} else {
			//不通过
			return '/uploads/xitong/beiyong2.jpg';
		} 
    }
    return false;
}


/**
 * [SendMail 邮件发送]
 * @param [type] $address  [description]
 * @param [type] $title    [description]
 * @param [type] $message  [description]
 * @param [type] $from     [description]
 * @param [type] $fromname [description]
 * @param [type] $smtp     [description]
 * @param [type] $username [description]
 * @param [type] $password [description]
 */
function SendMail($address)
{
    vendor('phpmailer.PHPMailerAutoload');
    //vendor('PHPMailer.class#PHPMailer');
    $mail = new \PHPMailer();          
     // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();                
    // 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->CharSet='UTF-8';         
    // 添加收件人地址，可以多次使用来添加多个收件人
    $mail->AddAddress($address); 

    $data = \think\Db::name('emailconfig')->where('email','email')->find();
            $title = $data['title'];
            $message = $data['content'];
            $from = $data['from_email'];
            $fromname = $data['from_name'];
            $smtp = $data['smtp'];
            $username = $data['username'];
            $password = $data['password'];   
    // 设置邮件正文
    $mail->Body=$message;           
    // 设置邮件头的From字段。
    $mail->From=$from;  
    // 设置发件人名字
    $mail->FromName=$fromname;  
    // 设置邮件标题
    $mail->Subject=$title;          
    // 设置SMTP服务器。
    $mail->Host=$smtp;
    // 设置为"需要验证" ThinkPHP 的config方法读取配置文件
    $mail->SMTPAuth=true;
    //设置html发送格式
    $mail->isHTML(true);           
    // 设置用户名和密码。
    $mail->Username=$username;
    $mail->Password=$password; 
    // 发送邮件。
    return($mail->Send());
}


/**
 * 阿里大鱼短信发送
 * @param [type] $appkey    [description]
 * @param [type] $secretKey [description]
 * @param [type] $type      [description]
 * @param [type] $name      [description]
 * @param [type] $param     [description]
 * @param [type] $phone     [description]
 * @param [type] $code      [description]
 * @param [type] $data      [description]
 */
function SendSms($param,$phone)
{
    // 配置信息
    import('dayu.top.TopClient');
    import('dayu.top.TopLogger');
    import('dayu.top.request.AlibabaAliqinFcSmsNumSendRequest');
    import('dayu.top.ResultSet');
    import('dayu.top.RequestCheckUtil');

    //获取短信配置
    $data = \think\Db::name('smsconfig')->where('sms','sms')->find();
            $appkey = $data['appkey'];
            $secretkey = $data['secretkey'];
            $type = $data['type'];
            $name = $data['name'];
            $code = $data['code'];
    
    $c = new \TopClient();
    $c ->appkey = $appkey;
    $c ->secretKey = $secretkey;
    
    $req = new \AlibabaAliqinFcSmsNumSendRequest();
    //公共回传参数，在“消息返回”中会透传回该参数。非必须
    $req ->setExtend("");
    //短信类型，传入值请填写normal
    $req ->setSmsType($type);
    //短信签名，传入的短信签名必须是在阿里大于“管理中心-验证码/短信通知/推广短信-配置短信签名”中的可用签名。
    $req ->setSmsFreeSignName($name);
    //短信模板变量，传参规则{"key":"value"}，key的名字须和申请模板中的变量名一致，多个变量之间以逗号隔开。
    $req ->setSmsParam($param);
    //短信接收号码。支持单个或多个手机号码，传入号码为11位手机号码，不能加0或+86。群发短信需传入多个号码，以英文逗号分隔，一次调用最多传入200个号码。
    $req ->setRecNum($phone);
    //短信模板ID，传入的模板必须是在阿里大于“管理中心-短信模板管理”中的可用模板。
    $req ->setSmsTemplateCode($code);
    //发送
    

    $resp = $c ->execute($req);
}

function level($level){
    $name=\think\Db::name('level_config')->where('level',$level)->find();
    return $name['name'];
}

/**
 * 获取用户级别信息
 * @param number $grade 级别
 * @param string $name  返回名称
 */

function grade($grade){
    $name=\think\Db::name('grade')->where('grade',$grade)->find();
    return $name['name'];
}

 //订单状态
 function ddstate($state){
    switch($state){
        case 0:$state_1="未付款";break;
        case 1:$state_1="已付款";break;
        case 2:$state_1="已发货";break;
        case 3:$state_1="已收货";break;
        case 4:$state_1="预订成功";break;
     }

    return $state_1;
 }
 
  //充值类型
 function czstate($state){
    switch($state){
        
        case 1:$state_1="报单币";break;
        case 2:$state_1="已发货";break;
        case 3:$state_1="已收货";break;
        case 4:$state_1="预订成功";break;
     }

    return $state_1;
 }
 //提现状态
 function txstate($state){
    switch($state){
        case 0:$state_1="未审核";break;
        case 1:$state_1="同意";break;
        case 2:$state_1="拒绝";break;

     }

    return $state_1;
 }
//转账状态
 function zzstate($state){
    switch($state){
        case 0:$state_1="未成功";break;
        case 1:$state_1="成功";break;
        case 2:$state_1="拒绝";break;

     }

    return $state_1;
 }
 
 //转账状态
 function zztype($state){
    switch($state){
        case 1:$state_1="报单币";break;
        case 2:$state_1="奖金";break;
        case 3:$state_1="VRTC";break;
        default:
        $state_1="NULL";
     }

    return $state_1;
 }

//奖金类型
 function jjtype($state){
    switch($state){
        case 1:$state_1="收入";break;
        case 2:$state_1="支出";break;
     }

    return $state_1;
 }


//奖金摘要
 function jjmemo($state){
	 $state_1="";
    switch($state){
        case 1:$state_1="充值";break;
		case 3:$state_1="提现";break;
        case 15:$state_1="提现手续费";break;
        case 16:$state_1="转账";break;
        case 17:$state_1="升级钱包";break;
        case 18:$state_1="转换币种";break;
		case 20:$state_1="推荐奖";break;
     }

    return $state_1;
 }
 
 //区位
 function pos1($pos){
    switch($pos)
	{
        case 1:$pos_1="左区";break;
		case 2:$pos_1="右区";break; 
		case 0:$pos_1="";break;       
    }

    return $pos_1;
 }


/**
 * 替换手机号码中间四位数字
 * @param  [type] $str [description]
 * @return [type]      [description]
 */
function hide_phone($str){
    $resstr = substr_replace($str,'****',3,4);  
    return $resstr;  
}

/**
 * 获取推荐人姓名
 * @param string $refree 推荐人
 * @param string $name   名字
 */
function getRefreeName($refree){
    $member=\think\Db::name('member')->field(['user_name','user_number','refree'])->where('refree',$refree)->select();
    return $member;
}

/**
 * 生成编号
 * @param number $number 编号
 */

function number($a){
    $temporary=rand(1,9);
    switch($temporary){
       case 1:
       $char="A";
       break;
       case 2:
       $char="B";
       break;
       case 3:
       $char="C";
       break;
       case 4:
       $char="D";
       break;
       case 5:
       $char="E";
       break;
       case 6:
       $char="F";
       break;
       case 7:
       $char="G";
       break;
       case 8:
       $char="H";
       break;
       case 9:
       $char="I";
       break;
    }
    $number=$char.rand(10000,99999);
    return $number;
}

/**
 * 激活会员写入奖金池并生成“K”值
 * @author LC 2018/12/10
 * @param float $bonus 参数设置
 */

function bonusIncrease(){
	
	$new_bonus=\think\Db::name('setting')->field('hcrate')->where('id',2)->find();
	//新增钱包时添加奖金
	if(\think\Db::name('setting')->where('id',2)->update(['hcrate'=>$new_bonus['hcrate']+300])){
		return true;
	}else{
		return false;
	}
	$bonus=\think\Db::name('setting')->field('hcrate,recommend,dbrate')->find();
	if($bonus['hcrate']>=$bonus['dbrate']){
		\think\Db::name('setting')->where('id',2)->update(['recommend'=>$bonus['recommend']+1]);//添加Key值
		\think\Db::name('setting')->where('id',2)->update(['hcrate'=>$bonus['hcrate']-1200]);//修改金额
	}
}

/**
 * 货币种类
 * @author LC 2018/12/10
 * @param string $name 奖金名称 
 */

 function currencyName($type){
    switch($type){
        case "bonus":
        $name="金币";
        break;
        case "price_repeat":
        $name='商城币';
        break;
        case "price":
        $name='钻石';
        break;
        case "vrtc":
        $name='VRTC';
        break;
    }
    return $name;
 }