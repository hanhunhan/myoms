<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class BenefitsModel extends Model{
     
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'BENEFITS';
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    //业务津贴申请状态标志 未申请^0^已申请,审核中^1^审核通过^2^
    protected $_benefits_status_remark = array(
                                1=>"未申请",
                                2=>"审核中",
                                3=>"审核通过",
                                4=>"审核未通过",
    );
    
    protected $_benefits_status = array(
                               "no_apply"=>1,       //未申请
                               "auditing"=>2,       //已申请，审核中
                               "passed"=>3,         //审核通过
                               "no_audit"=>4,       //审核未通过
    );
    
    //大额业务津贴报销状态标志
    protected $_cost_status_remark = array(
                            1=>"未申请报销",
                            2=>"未提交",
                            3=>"已提交",
                            4=>"已报销"
    );
    
    //大额业务津贴报销状态
    protected $_cost_status = array(
                            "no_apply_reim"=>1,       //未申请报销
                            "applied_reim"=>2,    //已申请 未提交
                            "auditing_reim"=>3,  //已提交，审核中
                            "have_reimed"=>4    //已报销
    );
    //未申请报销^1^已申请，未提交^2^已提交，审核中^3^审核通过，已报销^4^
    


    //获取津贴申请状态
    public function get_benefits_status(){
        return $this->_benefits_status;
    }
    
    //获取状态标志
    public function get_benefits_status_remark(){
        return $this->_benefits_status_remark;
    }
    
    //获取报销状态标志
    public function get_cost_status_remark(){
        return $this->_cost_status_remark;
    }
    
    //获取报销状态
     public function get_cost_status(){
        return $this->_cost_status;
    }
    
    
    //新增业务津贴
    public function add_benefits($data){
        $table = $this->tablePrefix.$this->tableName;
        $res = $this->table($table)->add($data);
        //echo $this->model->getLastSql();
        return $res;
    }
    
    /**
     * 根据业务津贴ID查询业务津贴信息
     *
     * @access	public
     * @param  mixed $ids 业务津贴编号
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 项目信息
     */
    public function get_info_by_id($ids, $search_field = array())
    {   
        $cond_where = "";
        $benefits_info = array();
        
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
        
        $benefits_info = self::get_info_by_cond($cond_where, $search_field);
        
        return $benefits_info;
    }
    
    /**
     * 根据条件获取津贴信息
     *
     * @access	public
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 项目信息
     */
    public function get_info_by_cond($cond_where, $search_field = array())
    {   
        $benefits_info = array();
        
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $project_info;
        }
        
        if(is_array($search_field) && !empty($search_field))
        {
            $search_str = implode(',', $search_field);
            $benefits_info = $this->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $benefits_info = $this->where($cond_where)->select();
        }
        
        return $benefits_info;
    }
    
     /**
     * 根据ID跟新业务津贴信息
     *
     * @access	public
     * @param	mixed  $ids 要更新的记录
     * @param array $update_arr 要跟新的字段
     * @return	
     */
    public function update_info_by_id($ids,$update_arr){
        $table = $this->tablePrefix.$this->tableName;
        if(is_array($ids) && !empty($ids)){
            $id_str = implode(",", $ids);
            $conf_where = "ID in($id_str)";
        }else{
            $conf_where = "ID=$ids";
        }
        $res = $this->table($table)->where($conf_where)->save($update_arr);
        //echo $this->_sql();
        return $res;
    }
    
    /**
     * 根据ID删除津贴信息
     * @param $ids mixed 
     * return $del_num 影响的行数
     */
    public function del_info_by_id($ids)
    {
        $del_num = "";
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }
        $del_num = self::del_info_by_cond($cond_where);
        return $del_num ? $del_num : false;
    }
    
    /**
     * 根据条件删除津贴信息
     * @param $cond_where  string 条件 
     * return $del_num 影响的行数
     */
    public function del_info_by_cond($cond_where)
    {
        $del_num = false;
        $del_num = $this->where($cond_where)->delete();
        return $del_num ? $del_num : false;
    }

    public function addFundPoolCostApply($data) {
        $result = false;
        if (notEmptyArray($data)) {
            $result = D('ProjectCost')->add_cost_info($data);
        }

        return $result;
    }

    public function getFundPoolCost($bizId) {
        $response = array();
        if ($bizId) {
            $sql = <<<SQL
              SELECT b.*,
                    u.deptid,
                    p.city_id
              FROM erp_benefits b
              LEFT JOIN erp_users u ON u.id = b.auser_id
              LEFT JOIN erp_project p ON p.id = b.project_id
              WHERE b.id = %d
SQL;
            $dbResult = $this->query(sprintf($sql, $bizId));
            if (notEmptyArray($dbResult)) {
                $dbResult = $dbResult[0];
                $response['CASE_ID'] = $dbResult['CASE_ID'];  //案例编号 【必填】
                $response['CASE_TYPE'] = $dbResult['SCALE_TYPE'];  // 项目类型
                $response['ENTITY_ID'] = $bizId;  // 业务实体编号 【必填】
                $response['EXPEND_ID'] = $bizId;  // 成本明细编号 【必填】
                $response['ORG_ENTITY_ID'] = $bizId;  // 业务实体编号 【必填】
                $response['ORG_EXPEND_ID'] = $bizId;
                $response['FEE'] = $dbResult['AMOUNT'];  // 成本金额 【必填】
                $response['ADD_UID'] = $_SESSION['uinfo']['uid'];  //操作用户编号 【必填】
                $response['OCCUR_TIME'] = date('Y-m-d H:i:s');  //发生时间 【必填】
                $response['ISFUNDPOOL'] = 1;  // 是否资金池（0否，1是） 【必填】
                $response['ISKF'] = 1;  // 是否扣非 【必填】
                $response['FEE_REMARK'] = '支付第三方费用申请报销'; //费用描述 【选填】
                $response['INPUT_TAX'] = 0; // 进项税 【选填】
                $response['FEE_ID'] = 80; // 支付第三方费用
                $response['EXPEND_FROM'] = 32; // 支付第三方费用
                $response['STATUS'] = 2;  //
                $response['PROJECT_ID'] = $dbResult['PROJECT_ID'];
                $response['USER_ID'] = $dbResult['AUSER_ID'];
                $response['DEPT_ID'] = $dbResult['DEPTID'];
                $response['CITY_ID'] = $dbResult['CITY_ID'];
                $response['ISCOST'] = $dbResult['ISCOST'];
                $response['TYPE'] = 16;  // 成本类型为支付第三方费用
            }
        }

        return $response;
    }
}