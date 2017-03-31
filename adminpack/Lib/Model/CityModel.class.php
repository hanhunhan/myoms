<?php

/**
 * 城市信息管理类
 *
 * @author liuhu
 */
class CityModel extends Model{
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'CITY';
    
    
    //构造函数
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    /**
     * 根据城市编号获取城市信息
     *
     * @access	public
     * @param	int  $city_id 城市编号
     * @param   array $search_field 查询字段
     * @return	array
     */
    public function get_city_info_by_id($city_id, $search_field = array())
    {
        $info = array();
        
        $city_id = intval($city_id);
        if($city_id <= 0)
        {
            return $info;
        }
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->field($search_str)->where("ID = $city_id")->find();
        }
        else
        {
            $info = $this->where("ID = $city_id")->find();
        }
        
        return $info;
    }
    
    
    /**
     * 根据城市拼音获取城市信息
     *
     * @access	public
     * @param	string  $city_py 城市拼音缩写
     * @param   array $search_field 查询字段
     * @return	array
     */
    public function get_city_info_by_py($city_py, $search_field = array())
    {
    	$info = array();
    
    	$city_py = strtolower(strip_tags($city_py));
    	
    	if($city_py == '')
    	{
    		return $info;
    	}
    
    	if(is_array($search_field) && !empty($search_field) )
    	{
    		$search_str = implode(',', $search_field);
    		$info = $this->field($search_str)->where("PY = '".$city_py."'")->find();
    	}
    	else
    	{
    		$info = $this->where("PY = '".$city_py."'")->find();
    	}
    	
    	return $info;
    }
}
