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
	/**
     +----------------------------------------------------------
     * ���캯�� ȡ��ģ�����ʵ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($className) {
		$class = new ReflectionClass('Flows/'.$className);//���� Person�����ķ�����  
		$this->instance  = $class->newInstanceArgs();//�൱��ʵ����Person ��  
    }
	public function doit($data){
		
		if($data['savedata']) {
			if ($data['flowNext']) {
				$this->handleworkflow();
			} else if ($data['flowPass']) {
				$this->passWorkflow();
			} else if ($data['flowNot']) {
				$this->notWorkflow();
			} else if ($data['flowStop']) {
				$this->finishworkflow();
			}else {
				$this->createworkflow();
			}
		}

	}
	public function nextstep($flowId){//�������
		$this->instance->nextstep($flowId);	
	}
    public function createHtml($flowId){//����������
		
		$this->instance->createHtml($flowId);
		if($flowId)$res = $this->nextstep($flowId);
		else $res = true;
		return $res;
	}
	public function handleworkflow(){//��һ��
		$this->instance->handleworkflow();
	}
	public function passWorkflow(){//ȷ��
		$this->instance->passWorkflow();
	}
	public function notWorkflow(){//���
		$this->instance->notWorkflow();
	}
	public function finishworkflow(){//����
		$this->instance->finishworkflow();
	}
	public function createworkflow(){//����������
		$this->instance->createworkflow();
	}
	
	
	
	 
}