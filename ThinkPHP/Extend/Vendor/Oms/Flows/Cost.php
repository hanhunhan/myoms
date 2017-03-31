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

class Cost extends FlowBase {

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

        $flow_status = $this->workflow->finishworkflow($data);

        if($flow_status) {
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