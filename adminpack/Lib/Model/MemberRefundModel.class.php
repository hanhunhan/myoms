<?php
/**
 * 电商业务办卡客户退款类
 *
 * @author liuhu
 */

class MemberRefundModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    private $table_list = 'MEMBER_REFUND_LIST';
    private $table_detail = 'MEMBER_REFUND_DETAIL';
    
    
    /***退款单状态***/
    private  $_conf_refund_list_status = array(
                                        'refund_list_no_sub' => 0,	//未提交
                                        'refund_list_sub' => 1,		//已提交
                                        'refund_list_stop' => 2,     //否决退款
                                        'refund_list_completed' => 3, //退款完成
                                    );
    
    /***退款单状态描述***/
    private $_conf_refund_list_status_remark = array(
                                            0 => '未提交审核',
                                            1 => '已提交审核',
                                            2 => '否决退款',
                                            3 => '退款完成'
                                    );
    
    
    /***退款明细退款状态***/
    private  $_conf_refund_status = array(
                                    'refund_no_sub' => 0,	//未提交
                                    'refund_audit' => 1,	//加入审核单
                                    'refund_apply' => 2,	//提交审核中
                                    'refund_stop' => 3,     //终止退款
                                    'refund_success' => 4,	//成功退款
                                    'refund_delete' => 5,     //删除退款
                                    'refund_received' => 6    //退款到账
    							);
    
    /***退款明细退款状态***/
    private  $_conf_refund_status_remark = array(
                                            0 => '未提交',
                                            1 => '加入审核单',
                                            2 => '提交审核中',
                                            3 => '终止退款',
                                            4 => '成功退款',
                                            5 => '删除退款',
                                            /*6 => '退款到账',*/
                                        );
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
         
    /**
     * 获取退款单状态描述数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_refund_list_status_remark()
    {
    	return $this->_conf_refund_list_status_remark;
    }
    
    
    /**
     * 获取退款单状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_refund_list_status()
    {
    	return $this->_conf_refund_list_status;
    }
    
    
    /**
     * 获取退款明细状态数组
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
     * 获取退款明细状态数组备注
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_refund_status_remark()
    {
    	return $this->_conf_refund_status_remark;
    }
    
    
    //获取退款单表名
    public function get_list_table_name()
    {   
        return $this->tablePrefix.$this->table_list;
    }
    
    
    //获取退款明细表名
    public function get_detail_table_name()
    {   
        return $this->tablePrefix.$this->table_detail;
    }
    
	
    /**
     * 添加退款单信息
     *
     * @access	private
     * @param	array  $refund_arr 退款单添加
     * @return	mixed  成功返回退款单编号，失败返回FALSE
     */
    public function add_refund_list($refund_arr)
    {   
        $insertId = 0;
        if(is_array($refund_arr) && !empty($refund_arr))
        {   
            // 自增主键返回插入ID
            $options['table'] = $this->get_list_table_name();
            $insertId = $this->add($refund_arr, $options);
        }
        
        return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 添加退款明细
     *
     * @access	private
     * @param	array  $refund_arr 退款信息
     * @return	mixed  成功返回退款单编号，失败返回FALSE
     */
    public function add_refund_details($refund_arr)
    {
        $insertId = 0;
        
        if(is_array($refund_arr) && !empty($refund_arr))
        {   
            // 自增主键返回插入ID
            $options['table'] = $this->get_detail_table_name();
            $insertId = $this->add($refund_arr, $options);
        }
        
        return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据退款明细ID删除退款明细
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function del_refund_detail_by_id($ids)
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

    	$up_num = self::del_refund_detail_by_cond($cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据条件删除退款明细信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function del_refund_detail_by_cond($cond_where)
    {
        $up_num = 0;
        
    	if($cond_where != '')
    	{	
            $update_arr = array();
            $update_arr['REFUND_STATUS'] = intval($this->_conf_refund_status['refund_delete']);
    		$up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 添加退款明细记录到退款审核单
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
            
            $no_sub_status  = $this->_conf_refund_status['refund_no_sub'];
            $cond_where .= " AND REFUND_STATUS = '".$no_sub_status."'";
            
            $update_arr['LIST_ID'] =  $list_id;
            $update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
            $update_arr['REFUND_STATUS'] = $this->_conf_refund_status['refund_audit'];
            $up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
        }
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据退款申请单编号提交退款申请到工作流审核
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_refund_detail_to_apply($list_id)
    {
    	$list_id = intval($list_id);
    
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$audit_status  = $this->_conf_refund_status['refund_audit'];
    		$cond_where .= " AND REFUND_STATUS = '".$audit_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['REFUND_STATUS'] = $this->_conf_refund_status['refund_apply'];
            
    		$up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 更改退款申请单到终止状态
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_refund_detail_to_stop($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$apply_status  = $this->_conf_refund_status['refund_apply'];
    		$cond_where .= " AND REFUND_STATUS = '".$apply_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['REFUND_STATUS'] = $this->_conf_refund_status['refund_stop'];
            
    		$up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 更改退款申请单到完成状态
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_refund_detail_to_success($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$apply_status  = $this->_conf_refund_status['refund_apply'];
    		$cond_where .= " AND REFUND_STATUS = '".$apply_status."'";
            
            $update_arr['CONFIRMTIME'] =  date('Y-m-d H:i:s');
    		$update_arr['REFUND_STATUS'] = $this->_conf_refund_status['refund_success'];
            
    		$up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 删除退款明细与退款申请单之间关系（退出退款申请单）
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
            
            $no_sub_status  = $this->_conf_refund_status['refund_no_sub'];
            $update_arr['LIST_ID'] =  '';
            $update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
            $update_arr['REFUND_STATUS'] = $no_sub_status;
            $up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
        }
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据退款明细ID更新退款明细信息
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_refund_detail_by_id($ids, $update_arr)
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

    	$up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据指定条件更新退款明细信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_refund_detail_by_cond($update_arr, $cond_where)
    {	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$options['table'] = $this->get_detail_table_name();
    		$up_num = $this->where($cond_where)->save($update_arr, $options);
    		//echo $this->getLastSql();
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据指定退款申请单ID提交退款申请单到工作流审核状态
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_refund_list_to_apply($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$no_sub_status  = $this->_conf_refund_list_status['refund_list_no_sub'];
    		$cond_where .= " AND STATUS = '".$no_sub_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_refund_list_status['refund_list_sub'];
    		$up_num = self::update_refund_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 更改退款申请单到完成状态
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_refund_list_to_completed($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$sub_status  = $this->_conf_refund_list_status['refund_list_sub'];
    		$cond_where .= " AND STATUS = '".$sub_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_refund_list_status['refund_list_completed'];
    		$up_num = self::update_refund_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 更改退款申请单到终止状态
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_refund_list_to_stop($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$sub_status  = $this->_conf_refund_list_status['refund_list_sub'];
    		$cond_where .= " AND STATUS = '".$sub_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_refund_list_status['refund_list_stop'];
            
    		$up_num = self::update_refund_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据指定ID更新退款申请单信息
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_refund_list_by_id($ids, $update_arr)
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

    	$up_num = self::update_refund_list_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据指定条件更新退款申请单信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_refund_list_by_cond($update_arr, $cond_where)
    {	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$options['table'] = self::get_list_table_name();
    		$up_num = $this->where($cond_where)->save($update_arr, $options);
    		//echo $this->getLastSql();
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据编号获取退款单信息
     *
     * @access	public
     * @param	int  $list_id 退款单号
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_refund_list_by_id($list_id, $search_field = array())
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
     * 根据条件获取退款单信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_refund_list_by_cond($cond_where, $search_field = array())
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
     * 获取最新一条退款单记录
     *
     * @access	public
     * @param int $add_uid 申请用户
     * @param int $city_id 城市编号
     * @param int $status 状态
     * @return	array 查询结果
     */
    public function get_last_refund_list($add_uid, $city_id, $status = 0)
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
        $cond_where = "ADD_UID = '".$add_uid."' AND STATUS = '".$status."' AND CITY_ID = '".$city_id."' ";
        $info = $this->table($list_table_name)->where($cond_where)->order('ID DESC')->find();
        //echo $this->getLastSql();
        return $info;
    }
    
    
    /**
     * 根据编号获取退款明细信息
     *
     * @access	public
     * @param	int  $list_id 退款单号
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_refund_detail_by_listid($list_id, $search_field = array())
    {
        $info = array();
        
        $list_id = intval($list_id);
        
        if($list_id <= 0)
        {
            return $info;
        }
        
        //查询条件
        $cond_where = " LIST_ID = '".$list_id."'";
        $info = $this->get_refund_detail_by_cond($cond_where, $search_field );
        
        return $info;
    }
    
    
    /**
     * 根据编号获取退款明细信息
     *
     * @access	public
     * @param	int  $id 退款单号
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_refund_detail_by_id($id, $search_field = array())
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
     * 根据条件获取退款明细信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_refund_detail_by_cond($cond_where, $search_field = array())
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

/* End of file MemberRefundModel.class.php */
/* Location: ./Lib/Model/MemberRefundModel.class.php */