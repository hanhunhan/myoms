<?php

/**
 * 采购合同管理
 *
 * @author liuhu
 */
class PurchaseContractModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'CONTRACT';
    
    /***合同签约状态描述***/
    private $_conf_sign_remark = array(
                                '0' => '未签约',
                                '-1' => '已签约'
                            );
    
    /***合同签约状态***/
    private $_conf_sign = array(
                                'not_sign' => 0,
                                'sign' => -1
                            );
    
    /***合同报销状态描述***/
    private $_conf_reim_status_remark = array(
                                '0' => '未申请',
                                '1' => '已申请',
                                '2' => '已报销'
                            );
    
    /***合同报销状态***/
    private $_conf_reim_status = array(
                                'not_apply' => 0,
                                'applied' => 1,
                                'reimbursed' => 2,
                            );
    
    //构造函数
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    /**
     * 获取合同签约状态描述数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_sign_remark()
    {
        return $this->_conf_sign_remark;
    }
    
    
    /**
     * 获取合同签约状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_sign()
    {
        return $this->_conf_sign;
    }
    
    
    /**
     * 获取合同报销状态描述数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_reim_status_remark()
    {
        return $this->_conf_reim_status_remark;
    }
    
    
    /**
     * 获取合同报销状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_reim_status()
    {
        return $this->_conf_reim_status;
    }
    
    
    /**
     * 添加合同信息
     * @param array $contract_info 合同信息
     * @return mixed 添加成功返回插入数据编号，插入失败返回FALSE
     */
    public function add_contract_info($contract_info)
    {
        $insertId = FALSE;
        $info = array();
        
    	if(is_array($contract_info) && !empty($contract_info))
    	{   
    		$insertId = $this->add($info);
        }
        
    	return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据ID获取合同编号
     *
     * @access	public
     * @param	mixed  $ids 单条或者多条合同ID
     * @param array $search_field 搜索字段
     * @return	array 合同信息
     */
    public function get_contract_info_by_id($ids, $search_field = array())
    {   
        $contract_info = array();
        
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
        
        $contract_info = self::get_info_by_cond($cond_where, $search_field);
        
        return $contract_info;
    }
    
    
    /**
     * 根据条件获合同信息
     *
     * @access	public
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 合同信息
     */
    public function get_info_by_cond($cond_where, $search_field = array())
    {   
        $contract_info = array();
        
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $contract_info;
        }
        
        if(is_array($search_field) && !empty($search_field))
        {
            $search_str = implode(',', $search_field);
            $contract_info = $this->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $contract_info = $this->where($cond_where)->select();
        }
        //echo $this->getLastSql();
        return $contract_info;
    }
    
    
    /**
     * 根据合同编号提更改合同报销状态为报销申请中
     *
     * @access	public
     * @param	mixed  $reim_list_ids 报销单编号
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function sub_contract_to_reim_not_apply_by_reim_listid($reim_list_ids)
    {   
        $cond_where = "";
    
    	if(is_array($reim_list_ids) && !empty($reim_list_ids))
    	{
    		$ids_str = implode(',', $reim_list_ids);
    		$cond_where = " REIM_LIST_ID IN (".$ids_str.")";
    	}
    	else
    	{
    		$reim_list_id  = intval($reim_list_ids);
    		$cond_where = " REIM_LIST_ID = '".$reim_list_id."'";
    	}
        
        $update_arr['REIM_STATUS'] = $this->_conf_reim_status['not_apply'];
        $update_arr['REIM_LIST_ID'] = 0;
        
        $update_num = self::update_info_by_cond($cond_where, $update_arr);
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * 根据合同编号提更改合同报销状态为报销申请中
     *
     * @access	public
     * @param	mixed  $contract_ids 合同编号
     * @param	mixed  $reim_list_id 报销单编号
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function sub_contract_to_reim_applied_by_id($contract_ids, $reim_list_id)
    {   
        $update_num = 0;
        
        if(!empty($contract_ids) && $reim_list_id > 0 )
        {
            $update_arr['REIM_STATUS'] = $this->_conf_reim_status['applied'];
            $update_arr['REIM_LIST_ID'] = intval($reim_list_id);
            
            $update_num = self::update_info_by_id($contract_ids, $update_arr);
        }
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * 根据报销的单编号更改合同报销状态为已报销
     *
     * @access	public
     * @param	mixed  $reim_list_ids 报销单编号
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function sub_contract_to_reimbursed_by_reim_listid($reim_list_ids)
    {   
        $cond_where = "";
        
    	if(is_array($reim_list_ids) && !empty($reim_list_ids))
    	{
    		$ids_str = implode(',', $reim_list_ids);
    		$cond_where = " REIM_LIST_ID IN (".$ids_str.")";
    	}
    	else
    	{
    		$reim_list_id  = intval($reim_list_ids);
    		$cond_where = " REIM_LIST_ID = '".$reim_list_id."'";
    	}
        
        $update_arr['REIM_STATUS'] = $this->_conf_reim_status['reimbursed'];
        
        $update_num = self::update_info_by_cond($cond_where, $update_arr);
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * 根据采购明细编号更改合同报销状态为已报销
     *
     * @access	public
     * @param	mixed  $list_ids 采购明细编号
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function sub_contract_to_reimbursed_by_listid($list_ids)
    {   
        $cond_where = "";
        
    	if(is_array($list_ids) && !empty($list_ids))
    	{
    		$ids_str = implode(',', $list_ids);
    		$cond_where = " ID IN (".$ids_str.")";
    	}
    	else
    	{
    		$list_id  = intval($list_ids);
    		$cond_where = " ID = '".$list_id."'";
    	}
        
        $update_arr['REIM_STATUS'] = $this->_conf_reim_status['reimbursed'];
        
        $update_num = self::update_info_by_cond($cond_where, $update_arr);
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * 根据ID更新收益合同信息
     *
     * @access	public
     * @param	string  $ids 要更新的记录
     * @param array $update_arr 要跟新的字段
     * @return	
     */
    public function update_info_by_id($ids, $update_arr)
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
        
        $res = $this->table($table)->where($cond_where)->save($update_arr);
        
        return $res;
    }
    
    
    /**
     * 根据条件更新合同信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_info_by_cond($cond_where, $update_arr)
    {
    	$up_num = 0;
        
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{
    		$up_num = $this->where($cond_where)->save($update_arr);
            //echo $this->getLastSql();
    	}
        
    	return $up_num > 0  ? $up_num : FALSE;
    }

    /**
     * 判断合同是否为库存领用合同
     * @param $contractID
     * @return bool
     */
    public function isFromStockContract($contractID){
        // num 购买数量
        // use_num 领用数量
        if (empty($contractID)) {
            throw_exception("查询PURCHASE_LIST表, CONTRACT_ID不能为空");
        }

        //获取供应商类型
        $sql = "SELECT A.ID FROM ERP_CONTRACT A LEFT  JOIN  ERP_SUPPLIER B ON  A.SUPPLIER_ID = B.ID ";
        $sql .= " WHERE B.TYPE = 1 AND A.ID = " . $contractID;

        $result = $this->query($sql);
        if (is_array($result) && count($result)) {
             return true;
        }

        return false;
    }
}

/* End of file PurchaseContract.class.php */
/* Location: ./Lib/Model/PurchaseContract.class.php */
