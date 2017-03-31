<?php
/**
 * 置换申请MODEL
 */
class DisplaceModel extends Model{

    protected $tablePrefix  =   'erp_';
    protected $tableNameList = 'displace_warehouse';
    protected $tableNameMain = 'displace_requisition';

    /**
     * 审核通过
     */
    const WAREHOUSE_AUDITED = 2;

    /***合同置换属性***/
    private  $_conf_contract_displace_status = array(
        0 => '非置换',
        1 => '部分置换',
        2 => '完全置换',
    );

    /***置换单状态***/
    private  $_conf_requisition_status = array(
        0 => '未提交',
        1 => '审核中',
        2 => '审核通过',
        3 => '审核未通过',
        4 => '置换完成',
    );

    /***置换仓库状态***/
    private $_conf_list_status = array(
        1 => '未入库',
        2 => '已入库',
        3 => '项目领用',
        4 => '公司内部领用',
        5 => '已售卖',
        6 => '已报损',
    );

    /***采购明细退库状态***/
    private $_conf_invoice_status = array(
        0 => '未申请',
        1 => '申请中',
        2 => '已开票',
    );


    //构造函数
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 获取置换单状态
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_requisition_status(){
        return $this->_conf_requisition_status;
    }

    /**
     * 获取仓库明细状态数组
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
     * 获取开票状态
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_invoice_status()
    {
        return $this->_conf_invoice_status;
    }


    /**
 * 根据置换单ID获取信息
 * @param $id
 * @param array $searchField
 * @return array
 */
    public function getDisplaceById($id, $searchField = array()){

        $return = array();

        //获取数值
        if(is_array($id) && !empty($id))
        {
            $idStr = implode(',', $id);
            $condWhere = "ID IN (".$idStr.")";
        }
        else
        {
            $id = intval($id);
            $condWhere = "ID = '".$id."'";
        }

        if(!empty($searchField))
            $searchFieldStr = implode(',',$searchField);

        $return = M($this->tablePrefix . $this->tableNameMain)
            ->field($searchFieldStr)
            ->where($condWhere)
            ->select();

        return $return;

    }

    /**
     * 根据置换明细ID获取信息
     * @param $id
     * @param array $searchField
     * @return array
     */
    public function getDisplaceDetailById($id, $searchField = array()){

        $return = array();

        //获取数值
        if(is_array($id) && !empty($id))
        {
            $idStr = implode(',', $id);
            $condWhere = "ID IN (".$idStr.")";
        }
        else
        {
            $id = intval($id);
            $condWhere = "ID = '".$id."'";
        }

        if(!empty($searchField))
            $searchFieldStr = implode(',',$searchField);

        $return = M('erp_displace_warehouse')
            ->field($searchFieldStr)
            ->where($condWhere)->select();

        return $return;

    }

    /**
     * 获取售卖总金额
     * @param $listId
     * @return int
     */
    function getSaleTotal($listId){
        $total = 0;

        $sql = "SELECT RTRIM(to_char(SUM(AMOUNT * MONEY),'fm99999999990.99'),'.') AS TOTALMONEY FROM ERP_DISPLACE_APPLYDETAIL A LEFT JOIN ERP_DISPLACE_APPLYLIST B ON A.LIST_ID = B.ID";
        $sql .= ' WHERE B.ID = ' . $listId;

        $dbResult = D()->query($sql);

        if($dbResult){
            $total = $dbResult[0]['TOTALMONEY'];
        }

        return $total;
    }


    /**
     * 删除置换单
     * @param $drId
     * @return bool
     */
    public function delDisplaceById($drId){
        $return = false;

        //删除明细
        $sql = 'delete from ' . $this->tablePrefix . $this->tableNameList . ' where DR_ID = ' . $drId;
        $deleteListRet = M($this->tablePrefix . $this->tableNameList)->query($sql);

        //删除置换单
        $sql = 'delete from ' . $this->tablePrefix . $this->tableNameMain . ' where Id = ' . $drId;
        $deleteMainRet = M($this->tablePrefix . $this->tableNameMain)->query($sql);

        if($deleteMainRet !== false && $deleteListRet !== false)
            $return = true;

        return $return;
    }

    /**
     * 删除申请单明细
     * @param int $detailId
     * @return bool
     */
    public function delDisplaceDetailById($detailId = 0){
        $return = false;

        //删除明细
        $sql = 'delete from ' . $this->tablePrefix . $this->tableNameList . ' where ID = ' . $detailId;
        $deleteListRet = M($this->tablePrefix . $this->tableNameList)->query($sql);

        if($deleteListRet !== false)
            $return = true;

        return $return;
    }

    /**
     * 置换申请提交，更新置换申请的状态为申请中
     * @param $drId
     * @return bool
     */
    public function submitDisplaceById($drId,$status){
        $return = false;

        //更新置换单状态
        $updateSql = 'update '. $this->tablePrefix . $this->tableNameMain . ' set status = ' . $status . ' where id = ' . $drId;
        $updateMainRet = M($this->tablePrefix . $this->tableNameMain)->query($updateSql);

        //更新明细状态
        $updateSql = 'update '. $this->tablePrefix . $this->tableNameList . ' set status = ' . $status . ' where dr_id = ' . $drId;
        $updateListRet = M($this->tablePrefix . $this->tableNameList)->query($updateSql);

        if($updateListRet !== false && $updateMainRet !== false)
            $return = true;

        return $return;
    }

