<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FeescaleChangeModel extends Model{
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'FEESCALE_CHANGE';
      /***标准修改单状态***/
    private  $_conf_requisition_status = array(
                                    'not_sub' => 1,  //未提交
                                    'submitted' => 2,  //流程审核中
                                    'approved' => 3,  //审核通过
                                    'not_agree' => 4,  //审核未通过 
    							);
     /***标准修改单状态描述***/
    private  $_conf_requisition_status_remark = array(
                                    '1' => '未提交',
                                    '2' => '流程审核中',
                                    '3' => '审核通过',
                                    '4' => '审核未通过',
                                    
    							);
    
    
      //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
       /**
     * 获取标准修改单状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_requisition_status()
    {
    	return $this->_conf_requisition_status;
    }
    
    
    /**
     * 获取标准修改单状态描述数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_requisition_status_remark()
    {
    	return $this->_conf_requisition_status_remark;
    }
        
    /**
     * 新增标准调整申请
     * @param  array $data  新增数据键值对
     *  return  int   $insertid 自增主键
     */
    public function add_standard_adjustment($data)
    {
        $insertid = 0;
        if(is_array($data) && !empty($data))
        {
            $table["option"] = $this->tablePrefix.$this->tableName;
            $insertid = $this->table($table["option"])->add($data);
        }
        return $insertid;
    }
    
    
    /**
     * 根据标准调整申请单ID，提交标准调整申请
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function submit_standardadjust_by_id($ids)
    {	
    	$up_num = 0;    	
    	$update_arr = array();
    	$status = intval($this->_conf_requisition_status['submitted']);
    	$update_arr['STATUS'] = $status;
    	$up_num = $this->update_standardadjust_by_id($ids, $update_arr);
    	
    	return $up_num > 0 ? $up_num :FALSE;
    }
    
    /**
     * 根据标准调整申请单更新标准调整申请单内容
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_standardadjust_by_id($ids, $update_arr)
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
    
    	$up_num = self::update_standardadjust_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据标准调整条件更新标准调整申请单
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_standardadjust_by_cond($update_arr, $cond_where)
    {
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{
    		$up_num = $this->where($cond_where)->save($update_arr);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
     /**
     * 根据标准调整ID 
     *
     * @access	public
     * @param	int  $id  id
     * @param	array  查询字段
     * @return	$info
     */
    public function get_info_by_ids($id,$field = "*"){
        $where = "ID = $id";
        $info = $this->field($field)->where($where)->find();
        if($info){
            return $info;
        }else{
            return false;
        }
       
    }
    
    /**
     * 根据id删除申请单
     * @param $ids mixed id
     * return $del_num 成功\删除的行数、失败\false
     * 
     */
    public function del_feescale_change_by_id($ids)
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
        $del_num = self::del_feescale_change_by_cond($cond_where);
        return $del_num ? $del_num : false;
    }
    
    /**
     * 根据条件删除申请单
     * @param $cond_where  string 条件 
     * return $del_num 影响的行数
     */
    public function del_feescale_change_by_cond($cond_where)
    {
        //$table["option"] = $this->tablePrefix.$this->tableName;
        $del_num = false;
        $del_num = $this->where($cond_where)->delete();
        return $del_num ? $del_num : false;
    }
}
