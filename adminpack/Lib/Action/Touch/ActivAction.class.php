<?php

/**
 * �����������������
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/2
 * Time: 9:06
 */
class ActivAction extends ExtendAction {
    /**
     * ��������
     */
    const ACTIVITY_CHANGE_FLOWSET = 23;

    /**
     * ��Ŀ�¶�������
     */
    const PROJECT_ACTIVITY_CHANGE_FLOWSET = 24;

    /**
     * ������ѯ���
     */
    const ACTIVITY_INFO_SQL = <<<ACTIVITY_SQL
            SELECT t.ID,
                   BUSINESSCLASS_ID,
                   t.TITLE,
                   t.contract_no,
                   to_char(HTIME,'YYYY-MM-DD') AS HTIME,
                   to_char(HETIME,'YYYY-MM-DD') AS HETIME,
                   APPLICANT,
                   CHARGE,
                   PRINCOME,
                   PERSONAL,
                   PRNUMBER,
                   CONTENT,
                   HMODE,
                   PROFITMARGIN,
                   ADDRESS,
                   DEPT_ID,
                   MYFEE,
                   BUSINESSFEE,
                   HTYPE,
                   BUDGET,
                   t.CONTRACT_NO,
                   d.DEPTNAME,
                   u.NAME AS APPLICANT_NAME,
                   b.YEWU
            FROM ERP_ACTIVITIES t
            LEFT JOIN ERP_DEPT d ON d.ID = t.DEPT_ID
            LEFT JOIN ERP_USERS u ON u.ID = t.APPLICANT
            LEFT JOIN ERP_BUSINESSCLASS b ON b.ID = t.BUSINESSCLASS_ID
            WHERE t.ID = %d
ACTIVITY_SQL;

    /**
     * �Ԥ���ѯ���
     */
    const ACTIVITY_BUDGET_SQL = <<<BUDGET_SQL
            SELECT t.ID,
                   AMOUNT,
                   FEE_ID,
                   MARK,
                   f.NAME AS FEE_NAME
            FROM ERP_ACTIBUDGETFEE t
            LEFT JOIN ERP_FEE f ON f.ID = t.FEE_ID
            WHERE t.ACTIVITIES_ID = %d
              AND t.ISVALID = -1
            ORDER BY t.ID DESC
BUDGET_SQL;

    /**
     * �����������������Ԥ���ѯ���
     */
    const ACTIVITY_CHANGED_BUDGET_SQL = <<<CHANGED_BUGET_SQL
        SELECT t.ID,
               AMOUNT,
               FEE_ID,
               MARK,
               f.NAME AS FEE_NAME
        FROM ERP_ACTIBUDGETFEE t
        LEFT JOIN ERP_FEE f ON f.ID = t.FEE_ID
        WHERE t.ACTIVITIES_ID = %d
            AND (t.ISVALID = -1 OR t.CID = %d)
        ORDER BY t.ID DESC
CHANGED_BUGET_SQL;

    /**
     * ��ȡ���֮�������
     */
    const CHANGED_SQL = <<<CHANGED_SQL
            SELECT VALUEE,
                   ORIVALUEE,
                   ISNEW,
                   BID,
                   COLUMS
            FROM ERP_CHANGELOG
            WHERE TABLEE = %s
              AND BID in %s
              AND CID = %d
CHANGED_SQL;


    protected $hModeValues = array(
        '1' => '����',
        '2' => '����'
    );

    protected $hTypeValues = array(
        '1' => '���̻',
        '2' => 'Ʒ���ƹ�',
        '3' => '��Ŀ�ƹ�',
        '4' => '���ֻ',
        '5' => '��ѵ�',
        '6' => '����'
    );

    /**
     * ��Ҫ�ӻ��ҵ�λ���ֶ�
     */
    protected $needMoneyUnit = array(
        'PRINCOME', 'BUDGET', 'MYFEE', 'BUSINESSFEE'
    );

    /**
     * ��Ҫ��Ӱٷֺŵ��ֶ�
     * @var array
     */
    protected $needPercentMark = array(
        'PROFITMARGIN'
    );

    /**
     * �Ƿ�Ϊ��Ŀ�» true=��
     * @var bool
     */
    protected $isProjectActivity = false;

    /**
     * ���ID
     * @var null
     */
    protected $activityID = null;

    /**
     * �Ƿ�Ϊ������ true=��
     * @var null
     */
    protected $isChanged = null;

    public function __construct() {
        parent::__construct();
        // ��ʼ���˵�
        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '����˵��'
            ),
            'activity_detail' => array(
                'name' => 'activity-detail',
                'text' => '�����'
            ),
            'budget' => array(
                'name' => 'budget',
                'text' => '�Ԥ��'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
        );

