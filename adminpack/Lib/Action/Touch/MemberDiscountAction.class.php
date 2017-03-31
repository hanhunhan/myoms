<?php
/**
 * ��Ա�������
 * Created by PhpStorm.
 * User: superkemi
 */

class MemberDiscountAction extends ExtendAction {
    /*
     * ���캯��
     */
    public function __construct() {
        parent::__construct();

        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '����˵��'
            ),
            'member_discount_list' => array(
                'name' => 'member-discount-list',
                'text' => '�����Ա�б�'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
        );

        $this->title = '��Ա����';
        $this->processTitle = '���ڻ�Ա������������';

        //recordId
        //�����һ�ε�������չ�����⣨������������
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
     * չʾ������Ϣ
     */
    public function process() {
        //process����
        $viewData = array();

        //ת����һ����״̬��
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);
        }
        $this->assignWorkFlows($this->flowId);

        $memberDiscountInfo = $this->memberDiscountInfo($this->recordId);

        $viewData['memberDiscountInfo'] = $memberDiscountInfo;

        //��Ʊ״̬
        $status_arr = D("Member")->get_conf_all_status_remark();
        $viewData['invoiceStatus'] = $status_arr;

        //����Ʊ��Ϣ
        $this->assign('viewData', $viewData);
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        //�˵�
        $this->assign('menu', $this->menu);
        $this->display('process');
    }


    /***
     * @param $id ��Ʊ��˵�LISTID
     * @return array
     */
    protected function memberDiscountInfo($id) {

        //��ǰ�û����
        $uid = intval($_SESSION['uinfo']['uid']);

        //��ǰ���б��
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
     * ����������
     */
    public function opinionFlow() {
        $_REQUEST = u2g($_REQUEST);

        $response = array(
            'status'=>false,
            'message'=>'',
            'data'=>null,
            'url'=>U('Flow/flowList','status=1'),
        );

        //Ȩ���ж�
        if($this->flowId) {
            if (!$this->myTurn) {
                $response['message'] = g2u('�Բ��𣬸ù�������û��Ȩ�޴���');
                die(@json_encode($response));
            }
        }

        //������֤
        $error_str = '';
        if($_REQUEST['flowNext'] && !$_REQUEST['DEAL_USERID']){
            $error_str .= "�ף���ѡ����һ��ת���ˣ�\n";
        }

        if(!trim($_REQUEST['DEAL_INFO'])){
            $error_str .= "�ף�����д���������\n";
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