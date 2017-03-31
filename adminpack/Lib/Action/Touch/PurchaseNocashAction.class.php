<?php

/**
 * 非付现
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/8
 * Time: 10:25
 */
class PurchaseNocashAction extends ExtendAction {
    /**
     * 采购需求查询语句
     */
    const PURCHASE_REQUIRE_SQL = <<<SQL
        SELECT A.*,B.PROJECTNAME,B.CONTRACT,F.NAME
        FROM ERP_NONCASHCOST  A
        LEFT JOIN ERP_PROJECT B 
		ON A.PROJECT_ID = B.ID
		LEFT JOIN ERP_FEE F
		ON F.ID = A.FEE_ID
        WHERE A.ID = %d
SQL;

  
    

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
                'text' => ' 非付现成本'
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
        $this->workFlow = new Flow('PurchaseNocash');  // 项目下采购申请
        $this->assign('flowType', 'PurchaseNocash');
        $this->assign('flowTypeText', '非付现成本申请');
    }

    /**
     * 处理工作流
     */
    public function process() {
		if($this->flowId){
            if ($this->myTurn) {
                $this->workFlow->nextstep($this->flowId);  // 先修改目前的状态
            }
			$purchaseInfo = $this->getPurchaseInfo($this->recordId);
		}else{
			$purchaseInfo = $this->getPurchaseInfo($_REQUEST['noncashcost_id']);
			$this->assign('recordId', $_REQUEST['noncashcost_id']);
		}

		$sql = "select PROJECTNAME from erp_project where contract='{$purchaseInfo['CONTRACT_NO']}'";
        $out_pro =  D()->query($sql);
        $this->assign('outProjectName' , $out_pro[0]);
		$this->assign('purchaseInfo', $purchaseInfo);  // 菜单
        $this->assignWorkFlows($this->flowId);
        $this->assign('title', '非付现成本申请');
        $this->assign('menu', $this->menu);  // 菜单
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // 控制按钮显示
        $this->display('index');
    }

    /**
     * 获取采购详情
     * @param $requireId
     * @param string $caseType
     * @return array
     */
    protected function getPurchaseInfo($requireId, $caseType = '') {
        

        $sql = sprintf(self::PURCHASE_REQUIRE_SQL, $requireId);
		$qResult = D()->query($sql);
		$type_arr = array(1=>'广告',2=>'差价',3=>'活动差价',4=>'其他');
		$status_arr = array('未提交','审核中','审核通过','审核未通过','财务已确认');
        $scaleType_arr = array(1=>'电商',2=>'分销',3=>'硬广',4=>'活动',5=>'产品',7=>'项目活动',8=>'非我方收筹');
		
		foreach($qResult as $key=>$one){
			$one['STATUS'] = $one['STATUS'] ? $one['STATUS'] : 0;  
			$qResult[$key]['TYPE'] = $type_arr[$one['TYPE']]; 
			$qResult[$key]['ATTACHMENT'] = $this->getWorkFlowFiles($one['ATTACHMENT']);
			$qResult[$key]['STATUS'] = $status_arr[$one['STATUS']];
            $qResult[$key]['SCALETYPE'] = $scaleType_arr[$one['SCALETYPE']];
		}
 
        return $qResult[0];
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
		if ($this->myTurn) {  // 非当前审批人是该登录用户
			$_REQUEST = u2g($_REQUEST);
			// Vendor('Oms.Flows.Flow');
			//$flow = new Flow('PurchaseNocash');
			$result = $this->workFlow->doit($_REQUEST);
			if (is_array($result)) {
				$response = $result;
			} else {
				$response['result']  = $result;
				$response['status'] = $result>0 ? 1:0 ;
			}
		} else {
            $response['message'] = '非当前审批人';
        }


        echo json_encode($response);
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