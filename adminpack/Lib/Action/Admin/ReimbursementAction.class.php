<?php

/**
 * ����������
 *
 * @author liuhu
 */
class ReimbursementAction extends ExtendAction{
    
    /*�ϲ���ǰģ���URL����*/
    private $_merge_url_param = array();

    private $reimListId = null;  // ��������id
    private $reimType = null;  // ��������
    
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
        
        //TAB URL����
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? intval($_GET['prjid']) : 0;
        !empty($_GET['TAB_NUMBER']) ? $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']) : '';
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = intval($_GET['CASEID']) : '';
        !empty($_GET['CASE_TYPE']) ? $this->_merge_url_param['CASE_TYPE'] = strip_tags($_GET['CASE_TYPE']) : '';
		!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;
    }
    
    
    /**
     +----------------------------------------------------------
     * ���뱨������
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function apply_agency_reward_reim()
    {
    	//��ǰ�û����
    	$uid = intval($_SESSION['uinfo']['uid']);
    	//��ǰ�û�����
    	$user_truename = strip_tags($_SESSION['uinfo']['tname']);
    	//��ǰ���б��
    	$city_id = intval($this->channelid);
    	
    	//���뱨���Ļ�Ա
		$memberId_arr = $_POST['memberId'];
        $apply_num = count($memberId_arr);
        
        //���뱨�������ַ���
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
			$info['msg']  = '�����������ʧ�ܣ�����ѡ��һ����Ա��Ϣ';
		}
		else if($reim_type == 0)
		{
			$info['state']  = 0;
			$info['msg']  = '�����������ʧ�ܣ����������쳣';
		}
		else
		{	
			/***��ѯ�ύ�Ļ�Ա��Ϣ�������Ϣ�������������***/
			$memeber_model = D('Member');
			$search_field = array();
			$member_info = $memeber_model->get_info_by_ids($memberId_arr, $search_field);
			
			$arr_source_no_agency = array();
			$arr_reim_applied = array();
			$arr_reim_data = array();
            $not_fee_arr = array();
            
			if(is_array($member_info) && !empty($member_info))
			{	
				//�������뵥MODEL
				$reim_list_model = D('ReimbursementList');
				//������ϸMODEL
				$reim_detail_model = D('ReimbursementDetail');
                //��Ŀ������ϢMODEl
                $house_model = D('House');
                
                //������ϸ״̬����
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status();
				
                //����δ�������
                $pay_not_complete_num = 0;
                //δǩԼ�ͻ�����
                $not_sign_num = 0;
                //��Ʊ״̬������������
                $invoice_disable_reim_num = 0;
				//�н�ɽ�������������
				$agency_deal_reward_num = 0;
				//��ҵ���ʳɽ���������
                $propetry_deal_reward_num = 0;
				//�ⲿ�ɽ�������������
				$out_reward_num = 0;
                //��Ʊ״̬
                $conf_invoice_status = $memeber_model->get_conf_invoice_status();

				/****����Ա��Դ�Ƿ��н飬���鿴�Ƿ��Ѿ����뱨���������У��ѱ���)****/
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
					
					//�Ѱ쿨��ǩԼ��δ���ɽɽ��Ϊ0����ƱΪ�ѿ����ѿ�δ���������
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


					//�������Ϊ6������Դ���н�
					if(($value['SOURCE'] == 1 || $value['SOURCE'] == 7 || $value['SOURCE'] == 8 || $reim_type == 6 || $reim_type == 12 || $reim_type == 21|| $reim_type == 25) && $value[$reim_fild] > 0)
					{	
						//��ѯ��ǰ�û��Ƿ��Ѿ������������
						$is_applied = 
                            $reim_detail_model->is_exisit_reim_detail($value['CASE_ID'], $value['ID'], $reim_type);

						if( !$is_applied )
						{	
                            //����CASEID��ѯ��Ӧ��Ŀ�Ƿ�Ϊ�ʽ����Ŀ
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
							// $arr_reim_data[$value['ID']]['ISKF'] = 0;//Ĭ�Ͽ۷�
							$arr_reim_data[$value['ID']]['ISKF'] = 1;//Ĭ�Ͽ۷�
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
                        //��Դ��Ϊ�н�
						$arr_source_no_agency[$value['ID']] = $value['REALNAME'];
					}
                    else
                    {   
                        //����δ��д
                        $not_fee_arr[$value['ID']] = $value['REALNAME'];
                    }
				}
				if($agency_deal_reward_num > 0)
				{
					$info['state']  = 0;
					$info['msg']  = '�����������ʧ�ܣ���������н�ɽ���������';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
				if($propetry_deal_reward_num > 0)
				{
					$info['state']  = 0;
					$info['msg']  = '�����������ʧ�ܣ����������ҵ���ʳɽ���������';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
				if($out_reward_num > 0)
				{
					$info['state']  = 0;
					$info['msg']  = '�����������ʧ�ܣ���������ⲿ�ɽ���������';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
				
				if($pay_not_complete_num > 0)
				{
					$info['state']  = 0;
					$info['msg']  = '�����������ʧ�ܣ��ѽ��ɽ��Ϊ0,��δ���ɽ�����0�Ļ�Ա�޷����뱨��';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
				
				if($not_sign_num > 0)
				{
					$info['state']  = 0;
					$info['msg']  = '�����������ʧ�ܣ��쿨״̬Ϊ�Ѱ쿨��ǩԼ�Ļ�Ա���������뱨��';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
				
				if($invoice_disable_reim_num > 0)
				{
					$info['state']  = 0;
					$info['msg']  = '�����������ʧ�ܣ���ƱΪ�ѿ����ѿ�δ��״̬�Ļ�Ա���������뱨��';
					$info['msg'] = g2u($info['msg']);
					echo json_encode($info);
					exit;
				}
                
				if(is_array($arr_reim_data) && !empty($arr_reim_data))
				{   
                    //��ѯ��ǰ�û�δ�ύͬ���ͱ������뵥
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
                    
                    //��ӳɹ�����
					$add_sucess_num = 0;
                    //���ʧ�ܸ���
                    $add_fail_num = 0;
                    
					foreach($arr_reim_data as $r_key => $r_value)
					{

						//����״ֵ̬�������У�
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
                        
                        //��ӱ�����ϸ����
						$reuslt_add = $reim_detail_model->add_reim_details($r_value);
                        
                        //���㱨�������ܶ��
                        $reuslt_add > 0 ? $total_amount += $r_value['MONEY'] : '';
                        
                        //���㱨����ϸ����ɹ�����������ʧ�ܴ���
                        $reuslt_add > 0 ? $add_sucess_num ++ : $add_fail_num ++;
					}
                    
                    if($reuslt_add > 0)
                    {   
                        //���±������뵥���
                        $up_num = 
                            $reim_list_model->update_reim_list_amount($last_reim_id, $total_amount);
                        
                        $info['state']  = 1;
                        $info['msg']  = '��������ɹ�';
                        
                        if($add_fail_num > 0)
                        {
                            $info['msg'] .= ',������'.$apply_num.'��,��ӳɹ�'.
                                    $add_sucess_num.'�������ʧ��'.$add_fail_num.'��';
                        }
                        
                        $applied_num = count($arr_reim_applied);
                        if(  $applied_num > 0)
                        {
                            $info['msg'] .= ',������������Ļ�Ա'.$applied_num.'��';
                        }
                        
                        $no_agency_num = count($arr_source_no_agency);
                        if(  $no_agency_num > 0)
                        {
                            $info['msg'] .= ',���н���Դ��Ա'.$no_agency_num.'��';
                        }
                        
                        $not_fee_num = count($not_fee_arr);
                        if( $not_fee_num > 0)
                        {
                            $info['msg'] .= '�������ý��δ��д,'.$not_fee_num.'��';
                        }
                        
                        if($up_num == FALSE)
                        {
                            $info['msg'] .= ',�������뵥��ȸ���ʧ��';
                        }
                    }
                    else
                    {
                        $info['state']  = 0;
                        $info['msg']  = '�����������ʧ��';
                    }
				}
                else 
                {
                    $info['state']  = 0;
                    $info['msg']  = '����ʧ�ܣ����뱨���Ļ�Ա���������������<br>'
                            . '1���쿨״̬Ϊ�Ѱ쿨��ǩԼ��<br>'
                            . '2���쿨��Աδ���ɽɽ��Ϊ0��<br>'
                            . '3���쿨��Ա��ƱΪ�ѿ����ѿ�δ�죻<br>'
                            . '4������д��Ҫ�������ã�<br>'
                            . '5��δ�������÷���;<br>';
					if($reim_type != 6 &&  $reim_type != 12 &&  $reim_type != 21&&  $reim_type != 25){
						$info['msg'] .= "6����Ա��Դ�������н�;";
					}
                }
			}
			else
			{
				$info['state']  = 0;
				$info['msg']  = '�����������ʧ�ܣ�δ�鵽������Ϣ';
			}
		}
		
		$info['msg'] = g2u($info['msg']);
		echo json_encode($info);
		exit;
    }
    
    
    /**
     +----------------------------------------------------------
     * ���������Ա��������
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function apply_agency_reward_reim_fx()
    {
    	//��ǰ�û����
    	$uid = intval($_SESSION['uinfo']['uid']);
    	//��ǰ�û�����
    	$user_truename = strip_tags($_SESSION['uinfo']['tname']);
    	//��ǰ���б��
    	$city_id = intval($this->channelid);
    	
    	//���뱨���Ļ�Ա
		$memberId_arr = $_POST['memberId'];
        $apply_num = count($memberId_arr);
        //$feescale = D("Feescale");
        //ʢ�鱨�������ַ���
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
			$info['msg']  = '�����������ʧ�ܣ�����ѡ��һ��������Ա��Ϣ';
		}
		else if($reim_type == 0)
		{
			$info['state']  = 0;
			$info['msg']  = '�����������ʧ�ܣ����������쳣';
		}
		else
		{	
			/***��ѯ�ύ�Ļ�Ա��Ϣ�������Ϣ�������������***/
			$memeber_distribution_model = D('MemberDistribution');
			$search_field = array('ID', 'CITY_ID', 'PRJ_ID', 'CASE_ID', 'REALNAME', 
                                'AGENCY_REWARD', 'AGENCY_DEAL_REWARD', 'PROPERTY_DEAL_REWARD','HOUSETOTAL');
			$member_info = $memeber_distribution_model->get_info_by_ids($memberId_arr, $search_field);
            
			$arr_money_empty = array();
			$arr_reim_applied = array();
			$arr_reim_data = array();
            
			if(is_array($member_info) && !empty($member_info))
			{	
				//�������뵥MODEL
				$reim_list_model = D('ReimbursementList');
				//������ϸMODEL
				$reim_detail_model = D('ReimbursementDetail');
                //��Ŀ������ϢMODEl
                $house_model = D('House');
                
                //������ϸ״̬����
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status();
				
				/****�鿴�Ƿ��Ѿ����뱨���������У��ѱ���)****/
				foreach ($member_info as $key => $value)
				{
                    if($value[$reim_field] > 0)
                    {
                        //��ѯ��ǰ�û��Ƿ��Ѿ������������
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

							//����CASEID��ѯ��Ӧ��Ŀ�Ƿ�Ϊ�ʽ����Ŀ
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
                            $arr_reim_data[$value['ID']]['ISKF'] = 1;//Ĭ���ǿ۷�
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
                    //��ѯ��ǰ�û�δ�ύͬ���ͱ������뵥
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
                    
                    //��ӳɹ�����
					$add_sucess_num = 0;
                    //���ʧ�ܸ���
                    $add_fail_num = 0;
                    
					foreach($arr_reim_data as $r_key => $r_value)
					{

						//����״ֵ̬�������У�
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
                        
                        //��ӱ�����ϸ����
						$reuslt_add = $reim_detail_model->add_reim_details($r_value);
                        
                        //���㱨�������ܶ��
                        $reuslt_add > 0 ? $total_amount += $r_value['MONEY'] : '';
                        
                        //���㱨����ϸ����ɹ�����������ʧ�ܴ���
                        $reuslt_add > 0 ? $add_sucess_num ++ : $add_fail_num ++;
					}
                    
                    if($reuslt_add > 0)
                    {   
                        //���±������뵥���
                        $up_num = 
                            $reim_list_model->update_reim_list_amount($last_reim_id, $total_amount);
                        
                        $info['state']  = 1;
                        $info['msg']  = '��������ɹ�';
                        
                        if($add_fail_num > 0)
                        {
                            $info['msg'] .= ',������'.$apply_num.'��,��ӳɹ�'.
                                    $add_sucess_num.'�������ʧ��'.$add_fail_num.'��';
                        }
                        
                        $applied_num = count($arr_reim_applied);
                        
                        if(  $applied_num > 0)
                        {
                            $info['msg'] .= ',������������Ļ�Ա'.$applied_num.'��';
                        }
                        
                        if($up_num == FALSE)
                        {
                            $info['msg'] .= ',�������뵥��ȸ���ʧ��';
                        }
                    }
                    else
                    {
                        $info['state']  = 0;
                        $info['msg']  = '�����������ʧ��';
                    }
				}
                else 
                {
                    $info['state']  = 0;
                    $info['msg']  = '��ѡ��Ļ�Ա��Ϣ�����ϱ�������';
                    
                    if(!empty($arr_reim_applied))
                    {
                        $info['msg']  .= ',�����뱨��'.count($arr_reim_applied).'��';
                    }
                    
                    if(!empty($arr_money_empty))
                    {
                        $info['msg']  .= ',Ӷ��/�ɽ�����δ��д'.count($arr_money_empty).'��';
                    }
                }
			}
			else
			{
				$info['state']  = 0;
				$info['msg']  = '�����������ʧ�ܣ�δ�鵽���������ı�����Ϣ';
			}
		}
		
		$info['msg'] = g2u($info['msg']);
		echo json_encode($info);
		exit;
    }
    
    
    /**
     +----------------------------------------------------------
     * �ֽ𷢷ű�������
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function apply_locale_granted_reim()
    {
        //��ǰ�û����
    	$uid = intval($_SESSION['uinfo']['uid']);
    	//��ǰ�û�����
    	$user_truename = strip_tags($_SESSION['uinfo']['tname']);
    	//��ǰ���б��
    	$city_id = intval($this->channelid);
        
        $reim_type = 7;
    	
    	//���뱨���Ļ�Ա
		$fx_id_arr = $_GET['fx_id'];
        $apply_num = count($fx_id_arr);
        
        if($apply_num > 0)
        {
            $local_granted_model = D('LocaleGranted');
            $granted_info = array();
            $granted_info = $local_granted_model->get_info_by_id($fx_id_arr);
            
            if(is_array($granted_info) && !empty($granted_info))
            {
                //�������뵥MODEL
                $reim_list_model = D('ReimbursementList');
                
                //������ϸMODEL
                $reim_detail_model = D('ReimbursementDetail');
                
                //��Ŀ������ϢMODEl
                $house_model = D('House');
                
                //������ϸ״̬����
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status();
                
                $arr_no_money = array();
                $arr_reim_applied = array();
				foreach ($granted_info as $key => $value)
				{   
                    /****�鿴�Ƿ��Ѿ����뱨���������У��ѱ���)****/
					if($value['MONEY'] > 0)
					{	
						//��ѯ��ǰ�û��Ƿ��Ѿ������������
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
                    //��ѯ��ǰ�û�δ�ύͬ���ͱ������뵥
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
                    
                    //��ӳɹ�����
					$add_sucess_num = 0;
                    //���ʧ�ܸ���
                    $add_fail_num = 0;
                    //��ӳɹ��ķ��ż�¼�������
                    $add_sucess = array();
					foreach($arr_reim_data as $r_key => $r_value)
					{
						$r_value['LIST_ID'] = $last_reim_id;
                        
                        //��ӱ�����ϸ����
						$reuslt_add = $reim_detail_model->add_reim_details($r_value);
                        
                        //���㱨�������ܶ��
                        $reuslt_add > 0 ? $total_amount += $r_value['MONEY'] : '';
                        
                        //���㱨����ϸ����ɹ�����������ʧ�ܴ���
                        $reuslt_add > 0 ? $add_sucess_num ++ : $add_fail_num ++;
                        $reuslt_add > 0 ? $add_sucess[] = $r_value['BUSINESS_ID'] : '';
					}
                    
                    if($reuslt_add > 0)
                    {   
                        //���·��ż�¼Ϊ�����뱨��
                        $up_granted_num = 
                            $local_granted_model->sub_granted_to_reim_applied_by_id($add_sucess , $last_reim_id);
                        
                        //���±������뵥���
                        $up_num = 
                            $reim_list_model->update_reim_list_amount($last_reim_id, $total_amount);
                        
                        $info['state']  = 1;
                        $info['msg']  = '��������ɹ�';
                        
                        if($add_fail_num > 0)
                        {
                            $info['msg'] .= ',������'.$apply_num.'��,��ӳɹ�'.
                                    $add_sucess_num.'�������ʧ��'.$add_fail_num.'��';
                        }
                        
                        $applied_num = count($arr_reim_applied);
                        if(  $applied_num > 0)
                        {
                            $info['msg'] .= ',�������������'.$applied_num.'��';
                        }
                        
                        $no_moeny_num = count($arr_no_money);
                        if(  $no_moeny_num > 0)
                        {
                            $info['msg'] .= ',�������Ϊ0'.$no_moeny_num.'��';
                        }
                        
                        if($up_num == FALSE)
                        {
                            $info['msg'] .= ',�������뵥��ȸ���ʧ��';
                        }
                    }
                }
                else
                {
                    $info['state']  = 0;
                    $info['msg']  = '�����������ʧ�ܣ��ύ���ݲ����ϱ�������';
                }
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '�����������ʧ�ܣ��������Ϣ';
            }
        }
        else
        {
            $info['state']  = 0;
			$info['msg']  = '�����������ʧ�ܣ�����ѡ��һ����Ϣ';
        }
        
        $info['msg'] = g2u($info['msg']);
		echo json_encode($info);
		exit;
    }

    private function createPurchaseReim($purchase, &$msg) {
        $dbResult = false;
        if (notEmptyArray($purchase)) {
            if (!in_array(intval($purchase['TYPE']), array(1, 2))) {
                $msg = "�����������ʧ�ܣ����������޷�ȷ��";
                return false;
            }

            // ��������ӵ���������Ĳɹ���ϸ
            if (D('ReimbursementDetail')->is_exisit_reim_detail($purchase['CASE_ID'], $purchase['DETAIL_ID'], $purchase['TYPE'])) {
                return true;
            }

            if (intval($purchase['TYPE']) == 1) {
                $reimType = 1;  // ��Ŀ�²ɹ���������
            } else {
                $reimType = 14;  // ���ڲɹ���������
            }

            // �ҵ����������б������ǰ�û���δ�����б�����ӵ��ñ����б��У����򣬴����µı����б�
            // ��Ŀ�²ɹ��ʹ��ڲɹ������뵽�ɹ���ϸ����
            $recentReimListId = $this->findRecentReimListId(1);

            if (intval($recentReimListId)) {
                $reimDetail['CITY_ID'] = $purchase['CITY_ID'];
                $reimDetail['CASE_ID'] = $purchase['CASE_ID'];
                $reimDetail['BUSINESS_ID'] = $purchase['DETAIL_ID'];
                $reimDetail['BUSINESS_PARENT_ID'] = $purchase['REQ_ID'];
                $reimDetail['MONEY'] = (floatval($purchase['PRICE']) * intval($purchase['NUM']));
                $reimDetail['STATUS'] = 0;  // ״̬��Ϊδ�ύ
                $reimDetail['APPLY_TIME'] = date('Y-m-d H:i:s');
                $reimDetail['ISFUNDPOOL'] = $purchase['IS_FUNDPOOL'];
                $reimDetail['ISKF'] = $purchase['IS_KF'];//Ĭ�Ͽ۷�
                $reimDetail['TYPE'] =  $reimType;
                $reimDetail['FEE_ID'] =  $purchase['FEE_ID'];
                $reimDetail['LIST_ID'] = $recentReimListId;

                $dbResult = D('ReimbursementDetail')->add_reim_details($reimDetail);
                if ($dbResult !== false) {  // ����걨������ĺ�������
                    $dbResult = $this->afterReimDetailAdded(array(
                        'REIM_LIST_ID' => $recentReimListId,
                        'REQ_ID' => $purchase['REQ_ID'],
                        'DETAIL_ID' => $purchase['DETAIL_ID'],
                        'MONEY' => $reimDetail['MONEY']
                    ));
                }
            } else {
                $msg = "��ȡ���������б�ʧ��";
                return false;
            }
        }

        return $dbResult;
    }

    private function findRecentReimListId($reimType) {
        if ($reimType == $this->reimType && intval($this->reimListId)) {
            return $this->reimListId;
        }

        $uid = intval($_SESSION['uinfo']['uid']);  // ��ǰ�û����
        $cityId = intval($this->channelid);  // //��ǰ���б��
        $userTrueName = strip_tags($_SESSION['uinfo']['tname']);  // �û�����

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

        $this->reimType = $reimType;  // �ɹ�����
        $this->reimListId = $id;  // ��������ID
        return $id;
    }

	/**
	 * ���ɱ�������
	 */
    public function apply_purchase_reim() {
        $dbResult = false;
        $purchaseList = $_POST['purchase_list'];
        if (notEmptyArray($purchaseList)) {
            // �ֱ���ÿ���ɹ���ϸ
            D()->startTrans();
            $msg = '';
            foreach ($purchaseList as $k => $v) {
                $dbPurchase = D('PurchaseList')->getPurchaseJoinReq($v);

                if (notEmptyArray($dbPurchase)) {
                    if ($dbPurchase[0]['DETAIL_STATUS'] == 0 || ($dbPurchase[0]['NUM'] == 0 && $dbPurchase[0]['USE_NUM'] == 0)) {
                        $msg = "��δ�ɹ��Ĳɹ���ϸ�����Ƚ��вɹ�";
                        $dbResult = false;
                    } else {
                        if ($dbPurchase[0]['TYPE'] == 1) {  // ҵ��ɹ�����Ҫ����ɱ���  (1:ҵ��ɹ�   2�����ڲɹ�)
                            $dbResult = D('ProjectCost')->insertOrUpdateCostList($v, $msg);
                        } else {
                            $dbResult = true;
                        }

						//���������� ���Ҵ��û��ֿ��л�ȡ�Ļ�������û��ֿ��״̬
						if($dbResult !== false){
							$dbResult = D('InboundUse')->updateBusinessOperate($dbPurchase[0]['DETAIL_ID'],4);
						}

                        if ($dbResult !== false) { //���ɱ�����
                            $dbResult = $this->reimPurchaseList($dbPurchase[0], $msg);
                        }

						if($dbResult !== false){ //���ȫ�����ò��Һ����û��ص����������ԭ��Ŀ40%������
							if($dbPurchase[0]['NUM'] == 0 && $dbPurchase[0]['USE_NUM']>0) {
								$dbResult = D('PurchaseList')->insertDisplaceIncome($dbPurchase[0]);
							}
						}
                    }
                } else {
                    $dbResult = false;
                    $msg = "û���ҵ���Ӧ�Ĳɹ���ϸ��" . $v;
                    break;
                }

                if ($dbResult === false) {
                    break;
                }
            }

            if ($dbResult !== false) {
                D()->commit();
                !empty($msg) or $msg = "���ɱ�������ɹ�";
                ajaxReturnJSON(1, g2u($msg));
            } else {
                D()->rollback();
                !empty($msg) or $msg = "���ɱ�������ʧ��";
                ajaxReturnJSON(0, g2u($msg));
            }
        }
//        ajaxReturnJSON(200, u2g('���óɹ�'), $_POST);
    }
    
    /**
     +----------------------------------------------------------
     * �ɹ���ͬ���뱨��
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function apply_purchase_contract_reim()
    {	
    	//��ǰ�û����
    	$uid = intval($_SESSION['uinfo']['uid']);
    	//��ǰ�û�����
    	$user_truename = strip_tags($_SESSION['uinfo']['tname']);
    	//��ǰ���б��
    	$city_id = intval($this->channelid);
    	
    	$reim_type = 1; //��Ŀ�ɹ���������
        $reim_type_bulk_purchase = 14;  //���ڲɹ���������
        
    	//���뱨���ĺ�ͬ���
    	$contract_ids_arr = $_GET['contract_ids'];
    	$apply_num = count($contract_ids_arr);
        
    	if($apply_num > 0) {
            //��ѯ��ͬ״̬���жϺ�ͬ�Ƿ��Ѿ�ǩԼ����ǩԼ�ĺ�ͬ�޷����뱨��
            $purchase_contract_model = D('PurchaseContract');
            $contract_info = $purchase_contract_model->get_contract_info_by_id($contract_ids_arr);
            //ǩԼ����
            $conf_sign = $purchase_contract_model->get_conf_sign();
            //��������
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
                    $info['msg']  = g2u('����δǩԼ��ͬ���޷����뱨��');
                    echo json_encode($info);
                    exit;
                }
                
                if($reim_applied_num > 0) {
                    $info['state']  = 0;
                    $info['msg']  = g2u('���������뱨�����ѱ����ĺ�ͬ���޷����뱨��');
                    echo json_encode($info);
                    exit;
                }
            } else {
                $info['state']  = 0;
                $info['msg']  = g2u('��ͬ��Ϣ�쳣���޷����뱨��');
                echo json_encode($info);
                exit;
            }
            
    		//�ɹ���ϸMODEL
    		$purchase_list_model = D('PurchaseList');
    		$purchase_info = array();
            
            //��ͬ�����вɹ���ϸ��Ϣ
    		$purchase_info = $purchase_list_model->get_purchase_list_by_contract_id($contract_ids_arr);
    		if(is_array($purchase_info) && !empty($purchase_info)) {
    			//�ɹ����뵥MODEL
    			$purchase_model = D('PurchaseRequisition');
    			
    			//�������뵥MODEL
    			$reim_list_model = D('ReimbursementList');
    			
    			//������ϸMODEL
    			$reim_detail_model = D('ReimbursementDetail');
    			
    			//������ϸ״̬����
    			$reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status();
                
                //��������
                $purchase_type =  $purchase_model->get_conf_purchase_type();
                
    			$arr_no_money = array();
    			$arr_reim_applied = array();
    			$reim_type_last = 0;
                
                //ѭ���ɹ���ϸ����
    			foreach ($purchase_info as $key => $value) {
    				if($value['PRICE'] > 0 || $value['USE_TOATL_PRICE'] > 0) {
    					//ͨ���ɹ���ID����ѯCASE_ID|CITY_ID
    					$purchase_requistion_info = array();
    					if($value['PR_ID'] > 0) {
    						$purchase_requistion_info = $purchase_model->get_purchase_by_id($value['PR_ID']);
    					}
    					
    					if(empty($purchase_requistion_info)) {
    						$info['state']  = 0;
    						$info['msg']  = g2u('�ɹ����쳣���޷����뱨��');
    						echo json_encode($info);
    						exit;
    					}
    					
    					$case_id = !empty($purchase_requistion_info[0]['CASE_ID']) ? 
    									$purchase_requistion_info[0]['CASE_ID'] : 0;
                        
                        //���ݲɹ���ϸ����ȷ�ϱ�����ϸ����
                        if($value['TYPE'] == $purchase_type['project_purchase']) {
                            $reim_type_last = $reim_type;
                        } else if($value['TYPE'] == $purchase_type['bulk_purchase']) {
                            $reim_type_last = $reim_type_bulk_purchase;
                        }
                        
                        if($reim_type_last == 0) {
                            $info['state']  = 0;
                            $info['msg']  =  g2u('�����������ʧ�ܣ����������޷�ȷ��');
                            
                            echo json_encode($info);
                            exit;
                        }
                        
    					//��ѯ�Ĳɹ���ϸ�Ƿ��Ѿ����������
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
    						$arr_reim_data[$value['ID']]['ISKF'] = $value['IS_KF'];//Ĭ�Ͽ۷�
    						$arr_reim_data[$value['ID']]['TYPE'] =  $reim_type_last;
                            $arr_reim_data[$value['ID']]['FEE_ID'] =  $value['FEE_ID'];
    					} else {
                            //�ѱ����Ĳɹ�
    						$arr_reim_applied[$value['ID']] = $value['ID'];
    					}
    				} else {
                        //���Ϊ0�Ĳɹ�
    					$arr_no_money[$value['ID']] = $value['ID'];
    				}
    			}
    		}
            
    		if(is_array($arr_reim_data) && !empty($arr_reim_data)) {
				//��ѯ��ǰ�û�δ�ύͬ���ͱ������뵥
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
                
				//��ӳɹ�����
				$add_sucess_num = 0;
				//���ʧ�ܸ���
				$add_fail_num = 0;
                //�����ܶ�
                $total_amount = 0;
                
                //ѭ�����뱨����ϸ��
				foreach($arr_reim_data as $r_key => $r_value)
				{   
					$r_value['LIST_ID'] = $last_reim_id;
                    
					//��ӱ�����ϸ����
					$reuslt_add = $reim_detail_model->add_reim_details($r_value);
                    
					//���㱨�������ܶ��
					$reuslt_add > 0 ? $total_amount += $r_value['MONEY'] : '';
                    
					//���㱨����ϸ����ɹ�����������ʧ�ܴ���
					$reuslt_add > 0 ? $add_sucess_num ++ : $add_fail_num ++;
				}
                
                //��ϸ����ɹ�
                if($reuslt_add > 0)
                {
					//���±������뵥���
					$up_num = $reim_list_model->update_reim_list_amount($last_reim_id, $total_amount);
                    
                    //���º�ͬΪ���뱨����
                    $up_num_contract =
                        $purchase_contract_model->sub_contract_to_reim_applied_by_id($contract_ids_arr, $last_reim_id);
                    
					$info['state']  = 1;
					$info['msg']  = '��������ɹ�';
                    $this->_merge_url_param['fromTab'] = 2;
                    $info['forward']  = U('/Purchasing/reim_manage/', $this->_merge_url_param);
                    
					if($add_fail_num > 0)
					{
						$info['msg'] .= ',������'.$apply_num.'��,��ӳɹ�'.
                                    $add_sucess_num.'�������ʧ��'.$add_fail_num.'��';
					}
                    
					if($up_num == FALSE)
					{
                        $info['msg'] .= ',�������뵥��ȸ���ʧ��';
                    }
				}
            }
			else
            {
				$info['state']  = 0;
				$info['msg']  = '�����������ʧ�ܣ��ύ���ݲ����ϱ�������';
            }
            
            $applied_num = count($arr_reim_applied);
            if(  $applied_num > 0)
            {
                $info['msg'] .= ',������������Ĳɹ���ϸ'.$applied_num.'��';
            }
            
            $no_moeny_num = count($arr_no_money);
            if(  $no_moeny_num > 0)
            {
                $info['msg'] .= ',�������Ϊ0�Ĳɹ���ϸ'.$no_moeny_num.'��';
            }
    	}
    	else
    	{
    		$info['state']  = 0;
    		$info['msg']  = '�����������ʧ�ܣ�����ѡ��һ����Ϣ';
    	}
    	
    	$info['msg'] = g2u($info['msg']);
    	echo json_encode($info);
    	exit;
    }	
    
    
    /**
     +----------------------------------------------------------
     * �ύ��������
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function sub_reim_to_apply()
    {
        //���뱨�������
		$reim_list_id_arr = $_GET['reim_list_id'];

        if(is_array($reim_list_id_arr) && !empty($reim_list_id_arr))
        {
            //�������뵥MODEL
            $reim_list_model = D('ReimbursementList');
            D()->startTrans();
			//�ֽ�������ʹ����������ж�
			$type = M("Erp_reimbursement_list")->where("ID = ".$reim_list_id_arr[0])->getField('TYPE');
			if($type != 7 && $type != 8 ) {
				//�жϱ�������Ƿ񳬳����߳��������
				$loan_case = D("ProjectCase")->get_conf_case_Loan();
				$loan_case_str = implode(",", array_keys($loan_case));

				//1,2,14,15  �ɹ�   Ԥ�������   ���ڲɹ�    С�۷�ɹ� ֧������������   �����ж�
				$reim_sql = "select  C.projectname,A.case_id,sum(money) as money from erp_reimbursement_detail A left join erp_case B on A.case_id = B.id";
				$reim_sql .= " left join erp_project C on B.project_id = C.id";
				$reim_sql .= " where A.status = 0 AND A.type not in(1,2,14,15,16) and list_id = $reim_list_id_arr[0] and B.scaletype in ($loan_case_str)";
				$reim_sql .= " group by C.projectname,A.case_id";

				$reim_data = M("erp_reimbursement_detail")->query($reim_sql);
				$error_str = "";
				foreach ($reim_data as $k => $v) {
					if ($ret_loan_limit = is_overtop_payout_limit($v['CASE_ID'], $v['MONEY'],0,1)) {
						$error_str .= "�������Ϊ��$reim_list_id_arr[0],��Ŀ��" . $v['PROJECTNAME'] . "���������ʱ����򳬳�����Ԥ�㣨�ܷ���>��Ʊ�ؿ�����*���ֳɱ��ʣ�,�Ƿ�������ʱ������� " . "<br />";
					}
				}
				//����д���ֱ�Ӵ��
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
                // ����Ƿ���ҵ������Ӧ���н�Ӷ������ϸ���޸�
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

            //�ɱ�MODEL
            $cost_model = D('ProjectCost');
            
            if($update_num > 0)
            {
                D()->commit();
                $info['state']  = 1;
                $info['msg']  = '���������ύ�ɹ�';
            }
            else
            {
                D()->rollback();
                $info['state']  = 0;
                $info['msg']  = '���������ύʧ��';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '���������ύʧ��';
        }
        
        $info['msg'] = g2u($info['msg']);
		echo json_encode($info);
		exit;
    }

    private function afterReimDetailAdded($data) {
        $dbResult = false;
        if (notEmptyArray($data)) {
            // ���²ɹ���ϸ
            $dbResult = D('PurchaseList')->where("ID = {$data['DETAIL_ID']}")->save(array(
                'STATUS' => 4
            ));

            if ($dbResult !== false) { // ���������б��еķ�����Ŀ
                $dbResult = D('ReimbursementList')->where("ID = {$data['REIM_LIST_ID']}")->setInc('AMOUNT', $data['MONEY']);
            }
        }

        return $dbResult;
    }

    /**
     * �ύ�ʽ�ط��ñ�������
     */
    public function applyFundPoolCost() {
        $dbResult = false;
        $msg = '����ʧ��';
        $bizId = intval($_REQUEST['biz_id']);
        if ($bizId) {
            $fundPoolCostData = D('Benefits')->getFundPoolCost($bizId);
            // �ж��Ƿ��Ѿ���������
            if( $fundPoolCostData["ISCOST"] != 1 ) { // ��ѡ��¼�����뱨���������ظ�����
                ajaxReturnJSON(false, g2u('���ʽ�ط��������뱨���������ظ�����'));
            }

            // ��ɱ����м���һ���ɱ���¼
            // ����һ��������ϸ�ͱ����б�

            D()->startTrans();

            $dbResult = D('Benefits')->addFundPoolCostApply($fundPoolCostData);  // ����һ����������ĳɱ�
            if ($dbResult !== false) {
                $dbResult = D('ReimbursementList')->addFundPoolReim($fundPoolCostData);
            }

            if ($dbResult !== false) {
                $dbResult = D('Benefits')->where("ID = {$bizId}")->save(array(
                    'ISCOST' => 3,
                    'STATUS' => 3
                ));  // ���ύ
            }


			
			 
			//��֧��ҵ��Ѵ���
			$sql = "select * from ERP_FINALACCOUNTS t where CASE_ID='".$fundPoolCostData['CASE_ID']."' and TYPE=1";
			$finalaccounts = M()->query($sql);
			$xgfee = $finalaccounts[0]['TOBEPAID_FUNDPOOL'] > $fundPoolCostData['FEE']  ? $finalaccounts[0]['TOBEPAID_FUNDPOOL']-$fundPoolCostData['FEE']  : 0;
			if($xgfee!=$finalaccounts[0]['TOBEPAID_FUNDPOOL'] && $finalaccounts[0]['STATUS']==2){
				D('Erp_finalaccounts')->where("CASE_ID='".$fundPoolCostData['CASE_ID']."' and TYPE=1")->save(array('TOBEPAID_FUNDPOOL'=>$xgfee) );
			}


            if ($dbResult !== false) {
                D()->commit();
                $msg = '����ɹ�';
            } else {
                D()->rollback();
                $msg = '����ʧ��';
            }
        }

        ajaxReturnJSON($dbResult, g2u($msg));
    }

    /**
     * �ɹ���ϸ���ɱ������룬����ǲ������ò��ֹ�����Ҫ���ɱ������뵥�������ȫ�����ã���ֱ�ӱ���
     * @param $purchaseInfo �ɹ���ϸ��Ϣ
     * @param string $msg ���������Ϣ
     * @return bool
     */
    private function reimPurchaseList($purchaseInfo, &$msg) {
        $result = false;
        if (notEmptyArray($purchaseInfo)) {
            if ($purchaseInfo['NUM'] == 0 && $purchaseInfo['USE_NUM'] == 0) {
                // ������
            } else {
                if (intval($purchaseInfo['NUM']) > 0) {
                    // �ɹ��й�����ʱ�����ɱ�������
                    $result = $this->createPurchaseReim($purchaseInfo, $msg);
                } else {
                    // �ɹ�ȫ������������ֱ�ӱ���
                    $result = D('PurchaseList')->where("id = {$purchaseInfo['DETAIL_ID']}")->save(array('STATUS' => 2));

                    if ($result !== false) {
                        if (D('PurchaseList')->is_all_purchased($purchaseInfo['DETAIL_ID'])) {
                            // �ɹ���ϸȫ���ɹ���������òɹ����뵥Ϊ�ɹ����״̬
                            $result = D('PurchaseRequisition')->where("ID = {$purchaseInfo['REQ_ID']}")->save(array('STATUS' => 4));
                        }
                    }

                    if ($result === false) {
                        $msg = 'ȫ�����ñ�������';
                    }
                }
            }
        }
        return $result;
    }
}
   
/* End of file ReimbursementAction.class.php */
/* Location: ./Lib/Action/ReimbursementAction.class.php */