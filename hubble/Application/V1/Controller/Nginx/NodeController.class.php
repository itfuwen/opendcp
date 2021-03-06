<?php
/*
 *  Copyright 2009-2016 Weibo, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */
/**
 * Created by PhpStorm.
 * User: yabo
 * Date: 16/9/1
 * Time: 下午3:52
 */
namespace V1\Controller\Nginx;


use Common\Dao\Nginx\NodeModel;
use Common\Dao\Nginx\UnitModel;
use Think\Controller\RestController;

class NodeController extends RestController
{

    public function __construct()
    {
        parent::__construct();
        $ret = hubble_middle_layer();
        if(!$ret[0])
            $this->ajaxReturn(std_error($ret[1]));
    }

    public function _empty(){ $this->response('404','', 404); }


    /*
     * 添加
     * @param string  ips  IP
     * @param int  unit_id   单元ID
     * @return mixed
     * 成功  int 0
     * 失败  int 1
     */
    public function add_post(){

        $ips = I('ips','');
        $unit_id = I('unit_id',0);
        $user = I('user','');

        if(empty($ips) || !is_string($ips)){
            $this->ajaxReturn(std_error('ips is empty'));
        }


        if($unit_id <= 0 ){
            $this->ajaxReturn(std_error('unit_id error'));
        }

        if(empty($user)){
            $this->ajaxReturn(std_error('user is empty'));
        }

        //检查是否存在单元
        $unit = new UnitModel() ;

        $filter = [];
        $filter['id'] = $unit_id;
        $ret = $unit->existsUnit($filter);

        if($ret['code'] != 0){
            $this->ajaxReturn(std_error($ret['msg']));
        }
        //添加
        $ip = array_unique(explode(",",$ips));
        $data = array();

        foreach($ip as $v ){
            $data[] = $v;
        }
        $filer = [];
        $arr = [];

        $filer['ip'] = ['in' , $data];
        $node = new NodeModel() ;
        //检查
        $check = $node->existsNode($filer);
        //错误
        if($check['code'] == 2){
            $this->ajaxReturn(std_error($ret['msg']));
        }
        //存在
        if($check['code'] == 0){
            foreach($check['content'] as $v ){
                $arr[] = $v['ip'];
            }
        }

        //判断diff
        $diff = array_diff($data,$arr) ;
        $intersect = array_intersect($data,$arr);

        if(empty($diff)){
            $msg = "exits:".implode(",",$intersect);
            $this->ajaxReturn(std_error($msg));
        }
        $ret = $node->addNode($unit_id,$user,array_diff($data,$arr));

        if($ret['code'] == 1){
            $this->ajaxReturn(std_error($ret['msg']));
        }

        hubble_oprlog('Nginx', 'Add node', I('server.HTTP_APPKEY'), $user, "ips:".json_encode($data).'exits:'.json_encode($arr));
        if(empty($intersect)){
            $this->ajaxReturn(std_return($ret['msg']));
        }else{
            $msg = $ret['msg']." exits:".implode(",",$intersect);
            $this->ajaxReturn(std_return(array(),$msg));
        }



    }

    public function detail_get(){
        $id = I('id',0);

        if($id <= 0){
            $this->ajaxReturn(std_error('id error'));
        }
        $node = new NodeModel() ;
        $ret = $node->getDetail($id);

        if($ret['code'] == 1){
            $this->ajaxReturn(std_error($ret['msg']));
        }
        $this->ajaxReturn(std_return($ret['content']));

    }

    /*
     * 删除节点
     * @param int  unit_id    单元ID
     * @return mixed
     * 成功  int 0
     * 失败  int 1
     */
    public function delete_delete(){
        $uid = I('unit_id',0);
        $nodes = I('nodes','');
        $user = I('user','');

        if($uid <= 0 ){
            $this->ajaxReturn(std_error('unit_id error'));
        }

        if(empty($nodes)  ){
            $this->ajaxReturn(std_error('nodes is empty'));
        }

        if(empty($user)){
            $this->ajaxReturn(std_error('user is empty'));
        }

        $filter = [];
        $where = [];
        $where['id'] = ['in',$nodes];

        //添加
        $filter['unit_id'] = $uid;
        $node = new NodeModel() ;

        $ret = $node->deleteNode($filter,$where);

        if($ret['code'] == 1){
            $this->ajaxReturn(std_error($ret['msg']));
        }
        hubble_oprlog('Nginx', 'del node', I('server.HTTP_APPKEY'), $user, "id:unit_id  nodes:".json_encode($nodes));
        $this->ajaxReturn(std_return($ret['msg']));


    }
    /*
     * 分页
     * @param int  page  页数
     * @param int  limit   分页数量
     * @param int  unit_id    单元ID
     * @return mixed
     * 成功  int 0
     * 失败  int 1
     */
    public function list_get(){

        $page = I('page',1);
        $limit = I('limit',20);
        $unit_id = I('unit_id',0 );
        $ip = I('ip','' );
        $like = I('like',true);
        $filter = [];

        if($page <= 0){
            $this->ajaxReturn(std_error('page error'));
        }

        if($limit <= 0){
            $this->ajaxReturn(std_error('limit error'));
        }

        if($unit_id <= 0 ){
            $this->ajaxReturn(std_error('unit_id error'));
        }

        $filter['unit_id'] = $unit_id;

        if(!empty($ip)){
            $filter['ip'] = $ip;
        }

        $node = new NodeModel();

        $ret = $node->listNode($filter,$page,$limit,$like);

        if($ret['code'] == 1){
            $this->ajaxReturn(std_error($ret['msg']));
        }
        $this->ajaxReturn(std_return($ret['content']));

    }



}
