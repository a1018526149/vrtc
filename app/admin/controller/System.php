<?php
 namespace app\admin\controller;
 use \think\Db;

 class System extends Permissions
 {	
	/**
	 * 会员列表首页  
	 */  
 	function Index(){
		$member=Db::name('setting')->select();
		$this->assign('setting',$member);
 		return $this->fetch();
	 }
	/**
	 * 奖金设置列表
	 */
	function moneyaward(){
		$grade=Db::name('grade')->select();
		$setting=Db::name('setting')->where('id',2)->find();
		$this->assign('grade',$grade);
		$this->assign('setting',$setting);
		return $this->fetch();
	}


	/**
	 * 奖金设置修改
	 */

	 function editApi(){
		$id=$this->request->param('id');
		$data=$this->request->post();
		
		if($id){
			//修改操作
			$allowsFields=[
				'hcrate',
				'dbrate',
				'recommend'
			];
			$updateData=[];
			foreach($data as $key => $value){
				if(in_array($key,$allowsFields)){
					$updateData[$key]=$value;
				}
			}
			if(empty($updateData)){
				$this->error('修改失败，请重试！');
			}else{
				Db::name('setting')->where('id',$id)->update($updateData);
				//addlog($id);
				$this->success('修改成功！','admin/system/moneyaward');
			}
			
		}
		else{

			$this->error('修改失败，请重试！');
		}
	 }



 }