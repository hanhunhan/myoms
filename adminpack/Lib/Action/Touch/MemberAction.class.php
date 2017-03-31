<?php
class MemberAction extends ExtendAction{
    private $is_login_from_oa = false;
    private $city_id = 1;
    private $uid = 0;
    private $uname = '';
    private $user_city_py = 'nj';

    //构造函数
    public function __construct() 
    {
        parent::__construct();
        load("@.member_common");
        $this->is_login_from_oa = ($_SESSION['uinfo']['is_login_from_oa']==true) ?true:false;
        $this->city_id = intval($_SESSION['uinfo']['city']);
        $this->uid = intval($_SESSION['uinfo']['uid']);
        $this->uname = trim($_SESSION['uinfo']['uname']);
        $this->tname = trim($_SESSION['uinfo']['tname']);
        $this->user_city_py = trim($_SESSION['uinfo']['city_py']);
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
            $cusname = trim(strip_tags($_POST['cusname']));

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
                    'username'=>urlencode(u2g($cusname)),
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
                $return['msg'] = "新用户已经成功录入";

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
        $projects = $member_model->get_projectinfo_by_uid($this->uid,$this->city_id);

        $this->assign('is_login_from_oa',$this->is_login_from_oa);
        $this->assign('projects',$projects);

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
                            }

							$is_sucess = intval($result['result']);
							$msg = $is_sucess == 1 ? '到场确认成功' : '验证失败，验证码无效或已过期';

                            //正确返回结果
                            $return['status'] = $is_sucess;

                            //到场确认日志记录
                            arrival_confirm_log($customer_id , $truename , $telno , $code , $project_listid , $project_id , $is_from , $is_sucess);

                            //使用统计日志
                            $operate_type = 3;
                            $operate_remark = '到场确认';
                            $operate_user = $this->uid;
                            $from_device = get_user_agent_device('num');
                            submit_user_operate_log($customer_id, $operate_type, $operate_remark, $operate_user, $from_device, $this->city_id, $project_id);
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


        //项目权限
        $projects = $member_model->get_arrivalprojectinfo_by_uid($this->uid,$this->city_id);

        $this->assign('is_login_from_oa',$this->is_login_from_oa);
        $this->assign('form_sub_auth_key',$form_sub_auth_key);
        $this->assign('projects',$projects);
        $this->display('Member:arrival_confirm');
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
    	//实例化会员MODEL
    	$member_model = D('Member');

        $act = isset($_REQUEST['act'])?trim($_REQUEST['act']):'';

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
                $member_info['PRJ_ID'] = $formdata['PRJID'];
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

                $member_info['IS_TAKE'] = $formdata['ISTAKE'];

                $member_info['IS_SMS'] = $formdata['IS_SMS'];

                $member_info['AGENCY_NAME'] = u2g($formdata['AGENCY_NAME']);

                $member_info['TOTAL_PRICE'] = intval($formdata['TOTAL_PRICE']);

                $member_info['PAID_MONEY'] = 0;
                $member_info['UNPAID_MONEY'] = floatval($formdata['TOTAL_PRICE']);

                $member_info['AGENCY_REWARD'] = floatval($formdata['AGENCY_REWARD']);
                $member_info['AGENCY_DEAL_REWARD'] = floatval($formdata['AGENCY_DEAL_REWARD']);
                $member_info['PROPERTY_DEAL_REWARD'] = floatval( $formdata['PROPERTY_DEAL_REWARD']);
                $member_info['OUT_REWARD'] = floatval($formdata['OUT_REWARD']);
                $member_info['DECORATION_STANDARD'] = intval( $formdata['DECORATION_STANDARD']);
                $member_info['LEAD_TIME'] = $formdata['LEAD_TIME'];

                $member_info['ADD_UID'] = $this->uid;
                $member_info['ADD_USERNAME'] = $_SESSION['uinfo']['tname'];
                $member_info_str = serialize($member_info);

