<?php
/* 
 * �������뵥����
 */
class ReimbursementListModel extends Model {
	    
    protected  $tablePrefix  =   'ERP_';
    protected  $tableName = 'REIMBURSEMENT_LIST';
    
    /***������״̬***/
    private  $_conf_reim_list_status = array(
							    		'reim_list_no_sub'	=> 0,	//δ�ύ
							    		'reim_list_sub'		=> 1,	//���ύδ���
							    		'reim_completed'	=> 2, 	//�ѱ���
                                        'reim_rejected'	    => 3, 	//�Ѳ���
	                                    'reim_deleted'      => 4,   //��ɾ��
                                        'reim_payout'       => 5,   //������ʱ���
	                                    
    								);
    
    /***������״̬����***/
    private $_conf_reim_list_status_remark = array(
								    		0 => 'δ�ύ',
								    		1 => '���ύ',
								    		2 => '�ѱ���',
                                            3 => '�Ѳ���',
                                            4 => '��ɾ��',
                                            5 => '�����������',
    									);

    /***���ñ�׼����***/
    private $_conf_fee_type = array(
                    'TOTAL_PRICE' => 1,
                    'AGENCY_REWARD' => 2,
                    'OUT_REWARD' => 3,
                    'AGENCY_DEAL_REWARD' => 4,
                    'PROPERTY_DEAL_REWARD' => 5,



    );
	
