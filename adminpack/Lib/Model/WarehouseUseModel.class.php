<?php
/**
 * 库存领用MODEL
 *
 * @author liuhu
 */
class WarehouseUseModel extends Model {
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'WAREHOUSE_USE_DETAILS';
    
    /***库存领用明细状态***/
    private  $_conf_status = array(
                                    'not_confirm' => 0,   //未确认
                                    'confirmed' => 1,     //已确认
                                );
    
    /***库存领用明细状态描述***/
    private  $_conf_status_remark = array(
                                        0 => '未确认',
                                        1 => '已确认',
                                    );
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * 获取库存领用明细状态数组
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
     * 获取库存领用明细状态描述数组
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
     * 添加库存领用明细
     *
     * @access	public
     * @param	array  $detail_arr 领用明细
     * @return	mixed  成功返回领用明细编号，失败返回FALSE
     */
    public function add_used_info($detail_arr)
    {
        $insertId = 0;
        
        if(is_array($detail_arr) && !empty($detail_arr))
        {   
            // 自增主键返回插入ID
            $insertId = $this->add($detail_arr);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    /**
     * 根据领用编号删除领用明细
     * @access	public
     * @param	int  $id  采购领用明细
     * @return	mixed  影响行数，失败返回FALSE
     */
    public function del_use_info_by_id($id)
    {   
        $up_num = 0;
        $id = intval($id);
        
        if($id > 0)
        {
            $cond_where = "ID = '".$id."'";
            $up_num = $this->where($cond_where)->delete();
        }
        
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据采购明细编号，确认领用
     *
     * @access	public
     * @param	mixed  $purchase_id 采购明细编号
     * @return	mixed  影响行数，失败返回FALSE
     */
    public function confirm_used_by_purchase_id($purchase_id)
    {   
        $update_arr['STATUS'] = $this->_conf_status['confirmed'];
        $update_arr['CONFIRM_TIME'] = date('Y-m-d H:i:s');
        
        $up_num =  self::update_info_by_id($purchase_id, $update_arr);
        
        return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据采购明细编号，取消确认领用状态
     *
     * @access	public
     * @param	mixed  $purchase_id 采购明细编号
     * @return	mixed  影响行数，失败返回FALSE
     */
    public function cancel_confirm_used_by_purchase_id($purchase_id)
    {   
        $update_arr['STATUS'] = $this->_conf_status['not_confirm'];
        $update_arr['CONFIRM_TIME'] = '';
        
        $up_num =  self::update_info_by_id($purchase_id, $update_arr);
        
        return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据编号更新明细信息
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
     * 根据条件更新某条明细信息
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
     * 获取最后一条领用信息
     * @access	public
     * @param	int  $purchase_id  采购明细编号
     * @return	 array $use_info
     */
    public function get_last_use_info_by_purchase_id($purchase_id)
    {
        $use_info = array();
        
        if( $purchase_id > 0)
        {
            $use_info = $this->where("PL_ID = '".$purchase_id."'")->order('ID DESC')->find();
        }
        
        return $use_info;
    }

    /**
     * 获取采购使用置换仓库信息
     * @param $purchase_id
     * @return array
     */
    public function getDisplaceUseByPurchaseId($purchase_id){
        $use_info = array();

        if( $purchase_id > 0)
        {
            $use_info = $this->where("PL_ID = '".$purchase_id."' AND TYPE = 2")->order('ID DESC')->select();
        }

        return $use_info;
    }

    /**
     * 获取库存领用数量  置换领用数量
     * @param $purchase_id
     * @param $useType
     * @return int
     */
    public function getSumnumByPurchaseId($purchase_id,$useType){

        if( $purchase_id > 0){
            $queryRet = M('erp_warehouse_use_details')->where("PL_ID = '".$purchase_id."' AND TYPE = {$useType}")->sum('USE_NUM');
            return intval($queryRet);
        }

        return 0;
    }
}

/* End of file WarehouseUseModel.class.php */
/* Location: ./Lib/Model/WarehouseUseModel.class.php */