        $this->init();
    }

    /**
     * ������ڹ���������Ĭ��Ȩ�޲鿴
     * ������Դ���
     * @param $flowId
     */
    protected function authMyTurn($flowId) {
        if (intval($flowId) > 0) {
            parent::authMyTurn($flowId);
        } else {
            switch($this->flowType) {

                default:
            }

            $this->myTurn = true;
        }
    }

    private function initWithoutFlow() {
        // ���������ID�����ڣ�˵���Ǵ���������
        $this->isChanged = intval($_REQUEST['CHANGE']) == -1;
        if ($this->isChanged) {
            // �������Ὣ����Ĳ���������
            // ������������Ŀ�»���״̬
            $this->CASEID = $_REQUEST['CASEID'];
            $this->recordId = $_REQUEST['RECORDID'];
            $this->activityID = $this->ACTIVID = $_REQUEST['ACTIVID'];
        } else {
            // Ҫ������������Ŀ�»��������
            $this->CASEID = $_REQUEST['prjId'];  // ��Ŀ�б��е��ύ��ť����
            if (empty($this->CASEID)) {
                // ���Ǵ���Ŀ�б���룬�������������ת��������
                $this->CASEID = $_REQUEST['CASEID'];
                $this->activityID = $this->recordId = $_REQUEST['RECORDID'];
            }
        }

        if (intval($this->CASEID) > 0) {
            // ����Ŀ�б����
            if (empty($this->activityID)) {
                $activity = D('Project')->getActivityByProjectId($this->CASEID);
                if (notEmptyArray($activity)) {
                    $this->activityID = $this->recordId = $activity['ID'];
                }
            }
        } else {
            js_alert('��ѡ����Ŀ');
            die();
        }
    }

    /**
     * ��ʼ��
     */
    private function init() {
        if ($this->flowId) {
            $this->isChanged = $this->record['FLOWSETID'] == self::ACTIVITY_CHANGE_FLOWSET || $this->record['FLOWSETID'] == self::PROJECT_ACTIVITY_CHANGE_FLOWSET;
            $this->activityID = !empty($this->record['ACTIVID']) ? $this->record['ACTIVID'] : $this->record['RECORDID'];  // �ID
        } else {
            $this->initWithoutFlow();
        }
        $this->isProjectActivity = $this->isProjectActivity($this->activityID);  // �Ƿ�Ϊ��Ŀ�» true=�� false=��

        // ��ʼ��������
        Vendor('Oms.Flows.Flow');
        if ($this->isProjectActivity) {  // ��Ŀ�»
            if ($this->isChanged) {  // ���
                $this->workFlow = new Flow('ProjectActivityChange');
                $this->flowType = 'xiangmuxiahuodongbiangeng';
                $this->assign('flowType', 'xiangmuxiahuodongbiangeng');
                $this->assign('flowTypeText', '��Ŀ�»������');
                $this->assign('activityTitle', '������Ŀ�»���������������');  // ����������
                $this->assign('CID', $_REQUEST['CID']);  // �����汾��
            } else {  // ����
                $this->workFlow = new Flow('ProjectActivity');
                $this->flowType = 'xiangmuxiahuodong';
                $this->assign('flowType', 'xiangmuxiahuodong');
                $this->assign('flowTypeText', '��Ŀ�»��������');
                $this->assign('activityTitle', '������Ŀ�»�������������');  // ����������
                $this->assign('showCC', true);
            }
        } else {  // �����
            if ($this->isChanged) {  // ���
                $this->workFlow = new Flow('ActivityChange');
                $this->flowType = 'dulihuodongbiangeng';
                $this->assign('flowType', 'dulihuodongbiangeng');
                $this->assign('flowTypeText', '�����������');
                $this->assign('activityTitle', '���ڶ�������������������');  // ����������
                $this->assign('CID', $_REQUEST['CID']);  // �����汾��
            } else {  // ����
                $this->workFlow = new Flow('Activity');
                $this->flowType = 'dulihuodong';
                $this->assign('flowType', 'dulihuodong');
                $this->assign('flowTypeText', '�������������');
                $this->assign('activityTitle', '���ڶ�����������������');  // ����������
                $this->assign('showCC', true);
            }
        }

        if (empty($this->flowId)) {
            $this->assign('CASEID', $this->CASEID);  // ����ĿID��ֵ��CASEID(WTF!!!)
            $this->assign('recordId', $this->recordId);
            $this->assign('ACTIVID', $this->ACTIVID);
            $this->assign('CHANGE', $this->isChanged ? -1 : 0);
        }
    }

    /**
     * ����
     */
    public function process() {
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);
        }

        if ($this->isChanged) {
            $activity = $this->getChangedActivityInfo($this->activityID);  // ��ȡ���Ϣ
            $budget = $this->getChangedBudget($this->activityID);  // ��ȡԤ����Ϣ
        } else {
            $activity = $this->getActivityInfo($this->activityID);  // ��ȡ���Ϣ
            $budget = $this->getBudget($this->activityID);  // ��ȡԤ����Ϣ
        }

        $this->assign('ACTIVID', $this->activityID);  // �ID
        $this->assign('activity', $activity);  // ��Ŀ��Ϣ
        $this->assign('budget', $budget);  // Ԥ����Ϣ

        $this->assignWorkFlows($this->flowId);
        $this->assign('menu', $this->menu);  // �˵�
