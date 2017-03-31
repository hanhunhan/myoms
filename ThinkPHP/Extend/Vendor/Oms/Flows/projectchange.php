<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
	include dirname(__FILE__).'/FlowBase.php';
}else {
	die('Sorry. Not load FlowBase file.');
}
/**
 +------------------------------------------------------------------------------
 * projectchange���̳���
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
     * ���캯�� ȡ��ģ�����ʵ��
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
    public function createHtml($flowId){//����������

		return $this->workflow->createHtml($flowId);
		 
	}
	public function handleworkflow($data){//��һ��
		$this->model->startTrans();
		$str = $this->workflow->handleworkflow($data);
		if ($str) {
			$this->model->commit();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","���������̰���ɹ�");
			if($this->cType=='pc') $result =1;//js_alert('����ɹ�', U('Flow/through'));
			else $result =2;//js_alert('����ɹ�' );
			
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","���������̰���ʧ��");
			$result = -2;
			//js_alert('����ʧ��');
			
		}
		return $result; 
	}
	public function passWorkflow($data){//ȷ��
		$this->model->startTrans();
		$str = $this->workflow->passWorkflow($data);

		if ($str) {
			$this->model->commit();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","����������ͬ��ɹ�");
			if($this->cType=='pc') $result = 3;//js_alert('ͬ��ɹ�', U('Flow/through'));
			else  $result = 4;//js_alert('ͬ��ɹ�' );
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","����������ͬ��ʧ��");
			//js_alert('ͬ��ʧ��');
			$result = -3;
			
		}
		return $result;  
	}
	public function notWorkflow($data){//���
		$this->model->startTrans();
		$str = $this->workflow->notWorkflow($data); 
		if ($str) {
			$this->model->commit();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","���������̷���ɹ�");
			if($this->cType=='pc') $result = 5;//js_alert('����ɹ�', U('Flow/already'));
			else $result = 6;//js_alert('����ɹ�' );
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","���������̷��ʧ��");
			//js_alert('���ʧ��');
			$result = -4;
		}
		return $result;  
	}
	public function finishworkflow($data){//����
		$auth = $this->workflow->flowPassRole($data['flowId']);
		if (!$auth) {
			//js_alert('δ�����ؾ���ɫ');
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

			//����project ����
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
			$res = $project_model->set_project_change($prjId);//����������ͳ��

			//֪ͨȫ����ϵͳ����
			$t = api_log($_SESSION['uinfo']['city'],__APP__ . '/Api/getOneProInfo&pID=' . $prjId,0,$_SESSION['uinfo']['uid'],4);

			$this->model->commit();
			//var_dump($res);
				//$this->UserLog->writeLog($data['flowId'],"__APP__","���������̱����ɹ�");
				if($this->cType=='pc')  $result = 7;//js_alert('�����ɹ�', U('Flow/already'));
				else $result = 8;//js_alert('�����ɹ�' );
				
			//}
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","���������̱���ʧ��");
			//js_alert('����ʧ��');
			$result = -6;
			
		}
		return $result; 
	}
	public function createworkflow($data){//����������
		$auth = $this->workflow->start_authority('xiangmujuesuan');
		if (!$auth) {
			//js_alert("����Ȩ��");
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
						//js_alert('�����ظ��ύŶ', U('House/opinionFlow', $this->_merge_url_param));
						$result = -8;
					} else {
						$_REQUEST['type'] = 'lixiangbiangeng';
						$this->model->startTrans();
						$str = $this->workflow->createworkflow($_REQUEST);
						if ($str) {
							$this->model->commit();
							//$this->UserLog->writeLog($data['flowId'],"__APP__","�����������ύ�ɹ�");
							//js_alert('�ύ�ɹ�', U('House/opinionFlow', $this->_merge_url_param));
							$result = 9;
							
						} else {
							$this->model->rollback();
							//$this->UserLog->writeLog($data['flowId'],"__APP__","�����������ύʧ��");
							$result = -9; 
							//js_alert('�ύʧ��', U('House/opinionFlow', $this->_merge_url_param));
							
						}
					}

				}
			}
		}
		return $result;
		 
	}

	
	
	
	 
}