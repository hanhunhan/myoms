<?php
import('Org.Io.Mylog');

/**
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/4/15
 * Time: 12:18
 */
class ExcelRow {
    /**
     * ��ͬ����
     */
    const CONTRACT_COLUMN = 'A';

    /**
     * ��Ŀ��
     */
    const PROJECT_NAME_COLUMN = 'B';

    /**
     * С����
     */
    const SUM_COLUMN = 'AS';

    /**
     * �ۺ�����
     */
    //const EVAL_COLUMN = 'AG';

    /**
     * ֧��������������
     */
    const THIRD_PARTY_COLUMN = 'P';

    /**
     * �ɹ�����
     */
    const COST_PURCHASE_APPLY = 1;

    /**
     * �ɹ���ͬǩ��
     */
    const COST_PURCHASE_CONTRACTED = 2;

    /**
     * Excel�������������ǰ׺
     */
    const FEE_DESC_PREFIX = '�������������ݴ�Excel����-1';

    /**
     * �ɹ�����ͨ��
     */
    const COST_PURCHASE_REIMED = 4;

    /**
     * ������Ŀ���
     */
    const SCALETYPE_DS = 1;
    /**
     * ���ҷ��ճ���Ŀ���
     */
    const SCALETYPE_FWFSC = 8;

    /**
     * Ĭ�ϵĽ�ֹ����
     * @var string
     */
    protected $defaultEndDate = '2016-06-07 00:00:00';


    protected $scaleType = 1;

    /**
     * ��ѯ�ѱ������ݵ���������
     * @var string
     */
    protected $boundaryDate = '2016-06-07 00:00:00';

    protected $dataMap = array(
        'A' => '��ͬ��',
        'B' => 'ԭ��Ŀ����',
        'C' => '��ͬǩԼ����',
        'D' => '����-�̳�',
        'E' => '��С��',
        'F' => 'д��¥',
        'G' => '����Ʒ',
        'H' => '��չ��',
        'I' => '��ҳ',
        'J' => 'Xչ��',
        'K' => '��˾Ա��',
        'L' => '��ְ��Ա����',
        'M' => 'ҵ�����',
        'N' => '��������',
        'O' => 'ʵ��Ӧ��',
        'P' => '֧������������',
        'Q' => '����ֳ�',
        'R' => '�н��',
        'S' => '�ϴ���',
        'T' => '�н����',
        'U' => '��������',
        'V' => '���ŷ�',
        'W' => '�绰��',
        'X' => '����',
        'Y' => '��ҵ����',
        'Z' => '�ͻ�',
        'AA' =>'����',
        'AB' =>'����',
        'AC' =>'LED',
        'AD' =>'����/����',
        'AE' =>'��̨',
        'AF' =>'��ֽ/��־',
        'AG' =>'��ͳ�',
        'AH' => '���⳵',
        'AI' => '����ѣ����',
        'AJ' => '����ů����',
        'AK' => '����ʳƷ��',
        'AL' => 'SEO/SEM�ƹ�',
        'AM' => '�ɽ�����',
        'AN' => '�ڲ����',
        'AO' => '�ⲿ����',
        'AP' => 'POS������',
        'AQ' => '˰��',
        'AR' => '����',
        'AS' => 'С��',
    );
    protected $columns = array();

    protected $feeIDs = array(
        'D' => 45, //����-�̳�
        'E' => 46, //��С��
        'F' => 47, //��д��¥
        'G' => 65, //����Ʒ
        'H' => 66, //��չ��
        'I' => 67, //'��ҳ',
        'J' => 68, //'Xչ�� ',
        'K' => 57,//'��˾Ա��',
        'L' => 58, //'��ְ��Ա����',
        'M' => 60, //'ҵ�����',
        'N' => 61, //'��������',
        'O' => 62, //'ʵ��Ӧ��',
        'P' => 80, //'֧������������',
        'Q' => 82, //'����ֳ�',
        'R' => 39, //'�н��',
        'S' => 84, //'�ϴ���',
        'T' => 86, //'�н����',
        'U' => 87, //'��������',
        'V' => 41, //'���ŷ�',
        'W' => 42, //'�绰��',
        'X' => 76, //'����',
        'Y' => 77, //'��ҵ����',
        'Z' => 78, //'�ͻ�',
        'AA' =>79, //'����',
        'AB' =>70, //'����',
        'AC' =>71, //'LED',
        'AD' =>72, //'����/����',
        'AE' =>73, //'��̨',
        'AF' =>74, //'��ֽ/��־',
        'AG' =>49, //'��ͳ�',
        'AH' =>50, //'���⳵',
        'AI' =>51, //'����ѣ����',
        'AJ' =>54, //'����ů����',
        'AK' =>55, //'����ʳƷ��',
        'AL' =>53, //'SEO/SEM�ƹ�',
        'AM' =>89, //'�ɽ�����',
        'AN' =>91, //'�ڲ����',
        'AO' =>93, //'�ⲿ����',
        'AP' =>95, //'POS������',
        'AQ' =>96, //'˰��',
        'AR' =>97, //'����',
    );

