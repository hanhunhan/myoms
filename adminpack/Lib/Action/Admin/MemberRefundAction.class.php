<?php

/**
 * 会员退款操作类
 *
 * @author liuhu
 */
class MemberRefundAction extends ExtendAction{
    const EXPORT_REFUNDS_SQL = <<<REFUNDS_SQL
            SELECT C.PRJ_ID,
               C.PRJ_NAME,
               C.REALNAME,
               C.MOBILENO,
               C.INVOICE_STATUS,
               C.ADD_UID,
               C.RECEIPTNO,
               C.INVOICE_NO,
               P.PAY_TYPE,
               P.CVV2,
               to_char(P.TRADE_TIME, 'yyyy-mm-dd') AS TRADE_TIME,
               P.ORIGINAL_MONEY,
               P.RETRIEVAL,
               P.MERCHANT_NUMBER,
               D.ID,
               D.MID,
               D.REFUND_MONEY,
               D.APPLY_UID,
               D.CREATETIME,
               D.LIST_ID,
               D.REFUND_STATUS,
               D.CITY_ID,
               D.CONFIRMTIME
            FROM ERP_MEMBER_REFUND_DETAIL D
            INNER JOIN ERP_CARDMEMBER C ON D.MID = C.ID
            INNER JOIN ERP_MEMBER_PAYMENT P ON D.PAY_ID = P.ID
            WHERE D.LIST_ID = %d
              AND D.REFUND_STATUS != 5
            ORDER BY ID DESC
REFUNDS_SQL;

    const PROJECT_REFUND_STAT_SQL = <<<PROJECT_REFUND_STAT_SQL
        SELECT C.PRJ_ID,
               P.PROJECTNAME,
               COUNT(1) TOATL_COUNT,
               SUM(D.REFUND_MONEY) TOTAL_AMOUNT
        FROM ERP_MEMBER_REFUND_DETAIL D
        LEFT JOIN ERP_CARDMEMBER C ON C.ID = D.MID
        LEFT JOIN ERP_PROJECT P ON P.ID = C.PRJ_ID
        WHERE D.ID IN
            (SELECT DISTINCT(D1.ID)
             FROM ERP_MEMBER_REFUND_DETAIL D1
             WHERE D1.LIST_ID = %d
             AND D1.REFUND_STATUS != 5)
        GROUP BY C.PRJ_ID,
                 P.PROJECTNAME
PROJECT_REFUND_STAT_SQL;

    /**
     * 加入审核单权限
     */
    const ADD_TO_AUDIT_LIST = 396;

    /**
     * 提交审核单权限
     */
    const VIEW_AUDIT_LIST = 370;

    /*合并当前模块的URL参数*/
    private $_merge_url_param = array();
    
    /**子页签编号**/
    private $_tab_number = 16;
    
    /**业务类型字符串描述**/
    private $_case_type = 'ds';

    private $refundListRecordOptions = array(
        '_check' => array(
            'default' => 395
        )
    );

    private $exportColumnMap = array(
        'A' => array(
            'alias' => 'MERCHANT_NUMBER',
            'name' => '商户编号',
            'setValueType' => 'string',
            'decode' => true
        ),
        'B' => array(
            'alias' => 'TRANSACTION_TYPE',
            'name' => '交易类型',
            'defaultValue' => '消费',
            'decode' => true
        ),
        'C' => array(
            'alias' => 'CVV2',
            'name' => '卡号后4位',
            'setValueType' => 'string',
            'decode' => true
        ),
        'D' => array(
            'alias' => 'TRADE_TIME',
            'name' => '原始交易日期',
            'decode' => true,
            'setValueType' => 'date',
            'showAsString' => true
        ),
        'E' => array(
            'alias' => 'ORIGINAL_MONEY',
            'name' => '原始交易金额',
            'decode' => true
        ),
        'F' => array(
            'alias' => 'RETRIEVAL',
            'name' => '原检索参考号后6位',
            'setValueType' => 'string',
            'decode' => true
        ),
        'G' => array(
            'alias' => 'REFUND_MONEY',
            'name' => '退款金额',
            'decode' => true
        ),
        'H' => array(
            'alias' => 'REFUND_PAY_TYPE',
            'name' => '退货资金支付途径',
            'defaultValue' => 1
        ),
        'I' => array(
            'alias' => 'REALNAME',
            'name' => '联系人',
            'decode' => true
        ),
        'J' => array(
            'alias' => 'MOBILENO',
            'name' => '联系电话',
            'setValueType' => 'string',
            'decode' => true
        ),
        'K' => array(
            'alias' => 'REASON',
            'name' => '申请原因',
            'defaultValue' => '无合适房源，申请退款',
            'decode' => true
        ),
        'L' => array(
            'alias' => '',
            'name' => '机构编号（商户无需填写）',
            'setValueType' => 'string',
            'decode' => true
        ),
        'M' => array(
            'alias' => '',
            'name' => '业务类型',
            'defaultValue' => 1
        ),
        'N' => array(
            'alias' => '',
            'name' => '线上线下标志(账单支付业务填写)'
        ),
        'O' => array(
            'alias' => '',
            'name' => '备注字段1(可选)'
        ),
        'P' => array(
            'alias' => '',
            'name' => '备注字段2(可选)'
        ),
        'Q' => array(
            'alias' => '',
            'name' => '备注字段3(可选)'
        ),
        'R' => array(
            'alias' => '',
            'name' => '备注字段4(可选)'
        ),
        'S' => array(
            'alias' => '',
            'name' => '备注字段5(可选)'
        ),
        'T' => array(
            'alias' => 'PRJ_NAME',
            'name' => '项目名称',
            'decode' => true,
            'setValueType' => 'explicit'
        ),
        'U' => array(
            'alias' => 'RECEIPTNO',
            'name' => '收据编号',
            'setValueType' => 'string',
            'decode' => true
        ),
        'V' => array(
            'alias' => 'INVOICE_STATUS',
            'name' => '发票状态',
            'map' => array(
                '1' => '未开',
                '2' => '已开未领',
                '3' => '已领',
                '4' => '已收回',
                '5' => '申请中'
            ),
            'decode' => true
        )
    );
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();

