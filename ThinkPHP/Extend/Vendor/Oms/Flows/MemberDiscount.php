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

class MemberDiscount extends FlowBase {

    public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();

        //���ػ�Աģ�鹫�ú����ļ�
        load("@.member_common");
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

        $this->model->startTrans();

        $recordId = intval($data['recordId']);

        $flow_status = $this->workflow->finishworkflow($_REQUEST);

        //��������ͬ����޸Ļ�Ա�����������
        $member_discount_model = D("MemberDiscount");
        $member_model = D("Member");

        $cond_where = "LIST_ID=".$recordId;

        $field_arr = array("MID","REDUCE_MONEY");
        $info = $member_discount_model->get_discount_detail_by_cond($cond_where,$field_arr);
        foreach($info as $key=>$val)
        {
            $mid[$key] = $val["MID"];
            $reduce_money[$key] = $val["REDUCE_MONEY"];
        }
        $field_arr = array("TOTAL_PRICE","PAID_MONEY","UNPAID_MONEY","REDUCE_MONEY");

        $flag = true;
        foreach($mid as $k=>$v)
        {
            $member_info = $member_model->get_info_by_id($v,$field_arr);
            $total_price = $member_info["TOTAL_PRICE"];
            $paid_money = $member_info["PAID_MONEY"];
            $unpaid_money = $member_info["UNPAID_MONEY"];

            if($paid_money == $total_price)
            {
                $unpaid_money = $unpaid_money-$reduce_money[$k];
            }
            else if($paid_money < $total_price)
            {
                $unpaid_money = $unpaid_money-$reduce_money[$k];
            }

            $update_arr["UNPAID_MONEY"] = $unpaid_money;
            $update_arr["REDUCE_MONEY"] = $reduce_money[$k];

            //�������ͨ����δ�ɽ��С�ڵ���0���Ҹû�Ա���нɷѼ�¼����ȷ�ϣ����޸ĸû�Ա�Ĳ���ȷ��״̬Ϊ��ȷ��
            if($unpaid_money <= 0)
            {
                $member_payment_info = D("Erp_member_payment")->field("ID")->where("MID = ".$v." AND STATUS = 0")->select();
                if(!$member_payment_info)
                {
                    $update_arr["FINANCIALCONFIRM"] = 3;
                }
            }
            $up_num = $member_model->update_info_by_id($v,$update_arr);

            if(!$up_num){
                $flag = false;
                break;
            }

        }


        if($flow_status && $flag) {
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