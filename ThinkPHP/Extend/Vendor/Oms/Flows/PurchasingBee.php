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
class PurchasingBee extends   FlowBase{
	 
	protected $workflow = null;//
	protected $city =null;

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
			//$this->UserLog->writeLog($data['flowId'],"__APP__","小蜜蜂超额申请流程办理成功");
			if($this->cType=='pc') $result =1;//js_alert('办理成功', U('Flow/through'));
			else $result =2;//js_alert('办理成功' );
			
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","小蜜蜂超额申请流程办理失败");
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
			//$this->UserLog->writeLog($data['flowId'],"__APP__","小蜜蜂超额申请流程同意成功");
			if($this->cType=='pc') $result = 3;//js_alert('同意成功', U('Flow/through'));
			else  $result = 4;//js_alert('同意成功' );
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","小蜜蜂超额申请流程同意失败");
			//js_alert('同意失败');
			$result = -3;
			
		}
		return  $result;
		 
	}
	public function notWorkflow($data){//否决
		$this->model->startTrans();
		$str = $this->workflow->notWorkflow($data);  
		$this->city = M("Erp_flows")->where("ID=".$data['flowId'])->find();
		
		if ($str) {

			$res = $this->_bee_option_follow_fail($data['recordId']);
			if($res  ){
				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","小蜜蜂超额申请流程否决成功");
				if($this->cType=='pc') $result = 5;//js_alert('否决成功', U('Flow/already'));
				else $result = 6;//js_alert('否决成功' );
			}else {
				$this->model->rollback();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","小蜜蜂超额申请流程否决失败");
				 
				$result = -31;
			}
			
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","小蜜蜂超额申请流程否决失败");
			//js_alert('否决失败');
			$result = -4;
		}
		return  $result;
		 
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
		$this->city = M("Erp_flows")->where("ID=".$data['flowId'])->find();
		if ($str) {
			$res = $this->_bee_option_follow_success($data['recordId']);
			if($res  ){
				$this->model->commit();
				//$this->UserLog->writeLog($data['flowId'],"__APP__","小蜜蜂超额申请流程备案成功");
				if($this->cType=='pc')  $result = 7;//js_alert('备案成功', U('Flow/already'));
				else $result = 8;//js_alert('备案成功' );
				
			}
		} else {
			$this->model->rollback();
			//$this->UserLog->writeLog($data['flowId'],"__APP__","小蜜蜂超额申请流程备案失败");
			//js_alert('备案失败');
			$result = -6;
			
		}
		return $result; 
	}
	public function createworkflow($data){//创建工作流
		 
		
		$form = $this->workflow->createHtml();
		
		if ($data['savedata']) {
			$this->model->startTrans();
			$model_bee_work = D('PurchaseBeeDetails');
			$model_bee      = D('PurchaseList');
			//$purchase_id = !empty($data['purchase_id']) ? intval($data['purchase_id']) : 0;
			$beeId = !empty($_REQUEST['recordId']) ?$_REQUEST['recordId'] : 0;//项目ID
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
				//更新小蜜蜂明细是否已发布流程状态
				$model_bee->where('ID='.$beeId)->save(array('IS_APPLY_PROCESS'=>1));
				$model_bee_work->where("ID IN ($beeDetailsId)")->save(array('STATUS'=>4));
				//js_alert('提交成功',U('Purchasing/bee',$this->_merge_url_param));
				$this->model->commit();
				$result = 9;
				//exit;
			}else{
				//js_alert('提交失败',U('Purchasing/BeeOpinionFlow',$this->_merge_url_param));
				//exit;
				$this->model->rollback();
				$result = -7;
			}
		}
		return $result;
		 
	}


	 /**
     * 超额流程审批通过自动生成报销申请
     * @param unknown $bee_id
     */
    private function _bee_option_follow_success($bee_id){
        //实例化对象
        $model_bee_work = D('PurchaseBeeDetails');
        $model_bee      = D('PurchaseList');
        //小蜜蜂采购明细
        $bee = $model_bee->find($bee_id);
        if (empty($bee)){
            return false;//小蜜蜂采购明细不存在
        }
        //获取所有提交的需要报销的小蜜蜂采购明细任务
        $bee_list = $model_bee_work->where("P_ID=$bee_id AND STATUS=4")->select();
        $money_total = 0;
        foreach ($bee_list as $key=>$val){
            $need_change_status[] = $val['ID'];
            $money_total+=$val['REIM_MONEY'];
        }
        //审核完毕生成报销申请
        $reim_list_model = D('ReimbursementList');      //报销申请单MODEL
        $reim_detail_model = D('ReimbursementDetail');  //报销明细MODEL
        $reim_list_model->startTrans();
        //生成报销申请单
        $uid = $bee['P_ID'];//intval($_SESSION['uinfo']['uid']);//当前用户编号
		$user = M('Erp_users')->where("ID=$uid")->find();
        $user_truename = $user['NAME'];  //$_SESSION['uinfo']['tname'];//当前用户姓名
        $city_id = intval($this->city['CITY']);//当前城市编号
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
            return false;  //添加报销申请失败
        }
        //生成报销明细
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
                return false;  //添加报销明细失败
            }
        }
        //修改小蜜蜂任务报销状态
        $need_change_status = implode(',', $need_change_status);
        $update_result = $model_bee_work->where('ID IN ('.$need_change_status.')')->save(array('STATUS'=>1,'CSTATUS'=>1));
        if (!$update_result){
            $reim_list_model->rollback();
            return false;  //修改小蜜蜂任务报销状态失败
        }
        $reim_list_model->commit();
        return true;
    }
	/**
     * 超额流程审批通过自动生成报销申请
     * @param unknown $bee_id
     */
    private function _bee_option_follow_fail($bee_id){
        //实例化对象
        $model_bee_work = D('PurchaseBeeDetails');
        $model_bee      = D('PurchaseList');
        //小蜜蜂采购明细
        $bee = $model_bee->find($bee_id); 
        if (empty($bee)){
            return false;//小蜜蜂采购明细不存在
        }
        //获取所有提交的需要报销的小蜜蜂采购明细任务
        $bee_list = $model_bee_work->where("P_ID=$bee_id AND STATUS=4")->select();
        foreach ($bee_list as $key=>$val){
            $need_change_status[] = $val['ID'];
            //$money_total+=$val['REIM_MONEY'];
        } 
        //审核完毕生成报销申请
       // $reim_list_model = D('ReimbursementList');      //报销申请单MODEL
        //$reim_detail_model = D('ReimbursementDetail');  //报销明细MODEL
        M()->startTrans();
        //生成报销申请单
        $uid = intval($_SESSION['uinfo']['uid']);//当前用户编号
        $user_truename = $_SESSION['uinfo']['tname'];//当前用户姓名
        $city_id = intval($this->city['CITY']);//当前城市编号
         
		$project_cost_model = D("ProjectCost");
        //生成报销明细
		$cost_insert_id = true;
        foreach ($bee_list as $key=>$value){
            $cost_info = array();
			$cost_info['CASE_ID'] = $bee["CASE_ID"]; //案例编号 【必填】       
			$cost_info['ENTITY_ID'] = $bee["PR_ID"];                                 
			$cost_info['EXPEND_ID'] = $bee["ID"];                            
			$cost_info['ORG_ENTITY_ID'] = $bee["PR_ID"];                    
			$cost_info['ORG_EXPEND_ID'] = $bee["ID"];                  //业务实体编号 【必填】
			$cost_info['FEE'] = -$value['REIM_MONEY'];                // 成本金额 【必填】 
			$cost_info['ADD_UID'] = $bee["P_ID"];//$_SESSION["uinfo"]["uid"];            //操作用户编号 【必填】
			$cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());        //发生时间 【必填】
			$cost_info['ISFUNDPOOL'] = $bee["IS_FUNDPOOL"];                  //是否资金池（0否，1是） 【必填】
			$cost_info['ISKF'] = $bee["IS_KF"];                             //成本类型ID 【必填】
			//$cost_info['INPUT_TAX'] = $v["INPUT_TAX"];                  //进项税 【选填】
			$cost_info['FEE_ID'] =  $bee["FEE_ID"];   
			$cost_info['EXPEND_FROM'] = 31; //?
			$cost_info['FEE_REMARK'] = "采购报销超额申请驳回";//成本类型ID 【必填】
			$cost_insert_id = $project_cost_model->add_cost_info($cost_info);
			if(!$cost_insert_id){
				$cost_insert_id = false;
				break;
			}
		}
        //修改小蜜蜂任务报销状态
        $need_change_status = implode(',', $need_change_status);
        $update_result = $model_bee_work->where('ID IN ('.$need_change_status.')')->save(array('STATUS'=>3,'CSTATUS'=>1));
        if (!$update_result || !$cost_insert_id){
            M()->rollback();
            return false;  //修改小蜜蜂任务报销状态失败
        }
		send_result_to_zk($need_change_status,$this->city['CITY'] );//同步到众客
        M()->commit();
        return true;
    }

	
	
	
	 
}