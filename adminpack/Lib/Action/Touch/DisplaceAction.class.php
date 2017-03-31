<?php

/**
 * 置换申请工作流控制器
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/8
 * Time: 10:25
 */
class DisplaceAction extends ExtendAction {
    /**
     * 置换需求查询语句
     */
    const DISPLACE_REQUIRE_SQL = <<<SQL
        SELECT A.ID,
               A.PRJ_ID,
               A.USER_ID,
               A.REASON,
               A.CASE_ID,
               P.PROJECTNAME,
               to_char(A.APPLY_TIME,'YYYY-MM-DD hh24:mi:ss') AS APPLY_TIME,
               to_char(A.END_TIME,'YYYY-MM-DD hh24:mi:ss') AS END_TIME,
               A.STATUS,
               U.NAME AS USER_NAME,
               C.SCALETYPE,
               IC.CONTRACT_NO,
               H.TOTAL_MONEY
        FROM ERP_DISPLACE_REQUISITION A
        LEFT JOIN ERP_CASE C ON C.ID = A.CASE_ID
        LEFT JOIN ERP_PROJECT P ON P.ID = A.PRJ_ID
        LEFT JOIN ERP_USERS U ON U.ID = A.USER_ID
        LEFT JOIN ERP_INCOME_CONTRACT IC  ON A.CONTRACT_ID = IC.ID
        LEFT JOIN (SELECT DECODE(SUBSTR(SUM(NUM * PRICE),1,1),'.','0'||SUM(NUM * PRICE),SUM(NUM * PRICE)) AS TOTAL_MONEY,DR_ID FROM ERP_DISPLACE_WAREHOUSE GROUP BY DR_ID) H ON A.ID = H.DR_ID
        WHERE A.ID = %d
SQL;

    /**
     * 置换明细查询语句
     */
    const DISPLACE_DETAIL_SQL = <<<DISPLACE_DETAIL_SQL
        SELECT A.ID,
               A.DR_ID,
               A.BRAND,
               A.MODEL,
               A.PRODUCT_NAME,
               U.NAME AS USER_NAME,
               A.NUM,
               DECODE(SUBSTR(A.PRICE,1,1),'.','0'||A.PRICE,A.PRICE) AS PRICE,
               A.STATUS,
               A.LIVETIME,
               A.ALARMTIME,
               A.SOURCE,
               A.ADD_TIME
        FROM ERP_DISPLACE_WAREHOUSE A
        LEFT JOIN ERP_USERS U ON U.ID = A.ADD_USERID
        WHERE A.DR_ID = %d
        ORDER BY A.ID DESC
DISPLACE_DETAIL_SQL;

    /**
     * 置换需求状态描述
     * @var array
     */
    protected $requirementDesc = array(
        0 => '未提交',
        1 => '审核中',
        2 => '审核通过',
        3 => '审核未通过',
        4 => '置换完成'
    );

    public function __construct() {
        parent::__construct();

        // 初始化菜单
        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),
            'displace_detail' => array(
                'name' => 'displace-detail',
                'text' => '置换详情'
            ),
            'displace_list' => array(
                'name' => 'displace-list',
                'text' => '置换明细'
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
    private function initWorkFlow() {
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('Displace');  // 项目下置换申请
        $this->assign('flowType', 'zhihuanshenqing');
        $this->assign('flowTypeText', '置换申请');
    }

    /**
     * 处理工作流
     */
    public function process() {
        if (empty($this->flowId)) {
            $this->recordId = $_REQUEST['RECORDID'];
        }
        //获取置换详情
        $displaceMainInfo = $this->getDisplaceInfo($this->recordId);

        if (intval($this->flowId) > 0 && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);  // 当前办理人已经审阅工作流，修改状态
        }
        $this->assigndisplaceInfo($displaceMainInfo);  // 将置换信息赋给视图
        $this->assignWorkFlows($this->flowId);

        //业务类型数组
        $caseTypeArr = D('ProjectCase')->get_conf_case_type();
        $caseTypePinYinArr = array_flip($caseTypeArr);
        $caseTypePinYin = $caseTypePinYinArr[$displaceMainInfo['desc']['SCALETYPE']]; //获取拼写

        //传递编辑参数
        $tabNumber = $caseTypePinYin=='yg'?12:13;
        $this->assign('tabNumber', $tabNumber);  // tabnumber
        $this->assign('caseTypePinYin', $caseTypePinYin);  // caseTypePinYin

        $this->assign('title', '置换申请');
        $this->assign('menu', $this->menu);  // 菜单
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // 控制按钮显示
        $this->assign('projectName', '关于置换申请的审批'); // 工作流标题
        $this->assign('CASEID', $displaceMainInfo['desc']['CASE_ID']);  // 案例编号
        $this->assign('prjid', $displaceMainInfo['desc']['PRJ_ID']);  // 案例编号
        $this->assign('SCALETYPE', $displaceMainInfo['desc']['SCALETYPE']);  // 业务类型
        $this->assign('recordId', $this->recordId); // can't comment
        $this->display('index');
    }

