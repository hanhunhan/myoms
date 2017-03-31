<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
	include dirname(__FILE__).'/FlowBase.php';
}else {
	die('Sorry. Not load FlowBase file.');
}
/**
 +------------------------------------------------------------------------------
 * projectset ���̳���
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Form.php  2015-05-22   $
 +------------------------------------------------------------------------------
 */
class BenefitFlow extends   FlowBase{
	 
	protected $workflow = null;//
	protected $UserLog = null;//
	 
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
			if($this->cType=='pc') $res=1;//js_alert('����ɹ�', U('Flow/through'));
			else $res=1;//js_alert('����ɹ�' );
			$this->model->commit();
			
			//$this->UserLog->writeLog($data['flowId'],"__APP__","Ԥ�������������������̰���ɹ�");

			
		} else {
			$this->model->rollback();
			$res=0;
			//js_alert('����ʧ��');
			
			//$this->UserLog->writeLog($data['flowId'],"__APP__","Ԥ�������������������̰���ʧ��");
		}
		return $res;
		 
	}
	public function passWorkflow($data){//ȷ��
		$this->model->startTrans();
		$str = $this->workflow->passWorkflow($data);

		if ($str) {
			
			$res = $this->pass($data);
			if($res){


				$benefits = D('Erp_benefits')->where("ID='".$data["recordId"]."'")->find();
				//��֧��ҵ��Ѵ���
				$sql = "select * from ERP_FINALACCOUNTS t where CASE_ID='".$benefits['CASE_ID']."' and TYPE=1";
				$finalaccounts = M()->query($sql);
				$xgfee = $finalaccounts[0]['TOBEPAID_YEWU'] > $benefits['AMOUNT']  ? $finalaccounts[0]['TOBEPAID_YEWU']-$benefits['AMOUNT']  : 0;
				if($xgfee!=$finalaccounts[0]['TOBEPAID_YEWU']){
					//D('Erp_finalaccounts')->where("CASE_ID='".$benefits['CASE_ID']."' and TYPE=1")->save(array('TOBEPAID_YEWU'=>$xgfee) );
				}




				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","Ԥ��������������������ͬ��ɹ�");
				//js_alert('ͬ��ɹ�', U('Flow/through'));
				$result = 1;
			}else{
				//js_alert('ͬ��ʧ�� ���ݲ���ʧ��');
				$result = $res;
				$this->model->rollback();
			}
		} else {
			//js_alert('ͬ��ʧ��');
			$result = 0;
			//$this->UserLog->writeLog($data['flowId'],"__APP__","Ԥ��������������������ͬ��ʧ��");
			$this->model->rollback();
			
		}
		return $result;
		 
	}
	public function notWorkflow($data){//���
		$prjId =   $data['CASEID'];
		$this->model->startTrans();
		$str = $this->workflow->notWorkflow($data);
		if ($str) {
			$sql = " UPDATE ERP_BENEFITS SET STATUS = 4 WHERE ID=".$_REQUEST["recordId"];
            $res = D("Benefits")->execute($sql);
			if($res){
				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","Ԥ�������������������̷���ɹ�");
				if($this->cType=='pc') $result = 1;//js_alert('����ɹ�', U('Flow/already'));
				else $result = 1;//js_alert('����ɹ�' );

				
			}else{
				$this->model->rollback();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","Ԥ�������������������̷��ʧ�� ���ݲ���ʧ��");
				//js_alert('���ʧ�� ���ݲ���ʧ��');
				$result = 0;
				
			}
		} else {
			$this->model->rollback();
			$result = 0;
			//js_alert('���ʧ��');
			//$this->UserLog->writeLog($data['flowId'],"__APP__","Ԥ�������������������̷��ʧ��");
			
		}
		return $result;
		 
	}
	public function finishworkflow($data){//����
		$auth = $this->workflow->flowPassRole($data['flowId']);
		//if (!$auth) {
			//js_alert('δ�����ؾ���ɫ');
			//$result = -2;
			//exit;
		//}
		$this->model->startTrans();
		$str = $this->workflow->finishworkflow($data);//var_dump($data);
		if ($str) {
			$res = $this->pass($data); 
			if($res>0) {
				//$result = $res;


				$benefits = D('Erp_benefits')->where("ID='".$data["recordId"]."'")->find();
				//��֧��ҵ��Ѵ���
				$sql = "select * from ERP_FINALACCOUNTS t where CASE_ID='".$benefits['CASE_ID']."' and TYPE=1";
				$finalaccounts = M()->query($sql);
				$xgfee = $finalaccounts[0]['TOBEPAID_YEWU'] > $benefits['AMOUNT']  ? $finalaccounts[0]['TOBEPAID_YEWU']-$benefits['AMOUNT']  : 0;
				if($xgfee!=$finalaccounts[0]['TOBEPAID_YEWU'] && $finalaccounts[0]['STATUS']==2){
					D('Erp_finalaccounts')->where("CASE_ID='".$benefits['CASE_ID']."' and TYPE=1")->save(array('TOBEPAID_YEWU'=>$xgfee) );
				}


				$this->model->commit();
				$result = 1;
			}else{
				$this->model->rollback();
				//js_alert('����ʧ�� ���ݲ���ʧ��');
				$result = $res;
				//$this->UserLog->writeLog($data['flowId'],"__APP__","Ԥ����������������������ʧ�� ���ݲ���ʧ��");
			}

		} else {
			$this->model->rollback();
			//js_alert('����ʧ��');
			$result = 0;
			//$this->UserLog->writeLog($data['flowId'],"__APP__","Ԥ����������������������ʧ�� ");
		}
		return $result; 
	}
	public function createworkflow($data){//����������
		$return = false;
		$flow_type_pinyin = 'yusuanqita';
		//$auth = $workflow->start_authority($flow_type_pinyin);
		if(!$auth)
		{
			//js_alert('����Ȩ��');
		}
		//$form = $workflow->createHtml();          
		if($data['savedata'])
		{                   
			if( !$data["CASEID"] )
			{
				$cond_where = "PROJECT_ID = $prjid and SCALETYPE = $scale_type";
				$case_info = $case_model->get_info_by_cond($cond_where,array("ID"));
				//echo $this->model->_sql();
				$case_id = $case_info[0]["ID"];
			}
			else
			{
			   $case_id  = $data["CASEID"];
			}
			$this->model->startTrans();
			$flow_data['type'] = $flow_type_pinyin;
			$flow_data['CASEID'] = $case_id;                    
			$flow_data['RECORDID'] = $data["recordId"];
			$flow_data['INFO'] = strip_tags($data['INFO']);
			$flow_data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
			$flow_data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
			$flow_data['DEAL_USERID'] = intval($data['DEAL_USERID']);
			$flow_data['FILES'] = $data['FILES']; 
			$flow_data['ISMALL'] =  intval($data['ISMALL']); 
			$flow_data['ISPHONE'] =  intval($data['ISPHONE']); 
			$str = $this->workflow->createworkflow($flow_data);
			//var_dump($flow_data);die;
			if($str)
			{   
				$sql = " UPDATE ERP_BENEFITS SET STATUS = 2 WHERE ID=".$data["recordId"];
				$res = D("Benefits")->execute($sql);
				//$benefits_type = intval($data['benefits_type']);
				$return = true;
				$this->model->commit(); 
			}
			else
			{
				$this->model->rollback(); 
				 
			}
		}
		return $return;  
	}
	private function pass($data){
		//������ID
        $flowId = !empty($data['flowId']) ? intval($data['flowId']) : 0;
		//����������ҵ��ID
        $recordId = !empty($data['recordId']) ? intval($data['recordId']) : 0;
	 
		$benefits_model = D("Benefits");
        $case_model = D("ProjectCase");
		$case_id = $data["CASEID"];
		$case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
		$scale_type = $case_info[0]["SCALETYPE"];
		$search_arr = array("TYPE","CASE_ID","AMOUNT");
		$benefits_info = $benefits_model->get_info_by_id($data["recordId"],$search_arr);

		//�����Ŀ�ɱ������ѵ��ʽ� > ��Ԥ��������*���ʱ���
		$is_overtop_limit = is_overtop_payout_limit($case_id,$benefits_info[0]['AMOUNT'],1);
		if($is_overtop_limit)
		{
			//js_alert("����Ŀ�ɱ��ѳ������ʶ�Ȼ򳬳�����Ԥ�㣨�ܷ���>��Ʊ����*���ֳɱ��ʣ������̲�������ͨ����",
			//U("Benefits/otherBenefits",$this->_merge_url_param),1);
			//die;
			//$this->UserLog->writeLog($data['flowId'],"__APP__","����Ŀ�ɱ��ѳ������ʶ�Ȼ򳬳�����Ԥ�㣨�ܷ���>��Ʊ�ؿ�����*���ֳɱ��ʣ������̲�������ͨ����");
			//return  false;
			return -1;
		}
	 
		$auth = $this->workflow->flowPassRole($flowId);
		if(!$auth){
			//js_alert('δ�����ؾ���ɫ');exit;
			//$this->UserLog->writeLog($data['flowId'],"__APP__","δ�����ؾ���ɫ");
			//return  false;
			return -2;
		}
		
	 
		$sql = " UPDATE ERP_BENEFITS SET STATUS = 3 WHERE ID=".$data["recordId"];
		$res = D("Benefits")->execute($sql);
		//������ͨ������ӳɱ���¼                            
		$search_arr = array("TYPE","CASE_ID","AMOUNT");
		$benefits_info = $benefits_model->get_info_by_id($data["recordId"],$search_arr);
		$benefits_type = $benefits_info[0]["TYPE"];
		
		//���ɱ�������Ӽ�¼
		$cost_info['CASE_ID'] = $benefits_info[0]["CASE_ID"];            //������� �����       
		$cost_info['ENTITY_ID'] = $data["recordId"];                 //ҵ��ʵ���� �����
		$cost_info['EXPEND_ID'] = $data["recordId"];                //�ɱ���ϸ��� �����
		
		$cost_info['ORG_ENTITY_ID'] = $data["recordId"];                 //ҵ��ʵ���� �����
		$cost_info['ORG_EXPEND_ID'] = $data["recordId"];
		
		$cost_info['FEE'] = $benefits_info[0]["AMOUNT"];                // �ɱ���� ����� 
		$cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];             //�����û���� �����
		$cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());        //����ʱ�� �����
		$cost_info['ISFUNDPOOL'] = 0;                                 //�Ƿ��ʽ�أ�0��1�ǣ� �����
		$cost_info['ISKF'] = 1;                                     //�Ƿ�۷� �����
		$cost_info['FEE_REMARK'] = "Ԥ�����������";             //�������� ��ѡ�
		$cost_info['INPUT_TAX'] = 0;                                //����˰ ��ѡ�
		$cost_info['FEE_ID'] = 60;                                  //�ɱ�����ID �����
		//�ɱ���Դ
		if($benefits_type == 0)
		{
		   $cost_info['EXPEND_FROM'] = 17;  
		}
		else if($benefits_type == 1)
		{
			$cost_info['EXPEND_FROM'] = 18; 
		}
		$project_cost_model = D("ProjectCost");  
		
		$cost_insert_id = $project_cost_model->add_cost_info($cost_info);
		 
		 
		return $cost_insert_id;
	}
	/**
     * ���ӷ��ҷ��ճ��ͬ
     * @param $projId ��Ŀid
     * @param $caseId
     * @return bool �����Ƿ���ӳɹ�
     */
    private function addFwfscIncomeContract($projId, $caseId) {
        $contractInfo = D('Contract')->where('CASE_ID = ' . $caseId)->find();
        if (is_array($contractInfo) && count($contractInfo)) {
            return false;
        }

        $project = D('erp_project')->where('ID=' . $projId)->find();
        if (empty($project)) {
            return false;//��Ŀ����Ϊ��
        }

        $contractNo = $project['CONTRACT'];
        $cityid = $project['CITY_ID'];  // ����Ŀ�б��л�ȡ���б��
        $sql = "select PY from ERP_CITY where ID=" . $cityid;
        $citypy = $this->model->query($sql);
        $citypy = strtolower($citypy[0]["PY"]);//�û�����ƴ��
        //��ȡ��ͬ������Ϣ
        load("@.contract_common");
        $fetchedData = getContractData($citypy, $contractNo);
        if ($fetchedData === false) {
			return false;//��ȡ��ͬ���ݳ���
            
        }

        $toInsertData['CONTRACT_NO'] = $contractNo;
        $toInsertData['COMPANY'] = $fetchedData['contunit'];
        $toInsertData['START_TIME'] = date("Y-m-d", $fetchedData['contbegintime']);
        $toInsertData['END_TIME'] = date("Y-m-d", $fetchedData['contendtime']);
        $toInsertData['PUB_TIME'] = $fetchedData['pubdate'];
        $toInsertData['CONF_TIME'] = empty($fetchedData['confirmtime']) ?
            '' : date("Y-m-d", $fetchedData['confirmtime']);
        $toInsertData['MONEY'] = $fetchedData['contmoney'];
        $toInsertData['STATUS'] = $fetchedData['step'];  // todo
        $toInsertData['SIGN_USER'] = $fetchedData['addman'];
        $toInsertData['CONTRACT_TYPE'] = $fetchedData['type'];
        $toInsertData['ADD_TIME'] = date("Y-m-d H:i:s");  // ���ʱ��
        $toInsertData['CASE_ID'] = $caseId;  // ���ʱ��
        $toInsertData['CITY_PY'] = $citypy;
        $toInsertData['CITY_ID'] = $cityid;
        unset($fetchedData);

        // ִ������
        //$this->model->startTrans();
        $insertedId = D("Contract")->add_contract_info($toInsertData);
        if ($insertedId !== false) {
            //���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ļؿ��¼,��������ͬ��������ϵͳ
            $insert_refund_id = $this->save_refund_data($contractNo, $insertedId, $citypy);
            //���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ��Ʊ��¼����������ͬ��������ϵͳ
            $insert_invoice_id = $this->save_invoice_data($contractNo, $insertedId, $citypy);
            if ($insert_invoice_id !== false && $insert_refund_id !== false) {
               // $this->model->commit();
                return true;
            } else {
               // $this->model->rollback();
                $error = '';
                if ($insert_refund_id == false) {
                    $error .= '��ȡ��ͬ�Ļؿ��¼����';
                }

                if ($insert_invoice_id == false) {
                    $error = empty($error) ? '��ȡ��ͬ�Ŀ�Ʊ��¼����' :
                        $error . '�� ��ȡ��ͬ�Ŀ�Ʊ��¼����';
                }

                // ���ؽ��
                return false;
            }
        } else {
			return false;
           //��Ӻ�ͬ����
        }
    }

	
	
	
	 
}