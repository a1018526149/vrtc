<?php
 namespace app\admin\controller;
 use \think\Db;

 class Logistics extends Permissions
 {	
	/**
	 * 会员列表首页  
	 */  
 	function Index(){
		$keywords=$this->request->param('keywords');
		if($keywords)
		{
			$logcom=Db::name('logcom')->where('company',$keywords)->select();
			$this->assign('logcom',$logcom);
		}
		else
		{
			$logcom=Db::name('logcom')->select();
			$this->assign('logcom',$logcom);
		}
		
 		return $this->fetch();
	 }
	/**
	 * 奖金设置列表
	 */

    function edit(){
		$id=$this->request->param('id');
		if($id){
			$searchFiedls=[
				'id',
				'company',
				'address',
				'tel',
				'contact',
				'url',
				'addtime'
			];
			$logcom=Db::name('logcom')->field($searchFiedls)->where('id',$id)->find();
			$this->assign('logcom',$logcom);
		}else{
			
		}
		$this->assign('num','1');
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
				'company',
				'address',
				'tel',
				'contact',
				'url'
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
				Db::name('logcom')->where('id',$id)->update($updateData);
				//addlog($id);
				$this->success('修改成功！','admin/logistics/index');
			}
			
		}
		else{
             //新增操作
			$allowsFields=[
				'company',
				'address',
				'tel',
				'contact',
				'url',
				'addtime'
			];
			$insertData=[];
			foreach($data as $k => $v){
				if(in_array($k,$allowsFields)){
					$insertData[$k]=$v;
				}
			}
			$insertData['addtime']=time();
			if(empty($insertData)){
				$this->error('添加失败！请重试');
				die;
			}
			if(Db::name('logcom')->insert($insertData)){
				//addlog($id);
				$this->success('添加成功','admin/logistics/index');
			}else{
				$this->error('添加失败！请稍后重试');
			}
		}
	 }
	 
	 
	 function delete(){
		if($this->request->isAjax()) {
			$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
			if(false == Db::name('logcom')->where('id',$id)->delete()) {
				return $this->error('删除失败');
			} else {
				addlog($id);//写入日志
				return $this->success('删除成功','admin/logistics/index');
			}
		}
	}


 }