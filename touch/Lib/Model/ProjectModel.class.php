<?php

/**
 * 项目信息管理类
 *
 * @author 
 */

class ProjectModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PROJECT';
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * 根据项目编号获取项目信息
     *
     * @access	public
     * @param  mixed $ids 项目编号
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 项目信息
     */
    public function get_info_by_id($ids, $search_field = array())
    {   
        $cond_where = "";
        $project_info = array();
        
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
        
        $project_info = self::get_info_by_cond($cond_where, $search_field);
        
        return $project_info;
    }
    
    
    /**
     * 根据条件获取项目信息
     *
     * @access	public
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 项目信息
     */
    public function get_info_by_cond($cond_where, $search_field = array())
    {   
        $project_info = array();
        
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $project_info;
        }
        
        if(is_array($search_field) && !empty($search_field))
        {
            $search_str = implode(',', $search_field);
            $project_info = $this->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $project_info = $this->where($cond_where)->select();
        }
        
        return $project_info;
    }
    
    
    /**
     * 获取当前用户的项目信息
     *
     * @access	public
     * @param	string $search_keyword 搜索关键词
     * @param	int $city_id 城市编号
     * @return	array 项目信息
     */
    public function get_my_project_list($search_keyword, $city_id = 0)
    {   
        $project_info = array();
        $search_keyword = strip_tags($search_keyword);
        
        //查询条件
        $cond_where = intval($city_id) ? "CITY_ID = 1 " : '';
        $cond_where .= !empty($cond_where) ? " AND " : '';
        $cond_where .= " PROJECTNAME LIKE '%".$search_keyword."%'";
        //echo $cond_where;
        $project_info = $this->get_info_by_cond($cond_where);
        
        return $project_info;
    }
    
    
    /**
     * 获取指定项目的收费佣金标准
     *
     * @access	public
     * @param	int $caseid 案例编号
     * @param	int $case_type 业务类型
     * @param	int $scaletype 收费类型
     * @param	int $status 费用状态
     * @return	array 费用信息
     */
    public function get_feescale_by_cid($caseid, $scaletype = '', $status = '')
    {	
    	$scale_info = array(); 
    	
    	if($caseid > 0)
    	{	
    		/**查询条件**/
	    	$cond_where = "CASE_ID = '".$caseid."'";
            
	    	!empty($scaletype) && $scaletype != '' ?  
	    		$cond_where .= " AND SCALETYPE = '".intval($scaletype)."'" : '';
	    	
	    	!empty($status) && $status != '' ? 
	    		$cond_where .= " AND STATUS = '".intval($status)."'" : '';
	    	
	    	/**数据表**/
	    	$table_name = $this->tablePrefix.'FEESCALE';
	    	$scale_info = $this->table($table_name)->where($cond_where)->select();
    	}
    	
    	return $scale_info;
    }
    
    
    /*
     * 根据项目ID修改项目信息
     * @param int $id 项目id
     * @param array $update 要修改的字段
     * return 
     */
    public function update_prj_info_by_id($ids, $update_arr)
    {
        $conf_where = "";
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",",$ids);
            $cong_where = "ID IN ($id_str)";
        }
        else if($ids)
        {
            $conf_where = "ID = $ids";
        }
        else if(!$ids)
        {
            return false;
        }
        
        $res = $this->where($conf_where)->save($update_arr);
        return $res;
    }
}
/* End of file ProjectModel.class.php */
/* Location: ./Lib/Model/ProjectModel.class.php */