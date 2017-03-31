<?php

/**
 * 案例模块
 *
 * @author liuhu
 */
class ProjectCaseModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'CASE';
    
    /***项目业务类型***/
    private  $_conf_case_type = array(
                                        'ds' => 1,   //电商
                                        'fx' => 2,   //分销
                                        'yg' => 3,   //硬广
                                        'hd' => 4,   //独立活动
                                        'cp' => 5,   //产品
                                        'xmxhd' => 7  //项目下活动
                                    );
    
    /***项目业务类型***/
    private  $_conf_case_type_remark = array(
                                            1 => '电商',
                                            2 => '分销',
                                            3 => '硬广',
                                            4 => '活动',
                                            5 => '产品',
                                            7 => '项目活动',
                                        );
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    /**
     * 获取项目业务类型
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_case_type()
    {
    	return $this->_conf_case_type;
    }
    
    
    /**
     * 获取项目业务类型描述
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_case_type_remark()
    {
    	return $this->_conf_case_type_remark;
    }
    
    
    /**
     * 根据案例编号获取案例信息
     *
     * @access	public
     * @param  mixed $cids 案例编号
     * @param array $search_field 搜索字段
     * @return	array 案例信息
     */
    public function get_info_by_id($cids, $search_field = array())
    {   
        $cond_where = "";
        $case_info = array();
        
        if(is_array($cids) && !empty($cids))
        {   
            $ids_str = implode(',', $cids);
            $cond_where = " ID IN (".$ids_str.")";
        }
        else
        {
            $id  = intval($cids);
            $cond_where = " ID = '".$id."'";
        }
        
        $case_info = self::get_info_by_cond($cond_where, $search_field);
        
        return $case_info;
    }
    
    
    /**
     * 根据项目编号获取案例信息
     *
     * @access	public
     * @param  mixed $ids 项目编号
     * @param	string  $case_type 案例类型字符描述(ds\fx\yg……)
     * @param array $search_field 搜索字段
     * @return	array 项目信息
     */
    public function get_info_by_pid($ids, $case_type = '', $search_field = array())
    {   
        $cond_where = "";
        $project_info = array();
        
        if(is_array($ids) && !empty($ids))
        {   
            $ids_str = implode(',', $ids);
            $cond_where = " PROJECT_ID IN (".$ids_str.")";
        }
        else
        {
            $id  = intval($ids);
            $cond_where = " PROJECT_ID = '".$id."'";
        }
        
        $case_type = strip_tags($case_type);
        if($case_type != '')
        {   
            $scaletype = !empty($this->_conf_case_type[$case_type]) ? 
                    $this->_conf_case_type[$case_type] : 0;
            $scaletype > 0 ? $cond_where .= " AND SCALETYPE = '".$scaletype."'" : '';
        }
        
        $project_info = self::get_info_by_cond($cond_where, $search_field);
        
        return $project_info;
    }
    
    
    /**
     * 根据项目编号查询是否存在某种业务类型
     *
     * @access	public
     * @param	int  $prj_id 项目编号
     * @param  string $case_type 业务类型字符串描述
     * @return	boolean 存在返回TRUE,不存在返回FALSE
     */
    public function is_exists_case_type($prj_id, $case_type)
    {   
        $num = 0;
        
        $prj_id  = intval($prj_id);
        $cond_where = " PROJECT_ID = '".$prj_id."'";

        $case_type = strip_tags($case_type);
        $scaletype = !empty($this->_conf_case_type[$case_type]) ? 
                $this->_conf_case_type[$case_type] : '';
        
        if($scaletype != '')
        {
            $cond_where .= " AND SCALETYPE = '".$scaletype."' ";
            $num = $this->where($cond_where)->count();
        }
        
        return $num > 0 ? TRUE : FALSE;
    }
    
    
    /**
     * 根据条件获取项目案例信息
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
        //echo $this->getLastSql();
        return $project_info;
    }
	 /*
     *业务类型状态变更 决算 终止 
     * @param int $caseid 业务类型id
     *  
     * return 
     */
	 public function update_case_status($id,$status){
		$table_name = $this->tablePrefix.'FINALACCOUNTS';
		$cond_where = "ID='$id'";
		$one = $this->table($table_name)->where($cond_where)->find();//FINALACCOUNTS

		//$table_name = $this->tablePrefix.'CASE';
		$conf_where = "ID= ".$one['CASE_ID'];
		$arr['FSTATUS'] = $status;
		$res = $this->where($conf_where)->save($arr) ; 
		return $res;
	 }
 

	 /*
     *业务类型状态变更  立项
     * @param int $caseid 业务类型id
     *  
     * return 
     */
	 public function update_case_status_pro($prjid,$status){
		 
		$conf_where = "PROJECT_ID= ".$prjid;
		$arr['FSTATUS'] = $status;
		$res = $this->where($conf_where)->save($arr) ; 
		return $res;
	 }
 

    /**
     * 根据案例编号获取案例类型
     *
     * @access	public
     * @param	int  $cid 案例编号
     * @return	string 案例类型
     */
    public function get_casetype_by_caseid($cid)
    {   
        $cid = intval($cid);
        $case_type = "";
        $search_field = array('SCALETYPE', 'PARENTID');
        $case_info = $this->get_info_by_id($cid, $search_field);
        
        if( !empty($case_info) )
        {   
            if($case_info[0]['PARENTID'] > 0)
            {
                $case_type = $this->get_casetype_by_caseid($case_info[0]['PARENTID']);
            }
            else
            {   
                $conf_case_type = self::get_conf_case_type();
                $conf_case_type_flip = array_flip( $conf_case_type );
                $case_type = $conf_case_type_flip[$case_info[0]['SCALETYPE']];
            }
        }
        
        return $case_type;
    }
 
}

/* End of file ProjectCaseModel.class.php */
/* Location: ./Lib/Model/ProjectCaseModel.class.php */