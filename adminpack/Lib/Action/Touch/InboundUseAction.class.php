<?php

/*置换物品售卖、内部领用、报损、售卖变更*/

class InboundUseAction extends ExtendAction
{

    /**
     * 详情查询语句
     */
    const INBOUNDUSE_REQUIRE_SQL = <<<SQL
        SELECT
               A.APPLY_REASON,
               to_char(A.APPLY_TIME,'YYYY-MM-DD hh24:mi:ss') AS APPLY_TIME,
               U.NAME AS USER_NAME,
               A.BUYER
        FROM ERP_DISPLACE_APPLYLIST A
        LEFT JOIN ERP_USERS U ON U.ID = A.APPLY_USER_ID
        WHERE A.ID = %d
SQL;

      const INBOUNDUSE_DETAIL_SQL = <<<INBOUNDUSE_DETAIL_SQL
        SELECT A.ID,L.APPLY_USER_ID,D.NAME AS USERNAME,L.STATUS,L.TYPE,A.LIST_ID,
                I.CONTRACT_NO AS CONTRACT,P.PROJECTNAME,A.AMOUNT,
                RTRIM(to_char(A.MONEY,'fm99999999990.99'),'.') AS MONEY,
                B.CHANGETIME,
                B.BRAND,B.MODEL,B.PRODUCT_NAME,B.SOURCE,
                RTRIM(to_char(B.PRICE,'fm99999999990.99'),'.') AS PRICE,
                (B.NUM + A.AMOUNT) AS NUM
                FROM ERP_DISPLACE_APPLYDETAIL A
                LEFT JOIN ERP_DISPLACE_APPLYLIST L ON A.LIST_ID = L.ID
                LEFT JOIN ERP_DISPLACE_WAREHOUSE B ON A.DID=B.ID
                LEFT JOIN ERP_DISPLACE_REQUISITION R ON R.ID = B.DR_ID
                LEFT JOIN ERP_INCOME_CONTRACT I ON I.ID = R.CONTRACT_ID
                LEFT JOIN ERP_CASE C ON B.CASE_ID=C.ID
                LEFT JOIN ERP_PROJECT P ON C.PROJECT_ID = P.ID
                LEFT JOIN ERP_USERS D ON L.APPLY_USER_ID = D.ID
                WHERE  L.ID = %d
INBOUNDUSE_DETAIL_SQL;

    /**
     * 置换状态描述
     * @var array
     */
    protected $requirementDesc = array(
        0 => '未提交',
        1 => '审核中',
        2 => '审核通过',
        3 => '审核未通过',
        4 => '完成'
    );
    /*
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();

        if (!$this->recordId)
            $this->recordId = intval($_REQUEST['RECORDID']);

        Vendor('Oms.Flows.Flow');
        Vendor('Oms.Flows.InboundUse');
        $this->workFlow = new Flow('InboundUse');
        $this->InboundUse = new InboundUse();

        $this->assign('flowId', $this->flowId);
        $this->assign('recordId', $this->recordId);



        // 初始化菜单
        $this->initWorkFlow();
    }

    /**
     * 初始化工作流
     */
    private function initWorkFlow() {
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('InboundUse');
        $flowTypePinYin = trim($_REQUEST['flowTypePinYin']);
        $this->assign('flowType', $flowTypePinYin);
    }


