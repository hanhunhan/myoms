<?php
/**
 * 标准调整
 * Created by PhpStorm.
 * User: superkemi
 */

class Feescale_changeAction extends ExtendAction {
    /*
     * 构造函数
     */
    protected $feeScaleType = null;

    public function __construct() {
        parent::__construct();

        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),
            'fee_scale_detail' => array(
                'name' => 'fee-scale-detail',
                'text' => '标准明细'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        $this->feeScaleType = array(
            '1'=>'单套收费标准',
            '2'=>'中介佣金',
            '3'=>'外部成交奖励',
            '4'=>'中介成交奖',
            '5'=>'置业顾问成交奖',
            '6'=>'带看奖',
        );

        $this->title = '标准调整';
        $this->processTitle = '关于标准调整申请的审核';

        //recordId
        //解决第一次调用数据展现问题（创建工作流）
        if(!$this->recordId)
            $this->recordId = intval($_REQUEST['RECORDID']);

        //caseID
        $this->caseId = intval($_REQUEST['CASEID']);

        Vendor('Oms.Flows.Flow');
        Vendor('Oms.Flows.Feescale_change');
        $this->workFlow = new Flow('Feescale_change');
        $this->feescale_change = new Feescale_change();

        $this->assign('flowId', $this->flowId);
        $this->assign('recordId', $this->recordId);
        $this->assign('CASEID', $this->caseId);
        $this->assign('title', $this->title);
    }

    /**
     * 展示流程信息
     */
    public function process() {
        //process数据
        $viewData = array();

        //转交下一步（状态）
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);
        }
        $this->assignWorkFlows($this->flowId);

        $feeScaleInfo = $this->getFeeScale($this->recordId);

        $viewData['feeScaleInfo'] = $feeScaleInfo;
        //业务类型
        $viewData['caseType'] = D("ProjectCase")->get_conf_case_type_remark();
        //标准类型
        $viewData['feeScaleType'] = $this->feeScaleType;

        //退票信息
        $this->assign('viewData', $viewData);
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        //菜单
        $this->assign('menu', $this->menu);
        $this->display('process');
    }


    /***
     * @param $id 退票审核单LISTID
     * @return array
     */
    protected function getFeeScale($id) {

        //当前用户编号
        $uid = intval($_SESSION['uinfo']['uid']);

        //当前城市编号
        $city_id = intval($this->channelid);

        $sql = <<<FeeScale_SQL
        SELECT P.CONTRACT ,P.PROJECTNAME,B.SCALETYPE,C.TYPE,C.ADATE,C.AUSER,A.STYPE,A.AMOUNT,A.MTYPE,A.REASON FROM ERP_FEESCALE A
            INNER JOIN ERP_CASE B ON A.CASE_ID = B.ID
            INNER JOIN ERP_FEESCALE_CHANGE C ON A.CH_ID = C.ID
            INNER JOIN ERP_PROJECT P ON B.PROJECT_ID = P.ID
            WHERE A.CH_ID = %d
FeeScale_SQL;

        $sql = sprintf($sql,$id);

        $feeScaleInfo = M()->query($sql);
        return $feeScaleInfo;
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

        echo json_encode($response);
    }

}