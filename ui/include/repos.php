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
class repos{

  private $domain;
  private $auth;
  private $reqid;

  public function __construct() {
    $this->domain = REPOS_DOMAIN;
    $this->auth = REOPS_AUTH;
    $this->reqid = str_replace(array('0.',' '),'',microtime());
  }

  function curl($token='', $module = '', $method = '', $data = '', $id = '') {
    if($module && $method ){
      $header = array(
        'accept: application/json',
        'Content-Type: application/json',
        'X-HTTP-Method-Override: ' . $method,
        'Token: '.$token,
        'X-CORRELATION-ID: ' . $this->reqid,
      );
      $url = $this -> domain . '/api/' . $module;
      if($method == 'GET' || $method == 'DELETE') $url.=(is_array($data))?'?'.http_build_query($data):$data;
      $handle = curl_init();
      curl_setopt($handle, CURLOPT_URL, $url);
      curl_setopt($handle, CURLOPT_HTTPHEADER, $header);
      curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($handle, CURLOPT_TIMEOUT, 10);
      curl_setopt($handle, CURLOPT_USERPWD, $this->auth);
      curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($handle, CURLOPT_HEADER, 1);
      curl_setopt($handle, CURLOPT_CUSTOMREQUEST, $method);
      if($method == 'POST'){
        curl_setopt($handle, CURLOPT_POST, 1);
        if(is_array($data)) $data=json_encode($data);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
      }
      $result = curl_exec($handle);
      $header_size = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
      $header = explode("\r\n",substr($result,0,$header_size));
      $arrHeader = array();
      foreach($header as $v){
        if(trim($v)==='') continue;
        if(strpos($v,'HTTP/')!==false){
          $arrHeader['http_code']=$v;
          continue;
        }
        $kv=strpos($v,':');
        $arrHeader[substr($v,0,$kv)]=trim(substr($v,$kv+1));
      }
      $result = substr($result, $header_size);
      if($t = json_decode($result,true)) $result = json_encode($t, JSON_UNESCAPED_UNICODE);
      $http_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
      if($http_code < 200 || $http_code >= 300){
        if($http_code == 0) $result = 'timeout';
        if($aRe=json_decode($result,true)){
          if(is_array($aRe['msg'])){
            $result=$aRe['msg'];
          }else{
            if(trim($result)=='') $result='""';
            return '{"code":1,"http_code":' . $http_code . ',"url":"' . addslashes($url) . '","msg":' . $result . ',"header":' . json_encode($arrHeader) . '}';
          }
        }
        return '{"code":1,"http_code":' . $http_code . ',"url":"' . addslashes($url) . '","msg":"' . preg_replace('/\s+/',' ',$result) . '","header":' . json_encode($arrHeader) . '}';
      }else{
        if(trim($result)=='') $result='""';
        return '{"code":0,"msg":"success","content":'.$result.',"header":' . json_encode($arrHeader) . '}';
      }
    }
    return false;
  }

  function get($token='', $module = '', $method = '', $data = '', $id = '') {
    if($ret = $this -> curl($token, $module, $method, $data, $id)) return $ret;
    return false;
  }

}

$repos=new repos();

?>
