<?php
/* 
 * 报销申请单管理
 */
class ReimbursementListModel extends Model {
	    
    protected  $tablePrefix  =   'ERP_';
    protected  $tableName = 'REIMBURSEMENT_LIST';
    
    /***报销单状态***/
    private  $_conf_reim_list_status = array(
							    		'reim_list_no_sub'	=> 0,	//未提交
							    		'reim_list_sub'		=> 1,	//已提交未审核
							    		'reim_completed'	=> 2, 	//已报销
                                        'reim_rejected'	    => 3, 	//已驳回
	                                    'reim_deleted'      => 4,   //已删除
                                        'reim_payout'       => 5,   //超额垫资报销
	                                    
    								);
    
    /***报销单状态描述***/
    private $_conf_reim_list_status_remark = array(
								    		0 => '未提交',
								    		1 => '已提交',
								    		2 => '已报销',
                                            3 => '已驳回',
                                            4 => '已删除',
                                            5 => '超额报销申请中',
    									);

    /***费用标准类型***/
    private $_conf_fee_type = array(
                    'TOTAL_PRICE' => 1,
                    'AGENCY_REWARD' => 2,
                    'OUT_REWARD' => 3,
                    'AGENCY_DEAL_REWARD' => 4,
                    'PROPERTY_DEAL_REWARD' => 5,



    );
	
