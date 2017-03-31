<?php

/**
 * 分销会员管理类
 *
 * @author liuhu
 */
class MemberDistributionModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'MEMBER_DISTRIBUTION';
    
    
    //构造函数
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    /**
     * 添加分销会员信息
     * @param array $member_info 会员信息数组
     * @return mixed 添加成功返回插入数据编号，插入失败返回FALSE
     */
    public function add_member_info($member_info)
    {
    	if(is_array($member_info) && !empty($member_info))
    	{
    		// 自增主键返回插入ID
    		$insertId = $this->add($member_info);
    	}
    
    	return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据编号删除分销会员信息
     *
     * @access	protected
     * @param	mixed $ids 电商会员编号
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
     * 根据编号删除分销会员信息
     *
     * @access	protected
     * @param	mixed $ids 要删除的分销会员编号
     * @return	int 删除条数，0删除失败
     */
    public function delete_info_by_id($ids)
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
    	
    	$delte_num = self::delete_info_by_cond($cond_where);
    	
    	return $delte_num > 0  ? $delte_num : FALSE;
    }

    
    /**
     * 删除分销会员信息
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
     * 根据电商会员编号更新分销会员信息
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_info_by_mid($ids, $update_arr)
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
    
    	$up_num = self::update_info_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据分销会员编号更新分销会员信息
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_info_by_id($ids, $update_arr)
    {
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
     * 更新某条分销会员信息
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
            //echo $this->getLastSql();
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据会员编号获取分销会员信息（单一用户）
     *
     * @access	public
     * @param  int $id 搜索ID
     * @param array $search_field 搜索字段
     * @return	array 分销会员信息
     */
    public function get_info_by_id($id, $search_field = array())
    {   
        $info = array();
        
        $id = intval($id);
        if($id <= 0)
        {
            return $info;
        }
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->field($search_str)->where("ID = $id")->find();
        }
        else
        {
            $info = $this->where("ID = $id")->find();
        }
        //echo $this->_sql();
        return $info;
    }
    
    /**
     * 根据会员编号获取分销会员会员信息（多用户）
     *
     * @access	public
     * @param  array $ids 搜索ID
     * @param array $search_field 搜索字段
     * @return	array 分销会员会员信息
     */
    public function get_info_by_ids($ids, $search_field = array())
    {   
        $info = array();
        
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",",$ids);
            $conf_where = "ID IN ($id_str)";
        }
        else
        {
            $conf_where = "1 = 0";
        } 
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->field($search_str)->where($conf_where)->select();
        }
        else
        {
            $info = $this->where($conf_where)->select();
        }
        //echo $this->_sql();
        return $info;
    } 
    
   /**
     * 根据条件获取分销会员信息
     *
     * @access	public
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 项目信息
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

/* End of file MemberDistributionModel.class.php */
/* Location: ./Lib/Model/MemberDistributionModel.class.php */