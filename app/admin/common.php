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

//admin模块公共函数

/**
 * 管理员密码加密方式
 * @param $password  密码
 * @param $password_code 密码额外加密字符
 * @return string
 */
function password($password, $password_code='lshi4AsSUrUOwWV')
{
    return md5(md5($password) . md5($password_code));
}

/**
 * 管理员操作日志
 * @param  [type] $data [description]
 * @return [type]       [description]
 */
function addlog($operation_id='')
{
    //获取网站配置
    $web_config = \think\Db::name('webconfig')->where('web','web')->find();
    if($web_config['is_log'] == 1) {
        $data['operation_id'] = $operation_id;
        $data['admin_id'] = \think\Session::get('admin');//管理员id
        $request = \think\Request::instance();
        $data['ip'] = $request->ip();//操作ip
        $data['create_time'] = time();//操作时间
        $url['module'] = $request->module();
        $url['controller'] = $request->controller();
        $url['function'] = $request->action();
        //获取url参数
        $parameter = $request->path() ? $request->path() : null;
        //将字符串转化为数组
        $parameter = explode('/',$parameter);
        //剔除url中的模块、控制器和方法
        foreach ($parameter as $key => $value) {
            if($value != $url['module'] and $value != $url['controller'] and $value != $url['function']) {
                $param[] = $value;
            }
        }

        if(isset($param) and !empty($param)) {
            //确定有参数
            $string = '';
            foreach ($param as $key => $value) {
                //奇数为参数的参数名，偶数为参数的值
                if($key%2 !== 0) {
                    //过滤只有一个参数和最后一个参数的情况
                    if(count($param) > 2 and $key < count($param)-1) {
                        $string.=$value.'&';
                    } else {
                        $string.=$value;
                    }
                } else {
                    $string.=$value.'=';
                }
            } 
        } else {
            //ajax请求方式，传递的参数path()接收不到，所以只能param()
            $string = [];
            $param = $request->param();
            foreach ($param as $key => $value) {
                if(!is_array($value)) {
                    //这里过滤掉值为数组的参数
                    $string[] = $key.'='.$value;
                }
            }
            $string = implode('&',$string);
        }
        $data['admin_menu_id'] = empty(\think\Db::name('admin_menu')->where($url)->where('parameter',$string)->value('id')) ? \think\Db::name('admin_menu')->where($url)->value('id') : \think\Db::name('admin_menu')->where($url)->where('parameter',$string)->value('id');
                    
        //return $data;
        \think\Db::name('admin_log')->insert($data);
    } else {
        //关闭了日志
        return true;
    }
	
}


/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 获取用户等级信息
 * @param number $level 等级
 * @param string $name  返回名称
 */

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