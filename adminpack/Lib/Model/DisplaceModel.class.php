<?php
/**
 * �û�����MODEL
 */
class DisplaceModel extends Model{

    protected $tablePrefix  =   'erp_';
    protected $tableNameList = 'displace_warehouse';
    protected $tableNameMain = 'displace_requisition';

    /**
     * ���ͨ��
     */
    const WAREHOUSE_AUDITED = 2;

    /***��ͬ�û�����***/
    private  $_conf_contract_displace_status = array(
        0 => '���û�',
        1 => '�����û�',
        2 => '��ȫ�û�',
    );

    /***�û���״̬***/
    private  $_conf_requisition_status = array(
        0 => 'δ�ύ',
        1 => '�����',
        2 => '���ͨ��',
        3 => '���δͨ��',
        4 => '�û����',
    );

    /***�û��ֿ�״̬***/
    private $_conf_list_status = array(
        1 => 'δ���',
        2 => '�����',
        3 => '��Ŀ����',
        4 => '��˾�ڲ�����',
        5 => '������',
        6 => '�ѱ���',
    );

    /***�ɹ���ϸ�˿�״̬***/
    private $_conf_invoice_status = array(
        0 => 'δ����',
        1 => '������',
        2 => '�ѿ�Ʊ',
    );


    //���캯��
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * ��ȡ�û���״̬
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_requisition_status(){
        return $this->_conf_requisition_status;
    }

    /**
     * ��ȡ�ֿ���ϸ״̬����
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
     * ��ȡ��Ʊ״̬
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
 * �����û���ID��ȡ��Ϣ
 * @param $id
 * @param array $searchField
 * @return array
 */
    public function getDisplaceById($id, $searchField = array()){

        $return = array();

        //��ȡ��ֵ
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
     * �����û���ϸID��ȡ��Ϣ
     * @param $id
     * @param array $searchField
     * @return array
     */
    public function getDisplaceDetailById($id, $searchField = array()){

        $return = array();

        //��ȡ��ֵ
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
     * ��ȡ�����ܽ��
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
     * ɾ���û���
     * @param $drId
     * @return bool
     */
    public function delDisplaceById($drId){
        $return = false;

        //ɾ����ϸ
        $sql = 'delete from ' . $this->tablePrefix . $this->tableNameList . ' where DR_ID = ' . $drId;
        $deleteListRet = M($this->tablePrefix . $this->tableNameList)->query($sql);

        //ɾ���û���
        $sql = 'delete from ' . $this->tablePrefix . $this->tableNameMain . ' where Id = ' . $drId;
        $deleteMainRet = M($this->tablePrefix . $this->tableNameMain)->query($sql);

        if($deleteMainRet !== false && $deleteListRet !== false)
            $return = true;

        return $return;
    }

    /**
     * ɾ�����뵥��ϸ
     * @param int $detailId
     * @return bool
     */
    public function delDisplaceDetailById($detailId = 0){
        $return = false;

        //ɾ����ϸ
        $sql = 'delete from ' . $this->tablePrefix . $this->tableNameList . ' where ID = ' . $detailId;
        $deleteListRet = M($this->tablePrefix . $this->tableNameList)->query($sql);

        if($deleteListRet !== false)
            $return = true;

        return $return;
    }

    /**
     * �û������ύ�������û������״̬Ϊ������
     * @param $drId
     * @return bool
     */
    public function submitDisplaceById($drId,$status){
        $return = false;

        //�����û���״̬
        $updateSql = 'update '. $this->tablePrefix . $this->tableNameMain . ' set status = ' . $status . ' where id = ' . $drId;
        $updateMainRet = M($this->tablePrefix . $this->tableNameMain)->query($updateSql);

        //������ϸ״̬
        $updateSql = 'update '. $this->tablePrefix . $this->tableNameList . ' set status = ' . $status . ' where dr_id = ' . $drId;
        $updateListRet = M($this->tablePrefix . $this->tableNameList)->query($updateSql);

        if($updateListRet !== false && $updateMainRet !== false)
            $return = true;

        return $return;
    }

    /**
     * ����ID������Ϣ
     *
     * @access	public
     * @param	string  $ids Ҫ���µļ�¼
     * @param array $update_arr Ҫ���µ��ֶ�
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
     * ��������������Ϣ
     *
     * @access	public
     * @param	string  $cond_where Ҫ���µļ�¼
     * @param array $update_arr Ҫ���µ��ֶεļ�ֵ��
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
     * ��ȡ�û��ֿ����Ʒ����
     * @param array $product
     * @param int $priceLimit
     * @param int $cityId
     * @return int
     */
    public function getTotalNumByName($product = array(), $priceLimit = 1, $cityId = 1) {
        $totalNum = 0;
        $brand = strip_tags($product['brand']); // Ʒ��
        $model = strip_tags($product['model']); // �ͺ�
        $productName = strip_tags($product['name']); // Ʒ��
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
     * ��ȡ�û��ֿ��еĲ�Ʒ
     * @param $product
     * @param $priceLimit
     * @param $cityId
     * @return mixed
     */
    public function getDisplaceWarehouseProduct($product, $priceLimit, $cityId) {
        //��ƷƷ��
        $brand = strip_tags($product['brand']);
        //��Ʒ�ͺ�
        $model = strip_tags($product['model']);
        //��Ʒ����
        $productName = strip_tags($product['name']);
        //����޼�
        $priceLimit = floatval($priceLimit);
        //���в���
        $cityId = intval($cityId);


        $warehouseProduct = array();
        if($brand != '' &&  $model != '' && $productName != '') {
            //��ȡ��������� + ������� > 0
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
     * �����û��ֿ��в�Ʒ������
     * @param int $warehouseProductId
     * @param int $useNum
     * @return bool
     */
    public function updateWarehouseUseNum($warehouseProductId = 0, $useNum = 1) {
        // ����Ĳ����д���
        if (intval($warehouseProductId) <= 0) {
            return false;
        }

        // ����Ҫ����
        if ($useNum == 0) {
            return true;
        }

        // �ȸ��¿�����ݣ�����ʣ��������,����Ǹ����������˻زֿ�
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