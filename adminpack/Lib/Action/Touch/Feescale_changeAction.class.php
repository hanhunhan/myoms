<?php
/**
 * ��׼����
 * Created by PhpStorm.
 * User: superkemi
 */

class Feescale_changeAction extends ExtendAction {
    /*
     * ���캯��
     */
    protected $feeScaleType = null;

    public function __construct() {
        parent::__construct();

        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '����˵��'
            ),
            'fee_scale_detail' => array(
                'name' => 'fee-scale-detail',
                'text' => '��׼��ϸ'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
        );

        $this->feeScaleType = array(
            '1'=>'�����շѱ�׼',
            '2'=>'�н�Ӷ��',
            '3'=>'�ⲿ�ɽ�����',
            '4'=>'�н�ɽ���',
            '5'=>'��ҵ���ʳɽ���',
            '6'=>'������',
        );

        $this->title = '��׼����';
        $this->processTitle = '���ڱ�׼������������';

        //recordId
        //�����һ�ε�������չ�����⣨������������
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

        $feeScaleInfo = $this->getFeeScale($this->recordId);

        $viewData['feeScaleInfo'] = $feeScaleInfo;
        //ҵ������
        $viewData['caseType'] = D("ProjectCase")->get_conf_case_type_remark();
        //��׼����
        $viewData['feeScaleType'] = $this->feeScaleType;

        //��Ʊ��Ϣ
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
    protected function getFeeScale($id) {

        //��ǰ�û����
        $uid = intval($_SESSION['uinfo']['uid']);

        //��ǰ���б��
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