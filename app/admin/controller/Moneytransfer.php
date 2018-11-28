<?php
 namespace app\admin\controller;
 use \think\Db;
 use \think\Paginator;


 class Moneytransfer extends Permissions
 {	
	static $page=20;	

	/**
	 * 会员列表首页  
	 */  
 	function Index(){
		$keywords=$this->request->param('keywords');
		$create_time=$this->request->param('create_time');
		if($keywords || $create_time)
		{
			$str="";
			if($keywords)
			{
			   $str=$str.' and username="'.$keywords.'"';
			}
			if($create_time)
			{
				$addtime=strtotime($create_time);
				$addtime1=$addtime+24*60*60;
				
				$str=$str." and (addtime>=".$addtime." and addtime<".$addtime1.")";
			}
			if($str)
			{
				$tranfer=Db::name('tranfer')->where('1  '.$str)->order("id desc")->paginate($this::$page,false,['query'=>$this->request->param()]);
			}
			else
			{
			     $tranfer=Db::name('tranfer')->order("id desc")->paginate($this::$page);
			}
			$this->assign('tranfer',$tranfer);
		}
		else
		{
			$tranfer=Db::name('tranfer')->order("id desc")->paginate($this::$page);
			$this->assign('tranfer',$tranfer);
		}
		
 		return $this->fetch();
	 }

	


 }