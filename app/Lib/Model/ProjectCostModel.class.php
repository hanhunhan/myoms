<?php

/**
 * 项目成本表
 *
 * @author liuhu
 */
class ProjectCostModel extends Model {
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'COST_LIST';
    
    //成本来源入口
    private $_conf_cost_from = array(
                                '1' => '申请采购',
                                '2' => '采购合同签订',
                                '3' => '采购报销申请',
                                '4' => '采购报销通过',
                                '5' => '会员成交中介佣金',
                                '6' => '中介佣金申请报销',
                                '7' => '中介佣金报销通过',
                                '8' => '会员成交中介成交奖',
                                '9' => '中介成交奖申请报销',
                                '10' => '中介成交奖报销通过',
                                '11' => '会员成交置业佣金',
                                '12' => '置业佣金申请报销',
                                '13' => '置业佣金报销通过',
                                '14' => '会员成交置业成交奖',
                                '15' => '置业成交奖申请报销',
                                '16' => '置业成交奖报销通过',
                                '17' => '业务津贴',
                                '18' => '预算外其它费用津贴',
                                '19' => '预算外其它费用申请报销',
                                '20' => '预算外其它费用报销通过',
                                '21' => '成本划拨',
                                '22' => '发放现金',
                                '23' => '发放现金申请报销',
                                '24' => '发放现金报销通过',
                                '25' => '成本填充申请报销',
                                '26' => '成本填充报销通过',
    							'27' => '采购退库'
                            );
    
    //成本状态
    private $_conf_cost_status = array(
                            '1' => '已申请未发生',
                            '2' => '已发生未报销',
                            //'3' => '申请报销',
                            '4' => '已报销'
                        );
    
    //成本来源与成本状态对应关系
    private $_conf_status_from_map = array(
                                        '1' => array(1),
                                        '2' => array(2,5,8,11,14,18,22),
                                        '3' => array(3,6,9,12,15,19,23,25),
                                        '4' => array(4,7,10,13,16,17,20,21,24,26,27)
                                    );
    
    //成本类型
    private $_conf_cost_type = array(
                            '1' => '采购',
                            '2' => '中介佣金',
                            '3' => '中介成交奖',
                            '4' => '置业佣金',
                            '5' => '置业成交奖',
                            '6' => '业务津贴',
                            '7' => '预算外其它费用',
                            '8' => '成本划拨',
                            '9' => '发放现金',
                            '10' => '成本填充',
                        );
    
