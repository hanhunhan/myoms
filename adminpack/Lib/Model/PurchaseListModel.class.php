<?php
/**
 * �ɹ���ϸMODEL
 *
 * @author liuhu
 */
class PurchaseListModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PURCHASE_LIST';

    /***�ɹ���ϸ״̬***/
    private $_conf_list_status = array(
        'not_purchased' => 0,   //δ�ɹ�
        'purchased' => 1,       //�Ѳɹ�
        'reimbursed' => 2,      //�ѱ���
        'in_warehouse' => 3,    //�����
        'reimbursing' => 4,  // �������뱨��
    );
    
    /***�ɹ���ϸ״̬����***/
    private $_conf_list_status_remark = array(
        0 => 'δ�ɹ�',
        1 => '�Ѳɹ�',
        2 => '�ѱ���',
        3 => '�����',
        4 => '���뱨����'
    );
    
    /***�ɹ���ϸ�˿�״̬***/
    private $_conf_back_stock_status = array(
                                            'not_apply' => 0,   //δ����
                                            'applied' => 1,     //������
                                            'send_back' => 2	//������
                                            );
    
    /***�ɹ���ϸ�˿�״̬***/
    private $_conf_back_stock_status_remark = array(
                                            0 => 'δ����',
                                            1 => '������',
    										2 => '������',
                                            );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * ��ȡ�ɹ���ϸ״̬����
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
     * ��ȡ�ɹ���ϸ״̬��������
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
     * ��ȡ�ɹ���ϸ�˿�״̬����
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
     * ��ȡ�ɹ���ϸ�˿�״̬��������
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
     * ��Ӳɹ���ϸ�嵥
     *
     * @access	public
     * @param	array  $purchase_arr �˿���Ϣ
     * @return	mixed  �ɹ������˿��ţ�ʧ�ܷ���FALSE
     */
    public function add_purchase_list($purchase_arr)
    {
        $insertId = 0;
        
        if(is_array($purchase_arr) && !empty($purchase_arr))
        {   
            // �����������ز���ID
            $insertId = $this->add($purchase_arr);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ���ݲɹ���ϸ��Ÿ��²ɹ���ϸΪ�Ѳɹ�
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݲɹ���ϸ��Ÿ��²ɹ���ϸΪ�ѱ���
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݲɹ���ϸ��Ÿ��²ɹ���ϸΪ�����
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݲɹ���ϸ��Ÿ��²ɹ���ϸΪ�����˿���
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݲɹ���ϸ��Ÿ��²ɹ���ϸ�˿�״̬Ϊ���
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݲɹ���ϸ��Ÿ��²ɹ���ϸ�˿�����
     *
     * @access	public
     * @param	mixed  $id ����ID
     * @param	int  $stock_num �˿�����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * �ɹ���ϸ��ӵ���ͬ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $contract_id ��ͬ���
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * �ɹ���ϸ����ͬȡ���ҿ���ϵ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݲɹ���ϸ��Ÿ��²ɹ���ϸ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ������Ŀ����
     * @param $purchaseInfo
     * @return bool
     */
    public function insertDisplaceIncome($purchaseInfo){
        if (notEmptyArray($purchaseInfo)) {
            $useInfo = D('WarehouseUse')->getDisplaceUseByPurchaseId($purchaseInfo['DETAIL_ID']);
            if(notEmptyArray($useInfo)){
                foreach($useInfo as $useInfoItem) {
                    //������Ŀ����
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
     * ���ݲɹ��������²ɹ���ϸ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݲɹ�����ţ���ȡ�ɹ���ϸ��Ϣ
     *
     * @access	public
     * @param	mixed  $id �ɹ�����š�������ߵ���������ϸ��š�
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
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
     * ���ݲɹ�����ţ���ȡ�ɹ���ϸ��Ϣ
     *
     * @access	public
     * @param	mixed  $id �ɹ�����š�������ߵ���������ϸ��š�
     * @return	array ��ѯ���
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
     * ���ݲɹ���ϸ��ţ���ȡ�ɹ���ϸ��Ϣ
     *
     * @access	public
     * @param	mixed  $id �ɹ�����š�������ߵ���������ϸ��š�
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
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
     * ���ݺ�ͬ��ţ���ȡ�ɹ���ϸ��Ϣ
     *
     * @access	public
     * @param	mixed  $id �ɹ���ͬ��š�������ߵ���������ϸ��š�
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
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
     * ���ݲɹ����뵥��ѯ�ɹ���ϸ����
     *
     * @access	public
     * @param	int  $pid �ɹ����뵥���
     * @return	array ��ѯ���
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
     * ����������ȡ��ȡ�ɹ���ϸ��Ϣ
     *
     * @access	public
     * @param	string  $brand  Ʒ��
     * @param	string  $model  �ͺ�
     * @param	string  $product_name  ��Ʒ����
     * @param   int    $city_id ���в���
     * @param	int     $limit  ����
     * @param	int     $offset ƫ����
     * @return	array ��ѯ���
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
     * ����������ȡ��ȡ�ɹ���ϸ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
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
     * ���ݲɹ�����Ų�ѯ�ɹ��������вɹ���ϸ�Ƿ�ȫ���ɹ����
     *
     * @access	public
     * @param	int  $pr_id �ɹ����뵥���
     * @return	boolean TRUE��ȫ���ɹ���FALSE��δȫ���ɹ�
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
     * ���ݱ��ɾ���ɹ�������ϸ
     *
     * @access	public
     * @param	mixed  $pr_ids �ɹ������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݱ��ɾ���ɹ�������ϸ
     *
     * @access	public
     * @param	mixed  $ids �ɹ���ϸ���
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
            throw_exception("��ѯ{$this->tableName}��, ID����Ϊ��");
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
     * ��ȡ�ɹ�����
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
     * ��ȡ�ɹ���ϸ���������
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
                    if($v['TYPE']==1) { //�ɹ��ֿ�
                        $response['warehouse_total_num'] += intval($v['USE_NUM']);
                    }else if($v['TYPE']==2){ //�û��ֿ�
                        $response['displace_ware_total_num'] += intval($v['USE_NUM']);
                    }
                }

                $response['total_num'] = $response['warehouse_total_num'] + $response['displace_ware_total_num'];
                // �������˰��
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
                $response['fee'] = $dbResult[0]['FEE'];  // �����ܶ�
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