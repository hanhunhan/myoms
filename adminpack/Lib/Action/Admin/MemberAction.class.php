
<?php
class MemberAction extends ExtendAction{
    /**
     * 申请退款权限
     */
    const REFUND_BY_MID = 183;

    /**
     * 申请开票权限
     */
    const APPLY_INVOICE = 611;

    /**
     * 申请减免权限
     */
    const APPLY_DISCOUNT = 290;

    /**
     * 申请退票权限
     */
    const RECYCLE_INVOICE = 302;

    /**
     * 申请换发票权限
     */
    const CHANGE_INVOICE = 385;

    /**
     * 中介佣金报销权限
     */
    const AGENCY_REWARD_REIM = 613;

    /**
     * 中介成交奖励报销权限
     */
    const AGENCY_DEAL_REWARD_REIM = 715;

    /**
     * 置业成交奖励报销权限
     */
    const PROPERTY_DEAL_REWARD_REIM = 716;

    /**
     * 会员导出权限
     */
    const DOWNLOAD_MEMBER = 608;

    /**
     * 会员导入权限
     */
    const IMPORT_MEMBER = 609;

    /**
     * 查看会员权限
     */
    const VIEW_MEMBERINFO = 377;

    /**
     * 批量改状态权限
     */
    const BATCH_CHANGE_STATUS = 614;


    /**
     * 现金发放类报销
     */
    const CASH_PAYMENT_REIM = 7;

    /**
     * 申请退款权限
     */
    const REFUND_BY_DETAILS = 610;

    /**
     * 申请报销权限
     */
    const LOCALE_GRANTED_REIM = 640;

    /**
     * 提交报销申请权限
     */
    const SUB_REIM_APPLY = 641;

    /**
     * 关联借款权限
     */
    const RELATED_MY_LOAN = 642;

    /**
     * 查询垫资比例超额申请FlowID的SQL
     */
    const PAYOUT_FLOWID_SQL = <<<PAYOUT_FLOWID_SQL
        SELECT ID
        FROM ERP_FLOWS T
        WHERE T.FLOWSETID = 35
          AND T.RECORDID = %d
PAYOUT_FLOWID_SQL;

    private $showPayListOptions = array(
        '_add' => array(
            'default' => 628,
        ),
        '_check' => array(
            'default' => 629
        ),
        '_edit' => array(
            'default' => 713
        ),
        '_del' => array(
            'default' => 714
        )
    );

    private $showRefundListOptions = array(
        '_check' => array(
            'default' => 632
        ),
        '_edit' => array(
            'default' => 631
        ),
        '_del' => array(
            'default' => 633
        )
    );

    private $showBillListOptions = array(
        '_check' => array(
            'default' => 635
        )
    );

    private $localeGrantedOptions = array(
        '_add' => array(
            'default' => 636,
        ),
        '_check' => array(
            'default' => 638
        ),
        '_edit' => array(
            'default' => 637
        ),
        '_del' => array(
            'default' => 639
        )
    );

    private $uid;
    private $city_id;
    
    /*合并当前模块的URL参数*/
    private $_merge_url_param = array();
    
    /**子页签编号**/
    private $_tab_number = 22;
    
    //构造函数
    public function __construct() 
    {	
        parent::__construct();
        // 权限映射表
        $this->authorityMap = array(
            'refund_by_mid' => self::REFUND_BY_MID,
            'apply_invoice' => self::APPLY_INVOICE,
            'apply_discount' => self::APPLY_DISCOUNT,
            'recycle_invoice' => self::RECYCLE_INVOICE,
            'change_invoice' => self::CHANGE_INVOICE,
            'agency_reward_reim' => self::AGENCY_REWARD_REIM,
            'agency_deal_reward_reim' => self::AGENCY_DEAL_REWARD_REIM,
            'property_deal_reward_reim' => self::PROPERTY_DEAL_REWARD_REIM,
            'download_member' => self::DOWNLOAD_MEMBER,
            'import_member' => self::IMPORT_MEMBER,
            'view_memberinfo' => self::VIEW_MEMBERINFO,
            'batch_change_status' => self::BATCH_CHANGE_STATUS,
            'refund_by_details' => self::REFUND_BY_DETAILS,
            'locale_granted_reim' => self::LOCALE_GRANTED_REIM,
            'sub_reim_apply' => self::SUB_REIM_APPLY,
            'related_my_loan' => self::RELATED_MY_LOAN,
        );
        //加载会员模块公用函数文件
        load("@.member_common");

        //城市ID
        $this->city_id = intval($_SESSION['uinfo']['city']);
        //用户ID
        $this->uid = intval($_SESSION['uinfo']['uid']);
        //用户姓名
        $this->uname = trim($_SESSION['uinfo']['uname']);
        //用户姓名
        $this->tname = trim($_SESSION['uinfo']['tname']);
        //城市简称
        $this->user_city_py = trim($_SESSION['uinfo']['city_py']);
        
        $this->_merge_url_param['TAB_NUMBER'] = intval($this->_tab_number) ;
    }

    public function main() {
        $this->_merge_url_param['TAB_NUMBER'] = 22;
        if (!empty($this->_merge_url_param['TAB_NUMBER'])) {
            $hasTabAuthority = $this->checkTabAuthority($this->_merge_url_param['TAB_NUMBER']);
            if ($hasTabAuthority['result']) {
                $roleInfo = D('erp_role')->where("LOAN_ROLEID = {$hasTabAuthority['roleID']}")->find();
                $url = U("{$roleInfo['LOAN_ROLECONTROL']}/{$roleInfo['LOAN_ROLEACTION']}", $this->_merge_url_param);
                halt2('', $url);
                return;
            }
        }
    }
    
    
    public function index()
    {
       
		
		$city = $_SESSION["uinfo"]["city"];

        $search = $this->_post('search');
        $model = D('member');
        $where = "m_city  = '$city'";
        
        if($search)
        {
          $where .= " and ( m_uid = '".$search."' or m_email= '".$search."' "
                  . "or m_moblie = '".$search."' )";  
        }
        
        $count = $model->where($where)->count();

        import("ORG.Util.Page");
        $p = new Page($count,C('PAGESIZE'));
        $para = "&bu_del=".$bu_del;
        if($para) $p->parameter = $para;
        $page = $p->show();	

        $user_info = $model->where($where)->limit($p->firstRow.','.$p->listRows)->select();

        $this->assign('search',$search);
        $this->assign('page',$page);
        $this->assign('data',$user_info);
        $this->display();
    }
    
    
    /**
    +----------------------------------------------------------
    * 电商自然到场客户录入
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function newMember()
    {
        //实例化会员MODEL
        $member_model = D('Member');
        $city_channel = $this->channelid;
        $uid = $_SESSION["uinfo"]["uid"];

        //数据提交
        if ($this->isPost() && !empty($_POST))
        {

            //返回数据结构
            $return = array(
                'status'=>false,
                'msg'=>'',
                'data'=>null,
            );

            //项目ID
            $project_id = intval($_POST['project_id']);
            //客户手机号
            $telno = trim(strip_tags($_POST['telno']));
            //客户姓名
            $cusname = u2g(trim(strip_tags($_POST['cusname'])));

            /**数据验证**/
            $returnstr = '';
            if (!$project_id)
                $returnstr .= "请选择项目名称\n";

            if(!preg_match('/^1[0-9]{10}$/',$telno))
                $returnstr .= "手机号填写有误\n";

            if(strlen($cusname) < 3)
                $returnstr .= "客户名称填写有误\n";

            //获取项目信息
            $project_info = $member_model->get_project_arr_by_pid($project_id);

            if (empty($project_info))
                $returnstr .= "对不起您选择的项目不存在\n";

            if(!empty($returnstr))
            {
                $return['msg'] = g2u($returnstr);
                die(@json_encode($return));
            }

            //CRM入库
            $activename = urlencode($project_info[0]['PROJECTNAME'].'自然来客');
            $project_listid = $project_info[0]['PRO_LISTID'];

            $cpi_arr = array(
                'city'=>$this->user_city_py,
                'mobile'=>$telno,
                'username'=>$cusname,
                'activefrom'=>231,
                'activename'=>$activename,
                'loupanids'=>$project_listid,
            );

            $crm_url = submit_crm_data_by_api_url($cpi_arr);
            //crm入库
            $ret_log = api_log($this->city_id,$crm_url,0,$this->uid,2);

            //CRM反馈值
            if($ret_log){
                $return['status'] = true;
                $return['msg'] = g2u("新用户已经成功录入");

                /**
                 *使用统计日志
                 ***/
                $operate_type = 4;
                $operate_remark = '自然来客录入';
                $operate_user = $this->uid;
                $from_device = get_user_agent_device('num');
                submit_user_operate_log(0, $operate_type, $operate_remark,$operate_user, $from_device, $this->city_id, $project_id);
            }
            else
            {
                $return['msg'] = g2u("请检查您录入的内容是否正确");
            }

