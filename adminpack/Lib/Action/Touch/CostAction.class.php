<?php
/**
 * 成本划拨
 * Created by PhpStorm.
 * User: superkemi
 */

class CostAction extends ExtendAction {
    /*
     * 构造函数
     */
    protected $feeScaleType = null;
    private $caseId = 0;

    public function __construct() {
        parent::__construct();

        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),

            'cost_project' => array(
                'name' => 'cost-project',
                'text' => '划拨项目'
            ),
            'cost_detail' => array(
                'name' => 'cost-detail',
                'text' => '划拨明细'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        $this->caseId = intval($_REQUEST['CASEID']);

        $this->title = '成本划拨';
        $this->processTitle = '关于成本划拨申请的审核';

        //recordId
        //解决第一次调用数据展现问题（创建工作流）
        if(!$this->recordId)
            $this->recordId = intval($_REQUEST['RECORDID']);

        Vendor('Oms.Flows.Flow');
        Vendor('Oms.Flows.Cost');
        $this->workFlow = new Flow('Cost');
        $this->cost = new Cost();

        // 工作流类型
        $this->assign('flowId', $this->flowId);
        $this->assign('CASEID', $this->caseId);
        $this->assign('recordId', $this->recordId);
        $this->assign('title', $this->title);
    }

    /**
     * 展示流程信息
     */
    public function process() {

        $case_id = $this->caseId;

        //process数据
        $viewData = array();

        //转交下一步（状态）
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);
        }
        $this->assignWorkFlows($this->flowId);

        //划拨申请说明
        $costInfo = $this->getCostInfo();
        $transferId = $costInfo['0']['TID'];

        //划出明细
        $costDetailInfo = $this->getCostDetailInfo($transferId);

        $caseType = D("ProjectCase")->get_conf_case_type_remark();

        foreach($costDetailInfo as $key=>$val){
            $scale_type_arr = explode(",",$val['SCALETYPES']);
            if($scale_type_arr) {
                $costDetailInfo[$key]['SCALETYPES'] = '';
                foreach ($scale_type_arr as $k => $v) {
                    //项目下活动不需要展现
                    if(intval($v)!=7) {
                        $costDetailInfo[$key]['SCALETYPES'] .= $caseType[$v] . ",";
                    }
                }
                $costDetailInfo[$key]['SCALETYPES'] = trim($costDetailInfo[$key]['SCALETYPES'], ",");
            }
        }

        //赋值
        $viewData['costInfo'] = $costInfo;
        $viewData['costDetailInfo'] = $costDetailInfo;

        //发票状态
        $status_arr = D("Member")->get_conf_all_status_remark();
        $viewData['invoiceStatus'] = $status_arr;

        //业务类型
        $viewData['caseType'] = D("ProjectCase")->get_conf_case_type_remark();

        //获取收费标准
        //设置收费标准
        $feescale = D('Project')->get_feescale_by_cid($case_id);

        $fees_arr = array();
        if(is_array($feescale) && !empty($feescale) ) {
            foreach ($feescale as $key => $value) {
                $unit = $value['STYPE'] == 0 ? '元' : '%';
                $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'] . $unit;
            }
        }

        $viewData['feesArr'] = $fees_arr;

        //退票信息
        $this->assign('viewData', $viewData);
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        //菜单
        $this->assign('menu', $this->menu);
        $this->display('process');
    }


    /**
     * 获取划拨汇总数据
     * @return array|mixed
     */
    private function getCostInfo(){
        $costInfo = array();

        $sql = <<<COST_SQL
            SELECT T.NAME AS CITY_NAME,C.CONTRACT,U.NAME AS UNAME,A.INFO,A.APPLYTIME,A.CSTATUS,A.STATUS,A.ID AS TID,C.ID,C.CITY_ID,C.CONTRACT,C.PROJECTNAME,C.CUSER,B.SCALETYPE,SUM(D.OUTCOST) AS OUTCOST,SUM(D.PROFIT) as PROFIT,SUM(D.FUNDPOOLCOST) as FUNDPOOLCOST FROM
            ERP_TRANSFER A
            LEFT JOIN ERP_CASE B ON A.CASEID = B.ID
            LEFT JOIN ERP_PROJECT C ON B.PROJECT_ID = C.ID
            RIGHT JOIN ERP_TRANSFEROUT_DETAIL D ON D.TRANSFER_ID = A.ID
            LEFT JOIN ERP_CITY T ON C.CITY_ID = T.ID
            LEFT JOIN ERP_USERS U ON U.ID = C.CUSER
            WHERE ISDEL=0 AND A.ID = %d GROUP BY D.TRANSFER_ID,C.ID,C.CITY_ID,C.CONTRACT,C.PROJECTNAME,C.CUSER,B.SCALETYPE,A.ID,A.APPLYTIME,A.CSTATUS,A.STATUS,A.INFO,T.NAME,U.NAME
COST_SQL;

        $sql = sprintf($sql,$this->recordId);

        $costInfo = M()->query($sql);
        return $costInfo;

    }


    /**
     * @param $transferId
     * @return array|mixed
     */
    private function getCostDetailInfo($transferId){
        $costDetailInfo = array();

        $sql = <<<CostDetail_SQL
        SELECT wmsys.wm_concat(CA.SCALETYPE) AS SCALETYPES,T.NAME AS CITY_NAME,U.NAME AS UNAME,C.CONTRACT,A.ID,C.CITY_ID,C.PROJECTNAME,C.CUSER,C.CONTRACT,B.APPLYTIME,A.ETIME,A.PROFIT,A.OUTCOST,A.KOUFEI,A.FUNDPOOLCOST,A.TRANSFER_ID,A.INFO,A.PROJECT_TYPE_ID,A.PROJECTID,A.STATUS
        FROM ERP_TRANSFEROUT_DETAIL A
        LEFT JOIN ERP_TRANSFER  B ON A.TRANSFER_ID = B.ID
        LEFT JOIN ERP_PROJECT C ON A.PROJECTID = C.ID
        LEFT JOIN ERP_CASE CA ON CA.PROJECT_ID = C.ID
        LEFT JOIN ERP_CITY T ON C.CITY_ID = T.ID
        LEFT JOIN ERP_USERS U ON U.ID = C.CUSER
        WHERE A.transfer_id = %d
        GROUP BY T.NAME,U.NAME,C.CONTRACT,A.ID,C.CITY_ID,C.PROJECTNAME,C.CUSER,C.CONTRACT,B.APPLYTIME,A.ETIME,A.PROFIT,A.OUTCOST,A.KOUFEI,A.FUNDPOOLCOST,A.TRANSFER_ID,A.INFO,A.PROJECT_TYPE_ID,A.PROJECTID,A.STATUS
CostDetail_SQL;

        $sql = sprintf($sql,$transferId);

        $costDetailInfo = M()->query($sql);
        return $costDetailInfo;

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

        //赋值caseId
        $_REQUEST['caseId'] = $this->caseId;

        $result = $this->workFlow->doit($_REQUEST);

        if (is_array($result)) {
            $response = $result;
        } else {
            if($result)
                $response['status'] = 1;
            else
                $response['status'] = 0;
        }

        echo json_encode($response);
    }

}