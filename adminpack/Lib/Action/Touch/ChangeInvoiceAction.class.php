<?php
/**
 * 会员申请退款
 * Created by PhpStorm.
 * User: superkemi
 */

class ChangeInvoiceAction extends ExtendAction {
    /*
     * 构造函数
     */
    public function __construct() {
        parent::__construct();

        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),
            'change_invoice_list' => array(
                'name' => 'change-invoice-list',
                'text' => '换发票列表'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        $this->title = '会员换发票';
        $this->processTitle = '关于会员换发票申请的审核';

        //recordId
        //解决第一次调用数据展现问题（创建工作流）
        if(!$this->recordId)
            $this->recordId = intval($_REQUEST['RECORDID']);

        Vendor('Oms.Flows.Flow');
        Vendor('Oms.Flows.ChangeInvoice');
        $this->workFlow = new Flow('ChangeInvoice');
        $this->changeinvoice = new ChangeInvoice();

        $this->assign('flowId', $this->flowId);
        $this->assign('recordId', $this->recordId);
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

        $changeInvoiceInfo = $this->changeInvoiceInfo($this->recordId);

        $viewData['changeInvoiceInfo'] = $changeInvoiceInfo;

        $caseId = $changeInvoiceInfo[0]['CASE_ID'];

        //发票状态
        $status_arr = D("Member")->get_conf_all_status_remark();
        $viewData['invoiceStatus'] = $status_arr;

        //换发票信息
        $this->assign('viewData', $viewData);
        $this->assign('CASEID', $caseId);
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        //菜单
        $this->assign('menu', $this->menu);
        $this->display('process');
    }


    /***
     * @param $id 退票审核单LISTID
     * @return array
     */
    protected function changeInvoiceInfo($id) {

        //当前用户编号
        $uid = intval($_SESSION['uinfo']['uid']);

        //当前城市编号
        $city_id = intval($this->channelid);

        $sql = <<<CHANGEINVOICE_SQL
        SELECT C.ID,C.APPLY_TIME,C.APPLY_USER_ID,C.MID,A.PRJ_NAME,A.REALNAME,A.CARDTIME,
                A.CARDSTATUS,A.INVOICE_STATUS,A.INVOICE_NO,A.PAID_MONEY,A.TOTAL_PRICE,A.MOBILENO,
                B.CONTRACT,U.NAME AS APPLY_USER_NAME,A.CASE_ID
                FROM ERP_CHANGE_INVOICE_DETAIL C
                LEFT JOIN ERP_CARDMEMBER A ON C.MID=A.ID
                LEFT JOIN ERP_PROJECT B ON A.PRJ_ID=B.ID
                LEFT JOIN ERP_USERS U ON C.APPLY_USER_ID = U.ID
                WHERE A.STATUS=1 AND C.MID = %d
CHANGEINVOICE_SQL;

        $sql = sprintf($sql,$id);

        $changeInvoiceInfo = M()->query($sql);
        return $changeInvoiceInfo;
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