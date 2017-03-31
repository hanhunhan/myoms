<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
	include dirname(__FILE__).'/FlowBase.php';
}else {
	die('Sorry. Not load FlowBase file.');
}
/**
 +------------------------------------------------------------------------------
 * lixiangshenqing流程成类
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-05-22   $
 +------------------------------------------------------------------------------
 */
class Termination extends   FlowBase{
	 
	protected $workflow = null;//
	 
	/**
     +----------------------------------------------------------
     * 构造函数 取得模板对象实例
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct() {
		$this->workflow = new newWorkFlow(); 
		$this->model = new Model();
		Vendor('Oms.UserLog');
		$this->UserLog = UserLog::Init();
    }
	public function nextstep($flowId){
		return $this->workflow->nextstep($flowId);
	}
    public function createHtml($flowId){//工作流界面

		return $this->workflow->createHtml($flowId);
		 
	}
	public function handleworkflow($data){//下一步
		$this->model->startTrans();
		$str = $this->workflow->handleworkflow($data);
		if ($str) {
			$this->model->commit();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","项目终止流程办理成功");
			//if($this->cType=='pc')js_alert('办理成功', U('Flow/through'));
			//else js_alert('办理成功' );
			$result =  2;
			
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","项目终止流程办理失败");
			//js_alert('办理失败');
			$result = -2;
			
		}
		return $result;
		 
	}
	public function passWorkflow($data){//确定
		$this->model->startTrans();
		$str = $this->workflow->passWorkflow($data);

		if ($str) {
			$this->model->commit();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","项目终止流程同意成功");
			//if($this->cType=='pc') js_alert('同意成功', U('Flow/through'));
			//else js_alert('同意成功' );
			$result = 3;
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","项目终止流程同意失败");
			//js_alert('同意失败');
			$result = -3;
			
		}
		return $result; 
	}
	public function notWorkflow($data){//否决
		$this->model->startTrans();
		$str = $this->workflow->notWorkflow($data);
		if ($str) {
			$project_model = D('Project');
			$recordId = !empty($data['recordId']) ?
            intval($data['recordId']) : 0;
			$project_model = D('Project');
            $res =  $project_model->update_termination_nopass_status($recordId);
			if($res){
				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","项目终止流程否决成功");
				//if($this->cType=='pc') js_alert('否决成功', U('Flow/already'));
				//else js_alert('否决成功' );
				$result = 4;
				
			}else{
				$this->model->rollback();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","项目终止流程否决失败 数据操作失败");
				//js_alert('否决失败 数据操作失败');
				$result = -4;
				
			}
		} else {
			//js_alert('否决失败');
			$result = -5;
			$this->model->rollback();
		}
		return $result;  
	}
	public function finishworkflow($data){//备案
		$auth = $this->workflow->flowPassRole($data['flowId']);
		if (!$auth) {
			//js_alert('未经过必经角色');
			$result = -6;
			return $result;
		}
		$this->model->startTrans();
		$str = $this->workflow->finishworkflow($data);
		if ($str) {
			$recordId = !empty($data['recordId']) ?
            intval($data['recordId']) : 0;
			$project_model = D('Project');
			$ress = $project_model->update_termination_status($recordId);
			// echo $prjId;var_dump($ress);
			if($ress){
				$this->model->commit();
				if ($data['ISPHONE'] && $data['PHONE']) {
					$msg = "经管系统:你有一条工作流待办";
					send_sms($msg, $data['PHONE'], $data['CITY']);
				}

				if ($data['ISMALL']) {
					$subject = "经管系统:工作流待办";
					$content = "你有一条待办的工作流,请及时处理";
					oa_notice($_SESSION['uinfo']['uid'], $data['DEAL_USERID'], $subject, $content);
				}
				//$this->UserLog->writeLog($data['flowId'],"__APP__","项目终止流程备案成功");
				//if($this->cType=='pc')  js_alert('备案成功', U('Flow/already'));
				//else js_alert('备案成功' );
				$result = 5;
			}else{
				$this->model->rollback();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","项目终止流程备案失败  数据操作失败");
				//js_alert('备案失败  数据操作失败!');
				$result = -7;
			}
		} else {
			$this->model->rollback();
			//js_alert('备案失败');
			$result = -8;
		}
		return $result; 
	}
	public function createworkflow($data){//创建工作流
		$auth = $this->workflow->start_authority('xiangmujuesuan');
		if (!$auth) {
			//js_alert("暂无权限");
		}
		$form = $this->workflow->createHtml();

		if ($data['savedata']) {
			$recordId = !empty($data['recordId']) ?
            intval($data['recordId']) : 0;
			$prjId = $data['prjid'] ? $data['prjid'] : $data['CASEID'];
			if ($recordId) {
				$project_model = D('Project');
				$fstatus = $project_model->get_finalaccounts_status($recordId);
				if ($fstatus == 0 || $fstatus == 3) {

					$flow_data['type'] = 'xiangmuzhongzhi';//$type;
					$flow_data['CASEID'] = $prjId;
					$flow_data['RECORDID'] = $recordId;
					$flow_data['INFO'] = strip_tags($data['INFO']);
					$flow_data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
					$flow_data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
					$flow_data['DEAL_USERID'] = intval($data['DEAL_USERID']);
					$flow_data['FILES'] = $data['FILES'];
					$flow_data['ISMALL'] = intval($data['ISMALL']);
					$flow_data['ISPHONE'] = intval($data['ISPHONE']);
					$this->model->startTrans();
					$str = $this->workflow->createworkflow($flow_data);

					if ($str) {
						//提交..申请

						//$project_model = D('Project');
						$ress = $project_model->update_termination_check_status($recordId);
						if($ress){
							$this->model->commit();
							//$this->UserLog->writeLog($data['flowId'],"__APP__","项目终止流程提交成功");
							//if($this->cType=='pc') js_alert('提交成功', U('Case/opinionFlow_final',  $data['url_param'])); else js_alert('提交成功');
							$result = 6;
						}else{
							$this->model->rollback();
							//$this->UserLog->writeLog($data['flowId'],"__APP__","项目终止流程提交失败 数据操作失败!");
							//js_alert('提交失败 数据操作失败!');
							$result = -9;
						}

						//exit;
					} else {
						$this->model->rollback();
						$this->UserLog->writeLog($data['flowId'],"__APP__","提交失败");
						//if($this->cType=='pc') js_alert('提交失败', U('Case/opinionFlow_final',$data['url_param']));else js_alert('提交失败');
						$result = -10;
						//exit;
					}
				} else {
					
					//if($this->cType=='pc') js_alert('请不要重复提交', U('Case/opinionFlow_final', $data['url_param']));else js_alert('请不要重复提交');
					$result = -11;
					//exit;
				}
			}
		}
		return $result; 
		 
	}

	
	
	
	 
}