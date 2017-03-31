<?php
/**
 * ��Ա�����˿�
 * Created by PhpStorm.
 * User: superkemi
 */

class ChangeInvoiceAction extends ExtendAction {
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
            'change_invoice_list' => array(
                'name' => 'change-invoice-list',
                'text' => '����Ʊ�б�'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
        );

        $this->title = '��Ա����Ʊ';
        $this->processTitle = '���ڻ�Ա����Ʊ��������';

        //recordId
        //�����һ�ε�������չ�����⣨������������
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

        $changeInvoiceInfo = $this->changeInvoiceInfo($this->recordId);

        $viewData['changeInvoiceInfo'] = $changeInvoiceInfo;

        $caseId = $changeInvoiceInfo[0]['CASE_ID'];

        //��Ʊ״̬
        $status_arr = D("Member")->get_conf_all_status_remark();
        $viewData['invoiceStatus'] = $status_arr;

        //����Ʊ��Ϣ
        $this->assign('viewData', $viewData);
        $this->assign('CASEID', $caseId);
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        //�˵�
        $this->assign('menu', $this->menu);
        $this->display('process');
    }


    /***
     * @param $id ��Ʊ��˵�LISTID
     * @return array
     */
    protected function changeInvoiceInfo($id) {

        //��ǰ�û����
        $uid = intval($_SESSION['uinfo']['uid']);

        //��ǰ���б��
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