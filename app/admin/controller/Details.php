<?php
 namespace app\admin\controller;
 use \think\Db;
 use \think\Paginator;


 class Details extends Permissions
 {	
	static $page=20;	

	/**
	 * 会员列表首页  
	 */  
 	function Index(){
		$keywords=$this->request->param('keywords');
		$create_time=$this->request->param('create_time');
		$type1=$this->request->param('type1');
		if($keywords || $create_time || $type1)
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
			if($type1)
			{
     			$str=$str." and type=".$type1."  ";
			}
			if($str)
			{
				$details=Db::name('e')->where('1  '.$str)->order("id desc")->paginate($this::$page,false,['query'=>$this->request->param()]);
			}
			else
			{
			     $details=Db::name('e')->order("id desc")->paginate($this::$page);
			}
			$this->assign('e',$details);
		}
		else
		{
			$details=Db::name('e')->order("id desc")->paginate($this::$page);

			$this->assign('e',$details);
		}
		
 		return $this->fetch();
	 }

	


 }