<?php

/**
 * 现场发放MODEL
 *
 * @author liuhu
 */
class LocaleGrantedModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'LOCALE_GRANTED';
    
    /***发放记录报销状态描述***/
    private $_conf_reim_status_remark = array(
                                '0' => '未申请',
                                '1' => '已申请',
                                '2' => '已报销'
                            );
    
    /***发放记录报销状态***/
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
     * 添加现场发放信息
     * @param array $grant_info 现场发放信息
     * @return mixed 添加成功返回插入数据编号，插入失败返回FALSE
     */
    public function add_grant_info($grant_info)
    {   
        $insertId = FALSE;
        
    	if(is_array($grant_info) && !empty($grant_info))
    	{   
    		// 自增主键返回插入ID
    		$insertId = $this->add($grant_info);
    	}
        //echo $this->getLastSql();
    	return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据编号删除现场发放信息
     *
     * @access	protected
     * @param	mixed $ids 现场发放信息编号
     * @return	int 删除条数，0删除失败
     */
    public function delete_info_by_mid($ids)
    {
    	$cond_where = "";
    	 
    	if(is_array($ids) && !empty($ids))
    	{
    		$ids_str = implode(',', $ids);
    		$cond_where = " MID IN (".$ids_str.")";
    	}
    	else
    	{
    		$id  = intval($ids);
    		$cond_where = " MID = '".$id."'";
    	}
    	 
    	$delte_num = self::delete_info_by_cond($cond_where);
    	 
    	return $delte_num > 0  ? $delte_num : FALSE;
    }
    
    
    /**
     * 删除发放信息
     *
     * @access	public
     * @param	string  $cond_where 删除条件
     * @return	mixed 删除成功返回更新条数，删除返回FALSE
     */
    public function delete_info_by_cond($cond_where)
    {	
    	$del_num = 0;
    	if($cond_where != '')
    	{
    		$del_num = $this->where($cond_where)->delete();
    		//echo $this->getLastSql();
    	}
    
    	return $del_num > 0  ? $del_num : FALSE;
    }
    
    
    /**
     * 根据编号更改发放记录报销状态为报销申请中
     *
     * @access	public
     * @param	mixed  $$reim_listids 报销单编号
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function sub_granted_to_reim_not_apply_by_reim_listid($reim_listids)
    {   
        $cond_where = "";
    
    	if(is_array($reim_listids) && !empty($reim_listids))
    	{
    		$ids_str = implode(',', $reim_listids);
    		$cond_where = " REIM_LIST_ID IN (".$ids_str.")";
    	}
    	else
    	{
    		$reim_list_id  = intval($reim_listids);
    		$cond_where = " REIM_LIST_ID = '".$reim_list_id."'";
    	}
        
        $update_arr['REIM_STATUS'] = $this->_conf_reim_status['not_apply'];
        $update_arr['REIM_LIST_ID'] = 0;
        
        $update_num = self::update_info_by_cond($update_arr, $cond_where);
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * 根据发放记录编号更改发放记录报销状态为报销申请中
     *
     * @access	public
     * @param	mixed  $ids 发放记录编号
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function sub_granted_to_reim_not_apply_by_id($ids)
    {   
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
        
        $update_arr['REIM_STATUS'] = $this->_conf_reim_status['not_apply'];
        $update_arr['REIM_LIST_ID'] = 0;
        
        $update_num = self::update_info_by_cond($update_arr, $cond_where);
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * 根据编号更改发放记录报销状态为报销申请中
     *
     * @access	public
     * @param	mixed  $ids 发放记录编号
     * @param	mixed  $reim_list_id 报销单编号
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function sub_granted_to_reim_applied_by_id($ids, $reim_list_id)
    {   
        $update_num = 0;
        
        if(!empty($ids) && $reim_list_id > 0 )
        {
            $update_arr['REIM_STATUS'] = $this->_conf_reim_status['applied'];
            $update_arr['REIM_LIST_ID'] = intval($reim_list_id);

            $update_num = self::update_info_by_id($ids, $update_arr);
        }
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * 根据报销单编号更改发放记录报销状态为已报销
     *
     * @access	public
     * @param	mixed  $reim_list_ids 报销单编号
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function sub_granted_to_reimbursed_by_id($reim_list_ids)
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
        
        $update_num = self::update_info_by_cond($update_arr, $cond_where);
        
        return $update_num > 0 ? $update_num : FALSE;
    }
    
    
    /**
     * 更新发放信息
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_info_by_id($ids, $update_arr)
    {
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
    
    	$up_num = self::update_info_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 更新发放信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_info_by_cond($update_arr, $cond_where)
    {	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{
    		$up_num = $this->where($cond_where)->save($update_arr);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据条件获取信息
     *
     * @access	public
     * @param	mixed  $ids 发放记录ID
     * @param array $search_field 搜索字段
     * @return	array 
     */
    public function get_info_by_id($ids, $search_field = array())
    {
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
        
        if(is_array($search_field) && !empty($search_field) )
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
}

/* End of file LocaleGrantedModel.class.php */
/* Location: ./Lib/Model/LocaleGrantedModel.class.php */