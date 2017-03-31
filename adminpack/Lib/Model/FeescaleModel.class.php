<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class FeescaleModel extends Model{
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'FEESCALE';
    
    /**��׼��ϸ״̬*/
    private $_conf_feescale_status = array(
                            'not_sub' => 1,  //δ�ύ
                            'submitted' => 2,  //���������
                            'approved' => 3,  //���ͨ��
                            'not_agree' => 4,  //���δͨ��
    );
    
    /**��׼��ϸ״̬����*/
    private  $_conf_feescale_status_remark = array(
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
    public function get_conf_feescale_status()
    {
    	return $this->_conf_feescale_status;
    }
    
    
    /**
     * ��ȡ��׼�޸���ϸ״̬��������
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_feescale_status_remark()
    {
    	return $this->_conf_feescale_status_remark;
    }
    /*
     * ������׼��ϸ
     */
    public function add_feescale_info($data)
    {
        if(is_array($data) && !empty($data))
        {
            $insertid = $this->add($data);
        }
        
        return $insertid ? $insertid : false;
    }
    
    /**
     * ���ݵ�����ID������ϸ״̬
     * @param mixed $ch_ids ������id
     * return $up_num 
     */
    public function update_info_by_ch_id($ch_ids,$update_arr)
    {
        if(is_array($ch_ids) && !empty($ch_ids))
        {
            $ch_id_str = implode(",", $ch_ids);
            $cond_where = "CH_ID IN($ch_id_str)";
        }
        else
        {
             $cond_where = "CH_ID = ".$ch_ids;
        }
        
        if(is_array($update_arr) && !empty($update_arr))
        {
            $up_num = self::update_info_by_cond($cond_where,$update_arr);
        }
        
        return $up_num ? $up_num : false;
    }
    
    /**
     * ������������
     * @param str $name Description
     * return $up_num \false
     */
    
    public function update_info_by_cond($cond_where,$update_arr)
    {
        if($cond_where && $update_arr)
        {
            $up_num = $this->where($cond_where)->save($update_arr);
        }
        return $up_num ? $up_num : false;
    }
    
    /**
     * ����idɾ����׼��ϸ
     * @param $ids mixed id
     * return $del_num �ɹ�\ɾ����������ʧ��\false
     * 
     */
    public function del_info_by_id($ids)
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
        $del_num = self::del_info_by_cond($cond_where);
        return $del_num ? $del_num : false;
    }
    
    /**
     * ��������ɾ����׼��ϸ
     * @param $cond_where  string ���� 
     * return $del_num Ӱ�������
     */
    public function del_info_by_cond($cond_where)
    {
        $del_num = false;
        $del_num = $this->where($cond_where)->delete();
        return $del_num ? $del_num : false;
    }
    
    /**
     * ���ݵ�����ID��ȡ��Ϣ
     * @param mixed $ch_ids ������id
     * return $up_num 
     */
    public function get_info_by_ch_id($ch_ids,$search_arr)
    {
        if(is_array($ch_ids) && !empty($ch_ids))
        {
            $ch_id_str = implode(",", $ch_ids);
            $cond_where = "CH_ID IN($ch_id_str)";
        }
        else
        {
             $cond_where = "CH_ID = ".$ch_ids;
        }
        $info = self::get_info_by_cond($cond_where,$search_arr);

        return $info ? $info : false;
    }
    
    /**
     * ���ݵ�������ȡ��Ϣ
     * @param mixed $ch_ids ������id
     * return $up_num 
     */
    public function get_info_by_cond($cond_where,$search_arr)
    {
        if(is_array($search_arr) && !empty($search_arr))
        {
            $info = $this->where($cond_where)->field($search_arr)->select();
        }
        else
        {
            $info = $this->where($cond_where)->select();
        }
        return $info ? $info : false;
    }
    
    
}

