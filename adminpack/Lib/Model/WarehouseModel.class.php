<?php
/**
 * 库存管理类
 *
 * @author 
 */

class WarehouseModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'WAREHOUSE';
    
    /***仓库物料状态描述数组***/
    private $_conf_status_remark = array(
    							'-1' => '打回退库申请',
                                '0' => '未确认入库',
                                '1' => '确认入库'
                            );
    
    /***仓库物料状态***/
    private $_conf_status = array(
    							'send_back' => '-1',
                                'not_audit' => 0,
                                'audited' => 1
                            );
    
    /***仓库物料来源描述***/
    private $_conf_from_remark = array(
                                1 => '大宗采购',
                                2 => '退库'
                            );
    
    /***仓库物料来源***/
    private $_conf_from = array(
                                'bulk_purchase' => 1,
                                'return_to_warehouse' => 2
                        );
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * 获取仓库物料状态描述数组
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
     * 获取仓库物料状态
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
     * 获取仓库物料来源描述数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_from_remark()
    {
        return $this->_conf_from_remark;
    }
    
    
    /**
     * 获取仓库物料来源数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_from()
    {
        return $this->_conf_from;
    }
    
    
    /**
     * 采购明细退库
     *
     * @access	public
     * @param	array $purchase_info 采购明细
     * @return	array
     */
    public function return_to_warehouse($purchase_info)
    {
        //自增主键返回插入ID
        $insertId = $this->add_warehouse_info($purchase_info);
        
        return $insertId;
    }
    
    
    /**
     * 添加库存信息
     *
     * @access	public
     * @param	array $product_info 物品信息
     * @return	array
     */
    public function add_warehouse_info($product_info)
    {
        if(is_array($product_info) && !empty($product_info))
        {   
            // 自增主键返回插入ID
            $insertId = $this->add($product_info);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 确认退库成功
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function confirm_to_warehouse($ids)
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
    	
    	$status_arr = self::get_conf_status();
    	$status_not_audit = $status_arr['not_audit'];
    	 
    	//更新条件
    	$cond_where .= " AND STATUS = '".$status_not_audit."'";
    	
    	$update_arr['STATUS'] = $status_arr['audited'];
    	$up_num = self::update_info_by_cond($update_arr, $cond_where);
        
        return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 打回退库申请
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function application_send_back($ids)
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
    	
    	$status_arr = self::get_conf_status();
    	$status_not_audit = $status_arr['not_audit'];
    	
    	//更新条件
    	$cond_where .= " AND STATUS = '".$status_not_audit."'";
		
    	$update_arr['STATUS'] = $status_arr['send_back'];
    	$up_num = self::update_info_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
	
    
    /**
     * 更新库存领用数量
     *
     * @access	public
     * @param	int  库存物品编号 
     * @param	float  $use_mum_this_time 本次领用数量
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_warehouse_use_num($id , $use_mum_this_time)
    {   
        $up_num = 0;
        
        $use_mum_this_time = floatval($use_mum_this_time);
        $id = intval($id);
        
        if( $id > 0 && abs($use_mum_this_time) > 0)
        {
            $update_arr['USE_NUM'] = array('exp' ,"USE_NUM + ". $use_mum_this_time) ;
            $up_num = self::update_info_by_id($id, $update_arr);
        }
        
        return $up_num > 0 ? $up_num : FALSE;
    }
    
    
    /**
     * 根据ID更新信息
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
     * 更新信息
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
    	//echo $this->getLastSql();
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据物品品类、名称获取物品库存数量
     *
     * @access	public
     * @param	string  $brand  品牌
     * @param	string  $model  型号
     * @param	string  $product_name  物品名称
     * @param	string  $price_limit   限价
     * @param	int  $city_id  城市编号
     * @return	float   库存数量
     */
    public function get_total_num_by_name($brand, $model, $product_name, $price_limit, $city_id)
    {   
        $total_num = 0;
        $brand = strip_tags($brand);
        $model = strip_tags($model);
        $product_name = strip_tags($product_name);
        $price_limit = floatval($price_limit);
        $city_id = intval($city_id);
        
        if($brand != '' &&  $model != '' && $product_name != '')
        {   
            $staus_audited = $this->_conf_status['audited'];   
            $cond_where = " CITY_ID = '".$city_id."' AND BRAND = '".$brand."' AND MODEL = '".$model."' "
                   . " AND PRODUCT_NAME = '".$product_name."' "
                   ." AND PRICE <= '".$price_limit."' AND STATUS = '".$staus_audited."' AND NUM > USE_NUM";
            
            $total_num = $this->where($cond_where)->sum('NUM - USE_NUM');
            //echo $this->getLastSql();
        }
        
        return $total_num;
    } 
    
    
    /**
     * 查询采购明细未确认的退库申请数量
     *
     * @access	public
     * @param	int  $purchase_list_id  采购明细编号
     * @return	int  未确认数量
     */
    function get_not_confrim_num_by_pl_id($purchase_list_id)
    {
    	$not_audit = $this->_conf_status['not_audit'];
    	$cond_where = "PL_ID = '".$purchase_list_id."' AND STATUS = '".$not_audit."' ";
    	$total_num = $this->where($cond_where)->count();
    	
    	return intval($total_num);
    }
	
    
    /**
     * 根据关键词获取最早的可以领用的商品库存情况
     *
     * @access	public
     * @param	string  $brand  品牌
     * @param	string  $model  型号
     * @param	string  $product_name  物品名称
     * @param	string  $price_limit   限价
     * @param	int     $city_id   城市编号
     * @param  array  $search_field 需要查询的字段
     * @return	array   符合条件的物品信息
     */
    public function get_earliest_puroduct_info_by_search_key($brand, $model, 
            $product_name, $price_limit, $city_id ,$search_field = array())
    {   
        //物品品牌
        $brand = strip_tags($brand);
        //物品型号
        $model = strip_tags($model);
        //物品名称
        $product_name = strip_tags($product_name);
        //最高限价
        $price_limit = floatval($price_limit);
        //城市参数
        $city_id = intval($city_id);
        
        $staus_audited = $this->_conf_status['audited'];
        $cond_where = " CITY_ID = '".$city_id."' AND BRAND = '".$brand."' AND MODEL = '".$model."' "
                . " AND PRODUCT_NAME = '".$product_name."'"
                . " AND PRICE <= '".$price_limit."' AND STATUS = '".$staus_audited."' AND NUM > USE_NUM";
        
        $product_info = $this->get_product_info_by_cond($cond_where, $search_field, 1, 'ID', 'ASC');
        
        return $product_info;
    }
    
    
    /**
     * 根据物品品类、名称获物品库存情况
     *
     * @access	public
     * @param	string $cond_where  查询条件
     * @param	array  $search_field 查询子弹数组
     * @return	array   符合条件的物品信息
     */
    public function get_product_info_by_ids($ids, $search_field = array(), $orderby = 'ID', $desc = 'ASC')
    {
        $info = array();
        
        $cond_where = "";
        if(is_array($ids) && !empty($ids))
        {   
            $ids_str = implode(',', $ids);
            $cond_where = " ID IN (".$ids_str.")";
            $limit = count($ids);
        }
        else
        {
            $id  = intval($ids);
            $cond_where = " ID = '".$id."'";
            $limit = 1;
        }
        
        $info = self::get_product_info_by_cond($cond_where, $search_field, $limit, $orderby, $desc);
        
        return $info;
    }
    
    
    /**
     * 根据物品品类、名称获物品库存情况
     *
     * @access	public
     * @param	string $cond_where  查询条件
     * @param	array  $search_field 查询子弹数组
     * @return	array   符合条件的物品信息
     */
    public function get_product_info_by_cond($cond_where, $search_field = array() , 
            $limit = 1, $orderby = 'ID' , $desc = 'ASC')
    {
        $info = array();
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $info;
        }
        
        if(is_array($search_field) && !empty($search_field) )
        {
            $info = $this->field($search_str)->where($cond_where)->order($orderby." ".$desc)->limit($limit)->select();
        }
        else
        {
            $info = $this->where($cond_where)->order($orderby." ".$desc)->limit($limit)->select();
        }
        
        return $info;
    }
}

/* End of file WarehouseModel.class.php */
/* Location: ./Lib/Model/WarehouseModel.class.php */