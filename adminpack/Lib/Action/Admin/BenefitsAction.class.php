<?php

/**
 * Class BenefitsAction branch
 */
class BenefitsAction extends ExtendAction{
    /**
     * �Ƿ������д�ۺ����Ȩ��ID
     */
    const DISCOUNT_AD_AUTHORITY = 257;

    /**
     * �Ƿ������д�ڶ�ȷ�Χ�ڵ�Ȩ��
     */
    const WITH_LIMIT_AUTHORITY = 358;

        /*�ϲ���ǰģ���URL����*/
        private $_merge_url_param = array();
        private $model;

        //���캯��
        public function __construct() 
        {
            $this->model = new Model();
            parent::__construct();

            // Ȩ��ӳ���
            $this->authorityMap = array(
                'apply_benefit' => 456,
                'commit_other_benefits'=>461,
            );

            //TAB URL����
            $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;
            !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
            !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
            !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : 
                                        $this->_merge_url_param['RECORDID']=$_GET['benefits_id'];  
            !empty($_GET['TAB_NUMBER']) ? $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']) : 0;
            !empty($_GET['benefits_type']) ? $this->_merge_url_param['benefits_type'] = $_GET['benefits_type'] : '';
            !empty($_GET['scale_type']) ? $this->_merge_url_param['scale_type'] = $_GET['scale_type'] : '';
            !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = $_GET['flowId'] : '';
            !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : '';
            
        }
        
