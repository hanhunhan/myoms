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
class projectset extends   FlowBase{
	 
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
			
			//$this->UserLog->writeLog($data['flowId'],"__APP__","�����������̰���ɹ�");

			
		} else {
			$this->model->rollback();
			$res=0;
			//js_alert('����ʧ��');
			
			//$this->UserLog->writeLog($data['flowId'],"__APP__","�����������̰���ʧ��");
		}
		return $res;
		 
	}
	public function passWorkflow($data){//ȷ��
        $result = $this->checkFxContract($data);  // ��������ͬ�Ƿ��ȡ�ɹ�
        if (!$result) {
            return array(
                'status' => false,
                'message' => '��ȡ������ͬ����ʧ�ܣ����Ժ�����'
            );
        }
		$this->model->startTrans();
		$str = $this->workflow->passWorkflow($data);

		if ($str) {
			
			$res = $this->pass($data);
			if($res){
				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","������������ͬ��ɹ�");
				//js_alert('ͬ��ɹ�', U('Flow/through'));
				$result = 1;
			}else{
				//js_alert('ͬ��ʧ�� ���ݲ���ʧ��');
				$result = 0;
				$this->model->rollback();
			}
		} else {
			//js_alert('ͬ��ʧ��');
			$result = 0;
			//$this->UserLog->writeLog($data['flowId'],"__APP__","������������ͬ��ʧ��");
			$this->model->rollback();
			
		}
		return $result;
		 
	}
	public function notWorkflow($data){//���
		//$prjId = $data['prjid'] ? $data['prjid'] : $data['CASEID'];
		$prjId = $data['recordId'];  
		$this->model->startTrans();
		$str = $this->workflow->notWorkflow($data);
		if ($str) {
			$project_model = D('Project');
            $res = $project_model->update_nopass_status($prjId);;//��� ��ͨ��
			if($res){
				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","�����������̷���ɹ�");
				if($this->cType=='pc') $result = 1;//js_alert('����ɹ�', U('Flow/already'));
				else $result = 1;//js_alert('����ɹ�' );

				
			}else{
				$this->model->rollback();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","�����������̷��ʧ�� ���ݲ���ʧ��");
				//js_alert('���ʧ�� ���ݲ���ʧ��');
				$result = 0;
				
			}
		} else {
			$this->model->rollback();
			$result = 0;
			//js_alert('���ʧ��');
			//$this->UserLog->writeLog($data['flowId'],"__APP__","�����������̷��ʧ��");
			
		}
		return $result;
		 
	}
	public function finishworkflow($data){//����
		$auth = $this->workflow->flowPassRole($data['flowId']);
		if (!$auth) {
			//js_alert('δ�����ؾ���ɫ');
			$result = 0;
			exit;
		}
        $result = $this->checkFxContract($data);  // ��������ͬ�Ƿ��ȡ�ɹ�
        if (!$result) {
            return array(
                'status' => false,
                'message' => '��ȡ������ͬ����ʧ�ܣ����Ժ�����'
            );
        }
		$flag = 1;
		$this->model->startTrans();
		$str = $this->workflow->finishworkflow($data);
		if ($str) {
			$res = $this->pass($data);
			if($flag) {
				
				$this->model->commit(); 
				$result = 1;
			}else{
				$this->model->rollback();
				//js_alert('����ʧ�� ���ݲ���ʧ��');
				$result = 0;
				//$this->UserLog->writeLog($data['flowId'],"__APP__","��������������ʧ�� ���ݲ���ʧ��");
			}

		} else {
			$this->model->rollback();
			//js_alert('����ʧ��');
			$result = 0;
			//$this->UserLog->writeLog($data['flowId'],"__APP__","��������������ʧ�� ");
		}
		return $result; 
	}
	public function createworkflow($data){//����������
		$prjId = $data['prjid'] ? $data['prjid'] : $data['recordId'];
		$auth = $this->workflow->start_authority('xiangmujuesuan');
		if (!$auth) {
			//js_alert("����Ȩ��");
			//$response['message'] = 'δ�����ؾ���ɫ';
            //return $response;
		}
		$form = $this->workflow->createHtml();

		if ($data['savedata']) { 
			if ($prjId) {
				$project_model = D('Project');
				$pstatus = $project_model->get_project_status($prjId);
				if ($pstatus == 2) {  
					$this->model->startTrans();
					$flow_data['type'] = 'lixiangshenqing';//$type;
					//$flow_data['CASEID'] = '';
					$flow_data['RECORDID'] = $prjId;
					$flow_data['INFO'] = strip_tags($data['INFO']);
					$flow_data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
					$flow_data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
					$flow_data['DEAL_USERID'] = intval($data['DEAL_USERID']);
					$flow_data['FILES'] = $data['FILES'];
					$flow_data['ISMALL'] = intval($data['ISMALL']);
					$flow_data['ISPHONE'] = intval($data['ISPHONE']);
					$flow_data['COPY_USERID'] = intval($data['COPY_USERID']);
					$str = $this->workflow->createworkflow($flow_data);
 
					if ($str) {  
						//�ύ..����
						$project_model = D('Project');
						$res = $project_model->update_check_status($prjId);//�����
						if($res){
							$this->model->commit();
							$result = 1;
							//js_alert('�ύ�ɹ�', U('House/opinionFlow', $this->_merge_url_param));
							//$this->UserLog->writeLog($data['flowId'],"__APP__","�����������ύ�ɹ�");
						}else{
							$this->model->rollback();
							//js_alert('����ʧ�� ���ݲ���ʧ��');
							$result = 0;
							//$this->UserLog->writeLog($data['flowId'],"__APP__","��������������ʧ�� ���ݲ���ʧ��");
						}
						//exit;
					} else {
						$this->model->rollback();
						//js_alert('�ύʧ��', U('House/opinionFlow', $this->_merge_url_param));
						$result = 0;
						//$this->UserLog->writeLog($data['flowId'],"__APP__","�����������ύʧ��");
						//exit;
					}
				} else {
					$result = 0;
					//js_alert('�벻Ҫ�ظ��ύ', U('House/opinionFlow', $this->_merge_url_param));
					//exit;
				}
			}
		}
		return $result;  
	}

    private function checkFxContract($data) {
        $result = false;
        $recordId = !empty($data['recordId']) ? intval($data['recordId']) : 0;
        $case_type = 'fx';
        $isexists = D('ProjectCase')->is_exists_case_type($recordId, $case_type);
        if ($isexists) {
            $fx_case_info = D('ProjectCase')->get_info_by_pid($recordId, $case_type, array('ID'));
            $contractInfo = D('Contract')->where('CASE_ID = ' . $fx_case_info[0]['ID'])->find();

            if (notEmptyArray($contractInfo)) {
                $hadContract = true;
                $result = true;
            } else {
                $hadContract = false;
            }

            if ($isexists && !$hadContract) {
                //��ѯ��Ŀ��ͬ��Ϣ
                $cond_where = "PROJECT_ID = '" . $recordId . "'";
                $house_info = M('erp_house')->field('CONTRACT_NUM')->where($cond_where)->find();
                $contract_no = !empty($house_info['CONTRACT_NUM']) ?
                    trim($house_info['CONTRACT_NUM']) : '';
                $city_info = D('City')->get_city_info_by_id($_COOKIE['CHANNELID'], array('PY'));
                $city_py = !empty($city_info['PY']) ? strtolower(strip_tags($city_info['PY'])) : '';
                load("@.contract_common");
                $contractInfo = getContractData($city_py, $contract_no);

                if (notEmptyArray($contractInfo)) {
                    $result = true;
                } else {
                    $result = false;
                }
            }
        } else {
            $result = true;
        }

        return $result;
    }

	private function pass($data){
		//������ID
        $flowId = !empty($data['flowId']) ? intval($data['flowId']) : 0;
		//����������ҵ��ID
        $recordId = !empty($data['recordId']) ? intval($data['recordId']) : 0;
		$conres = $ress = $contract_id = $insert_reund_id = $res = $insert_invoice_id = $res2 = true;
		/**
			*  �������Ŀ���ڷ��ҷ��ճ�
		*/
		$fwfscCase = D('ProjectCase')->where('PROJECT_ID = ' . $recordId . ' AND SCALETYPE = 8')->find();
		if (is_array($fwfscCase) && count($fwfscCase)) {
			$conres = $this->addFwfscIncomeContract($recordId, $fwfscCase['ID']); //****
		}

		$project_model = D('Project');
		$ress = $project_model->update_pass_status($data['recordId']);;//���ͨ�� //****

		/*�������Ŀ���ڷ���ҵ����ͨ����ͬ��Ż�ȡ��ͬϵͳ�к�ͬ��Ϣ��
		���洢�ھ���ϵͳ��ͬ����*/
		$project_case_model = D('ProjectCase');
		$case_type = 'fx';
		$isexists = $project_case_model->is_exists_case_type($recordId, $case_type);
		if($isexists){
			$fx_case_info = $project_case_model->get_info_by_pid($recordId, $case_type, array('ID'));
			$contractInfo = D('Contract')->where('CASE_ID = ' . $fx_case_info[0]['ID'])->find();
		}
		if (is_array($contractInfo) && count($contractInfo)) {
			$hadContract = true;
		} else {
			$hadContract = false;
		}

		if ($isexists && !$hadContract) {
			//��ѯ��Ŀ��ͬ��Ϣ
			$cond_where = "PROJECT_ID = '" . $recordId . "'";
			$house_info = M('erp_house')->field('CONTRACT_NUM')->where($cond_where)->find();
			$contract_no = !empty($house_info['CONTRACT_NUM']) ?
				$house_info['CONTRACT_NUM'] : '';
			$contract_no  = trim($contract_no );
			$city_model = D('City');
			$city_info = $city_model->get_city_info_by_id($_COOKIE['CHANNELID'], array('PY'));
			$city_py = !empty($city_info['PY']) ? strtolower(strip_tags($city_info['PY'])) : '';
			load("@.contract_common");
			$contract_info = getContractData($city_py, $contract_no);  

			if (is_array($contract_info) && !empty($contract_info)) {
				$info = array();
				$case_info = $project_case_model->get_info_by_pid($recordId, $case_type, array('ID'));
				$info['CASE_ID'] = $fx_case_info[0]['ID'];
				$info['CONTRACT_NO'] = $contract_info['fullcode'];
				$info['COMPANY'] = $contract_info['contunit'];
				$info['START_TIME'] = date('Y-m-d H:i:s', $contract_info['contbegintime']);
				$info['END_TIME'] = date('Y-m-d H:i:s', $contract_info['contendtime']);
				$info['PUB_TIME'] = !empty($contract_info['pubdate']) ?
					date('Y-m-d H:i:s', strtotime($contract_info['pubdate'])) : '';
				$info['CONF_TIME'] = !empty($contract_info['confirmtime']) ?
					date('Y-m-d H:i:s', $contract_info['confirmtime']) : '';
				$info['STATUS'] = $contract_info['step'];
				$info['MONEY'] = $contract_info['contmoney'];
				$info['ADD_TIME'] = date('Y-m-d H:i:s', time());
				$info['CONTRACT_TYPE'] = $contract_info['type'];
				$info['IS_NEED_INVOICE'] = 0;
				$info['SIGN_USER'] = $contract_info['addman'];
				$info['CITY_PY'] = $city_py;
				//ȡ���������������ڳ���
				$creator_info = $this->workflow->get_Flow_Creator_Info($flowId);
				$info['CITY_ID'] = $creator_info['CITY'];

				$contract_model = D('Contract');
				$contract_id = $contract_model->add_contract_info($info);//****

				/***ͬ����ͬ��Ʊ�ͻؿ��¼������ϵͳ***/
				if ($contract_id > 0) {
					//���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ļؿ��¼,��������ͬ��������ϵͳ
					$refundRecords = get_backmoney_data_by_no($city_py, $contract_no);

					$payment_model = D("PaymentRecord");
					if (!empty($refundRecords)) {
						foreach ($refundRecords as $key => $val) {
							$refund_data["MONEY"] = $val["money"];
							$refund_data["CREATETIME"] = $val["date"];
							$refund_data["REMARK"] = $val["note"];
							$refund_data["CASE_ID"] = $case_info[0]['ID'];
							$refund_data["CONTRACT_ID"] = $contract_id;
							$insert_reund_id = $payment_model->add_refund_records($refund_data);//****

							if ($insert_reund_id) {
								//����������ϸ��¼  
								$taxrate = get_taxrate_by_citypy($city_py);
								$tax = round($val["money"] / (1 + $taxrate) * $taxrate, 2);

								$income_info['INCOME_FROM'] = 7;
								$income_info['CASE_ID'] = $case_info[0]['ID'];
								$income_info['ENTITY_ID'] = $contract_id;
								$income_info['INCOME'] = $val["money"];
								$income_info['OUTPUT_TAX'] = $tax;
								$income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
								$income_info['OCCUR_TIME'] = $val["date"];
								$income_info['PAY_ID'] = $insert_reund_id;
								$income_info['INCOME_REMARK'] = u2g($val["note"]);
								$income_info['ORG_ENTITY_ID'] = $contract_id;
								$income_info['ORG_PAY_ID'] = $insert_reund_id;

								$ProjectIncome_model = D("ProjectIncome");
								$res = $ProjectIncome_model->add_income_info($income_info);//****
							}
						}
					}

					//���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ŀ�Ʊ��¼,��������ͬ��������ϵͳ
					$invoiceRecords = get_invoice_data_by_no($city_py, $contract_no);

					if (!empty($invoiceRecords)) {
						$billing_model = D("BillingRecord");
						$billing_status = $billing_model->get_invoice_status();

						foreach ($invoiceRecords as $key => $val) {
							$taxrate = get_taxrate_by_citypy($city_py);
							$tax = round($val["money"] / (1 + $taxrate) * $taxrate, 2);

							$invoice_data["INVOICE_MONEY"] = $val["money"];
							$invoice_data["TAX"] = $tax;
							$invoice_data["INVOICE_NO"] = $val["invono"];
							$invoice_data["REMARK"] = $val["note"];
							$invoice_data["INVOICE_TIME"] = $val["date"];
							$invoice_data["STATUS"] = $billing_status["have_invoiced"];
							$invoice_data["CONTRACT_ID"] = $contract_id;
							$invoice_data["CASE_ID"] = $case_info[0]['ID'];
							$invoice_data["CREATETIME"] = $val["date"];
							$invoice_data["INVOICE_TYPE"] = 1;
                            if ($val['type']) {
                                // ��Ʊ���ͣ������Ʊ���Ͳ�Ϊ1��2���򽫷�Ʊ��������Ϊ2(�����)
                                // ��������Ϊ1�����ѣ���2������ѣ�
                                if (!in_array($val['type'], array(1, 2))) {
                                    $val['type'] = 2;
                                }
                                $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                            }
							$insert_invoice_id = $billing_model->add_billing_info($invoice_data);//****

							if ($insert_invoice_id) {
								//����������ϸ��¼           
								$income_info['INCOME_FROM'] = 8;
								$income_info['CASE_ID'] = $case_info[0]['ID'];
								$income_info['ENTITY_ID'] = $contract_id;
								$income_info['INCOME'] = $val["money"];
								$income_info['OUTPUT_TAX'] = $tax;
								$income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
								$income_info['OCCUR_TIME'] = $val["date"];
								$income_info['PAY_ID'] = $insert_invoice_id;
								$income_info['INCOME_REMARK'] = u2g($val["note"]);
								$income_info['ORG_ENTITY_ID'] = $contract_id;
								$income_info['ORG_PAY_ID'] = $insert_invoice_id;

								$ProjectIncome_model = D("ProjectIncome");
								$res2 = $ProjectIncome_model->add_income_info($income_info);//****
							}
						}
					}
				}
			}
		}
		return $conres && $ress ;
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

	 /**
     * +----------------------------------------------------------
     *���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ļؿ��¼,��������ͬ��������ϵͳ
     * +----------------------------------------------------------
     * @param  none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function save_refund_data($contractnum, $contract_id, $citypy = "nj") {
        load("@.contract_common");
        $refundRecords = get_backmoney_data_by_no($citypy, $contractnum);
        if (empty($refundRecords) || (is_array($refundRecords) && count($refundRecords) == 0)) {
            return true;
        }
        //����ͬ�ؿ��¼���뵽����ϵͳ�����ݿ���
        if (!empty($refundRecords)) {
            $contract_model = D("Contract");
            $payment_model = D("PaymentRecord");

            $conf_where = "ID = '" . $contract_id . "'";
            $field_arr = array("CASE_ID");
            $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
            // ��ȡ��Ŀ������
            if (is_array($contract_info) && !empty($contract_info[0]['CASE_ID'])) {
                $scaleType = D('ProjectCase')->where('ID = ' . $contract_info[0]['CASE_ID'])->getField('SCALETYPE');
            }

            foreach ($refundRecords as $key => $val) {
                $refund_data["MONEY"] = $val["money"];
                $refund_data["CREATETIME"] = $val["date"];
                $refund_data["REMARK"] = $val["note"];
                $refund_data["CASE_ID"] = $contract_info[0]["CASE_ID"];
                $refund_data["CONTRACT_ID"] = $contract_id;
                $insert_reund_id = $payment_model->add_refund_records($refund_data);
                if (!$insert_reund_id) {
                    return false;
                } else {
                    //����������ϸ��¼
                    if ($scaleType == 3) {
                        $income_info['INCOME_FROM'] = 11;
                    } else if ($scaleType == 8) {
                        $income_info['INCOME_FROM'] = 22;
                    }
                    $taxrate = get_taxrate_by_citypy($citypy);
                    $tax = round($val["money"] / (1 + $taxrate) * $taxrate, 2);
                    $income_info['OUTPUT_TAX'] = $tax;

                    $income_info['CASE_ID'] = $contract_info[0]["CASE_ID"];
                    $income_info['ENTITY_ID'] = $contract_info[0]["ID"];
                    $income_info['INCOME'] = $val["money"];
                    $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                    $income_info['OCCUR_TIME'] = $val["date"];
                    $income_info['PAY_ID'] = $insert_reund_id;
                    $income_info['INCOME_REMARK'] = u2g($val["note"]);
                    $income_info['ORG_ENTITY_ID'] = $contract_info[0]["ID"];
                    $income_info['ORG_PAY_ID'] = $insert_reund_id;

                    $ProjectIncome_model = D("ProjectIncome");
                    $res = $ProjectIncome_model->add_income_info($income_info);
                    if (!$res) {
                        return false;
                    }
                }
            }

        }
        return $insert_reund_id ? $insert_reund_id : false;
    }
	
	/**
     * +----------------------------------------------------------
     *���ݺ�ͬ�źͳ���ƴ������ȡ��ͬ�Ŀ�Ʊ��¼,��������ͬ��������ϵͳ
     * +----------------------------------------------------------
     * @param  $contractnum ��ͬ��
     * @param  $contract_id ��ͬid
    +----------------------------------------------------------
     * @param $citypy ���ڳ���ƴ��
    +----------------------------------------------------------
     * @return bool
     */
    public function save_invoice_data($contractnum, $contract_id, $citypy = "nj") {
        load("@.contract_common");
        $invoiceRecords = get_invoice_data_by_no($citypy, $contractnum);
        if (empty($invoiceRecords) || (is_array($invoiceRecords) && count($invoiceRecords) == 0)) {
            return true;
        }
        //����ͬ��Ʊ��¼���뵽����ϵͳ�����ݿ���
        if (!empty($invoiceRecords)) {
            $billing_model = D("BillingRecord");
            $billing_status = $billing_model->get_invoice_status();

            $contract_model = D("Contract");
            $conf_where = "ID = '$contract_id'";
            $field_arr = array("CASE_ID");
            $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
            if (is_array($contract_info) && !empty($contract_info[0]['CASE_ID'])) {
                $scaleType = D('ProjectCase')->where('ID = ' . $contract_info[0]['CASE_ID'])->getField('SCALETYPE');
            }

            foreach ($invoiceRecords as $key => $val) {
                $invoice_data["INVOICE_MONEY"] = $val["money"];
                $invoice_data["TAX"] = $val["tax"];
                $invoice_data["INVOICE_NO"] = $val["invono"];
                $invoice_data["REMARK"] = $val["note"];
                $invoice_data["INVOICE_TIME"] = $val["date"];
                $invoice_data["STATUS"] = $billing_status["have_invoiced"];
                $invoice_data["CONTRACT_ID"] = $contract_id;
                $invoice_data["CASE_ID"] = $contract_info[0]["CASE_ID"];
                $invoice_data["CREATETIME"] = $val["date"];
                $invoice_data["INVOICE_TYPE"] = 1;
                if ($val['type']) {
                    // ��Ʊ���ͣ������Ʊ���Ͳ�Ϊ1��2���򽫷�Ʊ��������Ϊ�����
                    // ��������Ϊ1�����ѣ���2������ѣ�
                    if (!in_array($val['type'], array(1, 2))) {
                        $val['type'] = 2;
                    }
                    $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                }
                $insert_invoice_id = $billing_model->add_billing_info($invoice_data);
                if (!$insert_invoice_id) {
                    return false;
                } else {
                    //����������ϸ��¼
                    if ($scaleType == 3) {
                        $income_info['INCOME_FROM'] = 12;
                    } else if ($scaleType == 8) {
                        $income_info['INCOME_FROM'] = 23;
                    }
                    $taxrate = get_taxrate_by_citypy($citypy);
                    $tax = round($val["money"] / (1 + $taxrate) * $taxrate, 2);
                    $income_info['OUTPUT_TAX'] = $tax;

                    $income_info['CASE_ID'] = $contract_info[0]["CASE_ID"];
                    $income_info['ENTITY_ID'] = $contract_info[0]["ID"];
                    $income_info['INCOME'] = $val["money"];
                    $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                    $income_info['OCCUR_TIME'] = $val["date"];
                    $income_info['PAY_ID'] = $insert_invoice_id;
                    $income_info['INCOME_REMARK'] = u2g($val["note"]);
                    $income_info['ORG_ENTITY_ID'] = $contract_info[0]["ID"];
                    $income_info['ORG_PAY_ID'] = $insert_invoice_id;

                    $ProjectIncome_model = D("ProjectIncome");
                    $res = $ProjectIncome_model->add_income_info($income_info);
                    if (!$res) {
                        return false;
                    }
                }
            }
        }
        return $insert_invoice_id ? $insert_invoice_id : false;
    }
	
	 
}