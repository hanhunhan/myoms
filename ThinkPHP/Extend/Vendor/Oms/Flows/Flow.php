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
	//构造函数
    public function __construct($className) {
		include_once(dirname(__FILE__).'/'.$className.'.php');
		Vendor('Oms.UserLog');
		$this->UserLog = UserLog::Init();
		$class = new ReflectionClass($className);//建立 Person这个类的反射类  
		$this->instance  = $class->newInstanceArgs();//相当于实例化Person 类  
    }

    /**
     * 验证表单数据
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
        // 验证数据
        if ($this->validateRequest($data) == false) {
            return false;
        };

		if($data['savedata'] ) {
            // 替换文本框中的换行
            $data['DEAL_INFO'] = nl2br($data['DEAL_INFO']);
			if($data['flowId']>0){
				if($data['flowNext']){
					$this->UserLog->writeLog($data['flowId'],$_SERVER["REQUEST_URI"],"转交下一步:" . $data['flowTypePY'],serialize($data));
					return $this->handleworkflow($data);
				}elseif($data['flowPass']){
					$this->UserLog->writeLog($data['flowId'],$_SERVER["REQUEST_URI"],"通过:" . $data['flowTypePY'],serialize($data));
					return $this->passWorkflow($data);
				}elseif($data['flowNot']){
					$this->UserLog->writeLog($data['flowId'],$_SERVER["REQUEST_URI"],"否决:" . $data['flowTypePY'],serialize($data));
					return $this->notWorkflow($data);
				}elseif($data['flowStop']){
					$this->UserLog->writeLog($data['flowId'],$_SERVER["REQUEST_URI"],"备案:" . $data['flowTypePY'],serialize($data));
					return $this->finishworkflow($data);
				}
			}else{
                // 日志：创建工作流
                $this->UserLog->writeLog($data['recordId'], $_SERVER["REQUEST_URI"], "创建工作流：{$data['type']}", serialize($data));
				return $this->createworkflow($data);
			}
		}
		
	}
	public function nextstep($flowId){//点击办理
		return $this->instance->nextstep($flowId);	
	}
    public function createHtml($flowId){//工作流界面
		if($flowId) $this->nextstep($flowId);
		$res =$this->instance->createHtml($flowId);
		return $res;
	}
	public function handleworkflow($data){//下一步
		return $this->instance->handleworkflow($data);
	}
	public function passWorkflow($data){//确定
		return $this->instance->passWorkflow($data);
	}
	public function notWorkflow($data){//否决
		return $this->instance->notWorkflow($data);
	}
	public function finishworkflow($data){//备案
		return $this->instance->finishworkflow($data);
	}
	public function createworkflow($data){//创建工作流
		return $this->instance->createworkflow($data);
	}
	public function setcType($val){
		$this->instance->cType=$val;

	}
	
	
	
	 
}