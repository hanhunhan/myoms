<?php

/**
 * 会员付款MODEL类
 *
 * @author liuhu
 */
class MemberPayModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'MEMBER_PAYMENT';
    
    /*付款方式*/
    private  $_conf_pay_type = array(
                                1 => 'POS机', 
                                2 => '网银', 
                                3 => '现金',
                                4 => '综合'
                                );
    
    /***付款明细财务状态拼音***/
    private  $_conf_status = array(
                        'wait_confirm' => '0',		//等待确认
                        'confirmed' => '1', 		//已确认
                        'confirm_failure' => '2',	//确认失败
                        'deleted' => '4'            //删除
    					);
    
    /***付款明细状态***/
    private  $_conf_status_remark = array(
                        '0' => '未确认',		//等待确认
                        '1' => '已确认', 	//已确认
                        '2' => '确认失败'	//确认失败
    					);
    
    /***付款明细退款状态***/
    private  $_conf_refund_status = array(
                                        'no_refund' => 0,		//未申请退款
                                        'apply_refund' => 1,	//申请退款中
                                        );
    
    /***付款明细退款状态***/
    private  $_conf_refund_status_remark = array(
                                                0 => '未申请',
                                                1 => '申请中',
                                                );
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * 获取付款方式数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_pay_type()
    {
    	return $this->_conf_pay_type;
    }
    
    /**
     * 获取付款明细状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_status()
    {
    	return $this->_conf_status;
    }
    
    /**
     * 获取付款明细状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_refund_status_remark()
    {
    	return $this->_conf_refund_status_remark;
    }
    
    /**
     * 获取付款明细退款状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_refund_status()
    {
    	return $this->_conf_refund_status;
    }
    
    /**
     * 获取付款明细退款状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_status_remark()
    {
    	return $this->_conf_status_remark;
    }
    
    /**
     * 添加支付明细信息
     * @param array $pay_info 支付明细数组
     * @return mixed 添加成功返回插入数据编号，插入失败返回FALSE
     */
    public function add_member_info($pay_info) 
    {   
        if(is_array($pay_info) && !empty($pay_info))
        {   
            // 自增主键返回插入ID
            $insertId = $this->add($pay_info);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据付款明细编号删除付款明细信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function del_pay_detail_by_id($id)
    {   
        $up_num = 0;
        $id = intval($id);
        
        if($id > 0)
        {
            $cond_where = "ID = '".$id."'";
            $up_num = self::del_pay_detail_by_cond($cond_where);
        }
        
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据会员编号删除付款明细信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function del_pay_detail_by_mid($mid)
    {   
        $up_num = 0;
        $mid = intval($mid);
        
        if($mid > 0)
        {   
            $cond_where = "MID = '".$mid."' AND STATUS != '".$this->_conf_status['confirmed']."'";
            $up_num = self::del_pay_detail_by_cond($cond_where);
        }
        
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据条件删除报销明细信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function del_pay_detail_by_cond($cond_where)
    {   
        $up_num = 0;
        
    	if($cond_where != '')
    	{   
            $update_arr['STATUS'] = $this->_conf_status['deleted'];
    		$up_num = $this->where($cond_where)->save($update_arr);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据编号更新付款明细信息
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_info_by_id($ids, $update_arr)
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
    
    	$up_num = self::update_info_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
  	
    /**
     * 根据条件更新某条付款明细信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_info_by_cond($update_arr, $cond_where)
    {
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{
    		$up_num = $this->where($cond_where)->save($update_arr);
    		//echo $this->getLastSql();
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据付款编号，获取付款明细信息
     *
     * @access	public
     * @param	mixed  $pay_id 付款明细编号【数组或者单个付款明细编号】
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_payinfo_by_id($pay_id, $search_field = array())
    {
        $info = array();
        $cond_where = "";
        if(is_array($pay_id) && !empty($pay_id))
        {
            $pay_id_str = implode(',', $pay_id);
            $cond_where = "ID IN (".$pay_id_str.")";
        }
        else 
        {   
            $pay_id = intval($pay_id);
            $cond_where = "ID = '".$pay_id."'";
        }
        
        if($cond_where == '')
        {
            return $info;
        }
        
        $info = self::get_payinfo_by_cond($cond_where, $search_field);
        
        return $info;
    }
    
    
    /**
     * 根据会员编号，获取付款明细信息
     *
     * @access	public
     * @param	mixed  $mid 会员编号[数组或者单个会员编号]
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_payinfo_by_mid($mid, $search_field = array())
    {
        $info = array();
        $cond_where = "";
        
        if(is_array($mid) && !empty($mid))
        {
            $mid_str = implode(',', $mid);
            $cond_where = "MID IN (".$mid_str.")";
        }
        else 
        {   
            $mid = intval($mid);
            $cond_where = "MID = '".$mid."'";
        }
        
        if($cond_where == '')
        {
            return $info;
        }
        
        $info = self::get_payinfo_by_cond($cond_where, $search_field);
        
        return $info;
    }
    
    
    /**
     * 根据条件获取付款明细信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_payinfo_by_cond($cond_where, $search_field = array())
    {
        $info = array();
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $info;
        }
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $info = $this->where($cond_where)->select();
        }
        //echo $this->getLastSql();
        return $info;
    }
    
    
    /**
     * 根据会员编号获取付款总和
     *
     * @access	public
     * @param	int  $mid 会员编号
     * @param 	string $conf_status 付款状态字符串（wait_confirm）
     * @return	array 查询结果
     */
    public function get_sum_pay($mid, $status = '')
    {   
    	$trade_money = 0;
    	$mid = intval($mid);
        
    	if($mid <= 0)
    	{
            return $trade_money;
    	}
    	
    	//查询条件
    	$cond_where = "MID = '".$mid."'";
    	
    	$conf_status = $this->get_conf_status();
    	if(!empty($status) && !empty($conf_status) &&
    			 array_key_exists($status, $conf_status) )
    	{
            $cond_where .= " AND STATUS = '".$conf_status[$status]."'";
    	}
        else
        {
            $cond_where .= " AND STATUS IN (".$conf_status['wait_confirm'].",".$conf_status['confirmed'].")";
        }
        
    	$trade_money = $this->where($cond_where)->sum('TRADE_MONEY - REFUND_MONEY');
        
    	return floatval($trade_money);
    }
}

/* End of file MemberPayModel.class.php */
/* Location: ./Lib/Model/MemberPayModel.class.php */