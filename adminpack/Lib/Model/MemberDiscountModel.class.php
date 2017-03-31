<?php
/**
 * ����ҵ��쿨�ͻ�������
 *
 * @author liuhu
 */
class MemberDiscountModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    private $table_list = 'MEMBER_DISCOUNT_LIST';
    private $table_detail = 'MEMBER_DISCOUNT_DETAIL';
    
    
    /***���ⵥ״̬***/
    private  $_conf_discount_list_status = array(
    		'discount_list_no_sub' => 1,	//δ�ύ
    		'discount_list_sub' => 2,		//���ύ
    		'discount_list_stop' => 3,     //�������
    		'discount_list_completed' => 4, //�������
    );
    
     /***���ⵥ״̬����***/
    private $_conf_discount_list_status_remark = array(
                                            1 => 'δ�ύ���',
                                            2 => '���ύ���',
                                            3 => '����ͬ��',
                                            4 => '�������'
                                    );
    
    
    /***������ϸ����״̬***/
    private  $_conf_discount_status = array(
                                    'discount_no_sub' => 1,	//δ�ύ
                                    'discount_audit' => 2,	//������˵�
                                    'discount_apply' => 3,	//�ύ�����
                                    'discount_stop' => 4,     //��ֹ����
                                    'discount_success' => 5,	//�ɹ�����
                                    'discount_delete' => 6,     //ɾ������
                                    'discount_received' => 7    //���⵽��
    							);
    
    /***������ϸ״̬***/
    private  $_conf_discount_status_remark = array(
                                            1 => 'δ�ύ',
                                            2 => '������˵�',
                                            3 => '�ύ�����',
                                            4 => '��ֹ����',
                                            5 => '����ͨ��',
                                            6 => 'ɾ������',
                                            7 => '���⵽��',
                                        );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
            
    /**
     * ��ȡ���ⵥ״̬��������
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
     * ��ȡ���ⵥ״̬����
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
     * ��ȡ������ϸ״̬����
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
     * ��ȡ������ϸ״̬���鱸ע
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
     * ��Ӽ��ⵥ��Ϣ
     *
     * @access	private
     * @param	array  $discount_arr ���ⵥ���
     * @return	mixed  �ɹ����ؼ��ⵥ��ţ�ʧ�ܷ���FALSE
     */
    public function add_discount_list($discount_arr)
    {   
        $insertId = 0;
        if(is_array($discount_arr) && !empty($discount_arr))
        {   
            // �����������ز���ID
            $options['table'] = $this->tablePrefix.$this->table_list;
            $insertId = $this->add($discount_arr, $options);
        }
        
        return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ��Ӽ�����ϸ
     *
     * @access	private
     * @param	array  $discount_arr ������Ϣ
     * @return	mixed  �ɹ����ؼ��ⵥ��ţ�ʧ�ܷ���FALSE
     */
    public function add_discount_details($discount_arr)
    {
        $insertId = 0;
        
        if(is_array($discount_arr) && !empty($discount_arr))
        {   
            // �����������ز���ID
            $options['table'] = $this->tablePrefix.$this->table_detail;
            $insertId = $this->add($discount_arr, $options);
        }
        //echo $this->_sql();
        return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ���ݼ�����ϸIDɾ��������ϸ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
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
     * ��������ɾ��������ϸ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
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
     * ���ݼ������뵥����ύ�������뵽���������
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_discount_detail_to_apply($list_id)
    {
    	$list_id = intval($list_id);
    
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$audit_status  = $this->_conf_discount_status['discount_audit'];
    		$cond_where .= " AND REFUND_STATUS = '".$audit_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['REFUND_STATUS'] = $this->_conf_discount_status['discount_apply'];
            
    		$up_num = self::update_discount_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    
    
    
    
    
    /**
     * ���ݼ�����ϸID���¼�����ϸ��Ϣ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ����ָ���������¼�����ϸ��Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ����ָ��ID���¼������뵥��Ϣ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ����ָ���������¼������뵥��Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݱ�Ż�ȡ���ⵥ��Ϣ
     *
     * @access	public
     * @param	int  $list_id ���ⵥ��
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_discount_list_by_id($list_id, $search_field = array())
    {
        $info = array();
        
        $list_id = intval($list_id);
        
        if($list_id <= 0)
        {
            return $info;
        }
        
        //��ѯ����
        $cond_where = " ID = '".$list_id."'";
        
        //��ѯ����
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
     * ����������ȡ���ⵥ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
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
     * ��ȡ����һ�����ⵥ��¼
     *
     * @access	public
     * @param int $add_uid �����û�
     * @param int $city_id ���б��
     * @param int $status ״̬
     * @return	array ��ѯ���
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
     * ���ݱ�Ż�ȡ������ϸ��Ϣ
     *
     * @access	public
     * @param	int  $id ���ⵥ��
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_discount_detail_by_id($id, $search_field = array())
    {
        $info = array();
        
        $id = intval($id);
        
        if($id <= 0)
        {
            return $info;
        }
        
        //��ѯ����
        $cond_where = " ID = '".$id."'";
        
        //��ѯ����
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
     * ����������ȡ������ϸ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
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