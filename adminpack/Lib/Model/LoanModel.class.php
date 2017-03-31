<?php

/**
 * 借款管理类
 *
 * @author liuhu
 */
class LoanModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'LOANAPPLICATION';
    
    /***借款信息状态***/
    private  $_conf_loan_status = array(
						    		'loan_no_sub'	=> 0,			//未提交
						    		'loan_sub'		=> 1,			//已提交未审核
						    		'loan_audited'	=> 2,			//已审核
						    		'loan_audit_not_pass' => 3,		//审核未通过
                                    'have_relate_reim' => 4,        //已关联报销
						    		'loan_deleted'  => 5,   		//删除
						    		'have_part_reim'  => 6,   		//部分关联报销
    );
    
    
    /***借款信息状态***/
    private $_conf_loan_status_remark = array(
                                        0 => '未提交',
                                        1 => '审核中',
                                        2 => '已审核',
                                        3 => '审核未通过',
                                        4 => '已关联报销',
                                        5 => '已删除',
                                        6 => '部分关联报销',
    );
    
    
    //构造函数
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    /**
     * 获取借款信息状态
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_loan_status()
    {
        return $this->_conf_loan_status;
    }
    
    
    /**
     * 获取借款信息状态描述
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_loan_status_remark()
    {
        return $this->conf_loan_status_remark;
    }
    
    /**
     * 添加借款息
     * @param array $loan_info 借款信息
     * @return mixed 添加成功返回插入数据编号，插入失败返回FALSE
     */
    public function add_loan_info($loan_info)
    {   
        $insertId = FALSE;
        
    	if(is_array($loan_info) && !empty($loan_info))
    	{
    		// 自增主键返回插入ID
    		$insertId = $this->add($loan_info);
    	}
        //echo $this->getLastSql();
    	return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * 根据编号删除借款信息
     *
     * @access	protected
     * @param	mixed $ids 借款信息编号
     * @return	int 删除条数，0删除失败
     */
    public function delete_info_by_mid($ids)
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
    	 
    	$delte_num = self::delete_info_by_cond($cond_where);
    	 
    	return $delte_num > 0  ? $delte_num : FALSE;
    }
    
    
    /**
     * 删除借款信息
     *
     * @access	public
     * @param	string  $cond_where 删除条件
     * @return	mixed 删除成功返回更新条数，删除返回FALSE
     */
    public function delete_info_by_cond($cond_where)
    {	
    	$del_num = 0;
    	if($cond_where != '')
    	{
    		$del_num = $this->where($cond_where)->delete();
    		//echo $this->getLastSql();
    	}
    
    	return $del_num > 0  ? $del_num : FALSE;
    }
    
    
    /**
     * 报销申请关联借款
     *
     * @access	public
     * @param	mixed $ids 借款信息编号
     * @param	int 	$reim_listid 报销申请单编号
     * @return	mixed 删除成功返回更新条数，删除返回FALSE
     */
    public function reim_relate_loan($ids, $reim_listid)
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
    	
    	$reim_listid = intval($reim_listid);
    	if($reim_listid > 0)
    	{
    		$update_arr['REIMID'] =  $reim_listid;
            $update_arr['STATUS'] =  $this->_conf_loan_status['have_relate_reim'];
    		$audited_status = $this->_conf_loan_status['loan_audited'];
    		$cond_where .= " AND STATUS = '".$audited_status."' AND REIMID = 0";
    		$up_num = self::update_info_by_cond($update_arr, $cond_where);
    	}
    	
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    /**
     * 取消关联借款
     *
     * @access	public
     * @param	mixed $ids 借款信息编号
     * @return	mixed 成功：返回影响的行数|失败：false
     */
    public function cancle_related_loan($ids)
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
        
        $update_arr['REIMID'] = 0;
        $update_arr['STATUS'] =  $this->_conf_loan_status['loan_audited'];
        $audited_status = $this->_conf_loan_status['have_relate_reim'];
        $cond_where .= " AND STATUS = ".$audited_status;
        
        $up_num = self::update_info_by_cond($update_arr, $cond_where);
        return $up_num ? $up_num : false;
    }
    
    
    /**
     * 通过报销申请单取消关联借款
     *
     * @access	public
     * @param	int   $reim_ids 报销申请单
     * @return	mixed 成功：返回影响的行数|失败：false
     */
    public function cancle_related_loan_by_reim_ids($reim_ids)
    {
        if(is_array($reim_ids) && !empty($reim_ids))
        {
            $ids_str = implode(',', $reim_ids);
    		$cond_where = " REIMID IN (".$ids_str.")";
        }
        else
    	{
    		$id  = intval($reim_ids);
    		$cond_where = " REIMID = '".$id."'";
    	}
        
        $update_arr['REIMID'] = 0;
        $update_arr['STATUS'] =  $this->_conf_loan_status['loan_audited'];
        $audited_status = $this->_conf_loan_status['have_relate_reim'];
        $cond_where .= " AND STATUS = ".$audited_status;
        
        $up_num = self::update_info_by_cond($update_arr, $cond_where);
        return $up_num ? $up_num : false;
    }


    /**
     * 新版借款取消借款关联关系
     * @param $reim_ids
     * @return bool
     */
    public function cancleRelatedLoan($reim_ids){

        //翻译参数
        if(is_array($reim_ids) && !empty($reim_ids))
        {
            $ids_str = implode(',', $reim_ids);
            $cond_where = " REIMID IN (".$ids_str.")";
        }
        else
        {
            $id  = intval($reim_ids);
            $cond_where = " REIMID = '".$id."'";
        }

        //通过reim-ids获取loan_ids
        $sql = 'SELECT ID FROM ERP_REIMLOAN WHERE ' . $cond_where;

        $loan_ids = D()->query($sql);

        /***取消关联借款***/

        $cancleFlag = false;
        D("Loan")->startTrans();
        foreach($loan_ids as $key=>$val){

            //获取金额
            $sql = 'SELECT MONEY,LOANID FROM ERP_REIMLOAN WHERE ID = ' . $loan_ids[$key]['ID'];
            $queryRet = D("Loan")->query($sql);
            $reimMoney = $queryRet[0]['MONEY'];
            $loanId = $queryRet[0]['LOANID'];

            if(!$reimMoney || !$loanId)
            {
                $cancleFlag  = true;
                break;
            }

            //删除关系数据
            $sql = 'DELETE FROM ERP_REIMLOAN WHERE ID = ' . $loan_ids[$key]['ID'];
            $deleteRet = D("Loan")->query($sql);

            if($deleteRet===false){
                $cancleFlag  = true;
                break;
            }

            //更新状态和金额
            $loanStatus = 2;
            $sql = 'SELECT UNREPAYMENT,AMOUNT FROM ERP_LOANAPPLICATION WHERE ID = ' . $loanId;
            $queryRet = D("Loan")->query($sql);
            $amount = $queryRet[0]['AMOUNT'];
            $unRepayment = $queryRet[0]['UNREPAYMENT'];

            if(!$amount){
                $cancleFlag  = true;
                break;
            }

            if($amount-$unRepayment > $reimMoney){
                $loanStatus = 6;
            }

            $sql = 'UPDATE ERP_LOANAPPLICATION SET STATUS = ' . $loanStatus . ',UNREPAYMENT = UNREPAYMENT + ' . $reimMoney . ' WHERE ID = ' . $loanId;
            $updateRet = D("Loan")->query($sql);

            if($updateRet===false){
                $cancleFlag  = true;
                break;
            }
        }

        if($cancleFlag){
            D("Loan")->rollback();
        }else{
            D("Loan")->commit();
        }

        return !$cancleFlag;

        /***取消关联借款***/
    }

    /**
     * 判断关联借款是否大于报销金额
     * @param $reimId
     * @param $delReimDetailID
     * @return bool
     */
    public function checkDelReim($reimId,$delReimDetailID){
        $resStatus = false;

        //获取报销金额
        $cond_where = "LIST_ID = $reimId AND STATUS != 4 AND ID != $delReimDetailID";
        $sql = 'SELECT SUM(MONEY) AS SUMMONEY FROM ERP_REIMBURSEMENT_DETAIL WHERE ' .  $cond_where;
        $queryRet = D()->query($sql);
        $sumMoney = $queryRet[0]['SUMMONEY'];

        //获取关联借款金额
        $sql = "SELECT SUM(MONEY) AS LOANMONEY FROM ERP_REIMLOAN WHERE REIMID = $reimId";
        $queryRet = D()->query($sql);
        $loanMoney = $queryRet[0]['LOANMONEY'];

        if($loanMoney > $sumMoney)
            $resStatus = true;

        return $resStatus;
    }

    
    /**
     * 更新借款信息
     *
     * @access	public
     * @param	mixed  $ids 单个ID或者ID数组
     * @param	array  $update_arr 需要更新字段的键值对
     * @return	mixed  更新成功返回更新条数，失败返回FALSE
     */
    public function update_info_by_id($ids, $update_arr)
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
    
    	$up_num = self::update_info_by_cond($update_arr, $cond_where);
    
    	return $up_num > 0  ? $up_num : FALSE;
    }
    
    
    /**
     * 更新发放信息
     *
     * @access	public
     * @param	array  $update_arr  需要更新字段的键值对
     * @param	string  $cond_where 更新条件
     * @return	mixed 更新成功返回更新条数，失败返回FALSE
     */
    public function update_info_by_cond($update_arr, $cond_where)
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
     * 根据条件获取信息
     *
     * @access	public
     * @param	mixed  $ids 发放记录ID
     * @param array $search_field 搜索字段
     * @return	array 
     */
    public function get_info_by_id($ids, $search_field = array())
    {
        $info = array();
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
        
        $info = self::get_info_by_cond($cond_where, $search_field);
        
        return $info;
    }
    
    
    /**
     * 根据条件获取信息
     *
     * @access	public
     * @param	string  $cond_where 查询条件
     * @param array $search_field 搜索字段
     * @return	array 
     */
    public function get_info_by_cond($cond_where, $search_field = array())
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

    public function getPayTypeList() {
        return array(
            1 => '现金',
            2 => '网银',
            3 => '支票'
        );
    }
}

/* End of file LoanModel.class.php */
/* Location: ./Lib/Model/LoanModel.class.php */