<?php
 namespace app\admin\controller;
 use \think\Db;
 use \think\Paginator;


 class Recharge extends Permissions
 {	
	static $page=20;	

	/**
	 * 会员列表首页  
	 */  
 	function Index(){
		$keywords=$this->request->param('keywords');
		if($keywords)
		{
			$recharge=Db::name('recharge')->where('username = "'.$keywords.'" or  realname = "'.$keywords.'"')->order("id desc")->paginate($this::$page,false,['query'=>$this->request->param()]);
			$this->assign('recharge',$recharge);
		}
		else
		{
			$recharge=Db::name('recharge')->order("id desc")->paginate($this::$page);
			$this->assign('recharge',$recharge);
		}
		
 		return $this->fetch();
	 }
	/**
	 * 奖金设置列表
	 */

    function addczd(){
		$id=$this->request->param('id');
		
		return $this->fetch();
	}
	/**
	 * 奖金设置修改
	 */

	 function editApi(){
		$id="";
		$data=$this->request->post();
		if($id){
			//修改操作
			
		}
		else{
             //新增操作
			$allowsFields=[
				'username',
				'realname',
				'money',
				'state',
				'addtime',
				'type',
				'adminname'
			];
			$updateData=[];
			foreach($data as $key => $value){
				if(in_array($key,$allowsFields)){
					$updateData[$key]=$value;
				}
			}
			$updateData["state"]=1;
			$updateData["addtime"]=time();
			$updateData["adminname"]=\think\Session::get('admin');

			if(!empty($updateData["username"]))
			{
				$user1=Db::name('member')->where('user_number',$updateData["username"])->find();
				if($user1)
				{
				    
					$updateData["realname"]=$user1["user_name"];
					$money=$user1["price"];
					$money=$money+$updateData["money"];					
					$userFields=[
				      'price'
			        ];
					$userData=[];
					$userData["price"]=$money;
					Db::name('member')->where('user_number',$updateData["username"])->update($userData);
					$insertData["username"]=$user1["user_number"];
					$insertData["memo"]=1;
					$insertData["type"]=1;
					$insertData["addtime"]=time();
					$insertData["money"]=$updateData["money"];
					$insertData["memo1"]="后台充值报单币";
					$insertData["type1"]=1;
					$insertData["rank"]=0;
					Db::name('e')->insert($insertData);
				}
				else
				{
					$this->error('充值失败！会员名不存在');
				    die;
				}
			}
			if(empty($updateData)){
				$this->error('充值失败！请重试');
				die;
			}
			if(Db::name('recharge')->insert($updateData)){
				addlog($id);
				$this->success('充值成功','admin/recharge/index');
			}else{
				$this->error('充值失败！请稍后重试');
			}
		}
	 }
	 
	 
	 


 }