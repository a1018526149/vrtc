<?php
/**
 * 留言
 * @author 李财 2018/12/3
 */
 namespace app\index\controller;

 use \think\Db;
 use \think\Session;
 use \think\Request;
 class Message extends Index 
 {
    // 留言页面
    function index(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $this->assign('member',$member);
        return $this->fetch();
    }
    // 提交留言接口
    function pushMessage(){
        if(Request::instance()->isPost()){
            $id=Session::get('member')['id'];
            if(!$id){
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='请先登录';
                return json($this->returnMessage);die;
            }
            $data=$this->request->post();
            $insertData=[
                'user_id'=>$id,
                'message'=>$data['text'],
                'title'=>$data['title'],
                'thumb'=>$data['image'],
                'create_time'=>time(),
                'ip'=>$this->request->ip()
            ];
            if(Db::name('messages')->insert($insertData)==1){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='提交成功';
            }else{
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='提交失败，请稍后再试';
            }
        }
        return json($this->returnMessage);
    }
    // 留言列表
    function messageList(){
        $id=Session::get('member')['id'];
        $member=Db::name('member')->where('id',$id)->find();
        unset($member['user_password']);
        $this->assign('member',$member);
        $messages=Db::name('messages')->where('user_id',$id)->order('id desc')->paginate(10);
        // $messages['title']=substr($messages['title'],0,4);
        $this->assign('messages',$messages);
        return $this->fetch();
    }
    // 删除留言
    function deleteMsg(){
        if(!Request::instance()->isAjax()){
            $this->returnMessage['code']='error';
            $this->returnMessage['message']='非法请求！';
        }else{
            $id=$this->request->post('id');
            if(Db::name('messages')->where('id',$id)->delete()==1){
                $this->returnMessage['code']='success';
                $this->returnMessage['message']='删除成功';
            }else{
                $this->returnMessage['code']='error';
                $this->returnMessage['message']='删除失败';
            }
        }
        return json($this->returnMessage);
    }
 }