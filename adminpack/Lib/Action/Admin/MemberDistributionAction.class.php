<?php

/**
 * 分销会员控制器
 *
 * @author liuhu
 */
class MemberDistributionAction extends ExtendAction {
    /**
     * 中介佣金报销权限
     */
    const AGENCY_REWARD_REIM = 504;

    /**
     * 中介成交奖励报销权限
     */
    const AGENCY_DEAL_REWARD_REIM = 706;

    /**
     * 置业成交奖励报销权限
     */
    const PROPERTY_DEAL_REWARD_REIM = 707;

    /**
     * 申请开票权限
     */
    const APPLY_BILLING = 505;

    /**
     * manage函数下的新增
     */
    const MANAGE_ADD = 501;

    /**
     * 【提交开票申请】权限
     */
    const APPLYINVOICE = 517;

    /**
     * 提交报销申请权限
     */
    const SUB_REIM_APPLY = 518;

    /**
     * 关联借款权限
     */
    const RELATED_MY_LOAN = 708;

    /**
     * 单位：元
     */
    const UNIT_RMB_YUAN = '元';

    /**
     * 单位：%
     */
    const UNIT_PERCENT = '%';

    /**
     * 表单正处于编辑状态
     */
    const FORM_EDIT_STATUS = 1;

    /**
     * 保存表单数据操作
     */
    const SAVE_FORM_ACTION = 'saveFormData';

    /**
     * 导出开票申请SQL语句
     */
    const EXPORT_INVOICE_SQL = <<<INVOICE_SQL
        SELECT a.id,
               a.apply_user_id,
               to_char(a.createtime,'yyyy-MM-dd HH24:mi:ss') CREATETIME,
               a.case_id,
               b.contract_no,
               c.scaletype,
               d.projectname,
               d.city_id,
               u.name applier_name,
               city.name city_name
        FROM erp_billing_record a
        LEFT JOIN erp_income_contract b ON a.contract_id=b.id
        LEFT JOIN erp_case c ON a.case_id=c.id
        LEFT JOIN erp_project d ON c.project_id=d.id
        LEFT JOIN erp_users u ON u.id = a.apply_user_id
        LEFT JOIN erp_city city ON city.id = d.city_id
        WHERE a.case_id= %d
          AND a.id= %d
INVOICE_SQL;

    /**
     * 导出会员列表SQL语句
     */
    const EXPORT_MEMBER_SQL = <<<MEMBER_SQL
        SELECT d.ID,
               REALNAME,
               MOBILENO,
               CERTIFICATE_TYPE,
               d.CERTIFICATE_NO AS IDCARDNO,
               ROOMNO,
               HOUSEAREA,
               HOUSETOTAL,
               to_char(SIGNTIME,'YYYY-MM-DD') AS SIGNTIME,
               SIGNEDSUITE,
               de.INVOICE_STATUS,
               de.PERCENT,
               de.AMOUNT,
               concatUnit(AGENCY_REWARD_AFTER, f1.Stype) AGENCY_REWARD_AFTER,
               concatUnit(AGENCY_DEAL_REWARD, f2.Stype) AGENCY_DEAL_REWARD,
               concatUnit(PROPERTY_DEAL_REWARD, f3.Stype) PROPERTY_DEAL_REWARD,
               concatUnit(TOTAL_PRICE_AFTER, f4.Stype) TOTAL_PRICE_AFTER,
               concatUnit(OUT_REWARD, f5.Stype) OUT_REWARD
        FROM erp_commission_invoice_detail de
        LEFT JOIN erp_post_commission p ON p.id = de.post_commission_id
        LEFT JOIN ERP_CARDMEMBER d ON d.id = p.card_member_id
        LEFT JOIN erp_case c ON c.id = d.case_id
        LEFT JOIN ERP_FEESCALE f1 ON f1.case_id = d.case_id AND f1.amount = d.AGENCY_REWARD_AFTER AND f1.SCALETYPE = 2 AND f1.ISVALID = -1 AND f1.MTYPE = 1
        LEFT JOIN ERP_FEESCALE f2 ON f2.case_id = d.case_id AND f2.amount = d.AGENCY_DEAL_REWARD AND f2.SCALETYPE = 4 AND f2.ISVALID = -1
        LEFT JOIN ERP_FEESCALE f3 ON f3.case_id = d.case_id AND f3.amount = d.PROPERTY_DEAL_REWARD AND f3.SCALETYPE = 5 AND f3.ISVALID = -1
        LEFT JOIN ERP_FEESCALE f4 ON f4.case_id = d.case_id AND f4.amount = d.TOTAL_PRICE_AFTER AND f4.SCALETYPE = 1 AND f4.ISVALID = -1 and f4.MTYPE = 1
        LEFT JOIN ERP_FEESCALE f5 ON f5.case_id = d.case_id AND f5.amount = d.OUT_REWARD AND f5.SCALETYPE = 3 AND f5.ISVALID = -1
        WHERE d.CASE_ID = %d
          AND de.BILLING_RECORD_ID = %d
        ORDER BY ID DESC
MEMBER_SQL;

    const WORK_SHEET_TITLE = '分销会员开票申请';

    const EXCEL_MEMBER_TITLE = '会员列表';

    /**
     * 导出开票申请字段设置
     * @var array
     */
    private $outputInvoice = array(
        'CASE_ID' => array(
            'name' => '案例编号'
        ),
        'PROJECTNAME' => array(
            'name' => '项目名称'
        ),
        'CITY_NAME' => array(
            'name' => '城市',
            'width' => 10
        ),
        'CONTRACT_NO' => array(
            'name' => '合同编号',
            'width' => 25
        ),
        'APPLIER_NAME' => array(
            'name' => '申请人',
            'width' => 15
        ),
        'CREATETIME' => array(
            'name' => '申请时间',
            'width' => 25
        )
    );

    private $outputMember = array(
        'ID' => array(
            'name' => '编号',
        ),
        'REALNAME' => array(
            'name' => '客户姓名',
        ),
        'MOBILENO' => array(
            'name' => '手机号',
        ),
        'CERTIFICATE_TYPE' => array(
            'name' => '证件类型',
            'map' => array(
                '1' => '身份证',
                '2' => '户口簿',
                '3' => '军官证',
                '4' => '士兵证',
                '5' => '警官证',
                '6' => '护照',
                '7' => '台胞证',
                '8' => '回乡证',
                '9' => '身份证（港澳）',
                '10' => '营业执照',
                '11' => '法人代码',
                '12' => '其它',
            )
        ),
        'IDCARDNO' => array(
            'name' => '证件号码',
        ),
        'ROOMNO' => array(
            'name' => '房号',
        ),
        'HOUSEAREA' => array(
            'name' => '房屋面积（平米）',
            'dataType' => 'number'
        ),
        'HOUSETOTAL' => array(
            'name' => '房屋总价（元）',
            'dataType' => 'number'
        ),
        'SIGNTIME' => array(
            'name' => '签约日期',
        ),
        'SIGNEDSUITE' => array(
            'name' => '签约套数',
        ),
        'INVOICE_STATUS' => array(
            'name' => '发票状态',
            'map' => array(
                1 => '未开票',
                2 => '申请中',
                3 => '已开票',
            )
        ),
        'TOTAL_PRICE_AFTER' => array(
            'name' => '后佣收费标准',
        ),
        'PERCENT' => array(
            'name' => '结款比例（%）'
        ),
        'AMOUNT' => array(
            'name' => '结款金额（元）'
        ),
        'AGENCY_REWARD_AFTER' => array(
            'name' => '后佣中介佣金',
        ),
        'AGENCY_DEAL_REWARD' => array(
            'name' => '中介成交奖励',
        ),
        'PROPERTY_DEAL_REWARD' => array(
            'name' => '置业顾问成交奖励',
        ),
        'OUT_REWARD' => array(
            'name' => '外部成交奖励',
        )
    );

    /***TAB页参数数组集合***/
    private $_merge_url_param = array();

    /**子页签编号**/
    private $_tab_number = 4;

    /**业务类型字符串描述**/
    private $_case_type = 'fx';

    //构造函数
    public function __construct() {
        parent::__construct();

        // 权限映射表
        $this->authorityMap = array(
            'agency_reward_reim' => self::AGENCY_REWARD_REIM,
            'agency_deal_reward_reim' => self::AGENCY_DEAL_REWARD_REIM,
            'property_deal_reward_reim' => self::PROPERTY_DEAL_REWARD_REIM,
            'apply_billing' => self::APPLY_BILLING,
            'applyInvoice' => self::APPLYINVOICE,
            'sub_reim_apply' => self::SUB_REIM_APPLY,
            'related_my_loan' => self::RELATED_MY_LOAN,
        );

        /***TAB URL参数***/

        //项目编号
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
        //页签编号
        $this->_merge_url_param['TAB_NUMBER'] = $this->_tab_number;
        //业务类型
        $this->_merge_url_param['CASE_TYPE'] = $this->_case_type;
        //工作流类型
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        //业务案例ID
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        //工作流业务编号
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = intval($_GET['RECORDID']) : '';
        //工作流编号
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = intval($_GET['flowId']) : '';
        //工作流操作类型
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = strip_tags($_GET['operate']) : '';

        $this->user_city_py = trim($_SESSION['uinfo']['city_py']);

        //项目权限判断
        self::project_auth($this->_merge_url_param['prjid'], 2, $this->_merge_url_param['flowId']);
    }

    public function index() {
        $hasTabAuthority = $this->checkTabAuthority(4);
        if ($hasTabAuthority['result']) {
            $roleInfo = D('erp_role')->where("LOAN_ROLEID = {$hasTabAuthority['roleID']}")->find();
            $url = U("{$roleInfo['LOAN_ROLECONTROL']}/{$roleInfo['LOAN_ROLEACTION']}", $this->_merge_url_param);
            halt2('', $url);
            return;
        }
    }


    /**
     * +----------------------------------------------------------
     * 分销用户管理
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function manage() {
        $id = !empty($_POST['ID']) ? intval($_POST['ID']) : 0;
        $faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
        $uid = intval($_SESSION['uinfo']['uid']);
        $username = strip_tags($_SESSION['uinfo']['tname']);

        //实例化分销会员MODEL
        $member_istribution_model = D('MemberDistribution');

        //项目MODEL	        
        $project = D('Project');

        // 修改
        if (!empty($_POST) && $faction == 'saveFormData' && $id > 0) {
            $member_fx_info = array();
            $member_fx_info['REALNAME'] = u2g($_POST['REALNAME']);
            $member_fx_info['MOBILENO'] = $_POST['MOBILENO'];
            $member_fx_info['ROOMNO'] = u2g($_POST['ROOMNO']);
            $member_fx_info['CERTIFICATE_TYPE'] = u2g($_POST['CERTIFICATE_TYPE']);
            $member_fx_info['IDCARDNO'] = u2g($_POST['IDCARDNO']);

            if ($member_fx_info['CERTIFICATE_TYPE'] == 1) {
                if (!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $member_fx_info['IDCARDNO'])) {
                    $result['status'] = 0;
                    $result['msg'] = '添加失败，身份证号码格式不正确！';

                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }
            }

            $member_fx_info['HOUSETOTAL'] = $_POST['HOUSETOTAL'];
            $member_fx_info['HOUSEAREA'] = $_POST['HOUSEAREA'];
            $member_fx_info['SIGNEDSUITE'] = $_POST['SIGNEDSUITE'];
            $member_fx_info['SIGNTIME'] = $_POST['SIGNTIME'];
            $member_fx_info['INVOICE_STATUS'] = $_POST['INVOICE_STATUS'];

            /**
             * 未开状态，不可修改
             * 已申请状态，无法修改状态
             * 已开未领状态，可以修改为已领或已收回
             * 已领状态，可以修改为已收回
             * 已收回状态，无法修改状态
             */
            $invoicestatus_old = intval($_POST['INVOICE_STATUS_OLD']);
            if ($invoicestatus_old == 1 && $member_fx_info['INVOICE_STATUS'] != 1) {
                $result['status'] = 0;
                $result['msg'] = g2u('修改失败,违反发票状态规则：未开状态，不可修改');

                echo json_encode($result);
                exit;
            } else if ($invoicestatus_old == 2 && !in_array($member_fx_info['INVOICE_STATUS'], array(2, 3, 4))) {
                $result['status'] = 0;
                $result['msg'] = g2u('修改失败,违反发票状态规则：已开未领状态，只可以修改为已领或已收回');

                echo json_encode($result);
                exit;
            } else if ($invoicestatus_old == 3 && !in_array($member_fx_info['INVOICE_STATUS'], array(2, 3, 4))) {
                $result['status'] = 0;
                $result['msg'] = g2u('修改失败,违反发票状态规则：已领状态，只可以修改为已开未领或已收回');

                echo json_encode($result);
                exit;
            } else if ($invoicestatus_old == 4 && $member_fx_info['INVOICE_STATUS'] != 4) {
                $result['status'] = 0;
                $result['msg'] = g2u('修改失败,违反发票状态规则：已收回状态，不可修改');

                echo json_encode($result);
                exit;
            } else if ($invoicestatus_old == 5 && $member_fx_info['INVOICE_STATUS'] != 5) {
                $result['status'] = 0;
                $result['msg'] = g2u('修改失败,违反发票状态规则：申请中状态，不可修改');

                echo json_encode($result);
                exit;
            }
            $member_fx_info['INVOICE_NO'] = $_POST['INVOICE_NO'];
            $member_fx_info['AGENCY_REWARD'] = $_POST['AGENCY_REWARD'];
            /**中介佣金报销金额修改时，查看会员是否已经申请过中介佣金报销**/
            if ($member_fx_info['AGENCY_REWARD'] != floatval($_POST['AGENCY_REWARD_OLD'])) {
                $reim_deital_model = D('ReimbursementDetail');
                $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id, 9);

                if ($is_reimed) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,中介佣金已申请报销,无法修改!');

