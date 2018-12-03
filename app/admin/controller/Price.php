<?php
 namespace app\admin\controller;
 use \think\Db;
 use \think\Paginator;
 class Price extends Permissions
 {	
    static $page=20;	
	/**
	 * 会员列表首页  
	 */  
 	function Index(){
		
			
			$periods=Db::name('periods')->order("id desc")->paginate($this::$page);
			$this->assign('periods',$periods);
 		    return $this->fetch();
	 }

	/**
	 * 奖金设置修改
	 */

	 function periods_addsave(){
		$data=$this->request->post();
		$allowsFields=[
				'periods',
				'begintime',
				'endtime'
			];
			
			$insertData=[];
			foreach($data as $k => $v){
				if(in_array($k,$allowsFields)){
					$insertData[$k]=$v;
				}
			}
			
			if(empty($insertData["endtime"]))
			{
				$this->error('结束日期不能为空！请重选择');
				die;
			}
			$insertData["begintime"]=strtotime($insertData["begintime"]);
			$insertData["endtime"]=strtotime($insertData["endtime"]);
			if($insertData["begintime"]>$insertData["endtime"])
			{
				$this->error('结束日期不能小于开始日期！请重选择');
				die;
			}
			$periods=Db::name('periods')->where("begintime='".$insertData["begintime"]."' and endtime='".$insertData["endtime"]."'")->limit(1)->find();
			if($periods)
			{
				$this->error('请勿重复添加结算!');
				die;
			}
			$periods1=Db::name('periods')->where("('".$insertData["begintime"]."'>=begintime and '".$insertData["begintime"]."'<=endtime) or ('".$insertData["endtime"]."'>=begintime and '".$insertData["endtime"]."'<=endtime)")->limit(1)->find();
			if($periods)
			{
				$this->error('结算日期重复！请重新添加!');
				die;
			}
			$periods1=Db::name('periods')->where("periods<".$insertData["periods"]." and state<2")->limit(1)->find();
			if($periods1)
			{
				$this->error('前面期数没有发放奖金，请发放过了添加!');
				die;
			}
			if(empty($insertData)){
				$this->error('添加失败！请重试');
				die;
			}
			if(Db::name('periods')->insert($insertData)){
				addlog($insertData["periods"]);
				$this->success('提交成功','admin/price/index');
			}else{
				$this->error('提交失败！请稍后重试');
			}
	 }

	 /**
	  * 添加结算
	  */
	 function periods_add()
	 { 
	    $periods=Db::name('periods')->order("id desc")->limit(1)->find();
		if($periods)
		{
			$qs=$periods["periods"]+1;
			$kstime=$periods["endtime"]+24*60*60;
		}
		else
		{
			$user=Db::name('member')->order("id asc")->limit(1)->find();
			$kstime=$user["register_time"];
			$qs=1;
		}
		$this->assign('qs',$qs);
		$this->assign('kstime',$kstime);
		return $this->fetch();
	 }


	//  删除结算
	function periods_del(){
		 if($this->request->isAjax()) {
			 $id = $this->request->param('id', 0, 'intval');
			$periods=Db::name('periods')->where("id",$id)->find();
			if($periods["state"]==2)
			{
				$this->error('本期奖金已经发放，无法执行删除操作!');
				die;
			}
			$periods=Db::name('periods')->where("id>'".$id."'")->limit(1)->find();
			if($periods)
			{
				$this->error('操作错误，只能删除最新一期结算!');
				die;
			}
			 if(false == Db::name('periods')->where('id',$id)->delete()) {
				 return $this->error('删除失败');
			 } else {
				 addlog($id);//写入日志
				 return $this->success('删除成功','admin/price/index');
			 }
		 }
	 }
 }