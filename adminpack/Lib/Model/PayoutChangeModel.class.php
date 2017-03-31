<?php

/* 
 * 垫资比例Model类
 * 
 */
class PayoutChangeModel extends Model{
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PAYOUT';
    
    public function __construct() {
        parent::__construct();
    }
    
    //状态标识
    protected $_payout_status_remark = array(
                                1=>"未申请",
                                2=>"已申请，审核中",
                                3=>"审核通过",
                                4=>"审核未通过",
                                
                            );
    //状态
    protected $_payout_status = array(
                                "no_audit"=>1,  //未申请
                                "applied"=>2,   //已申请，审核中
                                "passed"=>3,    //审核通过
                                "no_passed"=>4, //审核未
                                
                            );
    
    public function get_payout_status_remark(){
        return $this->_payout_status_remark;
    }
    
    public function get_payout_status(){
        return $this->_payout_statusk;
    }
    /**
     * 新增垫资比例记录
     * @param array $data 要新增字段的键值对
     * return 成功：插入的ID \ 失败：false
     */
    public function add_payout_info($data)
    {
        $insertid = "";
        if(is_array($data) && !empty($data))
        {
            $insertid = $this->add($data);
        }
        return $insertid ? $insertid : false;
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
        
        return $info;
    }
    
    /**
     * 根据ID信息
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

    	$up_num = self::update_info_by_cond($cond_where,$update_arr);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据指定条件更新退款明细信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_info_by_cond( $cond_where,$update_arr)
    {	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$options['table'] = $this->tablePrefix.$this->tableName;
    		$up_num = $this->where($cond_where)->save($update_arr, $options);
    		//echo $this->getLastSql();
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    
}

