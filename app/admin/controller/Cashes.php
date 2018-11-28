<?php
 namespace app\admin\controller;
 use \think\Db;
 use \think\Paginator;


 class Cashes extends Permissions
 {	
	static $page=20;	

	/**
	 * 会员列表首页  
	 */  
 	function Index(){
		$keywords=$this->request->param('keywords');
		if($keywords)
		{
			$cashes=Db::name('cashes')->where('username',$keywords)->order("id desc")->paginate($this::$page,false,['query'=>$this->request->param()]);
			$this->assign('cashes',$cashes);
		}
		else
		{
			$cashes=Db::name('cashes')->order("id desc")->paginate($this::$page);
			$this->assign('cashes',$cashes);
		}
		
 		return $this->fetch();
	 }


	
	 //同意
	 function agree(){
		if($this->request->isAjax()) {
			$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
			if($id)
			{		
			    $cashiesFields["state"]=1;
			    Db::name('cashes')->where('id',$id)->update($cashiesFields);
				addlog($id);//写入日志
				$cashes=Db::name('cashes')->where('id',$id)->find();
				$insertData=[];
				$insertData["username"]=$cashes["username"];
				$insertData["memo"]=3;
				$insertData["type"]=2;
				$insertData["addtime"]=time();
				$insertData["money"]=$cashes["price"]-$cashes["fee"];
				$insertData["memo1"]="提现编号".$id;
				$insertData["type1"]=1;
				$insertData["rank"]=0;
				Db::name('e')->insert($insertData);
				if($cashes["fee"]>0)
				{
					$insertData=[];
					$insertData["username"]=$cashes["username"];
					$insertData["memo"]=15;
					$insertData["memo"]=15;
					$insertData["type"]=2;
					$insertData["addtime"]=time();
					$insertData["money"]=$cashes["fee"];
					$insertData["memo1"]="提现编号".$id;
					$insertData["type1"]=1;
					$insertData["rank"]=0;
					Db::name('e')->insert($insertData);
				}        
                

				
				return $this->success('同意成功','admin/cashes/index');
			}
		}
	}
	 //拒绝
	 function refuse(){
		if($this->request->isAjax()) {
			$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
			if($id)
			{
				$cashes=Db::name('cashes')->where('id',$id)->find();
				if($cashes)
				{
					$state=$cashes["state"];
					if($state==0)
					{
						
						$price=$cashes["price"];
						$username=$cashes["username"];
						$user1=Db::name('member')->where('user_number',$username)->find();
						if($user1)
						{
							
							$updateData["realname"]=$user1["user_name"];
							$money=$user1["bonus"];
							$money=$money+$price;					
							$userFields=[
							  'bonus'
							];
							$userData=[];
							$userData["bonus"]=$money;
							
							Db::name('member')->where('user_number',$username)->update($userData);
						}
						else
						{
							$this->error('拒绝失败！会员名不存在');
							die;
						}
					}
				}
			}
			
			$cashiesFields["state"]=2;
			if($id) {
			    Db::name('cashes')->where('id',$id)->update($cashiesFields);
				addlog($id);//写入日志
				return $this->success('拒绝成功','admin/cashes/index');
			}
		}
	}
	 
	 //删除
	 
	 function delete(){
		if($this->request->isAjax()) {
			$id = $this->request->has('id') ? $this->request->param('id', 0, 'intval') : 0;
			if($id)
			{
				$cashes=Db::name('cashes')->where('id',$id)->find();
				if($cashes)
				{
					$state=$cashes["state"];
					if($state==0)
					{
						$price=$cashes["price"];
						$username=$cashes["username"];
						$user1=Db::name('member')->where('user_number',$username)->find();
						if($user1)
						{
							
							$updateData["realname"]=$user1["user_name"];
							$money=$user1["bonus"];
							$money=$money+$price;					
							$userFields=[
							  'bonus'
							];
							$userData=[];
							$userData["bonus"]=$money;
							Db::name('member')->where('user_number',$username)->update($userData);
						}
						else
						{
							$this->error('删除失败！会员名不存在');
							die;
						}
					}
				}
			}
			if(false == Db::name('cashes')->where('id',$id)->delete()) {
				return $this->error('删除失败');
			} else {
				addlog($id);//写入日志
				return $this->success('删除成功','admin/cashes/index');
			}
		}
	}


 }