//        $this->assign('activityTitle', $activity['TITLE']['info']['VAL']);  // �����
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        $this->display('index');
    }

    /**
     * ��ȡ�������Ϣ
     * @param $id
     * @return array
     */
    protected function getActivityInfo($id) {
        $response = array();
        if (intval($id)) {
            $activity = D('erp_activities')->query(sprintf(self::ACTIVITY_INFO_SQL, $id));
            $mergedData = $this->mapChangedActivityData3($this->mapChangedActivityData2(array(), $activity[0]));
            if (!empty($mergedData)) {
                $response = $this->mapActivity($mergedData);
            }
        }

        return $response;
    }

    /**
     * ��ȡ��Ŀ�����Ϣ
     * @param $id
     * @return array
     */
    protected function getChangedActivityInfo($id) {
        $response = array();
        if (intval($id)) {
            $activity = D('erp_activities')->query(sprintf(self::ACTIVITY_INFO_SQL, $id));
            if (is_array($activity) && count($activity)) {
                $mergedData = $this->getChangedActivityData(array(
                    'TABLE' => 'ERP_ACTIVITIES',
                    'BID' => $id,
                    'CID' => $this->recordId
                ), $activity[0]);
                $response = $this->mapActivity($mergedData);
            }
        }

        return $response;
    }

    /**
     * @param $k
     * @param $v
     * @param $changedColumnData
     * @return array
     */
    private function mapActivityColumn($k, $v, $changedColumnData) {
        if (empty($changedColumnData)) {
            $response = $v;
        } else {
            $sign = '[ԭ]';
            if (intval($changedColumnData['ISNEW'] == -1)) {
                $sign = '[��]';
            }
            $response = array(
                'new' => $changedColumnData['VALUEE'],
                'sign' => $sign,
                'old' => $changedColumnData['ORIVALUEE']
            );
        }

        return $response;
    }

    /**
     * ӳ�����������ݣ�������
     * @param $data
     * @return mixed
     */
    protected function mapChangedActivityData3($data) {
        $response = $data;
        foreach($data as $k => $v) {
            // ��ӻ��ҵ�λ
            if (in_array($k, $this->needMoneyUnit)) {
                if (is_array($v['DESC'])) {
                    $response[$k]['DESC']['new'] = floatval($v['DESC']['new']) . self::MONEY_UNIT;
                    $response[$k]['DESC']['old'] = floatval($v['DESC']['old']) . self::MONEY_UNIT;
                } else {
                    $response[$k]['DESC'] = floatval($v['DESC']) . self::MONEY_UNIT;
                }
            }

            // ��Ӱٷֺ�
            if (in_array($k, $this->needPercentMark)) {
                if (is_array($v['DESC'])) {
                    $response[$k]['DESC']['new'] = floatval($v['DESC']['new']) . self::PERCENT_MARK;
                    $response[$k]['DESC']['old'] = floatval($v['DESC']['old']) . self::PERCENT_MARK;
                } else {
                    $response[$k]['DESC'] = floatval($v['DESC']) . self::PERCENT_MARK;
                }
            }

            // �ģʽ
            if ($k == 'HMODE') {
                if (is_array($v['DESC'])) {
                    $response[$k]['DESC']['new'] = $this->hModeValues[$v['DESC']['new']];
                    $response[$k]['DESC']['old'] = $this->hModeValues[$v['DESC']['old']];
                } else {
                    $response[$k]['DESC'] = $this->hModeValues[$v['DESC']];
                }
            }

            // ���ʽ
            if ($k == 'HTYPE') {
                if (is_array($v['DESC'])) {
                    $response[$k]['DESC']['new'] = $this->hTypeValues[$v['DESC']['new']];
                    $response[$k]['DESC']['old'] = $this->hTypeValues[$v['DESC']['old']];
                } else {
                    $response[$k]['DESC'] = $this->hTypeValues[$v['DESC']];
                }
            }

            // �����������ת���ַ���
            if (is_array($response[$k]['DESC'])) {
                $temp = vsprintf('%s<br><span class="text-red">%s%s</span>', $response[$k]['DESC']);
                $response[$k]['DESC'] = $temp;
            }
        }

        return $response;
    }

    /**
     * ӳ�����������ݣ��ڶ���
     * @param $changed
     * @param $oriActivity
     * @return array
     */
    protected function mapChangedActivityData2($changed, $oriActivity) {
        $response = array();
        $indexList = array();
        // ������������ֶ������鱣������
        if (!empty($changed)) {
            foreach($changed as $k => $v) {
                $indexList [$v['COLUMS']]= $k;
            }
        }

        foreach($oriActivity as $k => $v) {
            $response[$k] = array(
                'VAL' => $v,
                'DESC' => ''
            );
            $changedColumnData = array();
            if (in_array($k, array_keys($indexList))) {
                $changedColumnData = $changed[$indexList[$k]];
                $response[$k]['VAL'] = $changedColumnData['VALUEE'];
            }
            $response[$k]['DESC'] = $this->mapActivityColumn($k, $v, $changedColumnData);
        }

        return $response;
    }

    /**
     * ӳ�����������ݣ���һ��
     * @param $data
     * @param $oriActivity
     * @return array
     */
    protected function mapChangedActivityData1($data, $oriActivity) {
        $response = array();

        if (!empty($data) && !empty($oriActivity)) {
            $idStr = $this->getChangedDataBid($oriActivity);
            $changedColumnList = D()->query(vsprintf(self::CHANGED_SQL, array(
                "'{$data['TABLE']}'",
                $idStr,
                $data['CID']
            )));

            $response = $this->mapChangedActivityData2($changedColumnList, $oriActivity);
        }

        return $response;
    }

    /**
     *
     * @param $cost
     * @param $income
     * @return float|int
     */
    private function calcProfitRatio($cost, $income) {
        if (floatval($income) == 0) {
            return 0;
        }

        return round((floatval($income) - floatval($cost)) * 100 / floatval($income), 2);
    }

    /**
    /**
     * ��ȡ��䶯����
     * @param $data
     * @param $oriActivity
     * @return array
     */
    protected function getChangedActivityData($data, $oriActivity) {
        $response = $oriActivity;
        if (is_array($data) && count($data)) {
            $changedActivity = $this->mapChangedActivityData1($data, $oriActivity);
            if ($this->isProjectActivity == false) {
                $changedActivity['PROFITMARGIN']['VAL'] = $this->calcProfitRatio($changedActivity['BUDGET']['VAL'], $changedActivity['PRINCOME']['VAL']);
                if (is_array($changedActivity['PROFITMARGIN']['DESC'])) {
                    $changedActivity['PROFITMARGIN']['DESC']['new'] = $changedActivity['PROFITMARGIN']['VAL'];
                } else {
                    $changedActivity['PROFITMARGIN']['DESC'] = $changedActivity['PROFITMARGIN']['VAL'];
                }
            }

            $response = $this->mapChangedActivityData3($changedActivity);
        }

        return $response;
    }

    /**
     * ��ȡ�Ԥ��
     * @param $activityID
     * @return array
     */
    protected function getBudget($activityID) {
        $response = array();
        if (intval($activityID) > 0) {
            $budget = D()->query(sprintf(self::ACTIVITY_BUDGET_SQL, $activityID));
            if (is_array($budget) && count($budget)) {
                $response = $this->mapBudget($budget);
            }
        }

        return $response;
    }

    /**
     * ��ȡ��������еĻԤ��
     * @param $activityID
     * @return array
     */
    protected function getChangedBudget($activityID) {
        $response = array();
        if (intval($activityID) > 0) {
            $budget = D()->query(sprintf(self::ACTIVITY_CHANGED_BUDGET_SQL, $activityID, $this->recordId));
            if (is_array($budget) && count($budget)) {
                $mergedData = $this->getChangedBudgetData(array(
                    'TABLE' => 'ERP_ACTIBUDGETFEE',
                    'BID' => $activityID,
                    'CID' => $this->recordId
                ), $budget);
                $response = $this->mapBudget($mergedData);
            }
        }

        return $response;
    }

    /**
     * ��ȡ������ݵ�BID
     * @param $data
     * @return String
     */
    protected function getChangedDataBid($data) {
        $temp = array();
        if (is_array($data) && count($data)) {
            if (!empty($data['ID'])) {
                $temp []= $data['ID'];
            } else {
                foreach ($data as $v) {
                    if (!empty($v['ID'])) {
                        $temp [] = $v['ID'];
                    }
                }
            }
        }

        $response = sprintf("(%s)", implode(',', $temp));
        return $response;
    }

    /**
     * �Ա�����ݽ��е�һ���ӹ�
     * @param $data
     * @return array
     */
    protected function mapChangedBudgetData1($data) {
        $response = array();
        foreach($data as $v) {
            if (empty($response[$v['BID']])) {
                $response[$v['BID']] = array();
            }

            if (empty($response[$v['BID']][$v['COLUMS']])) {
                $response[$v['BID']][$v['COLUMS']] = array();
            }

            $response[$v['BID']][$v['COLUMS']]['VALUEE'] = $v['VALUEE'];
            $response[$v['BID']][$v['COLUMS']]['ORIVALUEE'] = $v['ORIVALUEE'];
            $response[$v['BID']][$v['COLUMS']]['ISNEW'] = $v['ISNEW'];
        }

        return $response;
    }

    /**
     * ��ȡԭʼ��δ�ӹ��ı������
     * @param $data
     * @param $oriBudget
     * @return array|mixed
     */
    protected function getRawChangedBudget($data, $oriBudget) {
        $response = array();
        if (is_array($data) && count($data)) {
            $IdStr = $this->getChangedDataBid($oriBudget);
            if (!empty($IdStr)) {
                $sql = <<<CHANGED_SQL
                    SELECT VALUEE,
                           ORIVALUEE,
                           ISNEW,
                           BID,
                           COLUMS
                    FROM ERP_CHANGELOG
                    WHERE TABLEE = %s
                      AND BID in %s
                      AND CID = %d
CHANGED_SQL;
                $response = D()->query(vsprintf($sql, array(
                    "'{$data['TABLE']}'",
                    $IdStr,
                    $data['CID']
                )));
            }
        }
        return $response;
    }

    /**
     * ��ȡ���������б�
     * @return array
     */
    protected function getFeeTypeList() {
        $response = array();
        $sql = <<<FEE_TYPE
            SELECT ID,
                   NAME
            FROM ERP_FEE
            WHERE ISVALID = -1
FEE_TYPE;

        $result = D()->query($sql);
        if (is_array($result) && count($result)) {
            foreach ($result as $v) {
                $response[$v['ID']] = $v['NAME'];
            }
        }

        return $response;
    }

    /**
     * �ϲ�������ݵ�һ��
     * @param $old
     * @param $new
     * @return mixed
     */
    protected function mergeChangedBudgetData1($old, $new) {
        $response = $old;

        $keys = array_keys($new);
        foreach($old as $k => $v) {
            $sign = '[ԭ]';
            if (in_array($k, $keys)) {
                if ($new[$k]['ISNEW'] == -1) {
                    $sign = '[��]';
                }
                $response[$k] = $new[$k];
                $response[$k]['SIGN'] = $sign;
            }
        }

        return $response;
    }

    /**
     * �ϲ�������ݵڶ���
     * @param $data
     * @param $sumAmount
     * @param $feeTypeList
     * @return array
     */
    protected function mergeChangedBudgetData2($data, $sumAmount, $feeTypeList) {
        $response = array();
        if (is_array($data) && count($data)) {
            // AMOUNT
            $response['ID'] = $data['ID'];
            $response['AMOUNT'] = $this->mergeAmount($data['AMOUNT']);

            // FEE
            $response['FEE'] = $this->mergeFee($data['FEE_ID'], $feeTypeList);

            // MARK
            $response['MARK'] = $this->mergeMark($data['MARK']);

            // RATIO
            $response['RATIO'] = sprintf('%s%s', round($response['AMOUNT']['VAL'] * 100 / floatval($sumAmount), 2), self::PERCENT_MARK);
        }

        return $response;
    }

    /**
     * ͳһ������Ŀ����
     * @param $data
     * @return array
     */
    private function mergeAmount($data) {
        $response = array(
            'VAL' => '',
            'DESC' => ''
        );
        if (is_array($data) && count($data)) {
            $response['VAL'] = $data['VALUEE'];
            if (floatval($data['VALUEE']) > 0) {
                $response['DESC'] .= $data['VALUEE'] . self::MONEY_UNIT;
            }
            $response['DESC'] .= "<br/><span class='text-red'>{$data['SIGN']}";  // ����һ�����з�
            if (floatval($data['ORIVALUEE']) > 0) {
                $response['DESC'] .= $data['ORIVALUEE'] . self::MONEY_UNIT;
            }
            $response['DESC'] .= '</span>';
        } else {
            $response['VAL'] = $data;
            $response['DESC'] = $data . self::MONEY_UNIT;
        }

        return $response;
    }

    /**
     * ͬ�������������
     * @param $data
     * @param $feeTypeList
     * @return array
     */
    private function mergeFee($data, $feeTypeList) {
        $response = array(
            'ID' => $data['VALUEE'],
            'NAME' => '',
            'DESC' => ''
        );

        if (is_array($data) && count($data)) {
            if (!empty($data['VALUEE'])) {
                $response['NAME'] = $feeTypeList[$data['VALUEE']];
                $response['DESC'] = $response['NAME'];
            }

            $response['DESC'] .= '<br/><span class="text-red">' . $data['SIGN'];
            if (!empty($data['ORIVALUEE'])) {
                $response['DESC'] .= $feeTypeList[$data['ORIVALUEE']];
            }
            $response['DESC'] .= '</span>';
        } else {
            $response['NAME'] =  $feeTypeList[$data];
            $response['DESC'] =  $feeTypeList[$data];
        }

        return $response;
    }

    /**
     * ͳһ��ע����
     * @param $data
     * @return array|string
     */
    private function mergeMark($data) {
        if (is_array($data) && count($data)) {
            $response = sprintf("%s<br/><span class='text-red'>%s%s</span>", $data['VALUEE'], $data['SIGN'], $data['ORIVALUEE']);
        } else {
            $response = $data;
        }

        return $response;
    }



    /**
     * ��ȡ������е�Ԥ�����ݣ���ԭʼ���ݺϲ�
     * @param $data
     * @param $oriBudget
     */
    public function getChangedBudgetData($data, $oriBudget) {
        $response = $oriBudget;
        if (is_array($data) && count($data)) {
            $changedBudget = $this->mapChangedBudgetData1($this->getRawChangedBudget($data, $response));
            $changedBudgetKeys = array_keys($changedBudget);
            foreach($oriBudget as $k => $v) {
                if (in_array($v['ID'], $changedBudgetKeys)) {
                    $response[$k] = $this->mergeChangedBudgetData1($v, $changedBudget[$v['ID']]);
                }
            }
        }

        return $response;
    }

    /**
     * ӳ��Ԥ��
     * @param $arr
     * @return array
     */
    protected function mapBudget($arr) {
        $response = array();
        if (is_array($arr) && count($arr)) {
            $feeTypeList = $this->getFeeTypeList();  // ��ȡ���ü�ֵ���б�
            $sum = 0;
            foreach ($arr as $k => $v) {
                if (is_array($v['AMOUNT']) && count($v['AMOUNT'])) {
                    $sum += floatval($v['AMOUNT']['VALUEE']);
                } else {
                    $sum += floatval($v['AMOUNT']);
                }
            }
            foreach ($arr as $k => $v) {
                $response [] = $this->mergeChangedBudgetData2($v, $sum, $feeTypeList);
            }
        }

        return $response;
    }

    /**
     * ӳ������
     * @param $activity
     * @return array
     */
    protected function mapActivity($activity) {
        $response = array();
        if (is_array($activity) && count($activity)) {
            $response = array(
                'CONTRACT_NO'=>array(
                    'alias' => '��ͬ���',
                    'info' => $activity['CONTRACT_NO']
                ),
                'DEPTNAME' => array(
                    'alias' => '����',
                    'info' => $activity['DEPTNAME']
                ),
                'APPLICANT_NAME' => array(
                    'alias' => '������',
                    'info' => $activity['APPLICANT_NAME']
                ),
                'TITLE' => array(
                    'alias' => '�����',
                    'info' => $activity['TITLE']
                ),
                'ADDRESS' => array(
                    'alias' => '��ص�',
                    'info' => $activity['ADDRESS']
                ),
                'HTIME' => array(
                    'alias' => '���ʼʱ��',
                    'info' => $activity['HTIME']
                ),
                'HETIME' => array(
                    'alias' => '�����ʱ��',
                    'info' => $activity['HETIME']
                ),
                'HMODE' => array(
                    'alias' => '�ģʽ',
                    'info' => $activity['HMODE']
                ),
                'HTYPE' => array(
                    'alias' => '���ʽ',
                    'info' => $activity['HTYPE']
                ),
                'PRINCOME' => array(
                    'alias' => 'Ԥ������',
                    'info' => $activity['PRINCOME']
                ),
                'PERSONAL' => array(
                    'alias' => '�μ���Ա����',
                    'info' => $activity['PERSONAL']
                ),
                'PRNUMBER' => array(
                    'alias' => 'Ԥ����������',
                    'info' => $activity['PRNUMBER']
                ),
                'CHARGE' => array(
                    'alias' => '��ܸ�����',
                    'info' => $activity['CHARGE']
                ),
                'CONTENT' => array(
                    'alias' => '�����',
                    'info' => $activity['CONTENT']
                ),
                'BUDGET' => array(
                    'alias' => 'Ԥ�����',
                    'info' => $activity['BUDGET']
                ),
                'PROFITMARGIN' => array(
                    'alias' => '������',
                    'info' => $activity['PROFITMARGIN']
                ),
                'MYFEE' => array(
                    'alias' => '�ҷ�����',
                    'info' => $activity['MYFEE']
                ),
                'BUSINESSFEE' => array(
                    'alias' => '���̷���',
                    'info' => $activity['BUSINESSFEE']
                ),
                'YEWU' => array(
                    'alias' => 'ҵ������',
                    'info' => $activity['YEWU']
                )
            );
        }
        if($this->isProjectActivity){
            unset($response['CONTRACT_NO']);
        }
        return $response;
    }

    /**
     * ��֤Ԥ���Ƿ����Ҫ��
     * @param $prjId
     * @return array
     */
    private function checkBudget($prjId) {
        $response = array(
            'status' => false,
            'message' => ''
        );
        if (intval($prjId) > 0) {
            $case = M('Erp_case')->where('SCALETYPE = 4 AND PROJECT_ID=' . $prjId)->find();
            $one = M('Erp_activities')->where('CASE_ID=' . $case['ID'])->find();
            $activityId = $one['ID'];
            $fees = M('Erp_actibudgetfee')->where(" ISVALID=-1 and ACTIVITIES_ID=" . $activityId)->select();

            if (!$fees) {
                $response['message'] = '������дԤ�����';
            } else {
                //�жϷ����Ƿ����
                $budgetFee = $one['BUDGET'];//Ԥ�����
                $pri_Budget_Fee = M('Erp_actibudgetfee')->where(" ACTIVITIES_ID ='" . $activityId . "'")->sum('AMOUNT');//ʵ��Ԥ�����
                if ($budgetFee > $pri_Budget_Fee) {
                    $response['message'] = 'Ԥ�������ʵ��Ԥ�������� ' . ($budgetFee - $pri_Budget_Fee) . "Ԫ";
                    $response['status'] = false;
                } else if ($budgetFee < $pri_Budget_Fee) {
                    $response['message'] = 'Ԥ�����С��ʵ��Ԥ�����';
                    $response['status'] = false;
                } else {
                    $response['status'] = true;
                }
            }
        }

        return $response;
    }

    /**
     * ���������
     */
    public function opinionFlow() {
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );

        // ���Ԥ���Ƿ��������
        $budgetResult = $this->checkBudget($_REQUEST['CASEID']);
        if ($budgetResult['status'] == false) {
            echo json_encode(g2u($budgetResult));
            exit();
        }

        if ($this->myTurn) {  // �ǵ�ǰ�������Ǹõ�¼�û�
            $data = u2g($_REQUEST);
            $result = $this->workFlow->doit($data);
            if (is_array($result)) {
                $response = $result;
            } else {
                $response['status'] = $result;
            }
        } else {
            $response['message'] = '�ǵ�ǰ������';
        }

        if (empty($response['url'])) {
            $response['url'] = U('Flow/flowList', 'status=1');
        }

        echo json_encode(g2u($response));
    }

    /**
     * ����������
     */
    public function opinionFlowChange() {
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );

        if ($this->myTurn) {
            $data = u2g($_REQUEST);
            $result = $this->checkChangedBudget($data['ACTIVID'], $data['RECORDID']);  // ���Ԥ������Ƿ���ȷ
            if ($result['status']) {
                if ($this->isChanged && !$this->isProjectActivity) {
                    $data['projectId'] = $this->record['CASEID'];  // ��ĿID
                    $data['activityId'] = $this->activityID;  // �ID
                }
                $result =$this->workFlow->doit($data);
            }

            if (is_array($result)) {
                $response = $result;
            } else {
                $response['status'] = $result;
            }
        } else {
            $response['message'] = '�ǵ�ǰ������';
        }

        if (empty($response['url'])) {
            $response['url'] = U('Flow/flowList', 'status=1');
        }

        echo json_encode(g2u($response));
    }

    /**
     * ��Ŀ�»����
     */
    public function XiangMuOpinionFlow() {
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );

        if ($this->myTurn) {
            $_REQUEST = u2g($_REQUEST);
            $result = $this->checkXMXBudget($_REQUEST['ACTIVID']);
            if ($result['status']) {
                $result = $this->workFlow->doit($_REQUEST);
            }

            if (is_array($result)) {
                $response = $result;
            } else {
                $response['status'] = $result;
            }
        } else {
            $response['message'] = '�ǵ�ǰ������';
        }

        if (empty($response['url'])) {
            $response['url'] = U('Flow/flowList', 'status=1');
        }
        echo json_encode(g2u($response));
    }

    /**
     * �ж��Ƿ�Ϊ��Ŀ�»
     * @param $activityID
     * @return bool
     */
    protected function isProjectActivity($activityID) {
        $response = false;
        if (intval($activityID) > 0) {
            $sql = <<<ACTIVITY_TYPE_SQL
            SELECT C.SCALETYPE
            FROM ERP_ACTIVITIES T
            LEFT JOIN ERP_CASE C ON C.ID = T.CASE_ID
            WHERE T.ID = %d
ACTIVITY_TYPE_SQL;

            $result = D()->query(sprintf($sql, $activityID));
            if (is_array($result) && count($result)) {
                $response = (intval($result[0]['SCALETYPE']) == 7);
            }
        }

        return $response;
    }

    /**
     * �������Ŀ��Ԥ������Ƿ���ȷ
     * @param $actId
     * @param $changeId
     * @return array
     */
    protected function checkChangedBudget($actId, $changeId) {
        $result = array(
            'status' => false,
            'message' => ""
        );
        if (intval($actId) && intval($changeId) > 0) {
            $activity = M("Erp_activities")->where("ID = {$actId}")->find();
            $budgetFee = $activity['BUDGET'];//Ԥ�����

            //Ԥ����ñ��
            $params = array(
                'TABLE' => 'ERP_ACTIVITIES',
                'BID' => $actId,
                'CID' => $changeId
            );

            Vendor('Oms.Changerecord');
            $changer = new Changerecord();
            $changer->fields = array('BUDGET');
            $change_budget = $changer->getRecords($params);
            $change_budget_Fee = $change_budget['BUDGET']['VALUEE'];
            $budgetFee = $change_budget_Fee ? $change_budget_Fee : $budgetFee;

            //ʵ��Ԥ����ñ��
            $pri_Budget_Fee = 0;
            //�жϷ����Ƿ����
            $fees = M('Erp_actibudgetfee')->where(sprintf("ACTIVITIES_ID = %d AND (CID = %d OR ISVALID = -1)", $actId, $_REQUEST['RECORDID']))->select();
            if ($fees) {
                $param = array(
                    'TABLE' => 'ERP_ACTIBUDGETFEE',
                    'CID' => $_REQUEST['RECORDID']
                );

                foreach ($fees as $fee) {
                    $param['BID'] = $fee['ID'];
                    $changer->fields = array('AMOUNT');
                    $Records = $changer->getRecords($param);

                    if ($Records) {
                        $pri_Budget_Fee += $Records['AMOUNT']['VALUEE'];
                    } else {
                        $pri_Budget_Fee += M('Erp_actibudgetfee')->where('ID = ' . $fee['ID'])->getField('AMOUNT');
                    }
                }
            }

            if (!$fees) {
                $result['status'] = false;
                $result['message'] = '������дԤ�����';
            } elseif ($budgetFee > $pri_Budget_Fee) {
                $result['status'] = false;
                $result['message'] = 'Ԥ�������ʵ��Ԥ�������� ' . ($budgetFee - $pri_Budget_Fee) . "Ԫ";
            } elseif ($budgetFee < $pri_Budget_Fee) {
                $result['status'] = false;
                $result['message'] = 'Ԥ�����С��ʵ��Ԥ�����';
            } elseif ($budgetFee = $pri_Budget_Fee) {
                $result['status'] = true;
                $result['message'] = '';
            }
        }

        return $result;
    }

    /**
     * �����Ŀ��Ԥ�����
     * @param $actId
     * @return array
     */
    protected function checkXMXBudget($actId) {
        $result = array(
            'status' => false,
            'message' => ''
        );
        if (intval($actId) > 0) {
            $fees = M('Erp_actibudgetfee')->where("ACTIVITIES_ID=".$actId)->select();

            $activety = M("Erp_activities")->where("ID = {$actId}")->find();
            $budgetFee = $activety['BUDGET'];//Ԥ�����

            $pri_Budget_Fee = M('Erp_actibudgetfee')->where("ISVALID = -1 AND ACTIVITIES_ID =".$actId)->sum('AMOUNT');//ʵ��Ԥ�����

            if(!$fees){
                $result['status'] = false;
                $result['message'] = '������дԤ�����';

            } elseif ($budgetFee >  $pri_Budget_Fee){
                $result['status'] = false;
                $result['message'] = 'Ԥ�������ʵ��Ԥ�������� '.($budgetFee-$pri_Budget_Fee).'Ԫ';
            }elseif($budgetFee <  $pri_Budget_Fee){
                $result['status'] = false;
                $result['message'] = 'Ԥ�����С��ʵ��Ԥ�����';
            }else{
                $result['status'] = true;
            }
        }
        return $result;
    }

    protected function getRealProjectName() {
        // todo
    }
}