        $this->authorityMap = array(
            'add_to_audit_list' => self::ADD_TO_AUDIT_LIST,
            'view_audit_list' => self::VIEW_AUDIT_LIST
        );

        load("@.member_common");

        //城市ID
        $this->city_id = intval($_SESSION['uinfo']['city']);
        //用户ID
        $this->uid = intval($_SESSION['uinfo']['uid']);
        //用户姓名
        $this->uname = trim($_SESSION['uinfo']['uname']);
        //城市简称
        $this->user_city_py = trim($_SESSION['uinfo']['city_py']);
        
        //TAB URL参数
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = strip_tags($_GET['FLOWTYPE']) : '';
        !empty($_GET['refund_list_id']) ? $this->_merge_url_param['refund_list_id'] = intval($_GET['refund_list_id']) : '';
        $this->_merge_url_param['TAB_NUMBER'] = $this->_tab_number;
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = intval($_GET['CASEID']) : '';
        !empty($_GET['CASE_TYPE']) ? $this->_merge_url_param['CASE_TYPE'] = strip_tags($_GET['CASE_TYPE']) : '';
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] =  intval($_GET['RECORDID']) : '';
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] =  intval($_GET['flowId']) : '';
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = strip_tags($_GET['operate']) : '';
    }
    
    
    /**
    +----------------------------------------------------------
    * 申请退款[根据会员编号]
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function apply_refund()
    {   
    	//当前用户编号
        $uid = intval($_SESSION['uinfo']['uid']);
        
        //当前城市编号
        $city_id = intval($this->channelid);
        
        //申请退款方式
        $refund_method = !empty($_GET['refund_method']) ? $_GET['refund_method'] : '';
        
        //付款明细状态
        $member_refund = D('MemberRefund');
        $conf_refund_status  = $member_refund->get_conf_refund_status();
        
        if( empty($conf_refund_status) || !isset($conf_refund_status['refund_no_sub']))
        {
        	$info['state']  = 0;
        	$info['msg']  = '退款申请添加失败，退款明细状态参数异常';
        } 
        else if (!empty($_GET) && $refund_method != '')
        { 
            //已付款信息
            $pay_info_arr = array();

            //会员付款MODEL类
            $member_pay = D('MemberPay');

            //需要查询的字段
            $search_field = array(
                                'ID', 'MID', 'TRADE_MONEY', 
                                'REFUND_STATUS', 'REFUND_MONEY', 'STATUS'
                                );

            if($refund_method == 'mid')
            {
                //通过会员批量退款申请
                $memberId_arr = array();
                $memberId_arr = $_GET['memberId'];
                $pay_info_arr = $member_pay->get_payinfo_by_mid($memberId_arr, $search_field);

                //会员锁定无法申请退款
                foreach($memberId_arr as $memberId){
                    $isMemberLock = M("Erp_cardmember")->where("ID=".$memberId)->getField('LOCK_UNLOCK');
                    if($isMemberLock == 0){
                        $info['state']  = 0;
                        $info['msg']  = g2u("无法申请退款，编号为".$memberId."的会员被锁定!");
                        echo json_encode($info);
                        exit;
                    }
                }
            }
            else if($refund_method == 'pay_details')
            {
                //通过付款明细退款申请
                $pay_id_arr = array();
                $pay_id_arr = $_GET['pay_id'];
                $pay_info_arr = $member_pay->get_payinfo_by_id($pay_id_arr, $search_field);

                //会员锁定无法申请退款
                foreach($pay_id_arr as $pay_id){
                    $memberId = M("Erp_member_payment")->where("ID=".$pay_id)->getField('MID');
                    $isMemberLock = M("Erp_cardmember")->where("ID=".$memberId)->getField('LOCK_UNLOCK');
                    if($isMemberLock == 0){
                        $info['state']  = 0;
                        $info['msg']  = g2u("无法申请退款，付款明细编号为".$pay_id."的会员被锁定!");
                        echo json_encode($info);
                        exit;
                    }
                }
            }

            //付款明细退款状态数组
            $pay_refund_status = array();
            $pay_refund_status = $member_pay->get_conf_refund_status();
            $pay_status = $member_pay->get_conf_status();
            
            //已在退款过程中数组
            $refund_process_arr = array();
            //财务未确认支付数组
            $not_confirmed_arr = array();
            //退款成功数组
            $refund_payid_arr = array();
            //退款失败数组
            $refund_fail_payid_arr = array();
            if(is_array($pay_info_arr) && !empty($pay_info_arr))
            {
                /***发票状态为“申请中、已开未领、已领”状态的会员，
                    点击申请退款，需提示无法申请退款，必须要先退票后退款**/
                $mid_arr = array();
                foreach($pay_info_arr as $key => $value)
                {  
                    $mid_arr[] = $value['MID']; 
                }
                
                $mid_arr = array_unique($mid_arr);
                
                $member_model = D('Member');
                $refund_member_info = array();
                $refund_member_info = $member_model->get_info_by_ids($mid_arr, array('INVOICE_STATUS'));
                
                //发票状态数组
                $conf_invoice_status = $member_model->get_conf_invoice_status();
                
                if(is_array($refund_member_info) && !empty($refund_member_info))
                {
                    foreach($refund_member_info as $key => $value)
                    { 
                        if(in_array($value['INVOICE_STATUS'] , 
                                array($conf_invoice_status['apply_invoice'], 
                                      $conf_invoice_status['invoiced'],
                                      $conf_invoice_status['has_taken']
                                      ) ))
                        {
                            $info['state']  = 0;
                            $info['msg']  = g2u('无法申请退款，所选的会员必须要先退票后退款!');

                            echo json_encode($info);
                            exit;
                        }
                    } 
                }
                else
                {
                    $info['state']  = 0;
                    $info['msg']  = g2u('退款申请失败，会员信息异常!');
                    
                    echo json_encode($info);
                    exit;
                }
                
                //循环判断是否需要将查到的付款明细信息加入退款明细表
                foreach($pay_info_arr as $key => $value)
                {   
                    if($value['STATUS'] == $pay_status['wait_confirm'])
                    {
                        $not_confirmed_arr[] = $value['ID'];
                        continue;
                    }
                    
                    //不在退款流程中的付款详情，并且已退金额小于缴纳金额
                    if($value['REFUND_STATUS'] != $pay_refund_status['no_refund'])
                    {   
                        $refund_process_arr[] = $value['ID'];
                        continue;
                    }
                    
                    $need_refund_money = 0;
                    
                    //缴纳金额
                    $trade_money = isset($value['TRADE_MONEY']) ? 
                                    intval($value['TRADE_MONEY']) : 0;
                    //已退金额
                    $refund_money = isset($value['REFUND_MONEY']) ? 
                                    intval($value['REFUND_MONEY']) : 0;
                    //还需用退的金额
                    $need_refund_money = $trade_money - $refund_money;

                    //退款信息数组
                    $refund_arr = array();
                    //需要退的金额大于0才会加入退款明细中
                    if($need_refund_money > 0 && $value['STATUS'] == $pay_status['confirmed'])
                    {	
                        $refund_arr['MID'] = $value['MID'];
                        $refund_arr['PAY_ID'] = $value['ID'];
                        $refund_arr['REFUND_MONEY'] = $need_refund_money;
                        $refund_arr['REFUND_STATUS'] = $conf_refund_status['refund_no_sub'];
                        $refund_arr['LIST_ID'] = '';
                        $refund_arr['APPLY_UID'] = $uid;
                        $refund_arr['CREATETIME'] = date('Y-m-d H:i:s');
                        $refund_arr['CITY_ID'] = $city_id;

                        //添加退款明细
                        $last_detail_id = $member_refund->add_refund_details($refund_arr);

                        //加入退款明细的付款明细编号数组
                        $last_detail_id > 0 ? 
                            $refund_payid_arr[] = $value['ID'] : $refund_fail_payid_arr[] = $value['ID'];
                    }
                    
                    unset($refund_arr);
                }
                
                //是否有数据已经插入到退款明细表
                if(is_array($refund_payid_arr) && !empty($refund_payid_arr))
                {
                    
                    $up_result = FALSE;
                	$update_arr['REFUND_STATUS'] = isset($pay_refund_status['apply_refund']) ? 
                									intval($pay_refund_status['apply_refund']) : '';
                	//更新付款明细为申请退款状态
                	if($update_arr['REFUND_STATUS'] !== '')
                	{
                		$up_result = $member_pay->update_info_by_id($refund_payid_arr, $update_arr);
                	}
                    
                    if($up_result)
                    {
                        $info['state']  = 1;
                        $info['msg']  = '退款申请添加成功';
                    }
                }
                else
                {
                    $info['state']  = 0;
                    $info['msg']  = '退款申请添加失败';
                }
                
                $num_process = count($refund_process_arr);
                if($num_process > 0)
                {
                    $info['msg']  .= '，'.$num_process.'条付款明细已在退款申请中';
                }
                
                $not_confirmed = count($not_confirmed_arr);
                if($not_confirmed > 0)
                {
                    $info['msg']  .= '，'.$not_confirmed.'条付款明细财务未确认';
                }
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '没有查到符合退款条件的的付款信息';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '参数错误';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
        exit;
    }
    
    
    /**
    +----------------------------------------------------------
    * 申请退款[根据付款明细]
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function apply_refund_by_payid()
    {   
        $uid = intval($_SESSION['uinfo']['uid']);
        $city_id = intval($this->channelid);
        
        if ($uid > 0 && $city_id > 0 && $this->isPost() && !empty($_POST))
        {   
            //查询当前用户最新一个退款申请单信息
            $refund_info = array();
            $refund_info['ADD_UID'] = $uid;
            $refund_info['CREATETIME'] = date('Y-m-d');
            $refund_info['STATUS'] = 0;
            $refund_info['CITY_ID'] = $city_id;
        }
        
        print_r($refund_info);
        exit;
    }
    
    
    /**
     +----------------------------------------------------------
     * 申请退款管理列表
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function refund_list()
    {	
        //当前操作
        $faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        
    	//当前用户编号
    	$uid = intval($_SESSION['uinfo']['uid']);
    	
    	//当前城市编号
    	$city_id = intval($this->channelid);
    	
    	//退款MODEL
    	$member_refund = D('MemberRefund');
    	
    	Vendor('Oms.Form');
    	$form = new Form();
        
        $conf_refund_status = $member_refund->get_conf_refund_status();
        $refund_no_sub = isset($conf_refund_status['refund_no_sub']) ? 
                $conf_refund_status['refund_no_sub'] : '';
        
        $cond_where = "CITY_ID =  '".$city_id."' AND REFUND_STATUS = '".$refund_no_sub."' ";
        
        //是否有查看全部的权限
        if(!$this->p_auth_all)
        {
            $cond_where .= "  AND PRJ_ID IN "
                . " (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' AND ISVALID = '-1' AND (ERP_ID = 1 OR ERP_ID = 2 )) ";
        }
        
        $form = $form->initForminfo(158)->where($cond_where);
        
        //经办人
        $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        
        //设置付款方式
        $member_pay = D('MemberPay');
        $pay_arr = $member_pay->get_conf_pay_type();
        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), FALSE);
        
        //设置退款状态
        $refund_status = $member_refund->get_conf_refund_status_remark();
        $form = $form->setMyField('REFUND_STATUS', 'LISTCHAR', array2listchar($refund_status), FALSE);
        
        //退款申请人
        $form = $form->setMyField('APPLY_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        
        if($this->_merge_url_param['flowId'] > 0)
        {
            //工作流入口编辑权限
            $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);
            
            if($flow_edit_auth)
            {
                $form->CZBTN = '<a class="cancel_from_details" href="javascript:;">撤销</a>';
            }
            else
            {
                $form->CZBTN = '--';
                $form->GABTN = '';
            }
        }
        else
        {
            //未提交的退款申请可以删除，否则无法删除
            $form->CZBTN = array(
                        '%REFUND_STATUS% == 0'=>'<a class="cancel_from_details" href="javascript:;">撤销</a>',
                        '%REFUND_STATUS% > 0' => '<font style="color:#333">——</font>');
        }

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
    	$this->assign('form',$formHtml);
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
    	$this->display('refund_list');
    }
    
    
    /**
     +----------------------------------------------------------
     * 退款记录管理列表
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function refund_list_record()
    {	
        //当前操作
        $faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        
    	//当前用户编号
    	$uid = intval($_SESSION['uinfo']['uid']);
    	
    	//当前城市编号
    	$city_id = intval($this->channelid);
    	
    	//退款MODEL
    	$member_refund = D('MemberRefund');
    	
    	Vendor('Oms.Form');
    	$form = new Form();
        
        $conf_refund_status = $member_refund->get_conf_refund_status();
        $refund_no_sub = isset($conf_refund_status['refund_no_sub']) ? 
                $conf_refund_status['refund_no_sub'] : '';
        
        //查看全部会员
        $cond_where = "CITY_ID =  '".$city_id."' ";
        
        //是否有查看全部的权限
        if(!$this->p_auth_all)
        {
            $cond_where .= " AND PRJ_ID IN "
            . "(SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' AND ISVALID = '-1' AND ERP_ID = 1)";
        }
        
        $form = $form->initForminfo(158)->where($cond_where);
        
        //经办人
        $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        
        //设置付款方式
        $member_pay = D('MemberPay');
        $pay_arr = $member_pay->get_conf_pay_type();
        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), FALSE);
        
        //设置退款状态
        $refund_status = $member_refund->get_conf_refund_status_remark();
        $form = $form->setMyField('REFUND_STATUS', 'LISTCHAR', array2listchar($refund_status), FALSE);
        
        //退款申请人
        $form = $form->setMyField('APPLY_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        
        //退款确认时间
        $form = $form->setMyField('CONFIRMTIME', 'GRIDVISIBLE', '-1', TRUE);
        $form = $form->setMyField('CONFIRMTIME', 'FILTER', '-1', TRUE);
        
        $form->GABTN = '';
        $form->DELCONDITION = '1 == 0';
        $form->EDITCONDITION = '1 == 0';

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->refundListRecordOptions);  // 权限前置
        $formHtml =  $form->getResult();
    	$this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
    	$this->display('refund_list_record');
    }
    
    
    /**
     +----------------------------------------------------------
     * 撤销退款申请（退款明细）
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function revoke_refund()
    {
        $this->delete_from_details();
    }
    
    
    /**
     +----------------------------------------------------------
     * 从退款明细表中删除退款申请
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function delete_from_details()
    {
        //删除的退款明细编号
        $refund_details_id = intval($_POST['reund_details_id']);
        
        if($refund_details_id > 0)
        {   
            $member_refund_model = D('MemberRefund');
            $update_num = $member_refund_model->del_refund_detail_by_id($refund_details_id);
            
            /***更新支付明细状态为未申请退款***/
            $refund_details_info = array();
            $refund_details_info = 
                    $member_refund_model->get_refund_detail_by_id($refund_details_id, array('PAY_ID'));
            //退款信息的支付明细ID
            $pay_id = !empty($refund_details_info['PAY_ID']) ? intval($refund_details_info['PAY_ID']) : 0;
            
            $member_pay_model = D('MemberPay');
            //付款明细表退款状态
            $pay_refund_status = $member_pay_model->get_conf_refund_status();
            $update_arr['REFUND_STATUS'] = $pay_refund_status['no_refund'];
            
            $update_num_pay = $member_pay_model->update_info_by_id($pay_id, $update_arr);
            
            if($update_num > 0 && $update_num_pay > 0)
            {
                $info['state']  = 1;
                $info['msg']  = '撤销退款成功';
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '撤销退款失败';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '参数错误';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
        exit;
    }
    
    
    /**
     +----------------------------------------------------------
     * 添加到退款审核单
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function add_to_audit_list()
    {
        //当前用户编号
        $uid = intval($_SESSION['uinfo']['uid']);
        
        //当前城市编号
        $city_id = intval($this->channelid);
        
        //申请加入审核单的退款详情编号
        $refundId = $_POST['refundId'];
        
        if ($uid > 0 && $city_id > 0 && !empty($refundId) && !empty($_POST) )
        { 
            //查询当前用户最新一个退款申请单信息
            $refund_info = array();
            $member_refund = D('MemberRefund');
            $refund_info = $member_refund->get_last_refund_list($uid, $city_id);
            
            if(is_array($refund_info) && !empty($refund_info) 
                    && $refund_info['STATUS'] == 0)
            {
                $last_list_id = $refund_info['ID'];
            }
            else
            {
                $refund_info = array();
                $refund_info['ADD_UID'] = $uid;
                $refund_info['CREATETIME'] = date('Y-m-d');
                $refund_info['STATUS'] = 0;
                $refund_info['CITY_ID'] = $city_id;
                
                $last_list_id = $member_refund->add_refund_list($refund_info);
            }
            
            $update_num = 0;
            if($last_list_id > 0)
            {  
               $update_num = $member_refund->add_details_to_audit_list($refundId, $last_list_id);
               
               if($update_num > 0)
               {
                    $info['state']  = 1;
                    $info['msg']  = '加入审核单成功';
               }
               else
               {
                    $info['state']  = 0;
                    $info['msg']  = '加入审核单失败';
               }
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '生成审核单操作失败';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '参数错误';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
        exit;
    }
    
    
    /**
     +----------------------------------------------------------
     * 退款审核单
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function refund_audit_list()
    {
    	//当前用户编号
    	$uid = intval($_SESSION['uinfo']['uid']);

    	//当前城市编号
    	$city_id = intval($this->channelid);
        
        //退款申请单编号
        $refund_list_id = !empty($_GET['refund_list_id']) ? intval($_GET['refund_list_id']) : 0;
    	
    	Vendor('Oms.Form');
    	$form = new Form();
        
        //退款MODEL
        $member_refund = D('MemberRefund');
        $refund_list_status = $member_refund->get_conf_refund_list_status();
        
    	$last_list_id = 0;
        if( $refund_list_id > 0 )
        {
            $last_list_id = $refund_list_id;
        }   
        else if ($uid > 0 && $city_id > 0)
        { 
            //查询当前用户最新一个退款申请单信息
            $refund_info = array();
            $refund_info = $member_refund->get_last_refund_list($uid, $city_id, 
            		$refund_list_status['refund_list_no_sub']);
			
            if(is_array($refund_info) && !empty($refund_info))
            {
            	$last_list_id = $refund_info['ID'];
            }            
        }
        
        $refund_status = $member_refund->get_conf_refund_status();
        $refund_delete_status = !empty($refund_status['refund_delete']) ?  
                $refund_status['refund_delete']: '';

        $cond_where = "LIST_ID =  '".$last_list_id."' AND CITY_ID = '".$city_id."'";
        if($refund_list_id)
            $cond_where = "LIST_ID =  '".$last_list_id."'";
        $cond_where .= " AND REFUND_STATUS != '".$refund_delete_status."'";
        
    	$form = $form->initForminfo(158)->where($cond_where);
        
        //当前退款申请单信息
        $reund_info = $member_refund->get_refund_list_by_id($last_list_id, array('ID', 'STATUS'));
        if(!empty($refund_list_status) && 
                $refund_list_status['refund_list_no_sub'] == $reund_info['STATUS'])
        {
            //修改删除按钮
            $form->CZBTN = '<a class = "delete_from_audit_list contrtable-link btn btn-danger btn-xs"'
                    . ' href="javascript:void(0);" title="删除"><i class="glyphicon glyphicon-trash"></i></a>';
            
            //修改底部按钮
            $form->GABTN = '<a id = "sub_audit_list" href="javascript:;" class="btn btn-info btn-sm">提交审核单</a>';
        }
        else if(!empty($refund_list_status) && 
                $refund_list_status['refund_list_sub'] == $reund_info['STATUS'])
        {
            //撤销按钮
            $form->CZBTN = '<a class = "revoke_refund contrtable-link btn btn-info btn-sm"'
                    . ' href="javascript:void(0);">撤销退款</a>';
            $form->GABTN = '';
        }
        else if(!empty($refund_list_status) && 
                ($refund_list_status['refund_list_stop'] == $reund_info['STATUS'] || 
                $refund_list_status['refund_list_completed'] == $reund_info['STATUS']))
        {
            $form->GABTN = '';
            $form->CZBTN = '--';
        }
        else
        {
            //无操作
            $form->CZBTN = '--';
        }
        //设置复选框不显示
        $form->SHOWCHECKBOX = 0;
        
        //经办人
        $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                
        //设置付款方式
        $member_pay = D('MemberPay');
        $pay_arr = $member_pay->get_conf_pay_type();
        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), FALSE);
        
        //设置退款状态不显示
        $form->setMyField('REFUND_STATUS', 'GRIDVISIBLE', '0', TRUE);
        
        //退款申请人
        $form = $form->setMyField('APPLY_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        $form->GABTN = $form->GABTN . '<a id = "export_refunds" href="javascript:;" class="btn btn-info btn-sm">导出退款审核单</a>';

        // 获取退款审核单中相应项目的退款统计状况
        $projectRefundStat = $this->getProjectRefundStat($last_list_id);

        $form =  $form->getResult();
    	$this->assign('form', $form);
        $this->assign('projectRefundStat', $projectRefundStat);
        $this->assign('refund_list_id', $last_list_id);
        $this->assign('flow_url_current', U('MemberRefund/opinionFlow',$this->_merge_url_param));
    	$this->display('refund_audit_list');
    }
    
    
    /**
     +----------------------------------------------------------
     * 进度列表
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function refund_progress_list()
    {
    	//当前用户编号
    	$uid = intval($_SESSION['uinfo']['uid']);
    	//当前城市编号
    	$city_id = intval($this->channelid);
    	
    	Vendor('Oms.Form');
    	$form = new Form();
        
        $refund_list_id = !empty($this->_merge_url_param['RECORDID']) ? 
                        $this->_merge_url_param['RECORDID'] : 0;
        
        if($refund_list_id > 0)
        {
            $cond_where = "ID = '".$refund_list_id."'";
        }
        else
        {
            $cond_where = "CITY_ID = '".$city_id."' AND ADD_UID = '".$uid."'";
        }
        
    	$form = $form->initForminfo(166)->where($cond_where);
        
        //修改删除按钮
        $form->CZBTN = '<a class = "view_audit_list btn btn-success btn-xs btn-xs-padding"'
            . ' href="javascript:void(0);">查看退款审核单</a>';

        //设置退款状态不显示
        $refund_model = D('MemberRefund');
        $refund_list_status = $refund_model->get_conf_refund_list_status_remark();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($refund_list_status), FALSE);
        
        //退款申请人
        $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
        $formHtml =  $form->getResult();
    	$this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
    	$this->display('refund_progress_list');
    }
    
    
    /**
     +----------------------------------------------------------
     * 退款明细从退款单中删除
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function delete_from_audit_list()
    {
        //删除的退款单编号
        $refund_details_id = intval($_POST['reund_details_id']);
        
        if($refund_details_id > 0)
        {   
            $member_refund = D('MemberRefund');
            $update_num = $member_refund->delete_details_from_audit_list($refund_details_id);
            
            if($update_num > 0)
            {
                $info['state']  = 1;
                $info['msg']  = '删除成功';
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '删除失败';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '参数错误';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
        exit;
    }
    
    
    /**
     +----------------------------------------------------------
     * 审批意见
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    function opinionFlow()
    {   
        //流程类型
		$type = !empty($_REQUEST['FLOWTYPE']) ? $_REQUEST['FLOWTYPE'] : 'tksq';

        //工作流ID
        $flowId = !empty($_REQUEST['flowId']) ? 
                intval($_REQUEST['flowId']) : 0;
        
        //工作流关联业务ID
        $recordId = !empty($_REQUEST['RECORDID']) ? 
                intval($_REQUEST['RECORDID']) : 0;

        //工作流
        Vendor('Oms.workflow');
        $workflow = new workflow();

        //如果工作流ID > 0
        if($flowId > 0)
        {
            $click  = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if($_REQUEST['savedata'])
            {
                if($_REQUEST['flowNext'])
                {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('办理成功',U('Flow/workStep'));
                    }
                    else
                    {
                       js_alert('办理失败');
                    }
                }
                else if($_REQUEST['flowPass'])
                {
                    $str = $workflow->passWorkflow($_REQUEST);
                    
                    if($str)
                    {   
                        js_alert('同意成功', U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('同意失败');
                    }
                }
                else if($_REQUEST['flowNot'])
                {
                    $str = $workflow->notWorkflow($_REQUEST);
                    
                    if($str)
                    {   
                        //退款MODEL
                        $refund_model = D('MemberRefund');
                        
                        //退款申请单终止
                        $list_update_num = $refund_model->sub_refund_list_to_stop($recordId);

                        //退款明细终止
                        $update_num = $refund_model->sub_refund_detail_to_stop($recordId);
                        
                        //根据退款单获取退款明细信息
                        $refund_details = array();
                        $refund_details = $refund_model->get_refund_detail_by_listid($recordId, 
                                array('PAY_ID', 'REFUND_STATUS'));
                        
                         //更新付款明细未申请退款
                        if(is_array($refund_details) && !empty($refund_details))
                        {
                            $member_pay_model = D('MemberPay');
                            
                            //退款明细状态
                            $refund_status = $refund_model->get_conf_refund_status();
                            $pay_refund_status = $member_pay_model->get_conf_refund_status();
                            
                            foreach($refund_details as $key => $value)
                            {   
                                if(!empty($refund_status) && $value['REFUND_STATUS'] == $refund_status['refund_stop'])
                                {
                                    $pay_id = $value['PAY_ID'];
                                    $update_arr['REFUND_STATUS'] = $pay_refund_status['no_refund'];
                                    $update_num_pay = $member_pay_model->update_info_by_id($pay_id, $update_arr);
                                }
                            }
                        }
                        
                        js_alert('否决成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('否决失败');
                    }
                }
                else if($_REQUEST['flowStop'])
                {
                    $auth = $workflow->flowPassRole($flowId);

                    if(!$auth)
                    {
                        js_alert('未经过必经角色');
                        exit;
                    }
                    
                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str)
                    {   
                        //退款MODEL
                        $refund_model = D('MemberRefund');
                        
                        //退款申请单完成
                        $list_update_num = $refund_model->sub_refund_list_to_completed($recordId);

                        //退款明细退款成功
                        $update_num = $refund_model->sub_refund_detail_to_success($recordId);
                        
                        //根据退款单获取退款明细信息
                        $refund_details = array();
                        $refund_details = $refund_model->get_refund_detail_by_listid($recordId, 
                                array('ID', 'MID', 'PAY_ID', 'REFUND_MONEY', 'REFUND_STATUS', 'APPLY_UID', 'UPDATETIME'));

                         //更新付款明细未申请退款
                        if(is_array($refund_details) && !empty($refund_details))
                        {

                            $member_model = D('Member');
                            $member_pay_model = D('MemberPay');
                            $income_model = D('ProjectIncome');
                            $project_cost_model = D("ProjectCost");
                            
                            //退款明细状态
                            $refund_status = $refund_model->get_conf_refund_status();
                            $pay_refund_status = $member_pay_model->get_conf_refund_status();
                            $invoice_status = $member_model->get_conf_invoice_status();
                            $not_open_arr = array($invoice_status['no_invoice'] , $invoice_status['apply_invoice']);
                            
                            foreach($refund_details as $key => $value)
                            {   
                                if(!empty($refund_status) && $value['REFUND_STATUS'] == $refund_status['refund_success'])
                                {  
                                    $mid = intval($value['MID']);
                                    
                                    /***更新付款明细退款状态和退款金额***/
                                    $pay_id = $value['PAY_ID'];
                                    $update_arr = array();
                                    $update_arr['REFUND_STATUS'] = $pay_refund_status['no_refund'];
                                    $update_arr['REFUND_MONEY'] = array('exp', "REFUND_MONEY + " .$value['REFUND_MONEY']);
                                    $update_arr['REFUND_TIME'] = date('Y-m-d H:i:s');
                                    $update_num_pay = $member_pay_model->update_info_by_id($pay_id, $update_arr);
                                    
                                    //根据会员ID获取会员信息
                                    $search_field = array('PRJ_NAME','REALNAME','MOBILENO','CARDTIME','LEAD_TIME','SIGNTIME','PRJ_ID','CASE_ID', 'PAID_MONEY', 'UNPAID_MONEY', 'REDUCE_MONEY', 'INVOICE_STATUS','INVOICE_NO','CITY_ID');
                                    $member_info = $member_model->get_info_by_id($mid, $search_field);

                                    //项目ID
                                    $prj_id = $member_info['PRJ_ID'];
                                    $invoice_no = $member_info['INVOICE_NO'];
                                    $prj_city = $member_info['CITY_ID'];
                                    $prj_city_info = D("City")->get_city_info_by_id($prj_city);
                                    $prj_city_py = $prj_city_info['PY'];

                                    //全部退款，退卡状态变成已退卡
                                    if($member_info['PAID_MONEY'] - $value['REFUND_MONEY'] == 0)
                                    {
                                        $member_update_arr['CARDSTATUS'] = 4;
                                        $member_update_arr['BACK_UID'] = $value['APPLY_UID'];
                                        $member_update_arr['BACKTIME'] = date('Y-m-d H:i:s');
                                        $member_update_arr['PAY_TYPE'] = 0;
                                    }

                                    ///退款减免已付金额
                                    $member_update_arr['PAID_MONEY'] = array('exp', "PAID_MONEY - " .$value['REFUND_MONEY']);
                                    $member_update_arr['UNPAID_MONEY'] = array('exp', "UNPAID_MONEY + " .$value['REFUND_MONEY']);
                                    $update_num_member = $member_model->update_info_by_id($mid, $member_update_arr);
                                    
                                    /***插入项目收益表***/
                                    $case_id = !empty($member_info['CASE_ID']) ? intval($member_info['CASE_ID']) : 0;
                                    $income_info = array();
                                    $income_info['CASE_ID'] = $case_id;
                                    $income_info['ENTITY_ID'] = $mid;

                                    //原始收入实体编号
                                    $income_info['ORG_ENTITY_ID'] = $mid;
                                    $income_info['PAY_ID'] = $value['PAY_ID'];

                                    //原始收益明细编号
                                    $income_info['ORG_PAY_ID'] = $value['PAY_ID'];
                                    if(in_array($member_info['INVOICE_STATUS'], $not_open_arr))
                                    {
                                        $income_info['INCOME_FROM'] = 4;//电商非开票会员退款
                                    }
                                    else       
                                    {
                                        $income_info['INCOME_FROM'] = 20;//电商开票会员退款
                                    }
                                    
                                    $income_info['INCOME'] = - $value['REFUND_MONEY'];
                                    $income_info['INCOME_REMARK'] = '电商会员退款';
                                    $income_info['ADD_UID'] = $value['APPLY_UID'];
                                    $income_info['OCCUR_TIME'] = $value['UPDATETIME'];
                                    $result = $income_model->add_income_info($income_info);

                                    //POS机手续费 --- 成本退回 (POS机)
                                    $pay_info = $member_pay_model->get_payinfo_by_id($pay_id,array('PAY_TYPE','MERCHANT_NUMBER','TRADE_MONEY'));

                                    if($pay_info[0]['PAY_TYPE']==1 && !in_array($member_info['INVOICE_STATUS'], $not_open_arr)){
                                        //案例编号 【必填】
                                        $cost_info['CASE_ID'] = $case_id;
                                        //业务实体编号 【必填】
                                        $cost_info['ENTITY_ID'] =  $value['MID'];
                                        $cost_info['EXPEND_ID'] = $value['PAY_ID'];
                                        $cost_info['ORG_ENTITY_ID'] = $value['MID'];
                                        $cost_info['ORG_EXPEND_ID'] = $value['PAY_ID'];

                                        // 成本金额 【必填】
                                        $fee = get_pos_fee($prj_city,$value['REFUND_MONEY'],$pay_info[0]['MERCHANT_NUMBER']);
                                        $cost_info['FEE'] = -$fee;
                                        //操作用户编号 【必填】
                                        $cost_info['ADD_UID'] = $this->uid;
                                        //发生时间 【必填】
                                        $cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());
                                        //是否资金池（0否，1是） 【必填】
                                        $cost_info['ISFUNDPOOL'] = 0;
                                        //成本类型ID 【必填】
                                        $cost_info['ISKF'] = 1;
                                        //进项税 【选填】
                                        $cost_info['INPUT_TAX'] = 0;
                                        //成本类型ID 【必填】
                                        //$cost_info['FEE_ID'] = $v["FEE_ID"];
                                        $cost_info['EXPEND_FROM'] = 28;
                                        $cost_info['FEE_REMARK'] = "会员开票POS机手续费";
                                        $cost_info['FEE_ID'] = 95;

                                        $cost_insert_id = $project_cost_model->add_cost_info($cost_info);
                                    }

                                    //如果是全额退款
                                    if($member_info['PAID_MONEY'] - $value['REFUND_MONEY'] == 0){
                                        $status_arr = $member_model->get_conf_all_status_remark();

                                        /***退卡通知CRM***/
                                        $crm_api_arr = array();
                                        $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                                        $crm_api_arr['mobile'] = strip_tags($member_info['MOBILENO']);
                                        $crm_api_arr['activefrom'] = 104;
                                        $crm_api_arr['city'] = $prj_city_py;
                                        $crm_api_arr['activename'] =  urlencode($member_info['PRJ_NAME'].
                                            '退卡'.oracle_date_format($member_info['CARDTIME'], 'Y-m-d'));
                                        $crm_api_arr['importfrom'] = urlencode('团立方监控后台');
                                        $crm_api_arr['tlfcard_status'] = 3;
                                        $crm_api_arr['tlfcard_creattime'] = strtotime(oracle_date_format($member_info['CARDTIME'], 'Y-m-d'));
                                        $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                                        $crm_api_arr['tlfcard_signtime'] = strtotime(oracle_date_format($member_info['SIGNTIME'], 'Y-m-d'));
                                        $crm_api_arr['tlfcard_backtime'] = time();
                                        $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                                        $crm_api_arr['projectid'] = $member_info['PRJ_ID'];

                                        if($member_info['CARDSTATUS'] == 3)
                                        {
                                            $house_info = M('erp_house')->field('PRO_LISTID')->
                                            where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();

                                            $pro_listid = !empty($house_info['PRO_LISTID']) ?
                                                intval($house_info['PRO_LISTID']) : '';

                                            $crm_api_arr['floor_id'] = $pro_listid;
                                        }

                                        //提交
                                        $crm_url = submit_crm_data_by_api_url($crm_api_arr);
                                        $ret_log = api_log($prj_city,$crm_url,0,$this->uid,2);
                                    }

                                    //如果存在发票号
                                    if($invoice_no) {
                                        //获取合同编号
                                        $contract_num = M("erp_project")
                                            ->field("CONTRACT")
                                            ->where('ID = ' . $prj_id)
                                            ->find();
                                        $contract_num = $contract_num['CONTRACT'];

                                        //进入合同系统(退款取消同步到合同系统)
                                        //$tongji_url = CONTRACT_API . 'sync_ct_invoice.php?city=' . $prj_city_py . '###contractnum=' . $contract_num . '###money=-' . $value['REFUND_MONEY'] . '###tax=0###invono=' . $invoice_no . '###type=2###date=' . date('Y-m-d') . '###note=' . urlencode('经管系统自动同步-退款');
                                        //api_log($prj_city, $tongji_url, 0, $this->uid, 1);
                                    }
                                }
                            }
                        }
                        js_alert('备案成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('备案失败');
                    }
                }
               exit;
            }
        }
        else
        {
            //判断权限
            $auth = $workflow->start_authority($type);

            if(!$auth)
               $this->error('暂无权限');

            $form = $workflow->createHtml($flowId);

            if($_REQUEST['savedata'])
            {   
                $refund_list_id = !empty($_GET['refund_list_id']) ? intval($_GET['refund_list_id']) : 0;

                $flow_data['type'] = $type; 
                $flow_data['CASEID'] = '';
                $flow_data['RECORDID'] = $refund_list_id;
                $flow_data['INFO'] = strip_tags($_POST['INFO']);
                $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                $flow_data['FILES'] = $_POST['FILES'];
                $flow_data['ISMALL'] =  intval($_POST['ISMALL']);
                $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']);
                
                //创建退款工作流
                $str = $workflow->createworkflow($flow_data);

                if($str)
                {
                    //退款MODEL
                    $refund_model = D('MemberRefund');
                    
                    //提交退款申请单
                    $list_update_num = $refund_model->sub_refund_list_to_apply($refund_list_id);
                    
                    //提交退款申请明细
                    $update_num = $refund_model->sub_refund_detail_to_apply($refund_list_id);

                    //工作流结果
                    if($list_update_num > 0 && $update_num > 0)
                        js_alert('退款申请提交成功', U('MemberRefund/refund_progress_list', $this->_merge_url_param));
                    else
                        $this->error('退款申请提交失败', U('MemberRefund/refund_progress_list', $this->_merge_url_param));
                }
                else
                {
                    js_alert('对不起，退款申请提交失败！', U('MemberRefund/refund_progress_list', $this->_merge_url_param));
                }
                exit;
            }
        }

        $this->assign('form', $form);
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));

        $this->display('opinionFlow');
    }

    public function exportRefunds() {
        $refundListID = intval($_REQUEST['refund_list_id']);
        if (empty($refundListID)) {
            die('导出会员失败：参数错误');
        }

        $refundList = D()->query(sprintf(self::EXPORT_REFUNDS_SQL, $refundListID));
        Vendor('phpExcel.PHPExcel');
        $Exceltitle = '退款审核单';//
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(16);//默认行宽
        $objActSheet->getDefaultColumnDimension()->setWidth(-1);//默认列宽
        $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'宋体'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//自动换行
        $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objActSheet->getRowDimension('1')->setRowHeight(40);
        $objActSheet->getRowDimension('2')->setRowHeight(26);

//        $objActSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $styleArray = array(
            'borders' => array (
                'allborders' => array (
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array ('argb' => 'FF000000'),//设置border颜色
                ),
            ),
        );

        $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'退款审核单'));
        $columnTitleRow = 1;
        $defaultColumnWidth = 20;
        $startColumn = 'A';
        $endColumn = 'V';
        $objActSheet->getStyle($startColumn . $columnTitleRow . ':' . $endColumn . $columnTitleRow)->getFont()->setBold(true);
        foreach($this->exportColumnMap as $key => $column) {
            $coordinate = $key . $columnTitleRow;
            $objActSheet->setCellValue($coordinate, iconv("gbk//ignore","utf-8//ignore",$column['name']));
            if (array_key_exists('width', $column) && intval($column['width']) > 0) {
                $objActSheet->getColumnDimension($key)->setWidth($column['width']);

            } else {
                $objActSheet->getColumnDimension($key)->setWidth($defaultColumnWidth);
            }
        }

        if (is_array($refundList) && count($refundList)) {
            $dataRow = $columnTitleRow + 1;
            foreach($refundList as $key => $row) {
                foreach($this->exportColumnMap as $key => $column) {
                    $value = '';
                    if (array_key_exists($column['alias'], $row)) {
                        $value = $row[$column['alias']];
                    } else if (array_key_exists('defaultValue', $column) && $column['defaultValue']) {
                        $value = $column['defaultValue'];
                    }
                    if (!empty($value)) {
                        // 是否需要数据转换
                        if (array_key_exists('map', $column) && !empty($column['map'][$value])) {
                            $value = $column['map'][$value];
                        }

                        // 是否需要转码
                        if (array_key_exists('decode', $column) && $column['decode']) {
                            $value = iconv("gbk//ignore","utf-8//ignore",$value);
                        }

                        // 设置单元格的方式
                        if (array_key_exists('setValueType', $column) && $column['setValueType']) {
                            if ($column['setValueType'] == 'explicit') {
                                $objActSheet->setCellValueExplicit($key . $dataRow, $value);
                            } else if ($column['setValueType'] == 'date') {
                                $t_year = substr($value, 0, 4);
                                $t_month = substr($value, 5, 2);
                                $t_day = substr($value, 8, 2);
                                if ($column['showAsString']) {
                                    $sDate = $t_year . $t_month . $t_day;
                                    $objActSheet->setCellValueExplicit($key . $dataRow, $sDate, PHPExcel_Cell_DataType::TYPE_STRING);
                                } else {
                                    $t_date = PHPExcel_Shared_Date::FormattedPHPToExcel($t_year, $t_month, $t_day);
                                    $objActSheet->setCellValue($key . $dataRow, $t_date);
                                    $objActSheet->getStyle($key . $dataRow)
                                        ->getNumberFormat()
                                        ->setFormatCode()
                                        ->setFormatCode('yyyymmdd');
                                }
                            } else if ($column['setValueType'] == 'string') {
                                $objActSheet->setCellValueExplicit($key . $dataRow, $value, PHPExcel_Cell_DataType::TYPE_STRING);
                            }
                        } else {
                            $objActSheet->setCellValue($key . $dataRow, $value);
                        }
                    }
                }

                $objActSheet->getRowDimension($dataRow)->setRowHeight(24);
                if($objActSheet->getRowDimension($dataRow)->getRowHeight() > 0) {
                    $objActSheet->getRowDimension($dataRow)->setRowHeight($objActSheet->getRowDimension($dataRow)->getRowHeight()+20);
                }

                $dataRow++;
            }
        }

        ob_end_clean();
        ob_start();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=".$Exceltitle.date("YmdHis").".xls");
        header("Content-Transfer-Encoding:binary");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 获取退款相应项目的统计数据
     * @param $refundListId
     * @return array
     */
    private function getProjectRefundStat($refundListId) {
        $response = array();
        if (intval($refundListId) > 0) {
            $response = D()->query(sprintf(self::PROJECT_REFUND_STAT_SQL, $refundListId));
            if (is_array($response) && count($response)) {
                $summary = array(
                    'PRJ_ID' => '',
                    'PROJECTNAME' => '合计',
                    'TOATL_COUNT' => 0,
                    'TOTAL_AMOUNT' => 0.0
                );

                foreach ($response as $v) {
                    $summary['TOATL_COUNT'] += intval($v['TOATL_COUNT']);
                    $summary['TOTAL_AMOUNT'] += intval($v['TOTAL_AMOUNT']);
                }
                array_push($response, $summary);
            }

        }



        return $response;
    }
}

/* End of file MemberRefundAction.class.php */
/* Location: ./Lib/Action/MemberRefundAction.class.php */