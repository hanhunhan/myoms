<?php

/**
 * �������
 *
 * @author hhh
 */
class LoanAction extends ExtendAction{
    /**
     * �ύȨ��
     */
    const ADD_FLOW = 620;

    /**
     * ȡ������Ȩ��
     */
    const CANCEL_RELATED_LOAN = 361;

    /*�ϲ���ǰģ���URL����*/
    private $_merge_url_param = array();
	private $UserLog;

    private $loanAppOptions = array(
        '_add' => array(
            'default' => 619,
        ),
        '_check' => array(
            'default' => 621
        ),
        '_edit' => array(
            'default' => 712
        ),
        '_del' => array(
            'default' => 622
        )
    );
    
    //���캯��
    public function __construct() 
    {
        parent::__construct();
		Vendor('Oms.UserLog');
		$this->UserLog = UserLog::Init();
        // Ȩ��ӳ���
        $this->authorityMap = array(
            'add_flow' => self::ADD_FLOW,
            'cancel_related_loan' => self::CANCEL_RELATED_LOAN
        );

        //TAB URL����
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        !empty($_GET['purchase_id']) ? $this->_merge_url_param['purchase_id'] = $_GET['purchase_id'] : ''; 
		!empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] =  intval($_GET['RECORDID']) : '';
		!empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] =  intval($_GET['CASEID']) : '';
		!empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] =  intval($_GET['flowId']) : '';
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = strip_tags($_GET['operate']) : '';
    }
    
    
    /**
    +----------------------------------------------------------
    *  �������
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
	public function loan_application()
    {
        //�û���Ϣ
        $uid = $_SESSION['uinfo']['uid'];
        $userName = $_SESSION['uinfo']['tname'];
        //����ID
        $cityId = intval($_SESSION['uinfo']['city']);
        //չ����ʽ
        $showForm = intval($_REQUEST['showForm']);
        $modifyId = intval($_REQUEST['ID']);
        // ��������
        $act = isset($_REQUEST['act'])?trim($_REQUEST['act']):'';

        //��ȡ��ͬ���
        if($act=='getContract'){

            $response = array(
                'status'=>false,
                'msg'=>'',
                'data'=>null,
            );

            $pId = isset($_REQUEST['pId'])?trim($_REQUEST['pId']):0;
            $res = D()->query('SELECT CONTRACT FROM ERP_PROJECT WHERE ID = ' . $pId);

            if($res){
                $response['status'] = true;
                $response['data']['contract'] = g2u($res[0]['CONTRACT']);
            }

            die(@json_encode($response));
        }


        //����������״̬
        if($act=='updateStatus'){

            $response = array(
                'status'=>false,
                'msg'=>'',
                'data'=>null,
            );

            $loanId = isset($_REQUEST['loanId'])?trim($_REQUEST['loanId']):0;

            //������֤
            $sql = 'SELECT STATUS FROM ERP_LOANAPPLICATION WHERE ID = ' . $loanId;
            $queryRet = D()->query($sql);
            $status = $queryRet[0]['STATUS'];

            if($status != 2 && $status != 6){
                $response['msg'] = g2u('�Բ���ֻ�ܽ�"�����"��"���ֹ�������"������"�ѹ�������"״̬��');
                die(@json_encode($response));
            }

            //����״ֵ̬
            $sql = 'UPDATE ERP_LOANAPPLICATION SET STATUS = 4,UNREPAYMENT = 0 WHERE ID = ' . $loanId;
            $updateRet = D()->query($sql);

            if($updateRet!==false){
                $response['status'] = true;
                $response['msg'] = g2u('״̬����ɹ�!');

            }else{
                $response['msg'] = g2u('״̬���ʧ��!');

            }
            die(@json_encode($response));
        }

        //���Model
//        $loan_model = D("Loan");
//        $loan_status = $loan_model->get_conf_loan_status();

        //��ʼ��
        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(153);

        //���ӵ��ȫ��Ȩ��
        if($this->p_auth_all) {
            //where����
            $form->where(" CITY_ID = '" . $this->channelid . "'");
        }else{
            $form->SQLTEXT = '(SELECT L.ID,L.CITY_ID,L.PAYTYPE,T.NAME AS CITYNAME,P.ID AS PID,P.PROJECTNAME,P.CONTRACT,L.STATUS,L.AMOUNT,L.REPAY_TIME,L.UNREPAYMENT,L.RESON,L.APPLICANT,U.NAME AS USERNAME,APPDATE FROM ERP_LOANAPPLICATION L
LEFT JOIN ERP_CITY T ON L.CITY_ID = T.ID
LEFT JOIN ERP_PROJECT P ON L.PID = P.ID
LEFT JOIN ERP_USERS U ON L.APPLICANT = U.ID)';

            $form->where("APPLICANT = '" . $uid . "' AND CITY_ID = '" . $this->channelid . "'");
        }

        //��������
        if($_REQUEST['faction'] == 'saveFormData')
        {
            //���ݼ��
            //��������ĳ��Ŀ�´���һ��δ��ȫ�������ʱ��������ڸ���Ŀ���޷��ٴ�������
            $pId = isset($_REQUEST['PID'])?intval($_REQUEST['PID']):0;

            //����
            if(!$modifyId)
                $sql = 'SELECT ID FROM ERP_LOANAPPLICATION WHERE PID = ' . $pId . ' AND APPLICANT = ' . $uid . ' AND STATUS IN(0,1,2,6)';
            //�༭
            else
                $sql = 'SELECT ID FROM ERP_LOANAPPLICATION WHERE PID = ' . $pId . ' AND APPLICANT = ' . $uid . ' AND STATUS IN(0,1,2,6) AND ID != ' . $modifyId;

            $ret = D()->query($sql);

            if($ret){
                $result['status'] = 0;
                $result['msg'] = g2u('�Բ��𣬸���Ŀ�����Ѿ�����һ�ʡ�δ��ȫ����������״̬�Ľ�������ʧ�ܣ�');
                die(json_encode($result));
            }

            $form->SQLTEXT = 'ERP_LOANAPPLICATION';

            //��ȡcontract
            $sql = 'SELECT CONTRACT,PROJECTNAME FROM ERP_PROJECT WHERE ID = ' . $pId;
            $queryRet = D()->query($sql);
            $contract = $queryRet[0]['CONTRACT'];
            $projectName = $queryRet[0]['PROJECTNAME'];

            $form->setMyFieldVal('CITY_ID', $cityId, TRUE);
            //δ�ύ������
            $form->setMyFieldVal('STATUS', '0', TRUE);
            //��Ŀcontract
            $form->setMyFieldVal('CONTRACT', $contract, TRUE);
            $form->setMyFieldVal('PROJECTNAME', g2u($projectName), TRUE);
            //δ�ؿ���
            $form->setMyFieldVal('UNREPAYMENT', floatval($_REQUEST['AMOUNT']), TRUE);
            $form->setMyFieldVal('APPDATE', date('Y-m-d H:i:s'), TRUE);
            $form->setMyFieldVal('REIMID', '0', TRUE);
        }

        //ɾ������
        if($_REQUEST['faction'] == 'delData'){
            $form->SQLTEXT = 'ERP_LOANAPPLICATION';
        }

        //�����������༭״̬�����������ˣ�
        if($showForm == 1 || $showForm == 3) {
            $form->setMyFieldVal('APPLICANT', $uid, TRUE);
            $form->setMyFieldVal('USERNAME', $userName, TRUE);

            //��Ŀ�б�չ��
            $user_model = M('erp_users');
            $form->setMyField('PID', 'EDITTYPE', '23', FALSE);

            if($this->p_auth_all) {
                $proListSql = 'SELECT DISTINCT ID,PROJECTNAME FROM(
                SELECT P.ID,P.PROJECTNAME FROM ERP_PROJECT P
LEFT JOIN ERP_CASE C ON P.ID = C.PROJECT_ID
WHERE P.STATUS != 2 AND P.PSTATUS = 3 AND P.PROJECTNAME IS NOT NULL AND P.CITY_ID = ' .  $this->channelid . ' AND C.SCALETYPE != 7 AND C.FSTATUS IN(2,4)
) UNION SELECT ID,PROJECTNAME FROM ERP_PROJECT WHERE ASTATUS IN(2,4) AND STATUS != 2 AND CITY_ID = ' .  $this->channelid . ' AND PROJECTNAME IS NOT NULL';
            }else{
                $proListSql = 'SELECT ID,PROJECTNAME FROM (SELECT DISTINCT ID,PROJECTNAME FROM(
                SELECT P.ID,P.PROJECTNAME FROM ERP_PROJECT P
LEFT JOIN ERP_CASE C ON P.ID = C.PROJECT_ID
WHERE P.STATUS != 2 AND P.PSTATUS = 3 AND P.PROJECTNAME IS NOT NULL AND P.CITY_ID = ' .  $this->channelid . ' AND C.SCALETYPE != 7 AND C.FSTATUS IN(2,4)
) UNION SELECT ID,PROJECTNAME FROM ERP_PROJECT WHERE ASTATUS IN(2,4) AND STATUS != 2 AND CITY_ID = ' .  $this->channelid . ' AND PROJECTNAME IS NOT NULL)
E INNER JOIN (SELECT PRO_ID FROM ERP_PROROLE WHERE ISVALID = -1 AND USE_ID = '. $uid . ' GROUP BY PRO_ID) R ON E.ID = R.PRO_ID';
            }
            $form->setMyField('PID', 'LISTSQL', $proListSql, FALSE);

            $proOptions = addslashes(u2g($form->getSelectTreeOption('PID', '', -1)));

            $this->assign('proOptions', $proOptions);

        }

        if($this->_get('layer') == 1)
        {
            //$form->where("STATUS = 2 AND REIMID is null"); //���ͨ���Ľ������
            $form->SHOWCHECKBOX     = -1;
            $form->GRIDAFTERDATA    = '<div class="handle-btn">'
                    . '<input type="button" value="ȷ&nbsp;��" onclick="parent.submitloanapplication();" class="btn-blue" />  '
                    . '<input type="button" value="��&nbsp;��" class="btn-gray  j-pageclose" onclick="parent.layer.closeAll();" />'
                    . '</div>';
            $form->ADDABLE = 0;
        }
        else
        {
            //����
            $form->ADDABLE = -1;
            //ɾ��
            $form->DELCONDITION = '%STATUS% == 0';
            //�༭
            $form->EDITCONDITION = '%STATUS% == 0';
            //������ť
            $form->GABTN = '<a href="javascript:void(0);" onclick="addflow();" class="btn btn-info btn-sm" id="add_flow">�ύ����</a> ';
            $form->GABTN .= '<a href="javascript:void(0);" onclick="statusEdit();" class="btn btn-info btn-sm" id="status_edit">���״̬�༭</a> ';
        }

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->loanAppOptions);
        $formHtml = $form->getResult();
        
        $this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->assign('layer', $this->_get('layer') );
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('loan_application');
    }
    
    
    /**
     +----------------------------------------------------------
     *  �����Ľ������
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function related_loan()
    {
    	$list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
        $rlId = isset($_GET['RLID'])?intval($_GET['RLID']):0;
        $uid = intval($_SESSION['uinfo']['uid']);
    	
    	Vendor('Oms.Form');
    	$form = new Form();
    	$form->initForminfo(209);

        //�������SQL
        $form->SQLTEXT = '(SELECT R.ID AS RLID,L.ID,L.CITY_ID,R.MONEY AS LOANMONEY,R.REIMID,L.PAYTYPE,T.NAME AS CITYNAME,P.ID AS PID,P.CONTRACT,L.STATUS,L.AMOUNT,L.REPAY_TIME,L.UNREPAYMENT,L.RESON,L.APPLICANT,U.NAME AS USERNAME,APPDATE FROM ERP_LOANAPPLICATION L
LEFT JOIN ERP_CITY T ON L.CITY_ID = T.ID
LEFT JOIN ERP_PROJECT P ON L.PID = P.ID
LEFT JOIN ERP_USERS U ON L.APPLICANT = U.ID
RIGHT JOIN ERP_REIMLOAN R ON L.ID = R.LOANID WHERE R.REIMID = ' . $list_id . ')';
        //�������
        $form->PKFIELD = 'RLID';
        $form->PKVALUE = $rlId;

        //�������뵥��Ϣ
        $reim_list = D('ReimbursementList');
        $reim_list_info = array();
        $reim_list_info = $reim_list->get_info_by_id($list_id, array('STATUS'));
        $conf_reim_list_status = $reim_list->get_conf_reim_list_status();
        
        if($this->_merge_url_param['flowId'] > 0)
        {
            //��������ڱ༭Ȩ��
            $flow_edit_auth = judgeFlowEdit($this->_merge_url_param['flowId'], $uid);
            
            //reim_list_no_sub
            if($flow_edit_auth)
            {   
                //����༭
                $form->EDITABLE = -1;
                if($reim_list_info[0]['STATUS'] == $conf_reim_list_status['reim_list_no_sub'])
                {
                    $form->GABTN = "<a href='javascript:;' onclick='cancle_related_loan();' id='cancel_related_loan' class='btn btn-info btn-sm'>ȡ������</a>";
                }
                else
                {
                    $form->GABTN = "";
                }
                $form->ADDABLE = '0';
            }
            else
            {
                $form->DELCONDITION = '1==0';
                $form->EDITCONDITION = '1==0';
                $form->ADDABLE = 0; 
                $form->GABTN = '';
            }
        }
        else
        {
            $form->ADDABLE = 0;
            $form->DELCONDITION = '1==0';
            $form->EDITCONDITION = '1==0';
            if($reim_list_info[0]['STATUS'] == $conf_reim_list_status['reim_list_no_sub'])
            {
                $form->GABTN = "<a href='javascript:;' onclick='cancle_related_loan();' id='cancel_related_loan' class='btn btn-info btn-sm'>ȡ������</a>";
            }
            else
            {
                $form->GABTN = "";
            }
        }

        //$form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // ��ťǰ��
    	$formHtml = $form->getResult();

        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
    	$this->assign('form', $formHtml);
    	$this->assign('paramUrl', $this->_merge_url_param);
    	$this->display('related_loan');
    }
    
    
    /**
     +----------------------------------------------------------
     *  �ҵĽ���б�
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function my_loan_list()
    {
    	$uid = intval($_SESSION['uinfo']['uid']);
    	$cityId = $this->channelid;
    	$reim_list_id = !empty($_GET['reim_list_id']) ? intval($_GET['reim_list_id']) : 0;
    	
    	Vendor('Oms.Form');
    	$form = new Form();
        $form = $form->initForminfo(153);

        $form->SQLTEXT = "(SELECT L.ID,L.CITY_ID,L.PAYTYPE,T.NAME AS CITYNAME,P.ID AS PID,P.CONTRACT,L.STATUS,L.AMOUNT,L.REPAY_TIME,L.UNREPAYMENT,L.RESON,L.APPLICANT,U.NAME AS USERNAME,APPDATE FROM ERP_LOANAPPLICATION L
        LEFT JOIN ERP_CITY T ON L.CITY_ID = T.ID
        LEFT JOIN ERP_PROJECT P ON L.PID = P.ID
        LEFT JOIN ERP_USERS U ON L.APPLICANT = U.ID WHERE L.APPLICANT = '".$uid."' AND L.CITY_ID = '".$cityId."' AND L.STATUS IN (2,6))";

        //�����ֶ�
        $input_arr = array(
            array('TDNAME' => '�������', 'INPUTNAME' => 'associated_amount','TYPE'=>'INPUT'),
        );
        $form->addNewTd($input_arr);

        $form->SHOWCHECKBOX = '-1';
        $form->DELCONDITION = '1 == 0';
        $form->EDITCONDITION = '%STATUS% == 0';

        //��ȡ�������
        $sql = "SELECT AMOUNT FROM ERP_REIMBURSEMENT_LIST WHERE ID = " . $reim_list_id;
        $ret = D()->query($sql);
        if($ret){
            $reimMoney = $ret[0]['AMOUNT'];
        }

    	//�޸ĵײ���ť
    	$form->GABTN = '<a id = "reim_relate_loan" href="javascript:;" class="btn btn-info btn-sm">�������</a>';
    	$form = $form->getResult();
    	$this->assign('form', $form);
    	$this->assign('paramUrl', $this->_merge_url_param);
    	$this->assign('reim_list_id', $reim_list_id);
    	$this->assign('reimMoney', $reimMoney);
    	$this->display('my_loan_list');
    }
    
    
    /**
     +----------------------------------------------------------
     *  �첽�������
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function ajax_relate_loan()
    {
		//��Ҫ�����ı�����ID
		$reim_list_id = !empty($_GET['reim_list_id']) ? 
						intval($_GET['reim_list_id']) : 0;
		
		//�����Ľ��ID
		$loan_id_arr = !empty($_GET['loanId']) ? 
						$_GET['loanId'] : array();

		//�����Ľ����
		$loan_money_arr = !empty($_GET['loanMoney']) ?
						$_GET['loanMoney'] : array();

        $res = array(
            'status'=>false,
            'msg'=>'',
            'data'=>null,
        );

        /***������֤***/
        $error_str = '';
        $moneyTotal = 0;

        //��ȡ�������
        $sql = "SELECT AMOUNT FROM ERP_REIMBURSEMENT_LIST WHERE ID = " . $reim_list_id;
        $ret = D()->query($sql);
        if($ret){
            $reimMoney = $ret[0]['AMOUNT'];
        }

        //��ȡ�ѹ������
        $sql = 'SELECT SUM(MONEY) AS LOANMONEY FROM ERP_REIMLOAN WHERE REIMID = ' . $reim_list_id;
        $queryRet = D()->query($sql);
        $loanMoney = $queryRet[0]['LOANMONEY'];

        foreach($loan_id_arr as $key=>$val){
            if(floatval($loan_money_arr[$key])==0){
                $error_str .= '��' . ($key+1) . '��,���Ϊ��!' . "\n";
            }
            $moneyTotal += floatval($loan_money_arr[$key]);
        }

        if($moneyTotal + $loanMoney>$reimMoney){
            $error_str .= '�Բ��𣬹��������ڱ�������˲�!';
        }

        //�������
        if($error_str){
            $res['status'] = false;
            $res['msg'] = g2u($error_str);
            die(@json_encode($res));
        }

        /***������֤***/

        //���ݴ���
		if ($reim_list_id > 0 && is_array($loan_id_arr) && !empty($loan_id_arr) && is_array($loan_money_arr))
    	{
    		//������״̬
            $disable_relate_num = 0;
            $reim_list_info = array();
    		$reim_list_model = D('ReimbursementList');
    		$reim_list_info = $reim_list_model->get_info_by_id($reim_list_id);
    		
    		if(is_array($reim_list_info) && !empty($reim_list_info))
    		{	
    			$conf_reim_list_status = $reim_list_model->get_conf_reim_list_status();
    			foreach($reim_list_info as $key => $value)
    			{
    				if($value['STATUS'] != $conf_reim_list_status['reim_list_no_sub'])
    				{
    					$disable_relate_num ++;
    				}
    			}
    		}

            //����״̬��֤
    		if($disable_relate_num > 0)
    		{
    			$res['msg']  = g2u('����ʧ��,δ�ύ״̬�ı������뵥������������');
    			die(@json_encode($res));
    		}

            //���±�����
			$loan_model = D('Loan');
    		//$update_num = $loan_model->reim_relate_loan($loan_id_arr, $reim_list_id);

            /**����������**/
            D('Loan')->startTrans();

            $backFlag = false;
            foreach($loan_id_arr as $k=>$v) {
                //����һ����������
                $sql = "INSERT INTO ERP_REIMLOAN(REIMID,LOANID,MONEY) VALUES($reim_list_id,$loan_id_arr[$k],$loan_money_arr[$k])";
                $insertRet = D('Loan')->query($sql);
                if($insertRet===false){
                    $backFlag = true;
                }

                //����״̬�ͽ��
                $updateStatus = 4;
                $sql = "SELECT UNREPAYMENT FROM ERP_LOANAPPLICATION WHERE ID = " . $loan_id_arr[$k];
                $queryRet = D('Loan')->query($sql);
                $unRepayment = $queryRet['0']['UNREPAYMENT'];

                if(!$unRepayment){
                    $backFlag = true;
                }

                //����ǲ��ֹ�����ô���ֵҲ�ǲ���
                if($unRepayment > $loan_money_arr[$k]){
                    $updateStatus = 6;
                }

                $sql = 'UPDATE ERP_LOANAPPLICATION SET UNREPAYMENT=UNREPAYMENT-' . $loan_money_arr[$k] . ',STATUS = ' . $updateStatus . ' WHERE ID = ' . $loan_id_arr[$k];
                $updateRet = D('Loan')->query($sql);

                if($updateRet===false){
                    $backFlag = true;
                }
            }
            /**����������**/

            if($backFlag){
                D('Loan')->rollback();
                $res['msg']  = g2u('����ʧ��');
                $this->UserLog->writeLog($reim_list_id,$_SERVER["REQUEST_URI"],"�������ʧ��" ,serialize($loan_id_arr));
            }else{
                D('Loan')->commit();
                $res['status']  = true;
                $res['msg']  = g2u('�����ɹ�');
                $this->UserLog->writeLog($reim_list_id,$_SERVER["REQUEST_URI"],"�������ɹ�" ,serialize($loan_id_arr));
            }
    	}
    	else
    	{
            $res['msg']  = g2u('��������');
    	}

        die(@json_encode($res));
    }
    
    /**
     +----------------------------------------------------------
     *  �첽ȡ���������
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function cancle_related_loan()
    {
        //Loanʵ����
        $loan_model = D("Loan");
        //����ID
        $loan_ids = $_REQUEST["loan_id"] ? $_REQUEST["loan_id"] : "";

        $res = array(
            'status' => false,
            'msg' => '',
            'data' => null,
        );
        
        if($loan_ids)
        {
            /***������֤***/

            //�жϹ������ı������뵥�Ƿ��Ѿ��ύ
            $loanIds = implode(',',$loan_ids);
            $sql = 'SELECT REIMID FROM ERP_REIMLOAN WHERE ID IN(' . $loanIds . ')';
            $reimIdInfo = D("Loan")->query($sql);
            
            $reim_id_arr = array();
            if(!empty($reimIdInfo) && is_array($reimIdInfo))
            {
                foreach($reimIdInfo as $key => $value )
                {
                    $reim_id_arr[] = $value['REIMID'];
                }
            }

            //��ȡ����ID
            $reim_list_id_arr = !empty($reim_id_arr) ? array_unique($reim_id_arr) : array();
            
            if(!empty($reim_list_id_arr))
            {
                $reim_list_model = D('ReimbursementList');
                $reim_list_info = $reim_list_model->get_info_by_id($reim_list_id_arr, array('STATUS'));
                $conf_reim_list_status = $reim_list_model->get_conf_reim_list_status();
                
                if(!empty($reim_list_info) && is_array($reim_list_info))
                {
                    foreach($reim_list_info as $key => $value )
                    {   
                        if($value['STATUS'] != $conf_reim_list_status['reim_list_no_sub'])
                        {
                            $res["msg"] = g2u("ȡ��ʧ�ܣ����ύ�����ı�����������ȡ���������");
                            die(@json_encode($res));
                        }
                    }
                }
            }

            /***������֤***/

            /***ȡ���������***/

            $cancleFlag = false;
            D("Loan")->startTrans();
            foreach($loan_ids as $key=>$val){

                //��ȡ���
                $sql = 'SELECT MONEY,LOANID FROM ERP_REIMLOAN WHERE ID = ' . $loan_ids[$key];
                $queryRet = D("Loan")->query($sql);
                $reimMoney = $queryRet[0]['MONEY'];
                $loanId = $queryRet[0]['LOANID'];

                if(!$reimMoney || !$loanId)
                {
                    $cancleFlag  = true;
                    break;
                }

                //ɾ����ϵ����
                $sql = 'DELETE FROM ERP_REIMLOAN WHERE ID = ' . $loan_ids[$key];
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
                $res["msg"] = g2u("ȡ��ʧ�ܣ������³���");
                $this->UserLog->writeLog($loan_ids,$_SERVER["REQUEST_URI"],"ȡ���ɹ��������ʧ��" ,serialize($_REQUEST));
            }else{

                D("Loan")->commit();
                $res["status"] = true;
                $res["msg"] = g2u("ȡ���ɹ�");
                $this->UserLog->writeLog($loan_ids,$_SERVER["REQUEST_URI"],"ȡ���ɹ��������ɹ�" ,serialize($_REQUEST));
            }

            /***ȡ���������***/
        }

        //������
        die(@json_encode($res));
    }
    
    
    /**
     +----------------------------------------------------------
     * �������
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    function opinionFlow()
    {   
        Vendor('Oms.workflow');			
        $workflow = new workflow();
        
        $type = !empty($_REQUEST['FLOWTYPE']) ? $_REQUEST['FLOWTYPE'] : '';
        $flowId = $_REQUEST['flowId'];
        $recordId = !empty($_REQUEST['RECORDID']) ? $_REQUEST['RECORDID'] : 0;
		$caseId = !empty($_REQUEST['CASEID']) ? $_REQUEST['CASEID'] : 0;
        
        if($flowId)
        {
            $click  = $workflow->nextstep($flowId);
            $form   = $workflow->createHtml($flowId);
            
            if($_REQUEST['savedata'])
            {
                if($_REQUEST['flowNext'])
                {
                    $str = $workflow->handleworkflow($_REQUEST);
                    
                    if($str)
                    {
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('����ʧ��');
                    }
                }
                else if($_REQUEST['flowPass'])
                {
                    $str = $workflow->passWorkflow($_REQUEST);
                    
                    if($str)
                    {
                        js_alert('ͬ��ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('ͬ��ʧ��');
                    }
                }
                elseif($_REQUEST['flowNot'])
                {
                    $str = $workflow->notWorkflow($_REQUEST);
                    
                    if($str)
                    {
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('���ʧ��');
                    }
                }
                elseif($_REQUEST['flowStop'])
                {
					$auth = $workflow->flowPassRole($flowId);
                    
					if(!$auth)
                    {
						js_alert('δ�����ؾ���ɫ');exit;
					}

                    $str = $workflow->finishworkflow($_REQUEST);
                    
                    if($str)
                    {
                        js_alert('�����ɹ�',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('����ʧ��');
                    }
                }
                exit;
            }
        }
        else
        {
            $auth = $workflow->start_authority($type);
            if(!$auth)
            {
                js_alert('����Ȩ��');
            }
            $form = $workflow->createHtml();

            if($_REQUEST['savedata'])
            {   
                $flow_data['type'] = $type; 
                $flow_data['CASEID'] = $caseId;
                $flow_data['RECORDID'] = $recordId;
                $flow_data['INFO'] = strip_tags($_POST['INFO']);
                $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                $flow_data['FILES'] = $_POST['FILES'];
                $flow_data['ISMALL'] =  intval($_POST['ISMALL']);
                $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']);
                
                $str = $workflow->createworkflow($flow_data);
                
                if($str)
                {   
                    js_alert('�ύ�ɹ�',U('Loan/opinionFlow',$this->_merge_url_param));
                    exit;
                }
                else
                {
                    js_alert('�ύʧ��',U('Loan/opinionFlow',$this->_merge_url_param));
                    exit;
                }
            }
        }
        
        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('current_url', U('Loan/opinionFlow',$this->_merge_url_param));
		//$this->assign('tabs',$this->getTabs(2,$this->_merge_url_param));
        $this->display('opinionFlow');
    } 
}

/* End of file ReimbursementAction.class.php */
/* Location: ./Lib/Action/ReimbursementAction.class.php */