    /**
     * 页面渲染
     */
    public function process(){

        $DisplaceMethod = trim($_REQUEST['flowTypePinYin']); //获取工作流类型

        switch($DisplaceMethod){
            case "shoumai":
                $projectName = "关于置换物品售卖的审批";
                $title = "置换物品售卖";
                $this->menu = array(
                    'application' => array(
                        'name' => 'application',
                        'text' => '申请说明'
                    ),
                    'Inbound_detail' => array(
                        'name' => 'Inbound_detail',
                        'text' => '售卖详情'
                    ),
                    'Inbound_list' => array(
                        'name' => 'Inbound_list',
                        'text' => '售卖明细'
                    ),
                    'opinion' => array(
                        'name' => 'opinion',
                        'text' => '审批意见'
                    )
                );
                break;
            case "baosun":
                $projectName = "关于置换物品报损的审批";
                $title = "置换物品报损";
                $this->menu = array(
                    'application' => array(
                        'name' => 'application',
                        'text' => '申请说明'
                    ),
                    'Inbound_detail' => array(
                        'name' => 'Inbound_detail',
                        'text' => '报损详情'
                    ),
                    'Inbound_list' => array(
                        'name' => 'Inbound_list',
                        'text' => '置换明细'
                    ),
                    'opinion' => array(
                        'name' => 'opinion',
                        'text' => '审批意见'
                    )
                );
                break;
            case "neibulingyong":
                $projectName = "关于置换物品内部领用的审批";
                $title = "置换物品内部领用";
                $this->menu = array(
                    'application' => array(
                        'name' => 'application',
                        'text' => '申请说明'
                    ),
                    'Inbound_detail' => array(
                        'name' => 'Inbound_detail',
                        'text' => '领用详情'
                    ),
                    'Inbound_list' => array(
                        'name' => 'Inbound_list',
                        'text' => '领用明细'
                    ),
                    'opinion' => array(
                        'name' => 'opinion',
                        'text' => '审批意见'
                    )
                );
                break;
        }

        //转交下一步（状态）
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);
        }
        $this->assignWorkFlows($this->flowId);

        $this->assign("displaceMethod",$DisplaceMethod);
        $this->assign("title",$title);
        $this->assign("projectName",$projectName);

        $inboundUseInfo = $this->getInboundInfo($this->recordId,$DisplaceMethod);
        $this->assigninboundUseInfo($inboundUseInfo,$DisplaceMethod);
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        //菜单
        $this->assign('menu', $this->menu);
        $this->display('process');
    }

    /**
     * 获取使用详情信息
     * @param $inboundUseInfo
     */
    private function assigninboundUseInfo($inboundUseInfo,$DisplaceMethod) {
        if (is_array($inboundUseInfo) && count($inboundUseInfo)) {
            $require = $this->mapRequirement($inboundUseInfo['desc'],$DisplaceMethod);

            //内部领用 + 报损  不存在买家
            if($DisplaceMethod=='neibulingyong' || $DisplaceMethod=='baosun'){
                unset($require['BUYER']);
            }

            $this->assign('require', $require);  //总述

            $this->assign('list', $inboundUseInfo['list']);  //明细
            $this->assign('inboundUseInfoListJSON', json_encode(g2u($inboundUseInfo['list'])));
        }
    }

    /**
     * 获取售卖报损零用明细
     * @param $requireId
     * @return array
     */
    protected function getInboundInfo($requireId,$DisplaceMethod) {
        $inboundUse = array(
            'result' => false,
            'desc' => array(),
            'list' => array()
        );

        try {

            $sql = sprintf(self::INBOUNDUSE_REQUIRE_SQL, $requireId);
            $dbResult = D()->query($sql);
            if (is_array($dbResult) && count($dbResult)) {
                $inboundUse['result'] = true;
                $inboundUse['desc'] = $dbResult[0];
                $inboundUse['list'] = $this->mapInboundUseList($this->getInboundUseDetail($requireId),$DisplaceMethod);
            }
        } catch (Exception $e) {
            $inboundUse['result'] = false;
        }

        //获取总金额
        foreach($inboundUse['list'] as $key=>$val) {
            $inboundUse['desc']['TOTAL_MONEY'] += $val['TOTAL_COST'];
        }

        return $inboundUse;
    }

    /**
     * @param $requireId
     * @return array|mixed
     */
    protected function getInboundUseDetail($requireId) {
        $response = array();
        if (!empty($requireId)) {
            $response = D()->query(sprintf(self::INBOUNDUSE_DETAIL_SQL, $requireId));
        }
        return $response;
    }

    /**
     * 获取总价格
     * @param $data
     * @return array
     */
    protected function mapInboundUseList($data,$displaceMethod) {
        $response = array();
        if (notEmptyArray($data)) {
            foreach ($data as $k => $v) {
                $response[$k] = $v;
                if (floatval($v['TOTAL_COST']) <= 0) {
                    switch($displaceMethod){
                        case 'shoumai':
                            $response[$k]['TOTAL_COST'] = floatval($v['AMOUNT']) * floatval($v['MONEY']);
                            break;
                        case 'neibulingyong':
                            $response[$k]['TOTAL_COST'] = floatval($v['PRICE']) * floatval($v['AMOUNT']);
                            break;
                        case 'baosun':
                            $response[$k]['TOTAL_COST'] = floatval($v['PRICE']) * floatval($v['NUM']);
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        return $response;
    }

    /**
     * 映射使用方法的数据
     * @param $response
     * @return mixed
     */
    protected function mapRequirement($response,$DisplaceMethod) {
        switch($DisplaceMethod) {
            //售卖
            case "shoumai":
                $response['USER_NAME'] = array(
                    'alias' => '发起人',
                    'val' => $response['USER_NAME']
                );
                $response['APPLY_REASON'] = array(
                    'alias' => '售卖原因',
                    'val' => $response['APPLY_REASON']
                );
                $response['BUYER'] = array(
                    'alias' => '买家',
                    'val' => $response['BUYER'],
                );
                $response['APPLY_TIME'] = array(
                    'alias' => '申请时间',
                    'val' => $response['APPLY_TIME']
                );
                $response['TOTAL_MONEY'] = array(
                    'alias' => '售卖总金额',
                    'val' => $response['TOTAL_MONEY']
                );
                break;
            //内部领用
            case "neibulingyong":
                $response['USER_NAME'] = array(
                    'alias' => '发起人',
                    'val' => $response['USER_NAME'],
                );
                $response['APPLY_REASON'] = array(
                    'alias' => '领用原因',
                    'val' => $response['APPLY_REASON']
                );
                $response['APPLY_TIME'] = array(
                    'alias' => '申请时间',
                    'val' => $response['APPLY_TIME']
                );
                $response['TOTAL_MONEY'] = array(
                    'alias' => '领用总金额',
                    'val' => $response['TOTAL_MONEY']
                );
                break;
            //报损
            case "baosun":
                $response['USER_NAME'] = array(
                    'alias' => '发起人',
                    'val' => $response['USER_NAME']
                );
                $response['APPLY_TIME'] = array(
                    'alias' => '申请时间',
                    'val' => $response['APPLY_TIME']
                );
                $response['TOTAL_MONEY'] = array(
                    'alias' => '报损总价值',
                    'val' => $response['TOTAL_MONEY']
                );
                $response['APPLY_REASON'] = array(
                    'alias' => '报损说明',
                    'val' => $response['APPLY_REASON']
                );
        }
        return $response;
    }

    /**
     * 审批工作流售卖
     */
    public function opinionFlow(){
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );

        //工作流类型 1：售卖 2：内部领用 3：报损  4：售卖变更
        $flowDisplaceType = isset($_REQUEST['flowDisplaceType'])?intval($_REQUEST['flowDisplaceType']):0;

        if ($this->myTurn) {
            $data = u2g($_REQUEST);
            $data['flowDisplaceType'] = $flowDisplaceType; //传递type类型
            Vendor('Oms.Flows.Flow');
            $this->workFlow = new Flow('InboundUse');
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

        $response['url'] = U('Flow/flowList', 'status=1');
        echo json_encode(g2u($response));
    }

    /**
     * 权限判断
     * @param $flowId
     */
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