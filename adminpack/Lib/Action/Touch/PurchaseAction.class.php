<?php

/**
 * 采购工作流控制器
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/8
 * Time: 10:25
 */
class PurchaseAction extends ExtendAction {
    /**
     * 采购需求查询语句
     */
    const PURCHASE_REQUIRE_SQL = <<<SQL
        SELECT A.ID,
               A.PRJ_ID,
               A.USER_ID,
               A.REASON,
               A.TYPE,
               A.CASE_ID,
               P.PROJECTNAME,
               P.CONTRACT AS CONTRACT_NUM,
               to_char(A.APPLY_TIME,'YYYY-MM-DD hh24:mi:ss') AS APPLY_TIME,
               to_char(A.END_TIME,'YYYY-MM-DD hh24:mi:ss') AS END_TIME,
               A.STATUS,
               U.NAME AS USER_NAME,
               C.SCALETYPE
        FROM ERP_PURCHASE_REQUISITION A
        LEFT JOIN ERP_CASE C ON C.ID = A.CASE_ID
        LEFT JOIN ERP_PROJECT P ON P.ID = A.PRJ_ID
        LEFT JOIN ERP_USERS U ON U.ID = A.USER_ID
        WHERE A.ID = %d
SQL;

    /**
     * 硬广采购需求查询语句
     */
    const YG_PURCHASE_REQUIRE_SQL = <<<SQL
        SELECT A.ID,
               A.PRJ_ID,
               A.USER_ID,
               A.REASON,
               A.TYPE,
               A.CASE_ID,
               P.PROJECTNAME,
               B.CONTRACT_NO AS CONTRACT_NUM
               to_char(A.APPLY_TIME,'YYYY-MM-DD hh24:mi:ss') AS APPLY_TIME,
               to_char(A.END_TIME,'YYYY-MM-DD hh24:mi:ss') AS END_TIME,
               A.STATUS,
               U.NAME AS USER_NAME,
               C.SCALETYPE
        FROM ERP_PURCHASE_REQUISITION A
        LEFT JOIN ERP_CASE C ON C.ID = A.CASE_ID
        LEFT JOIN ERP_INCOME_CONTRACT B ON A.CASE_ID = B.CASE_ID
        LEFT JOIN ERP_PROJECT P ON P.ID = A.PRJ_ID
        LEFT JOIN ERP_USERS U ON U.ID = A.USER_ID
        WHERE A.ID = %D
SQL;

    /**
     * 采购明细查询语句
     */
    const PURCHASE_LIST_SQL = <<<PURCHASE_LIST_SQL
        SELECT A.ID,
               A.PR_ID,
               A.BRAND,
               A.MODEL,
               A.PRODUCT_NAME,
               A.P_ID,
               U1.NAME AS P_NAME,
               A.APPLY_USER_ID,
               U2.NAME AS APPLY_USER_NAME,
               A.USE_NUM,
               A.USE_TOATL_PRICE,
               A.PRICE_LIMIT,
               A.PRICE,
               A.NUM_LIMIT,
               A.NUM,
               A.PURCHASE_COST,
               A.TOTAL_COST,
               A.FEE_ID,
               F.NAME AS FEE_NAME,
               A.STOCK_NUM,
               A.BACK_STOCK_STATUS,
               A.IS_FUNDPOOL,
               A.IS_KF,
               A.STATUS
        FROM
          (SELECT L.*,
                  R.END_TIME,
                  (PRICE * NUM) AS PURCHASE_COST,
                  ((PRICE * NUM) + USE_TOATL_PRICE) AS TOTAL_COST
           FROM ERP_PURCHASE_LIST L
           LEFT JOIN ERP_PURCHASE_REQUISITION R ON L.PR_ID = R.ID
           WHERE L.FEE_ID!=15) A
        LEFT JOIN ERP_USERS U1 ON U1.ID = A.P_ID
        LEFT JOIN ERP_USERS U2 ON U2.ID = A.APPLY_USER_ID
        LEFT JOIN ERP_FEE F ON F.ID = A.FEE_ID
        WHERE A.PR_ID = %d
        ORDER BY A.ID DESC
PURCHASE_LIST_SQL;

    /**
     * 采购需求状态描述
     * @var array
     */
    protected $requirementDesc = array(
        0 => '未提交',
        1 => '审核中',
        2 => '审核通过',
        3 => '审核未通过',
        4 => '采购完成'
    );

