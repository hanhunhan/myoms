<?php

/**
 * 项目收益模块
 *
 * @author liuhu
 */
class ProjectIncomeModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'INCOME_LIST';
    
    //收入来源入口
    private $_conf_income_from = array(
                                '1' => '电商会员支付',
                                '2' => '确认电商会员收入',
                                '3' => '电商会员开票',
                                '4' => '电商会员退款',
                                '5' => '删除电商会员收入',
                                '6' => '成本划拨',
                                '7' => '分销会员回款',
                                '8' => '分销会员开票',
                                '9' => '修改分销会员回款',
                                '10' => '删除分销会员回款',
                                '11' => '硬广回款',
                                '12' => '硬广开票',
                                '13' => '修改硬广回款',
                                '14' => '删除硬广回款',
                                '15' => '活动回款',
                                '16' => '活动开票',
                                '17' => '修改活动回款',
                                '18' => '删除活动回款',
                                '19' => '成本划拨收益'
                            );
    
    //收入状态
    private $_conf_income_status = array(
                            '1' => '业务预收',
                            '2' => '财务(确认)预收',
                            '3' => '开票收入',
                            '4' => '回款收入'
                        );
    
   //收入来源与收入状态对应关系
   private $_conf_status_from_map = array(
                                        '1' => array(1),
                                        '2' => array(2,19),
                                        '3' => array(3,8,12,16),
                                        '4' => array(7,11,15)
                                    );
   
   //需要查询最新收益记录状态的收益来源
   private $_conf_get_last_income_status = array(4);
   
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * 返回收益来源
     *
     * @access	public
     * @param	none
     * @return	array 收益来源数组
     */
    public function get_conf_income_from ()
    {
        return $this->_conf_income_from;
    }
    
    
    /**
     * 返回收益状态描述
     *
     * @access	public
     * @param	none
     * @return	array 收益来源数组
     */
    public function get_conf_income_status()
    {
        return $this->_conf_income_status;
    }
    
    
    /**
     * 添加项目收益
     *
     * @access	public
     * @param	string  $income_info 收入信息数组
     * @param  int     $cost_info['CASE_ID']    案例编号 【必填】
     * @param  int     $income_info['ENTITY_ID']  业务实体编号 【必填】
     * @param  int     $income_info['PAY_ID']  收入明细编号 【必填】
     * @param  int     $income_info['INCOME_FROM'] 收入来源 【必填】
     * @param  float   $income_info['INCOME'] 收入金额 【必填】
     * @param  string  $income_info['INCOME_REMARK'] 收入描述 【选填】
     * @param  float   $income_info['OUTPUT_TAX'] 销项税 【选填】 
     * @param  int     $income_info['ADD_UID']    操作用户编号 【必填】
     * @param  date    $income_info['OCCUR_TIME'] 发生时间 【必填】
     * @return	mixed  成功返回收益编号，失败返回FALSE
     */
    public function add_income_info($income_info)
    {   
        $insert_result = FALSE;
        $income_arr = array();
        
        //案例编号
        $income_arr['CASE_ID'] = intval($income_info['CASE_ID']);
        if($income_arr['CASE_ID'] > 0 )
        {   
            /**根据案例编号获取需要的案例信息**/
            $project_case = D('ProjectCase');
            $caseinfo = array();
            $search_field = array('SCALETYPE ', 'CUSER', 'PROJECT_ID');
            $caseinfo = $project_case->get_info_by_id($income_arr['CASE_ID'], $search_field);
            
            if(is_array($caseinfo) && !empty($caseinfo))
            {   
                //项目编号
                $income_arr['PROJECT_ID'] = !empty($caseinfo[0]['PROJECT_ID']) ? 
                                            intval($caseinfo[0]['PROJECT_ID']) : 0;
                //案例类型
                $income_arr['CASE_TYPE'] = !empty($caseinfo[0]['SCALETYPE']) ? 
                                            intval($caseinfo[0]['SCALETYPE']) : 0;
                //案例申请人
                $income_arr['USER_ID'] = !empty($caseinfo[0]['CUSER']) ? 
                                            intval($caseinfo[0]['CUSER']) : 0;
                //案例申请人所在部门
                $userinfo = array();
                $cond_where = "ID = '".$income_arr['USER_ID']."'";
                $userinfo = M('erp_users')->field('DEPTID')->where($cond_where)->find();
                $income_arr['DEPT_ID'] = !empty($userinfo['DEPTID']) ? 
                                            intval($userinfo['DEPTID']) : 0;
                //案例申请人部门所在城市
                $deptinfo = array();
                $cond_where = "ID = '".$income_arr['DEPT_ID']."'";
                $deptinfo = M('erp_dept')->field('CITY_ID')->where($cond_where)->find();
                $income_arr['CITY_ID'] = !empty($deptinfo['CITY_ID']) ? 
                                            intval($deptinfo['CITY_ID']) : 0;
            }
        }
        else
        {
            return $insert_result;
        }
        
        //业务实体编号（会员编号、广告合同编号、划拨申请单编号……）
        $income_arr['ENTITY_ID'] = intval($income_info['ENTITY_ID']);
        //收益明细编号
        $income_arr['PAY_ID'] = intval($income_info['PAY_ID']);
        //收入来源
        $income_arr['INCOME_FROM'] = intval($income_info['INCOME_FROM']);
        //收入金额
        $income_arr['INCOME'] = floatval($income_info['INCOME']);
        //收入金额描述(非必填)
        $income_arr['INCOME_REMARK'] = strip_tags($income_info['INCOME_REMARK']);
        //销项税（非必填）
        $income_arr['OUTPUT_TAX'] = floatval($income_info['OUTPUT_TAX']);
        //添加人ID
        $income_arr['ADD_UID'] = intval($income_info['ADD_UID']);
        //收入发生时间
        $income_arr['OCCUR_TIME'] = $income_info['OCCUR_TIME'];
        //收入状态
        if(in_array($income_arr['INCOME_FROM'], $this->_conf_get_last_income_status))
        {   
            /**获取最新一条收益状态**/
            $last_income = $this->get_last_income_by_pid($income_arr['CASE_ID'], 
                    $income_arr['ENTITY_ID'], $income_arr['PAY_ID']);
            
            $status = !empty($last_income['STATUS']) ? 
                        intval($last_income['STATUS']) : 0;
        }
        else
        {
            $status = self::_get_status_by_from($income_arr['INCOME_FROM']);
        }
        
        $income_arr['STATUS'] = $status;

        if(empty($income_arr['STATUS']))
        { 
            return $insert_result;
        }
        $insert_result = $this->add($income_arr);
        return $insert_result > 0 ? $insert_result : FALSE;
    }
    
    
    /**
     * 修改项目收益信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	int  $case_id 案例编号
     * @param	int  $entity_id 业务实体编号（会员编号，或者广告合同编号……）
     * @param	int  $pay_id 支付明细编号或者开票记录编号
     * @param	int  $status 收益状态
     * @return	mixed   删除成功返回更新条数，删除返回FALSE
     */
    public function update_income_info($update_arr, $case_id, $entity_id, $pay_id = 0, $status = '')
    {   
        $up_num = 0;
        
        $case_id  = intval($case_id);
        $entity_id = intval($entity_id);
        $pay_id = intval($pay_id);
        $status = intval($status);
        
        if($case_id > 0 && $entity_id > 0)
        {   
            $cond_where = "CASE_ID = '".$case_id."' AND ENTITY_ID = '".$entity_id."' ";
            $pay_id > 0 ? $cond_where .= " AND PAY_ID = '".$pay_id."'" : "";
            $status > 0 ? $cond_where .= " AND STATUS = '".$status."'" : "";
            
            $up_num = self::update_info_by_cond($update_arr, $cond_where);
        }
        
        return $up_num;
    }
    
    
    /**
     * 根据条件更新信息
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
     * 删除项目收益信息
     *
     * @access	public
     * @param	int  $case_id 案例编号
     * @param	int  $entity_id 业务实体编号（会员编号，或者广告合同编号……）
     * @param	int  $pay_id 支付明细编号或者开票记录编号
     * @param	int  $status 收益状态
     * @return	mixed   删除成功返回更新条数，删除返回FALSE
     */
    public function delete_income_info($case_id, $entity_id, $pay_id = 0, $status = '')
    {   
        $del_num = FALSE;
        
        $case_id  = intval($case_id);
        $entity_id = intval($entity_id);
        $pay_id = intval($pay_id);
        $status = intval($status);
        
        if($case_id > 0 && $entity_id > 0)
        {   
            $cond_where = "CASE_ID = '".$case_id."' AND ENTITY_ID = '".$entity_id."' ";
            $pay_id > 0 ? $cond_where .= " AND PAY_ID = '".$pay_id."'" : "";
            $status > 0 ? $cond_where .= " AND STATUS = '".$status."'" : "";
            
            $del_num = self::delete_info_by_cond($cond_where);
        }
        
        return $del_num;
    }
    
    
    /**
     * 删除收益信息
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
    	}
    
    	return $del_num > 0  ? $del_num : FALSE ;
    }
    
    
    /**
     * 根据来源确认收益状态
     *
     * @access	public
     * @param	int  $from 来源标志
     * @return	mixed 匹配成功返回状态标志，匹配失败返回FALSE
     */
    private function _get_status_by_from($from)
    {   
        $status = FALSE;
        $from  = intval($from);
        
        if( $from > 0)
        {
            foreach($this->_conf_status_from_map as $key => $value)
            {
                if(in_array($from, $value))
                {
                    $status = $key;
                    break;
                }
            }
        }
        
        return $status;
    }
    
    
    /**
     * 根据支付明细编号查询最近一条收入明细记录
     *
     * @access	public
     * @param	int  $case_id 案例编号
     * @param	int  $entity_id 业务实体编号（会员编号，或者广告合同编号……）
     * @param	int  $pay_id 支付明细编号或者开票记录编号
     * @return	array 收入明细
     */
    public function get_last_income_by_pid($case_id, $entity_id, $pay_id)
    {   
        $income_info = array();
        
        $case_id = intval($case_id);
        $entity_id = intval($entity_id);
        $pay_id = intval($pay_id);
        
        if($case_id > 0 && $entity_id > 0 && $pay_id > 0)
        {
            $cond_where = "CASE_ID = '".$case_id."' AND "
                    . " ENTITY_ID = '".$entity_id."' AND PAY_ID = '".$pay_id."' ";
            $income_info = $this->where($cond_where)->order('ID DESC')->find();
        }
        
        return $income_info;
    }
}

/* End of file ProjectIncomeModel.class.php */
/* Location: ./Lib/Model/ProjectIncomeModel.class.php */