                    echo json_encode($result);
                    exit;
                }
            }
            $member_fx_info['AGENCY_DEAL_REWARD'] = $_POST['AGENCY_DEAL_REWARD'];
            /**中介成交奖励报销金额修改时，查看会员是否已经申请过中介成交奖励报销**/
            if ($member_fx_info['AGENCY_DEAL_REWARD'] != floatval($_POST['AGENCY_DEAL_REWARD_OLD'])) {
                $reim_deital_model = D('ReimbursementDetail');
                $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id, 10);

                if ($is_reimed) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,中介成交奖励已申请报销,无法修改!');

                    echo json_encode($result);
                    exit;
                }
            }
            //$member_fx_info['PROPERTY_REWARD'] = $_POST['PROPERTY_REWARD'];
            $member_fx_info['PROPERTY_DEAL_REWARD'] = $_POST['PROPERTY_DEAL_REWARD'];
            /**置业顾问成交奖励金额修改时，查看会员是否已经申请过置业顾问成交奖励报销**/
            if ($member_fx_info['PROPERTY_DEAL_REWARD'] != floatval($_POST['PROPERTY_DEAL_REWARD_OLD'])) {
                $reim_deital_model = D('ReimbursementDetail');
                $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id, 12);

                if ($is_reimed) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,置业顾问成交奖励已申请报销,无法修改!');

                    echo json_encode($result);
                    exit;
                }
            }
            $member_fx_info['TOTAL_PRICE'] = floatval($_POST['TOTAL_PRICE']);
            //当单套收费标准是百分比类型时，提醒房屋总价必须填写
            if ($member_fx_info['TOTAL_PRICE'] > 0) {
                //设置收费标准
                $feescale = array();
                $feescale = $project->get_feescale_by_cid_vaild($_POST['CASE_ID'], 1);

                $fees_arr = array();
                if (is_array($feescale) && !empty($feescale)) {
                    foreach ($feescale as $key => $value) {
                        if ($member_fx_info['TOTAL_PRICE'] == $value['AMOUNT']) {
                            if ($value['STYPE'] == 1 && $member_fx_info['HOUSETOTAL'] == 0) {
                                $result['status'] = 0;
                                $result['msg'] = '修改分销会员信息失败，单套收费标准为百分比，房屋总价必须填写';

                                $result['msg'] = g2u($result['msg']);
                                echo json_encode($result);
                                exit;
                            } else {
                                break;
                            }
                        }
                    }
                }
            }
			if ($member_fx_info['AGENCY_REWARD'] > 0) {
                //设置收费标准
                $feescale = array();
                $feescale = $project->get_feescale_by_cid_vaild($_POST['CASE_ID'], 2);
 
                $fees_arr = array();
                if (is_array($feescale) && !empty($feescale)) {
                    foreach ($feescale as $key => $value) {
                        if ($member_fx_info['AGENCY_REWARD'] == $value['AMOUNT']) { 
                            if ($value['STYPE'] == 1 && $member_fx_info['HOUSETOTAL'] == 0) {
                                $result['status'] = 0;
                                $result['msg'] = '修改分销会员信息失败，中介佣金为百分比，房屋总价必须填写';

                                $result['msg'] = g2u($result['msg']);
                                echo json_encode($result);
                                exit;
                            } else {
                                break;
                            }
                        }
                    }
                }
            }
			if ($member_fx_info['AGENCY_DEAL_REWARD'] > 0) {
                //设置收费标准
                $feescale = array();
                $feescale = $project->get_feescale_by_cid_vaild($_POST['CASE_ID'], 4);

                $fees_arr = array();
                if (is_array($feescale) && !empty($feescale)) {
                    foreach ($feescale as $key => $value) {
                        if ($member_fx_info['AGENCY_DEAL_REWARD'] == $value['AMOUNT']) {
                            if ($value['STYPE'] == 1 && $member_fx_info['HOUSETOTAL'] == 0) {
                                $result['status'] = 0;
                                $result['msg'] = '修改分销会员信息失败，中介成交奖励为百分比，房屋总价必须填写';

                                $result['msg'] = g2u($result['msg']);
                                echo json_encode($result);
                                exit;
                            } else {
                                break;
                            }
                        }
                    }
                }
            }
			if ($member_fx_info['PROPERTY_DEAL_REWARD'] > 0) {
                //设置收费标准
                $feescale = array();
                $feescale = $project->get_feescale_by_cid_vaild($_POST['CASE_ID'], 5);

                $fees_arr = array();
                if (is_array($feescale) && !empty($feescale)) {
                    foreach ($feescale as $key => $value) {
                        if ($member_fx_info['PROPERTY_DEAL_REWARD'] == $value['AMOUNT']) {
                            if ($value['STYPE'] == 1 && $member_fx_info['HOUSETOTAL'] == 0) {
                                $result['status'] = 0;
                                $result['msg'] = '修改分销会员信息失败，置业顾问成交奖励为百分比，房屋总价必须填写';

                                $result['msg'] = g2u($result['msg']);
                                echo json_encode($result);
                                exit;
                            } else {
                                break;
                            }
                        }
                    }
                }
            }
            $member_fx_info['NOTE'] = u2g($_POST['NOTE']);
            $member_fx_info['UPDATETIME'] = date('Y-m-d H:i:s');

            $update_num = $member_istribution_model->update_info_by_id($id, $member_fx_info);

            if ($update_num > 0) {
                $result['status'] = 2;
                $result['msg'] = '修改分销会员信息成功';
            } else {
                $result['status'] = 0;
                $result['msg'] = '修改分销会员信息失败！';
            }

            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        } //新增
        else if (!empty($_POST) && $faction == 'saveFormData') {
            $member_fx_info = array();
            $member_fx_info['PRJ_ID'] = intval($_POST['PRJ_ID']);
            $member_fx_info['CASE_ID'] = intval($_POST['CASE_ID']);
            $member_fx_info['CITY_ID'] = intval($_SESSION['uinfo']['city']);
            $member_fx_info['REALNAME'] = u2g($_POST['REALNAME']);
            $member_fx_info['MOBILENO'] = strip_tags($_POST['MOBILENO']);
            $member_fx_info['ROOMNO'] = u2g($_POST['ROOMNO']);
            $member_fx_info['CERTIFICATE_TYPE'] = intval($_POST['CERTIFICATE_TYPE']);
            $member_fx_info['IDCARDNO'] = u2g($_POST['IDCARDNO']);
            if ($member_fx_info['CERTIFICATE_TYPE'] == 1) {
                if (!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $member_fx_info['IDCARDNO'])) {
                    $result['status'] = 0;
                    $result['msg'] = '添加失败，身份证号码格式不正确！';

                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }
            }

            $member_fx_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
            $member_fx_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            $member_fx_info['FILINGTIME'] = strip_tags($_POST['FILINGTIME']);
            if ($member_fx_info['FILINGTIME'] == '') {
                $result['status'] = 0;
                $result['msg'] = '新增失败，报备时间必须填写';

                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }

            $member_fx_info['SIGNEDSUITE'] = $_POST['SIGNEDSUITE'];
            $member_fx_info['SIGNTIME'] = $_POST['SIGNTIME'];
            $member_fx_info['INVOICE_STATUS'] = $_POST['INVOICE_STATUS'];
            $member_fx_info['INVOICE_NO'] = $_POST['INVOICE_NO'];
            $member_fx_info['AGENCY_DEAL_REWARD'] = $_POST['AGENCY_DEAL_REWARD'];
            $member_fx_info['AGENCY_REWARD'] = $_POST['AGENCY_REWARD'];
            $member_fx_info['PROPERTY_REWARD'] = $_POST['PROPERTY_REWARD'];
            $member_fx_info['PROPERTY_DEAL_REWARD'] = $_POST['PROPERTY_DEAL_REWARD'];
            $member_fx_info['TOTAL_PRICE'] = floatval($_POST['TOTAL_PRICE']);
            //当单套收费标准是百分比类型时，提醒房屋总价必须填写
            if ($member_fx_info['TOTAL_PRICE'] > 0) {
                //设置收费标准
                $feescale = array();
                $feescale = $project->get_feescale_by_cid_vaild($member_fx_info['CASE_ID'], 1);

                $fees_arr = array();
                if (is_array($feescale) && !empty($feescale)) {
                    foreach ($feescale as $key => $value) {
                        if ($member_fx_info['TOTAL_PRICE'] == $value['AMOUNT']) {
                            if ($value['STYPE'] == 1 && $member_fx_info['HOUSETOTAL'] == 0) {
                                $result['status'] = 0;
                                $result['msg'] = '新增失败，单套收费标准为百分比，房屋总价必须填写';

                                $result['msg'] = g2u($result['msg']);
                                echo json_encode($result);
                                exit;
                            } else {
                                break;
                            }
                        }
                    }
                }
            }
			
			if ($member_fx_info['AGENCY_REWARD'] > 0) {  
                //设置收费标准
                $feescale = array();
                $feescale = $project->get_feescale_by_cid_vaild($member_fx_info['CASE_ID'], 2);

                $fees_arr = array();
                if (is_array($feescale) && !empty($feescale)) {
                    foreach ($feescale as $key => $value) {
                        if ($member_fx_info['AGENCY_REWARD'] == $value['AMOUNT']) {
                            if ($value['STYPE'] == 1 && $member_fx_info['HOUSETOTAL'] == 0) {
                                $result['status'] = 0;
                                $result['msg'] = '新增失败，中介佣金为百分比，房屋总价必须填写';

                                $result['msg'] = g2u($result['msg']);
                                echo json_encode($result);
                                exit;
                            } else {
                                break;
                            }
                        }
                    }
                }
            }
			if ($member_fx_info['AGENCY_DEAL_REWARD'] > 0) {
                //设置收费标准
                $feescale = array();
                $feescale = $project->get_feescale_by_cid_vaild($member_fx_info['CASE_ID'], 4);

                $fees_arr = array();
                if (is_array($feescale) && !empty($feescale)) {
                    foreach ($feescale as $key => $value) {
                        if ($member_fx_info['AGENCY_DEAL_REWARD'] == $value['AMOUNT']) {
                            if ($value['STYPE'] == 1 && $member_fx_info['HOUSETOTAL'] == 0) {
                                $result['status'] = 0;
                                $result['msg'] = '新增失败，中介成交奖励为百分比，房屋总价必须填写';

                                $result['msg'] = g2u($result['msg']);
                                echo json_encode($result);
                                exit;
                            } else {
                                break;
                            }
                        }
                    }
                }
            }
			if ($member_fx_info['PROPERTY_DEAL_REWARD'] > 0) {
                //设置收费标准
                $feescale = array();
                $feescale = $project->get_feescale_by_cid_vaild($member_fx_info['CASE_ID'], 5);

                $fees_arr = array();
                if (is_array($feescale) && !empty($feescale)) {
                    foreach ($feescale as $key => $value) {
                        if ($member_fx_info['PROPERTY_DEAL_REWARD'] == $value['AMOUNT']) {
                            if ($value['STYPE'] == 1 && $member_fx_info['HOUSETOTAL'] == 0) {
                                $result['status'] = 0;
                                $result['msg'] = '新增失败，置业顾问成交奖励为百分比，房屋总价必须填写';

                                $result['msg'] = g2u($result['msg']);
                                echo json_encode($result);
                                exit;
                            } else {
                                break;
                            }
                        }
                    }
                }
            }
            $member_fx_info['NOTE'] = u2g($_POST['NOTE']);
            $member_fx_info['CREATETIME'] = date('Y-m-d H:i:s');
            $member_fx_info['UPDATETIME'] = $member_fx_info['CREATETIME'];
            $member_fx_info['ADD_UID'] = intval($_SESSION['uinfo']['uid']);

            $insert_id = $member_istribution_model->add_member_info($member_fx_info);

            if ($insert_id > 0) {
                $result['status'] = 2;
                $result['msg'] = '添加分销会员信息成功';
            } else {
                $result['status'] = 0;
                $result['msg'] = '添加分销会员信息失败！';
            }

            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        } else if ($faction == 'delData') {
            $result['status'] = 0;
            $result['msg'] = '删除失败，暂时不支持删除功能';

            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        } else {
            Vendor('Oms.Form');
            $form = new Form();

            $modify_id = !empty($_GET['ID']) ? intval($_GET['ID']) : 0;

            /***数据展示条件***/
            $case_id = 0;
            if ($modify_id > 0) {
                $search_field = array('CASE_ID', 'ADD_UID');
                $userinfo = $member_istribution_model->get_info_by_id($modify_id, $search_field);

                $case_id = !empty($userinfo['CASE_ID']) ? intval($userinfo['CASE_ID']) : 0;
                $add_uid = !empty($userinfo['ADD_UID']) ? intval($userinfo['ADD_UID']) : 0;
            } else if (!empty($this->_merge_url_param['CASEID'])) {
                $case_id = intval($this->_merge_url_param['CASEID']);
            } else {
                $case_info = array();
                $project_case_model = D('ProjectCase');
                $case_info = $project_case_model->get_info_by_pid($prjid, 'fx', array('ID'));
                $case_id = !empty($case_info[0]['ID']) ? $case_info[0]['ID'] : 0;
            }

            //查询条件
            $cond_where = '';
            if ($case_id > 0) {
                $case_id > 0 ? $cond_where .= "CASE_ID = '" . $case_id . "'" : "";
            } else {
                $cond_where .= "1 = 0";
            }

            $form = $form->initForminfo(154)->where($cond_where);

            //会员MODEL
            $member_model = D('Member');

            /***设置证件类型***/
            $certificate_type_arr = $member_model->get_conf_certificate_type();
            $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR',
                array2listchar($certificate_type_arr), FALSE);

            /***发票状态***/
            if ($_GET['showForm'] == 1) {
                $form->setMyField('INVOICE_STATUS', 'READONLY', '0', FALSE);
            }
            $conf_invoice_status = $member_model->get_conf_invoice_status_remark();
            $form = $form->setMyField('INVOICE_STATUS', 'LISTCHAR',
                array2listchar($conf_invoice_status['INVOICE_STATUS']), FALSE);

            if ($_GET['showForm'] == 3) {
                //新增时候展示认购时间、签约时间默认
                $current_time = date('Y-m-d H:i:s');
                $form->setMyField('FILINGTIME', 'READONLY', '0', FALSE);
                $form->setMyFieldVal('FILINGTIME', $current_time, false);
            }

            //表单页面
            if ($_GET['showForm'] > 0) {
                //修改记录ID
                $modify_id = !empty($_GET['ID']) ? intval($_GET['ID']) : 0;

                $project_info = $project->get_info_by_id($prjid);
                $project_name = !empty($project_info[0]['PROJECTNAME']) ? $project_info[0]['PROJECTNAME'] : '';

                //设置项目名称
                $form = $form->setMyFieldVal('PRJ_NAME', $project_name, TRUE);

                //设置经办人信息
                if ($add_uid) {
                    $form = $form->setMyFieldVal('ADD_UID', $add_uid, TRUE);
                } else {
                    $form = $form->setMyFieldVal('ADD_UID', $uid, TRUE);
                }

                //设置案例编号
                $form = $form->setMyFieldVal('CASE_ID', $case_id, TRUE);

                $input_arr = array(
                    array('name' => 'PRJ_ID', 'val' => $prjid, 'class' => 'PRJ_ID'),
                );
                $form->addHiddenInput($input_arr);
            }

            //设置收费标准
            $feescale = array();
            $feescale = $project->get_feescale_by_cid_vaild($case_id);

            $fees_arr = array();
            if (is_array($feescale) && !empty($feescale)) {
                foreach ($feescale as $key => $value) {
					if($value['ISVALID']==-1){
						$unit = $value['STYPE'] == 0 ? self::UNIT_RMB_YUAN : self::UNIT_PERCENT; // 解决BUG #15383
						$fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'] . $unit;
					}

                }

                //单套收费标准
                $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
                //中介佣金
                $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
                //置业顾问佣金
                $form->setMyField('PROPERTY_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                //中介成交奖
                $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                //置业成交奖金
                $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);
            }

            if ($this->_merge_url_param['flowId'] > 0) {
                //工作流入口编辑权限
                $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

                if ($flow_edit_auth) {
                    //允许编辑
                    $form->EDITABLE = -1;
                    $form->GABTN = '';
                    $form->ADDABLE = '0';
                } else {
                    //删除
                    $form->DELCONDITION = '1==0';
                    //编辑
                    $form->EDITCONDITION = '1==0';
                    $form->ADDABLE = '0';
                    $form->GABTN = '';
                }
            } else {
                //$form->EDITCONDITION = '%INVOICE_STATUS% == 1';
            }

            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, array(
                '_add' => 501,
                '_edit' => 502,
                '_check' => 503,
            ));
            $formHtml = $form->getResult();
            $this->assign('isShowOptionBtn', $this->isShowOptionBtn($case_id));
            $this->assign('form', $formHtml);
			$this->assign('case_id', $case_id);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
            $this->assign('prjid', $prjid);
            $this->display('manage');
        }
    }


    /**
     * +----------------------------------------------------------
     * 开票管理
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function open_billing_record() {
        $Memberdistribution_model = D("MemberDistribution");

        $id = !empty($_REQUEST['ID']) ? intval($_REQUEST['ID']) : 0;
        $faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = !empty($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
        $uid = intval($_SESSION['uinfo']['uid']);
        //$username = strip_tags($_SESSION['uinfo']['tname']);
        $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;

        if ($faction == 'delData' && $id > 0) {
            D()->startTrans();
            $dbResult = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$id}")->delete();
            if ($dbResult !== false) {
                $dbResult = D('erp_billing_record')->where("ID = {$id}")->delete();
            }
            if ($dbResult !== false) {
                D()->commit();
                $result = array(
                    'status' => 'success',
                    'msg' => g2u('删除成功')
                );
            } else {
                D()->rollback();
                $result = array(
                    'status' => 'error',
                    'msg' => g2u('删除失败')
                );
            }
            echo json_encode($result);
            exit;
        }

        $project_case_model = D('ProjectCase');
        $case_info = $project_case_model->get_info_by_pid($prjid, 'fx', array('ID'));
        $case_id = !empty($case_info) ? intval($case_info[0]['ID']) : 0;

        if ($faction == 'saveFormData' && $case_id == 0) {
            $result['status'] = 0;
            $result['msg'] = g2u('添加开票记录失败,项目无分销案例信息！');
            echo json_encode($result);
            exit;
        }

        //根据CASEID获取合同信息
        $contract_model = D('Contract');
        $contract_info = $contract_model->get_contract_info_by_caseid($case_id);
        $contract_id = !empty($contract_info) ? $contract_info[0]['ID'] : 0;

        //开票MODEL
        $billing_record_model = D('BillingRecord');

        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(136);

        // 修改
        if (!empty($_POST) && $faction == 'saveFormData' && $id > 0) {
            //判断当前用户是否有权限修改开票信息
            $kp_info = array();
            $kp_info['INVOICE_MONEY'] = $_POST['INVOICE_MONEY'];
            $kp_info['CREATETIME'] = empty($_POST['CREATETIME']) ? date('Y-m-d H:i:s') : $_POST['CREATETIME'];
            $kp_info['REMARK'] = u2g(strip_tags($_POST['REMARK']));
			$kp_info["INVOICE_CLASS"] =  $_POST["INVOICE_CLASS"];
			$kp_info["INVOICE_BIZ_TYPE"] =  $_POST["INVOICE_BIZ_TYPE"];  // 开票类型
			$kp_info["FILES"] =  u2g($_POST["FILES"]);  // 文件上传
            $taxrate = get_taxrate_by_citypy($this->user_city_py);
            $kp_info["TAX"] = round($kp_info['INVOICE_MONEY'] / (1 + $taxrate) * $taxrate, 2);
            $insert_id = $billing_record_model->update_info_by_id($id, $kp_info);

            if ($insert_id > 0) {
                $result['status'] = 1;
                $result['msg'] = '修改开票记录成功';
            } else {
                $result['status'] = 0;
                $result['msg'] = '修改开票记录失败！';
            }

            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        } //新增
        else if (!empty($_POST) && $faction == 'saveFormData' && $id == 0) {

            //判断当前用户是否有权限添加开票
            $kp_info = array();
            $kp_info['CASE_ID'] = $case_id;
            $kp_info['CONTRACT_ID'] = $contract_id;
            $kp_info['INVOICE_MONEY'] = $_POST['INVOICE_MONEY'];
            $kp_info['CREATETIME'] = empty($_POST['CREATETIME']) ? date('Y-m-d H:i:s') : $_POST['CREATETIME'];;
            $kp_info['APPLY_USER_ID'] = $uid;
            $kp_info['REMARK'] = u2g(strip_tags($_POST['REMARK']));
            $kp_info['INVOICE_TYPE'] = 3;
            $kp_info['STATUS'] = 1;
			$kp_info["INVOICE_CLASS"] =  $_REQUEST["INVOICE_CLASS"];
			$kp_info["INVOICE_BIZ_TYPE"] =  $_REQUEST["INVOICE_BIZ_TYPE"];  // 发票类型：1=广告费，2=服务费
            $kp_info["FILES"] = u2g($_REQUEST['FILES']);
            $city_id = $_SESSION["uinfo"]["city"];
            $city_py = D("Erp_city")->field("PY")->find($city_id);
            $city_py = $city_py["PY"];
            $taxrate = get_taxrate_by_citypy($city_py);
            //var_dump($taxrate);
            $kp_info["TAX"] = round($kp_info['INVOICE_MONEY'] / (1 + $taxrate) * $taxrate, 2);
           // var_dump($_REQUEST);die;
            $insert_id = $billing_record_model->add_billing_info($kp_info);

            if ($insert_id > 0) {
                //添加成功，关联分销会员，并修改会员发票状态为申请中
                $memberid_str = rtrim($_REQUEST["memberid_str"], ",");
                $Memberdistribution_model = D("MemberDistribution");
                $update_arr = array("RELATE_INVOICE_ID" => $insert_id, "INVOICE_STATUS" => 5);
                $cond_where = "ID IN($memberid_str)";
                $res = $Memberdistribution_model->update_info_by_cond($update_arr, $cond_where);
                //echo M()->_sql();die;
                $result['status'] = 2;
                $result['msg'] = '添加开票记录成功';
            } else {
                $result['status'] = 0;
                $result['msg'] = '添加开票记录失败！';
            }

            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        } //删除
        else if ($faction == 'delData' && $id > 0) {
            D()->startTrans();
            $dbResult = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$id}")->delete();
            if ($dbResult !== false) {
                $dbResult = D('erp_billing_record')->where("ID = {$id}")->delete();
            }
            if ($dbResult !== false) {
                D()->commit();
                $result = array(
                    'status' => 1,
                    'msg' => g2u('删除成功')
                );
            } else {
                D()->rollback();
                $result = array(
                    'status' => 0,
                    'msg' => g2u('删除失败')
                );
            }
            echo json_encode($result);
            exit;
//            $cond_where = "RELATE_INVOICE_ID = $id";
//            $update_num = $Memberdistribution_model->update_info_by_cond(array("RELATE_INVOICE_ID" => NULL, "INVOICE_STATUS" => 1), $cond_where);
        } else {
            if (!empty($contract_info) && is_array($contract_info)) {
                $cond_where = "CONTRACT_ID = '" . $contract_info[0]['ID'] . "' AND CASE_ID = '" . $case_id . "'";
            } else {
                $cond_where = "0 = 1  AND CASE_ID = '" . $case_id . "'";
            }
        }

        $form = $form->where($cond_where);

        if ($showForm == 1) {
            $form = $form->setMyFieldVal('APPLY_USER_ID', $_SESSION["uinfo"]["uid"], TRUE);
            $form = $form->setMyFieldVal('CREATETIME', null, TRUE);

        }

        if ($showForm == 3) {
            $memberid = $_REQUEST["memberid"];

            //判断所选的分销会员是否已经申请过开票的，如果有，提示不允许在此申请
            if ($_REQUEST["is_ajax"] == 1) {
                $mem_invoice_info = $Memberdistribution_model->get_info_by_ids($memberid, array("RELATE_INVOICE_ID"));
                //var_dump($mem_invoice_info);die;
                if (!empty($mem_invoice_info[0]["RELATE_INVOICE_ID"])) {
                    $result["state"] = 0;
                    $result["msg"] = g2u("所选择的分销会员记录中存在已经申请开票记录，不能重复申请，请重新选择");
                    echo json_encode($result);
                    exit();
                } else {
                    $result["state"] = 1;
                    echo json_encode($result);
                    exit();
                }
            }
            $form->FORMCHANGEBTN = ' ';
            $form = $form->setMyFieldVal('APPLY_USER_ID', $_SESSION["uinfo"]["uid"], TRUE);
            $form = $form->setMyFieldVal('INVOICE_BIZ_TYPE', 2, false);  // 分销的开票类型默认为服务费，对应的值为2
            $form = $form->setMyFieldVal('CREATETIME', date("Y-m-d H:i:s"), TRUE)->setMyFieldVal('STATUS', 1);
        }

        if ($showForm != 3) {
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        }

        if ($this->_merge_url_param['flowId'] > 0) {
            //工作流入口编辑权限
            $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

            if ($flow_edit_auth) {
                //允许编辑
                $form->EDITABLE = -1;
                $form->GABTN = '';
                $form->ADDABLE = '0';
            } else {
                $form->DELCONDITION = '1==0';
                $form->EDITCONDITION = '1==0';
                $form->ADDABLE = '0';
                $form->GABTN = '';
            }
        } else {
            $form->ADDABLE = 0;
            $form->GABTN = "<a id='applyInvoice' href='javascript:;' class='btn btn-info btn-sm'>提交开票申请</a>";
            $form->GABTN .= "<a id='changeInvoice' href='javascript:;' class='btn btn-info btn-sm'>申请换票</a>";
            $form->GABTN .= "<a id='refundInvoice' href='javascript:;' class='btn btn-info btn-sm'>申请退票</a>";
            $form->SHOWCHECKBOX = "-1";
            $form->DELCONDITION = "%STATUS% == 1";
            $form->EDITCONDITION = "%STATUS% == 1";
        }

        $invoice_status = $billing_record_model->get_invoice_status_remark();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($invoice_status), TRUE);
        $form->setMyField('INVOICE_MONEY', 'READONLY', -1)
            ->setMyField('REMARK', 'READONLY', -1);
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, array(
            '_edit' => 727,
            '_check' => 516,
            '_del' => 728
        ));

        $childrenParams = $this->_merge_url_param;
        $childrenParams['from'] = 'open_billing_record';
        $form->setChildren(array(
            array('开票明细', U('MemberDistribution/invoice_payment_history', $childrenParams))
        ));
        $hasOtherIncome = D('House')->where("PROJECT_ID = {$prjid}")->getField('OTHERINCOME');
        // 如果有其他收入，则显示新增按钮
        if ($hasOtherIncome == -1) {
            $form->GABTN = sprintf("%s%s", "<a id='add_invoice' href='javascript:;' class='btn btn-info btn-sm'>新增开票</a>", $form->GABTN);
        }

        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->assign('case_id', $case_id);
        $this->assign('contract_id', $contract_id);
        $this->assign('project_id', $prjid);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('billing_record');
    }


    /**
     * +----------------------------------------------------------
     * 回款管理
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function received_payments_record() {
        $id = !empty($_POST['ID']) ? intval($_POST['ID']) : 0;
        $faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = !empty($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
        $uid = intval($_SESSION['uinfo']['uid']);
        $username = strip_tags($_SESSION['uinfo']['tname']);
        $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;

        $project_case_model = D('ProjectCase');
        $case_info = $project_case_model->get_info_by_pid($prjid, 'fx', array('ID'));
        $case_id = !empty($case_info) ? intval($case_info[0]['ID']) : 0;

        if ($case_id > 0) {
            $cond_where = "CASE_ID = '" . $case_id . "'";
        } else {
            $cond_where = "0 = 1";
        }

        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(135)->where($cond_where);

        $ofInvoiceNoSql = <<<SQL
                SELECT B.ID,
                       B.INVOICE_NO
                FROM ERP_BILLING_RECORD B
                WHERE B.STATUS = 4
                  AND B.CASE_ID = {$case_id}
SQL;
        $form->setMyField('BILLING_RECORD_ID', 'LISTSQL', $ofInvoiceNoSql);
        if ($this->_merge_url_param['flowId'] > 0) {
            //工作流入口编辑权限
            $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

            if ($flow_edit_auth) {
                //允许编辑
                $form->EDITABLE = -1;
                $form->GABTN = '';
                $form->ADDABLE = '0';
            } else {
                $form->DELCONDITION = '1==0';
                $form->EDITCONDITION = '1==0';
                $form->ADDABLE = '0';
                $form->GABTN = '';
            }
        } else {
            $form->EDITABLE = 0;
            $form->ADDABLE = 0;
        }

        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('received_payments_record');
    }


    /**
     * +----------------------------------------------------------
     * 会员费用报销管理
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function reim_manage() {
        //报销MODEL
        $reim_type_model = D('ReimbursementType');
        $reim_list_model = D('ReimbursementList');
        $reim_detail_model = D('ReimbursementDetail');

        $uid = intval($_SESSION['uinfo']['uid']);
        $city = $this->channelid;
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
        $ID = intval(($_GET['ID']));

        Vendor('Oms.Form');
        $form = new Form();

        if ($faction == 'delData') {
            $list_id = intval($_GET['ID']);

            $del_list_result = FALSE;
            $del_detail_result = FALSE;

            if ($list_id > 0) {
                $del_list_result = $reim_list_model->del_reim_list_by_ids($list_id);
                $detailList = $reim_detail_model->field('id, type, business_id, business_parent_id')->where("LIST_ID = {$list_id} AND STATUS = 0")->select();

                $dbResult = false;
                D()->startTrans();
                foreach ($detailList as $item) {
                    $dbResult = $reim_detail_model->handleDelReimDetail($item);
                    if ($dbResult === false) {
                        break;
                    }
                }

                if ($dbResult !== false) {
                    D()->commit();
                } else {
                    D()->rollback();
                    $info['status'] = 'error';
                    $info['msg'] = g2u('报销明细删除失败');
                    echo json_encode(array(
                        'status' => 'error',
                        'msg' => g2u('更新报销明细对应的数据失败，删除数据失败')
                    ));
                    exit(0);
                }

                if ($del_list_result) {
                    $del_detail_result = $reim_detail_model->del_reim_detail_by_listid($list_id);
                }

                //删除关联借款关系
                $loan_model = D('Loan');
                $up_num_loan = $loan_model->cancleRelatedLoan($list_id);
            }

            if ($del_list_result > 0 && $del_detail_result > 0) {
                $info['status'] = 'success';
                $info['msg'] = g2u('删除成功');
            } else if (!$del_detail_result) {
                $info['status'] = 'error';
                $info['msg'] = g2u('报销明细删除失败');
            } else {
                $info['status'] = 'error';
                $info['msg'] = g2u('删除失败');
            }

            echo json_encode($info);
            exit;
        }

        if ($showForm == 1 && $faction == "saveFormData" && $ID > 0) {
            $updateData['ATTACHMENT'] = u2g($_REQUEST['ATTACHMENT']);
            D()->startTrans();
            $dbResult = D('ReimbursementList')->where("ID = {$ID}")->save($updateData);
            if ($dbResult !== false) {
                D()->commit();
                ajaxReturnJSON(true, g2u('修改成功'));
            } else {
                D()->rollback();
                ajaxReturnJSON(false, g2u('修改失败'));
            }
        }

        //项目下分销业务CASE_ID 
        $case_info = array();
        $proejct_case_model = D('ProjectCase');
        $form = $form->initForminfo(176);

        $form->SQLTEXT = '(select a.*,b.LOANMONEY  from ERP_REIMBURSEMENT_LIST a
        left join (select  APPLICANT,sum(UNREPAYMENT) as LOANMONEY from ERP_LOANAPPLICATION where STATUS IN(2,6) AND CITY_ID = '. $city . ' group by APPLICANT) b
        on a.APPLY_UID = b.APPLICANT )';

        $case_info = $proejct_case_model->get_info_by_pid($this->_merge_url_param['prjid'], 'fx');

        if (!empty($case_info) && is_array($case_info)) {
            $case_id = intval($case_info[0]['ID']);

            $cond_where = " CASE_ID = '" . $case_id . "' and STATUS<>4";
            //$cond_where = " CASE_ID = 242";
            $reim_list_id_arr = $reim_detail_model->get_detail_info_by_cond($cond_where, array('LIST_ID'));

            $list_id_arr = array();
            if (!empty($reim_list_id_arr)) {
                foreach ($reim_list_id_arr as $key => $value) {
                    if (!in_array($value['LIST_ID'], $list_id_arr)) {
                        $list_id_arr[$key] = $value['LIST_ID'];
                    }
                }
            }

            $list_id_str = implode(',', $list_id_arr);

            $cond_where = "ID IN ($list_id_str) AND CITY_ID = '" . $city . "' AND TYPE IN (22,11,23,17,24) AND STATUS != 4";
        } else {
            $cond_where = " 1 = 0";
        }

        $form->where($cond_where);

        //设置报销单类型
        $type_arr = $reim_type_model->get_reim_type();
        $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);

        //设置报销单状态
        $status_arr = $reim_list_model->get_conf_reim_list_status_remark();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($status_arr), FALSE);

        //详情页
        if ($showForm == 1) {
            //审核人
            $form = $form->setMyField('REIM_UID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
        }

        if ($this->_merge_url_param['flowId'] > 0) {
            //工作流入口编辑权限
            $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

            if ($flow_edit_auth) {
                //允许编辑
                $form->EDITABLE = -1;
                $form->GABTN = "<a id='related_my_loan' href='javascript:;'  class='btn btn-info btn-sm'>关联借款</a>";
                $form->ADDABLE = '0';
            } else {
                $form->DELCONDITION = '1==0';
                $form->EDITCONDITION = '1==0';
                $form->ADDABLE = '0';
                $form->GABTN = '';
            }
        } else {
            //根据状态控制删除按钮是否显示
            $form->DELCONDITION = '%STATUS% == 0';
            $form->GABTN = "<a id='sub_reim_apply' href='javascript:;' class='btn btn-info btn-sm'>提交报销申请</a>  "
                . "<a id='related_my_loan' href='javascript:;' class='btn btn-info btn-sm'>关联借款</a>"
                ."<a id = 'show_flow_step'  href='javascript:;' class='btn btn-info btn-sm'>超额报销流程图</a>";
        }

        $children_data = array(
            array('报销明细', U('/MemberDistribution/reim_detail_manage', $this->_merge_url_param)),
            array('关联借款', U('Loan/related_loan', $this->_merge_url_param))
        );

        $form = $form->setChildren($children_data);
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, array(
            '_check' => 521,
            '_del' => 522
        ));
        $form->EDITCONDITION = "%STATUS% == 0";
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
        $this->assign('form', $formHtml);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('reim_manage');
    }


    /**
     * +----------------------------------------------------------
     * 报销明细
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function reim_detail_manage() {
        $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
        $uid = intval($_SESSION['uinfo']['uid']);
        $city = $this->channelid;
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';

        //报销申请单MODEL
        $reim_list_model = D('ReimbursementList');
        //报销MODEL
        $reim_detail_model = D('ReimbursementDetail');
        //报销类型
        $reim_type_model = D('ReimbursementType');
        $id = $_GET['ID'];

        $reim_list_status = null;  // 报销申请单的状态

        Vendor('Oms.Form');
        $form = new Form();
        // ----begin:中介佣金部分----
        $parentId = $_REQUEST['parentchooseid'];  // 父窗体ID
        $reimType = $reim_list_model->where("ID = {$parentId}")->getField('TYPE');

        // 分销后佣中介佣金修改
        if ($reimType == 17 && $showForm == 1 && $faction == 'saveFormData') {
            if (intval($id)) {
                // 保存修改金额
                $sql = <<<REIM_DETAIL_SQL
                    SELECT
                    c.card_member_id, d.reim_list_id, d.reim_detail_id, d.post_commission_id
                    FROM erp_commission_reim_detail d
                    LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
                    WHERE d.id = {$id}
REIM_DETAIL_SQL;
                $dbResult = D()->query($sql);
                if (notEmptyArray($dbResult)) {
                    $memId = $dbResult[0]['CARD_MEMBER_ID'];
                    $reimDetailId = $dbResult[0]['REIM_DETAIL_ID'];
                    $reimListId = $dbResult[0]['REIM_LIST_ID'];
                    $comisId = $dbResult[0]['POST_COMMISSION_ID'];
                    $remainReimAmount = D('ReimbursementList')->getRemainFxPostComisReimAmount($memId, $comisId);
                    $remainReimAmount = floatval($remainReimAmount) + floatval($_POST['AMOUNT_OLD']);
                    if ($remainReimAmount < floatval(trim($_POST['AMOUNT']))) {
                        ajaxReturnJSON('error', g2u(sprintf('申请报销金额超过了最大可申请额度%s，不能申请', $remainReimAmount)));
                    }
                    D()->startTrans();
                    $dbResult = D('erp_commission_reim_detail')->where("ID = {$id}")->save(array(
                        'PERCENT' => trim($_POST['PERCENT']),
                        'AMOUNT' => trim($_POST['AMOUNT'])
                    ));
                    if ($dbResult !== false) {
                        $dbResult = D('erp_post_commission')->where("ID = {$comisId}")->save(array('UPDATETIME' => date('Y-m-d H:i:s')));
                    }

                    if ($dbResult !== false) {
                        $updateData['MONEY'] = trim($_POST['AMOUNT']);
                        $updateData['ISKF'] = trim($_POST['ISKF']);
                        $dbResult = D('ReimbursementDetail')->where("ID = {$reimDetailId}")->save($updateData);
                    }

                    if ($dbResult !== false) {
                        $reimTotalAmount = D('ReimbursementDetail')->get_sum_total_money_by_listid($reimListId);
                        $dbResult = D('ReimbursementList')->update_reim_list_amount($reimListId, $reimTotalAmount, 'cover');
                    }

                    if ($dbResult !== false) {
                        D()->commit();
                        ajaxReturnJSON(1, g2u('数据保存成功'));
                    } else {
                        D()->rollback();
                        ajaxReturnJSON(0, g2u('数据保存失败'));
                    }
                }
                // 修改报销明细金额和报销单金额
            }

            ajaxReturnJSON(0, g2u('数据保存失败'));
        }

        if ($reimType == 17 && $faction != 'delData') {
            $sql = <<<SQL
            (SELECT
            m.REALNAME,
            m.HOUSETOTAL,
            m.TOTAL_PRICE,
            concatUnit(m.AGENCY_REWARD_AFTER, f1.Stype) AGENCY_REWARD_AFTER,
            m.PRJ_NAME,
            m.ID AS MEMBER_ID,
            m.MOBILENO,
            d.amount,
            d.percent,
            d.status,
            d.reim_list_id,
            d.reim_detail_id,
            d.id,
            c.INVOICE_STATUS,
            c.PAYMENT_STATUS,
            r.isfundpool,
            r.iskf
            FROM erp_commission_reim_detail d
            LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
            LEFT JOIN erp_reimbursement_detail r ON r.id = d.reim_detail_id
            LEFT JOIN erp_cardmember m ON m.id = c.card_member_id
            LEFT JOIN ERP_FEESCALE f1 ON f1.case_id = m.case_id AND f1.amount = m.AGENCY_REWARD_AFTER AND f1.SCALETYPE = 2 AND f1.ISVALID = -1 AND f1.MTYPE = 1)
SQL;
            Vendor('Oms.Form');
            $form = new Form();
            $form->initForminfo(207);
            $form->SQLTEXT = $sql;
            $form->FKFIELD = 'REIM_LIST_ID';
            $form->EDITCONDITION = '%STATUS% == 1';
            $form->DELCONDITION = '%STATUS% == 1';
            $form->setMyField('MEMBER_ID', 'READONLY', -1)
                ->setMyField('INVOICE_STATUS', 'READONLY', -1)
                ->setMyField('PAYMENT_STATUS', 'READONLY', -1)
                ->setMyField('ISKF', 'FORMVISIBLE', -1);
            if (intval($id)) {
                $totalAmount = $this->getCommissionTotalAmount(2, $id);
                $this->assign('total_amount', $totalAmount);
            }

            $this->assign('html', $form->getResult());
            $this->display('commission_reim_history');
            exit();
        }
        // ----end:中介佣金部分----
        $cond_where = "LIST_ID = '" . $list_id . "' AND STATUS != 4";
        $form = $form->initForminfo(179)->where($cond_where);
        $this->setTotalPriceUnit($this->_request('prjid'), $this->_request('CASE_TYPE'), $form);

        if ($faction == 'delData') {
            $id = intval($_GET['ID']);
            if ($reimType == 17) {
                $bizId = $id;
                $id = D('erp_commission_reim_detail')->where("ID = {$id}")->getField("REIM_DETAIL_ID");
            }

            //数据验证
            //删除明细剩余金额不能小于借款金额
            if(D("Loan")->checkDelReim($list_id,$id)){
                $info['status']  = 'error';
                $info['msg']  = g2u('对不起，您此报销单关联的借款金额已大于报销金额，删除失败!');
                die(json_encode($info));
            }

            $del_detail_result = FALSE;
            $up_list_result = FALSE;
            $rmBizResult = true;  // 删除对应的业务记录
            $commDataProcessResult = true;  // 对于中介成交奖励等的操作结果
            D()->startTrans();
            if ($id > 0) {
                $cardMemId = $reim_detail_model->where("ID ={$id}")->getField('BUSINESS_ID');
                if ($reimType == 17) {
                    $rmBizResult = D('erp_commission_reim_detail')->where("ID = {$bizId}")->delete();
                }
                $del_detail_result = $reim_detail_model->del_reim_detail_by_id($id);
                if ($reimType != 17) {
                    $up_list_result = true;
                    $updateStatusData = array();
                    switch($reimType) {
                        case 22:
                            $updateStatusData['AGENCY_DEAL_REWARD_STATUS'] = 1;
                            break;
                        case 23:
                            $updateStatusData['PROPERTY_DEAL_REWARD_STATUS'] = 1;
                            break;
                        default:
                            $updateStatusData['OUT_REWARD_STATUS'] = 1;
                    }
                    $commDataProcessResult = D('Member')->where("ID = {$cardMemId}")->save($updateStatusData);
                }

                if ($del_detail_result && $rmBizResult) {
                    $total_amount = $reim_detail_model->get_sum_total_money_by_listid($list_id);
                    $up_list_result = $reim_list_model->update_reim_list_amount($list_id, $total_amount, 'cover');
                }
            }

            if ($del_detail_result > 0 && $up_list_result > 0 && $commDataProcessResult !== false) {
                D()->commit();
                $info['status'] = 'success';
                $info['msg'] = g2u('删除成功');
            } else if (!$up_list_result) {
                D()->rollback();
                $info['status'] = 'error';
                $info['msg'] = g2u('报销申请单金额更新失败');
            } else {
                D()->rollback();
                $info['status'] = 'error';
                $info['msg'] = g2u('删除失败');
            }

            echo json_encode($info);
            exit;
        }

        if ($this->_merge_url_param['flowId'] > 0) {
            //工作流入口编辑权限
            $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

            if ($flow_edit_auth) {
                //允许编辑
                $form->EDITABLE = '-1';
                $form->DELABLE = '-1';
            } else {
                //删除
                $form->DELCONDITION = '1==0';
                //编辑
                $form->EDITCONDITION = '1==0';
                $form->GABTN = '';
            }
        } else {
            /***根据状态控制编辑删除按钮是否显示***/
            $list_info = $reim_list_model->get_info_by_id($list_id, array('STATUS'));
            $reim_list_status = !empty($list_info[0]['STATUS']) ? intval($list_info[0]['STATUS']) : 0;
            $conf_reim_list_status = $reim_list_model->get_conf_reim_list_status();

            if ($conf_reim_list_status['reim_list_no_sub'] == $reim_list_status) {
                $form->EDITABLE = '-1';
                $form->DELABLE = '-1';
            } else {
                $form->EDITABLE = 0;
                $form->DELABLE = 0;
            }
        }

        $member_model = D('Member');

        //设置会员来源
        $source_arr = $member_model->get_conf_member_source_remark();
        $form = $form->setMyField('SOURCE', 'LISTCHAR',
            array2listchar($source_arr), FALSE);

        //经办人
        $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);

        //设置证件类型
        $certificate_type_arr = $member_model->get_conf_certificate_type();
        $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR',
            array2listchar($certificate_type_arr), FALSE);

        /***发票状态***/
        $conf_invoice_status = $member_model->get_conf_invoice_status_remark();
        if (in_array($reimType, array(17, 22, 23, 24))) {
            // 如果是从分销佣金管理报销的，则显示后佣的发票状态及回款状态
            $form->setMyField('INVOICE_STATUS', 'LISTCHAR', array2listchar(array(
                1 => '未开票',
                2 => '部分开票',
                3 => '完成开票'
            )));

            $form->setMyField("PAYMENT_STATUS", "READONLY", -1)
                ->setMyField("MONEY", "READONLY", -1, true);
        } else {
            $form = $form->setMyField('INVOICE_STATUS', 'LISTCHAR',
                array2listchar($conf_invoice_status['INVOICE_STATUS']), FALSE);
            $form->setMyField("PAYMENT_STATUS", "FORMVISIBILE", 0)
                ->setMyField("PAYMENT_STATUS", "GRIDVISIBILE", 0);
        }


        //设置付款方式
        $member_pay = D('MemberPay');
        $pay_arr = $member_pay->get_conf_pay_type();
        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE);

        //设置报销单类型
        $type_arr = $reim_type_model->get_reim_type();
        $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);

        //报销明细状态数组
        $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);

		$rdata = $_REQUEST;
		$rdata['list_id'] = $list_id;
        // 根据showForm的状态自定义字段属性
        $this->customFieldsProps($showForm, $form);
        $this->handleFormSubmit($showForm, $faction, $rdata);

        /***根据状态控制编辑删除按钮是否显示***/
        $reim_list_status = $reim_list_model->where("ID = {$list_id}")->getField('STATUS');
        if ($reim_list_status == 0) {
            $status0html = '<a class="contrtable-link fedit btn btn-primary btn-xs" onclick="editThis(this);" title="编辑"  href="javascript:void(0);">
			<i class="glyphicon glyphicon-edit"></i>
			</a>
			<a class="contrtable-link fedit btn btn-success btn-xs" onclick="viewThis(this);" title="查看"   href="javascript:void(0);">
			<i class="glyphicon glyphicon-eye-open"></i>
			</a>
			<a class="contrtable-link btn btn-danger btn-xs" onclick="delThis(this);"  title="删除" href="javascript:void(0);">
			<i class="glyphicon glyphicon-trash"></i>
			</a>';
        } else {
            $status0html = '<a class="contrtable-link fedit btn btn-success btn-xs" onclick="viewThis(this);" title="查看"   href="javascript:void(0);">
			<i class="glyphicon glyphicon-eye-open"></i>
			</a>';
        }


		$form->CZBTN = array(
			'%STATUS%==0' => $status0html ,

			'%STATUS%==1' => ' 
			<a class="contrtable-link fedit btn btn-success btn-xs" onclick="viewThis(this);" title="查看"   href="javascript:void(0);">
			<i class="glyphicon glyphicon-eye-open"></i>
			</a>'
			  ,
				  '%STATUS%==3' => ' 
			<a class="contrtable-link fedit btn btn-success btn-xs" onclick="viewThis(this);" title="查看"   href="javascript:void(0);">
			<i class="glyphicon glyphicon-eye-open"></i>
			</a>'
			  ,
				  '%STATUS%==4' => ' 
			<a class="contrtable-link fedit btn btn-success btn-xs" onclick="viewThis(this);" title="查看"   href="javascript:void(0);">
			<i class="glyphicon glyphicon-eye-open"></i>
			</a>'
			  ,
		);

       // $form = $form->getResult();
        //$this->assign('form', $form);
        $form->setMyField('INPUT_TAX','GRIDVISIBLE',0,false);
        $form->setMyField('INPUT_TAX','FORMVISIBLE',0,false);