    private static $instance = null;
    protected $project = null;
    protected $dsCase = null;
    protected $cityID = null;
    protected $cityName = null;
    protected $user = null;
    protected $budget = null;

    private function __construct() {
        $this->columns = array();
    }

    public static function instance() {
        if (self::$instance == null) {
            self::$instance = new ExcelRow();
        }
        self::$instance->clear();
        return self::$instance;
    }

    public function clear() {
        $this->columns = array();
    }

    private function getToDate() {
        if (strtotime($this->budget['TODATE']) > strtotime(date('Y-m-d H:i:s'))) {
//            return '2016-03-31 00:00:00';
            return $this->defaultEndDate;
        } else {
            return $this->budget['TODATE'];
        }
    }

    public function setColumn($key, $val) {
        if (in_array($key, $this->needTransWords)) {
            $val = u2g($val);
        }

        // С����
        if ($key == self::SUM_COLUMN) {
            $val = 0;
            for ($i = 'E'; $i <= 'S'; ++$i) {
                $val += floatval($this->columns[$i]);
            }
        }

        // �ۺ�����
//        if ($key == self::EVAL_COLUMN) {
//            if ($val[0] == '=') {
//                eval('$val' . $val . ';');
//            }
//        }
        $this->columns[$key] = $val;
    }

    public function getColumn($key) {
        return $this->columns[$key];
    }

    /**
     * ��ȡ��Ŀ��Ϣ
     * @throws Exception
     */
    private function getProjectInfo() {
        $this->getProject();  // ��Ŀ��Ϣ
        $this->getCase();  // ������Ϣ
        $this->getUser();  // �û���Ϣ
        $this->getCaseBudget();  // ��ĿԤ��
    }