    /**
     * 根据ID更新信息
     *
     * @access	public
     * @param	string  $ids 要更新的记录
     * @param array $update_arr 要跟新的字段
     * @return
     */
    public function update_info_by_id($ids, $update_arr)
    {
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN ($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }

        $res = self::update_info_by_cond($cond_where, $update_arr);

        return $res;
    }


    /**
     * 根据条件更新信息
     *
     * @access	public
     * @param	string  $cond_where 要更新的记录
     * @param array $update_arr 要跟新的字段的键值对
     * @return
     */
    public function update_info_by_cond($cond_where, $update_arr)
    {
        $up_num = 0;

        if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
        {
            $up_num = $this->where($cond_where)->save($update_arr);
        }

        return $up_num > 0  ? $up_num : FALSE;
    }

    /**
     * 获取置换仓库的物品数量
     * @param array $product
     * @param int $priceLimit
     * @param int $cityId
     * @return int
     */
    public function getTotalNumByName($product = array(), $priceLimit = 1, $cityId = 1) {
        $totalNum = 0;
        $brand = strip_tags($product['brand']); // 品牌
        $model = strip_tags($product['model']); // 型号
        $productName = strip_tags($product['name']); // 品名
        $priceLimit = floatval($priceLimit);
        $cityId = intval($cityId);

        if($brand != '' &&  $model != '' && $productName != '') {
            $template = <<<TEMPLATE_SQL
                CITY_ID = %s
                AND BRAND = '%s'
                AND MODEL = '%s'
                AND PRODUCT_NAME = '%s'
                AND PRICE <= '%s'
                AND STATUS = %d
                AND INBOUND_STATUS = 2
TEMPLATE_SQL;
            $where = sprintf($template, $cityId, $brand, $model, $productName, $priceLimit, self::WAREHOUSE_AUDITED);
            $totalNum = M('erp_displace_warehouse')->where($where)->sum('NUM');
        }

        return $totalNum;
    }

    /**
     * 获取置换仓库中的产品
     * @param $product
     * @param $priceLimit
     * @param $cityId
     * @return mixed
     */
    public function getDisplaceWarehouseProduct($product, $priceLimit, $cityId) {
        //物品品牌
        $brand = strip_tags($product['brand']);
        //物品型号
        $model = strip_tags($product['model']);
        //物品名称
        $productName = strip_tags($product['name']);
        //最高限价
        $priceLimit = floatval($priceLimit);
        //城市参数
        $cityId = intval($cityId);


        $warehouseProduct = array();
        if($brand != '' &&  $model != '' && $productName != '') {
            //获取已入库数据 + 库存数量 > 0
            $template = <<<TEMPLATE_SQL
                CITY_ID = %s
                AND BRAND = '%s'
                AND MODEL = '%s'
                AND PRODUCT_NAME = '%s'
                AND PRICE <= '%s'
                AND STATUS = %d
                AND INBOUND_STATUS = 2
                AND NUM > 0
TEMPLATE_SQL;
            $where = sprintf($template, $cityId, $brand, $model, $productName, $priceLimit, self::WAREHOUSE_AUDITED);
            $warehouseProduct = D('erp_displace_warehouse')->where($where)->order("ID ASC")->limit(1)->select();
        }

        return $warehouseProduct;
    }

    /**
     * 更新置换仓库中产品的数量
     * @param int $warehouseProductId
     * @param int $useNum
     * @return bool
     */
    public function updateWarehouseUseNum($warehouseProductId = 0, $useNum = 1) {
        // 传入的参数有错误
        if (intval($warehouseProductId) <= 0) {
            return false;
        }

        // 不需要更新
        if ($useNum == 0) {
            return true;
        }

        // 先更新库存数据，减少剩余数据量,如果是负数，则是退回仓库
        if($useNum > 0) {
            $dbResult = D('erp_displace_warehouse')->where("ID = {$warehouseProductId}")->setDec('NUM', $useNum);
        }else{
            $dbResult = D('erp_displace_warehouse')->where("ID = {$warehouseProductId}")->setInc('NUM', abs($useNum));
        }
        if ($dbResult === false) {
            return false;
        }

        $updateNumExp = " USE_NUM = USE_NUM + ({$useNum})";
        $updateSql = <<<UPDATE_USE_NUM_SQL
            UPDATE erp_displace_warehouse
            SET %s
            WHERE id = %d
UPDATE_USE_NUM_SQL;
        $sql = sprintf($updateSql, $updateNumExp, $warehouseProductId);
        $dbResult = $this->query($sql);
        if ($dbResult === false) {
            return false;
        }

        return true;
    }
}

/* End of file DisplaceModel.class.php */
/* Location: ./Lib/Model/DisplaceModel.class.php */