            //TODO 使用统计日志
            die(json_encode($return));
        }

        //项目权限
        $projects = $member_model->get_projectinfo_by_uid($uid,$city_channel);
        $this->assign('is_login_from_oa',$this->is_login_from_oa);
        $this->assign('projects',$projects);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('new_member');
    }
	
	
     /**
    +----------------------------------------------------------
    * 会员到场确认
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function arrivalConfirm()
	{
        $uid = $_SESSION["uinfo"]["uid"];
        $city_channel = $this->channelid;
        //实例化会员MODEL
        $member_model = D('Member');

        //md5——key
        $form_sub_auth_key = md5("HOUSE365_JINGGUAN_".date('Ymd').'_'.$this->uname);

        //操作类型
        $action_type = isset($_POST['action_type']) ? trim($_POST['action_type']) : '';

        switch ($action_type)
        {
            //签到确认
            case 'arrive_confirm':

                $return = array(
                    'status'=>false,
                    'msg'=>'',
                    'data'=>null,
                );

                //确认验证码
                $authcode_key = strip_tags($_POST['authcode_key']);

                //通用楼盘编号
                $project_listid = intval($_POST['project_listid']);

                //项目编号
                $project_id = intval($_POST['project_id']);

                //验证码
                $code = strip_tags($_POST['code']);

                //客户真实姓名
                $truename = strip_tags($_POST['truename']);

                //客户手机号码
                $telno = strip_tags($_POST['telno']);

                //数据来源
                $is_from = strip_tags($_POST['is_from']);

                //客户ID
                $customer_id = intval($_POST['customer_id']);

                //根据客户验证码获取的项目ID
                $user_project_id = intval($_POST['user_project_id']);

                //project名称
                $project_name = strip_tags($_POST['project_name']);

                if( $authcode_key == $form_sub_auth_key && $customer_id > 0)
                {
                    if( $project_id > 0 && $code > 0)
                    {
                        //判断验证码获得的项目与当前选中的项目是否一致
                        if( ($is_from == 1 && $user_project_id == $project_id ) ||
                            ($is_from == 2 && $user_project_id == $project_listid ) )
                        {
                            if($is_from == 1)
                            {
                                //根据项目编号和手机号码查询FGJ系统中是否存在已经确认的用户，
                                //如果有则提醒无法再次到场确认
                                $fgj_user_info = array();
                                $fgj_user_info = get_fgj_userinfo_by_pid_telno($project_listid, $telno);
                                if(is_array($fgj_user_info) && !empty($fgj_user_info) &&
                                    $fgj_user_info['result'] == 1 && !empty($fgj_user_info['data']))
                                {
                                    $is_confirmed = 0;
                                    foreach ($fgj_user_info['data'] as $key => $value)
                                    {
                                        //0表示未过保护期，1表示已经过了保护期
                                        if($value['overProtection'] == 0)
                                        {
                                            //到场确认状态 1未认证，0代表已经认证
                                            if( $value['status'] == 0 && $value['overProtection'] == 0 )
                                            {
                                                $is_confirmed = 1;
                                            }
                                        }
                                    }

                                    if($is_confirmed == 1)
                                    {
                                        $return['msg'] = g2u('该用户已经在房管家系统中到场确认，无法再次到场确认');
                                        die(@json_encode($return));
                                    }
                                }
                                $result = arrival_confirm_crm($customer_id, $code);
                            }
                            else if($is_from == 2)
                            {
                                //经纪人ID
                                $ag_id = intval($_POST['ag_id']);
                                //报备ID
                                $cp_id = intval($_POST['cp_id']);

                                //FGJ签到确认更新CRM用户状态
                                $userinfo_crm_arr = get_crm_userinfo_by_pid_telno($project_id, $telno, $this->user_city_py);
                                if(is_array($userinfo_crm_arr) && $userinfo_crm_arr['result'] == 1 &&
                                    !empty($userinfo_crm_arr['meminfo']))
                                {
                                    if( $userinfo_crm_arr['meminfo']['codestatus'] == 1 )
                                    {
                                        $return['msg'] = g2u('该用户已经在CRM系统中到场确认，无法再次到场确认');
                                        die(@json_encode($return));
                                    }
                                    else
                                    {
                                        //CRM中用户ID
                                        $customer_id_crm = 0;
                                        $customer_id_crm = $userinfo_crm_arr['meminfo']['pmid'];
                                        $up_result = update_crm_user_source($customer_id_crm , 5);
                                    }
                                }
                                $result = arrival_confirm_fgj($customer_id, $ag_id, $cp_id);
                                $is_sucess = intval($result['result']);
                                $msg = $is_sucess == 1 ? '到场确认成功' : '验证失败，验证码无效或已过期';

                                //到场确认日志记录
                                arrival_confirm_log($customer_id , $truename , $telno , $code , $project_listid , $project_id , $is_from , $is_sucess);

                                //使用统计日志
                                $operate_type = 3;
                                $operate_remark = '到场确认';
                                $operate_user = $this->uid;
                                $from_device = get_user_agent_device('num');
                                submit_user_operate_log($customer_id, $operate_type, $operate_remark, $operate_user, $from_device, $this->city_id, $project_id);

                            }

                            //正确返回结果
                            $return['status'] = true;
                            //$return['msg'] = "确认结束";
                            $msg = "确认结束";

                        }
                        else
                        {
                            //作为自然来客添加到CRM
                            $reg_result = register_natural_customer($this->user_city_py , $truename , $telno , $project_listid , $project_name);
                            $msg = "验证码获取项目信息与当前项目信息不一致";
                            $msg .= $reg_result == 1 ? ',用户信息已作为自然来客录入！' : '!';
                        }
                    }
                    else
                    {
                        $msg = "项目名称和验证码必须填写";
                    }
                }
                else
                {
                    $msg = "参数错误或客户错误 -> 验证失败";
                }
                $return['msg'] = g2u($msg);
                die(@json_encode($return));
                break;

            //根据验证码获取用户信息
            case 'ajax_userinfo_by_code':
                $code = intval($_POST['code']);
                $project_listid = intval($_POST['project_listid']);
                $userinfo = get_userinfo_by_code($code, $project_listid);
                //var_dump($project_listid);
                die(@json_encode($userinfo));
                break;

            //当用户没有验证码时，根据用户手机号码或经纪人手机号码获取用户信息
            case 'ajax_userinfo_by_telno':
                $customer_telno = trim($_POST['customer_telno']);
                $agent_telno = trim($_POST['agent_telno']);

                //如果是有经纪人电话取原项目ID
                if(strlen($agent_telno) == 0)
                {
                    $project_id = intval($_POST['project_id']);
                }
                //如果是客户电话取新房项目ID
                else
                {
                    $project_id = intval($_POST['project_listid']);
                }
                $userinfo = get_userinfo_by_telno($project_id, $customer_telno, $agent_telno);
                die(@json_encode($userinfo));
                break;
        }
        //项目权限数据
        $projects = $member_model-> get_arrivalprojectinfo_by_uid($uid,$city_channel);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->assign('form_sub_auth_key',$form_sub_auth_key);
        $this->assign('projects',$projects);
        $this->display('arrival_confirm'); 
	}

    /**
    +----------------------------------------------------------
    * 注册电商办卡会员
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function RegMember()
    {   
        $id = !empty($_POST['ID']) ? intval($_POST['ID']) : 0;
    	$faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $uid = intval($_SESSION['uinfo']['uid']);
        $username = $_SESSION['uinfo']['tname'];
        $showForm = intval($_GET['showForm']);
        //操作行为
        $act = !empty($_POST['act']) ? intval($_POST['act']) : '';
    	
    	//实例化会员MODEL
    	$member_model = D('Member');
        
        /***获取会员办卡、开票、发票状态***/
        $status_arr = $member_model->get_conf_all_status_remark();
        
        //装修标准
        $conf_zx_standard = $member_model->get_conf_zx_standard();

        //如果是保存配置
        if($act=='savecfg'){

            $return = array(
                'status'=>false,
                'msg'=>'',
                'data'=>null,
            );

            $formdata_str = $_POST['formdata'];
            parse_str($formdata_str,$formdata);

            $member_info = array();
            //如果返回数据
            if(!empty($formdata)){
                $member_info['CITY_ID'] = $formdata['CITY_ID'];
                $member_info['PRJ_ID'] = $formdata['PRJ_ID'];
                $member_info['PRJ_NAME'] =  u2g($formdata['PRJ_NAME']);
                //获取caseid
                $case_model = D('ProjectCase');
                $case_info = $case_model->get_info_by_pid($member_info['PRJ_ID'], 'ds');
                $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
                $member_info['CASE_ID'] = $case_id;
                $member_info['SOURCE'] = $formdata['SOURCE'];

                $member_info['CERTIFICATE_TYPE'] = $formdata['CERTIFICATE_TYPE'];

                $member_info['CARDSTATUS'] = $formdata['CARDSTATUS'];

                $member_info['SIGNEDSUITE'] = intval($formdata['SIGNEDSUITE']);

                $member_info['RECEIPTSTATUS'] = $formdata['RECEIPTSTATUS'];

                $member_info['IS_TAKE'] = $formdata['IS_TAKE'];

                $member_info['IS_SMS'] = $formdata['IS_SMS'];

                $member_info['TOTAL_PRICE'] = intval($formdata['TOTAL_PRICE']);

                $member_info['PAID_MONEY'] = 0;
                $member_info['UNPAID_MONEY'] = floatval($formdata['TOTAL_PRICE']);

                $member_info['AGENCY_REWARD'] = floatval($formdata['AGENCY_REWARD']);
                $member_info['AGENCY_DEAL_REWARD'] = floatval($formdata['AGENCY_DEAL_REWARD']);
                $member_info['PROPERTY_DEAL_REWARD'] = floatval( $formdata['PROPERTY_DEAL_REWARD']);

                $member_info['DECORATION_STANDARD'] = intval( $formdata['DECORATION_STANDARD']);
                $member_info['LEAD_TIME'] = $formdata['LEAD_TIME'];

                $member_info['ADD_UID'] = $this->uid;
                $member_info['ADD_USERNAME'] = $_SESSION['uinfo']['tname'];

                $member_info_str = serialize($member_info);

                $ret = D("Member")->put_user_config('MEMBER_ADD',$member_info_str,$this->uid);
            }

            if($ret){
                $return['status'] = true;
                $return['msg'] = g2u('亲，保存当前电商会员配置成功！');
            }
            die(@json_encode($return));
        }

    	//修改会员信息
    	if($this->isPost() && !empty($_POST) && $faction == 'saveFormData' && $id > 0)
    	{	
    		$member_info = array();
    		$member_info['REALNAME'] = u2g($_POST['REALNAME']);
            
            //处理隐藏部分内容
            if($_POST['MOBILENO'] != $_POST['MOBILENO_OLD'])
            {
                $member_info['MOBILENO'] = $_POST['MOBILENO'];
                if($member_info['MOBILENO'] == "")
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,请填入购房人手机号！');
                    echo json_encode($result);
                    exit;
                }
            }

            if($_POST['LOOKER_MOBILENO'] != $_POST['LOOKER_MOBILENO_OLD'])
            {
                $member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            }
            
    		$member_info['SOURCE'] = $_POST['SOURCE'];
    		$member_info['CARDTIME'] = $_POST['CARDTIME'];
    		$member_info['CERTIFICATE_TYPE'] = $_POST['CERTIFICATE_TYPE'];
            
            //证件号码
            if (trim($_POST['CERTIFICATE_NO']) == '') 
            {
                $result['status'] = 0;
                $result['msg'] = g2u('修改失败，证件号码必须填写！');
                
                echo json_encode($result);
                exit;
            }
                
            //处理号码部分隐藏
            if($_POST['CERTIFICATE_NO'] != $_POST['CERTIFICATE_NO_OLD'])
            {
                $member_info['CERTIFICATE_NO'] = $_POST['CERTIFICATE_NO'];
                if($member_info['CERTIFICATE_TYPE'] == 1)
                {   
                    if (!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $member_info['CERTIFICATE_NO'])) 
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('修改失败，身份证号码格式不正确！');

                        echo json_encode($result);
                        exit;
                    } 
                }
            }
            
    		$member_info['ROOMNO'] = u2g($_POST['ROOMNO']);
    		$member_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
    		$member_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            $member_info['LEAD_TIME'] = strip_tags($_POST['LEAD_TIME']);
            $member_info['DECORATION_STANDARD'] = intval($_POST['DECORATION_STANDARD']);
			$member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            $member_info['DIRECTSALLER'] = u2g($_POST['DIRECTSALLER']);
            //附件
            $member_info['ATTACH'] = u2g($_POST['ATTACH']);
            switch($member_info['CARDSTATUS'])
            {
                case '2':
                    //已认购
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    $member_info['SIGNTIME'] = '';
                    $member_info['SIGNEDSUITE'] = '';
                    if($member_info['SUBSCRIBETIME'] == ''|| $member_info['SUBSCRIBETIME'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('修改失败,办卡状态为已办已认购，认购日期必须填写！');
                        
                        echo json_encode($result);
                        exit;
                    }
                    break;
                case '3':
                    //已签约
                    $member_info['SIGNTIME'] = strip_tags($_POST['SIGNTIME']);
                    $member_info['SIGNEDSUITE'] = intval($_POST['SIGNEDSUITE']);
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    if($member_info['SIGNTIME'] == '' || $member_info['SIGNEDSUITE'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('修改失败,办卡状态为已办已签约，签约日期和签约套数必须填写！');

                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['ROOMNO'] == '' && $_POST['CITY_ID'] == 1)
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('修改失败,办卡状态为已办已签约，楼栋房号必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['LEAD_TIME'] == '' || $_POST['DECORATION_STANDARD'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('修改失败,办卡状态为已办已签约，交付时间、装标准必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                    break;
                case '4':
                    //退卡
                    $member_info['BACKTIME'] = strip_tags($_POST['BACKTIME']);
                    $member_info['BACK_UID'] = intval($_POST['BACK_UID']);
                    
                    if($member_info['BACKTIME'] == '' || $member_info['BACK_UID'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('修改失败,办卡状态为退卡，退卡日期和退卡经办人必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                    break;
            }
            
            /*  已办卡未成交状态的会员，可修改为已办卡已认购或者已办卡已签约
                已办卡已认购状态的会员，可修改为已办卡已签约
                已办卡已签约的会员，无法修改
                已退卡的会员，无法修改 
            */
            $cardstatus_old = intval($_POST['CARDSTATUS_OLD']);
            if($cardstatus_old == 1 && !in_array($member_info['CARDSTATUS'], array(1,2,3)))
            {
    			$result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反办卡状态规则：已办卡未成交状态，只可以修改为已办卡已认购或者已办卡已签约');
                
                echo json_encode($result);
                exit;
            }
            else if($cardstatus_old == 2 && !in_array($member_info['CARDSTATUS'], array(2,3)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反办卡状态规则：已办卡已认购状态，只可以修改为已办卡已签约');
                
                echo json_encode($result);
                exit;
            }
            else if($cardstatus_old == 3 && $member_info['CARDSTATUS'] != 3)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反办卡状态规则：已办卡已签约状态，不可修改');
                
                echo json_encode($result);
                exit;
            }
            else if($cardstatus_old == 4 && $member_info['CARDSTATUS'] != 4)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反办卡状态规则：已退卡状态，不可修改');
                
                echo json_encode($result);
                exit;
            }
            
            $member_info['PAY_TYPE'] = $_POST['PAY_TYPE'];
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            /**
                已开未领可以修改为已领或已收回
                已领可以修改为已收回  
                已收回无法修改收据状态
             */    
            $receiptstatus_old = intval($_POST['RECEIPTSTATUS_OLD']);
            if($receiptstatus_old == 2 && !in_array($member_info['RECEIPTSTATUS'], array(2,3,4)))
            {
    			$result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反收据状态规则：已开未领，只可以修改为已领或已收回');
                
                echo json_encode($result);
                exit;
            }
            else if($receiptstatus_old == 3 && !in_array($member_info['RECEIPTSTATUS'], array(2,3,4)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反收据状态规则：已领状态，只可以修改为已开未领或已收回');
                
                echo json_encode($result);
                exit;
            }
            else if($receiptstatus_old == 4 && $member_info['RECEIPTSTATUS'] != 4)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反收据状态规则：已收回状态，不可修改');
                
                echo json_encode($result);
                exit;
            }
            $member_info['RECEIPTNO'] = trim(str_replace(array("，","/","、")," ", $_POST['RECEIPTNO']));
            $member_info['RECEIPTNO'] = preg_replace('/([^0-9])+/',' ',$member_info['RECEIPTNO']);
            $member_info['RECEIPTNO'] = preg_replace('/(\s)+/',' ',$member_info['RECEIPTNO']);
			if($member_info['RECEIPTNO'] != $_POST['RECEIPTNO_OLD']){
				$receiptno = M('Erp_cardmember')->where("RECEIPTNO='".$member_info['RECEIPTNO']."' AND CITY_ID='".$this->channelid."' AND STATUS = 1")->find(); 
				if($receiptno){
					 $result['status'] = 0;
					$result['msg'] = g2u('修改失败,该城市下已经存在相同的收据编号！');
					
					echo json_encode($result);
					exit;
				}
			}
    		$member_info['INVOICE_STATUS'] = $_POST['INVOICE_STATUS'];
            /**
                未开状态，不可修改
                申请中状态，不可以修改
                已开未领状态，可以修改为已领
                已领状态，无法修改
                已收回状态，无法修改状
		    */    
            $invoicestatus_old = intval($_POST['INVOICE_STATUS_OLD']);

            if($invoicestatus_old == 1 && $member_info['INVOICE_STATUS'] != 1)
            {
                $result['status'] = 0;
                $result['msg'] = g2u('修改失败,违反发票状态规则：未开状态，不可修改');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 2 && !in_array($member_info['INVOICE_STATUS'], array(2,3)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反发票状态规则：已开未领状态，只可以修改为已领');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 3 && !in_array($member_info['INVOICE_STATUS'], array(2,3)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反发票状态规则：已领状态，只可以修改为已开未领');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 4 && $member_info['INVOICE_STATUS'] != 4)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反发票状态规则：已收回状态，不可以修改');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 5 && ($member_info['INVOICE_STATUS'] != 5 && $member_info['INVOICE_STATUS'] != 1))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反发票状态规则：申请中状态，只能修改为未开或申请中状态');
                
                echo json_encode($result);
                exit;
            }
            $member_info['IS_TAKE'] = $_POST['IS_TAKE'];
            $member_info['IS_SMS'] = $_POST['IS_SMS'];
            $member_info['TOTAL_PRICE'] = floatval($_POST['TOTAL_PRICE']);

            $total_price_old = floatval($_POST['TOTAL_PRICE_OLD']);
            if($total_price_old != $member_info['TOTAL_PRICE']){

                if($member_info['INVOICE_STATUS'] != 1){
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,单套收费标准修改规则：修改单套收费标准，发票状态只能是未开！');

                    die(@json_encode($result));
                }

                //更新未缴纳金额
                $member_info['UNPAID_MONEY'] = $member_info['TOTAL_PRICE'] - floatval($_POST['PAID_MONEY']) - floatval($_POST['REDUCE_MONEY']);
                //更新确认状态
                $userInfo = $member_model->get_userinfo_by_uid($id);

                $member_pay = D('MemberPay');
                $confirmMoney = $member_pay->get_sum_pay($id,'confirmed');

                if($confirmMoney==0){
                    $member_info['FINANCIALCONFIRM'] = 1;
                }
                else if($confirmMoney + $_POST['REDUCE_MONEY'] < $member_info['TOTAL_PRICE']){
                    $member_info['FINANCIALCONFIRM'] = 2;
                }
                else if($confirmMoney + $_POST['REDUCE_MONEY'] >= $member_info['TOTAL_PRICE']){
                    $member_info['FINANCIALCONFIRM'] = 3;
                }
            }

            $member_info['AGENCY_REWARD'] = floatval($_POST['AGENCY_REWARD']);
            /**中介佣金报销金额修改时，查看会员是否已经申请过中介佣金报销**/
            if($member_info['AGENCY_REWARD'] != floatval($_POST['AGENCY_REWARD_OLD']))
            {
               $reim_deital_model = D('ReimbursementDetail');
               $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id , 3);
               
               if($is_reimed)
               {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,中介佣金已申请报销,无法修改!');
                    
                    echo json_encode($result);
                    exit;
               }
            }
            
            $member_info['AGENCY_DEAL_REWARD'] = floatval($_POST['AGENCY_DEAL_REWARD']);
            /**中介成交奖励报销金额修改时，查看会员是否已经申请过中介成交奖励报销**/
            if($member_info['AGENCY_DEAL_REWARD'] != floatval($_POST['AGENCY_DEAL_REWARD_OLD']))
            {
               $reim_deital_model = D('ReimbursementDetail');
               $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id , 4);
               
               if($is_reimed)
               {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,中介成交奖励已申请报销,无法修改!');
                    
                    echo json_encode($result);
                    exit;
               }
            }
            $member_info['PROPERTY_DEAL_REWARD'] = floatval($_POST['PROPERTY_DEAL_REWARD']);
            /**置业顾问成交奖励报销金额修改时，查看会员是否已经申请过置业顾问成交奖励报销**/
            if($member_info['PROPERTY_DEAL_REWARD'] != floatval($_POST['PROPERTY_DEAL_REWARD_OLD']))
            {
               $reim_deital_model = D('ReimbursementDetail');
               $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id , 6);
               
               if($is_reimed)
               {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,置业顾问成交奖励已申请报销,无法修改!');
                    
                    echo json_encode($result);
                    exit;
               }
            }
    		$member_info['NOTE'] = u2g($_POST['NOTE']);
                $member_info['AGENCY_NAME'] = u2g($_POST['AGENCY_NAME']);
    		$member_info['UPDATETIME'] = date('Y-m-d');
			$member_info['OUT_REWARD']=$_POST['OUT_REWARD'];
            //$member_info['OUT_REWARD_STATUS']=1;
            //中介来源，办卡状态是已办已签约必须
            if(($member_info['SOURCE'] == 1 || $member_info['SOURCE'] == 7 || $member_info['SOURCE'] == 8) && $member_info['CARDSTATUS'] == 3 )
            {
                if($member_info['AGENCY_REWARD'] == 0)
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,中介佣金必须填写');
                    
                    echo json_encode($result);
                    exit;
                }
            }

    		$update_num = 0;
    		$update_num = $member_model->update_info_by_id($id, $member_info);
    		
    		if($update_num > 0)
    		{   
                if($_POST['CARDSTATUS_OLD'] < $member_info['CARDSTATUS'] && $member_info['CARDSTATUS'] > 2)
                {
                    switch($member_info['CARDSTATUS'])
                    {
                        case '3':
                            $tlfcard_status = 2;
                            $tlfcard_signtime = strtotime($member_info['SIGNTIME']);
                            $tlfcard_backtime = 0;
                            break;
                        case '4':
                            $tlfcard_status = 3; 
                            $tlfcard_signtime = 0;
                            $tlfcard_backtime = strtotime($member_info['BACKTIME']);
                            break;
                    }
                    
                    $crm_api_arr = array();
                    $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                    $crm_api_arr['mobile'] = strip_tags($_POST['MOBILENO_HIDDEN']);
                    $crm_api_arr['activefrom'] = 104;
                    $crm_api_arr['city'] = $this->city;
                    $crm_api_arr['activename'] =  urlencode(u2g($_POST['PRJ_NAME']).
                    $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']].
                    $member_info['CARDTIME'].$conf_zx_standard[$_POST['DECORATION_STANDARD']]);
                    $crm_api_arr['importfrom'] = urlencode('团立方监控后台');
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = strtotime($member_info['CARDTIME']);
                    $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                    $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                    $crm_api_arr['projectid'] = $_POST['PRJ_ID'];
                    
                    if($member_info['CARDSTATUS'] == 3)
                    {
                        $house_info = M('erp_house')->field('PRO_LISTID')->
                                where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();
                        
                        $pro_listid = !empty($house_info['PRO_LISTID']) ?
                                intval($house_info['PRO_LISTID']) : '';
                        
                        $crm_api_arr['floor_id'] = $pro_listid;
                    }
                    
                    submit_crm_data_by_api($crm_api_arr);
                }

                //通知全链条精准导购系统
                if($_POST['CARDSTATUS_OLD'] != $member_info['CARDSTATUS']){

                    $qltStatus = 3;

                    switch($member_info['CARDSTATUS'])
                    {
                        case '1':
                            $qltStatus = 3;
                            break;
                        case '2':
                            $qltStatus = 4;
                            break;
                        case '3':
                            $qltStatus = 5;
                            break;
                    }

                    $queryRet = M('Erp_project')->field('CONTRACT')
                        ->where('ID='.$_POST['PRJ_ID'])->find();

                    $qltContract = $queryRet['CONTRACT'];

                    $qltUserInfo = $member_model->get_userinfo_by_uid($id);

                    $qltApiUrl = QLTAPI . '###serviceName=updateStatus###status=' . $qltStatus . '###contract=' . $qltContract . '###phone=' . $qltUserInfo['MOBILENO'];
                    api_log($this->channelid,$qltApiUrl,0,$uid,3);
                }

                $result['status'] = 1;
                $result['msg'] = '修改成功';
    		}
    		else
    		{
                    $result['status'] = 0;
                    $result['msg'] = '修改失败';
    		}
    		
            $result['msg'] = g2u($result['msg']);

            if ($result['forward'] == '' && $_REQUEST['fromUrl']) {
                $result['forward'] = $_REQUEST['fromUrl'];  // 跳转地址
            }

    		echo json_encode($result);
    		exit;
    	}
    	//新增
    	else if (!empty($_POST) && $faction == 'saveFormData')
        {   
            $member_info = array();
            $member_info['CITY_ID'] = $_POST['CITY_ID'];
            $member_info['PRJ_ID'] = $_POST['PRJ_ID'];
            $member_info['PRJ_NAME'] =  u2g($_POST['PRJ_NAME']);
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_pid($member_info['PRJ_ID'], 'ds');
            $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
            $member_info['CASE_ID'] = $case_id;
            $member_info['REALNAME'] =  u2g($_POST['REALNAME']);
            $member_info['MOBILENO'] = $_POST['MOBILENO'];
            $member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            $member_info['SOURCE'] = $_POST['SOURCE'];
            $member_info['CARDTIME'] = $_POST['CARDTIME'];
            $member_info['CERTIFICATE_TYPE'] = $_POST['CERTIFICATE_TYPE'];
            $member_info['CERTIFICATE_NO'] = $_POST['CERTIFICATE_NO'];
            if($member_info['CERTIFICATE_TYPE'] == 1)
            {
                if (!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $member_info['CERTIFICATE_NO'])) 
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败，身份证号码格式不正确！');
                    
                    echo json_encode($result);
                    exit;
                } 
            }
            else if (trim($member_info['CERTIFICATE_NO']) == '') 
            {
                $result['status'] = 0;
                $result['msg'] = g2u('添加失败，证件号码必须填写！');

                echo json_encode($result);
                exit;
            }
            
            $member_info['ROOMNO'] =  u2g($_POST['ROOMNO']);
            $member_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
            $member_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            $member_info['LEAD_TIME'] = strip_tags($_POST['LEAD_TIME']);
            $member_info['DECORATION_STANDARD'] = intval($_POST['DECORATION_STANDARD']);
            
            switch($member_info['CARDSTATUS'])
            {
                case '2':
                    //已认购
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    $member_info['SIGNTIME'] = '';
                    $member_info['SIGNEDSUITE'] = '';
                    if($member_info['SUBSCRIBETIME'] == '' || $member_info['SUBSCRIBETIME'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为已办已认购，认购日期必须填写！');
                        
                        echo json_encode($result);
                        exit;
                    }
                break;
                case '3':
                    //已签约
                    $member_info['SIGNTIME'] = strip_tags($_POST['SIGNTIME']);
                    $member_info['SIGNEDSUITE'] = intval($_POST['SIGNEDSUITE']);
                    if($member_info['SIGNTIME'] == '' || $member_info['SIGNEDSUITE'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为已办已签约，签约日期和签约套数必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['ROOMNO'] == '' && $_POST['CITY_ID'] == 1)
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为已办已签约，楼栋房号必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['LEAD_TIME'] == '' || $_POST['DECORATION_STANDARD'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为已办已签约，交付时间、装标准必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                break;
                case '4':
                    //退卡
                    $member_info['BACKTIME'] = strip_tags($_POST['BACKTIME']);
                    $member_info['BACK_UID'] = intval($_POST['BACK_UID']);
                    
                    if($member_info['BACKTIME'] == '' || $member_info['BACK_UID'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为退卡，退卡日期和退卡经办人必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                break;
            }
            
            $member_info['PAY_TYPE'] = $_POST['PAY_TYPE'];
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            $member_info['RECEIPTNO'] = trim(str_replace(array("，","/","、"),",", $_POST['RECEIPTNO']));
            $member_info['RECEIPTNO'] = preg_replace('/([^0-9])+/',' ',$member_info['RECEIPTNO']);
            $member_info['RECEIPTNO'] = preg_replace('/(\s)+/',' ',$member_info['RECEIPTNO']);
			if($member_info['RECEIPTNO'] ){
				$receiptno = M('Erp_cardmember')->where("RECEIPTNO='".$member_info['RECEIPTNO']."' AND CITY_ID='".$this->channelid."' AND STATUS = 1")->find(); 
				if($receiptno){
					 $result['status'] = 0;
					$result['msg'] = g2u('添加失败,该城市下已经存在相同的收据编号！');
					
					echo json_encode($result);
					exit;
				}
			}
            $member_info['INVOICE_STATUS'] = 1; //新增默认未开
            $member_info['IS_TAKE'] = $_POST['IS_TAKE'];
            $member_info['IS_SMS'] = $_POST['IS_SMS'];
            //附件
            $member_info['ATTACH'] = u2g($_POST['ATTACH']);
            $member_info['TOTAL_PRICE'] = intval($_POST['TOTAL_PRICE']);
            $member_info['PAID_MONEY'] = 0;
            $member_info['UNPAID_MONEY'] = floatval($_POST['TOTAL_PRICE']);
            $member_info['AGENCY_REWARD'] = floatval($_POST['AGENCY_REWARD']);
            $member_info['AGENCY_DEAL_REWARD'] = floatval($_POST['AGENCY_DEAL_REWARD']);
            $member_info['PROPERTY_DEAL_REWARD'] = floatval( $_POST['PROPERTY_DEAL_REWARD']);
            
            //中介来源
            if(($member_info['SOURCE'] == 1 || $member_info['SOURCE'] == 7 || $member_info['SOURCE'] == 8) && $member_info['CARDSTATUS'] == 3 )
            {
                if($member_info['AGENCY_REWARD'] == 0)
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败,中介佣金必须填写');

                    echo json_encode($result);
                    exit;
                }
            }
            
            $member_info['ADD_UID'] = intval($_SESSION['uinfo']['uid']);
            $member_info['ADD_USERNAME'] = $_SESSION['uinfo']['tname'];
            $member_info['NOTE'] =  u2g($_POST['NOTE']);
            $member_info['AGENCY_NAME'] =  u2g($_POST['AGENCY_NAME']);
            $member_info['DIRECTSALLER'] = u2g($_POST['DIRECTSALLER']);
            $member_info['CREATETIME'] = date('Y-m-d H:i:s');
            $member_info['STATUS'] = 1;
            
            /****是否需要到场确认****/
            $is_crm_confirm = intval($_POST['is_crm_confirm']);
            $is_fgj_confirm = intval($_POST['is_fgj_confirm']);
            if($is_crm_confirm == 1 || $is_fgj_confirm == 1)
            {
                //客户ID
                $customer_id = intval($_POST['customer_id']);
                //验证码
                $code = strip_tags($_POST['code']);
                //数据来源
                $is_from = strip_tags($_POST['is_from']);
                //到场确认
                if($is_from == 1 && $is_crm_confirm == 1)
                {
                    $result = arrival_confirm_crm($customer_id , $code);
                }
                else if($is_from == 2 && $is_fgj_confirm == 1)
                {   
                    //经纪人ID
                    $ag_id = intval($_POST['ag_id']);
                    //报备ID
                    $cp_id = intval($_POST['cp_id']);
                    //城市参数
                    $user_city_py = $_SESSION['uinfo']['city'];
                    $userinfo_crm_arr = get_crm_userinfo_by_pid_telno($member_info['PRJ_ID'], 
                    		$member_info['MOBILENO'], $user_city_py);
                    if(is_array($userinfo_crm_arr) && $userinfo_crm_arr['result'] == 1 && 
                            !empty($userinfo_crm_arr['meminfo']))
                    {   
                        if( $userinfo_crm_arr['meminfo']['codestatus'] != 1 )
                        {
                            //CRM中用户ID
                            $customer_id_crm = 0;
                            $customer_id_crm = $userinfo_crm_arr['meminfo']['pmid'];
                            $up_result = update_crm_user_source($customer_id_crm , 5);
                        }
                    }

                    $result = arrival_confirm_fgj($customer_id , $ag_id , $cp_id);
                }

                //记录日志
                $is_sucess = intval($result['result']);
                $msg = $is_sucess == 1 ? '到场确认成功' : '验证失败，验证码无效或已过期';
                //arrival_confirm_log($customer_id, $member_info['REALNAME'], $member_info['MOBILENO'],
                //		$code, $_POST['LIST_ID'], $member_info['PRJ_ID'], $is_from, $is_sucess);
            }
            /****是否需要到场确认****/
			$member_info['IS_DIS']=1;//电商
			$member_info['OUT_REWARD']=$_POST['OUT_REWARD'];
			$member_info['OUT_REWARD_STATUS']=1;
            $insert_id = $member_model->add_member_info($member_info);
            
            if($insert_id > 0)
            {
                //发送短信
                if(isset($member_info['IS_SMS']) && $member_info['IS_SMS'] == 2 
                    && $member_info['CARDSTATUS'] < 4)
                {
                    $msg = "尊敬的365会员".$member_info['REALNAME']."，"."您已办卡成功,客服热线400-8181-365。";
                    send_sms($msg, $member_info['MOBILENO'], $this->city_config_array[$this->channelid]);
                }
                
                //crm 
                if($member_info['CARDSTATUS'])
                {
                    switch($member_info['CARDSTATUS'])
                    {
                        case '1':
                        case '2':
                        $tlfcard_status = 1;
                        $tlfcard_signtime = 0;
                        $tlfcard_backtime = 0;
                        break;
                        case '3':
                            $tlfcard_status = 2;
                            $tlfcard_signtime = strtotime($member_info['SIGNTIME']);
                            $tlfcard_backtime = 0;
                        break;
                        case '4':
                            $tlfcard_status = 3;
                            $tlfcard_signtime = 0;
                            $tlfcard_backtime = strtotime($member_info['BACKTIME']);
                        break;
                    }
                    $crm_api_arr = array();
                    $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                    $crm_api_arr['mobile'] = $member_info['MOBILENO'];
                    $crm_api_arr['activefrom'] = 104;
                    $crm_api_arr['city'] = $this->city;
                    $crm_api_arr['activename'] =  urlencode(u2g($_POST['PRJ_NAME']).
                            $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']].$member_info['CARDTIME'].$conf_zx_standard[$_POST['DECORATION_STANDARD']]);
                    $crm_api_arr['importfrom'] = urlencode('团立方监控后台');
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = strtotime($member_info['CARDTIME']);
                    $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                    $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                    $crm_api_arr['projectid'] = $_POST['PRJ_ID'];
                    
                    if($member_info['CARDSTATUS'] == 3)
                    {   
                        $house_info = M('erp_house')->field('PRO_LISTID')->
                                where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();
                        
                        $pro_listid = !empty($house_info['PRO_LISTID']) ?
                                intval($house_info['PRO_LISTID']) : '';
                        
                        $crm_api_arr['floor_id'] = $pro_listid;
                    }
                    
                    submit_crm_data_by_api($crm_api_arr);
                }
                
                $result['status'] = 2;
                $result['msg'] = '添加会员成功';
            }
            else
            {	
            	$result['status'] = 0;
            	$result['msg'] = '添加会员失败！';
            }
            
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        else if ($faction == 'delData')
        {   
            //查看会员是否存在财务已确认的付款明细，如果存在不允许删除
            $mid = intval($_GET['ID']);
            $update_num = 0;
            
            if($mid > 0)
            {   
                $member_pay = D('MemberPay');
                $member_pay_info = $member_pay->get_payinfo_by_mid($mid);
                $conf_pay_status = $member_pay->get_conf_status();

                //获取会员信息
                $member_info = $member_model->get_info_by_id($mid);
                
                $confirm_payment_num = 0;
                if(is_array($member_pay_info) && !empty($member_pay_info))
                {
                    foreach($member_pay_info as $key => $value)
                    {
                        if($value['STATUS'] == $conf_pay_status['confirmed'])
                        {
                            $confirm_payment_num ++;
                        }
                    }
                    
                    if($confirm_payment_num > 0)
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('删除会员失败，存在财务确认付款明细');
                        echo json_encode($result);
                        exit;
                    }
                    
                    //删除会员付款明细信息
                    $delete_payment = $member_pay->del_pay_detail_by_mid($mid);
					
                    if($delete_payment > 0)
                    {
                        $income_from = 1;//电商会员支付
                        $income_model = D('ProjectIncome');
                        //删除收益
                        foreach($member_pay_info as $key => $value)
                        {
                                $income_model->delete_income_info($member_info['CASE_ID'], $mid, $value['ID'], $income_from);
                        }
                    }
                }
                
                //删除会员信息
                $update_num = $member_model->delete_info_by_id($mid);
                
                //删除结果
                if($update_num > 0)
                {
                    /***退卡通知CRM***/
                    if($member_info['CARDSTATUS'] != 4)
                    {   
                        $crm_api_arr = array();
                        $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                        $crm_api_arr['mobile'] = strip_tags($member_info['MOBILENO']);
                        $crm_api_arr['activefrom'] = 104;
                        $crm_api_arr['city'] = $this->city;
                        $crm_api_arr['activename'] =  urlencode($member_info['PRJ_NAME'].
                                '退卡'. oracle_date_format($member_info['CARDTIME'], 'Y-m-d').$conf_zx_standard[$member_info['DECORATION_STANDARD']]);
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

                        submit_crm_data_by_api($crm_api_arr);
                    }

                    //会员操作日志
                    $log_info = array();
                    $log_info['OP_UID'] = $uid;
                    $log_info['OP_USERNAME'] = $username;
                    $log_info['OP_LOG'] = '删除会员信息【'.$mid.'】';
                    $log_info['ADD_TIME'] = date('Y-m-d H:i:s');
                    $log_info['OP_CITY'] = $this->channelid;
                    $log_info['OP_IP'] = GetIP();
                    $log_info['TYPE'] = 2;
                    
                    member_opreate_log($log_info);

                    //全链条精准导购系统(变更状态)
                    $qltStatus = 6;
                    $queryRet = M('Erp_project')->field('CONTRACT')
                        ->where('ID='.$member_info['PRJ_ID'])->find();

                    $qltContract = $queryRet['CONTRACT'];

                    $qltApiUrl = QLTAPI . '###serviceName=updateStatus###status=' . $qltStatus . '###contract=' . $qltContract . '###phone=' . $member_info['MOBILENO'];
                    api_log($this->channelid,$qltApiUrl,0,intval($_SESSION['uinfo']['uid']),3);
                    
                    $result['status'] = 'success';
                    $result['msg'] = '删除会员成功';
                }
                else
                {	
                    $result['status'] = 'error';
                    $result['msg'] = '删除会员失败！';
                }
            }
            else 
            {
                $result['status'] = 'error';
                $result['msg'] = '参数异常！';
            }
            
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        else
        {   
            Vendor('Oms.Form');
            $form = new Form();

            $form = $form->initForminfo(103);
            $form->SQLTEXT = "(";
            $form->SQLTEXT .= "SELECT DISTINCT * FROM ERP_CARDMEMBER WHERE CITY_ID = '".$this->channelid."' AND STATUS = 1 ";
            
            //是否有查看全部的权限
            if(!$this->p_auth_all)
            {
                $form->SQLTEXT .= " AND PRJ_ID IN "
                    . " (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' AND ISVALID = '-1' AND ERP_ID = 1) ";
            }
            
            //是否自己创建
            if(!$this->p_vmem_all)
            {
                $form->SQLTEXT .= " AND ADD_UID = $uid";
            }

            //付款明细表查询条件（业务部门需求）
            if(!empty($_REQUEST))
            {   
                //截断搜索条件，改变搜索方式，使用联表字段作为子查询条件
                $pay_search_filed = array ('RETRIEVAL', 'CVV2', 'MERCHANT_NUMBER', 'TRADE_TIME', 'STATUS','REFUND_TIME','REFUND_APPLY_TIME');
                
                $cond_where = "";
                for($i = 1 ; $i <= 4 ; $i ++)
                {   
                    if(in_array($_REQUEST['search'.$i], $pay_search_filed))
                    {   
                       //拼接子查询SQL
                        if($_REQUEST['search'.$i] == "REFUND_APPLY_TIME"){
                            $_REQUEST['search'.$i] = "CREATETIME";
                        }
                       $cond_where .=  
                            $form->pjsql($_REQUEST['search'.$i], $_REQUEST['search'.$i.'_s'], $_REQUEST['search'.$i.'_t'], $_REQUEST['search'.$i.'_t_type']);
                       
                       //拼接子查询后，翻页需要用到搜索参数，组装搜索条件给分页函数使用
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'='.$_REQUEST['search'.$i];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_s='.$_REQUEST['search'.$i.'_s'];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_t='.$_REQUEST['search'.$i.'_t'];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_t_type='.$_REQUEST['search'.$i.'_t_type'];
                       
                       unset($_POST['search'.$i]);
                       unset($_POST['search'.$i.'_s']);
                       unset($_POST['search'.$i.'_t']);
                       unset($_POST['search'.$i.'_t_type']);

                       unset($_GET['search'.$i]);
                       unset($_GET['search'.$i.'_s']);
                       unset($_GET['search'.$i.'_t']);
                       unset($_GET['search'.$i.'_t_type']);

                    }
                }
                
                if($cond_where != '')
                {
                    if($_REQUEST['search1'] == "CREATETIME" or $_REQUEST['search2'] == "CREATETIME" or
                    $_REQUEST['search3'] == "CREATETIME" or $_REQUEST['search4'] == "CREATETIME"){
                        $form->SQLTEXT .= "  AND ID IN (SELECT DISTINCT MID  FROM ERP_MEMBER_REFUND_DETAIL WHERE 1=1 " . $cond_where . ")";
                    }else {
                        $form->SQLTEXT .= "  AND ID IN (SELECT DISTINCT MID FROM ERP_MEMBER_PAYMENT WHERE STATUS != 4 " . $cond_where . ")";
                    }
                }
            }

            $form->SQLTEXT .= " AND IS_DIS=1 )";
            // echo $form->SQLTEXT;
            //手机号码隐藏
            $form->setMyField('MOBILENO', 'ENCRY', '4,8', FALSE);
            $form->setMyField('LOOKER_MOBILENO', 'ENCRY', '4,8', FALSE);
			$form->setMyField('TOTAL_PRICE_AFTER', 'FORMVISIBLE', '0', FALSE);
			$form->setMyField('AGENCY_REWARD_AFTER', 'FORMVISIBLE', '0', FALSE);
            
            //设置证件类型
            $certificate_type_arr = $member_model->get_conf_certificate_type();
            $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR', 
            		array2listchar($certificate_type_arr), FALSE);

            //证件号码
            $form->setMyField('CERTIFICATE_NO', 'ENCRY', '4,13', FALSE);
            
            //设置付款方式
	        $member_pay = D('MemberPay');
	        $pay_arr = $member_pay->get_conf_pay_type();
	        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', 
	        		array2listchar($pay_arr), FALSE);
	        
	        //办卡状态
            $card_status_arr = $status_arr['CARDSTATUS'];
            if($showForm == 3)
            {
                array_pop($card_status_arr); 

            }
	        $form->setMyField('CARDSTATUS', 'LISTCHAR', 
	        		array2listchar($card_status_arr), FALSE);
            
            $current_time = date('Y-m-d');
            if($showForm == 3)
            {   
                //新增时候展示办卡时间默认
                $form->setMyFieldVal('CARDTIME', $current_time, false);
            }
	        
	        //发票状态
            if($showForm == 3)
            {   
                //新增页面发票只读、默认为未开
                $form->setMyFieldVal('INVOICE_STATUS', '1', TRUE );
                $form->setMyField('INVOICE_STATUS', 'LISTCHAR', 
	        		array2listchar($status_arr['INVOICE_STATUS']), TRUE);
                
                $form->setMyFieldVal('INVOICE_STATUS', '1', TRUE );
            }
            else
            {
                $form->setMyField('INVOICE_STATUS', 'LISTCHAR',
                        array2listchar($status_arr['INVOICE_STATUS']), FALSE);
            }
            
            //支付信息财务确认状态（支持搜索，勿删）
            $conf_pay_status = $member_pay->get_conf_status_remark();
            $form->setMyField('STATUS', 'LISTCHAR',
                        array2listchar($conf_pay_status), FALSE);
	        
	        //收据状态
            $receipt_status_arr = $status_arr['RECEIPTSTATUS'];
            if($showForm == 3)
            {
                array_pop($receipt_status_arr); 
            }
	        $form->setMyField('RECEIPTSTATUS', 'LISTCHAR', 
	        		array2listchar($receipt_status_arr), FALSE);
            
            //表单页面
            /***
             *   $showForm 1: 编辑
             *   $showForm 2: 查看
             *   $showForm 3: 新增
             */
            if($showForm == 1 || $showForm == 3 || $showForm == 2 )
            {   
                //修改记录ID
                $modify_id = !empty($_GET['ID']) ? intval($_GET['ID']) : 0;

                //设置经办人信息
                $userinfo = array();
                $form->setMyFieldVal('ADD_USERNAME', $username, TRUE);

                if($modify_id > 0)
                {
                    $search_field = array('PRJ_ID', 'CASE_ID','ADD_USERNAME');
                    $userinfo = $member_model->get_info_by_id($modify_id, $search_field);
                	
                        //设置收费标准
                        $case_id =  !empty($userinfo['CASE_ID']) ? intval($userinfo['CASE_ID']) : 0;
                        $feescale = array();
                        $project = D('Project');
                        $feescale = $project->get_feescale_by_cid($case_id);

                        $fees_arr = array();
                        if(is_array($feescale) && !empty($feescale) )
                        {
                            foreach($feescale as $key => $value)
                            {
                                if($value['AMOUNT'][0]=='.'){
                                    $value['AMOUNT'] = '0' . $value['AMOUNT'];
                                }
                                $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'];
                            }

                        //设置经办人(编辑状态：保持不变)
                        $form->setMyFieldVal('ADD_USERNAME', $userinfo['ADD_USERNAME'], TRUE);
                        //单套收费标准
                        $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
                        //中介佣金
                        $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
                        //置业顾问佣金
                       // $form->setMyField('PROPERTY_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
						  $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                        //中介成交奖
                        $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                        //置业成交奖金
                        $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);
                    }
                    
                    //会员MODEL
                    $member_model = D('Member');
                    $member_info = $member_model->get_info_by_id($modify_id, array('CITY_ID', 'PRJ_ID','CASE_ID','MOBILENO'));
                    
                    if(is_array($member_info) && !empty($member_info))
                    {
                        $input_arr = array(
                                array('name' => 'CITY_ID', 'val' => $member_info['CITY_ID'], 'id' => 'CITY_ID'),
                                array('name' => 'PRJ_ID', 'val' => $member_info['PRJ_ID'], 'id' => 'PRJ_ID'),
                                array('name' => 'CASE_ID', 'val' => $member_info['CASE_ID'], 'id' => 'CASE_ID'),
                                array('name' => 'MOBILENO_HIDDEN', 'val' => $member_info['MOBILENO'], 'id' => 'MOBILENO_HIDDEN'),
                                );
                        $form = $form->addHiddenInput($input_arr);
                    }
                }
                else
                {	
                    //新增页面参数设置
                    $input_arr = array(
                            array('name' => 'ADD_UID', 'val' => $userinfo['ID'], 'class' => 'ADD_UID'),
                            array('name' => 'is_fgj_confirm', 'val' => '', 'id' => 'is_fgj_confirm'),
                            array('name' => 'is_crm_confirm', 'val' => '', 'id' => 'is_crm_confirm'),
                            array('name' => 'code', 'val' => '', 'id' => 'code'),
                            array('name' => 'ag_id', 'val' => '', 'id' => 'ag_id'),
                            array('name' => 'cp_id', 'val' => '', 'id' => 'cp_id'),
                            array('name' => 'is_from', 'val' => '', 'id' => 'is_from'),
                            array('name' => 'customer_id', 'val' => '', 'id' => 'customer_id'),
                            array('name' => 'multi_user_to_jump', 'val' => '', 'id' => 'multi_user_to_jump'),
                            array('name' => 'multi_from_to_jump', 'val' => '', 'id' => 'multi_from_to_jump'),
                            );
                    $form->addHiddenInput($input_arr);
                }
            }
            else 
            {	
            	/***列表页参数设置***/
            	//添加人
                $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                //单套收费标准
                $form->setMyField('TOTAL_PRICE', 'EDITTYPE',"1", TRUE);
                //财务确认状态
                $form->setMyField('FINANCIALCONFIRM', 'LISTCHAR', array2listchar($status_arr['FINANCIALCONFIRM']), FALSE);
            }

            //设置会员来源
            $source_arr = $member_model->get_conf_member_source_remark();

            //编辑，根据项目ID查询项目分解目标销售方式
            if($showForm == 1)
            {
                //设置会员来源(修改时需要当前会员项目信息，注意代码位置)

                $prj_id =  !empty($userinfo['PRJ_ID']) ? intval($userinfo['PRJ_ID']) : 0;
                $source_arr = $member_model->getPrjSaleMethod($prj_id);

            }
            else if($showForm == 2)
            {
                //设置会员来源
                $source_arr = $member_model->get_conf_member_source_remark();

                //会员MODEL
                $id = !empty($_GET['ID']) ? intval($_GET['ID']) : 0;
                $member_model = D('Member');
                $member_info = $member_model->get_info_by_id($id, array('ADD_USERNAME'));

                //设置添加人
                $form->setMyFieldVal('ADD_USERNAME', $member_info['ADD_USERNAME'], TRUE);
            }

            /**
             *  如果是新增会员，有相应的保存配置，直接读取
             */
            if($showForm==3){

                //常用配置ID --- 新增会员避免重复修改
                $user_config = $member_model->get_user_config('MEMBER_ADD',$this->uid);
                $user_config = unserialize($user_config);

                if($user_config){
                    //设置收费标准
                    $case_id =  !empty($user_config['CASE_ID']) ? intval($user_config['CASE_ID']) : 0;
                    $feescale = D('Project')->get_feescale_by_cid($case_id);

                    if(is_array($feescale) && !empty($feescale) )
                    {
                        foreach($feescale as $key => $value)
                        {
                            if($value['AMOUNT'][0]=='.'){
                                $value['AMOUNT'] = '0' . $value['AMOUNT'];
                            }
                            $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'];
                        }

                        //设置经办人(编辑状态：保持不变)
                        $form->setMyFieldVal('ADD_USERNAME', $user_config['ADD_USERNAME'], TRUE);
                        //单套收费标准
                        $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
                        //中介佣金
                        $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
                        //置业顾问佣金  外部奖励
                        $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                        //中介成交奖
                        $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                        //置业成交奖金
                        $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);
                    }

                    //重新赋值
                    $form->setMyFieldVal('PRJ_NAME', $user_config['PRJ_NAME'], FALSE);
                    $form->setMyFieldVal('SOURCE', $user_config['SOURCE'], FALSE);
                    $form->setMyFieldVal('CERTIFICATE_TYPE', $user_config['CERTIFICATE_TYPE'], FALSE);
                    $form->setMyFieldVal('CARDSTATUS', $user_config['CARDSTATUS'], FALSE);
                    $form->setMyFieldVal('SIGNEDSUITE', $user_config['SIGNEDSUITE'], FALSE);
                    $form->setMyFieldVal('RECEIPTSTATUS', $user_config['RECEIPTSTATUS'], FALSE);
                    $form->setMyFieldVal('IS_TAKE', $user_config['IS_TAKE'], FALSE);
                    $form->setMyFieldVal('IS_SMS', $user_config['IS_SMS'], FALSE);
                    $form->setMyFieldVal('TOTAL_PRICE', $user_config['TOTAL_PRICE'], FALSE);
                    $form->setMyFieldVal('UNPAID_MONEY', $user_config['UNPAID_MONEY'], TRUE);
                    $form->setMyFieldVal('PAID_MONEY', $user_config['PAID_MONEY'], TRUE);
                    $form->setMyFieldVal('AGENCY_REWARD', $user_config['AGENCY_REWARD'], FALSE);
                    $form->setMyFieldVal('AGENCY_DEAL_REWARD', $user_config['AGENCY_DEAL_REWARD'], FALSE);
                    $form->setMyFieldVal('PROPERTY_DEAL_REWARD', $user_config['PROPERTY_DEAL_REWARD'], FALSE);
                    $form->setMyFieldVal('LEAD_TIME', $user_config['LEAD_TIME'], FALSE);
                    $form->setMyFieldVal('DECORATION_STANDARD', $user_config['DECORATION_STANDARD'], FALSE);


                    $input_arr = array(
                        array('name' => 'CITY_ID', 'val' => $user_config['CITY_ID'], 'id' => 'CITY_ID'),
                        array('name' => 'PRJ_ID', 'val' => $user_config['PRJ_ID'], 'id' => 'PRJ_ID'),
                        array('name' => 'CASE_ID', 'val' => $user_config['CASE_ID'], 'id' => 'CASE_ID'),
                    );
                    $form = $form->addHiddenInput($input_arr);

                    //会员来源
                    $source_arr = $member_model->getPrjSaleMethod($user_config['PRJ_ID']);
                }
            }

            $form->setMyField('SOURCE', 'LISTCHAR', array2listchar($source_arr), FALSE);
            
            //装修标准
            $form->setMyField('DECORATION_STANDARD', 'LISTCHAR', array2listchar($conf_zx_standard), FALSE);

            //增加直销人员
            $form->setMyField('DIRECTSALLER', 'FORMVISIBLE', -1, FALSE);
            $form->setMyField('DIRECTSALLER', 'GRIDVISIBLE', -1, FALSE);
            //显示保存当前设置按钮 (添加 + 编辑)
            if($showForm==1 || $showForm==3)
                $form->showSaveCfg = true;
            
            //绑定状态颜色array('1','BSTATUS') 1为类型对应ERP_STATUS_TYPE表
            //BSTATUS为需要绑定颜色的字段名
            $arr_param = array(
                            array('2','CARDSTATUS') , 
                            array('3','RECEIPTSTATUS'), 
                            array('4','INVOICE_STATUS'), 
                            array('5','FINANCIALCONFIRM')
                        );
            $form = $form->showStatusTable($arr_param);
            $children_data = array(
                                array('付款明细', U('/Member/show_pay_list')),
                                array('退款记录', U('/Member/show_refund_list')),
                                array('开票记录', U('/Member/show_bill_list'))
                            );
            $form->GABTN.="<a id='lock_member' href='javascript:;' data-id = 0 class='btn btn-info btn-sm'>
            会员锁定
            </a>
            <a id='unlock_member' href='javascript:;' data-id = 1 class='btn btn-info btn-sm'>
            会员解锁
            </a>";

            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
            $formhtml =  $form->setChildren($children_data)->getResult();

            $this->assign('form', $formhtml);
            $this->assign('showForm', $showForm);
            //添加搜索条件
            $this->assign('filter_sql',$form->getFilterSql());
            //添加排序条件
            $this->assign('sort_sql',$form->getSortSql());
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
			$this->assign('case_type','ds');
            $this->display('reg_member');
        }
    }
    

	 /**
    +----------------------------------------------------------
    * 注册分销办卡会员
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function DisRegMember()
    {   
        $id = !empty($_POST['ID']) ? intval($_POST['ID']) : 0;
    	$faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $uid = intval($_SESSION['uinfo']['uid']);
        $username = $_SESSION['uinfo']['tname'];
        $showForm = intval($_GET['showForm']);
		
        //操作行为
        $act = !empty($_POST['act']) ? intval($_POST['act']) : '';
    	
    	//实例化会员MODEL
    	$member_model = D('Member');
        
        /***获取会员办卡、开票、发票状态***/
        $status_arr = $member_model->get_conf_all_status_remark();
        
        //装修标准
        $conf_zx_standard = $member_model->get_conf_zx_standard();

        //如果是保存配置
        if($act=='savecfg'){

            $return = array(
                'status'=>false,
                'msg'=>'',
                'data'=>null,
            );

            $formdata_str = $_POST['formdata'];
            parse_str($formdata_str,$formdata);

            $member_info = array();
            //如果返回数据
            if(!empty($formdata)){
                $member_info['CITY_ID'] = $formdata['CITY_ID'];
                $member_info['PRJ_ID'] = $formdata['PRJ_ID'];
                $member_info['PRJ_NAME'] =  u2g($formdata['PRJ_NAME']);
                //获取caseid
                $case_model = D('ProjectCase');
                $case_info = $case_model->get_info_by_pid($member_info['PRJ_ID'], 'fx');
                $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
                $member_info['CASE_ID'] = $case_id;
                $member_info['SOURCE'] = $formdata['SOURCE'];

                $member_info['CERTIFICATE_TYPE'] = $formdata['CERTIFICATE_TYPE'];

                $member_info['CARDSTATUS'] = $formdata['CARDSTATUS'];

                $member_info['SIGNEDSUITE'] = intval($formdata['SIGNEDSUITE']);

                $member_info['RECEIPTSTATUS'] = $formdata['RECEIPTSTATUS'];

                $member_info['IS_TAKE'] = $formdata['IS_TAKE'];

                $member_info['IS_SMS'] = $formdata['IS_SMS'];

                $member_info['TOTAL_PRICE'] = intval($formdata['TOTAL_PRICE']);
				$member_info['TOTAL_PRICE_AFTER'] = intval($formdata['TOTAL_PRICE_AFTER']);
                $member_info['PAID_MONEY'] = 0;
                $member_info['UNPAID_MONEY'] = floatval($formdata['TOTAL_PRICE']);

                $member_info['AGENCY_REWARD'] = floatval($formdata['AGENCY_REWARD']);
                $member_info['AGENCY_DEAL_REWARD'] = floatval($formdata['AGENCY_DEAL_REWARD']);
                $member_info['PROPERTY_DEAL_REWARD'] = floatval( $formdata['PROPERTY_DEAL_REWARD']);

                $member_info['DECORATION_STANDARD'] = intval( $formdata['DECORATION_STANDARD']);
                $member_info['LEAD_TIME'] = $formdata['LEAD_TIME'];
				$member_info['AGENCY_REWARD_AFTER'] = $formdata['AGENCY_REWARD_AFTER'];
				$member_info['OUT_REWARD'] = $formdata['OUT_REWARD'];

                $member_info['ADD_UID'] = $this->uid;
                $member_info['ADD_USERNAME'] = $_SESSION['uinfo']['tname'];

                $member_info_str = serialize($member_info);

                $ret = D("Member")->put_user_config('DISMEMBER_ADD',$member_info_str,$this->uid);
            }

            if($ret){
                $return['status'] = true;
                $return['msg'] = g2u('亲，保存当前分销会员配置成功！');
            }
            die(@json_encode($return));
        }
		if(!empty($_POST) ){
			$project = D('Project');
			$case_model = D('ProjectCase');
			$case_info = $case_model->get_info_by_pid($_POST['PRJ_ID'], 'fx');
			$case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
			$flag1 = $project->get_feescale_by_cid_stype($case_id,1, $_POST['TOTAL_PRICE'],1,0);
			$flag2 = $project->get_feescale_by_cid_stype($case_id,1, $_POST['TOTAL_PRICE_AFTER'],1,1);
			$flag3 = $project->get_feescale_by_cid_stype($case_id,2, $_POST['AGENCY_REWARD'],1,0);
			$flag4 = $project->get_feescale_by_cid_stype($case_id,2, $_POST['AGENCY_REWARD_AFTER'],1,1);
			if($flag1 || $flag2 || $flag3  ||$flag4 ){
				if(!$_POST['HOUSETOTAL']){
					$result['status'] = 0;
					$result['msg'] = g2u('收费标准或佣金为百分比，必须填写房屋总价!');
					echo json_encode($result);
					exit;
				}
			}
			$flag5 = $project->get_feescale_by_cid_stype($case_id,3, $_POST['OUT_REWARD'],1);
			$flag6 = $project->get_feescale_by_cid_stype($case_id,4, $_POST['AGENCY_DEAL_REWARD'],1);
			$flag7 = $project->get_feescale_by_cid_stype($case_id,5, $_POST['PROPERTY_DEAL_REWARD'],1);
			//var_dump($flag5 );var_dump($flag6 );var_dump($_POST['OUT_REWARD']);  
			if($flag5 || $flag6 || $flag7  ){
				if(!$_POST['HOUSETOTAL']){
					$result['status'] = 0;
					$result['msg'] = g2u('当置业顾问成交奖励、中介成交奖励、外部成交奖励选择百分比，必须填写房屋总价!');
					echo json_encode($result);
					exit;
				}
				if($_POST['TOTAL_PRICE'] && !$_POST['TOTAL_PRICE_AFTER'] ){
					$result['status'] = 0;
					$result['msg'] = g2u('当单套收费标准只选择了前佣， 中介成交奖励、外部成交奖励、置业顾问成交奖励必须是金额!');
					echo json_encode($result);
					exit;

				}
			}
		} 

    	//修改会员信息
    	if($this->isPost() && !empty($_POST) && $faction == 'saveFormData' && $id > 0)
    	{	
    		$member_info = array();
    		$member_info['REALNAME'] = u2g($_POST['REALNAME']);
            
            //处理隐藏部分内容
            if($_POST['MOBILENO'] != $_POST['MOBILENO_OLD'])
            {
                $member_info['MOBILENO'] = $_POST['MOBILENO'];
                if($member_info['MOBILENO'] == "")
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,请填入购房人手机号！');
                    echo json_encode($result);
                    exit;
                }
            }
			if($_POST['TOTAL_PRICE']=='' &&  $_POST['TOTAL_PRICE_AFTER']=='' ){
				$result['status'] = 0;
				$result['msg'] = g2u('修改失败,请选择前佣收费标准或者后佣收费标准！');
				echo json_encode($result);
				exit;

			}elseif($_POST['TOTAL_PRICE_AFTER']==''){
				
                 

				$OUT_REWARD = $project->get_feescale_by_cid_stype($case_id,3, $_POST['OUT_REWARD']);
				if($OUT_REWARD){
					$result['status'] = 0;
					$result['msg'] = g2u('修改失败,单套收费标准只有前佣的外部成交奖励不能为百分比！');
				}
				$AGENCY_DEAL_REWARD = $project->get_feescale_by_cid_stype($case_id,4, $_POST['AGENCY_DEAL_REWARD']);
				if($AGENCY_DEAL_REWARD){
					$result['status'] = 0;
					$result['msg'] = g2u('修改失败,单套收费标准只有前佣的中介成交奖励不能为百分比！');
				}
				$PROPERTY_DEAL_REWARD = $project->get_feescale_by_cid_stype($case_id,5, $_POST['PROPERTY_DEAL_REWARD']);
				if($PROPERTY_DEAL_REWARD){
					$result['status'] = 0;
					$result['msg'] = g2u('修改失败,单套收费标准只有前佣的置业顾问成交奖励不能为百分比！');
				}
				  
				if($OUT_REWARD || $AGENCY_DEAL_REWARD ||$PROPERTY_DEAL_REWARD ){
					echo json_encode($result);
					exit;
				}

			}
			if($_POST['TOTAL_PRICE']){
				if(!$_POST['RECEIPTSTATUS']){
					$result['status'] = 0;
					$result['msg'] = g2u('修改失败, 已选择前佣收费标准的必须选择收据状态！');
					echo json_encode($result);
					exit;
				}
				if(!$_POST['RECEIPTNO']){
					$result['status'] = 0;
					$result['msg'] = g2u('修改失败, 已选择前佣收费标准的必须填写收据编号！');
					echo json_encode($result);
					exit;
				}

				if($_POST['AGENCY_REWARD_STATUS']>1){
					if($_POST['AGENCY_REWARD']!=$_POST['AGENCY_REWARD_OLD']){
					$result['status'] = 0;
					$result['msg'] = g2u('修改失败, 中介佣金已申请报销不允许修改！');
					echo json_encode($result);
					exit;

				}
				 
				}
				if($_POST['AGENCY_DEAL_REWARD_STATUS']>1){
					 
					if($_POST['AGENCY_DEAL_REWARD']!=$_POST['AGENCY_DEAL_REWARD_OLD']){
						$result['status'] = 0;
						$result['msg'] = g2u('修改失败, 中介成交奖励已申请报销不允许修改！');
						echo json_encode($result);
						exit;

					}
				}
				if($_POST['PROPERTY_DEAL_REWARD_STATUS']>1){
					 
					if($_POST['PROPERTY_DEAL_REWARD']!=$_POST['PROPERTY_DEAL_REWARD_OLD']){
						$result['status'] = 0;
						$result['msg'] = g2u('修改失败, 置业顾问成交奖励已申请报销不允许修改！');
						echo json_encode($result);
						exit;

					}
				}
				if($_POST['OUT_REWARD_STATUS']>1){
					 
					if($_POST['OUT_REWARD']!=$_POST['OUT_REWARD_OLD']){
						$result['status'] = 0;
						$result['msg'] = g2u('修改失败, 外部成交奖励已申请报销不允许修改！');
						echo json_encode($result);
						exit;

					}
				}

				


			}
			if($_POST['TOTAL_PRICE_AFTER_OLD'] && ($_POST['REWARD_STATUS']==2 || $_POST['REWARD_STATUS']==3 ) ){
				if($_POST['AGENCY_REWARD_STATUS']>1){
					if($_POST['AGENCY_REWARD']!=$_POST['AGENCY_REWARD_OLD']){
					$result['status'] = 0;
					$result['msg'] = g2u('修改失败, 中介佣金已申请报销不允许修改！');
					echo json_encode($result);
					exit;

				}
				 
				}
				if($_POST['AGENCY_DEAL_REWARD_STATUS']>1){
					 
					if($_POST['AGENCY_DEAL_REWARD']!=$_POST['AGENCY_DEAL_REWARD_OLD']){
						$result['status'] = 0;
						$result['msg'] = g2u('修改失败, 中介成交奖励已申请报销不允许修改！');
						echo json_encode($result);
						exit;

					}
				}
				if($_POST['PROPERTY_DEAL_REWARD_STATUS']>1){
					 
					if($_POST['PROPERTY_DEAL_REWARD']!=$_POST['PROPERTY_DEAL_REWARD_OLD']){
						$result['status'] = 0;
						$result['msg'] = g2u('修改失败, 置业顾问成交奖励已申请报销不允许修改！');
						echo json_encode($result);
						exit;

					}
				}
				if($_POST['OUT_REWARD_STATUS']>1){
					 
					if($_POST['OUT_REWARD']!=$_POST['OUT_REWARD_OLD']){
						$result['status'] = 0;
						$result['msg'] = g2u('修改失败, 外部成交奖励已申请报销不允许修改！');
						echo json_encode($result);
						exit;

					}
				}

			}

			if($_POST['TOTAL_PRICE_AFTER_OLD'] && ($_POST['REWARD_STATUS']==2 || $_POST['REWARD_STATUS']==3 ) && $_POST['INVOICE_STATUS']>1 ){
				 
				if($_POST['TOTAL_PRICE_AFTER']!=$_POST['TOTAL_PRICE_AFTER_OLD']){
					$result['status'] = 0;
					$result['msg'] = g2u('修改失败, 该后佣模式会员,已申请结佣且已申请开票,不可以修改后佣收费标准！');
					echo json_encode($result);
					exit;
				}

				 
			}
			if($_POST['TOTAL_PRICE_AFTER_OLD']!=$_POST['TOTAL_PRICE_AFTER'] ){
				$onee = M('Erp_post_commission')->where("CARD_MEMBER_ID=".$_POST['ID'])->find();
				if($onee){
					$ressss = M('Erp_commission_invoice_detail')->where("POST_COMMISSION_ID=".$onee['ID'])->find();
					if($ressss){
						$result['status'] = 0;
						$result['msg'] = g2u('修改失败, 该后佣模式会员,已申请结佣且已申请开票,不可以修改后佣收费标准  ！');
						echo json_encode($result);
						exit;
					}
				}

			}
			if($_POST['AGENCY_REWARD_AFTER_OLD']!=$_POST['AGENCY_REWARD_AFTER'] ){
				$onee = M('Erp_post_commission')->where("CARD_MEMBER_ID=".$_POST['ID'])->find();
				if($onee){
					$ressss = M('Erp_commission_reim_detail')->where("POST_COMMISSION_ID=".$onee['ID'])->find();
					if($ressss){
						$result['status'] = 0;
						$result['msg'] = g2u('修改失败, 该后佣模式会员,已经申请中介佣金报销,不可以修改后佣中介佣金标准  ！');
						echo json_encode($result);
						exit;
					}
				}

			}

			 
			$mres = D('Erp_member_payment')->where('MID='.$id)->select(); //var_dump($mres);
			if($mres && !$_POST['TOTAL_PRICE']){
				$result['status'] = 0;
				$result['msg'] = g2u('有付款明细的分销会员,前佣收费标准不能为空!');
				echo json_encode($result);
				exit;
			}

			if(($_POST['REWARD_STATUS']==3||$_POST['REWARD_STATUS']==2) && !$_POST['TOTAL_PRICE_AFTER']){

				$result['status'] = 0;
				$result['msg'] = g2u('已申请结佣的客户，后佣收费标准必须选择！');
				echo json_encode($result);
				exit;
			}

            if($_POST['LOOKER_MOBILENO'] != $_POST['LOOKER_MOBILENO_OLD'])
            {
                $member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            }
            
    		$member_info['SOURCE'] = $_POST['SOURCE'];
    		$member_info['CARDTIME'] = $_POST['CARDTIME'];
    		$member_info['CERTIFICATE_TYPE'] = $_POST['CERTIFICATE_TYPE'];
            
            //证件号码
            if (trim($_POST['CERTIFICATE_NO']) == '') 
            {
                $result['status'] = 0;
                $result['msg'] = g2u('修改失败，证件号码必须填写！');
                
                echo json_encode($result);
                exit;
            }
                
            //处理号码部分隐藏
            if($_POST['CERTIFICATE_NO'] != $_POST['CERTIFICATE_NO_OLD'])
            {
                $member_info['CERTIFICATE_NO'] = $_POST['CERTIFICATE_NO'];
                if($member_info['CERTIFICATE_TYPE'] == 1)
                {   
                    if (!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $member_info['CERTIFICATE_NO'])) 
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('修改失败，身份证号码格式不正确！');

                        echo json_encode($result);
                        exit;
                    } 
                }
            }
            
    		$member_info['ROOMNO'] = u2g($_POST['ROOMNO']);
    		$member_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
    		$member_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            $member_info['LEAD_TIME'] = strip_tags($_POST['LEAD_TIME']);
            $member_info['DECORATION_STANDARD'] = intval($_POST['DECORATION_STANDARD']);
            $member_info['DIRECTSALLER'] = u2g($_POST['DIRECTSALLER']);
            //附件
            $member_info['ATTACH'] = u2g($_POST['ATTACH']);
            switch($member_info['CARDSTATUS'])
            {
                case '2':
                    //已认购
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    $member_info['SIGNTIME'] = '';
                    $member_info['SIGNEDSUITE'] = '';
                    if($member_info['SUBSCRIBETIME'] == ''|| $member_info['SUBSCRIBETIME'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('修改失败,办卡状态为已办已认购，认购日期必须填写！');
                        
                        echo json_encode($result);
                        exit;
                    }
                    break;
                case '3':
                    //已签约
                    $member_info['SIGNTIME'] = strip_tags($_POST['SIGNTIME']);
                    $member_info['SIGNEDSUITE'] = intval($_POST['SIGNEDSUITE']);
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    if($member_info['SIGNTIME'] == '' || $member_info['SIGNEDSUITE'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('修改失败,办卡状态为已办已签约，签约日期和签约套数必须填写！');

                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['ROOMNO'] == '' && $_POST['CITY_ID'] == 1)
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('修改失败,办卡状态为已办已签约，楼栋房号必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['LEAD_TIME'] == '' || $_POST['DECORATION_STANDARD'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('修改失败,办卡状态为已办已签约，交付时间、装标准必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                    break;
                case '4':
                    //退卡
                    $member_info['BACKTIME'] = strip_tags($_POST['BACKTIME']);
                    $member_info['BACK_UID'] = intval($_POST['BACK_UID']);
                    
                    if($member_info['BACKTIME'] == '' || $member_info['BACK_UID'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('修改失败,办卡状态为退卡，退卡日期和退卡经办人必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                    break;
            }
            
            /*  已办卡未成交状态的会员，可修改为已办卡已认购或者已办卡已签约
                已办卡已认购状态的会员，可修改为已办卡已签约
                已办卡已签约的会员，无法修改
                已退卡的会员，无法修改 
            */
            $cardstatus_old = intval($_POST['CARDSTATUS_OLD']);
            if($cardstatus_old == 1 && !in_array($member_info['CARDSTATUS'], array(1,2,3)))
            {
    			$result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反办卡状态规则：已办卡未成交状态，只可以修改为已办卡已认购或者已办卡已签约');
                
                echo json_encode($result);
                exit;
            }
            else if($cardstatus_old == 2 && !in_array($member_info['CARDSTATUS'], array(2,3)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反办卡状态规则：已办卡已认购状态，只可以修改为已办卡已签约');
                
                echo json_encode($result);
                exit;
            }
            else if($cardstatus_old == 3 && $member_info['CARDSTATUS'] != 3)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反办卡状态规则：已办卡已签约状态，不可修改');
                
                echo json_encode($result);
                exit;
            }
            else if($cardstatus_old == 4 && $member_info['CARDSTATUS'] != 4)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反办卡状态规则：已退卡状态，不可修改');
                
                echo json_encode($result);
                exit;
            }
            
            $member_info['PAY_TYPE'] = $_POST['PAY_TYPE'];
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            /**
                已开未领可以修改为已领或已收回
                已领可以修改为已收回  
                已收回无法修改收据状态
             */    
            $receiptstatus_old = intval($_POST['RECEIPTSTATUS_OLD']);
            if($receiptstatus_old == 2 && !in_array($member_info['RECEIPTSTATUS'], array(2,3,4)))
            {
    			$result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反收据状态规则：已开未领，只可以修改为已领或已收回');
                
                echo json_encode($result);
                exit;
            }
            else if($receiptstatus_old == 3 && !in_array($member_info['RECEIPTSTATUS'], array(2,3,4)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反收据状态规则：已领状态，只可以修改为已开未领或已收回');
                
                echo json_encode($result);
                exit;
            }
            else if($receiptstatus_old == 4 && $member_info['RECEIPTSTATUS'] != 4)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反收据状态规则：已收回状态，不可修改');
                
                echo json_encode($result);
                exit;
            }
            $member_info['RECEIPTNO'] = trim(str_replace(array("，","/","、")," ", $_POST['RECEIPTNO']));
            $member_info['RECEIPTNO'] = preg_replace('/([^0-9])+/',' ',$member_info['RECEIPTNO']);
            $member_info['RECEIPTNO'] = preg_replace('/(\s)+/',' ',$member_info['RECEIPTNO']);
			if($member_info['RECEIPTNO'] != $_POST['RECEIPTNO_OLD']){
				$receiptno = M('Erp_cardmember')->where("RECEIPTNO='".$member_info['RECEIPTNO']."' AND CITY_ID='".$this->channelid."' AND STATUS = 1")->find(); 
				if($receiptno){
					 $result['status'] = 0;
					$result['msg'] = g2u('修改失败,该城市下已经存在相同的收据编号！');
					
					echo json_encode($result);
					exit;
				}
			}

    		$member_info['INVOICE_STATUS'] = $_POST['INVOICE_STATUS'];
            /**
                未开状态，不可修改
                申请中状态，不可以修改
                已开未领状态，可以修改为已领
                已领状态，无法修改
                已收回状态，无法修改状
		    */    
            $invoicestatus_old = intval($_POST['INVOICE_STATUS_OLD']);

            if($invoicestatus_old == 1 && $member_info['INVOICE_STATUS'] != 1)
            {
                $result['status'] = 0;
                $result['msg'] = g2u('修改失败,违反发票状态规则：未开状态，不可修改');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 2 && !in_array($member_info['INVOICE_STATUS'], array(2,3)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反发票状态规则：已开未领状态，只可以修改为已领');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 3 && !in_array($member_info['INVOICE_STATUS'], array(2,3)))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反发票状态规则：已领状态，只可以修改为已开未领');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 4 && $member_info['INVOICE_STATUS'] != 4)
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反发票状态规则：已收回状态，不可以修改');
                
                echo json_encode($result);
                exit;
            }
            else if($invoicestatus_old == 5 && ($member_info['INVOICE_STATUS'] != 5 && $member_info['INVOICE_STATUS'] != 1))
            {
                $result['status'] = 0;
    			$result['msg'] = g2u('修改失败,违反发票状态规则：申请中状态，只能修改为未开或申请中状态');
                
                echo json_encode($result);
                exit;
            }
            $member_info['IS_TAKE'] = $_POST['IS_TAKE'];
            $member_info['IS_SMS'] = $_POST['IS_SMS'];
            $member_info['TOTAL_PRICE'] = floatval($_POST['TOTAL_PRICE']);

            $total_price_old = floatval($_POST['TOTAL_PRICE_OLD']);
            if($total_price_old != $member_info['TOTAL_PRICE']){

                if($member_info['INVOICE_STATUS'] != 1){
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,单套收费标准修改规则：修改单套收费标准，发票状态只能是未开！');

                    die(@json_encode($result));
                }

                //更新未缴纳金额
                $member_info['UNPAID_MONEY'] = $member_info['TOTAL_PRICE'] - floatval($_POST['PAID_MONEY']) - floatval($_POST['REDUCE_MONEY']);
                //更新确认状态
                $userInfo = $member_model->get_userinfo_by_uid($id);

                $member_pay = D('MemberPay');
                $confirmMoney = $member_pay->get_sum_pay($id,'confirmed');

                if($confirmMoney==0){
                    $member_info['FINANCIALCONFIRM'] = 1;
                }
                else if($confirmMoney + $_POST['REDUCE_MONEY'] < $member_info['TOTAL_PRICE']){
                    $member_info['FINANCIALCONFIRM'] = 2;
                }
                else if($confirmMoney + $_POST['REDUCE_MONEY'] >= $member_info['TOTAL_PRICE']){
                    $member_info['FINANCIALCONFIRM'] = 3;
                }
            }

            $member_info['AGENCY_REWARD'] = floatval($_POST['AGENCY_REWARD']);
            /**中介佣金报销金额修改时，查看会员是否已经申请过中介佣金报销**/
            if($member_info['AGENCY_REWARD'] != floatval($_POST['AGENCY_REWARD_OLD']))
            {
               $reim_deital_model = D('ReimbursementDetail');
               $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id , 3);
               
               if($is_reimed)
               {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,中介佣金已申请报销,无法修改!');
                    
                    echo json_encode($result);
                    exit;
               }
            }
            
            $member_info['AGENCY_DEAL_REWARD'] = floatval($_POST['AGENCY_DEAL_REWARD']);
            /**中介成交奖励报销金额修改时，查看会员是否已经申请过中介成交奖励报销**/
            if($member_info['AGENCY_DEAL_REWARD'] != floatval($_POST['AGENCY_DEAL_REWARD_OLD']))
            {
               $reim_deital_model = D('ReimbursementDetail');
               $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id , 4);
               
               if($is_reimed)
               {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,中介成交奖励已申请报销,无法修改!');
                    
                    echo json_encode($result);
                    exit;
               }
            }
            $member_info['PROPERTY_DEAL_REWARD'] = floatval($_POST['PROPERTY_DEAL_REWARD']);
            /**置业顾问成交奖励报销金额修改时，查看会员是否已经申请过置业顾问成交奖励报销**/
            if($member_info['PROPERTY_DEAL_REWARD'] != floatval($_POST['PROPERTY_DEAL_REWARD_OLD']))
            {
               $reim_deital_model = D('ReimbursementDetail');
               $is_reimed = $reim_deital_model->is_exisit_reim_detail($_POST['CASE_ID'], $id , 6);
               
               if($is_reimed)
               {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,置业顾问成交奖励已申请报销,无法修改!');
                    
                    echo json_encode($result);
                    exit;
               }
            }
    		$member_info['NOTE'] = u2g($_POST['NOTE']);
                $member_info['AGENCY_NAME'] = u2g($_POST['AGENCY_NAME']);
    		$member_info['UPDATETIME'] = date('Y-m-d');
            
            //中介来源，办卡状态是已办已签约必须
            if(($member_info['SOURCE'] == 1 || $member_info['SOURCE'] == 7 || $member_info['SOURCE'] == 8) && $member_info['CARDSTATUS'] == 3 )
            {
                if($member_info['AGENCY_REWARD'] == 0 && $_POST['AGENCY_REWARD_AFTER'] == 0)
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('修改失败,前佣或者后佣中介佣金必须至少填写一个');
                    
                    echo json_encode($result);
                    exit;
                }
            }

    		$update_num = 0;
			$member_info['FILINGTIME']=$_POST['FILINGTIME'];
			$member_info['TOTAL_PRICE_AFTER']=$_POST['TOTAL_PRICE_AFTER'];
			$member_info['AGENCY_REWARD_AFTER']=$_POST['AGENCY_REWARD_AFTER'];
			$member_info['OUT_REWARD']=$_POST['OUT_REWARD'];
			$member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
    		$update_num = $member_model->update_info_by_id($id, $member_info);
    		
    		if($update_num > 0)
    		{   
                if($_POST['CARDSTATUS_OLD'] < $member_info['CARDSTATUS'] && $member_info['CARDSTATUS'] > 2)
                {
                    switch($member_info['CARDSTATUS'])
                    {
                        case '3':
                            $tlfcard_status = 2;
                            $tlfcard_signtime = strtotime($member_info['SIGNTIME']);
                            $tlfcard_backtime = 0;
                            break;
                        case '4':
                            $tlfcard_status = 3; 
                            $tlfcard_signtime = 0;
                            $tlfcard_backtime = strtotime($member_info['BACKTIME']);
                            break;
                    }
                    
                    $crm_api_arr = array();
                    $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                    $crm_api_arr['mobile'] = strip_tags($_POST['MOBILENO_HIDDEN']);
                    $crm_api_arr['activefrom'] = 104;
                    $crm_api_arr['city'] = $this->city;
                    $crm_api_arr['activename'] =  urlencode(u2g($_POST['PRJ_NAME']).
                    $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']].
                    $member_info['CARDTIME'].$conf_zx_standard[$_POST['DECORATION_STANDARD']]);
                    $crm_api_arr['importfrom'] = urlencode('团立方监控后台');
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = strtotime($member_info['CARDTIME']);
                    $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                    $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                    $crm_api_arr['projectid'] = $_POST['PRJ_ID'];
                    
                    if($member_info['CARDSTATUS'] == 3)
                    {
                        $house_info = M('erp_house')->field('PRO_LISTID')->
                                where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();
                        
                        $pro_listid = !empty($house_info['PRO_LISTID']) ?
                                intval($house_info['PRO_LISTID']) : '';
                        
                        $crm_api_arr['floor_id'] = $pro_listid;
                    }
                    
                    submit_crm_data_by_api($crm_api_arr);
                }


                //通知全链条精准导购系统
                if($_POST['CARDSTATUS_OLD'] != $member_info['CARDSTATUS']){

                    $qltStatus = 3;

                    switch($member_info['CARDSTATUS'])
                    {
                        case '1':
                            $qltStatus = 3;
                            break;
                        case '2':
                            $qltStatus = 4;
                            break;
                        case '3':
                            $qltStatus = 5;
                            break;
                    }

                    $queryRet = M('Erp_project')->field('CONTRACT')
                        ->where('ID='.$_POST['PRJ_ID'])->find();

                    $qltContract = $queryRet['CONTRACT'];

                    $qltUserInfo = $member_model->get_userinfo_by_uid($id);

                    $qltApiUrl = QLTAPI . '###serviceName=updateStatus###status=' . $qltStatus . '###contract=' . $qltContract . '###phone=' . $qltUserInfo['MOBILENO'];
                    api_log($this->channelid,$qltApiUrl,0,$uid,3);
                }

                $result['status'] = 1;
                $result['msg'] = '修改成功';

            }
    		else
    		{
                    $result['status'] = 0;
                    $result['msg'] = '修改失败';
    		}
    		
            $result['msg'] = g2u($result['msg']);

            if ($result['forward'] == '' && $_REQUEST['fromUrl']) {
                $result['forward'] = $_REQUEST['fromUrl'];  // 跳转地址
            }

    		echo json_encode($result);
    		exit;
    	}
    	//新增
    	else if (!empty($_POST) && $faction == 'saveFormData')
        {   
            $member_info = array();
            $member_info['CITY_ID'] = $_POST['CITY_ID'];
            $member_info['PRJ_ID'] = $_POST['PRJ_ID'];
            $member_info['PRJ_NAME'] =  u2g($_POST['PRJ_NAME']);
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_pid($member_info['PRJ_ID'], 'fx');
            $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
            $member_info['CASE_ID'] = $case_id;
            $member_info['REALNAME'] =  u2g($_POST['REALNAME']);
            $member_info['MOBILENO'] = $_POST['MOBILENO'];
            $member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            $member_info['SOURCE'] = $_POST['SOURCE'];
            $member_info['CARDTIME'] = $_POST['CARDTIME'];
            $member_info['CERTIFICATE_TYPE'] = $_POST['CERTIFICATE_TYPE'];
            $member_info['CERTIFICATE_NO'] = $_POST['CERTIFICATE_NO'];
            $member_info['DIRECTSALLER'] = u2g($_POST['DIRECTSALLER']);
            if($member_info['CERTIFICATE_TYPE'] == 1)
            {
                if (!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $member_info['CERTIFICATE_NO'])) 
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败，身份证号码格式不正确！');
                    
                    echo json_encode($result);
                    exit;
                } 
            }

            else if (trim($member_info['CERTIFICATE_NO']) == '') 
            {
                $result['status'] = 0;
                $result['msg'] = g2u('添加失败，证件号码必须填写！');

                echo json_encode($result);
                exit;
            }
			if($_POST['TOTAL_PRICE']=='' &&  $_POST['TOTAL_PRICE_AFTER']=='' ){
				$result['status'] = 0;
				$result['msg'] = g2u('添加失败,请选择前佣收费标准或者后佣收费标准！');
				echo json_encode($result);
				exit;

			}elseif($_POST['TOTAL_PRICE_AFTER']==''){
				//$project = D('Project');
				////$case_model = D('ProjectCase');
                //$case_info = $case_model->get_info_by_pid($_POST['PRJ_ID'], 'fx');
                //$case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
                 

				$OUT_REWARD = $project->get_feescale_by_cid_stype($case_id,3, $_POST['OUT_REWARD']);
				if($OUT_REWARD){
					$result['status'] = 0;
					$result['msg'] = g2u('添加失败,单套收费标准只有前佣的外部成交奖励不能为百分比！');
				}
				$AGENCY_DEAL_REWARD = $project->get_feescale_by_cid_stype($case_id,4, $_POST['AGENCY_DEAL_REWARD']);
				if($AGENCY_DEAL_REWARD){
					$result['status'] = 0;
					$result['msg'] = g2u('添加失败,单套收费标准只有前佣的中介成交奖励不能为百分比！');
				}
				$PROPERTY_DEAL_REWARD = $project->get_feescale_by_cid_stype($case_id,5, $_POST['PROPERTY_DEAL_REWARD']);
				if($PROPERTY_DEAL_REWARD){
					$result['status'] = 0;
					$result['msg'] = g2u('添加失败,单套收费标准只有前佣的置业顾问成交奖励不能为百分比！');
				}
				  
				if($OUT_REWARD || $AGENCY_DEAL_REWARD ||$PROPERTY_DEAL_REWARD ){
					echo json_encode($result);
					exit;
				}

			}
            if($_POST['TOTAL_PRICE']){
				if(!$_POST['RECEIPTSTATUS']){
					$result['status'] = 0;
					$result['msg'] = g2u('添加失败, 已选择前佣收费标准的必须选择收据状态！');
					echo json_encode($result);
					exit;
				}
				if(!$_POST['RECEIPTNO']){
					$result['status'] = 0;
					$result['msg'] = g2u('添加失败, 已选择前佣收费标准的必须填写收据编号！');
					echo json_encode($result);
					exit;
				}


			}
            $member_info['ROOMNO'] =  u2g($_POST['ROOMNO']);
            $member_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
            $member_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            $member_info['LEAD_TIME'] = strip_tags($_POST['LEAD_TIME']);
            $member_info['DECORATION_STANDARD'] = intval($_POST['DECORATION_STANDARD']);
            
            switch($member_info['CARDSTATUS'])
            {
                case '2':
                    //已认购
                    $member_info['SUBSCRIBETIME'] = strip_tags($_POST['SUBSCRIBETIME']);
                    $member_info['SIGNTIME'] = '';
                    $member_info['SIGNEDSUITE'] = '';
                    if($member_info['SUBSCRIBETIME'] == '' || $member_info['SUBSCRIBETIME'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为已办已认购，认购日期必须填写！');
                        
                        echo json_encode($result);
                        exit;
                    }
                break;
                case '3':
                    //已签约
                    $member_info['SIGNTIME'] = strip_tags($_POST['SIGNTIME']);
                    $member_info['SIGNEDSUITE'] = intval($_POST['SIGNEDSUITE']);
                    if($member_info['SIGNTIME'] == '' || $member_info['SIGNEDSUITE'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为已办已签约，签约日期和签约套数必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['ROOMNO'] == '' && $_POST['CITY_ID'] == 1)
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为已办已签约，楼栋房号必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                    
                    if($member_info['LEAD_TIME'] == '' || $_POST['DECORATION_STANDARD'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为已办已签约，交付时间、装标准必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                break;
                case '4':
                    //退卡
                    $member_info['BACKTIME'] = strip_tags($_POST['BACKTIME']);
                    $member_info['BACK_UID'] = intval($_POST['BACK_UID']);
                    
                    if($member_info['BACKTIME'] == '' || $member_info['BACK_UID'] == '')
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为退卡，退卡日期和退卡经办人必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                break;
            }
            
            $member_info['PAY_TYPE'] = $_POST['PAY_TYPE'];
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            $member_info['RECEIPTNO'] = trim(str_replace(array("，","/","、"),",", $_POST['RECEIPTNO']));
            $member_info['RECEIPTNO'] = preg_replace('/([^0-9])+/',' ',$member_info['RECEIPTNO']);
            $member_info['RECEIPTNO'] = preg_replace('/(\s)+/',' ',$member_info['RECEIPTNO']);
			if($member_info['RECEIPTNO'] ){
				$receiptno = M('Erp_cardmember')->where("RECEIPTNO='".$member_info['RECEIPTNO']."' AND CITY_ID='".$this->channelid."' AND STATUS = 1")->find(); 
				if($receiptno){
					 $result['status'] = 0;
					$result['msg'] = g2u('添加失败,该城市下已经存在相同的收据编号！');
					
					echo json_encode($result);
					exit;
				}
			}

            $member_info['INVOICE_STATUS'] = 1; //新增默认未开
            $member_info['IS_TAKE'] = $_POST['IS_TAKE'];
            $member_info['IS_SMS'] = $_POST['IS_SMS'];
            //附件
            $member_info['ATTACH'] = u2g($_POST['ATTACH']);
            $member_info['TOTAL_PRICE'] = intval($_POST['TOTAL_PRICE']);
            $member_info['PAID_MONEY'] = 0;
            $member_info['UNPAID_MONEY'] = floatval($_POST['TOTAL_PRICE']);
            $member_info['AGENCY_REWARD'] = floatval($_POST['AGENCY_REWARD']);
            $member_info['AGENCY_DEAL_REWARD'] = floatval($_POST['AGENCY_DEAL_REWARD']);
            $member_info['PROPERTY_DEAL_REWARD'] = floatval( $_POST['PROPERTY_DEAL_REWARD']);
            
            //中介来源
            if($member_info['SOURCE'] == 1 && $member_info['CARDSTATUS'] == 3 )
            {
                if($member_info['AGENCY_REWARD'] == 0  && $_POST['AGENCY_REWARD_AFTER'] == 0)
                {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败,前佣或者后佣中介佣金必须至少填写一个');

                    echo json_encode($result);
                    exit;
                }
            }
            
            $member_info['ADD_UID'] = intval($_SESSION['uinfo']['uid']);
            $member_info['ADD_USERNAME'] = $_SESSION['uinfo']['tname'];
            $member_info['NOTE'] =  u2g($_POST['NOTE']);
            $member_info['AGENCY_NAME'] =  u2g($_POST['AGENCY_NAME']);
            $member_info['CREATETIME'] = date('Y-m-d H:i:s');
            $member_info['STATUS'] = 1;
            
            /****是否需要到场确认****/
            $is_crm_confirm = intval($_POST['is_crm_confirm']);
            $is_fgj_confirm = intval($_POST['is_fgj_confirm']);
            if($is_crm_confirm == 1 || $is_fgj_confirm == 1)
            {
                //客户ID
                $customer_id = intval($_POST['customer_id']);
                //验证码
                $code = strip_tags($_POST['code']);
                //数据来源
                $is_from = strip_tags($_POST['is_from']);
                //到场确认
                if($is_from == 1 && $is_crm_confirm == 1)
                {
                    $result = arrival_confirm_crm($customer_id , $code);
                }
                else if($is_from == 2 && $is_fgj_confirm == 1)
                {   
                    //经纪人ID
                    $ag_id = intval($_POST['ag_id']);
                    //报备ID
                    $cp_id = intval($_POST['cp_id']);
                    //城市参数
                    $user_city_py = $_SESSION['uinfo']['city'];
                    $userinfo_crm_arr = get_crm_userinfo_by_pid_telno($member_info['PRJ_ID'], 
                    		$member_info['MOBILENO'], $user_city_py);
                    if(is_array($userinfo_crm_arr) && $userinfo_crm_arr['result'] == 1 && 
                            !empty($userinfo_crm_arr['meminfo']))
                    {   
                        if( $userinfo_crm_arr['meminfo']['codestatus'] != 1 )
                        {
                            //CRM中用户ID
                            $customer_id_crm = 0;
                            $customer_id_crm = $userinfo_crm_arr['meminfo']['pmid'];
                            $up_result = update_crm_user_source($customer_id_crm , 5);
                        }
                    }

                    $result = arrival_confirm_fgj($customer_id , $ag_id , $cp_id);
                }

                //记录日志
                $is_sucess = intval($result['result']);
                $msg = $is_sucess == 1 ? '到场确认成功' : '验证失败，验证码无效或已过期';
                //arrival_confirm_log($customer_id, $member_info['REALNAME'], $member_info['MOBILENO'],
                //		$code, $_POST['LIST_ID'], $member_info['PRJ_ID'], $is_from, $is_sucess);
            }
			$member_info['IS_DIS']=2;//分销
			$member_info['FILINGTIME']=$_POST['FILINGTIME'];
			$member_info['TOTAL_PRICE_AFTER']=$_POST['TOTAL_PRICE_AFTER'];
			$member_info['AGENCY_REWARD_AFTER']=$_POST['AGENCY_REWARD_AFTER'];
			$member_info['OUT_REWARD']=$_POST['OUT_REWARD'];
			$member_info['REWARD_STATUS']=1;
			$member_info['OUT_REWARD_STATUS']=1;

            /****是否需要到场确认****/
            $insert_id = $member_model->add_member_info($member_info);
            
            if($insert_id > 0)
            {
                //发送短信
                if(isset($member_info['IS_SMS']) && $member_info['IS_SMS'] == 2 
                    && $member_info['CARDSTATUS'] < 4)
                {
                    $msg = "尊敬的365会员".$member_info['REALNAME']."，"."您已办卡成功,客服热线400-8181-365。";
                    send_sms($msg, $member_info['MOBILENO'], $this->city_config_array[$this->channelid]);
                }
                
                //crm 
                if($member_info['CARDSTATUS'])
                {
                    switch($member_info['CARDSTATUS'])
                    {
                        case '1':
                        case '2':
                        $tlfcard_status = 1;
                        $tlfcard_signtime = 0;
                        $tlfcard_backtime = 0;
                        break;
                        case '3':
                            $tlfcard_status = 2;
                            $tlfcard_signtime = strtotime($member_info['SIGNTIME']);
                            $tlfcard_backtime = 0;
                        break;
                        case '4':
                            $tlfcard_status = 3;
                            $tlfcard_signtime = 0;
                            $tlfcard_backtime = strtotime($member_info['BACKTIME']);
                        break;
                    }
                    $crm_api_arr = array();
                    $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                    $crm_api_arr['mobile'] = $member_info['MOBILENO'];
                    $crm_api_arr['activefrom'] = 104;
                    $crm_api_arr['city'] = $this->city;
                    $crm_api_arr['activename'] =  urlencode(u2g($_POST['PRJ_NAME']).
                            $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']].$member_info['CARDTIME'].$conf_zx_standard[$_POST['DECORATION_STANDARD']]);
                    $crm_api_arr['importfrom'] = urlencode('团立方监控后台');
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = strtotime($member_info['CARDTIME']);
                    $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                    $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                    $crm_api_arr['projectid'] = $_POST['PRJ_ID'];
                    
                    if($member_info['CARDSTATUS'] == 3)
                    {   
                        $house_info = M('erp_house')->field('PRO_LISTID')->
                                where("PROJECT_ID = '".$_POST['PRJ_ID']."'")->find();
                        
                        $pro_listid = !empty($house_info['PRO_LISTID']) ?
                                intval($house_info['PRO_LISTID']) : '';
                        
                        $crm_api_arr['floor_id'] = $pro_listid;
                    }
                    
                    submit_crm_data_by_api($crm_api_arr);
                }
                
                $result['status'] = 2;
                $result['msg'] = '添加会员成功';
            }
            else
            {	
            	$result['status'] = 0;
            	$result['msg'] = '添加会员失败！@';
            }
            
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }

        else if($faction == 'delData')
        {   
            //查看会员是否存在财务已确认的付款明细，如果存在不允许删除
            $mid = intval($_GET['ID']);
            $update_num = 0;
            
            if($mid > 0)
            {   
                $member_pay = D('MemberPay');
				$members = D('Member');
                $member_pay_info = $member_pay->get_payinfo_by_mid($mid);
                $conf_pay_status = $member_pay->get_conf_status();

                //获取会员信息
                $member_info = $member_model->get_info_by_id($mid);
                
                $confirm_payment_num = 0;
				$confirm_yong_num = 0;
                if(is_array($member_pay_info) && !empty($member_pay_info))
                {
                    foreach($member_pay_info as $key => $value)
                    {
                        if($value['STATUS'] == $conf_pay_status['confirmed'])
                        {
                            $confirm_payment_num ++;
                        }
						 
                    }
                    
                    if($confirm_payment_num > 0)
                    {
                        $result['status'] = 0;
                        $result['msg'] = g2u('删除会员失败，存在财务确认付款明细');
                        echo json_encode($result);
                        exit;
                    }
                    
                    //删除会员付款明细信息
                    $delete_payment = $member_pay->del_pay_detail_by_mid($mid);
					
                    if($delete_payment > 0)
                    {
                        $income_from = 1;//电商会员支付
                        $income_model = D('ProjectIncome');
                        //删除收益
                        foreach($member_pay_info as $key => $value)
                        {
                                $income_model->delete_income_info($member_info['CASE_ID'], $mid, $value['ID'], $income_from);
                        }
                    }
                }
                $ss = $members->check_member_status2($mid); 
				if($ss){
					$result['status'] = 0;
					$result['msg'] = g2u('删除会员失败，存在已申请结佣的会员');
					echo json_encode($result);
					exit;
				}
                //删除会员信息
                $update_num = $member_model->delete_info_by_id($mid);
                
                //删除结果
                if($update_num > 0)
                {
                    /***退卡通知CRM***/
                    if($member_info['CARDSTATUS'] != 4)
                    {   
                        $crm_api_arr = array();
                        $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                        $crm_api_arr['mobile'] = strip_tags($member_info['MOBILENO']);
                        $crm_api_arr['activefrom'] = 104;
                        $crm_api_arr['city'] = $this->city;
                        $crm_api_arr['activename'] =  urlencode($member_info['PRJ_NAME'].
                                '退卡'. oracle_date_format($member_info['CARDTIME'], 'Y-m-d').$conf_zx_standard[$member_info['DECORATION_STANDARD']]);
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

                        submit_crm_data_by_api($crm_api_arr);
                    }

                    //会员操作日志
                    $log_info = array();
                    $log_info['OP_UID'] = $uid;
                    $log_info['OP_USERNAME'] = $username;
                    $log_info['OP_LOG'] = '删除会员信息【'.$mid.'】';
                    $log_info['ADD_TIME'] = date('Y-m-d H:i:s');
                    $log_info['OP_CITY'] = $this->channelid;
                    $log_info['OP_IP'] = GetIP();
                    $log_info['TYPE'] = 2;
                    
                    member_opreate_log($log_info);

                    //全链条精准导购系统(变更状态)
                    $qltStatus = 6;
                    $queryRet = M('Erp_project')->field('CONTRACT')
                        ->where('ID='.$member_info['PRJ_ID'])->find();

                    $qltContract = $queryRet['CONTRACT'];

                    $qltApiUrl = QLTAPI . '###serviceName=updateStatus###status=' . $qltStatus . '###contract=' . $qltContract . '###phone=' . $member_info['MOBILENO'];
                    api_log($this->channelid,$qltApiUrl,0,intval($_SESSION['uinfo']['uid']),3);

                    $result['status'] = 'success';
                    $result['msg'] = '删除会员成功';
                }
                else
                {	
                    $result['status'] = 'error';
                    $result['msg'] = '删除会员失败！';
                }
            }
            else 
            {
                $result['status'] = 'error';
                $result['msg'] = '参数异常！';
            }
            
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        else
        {   
            Vendor('Oms.Form');
            $form = new Form();
            
            $form = $form->initForminfo(103);
            $form->SQLTEXT = "(";
            $form->SQLTEXT .= "SELECT DISTINCT * FROM ERP_CARDMEMBER WHERE CITY_ID = '".$this->channelid."' AND STATUS = 1 ";
            
            //是否有查看全部的权限
            if(!$this->p_auth_all)
            {
                $form->SQLTEXT .= " AND PRJ_ID IN "
                    . " (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' AND ISVALID = '-1' AND ERP_ID = 2) ";
            }
            
            //是否自己创建
            if(!$this->p_vmem_all)
            {
                $form->SQLTEXT .= " AND ADD_UID = $uid";
            }
			/*
            //付款明细表查询条件（业务部门需求）
            if(!empty($_REQUEST))
            {   
                //截断搜索条件，改变搜索方式，使用联表字段作为子查询条件
                $pay_search_filed = array ('RETRIEVAL', 'CVV2', 'MERCHANT_NUMBER', 'TRADE_TIME', 'STATUS');
                
                $cond_where = "";
                for($i = 1 ; $i <= 4 ; $i ++)
                {   
                    if(in_array($_REQUEST['search'.$i], $pay_search_filed))
                    {   
                       //拼接子查询SQL
                       $cond_where .=  
                            $form->pjsql($_REQUEST['search'.$i], $_REQUEST['search'.$i.'_s'], $_REQUEST['search'.$i.'_t'], $_REQUEST['search'.$i.'_t_type']);
                       
                       //拼接子查询后，翻页需要用到搜索参数，组装搜索条件给分页函数使用
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'='.$_REQUEST['search'.$i];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_s='.$_REQUEST['search'.$i.'_s'];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_t='.$_REQUEST['search'.$i.'_t'];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_t_type='.$_REQUEST['search'.$i.'_t_type'];
                       
                       unset($_POST['search'.$i]);
                       unset($_POST['search'.$i.'_s']);
                       unset($_POST['search'.$i.'_t']);
                       unset($_POST['search'.$i.'_t_type']);
                       
                       unset($_GET['search'.$i]);
                       unset($_GET['search'.$i.'_s']);
                       unset($_GET['search'.$i.'_t']);
                       unset($_GET['search'.$i.'_t_type']);
                    }
                }
                
                if($cond_where != '')
                {
                    $form->SQLTEXT .= "  AND ID IN (SELECT DISTINCT MID FROM ERP_MEMBER_PAYMENT WHERE STATUS != 4 ".$cond_where.")";
                }
            }
			*/
			///////////////

			//付款明细表查询条件（业务部门需求）
            if(!empty($_REQUEST))
            {   
                //截断搜索条件，改变搜索方式，使用联表字段作为子查询条件
                $pay_search_filed = array ('RETRIEVAL', 'CVV2', 'MERCHANT_NUMBER', 'TRADE_TIME', 'STATUS','REFUND_TIME','REFUND_APPLY_TIME');
                
                $cond_where = "";
                for($i = 1 ; $i <= 4 ; $i ++)
                {   
                    if(in_array($_REQUEST['search'.$i], $pay_search_filed))
                    {   
                       //拼接子查询SQL
                        if($_REQUEST['search'.$i] == "REFUND_APPLY_TIME"){
                            $_REQUEST['search'.$i] = "CREATETIME";
                        }
                       $cond_where .=  
                            $form->pjsql($_REQUEST['search'.$i], $_REQUEST['search'.$i.'_s'], $_REQUEST['search'.$i.'_t'], $_REQUEST['search'.$i.'_t_type']);
                       
                       //拼接子查询后，翻页需要用到搜索参数，组装搜索条件给分页函数使用
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'='.$_REQUEST['search'.$i];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_s='.$_REQUEST['search'.$i.'_s'];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_t='.$_REQUEST['search'.$i.'_t'];
                       $_SERVER['REQUEST_URI'] .= '&search'.$i.'_t_type='.$_REQUEST['search'.$i.'_t_type'];
                       
                       unset($_POST['search'.$i]);
                       unset($_POST['search'.$i.'_s']);
                       unset($_POST['search'.$i.'_t']);
                       unset($_POST['search'.$i.'_t_type']);
                       
                       unset($_GET['search'.$i]);
                       unset($_GET['search'.$i.'_s']);
                       unset($_GET['search'.$i.'_t']);
                       unset($_GET['search'.$i.'_t_type']);
                    }
                }
                
                if($cond_where != '')
                {
                    if($_REQUEST['search1'] == "CREATETIME" or $_REQUEST['search2'] == "CREATETIME" or
                    $_REQUEST['search3'] == "CREATETIME" or $_REQUEST['search4'] == "CREATETIME"){
                        $form->SQLTEXT .= "  AND ID IN (SELECT DISTINCT MID  FROM ERP_MEMBER_REFUND_DETAIL WHERE 1=1 " . $cond_where . ")";
                    }else {
                        $form->SQLTEXT .= "  AND ID IN (SELECT DISTINCT MID FROM ERP_MEMBER_PAYMENT WHERE STATUS != 4 " . $cond_where . ")";
                    }
                }
            }
			///////////////
            $form->SQLTEXT .= " AND IS_DIS=2 )";
            //echo $form->SQLTEXT;
            //手机号码隐藏
            $form->setMyField('MOBILENO', 'ENCRY', '4,8', FALSE);
            $form->setMyField('LOOKER_MOBILENO', 'ENCRY', '4,8', FALSE);
			$form->setMyField('TOTAL_PRICE', 'FIELDMEANS', '前佣收费标准', FALSE);
			//$form->setMyField('TOTAL_PRICE', 'ISVIRTUAL', '-1', FALSE);
			$form->setMyField('AGENCY_REWARD', 'FIELDMEANS', '前佣中介佣金', FALSE);
			//$form->setMyField('AGENCY_REWARD', 'ISVIRTUAL', '-1', FALSE);
			$form->setMyField('FILINGTIME', 'FORMVISIBLE', '-1', FALSE);

            //直销人员
            $form->setMyField('DIRECTSALLER','FORMVISIBLE',-1);
            $form->setMyField('DIRECTSALLER','GRIDVISIBLE',-1);
            //设置证件类型
            $certificate_type_arr = $member_model->get_conf_certificate_type();
            $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR', 
            		array2listchar($certificate_type_arr), FALSE);

            //证件号码
            $form->setMyField('CERTIFICATE_NO', 'ENCRY', '4,13', FALSE);
            
            //设置付款方式
	        $member_pay = D('MemberPay');
	        $pay_arr = $member_pay->get_conf_pay_type();
	        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', 
	        		array2listchar($pay_arr), FALSE);
	        
			$form->GABTN = "<a id='refund_by_mid' href='javascript:;' class='btn btn-info btn-sm'>
				申请退款
			</a>
			<a id='apply_invoice' href='javascript:;' class='btn btn-info btn-sm'>
				申请开票
			</a>
			<a onclick='discount();' href='javascript:;' class='btn btn-info btn-sm' id='apply_discount'>
				申请减免
			</a>
			<a onclick='recycle_invoice();' href='javascript:;' class='btn btn-info btn-sm' id='recycle_invoice'>
				申请退票
			</a>
			<a onclick='change_invoice()' href='javascript:;' class='btn btn-info btn-sm' id='change_invoice'>
				申请换发票
			</a>
			<a id='agency_reward_reim' href='javascript:;' class='btn btn-info btn-sm'>
				中介佣金报销
			</a>
			<a id='agency_deal_reward_reim' href='javascript:;' class='btn btn-info btn-sm'>
				中介成交奖励报销
			</a>
			<a id='property_deal_reward_reim' href='javascript:;' class='btn btn-info btn-sm'>
				置业成交奖励报销
			</a>
			<a id='out_reward_reim' href='javascript:;' class='btn btn-info btn-sm'>
				外部成交奖励报销
			</a>
			<a id='download_member' href='javascript:;' class='btn btn-info btn-sm'>
				会员导出
			</a>
			<a id='import_member' href='javascript:;' class='btn btn-info btn-sm'>
				会员导入
			</a>
			<a id='view_memberinfo' href='javascript:;' class='btn btn-info btn-sm'>
				查看会员
			</a>
			<a id='batch_change_status' href='javascript:;' class='btn btn-info btn-sm'>
				批量变更信息
			</a><a id='pro_refund' href='javascript:;' class='btn btn-info btn-sm'>
				申请退房
			</a>
			<a  id='pro_post_commission'  href='javascript:;' class='btn btn-info btn-sm'>
				申请结佣
			</a>
			<a  id='move_member_prj'  href='javascript:;' class='btn btn-info btn-sm'>
				项目转移
			</a>";
	        //办卡状态
            $card_status_arr = $status_arr['CARDSTATUS'];
            if($showForm == 3)
            {
                array_pop($card_status_arr); 

            }
	        $form->setMyField('CARDSTATUS', 'LISTCHAR', 
	        		array2listchar($card_status_arr), FALSE);
            
            $current_time = date('Y-m-d');
            if($showForm == 3)
            {   
                //新增时候展示办卡时间默认
               // $form->setMyFieldVal('CARDTIME', $current_time, false);
            }
	        
	        //发票状态
            if($showForm == 3)
            {   
                //新增页面发票只读、默认为未开
                $form->setMyFieldVal('INVOICE_STATUS', '1', TRUE );
                $form->setMyField('INVOICE_STATUS', 'LISTCHAR', 
	        		array2listchar($status_arr['INVOICE_STATUS']), TRUE);
                
                $form->setMyFieldVal('INVOICE_STATUS', '1', TRUE );
            }
            else
            {
                $form->setMyField('INVOICE_STATUS', 'LISTCHAR',
                        array2listchar($status_arr['INVOICE_STATUS']), FALSE);
            }
            
            //支付信息财务确认状态（支持搜索，勿删）
            $conf_pay_status = $member_pay->get_conf_status_remark();
            $form->setMyField('STATUS', 'LISTCHAR',
                        array2listchar($conf_pay_status), FALSE);
	        
	        //收据状态
            $receipt_status_arr = $status_arr['RECEIPTSTATUS'];
            if($showForm == 3)
            {
                array_pop($receipt_status_arr); 
            }
	        $form->setMyField('RECEIPTSTATUS', 'LISTCHAR', 
	        		array2listchar($receipt_status_arr), FALSE);
            
            //表单页面
            /***
             *   $showForm 1: 编辑
             *   $showForm 2: 查看
             *   $showForm 3: 新增
             */
            if($showForm == 1 || $showForm == 3 || $showForm == 2 )
            {   
                //修改记录ID
                $modify_id = !empty($_GET['ID']) ? intval($_GET['ID']) : 0;

                //设置经办人信息
                $userinfo = array();
                $form->setMyFieldVal('ADD_USERNAME', $username, TRUE);

                if($modify_id > 0)
                {
                    $search_field = array('PRJ_ID', 'CASE_ID','ADD_USERNAME');
                    $userinfo = $member_model->get_info_by_id($modify_id, $search_field);
                	
                    //设置收费标准
                    $case_id =  !empty($userinfo['CASE_ID']) ? intval($userinfo['CASE_ID']) : 0;
                    $feescale = array();
                    $project = D('Project');
                    $feescale = $project->get_feescale_by_cid($case_id);
 
                    $fees_arr = array();
                    if(is_array($feescale) && !empty($feescale) )
                    {
                        foreach($feescale as $key => $value)
                        {
                            $dw = $value['STYPE']?'%':'元';
                            if ($value['AMOUNT'][0] == '.') {
                                $value['AMOUNT'] = '0' . $value['AMOUNT'];
                            }
							if($value['SCALETYPE']==1 || $value['SCALETYPE']==2){
								
								if($value['MTYPE'])$fees_arr[$value['SCALETYPE'].'_'.$value['MTYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;
								else $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;


							}else $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;
                        }

                        //设置经办人(编辑状态：保持不变)
                        $form->setMyFieldVal('ADD_USERNAME', $userinfo['ADD_USERNAME'], TRUE);
                        //单套收费标准
                        $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
						$form->setMyField('TOTAL_PRICE_AFTER', 'LISTCHAR', array2listchar($fees_arr['1_1']), FALSE);
                        //中介佣金
                        $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
						  $form->setMyField('AGENCY_REWARD_AFTER', 'LISTCHAR', array2listchar($fees_arr['2_1']), FALSE);
                        //置业顾问佣金  外部奖励
                        $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                        //中介成交奖
                        $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                        //置业成交奖金
                        $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);
                    }
                    
                    //会员MODEL
                    $member_model = D('Member');
                    $member_info = $member_model->get_info_by_id($modify_id, array('CITY_ID', 'PRJ_ID','CASE_ID','MOBILENO'));
                    
                    if(is_array($member_info) && !empty($member_info))
                    {
                        $input_arr = array(
                                array('name' => 'CITY_ID', 'val' => $member_info['CITY_ID'], 'id' => 'CITY_ID'),
                                array('name' => 'PRJ_ID', 'val' => $member_info['PRJ_ID'], 'id' => 'PRJ_ID'),
                                array('name' => 'CASE_ID', 'val' => $member_info['CASE_ID'], 'id' => 'CASE_ID'),
                                array('name' => 'MOBILENO_HIDDEN', 'val' => $member_info['MOBILENO'], 'id' => 'MOBILENO_HIDDEN'),
                                );
                        $form = $form->addHiddenInput($input_arr);
                    }
                }
                else
                {	
                    //新增页面参数设置
                    $input_arr = array(
                            array('name' => 'ADD_UID', 'val' => $userinfo['ID'], 'class' => 'ADD_UID'),
                            array('name' => 'is_fgj_confirm', 'val' => '', 'id' => 'is_fgj_confirm'),
                            array('name' => 'is_crm_confirm', 'val' => '', 'id' => 'is_crm_confirm'),
                            array('name' => 'code', 'val' => '', 'id' => 'code'),
                            array('name' => 'ag_id', 'val' => '', 'id' => 'ag_id'),
                            array('name' => 'cp_id', 'val' => '', 'id' => 'cp_id'),
                            array('name' => 'is_from', 'val' => '', 'id' => 'is_from'),
                            array('name' => 'customer_id', 'val' => '', 'id' => 'customer_id'),
                            array('name' => 'multi_user_to_jump', 'val' => '', 'id' => 'multi_user_to_jump'),
                            array('name' => 'multi_from_to_jump', 'val' => '', 'id' => 'multi_from_to_jump'),
                            );
                    $form->addHiddenInput($input_arr);
                }
            }
            else 
            {	
            	/***列表页参数设置***/
            	//添加人
                $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                //单套收费标准
                $form->setMyField('TOTAL_PRICE', 'EDITTYPE',"1", TRUE);
				$form->setMyField('TOTAL_PRICE_AFTER', 'EDITTYPE',"1", TRUE);
				$form->setMyField('AGENCY_REWARD_AFTER', 'EDITTYPE',"1", TRUE);
				$form->setMyField('OUT_REWARD', 'EDITTYPE',"1", TRUE);
                //财务确认状态
                $form->setMyField('FINANCIALCONFIRM', 'LISTCHAR', array2listchar($status_arr['FINANCIALCONFIRM']), FALSE);
            }

            //设置会员来源
            $source_arr = $member_model->get_conf_member_source_remark();

            //编辑，根据项目ID查询项目分解目标销售方式
            if($showForm == 1)
            {   
                //设置会员来源(修改时需要当前会员项目信息，注意代码位置)
                $prj_id =  !empty($userinfo['PRJ_ID']) ? intval($userinfo['PRJ_ID']) : 0;
                //会员来源
                $source_arr = $member_model->getPrjSaleMethod($prj_id);
            }
            else if($showForm == 2)
            {
                //设置会员来源
                $source_arr = $member_model->get_conf_member_source_remark();

                //会员MODEL
                $id = !empty($_GET['ID']) ? intval($_GET['ID']) : 0;
                $member_model = D('Member');
                $member_info = $member_model->get_info_by_id($id, array('ADD_USERNAME'));

                //设置添加人
                $form->setMyFieldVal('ADD_USERNAME', $member_info['ADD_USERNAME'], TRUE);
            }

            /**
             *  如果是新增会员，有相应的保存配置，直接读取
             */
            if($showForm==3){

                //常用配置ID --- 新增会员避免重复修改
                $user_config = $member_model->get_user_config('DISMEMBER_ADD',$this->uid);
                $user_config = unserialize($user_config);

                if($user_config){
                    //设置收费标准
                    $case_id =  !empty($user_config['CASE_ID']) ? intval($user_config['CASE_ID']) : 0;
                    $feescale = D('Project')->get_feescale_by_cid($case_id);
  //var_dump( $feescale);
                    if(is_array($feescale) && !empty($feescale) )
                    {
                        foreach($feescale as $key => $value)
                        {  
						   $dw = $value['STYPE']  ? '%' : '元';
                            if ($value['AMOUNT'][0] == '.') {
                                $value['AMOUNT'] = '0' . $value['AMOUNT'];
                            }
							if($value['SCALETYPE']==1 || $value['SCALETYPE']==2){
								
								if($value['MTYPE'])$fees_arr[$value['SCALETYPE'].'_'.$value['MTYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;
								else $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;


							}else $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;
                        }

                        //设置经办人(编辑状态：保持不变)
                        $form->setMyFieldVal('ADD_USERNAME', $user_config['ADD_USERNAME'], TRUE);
                        //单套收费标准
                        $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
                        //中介佣金
                        $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
						  $form->setMyField('AGENCY_REWARD_AFTER', 'LISTCHAR', array2listchar($fees_arr['2_1']), FALSE);
                        //置业顾问佣金
                        //$form->setMyField('PROPERTY_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
						//后佣
						$form->setMyField('TOTAL_PRICE_AFTER', 'LISTCHAR', array2listchar($fees_arr['1_1']), FALSE);
						 
                        //中介成交奖
                        $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                        //置业成交奖金
                        $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);
						$form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                    }

                    //重新赋值
					$form->setMyFieldVal('AGENCY_REWARD_AFTER', $user_config['AGENCY_REWARD_AFTER'], FALSE);
					$form->setMyFieldVal('OUT_REWARD', $user_config['OUT_REWARD'], FALSE);
                    $form->setMyFieldVal('PRJ_NAME', $user_config['PRJ_NAME'], FALSE);
                    $form->setMyFieldVal('SOURCE', $user_config['SOURCE'], FALSE);
                    $form->setMyFieldVal('CERTIFICATE_TYPE', $user_config['CERTIFICATE_TYPE'], FALSE);
                    $form->setMyFieldVal('CARDSTATUS', $user_config['CARDSTATUS'], FALSE);
                    $form->setMyFieldVal('SIGNEDSUITE', $user_config['SIGNEDSUITE'], FALSE);
                    $form->setMyFieldVal('RECEIPTSTATUS', $user_config['RECEIPTSTATUS'], FALSE);
                    $form->setMyFieldVal('IS_TAKE', $user_config['IS_TAKE'], FALSE);
                    $form->setMyFieldVal('IS_SMS', $user_config['IS_SMS'], FALSE);
                    $form->setMyFieldVal('TOTAL_PRICE', $user_config['TOTAL_PRICE'], FALSE);
					$form->setMyFieldVal('TOTAL_PRICE_AFTER', $user_config['TOTAL_PRICE_AFTER'], FALSE);
                    $form->setMyFieldVal('UNPAID_MONEY', $user_config['UNPAID_MONEY'], TRUE);
                    $form->setMyFieldVal('PAID_MONEY', $user_config['PAID_MONEY'], TRUE);
                    $form->setMyFieldVal('AGENCY_REWARD', $user_config['AGENCY_REWARD'], FALSE);
                    $form->setMyFieldVal('AGENCY_DEAL_REWARD', $user_config['AGENCY_DEAL_REWARD'], FALSE);
                    $form->setMyFieldVal('PROPERTY_DEAL_REWARD', $user_config['PROPERTY_DEAL_REWARD'], FALSE);
                    $form->setMyFieldVal('LEAD_TIME', $user_config['LEAD_TIME'], FALSE);
                    $form->setMyFieldVal('DECORATION_STANDARD', $user_config['DECORATION_STANDARD'], FALSE);

					$form->setMyFieldVal('FILINGTIME', date("Y-m-d"), FALSE);
                    $input_arr = array(
                        array('name' => 'CITY_ID', 'val' => $user_config['CITY_ID'], 'id' => 'CITY_ID'),
                        array('name' => 'PRJ_ID', 'val' => $user_config['PRJ_ID'], 'id' => 'PRJ_ID'),
                        array('name' => 'CASE_ID', 'val' => $user_config['CASE_ID'], 'id' => 'CASE_ID'),
                    );
                    $form = $form->addHiddenInput($input_arr);

                    //会员来源
                    $source_arr = $member_model->getPrjSaleMethod($user_config['PRJ_ID']);
                }
            }

			//$form->setMyFieldVal('FILINGTIME', date("Y-m-d"), FALSE);
            $form->setMyField('SOURCE', 'LISTCHAR', array2listchar($source_arr), FALSE);
            $form->setMyField('TOTAL_PRICE_AFTER', 'GRIDVISIBLE', '-1', FALSE);
			$form->setMyField('RECEIPTSTATUS', 'NOTNULL', '0', FALSE);
			$form->setMyField('RECEIPTNO', 'NOTNULL', '0', FALSE);
			$form->setMyField('CARDTIME', 'NOTNULL', '0', FALSE);
			$form->setMyField('TOTAL_PRICE', 'NOTNULL', '0', FALSE);
			$form->setMyField('REWARD_STATUS', 'GRIDVISIBLE', '-1', FALSE);
			$form->setMyField('REWARD_STATUS', 'FORMVISIBLE', '-1', FALSE);
			$form->setMyField('REWARD_STATUS', 'FILTER', '-1', FALSE);
			$form->setMyField('REWARD_STATUS', 'SORT', '-1', FALSE);
			$form->setMyField('TOTAL_PRICE_AFTER', 'FILTER', '-1', FALSE);
			$form->setMyField('TOTAL_PRICE_AFTER', 'SORT', '-1', FALSE);
			$form->setMyField('AGENCY_REWARD_AFTER', 'FILTER', '-1', FALSE);
			$form->setMyField('AGENCY_REWARD_AFTER', 'SORT', '-1', FALSE);
			$form->setMyField('OUT_REWARD_STATUS', 'FILTER', '-1', FALSE);
			$form->setMyField('OUT_REWARD_STATUS', 'SORT', '-1', FALSE);
			$form->setMyField('OUT_REWARD', 'FILTER', '-1', FALSE);
			$form->setMyField('OUT_REWARD', 'SORT', '-1', FALSE);
			$form->setMyField('FILINGTIME', 'FILTER', '-1', FALSE);
			$form->setMyField('FILINGTIME', 'SORT', '-1', FALSE);

            //装修标准
            $form->setMyField('DECORATION_STANDARD', 'LISTCHAR', array2listchar($conf_zx_standard), FALSE);

            //显示保存当前设置按钮 (添加 + 编辑)
            if($showForm==1 || $showForm==3)
                $form->showSaveCfg = true;
            
            //绑定状态颜色array('1','BSTATUS') 1为类型对应ERP_STATUS_TYPE表
            //BSTATUS为需要绑定颜色的字段名
            $arr_param = array(
                            array('2','CARDSTATUS') , 
                            array('3','RECEIPTSTATUS'), 
                            array('4','INVOICE_STATUS'), 
                            array('5','FINANCIALCONFIRM')
                        );
            $form = $form->showStatusTable($arr_param);
            $children_data = array(
                                array('付款明细', U('/Member/show_pay_list')),
                                array('退款记录', U('/Member/show_refund_list')),
                                array('开票记录', U('/Member/show_bill_list'))
                            );

            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
            $formhtml =  $form->setChildren($children_data)->getResult();

            $this->assign('form', $formhtml);
            $this->assign('showForm', $showForm);
            //添加搜索条件
            $this->assign('filter_sql',$form->getFilterSql());
            //添加排序条件
            $this->assign('sort_sql',$form->getSortSql());
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
			$this->assign('case_type','fx');
            $this->display('reg_member');
        }
    }


    /**
    +----------------------------------------------------------
     * 会员项目转移
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
    */
    public function moveProject(){

        //返回结果集
        $return = array(
            'status' => false,
            'msg' => '',
            'data' => null,
        );

        $memberIdsStr = isset($_REQUEST['memberIds'])?$_REQUEST['memberIds']:0; //转移对象
        $fromCaseId = isset($_REQUEST['fromCaseId'])?$_REQUEST['fromCaseId']:0; //来源项目
        $toCaseId = isset($_REQUEST['toCaseId'])?$_REQUEST['toCaseId']:0; //目标项目

        $isCheck = isset($_REQUEST['isCheck'])?intval($_REQUEST['isCheck']):0; //是否是检查数据
        $showWindow = isset($_REQUEST['showWindow'])?$_REQUEST['showWindow']:0; //展现

        $memberIds = explode(",",$memberIdsStr);

        //数据验证
        if(empty($memberIds) || count($memberIds) < 1){
            $return['msg'] = '对不起，亲，请选择至少一条记录进行操作!';
            die(@json_encode(g2u($return)));
        }

        //项目验证
        $sql = 'select DISTINCT CASE_ID from erp_cardmember where id in(' . $memberIdsStr .  ')';
        $queryRet = D('Erp_cardmember')->query($sql);

        if(is_array($queryRet) && count($queryRet) > 1){
            $return['msg'] = '对不起，亲，请选择同一个项目进行操作!';
            die(@json_encode(g2u($return)));
        }

        //已申请报销后佣中介佣金（或已报销）不能进行项目转移
        foreach($memberIds as $member){
            $agencyStatus = M("Erp_cardmember")->where("ID=".$member)->getField('AGENCY_REWARD_STATUS');
            $agencyResultSql = "select d.pencent  from erp_commission_invoice_detail d
            LEFT JOIN erp_post_commission c on c.id = d.post_commission_id where c.card_member_id = ".$member;
            $agencyResult = D()->query($agencyResultSql);
            if(notEmptyArray($agencyResult)){
                $return['msg'] = '对不起，亲，编号为'.$member.'已申请报销后佣中介佣金（或已报销）不能进行项目转移!';
                die(@json_encode(g2u($return)));
            }
            //会员发票明细的状态为未申请或申请中），应该不允许转移
            $invoiceResultSql = "select d.invoice_status  from erp_commission_invoice_detail d
            LEFT JOIN erp_post_commission c on c.id = d.post_commission_id where c.card_member_id = ".$member;
            $invoiceResult = D()->query($invoiceResultSql);
            if(notEmptyArray($invoiceResult)){
                $return['msg'] = '对不起，亲，编号为'.$member.'已申请开票会员不能进行项目转移!';
                die(@json_encode(g2u($return)));

            }
        }

        //如果是检查数据
        if($isCheck){
            $return['status'] = true;
            die(@json_encode(g2u($return)));
        }

        //表单展现
        if($showWindow){

            $fromCaseId = $queryRet[0]['CASE_ID'];

            //获取type类型
            $caseTypePY = D('ProjectCase')->get_casetype_by_caseid($fromCaseId);
            $caseTypes = D('ProjectCase')->get_conf_case_type();
            $caseType = $caseTypes[$caseTypePY];

            $caseTypeRemark = D('ProjectCase')->get_conf_case_type_remark();

            //获取项目信息
            $sql = 'select PROJECTNAME from erp_project P left join erp_case C ON P.id = C.project_id where C.id = ' . $fromCaseId;
            $projectInfo = D()->query($sql);

            //获取项目信息
            $sql = 'SELECT C.ID,P.PROJECTNAME,P.CONTRACT FROM ERP_PROJECT P LEFT JOIN ERP_CASE C ON P.ID = C.PROJECT_ID WHERE P.PSTATUS = 3 AND P.STATUS != 2 AND PROJECTNAME IS NOT NULL ';
            $sql .= ' AND C.SCALETYPE = ' . $caseType;
            $sql .= ' AND P.CITY_ID = ' . $this->channelid;
            $allPro = D()->query($sql);

            $this->assign('memberCount',count($memberIds)); //会员数量
            $this->assign('memberIds',$memberIdsStr); //转移会员
            $this->assign('fromProjectName',$projectInfo[0]['PROJECTNAME']);  //项目名称
            $this->assign('allPro',$allPro);  //项目
            $this->assign('fromCaseId',$fromCaseId);  //来源CASEID
            $this->assign('caseType',$caseTypeRemark[$caseType]);

            $this->display('moveProject');

            exit();
        }

        //项目转移
        if(is_array($memberIds) && $fromCaseId && $toCaseId) {
            $errorStr = '';
            $memberObj = D('Member');

            $memberObj->startTrans();

            //循环处理
            foreach ($memberIds as $key => $val) {
                $convertReturn = $memberObj->convertMember($fromCaseId, $toCaseId ,$val);
                if (!$convertReturn['status']) {
                    $errorStr .= $convertReturn['msg'] . "<br />";
                }
            }

            //返回结果集
            if ($errorStr == '') {
                $return['status'] = true;
                $return['msg'] = '亲，转移项目成功!';
                $memberObj->commit();
            } else {
                $return['status'] = false;
                $return['msg'] = "对不起，亲，有如下问题导致失败:<br />" . $errorStr;
                $memberObj->rollback();
            }
        }

        die(@json_encode(g2u($return)));

    }

	 /**
    +----------------------------------------------------------
    * 退房管理
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function return_member(){
		//
		if($_REQUEST['faction']=='getfeescale'){
			$memberId_arr = array();
            $memberId_arr = $_REQUEST['memberId'];
			foreach($memberId_arr as $one)
			{
				$temp = M('Erp_cardmember')->where('ID='.$one)->find();
				$dw = D('Project')->get_feescale_by_cid_val2($temp['CASE_ID'],1,$temp['TOTAL_PRICE_AFTER'],1);
				$temp2['memberid'] = $one;
				$temp2['dw'] = $dw[0]['STYPE']==1 ?'%':' 元';
				$temp2['dw'] = g2u($temp2['dw']);
				$res[] = $temp2;
			}
			$result['status'] = 1;
			$result['data'] = $res ;
			echo json_encode($result);
			exit;

			
			
		}
		if($_REQUEST['faction'] == 'delData')
        {
            $member = D("Member");
			$temp['REWARD_STATUS']=1;
			$del_result = $member->where("ID=".$_REQUEST['ID'])->save($temp);
            if($del_result)
            {
                $info['status']  = 'success';
                $info['msg']  = g2u('删除成功');
            }
            else
            {
                $info['status']  = 'error';
                $info['msg']  = g2u('删除失败');
            }
            
            echo json_encode($info);
            exit;
        }
		if($_REQUEST['refund_method']=='mid'){
			$member = D("Member");
			$memberId_arr = array();
            $memberId_arr = $_REQUEST['memberId'];
			$rr = $member->check_member_status($memberId_arr);
			
			if($rr && $_REQUEST['re_type']=='pro'){
				$info['state']  = 0;
                $info['msg']  ='所选客户状态不能申请退房（包括：已申请结佣、已结佣、已申请退房、已退房）';
				$info['msg'] = g2u($info['msg']);
				echo json_encode($info);
				exit;
			}
			$rr2 = $member->check_member_yong($memberId_arr);
			if($rr2 && $_REQUEST['re_type']=='pro'){
				$info['state']  = 0;
                $info['msg']  ='所选客户中必须有后佣收费标准才能申请退房！';
				$info['msg'] = g2u($info['msg']);
				echo json_encode($info);
				exit;
			}
			$status = $_REQUEST['STATUS'] ? $_REQUEST['STATUS']:5;
			if($status==5 && $_REQUEST['re_type']=='tuifang'){
				$rr3 = $member->check_member_status3($memberId_arr);
				if($rr3){
					$info['state']  = 0;
					$info['msg']  ='失败,已退房的不允许在确认退房！';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
			}
			$res = $member->set_member_status($memberId_arr,$status);
			if($res){
				$info['state']  = 1;
                $info['msg']  ='执行成功';
				if($status==5){ 
					$member->submit_member_crm($memberId_arr,$this->city);
				}
			}else{
				$info['state']  = 0;
                $info['msg']  ='失败';
			}
			$info['msg'] = g2u($info['msg']);
			echo json_encode($info);
			exit;

		}else{
			$uid = intval($_SESSION['uinfo']['uid']);
			Vendor('Oms.Form');
			$form = new Form(); 
			$form = $form->initForminfo(208);
			 $form->SQLTEXT = "(";
            $form->SQLTEXT .= "SELECT DISTINCT * FROM ERP_CARDMEMBER WHERE CITY_ID = '".$this->channelid."' AND STATUS = 1 ";
            //根据状态控制删除按钮是否显示
            $form->DELCONDITION = '%REWARD_STATUS% <> 5';
            //是否有查看全部的权限
            if(!$this->p_auth_all)
            {
                $form->SQLTEXT .= " AND PRJ_ID IN "
                    . " (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' AND ISVALID = '-1' AND ERP_ID = 2) ";
            }
            
            //是否自己创建
            if(!$this->p_vmem_all)
            {
                $form->SQLTEXT .= " AND ADD_UID = $uid";
            }
			$form->SQLTEXT .= " AND　REWARD_STATUS in(4,5)  )";
			//$form->where("REWARD_STATUS in(4,5) AND CITY_ID = '".$this->channelid."'");
			$formhtml = $form->getResult();
			$this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
			$this->assign('form', $formhtml);
			$this->display('return_member');
		}

	}
	 /**
    +----------------------------------------------------------
    * 申请节佣
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function post_commission(){
		if($_REQUEST['refund_method']=='mid'){
			$commission = D("Erp_post_commission");
			$mid = $_REQUEST['memberId'];
			$member = D("Member");
			$rr = $member->check_member_status($mid,2);
			if($rr ){
				$info['state']  = 0;
                $info['msg']  ='所选客户状态不能申请结佣（包括：已申请结佣、已结佣、已申请退房、已退房）';
				$info['msg'] = g2u($info['msg']);
				echo json_encode($info);
				exit;
			}

			if(is_array($mid) && !empty($mid))
			{
				$mid_str = implode(',', $mid);
				$cond_where = "CARD_MEMBER_ID IN (".$mid_str.")";
				$cond_where2=  "ID IN (".$mid_str.")";
			}
			else 
			{   
				$midid = intval($mid);
				$cond_where = "CARD_MEMBER_ID = '".$midid."'";
				$cond_where2 = "ID = '".$midid."'";

			}
			$list = $commission->where($cond_where)->select(); 
			if(!$list){
				$mlist = D('Erp_cardmember')->where($cond_where2)->select();
				$flag =  true;
				foreach($mlist as $one){
					if(!($one['TOTAL_PRICE_AFTER'] && $one['CARDSTATUS']==3 )){
						$flag = false;
						break;
					}
				}
				if($flag){
					foreach($mid as $value){
						$temp = array();
						$temp['CARD_MEMBER_ID'] = $value;
						$temp['INVOICE_STATUS']  = 1;
						$temp['PAYMENT_STATUS']  = 1;
						$temp['POST_COMMISSION_STATUS']  = 1;
						$temp['CREATETIME'] = date('Y-m-d H:i:s');
						 
						$res = $commission->add($temp);  

						
					}
					if($res){
						$res2 = $member->set_member_status($mid,2);
						$info['state']  = 1;
						$info['msg']  ='申请成功';
					}else{
						$info['state']  = 0;
						$info['msg']  ='失败';
					}
				}else{
					$info['state']  = 0;
					$info['msg']  ='所选会员办卡状态必须为已办已签约且存在后佣取费';
				}
			}else{
				$info['state']  = 0;
                $info['msg']  ='请不要重复申请';
			}

			
			$info['msg'] = g2u($info['msg']);
			echo json_encode($info);
			exit;

		}

	}
    
    /**
    +----------------------------------------------------------
    * 注册电商办卡会员
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function apply_view_memberinfo()
    {
        $uid = intval($_SESSION['uinfo']['uid']);
        $username = strip_tags($_SESSION['uinfo']['tname']);
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        
        $mid_str = isset($_GET['memberid']) ? strip_tags($_GET['memberid']) : "";
        
        Vendor('Oms.Form');
        $form = new Form();
        
        $form = $form->initForminfo(185);
        
        if(!empty($mid_str))
        {   
            $form->SQLTEXT = "(";
            $form->SQLTEXT .= "SELECT DISTINCT * FROM ERP_CARDMEMBER WHERE CITY_ID = '".$this->channelid."' AND ID IN (".$mid_str.") AND STATUS = 1 ";
            
            //是否有查看全部的权限
            if(!$this->p_auth_all)
            {
                $form->SQLTEXT .= " AND PRJ_ID IN "
                    . " (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' AND ISVALID = '-1' AND ERP_ID = 1) ";
            }
            $form->SQLTEXT .= ")";
            
            if($showForm == '')
            {
                $log_info = array();
                $log_info['OP_UID'] = $uid;
                $log_info['OP_USERNAME'] = $username;
                $log_info['OP_LOG'] = '查看会员信息【'.$mid_str.'】';
                $log_info['ADD_TIME'] = date('Y-m-d H:i:s');
                $log_info['OP_CITY'] = $this->channelid;
                $log_info['OP_IP'] = GetIP();
                $log_info['TYPE'] = 1;

                member_opreate_log($log_info);
            }
        }
        else
        {
            $form->where('1= 0');
        }
        
        //实例化会员MODEL
    	$member_model = D('Member');
        
        //设置证件类型
        $certificate_type_arr = $member_model->get_conf_certificate_type();
        $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR', array2listchar($certificate_type_arr), FALSE);
        //
        //添加人
        $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        
        $form =  $form->getResult();
        $this->assign('form', $form);
        $this->display('apply_view_memberinfo');
    }
    
    
    //操作日志
    public function show_operate_log()
    {
        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(186);
        $formHtml =  $form->where('OP_CITY='.$this->channelid)->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 保存上次检索的结果
        $this->assign('form', $formHtml);
        $this->display('show_operate_log');
    }
    
    
    
    /**
    +----------------------------------------------------------
    * 显示付款明细列表
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function show_pay_list()
    {   
        $mid = isset($_POST['MID']) ? intval($_POST['MID']) : 0;
        $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        $faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $uid = intval($_SESSION['uinfo']['uid']);
        //电商会员支付
	    $income_from = 1;
        $member_pay = D('MemberPay');
		$member_model = D('Member');
		//判断是否只有后佣
		if($_REQUEST['showForm']==3){
			$caseinfo = array();
			$case_model = D('ProjectCase');
            $caseinfo = $case_model->get_info_by_pid($_REQUEST['parentchooseid'], 'fx');
			$case_id = !empty($caseinfo[0]['ID']) ? intval($caseinfo[0]['ID']) : 0;
			$project = D('Project');
            $dtsf_front= $project->get_feescale_by_cid($case_id, null, null,0)?1:0;
			$mem = $member_model->where("ID=$mid")->find();
			$is_dis = $mem['IS_DIS'];
			if( $is_dis==2 && !$mem['TOTAL_PRICE'] ){
				$result['status'] = 0;
                $result['msg'] =  g2u('该分销项目只有“后佣”取费模式，无法新增付款明细！');
                echo json_encode($result);
				//js_alert('该分销项目只有“后佣”取费模式，无法新增付款明细！');
                exit;
			}
		}
        
        //添加支付信息
        if($this->isPost() && !empty($_POST) && $faction == 'saveFormData' && $id == 0)
        {   
            //查询会员信息
            //$member_model = D('Member');
            $member_info = array();
            $search_field = array( 'CASE_ID', 'TOTAL_PRICE', 
                'REDUCE_MONEY', 'UNPAID_MONEY', 'FINANCIALCONFIRM');
            $member_info = $member_model->get_info_by_id($mid, $search_field);
            $trade_money = floatval($this->_post('TRADE_MONEY'));
            
            if($trade_money > $member_info['UNPAID_MONEY'])
            {
                $result['status'] = 0;
                $result['msg'] =  g2u('添加失败，支付金额大于未缴纳金额');
                echo json_encode($result);
                exit;
            }
            
            $pay_info = array();
            $pay_info['MID'] = $this->_post('MID');
            $pay_info['REAL_NAME'] = $this->_post('REAL_NAME');
            $pay_info['PAY_TYPE'] = $this->_post('PAY_TYPE');
            $pay_info['TRADE_MONEY'] = $this->_post('TRADE_MONEY');
            $pay_info['ORIGINAL_MONEY'] = $pay_info['TRADE_MONEY'];
            
            if($pay_info['PAY_TYPE'] == 1)
            {
                $pay_info['MERCHANT_NUMBER'] = trim($this->_post('MERCHANT_NUMBER'));
                $pay_info['RETRIEVAL'] = trim($this->_post('RETRIEVAL'));
                $pay_info['CVV2'] = trim($this->_post('CVV2'));
                
                if($pay_info['MERCHANT_NUMBER'] == '' || 
                    $pay_info['RETRIEVAL'] == '' || 
                    $pay_info['CVV2'] == '')
                {
                    $result['status'] = 0;
                    $result['msg'] = '添加失败,POS机付款方式\'6位检索号\',\'卡号后四位\',\'商户编号\'必填';
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }
            }
            
            //新增明细状态
            $status_arr = $member_pay->get_conf_status();
            $pay_info['STATUS'] = $status_arr['wait_confirm'];
            $pay_info['TRADE_TIME'] = $this->_post('TRADE_TIME');
            $pay_info['ADD_UID'] = $uid;
            
            $insert_id = $member_pay->add_member_info($pay_info);
            
            if($insert_id > 0)
            {
            	//更新会员已缴纳和未缴纳金额
            	$paid_money = $member_pay->get_sum_pay($mid);
            	
            	//减免金额
                $reduce_money = !empty($member_info['REDUCE_MONEY']) ? 
                                    floatval($member_info['REDUCE_MONEY']) : 0;
                //单套收费标准
                $total_price = !empty($member_info['TOTAL_PRICE']) ? 
                    floatval($member_info['TOTAL_PRICE']) : 0;
                
            	//支付多笔支付类型更改为综合
            	$paid_money > $pay_info['TRADE_MONEY'] ? 
                    $update_arr['PAY_TYPE'] = 4 : $update_arr['PAY_TYPE'] = $pay_info['PAY_TYPE'];
            	$update_arr['PAID_MONEY'] = $paid_money;
                //FINANCIALCONFIRM财务确认状态（1未确认、2部分确认、3已确认）
                $confirm_status = $member_model->get_conf_confirm_status();
                $member_info['FINANCIALCONFIRM'] == $confirm_status['confirmed'] ? 
                        $update_arr['FINANCIALCONFIRM'] = $confirm_status['part_confirmed'] : '';
            	$update_arr['UNPAID_MONEY'] = $total_price > 0 ? 
                                              $total_price - $paid_money - $reduce_money : 0;
            	$member_model->update_info_by_id($mid, $update_arr);
                
                //添加收益信息到收益表
                $income_info['CASE_ID'] = $member_info['CASE_ID'];
                $income_info['ENTITY_ID'] = $mid;
                $income_info['PAY_ID'] = $insert_id;
                $income_info['INCOME_FROM'] = $income_from;
                $income_info['INCOME'] = $pay_info['TRADE_MONEY'];
                $income_info['INCOME_REMARK'] = '';
                $income_info['ADD_UID'] = $uid;
                $income_info['OCCUR_TIME'] = $pay_info['TRADE_TIME'];
                
                $income_model = D('ProjectIncome');
                $income_model->add_income_info($income_info);
                
                $result['status'] = 1;
                $result['msg'] = '添加成功';
            }
            else
            {	
            	$result['status'] = 0;
            	$result['msg'] = '添加失败！';
            }
            
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        else if($this->isPost() && !empty($_POST) && $faction == 'saveFormData' && $id > 0)
        {	
            //修改支付信息
            $pay_info = array();
            $pay_info['MID'] = intval($this->_post('MID'));
            $pay_info['REAL_NAME'] = addslashes(strip_tags($this->_post('REAL_NAME')));
            $pay_info['PAY_TYPE'] = intval($this->_post('PAY_TYPE'));
            $pay_info['TRADE_MONEY'] = floatval($this->_post('TRADE_MONEY'));
            $pay_info['ORIGINAL_MONEY'] = floatval($pay_info['TRADE_MONEY']);
            $pay_info['RETRIEVAL'] = addslashes(strip_tags($this->_post('RETRIEVAL')));
            $pay_info['CVV2'] = addslashes(strip_tags($this->_post('CVV2')));
            $pay_info['MERCHANT_NUMBER'] = addslashes(strip_tags($this->_post('MERCHANT_NUMBER')));
            $pay_info['TRADE_TIME'] = $this->_post('TRADE_TIME');
            
            //查询会员信息
            $member_model = D('Member');
            $member_info = array();
            $search_field = array( 'CASE_ID', 'TOTAL_PRICE', 'PAID_MONEY',
                'REDUCE_MONEY', 'UNPAID_MONEY', 'FINANCIALCONFIRM');
            $member_info = $member_model->get_info_by_id($pay_info['MID'], $search_field);
            
            $paid_money = $member_pay->get_sum_pay($mid);
            
            //付款限制
            if($paid_money - floatval($this->_post('TRADE_MONEY_OLD')) + $pay_info['TRADE_MONEY'] > ($member_info['TOTAL_PRICE'] - $member_info['REDUCE_MONEY']) )
            {
                $result['status'] = 0;
                $result['msg'] =  g2u('修改失败，支付金额大于应交金额');
                echo json_encode($result);
                exit;
            }


            //判断是否是大额付款(商户编号)
            if($pay_info['PAY_TYPE'] ==1)
            {
                if ($member_model->isLargeMerchant($pay_info['MERCHANT_NUMBER'], $this->city_id)) {
                    if (strlen($pay_info['CVV2']) < 10) {
                        $result['status'] = 0;
                        $result['msg'] = g2u("付款方式为POS机的付款明细，商户编号选择大额，请写全卡号！");
                        echo json_encode($result);
                        exit;
                    }
                } else {
                    if (strlen($pay_info['CVV2']) != 4) {
                        $result['status'] = 0;
                        $result['msg'] = g2u("付款方式为POS机的付款明细，请编号卡号后四位！");
                        echo json_encode($result);
                        exit;
                    }
                }
            }
            
            $member_pay = D('MemberPay');

            //财务已经确认，付款金额不给修改
            $pay_old_info = $member_pay->get_payinfo_by_id($id,array("STATUS,TRADE_MONEY,PAY_TYPE,MERCHANT_NUMBER"));
            if($pay_old_info[0]['STATUS'] == 1){
                if($pay_info['TRADE_MONEY'] != $pay_old_info[0]['TRADE_MONEY']){
                    $result['status'] = 0;
                    $result['msg'] = g2u("对不起，该笔金额财务已经确认，不能修改‘交易金额’！");
                    die(json_encode($result));
                }

                if($pay_info['PAY_TYPE'] != $pay_old_info[0]['PAY_TYPE']){
                    $result['status'] = 0;
                    $result['msg'] = g2u("对不起，该笔金额财务已经确认，付款方式不能修改！");
                    die(json_encode($result));
                }

                if($pay_info['MERCHANT_NUMBER'] != $pay_old_info[0]['MERCHANT_NUMBER']){
                    $result['status'] = 0;
                    $result['msg'] = g2u("对不起，该笔金额财务已经确认，商户编号不能修改！");
                    die(json_encode($result));
                }
            }

            $up_num = $member_pay->update_info_by_id($id, $pay_info);
            
            if($up_num > 0)
            {	
            	//更新会员已缴纳和未缴纳金额
            	$paid_money = $member_pay->get_sum_pay($mid);
            	 
            	//查询会员信息
            	$member_model = D('Member');
            	$member_info = array();
            	$search_field = array('CASE_ID', 'TOTAL_PRICE', 'REDUCE_MONEY');
            	$member_info = $member_model->get_info_by_id($mid, $search_field);
            	 
            	//减免金额
            	$reduce_money = !empty($member_info['REDUCE_MONEY']) ?
            			floatval($member_info['REDUCE_MONEY']) : 0;
            	
            	//单套收费标准
            	$total_price = !empty($member_info['TOTAL_PRICE']) ? 
            				floatval($member_info['TOTAL_PRICE']) : 0;

            	//支付多笔支付类型更改为综合
            	$paid_money > $pay_info['TRADE_MONEY'] ? $update_arr['PAY_TYPE'] = 4 : ($update_arr['PAY_TYPE'] = $pay_info['PAY_TYPE']);
            	
            	$update_arr['PAID_MONEY'] = $paid_money;
            	$update_arr['UNPAID_MONEY'] = $total_price - $paid_money - $reduce_money;
            	$member_model->update_info_by_id($mid, $update_arr);

                /****修改收益信息****/
                $income_info['INCOME'] = $pay_info['TRADE_MONEY'];
                $income_info['INCOME_REMARK'] = '会员支付修改';
                
                $income_model = D('ProjectIncome');
                $income_model->update_income_info($income_info, $member_info['CASE_ID'], $mid, $id, $income_from);
            	
                $result['status'] = 1;
                $result['msg'] = '修改成功！';
            }
            else
            {
                $result['status'] = 0;
                $result['msg'] = '修改失败！';
            }
            
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        else if($faction == 'delData')
        {
            $del_detail_result = FALSE;
            $del_id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
            $mid = isset($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;

            if($del_id > 0)
            {   
                $member_pay = D('MemberPay');
                $del_result = $member_pay->del_pay_detail_by_id($del_id);
                
                if($del_result > 0)
                {
                    //更新会员已缴纳和未缴纳金额
                    $paid_money = $member_pay->get_sum_pay($mid);
                    
                    //查询会员信息
                    $member_model = D('Member');
                    $member_info = array();
                    $search_field = array('CASE_ID', 'TOTAL_PRICE', 'REDUCE_MONEY');
                    $member_info = $member_model->get_info_by_id($mid, $search_field);
                    
                    //减免金额
                    $reduce_money = !empty($member_info['REDUCE_MONEY']) ?
                                        floatval($member_info['REDUCE_MONEY']) : 0;
                    
                    //单套收费标准
                    $total_price = !empty($member_info['TOTAL_PRICE']) ? 
                                        floatval($member_info['TOTAL_PRICE']) : 0;
                    
                    $update_arr['PAID_MONEY'] = $paid_money;
                    $update_arr['UNPAID_MONEY'] = $total_price - $paid_money - $reduce_money;
                    if($paid_money == 0)
                    {
                        $update_arr['PAY_TYPE'] = 0;
                    }
                    $member_model->update_info_by_id($mid, $update_arr);
                    
                    //删除收益
                    $income_model = D('ProjectIncome');   
                    $income_model->delete_income_info($member_info['CASE_ID'], $mid, $del_id, $income_from);
                }
            }
            
            if($del_result)
            {
                $info['status']  = 'success';
                $info['msg']  = g2u('删除成功');
            }
            else
            {
                $info['status']  = 'error';
                $info['msg']  = g2u('删除失败');
            }
            
            echo json_encode($info);
            exit;
        }
        else 
        {
            Vendor('Oms.Form');
            $form = new Form();
            $form = $form->initForminfo(121)->where("STATUS != 4");
            $member = D('Member');
            $m_id = intval($this->_get('parentchooseid'));
            
            $member_info = array();
            $member_info = $member->get_info_by_id($m_id, array('CITY_ID', 'REALNAME'));
            $member_name = !empty($member_info['REALNAME']) ? $member_info['REALNAME'] : '';
            $form = $form->setMyFieldVal('REAL_NAME', $member_name, 0);
            
            //设置会员姓名
            $form = $form->setMyField('MID', 'LISTSQL', "SELECT ID, REALNAME FROM ERP_CARDMEMBER", FALSE);
            
            //设置付款方式
	        $member_pay = D('MemberPay');
	        $pay_arr = $member_pay->get_conf_pay_type();
            array_pop($pay_arr);//去掉综合付款
	        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), FALSE);
            
            //设置付款明细状态
            $status_arr = $member_pay->get_conf_status_remark();
            $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($status_arr), FALSE);
            
            //设置付款明细退款状态
            $refund_status_arr = $member_pay->get_conf_refund_status_remark();
            $form = $form->setMyField('REFUND_STATUS', 'LISTCHAR', array2listchar($refund_status_arr), TRUE);
	        
	        //设置商户编号
	        $merchant_arr = array();
	        $city_id = !empty($member_info['CITY_ID']) ? intval($member_info['CITY_ID']) : 0;
	        $merchant_info = array();
	        $merchant_info = M('erp_merchant')->where("CITY_ID = '".$city_id."'")->select();
	        if(is_array($merchant_info) && !empty($merchant_info))
	        {
	        	foreach($merchant_info as $key => $value)
	        	{	
	        		$large_str = '';
	        		$value['IS_LARGE'] == 1 ? $large_str .= '[大额]' : '';
	        		$merchant_arr[$value['MERCHANT_NUMBER']] = $value['MERCHANT_NUMBER'].$large_str;
	        	}
	        }
            
            //根据状态控制删除按钮是否显示
            $form->DELCONDITION = '%STATUS% == 0';
	        $form = $form->setMyField('MERCHANT_NUMBER', 'LISTCHAR', array2listchar($merchant_arr), FALSE);
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->showPayListOptions);  // 按钮前置
            $formHtml = $form->getResult();
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
            $this->assign('form',$formHtml);
            $this->display('pay_list'); 
        }
    }
    
    
    /**
    +----------------------------------------------------------
    * 添加会员付款明细信息
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function add_pay_info()
    { 
        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(121)->getResult();
        $this->assign('form',$form);
        $this->display('add_pay'); 
    }
    
    
    /**
    +----------------------------------------------------------
    * 显示退款明细列表
    +----------------------------------------------------------
    * @param none
     * 
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function show_refund_list()
    {	
    	$id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
    	$faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
    	$showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
    	$member_refund = D('MemberRefund');
    	
    	//修改退款数据
    	if($faction == 'saveFormData' && $id > 0)
    	{
    		$up_num = 0;
    		$refund_money = isset($_POST['REFUND_MONEY']) ? 
    							intval($_POST['REFUND_MONEY']) : 0;
            
            $trade_money = isset($_POST['TRADE_MONEY']) ? 
    							intval($_POST['TRADE_MONEY']) : 0;
            
            $refund_money_total = isset($_POST['REFUND_MONEY_TOTAL']) ? 
                    intval($_POST['REFUND_MONEY_TOTAL']) : 0;
            
            if($refund_money > ($trade_money - $refund_money_total))
            {
                $result['status'] =  0;
                $result['msg'] =  g2u('修改失败，退款金额不能超过交易金额减去累计退款金额。');
                
                echo json_encode($result);
                exit;
            }
            
    		if($refund_money > 0)
    		{
    			$update_arr['REFUND_MONEY'] = $refund_money;
    			$up_num = $member_refund->update_refund_detail_by_id($id, $update_arr);
    		}
    		
    	    if($up_num > 0)
            {   
                $result['status'] = 1;
                $result['msg'] =  g2u('修改成功');
            }
            else
            {
                $result['status'] = 0;
                $result['msg'] =  g2u('修改失败');
            }
            
            echo json_encode($result);
            exit;
    	}
        else if($faction == 'delData')
        {   
            $del_id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
            
            $refund_details = $member_refund->get_refund_detail_by_id($del_id, array('PAY_ID', 'REFUND_STATUS'));
            
            if(empty($refund_details))
            {
                $result['status'] =  'error';
                $result['msg'] =   g2u('删除失败，数据异常');
                echo json_encode($result);
                exit;
            }
            
            $no_sub_status = intval($refund_status_arr['refund_no_sub']);
            if($no_sub_status != $refund_details['REFUND_STATUS'])
            {
                $result['status'] =  'error';
                $result['msg'] =  g2u('删除失败，未加入退款审核单之前的退款申请才可以删除');
                echo json_encode($result);
                exit;
            }
            
            //更新付款信息
            $member_pay = D('MemberPay');
            $conf_refund_status = $member_pay->get_conf_refund_status();
            $update_arr['REFUND_STATUS'] = $conf_refund_status['no_refund'];
            $update_pay = $member_pay->update_info_by_id($refund_details['PAY_ID'], $update_arr);
            
            //删除退款信息（更新状态）
            $up_num = $member_refund->del_refund_detail_by_id($del_id);
            
            if($up_num > 0)
            {
                $result['status'] =  'success';
                $result['msg'] =  g2u('删除成功');
            }
            else
            {
                $result['status'] =  'error';
                $result['msg'] =  g2u('删除失败');
            }
            
            echo json_encode($result);
            exit;
        }
    	else
    	{
	        Vendor('Oms.Form');
	        $form = new Form();
	        
            $refund_status_arr = $member_refund->get_conf_refund_status();
            $cond_where = " REFUND_STATUS != '".$refund_status_arr['refund_delete']."'";
            
	        $form = $form->initForminfo(122)->where($cond_where);
	        //设置会员编号
	        $form = $form->setMyField('MID', 'LISTSQL','SELECT ID,REALNAME FROM ERP_CARDMEMBER', TRUE);
	        //设置付款方式
	        $member_pay = D('MemberPay');
	        $pay_arr = $member_pay->get_conf_pay_type();
	        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE);
	        //设置操作人
	        $form = $form->setMyField('APPLY_UID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', TRUE);
	        
	        //获取退款明细退款状态
	        $refund_status_remark_arr = array();
	        $refund_status_remark_arr = $member_refund->get_conf_refund_status_remark();
	        $form = $form->setMyField('REFUND_STATUS', 'LISTCHAR', 
	        		array2listchar($refund_status_remark_arr), TRUE);
            
            //根据状态控制删除按钮是否显示()
            $no_sub_status = intval($refund_status_arr['refund_no_sub']);
            
            $form->DELCONDITION = '%REFUND_STATUS% == '.$no_sub_status;
            $form->EDITCONDITION = '%REFUND_STATUS% == '.$no_sub_status;

            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->showRefundListOptions);  // 按钮前置
	        $formHtml = $form->getResult();
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
	        $this->assign('form',$formHtml);
	        $this->display('refund_list');
    	}
    }
    
    
    /**
    +----------------------------------------------------------
    * 显示开票记录列表
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function show_bill_list()
    {
        Vendor('Oms.Form');
        $form = new Form();
        $form = $form->initForminfo(123)->where("INVOICE_TYPE = 2");
        //设置会员编号
	    $form = $form->setMyField('CONTRACT_ID','LISTSQL','SELECT ID,REALNAME FROM ERP_CARDMEMBER', TRUE);
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->showBillListOptions);  // 按钮前置
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
        $this->assign('form',$formHtml);
        $this->display('bill_list');
    }
    
    
    /**
    +----------------------------------------------------------
    * 申请开票
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function apply_invoice ()
    {   
        $info = array();
        $id_arr = $_POST['memberId'];
        $nj_no_pass_num = 0;
        $no_pass_num = 0;
        
        if(is_array($id_arr) && !empty($id_arr))
        {   
            $member_info = array();
            $search_field = array('CITY_ID','REALNAME','CARDSTATUS','INVOICE_STATUS','ROOMNO','UNPAID_MONEY');
            $member_model = D('Member');
            $invoice_status = $member_model->get_conf_invoice_status();
            $member_info = $member_model->get_info_by_ids($id_arr, $search_field);
            
            if(is_array($member_info) && !empty($member_info))
            {  
                foreach ($member_info as $key => $value)
                {   
                    #南京的特殊情况
                    if($value['CITY_ID'] == 1)
                    {
                        if($value['CARDSTATUS'] != 3 || 
                            $value['INVOICE_STATUS'] != $invoice_status['no_invoice'] || 
                            trim($value['ROOMNO']) == '' || 
                               $value['UNPAID_MONEY'] != 0)
                        {   
                            $nj_no_pass_num ++;
                        }
                    }
                    else
                    {
                        if($value['CARDSTATUS'] != 3 || 
                            $value['INVOICE_STATUS'] != $invoice_status['no_invoice'] || 
                            $value['UNPAID_MONEY'] != 0)
                        {   
                            $no_pass_num ++;
                        }
                    }
                }
            }
            else 
            {
                $info['state']  = 0;
                $info['msg'] = g2u('操作失败，会员信息异常');
                echo json_encode($info);
                exit;
            }
            
            if($nj_no_pass_num > 0)
            {   
                $info['state']  = 0;
                $msg = '申请开票失败,['.$nj_no_pass_num.']条数据不符合开票条件，'
                        . '<br>请检查办卡会员信息是否符合以下条件：'
                        . '<br>1、办卡状态为已办已签约；'
                        . '<br>2、发票状态为未开；'
                        . '<br>3、楼栋房号已填写；'
                        . '<br>4、不存在未缴纳金额；'
                        . '<br>5、付款明细财务均已确认；'
                        . '<br>6、付款明细不在申请退款。';
                $info['msg'] = g2u($msg);
                echo json_encode($info);
                exit;
            }
            
            if($no_pass_num > 0)
            {   
                $info['state']  = 0;
                $msg = '申请开票失败,['.$no_pass_num.']条数据不符合开票条件，'
                        . '<br>请检查办卡会员信息是否符合以下条件：'
                        . '<br>1、办卡状态为已办已签约；'
                        . '<br>2、发票状态为未开；'
                        . '<br>3、不存在未缴纳金额'
                        . '<br>4、付款明细财务均已确认；'
                        . '<br>5、付款明细不在申请退款。';
                $info['msg'] = g2u($msg);
                echo json_encode($info);
                exit;
            }
            
            //查询会员信息付款明细是否都已经被财务确认
            $pay_list_no_confrim_num = 0;
            $member_pay_model = D('MemberPay');
            //支付明细确认状态
            $conf_pay_status = $member_pay_model->get_conf_status();
            //支付明细退款状态
            $conf_pay_refund_status = $member_pay_model->get_conf_refund_status();
            
            //支付明细信息
            $pay_list_info = $member_pay_model->get_payinfo_by_mid($id_arr, array('STATUS', 'REFUND_STATUS'));
            //未确认明细个数
            $pay_list_no_confrim_num = 0;
            //退款明细个数
            $pay_list_refund_num = 0;
            if(is_array($pay_list_info) && !empty($pay_list_info))
            {
                foreach($pay_list_info as $key => $value)
                {   
                    //未确认
                    if($value['STATUS'] == $conf_pay_status['wait_confirm'])
                    {
                        $pay_list_no_confrim_num ++;
                    }
                    
                    //退款
                    if($value['REFUND_STATUS'] != $conf_pay_refund_status['no_refund'])
                    {
                        $pay_list_refund_num ++;
                    }
                }
                
                if($pay_list_no_confrim_num > 0)
                {
                    $info['state']  = 0;
                    $info['msg'] = g2u('申请开票失败，存在未确认的付款明细信息');
                    echo json_encode($info);
                    exit;
                }
                
                if($pay_list_refund_num > 0)
                {
                    $info['state']  = 0;
                    $info['msg'] = g2u('申请开票失败，存在申请退款的付款明细');
                    echo json_encode($info);
                    exit;
                }
            }
            else
            {
                $info['state']  = 0;
                $info['msg'] = g2u('无支付明细信息的会员无法申请开票');
                echo json_encode($info);
                exit;
            }
            
            $result = FALSE;
            $result = $member_model->update_info_by_id($id_arr, array('INVOICE_STATUS' => $invoice_status['apply_invoice']));
            
            if( $result > 0)
            {
                $info['state']  = 1;
                $info['msg']  = '开票申请成功';
            }
            else
            {   
                $info['state']  = 0;
                $info['msg']  = '开票失败!';
            }
        }
        else
        {   
            $info['state']  = 0;
            $info['msg']  = '请至少选择一条记录!';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
    }
    
    
    /**
    +----------------------------------------------------------
    * 根据手机号码获取用户在CRM/FGJ系统中的信息
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function get_minfo_by_telno()
    {
        //ajax根据手机和楼盘编号获取用户信息
        if(isset($_POST['action_type']) && $_POST['action_type'] == 'ajax_userinfo_by_telno')
        {   
            $project_id = intval($_POST['project_id']);
            $telno = strip_tags($_POST['telno']);
            $pro_listid = isset($_POST['pro_listid']) ? intval($_POST['pro_listid']) : 0;
            
            $cond_where = "ID = ".intval($_SESSION['uinfo']['city']);
            $city_info = M('erp_city')->field('PY')->where($cond_where)->find();
            $user_city_py = !empty($city_info) ? strip_tags($city_info['PY']) : '';
            $userinfo = get_userinfo_by_pid_telno($project_id, $telno, $pro_listid, $user_city_py);

            echo json_encode($userinfo);
            exit;
        }
    }
    
    
    /**
     +----------------------------------------------------------
     * 现场发放现金
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function locale_granted()
    {   
        $uid = intval($_SESSION['uinfo']['uid']);
        $username = strip_tags($_SESSION['uinfo']['tname']);
        $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
    	$faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
    	$showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        
        Vendor('Oms.Form');
        $form = new Form();
        
        $form = $form->initForminfo(169);
        $form->SQLTEXT = "(SELECT * FROM ERP_LOCALE_GRANTED WHERE CITY_ID = '".$this->channelid."' AND PRJ_ID IN "
            . " (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' AND ISVALID = '-1' AND ERP_ID = 1) )";
        
        $grant_model = D('LocaleGranted');
    	if($faction == 'saveFormData' && $id > 0)
    	{	
            $grant_info = array();
            $grant_info['MONEY'] = floatval($_POST['MONEY']);
            $grant_info['NUM'] = floatval($_POST['NUM']);
            $grant_info['ISFUNDPOOL'] = intval($_POST['ISFUNDPOOL']);
            $grant_info['ISKF'] = intval($_POST['ISKF']);
            $grant_info['ATTACHMENTS'] = u2g($_POST['ATTACHMENTS']);
            $grant_info['UPDATETIME'] = date('Y-m-d H:i:s');
            
            if($grant_info['ATTACHMENTS'] == '')
            {
                $result['status'] = 0;
    		$result['msg'] = g2u('修改失败，附件必须添加！');
                echo json_encode($result);
                exit;
            }
            
            $update_num = $grant_model->update_info_by_id($id, $grant_info);
            
            if($update_num > 0)
            {
                $result['status'] = 1;
                $result['msg'] = g2u('修改成功');
            }
            else
            {
                $result['status'] = 0;
                $result['msg'] = g2u('修改失败');
            }

            echo json_encode($result);
            exit;
        }
        else if($faction == 'saveFormData' && $id == 0)
        {   
            $grant_info = array();
            $grant_info['PRJ_ID'] = intval($_POST['PRJ_ID']);
                        
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_pid($grant_info['PRJ_ID'], 'ds');
            $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
            
            if($case_id <= 0)
            {
                $result['status'] = 0;
                $result['msg'] = g2u('项目无电商业务，无法添加现场发放记录');
                echo json_encode($result);
                exit;
            }
            
            //项目MODEL
            $project_model = D('Project');
            $project_info = $project_model->get_info_by_id($grant_info['PRJ_ID'], array('BSTATUS'));
            $ds_status = !empty($project_info[0]['BSTATUS']) ? intval($project_info[0]['BSTATUS']) : 0;
            
            if($ds_status != 2)
            {
                $result['status'] = 0;
                $result['msg'] = g2u('项目电商业务不在执行中，无法添加现场发放记录');
                echo json_encode($result);
                exit;
            }
            
            $is_overtop = is_overtop_payout_limit($case_id);
            if($is_overtop)
            {
                $result['status'] = 0;
    		$result['msg'] = g2u('成本已经超过垫资额度或超出费用预算（总费用>开票收入*付现成本率），无法添加现场发放记录');
                echo json_encode($result);
                exit;
            }
            
            $grant_info['CASE_ID'] = $case_id;
            $grant_info['CITY_ID'] = intval($_POST['CITY_ID']);
            $grant_info['MONEY'] = floatval($_POST['MONEY']);
            $grant_info['NUM'] = floatval($_POST['NUM']);
            $grant_info['ADD_UID'] = $uid;
            $grant_info['ISFUNDPOOL'] = intval($_POST['ISFUNDPOOL']);
            $grant_info['ISKF'] = intval($_POST['ISKF']);
            $grant_info['OCCUR_TIME'] = $_POST['OCCUR_TIME'];
            $grant_info['ATTACHMENTS'] = u2g($_POST['ATTACHMENTS']);
            $grant_info['CREATTIME'] = date('Y-m-d H:i:s');
            
            if($grant_info['ATTACHMENTS'] == '')
            {
                $result['status'] = 0;
    		    $result['msg'] = g2u('添加失败，附件必须添加！');
                echo json_encode($result);
                exit;
            }
            
            $insert_id = $grant_model->add_grant_info($grant_info);
            
            if($insert_id > 0)
            {
                $result['status'] = 1;
                $result['msg'] = g2u('添加成功');
            }
            else
            {
                $result['status'] = 0;
                $result['msg'] = g2u('添加失败');
            }

            echo json_encode($result);
            exit;
        }
        else 
        { 
            //详情页
            if( $showForm > 0 )
            {  
                $modify_id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
                
                //编辑页面项目名称显示，城市参数、项目ID隐藏；
                if( $modify_id > 0 )
                {  
                    $serach_field = array('PRJ_ID', 'CITY_ID');
                    $grand_info = $grant_model->get_info_by_id($modify_id, $serach_field);
                    
                    if(is_array($grand_info) && !empty($grand_info))
                    {
                        $prj_id = intval($grand_info[0]['PRJ_ID']);
                        $city_id = intval($grand_info[0]['CITY_ID']);
                        
                        $input_arr = array(
                            array('name' => 'PRJ_ID', 'val' => $prj_id, 'id' => 'PRJ_ID'),
                            array('name' => 'CITY_ID', 'val' => $city_id, 'id' => 'CITY_ID'),
                        );
                        $form =  $form->addHiddenInput($input_arr);
                        
                        $project_info = array();
                        $project_model = D('Project');         
                        $project_info = $project_model->get_info_by_id($prj_id);
                        
                        if(is_array($project_info) && !empty($project_info))
                        {
                            $form->setMyFieldVal('PRJ_NAME', $project_info[0]['PROJECTNAME']);
                        }
                    }
                }
            }
            else
            {
                //项目名称
                $form = $form->setMyField('PRJ_ID', 'LISTSQL', "SELECT ID, PROJECTNAME FROM ERP_PROJECT", TRUE);
                
                //根据状态控制删除按钮是否显示
                $form->DELCONDITION = '%REIM_STATUS% == 0';
                $form->EDITCONDITION = '%REIM_STATUS% == 0';
            }
            
            //发放人
            if($showForm == 3 )
            {
                $form->setMyField('ADD_UID', 'EDITTYPE', 22);
                $form->setMyField('ADD_UID', 'LISTCHAR', array2listchar(array($uid => $username)), TRUE);
                $form->setMyFieldVal('ADD_UID', $uid);
                //$form->setMyField('OCCUR_TIME', 'READONLY', '-1');
                $form->setMyFieldVal('OCCUR_TIME', date('Y-m-d', time()));
            }
            else
            {
                $form = $form->setMyField('ADD_UID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
            }
            
            //报销状态
            $conf_reim_status = $grant_model->get_conf_reim_status_remark();
            $form->setMyField( 'REIM_STATUS', 'LISTCHAR' , array2listchar($conf_reim_status), TRUE);
        }

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->localeGrantedOptions);  // 按钮前置
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->assign('form', $formHtml);
        $this->assign('showForm', $showForm);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
        $this->display('locale_granted');
    }
    
    
    /**
     +----------------------------------------------------------
     * 商户编号管理
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function merchant_manage()
    {
    	Vendor('Oms.Form');
    	$form = new Form();
    	$form = $form->initForminfo(147);
    	//设置城市
    	$form = $form->setMyField('CITY_ID','LISTSQL','SELECT ID,NAME FROM ERP_CITY WHERE ISVALID = -1', FALSE);
    	$formHtml = $form->getResult();
    	$this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
    	$this->display('merchant_manage');
    }
    
    
    /**
     +----------------------------------------------------------
     * 会员费用报销管理
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function reim_manage()
    {	
        //报销MODEL
        $reim_type_model = D('ReimbursementType');
        $reim_list_model = D('ReimbursementList');
        $reim_detail_model = D('ReimbursementDetail');
        
    	$uid = intval($_SESSION['uinfo']['uid']);
    	$city = $this->channelid;
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
        
        if($faction == 'delData')
        {   
            $list_id = intval($_GET['ID']);

            $del_list_result = FALSE;
            $del_detail_result = FALSE;

            $memberInfo = $reim_detail_model->get_detail_info_by_listid($list_id,array("BUSINESS_ID","CASE_ID","TYPE","STATUS"));

            if($memberInfo){
                foreach($memberInfo as $k1=>$v1){
                    if($v1['STATUS']==0) {
                        $reim_type = $v1['TYPE'];
                        $memberIds = $v1['BUSINESS_ID'] . ",";
                    }
                }
                $memberIds = trim($memberIds,",");
            }

            if(in_array($reim_type,array(3,4,6,9,10,11,12,21,25))) {
                //更新状态
                $status_up_array = array();
                switch (intval($reim_type)) {
                    case 3:
                        $status_up_array['AGENCY_REWARD_STATUS'] = 1;
                        break;
                    case 4:
                        $status_up_array['AGENCY_DEAL_REWARD_STATUS'] = 1;
                        break;
                    case 6:
                        $status_up_array['PROPERTY_DEAL_REWARD_STATUS'] = 1;
                        break;
					case 9:
						$status_up_array['AGENCY_REWARD_STATUS'] = 1;
						break;
					case 10:
						$status_up_array['AGENCY_DEAL_REWARD_STATUS'] = 1;
						break;
					case 12:
						$status_up_array['PROPERTY_DEAL_REWARD_STATUS'] = 1;
						break;
					case 21:
						$status_up_array['OUT_REWARD_STATUS'] = 1;
						break;
					case 25:
						$status_up_array['OUT_REWARD_STATUS'] = 1;
						break;
                }

                $reim_status_up = M("Erp_cardmember")->where("ID IN ({$memberIds})")->save($status_up_array);
            }

            //如果报销ID > 0 && $reim_status_up执行结果不为false
            if($list_id > 0 && ($reim_status_up!==false))
            {   
                $del_list_result = $reim_list_model->del_reim_list_by_ids($list_id);
                
                if($del_list_result)
                {
                    $del_detail_result = $reim_detail_model->del_reim_detail_by_listid($list_id);
                }
                //var_dump($del_detail_result);
                //更新发放记录报销状态为未申请
                $locale_granted_model = D('LocaleGranted');
                $up_num_granted = $locale_granted_model->sub_granted_to_reim_not_apply_by_reim_listid($list_id);

                //更新关联借款
                $loan_model = D('Loan');
                $up_num_loan = $loan_model->cancleRelatedLoan($list_id);
            }
            
            if($del_list_result > 0 && $del_detail_result > 0)
            {
                $info['status']  = 'success';
                $info['msg']  = g2u('删除成功');
            }
            else if(!$del_detail_result)
            {   
                $info['status']  = 'error';
                $info['msg']  = g2u('报销明细删除失败');
            }
            else 
            {   
                $info['status']  = 'error';
                $info['msg']  = g2u('删除失败');
            }
            
            echo json_encode($info);
    		exit;
        }
        
        Vendor('Oms.Form');
    	$form = new Form();
        
        //报销申请单状态
        $list_status = $reim_list_model->get_conf_reim_list_status();
    	$cond_where = "APPLY_UID = '".$uid."' AND CITY_ID = '".$city."' "
                . "AND TYPE IN (3,4,5,6,7,9,10,11,12,21,25) AND STATUS != '".$list_status['reim_deleted']."' ";
    	$form = $form->initForminfo(176)->where($cond_where);

        $form->SQLTEXT = '(select a.*,b.LOANMONEY  from ERP_REIMBURSEMENT_LIST a
        left join (select  APPLICANT,sum(UNREPAYMENT) as LOANMONEY from ERP_LOANAPPLICATION where STATUS IN(2,6) AND CITY_ID = '. $city . ' group by APPLICANT) b
        on a.APPLY_UID = b.APPLICANT )';
        
        if($this->_merge_url_param['flowId'] > 0)
        {
            //工作流入口编辑权限
            $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);

            if($flow_edit_auth)
            {
                //允许编辑
                //$form->EDITABLE = -1;
                $form->GABTN = "<a id='related_my_loan' href='javascript:;' class='btn btn-info btn-sm'>关联借款</a>";
                $form->ADDABLE = '0';
            }
            else
            {
                $form->DELCONDITION = '1==0';
                $form->EDITCONDITION = '1==0';
                $form->ADDABLE = '0'; 
                $form->GABTN = '';
            }
        }
        else
        {
            //根据状态控制删除按钮是否显示
            $form->DELABLE = -1;
            $form->DELCONDITION = '%STATUS% == 0';
            //$form->EDITABLE = -1;
            $form->EDITCONDITION = '%STATUS% == 0';
            $form->GABTN = "<a id='sub_reim_apply' href='javascript:;' class='btn btn-info btn-sm'>提交报销申请</a>  "
                    . "<a id='related_my_loan' href='javascript:;' class='btn btn-info btn-sm'>关联借款</a>"
                    ."<a id = 'show_flow_step'  href='javascript:;' class='btn btn-info btn-sm'>超额报销流程图</a>";

        }
        $form->EDITABLE = 0;
    	//设置报销单类型
    	$type_arr = $reim_type_model->get_reim_type();
    	$form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);
    	
    	//设置报销单状态
    	$status_arr = $reim_list_model->get_conf_reim_list_status_remark();
    	$form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($status_arr), FALSE);
    	
    	//详情页
    	if($showForm > 0)
    	{
    		//审核人
    		$form = $form->setMyField('REIM_UID', 'LISTSQL', "SELECT ID, NAME FROM ERP_USERS", TRUE);
    	}
		
    	$children_data = array(
    			array('报销明细', U('Member/reim_detail_manage', 'fromTab=22')),
    			array('关联借款', U('Loan/related_loan'))
    	);
        
        
    	$form =  $form->setChildren($children_data);
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->localeGrantedOptions);  // 按钮前置
    	$formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
    	$this->assign('form', $formHtml);
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
    	$this->display('reim_manage');
    }
    
    
    /**
     +----------------------------------------------------------
     * 报销明细
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function reim_detail_manage()
    {	
    	$list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
    	$uid = intval($_SESSION['uinfo']['uid']);
    	$city = $this->channelid;
    	$faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
    	$showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';
    	$id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
        //操作行为
        $act = isset($_REQUEST['act'])?trim($_REQUEST['act']):'';
        
    	//报销申请单MODEL
    	$reim_list_model = D('ReimbursementList');
        //报销MODEL
        $reim_detail_model = D('ReimbursementDetail');
        //报销类型
        $reim_type_model = D('ReimbursementType');

        if($act=='changeStatus'){
            $return = array(
                'msg'=>'操作失败',
                'data'=>null,
                'state'=>0,
            );

            $idArr = $_REQUEST['idArr'];
            $isFundPool = isset($_REQUEST['isfundpool'])?intval($_REQUEST['isfundpool']):0;

            $ids = implode(",",$idArr);
            $sql = "UPDATE ERP_REIMBURSEMENT_DETAIL SET ISFUNDPOOL = $isFundPool WHERE ID IN($ids)";
            $updateRet = $reim_detail_model->query($sql);

            if($updateRet!==false){
                $return['msg'] = '操作成功';
                $return['state'] = 1;
            }

            die(json_encode(g2u($return)));
        }
        
    	Vendor('Oms.Form');
    	$form = new Form();
        
        if($faction == 'saveFormData' && $id > 0)
    	{	
            $update_arr = array();
            $update_arr['ISFUNDPOOL'] = intval($_POST['ISFUNDPOOL']);
            $update_arr['ISKF'] = intval($_POST['ISKF']);
            $update_arr['MONEY'] = floatval($_POST['MONEY']);
            $update_num = $reim_detail_model->update_reim_detail_by_id($id, $update_arr);
            
            if($update_num > 0)
    		{
                $aReimDetail = $reim_detail_model->where('ID = ' . $id)->find();;
                $this->syncAssocTable($aReimDetail, $update_arr);  // 同步到发放记录里

                $reim_list_info = $reim_detail_model->get_detail_info_by_id($id, array('LIST_ID'));
                $reim_list_id = !empty($reim_list_info) ? $reim_list_info[0]['LIST_ID'] : 0;

                $total_amount = $reim_detail_model->get_sum_total_money_by_listid($reim_list_id);
                $up_list_result = $reim_list_model->update_reim_list_amount($reim_list_id, $total_amount, 'cover');
                        
    			$result['status'] = 1;
    			$result['msg'] = g2u('修改成功');
    		}
    		else
    		{
    			$result['status'] = 0;
    			$result['msg'] = g2u('修改失败');
    		}
            
    		echo json_encode($result);
    		exit;
        }
        else
        {
            //根据LIST查询报销单类型
            $list_info = $reim_list_model->get_info_by_id($list_id, array('TYPE', 'STATUS'));
            $reim_type = !empty($list_info[0]['TYPE']) ? intval($list_info[0]['TYPE']) : 0;
            $reim_list_status = !empty($list_info[0]['STATUS']) ? intval($list_info[0]['STATUS']) : 0;
            if(in_array($reim_type, array(3,4,5,6,9,10,11,12,21,25)) )
            {
                $form_id = 177;
            }
            else if( $reim_type == 7)
            {
                $form_id = 178;
            }

            $detail_status = $reim_detail_model->get_conf_reim_detail_status();
            $cond_where = "LIST_ID = '".$list_id."' AND STATUS != '".$detail_status['reim_detail_deleted']."'";
            $form = $form->initForminfo($form_id)->where($cond_where);
            
            //证件号码
            $form->setMyField('CERTIFICATE_NO', 'ENCRY', '4,13', FALSE);
            
            //根据状态控制编辑删除按钮是否显示
            $conf_reim_list_status = $reim_list_model->get_conf_reim_list_status();
            if($conf_reim_list_status['reim_list_no_sub'] == $reim_list_status)
            {   
                $form->EDITABLE = '-1';
                $form->DELABLE = '-1';
            }

            if($faction == 'delData')
            {
                $id = intval($_GET['ID']);

                //数据验证
                //删除明细剩余金额不能小于借款金额
                if(D("Loan")->checkDelReim($list_id,$id)){
                    $info['status']  = 'error';
                    $info['msg']  = g2u('对不起，您此报销单关联的借款金额已大于报销金额，删除失败!');
                    die(json_encode($info));
                }

                $del_detail_result = FALSE;
                $up_list_result = FALSE;

                $memberInfo = $reim_detail_model->get_detail_info_by_cond(" ID=$id AND STATUS=0 ",array("BUSINESS_ID","CASE_ID","TYPE"));
                $memberId = $memberInfo[0]['BUSINESS_ID'];
                $reim_type = $memberInfo[0]['TYPE'];

                if(in_array($reim_type,array(3,4,6,9,10,11,12,21,25))) {
                    //更新状态
                    $status_up_array = array();
                    switch (intval($reim_type)) {
                        case 3:
                            $status_up_array['AGENCY_REWARD_STATUS'] = 1;
                            break;
                        case 4:
                            $status_up_array['AGENCY_DEAL_REWARD_STATUS'] = 1;
                            break;
                        case 6:
                            $status_up_array['PROPERTY_DEAL_REWARD_STATUS'] = 1;
                            break;
						case 9:
                            $status_up_array['AGENCY_REWARD_STATUS'] = 1;
                            break;
						case 10:
                            $status_up_array['AGENCY_DEAL_REWARD_STATUS'] = 1;
                            break;
						case 12:
                            $status_up_array['PROPERTY_DEAL_REWARD_STATUS'] = 1;
                            break;
						case 21:
                            $status_up_array['OUT_REWARD_STATUS'] = 1;
                            break;
						case 25:
                            $status_up_array['OUT_REWARD_STATUS'] = 1;
                            break;
                    }
                    $reim_status_up = M("Erp_cardmember")->where("ID = {$memberId}")->save($status_up_array);
                }
 
                if($id > 0 && $reim_status_up !== false)
                {   
                    $del_detail_result = $reim_detail_model->del_reim_detail_by_id($id);

                    if($del_detail_result)
                    {	
                        $reim_list_info= $reim_detail_model->get_detail_info_by_id($id, array('LIST_ID','BUSINESS_ID'));
                        $reim_list_id = !empty($reim_list_info) ? intval($reim_list_info[0]['LIST_ID']) : 0;
                    
                        $total_amount = $reim_detail_model->get_sum_total_money_by_listid($reim_list_id);
                        $up_list_result = $reim_list_model->update_reim_list_amount($reim_list_id, $total_amount, 'cover');
                        
                        $grant_id = !empty($reim_list_info) ? intval($reim_list_info[0]['BUSINESS_ID']) : 0;
                        
                        //更新发放记录报销状态为未申请
                        $locale_granted_model = D('LocaleGranted');
                        $up_num_granted = $locale_granted_model->sub_granted_to_reim_not_apply_by_id($grant_id);
                    }
                }

                if($del_detail_result > 0 && $up_list_result > 0)
                {
                    $info['status']  = 'success';
                    $info['msg']  = g2u('删除成功');
                }
                else if(!$up_list_result)
                {
                    $info['status']  = 'error';
                    $info['msg']  = g2u('报销申请单金额更新失败');
                }
                else
                {
                    $info['status']  = 'error';
                    $info['msg']  = g2u('删除失败');
                }

                echo json_encode($info);
                exit;
            }

            if(in_array($reim_type, array(3,4,5,6,9,10,11,12,21,25)) )
            {
                $member_model = D('Member');

                //设置会员来源
                $source_arr = $member_model->get_conf_member_source_remark();
                $form = $form->setMyField('SOURCE', 'LISTCHAR',
                        array2listchar($source_arr), FALSE);

                //经办人
                $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);

                //是否资金池
                //$form->setMyFieldVal('ISFUNDPOOL',0,FALSE);

                //设置证件类型
                $certificate_type_arr = $member_model->get_conf_certificate_type();
                $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR',
                        array2listchar($certificate_type_arr), FALSE);

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
            }
            else if( $reim_type == 7)
            {   
                //项目名称
                $form = $form->setMyField('PRJ_ID', 'LISTSQL', 'SELECT ID, PROJECTNAME FROM ERP_PROJECT', TRUE);

                //设置报销单类型
                $type_arr = $reim_type_model->get_reim_type();
                $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);

                //报销明细状态数组
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
                $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);
            }
        }
		 $form->setMyField('AGENCY_NAME','READONLY',-1,false);
        $form->setMyField('INPUT_TAX','GRIDVISIBLE',0,false);
        $form->setMyField('INPUT_TAX','FORMVISIBLE',0,false);

        if($reim_type==3 || $reim_type==4){
            $form->GABTN='<a href = "javascript:;" id= "changeStatus" class="btn btn-info btn-sm">变更资金池状态</a>';
        }

        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
        $this->display('reim_detail_manage');
    }
    
    
    /**
    +----------------------------------------------------------
    * 会员导出
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function export_member()
    {	
    	$uid = intval($_SESSION['uinfo']['uid']);
    	
        Vendor('phpExcel.PHPExcel');
        if($_REQUEST['case_type']=='fx'){
			$Exceltitle = '分销客户导出';
		}else{
			$Exceltitle = '电商会员导出';
		}
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(g2u($Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(16);//默认行宽
        $objActSheet->getDefaultColumnDimension()->setWidth(16);//默认列宽
        $objActSheet->getDefaultStyle()->getFont()->setName(g2u('宋体'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//自动换行
        $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objActSheet->getRowDimension('1')->setRowHeight(40);
        $objActSheet->getRowDimension('2')->setRowHeight(26);
        
        $i = 1;
        $objActSheet->getStyle('A'.$i.':AN'.$i)->getFont()->setName('Candara' );
        $objActSheet->getStyle('A'.$i.':AN'.$i)->getFont()->setSize(12);
        $objActSheet->getStyle('A'.$i.':AN'.$i)->getFont()->setBold(true);
        
        //合并单元格
        $objActSheet->setCellValue('A'.$i, g2u('会员信息'));
        $objActSheet->mergeCells( 'A1:Z1');
        $objActSheet->setCellValue('AA'.$i, g2u('支付信息'));
        $objActSheet->mergeCells( 'AA1:AI1');
        
        $i = $i +1;
        $objActSheet->getStyle('A'.$i.':AN'.$i)->getFont()->setName('Candara' );
        $objActSheet->getStyle('A'.$i.':AN'.$i)->getFont()->setSize(10);
        $objActSheet->getStyle('A'.$i.':AN'.$i)->getFont()->setBold(true);
        
        $objActSheet->setCellValue('A'.$i, g2u('编号'));
        $objActSheet->setCellValue('B'.$i, g2u('会员姓名'));
        $objActSheet->setCellValue('C'.$i, g2u('购房人手机号'));
        $objActSheet->setCellValue('D'.$i, g2u('证件类型'));
        $objActSheet->setCellValue('E'.$i, g2u('证件号码'));
        $objActSheet->setCellValue('F'.$i, g2u('项目名称'));
        $objActSheet->setCellValue('G'.$i, g2u('楼栋房号'));
        $objActSheet->setCellValue('H'.$i, g2u('会员来源'));
        $objActSheet->setCellValue('I'.$i, g2u('办卡日期'));
        $objActSheet->setCellValue('J'.$i, g2u('办卡状态'));
        $objActSheet->setCellValue('K'.$i, g2u('收据状态'));
        $objActSheet->setCellValue('L'.$i, g2u('收据编号'));
        $objActSheet->setCellValue('M'.$i, g2u('发票状态'));
        $objActSheet->setCellValue('N'.$i, g2u('发票编号'));
        $objActSheet->setCellValue('O'.$i, g2u('开票时间'));
        $objActSheet->setCellValue('P'.$i, g2u('财务确认状态'));
        $objActSheet->setCellValue('Q'.$i, g2u('付款方式'));
        $objActSheet->setCellValue('R'.$i, g2u('已缴金额'));
        $objActSheet->setCellValue('S'.$i, g2u('未缴纳金额'));
        $objActSheet->setCellValue('T'.$i, g2u('直销人员'));
        if($_REQUEST['case_type']=='fx'){
			$objActSheet->setCellValue('U'.$i, g2u('前佣收费标准'));
			$objActSheet->setCellValue('V'.$i, g2u('后佣收费标准'));
			$objActSheet->setCellValue('W'.$i, g2u('是否带看'));
			$objActSheet->setCellValue('X'.$i, g2u('房屋总价'));
			$objActSheet->setCellValue('Y'.$i, g2u('经纪公司'));
			$objActSheet->setCellValue('Z'.$i, g2u('交付时间'));
			$objActSheet->setCellValue('AA'.$i, g2u('装修标准'));
			$objActSheet->setCellValue('AB'.$i, g2u('经办人'));
			$objActSheet->setCellValue('AC'.$i, g2u('备注'));
			$objActSheet->setCellValue('AD'.$i, g2u('支付方式'));
			$objActSheet->setCellValue('AE'.$i, g2u('支付金额'));
			$objActSheet->setCellValue('AF'.$i, g2u('刷卡年'));
			$objActSheet->setCellValue('AG'.$i, g2u('刷卡月'));
			$objActSheet->setCellValue('AH'.$i, g2u('刷卡日'));
			$objActSheet->setCellValue('AI'.$i, g2u('POS号码'));
			$objActSheet->setCellValue('AJ'.$i, g2u('卡号后四位'));
			$objActSheet->setCellValue('AK'.$i, g2u('退款金额'));
			$objActSheet->setCellValue('AL'.$i, g2u('退款时间'));
            $objActSheet->setCellValue('AM'.$i, g2u('认购时间'));
            $objActSheet->setCellValue('AN'.$i, g2u('签约时间'));

		}else{
			$objActSheet->setCellValue('U'.$i, g2u('单套收费标准'));
		
			$objActSheet->setCellValue('V'.$i, g2u('是否带看'));
			$objActSheet->setCellValue('W'.$i, g2u('房屋总价'));
			$objActSheet->setCellValue('X'.$i, g2u('经纪公司'));
			$objActSheet->setCellValue('Y'.$i, g2u('交付时间'));
			$objActSheet->setCellValue('Z'.$i, g2u('装修标准'));
			$objActSheet->setCellValue('AA'.$i, g2u('经办人'));
			$objActSheet->setCellValue('AB'.$i, g2u('备注'));
			$objActSheet->setCellValue('AC'.$i, g2u('支付方式'));
			$objActSheet->setCellValue('AD'.$i, g2u('支付金额'));
			$objActSheet->setCellValue('AE'.$i, g2u('刷卡年'));
			$objActSheet->setCellValue('AF'.$i, g2u('刷卡月'));
			$objActSheet->setCellValue('AG'.$i, g2u('刷卡日'));
			$objActSheet->setCellValue('AH'.$i, g2u('POS号码'));
			$objActSheet->setCellValue('AI'.$i, g2u('卡号后四位'));
			$objActSheet->setCellValue('AJ'.$i, g2u('退款金额'));
			$objActSheet->setCellValue('AK'.$i, g2u('退款时间'));
		}
		//if($_REQUEST['case_type']=='fx') $objActSheet->setCellValue('AK'.$i, g2u('后佣收费标准'));


        //获取搜索条件
        $filter_sql = isset($_GET['Filter_Sql'])?trim($_GET['Filter_Sql']):'';
        //获取排序条件
        $sort_sql = isset($_GET['Sort_Sql'])?trim($_GET['Sort_Sql']):'';


        /***查询需要导出的会员信息***/
        $search_field = array('ID', 'PRJ_NAME', 'REALNAME', 'MOBILENO', 'SOURCE','ROOMNO', 
        					'CARDSTATUS', 'CARDTIME', 'INVOICE_STATUS', 'INVOICE_NO',
                            'CONFIRMTIME', 'RECEIPTSTATUS','RECEIPTNO' , 'DIRECTSALLER',
        					'FINANCIALCONFIRM', 'ADD_USERNAME', 'CERTIFICATE_TYPE', 
        					'CERTIFICATE_NO', 'PAY_TYPE', 'PAID_MONEY', 'UNPAID_MONEY',
        					'TOTAL_PRICE', 'TOTAL_PRICE', 'LEAD_TIME', 'IS_TAKE','SUBSCRIBETIME','SIGNTIME',
                            'DECORATION_STANDARD', 'NOTE', 'HOUSETOTAL','AGENCY_NAME','TOTAL_PRICE_AFTER,CASE_ID');
        
        $search_str = implode(',', $search_field);
        $result = array();
		$query_sql = "SELECT $search_str FROM ERP_CARDMEMBER WHERE "
                . "CITY_ID = '".$this->channelid."' AND STATUS = 1 AND PRJ_ID IN ".
				"(SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' "
                . "AND ISVALID = '-1' AND ERP_ID = 1) ";


        $query_sql = "SELECT $search_str FROM ERP_CARDMEMBER WHERE "
            . "CITY_ID = '".$this->channelid."' AND STATUS = 1 ";


        //是否有查看全部的权限
        if(!$this->p_auth_all)
        {
            $query_sql .= " AND PRJ_ID IN ".
                "(SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = '".$uid."' "
                . "AND ISVALID = '-1' AND ERP_ID = 1) ";
        }

        //是否自己创建
        if(!$this->p_vmem_all)
        {
            $query_sql  .= " AND ADD_UID = $uid ";
        }
		if($_REQUEST['case_type']=='fx'){
			$query_sql  .= " AND IS_DIS=2 ";

		}else {
			$query_sql  .= " AND IS_DIS=1 ";
		}

        if($filter_sql)
            $query_sql .= $filter_sql;

        if($sort_sql)
            $query_sql .= ' ' . $sort_sql;

        //获取数据
        $result = M('')->query($query_sql);
        
        if(is_array($result))
        {	
            if(count($result) > 1000)
            {
                $this->error('最多下载1000条数据，查询的数据超过1000条');
            }
            
            $member_model = D('Member');
            //获取会员办卡、开票、发票状态
            $status_arr = $member_model->get_conf_all_status_remark();

            //设置付款方式
            $member_pay_model = D('MemberPay');
            $pay_arr = $member_pay_model->get_conf_pay_type();

            //设置证件类型
            $certificate_type_arr = $member_model->get_conf_certificate_type();

            //交付标准
            $conf_zx_standard_arr = $member_model->get_conf_zx_standard();
            
            //会员来源
            $conf_member_source = $member_model->get_conf_member_source_remark();

            $member_ids = array();
            foreach($result as $key => $value)
            {
               $member_ids[] =  $value['ID'];
            }
            
            //查询所有会员付款信息
        	$member_pay_info = $member_pay_model->get_payinfo_by_mid($member_ids);
            $pay_num = count($member_pay_info);
            
            $member_pay_arr = array();
            for($j = 0 ; $j < $pay_num; $j ++)
            {
                $member_pay_arr[$member_pay_info[$j]['MID']][] = $member_pay_info[$j];
            }
            
            $i = $i +1;
            foreach($result as $k => $r)
            {
                $objActSheet->setCellValue('A'.$i, $r['ID']);
                $objActSheet->setCellValue('B'.$i, g2u($r['REALNAME']) );
                
                $objActSheet->setCellValue('D'.$i, g2u($certificate_type_arr[$r['CERTIFICATE_TYPE']]));
//                if($this->p_auth_all)
//                {
//                    $objActSheet->setCellValue('C'.$i, " ".substr_replace($r['MOBILENO'], '****', 5, 4));
//                    $objActSheet->setCellValue('E'.$i, " ".substr_replace($r['CERTIFICATE_NO'], '**********', 5, 9));
//                }
//                else
//                {
//                    $objActSheet->setCellValue('C'.$i, " ".substr_replace($r['MOBILENO'], '****', 5, 4));
//                    $objActSheet->setCellValue('E'.$i, " ".substr_replace($r['CERTIFICATE_NO'], '**********', 5, 9));
//                }
                $objActSheet->setCellValue('C'.$i, g2u($r['MOBILENO']));
                $objActSheet->setCellValue('E'.$i, g2u("=\"$r[CERTIFICATE_NO]\""));
                $objActSheet->setCellValue('F'.$i, g2u($r['PRJ_NAME']) );
                $objActSheet->setCellValue('G'.$i, g2u($r['ROOMNO']));
                $objActSheet->setCellValue('H'.$i, g2u($conf_member_source[$r['SOURCE']]));//会员来源
                $objActSheet->setCellValue('I'.$i, oracle_date_format($r['CARDTIME']));
                $objActSheet->setCellValue('J'.$i, g2u($status_arr['CARDSTATUS'][$r['CARDSTATUS']]));
                $objActSheet->setCellValue('K'.$i, g2u($status_arr['RECEIPTSTATUS'][$r['RECEIPTSTATUS']]));
                $objActSheet->setCellValueExplicit('L'.$i, $r['RECEIPTNO']);
                $objActSheet->setCellValue('M'.$i, g2u($status_arr['INVOICE_STATUS'][$r['INVOICE_STATUS']]));
                $objActSheet->setCellValue('N'.$i, $r['INVOICE_NO']);
                $objActSheet->setCellValue('O'.$i, oracle_date_format($r['CONFIRMTIME']));
                $objActSheet->setCellValue('P'.$i, g2u($status_arr['FINANCIALCONFIRM'][$r['FINANCIALCONFIRM']]));
                $objActSheet->setCellValue('Q'.$i, g2u($pay_arr[$r['PAY_TYPE']]));
                $objActSheet->setCellValue('R'.$i, $r['PAID_MONEY']);
                $objActSheet->setCellValue('S'.$i, $r['UNPAID_MONEY']);
                $objActSheet->setCellValue('T'.$i, g2u($r['DIRECTSALLER']));
                $objActSheet->setCellValue('U'.$i, $r['TOTAL_PRICE']);
				$is_take = $r['IS_TAKE'] == 1 ? '是' : '否';
				if($_REQUEST['case_type']=='fx'){
					$onetemp = D('Project')->get_feescale_by_cid_val2($r['CASE_ID'],1,$r['TOTAL_PRICE_AFTER'],1);
					if($onetemp)$bfb = $onetemp[0]['STYPE'] == 1 ? '%' : '元';
					else $bfb= '';
					 

					$objActSheet->setCellValue('V'.$i, g2u($r['TOTAL_PRICE_AFTER'].$bfb));
					$objActSheet->setCellValue('W'.$i, g2u($is_take));  //是否带看
					$objActSheet->setCellValue('X'.$i, $r['HOUSETOTAL']);
					$objActSheet->setCellValue('Y'.$i, g2u($r['AGENCY_NAME']));
					$objActSheet->setCellValue('Z'.$i, oracle_date_format($r['LEAD_TIME']));
					$objActSheet->setCellValue('AA'.$i, g2u($conf_zx_standard_arr[$r['DECORATION_STANDARD']]));
					$objActSheet->setCellValue('AB'.$i, g2u($r['ADD_USERNAME']));
					$objActSheet->setCellValue('AC'.$i, g2u($r['NOTE']));
                    $objActSheet->setCellValue('AM'.$i, oracle_date_format($r['SUBSCRIBETIME']));
                    $objActSheet->setCellValue('AN'.$i, oracle_date_format($r['SIGNTIME']));
					//查询付款记录
					$member_pay_num = count($member_pay_arr[$r['ID']]);
					
					if($member_pay_num > 0)
					{
						for($start_row = 0 ; $start_row < $member_pay_num; $start_row ++ )
						{
							$objActSheet->setCellValue('AD'.($i + $start_row),  g2u($pay_arr[$member_pay_arr[$r['ID']][$start_row]['PAY_TYPE']]));
							$objActSheet->setCellValue('AE'.($i + $start_row), $member_pay_arr[$r['ID']][$start_row]['TRADE_MONEY']);
							//日期格式
							$format_time = strtotime(oracle_date_format($member_pay_arr[$r['ID']][$start_row]['TRADE_TIME']));
							$objActSheet->setCellValue('AF'.($i + $start_row), date('Y', $format_time));
							$objActSheet->setCellValue('AG'.($i + $start_row), date('m', $format_time));
							$objActSheet->setCellValue('AH'.($i + $start_row), date('d', $format_time));
							$objActSheet->setCellValue('AI'.($i + $start_row), " ".$member_pay_arr[$r['ID']][$start_row]['MERCHANT_NUMBER']);
							$objActSheet->setCellValue('AJ'.($i + $start_row), $member_pay_arr[$r['ID']][$start_row]['CVV2']);
							$objActSheet->setCellValue('AK'.($i + $start_row), $member_pay_arr[$r['ID']][$start_row]['REFUND_MONEY']);
							$objActSheet->setCellValue('AL'.($i + $start_row), oracle_date_format($member_pay_arr[$r['ID']][$start_row]['REFUND_TIME']));

						}
						
						$i += $member_pay_num - 1;
					}
				}else{
					$objActSheet->setCellValue('V'.$i, g2u($is_take));  //是否带看
					$objActSheet->setCellValue('W'.$i, $r['HOUSETOTAL']);
					$objActSheet->setCellValue('X'.$i, g2u($r['AGENCY_NAME']));
					$objActSheet->setCellValue('Y'.$i, oracle_date_format($r['LEAD_TIME']));
					$objActSheet->setCellValue('Z'.$i, g2u($conf_zx_standard_arr[$r['DECORATION_STANDARD']]));
					$objActSheet->setCellValue('AA'.$i, g2u($r['ADD_USERNAME']));
					$objActSheet->setCellValue('AB'.$i, g2u($r['NOTE']));
					
					//查询付款记录
					$member_pay_num = count($member_pay_arr[$r['ID']]);
					
					if($member_pay_num > 0)
					{
						for($start_row = 0 ; $start_row < $member_pay_num; $start_row ++ )
						{
							$objActSheet->setCellValue('AC'.($i + $start_row),  g2u($pay_arr[$member_pay_arr[$r['ID']][$start_row]['PAY_TYPE']]));
							$objActSheet->setCellValue('AD'.($i + $start_row), $member_pay_arr[$r['ID']][$start_row]['TRADE_MONEY']);
							//日期格式
							$format_time = strtotime(oracle_date_format($member_pay_arr[$r['ID']][$start_row]['TRADE_TIME']));
							$objActSheet->setCellValue('AE'.($i + $start_row), date('Y', $format_time));
							$objActSheet->setCellValue('AF'.($i + $start_row), date('m', $format_time));
							$objActSheet->setCellValue('AG'.($i + $start_row), date('d', $format_time));
							$objActSheet->setCellValue('AH'.($i + $start_row), " ".$member_pay_arr[$r['ID']][$start_row]['MERCHANT_NUMBER']);
							$objActSheet->setCellValue('AI'.($i + $start_row), $member_pay_arr[$r['ID']][$start_row]['CVV2']);
							$objActSheet->setCellValue('AJ'.($i + $start_row), $member_pay_arr[$r['ID']][$start_row]['REFUND_MONEY']);
							$objActSheet->setCellValue('AK'.($i + $start_row), oracle_date_format($member_pay_arr[$r['ID']][$start_row]['REFUND_TIME']));
						}
						
						$i += $member_pay_num - 1;
					}
				}
               
                
				if($_REQUEST['case_type']=='fx'){
					//$objActSheet->setCellValue('AK'.$i, g2u($r['TOTAL_PRICE_AFTER']));
				}
                
                $objActSheet->getRowDimension($i)->setRowHeight(24);
                
                $i++;
                
//                if($i > 1000)
//                {
//                    exit;
//                }
            }


            $log_info = array();
            $log_info['OP_UID'] = $this->uid;
            $log_info['OP_USERNAME'] = $this->tname;
            $log_info['OP_LOG'] = '导出会员信息【'.implode(",",$member_ids).'】';
            $log_info['ADD_TIME'] = date('Y-m-d H:i:s');
            $log_info['OP_CITY'] = $this->channelid;
            $log_info['OP_IP'] = GetIP();
            $log_info['TYPE'] = 1;

            member_opreate_log($log_info);
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
        header("Content-Disposition:attachment;filename=".$Exceltitle.date("YmdHis").".xlsx");
        header("Content-Transfer-Encoding:binary");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');               
        exit;
    }
    
    
    /**
    +----------------------------------------------------------
    * 会员导入
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function import_member()
    {

        //会员导入
        if(!empty($_POST['opreate']) && $_POST['opreate'] == 'import_data' && !empty($_FILES))
        {
            //返回结果
            $return = array(
                'state'=>0,
                'msg'=>'',
                'data'=>null,
            );

            //错误字符串
            $error_str = '';

            //获取会员信息 EXCEL
            $tmp_file = $_FILES ['upfile'] ['tmp_name'];
            $file_types = explode ( ".", $_FILES ['upfile'] ['name'] );
            $file_type = $file_types [count ( $file_types ) - 1];
            $caseType = trim($_REQUEST['case_type']); //业务类型

            //业务说明
            $caseInfo = $caseType=='ds'?'电商':'分销';

            /*判别是不是.xls文件，判别是不是excel文件*/
            if (strtolower ( $file_type ) != "xlsx" && strtolower ( $file_type ) != "xls")             
            {
                $return['msg'] = g2u('对不起，仅支持xlsx/xls格式文件！');
                die(json_encode($return));
            }
            
            //判断大小
            Vendor('phpExcel.PHPExcel');
            Vendor('phpExcel.IOFactory.php');
            Vendor('phpExcel.Reader.Excel5.php');
            
            $PHPExcel = new PHPExcel();
            $PHPReader = new PHPExcel_Reader_Excel2007();
            
            $objPHPExcel = $PHPReader->load($tmp_file, 'UTF-8');
            /**读取excel文件中的第一个工作表*/
            $currentSheet = $objPHPExcel->getSheet(0);
            /**取得最大的列号*/
            $allColumn = $currentSheet->getHighestColumn();
            /**取得一共有多少行*/
            $allRow = $currentSheet->getHighestRow();

            //判断支持最大记录数
            if($allRow>102){
                $return['msg'] = g2u('对不起，最大支持导入100条记录！');
                die(json_encode($return));
            }

            $insert_member_data = array();
            $insert_pay_data = array();
            
            //项目信息特殊处理
            //权限项目(只允许导入有项目权限的项目)
            $project = D('Project');
            $permission_project = $project->get_permission_project_by_uid($this->uid , $caseType);

            if(is_array($permission_project) && !empty($permission_project))
            {
                foreach ($permission_project as $key => $value)
                {
                    $project_id_arr[] = $value['PRO_ID'];
                }

                $cond['ID']  = array('IN', $project_id_arr);
            }
            if($caseType=='ds') {
                $cond['BSTATUS'] = array('IN', array(2, 3, 4));
            }else{
                $cond['MSTATUS'] = array('IN', array(2, 3, 4));
            }
            //城市
            $cond['CITY_ID']  = array('EQ', $this->city_id);

            $project_info = $project->field('ID,PROJECTNAME')->where($cond)->select();

            $project_num = count($project_info);

            if($project_num == 0)
            {
                $return['msg'] = g2u('对不起，导入失败，当前用户没用' . $caseInfo . '项目权限！');
                die(json_encode($return));
            }
            
            $project_info_flip = array();
            foreach($project_info as $key => $value)
            {
                $project_info_flip[$value['PROJECTNAME']] = $value['ID']; 
            }
            
            $member_model = D('Member');
            $member_pay_model = D('MemberPay');
            
            //交付标准
            $conf_zx_standard_arr = $member_model->get_conf_zx_standard();
            //会员状态
            $status_arr = $member_model->get_conf_all_status_remark('CARDSTATUS');
            //收据状态
            $receipt_status_arr = $member_model->get_conf_all_status_remark('RECEIPTSTATUS');
            //发票状态
            $invoice_status_arr = $member_model->get_conf_invoice_status_remark();
            //需要反转的配置数组信息
            $cfg_certificate_type_flip = array_flip($member_model->get_conf_certificate_type());
            $cfg_source_flip = array_flip($member_model->get_conf_member_source_remark());
            $cfg_cardstatus_flip = array_flip($status_arr['CARDSTATUS']);
            $cfg_pay_type_flip = array_flip($member_pay_model->get_conf_pay_type());
            $cfg_receiptstatus_flip = array_flip($receipt_status_arr['RECEIPTSTATUS']);
            $cfg_invoice_status_flip = array_flip($invoice_status_arr['INVOICE_STATUS']);


            //发送短信和是否带看
            $cfg_sms_flip = array('不发送' => 1, '发送' => 2);
            $cfg_istake_flip = array('否' => 2, '是' => 1);


            $i = 0;
            $prj_id_arr = array(); //项目编号数组
            $prj_add_user = array();//经办人数组
            for($currentRow = 3; $currentRow <= $allRow; $currentRow++)
            {   
                //*项目名称
                $PRJ_NAME = $objPHPExcel->getActiveSheet()->getCell("A".$currentRow)->getValue();

                if(trim($PRJ_NAME) == '')
                {
                    $error_str .= "第" . ($i+1) ."行，项目名称为空<br />";
                    $i++;
                    continue;
                }
				//判断电商分销
				$insert_member_data[$i]['IS_DIS'] = $_REQUEST['case_type'] == 'fx'?2:1;
                //项目名称
                $insert_member_data[$i]['PRJ_NAME'] = trim(u2g($PRJ_NAME));

                //项目ID
                $insert_member_data[$i]['PRJ_ID'] = intval($project_info_flip[$insert_member_data[$i]['PRJ_NAME']]);

                if(empty($insert_member_data[$i]['PRJ_ID']))
                {
                    $error_str .= "第" . ($i+1) ."行，在您的{$caseInfo}项目权限范围未查到 <" . $insert_member_data[$i]['PRJ_NAME'] . "> 的项目信息<br />";
                    $i++;
                    continue;
                }

                $prj_id_arr[] = $insert_member_data[$i]['PRJ_ID'];

                //会员所在城市
                $insert_member_data[$i]['CITY_ID'] = $this->channelid;

                //*手机号码
                $insert_member_data[$i]['MOBILENO'] = $objPHPExcel->getActiveSheet()->getCell("B".$currentRow)->getValue();

                //*会员姓名
                $insert_member_data[$i]['REALNAME'] = u2g($objPHPExcel->getActiveSheet()->getCell("C".$currentRow)->getValue());

                //看房人手机号
                $insert_member_data[$i]['LOOKER_MOBILENO'] = $objPHPExcel->getActiveSheet()->getCell("D".$currentRow)->getValue();

                //*证件类型
                $CERTIFICATE_TYPE = u2g($objPHPExcel->getActiveSheet()->getCell("E".$currentRow)->getValue());
                $insert_member_data[$i]['CERTIFICATE_TYPE'] = intval($cfg_certificate_type_flip[$CERTIFICATE_TYPE]);

                //*证件号码
                $insert_member_data[$i]['CERTIFICATE_NO'] = $objPHPExcel->getActiveSheet()->getCell("F".$currentRow)->getValue();

                //*会员来源
                $SOURCE = u2g($objPHPExcel->getActiveSheet()->getCell("G".$currentRow)->getValue());
                $insert_member_data[$i]['SOURCE'] = intval($cfg_source_flip[$SOURCE]);

                //*是否带看
                $IS_TAKE = u2g($objPHPExcel->getActiveSheet()->getCell("H".$currentRow)->getValue());
                $insert_member_data[$i]['IS_TAKE'] = intval($cfg_istake_flip[$IS_TAKE]);

                //*办卡状态
                $CARDSTATUS = trim(u2g($objPHPExcel->getActiveSheet()->getCell("I".$currentRow)->getValue()));
                $insert_member_data[$i]['CARDSTATUS'] = intval($cfg_cardstatus_flip[$CARDSTATUS]);

                //*办卡日期
                $cardtime = $objPHPExcel->getActiveSheet()->getCell("J".$currentRow)->getValue();
                $insert_member_data[$i]['CARDTIME'] = $cardtime?date('Y-m-d', strtotime($cardtime)):null;

                //认购日期
                $subscribetime = $objPHPExcel->getActiveSheet()->getCell("K".$currentRow)->getValue();
                $insert_member_data[$i]['SUBSCRIBETIME'] = $subscribetime?date('Y-m-d', strtotime($subscribetime)):null;

                //签约日期
                $signtime = $objPHPExcel->getActiveSheet()->getCell("L".$currentRow)->getValue();
                $insert_member_data[$i]['SIGNTIME'] = $signtime?date('Y-m-d', strtotime($signtime)):null;

                //直销人员
                $insert_member_data[$i]['DIRECTSALLER']  = u2g($objPHPExcel->getActiveSheet()->getCell("M".$currentRow)->getValue());

                //*付款方式
                $PAY_TYPE = u2g($objPHPExcel->getActiveSheet()->getCell("N".$currentRow)->getValue());
                $insert_member_data[$i]['PAY_TYPE'] = intval($cfg_pay_type_flip[$PAY_TYPE]);

                //----------------------付款明细----------------------//
                $insert_pay_data[$i]['PAY_TYPE'] = $insert_member_data[$i]['PAY_TYPE'];

                //6位检索号
                $insert_pay_data[$i]['RETRIEVAL'] = u2g($objPHPExcel->getActiveSheet()->getCell("O".$currentRow)->getValue());

                //卡号后4位
                $insert_pay_data[$i]['CVV2'] = u2g($objPHPExcel->getActiveSheet()->getCell("P".$currentRow)->getValue());

                //原始交易时间
                $trade_time = u2g($objPHPExcel->getActiveSheet()->getCell("Q".$currentRow)->getFormattedValue());
                $insert_pay_data[$i]['TRADE_TIME'] = $trade_time?date('Y-m-d H:i:s', strtotime($trade_time)):null;

                //原始交易金额
                $insert_pay_data[$i]['ORIGINAL_MONEY'] = floatval(u2g($objPHPExcel->getActiveSheet()->getCell("R".$currentRow)->getValue()));
                $insert_pay_data[$i]['TRADE_MONEY'] = $insert_pay_data[$i]['ORIGINAL_MONEY'];

                //商户编号
                $insert_pay_data[$i]['MERCHANT_NUMBER'] = $objPHPExcel->getActiveSheet()->getCell("S".$currentRow)->getValue();

                //----------------------付款明细----------------------//
				if($_REQUEST['case_type']=='fx'){
					$insert_member_data[$i]['OUT_REWARD_STATUS'] = 1;
					$insert_member_data[$i]['REWARD_STATUS'] = 1;
					//前佣收费标准
					$insert_member_data[$i]['TOTAL_PRICE'] = $objPHPExcel->getActiveSheet()->getCell("T".$currentRow)->getValue();
					//后佣收费标准
					$insert_member_data[$i]['TOTAL_PRICE_AFTER'] = $objPHPExcel->getActiveSheet()->getCell("U".$currentRow)->getValue();

					//前佣中介佣金
					$insert_member_data[$i]['AGENCY_REWARD'] = $insert_member_data[$i]['TOTAL_PRICE']?$objPHPExcel->getActiveSheet()->getCell("V".$currentRow)->getValue():'';
					//后佣中介佣金
					$insert_member_data[$i]['AGENCY_REWARD_AFTER'] = $insert_member_data[$i]['TOTAL_PRICE_AFTER']?$objPHPExcel->getActiveSheet()->getCell("W".$currentRow)->getValue():'';
					//报备时间
					//$insert_member_data[$i]['FILINGTIME'] = $objPHPExcel->getActiveSheet()->getCell("W".$currentRow)->getValue();
					$filengtime = $objPHPExcel->getActiveSheet()->getCell("X".$currentRow)->getValue();
					$insert_member_data[$i]['FILINGTIME'] = $filengtime?date('Y-m-d', strtotime($filengtime)):null;

					//*收据状态
					$RECEIPTSTATUS = u2g($objPHPExcel->getActiveSheet()->getCell("Y".$currentRow)->getValue());
					$insert_member_data[$i]['RECEIPTSTATUS'] = intval($cfg_receiptstatus_flip[$RECEIPTSTATUS]);

					//收据编号
					$receiptno = $objPHPExcel->getActiveSheet()->getCell("Z".$currentRow)->getValue();
					$receiptno = preg_replace('/([^0-9])+/',' ',$receiptno);
					$receiptno = preg_replace('/(\s)+/',' ',$receiptno);
					$insert_member_data[$i]['RECEIPTNO'] = $receiptno;

					//楼栋房号
					$insert_member_data[$i]['ROOMNO'] = $objPHPExcel->getActiveSheet()->getCell("AA".$currentRow)->getValue();

					//房型面积
					$insert_member_data[$i]['HOUSEAREA'] = $objPHPExcel->getActiveSheet()->getCell("AB".$currentRow)->getValue();

					//房屋总价
					$insert_member_data[$i]['HOUSETOTAL'] = $objPHPExcel->getActiveSheet()->getCell("AC".$currentRow)->getValue();

					//经纪公司
					$insert_member_data[$i]['AGENCY_NAME'] = u2g($objPHPExcel->getActiveSheet()->getCell("AD".$currentRow)->getValue());

					//交付时间
					$lead_time = u2g($objPHPExcel->getActiveSheet()->getCell("AE".$currentRow)->getFormattedValue());
					$insert_member_data[$i]['LEAD_TIME'] = $lead_time?date('Y-m-d H:i:s', strtotime($lead_time)):null;

					//装修标准
					$decoration_standard = trim($objPHPExcel->getActiveSheet()->getCell("AF".$currentRow)->getValue());
					$insert_member_data[$i]['DECORATION_STANDARD'] = array_search(u2g($decoration_standard),$conf_zx_standard_arr);
					$insert_member_data[$i]['DECORATION_STANDARD'] = intval($insert_member_data[$i]['DECORATION_STANDARD']);

					//*经办人
					$insert_member_data[$i]['ADD_USERNAME'] = u2g($objPHPExcel->getActiveSheet()->getCell("AG".$currentRow)->getValue());
					$prj_add_user[] = $insert_member_data[$i]['ADD_USERNAME'];

					//备注
					$insert_member_data[$i]['NOTE'] = u2g($objPHPExcel->getActiveSheet()->getCell("AH".$currentRow)->getValue());
					$insert_member_data[$i]['CREATETIME'] = date('Y-m-d H:i:s');

				}else{
					//单套收费标准
					$insert_member_data[$i]['TOTAL_PRICE'] = $objPHPExcel->getActiveSheet()->getCell("T".$currentRow)->getValue();

					//中介佣金
					$insert_member_data[$i]['AGENCY_REWARD'] = $objPHPExcel->getActiveSheet()->getCell("U".$currentRow)->getValue();

					//*收据状态
					$RECEIPTSTATUS = u2g($objPHPExcel->getActiveSheet()->getCell("V".$currentRow)->getValue());
					$insert_member_data[$i]['RECEIPTSTATUS'] = intval($cfg_receiptstatus_flip[$RECEIPTSTATUS]);

					//收据编号
					$receiptno = $objPHPExcel->getActiveSheet()->getCell("W".$currentRow)->getValue();
					$receiptno = preg_replace('/([^0-9])+/',' ',$receiptno);
					$receiptno = preg_replace('/(\s)+/',' ',$receiptno);
					$insert_member_data[$i]['RECEIPTNO'] = $receiptno;

					//楼栋房号
					$insert_member_data[$i]['ROOMNO'] = $objPHPExcel->getActiveSheet()->getCell("X".$currentRow)->getValue();

					//房型面积
					$insert_member_data[$i]['HOUSEAREA'] = $objPHPExcel->getActiveSheet()->getCell("Y".$currentRow)->getValue();

					//房屋总价
					$insert_member_data[$i]['HOUSETOTAL'] = $objPHPExcel->getActiveSheet()->getCell("Z".$currentRow)->getValue();

					//经纪公司
					$insert_member_data[$i]['AGENCY_NAME'] = u2g($objPHPExcel->getActiveSheet()->getCell("AA".$currentRow)->getValue());

					//交付时间
					$lead_time = u2g($objPHPExcel->getActiveSheet()->getCell("AB".$currentRow)->getFormattedValue());
					$insert_member_data[$i]['LEAD_TIME'] = $lead_time?date('Y-m-d H:i:s', strtotime($lead_time)):null;

					//装修标准
					$decoration_standard = trim($objPHPExcel->getActiveSheet()->getCell("AC".$currentRow)->getValue());
					$insert_member_data[$i]['DECORATION_STANDARD'] = array_search(u2g($decoration_standard),$conf_zx_standard_arr);
					$insert_member_data[$i]['DECORATION_STANDARD'] = intval($insert_member_data[$i]['DECORATION_STANDARD']);

					//*经办人
					$insert_member_data[$i]['ADD_USERNAME'] = u2g($objPHPExcel->getActiveSheet()->getCell("AD".$currentRow)->getValue());
					$prj_add_user[] = $insert_member_data[$i]['ADD_USERNAME'];

					//备注
					$insert_member_data[$i]['NOTE'] = u2g($objPHPExcel->getActiveSheet()->getCell("AE".$currentRow)->getValue());
					$insert_member_data[$i]['CREATETIME'] = date('Y-m-d H:i:s');


                }
                $i ++;
            }

            //先第一轮数据验证
            if($error_str){
                $return['msg'] = g2u($error_str);
                die(@json_encode($return));
            }

            //第二轮数据验证
            $error_str = '';

            $case_model = D("ProjectCase");

            /***数据规则验证***/
            foreach($insert_member_data as $key => $value)
            {

                //获取caseid
				if($_REQUEST['case_type']=='fx'){
					$case_info = $case_model->get_info_by_pid($value['PRJ_ID'], 'fx',array('ID','PROJECT_ID'));
					$case_id = $case_info[0]['ID'];
				}else{
					$case_info = $case_model->get_info_by_pid($value['PRJ_ID'], 'ds',array('ID','PROJECT_ID'));
					$case_id = $case_info[0]['ID'];
				}


                // 单套收费标准 = 已缴 + 未缴
                $insert_member_data[$key]['PAID_MONEY'] = $insert_pay_data[$key]['TRADE_MONEY'];
                $insert_member_data[$key]['UNPAID_MONEY'] = $value['TOTAL_PRICE'] - $insert_pay_data[$key]['TRADE_MONEY'];

                //如果未缴纳金额小于0
                if($insert_member_data[$key]['UNPAID_MONEY'] < 0){
                    $error_str .= "第" . ($key+1) . "行，已缴纳金额 > 单套收费标准！<br />";
                    continue;
                }

                //发票状态
                $insert_member_data[$key]['INVOICE_STATUS'] = 1;

                //不发送短信
                $insert_member_data[$key]['IS_SMS'] = 1;

                //验证项目权限
//                $ret_data = M("erp_prorole")
//                    ->where("use_id = " . $this->uid . " and pro_id = " . $value['PRJ_ID'] . " and erp_id = 1 and isvalid = -1")
//                    ->select();
//
//                if(!$ret_data){
//                    $error_str .= "第" . ($key+1) . "行，项目无电商业务或者您没有该项目的权限！<br />";
//                    continue;
//                }

                //验证经办人
                $add_user_info = M('Erp_users')
                    ->field('ID,USERNAME,NAME')
                    ->where("USERNAME = '{$value['ADD_USERNAME']}'")
                    ->find();

                if(!$add_user_info){
                    $error_str .= "第" . ($key+1) . "行，经办人无效！<br />";
                    continue;
                }

                //添加案例编号信息
                $insert_member_data[$key]['CASE_ID'] = $case_id;

                //添加经办人信息
                $insert_member_data[$key]['ADD_UID'] = $add_user_info['ID'];
                $insert_member_data[$key]['ADD_USERNAME'] = $add_user_info['NAME'];

                //手机号码
                if($value['MOBILENO'] == '') {
                    $error_str .= "第" . ($key+1) . "行，请填写购房人手机号！<br />";
                    continue;
                }

                //手机号认证
                if($value['MOBILENO'] && !preg_match("/^1[3-9]\d{9}$/",$value['MOBILENO'])){
                    $error_str .= "第" . ($key+1) . "行，请填写正确的购房人手机号！<br />";
                    continue;
                }

                //名称
                if($value['REALNAME'] == '') {
                    $error_str .= "第" . ($key+1) . "行，请填写会员姓名！<br />";
                    continue;
                }

                //证件类型
                if(empty($value['CERTIFICATE_TYPE'])) {
                    $error_str .= "第" . ($key+1) . "行，请选择证件类型！<br />";
                    continue;
                }

                //证件号码
                if($value['CERTIFICATE_TYPE']==1 && !preg_match("/^(\d{18}|\d{15}|\d{17}(x|X))$/",$value['CERTIFICATE_NO'])) {
                    $error_str .= "第" . ($key+1) . "行，请填写正确的身份证号！<br />";
                    continue;
                }

                //证件号不为空
                if($value['CERTIFICATE_TYPE'] != 1 && $value['CERTIFICATE_NO']=='') {
                    $error_str .= "第" . ($key+1) . "行，证件号码不能为空！<br />";
                    continue;
                }

                //会员来源
                if(empty($value['SOURCE'])) {
                    $error_str .= "第" . ($key+1) . "行，请选择会员来源！<br />";
                    continue;
                }

                //是否中介带看
                if(empty($value['IS_TAKE'])) {
                    $error_str .= "第" . ($key+1) . "行，请选择是否中介带看！<br />";
                    continue;
                }

                //办卡状态
                if(empty($value['CARDSTATUS'])) {
                    $error_str .= "第" . ($key+1) . "行，请选择会员办卡状态！<br />";
                    continue;
                }

                //办卡日期
                if(empty($value['CARDTIME']) && $_REQUEST['case_type']!='fx') {
                    $error_str .= "第" . ($key+1) . "行，请填写会员办卡时间！<br />";
                    continue;
                }

                //付款方式
                if(empty($value['PAY_TYPE']) && $_REQUEST['case_type']!='fx' ) {
                    $error_str .= "第" . ($key+1) . "行，请选择会员付款方式！<br />";
                    continue;
                }

                //收据状态
                if(empty($value['RECEIPTSTATUS']) && $_REQUEST['case_type']!='fx' ){
                    $error_str .= "第" . ($key+1) . "行，请选择收据状态！<br />";
                    continue;
                }
				//收据编号
				if($value['RECEIPTNO']  ){
						$receiptno = M('Erp_cardmember')->where("RECEIPTNO='".$value['RECEIPTNO']."' AND CITY_ID='".$this->channelid."' AND STATUS = 1")->find(); 
						if($receiptno){
							
							$error_str .= "第" . ($key+1) . "该城市下已经存在相同的收据编号<br />";
							
							continue;
						}
				}
				if($_REQUEST['case_type']=='fx'){
					if($value['TOTAL_PRICE']){
						if(empty($value['TOTAL_PRICE'])   ) {
							$error_str .= "第" . ($key+1) . "行，请选填写单套收费标准!<br />";
							continue;
						}

						if(empty($value['RECEIPTNO'])   ) {
							$error_str .= "第" . ($key+1) . "行，请填写收据编号！<br />";
							continue;
						}

					}
					if(empty($value['FILINGTIME'])){
						$error_str .= "第" . ($key+1) . "行，请填写报备时间！<br />";
							continue;

					}

					if( empty($value['TOTAL_PRICE']) && empty($value['TOTAL_PRICE_AFTER'])){
						$error_str .= "第" . ($key+1) . "行， 前佣收费标准或者后佣收费标准必填一项！<br />";
						continue;
					}

					
					if($value['CARDSTATUS']==3 && ($value['SOURCE']==1 || $value['SOURCE']==7 || $value['SOURCE']==8) && empty($value['AGENCY_REWARD']) && empty($value['AGENCY_REWARD_AFTER'])) {
						$error_str .= "第" . ($key+1) . "行，办卡状态为已办已签约，会员来源为中介或者分销公司,前佣中介佣金或者后佣中介佣金必填一项！<br />";
						continue;
					}

					$project = D('Project');
					$case_model = D('ProjectCase');
					$case_info = $case_model->get_info_by_pid($value['PRJ_ID'], 'fx');
					$case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
					//$flag1 = $project->get_feescale_by_cid_stype($case_id,1, $value['TOTAL_PRICE'],1,0);
					$flag2 = $project->get_feescale_by_cid_stype($case_id,1, $value['TOTAL_PRICE_AFTER'],1,1);
					//$flag3 = $project->get_feescale_by_cid_stype($case_id,2, $value['AGENCY_REWARD'],1,0);
					$flag4 = $project->get_feescale_by_cid_stype($case_id,2, $value['AGENCY_REWARD_AFTER'],1,1);
					if( $flag2  ||$flag4 ){
						if(!$value['HOUSETOTAL']){
							 
							$error_str .= "第" . ($key+1) . "行，后佣收费标准或佣金为百分比，必须填写房屋总价!<br />";
							continue;
						}
					}


				}else{
					if($value['CARDSTATUS']==3 && ($value['SOURCE']==1 || $value['SOURCE']==7 || $value['SOURCE']==8) && empty($value['AGENCY_REWARD'])) {
						$error_str .= "第" . ($key+1) . "行，办卡状态为已办已签约，会员来源为中介或分销公司,中介佣金必填！<br />";
						continue;
					}
				}

                if(empty($value['TOTAL_PRICE']) && $_REQUEST['case_type']!='fx' ) {
                    $error_str .= "第" . ($key+1) . "行，请选填写单套收费标准!<br />";
                    continue;
                }

                if(empty($value['RECEIPTNO']) && $_REQUEST['case_type']!='fx' ) {
                    $error_str .= "第" . ($key+1) . "行，请填写收据编号！<br />";
                    continue;
                }

                if($value['CITY_ID']==1 && $value['CARDSTATUS']==3 && $value['ROOMNO']=='') {
                    $error_str .= "第" . ($key+1) . "行，办卡用户为已办已签约状态，请填写楼栋房号！<br />";
                    continue;
                }

                if($value['CARDSTATUS']==3 && ($value['LEAD_TIME']=='' || !$value['DECORATION_STANDARD'])) {
                    $error_str .= "第" . ($key+1) . "行，办卡用户为已办已签约状态，请填写交付时间和装修标准！<br />";
                    continue;
                }

                //办卡状态

                $card_flag = false;
                switch($value['CARDSTATUS'])
                {
                    //已认购
                    case '2':
                        $insert_member_data[$key]['SUBSCRIBETIME'] = strip_tags($value['SUBSCRIBETIME']);
                        $insert_member_data[$key]['SIGNTIME'] = null;
                        $insert_member_data[$key]['SIGNEDSUITE'] = null;
                        if(!$value['SUBSCRIBETIME']) {
                            $error_str .= "第" . ($key + 1) . "行，办卡状态为已办已认购，认购日期必须填写！<br />";
                            $card_flag = true;
                        }
                        break;
                    case '3':
                        //已签约
                        $insert_member_data[$key]['SIGNTIME'] = strip_tags($value['SIGNTIME']);
                        //签约套数默认为1
                        $insert_member_data[$key]['SIGNEDSUITE'] = 1;

                        if(empty($value['SIGNTIME']))
                        {
                            $error_str .= "第" . ($key + 1) . "行，办卡状态为已办已签约，签约日期必须填写！<br />";
                            $card_flag = true;
                        }
                        break;
                }

                if($card_flag){
                    continue;
                }

                //办卡状态只支持    已办未成交、已办已签约、已办已认购
                if($value['CARDSTATUS']>3){
                    $error_str .= "第" . ($key + 1) . "行，亲，办卡状态只支持  已办未成交、已办已签约、已办已认购！<br />";
                    continue;
                }

                //付款方式的验证
                //商户编号
                $merchant_arr = array();
                $merchant_info = M('erp_merchant')->where("CITY_ID = '".$this->city_id."'")->select();
                if(is_array($merchant_info) && !empty($merchant_info))
                {
                    foreach($merchant_info as $k => $v)
                    {
                        $large_str = '';
                        $v['IS_LARGE'] == 1 ? $large_str .= '[大额]' : '';
                        $merchant_arr[$v['MERCHANT_NUMBER']] = $v['MERCHANT_NUMBER'].$large_str;
                    }
                }

                $pay_flag = false;

                //如果是POS机方式
                if($value['PAY_TYPE']==1){
                    if(strlen($insert_pay_data[$key]['RETRIEVAL']) != 6){
                        $error_str .= '第' . ($key+1) . '行,' . "付款方式为POS机的付款明细，6位检索号有误！<br />";
                        $pay_flag = true;
                    }
                    if(empty($insert_pay_data[$key]['MERCHANT_NUMBER'])){
                        $error_str .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，商户编号未填写！<br />";
                        $pay_flag = true;
                    }
                    if(empty($insert_pay_data[$key]['TRADE_TIME']) && $_REQUEST['case_type']!='fx'){
                        $error_str .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，原始交易时间不能为空！<br />";
                        $pay_flag = true;
                    }
                    if($insert_pay_data[$key]['TRADE_MONEY']==0){
                        $error_str .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，原始交易金额不能为空！<br />";
                        $pay_flag = true;
                    }

                    //判断是否是大额付款(商户编号)
                    if(strpos($merchant_arr[$insert_pay_data[$key]['MERCHANT_NUMBER']],"大额")!==false){
                        if(strlen($insert_pay_data[$key]['CVV2'])<10){
                            $error_str .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，商户编号选择大额，请写全卡号！<br />";
                            $pay_flag = true;
                        }
                    }
                    else
                    {
                        if(strlen($insert_pay_data[$key]['CVV2']) != 4) {
                            $error_str .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，请编号卡号后四位！<br />";
                            $pay_flag = true;
                        }
                    }
                }
                //如果是现金和网银方式
                else if($value['PAY_TYPE']==2 || $value['PAY_TYPE']==3){
                    if($insert_pay_data[$key]['TRADE_MONEY']==0 || empty($insert_pay_data[$key]['TRADE_TIME'])){
                        $error_str .= '第' . ($key+1) . '笔,' . "付款方式为现金或者网银的付款明细，原始交易金额和原始交易时间不能为空！<br />";
                        $pay_flag = true;
                    }
                }

                if($pay_flag){
                    continue;
                }
            }

            //返回结果集
            if($error_str){
                $return['msg'] = g2u($error_str);
                die(@json_encode($return));
            }

            //-------------------数据库操作-------------------//
            //插入数据
            $insert_success_num = 0;
            //导入会员数量
            $member_num = count($insert_member_data);
            
            $member_pay = D('MemberPay');
            $pay_status_arr = $member_pay->get_conf_status();

            //收益MODEL
            $income_model = D('ProjectIncome');
            $insert_member_id = 0;

            if(is_array($insert_member_data) && !empty($insert_member_data))
            {
                $this->model = new Model();
                //事务开始
                $this->model->startTrans();
                foreach($insert_member_data as $key => $member_info)
                {
                    //添加会员信息
                    $insert_member_id = $member_model->add_member_info($member_info);

                    if($insert_member_id > 0)
                    {

                        //数据入crm
                        if($member_info['CARDSTATUS']) {
                            switch ($member_info['CARDSTATUS']) {
                                case '1':
                                case '2':
                                    $tlfcard_status = 1;
                                    $tlfcard_signtime = 0;
                                    $tlfcard_backtime = 0;
                                    break;
                                case '3':
                                    $tlfcard_status = 2;
                                    $tlfcard_signtime = $member_info['SIGNTIME'];
                                    $tlfcard_backtime = 0;
                                    break;
                            }
                            $crm_api_arr = array();
                            //用户名
                            $crm_api_arr['username'] = urlencode($member_info['REALNAME']);
                            //号码
                            $crm_api_arr['mobile'] = $member_info['MOBILENO'];
                            //编号
                            $crm_api_arr['activefrom'] = 104;
                            //城市
                            $crm_api_arr['city'] = $this->user_city_py;
                            //装修标准
                            $conf_zx_standard = $member_model->get_conf_zx_standard();
                            //行为
                            $crm_api_arr['activename'] = urlencode($member_info['PRJ_NAME'] . "-" .
                                    $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']] . "-" . $member_info['CARDTIME'] . "-" .
                                    $conf_zx_standard[$member_info['DECORATION_STANDARD']]);

                            //来源
                            $crm_api_arr['importfrom'] = urlencode('团立方监控后台');
                            //支付时间
                            $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                            $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                            $crm_api_arr['tlfcard_creattime'] = $member_info['CARDTIME'];
                            $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                            $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                            $crm_api_arr['tlf_username'] = trim($this->uname);
                            //项目ID
                            $crm_api_arr['projectid'] = $member_info['PRJ_ID'];

                            //if ($member_info['CARDSTATUS'] == 3)
                             //   $crm_api_arr['floor_id'] = $member_info['PRJ_ID'];

                            //提交
                            $crm_url = submit_crm_data_by_api_url($crm_api_arr);
                            $ret_log = api_log($this->city_id,$crm_url,0,$this->uid,2);
                        }

                    }
                    
                    //添加支付信息
                    if($insert_member_id > 0 && !empty($insert_pay_data[$key]) && $insert_pay_data[$key]['TRADE_MONEY']  )
                    {
                        //新增明细状态
                        $insert_pay_data[$key]['MID'] = $insert_member_id;
                        $insert_pay_data[$key]['STATUS'] = $pay_status_arr['wait_confirm'];
                        $insert_pay_data[$key]['ADD_UID'] = $this->uid;
                        $insert_payment_id = $member_pay->add_member_info($insert_pay_data[$key]);
 
                        if($insert_payment_id > 0)
                        {
                            //添加收益信息到收益表
                            $income_info['CASE_ID'] = $member_info['CASE_ID'];
                            $income_info['ENTITY_ID'] = $insert_member_id;
                            $income_info['PAY_ID'] = $insert_payment_id;
                            $income_info['INCOME_FROM'] = 1;//电商会员支付
                            $income_info['INCOME'] = $insert_pay_data[$key]['TRADE_MONEY'];
                            $income_info['INCOME_REMARK'] = '';
                            $income_info['ADD_UID'] = $this->uid;
                            $income_info['OCCUR_TIME'] = $insert_pay_data[$key]['TRADE_TIME'];
                            $insert_sy_id = $income_model->add_income_info($income_info);
                        }
                    }

                    //提交事务基础
					if($insert_pay_data[$key]['TRADE_MONEY']){
						if( $insert_member_id > 0 && $insert_payment_id > 0 && $insert_sy_id > 0 && $ret_log)
                        $insert_success_num ++;
					}else{
						if( $insert_member_id > 0 && $ret_log)
                        $insert_success_num ++;
					}
                }
            }


            //事务处理
            if($insert_success_num == $member_num)
            {   
                $this->model->commit();
                $return['state']  = 1;
                $return['msg'] = g2u('会员数据导入成功');
                die( json_encode($return));
            }
            else
            {   
                $this->model->rollback();
                $return['state']  = 0;
                $return['msg'] = g2u("会员数据导入失败,请重试.... $insert_member_id  -  $insert_payment_id  - $insert_sy_id - $ret_log");
                die(json_encode($return));
            }
        }
        else
        {
            $this->assign('case_type',$_REQUEST['case_type']);
			$this->display('import_member');
        }
    }

    /**
     * 获取采购申请的工作流ID
     */
    public function getFlowId() {
        $response = array(
            'status' => false,
            'message' => '参数错误',
            'data' => ''
        );
        $advanceId = $_REQUEST['AdvanceId'];
        if (intval($advanceId) > 0) {
            try {
                $result = D()->query(sprintf(self::PAYOUT_FLOWID_SQL, $advanceId));
                if (notEmptyArray($result)) {
                    $response['status'] = true;
                    $response['message'] = '获取工作流ID成功';
                    $response['data'] = $result[0]['ID'];
                } else {
                    $response['message'] = '该报销项目尚未发起超额工作流!';
                }
            } catch (Exception $e) {
                $response['status'] = false;
                $response['message'] = $e->getMessage();
            }
        }

        echo json_encode(g2u($response));
    }
	/**
    +----------------------------------------------------------
    * 房管局状态对比
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
	public function prjbudget(){
		if($_POST['sub']){
			//print_r($_REQUEST);EXIT;
			$map = array();
			$prj_id = $_POST['prj_id'];//项目id
			$buildno = $_POST['buildno'];//栋号
			$map['prj_id'] = $prj_id;
			$map['prj_name'] = array('like',trim($_POST['prj_name']));
			$map['roomno'] = array('like',($buildno)."-%");
			//print_r($map);exit;
			import('ORG.Util.Page');
			$count = M("Erp_cardmember")->where($map)->count();
			$Page = new Page($count);
			$show = $Page->show();//分页
			$memberData = M("Erp_cardmember")
						  ->where($map)
						  ->limit($Page->firstRow.','.$Page->listRows)
						  ->select();
			
			if($memberData){
				$prjid = M('Erp_house')
						->where("project_id =".$prj_id)
						->getField("FORNANJING");//获取prjid
				
				if($prjid){
					$manageData=get_Compare_Data($prjid,$buildno);//房管局数据
					
					if($manageData){
						foreach($memberData as &$member){
							$room = explode('-',$member['ROOMNO']);
							foreach($manageData as $manage){
								if($room[1] == intval($manage['room'])){
									$member['HOUSESTATUS'] = $manage['presaleid'];
								}
							}
						}
					}
				}
			}
			
			$this->assign('page',$show);
			$this->assign('searchInfo',array('prj_name'=>$_POST['prj_name'],'prj_id'=>$prj_id,'buildno'=>$buildno));
			$this->assign('memberData',$memberData);
		}
        $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
		$this->display();
	}
    
    
    /**
     +----------------------------------------------------------
     * 批量修改状态弹框
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function show_change_status_window()
    {
        Vendor('Oms.Form');
    	$form = new Form();
    	$form = $form->initForminfo(188);
        
        //实例化会员MODEL
    	$member_model = D('Member');

        //获取项目名称是否不同，如果相同可以更改奖励，不同奖励不显示,1相同，0不同
        $reward = $_REQUEST['isReward'] ? $_REQUEST['isReward']: '0';
        $memberId = $_REQUEST['memberId'] ? $_REQUEST['memberId']: '0';
        if($reward == 1){
            $form->setMyField('PROPERTY_DEAL_REWARD','FORMVISIBLE',-1)
                ->setMyField('AGENCY_DEAL_REWARD','FORMVISIBLE',-1)
                ->setMyField('OUT_REWARD','FORMVISIBLE',-1);
            if($memberId) {
                $caseId = M("Erp_cardmember")->where("ID=" . $memberId)->getField("CASE_ID");

                //设置收费标准
                $feescale = array();
                $project = D('Project');
                $feescale = $project->get_feescale_by_cid($caseId);

                $fees_arr = array();
                if(is_array($feescale) && !empty($feescale) ) {
                    foreach ($feescale as $key => $value) {
                        $dw = $value['STYPE']?'%':'元';
                        if ($value['AMOUNT'][0] == '.') {
                            $value['AMOUNT'] = '0' . $value['AMOUNT'];
                        }
                        if($value['SCALETYPE']==1 || $value['SCALETYPE']==2){

                        if($value['MTYPE'])$fees_arr[$value['SCALETYPE'].'_'.$value['MTYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;
                        else $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;

                        }else $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'].$dw;
                    }
                }

                //置业顾问佣金
                // $form->setMyField('PROPERTY_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                //中介成交奖
                $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                //置业成交奖金
                $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);
            }
        }
        /***获取会员办卡、收据、发票状态***/
        $status_arr = $member_model->get_conf_all_status_remark();
        
        //办卡状态
        $card_status_arr = $status_arr['CARDSTATUS'];
        $form->setMyField('CARDSTATUS', 'LISTCHAR', 
                array2listchar($card_status_arr), FALSE);
        
        //发票状态
        $form->setMyField('INVOICE_STATUS', 'LISTCHAR',
                array2listchar($status_arr['INVOICE_STATUS']), FALSE);
        
        //收据状态
        $receipt_status_arr = $status_arr['RECEIPTSTATUS'];
        $form->setMyField('RECEIPTSTATUS', 'LISTCHAR', 
                array2listchar($receipt_status_arr), FALSE);

        //发票状态
        $form->setMyFieldVal('SUBSCRIBETIME', date('Y-m-d H:i:s',time()) , FALSE);
        $form->setMyFieldVal('SIGNTIME', date('Y-m-d H:i:s',time()) , FALSE);

        //支付时间
        $form->setMyFieldVal('LEAD_TIME', date('Y-m-d H:i:s',time()) , FALSE);

        //装修标准
        $conf_zx_standard = $member_model->get_conf_zx_standard();
        $form->setMyField('DECORATION_STANDARD', 'LISTCHAR', array2listchar($conf_zx_standard), FALSE);

        //以弹框方式出现，替换原有保存，取消按钮，勿动
        $form->FORMCHANGEBTN = ' ';
        
        $form = $form->getResult();
        $this->assign('form', $form);
        $this->display('change_status_window');
    }
    
    /**
     +----------------------------------------------------------
     * 批量修改会员状态
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function batch_change_status()
    {
        $info = array();
        $id_arr = $_GET['memberId'];
        $card_status = !empty($_GET['card_status']) ? intval($_GET['card_status']) : 0;
        $invoice_status = !empty($_GET['invoice_status']) ? intval($_GET['invoice_status']) : 0;
        $receipstatus = !empty($_GET['receipstatus']) ?  intval($_GET['receipstatus']) : 0;
        $subscribetime = !empty($_GET['subscribetime']) ?  trim($_GET['subscribetime']) : '';
        $signtime = !empty($_GET['signtime']) ?  trim($_GET['signtime']) : '';
        $lead_time = !empty($_GET['lead_time']) ?  trim($_GET['lead_time']) : '';
        $decoration_standard = !empty($_GET['decoration_standard']) ?  trim($_GET['decoration_standard']) : 0;
        $property_deal_reward = !empty($_GET['property_deal_reward']) ?  trim($_GET['property_deal_reward']) : 0;
        $agency_deal_reward = !empty($_GET['agency_deal_reward']) ?  trim($_GET['agency_deal_reward']) : 0;
        $out_reward = !empty($_GET['out_reward']) ?  trim($_GET['out_reward']) : 0;

        $data = array();
        if ($property_deal_reward > 0) {
            $data['PROPERTY_DEAL_REWARD'] = $property_deal_reward;
        }
        if ($agency_deal_reward > 0) {
            $data['AGENCY_DEAL_REWARD'] = $agency_deal_reward;
        }
        if ($out_reward > 0) {
            $data['OUT_REWARD'] = $out_reward;
        }
        D()->startTrans();
        if(!empty($data) && count($data)>0){
            foreach($id_arr as $id){
                $out_reward_status = D('erp_cardmember')->where("ID=".$id)->getField("OUT_REWARD_STATUS");
                $agency_deal_reward_status = D('erp_cardmember')->where("ID=".$id)->getField("AGENCY_DEAL_REWARD_STATUS");
                $property_deal_reward_status = D('erp_cardmember')->where("ID=".$id)->getField("PROPERTY_DEAL_REWARD_STATUS");

				if( !empty($_GET['out_reward'])){
					if($out_reward_status == 2 || $out_reward_status == 5 ){
						D()->rollback();
						$info['state'] = 0;
						$info['msg'] = g2u('修改失败,外部成交奖励已申请报销或已报销,无法修改');
						$info['out_reward_status'] =$out_reward_status;
						echo json_encode($info);
						exit;

					}
				}
				if( !empty($_GET['agency_deal_reward'])){
					if($agency_deal_reward_status != 1  ){
						D()->rollback();
						$info['state'] = 0;
						$info['msg'] = g2u('修改失败,中介成交奖励已申请报销或已报销,无法修改');
						echo json_encode($info);
						exit;

					}
				}
				if( !empty($_GET['property_deal_reward'])){
					if($property_deal_reward_status != 1    ){
						D()->rollback();
						$info['state'] = 0;
						$info['msg'] = g2u('修改失败,置业顾问成交奖励已申请报销或已报销,无法修改');
						echo json_encode($info);
						exit;

					}
				}
                $updataNum = D('erp_cardmember')->where("ID=".$id)->save($data);
                if($updataNum == ""){
                    D()->rollback();
                    $info['state'] = 0;
                    $info['msg'] = g2u('操作失败');
                    echo json_encode($info);
                    exit;
                }
            }
        }
        if($card_status == 0 && $invoice_status == 0 && $receipstatus == 0 && $property_deal_reward == 0 && $agency_deal_reward == 0 && $out_reward == 0)
        {
            D()->rollback();
            $info['state']  = 0;
            $info['msg'] = g2u('批量操作失败，办卡状态、发票状态、收据状态至少要选择一个');
            echo json_encode($info);
            exit;
        }
        
                //已办已认购
        if($card_status == '2' && $subscribetime == '')
        {
            D()->rollback();
            $info['state']  = 0;
            $info['msg'] = g2u('批量操作失败，办卡状态为已办已认购，认购日期日期必须填写');
            echo json_encode($info);
            exit;
        }
        //已办已签约
        else if($card_status == '3' && ($signtime == '' || $lead_time=='' || $decoration_standard==0))
        {
            D()->rollback();
            $info['state']  = 0;
            $info['msg'] = g2u('批量操作失败，办卡状态为已办已签约，签约日期、交付时间、装修标准必须填写');
            echo json_encode($info);
            exit;
        }
        
        $card_status_no_pass_num = 0;
        $invoice_status_no_pass_num = 0;
        $receipstatus_no_pass_num = 0;
        
        if(is_array($id_arr) && !empty($id_arr))
        {   
            $member_model = D('Member');
            
            //会员发票状态
            $conf_invoice_status = $member_model->get_conf_invoice_status();
            
            //会员信息
            $member_info = array();
            $search_field = array('CARDSTATUS','INVOICE_STATUS','RECEIPTSTATUS','PRJ_NAME','PRJ_ID','REALNAME','MOBILENO','CITY_ID','CREATETIME');
            $member_info = $member_model->get_info_by_ids($id_arr, $search_field);
            
            if(is_array($member_info) && !empty($member_info))
            {  
                foreach ($member_info as $key => $value)
                {   
                    if($card_status > 0)
                    {
                        //办卡状态判断
                        if($value['CARDSTATUS'] == 1 && !in_array($card_status, array(1,2,3)))
                        {   
                            $card_status_no_pass_num ++;
                        }
                        else if($value['CARDSTATUS'] == 2 && !in_array($card_status, array(2,3)))
                        {
                            $card_status_no_pass_num ++;
                        }
                        else if($value['CARDSTATUS'] == 3 && $card_status != 3)
                        {
                            $card_status_no_pass_num ++;
                        }
                        else if($value['CARDSTATUS'] == 4 && $card_status != 4)
                        {
                            $card_status_no_pass_num ++;
                        }
                    }
                    
                    if($invoice_status > 0)
                    {
                        //发票状态判断
                        if($value['INVOICE_STATUS'] == 1 && $invoice_status != 1)
                        {   
                            $invoice_status_no_pass_num ++;
                        }
                        else if($value['INVOICE_STATUS'] == 2 && !in_array($invoice_status, array(2,3)))
                        {
                            $invoice_status_no_pass_num ++;
                        }
                        else if($value['INVOICE_STATUS'] == 3 && !in_array($invoice_status, array(2,3)))
                        {
                            $invoice_status_no_pass_num ++;
                        }
                        else if($value['INVOICE_STATUS'] == 4 && $invoice_status != 4)
                        {
                            $invoice_status_no_pass_num ++;
                        }
                        else if($value['INVOICE_STATUS'] == 5 && ($invoice_status != 5 && $invoice_status != 1))
                        {
                            $invoice_status_no_pass_num ++;
                        }
                    }
                    
                    if($receipstatus > 0)
                    {
                        //收据状态判断
                        if($value['RECEIPTSTATUS'] == 2 && !in_array($receipstatus, array(2,3,4)))
                        {   
                            $receipstatus_no_pass_num ++;
                        }
                        else if($value['RECEIPTSTATUS'] == 3 && !in_array($receipstatus, array(2,3,4)))
                        {
                            $receipstatus_no_pass_num ++;
                        }
                        else if($value['RECEIPTSTATUS'] == 4 && $receipstatus != 4)
                        {
                            $receipstatus_no_pass_num ++;
                        }
                    }
                }
            }
            else 
            {
                D()->rollback();
                $info['state']  = 0;
                $info['msg'] = g2u('批量操作失败，会员信息异常');
                echo json_encode($info);
                exit;
            }
            
            if($card_status_no_pass_num > 0)
            {
                D()->rollback();
                $info['state']  = 0;
                $msg = '批量修改状态失败,['.$card_status_no_pass_num.']条数据不符合条件，'
                        . '<br>修改会员办卡状态需要符合以下条件：'
                        . '<br>1、已办卡未成交状态的会员，可修改为已办卡已认购或者已办卡已签约；'
                        . '<br>2、已办卡已认购状态的会员，可修改为已办卡已签约；'
                        . '<br>3、已办卡已签约的会员，无法修改；'
                        . '<br>4、已退卡的会员，无法修改。';
                $info['msg'] = g2u($msg);
                echo json_encode($info);
                exit;
            }
            
            if($invoice_status_no_pass_num > 0)
            {
                D()->rollback();
                $info['state']  = 0;
                $msg = '批量修改状态失败,['.$invoice_status_no_pass_num.']条数据不符合条件，'
                        . '<br>修改会员发票状态需要符合以下条件：'
                        . '<br>1、未开状态，不可修改；'
                        . '<br>2、申请中状态，只能修改为“未开”的状态；'
                        . '<br>3、已开未领状态，可以修改为已领；'
                        . '<br>4、已领状态，可以修改为已开未领；'
                        . '<br>5、已收回状态，无法修改状态。';
                $info['msg'] = g2u($msg);
                echo json_encode($info);
                exit;
            }
            
            if($receipstatus_no_pass_num > 0)
            {
                D()->rollback();
                $info['state']  = 0;
                $msg = '批量修改状态失败,['.$receipstatus_no_pass_num.']条数据不符合条件，'
                        . '<br>修改会员收据状态需要符合以下条件：'
                        . '<br>1、已开未领可修改为已领或已收回；'
                        . '<br>2、已领可修改为已收回或已开未领；'
                        . '<br>3、已收回不可修改收据状态。';
                $info['msg'] = g2u($msg);
                echo json_encode($info);
                exit;
            }
            
            $update_arr = array();
            $card_status > 0 ? $update_arr['CARDSTATUS'] = $card_status : "";
            $invoice_status > 0 ? $update_arr['INVOICE_STATUS'] = $invoice_status : "";
            $receipstatus > 0 ? $update_arr['RECEIPTSTATUS'] = $receipstatus : "";
            if($card_status==3) {
                $update_arr['SIGNTIME'] = $signtime;
                //签约套数
                $update_arr['SIGNEDSUITE'] = 1;
            }
            else if($card_status==2){
                $update_arr['SUBSCRIBETIME'] = $subscribetime;
            }
            if($lead_time != "" or $decoration_standard != ""){
                $update_arr['LEAD_TIME'] = $lead_time;
                $update_arr['DECORATION_STANDARD'] = $decoration_standard;
            }

            if($property_deal_reward > 0) {
                $update_arr['PROPERTY_DEAL_REWARD'] = $property_deal_reward;
            }

            if($agency_deal_reward > 0) {
                $update_arr['AGENCY_DEAL_REWARD'] = $agency_deal_reward;
            }

            if($out_reward > 0) {
                $update_arr['OUT_REWARD'] = $out_reward;
            }

            if(!empty($update_arr))
            {
                $result = $member_model->update_info_by_id($id_arr, $update_arr);
            }

            //同步到crm系统
            /***获取会员办卡、开票、发票状态***/
            $status_arr = $member_model->get_conf_all_status_remark();

            //装修标准
            $conf_zx_standard = $member_model->get_conf_zx_standard();

            //城市配置信息
            $city_info =  $member_model->get_cityinfo("py");

            foreach ($member_info as $key => $value) {
                if ($value['CARDSTATUS'] != $card_status && $card_status) {
                    //行为
                    $activename = $value['PRJ_NAME'] . "办卡状态:{$status_arr['CARDSTATUS'][$card_status]}" . " 日期：" . date("Y-m-d",time()) . $conf_zx_standard[$decoration_standard];

                    if ($value['CARDSTATUS'] < $card_status) {
                        if ($card_status == 3) {
                            //CRM通知信息
                            $tlfcard_status = 2;
                            $tlfcard_signtime = time();
                            $tlfcard_backtime = 0;

                            //提交CRM数据
                            $crm_api_arr = array();
                            $crm_api_arr['username'] = urlencode($value['REALNAME']);
                            $crm_api_arr['mobile'] = $value['MOBILENO'];
                            $crm_api_arr['activefrom'] = 104;
                            $crm_api_arr['importfrom'] = urlencode('团立方监控后台');
                            $crm_api_arr['city'] =$city_info[$value['CITY_ID']];
                            $crm_api_arr['activename'] = urlencode($activename);
                            $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                            $crm_api_arr['tlfcard_creattime'] = strtotime(oracle_date_format($member_info['CREATETIME'], 'Y-m-d'));
                            $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                            $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                            $crm_api_arr['tlf_username'] = urlencode($this->uname);
                            $crm_api_arr['projectid'] = $value['PRJ_ID'];
                            //$crm_api_arr['floor_id'] = $pro_listid;
                            $crm_api_arr['pay_time'] = strtotime($lead_time);

                            $crm_url = submit_crm_data_by_api_url($crm_api_arr);
                            $ret_log = api_log($value['CITY_ID'],$crm_url,0,$this->uid,2);
                        }
                    }

                    //状态回退，异常
                    if ($value['CARDSTATUS'] > $card_status) {
                        if ($value['CARDSTATUS'] > 2) {
                            switch ($card_status) {
                                case '1':
                                case '2':
                                    $tlfcard_status = 1;
                                    $tlfcard_signtime = 0;
                                    $tlfcard_backtime = 0;
                                    break;
                            }

                            //提交CRM数据
                            $crm_api_arr = array();
                            $crm_api_arr['username'] = urlencode($value['REALNAME']);
                            $crm_api_arr['mobile'] = $value['MOBILENO'];
                            $crm_api_arr['activefrom'] = 104;
                            $crm_api_arr['activename'] = urlencode($activename);
                            $crm_api_arr['city'] = $city_info[$value['CITY_ID']];
                            $crm_api_arr['importfrom'] = urlencode('团立方监控后台');
                            $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                            $crm_api_arr['tlfcard_creattime'] = strtotime($value['CREATETIME']);
                            $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                            $crm_api_arr['tlf_username'] = urlencode($this->uname);
                            $crm_api_arr['projectid'] = $value['PRJ_ID'];
                            $crm_api_arr['pay_time'] = strtotime($lead_time);

                            $crm_url = submit_crm_data_by_api_url($crm_api_arr);
                            $ret_log = api_log($value['CITY_ID'],$crm_url,0,$this->uid,2);
                        }
                    }

                    //全链条精准导购系统
                    $qltStatus = 3;

                    switch($card_status)
                    {
                        case '1':
                            $qltStatus = 3;
                            break;
                        case '2':
                            $qltStatus = 4;
                            break;
                        case '3':
                            $qltStatus = 5;
                            break;
                    }

                    $queryRet = M('Erp_project')->field('CONTRACT')
                        ->where('ID='.$value['PRJ_ID'])->find();

                    $qltContract = $queryRet['CONTRACT'];

                    $qltApiUrl = QLTAPI . '###serviceName=updateStatus###status=' . $qltStatus . '###contract=' . $qltContract . '###phone=' . $value['MOBILENO'];
                    api_log($this->channelid,$qltApiUrl,0,$this->uid,3);

                }
            }
            
            if( $result > 0)
            {
                D()->commit();
                $info['state']  = 1;
                $info['msg']  = '批量修改状态成功';
            }
            else
            {
                D()->rollback();
                $info['state']  = 0;
                $info['msg']  = '批量修改状态失败!';
            }
        }
        else
        {
            D()->rollback();
            $info['state']  = 0;
            $info['msg']  = '请至少选择一条记录!';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
    }

    /**
     * 同步到发放记录中
     */
    private function syncAssocTable($reimDetail, $toUpdateArr) {
        if (empty($reimDetail) || empty($reimDetail['TYPE'])) {
            return;
        }

        // 如果是现金发放类型
        if ($reimDetail['TYPE'] && $reimDetail['TYPE'] == self::CASH_PAYMENT_REIM) {
            $localeGrantedModel = D('LocaleGranted');
            $localeGrantedModel->startTrans();
            $updated = $localeGrantedModel->where('ID = ' . $reimDetail['BUSINESS_ID'])->save($toUpdateArr);
            if ($updated !== false) {
                $localeGrantedModel->commit();
            } else {
                $localeGrantedModel->rollback();
            }
        }
    }

    //电商办卡客户锁定解锁，会员锁定0，会员解锁1
    public function lock_unlock(){
        //返回数据结构
        $return = array(
            'state'=>false,
            'msg'=>'操作失败',
            'data'=>null,
        );
        $contract = M("Erp_income_contract")->field('ID')->select();
        $type = $_REQUEST['type'];
        $midList = $_REQUEST['memberId'] ? $_REQUEST['memberId']: "0";
        if($type ==  0){
            if(count($midList) > 0){
                foreach($midList as $mid){
                    $data['LOCK_UNLOCK'] = 0;
                    $res = M("Erp_cardmember")->where("ID=".$mid)->save($data);
                }
            }
        }else{
            if(count($midList) > 0){
                foreach($midList as $mid){
                    $data['LOCK_UNLOCK'] = 1;
                    $res = M("Erp_cardmember")->where("ID=".$mid)->save($data);
                }
            }
        }
        if($res > 0){
            $return = array(
                'state'=> True,
                'msg'=>'操作成功',
                'data'=>null,
            );
        }
        die(json_encode(g2u($return)));
    }
 }
 
/* End of file MemberAction.class.php */
/* Location: ./Lib/Action/MemberAction.class.php */