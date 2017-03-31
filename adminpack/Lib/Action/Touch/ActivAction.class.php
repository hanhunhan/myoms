<?php

/**
 * 活动工作流审批控制器
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/2
 * Time: 9:06
 */
class ActivAction extends ExtendAction {
    /**
     * 独立活动变更
     */
    const ACTIVITY_CHANGE_FLOWSET = 23;

    /**
     * 项目下独立活动变更
     */
    const PROJECT_ACTIVITY_CHANGE_FLOWSET = 24;

    /**
     * 活动详情查询语句
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
     * 活动预算查询语句
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
     * 独立活动立项变更过程中预算查询语句
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
     * 获取变更之后的数据
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
        '1' => '线上',
        '2' => '线下'
    );

    protected $hTypeValues = array(
        '1' => '招商活动',
        '2' => '品牌推广',
        '3' => '项目推广',
        '4' => '研讨活动',
        '5' => '培训活动',
        '6' => '其他'
    );

    /**
     * 需要加货币单位的字段
     */
    protected $needMoneyUnit = array(
        'PRINCOME', 'BUDGET', 'MYFEE', 'BUSINESSFEE'
    );

    /**
     * 需要添加百分号的字段
     * @var array
     */
    protected $needPercentMark = array(
        'PROFITMARGIN'
    );

    /**
     * 是否为项目下活动 true=是
     * @var bool
     */
    protected $isProjectActivity = false;

    /**
     * 活动的ID
     * @var null
     */
    protected $activityID = null;

    /**
     * 是否为立项变更 true=是
     * @var null
     */
    protected $isChanged = null;

    public function __construct() {
        parent::__construct();
        // 初始化菜单
        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),
            'activity_detail' => array(
                'name' => 'activity-detail',
                'text' => '活动详情'
            ),
            'budget' => array(
                'name' => 'budget',
                'text' => '活动预算'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        $this->init();
    }

    /**
     * 如果存在工作流则走默认权限查看
     * 否则个性处理
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
        // 如果工作流ID不存在，说明是创建工作流
        $this->isChanged = intval($_REQUEST['CHANGE']) == -1;
        if ($this->isChanged) {
            // 立项变更会将所需的参数都传入
            // 处理独立活动、项目下活动变更状态
            $this->CASEID = $_REQUEST['CASEID'];
            $this->recordId = $_REQUEST['RECORDID'];
            $this->activityID = $this->ACTIVID = $_REQUEST['ACTIVID'];
        } else {
            // 要处理独立活动、项目下活动两种类型
            $this->CASEID = $_REQUEST['prjId'];  // 项目列表中的提交按钮进入
            if (empty($this->CASEID)) {
                // 不是从项目列表进入，而是审批意见中转交工作流
                $this->CASEID = $_REQUEST['CASEID'];
                $this->activityID = $this->recordId = $_REQUEST['RECORDID'];
            }
        }

        if (intval($this->CASEID) > 0) {
            // 从项目列表进入
            if (empty($this->activityID)) {
                $activity = D('Project')->getActivityByProjectId($this->CASEID);
                if (notEmptyArray($activity)) {
                    $this->activityID = $this->recordId = $activity['ID'];
                }
            }
        } else {
            js_alert('请选择项目');
            die();
        }
    }

    /**
     * 初始化
     */
    private function init() {
        if ($this->flowId) {
            $this->isChanged = $this->record['FLOWSETID'] == self::ACTIVITY_CHANGE_FLOWSET || $this->record['FLOWSETID'] == self::PROJECT_ACTIVITY_CHANGE_FLOWSET;
            $this->activityID = !empty($this->record['ACTIVID']) ? $this->record['ACTIVID'] : $this->record['RECORDID'];  // 活动ID
        } else {
            $this->initWithoutFlow();
        }
        $this->isProjectActivity = $this->isProjectActivity($this->activityID);  // 是否为项目下活动 true=是 false=否

        // 初始化工作流
        Vendor('Oms.Flows.Flow');
        if ($this->isProjectActivity) {  // 项目下活动
            if ($this->isChanged) {  // 变更
                $this->workFlow = new Flow('ProjectActivityChange');
                $this->flowType = 'xiangmuxiahuodongbiangeng';
                $this->assign('flowType', 'xiangmuxiahuodongbiangeng');
                $this->assign('flowTypeText', '项目下活动立项变更');
                $this->assign('activityTitle', '关于项目下活动立项变更申请的审批');  // 工作流标题
                $this->assign('CID', $_REQUEST['CID']);  // 活动变更版本号
            } else {  // 立项
                $this->workFlow = new Flow('ProjectActivity');
                $this->flowType = 'xiangmuxiahuodong';
                $this->assign('flowType', 'xiangmuxiahuodong');
                $this->assign('flowTypeText', '项目下活动立项申请');
                $this->assign('activityTitle', '关于项目下活动立项申请的审批');  // 工作流标题
                $this->assign('showCC', true);
            }
        } else {  // 独立活动
            if ($this->isChanged) {  // 变更
                $this->workFlow = new Flow('ActivityChange');
                $this->flowType = 'dulihuodongbiangeng';
                $this->assign('flowType', 'dulihuodongbiangeng');
                $this->assign('flowTypeText', '独立活动立项变更');
                $this->assign('activityTitle', '关于独立活动立项变更申请的审批');  // 工作流标题
                $this->assign('CID', $_REQUEST['CID']);  // 活动变更版本号
            } else {  // 立项
                $this->workFlow = new Flow('Activity');
                $this->flowType = 'dulihuodong';
                $this->assign('flowType', 'dulihuodong');
                $this->assign('flowTypeText', '独立活动立项申请');
                $this->assign('activityTitle', '关于独立活动立项申请的审批');  // 工作流标题
                $this->assign('showCC', true);
            }
        }

        if (empty($this->flowId)) {
            $this->assign('CASEID', $this->CASEID);  // 将项目ID赋值给CASEID(WTF!!!)
            $this->assign('recordId', $this->recordId);
            $this->assign('ACTIVID', $this->ACTIVID);
            $this->assign('CHANGE', $this->isChanged ? -1 : 0);
        }
    }