    /**
     * �����ݴ������ݿ���
     * @param $city
     * @throws Exception
     */
    public function saveDataToDB($city) {
        try {
            $this->cityID = $city;
            $this->cityName = $this->cities[$city];
            $this->getProjectInfo();
            $feeLetters = array_keys($this->feeIDs);

            foreach ($this->columns as $letter => $value) {
                if (in_array($letter, $feeLetters)) {
                    Mylog::write(sprintf('��������Ϊ%s��������ĿΪ%s, ����IDΪ%s', $this->dataMap[$letter], floatval($this->columns[$letter]), $this->feeIDs[$letter]));
                    $this->purchaseAndReimbursement($letter, $value, $this->feeIDs[$letter]);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $toCase
     * @return array|mixed
     * @throws Exception
     */
    private function projectMemberInfo($toCase){

        $projectInfo = array();

        try {
            $sql = "SELECT PROJECTNAME,P.ID AS ID,C.SCALETYPE FROM ERP_PROJECT P INNER JOIN ERP_CASE C ON P.ID = C.PROJECT_ID AND C.SCALETYPE =1 AND C.ID = " . $toCase;
            $projectInfo = D('Erp_project')->query($sql);

            if (is_array($projectInfo) && count($projectInfo)) {
                $scaleName = "����";
                $this->scaleType = $projectInfo[0]['SCALETYPE'];
                MyLog::write(sprintf('����ĿIDΪ%s����Ŀ����Ϊ%s,ҵ������Ϊ%s', $projectInfo[0]['ID'], $projectInfo[0]['PROJECTNAME'],$scaleName));
            } else {
                throw_exception(sprintf('��Ŀ������', 1));
            }

        } catch (Exception $e) {
            throw $e;
        }

        return $projectInfo;
    }

    /**
     * @param $fromCase
     * @param $receiptNo
     * @return array|mixed
     * @throws Exception
     */
    private function getMemberInfo($fromCase,$receiptNo){
        $memberInfo = array();

        try {
            $sql = "SELECT REALNAME,ID,INVOICE_STATUS,INVOICE_NO FROM ERP_CARDMEMBER M WHERE M.RECEIPTNO = '$receiptNo' AND STATUS = 1 AND CASE_ID = $fromCase";
            //echo $sql;
            $memberInfo = D('Erp_project')->query($sql);

            if (is_array($memberInfo) && count($memberInfo)==1) {
                MyLog::write(sprintf('�û�ԱΪ%s����ԱIDΪ%s����Ա��Ʊ״̬Ϊ%s', $memberInfo[0]['REALNAME'], $memberInfo[0]['ID'], $memberInfo[0]['INVOICE_STATUS']));
            } else {
                throw_exception(sprintf('��Ա�վݱ��Ϊ%s�������ڣ�����ڶ�����', $receiptNo));
            }
        } catch (Exception $e) {
            throw $e;
        }

        return $memberInfo;
    }

    /**
     * @param $receiptNo
     * @param $city
     * @param $fromCase
     * @param $toCase
     * @throws Exception
     */
    public function transMember($receiptNo,$city,$fromCase,$toCase){
        try {

            D()->startTrans();

            //��ȡ�û���Ϣ
            $memberInfo = $this->getMemberInfo($fromCase,$receiptNo);

            //��ȡ��Ŀ��Ϣ
            $projectInfo = $this->projectMemberInfo($toCase);

            //��Ŀ����
            $projectName = $projectInfo[0]['PROJECTNAME'];
            //Ŀ����ĿID
            $prjId = $projectInfo[0]['ID'];
            //��ԱID
            $memberId = $memberInfo[0]['ID'];
            //��Ա״̬
            $memberInvoiceStatus = $memberInfo[0]['INVOICE_STATUS'];
            //��Ա��Ʊ���
            $invoice_no = $memberInfo[0]['INVOICE_NO'];


            echo $projectName . '----' . $prjId . '----' . $memberId . '----' . $memberInvoiceStatus . '<br />';

            if($memberInfo){
                //����erp_cardmember CASEID  projectName  prj_id
                $update_member = array();
                $update_member['PRJ_ID'] = $prjId;
                $update_member['CASE_ID'] = $toCase;
                $update_member['PRJ_NAME'] = $projectName;
                $update_member_ret = M('Erp_cardmember')->where('ID='.$memberId)->save($update_member);

                //����erp_income_list ����
                $update_income_list = array();
                $update_income_list['CASE_ID'] = $toCase;
                $update_income_list_ret = M('Erp_income_list')->where('CASE_TYPE=1 AND INCOME_FROM IN(1,2,3,4,5) AND ENTITY_ID = '.$memberId)->save($update_income_list);

                if($memberInvoiceStatus == 2 || $memberInvoiceStatus == 3) {
                    //�����Ʊ�ˣ�����erp_billing_record
                    $update_billing_arr = array();
                    $update_billing_arr['CASE_ID'] = $toCase;
                    //���⴦��
                    //$invoice_no = '00' . $invoice_no;
                    $update_billing_ret = M('Erp_billing_record')->where("INVOICE_NO = '{$invoice_no}'")->save($update_billing_arr);
                    //echo M('Erp_billing_record')->getLastSql();

                    //�����Ʊ�ˣ�����erp_cost_list
                    $update_cost_arr = array();
                    $update_cost_arr['CASE_ID'] = $toCase;
                    $update_cost_arr['PROJECT_ID'] = $prjId;
                    $update_cost_ret = M('Erp_cost_list')->where("CASE_ID = {$fromCase} AND ENTITY_ID = $memberId AND EXPEND_FROM = 28")->save($update_cost_arr);
                    //echo M('Erp_cost_list')->getLastSql();

                    //�����Ʊ�ˣ�֪ͨ��ͬϵͳ�޸�
                    //ֱ�Ӻ�ͬϵͳ�޸�
                }
            }

            var_dump($update_member_ret);
            var_dump($update_income_list_ret);
            var_dump($update_cost_ret);
            var_dump($update_billing_ret);


            if($update_member_ret && $update_income_list_ret && ($update_billing_ret!==false) && ($update_cost_ret!==false)) {
                MyLog::write("�ɹ�");
                D()->commit();
            }
            else{
                MyLog::write("ʧ��");
                D()->rollback();
            }
        } catch (Exception $e) {
            throw $e;
        }
    }


    /**
     * ������Ŀ
     * @throws Exception
     */
    private function getProject() {

        try {
            $where = "NLS_UPPER(CONTRACT) = '" . strtoupper(trim($this->columns['A'])) . "' AND CITY_ID = {$this->cityID} AND PSTATUS=3 AND STATUS<>2";

            D('erp_project')->where($where)->find();

            $this->project = D('erp_project')->where($where)->find();

            if (is_array($this->project) && count($this->project)) {
                MyLog::write(sprintf('����Ŀ��ͬ��Ϊ%s����Ŀ��Ϊ%s����Ŀ������Ϊ%s', $this->project['CONTRACT'], $this->project['PROJECTNAME'], $this->project['CUSER']));
            } else {
                throw_exception(sprintf('��ͬ��Ϊ%s����Ŀ��Ϊ%s����Ŀ������', $this->columns[self::CONTRACT_COLUMN], $this->columns[self::PROJECT_NAME_COLUMN]));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getCaseBudget() {
        try {
            $where = "CASE_ID = {$this->dsCase['ID']}";
            $this->budget = D('erp_prjbudget')->field("ID, to_char(TODATE, 'YYYY-MM-DD HH24:MI:SS') TODATE")->where($where)->find();
            if (is_array($this->budget) && count($this->budget)) {
                MyLog::write(sprintf('��erp_prjbudget���õ�����Ŀ��Ԥ����Ϊ%s����Ŀ��ֹ������%s', $this->budget['ID'], $this->budget['TODATE']));
            } else {
                throw_exception('��erp_prjbudget����ȡԤ������');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * ������
     * @throws Exception
     */
    private function getCase() {
        try {
            $where = "PROJECT_ID = {$this->project['ID']} AND (SCALETYPE = 1 OR SCALETYPE = 8)";
            $this->dsCase = D('erp_case')->where($where)->find();

            //echo D('erp_case')->getLastSql();
            if($this->dsCase['SCALETYPE']==1){
                $scaleName = '����';
            }else if($this->dsCase['SCALETYPE']==8){
                $scaleName = '���ҷ��ճ�';
            }
            $this->scaleType = $this->dsCase['SCALETYPE'];

            if (is_array($this->dsCase) && count($this->dsCase)) {
                MyLog::write(sprintf('�ú�ͬ�´��ڵ��̻���ҷ��ճ���Ŀ�����Ϊ%s,ҵ������Ϊ%s', $this->dsCase['ID'],$scaleName));
            } else {
                throw_exception('�ú�ͬ�²����ڵ��̻���ҷ��ճ���Ŀ');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getUser() {
        try {
            $this->user = D('erp_users')->where("ID = {$this->project['CUSER']}")->find();
            if (is_array($this->project) && count($this->project)) {
                MyLog::write(sprintf('��Ŀ������%s�����ű��%s', $this->user['NAME'], $this->user['DEPTID']));
            } else {
                throw_exception(sprintf('��Ŀ��������'));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getUsedFee($feeID) {
        $sql = "
            SELECT SUM(FEE) TOTAL
            FROM erp_cost_list
            WHERE CASE_ID = {$this->dsCase['ID']}
            AND STATUS = 4
            AND CITY_ID = {$this->cityID}
            AND FEE_ID = {$feeID}
            AND to_char(OCCUR_TIME, 'YYYY-MM-DD HH24:MI:SS') < '{$this->boundaryDate}'
        ";

        $usedFee = 0;
        $result = D()->query($sql);
        if (is_array($result) && count($result)) {
            $usedFee = $result[0]['TOTAL'];
        } else if ($result === false) {
            throw_exception(sprintf('��erp_cost_list������ϵͳ���ѱ������ó���CASE_ID=%s��FEE_ID=%s', $this->dsCase['ID'], $feeID));
        }

        return $usedFee;
    }

    private function purchaseAndReimbursement($column, $value, $feeID) {
        try {
            $usedFee = $this->getUsedFee($feeID);
            $purchaseFee = floatval($value) - floatval($usedFee);
            Mylog::write(sprintf('ʵ����Ҫ����ķ�����ĿΪ%s', $purchaseFee));

            /**��ֵ���ɵ���**/
            if ($purchaseFee == 0) {
                Mylog::write(sprintf('ʵ����Ҫ����ķ���<����>0�����赼��'));
                return;
            }
            if ($column == self::THIRD_PARTY_COLUMN) {
                $isFundPool = 1;
            } else {
                $isFundPool = 0;
            }
            D()->startTrans();
            $requisitionID = $this->savePurchaseRequisition();  // ��Ӳɹ�����
            $reimListID = $this->saveReimList($purchaseFee);  // ��ӱ����б�
            $contractID = $this->savePurchaseContract($reimListID);  // ��Ӳɹ���ͬ
            $purchaseListID = $this->savePurchaseList($requisitionID, $purchaseFee, $feeID, $contractID, $isFundPool);  // ��Ӳɹ���ϸ
            $reimDetailID = $this->saveReimDetail($purchaseListID, $purchaseFee, $reimListID, $feeID);  // ��ӱ�����ϸ��
            // ��Ӳɹ�����
            $costApplyID = $this->saveCostList($requisitionID, $purchaseListID, $purchaseFee, self::FEE_DESC_PREFIX . '-�ɹ�����', $feeID, self::COST_PURCHASE_APPLY, $isFundPool, 1);
            // ��Ӳɹ���ͬǩ��
            $costContractedID = $this->saveCostList($requisitionID, $purchaseListID, $purchaseFee, self::FEE_DESC_PREFIX . '-�ɹ���ͬǩ��', $feeID, self::COST_PURCHASE_CONTRACTED, $isFundPool, 2);
            // ��ӱ���ͨ��
            $costReimedID = $this->saveCostList($requisitionID, $purchaseListID, $purchaseFee, self::FEE_DESC_PREFIX . '-�ɹ�����ͨ��', $feeID, self::COST_PURCHASE_REIMED, $isFundPool, 4);


            if ($requisitionID !== false
                && $reimListID !== false
                && $contractID !== false
                && $purchaseListID !== false
                && $reimDetailID !== false
                && $costApplyID !== false
                && $costContractedID !== false
                && $costReimedID !== false
            ) {
                D()->commit();
            } else {
                D()->rollback();
                throw_exception('���̳��ִ��󣬻ع�����');
            }
        } catch (Exception $e) {
            D()->rollback();
            throw $e;
        }
    }

    private function savePurchaseRequisition() {
        $data = array(
            'CASE_ID' => $this->dsCase['ID'],
            'REASON' => self::FEE_DESC_PREFIX,
            'USER_ID' => $this->user['ID'],
            'DEPT_ID' => $this->user['DEPTID'],
            'APPLY_TIME' => $this->getToDate(),
            'END_TIME' => $this->getToDate(),
            'PRJ_ID' => $this->project['ID'],
            'TYPE' => 1,
            'CITY_ID' => $this->cityID,
            'STATUS' => 4
        );

        $insertedID = D('erp_purchase_requisition')->add($data);
        if ($insertedID !== false && $insertedID > 0) {
            MyLog::write(sprintf('��erp_purchase_requisition���������ݳɹ���IDΪ%s', $insertedID));
        } else {
            throw_exception('��erp_purchase_requisition����������ʧ��');
        }

        return $insertedID;
    }

    private function saveReimList($purchaseFee) {
        $data = array(
            'STATUS' => 2,
            'REIM_TIME' => $this->getToDate(),
            'REIM_UID' => $this->user['ID'],
            'TYPE' => 1,
            'APPLY_UID' => $this->user['ID'],
            'APPLY_TRUENAME' => $this->user['NAME'],
            'APPLY_TIME' => $this->getToDate(),
            'CITY_ID' => $this->cityID,
            'AMOUNT' => $purchaseFee
        );

        $insertedID = D('erp_reimbursement_list')->add($data);
        if ($insertedID !== false && $insertedID > 0) {
            MyLog::write(sprintf('��erp_reimbursement_list���������ݳɹ���IDΪ%s', $insertedID));
        } else {
            throw_exception('��erp_reimbursement_list����������');
        }

        return $insertedID;
    }

    private function savePurchaseContract($reimListID) {
        $data = array(
            'CONTRACTID' => '',
            'PROMOTER' => $this->user['ID'],
            'TYPE' => 1, // ����
            'SIGINGTIME' => $this->getToDate(),
            'REIM_ID' => $reimListID,
            'ISSIGN' => 2,
            'CITY_ID' => $this->cityID
        );

        $insertedID = M('erp_contract')->add($data);
        if ($insertedID !== false && $insertedID > 0) {
            MyLog::write(sprintf('��erp_contract���������ݳɹ���IDΪ%s', $insertedID));
        } else {
            throw_exception('��erp_contract����������ʧ��');
        }

        return $insertedID;
    }

    private function savePurchaseList($purReqID, $purchaseFee, $feeID, $contractID, $isFundPool = 0) {
        $data = array(
            'PRODUCT_NAME' => '����������Excel�ɹ�����-1',
            'PR_ID' => $purReqID,
            'NUM_LIMIT' => 1,  // ��������
            'PRICE_LIMIT' => $purchaseFee,  // �۸�����
            'PRICE' => $purchaseFee,  // �۸�
            'FEE_ID' => $feeID,
            'IS_FUNDPOOL' => $isFundPool, // �Ƿ��ʽ����Ŀ
            'IS_KF' => 1, // �Ƿ�۷ǣ�Ĭ��1
            'P_ID' => 0,
            'TYPE' => 1, // �ɹ����� 1ҵ��ɹ���2���ڲɹ�
            'CONTRACT_ID' => $contractID,
            'CASE_ID' => $this->dsCase['ID'],
            'CITY_ID' => $this->cityID,
            'STATUS' => 2,
            'NUM' => 1
        );

        $insertedID = M('erp_purchase_list')->add($data);
        if ($insertedID !== false && $insertedID > 0) {
            MyLog::write(sprintf('��erp_purchase_list���������ݳɹ���IDΪ%s', $insertedID));
        } else {
            throw_exception('��erp_purchase_list����������ʧ��');
        }

        return $insertedID;
    }

    private function saveReimDetail($purchaseListID, $purchaseFee, $reimListID, $feeID) {
        $data = array(
            'CITY_ID' => $this->cityID,
            'CASE_ID' => $this->dsCase['ID'],
            'BUSINESS_ID' => $purchaseListID,  // ����ҵ��ID
            'MONEY' => $purchaseFee,
            'STATUS' => 1,  // �ѱ���
            'APPLY_TIME' => $this->getToDate(),
            'ISFUNDPOOL' => 0,  // todo
            'ISKF' => 1, // Ĭ�Ͽ۷�
            'TYPE' => 1, // ��������
            'LIST_ID' => $reimListID, // ���������
            'FEE_ID' => $feeID
        );

        $insertedID = M('erp_reimbursement_detail')->add($data);
        if ($insertedID !== false && $insertedID > 0) {
            MyLog::write(sprintf('��erp_reimbursement_detail���������ݳɹ���IDΪ%s', $insertedID));
        } else {
            throw_exception('��erp_reimbursement_detail����������ʧ��');
        }

        return $insertedID;
    }

    private function saveCostList($purReqID, $purchaseListID, $purchaseFee, $feeRemark, $feeID, $type, $isFundPool = 0, $status) {
        //���ɱ�������Ӽ�¼
        $data['CASE_ID'] = $this->dsCase['ID'];  //������� �����
        $data['CASE_TYPE'] = $this->scaleType;  // ҵ����Ŀ
        $data['ENTITY_ID'] = $purReqID;  // ҵ��ʵ���� �����
        $data['EXPEND_ID'] = $purchaseListID;  // �ɱ���ϸ��� �����
        $data['ORG_ENTITY_ID'] = $purReqID;  // ҵ��ʵ���� �����
        $data['ORG_EXPEND_ID'] = $purchaseListID;
        $data['FEE'] = $purchaseFee;  // �ɱ���� �����
        $data['ADD_UID'] = $this->user['ID'];  //�����û���� �����
        $data['OCCUR_TIME'] = $this->getToDate();  //����ʱ�� �����
        $data['ISFUNDPOOL'] = $isFundPool;  // �Ƿ��ʽ�أ�0��1�ǣ� �����
        $data['ISKF'] = 1;  // �Ƿ�۷� �����
        $data['FEE_REMARK'] = $feeRemark; //�������� ��ѡ�
        $data['INPUT_TAX'] = 0; // ����˰ ��ѡ�
        $data['FEE_ID'] = $feeID; // �ɱ�����ID �����
        $data['EXPEND_FROM'] = $type; // ��Դ����
        $data['STATUS'] = $status;  //
        $data['PROJECT_ID'] = $this->project['ID'];
        $data['USER_ID'] = $this->user['ID'];
        $data['DEPT_ID'] = $this->user['DEPTID'];
        $data['CITY_ID'] = $this->cityID;

        $insertedID = M('erp_cost_list')->add($data);
        if ($insertedID !== false && $insertedID > 0) {
            MyLog::write(sprintf('��erp_cost_list��%s���������ݳɹ���IDΪ%s', $feeRemark, $insertedID));
        } else {
            throw_exception(sprintf('��erp_cost_list��%s����������ʧ��', $feeRemark));
        }

        return $insertedID;
    }
}