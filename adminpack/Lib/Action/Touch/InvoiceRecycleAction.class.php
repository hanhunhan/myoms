<?php
/**
 * ��Ա������Ʊ
 * Created by PhpStorm.
 * User: superkemi
 */

class InvoiceRecycleAction extends ExtendAction {
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
            'recycle_invoice_list' => array(
                'name' => 'recycle-invoice-list',
                'text' => '��Ʊ�б�'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������',
            )
        );

        $this->title = '��Ʊ����';
        $this->processTitle = '���ڻ�Ա��Ʊ��������';

        //recordId
        //�����һ�ε�������չ�����⣨������������
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

        $recycleInfo = $this->getRecycleInfo($this->recordId);
        $viewData['recycleInfo'] = $recycleInfo;

        //��Ʊ״̬
        $status_arr = D("Member")->get_conf_all_status_remark();
        $viewData['invoiceStatus'] = $status_arr;

        //��Ʊ��Ϣ
        $this->assign('viewData', $viewData);
        $this->assign("recordId",$this->recordId);
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        //�˵�
        $this->assign('menu', $this->menu);
        $this->display('process');
    }

    /**
    +----------------------------------------------------------
     * ������Ʊ����
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
     * @param $id ��Ʊ��˵�LISTID
     * @return array
     */
    protected function getRecycleInfo($id) {

        //��ǰ�û����
        $uid = intval($_SESSION['uinfo']['uid']);

        //��ǰ���б��
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

        //ʵ�ʲ���
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