<?php
/**
 * 会员申请退款
 * Created by PhpStorm.
 * User: superkemi
 */

class MemberRefundAction extends ExtendAction {
    const PROJECT_REFUND_STAT_SQL = <<<PROJECT_REFUND_STAT_SQL
        SELECT C.PRJ_ID,
               P.PROJECTNAME,
               COUNT(1) TOTAL_COUNT,
               SUM(D.REFUND_MONEY) TOTAL_AMOUNT
        FROM ERP_MEMBER_REFUND_DETAIL D
        LEFT JOIN ERP_CARDMEMBER C ON C.ID = D.MID
        LEFT JOIN ERP_PROJECT P ON P.ID = C.PRJ_ID
        WHERE D.ID IN
            (SELECT DISTINCT(D1.ID)
             FROM ERP_MEMBER_REFUND_DETAIL D1
             WHERE D1.LIST_ID = %d
             AND D1.REFUND_STATUS != 5)
        GROUP BY C.PRJ_ID,
                 P.PROJECTNAME
PROJECT_REFUND_STAT_SQL;

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
            'refund_list' => array(
                'name' => 'refund-list',
                'text' => '退款列表',
            ),
            'refund_total' => array(
                'name' => 'refund-total',
                'text' => '项目汇总',
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        $this->title = '退款申请';
        $this->processTitle = '关于退款申请的审核';

        //recordId
        //解决第一次调用数据展现问题（创建工作流）
        if(!$this->recordId)
            $this->recordId = intval($_REQUEST['RECORDID']);

        Vendor('Oms.Flows.Flow');
        Vendor('Oms.Flows.MemberRefund');
        $this->workFlow = new Flow('MemberRefund');
        $this->memberRefund = new MemberRefund();

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

        $refundInfo = $this->getReundInfo($this->recordId);

        $viewData['refundInfo'] = $refundInfo;

        //发票状态
        $status_arr = D("Member")->get_conf_all_status_remark();
        $viewData['invoiceStatus'] = $status_arr;

        //付款方式
        $member_pay = D('MemberPay');
        $pay_arr = $member_pay->get_conf_pay_type();
        $viewData['payType'] = $pay_arr;

        // 获取退款审核单中相应项目的退款统计状况
        $projectRefundStat = $this->getProjectRefundStat($this->recordId);

        //退款信息
        $this->assign('viewData', $viewData);
        $this->assign('refund_list_id',$this->recordId);
        $this->assign('projectRefundStat', $projectRefundStat);
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        //菜单
        $this->assign('menu', $this->menu);
        $this->display('process');
    }

    /**
    +----------------------------------------------------------
     * 撤销退款申请
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function delete_from_audit_list()
    {
        $res = array(
            'status'=>false,
            'msg'=>'',
            'data'=>null,
            'url'=>'',
        );

        $detail_id = isset($_REQUEST['reund_details_id'])?intval($_REQUEST['reund_details_id']):0;

        if(!$detail_id)
            die(json_encode($res));


        D()->startTrans();

        $flagStatus = $this->memberRefund->delete_details_from_audit_list($detail_id);

        /***更新支付明细状态为未申请退款***/
        $member_refund_model = D('MemberRefund');
        $refund_details_info = array();
        $refund_details_info =
            $member_refund_model->get_refund_detail_by_id($detail_id, array('PAY_ID'));
        //退款信息的支付明细ID
        $pay_id = !empty($refund_details_info['PAY_ID']) ? intval($refund_details_info['PAY_ID']) : 0;

        //付款明细表退款状态
        $member_pay_model = D('MemberPay');
        $pay_refund_status = $member_pay_model->get_conf_refund_status();
        $update_arr['REFUND_STATUS'] = $pay_refund_status['no_refund'];

        $update_num_pay = $member_pay_model->update_info_by_id($pay_id, $update_arr);

        if($flagStatus && $update_num_pay){
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
    protected function getReundInfo($id) {

        //当前用户编号
        $uid = intval($_SESSION['uinfo']['uid']);

        //当前城市编号
        $city_id = intval($this->channelid);

        $sql = <<<Refund_SQL
        SELECT U.NAME AS USERNAME,C.PRJ_ID, C.PRJ_NAME, C.REALNAME, C.MOBILENO, C.INVOICE_STATUS, C.ADD_UID,C.RECEIPTNO,C.INVOICE_NO, P.PAY_TYPE, P.CVV2, P.TRADE_TIME, P.ORIGINAL_MONEY, P.RETRIEVAL,P.MERCHANT_NUMBER, D.ID,D.MID, D.REFUND_MONEY, D.APPLY_UID,D.CREATETIME,D.LIST_ID,D.REFUND_STATUS, D.CITY_ID, D.CONFIRMTIME
                from ERP_MEMBER_REFUND_DETAIL D
                INNER JOIN ERP_CARDMEMBER C ON D.MID = C.ID
                INNER JOIN ERP_MEMBER_PAYMENT P ON D.PAY_ID = P.ID
                LEFT JOIN ERP_USERS U ON D.APPLY_UID = U.ID
                WHERE D.REFUND_STATUS != 5 AND D.LIST_ID = %d
Refund_SQL;

        $sql = sprintf($sql,$id);

        $refundInfo = M()->query($sql);
        return $refundInfo;
    }

    /**
     * 获取退款相应项目的统计数据
     * @param $refundListId
     * @return array
     */
    private function getProjectRefundStat($refundListId) {
        $response = array();
        if (intval($refundListId) > 0) {
            $response = D()->query(sprintf(self::PROJECT_REFUND_STAT_SQL, $refundListId));
            if (is_array($response) && count($response)) {
                $summary = array(
                    'PRJ_ID' => '',
                    'PROJECTNAME' => '合计',
                    'TOTAL_COUNT' => 0,
                    'TOTAL_AMOUNT' => 0.0
                );

                foreach ($response as $v) {
                    $summary['TOTAL_COUNT'] += intval($v['TOTAL_COUNT']);
                    $summary['TOTAL_AMOUNT'] += intval($v['TOTAL_AMOUNT']);
                }
                array_push($response, $summary);
            }

        }



        return $response;
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
            if(!$this->myTurn){
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