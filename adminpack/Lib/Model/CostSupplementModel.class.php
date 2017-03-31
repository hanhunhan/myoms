<?php

/**
 * �ɱ����MODEL
 *
 * @author liuhu
 */
class CostSupplementModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'COST_SUPPLEMENT';
    
    //״̬��ʶ
    private $_conf_cost_supplement_status_remark = array(
                    "1" => "δ����",
                    "2" => "������",
                    "3" => "�ѱ���"
        );
    
    //״̬
    private $_conf_cost_supplement_status = array(
                    "no_apply"   => 1,              //δ����
                    "appling"    => 2,              //������
                    "have_reim"  => 3               //�ѱ���
    );
    
    //�ɱ�������ͱ�ʶ
    private $_conf_cost_sup_type_remark = array(
                    "1"   => "��ɱ����",
                    "2"   => "���̳ɱ����",
    );
    
    //�ɱ��������
    private $_conf_cost_sup_type = array(
                    "active_cost" => 1,
                    "ds_cost"     => 2,
    );
    

    //���캯��
    public function __construct($name = '') {
        parent::__construct($name);
    }
    
    /**
     * ��ȡ״̬��ʶ
     * @return 
     */
    public function get_cost_supplement_status_remark(){
        return $this->_conf_cost_supplement_status_remark;
    }
    
    /**
     * ��ȡ״̬
     * @return 
     */
    public function get_cost_supplement_status(){
        return $this->_conf_cost_supplement_status;
    }

    public function get_cost_sup_type(){
        return $this->_conf_cost_sup_type;
    }
    
     public function get_cost_sup_type_remark(){
        return $this->_conf_cost_sup_type_remark;
    }

    /**
     *�����ɱ������Ϣ 
     * @param $data array() �ֶμ�ֵ��
     * return int $insertid �ɹ���������id \ʧ�ܣ�false
     */
    public function add_cost_supplement_info($data)
    {
        $insertid = false;
        if(is_array($data) && !empty($data))
        {
            $insertid = $this->add($data);
        }
        return $insertid ? $insertid : false;
    }
    
    /**
     * ����ID������Ϣ
     * @param mixed $ids ����Id������
     * @update_arr array() Ҫ���µ��ֶ�
     * return $up_num �ɹ���Ӱ��ļ�¼�� \ ʧ�ܣ�false
     */
    public function update_cost_supplement_info_by_ids($ids,$update_arr)
    {
        $up_num = "";
        $cond_where = "";
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }
        
        if(is_array($update_arr) && !empty($update_arr))
        {
            $up_num = self::update_cost_supplement_info_by_cond($cond_where,$update_arr);
        }
        return $up_num ? $up_num : false;
    }
    
    /**
     * ��������������Ϣ
     * @param array() $cond_where ����
     * @update_arr array() Ҫ���µ��ֶ�
     * return $up_num �ɹ���Ӱ��ļ�¼�� \ ʧ�ܣ�false
     */
    public function update_cost_supplement_info_by_cond($cond_where,$update_arr)
    {
        $up_num = "";
        if($cond_where && is_array($update_arr) && !empty($update_arr))
        {
            $up_num = $this->where($cond_where)->save($update_arr);
        }
        return $up_num ? $up_num : false;
    }
    
    /**
     * ����IDɾ����Ϣ
     * @param mixed $ids 
     * return $del_num �ɹ���Ӱ������� \ ʧ�ܣ�false
     */
    public function del_cost_supplement_info_by_ids($ids)
    {
        $del_num = "";
        $cond_where = "";
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }

        $del_num = self::del_cost_supplement_info_by_cond($cond_where);

        return $del_num ? $del_num : false;
    }
    
    public function del_cost_supplement_info_by_cond($cond_where)
    {
        $del_num = "";
        if($cond_where)
        {
            $del_num = $this->where($cond_where)->delete();
        }
        return $del_num ? $del_num : false;
    }
    
    /**
     * ����ID��ȡ�ɱ������Ϣ
     * @param mixed $ids ����ID������
     * @param array $search_arr() Ҫ��ѯ������
     * return $info �ɹ� ������ \ ʧ�ܣ�false
     */
    public function get_cost_supplement_info_by_ids($ids,$search_arr)
    {
        $info = array();
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }
        $info = self::get_cost_supplement_info_by_cond($cond_where,$search_arr);
        
        return $info ? $info : false;
    }
    
    /**
     * ����������ѯ��Ϣ
     * @param string $cond_where ����
     * @param array $search_arr ��ѯ�ֶ�
     * return array $info �ɹ��� ��ѯ������������ \ʧ�ܣ�false
     */
    public function get_cost_supplement_info_by_cond($cond_where = "",$search_arr)
    {
        $info = array();
        if($cond_where)
        {
            if(is_array($search_arr) && !empty($search_arr))
            {
                $info = $this->where($cond_where)->field($search_arr)->select();
            }
            else
            {
                $info = $this->where($cond_where)->select();
            }
        }
        
        return $info ? $info : false;
    }
    
}

