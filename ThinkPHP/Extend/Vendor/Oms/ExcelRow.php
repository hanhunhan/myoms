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
     * 合同号列
     */
    const CONTRACT_COLUMN = 'A';

    /**
     * 项目列
     */
    const PROJECT_NAME_COLUMN = 'B';

    /**
     * 小计列
     */
    const SUM_COLUMN = 'AS';

    /**
     * 折后广告列
     */
    //const EVAL_COLUMN = 'AG';

    /**
     * 支付第三方费用列
     */
    const THIRD_PARTY_COLUMN = 'P';

    /**
     * 采购申请
     */
    const COST_PURCHASE_APPLY = 1;

    /**
     * 采购合同签订
     */
    const COST_PURCHASE_CONTRACTED = 2;

    /**
     * Excel导入费用描述的前缀
     */
    const FEE_DESC_PREFIX = '团立方报销数据从Excel导入-1';

    /**
     * 采购报销通过
     */
    const COST_PURCHASE_REIMED = 4;

    /**
     * 电商项目编号
     */
    const SCALETYPE_DS = 1;
    /**
     * 非我方收筹项目编号
     */
    const SCALETYPE_FWFSC = 8;

    /**
     * 默认的截止日期
     * @var string
     */
    protected $defaultEndDate = '2016-06-07 00:00:00';


    protected $scaleType = 1;

    /**
     * 查询已报销数据的最晚日期
     * @var string
     */
    protected $boundaryDate = '2016-06-07 00:00:00';

    protected $dataMap = array(
        'A' => '合同号',
        'B' => '原项目名称',
        'C' => '合同签约日期',
        'D' => '超市-商场',
        'E' => '进小区',
        'F' => '写字楼',
        'G' => '宣传品',
        'H' => '布展费',
        'I' => '单页',
        'J' => 'X展架',
        'K' => '公司员工',
        'L' => '兼职人员工资',
        'M' => '业务津贴',
        'N' => '其他费用',
        'O' => '实际应酬',
        'P' => '支付第三方费用',
        'Q' => '利润分成',
        'R' => '中介费',
        'S' => '老带新',
        'T' => '中介带看',
        'U' => '渠道带看',
        'V' => '短信费',
        'W' => '电话费',
        'X' => '网友',
        'Y' => '置业顾问',
        'Z' => '客户',
        'AA' =>'其他',
        'AB' =>'大牌',
        'AC' =>'LED',
        'AD' =>'公交/地铁',
        'AE' =>'电台',
        'AF' =>'报纸/杂志',
        'AG' =>'大巴车',
        'AH' => '出租车',
        'AI' => '运输费（载物）',
        'AJ' => '案场暖场费',
        'AK' => '网友食品费',
        'AL' => 'SEO/SEM推广',
        'AM' => '成交奖励',
        'AN' => '内部提成',
        'AO' => '外部奖励',
        'AP' => 'POS手续费',
        'AQ' => '税金',
        'AR' => '其他',
        'AS' => '小计',
    );
    protected $columns = array();

    protected $feeIDs = array(
        'D' => 45, //超市-商场
        'E' => 46, //进小区
        'F' => 47, //进写字楼
        'G' => 65, //宣传品
        'H' => 66, //布展费
        'I' => 67, //'单页',
        'J' => 68, //'X展架 ',
        'K' => 57,//'公司员工',
        'L' => 58, //'兼职人员工资',
        'M' => 60, //'业务津贴',
        'N' => 61, //'其他费用',
        'O' => 62, //'实际应酬',
        'P' => 80, //'支付第三方费用',
        'Q' => 82, //'利润分成',
        'R' => 39, //'中介费',
        'S' => 84, //'老带新',
        'T' => 86, //'中介带看',
        'U' => 87, //'渠道带看',
        'V' => 41, //'短信费',
        'W' => 42, //'电话费',
        'X' => 76, //'网友',
        'Y' => 77, //'置业顾问',
        'Z' => 78, //'客户',
        'AA' =>79, //'其他',
        'AB' =>70, //'大牌',
        'AC' =>71, //'LED',
        'AD' =>72, //'公交/地铁',
        'AE' =>73, //'电台',
        'AF' =>74, //'报纸/杂志',
        'AG' =>49, //'大巴车',
        'AH' =>50, //'出租车',
        'AI' =>51, //'运输费（载物）',
        'AJ' =>54, //'案场暖场费',
        'AK' =>55, //'网友食品费',
        'AL' =>53, //'SEO/SEM推广',
        'AM' =>89, //'成交奖励',
        'AN' =>91, //'内部提成',
        'AO' =>93, //'外部奖励',
        'AP' =>95, //'POS手续费',
        'AQ' =>96, //'税金',
        'AR' =>97, //'其他',
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

        // 小计列
        if ($key == self::SUM_COLUMN) {
            $val = 0;
            for ($i = 'E'; $i <= 'S'; ++$i) {
                $val += floatval($this->columns[$i]);
            }
        }

        // 折后广告列
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
     * 获取项目信息
     * @throws Exception
     */
    private function getProjectInfo() {
        $this->getProject();  // 项目信息
        $this->getCase();  // 案例信息
        $this->getUser();  // 用户信息
        $this->getCaseBudget();  // 项目预算
    }

    /**
     * 将数据存入数据库中
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
                    Mylog::write(sprintf('费用名称为%s，费用数目为%s, 费用ID为%s', $this->dataMap[$letter], floatval($this->columns[$letter]), $this->feeIDs[$letter]));
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
                $scaleName = "电商";
                $this->scaleType = $projectInfo[0]['SCALETYPE'];
                MyLog::write(sprintf('该项目ID为%s，项目名称为%s,业务类型为%s', $projectInfo[0]['ID'], $projectInfo[0]['PROJECTNAME'],$scaleName));
            } else {
                throw_exception(sprintf('项目不存在', 1));
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
                MyLog::write(sprintf('该会员为%s，会员ID为%s，会员发票状态为%s', $memberInfo[0]['REALNAME'], $memberInfo[0]['ID'], $memberInfo[0]['INVOICE_STATUS']));
            } else {
                throw_exception(sprintf('会员收据编号为%s，不存在（或存在多条）', $receiptNo));
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

            //获取用户信息
            $memberInfo = $this->getMemberInfo($fromCase,$receiptNo);

            //获取项目信息
            $projectInfo = $this->projectMemberInfo($toCase);

            //项目名称
            $projectName = $projectInfo[0]['PROJECTNAME'];
            //目标项目ID
            $prjId = $projectInfo[0]['ID'];
            //会员ID
            $memberId = $memberInfo[0]['ID'];
            //会员状态
            $memberInvoiceStatus = $memberInfo[0]['INVOICE_STATUS'];
            //会员发票编号
            $invoice_no = $memberInfo[0]['INVOICE_NO'];


            echo $projectName . '----' . $prjId . '----' . $memberId . '----' . $memberInvoiceStatus . '<br />';

            if($memberInfo){
                //更新erp_cardmember CASEID  projectName  prj_id
                $update_member = array();
                $update_member['PRJ_ID'] = $prjId;
                $update_member['CASE_ID'] = $toCase;
                $update_member['PRJ_NAME'] = $projectName;
                $update_member_ret = M('Erp_cardmember')->where('ID='.$memberId)->save($update_member);

                //更新erp_income_list 数据
                $update_income_list = array();
                $update_income_list['CASE_ID'] = $toCase;
                $update_income_list_ret = M('Erp_income_list')->where('CASE_TYPE=1 AND INCOME_FROM IN(1,2,3,4,5) AND ENTITY_ID = '.$memberId)->save($update_income_list);

                if($memberInvoiceStatus == 2 || $memberInvoiceStatus == 3) {
                    //如果开票了，更新erp_billing_record
                    $update_billing_arr = array();
                    $update_billing_arr['CASE_ID'] = $toCase;
                    //特殊处理
                    //$invoice_no = '00' . $invoice_no;
                    $update_billing_ret = M('Erp_billing_record')->where("INVOICE_NO = '{$invoice_no}'")->save($update_billing_arr);
                    //echo M('Erp_billing_record')->getLastSql();

                    //如果开票了，更新erp_cost_list
                    $update_cost_arr = array();
                    $update_cost_arr['CASE_ID'] = $toCase;
                    $update_cost_arr['PROJECT_ID'] = $prjId;
                    $update_cost_ret = M('Erp_cost_list')->where("CASE_ID = {$fromCase} AND ENTITY_ID = $memberId AND EXPEND_FROM = 28")->save($update_cost_arr);
                    //echo M('Erp_cost_list')->getLastSql();

                    //如果开票了，通知合同系统修改
                    //直接合同系统修改
                }
            }

            var_dump($update_member_ret);
            var_dump($update_income_list_ret);
            var_dump($update_cost_ret);
            var_dump($update_billing_ret);


            if($update_member_ret && $update_income_list_ret && ($update_billing_ret!==false) && ($update_cost_ret!==false)) {
                MyLog::write("成功");
                D()->commit();
            }
            else{
                MyLog::write("失败");
                D()->rollback();
            }
        } catch (Exception $e) {
            throw $e;
        }
    }


    /**
     * 处理项目
     * @throws Exception
     */
    private function getProject() {

        try {
            $where = "NLS_UPPER(CONTRACT) = '" . strtoupper(trim($this->columns['A'])) . "' AND CITY_ID = {$this->cityID} AND PSTATUS=3 AND STATUS<>2";

            D('erp_project')->where($where)->find();

            $this->project = D('erp_project')->where($where)->find();

            if (is_array($this->project) && count($this->project)) {
                MyLog::write(sprintf('该项目合同号为%s，项目名为%s，项目经理编号为%s', $this->project['CONTRACT'], $this->project['PROJECTNAME'], $this->project['CUSER']));
            } else {
                throw_exception(sprintf('合同号为%s，项目名为%s的项目不存在', $this->columns[self::CONTRACT_COLUMN], $this->columns[self::PROJECT_NAME_COLUMN]));
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
                MyLog::write(sprintf('表erp_prjbudget，该电商项目的预算编号为%s，项目终止日期是%s', $this->budget['ID'], $this->budget['TODATE']));
            } else {
                throw_exception('表erp_prjbudget，获取预算表出错');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * 处理案例
     * @throws Exception
     */
    private function getCase() {
        try {
            $where = "PROJECT_ID = {$this->project['ID']} AND (SCALETYPE = 1 OR SCALETYPE = 8)";
            $this->dsCase = D('erp_case')->where($where)->find();

            //echo D('erp_case')->getLastSql();
            if($this->dsCase['SCALETYPE']==1){
                $scaleName = '电商';
            }else if($this->dsCase['SCALETYPE']==8){
                $scaleName = '非我方收筹';
            }
            $this->scaleType = $this->dsCase['SCALETYPE'];

            if (is_array($this->dsCase) && count($this->dsCase)) {
                MyLog::write(sprintf('该合同下存在电商或非我方收筹项目，编号为%s,业务类型为%s', $this->dsCase['ID'],$scaleName));
            } else {
                throw_exception('该合同下不存在电商或非我方收筹项目');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function getUser() {
        try {
            $this->user = D('erp_users')->where("ID = {$this->project['CUSER']}")->find();
            if (is_array($this->project) && count($this->project)) {
                MyLog::write(sprintf('项目经理是%s，部门编号%s', $this->user['NAME'], $this->user['DEPTID']));
            } else {
                throw_exception(sprintf('项目经理不存在'));
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
            throw_exception(sprintf('表erp_cost_list，检索系统中已报销费用出错，CASE_ID=%s，FEE_ID=%s', $this->dsCase['ID'], $feeID));
        }

        return $usedFee;
    }

    private function purchaseAndReimbursement($column, $value, $feeID) {
        try {
            $usedFee = $this->getUsedFee($feeID);
            $purchaseFee = floatval($value) - floatval($usedFee);
            Mylog::write(sprintf('实际需要导入的费用数目为%s', $purchaseFee));

            /**负值依旧导入**/
            if ($purchaseFee == 0) {
                Mylog::write(sprintf('实际需要导入的费用<等于>0，不予导入'));
                return;
            }
            if ($column == self::THIRD_PARTY_COLUMN) {
                $isFundPool = 1;
            } else {
                $isFundPool = 0;
            }
            D()->startTrans();
            $requisitionID = $this->savePurchaseRequisition();  // 添加采购申请
            $reimListID = $this->saveReimList($purchaseFee);  // 添加报销列表
            $contractID = $this->savePurchaseContract($reimListID);  // 添加采购合同
            $purchaseListID = $this->savePurchaseList($requisitionID, $purchaseFee, $feeID, $contractID, $isFundPool);  // 添加采购明细
            $reimDetailID = $this->saveReimDetail($purchaseListID, $purchaseFee, $reimListID, $feeID);  // 添加报销明细表
            // 添加采购申请
            $costApplyID = $this->saveCostList($requisitionID, $purchaseListID, $purchaseFee, self::FEE_DESC_PREFIX . '-采购申请', $feeID, self::COST_PURCHASE_APPLY, $isFundPool, 1);
            // 添加采购合同签订
            $costContractedID = $this->saveCostList($requisitionID, $purchaseListID, $purchaseFee, self::FEE_DESC_PREFIX . '-采购合同签订', $feeID, self::COST_PURCHASE_CONTRACTED, $isFundPool, 2);
            // 添加报销通过
            $costReimedID = $this->saveCostList($requisitionID, $purchaseListID, $purchaseFee, self::FEE_DESC_PREFIX . '-采购报销通过', $feeID, self::COST_PURCHASE_REIMED, $isFundPool, 4);


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
                throw_exception('过程出现错误，回滚事务');
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
            MyLog::write(sprintf('表erp_purchase_requisition，插入数据成功，ID为%s', $insertedID));
        } else {
            throw_exception('表erp_purchase_requisition，插入数据失败');
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
            MyLog::write(sprintf('表erp_reimbursement_list，插入数据成功，ID为%s', $insertedID));
        } else {
            throw_exception('表erp_reimbursement_list，插入数据');
        }

        return $insertedID;
    }

    private function savePurchaseContract($reimListID) {
        $data = array(
            'CONTRACTID' => '',
            'PROMOTER' => $this->user['ID'],
            'TYPE' => 1, // 定额
            'SIGINGTIME' => $this->getToDate(),
            'REIM_ID' => $reimListID,
            'ISSIGN' => 2,
            'CITY_ID' => $this->cityID
        );

        $insertedID = M('erp_contract')->add($data);
        if ($insertedID !== false && $insertedID > 0) {
            MyLog::write(sprintf('表erp_contract，插入数据成功，ID为%s', $insertedID));
        } else {
            throw_exception('表erp_contract，插入数据失败');
        }

        return $insertedID;
    }

    private function savePurchaseList($purReqID, $purchaseFee, $feeID, $contractID, $isFundPool = 0) {
        $data = array(
            'PRODUCT_NAME' => '团立方导入Excel采购数据-1',
            'PR_ID' => $purReqID,
            'NUM_LIMIT' => 1,  // 数量限制
            'PRICE_LIMIT' => $purchaseFee,  // 价格限制
            'PRICE' => $purchaseFee,  // 价格
            'FEE_ID' => $feeID,
            'IS_FUNDPOOL' => $isFundPool, // 是否资金池项目
            'IS_KF' => 1, // 是否扣非，默认1
            'P_ID' => 0,
            'TYPE' => 1, // 采购类型 1业务采购，2大宗采购
            'CONTRACT_ID' => $contractID,
            'CASE_ID' => $this->dsCase['ID'],
            'CITY_ID' => $this->cityID,
            'STATUS' => 2,
            'NUM' => 1
        );

        $insertedID = M('erp_purchase_list')->add($data);
        if ($insertedID !== false && $insertedID > 0) {
            MyLog::write(sprintf('表erp_purchase_list，插入数据成功，ID为%s', $insertedID));
        } else {
            throw_exception('表erp_purchase_list，插入数据失败');
        }

        return $insertedID;
    }

    private function saveReimDetail($purchaseListID, $purchaseFee, $reimListID, $feeID) {
        $data = array(
            'CITY_ID' => $this->cityID,
            'CASE_ID' => $this->dsCase['ID'],
            'BUSINESS_ID' => $purchaseListID,  // 报销业务ID
            'MONEY' => $purchaseFee,
            'STATUS' => 1,  // 已报销
            'APPLY_TIME' => $this->getToDate(),
            'ISFUNDPOOL' => 0,  // todo
            'ISKF' => 1, // 默认扣非
            'TYPE' => 1, // 报销类型
            'LIST_ID' => $reimListID, // 报销单编号
            'FEE_ID' => $feeID
        );

        $insertedID = M('erp_reimbursement_detail')->add($data);
        if ($insertedID !== false && $insertedID > 0) {
            MyLog::write(sprintf('表erp_reimbursement_detail，插入数据成功，ID为%s', $insertedID));
        } else {
            throw_exception('表erp_reimbursement_detail，插入数据失败');
        }

        return $insertedID;
    }

    private function saveCostList($purReqID, $purchaseListID, $purchaseFee, $feeRemark, $feeID, $type, $isFundPool = 0, $status) {
        //往成本表中添加记录
        $data['CASE_ID'] = $this->dsCase['ID'];  //案例编号 【必填】
        $data['CASE_TYPE'] = $this->scaleType;  // 业务项目
        $data['ENTITY_ID'] = $purReqID;  // 业务实体编号 【必填】
        $data['EXPEND_ID'] = $purchaseListID;  // 成本明细编号 【必填】
        $data['ORG_ENTITY_ID'] = $purReqID;  // 业务实体编号 【必填】
        $data['ORG_EXPEND_ID'] = $purchaseListID;
        $data['FEE'] = $purchaseFee;  // 成本金额 【必填】
        $data['ADD_UID'] = $this->user['ID'];  //操作用户编号 【必填】
        $data['OCCUR_TIME'] = $this->getToDate();  //发生时间 【必填】
        $data['ISFUNDPOOL'] = $isFundPool;  // 是否资金池（0否，1是） 【必填】
        $data['ISKF'] = 1;  // 是否扣非 【必填】
        $data['FEE_REMARK'] = $feeRemark; //费用描述 【选填】
        $data['INPUT_TAX'] = 0; // 进项税 【选填】
        $data['FEE_ID'] = $feeID; // 成本类型ID 【必填】
        $data['EXPEND_FROM'] = $type; // 来源类型
        $data['STATUS'] = $status;  //
        $data['PROJECT_ID'] = $this->project['ID'];
        $data['USER_ID'] = $this->user['ID'];
        $data['DEPT_ID'] = $this->user['DEPTID'];
        $data['CITY_ID'] = $this->cityID;

        $insertedID = M('erp_cost_list')->add($data);
        if ($insertedID !== false && $insertedID > 0) {
            MyLog::write(sprintf('表erp_cost_list，%s，插入数据成功，ID为%s', $feeRemark, $insertedID));
        } else {
            throw_exception(sprintf('表erp_cost_list，%s，插入数据失败', $feeRemark));
        }

        return $insertedID;
    }
}