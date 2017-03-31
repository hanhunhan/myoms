<?php
/**
 * 采购明细MODEL
 *
 * @author liuhu
 */
class PurchaseListModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PURCHASE_LIST';

    /***采购明细状态***/
    private $_conf_list_status = array(
        'not_purchased' => 0,   //未采购
        'purchased' => 1,       //已采购
        'reimbursed' => 2,      //已报销
        'in_warehouse' => 3,    //已入库
        'reimbursing' => 4,  // 正在申请报销
    );
    
    /***采购明细状态描述***/
    private $_conf_list_status_remark = array(
        0 => '未采购',
        1 => '已采购',
        2 => '已报销',
        3 => '已入库',
        4 => '申请报销中'
    );
    
    /***采购明细退库状态***/
    private $_conf_back_stock_status = array(
                                            'not_apply' => 0,   //未申请
                                            'applied' => 1,     //申请中
                                            'send_back' => 2	//申请打回
                                            );
    
    /***采购明细退库状态***/
    private $_conf_back_stock_status_remark = array(
                                            0 => '未申请',
                                            1 => '申请中',
    										2 => '申请打回',
                                            );
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * 获取采购明细状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_list_status()
    {
    	return $this->_conf_list_status;
    }
    
    
    /**
     * 获取采购明细状态描述数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_list_status_remark()
    {
    	return $this->_conf_list_status_remark;
    }
    
    
    /**
     * 获取采购明细退库状态数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_back_stock_status()
    {
    	return $this->_conf_back_stock_status;
    }
    
    
    /**
     * 获取采购明细退库状态描述数组
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_back_stock_status_remark()
    {
    	return $this->_conf_back_stock_status_remark;
    }
    
    
    /**
     * 添加采购明细清单
     *
     * @access	public
     * @param	array  $purchase_arr 退款信息
     * @return	mixed  成功返回退款单编号，失败返回FALSE
     */
    public function add_purchase_list($purchase_arr)
    {
        $insertId = 0;
        
        if(is_array($purchase_arr) && !empty($purchase_arr))
        {   
            // 自增主键返回插入ID
            $insertId = $this->add($purchase_arr);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据采购明细编号更新采购明细为已采购
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_to_purchased_by_id($ids)
    {   
    	$up_num = 0;
    	
    	$update_arr = array();
    	$status = intval($this->_conf_list_status['purchased']);
    	$update_arr['STATUS'] = $status;
    	$up_num = $this->update_purchase_list_by_id($ids, $update_arr);
    	
    	return $up_num > 0 ? $up_num :FALSE;
    }
    
    
    /**
     * 根据采购明细编号更新采购明细为已报销
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_to_reimbursed_by_id($ids)
    {   
    	$up_num = 0;
    	$update_arr = array();
    	$status = intval($this->_conf_list_status['reimbursed']);
    	$update_arr['STATUS'] = $status;
    	$up_num = $this->update_purchase_list_by_id($ids, $update_arr);
    	
    	return $up_num > 0 ? $up_num :FALSE;
    }
    
    
    /**
     * 根据采购明细编号更新采购明细为已入库
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_to_in_warehouse_by_id($ids)
    {   
    	$up_num = 0;
    	
    	$update_arr = array();
    	$status = intval($this->_conf_list_status['in_warehouse']);
    	$update_arr['STATUS'] = $status;
    	$up_num = $this->update_purchase_list_by_id($ids, $update_arr);
    	
    	return $up_num > 0 ? $up_num :FALSE;
    }
    
    
    /**
     * 根据采购明细编号更新采购明细为申请退库中
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_to_apply_back_stock_by_id($ids)
    {   
    	$up_num = 0;
    	
    	$update_arr = array();
    	$status = intval($this->_conf_back_stock_status['applied']);
    	$update_arr['BACK_STOCK_STATUS'] = $status;
    	$up_num = $this->update_purchase_list_by_id($ids, $update_arr);
    	
    	return $up_num > 0 ? $up_num :FALSE;
    }
    
    
    /**
     * 根据采购明细编号更新采购明细退库状态为打回
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_apply_send_back_by_id($ids)
    {
    	$up_num = 0;
    	 
    	$update_arr = array();
    	$status = intval($this->_conf_back_stock_status['send_back']);
    	$update_arr['BACK_STOCK_STATUS'] = $status;
    	$up_num = $this->update_purchase_list_by_id($ids, $update_arr);
    	
    	return $up_num > 0 ? $up_num :FALSE;
    }
    
    
    /**
     * 根据采购明细编号更新采购明细退库数量
     *
     * @access	public
     * @param	mixed  $id 单个ID
     * @param	int  $stock_num 退库数量
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_stock_num_by_id($id, $stock_num)
    {   
    	$up_num = 0;
    	
    	$update_arr = array();
    	$status = intval($this->_conf_back_stock_status['not_apply']);
    	$update_arr['BACK_STOCK_STATUS'] = $status;
        $update_arr['STOCK_NUM'] = array('exp', 'STOCK_NUM + '.$stock_num );
        
    	$up_num = $this->update_purchase_list_by_id($id, $update_arr);
    	
    	return $up_num > 0 ? $up_num :FALSE;
    }
    
    
    /**
     * 采购明细添加到合同
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $contract_id 合同编号
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function add_to_contract($ids, $contract_id)
    {
        $up_num = 0;
    	
    	$update_arr = array();
        $update_arr['CONTRACT_ID'] = $contract_id;
    	$up_num = $this->update_purchase_list_by_id($ids, $update_arr);
    	
    	return $up_num > 0 ? $up_num : FALSE;
    }
    
    
    /**
     * 采购明细、合同取消挂靠关系
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function delete_from_contract($ids)
    {
        $up_num = 0;
    	$update_arr = array();
        $update_arr['CONTRACT_ID'] = '';
    	$up_num = $this->update_purchase_list_by_id($ids, $update_arr);
    	
    	return $up_num > 0 ? $up_num :FALSE;
    }
    
    
    /**
     * 根据采购明细编号更新采购明细
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_purchase_list_by_id($ids, $update_arr)
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
    
    	$up_num = self::update_purchase_list_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }

    /**
     * 插入项目收益
     * @param $purchaseInfo
     * @return bool
     */
    public function insertDisplaceIncome($purchaseInfo){
        if (notEmptyArray($purchaseInfo)) {
            $useInfo = D('WarehouseUse')->getDisplaceUseByPurchaseId($purchaseInfo['DETAIL_ID']);
            if(notEmptyArray($useInfo)){
                foreach($useInfo as $useInfoItem) {
                    //插入项目收益
                    $result = D('InboundUse')->insertProIncome($useInfoItem);
                    if($result===false){
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    
    /**
     * 根据采购条件更新采购明细
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_purchase_list_by_cond($update_arr, $cond_where)
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
     * 根据采购单编号，获取采购明细信息
     *
     * @access	public
     * @param	mixed  $id 采购单编号【数组或者单个付款明细编号】
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_purchase_list_by_prid($id, $search_field = array())
    {
        $info = array();
        $cond_where = '';
        if(is_array($id) && !empty($id))
        {
            $id_str = implode(',', $id);
            $cond_where = "PR_ID IN (".$id_str.")";
        }
        else 
        {   
            $id = intval($id);
            $cond_where = "PR_ID = '".$id."'";
        }
        
        if($cond_where != '')
        {
            $info = self::get_purchase_list_by_cond($cond_where, $search_field);
        }
        
        return $info;
    }
    
    
    /**
     * 根据采购单编号，获取采购明细信息
     *
     * @access	public
     * @param	mixed  $id 采购单编号【数组或者单个付款明细编号】
     * @return	array 查询结果
     */
    public function get_purchase_list_num_by_prid($id)
    {
    	$num = 0;
    	
    	$cond_where = '';
    	if(is_array($id) && !empty($id))
    	{
    		$id_str = implode(',', $id);
    		$cond_where = "PR_ID IN (".$id_str.")";
    	}
    	else
    	{
    		$id = intval($id);
    		$cond_where = "PR_ID = '".$id."'";
    	}
    
    	if($cond_where != '')
    	{
    		$num = $this->where($cond_where)->select();
    	}
    
    	return $num;
    }
    
    
    /**
     * 根据采购明细编号，获取采购明细信息
     *
     * @access	public
     * @param	mixed  $id 采购单编号【数组或者单个付款明细编号】
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_purchase_list_by_id($id, $search_field = array())
    {
        $info = array();
        $cond_where = '';
        
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
        
        if($cond_where != '')
        {
            $info = self::get_purchase_list_by_cond($cond_where, $search_field);
        }
        
        return $info;
    }
    
    
    /**
     * 根据合同编号，获取采购明细信息
     *
     * @access	public
     * @param	mixed  $id 采购合同编号【数组或者单个付款明细编号】
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_purchase_list_by_contract_id($id, $search_field = array())
    {
    	$info = array();
    	$cond_where = '';
    
    	if(is_array($id) && !empty($id))
    	{
    		$id_str = implode(',', $id);
    		$cond_where = "CONTRACT_ID IN (".$id_str.")";
    	}
    	else
    	{
    		$id = intval($id);
    		$cond_where = "CONTRACT_ID = '".$id."'";
    	}
    
    	if($cond_where != '')
    	{
    		$info = self::get_purchase_list_by_cond($cond_where, $search_field);
    	}
    
    	return $info;
    }
    
    
    /**
     * 根据采购申请单查询采购明细数量
     *
     * @access	public
     * @param	int  $pid 采购申请单编号
     * @return	array 查询结果
     */
    public function count_purchase_list_by_pid($pid)
    {
    	$num = 0;
    	$cond_where = '';
        
        $pid = intval($pid);
        
        $cond_where = "PR_ID = '".$pid."' AND STATUS = '".$this->_conf_list_status['not_purchased']."'";
        
    	if($cond_where != '')
    	{
    		$num = $this->where($cond_where)->count();
    	}
    
    	return $num;
    }
    
    
    /**
     * 根据条件获取获取采购明细信息
     *
     * @access	public
     * @param	string  $brand  品牌
     * @param	string  $model  型号
     * @param	string  $product_name  物品名称
     * @param   int    $city_id 城市参数
     * @param	int     $limit  数量
     * @param	int     $offset 偏移量
     * @return	array 查询结果
     */
    public function get_lower_price_by_search($brand, $model, $product_name, $city_id , $limit = 10, $offset = 0)
    {	
    	$info = array();
        $brand = strip_tags($brand);
        $model = strip_tags($model);
        $product_name = strip_tags($product_name);
        $city_id = intval($city_id);
        
        if($brand != '' &&  $model != '' && $product_name != '' && $city_id > 0)
        {   
            $staus_purchased = $this->_conf_list_status['purchased'];   
            $cond_where = " CITY_ID = '".$city_id."' AND BRAND = '".$brand."' AND MODEL = '".$model."' "
                   . " AND PRODUCT_NAME = '".$product_name."' "
                   ."  AND STATUS >= '".$staus_purchased."'";
            
            $info = $this->where($cond_where)
           			->limit($offset.','.$limit)->order("PRICE ASC")->select();
        }
        
        return  $info;
    }
    
    
    /**
     * 根据条件获取获取采购明细信息
     *
     * @access	public
     * @param	string  $cond_where SQL条件
     * @param array $search_field 搜索字段
     * @return	array 查询结果
     */
    public function get_purchase_list_by_cond($cond_where, $search_field = array())
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
     * 根据采购单编号查询采购单下所有采购明细是否全部采购完成
     *
     * @access	public
     * @param	int  $pr_id 采购申请单编号
     * @return	boolean TRUE已全部采购、FALSE还未全部采购
     */
    public function is_all_purchased($pr_id)
    {   
        $purchased_result = FALSE;
        $pr_id = intval($pr_id);
        
        if($pr_id > 0)
        {
            $search_field = array( 'CONTRACT_ID', 'STATUS');
            $all_purchase_list_info = $this->get_purchase_list_by_prid($pr_id, $search_field);
            
            if(is_array($all_purchase_list_info) && !empty($all_purchase_list_info))
            {   
                $not_purchased = $this->_conf_list_status['not_purchased'];
                foreach ($all_purchase_list_info as $key => $value)
                {
//                    if($value['CONTRACT_ID'] == 0 || $value['STATUS'] == $not_purchased )
                    if($value['STATUS'] == $not_purchased )
                    {
                        $purchased_result = FALSE;
                        break;
                    }
                    else
                    {
                        $purchased_result = TRUE;
                    }
                }
            }
        }
        
        return $purchased_result;
    }
    
    
    /**
     * 根据编号删除采购申请明细
     *
     * @access	public
     * @param	mixed  $pr_ids 采购单编号
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function del_purchase_list_by_pr_ids($pr_ids)
    {
    	$cond_where = "";
    	 
    	if(is_array($pr_ids) && !empty($pr_ids))
    	{
    		$pr_ids_str = implode(',', $pr_ids);
    		$cond_where = " PR_ID IN (".$pr_ids_str.")";
    	}
    	else
    	{
    		$pr_ids  = intval($pr_ids);
    		$cond_where = " PR_ID = '".$pr_ids."'";
    	}
    	 
    	$up_num = $this->where($cond_where)->delete();
    	 
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 根据编号删除采购申请明细
     *
     * @access	public
     * @param	mixed  $ids 采购明细编号
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function del_purchase_list_by_ids($ids)
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

    public function isFromStockPurchase($purchaseID) {
        if (empty($purchaseID)) {
            throw_exception("查询{$this->tableName}表, ID不能为空");
        }

        $sql = "
            SELECT
              num buy_num,
              use_num from_stock_num
            FROM erp_purchase_list t
            WHERE t.ID = {$purchaseID}
        ";
        $result = $this->query($sql);
        if (is_array($result) && count($result)) {
            if (intval($result[0]['buy_num']) == 0 && intval($result[0]['from_stock_num']) > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取采购申请
     * @param $purchaseDetailList
     * @return array|mixed
     */
    public function getPurchaseJoinReq($purchaseDetailList) {
        $response = array();
        if (!empty($purchaseDetailList)) {
            if (!is_array($purchaseDetailList)) {
                $purchaseDetailList = array($purchaseDetailList);
            }

            $sql = <<<PURCHASE_INFO_SQL
                SELECT R.ID AS REQ_ID,
                       R.STATUS AS REQ_STATUS,
                       R.USER_ID,
                       R.CASE_ID,
                       R.PRJ_ID,
                       R.CITY_ID,
                       L.CONTRACT_ID,
                       L.ID AS DETAIL_ID,
                       L.STATUS AS DETAIL_STATUS,
                       L.TYPE,
                       L.PRICE,
                       L.NUM,
                       L.IS_FUNDPOOL,
                       L.IS_KF,
                       L.FEE_ID,
                       L.USE_NUM
                FROM ERP_PURCHASE_LIST L
                LEFT JOIN ERP_PURCHASE_REQUISITION R ON R.ID = L.PR_ID
                WHERE L.ID IN (%s)
PURCHASE_INFO_SQL;
            $strPurchaseDetail = implode(',', $purchaseDetailList);
            $response = $this->query(sprintf($sql, $strPurchaseDetail));
        }

        return $response;
    }

    /**
     * 获取采购明细的领用情况
     * @param $purchaseId
     * @return mixed
     */
    public function getWarehouseUsage($purchaseId) {
        $response['status'] = false;
        if (intval($purchaseId)) {
            $sql = <<<SUM_WAREHOUSE_USE
            SELECT
                t.USE_NUM,
                t.USE_PRICE,
                t.TYPE,
                h.price,
                h.num,
                h.input_tax
            FROM ERP_WAREHOUSE_USE_DETAILS t
            LEFT JOIN erp_warehouse h ON h.id = t.wh_id
            WHERE t.PL_ID = %d
            AND t.STATUS = 0
SUM_WAREHOUSE_USE;

            $dbResult = D('WarehouseUse')->query(sprintf($sql, $purchaseId));
            if (notEmptyArray($dbResult)) {
                $response['status'] = true;
                $response['price'] = $dbResult[0]['USE_PRICE'];

                $response['total_num'] = 0;
                $response['warehouse_total_num'] = 0;
                $response['displace_ware_total_num'] = 0;

                foreach($dbResult as $k => $v) {
                    if($v['TYPE']==1) { //采购仓库
                        $response['warehouse_total_num'] += intval($v['USE_NUM']);
                    }else if($v['TYPE']==2){ //置换仓库
                        $response['displace_ware_total_num'] += intval($v['USE_NUM']);
                    }
                }

                $response['total_num'] = $response['warehouse_total_num'] + $response['displace_ware_total_num'];
                // 计算进项税率
                $sumMoney = floatval($dbResult[0]['PRICE']) * floatval($dbResult[0]['NUM']);
                if ($sumMoney > 0) {
                    $response['input_tax_rate'] = round($dbResult[0]['INPUT_TAX'] / $sumMoney, 2);
                } else {
                    $response['input_tax_rate'] = 0;
                }

            }
        }

        return $response;
    }

    public function getWarehouseCost($purchaseId, $reqId) {
        $response['status'] = false;
        if (intval($purchaseId)) {
            $sql = <<<WAREHOUSE_COST
                SELECT
                    t.ID,
                    t.FEE
                FROM ERP_COST_LIST t
                WHERE t.ORG_EXPEND_ID = %d
                AND t.ORG_ENTITY_ID = %d
                AND t.EXPEND_FROM = 4
                AND t.STATUS = 4
WAREHOUSE_COST;
            $dbResult = $this->query(sprintf($sql, $purchaseId, $reqId));
            if (notEmptyArray($dbResult)) {
                $response['status'] = true;
                $response['fee'] = $dbResult[0]['FEE'];  // 费用总额
                $response['id'] = $dbResult[0]['ID'];
            }
        }

        return $response;
    }

    public function reset2NotPurchase($id) {
        $response = false;
        if ($id) {
            $purchaseListId = D('ReimbursementDetail')->where("ID = {$id}")->getField('business_id');
            if ($purchaseListId) {
                $response = $this->where("ID = {$purchaseListId}")->save(array('STATUS' => 1));
            }

//            $prId = $this->where("ID = {$id}")->getField('pr_id');
//            if ($prId) {
//                $response = $this->where("ID = {$id}")->save(array('STATUS' => 1));
//                if ($response !== false) {
//                    if ($this->is_all_purchased($prId)) {
//                        $response = D('PurchaseRequisition')->where("ID = {$prId}")->save(array());
//                    }
//                }
//            }
        }

        return $response;
    }
}

/* End of file PurchaseListModel.class.php */
/* Location: ./Lib/Model/PurchaseListModel.class.php */