                $ret = D("Member")->put_user_config('MEMBER_ADD',$member_info_str,$this->uid);
            }

            if($ret){
                $return['status'] = true;
                $return['msg'] = g2u('亲，保存当前配置成功！');
            }
            die(@json_encode($return));
        }
        
        /***获取会员办卡、开票、发票状态***/
        $status_arr = $member_model->get_conf_all_status_remark();

        //商户编号
        $merchant_arr = array();
        $merchant_info = M('erp_merchant')->where("CITY_ID = '".$this->city_id."'")->select();
        if(is_array($merchant_info) && !empty($merchant_info))
        {
            foreach($merchant_info as $key => $value)
            {
                $large_str = '';
                $value['IS_LARGE'] == 1 ? $large_str .= '[大额]' : '';
                $merchant_arr[$value['MERCHANT_NUMBER']] = $value['MERCHANT_NUMBER'].$large_str;
            }
        }

        //新增会员
        if($this->isPost() && !empty($_POST))
        {

            //返回数据结构
            $return = array(
              'status'=>false,
              'msg'=>'',
              'data'=>null,
            );

            $member_info = array();
            //项目ID
            $member_info['PRJ_ID'] = intval($_POST['PRJID']);
            //城市
            $pro_city_info = $member_model->get_pro_city_py($member_info['PRJ_ID']);
            $pro_city_py = $pro_city_info[$member_info['PRJ_ID']]['py'];

            $member_info['CITY_ID'] = $pro_city_info[$member_info['PRJ_ID']]['city_id'];

            //楼盘ID
            $pro_listid = intval($_POST['LIST_ID']);
            //项目名称
            $member_info['PRJ_NAME'] =  u2g($_POST['PRJ_NAME']);
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_pid($member_info['PRJ_ID'], 'ds');
            $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
            //案例ID
            $member_info['CASE_ID'] = $case_id;
            //会员姓名
            $member_info['REALNAME'] =  u2g($_POST['REALNAME']);
            //手机号码
            $member_info['MOBILENO'] = $_POST['MOBILENO'];
            //看房人手机号
            $member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            //直销人员
            $member_info['DIRECTSALLER'] = u2g($_POST['DIRECTSALLER']);
            //来源
            $member_info['SOURCE'] = $_POST['SOURCE'];
            //经纪公司
            $member_info['AGENCY_NAME'] = u2g($_POST['AGENCY_NAME']);
            //办卡时间
            $member_info['CARDTIME'] = $_POST['CARDTIME'];
            //证件类型
            $member_info['CERTIFICATE_TYPE'] = $_POST['CERTIFICATE_TYPE'];
            //证件号码
            $member_info['CERTIFICATE_NO'] = $_POST['IDCARDNO'];
            //楼栋号
            $member_info['ROOMNO'] =  u2g($_POST['ROOMNO']);
            //房屋总价
            $member_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
            //房屋面积
            $member_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            //办卡状态
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            switch($member_info['CARDSTATUS'])
            {
                case '2':
                    //已认购
                    $member_info['SUBSCRIBETIME'] = date('Y-m-d H:i:s',strtotime(strip_tags($_POST['SUBSCRIBETIME'])));
                    $member_info['SUBSCRIBEDATE'] = date('Y-m-d',strtotime(strip_tags($_POST['SUBSCRIBETIME'])));
                    break;
                case '3':
                    //已签约
                    $member_info['SIGNTIME'] = date('Y-m-d H:i:s',strtotime(strip_tags($_POST['SIGNTIME'])));
                    $member_info['SIGNEDSUITE'] = intval($_POST['SIGNEDSUITE']);
                    break;
            }
            //收据号码
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            //收据编号
            $member_info['RECEIPTNO'] = trim(str_replace(array("，","/","、"),",", $_POST['RECEIPTNO']));
            $member_info['RECEIPTNO'] = preg_replace('/([^0-9])+/',' ',$member_info['RECEIPTNO']);
            $member_info['RECEIPTNO'] = preg_replace('/(\s)+/',' ',$member_info['RECEIPTNO']);
            //发票状态（插入保持未开状态）
            $member_info['INVOICE_STATUS'] = 1;
            //是否带看
            $member_info['IS_TAKE'] = $_POST['ISTAKE'];
            //是否发送短信
            $member_info['IS_SMS'] = $_POST['IS_SMS'];
            //单套收费标准
            $member_info['TOTAL_PRICE'] = intval($_POST['TOTAL_PRICE']);
            //中介佣金
            $member_info['AGENCY_REWARD'] = intval($_POST['AGENCY_REWARD']);
            //中介成交奖励
            $member_info['AGENCY_DEAL_REWARD'] = intval($_POST['AGENCY_DEAL_REWARD']);
            //置业顾问成交奖励
            $member_info['PROPERTY_DEAL_REWARD'] = intval( $_POST['PROPERTY_DEAL_REWARD']);
            //外部成交奖励
            $member_info['OUT_REWARD'] = intval($_POST['OUT_REWARD']);
            //提交人
            $member_info['ADD_UID'] = intval($this->uid);
            //提交人姓名
            $member_info['ADD_USERNAME'] = trim($this->tname);
			//交付时间
			$member_info['LEAD_TIME'] = strip_tags($_POST['LEAD_TIME']);
			//装修标准
            $member_info['DECORATION_STANDARD'] = intval($_POST['DECORATION_STANDARD']);
            //备注
            $member_info['NOTE'] =  u2g($_POST['NOTE']);
            //创建时间
            $member_info['CREATETIME'] = date('Y-m-d h:i:s');
            //附件
            $member_info['ATTACH'] = u2g($_POST['attach']);


            /**数据验证**/
            $returnstr = '';

			if($member_info['PRJ_ID'] == 0)
                $returnstr .= "请选择会员项目！\n";

			if($member_info['MOBILENO'] == '')
                $returnstr .= "请填写购房人手机号！\n";

            if($member_info['REALNAME'] == '')
                $returnstr .= "请填写会员姓名！\n";

            if($member_info['CERTIFICATE_TYPE']==1 && !preg_match("/^(\d{18}|\d{15}|\d{17}(x|X))$/",$member_info['CERTIFICATE_NO']))
                $returnstr .= "请填写正确的身份证号！\n";

            if($member_info['CERTIFICATE_TYPE'] != 1 && $member_info['CERTIFICATE_NO']=='')
                $returnstr .= "证件号码不能为空！\n";

            if(empty($member_info['SOURCE']))
                $returnstr .= "请选择会员来源！\n";

            if($member_info['CARDSTATUS']==3 && ($member_info['SOURCE']==1 || $member_info['SOURCE']==7 || $member_info['SOURCE']==8) && empty($member_info['AGENCY_REWARD']))
                $returnstr .= "会员来源为中介或分销公司,中介佣金必填！\n";

            if(empty($member_info['TOTAL_PRICE']))
                $returnstr .= "请选填写单套收费标准！\n";

            if(empty($member_info['RECEIPTNO']))
                $returnstr .= "请选填写收据编号！\n";
			if($member_info['RECEIPTNO'] ){
				$receiptno = M('Erp_cardmember')->where("RECEIPTNO='".$member_info['RECEIPTNO']."' AND CITY_ID='".$this->channelid."' AND STATUS = 1")->find(); 
				if($receiptno){
					 
					$returnstr .= "添加失败,该城市下已经存在相同的收据编号！\n";
				}
			}

			if($member_info['CITY_ID']==1 && $member_info['CARDSTATUS']==3 && $member_info['ROOMNO']=='')
                $returnstr .= "办卡用户为已办已签约状态，请填写楼栋房号！\n";

			if($member_info['CARDSTATUS']==3 && empty($member_info['SIGNEDSUITE']))
                $returnstr .= "办卡用户为已办已签约状态，请选择签约套数！\n";

			if($member_info['CARDSTATUS']==3 && ($member_info['LEAD_TIME']=='' || empty($member_info['DECORATION_STANDARD'])))
                $returnstr .= "办卡用户为已办已签约状态，请填写交付时间和装修标准！\n";

            //付款方式
            switch(count($_POST['PAYTYPE'])){
                case 1:
                    $member_info['PAY_TYPE'] = $_POST['PAYTYPE'][0];
                    break;
                case 0:
                    $member_info['PAY_TYPE'] = 0;
                    break;
                default:
                    $member_info['PAY_TYPE'] = 4;
                    break;
            }


            /**付款明细**/
            /**已缴纳金额+未缴纳金额**/
            $paid_money = 0;
            $unpaid_money = 0;

            if(!empty($_POST['PAYTYPE']) && is_array($_POST['PAYTYPE']) && $_POST['PAYTYPE'][0] !='') {
                foreach ($_POST['PAYTYPE'] as $key=>$val){
                    //如果是POS机方式
                    if($val==1){
                        if(strlen($_POST['RETRIEVAL'][$key]) != 6){
                            $returnstr .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，6位检索号有误！\n";
                        }
                        if(empty($_POST['MERCHANTNUMBER'][$key])){
                            $returnstr .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，商户编号未选择！\n";
                        }
                        if(empty($_POST['TRADEMONEY'][$key])){
                            $returnstr .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，原始交易金额不能为空！\n";
                        }

                        //判断是否是大额付款(商户编号)
                        if(strpos($merchant_arr[$_POST['MERCHANTNUMBER'][$key]],"大额")!==false){
                            if(strlen($_POST['CVV2'][$key])<10){
                                $returnstr .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，商户编号选择大额，请写全卡号！\n";
                            }
                        }
                        else
                        {
                            if(strlen($_POST['CVV2'][$key]) != 4) {
                                $returnstr .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，请编号卡号后四位！\n";
                            }
                        }
                    }
                    //如果是现金和网银方式
                    else if($val==2 || $val==3){
                        if(empty($_POST['TRADEMONEY'][$key])){
                            $returnstr .= '第' . ($key+1) . '笔,' . "付款方式为现金或者网银的付款明细，原始交易金额不能为空！\n";
                        }
                    }

                    //已缴纳金额
                    $paid_money += $_POST['TRADEMONEY'][$key];
                }
            }

            $member_info['PAID_MONEY'] = $paid_money;
            $member_info['UNPAID_MONEY'] = $member_info['TOTAL_PRICE'] - $paid_money;

            //单套收费标准与缴纳金额的确认
            if($member_info['UNPAID_MONEY']<0)
                $returnstr .= "对不起，您填写的交易金额之和 > 单套收费标准！\n";

            //返回数据验证
            if($returnstr)
            {
                $return['msg'] = g2u($returnstr);
                die(@json_encode($return));
            }

            /**事务开始**/
            $member_model->startTrans();
            $sign = false;
            //返回值
            $insert_member_id = $member_model->add_member_info($member_info);

            if(!$insert_member_id)
                $sign = true;

            //付款明细
            if($insert_member_id > 0){
                if(!empty($_POST['PAYTYPE']) && is_array($_POST['PAYTYPE']) && $_POST['PAYTYPE'][0] !='') {
                    $member_paymet = M('erp_member_payment');
                    foreach($_POST['PAYTYPE'] as $key=>$val){
                        $pay_info = array();
                        $pay_info['MID'] = $insert_member_id;
                        $pay_info['PAY_TYPE'] = $_POST['PAYTYPE'][$key];
                        $pay_info['TRADE_MONEY'] = $_POST['TRADEMONEY'][$key];
                        //原始交易金额
                        $pay_info['ORIGINAL_MONEY'] = $_POST['TRADEMONEY'][$key];
                        $pay_info['ADD_UID'] = $this->uid;
                        $pay_info['TRADE_TIME'] = date('Y-m-d H:i:s',strtotime($_POST['TRADETIME'][$key]));

                        //POS机
                        if($val==1) {
                            $pay_info['RETRIEVAL'] = $_POST['RETRIEVAL'][$key];
                            $pay_info['CVV2'] = $_POST['CVV2'][$key];
                            $pay_info['MERCHANT_NUMBER'] = $_POST['MERCHANTNUMBER'][$key];
                        }

                        $insert_payment_id = $member_paymet->add($pay_info);
                        if(!$insert_payment_id)
                            $sign = true;

                        //添加到项目收益表
                        $income_info['CASE_ID'] = $member_info['CASE_ID'];
                        $income_info['ENTITY_ID'] = $insert_member_id;
                        $income_info['PAY_ID'] = $insert_payment_id;
                        $income_info['INCOME_FROM'] = 1;//电商会员支付
                        $income_info['INCOME'] = $pay_info['TRADE_MONEY'];
                        $income_info['INCOME_REMARK'] = '办卡会员';
                        $income_info['ADD_UID'] = $this->uid;
                        $income_info['OCCUR_TIME'] = $pay_info['TRADE_TIME'];

                        $income_model = D('ProjectIncome');
                        $ret_bft =  $income_model->add_income_info($income_info);

                        if(!$ret_bft)
                            $sign = true;
                    }
                }
            }

            //事务提交
            if(!$sign) {
                $member_model->commit();
                $return['status'] = true;
                $return['msg'] = g2u('添加会员成功！');
            }
            else {
                $member_model->rollback();
                $return['msg'] = g2u('添加会员失败！');
            }

            /**发送短信和数据入CRM**/
            if($insert_member_id > 0)
            {
                //发送短信
                if(isset($member_info['IS_SMS']) && $member_info['IS_SMS'] == 2)
                {   
                    $msg = "尊敬的365会员".$member_info['REALNAME']."，".
                           "您已办卡成功，客服热线400-8181-365";
                    send_sms($msg, $member_info['MOBILENO'], $this->user_city_py);
                }

                //统计日志
                $operate_type = 1;
                $operate_remark = '办卡用户';
                $operate_user = $this->uid;
                $from_device = get_user_agent_device('num');
                submit_user_operate_log($insert_member_id, $operate_type, $operate_remark, $operate_user, $from_device, $this->city_id, $member_info['PRJ_ID']);

                //数据入crm
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
                    $crm_api_arr['city'] = $pro_city_py;
                    //装修标准
                    $conf_zx_standard = $member_model->get_conf_zx_standard();
                    //行为
                    $crm_api_arr['activename'] =  urlencode(u2g($_POST['PRJ_NAME']).
                            $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']].$member_info['CARDTIME'])."-".$conf_zx_standard[$member_info['DECORATION_STANDARD']];
                    //来源
                    $crm_api_arr['importfrom'] = urlencode('团立方监控后台');
                    //支付时间
                    $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = strtotime($member_info['CREATETIME']);
                    $crm_api_arr['tlfcard_signtime'] = strtotime($tlfcard_signtime);
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = trim($this->uname);

                    //项目ID
                    $crm_api_arr['projectid'] = $member_info['PRJ_ID'];
                    
                    if($member_info['CARDSTATUS'] == 3)
                        $crm_api_arr['floor_id'] = $pro_listid;

                    //提交
                    $ret = submit_crm_data_by_api($crm_api_arr);
                }
            }

            /****是否需要到场确认-处理****/
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

                    $userinfo_crm_arr = get_crm_userinfo_by_pid_telno($member_info['PRJ_ID'],
                        $member_info['MOBILENO'], $this->user_city_py);
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
                arrival_confirm_log($customer_id, $member_info['REALNAME'], $member_info['MOBILENO'], $code, $pro_listid, $member_info['PRJ_ID'], $is_from, $is_sucess);
            }
            //返回数据
            die(json_encode($return));
        }

        //办卡日期
        $today = date("Y-m-d",time());
		$now = date("Y-m-d H:i",time());
        //项目权限
        $projects = $member_model->get_projectinfo_by_uid($this->uid,$this->city_id);

        //证件类型
        $certificate = $member_model->get_conf_certificate_type();

       /***添加会员时各状态赋值***/
        //办卡状态
        $card_status = array(
            '1'=>'已办未成交',
            '2'=>'已办已认购',
            '3'=>'已办已签约',
        );
        //收据状态
        $receipt_status = array(
            '2' => "已开未领",
            '3' => "已领",
        );
        //发票状态
        $invoice_status = array(
            '1' => "未开",
        );

        //cookie值
        $selected_project_id = isset($_COOKIE['rt_cookie']['project_id']) ? intval($_COOKIE['rt_cookie']['project_id']) : 0;
        $selected_pro_name = isset($_COOKIE['rt_cookie']['pro_name']) ? iconv('utf8', 'gbk', strip_tags($_COOKIE['rt_cookie']['pro_name'])) : '选择项目';
        $selected_pro_listid = isset($_COOKIE['rt_cookie']['pro_listid']) ? intval($_COOKIE['rt_cookie']['pro_listid']) : 0;

        //获取 用户添加  配置信息
        $user_member_config = D("Member")->get_user_config('MEMBER_ADD',$this->uid);
        $user_member_config = unserialize($user_member_config);

		$this->assign('city_id',$this->city_id);
        $this->assign('is_login_from_oa',$this->is_login_from_oa);
        //添加人
        $this->assign('adduid',$this->uid);

        //保存的表格信息
        $this->assign('user_member_config',$user_member_config);

        //项目信息
        $this->assign('projects',$projects);
        $this->assign('today',$today);
		$this->assign('now',$now);
        $this->assign('certificate',$certificate);
        $this->assign('card_status',$card_status);
        $this->assign('receipt_status',$receipt_status);
        $this->assign('invoice_status',$invoice_status);
        //商户编号
        $this->assign('merchant_arr',$merchant_arr);
        //cookie值（默认项目）
        if($user_member_config)
            $selected_project_id = intval($user_member_config['PRJ_ID']);
        $this->assign('selected_project_id',$selected_project_id);
        $this->assign('selected_pro_name',$selected_pro_name);
        $this->assign('selected_pro_listid',$selected_pro_listid);
        $this->display('Member:reg_member');

    }

    /**
     +---------------------------------------------------------
     * 注册分销办卡会员
     +---------------------------------------------------------
     */
    public function DisRegMember()
    {
        //实例化会员MODEL
        $member_model = D('Member');

        $act = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : '';

        if ($act == 'savecfg') {

            $return = array(
                'status' => false,
                'msg' => '',
                'data' => null,
            );

            $formdata_str = $_POST['formdata'];
            parse_str($formdata_str, $formdata);

            $member_info = array();
            //如果返回数据
            if (!empty($formdata)) {
                $member_info['CITY_ID'] = $formdata['CITY_ID'];
                $member_info['PRJ_ID'] = $formdata['PRJID'];
                $member_info['PRJ_NAME'] = u2g($formdata['PRJ_NAME']);
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
                $member_info['PROPERTY_DEAL_REWARD'] = floatval($formdata['PROPERTY_DEAL_REWARD']);

                $member_info['DECORATION_STANDARD'] = intval($formdata['DECORATION_STANDARD']);
                $member_info['LEAD_TIME'] = $formdata['LEAD_TIME'];
                $member_info['FILINGTIME'] = date('Y-m-d H:i:s',strtotime(strip_tags($formdata['FILINGTIME'])));
                $member_info['AGENCY_REWARD_AFTER'] = $formdata['AGENCY_REWARD_AFTER'];
                $member_info['OUT_REWARD'] = floatval($formdata['OUT_REWARD']);

                $member_info['ADD_UID'] = $this->uid;
                $member_info['ADD_USERNAME'] = $_SESSION['uinfo']['tname'];

                $member_info_str = serialize($member_info);

                $ret = D("Member")->put_user_config('DISMEMBER_ADD', $member_info_str, $this->uid);
            }

            if ($ret) {
                $return['status'] = true;
                $return['msg'] = g2u('亲，保存当前分销会员配置成功！');
            }
            die(@json_encode($return));
        }
        /***获取会员办卡、开票、发票状态***/
        $status_arr = $member_model->get_conf_all_status_remark();

        //商户编号
        $merchant_arr = array();
        $merchant_info = M('erp_merchant')->where("CITY_ID = '" . $this->city_id . "'")->select();
        if (is_array($merchant_info) && !empty($merchant_info)) {
            foreach ($merchant_info as $key => $value) {
                $large_str = '';
                $value['IS_LARGE'] == 1 ? $large_str .= '[大额]' : '';
                $merchant_arr[$value['MERCHANT_NUMBER']] = $value['MERCHANT_NUMBER'] . $large_str;
            }
        }

        //新增会员
        if ($this->isPost() && !empty($_POST)) {
            $project = D('Project');
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_pid($_POST['PRJID'], 'fx');
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
            $member_info = array();
            $member_info['CITY_ID'] = $_POST['CITY_ID'];
            $member_info['PRJ_ID'] = $_POST['PRJID'];
            $member_info['PRJ_NAME'] = u2g($_POST['PRJ_NAME']);
            $case_model = D('ProjectCase');
            $case_info = $case_model->get_info_by_pid($member_info['PRJ_ID'], 'fx');
            $case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;
            $member_info['CASE_ID'] = $case_id;
            $member_info['REALNAME'] = u2g($_POST['REALNAME']);
            $member_info['MOBILENO'] = $_POST['MOBILENO'];
            $member_info['LOOKER_MOBILENO'] = $_POST['LOOKER_MOBILENO'];
            $member_info['SOURCE'] = $_POST['SOURCE'];
            $member_info['CARDTIME'] = $_POST['CARDTIME'];
            $member_info['CERTIFICATE_TYPE'] = $_POST['CERTIFICATE_TYPE'];
            $member_info['CERTIFICATE_NO'] = $_POST['IDCARDNO'];
            $member_info['DIRECTSALLER'] = u2g($_POST['DIRECTSALLER']);
            if ($member_info['CERTIFICATE_TYPE'] == 1) {
                if (!preg_match("/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/", $member_info['CERTIFICATE_NO'])) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败，身份证号码格式不正确！');

                    echo json_encode($result);
                    exit;
                }
            } else if (trim($member_info['CERTIFICATE_NO']) == '') {
                $result['status'] = 0;
                $result['msg'] = g2u('添加失败，证件号码必须填写！');

                echo json_encode($result);
                exit;
            }
            if ($_POST['TOTAL_PRICE'] == '' && $_POST['TOTAL_PRICE_AFTER'] == '') {
                $result['status'] = 0;
                $result['msg'] = g2u('添加失败,请选择前佣收费标准或者后佣收费标准！');
                echo json_encode($result);
                exit;

            } elseif ($_POST['TOTAL_PRICE_AFTER'] == '') {
                //$project = D('Project');
                ////$case_model = D('ProjectCase');
                //$case_info = $case_model->get_info_by_pid($_POST['PRJ_ID'], 'fx');
                //$case_id = !empty($case_info[0]['ID']) ? intval($case_info[0]['ID']) : 0;


                $OUT_REWARD = $project->get_feescale_by_cid_stype($case_id, 3, $_POST['OUT_REWARD']);
                if ($OUT_REWARD) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败,单套收费标准只有前佣的外部成交奖励不能为百分比！');
                }
                $AGENCY_DEAL_REWARD = $project->get_feescale_by_cid_stype($case_id, 4, $_POST['AGENCY_DEAL_REWARD']);
                if ($AGENCY_DEAL_REWARD) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败,单套收费标准只有前佣的中介成交奖励不能为百分比！');
                }
                $PROPERTY_DEAL_REWARD = $project->get_feescale_by_cid_stype($case_id, 5, $_POST['PROPERTY_DEAL_REWARD']);
                if ($PROPERTY_DEAL_REWARD) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败,单套收费标准只有前佣的置业顾问成交奖励不能为百分比！');
                }

                if ($OUT_REWARD || $AGENCY_DEAL_REWARD || $PROPERTY_DEAL_REWARD) {
                    echo json_encode($result);
                    exit;
                }

            }
            if ($_POST['TOTAL_PRICE']) {
                if (!$_POST['RECEIPTSTATUS']) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败, 已选择前佣收费标准的必须选择收据状态！');
                    echo json_encode($result);
                    exit;
                }
                if (!$_POST['RECEIPTNO']) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败, 已选择前佣收费标准的必须填写收据编号！');
                    echo json_encode($result);
                    exit;
                }


            }
            $member_info['ROOMNO'] = u2g($_POST['ROOMNO']);
            $member_info['HOUSETOTAL'] = floatval($_POST['HOUSETOTAL']);
            $member_info['HOUSEAREA'] = floatval($_POST['HOUSEAREA']);
            $member_info['CARDSTATUS'] = $_POST['CARDSTATUS'];
            $member_info['LEAD_TIME'] = strip_tags($_POST['LEAD_TIME']);
            $member_info['DECORATION_STANDARD'] = intval($_POST['DECORATION_STANDARD']);

            switch ($member_info['CARDSTATUS']) {
                case '2':
                    //已认购
                    $member_info['SUBSCRIBETIME'] = date('Y-m-d H:i:s',strtotime(strip_tags($_POST['SUBSCRIBETIME'])));
                    $member_info['SIGNTIME'] = '';
                    $member_info['SIGNEDSUITE'] = '';
                    if ($member_info['SUBSCRIBETIME'] == '' || $member_info['SUBSCRIBETIME'] == '') {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为已办已认购，认购日期必须填写！');

                        echo json_encode($result);
                        exit;
                    }
                    break;
                case '3':
                    //已签约
                    $member_info['SIGNTIME'] = date('Y-m-d H:i:s',strtotime(strip_tags($_POST['SIGNTIME'])));
                    $member_info['SIGNEDSUITE'] = intval($_POST['SIGNEDSUITE']);
                    if ($member_info['SIGNTIME'] == '' || $member_info['SIGNEDSUITE'] == '') {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为已办已签约，签约日期和签约套数必须填写！');
                        echo json_encode($result);
                        exit;
                    }

                    if ($member_info['ROOMNO'] == '' && $_POST['CITY_ID'] == 1) {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为已办已签约，楼栋房号必须填写！');
                        echo json_encode($result);
                        exit;
                    }

                    if ($member_info['LEAD_TIME'] == '' || $_POST['DECORATION_STANDARD'] == '') {
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

                    if ($member_info['BACKTIME'] == '' || $member_info['BACK_UID'] == '') {
                        $result['status'] = 0;
                        $result['msg'] = g2u('添加失败,办卡状态为退卡，退卡日期和退卡经办人必须填写！');
                        echo json_encode($result);
                        exit;
                    }
                    break;
            }

            $member_info['PAY_TYPE'] = $_POST['PAY_TYPE'];
            $member_info['RECEIPTSTATUS'] = $_POST['RECEIPTSTATUS'];
            $member_info['RECEIPTNO'] = trim(str_replace(array("，", "/", "、"), ",", $_POST['RECEIPTNO']));
            $member_info['RECEIPTNO'] = preg_replace('/([^0-9])+/', ' ', $member_info['RECEIPTNO']);
            $member_info['RECEIPTNO'] = preg_replace('/(\s)+/', ' ', $member_info['RECEIPTNO']);
            if ($member_info['RECEIPTNO']) {
                $receiptno = M('Erp_cardmember')->where("RECEIPTNO='" . $member_info['RECEIPTNO'] . "' AND CITY_ID='" . $this->channelid . "' AND STATUS = 1")->find();
                if ($receiptno) {
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
            $member_info['ATTACH'] = u2g($_POST['attach']);
            $member_info['TOTAL_PRICE'] = intval($_POST['TOTAL_PRICE']);
            $member_info['PAID_MONEY'] = 0;
            $member_info['UNPAID_MONEY'] = floatval($_POST['TOTAL_PRICE']);
            $member_info['AGENCY_REWARD'] = floatval($_POST['AGENCY_REWARD']);
            $member_info['AGENCY_DEAL_REWARD'] = floatval($_POST['AGENCY_DEAL_REWARD']);
            $member_info['PROPERTY_DEAL_REWARD'] = floatval($_POST['PROPERTY_DEAL_REWARD']);

            //中介来源
            if (($member_info['SOURCE'] == 1 || $member_info['SOURCE'] == 7 || $member_info['SOURCE'] == 8) && $member_info['CARDSTATUS'] == 3) {
                if ($member_info['AGENCY_REWARD'] == 0 && $_POST['AGENCY_REWARD_AFTER'] == 0) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败,前佣或者后佣中介佣金必须至少填写一个');

                    echo json_encode($result);
                    exit;
                }
            }

            $member_info['ADD_UID'] = intval($_SESSION['uinfo']['uid']);
            $member_info['ADD_USERNAME'] = $_SESSION['uinfo']['tname'];
            $member_info['NOTE'] = u2g($_POST['NOTE']);
            $member_info['AGENCY_NAME'] = u2g($_POST['AGENCY_NAME']);
            $member_info['CREATETIME'] = date('Y-m-d H:i:s');
            $member_info['STATUS'] = 1;

            /****是否需要到场确认****/
            $is_crm_confirm = intval($_POST['is_crm_confirm']);
            $is_fgj_confirm = intval($_POST['is_fgj_confirm']);
            if ($is_crm_confirm == 1 || $is_fgj_confirm == 1) {
                //客户ID
                $customer_id = intval($_POST['customer_id']);
                //验证码
                $code = strip_tags($_POST['code']);
                //数据来源
                $is_from = strip_tags($_POST['is_from']);
                //到场确认
                if ($is_from == 1 && $is_crm_confirm == 1) {
                    $result = arrival_confirm_crm($customer_id, $code);
                } else if ($is_from == 2 && $is_fgj_confirm == 1) {
                    //经纪人ID
                    $ag_id = intval($_POST['ag_id']);
                    //报备ID
                    $cp_id = intval($_POST['cp_id']);
                    //城市参数
                    $user_city_py = $_SESSION['uinfo']['city'];
                    $userinfo_crm_arr = get_crm_userinfo_by_pid_telno($member_info['PRJ_ID'],
                        $member_info['MOBILENO'], $user_city_py);
                    if (is_array($userinfo_crm_arr) && $userinfo_crm_arr['result'] == 1 &&
                        !empty($userinfo_crm_arr['meminfo'])
                    ) {
                        if ($userinfo_crm_arr['meminfo']['codestatus'] != 1) {
                            //CRM中用户ID
                            $customer_id_crm = 0;
                            $customer_id_crm = $userinfo_crm_arr['meminfo']['pmid'];
                            $up_result = update_crm_user_source($customer_id_crm, 5);
                        }
                    }

                    $result = arrival_confirm_fgj($customer_id, $ag_id, $cp_id);
                }

                //记录日志
                $is_sucess = intval($result['result']);
                $msg = $is_sucess == 1 ? '到场确认成功' : '验证失败，验证码无效或已过期';
                //arrival_confirm_log($customer_id, $member_info['REALNAME'], $member_info['MOBILENO'],
                //		$code, $_POST['LIST_ID'], $member_info['PRJ_ID'], $is_from, $is_sucess);
            }
            $member_info['IS_DIS'] = 2;//分销
            $member_info['FILINGTIME'] = date('Y-m-d H:i:s',strtotime(strip_tags($_POST['FILINGTIME'])));
            $member_info['TOTAL_PRICE_AFTER'] = $_POST['TOTAL_PRICE_AFTER'];
            $member_info['AGENCY_REWARD_AFTER'] = $_POST['AGENCY_REWARD_AFTER'];
            $member_info['OUT_REWARD'] = $_POST['OUT_REWARD'];
            $member_info['REWARD_STATUS'] = 1;
            $member_info['OUT_REWARD_STATUS'] = 1;


            /**付款明细**/
            /**已缴纳金额+未缴纳金额**/
            $paid_money = 0;
            $unpaid_money = 0;
            $returnstr = '';
            if(!empty($_POST['PAYTYPE']) && is_array($_POST['PAYTYPE']) && $_POST['PAYTYPE'][0] !='') {
                foreach ($_POST['PAYTYPE'] as $key=>$val){
                    //如果是POS机方式
                    if($val==1){
                        if(strlen($_POST['RETRIEVAL'][$key]) != 6){
                            $returnstr .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，6位检索号有误！\n";
                        }
                        if(empty($_POST['MERCHANTNUMBER'][$key])){
                            $returnstr .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，商户编号未选择！\n";
                        }
                        if(empty($_POST['TRADEMONEY'][$key])){
                            $returnstr .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，原始交易金额不能为空！\n";
                        }

                        //判断是否是大额付款(商户编号)
                        if(strpos($merchant_arr[$_POST['MERCHANTNUMBER'][$key]],"大额")!==false){
                            if(strlen($_POST['CVV2'][$key])<10){
                                $returnstr .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，商户编号选择大额，请写全卡号！\n";
                            }
                        }
                        else
                        {
                            if(strlen($_POST['CVV2'][$key]) != 4) {
                                $returnstr .= '第' . ($key+1) . '笔,' . "付款方式为POS机的付款明细，请编号卡号后四位！\n";
                            }
                        }
                    }
                    //如果是现金和网银方式
                    else if($val==2 || $val==3){
                        if(empty($_POST['TRADEMONEY'][$key])){
                            $returnstr .= '第' . ($key+1) . '笔,' . "付款方式为现金或者网银的付款明细，原始交易金额不能为空！\n";
                        }
                    }

                    //已缴纳金额
                    $paid_money += $_POST['TRADEMONEY'][$key];
                }
            }

            $member_info['PAID_MONEY'] = $paid_money;
            $member_info['UNPAID_MONEY'] = $member_info['TOTAL_PRICE'] - $paid_money;

            //单套收费标准与缴纳金额的确认
            if($member_info['UNPAID_MONEY']<0)
                $returnstr .= "对不起，您填写的交易金额之和 > 前佣+后佣收费标准！\n";

            //返回数据验证
            if($returnstr)
            {
                $return['msg'] = g2u($returnstr);
                die(@json_encode($return));
            }
            /****是否需要到场确认****/
            $insert_id = $member_model->add_member_info($member_info);

            if ($insert_id > 0) {
                if(!empty($_POST['PAYTYPE']) && is_array($_POST['PAYTYPE']) && $_POST['PAYTYPE'][0] !='') {
                    $member_paymet = M('erp_member_payment');
                    foreach($_POST['PAYTYPE'] as $key=>$val){
                        $pay_info = array();
                        $pay_info['MID'] = $insert_id;
                        $pay_info['PAY_TYPE'] = $_POST['PAYTYPE'][$key];
                        $pay_info['TRADE_MONEY'] = $_POST['TRADEMONEY'][$key];
                        //原始交易金额
                        $pay_info['ORIGINAL_MONEY'] = $_POST['TRADEMONEY'][$key];
                        $pay_info['ADD_UID'] = $this->uid;
                        $pay_info['TRADE_TIME'] = date('Y-m-d H:i:s',strtotime($_POST['TRADETIME'][$key]));

                        //POS机
                        if($val==1) {
                            $pay_info['RETRIEVAL'] = $_POST['RETRIEVAL'][$key];
                            $pay_info['CVV2'] = $_POST['CVV2'][$key];
                            $pay_info['MERCHANT_NUMBER'] = $_POST['MERCHANTNUMBER'][$key];
                        }

                        $insert_payment_id = $member_paymet->add($pay_info);
                        if(!$insert_payment_id)
                            $sign = true;

                        //添加到项目收益表
                        $income_info['CASE_ID'] = $member_info['CASE_ID'];
                        $income_info['ENTITY_ID'] = $insert_id;
                        $income_info['PAY_ID'] = $insert_payment_id;
                        $income_info['INCOME_FROM'] = 1;//电商会员支付
                        $income_info['INCOME'] = $pay_info['TRADE_MONEY'];
                        $income_info['INCOME_REMARK'] = '办卡会员';
                        $income_info['ADD_UID'] = $this->uid;
                        $income_info['OCCUR_TIME'] = $pay_info['TRADE_TIME'];

                        $income_model = D('ProjectIncome');
                        $ret_bft =  $income_model->add_income_info($income_info);

                        if(!$ret_bft)
                            $sign = true;
                    }
                }
                //发送短信
                if (isset($member_info['IS_SMS']) && $member_info['IS_SMS'] == 2
                    && $member_info['CARDSTATUS'] < 4
                ) {
                    $msg = "尊敬的365会员" . $member_info['REALNAME'] . "，" . "您已办卡成功,客服热线400-8181-365。";
                    send_sms($msg, $member_info['MOBILENO'], $this->city_config_array[$this->channelid]);
                }

                //crm
                if ($member_info['CARDSTATUS']) {
                    switch ($member_info['CARDSTATUS']) {
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
                    $crm_api_arr['activename'] = urlencode(u2g($_POST['PRJ_NAME']) .
                        $status_arr['CARDSTATUS'][$member_info['CARDSTATUS']] . $member_info['CARDTIME'] . $conf_zx_standard[$_POST['DECORATION_STANDARD']]);
                    $crm_api_arr['importfrom'] = urlencode('团立方监控后台');
                    $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                    $crm_api_arr['tlfcard_creattime'] = strtotime($member_info['CARDTIME']);
                    $crm_api_arr['pay_time'] = strtotime($member_info['LEAD_TIME']);
                    $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                    $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                    $crm_api_arr['tlf_username'] = intval($_SESSION['uinfo']['uname']);
                    $crm_api_arr['projectid'] = $_POST['PRJ_ID'];

                    if ($member_info['CARDSTATUS'] == 3) {
                        $house_info = M('erp_house')->field('PRO_LISTID')->
                        where("PROJECT_ID = '" . $_POST['PRJ_ID'] . "'")->find();

                        $pro_listid = !empty($house_info['PRO_LISTID']) ?
                            intval($house_info['PRO_LISTID']) : '';

                        $crm_api_arr['floor_id'] = $pro_listid;
                    }

                    submit_crm_data_by_api($crm_api_arr);
                }

                $result['status'] = 2;
                $result['msg'] = '添加会员成功';
            } else {
                $result['status'] = 0;
                $result['msg'] = '添加会员失败！@';
            }

            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        //项目权限
        $projects = $member_model->get_projectfxinfo_by_uid($this->uid,$this->city_id);
        //获取 用户添加  配置信息
        $user_member_config = D("Member")->get_user_config('DISMEMBER_ADD',$this->uid);
        $user_member_config = unserialize($user_member_config);

        $this->assign('city_id',$this->city_id);
        $this->assign('is_login_from_oa',$this->is_login_from_oa);
        //添加人
        $this->assign('adduid',$this->uid);

        //保存的表格信息
        $this->assign('user_member_config',$user_member_config);

        //项目信息
        $today = date('Y-m-d');
        $now = date("Y-m-d H:i",time());
        $certificate = $member_model->get_conf_certificate_type();
        //办卡状态
        $card_status = array(
            '1'=>'已办未成交',
            '2'=>'已办已认购',
            '3'=>'已办已签约',
        );
        //收据状态
        $receipt_status = array(
            '2' => "已开未领",
            '3' => "已领",
        );
        //发票状态
        $invoice_status = array(
            '1' => "未开",
        );
        $selected_project_id = isset($_COOKIE['rt_cookie']['project_id']) ? intval($_COOKIE['rt_cookie']['project_id']) : 0;
        $selected_pro_name = isset($_COOKIE['rt_cookie']['pro_name']) ? iconv('utf8', 'gbk', strip_tags($_COOKIE['rt_cookie']['pro_name'])) : '选择项目';
        $selected_pro_listid = isset($_COOKIE['rt_cookie']['pro_listid']) ? intval($_COOKIE['rt_cookie']['pro_listid']) : 0;
        $this->assign('projects',$projects);
        $this->assign('today',$today);
        $this->assign('now',$now);
        $this->assign('certificate',$certificate);
        $this->assign('card_status',$card_status);
        $this->assign('receipt_status',$receipt_status);
        $this->assign('invoice_status',$invoice_status);
        //商户编号
        $this->assign('merchant_arr',$merchant_arr);
        //cookie值（默认项目）
        if($user_member_config)
            $selected_project_id = intval($user_member_config['PRJ_ID']);
        $this->assign('selected_project_id',$selected_project_id);
        $this->assign('selected_pro_name',$selected_pro_name);
        $this->assign('selected_pro_listid',$selected_pro_listid);
        $this->display('Member:dis_reg_member');
    }

    /**
     +----------------------------------------------------------
     * 状态变更
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function show_change_status_window(){

        //操作类型
        $action_type = isset($_REQUEST['action_type'])?strip_tags($_REQUEST['action_type']):'';

        //key值验证
        $form_sub_auth_key = md5("HOUSE365_RONGTONG_".date('Ymd').'_'.$this->uname);

        //实例化会员MODEL
        $member_model = D('Member');

        //获取城市名称
        $city_info = $member_model->get_cityinfo();

        switch ($action_type)
        {
            //搜索客户列表
            case 'serach_user_list':

                //返回用户数据
                $userinfo = array();

                //确认验证码
                $authcode_key = strip_tags($_POST['authcode_key']);

                //用户名
                $truename = strip_tags($_POST['truename']);

                //客户手机号码
                $telno = strip_tags($_POST['telno']);

                if( $authcode_key == $form_sub_auth_key)
                {
                    //搜索用户数据
                    if($truename != '' || $telno != '')
                    {
                        $userinfo = $member_model->get_userlist_by_cond($this->city_id , $truename , $telno);
                    }

                    if(!empty($userinfo)){
                        foreach ($userinfo as $key=>$val) {
                            $userinfo[$key]['CITY_NAME'] = $city_info[$userinfo[$key]['CITY_ID']];
                        }
                    }
                }
                else
                {
                    js_alert('参数错误');
                }
                break;
            //ajax 获取搜索列表
            case 'ajax_serach_user_list':

                $return = array(
                    'status'=>false,
                    'msg'=>'',
                    'data'=>null,
                );

                //确认验证码
                $authcode_key = strip_tags($_POST['authcode_key']);

                //用户名
                $truename = strip_tags($_POST['truename']);

                //客户手机号码
                $telno = strip_tags($_POST['telno']);

                //下页页数
                $page = strip_tags($_POST['next_page']);

                //每页显示条数
                $limit = strip_tags($_POST['perpage_num']);

                $start = ( $page - 1 ) * $limit;

                if( $authcode_key == $form_sub_auth_key)
                {
                    if( ($page - 1) >= 0 )
                    {
                        $userinfo = array('result' => 1,'authcode_key' => $authcode_key);
                        //搜索用户数据
                        if($truename != '' || $telno != '')
                        {
                            $userinfo_temp = $member_model->get_userlist_by_cond($this->city_id , $truename , $telno , $start , $limit);
                        }


                        if (!empty($userinfo_temp) && is_array($userinfo_temp)){

                            //城市数组
                            $city_info = $member_model->get_cityinfo();

                            foreach ($userinfo_temp as $key => $value) {
                                $userinfo['user_list'][$key]['id'] = $value['ID'];
                                $userinfo['user_list'][$key]['realname'] = iconv('GBK', 'UTF-8', $value['REALNAME']);
                                $userinfo['user_list'][$key]['mobileno'] = $value['MOBILENO'];
                                $userinfo['user_list'][$key]['projectname'] = g2u($value['PROJECTNAME']);
                                $userinfo['user_list'][$key]['cityname'] = g2u($city_info[$value['CITY_ID']]);
                            }

                            //返回数据
                            $return['status'] = true;
                            $return['data'] = $userinfo;
                        }
                    }
                }

                die(@json_encode($return));
                break;
            //查看客户详情
            case 'get_userinfo':
                $today = date('Y-m-d');
                //用户ID
                $user_id =  intval($_GET['uid']);
                //key
                $authcode_key = strip_tags($_GET['authcode_key']);

                if( $user_id > 0 && $authcode_key == $form_sub_auth_key)
                {
                    $userinfo = $member_model->get_userinfo_by_uid($user_id);
//                    $userinfo['ATTACH'] = $this->getWorkFlowFiles( $userinfo['ATTACH']);
                    //项目名称
                    $project_name = '';
                    //楼盘ID
                    $pro_listid = 0 ;

                    if(!empty($userinfo))
                    {
                        $prjid = intval($userinfo['PRJ_ID']);
                        //获取项目信息
                        $project_arr = $member_model->get_project_arr_by_pid($prjid);

                        if(is_array($project_arr) && !empty($project_arr))
                        {
                            $project_name = $project_arr[0]['PROJECTNAME'];
                            $pro_listid = $project_arr[0]['PRO_LISTID'];
                        }
                    }


                    $card_status = array();
                    $receipt_status = array();
                    $invoice_status = array();

                    $current_card_status = $userinfo['CARDSTATUS'];

                    //办卡状态
                    switch(intval($current_card_status)){
                        case 1:
                            $card_status = array(
                                '1' => "已办未成交",
                                '2' => "已办已认购",
                                '3' => "已办已签约",
                            );
                            break;
                        case 2:
                            $card_status = array(
                                '2' => "已办已认购",
                                '3' => "已办已签约",
                            );
                            break;
                        case 3:
                            $card_status = array(
                                '3' => "已办已签约",
                            );
                            break;
                        case 4:
                            $card_status = array(
                                '4' => "退卡",
                            );
                            break;
                    }

                    //当前发票状态
                    $current_invoice_status = $userinfo['INVOICE_STATUS'];

                    //发票状态
                    switch(intval($current_invoice_status)){
                        case 1:
                            $invoice_status = array(
                                '1' => "未开",
                            );
                            break;
                        case 2:
                            $invoice_status = array(
                                '2' => "已开未领",
                                '3' => "已领",
                            );
                            break;
                        case 3:
                            $invoice_status = array(
                                '2' => "已开未领",
                                '3' => "已领",
                            );
                            break;
                        case 4:
                            $invoice_status = array(
                                '4' => "已收回",
                            );
                            break;
                        case 5:
                            $invoice_status = array(
                                '1' => "未开",
                                '5' => "申请中",
                            );
                            break;
                    }


                    //收据状态
                    $current_receipt_status = $userinfo['RECEIPTSTATUS'];

                    switch(intval($current_receipt_status)){
                        case 2:
                            $receipt_status = array(
                                '2' => "已开未领",
                                '3' => "已领",
                                '4' => "已收回",
                            );
                            break;
                        case 3:
                            $receipt_status = array(
                                '2' => "已开未领",
                                '3' => "已领",
                                '4' => "已收回",
                            );
                            break;
                        case 4:
                            $receipt_status = array(
                                '4' => "已收回",
                            );
                            break;
                    }

                }
                else
                {
                    js_alert('参数错误');
                }
                break;

            //更新用户状态
            case 'update_user_status':

                $return = array(
                    'status'=>false,
                    'msg'=>'',
                    'data'=>null,
                );

                //账号编号
                $user_id =  intval($_POST['uid']);
                //操作验证码
                $authcode_key = strip_tags($_POST['authcode_key']);
                //办卡状态
                $cardstatus = intval($_POST['cardstatus']);
                //收据状态
                $receiptstatus = intval($_POST['receiptstatus']);
                //发票状态
                $invoicestatus = strip_tags($_POST['invoicestatus']);
                //看房人手机号
                $looker_mobileno = trim(strip_tags($_POST['looker_mobileno']));
                //认购时间
                $subscribetime = trim($_POST['subscribetime']);
                //签约时间
                $signtime = trim($_POST['signtime']);
                //签约套数
                $signedsuite = intval($_POST['signedsuite']);
                //楼栋号
                $roomno = trim($_POST['roomno']);
                //交付时间
                $lead_time = trim($_POST['lead_time']);
                //装修标准
                $decoration_standard = intval($_POST['decoration_standard']);
                //附件
                $attach = u2g($_POST['attach']);
                //获取用户信息
                $memberinfo = $member_model->get_userinfo_by_uid($user_id);

                if($memberinfo) {
                    //操作之前的办卡状态
                    $old_cardstatus = intval($memberinfo['CARDSTATUS']);

                    //用户姓名
                    $realname = trim($memberinfo['REALNAME']);

                    //用户号码
                    $mobileno = trim($memberinfo['MOBILENO']);

                    //来源
                    $source = intval($memberinfo['SOURCE']);

                    //会员创建日期
                    $card_creattime = strtotime(oracle_date_format($memberinfo['CREATETIME']));

                    //项目ID
                    $project_id = intval($memberinfo['PRJ_ID']);
                }

                $projectinfo = $member_model->get_project_arr_by_pid($project_id);

                //项目城市
                $pro_city_info = $member_model->get_pro_city_py($project_id);
                $pro_city_py = $pro_city_info[$project_id]['py'];

                if($projectinfo) {
                    //项目名称
                    $project_name = $projectinfo[0]['PROJECTNAME'];
                    //楼盘ID
                    $pro_listid = $projectinfo[0]['PRO_LISTID'];
                    //合同号
                    $qltContract = $projectinfo[0]['CONTRACT'];
                }

                $returnstr = "";
                if(!$user_id || empty($memberinfo))
                    $returnstr .= "用户信息有误！\n";

                if($looker_mobileno && !preg_match("/^1[3-9]\d{9}$/",$looker_mobileno))
                    $returnstr .= "手机号码格式不正确\n";

                if($authcode_key != $form_sub_auth_key)
                    $returnstr .= "参数验证不正确\n";

                //如果办卡状态是‘已办认购，已办已签约’,时间需要判断
                if($cardstatus==2 && !$subscribetime)
                    $returnstr .= "办卡状态为认购，认购时间必填！\n";

                if($cardstatus==3 && !$signtime)
                    $returnstr .= "办卡状态为签约，签约时间必填！\n";

                if($memberinfo['CITY_ID']==1 && $cardstatus==3 && $roomno=='')
                    $returnstr .= "办卡用户为已办已签约状态，请填写楼栋房号！\n";

                if($cardstatus==3 && empty($signedsuite))
                    $returnstr .= "办卡用户为已办已签约状态，请选择签约套数！\n";

                if($cardstatus==3 && ($lead_time=='' || empty($decoration_standard)))
                    $returnstr .= "办卡用户为已办已签约状态，请填写交付时间和装修标准！\n";

                //数据验证返回
                if($returnstr){
                    $return['msg'] = g2u($returnstr);
                    die(@json_encode($return));
                }

                //更新数据
                $up_arr = array();

                switch($cardstatus)
                {
                    case '2':
                        $up_arr['SUBSCRIBETIME'] = $subscribetime;
                        break;
                    case '3':
                        $up_arr['SIGNTIME'] = $signtime;
                        break;
                }
                $up_arr['CARDSTATUS'] = $cardstatus;
                $up_arr['RECEIPTSTATUS'] = $receiptstatus;
                $up_arr['INVOICE_STATUS'] = $invoicestatus;
                $up_arr['LOOKER_MOBILENO'] = $looker_mobileno;
                $up_arr['UPDATETIME'] = date("Y-m-d h:i:s",time());
                $up_arr['SIGNEDSUITE'] = $signedsuite;
                $up_arr['ROOMNO'] = $roomno;
                $up_arr['LEAD_TIME'] = $lead_time;
                $up_arr['DECORATION_STANDARD'] = $decoration_standard;
                $up_arr['ATTACH'] = $attach;

                //返回值
                $update_member_id = $member_model->update_info_by_id($user_id, $up_arr);

                if(!$update_member_id){
                    $return['msg'] = g2u("更新失败!");
                    die(@json_encode($return));
                }
                else
                {
                    $return['status'] = true;
                    $return['msg'] = g2u("状态更新成功!");

                }

                //状态记录
                $operate_type = 2;
                $operate_remark = '用户状态变更';
                $operate_user = $this->uid;
                $from_device = get_user_agent_device('num');
                submit_user_operate_log($user_id, $operate_type, $operate_remark, $operate_user, $from_device, $this->city_id, $project_id);

                /******办卡状态变更，ＣＲＭ数据状态同步******/
                //装修标准
                $conf_zx_standard = $member_model->get_conf_zx_standard();

                $status_arr = $member_model->get_conf_all_status_remark();

                //如果办卡状态变更
                if($old_cardstatus != $cardstatus)
                {
                    //全链条精准导购系统更新
                    $qltStatus = 3;

                    switch($cardstatus)
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

                    $qltApiUrl = QLTAPI . '###serviceName=updateStatus###status=' . $qltStatus . '###contract=' . $qltContract . '###phone=' . $mobileno;
                    api_log($this->channelid,$qltApiUrl,0,$this->uid,3);

                    //行为
                    $activename = $project_name."办卡状态:{$status_arr['CARDSTATUS'][$cardstatus]}"." 日期：".date("Y-m-d",$card_creattime) . $conf_zx_standard[$decoration_standard];

                    if($old_cardstatus < $cardstatus)
                    {
                        if($cardstatus == 3)
                        {
                            //CRM通知信息
                            $tlfcard_status = 2;
                            $tlfcard_signtime = time();
                            $tlfcard_backtime = 0;

                            //提交CRM数据
                            $crm_api_arr = array();
                            $crm_api_arr['username'] = urlencode($realname);
                            $crm_api_arr['mobile'] = $mobileno;
                            $crm_api_arr['activefrom'] = 104;
                            $crm_api_arr['importfrom'] = urlencode('团立方监控后台');
                            $crm_api_arr['city'] = $pro_city_py;
                            $crm_api_arr['activename'] = urlencode($activename);
                            $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                            $crm_api_arr['tlfcard_creattime'] = $card_creattime;
                            $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                            $crm_api_arr['tlfcard_backtime'] = $tlfcard_backtime;
                            $crm_api_arr['tlf_username'] = urlencode($this->uname);
                            $crm_api_arr['projectid'] = $project_id;
                            $crm_api_arr['floor_id'] = $pro_listid;
                            $crm_api_arr['pay_time'] = strtotime($lead_time);

                            $apiret = submit_crm_data_by_api($crm_api_arr);
                        }
                    }

                    //状态回退，异常
                    if($old_cardstatus > $cardstatus)
                    {
                        if($old_cardstatus > 2)
                        {
                            switch($cardstatus){
                                case '1':
                                case '2':
                                    $tlfcard_status = 1;
                                    $tlfcard_signtime = 0;
                                    $tlfcard_backtime = 0;
                                    break;
                            }

                            //提交CRM数据
                            $crm_api_arr = array();
                            $crm_api_arr['username'] = urlencode($realname);
                            $crm_api_arr['mobile'] = $mobileno;
                            $crm_api_arr['activefrom'] = 104;
                            $crm_api_arr['activename'] = urlencode($activename);
                            $crm_api_arr['city'] = $pro_city_py;
                            $crm_api_arr['importfrom'] = urlencode('团立方监控后台');
                            $crm_api_arr['tlfcard_status'] = $tlfcard_status;
                            $crm_api_arr['tlfcard_creattime'] = $card_creattime;
                            $crm_api_arr['tlfcard_signtime'] = $tlfcard_signtime;
                            $crm_api_arr['tlf_username'] = urlencode($this->uname);
                            $crm_api_arr['projectid'] = $project_id;
                            $crm_api_arr['pay_time'] = strtotime($lead_time);

                            $apiret = submit_crm_data_by_api($crm_api_arr);
                        }
                    }




                }
                /******办卡状态变更，ＣＲＭ数据状态同步******/
                die(@json_encode($return));
                break;
        }
        
        $this->assign('is_login_from_oa',$this->is_login_from_oa);
        $this->assign('form_sub_auth_key',$form_sub_auth_key);
        
        //时间
        $this->assign('today',$today);
        
        //搜索的用户信息
        $this->assign('truename',$truename);
        $this->assign('telno',$telno);
        
        //数据获取用户信息
        $this->assign('userinfo',$userinfo);
        
        //项目信息
        $this->assign('project_name',$project_name);
        $this->assign('pro_listid',$pro_listid);
        
        //办卡信息
        $this->assign('card_status',$card_status);
        $this->assign('receipt_status',$receipt_status);
        $this->assign('invoice_status',$invoice_status);
        
        //用户当前办卡信息
        $this->assign('current_card_status',$current_card_status);
        $this->assign('current_invoice_status',$current_invoice_status);
        $this->assign('current_receipt_status',$current_receipt_status);

        //操作类型
        $this->assign('action_type',$action_type);

        $this->display('Member:status_change');
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
            //项目ID
            $project_id = intval($_POST['project_id']);
            //电话号码
            $telno = strip_tags($_POST['telno']);
            //楼盘ID
            $pro_listid = isset($_POST['pro_listid']) ? intval($_POST['pro_listid']) : 0;

            $userinfo = get_userinfo_by_pid_telno($project_id, $telno, $pro_listid, $this->user_city_py);

            die(@json_encode($userinfo));
        }
    }

 }