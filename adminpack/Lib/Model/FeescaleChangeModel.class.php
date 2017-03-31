<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FeescaleChangeModel extends Model{
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'FEESCALE_CHANGE';
      /***��׼�޸ĵ�״̬***/
    private  $_conf_requisition_status = array(
                                    'not_sub' => 1,  //δ�ύ
                                    'submitted' => 2,  //���������
                                    'approved' => 3,  //���ͨ��
                                    'not_agree' => 4,  //���δͨ�� 
    							);
     /***��׼�޸ĵ�״̬����***/
    private  $_conf_requisition_status_remark = array(
                                    '1' => 'δ�ύ',
                                    '2' => '���������',
                                    '3' => '���ͨ��',
                                    '4' => '���δͨ��',
                                    
    							);
    
    
      //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
       /**
     * ��ȡ��׼�޸ĵ�״̬����
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
     * ��ȡ��׼�޸ĵ�״̬��������
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
     * ������׼��������
     * @param  array $data  �������ݼ�ֵ��
     *  return  int   $insertid ��������
     */
    public function add_standard_adjustment($data)
    {
        $insertid = 0;
        if(is_array($data) && !empty($data))
        {
            $table["option"] = $this->tablePrefix.$this->tableName;
            $insertid = $this->table($table["option"])->add($data);
        }
        return $insertid;
    }
    
    
    /**
     * ���ݱ�׼�������뵥ID���ύ��׼��������
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function submit_standardadjust_by_id($ids)
    {	
    	$up_num = 0;    	
    	$update_arr = array();
    	$status = intval($this->_conf_requisition_status['submitted']);
    	$update_arr['STATUS'] = $status;
    	$up_num = $this->update_standardadjust_by_id($ids, $update_arr);
    	
    	return $up_num > 0 ? $up_num :FALSE;
    }
    
    /**
     * ���ݱ�׼�������뵥���±�׼�������뵥����
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_standardadjust_by_id($ids, $update_arr)
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
    
    	$up_num = self::update_standardadjust_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * ���ݱ�׼�����������±�׼�������뵥
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
     */
    public function update_standardadjust_by_cond($update_arr, $cond_where)
    {
    	$up_num = 0;
    	if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
    	{
    		$up_num = $this->where($cond_where)->save($update_arr);
    	}
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
     /**
     * ���ݱ�׼����ID 
     *
     * @access	public
     * @param	int  $id  id
     * @param	array  ��ѯ�ֶ�
     * @return	$info
     */
    public function get_info_by_ids($id,$field = "*"){
        $where = "ID = $id";
        $info = $this->field($field)->where($where)->find();
        if($info){
            return $info;
        }else{
            return false;
        }
       
    }
    
    /**
     * ����idɾ�����뵥
     * @param $ids mixed id
     * return $del_num �ɹ�\ɾ����������ʧ��\false
     * 
     */
    public function del_feescale_change_by_id($ids)
    {
        $del_num = "";
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }
        $del_num = self::del_feescale_change_by_cond($cond_where);
        return $del_num ? $del_num : false;
    }
    
    /**
     * ��������ɾ�����뵥
     * @param $cond_where  string ���� 
     * return $del_num Ӱ�������
     */
    public function del_feescale_change_by_cond($cond_where)
    {
        //$table["option"] = $this->tablePrefix.$this->tableName;
        $del_num = false;
        $del_num = $this->where($cond_where)->delete();
        return $del_num ? $del_num : false;
    }
}