    public function detail() {
        $this->display('detail');
    }

    /**
     * 翻转置换数据
     * @param $data
     * @return array
     */
    protected function verticaldisplaceList($data) {
        $displaceNames = array(
            'BRAND' => '品牌',
            'MODEL' => '型号',
            'PRODUCT_NAME' => '品名',
            'USER_NAME' => '置换数量',
            'PRICE' => '置换价',
            'SOURCE' => '货源',
            'LIVETIME' => '有效期',
            'ALARMTIME' => '有效期报警时间',
            'ADD_USERID' => '添加人',
            'ADD_TIME' => '添加时间',
            'TOTAL_COST' => '合计金额',
        );

        $rows = array();
        foreach ($data as $k => $v) {
            $index = 0;
            foreach ($v as $k1 => $v1) {
                if (in_array($k1, array_keys($displaceNames))) {
                    if (empty($rows[$k1][0])) {
                        $rows[$index][0] = $displaceNames[$k1];
                    }
                    $rows[$index] [$k + 1]= $v1;
                    $index++;
                }
            }
        }

        return $rows;
    }

    /**
     * 获取置换详情
     * @param $requireId
     * @param string $caseType
     * @return array
     */
    protected function getDisplaceInfo($requireId) {
        $displace = array(
            'result' => false,
            'desc' => array(),
            'list' => array()
        );

        try {
            $sql = sprintf(self::DISPLACE_REQUIRE_SQL, $requireId);

            $dbResult = D()->query($sql);
            if (is_array($dbResult) && count($dbResult)) {
                $displace['result'] = true;
                $displace['desc'] = $dbResult[0];
                $displace['list'] = $this->mapDisplaceList($this->getDisplaceDetail($requireId));
                $this->initWorkFlow($dbResult[0]);
            }
        } catch (Exception $e) {
            $displace['result'] = false;
        }

        return $displace;
    }

    /**
     * 映射置换数据
     * @param $data
     * @return array
     */
    protected function mapDisplaceList($data) {
        $response = array();
        if (notEmptyArray($data)) {
            foreach ($data as $k => $v) {
                $response[$k] = $v;
                if (floatval($v['TOTAL_COST']) <= 0) {
                    $response[$k]['TOTAL_COST'] = floatval($v['PRICE']) * floatval($v['NUM']);
                }
            }
        }
        return $response;
    }

    /**
     * 获取置换列表
     * @param $requireId
     * @return array
     */
    protected function getDisplaceDetail($requireId) {
        $response = array();
        if (!empty($requireId)) {
            $response = D()->query(sprintf(self::DISPLACE_DETAIL_SQL, $requireId));
        }
        return $response;
    }

    /**
     * 格式化置换需求
     * @param $data
     * @return array
     */
    protected function mapRequirement($data) {
        empty($data['PROJECTNAME']) or $response['PROJECTNAME'] = array(
            'alias' => '项目名称',
            'val' => $data['PROJECTNAME']
        );
        empty($data['CONTRACT_NO']) or $response['CONTRACT_NO'] = array(
            'alias' => '合同编号',
            'val' => $data['CONTRACT_NO']
        );
        empty($data['USER_NAME']) or $response['USER_NAME'] = array(
            'alias' => '发起人',
            'val' => $data['USER_NAME']
        );
        empty($data['REASON']) or $response['REASON'] = array(
            'alias' => '置换原因',
            'val' => $data['REASON']
        );
        empty($data['APPLY_TIME']) or $response['APPLY_TIME'] = array(
            'alias' => '添加时间',
            'val' => $data['APPLY_TIME']
        );
        empty($data['END_TIME']) or $response['END_TIME'] = array(
            'alias' => '最晚送达时间',
            'val' => $data['END_TIME']
        );
        empty($data['TOTAL_MONEY']) or $response['TOTAL_MONEY'] = array(
            'alias' => '合计置换金额',
            'val' => $data['TOTAL_MONEY'] . '元'
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
            $this->workFlow = new Flow('Displace');  // 项目下置换申请
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
     * 将置换信息赋给界面
     * @param $displaceInfo
     */
    private function assigndisplaceInfo($displaceInfo) {
        if (is_array($displaceInfo) && count($displaceInfo)) {
            $require = $this->mapRequirement($displaceInfo['desc']);
            if ($_REQUEST['displaceType'] == 'bulkdisplace') {
                unset($require['PROJECTNAME']);
                unset($require['END_TIME']);
            }
            $this->assign('require', $require);  // 需求描述
            $this->assign('list', $displaceInfo['list']);  // 置换信息
            $this->assign('displaceListJSON', json_encode(g2u($displaceInfo['list'])));
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