    /**
     * 处理
     */
    public function process() {
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);
        }

        if ($this->isChanged) {
            $activity = $this->getChangedActivityInfo($this->activityID);  // 获取活动信息
            $budget = $this->getChangedBudget($this->activityID);  // 获取预算信息
        } else {
            $activity = $this->getActivityInfo($this->activityID);  // 获取活动信息
            $budget = $this->getBudget($this->activityID);  // 获取预算信息
        }

        $this->assign('ACTIVID', $this->activityID);  // 活动ID
        $this->assign('activity', $activity);  // 项目信息
        $this->assign('budget', $budget);  // 预算信息

        $this->assignWorkFlows($this->flowId);
        $this->assign('menu', $this->menu);  // 菜单
//        $this->assign('activityTitle', $activity['TITLE']['info']['VAL']);  // 活动主题
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        $this->display('index');
    }

    /**
     * 获取独立活动信息
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
     * 获取项目变更信息
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
            $sign = '[原]';
            if (intval($changedColumnData['ISNEW'] == -1)) {
                $sign = '[增]';
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
     * 映射活动详情变更数据：第三步
     * @param $data
     * @return mixed
     */
    protected function mapChangedActivityData3($data) {
        $response = $data;
        foreach($data as $k => $v) {
            // 添加货币单位
            if (in_array($k, $this->needMoneyUnit)) {
                if (is_array($v['DESC'])) {
                    $response[$k]['DESC']['new'] = floatval($v['DESC']['new']) . self::MONEY_UNIT;
                    $response[$k]['DESC']['old'] = floatval($v['DESC']['old']) . self::MONEY_UNIT;
                } else {
                    $response[$k]['DESC'] = floatval($v['DESC']) . self::MONEY_UNIT;
                }
            }

            // 添加百分号
            if (in_array($k, $this->needPercentMark)) {
                if (is_array($v['DESC'])) {
                    $response[$k]['DESC']['new'] = floatval($v['DESC']['new']) . self::PERCENT_MARK;
                    $response[$k]['DESC']['old'] = floatval($v['DESC']['old']) . self::PERCENT_MARK;
                } else {
                    $response[$k]['DESC'] = floatval($v['DESC']) . self::PERCENT_MARK;
                }
            }

            // 活动模式
            if ($k == 'HMODE') {
                if (is_array($v['DESC'])) {
                    $response[$k]['DESC']['new'] = $this->hModeValues[$v['DESC']['new']];
                    $response[$k]['DESC']['old'] = $this->hModeValues[$v['DESC']['old']];
                } else {
                    $response[$k]['DESC'] = $this->hModeValues[$v['DESC']];
                }
            }

            // 活动形式
            if ($k == 'HTYPE') {
                if (is_array($v['DESC'])) {
                    $response[$k]['DESC']['new'] = $this->hTypeValues[$v['DESC']['new']];
                    $response[$k]['DESC']['old'] = $this->hTypeValues[$v['DESC']['old']];
                } else {
                    $response[$k]['DESC'] = $this->hTypeValues[$v['DESC']];
                }
            }

            // 如果是数组则转成字符串
            if (is_array($response[$k]['DESC'])) {
                $temp = vsprintf('%s<br><span class="text-red">%s%s</span>', $response[$k]['DESC']);
                $response[$k]['DESC'] = $temp;
            }
        }

        return $response;
    }

    /**
     * 映射活动详情变更数据：第二步
     * @param $changed
     * @param $oriActivity
     * @return array
     */
    protected function mapChangedActivityData2($changed, $oriActivity) {
        $response = array();
        $indexList = array();
        // 将发生变更的字段用数组保存起来
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
     * 映射活动详情变更数据：第一步
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
     * 获取活动变动数据
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
     * 获取活动预算
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
     * 获取变更过程中的活动预算
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
     * 获取变更数据的BID
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
     * 对变更数据进行第一步加工
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
     * 获取原始的未加工的变更数据
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
     * 获取费用类型列表
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
     * 合并变更数据第一步
     * @param $old
     * @param $new
     * @return mixed
     */
    protected function mergeChangedBudgetData1($old, $new) {
        $response = $old;

        $keys = array_keys($new);
        foreach($old as $k => $v) {
            $sign = '[原]';
            if (in_array($k, $keys)) {
                if ($new[$k]['ISNEW'] == -1) {
                    $sign = '[增]';
                }
                $response[$k] = $new[$k];
                $response[$k]['SIGN'] = $sign;
            }
        }

        return $response;
    }

    /**
     * 合并变更数据第二步
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
     * 统一费用数目数据
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
            $response['DESC'] .= "<br/><span class='text-red'>{$data['SIGN']}";  // 加上一个换行符
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
     * 同意费用类型数据
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
     * 统一备注数据
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
     * 获取变更表中的预算数据，与原始数据合并
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
     * 映射预算
     * @param $arr
     * @return array
     */
    protected function mapBudget($arr) {
        $response = array();
        if (is_array($arr) && count($arr)) {
            $feeTypeList = $this->getFeeTypeList();  // 获取费用键值对列表
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
     * 映射活动数据
     * @param $activity
     * @return array
     */
    protected function mapActivity($activity) {
        $response = array();
        if (is_array($activity) && count($activity)) {
            $response = array(
                'CONTRACT_NO'=>array(
                    'alias' => '合同编号',
                    'info' => $activity['CONTRACT_NO']
                ),
                'DEPTNAME' => array(
                    'alias' => '部门',
                    'info' => $activity['DEPTNAME']
                ),
                'APPLICANT_NAME' => array(
                    'alias' => '申请人',
                    'info' => $activity['APPLICANT_NAME']
                ),
                'TITLE' => array(
                    'alias' => '活动主题',
                    'info' => $activity['TITLE']
                ),
                'ADDRESS' => array(
                    'alias' => '活动地点',
                    'info' => $activity['ADDRESS']
                ),
                'HTIME' => array(
                    'alias' => '活动开始时间',
                    'info' => $activity['HTIME']
                ),
                'HETIME' => array(
                    'alias' => '活动结束时间',
                    'info' => $activity['HETIME']
                ),
                'HMODE' => array(
                    'alias' => '活动模式',
                    'info' => $activity['HMODE']
                ),
                'HTYPE' => array(
                    'alias' => '活动形式',
                    'info' => $activity['HTYPE']
                ),
                'PRINCOME' => array(
                    'alias' => '预计收入',
                    'info' => $activity['PRINCOME']
                ),
                'PERSONAL' => array(
                    'alias' => '参加人员类型',
                    'info' => $activity['PERSONAL']
                ),
                'PRNUMBER' => array(
                    'alias' => '预估到场人数',
                    'info' => $activity['PRNUMBER']
                ),
                'CHARGE' => array(
                    'alias' => '活动总负责人',
                    'info' => $activity['CHARGE']
                ),
                'CONTENT' => array(
                    'alias' => '活动内容',
                    'info' => $activity['CONTENT']
                ),
                'BUDGET' => array(
                    'alias' => '预算费用',
                    'info' => $activity['BUDGET']
                ),
                'PROFITMARGIN' => array(
                    'alias' => '利润率',
                    'info' => $activity['PROFITMARGIN']
                ),
                'MYFEE' => array(
                    'alias' => '我方费用',
                    'info' => $activity['MYFEE']
                ),
                'BUSINESSFEE' => array(
                    'alias' => '电商费用',
                    'info' => $activity['BUSINESSFEE']
                ),
                'YEWU' => array(
                    'alias' => '业务类型',
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
     * 验证预算是否符合要求
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
                $response['message'] = '请先填写预算费用';
            } else {
                //判断费用是否相等
                $budgetFee = $one['BUDGET'];//预算费用
                $pri_Budget_Fee = M('Erp_actibudgetfee')->where(" ACTIVITIES_ID ='" . $activityId . "'")->sum('AMOUNT');//实际预算费用
                if ($budgetFee > $pri_Budget_Fee) {
                    $response['message'] = '预算费用与实际预算费用相差 ' . ($budgetFee - $pri_Budget_Fee) . "元";
                    $response['status'] = false;
                } else if ($budgetFee < $pri_Budget_Fee) {
                    $response['message'] = '预算费用小于实际预算费用';
                    $response['status'] = false;
                } else {
                    $response['status'] = true;
                }
            }
        }

        return $response;
    }

    /**
     * 独立活动审批
     */
    public function opinionFlow() {
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );

        // 检查预算是否符合条件
        $budgetResult = $this->checkBudget($_REQUEST['CASEID']);
        if ($budgetResult['status'] == false) {
            echo json_encode(g2u($budgetResult));
            exit();
        }

        if ($this->myTurn) {  // 非当前审批人是该登录用户
            $data = u2g($_REQUEST);
            $result = $this->workFlow->doit($data);
            if (is_array($result)) {
                $response = $result;
            } else {
                $response['status'] = $result;
            }
        } else {
            $response['message'] = '非当前审批人';
        }

        if (empty($response['url'])) {
            $response['url'] = U('Flow/flowList', 'status=1');
        }

        echo json_encode(g2u($response));
    }

    /**
     * 活动变更工作流
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
            $result = $this->checkChangedBudget($data['ACTIVID'], $data['RECORDID']);  // 检查预算费用是否正确
            if ($result['status']) {
                if ($this->isChanged && !$this->isProjectActivity) {
                    $data['projectId'] = $this->record['CASEID'];  // 项目ID
                    $data['activityId'] = $this->activityID;  // 活动ID
                }
                $result =$this->workFlow->doit($data);
            }

            if (is_array($result)) {
                $response = $result;
            } else {
                $response['status'] = $result;
            }
        } else {
            $response['message'] = '非当前审批人';
        }

        if (empty($response['url'])) {
            $response['url'] = U('Flow/flowList', 'status=1');
        }

        echo json_encode(g2u($response));
    }

    /**
     * 项目下活动审批
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
            $response['message'] = '非当前审批人';
        }

        if (empty($response['url'])) {
            $response['url'] = U('Flow/flowList', 'status=1');
        }
        echo json_encode(g2u($response));
    }

    /**
     * 判断是否为项目下活动
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
     * 检查变更项目的预算费用是否正确
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
            $budgetFee = $activity['BUDGET'];//预算费用

            //预算费用变更
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

            //实际预算费用变更
            $pri_Budget_Fee = 0;
            //判断费用是否相等
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
                $result['message'] = '请先填写预算费用';
            } elseif ($budgetFee > $pri_Budget_Fee) {
                $result['status'] = false;
                $result['message'] = '预算费用与实际预算费用相差 ' . ($budgetFee - $pri_Budget_Fee) . "元";
            } elseif ($budgetFee < $pri_Budget_Fee) {
                $result['status'] = false;
                $result['message'] = '预算费用小于实际预算费用';
            } elseif ($budgetFee = $pri_Budget_Fee) {
                $result['status'] = true;
                $result['message'] = '';
            }
        }

        return $result;
    }

    /**
     * 检查项目下预算费用
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
            $budgetFee = $activety['BUDGET'];//预算费用

            $pri_Budget_Fee = M('Erp_actibudgetfee')->where("ISVALID = -1 AND ACTIVITIES_ID =".$actId)->sum('AMOUNT');//实际预算费用

            if(!$fees){
                $result['status'] = false;
                $result['message'] = '请先填写预算费用';

            } elseif ($budgetFee >  $pri_Budget_Fee){
                $result['status'] = false;
                $result['message'] = '预算费用与实际预算费用相差 '.($budgetFee-$pri_Budget_Fee).'元';
            }elseif($budgetFee <  $pri_Budget_Fee){
                $result['status'] = false;
                $result['message'] = '预算费用小于实际预算费用';
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