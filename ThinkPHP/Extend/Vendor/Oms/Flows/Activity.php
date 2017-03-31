<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * ����������������
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
     * ת��
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
     * ͨ��
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
     * ���
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

        $this->model->startTrans();
        $response['status'] = $this->workflow->finishworkflow($data);
        $updateSuccess = false;
        if ($response['status']) {
            $updateSuccess = $this->updateProjectStatus($data['recordId']);  // ������Ŀ״̬
            if ($updateSuccess !== false) {
                // ����к�ͬ�ţ�����ݺ�ͬ����ȡ��Ŀ��Ϣ
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
     * ����ͬϵͳ����Ϣ���뵽��ϵͳ���ݿ�
     * @param $activityId int �ID
     * @return bool|mixed bool|int ���ݲ������
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

                // �����ĿIdΪ�գ�˵����Ŀ����Ч��
                if (intval($projectId) <= 0) {
                    return false;
                }
                // �жϺ�ͬ�Ƿ��Ѿ�����ϵͳ
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
                    //��ʼʱ��
                    $insertData['START_TIME'] = date("Y-m-d",$apiContractData['contbegintime']);
                    //����ʱ��
                    $insertData['END_TIME'] = date("Y-m-d",$apiContractData['contendtime']);
                    //��ͬ״̬
                    $insertData['STATUS'] = $apiContractData['step'];
                    //��ͬ���
                    $insertData['MONEY'] = $apiContractData['contmoney'];
                    //��ͬ����
                    $insertData['CONTRACT_TYPE'] = $apiContractData['type'];
                    //��ͬǩԼ��
                    $insertData['SIGN_USER'] = $apiContractData['addman'];

                    //����ȷ��ʱ��
                    if($apiContractData['confirmtime']) {
                        $insertData['CONF_TIME'] = date("Y-m-d H:i:s",$apiContractData['confirmtime']);
                    }

                    if ($apiContractData['pubdate']) {
                        $insertData['PUB_TIME'] = trim($apiContractData['pubdate']);
                    }

                    // ��������
                    $response = D('Contract')->add($insertData);
                    $contractId = $response;

                    // ����ͬ�ű�������Ŀ��
                    if ($response !== false) {
                        $response = D('Project')->where("ID = {$projectId}")->save(array(
                            "CONTRACT" => $contractNo
                        ));
                    }

                    // ͬ����ͬ��Ϣ
                    if ($response !== false) {
                        // ͬ����Ʊ��Ϣ
                        $response = D('Contract')->syncInvoiceData($contractNo, $contractId, $cityPY);
                    }

                    if ($response !== false) {
                        // ͬ���ؿ���Ϣ
                        $response = D('Contract')->syncRefundData($contractNo, $contractId, $cityPY);
                    }
                }
            }
        }

        return $response;
    }

    /**
     * �ύ�������������
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
            if($data['CASEID']) {  // �����CASEID������ǻ������ĿID
                $project_model = D('Project');
                $pstatus = $project_model->get_project_status($data['CASEID']);
                if($pstatus == 2) {  // ��ǰ��Ŀ���ڡ���ѡҵ��״̬��
                    $data['RECORDID'] = $data['recordId'];
                    $data['INFO'] = strip_tags($data['INFO']);
                    $data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
                    $data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
                    $data['DEAL_USERID'] = intval($data['DEAL_USERID']);
                    D()->startTrans();
                    $dbResult = $this->workflow->createworkflow($data);
                    if ($dbResult !== false) {
                        // ������Ŀ״̬Ϊ�����
                        $dbResult = $project_model->update_check_status($data['CASEID']);
                    }

                    if ($dbResult !== false) {
                        D()->commit();
                        $response['status'] = true;
                        $response['message'] = '��������ύ��ˣ�';
                    } else {
                        D()->rollback();
                        $response['status'] = false;
                        $response['message'] = '�ύ���ʧ�ܣ�';
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = '����ύ��ˣ��벻Ҫ�ظ��ύ��';
                }
            } else {
                $response['status'] = false;
                $response['message'] = '��ѡ����Ŀ��';
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
                $response = D('Project')->update_pass_status($projectID);
            }
        }

        return $response;
    }
}