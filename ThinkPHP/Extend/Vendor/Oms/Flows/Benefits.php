<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * ����������������
 * Created by PhpStorm.
 * User: superkemi
 */

class Benefits extends FlowBase {

    public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();
    }

    /**
     * @param $flowId ������ID
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
     * ����������
     * @param $data
     */
    function createworkflow($data){

        $return = false;

        $this->model->startTrans();

        $flowTypePY = $data['flowTypePY'];
        $recordId = $data['recordId'];

        $auth = $this->workflow->start_authority($flowTypePY);

        if(!$auth) {
            $response['message'] = '�Բ���������Ȩ�ޣ�';
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
     * ת��
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
     * ͨ��
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
     * ���
     * @param $data
     * @return bool
     */
    function notWorkflow($data) {

        $flagStatus = false;

        $this->model->startTrans();

        //����������
        $flow_status = $this->workflow->notWorkflow($data);

        $caseId = $data["caseId"];
        $recordId = $data["recordId"];

        $case_model = D("ProjectCase");
        $case_info = $case_model->get_info_by_id($caseId,array("SCALETYPE"));
        $scale_type = $case_info[0]["SCALETYPE"];

        $sql = " UPDATE ERP_BENEFITS SET STATUS = 4 WHERE ID=".$recordId;
        $res = D("Benefits")->execute($sql);

        //����ǻ
        $hd_flag = true;
        if ($scale_type == 4) {
            // ҵ�����ҵ����ISVALID: 0=��δ��� -1=�����
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
     * ����
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
            $response['message'] = '�Բ��𣬸ù�����δ�����ؾ���ɫ��';
            return $response;
        }

        $flagStatus = false;

        //ҵ��ID
        $caseId = $data["caseId"];
        //������rocordId
        $recordId = $data["recordId"];

        //��ȡҵ������
        $case_model = D("ProjectCase");
        $case_info = $case_model->get_info_by_id($caseId,array("SCALETYPE"));
        $scale_type = $case_info[0]["SCALETYPE"];

        //��ȡ��������
        $benefits_model = D("Benefits");
        $search_arr = array("TYPE","CASE_ID","AMOUNT");
        $benefits_info = $benefits_model->get_info_by_id($recordId,$search_arr);
        $benefits_type = $benefits_info[0]['TYPE'];


        $this->model->startTrans();

        //�������������
        $flow_status = $this->workflow->finishworkflow($_REQUEST);

        //�����Ŀ�ɱ������ѵ��ʽ� > ��Ԥ��������*���ʱ���
        $is_overtop_limit = is_overtop_payout_limit($caseId,$benefits_info[0]['AMOUNT'],1);

        if($is_overtop_limit)
        {
            $this->model->rollback();
            $response['message'] = g2u('����Ŀ�ɱ��ѳ������ʶ�Ȼ򳬳�����Ԥ�㣨�ܷ���>��Ʊ�ؿ�����*���ֳɱ��ʣ������̲�������ͨ����');
            return $response;
        }

        $sql = " UPDATE ERP_BENEFITS SET STATUS = 3 WHERE ID=".$recordId;
        $res = D("Benefits")->execute($sql);

		

        //���ɱ�������Ӽ�¼
        $cost_info['CASE_ID'] = $benefits_info[0]["CASE_ID"];            //������� �����
        $cost_info['ENTITY_ID'] = $_REQUEST["RECORDID"];                 //ҵ��ʵ���� �����
        $cost_info['EXPEND_ID'] = $_REQUEST["RECORDID"];                //�ɱ���ϸ��� �����

        $cost_info['ORG_ENTITY_ID'] = $_REQUEST["RECORDID"];                 //ҵ��ʵ���� �����
        $cost_info['ORG_EXPEND_ID'] = $_REQUEST["RECORDID"];

        $cost_info['FEE'] = $benefits_info[0]["AMOUNT"];                // �ɱ���� �����
        $cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];             //�����û���� �����
        $cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());        //����ʱ�� �����
        $cost_info['ISFUNDPOOL'] = 0;                                 //�Ƿ��ʽ�أ�0��1�ǣ� �����
        $cost_info['ISKF'] = 1;                                     //�Ƿ�۷� �����
        $cost_info['FEE_REMARK'] = "ҵ���������";             //�������� ��ѡ�
        $cost_info['INPUT_TAX'] = 0;                                //����˰ ��ѡ�
        $cost_info['FEE_ID'] = 60;                                  //�ɱ�����ID �����

        //�ɱ���Դ
        $hd_flag = true;
        if($benefits_type == 0)
        {
            $cost_info['EXPEND_FROM'] = 17;
            //����ǻ
            if ($scale_type == 4) {
                // ҵ�����ҵ����ISVALID: 0����δ��� -1�������
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

		//��֧��ҵ��Ѵ���
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