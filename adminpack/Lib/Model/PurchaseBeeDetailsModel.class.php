<?php
/**
 * 小蜜蜂采购众客回执单
 *
 * @author zhang Xiaojun
 */
class PurchaseBeeDetailsModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PURCHASER_BEE_DETAILS';
    
    //构造函数
    public function __construct(){
        parent::__construct();
    }
    
    public function get_bee_detail_status(){
        return $status = array(
            0 => '未提交报销',
            1 => '已提交报销',
            2 => '已报销',
            3 => '已驳回',
            4 => '超额流程审核中',
        );
    }
	/**
     * 根据指定条件更新小蜜蜂任务信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_bee_detail_info($update_arr, $cond_where){	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$up_num = $this->where($cond_where)->save($update_arr);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
}

/* End of file PurchaseListModel.class.php */
/* Location: ./Lib/Model/PurchaseListModel.class.php */