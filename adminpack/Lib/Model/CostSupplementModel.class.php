<?php

/**
 * 成本填充MODEL
 *
 * @author liuhu
 */
class CostSupplementModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'COST_SUPPLEMENT';
    
    //状态标识
    private $_conf_cost_supplement_status_remark = array(
                    "1" => "未报销",
                    "2" => "申请中",
                    "3" => "已报销"
        );
    
    //状态
    private $_conf_cost_supplement_status = array(
                    "no_apply"   => 1,              //未申请
                    "appling"    => 2,              //申请中
                    "have_reim"  => 3               //已报销
    );
    
    //成本填充类型标识
    private $_conf_cost_sup_type_remark = array(
                    "1"   => "活动成本填充",
                    "2"   => "电商成本填充",
    );
    
    //成本填充类型
    private $_conf_cost_sup_type = array(
                    "active_cost" => 1,
                    "ds_cost"     => 2,
    );
    

    //构造函数
    public function __construct($name = '') {
        parent::__construct($name);
    }
    
    /**
     * 获取状态标识
     * @return 
     */
    public function get_cost_supplement_status_remark(){
        return $this->_conf_cost_supplement_status_remark;
    }
    
    /**
     * 获取状态
     * @return 
     */
    public function get_cost_supplement_status(){
        return $this->_conf_cost_supplement_status;
    }

    public function get_cost_sup_type(){
        return $this->_conf_cost_sup_type;
    }
    
     public function get_cost_sup_type_remark(){
        return $this->_conf_cost_sup_type_remark;
    }

    /**
     *新增成本填充信息 
     * @param $data array() 字段键值对
     * return int $insertid 成功：新增的id \失败：false
     */
    public function add_cost_supplement_info($data)
    {
        $insertid = false;
        if(is_array($data) && !empty($data))
        {
            $insertid = $this->add($data);
        }
        return $insertid ? $insertid : false;
    }
    
    /**
     * 根据ID更新信息
     * @param mixed $ids 单个Id或数组
     * @update_arr array() 要更新的字段
     * return $up_num 成功：影响的记录数 \ 失败：false
     */
    public function update_cost_supplement_info_by_ids($ids,$update_arr)
    {
        $up_num = "";
        $cond_where = "";
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }
        
        if(is_array($update_arr) && !empty($update_arr))
        {
            $up_num = self::update_cost_supplement_info_by_cond($cond_where,$update_arr);
        }
        return $up_num ? $up_num : false;
    }
    
    /**
     * 根据条件更新信息
     * @param array() $cond_where 条件
     * @update_arr array() 要更新的字段
     * return $up_num 成功：影响的记录数 \ 失败：false
     */
    public function update_cost_supplement_info_by_cond($cond_where,$update_arr)
    {
        $up_num = "";
        if($cond_where && is_array($update_arr) && !empty($update_arr))
        {
            $up_num = $this->where($cond_where)->save($update_arr);
        }
        return $up_num ? $up_num : false;
    }
    
    /**
     * 根据ID删除信息
     * @param mixed $ids 
     * return $del_num 成功：影响的行数 \ 失败：false
     */
    public function del_cost_supplement_info_by_ids($ids)
    {
        $del_num = "";
        $cond_where = "";
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }

        $del_num = self::del_cost_supplement_info_by_cond($cond_where);

        return $del_num ? $del_num : false;
    }
    
    public function del_cost_supplement_info_by_cond($cond_where)
    {
        $del_num = "";
        if($cond_where)
        {
            $del_num = $this->where($cond_where)->delete();
        }
        return $del_num ? $del_num : false;
    }
    
    /**
     * 根据ID获取成本填充信息
     * @param mixed $ids 单个ID或数组
     * @param array $search_arr() 要查询的数组
     * return $info 成功 ：数组 \ 失败：false
     */
    public function get_cost_supplement_info_by_ids($ids,$search_arr)
    {
        $info = array();
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }
        $info = self::get_cost_supplement_info_by_cond($cond_where,$search_arr);
        
        return $info ? $info : false;
    }
    
    /**
     * 根据条件查询信息
     * @param string $cond_where 条件
     * @param array $search_arr 查询字段
     * return array $info 成功： 查询到的数据数组 \失败：false
     */
    public function get_cost_supplement_info_by_cond($cond_where = "",$search_arr)
    {
        $info = array();
        if($cond_where)
        {
            if(is_array($search_arr) && !empty($search_arr))
            {
                $info = $this->where($cond_where)->field($search_arr)->select();
            }
            else
            {
                $info = $this->where($cond_where)->select();
            }
        }
        
        return $info ? $info : false;
    }
    
}

