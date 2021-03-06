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
header('Content-type: application/json');
include_once('../include/config.inc.php');
include_once('../include/function.php');
include_once('../include/login.php');
$thisClass = $login;

class myself{

  function login($param = array()){
    global $thisClass;
    $ret=array('code' => 1, 'msg' => 'Illegal Request', 'ret' => '');
    if($arrList = $thisClass->userAuth($param)){
        $ret = array(
          'code' => 0,
          'msg' => 'success',
          'content' => $arrList,
        );
    }else{
      $ret['msg']='auth failed';
      $ret['content']=$arrList;
    }
    return $ret;
  }

  function logout(){
    global $thisClass;
    $thisClass->userLogout();
    return true;
  }

}
$mySelf=new myself();

/*权限检查*/
$pageForSuper = false;//当前页面是否需要管理员权限
$hasLimit = ($pageForSuper)?isSuper($myUser):true;
$myAction = (isset($_POST['action'])&&!empty($_POST['action']))?trim($_POST['action']):((isset($_GET['action'])&&!empty($_GET['action']))?trim($_GET['action']):'');
$myPage = (isset($_POST['page'])&&intval($_POST['page'])>0)?intval($_POST['page']):((isset($_GET['page'])&&intval($_GET['page'])>0)?intval($_GET['page']):1);
$myPageSize = (isset($_POST['pagesize'])&&intval($_POST['pagesize'])>0)?intval($_POST['pagesize']):((isset($_GET['pagesize'])&&intval($_GET['pagesize'])>0)?intval($_GET['pagesize']):$myPageSize);

$myJson=(isset($_POST['data'])&&!empty($_POST['data']))?trim($_POST['data']):((isset($_GET['data'])&&!empty($_GET['data']))?trim($_GET['data']):'');
$arrJson=($myJson)?json_decode($myJson,true):array();
$logJson=$arrJson;
if(isset($logJson['pass'])) $logJson['pass']=md5('z_'+$logJson['pw']);

//记录操作日志
$logFlag = true;
$logDesc = '';
$arrRecodeLog=array(
  't_time' => date('Y-m-d H:i:s'),
  't_user' => '',
  't_module' => '登录登出',
  't_action' => '',
  't_desc' => 'Resource:' . $_SERVER['REMOTE_ADDR'] . '.',
  't_code' => '传入：' . json_encode($logJson) . "\n\n",
);
//返回
$retArr = array(
  'code' => 1,
  'action' => $myAction,
);
if($hasLimit){
  $retArr['msg'] = 'Param Error!';
  switch($myAction){
    case 'login':
      $arrRecodeLog['t_action'] = 'Login';
      $arrRecodeLog['t_user']=(isset($arrJson['user'])&&!empty($arrJson['user']))?$arrJson['user']:'undefined';
      if(isset($arrJson) && !empty($arrJson)){
        $retArr = $mySelf->login($arrJson);
        $logDesc = (isset($retArr['code']) && $retArr['code'] == 0) ? 'SUCCESS' : 'FAILED';
        $arrRecodeLog['t_desc'] = $logDesc.', '.$arrRecodeLog['t_desc'];
      }
      $arrRecodeLog['t_code'] .= '返回：' . json_encode($retArr,JSON_UNESCAPED_UNICODE);
    break;
    case 'logout':
      session_start();
      $myUser   = @$_SESSION['open_user'];
      $arrRecodeLog['t_action'] = 'Logout';
      $arrRecodeLog['t_user']=(isset($myUser))?$myUser:'undefined';
      if($mySelf->logout()){
        $retArr['code']=0;
        $retArr['msg']='success';
        $logDesc='SUCCESS';
      }else{
        $retArr['msg']='failed';
        $logDesc='FAILED';
      }
      $arrRecodeLog['t_code'] .= '返回：' . json_encode($retArr,JSON_UNESCAPED_UNICODE);
    break;
  }
}else{
  $retArr['msg'] = 'Permission Denied!';
}
//记录日志
if($logFlag){
  if(empty($arrRecodeLog['t_action'])) $arrRecodeLog['t_action'] = $myAction;
  logRecord($arrRecodeLog);
}
//返回结果
if(isset($retArr['action']) && !empty($retArr['action'])) $retArr['action'] = $myAction;
if(isset($retArr['ret'])) unset($retArr['ret']);
echo json_encode($retArr, JSON_UNESCAPED_UNICODE);
?>
