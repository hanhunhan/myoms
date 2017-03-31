<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
	include dirname(__FILE__).'/FlowBase.php';
}else {
	die('Sorry. Not load FlowBase file.');
}
/**
 +------------------------------------------------------------------------------
 * projectchange流程成类
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-05-22   $
 +------------------------------------------------------------------------------
 */
class projectchange extends   FlowBase{
	 
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
			//$this->UserLog->writeLog($data['flowId'],"__APP__","立项变更流程办理成功");
			if($this->cType=='pc') $result =1;//js_alert('办理成功', U('Flow/through'));
			else $result =2;//js_alert('办理成功' );
			
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","立项变更流程办理失败");
			$result = -2;
			//js_alert('办理失败');
			
		}
		return $result; 
	}
	public function passWorkflow($data){//确定
		$this->model->startTrans();
		$str = $this->workflow->passWorkflow($data);

		if ($str) {
			$this->model->commit();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","立项变更流程同意成功");
			if($this->cType=='pc') $result = 3;//js_alert('同意成功', U('Flow/through'));
			else  $result = 4;//js_alert('同意成功' );
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","立项变更流程同意失败");
			//js_alert('同意失败');
			$result = -3;
			
		}
		return $result;  
	}
	public function notWorkflow($data){//否决
		$this->model->startTrans();
		$str = $this->workflow->notWorkflow($data); 
		if ($str) {
			$this->model->commit();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","立项变更流程否决成功");
			if($this->cType=='pc') $result = 5;//js_alert('否决成功', U('Flow/already'));
			else $result = 6;//js_alert('否决成功' );
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","立项变更流程否决失败");
			//js_alert('否决失败');
			$result = -4;
		}
		return $result;  
	}
	public function finishworkflow($data){//备案
		$auth = $this->workflow->flowPassRole($data['flowId']);
		if (!$auth) {
			//js_alert('未经过必经角色');
			$result = -5;
			exit;
		} //var_dump($data);
		$this->model->startTrans();
		$str = $this->workflow->finishworkflow($data);
		if ($str) {
			Vendor('Oms.Changerecord');
            $changer = new Changerecord();
			$prjId =  $data['CASEID']; 
			$CID = $data['recordId']; 
			$changer->setRecords($CID);

			
			//$ress =$project_model->update_finalaccounts_status($prjId);

			//更改project 名称
			$PRO_NAME = M("Erp_house")->where("PROJECT_ID = $prjId")->getField('PRO_NAME');
			$DEV_ENT = M("Erp_house")->where("PROJECT_ID = $prjId")->getField('DEV_ENT');
			$CONTRACT_NUM = M("Erp_house")->where("PROJECT_ID = $prjId")->getField('CONTRACT_NUM');
			
			$UPDATE1 = M("Erp_project")->where("ID = $prjId")->setField("PROJECTNAME", $PRO_NAME);
			$UPDATE2 = M("Erp_project")->where("ID = $prjId")->setField("CONTRACT", $CONTRACT_NUM);
			$UPDATE3 = M("Erp_project")->where("ID = $prjId")->setField("COMPANY", $DEV_ENT);
            //var_dump($res); var_dump($UPDATE1); var_dump($UPDATE2); var_dump($UPDATE3);
			//if($res && $UPDATE1 && $UPDATE2 && $UPDATE3){
				//$this->model->commit();
			$project_model = D('Project');
			$res = $project_model->set_project_change($prjId);//变更后的数据统计

			//通知全链条系统数据
			$t = api_log($_SESSION['uinfo']['city'],__APP__ . '/Api/getOneProInfo&pID=' . $prjId,0,$_SESSION['uinfo']['uid'],4);

			$this->model->commit();
			//var_dump($res);
				//$this->UserLog->writeLog($data['flowId'],"__APP__","立项变更流程备案成功");
				if($this->cType=='pc')  $result = 7;//js_alert('备案成功', U('Flow/already'));
				else $result = 8;//js_alert('备案成功' );
				
			//}
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","立项变更流程备案失败");
			//js_alert('备案失败');
			$result = -6;
			
		}
		return $result; 
	}
	public function createworkflow($data){//创建工作流
		$auth = $this->workflow->start_authority('xiangmujuesuan');
		if (!$auth) {
			//js_alert("暂无权限");
			$result = -7;
		}
		
		$form = $this->workflow->createHtml();

		if ($data['savedata']) {
			$recordId = !empty($data['recordId']) ?
            intval($data['recordId']) : 0;
			$prjId = $data['prjid'] ? $data['prjid'] : $data['CASEID'];
			if ($recordId) {
				if ($recordId) {
					$project_model = D('Project');
					$pstatus = $project_model->get_Change_Flow_Status($recordId);

					if ($pstatus == '1') {
						//js_alert('请勿重复提交哦', U('House/opinionFlow', $this->_merge_url_param));
						$result = -8;
					} else {
						$_REQUEST['type'] = 'lixiangbiangeng';
						$this->model->startTrans();
						$str = $this->workflow->createworkflow($_REQUEST);
						if ($str) {
							$this->model->commit();
							//$this->UserLog->writeLog($data['flowId'],"__APP__","立项变更流程提交成功");
							//js_alert('提交成功', U('House/opinionFlow', $this->_merge_url_param));
							$result = 9;
							
						} else {
							$this->model->rollback();
							//$this->UserLog->writeLog($data['flowId'],"__APP__","立项变更流程提交失败");
							$result = -9; 
							//js_alert('提交失败', U('House/opinionFlow', $this->_merge_url_param));
							
						}
					}

				}
			}
		}
		return $result;
		 
	}

	
	
	
	 
}