<?php
/**
 * 供应商管理类
 *
 * @author 
 */

class SupplierModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'SUPPLIER';
    
    /***供应商状态描述数组***/
    private $_conf_status_remark = array(
                                0 => '无效',
                                1 => '有效'
                            );
    
    /***供应商状态状态***/
    private $_conf_status = array( 
                                'invalid' => 0,
                                'valid' => 1
                            );
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * 获取供应商状态描述数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_status_remark()
    {
        return $this->_conf_status_remark;
    }
    
    
    /**
     * 获取供应商状态
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_status()
    {
        return $this->_conf_status;
    }
    
    
    /**
     * 添加供应商信息
     *
     * @access	public
     * @param	array  $supplier_info 供应商信息
     * @return	mixed  成功返回新增ID，失败返回FALSE
     */
    public function add_supplier_info ($supplier_info)
    {
        if(is_array($supplier_info) && !empty($supplier_info))
        {   
            // 自增主键返回插入ID
            $insertId = $this->add($supplier_info);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据编号更新明细信息
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
     * 更新信息
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
        //echo $this->getLastSql();
    	return $info;
    }
}

/* End of file SupplierModel.class.php */
/* Location: ./Lib/Model/SupplierModel.class.php */