    /**构造函数**/
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    /**
     * 获取报销申请单状态
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
     * 获取报销申请单状态描述
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
     * 获取费用标准类型
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
     * 添加报销单信息
     *
     * @access	public
     * @param	array  $reim_arr 报销单数组信息
     * @return	mixed  成功返报销单编号，失败返回FALSE
     */
    public function add_reim_list($reim_arr)
    {
    	$insertId = 0;
    	
    	if(is_array($reim_arr) && !empty($reim_arr))
    	{
    		// 自增主键返回插入ID
    		$insertId = $this->add($reim_arr);
    	}
        
    	return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据条件删除报销申请单
     *
     * @access	public
     * @param	mixed  $ids 报销单编号
     * @return	mixed  成功返回删除行数，失败返回FALSE
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
     * 根据条件删除报销申请单
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @return	mixed  成功返回删除行数，失败返回FALSE
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
     * 添加报销明细到报销单
     *
     * @access	public
     * @param	int     $list_id 报销单编号
     * @param	float   $amount 报销单金额
	 * @param	string  $action_type 更新方式（adds累加，cover覆盖）
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
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
     * 提交报销单到财务审核
     *
     * @access	public
     * @param	$int  $list_id 报销单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
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
    		//未提交审核状态,金额大于0
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
     * 财务审核通过报销申请单
     *
     * @access	public
     * @param	$int  $list_id  报销单编号
     * @param	$int  $reim_uid 操作人UID
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_reim_list_to_completed($list_id, $reim_uid)
    {
    	$list_id = intval($list_id);
        $reim_uid = intval($reim_uid);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//已提交状态
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
     * 财务退回报销申请为申请状态
     *
     * @access	public
     * @param	$int  $list_id 报销单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_reim_list_backto_apply($list_id,$money=0,$bee=false)
    {
    	$list_id = intval($list_id);
    
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//已提交状态
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
     * 根据指定条件更新报销申请单信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
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
     * 根据ID查询信息
     *
     * @access	public
     * @param  mixed $ids 
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 信息
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
     * 根据条件获取最新一条报销申请单记录
     *
     * @access	public
     * @param int $apply_uid  申请用户
     * @param int $type     报销类型
     * @param int $city_id  城市编号
     * @param int $status   状态
     * @return	array 查询结果
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
     * 根据条件获取信息
     *
     * @access	public
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
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
            $listId = $this->add($listArr);  // 插入报销列表成功

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
     * 获取报销金额
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
     * 中介佣金、奖励报销
     * @param array $memberList 分销会员列表
     * @param array $data 中介佣金数据
     * @param int $reimListId 报销申请单编号
     * @param int $reimType 报销类型
     * @param string $reimField 会员表中对应的字段
     * @param int $cityId 城市ID
     * @param string $msg 操作信息
     * @return bool|mixed
     */
    public function agencyRewardReim($memberList = array(), $data = array(), &$reimListId = 0, $reimType = 0, $reimField = '', $cityId = 0, $reimName, &$msg = '', &$resultReimDetailList = array()) {
        $dbResult = false;
        $feeType = $this->get_conf_fee_type();
        if (notEmptyArray($memberList)) {
            // 获取报销金额
            $amountList = array();
            $reimDetailList = array();
            if ($reimType == 17) {  // 中介后佣报销类型
                foreach ($data as $item) {
                    $remainPostComisAmount = D('ReimbursementList')->getRemainFxPostComisReimAmount($item['card_member_id'], $item['id']);
                    if ($remainPostComisAmount < $item['amount']) {
                        $msg = sprintf('金额为%s的报销无法申请，已经超过了剩余可申请额%s，佣金记录编号为%d', $item['amount'], $remainPostComisAmount, $item['id']);
                        return false;
                    }

                    $amountList[$item['card_member_id']] = $item['amount'];
                    $businessIdList[$item['card_member_id']] = $item['id'];
                }
            }
            // 构造报销明细数据
            foreach ($memberList as $member) {
                $reimed = false;
                if ($reimType != 17) {
                    $reimed = D('ReimbursementDetail')->is_exisit_reim_detail($member['CASE_ID'], $member['ID'], $reimType);
                }

                // 如果没有报销则添加报销记录
                if (!$reimed && floatval($member[$reimField]) > 0) {
                    // 获取报销金额
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
                    $reimDetailList[$member['ID']]['MONEY'] = $money;  // 报销明细金额
                    $reimDetailList[$member['ID']]['STATUS'] = 0;  // 报销明细未提交
                    $reimDetailList[$member['ID']]['APPLY_TIME'] = date('Y-m-d H:i:s');
                    $reimDetailList[$member['ID']]['ISFUNDPOOL'] = 0; // 分销无资金池
                    $reimDetailList[$member['ID']]['ISKF'] = 1;
                    $reimDetailList[$member['ID']]['TYPE'] = $reimType;  // 报销类型
                    $reimDetailList[$member['ID']]['FEE_ID'] = 39;  // 中介费
                }
            }

            if (count($reimDetailList) == 0) {
                $msg = '没有符合条件的待报销记录';
                return false;
            }

            if ($reimListId == 0) {  // 如果没有未申请的报销单ID，则新建一条报销单记录
                $insertReimListData = array();
                $insertReimListData['TYPE'] =  $reimType;
                $insertReimListData['APPLY_UID'] =  intval($_SESSION['uinfo']['uid']);
                $insertReimListData['APPLY_TRUENAME'] =  strip_tags($_SESSION['uinfo']['tname']);
                $insertReimListData['APPLY_TIME'] =  date('Y-m-d H:i:s');
                $insertReimListData['CITY_ID'] =  $cityId;
                $reimListId = D('ReimbursementList')->add_reim_list($insertReimListData);
            }

            if ($reimListId == 0) {
                $msg = '创建报销申请单失败';
                return false;
            }

            // 将报销明细添加至数据库
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
                        $msg = '更新报销状态失败';
                        break;
                    }
                }

                $item['LIST_ID'] = $reimListId;
                $dbResult = D('ReimbursementDetail')->add($item);
                if ($dbResult === false) {
                    $msg = '报销明细添加失败';
                    break;
                }
                if ($reimType == 17) {
                    $resultReimDetailList[$item['BUSINESS_ID']] = $dbResult;
                }
                $sumMoney += $item['MONEY'];  // 累加金额
            }

            // 修改金额
            if ($dbResult !== false) {
                $dbResult = D('ReimbursementList')->where("ID = {$reimListId}")->setInc('AMOUNT', $sumMoney);
            }

            if ($dbResult === false) {
                !empty($msg) or $msg = $reimName . '报销申请失败';
            } else {
                !empty($msg) or $msg = $reimName . '报销申请成功';
            }
        }

        return $dbResult;
    }

    /**
     * 获取用户最新的可用的报销单ID
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
                $reimName = '后佣中介佣金';
                break;
            case 'agency_deal_reward_reim':
//                $reimType = 10;
                $reimType = 22;
                $reimField = 'AGENCY_DEAL_REWARD';
                $reimName = '中介成交奖励';
                break;
            case 'property_deal_reward_reim':
//                $reimType = 12;
                $reimType = 23;
                $reimField = 'PROPERTY_DEAL_REWARD';
                $reimName = '置业成交奖励';
                break;
            case 'out_reward_reim':
//                $reimType = 21;
                $reimType = 24;
                $reimField = 'OUT_REWARD';
                $reimName = '外部奖励';
                break;
            default:
                $reimType = 0;
        }

    }

    /**
     * 获取分销中介后佣剩余待报销资金
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