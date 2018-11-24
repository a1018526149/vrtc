<?php
namespace app\admin\controller;
use \think\Db;

class Coupon extends Permissions 
{
    function index(){
        $coupon=Db::name('coupon')->select();
        $this->assign('coupon',$coupon);
        return $this->fetch();
    }
    function publish(){
        $id=$this->request->param('id');
        if($id){
            $coupon=Db::name('coupon')->where('id',$id)->find();
            $this->assign('coupon',$coupon);
        }
        $level=Db::name('level_config')->select();
        $grade=Db::name('grade')->select();
        $this->assign('level',$level);
        $this->assign('grade',$grade);
        return $this->fetch();
    }
    function publishApi(){
        $id=$this->request->param('id');
        $data=$this->request->post();
        $allowsFields=[
            'name',
            'thumb',
            'term',
            'grade',
            'level',
            'sum'
        ];
        if($id){
            $updateData=[];
            foreach($data as $key => $value){
                if(in_array($key,$allowsFields)){
                    $updateData[$key]=$value;
                }
            }
            if(Db::name('coupon')->where('id',$id)->update($updateData)){
                $this->success('修改成功','admin/coupon/index');
            }else{
                $this->error('修改失败');
            }
        }else{
            $insertData=[];
            foreach($data as $k => $v ){
                if(in_array($k,$allowsFields)){
                    $insertData[$k]=$v;
                }
            }
            $insertData['create_time']=time();
            if(Db::name('coupon')->insert($insertData)){
                $this->success('添加成功','admin/coupon/index');
            }else{
                $this->error('添加失败');
            }
        }
    }
}