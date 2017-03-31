<?php
/**
 * 电商业务办卡客户减免类
 *
 * @author liuhu
 */
class MemberDiscountModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    private $table_list = 'MEMBER_DISCOUNT_LIST';
    private $table_detail = 'MEMBER_DISCOUNT_DETAIL';
    
    
    /***减免单状态***/
    private  $_conf_discount_list_status = array(
    		'discount_list_no_sub' => 1,	//未提交
    		'discount_list_sub' => 2,		//已提交
    		'discount_list_stop' => 3,     //否决减免
    		'discount_list_completed' => 4, //减免完成
    );
    
     /***减免单状态描述***/
    private $_conf_discount_list_status_remark = array(
                                            1 => '未提交审核',
                                            2 => '已提交审核',
                                            3 => '减免同意',
                                            4 => '否决减免'
                                    );
    
    
    /***减免明细减免状态***/
    private  $_conf_discount_status = array(
                                    'discount_no_sub' => 1,	//未提交
                                    'discount_audit' => 2,	//加入审核单
                                    'discount_apply' => 3,	//提交审核中
                                    'discount_stop' => 4,     //终止减免
                                    'discount_success' => 5,	//成功减免
                                    'discount_delete' => 6,     //删除减免
                                    'discount_received' => 7    //减免到账
    							);
    
    /***减免明细状态***/
    private  $_conf_discount_status_remark = array(
                                            1 => '未提交',
                                            2 => '加入审核单',
                                            3 => '提交审核中',
                                            4 => '终止减免',
                                            5 => '减免通过',
                                            6 => '删除减免',
                                            7 => '减免到账',
                                        );
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
            
    /**
     * 获取减免单状态描述数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_discount_list_status_remark()
    {
    	return $this->_conf_discount_list_status_remark;
    }
    
    /**
     * 获取减免单状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_discount_list_status()
    {
    	return $this->_conf_discount_list_status;
    }
    
    
    /**
     * 获取减免明细状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_discount_detail_status()
    {
    	return $this->_conf_discount_status;
    }
    
    
    /**
     * 获取减免明细状态数组备注
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_discount_detail_status_remark()
    {
    	return $this->_conf_discount_status_remark;
    }
    
    
    
	
    /**
     * 添加减免单信息
     *
     * @access	private
     * @param	array  $discount_arr 减免单添加
     * @return	mixed  成功返回减免单编号，失败返回FALSE
     */
    public function add_discount_list($discount_arr)
    {   
        $insertId = 0;
        if(is_array($discount_arr) && !empty($discount_arr))
        {   
            // 自增主键返回插入ID
            $options['table'] = $this->tablePrefix.$this->table_list;
            $insertId = $this->add($discount_arr, $options);
        }
        
        return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 添加减免明细
     *
     * @access	private
     * @param	array  $discount_arr 减免信息
     * @return	mixed  成功返回减免单编号，失败返回FALSE
     */
    public function add_discount_details($discount_arr)
    {
        $insertId = 0;
        
        if(is_array($discount_arr) && !empty($discount_arr))
        {   
            // 自增主键返回插入ID
            $options['table'] = $this->tablePrefix.$this->table_detail;
            $insertId = $this->add($discount_arr, $options);
        }
        //echo $this->_sql();
        return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据减免明细ID删除减免明细
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function del_discount_detail_by_id($ids)
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

    	$up_num = self::del_discount_detail_by_cond($cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据条件删除减免明细信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @return	mixed  成功返回删除行数，失败返回FALSE
     */
    public function del_discount_detail_by_cond($cond_where)
    {
        $up_num = 0;
        
    	if($cond_where != '')
    	{	
            $update_arr = array();
            $update_arr['REFUND_STATUS'] = intval($this->_conf_discount_status['discount_delete']);
    		$up_num = self::update_discount_detail_by_cond($update_arr, $cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
   
        
    /**
     * 根据减免申请单编号提交减免申请到工作流审核
     *
     * @access	public
     * @param	$int  $list_id 审核单编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function sub_discount_detail_to_apply($list_id)
    {
    	$list_id = intval($list_id);
    
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//已提交审核单状态
    		$audit_status  = $this->_conf_discount_status['discount_audit'];
    		$cond_where .= " AND REFUND_STATUS = '".$audit_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['REFUND_STATUS'] = $this->_conf_discount_status['discount_apply'];
            
    		$up_num = self::update_discount_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    
    
    
    
    
    /**
     * 根据减免明细ID更新减免明细信息
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_discount_detail_by_id($ids, $update_arr)
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

    	$up_num = self::update_discount_detail_by_cond($cond_where,$update_arr);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据指定条件更新减免明细信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_discount_detail_by_cond($cond_where,$update_arr)
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
     * 根据指定ID更新减免申请单信息
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_discount_list_by_id($ids, $update_arr)
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

    	$up_num = self::update_discount_list_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据指定条件更新减免申请单信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_discount_list_by_cond($update_arr, $cond_where)
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
     * 根据编号获取减免单信息
     *
     * @access	public
     * @param	int  $list_id 减免单号
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_discount_list_by_id($list_id, $search_field = array())
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
     * 根据条件获取减免单信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_discount_list_by_cond($cond_where, $search_field = array())
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
     * 获取最新一条减免单记录
     *
     * @access	public
     * @param int $add_uid 申请用户
     * @param int $city_id 城市编号
     * @param int $status 状态
     * @return	array 查询结果
     */
    public function get_last_discount_list($add_uid, $city_id, $status = 0)
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
     * 根据编号获取减免明细信息
     *
     * @access	public
     * @param	int  $id 减免单号
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_discount_detail_by_id($id, $search_field = array())
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
     * 根据条件获取减免明细信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_discount_detail_by_cond($cond_where, $search_field = array())
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
    
   public function get_detail_table_name(){
       return $this->tablePrefix.$this->table_detail;
   }
   
   public function get_list_table_name(){
       return $this->tablePrefix.$this->table_list;
   }
}

/* End of file MemberDiscountModel.class.php */
/* Location: ./Lib/Model/MemberDiscountModel.class.php */