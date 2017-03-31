<?php
/**
 * 会员申请减免
 * Created by PhpStorm.
 * User: superkemi
 */

class MemberDiscountAction extends ExtendAction {
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
            'member_discount_list' => array(
                'name' => 'member-discount-list',
                'text' => '减免会员列表'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        $this->title = '会员减免';
        $this->processTitle = '关于会员减免申请的审核';

        //recordId
        //解决第一次调用数据展现问题（创建工作流）
        if(!$this->recordId)
            $this->recordId = intval($_REQUEST['RECORDID']);

        Vendor('Oms.Flows.Flow');
        Vendor('Oms.Flows.MemberDiscount');
        $this->workFlow = new Flow('MemberDiscount');
        $this->memberdiscount = new MemberDiscount();

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

        $memberDiscountInfo = $this->memberDiscountInfo($this->recordId);

        $viewData['memberDiscountInfo'] = $memberDiscountInfo;

        //发票状态
        $status_arr = D("Member")->get_conf_all_status_remark();
        $viewData['invoiceStatus'] = $status_arr;

        //换发票信息
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
    protected function memberDiscountInfo($id) {

        //当前用户编号
        $uid = intval($_SESSION['uinfo']['uid']);

        //当前城市编号
        $city_id = intval($this->channelid);

        $sql = <<<MEMBERDISCOUNT_SQL
            SELECT B.ID,A.PRJ_NAME,A.REALNAME,A.MOBILENO,A.INVOICE_STATUS,A.ADD_UID ADD_USER,A.PAID_MONEY,A.UNPAID_MONEY,A.TOTAL_PRICE,A.CITY_ID,A.PRJ_ID,B.REDUCE_MONEY,B.APPLY_TIME,
            B.APPLY_USER,B.STATUS,B.MID,B.LIST_ID
            FROM ERP_MEMBER_DISCOUNT_DETAIL B
            LEFT JOIN ERP_CARDMEMBER A ON A.ID=B.MID
            WHERE B.LIST_ID = %d
MEMBERDISCOUNT_SQL;

        $sql = sprintf($sql,$id);

        $memberDiscountInfo = M()->query($sql);
        return $memberDiscountInfo;
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