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
                                        'xmxhd' => 7,  //��Ŀ�»,
                                        'fwfsc' => 8  // ���ҷ��ճ�
                                    );
    
    /***��Ŀҵ������***/
    private  $_conf_case_type_remark = array(
                                            1 => '����',
                                            2 => '����',
                                            3 => 'Ӳ��',
                                            4 => '�',
                                            5 => '��Ʒ',
                                            7 => '��Ŀ�',
											8 => '���ҷ��ճ�',
                                        );

    /***���ʱ�������ҵ������***/
    private  $_conf_case_Loan = array(
                    1 => '����',
                    2 => '����',
                    8 => '���ҷ��ճ�',
                );

    // 2 = ִ����
    // 3 = ���
    // 4 = ��Ŀ���ڽ���
    protected $arrExecStatus = array(2,3, 4);

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
     * ��ȡ��Ŀҵ������(�߱����ʱ�������)
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_case_Loan()
    {
        return $this->_conf_case_Loan;
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
		if( in_array($status,array(3,5)) ) $conf_where .= ' or PARENTID = '.$one['CASE_ID'];//����� ����ʱ��ͬʱ������Ŀ�»��case��¼״̬
		$arr['FSTATUS'] = $status;
		$res = $this->where($conf_where)->save($arr); 
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
     * @param	int  $level ���Ͳ㼶 0������������,1������������������
     * @return	string ��������
     */
    public function get_casetype_by_caseid($cid, $level = 0)
    {   
        $cid = intval($cid);
        $case_type = "";
        $search_field = array('SCALETYPE', 'PARENTID');
        $case_info = $this->get_info_by_id($cid, $search_field);
        
        if( !empty($case_info) )
        {   
            if($case_info[0]['PARENTID'] > 0 && $level == 1)
            {
                $case_type = $this->get_casetype_by_caseid($case_info[0]['PARENTID'], $level);
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

	/**
     * ���ݻid���°���״̬
     *
     * @access	public
     * @param	int  $activitiesId �id
     * @param	int  $status  ״ֵ̬
     *  
     */
    public function set_case_by_activitiesId($activitiesId, $status )
    {   
        $activitiesId = intval($activitiesId);
		$table_name = $this->tablePrefix.'ACTIVITIES';
		$conf_where = "ID=$activitiesId";
		$one = $this->table($table_name)->where($conf_where)->find();
		if($one){
			$conf_where = "ID=".$one['CASE_ID'];
			$arr['FSTATUS'] = $status;
			$res = $this->where($conf_where)->save($arr) ; 
		}
        return $res;
    }

    public function getLoanMoney($caseID,$cmoney, $type,$case_sign = "0") {
        if (empty($caseID) || empty($type)) {
            return null;
        }

        $sql = "SELECT getloanmoney({$caseID},{$cmoney}, {$type},{$case_sign}) AMOUNT from dual";
        $result = $this->query($sql);
        if (is_array($result) && count($result)) {
            $amount = $result[0]['AMOUNT'];
        } else {
            $amount = null;
        }

        return $amount;
    }


    public function getPreCostPreRate($caseID, &$preCost, &$preRate){
        if (empty($caseID)) {
            return null;
        }

        $sql = "
            SELECT SUMPROFIT,
                   OFFLINE_COST_SUM
            FROM erp_prjbudget
            WHERE case_id = {$caseID}
        ";

        $result = $this->query($sql);
        if (is_array($result) && count($result)) {
            $preRate = intval($result[0]['SUMPROFIT']);
            $preCost = intval($result[0]['OFFLINE_COST_SUM']);
            if ($preRate != 0) {
                $preRate = $preCost * 100 / $preRate;
            }

            return true;
        } else {
            return false;
        }
    }

    public function canCommitBenefit($projectID, $scaleType) {
        $where = array(
            'PROJECT_ID' => $projectID,
            'SCALETYPE' => $scaleType
        );
        $case = $this->where($where)->find();
        if (is_array($case)
            && count($case)
            && in_array(intval($case['FSTATUS']), $this->arrExecStatus)
        ) {
            return true;
        }

        return false;
    }


    /**
     * ���¿ͻ�����
     * @param $proId ��ĿID
     * @param $uId  �û�ID
     * @return bool
     */
    public function updateProMan($proId,$uId){
        //������Ŀ��
        $sql = 'UPDATE ERP_PROJECT SET CUSER = ' . $uId . ' WHERE ID = ' . $proId;
        $updatePro = D()->query($sql);

        //����House��
        $sql = 'UPDATE ERP_HOUSE SET CUSTOMER_MAN = ' . $uId . ' WHERE PROJECT_ID = ' . $proId;
        $updateHouse = D()->query($sql);

        if($updatePro===false || $updateHouse===false){
            return false;
        }

        return true;
    }

    public function getSelectList($data){
        foreach($data as $val){
            switch($val['SCALETYPE']){
                case "1":
                    $result['1'] = "����";
                    break;
                case "2":
                    $result['2'] = "����";
                    break;
                case "3":
                    $result['3'] = "Ӳ��";
                    break;
                case "4":
                    $result['4'] = "�";
                    break;
                case "5":
                    $result['5'] = "��Ʒ";
                    break;
                case "7":
                    $result['7'] = "��Ŀ�";
                    break;
                case "8":
                    $result['8'] = "���ҷ��ճ�";
                    break;

            }
        }
        return $result;
    }
}

/* End of file ProjectCaseModel.class.php */
/* Location: ./Lib/Model/ProjectCaseModel.class.php */