//        $form->setMyField('AGENCY_REWARD',"EDITTYPE",12,false);
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
        $this->assign('form', $formHtml);
 
		$this->assign('PID', $_REQUEST['prjid']);
		$this->assign('MID', $_REQUEST['parentchooseid']);
        $this->display('reim_detail_manage');
    }

    /**
     *
     */
    public function exportMembers() {
        try {
            $caseID = intval($_REQUEST['CASEID']);
            if (!empty($_REQUEST['flowId']) && intval($_REQUEST['flowId']) > 0) {
                $cond_where = "FLOW_ID=" . $_REQUEST["flowId"];
                $invoice_info = D('BillingRecord')->get_info_by_cond($cond_where,array("ID"));
                $invoiceID = $invoice_info[0]["ID"];
            } else {
                $invoiceID = intval($_REQUEST['invoiceId']);
            }

            // 获取开票记录
            $invoice = D()->query(sprintf(self::EXPORT_INVOICE_SQL, $caseID, $invoiceID));
            // 获取会员列表
            $members = D()->query(sprintf(self::EXPORT_MEMBER_SQL, $caseID, $invoiceID));

            $this->initExport($objPHPExcel, $objActSheet, self::WORK_SHEET_TITLE, self::DEFAULT_EXCEL_COLUMN_WIDTH, self::DEFAULT_EXCEL_ROW_HEIGHT);
            $row = 1;

            $this->exportInvoiceData($objActSheet, $invoice, $row);
            $row += 2;
            $this->exportMembersData($objActSheet, $members, $row);
            ob_end_clean();
            ob_start();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");
            header("Content-Disposition:attachment;filename=" . self::WORK_SHEET_TITLE . date("YmdHis") . ".xls");
            header("Content-Transfer-Encoding:binary");

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
        } catch (Exception $e) {
            die(sprintf("%s:%s", $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * 根据showForm状态自定义界面的字段属性
     * @param $showForm
     * @param $form
     */
    private function customFieldsProps($showForm, &$form) {
        if (empty($showForm) || empty($form)) {
            return;
        }

        if ($showForm == 1) {  // 如果处于编辑状态
            // 设置只读字段
            $form->setMyField('TYPE', 'READONLY', -1);
            $form->setMyField('MOBILENO', 'READONLY', -1);
            $form->setMyField('MONEY', 'READONLY', -1);
            $form->setMyField('STATUS', 'READONLY', -1);
            $form->setMyField('PROPERTY_DEAL_REWARD', 'READONLY', -1);
            $form->setMyField('AGENCY_REWARD', 'READONLY', -1);
            $form->setMyField('AGENCY_DEAL_REWARD', 'READONLY', -1);
            $form->setMyField('INVOICE_STATUS', 'READONLY', -1);

            // 设置扣费字段为可修改字段
            $form->setMyField('ISKF', 'READONLY', 0);
			$form->setMyField('MONEY', 'READONLY', 0);
        }

    }

    /**
     * 处理表单的提交
     * @param $showForm
     * @param $faction
     * @param $data
     */
    private function handleFormSubmit($showForm, $faction, $data) {
        // 完成数据修改
        if ($showForm == self::FORM_EDIT_STATUS && $faction == self::SAVE_FORM_ACTION) {
            $dbModel = D('ReimbursementDetail');
            if (empty($data['ID'])) {
                js_alert('项目编号不能为空', U('MemberDistribution/reim_detail_manage', $this->_merge_url_param));
            }

            $toUpdateData = array(
                'ID' => $data['ID'],
                'ISKF' => $data['ISKF'],
				'MONEY' => $data['MONEY']
            );

            $dbModel->startTrans();

            $affected = $dbModel->where('ID = ' . $toUpdateData['ID'])->save($toUpdateData);
			if($data['MONEY']!=$data['MONEY_OLD']){
				$one = M('Erp_reimbursement_list')->where("ID=".$data['list_id'])->find();
				$amount = $one['AMOUNT']-$data['MONEY_OLD']+$data['MONEY'];
				$temp['AMOUNT'] = $amount;
				$affected1 = M('Erp_reimbursement_list')->where("ID=".$data['list_id'])->save($temp);

			}else $affected1=true;
            if ($affected !== false  && $affected1!==false) {
                $dbModel->commit();
                $result = array(
                    'status' => '1',
                    'msg' => g2u('保存成功')
                );
            } else {
                $dbModel->rollback();
                $result = array(
                    'status' => 0,
                    'msg' => g2u('保存失败')
                );
            }

            echo json_encode($result);
            exit;  // 中止后续的执行
        }
    }

    private function getFeeScle($projID, $caseType) {
        if (empty($projID)) {
            return;
        }

        $knownCaseTypes = D('ProjectCase')->get_conf_case_type();
        $scaleType = $knownCaseTypes[$caseType];
        if ($scaleType) {
            $where = "PROJECT_ID = {$projID} AND SCALETYPE = {$scaleType}";
            $caseId = D('ProjectCase')->where($where)->getField('ID');
            if ($caseId !== false && !empty($caseId)) {
                return D('Project')->get_feescale_by_cid_vaild($caseId);
            }
        }

        return false;
    }

    private function setTotalPriceUnit($projID, $caseType, &$form) {
        $feescale = $this->getFeeScle($projID, $caseType);
        if (is_array($feescale) && !empty($feescale)) {
            foreach ($feescale as $key => $value) {
                if (($value['SCALETYPE'] == 1 && $value['MTYPE'] != 1) or ($value['SCALETYPE'] == 2 && $value['MTYPE'] != 1)) {
                    continue;
                }
                $unit = $value['STYPE'] == 0 ? self::UNIT_RMB_YUAN : self::UNIT_PERCENT; // 解决BUG #15383
                $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'] . $unit;
            }

            //单套收费标准
            $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
            //中介佣金
            $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
            //外部成交奖励
            $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
            //中介成交奖
            $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
            //置业成交奖金
            $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);

        }
    }

    /**
     * 导出会员数据
     * @param $objActSheet
     * @param $members
     * @param $row
     */
    private function exportMembersData(&$objActSheet, $members, &$row) {
        $this->commonExportAction($objActSheet, $members, $row, self::EXCEL_MEMBER_TITLE, $this->outputMember, array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        ));
    }

    /**
     * @param $objActSheet worksheet对象
     * @param $invoice 开票数据
     * @param $row 起始行
     */
    private function exportInvoiceData(&$objActSheet, $invoice, &$row) {
        $this->commonExportAction($objActSheet, $invoice, $row, self::WORK_SHEET_TITLE, $this->outputInvoice, array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        ));
    }

    public function commission_manage() {
        $faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';  // 操作
        $id = !empty($_REQUEST['ID']) ? intval($_REQUEST['ID']) : 0;
        $prjId = $_GET['prjid'];  // 项目ID
        $caseInfo = D('ProjectCase')->get_info_by_pid($prjId, 'fx', array('ID'));
        $caseId = !empty($caseInfo) ? intval($caseInfo[0]['ID']) : 0;
        $this->assign('prjId', $prjId);
        $this->assign('caseId', $caseId);

        if ($faction == 'delData') {  // 删除选中的后佣记录
            // 判断是否符合删除条件
            if (empty($id)) {
                echo json_encode(array(
                    'status' => 0,
                    'msg' => g2u('不存在对应的ID')
                ));
                exit;
            }
            $sql = <<<COMIS_STATUS_SQL
                SELECT p.invoice_status,
                       p.payment_status,
                       p.post_commission_status,
                       c.agency_reward_status,
                       c.agency_deal_reward_status,
                       c.property_deal_reward_status,
                       c.out_reward_status,
                       p.card_member_id
                FROM erp_post_commission p
                LEFT JOIN erp_cardmember c ON c.id = p.card_member_id
                WHERE p.id = {$id}
COMIS_STATUS_SQL;
            $dbAllResult = D()->query($sql);
            if (notEmptyArray($dbAllResult)) {
                $dbResult = $dbAllResult[0];
            }
            $cardMembId = $dbResult['CARD_MEMBER_ID'];

            if ($dbResult['INVOICE_STATUS'] == 1 && $dbResult['PAYMENT_STATUS'] == 1
                && $dbResult['POST_COMMISSION_STATUS'] == 1) {
                if ($dbResult['AGENCY_DEAL_REWARD_STATUS'] == 1
                    && $dbResult['PROPERTY_DEAL_REWARD_STATUS'] == 1
                    && $dbResult['OUT_REWARD_STATUS'] == 1) {

                } else {
                    // 如果至少有一条记录是在后佣管理添加的，则不可以删除
                    $dbResult = D()->query(sprintf("
                            SELECT count(1) cnt
                            FROM erp_reimbursement_detail d
                            LEFT JOIN erp_reimbursement_list l ON l.id = d.list_id
                            WHERE d.case_id = %d
                              AND l.type IN (22, 23, 24)
                              AND d.status <> 4
                              AND d.business_id IN
                                (SELECT c.card_member_id
                                 FROM erp_post_commission c
                                 WHERE c.id = %d)", $caseId, $id));
                    if ($dbResult !== false && notEmptyArray($dbResult) && intval($dbResult[0]['CNT']) > 0) {
                        echo json_encode(array(
                            'status' => 0,
                            'msg' => g2u('不符合删除条件，无法删除该条记录')
                        ));
                        exit;
                    }
                }
            } else {
                echo json_encode(array(
                    'status' => 0,
                    'msg' => g2u('不符合删除条件，无法删除该条记录')
                ));
                exit;
            }

            // 检查是否有申请中的开票申请
            $applyingCnt = D('erp_commission_invoice_detail')->where("POST_COMMISSION_ID = {$id}")->count();
            if ($applyingCnt) {
                echo json_encode(array(
                    'status' => 0,
                    'msg' => g2u('存在开票申请，不能删除记录')
                ));
                exit;
            }

            // 检查是否有申请中的报销申请
            $applyingCnt = D('erp_commission_reim_detail')->where("POST_COMMISSION_ID = {$id}")->count();
            if ($applyingCnt) {
                echo json_encode(array(
                    'status' => 0,
                    'msg' => g2u('存在后佣中介佣金报销申请，不能删除记录')
                ));
                exit;
            }

            // 删掉与该条记录管理的开票记录、中介佣金记录等
            D()->startTrans();
            $dbResult = D('erp_commission_invoice_detail')->where("POST_COMMISSION_ID = {$id}")->delete();
            if ($dbResult !== false) {
                $dbResult = D('erp_commission_reim_detail')->where("POST_COMMISSION_ID = {$id}")->delete();
            }
            if ($dbResult !== false) {
                $sql = <<<DEL_SQL
                DELETE
                FROM erp_billing_record
                WHERE id IN
                    (SELECT t.billing_record_id
                     FROM erp_commission_invoice_detail t
                     WHERE t.post_commission_id = {$id})
DEL_SQL;
                $dbResult = D('erp_billing_record')->query($sql);
            }

            // 更改对应分销会员的状态值，改为未申请状态
            if ($dbResult !== false) {
                $dbResult = D('Member')->where("ID = {$cardMembId}")->save(array('REWARD_STATUS' => 1));
            }

            // 删除该条记录
            if ($dbResult !== false) {
                $dbResult = D('erp_post_commission')->where("ID = {$id}")->delete();
            }

            if ($dbResult !== false) {
                D()->commit();
                $result = array(
                    'status' => 'success',
                    'msg' => g2u('删除成功')
                );
            } else {
                D()->rollback();
                $result = array(
                    'status' => 'error',
                    'msg' => g2u('删除失败')
                );
            }
            echo json_encode($result);
            exit;
        }

        $sql = <<<SQL
            (SELECT
            m.PRJ_NAME,
            m.REALNAME,
            m.MOBILENO,
            m.ROOMNO,
            m.HOUSEAREA,
            m.HOUSETOTAL,
            m.TOTAL_PRICE_AFTER,
            m.AGENCY_REWARD_AFTER,
            m.PROPERTY_DEAL_REWARD,
            m.PROPERTY_DEAL_REWARD_STATUS,
            m.AGENCY_DEAL_REWARD,
            m.AGENCY_DEAL_REWARD_STATUS,
            m.SOURCE,
            m.OUT_REWARD,
            m.OUT_REWARD_STATUS,
            m.SIGNTIME,
            m.SIGNEDSUITE,
            m.CERTIFICATE_TYPE,
            m.CERTIFICATE_NO,
            m.DIRECTSALLER,
            to_char(m.FILINGTIME,'yyyy-MM-dd') FILINGTIME,
            m.ADD_USERNAME,
            c.INVOICE_STATUS,
            c.PAYMENT_STATUS,
            c.POST_COMMISSION_STATUS,
            c.CARD_MEMBER_ID,
            c.ID
            FROM erp_post_commission c
            LEFT JOIN erp_cardmember m ON m.id = c.card_member_id
            WHERE m.CITY_ID = {$this->channelid}
             AND m.case_id = {$caseId}
             AND m.STATUS = 1)
SQL;

        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(205);
        $form->EDITABLE = 0;
        $form->DELCONDITION = '%INVOICE_STATUS% == 1 AND %PAYMENT_STATUS% == 1 AND %POST_COMMISSION_STATUS% == 1 AND %AGENCY_DEAL_REWARD_STATUS% == 0 AND %PROPERTY_DEAL_REWARD_STATUS% == 0 AND %OUT_REWARD_STATUS% == 0';
        $form->SQLTEXT = $sql;
        $childrenParams = $this->_merge_url_param;
        $childrenParams['from'] = 'commission_manage';
        // 设置佣金显示单位
        $rawFeeScaleList = D('Project')->get_feescale_by_cid_vaild($caseId);
        $mappedFeeScaleList = array();
        if (notEmptyArray($rawFeeScaleList)) {
            foreach ($rawFeeScaleList as $k => $v) {
                if ($v['ISVALID'] == -1) {
                    if ($v['SCALETYPE'] == 1 && $v['MTYPE'] != 1) {
                        continue;
                    }

                    $mappedFeeScaleList[$v['SCALETYPE']][$v['AMOUNT']] = sprintf("%s%s", $v['AMOUNT'], $v['STYPE'] == 0 ? self::UNIT_RMB_YUAN : self::UNIT_PERCENT);
                }
            }
        }
        $form->setMyField('DIRECTSALLER','FORMVISIBLE',-1);
        $form->setMyField('DIRECTSALLER','GRIDVISIBLE',-1);
        $form->setMyField('TOTAL_PRICE_AFTER', 'LISTCHAR', array2listchar($mappedFeeScaleList[1]), FALSE); // 后佣单套收费标准
        $form->setMyField('AGENCY_REWARD_AFTER', 'LISTCHAR', array2listchar($mappedFeeScaleList[2]), FALSE); // 后佣中介佣金
        $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($mappedFeeScaleList[3]), FALSE); // 外部成交奖励
        $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($mappedFeeScaleList[4]), FALSE); // 中介成交奖
        $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($mappedFeeScaleList[5]), FALSE); // 置业成交奖金
        $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR', array2listchar(D('Member')->get_conf_certificate_type()), FALSE);  // 证件类型
//        $form->setMyField('CERTIFICATE_NO', 'ENCRY', '4,13', FALSE); // 证件号码加密

        $form->setChildren(array(
            array('开票回款记录', U('MemberDistribution/invoice_payment_history', $childrenParams)),
            array('中介佣金报销', U('MemberDistribution/commission_reim_history', $childrenParams))
        ));

        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->assign('html', $form->getResult());
        $this->assign('prjId', $prjId);
        //添加搜索条件
        $this->assign('filter_sql',$form->getFilterSql());
        //添加排序条件
        $this->assign('sort_sql',$form->getSortSql());
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->display('commission_manage');
    }

    public function invoice_payment_history() {
        $showForm = trim($_REQUEST['showForm']);
        $faction = trim($_REQUEST['faction']);
        $dbResult = false;
        // 开票记录的明细删除的逻辑操作
        if ($faction == 'delData') {
            $id = $_GET['ID'];
            if (intval($id)) {
                $dbResult = D()->query("
                    SELECT c.card_member_id, c.id, d.billing_record_id
                    FROM erp_commission_invoice_detail d
                    LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
                    WHERE d.id = {$id}
                ");

                if (notEmptyArray($dbResult)) {
                    $comisId = $dbResult[0]['ID'];
                    $billRecId = $dbResult[0]['BILLING_RECORD_ID'];
                    D()->startTrans();
                    $dbResult = D('erp_commission_invoice_detail')->where("ID = {$id}")->delete();
                    if ($dbResult !== false) {
                        $billRecAmount = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$billRecId}")->sum('AMOUNT');
                        if (floatval($billRecAmount) == 0) {
                            $dbResult = D('BillingRecord')->where("ID = {$billRecId}")->delete();
                        } else {
                            $cityPY = D('erp_city')->where("ID = {$this->channelid}")->getField('PY');
                            $taxRate = get_taxrate_by_citypy($cityPY);
                            if (floatval($taxRate) === 0) {
                                ajaxReturnJSON(0, g2u('获取城市税率失败，请稍后重试'));
                            }
                            $dbResult = D('BillingRecord')->where("ID = {$billRecId}")->save(array(
                                'INVOICE_MONEY' => $billRecAmount,
                                'TAX' => round($billRecAmount / (1 + $taxRate) * $taxRate, 2)
                            ));
                        }
                    }

                    if ($dbResult !== false) {
                        $dbResult = D('erp_post_commission')->where("ID = {$comisId}")->save(array(
                            'UPDATETIME' => date('Y-m-d H:i:s')
                        ));
                    }

                    $dbResult !== false ? D()->commit() : D()->rollback();
                }
            }

            if ($dbResult !== false) {
                $result = array(
                    'status' => 'success',
                    'msg' => g2u('删除成功')
                );
            } else {
                $result = array(
                    'status' => 'error',
                    'msg' => g2u('删除失败')
                );
            }
            echo json_encode($result);
            exit();
        }



        // 编辑保存时对数据的有效性进行验证
        if ($showForm == 1) {
            $id = $_GET['ID'];
            $totalAmount = $this->getCommissionTotalAmount(1, $id);
            $this->assign('total_amount', $totalAmount);
            if ($faction == 'saveFormData') {
                if (intval($id)) {
                    $amount = floatval($_POST['AMOUNT']);
                    $dbResult = D()->query("
                    SELECT c.card_member_id, c.id, d.billing_record_id
                    FROM erp_commission_invoice_detail d
                    LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
                    WHERE d.id = {$id}
                ");
                    if (notEmptyArray($dbResult)) {
                        $cardMemId = $dbResult[0]['CARD_MEMBER_ID']; // 会员ID
                        $comisId = $dbResult[0]['ID'];  // 报销记录ID
                        $billRecId = $dbResult[0]['BILLING_RECORD_ID'];  // 开票记录ID
                        $remainAmount = D('BillingRecord')->getRemainFxPostComisInvoiceAmount($cardMemId, $comisId);
                        $amountOld = floatval($_POST['AMOUNT_OLD']);
                        $remainAmount = floatval($remainAmount) + $amountOld;
                        if ((floatval($remainAmount) + 1) < floatval($amount)) {
                            ajaxReturnJSON(false, g2u(sprintf('申请的金额超过了最大的可申请金额%s，不能申请', $remainAmount)));
                        }

                        D()->startTrans();
                        $updateData = array();
                        if (!empty($_POST['PERCENT'])) {
                            $updateData['PERCENT'] = trim($_POST['PERCENT']);
                            $updateData['AMOUNT'] = trim($_POST['AMOUNT']);
                        }

                        if (!empty($_POST['PAYMENT_STATUS'])) {
                            $updateData['PAYMENT_STATUS'] = trim($_POST['PAYMENT_STATUS']);
                            $updateData['PAYMENT_AMOUNT'] = trim($_POST['PAYMENT_AMOUNT']);
                        }

                        $dbResult = D('erp_commission_invoice_detail')->where("ID = {$id}")->save($updateData);
                        if ($dbResult !== false) {
                            $billRecAmount = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$billRecId}")->sum('AMOUNT');
                            $cityPY = D('erp_city')->where("ID = {$this->channelid}")->getField('PY');
                            $taxRate = get_taxrate_by_citypy($cityPY);
                            if (floatval($taxRate) === 0) {
                                ajaxReturnJSON(false, g2u('获取城市税率失败，请稍后重试'));
                            }
                            $dbResult = D('BillingRecord')->where("ID = {$billRecId}")->save(array(
                                'INVOICE_MONEY' => $billRecAmount,
                                'TAX' => round($billRecAmount / (1 + $taxRate) * $taxRate, 2)
                            ));
                        }

                        if ($dbResult !== false) {
                            $req['invoice_id'] = $id;
                            $req['post_commission_id'] = $comisId;
                            $req['card_member_id'] = $cardMemId;
                            $req['detail_payment_status'] = trim($_POST['PAYMENT_STATUS']);
                            $req['total_price_after'] = $totalAmount;
                            if ($_POST['PAYMENT_STATUS']) {
                                if ($_POST['INVOICE_STATUS'] == 3 && $_POST['PAYMENT_STATUS_OLD'] != 3) {
                                    $dbResult = D('PostCommission')->updateThroughInvoiceDetailChange($req);
                                }
                            }
                        }

                        // 修改对应的会员状态

                        if ($dbResult !== false) {
                            D()->commit();
                            ajaxReturnJSON(true, g2u('数据保存成功'));
                        } else {
                            D()->rollback();
                            ajaxReturnJSON(false, g2u('数据保存失败'));
                        }
                    }
                }
                ajaxReturnJSON(false, g2u('数据保存失败'));
            }
        }
        $sql = <<<SQL
            (SELECT
            m.REALNAME,
            m.HOUSETOTAL,
            m.TOTAL_PRICE,
            m.MOBILENO,
            concatUnit(m.TOTAL_PRICE_AFTER, f4.Stype) TOTAL_PRICE_AFTER,
            d.*
            FROM erp_commission_invoice_detail d
            LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
            LEFT JOIN erp_cardmember m ON m.id = c.card_member_id
            LEFT JOIN ERP_FEESCALE f4 ON f4.case_id = m.case_id AND f4.amount = m.TOTAL_PRICE_AFTER AND f4.SCALETYPE = 1 AND f4.ISVALID = -1 and f4.MTYPE = 1)
SQL;

        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(206);
        $form->SQLTEXT = $sql;
        if ($_REQUEST['from'] == 'open_billing_record') {
            $form->FKFIELD = 'BILLING_RECORD_ID';
        } else {
            $form->FKFIELD = 'POST_COMMISSION_ID';
        }
        // 获取后佣收费标准的金额
        $id = $_GET["ID"];



        $form->DELCONDITION = '%INVOICE_STATUS% == 1 AND (%PAYMENT_STATUS% == 1 OR %PAYMENT_STATUS% == 2)';
        $form->EDITABLE = -1; // 可以编辑
        $form->EDITCONDITION = '(%INVOICE_STATUS% == 1) OR ( %INVOICE_STATUS% == 3 AND %PAYMENT_STATUS% != 3)';

        if ($showForm == 1) { // 编辑页面
            $ID = $_REQUEST['ID'];
            $dbResult = D('erp_commission_invoice_detail')->where("ID = {$ID}")->find();
            if ($dbResult['INVOICE_STATUS'] == 1) {
                $form->setMyField('PAYMENT_STATUS', 'FORMVISIBLE', 0)
                    ->setMyField('PAYMENT_AMOUNT', 'FORMVISIBLE', 0)
                    ->setMyField('INVOICE_NO', 'FORMVISIBLE', 0);
            } else if ($dbResult['INVOICE_STATUS'] == 2) {
                $form->setMyField('PAYMENT_STATUS', 'READONLY', -1)
                    ->setMyField('PAYMENT_AMOUNT', 'READONLY', -1)
                    ->setMyField('PERCENT', 'READONLY', -1)
                    ->setMyField('AMOUNT', 'READONLY', -1);
            } else {
                // 有财务管理业务回款的权限才可修改回款状态
                if (!$this->haspermission(721)) {
                    $form->setMyField('PAYMENT_STATUS', 'READONLY', -1)
                        ->setMyField('PAYMENT_AMOUNT', 'READONLY', -1);
                }
                $form->setMyField('PERCENT', 'READONLY', -1)
                    ->setMyField('AMOUNT', 'READONLY', -1);

                // 如果是完全回款，则不能修改回款状态
                if ($dbResult['PAYMENT_STATUS'] == 3) {
                    $form->setMyField('PAYMENT_STATUS', 'READONLY', -1)
                        ->setMyField('PAYMENT_AMOUNT', 'READONLY', -1);
                }
            }
        }

        $this->assign('html', $form->getResult());
        $this->display('invoice_payment_history');
    }

    public function commission_reim_history() {
        $showForm = $_REQUEST['showForm'];
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $dbResult = false;
        // 删除分销后佣申请记录
        if ($faction == 'delData') {
            $id = $_GET['ID'];
            if (intval($id)) {
                $dbResult = D('erp_commission_reim_detail')->field('REIM_LIST_ID, REIM_DETAIL_ID')->where("ID = {$id}")->find();
                if (notEmptyArray($dbResult)) {
                    $reimListId = $dbResult['REIM_LIST_ID'];
                    $reimDetailId = $dbResult['REIM_DETAIL_ID'];
                    D()->startTrans();
                    $dbResult = D('erp_commission_reim_detail')->where("ID = {$id}")->delete();
                    if ($dbResult !== false) {
                        $dbResult = D('ReimbursementDetail')->del_reim_detail_by_id($reimDetailId);
                    }
                    if ($dbResult !== false) {
                        $totalAmount = D('ReimbursementDetail')->get_sum_total_money_by_listid($reimListId);
                        $dbResult = D('ReimbursementList')->update_reim_list_amount($reimListId, $totalAmount, 'cover');
                    }
                    $dbResult === false ? D()->rollback() : D()->commit();
                }
            }

            if ($dbResult !== false) {
                $result = array(
                    'status' => 'success',
                    'msg' => g2u('删除成功')
                );
            } else {
                $result = array(
                    'status' => 'error',
                    'msg' => g2u('删除失败')
                );
            }
            echo json_encode($result);
            exit();
        }

        // 编辑时对数值进行验证
        if ($showForm == 1) {
            $id = $_GET['ID'];
            if ($faction == 'saveFormData') {
                if (intval($id)) {
                    $amount = floatval($_POST['AMOUNT']);
                    $dbResult = D()->query("
                    SELECT c.card_member_id, c.id, d.reim_detail_id, d.reim_list_id
                    FROM erp_commission_reim_detail d
                    LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
                    WHERE d.id = {$id}
                ");
                    if (notEmptyArray($dbResult)) {
                        $reimDetailId = $dbResult[0]['REIM_DETAIL_ID'];
                        $reimListId = $dbResult[0]['REIM_LIST_ID'];
                        $remainAmount = D('ReimbursementList')->getRemainFxPostComisReimAmount($dbResult[0]['CARD_MEMBER_ID'], $dbResult[0]['ID']);
                        $amountOld = floatval($_POST['AMOUNT_OLD']);
                        $remainAmount = floatval($remainAmount) + $amountOld;
                        if (floatval($remainAmount) < floatval($amount)) {
                            echo json_encode(array(
                                'status' => 0,
                                'msg' => g2u(sprintf('申请的金额超过了最大的可申请金额%s，不能申请', $remainAmount))
                            ));
                            exit;
                        }

                        D()->startTrans();
                        $dbResult = D('erp_commission_reim_detail')->where("ID = {$id}")->save(array(
                            'PERCENT' => trim($_POST['PERCENT']),
                            'AMOUNT' => trim($_POST['AMOUNT'])
                        ));

                        if ($dbResult !== false) {
                            $updateData['AMOUNT'] = $amount;
                            $updateData['ISKF'] = trim($_POST['ISKF']);
                            $dbResult = D('ReimbursementDetail')->where("ID = {$reimDetailId}")->save($updateData);
                        }

                        if ($dbResult !== false) {
                            $reimTotalAmount = D('ReimbursementDetail')->get_sum_total_money_by_listid($reimListId);
                            $dbResult = D('ReimbursementList')->update_reim_list_amount($reimListId, $reimTotalAmount, 'cover');
                        }

                        if ($dbResult !== false) {
                            D()->commit();
                            $result = array(
                                'status' => 1,
                                'msg' => g2u('数据保存成功')
                            );
                        } else {
                            D()->rollback();
                            $result = array(
                                'status' => 0,
                                'msg' => g2u('数据保存失败')
                            );
                        }
                        echo json_encode($result);
                        exit;
                    }
                }
                echo json_encode(array(
                    'status' => 0,
                    'msg' => g2u('数据保存失败')
                ));
                exit;
            } else {
                if (intval($id)) {
                    $totalAmount = $this->getCommissionTotalAmount(2, $id);
                    $this->assign('total_amount', $totalAmount);
                }
            }
        }

        $sql = <<<SQL
            (SELECT
            m.REALNAME,
            m.HOUSETOTAL,
            m.TOTAL_PRICE,
            m.PRJ_NAME,
            m.MOBILENO,
            concatUnit(m.AGENCY_REWARD_AFTER, f1.Stype) AGENCY_REWARD_AFTER,
            m.ID AS MEMBER_ID,
            r.iskf,
            r.isfundpool,
            d.amount,
            d.percent,
            d.status,
            d.reim_list_id,
            d.reim_detail_id,
            d.id,
            d.post_commission_id,
            c.INVOICE_STATUS,
            c.PAYMENT_STATUS
            FROM erp_commission_reim_detail d
            LEFT JOIN erp_reimbursement_detail r ON r.id = d.reim_detail_id
            LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
            LEFT JOIN erp_cardmember m ON m.id = c.card_member_id
            LEFT JOIN ERP_FEESCALE f1 ON f1.case_id = m.case_id AND f1.amount = m.AGENCY_REWARD_AFTER AND f1.SCALETYPE = 2 AND f1.ISVALID = -1 AND f1.MTYPE = 1)
SQL;

        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(207);
        $form->SQLTEXT = $sql;
        $form->FKFIELD = 'POST_COMMISSION_ID';
        $form->EDITCONDITION = '%STATUS% == 1';
        $form->DELCONDITION = '%STATUS% == 1';
        $form->setMyField('PRJ_NAME', 'FORMVISIBLE', 0)
            ->setMyField('PRJ_NAME', 'GRIDVISIBLE', 0)
            ->setMyField('MEMBER_ID', 'FORMVISIBLE', 0)
            ->setMyField('MEMBER_ID', 'GRIDVISIBLE', 0)
            ->setMyField('INVOICE_STATUS', 'READONLY', -1)
            ->setMyField('PAYMENT_STATUS', 'READONLY', -1);

        if ($showForm == 1) { // 编辑页面
            $ID = $_REQUEST['ID'];
            $dbResult = D('erp_commission_reim_detail')->where("ID = {$ID}")->find();
            if ($dbResult['STATUS'] == 0) {
                $form->setMyField('PAYMENT_STATUS', 'FORMVISIBLE', 0)
                    ->setMyField('PAYMENT_AMOUNT', 'FORMVISIBLE', 0);
            }
        }

        $this->assign('html', $form->getResult());
        $this->display('commission_reim_history');
    }

    public function ajaxGetCommissionData() {
        $response = array(
            'status' => false,
            'total' => 0,
            'list' => array()
        );
        $act = $_POST['act_name'];
        $comisIds = $_POST['commission_ids'];
        if (notEmptyArray($comisIds)) {
            $comisIdStr = sprintf('(%s)', implode(',', $comisIds));
            $sql = <<<MEMBER_SQL
                SELECT m.prj_name,
                       m.realname,
                       m.housetotal,
                       m.total_price_after,
                       m.agency_reward_after,
                       c.card_member_id,
                       c.id,
                       m.case_id,
                       m.source
                FROM erp_cardmember m
                LEFT JOIN erp_post_commission c ON c.card_member_id = m.id
                WHERE c.id in {$comisIdStr}
MEMBER_SQL;
            $dbMemberList = D()->query($sql);
            $mapMemberList = array();
            if ($act == 'apply_billing') {  // 申请开票
                foreach($dbMemberList as $member) {
                    if ($member['TOTAL_PRICE_AFTER'] <= 0) {
                        ajaxReturnJSON(false, g2u(sprintf('编号为%d的佣金记录后佣收费标准为0，不能申请后佣', $member['ID'])));
                    }
                    $member['TOTAL_PRICE_AFTER_AMOUNT'] = getFeeScaleAmount($member['CASE_ID'], $member['TOTAL_PRICE_AFTER'], $member['HOUSETOTAL'], $feeType);
                    $member['AMOUNT'] = D('BillingRecord')->getRemainFxPostComisInvoiceAmount($member['CARD_MEMBER_ID'], $member['ID']);
                    if ($member['AMOUNT'] <= 0) {
                        ajaxReturnJSON(false, g2u(sprintf('编号为%d的佣金记录可申请开票佣金数目为0，不能申请开票', $member['ID'])));
                    }

                    $member['REMAIN_AMOUNT'] = round($member['AMOUNT'], 2);
                    $member['PERCENT'] = round($member['AMOUNT'] * 100 / $member['TOTAL_PRICE_AFTER_AMOUNT'], 2);
                    $member['REMAIN_PERCENT'] = round($member['PERCENT'], 2);
                    if ($feeType == 1) {
                        $member['TOTAL_PRICE_AFTER'] .= self::UNIT_PERCENT;
                    } else {
                        $member['TOTAL_PRICE_AFTER'] .= self::UNIT_RMB_YUAN;
                    }
                    $mapMemberList[] = $member;

                    $response['total'] += $member['AMOUNT'];
                }
            } else if ($act == 'post_agency_reward_reim') {  // 申请中介后佣
                foreach($dbMemberList as $member) {
//                    if ($member['SOURCE'] != 1) {
//                        ajaxReturnJSON(false, g2u(sprintf('编号为%d的佣金记录的客户来源不是中介，不能申请后佣中介佣金报销', $member['ID'])));
//                    }

                    $member['AGENCY_REWARD_AFTER_AMOUNT'] = getFeeScaleAmount($member['CASE_ID'], $member['AGENCY_REWARD_AFTER'], $member['HOUSETOTAL'], $feeType);
                    if ($member['AGENCY_REWARD_AFTER'] <= 0) {
                        ajaxReturnJSON(false, g2u(sprintf('编号为%d的佣金记录后佣数目为0，不能申请后佣', $member['ID'])));
                    }

                    $member['AMOUNT'] = D('ReimbursementList')->getRemainFxPostComisReimAmount($member['CARD_MEMBER_ID'], $member['ID']);
                    if ($member['AMOUNT'] <= 0) {
                        ajaxReturnJSON(false, g2u(sprintf('编号为%d的佣金记录可申请报销佣金数目为0，不能申请报销', $member['ID'])));
                    }

                    $member['REMAIN_AMOUNT'] = round($member['AMOUNT'], 2);
                    $member['PERCENT'] = round($member['AMOUNT'] * 100 / $member['AGENCY_REWARD_AFTER_AMOUNT'], 2);
                    $member['REMAIN_PERCENT'] = round($member['PERCENT'], 2);
                    if ($feeType == 1) {
                        $member['AGENCY_REWARD_AFTER'] .= self::UNIT_PERCENT;
                    } else {
                        $member['AGENCY_REWARD_AFTER'] .= self::UNIT_RMB_YUAN;
                    }
                    $mapMemberList[] = $member;

                    $response['total'] += $member['AMOUNT'];
                }
            }
            unset($dbMemberList);

            $response['status'] = true;
            $response['list'] = $mapMemberList;
        }

        ajaxReturnJSON(true, g2u('接口调用成功'), g2u($response));
    }

    public function ajaxPostInvoice() {
        $msg = '';
        $invoiceData = $_POST['commission_data'];
        $uid = intval($_SESSION['uinfo']['uid']);
        if (intval($invoiceData['prj_id']) > 0) {
            if (count($invoiceData['list']) === 0) {
                ajaxReturnJSON(false, g2u('待开票的记录为空，不能申请开票'));
            }

            $caseInfo = D('ProjectCase')->get_info_by_pid($invoiceData['prj_id'], 'fx', array('ID'));
            $caseId = !empty($caseInfo) ? intval($caseInfo[0]['ID']) : 0;
            if (intval($caseId) === 0) {
                ajaxReturnJSON(false, g2u('获取案例编号失败'));
            }

            $contractId = D('Contract')->where("CASE_ID = {$caseId}")->getField('ID');
            if (intval($contractId) === 0) {
                ajaxReturnJSON(false, g2u('获取合同编号失败'));
            }

            $cityPY = D('erp_city')->where("ID = {$this->channelid}")->getField('PY');
            $taxRate = get_taxrate_by_citypy($cityPY);
            if (floatval($taxRate) === 0) {
                ajaxReturnJSON(false, g2u('获取城市税率失败，请稍后重试'));
            }

            // 数据的插入顺序
            // 先插入开票记录，然后将开票记录与开票明细关联
            $insertData['CASE_ID'] = $caseId;
            $insertData['CONTRACT_ID'] = $contractId;
            $insertData['INVOICE_MONEY'] = $invoiceData['total_amount'];
            $insertData['CREATETIME'] = date('Y-m-d H:i:s');
            $insertData['APPLY_USER_ID'] = $uid;
            $insertData['REMARK'] = u2g(strip_tags($invoiceData['invoice_desc']));
            $insertData['INVOICE_TYPE'] = 3;
            $insertData['STATUS'] = 1;
            $insertData["INVOICE_CLASS"] = $invoiceData["invoice_class"];
            $insertData["INVOICE_BIZ_TYPE"] = $invoiceData["invoice_biz_type"];  // 发票类型：1=广告费，2=服务费
            $insertData["TAX"] = round($insertData['INVOICE_MONEY'] / (1 + $taxRate) * $taxRate, 2);
            D()->startTrans();
            $insertedId = D('BillingRecord')->add_billing_info($insertData);
            if ($insertedId > 0) {
                foreach ($invoiceData['list'] as $invoice) {
                    $itemData['PERCENT'] = $invoice['percent'];
                    $itemData['AMOUNT'] = $invoice['amount'];
                    $itemData['REMAIN_PERCENT'] = floatval($invoice['percent']) - floatval($invoice['remain_percent'])  ;
                    $itemData['REMAIN_AMOUNT'] =  floatval($invoice['amount']) - floatval($invoice['remain_amount']);
                    if (floatval($itemData['REMAIN_PERCENT']) > 0 || floatval($itemData['REMAIN_AMOUNT']) > 1 ) {
                        $dbResult = false;
                        $msg = '结算比例或结算金额大于剩余可结算比例、金额';
                        break;
                    }
                    $itemData['BILLING_RECORD_ID'] = $insertedId;
                    $itemData['POST_COMMISSION_ID'] = $invoice['id'];
                    $itemData['STATUS'] = 1;
                    $itemData['INVOICE_STATUS'] = 1;  // 开票状态
                    $itemData['PAYMENT_STATUS'] = 1;  // 未回款状态
                    $dbResult = D('erp_commission_invoice_detail')->add($itemData);
                    // 尽早跳出循环
                    if ($dbResult === false) {
                        break;
                        $msg = '申请开票失败';
                    }
                }
            } else {
                $dbResult = false;
            }

            if ($dbResult !== false) {
                D()->commit();
                !empty($msg) or $msg = '申请开票成功';
            } else {
                D()->rollback();
                !empty($msg) or $msg = '申请开票失败';
            }
        }

        ajaxReturnJSON($dbResult, g2u($msg));
    }

    /**
     * 后佣中介佣金申请报销
     */
    public function ajaxPostCommissionReim() {
        // 分为两类：中介佣金报销和成交奖励报销
        $msg = '';
        $reqData = $_POST['commission_data'];
        $actName = $_REQUEST['act_name'];  // 报销类型名称
        $caseId = $_POST['case_id'];
        $cityId = intval($this->channelid); // 当前城市编号
        $reimType = 0;  // 报销类型
        $reimField = ''; // 报销类型对应的字段
        $reimName = '';  // 报销类型名称
        $memberIds = array();  // 用户列表
        $reimDetailList = array();  // 报销明细ID列表

        if (empty($caseId) || $caseId == 0) {
            ajaxReturnJSON(false, g2u('案例编号为空'));
        }

        D('ReimbursementList')->getReimTypeAndField($actName, $reimType, $reimField, $reimName);
        if ($reimType == 0) {
            ajaxReturnJSON(false, g2u('不支持的报销类型'));
        }
        $reimListId = D('ReimbursementList')->getNewestReimListId($reimType, $caseId, $cityId);
        if ($reimType == 17) {
            if (count($reqData['list']) === 0) {
                ajaxReturnJSON(false, g2u('待报销记录为空，不能申请报销'));
            }

            foreach ($reqData['list'] as $item) {
                $memberIds []= $item['card_member_id'];
            }
        } else {
            $memberIds = $_POST['member_ids'];
            foreach ($memberIds as $member) {
                $statusName = $reimField . '_STATUS';
                $memberInfo = D('erp_cardmember')->field(sprintf("%s, %s", $statusName, $reimField))->where("ID = {$member}")->find();
                if ($memberInfo[$statusName] != 1) {
                    ajaxReturnJSON(false, g2u(sprintf('只有处于未提交状态的%s才可申请报销，分销会员编号为%d不处于未提交状态',$reimName, $member)));
                }

                if (floatval($memberInfo[$reimField]) <= 0) {
                    ajaxReturnJSON(false, g2u(sprintf('分销会员编号为%d没有%s', $member, $reimName)));
                }
            }
        }


        $fields = array(
            'ID', 'CITY_ID', 'PRJ_ID', 'CASE_ID',
            'REALNAME', 'AGENCY_REWARD_AFTER', 'AGENCY_DEAL_REWARD',
            'PROPERTY_DEAL_REWARD', 'HOUSETOTAL', 'OUT_REWARD'
        );
        $memberList = D('Member')->get_info_by_ids($memberIds, $fields);
        // 后佣报销申请
        if ($actName == 'post_agency_reward_reim') {
            D()->startTrans();
            $dbResult = D('ReimbursementList')->agencyRewardReim($memberList, $reqData['list'], $reimListId, $reimType, $reimField, $cityId, $reimName, $msg, $reimDetailList);
            if ($dbResult !== false) {
                // 添加中介佣金报销记录
                foreach ($reqData['list'] as $item) {
                    $itemData = array();
                    $itemData['AMOUNT'] = $item['amount'];
                    $itemData['PERCENT'] = $item['percent'];
                    $itemData['REMAIN_PERCENT'] = floatval($item['remain_percent']) - floatval($item['percent']);
                    $itemData['REMAIN_AMOUNT'] = floatval($item['remain_amount']) - floatval($item['amount']);
                    if (floatval($itemData['REMAIN_PERCENT']) < 0 || floatval($itemData['REMAIN_AMOUNT']) < 0) {
                        $dbResult = false;
                        $msg = '结算比例或结算金额大于剩余可结算比例、金额';
                        break;
                    }

                    $itemData['POST_COMMISSION_ID'] = $item['id'];
                    $itemData['REIM_LIST_ID'] = $reimListId;  // 报销单编号
                    $itemData['REIM_DETAIL_ID'] = $reimDetailList[$item['id']];
                    $itemData['STATUS'] = 1;  // 未报销
                    $itemData['PAYMENT_STATUS'] = 1;  // 未回款
                    $dbResult = D('erp_commission_reim_detail')->add($itemData);

                    // 尽早跳出循环
                    if ($dbResult === false) {
                        $msg = '申请报销失败';
                        break;
                    }
                }
            }

            if ($dbResult !== false) {
                D()->commit();
                !empty($msg) or $msg = $reimName . '报销申请成功';
            } else {
                D()->rollback();
                !empty($msg) or $msg = $reimName . '中介佣金报销申请失败';
            }
        } else {
            // 中介奖励报销
            D()->startTrans();
            $dbResult = D('ReimbursementList')->agencyRewardReim($memberList, array(), $reimListId, $reimType, $reimField, $cityId, $reimName, $msg);
            if ($dbResult !== false) {
                D()->commit();
                !empty($msg) or $msg = $reimName . '报销申请成功';
            } else {
                D()->rollback();
                !empty($msg) or $msg = $reimName . '报销申请失败';
            }
        }

        ajaxReturnJSON(!!$dbResult, g2u($msg));
    }

    /**
     * 新增开票记录
     */
    public function addInvoice() {
        $this->assign('case_id', trim($_GET['case_id']));
        $this->assign('contract_id', trim($_GET['contract_id']));
        $this->assign('prj_id', trim($_GET['prjid']));
        $this->display('add_invoice');
    }

    /**
     * 导出后佣记录
     */
    public function exportPostCommission() {
        $sql = <<<SQL
            (SELECT
            m.PRJ_NAME,
            m.REALNAME,
            m.MOBILENO,
            m.ROOMNO,
            m.HOUSEAREA,
            m.HOUSETOTAL,
            concatUnit(m.AGENCY_REWARD_AFTER, f1.Stype) AGENCY_REWARD_AFTER,
            concatUnit(m.AGENCY_DEAL_REWARD, f2.Stype) AGENCY_DEAL_REWARD,
            concatUnit(m.PROPERTY_DEAL_REWARD, f3.Stype) PROPERTY_DEAL_REWARD,
            concatUnit(m.TOTAL_PRICE_AFTER, f4.Stype) TOTAL_PRICE_AFTER,
            concatUnit(m.OUT_REWARD, f5.Stype) OUT_REWARD,
            m.PROPERTY_DEAL_REWARD_STATUS,
            m.AGENCY_DEAL_REWARD_STATUS,
            m.SOURCE,
            m.OUT_REWARD_STATUS,
            to_char(m.SIGNTIME, 'YYYY-MM-DD HH24:MI:SS') AS SIGNTIME,
            m.SIGNEDSUITE,
            m.CERTIFICATE_TYPE,
            m.CERTIFICATE_NO,
            m.FILINGTIME,
            m.ADD_USERNAME,
            c.INVOICE_STATUS,
            c.PAYMENT_STATUS,
            c.POST_COMMISSION_STATUS,
            c.CARD_MEMBER_ID,
            c.ID
            FROM erp_post_commission c
            LEFT JOIN erp_cardmember m ON m.id = c.card_member_id
            LEFT JOIN ERP_FEESCALE f1 ON f1.case_id = m.case_id AND f1.amount = m.AGENCY_REWARD_AFTER AND f1.SCALETYPE = 2 AND f1.ISVALID = -1 AND f1.MTYPE = 1
            LEFT JOIN ERP_FEESCALE f2 ON f2.case_id = m.case_id AND f2.amount = m.AGENCY_DEAL_REWARD AND f2.SCALETYPE = 4 AND f2.ISVALID = -1
            LEFT JOIN ERP_FEESCALE f3 ON f3.case_id = m.case_id AND f3.amount = m.PROPERTY_DEAL_REWARD AND f3.SCALETYPE = 5 AND f3.ISVALID = -1
            LEFT JOIN ERP_FEESCALE f4 ON f4.case_id = m.case_id AND f4.amount = m.TOTAL_PRICE_AFTER AND f4.SCALETYPE = 1 AND f4.ISVALID = -1 and f4.MTYPE = 1
            LEFT JOIN ERP_FEESCALE f5 ON f5.case_id = m.case_id AND f5.amount = m.OUT_REWARD AND f5.SCALETYPE = 3 AND f5.ISVALID = -1
            WHERE m.CITY_ID = {$this->channelid}
             AND m.case_id = %d
             AND m.STATUS = 1)
SQL;
        $caseId = $_REQUEST['case_id'];
        if (intval($caseId) > 0) {
            $sql = sprintf($sql, $caseId);
            $sql = " SELECT * FROM {$sql} WHERE 1=1 ";

            if ($_GET['filter']) {
                $sql .= sprintf(" %s ", $_GET['filter']);
            }

            if ($_GET['sort']) {
                $sql .= sprintf(" %s ", $_GET['sort']);
            }
            $records = D()->query(sprintf($sql, $caseId));
        } else {
            echo <<<EOT
                <script>
                    alert('导出数据失败');
                    history.back();
                </script>
EOT;
            return;
        }

        $dataFormat = array(
            'ID' => array(
                'name' => '编号'
            ),
            'PRJ_NAME' => array(
                'name' => '项目名称'
            ),
            'CARD_MEMBER_ID' => array(
                'name' => '会员编号'
            ),
            'REALNAME' => array(
                'name' => '会员姓名'
            ),
            'MOBILENO' => array(
                'name' => '手机号'
            ),
            'ROOMNO' => array(
                'name' => '房号',
            ),
            'HOUSEAREA' => array(
                'name' => '房型面积（平米）',
            ),
            'HOUSETOTAL' => array(
                'name' => '房屋总价（元）',
            ),
            'CERTIFICATE_TYPE' => array(
                'name' => '证件类型',
                'map' => array(
                    '1' => '身份证',
                    '2' => '户口簿',
                    '3' => '军官证',
                    '4' => '士兵证',
                    '5' => '警官证',
                    '6' => '护照',
                    '7' => '台胞证',
                    '8' => '回乡证',
                    '9' => '身份证（港澳）',
                    '10' => '营业执照',
                    '11' => '法人代码',
                    '12' => '其它',
                )
            ),
            'CERTIFICATE_NO' => array(
                'name' => '证件号码',
            ),
            'SOURCE' => array(
                'name' => '客户来源',
                'map' => array(
                    "1" => '中介',
                    "2" => '渠道',
                    "3" => '数据营销',
                    "4" => '拓客',
                    "5" => '线上',
                    "6" => '自然来客'
                )
            ),
            'SIGNEDSUITE' => array(
                'name' => '签约套数',
            ),
            'SIGNTIME' => array(
                'name' => '签约日期',
            ),
            'TOTAL_PRICE_AFTER' => array(
                'name' => '后佣收费标准',
            ),
            'AGENCY_REWARD_AFTER' => array(
                'name' => '后佣中介佣金',
            ),
            'AGENCY_DEAL_REWARD' => array(
                'name' => '中介成交奖励',
            ),
            'PROPERTY_DEAL_REWARD' => array(
                'name' => '置业顾问成交奖励',
            ),
            'OUT_REWARD' => array(
                'name' => '外部成交奖励',
            ),
            'INVOICE_STATUS' => array(
                'name' => '发票状态',
                'map' => array(
                    1 => '未开票',
                    2 => '部分开票',
                    3 => '完成开票',
                )
            ),
            'PAYMENT_STATUS' => array(
                'name' => '回款状态',
                'map' => array(
                    1 => '未回款',
                    2 => '部分回款',
                    3 => '完成回款',
                )
            ),
            'POST_COMMISSION_STATUS' => array(
                'name' => '后佣中介佣金状态',
                'map' => array(
                    1 => '未报销',
                    2 => '部分报销',
                    3 => '完全报销'
                )
            ),
            'AGENCY_DEAL_REWARD_STATUS' => array(
                'name' => '中介成交奖励报销状态',
                'map' => array(
                    1 => '未报销',
                    2 => '申请中',
                    5 => '已报销'
                )
            ),
            'PROPERTY_DEAL_REWARD_STATUS' => array(
                'name' => '置业顾问成交奖励报销状态',
                'map' => array(
                    1 => '未报销',
                    2 => '申请中',
                    5 => '已报销'
                )
            ),
            'OUT_REWARD_STATUS' => array(
                'name' => '外部成交奖励报销状态',
                'map' => array(
                    1 => '未报销',
                    2 => '申请中',
                    5 => '已报销'
                )
            ),
        );

        $this->initExport($objPHPExcel, $objActSheet, '佣金记录列表', self::DEFAULT_EXCEL_COLUMN_WIDTH, self::DEFAULT_EXCEL_ROW_HEIGHT);
        $row = 1;
        $this->commonExportAction($objActSheet, $records, $row, self::EXCEL_MEMBER_TITLE, $dataFormat, array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        ));
        ob_end_clean();
        ob_start();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=" . '佣金记录列表' . date("YmdHis") . ".xls");
        header("Content-Transfer-Encoding:binary");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     * 获取后佣收费标准或后佣中介佣金标准的数量
     * @param $type int 1=后佣收费标准 2=后佣中介佣金标准
     * @param $id
     * @return int
     */
    private function getCommissionTotalAmount($type, $id) {
        $response = 0;
        if (intval($id)) {
            $tableName = 'erp_commission_invoice_detail';
            $amountName = 'TOTAL_PRICE_AFTER';
            if ($type == 2) {
                $tableName = 'erp_commission_reim_detail';
                $amountName = 'AGENCY_REWARD_AFTER';
            }

            $postCommissionId = D($tableName)->where("ID = {$id}")->getField('POST_COMMISSION_ID');
            if (intval($postCommissionId) > 0) {
                $sql = <<<MEMBER_SQL
                SELECT m.case_id,
                       m.total_price_after,
                       m.agency_reward_after,
                       m.housetotal
                FROM erp_cardmember m
                LEFT JOIN erp_post_commission c ON c.card_member_id = m.id
                WHERE c.id = %d
MEMBER_SQL;

                $memberList = D()->query(sprintf($sql, $postCommissionId));
                if (notEmptyArray($memberList)) {
                    $member = $memberList[0];
                    $totalAmount = getFeeScaleAmount($member['CASE_ID'], $member[$amountName], $member['HOUSETOTAL'], $feeType);
                    $response = round($totalAmount, 2);
                }
            }
        }

        return $response;
    }

}

/* End of file MemberDistributionAction.class.php */
/* Location: ./Lib/Action/MemberDistributionAction.class.php */