<?php
 namespace app\admin\controller;
 use \think\Db;

 class Member extends Permissions
 {	
	/**
	 * 会员列表首页  
	 */  
 	function Index(){
		$member=Db::name('member')->select();
		$this->assign('member',$member);
 		return $this->fetch();
	 }
	/**
	 * 会员等级列表
	 */
	function levelSet(){
		$level=Db::name('level_config')->select();
		$this->assign('level',$level);
		return $this->fetch();
	}
	/**
	 * 会员等级修改
	 */
	function levelEdit(){
		$id=$this->request->param('id');
		if($id){
			//编辑操作
			$level=Db::name('level_config')->where('id',$id)->find();
			$this->assign('level',$level);
		}
		return $this->fetch();
	}

	/**
	 * 会员信息查看/编辑
	 */

	function publish(){
		$id=$this->request->param('id');
		if($id){
			$searchFiedls=[
				'id',
				'user_name',
				'mobile',
				'sex',
				'refree',
				'price',
				'bonus',
				'register_time',
				'level',
				'user_number',
				'status',
				'grade'
			];
			$member=Db::name('member')->field($searchFiedls)->where('id',$id)->find();
			$this->assign('member',$member);
		}else{
			$info="默认密码‘0123456789’";
			$this->assign('info',$info);
		}
		$grade=Db::name('grade')->select();
		$level_cate=Db::name('level_config')->select();
		$this->assign('grade',$grade);
		$this->assign('level_cate',$level_cate);
		$this->assign('num','1');
		return $this->fetch();
	}

	/**
	 * 会员修改接口
	 */

	 function memberEditApi(){
		$id=$this->request->param('id');
		$data=$this->request->post();
		
		if($id){
			//修改操作
			$allowsFields=[
				'user_name',
				'mobile',
				'sex',
				'refree',
				'price',
				'bonus',
				'register_time',
				'level',
				'user_number',
				'status'
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
				Db::name('member')->where('id',$id)->update($updateData);
				addlog($id);
				$this->success('修改成功！','admin/member/index');
			}
		}else{
			//新增操作
			$data['user_password']=md5(md5($this->request->post('user_password')));
			$allowsFields=[
				'user_name',
				'mobile',
				'sex',
				'refree',
				'price',
				'bonus',
				'register_time',
				'level',
				'user_number',
				'status',
				'user_password'
			];
			$insertData=[];
			foreach($data as $k => $v){
				if(in_array($k,$allowsFields)){
					$insertData[$k]=$v;
				}
			}
			$insertData['register_time']=time();
			if(empty($insertData)){
				$this->error('添加失败！请重试');
				die;
			}
			if(Db::name('member')->insert($insertData)){
				addlog($id);
				$this->success('注册成功','admin/member/index');
			}else{
				$this->error('注册失败！请稍后重试');
			}
		}
	 }


	// 会员等级修改操作

	function updateLevel(){
		$id=$this->request->param('id');
		$data=$this->request->post();
		$allowsFields=[
			'name',
			'level'
		];
		if($id){
			// 修改操作
			$updateData=[];
			foreach($data as $k => $v ){
				if(in_array($k,$allowsFields)){
					$updateData[$k]=$v;
				}
			}
			if(Db::name('level_config')->where('id',$id)->update($updateData)){
				
				$this->success('修改成功','admin/member/levelset');
			}else{
				$this->error('修改失败！');
			}
		}else{
			// 新增操作
			$insertData=[];
			foreach($data as $k => $v ){
				if(in_array($k,$allowsFields)){
					$insertData[$k]=$v;
				}
			}
			if(Db::name('level_config')->insert($insertData)){
				$this->success('添加成功','admin/member/levelset');
			}else{
				$this->error('添加失败！');
			}
		}
	}

	//  审核/通过会员
	function status(){
		 if($this->request->isPost()){
			 $post = $this->request->post();
			 if(false == Db::name('member')->where('id',$post['id'])->update(['status'=>$post['status']])) {
				 return $this->error('设置失败');
			 } else {
				 return $this->success('设置成功','admin/member/index');
			 }
		 }
	 }

	//  删除会员
	function delete(){
		 if($this->request->isAjax()) {
			 $id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
			 if(false == Db::name('member')->where('id',$id)->delete()) {
				 return $this->error('删除失败');
			 } else {
				 addlog($id);//写入日志
				 return $this->success('删除成功','admin/member/index');
			 }
		 }
	 }

 }