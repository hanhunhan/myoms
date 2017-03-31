<?php

/**
 * ��Ŀ��Ϣ������
 *
 * @author 
 */

class ProjectModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PROJECT';
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * ������Ŀ��Ż�ȡ��Ŀ��Ϣ
     *
     * @access	public
     * @param  mixed $ids ��Ŀ���
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��Ŀ��Ϣ
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
     * ����������ȡ��Ŀ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��Ŀ��Ϣ
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
     * ��ȡ��ǰ�û�����Ŀ��Ϣ
     *
     * @access	public
     * @param	string $search_keyword �����ؼ���
     * @param	int $city_id ���б��
     * @return	array ��Ŀ��Ϣ
     */
    public function get_my_project_list($search_keyword, $city_id = 0)
    {   
        $project_info = array();
        $search_keyword = strip_tags($search_keyword);
        
        //��ѯ����
        $cond_where = intval($city_id) ? "CITY_ID = 1 " : '';
        $cond_where .= !empty($cond_where) ? " AND " : '';
        $cond_where .= " PROJECTNAME LIKE '%".$search_keyword."%'";
        //echo $cond_where;
        $project_info = $this->get_info_by_cond($cond_where);
        
        return $project_info;
    }
    
    
    /**
     * ��ȡָ����Ŀ���շ�Ӷ���׼
     *
     * @access	public
     * @param	int $caseid �������
     * @param	int $case_type ҵ������
     * @param	int $scaletype �շ�����
     * @param	int $status ����״̬
     * @return	array ������Ϣ
     */
    public function get_feescale_by_cid($caseid, $scaletype = '', $status = '')
    {	
    	$scale_info = array(); 
    	
    	if($caseid > 0)
    	{	
    		/**��ѯ����**/
	    	$cond_where = "CASE_ID = '".$caseid."'";
            
	    	!empty($scaletype) && $scaletype != '' ?  
	    		$cond_where .= " AND SCALETYPE = '".intval($scaletype)."'" : '';
	    	
	    	!empty($status) && $status != '' ? 
	    		$cond_where .= " AND STATUS = '".intval($status)."'" : '';
	    	
	    	/**���ݱ�**/
	    	$table_name = $this->tablePrefix.'FEESCALE';
	    	$scale_info = $this->table($table_name)->where($cond_where)->select();
    	}
    	
    	return $scale_info;
    }
    
    
    /*
     * ������ĿID�޸���Ŀ��Ϣ
     * @param int $id ��Ŀid
     * @param array $update Ҫ�޸ĵ��ֶ�
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