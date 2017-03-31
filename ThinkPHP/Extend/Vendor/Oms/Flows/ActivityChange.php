<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * ���������������������
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/15
 * Time: 09:47
 */

class ActivityChange extends FlowBase {

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
            'message' => ''
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

    /**
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
                    $response['message'] = '�������������ύ�ɹ���';
                } else {
                    D()->rollback();
                    $response['status'] = false;
                    $response['message'] = '�������������ύʧ�ܣ�';
                }
            }
        }

        return $response;
    }

    /**
     * ���¶������Ŀ״̬
     * @param $activityID
     * @return bool
     */
    protected function updateProjectStatus($activityID){
        $response = false;
        if (intval($activityID)) {
            $sql = <<<FIND_PROJECT_SQL
            SELECT C.PROJECT_ID
                FROM ERP_ACTIVITIES T
            LEFT JOIN ERP_CASE C ON C.ID = T.CASE_ID
            WHERE T.ID = %d
FIND_PROJECT_SQL;

            $result = D()->query(sprintf($sql, $activityID));
            if (is_array($result) && count($result)) {
                $projectID = $result[0]['PROJECT_ID'];
                D('Project')->update_pass_status($projectID);
            }
        }

        return $response;
    }

    private function beforeFinishWorkFlow($data) {
        $response = false;
        $CID = intval($data['recordId']);  // ����汾��
        $projectID = intval($data['projectId']);  // ��ĿID
        $activityID = intval($data['activityId']); // �ID
        if ($CID && $projectID && $activityID) {
            // ���±�����е�����
            Vendor('Oms.Changerecord');
            $changeModel = new Changerecord();
            $response = $changeModel->setRecords($CID);

            // ���¶��������Ŀ����
            if ($response) {
                $project = M("Erp_project")->where("ID = {$projectID}")->find();

                if($project['ACSTATUS'] ) {
                    $activity = M("Erp_activities")->where("ID = {$activityID}")->find();
                    $response = M("Erp_project")->where("ID = {$projectID}")->setField("PROJECTNAME",$activity['TITLE']);
                }
            }

            // ����Ԥ���
            if ($response) {
                $response = D('ActiveBudget')->onActiveChangeSuccess($CID);
            }
        }

        return $response;
    }
}