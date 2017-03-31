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
class BenefitFlow extends   FlowBase{
	 
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
			
			//$this->UserLog->writeLog($data['flowId'],"__APP__","预算外其他费用申请流程办理成功");

			
		} else {
			$this->model->rollback();
			$res=0;
			//js_alert('办理失败');
			
			//$this->UserLog->writeLog($data['flowId'],"__APP__","预算外其他费用申请流程办理失败");
		}
		return $res;
		 
	}
	public function passWorkflow($data){//确定
		$this->model->startTrans();
		$str = $this->workflow->passWorkflow($data);

		if ($str) {
			
			$res = $this->pass($data);
			if($res){


				$benefits = D('Erp_benefits')->where("ID='".$data["recordId"]."'")->find();
				//待支付业务费处理
				$sql = "select * from ERP_FINALACCOUNTS t where CASE_ID='".$benefits['CASE_ID']."' and TYPE=1";
				$finalaccounts = M()->query($sql);
				$xgfee = $finalaccounts[0]['TOBEPAID_YEWU'] > $benefits['AMOUNT']  ? $finalaccounts[0]['TOBEPAID_YEWU']-$benefits['AMOUNT']  : 0;
				if($xgfee!=$finalaccounts[0]['TOBEPAID_YEWU']){
					//D('Erp_finalaccounts')->where("CASE_ID='".$benefits['CASE_ID']."' and TYPE=1")->save(array('TOBEPAID_YEWU'=>$xgfee) );
				}




				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","预算外其他费用申请流程同意成功");
				//js_alert('同意成功', U('Flow/through'));
				$result = 1;
			}else{
				//js_alert('同意失败 数据操作失败');
				$result = $res;
				$this->model->rollback();
			}
		} else {
			//js_alert('同意失败');
			$result = 0;
			//$this->UserLog->writeLog($data['flowId'],"__APP__","预算外其他费用申请流程同意失败");
			$this->model->rollback();
			
		}
		return $result;
		 
	}
	public function notWorkflow($data){//否决
		$prjId =   $data['CASEID'];
		$this->model->startTrans();
		$str = $this->workflow->notWorkflow($data);
		if ($str) {
			$sql = " UPDATE ERP_BENEFITS SET STATUS = 4 WHERE ID=".$_REQUEST["recordId"];
            $res = D("Benefits")->execute($sql);
			if($res){
				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","预算外其他费用申请流程否决成功");
				if($this->cType=='pc') $result = 1;//js_alert('否决成功', U('Flow/already'));
				else $result = 1;//js_alert('否决成功' );

				
			}else{
				$this->model->rollback();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","预算外其他费用申请流程否决失败 数据操作失败");
				//js_alert('否决失败 数据操作失败');
				$result = 0;
				
			}
		} else {
			$this->model->rollback();
			$result = 0;
			//js_alert('否决失败');
			//$this->UserLog->writeLog($data['flowId'],"__APP__","预算外其他费用申请流程否决失败");
			
		}
		return $result;
		 
	}
	public function finishworkflow($data){//备案
		$auth = $this->workflow->flowPassRole($data['flowId']);
		//if (!$auth) {
			//js_alert('未经过必经角色');
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
				//待支付业务费处理
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
				//js_alert('备案失败 数据操作失败');
				$result = $res;
				//$this->UserLog->writeLog($data['flowId'],"__APP__","预算外其他费用申请流备案失败 数据操作失败");
			}

		} else {
			$this->model->rollback();
			//js_alert('备案失败');
			$result = 0;
			//$this->UserLog->writeLog($data['flowId'],"__APP__","预算外其他费用申请流备案失败 ");
		}
		return $result; 
	}
	public function createworkflow($data){//创建工作流
		$return = false;
		$flow_type_pinyin = 'yusuanqita';
		//$auth = $workflow->start_authority($flow_type_pinyin);
		if(!$auth)
		{
			//js_alert('暂无权限');
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
		//工作流ID
        $flowId = !empty($data['flowId']) ? intval($data['flowId']) : 0;
		//工作流关联业务ID
        $recordId = !empty($data['recordId']) ? intval($data['recordId']) : 0;
	 
		$benefits_model = D("Benefits");
        $case_model = D("ProjectCase");
		$case_id = $data["CASEID"];
		$case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
		$scale_type = $case_info[0]["SCALETYPE"];
		$search_arr = array("TYPE","CASE_ID","AMOUNT");
		$benefits_info = $benefits_model->get_info_by_id($data["recordId"],$search_arr);

		//如果项目成本（即已垫资金额） > 立预算总收益*垫资比例
		$is_overtop_limit = is_overtop_payout_limit($case_id,$benefits_info[0]['AMOUNT'],1);
		if($is_overtop_limit)
		{
			//js_alert("该项目成本已超出垫资额度或超出费用预算（总费用>开票收入*付现成本率），流程不允许备案通过！",
			//U("Benefits/otherBenefits",$this->_merge_url_param),1);
			//die;
			//$this->UserLog->writeLog($data['flowId'],"__APP__","该项目成本已超出垫资额度或超出费用预算（总费用>开票回款收入*付现成本率），流程不允许备案通过！");
			//return  false;
			return -1;
		}
	 
		$auth = $this->workflow->flowPassRole($flowId);
		if(!$auth){
			//js_alert('未经过必经角色');exit;
			//$this->UserLog->writeLog($data['flowId'],"__APP__","未经过必经角色");
			//return  false;
			return -2;
		}
		
	 
		$sql = " UPDATE ERP_BENEFITS SET STATUS = 3 WHERE ID=".$data["recordId"];
		$res = D("Benefits")->execute($sql);
		//工作流通过，添加成本记录                            
		$search_arr = array("TYPE","CASE_ID","AMOUNT");
		$benefits_info = $benefits_model->get_info_by_id($data["recordId"],$search_arr);
		$benefits_type = $benefits_info[0]["TYPE"];
		
		//往成本表中添加记录
		$cost_info['CASE_ID'] = $benefits_info[0]["CASE_ID"];            //案例编号 【必填】       
		$cost_info['ENTITY_ID'] = $data["recordId"];                 //业务实体编号 【必填】
		$cost_info['EXPEND_ID'] = $data["recordId"];                //成本明细编号 【必填】
		
		$cost_info['ORG_ENTITY_ID'] = $data["recordId"];                 //业务实体编号 【必填】
		$cost_info['ORG_EXPEND_ID'] = $data["recordId"];
		
		$cost_info['FEE'] = $benefits_info[0]["AMOUNT"];                // 成本金额 【必填】 
		$cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];             //操作用户编号 【必填】
		$cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());        //发生时间 【必填】
		$cost_info['ISFUNDPOOL'] = 0;                                 //是否资金池（0否，1是） 【必填】
		$cost_info['ISKF'] = 1;                                     //是否扣非 【必填】
		$cost_info['FEE_REMARK'] = "预算外费用申请";             //费用描述 【选填】
		$cost_info['INPUT_TAX'] = 0;                                //进项税 【选填】
		$cost_info['FEE_ID'] = 60;                                  //成本类型ID 【必填】
		//成本来源
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

	
	
	
	 
}