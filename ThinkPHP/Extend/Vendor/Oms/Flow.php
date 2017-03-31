<?php

/**
 +------------------------------------------------------------------------------
 * FLOW流程成类
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
     * 构造函数 取得模板对象实例
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($className) {
		$class = new ReflectionClass('Flows/'.$className);//建立 Person这个类的反射类  
		$this->instance  = $class->newInstanceArgs();//相当于实例化Person 类  
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
	public function nextstep($flowId){//点击办理
		$this->instance->nextstep($flowId);	
	}
    public function createHtml($flowId){//工作流界面
		
		$this->instance->createHtml($flowId);
		if($flowId)$res = $this->nextstep($flowId);
		else $res = true;
		return $res;
	}
	public function handleworkflow(){//下一步
		$this->instance->handleworkflow();
	}
	public function passWorkflow(){//确定
		$this->instance->passWorkflow();
	}
	public function notWorkflow(){//否决
		$this->instance->notWorkflow();
	}
	public function finishworkflow(){//备案
		$this->instance->finishworkflow();
	}
	public function createworkflow(){//创建工作流
		$this->instance->createworkflow();
	}
	
	
	
	 
}