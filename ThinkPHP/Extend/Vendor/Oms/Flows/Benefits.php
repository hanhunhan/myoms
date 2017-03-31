<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * 独立活动立项工作流处理
 * Created by PhpStorm.
 * User: superkemi
 */

class Benefits extends FlowBase {

    public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();
    }

    /**
     * @param $flowId 工作流ID
     * @return bool
     */
    function nextstep($flowId) {
        $this->model->startTrans();

        $flagStatus = $this->workflow->nextstep($flowId);

        if(!$flagStatus)
            $this->model->rollback();
        else
            $this->model->commit();

        return $flagStatus;
    }

    function createHtml($flowId) {
        // TODO: Implement createHtml() method.
    }

    /**
     * 创建工作流
     * @param $data
     */
    function createworkflow($data){

        $return = false;

        $this->model->startTrans();

        $flowTypePY = $data['flowTypePY'];
        $recordId = $data['recordId'];

        $auth = $this->workflow->start_authority($flowTypePY);

        if(!$auth) {
            $response['message'] = '对不起，您暂无权限！';
            return $response;
        }

        $flagStatus = $this->workflow->createworkflow($data);

        $sql = " UPDATE ERP_BENEFITS SET STATUS = 2 WHERE ID = " . $recordId;
        $res = D("Benefits")->execute($sql);

        if($flagStatus && $res){
            $this->model->commit();
            $return = true;
        }
        else{
            $this->model->rollback();
        }

        return $return;

    }

    /**
     * 转交
     * @param $data
     * @return bool
     */
    function handleworkflow($data) {

        $flagStatus = false;

        $this->model->startTrans();

        $flag_status = $this->workflow->handleworkflow($data);

        if($flag_status) {
            $flagStatus = true;
            $this->model->commit();
        }
        else
            $this->model->rollback();

        return $flagStatus;
    }

    /**
     * 通过
     * @param $data
     * @return bool
     */
    function passWorkflow($data) {

        $flagStatus = false;

        $this->model->startTrans();

        $flag_status = $this->workflow->passWorkflow($data);

        if($flag_status) {
            $flagStatus = true;
            $this->model->commit();
        }
        else
            $this->model->rollback();

        return $flagStatus;
    }

    /**
     * 否决
     * @param $data
     * @return bool
     */
    function notWorkflow($data) {

        $flagStatus = false;

        $this->model->startTrans();

        //工作流操作
        $flow_status = $this->workflow->notWorkflow($data);

        $caseId = $data["caseId"];
        $recordId = $data["recordId"];

        $case_model = D("ProjectCase");
        $case_info = $case_model->get_info_by_id($caseId,array("SCALETYPE"));
        $scale_type = $case_info[0]["SCALETYPE"];

        $sql = " UPDATE ERP_BENEFITS SET STATUS = 4 WHERE ID=".$recordId;
        $res = D("Benefits")->execute($sql);

        //如果是活动
        $hd_flag = true;
        if ($scale_type == 4) {
            // 业务津贴业务中ISVALID: 0=尚未审核 -1=已审核
            $hd = D('erp_actibudgetfee')->where("CASE_ID = {$caseId} AND ISVALID = 0 AND FEE_ID = 98")->save(array(
                'ISVALID' => 0
            ));
            if($hd===false)
                $hd_flag = false;
        }


        if($flow_status && $res && $hd_flag) {
            $this->model->commit();
            $flagStatus = true;
        }
        else
            $this->model->rollback();

        return $flagStatus;
    }

    /**
     * 备案
     * @param $data
     * @return bool
     */
    function finishworkflow($data) {
        $response = array(
            'status' => false,
            'message' => ''
        );

        $auth = $this->workflow->flowPassRole($data['flowId']);

        if(!$auth) {
            $response['message'] = '对不起，该工作流未经过必经角色！';
            return $response;
        }

        $flagStatus = false;

        //业务ID
        $caseId = $data["caseId"];
        //工作流rocordId
        $recordId = $data["recordId"];

        //获取业务类型
        $case_model = D("ProjectCase");
        $case_info = $case_model->get_info_by_id($caseId,array("SCALETYPE"));
        $scale_type = $case_info[0]["SCALETYPE"];

        //获取津贴数据
        $benefits_model = D("Benefits");
        $search_arr = array("TYPE","CASE_ID","AMOUNT");
        $benefits_info = $benefits_model->get_info_by_id($recordId,$search_arr);
        $benefits_type = $benefits_info[0]['TYPE'];


        $this->model->startTrans();

        //工作流更新情况
        $flow_status = $this->workflow->finishworkflow($_REQUEST);

        //如果项目成本（即已垫资金额） > 立预算总收益*垫资比例
        $is_overtop_limit = is_overtop_payout_limit($caseId,$benefits_info[0]['AMOUNT'],1);

        if($is_overtop_limit)
        {
            $this->model->rollback();
            $response['message'] = g2u('该项目成本已超出垫资额度或超出费用预算（总费用>开票回款收入*付现成本率），流程不允许备案通过！');
            return $response;
        }

        $sql = " UPDATE ERP_BENEFITS SET STATUS = 3 WHERE ID=".$recordId;
        $res = D("Benefits")->execute($sql);

		

        //往成本表中添加记录
        $cost_info['CASE_ID'] = $benefits_info[0]["CASE_ID"];            //案例编号 【必填】
        $cost_info['ENTITY_ID'] = $_REQUEST["RECORDID"];                 //业务实体编号 【必填】
        $cost_info['EXPEND_ID'] = $_REQUEST["RECORDID"];                //成本明细编号 【必填】

        $cost_info['ORG_ENTITY_ID'] = $_REQUEST["RECORDID"];                 //业务实体编号 【必填】
        $cost_info['ORG_EXPEND_ID'] = $_REQUEST["RECORDID"];

        $cost_info['FEE'] = $benefits_info[0]["AMOUNT"];                // 成本金额 【必填】
        $cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];             //操作用户编号 【必填】
        $cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());        //发生时间 【必填】
        $cost_info['ISFUNDPOOL'] = 0;                                 //是否资金池（0否，1是） 【必填】
        $cost_info['ISKF'] = 1;                                     //是否扣非 【必填】
        $cost_info['FEE_REMARK'] = "业务津贴申请";             //费用描述 【选填】
        $cost_info['INPUT_TAX'] = 0;                                //进项税 【选填】
        $cost_info['FEE_ID'] = 60;                                  //成本类型ID 【必填】

        //成本来源
        $hd_flag = true;
        if($benefits_type == 0)
        {
            $cost_info['EXPEND_FROM'] = 17;
            //如果是活动
            if ($scale_type == 4) {
                // 业务津贴业务中ISVALID: 0：尚未审核 -1：已审核
                $updated = D('erp_actibudgetfee')->where("CASE_ID = {$caseId} AND ISVALID = 0 AND FEE_ID = 98")->save(array(
                    'ISVALID' => -1
                ));
                if ($updated === false)
                    $hd_flag = false;
            }
        }
        else if($benefits_type == 1)
        {
            $cost_info['EXPEND_FROM'] = 18;
        }
        $project_cost_model = D("ProjectCost");
        $cost_insert_id = $project_cost_model->add_cost_info($cost_info);

		//待支付业务费处理
		$sql = "select * from ERP_FINALACCOUNTS t where CASE_ID='".$cost_info['CASE_ID']."' and TYPE=1";
		$finalaccounts = M()->query($sql);
		$xgfee = $finalaccounts[0]['TOBEPAID_YEWU'] > $cost_info['FEE']  ? $finalaccounts[0]['TOBEPAID_YEWU']-$cost_info['FEE']  : 0;
		if($xgfee!=$finalaccounts[0]['TOBEPAID_YEWU'] && $finalaccounts[0]['STATUS']==2){
			D('Erp_finalaccounts')->where("CASE_ID='".$cost_info['CASE_ID']."' and TYPE=1")->save(array('TOBEPAID_YEWU'=>$xgfee) );
		}


        if($flow_status && $res && $cost_insert_id && $hd_flag) {
            $flagStatus = true;
            $this->model->commit();
        }
        else
            $this->model->rollback();

        return $flagStatus;
    }

}