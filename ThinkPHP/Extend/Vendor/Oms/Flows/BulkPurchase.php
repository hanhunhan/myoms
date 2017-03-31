<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * 大宗采购工作流处理
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/15
 * Time: 16:28
 */

class BulkPurchase extends FlowBase {
    /**
     * 需要进行额度检查的项目类型
     * @var array
     */
    protected $needRangeCheckType = array('fx', 'ds', 'fwfsc');

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
     * 备案工作流
     * @param $data
     * @return array
     */
    function finishworkflow($data) {
        $response = array(
            'status' => false,
            'message' => ''
        );

        if (is_array($data) && count($data)) {
            $auth = $this->workflow->flowPassRole($data['flowId']);

            if (!$auth) {
                $response['message'] = '未经过必经角色';
                return $response;
            }

            $response['status'] = $this->workflow->finishworkflow($data);
        }

        return $response;
    }

    /**
     * 大宗采购工作流申请
     * @param $data
     * @return array
     */
    function createworkflow($data) {
        $response = array(
            'status' => false,
            'message' => '',
            'url' => U('Case/projectlist')
        );

        // 检查权限
        $auth = $this->workflow->start_authority('caigoushenqing');
        if(!$auth) {
            $response['status'] = false;
            $response['message'] = '暂无权限';
        } else {
            $data['INFO'] = strip_tags($data['INFO']);
            $data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
            $data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
            $data['DEAL_USERID'] = intval($data['DEAL_USERID']);
            $data['ACTIVID'] = intval($data['SCALETYPE']);
            $data['CASEID'] = 0;  // 大宗采购的CASEID为0
            D()->startTrans();
            $dbResult = $this->workflow->createworkflow($data);
            if ($dbResult !== false) {
                //提交采购申请
                $dbResult = D('PurchaseRequisition')->submit_purchase_by_id($data['RECORDID']);
            }

            if ($dbResult !== false) {
                $response['status'] = true;
                $response['message'] = '采购申请提交成功！';
                D()->commit();
            } else {
                $response['status'] = false;
                $response['message'] = '采购申请提交失败';
                D()->rollback();
            }
            $response['url'] = U('Touch/Purchase/process', 'RECORDID=' . $data['RECORDID']);
        }

        return $response;
    }

    /**
     * 获取采购申请单信息
     * @param $requireID
     * @return array
     */
    protected function getPurchaseRequireInfo($requireID) {
        $response = array();
        if ($requireID) {
            //采购申请单MODEL
            $model = D('PurchaseRequisition');
            // 所需的字段
            $fields = array(
                'DEPT_ID',
                'USER_ID',
                'CASE_ID',
                'APPLY_TIME',
                'to_char(END_TIME, \'YYYY-MM-DD HH24:MI:SS\') as END_TIME',
                'CITY_ID',
                'REASON',
                'PRJ_ID',
            );

            $response = $model->get_purchase_by_id($requireID, $fields);
        }

        return $response;
    }

    /**
     * 获取采购明细列表
     * @param $recordID
     * @return array
     */
    private function getPurchaseList($recordID) {
        $response = array();
        if (!empty($recordID)) {
            //采购明细MDOEL
            $model = D('PurchaseList');
            //根据采购申请单号获取采购明细
            $fields = array(
                'ID', 'PR_ID', 'PRICE_LIMIT',
                'NUM_LIMIT', 'FEE_ID', 'IS_FUNDPOOL','ZK_STATUS',
                'IS_KF'
            );
            $response = $model->get_purchase_list_by_prid($recordID, $fields);
        }

        return $response;
    }

