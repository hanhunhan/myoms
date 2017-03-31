<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
+------------------------------------------------------------------------------
 * AdvancChaoe���̳���
+------------------------------------------------------------------------------
 * @category   xxx

 * @author    xxx
 * @version   $Id: Form.php  2017-01-17   $
+------------------------------------------------------------------------------
 */
class AdvanceChaoe extends   FlowBase{

    protected $workflow = null;//
    protected $city =null;

    /**
    +----------------------------------------------------------
     * ���캯�� ȡ��ģ�����ʵ��
    +----------------------------------------------------------
     * @access public
    +----------------------------------------------------------
     */
    public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();
        Vendor('Oms.UserLog');
        $this->UserLog = UserLog::Init();

    }
    public function nextstep($flowId){

        return $this->workflow->nextstep($flowId);
    }
    public function createHtml($flowId){//����������

        return $this->workflow->createHtml($flowId);

    }
    public function handleworkflow($data){//��һ��
        $this->model->startTrans();
        $str = $this->workflow->handleworkflow($data);
        if ($str) {
            $this->model->commit();
            if($this->cType=='pc') $result =1;
            else $result =2;//


        } else {
            $this->model->rollback();
            $result = -2;

        }
        return $result;
    }
    public function passWorkflow($data){//ȷ��
        $this->model->startTrans();
        $str = $this->workflow->passWorkflow($data);

        if ($str) {
            $this->model->commit();
            if($this->cType=='pc') $result = 3;
            else  $result = 4;

        } else {
            $this->model->rollback();
            $result = -3;

        }
        return  $result;

    }
    public function notWorkflow($data){//���
        $this->model->startTrans();
        $str = $this->workflow->notWorkflow($data);
        $this->city = M("Erp_flows")->where("ID=".$data['flowId'])->find();

        if ($str) {

            $list['STATUS'] = 0;
            $res = M("Erp_reimbursement_list")->where("ID = ".$data['recordId'])->save($list);
            if($res  ){
                $this->model->commit();
                if($this->cType=='pc') $result = 5;
                else $result = 6;
            }else {
                $this->model->rollback();

                $result = -31;
            }

        } else {
            $this->model->rollback();
            $result = -4;
        }
        return  $result;

    }
    public function finishworkflow($data){//����
        $auth = $this->workflow->flowPassRole($data['flowId']);
        if (!$auth) {
            exit;
        }
        $this->model->startTrans();
        $str = $this->workflow->finishworkflow($data);
        $this->city = M("Erp_flows")->where("ID=".$data['flowId'])->find();
        if ($str) {
            $res = $this->changePayOutRate($data['recordId']);
            if($res){
                $this->model->commit();
                if($this->cType=='pc')  $result = 7;
                else $result = 8;

            }
        } else {
            $this->model->rollback();
            $result = -6;

        }
        return $result;
    }
    public function createworkflow($data){//����������


        $form = $this->workflow->createHtml();

        if ($data['savedata']) {
            $this->model->startTrans();
            $advanceId = !empty($_REQUEST['recordId']) ?$_REQUEST['recordId'] : 0;//��ĿID
            $flow_data['type'] =  'dianzibilichaoe';
            $flow_data['CASEID'] = 0;
            $flow_data['RECORDID'] = $advanceId;
            $flow_data['INFO'] = strip_tags($data['INFO']);
            $flow_data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
            $flow_data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
            $flow_data['DEAL_USERID'] = intval($data['DEAL_USERID']);
            $flow_data['FILES'] = $data['FILES'];
            $flow_data['ISMALL'] =  intval($data['ISMALL']);
            $flow_data['ISPHONE'] =  intval($data['ISPHONE']);
            $list['STATUS'] = 5;
            $res = M("Erp_reimbursement_list")->where("ID = ".$advanceId)->save($list);
            $str = $this->workflow->createworkflow($flow_data);
            if($str && $res){
                $this->model->commit();
                $result = 9;
            }else{
                $this->model->rollback();
                $result = -7;
            }
        }
        return $result;

    }


    /**
     * ����Ĭ���ύ���񣬵ȴ������ȷ��
     * @param $recordId ID
     */
    private function changePayOutRate($advanceId){
        $reim_list_model = D('ReimbursementList');
        $update_num = $reim_list_model->sub_reim_list_to_aduit($advanceId,5);

        //Ŀǰ�����жϺ͵������ʱ���
//        $scaleTypeSql = <<<SQL
//                SELECT  DISTINCT d.list_id, c.scaletype, d.type
//                FROM erp_reimbursement_detail d
//                LEFT JOIN erp_case c ON c.id = d.case_id
//                WHERE d.list_id = {$advanceId} and c.scaletype = 2
//SQL;
//        $dbResult = D()->query($scaleTypeSql);
//        if (notEmptyArray($dbResult)) {
//            // ����Ƿ���ҵ������Ӧ���н�Ӷ������ϸ���޸�
//            $filtedReimList = array();
//            foreach ($dbResult as $item) {
//                if ($item['TYPE'] == 17) {
//                    $filtedReimList []= $item['LIST_ID'];
//                }
//            }
//            if (notEmptyArray($filtedReimList)) {
//                $filtedReimListStr = '(' . implode(',', $filtedReimList) . ')';
//                $update_num = D('erp_commission_reim_detail')->where("REIM_LIST_ID in {$filtedReimListStr}")->save(array(
//                    'STATUS' => 2
//                ));
//            }
//        }
//
//        //��ʵ�ʻؿ����룼Ԥ������ʱ�����̱�����ϵͳ�Զ��������ʱ�������ʵ�ʻؿ������Ԥ�������ǣ����̱�����ϵͳ���������������κ����ݣ�������ȷ�ϵ�ʱ������˹�����
//        $sql = "SELECT D.ID,D.CASE_ID FROM ERP_REIMBURSEMENT_DETAIL D INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID
//						AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID WHERE 1=1 AND LIST_ID = %d AND D.STATUS != 4";
//        $payOutArr = D()->query(sprintf($sql,$advanceId));
//        $prjCaseId = $payOutArr[0]['CASE_ID'];
//        //�ؿ�����<Ԥ������
//        $model = new model();
//        $projectModel = D('Project');
//        $oneBudget = M()->query("
//                SELECT t.*,
//                       to_char(FROMDATE,'yyyy-mm-dd') AS FROMDATE,
//                       to_char(TODATE,'yyyy-mm-dd') AS TODATE
//                FROM ERP_PRJBUDGET t
//                WHERE CASE_ID='$prjCaseId'
//            ");
//            $sumProeit = $oneBudget[0]['SUMPROFIT'];//����_Ԥ��
//            $scaleType = M("Erp_case")->where("ID = ".$prjCaseId)->getField('SCALETYPE');
//            $realIncome = $projectModel->getCaseInvoiceAndReturned($prjCaseId, $scaleType, 2); //�ؿ�����
//            if($realIncome < $sumProeit ) {
//                $loan_limit = D("ProjectCase")->getLoanMoney($prjCaseId, 0, 2);
//                $loan_limit = $loan_limit/100;
//                $update_num = M('Erp_prjbudget')->where("CASE_ID = " . $prjCaseId)->save(array(
//                    'PAYOUT' => $loan_limit
//                ));
//            }

        return  $update_num;
    }




}