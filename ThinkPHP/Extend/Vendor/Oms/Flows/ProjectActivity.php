<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * ��Ŀ�»�����������
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/7
 * Time: 13:25
 */

class ProjectActivity extends FlowBase {

    public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();
        Vendor('Oms.UserLog');
        $this->UserLog = UserLog::Init();
    }

    function nextstep($flowId) {
        return $this->workflow->nextstep($flowId);
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
        $result = $this->workflow->handleworkflow($data);
        return $result;
    }

    /**
     * ͨ��
     * @param $data
     * @return bool
     */
    function passWorkflow($data) {
        $result = $this->workflow->passWorkflow($data);
        return $result;
    }

    /**
     * ���
     * @param $data
     * @return bool
     */
    function notWorkflow($data) {
        $result = $this->workflow->notWorkflow($data);
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
            'message' => '',
            'url' => $_SERVER['HTTP_REFERER']
        );
        $auth = $this->workflow->flowPassRole($data['flowId']);

        if(!$auth) {
            $response['message'] = 'δ�����ؾ���ɫ';
            return $response;
        }

        D()->startTrans();
        $response['status'] = $this->workflow->finishworkflow($data);
        if ($response['status'] !== false) {
            $response['status'] = D('ProjectCase')->set_case_by_activitiesId($_REQUEST['recordId'], 2);
        }
        if ($response['status'] !== false) {
            D()->commit();
        } else {
            D()->rollback();
        }

        return $response;
    }

    function createworkflow($data) {
        $response = array(
            'status' => false,
            'message' => '',
            'url' => U('Case/projectlist')
        );

        // ���Ȩ��
        $auth = $this->workflow->start_authority('dulihuodong');
        if(!$auth) {
            $response['status'] = false;
            $response['message'] = '����Ȩ��';
        } else {
            $activityStatus = D('erp_activities')->where("ID = {$data['RECORDID']}")->getField('STATUS');
            if (intval($activityStatus) == 0) {
                $data['INFO'] = strip_tags($data['INFO']);
                $data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
                $data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
                $data['DEAL_USERID'] = intval($data['DEAL_USERID']);
                $data['FILES'] = $data['FILES'];
                D()->startTrans();
                $dbResult = $this->workflow->createworkflow($data);
                if($dbResult){
                    //��������ʱ��
                    $aptime = date("Y/m/d H:m:s");
                    $updated = true;
                    $updated = M()->execute(sprintf("update erp_activities set aptime = to_date('{$aptime}','yyyy/mm/dd HH24:MI:SS') where id = %d", $data['RECORDID']));
                }

                if ($dbResult !== false && $updated !== false) {
                    D()->commit();
                    $response['status'] = true;
                    $response['message'] = '��Ŀ�»�����ύ�ɹ���';
                } else {
                    D()->rollback();
                    $response['status'] = false;
                    $response['message'] = '��Ŀ�»�ύʧ�ܣ�';
                }
            } else {
                $response['status'] = false;
                $response['message'] = '�û���ύ��ˣ������ظ��ύ��';
            }
        }

        return $response;
    }
}