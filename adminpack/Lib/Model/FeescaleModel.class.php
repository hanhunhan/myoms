<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FeescaleModel extends Model{
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'FEESCALE';
    
    /**标准明细状态*/
    private $_conf_feescale_status = array(
                            'not_sub' => 1,  //未提交
                            'submitted' => 2,  //流程审核中
                            'approved' => 3,  //审核通过
                            'not_agree' => 4,  //审核未通过
    );
    
    /**标准明细状态描述*/
    private  $_conf_feescale_status_remark = array(
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
    public function get_conf_feescale_status()
    {
    	return $this->_conf_feescale_status;
    }
    
    
    /**
     * 获取标准修改明细状态数组描述
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_feescale_status_remark()
    {
    	return $this->_conf_feescale_status_remark;
    }
    /*
     * 新增标准明细
     */
    public function add_feescale_info($data)
    {
        if(is_array($data) && !empty($data))
        {
            $insertid = $this->add($data);
        }
        
        return $insertid ? $insertid : false;
    }
    
    /**
     * 根据调整单ID更新明细状态
     * @param mixed $ch_ids 调整单id
     * return $up_num 
     */
    public function update_info_by_ch_id($ch_ids,$update_arr)
    {
        if(is_array($ch_ids) && !empty($ch_ids))
        {
            $ch_id_str = implode(",", $ch_ids);
            $cond_where = "CH_ID IN($ch_id_str)";
        }
        else
        {
             $cond_where = "CH_ID = ".$ch_ids;
        }
        
        if(is_array($update_arr) && !empty($update_arr))
        {
            $up_num = self::update_info_by_cond($cond_where,$update_arr);
        }
        
        return $up_num ? $up_num : false;
    }
    
    /**
     * 根据条件更新
     * @param str $name Description
     * return $up_num \false
     */
    
    public function update_info_by_cond($cond_where,$update_arr)
    {
        if($cond_where && $update_arr)
        {
            $up_num = $this->where($cond_where)->save($update_arr);
        }
        return $up_num ? $up_num : false;
    }
    
    /**
     * 根据id删除标准明细
     * @param $ids mixed id
     * return $del_num 成功\删除的行数、失败\false
     * 
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
     * 根据条件删除标准明细
     * @param $cond_where  string 条件 
     * return $del_num 影响的行数
     */
    public function del_info_by_cond($cond_where)
    {
        $del_num = false;
        $del_num = $this->where($cond_where)->delete();
        return $del_num ? $del_num : false;
    }
    
    /**
     * 根据调整单ID获取信息
     * @param mixed $ch_ids 调整单id
     * return $up_num 
     */
    public function get_info_by_ch_id($ch_ids,$search_arr)
    {
        if(is_array($ch_ids) && !empty($ch_ids))
        {
            $ch_id_str = implode(",", $ch_ids);
            $cond_where = "CH_ID IN($ch_id_str)";
        }
        else
        {
             $cond_where = "CH_ID = ".$ch_ids;
        }
        $info = self::get_info_by_cond($cond_where,$search_arr);

        return $info ? $info : false;
    }
    
    /**
     * 根据调条件获取信息
     * @param mixed $ch_ids 调整单id
     * return $up_num 
     */
    public function get_info_by_cond($cond_where,$search_arr)
    {
        if(is_array($search_arr) && !empty($search_arr))
        {
            $info = $this->where($cond_where)->field($search_arr)->select();
        }
        else
        {
            $info = $this->where($cond_where)->select();
        }
        return $info ? $info : false;
    }
    
    
}

