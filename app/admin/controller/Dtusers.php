<?php
 namespace app\admin\controller;
 use \think\Db;

 class Dtusers extends Permissions
 {	
	/**
	 * 会员列表首页  
	 */  
 	function Index(){
		$userid=$this->request->param('userid');
		if($userid)
		{
			$member=Db::name('member')->where('user_number',$userid)->find();
			$this->assign('member',$member);
			$tree=$this->tree($member["user_number"],3,1);
			$this->assign('tree',$tree);
		}
		else
		{
			
			$member=Db::name('member')->order("id asc")->limit(1)->find();
			$this->assign('member',$member);
			$tree=$this->tree($member["user_number"],3,1);
			$this->assign('tree',$tree);
		}
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
				addlog($id);
				$this->success('修改成功！','admin/system/moneyaward');
			}
			
		}
		else{

			$this->error('修改失败，请重试！');
		}
	 }

	 /**
	  * 推荐树状图
	  */
	 function tree($tj_username,$dtp,$dtp1)
	 { 
	    static $str="";
		if ($dtp>=$dtp1){
			$str=$str.'<table align="center" cellpadding="0" cellspacing="0" width="80%"><tr>';
			$res=Db::name('member')->where('user_number',$tj_username)->find();
            $i=1;
			$posnum=2;
			for($m=1;$m<=$posnum;$m++){
				$where["refree_node"]=$tj_username;
				$where["pos"]=$m;
                $rs=Db::name('member')->where($where)->find();
				$c_num=$posnum;
				$price=0;
				$new=0;
				$str=$str.'<td valign="top">';
				if ($c_num>1)
				{
					if (($i==1) &&($c_num>$i))
					{
						$str=$str.'<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr>';
                        $str=$str.'<td align="center"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>';
                        $str=$str.'<td width="50%" height="1" ></td>';
						$str=$str.'<td width="50%" height="1" bgcolor="#003399"></td>';
					    $str=$str.'</tr></table>';
					    $str=$str.'<img style="WIDTH: 1px; HEIGHT: 20px" alt="" src="/static/admin/images/line.gif" border="0" /></td></tr></table>';	
					}
					else if(($i>1) &&($c_num==$i))
					{
						$str=$str.'<table border="0" cellspacing="0" cellpadding="0" width="100%" ><tr>';
                        $str=$str.'<td align="center"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>';
                        $str=$str.'<td width="50%" height="1" bgcolor="#003399"></td><td width="50%" height="1" ></td>';
					    $str=$str.'</tr></table>';
					    $str=$str.'<img style="WIDTH: 1px; HEIGHT: 20px" alt="" src="/static/admin/images/line.gif" border="0" /></td></tr></table>';
					}
					else
					{
						$str=$str.'<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr>';
                        $str=$str.'<td align="center"><table width="100%" border="0" cellspacing="0" cellpadding="0"><tr>';
                        $str=$str.'<td width="50%" height="1" bgcolor="#003399"></td><td width="50%" height="1" bgcolor="#003399" ></td>';
					    $str=$str.'</tr></table>';
					    $str=$str.'<img style="WIDTH: 1px; HEIGHT: 20px" alt="" src="/static/admin/images/line.gif" border="0" /></td></tr></table>';
					}
					if(!empty($rs))
					{
						 $str=$str.'<table border="0" cellpadding="0" cellspacing="1" bgcolor="#517DBF" align="center" style="margin:0px auto 0 auto; border:1px solid #06F;" width="120px"><tr><td align="center" bgcolor="#FFFFFF"><table width="100%" border="0" cellspacing="1" cellpadding="0">';
						 switch($rs["grade"]){
											case 1:$bg1="#009999";$bgcol="#009999";$bg2="#009999";break;
											case 2:$bg1="#8891ed";$bgcol="#8891ed";$bg2="#8891ed";break;
											case 3:$bg1="#AA3939";$bgcol="#AA3939";$bg2="#AA3939";break;
											case 4:$bg1="#336699";$bgcol="#336699";$bg2="#336699";break;
											case 5:$bg1="#ff0000";$bgcol="#ff0000";$bg2="#ff0000";break;
											case 6:$bg1="#FFCC00";$bgcol="#FFCC00";$bg2="#FFCC00";break;
											case 7:$bg1="#FF9900";$bgcol="#FF9900";$bg2="#FF9900";break;
											}
						 if($rs['status']==0) {$bg1="#CCCCCC";$bgcol="#CCCCCC";$bg2="#CCCCCC";}
						 $str=$str.'<tr><td align="center" bgcolor="'.$bg1.'"><a style="WIDTH: 80px; BACKGROUND-COLOR: " href="?userid='.urlencode($rs["user_number"]).'"><font color="ffffff"><strong>'.$rs["user_number"].'</strong></font></a></td></tr>';
						 $str=$str.'<tr><td height="15" align="center" bgcolor="'.$bg1.'"><font color="ffffff">'.$rs["user_name"].'</font></td></tr>';
						 $str=$str.'<tr><td align="center" bgcolor="'.$bg2.'">';$rankname=grade($rs["grade"]);
						 $str=$str.$rankname.'</td>';
						 if($rs['status']==0) {
							 $str=$str.' <tr><td align="center" background="images/tab_19.gif"><span style=" color:red">未激活</span></td></tr>';
							 }
                        $str=$str.'<tr><td align="center" background="images/tab_19.gif">'.date("Y-m-d",$rs["register_time"]).'</td></tr>';
						 $str=$str.'</table></td></tr></table>';
					}
					else
					{
						$str=$str.'<table border="0" cellpadding="0" cellspacing="1" bgcolor="#517DBF" align="center" style="margin:0px auto 0 auto;">';
						$str=$str.'<tr><td align="center" background="images/tab_19.gif" width="70">';
			            $str=$str.'<a href="/admin/member/publish/prename/'.$tj_username.'/pos/'.$i.'"><font color="#0f743c"><strong>注册</strong></font></a>';
						$str=$str.'</td>  </tr></table>';
					}
					$rs1=Db::name('member')->where('user_number',$rs["user_number"])->find();
					$str=$str.'<div style="display:" id="table_'.$rs["id"].'">';
					if($rs1)
					{
						$str=$str.'<table align="center" border="0" cellspacing="0" cellpadding="0">
									  <tr>
										<td align="center"><img style="WIDTH: 1px; HEIGHT: 20px" alt="" src="/static/admin/images/line.gif" border="0" /></td>
									  </tr>
									</table>';
					
					$this->tree($rs["user_number"],$dtp,$dtp1+1);
					}
					$i++;
				}
                
				$str=$str.'</div></td>';
			}
			$str=$str.'</tr></table>';
		}
		return $str;
	 }

	 /**
	  * 网状关系图
	  */
	function meshPlots(){
		return $this->fetch();
	}
 }