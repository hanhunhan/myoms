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

class Advert extends FlowBase {

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

        $flowId = $data['flowId'];
        $caseId = $data['caseId'];
        $recordId = $data['recordId'];

        $billing_model = D("BillingRecord");
        $cond_where = "FLOW_ID = ".$flowId;
        $update_arr = array("STATUS"=>5);
        $update_ret = $billing_model->update_info_by_cond($cond_where,$update_arr);

        //��ȡ��Ʊ����
        $dbRes = D("BillingRecord")->bRFromType($flowId);

        if($dbRes) {
            if ($dbRes[0]['FROMTYPE'] == 2) {
                $updateListStatus = D("DisplaceApply")->updateListStatus($dbRes[0]['FROMLISTID'], 1); //����״̬��δ����״̬
            }
        }

        //����Ƿ����ĺ�ͬ��Ʊ�����̷�����ö�Ӧ������Ա�������±�ѡ�п�Ʊ,�����䷢Ʊ״̬��Ϊδ��
        $case_model = D("ProjectCase");
        $cond_where = "ID = ".$caseId;
        $case_info = $case_model->get_info_by_cond($cond_where,array("SCALETYPE"));

        $upd = true;
        if($case_info[0]["SCALETYPE"] == 2) {
            $ret = $this->updateDistribution($flowId, $caseId, 5);
            if(!$ret)
                $upd = false;
        }

        if($flow_status && $update_ret && $upd && $updateListStatus!==false) {
            $this->model->commit();
            $flagStatus = true;
        }
        else
            $this->model->rollback();

        return $flagStatus;
    }

    /**
     * ������Ӧ��memeber_distribution���¼״̬
     * @param $flowId ������id
     * @param $caseId ����id
     * @param $targetStatus Ŀ��״̬
     */
    private function updateDistribution($flowId, $caseId, $targetStatus)
    {
        if (empty($flowId) || empty($caseId)) {
            return;
        }

        $aBillingRec = D('BillingRecord')->where('FLOW_ID = ' . $flowId)->find();
       $BillingRecordModel = D('BillingRecord');
        if (!empty($aBillingRec)) {
            $relateInvoiceID = $aBillingRec['ID'];
            //$memberDistributionModel = D("MemberDistribution");
            $cond_where = "CASE_ID = $caseId AND ID = $relateInvoiceID";
            $update_arr = array("STATUS" => $targetStatus);
            //��Ӧ��Ʊ��ϸ�������ɾ��
            $res = D("erp_commission_invoice_detail")->where(" BILLING_RECORD_ID = ".$relateInvoiceID )->delete();
            $result = $BillingRecordModel->update_info_by_cond($cond_where,$update_arr);
        }
        return $result;
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

        $this->model->startTrans();

        $flowId = $data['flowId'];
        $recordId = $data['recordId'];

        $billing_model = D("BillingRecord");
        $contract_model = D("Contract");

        $flow_status = $this->workflow->finishworkflow($data);

        $bill_ret = $billing_model->update_info_by_cond("FLOW_ID = ".$flowId,array("STATUS"=>3));
        $contract_ret = $contract_model->update_info_by_id($recordId, array("IS_NEED_INVOICE"=>1));


        if($flow_status && $bill_ret && $contract_ret) {
            $flagStatus = true;
            $this->model->commit();
        }
        else
            $this->model->rollback();

        return $flagStatus;

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
        $invoiceid = $data['invoiceId'];

        $auth = $this->workflow->start_authority($flowTypePY);

        if(!$auth) {
            $response['message'] = '�Բ���������Ȩ�ޣ�';
            return $response;
        }

        $workNum = $this->workflow->createworkflow($data);

        $sql = "UPDATE ERP_BILLING_RECORD SET STATUS=2,FLOW_ID = $workNum WHERE ID=".$invoiceid;
        $res = $this->model->execute($sql);
        if ($res !== false) {
            $scaleType = D('ProjectCase')->where("ID = {$data['CASEID']}")->getField("SCALETYPE");
            if ($scaleType == 2) {
                $res = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$invoiceid}")->save(array(
                    "INVOICE_STATUS" => 2
                ));
            }
        }


        if($workNum && $res !== false){
            $this->model->commit();
            $return = true;
        }
        else{
            $this->model->rollback();
        }

        return $return;

    }
}