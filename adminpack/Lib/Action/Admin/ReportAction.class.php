<?php

class ReportAction extends ExtendAction
{
    private $outputReim = array(
        'ID' => array(
            'name' => '编号',
        ),
        'PROJECTNAME' => array(
            'name' => '项目名称',
        ),
        'CONTRACT' => array(
            'name' => '合同编号',
        ),
        'APPLY_UNAME' => array(
            'name' => '申请人',
        ),
        'APPLY_TIME' => array(
            'name' => '申请时间',
        ),
        'FEE_NAME' => array(
            'name' => '费用类型',
        ),
        'MONEY' => array(
            'name' => '费用金额',
        ),
        'ISKF' => array(
            'name' => '是否扣非',
            'map' => array(
                '0' => '否',
                '1' => '是',
            )
        ),
        'ISFUNDPOOL' => array(
            'name' => '是否资金池费用	',
            'map' => array(
                '0' => '否',
                '1' => '是',
            )
        ),
        'TYPE' => array(
            'name' => '报销类型',
            'map'=>array(),
        ),
        'REIM_TIME' => array(
            'name' => '报销确认时间',
        ),
        'SUPPLIER' => array(
            'name' => '供应商',
        ),
        'ADD_TIME' => array(
            'name' => '采购发起时间',
        ),
        'INPUT_TAX' => array(
            'name' => '进项税',
        ),
        'COST_OCCUR_TIME' => array(
            'name' => '费用发生时间',
        ),
        'PURCHASE_OCCUR_TIME' => array(
            'name' => '费用录入时间',
        ),
    );

    private $outputPay = array(
        'ID' => array(
            'name' => '编号',
        ),
        'PROJECTNAME' => array(
            'name' => '项目名称',
        ),
        'CONTRACT' => array(
            'name' => '合同编号',
        ),
        'SHOWTYPE' => array(
            'name' => '费用来源',
            'map' => array(
                '1' => '采购、大宗采购',
                '2' => '小蜜蜂采购',
                '3' => '预算外其他',
                '4' => '资金池费用',
                '5' => '现金发放',
            )
        ),
        'APPLY_UNAME' => array(
            'name' => '申请人',
        ),
        'FEE_NAME' => array(
            'name' => '费用类型',
        ),
        'MONEY' => array(
            'name' => '费用金额',
        ),
        'IS_KF' => array(
            'name' => '是否扣非',
            'map' => array(
                '0' => '否',
                '1' => '是',
            )
        ),
        'IS_FUNDPOOL' => array(
            'name' => '是否资金池费用	',
            'map' => array(
                '0' => '否',
                '1' => '是',
            )
        ),
        'SUPPLIER' => array(
            'name' => '供应商',
        ),
        'ADD_TIME' => array(
            'name' => '费用发起时间',
        ),
        'COST_OCCUR_TIME' => array(
            'name' => '费用发生时间',
        ),
        'PURCHASE_OCCUR_TIME' => array(
            'name' => '费用录入时间',
        ),
    );

    public $show_field_conf_public = array(
        'ORDER_ID' => array('show_name' => '编号'),
        'CONTRACT' => array('show_name' => '合同号'),
        'PROJECTNAME' => array('show_name' => '项目名称'),
        'FROMDATE' => array('show_name' => '进场时间'),
        'PRE_MONEY' => array('show_name' => '预收款(总)'),//(总)
        'PRE_INVOICE' => array('show_name' => '预开票'),//无
        'INVOICE_MONEY' => array('show_name' => '已开票的回款'),
        'fee_type' => array(
            'show_name' => '费用类别 ',
            'child_field' => array(
                'space_fee' => array('show_name' => '场地费'),
                'production_fee' => array('show_name' => '制作费'),
                'salary_fee' => array('show_name' => '人员工资'),
                'business_fee' => array('show_name' => '业务费'),
                'third_party_fee' => array('show_name' => '支付第三方费用'),
                'agency_fee' => array('show_name' => '中介费'),
                'channel_fee' => array('show_name' => '渠道费'),
                'sms_fee' => array('show_name' => '短信费'),
                'telephone_fee' => array('show_name' => '电话费'),
                'publicity_fee' => array('show_name' => '宣传费'),
                'external_advertising_fee' => array('show_name' => '外部广告费'),
                'bus_fee' => array('show_name' => '租车费'),
                'pos_fee' => array('show_name' => 'POS手续费'),
                'taxes_fee' => array('show_name' => '税金'),
                'other_fee' => array('show_name' => '杂费及带看费'),
                'profit_sharing_fee' => array('show_name' => '项目分成'),
                'transaction_rewards_fee' => array('show_name' => '成交费'),
                'internal_commission' => array('show_name' => '内部佣金'),
                'external_commission' => array('show_name' => '外部佣金'),
                'subtotal_fee' => array('show_name' => '小计'),
            )
        ),
        'fundpool_' => array(
            'show_name' => '资金池非付现费用',
            'child_field' => array(
                'FUNDPOOL_AD' => array('show_name' => '资金池转本公司广告'),
                'FUNDPOOL_DIFF' => array('show_name' => '资金池执行差价'),
                'FUNDPOOL_OTHER' => array('show_name' => '资金池其他'),
                'FUNDPOOL_TOTAL' => array('show_name' => '合计')
            )
        ),
        'COST_ONLINE_AD' => array('show_name' => '折后广告费'),
        'PROFIT_AMOUT' => array('show_name' => '利润金额'),
        'UNDOTIME' => array('show_name' => '撤场时间'),
        'PROFIT_RATE' => array('show_name' => '项目利润率'),
        'project_application' => array(
            'show_name' => '立项申报情况',
            'child_field' => array(
                'PRO_INCOME_FEE' => array('show_name' => '预估收入'),
                'PRO_COST_ONLINE_AD' => array('show_name' => '预估折后广告费'),
                'PRO_COST_FEE' => array('show_name' => '预估费用支出'),
                'PRO_BENEFITS' => array('show_name' => '预估津贴'),
                'PRO_PROFIT_RATE' => array('show_name' => '预估利润率'),
                'IS_FINAL' => array('show_name' => '是否决算'),
                'IS_FUNDPOOL' => array('show_name' => '是否资金池项目')
            )
        ),
        'CASH_PROFIT' => array('show_name' => '付现利润额'),
        'CASH_PROFIT_RATE' => array('show_name' => '付现利润率'),
        'CASH_INCOME' => array('show_name' => '收现利润额'),
        'CASH_INCOME_RATE' => array('show_name' => '收现利润率'),
        'MONTH_WITHHOLD_COST' => array('show_name' => '预扣费用'),
        'CFM_INCOME' => array('show_name' => '考核收入'),
    );

    /*****需要统计的业务类型状态****/
    public $static_status = array(
        'ds' => '2,3,4,5',
    );

    /******自定义统计项目状态******/
    public $custom_pro_status = array(
        '2' => '执行中',
        '3' => '办结',
        '4' => '执行周期结束',
        '5' => '终止',
    );

    public function __construct()
    {
        parent::__construct();
        $this->model = new Model();
        //城市ID
        $this->city_id = intval($_SESSION['uinfo']['city']);
        //用户ID
        $this->uid = intval($_SESSION['uinfo']['uid']);
        //用户姓名
        $this->uname = trim($_SESSION['uinfo']['uname']);
        //城市简称
        $this->user_city_py = trim($_SESSION['uinfo']['city_py']);

        //TAB URL参数
        $this->_merge_url_param['FLOWTYPE'] = isset($_GET['FLOWTYPE']) ? $_GET['FLOWTYPE'] : 13;
        $this->_merge_url_param['CASEID'] = isset($_GET['CASEID']) ? $_GET['CASEID'] : 0;
        $this->_merge_url_param['RECORDID'] = isset($_GET['RECORDID']) ? $_GET['RECORDID'] : 0;
        $this->_merge_url_param['flowId'] = isset($_GET['flowId']) ? $_GET['flowId'] : 0;
		!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;

    }

    /* ----------------------------------------------------------------------------------*/
    /*   ------- 数据发生表 -------------------------------------------------------------*/
    /* ----------------------------------------------------------------------------------*/

