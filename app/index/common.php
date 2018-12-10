<?php
function createqrcode($number,$pos){

	require_once("../extend/phpqrcode/phpqrcode.php");
	//定义纠错级别
	$errorLevel = "L";
	//定义生成图片的宽度和高度，默认为3
	$size 	= 5;

	//定义生成内容
	// $content = "尝试下内容测试";
	$content= "http://localhost/index/register/login?pos=".$pos."&refree=".$number;//如果是网址，则需要加上http，否则无法进入网址

	//调用Qrcode类的静态png方法生成二维码团
	$qrcode = Qrcode::png($content,'./cache/'.$number.'.png',$errorLevel,$size);

	// $logo 	= './cache/logo.png';//准备好的logo图片   
	$QR 	= './cache/'.$number.'.png';//已经生成的原始二维码图   
    $QR = imagecreatefromstring(file_get_contents($QR));   

	// if ($logo !== FALSE) {   
	//     $QR = imagecreatefromstring(file_get_contents($QR));   
	//     $logo 			= imagecreatefromstring(file_get_contents($logo));   
	//     $QR_width 		= imagesx($QR);//二维码图片宽度   
	//     $QR_height 		= imagesy($QR);//二维码图片高度   
	//     $logo_width 	= imagesx($logo);//logo图片宽度   
	//     $logo_height	= imagesy($logo);//logo图片高度   
	//     $logo_qr_width  = $QR_width / 5;   
	//     $scale 			= $logo_width/$logo_qr_width;   
	//     $logo_qr_height = $logo_height/$scale;   
	//     $from_width 	= ($QR_width - $logo_qr_width) / 2;   
	//     //重新组合图片并调整大小   
	//     imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,   
	//     $logo_qr_height, $logo_width, $logo_height);   
	// }   
	//输出图片   
	imagepng($QR, './cache/'.$number.'.png');   

	return './cache/'.$number.'.png';
}

function createImage(){

	$path1= createqrcode();
	$path2= "./cache/timg.png";
	$im1  = imagecreatefrompng($path1); 
	$im2  = imagecreatefromjpeg($path2);
	$im3  = imagecreatetruecolor(imagesx($im2), imagesy($im2) + imagesy($im1)); 
	$font = 'tsst.ttf';
	$black= imagecolorallocate($im2, 0, 0, 0);//字体颜色 RGB
	 
	$fontSize 	= 25;   //字体大小
	$circleSize = 0; //旋转角度
	$left 		= 400;      //左边距
	$top 		= 600;       //顶边距
	
	// imagefttext($im2, $fontSize, $circleSize, $left, $top, $black, $font, "扫码关注更多详情");
	$_bg_color  = imagecolorallocate($im3, 255,255,255); //创建颜色，返回颜色标识符 
	imagefill($im3, 0, 0, $_bg_color); //初始化图像背景为$_bg_color
	imagecopymerge($im2, $im1, 160, 273, 0, 0, imagesx($im1), imagesy($im1), 100); 
	// imagecopymerge($im3, $im2, 0, imagesy($im1), 0, 0, imagesx($im2), imagesy($im2), 100);
	
	// header("Content-type: image/png"); 
	// imagepng($im2);
	imagepng($im2,"./img/".time().".png");
	return "./img/".time().".png";
}

/**
 * 获取管理员名称
 * @author LC 2018/12/4
 */
function admin($id){
	$admin=\think\Db::name('admin')->field('nickname')->where('id',$id)->find();
	return $admin['nickname'];
}


