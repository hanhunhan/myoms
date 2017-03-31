<?php

/**
 * 案例模块
 *
 * @author liuhu
 */
class ProjectCaseModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'CASE';
    
    /***项目业务类型***/
    private  $_conf_case_type = array(
                                        'ds' => 1,   //电商
                                        'fx' => 2,   //分销
                                        'yg' => 3,   //硬广
                                        'hd' => 4,   //独立活动
                                        'cp' => 5,   //产品
                                        'xmxhd' => 7,  //项目下活动,
                                        'fwfsc' => 8  // 非我方收筹
                                    );
    
    /***项目业务类型***/
    private  $_conf_case_type_remark = array(
                                            1 => '电商',
                                            2 => '分销',
                                            3 => '硬广',
                                            4 => '活动',
                                            5 => '产品',
                                            7 => '项目活动',
											8 => '非我方收筹',
                                        );

    /***垫资比例属性业务类型***/
    private  $_conf_case_Loan = array(
                    1 => '电商',
                    2 => '分销',
                    8 => '非我方收筹',
                );

    // 2 = 执行中
    // 3 = 办结
    // 4 = 项目周期结束
    protected $arrExecStatus = array(2,3, 4);

    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    /**
     * 获取项目业务类型
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
     * 获取项目业务类型描述
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
     * 获取项目业务类型(具备垫资比例属性)
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
     * 根据案例编号获取案例信息
     *
     * @access	public
     * @param  mixed $cids 案例编号
     * @param array $search_field 搜索字段
     * @return	array 案例信息
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
     * 根据项目编号获取案例信息
     *
     * @access	public
     * @param  mixed $ids 项目编号
     * @param	string  $case_type 案例类型字符描述(ds\fx\yg……)
     * @param array $search_field 搜索字段
     * @return	array 项目信息
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
     * 根据项目编号查询是否存在某种业务类型
     *
     * @access	public
     * @param	int  $prj_id 项目编号
     * @param  string $case_type 业务类型字符串描述
     * @return	boolean 存在返回TRUE,不存在返回FALSE
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
     * 根据条件获取项目案例信息
     *
     * @access	public
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 项目信息
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
     *业务类型状态变更 决算 终止 
     * @param int $caseid 业务类型id
     *  
     * return 
     */
	 public function update_case_status($id,$status){
		$table_name = $this->tablePrefix.'FINALACCOUNTS';
		$cond_where = "ID='$id'";
		$one = $this->table($table_name)->where($cond_where)->find();//FINALACCOUNTS

		//$table_name = $this->tablePrefix.'CASE';
		$conf_where = "ID= ".$one['CASE_ID'];
		if( in_array($status,array(3,5)) ) $conf_where .= ' or PARENTID = '.$one['CASE_ID'];//决算或 办结的时候同时更新项目下活动的case记录状态
		$arr['FSTATUS'] = $status;
		$res = $this->where($conf_where)->save($arr); 
		return $res;
	 }
 

	 /*
     *业务类型状态变更  立项
     * @param int $caseid 业务类型id
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
     * 根据案例编号获取案例类型
     *
     * @access	public
     * @param	int  $cid 案例编号
     * @param	int  $level 类型层级 0案例本身类型,1、案例父级案例类型
     * @return	string 案例类型
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
     * 根据活动id更新案例状态
     *
     * @access	public
     * @param	int  $activitiesId 活动id
     * @param	int  $status  状态值
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
     * 更新客户经理
     * @param $proId 项目ID
     * @param $uId  用户ID
     * @return bool
     */
    public function updateProMan($proId,$uId){
        //更新项目表
        $sql = 'UPDATE ERP_PROJECT SET CUSER = ' . $uId . ' WHERE ID = ' . $proId;
        $updatePro = D()->query($sql);

        //更新House表
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
                    $result['1'] = "电商";
                    break;
                case "2":
                    $result['2'] = "分销";
                    break;
                case "3":
                    $result['3'] = "硬广";
                    break;
                case "4":
                    $result['4'] = "活动";
                    break;
                case "5":
                    $result['5'] = "产品";
                    break;
                case "7":
                    $result['7'] = "项目活动";
                    break;
                case "8":
                    $result['8'] = "非我方收筹";
                    break;

            }
        }
        return $result;
    }
}

/* End of file ProjectCaseModel.class.php */
/* Location: ./Lib/Model/ProjectCaseModel.class.php */