        //ҵ�����
		public function benefits()
        {
            $caseModel = D('ProjectCase');
            $prjectId = $_REQUEST["prjid"];
            $this->project_case_auth($prjectId);//��Ŀҵ��Ȩ���ж�
           //var_dump( $this->_merge_url_param);
            $project_model = D("Project");
            $benefits_model = D("Benefits");
            $case_model = D("ProjectCase");
            
            $benefits_status = $benefits_model->get_benefits_status();
            
			Vendor('Oms.Form');			
			$form = new Form();
			
            //������ĿId��ȡ��Ŀ��Ϣ
            $info = $project_model->get_info_by_id($prjectId,array("PROJECTNAME","BSTATUS","MSTATUS","ASTATUS","ACSTATUS","CPSTATUS", "SCSTATUS"));
            $prjname = $info[0]["PROJECTNAME"];
            // ��ȡ��ǰ��Ŀ������ҵ������
            $benefitsScaleTypeList = $this->benefitsScaleTypeList($info);
            $form->initForminfo(115);

			if($_REQUEST['showForm'] == 3 && !$_REQUEST["ID"] && !$_REQUEST["faction"])//��ʾ������
            {
                $conf_where = "PROJECT_ID = ".$prjectId." and STATUS in(1,2)";
                $field_arr = array("ID"); 
                $benefits_info = $benefits_model->get_info_by_cond($conf_where,$field_arr);
                $form->setMyFieldVal('ADDTIME',date('Y-m-d H:i:s'),true);
				$form ->setMyField('NAME','EDITTYPE','1')
				->setMyField('NAME','READONLY','-1')
                ->setMyFieldVal('NAME',$_SESSION['uinfo']['tname'],true);
            }
            elseif($_REQUEST['showForm'] == 3 && $_REQUEST["faction"] == "saveFormData" && !$_REQUEST["ID"])
            {
                $conf_where = "PROJECT_ID = $prjectId and SCALETYPE = ".$_REQUEST["SCALE_TYPE"];
                $case_info = $case_model->get_info_by_cond($conf_where ,$search_field = array("ID"));
                $case_id = $case_info[0]["ID"];
                $data['PROJECT_ID'] = $prjectId;
                $data['PROJECT_NAME'] = u2g($_REQUEST["PROJECT_NAME"]);
                $data['AUSER_ID'] = $_SESSION["uinfo"]["uid"];
                $data['AMOUNT'] = $_REQUEST["AMOUNT"];
                $data['DESRIPT'] =  u2g($_REQUEST["DESRIPT"]);
                $data['TYPE'] = 0;
                $data['ADDTIME'] = date('Y-m-d H:i:s');
                $data['SCALE_TYPE'] = $_REQUEST["SCALE_TYPE"];
                $data['CASE_ID'] = $case_id;
                $data['STATUS'] = $benefits_status['no_apply'];
                $data['CONTRACT_NO'] = $this->_post('CONTRACT_NO');
                //var_dump($data);die;
                
                if($_REQUEST["SCALE_TYPE"] == 3)
                {
                    $income_contract_info = D("Contract")->get_contract_info_by_caseid($case_id, array("ID"));
                    if(!$income_contract_info)
                    {
                        $result["status"] = 0;
                        $result["msg"] = "��Ӳ����Ŀ��δִ�У�û���ҵ���֮��Ӧ�ĺ�ͬ��Ϣ������������ҵ�����!";
                        $result["msg"] = g2u($result["msg"]);
                        echo json_encode($result);
                        exit;
                    }
                }
                
 
                if( $data['AMOUNT'] >= 50000 && !in_array($_REQUEST["SCALE_TYPE"],array(3,4)) )
 
 
                {
                    $result["status"] = 0;
                    $result["msg"] = "��ͨҵ��������ܴ���50000Ԫ!";
                }
                else
                {
                    $res = $benefits_model->add_benefits($data);
                    if($res){
                        $result["status"] = 2;
                        $result["msg"] = "�����ɹ�!";
                    }else{
                        $result["status"] = 0;
                        $result["msg"] = "����ʧ��!";
                    }
                }                
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
            else if($_REQUEST['showForm'] == 1 && $_REQUEST["ID"] > 0 && !$_REQUEST["faction"])
            {
                $addTime = $this->getBenefitAddedTime($this->_request('ID'));
                $form->setMyFieldVal('ADDTIME',$addTime,true);
            }
            else if($_REQUEST['showForm'] == 1 && $_REQUEST["ID"] > 0 && $_REQUEST["faction"])
            {
                $data['AMOUNT'] = $_REQUEST["AMOUNT"];
                $data['DESRIPT'] =  u2g($_REQUEST["DESRIPT"]);
                $data['SCALE_TYPE'] = $_REQUEST["SCALE_TYPE"];
                $data['CONTRACT_NO'] = $this->_post('CONTRACT_NO');

                $conf_where = "PROJECT_ID = $prjectId and SCALETYPE = ".$_REQUEST["SCALE_TYPE"];
                $case_info = $case_model->get_info_by_cond($conf_where ,$search_field = array("ID"));
                $case_id = $case_info[0]["ID"];
                $data['CASE_ID'] = $case_id;

                //var_dump($data);die;
 
                if( $data['AMOUNT'] >= 50000 && !in_array($_REQUEST["SCALE_TYPE"],array(3,4)))
                {
                    $result["status"] = 0;
                    $result["msg"] = "��ͨҵ��������ܴ���50000Ԫ!";
                }
                else
                {
                     $res = $benefits_model->update_info_by_id(intval($_REQUEST["ID"]),$data);
                    if($res){
                        $result["status"] = 1;
                        $result["msg"] = "�޸ĳɹ�!";
                    }else{
                        $result["status"] = 0;
                        $result["msg"] = "�޸�ʧ��!";
                    }
                }
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
            else if($_REQUEST["faction"] == "delData" && $_REQUEST["ID"])
            {
                $res = $benefits_model->del_info_by_id($_REQUEST["ID"]);
                $scale_type = D('erp_benefits')->where("ID = {$_REQUEST["ID"]}")->getField('SCALE_TYPE');
                if ($scale_type == self::HD) {
                    $case_id = D('ProjectCase')->where("SCALETYPE = {$scale_type} AND PROJECT_ID = {$_REQUEST['prjid']}")->getField('ID');
                    D()->startTrans();
                    $deleted = D('erp_actibudgetfee')->where("CASE_ID = {$case_id} AND ISVALID = 0 AND FEE_ID = 98")->delete();
                    if ($deleted === false) {
                        D()->rollback();
                    } else {
                        D()->commit();
                    }
                }
                if($res)
                {
                    $result["status"] = "success";
                    $result["msg"] = "ɾ���ɹ�";                   
                }
                else
                {
                    $result["status"] = "error";
                    $result["msg"] = "ɾ��ʧ��";
                }
               $result["msg"] = g2u($result["msg"]);
               echo json_encode($result);
               exit;
            }

            $form->setMyField('NAME','EDITTYPE','1')
                ->setMyField('NAME','READONLY','-1') 
                ->setMyFieldVal('PROJECT_NAME',$prjname,true)
                ->setMyFieldVal('PROJECT_ID',$prjectId,true)
                ->setMyFieldVal("TYPE",0)
                ->setMyField('SUPPLIER','FORMVISIBLE',0)
                ->setMyField('SUPPLIER','GRIDVISIBLE',0);


            $caseInfo = $caseModel->get_info_by_pid($prjectId, 'hd');
            $caseId = !empty($caseInfo[0]['ID']) ? intval($caseInfo[0]['ID']) : 0;
            //���ʾ��ͬ��
            if(count($benefitsScaleTypeList) == 1 && array_key_exists(4,$benefitsScaleTypeList)) {
                $form = $form->setMyField('CONTRACT_NO', 'LISTSQL', 'SELECT ID,CONTRACT_NO FROM ERP_INCOME_CONTRACT WHERE  CASE_ID = ' . $caseId, FALSE);
                $form->setMyField('CONTRACT_NO','FORMVISIBLE',-1)
                    ->setMyField('CONTRACT_NO','GRIDVISIBLE',-1);
            }
            // ��ʾ��Ŀ��ҵ������
            if (count($benefitsScaleTypeList) == 1) {
                $keyList = array_keys($benefitsScaleTypeList);
                $form->setMyFieldVal("SCALE_TYPE", $keyList[0], true);
            } else {
                $form->setMyField("SCALE_TYPE",'LISTCHAR', array2listchar($benefitsScaleTypeList));
            }
            //
            $form->EDITCONDITION = '%STATUS% == 1';
            $form->DELCONDITION = '%STATUS% == 1';
            $form->setMyField("ISCOST", "GRIDVISIBLE", "0")->setMyField("TYPE", "FORMVISIBLE", "0");
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);
            $formHtml = $form->where("TYPE=0 and PROJECT_ID=$prjectId")->getResult();

            $this->assign('isShowOptionBtn', $this->isProjectContextMenuRunnable($prjectId, 'benefits'));
			$this->assign('form',$formHtml);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
            $this->assign("prjname",$prjname);
            $this->assign("paramUrl",$this->_merge_url_param);
            $this->assign('benefits_type',0);
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
			$this->display('benefits');
		 }
         
         //Ԥ��������ҵ����ã���
        public function otherBenefits()
        {	
            $prjectId = $_REQUEST["prjid"];    
			$this->project_case_auth($prjectId);//��Ŀҵ��Ȩ���ж�	
            $project_model = D("Project");
            $benefits_model = D("Benefits");
            $case_model = D("ProjectCase");
            $showForm = $_REQUEST['showForm'];
            $benefits_status = $benefits_model->get_benefits_status();
            $benefits_cost_status = $benefits_model->get_cost_status();
            
			Vendor('Oms.Form');			
			$form = new Form();			           
             //������ĿId��ȡ��Ŀ��Ϣ
            $info = $project_model->get_info_by_id($prjectId,array("PROJECTNAME","BSTATUS","MSTATUS","ASTATUS","ACSTATUS","CPSTATUS", "SCSTATUS"));
            $prjname = $info[0]["PROJECTNAME"];
            $benefitsScaleTypeList = $this->benefitsScaleTypeList($info);
            $form->initForminfo(115);
            $GABTN = '<a onclick="show_benefit_info();" href="javascript:;" id="commit_other_benefits" class="btn btn-info btn-sm">�ύ</a>'
                . '<a onclick="applyReimburse();" href="javascript:;" id="apply_reim" class="btn btn-info btn-sm">���뱨��</a>'
                . '<a onclick="show_flow_step();" href="javascript:;" id="show_steps" class="btn btn-info btn-sm">�걨����ͼ</a>';
            
			if($_REQUEST['showForm'] == 3 && !$_REQUEST["ID"] && !$_REQUEST["faction"])//��ʾ������
            {   
                $conf_where = "PROJECT_ID = ".$prjectId." and STATUS in(1,2)";
                $field_arr = array("ID"); 
                $benefits_info = $benefits_model->get_info_by_cond($conf_where,$field_arr);
                //if($benefits_info)//����Ŀ����δ����ͨ����ҵ����������������
                //{
                //    js_alert("����Ŀ����δ����������ҵ�������Ԥ�����������ã�����������ſ����ٴ�����",
                //        U("Benefits/otherBenefits",$this->_merge_url_param));
                //}
                $form->GABTN = $GABTN;
                $form->setMyFieldVal('ADDTIME',date('Y-m-d H:i:s'),true);
				$form ->setMyField('NAME','EDITTYPE','1')
				->setMyField('NAME','READONLY','-1')
                ->setMyFieldVal('NAME',$_SESSION['uinfo']['tname'],true);
            }
            elseif( $_REQUEST['showForm'] == 3 && $_REQUEST["faction"] == "saveFormData" && !$_REQUEST["ID"] )//����
            {
                if( $_REQUEST["AMOUNT"] < 50000 ){
                    $result["status"] = 0;
                    $result["msg"] = "Ԥ������ý��������50000Ԫ����";
                    $result["msg"] = g2u($result["msg"]);
                  //  echo json_encode($result);
                   // exit;
                }
                $conf_where = "PROJECT_ID = $prjectId and SCALETYPE = ".$_REQUEST["SCALE_TYPE"];
                $case_info = $case_model->get_info_by_cond($conf_where ,$search_field = array("ID"));
                $case_id = $case_info[0]["ID"];
                
                $data['PROJECT_ID'] = $prjectId;
                $data['PROJECT_NAME'] = u2g($_REQUEST["PROJECT_NAME"]);
                $data['AUSER_ID'] = $_SESSION["uinfo"]["uid"];
                $data['AMOUNT'] = $_REQUEST["AMOUNT"];
                $data['DESRIPT'] =  u2g($_REQUEST["DESRIPT"]);
                $data['TYPE'] = 1;
                $data['ADDTIME'] = date('Y-m-d H:i:s');
                $data['CASE_ID'] = $case_id;
                $data['ISCOST'] = $benefits_cost_status["no_apply_reim"];
                $data['STATUS'] = $benefits_status['no_apply'];
                $data['SCALE_TYPE'] = $_REQUEST["SCALE_TYPE"];
                $data['SUPPLIER'] = u2g( $_REQUEST['S_ID_GET']);
                //var_dump($data);DIE;
                $res = $benefits_model->add_benefits($data);
                if($res){
                    $result["status"] = 2;
                    $result["msg"] = "�����ɹ���";
                }else{
                    $result["status"] = 0;
                    $result["msg"] = "����ʧ�ܣ�";
                }
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
            else if($_REQUEST['showForm'] == 1 && $_REQUEST["ID"] > 0 && !$_REQUEST["faction"])
            {
                $benefits_info = $benefits_model->get_info_by_id(intval($_REQUEST["ID"]),array("ADDTIME"));
                
                $format_date = oracle_date_format($benefits_info[0]["ADDTIME"]);
                if($format_date)
                {
                    $addtime = $format_date;                    
                }
                else
                {
                    $addtime = $benefits_info[0]["ADDTIME"];
                }
                $form->setMyFieldVal('ADDTIME',$addtime,true);
                $status = M("Erp_benefits")->where("ID=".$_REQUEST['ID'])->getField('STATUS');
                if($status != 1){
                    $form->setMyField('AMOUNT','FORMVISIBLE',-1,true)
                        ->setMyField('DESRIPT','FORMVISIBLE',-1,true);
                }
            }
            else if($_REQUEST['showForm'] == 1 && $_REQUEST["ID"] > 0 && $_REQUEST["faction"])
            {

                $conf_where = "PROJECT_ID = $prjectId and SCALETYPE = ".$_REQUEST["SCALE_TYPE"];
                $case_info = $case_model->get_info_by_cond($conf_where ,$search_field = array("ID"));
                $case_id = $case_info[0]["ID"];
				
				$data['AMOUNT'] = $_REQUEST["AMOUNT"];
                $data['DESRIPT'] =  u2g($_REQUEST["DESRIPT"]);
                $data['SCALE_TYPE'] = $_REQUEST["SCALE_TYPE"];
				$data['CASE_ID'] = $case_id;
                $data['SUPPLIER'] = u2g( $_REQUEST['S_ID_GET']);
                //var_dump($data);die;
               // if( $data['AMOUNT'] < 50000)
                //{
                    //$result["status"] = 0;
                    //$result["msg"] = "Ԥ������ý��������50000Ԫ!";
               // }
                //else
                //{
                    $res = $benefits_model->update_info_by_id(intval($_REQUEST["ID"]),$data);
                    if($res){
                        $result["status"] = 1;
                        $result["msg"] = "�޸ĳɹ�!";
                    }else{
                        $result["status"] = 0;
                        $result["msg"] = "�޸�ʧ��!";
                    }
                //}
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
            else if($_REQUEST["faction"] == "delData" && $_REQUEST["ID"])
            {
                $res = $benefits_model->del_info_by_id($_REQUEST["ID"]);
                if($res)
                {
                    $result["status"] = "success";
                    $result["msg"] = "ɾ���ɹ�";
                    
                }
                else
                {
                    $result["status"] = "error";
                    $result["msg"] = "ɾ��ʧ��";
                }
               $result["msg"] = g2u($result["msg"]);
               echo json_encode($result);
               exit;
            }


            //��Ӧ��
            if($showForm != 3 && $showForm != 1) {
                $form->setMyField('SUPPLIER', 'EDITTYPE', 21, FALSE);
            }
            if($showForm == 1){
                $supplierId = M("Erp_benefits")->where("ID=".$_REQUEST['ID'])->getField("SUPPLIER");
                $supplierName = M("Erp_supplier")->where("ID=".$supplierId)->getField("NAME");
                $form = $form->setMyFieldVal('SUPPLIER', $supplierName,false);
            }
            $form = $form->setMyField('SUPPLIER', 'LISTSQL', 'SELECT ID,NAME FROM ERP_SUPPLIER', FALSE);

			$form ->setMyField('NAME','EDITTYPE','1')->setMyField('NAME','READONLY','-1')
                
                ->setMyFieldVal('PROJECT_NAME',$prjname,true)
                ->setMyFieldVal('PROJECT_ID',$prjectId,true)
//                ->setMyFieldVal("SCALE_TYPE",$scale_type,true)
                ->setMyFieldVal("TYPE",1)->setMyField("TYPE", "FORMVISIBLE", "0");
            // ��ʾ��Ŀ��ҵ������
            if (count($benefitsScaleTypeList) == 1) {
                $keyList = array_keys($benefitsScaleTypeList);
                $form->setMyFieldVal("SCALE_TYPE", $keyList[0], true);
            } else {
                $form->setMyField("SCALE_TYPE",'LISTCHAR', array2listchar($benefitsScaleTypeList));
            }

            //���ð�ťչʾ���

            $form->DELCONDITION = '%STATUS% == 1';
            $form->GABTN = $GABTN;
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);
            $formHtml = $form->where("TYPE=1 and PROJECT_ID=$prjectId")->getResult();
            $this->assign('isShowOptionBtn', $this->isProjectContextMenuRunnable($prjectId, 'otherBenefits'));
			$this->assign('form',$formHtml);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
            $this->assign("prjname",$prjname);
            $this->assign("paramUrl",$this->_merge_url_param);
            $this->assign('benefits_type',1);
			 $this->assign('supplierId',$supplierId);
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
			$this->display('benefits');
		 }
         
         //չʾ���������Ϣ
        public function show_benefit_info()
        {
            $flowId = $_REQUEST['flowId'] ? $flowId = $_REQUEST['flowId'] : 0;
            $benefits_id = !empty($_REQUEST["benefits_id"]) ? $_REQUEST["benefits_id"] : $_REQUEST["RECORDID"];

            //Ȩ���ж�
            $permisition_con_dis = intval($this->haspermission(self::DISCOUNT_AD_AUTHORITY));//��ͬ����ԱȨ��
            $permisition_in_limit = intval($this->haspermission(self::WITH_LIMIT_AUTHORITY));//������ѡ�Ƿ��ڶ����Ȩ��

            //ҵ�����Model
            $benefits_model =D("Benefits");
            if($_GET['prjid'] )
            {
                $prjid = $_GET['prjid']; 
                $scale_type = !empty($_GET['scale_type']) ? $_GET['scale_type'] : 0;//ҵ������
                $canCommitBenefit = D('ProjectCase')->canCommitBenefit($prjid, $scale_type);
                if (!$canCommitBenefit) {
                    js_alert("����Ŀ������ִ���л����ڽ���״̬����������������");
                    exit;
                }
                $case_model = D("ProjectCase"); 
                $conf_where = "PROJECT_ID=$prjid and SCALETYPE=$scale_type";
                $field_arr = array("ID");
                $case_id =$case_model->get_info_by_cond($conf_where,$field_arr);
                $case_id = $case_id[0]["ID"];
                $benefits_info = $benefits_model->get_info_by_id($benefits_id,array("DISCOUNT_AD","TYPE","FEE_TYPE","INQUOTA"));
                $discount_ad = $benefits_info[0]["DISCOUNT_AD"] !== null ? $benefits_info[0]["DISCOUNT_AD"] : -1; //�ۺ���
                $benefits_type = $benefits_info[0]["TYPE"];
                $fee_type = $benefits_info[0]["FEE_TYPE"];
                $isquota = $benefits_info[0]["INQUOTA"];
            }
            else
            {
                $search_arr = array("PROJECT_ID","CASE_ID","SCALE_TYPE","DISCOUNT_AD","TYPE","FEE_TYPE","INQUOTA");
                $benefits_info = $benefits_model->get_info_by_id($benefits_id,$search_arr);
                $prjid = $benefits_info[0]["PROJECT_ID"];
                $scale_type = $benefits_info[0]["SCALE_TYPE"];
                $case_id = $benefits_info[0]["CASE_ID"];
                $discount_ad = $benefits_info[0]["DISCOUNT_AD"] !== null ? $benefits_info[0]["DISCOUNT_AD"] : -1; //�ۺ���
                $benefits_type = $benefits_info[0]["TYPE"];
                $fee_type = $benefits_info[0]["FEE_TYPE"];
                $isquota = $benefits_info[0]["INQUOTA"];
            }
            $this->_merge_url_param['benefits_type'] = $benefits_type;
            $sql = $this->getBenefitSql($benefits_id, $scale_type);  // ������Ŀ���ͻ�ȡ��sql���
            $arr = $this->model->query($sql);
            $user = D("Erp_users")->field("NAME")->where("ID=".$arr[0]["AUSER_ID"])->find();
            $contract_no = $arr[0]["CONTRACT"];//��ͬ���
            $project_name = $arr[0]["PROJECTNAME"];//��Ŀ����
            $company = $arr[0]["COMPANY"];//��ͬ�ͻ�����
            $contract_money = floatval($arr[0]["MONEY"]);  // ��ͬ���
            $apply_amount = $arr[0]["AMOUNT"];//����������
            $auser = $user["NAME"];


            
            $sql1 = "select sum(AMOUNT) SUMMONEY from erp_benefits a where a.CASE_ID = {$case_id} and status = 3";
            $arr1 = $this->model->query($sql1);
            $sum_money = $arr1[0]["SUMMONEY"] ? $arr1[0]["SUMMONEY"] : 0;//�ۼ������� (��������)
            
            //����Ԥ����ط���
            $case_type = D("ProjectCase")->get_info_by_id($case_id,array("SCALETYPE"));
            $case_type = $case_type[0]["SCALETYPE"];
                       
            //��Ŀ���롢�������󡢸��������ʡ��ۺ�������
            if(in_array($case_type, array(1, 2)))//����
            {
                $sql = "select SUMPROFIT,OFFLINE_COST_SUM_PROFIT,OFFLINE_COST_SUM_PROFIT_RATE,ONLINE_COST_RATE "
                . "from ERP_PRJBUDGET where CASE_ID = $case_id";
                //echo $sql;
                $fee_info = $this->model->query($sql);
                $prj_income_budget = $fee_info[0]["SUMPROFIT"] ? $fee_info[0]["SUMPROFIT"] : 0 ;//��Ŀ����
                $pay_profit_budget = $fee_info[0]["OFFLINE_COST_SUM_PROFIT"] ? $fee_info[0]["OFFLINE_COST_SUM_PROFIT"] : 0;//��������
                $pay_profit_rate_budget = $fee_info[0]["OFFLINE_COST_SUM_PROFIT_RATE"] ? $fee_info[0]["OFFLINE_COST_SUM_PROFIT_RATE"] : 0;//����������
                $Comprehensive_profit_rate_budget = $fee_info[0]["ONLINE_COST_RATE"] ? $fee_info[0]["ONLINE_COST_RATE"] : 0;//�ۺ�������
                
                //Ԥ�����·���
                $sql1 = "select GETBUGVCOST($case_id) BUGVCOST from dual";
                $prj_budget_vcost = $this->model->query($sql1);
                $prj_budget_vcost = $prj_budget_vcost[0]["BUGVCOST"] ? $prj_budget_vcost[0]["BUGVCOST"] : 0;            
                
                //Ԥ���ۺ����
				$sql2 = "select nvl(sum(B.AMOUNT),0) vadcost from erp_prjbudget a,erp_budgetfee b where a.case_id = $case_id and a.id=B.BUDGETID and b.feeid =98 and b.isvalid=-1";

                $discount_ad_budget = $this->model->query($sql2);
                $discount_ad_budget = $discount_ad_budget ? $discount_ad_budget[0]["VADCOST"] : 0;
                
                //Ԥ����������
                $sql3 = "select GETBUGOTHERCOST($case_id) OTHER_FEE from dual";
                $other_fee_budget = $this->model->query($sql3);
                //echo $case_id;
                $other_fee_budget = $other_fee_budget[0]["OTHER_FEE"] ? $other_fee_budget[0]["OTHER_FEE"] : 0;
            }
            else if($case_type == 4)//�
            {
                $act_id = D("Erp_activities")->where("CASE_ID = ".$case_id)->field("ID")->find();
                $activities_id = $act_id["ID"];
                $sql1 = "SELECT PRINCOME,BUDGET FROM ERP_ACTIVITIES WHERE ID = ".$activities_id;
                $fee_info = $this->model->query($sql1);
                
                //��Ŀ����
                $prj_income_budget = $fee_info[0]["PRINCOME"];
                
                //���·���
                $prj_budget_vcost = $fee_info[0]["BUDGET"];
                
                //��������
                $pay_profit_budget = $fee_info[0]["PRINCOME"] - $fee_info[0]["BUDGET"];   
                
                //����������
                $pay_profit_rate_budget = round(($fee_info[0]["PRINCOME"] - $fee_info[0]["BUDGET"]) * 100 /$fee_info[0]["PRINCOME"],2);
                
                //Ԥ����������
                $other_fee_budget = 0;
                
                //Ԥ���ۺ����
                //$sql2 = "select GETVADCOST($activities_id,$case_type) AD_BUDGET from dual";
				$sql2 = "select nvl(sum(B.AMOUNT),0) vadcost from erp_actibudgetfee B where B.activities_id = $activities_id  and b.fee_id = 98 and b.isvalid=0";
                $discount_ad_budget = $this->model->query($sql2);
				
                $discount_ad_budget = $discount_ad_budget ? $discount_ad_budget[0]["VADCOST"] : 0;
               //�ۺ�������
               $Comprehensive_profit_rate_budget = round(($fee_info[0]["PRINCOME"] - $fee_info[0]["BUDGET"] - $discount_ad_budget)/$fee_info[0]["PRINCOME"],2);
            }          
            $project_budget_fee["prj_income"] = $prj_income_budget;
            $project_budget_fee["cost_fee"] = $prj_budget_vcost;
            $project_budget_fee["pay_profit"] = $pay_profit_budget;
            $project_budget_fee["pay_profit_rate"] = $pay_profit_rate_budget;
            $project_budget_fee["discount_ad"] = $discount_ad_budget;
            $project_budget_fee["other_fee"] = $other_fee_budget;
            $project_budget_fee["Comprehensive_profit_rate"] = $Comprehensive_profit_rate_budget;
            //echo $case_id;
            
            //��Ŀִ��״����ط���     
            if(in_array($case_type, array(1, 2)))
            {
                //��Ŀ����
                if ($case_type == 1) {
                    // �����ǿ�Ʊ����
                    $prj_income_e =  $this->model->query("select getprjdata($case_id,2,null) PRJ_INCOME_E from dual");
                } else {
                    // �����ǻؿ�����
                    $prj_income_e =  $this->model->query("select getCaseInvoiceAndReturned($case_id, 2, 2) PRJ_INCOME_E from dual");
                }
                $prj_income_e = $prj_income_e ? $prj_income_e[0]["PRJ_INCOME_E"] : 0;

                //ʵ�����·���
                $cost_fee_e = $this->model->query("select GETOFFLINECOST($case_id) COST_FEE_E from dual");
                $cost_fee_e = $cost_fee_e ? $cost_fee_e[0]["COST_FEE_E"] : 0;
                
                //��������
                $pay_profit_e = floatval($prj_income_e) - floatval($cost_fee_e);

                //����������
                $pay_profit_rate_e = 0;
                if (floatval($prj_income_e) != 0) {
                    $pay_profit_rate_e = round($pay_profit_e * 100 / floatval($prj_income_e), 2);
                }

                //�ۺ���
                $discount_ad_e = $discount_ad;
                
                //ִ�У�ʵ�ʣ���������
                $sql4 = "select SUM(AMOUNT) OTHER_FEE_E from erp_benefits where CASE_ID = $case_id and TYPE = 1 AND STATUS=3";
                $other_fee_e = $this->model->query($sql4);
                $other_fee_e = $other_fee_e[0]["OTHER_FEE_E"] ? $other_fee_e[0]["OTHER_FEE_E"] : 0;
                
               //ִ�У�ʵ�ʣ��ۺ�������
                $Comprehensive_profit_rate_e = ($pay_profit_e - floatval($discount_ad_e)) / floatval($prj_income_e);
            }
            else if($case_type == 4)
            {
                //ʵ����Ŀ����
                $sql = "select sum(INVOICE_MONEY) SUM_INVOICE_MONEY from erp_billing_record where CASE_ID = $case_id and status = 4";
                $prj_income_e = $this->model->query($sql);
                $prj_income_e = $prj_income_e[0]["SUM_INVOICE_MONEY"] ? $prj_income_e[0]["SUM_INVOICE_MONEY"] : 0;
                
                //ʵ�����·���
                $cost_fee_e = $this->model->query("select GETOFFLINECOST($case_id) COST_FEE_E from dual");
                $cost_fee_e = $cost_fee_e ? $cost_fee_e[0]["COST_FEE_E"] : 0;
                
                //��������
                $pay_profit_e = floatval($prj_income_e) - floatval($cost_fee_e);

                // ����������
                $pay_profit_rate_e = null;
                if (floatval($prj_income_e) > 0) {
                    $pay_profit_rate_e = round($pay_profit_e * 100 / $prj_income_e, 2);
                }

                //�ۺ���
                $discount_ad_e = $discount_ad;
                //ִ�У�ʵ�ʣ���������
                $other_fee_e = 0;
                //�ۺ�������
                $Comprehensive_profit_rate_e = round(($prj_income_e - $cost_fee_e - 0) * 100 / $prj_income_e, 2);
            }
            
            $project_exe_fee["prj_income_e"] = $prj_income_e;
            $project_exe_fee["cost_fee_e"] = $cost_fee_e;
            $project_exe_fee["pay_profit_e"] = $pay_profit_e;
            $project_exe_fee["pay_profit_rate_e"] = $pay_profit_rate_e;
            $project_exe_fee["discount_ad_e"] = $discount_ad_e;
            $project_exe_fee["other_fee_e"] = $other_fee_e;
            $project_exe_fee["Comprehensive_profit_rate_e"] = $Comprehensive_profit_rate_e; 
                       
            //Ӳ����Ŀʵ��ִ����ط���            
            //��Ʊ���
            $sql5 = "select sum(INVOICE_MONEY) SUM_INVOICE_MONEY from erp_billing_record where CASE_ID = $case_id and status = 4";
            //echo $sql5;
            $invoice_money_ad = $this->model->query($sql5);
            $invoice_money_ad = $invoice_money_ad[0]["SUM_INVOICE_MONEY"] ? $invoice_money_ad[0]["SUM_INVOICE_MONEY"] : 0;
            
            //�ؿ���
            $sql6 = "SELECT SUM(t.INCOME) SUM_MONEY FROM erp_income_list t WHERE t.case_id = {$case_id} AND t.status = 4 ";
            $refund_money_ad = $this->model->query($sql6);
            $refund_money_ad= $refund_money_ad[0]["SUM_MONEY"] ? $refund_money_ad[0]["SUM_MONEY"] : 0;
           
            //ʵ�����·���
            $cost_fee_ad = $this->model->query("select GETOFFLINECOST($case_id) COST_FEE_E from dual");
            $cost_fee_ad = $cost_fee_ad ? $cost_fee_ad[0]["COST_FEE_E"] : 0;

            //��������
            $pay_profit_ad = $refund_money_ad - $cost_fee_ad;
            
            //����������
            $pay_profit_rate_ad = round(($refund_money_ad - $cost_fee_ad)/$refund_money_ad * 100,2);
            
            $contract_discount_ad = $discount_ad;//��ͬ�ۿ�
            $contract_exe_fee["invoice_money_ad"] = $invoice_money_ad;
            $contract_exe_fee["refund_money_ad"] = $refund_money_ad;
            $contract_exe_fee["cost_fee_ad"] = $cost_fee_ad;
            $contract_exe_fee["pay_profit_ad"] = $pay_profit_ad;
            $contract_exe_fee["pay_profit_rate_ad"] = $pay_profit_rate_ad;
            $contract_exe_fee["contract_discount_ad"] = $contract_discount_ad;

            //���������
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('case_id',$case_id);
            $this->assign('benefits_type',$benefits_type);
            $this->assign('scale_type',$scale_type);
            $this->assign('permisition_con_dis',$permisition_con_dis);
            $this->assign('permisition_in_limit',$permisition_in_limit);
            $this->assign('flowid',$flowId);
            $this->assign('activities_id',$activities_id);
            $this->assign('fee_type',$fee_type);
            $this->assign('isquota',$isquota);
            
            //������Ϣ����
            $this->assign('apply_amount',$apply_amount);
            $this->assign('contract_no',$contract_no);
            $this->assign('company',$company);
            $this->assign('contract_money',$contract_money);
            $this->assign('project_name',$project_name);
            $this->assign('sum_money',$sum_money);
            $this->assign('auser',$auser);
            
            //��Ŀ����ԱȲ���
            $this->assign("project_budget_fee",$project_budget_fee);//��ĿԤ���������
            $this->assign("project_exe_fee",$project_exe_fee);//��Ŀִ������������
            $this->assign("contract_exe_fee",$contract_exe_fee);//Ӳ��ִ������������
            
            $this->assign('discount_ad',$discount_ad);           
            $this->display('benefits_info');
         }
         
         /**
         +----------------------------------------------------------
         * ҵ������������
         +----------------------------------------------------------
         * @param none
         +----------------------------------------------------------
         * @return none
         +----------------------------------------------------------
         */
        public function opinionFlow() {
            $benefits_model = D("Benefits");
            $case_model = D("ProjectCase");
            Vendor('Oms.workflow');			
            $workflow = new workflow();
            
            $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;//��ĿID
            $scale_type = $_GET["scale_type"];
            $uid = intval($_SESSION['uinfo']['uid']);
            $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;                  
            $flowId = $_REQUEST['flowId'];

            $search_arr = array("TYPE","CASE_ID","AMOUNT");
            $benefits_info = $benefits_model->get_info_by_id($_REQUEST["RECORDID"],$search_arr);
            $benefits_type = $benefits_info[0]['TYPE'];
            if($flowId)
            {
                $click = $workflow->nextstep($flowId);
                $form=$workflow->createHtml($flowId);

                if($_REQUEST['savedata']){
                    if($_REQUEST['flowNext']){
                        $str = $workflow->handleworkflow($_REQUEST);
                        if($str){
                            js_alert('����ɹ�',U('Flow/workStep'));
                        }else{
                            js_alert('����ʧ��');
                        }
                    }elseif($_REQUEST['flowPass']){
                        $str = $workflow->passWorkflow($_REQUEST);
                        if($str){
                            js_alert('ͬ��ɹ�',U('Flow/workStep'));
                        }else{
                            js_alert('ͬ��ʧ��');
                        }
                    }elseif($_REQUEST['flowNot']){

                        $str = $workflow->notWorkflow($_REQUEST);
                        if($str){
                            $case_id = $_REQUEST["CASEID"];
                            $case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
                            $scale_type = $case_info[0]["SCALETYPE"];

                            $sql = " UPDATE ERP_BENEFITS SET STATUS = 4 WHERE ID=".$_REQUEST["RECORDID"];
                            $res = D("Benefits")->execute($sql);
                            if ($scale_type == self::HD) {
                                // ҵ�����ҵ����ISVALID: 0=��δ��� -1=�����
                               D('erp_actibudgetfee')->where("CASE_ID = {$case_id} AND ISVALID = 0 AND FEE_ID = 98")->save(array(
                                    'ISVALID' => 0
                                ));
                            }
                            js_alert('����ɹ�',U('Flow/workStep'));
                        }else{
                            js_alert('���ʧ��');
                        }

                    }
                    elseif($_REQUEST['flowStop'])
                    {
                        $case_id = $_REQUEST["CASEID"];
                        $case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
                        $scale_type = $case_info[0]["SCALETYPE"];

                        //�����Ŀ�ɱ������ѵ��ʽ� > ��Ԥ��������*���ʱ���
                        $is_overtop_limit = is_overtop_payout_limit($case_id,$benefits_info[0]['AMOUNT']);
                        if($is_overtop_limit)
                        {
                            js_alert("����Ŀ�ɱ��ѳ������ʶ�Ȼ򳬳�����Ԥ�㣨�ܷ���>��Ʊ�ؿ�����*���ֳɱ��ʣ������̲�������ͨ����",
                            U("Benefits/opinionFlow",$this->_merge_url_param),1);
                            die;
                        }
                        
                        $auth = $workflow->flowPassRole($flowId);
                        if(!$auth)
                        {
                            js_alert('δ�����ؾ���ɫ');exit;
                        }
                        
                        $str = $workflow->finishworkflow($_REQUEST);
                        if($str)
                        {
                            $sql = " UPDATE ERP_BENEFITS SET STATUS = 3 WHERE ID=".$_REQUEST["RECORDID"];
                            $res = D("Benefits")->execute($sql);
                            //������ͨ������ӳɱ���¼                            
                            $search_arr = array("TYPE","CASE_ID","AMOUNT");
                            $benefits_info = $benefits_model->get_info_by_id($_REQUEST["RECORDID"],$search_arr);
                            $benefits_type = $benefits_info[0]["TYPE"];
                            
                            //���ɱ�������Ӽ�¼
                            $cost_info['CASE_ID'] = $benefits_info[0]["CASE_ID"];            //������� �����       
                            $cost_info['ENTITY_ID'] = $_REQUEST["RECORDID"];                 //ҵ��ʵ���� �����
                            $cost_info['EXPEND_ID'] = $_REQUEST["RECORDID"];                //�ɱ���ϸ��� �����
                            
                            $cost_info['ORG_ENTITY_ID'] = $_REQUEST["RECORDID"];                 //ҵ��ʵ���� �����
                            $cost_info['ORG_EXPEND_ID'] = $_REQUEST["RECORDID"];
                            
                            $cost_info['FEE'] = $benefits_info[0]["AMOUNT"];                // �ɱ���� ����� 
                            $cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];             //�����û���� �����
                            $cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());        //����ʱ�� �����
                            $cost_info['ISFUNDPOOL'] = 0;                                 //�Ƿ��ʽ�أ�0��1�ǣ� �����
                            $cost_info['ISKF'] = 1;                                     //�Ƿ�۷� �����
                            $cost_info['FEE_REMARK'] = "ҵ���������";             //�������� ��ѡ�
                            $cost_info['INPUT_TAX'] = 0;                                //����˰ ��ѡ�
                            $cost_info['FEE_ID'] = 60;                                  //�ɱ�����ID �����
                            //�ɱ���Դ
                            if($benefits_type == 0)
                            {
                               $cost_info['EXPEND_FROM'] = 17;
                                if ($scale_type == self::HD) {
                                    D()->startTrans();
                                    // ҵ�����ҵ����ISVALID: 0=��δ��� -1=�����
                                    $updated = D('erp_actibudgetfee')->where("CASE_ID = {$case_id} AND ISVALID = 0 AND FEE_ID = 98")->save(array(
                                        'ISVALID' => -1
                                    ));
                                    if ($updated === false) {
                                        D()->rollback();
                                    } else {
                                        D()->commit();
                                    }
                                }
                            }
                            else if($benefits_type == 1)
                            {
                                $cost_info['EXPEND_FROM'] = 18; 
                            }
                            $project_cost_model = D("ProjectCost");
                            $cost_insert_id = $project_cost_model->add_cost_info($cost_info);
                            js_alert('�����ɹ�',U('Flow/workStep'));
                        }else{
                            js_alert('����ʧ��');
                        }
                    }
					exit;
                }
            }
            else
            {      
                $flow_type_pinyin = $_REQUEST['FLOWTYPE'];
                $auth = $workflow->start_authority($flow_type_pinyin);
                if(!$auth)
                {
                    js_alert('����Ȩ��');
                }
                $form = $workflow->createHtml();          
                if($_REQUEST['savedata'])
                {                   
                    if( !$_REQUEST["CASEID"] )
                    {
                        $cond_where = "PROJECT_ID = $prjid and SCALETYPE = $scale_type";
                        $case_info = $case_model->get_info_by_cond($cond_where,array("ID"));
                        //echo $this->model->_sql();
                        $case_id = $case_info[0]["ID"];
                    }
                    else
                    {
                       $case_id  = $_REQUEST["CASEID"];
                    }
                    $flow_data['type'] = $flow_type_pinyin;
                    $flow_data['CASEID'] = $case_id;                    
                    $flow_data['RECORDID'] = $_REQUEST["RECORDID"];
                    $flow_data['INFO'] = strip_tags($_POST['INFO']);
                    $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                    $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                    $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                    $flow_data['FILES'] = $_POST['FILES']; 
                    $flow_data['ISMALL'] =  intval($_POST['ISMALL']); 
                    $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']); 
                    $str = $workflow->createworkflow($flow_data);
                    //var_dump($flow_data);die;
                    if($str)
                    {   
                        $sql = " UPDATE ERP_BENEFITS SET STATUS = 2 WHERE ID=".$_REQUEST["RECORDID"];
                        $res = D("Benefits")->execute($sql);
                        $benefits_type = intval($_REQUEST['benefits_type']);
                        if($benefits_type === 0){
                            js_alert('�ύ�ɹ�',U('Benefits/benefits',$this->_merge_url_param));
                        }else if($benefits_type === 1){
                            js_alert('�ύ�ɹ�',U('Benefits/otherBenefits',$this->_merge_url_param));
                        }
                        exit;
                    }
                    else
                    {
                        js_alert('�ύʧ��',U('Benefits/opinionFlow',$this->_merge_url_param));
                        exit;
                    }
                }
            }
            $this->assign('form', $form);
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('benefits_type',$benefits_type);
            $this->assign('contract_type',$_REQUEST["contract_type"]);
            $this->display('opinionFlow');
        }
        
