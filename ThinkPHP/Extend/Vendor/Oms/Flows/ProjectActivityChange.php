<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * 项目下活动立项变更工作流处理
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/15
 * Time: 09:48
 */

class ProjectActivityChange extends FlowBase {
    /**
     * 处理立项变更的数据模型
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
            'message' => '',
            'url' => $_SERVER['HTTP_REFERER']
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

    function createworkflow($data) {
        $response = array(
            'status' => false,
            'message' => '',
            'url' => U('Case/projectlist')
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
                    $response['message'] = '项目下活动变更申请提交成功！';
                } else {
                    D()->rollback();
                    $response['status'] = false;
                    $response['message'] = '项目下活动变更申请提交失败！';
                }
            }
        }

        return $response;
    }

    private function beforeFinishWorkFlow($data) {
        $response = false;
        $CID = intval($data['recordId']);  // 变更版本号
        if ($CID) {
            // 更新变更表中的数据
            $response = $this->changeModel->setRecords($CID);

            // 更新预算表
            if ($response) {
                $response = D('ActiveBudget')->onActiveChangeSuccess($CID);
            }
        }

        return $response;
    }
}