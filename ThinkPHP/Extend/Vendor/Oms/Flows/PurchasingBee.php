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
class PurchasingBee extends   FlowBase{
	 
	protected $workflow = null;//
	protected $city =null;

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
			//$this->UserLog->writeLog($data['flowId'],"__APP__","С�۷䳬���������̰���ɹ�");
			if($this->cType=='pc') $result =1;//js_alert('����ɹ�', U('Flow/through'));
			else $result =2;//js_alert('����ɹ�' );
			
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","С�۷䳬���������̰���ʧ��");
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
			//$this->UserLog->writeLog($data['flowId'],"__APP__","С�۷䳬����������ͬ��ɹ�");
			if($this->cType=='pc') $result = 3;//js_alert('ͬ��ɹ�', U('Flow/through'));
			else  $result = 4;//js_alert('ͬ��ɹ�' );
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","С�۷䳬����������ͬ��ʧ��");
			//js_alert('ͬ��ʧ��');
			$result = -3;
			
		}
		return  $result;
		 
	}
	public function notWorkflow($data){//���
		$this->model->startTrans();
		$str = $this->workflow->notWorkflow($data);  
		$this->city = M("Erp_flows")->where("ID=".$data['flowId'])->find();
		
		if ($str) {

			$res = $this->_bee_option_follow_fail($data['recordId']);
			if($res  ){
				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","С�۷䳬���������̷���ɹ�");
				if($this->cType=='pc') $result = 5;//js_alert('����ɹ�', U('Flow/already'));
				else $result = 6;//js_alert('����ɹ�' );
			}else {
				$this->model->rollback();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","С�۷䳬���������̷��ʧ��");
				 
				$result = -31;
			}
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","С�۷䳬���������̷��ʧ��");
			//js_alert('���ʧ��');
			$result = -4;
		}
		return  $result;
		 
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
		$this->city = M("Erp_flows")->where("ID=".$data['flowId'])->find();
		if ($str) {
			$res = $this->_bee_option_follow_success($data['recordId']);
			if($res  ){
				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","С�۷䳬���������̱����ɹ�");
				if($this->cType=='pc')  $result = 7;//js_alert('�����ɹ�', U('Flow/already'));
				else $result = 8;//js_alert('�����ɹ�' );
				
			}
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","С�۷䳬���������̱���ʧ��");
			//js_alert('����ʧ��');
			$result = -6;
			
		}
		return $result; 
	}
	public function createworkflow($data){//����������
		 
		
		$form = $this->workflow->createHtml();
		
		if ($data['savedata']) {
			$this->model->startTrans();
			$model_bee_work = D('PurchaseBeeDetails');
			$model_bee      = D('PurchaseList');
			//$purchase_id = !empty($data['purchase_id']) ? intval($data['purchase_id']) : 0;
			$beeId = !empty($_REQUEST['recordId']) ?$_REQUEST['recordId'] : 0;//��ĿID
			$beeDetailsId = !empty($_REQUEST['others']) ? str_replace('-', ',', $_REQUEST['others']) : 0;
			$flow_data['type'] =  'xiaomifengchaoe';
			$flow_data['CASEID'] = 0;
			$flow_data['RECORDID'] = $beeId;
			$flow_data['INFO'] = strip_tags($data['INFO']);
			$flow_data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
			$flow_data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
			$flow_data['DEAL_USERID'] = intval($data['DEAL_USERID']);
			$flow_data['FILES'] = $data['FILES'];
			$flow_data['ISMALL'] =  intval($data['ISMALL']);
			$flow_data['ISPHONE'] =  intval($data['ISPHONE']);
			$str = $this->workflow->createworkflow($flow_data);
			if($str){
				//����С�۷���ϸ�Ƿ��ѷ�������״̬
				$model_bee->where('ID='.$beeId)->save(array('IS_APPLY_PROCESS'=>1));
				$model_bee_work->where("ID IN ($beeDetailsId)")->save(array('STATUS'=>4));
				//js_alert('�ύ�ɹ�',U('Purchasing/bee',$this->_merge_url_param));
				$this->model->commit();
				$result = 9;
				//exit;
			}else{
				//js_alert('�ύʧ��',U('Purchasing/BeeOpinionFlow',$this->_merge_url_param));
				//exit;
				$this->model->rollback();
				$result = -7;
			}
		}
		return $result;
		 
	}


	 /**
     * ������������ͨ���Զ����ɱ�������
     * @param unknown $bee_id
     */
    private function _bee_option_follow_success($bee_id){
        //ʵ��������
        $model_bee_work = D('PurchaseBeeDetails');
        $model_bee      = D('PurchaseList');
        //С�۷�ɹ���ϸ
        $bee = $model_bee->find($bee_id);
        if (empty($bee)){
            return false;//С�۷�ɹ���ϸ������
        }
        //��ȡ�����ύ����Ҫ������С�۷�ɹ���ϸ����
        $bee_list = $model_bee_work->where("P_ID=$bee_id AND STATUS=4")->select();
        $money_total = 0;
        foreach ($bee_list as $key=>$val){
            $need_change_status[] = $val['ID'];
            $money_total+=$val['REIM_MONEY'];
        }
        //���������ɱ�������
        $reim_list_model = D('ReimbursementList');      //�������뵥MODEL
        $reim_detail_model = D('ReimbursementDetail');  //������ϸMODEL
        $reim_list_model->startTrans();
        //���ɱ������뵥
        $uid = $bee['P_ID'];//intval($_SESSION['uinfo']['uid']);//��ǰ�û����
		$user = M('Erp_users')->where("ID=$uid")->find();
        $user_truename = $user['NAME'];  //$_SESSION['uinfo']['tname'];//��ǰ�û�����
        $city_id = intval($this->city['CITY']);//��ǰ���б��
        $list_arr = array();
        $list_arr["AMOUNT"] = $money_total;
        $list_arr["TYPE"] = 15;
        $list_arr["APPLY_UID"] = $uid;
        $list_arr["APPLY_TRUENAME"] = $user_truename;
        $list_arr["APPLY_TIME"] = date("Y-m-d H:m:s");
        $list_arr["CITY_ID"] = $city_id;
        $last_id = $reim_list_model->add_reim_list($list_arr);
        if (!$last_id){
            $reim_list_model->rollback();
            return false;  //��ӱ�������ʧ��
        }
        //���ɱ�����ϸ
        foreach ($bee_list as $key=>$value){
            $detail_add = array(
                'LIST_ID' => $last_id,
                'CITY_ID' => $city_id,
                'CASE_ID' => $bee['CASE_ID'],
                'BUSINESS_ID' => $bee['ID'],
				'PURCHASER_BEE_ID' =>  $value['ID'],
                'BUSINESS_PARENT_ID' => $bee['PR_ID'],
                'MONEY' => $value['REIM_MONEY'],
                'STATUS' => 0,
                'APPLY_TIME' => date('Y-m-d H:i:s'),
                'ISFUNDPOOL' => $bee['IS_FUNDPOOL'],
                'ISKF' => $bee['IS_KF'],
                'TYPE' => 15,
                'FEE_ID' => $bee['FEE_ID'],
            );
            $reuslt_add = $reim_detail_model->add_reim_details($detail_add);
            if (!$reuslt_add){
                $reim_list_model->rollback();
                return false;  //��ӱ�����ϸʧ��
            }
        }
        //�޸�С�۷�������״̬
        $need_change_status = implode(',', $need_change_status);
        $update_result = $model_bee_work->where('ID IN ('.$need_change_status.')')->save(array('STATUS'=>1,'CSTATUS'=>1));
        if (!$update_result){
            $reim_list_model->rollback();
            return false;  //�޸�С�۷�������״̬ʧ��
        }
        $reim_list_model->commit();
        return true;
    }
	/**
     * ������������ͨ���Զ����ɱ�������
     * @param unknown $bee_id
     */
    private function _bee_option_follow_fail($bee_id){
        //ʵ��������
        $model_bee_work = D('PurchaseBeeDetails');
        $model_bee      = D('PurchaseList');
        //С�۷�ɹ���ϸ
        $bee = $model_bee->find($bee_id); 
        if (empty($bee)){
            return false;//С�۷�ɹ���ϸ������
        }
        //��ȡ�����ύ����Ҫ������С�۷�ɹ���ϸ����
        $bee_list = $model_bee_work->where("P_ID=$bee_id AND STATUS=4")->select();
        foreach ($bee_list as $key=>$val){
            $need_change_status[] = $val['ID'];
            //$money_total+=$val['REIM_MONEY'];
        } 
        //���������ɱ�������
       // $reim_list_model = D('ReimbursementList');      //�������뵥MODEL
        //$reim_detail_model = D('ReimbursementDetail');  //������ϸMODEL
        M()->startTrans();
        //���ɱ������뵥
        $uid = intval($_SESSION['uinfo']['uid']);//��ǰ�û����
        $user_truename = $_SESSION['uinfo']['tname'];//��ǰ�û�����
        $city_id = intval($this->city['CITY']);//��ǰ���б��
         
		$project_cost_model = D("ProjectCost");
        //���ɱ�����ϸ
		$cost_insert_id = true;
        foreach ($bee_list as $key=>$value){
            $cost_info = array();
			$cost_info['CASE_ID'] = $bee["CASE_ID"]; //������� �����       
			$cost_info['ENTITY_ID'] = $bee["PR_ID"];                                 
			$cost_info['EXPEND_ID'] = $bee["ID"];                            
			$cost_info['ORG_ENTITY_ID'] = $bee["PR_ID"];                    
			$cost_info['ORG_EXPEND_ID'] = $bee["ID"];                  //ҵ��ʵ���� �����
			$cost_info['FEE'] = -$value['REIM_MONEY'];                // �ɱ���� ����� 
			$cost_info['ADD_UID'] = $bee["P_ID"];//$_SESSION["uinfo"]["uid"];            //�����û���� �����
			$cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());        //����ʱ�� �����
			$cost_info['ISFUNDPOOL'] = $bee["IS_FUNDPOOL"];                  //�Ƿ��ʽ�أ�0��1�ǣ� �����
			$cost_info['ISKF'] = $bee["IS_KF"];                             //�ɱ�����ID �����
			//$cost_info['INPUT_TAX'] = $v["INPUT_TAX"];                  //����˰ ��ѡ�
			$cost_info['FEE_ID'] =  $bee["FEE_ID"];   
			$cost_info['EXPEND_FROM'] = 31; //?
			$cost_info['FEE_REMARK'] = "�ɹ������������벵��";//�ɱ�����ID �����
			$cost_insert_id = $project_cost_model->add_cost_info($cost_info);
			if(!$cost_insert_id){
				$cost_insert_id = false;
				break;
			}
		}
        //�޸�С�۷�������״̬
        $need_change_status = implode(',', $need_change_status);
        $update_result = $model_bee_work->where('ID IN ('.$need_change_status.')')->save(array('STATUS'=>3,'CSTATUS'=>1));
        if (!$update_result || !$cost_insert_id){
            M()->rollback();
            return false;  //�޸�С�۷�������״̬ʧ��
        }
		send_result_to_zk($need_change_status,$this->city['CITY'] );//ͬ�����ڿ�
        M()->commit();
        return true;
    }

	
	
	
	 
}