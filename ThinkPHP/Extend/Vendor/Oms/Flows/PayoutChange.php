<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * ���ʱ�������
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/16
 * Time: 9:20
 */

class PayoutChange extends FlowBase {
     public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();
    }

    function nextstep($flowId) {
        $this->model->startTrans();
        $result = $this->workflow->nextstep($flowId);
        if ($result) {
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
     * ������ĿԤ��
     * @param $data
     * @return bool
     */
    protected function updateProjectBudget($data) {
        $response = true;
        if (!empty($data)) {
            D()->startTrans();
            // ������Ŀ���ʱ���
            $result = D("Erp_prjbudget")->where(sprintf('CASE_ID = %d', $data['CASE_ID']))->save(array(
                'PAYOUT' => $data['PAYOUT']
            ));
            if ($result !== false) {
                D()->commit();
                $response = true;
            } else {
                D()->rollback();
                $response = false;
            }
        }

        return $response;
    }

    function handleworkflow($data) {
        $this->model->startTrans();
        $result = $this->workflow->handleworkflow($data);
        if ($result !== false) {
            $result = $this->updateProjectBudget($data);
            if ($result !== false) {
                $this->model->commit();
            }
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    function passWorkflow($data) {
        $this->model->startTrans();
        $result = $this->workflow->passWorkflow($data);
        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

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

        $this->model->startTrans();
        $response['status'] = $this->workflow->finishworkflow($data);
        $updateSuccess = false;
        if ($response['status'] !== false) {
            $updateSuccess = $this->updateProjectBudget($data);
        }

        if ($response['status'] !== false && $updateSuccess !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $response;
    }

    /**
     * �������ʱ�������
     * @param $data
     * @return array
     */
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
                D()->commit();
                $response['status'] = true;
                $response['message'] = '���ʱ����������ύ�ɹ�!';
            } else {
                D()->rollback();
                $response['status'] = false;
                $response['message'] = '���ʱ����������ύʧ��!';
            }
        }

        return $response;
    }
}