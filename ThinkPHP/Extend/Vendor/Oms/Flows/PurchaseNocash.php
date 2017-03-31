<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
	include dirname(__FILE__).'/FlowBase.php';
}else {
	die('Sorry. Not load FlowBase file.');
}
/**
 +------------------------------------------------------------------------------
 * �Ǹ��ֳɱ�����
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-05-22   $
 +------------------------------------------------------------------------------
 */
class PurchaseNocash extends   FlowBase{
	 
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
			//$this->UserLog->writeLog($data['flowId'],"__APP__","�Ǹ��ֳɱ��������̰���ɹ�");
			if($this->cType=='pc') $result =1;//js_alert('����ɹ�', U('Flow/through'));
			else $result =2;//js_alert('����ɹ�' );
			
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","�Ǹ��ֳɱ��������̰���ʧ��");
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
			//$this->UserLog->writeLog($data['flowId'],"__APP__","�Ǹ��ֳɱ���������ͬ��ɹ�");
			if($this->cType=='pc') $result = 3;//js_alert('ͬ��ɹ�', U('Flow/through'));
			else  $result = 4;//js_alert('ͬ��ɹ�' );
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","�Ǹ��ֳɱ���������ͬ��ʧ��");
			//js_alert('ͬ��ʧ��');
			$result = -3;
			
		}
		return $result;
		 
	}
	public function notWorkflow($data){//���
		$this->model->startTrans();
		$str = $this->workflow->notWorkflow($data);//var_dump($data);
		if ($str) {
			 
			
			$this->model->commit();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","�Ǹ��ֳɱ��������̷���ɹ�");
			if($this->cType=='pc') $result = 5;//js_alert('����ɹ�', U('Flow/already'));
			else $result = 6;//js_alert('����ɹ�' );
			
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","�Ǹ��ֳɱ��������̷��ʧ��");
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
		}
		$this->model->startTrans();
		$str = $this->workflow->finishworkflow($data);

		//�Ǹ��ֳɱ�ת����Ŀ���غ�������Ŀ,�������Ͳ�һ��
		$res = $this->addIncomeCost($data);
		if ($str && $res) {
			 
			
			$this->model->commit();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","�Ǹ��ֳɱ��������̱����ɹ�");
			if($this->cType=='pc')  $result = 7;//js_alert('�����ɹ�', U('Flow/already'));
			else $result = 8;//js_alert('�����ɹ�' );
				
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","�Ǹ��ֳɱ��������̱���ʧ��");
			//js_alert('����ʧ��');
			$result = -6;
			
		}
		return $result; 
	}
	public function createworkflow($data){//����������
		 
		
		$form = $this->workflow->createHtml();

		if ($data['savedata']) {

			$nonCashCostId = $_REQUEST['recordId'];
            $nonCashCostIds = explode('-', $nonCashCostId);
			$this->model->startTrans();
			if($this->addNonCashCostWorkFlow($this->workflow, 1, $nonCashCostIds, $data)) {
				// ��������ӳɹ�
				//js_alert('�ύ�ɹ�', U('Purchase/opinionFlow', $this->_merge_url_param));
				//exit;
				$this->model->commit();
				$result = 9;
			} else {
				// �����������ʧ��
				//js_alert('�ύʧ��', U('Purchase/opinionFlow', $this->_merge_url_param));
				//exit;
				$result = -7;
				$this->model->rollback();
			}
			 
		}
		return $result;
		 
	}

	private function addNonCashCostWorkFlow(&$workflow, $activid, $nonCashCostIds, $post = null) {
        if (empty($nonCashCostIds) || !$nonCashCostIds) {
            return false;
        }
        $itsModel = D('NonCashCost');
        $can_continue = true;
        foreach($nonCashCostIds as $key => $nonCashCostId)
        {
            if($key == 0)
                $RECORDID = $nonCashCostId;
            $itsRecord = $itsModel->where('ID = ' . $nonCashCostId)->find();
            if(!is_array($itsRecord) || !count($itsRecord))
            {
                $can_continue = false;
            }
            if ($itsRecord['STATUS'] != '0' && $itsRecord['STATUS'] !== null) {
                return false;
            }
        }
        if ($can_continue) {
            $flow_data['type'] = 'feifuxianchengbenshenqing';
            $flow_data['CASEID'] = $itsRecord['CASE_ID'];
            $flow_data['RECORDID'] = $RECORDID;
            $flow_data['INFO'] = strip_tags($post['INFO']);
            $flow_data['DEAL_INFO'] = strip_tags($post['DEAL_INFO']);
            $flow_data['DEAL_USER'] = strip_tags($post['DEAL_USER']);
            $flow_data['DEAL_USERID'] = intval($post['DEAL_USERID']);
            $flow_data['FILES'] = $post['FILES'];
            $flow_data['ISMALL'] = intval($post['ISMALL']);
            $flow_data['ISPHONE'] = intval($post['ISPHONE']);
            $flow_data['ACTIVID'] = intval($activid);
            $flow_data['ISNONCASH'] = 1; //�Ƿ�Ϊ�Ǹ��ֹ�����
            $insertedWorkFlow = $workflow->createworkflow($flow_data);
            if ($insertedWorkFlow !== false) {
                //$itsModel->startTrans();
                foreach($nonCashCostIds as $nonCashCostId)
                {
                    $updatedRows = $itsModel->where('ID = ' . $nonCashCostId)->save(array(
                        'STATUS' => 1  // �����״̬
                    ));
                }
                
                if ($updatedRows !== false) {
                    $this->addFlowNoncash($insertedWorkFlow, $nonCashCostIds); 
                   // $itsModel->commit();
                    return $updatedRows;
                } else {
                  //  $itsModel->rollback();
                    return false;
                }

            } else {
                return false;
            }
        }

        return false;
    }

	private function addFlowNoncash($flow_id, $nonCashCostIds)
    {
        $response = FALSE;
        if (notEmptyArray($nonCashCostIds)) {
            $response = D('FlowNoncash')->add_flow_noncash($flow_id, $nonCashCostIds);
        }
        return $response;
    }
	
	private  function addIncomeCost($data){
		$project_cost_model = D("ProjectCost");
		$nonCashCost  =  M("Erp_noncashcost")->where("ID=".$data['recordId'])->find();
		if($data['recordId']) {
			//�Ǹ��ֳɱ����ڷ������ͣ���ת����ĿΪ�Լ�����������ɱ���һ�������ʽ�ط��ã�һ������
			if ($nonCashCost['FEE_ID']) {
				//���ɱ�������Ӽ�¼,���������� ��ֵ
				$cost_info = $this->costArr($data, $nonCashCost);
				$cost_insert_id = $project_cost_model->add_cost_info($cost_info);
				//��ֵ������������ѡ
				$cost_info = $this->costArr($data, $nonCashCost, 0);
				$cost_insert_id2 = $project_cost_model->add_cost_info($cost_info);
				if (!$cost_insert_id && !$cost_insert_id2) {
					return false;
				}
			} else {
				//�޷������ͣ��ж��Ƿ��ǵ��̣����������¼��������Ŀ���������¼��������ؿ��¼��ֻ����һ���ɱ�
				$cityId = M("Erp_project")->where("ID=".$nonCashCost['PROJECT_ID'])->getField("CITY_ID");
				$income_projectId =  M("Erp_project")->where("CONTRACT='{$nonCashCost['CONTRACT_NO']}' and CITY_ID=".$cityId." AND STATUS !=2")->getField('ID');
				$income_caseId = M("Erp_case")->where("PROJECT_ID=".$income_projectId." and SCALETYPE=".$nonCashCost['SCALETYPE'])->getField("ID");
				if ($nonCashCost['SCALETYPE'] == 1) { //����
					$income_from = array(29, 30, 31);   //�ʽ�س�ֵ���

					foreach ($income_from as $income_status) {
						$income_info['INCOME_FROM'] = $income_status;
						$income_info['CASE_ID'] = $income_caseId;
						$income_info['ENTITY_ID'] = $data['RECORDID'];
						$income_info['ORG_ENTITY_ID'] = $data['RECORDID'];
						$income_info['PAY_ID'] = $data['RECORDID'];
						$income_info['ORG_PAY_ID'] = $data['RECORDID'];
						$income_info['INCOME'] = $nonCashCost['AMOUNT'];
						$income_info['INCOME_REMARK'] = '�Ǹ��ֳɱ�����';
						$income_info['ADD_UID'] = $_SESSION['uinfo']['uid'];
						$income_info['OCCUR_TIME'] = date("Y-m-d H:i:s", time());
						$income_model = D('ProjectIncome');
						$ret = $income_model->add_income_info($income_info);
						if (!$ret) {
							return false;
						}
					}

				} else {
					$income_info['INCOME_FROM'] = 28;
					$income_info['CASE_ID'] = $income_caseId;
					$income_info['ENTITY_ID'] = $data['RECORDID'];
					$income_info['ORG_ENTITY_ID'] = $data['RECORDID'];
					$income_info['PAY_ID'] = $data['RECORDID'];
					$income_info['ORG_PAY_ID'] = $data['RECORDID'];
					$income_info['INCOME'] = $nonCashCost['AMOUNT'];
					$income_info['INCOME_REMARK'] = '�Ǹ��ֳɱ�����';
					$income_info['ADD_UID'] = $_SESSION['uinfo']['uid'];
					$income_info['OCCUR_TIME'] = date("Y-m-d H:i:s", time());
					$income_model = D('ProjectIncome');
					$ret = $income_model->add_income_info($income_info);
					if (!$ret) {
						return false;
					}

				}
				$cost_info = $this->costArr($data, $nonCashCost);
				$cost_insert_id = $project_cost_model->add_cost_info($cost_info);
				if(!$cost_insert_id) {
					return false;
				}
			}
			return true;
		}
	}

	private  function costArr($data,$nonCashCost,$iscost = 1){
		$cost_info = array();
		$cost_info['CASE_ID'] = $nonCashCost["CASE_ID"];            //������� �����
		$cost_info['ENTITY_ID'] = $data['RECORDID'];                 //ҵ��ʵ���� �����
		$cost_info['EXPEND_ID'] = $data['RECORDID'];                  //�ɱ���ϸ��� �����
		$cost_info['ORG_ENTITY_ID'] = $data['RECORDID'];                  //ҵ��ʵ���� �����
		$cost_info['ORG_EXPEND_ID'] = $data['RECORDID'];
		$cost_info['FEE'] = $nonCashCost["AMOUNT"];                // �ɱ���� �����
		$cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];             //�����û���� �����
		$cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());        //����ʱ�� �����
		$cost_info['ISFUNDPOOL'] = 1;                                 //�Ƿ��ʽ�أ�0��1�ǣ� �����
		$cost_info['ISKF'] = 1;                                     //�Ƿ�۷� �����
		$cost_info['FEE_REMARK'] = "�ʽ�س��";             //�������� ��ѡ�
		$cost_info['INPUT_TAX'] = 0;                                //����˰ ��ѡ�
		$cost_info['FEE_ID'] = 80;                                  //�ɱ�����ID �����֧������������
		$cost_info['EXPEND_FROM'] = 36;                             //�ɱ���Դ
		if($iscost == 0 ){
			$cost_info['ISFUNDPOOL'] = 0;
			$cost_info['FEE_ID'] = $nonCashCost['FEE_ID'];
			$cost_info['FEE'] = 0-$nonCashCost["AMOUNT"];
		}
		return $cost_info;
	}
}