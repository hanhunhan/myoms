<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * ���������
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/16
 * Time: 13:02
 */

class Loan extends FlowBase {
    public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();
    }

    function nextstep($flowId) {
        $this->model->startTrans();
        $result = $this->workflow->nextstep($flowId);
        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    function createHtml($flowId) {
        // TODO: Implement createHtml() method.
    }

    /**
     * ת��
     * @param $data
     * @return bool
     * @throws Exception
     */
    function handleworkflow($data) {
        $this->model->startTrans();
        $result = $this->workflow->handleworkflow($data);
        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    /**
     * ͨ��
     * @param $data
     * @return bool
     * @throws Exception
     */
    function passWorkflow($data) {
        $result = $this->workflow->passWorkflow($data);
        return $result;
    }

    /**
     * ���
     * @param $data
     * @return bool
     * @throws Exception
     */
    function notWorkflow($data) {
        $this->model->startTrans();
        $result = $this->workflow->notWorkflow($data);
        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    /**
     * ����
     * @param $data
     * @return array
     * @throws Exception
     */
    function finishworkflow($data) {
        $response = array(
            'status' => false,
            'message' => ''
        );
        $auth = $this->workflow->flowPassRole($data['flowId']);

        if(!$auth) {
            $response['message'] = 'δ�����ؾ���ɫ';
            return $response;
        }

        $response['status'] = $this->workflow->finishworkflow($data);
        if ($response['status'] !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $response;
    }

    function createworkflow($data) {
        $response = array(
            'status' => false,
            'message' => '',
            'url' => $_SERVER['HTTP_REFERER']
        );

        // ���Ȩ��
        $auth = $this->workflow->start_authority($data['type']);
        if(!$auth) {
            $response['status'] = false;
            $response['message'] = '����Ȩ��';
        } else {
            $data['INFO'] = strip_tags($data['INFO']);
            $data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
            $data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
            $data['DEAL_USERID'] = intval($data['DEAL_USERID']);

            D()->startTrans();
            $dbResult = $this->workflow->createworkflow($data);
            if ($dbResult !== false) {
                $response['status'] = true;
                $response['message'] = '������빤�����ύ�ɹ���';
                D()->commit();
            } else {
                $response['status'] = false;
                $response['message'] = '������빤�����ύʧ�ܣ�';
                D()->rollback();
            }
        }

        return $response;
    }
}