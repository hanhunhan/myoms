<?php
//引入文件
if (is_file(dirname(dirname(__FILE__)).'/newWorkFlow.php')){
	include dirname(dirname(__FILE__)).'/newWorkFlow.php';
}else {
	die('Sorry. Not load newWorkFlow file.');
}
/**
 +------------------------------------------------------------------------------
 * FlowBase 抽象类
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-05-22   $
 +------------------------------------------------------------------------------
 */
abstract class  FlowBase{
 
	public   $cType = 'pc';//客户端类型 pc  mobile

	abstract function nextstep($flowId);//办理
		 
	
    abstract function createHtml($flowId);//工作流界面
		 
	
	abstract function handleworkflow($data);//下一步
		 
	
	abstract function passWorkflow($data);//确定
		 
	
	abstract function notWorkflow($data);//否决
		 
	
	abstract function finishworkflow($data);//备案
		 
	
	abstract function createworkflow($data);//创建工作流
		 
	
	
	
	
	 
}