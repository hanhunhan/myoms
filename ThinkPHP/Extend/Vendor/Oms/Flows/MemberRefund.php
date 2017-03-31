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

class MemberRefund extends FlowBase {

    public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();

        //���ػ�Աģ�鹫�ú����ļ�
        load("@.member_common");
    }

    /**
     * ɾ���˿���ϸ���˿����뵥֮���ϵ���˳��˿����뵥��
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function delete_details_from_audit_list($ids)
    {

        $flagStatus = true;

        $cond_where = "";

        if(!empty($ids))
        {
            if(is_array($ids) && !empty($ids))
            {
                $ids_str = implode(',', $ids);
                $cond_where = " ID IN (".$ids_str.")";
            }
            else
            {
                $id  = intval($ids);
                $cond_where = " ID = '".$id."'";
            }

            $no_sub_status  = D("MemberRefund")->get_conf_refund_status();
            $no_sub_status = $no_sub_status['refund_delete'];

            $update_arr['LIST_ID'] =  '';
            $update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
            $update_arr['REFUND_STATUS'] = $no_sub_status;

            $ret = M("Erp_member_refund_detail")->where($cond_where)->save($update_arr);

            if(!$ret)
                return false;
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
        $refund_model = D('MemberRefund');

        //�˿����뵥��ֹ
        $list_update_num = $refund_model->sub_refund_list_to_stop($recordId);

        //�˿���ϸ��ֹ
        $update_num = $refund_model->sub_refund_detail_to_stop($recordId);

        //�����˿��ȡ�˿���ϸ��Ϣ
        $refund_details = array();
        $refund_details = $refund_model->get_refund_detail_by_listid($recordId,
            array('PAY_ID', 'REFUND_STATUS'));

        $update_pay_status = true;

        //���¸�����ϸδ�����˿�
        if(is_array($refund_details) && !empty($refund_details))
        {
            $member_pay_model = D('MemberPay');

            //�˿���ϸ״̬
            $refund_status = $refund_model->get_conf_refund_status();
            $pay_refund_status = $member_pay_model->get_conf_refund_status();

            foreach($refund_details as $key => $value)
            {
                if(!empty($refund_status) && $value['REFUND_STATUS'] == $refund_status['refund_stop'])
                {
                    $pay_id = $value['PAY_ID'];
                    $update_arr['REFUND_STATUS'] = $pay_refund_status['no_refund'];
                    $update_num_pay = $member_pay_model->update_info_by_id($pay_id, $update_arr);
                    if(!$update_num_pay) {
                        $update_pay_status = false;
                        break;
                    }
                }
            }
        }

        if($flow_status && $list_update_num && $update_num && $update_pay_status) {
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
            $response['message'] = g2u('�Բ��𣬸ù�����δ�����ؾ���ɫ��');
            return $response;
        }

        $flagStatus = false;

        $recordId = intval($data['recordId']);

        $this->model->startTrans();

        $flow_status = $this->workflow->finishworkflow($_REQUEST);

        //�˿�MODEL
        $refund_model = D('MemberRefund');

        //�˿����뵥���
        $list_update_num = $refund_model->sub_refund_list_to_completed($recordId);

        //�˿���ϸ�˿�ɹ�
        $update_num = $refund_model->sub_refund_detail_to_success($recordId);

        //�����˿��ȡ�˿���ϸ��Ϣ
        $refund_details = array();
        $refund_details = $refund_model->get_refund_detail_by_listid($recordId,
            array('ID', 'MID', 'PAY_ID', 'REFUND_MONEY', 'REFUND_STATUS', 'APPLY_UID', 'UPDATETIME'));

        //���¸�����ϸδ�����˿�
        if(is_array($refund_details) && !empty($refund_details))
        {
            $member_model = D('Member');
            $member_pay_model = D('MemberPay');
            $income_model = D('ProjectIncome');
            $project_cost_model = D("ProjectCost");

            //�˿���ϸ״̬
            $refund_status = $refund_model->get_conf_refund_status();
            $pay_refund_status = $member_pay_model->get_conf_refund_status();
            $invoice_status = $member_model->get_conf_invoice_status();
            $not_open_arr = array($invoice_status['no_invoice'] , $invoice_status['apply_invoice']);


            $flag = true;
            foreach($refund_details as $key => $value)
            {
                if(!empty($refund_status) && $value['REFUND_STATUS'] == $refund_status['refund_success'])
                {
                    $member_update_arr = array();

                    $mid = intval($value['MID']);

                    /***���¸�����ϸ�˿�״̬���˿���***/
                    $pay_id = $value['PAY_ID'];
                    $update_arr = array();
                    $update_arr['REFUND_STATUS'] = $pay_refund_status['no_refund'];
                    $update_arr['REFUND_MONEY'] = array('exp', "REFUND_MONEY + " .$value['REFUND_MONEY']);
                    $update_arr['REFUND_TIME'] = date('Y-m-d H:i:s');
                    $update_num_pay = $member_pay_model->update_info_by_id($pay_id, $update_arr);

                    //���ݻ�ԱID��ȡ��Ա��Ϣ
                    $search_field = array('PRJ_NAME','REALNAME','MOBILENO','CARDTIME','LEAD_TIME','SIGNTIME','PRJ_ID','CASE_ID', 'PAID_MONEY', 'UNPAID_MONEY', 'REDUCE_MONEY', 'INVOICE_STATUS','INVOICE_NO','CITY_ID');
                    $member_info = $member_model->get_info_by_id($mid, $search_field);


                    //��ĿID
                    $prj_id = $member_info['PRJ_ID'];
                    $invoice_no = $member_info['INVOICE_NO'];
                    $prj_city = $member_info['CITY_ID'];
                    $prj_city_info = D("City")->get_city_info_by_id($prj_city);
                    $prj_city_py = $prj_city_info['PY'];

                    //ȫ���˿�˿�״̬������˿�
                    if($member_info['PAID_MONEY'] - $value['REFUND_MONEY'] == 0)
                    {
                        $member_update_arr['CARDSTATUS'] = 4;
                        $member_update_arr['BACK_UID'] = $value['APPLY_UID'];
                        $member_update_arr['BACKTIME'] = date('Y-m-d H:i:s');
                        $member_update_arr['PAY_TYPE'] = 0;
                    }

                    ///�˿�����Ѹ����
                    $member_update_arr['PAID_MONEY'] = array('exp', "PAID_MONEY - " .$value['REFUND_MONEY']);
                    $member_update_arr['UNPAID_MONEY'] = array('exp', "UNPAID_MONEY + " .$value['REFUND_MONEY']);
                    $update_num_member = $member_model->update_info_by_id($mid, $member_update_arr);

                    if(!$update_num_member)
                    {
                        $flag = false;
                        break;
                    }

                    //������Ŀ�����
                    $case_id = !empty($member_info['CASE_ID']) ? intval($member_info['CASE_ID']) : 0;
                    $income_info = array();
                    $income_info['CASE_ID'] = $case_id;
                    $income_info['ENTITY_ID'] = $mid;

                    //ԭʼ����ʵ����
                    $income_info['ORG_ENTITY_ID'] = $mid;
                    $income_info['PAY_ID'] = $value['PAY_ID'];

                    //ԭʼ������ϸ���
                    $income_info['ORG_PAY_ID'] = $value['PAY_ID'];
                    if(in_array($member_info['INVOICE_STATUS'], $not_open_arr))
                    {
                        $income_info['INCOME_FROM'] = 4;//���̷ǿ�Ʊ��Ա�˿�
                    }
                    else
                    {
                        $income_info['INCOME_FROM'] = 20;//���̿�Ʊ��Ա�˿�
                    }

                    $income_info['INCOME'] = - $value['REFUND_MONEY'];
                    if($invoice_no){
                        //��Ʊ˰��
                        $taxrate = get_taxrate_by_citypy($prj_city_py);
                        $income_info['OUTPUT_TAX'] = round((0-$value['REFUND_MONEY'])/(1+$taxrate) * $taxrate,2);
                    }

                    $income_info['INCOME_REMARK'] = '���̻�Ա�˿�';
                    $income_info['ADD_UID'] = $value['APPLY_UID'];
                    $income_info['OCCUR_TIME'] = $value['UPDATETIME'];
                    $result = $income_model->add_income_info($income_info);

                    if(!$result)
                    {
                        $flag = false;
                        break;
                    }


                    //POS�������� --- �ɱ��˻� (POS��)
                    $pay_info = $member_pay_model->get_payinfo_by_id($pay_id,array('PAY_TYPE','MERCHANT_NUMBER','TRADE_MONEY'));

                    if($pay_info[0]['PAY_TYPE']==1 && !in_array($member_info['INVOICE_STATUS'], $not_open_arr)){
                        //�������
                        $cost_info['CASE_ID'] = $case_id;
                        //ҵ��ʵ����
                        $cost_info['ENTITY_ID'] =  $value['MID'];
                        $cost_info['EXPEND_ID'] = $value['PAY_ID'];
                        $cost_info['ORG_ENTITY_ID'] = $value['MID'];
                        $cost_info['ORG_EXPEND_ID'] = $value['PAY_ID'];

                        // �ɱ����
                        $fee = get_pos_fee($prj_city,$value['REFUND_MONEY'],$pay_info[0]['MERCHANT_NUMBER']);
                        $cost_info['FEE'] = -$fee;
                        //�����û����
                        $cost_info['ADD_UID'] = $this->uid;
                        //����ʱ��
                        $cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());
                        //�Ƿ��ʽ�أ�0��1�ǣ�
                        $cost_info['ISFUNDPOOL'] = 0;
                        //�ɱ�����ID
                        $cost_info['ISKF'] = 1;
                        //����˰
                        $cost_info['INPUT_TAX'] = 0;
                        //�ɱ�����ID
                        //$cost_info['FEE_ID'] = $v["FEE_ID"];
                        $cost_info['EXPEND_FROM'] = 28;
                        $cost_info['FEE_REMARK'] = "��Ա��ƱPOS��������";
                        $cost_info['FEE_ID'] = 95;

                        $cost_insert_id = $project_cost_model->add_cost_info($cost_info);

                        if(!$cost_insert_id)
                        {
                            $flag = false;
                            break;
                        }
                    }

                    //�����ȫ���˿�(���뵽CRM��)
                    if($member_info['PAID_MONEY'] - $value['REFUND_MONEY'] == 0){
                        $status_arr = $member_model->get_conf_all_status_remark();

                        /***�˿�֪ͨCRM***/
                        $crm_api_arr = array();
                        $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                        $crm_api_arr['mobile'] = strip_tags($member_info['MOBILENO']);
                        $crm_api_arr['activefrom'] = 104;
                        $crm_api_arr['city'] = $prj_city_py;
                        $crm_api_arr['activename'] =  urlencode($member_info['PRJ_NAME'].
                            '�˿�'.oracle_date_format($member_info['CARDTIME'], 'Y-m-d'));
                        $crm_api_arr['importfrom'] = urlencode('��������غ�̨');
                        $crm_api_arr['tlfcard_status'] = 3;
                        $crm_api_arr['tlfcard_creattime'] = strtotime(oracle_date_format($member_info['CARDTIME'], 'Y-m-d'));
                        $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                        $crm_api_arr['tlfcard_signtime'] = strtotime(oracle_date_format($member_info['SIGNTIME'], 'Y-m-d'));
                        $crm_api_arr['tlfcard_backtime'] = time();
                        $crm_api_arr['tlf_username'] = trim($_SESSION['uinfo']['uname']);
                        $crm_api_arr['projectid'] = $member_info['PRJ_ID'];

                        if($member_info['CARDSTATUS'] == 3)
                        {
                            $house_info = M('erp_house')->field('PRO_LISTID')
                                ->where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();

                            $pro_listid = !empty($house_info['PRO_LISTID']) ?
                                intval($house_info['PRO_LISTID']) : '';

                            $crm_api_arr['floor_id'] = $pro_listid;
                        }

                        //�ύ
                        $crm_url = submit_crm_data_by_api_url($crm_api_arr);
                        $ret_log = api_log($prj_city,$crm_url,0,$this->uid,2);

                        if(!$ret_log)
                        {
                            $flag = false;
                            break;
                        }

                        //ȫ������׼����ϵͳ(���״̬)
                        $qltStatus = 6;
                        $queryRet = M('Erp_project')->field('CONTRACT')
                            ->where('ID='.$member_info['PRJ_ID'])->find();

                        $qltContract = $queryRet['CONTRACT'];

                        $qltApiUrl = QLTAPI . '###serviceName=updateStatus###status=' . $qltStatus . '###contract=' . $qltContract . '###phone=' . $member_info['MOBILENO'];
                        api_log(intval($_SESSION['uinfo']['city']),$qltApiUrl,0,intval($_SESSION['uinfo']['uid']),3);
                    }

