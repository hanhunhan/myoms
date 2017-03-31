<?php

/**
 * ����ģ��
 *
 * @author liuhu
 */
class ProjectCaseModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'CASE';
    
    /***��Ŀҵ������***/
    private  $_conf_case_type = array(
                                        'ds' => 1,   //����
                                        'fx' => 2,   //����
                                        'yg' => 3,   //Ӳ��
                                        'hd' => 4,   //�����
                                        'cp' => 5,   //��Ʒ
                                        'xmxhd' => 7  //��Ŀ�»
                                    );
    
    /***��Ŀҵ������***/
    private  $_conf_case_type_remark = array(
                                            1 => '����',
                                            2 => '����',
                                            3 => 'Ӳ��',
                                            4 => '�',
                                            5 => '��Ʒ',
                                            7 => '��Ŀ�',
                                        );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    /**
     * ��ȡ��Ŀҵ������
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_case_type()
    {
    	return $this->_conf_case_type;
    }
    
    
    /**
     * ��ȡ��Ŀҵ����������
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_case_type_remark()
    {
    	return $this->_conf_case_type_remark;
    }
    
    
    /**
     * ���ݰ�����Ż�ȡ������Ϣ
     *
     * @access	public
     * @param  mixed $cids �������
     * @param array $search_field �����ֶ�
     * @return	array ������Ϣ
     */
    public function get_info_by_id($cids, $search_field = array())
    {   
        $cond_where = "";
        $case_info = array();
        
        if(is_array($cids) && !empty($cids))
        {   
            $ids_str = implode(',', $cids);
            $cond_where = " ID IN (".$ids_str.")";
        }
        else
        {
            $id  = intval($cids);
            $cond_where = " ID = '".$id."'";
        }
        
        $case_info = self::get_info_by_cond($cond_where, $search_field);
        
        return $case_info;
    }
    
    
    /**
     * ������Ŀ��Ż�ȡ������Ϣ
     *
     * @access	public
     * @param  mixed $ids ��Ŀ���
     * @param	string  $case_type ���������ַ�����(ds\fx\yg����)
     * @param array $search_field �����ֶ�
     * @return	array ��Ŀ��Ϣ
     */
    public function get_info_by_pid($ids, $case_type = '', $search_field = array())
    {   
        $cond_where = "";
        $project_info = array();
        
        if(is_array($ids) && !empty($ids))
        {   
            $ids_str = implode(',', $ids);
            $cond_where = " PROJECT_ID IN (".$ids_str.")";
        }
        else
        {
            $id  = intval($ids);
            $cond_where = " PROJECT_ID = '".$id."'";
        }
        
        $case_type = strip_tags($case_type);
        if($case_type != '')
        {   
            $scaletype = !empty($this->_conf_case_type[$case_type]) ? 
                    $this->_conf_case_type[$case_type] : 0;
            $scaletype > 0 ? $cond_where .= " AND SCALETYPE = '".$scaletype."'" : '';
        }
        
        $project_info = self::get_info_by_cond($cond_where, $search_field);
        
        return $project_info;
    }
    
    
    /**
     * ������Ŀ��Ų�ѯ�Ƿ����ĳ��ҵ������
     *
     * @access	public
     * @param	int  $prj_id ��Ŀ���
     * @param  string $case_type ҵ�������ַ�������
     * @return	boolean ���ڷ���TRUE,�����ڷ���FALSE
     */
    public function is_exists_case_type($prj_id, $case_type)
    {   
        $num = 0;
        
        $prj_id  = intval($prj_id);
        $cond_where = " PROJECT_ID = '".$prj_id."'";

        $case_type = strip_tags($case_type);
        $scaletype = !empty($this->_conf_case_type[$case_type]) ? 
                $this->_conf_case_type[$case_type] : '';
        
        if($scaletype != '')
        {
            $cond_where .= " AND SCALETYPE = '".$scaletype."' ";
            $num = $this->where($cond_where)->count();
        }
        
        return $num > 0 ? TRUE : FALSE;
    }
    
    
    /**
     * ����������ȡ��Ŀ������Ϣ
     *
     * @access	public
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��Ŀ��Ϣ
     */
    public function get_info_by_cond($cond_where, $search_field = array())
    {   
        $project_info = array();
        
        $cond_where = strip_tags($cond_where);
        
        if(empty($cond_where) || $cond_where == "")
        {
            return $project_info;
        }
        
        if(is_array($search_field) && !empty($search_field))
        {
            $search_str = implode(',', $search_field);
            $project_info = $this->field($search_str)->where($cond_where)->select();
        }
        else
        {
            $project_info = $this->where($cond_where)->select();
        }
        //echo $this->getLastSql();
        return $project_info;
    }
	 /*
     *ҵ������״̬��� ���� ��ֹ 
     * @param int $caseid ҵ������id
     *  
     * return 
     */
	 public function update_case_status($id,$status){
		$table_name = $this->tablePrefix.'FINALACCOUNTS';
		$cond_where = "ID='$id'";
		$one = $this->table($table_name)->where($cond_where)->find();//FINALACCOUNTS

		//$table_name = $this->tablePrefix.'CASE';
		$conf_where = "ID= ".$one['CASE_ID'];
		$arr['FSTATUS'] = $status;
		$res = $this->where($conf_where)->save($arr) ; 
		return $res;
	 }
 

	 /*
     *ҵ������״̬���  ����
     * @param int $caseid ҵ������id
     *  
     * return 
     */
	 public function update_case_status_pro($prjid,$status){
		 
		$conf_where = "PROJECT_ID= ".$prjid;
		$arr['FSTATUS'] = $status;
		$res = $this->where($conf_where)->save($arr) ; 
		return $res;
	 }
 

    /**
     * ���ݰ�����Ż�ȡ��������
     *
     * @access	public
     * @param	int  $cid �������
     * @return	string ��������
     */
    public function get_casetype_by_caseid($cid)
    {   
        $cid = intval($cid);
        $case_type = "";
        $search_field = array('SCALETYPE', 'PARENTID');
        $case_info = $this->get_info_by_id($cid, $search_field);
        
        if( !empty($case_info) )
        {   
            if($case_info[0]['PARENTID'] > 0)
            {
                $case_type = $this->get_casetype_by_caseid($case_info[0]['PARENTID']);
            }
            else
            {   
                $conf_case_type = self::get_conf_case_type();
                $conf_case_type_flip = array_flip( $conf_case_type );
                $case_type = $conf_case_type_flip[$case_info[0]['SCALETYPE']];
            }
        }
        
        return $case_type;
    }
 
}

/* End of file ProjectCaseModel.class.php */
/* Location: ./Lib/Model/ProjectCaseModel.class.php */