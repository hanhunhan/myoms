<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
	include dirname(__FILE__).'/FlowBase.php';
}else {
	die('Sorry. Not load FlowBase file.');
}
/**
 +------------------------------------------------------------------------------
 * lixiangshenqing���̳���
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
			//$this->UserLog->writeLog($data['flowId'],"__APP__","��Ŀ��ֹ���̰���ɹ�");
			//if($this->cType=='pc')js_alert('����ɹ�', U('Flow/through'));
			//else js_alert('����ɹ�' );
			$result =  2;
			
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","��Ŀ��ֹ���̰���ʧ��");
			//js_alert('����ʧ��');
			$result = -2;
			
		}
		return $result;
		 
	}
	public function passWorkflow($data){//ȷ��
		$this->model->startTrans();
		$str = $this->workflow->passWorkflow($data);

		if ($str) {
			$this->model->commit();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","��Ŀ��ֹ����ͬ��ɹ�");
			//if($this->cType=='pc') js_alert('ͬ��ɹ�', U('Flow/through'));
			//else js_alert('ͬ��ɹ�' );
			$result = 3;
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","��Ŀ��ֹ����ͬ��ʧ��");
			//js_alert('ͬ��ʧ��');
			$result = -3;
			
		}
		return $result; 
	}
	public function notWorkflow($data){//���
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
				//$this->UserLog->writeLog($data['flowId'],"__APP__","��Ŀ��ֹ���̷���ɹ�");
				//if($this->cType=='pc') js_alert('����ɹ�', U('Flow/already'));
				//else js_alert('����ɹ�' );
				$result = 4;
				
			}else{
				$this->model->rollback();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","��Ŀ��ֹ���̷��ʧ�� ���ݲ���ʧ��");
				//js_alert('���ʧ�� ���ݲ���ʧ��');
				$result = -4;
				
			}
		} else {
			//js_alert('���ʧ��');
			$result = -5;
			$this->model->rollback();
		}
		return $result;  
	}
	public function finishworkflow($data){//����
		$auth = $this->workflow->flowPassRole($data['flowId']);
		if (!$auth) {
			//js_alert('δ�����ؾ���ɫ');
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
					$msg = "����ϵͳ:����һ������������";
					send_sms($msg, $data['PHONE'], $data['CITY']);
				}

				if ($data['ISMALL']) {
					$subject = "����ϵͳ:����������";
					$content = "����һ������Ĺ�����,�뼰ʱ����";
					oa_notice($_SESSION['uinfo']['uid'], $data['DEAL_USERID'], $subject, $content);
				}
				//$this->UserLog->writeLog($data['flowId'],"__APP__","��Ŀ��ֹ���̱����ɹ�");
				//if($this->cType=='pc')  js_alert('�����ɹ�', U('Flow/already'));
				//else js_alert('�����ɹ�' );
				$result = 5;
			}else{
				$this->model->rollback();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","��Ŀ��ֹ���̱���ʧ��  ���ݲ���ʧ��");
				//js_alert('����ʧ��  ���ݲ���ʧ��!');
				$result = -7;
			}
		} else {
			$this->model->rollback();
			//js_alert('����ʧ��');
			$result = -8;
		}
		return $result; 
	}
	public function createworkflow($data){//����������
		$auth = $this->workflow->start_authority('xiangmujuesuan');
		if (!$auth) {
			//js_alert("����Ȩ��");
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
						//�ύ..����

						//$project_model = D('Project');
						$ress = $project_model->update_termination_check_status($recordId);
						if($ress){
							$this->model->commit();
							//$this->UserLog->writeLog($data['flowId'],"__APP__","��Ŀ��ֹ�����ύ�ɹ�");
							//if($this->cType=='pc') js_alert('�ύ�ɹ�', U('Case/opinionFlow_final',  $data['url_param'])); else js_alert('�ύ�ɹ�');
							$result = 6;
						}else{
							$this->model->rollback();
							//$this->UserLog->writeLog($data['flowId'],"__APP__","��Ŀ��ֹ�����ύʧ�� ���ݲ���ʧ��!");
							//js_alert('�ύʧ�� ���ݲ���ʧ��!');
							$result = -9;
						}

						//exit;
					} else {
						$this->model->rollback();
						$this->UserLog->writeLog($data['flowId'],"__APP__","�ύʧ��");
						//if($this->cType=='pc') js_alert('�ύʧ��', U('Case/opinionFlow_final',$data['url_param']));else js_alert('�ύʧ��');
						$result = -10;
						//exit;
					}
				} else {
					
					//if($this->cType=='pc') js_alert('�벻Ҫ�ظ��ύ', U('Case/opinionFlow_final', $data['url_param']));else js_alert('�벻Ҫ�ظ��ύ');
					$result = -11;
					//exit;
				}
			}
		}
		return $result; 
		 
	}

	
	
	
	 
}