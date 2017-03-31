<?php
/**
 * 采购管理申请单MODEL类
 *
 * @author liuhu
 */
class PurchaseRequisitionModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PURCHASE_REQUISITION';
    
    /***采购单状态***/
    private  $_conf_requisition_status = array(
			                                    'not_sub' => 0,		//未提交
			                                    'submitted' => 1,	//流程审核中
			                                    'approved' => 2,	//审核通过
			                                    'not_agree' => 3,	//审核未通过
			                                    'finished' => 4,	//已采购
    									);
    
    /***采购单状态描述***/
    private  $_conf_requisition_status_remark = array(
				                                    0 => '未提交',
				                                    1 => '审核中',
				                                    2 => '审核通过',
				                                    3 => '审核未通过',
				                                    4 => '采购完成'
    											);

    /***采购类型***/
    private $_conf_purchase_type = array(
							    		'project_purchase' => '1',
							    		'bulk_purchase' => '2',
    								);
    
    /***采购类型描述***/
    private $_conf_purchase_type_remark = array(
												'1' => '业务采购',
												'2' => '大宗采购',
    										);
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * 获取采购单状态数组
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
     * 获取采购单状态描述数组
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
     * 获取采购类型数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_purchase_type()
    {
    	return $this->_conf_purchase_type;
    }
    
    
    /**
     * 获取采购类型描述数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_purchase_type_remark()
    {
    	return $this->_conf_purchase_type_remark;
    }
    
    
    /**
     * 添加采购申请
     *
     * @access	public
     * @param	array  $requisition_arr 申请单信息
     * @return	mixed  成功返回退款单编号，失败返回FALSE
     */
    public function add_purchase_requisition($requisition_arr)
    {   
        $insertId = 0;
        if(is_array($requisition_arr) && !empty($requisition_arr))
        {   
            // 自增主键返回插入ID
            $insertId = $this->add($requisition_arr);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据采购申请单ID，提交采购申请
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function submit_purchase_by_id($ids)
    {	
    	$up_num = 0;
    	
    	$update_arr = array();
    	$status = intval($this->_conf_requisition_status['submitted']);
    	$update_arr['STATUS'] = $status;
    	$up_num = $this->update_purchase_by_id($ids, $update_arr);
    	
    	return $up_num > 0 ? $up_num :FALSE;
    }
    
    
    /**
     * 根据采购申请单更新采购申请单内容
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_to_finished_by_id($ids)
    {   
    	$up_num = 0;
    	
    	$update_arr = array();
    	$status = intval($this->_conf_requisition_status['finished']);
    	$update_arr['STATUS'] = $status;
    	$up_num = $this->update_purchase_by_id($ids, $update_arr);
    	
    	return $up_num > 0 ? $up_num :FALSE;
    }
    
    
    /**
     * 根据采购申请单更新采购申请单内容
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_purchase_by_id($ids, $update_arr)
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
    
    	$up_num = self::update_purchase_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据采购条件更新采购申请单
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_purchase_by_cond($update_arr, $cond_where)
    {
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{
    		$up_num = $this->where($cond_where)->save($update_arr);
    		//echo $this->getLastSql();
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
 
    
    /**
     * 根据采购单编号，获取采购单信息
     *
     * @access	public
     * @param	mixed  $id 采购单编号【数组或者单个付款明细编号】
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_purchase_by_id($id, $search_field = array())
    {
        $info = array();
        $cond_where = "";
        if(is_array($id) && !empty($id))
        {
            $id_str = implode(',', $id);
            $cond_where = "ID IN (".$id_str.")";
        }
        else 
        {   
            $id = intval($id);
            $cond_where = "ID = '".$id."'";
        }
        
        if($cond_where == '')
        {
            return $info;
        }
        
        $info = self::get_purchase_by_cond($cond_where, $search_field);
        
        return $info;
    }
    
    
    /**
     * 根据条件获取获取采购单信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_purchase_by_cond($cond_where, $search_field = array())
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
        
        return $info;
    }
    
    
    /**
     * 根据编号删除采购申请单
     *
     * @access	public
     * @param	mixed  $ids 编号
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function del_purchase_by_ids($ids)
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
    	
    	$up_num = $this->where($cond_where)->delete();
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
}

/* End of file PurchaseModel.class.php */
/* Location: ./Lib/Model/PurchaseModel.class.php */