<?php

/**
 * ��������
 *
 * @author liuhu
 */
class LoanModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'LOANAPPLICATION';
    
    /***�����Ϣ״̬***/
    private  $_conf_loan_status = array(
						    		'loan_no_sub'	=> 0,			//δ�ύ
						    		'loan_sub'		=> 1,			//���ύδ���
						    		'loan_audited'	=> 2,			//�����
						    		'loan_audit_not_pass' => 3,		//���δͨ��
                                    'have_relate_reim' => 4,        //�ѹ�������
						    		'loan_deleted'  => 5,   		//ɾ��
						    		'have_part_reim'  => 6,   		//���ֹ�������
    );
    
    
    /***�����Ϣ״̬***/
    private $_conf_loan_status_remark = array(
                                        0 => 'δ�ύ',
                                        1 => '�����',
                                        2 => '�����',
                                        3 => '���δͨ��',
                                        4 => '�ѹ�������',
                                        5 => '��ɾ��',
                                        6 => '���ֹ�������',
    );
    
    
    //���캯��
    public function __construct()
    {
    	parent::__construct();
    }
    
    
    /**
     * ��ȡ�����Ϣ״̬
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
     * ��ȡ�����Ϣ״̬����
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
     * ��ӽ��Ϣ
     * @param array $loan_info �����Ϣ
     * @return mixed ��ӳɹ����ز������ݱ�ţ�����ʧ�ܷ���FALSE
     */
    public function add_loan_info($loan_info)
    {   
        $insertId = FALSE;
        
    	if(is_array($loan_info) && !empty($loan_info))
    	{
    		// �����������ز���ID
    		$insertId = $this->add($loan_info);
    	}
        //echo $this->getLastSql();
    	return !empty($insertId) && $insertId > 0 ?  $insertId : FALSE ;
    }
    
    
    /**
     * ���ݱ��ɾ�������Ϣ
     *
     * @access	protected
     * @param	mixed $ids �����Ϣ���
     * @return	int ɾ��������0ɾ��ʧ��
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
     * ɾ�������Ϣ
     *
     * @access	public
     * @param	string  $cond_where ɾ������
     * @return	mixed ɾ���ɹ����ظ���������ɾ������FALSE
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
     * ��������������
     *
     * @access	public
     * @param	mixed $ids �����Ϣ���
     * @param	int 	$reim_listid �������뵥���
     * @return	mixed ɾ���ɹ����ظ���������ɾ������FALSE
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
     * ȡ���������
     *
     * @access	public
     * @param	mixed $ids �����Ϣ���
     * @return	mixed �ɹ�������Ӱ�������|ʧ�ܣ�false
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
     * ͨ���������뵥ȡ���������
     *
     * @access	public
     * @param	int   $reim_ids �������뵥
     * @return	mixed �ɹ�������Ӱ�������|ʧ�ܣ�false
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
     * �°���ȡ����������ϵ
     * @param $reim_ids
     * @return bool
     */
    public function cancleRelatedLoan($reim_ids){

        //�������
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

        //ͨ��reim-ids��ȡloan_ids
        $sql = 'SELECT ID FROM ERP_REIMLOAN WHERE ' . $cond_where;

        $loan_ids = D()->query($sql);

        /***ȡ���������***/

        $cancleFlag = false;
        D("Loan")->startTrans();
        foreach($loan_ids as $key=>$val){

            //��ȡ���
            $sql = 'SELECT MONEY,LOANID FROM ERP_REIMLOAN WHERE ID = ' . $loan_ids[$key]['ID'];
            $queryRet = D("Loan")->query($sql);
            $reimMoney = $queryRet[0]['MONEY'];
            $loanId = $queryRet[0]['LOANID'];

            if(!$reimMoney || !$loanId)
            {
                $cancleFlag  = true;
                break;
            }

            //ɾ����ϵ����
            $sql = 'DELETE FROM ERP_REIMLOAN WHERE ID = ' . $loan_ids[$key]['ID'];
            $deleteRet = D("Loan")->query($sql);

            if($deleteRet===false){
                $cancleFlag  = true;
                break;
            }

            //����״̬�ͽ��
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

        /***ȡ���������***/
    }

    /**
     * �жϹ�������Ƿ���ڱ������
     * @param $reimId
     * @param $delReimDetailID
     * @return bool
     */
    public function checkDelReim($reimId,$delReimDetailID){
        $resStatus = false;

        //��ȡ�������
        $cond_where = "LIST_ID = $reimId AND STATUS != 4 AND ID != $delReimDetailID";
        $sql = 'SELECT SUM(MONEY) AS SUMMONEY FROM ERP_REIMBURSEMENT_DETAIL WHERE ' .  $cond_where;
        $queryRet = D()->query($sql);
        $sumMoney = $queryRet[0]['SUMMONEY'];

        //��ȡ���������
        $sql = "SELECT SUM(MONEY) AS LOANMONEY FROM ERP_REIMLOAN WHERE REIMID = $reimId";
        $queryRet = D()->query($sql);
        $loanMoney = $queryRet[0]['LOANMONEY'];

        if($loanMoney > $sumMoney)
            $resStatus = true;

        return $resStatus;
    }

    
    /**
     * ���½����Ϣ
     *
     * @access	public
     * @param	mixed  $ids ����ID����ID����
     * @param	array  $update_arr ��Ҫ�����ֶεļ�ֵ��
     * @return	mixed  ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ���·�����Ϣ
     *
     * @access	public
     * @param	array  $update_arr  ��Ҫ�����ֶεļ�ֵ��
     * @param	string  $cond_where ��������
     * @return	mixed ���³ɹ����ظ���������ʧ�ܷ���FALSE
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
     * ����������ȡ��Ϣ
     *
     * @access	public
     * @param	mixed  $ids ���ż�¼ID
     * @param array $search_field �����ֶ�
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
     * ����������ȡ��Ϣ
     *
     * @access	public
     * @param	string  $cond_where ��ѯ����
     * @param array $search_field �����ֶ�
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
            1 => '�ֽ�',
            2 => '����',
            3 => '֧Ʊ'
        );
    }
}

/* End of file LoanModel.class.php */
/* Location: ./Lib/Model/LoanModel.class.php */