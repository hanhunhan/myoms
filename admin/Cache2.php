<?php
ini_set('display_errors','on');
error_reporting(E_ALL);
$cache = new fzz_cache; //var_dump($_SERVER);
//$fzz->kk = '111'; //д�뻺��
//$fzz->_set("kk",'sss',10000); //�˷����������������ͻ�����������⻺������
//print_r($fzz->kk);  //��ȡ����
//print_r($fzz->get("kk"));
//unset($fzz->kk); //ɾ������
//$fzz->_unset("kk");
//var_dump(isset($fzz->kk)); //�жϻ����Ƿ����
//$fzz->_isset("kk");
//$fzz->clear(); //������ڻ���
//$fzz->clear_all(); //�������л����ļ�
//$ff = $fzz->_get("kk" ); 
//var_dump($ff );
class fzz_cache{
var $limit_time = 20000; //�������ʱ��
var $cache_dir = "cache"; //�����ļ�����Ŀ¼
function file_put_contents_old($filename,$word){
	$fh = fopen($filename, "w"); //w�ӿ�ͷд�� a׷��д��
	fwrite($fh, $word);
	fclose($fh);
}
//д�뻺��
function __set($key , $val){
$this->_set($key ,$val);
}
//����������Ϊ����ʱ��
function _set($key ,$val,$limit_time=null){  
$limit_time = $limit_time ? $limit_time : $this->limit_time;
$file = $this->cache_dir."/".$key.".cache";
$val = serialize($val);
 $this->file_put_contents_old($file,$val) or $this->error(__line__,"fail to write in file");
 @chmod($file,0777);
 @touch($file,time()+$limit_time) or $this->error(__line__,"fail to change time");
}

//��ȡ����
function __get($key){
return $this->_get($key);
}
function _get($key){
$file = $this->cache_dir."/".$key.".cache";
if (@filemtime($file)>=time()){
return unserialize(file_get_contents($file));
}else{
@unlink($file) or $this->error(__line__,"fail to unlink");
return false;
}
}

//ɾ�������ļ�
function __unset($key){
return $this->_unset($key);
}
function _unset($key){
if (@unlink($this->cache_dir."/".$key.".cache")){
return true;
}else{
return false;
}
}

//��黺���Ƿ���ڣ���������Ϊ������
function __isset($key){
return $this->_isset($key);
}
function _isset($key){
$file = $this->cache_dir."/".$key.".cache";
if (@filemtime($file)>=time()){
return true;
}else{
@unlink($file) ;
return false;
}
}

//������ڻ����ļ�
function clear(){
$files = scandir($this->cache_dir);
foreach ($files as $val){
if (filemtime($this->cache_dir."/".$val)<time()){
@unlink($this->cache_dir."/".$val);
}
}
}

//������л����ļ�
function clear_all(){
$files = scandir($this->cache_dir);
foreach ($files as $val){
@unlink($this->cache_dir."/".$val);
}
}

function error($msg,$debug = false) {
 
}
}
?>
