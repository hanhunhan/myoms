<?php

/**
 * ������Ϣ������
 *
 * @author liuhu
 */
class CityModel extends Model{
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'CITY';
    
    
    //���캯��
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    /**
     * ���ݳ��б�Ż�ȡ������Ϣ
     *
     * @access	public
     * @param	int  $city_id ���б��
     * @param   array $search_field ��ѯ�ֶ�
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
     * ���ݳ���ƴ����ȡ������Ϣ
     *
     * @access	public
     * @param	string  $city_py ����ƴ����д
     * @param   array $search_field ��ѯ�ֶ�
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
