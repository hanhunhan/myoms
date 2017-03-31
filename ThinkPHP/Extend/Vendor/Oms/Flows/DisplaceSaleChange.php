<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * 售卖管理变更工作流
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/12/2
 */

class DisplaceSaleChange extends FlowBase {
    /**
     * 审核通过状态
     */
    const AUDIT_PASS_STATUS = 2;

    /**
     * 售卖变更前后的信息
     */
    const SALE_CHANGE_BEFORE_AFTER_SQL = <<<SALE_CHANGE_BEFORE_AFTER_SQL
        SELECT
            a.org_sale_list_id AS org_list_id,
            d1.did as displace_warehouse_id,
            d1.id AS new_detail_id,
            d1.amount AS new_amount,
            d1.money AS new_money,
            d2.id AS old_detail_id,
            d2.amount as old_amount,
            d2.money as old_money
        FROM erp_displace_applylist a
        LEFT JOIN erp_displace_applydetail d1 ON d1.list_id = a.id
        LEFT JOIN erp_displace_applydetail d2 ON d2.id = d1.org_sale_detail_id
SALE_CHANGE_BEFORE_AFTER_SQL;

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
        D()->startTrans();
        $result = $this->workflow->notWorkflow($data);
        if ($result !== false) { //否决不用管
            $result = $this->afterDenySuccess($data['recordId']);
        }

        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    private function doChangeDisplaceWarehouse($data = array(), &$warehouse = array(), &$orgListId = 0, &$msg = '') {
        if (empty($data)) {
            return false;
        }

        if($data['diff_amount']>0){ //如果申请值大于已售卖则备案失败
            $msg = '服务器内部错误0';
            return false;
        }

        $updateApplyDetailData = array();
        $updateApplyDetailData['AMOUNT'] = $data['new_amount'];
        $updateApplyDetailData['MONEY'] = $data['new_price'];
        $dbResult = D('erp_displace_applydetail')->where("ID = {$data['old_detail_id']}")->save($updateApplyDetailData);
        if ($dbResult === false) {
            $msg = '服务器内部错误1';
            return false;
        }

        //更新库存值
        $sql = "SELECT A.*,to_char(A.INBOUND_TIME,'YYYY-MM-DD hh24:mi:ss') as NEW_INBOUND_TIME FROM ERP_DISPLACE_WAREHOUSE A WHERE A.ID = " . $data['displace_warehouse_id'];
        $queryRet = D()->query($sql);
        if($queryRet===false) {
            $msg = '服务器内部错误2';
            return false;
        }

        if($data['diff_amount']<0){

            //插入一条新的数据
            $addData = $queryRet[0];
            $addData['NUM'] = - $data['diff_amount']; //数量
            $addData['INBOUND_STATUS'] = 2; //已入库
            $addData['USE_NUM'] = 0; //项目领用值为0
            //时间转换
            $addData['LIVETIME'] = oracle_date_format($addData['LIVETIME'],'Y-m-d H:i:s');
            $addData['ALARMTIME'] = oracle_date_format($addData['ALARMTIME'],'Y-m-d H:i:s');
            $addData['ADD_TIME'] = oracle_date_format($addData['ADD_TIME'],'Y-m-d H:i:s');
            $addData['INBOUND_TIME'] = $addData['NEW_INBOUND_TIME'];

            unset($addData['ID']);
            unset($addData['UPDATE_USERID']);
            unset($addData['UPDATE_TIME']);
            unset($addData['PARENTID']);

            $resLess = M("Erp_displace_warehouse")
                ->add($addData);

            if($resLess===false){
                $msg = '服务器内部错误3';
                return false;
            }

            //更新原库存值
            $sql = "update erp_displace_warehouse set num = num + {$data['diff_amount']} where id = " . $data['displace_warehouse_id'];
            $updateRet  = D()->query($sql);

            if($updateRet === false){
                $msg = '服务器内部错误4';
                return false;
            }
        }

        // 将原售卖的状态从售卖变更中状态修改为未开票状态
        $dbResult = D('erp_displace_applylist')->where("ID = {$orgListId}")->save(array(
            'STATUS' => self::AUDIT_PASS_STATUS
        ));

        return $dbResult;
    }

