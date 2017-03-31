<?php

/**
 * 报销控制器
 *
 * @author liuhu
 */
class ReimbursementAction extends ExtendAction{
    
    /*合并当前模块的URL参数*/
    private $_merge_url_param = array();

    private $reimListId = null;  // 报销申请id
    private $reimType = null;  // 报销类型
    
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
        
        //TAB URL参数
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? intval($_GET['prjid']) : 0;
        !empty($_GET['TAB_NUMBER']) ? $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']) : '';
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = intval($_GET['CASEID']) : '';
        !empty($_GET['CASE_TYPE']) ? $this->_merge_url_param['CASE_TYPE'] = strip_tags($_GET['CASE_TYPE']) : '';
		!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;
    }
    
    
    /**
     +----------------------------------------------------------
     * 申请报销申请
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function apply_agency_reward_reim()
    {
    	//当前用户编号
    	$uid = intval($_SESSION['uinfo']['uid']);
    	//当前用户姓名
    	$user_truename = strip_tags($_SESSION['uinfo']['tname']);
    	//当前城市编号
    	$city_id = intval($this->channelid);
    	
    	//申请报销的会员
		$memberId_arr = $_POST['memberId'];
        $apply_num = count($memberId_arr);
        
        //申请报销类型字符串
        $reim_type_str = strip_tags($_POST['reim_type_str']);
        $reim_type = 0;
        switch ($reim_type_str)
        {
        	case 'agency_reward_reim':
        		$reim_type = 3;
        		$reim_fild = 'AGENCY_REWARD';
        		break;
        	case 'agency_deal_reward_reim':
        		$reim_type = 4;
        		$reim_fild = 'AGENCY_DEAL_REWARD';
        		break;
        	case 'property_deal_reward_reim':
        		$reim_type = 6;
        		$reim_fild = 'PROPERTY_DEAL_REWARD';
        		break;
			case 'agency_reward_reim_fx':
        		$reim_type = 9;
        		$reim_fild = 'AGENCY_REWARD';
        		break;
        	case 'agency_deal_reward_reim_fx':
        		$reim_type = 10;
        		$reim_fild = 'AGENCY_DEAL_REWARD';
        		break;
        	case 'property_deal_reward_reim_fx':
        		$reim_type = 12;
        		$reim_fild = 'PROPERTY_DEAL_REWARD';
        		break;
			case 'out_reward_reim':
				$reim_type =  21;
        		$reim_fild = 'OUT_REWARD';
        		break;
			case 'out_reward_reim_fx':
				$reim_type =  25;
        		$reim_fild = 'OUT_REWARD';
        		break;
        }
        
		if($apply_num == 0)
		{
			$info['state']  = 0;
			$info['msg']  = '报销申请添加失败，至少选择一条会员信息';
		}
		else if($reim_type == 0)
		{
			$info['state']  = 0;
			$info['msg']  = '报销申请添加失败，报销参数异常';
		}
		else
		{	
			/***查询提交的会员信息，添加信息到报销申请表中***/
			$memeber_model = D('Member');
			$search_field = array();
			$member_info = $memeber_model->get_info_by_ids($memberId_arr, $search_field);
			
			$arr_source_no_agency = array();
			$arr_reim_applied = array();
			$arr_reim_data = array();
            $not_fee_arr = array();
            
			if(is_array($member_info) && !empty($member_info))
			{	
				//报销申请单MODEL
				$reim_list_model = D('ReimbursementList');
				//报销明细MODEL
				$reim_detail_model = D('ReimbursementDetail');
                //项目立项信息MODEl
                $house_model = D('House');
                
                //报销明细状态数组
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status();
				
                //付款未完成数量
                $pay_not_complete_num = 0;
                //未签约客户数量
                $not_sign_num = 0;
                //发票状态不允许报销数量
                $invoice_disable_reim_num = 0;
				//中介成交奖励报销数量
				$agency_deal_reward_num = 0;
				//置业顾问成交奖励报销
                $propetry_deal_reward_num = 0;
				//外部成交奖励报销数量
				$out_reward_num = 0;
                //发票状态
                $conf_invoice_status = $memeber_model->get_conf_invoice_status();

				/****检查会员来源是否中介，并查看是否已经申请报销（申请中，已报销)****/
				foreach ($member_info as $key => $value)
				{
					if($value['AGENCY_DEAL_REWARD_STATUS'] >1 && $reim_fild == 'AGENCY_DEAL_REWARD'){
						$agency_deal_reward_num ++;
						break;
					}
					if($value['PROPERTY_DEAL_REWARD_STATUS'] >1 && $reim_fild == 'PROPERTY_DEAL_REWARD'){
						$propetry_deal_reward_num ++;
						break;
					}
					if($value['OUT_REWARD_STATUS'] >1 && $reim_fild == 'OUT_REWARD'){
						$out_reward_num ++;
						break;
					}
					
					//已办卡已签约、未缴纳缴金额为0、发票为已开或已开未领可以申请
					if($value['PAID_MONEY'] <= 0 || $value['UNPAID_MONEY'] > 0)
					{
						$pay_not_complete_num ++;
						break;
					}
					
					if($value['CARDSTATUS'] != 3)
					{
						$not_sign_num ++ ;
						break;
					}
					
					if($value['INVOICE_STATUS'] != $conf_invoice_status['invoiced'] && 
						$value['INVOICE_STATUS'] != $conf_invoice_status['has_taken'] )
					{
						$invoice_disable_reim_num ++;
						break;
					}


					//如果类型为6或者来源是中介
					if(($value['SOURCE'] == 1 || $value['SOURCE'] == 7 || $value['SOURCE'] == 8 || $reim_type == 6 || $reim_type == 12 || $reim_type == 21|| $reim_type == 25) && $value[$reim_fild] > 0)
					{	
						//查询当前用户是否已经申请过该申请
						$is_applied = 
                            $reim_detail_model->is_exisit_reim_detail($value['CASE_ID'], $value['ID'], $reim_type);

						if( !$is_applied )
						{	
                            //根据CASEID查询对应项目是否为资金池项目
                            $isfundpool = $house_model->get_isfundpool_by_prjid($value['PRJ_ID']);
							$arr_reim_data[$value['ID']]['CITY_ID'] = $value['CITY_ID'];
							$arr_reim_data[$value['ID']]['CASE_ID'] = $value['CASE_ID'];
							$arr_reim_data[$value['ID']]['BUSINESS_ID'] = $value['ID'];
                            $arr_reim_data[$value['ID']]['BUSINESS_PARENT_ID'] = $value['ID'];
							$arr_reim_data[$value['ID']]['MONEY'] = $value[$reim_fild];
							$arr_reim_data[$value['ID']]['STATUS'] = 
                                                $reim_detail_statu_arr['reim_detail_no_sub'];
							$arr_reim_data[$value['ID']]['APPLY_TIME'] = date('Y-m-d H:i:s');
							$arr_reim_data[$value['ID']]['ISFUNDPOOL'] = ($isfundpool == TRUE) ? 1 : 0;
							// $arr_reim_data[$value['ID']]['ISKF'] = 0;//默认扣非
							$arr_reim_data[$value['ID']]['ISKF'] = 1;//默认扣非
                            $arr_reim_data[$value['ID']]['TYPE'] =  $reim_type;
                            $arr_reim_data[$value['ID']]['FEE_ID'] =  39;
						}
						else
						{
							$arr_reim_applied[$value['ID']] = $value['REALNAME'];
						}
					}
					else if($value['SOURCE'] != 1)
					{   
                        //来源不为中介
						$arr_source_no_agency[$value['ID']] = $value['REALNAME'];
					}
                    else
                    {   
                        //费用未填写
                        $not_fee_arr[$value['ID']] = $value['REALNAME'];
                    }
				}
				if($agency_deal_reward_num > 0)
				{
					$info['state']  = 0;
					$info['msg']  = '报销申请添加失败，已申请过中介成交奖励报销';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
				if($propetry_deal_reward_num > 0)
				{
					$info['state']  = 0;
					$info['msg']  = '报销申请添加失败，已申请过置业顾问成交奖励报销';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
				if($out_reward_num > 0)
				{
					$info['state']  = 0;
					$info['msg']  = '报销申请添加失败，已申请过外部成交奖励报销';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
				
				if($pay_not_complete_num > 0)
				{
					$info['state']  = 0;
					$info['msg']  = '报销申请添加失败，已缴纳金额为0,或未缴纳金额大于0的会员无法申请报销';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
				
				if($not_sign_num > 0)
				{
					$info['state']  = 0;
					$info['msg']  = '报销申请添加失败，办卡状态为已办卡已签约的会员才允许申请报销';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
				
				if($invoice_disable_reim_num > 0)
				{
					$info['state']  = 0;
					$info['msg']  = '报销申请添加失败，发票为已开或已开未领状态的会员才允许申请报销';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
                
				if(is_array($arr_reim_data) && !empty($arr_reim_data))
				{   
                    //查询当前用户未提交同类型报销申请单
					$last_reim_info = $reim_list_model->get_last_reim_list($uid, $reim_type, $city_id);
                    
					if(empty($last_reim_info))
					{
						$reim_list_arr = array();
						//$reim_list_arr['AMOUNT'] =  $total_amount;
						$reim_list_arr['TYPE'] =  $reim_type;
						$reim_list_arr['APPLY_UID'] =  $uid;
						$reim_list_arr['APPLY_TRUENAME'] =  $user_truename;
						$reim_list_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
						$reim_list_arr['CITY_ID'] =  $city_id;
						
						$last_reim_id = $reim_list_model->add_reim_list($reim_list_arr);
					}
                    else
                    {
                        $last_reim_id = $last_reim_info['ID'];
                    }
                    
                    //添加成功数量
					$add_sucess_num = 0;
                    //添加失败个数
                    $add_fail_num = 0;
                    
					foreach($arr_reim_data as $r_key => $r_value)
					{

						//更新状态值（申请中）
						$update_status_arr = array();
						switch($reim_type){
							case 3:
								$update_status_arr['AGENCY_REWARD_STATUS'] = 2;
								break;
							case 4:
								$update_status_arr['AGENCY_DEAL_REWARD_STATUS'] = 2;
								break;
							case 6:
								$update_status_arr['PROPERTY_DEAL_REWARD_STATUS'] = 2;
								break;
							case 9:
								$update_status_arr['AGENCY_REWARD_STATUS'] = 2;
								break;
							case 10:
								$update_status_arr['AGENCY_DEAL_REWARD_STATUS'] = 2;
								break;
							case 12:
								$update_status_arr['PROPERTY_DEAL_REWARD_STATUS'] = 2;
								break;
							case 21:
								$update_status_arr['OUT_REWARD_STATUS'] = 2;
								break;
							case 25:
								$update_status_arr['OUT_REWARD_STATUS'] = 2;
								break;
						}
						D("erp_cardmember")->where("ID = " . $r_key)->save($update_status_arr);

						$r_value['LIST_ID'] = $last_reim_id;
                        
                        //添加报销明细数据
						$reuslt_add = $reim_detail_model->add_reim_details($r_value);
                        
                        //计算报销申请总额度
                        $reuslt_add > 0 ? $total_amount += $r_value['MONEY'] : '';
                        
                        //计算报销明细插入成功次数、插入失败次数
                        $reuslt_add > 0 ? $add_sucess_num ++ : $add_fail_num ++;
					}
                    
                    if($reuslt_add > 0)
                    {   
                        //更新报销申请单金额
                        $up_num = 
                            $reim_list_model->update_reim_list_amount($last_reim_id, $total_amount);
                        
                        $info['state']  = 1;
                        $info['msg']  = '报销申请成功';
                        
                        if($add_fail_num > 0)
                        {
                            $info['msg'] .= ',共申请'.$apply_num.'条,添加成功'.
                                    $add_sucess_num.'条，添加失败'.$add_fail_num.'条';
                        }
                        
                        $applied_num = count($arr_reim_applied);
                        if(  $applied_num > 0)
                        {
                            $info['msg'] .= ',已申请过报销的会员'.$applied_num.'条';
                        }
                        
                        $no_agency_num = count($arr_source_no_agency);
                        if(  $no_agency_num > 0)
                        {
                            $info['msg'] .= ',非中介来源会员'.$no_agency_num.'条';
                        }
                        
                        $not_fee_num = count($not_fee_arr);
                        if( $not_fee_num > 0)
                        {
                            $info['msg'] .= '报销费用金额未填写,'.$not_fee_num.'条';
                        }
                        
                        if($up_num == FALSE)
                        {
                            $info['msg'] .= ',报销申请单额度更新失败';
                        }
                    }
                    else
                    {
                        $info['state']  = 0;
                        $info['msg']  = '报销申请添加失败';
                    }
				}
                else 
                {
                    $info['state']  = 0;
                    $info['msg']  = '报销失败，申请报销的会员需符合以下条件：<br>'
                            . '1、办卡状态为已办卡已签约；<br>'
                            . '2、办卡会员未缴纳缴金额为0；<br>'
                            . '3、办卡会员发票为已开或已开未领；<br>'
                            . '4、已填写需要报销费用；<br>'
                            . '5、未报销过该费用;<br>';
					if($reim_type != 6 &&  $reim_type != 12 &&  $reim_type != 21&&  $reim_type != 25){
						$info['msg'] .= "6、会员来源必须是中介;";
					}
                }
			}
			else
			{
				$info['state']  = 0;
				$info['msg']  = '报销申请添加失败，未查到报销信息';
			}
		}
		
		$info['msg'] = g2u($info['msg']);
		echo json_encode($info);
		exit;
    }
    
    
    /**
     +----------------------------------------------------------
     * 申请分销会员报销申请
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function apply_agency_reward_reim_fx()
    {
    	//当前用户编号
    	$uid = intval($_SESSION['uinfo']['uid']);
    	//当前用户姓名
    	$user_truename = strip_tags($_SESSION['uinfo']['tname']);
    	//当前城市编号
    	$city_id = intval($this->channelid);
    	
    	//申请报销的会员
		$memberId_arr = $_POST['memberId'];
        $apply_num = count($memberId_arr);
        //$feescale = D("Feescale");
        //盛情报销类型字符串
        $reim_type_str = strip_tags($_POST['reim_type_str']);
        $reim_type = 0;
        switch ($reim_type_str)
        {
        	case 'agency_reward_reim':
        		$reim_type = 9;
        		$reim_field = 'AGENCY_REWARD';
        		break;
        	case 'agency_deal_reward_reim':
        		$reim_type = 10;
        		$reim_field = 'AGENCY_DEAL_REWARD';
        		break;
        	case 'property_deal_reward_reim':
        		$reim_type = 12;
        		$reim_field = 'PROPERTY_DEAL_REWARD';
        		break;
        }
		
		if($apply_num == 0)
		{
			$info['state']  = 0;
			$info['msg']  = '报销申请添加失败，至少选择一条分销会员信息';
		}
		else if($reim_type == 0)
		{
			$info['state']  = 0;
			$info['msg']  = '报销申请添加失败，报销参数异常';
		}
		else
		{	
			/***查询提交的会员信息，添加信息到报销申请表中***/
			$memeber_distribution_model = D('MemberDistribution');
			$search_field = array('ID', 'CITY_ID', 'PRJ_ID', 'CASE_ID', 'REALNAME', 
                                'AGENCY_REWARD', 'AGENCY_DEAL_REWARD', 'PROPERTY_DEAL_REWARD','HOUSETOTAL');
			$member_info = $memeber_distribution_model->get_info_by_ids($memberId_arr, $search_field);
            
			$arr_money_empty = array();
			$arr_reim_applied = array();
			$arr_reim_data = array();
            
			if(is_array($member_info) && !empty($member_info))
			{	
				//报销申请单MODEL
				$reim_list_model = D('ReimbursementList');
				//报销明细MODEL
				$reim_detail_model = D('ReimbursementDetail');
                //项目立项信息MODEl
                $house_model = D('House');
                
                //报销明细状态数组
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status();
				
				/****查看是否已经申请报销（申请中，已报销)****/
				foreach ($member_info as $key => $value)
				{
                    if($value[$reim_field] > 0)
                    {
                        //查询当前用户是否已经申请过该申请
                        $is_applied = 
                            $reim_detail_model->is_exisit_reim_detail($value['CASE_ID'], $value['ID'], $reim_type);

                        if( !$is_applied )
                        {	
                           // $search_arr = array('STYPE');
							//$cond_where['CASE_ID'] = $_REQUEST['case_id'];
							//$cond_where['AMOUT'] = $value[$reim_field];
							//$feeone = $feescale->get_info_by_cond($cond_where,$search_arr);var_dump($feeone);
							$feeone = M('Erp_feescale')->where("CASE_ID='".$_REQUEST['case_id']."' and  AMOUNT=". $value[$reim_field])->find();//echo "CASE_ID='".$_REQUEST['case_id']."' and  AMOUNT=". $value[$reim_field];var_dump($feeone);
							if($feeone['STYPE']==1){
								$MONEY = $value['HOUSETOTAL'] * $value[$reim_field]/100;  
							}else $MONEY = $value[$reim_field];

							//根据CASEID查询对应项目是否为资金池项目
                            $isfundpool = $house_model->get_isfundpool_by_prjid($value['PRJ_ID']);

                            $arr_reim_data[$value['ID']]['CITY_ID'] = $value['CITY_ID'];
                            $arr_reim_data[$value['ID']]['CASE_ID'] = $value['CASE_ID'];
                            $arr_reim_data[$value['ID']]['BUSINESS_ID'] = $value['ID'];
                            $arr_reim_data[$value['ID']]['BUSINESS_PARENT_ID'] = $value['ID'];
                            $arr_reim_data[$value['ID']]['MONEY'] = $MONEY;
                            $arr_reim_data[$value['ID']]['STATUS'] = 
                                                $reim_detail_statu_arr['reim_detail_no_sub'];
                            $arr_reim_data[$value['ID']]['APPLY_TIME'] = date('Y-m-d H:i:s');
                            $arr_reim_data[$value['ID']]['ISFUNDPOOL'] = 0;//($isfundpool == TRUE) ? 1 : 0;
                            $arr_reim_data[$value['ID']]['ISKF'] = 1;//默认是扣非
                            $arr_reim_data[$value['ID']]['TYPE'] =  $reim_type;
                            $arr_reim_data[$value['ID']]['FEE_ID'] =  39;
                        }
                        else
                        {
                            $arr_reim_applied[$value['ID']] = $value['REALNAME'];
                        }
                    }
                    else
                    {
                        $arr_money_empty[$value['ID']] = $value['REALNAME'];
                    }
				}
                
				if(is_array($arr_reim_data) && !empty($arr_reim_data))
				{   
                    //查询当前用户未提交同类型报销申请单
					//$last_reim_info = $reim_list_model->get_last_reim_list($uid, $reim_type, $city_id);
                    $sql = "select A.* from ERP_REIMBURSEMENT_LIST  A left join ERP_REIMBURSEMENT_DETAIL  B on A.ID=B.LIST_ID where A.APPLY_UID = '".$uid."' AND A.TYPE = '".$reim_type."' AND "
                . " A.CITY_ID = '".$city_id."' AND A.STATUS = 0 AND B.CASE_ID='".$member_info[0]['CASE_ID']."' ";
					$linfo = M()->query($sql);
					if(empty($linfo[0]))
					{
						$reim_list_arr = array();
						$reim_list_arr['TYPE'] =  $reim_type;
						$reim_list_arr['APPLY_UID'] =  $uid;
						$reim_list_arr['APPLY_TRUENAME'] =  $user_truename;
						$reim_list_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
						$reim_list_arr['CITY_ID'] =  $city_id;
						
						$last_reim_id = $reim_list_model->add_reim_list($reim_list_arr);
					}
                    else
                    {
                        $last_reim_id = $linfo[0]['ID'];
                    }
                    
                    //添加成功数量
					$add_sucess_num = 0;
                    //添加失败个数
                    $add_fail_num = 0;
                    
					foreach($arr_reim_data as $r_key => $r_value)
					{

						//更新状态值（申请中）
						$update_status_arr = array();
						switch($reim_type){
							case 9:
								$update_status_arr['AGENCY_REWARD_STATUS'] = 2;
								break;
							case 10:
								$update_status_arr['AGENCY_DEAL_REWARD_STATUS'] = 2;
								break;
							case 12:
								$update_status_arr['PROPERTY_DEAL_REWARD_STATUS'] = 2;
								break;
						}
						D("erp_member_distribution")->where("ID = " . $r_key)->save($update_status_arr);


						$r_value['LIST_ID'] = $last_reim_id;
                        
                        //添加报销明细数据
						$reuslt_add = $reim_detail_model->add_reim_details($r_value);
                        
                        //计算报销申请总额度
                        $reuslt_add > 0 ? $total_amount += $r_value['MONEY'] : '';
                        
                        //计算报销明细插入成功次数、插入失败次数
                        $reuslt_add > 0 ? $add_sucess_num ++ : $add_fail_num ++;
					}
                    
                    if($reuslt_add > 0)
                    {   
                        //更新报销申请单金额
                        $up_num = 
                            $reim_list_model->update_reim_list_amount($last_reim_id, $total_amount);
                        
                        $info['state']  = 1;
                        $info['msg']  = '报销申请成功';
                        
                        if($add_fail_num > 0)
                        {
                            $info['msg'] .= ',共申请'.$apply_num.'条,添加成功'.
                                    $add_sucess_num.'条，添加失败'.$add_fail_num.'条';
                        }
                        
                        $applied_num = count($arr_reim_applied);
                        
                        if(  $applied_num > 0)
                        {
                            $info['msg'] .= ',已申请过报销的会员'.$applied_num.'条';
                        }
                        
                        if($up_num == FALSE)
                        {
                            $info['msg'] .= ',报销申请单额度更新失败';
                        }
                    }
                    else
                    {
                        $info['state']  = 0;
                        $info['msg']  = '报销申请添加失败';
                    }
				}
                else 
                {
                    $info['state']  = 0;
                    $info['msg']  = '您选择的会员信息不符合报销条件';
                    
                    if(!empty($arr_reim_applied))
                    {
                        $info['msg']  .= ',已申请报销'.count($arr_reim_applied).'条';
                    }
                    
                    if(!empty($arr_money_empty))
                    {
                        $info['msg']  .= ',佣金/成交奖励未填写'.count($arr_money_empty).'条';
                    }
                }
			}
			else
			{
				$info['state']  = 0;
				$info['msg']  = '报销申请添加失败，未查到符合条件的报销信息';
			}
		}
		
		$info['msg'] = g2u($info['msg']);
		echo json_encode($info);
		exit;
    }
    
    
    /**
     +----------------------------------------------------------
     * 现金发放报销申请
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function apply_locale_granted_reim()
    {
        //当前用户编号
    	$uid = intval($_SESSION['uinfo']['uid']);
    	//当前用户姓名
    	$user_truename = strip_tags($_SESSION['uinfo']['tname']);
    	//当前城市编号
    	$city_id = intval($this->channelid);
        
        $reim_type = 7;
    	
    	//申请报销的会员
		$fx_id_arr = $_GET['fx_id'];
        $apply_num = count($fx_id_arr);
        
        if($apply_num > 0)
        {
            $local_granted_model = D('LocaleGranted');
            $granted_info = array();
            $granted_info = $local_granted_model->get_info_by_id($fx_id_arr);
            
            if(is_array($granted_info) && !empty($granted_info))
            {
                //报销申请单MODEL
                $reim_list_model = D('ReimbursementList');
                
                //报销明细MODEL
                $reim_detail_model = D('ReimbursementDetail');
                
                //项目立项信息MODEl
                $house_model = D('House');
                
                //报销明细状态数组
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status();
                
                $arr_no_money = array();
                $arr_reim_applied = array();
				foreach ($granted_info as $key => $value)
				{   
                    /****查看是否已经申请报销（申请中，已报销)****/
					if($value['MONEY'] > 0)
					{	
						//查询当前用户是否已经申请过该申请
						$is_applied = 
                            $reim_detail_model->is_exisit_reim_detail($value['CASE_ID'], $value['ID'], $reim_type);
                        
						if( !$is_applied )
						{	
							$arr_reim_data[$value['ID']]['CITY_ID'] = $value['CITY_ID'];
							$arr_reim_data[$value['ID']]['CASE_ID'] = $value['CASE_ID'];
							$arr_reim_data[$value['ID']]['BUSINESS_ID'] = $value['ID'];
                            $arr_reim_data[$value['ID']]['BUSINESS_PARENT_ID'] = $value['ID'];
							$arr_reim_data[$value['ID']]['MONEY'] = $value['MONEY'];
							$arr_reim_data[$value['ID']]['STATUS'] = 
                                                $reim_detail_statu_arr['reim_detail_no_sub'];
							$arr_reim_data[$value['ID']]['APPLY_TIME'] = date('Y-m-d H:i:s');
							$arr_reim_data[$value['ID']]['ISFUNDPOOL'] = $value['ISFUNDPOOL'];
							$arr_reim_data[$value['ID']]['ISKF'] = $value['ISKF'];
                            $arr_reim_data[$value['ID']]['TYPE'] =  $reim_type;
                            $arr_reim_data[$value['ID']]['FEE_ID'] =  83;
						}
						else
						{
							$arr_reim_applied[$value['ID']] = $value['REALNAME'];
						}
					}
					else
					{
						$arr_no_money[$value['ID']] = $value['REALNAME'];
					}
				}
                
				if(is_array($arr_reim_data) && !empty($arr_reim_data))
				{   
                    //查询当前用户未提交同类型报销申请单
					$last_reim_info = $reim_list_model->get_last_reim_list($uid, $reim_type, $city_id);
                    
					if(empty($last_reim_info))
					{
						$reim_list_arr = array();
						$reim_list_arr['TYPE'] =  $reim_type;
						$reim_list_arr['APPLY_UID'] =  $uid;
						$reim_list_arr['APPLY_TRUENAME'] =  $user_truename;
						$reim_list_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
						$reim_list_arr['CITY_ID'] =  $city_id;
						
						$last_reim_id = $reim_list_model->add_reim_list($reim_list_arr);
					}
                    else
                    {
                        $last_reim_id = $last_reim_info['ID'];
                    }
                    
                    //添加成功数量
					$add_sucess_num = 0;
                    //添加失败个数
                    $add_fail_num = 0;
                    //添加成功的发放记录编号数组
                    $add_sucess = array();
					foreach($arr_reim_data as $r_key => $r_value)
					{
						$r_value['LIST_ID'] = $last_reim_id;
                        
                        //添加报销明细数据
						$reuslt_add = $reim_detail_model->add_reim_details($r_value);
                        
                        //计算报销申请总额度
                        $reuslt_add > 0 ? $total_amount += $r_value['MONEY'] : '';
                        
                        //计算报销明细插入成功次数、插入失败次数
                        $reuslt_add > 0 ? $add_sucess_num ++ : $add_fail_num ++;
                        $reuslt_add > 0 ? $add_sucess[] = $r_value['BUSINESS_ID'] : '';
					}
                    
                    if($reuslt_add > 0)
                    {   
                        //更新发放记录为已申请报销
                        $up_granted_num = 
                            $local_granted_model->sub_granted_to_reim_applied_by_id($add_sucess , $last_reim_id);
                        
                        //更新报销申请单金额
                        $up_num = 
                            $reim_list_model->update_reim_list_amount($last_reim_id, $total_amount);
                        
                        $info['state']  = 1;
                        $info['msg']  = '报销申请成功';
                        
                        if($add_fail_num > 0)
                        {
                            $info['msg'] .= ',共申请'.$apply_num.'条,添加成功'.
                                    $add_sucess_num.'条，添加失败'.$add_fail_num.'条';
                        }
                        
                        $applied_num = count($arr_reim_applied);
                        if(  $applied_num > 0)
                        {
                            $info['msg'] .= ',已申请过报销的'.$applied_num.'条';
                        }
                        
                        $no_moeny_num = count($arr_no_money);
                        if(  $no_moeny_num > 0)
                        {
                            $info['msg'] .= ',报销金额为0'.$no_moeny_num.'条';
                        }
                        
                        if($up_num == FALSE)
                        {
                            $info['msg'] .= ',报销申请单额度更新失败';
                        }
                    }
                }
                else
                {
                    $info['state']  = 0;
                    $info['msg']  = '报销申请添加失败，提交数据不符合报销条件';
                }
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '报销申请添加失败，无相关信息';
            }
        }
        else
        {
            $info['state']  = 0;
			$info['msg']  = '报销申请添加失败，至少选择一条信息';
        }
        
        $info['msg'] = g2u($info['msg']);
		echo json_encode($info);
		exit;
    }

    private function createPurchaseReim($purchase, &$msg) {
        $dbResult = false;
        if (notEmptyArray($purchase)) {
            if (!in_array(intval($purchase['TYPE']), array(1, 2))) {
                $msg = "报销申请添加失败，报销类型无法确认";
                return false;
            }

            // 跳过已添加到报销申请的采购明细
            if (D('ReimbursementDetail')->is_exisit_reim_detail($purchase['CASE_ID'], $purchase['DETAIL_ID'], $purchase['TYPE'])) {
                return true;
            }

            if (intval($purchase['TYPE']) == 1) {
                $reimType = 1;  // 项目下采购报销类型
            } else {
                $reimType = 14;  // 大宗采购报销类型
            }

            // 找到报销申请列表：如果当前用户有未报销列表，则添加到该报销列表中，否则，创建新的报销列表
            // 项目下采购和大宗采购都加入到采购明细表中
            $recentReimListId = $this->findRecentReimListId(1);

            if (intval($recentReimListId)) {
                $reimDetail['CITY_ID'] = $purchase['CITY_ID'];
                $reimDetail['CASE_ID'] = $purchase['CASE_ID'];
                $reimDetail['BUSINESS_ID'] = $purchase['DETAIL_ID'];
                $reimDetail['BUSINESS_PARENT_ID'] = $purchase['REQ_ID'];
                $reimDetail['MONEY'] = (floatval($purchase['PRICE']) * intval($purchase['NUM']));
                $reimDetail['STATUS'] = 0;  // 状态置为未提交
                $reimDetail['APPLY_TIME'] = date('Y-m-d H:i:s');
                $reimDetail['ISFUNDPOOL'] = $purchase['IS_FUNDPOOL'];
                $reimDetail['ISKF'] = $purchase['IS_KF'];//默认扣非
                $reimDetail['TYPE'] =  $reimType;
                $reimDetail['FEE_ID'] =  $purchase['FEE_ID'];
                $reimDetail['LIST_ID'] = $recentReimListId;

                $dbResult = D('ReimbursementDetail')->add_reim_details($reimDetail);
                if ($dbResult !== false) {  // 添加完报销申请的后续操作
                    $dbResult = $this->afterReimDetailAdded(array(
                        'REIM_LIST_ID' => $recentReimListId,
                        'REQ_ID' => $purchase['REQ_ID'],
                        'DETAIL_ID' => $purchase['DETAIL_ID'],
                        'MONEY' => $reimDetail['MONEY']
                    ));
                }
            } else {
                $msg = "获取报销申请列表失败";
                return false;
            }
        }

        return $dbResult;
    }

    private function findRecentReimListId($reimType) {
        if ($reimType == $this->reimType && intval($this->reimListId)) {
            return $this->reimListId;
        }

        $uid = intval($_SESSION['uinfo']['uid']);  // 当前用户编号
        $cityId = intval($this->channelid);  // //当前城市编号
        $userTrueName = strip_tags($_SESSION['uinfo']['tname']);  // 用户姓名

        $recentReimList = D('ReimbursementList')->get_last_reim_list($uid, $reimType, $cityId);
        if (empty($recentReimList)) {
            $reimList = array();
            $reimList['TYPE'] =  $reimType;
            $reimList['APPLY_UID'] =  $uid;
            $reimList['APPLY_TRUENAME'] =  $userTrueName;
            $reimList['APPLY_TIME'] =  date('Y-m-d H:i:s');
            $reimList['CITY_ID'] =  $cityId;

            $id = D('ReimbursementList')->add_reim_list($reimList);
        } else {
            $id = $recentReimList['ID'];
        }

        $this->reimType = $reimType;  // 采购类型
        $this->reimListId = $id;  // 报销单的ID
        return $id;
    }

	/**
	 * 生成报销申请
	 */
    public function apply_purchase_reim() {
        $dbResult = false;
        $purchaseList = $_POST['purchase_list'];
        if (notEmptyArray($purchaseList)) {
            // 分别处理每条采购明细
            D()->startTrans();
            $msg = '';
            foreach ($purchaseList as $k => $v) {
                $dbPurchase = D('PurchaseList')->getPurchaseJoinReq($v);

                if (notEmptyArray($dbPurchase)) {
                    if ($dbPurchase[0]['DETAIL_STATUS'] == 0 || ($dbPurchase[0]['NUM'] == 0 && $dbPurchase[0]['USE_NUM'] == 0)) {
                        $msg = "有未采购的采购明细，请先进行采购";
                        $dbResult = false;
                    } else {
                        if ($dbPurchase[0]['TYPE'] == 1) {  // 业务采购则需要处理成本表  (1:业务采购   2：大宗采购)
                            $dbResult = D('ProjectCost')->insertOrUpdateCostList($v, $msg);
                        } else {
                            $dbResult = true;
                        }

						//如有有领用 并且从置换仓库中获取的话则更新置换仓库的状态
						if($dbResult !== false){
							$dbResult = D('InboundUse')->updateBusinessOperate($dbPurchase[0]['DETAIL_ID'],4);
						}

                        if ($dbResult !== false) { //生成报销单
                            $dbResult = $this->reimPurchaseList($dbPurchase[0], $msg);
                        }

						if($dbResult !== false){ //如果全部领用并且含有置换池的数据则插入原项目40%的收益
							if($dbPurchase[0]['NUM'] == 0 && $dbPurchase[0]['USE_NUM']>0) {
								$dbResult = D('PurchaseList')->insertDisplaceIncome($dbPurchase[0]);
							}
						}
                    }
                } else {
                    $dbResult = false;
                    $msg = "没有找到对应的采购明细：" . $v;
                    break;
                }

                if ($dbResult === false) {
                    break;
                }
            }

            if ($dbResult !== false) {
                D()->commit();
                !empty($msg) or $msg = "生成报销申请成功";
                ajaxReturnJSON(1, g2u($msg));
            } else {
                D()->rollback();
                !empty($msg) or $msg = "生成报销申请失败";
                ajaxReturnJSON(0, g2u($msg));
            }
        }
//        ajaxReturnJSON(200, u2g('调用成功'), $_POST);
    }
    
    /**
     +----------------------------------------------------------
     * 采购合同申请报销
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function apply_purchase_contract_reim()
    {	
    	//当前用户编号
    	$uid = intval($_SESSION['uinfo']['uid']);
    	//当前用户姓名
    	$user_truename = strip_tags($_SESSION['uinfo']['tname']);
    	//当前城市编号
    	$city_id = intval($this->channelid);
    	
    	$reim_type = 1; //项目采购报销类型
        $reim_type_bulk_purchase = 14;  //大宗采购报销类型
        
    	//申请报销的合同编号
    	$contract_ids_arr = $_GET['contract_ids'];
    	$apply_num = count($contract_ids_arr);
        
    	if($apply_num > 0) {
            //查询合同状态，判断合同是否已经签约，不签约的合同无法申请报销
            $purchase_contract_model = D('PurchaseContract');
            $contract_info = $purchase_contract_model->get_contract_info_by_id($contract_ids_arr);
            //签约配置
            $conf_sign = $purchase_contract_model->get_conf_sign();
            //报销配置
            $conf_reim_status = $purchase_contract_model->get_conf_reim_status();
            
            $not_sign_num = 0;
            $reim_applied_num = 0;
            if(is_array($contract_info) && !empty($contract_info)) {
                foreach($contract_info as $key => $value) {
                    if($conf_sign['not_sign'] == $value['ISSIGN']) {
                        $not_sign_num ++;
                        break;
                    }
                    
                    if($conf_reim_status['not_apply'] != $value['REIM_STATUS']) {
                        $reim_applied_num ++;
                        break;
                    }
                }
                
                if($not_sign_num > 0) {
                    $info['state']  = 0;
                    $info['msg']  = g2u('存在未签约合同，无法申请报销');
                    echo json_encode($info);
                    exit;
                }
                
                if($reim_applied_num > 0) {
                    $info['state']  = 0;
                    $info['msg']  = g2u('存在已申请报销或已报销的合同，无法申请报销');
                    echo json_encode($info);
                    exit;
                }
            } else {
                $info['state']  = 0;
                $info['msg']  = g2u('合同信息异常，无法申请报销');
                echo json_encode($info);
                exit;
            }
            
    		//采购明细MODEL
    		$purchase_list_model = D('PurchaseList');
    		$purchase_info = array();
            
            //合同下所有采购明细信息
    		$purchase_info = $purchase_list_model->get_purchase_list_by_contract_id($contract_ids_arr);
    		if(is_array($purchase_info) && !empty($purchase_info)) {
    			//采购申请单MODEL
    			$purchase_model = D('PurchaseRequisition');
    			
    			//报销申请单MODEL
    			$reim_list_model = D('ReimbursementList');
    			
    			//报销明细MODEL
    			$reim_detail_model = D('ReimbursementDetail');
    			
    			//报销明细状态数组
    			$reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status();
                
                //报销类型
                $purchase_type =  $purchase_model->get_conf_purchase_type();
                
    			$arr_no_money = array();
    			$arr_reim_applied = array();
    			$reim_type_last = 0;
                
                //循环采购明细处理
    			foreach ($purchase_info as $key => $value) {
    				if($value['PRICE'] > 0 || $value['USE_TOATL_PRICE'] > 0) {
    					//通过采购单ID，查询CASE_ID|CITY_ID
    					$purchase_requistion_info = array();
    					if($value['PR_ID'] > 0) {
    						$purchase_requistion_info = $purchase_model->get_purchase_by_id($value['PR_ID']);
    					}
    					
    					if(empty($purchase_requistion_info)) {
    						$info['state']  = 0;
    						$info['msg']  = g2u('采购单异常，无法申请报销');
    						echo json_encode($info);
    						exit;
    					}
    					
    					$case_id = !empty($purchase_requistion_info[0]['CASE_ID']) ? 
    									$purchase_requistion_info[0]['CASE_ID'] : 0;
                        
                        //根据采购明细类型确认报销明细类型
                        if($value['TYPE'] == $purchase_type['project_purchase']) {
                            $reim_type_last = $reim_type;
                        } else if($value['TYPE'] == $purchase_type['bulk_purchase']) {
                            $reim_type_last = $reim_type_bulk_purchase;
                        }
                        
                        if($reim_type_last == 0) {
                            $info['state']  = 0;
                            $info['msg']  =  g2u('报销申请添加失败，报销类型无法确认');
                            
                            echo json_encode($info);
                            exit;
                        }
                        
    					//查询改采购明细是否已经申请过报销
    					$is_applied =
    					$reim_detail_model->is_exisit_reim_detail($case_id, $value['ID'], $reim_type_last);
                        
    					if( !$is_applied ) {
    						$arr_reim_data[$value['ID']]['CITY_ID'] = $purchase_requistion_info[0]['CITY_ID'];
    						$arr_reim_data[$value['ID']]['CASE_ID'] = $case_id;
    						$arr_reim_data[$value['ID']]['BUSINESS_ID'] = $value['ID'];
                            $arr_reim_data[$value['ID']]['BUSINESS_PARENT_ID'] = $value['PR_ID'];
    						$arr_reim_data[$value['ID']]['MONEY'] = ($value['PRICE'] * $value['NUM']);
    						$arr_reim_data[$value['ID']]['STATUS'] =
    						$reim_detail_statu_arr['reim_detail_no_sub'];
    						$arr_reim_data[$value['ID']]['APPLY_TIME'] = date('Y-m-d H:i:s');
    						$arr_reim_data[$value['ID']]['ISFUNDPOOL'] = $value['IS_FUNDPOOL'];
    						$arr_reim_data[$value['ID']]['ISKF'] = $value['IS_KF'];//默认扣非
    						$arr_reim_data[$value['ID']]['TYPE'] =  $reim_type_last;
                            $arr_reim_data[$value['ID']]['FEE_ID'] =  $value['FEE_ID'];
    					} else {
                            //已报销的采购
    						$arr_reim_applied[$value['ID']] = $value['ID'];
    					}
    				} else {
                        //金额为0的采购
    					$arr_no_money[$value['ID']] = $value['ID'];
    				}
    			}
    		}
            
    		if(is_array($arr_reim_data) && !empty($arr_reim_data)) {
				//查询当前用户未提交同类型报销申请单
				$last_reim_info = $reim_list_model->get_last_reim_list($uid, $reim_type, $city_id);
                
				if(empty($last_reim_info))
				{
					$reim_list_arr = array();
					$reim_list_arr['TYPE'] =  $reim_type;
					$reim_list_arr['APPLY_UID'] =  $uid;
					$reim_list_arr['APPLY_TRUENAME'] =  $user_truename;
					$reim_list_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
					$reim_list_arr['CITY_ID'] =  $city_id;
						
					$last_reim_id = $reim_list_model->add_reim_list($reim_list_arr);
				}
                else
                {
                   $last_reim_id = $last_reim_info['ID'];
                }
                
				//添加成功数量
				$add_sucess_num = 0;
				//添加失败个数
				$add_fail_num = 0;
                //报销总额
                $total_amount = 0;
                
                //循环插入报销明细表
				foreach($arr_reim_data as $r_key => $r_value)
				{   
					$r_value['LIST_ID'] = $last_reim_id;
                    
					//添加报销明细数据
					$reuslt_add = $reim_detail_model->add_reim_details($r_value);
                    
					//计算报销申请总额度
					$reuslt_add > 0 ? $total_amount += $r_value['MONEY'] : '';
                    
					//计算报销明细插入成功次数、插入失败次数
					$reuslt_add > 0 ? $add_sucess_num ++ : $add_fail_num ++;
				}
                
                //明细插入成功
                if($reuslt_add > 0)
                {
					//更新报销申请单金额
					$up_num = $reim_list_model->update_reim_list_amount($last_reim_id, $total_amount);
                    
                    //更新合同为申请报销中
                    $up_num_contract =
                        $purchase_contract_model->sub_contract_to_reim_applied_by_id($contract_ids_arr, $last_reim_id);
                    
					$info['state']  = 1;
					$info['msg']  = '报销申请成功';
                    $this->_merge_url_param['fromTab'] = 2;
                    $info['forward']  = U('/Purchasing/reim_manage/', $this->_merge_url_param);
                    
					if($add_fail_num > 0)
					{
						$info['msg'] .= ',共申请'.$apply_num.'条,添加成功'.
                                    $add_sucess_num.'条，添加失败'.$add_fail_num.'条';
					}
                    
					if($up_num == FALSE)
					{
                        $info['msg'] .= ',报销申请单额度更新失败';
                    }
				}
            }
			else
            {
				$info['state']  = 0;
				$info['msg']  = '报销申请添加失败，提交数据不符合报销条件';
            }
            
            $applied_num = count($arr_reim_applied);
            if(  $applied_num > 0)
            {
                $info['msg'] .= ',已申请过报销的采购明细'.$applied_num.'条';
            }
            
            $no_moeny_num = count($arr_no_money);
            if(  $no_moeny_num > 0)
            {
                $info['msg'] .= ',报销金额为0的采购明细'.$no_moeny_num.'条';
            }
    	}
    	else
    	{
    		$info['state']  = 0;
    		$info['msg']  = '报销申请添加失败，至少选择一条信息';
    	}
    	
    	$info['msg'] = g2u($info['msg']);
    	echo json_encode($info);
    	exit;
    }	
    
    
    /**
     +----------------------------------------------------------
     * 提交报销申请
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function sub_reim_to_apply()
    {
        //申请报销单编号
		$reim_list_id_arr = $_GET['reim_list_id'];

        if(is_array($reim_list_id_arr) && !empty($reim_list_id_arr))
        {
            //报销申请单MODEL
            $reim_list_model = D('ReimbursementList');
            D()->startTrans();
			//现金带看奖和带看奖无需判断
			$type = M("Erp_reimbursement_list")->where("ID = ".$reim_list_id_arr[0])->getField('TYPE');
			if($type != 7 && $type != 8 ) {
				//判断报销金额是否超出，走超额报销流程
				$loan_case = D("ProjectCase")->get_conf_case_Loan();
				$loan_case_str = implode(",", array_keys($loan_case));

				//1,2,14,15  采购   预算外费用   大宗采购    小蜜蜂采购 支付第三方费用   不做判断
				$reim_sql = "select  C.projectname,A.case_id,sum(money) as money from erp_reimbursement_detail A left join erp_case B on A.case_id = B.id";
				$reim_sql .= " left join erp_project C on B.project_id = C.id";
				$reim_sql .= " where A.status = 0 AND A.type not in(1,2,14,15,16) and list_id = $reim_list_id_arr[0] and B.scaletype in ($loan_case_str)";
				$reim_sql .= " group by C.projectname,A.case_id";

				$reim_data = M("erp_reimbursement_detail")->query($reim_sql);
				$error_str = "";
				foreach ($reim_data as $k => $v) {
					if ($ret_loan_limit = is_overtop_payout_limit($v['CASE_ID'], $v['MONEY'],0,1)) {
						$error_str .= "报销编号为：$reim_list_id_arr[0],项目“" . $v['PROJECTNAME'] . "”超出垫资比例或超出费用预算（总费用>开票回款收入*付现成本率）,是否申请垫资比例流程 " . "<br />";
					}
				}
				//如果有错误直接打回
				if (!empty($error_str)) {
					D()->rollback();
					$info['state'] = 2;
					$info['msg'] = g2u($error_str);
					$info['num'] = $reim_list_id_arr[0];
					die(json_encode($info));
				}
			}
            $update_num = $reim_list_model->sub_reim_list_to_aduit($reim_list_id_arr);
            $reimListStr = '(' . implode(',', $reim_list_id_arr) . ')';
            $scaleTypeSql = <<<SQL
                SELECT  DISTINCT d.list_id, c.scaletype, d.type
                FROM erp_reimbursement_detail d
                LEFT JOIN erp_case c ON c.id = d.case_id
                WHERE d.list_id in {$reimListStr} and c.scaletype = 2
SQL;
            $dbResult = D()->query($scaleTypeSql);
            if (notEmptyArray($dbResult)) {
                // 如果是分销业务，则将相应的中介佣金报销明细做修改
                $filtedReimList = array();
                foreach ($dbResult as $item) {
                    if ($item['TYPE'] == 17) {
                        $filtedReimList []= $item['LIST_ID'];
                    }
                }
                if (notEmptyArray($filtedReimList)) {
                    $filtedReimListStr = '(' . implode(',', $filtedReimList) . ')';
                    $update_num = D('erp_commission_reim_detail')->where("REIM_LIST_ID in {$filtedReimListStr}")->save(array(
                        'STATUS' => 2
                    ));
                }
            }

            //成本MODEL
            $cost_model = D('ProjectCost');
            
            if($update_num > 0)
            {
                D()->commit();
                $info['state']  = 1;
                $info['msg']  = '报销申请提交成功';
            }
            else
            {
                D()->rollback();
                $info['state']  = 0;
                $info['msg']  = '报销申请提交失败';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '报销申请提交失败';
        }
        
        $info['msg'] = g2u($info['msg']);
		echo json_encode($info);
		exit;
    }

    private function afterReimDetailAdded($data) {
        $dbResult = false;
        if (notEmptyArray($data)) {
            // 更新采购明细
            $dbResult = D('PurchaseList')->where("ID = {$data['DETAIL_ID']}")->save(array(
                'STATUS' => 4
            ));

            if ($dbResult !== false) { // 更新申请列表中的费用数目
                $dbResult = D('ReimbursementList')->where("ID = {$data['REIM_LIST_ID']}")->setInc('AMOUNT', $data['MONEY']);
            }
        }

        return $dbResult;
    }

    /**
     * 提交资金池费用报销申请
     */
    public function applyFundPoolCost() {
        $dbResult = false;
        $msg = '申请失败';
        $bizId = intval($_REQUEST['biz_id']);
        if ($bizId) {
            $fundPoolCostData = D('Benefits')->getFundPoolCost($bizId);
            // 判断是否已经报销过了
            if( $fundPoolCostData["ISCOST"] != 1 ) { // 所选记录已申请报销，不能重复申请
                ajaxReturnJSON(false, g2u('该资金池费用已申请报销，不能重复申请'));
            }

            // 向成本表中加入一条成本记录
            // 生成一条报销明细和报销列表

            D()->startTrans();

            $dbResult = D('Benefits')->addFundPoolCostApply($fundPoolCostData);  // 增加一条报销申请的成本
            if ($dbResult !== false) {
                $dbResult = D('ReimbursementList')->addFundPoolReim($fundPoolCostData);
            }

            if ($dbResult !== false) {
                $dbResult = D('Benefits')->where("ID = {$bizId}")->save(array(
                    'ISCOST' => 3,
                    'STATUS' => 3
                ));  // 已提交
            }


			
			 
			//待支付业务费处理
			$sql = "select * from ERP_FINALACCOUNTS t where CASE_ID='".$fundPoolCostData['CASE_ID']."' and TYPE=1";
			$finalaccounts = M()->query($sql);
			$xgfee = $finalaccounts[0]['TOBEPAID_FUNDPOOL'] > $fundPoolCostData['FEE']  ? $finalaccounts[0]['TOBEPAID_FUNDPOOL']-$fundPoolCostData['FEE']  : 0;
			if($xgfee!=$finalaccounts[0]['TOBEPAID_FUNDPOOL'] && $finalaccounts[0]['STATUS']==2){
				D('Erp_finalaccounts')->where("CASE_ID='".$fundPoolCostData['CASE_ID']."' and TYPE=1")->save(array('TOBEPAID_FUNDPOOL'=>$xgfee) );
			}


            if ($dbResult !== false) {
                D()->commit();
                $msg = '申请成功';
            } else {
                D()->rollback();
                $msg = '申请失败';
            }
        }

        ajaxReturnJSON($dbResult, g2u($msg));
    }

    /**
     * 采购明细生成报销申请，如果是部分领用部分购买则要生成报销申请单；如果是全部领用，则直接报销
     * @param $purchaseInfo 采购明细信息
     * @param string $msg 操作结果信息
     * @return bool
     */
    private function reimPurchaseList($purchaseInfo, &$msg) {
        $result = false;
        if (notEmptyArray($purchaseInfo)) {
            if ($purchaseInfo['NUM'] == 0 && $purchaseInfo['USE_NUM'] == 0) {
                // 不处理
            } else {
                if (intval($purchaseInfo['NUM']) > 0) {
                    // 采购有购买发生时才生成报销申请
                    $result = $this->createPurchaseReim($purchaseInfo, $msg);
                } else {
                    // 采购全部来自于领用直接报销
                    $result = D('PurchaseList')->where("id = {$purchaseInfo['DETAIL_ID']}")->save(array('STATUS' => 2));

                    if ($result !== false) {
                        if (D('PurchaseList')->is_all_purchased($purchaseInfo['DETAIL_ID'])) {
                            // 采购明细全部采购完成则设置采购申请单为采购完成状态
                            $result = D('PurchaseRequisition')->where("ID = {$purchaseInfo['REQ_ID']}")->save(array('STATUS' => 4));
                        }
                    }

                    if ($result === false) {
                        $msg = '全部领用报销出错';
                    }
                }
            }
        }
        return $result;
    }
}
   
/* End of file ReimbursementAction.class.php */
/* Location: ./Lib/Action/ReimbursementAction.class.php */