<?php
/**
 * ��������
 *
 * @author 
 */

class WarehouseModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'WAREHOUSE';
    
    /***�ֿ�����״̬��������***/
    private $_conf_status_remark = array(
    							'-1' => '����˿�����',
                                '0' => 'δȷ�����',
                                '1' => 'ȷ�����'
                            );
    
    /***�ֿ�����״̬***/
    private $_conf_status = array(
    							'send_back' => '-1',
                                'not_audit' => 0,
                                'audited' => 1
                            );
    
    /***�ֿ�������Դ����***/
    private $_conf_from_remark = array(
                                1 => '���ڲɹ�',
                                2 => '�˿�'
                            );
    
    /***�ֿ�������Դ***/
    private $_conf_from = array(
                                'bulk_purchase' => 1,
                                'return_to_warehouse' => 2
                        );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * ��ȡ�ֿ�����״̬��������
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
     * ��ȡ�ֿ�����״̬
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
     * ��ȡ�ֿ�������Դ��������
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
     * ��ȡ�ֿ�������Դ����
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
     * �ɹ���ϸ�˿�
     *
     * @access	public
     * @param	array $purchase_info �ɹ���ϸ
     * @return	array
     */
    public function return_to_warehouse($purchase_info)
    {
        //�����������ز���ID
        $insertId = $this->add_warehouse_info($purchase_info);
        
        return $insertId;
    }
    
    
    /**
     * ��ӿ����Ϣ
     *
     * @access	public
     * @param	array $product_info ��Ʒ��Ϣ
     * @return	array
     */
    public function add_warehouse_info($product_info)
    {
        if(is_array($product_info) && !empty($product_info))
        {   
            // �����������ز���ID
            $insertId = $this->add($product_info);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ȷ���˿�ɹ�
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
    	 
    	//��������
    	$cond_where .= " AND STATUS = '".$status_not_audit."'";
    	
    	$update_arr['STATUS'] = $status_arr['audited'];
    	$up_num = self::update_info_by_cond($update_arr, $cond_where);
        
        return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����˿�����
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
    	
    	//��������
    	$cond_where .= " AND STATUS = '".$status_not_audit."'";
		
    	$update_arr['STATUS'] = $status_arr['send_back'];
    	$up_num = self::update_info_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
	
    
    /**
     * ���¿����������
     *
     * @access	public
     * @param	int  �����Ʒ��� 
     * @param	float  $use_mum_this_time ������������
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ����ID������Ϣ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ������Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ������ƷƷ�ࡢ���ƻ�ȡ��Ʒ�������
     *
     * @access	public
     * @param	string  $brand  Ʒ��
     * @param	string  $model  �ͺ�
     * @param	string  $product_name  ��Ʒ����
     * @param	string  $price_limit   �޼�
     * @param	int  $city_id  ���б��
     * @return	float   �������
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
     * ��ѯ�ɹ���ϸδȷ�ϵ��˿���������
     *
     * @access	public
     * @param	int  $purchase_list_id  �ɹ���ϸ���
     * @return	int  δȷ������
     */
    function get_not_confrim_num_by_pl_id($purchase_list_id)
    {
    	$not_audit = $this->_conf_status['not_audit'];
    	$cond_where = "PL_ID = '".$purchase_list_id."' AND STATUS = '".$not_audit."' ";
    	$total_num = $this->where($cond_where)->count();
    	
    	return intval($total_num);
    }
	
    
    /**
     * ���ݹؼ��ʻ�ȡ����Ŀ������õ���Ʒ������
     *
     * @access	public
     * @param	string  $brand  Ʒ��
     * @param	string  $model  �ͺ�
     * @param	string  $product_name  ��Ʒ����
     * @param	string  $price_limit   �޼�
     * @param	int     $city_id   ���б��
     * @param  array  $search_field ��Ҫ��ѯ���ֶ�
     * @return	array   ������������Ʒ��Ϣ
     */
    public function get_earliest_puroduct_info_by_search_key($brand, $model, 
            $product_name, $price_limit, $city_id ,$search_field = array())
    {   
        //��ƷƷ��
        $brand = strip_tags($brand);
        //��Ʒ�ͺ�
        $model = strip_tags($model);
        //��Ʒ����
        $product_name = strip_tags($product_name);
        //����޼�
        $price_limit = floatval($price_limit);
        //���в���
        $city_id = intval($city_id);
        
        $staus_audited = $this->_conf_status['audited'];
        $cond_where = " CITY_ID = '".$city_id."' AND BRAND = '".$brand."' AND MODEL = '".$model."' "
                . " AND PRODUCT_NAME = '".$product_name."'"
                . " AND PRICE <= '".$price_limit."' AND STATUS = '".$staus_audited."' AND NUM > USE_NUM";
        
        $product_info = $this->get_product_info_by_cond($cond_where, $search_field, 1, 'ID', 'ASC');
        
        return $product_info;
    }
    
    
    /**
     * ������ƷƷ�ࡢ���ƻ���Ʒ������
     *
     * @access	public
     * @param	string $cond_where  ��ѯ����
     * @param	array  $search_field ��ѯ�ӵ�����
     * @return	array   ������������Ʒ��Ϣ
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
     * ������ƷƷ�ࡢ���ƻ���Ʒ������
     *
     * @access	public
     * @param	string $cond_where  ��ѯ����
     * @param	array  $search_field ��ѯ�ӵ�����
     * @return	array   ������������Ʒ��Ϣ
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