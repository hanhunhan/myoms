<?php
//�����ļ�
if (is_file(dirname(dirname(__FILE__)).'/newWorkFlow.php')){
	include dirname(dirname(__FILE__)).'/newWorkFlow.php';
}else {
	die('Sorry. Not load newWorkFlow file.');
}
/**
 +------------------------------------------------------------------------------
 * FlowBase ������
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-05-22   $
 +------------------------------------------------------------------------------
 */
abstract class  FlowBase{
 
	public   $cType = 'pc';//�ͻ������� pc  mobile

	abstract function nextstep($flowId);//����
		 
	
    abstract function createHtml($flowId);//����������
		 
	
	abstract function handleworkflow($data);//��һ��
		 
	
	abstract function passWorkflow($data);//ȷ��
		 
	
	abstract function notWorkflow($data);//���
		 
	
	abstract function finishworkflow($data);//����
		 
	
	abstract function createworkflow($data);//����������
		 
	
	
	
	
	 
}