    public function __construct() {
        parent::__construct();

        // 初始化菜单
        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),
            'purchase_detail' => array(
                'name' => 'purchase-detail',
                'text' => '采购详情'
            ),
            'purchase_list' => array(
                'name' => 'purchase-list',
                'text' => '采购明细'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );
    }

    /**
     * 初始化工作流
     */
    private function initWorkFlow($req) {
        Vendor('Oms.Flows.Flow');
        if (intval($req['TYPE']) == 2) {  // 大宗采购申请
            $this->workFlow = new Flow('BulkPurchase');
            $this->assign('flowType', 'bulkPurchase');
            $this->assign('flowTypeText', '大宗采购申请');
        } else {
            $this->workFlow = new Flow('Purchase');  // 项目下采购申请
            $this->assign('flowType', 'caigoushenqing');
            $this->assign('flowTypeText', '采购申请');
        }


    }

    /**
     * 处理工作流
     */
    public function process() {
        if (empty($this->flowId)) {
            $this->recordId = $_REQUEST['RECORDID'];
        }
        $purchaseInfo = $this->getPurchaseInfo($this->recordId);
        if (intval($this->flowId) > 0 && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);  // 当前办理人已经审阅工作流，修改状态
        }
        $this->assignPurchaseInfo($purchaseInfo);  // 将采购信息赋给视图
        $this->assignWorkFlows($this->flowId);
        $this->assign('title', '采购申请');
        $this->assign('menu', $this->menu);  // 菜单
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // 控制按钮显示
        $this->assign('projectName', '关于采购申请的审批'); // 工作流标题
        $this->assign('CASEID', $purchaseInfo['desc']['CASE_ID']);  // 案例编号
        $this->assign('SCALETYPE', $purchaseInfo['desc']['SCALETYPE']);  // 业务类型
        $this->assign('recordId', $this->recordId); // can't comment
        $this->display('index');
    }

    public function detail() {
        $this->display('detail');
    }

    /**
     * 翻转采购数据
     * @param $data
     * @return array
     */
    protected function verticalPurchaseList($data) {
        $purchaseNames = array(
            'BRAND' => '品牌',
            'MODEL' => '采购型号',
            'PRODUCT_NAME' => '品名',
            'P_NAME' => '采购发起人',
            'APPLY_USER_NAME' => '指定采购人',
            'USE_NUM' => '领用库存数量',
            'USE_TOATL_PRICE' => '领用成本',
            'PRICE_LIMIT' => '单价最高限价',
            'PRICE' => '成交价',
            'NUM_LIMIT' => '申请数量',
            'NUM' => '购买数量',
            'PURCHASE_COST' => '采购成本',
            'TOTAL_COST' => '合计金额',
            'FEE_NAME' => '费用类型',
            'STOCK_NUM' => '退库数量',
            'BACK_STOCK_STATUS' => '退库状态',
            'IS_FUNDPOOL' => '是否资金池费用',
            'IS_KF' => '是否扣非'
        );

        $backStatusValues = array(
            0 => '未申请',
            1 => '申请中',
            2 => '申请打回'
        );

        $yesOrNo = array(
            0 => '否',
            1 => '是'
        );

        $rows = array();
        foreach ($data as $k => $v) {
            $index = 0;
            foreach ($v as $k1 => $v1) {
                if (in_array($k1, array_keys($purchaseNames))) {
                    if (empty($rows[$k1][0])) {
                        $rows[$index][0] = $purchaseNames[$k1];
                    }
                    if ($k1 == 'BACK_STOCK_STATUS') {
                        $v1 = $backStatusValues[$v1];
                    } else if (in_array($k1, array('IS_FUNDPOOL', 'IS_KF'))) {
                        $v1 = $yesOrNo[$v1];
                    }
                    $rows[$index] [$k + 1]= $v1;
                    $index++;
                }
            }
        }

        return $rows;
    }

    /**
     * 获取采购详情
     * @param $requireId
     * @param string $caseType
     * @return array
     */
    protected function getPurchaseInfo($requireId, $caseType = '') {
        $purchase = array(
            'result' => false,
            'desc' => array(),
            'list' => array()
        );

        try {
            if ($caseType == 'yg') {
                $sql = sprintf(self::YG_PURCHASE_REQUIRE_SQL, $requireId);
            } else {
                $sql = sprintf(self::PURCHASE_REQUIRE_SQL, $requireId);
            }
            $dbResult = D()->query($sql);
            if (is_array($dbResult) && count($dbResult)) {
                $purchase['result'] = true;
                $purchase['desc'] = $dbResult[0];
                $purchase['list'] = $this->mapPurchaseList($this->getPurchaseList($requireId));
                $this->initWorkFlow($dbResult[0]);
            }
        } catch (Exception $e) {
            $purchase['result'] = false;
        }

        return $purchase;
    }

    /**
     * 映射采购数据
     * @param $data
     * @return array
     */
    protected function mapPurchaseList($data) {
        $response = array();
        if (notEmptyArray($data)) {
            foreach ($data as $k => $v) {
                $response[$k] = $v;
                if (floatval($v['TOTAL_COST']) <= 0) {
                    $response[$k]['TOTAL_COST'] = floatval($v['PRICE_LIMIT']) * floatval($v['NUM_LIMIT']);
                }
            }
        }
        return $response;
    }

    /**
     * 获取采购列表
     * @param $requireId
     * @return array
     */
    protected function getPurchaseList($requireId) {
        $response = array();
        if (!empty($requireId)) {
            $response = D()->query(sprintf(self::PURCHASE_LIST_SQL, $requireId));
        }

        return $response;
    }

    /**
     * 格式化采购需求
     * @param $data
     * @return array
     */
    protected function mapRequirement($data) {
        empty($data['PROJECTNAME']) or $response['PROJECTNAME'] = array(
            'alias' => '项目名称',
            'val' => $data['PROJECTNAME']
        );
        empty($data['CONTRACT_NUM']) or $response['CONTRACT_NUM'] = array(
            'alias' => '合同编号',
            'val' => $data['CONTRACT_NUM']
        );
        empty($data['USER_NAME']) or $response['USER_NAME'] = array(
            'alias' => '发起人',
            'val' => $data['USER_NAME']
        );
        empty($data['REASON']) or $response['REASON'] = array(
            'alias' => '采购原因',
            'val' => $data['REASON']
        );
        empty($data['APPLY_TIME']) or $response['APPLY_TIME'] = array(
            'alias' => '申请时间',
            'val' => $data['APPLY_TIME']
        );
        empty($data['END_TIME']) or $response['END_TIME'] = array(
            'alias' => '最晚送达时间',
            'val' => $data['END_TIME']
        );
        ($data['STATUS'] === null) or $response['STATUS'] = array(
            'alias' => '状态',
            'val' => $this->requirementDesc[$data['STATUS']]
        );

        return $response;
    }

    /**
     * 审批工作流
     */
    public function opinionFlow() {
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );

        if ($this->myTurn) {
            $data = u2g($_REQUEST);
            Vendor('Oms.Flows.Flow');
            $this->workFlow = new Flow('Purchase');  // 项目下采购申请
            $result = $this->workFlow->doit($data);
            if (is_array($result)) {
                $response = $result;
            } else {
                $response['status'] = $result;
            }
        } else {
            $response['status'] = false;
            $response['message'] = '非当前审批人';
        }

        if (empty($response['url'])) {
            $response['url'] = U('Flow/flowList', 'status=1');
        }

        echo json_encode(g2u($response));
    }

    /**
     * 大宗采购申请处理流程
     */
    public function bulk_purchase_opinionFlow() {
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );

        // todo 试用 haspermission方法
        if ($this->myTurn) {
            $data = u2g($_REQUEST);
            $data['type'] = 'caigoushenqing';
            Vendor('Oms.Flows.Flow');
            $this->workFlow = new Flow('BulkPurchase');  // 大宗采购申请
            $result = $this->workFlow->doit($data);
            if (is_array($result)) {
                $response = $result;
            } else {
                $response['status'] = $result;
            }
        }

        if (empty($response['url'])) {
            $response['url'] = U('Flow/flowList', 'status=1');
        }
        echo json_encode(g2u($response));
    }

    /**
     * 将采购信息赋给界面
     * @param $purchaseInfo
     */
    private function assignPurchaseInfo($purchaseInfo) {
        if (is_array($purchaseInfo) && count($purchaseInfo)) {
            $require = $this->mapRequirement($purchaseInfo['desc']);
            if ($_REQUEST['purchaseType'] == 'bulkPurchase') {
                unset($require['PROJECTNAME']);
                unset($require['END_TIME']);
            }
            $this->assign('require', $require);  // 需求描述
            $this->assign('list', $purchaseInfo['list']);  // 采购信息
            $this->assign('purchaseListJSON', json_encode(g2u($purchaseInfo['list'])));
        }
    }

    protected function authMyTurn($flowId) {
        if (intval($flowId) > 0) {
            parent::authMyTurn($flowId);
        } else {
            switch($this->flowType) {

                default:
            }

            $this->myTurn = true;
        }
    }
}