    function data()
    {
        //操作类型
        $action_type = isset($_REQUEST['action_type']) ? intval($_REQUEST['action_type']) : '';
        //查询时间维度
        $search_btime = isset($_REQUEST['search_btime']) ? strip_tags($_REQUEST['search_btime']) : date('Y-m');
        //项目状态
        $search_state = isset($_REQUEST['search_state']) ? intval($_REQUEST['search_state']) : 0;
        //是否资金池项目
        $isfundpool = isset($_REQUEST['isfundpool']) ? intval($_REQUEST['isfundpool']) : 0;
        //查询项目
        $search_prjname = isset($_REQUEST['search_prjname']) ? strip_tags($_REQUEST['search_prjname']) : '';
        //费用类型 查询
        $coststate = isset($_REQUEST['coststate']) ? intval($_REQUEST['coststate']) : '';
        //是否导出
        $export = isset($_REQUEST['export']) ? $_REQUEST['export'] : '';


        //$count_time_conf = array(1 => '本月发生', 2 => '本年累计发生', 3 => '累计发生');
        $prjState = array(0 => '全部项目', 1 => '未决算项目', 2 => '已决算项目');
        $prj_isfundpool = array(0 => '全部项目', 1 => '非资金池项目', 2 => '资金池项目');
        $cost_state = array(0 => '包含已经发生和报销', 1 => '只包含报销', 2 => '只包含发生未报销');


        $pageurl = __ACTION__;
        $model = M();
        $show_field_conf = $this->show_field_conf_public;

        $th_first_str = '<tr>';
        $th_second_str = "<tr>";
        foreach ($show_field_conf as $key => $value) {
            $rowspan = "rowspan = 2";
            $child_field_num = !empty($value['child_field']) ? count($value['child_field']) : 0;

            if ($child_field_num > 0) {
                $colspan = "colspan = '" . $child_field_num . "'";
                $th_first_str .= '<td ' . $colspan . '>' . $value['show_name'] . '</td>';

                foreach ($value['child_field'] as $key => $value) {
                    $th_second_str .= '<td>' . $value['show_name'] . '</td>';
                }
            } else {
                $th_first_str .= '<td ' . $rowspan . '>' . $value['show_name'] . '</td>';
            }
        }
        $th_first_str .= '</tr>';
        $th_second_str .= '</tr>';
        $th_str = $th_first_str . $th_second_str;

        $where_prj = " WHERE P.CITY_ID='" . $this->channelid . "'  AND P.PSTATUS=3  AND P.STATUS<>2   ";
        //  p_status_param.set_param('1,4,5')=0 and  p_stattime_param.set_param('20151001')=0 and  p_stattime_param.set_param('20161001')=0

        if (!empty($search_prjname)) {
            $prjname_arr = explode(",", $search_prjname);

            if (is_array($prjname_arr) && !empty($prjname_arr)) {
                $prjname_search_str = '';
                foreach ($prjname_arr as $key => $value) {
                    if (!empty($value)) {
                        $prjname_search_str .= ($prjname_search_str != '') ?
                            ",'" . $value . "'" : "'" . $value . "'";
                    }
                }
                $where_prj .= !empty($prjname_search_str) ? " AND P.PROJECTNAME IN (" . $prjname_search_str . ")" : '';
            } else {
                $where_prj .= " AND P.PROJECTNAME IN ('" . $search_prjname . "')";
            }
            $pageurl .= '&search_prjname=' . $search_prjname;
        }

        if (!empty($search_btime)) {
            $last_time = date('Ym', strtotime('-1 month', strtotime($search_btime))) . '26';
            $next_time = date('Ym', strtotime($search_btime)) . '25';
            $where_prj .= "AND  p_stattime_param.set_param('$last_time ')=0 AND p_endtime_param.set_param('$next_time')=0 ";
        }
        if (!empty($search_state)) {
            //未决算
            if ($search_state == 1) {
                $where_prj .= " AND IS_FINAL in (2,3,31,51) ";
            //已决算
            } elseif ($search_state == 2) {
                $where_prj .= " AND IS_FINAL =3 ";
            }

            $pageurl .= '&search_state=' . $search_state;
        }
        if (!empty($isfundpool)) {
            if ($isfundpool == 1) {//非
                $where_prj .= " AND IS_FUNDPOOL = 1";
            } elseif ($isfundpool == 2) {
                $where_prj .= " AND IS_FUNDPOOL <> 1";
            }

            $pageurl .= '&isfundpool=' . $isfundpool;
        }
        if (!empty($coststate)) {
            if ($coststate == 1) {//只包含报销
                $where_prj .= " AND p_status_param.set_param('4')=0";
            } elseif ($coststate == 2) {//只包含发生未报销
                $where_prj .= " AND p_status_param.set_param('2')=0";
            } else {//包含已经发生和报销
//                    $where_prj .= " AND p_status_param.set_param('1,4')=0";
                $where_prj .= " AND p_status_param.set_param('6')=0";
            }

            $pageurl .= '&coststate=' . $coststate;
        } else $where_prj .= " AND p_status_param.set_param('6')=0";
        $sql = "SELECT COUNT(*) AS COUNT FROM v_stat_tlfdata " . " P " . $where_prj;
        $res = $model->query($sql);
        $count_num = $res[0]['COUNT'];

//        if (empty($export)) {
//            $page = isset($_GET['pn']) ? (int)$_GET['pn'] : 1;
//            $limit = 10;
//            $offset = ($page - 1) * $limit;
//            $length = $offset + $limit;
//            $page_nav = page($count_num, $limit, $page, $pageurl);
//        } else {
//            $page = 1;
//            $limit = $count_num;
//            $offset = ($page - 1) * $limit;
//            $length = $offset + $limit;
//            $page_nav = page($count_num, $limit, $page, $pageurl);
//        }

        $page = isset($_GET['pn']) ? (int)$_GET['pn'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $length = $offset + $limit;
        $page_nav = page($count_num, $limit, $page, $pageurl);


        $result = array();
        $sql = "SELECT * FROM v_stat_tlfdata "
            . " P " . $where_prj . "   ";
        $sql = "SELECT * FROM ( SELECT A.*, ROWNUM RN FROM ($sql) A WHERE ROWNUM <= $length ) WHERE RN >  $offset";
        $result = $model->query($sql);
        $data_sum = array();
        if ($result) {
            foreach ($result as $val) {
                $data_str .= '<tr>';
                $fee_type_total = 0;
                foreach ($show_field_conf as $key => $value) {
                    if ($key == 'fee_type' || $key == 'fundpool_' || $key == 'project_application') {
                        foreach ($value['child_field'] as $keyy => $valuee) {
                            if ($keyy == 'subtotal_fee') $val[strtoupper($keyy)] = $fee_type_total;
                            if ($key == 'fee_type') $fee_type_total += $val[strtoupper($keyy)];
                            if ($keyy == 'IS_FINAL') {
                                if ($val[strtoupper($keyy)] == 3) {
                                    $val[strtoupper($keyy)] = '是';
                                } else $val[strtoupper($keyy)] = '否';
                            }
                            if ($keyy == 'IS_FUNDPOOL') {
                                if ($val[strtoupper($keyy)] == 1) {
                                    $val[strtoupper($keyy)] = '否';
                                } else $val[strtoupper($keyy)] = '是';
                            }
                            //if($key=='fee_type')
                            //$data_str .= '<td>'.$val[$valuee['show_name'] ].'</td>';
                            //else
                            $data_str .= '<td>' . $val[strtoupper($keyy)] . '</td>';
                            $data_sum[$keyy][] = isset($val[strtoupper($keyy)]) ? $val[strtoupper($keyy)] : '';
                        }
                    } else {
                        if ($coststate != 0) {
                            if ($key == 'MONTH_WITHHOLD_COST' || $key == 'CFM_INCOME') $val[strtoupper($key)] = '';
                        }
                        $data_str .= '<td>' . $val[strtoupper($key)] . '</td>';
                        $data_sum[$key][] = isset($val[strtoupper($key)]) ? $val[strtoupper($key)] : '';
                    }
                    $data_sum_rate['VCOST'][] = $val['VCOST'];
                    $data_sum_rate['YUSHOU'][] = $val['YUSHOU'];
                }
                $data_str .= '</tr>';
            }
        } else  $data_str .= '<tr><td colspan ="53">没有查到符合条件的数据</td></tr>';

        //导出EXCEL
        $page_title = $search_btime . '数据发生表' . '_';
        if (!empty($export)) {
            $i = 0;
            $data_sum_str = '<tr>';
            $data_sum_str .= '<td colspan="4">合计</td>';
            foreach ($data_sum as $k => $value) {

                if ($i > 3) {
                    if ($k == 'PROFIT_RATE') {//项目利润率
                        $vincome = array_sum($data_sum['INVOICE_MONEY']);
                        $sum = round(($vincome - array_sum($data_sum_rate['VCOST']) - array_sum($data_sum['COST_ONLINE_AD'])) / $vincome * 100, 2) . '%';
                    } elseif ($k == 'PRO_PROFIT_RATE') {//预估利润率
                        $pro_income_fee = array_sum($data_sum['PRO_INCOME_FEE']);
                        $sum = round(($pro_income_fee - array_sum($data_sum['PRO_COST_FEE'])) / $pro_income_fee * 100, 2) . '%';

                    } elseif ($k == 'CASH_PROFIT_RATE') {//付现利润率
                        $vincome = array_sum($data_sum['INVOICE_MONEY']);
                        $sum = round(($vincome - array_sum($data_sum_rate['VCOST'])) / $vincome * 100, 2) . '%';

                    } elseif ($k == 'CASH_INCOME_RATE') {//收现利润率
                        $vincome = array_sum($data_sum['INVOICE_MONEY']) + array_sum($data_sum_rate['YUSHOU']);
                        $sum = round(($vincome - array_sum($data_sum_rate['VCOST'])) / $vincome * 100, 2) . '%';
                    } else {
                        $sum = array_sum($value);
                    }
                    $data_sum_str .= '<td>' . $sum . '</td>';
                }
                $i++;
            }
            $data_sum_str .= '</tr>';
            $Exceltitle = $page_title;
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");
            header("Content-Disposition:attachment;filename=" . $Exceltitle . date("YmdHis") . ".xls");
            $html = '<meta http-equiv="content-type" content="application/ms-excel; charset=gb2312"/>';
            $html .= '<table border="1" width="100%">  ' . $th_str . $data_str . $data_sum_str . ' </table>';
            exit($html);
        }

        $this->assign('th_str', $th_str);
        $this->assign('data_str', $data_str);
        $this->assign('page_nav', $page_nav);
        $this->assign('prjState', $prjState);
        $this->assign('prj_isfundpool', $prj_isfundpool);
        $this->assign('cost_state', $cost_state);

        $this->assign('search_prjname', $search_prjname);
        $this->assign('search_btime', $search_btime);
        $this->assign('search_state', $search_state);
        $this->assign('isfundpool', $isfundpool);
        $this->assign('coststate', $coststate);
        $this->display('data_new');
    }

    /*-------------------------------------------------------------------*/
    /*--------------------累计发生明细表-------------------------------*/
    /*-------------------------------------------------------------------*/
    function cumulative()
    {//
        //操作类型
        $action_type = isset($_REQUEST['action_type']) ? intval($_REQUEST['action_type']) : '';

        //项目状态
        $search_state = isset($_REQUEST['search_state']) ? intval($_REQUEST['search_state']) : 0;
        //是否资金池项目
        $isfundpool = isset($_REQUEST['isfundpool']) ? intval($_REQUEST['isfundpool']) : 0;
        //查询项目
        $search_prjname = isset($_REQUEST['search_prjname']) ? strip_tags($_REQUEST['search_prjname']) : '';
        //费用类型 查询
        $coststate = isset($_REQUEST['coststate']) ? intval($_REQUEST['coststate']) : '';
        //是否导出
        $export = isset($_REQUEST['export']) ? $_REQUEST['export'] : '';
        //$count_time_conf = array(1 => '本月发生', 2 => '本年累计发生', 3 => '累计发生');
        $prjState = array(0 => '全部项目', 1 => '未决算项目', 2 => '已决算项目');
        $prj_isfundpool = array(0 => '全部项目', 1 => '非资金池项目', 2 => '资金池项目');
        $cost_state = array(0 => '包含已经发生和报销', 1 => '只包含报销', 2 => '只包含发生未报销');
        $pageurl = __ACTION__;
        $model = M();
        $show_field_conf = $this->show_field_conf_public;

        $th_first_str = '<tr>';
        $th_second_str = "<tr>";
        foreach ($show_field_conf as $key => $value) {
            $rowspan = "rowspan = 2";
            $child_field_num = !empty($value['child_field']) ? count($value['child_field']) : 0;

            if ($child_field_num > 0) {
                $colspan = "colspan = '" . $child_field_num . "'";
                $th_first_str .= '<td ' . $colspan . '>' . $value['show_name'] . '</td>';

                foreach ($value['child_field'] as $key => $value) {
                    $th_second_str .= '<td>' . $value['show_name'] . '</td>';
                }
            } else {
                $th_first_str .= '<td ' . $rowspan . '>' . $value['show_name'] . '</td>';
            }
        }
        $th_first_str .= '</tr>';
        $th_second_str .= '</tr>';
        $th_str = $th_first_str . $th_second_str;

        $where_prj = " where P.CITY_ID='" . $this->channelid . "'  and P.PSTATUS=3  and P.STATUS<>2  ";//  p_status_param.set_param('1,4,5')=0 and  p_stattime_param.set_param('20151001')=0 and  p_stattime_param.set_param('20161001')=0
        if (!empty($search_prjname)) {
            $prjname_arr = explode(",", $search_prjname);

            if (is_array($prjname_arr) && !empty($prjname_arr)) {
                $prjname_search_str = '';
                foreach ($prjname_arr as $key => $value) {
                    if (!empty($value)) {
                        $prjname_search_str .= ($prjname_search_str != '') ?
                            ",'" . $value . "'" : "'" . $value . "'";
                    }
                }

                $where_prj .= !empty($prjname_search_str) ? " AND P.PROJECTNAME IN (" . $prjname_search_str . ")" : '';
            } else {
                $where_prj .= " AND P.PROJECTNAME IN ('" . $search_prjname . "')";
            }

            $pageurl .= '&search_prjname=' . $search_prjname;

        }
        //$where_prj .= "AND  p_stattime_param.set_param('')=0 AND p_endtime_param.set_param('')=0 ";
        if (!empty($search_state)) {
            if ($search_state == 1) {//未决算
                $where_prj .= " AND IS_FINAL in (2,3,31,51) ";
            } elseif ($search_state == 2) {//已决算
                $where_prj .= " AND IS_FINAL =3 ";
            }

            $pageurl .= '&search_state=' . $search_state;
        }
        if (!empty($isfundpool)) {
            if ($isfundpool == 1) {//非
                $where_prj .= " AND IS_FUNDPOOL = 1";
            } elseif ($isfundpool == 2) {
                $where_prj .= " AND IS_FUNDPOOL <> 1";
            }

            $pageurl .= '&isfundpool=' . $isfundpool;
        }
        if (!empty($coststate)) {
            if ($coststate == 1) {//只包含报销
                $where_prj .= " AND p_status_param.set_param('4')=0";
            } elseif ($coststate == 2) {//只包含发生未报销
                $where_prj .= " AND p_status_param.set_param('2')=0";
            } else {//包含已经发生和报销
                $where_prj .= " AND p_status_param.set_param('6')=0";
            }

            $pageurl .= '&coststate=' . $coststate;
        } else $where_prj .= " AND p_status_param.set_param('6')=0";
        $sql = "SELECT COUNT(*) as COUNT FROM STAT_TLFDATA2 " . " P " . $where_prj;
        $res = $model->query($sql);
        $count_num = $res[0]['COUNT'];


        $page = isset($_GET['pn']) ? (int)$_GET['pn'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $length = $offset + $limit;
        $page_nav = page($count_num, $limit, $page, $pageurl);

//        if (empty($export)) {
//            $page = isset($_GET['pn']) ? (int)$_GET['pn'] : 1;
//            $limit = 10;
//            $offset = ($page - 1) * $limit;
//            $length = $offset + $limit;
//            $page_nav = page($count_num, $limit, $page, $pageurl);
//        } else {
//            $page = 1;
//            $limit = $count_num;
//            $offset = ($page - 1) * $limit;
//            $length = $offset + $limit;
//            $page_nav = page($count_num, $limit, $page, $pageurl);
//        }

        $result = array();
        $sql = "SELECT * FROM v_stat_tlfdata "
            . " P " . $where_prj . "   ";
        $sql = "SELECT * FROM ( SELECT A.*, ROWNUM RN FROM ($sql) A WHERE ROWNUM <= $length ) WHERE RN >  $offset";
        $result = $model->query($sql);

        if ($result) {
            foreach ($result as $val) {
                $data_str .= '<tr>';
                $fee_type_total = 0;
                foreach ($show_field_conf as $key => $value) {
                    if ($key == 'fee_type' || $key == 'fundpool_' || $key == 'project_application') {
                        foreach ($value['child_field'] as $keyy => $valuee) {
                            if ($keyy == 'subtotal_fee') $val[strtoupper($keyy)] = $fee_type_total;
                            if ($key == 'fee_type') $fee_type_total += $val[strtoupper($keyy)];
                            if ($keyy == 'IS_FINAL') {
                                if ($val[strtoupper($keyy)] == 3) {
                                    $val[strtoupper($keyy)] = '是';
                                } else $val[strtoupper($keyy)] = '否';
                            }
                            if ($keyy == 'IS_FUNDPOOL') {
                                if ($val[strtoupper($keyy)] == 1) {
                                    $val[strtoupper($keyy)] = '否';
                                } else $val[strtoupper($keyy)] = '是';
                            }
                            //if($key=='fee_type')
                            //$data_str .= '<td>'.$val[$valuee['show_name'] ].'</td>';
                            //else
                            $data_str .= '<td>' . $val[strtoupper($keyy)] . '</td>';
                            $data_sum[$keyy][] = isset($val[strtoupper($keyy)]) ? $val[strtoupper($keyy)] : '';
                        }
                    } else {
                        if ($coststate != 0) {
                            if ($key == 'MONTH_WITHHOLD_COST' || $key == 'CFM_INCOME') $val[strtoupper($key)] = '';
                        }
                        $data_str .= '<td>' . $val[strtoupper($key)] . '</td>';
                        $data_sum[$key][] = isset($val[strtoupper($key)]) ? $val[strtoupper($key)] : '';
                    }
                    $data_sum_rate['VCOST'][] = $val['VCOST'];
                    $data_sum_rate['YUSHOU'][] = $val['YUSHOU'];
                }
                $data_str .= '</tr>';
            }
        } else  $data_str .= '<tr><td colspan ="53">没有查到符合条件的数据</td></tr>';
        //导出EXCEL
        $page_title = '累计发生明细表' . '_';
        if (!empty($export)) {
            $i = 0;
            $data_sum_str = '<tr>';
            $data_sum_str .= '<td colspan="4">合计</td>';
            foreach ($data_sum as $k => $value) {

                if ($i > 3) {
                    if ($k == 'PROFIT_RATE') {//项目利润率
                        $vincome = array_sum($data_sum['INVOICE_MONEY']);
                        $sum = round(($vincome - array_sum($data_sum_rate['VCOST']) - array_sum($data_sum['COST_ONLINE_AD'])) / $vincome * 100, 2) . '%';
                    } elseif ($k == 'PRO_PROFIT_RATE') {//预估利润率
                        $pro_income_fee = array_sum($data_sum['PRO_INCOME_FEE']);
                        $sum = round(($pro_income_fee - array_sum($data_sum['PRO_COST_FEE'])) / $pro_income_fee * 100, 2) . '%';

                    } elseif ($k == 'CASH_PROFIT_RATE') {//付现利润率
                        $vincome = array_sum($data_sum['INVOICE_MONEY']);
                        $sum = round(($vincome - array_sum($data_sum_rate['VCOST'])) / $vincome * 100, 2) . '%';

                    } elseif ($k == 'CASH_INCOME_RATE') {//收现利润率
                        $vincome = array_sum($data_sum['INVOICE_MONEY']) + array_sum($data_sum_rate['YUSHOU']);
                        $sum = round(($vincome - array_sum($data_sum_rate['VCOST'])) / $vincome * 100, 2) . '%';
                    } else {
                        $sum = array_sum($value);
                    }
                    $data_sum_str .= '<td>' . $sum . '</td>';
                }
                $i++;
            }
            $data_sum_str .= '</tr>';
            $Exceltitle = $page_title;
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");
            header("Content-Disposition:attachment;filename=" . $Exceltitle . date("YmdHis") . ".xls");
            $html = '<meta http-equiv="content-type" content="application/ms-excel; charset=gb2312"/>';
            $html .= '<table border="1" width="100%">  ' . $th_str . $data_str . $data_sum_str . ' </table>';
            exit($html);
        }
        $this->assign('th_str', $th_str);
        $this->assign('data_str', $data_str);
        $this->assign('page_nav', $page_nav);
        $this->assign('prjState', $prjState);
        $this->assign('prj_isfundpool', $prj_isfundpool);
        $this->assign('cost_state', $cost_state);

        $this->assign('search_prjname', $search_prjname);

        $this->assign('search_state', $search_state);
        $this->assign('isfundpool', $isfundpool);
        $this->assign('coststate', $coststate);
        $this->display('cumulative_new');
    }

    /*-------------------------------------------------------------------*/
    /*--------------------调整过程表-----------------------------------*/
    /*-------------------------------------------------------------------*/
    function fundpooladj()
    {
        //操作类型
        $action_type = isset($_REQUEST['action_type']) ? intval($_REQUEST['action_type']) : '';

        //项目状态
        $search_state = isset($_REQUEST['search_state']) ? intval($_REQUEST['search_state']) : 0;
        //是否资金池项目
        $isfundpool = isset($_REQUEST['isfundpool']) ? intval($_REQUEST['isfundpool']) : 0;
        //查询项目
        $search_prjname = isset($_REQUEST['search_prjname']) ? strip_tags($_REQUEST['search_prjname']) : '';
        //费用类型 查询
        $coststate = isset($_REQUEST['coststate']) ? intval($_REQUEST['coststate']) : '';
        //是否导出
        $export = isset($_REQUEST['export']) ? $_REQUEST['export'] : '';
        //$count_time_conf = array(1 => '本月发生', 2 => '本年累计发生', 3 => '累计发生');
        $prjState = array(0 => '全部项目', 1 => '未决算项目', 2 => '已决算项目');
        $prj_isfundpool = array(0 => '全部项目', 1 => '非资金池项目', 2 => '资金池项目');
        $cost_state = array(0 => '包含已经发生和报销', 1 => '只包含报销', 2 => '只包含发生未报销');

        $pageurl = __ACTION__;
        $model = M();

        $show_field_conf = array(
            'order_id' => array('show_name' => '编号'),
            'CONTRACT' => array('show_name' => '合同号'),
            'PROJECTNAME' => array('show_name' => '楼盘项目名称'),
            'FROMDATE' => array('show_name' => '进场时间'),
            'pre_money' => array('show_name' => '预收款'),//(总)
            'pre_invoice' => array('show_name' => '预开票'),//无
            'invoice_money' => array('show_name' => '已开票的回款'),
            'fee_pay' => array(
                'show_name' => '付现费用',
                'child_field' => array(
                    'UNFUNDPOOL_COST' => array('show_name' => '非资金池费用小计'),
                    'FUNDPOOL_COST' => array('show_name' => '资金池费用小计'),
                    'OFFLINE_COST_SUM' => array('show_name' => '付现费用合计')

                )
            ),
            'fundpool_' => array(
                'show_name' => '资金池非付现费用',
                'child_field' => array(
                    'FUNDPOOL_AD' => array('show_name' => '资金池转本公司广告'),
                    'FUNDPOOL_DIFF' => array('show_name' => '资金池执行差价'),
                    'FUNDPOOL_OTHER' => array('show_name' => '资金池其他'),
                    'FUNDPOOL_TOTAL' => array('show_name' => '合计')
                )
            ),
            'COST_ONLINE_AD' => array('show_name' => '折后广告费'),
            'PROFIT_AMOUT' => array('show_name' => '利润金额'),
            'UNDOTIME' => array('show_name' => '撤场时间'),
            'PROFIT_RATE' => array('show_name' => '项目利润率'),
            'project_application' => array(
                'show_name' => '立项申报情况',
                'child_field' => array(
                    'PRO_INCOME_FEE' => array('show_name' => '预估收入'),
                    'PRO_COST_ONLINE_AD' => array('show_name' => '预估折后广告费'),
                    'PRO_COST_FEE' => array('show_name' => '预估费用支出'),
                    'PRO_BENEFITS' => array('show_name' => '预估津贴'),
                    'PRO_PROFIT_RATE' => array('show_name' => '预估利润率'),
                    'IS_FINAL' => array('show_name' => '是否决算'),
                    'IS_FUNDPOOL' => array('show_name' => '是否资金池项目')
                )
            ),
            'cost_income_count' => array(
                'show_name' => '费用计算',
                'child_field' => array(
                    'FUNDPOOL_RATIO' => array('show_name' => '资金池比例'),
                    'by_ratio_fundpool_cost' => array('show_name' => '按比例计算资金池费用'),
                    'cost_income_ratio' => array('show_name' => '费用占收入比(除资金池费用)'),
                    'by_ratio_cost' => array('show_name' => '按比例计算费用')

                )
            ),
            'withhold_cost' => array(
                'show_name' => '孰高原则应确认费用累计',
                'child_field' => array(
                    'withhold_fundpool_cost' => array('show_name' => '孰高资金池费用'),
                    'withhold_offline_cost' => array('show_name' => '孰高线下活动费用'),
                    //'withhold_cost' => array('show_name'=>'预扣5%费用'),
                    'withhold_special_cost' => array('show_name' => '特殊事项预扣费用'),
                    'withhold_total_cost' => array('show_name' => '累计合计')

                )
            ),
            'ajdfund_sum' => array('show_name' => $pre_title . '截止上月资金池调整表的累计余额'),
            'month_withhold_cost' => array('show_name' => $pre_title . '本月需预扣费用'),
            'cfm_income' => array('show_name' => $pre_title . '累计确认考核收入')
        );

        $th_first_str = '<tr>';
        $th_second_str = "<tr>";
        foreach ($show_field_conf as $key => $value) {
            $rowspan = "rowspan = 2";
            $child_field_num = !empty($value['child_field']) ? count($value['child_field']) : 0;

            if ($child_field_num > 0) {
                $colspan = "colspan = '" . $child_field_num . "'";
                $th_first_str .= '<td ' . $colspan . '>' . $value['show_name'] . '</td>';

                foreach ($value['child_field'] as $key => $value) {
                    $th_second_str .= '<td>' . $value['show_name'] . '</td>';
                }
            } else {
                $th_first_str .= '<td ' . $rowspan . '>' . $value['show_name'] . '</td>';
            }
        }
        $th_first_str .= '</tr>';
        $th_second_str .= '</tr>';
        $th_str = $th_first_str . $th_second_str;

        $where_prj = " WHERE P.CITY_ID='" . $this->channelid . "'   ";//  p_status_param.set_param('1,4,5')=0 and  p_stattime_param.set_param('20151001')=0 and  p_stattime_param.set_param('20161001')=0
        if (!empty($search_prjname)) {
            $prjname_arr = explode(",", $search_prjname);

            if (is_array($prjname_arr) && !empty($prjname_arr)) {
                $prjname_search_str = '';
                foreach ($prjname_arr as $key => $value) {
                    if (!empty($value)) {
                        $prjname_search_str .= ($prjname_search_str != '') ?
                            ",'" . $value . "'" : "'" . $value . "'";
                    }
                }

                $where_prj .= !empty($prjname_search_str) ? " AND P.PROJECTNAME IN (" . $prjname_search_str . ")" : '';
            } else {
                $where_prj .= " AND P.PROJECTNAME IN ('" . $search_prjname . "')";
            }

            $pageurl .= '&search_prjname=' . $search_prjname;

        }

        //$where_prj .= "AND  p_stattime_param.set_param('')=0 AND p_endtime_param.set_param('')=0 ";
        if (!empty($search_state)) {
            if ($search_state == 1) {//未决算
                $where_prj .= " AND IS_FINAL in (2,3,31,51) ";
            } elseif ($search_state == 2) {//已决算
                $where_prj .= " AND IS_FINAL =3 ";
            }

            $pageurl .= '&search_state=' . $search_state;
        }
        /*if(!empty($isfundpool)){
                if($isfundpool==1){//非
                    $where_prj .= " AND IS_FUNDPOOL = 1";
                }elseif($isfundpool==2){
                    $where_prj .= " AND IS_FUNDPOOL <> 1";
                }

                $pageurl .= '&isfundpool='.$isfundpool;
            }*/
        $where_prj .= " AND IS_FUNDPOOL <> 1";

        $sql = "SELECT COUNT(*) AS COUNT FROM STAT_FUNDPOOLADJ_MV " . " P " . $where_prj;

        $res = $model->query($sql);
        $count_num = $res[0]['COUNT'];

        $page = isset($_GET['pn']) ? (int)$_GET['pn'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        $length = $offset + $limit;
        $page_nav = page($count_num, $limit, $page, $pageurl);

//        if (empty($export)) {
//            $page = isset($_GET['pn']) ? (int)$_GET['pn'] : 1;
//            $limit = 10;
//            $offset = ($page - 1) * $limit;
//            $length = $offset + $limit;
//            $page_nav = page($count_num, $limit, $page, $pageurl);
//        } else {
//            $page = 1;
//            $limit = $count_num;
//            $offset = ($page - 1) * $limit;
//            $length = $offset + $limit;
//            $page_nav = page($count_num, $limit, $page, $pageurl);
//        }

        $result = array();
        $sql = "SELECT * FROM STAT_FUNDPOOLADJ_MV "
            . " P " . $where_prj . "   ";
        $sql = "SELECT * FROM ( SELECT A.*, ROWNUM RN FROM ($sql) A WHERE ROWNUM <= $length ) WHERE RN >  $offset";
        $result = $model->query($sql);
        $title_arr = array('fee_pay', 'fundpool_', 'project_application', 'cost_income_count', 'withhold_cost');

        if ($result) {
            foreach ($result as $val) {
                $data_str .= '<tr>';
                foreach ($show_field_conf as $key => $value) {
                    if (in_array($key, $title_arr)) {
                        foreach ($value['child_field'] as $keyy => $valuee) {
                            if ($keyy == 'IS_FINAL') {
                                if ($val[strtoupper($keyy)] == 3) {
                                    $val[strtoupper($keyy)] = '是';
                                } else $val[strtoupper($keyy)] = '否';
                            }
                            if ($keyy == 'IS_FUNDPOOL') {
                                if ($val[strtoupper($keyy)] == 1) {
                                    $val[strtoupper($keyy)] = '否';
                                } else $val[strtoupper($keyy)] = '是';
                            }
                            $data_value = $val[strtoupper($keyy)];
                            $data_str .= '<td>' . $data_value . '</td>';
                            $data_sum[$keyy][] = isset($val[strtoupper($keyy)]) ? $val[strtoupper($keyy)] : '';
                        }
                    } else {
                        $data_value = $val[strtoupper($key)];
                        $data_str .= '<td>' . $data_value . '</td>';
                        $data_sum[$key][] = isset($val[strtoupper($key)]) ? $val[strtoupper($key)] : '';
                    }

                }
                $data_str .= '</tr>';
            }
        } else  $data_str .= '<tr><td colspan ="44">没有查到符合条件的数据</td></tr>';
        //导出EXCEL
        $page_title = '资金池调整过程表' . '_';
        if (!empty($export)) {
            $i = 0;
            $data_sum_str = '<tr>';
            $data_sum_str .= '<td colspan="4">合计</td>';
            foreach ($data_sum as $k => $value) {

                if ($i > 3) {
                    if ($k == 'PROFIT_RATE') {//项目利润率
                        $vincome = array_sum($data_sum['INVOICE_MONEY']);
                        $sum = round(($vincome - array_sum($data_sum_rate['VCOST']) - array_sum($data_sum['COST_ONLINE_AD'])) / $vincome * 100, 2) . '%';
                    } elseif ($k == 'PRO_PROFIT_RATE') {//预估利润率
                        $pro_income_fee = array_sum($data_sum['PRO_INCOME_FEE']);
                        $sum = round(($pro_income_fee - array_sum($data_sum['PRO_COST_FEE'])) / $pro_income_fee * 100, 2) . '%';

                    } elseif ($k == 'FUNDPOOL_RATIO') {//资金池比例

                        foreach ($data_sum['FUNDPOOL_RATIO'] as $one) {
                            $sum += substr($one, 0, -1);
                        }
                        echo $sum;
                        $sum = round($sum / count($data_sum['FUNDPOOL_RATIO']), 2) . '%';

                    } elseif ($k == 'cost_income_ratio') {//费用占收入比
                        $PRO_COST_ONLINE_AD = array_sum($data_sum['PRO_COST_ONLINE_AD']);//预估折后广告费
                        $PRO_COST_FEE = array_sum($data_sum['PRO_COST_FEE']);//预估费用
                        $PRO_INCOME_FEE = array_sum($data_sum['PRO_INCOME_FEE']);//预估收入
                        $fpscale = substr($data_sum['FUNDPOOL_RATIO'], 0, -1);


                        $sum = round(($PRO_COST_ONLINE_AD + $PRO_COST_FEE - $PRO_INCOME_FEE * $fpscale / 100) / $PRO_INCOME_FEE, 2) . '%';
                    } else {
                        $sum = array_sum($value);
                    }
                    $data_sum_str .= '<td>' . $sum . '</td>';
                }
                $i++;
            }
            $data_sum_str .= '</tr>';
            $Exceltitle = $page_title;
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");
            header("Content-Disposition:attachment;filename=" . $Exceltitle . date("YmdHis") . ".xls");
            $html = '<meta http-equiv="content-type" content="application/ms-excel; charset=gb2312"/>';
            $html .= '<table border="1" width="100%">  ' . $th_str . $data_str . $data_sum_str . ' </table>';
            exit($html);
        }
        $this->assign('th_str', $th_str);
        $this->assign('data_str', $data_str);
        $this->assign('page_nav', $page_nav);
        $this->assign('prjState', $prjState);
        $this->assign('prj_isfundpool', $prj_isfundpool);
        $this->assign('cost_state', $cost_state);

        $this->assign('search_prjname', $search_prjname);

        $this->assign('search_state', $search_state);
        $this->assign('isfundpool', $isfundpool);
        $this->assign('coststate', $coststate);
        $this->display('fundpooladj');
    }

    /**
     * 导出报销数据
     * @param $objActSheet
     * @param $reimData
     * @param $row
     */
    private function exportReimData(&$objActSheet, $reimData, &$row) {
        $this->commonExportAction($objActSheet, $reimData, $row, "报销数据", $this->outputReim, array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        ));
    }

    //报销统计报表
    public function reim_cumulative()
    {
        $act = isset($_REQUEST['act'])?trim($_REQUEST['act']):'';

        $city_channel = $this->channelid;
        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(190);

        //如果是导出功能
        if($act == 'export'){
            try {
                $export_sql = "SELECT ID,
       PROJECTNAME,
       CONTRACT,
       APPLY_UNAME,
       to_char(APPLY_TIME,'YYYY-MM-DD hh24:mi:ss') AS APPLY_TIME,
       FEE_NAME,
       MONEY,
       ISKF,
       ISFUNDPOOL,
       TYPE,
       to_char(REIM_TIME,'YYYY-MM-DD') AS REIM_TIME,
       SUPPLIER,
       to_char(ADD_TIME,'YYYY-MM-DD hh24:mi:ss') AS ADD_TIME,
       INPUT_TAX,
       to_char(COST_OCCUR_TIME,'YYYY-MM-DD hh24:mi:ss') AS COST_OCCUR_TIME,
       to_char(PURCHASE_OCCUR_TIME,'YYYY-MM-DD hh24:mi:ss') AS PURCHASE_OCCUR_TIME
FROM
  (SELECT A.ID,
          A.FEE_ID,
          A.ISFUNDPOOL,
          A.ISKF,
          A.TYPE,
          A.STATUS,
          F.NAME AS FEE_NAME,
          U.NAME AS APPLY_UNAME,
          P.PROJECTNAME,
          P.CONTRACT,
          A.CITY_ID,
          B.APPLY_UID,
          B.APPLY_TIME,
          B.REIM_TIME,
          A.MONEY,
          (CASE
               WHEN A.TYPE IN(1,
                              14) THEN L.ADD_TIME
               ELSE NULL
           END) ADD_TIME,
          A.INPUT_TAX,
          (CASE
               WHEN A.TYPE IN(1,
                              14) THEN L.COST_OCCUR_TIME
               ELSE NULL
           END) COST_OCCUR_TIME,
          (CASE
               WHEN A.TYPE IN(1,
                              14) THEN L.PURCHASE_OCCUR_TIME
               ELSE NULL
           END) PURCHASE_OCCUR_TIME,
          (CASE
               WHEN A.TYPE IN(1,
                              14,16) THEN s.name
               WHEN A.TYPE IN(3,4,5,6,9,10,11,12,21,22,23,24,25) THEN  CM.AGENCY_NAME
               ELSE NULL
           END) SUPPLIER
   FROM ERP_REIMBURSEMENT_DETAIL A
   LEFT JOIN ERP_CASE C ON C.ID = A.CASE_ID
   LEFT JOIN ERP_project P ON P.ID = C.PROJECT_ID
   LEFT JOIN ERP_REIMBURSEMENT_LIST B ON A.LIST_ID = B.ID
   LEFT JOIN ERP_PURCHASE_LIST L ON A.BUSINESS_ID = L.ID
   LEFT JOIN ERP_USERS U ON B.APPLY_UID = U.ID
  LEFT JOIN ERP_FEE F ON A.FEE_ID = F.ID
   LEFT JOIN ERP_SUPPLIER S ON S.ID = L.S_ID
   LEFT JOIN ERP_CARDMEMBER CM ON A.BUSINESS_ID = CM.ID
   WHERE A.TYPE NOT IN (2,15,17)

   UNION SELECT A.ID,
          A.FEE_ID,
          A.ISFUNDPOOL,
          A.ISKF,
          A.TYPE,
          A.STATUS,
          F.NAME AS FEE_NAME,
          U.NAME AS APPLY_UNAME,
          P.PROJECTNAME,
          P.CONTRACT,
          A.CITY_ID,
          B.APPLY_UID,
          B.APPLY_TIME,
          B.REIM_TIME,
          A.MONEY,
          NULL ADD_TIME,
          A.INPUT_TAX,
          NULL COST_OCCUR_TIME,
          NULL PURCHASE_OCCUR_TIME,
          (CASE
               WHEN A.TYPE IN(2) THEN s.name
               ELSE NULL
           END) SUPPLIER
   FROM ERP_REIMBURSEMENT_DETAIL A
   LEFT JOIN ERP_CASE C ON C.ID = A.CASE_ID
   LEFT JOIN ERP_project P ON P.ID = C.PROJECT_ID
   LEFT JOIN ERP_REIMBURSEMENT_LIST B ON A.LIST_ID = B.ID
   LEFT JOIN ERP_BENEFITS L ON A.BUSINESS_ID = L.ID
   LEFT JOIN ERP_USERS U ON B.APPLY_UID = U.ID
  LEFT JOIN ERP_FEE F ON A.FEE_ID = F.ID
   LEFT JOIN ERP_SUPPLIER S ON S.ID = L.SUPPLIER
   LEFT JOIN ERP_CARDMEMBER CM ON A.BUSINESS_ID = CM.ID
   WHERE A.TYPE = 2

   UNION SELECT A.ID,
              A.FEE_ID,
              A.ISFUNDPOOL,
              A.ISKF,
              A.TYPE,
              A.STATUS,
              F.NAME AS FEE_NAME,
              U.NAME AS APPLY_UNAME,
              P.PROJECTNAME,
              P.CONTRACT,
              A.CITY_ID,
              B.APPLY_UID,
              B.APPLY_TIME,
              B.REIM_TIME,
              A.MONEY,
              NULL ADD_TIME,
              A.INPUT_TAX,
              NULL COST_OCCUR_TIME,
              NULL PURCHASE_OCCUR_TIME,
              CM.AGENCY_NAME SUPPLIER
       FROM ERP_REIMBURSEMENT_DETAIL A
       LEFT JOIN ERP_CASE C ON C.ID = A.CASE_ID
       LEFT JOIN ERP_project P ON P.ID = C.PROJECT_ID
       LEFT JOIN ERP_REIMBURSEMENT_LIST B ON A.LIST_ID = B.ID
       LEFT JOIN ERP_PURCHASE_LIST L ON A.BUSINESS_ID = L.ID
       LEFT JOIN ERP_USERS U ON B.APPLY_UID = U.ID
      LEFT JOIN ERP_FEE F ON A.FEE_ID = F.ID
       LEFT JOIN ERP_SUPPLIER S ON S.ID = L.S_ID
       LEFT JOIN ERP_POST_COMMISSION PC ON A.BUSINESS_ID = PC.ID
       LEFT JOIN ERP_CARDMEMBER CM ON PC.CARD_MEMBER_ID = CM.ID
       WHERE A.TYPE = 17

   UNION SELECT A.ID,
                A.FEE_ID,
                A.ISFUNDPOOL,
                A.ISKF,
                A.TYPE,
                A.STATUS,
                F.NAME AS FEE_NAME,
                U.NAME AS APPLY_UNAME,
                P.PROJECTNAME,
                P.CONTRACT,
                A.CITY_ID,
                B.APPLY_UID,
                B.APPLY_TIME,
                B.REIM_TIME,
                A.MONEY,
                L.EXEC_START AS ADD_TIME,
                0 AS INPUT_TAX,
                NULL AS COST_OCCUR_TIME,
                NULL AS PURCHASE_OCCUR_TIME,
                L.SUPPLIER SUPPLIER
   FROM ERP_REIMBURSEMENT_DETAIL A
   LEFT JOIN ERP_CASE C ON C.ID = A.CASE_ID
   LEFT JOIN ERP_project P ON P.ID = C.PROJECT_ID
   LEFT JOIN ERP_REIMBURSEMENT_LIST B ON A.LIST_ID = B.ID
   LEFT JOIN erp_purchaser_bee_details L ON L.ID = A.PURCHASER_BEE_ID
   LEFT JOIN ERP_USERS U ON B.APPLY_UID = U.ID
   LEFT JOIN ERP_FEE F ON A.FEE_ID = F.ID
   WHERE A.TYPE = 15 ) ";

                $export_where = " WHERE 1=1 AND STATUS = 1 AND CITY_ID = " . $city_channel . ' ';
                $export_where .= isset($_REQUEST['filterSql'])?trim($_REQUEST['filterSql']):'';
                $export_order = " ORDER BY REIM_TIME DESC";
                $export_sql = $export_sql . $export_where . $export_order;

                // 获取会员列表
                $reimData = D()->query($export_sql);

                if(count($reimData) > 1000)
                {
                    $this->error('最多下载1000条数据，查询的数据超过1000条');
                }

                $this->outputReim['TYPE']['map'] = D("ReimbursementType")->get_reim_type();

                $this->initExport($objPHPExcel, $objActSheet, '报销数据', 20, 30);
                $row = 1;
                $this->exportReimData($objActSheet, $reimData, $row);
                ob_end_clean();
                ob_start();
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
                header("Content-Type:application/force-download");
                header("Content-Type:application/vnd.ms-execl");
                header("Content-Type:application/octet-stream");
                header("Content-Type:application/download");
                header("Content-Disposition:attachment;filename=" . '报销统计' . date("YmdHis") . ".xls");
                header("Content-Transfer-Encoding:binary");

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
            } catch (Exception $e) {
                die(sprintf("%s:%s", $e->getCode(), $e->getMessage()));
            }
        }

        $reim_type_arr = D("ReimbursementType")->get_reim_type();
        $form->setMyField("TYPE", "LISTCHAR", array2listchar($reim_type_arr));
        if ($_REQUEST["faction"] == "getSelectOption") {
            $form->setMyField("PROJECTNAME", "EDITTYPE", 1)
                ->setMyField("CONTRACT", "EDITTYPE", 1);
        }
        $form->orderField = "ID DESC";

        $filterSql = $form->getFilterSql();

        $form->GABTN .= "<a id='export_reim' href='javascript:void(0);' class='btn btn-info btn-sm'>导出报表</a>";
        $where = "STATUS = 1 AND CITY_ID = " . $city_channel;
        $formHtml = $form->where($where)->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件

        $this->assign("form", $formHtml);
        $this->assign("filterSql", $filterSql);
        $this->display("reim_cumulative");
    }


    /**
     * 导出报销数据
     * @param $objActSheet
     * @param $reimData
     * @param $row
     */
    private function exportPayData(&$objActSheet, $payData, &$row) {
        $this->commonExportAction($objActSheet, $payData, $row, "应付未付数据", $this->outputPay, array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        ));
    }

    /**
     * 应付未付列表
     */
    public function pay_cumulative()
    {
        $act = isset($_REQUEST['act'])?trim($_REQUEST['act']):'';

        $city_channel = $this->channelid;
        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(216);

        //如果是导出功能
        if($act == 'export'){
            try {
                $export_sql = <<<EXPORT_SQL
SELECT
H.ID,
F.NAME AS FEE_NAME,
IS_FUNDPOOL,
IS_KF,
STATUS,
PROJECTNAME,
CONTRACT,
CITY_ID,
U.NAME AS APPLY_UNAME,
RTRIM(TO_CHAR(MONEY,'fm9999990.9999') ,'.') AS MONEY,
to_char(ADD_TIME,'YYYY-MM-DD HH24:MI:SS') AS ADD_TIME,
SUPPLIER,
to_char(COST_OCCUR_TIME,'YYYY-MM-DD HH24:MI:SS') AS COST_OCCUR_TIME,
to_char(PURCHASE_OCCUR_TIME,'YYYY-MM-DD HH24:MI:SS') AS PURCHASE_OCCUR_TIME,
SHOWTYPE
 FROM (SELECT L.ID,L.FEE_ID,L.IS_FUNDPOOL,L.IS_KF,L.STATUS,
P.PROJECTNAME,P.CONTRACT,L.CITY_ID,L.APPLY_USER_ID,(L.PRICE * L.NUM) AS MONEY,
L.ADD_TIME,s.name SUPPLIER,L.COST_OCCUR_TIME,
L.PURCHASE_OCCUR_TIME,1 AS SHOWTYPE
FROM ERP_PURCHASE_LIST L
LEFT JOIN ERP_CASE C ON C.ID = L.CASE_ID
LEFT JOIN ERP_project P ON P.ID = C.PROJECT_ID
LEFT JOIN ERP_SUPPLIER S ON S.ID = L.S_ID WHERE  L.STATUS IN (1,4)
union
SELECT PLT.ID,PLT.FEE_ID,PLT.IS_FUNDPOOL,PLT.IS_KF,
PLT.STATUS,P.PROJECTNAME,P.CONTRACT,PLT.CITY_ID,
PLT.APPLY_USER_ID,
L.REIM_MONEY AS MONEY,
L.EXEC_START AS ADD_TIME,
L.SUPPLIER SUPPLIER,
NULL AS COST_OCCUR_TIME,
NULL AS PURCHASE_OCCUR_TIME,
2 AS SHOWTYPE
FROM erp_purchaser_bee_details L
LEFT JOIN erp_purchase_list PLT ON L.P_ID = PLT.ID
LEFT JOIN ERP_CASE C ON C.ID = PLT.CASE_ID
LEFT JOIN ERP_project P ON P.ID = C.PROJECT_ID
WHERE L.STATUS NOT IN (2,3)
union
select B.ID,
61 AS FEE_ID,
0 AS IS_FUNDPOOL,
1 AS IS_KF,
NULL AS STATUS,
P.PROJECTNAME,
P.CONTRACT,
P.CITY_ID,
B.Auser_Id AS APPLY_USER_ID,
B.AMOUNT AS MONEY,
B.ADDTIME AS  ADD_TIME,
s.name SUPPLIER,
NULL AS COST_OCCUR_TIME,
NULL AS PURCHASE_OCCUR_TIME,
3 AS SHOWTYPE
FROM erp_benefits B
left join erp_project P On B.project_id = P.ID
LEFT JOIN ERP_SUPPLIER S ON S.ID = B.SUPPLIER
where B.type = 1 and B.Status = 3 and B.iscost != 4
union
select B.ID,80 AS FEE_ID,1 AS IS_FUNDPOOL,
1 AS IS_KF,NULL AS STATUS,P.PROJECTNAME,
P.CONTRACT,
P.CITY_ID,
B.Auser_Id AS APPLY_USER_ID,
B.AMOUNT AS MONEY,
B.ADDTIME AS  ADD_TIME,
s.name SUPPLIER,
NULL AS COST_OCCUR_TIME,
NULL AS PURCHASE_OCCUR_TIME,
4 AS SHOWTYPE
FROM erp_benefits B
left join erp_project P On B.project_id = P.ID
LEFT JOIN ERP_SUPPLIER S ON S.ID = B.SUPPLIER
where B.type = 2 and B.iscost != 4
union
select L.ID,
83 AS FEE_ID,
L.ISFUNDPOOL AS IS_FUNDPOOL,
L.ISKF AS IS_KF,
NULL AS STATUS,
P.PROJECTNAME,
P.CONTRACT,
P.CITY_ID,
L.ADD_UID AS APPLY_USER_ID,
L.MONEY,
L.CREATTIME AS ADD_TIME,
NULL AS SUPPLIER,
NULL AS COST_OCCUR_TIME,
NULL AS PURCHASE_OCCUR_TIME,
5 AS SHOWTYPE
FROM ERP_LOCALE_GRANTED L
left join erp_project P On L.PRJ_ID = P.ID
where L.REIM_STATUS = 1) H
LEFT JOIN ERP_USERS U ON H.APPLY_USER_ID = U.ID
LEFT JOIN ERP_FEE F ON H.FEE_ID = F.ID
EXPORT_SQL;
                $export_where = " WHERE 1=1 AND CITY_ID = " . $city_channel . ' ';
                $export_where .= isset($_REQUEST['filterSql'])?trim($_REQUEST['filterSql']):'';
                $export_order = " ORDER BY ADD_TIME DESC";
                $export_sql = $export_sql . $export_where . $export_order;

                // 获取会员列表
                $payData = D()->query($export_sql);

                if(count($payData) > 1000)
                {
                    $this->error('最多下载1000条数据，查询的数据超过1000条');
                }

                $this->initExport($objPHPExcel, $objActSheet, '应付未付数据', 20, 30);
                $row = 1;
                $this->exportPayData($objActSheet, $payData, $row);
                ob_end_clean();
                ob_start();
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
                header("Content-Type:application/force-download");
                header("Content-Type:application/vnd.ms-execl");
                header("Content-Type:application/octet-stream");
                header("Content-Type:application/download");
                header("Content-Disposition:attachment;filename=" . '应付未付统计' . date("YmdHis") . ".xls");
                header("Content-Transfer-Encoding:binary");

                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
                $objWriter->save('php://output');
            } catch (Exception $e) {
                die(sprintf("%s:%s", $e->getCode(), $e->getMessage()));
            }
        }

//        $reim_type_arr = D("ReimbursementType")->get_reim_type();
//        $form->setMyField("TYPE", "LISTCHAR", array2listchar($reim_type_arr));
        if ($_REQUEST["faction"] == "getSelectOption") {
            $form->setMyField("PROJECTNAME", "EDITTYPE", 1)
                ->setMyField("CONTRACT", "EDITTYPE", 1);
        }
        $form->orderField = "ADD_TIME DESC";

        $filterSql = $form->getFilterSql();

        $form->GABTN .= "<a id='export_pay' href='javascript:void(0);' class='btn btn-info btn-sm'>导出报表</a>";
        $where = " CITY_ID = " . $city_channel;

        $formHtml = $form->where($where)->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件

        $this->assign("form", $formHtml);
        $this->assign("filterSql", $filterSql);
        $this->display("pay_cumulative");
    }


    /****
     * 自定义报表
     * 业务范围：电商、分销、硬广、活动
     ****/
    function custom()
    {
        /*****获取城市*****/
        $citylist = M('Erp_city')->where("ISVALID=-1")->select();
        foreach ($citylist as $k => $one) {
            $cfg['city'][$one['ID']] = $one['NAME'];
        }

        /****城市下拉联想****/
        if ($_REQUEST['act'] == "getprj") {
            $search_word = iconv("utf-8", "gbk//ignore", trim($_REQUEST['q']));
            $res = M('Erp_project')->where("PROJECTNAME LIKE '%$search_word%'")->select();
            if (is_array($res)) {
                foreach ($res as $k => $r) {
                    $cityone = M('Erp_city')->where("ID=" . $r['CITY_ID'])->find();
                    $prj_arr[] = array('prjid' => $r['ID'], 'prjname' => iconv("gbk", "utf-8//IGNORE", $r['PROJECTNAME']), 'city' => iconv("gbk", "utf-8//IGNORE", $cityone['NAME']));
                }
            }
            exit(json_encode($prj_arr));
        }

        /*****保存用户的配置行为****/
        if ($_REQUEST['act'] == "savecfg") {
            $cfgone = M('Erp_user_cfg')->where("TYPE = 'REPORT_CUSTOM' AND ADDUID ='" . $_SESSION['uinfo']['uid'] . "'")->find();
            $info = array();

            $cfg = explode(',', $_REQUEST['cfg']);

            if (!$cfgone) {
                $info['TYPE'] = 'REPORT_CUSTOM';
                $info['CONFIG'] = serialize($cfg);
                $info['DATELINE'] = date('Y-m-d H:i:s', time());
                $info['ADDUID'] = $_SESSION['uinfo']['uid'];
                M('Erp_user_cfg')->add($info);

                exit("true");
            } else {
                $info['CONFIG'] = serialize($cfg);
                $info['DATELINE'] = date('Y-m-d H:i:s', time());

                M('Erp_user_cfg')->where("ID='" . intval($cfgone['ID']) . "'")->save($info);

                exit("true");
            }
        }

        /******自定义统计开始********/
        //参数接收
        $pageurl = __ACTION__;
        $pagesize = 10;

        //业务类型
        $search_yw = isset($_REQUEST['search_yw'])?intval($_REQUEST['search_yw']) : 1;
        //页码
        $page = isset($_GET['pn']) ? (int)$_GET['pn'] : 1;
        //搜索开始时间
        $search_btime = trim($_REQUEST['search_btime']);
        //搜索结束时间
        $search_etime = trim($_REQUEST['search_etime']);
        //财务确认状态
        $fin_confirm = intval($_REQUEST['check_finan_con']);
        //项目状态
        $search_state = intval($_REQUEST['search_state']);
        //查询类型（城市、项目）
        $check_con = isset($_REQUEST['check_con']) ? intval($_REQUEST['check_con']) : 1;
        //查询字段
        $search_checked = $_REQUEST['search_checked'];
        //查询城市
        $arr_search_city = isset($_REQUEST['arr_search_city']) ? $_REQUEST['arr_search_city'] : null;
        $search_city = isset($_REQUEST['search_city']) ? $_REQUEST['search_city'] : $this->city_id;
        //查询项目
        $arr_search_prj = isset($_REQUEST['arr_search_prj']) ? $_REQUEST['arr_search_prj'] : null;
        $search_prj = isset($_REQUEST['search_prj']) ? $_REQUEST['search_prj'] : 0;
        //是否是项目查询
        $whole_prj_checkbox = intval($_REQUEST['whole_prj_checkbox']);
        //是否打印
        $print = isset($_REQUEST['print']) ? $_REQUEST['print'] : '';

        if($arr_search_city)
            $search_city = implode(",",$arr_search_city);

        if($search_city)
            $arr_search_city = explode(",",$search_city);

        //如果城市数量大于1
        if(count($arr_search_city)>1)
            unset($whole_prj_checkbox);

        if($arr_search_prj[0])
            $search_prj = implode(",",$arr_search_prj);

        if(!$search_checked){
            //获取配置信息
            $r = M('Erp_user_cfg')->where("TYPE='REPORT_CUSTOM' AND ADDUID='" . $_SESSION['uinfo']['uid'] . "'")->find();
            $search_checked = 'card_count,card_profit,cfm_income,final_profit,final_profit_rate';
            if ($r)
                $search_checked = implode(',', unserialize($r['CONFIG']));
        }
        $arr_search_checked = explode(',', $search_checked);

        //是否是城市查询
        $is_city_search = ($search_prj || $whole_prj_checkbox)?false:true;

        $pageurl .= "&search_yw=$search_yw&pn=$page&search_btime=$search_btime&search_etime=$search_etime&check_finan_con=$fin_confirm";
        $pageurl .= "&check_con=$check_con&search_checked=$search_checked&search_city=$search_city&whole_prj_checkbox=$whole_prj_checkbox";

        //查询归位
        switch($check_con){
            case 1:
                unset($search_prj);
                unset($arr_search_prj);
                break;
            case 2:
                unset($search_city);
                unset($arr_search_city);
                unset($whole_prj_checkbox);
                break;
        }

        //业务类型
        $case_type = D("ProjectCase")->get_conf_case_type_remark();
        //产品
        unset($case_type['5']);
        //项目活动
        unset($case_type['7']);
        //非我方收筹
        unset($case_type['8']);

        $report = D("Report");

        /***业务下类别选择***/
        foreach ($case_type as $key=>$val) {
            $custom_checked[$key] = $report->scale_custom($key,$is_city_search);
        }

        foreach($arr_search_checked as $key=>$val){
            if($custom_checked[$search_yw][$val])
                $arr_search_field[$val] = $custom_checked[$search_yw][$val];
        }

        switch($search_yw){
            case 1:
                $html = $report->ds_html($search_btime,$search_etime,$fin_confirm,$search_state,$arr_search_field,$arr_search_city,$arr_search_prj,$is_city_search,$page,$pagesize);
                break;
            case 2:
                $html = $report->fx_html($search_btime,$search_etime,$fin_confirm,$search_state,$arr_search_field,$arr_search_city,$arr_search_prj,$is_city_search,$page,$pagesize);
                break;
            case 3:
                $html = $report->yg_html($search_btime,$search_etime,$fin_confirm,$search_state,$arr_search_field,$arr_search_city,$arr_search_prj,$is_city_search,$page,$pagesize);
                break;
            case 4:
                $html = $report->hd_html($search_btime,$search_etime,$fin_confirm,$search_state,$arr_search_field,$arr_search_city,$arr_search_prj,$is_city_search,$page,$pagesize);
                break;
        }

        //打印
        if($print) {
            $html = "<table border='1'>$html</table>";
            exportHtml("自定义统计-{$case_type[$search_yw]}", $html);
        }

        //项目查询  分页
        if(!$is_city_search){
            //如果是查询全部项目  ---- 非搜索查询
            if($search_state)
                $where_page = ' AND  C.FSTATUS=' . $search_state;

            $sql = "SELECT C.PROJECT_ID,C.ID,P.CITY_ID,P.PROJECTNAME FROM ERP_CASE C LEFT JOIN ERP_PROJECT P ON C.PROJECT_ID = P.ID WHERE P.CITY_ID IN($search_city) AND P.STATUS != 2 AND C.SCALETYPE = $search_yw  $where_page ORDER BY P.ID DESC";
            if ($arr_search_prj[0])
                $sql = "SELECT C.PROJECT_ID,C.ID,P.CITY_ID,P.PROJECTNAME FROM ERP_CASE C LEFT JOIN ERP_PROJECT P ON C.PROJECT_ID = P.ID WHERE P.ID IN($search_prj) AND P.STATUS != 2 AND C.SCALETYPE = $search_yw  $where_page ORDER BY P.ID DESC";

            $ret = M()->query($sql);
            $count_num = count($ret);

            //分页代码
            $page = $page;
            $offset = ($page - 1) * $pagesize;
            $page_nav = page($count_num, $pagesize, $page, $pageurl);
        }

        //已选择
        $this->assign("custom_checked", $custom_checked);

        //项目状态
        $this->assign('prjState', $this->custom_pro_status);
        //业务类型
        $this->assign('case_type', $case_type);
        $this->assign('search_yw', $search_yw);

        //开始时间
        $this->assign('search_btime', $search_btime);
        //结束时间
        $this->assign('search_etime', $search_etime);
        //搜索项目状态
        $this->assign('search_state', $search_state);

        //权限城市
        $p_authcity = explode(',', $this->powercity);
        $rev_authCity = implode(',', $p_authcity);

        $this->assign('rev_authCity', $rev_authCity);
        $this->assign('p_authcity', $p_authcity);
        $this->assign('cityname', $this->city_config);

        //城市搜索
        $this->assign('search_city', $search_city);
        $this->assign('arr_search_city', $arr_search_city);

        //财务状态
        $this->assign('check_finan_con', $fin_confirm);

        //查询方式
        $this->assign('check_con', $check_con);

        //获取标签
        $this->assign('arr_search_checked', $arr_search_checked);
        $this->assign('search_checked', $search_checked);

        //是否获取全部项目
        $this->assign('whole_prj_checkbox', $whole_prj_checkbox);

        //项目列表
        $this->assign('search_prj', $search_prj);
        $this->assign('arr_search_prj', $arr_search_prj);

        //获取所有的项目
        $arr_projects = array();
        $sql = "SELECT ID,CITY_ID,PROJECTNAME FROM ERP_PROJECT WHERE STATUS<>2 ORDER BY PROJECTNAME ASC";
        $rs = M()->query($sql);
        foreach ($rs as $r) {
            $arr_projects[$r['ID']] = '[' . $cfg['city'][$r['CITY_ID']] . ']' . $r['PROJECTNAME'];
        }

        $this->assign('arr_projects', $arr_projects);

        //表格内容
        $this->assign('html', $html);

        //分页
        $this->assign('pageurl', $pageurl);

        //只有项目查询有分页
        if(!$is_city_search)
            $this->assign('page_nav', $page_nav);

        $this->display("custom");
    }


    //	办卡统计--（城市办卡、项目办卡）
    function card_statistics()
    {

        //初始化
        $where_card = $where_back = $where_deal = $where_prj = ' WHERE 1=1 AND A.status = 1 AND A.city_id = ' . $this->city_id . ' ';
        //办卡用户表
        $tbl = "erp_cardmember";
        //项目表
        $tbl_prj = "erp_project";
        //付款明细表
        $tbl_pay_ment = "erp_member_payment";

        //分页参数
        $pageurl = U("Report/card_statistics");

        //操作行为
        $act = trim($_REQUEST['act']);

        //城市办卡
        if ($act == "citycard") {

            $pageurl .= '&act=citycard';
            $cardCondition = array("全部", "项目执行中", "项目已结束");

            $stat = array();
            $month_data = array();

            //循环获取数据
            $where_sel = " WHERE 1= 1 AND A.STATUS = 1 AND A.CITY_ID = {$this->city_id}  ";
            $where_select = '';

            //开始时间
            $search_btime = isset($_REQUEST['search_btime']) ? trim($_REQUEST['search_btime']) : "2016-01-01";
            $pageurl .= '&search_btime=' . $search_btime;

            //结束时间
            $search_etime = isset($_REQUEST['search_etime']) ? trim($_REQUEST['search_etime']) : "2016-12-31";
            $pageurl .= '&search_etime=' . $search_etime;

            //项目状态
            if ($search_cardcon = intval($_REQUEST['search_cardcon'])) {
                $where_select .= $search_cardcon == 1 ? " AND P.BSTATUS = 2" : " AND P.BSTATUS = 3";
                $pageurl .= '&search_cardcon=' . $search_cardcon;
            }

            $where_sel .= $where_select;

            //办卡时间
            $card_time = "AND A.cardtime >= to_date('" . $search_btime . "','yyyy-mm-dd') AND A.cardtime <=  to_date('" . $search_etime . "','yyyy-mm-dd')";
            //退卡时间
            $back_time = "AND A.backtime >= to_date('" . $search_btime . "','yyyy-mm-dd') AND A.backtime <=  to_date('" . $search_etime . "','yyyy-mm-dd')";

            //办卡量（跟状态没有关系）
            $sql = "SELECT COUNT(1) AS CNT,to_char(CARDTIME,'YYYY-MM') AS CARDMONTH   FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel $card_time GROUP BY to_char(CARDTIME,'YYYY-MM')";
            $card_count = $this->model->query($sql);

            foreach ($card_count as $key => $val) {
                $month_data[$val['CARDMONTH']]['card_count'] = $val['CNT'];
            }

            //办卡收益
            $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY,to_char(CARDTIME,'YYYY-MM') AS CARDMONTH  FROM $tbl_pay_ment B INNER JOIN $tbl A  ON B.MID = A.ID LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID  $where_sel  $card_time GROUP BY to_char(CARDTIME,'YYYY-MM')";
            $card_profit = $this->model->query($sql);

            foreach ($card_profit as $key => $val) {
                $month_data[$val['CARDMONTH']]['card_profit'] = $val['TRADE_MONEY'];
            }

            //退卡量
            $sql = "SELECT COUNT(1) AS CNT,to_char(BACKTIME,'YYYY-MM') AS BACKMONTH FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel  $back_time AND A.CARDSTATUS = 4  GROUP BY to_char(BACKTIME,'YYYY-MM')";
            $back_count = $this->model->query($sql);

            foreach ($back_count as $key => $val) {
                $month_data[$val['BACKMONTH']]['back_count'] = $val['CNT'];
            }

            //总退卡额
            $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY,to_char(BACKTIME,'YYYY-MM') AS BACKMONTH FROM $tbl_pay_ment B INNER JOIN $tbl A ON B.MID = A.ID LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel  $back_time AND A.CARDSTATUS = 4 GROUP BY to_char(BACKTIME,'YYYY-MM')";
            $back_money = $this->model->query($sql);

            foreach ($back_money as $key => $val) {
                $month_data[$val['BACKMONTH']]['back_money'] = $val['TRADE_MONEY'];
            }

            //总成交套数
            $sql = "SELECT COUNT(1) AS CNT,to_char(CARDTIME,'YYYY-MM') AS DEALMONTH FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel $card_time AND A.CARDSTATUS = 3 AND A.INVOICE_STATUS IN(2,3)  GROUP BY to_char(CARDTIME,'YYYY-MM')";
            $deal_count = $this->model->query($sql);

            foreach ($deal_count as $key => $val) {
                $month_data[$val['DEALMONTH']]['deal_count'] = $val['CNT'];
            }

            //总成交额
            $sql = "SELECT SUM(PAID_MONEY) AS PAIDMONEY,to_char(CARDTIME,'YYYY-MM') AS DEALMONTH FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel $card_time AND A.CARDSTATUS = 3 AND A.INVOICE_STATUS IN(2,3)  GROUP BY to_char(CARDTIME,'YYYY-MM')";
            $deal_money = $this->model->query($sql);

            foreach ($deal_money as $key => $val) {
                $month_data[$val['DEALMONTH']]['deal_money'] = $val['PAIDMONEY'];
            }

            /**合计开始**/
            $sql = "SELECT P.ID,nvl(B.CARDCOUNT,0) AS CNT FROM $tbl_prj P LEFT JOIN (SELECT COUNT(ID) AS CARDCOUNT,PRJ_ID FROM $tbl A WHERE A.CITY_ID = {$this->city_id} AND A.STATUS = 1 $card_time GROUP BY PRJ_ID) B ON B.PRJ_ID = P.ID WHERE 1=1 $where_select AND  P.STATUS != 2 AND P.CITY_ID = {$this->city_id}";
            $p_ret = $this->model->query($sql);
            $stat['all_prj'] = count($p_ret);

            $sql = "SELECT COUNT(1) AS CNT  FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel $card_time";
            $count = $this->model->query($sql);
            $stat['all_card'] = $count[0]['CNT'];

            $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY  FROM $tbl_pay_ment B INNER JOIN $tbl A ON B.MID = A.ID LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel  $card_time";
            $count = $this->model->query($sql);
            $stat['all_card_profit'] = $count[0]['TRADE_MONEY'];

            $sql = "SELECT COUNT(1) AS CNT FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel  $back_time AND A.CARDSTATUS = 4  ";
            $count = $this->model->query($sql);
            $stat['all_back'] = $count[0]['CNT'];

            $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY FROM $tbl_pay_ment B INNER JOIN $tbl A ON B.MID = A.ID LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel  $back_time AND A.CARDSTATUS = 4 ";
            $count = $this->model->query($sql);
            $stat['all_back_money'] = $count[0]['TRADE_MONEY'];

            $sql = "SELECT COUNT(1) AS CNT FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel AND A.CARDSTATUS = 3 AND A.INVOICE_STATUS IN(2,3) ";
            $count = $this->model->query($sql);
            $stat['all_deal'] = $count[0]['CNT'];

            $sql = "SELECT SUM(PAID_MONEY) AS PAIDMONEY FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel $card_time AND A.CARDSTATUS = 3 AND A.INVOICE_STATUS IN(2,3)";
            $count = $this->model->query($sql);
            $stat['all_deal_money'] = $count[0]['PAIDMONEY'];

            krsort($month_data);

            /***表格渲染开始***/
            $html = '<table cellpadding="0" cellspacing="0" border="1" width="100%" class="jbtab tdbgwt" id="table_style">';
            $html .= '<tr><th>序号</th><th>日期</th><th>总项目数</th><th>总办卡数</th><th>总办卡收益</th><th>总退卡数	</th><th>总退卡额</th><th>总成交套数</th><th>总成交额</th></tr>';

            if (is_array($month_data)) {
                $i = 1;
                foreach ($month_data as $k => $r) {
                    $html .= "<tr>";
                    $html .= "<td>{$i}</td>";
                    $html .= "<td>{$k}</td>";
                    $html .= "<td> - </td>";
                    $html .= "<td>" . intval($r['card_count']) . "</td>";
                    $html .= "<td>" . intval($r['card_profit']) . "</td>";
                    $html .= "<td>" . intval($r['back_count']) . "</td>";
                    $html .= "<td>" . intval($r['back_money']) . "</td>";
                    $html .= "<td>" . intval($r['deal_count']) . "</td>";
                    $html .= "<td>" . intval($r['deal_money']) . "</td>";
                    $html .= "</tr>";
                    $i++;
                }
            }

            $html .= "<td>合计</td>";
            $html .= "<td> - </td>";
            $html .= "<td>" . intval($stat['all_prj']) . "</td>";
            $html .= "<td>" . intval($stat['all_card']) . "</td>";
            $html .= "<td>" . intval($stat['all_card_profit']) . "</td>";
            $html .= "<td>" . intval($stat['all_back']) . "</td>";
            $html .= "<td>" . intval($stat['all_back_money']) . "</td>";
            $html .= "<td>" . intval($stat['all_deal']) . "</td>";
            $html .= "<td>" . intval($stat['all_deal_money']) . "</td>";

            $html .= "</table>";
            /***表格渲染结束***/

            if ($_REQUEST['export'])
                exportHtml("城市办卡统计", $html);

            //渲染
            $this->assign('act', $act);
            $this->assign('search_btime', $search_btime);
            $this->assign('search_etime', $search_etime);
            $this->assign('cardCondition', $cardCondition);
            $this->assign('search_cardcon', $search_cardcon);

            $this->assign('month_data', $month_data);
            $this->assign('html', $html);
            $this->assign('pageurl', $pageurl);
            $this->assign('stat', $stat);

            $this->display("card_statistics_citycard");
            die();
        }

        //项目办卡
        if ($_REQUEST['act'] == "prjcard") {
            //页码
            $page = isset($_REQUEST['pn']) ? intval($_REQUEST['pn']) : 1;
            //页码大小
            $pagesize = 20;
            //URL链接
            $pageurl .= '&act=prjcard';

            //循环获取数据
            $where_sel = " WHERE 1= 1 AND A.STATUS = 1 AND A.CITY_ID = {$this->city_id}  AND P.STATUS != 2";
            //获取所有的项目
            $where_select = '';

            $order_sel = ' ';

            $cardCondition = array("全部", "项目执行中", "项目已结束");
            $cardShow = array("只显示总办卡数", "按月显示");
            $cardOrder = array("项目添加时间", "总办卡数");

            $search_btime = isset($_REQUEST['search_btime']) ? trim($_REQUEST['search_btime']) : '2016-01-01';
            $search_etime = isset($_REQUEST['search_etime']) ? trim($_REQUEST['search_etime']) : '2016-12-31';
            $pageurl .= '&search_btime=' . $search_btime;
            $pageurl .= '&search_etime=' . $search_etime;

            //项目条件
            if ($search_cardcon = intval($_REQUEST['search_cardcon'])) {
                $where_select .= $search_cardcon == 1 ? " AND P.BSTATUS = 2" : " AND (P.BSTATUS = 3 OR P.BSTATUS = 4 OR P.BSTATUS = 5)";
                $pageurl .= '&search_cardcon=' . $search_cardcon;
            } else {
                $where_select .= " AND P.BSTATUS IN ({$this->static_status['ds']})";
            }

            //项目名称
            if ($search_prjname = trim($_REQUEST['search_prjname'])) {
                $where_select .= " AND P.PROJECTNAME LIKE '%" . $search_prjname . "%'";
                $pageurl .= '&search_prjname=' . urlencode($search_prjname);
            }

            //显示方式
            if ($search_cardshow = intval($_REQUEST['search_cardshow'])) {
                $pageurl .= '&search_cardshow=' . $search_cardshow;
            }

            $where_sel .= $where_select;

            //排序方式
            $search_cardorder = intval($_REQUEST['search_cardorder']);
            $order_sel .= intval($_REQUEST['search_cardorder']) == 0 ? " ORDER BY P.ID DESC" : " ORDER BY CNT DESC";
            $pageurl .= '&search_cardorder=' . $search_cardorder;

            //按总数显示
            if ($search_cardshow == 0) {
                //办卡时间
                $card_time = "AND A.CARDTIME >= to_date('" . $search_btime . "','yyyy-mm-dd') AND A.CARDTIME <=  to_date('" . $search_etime . "','yyyy-mm-dd')";
                //退卡时间
                $back_time = "AND A.BACKTIME >= to_date('" . $search_btime . "','yyyy-mm-dd') AND A.BACKTIME <=  to_date('" . $search_etime . "','yyyy-mm-dd')";

                //办卡量（跟状态没有关系）
                $sql = "SELECT P.ID,nvl(B.CARDCOUNT,0) AS CNT FROM $tbl_prj P LEFT JOIN (SELECT COUNT(ID) AS CARDCOUNT,PRJ_ID FROM $tbl A WHERE A.CITY_ID = {$this->city_id} AND A.STATUS = 1 $card_time GROUP BY PRJ_ID) B ON B.PRJ_ID = P.ID WHERE 1=1 $where_select AND  P.STATUS != 2 AND P.CITY_ID = {$this->city_id} $order_sel";
                $p_ret = $this->model->query($sql);
                $count_num = count($p_ret);

                $sql = "SELECT * FROM ( SELECT A.*, ROWNUM RN FROM ($sql) A WHERE ROWNUM <= $page*$pagesize ) WHERE RN >  ($page-1)*$pagesize";
                $card_count = $this->model->query($sql);

                $PIDS = array();
                foreach ($card_count as $key => $val) {
                    $PIDS[] = $val['ID'];
                    $month_data[$val['ID']]['card_count'] = $val['CNT'];
                }

                $PIDS = array_unique($PIDS);
                $PIDS = implode(",", $PIDS);

                //办卡收益
                $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY,P.ID AS PID  FROM $tbl_pay_ment B INNER JOIN $tbl A  ON B.MID = A.ID LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID  $where_sel  $card_time AND P.ID IN ($PIDS) GROUP BY P.ID";
                $card_profit = $this->model->query($sql);

                foreach ($card_profit as $key => $val) {
                    $month_data[$val['PID']]['card_profit'] = $val['TRADE_MONEY'];
                }

                //退卡量
                $sql = "SELECT COUNT(1) AS CNT,P.ID AS PID FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel  $back_time AND A.CARDSTATUS = 4  AND P.ID IN ($PIDS)  GROUP BY P.ID";
                $back_count = $this->model->query($sql);

                foreach ($back_count as $key => $val) {
                    $month_data[$val['PID']]['back_count'] = $val['CNT'];
                }

                //总退卡额
                $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY,P.ID AS PID FROM $tbl_pay_ment B INNER JOIN $tbl A ON B.MID = A.ID LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel  $back_time AND A.CARDSTATUS = 4 AND P.ID IN ($PIDS) GROUP BY P.ID";
                $back_money = $this->model->query($sql);

                foreach ($back_money as $key => $val) {
                    $month_data[$val['PID']]['back_money'] = $val['TRADE_MONEY'];
                }

                //总成交套数
                $sql = "SELECT COUNT(1) AS CNT,P.ID AS PID FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel $card_time AND A.CARDSTATUS = 3 AND A.INVOICE_STATUS IN(2,3) AND P.ID IN ($PIDS)  GROUP BY P.ID";
                $deal_count = $this->model->query($sql);

                foreach ($deal_count as $key => $val) {
                    $month_data[$val['PID']]['deal_count'] = $val['CNT'];
                }

                //总成交额
                $sql = "SELECT SUM(PAID_MONEY) AS PAIDMONEY,P.ID AS PID FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel $card_time AND A.CARDSTATUS = 3 AND A.INVOICE_STATUS IN(2,3) AND P.ID IN ($PIDS)  GROUP BY P.ID";
                $deal_money = $this->model->query($sql);

                foreach ($deal_money as $key => $val) {
                    $month_data[$val['PID']]['deal_money'] = $val['PAIDMONEY'];
                }

                //循环处理
                foreach ($month_data as $key => $val) {
                    $sql = "SELECT  C.PROJECTNAME,C.CONTRACT,C.CITY_ID,A.FROMDATE,A.TODATE  FROM ERP_PRJBUDGET A INNER JOIN ERP_CASE B ON A.CASE_ID = B.ID INNER JOIN ERP_PROJECT C ON B.PROJECT_ID = C.ID ";
                    $sql .= "WHERE A.SCALETYPE = 1 AND C.ID = {$key}";
                    $project_data = M()->query($sql);

                    if ($project_data) {
                        $city_name = D("City")->get_city_info_by_id($project_data[0]['CITY_ID'], array("NAME"));

                        $month_data[$key]['projectname'] = $project_data[0]['PROJECTNAME'];
                        $month_data[$key]['contract'] = $project_data[0]['CONTRACT'];
                        $month_data[$key]['city_name'] = $city_name['NAME'];
                        $month_data[$key]['etime'] = oracle_date_format($project_data[0]['FROMDATE'], "Y-m-d") . "至" . oracle_date_format($project_data[0]['TODATE'], "Y-m-d");
                    }
                }

                /***画表格开始***/
                $html = '<table cellpadding="0" cellspacing="0" width="100%" class="jbtab tdbgwt" id="table_style">';
                $html .= '<tr><th>编号</th><th>项目名称</th><th>合同编号</th><th>城市</th><th>执行周期</th><th>总办卡数</th><th>总办卡收益</th><th>总退卡数</th><th>总退卡额</th><th>总成交套数</th><th>总成交额</th></tr>';

                if (is_array($month_data)) {
                    foreach ($month_data as $k => $r) {
                        $html .= "<tr>";
                        $html .= "<td>$k</td>";
                        $html .= "<td>{$r['projectname']}</td>";
                        $html .= "<td>{$r['contract']}</td>";
                        $html .= "<td>{$r['city_name']}</td>";
                        $html .= "<td>{$r['etime']}</td>";
                        $html .= "<td>" . intval($r['card_count']) . "</td>";
                        $html .= "<td>" . intval($r['card_profit']) . "</td>";
                        $html .= "<td>" . intval($r['back_count']) . "</td>";
                        $html .= "<td>" . intval($r['back_money']) . "</td>";
                        $html .= "<td>" . intval($r['deal_count']) . "</td>";
                        $html .= "<td>" . intval($r['deal_money']) . "</td>";
                        $html .= "</tr>";
                    }
                }
                $html .= "</table>";
                /***画表格结束***/

            }

            //按月份显示
            if ($search_cardshow == 1) {
                //办卡时间
                $card_time = "AND A.CARDTIME >= to_date('" . $search_btime . "','yyyy-mm-dd') AND A.CARDTIME <=  to_date('" . $search_etime . "','yyyy-mm-dd')";
                //退卡时间
                $back_time = "AND A.BACKTIME >= to_date('" . $search_btime . "','yyyy-mm-dd') AND A.BACKTIME <=  to_date('" . $search_etime . "','yyyy-mm-dd')";

                //办卡量（跟状态没有关系）
                $sql = "SELECT P.ID,nvl(B.CARDCOUNT,0) AS CNT FROM $tbl_prj P LEFT JOIN (SELECT COUNT(ID) AS CARDCOUNT,PRJ_ID FROM $tbl A WHERE A.CITY_ID = {$this->city_id} AND A.STATUS = 1 $card_time GROUP BY PRJ_ID) B ON B.PRJ_ID = P.ID WHERE 1=1 $where_select AND  P.STATUS != 2 AND P.CITY_ID = {$this->city_id} $order_sel";
                $p_ret = $this->model->query($sql);
                $count_num = count($p_ret);

                $sql = "SELECT * FROM ( SELECT A.*, ROWNUM RN FROM ($sql) A WHERE ROWNUM <= $page*$pagesize ) WHERE RN >  ($page-1)*$pagesize";
                $pid_count = $this->model->query($sql);

                $PIDS = array();
                foreach ($pid_count as $key => $val) {
                    $PIDS[] = $val['ID'];
                }
                $PIDS = array_unique($PIDS);
                $PIDS = implode(",", $PIDS);

                //办卡量（跟状态没有关系）
                $sql = "SELECT P.ID AS PID,COUNT(1) AS CNT,to_char(CARDTIME,'YYYY-MM') AS CARDMONTH  FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel $card_time AND P.ID IN($PIDS) GROUP BY P.ID,to_char(CARDTIME,'YYYY-MM') $order_sel";
                $card_count = $this->model->query($sql);

                foreach ($card_count as $key => $val) {
                    $month_data[$val['PID']]['month'][$val['CARDMONTH']]['card_count'] = $val['CNT'];
                }

                //办卡收益
                $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY,P.ID AS PID,to_char(CARDTIME,'YYYY-MM') AS CARDMONTH  FROM $tbl_pay_ment B INNER JOIN $tbl A  ON B.MID = A.ID LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID  $where_sel  $card_time  AND P.ID IN($PIDS)  GROUP BY P.ID,to_char(CARDTIME,'YYYY-MM')";
                $card_profit = $this->model->query($sql);

                foreach ($card_profit as $key => $val) {
                    $month_data[$val['PID']]['month'][$val['CARDMONTH']]['card_profit'] = $val['TRADE_MONEY'];
                }

                //退卡量
                $sql = "SELECT COUNT(1) AS CNT,P.ID AS PID,to_char(BACKTIME,'YYYY-MM') AS BACKMONTH FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel  $back_time AND A.CARDSTATUS = 4  AND P.ID IN($PIDS)   GROUP BY P.ID,to_char(BACKTIME,'YYYY-MM')";
                $back_count = $this->model->query($sql);

                foreach ($back_count as $key => $val) {
                    $month_data[$val['PID']]['month'][$val['BACKMONTH']]['back_count'] = $val['CNT'];
                }

                //总退卡额
                $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY,P.ID AS PID,to_char(BACKTIME,'YYYY-MM') AS BACKMONTH FROM $tbl_pay_ment B INNER JOIN $tbl A ON B.MID = A.ID LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel  $back_time AND A.CARDSTATUS = 4  AND P.ID IN($PIDS)  GROUP BY P.ID,to_char(BACKTIME,'YYYY-MM')";
                $back_money = $this->model->query($sql);

                foreach ($back_money as $key => $val) {
                    $month_data[$val['PID']]['month'][$val['BACKMONTH']]['back_money'] = $val['TRADE_MONEY'];
                }

                //总成交套数
                $sql = "SELECT COUNT(1) AS CNT,P.ID AS PID,to_char(CARDTIME,'YYYY-MM') AS DEALMONTH FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel $card_time AND A.CARDSTATUS = 3 AND A.INVOICE_STATUS IN(2,3)  AND P.ID IN($PIDS)   GROUP BY P.ID,to_char(CARDTIME,'YYYY-MM')";
                $deal_count = $this->model->query($sql);

                foreach ($deal_count as $key => $val) {
                    $month_data[$val['PID']]['month'][$val['DEALMONTH']]['deal_count'] = $val['CNT'];
                }

                //总成交额
                $sql = "SELECT SUM(PAID_MONEY) AS PAIDMONEY,P.ID AS PID,to_char(CARDTIME,'YYYY-MM') AS DEALMONTH FROM $tbl A LEFT JOIN $tbl_prj P ON A.PRJ_ID=P.ID $where_sel $card_time AND A.CARDSTATUS = 3 AND A.INVOICE_STATUS IN(2,3)  AND P.ID IN($PIDS)   GROUP BY P.ID,to_char(CARDTIME,'YYYY-MM')";
                $deal_money = $this->model->query($sql);

                foreach ($deal_money as $key => $val) {
                    $month_data[$val['PID']]['month'][$val['DEALMONTH']]['deal_money'] = $val['PAIDMONEY'];
                }

                //循环处理
                foreach ($month_data as $key => $val) {
                    $sql = "SELECT  C.PROJECTNAME,C.CONTRACT,C.CITY_ID,A.FROMDATE,A.TODATE  FROM ERP_PRJBUDGET A INNER JOIN ERP_CASE B ON A.CASE_ID = B.ID INNER JOIN ERP_PROJECT C ON B.PROJECT_ID = C.ID ";
                    $sql .= "WHERE A.SCALETYPE = 1 AND C.ID = {$key}";
                    $project_data = M()->query($sql);

                    if ($project_data) {
                        $city_name = D("City")->get_city_info_by_id($project_data[0]['CITY_ID'], array("NAME"));

                        $month_data[$key]['projectname'] = $project_data[0]['PROJECTNAME'];
                        $month_data[$key]['contract'] = $project_data[0]['CONTRACT'];
                        $month_data[$key]['city_name'] = $city_name['NAME'];
                        $month_data[$key]['etime'] = oracle_date_format($project_data[0]['FROMDATE'], "Y-m-d") . "至" . oracle_date_format($project_data[0]['TODATE'], "Y-m-d");
                    }
                }

                /***画表格开始**/
                $html = '<table cellpadding="0" cellspacing="0" width="100%" class="jbtab tdbgwt" id="table_style">';
                $html .= '<tr><th>编号</th><th>项目名称</th><th>合同编号</th><th>城市</th><th>执行周期</th><th>日期</th><th>总办卡数</th><th>总办卡收益</th><th>总退卡数</th><th>总退卡额</th><th>总成交套数</th><th>总成交额</th></tr>';

                $i = 1;
                if (is_array($month_data)) {
                    foreach ($month_data as $k => $r) {
                        $count = count($r['month']);
                        $flag = 0;
                        krsort($r['month']);

                        foreach ($r['month'] as $key => $val) {
                            $html .= '<tr>';

                            $html .= "<td>$i</td>";
                            if ($flag == 0) {
                                $html .= "<td rowspan={$count}>" . $r['projectname'] . "</td>";
                                $html .= "<td rowspan={$count}>" . $r['contract'] . "</td>";
                                $html .= "<td rowspan={$count}>" . $r['city_name'] . "</td>";
                                $html .= "<td rowspan={$count}>" . $r['etime'] . "</td>";
                            }
                            $html .= "<td>{$key}</td>";
                            $html .= "<td>" . intval($val['card_count']) . "</td>";
                            $html .= "<td>" . intval($val['card_profit']) . "</td>";
                            $html .= "<td>" . intval($val['back_count']) . "</td>";
                            $html .= "<td>" . intval($val['back_money']) . "</td>";
                            $html .= "<td>" . intval($val['deal_count']) . "</td>";
                            $html .= "<td>" . intval($val['deal_money']) . "</td>";
                            $html .= "</tr>";
                            $flag++;
                            $i++;
                        }
                    }
                }
                $html .= '</table>';
                /***画表格结束**/
            }

            //按年显示 -- 暂时不处理
            if ($search_cardshow == 2) {

            }

            //导出
            if ($_REQUEST['export'])
                exportHtml("项目办卡统计", $html);

            //分页代码
            $page = $page;
            $limit = $pagesize;
            $offset = ($page - 1) * $limit;
            $page_nav = page($count_num, $limit, $page, $pageurl);

            //渲染
            $this->assign('act', $act);
            $this->assign('search_cardshow', $search_cardshow);
            $this->assign('search_cardorder', $search_cardorder);
            $this->assign('search_cardcon', $search_cardcon);
            $this->assign('search_prjname', $search_prjname);
            $this->assign('search_btime', $search_btime);
            $this->assign('search_etime', $search_etime);

            $this->assign('cardCondition', $cardCondition);
            $this->assign('cardShow', $cardShow);
            $this->assign('cardOrder', $cardOrder);

            $this->assign('page_nav', $page_nav);
            $this->assign('pageurl', $pageurl);

            $this->assign('month_data', $month_data);
            $this->assign('html', $html);

            $this->display("card_statistics_prjcard");
            exit();
        }

        $this->display("card_statistics");
    }
}