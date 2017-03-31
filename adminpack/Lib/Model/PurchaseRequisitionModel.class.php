<?php
/**
 * �ɹ��������뵥MODEL��
 *
 * @author liuhu
 */
class PurchaseRequisitionModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PURCHASE_REQUISITION';
    
    /***�ɹ���״̬***/
    private  $_conf_requisition_status = array(
			                                    'not_sub' => 0,		//δ�ύ
			                                    'submitted' => 1,	//���������
			                                    'approved' => 2,	//���ͨ��
			                                    'not_agree' => 3,	//���δͨ��
			                                    'finished' => 4,	//�Ѳɹ�
    									);
    
    /***�ɹ���״̬����***/
    private  $_conf_requisition_status_remark = array(
				                                    0 => 'δ�ύ',
				                                    1 => '�����',
				                                    2 => '���ͨ��',
				                                    3 => '���δͨ��',
				                                    4 => '�ɹ����'
    											);

    /***�ɹ�����***/
    private $_conf_purchase_type = array(
							    		'project_purchase' => '1',
							    		'bulk_purchase' => '2',
    								);
    
    /***�ɹ���������***/
    private $_conf_purchase_type_remark = array(
												'1' => 'ҵ��ɹ�',
												'2' => '���ڲɹ�',
    										);
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    /**
     * ��ȡ�ɹ���״̬����
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
     * ��ȡ�ɹ���״̬��������
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
     * ��ȡ�ɹ���������
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
     * ��ȡ�ɹ�������������
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
     * ��Ӳɹ�����
     *
     * @access	public
     * @param	array  $requisition_arr ���뵥��Ϣ
     * @return	mixed  �ɹ������˿��ţ�ʧ�ܷ���FALSE
     */
    public function add_purchase_requisition($requisition_arr)
    {   
        $insertId = 0;
        if(is_array($requisition_arr) && !empty($requisition_arr))
        {   
            // �����������ز���ID
            $insertId = $this->add($requisition_arr);
        }
        
        return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ���ݲɹ����뵥ID���ύ�ɹ�����
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݲɹ����뵥���²ɹ����뵥����
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݲɹ����뵥���²ɹ����뵥����
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݲɹ��������²ɹ����뵥
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���ݲɹ�����ţ���ȡ�ɹ�����Ϣ
     *
     * @access	public
     * @param	mixed  $id �ɹ�����š�������ߵ���������ϸ��š�
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
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
     * ����������ȡ��ȡ�ɹ�����Ϣ
     *
     * @access	public
     * @param	string  $cond_where SQL����
     * @param array $search_field �����ֶ�
     * @return	array ��ѯ���
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
     * ���ݱ��ɾ���ɹ����뵥
     *
     * @access	public
     * @param	mixed  $ids ���
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
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