<?php

/**
 * 报销明细管理类
 *
 * @author liuhu
 */
class ReimbursementDetailModel extends Model {

    /**
     * 单位：元
     */
    const UNIT_RMB_YUAN = '元';

    /**
     * 单位：%
     */
    const UNIT_PERCENT = '%';

    /***报销明细表***/
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'REIMBURSEMENT_DETAIL';

    /***报销明细状态***/
    private $_conf_reim_details_status = array(
        'reim_detail_no_sub' => 0,    //未提交
        'reim_detail_completed' => 1,    //已报销
        'reim_detail_deleted' => 4,   //删除报销明细
        'reim_detail_rejected' => 3,    //已驳回
    );

    /***明细退款状态***/
    private $_conf_reim_details_remark = array(
        0 => '未报销',
        1 => '已报销',
        4 => '删除',
        3 => '已驳回',
    );
    
    /**构造函数**/
    public function __construct()
    {
    	parent::__construct();
    }
    
    /**
     * 获取报销明细状态
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_reim_detail_status()
    {
        return $this->_conf_reim_details_status;
    }
    
    
    /**
     * 获取报销申请明细状态描述
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_reim_detail_status_remark()
    {
        return $this->_conf_reim_details_remark;
    }
    
    
    /**
     * 添加报销明细
     *
     * @access	public
     * @param	array  $reim_details_arr 退款信息
     * @return	mixed  成功返回退款单编号，失败返回FALSE
     */
    public function add_reim_details($reim_details_arr)
    {
    	$insertId = 0;
    
    	if(is_array($reim_details_arr) && !empty($reim_details_arr))
    	{
    		// 自增主键返回插入ID
    		$insertId = $this->add($reim_details_arr);
            //echo $this->getLastSql();
    	}
    
    	return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据申请单ID删除报销明细信息
     *
     * @access	public
     * @param	int  $listid 申请单编号
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function del_reim_detail_by_listid($listid)
    {
        $up_num = 0;
        $listid = intval($listid);
        
    	if($listid > 0)
    	{	
            $cond_where = "LIST_ID = '".$listid."'";
            
    		$up_num = self::del_reim_detail_by_cond($cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据申请单ID删除报销明细信息
     *
     * @access	public
     * @param	int  $id 报销明细ID
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function del_reim_detail_by_id($id)
    {
        $up_num = 0;
        $id = intval($id);
        
    	if($id > 0)
    	{	
            $cond_where = "ID = '".$id."'";
            
    		$up_num = self::del_reim_detail_by_cond($cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据条件删除报销明细信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function del_reim_detail_by_cond($cond_where)
    {   
        $up_num = 0;
        
    	if($cond_where != '')
    	{
            $update_arr = array();
            $update_arr['STATUS'] = intval($this->_conf_reim_details_status['reim_detail_deleted']);
            
    		$up_num = self::update_reim_detail_by_cond($update_arr, $cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 财务审核通过报销申请单
     *
     * @access	public
     * @param	$int  $list_id 报销单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_reim_detail_to_completed($list_id)
    {
    	$list_id = intval($list_id);
    
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//已提交状态
    		$no_sub_status  = $this->_conf_reim_details_status['reim_detail_no_sub'];
    		$cond_where .= " AND STATUS = '".$no_sub_status."'";
            
    		$update_arr['STATUS'] = $this->_conf_reim_details_status['reim_detail_completed'];
            
    		$up_num = self::update_reim_detail_by_cond($update_arr, $cond_where);
    	}
        
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据ID更新报销明细信息
     *
     * @access	public
     * @param	int  $id 报销明细ID
     * @param	array  $update_arr  需要更新字段的键值对
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function update_reim_detail_by_id($id, $update_arr = array())
    {
        $up_num = 0;
        $id = intval($id);
        
    	if($id > 0)
    	{	
            $cond_where = "ID = '".$id."'";
            
    		$up_num = self::update_reim_detail_by_cond($update_arr,$cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    /**
     * 根据指定条件更新报销申请明细信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_reim_detail_by_cond($update_arr, $cond_where)
    {	
    	$up_num = 0;
        
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$up_num = $this->where($cond_where)->save($update_arr);
    	}
    	//echo $this->getLastSql();
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据报销申请单编号获取报销总金额
     *
     * @access	public
     * @param	int  $list_id  报销申请单编号
     * @return	float 报销总金额
     */
    public function get_sum_total_money_by_listid($list_id)
    {	
    	$amount = 0;
    	
    	$list_id = intval($list_id);
    	
    	if($list_id > 0)
    	{	
    		$status_deleted = $this->_conf_reim_details_status['reim_detail_deleted'];
    		$cond_where = "LIST_ID = '".$list_id."' AND STATUS != '".$status_deleted."' ";
            //echo $cond_where;
    		$amount = $this->where($cond_where)->sum('MONEY');
            //echo $this->getLastSql();
    	}
    	
    	return floatval($amount);
    }
    
    /**
     * 根据LIST_ID获取报销明细
     * @param int $list_id 报销单id
     * @param array() $search_arr 要查询字段的键值对
     * return array() $info 报销明细数组
     */
    public function get_detail_info_by_listid($list_id, $search_arr)
    {
        $list_id = intval($list_id);
        $info = array();
        if( $list_id >0 )
        {
            $conf_where = "LIST_ID = ".$list_id;
            if(is_array($search_arr) && !empty($search_arr))
            {
                $info = $this->where($conf_where)->field($search_arr)->select();
            }
            
            return $info;
            
        }
    }
    
    /**
     * 根据LIST_ID获取报销明细
     * @param mixed $ids 明细id
     * @param array() $search_arr 要查询字段的键值对
     * return array() $info 报销明细数组
     */
    public function get_detail_info_by_id($ids,$search_arr = array())
    {
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN ($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }
        if(is_array($search_arr) && !empty($search_arr))
        {
            $info = $this->where($cond_where)->field($search_arr)->select();
        }
        else
        {
            $info = $this->where($cond_where)->select();
        }
        return $info ? $info : false;
    }
    
    /**
     * 根据条件获取报销明细
     * @param string $cond_where 查询条件
     * @param array() $search_arr 要查询字段的键值对
     * return array() $info 报销明细数组
     */
    public function get_detail_info_by_cond($cond_where,$search_arr = array())
    {
        $info = array();
        if(is_array($search_arr) && !empty($search_arr))
        {
            $info = $this->where($cond_where)->field($search_arr)->select();
        }
        else
        {
            $info = $this->where($cond_where)->select();
        }
        
        return $info ? $info : false;
    }
    
    /**
     * 是否已经报销过费用
     *
     * @access	public
     * @param	int  $case_id  案例编号
     * @param	int  $business_id  报销业务编号
     * @param	int  $type  报销类型
     * @return	boolean TRUE 存在 FALSE不存在
     */
    public function is_exisit_reim_detail($case_id, $business_id, $type)
    {
        $num = 0;
    	
    	$case_id = intval($case_id);
        $business_id = intval($business_id);
        $type = intval($type);
    	
    	if($business_id > 0 && $type > 0)
    	{	
    		$status_deleted = $this->_conf_reim_details_status['reim_detail_deleted'];
    		$cond_where = "CASE_ID = '".$case_id."' AND BUSINESS_ID = '".$business_id."' "
                        . "AND TYPE = '".$type."' AND  STATUS != '".$status_deleted."' ";
    		$num = $this->where($cond_where)->count();
    	}
    	
    	return $num > 0 ? TRUE : FALSE;
    }

    /**
     * 获取FeeScale
     * @param $listID
     */
    public function getFeeScalesByListID($listID) {
        $caseID = D('ReimbursementDetail')->where("LIST_ID  = {$listID}")->getField('CASE_ID');
        $feeScale = D('Project')->get_feescale_by_cid($caseID);

        if (is_array($feeScale) && count($feeScale)) {
            foreach ($feeScale as $key => $value) {
                $unit = $value['STYPE'] == 0 ? self::UNIT_RMB_YUAN : self::UNIT_PERCENT;
                $arrFee[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'] . $unit;
            }
        }

        return  $arrFee;
    }

    /**
     * 删除报销明细
     * @param array $data
     */
    public function handleDelReimDetail($data = array()) {
        $response = false;
        if (notEmptyArray($data)) {
            if ($data['TYPE'] == 17) {
                $response = D('erp_commission_reim_detail')->where("REIM_DETAIL_ID = {$data['ID']}")->delete();
            } else {
                $updateStatusData = array();
                switch ($data['TYPE']) {
                    case 22:
                        $updateStatusData['AGENCY_DEAL_REWARD_STATUS'] = 1;
                        break;
                    case 23:
                        $updateStatusData['PROPERTY_DEAL_REWARD_STATUS'] = 1;
                        break;
                    default:
                        $updateStatusData['OUT_REWARD_STATUS'] = 1;
                }
                $response = D('Member')->where("ID = {$data['BUSINESS_ID']}")->save($updateStatusData);
            }
        }
        return $response;
    }
}

/* End of file ReimbursementDetailModel.class.php */
/* Location: ./Lib/Model/ReimbursementDetailModel.class.php */