    //成本来源与成本类型对应关系
    private $_conf_type_from_map = array(
                                        '1' => array(1, 2, 3, 4, 27),
                                        '2' => array(5, 6, 7),
                                        '3' => array(8, 9, 10),
                                        '4' => array(11, 12, 13),
                                        '5' => array(14, 15, 16),
                                        '6' => array(17),
                                        '7' => array(18, 19, 20),
                                        '8' => array(21),
                                        '9' => array(22, 23, 24),
                                        '10' => array(25, 26)
                                    );
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * 返回成本来源
     *
     * @access	public
     * @param	none
     * @return	array 收益来源数组
     */
    public function get_conf_cost_from ()
    {
        return $this->_conf_cost_from;
    }
    
    
    /**
     * 返回成本状态描述
     *
     * @access	public
     * @param	none
     * @return	array 收益来源数组
     */
    public function get_conf_cost_status()
    {
        return $this->_conf_cost_status;
    }
    
    
    /**
     * 添加项目成本
     *
     * @access	public
     * @param	string  $cost_info 成本信息数组
     * @param  int     $cost_info['CASE_ID']    案例编号 【必填】
     * @param  int     $cost_info['ENTITY_ID']  业务编号 【必填】
     * @param  int     $cost_info['EXPEND_ID']  成本明细编号 【必填】
     * @param  int     $cost_info['ORG_ENTITY_ID']  原始业务编号 【必填】
     * @param  int     $cost_info['ORG_EXPEND_ID']  原始成本明细编号 【必填】
     * @param  int     $cost_info['EXPEND_FROM'] 成本来源 【必填】
     * @param  float   $cost_info['FEE'] 成本金额 【必填】
     * @param  string  $cost_info['FEE_REMARK'] 费用描述 【选填】
     * @param  float  $cost_info['INPUT_TAX'] 进项税 【选填】 
     * @param  int     $cost_info['ADD_UID']    操作用户编号 【必填】
     * @param  date    $cost_info['OCCUR_TIME'] 发生时间 【必填】
     * @param  int     $cost_info['ISFUNDPOOL'] 是否资金池（0否，1是） 【必填】
     * @param  int     $cost_info['IS_KF'] 成本类型ID 【必填】
     * @param  int     $cost_info['FEE_ID'] 成本类型ID 【必填】
     * @return	mixed  成功返回成本编号，失败返回FALSE
     */
    public function add_cost_info($cost_info)
    {   
        $insert_result = FALSE;
        $cost_arr = array();
        
        //案例编号
        $cost_info['CASE_ID'] = intval($cost_info['CASE_ID']);
        
        if($cost_info['CASE_ID'] > 0 )
        {   
            /**根据案例编号获取需要的案例信息**/
            $project_case = D('ProjectCase');
			$project = D('Project');
            $caseinfo = array();
            $search_field = array('SCALETYPE ', 'CUSER', 'PROJECT_ID');
            $caseinfo = $project_case->get_info_by_id($cost_info['CASE_ID'], $search_field);
            
            if(is_array($caseinfo) && !empty($caseinfo))
            {   
                //项目编号
                $cost_info['PROJECT_ID'] = !empty($caseinfo[0]['PROJECT_ID']) ? 
                                            intval($caseinfo[0]['PROJECT_ID']) : 0;
                //案例类型
                $cost_info['CASE_TYPE'] = !empty($caseinfo[0]['SCALETYPE']) ? 
                                            intval($caseinfo[0]['SCALETYPE']) : 0;
                //案例申请人
                $cost_info['USER_ID'] = !empty($caseinfo[0]['CUSER']) ? 
                                            intval($caseinfo[0]['CUSER']) : 0;
                //案例申请人所在部门
                $userinfo = array();
                $cond_where = "ID = '".$cost_info['USER_ID']."'";
                $userinfo = M('erp_users')->field('DEPTID,CITY')->where($cond_where)->find();
                $cost_info['DEPT_ID'] = !empty($userinfo['DEPTID']) ? 
                                            intval($userinfo['DEPTID']) : 0;
				$projectinfo = $project->get_info_by_id($cost_info['PROJECT_ID'],array('CITY_ID'));
                //案例申请人所在城市
                //$cost_info['CITY_ID'] = !empty($userinfo['CITY']) ?   intval($userinfo['CITY']) : 0;
				$cost_info['CITY_ID'] = !empty($projectinfo[0]['CITY_ID']) ?   intval($projectinfo[0]['CITY_ID']) : 0;
				
            }
        }
        else
        {
            return $insert_result;
        }
        
        //业务实体编号（采购申请单编号、业务津贴申请单编号……）
        $cost_info['ENTITY_ID'] = intval($cost_info['ENTITY_ID']);
        //成本明细编号
        $cost_info['EXPEND_ID'] = intval($cost_info['EXPEND_ID']);
        //原始业务实体编号（采购申请单编号、业务津贴申请单编号……）
        $cost_info['ORG_ENTITY_ID'] = intval($cost_info['ORG_ENTITY_ID']);
        //原始成本明细编号
        $cost_info['ORG_EXPEND_ID'] = intval($cost_info['ORG_EXPEND_ID']);
        //成本来源
        $cost_info['EXPEND_FROM'] = intval($cost_info['EXPEND_FROM']);
        //成本金额
        $cost_info['FEE'] = floatval($cost_info['FEE']);
        //成本金额描述(非必填)
        $cost_info['FEE_REMARK'] = strip_tags($cost_info['FEE_REMARK']);
        //进项税（非必填）
        $cost_info['INPUT_TAX'] = floatval($cost_info['INPUT_TAX']);
        //添加人ID
        $cost_info['ADD_UID'] = intval($cost_info['ADD_UID']);
        //成本发生时间
        $cost_info['OCCUR_TIME'] = $cost_info['OCCUR_TIME'];
        //成本状态
        $status = self::_get_status_by_from($cost_info['EXPEND_FROM']);
        $cost_info['STATUS'] = $status;
        
        if(empty($cost_info['STATUS']))
        { 
            return $insert_result;
        }
        
        $type = self::_get_type_by_from($cost_info['EXPEND_FROM']);
        
        if(empty($type))
        { 
            return $insert_result;
        }
        
        $cost_info['TYPE'] = $type;
        
        //是否资金池
        $cost_info['ISFUNDPOOL'] = intval($cost_info['ISFUNDPOOL']);
        //是否扣非
        $cost_info['ISKF'] = intval($cost_info['ISKF']);
        //成本类型ID
        $cost_info['FEE_ID'] = intval($cost_info['FEE_ID']);
        
        $insert_result = $this->add($cost_info);
        
        return $insert_result > 0 ? $insert_result : FALSE;
    }
    
    
    /**
     * 根据来源确认成本状态
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
     * 根据来源确认成本类型
     *
     * @access	public
     * @param	int  $from 来源标志
     * @return	mixed 匹配成功返回状态标志，匹配失败返回FALSE
     */
    private function _get_type_by_from($from)
    {   
        $type = FALSE;
        $from  = intval($from);
        
        if( $from > 0)
        {
            foreach($this->_conf_type_from_map as $key => $value)
            {
                if(in_array($from, $value))
                {
                    $type = $key;
                    break;
                }
            }
        }
        
        return $type;
    }
    
    
    /**
     * 修改项目成本信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	int  $case_id 案例编号
     * @param	int  $entity_id 业务实体编号（采购申请单编号，津贴申请单编号……）
     * @param	int  $expend_id 采购明细、津贴明细ID
     * @param	int  $status 成本记录状态
     * @return	mixed   删除成功返回更新条数，删除返回FALSE
     */
    public function update_income_info($update_arr, $case_id, $entity_id, $expend_id = 0, $status = '')
    {   
        $up_num = 0;
        
        $case_id  = intval($case_id);
        $entity_id = intval($entity_id);
        $expend_id = intval($expend_id);
        $status = intval($status);
        
        if($case_id > 0 && $entity_id > 0)
        {   
            $cond_where = "CASE_ID = '".$case_id."' AND ENTITY_ID = '".$entity_id."' ";
            $pay_id > 0 ? $cond_where .= " AND EXPEND_ID = '".$expend_id."'" : "";
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
    	}
        
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 删除指定项目成本信息
     *
     * @access	public
     * @param	int  $case_id 案例编号
     * @param	int  $entity_id 业务实体编号（采购申请单编号，津贴申请单编号……）
     * @param	int  $expend_id 采购明细、津贴明细ID
     * @param	int  $status 成本记录状态
     * @return	mixed   删除成功返回更新条数，删除返回FALSE
     */
    public function delete_income_info($case_id, $entity_id, $expend_id = 0, $status = '')
    {   
        $del_num = FALSE;
        
        $case_id  = intval($case_id);
        $entity_id = intval($entity_id);
        $expend_id = intval($expend_id);
        $status = intval($status);
        
        if($case_id > 0 && $entity_id > 0)
        {   
            $cond_where = "CASE_ID = '".$case_id."' AND ENTITY_ID = '".$entity_id."' ";
            $pay_id > 0 ? $cond_where .= " AND EXPEND_ID = '".$expend_id."'" : "";
            $status > 0 ? $cond_where .= " AND STATUS = '".$status."'" : "";
            
            $del_num = self::delete_info_by_cond($cond_where);
        }
        
        return $del_num;
    }
    
    
    /**
     * 根据条件删除信息
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
     * 根据条件获取成本信息
     * @param string $cond_where 条件
     * @param array  $search_arr 查询字段
     * return $info 成功:数组 \ 失败 ：false
     */
    public function get_cost_info_by_cond($cond_where,$search_arr)
    {
        $info = array();
        if(is_array($search_arr) && !empty($search_arr))
        {
            $info = $this->where($cond_where)->field($search_arr)->select();
        }
        return $info ? $info : false;
        
    }
}

/* End of file ProjectCostModel.class.php */
/* Location: ./Lib/Model/ProjectCostModel.class.php */