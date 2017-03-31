<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * 独立活动立项工作流处理
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/7
 * Time: 13:24
 */

class Activity extends FlowBase {

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
     * 转交
     * @param $data
     * @return bool
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
     * 通过
     * @param $data
     * @return bool
     */
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

    /**
     * 否决
     * @param $data
     * @return bool
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

        $this->model->startTrans();
        $response['status'] = $this->workflow->finishworkflow($data);
        $updateSuccess = false;
        if ($response['status']) {
            $updateSuccess = $this->updateProjectStatus($data['recordId']);  // 更新项目状态
            if ($updateSuccess !== false) {
                // 如果有合同号，则根据合同号拉取项目信息
                $updateSuccess = $this->addContractData($data['ACTIVID']);
            }
        }

        if ($response['status'] !== false && $updateSuccess !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $response;
    }

    /**
     * 将合同系统的信息导入到本系统数据库
     * @param $activityId int 活动ID
     * @return bool|mixed bool|int 数据操作结果
     */
    private function addContractData($activityId) {
        $response = true;
        if (intval($activityId)) {
            $sql = <<<QUERY_ACTIVITY_SQL
                SELECT p.city_id,
                       t.contract_no,
                       t.case_id,
                       p.id AS project_id,
                       p.city_id
                FROM erp_activities t
                LEFT JOIN erp_case c ON c.id = t.case_id
                LEFT JOIN erp_project p ON p.id = c.project_id
                WHERE t.id = {$activityId}
QUERY_ACTIVITY_SQL;

            $dbAllResult = D()->query($sql);
            if (notEmptyArray($dbAllResult)) {
                $projectId = $dbAllResult[0]['PROJECT_ID'];
                $contractNo = trim($dbAllResult[0]['CONTRACT_NO']);
                $cityId = $dbAllResult[0]['CITY_ID'];
                if (empty($contractNo)) {
                    return true;
                }

                // 如果项目Id为空，说明项目是无效的
                if (intval($projectId) <= 0) {
                    return false;
                }
                // 判断合同是否已经存在系统
//                $isExistContract = D('Contract')->isExistContract($contractNo, 4, $cityId);
//                if ($isExistContract) {
//                    return true;
//                }

                $cityPY = getCityPY($dbAllResult[0]['CITY_ID']);
                load("@.contract_common");
                $apiContractData = getContractData($cityPY, $contractNo);
                if (notEmptyArray($apiContractData)) {
                    $insertData['CASE_ID'] = $dbAllResult[0]['CASE_ID'];
                    $insertData['CONTRACT_NO'] = $contractNo;
                    $insertData['CITY_PY'] = $cityPY;
                    $insertData['CITY_ID'] = $dbAllResult[0]['CITY_ID'];
                    $insertData['COMPANY'] =  $apiContractData['contunit'];
                    //开始时间
                    $insertData['START_TIME'] = date("Y-m-d",$apiContractData['contbegintime']);
                    //结束时间
                    $insertData['END_TIME'] = date("Y-m-d",$apiContractData['contendtime']);
                    //合同状态
                    $insertData['STATUS'] = $apiContractData['step'];
                    //合同金额
                    $insertData['MONEY'] = $apiContractData['contmoney'];
                    //合同类型
                    $insertData['CONTRACT_TYPE'] = $apiContractData['type'];
                    //合同签约人
                    $insertData['SIGN_USER'] = $apiContractData['addman'];

                    //财务确认时间
                    if($apiContractData['confirmtime']) {
                        $insertData['CONF_TIME'] = date("Y-m-d H:i:s",$apiContractData['confirmtime']);
                    }

                    if ($apiContractData['pubdate']) {
                        $insertData['PUB_TIME'] = trim($apiContractData['pubdate']);
                    }

                    // 插入数据
                    $response = D('Contract')->add($insertData);
                    $contractId = $response;

                    // 将合同号保存至项目中
                    if ($response !== false) {
                        $response = D('Project')->where("ID = {$projectId}")->save(array(
                            "CONTRACT" => $contractNo
                        ));
                    }

                    // 同步合同信息
                    if ($response !== false) {
                        // 同步开票信息
                        $response = D('Contract')->syncInvoiceData($contractNo, $contractId, $cityPY);
                    }

                    if ($response !== false) {
                        // 同步回款信息
                        $response = D('Contract')->syncRefundData($contractNo, $contractId, $cityPY);
                    }
                }
            }
        }

        return $response;
    }

    /**
     * 提交独立活动立项申请
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
            if($data['CASEID']) {  // 这里的CASEID代表的是活动所在项目ID
                $project_model = D('Project');
                $pstatus = $project_model->get_project_status($data['CASEID']);
                if($pstatus == 2) {  // 当前项目处于“已选业务状态”
                    $data['RECORDID'] = $data['recordId'];
                    $data['INFO'] = strip_tags($data['INFO']);
                    $data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
                    $data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
                    $data['DEAL_USERID'] = intval($data['DEAL_USERID']);
                    D()->startTrans();
                    $dbResult = $this->workflow->createworkflow($data);
                    if ($dbResult !== false) {
                        // 设置项目状态为审核中
                        $dbResult = $project_model->update_check_status($data['CASEID']);
                    }

                    if ($dbResult !== false) {
                        D()->commit();
                        $response['status'] = true;
                        $response['message'] = '独立活动已提交审核！';
                    } else {
                        D()->rollback();
                        $response['status'] = false;
                        $response['message'] = '提交审核失败！';
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = '活动已提交审核，请不要重复提交！';
                }
            } else {
                $response['status'] = false;
                $response['message'] = '请选择项目！';
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
                $response = D('Project')->update_pass_status($projectID);
            }
        }

        return $response;
    }
}