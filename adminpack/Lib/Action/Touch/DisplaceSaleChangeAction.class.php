<?php
/**
 * 售卖变更工作流
 * Created by PhpStorm.
 * User: xuke
 */

class DisplaceSaleChangeAction extends ExtendAction {

    /**
     * 售卖明细列表SQL
     */
    const SALE_DETAIL_LIST_SQL = <<<SALE_MANAGE_DETAIL_SQL
        SELECT
            d.id,
            d.list_id,
            d.amount,
            RTRIM(to_char(d.money,'fm99999999990.99'),'.') AS money,
            d2.amount as old_amount,
            RTRIM(to_char(d2.money,'fm99999999990.99'),'.') AS old_money,
            w.id AS did,
            w.brand,
            w.model,
            w.product_name,
            w.source,
            w.inbound_status as status,
            w.alarmtime,
            w.livetime,
            RTRIM(to_char(w.price,'fm99999999990.99'),'.') AS WAREHOUSE_PRICE,
            p.contract AS contract_no,
            p.projectname AS project_name,
            to_char(w.changetime,'yyyy-MM-dd ') DAMAGETIME
        FROM erp_displace_applydetail d
        LEFT JOIN ERP_DISPLACE_WAREHOUSE w ON w.id = d.did
        LEFT JOIN erp_case c ON w.case_id = c.id
        LEFT JOIN erp_project p ON p.id = c.project_id
        LEFT JOIN erp_displace_applydetail d2 ON d2.id = d.org_sale_detail_id
SALE_MANAGE_DETAIL_SQL;

    /**
     * 售卖变更申请单
     */
    const SALE_CHANGE_SQL = <<<SALE_CHANGE_SQL
        SELECT
            l.id,
            l.buyer,
            l.apply_reason,
            to_char(l.APPLY_TIME,'YYYY-MM-DD hh24:mi:ss') as apply_time,
            l.apply_user_id,
            u.name as username
        FROM erp_displace_applylist l
        LEFT JOIN erp_users u ON u.id = l.apply_user_id
SALE_CHANGE_SQL;

    public function __construct() {
        parent::__construct();

        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),
            'sale_change_master' => array(
                'name' => 'sale_change_master',
                'text' => '变更申请单'
            ),
            'sale_change_detail' => array(
                'name' => 'sale_change_detail',
                'text' => '变更明细'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        $this->init();
    }

    /**
     * 初始化工作流
     */
    private function init() {
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('DisplaceSaleChange');
        $this->assign('flowType', 'shoumaibiangeng');
        $this->assign('flowTypeText', '售卖变更');
        $this->assign('title', '售卖变更');
        $this->assignWorkFlows($this->flowId);
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // 控制按钮显示
        $this->assign('menu', $this->menu);  // 菜单
        if (empty($this->CASEID)) {
            $this->CASEID = $_REQUEST['CASEID'];
        }

        if (empty($this->recordId)) {
            $this->recordId = $_REQUEST['RECORDID'];
        }
        $this->assign('CASEID', $this->CASEID);
        $this->assign('recordId', $this->recordId);
    }

    /**
     * 展示流程信息
     */
    public function process() {
        $request = $_GET;
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);  // 先修改目前的状态
        }

        $saleChangeId = $request['sale_change_id'];
        if (!empty($saleChangeId)) {
            $this->recordId = $saleChangeId;
        }
        $saleChange = $this->getSaleChange($this->recordId);
        $saleChange['info'] = $this->mapSaleChangeInfo($saleChange['info']);
        $this->assign('sale_change', $saleChange);
        $this->assign('project_name', '关于资源置换售卖变更的审核');
        $this->display('process');
    }

    private function getSaleChange($saleChangeId) {
        if (intval($saleChangeId) <= 0) {
            return false;
        }

        $where = " WHERE l.id = {$saleChangeId} ";
        $sql = self::SALE_CHANGE_SQL . $where;
        $dbResult = D()->query($sql);
        if ($dbResult === false) {
            return false;
        }
        $saleChange = $dbResult[0];

        $where = " WHERE d.list_id = {$saleChangeId} ";
        $sql = self::SALE_DETAIL_LIST_SQL . $where;
        $dbResult = D()->query($sql);
        $newTotalMoney = 0;
        $oldTotalMoney = 0;
        if (notEmptyArray($dbResult)) {
            $response['items'] = $this->getTotalMoney($dbResult, $newTotalMoney, $oldTotalMoney);
            $saleChange['NEW_TOTAL_MONEY'] = $newTotalMoney;
            $saleChange['OLD_TOTAL_MONEY'] = $oldTotalMoney;
            $response['info'] = $saleChange;
        }

        return $response;
    }

    private function getTotalMoney($input = array(), &$newTotalMoney = 0, &$oldTotalMoney = 0) {
        if (empty($input)) {
            return false;
        }

        foreach ($input as $item) {
            $newTotalMoney += floatval($item['AMOUNT'] * $item['MONEY']);
            $oldTotalMoney += floatval($item['OLD_AMOUNT'] * $item['OLD_MONEY']);
        }

        return $input;
    }

    /**
     * 审批工作流
     */
    public function opinionFlow() {
        $_REQUEST = u2g($_REQUEST);
        $response = array(
            'status'=>false,
            'message'=>'',
            'data'=>null,
            'url'=>U('Flow/flowList','status=1'),
        );

        //权限判断
        if($this->flowId) {
            if (!$this->myTurn) {
                $response['message'] = g2u('对不起，该工作流您没有权限处理');
                die(@json_encode($response));
            }
        }

        //数据验证
        $error_str = '';
        if($_REQUEST['flowNext'] && !$_REQUEST['DEAL_USERID']){
            $error_str .= "亲，请选择下一步转交人！\n";
        }

        if(!trim($_REQUEST['DEAL_INFO'])){
            $error_str .= "亲，请填写审批意见！\n";
        }

        if($error_str){
            $response['message'] = g2u($error_str);
            die(@json_encode($response));
        }

        $result = $this->workFlow->doit($_REQUEST);
        if (is_array($result)) {
            $response = $result;
        } else {
            if($result)
                $response['status'] = 1;
            else
                $response['status'] = 0;
        }

        echo json_encode(g2u($response));
    }

    private function mapSaleChangeInfo($data = array()) {
        if (empty($data)) {
            return false;
        }

        $response = array(
            'ID' => array(
                'alias' => '编号',
                'info' => $data['ID']
            ),
            'BUYER' => array(
                'alias' => '买家',
                'info' => $data['BUYER']
            ),
            'APPLY_REASON' => array(
                'alias' => '变更说明',
                'info' => $data['APPLY_REASON']
            ),
            'OLD_TOTAL_MONEY' => array(
                'alias' => '变更前总价',
                'info' => $data['OLD_TOTAL_MONEY']
            ),
            'NEW_TOTAL_MONEY' => array(
                'alias' => '变更后总价',
                'info' => $data['NEW_TOTAL_MONEY']
            ),
            'USERNAME' => array(
                'alias' => '申请人',
                'info' => $data['USERNAME']
            ),
            'APPLY_TIME' => array(
                'alias' => '申请时间',
                'info' => $data['APPLY_TIME']
            )
        );

        return $response;
    }
}