    /**���캯��**/
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    /**
     * ��ȡ�������뵥״̬
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_reim_list_status()
    {
        return $this->_conf_reim_list_status;
    }
    
    
    /**
     * ��ȡ�������뵥״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_reim_list_status_remark()
    {
        return $this->_conf_reim_list_status_remark;
    }

    /**
     * ��ȡ���ñ�׼����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_fee_type()
    {
        return $this->_conf_fee_type;
    }
    
    /**
     * ��ӱ�������Ϣ
     *
     * @access	public
     * @param	array  $reim_arr ������������Ϣ
     * @return	mixed  �ɹ�����������ţ�ʧ�ܷ���FALSE
     */
    public function add_reim_list($reim_arr)
    {
    	$insertId = 0;
    	
    	if(is_array($reim_arr) && !empty($reim_arr))
    	{
    		// �����������ز���ID
    		$insertId = $this->add($reim_arr);
    	}
        
    	return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ��������ɾ���������뵥
     *
     * @access	public
     * @param	mixed  $ids ���������
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
     */
    public function del_reim_list_by_ids($ids)
    {
        $up_num = 0;
    	$cond_where = "";
    
    	if(is_array($ids) && !empty($ids))
    	{
    		$ids_str = implode(',', $ids);
    		$cond_where = " ID IN (".$ids_str.")";
    	}
    	else
    	{
    		$id  = intval($ids);
    		$cond_where = " ID = '".$id."'";
    	}
        
        
    	if($cond_where != '')
    	{	            
    		$up_num = self::del_reim_list_by_cond($cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ��������ɾ���������뵥
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
     */
    public function del_reim_list_by_cond($cond_where)
    {
        $up_num = 0;
        
    	if($cond_where != '')
    	{	
            $update_arr = array();
            $update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
            $update_arr['STATUS'] = intval($this->_conf_reim_list_status['reim_deleted']);
            
    		$up_num = self::update_reim_list_by_cond($update_arr, $cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ��ӱ�����ϸ��������
     *
     * @access	public
     * @param	int     $list_id ���������
     * @param	float   $amount ���������
	 * @param	string  $action_type ���·�ʽ��adds�ۼӣ�cover���ǣ�
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_reim_list_amount($list_id, $amount, $action_type = 'adds')
    {
    	$list_id = intval($list_id);
        $amount = floatval($amount);
                
    	if($list_id > 0)
    	{	
    		$cond_where = "ID = '".$list_id."'";
            
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		if($action_type == 'cover')
    		{	
    			$update_arr['AMOUNT'] =  $amount;
    		}
    		else if($action_type == 'adds')
    		{
    			$update_arr['AMOUNT'] =  array('exp', "AMOUNT + ".$amount);
    		}
    		else
    		{
    			return FALSE;
    		}
            
    		$up_num = self::update_reim_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * �ύ���������������
     *
     * @access	public
     * @param	$int  $list_id ���������
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_reim_list_to_aduit($list_id,$reim_payout ="")
    {   
        $up_num = 0;
        
        if(is_array($list_id) && !empty($list_id))
    	{
    		$list_id_str = implode(',', $list_id);
    		$cond_where = " ID IN (".$list_id_str.")";
    	}
    	else
    	{
    		$id  = intval($list_id);
    		$cond_where = " ID = '".$id."'";
    	}
        
    	if($cond_where != '')
    	{	
    		//δ�ύ���״̬,������0
    		$no_sub_status  = $this->_conf_reim_list_status['reim_list_no_sub'];
            $payout_status = $this->_conf_reim_list_status['reim_payout'];
            if($reim_payout) {
                $cond_where .= " AND STATUS = '" . $payout_status . "' AND AMOUNT > 0 ";
            }else{
                $cond_where .= " AND STATUS = '" . $no_sub_status . "' AND AMOUNT > 0 ";
            }
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_reim_list_status['reim_list_sub'];
            
    		$up_num = self::update_reim_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * �������ͨ���������뵥
     *
     * @access	public
     * @param	$int  $list_id  ���������
     * @param	$int  $reim_uid ������UID
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_reim_list_to_completed($list_id, $reim_uid)
    {
    	$list_id = intval($list_id);
        $reim_uid = intval($reim_uid);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//���ύ״̬
    		$sub_status  = $this->_conf_reim_list_status['reim_list_sub'];
    		$cond_where .= " AND STATUS = '".$sub_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_reim_list_status['reim_completed'];
            $update_arr['REIM_TIME'] = date('Y-m-d H:i:s');
            $update_arr['REIM_UID'] = $reim_uid;
            
    		$up_num = self::update_reim_list_by_cond($update_arr, $cond_where);
    	}
        
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * �����˻ر�������Ϊ����״̬
     *
     * @access	public
     * @param	$int  $list_id ���������
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_reim_list_backto_apply($list_id,$money=0,$bee=false)
    {
    	$list_id = intval($list_id);
    
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//���ύ״̬
    		$sub_status  = $this->_conf_reim_list_status['reim_list_sub'];
    		$cond_where .= " AND STATUS = '".$sub_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
            $update_arr['BACK_NUM'] =  array('exp', "BACK_NUM + 1" );
    		$update_arr['STATUS'] = $this->_conf_reim_list_status['reim_list_no_sub'];
    		if ($bee){
    		    $update_arr['STATUS']  = $this->_conf_reim_list_status['reim_rejected'];
    		}
    		$up_num = self::update_reim_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����ָ���������±������뵥��Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_reim_list_by_cond($update_arr, $cond_where){	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$up_num = $this->where($cond_where)->save($update_arr);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����ID��ѯ��Ϣ
     *
     * @access	public
     * @param  mixed $ids 
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��Ϣ
     */
    public function get_info_by_id($ids, $search_field = array())
    {   
        $cond_where = "";
        $info = array();
        
        if(is_array($ids) && !empty($ids))
        {   
            $ids_str = implode(',', $ids);
            $cond_where = " ID IN (".$ids_str.")";
        }
        else
        {
            $id  = intval($ids);
            $cond_where = " ID = '".$id."'";
        }
        
        $info = self::get_info_by_cond($cond_where, $search_field);
        
        return $info;
    }
    
    
    /**
     * ����������ȡ����һ���������뵥��¼
     *
     * @access	public
     * @param int $apply_uid  �����û�
     * @param int $type     ��������
     * @param int $city_id  ���б��
     * @param int $status   ״̬
     * @return	array ��ѯ���
     */
    public function get_last_reim_list($apply_uid, $type, $city_id, $status = 0)
    {
        $info = array();
        
        $apply_uid = intval($apply_uid);
        $type = intval($type);
        $status = intval($status);
        $city_id = intval($city_id);
        
        if($apply_uid > 0)
        {
            $cond_where = "APPLY_UID = '".$apply_uid."' AND TYPE = '".$type."' AND "
                . "CITY_ID = '".$city_id."' AND STATUS = '".$status."' ";
            
            $info = $this->where($cond_where)->order('ID DESC')->find();
        }
        //echo $this->_sql();
        return $info;
    }
    
    
    /**
     * ����������ȡ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array
     */
    public function get_info_by_cond($cond_where, $search_field = array())
    {   
        $info = array();
        
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $info;
        }
        
        if(is_array($search_field) && !empty($search_field))
        {
            $search_str = implode(',', $search_field);
            $info = $this->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $info = $this->where($cond_where)->select();
        }
        
        return $info;
    }

    public function addFundPoolReim($data) {
        $result = false;
        if (notEmptyArray($data)) {
            $listArr["AMOUNT"] = $data['FEE'];
            $listArr["TYPE"] = $data['TYPE'];
            $listArr["STATUS"] = 1;
            $listArr["APPLY_UID"] = $data['ADD_UID'];
            $listArr["APPLY_TRUENAME"] = $_SESSION['uinfo']['tname'];
            $listArr["APPLY_TIME"] = date("Y-m-d H:i:s");
            $listArr["CITY_ID"] = $data['CITY_ID'];
            $listId = $this->add($listArr);  // ���뱨���б�ɹ�

            if ($listId) {
                $detailArr["LIST_ID"] = $listId;
                $detailArr["CITY_ID"] = $data['CITY_ID'];
                $detailArr["CASE_ID"] = $data['CASE_ID'];
                $detailArr["BUSINESS_ID"] = $data['ENTITY_ID'];
                $detailArr["MONEY"] = $data['FEE'];
                $detailArr["STATUS"] = 0;
                $detailArr["TYPE"] = $data['TYPE'];
                $detailArr["ISKF"] = 1;
                $detailArr["ISFUNDPOOL"] = 1;
                $detailArr["FEE_ID"] = 80;
                $detailArr["BUSINESS_PARENT_ID"] = $data['ENTITY_ID'];
                $result = D('ReimbursementDetail')->add_reim_details($detailArr);
            }
        }

        return $result;
    }

    /**
     * ��ȡ�������
     */
    private function getFeeScaleAmount($caseId, $amount, $houseTotal,$feeType ="") {
        $response = 0;
        if (intval($caseId) > 0) {
            if($feeType){
                $feeone = M('Erp_feescale')->where("CASE_ID={$caseId} and AMOUNT='{$amount}' and ISVALID = -1 and scaletype='{$feeType}'")->find();
            }else{
                $feeone = M('Erp_feescale')->where("CASE_ID={$caseId} and AMOUNT='{$amount}' and ISVALID = -1")->find();
            }

            if ($feeone['STYPE'] == 1) {
                $response = $houseTotal * $amount / 100;
            } else {
                $response = $amount;
            }
        }

        return $response;
    }

    /**
     * �н�Ӷ�𡢽�������
     * @param array $memberList ������Ա�б�
     * @param array $data �н�Ӷ������
     * @param int $reimListId �������뵥���
     * @param int $reimType ��������
     * @param string $reimField ��Ա���ж�Ӧ���ֶ�
     * @param int $cityId ����ID
     * @param string $msg ������Ϣ
     * @return bool|mixed
     */
    public function agencyRewardReim($memberList = array(), $data = array(), &$reimListId = 0, $reimType = 0, $reimField = '', $cityId = 0, $reimName, &$msg = '', &$resultReimDetailList = array()) {
        $dbResult = false;
        $feeType = $this->get_conf_fee_type();
        if (notEmptyArray($memberList)) {
            // ��ȡ�������
            $amountList = array();
            $reimDetailList = array();
            if ($reimType == 17) {  // �н��Ӷ��������
                foreach ($data as $item) {
                    $remainPostComisAmount = D('ReimbursementList')->getRemainFxPostComisReimAmount($item['card_member_id'], $item['id']);
                    if ($remainPostComisAmount < $item['amount']) {
                        $msg = sprintf('���Ϊ%s�ı����޷����룬�Ѿ�������ʣ��������%s��Ӷ���¼���Ϊ%d', $item['amount'], $remainPostComisAmount, $item['id']);
                        return false;
                    }

                    $amountList[$item['card_member_id']] = $item['amount'];
                    $businessIdList[$item['card_member_id']] = $item['id'];
                }
            }
            // ���챨����ϸ����
            foreach ($memberList as $member) {
                $reimed = false;
                if ($reimType != 17) {
                    $reimed = D('ReimbursementDetail')->is_exisit_reim_detail($member['CASE_ID'], $member['ID'], $reimType);
                }

                // ���û�б�������ӱ�����¼
                if (!$reimed && floatval($member[$reimField]) > 0) {
                    // ��ȡ�������
                    if ($reimType == 17) {
                        $money = $amountList[$member['ID']];
                        $businessID = $businessIdList[$member['ID']];
                    } else {
                        $money = $this->getFeeScaleAmount($member['CASE_ID'], $member[$reimField], $member['HOUSETOTAL'],$feeType[$reimField]);
                        $businessID =  $member['ID'];
                    }

//  todo                    $isFundPool = D('House')->get_isfundpool_by_prjid($member['PRJ_ID']);
                    $reimDetailList[$member['ID']]['CITY_ID'] = $member['CITY_ID'];
                    $reimDetailList[$member['ID']]['CASE_ID'] = $member['CASE_ID'];
                    $reimDetailList[$member['ID']]['BUSINESS_ID'] = $businessID;
                    $reimDetailList[$member['ID']]['BUSINESS_PARENT_ID'] = $member['ID'];
                    $reimDetailList[$member['ID']]['MONEY'] = $money;  // ������ϸ���
                    $reimDetailList[$member['ID']]['STATUS'] = 0;  // ������ϸδ�ύ
                    $reimDetailList[$member['ID']]['APPLY_TIME'] = date('Y-m-d H:i:s');
                    $reimDetailList[$member['ID']]['ISFUNDPOOL'] = 0; // �������ʽ��
                    $reimDetailList[$member['ID']]['ISKF'] = 1;
                    $reimDetailList[$member['ID']]['TYPE'] = $reimType;  // ��������
                    $reimDetailList[$member['ID']]['FEE_ID'] = 39;  // �н��
                }
            }

            if (count($reimDetailList) == 0) {
                $msg = 'û�з��������Ĵ�������¼';
                return false;
            }

            if ($reimListId == 0) {  // ���û��δ����ı�����ID�����½�һ����������¼
                $insertReimListData = array();
                $insertReimListData['TYPE'] =  $reimType;
                $insertReimListData['APPLY_UID'] =  intval($_SESSION['uinfo']['uid']);
                $insertReimListData['APPLY_TRUENAME'] =  strip_tags($_SESSION['uinfo']['tname']);
                $insertReimListData['APPLY_TIME'] =  date('Y-m-d H:i:s');
                $insertReimListData['CITY_ID'] =  $cityId;
                $reimListId = D('ReimbursementList')->add_reim_list($insertReimListData);
            }

            if ($reimListId == 0) {
                $msg = '�����������뵥ʧ��';
                return false;
            }

            // ��������ϸ��������ݿ�
            $sumMoney = 0;
            foreach ($reimDetailList as $key => $item) {
                switch($reimType){
                    case 22:
                        $statusData['AGENCY_DEAL_REWARD_STATUS'] = 2;
                        break;
                    case 23:
                        $statusData['PROPERTY_DEAL_REWARD_STATUS'] = 2;
                        break;
                    case 24:
                        $statusData['OUT_REWARD_STATUS'] = 2;
                        break;
                }

                if ($reimType != 17) {
                    $dbResult = D("Member")->where("ID = {$key}")->save($statusData);
                    if ($dbResult === false) {
                        $msg = '���±���״̬ʧ��';
                        break;
                    }
                }

                $item['LIST_ID'] = $reimListId;
                $dbResult = D('ReimbursementDetail')->add($item);
                if ($dbResult === false) {
                    $msg = '������ϸ���ʧ��';
                    break;
                }
                if ($reimType == 17) {
                    $resultReimDetailList[$item['BUSINESS_ID']] = $dbResult;
                }
                $sumMoney += $item['MONEY'];  // �ۼӽ��
            }

            // �޸Ľ��
            if ($dbResult !== false) {
                $dbResult = D('ReimbursementList')->where("ID = {$reimListId}")->setInc('AMOUNT', $sumMoney);
            }

            if ($dbResult === false) {
                !empty($msg) or $msg = $reimName . '��������ʧ��';
            } else {
                !empty($msg) or $msg = $reimName . '��������ɹ�';
            }
        }

        return $dbResult;
    }

    /**
     * ��ȡ�û����µĿ��õı�����ID
     * @param int $reimType
     * @param int $caseId
     * @param int $cityId
     * @return int
     */
    public function getNewestReimListId($reimType = 0, $caseId = 0, $cityId = 0) {
        $response = 0;
        if ($reimType && $caseId) {
            $reimListSql = <<<SQL
                SELECT A.ID
                FROM ERP_REIMBURSEMENT_LIST A
                LEFT JOIN ERP_REIMBURSEMENT_DETAIL B ON A.ID=B.LIST_ID
                WHERE A.APPLY_UID = %d
                  AND A.TYPE = %d
                  AND A.CITY_ID = %d
                  AND A.STATUS = 0
                  AND B.CASE_ID= %d
SQL;
            $dbResult = D()->query(sprintf($reimListSql, intval($_SESSION['uinfo']['uid']), $reimType, $cityId, $caseId));
            if (notEmptyArray($dbResult)) {
                $response = $dbResult[0]['ID'];
            }
        }

        return $response;
    }

    public function getReimTypeAndField($actName, &$reimType, &$reimField, &$reimName) {
        switch ($actName) {
            case 'post_agency_reward_reim':
                $reimType = 17;
                $reimField = 'AGENCY_REWARD_AFTER';
                $reimName = '��Ӷ�н�Ӷ��';
                break;
            case 'agency_deal_reward_reim':
//                $reimType = 10;
                $reimType = 22;
                $reimField = 'AGENCY_DEAL_REWARD';
                $reimName = '�н�ɽ�����';
                break;
            case 'property_deal_reward_reim':
//                $reimType = 12;
                $reimType = 23;
                $reimField = 'PROPERTY_DEAL_REWARD';
                $reimName = '��ҵ�ɽ�����';
                break;
            case 'out_reward_reim':
//                $reimType = 21;
                $reimType = 24;
                $reimField = 'OUT_REWARD';
                $reimName = '�ⲿ����';
                break;
            default:
                $reimType = 0;
        }

    }

    /**
     * ��ȡ�����н��Ӷʣ��������ʽ�
     * @param $memberId
     * @param $postComisId
     * @return float|int
     */
    public function getRemainFxPostComisReimAmount($memberId, $postComisId) {
        $response = 0;
        if ($memberId && $postComisId) {
            $dbResult = D('erp_cardmember')->field('CASE_ID, AGENCY_REWARD_AFTER, HOUSETOTAL')->where("ID = {$memberId}")->find();
            if (notEmptyArray($dbResult)) {
                $totalAmount = getFeeScaleAmount($dbResult['CASE_ID'], $dbResult['AGENCY_REWARD_AFTER'], $dbResult['HOUSETOTAL']);
                $payTotalAmount = floatval(D('erp_commission_reim_detail')->where("POST_COMMISSION_ID = {$postComisId}")->sum('AMOUNT'));
                $response = round($totalAmount, 2) - $payTotalAmount;
            }
        }

        return round($response, 2);
    }
}

/* End of file ReimbursementListModel.class.php */
/* Location: ./Lib/Model/ReimbursementListModel.class.php */