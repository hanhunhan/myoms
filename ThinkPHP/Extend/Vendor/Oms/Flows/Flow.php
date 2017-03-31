<?php

/**
 +------------------------------------------------------------------------------
 * FLOW���̳���
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-05-22   $
 +------------------------------------------------------------------------------
 */
class Flow{
	protected $instance = null;
	//���캯��
    public function __construct($className) {
		include_once(dirname(__FILE__).'/'.$className.'.php');
		Vendor('Oms.UserLog');
		$this->UserLog = UserLog::Init();
		$class = new ReflectionClass($className);//���� Person�����ķ�����  
		$this->instance  = $class->newInstanceArgs();//�൱��ʵ����Person ��  
    }

    /**
     * ��֤������
     * @param $data
     * @return bool
     */
    protected function validateRequest($data) {
        if ($data['flowNext'] || $data['flowPass']) {
            if (empty($data['DEAL_USERID'])) {
                return false;
            }
        }

        if (empty($data['DEAL_INFO'])) {
            return false;
        }

        return true;
    }

	public function doit($data){
        // ��֤����
        if ($this->validateRequest($data) == false) {
            return false;
        };

		if($data['savedata'] ) {
            // �滻�ı����еĻ���
            $data['DEAL_INFO'] = nl2br($data['DEAL_INFO']);
			if($data['flowId']>0){
				if($data['flowNext']){
					$this->UserLog->writeLog($data['flowId'],$_SERVER["REQUEST_URI"],"ת����һ��:" . $data['flowTypePY'],serialize($data));
					return $this->handleworkflow($data);
				}elseif($data['flowPass']){
					$this->UserLog->writeLog($data['flowId'],$_SERVER["REQUEST_URI"],"ͨ��:" . $data['flowTypePY'],serialize($data));
					return $this->passWorkflow($data);
				}elseif($data['flowNot']){
					$this->UserLog->writeLog($data['flowId'],$_SERVER["REQUEST_URI"],"���:" . $data['flowTypePY'],serialize($data));
					return $this->notWorkflow($data);
				}elseif($data['flowStop']){
					$this->UserLog->writeLog($data['flowId'],$_SERVER["REQUEST_URI"],"����:" . $data['flowTypePY'],serialize($data));
					return $this->finishworkflow($data);
				}
			}else{
                // ��־������������
                $this->UserLog->writeLog($data['recordId'], $_SERVER["REQUEST_URI"], "������������{$data['type']}", serialize($data));
				return $this->createworkflow($data);
			}
		}
		
	}
	public function nextstep($flowId){//�������
		return $this->instance->nextstep($flowId);	
	}
    public function createHtml($flowId){//����������
		if($flowId) $this->nextstep($flowId);
		$res =$this->instance->createHtml($flowId);
		return $res;
	}
	public function handleworkflow($data){//��һ��
		return $this->instance->handleworkflow($data);
	}
	public function passWorkflow($data){//ȷ��
		return $this->instance->passWorkflow($data);
	}
	public function notWorkflow($data){//���
		return $this->instance->notWorkflow($data);
	}
	public function finishworkflow($data){//����
		return $this->instance->finishworkflow($data);
	}
	public function createworkflow($data){//����������
		return $this->instance->createworkflow($data);
	}
	public function setcType($val){
		$this->instance->cType=$val;

	}
	
	
	
	 
}