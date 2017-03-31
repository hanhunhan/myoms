<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * ��Ŀ�»����������������
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/15
 * Time: 09:48
 */

class ProjectActivityChange extends FlowBase {
    /**
     * ����������������ģ��
     * @var null
     */
    protected $changeModel = null;

    public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();
        Vendor('Oms.UserLog');
        $this->UserLog = UserLog::Init();

        Vendor('Oms.Changerecord');
        $this->changeModel = new Changerecord();
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

        if ($this->beforeFinishWorkFlow($data)) {  // ��Ӧ���ݸ��³ɹ�
            $response['status'] = $this->workflow->finishworkflow($data);
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
            $project_model = D('Project');
            $pstatus = $project_model->get_Change_Flow_Status($data['RECORDID']);

            if($pstatus == '1') {
                $response['status'] = false;
                $response['message'] = '�����ظ��ύ��';
            } else {
                D()->startTrans();
                $dbResult = $this->workflow->createworkflow($data);
                if($dbResult !== false){
                    D()->commit();
                    $response['status'] = true;
                    $response['message'] = '��Ŀ�»��������ύ�ɹ���';
                } else {
                    D()->rollback();
                    $response['status'] = false;
                    $response['message'] = '��Ŀ�»��������ύʧ�ܣ�';
                }
            }
        }

        return $response;
    }

    private function beforeFinishWorkFlow($data) {
        $response = false;
        $CID = intval($data['recordId']);  // ����汾��
        if ($CID) {
            // ���±�����е�����
            $response = $this->changeModel->setRecords($CID);

            // ����Ԥ���
            if ($response) {
                $response = D('ActiveBudget')->onActiveChangeSuccess($CID);
            }
        }

        return $response;
    }
}