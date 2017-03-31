<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * ��Ա��Ʊmodel
 */
class InvoiceRecycleModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    private $table_list = 'INVOICE_RECYCLE_LIST';
    private $table_detail = 'INVOICE_RECYCLE_DETAIL';
    
    
    /***��Ʊ��״̬***/
    private  $_conf_invoice_recycle_list_status = array(
    		'invoice_recycle_list_no_sub' => 1,	//δ�ύ
    		'invoice_recycle_list_sub' => 2,		//���ύ
    		'invoice_recycle_list_stop' => 3,     //�����Ʊ
    		'invoice_recycle_list_completed' => 4, //��Ʊ���
    );
    
     /***��Ʊ��״̬����***/
    private $_conf_invoice_recycle_list_status_remark = array(
                                            1 => 'δ�ύ���',
                                            2 => '���ύ���',
                                            3 => '�����Ʊ',
                                            4 => '��Ʊ���'
                                    );
    
    
    /***��Ʊ��ϸ��Ʊ״̬***/
    private  $_conf_invoice_recycle_detail_status = array(
                                    'invoice_recycle_no_sub' => 1,	//δ�ύ
                                    'invoice_recycle_audit' => 2,	//������˵�
                                    'invoice_recycle_apply' => 3,	//�ύ�����
                                    'invoice_recycle_stop' => 4,     //��ֹ��Ʊ
                                    'invoice_recycle_success' => 5,	//�ɹ���Ʊ
                                    'invoice_recycle_delete' => 6,     //ɾ����Ʊ
                                    'invoice_recycle_received' =>7    //��Ʊ����
                                    
    							);
    
    /***��Ʊ��ϸ��Ʊ״̬***/
    private  $_conf_invoice_recycle_detail_status_remark = array(
                                            1 => 'δ�ύ',
                                            2 => '������˵�',
                                            3 => '�ύ�����',
                                            4 => '��ֹ��Ʊ',
                                            5 => '�ɹ���Ʊ',
                                            6 => 'ɾ����Ʊ',
                                            7 => '��Ʊ����',
                                            
                                        );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    /**
     * ��ȡ��Ʊ��״̬��������
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_invoice_recycle_list_status_remark()
    {
    	return $this->_conf_invoice_recycle_list_status_remark;
    }
    
    /**
     * ��ȡ��Ʊ��״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_invoice_recycle_list_status()
    {
    	return $this->_conf_invoice_recycle_list_status;
    }
    
    
    /**
     * ��ȡ��Ʊ��ϸ״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_invoice_recycle_status()
    {
    	return $this->_conf_invoice_recycle_detail_status;
    }
    
    
    /**
     * ��ȡ��Ʊ��ϸ״̬���鱸ע
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_invoice_recycle_status_remark()
    {
    	return $this->_conf_invoice_recycle_detail_status_remark;
    }
    
    
    //��ȡ��Ʊ������
    public function get_list_table_name()
    {   
        return $this->tablePrefix.$this->table_list;
    }
    
    
    //��ȡ��Ʊ��ϸ����
    public function get_detail_table_name()
    {   
        return $this->tablePrefix.$this->table_detail;
    }
    
	
    /**
     * �����Ʊ����Ϣ
     *
     * @access	private
     * @param	array  $invoice_recycle_arr ��Ʊ�����
     * @return	mixed  �ɹ�������Ʊ����ţ�ʧ�ܷ���FALSE
     */
    public function add_invoice_recycle_list($invoice_recycle_arr)
    {   
        $insertId = 0;
        if(is_array($invoice_recycle_arr) && !empty($invoice_recycle_arr))
        {   
            // �����������ز���ID
            $options['table'] = $this->get_list_table_name();
            $insertId = $this->add($invoice_recycle_arr, $options);
        }
        
        return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * �����Ʊ��ϸ
     *
     * @access	private
     * @param	array  $invoice_recycle_arr ��Ʊ��Ϣ
     * @return	mixed  �ɹ�������Ʊ����ţ�ʧ�ܷ���FALSE
     */
    public function add_invoice_recycle_details($invoice_recycle_arr)
    {
        $insertId = 0;
        
        if(is_array($invoice_recycle_arr) && !empty($invoice_recycle_arr))
        {   
            // �����������ز���ID
            $options['table'] = $this->get_detail_table_name();
            $insertId = $this->add($invoice_recycle_arr, $options);
        }
        
        return $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ������Ʊ��ϸIDɾ����Ʊ��ϸ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
     */
    public function del_invoice_recycle_detail_by_id($ids)
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

    	$up_num = self::del_invoice_recycle_detail_by_cond($cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ��������ɾ����Ʊ��ϸ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @return	mixed  �ɹ�����ɾ��������ʧ�ܷ���FALSE
     */
    public function del_invoice_recycle_detail_by_cond($cond_where)
    {
        $up_num = 0;
        
    	if($cond_where != '')
    	{	
            $update_arr = array();
            $update_arr['STATUS'] = intval($this->_conf_invoice_recycle_detail_status['invoice_recycle_delete']);
    		$up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * �����Ʊ��ϸ��¼����Ʊ��˵�
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
            
            $no_sub_status  = $this->_conf_invoice_recycle_detail_status['invoice_recycle_no_sub'];
            $cond_where .= " AND STATUS = '".$no_sub_status."'";
            
            $update_arr['LIST_ID'] =  $list_id;
            $update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
            $update_arr['STATUS'] = $this->_conf_invoice_recycle_detail_status['invoice_recycle_audit'];
            $up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
        }
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
        
    /**
     * ������Ʊ���뵥����ύ��Ʊ���뵽���������
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_invoice_recycle_detail_to_apply($list_id)
    {
    	$list_id = intval($list_id);
    
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$audit_status  = $this->_conf_invoice_recycle_detail_status['invoice_recycle_audit'];
    		$cond_where .= " AND STATUS = '".$audit_status."'";
    		
    		$update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_invoice_recycle_detail_status['invoice_recycle_apply'];
            
    		$up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ������Ʊ���뵥����ֹ״̬
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_invoice_recycle_detail_to_stop($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$audit_status  = $this->_conf_invoice_recycle_detail_status['invoice_recycle_audit'];
    		$cond_where .= " AND STATUS = '".$audit_status."'";
    		
    		//$update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_invoice_recycle_detail_status['invoice_recycle_stop'];
            
    		$up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ������Ʊ��ϸ�������״̬
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_invoice_recycle_detail_to_success($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " LIST_ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$apply_status  = $this->_conf_invoice_recycle_detail_status['invoice_recycle_apply'];
    		$cond_where .= " AND STATUS = '".$apply_status."'";
            
            $update_arr['CONFIRMTIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_invoice_recycle_detail_status['invoice_recycle_success'];
            
    		$up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    
    /**
     * ɾ����Ʊ��ϸ����Ʊ���뵥֮���ϵ���˳���Ʊ���뵥��
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
            
            $no_sub_status  = $this->_conf_invoice_recycle_detail_status['invoice_recycle_no_sub'];
            $update_arr['LIST_ID'] =  '';
            //$update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
            $update_arr['STATUS'] = $no_sub_status;
            $up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
        }
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ������Ʊ��ϸID������Ʊ��ϸ��Ϣ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_invoice_recycle_detail_by_id($ids, $update_arr)
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

    	$up_num = self::update_invoice_recycle_detail_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����ָ������������Ʊ��ϸ��Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_invoice_recycle_detail_by_cond($update_arr, $cond_where)
    {	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$options['table'] = $this->get_detail_table_name();
    		$up_num = $this->where($cond_where)->save($update_arr, $options);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����ָ����Ʊ���뵥ID�ύ��Ʊ���뵥�����������״̬
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_invoice_recycle_list_to_apply($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$no_sub_status  = $this->_conf_invoice_recycle_list_status['invoice_recycle_list_no_sub'];
    		$cond_where .= " AND STATUS = '".$no_sub_status."'";
    		
    		$update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_invoice_recycle_list_status['invoice_recycle_list_sub'];
    		$up_num = self::update_invoice_recycle_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ������Ʊ���뵥�����״̬
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_invoice_recycle_list_to_completed($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$sub_status  = $this->_conf_invoice_recycle_list_status['invoice_recycle_list_sub'];
    		$cond_where .= " AND STATUS = '".$sub_status."'";
    		
    		//$update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_invoice_recycle_list_status['invoice_recycle_list_completed'];
    		$up_num = self::update_invoice_recycle_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ������Ʊ���뵥����ֹ״̬
     *
     * @access	public
     * @param	$int  $list_id ��˵����
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function sub_invoice_recycle_list_to_stop($list_id)
    {
        $list_id = intval($list_id);
        
    	if($list_id > 0)
    	{	
    		$cond_where = " ID = '".$list_id."'";
    		
    		//���ύ��˵�״̬
    		$sub_status  = $this->_conf_invoice_recycle_list_status['invoice_recycle_list_sub'];
    		$cond_where .= " AND STATUS = ".$sub_status;
    		
    		//$update_arr['APPLY_TIME'] =  date('Y-m-d H:i:s');
    		$update_arr['STATUS'] = $this->_conf_invoice_recycle_list_status['invoice_recycle_list_stop'];
    		$up_num = self::update_invoice_recycle_list_by_cond($update_arr, $cond_where);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����ָ��ID������Ʊ���뵥��Ϣ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_invoice_recycle_list_by_id($ids, $update_arr)
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

    	$up_num = self::update_invoice_recycle_list_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ����ָ������������Ʊ���뵥��Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_invoice_recycle_list_by_cond($update_arr, $cond_where)
    {	
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{	
    		$options['table'] = self::get_list_table_name();
    		$up_num = M("Erp_invoice_recycle_list")->where($cond_where)->save($update_arr);
//            echo M("Erp_invoice_recycle_list")->getLastSql();
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ���ݱ�Ż�ȡ��Ʊ����Ϣ
     *
     * @access	public
     * @param	int  $list_id ��Ʊ����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_invoice_recycle_list_by_id($list_id, $search_field = array())
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
     * ����������ȡ��Ʊ����Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_invoice_recycle_list_by_cond($cond_where, $search_field = array())
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
     * ��ȡ����һ����Ʊ����¼
     *
     * @access	public
     * @param int $add_uid �����û�
     * @param int $city_id ���б��
     * @param int $status ״̬
     * @return	array ��ѯ���
     */
    public function get_last_invoice_recycle_list($add_uid, $city_id, $status = 1)
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
        $cond_where = "APPLY_USER = '".$add_uid."' AND STATUS = '".$status."' AND CITY_ID = '".$city_id."' ";
        $info = $this->table($list_table_name)->where($cond_where)->order('ID DESC')->find();
        //echo $this->getLastSql();
        return $info;
    }
    
    /**
     * ������Ʊ����Ż�ȡ��Ʊ��ϸ��Ϣ
     *
     * @access	public
     * @param	int  $list_id ��Ʊ����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_invoice_recycle_detail_by_listid($list_id, $search_field = array())
    {
        $info = array();
        
        $list_id = intval($list_id);
        
        if($list_id <= 0)
        {
            return $info;
        }
        
        //��ѯ����
        $cond_where = " LIST_ID = '".$list_id."'";
        $info = $this->get_invoice_recycle_detail_info_by_cond($cond_where, $search_field );
        
        return $info;
    }
    
    
    /**
     * ���ݱ�Ż�ȡ��Ʊ��ϸ��Ϣ
     *
     * @access	public
     * @param	int  $id ��Ʊ����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_invoice_recycle_detail_info_by_id($id, $search_field = array())
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
     * ����������ȡ��Ʊ��ϸ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
     */
    public function get_invoice_recycle_detail_info_by_cond($cond_where, $search_field = array())
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

