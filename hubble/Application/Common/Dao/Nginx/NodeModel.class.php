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
 * Time: 下午2:31
 */
namespace Common\Dao\Nginx;
class NodeModel{
    private $table;

    public function __construct()
    {
        $this->table= M('NginxNode');
    }

    //添加分组  ips array()
    public function addNode($uid,$user,$data){
        $arr = [] ;
        foreach($data as $v ){
            $arr[] = ['ip'=>$v,'unit_id'=>$uid,'opr_user'=>$user,'create_time'=>date("Y-m-d H:i:s") ];
        }
        $ret = $this->table->addAll($arr);
        if($ret === false){
            hubble_log(HUBBLE_ERROR, $this->table->getLastSql().' ERROR: '. $this->table->getDbError());
            return array('code'=>1 ,'msg'=>"db error: {$this->table->getDbError()}") ;
        }
        return array('code'=>0,'msg'=>'success');

    }

    public function getDetail($id){

        $ret = $this->table
            ->where(['id' => $id])
            ->find();

        $return = ['code' => 0, 'msg' => 'success', 'content' => ''];
        if($ret === NULL){
            $return['code'] = HUBBLE_RET_NULL;
            $return['msg'] = 'no such content';
        } elseif($ret === false) {
            $return['code'] = HUBBLE_DB_ERR;
            $return['msg'] = 'db error: '.$this->table->getDbError();
            hubble_log(HUBBLE_ERROR, $this->table->getLastSql().' ERROR: '. $this->table->getDbError());
        } else{
            $return['content'] = $ret;
        }
        return $return;
    }

    //删除分组
    public function deleteNode($filter,$where){

        $ret = $this->table->where($filter)->where($where)->delete();
        if($ret === false){
            hubble_log(HUBBLE_ERROR, $this->table->getLastSql().' ERROR: '. $this->table->getDbError());
            return array('code'=>1 ,'msg'=>"db error: {$this->table->getDbError()}") ;
        }

        return array('code'=>0,'msg'=>"success");

    }
    //查询分组
    public function existsNode($filter){

        $ret = $this->table->where($filter)->select();
        if($ret === false){
            hubble_log(HUBBLE_ERROR, $this->table->getLastSql().' ERROR: '. $this->table->getDbError());
            return array('code'=>2 ,'msg'=>"db error: {$this->table->getDbError()}") ;
        }
        if(empty($ret)){
            return array('code'=>1 ,'msg'=>"no such content") ;
        }
        return array('code'=>0,'msg'=>"success",'content'=>$ret);

    }

    //查询
    public function listNode($filter,$page,$limit,$like=true){

        $return = array('code'=>0,'msg'=>'success');
        foreach($filter as $k => $v ){
            if($k == 'ip' && $like){
                $filter[$k] = ['LIKE',"%$v%"];
            }
        }

        //数量

        $count = $this->table->where($filter)->count();

        //页数
        $total_page = ceil($count/$limit);
        //数据

        $ret  = $this->table->where($filter)->page($page, $limit)->select();

        if($ret === false){
            hubble_log(HUBBLE_ERROR, $this->table->getLastSql().' ERROR: '. $this->table->getDbError());
            return array('code'=>1 ,'msg'=>"db error: {$this->table->getDbError()}") ;
        }


        $return['content']['count'] = $count;
        $return['content']['page'] = $page;
        $return['content']['limit'] = $limit;
        $return['content']['total_page'] = $total_page;
        $return['content']['content'] = $ret;

        return  $return;


    }

    public function getNodeIpsByGroupId($groupId){

        $sql = "SELECT ip FROM tbl_hubble_nginx_node WHERE unit_id IN (SELECT id FROM tbl_hubble_nginx_unit WHERE group_id = '$groupId')";

        $return = ['code' => 0, 'msg' => 'success', 'content' => ''];

        $node = M();
        $ret = $node->query($sql);

        if($ret === false){
            $return['code'] = 1;
            $return['msg'] = 'db error: '. $node->getDbError();
        }elseif(empty($ret)){
            $return['code'] = 2;
            $return['msg'] = 'no such content';
        }else {
            $return['content'] = array_map(function($i){return $i['ip'];}, $ret);
        }
        return $return;

    }

    public function getNodeIpsByUnitIds($unitId){

        $ret = $this->table->field('ip')->where(['unit_id' => ['IN', $unitId]])->select();
        $return = ['code' => 0, 'msg' => 'success', 'content' => ''];

        if($ret === false){
            $return['code'] = 1;
            $return['msg'] = 'db error: '. $this->table->getDbError();
        }elseif(empty($ret)){
            $return['code'] = 2;
            $return['msg'] = 'no such content';
        }else {
            $return['content'] = array_map(function($i){return $i['ip'];}, $ret);
        }
        return $return;

    }

}
