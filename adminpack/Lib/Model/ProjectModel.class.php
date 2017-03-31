<?php

/**
 * ��Ŀ��Ϣ������
 *
 * @author 
 */

class ProjectModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PROJECT';

    protected $scaleType2statusName = array(
        1 => 'BSTATUS',
        2 => 'MSTATUS',
        3 => 'ASTATUS',
        4 => 'ACSTATUS',
        5 => 'CPSTATUS',
        8 => 'SCSTATUS'
    );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }

    public function getStatusFieldNameList() {
        return $this->scaleType2statusName;
    }
    
    
    /**
     * ������Ŀ��Ż�ȡ��Ŀ��Ϣ
     *
     * @access	public
     * @param  mixed $ids ��Ŀ���
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
     * @return	array ��Ŀ��Ϣ
     */
    public function get_info_by_id($ids, $search_field = array())
    {   
        $cond_where = "";
        $project_info = array();
        
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
        
        $project_info = self::get_info_by_cond($cond_where, $search_field);
        
        return $project_info;
    }
    
    
    /**
     * ����������ȡ��Ŀ��Ϣ
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
        
        return $project_info;
    }
    
    
    /**
     * ��ȡ��ǰ�û���Ȩ�޵���Ŀ��Ϣ
     *
     * @access	public
     * @param	int $uid �û����
     * @param	int $scaletype ҵ�����ͱ��
     * @param	string $search_keyword �����ؼ���
     * @param	int $city_id ���б��
     * @return	array ��Ŀ��Ϣ
     */
    public function get_my_project_list($uid, $scaletype, $search_keyword, $city_id = 0)
    {   
        $project_info = array();
        $search_keyword = strip_tags($search_keyword);
        $city_id = intval($city_id);
        
        if($search_keyword != '')
        {
            //��ѯ����
            $cond['CITY_ID']  = array('EQ', $city_id);
            $cond['PROJECTNAME'] = array('LIKE', '%'.$search_keyword.'%');

            //Ȩ����Ŀ
            $permission_project = $this->get_permission_project_by_uid($uid , $scaletype);

            if(is_array($permission_project) && !empty($permission_project))
            {
                foreach ($permission_project as $key => $value)
                {
                    $project_id_arr[] = $value['PRO_ID'];
                }

                $cond['ID']  = array('IN', $project_id_arr);
            }

            switch ($scaletype){
                case 1:
                    //����
 
                    $cond['BSTATUS']  = array('in', '2,4');
 
                    break;
                case 2:
                    //����
 
                    $cond['MSTATUS']  = array('in', '2,4');
 
                    break;
                case 3:
                    //Ӳ��
                    $cond['ASTATUS']  = array('in', '2,4');
                    break;
                case 4:
                    //�
                    $cond['ACSTATUS']  = array('in', '2,4');
                    break;
                case 5:
                    //��Ʒ
                    $cond['CPSTATUS']  = array('in', '2,4');
                    break;
            }
            
            $project_info = $this->where($cond)->select();
        }
        
        //echo $this->getLastSql();
        return $project_info;
    }
    
    
    /**
     * �����û���Ż�ȡȨ����Ŀ
     *
     * @access	public
     * @param	int $uid �û����
     * @param	int $scaletype ҵ������
     * @return	array ��Ŀ��Ϣ
     */
    public function get_permission_project_by_uid($uid , $scaletype = 'ds')
    {   
        $permission_project = array();
        $uid = intval($uid);

        $caseTypeArr = D('ProjectCase')->get_conf_case_type();
        $scaletype = $caseTypeArr[$scaletype];

        //��ѯ����
        $cond['USE_ID']  = array('EQ', $uid);
        
        if($scaletype > 0)
        {
            $cond['ERP_ID']  = array('EQ', $scaletype);
        }
        
        $cond['ISVALID']  = array('EQ', -1);
        $permission_project = $this->table('ERP_PROROLE')->field('PRO_ID')->where($cond)->select();

        return $permission_project;
    }
    
    
    /**
     * ��ȡָ����Ŀ���շ�Ӷ���׼
     *
     * @access	public
     * @param	int $caseid �������
     * @param	int $case_type ҵ������
     * @param	int $scaletype �շ�����
     * @param	int $status ����״̬
     * @return	array ������Ϣ
     */
    public function get_feescale_by_cid($caseid, $scaletype = '', $status = '',$mtype=null)
    {	
    	$scale_info = array(); 
    	
    	if($caseid > 0)
    	{	
            /**��ѯ����**/
            $cond_where = "CASE_ID = '".$caseid."'  ";//AND ISVALID = -1

            !empty($scaletype) && $scaletype != '' ?  
                    $cond_where .= " AND SCALETYPE = '".intval($scaletype)."'" : '';

            if(!empty($status) && $status != '' ){
                    $cond_where .= " AND STATUS = '".intval($status)."'" ;
					} else  $cond_where .= " AND (STATUS = 3 or ISVALID=-1)";
			if(!is_null($mtype)){
				$cond_where .= " AND MTYPE=$mtype ";
			}
            /**���ݱ�**/
            $table_name = $this->tablePrefix.'FEESCALE';  
            $scale_info = $this->table($table_name)->where($cond_where)->select();
    	}
    	
    	return $scale_info;
    }
	 public function get_feescale_by_cid_vaild($caseid, $scaletype = '' )
    {	
    	$scale_info = array(); 
    	
    	if($caseid > 0)
    	{	
            /**��ѯ����**/
            $cond_where = "CASE_ID = '".$caseid."'  ";//AND ISVALID = -1

            !empty($scaletype) && $scaletype != '' ?  
                    $cond_where .= " AND SCALETYPE = '".intval($scaletype)."'" : '';

            
                      $cond_where .= " AND ISVALID = -1 ";

            /**���ݱ�**/
            $table_name = $this->tablePrefix.'FEESCALE';
            $scale_info = $this->table($table_name)->where($cond_where)->select();
    	}
    	
    	return $scale_info;
    }

	public function get_feescale_by_cid_stype($caseid, $scaletype = '',$val,$stype=1,$mtype=null )
    {	
    	$scale_info = array(); 
    	
    	if($caseid > 0)
    	{	
            /**��ѯ����**/
            $cond_where = "CASE_ID = '".$caseid."'  ";//AND ISVALID = -1

            !empty($scaletype) && $scaletype != '' ?  
                    $cond_where .= " AND SCALETYPE = '".intval($scaletype)."'" : '';
			!is_null($mtype)   ?  
                    $cond_where .= " AND MTYPE = '".intval($mtype)."'" : '';
			
            
            $cond_where .= " AND AMOUNT = '". $val ."'" ;

            
			$cond_where .= " AND STYPE=$stype AND ISVALID = -1 ";

            /**���ݱ�**/
            $table_name = $this->tablePrefix.'FEESCALE';
            $scale_info = $this->table($table_name)->where($cond_where)->select();
    	}
    	
    	return $scale_info;
    }
	public function get_feescale_by_cid_val2($caseid, $scaletype = '',$val, $mtype=null )
    {	
    	$scale_info = array(); 
    	
    	if($caseid > 0)
    	{	
            /**��ѯ����**/
            $cond_where = "CASE_ID = '".$caseid."'  ";//AND ISVALID = -1

            !empty($scaletype) && $scaletype != '' ?  
                    $cond_where .= " AND SCALETYPE = '".intval($scaletype)."'" : '';
			!is_null($mtype)   ?  
                    $cond_where .= " AND MTYPE = '".intval($mtype)."'" : '';
			
            
            $cond_where .= " AND AMOUNT = '". $val ."'" ;
			$cond_where .= " AND ISVALID = -1 ";
            
		 

            /**���ݱ�**/
            $table_name = $this->tablePrefix.'FEESCALE';
            $scale_info = $this->table($table_name)->where($cond_where)->select();
    	}
    	
    	return $scale_info;
    }
    
    
    /*
     * ������ĿID�޸���Ŀ��Ϣ
     * @param int $id ��Ŀid
     * @param array $update Ҫ�޸ĵ��ֶ�
     * return 
     */
    public function update_prj_info_by_id($ids, $update_arr)
    {
        $conf_where = "";
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",",$ids);
            $cong_where = "ID IN ($id_str)";
        }
        else if($ids)
        {
            $conf_where = "ID = $ids";
        }
        else if(!$ids)
        {
            return false;
        }
        
        $res = $this->where($conf_where)->save($update_arr);
        return $res;
    }
    
    
	/*
     * ��ȡ��Ŀ����״̬
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function get_project_status($prjid){
		$res = $this->field('PSTATUS')->where("ID=$prjid")->find();
		
        return $res['PSTATUS'];
	}

	/*
     * ��ȡ��Ŀ���״̬
     * @param int $id ���id
     *  
     * return 
     */
	 public function get_Change_Flow_Status($change_id){
		$res = M("Erp_project_change")->field('STATUS')->where("ID=$change_id")->find();
		
        return $res['STATUS'];
	}
    
    
	/*
     * ��Ŀ���������״̬
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function update_check_status($prjid){
		 
		$update_arr['PSTATUS'] = 6;//�����
		$conf_where = "ID = $prjid"; 
		$res = $this->where($conf_where)->save($update_arr);
		//if($res) $res=$this->update_case_status_pro($prjid,2);//case״̬�ĳ�ִ����
		
        return $res;
	 }
	 
    
     /*
     * ��Ŀ�������ͨ��״̬
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function update_pass_status($prjid){
		$cond_where = "ID=$prjid";
		$project_info = $this->field('ID, PSTATUS, BSTATUS,MSTATUS,ASTATUS,ACSTATUS,CPSTATUS,SCSTATUS')->where($cond_where)->find();
		if($project_info['PSTATUS']==6)$update_arr['PSTATUS'] = 3;
		if($project_info['BSTATUS']==1) $update_arr['BSTATUS'] = 2;//����
		if($project_info['MSTATUS']==1) $update_arr['MSTATUS'] = 2;//����
		if($project_info['ACSTATUS']==1) $update_arr['ACSTATUS'] = 2;//�����
		if($project_info['SCSTATUS']==1) $update_arr['SCSTATUS'] = 2;//���ҷ��ճ�
		$res = $this->where($cond_where)->save($update_arr);
		if($res) $res=$this->update_case_status_pro($prjid,2);//case״̬�ĳ�ִ����
        return $res;
	 }
	  
     
    /*
     * ��Ŀ��˲�ͨ��״̬
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function update_nopass_status($prjid){
		$cond_where = "ID=$prjid";
		$project_info = $this->field('ID, PSTATUS, BSTATUS,MSTATUS,ASTATUS,ACSTATUS,CPSTATUS')->where($cond_where)->find();
		if($project_info['PSTATUS']==6) $update_arr['PSTATUS'] = 5;
		if($project_info['BSTATUS'] ) $update_arr['BSTATUS'] = 1;//����
		if($project_info['MSTATUS'] ) $update_arr['MSTATUS'] = 1;//����
		if($project_info['ACSTATUS'] ) $update_arr['ACSTATUS'] = 1;//�����
		$res = $this->where($cond_where)->save($update_arr);
		if($res) $res=$this->update_case_status_pro($prjid,1);//case״̬ 1 Ĭ��
        return $res;
	 }

	
    /*
     * ��Ŀ��� �˻ط�����״̬
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function update_reback_status($prjid){
		$cond_where = "ID=$prjid";
		$project_info = $this->field('ID, PSTATUS, BSTATUS,MSTATUS,ASTATUS,ACSTATUS,CPSTATUS')->where($cond_where)->find();
		if($project_info['PSTATUS']==6) $update_arr['PSTATUS'] = 2;
		if($project_info['BSTATUS'] ) $update_arr['BSTATUS'] = 1;//����
		if($project_info['MSTATUS'] ) $update_arr['MSTATUS'] = 1;//����
		if($project_info['ACSTATUS'] ) $update_arr['ACSTATUS'] = 1;//�����
		$res = $this->where($cond_where)->save($update_arr);
		if($res) $res=$this->update_case_status_pro($prjid,1);//case״̬ 1 Ĭ��
        return $res;
	 }
     
     
	/*
     * ��Ŀ�������״̬
     * @param int $id ��Ŀid
     *  
     * return 
     */
	public function update_finalaccounts_status($id){
		$table_name = $this->tablePrefix.'FINALACCOUNTS';
		$cond_where = "ID='$id'";
		$one = $this->table($table_name)->where($cond_where)->find();//FINALACCOUNTS
		//$cond_where = "TYPE=1 and PROJECT='".$one['PROJECT']."'  ";
		//echo $finalcount =  $this->table($table_name)->where($cond_where)->count();
		 
		$table_name = $this->tablePrefix.'CASE';
		$case = $this->table($table_name)->where("ID = ".$one['CASE_ID'])->find();//case
		//ҵ����������
		$casecount = $this->table($table_name)->where(" PROJECT_ID = ".$case['PROJECT_ID'])->count();
		$cond_where = "ID=".$case['PROJECT_ID'];
		$project = $this->field('ID, PSTATUS, BSTATUS,MSTATUS,ASTATUS,ACSTATUS,CPSTATUS, SCSTATUS')->where($cond_where)->find();//project
		 
		if($project['BSTATUS'] && $case['SCALETYPE']==1) $update_arr['BSTATUS'] = 3;//����
		if($project['MSTATUS'] && $case['SCALETYPE']==2 ) $update_arr['MSTATUS'] = 3;//����
		if($project['ASTATUS'] && $case['SCALETYPE']==3 ) $update_arr['ASTATUS'] = 3;//Ӳ��
		if($project['ACSTATUS'] && $case['SCALETYPE']==4 ) $update_arr['ACSTATUS'] = 3;//�����
		if($project['CPSTATUS'] && $case['SCALETYPE']==5 ) $update_arr['CPSTATUS'] = 3;//��Ʒ
		if($project['SCSTATUS'] && $case['SCALETYPE']==8 ) $update_arr['SCSTATUS'] = 3;//��Ʒ

		$res = $this->where($cond_where)->save($update_arr);//project
		 
		 
		if($res){
			$this->update_finalaccounts_pass_status($id);
			
		}
        return $res;
	}
    /*
     *ҵ������״̬���
     * @param int $id ���� ��ֹ ��id $status״̬
     *  
     * return 
     */
	 public function update_case_status($id,$status){
		$case =  D('ProjectCase');
		$res = $case->update_case_status($id,$status);
		return $res;
	 }
	/*
     *ҵ������״̬���
     * @param int $prjid ��Ŀid $status״̬
     *  
     * return 
     */
	 public function update_case_status_pro($prjid,$status){
		$case =  D('ProjectCase');
		$res = $case->update_case_status_pro($prjid,$status);
		return $res;
	 }
    
	/*
     * ��Ŀ ���� �����
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function update_finalaccounts_check_status($id){
		 $table_name = $this->tablePrefix.'FINALACCOUNTS';
		 $conf_where = " ID=$id";
		 $update_arr['STATUS'] = 1;//�����
		 $res = $this->table($table_name)->where($conf_where)->save($update_arr);
		 //if($res) $res=$this->update_case_status($id,31);//case  ������ ״̬
		 return $res;
	 }
     
     
	/*
     * ��Ŀ ���� ���ͨ��
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function update_finalaccounts_pass_status($id){
		 $table_name = $this->tablePrefix.'FINALACCOUNTS';
 
		 $conf_where = "  ID=$id";
 
		 $update_arr['STATUS'] = 2;
		 $res = $this->table($table_name)->where($conf_where)->save($update_arr);
		 if($res){
			 $res=$this->update_case_status($id,3);//case �Ѿ��� ״̬

		 }
		 return $res;
	 }
     
     
	/*
     * ��Ŀ ���� ��˲�ͨ��
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function update_finalaccounts_nopass_status($id){
		 $table_name = $this->tablePrefix.'FINALACCOUNTS';
		 $conf_where = " ID=$id";
		 $update_arr['STATUS'] = 3;
		 $res = $this->table($table_name)->where($conf_where)->save($update_arr);
		// if($res) $res=$this->update_case_status($id,2);//case ��˲�ͨ�� �˻�ִ���� ״̬
		return $res;
	 }
     
	/*
     * ��ȡ��Ŀҵ��� ����״̬
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function get_finalaccounts_status($id){
		$table_name = $this->tablePrefix.'FINALACCOUNTS';
 
		$conf_where = " ID=$id";
 
		  
		$res = $this->table($table_name)->where($conf_where)->find();
		return $res['STATUS'];
	 }
     
     
	 /*
     * ��Ŀ��ֹ 
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function update_termination_status($id ){
		$table_name = $this->tablePrefix.'FINALACCOUNTS';
		$cond_where = "ID='$id'";
		$one = $this->table($table_name)->where($cond_where)->find();//FINALACCOUNTS
		$cond_where = "TYPE=2 and PROJECT='".$one['PROJECT']."' and STATUS=2";
		$finalcount =  $this->table($table_name)->where($cond_where)->count();
		 
		$table_name = $this->tablePrefix.'CASE';
		$case = $this->table($table_name)->where("ID = ".$one['CASE_ID'])->find();//case
		//ҵ����������
		$casecount = $this->table($table_name)->where(" PROJECT_ID = ".$case['PROJECT_ID'])->count();
		$cond_where = "ID=".$case['PROJECT_ID'];
		$project = $this->field('ID, PSTATUS, BSTATUS,MSTATUS,ASTATUS,ACSTATUS,CPSTATUS,SCSTATUS')->where($cond_where)->find();//project
		 
		if($project['BSTATUS'] && $case['SCALETYPE']==1) $update_arr['BSTATUS'] = 5;//����
		if($project['MSTATUS'] && $case['SCALETYPE']==2 ) $update_arr['MSTATUS'] = 5;//����
		if($project['ASTATUS'] && $case['SCALETYPE']==3 ) $update_arr['ASTATUS'] = 5;//Ӳ��
		if($project['ACSTATUS'] && $case['SCALETYPE']==4 ) $update_arr['ACSTATUS'] = 5;//�����
		if($project['CPSTATUS'] && $case['SCALETYPE']==5 ) $update_arr['CPSTATUS'] = 5;//��Ʒ
		if($project['SCSTATUS'] && $case['SCALETYPE']==8 ) $update_arr['SCSTATUS'] = 5; // ���ҷ��ճ�

		//if($finalcount+1>=$casecount)$update_arr['PSTATUS'] = 5;
		 
		$res = $this->where($cond_where)->save($update_arr);//project
		if($res){
			$res=$this->update_termination_pass_status($id);
			
		}
		 
        return $res;
	 }
     
     
    /*
     * ��Ŀ��ֹ  �����
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function update_termination_check_status($id ){
		 $table_name = $this->tablePrefix.'FINALACCOUNTS';
		 $conf_where = "ID=$id";
		 $update_arr['STATUS'] = 1;
		 $res = $this->table($table_name)->where($conf_where)->save($update_arr);
		// if($res) $res=$this->update_case_status($id,51);//case �����  1��ֹ����� ״̬
		 return $res;	
	 }
     
     
	/*
     * ��Ŀ��ֹ  ���ͨ��
     * @param int $id ��Ŀid
     *  
     * return 
     */
	public function update_termination_pass_status($id )
    {
        $table_name = $this->tablePrefix.'FINALACCOUNTS';
        $conf_where = "ID = $id";
        $update_arr['STATUS'] = 2;
        $res = $this->table($table_name)->where($conf_where)->save($update_arr);
		if($res) $res=$this->update_case_status($id,5);//case ���ͨ�� ��ֹ ״̬
        return $res;
	 }
     
     
	 /*
     * ��Ŀ��ֹ  ��˲�ͨ��
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function update_termination_nopass_status($id ){
		 $table_name = $this->tablePrefix.'FINALACCOUNTS';
		 $conf_where = "ID = $id";
		 $update_arr['STATUS'] = 3;
		 $res = $this->table($table_name)->where($conf_where)->save($update_arr);
		 if($res) $res=$this->update_case_status($id,2);//case ��˲�ͨ�� �˻�ִ�� ״̬
		 return $res;
		
	 }
	 
     
    /*
     * ��Ŀ ��ֹ״̬
     * @param int $id ��Ŀid
     *  
     * return 
     */
	public function get_termination_status($id){
		$table_name = $this->tablePrefix.'FINALACCOUNTS';
		$conf_where = " ID = $id";
		  
		$res = $this->table($table_name)->where($conf_where)->find();
		return $res['STATUS'];
	 }
     
    
    /*
     * ����ͳ��
     * @param int $id ��Ŀid
     *  
     * return 
     */
	public function get_prjdata($cid,$kind ){
		$sql = "select  getprjdata($cid,$kind,null) from dual";
		$data =  $this->query($sql); 
		 
		return current($data[0]);
	 }
     
     
	 /*
     * ʵ�����·���
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function get_bugcost($cid){
		$sql = "select GetOfflineCost($cid, 1) from dual";
		$data =  $this->query($sql); 
		 
		return current($data[0]);
	 }
     
     
	 /*
     * �ۺ���� Ԥ��
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function get_vadcost($cid)
    {
		$sql = "select  GETVADCOST($cid ) from dual";
		$data =  $this->query($sql); 
		 
		return current($data[0]);
	 }
     
     
	/*
     * ��Ʊ���� ʵ������
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function get_vincome($cid)
    {
		$sql = "select GETVINCOME($cid) from dual";
		$data =  $this->query($sql);
		 
		return current($data[0]);
	 }
     
     
	/*
     * ҵ����ֹʱ��
     * @param int $id ��Ŀid
     *  
     * return 
     */
	public function get_zjtime($id)
    {
		$sql = "select to_char(ZJTIME,'yyyy-mm-dd') as zjtime from ERP_FINALACCOUNTS where ID=$id";
		$data =  $this->query($sql); 
		
		return current( $data[0] );
	}
	/*
     * ɾ����Ŀ
     * @param int $id ��Ŀid
     *  
     * return 
     */
	public function del_project($project_id)
    {
		$this->where("ID=".$project_id)->delete();
	}
    
    
    /*
     * ������Ŀ��Ż�ȡ��Ŀ��Ч���۷�ʽ
     * @param int $prj_id ��Ŀid
     * return array ���۷�ʽ����
     */
    public function get_project_budget_sale_by_prjid($prj_id, $isvalid = '-1')
    {   
        $result = array();
        
        $prj_id = intval($prj_id);
        
        if($prj_id > 0)
        {
            $table_name = $this->tablePrefix.'BUDGETSALE';
            $cond_where = "PROJECTT_ID = '".$prj_id."' AND ISVALID = '".$isvalid."'";
            $result = $this->table($table_name)->where($cond_where)->order('salemethodid asc')->select();
        }
        
        return $result;	
     }
	  /*
     * ������Ŀ������ñ����¼
     * @param int $prj_id ��Ŀid
     * return  
     */
	public function set_project_change($project_id){
		$clist = $this->table($this->tablePrefix.'CASE')->field('ID')->where("PROJECT_ID=$project_id and PARENTID is null")->select();
		foreach($clist as $v){// var_dump($v);
			$pbudgetId = $this->get_prjbudget_id($v['ID']);
			$buddata = $this->get_fees($pbudgetId );
			//$budget->set_budgetfee($pbudgetId,$buddata);//���浽Ԥ���
			// $this->table($this->tablePrefix.'PRJBUDGET')->where("ID=$pbudgetId")->save($buddata);
            // ִ�����񣬸�����ĿԤ���
           // $this->startTrans();
            $affected = D('Erp_prjbudget')->where("ID=$pbudgetId")->save($buddata);//var_dump($buddata);var_dump($affected);
           
		}
		return true;  
	}
	public function get_prjbudget_id($caseId){
		$budget = $this->table($this->tablePrefix.'PRJBUDGET')->field('ID')->where("CASE_ID=$caseId")->find();
		return $budget['ID'];
	}
	public function get_fees($pbudgetId){
		$budgetdata = $this->table($this->tablePrefix.'BUDGETFEE')->where("BUDGETID=$pbudgetId")->select();
		foreach($budgetdata as $value){
			if($value['FEEID']==108)$buddata['OFFLINE_COST_SUM'] = $value['AMOUNT']; 
			if($value['FEEID']==109)$buddata['OFFLINE_COST_SUM_PROFIT'] = $value['AMOUNT']; 
			if($value['FEEID']==110)$buddata['OFFLINE_COST_SUM_PROFIT_RATE'] = $value['AMOUNT']; 
			if($value['FEEID']==101)$buddata['PRO_TAXES'] = $value['AMOUNT']; 
			if($value['FEEID']==102)$buddata['PRO_TAXES_PROFIT'] = $value['AMOUNT']; 
			if($value['FEEID']==103)$buddata['PRO_TAXES_PROFIT_RATE'] = $value['AMOUNT']; 
			if($value['FEEID']==106)$buddata['ONLINE_COST'] = $value['AMOUNT']; 
			if($value['FEEID']==107)$buddata['ONLINE_COST_RATE'] = $value['AMOUNT']; 
		}
		return $buddata;
	}
	//��ȡ ��Ŀ��ҵ������
	public function get_businessclass($project_id){
		$clist = $this->table($this->tablePrefix.'CASE')->field('ID,SCALETYPE')->where("PROJECT_ID=$project_id")->select();
		return $clist;
	}

    /**
     * ��ȡԤ�տ�����
     * @param $caseID
     * @param $scaleType
     * @param $bzType 1=����Ԥ�տ� 2=����ȷ��Ԥ�տ�
     * @return mixed
     */
    public function getCaseAdvances($caseID, $scaleType, $bzType) {
        if (empty($caseID) || empty($scaleType)) {
            return;
        }

        // ��ȡ��Ŀ����
        if (in_array($scaleType, array(1, 2))) {
            $sql = "SELECT getCaseSumAdvances($caseID, $scaleType, $bzType) FROM dual";
            $data =  $this->query($sql);

            return current($data[0]);
        } else {
            return 0;
        }
    }

    /**
     * ��ȡ��Ʊ������ؿ�����
     * @param $caseID
     * @param $scaleType
     * @param $bzType
     * @return mixed|void
     */
    public function getCaseInvoiceAndReturned($caseID, $scaleType, $bzType) {
        if (empty($caseID) || empty($scaleType)) {
            return;
        }

        // ͳ����Ŀ�»�Ļؿ�����
        $sql = "
            SELECT c.ID,
                   c.SCALETYPE
            FROM erp_case c
            WHERE c.parentid = {$caseID}
            OR c.id = {$caseID}
        ";
        $sum = 0;
        $children = $this->query($sql);
        foreach ($children as $child) {
            $sql = "SELECT getCaseInvoiceAndReturned({$child['ID']}, {$child['SCALETYPE']}, $bzType) FROM dual";
            $data = $this->query($sql);
            $sum += current($data[0]);
        }

        return $sum;
    }

    public function getProjectAffirmIncome($prjId) {
        $result = 0;
        if (intval($prjId)) {
            $sql = <<<INCOME_SQL
                SELECT SUM(getAffirmIncome(cid, caseType)) affirm_income
                FROM
                  (SELECT c.id cid,
                          c.scaleType caseType
                   FROM erp_case c
                   WHERE c.project_id = %d
                    AND c.scaletype = 1)
INCOME_SQL;
            $dbResult = $this->query(sprintf($sql, $prjId));
            if (notEmptyArray($dbResult)) {
                $result = floatval($dbResult[0]['AFFIRM_INCOME']);
            }
        }

        return $result;
    }

    public function getProjectAppliedFundPoolCost($prjId) {
        $result = 0;
        if (intval($prjId)) {
            $caseId = D('erp_case')->where("project_id = {$prjId} AND scaletype = 1")->getField('id');
            if ($caseId) {
                $sql = <<<COST_SQL
                SELECT getProjectFundPoolCost(%d, 7) cost
                FROM dual
COST_SQL;
                $dbResult = $this->query(sprintf($sql, $caseId));
                if (notEmptyArray($dbResult)) {
                    $result = floatval($dbResult[0]['COST']);
                }
            }

            $notApplyFundPoolCost = D('erp_benefits')->where("CASE_ID = {$caseId} AND TYPE = 2 AND ISCOST = 1")->sum('AMOUNT');
            $result += floatval($notApplyFundPoolCost);
        }

        return $result;
    }

    /**
     * @param $caseID
     * @param $bzType 1=�ѱ���; 3=�ѷ���δ����; 2 = 1 + 3;
     * @return int|mixed|void
     */
    public function getCaseCost($caseID, $bzType) {
        if (empty($caseID) || empty($bzType)) {
            return;
        }

        // ͳ����Ŀ�»�Ļؿ�����
        $sql = "
            SELECT c.ID,
                   c.SCALETYPE
            FROM erp_case c
            WHERE c.parentid = {$caseID}
            OR c.id = {$caseID}
        ";

        $sum = 0;
        $children = $this->query($sql);
        foreach ($children as $child) {
            $sql = "SELECT GetCaseCost({$child['ID']}, $bzType) FROM dual";
            $data = $this->query($sql);
            $sum += current($data[0]);
        }

        return $sum;
    }

    /**
     * ��ȡǩԼδ������
     * @param $caseId
     * @param $bzType
     * @return float|void
     */
    public function caseSignNoPay($caseId,$bzType){
        if (empty($caseId) || empty($bzType)) {
            return;
        }

        $sql = "SELECT CASE_SIGN_NOPAY($caseId, $bzType) caseSignNopay FROM dual";
        $queryRet = $this->query($sql);

        return floatval($queryRet[0]['CASESIGNNOPAY']);

    }

    /**
     * ��ȡ�ʽ���ʽ����
     * @param $cid
     * @param $scaleType ��Ŀ����
     * @param int $bzType ҵ������ 1=�ѱ��� 2=�ѷ���δ���� 3=1+2
     * @return int|mixed
     */
    public function getFundPoolAmount($cid, $scaleType, $bzType = 1) {
        if (empty($cid) || empty($scaleType)) {
            return 0;
        }

        $sql = "SELECT getFundPoolAmount({$cid}, {$scaleType}, {$bzType}) FROM dual";
        $data = $this->query($sql);
        return current($data[0]);
    }

    public function getFundPoolRatio($prjId) {
        $response = array(
            'result' => false
        );

        if (intval($prjId)) {
            $houseSql = <<<HOUSE_SQL
                SELECT H.ISFUNDPOOL TYPE,
                    H.FPSCALE RATIO
                FROM ERP_HOUSE H
                WHERE H.PROJECT_ID = %d
HOUSE_SQL;
            $dbResult = $this->query(sprintf($houseSql, $prjId));
            if (notEmptyArray($dbResult)) {
                $response['result'] = true;
                $response['type'] = $dbResult[0]['TYPE'];
                $response['ratio'] = floatval($dbResult[0]['RATIO']);
            }
        }

        return $response;
    }

    public function getActivityByProjectId($prjId){
        $result = array();
        if (intval($prjId) > 0) {
            $sql = <<<ACTIVITY
                    SELECT A.ID
                    FROM ERP_ACTIVITIES A,
                         ERP_CASE B
                    WHERE A.CASE_ID = B.ID
                      AND B.PROJECT_ID = %d
ACTIVITY;
            $dbResult = D()->query(sprintf($sql, $prjId));
            if (notEmptyArray($dbResult)) {
                $result = $dbResult[0];
            }
        }

        return $result;
    }

    /**
     * ��ȡ��Ŀ����
     * @param $prjId
     * @return string
     */
    public function getProjectName($prjId) {
        $result = '';
        if (intval($prjId)) {
            $prjInfo = $this->get_info_by_id($prjId, array("PROJECTNAME", "BSTATUS", "MSTATUS", "ASTATUS", "ACSTATUS", "CPSTATUS", "SCSTATUS"));
            if (notEmptyArray($prjInfo)) {
                $result = $prjInfo[0]['PROJECTNAME'];
            }
        }

        return $result;
    }
	  /**
     * ��ȡ��Ŀ���� 
     * @param $caseid
     * 
     */

	public function get_feeCost($cid,$feeid,$bzType,$type='d')
    {
		$sql = "select getFee($cid,'$type',$feeid,$bzType,null,null)   from dual";
		$data =  $this->query($sql);
		 
		return current($data[0]);
	 }


	 /*
     * ��ȡ��Ŀҵ��� ���� 
     * @param int $id ��Ŀid
     *  
     * return 
     */
	 public function get_finalaccounts_info($id){
		$table_name = $this->tablePrefix.'FINALACCOUNTS';
 
		$conf_where = " ID=$id";
 
		  
		$res = $this->table($table_name)->where($conf_where)->find();
		return $res;
	 }
     

}

/* End of file ProjectModel.class.php */
/* Location: ./Lib/Model/ProjectModel.class.php */