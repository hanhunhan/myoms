<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * 会员退票model
 */
class InvoiceRecycleModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    private $table_list = 'INVOICE_RECYCLE_LIST';
    private $table_detail = 'INVOICE_RECYCLE_DETAIL';
    
    
    /***退票单状态***/
    private  $_conf_invoice_recycle_list_status = array(
    		'invoice_recycle_list_no_sub' => 1,	//未提交
    		'invoice_recycle_list_sub' => 2,		//已提交
    		'invoice_recycle_list_stop' => 3,     //否决退票
    		'invoice_recycle_list_completed' => 4, //退票完成
    );
    
     /***退票单状态描述***/
    private $_conf_invoice_recycle_list_status_remark = array(
                                            1 => '未提交审核',
                                            2 => '已提交审核',
                                            3 => '否决退票',
                                            4 => '退票完成'
                                    );
    
    
    /***退票明细退票状态***/
    private  $_conf_invoice_recycle_detail_status = array(
                                    'invoice_recycle_no_sub' => 1,	//未提交
                                    'invoice_recycle_audit' => 2,	//加入审核单
                                    'invoice_recycle_apply' => 3,	//提交审核中
                                    'invoice_recycle_stop' => 4,     //终止退票
                                    'invoice_recycle_success' => 5,	//成功退票
                                    'invoice_recycle_delete' => 6,     //删除退票
                                    'invoice_recycle_received' =>7    //退票到账
                                    
    							);
    
    /***退票明细退票状态***/
    private  $_conf_invoice_recycle_detail_status_remark = array(
                                            1 => '未提交',
                                            2 => '加入审核单',
                                            3 => '提交审核中',
                                            4 => '终止退票',
                                            5 => '成功退票',
                                            6 => '删除退票',
                                            7 => '退票到账',
                                            
                                        );
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    /**
     * 获取退票单状态描述数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_invoice_recycle_list_status_remark()
    {
    	return $this->_conf_invoice_recycle_list_status_remark;
    }
    
    /**
     * 获取退票单状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_invoice_recycle_list_status()
    {
    	return $this->_conf_invoice_recycle_list_status;
    }
    
    
    /**
     * 获取退票明细状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_invoice_recycle_status()
    {
    	return $this->_conf_invoice_recycle_detail_status;
    }
    
    
    /**
     * 获取退票明细状态数组备注
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_invoice_recycle_status_remark()
    {
    	return $this->_conf_invoice_recycle_detail_status_remark;
    }
    
    
    //获取退票单表名
    public function get_list_table_name()
    {   
        return $this->tablePrefix.$this->table_list;
    }
    
    
    //获取退票明细表名
    public function get_detail_table_name()
    {   
        return $this->tablePrefix.$this->table_detail;
    }
    
	
    /**
     * 添加退票单信息
     *
     * @access	private
     * @param	array  $invoice_recycle_arr 退票单添加
     * @return	mixed  成功返回退票单编号，失败返回FALSE
     */
    public function add_invoice_recycle_list($invoice_recycle_arr)
    {   
        $insertId = 0;
        if(is_array($invoice_recycle_arr) && !empty($invoice_recycle_arr))
        {   
            // 自增主键返回插入ID
            $options['table'] = $this->get_list_table_name();
            $insertId = $this->add($invoice_recycle_arr, $options);
        }
        
        return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 添加退票明细
     *
     * @access	private
     * @param	array  $invoice_recycle_arr 退票信息
     * @return	mixed  成功返回退票单编号，失败返回FALSE
     */
    public function add_invoice_recycle_details($invoice_recycle_arr)
    {
        $insertId = 0;
        
        if(is_array($invoice_recycle_arr) && !empty($invoice_recycle_arr))
        {   
            // 自增主键返回插入ID
            $options['table'] = $this->get_detail_table_name();
            $insertId = $this->add($invoice_recycle_arr, $options);
        }
        
        return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据退票明细ID删除退票明细
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function del_invoice_recycle_detail_by_id($ids)
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

    	$up_num = self::del_invoice_recycle_detail_by_cond($cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据条件删除退票明细信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function del_invoice_recycle_detail_by_cond($cond_where)
    {
        $up_num = 0;
        
    	if($cond_where != '')
    	{	
            $update_arr = array();
            $update_arr['STATUS'] = intval($this->_conf_invoice_recycle_detail_status['invoice_recycle_delete']);
    		$up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 添加退票明细记录到退票审核单
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function add_details_to_audit_list($ids, $list_id)
    {
    	$cond_where = "";
        $list_id = intval($list_id);
        
        if($list_id > 0 && !empty($ids))
        {
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
            
            $no_sub_status  = $this->_conf_invoice_recycle_detail_status['invoice_recycle_no_sub'];
            $cond_where .= " AND STATUS = '".$no_sub_status."'";
            
            $update_arr['LIST_ID'] =  $list_id;
            $update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
            $update_arr['STATUS'] = $this->_conf_invoice_recycle_detail_status['invoice_recycle_audit'];
            $up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
        }
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
        
    /**
     * 根据退票申请单编号提交退票申请到工作流审核
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_invoice_recycle_detail_to_apply($list_id)
    {
    	$list_id = intval($list_id);
    
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$audit_status  = $this->_conf_invoice_recycle_detail_status['invoice_recycle_audit'];
    		$cond_where .= " AND STATUS = '".$audit_status."'";
    		
    		$update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_invoice_recycle_detail_status['invoice_recycle_apply'];
            
    		$up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 更改退票申请单到终止状态
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_invoice_recycle_detail_to_stop($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$audit_status  = $this->_conf_invoice_recycle_detail_status['invoice_recycle_audit'];
    		$cond_where .= " AND STATUS = '".$audit_status."'";
    		
    		//$update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_invoice_recycle_detail_status['invoice_recycle_stop'];
            
    		$up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 更改退票明细单到完成状态
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_invoice_recycle_detail_to_success($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$apply_status  = $this->_conf_invoice_recycle_detail_status['invoice_recycle_apply'];
    		$cond_where .= " AND STATUS = '".$apply_status."'";
            
            $update_arr['CONFIRMTIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_invoice_recycle_detail_status['invoice_recycle_success'];
            
    		$up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    
    /**
     * 删除退票明细与退票申请单之间关系（退出退票申请单）
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function delete_details_from_audit_list($ids)
    {
    	$cond_where = "";
        
        if(!empty($ids))
        {
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
            
            $no_sub_status  = $this->_conf_invoice_recycle_detail_status['invoice_recycle_no_sub'];
            $update_arr['LIST_ID'] =  '';
            //$update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
            $update_arr['STATUS'] = $no_sub_status;
            $up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
        }
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据退票明细ID更新退票明细信息
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_invoice_recycle_detail_by_id($ids, $update_arr)
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

    	$up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据指定条件更新退票明细信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_invoice_recycle_detail_by_cond($update_arr, $cond_where)
    {	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$options['table'] = $this->get_detail_table_name();
    		$up_num = $this->where($cond_where)->save($update_arr, $options);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据指定退票申请单ID提交退票申请单到工作流审核状态
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_invoice_recycle_list_to_apply($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$no_sub_status  = $this->_conf_invoice_recycle_list_status['invoice_recycle_list_no_sub'];
    		$cond_where .= " AND STATUS = '".$no_sub_status."'";
    		
    		$update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_invoice_recycle_list_status['invoice_recycle_list_sub'];
    		$up_num = self::update_invoice_recycle_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 更改退票申请单到完成状态
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_invoice_recycle_list_to_completed($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$sub_status  = $this->_conf_invoice_recycle_list_status['invoice_recycle_list_sub'];
    		$cond_where .= " AND STATUS = '".$sub_status."'";
    		
    		//$update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_invoice_recycle_list_status['invoice_recycle_list_completed'];
    		$up_num = self::update_invoice_recycle_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 更改退票申请单到终止状态
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_invoice_recycle_list_to_stop($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$sub_status  = $this->_conf_invoice_recycle_list_status['invoice_recycle_list_sub'];
    		$cond_where .= " AND STATUS = ".$sub_status;
    		
    		//$update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_invoice_recycle_list_status['invoice_recycle_list_stop'];
    		$up_num = self::update_invoice_recycle_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据指定ID更新退票申请单信息
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_invoice_recycle_list_by_id($ids, $update_arr)
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

    	$up_num = self::update_invoice_recycle_list_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据指定条件更新退票申请单信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_invoice_recycle_list_by_cond($update_arr, $cond_where)
    {	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$options['table'] = self::get_list_table_name();
    		$up_num = M("Erp_invoice_recycle_list")->where($cond_where)->save($update_arr);
//            echo M("Erp_invoice_recycle_list")->getLastSql();
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据编号获取退票单信息
     *
     * @access	public
     * @param	int  $list_id 退票单号
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_invoice_recycle_list_by_id($list_id, $search_field = array())
    {
        $info = array();
        
        $list_id = intval($list_id);
        
        if($list_id <= 0)
        {
            return $info;
        }
        
        //查询条件
        $cond_where = " ID = '".$list_id."'";
        
        //查询表名
        $list_table_name = self::get_list_table_name();
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->table($list_table_name)->field($search_str)->where($cond_where)->find();
        }
        else
        {
            $info = $this->table($list_table_name)->where($cond_where)->find();
        }
        
        return $info;
    }
    
    
    /**
     * 根据条件获取退票单信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_invoice_recycle_list_by_cond($cond_where, $search_field = array())
    {
        $info = array();
        
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $info;
        }
        
        $list_table_name = self::get_list_table_name();
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->table($list_table_name)->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $info = $this->table($list_table_name)->where($cond_where)->select();
        }
        
        return $info;
    }
    
    
    /**
     * 获取最新一条退票单记录
     *
     * @access	public
     * @param int $add_uid 申请用户
     * @param int $city_id 城市编号
     * @param int $status 状态
     * @return	array 查询结果
     */
    public function get_last_invoice_recycle_list($add_uid, $city_id, $status = 1)
    {
        $info = array();
        
        $add_uid = intval($add_uid);
        $status = intval($status);
        $city_id = intval($city_id);
        
        if($add_uid <= 0)
        {
            return $info;
        }
        
        $list_table_name = self::get_list_table_name();
        $cond_where = "APPLY_USER = '".$add_uid."' AND STATUS = '".$status."' AND CITY_ID = '".$city_id."' ";
        $info = $this->table($list_table_name)->where($cond_where)->order('ID DESC')->find();
        //echo $this->getLastSql();
        return $info;
    }
    
    /**
     * 根据退票单编号获取退票明细信息
     *
     * @access	public
     * @param	int  $list_id 退票单号
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_invoice_recycle_detail_by_listid($list_id, $search_field = array())
    {
        $info = array();
        
        $list_id = intval($list_id);
        
        if($list_id <= 0)
        {
            return $info;
        }
        
        //查询条件
        $cond_where = " LIST_ID = '".$list_id."'";
        $info = $this->get_invoice_recycle_detail_info_by_cond($cond_where, $search_field );
        
        return $info;
    }
    
    
    /**
     * 根据编号获取退票明细信息
     *
     * @access	public
     * @param	int  $id 退票单号
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_invoice_recycle_detail_info_by_id($id, $search_field = array())
    {
        $info = array();
        
        $id = intval($id);
        
        if($id <= 0)
        {
            return $info;
        }
        
        //查询条件
        $cond_where = " ID = '".$id."'";
        
        //查询表名
        $detail_table_name = self::get_detail_table_name();
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->table($detail_table_name)->field($search_str)->where($cond_where)->find();
        }
        else
        {
            $info = $this->table($detail_table_name)->where($cond_where)->find();
        }
        
        return $info;
    }
    
    
    /**
     * 根据条件获取退票明细信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_invoice_recycle_detail_info_by_cond($cond_where, $search_field = array())
    {
        $info = array();
        
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $info;
        }
        
        $detail_table_name = self::get_detail_table_name();
        if(is_array($search_field) && !empty($search_field) )
        {
            $search_str = implode(',', $search_field);
            $info = $this->table($detail_table_name)->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $info = $this->table($detail_table_name)->where($cond_where)->select();
        }

        return $info;
    }
    
}

