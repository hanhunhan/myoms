<?php
/**
 * ����ҵ��쿨�ͻ��˿���
 *
 * @author liuhu
 */

class MemberRefundModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    private $table_list = 'MEMBER_REFUND_LIST';
    private $table_detail = 'MEMBER_REFUND_DETAIL';
    
    
    /***�˿״̬***/
    private  $_conf_refund_list_status = array(
                                        'refund_list_no_sub' => 0,	//δ�ύ
                                        'refund_list_sub' => 1,		//���ύ
                                        'refund_list_stop' => 2,     //����˿�
                                        'refund_list_completed' => 3, //�˿����
                                    );
    
    /***�˿״̬����***/
    private $_conf_refund_list_status_remark = array(
                                            0 => 'δ�ύ���',
                                            1 => '���ύ���',
                                            2 => '����˿�',
                                            3 => '�˿����'
                                    );
    
    
    /***�˿���ϸ�˿�״̬***/
    private  $_conf_refund_status = array(
                                    'refund_no_sub' => 0,	//δ�ύ
                                    'refund_audit' => 1,	//������˵�
                                    'refund_apply' => 2,	//�ύ�����
                                    'refund_stop' => 3,     //��ֹ�˿�
                                    'refund_success' => 4,	//�ɹ��˿�
                                    'refund_delete' => 5,     //ɾ���˿�
                                    'refund_received' => 6    //�˿��
    							);
    
    /***�˿���ϸ�˿�״̬***/
    private  $_conf_refund_status_remark = array(
                                            0 => 'δ�ύ',
                                            1 => '������˵�',
                                            2 => '�ύ�����',
                                            3 => '��ֹ�˿�',
                                            4 => '�ɹ��˿�',
                                            5 => 'ɾ���˿�',
                                            /*6 => '�˿��',*/
                                        );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
         
    /**
     * ��ȡ�˿״̬��������
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_refund_list_status_remark()
    {
    	return $this->_conf_refund_list_status_remark;
    }
    
    
    /**
     * ��ȡ�˿״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_refund_list_status()
    {
    	return $this->_conf_refund_list_status;
    }
    
    
    /**
     * ��ȡ�˿���ϸ״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_refund_status()
    {
    	return $this->_conf_refund_status;
    }
    
    
    /**
     * ��ȡ�˿���ϸ״̬���鱸ע
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_refund_status_remark()
    {
    	return $this->_conf_refund_status_remark;
    }
    
    
    //��ȡ�˿����
    public function get_list_table_name()
    {   
        return $this->tablePrefix.$this->table_list;
    }
    
    
    //��ȡ�˿���ϸ����
    public function get_detail_table_name()
    {   
        return $this->tablePrefix.$this->table_detail;
    }
    
	
    /**
     * ����˿��Ϣ
     *
     * @access	private
     * @param	array  $refund_arr �˿���
     * @return	mixed  �ɹ������˿��ţ�ʧ�ܷ���FALSE
     */
    public function add_refund_list($refund_arr)
    {   
        $insertId = 0;
        if(is_array($refund_arr) && !empty($refund_arr))
        {   
            // �����������ز���ID
            $options['table'] = $this->get_list_table_name();
            $insertId = $this->add($refund_arr, $options);
        }
        
        return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ����˿���ϸ
     *
     * @access	private
     * @param	array  $refund_arr �˿���Ϣ
     * @return	mixed  �ɹ������˿��ţ�ʧ�ܷ���FALSE
     */
    public function add_refund_details($refund_arr)
    {
        $insertId = 0;
        
        if(is_array($refund_arr) && !empty($refund_arr))
        {   
            // �����������ز���ID
            $options['table'] = $this->get_detail_table_name();
            $insertId = $this->add($refund_arr, $options);
        }
        
        return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * �����˿���ϸIDɾ���˿���ϸ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
     */
    public function del_refund_detail_by_id($ids)
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

    	$up_num = self::del_refund_detail_by_cond($cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ��������ɾ���˿���ϸ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
     */
    public function del_refund_detail_by_cond($cond_where)
    {
        $up_num = 0;
        
    	if($cond_where != '')
    	{	
            $update_arr = array();
            $update_arr['REFUND_STATUS'] = intval($this->_conf_refund_status['refund_delete']);
    		$up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����˿���ϸ��¼���˿���˵�
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function add_details_to_audit_list($ids, $list_id)
    {
    	$cond_where = "";
        $list_id = intval($list_id);
        
        if($list_id > 0 && !empty($ids))
        {
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
            
            $no_sub_status  = $this->_conf_refund_status['refund_no_sub'];
            $cond_where .= " AND REFUND_STATUS = '".$no_sub_status."'";
            
            $update_arr['LIST_ID'] =  $list_id;
            $update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
            $update_arr['REFUND_STATUS'] = $this->_conf_refund_status['refund_audit'];
            $up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
        }
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * �����˿����뵥����ύ�˿����뵽���������
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_refund_detail_to_apply($list_id)
    {
    	$list_id = intval($list_id);
    
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$audit_status  = $this->_conf_refund_status['refund_audit'];
    		$cond_where .= " AND REFUND_STATUS = '".$audit_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['REFUND_STATUS'] = $this->_conf_refund_status['refund_apply'];
            
    		$up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * �����˿����뵥����ֹ״̬
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_refund_detail_to_stop($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$apply_status  = $this->_conf_refund_status['refund_apply'];
    		$cond_where .= " AND REFUND_STATUS = '".$apply_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['REFUND_STATUS'] = $this->_conf_refund_status['refund_stop'];
            
    		$up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * �����˿����뵥�����״̬
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_refund_detail_to_success($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$apply_status  = $this->_conf_refund_status['refund_apply'];
    		$cond_where .= " AND REFUND_STATUS = '".$apply_status."'";
            
            $update_arr['CONFIRMTIME'] =  date('Y-m-d H:i:s');
    		$update_arr['REFUND_STATUS'] = $this->_conf_refund_status['refund_success'];
            
    		$up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ɾ���˿���ϸ���˿����뵥֮���ϵ���˳��˿����뵥��
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function delete_details_from_audit_list($ids)
    {
    	$cond_where = "";
        
        if(!empty($ids))
        {
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
            
            $no_sub_status  = $this->_conf_refund_status['refund_no_sub'];
            $update_arr['LIST_ID'] =  '';
            $update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
            $update_arr['REFUND_STATUS'] = $no_sub_status;
            $up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
        }
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * �����˿���ϸID�����˿���ϸ��Ϣ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_refund_detail_by_id($ids, $update_arr)
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

    	$up_num = self::update_refund_detail_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����ָ�����������˿���ϸ��Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_refund_detail_by_cond($update_arr, $cond_where)
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
     * ����ָ���˿����뵥ID�ύ�˿����뵥�����������״̬
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_refund_list_to_apply($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$no_sub_status  = $this->_conf_refund_list_status['refund_list_no_sub'];
    		$cond_where .= " AND STATUS = '".$no_sub_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_refund_list_status['refund_list_sub'];
    		$up_num = self::update_refund_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * �����˿����뵥�����״̬
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_refund_list_to_completed($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$sub_status  = $this->_conf_refund_list_status['refund_list_sub'];
    		$cond_where .= " AND STATUS = '".$sub_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_refund_list_status['refund_list_completed'];
    		$up_num = self::update_refund_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * �����˿����뵥����ֹ״̬
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_refund_list_to_stop($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$sub_status  = $this->_conf_refund_list_status['refund_list_sub'];
    		$cond_where .= " AND STATUS = '".$sub_status."'";
    		
    		$update_arr['UPDATETIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_refund_list_status['refund_list_stop'];
            
    		$up_num = self::update_refund_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����ָ��ID�����˿����뵥��Ϣ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_refund_list_by_id($ids, $update_arr)
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

    	$up_num = self::update_refund_list_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����ָ�����������˿����뵥��Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_refund_list_by_cond($update_arr, $cond_where)
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
     * ���ݱ�Ż�ȡ�˿��Ϣ
     *
     * @access	public
     * @param	int  $list_id �˿��
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_refund_list_by_id($list_id, $search_field = array())
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
     * ����������ȡ�˿��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_refund_list_by_cond($cond_where, $search_field = array())
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
     * ��ȡ����һ���˿��¼
     *
     * @access	public
     * @param int $add_uid �����û�
     * @param int $city_id ���б��
     * @param int $status ״̬
     * @return	array ��ѯ���
     */
    public function get_last_refund_list($add_uid, $city_id, $status = 0)
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
     * ���ݱ�Ż�ȡ�˿���ϸ��Ϣ
     *
     * @access	public
     * @param	int  $list_id �˿��
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_refund_detail_by_listid($list_id, $search_field = array())
    {
        $info = array();
        
        $list_id = intval($list_id);
        
        if($list_id <= 0)
        {
            return $info;
        }
        
        //��ѯ����
        $cond_where = " LIST_ID = '".$list_id."'";
        $info = $this->get_refund_detail_by_cond($cond_where, $search_field );
        
        return $info;
    }
    
    
    /**
     * ���ݱ�Ż�ȡ�˿���ϸ��Ϣ
     *
     * @access	public
     * @param	int  $id �˿��
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_refund_detail_by_id($id, $search_field = array())
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
     * ����������ȡ�˿���ϸ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_refund_detail_by_cond($cond_where, $search_field = array())
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
}

/* End of file MemberRefundModel.class.php */
/* Location: ./Lib/Model/MemberRefundModel.class.php */