        /**
         +----------------------------------------------------------
         * Ԥ��������������
         +----------------------------------------------------------
         * @param none
         +----------------------------------------------------------
         * @return none
         +----------------------------------------------------------
         */
        public function opinionFlowY()
        {   //var_dump($this->_merge_url_param);            
            $benefits_model = D("Benefits");
            $case_model = D("ProjectCase");
            Vendor('Oms.workflow');			
            $workflow = new workflow();
            
            $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;//��ĿID
            $scale_type = $_GET["scale_type"];
            $uid = intval($_SESSION['uinfo']['uid']);
            $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;                  
            $flowId = $_REQUEST['flowId'];

            $search_arr = array("TYPE","CASE_ID","AMOUNT");
            $benefits_info = $benefits_model->get_info_by_id($_REQUEST["RECORDID"],$search_arr);
            $benefits_type = $benefits_info[0]['TYPE'];
            if($flowId)
            {
                $click = $workflow->nextstep($flowId);
                $form=$workflow->createHtml($flowId);

                if($_REQUEST['savedata']){
                    if($_REQUEST['flowNext']){
                        $str = $workflow->handleworkflow($_REQUEST);
                        if($str){
                            js_alert('����ɹ�',U('Flow/workStep'));
                        }else{
                            js_alert('����ʧ��');
                        }
                    }elseif($_REQUEST['flowPass']){
                        $str = $workflow->passWorkflow($_REQUEST);
                        if($str){
                            js_alert('ͬ��ɹ�',U('Flow/workStep'));
                        }else{
                            js_alert('ͬ��ʧ��');
                        }
                    }elseif($_REQUEST['flowNot']){

                        $str = $workflow->notWorkflow($_REQUEST);
                        if($str){
                            $sql = " UPDATE ERP_BENEFITS SET STATUS = 4 WHERE ID=".$_REQUEST["RECORDID"];
                            $res = D("Benefits")->execute($sql);
                            js_alert('����ɹ�',U('Flow/workStep'));
                        }else{
                            js_alert('���ʧ��');
                        }

                    }elseif($_REQUEST['flowStop']){
                        $case_id = $_REQUEST["CASEID"];
                        $case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
                        $scale_type = $case_info[0]["SCALETYPE"];

                        //�����Ŀ�ɱ������ѵ��ʽ� > ��Ԥ��������*���ʱ���
                        $is_overtop_limit = is_overtop_payout_limit($case_id,$benefits_info[0]['AMOUNT']);
                        if($is_overtop_limit)
                        {
                            js_alert("����Ŀ�ɱ��ѳ������ʶ�Ȼ򳬳�����Ԥ�㣨�ܷ���>��Ʊ�ؿ�����*���ֳɱ��ʣ������̲�������ͨ����",
                            U("Benefits/otherBenefits",$this->_merge_url_param),1);
                            die;
                        }
                        
                        $auth = $workflow->flowPassRole($flowId);
                        if(!$auth){
                            js_alert('δ�����ؾ���ɫ');exit;
                        }
                        
                        $str = $workflow->finishworkflow($_REQUEST);
                        if($str){
                            $sql = " UPDATE ERP_BENEFITS SET STATUS = 3 WHERE ID=".$_REQUEST["RECORDID"];
                            $res = D("Benefits")->execute($sql);
                            //������ͨ������ӳɱ���¼                            
                            $search_arr = array("TYPE","CASE_ID","AMOUNT");
                            $benefits_info = $benefits_model->get_info_by_id($_REQUEST["RECORDID"],$search_arr);
                            $benefits_type = $benefits_info[0]["TYPE"];
                            
                            //���ɱ�������Ӽ�¼
                            $cost_info['CASE_ID'] = $benefits_info[0]["CASE_ID"];            //������� �����       
                            $cost_info['ENTITY_ID'] = $_REQUEST["RECORDID"];                 //ҵ��ʵ���� �����
                            $cost_info['EXPEND_ID'] = $_REQUEST["RECORDID"];                //�ɱ���ϸ��� �����
                            
                            $cost_info['ORG_ENTITY_ID'] = $_REQUEST["RECORDID"];                 //ҵ��ʵ���� �����
                            $cost_info['ORG_EXPEND_ID'] = $_REQUEST["RECORDID"];
                            
                            $cost_info['FEE'] = $benefits_info[0]["AMOUNT"];                // �ɱ���� ����� 
                            $cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];             //�����û���� �����
                            $cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());        //����ʱ�� �����
                            $cost_info['ISFUNDPOOL'] = 0;                                 //�Ƿ��ʽ�أ�0��1�ǣ� �����
                            $cost_info['ISKF'] = 1;                                     //�Ƿ�۷� �����
                            $cost_info['FEE_REMARK'] = "Ԥ�����������";             //�������� ��ѡ�
                            $cost_info['INPUT_TAX'] = 0;                                //����˰ ��ѡ�
                            $cost_info['FEE_ID'] = 60;                                  //�ɱ�����ID �����
                            //�ɱ���Դ
                            if($benefits_type == 0)
                            {
                               $cost_info['EXPEND_FROM'] = 17;  
                            }
                            else if($benefits_type == 1)
                            {
                                $cost_info['EXPEND_FROM'] = 18; 
                            }
                            $project_cost_model = D("ProjectCost");
                            
                            $cost_insert_id = $project_cost_model->add_cost_info($cost_info);
                            js_alert('�����ɹ�',U('Flow/workStep'));
                        }else{
                            js_alert('����ʧ��');
                        }
                    }
					exit;
                }
            }
            else
            {      
                $flow_type_pinyin = $_REQUEST['FLOWTYPE'];
                $auth = $workflow->start_authority($flow_type_pinyin);
                if(!$auth)
                {
                    js_alert('����Ȩ��');
                }
                $form = $workflow->createHtml();          
                if($_REQUEST['savedata'])
                {                   
                    if( !$_REQUEST["CASEID"] )
                    {
                        $cond_where = "PROJECT_ID = $prjid and SCALETYPE = $scale_type";
                        $case_info = $case_model->get_info_by_cond($cond_where,array("ID"));
                        //echo $this->model->_sql();
                        $case_id = $case_info[0]["ID"];
                    }
                    else
                    {
                       $case_id  = $_REQUEST["CASEID"];
                    }
                    $flow_data['type'] = $flow_type_pinyin;
                    $flow_data['CASEID'] = $case_id;                    
                    $flow_data['RECORDID'] = $_REQUEST["RECORDID"];
                    $flow_data['INFO'] = strip_tags($_POST['INFO']);
                    $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                    $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                    $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                    $flow_data['FILES'] = $_POST['FILES']; 
                    $flow_data['ISMALL'] =  intval($_POST['ISMALL']); 
                    $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']); 
                    $str = $workflow->createworkflow($flow_data);
                    //var_dump($flow_data);die;
                    if($str)
                    {   
                        $sql = " UPDATE ERP_BENEFITS SET STATUS = 2 WHERE ID=".$_REQUEST["RECORDID"];
                        $res = D("Benefits")->execute($sql);
                        $benefits_type = intval($_REQUEST['benefits_type']);
                        if($benefits_type === 0){
                            js_alert('�ύ�ɹ�',U('Benefits/benefits',$this->_merge_url_param));
                        }else if($benefits_type === 1){
                            js_alert('�ύ�ɹ�',U('Benefits/otherBenefits',$this->_merge_url_param));
                        }
                        exit;
                    }
                    else
                    {
                        js_alert('�ύʧ��',U('Benefits/opinionFlow',$this->_merge_url_param));
                        exit;
                    }
                }
            }
            $this->assign('form', $form);
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('benefits_type',$benefits_type);
            $this->assign('contract_type',$_REQUEST["contract_type"]);
            $this->display('opinionFlowY');
        }
        
       /**
         +----------------------------------------------------------
         * ��������
         +----------------------------------------------------------
         * @param none
         +----------------------------------------------------------
         * @return none
         +----------------------------------------------------------
         */
         public function apply_reimburse(){
            $reim_type_model = D("ReimbursementType");
            $reim_list_model = D("ReimbursementList");
            $reim_detail_model = D("ReimbursementDetail");
            $benefits_model = D("Benefits");
            
            $benefits_status = $benefits_model->get_benefits_status();
            //��ǰ�û����
            $uid = intval($_SESSION['uinfo']['uid']);
            //��ǰ�û�����
            $user_truename = $_SESSION['uinfo']['tname'];
            //��ǰ���б��
            $city_id = intval($this->channelid);
             
            //����������
            $reimburse_type = 2;
             
            //����ID
            $benefits_id = $_REQUEST["benefits_id"]; 
            //var_dump($benefits_id);die;            
            $field_arr = array("AMOUNT","STATUS","CASE_ID");
            $benefits_info = $benefits_model->get_info_by_id($benefits_id,$field_arr);  
           // echo $benefits_model->_sql();die;
            $amount = $benefits_info[0]["AMOUNT"];            
            $status = $benefits_info[0]["STATUS"];
            $case_id = $benefits_info[0]["CASE_ID"];
            
            if($status != $benefits_status["passed"]){//������δ���� ������˹����У���������
                $result['state'] = 0;
                $result["msg"] = "��ͨ����˵Ľ����ſ������뱨��, �ý�����δ������˻�����˹����У�����������";
                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }
            
            $is_exist = $benefits_model->get_info_by_id($benefits_id, array("ISCOST"));
            //echo M()->_sql();die;
            if( $is_exist[0]["ISCOST"] != 1 ) //��ѡ��¼�����뱨���������ظ�����
            {
                $result['state'] = 0;
                $result["msg"] = "�ý��������뱨���������ظ����룡";
                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit();
            }
            else
            {
                $reim_list_status = $reim_list_model->get_conf_reim_list_status();                  
                //�����µı�����             
                $list_arr["AMOUNT"] = 0;
                $list_arr["TYPE"] = $reimburse_type;                      
                $list_arr["STATUS"] = $reim_list_status["reim_list_no_sub"];
                $list_arr["APPLY_UID"] = $uid;
                $list_arr["APPLY_TRUENAME"] = $user_truename;
                $list_arr["APPLY_TIME"] = date("Y-m-d H:i:s");
                $list_arr["CITY_ID"] = $city_id;
                $this->model->startTrans();
                $last_id = $reim_list_model->add_reim_list($list_arr);
            }
            
            $detail_status = $reim_detail_model->get_conf_reim_detail_status();
            
            $reim_details_arr["LIST_ID"] = $last_id;
            $reim_details_arr["CITY_ID"] = $city_id;
            $reim_details_arr["CASE_ID"] = $case_id;
            $reim_details_arr["BUSINESS_ID"] = $benefits_id;
            $reim_details_arr["MONEY"] = $amount;
            $reim_details_arr["STATUS"] = $detail_status["reim_detail_no_sub"];
            $reim_details_arr["TYPE"] = $reimburse_type;
            $reim_details_arr["ISKF"] = 1;
            $reim_details_arr["ISFUNDPOOL"] = 0;
            $reim_details_arr["FEE_ID"] = 61;
            $reim_details_arr["BUSINESS_PARENT_ID"] = $benefits_id;            
            $res = $reim_detail_model->add_reim_details($reim_details_arr);            
            //echo $this->model->_sql();
            if($res){
                $benefits_reim_status = D("Benefits")->get_cost_status();
                //������ϸ��ӳɹ���������ۼӵ���������
                $sql = "update ERP_REIMBURSEMENT_LIST set AMOUNT = AMOUNT + '$amount' where ID = $last_id";
                $up_num = $this->model->execute($sql);
                //�޸ı���״̬
                $benefits_arr["ISCOST"] = $benefits_reim_status["applied_reim"];
                $res1 = $benefits_model->update_info_by_id($benefits_id,$benefits_arr);
                if($res1 && $up_num){
                    $this->model->commit();
                    $result['state'] = 1;
                    $result["msg"] = "��������ɹ���";
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit();
                }else{
                    $this->model->rollback();
                    $result['state'] = 0;
                    $result["msg"] = "��������ʧ�ܣ�����";
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit();
                }
             }             
         }
         
         //�鿴����ͼ
        public function show_flow_step(){
            $benefits_model = D("Benefits");
            $case_model = D("ProjectCase");
            
            if($_REQUEST["flowid"])
            {            
                Vendor('Oms.workflow');			
                $workflow = new workflow();
                $flow = $workflow->chartworkflow($_REQUEST["flowid"]);
                $this->assign("flow",$flow);
                $this->display("show_workflow");    
            }
            else
            {
                $benefits_id = $_REQUEST["benefits_id"];
                $scale_type = $_REQUEST["scale_type"];
				$benefits_type = $_REQUEST["benefits_type"];
                $project_id = $benefits_model->get_info_by_id($benefits_id,array("PROJECT_ID"));
                $project_id = $project_id[0]["PROJECT_ID"];

                $conf_where = "PROJECT_ID=$project_id and SCALETYPE=$scale_type";
                $case_id = $case_model->get_info_by_cond($conf_where,array("ID"));
                $case_id = $case_id[0]["ID"]; 

                $type = $benefits_type? 28:7;
                $sql = "select d.id from(select b.*,a.flowtype from erp_flowset a 
                            left join erp_flows b on a.id= b.flowsetid) d
                            where CASEID = $case_id AND FLOWTYPE = $type and RECORDID = ".$benefits_id;
                //echo $sql;
                $res = $this->model->query($sql);
                $flowid = $res[0]["ID"];
				
                if(!$flowid)
                {
                     $result["state"] = 0;
                     $result["msg"] = "�Բ���δ�ҵ����������Ϣ";
                }
                else
                {
                    $result["state"] = 1;
                    $result["msg"] = $flowid;
                }
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
         
        }
         
        public function update_benefits_data()
        {
            if($_REQUEST["discount_ad_budget"])
            {
                $data["AMOUNT"] = floatval($_REQUEST["discount_ad_budget"]);
                $data["FEE_ID"] = 98;
                $data["ACTIVITIES_ID"] = intval($_REQUEST["activities_id"]);
                $data["ISVALID"] = 0;  // 0=��δ��� -1=�����
                $data["MARK"] = "�ۺ����";

                $caseID = D('ProjectCase')->where("PROJECT_ID = {$_REQUEST['prjid']} AND SCALETYPE = {$_REQUEST['scale_type']}")->getField('ID');
                if ($caseID !== false) {
                    $data['CASE_ID'] = $caseID;
                }
                
                //���ݻid�鿴�Ƿ��Ѿ��м�¼
                $fee_info = D("Erp_actibudgetfee")
                    ->where("ACTIVITIES_ID = ".$_REQUEST["activities_id"]." AND FEE_ID = 98 AND ISVALID = 0")->field("ID")->find();

                if($fee_info["ID"])
                {
                    $up_num = D("Erp_actibudgetfee")->where("ID = ".$fee_info["ID"])->save(array("AMOUNT"=>$data["AMOUNT"]));
                    //var_dump($up_num);
                }
                else
                {
                    $insert_id = D("Erp_actibudgetfee")->add($data);
                }

                if($insert_id > 0 || $up_num > 0)
                {
                    $result['state'] = 1;
                    $result["msg"] = "���ݱ���ɹ���";
                }
                else
                {
                    $result['state'] = 0;
                    $result["msg"] = "���ݱ���ʧ�ܣ�";
                }
                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }
            
            if(array_key_exists('discount_ad', $_REQUEST) && $_REQUEST['discount_ad'] !== '') $updata_arr["DISCOUNT_AD"] = $_REQUEST["discount_ad"];
            if($_REQUEST["inquota"]) $updata_arr["INQUOTA"] = intval($_REQUEST["inquota"]);
            $feeType = trim($_REQUEST["fee_type"]);
            if(!empty($feeType)) $updata_arr["FEE_TYPE"] = u2g($_REQUEST["fee_type"]);
            $benefits_id = $_REQUEST["RECORDID"];
            $benefits_model = D("Benefits");
            if (empty($updata_arr)) return;
            $res = $benefits_model->update_info_by_id($benefits_id,$updata_arr);
            //echo $this->model->_sql();die;
            if($res)
            {  
                $result['state'] = 1;
                $result["msg"] = "���ݱ���ɹ���";
            }
            else
            {
                $result['state'] = 0;
                $result["msg"] = "���ݱ���ʧ�ܣ�";
            }
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        
       /**
        * �������б�
        * @param  none 
        * return none
        */
       public function benefits_reim_list()
       {
            $bebefits_model = D("Benefits");
            $reim_list_model = D("ReimbursementList");
            $reim_detail_model = D("ReimbursementDetail");
            $reim_type_model = D("ReimbursementType");
            $case_model = D("ProjectCase");
            
            $uid = $_SESSION["uinfo"]["uid"];  
            $benefits_iscost_status = $bebefits_model->get_cost_status();
            $reim_list_status = $reim_list_model->get_conf_reim_list_status();
            $reim_list_status_remark = $reim_list_model->get_conf_reim_list_status_remark();
            $reim_list_type = $reim_type_model->get_reim_type();
            
            //ɾ��������
            if($_REQUEST["faction"] == "delData" && $_REQUEST["ID"])
            {
                $reim_list_id = $_REQUEST["ID"];
                $this->model->startTrans();
                $list_up_num = $reim_list_model->del_reim_list_by_ids($reim_list_id);
                if($list_up_num)
                {
                    $business_ids = $reim_detail_model->get_detail_info_by_listid($reim_list_id,array("BUSINESS_ID"));
                    
                    foreach ($business_ids as $key=>$val)
                    {
                        $benefits_ids[] = $val["BUSINESS_ID"];
                    }
                    
                    $detail_up_num = $reim_detail_model->del_reim_detail_by_listid($reim_list_id);
                    //ɾ����������ϵ
                    $loan_model = D('Loan');
//                    $up_num_loan = $loan_model->cancle_related_loan_by_reim_ids($reim_list_id);
                    $up_num_loan = $loan_model->cancleRelatedLoan($reim_list_id);
                    $iscost_status = $benefits_iscost_status["no_apply_reim"];
                    $benefits_up_num = $bebefits_model->update_info_by_id($benefits_ids,array("ISCOST"=>$iscost_status));
                    
                    if($detail_up_num && $benefits_up_num)
                    {
                        $this->model->commit();
                        //ɾ�������� �޸Ķ�Ӧ�����ı���״̬Ϊδ���뱨��
                        
                        $result["status"] = "success";
                        $result["msg"] = "������ɾ���ɹ���";
                        $result["msg"] = g2u($result["msg"]);
                        echo json_encode($result);
                        exit;
                    }
                    else
                    {
                        $this->model->rollback();
                        $result["status"] = "error";
                        $result["msg"] = "������ɾ��ʧ�ܣ�";
                        $result["msg"] = g2u($result["msg"]);
                        echo json_encode($result);
                        exit;
                    }
                }
                else
                {
                    $this->model->rollback();
                    $result["status"] = "error";
                    $result["msg"] = "������ɾ��ʧ�ܣ�";
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }
            }
            
            $prjid = $_REQUEST["prjid"];
            $case_info = $case_model->get_info_by_pid($prjid,"",array("ID"));
            foreach($case_info as $key=>$val)
            {
                $case_id[] = $val["ID"];
            }
            $case_id_str = implode(",", $case_id);           
            //����case_id ���ұ�����ϸ�еı�����id��LIST_ID��
            $cond_where = "CASE_ID IN ($case_id_str)";

            $list_id = $reim_detail_model->get_detail_info_by_cond($cond_where,array("LIST_ID"));
            $list_id_str = "";
            foreach($list_id as $k=>$v)
            {
                $list_id_str .= $v["LIST_ID"].",";
            }
            $list_id_str = rtrim($list_id_str,",");           
            $conf_where = " APPLY_UID=".$uid." and TYPE = 2 and STATUS < 4";

            Vendor('Oms.Form');			
            $form = new Form();
            $children = array(
                array('������ϸ',U('/Benefits/reim_detail')),
                array('�������',U('/Loan/related_loan')),
                );
            $form->initForminfo(176)
                ->where($conf_where)
                ->setMyField("TYPE", "LISTCHAR", array2listchar($reim_list_type))
                ->setMyField("STATUS", "LISTCHAR", array2listchar($reim_list_status_remark))
                ->setChildren($children);

           $form->SQLTEXT = '(select a.*,b.LOANMONEY  from ERP_REIMBURSEMENT_LIST a
        left join (select  APPLICANT,sum(UNREPAYMENT) as LOANMONEY from ERP_LOANAPPLICATION where STATUS IN(2,6) AND CITY_ID = '. $this->channelid . ' group by APPLICANT) b
        on a.APPLY_UID = b.APPLICANT left join (select distinct(list_id) from ERP_REIMBURSEMENT_detail) c on c.list_id = a.id )';

           $form->DELCONDITION = "%STATUS%==0";
           $form->GABTN = '<a href="javascript:;" id="sub_reim_apply" class="btn btn-info btn-sm">�ύ������</a>'
               . '<a href="javascript:;" id="related_my_loan" class="btn btn-info btn-sm">�������</a>';

            $formHtml = $form->getResult();
            $this->assign('isShowOptionBtn', $this->isProjectContextMenuRunnable($prjid, 'otherBenefits'));
           $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
            $this->assign("form",$formHtml);
            $this->assign("paramUrl",$this->_merge_url_param);
            $this->display("benefits_reim_list");
       }
       
       /**
        * ������ϸ
        * @param none
        * 
        */
       public function reim_detail()
       {
            $prjid = intval($_REQUEST["prjid"]);
            $reim_list_id = $_REQUEST["parentchooseid"];
            
            $bebefits_model = D("Benefits");
            $reim_list_model = D("ReimbursementList");
            $reim_detail_model = D("ReimbursementDetail");    
            
            $reim_detail_status_remark = $reim_detail_model->get_conf_reim_detail_status_remark();
            $reim_detail_id = $reim_detail_model->get_detail_info_by_listid($reim_list_id,array("ID"));
            $reim_detail_id_str = "(";
            foreach($reim_detail_id as $key=>$val)
            {
                $reim_detail_id_str .= $val["ID"].",";
            }
            $reim_detail_id_str = rtrim($reim_detail_id_str,",");
            $reim_detail_id_str .= ")"; 
            
            $conf_where = "ID IN".$reim_detail_id_str;
            Vendor('Oms.Form');			
            $form = new Form();
            $form->initForminfo(115)
                ->where($conf_where);
            $form->SQLTEXT = "(SELECT A.ID,A.CASE_ID,A.MONEY AMOUNT,A.STATUS ISCOST,B.PROJECT_NAME,B.SCALE_TYPE,B.ADDTIME,
                                B.AUSER_ID NAME,B.DESRIPT,S.NAME AS SUPPLIER
                                FROM ERP_REIMBURSEMENT_DETAIL A LEFT JOIN ERP_BENEFITS B ON A.BUSINESS_ID=B.ID
                                LEFT JOIN ERP_SUPPLIER S ON S.ID = B.SUPPLIER
                                WHERE A.TYPE=2)";
            $form->GABTN = "";
            $form->EDITABLE = 0;
            $form->DELABLE = 0;
            $form->ADDABLE = 0;
            //$form->SHOWDETAIL = 0;
            
            //��Ŀ���ۼ���ͨ����ҵ�����
            $detail_id = intval($reim_detail_id[0]["ID"]);
            $case_id = $reim_detail_model->get_detail_info_by_id($detail_id,array("CASE_ID"));
            $case_id = $case_id[0]["CASE_ID"];
            $sql = "SELECT sum(AMOUNT) SUM_BENEFITS FROM ERP_BENEFITS WHERE CASE_ID = ".$case_id." AND STATUS =3";
            $sum_benefits = $this->model->query($sql);
            $sum_benefits = $sum_benefits[0]["SUM_BENEFITS"];
            $form->setMyField("STATUS", "GRIDVISIBLE", "0")
                ->setMyField("SUM_MONEY","GRIDVISIBLE","-1")
                ->setMyFieldVal("SUM_MONEY",$sum_benefits,true)
                ->setMyField("NAME","LISTSQL","select ID,NAME from erp_users")
                ->setMyField("ISCOST", "LISTCHAR", array2listchar($reim_detail_status_remark));          
            
            $formHtml = $form->getResult();
           $this->assign("form",$formHtml);
           $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // �����εļ��������ʾ��ҳ����
           $this->assign("paramUrl",$this->_merge_url_param);
            $this->display("reim_detail");
        
       }
       
        /**
         +----------------------------------------------------------
         * �ύ��������
         +----------------------------------------------------------
         * @param none
         +----------------------------------------------------------
         * @return none
         +----------------------------------------------------------
         */
        public function sub_reim_to_apply()
        {
            //���뱨�������
            $reim_list_id_arr = $_GET['reim_list_id'];

            if(is_array($reim_list_id_arr) && !empty($reim_list_id_arr))
            {
                $this->model->startTrans();
                //�������뵥MODEL
                $reim_list_model = D('ReimbursementList');
                $update_num = $reim_list_model->sub_reim_list_to_aduit($reim_list_id_arr);

                if($update_num > 0)
                {
                    //�ύ�ɹ����޸Ľ�������״̬
                    $benefits_model = D("Benefits");
                    $reim_list_model = D("ReimbursementList");
                    $reim_detail_model = D("ReimbursementDetail"); 
                    $benefits_status_arr = $benefits_model->get_cost_status();
                    $fail_num = array();
                    foreach($reim_list_id_arr as $key=>$val)
                    {
                        $search_arr = array("BUSINESS_ID");
                        $business_ids = $reim_detail_model->get_detail_info_by_listid($val,$search_arr);
                        foreach($business_ids as $k=>$v)
                        {
                            $benefits_id[] = $v["BUSINESS_ID"];
                        }
                        $benefits_status = $benefits_status_arr["auditing_reim"];
                        $benefits_up_num = $benefits_model->update_info_by_id($benefits_id,array("ISCOST"=>$benefits_status));
                       
                        if(!$benefits_up_num)
                        {
                            $fail_num[] = $val; 
                        }
                    }
                    if(empty($fail_num))
                    {
                        $this->model->commit();
                        $info['state']  = 1;
                        $info['msg']  = '���������ύ�ɹ�';
                    }
                    else
                    {
                        $this->model->rollback();
                        $info['state']  = 0;
                        $info['msg']  = '���������ύʧ��';
                    }
                   
                }
                else
                {
                    $this->model->rollback();
                    $info['state']  = 0;
                    $info['msg']  = '���������ύʧ��';
                }
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '���������ύʧ��';
            }

            $info['msg'] = g2u($info['msg']);
            echo json_encode($info);
            exit;
        }

    /**
     * ��ȡbenefit��sql���
     * @param $benefitId
     * @param $scaleType
     * @return string
     */
    private function getBenefitSql($benefitId, $scaleType) {
        if (empty($benefitId) || empty($scaleType)) {
            return '';
        }

        // ����ǵ�����Ŀ
        if ($scaleType == 1) {
            $sql = <<< ET
                    SELECT a.CONTRACT,
                           a.PROJECTNAME,
                           a.COMPANY,
                           b.AMOUNT,
                           b.AUSER_ID
                    FROM erp_benefits b
                    LEFT JOIN erp_project a ON b.project_id =a.id
                    where b.id={$benefitId}
ET;
        } else {
            $sql = <<< ET
            SELECT a.CONTRACT,
                   a.PROJECTNAME,
                   c.COMPANY,
                   b.AMOUNT,
                   b.AUSER_ID,
                   c.MONEY
            FROM erp_benefits b
            LEFT JOIN erp_project a ON b.project_id =a.id
            LEFT JOIN erp_income_contract c ON c.contract_no = a.contract
            WHERE b.id={$benefitId}
ET;
        }
        return $sql;
    }

    /**
     * ��ȡ�������ʱ��
     * @param $id
     * @return string
     */
    private function getBenefitAddedTime($id) {
        if (empty($id)) {
            return '';
        }

        $benefits_info = D('Benefits')->query("
            SELECT to_char(ADDTIME,'YYYY-MM-DD hh24:mi:ss') AS ADDTIME
            FROM erp_benefits
            WHERE ID = {$this->_request('ID')}
        ");

        if (is_array($benefits_info) && count($benefits_info)) {
            $addTime = $benefits_info[0]['ADDTIME'];
        }

        return $addTime;

    }

    private function benefitsScaleTypeList($info) {
        $response = array();
        if (is_array($info) && count($info)) {
            $project_scale_type = array(
                "BSTATUS" => $info[0]["BSTATUS"],
                "MSTATUS" => $info[0]["MSTATUS"],
                "ASTATUS" => $info[0]["ASTATUS"],
                "ACSTATUS" => $info[0]["ACSTATUS"],
                "CPSTATUS" => $info[0]["CPSTATUS"],
                "SCSTATUS" => $info[0]["SCSTATUS"],
            );

            $SCALETYPE_CONFIG = array(
                "BSTATUS" => array('TYPE' => 1, 'DESC' => '����'),
                "MSTATUS" => array('TYPE' => 2, 'DESC' => '����'),
                "ASTATUS" => array('TYPE' => 3, 'DESC' => 'Ӳ��'),
                "ACSTATUS" => array('TYPE' => 4, 'DESC' => '�'),
                "CPSTATUS" => array('TYPE' => 5, 'DESC' => '��Ʒ'),
                "SCSTATUS" => array('TYPE' => 8, 'DESC' => '���ҷ��ճ�')
            );

            // ����
            // 5=ҵ����ֹ 3=���
            if ($project_scale_type["BSTATUS"] && (intval($project_scale_type["BSTATUS"]) != 5)) {
                $response[$SCALETYPE_CONFIG["BSTATUS"]['TYPE']] = $SCALETYPE_CONFIG["BSTATUS"]['DESC'];
            }

            // ����
            // 5=ҵ����ֹ 3=���
            if ($project_scale_type["MSTATUS"] && (intval($project_scale_type["MSTATUS"]) != 5 )) {
                $response [$SCALETYPE_CONFIG["MSTATUS"]['TYPE']] = $SCALETYPE_CONFIG["MSTATUS"]['DESC'];
            }

            // Ӳ��
            // 5=ҵ����ֹ 3=���
            if ($project_scale_type["ASTATUS"] && (intval($project_scale_type["ASTATUS"]) != 5 )) {
                $response[$SCALETYPE_CONFIG["ASTATUS"]['TYPE']] = $SCALETYPE_CONFIG["ASTATUS"]['DESC'];
            }

            // �
            // 5=ҵ����ֹ 3=���
            if ($project_scale_type["ACSTATUS"] && (intval($project_scale_type["ACSTATUS"]) != 5 /*&& intval($project_scale_type["ACSTATUS"] != 3*/)) {
                $response[$SCALETYPE_CONFIG["ACSTATUS"]['TYPE']] = $SCALETYPE_CONFIG["ACSTATUS"]['DESC'];
            }

            // ���ҷ��ճ�
            // 5=ҵ����ֹ 3=���
            if ($project_scale_type['SCSTATUS'] && (intval($project_scale_type["SCSTATUS"]) != 5 )) {
                $response [$SCALETYPE_CONFIG['SCSTATUS']['TYPE']] = $SCALETYPE_CONFIG['SCSTATUS']['DESC'];
            }
        }

        return $response;
    }

    /**
     * �ʽ�ط���ҳ��
     */
    public function fundPoolCost() {
        $prjId = intval($_REQUEST['prjId']);
        if ($prjId) {
            $this->project_case_auth($prjId);  // ��ĿȨ���ж�
            // �����Ŀ��Ϣ
            $this->checkFundPoolProject($prjId, $maxApplyCost);
            $showForm = $_REQUEST['showForm'];
            $action = $_REQUEST['faction'];
            if ($action == 'saveFormData') {
                $_REQUEST['MAX_APPLY_COST'] = $maxApplyCost;
                $this->saveFundPool($showForm, $prjId, $_REQUEST);
            }

            $prjInfo = D('Project')->get_info_by_id($prjId, array("PROJECTNAME", "CONTRACT", "BSTATUS", "MSTATUS", "ASTATUS", "ACSTATUS", "CPSTATUS", "SCSTATUS"));
            if (notEmptyArray($prjInfo)) {
                $prjName = $prjInfo[0]['PROJECTNAME'];
                $contract = $prjInfo[0]['CONTRACT'];
            }

            Vendor('Oms.Form');
            $form = new Form();
            $form->initForminfo(203);

            if ($showForm == 3 || $showForm == 1) {
                $form->setMyFieldVal('ADDTIME',date('Y-m-d H:i:s'), true)
                    ->setMyField('ISCOST', 'FORMVISIBLE', "0")
                    ->setMyField("TYPE", "FORMVISIBLE", "0")
                    ->setMyFieldVal('SCALE_TYPE', 1, true);
            }

            $form->setMyField('NAME','EDITTYPE','1')
                 ->setMyField("STATUS", "GRIDVISIBLE", "0")
                 ->setMyFieldVal('PROJECT_ID', $prjId, true)
                 ->setMyFieldVal('PROJECT_NAME', $prjName, true)
                 ->setMyFieldVal('CONTRACT', $contract, true)
                 ->setMyFieldVal('NAME',$_SESSION['uinfo']['tname'],true);

            //��Ӧ��
            if($showForm != 3 && $showForm != 1) {
                $form->setMyField('SUPPLIER', 'EDITTYPE', 21, FALSE);
            }
            if($showForm == 1){
                $supplierId = M("Erp_benefits")->where("ID=".$_REQUEST['ID'])->getField("SUPPLIER");
                $supplierName = M("Erp_supplier")->where("ID=".$supplierId)->getField("NAME");
                $form = $form->setMyFieldVal('SUPPLIER', $supplierName,false);
            }

            $form = $form->setMyField('SUPPLIER', 'LISTSQL', 'SELECT ID,NAME FROM ERP_SUPPLIER', FALSE);

            // ��ʾ��Ŀ��ҵ������
            $benefitsScaleTypeList = $this->benefitsScaleTypeList($prjInfo);
            if (count($benefitsScaleTypeList) == 1) {
                $keyList = array_keys($benefitsScaleTypeList);
                $form->setMyFieldVal("SCALE_TYPE", $keyList[0], true);
            } else {
                $form->setMyField("SCALE_TYPE",'LISTCHAR', array2listchar($benefitsScaleTypeList));
            }

            // ��������״̬���ð�ť�Ƿ�ɼ�
            $form->EDITCONDITION = '%ISCOST% == 1';
            $form->DELCONDITION = '%ISCOST% == 1';


            $this->assign('srcUrl', U('Benefits/fundPoolCost', 'prjId=' . $prjId));
            $this->assign('html', $form->where("TYPE = 2 and PROJECT_ID = {$prjId}")->getResult());
            $this->assign('prjName', $prjName);
			$this->assign('S_ID_GET', $supplierId);
            $this->display('fund_pool_cost');
        } else {
            // header('404.html');
        }
    }

    private function saveFundPool($showForm = 1, $prjId, $data) {
        $dbResult = false;
        $scaleType = $_REQUEST['SCALE_TYPE'];
        $_REQUEST['DESRIPT'] = trim($_REQUEST['DESRIPT']);

        $fundPool = D('Project')->getFundPoolRatio($prjId);
        if ($fundPool['result']) {
            $ratio = floatval($fundPool['ratio']);
            if ($fundPool['type'] == 0 && $ratio ==0) {
                ajaxReturnJSON(false, g2u('�ʽ�ر���Ϊ0������������ſ�֧��'));
            }
        }

        if (floatval($data['MAX_APPLY_COST']) <= 0) {
            ajaxReturnJSON(false, g2u("��ǰ����֧�������0����������"));
        }

        if (floatval($data['AMOUNT']) > floatval($data['MAX_APPLY_COST'])) {
            ajaxReturnJSON(false, g2u('�������������֧��������֧�����Ϊ' . $data['MAX_APPLY_COST']));
        }

        if (strlen($_REQUEST['DESRIPT']) > 480) {
            ajaxReturnJSON(false, g2u('˵�����������������ַ�������������160����'));
        }
        $where = sprintf("PROJECT_ID = %d AND SCALETYPE = %d", $prjId, $scaleType);
        $caseId = D('ProjectCase')->where($where)->getField('ID');
        if($ret_loan_limit = is_overtop_payout_limit($caseId, floatval($data['AMOUNT']),1)) {
            ajaxReturnJSON(false, g2u('�������ʱ����򳬳�����Ԥ�㣨�ܷ���>��Ʊ�ؿ�����*���ֳɱ��ʣ���������'));
        }

        if ($prjId) {
            D()->startTrans();
            if ($showForm == 3) {
                $scaleType = $data['SCALE_TYPE'];
                $bizData['PROJECT_ID'] = $prjId;
                $bizData['PROJECT_NAME'] = u2g($data["PROJECT_NAME"]);
                $bizData['AUSER_ID'] = $_SESSION["uinfo"]["uid"];
                $bizData['AMOUNT'] = $data["AMOUNT"];
                $bizData['DESRIPT'] =  u2g($data["DESRIPT"]);
                $bizData['TYPE'] = 2;
                $bizData['ADDTIME'] = date('Y-m-d H:i:s');
                $bizData['SCALE_TYPE'] = $scaleType;
                $bizData['CASE_ID'] = $caseId;
                $bizData['STATUS'] = 1;
                $bizData['ATTACHMENT'] = u2g($data['ATTACHMENT']);
                $bizData['SUPPLIER'] = u2g($data['S_ID_GET']);
                $bizData['ISCOST'] = 1;  // δ���뱨��
                $status = 2; // ����

                $dbResult = $dbResult = D('Benefits')->add($bizData);
            } else if ($showForm == 1) {
                $scaleType = $data['SCALE_TYPE'];
                $where = sprintf("PROJECT_ID = %d AND SCALETYPE = %d", $prjId, $scaleType);
                $caseId = D('ProjectCase')->where($where)->getField('ID');
                $bizData['CASE_ID'] = $caseId;
                $bizData['AMOUNT'] = $data["AMOUNT"];
                $bizData['DESRIPT'] =  u2g($data["DESRIPT"]);
                $bizData['SCALE_TYPE'] = $data["SCALE_TYPE"];
                $bizData['ATTACHMENT'] = u2g($data['ATTACHMENT']);
                $bizData['SUPPLIER'] = u2g($data['S_ID_GET']);
                $status = 1; // �޸�

                if ($data['ID']) {
                    $dbResult = D('Benefits')->where("ID = {$data['ID']}")->save($bizData);
                } else {
                    $dbResult = false;
                }
            }

            if ($dbResult !== false) {
                D()->commit();
                $msg = '����ɹ�';
            } else {
                D()->rollback();
                $msg = '����ʧ��';
                $status = 0;
            }
            ajaxReturnJSON($status, g2u($msg));
        }
    }

    /**
     * ����ʽ����Ŀ��Ϣ
     * @param $prjId
     */
    private function checkFundPoolProject($prjId, &$maxApplyCost) {
        
  
        if (intval($prjId)) {
            $fundPool = D('Project')->getFundPoolRatio($prjId);
            if ($fundPool['result']) {
                $ratio = floatval($fundPool['ratio']);
                
                if ($fundPool['type'] == 1) {
                    ajaxReturnJSON(false, g2u('���ʽ����Ŀ�����������ʽ�ط���'));
                }
                
                if ($ratio > 0) {
                    $ratio = $ratio / 100;
                }
                // �ʽ�ر���
                $this->assign('fundPoolRatio', $ratio * 100);
            }

            // ��Ŀȷ������
            $prjAffirmIncome = D('Project')->getProjectAffirmIncome($prjId);
            $this->assign('prjAffirmIncome', $prjAffirmIncome);

            // ����������
            $appliedCost = D('Project')->getProjectAppliedFundPoolCost($prjId);

            $maxApplyCost = round($prjAffirmIncome * $ratio - $appliedCost, 2);
            $this->assign('maxApplyCost', sprintf("%.2f", $maxApplyCost));
        }
    }
}