    /**
     * 分裂库存
     * @param array $data
     * @return bool
     */
    private function changeDisplaceWarehouse($data = array(), &$warehouse, $orgListId) {
        // 得到原库存与新库存的数量与价格信息
        if (empty($data)) {
            return false;
        }

        foreach ($data as $item) {
            // 先插入再更改
            $dbResult = $this->doChangeDisplaceWarehouse($item, $warehouse, $orgListId);
            if ($dbResult === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * 更新成本表
     */
    private function addIncomeList() {

        return true;
    }

    /**
     * 计算新旧库存差异
     * @param $data
     * @return array|void
     */
    private function calcWarehouseDiff($data) {
        if (empty($data)) {
            return;
        }

        // 用新值减去旧值
        $response = array();
        $diffAmount = intval($data['NEW_AMOUNT']) - intval($data['OLD_AMOUNT']);
        $diffTotalMoney = floatval($data['NEW_AMOUNT'] * $data['NEW_MONEY']) - floatval($data['OLD_AMOUNT'] * $data['OLD_MONEY']);

        $response['displace_warehouse_id'] = $data['DISPLACE_WAREHOUSE_ID'];
        $response['diff_amount'] = $diffAmount;
        $response['new_amount'] = $data['NEW_AMOUNT'];
        $response['new_detail_id'] = $data['NEW_DETAIL_ID'];
        $response['old_detail_id'] = $data['OLD_DETAIL_ID'];
        $response['new_price'] = $data['NEW_MONEY'];
        $response['diff_total_money'] = $diffTotalMoney;

        return $response;
    }

    /**
     * 获取变更前后的信息
     * @param $recordId
     * @param $displaceWarehouseDiff
     * @param $totalMoneyDiff
     * @return bool
     */
    private function getCompareSaleChange($recordId, &$displaceWarehouseDiff, &$totalMoneyDiff, &$orgListId = 0) {
        if (intval($recordId) <= 0) {
            return false;
        }

        try {
            $where = " WHERE a.id = {$recordId} ";
            $sql = self::SALE_CHANGE_BEFORE_AFTER_SQL . $where;

            $dbResult = D()->query($sql);
            if ($dbResult === false) {
                return false;
            }

            $orgListId = $dbResult[0]['ORG_LIST_ID'];  // 原售卖列表ID
            $displaceWarehouseDiff = array();
            foreach ($dbResult as $item) {
                $temp = $this->calcWarehouseDiff($item);
                $displaceWarehouseDiff [] = $temp;
                $totalMoneyDiff += $temp['diff_total_money'];
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 工作流否决成功
     * @param $listId
     * @param string $msg
     * @return bool
     */
    public function afterDenySuccess($listId, &$msg = '') {
        if (intval($listId) <= 0) {
            return true;
        }

        $where = " WHERE a.id = {$listId} ";
        $sql = self::SALE_CHANGE_BEFORE_AFTER_SQL . $where;
        $dbResult = D()->query($sql);
        if ($dbResult === false) {
            return false;
        }

        $orgListId = $dbResult[0]['ORG_LIST_ID'];  // 原售卖列表ID
        $result = D('erp_displace_applylist')->where("ID = {$orgListId}")->save(array(
            'STATUS' => self::AUDIT_PASS_STATUS
        ));

        return $result;
    }

    /**
     * 售卖变更成功之后的处理
     */
    private function afterSaleChangeSuccess($recordId) {
        $displaceWarehouseDiff = array();  // 新旧存储差异化
        $totalIncomeDiff = 0;  // 收益的差值

        $dbResult = $this->getCompareSaleChange($recordId, $displaceWarehouseDiff, $totalIncomeDiff, $orgListId);
        if ($dbResult === false) {
            return false;
        }

        // 先将原库存中的记录进行分裂
        $dbResult = $this->changeDisplaceWarehouse($displaceWarehouseDiff, $warehouse, $orgListId);
        if ($dbResult === false) {
            return false;
        }

        return $dbResult !== false;
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
            'url' => U('Flow/flowList', 'status=1'),
        );
        $auth = $this->workflow->flowPassRole($data['flowId']);

        if(!$auth) {
            $response['message'] = '未经过必经角色';
            return $response;
        }

        $this->model->startTrans();
        $response['status'] = $this->workflow->finishworkflow($data);
        if ($response['status']) {
            // 售卖变更成功之后，先将displace_warehouse表中的数据更改，再根据收益的差异计入成本表
            $response['status'] = $this->afterSaleChangeSuccess($data['recordId']);
        }

        if ($response['status'] !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
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
//        $auth = $this->workflow->start_authority('shoumaibiangeng');
        $auth = true;
        if(!$auth) {
            $response['status'] = false;
            $response['message'] = '暂无权限';
        } else {
            D()->startTrans();
            $dbResult = $this->workflow->createworkflow($data);
            if ($dbResult !== false) {
                D()->commit();
                $response['status'] = true;
                $response['message'] = '售卖变更已提交审核！';
            } else {
                D()->rollback();
                $response['status'] = false;
                $response['message'] = '提交审核失败！';
            }
        }

        return $response;
    }
}