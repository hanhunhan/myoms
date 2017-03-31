<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
	include dirname(__FILE__).'/FlowBase.php';
}else {
	die('Sorry. Not load FlowBase file.');
}
/**
 +------------------------------------------------------------------------------
 * projectset 流程成类
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
			if($this->cType=='pc') $res=1;//js_alert('办理成功', U('Flow/through'));
			else $res=1;//js_alert('办理成功' );
			$this->model->commit();
			
			//$this->UserLog->writeLog($data['flowId'],"__APP__","立项申请流程办理成功");

			
		} else {
			$this->model->rollback();
			$res=0;
			//js_alert('办理失败');
			
			//$this->UserLog->writeLog($data['flowId'],"__APP__","立项申请流程办理失败");
		}
		return $res;
		 
	}
	public function passWorkflow($data){//确定
        $result = $this->checkFxContract($data);  // 检查分销合同是否获取成功
        if (!$result) {
            return array(
                'status' => false,
                'message' => '获取分销合同数据失败，请稍后再试'
            );
        }
		$this->model->startTrans();
		$str = $this->workflow->passWorkflow($data);

		if ($str) {
			
			$res = $this->pass($data);
			if($res){
				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","立项申请流程同意成功");
				//js_alert('同意成功', U('Flow/through'));
				$result = 1;
			}else{
				//js_alert('同意失败 数据操作失败');
				$result = 0;
				$this->model->rollback();
			}
		} else {
			//js_alert('同意失败');
			$result = 0;
			//$this->UserLog->writeLog($data['flowId'],"__APP__","立项申请流程同意失败");
			$this->model->rollback();
			
		}
		return $result;
		 
	}
	public function notWorkflow($data){//否决
		//$prjId = $data['prjid'] ? $data['prjid'] : $data['CASEID'];
		$prjId = $data['recordId'];  
		$this->model->startTrans();
		$str = $this->workflow->notWorkflow($data);
		if ($str) {
			$project_model = D('Project');
            $res = $project_model->update_nopass_status($prjId);;//审核 不通过
			if($res){
				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","立项申请流程否决成功");
				if($this->cType=='pc') $result = 1;//js_alert('否决成功', U('Flow/already'));
				else $result = 1;//js_alert('否决成功' );

				
			}else{
				$this->model->rollback();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","立项申请流程否决失败 数据操作失败");
				//js_alert('否决失败 数据操作失败');
				$result = 0;
				
			}
		} else {
			$this->model->rollback();
			$result = 0;
			//js_alert('否决失败');
			//$this->UserLog->writeLog($data['flowId'],"__APP__","立项申请流程否决失败");
			
		}
		return $result;
		 
	}
	public function finishworkflow($data){//备案
		$auth = $this->workflow->flowPassRole($data['flowId']);
		if (!$auth) {
			//js_alert('未经过必经角色');
			$result = 0;
			exit;
		}
        $result = $this->checkFxContract($data);  // 检查分销合同是否获取成功
        if (!$result) {
            return array(
                'status' => false,
                'message' => '获取分销合同数据失败，请稍后再试'
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
				//js_alert('备案失败 数据操作失败');
				$result = 0;
				//$this->UserLog->writeLog($data['flowId'],"__APP__","立项申请流备案失败 数据操作失败");
			}

		} else {
			$this->model->rollback();
			//js_alert('备案失败');
			$result = 0;
			//$this->UserLog->writeLog($data['flowId'],"__APP__","立项申请流备案失败 ");
		}
		return $result; 
	}
	public function createworkflow($data){//创建工作流
		$prjId = $data['prjid'] ? $data['prjid'] : $data['recordId'];
		$auth = $this->workflow->start_authority('xiangmujuesuan');
		if (!$auth) {
			//js_alert("暂无权限");
			//$response['message'] = '未经过必经角色';
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
						//提交..申请
						$project_model = D('Project');
						$res = $project_model->update_check_status($prjId);//审核中
						if($res){
							$this->model->commit();
							$result = 1;
							//js_alert('提交成功', U('House/opinionFlow', $this->_merge_url_param));
							//$this->UserLog->writeLog($data['flowId'],"__APP__","立项申请流提交成功");
						}else{
							$this->model->rollback();
							//js_alert('备案失败 数据操作失败');
							$result = 0;
							//$this->UserLog->writeLog($data['flowId'],"__APP__","立项申请流备案失败 数据操作失败");
						}
						//exit;
					} else {
						$this->model->rollback();
						//js_alert('提交失败', U('House/opinionFlow', $this->_merge_url_param));
						$result = 0;
						//$this->UserLog->writeLog($data['flowId'],"__APP__","立项申请流提交失败");
						//exit;
					}
				} else {
					$result = 0;
					//js_alert('请不要重复提交', U('House/opinionFlow', $this->_merge_url_param));
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
                //查询项目合同信息
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
		//工作流ID
        $flowId = !empty($data['flowId']) ? intval($data['flowId']) : 0;
		//工作流关联业务ID
        $recordId = !empty($data['recordId']) ? intval($data['recordId']) : 0;
		$conres = $ress = $contract_id = $insert_reund_id = $res = $insert_invoice_id = $res2 = true;
		/**
			*  如果该项目存在非我方收筹
		*/
		$fwfscCase = D('ProjectCase')->where('PROJECT_ID = ' . $recordId . ' AND SCALETYPE = 8')->find();
		if (is_array($fwfscCase) && count($fwfscCase)) {
			$conres = $this->addFwfscIncomeContract($recordId, $fwfscCase['ID']); //****
		}

		$project_model = D('Project');
		$ress = $project_model->update_pass_status($data['recordId']);;//审核通过 //****

		/*如果该项目存在分销业务，则通过合同编号获取合同系统中合同信息，
		并存储在经管系统合同表中*/
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
			//查询项目合同信息
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
				//取工作流发起人所在城市
				$creator_info = $this->workflow->get_Flow_Creator_Info($flowId);
				$info['CITY_ID'] = $creator_info['CITY'];

				$contract_model = D('Contract');
				$contract_id = $contract_model->add_contract_info($info);//****

				/***同步合同开票和回款记录到经管系统***/
				if ($contract_id > 0) {
					//根据合同号和城市拼音，获取合同的回款记录,并将数据同步到经管系统
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
								//新增收益明细记录  
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

					//根据合同号和城市拼音，获取合同的开票记录,并将数据同步到经管系统
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
                                // 发票类型，如果发票类型不为1或2，则将发票类型设置为2(服务费)
                                // 否则设置为1（广告费）或2（服务费）
                                if (!in_array($val['type'], array(1, 2))) {
                                    $val['type'] = 2;
                                }
                                $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                            }
							$insert_invoice_id = $billing_model->add_billing_info($invoice_data);//****

							if ($insert_invoice_id) {
								//新增收益明细记录           
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
     * 增加非我非收筹合同
     * @param $projId 项目id
     * @param $caseId
     * @return bool 数据是否添加成功
     */
    private function addFwfscIncomeContract($projId, $caseId) {
        $contractInfo = D('Contract')->where('CASE_ID = ' . $caseId)->find();
        if (is_array($contractInfo) && count($contractInfo)) {
            return false;
        }

        $project = D('erp_project')->where('ID=' . $projId)->find();
        if (empty($project)) {
            return false;//项目不能为空
        }

        $contractNo = $project['CONTRACT'];
        $cityid = $project['CITY_ID'];  // 从项目列表中获取城市编号
        $sql = "select PY from ERP_CITY where ID=" . $cityid;
        $citypy = $this->model->query($sql);
        $citypy = strtolower($citypy[0]["PY"]);//用户城市拼音
        //获取合同基本信息
        load("@.contract_common");
        $fetchedData = getContractData($citypy, $contractNo);
        if ($fetchedData === false) {
			return false;//获取合同数据出错
            
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
        $toInsertData['ADD_TIME'] = date("Y-m-d H:i:s");  // 添加时间
        $toInsertData['CASE_ID'] = $caseId;  // 添加时间
        $toInsertData['CITY_PY'] = $citypy;
        $toInsertData['CITY_ID'] = $cityid;
        unset($fetchedData);

        // 执行事务
        //$this->model->startTrans();
        $insertedId = D("Contract")->add_contract_info($toInsertData);
        if ($insertedId !== false) {
            //根据合同号和城市拼音，获取合同的回款记录,并将数据同步到经管系统
            $insert_refund_id = $this->save_refund_data($contractNo, $insertedId, $citypy);
            //根据合同号和城市拼音，获取合同开票记录，并将数据同步到经管系统
            $insert_invoice_id = $this->save_invoice_data($contractNo, $insertedId, $citypy);
            if ($insert_invoice_id !== false && $insert_refund_id !== false) {
               // $this->model->commit();
                return true;
            } else {
               // $this->model->rollback();
                $error = '';
                if ($insert_refund_id == false) {
                    $error .= '获取合同的回款记录错误';
                }

                if ($insert_invoice_id == false) {
                    $error = empty($error) ? '获取合同的开票记录错误' :
                        $error . '， 获取合同的开票记录错误';
                }

                // 返回结果
                return false;
            }
        } else {
			return false;
           //添加合同出错
        }
    }

	 /**
     * +----------------------------------------------------------
     *根据合同号和城市拼音，获取合同的回款记录,并将数据同步到经管系统
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
        //将合同回款记录插入到经管系统的数据库中
        if (!empty($refundRecords)) {
            $contract_model = D("Contract");
            $payment_model = D("PaymentRecord");

            $conf_where = "ID = '" . $contract_id . "'";
            $field_arr = array("CASE_ID");
            $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
            // 获取项目的类型
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
                    //新增收益明细记录
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
     *根据合同号和城市拼音，获取合同的开票记录,并将数据同步到经管系统
     * +----------------------------------------------------------
     * @param  $contractnum 合同号
     * @param  $contract_id 合同id
    +----------------------------------------------------------
     * @param $citypy 所在城市拼音
    +----------------------------------------------------------
     * @return bool
     */
    public function save_invoice_data($contractnum, $contract_id, $citypy = "nj") {
        load("@.contract_common");
        $invoiceRecords = get_invoice_data_by_no($citypy, $contractnum);
        if (empty($invoiceRecords) || (is_array($invoiceRecords) && count($invoiceRecords) == 0)) {
            return true;
        }
        //将合同开票记录插入到经管系统的数据库中
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
                    // 发票类型，如果发票类型不为1或2，则将发票类型设置为服务费
                    // 否则设置为1（广告费）或2（服务费）
                    if (!in_array($val['type'], array(1, 2))) {
                        $val['type'] = 2;
                    }
                    $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                }
                $insert_invoice_id = $billing_model->add_billing_info($invoice_data);
                if (!$insert_invoice_id) {
                    return false;
                } else {
                    //新增收益明细记录
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