<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class BenefitsModel extends Model{
     
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'BENEFITS';
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    //ҵ���������״̬��־ δ����^0^������,�����^1^���ͨ��^2^
    protected $_benefits_status_remark = array(
                                1=>"δ����",
                                2=>"�����",
                                3=>"���ͨ��",
                                4=>"���δͨ��",
    );
    
    protected $_benefits_status = array(
                               "no_apply"=>1,       //δ����
                               "auditing"=>2,       //�����룬�����
                               "passed"=>3,         //���ͨ��
                               "no_audit"=>4,       //���δͨ��
    );
    
    //���ҵ���������״̬��־
    protected $_cost_status_remark = array(
                            1=>"δ���뱨��",
                            2=>"δ�ύ",
                            3=>"���ύ",
                            4=>"�ѱ���"
    );
    
    //���ҵ���������״̬
    protected $_cost_status = array(
                            "no_apply_reim"=>1,       //δ���뱨��
                            "applied_reim"=>2,    //������ δ�ύ
                            "auditing_reim"=>3,  //���ύ�������
                            "have_reimed"=>4    //�ѱ���
    );
    //δ���뱨��^1^�����룬δ�ύ^2^���ύ�������^3^���ͨ�����ѱ���^4^
    


    //��ȡ��������״̬
    public function get_benefits_status(){
        return $this->_benefits_status;
    }
    
    //��ȡ״̬��־
    public function get_benefits_status_remark(){
        return $this->_benefits_status_remark;
    }
    
    //��ȡ����״̬��־
    public function get_cost_status_remark(){
        return $this->_cost_status_remark;
    }
    
    //��ȡ����״̬
     public function get_cost_status(){
        return $this->_cost_status;
    }
    
    
    //����ҵ�����
    public function add_benefits($data){
        $table = $this->tablePrefix.$this->tableName;
        $res = $this->table($table)->add($data);
        //echo $this->model->getLastSql();
        return $res;
    }
    
    /**
     * ����ҵ�����ID��ѯҵ�������Ϣ
     *
     * @access	public
     * @param  mixed $ids ҵ��������
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��Ŀ��Ϣ
     */
    public function get_info_by_id($ids, $search_field = array())
    {   
        $cond_where = "";
        $benefits_info = array();
        
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
        
        $benefits_info = self::get_info_by_cond($cond_where, $search_field);
        
        return $benefits_info;
    }
    
    /**
     * ����������ȡ������Ϣ
     *
     * @access	public
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��Ŀ��Ϣ
     */
    public function get_info_by_cond($cond_where, $search_field = array())
    {   
        $benefits_info = array();
        
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $project_info;
        }
        
        if(is_array($search_field) && !empty($search_field))
        {
            $search_str = implode(',', $search_field);
            $benefits_info = $this->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $benefits_info = $this->where($cond_where)->select();
        }
        
        return $benefits_info;
    }
    
     /**
     * ����ID����ҵ�������Ϣ
     *
     * @access	public
     * @param	mixed  $ids Ҫ���µļ�¼
     * @param array $update_arr Ҫ���µ��ֶ�
     * @return	
     */
    public function update_info_by_id($ids,$update_arr){
        $table = $this->tablePrefix.$this->tableName;
        if(is_array($ids) && !empty($ids)){
            $id_str = implode(",", $ids);
            $conf_where = "ID in($id_str)";
        }else{
            $conf_where = "ID=$ids";
        }
        $res = $this->table($table)->where($conf_where)->save($update_arr);
        //echo $this->_sql();
        return $res;
    }
    
    /**
     * ����IDɾ��������Ϣ
     * @param $ids mixed 
     * return $del_num Ӱ�������
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
     * ��������ɾ��������Ϣ
     * @param $cond_where  string ���� 
     * return $del_num Ӱ�������
     */
    public function del_info_by_cond($cond_where)
    {
        $del_num = false;
        $del_num = $this->where($cond_where)->delete();
        return $del_num ? $del_num : false;
    }

    public function addFundPoolCostApply($data) {
        $result = false;
        if (notEmptyArray($data)) {
            $result = D('ProjectCost')->add_cost_info($data);
        }

        return $result;
    }

    public function getFundPoolCost($bizId) {
        $response = array();
        if ($bizId) {
            $sql = <<<SQL
              SELECT b.*,
                    u.deptid,
                    p.city_id
              FROM erp_benefits b
              LEFT JOIN erp_users u ON u.id = b.auser_id
              LEFT JOIN erp_project p ON p.id = b.project_id
              WHERE b.id = %d
SQL;
            $dbResult = $this->query(sprintf($sql, $bizId));
            if (notEmptyArray($dbResult)) {
                $dbResult = $dbResult[0];
                $response['CASE_ID'] = $dbResult['CASE_ID'];  //������� �����
                $response['CASE_TYPE'] = $dbResult['SCALE_TYPE'];  // ��Ŀ����
                $response['ENTITY_ID'] = $bizId;  // ҵ��ʵ���� �����
                $response['EXPEND_ID'] = $bizId;  // �ɱ���ϸ��� �����
                $response['ORG_ENTITY_ID'] = $bizId;  // ҵ��ʵ���� �����
                $response['ORG_EXPEND_ID'] = $bizId;
                $response['FEE'] = $dbResult['AMOUNT'];  // �ɱ���� �����
                $response['ADD_UID'] = $_SESSION['uinfo']['uid'];  //�����û���� �����
                $response['OCCUR_TIME'] = date('Y-m-d H:i:s');  //����ʱ�� �����
                $response['ISFUNDPOOL'] = 1;  // �Ƿ��ʽ�أ�0��1�ǣ� �����
                $response['ISKF'] = 1;  // �Ƿ�۷� �����
                $response['FEE_REMARK'] = '֧���������������뱨��'; //�������� ��ѡ�
                $response['INPUT_TAX'] = 0; // ����˰ ��ѡ�
                $response['FEE_ID'] = 80; // ֧������������
                $response['EXPEND_FROM'] = 32; // ֧������������
                $response['STATUS'] = 2;  //
                $response['PROJECT_ID'] = $dbResult['PROJECT_ID'];
                $response['USER_ID'] = $dbResult['AUSER_ID'];
                $response['DEPT_ID'] = $dbResult['DEPTID'];
                $response['CITY_ID'] = $dbResult['CITY_ID'];
                $response['ISCOST'] = $dbResult['ISCOST'];
                $response['TYPE'] = 16;  // �ɱ�����Ϊ֧������������
            }
        }

        return $response;
    }
}