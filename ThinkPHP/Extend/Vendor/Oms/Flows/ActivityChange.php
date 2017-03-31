<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * 独立活动立项变更工作流处理
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
     * 转交
     * @param $data
     * @return bool
     */
    function handleworkflow($data) {
        $result = $this->workflow->handleworkflow($data);
        return $result;
    }

    /**
     * 通过
     * @param $data
     * @return bool
     */
    function passWorkflow($data) {
        $result = $this->workflow->passWorkflow($data);
        return $result;
    }

    /**
     * 否决
     * @param $data
     * @return bool
     */
    function notWorkflow($data) {
        $result = $this->workflow->notWorkflow($data);
        return $result;
    }

    /**
     * 备案
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
            $response['message'] = '未经过必经角色';
            return $response;
        }

        if ($this->beforeFinishWorkFlow($data)) {  // 对应数据更新成功
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

        // 检查权限
        $auth = $this->workflow->start_authority('dulihuodong');
        if(!$auth) {
            $response['status'] = false;
            $response['message'] = '暂无权限';
        } else {
            $project_model = D('Project');
            $pstatus = $project_model->get_Change_Flow_Status($data['RECORDID']);

            if($pstatus == '1') {
                $response['status'] = false;
                $response['message'] = '请勿重复提交！';
            } else {
                D()->startTrans();
                $dbResult = $this->workflow->createworkflow($data);
                if($dbResult !== false){
                    D()->commit();
                    $response['status'] = true;
                    $response['message'] = '独立活动变更申请提交成功！';
                } else {
                    D()->rollback();
                    $response['status'] = false;
                    $response['message'] = '独立活动变更申请提交失败！';
                }
            }
        }

        return $response;
    }

    /**
     * 更新独立活动项目状态
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
        $CID = intval($data['recordId']);  // 变更版本号
        $projectID = intval($data['projectId']);  // 项目ID
        $activityID = intval($data['activityId']); // 活动ID
        if ($CID && $projectID && $activityID) {
            // 更新变更表中的数据
            Vendor('Oms.Changerecord');
            $changeModel = new Changerecord();
            $response = $changeModel->setRecords($CID);

            // 更新独立活动则项目名称
            if ($response) {
                $project = M("Erp_project")->where("ID = {$projectID}")->find();

                if($project['ACSTATUS'] ) {
                    $activity = M("Erp_activities")->where("ID = {$activityID}")->find();
                    $response = M("Erp_project")->where("ID = {$projectID}")->setField("PROJECTNAME",$activity['TITLE']);
                }
            }

            // 更新预算表
            if ($response) {
                $response = D('ActiveBudget')->onActiveChangeSuccess($CID);
            }
        }

        return $response;
    }
}