    /**
     * 向成本表中插入数据
     * @param $purchaseInfo
     * @param $purchaseListInfo
     * @return bool
     */
    protected function insertCostInfo($purchaseInfo, $purchaseListInfo) {
        if (is_array($purchaseListInfo) && count($purchaseListInfo)) {
            foreach ($purchaseListInfo as $key => $value) {
                $cost_info['CASE_ID'] = $purchaseInfo['CASE_ID'];
                $cost_info['ENTITY_ID'] = $value['PR_ID'];
                $cost_info['EXPEND_ID'] = $value['ID'];
                $cost_info['ORG_ENTITY_ID'] = $value['PR_ID'];
                $cost_info['ORG_EXPEND_ID'] = $value['ID'];
                $cost_info['EXPEND_FROM'] = 1;//申请采购备案
                $cost_info['FEE'] = $value['PRICE_LIMIT'] * $value['NUM_LIMIT'];
                $cost_info['FEE_REMARK'] = '申请采购通过';
                $cost_info['ADD_UID'] = intval($_SESSION['uinfo']['uid']);
                $cost_info['OCCUR_TIME'] = date('Y-m-d H:i:s');
                $cost_info['ISKF'] = $value['IS_KF'];
                $cost_info['ISFUNDPOOL'] = $value['IS_FUNDPOOL'];
                $cost_info['FEE_ID'] = $value['FEE_ID'];

                D()->startTrans();
                $addResult = D('ProjectCost')->add_cost_info($cost_info);
                $updateResult = true;
                if ($value['FEE_ID'] == 58){
                    $curl_result = $this->_zk_api($purchaseInfo, $value);
                    if ($curl_result){
                        $curl_result = json_decode($curl_result);
                        if ($curl_result->code == 200){
                            $updateResult = D('PurchaseList')->where('ID='.$value['ID'])->save(array('ZK_STATUS'=>1));
                        }
                    }
                }

                if ($addResult !== false && $updateResult !== false) {
                    D()->commit();
                } else {
                    D()->rollback();
                    return false;
                }
            }
            return true;
        }

        return true;
    }

    /**
     * 检查是否超过预算
     * @param $purchaseInfo
     * @param $purchaseListInfo
     * @return bool
     */
    protected function checkIsOverTop($purchaseInfo, $purchaseListInfo) {
        $cost_total = 0;
        foreach ($purchaseListInfo as $key => $value) {
            $cost_total += $value['PRICE_LIMIT'] * $value['NUM_LIMIT'];
        }

        //查询电商、分销业务的CASE_ID
        $search_field = array('ID', 'PARENTID');
        $caseInfo = D('ProjectCase')->get_info_by_id(intval($purchaseInfo['CASE_ID']), $search_field);

        $caseID = intval($caseInfo[0]['PARENTID']) > 0 ? intval($caseInfo[0]['PARENTID']) : intval($purchaseInfo[0]['CASE_ID']);
        $isOverTop = is_overtop_payout_limit($caseID, $cost_total);

        return $isOverTop;
    }

    /**
     * 小蜜蜂采购众客api
     * @param $purchase_info
     * @param $purchase_list_info
     * @return bool|mixed
     */
    protected function _zk_api($purchase_info,$purchase_list_info){
        if ( isset($purchase_list_info['ZK_STATUS']) && $purchase_list_info['ZK_STATUS']==1){
            return false;
        }
        //获取城市简拼
        $model_city = D('City');
        $city = $model_city->get_city_info_by_id($purchase_info['CITY_ID']);
        $citypy = strtolower($city["PY"]);
        //获取项目房产信息
        $house =  D('Erp_house')->where("PROJECT_ID =".$purchase_info['PRJ_ID'])->find();
        //获取项目信息
        $prgect= D('Project')->find($purchase_info['PRJ_ID']);

        //数据整合
        $param = array(
            'prj_id' => $purchase_info['PRJ_ID'],
            'prj_name' => mb_convert_encoding($prgect['PROJECTNAME'], 'UTF-8','GBK'),
            'p_id' => $purchase_list_info['ID'],
            'p_name' => mb_convert_encoding($purchase_info['REASON'], 'UTF-8','GBK'),
            'price_limit' => $purchase_list_info['PRICE_LIMIT'],
            'num_limit' => $purchase_list_info['NUM_LIMIT'],
            'city' => $citypy,
            'pro_listid' => $house['PRO_LISTID'],
            'rel_newhouseid' => $house['PRO_LISTID'],
            'rel_newhouse' => mb_convert_encoding($house['REL_PROPERTY'], 'UTF-8','GBK'),
            'end_time' => strtotime($purchase_info['END_TIME']),
            'key' => md5(md5($purchase_list_info['ID'].$citypy)."BEE"),
        );
        //写入众客系统
        $api = ZKAPI1;
        return curlPost($api, $param);
    }
}