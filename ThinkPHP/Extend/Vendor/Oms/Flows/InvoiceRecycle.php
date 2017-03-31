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

class InvoiceRecycle extends FlowBase {

    public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();
    }

    /**
    +----------------------------------------------------------
    * ����Ʊ��ϸ����ɾ����Ʊ����(����)
    +----------------------------------------------------------
    * @param $invoice_recycle_details_id
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function delete_from_details($invoice_recycle_details_id)
    {
        $flagStatus = true;

        //ɾ������Ʊ��ϸ���
        if($invoice_recycle_details_id > 0)
        {
            $invoice_recycle_model = D('InvoiceRecycle');
            $update_num = $invoice_recycle_model->del_invoice_recycle_detail_by_id($invoice_recycle_details_id);

            if(!$update_num)
                return false;

            if($update_num > 0 )
            {
                $commission_model = D("CommissionBack");
                $invoice_recycle_model = D("InvoiceRecycle");

                //����ID��ȡ��Ʊ����Ա���
                $invoice_recycle_info = $invoice_recycle_model->get_invoice_recycle_detail_info_by_id($invoice_recycle_details_id,array("MID"));
                $mid = $invoice_recycle_info["MID"];

                $conf_where = "MID = $mid";
                //ɾ��Ӷ������
                $del_result = $commission_model->del_commission_info_by_conf($conf_where);
            }
        }
        else
        {
            $flagStatus = false;
        }

        return $flagStatus;
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

        $recordId = intval($data['recordId']);

        $this->model->startTrans();

        //����������
        $flow_status = $this->workflow->notWorkflow($data);

        //ҵ�����
        $invoice_recycle_model = D('InvoiceRecycle');

        //��Ʊ���뵥��ֹ
        $list_update_num = $invoice_recycle_model->sub_invoice_recycle_list_to_stop($recordId);

        //��Ʊ��ϸ��ֹ
        $update_num = $invoice_recycle_model->sub_invoice_recycle_detail_to_stop($recordId);

        //��ȡmid���ַ���
        $mids = M("erp_invoice_recycle_detail")->query("select mid from erp_invoice_recycle_detail where list_id = $recordId");
        $mids = array2new($mids);
        $mids_str = implode(",",$mids);
        //Ӷ������(״ֵ̬��Ϊ4)
        $ret_commission_back = M("erp_commission_back")->query("update ERP_COMMISSION_BACK set status = 4 where  mid in ($mids_str)");

        if($flow_status) {
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

        $recordId = intval($data['recordId']);

        $this->model->startTrans();

        $flow_status = $this->workflow->finishworkflow($_REQUEST);

        //��ƱMODEL
        $invoice_recycle_model = D('InvoiceRecycle');

        //�޸���ϸ��Ʊ��ϸ�ж�Ӧ��Ա�ķ�Ʊ״̬Ϊ�ѻ���
        $invoice_recycle_status = $invoice_recycle_model->get_conf_invoice_recycle_status();

        $invoice_recycle_success_status = !empty($invoice_recycle_status['invoice_recycle_success']) ?
            $invoice_recycle_status['invoice_recycle_success']: '';

        $cond_where = "LIST_ID =  '".$recordId."'";
        $cond_where .= " AND STATUS = '".$invoice_recycle_success_status."'";

        $mid = $invoice_recycle_model->get_invoice_recycle_detail_info_by_cond($cond_where,array("MID"));
        foreach($mid as $key=>$val)
        {
            $mids[] = $val["MID"];
        }

        $member_model = D("Member");
        $invoice_status_arr = $member_model->get_conf_invoice_status();//��Ա��Ʊ״̬����
        $member_up_num = $member_model->update_info_by_id($mids,array("INVOICE_STATUS"=>$invoice_status_arr["callback"]));

        if($flow_status && $member_up_num) {
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
     * @return bool
     */
    function createworkflow($data) {
        // TODO: Implement createworkflow() method.
        $return = false;

        $this->model->startTrans();

        $flowTypePY = $data['flowTypePY'];

        $auth = $this->workflow->start_authority($flowTypePY);

        if(!$auth) {
            $response['message'] = '�Բ���������Ȩ�ޣ�';
            return $response;
        }

        $flagStatus = $this->workflow->createworkflow($data);

        if($flagStatus){
            $this->model->commit();
            $return = true;
        }
        else{
            $this->model->rollback();
        }

        return $return;
    }
}