//                    //������ڷ�Ʊ��
//                    if($invoice_no) {
//                        //��ȡ��ͬ���
//                        $contract_num = M("erp_project")
//                            ->field("CONTRACT")
//                            ->where('ID = ' . $prj_id)
//                            ->find();
//                        $contract_num = $contract_num['CONTRACT'];
//
//                        //�����ͬϵͳ
//                        $tongji_url = CONTRACT_API . 'sync_ct_invoice.php?city=' . $prj_city_py . '###contractnum=' . $contract_num . '###money=-' . $value['REFUND_MONEY'] . '###tax=0###invono=' . $invoice_no . '###type=2###date=' . date('Y-m-d') . '###note=' . urlencode('����ϵͳ�Զ�ͬ��-�˿�');
//                        $ret_log = api_log($prj_city, $tongji_url, 0, $this->uid, 1);
//
//                        if(!$ret_log)
//                        {
//                            $flag = false;
//                            break;
//                        }
//                    }
                }
            }
        }

        if($flow_status && $list_update_num && $update_num && $flag) {
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

        $auth = $this->workflow->start_authority($flowTypePY);

        if(!$auth) {
            $response['message'] = '�Բ���������Ȩ�ޣ�';
            return $response;
        }

        $flagStatus = $this->workflow->createworkflow($data);


        //�˿�MODEL
        $refund_model = D('MemberRefund');

        //�ύ�˿����뵥
        $list_update_num = $refund_model->sub_refund_list_to_apply($recordId);

        //�ύ�˿�������ϸ
        $update_num = $refund_model->sub_refund_detail_to_apply($recordId);

        if($flagStatus && $list_update_num && $update_num){
            $this->model->commit();
            $return = true;
        }
        else{
            $this->model->rollback();
        }

        return $return;

    }
}