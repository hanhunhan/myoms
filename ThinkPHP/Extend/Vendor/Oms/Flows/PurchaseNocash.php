<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
	include dirname(__FILE__).'/FlowBase.php';
}else {
	die('Sorry. Not load FlowBase file.');
}
/**
 +------------------------------------------------------------------------------
 * 非付现成本申请
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
			//$this->UserLog->writeLog($data['flowId'],"__APP__","非付现成本申请流程办理成功");
			if($this->cType=='pc') $result =1;//js_alert('办理成功', U('Flow/through'));
			else $result =2;//js_alert('办理成功' );
			
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","非付现成本申请流程办理失败");
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
			//$this->UserLog->writeLog($data['flowId'],"__APP__","非付现成本申请流程同意成功");
			if($this->cType=='pc') $result = 3;//js_alert('同意成功', U('Flow/through'));
			else  $result = 4;//js_alert('同意成功' );
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","非付现成本申请流程同意失败");
			//js_alert('同意失败');
			$result = -3;
			
		}
		return $result;
		 
	}
	public function notWorkflow($data){//否决
		$this->model->startTrans();
		$str = $this->workflow->notWorkflow($data);//var_dump($data);
		if ($str) {
			 
			
			$this->model->commit();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","非付现成本申请流程否决成功");
			if($this->cType=='pc') $result = 5;//js_alert('否决成功', U('Flow/already'));
			else $result = 6;//js_alert('否决成功' );
			
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","非付现成本申请流程否决失败");
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
		}
		$this->model->startTrans();
		$str = $this->workflow->finishworkflow($data);

		//非付现成本转出项目本地和其他项目,增加类型不一致
		$res = $this->addIncomeCost($data);
		if ($str && $res) {
			 
			
			$this->model->commit();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","非付现成本申请流程备案成功");
			if($this->cType=='pc')  $result = 7;//js_alert('备案成功', U('Flow/already'));
			else $result = 8;//js_alert('备案成功' );
				
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","非付现成本申请流程备案失败");
			//js_alert('备案失败');
			$result = -6;
			
		}
		return $result; 
	}
	public function createworkflow($data){//创建工作流
		 
		
		$form = $this->workflow->createHtml();

		if ($data['savedata']) {

			$nonCashCostId = $_REQUEST['recordId'];
            $nonCashCostIds = explode('-', $nonCashCostId);
			$this->model->startTrans();
			if($this->addNonCashCostWorkFlow($this->workflow, 1, $nonCashCostIds, $data)) {
				// 工作流添加成功
				//js_alert('提交成功', U('Purchase/opinionFlow', $this->_merge_url_param));
				//exit;
				$this->model->commit();
				$result = 9;
			} else {
				// 工作流提添加失败
				//js_alert('提交失败', U('Purchase/opinionFlow', $this->_merge_url_param));
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
            $flow_data['ISNONCASH'] = 1; //是否为非付现工作流
            $insertedWorkFlow = $workflow->createworkflow($flow_data);
            if ($insertedWorkFlow !== false) {
                //$itsModel->startTrans();
                foreach($nonCashCostIds as $nonCashCostId)
                {
                    $updatedRows = $itsModel->where('ID = ' . $nonCashCostId)->save(array(
                        'STATUS' => 1  // 审核中状态
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
			//非付现成本存在费用类型，则转出项目为自己，添加两条成本，一条正的资金池费用，一条负的
			if ($nonCashCost['FEE_ID']) {
				//往成本表中添加记录,第三方费用 正值
				$cost_info = $this->costArr($data, $nonCashCost);
				$cost_insert_id = $project_cost_model->add_cost_info($cost_info);
				//负值，费用类型自选
				$cost_info = $this->costArr($data, $nonCashCost, 0);
				$cost_insert_id2 = $project_cost_model->add_cost_info($cost_info);
				if (!$cost_insert_id && !$cost_insert_id2) {
					return false;
				}
			} else {
				//无费用类型，判断是否是电商，插入收益记录，其他项目插入收益记录，不插入回款记录，只插入一条成本
				$cityId = M("Erp_project")->where("ID=".$nonCashCost['PROJECT_ID'])->getField("CITY_ID");
				$income_projectId =  M("Erp_project")->where("CONTRACT='{$nonCashCost['CONTRACT_NO']}' and CITY_ID=".$cityId." AND STATUS !=2")->getField('ID');
				$income_caseId = M("Erp_case")->where("PROJECT_ID=".$income_projectId." and SCALETYPE=".$nonCashCost['SCALETYPE'])->getField("ID");
				if ($nonCashCost['SCALETYPE'] == 1) { //电商
					$income_from = array(29, 30, 31);   //资金池冲抵电商

					foreach ($income_from as $income_status) {
						$income_info['INCOME_FROM'] = $income_status;
						$income_info['CASE_ID'] = $income_caseId;
						$income_info['ENTITY_ID'] = $data['RECORDID'];
						$income_info['ORG_ENTITY_ID'] = $data['RECORDID'];
						$income_info['PAY_ID'] = $data['RECORDID'];
						$income_info['ORG_PAY_ID'] = $data['RECORDID'];
						$income_info['INCOME'] = $nonCashCost['AMOUNT'];
						$income_info['INCOME_REMARK'] = '非付现成本收益';
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
					$income_info['INCOME_REMARK'] = '非付现成本收益';
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
		$cost_info['CASE_ID'] = $nonCashCost["CASE_ID"];            //案例编号 【必填】
		$cost_info['ENTITY_ID'] = $data['RECORDID'];                 //业务实体编号 【必填】
		$cost_info['EXPEND_ID'] = $data['RECORDID'];                  //成本明细编号 【必填】
		$cost_info['ORG_ENTITY_ID'] = $data['RECORDID'];                  //业务实体编号 【必填】
		$cost_info['ORG_EXPEND_ID'] = $data['RECORDID'];
		$cost_info['FEE'] = $nonCashCost["AMOUNT"];                // 成本金额 【必填】
		$cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];             //操作用户编号 【必填】
		$cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());        //发生时间 【必填】
		$cost_info['ISFUNDPOOL'] = 1;                                 //是否资金池（0否，1是） 【必填】
		$cost_info['ISKF'] = 1;                                     //是否扣非 【必填】
		$cost_info['FEE_REMARK'] = "资金池冲抵";             //费用描述 【选填】
		$cost_info['INPUT_TAX'] = 0;                                //进项税 【选填】
		$cost_info['FEE_ID'] = 80;                                  //成本类型ID 【必填】支付第三方费用
		$cost_info['EXPEND_FROM'] = 36;                             //成本来源
		if($iscost == 0 ){
			$cost_info['ISFUNDPOOL'] = 0;
			$cost_info['FEE_ID'] = $nonCashCost['FEE_ID'];
			$cost_info['FEE'] = 0-$nonCashCost["AMOUNT"];
		}
		return $cost_info;
	}
}