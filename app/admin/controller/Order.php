<?php
 namespace app\admin\controller;
 use \think\Db;
 use \think\Paginator;
 class Order extends Permissions
 {	
	static $page=20;
	/**
	 * 会员列表首页  
	 */  
 	function Index(){
		$keywords=$this->request->param('keywords');
		if($keywords)
		{
			$order=Db::name('orders')->where('username LIKE "%'.$keywords.'%" or  realname LIKE "%'.$keywords.'%"')->order('id desc')->select();
			$this->assign('order',$order);
		}
		else
		{
			$order=Db::name('orders')->order('id desc')->paginate($this::$page);
			$this->assign('order',$order);
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
	 
	 
	 function ziti(){
			$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
			if($id){
				//修改自提
				$updateData=[];
				$updateData['state']='3';
				$updateData['shouhuotime']=time();
				$updateData['fahuotime']=time();
				$updateData['ziti']='1';
				if(empty($updateData)){
					$this->error('自提修改失败，请重试！');
				}else{
					Db::name('orders')->where('id',$id)->update($updateData);
					addlog($id);
					$this->success('自提修改成功！','admin/order/index');
				}
				
			}else{
				$this->error('操作失败！请稍后重试');
			}
		
	}

	 function delete(){
		if($this->request->isAjax()) {
			$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
			if(false == Db::name('orders')->where('id',$id)->delete()) {
				return $this->error('删除失败');
			} else {
				addlog($id);//写入日志
				return $this->success('删除成功','admin/order/index');
			}
		}
	}


 }