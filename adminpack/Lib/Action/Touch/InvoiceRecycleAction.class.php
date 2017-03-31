<?php
/**
 * 会员申请退票
 * Created by PhpStorm.
 * User: superkemi
 */

class InvoiceRecycleAction extends ExtendAction {
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
            'recycle_invoice_list' => array(
                'name' => 'recycle-invoice-list',
                'text' => '退票列表'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见',
            )
        );

        $this->title = '退票申请';
        $this->processTitle = '关于会员退票申请的审核';

        //recordId
        //解决第一次调用数据展现问题（创建工作流）
        if(!$this->recordId)
            $this->recordId = intval($_REQUEST['RECORDID']);

        Vendor('Oms.Flows.Flow');
        Vendor('Oms.Flows.RecycleInvoice');
        $this->workFlow = new Flow('InvoiceRecycle');
        $this->recycleInvoice = new InvoiceRecycle();

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

        $recycleInfo = $this->getRecycleInfo($this->recordId);
        $viewData['recycleInfo'] = $recycleInfo;

        //发票状态
        $status_arr = D("Member")->get_conf_all_status_remark();
        $viewData['invoiceStatus'] = $status_arr;

        //退票信息
        $this->assign('viewData', $viewData);
        $this->assign("recordId",$this->recordId);
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        //菜单
        $this->assign('menu', $this->menu);
        $this->display('process');
    }

    /**
    +----------------------------------------------------------
     * 撤销退票申请
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function revoke_invoice_recycle()
    {
        $res = array(
            'status'=>false,
            'msg'=>'',
            'data'=>null,
            'url'=>'',
        );

        $detail_id = isset($_REQUEST['invoiceRecycleId'])?intval($_REQUEST['invoiceRecycleId']):0;

        D()->startTrans();
        $flagStatus = $this->recycleInvoice->delete_from_details($detail_id);

        if($flagStatus){
            $res['status'] = true;
            D()->commit();
        }
        else {
            D()->rollback();
        }

        die(@json_encode(g2u($res)));
    }

    /***
     * @param $id 退票审核单LISTID
     * @return array
     */
    protected function getRecycleInfo($id) {

        //当前用户编号
        $uid = intval($_SESSION['uinfo']['uid']);

        //当前城市编号
        $city_id = intval($this->channelid);


        $sql = <<<Recycle_SQL
        SELECT A.ID,A.APPLY_USER,D.NAME AS USERNAME,A.APPLY_TIME,A.STATUS,A.CITY_ID,A.LIST_ID,
                B.RECEIPTNO AS RECEIPT_NO,B.INVOICE_NO,B.REALNAME,B.MOBILENO,B.INVOICE_NO,B.INVOICE_STATUS,B.PRJ_ID,
                C.CONTRACT,C.PROJECTNAME
                FROM ERP_INVOICE_RECYCLE_DETAIL A
                LEFT JOIN ERP_CARDMEMBER B ON A.MID=B.ID
                LEFT JOIN ERP_PROJECT C ON B.PRJ_ID=C.ID
                LEFT JOIN ERP_USERS D ON A.APPLY_USER = D.ID
                WHERE A.STATUS != 6 AND A.LIST_ID = %d
Recycle_SQL;

        $sql = sprintf($sql,$id);

        $recycleInfo = M()->query($sql);
        return $recycleInfo;
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

        //实际操作
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