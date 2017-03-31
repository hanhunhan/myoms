<?php

/**
 * Class BenefitsAction branch
 */
class BenefitsAction extends ExtendAction{
    /**
     * 是否可以填写折后广告的权限ID
     */
    const DISCOUNT_AD_AUTHORITY = 257;

    /**
     * 是否可以填写在额度范围内的权限
     */
    const WITH_LIMIT_AUTHORITY = 358;

        /*合并当前模块的URL参数*/
        private $_merge_url_param = array();
        private $model;

        //构造函数
        public function __construct() 
        {
            $this->model = new Model();
            parent::__construct();

            // 权限映射表
            $this->authorityMap = array(
                'apply_benefit' => 456,
                'commit_other_benefits'=>461,
            );

            //TAB URL参数
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
        
        //业务津贴
		public function benefits()
        {
            $caseModel = D('ProjectCase');
            $prjectId = $_REQUEST["prjid"];
            $this->project_case_auth($prjectId);//项目业务权限判断
           //var_dump( $this->_merge_url_param);
            $project_model = D("Project");
            $benefits_model = D("Benefits");
            $case_model = D("ProjectCase");
            
            $benefits_status = $benefits_model->get_benefits_status();
            
			Vendor('Oms.Form');			
			$form = new Form();
			
            //根据项目Id获取项目信息
            $info = $project_model->get_info_by_id($prjectId,array("PROJECTNAME","BSTATUS","MSTATUS","ASTATUS","ACSTATUS","CPSTATUS", "SCSTATUS"));
            $prjname = $info[0]["PROJECTNAME"];
            // 获取当前项目包含的业务类型
            $benefitsScaleTypeList = $this->benefitsScaleTypeList($info);
            $form->initForminfo(115);

			if($_REQUEST['showForm'] == 3 && !$_REQUEST["ID"] && !$_REQUEST["faction"])//显示新增表单
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
                        $result["msg"] = "该硬广项目尚未执行（没有找到与之对应的合同信息），不能新增业务津贴!";
                        $result["msg"] = g2u($result["msg"]);
                        echo json_encode($result);
                        exit;
                    }
                }
                
 
                if( $data['AMOUNT'] >= 50000 && !in_array($_REQUEST["SCALE_TYPE"],array(3,4)) )
 
 
                {
                    $result["status"] = 0;
                    $result["msg"] = "普通业务津贴金额不能大于50000元!";
                }
                else
                {
                    $res = $benefits_model->add_benefits($data);
                    if($res){
                        $result["status"] = 2;
                        $result["msg"] = "新增成功!";
                    }else{
                        $result["status"] = 0;
                        $result["msg"] = "新增失败!";
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
                    $result["msg"] = "普通业务津贴金额不能大于50000元!";
                }
                else
                {
                     $res = $benefits_model->update_info_by_id(intval($_REQUEST["ID"]),$data);
                    if($res){
                        $result["status"] = 1;
                        $result["msg"] = "修改成功!";
                    }else{
                        $result["status"] = 0;
                        $result["msg"] = "修改失败!";
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
                    $result["msg"] = "删除成功";                   
                }
                else
                {
                    $result["status"] = "error";
                    $result["msg"] = "删除失败";
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
            //活动显示合同号
            if(count($benefitsScaleTypeList) == 1 && array_key_exists(4,$benefitsScaleTypeList)) {
                $form = $form->setMyField('CONTRACT_NO', 'LISTSQL', 'SELECT ID,CONTRACT_NO FROM ERP_INCOME_CONTRACT WHERE  CASE_ID = ' . $caseId, FALSE);
                $form->setMyField('CONTRACT_NO','FORMVISIBLE',-1)
                    ->setMyField('CONTRACT_NO','GRIDVISIBLE',-1);
            }
            // 显示项目的业务类型
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
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
            $this->assign("prjname",$prjname);
            $this->assign("paramUrl",$this->_merge_url_param);
            $this->assign('benefits_type',0);
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
			$this->display('benefits');
		 }
         
         //预算外其他业务费用（大额）
        public function otherBenefits()
        {	
            $prjectId = $_REQUEST["prjid"];    
			$this->project_case_auth($prjectId);//项目业务权限判断	
            $project_model = D("Project");
            $benefits_model = D("Benefits");
            $case_model = D("ProjectCase");
            $showForm = $_REQUEST['showForm'];
            $benefits_status = $benefits_model->get_benefits_status();
            $benefits_cost_status = $benefits_model->get_cost_status();
            
			Vendor('Oms.Form');			
			$form = new Form();			           
             //根据项目Id获取项目信息
            $info = $project_model->get_info_by_id($prjectId,array("PROJECTNAME","BSTATUS","MSTATUS","ASTATUS","ACSTATUS","CPSTATUS", "SCSTATUS"));
            $prjname = $info[0]["PROJECTNAME"];
            $benefitsScaleTypeList = $this->benefitsScaleTypeList($info);
            $form->initForminfo(115);
            $GABTN = '<a onclick="show_benefit_info();" href="javascript:;" id="commit_other_benefits" class="btn btn-info btn-sm">提交</a>'
                . '<a onclick="applyReimburse();" href="javascript:;" id="apply_reim" class="btn btn-info btn-sm">申请报销</a>'
                . '<a onclick="show_flow_step();" href="javascript:;" id="show_steps" class="btn btn-info btn-sm">申报流程图</a>';
            
			if($_REQUEST['showForm'] == 3 && !$_REQUEST["ID"] && !$_REQUEST["faction"])//显示新增表单
            {   
                $conf_where = "PROJECT_ID = ".$prjectId." and STATUS in(1,2)";
                $field_arr = array("ID"); 
                $benefits_info = $benefits_model->get_info_by_cond($conf_where,$field_arr);
                //if($benefits_info)//改项目下有未审批通过的业务津贴，不允许添加
                //{
                //    js_alert("该项目下有未审批结束的业务津贴或预算外其他费用，审批结束后才可以再次申请",
                //        U("Benefits/otherBenefits",$this->_merge_url_param));
                //}
                $form->GABTN = $GABTN;
                $form->setMyFieldVal('ADDTIME',date('Y-m-d H:i:s'),true);
				$form ->setMyField('NAME','EDITTYPE','1')
				->setMyField('NAME','READONLY','-1')
                ->setMyFieldVal('NAME',$_SESSION['uinfo']['tname'],true);
            }
            elseif( $_REQUEST['showForm'] == 3 && $_REQUEST["faction"] == "saveFormData" && !$_REQUEST["ID"] )//新增
            {
                if( $_REQUEST["AMOUNT"] < 50000 ){
                    $result["status"] = 0;
                    $result["msg"] = "预算外费用金额必须大于50000元！！";
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
                    $result["msg"] = "新增成功！";
                }else{
                    $result["status"] = 0;
                    $result["msg"] = "新增失败！";
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
                    //$result["msg"] = "预算外费用金额必须大于50000元!";
               // }
                //else
                //{
                    $res = $benefits_model->update_info_by_id(intval($_REQUEST["ID"]),$data);
                    if($res){
                        $result["status"] = 1;
                        $result["msg"] = "修改成功!";
                    }else{
                        $result["status"] = 0;
                        $result["msg"] = "修改失败!";
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
                    $result["msg"] = "删除成功";
                    
                }
                else
                {
                    $result["status"] = "error";
                    $result["msg"] = "删除失败";
                }
               $result["msg"] = g2u($result["msg"]);
               echo json_encode($result);
               exit;
            }


            //供应商
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
            // 显示项目的业务类型
            if (count($benefitsScaleTypeList) == 1) {
                $keyList = array_keys($benefitsScaleTypeList);
                $form->setMyFieldVal("SCALE_TYPE", $keyList[0], true);
            } else {
                $form->setMyField("SCALE_TYPE",'LISTCHAR', array2listchar($benefitsScaleTypeList));
            }

            //设置按钮展示与否

            $form->DELCONDITION = '%STATUS% == 1';
            $form->GABTN = $GABTN;
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);
            $formHtml = $form->where("TYPE=1 and PROJECT_ID=$prjectId")->getResult();
            $this->assign('isShowOptionBtn', $this->isProjectContextMenuRunnable($prjectId, 'otherBenefits'));
			$this->assign('form',$formHtml);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
            $this->assign("prjname",$prjname);
            $this->assign("paramUrl",$this->_merge_url_param);
            $this->assign('benefits_type',1);
			 $this->assign('supplierId',$supplierId);
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
			$this->display('benefits');
		 }
         
         //展示津贴相关信息
        public function show_benefit_info()
        {
            $flowId = $_REQUEST['flowId'] ? $flowId = $_REQUEST['flowId'] : 0;
            $benefits_id = !empty($_REQUEST["benefits_id"]) ? $_REQUEST["benefits_id"] : $_REQUEST["RECORDID"];

            //权限判断
            $permisition_con_dis = intval($this->haspermission(self::DISCOUNT_AD_AUTHORITY));//合同管理员权限
            $permisition_in_limit = intval($this->haspermission(self::WITH_LIMIT_AUTHORITY));//财务填选是否在额度内权限

            //业务津贴Model
            $benefits_model =D("Benefits");
            if($_GET['prjid'] )
            {
                $prjid = $_GET['prjid']; 
                $scale_type = !empty($_GET['scale_type']) ? $_GET['scale_type'] : 0;//业务类型
                $canCommitBenefit = D('ProjectCase')->canCommitBenefit($prjid, $scale_type);
                if (!$canCommitBenefit) {
                    js_alert("该项目不处于执行中或周期结束状态，不符合申请条件");
                    exit;
                }
                $case_model = D("ProjectCase"); 
                $conf_where = "PROJECT_ID=$prjid and SCALETYPE=$scale_type";
                $field_arr = array("ID");
                $case_id =$case_model->get_info_by_cond($conf_where,$field_arr);
                $case_id = $case_id[0]["ID"];
                $benefits_info = $benefits_model->get_info_by_id($benefits_id,array("DISCOUNT_AD","TYPE","FEE_TYPE","INQUOTA"));
                $discount_ad = $benefits_info[0]["DISCOUNT_AD"] !== null ? $benefits_info[0]["DISCOUNT_AD"] : -1; //折后广告
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
                $discount_ad = $benefits_info[0]["DISCOUNT_AD"] !== null ? $benefits_info[0]["DISCOUNT_AD"] : -1; //折后广告
                $benefits_type = $benefits_info[0]["TYPE"];
                $fee_type = $benefits_info[0]["FEE_TYPE"];
                $isquota = $benefits_info[0]["INQUOTA"];
            }
            $this->_merge_url_param['benefits_type'] = $benefits_type;
            $sql = $this->getBenefitSql($benefits_id, $scale_type);  // 根据项目类型获取的sql语句
            $arr = $this->model->query($sql);
            $user = D("Erp_users")->field("NAME")->where("ID=".$arr[0]["AUSER_ID"])->find();
            $contract_no = $arr[0]["CONTRACT"];//合同编号
            $project_name = $arr[0]["PROJECTNAME"];//项目名称
            $company = $arr[0]["COMPANY"];//合同客户名称
            $contract_money = floatval($arr[0]["MONEY"]);  // 合同金额
            $apply_amount = $arr[0]["AMOUNT"];//本次申请金额
            $auser = $user["NAME"];


            
            $sql1 = "select sum(AMOUNT) SUMMONEY from erp_benefits a where a.CASE_ID = {$case_id} and status = 3";
            $arr1 = $this->model->query($sql1);
            $sum_money = $arr1[0]["SUMMONEY"] ? $arr1[0]["SUMMONEY"] : 0;//累计申请金额 (不含本次)
            
            //立项预算相关费用
            $case_type = D("ProjectCase")->get_info_by_id($case_id,array("SCALETYPE"));
            $case_type = $case_type[0]["SCALETYPE"];
                       
            //项目收入、付现利润、付现利润率、综合利润率
            if(in_array($case_type, array(1, 2)))//电商
            {
                $sql = "select SUMPROFIT,OFFLINE_COST_SUM_PROFIT,OFFLINE_COST_SUM_PROFIT_RATE,ONLINE_COST_RATE "
                . "from ERP_PRJBUDGET where CASE_ID = $case_id";
                //echo $sql;
                $fee_info = $this->model->query($sql);
                $prj_income_budget = $fee_info[0]["SUMPROFIT"] ? $fee_info[0]["SUMPROFIT"] : 0 ;//项目收入
                $pay_profit_budget = $fee_info[0]["OFFLINE_COST_SUM_PROFIT"] ? $fee_info[0]["OFFLINE_COST_SUM_PROFIT"] : 0;//付现利润
                $pay_profit_rate_budget = $fee_info[0]["OFFLINE_COST_SUM_PROFIT_RATE"] ? $fee_info[0]["OFFLINE_COST_SUM_PROFIT_RATE"] : 0;//付现利润率
                $Comprehensive_profit_rate_budget = $fee_info[0]["ONLINE_COST_RATE"] ? $fee_info[0]["ONLINE_COST_RATE"] : 0;//综合利润率
                
                //预估线下费用
                $sql1 = "select GETBUGVCOST($case_id) BUGVCOST from dual";
                $prj_budget_vcost = $this->model->query($sql1);
                $prj_budget_vcost = $prj_budget_vcost[0]["BUGVCOST"] ? $prj_budget_vcost[0]["BUGVCOST"] : 0;            
                
                //预估折后广告费
				$sql2 = "select nvl(sum(B.AMOUNT),0) vadcost from erp_prjbudget a,erp_budgetfee b where a.case_id = $case_id and a.id=B.BUDGETID and b.feeid =98 and b.isvalid=-1";

                $discount_ad_budget = $this->model->query($sql2);
                $discount_ad_budget = $discount_ad_budget ? $discount_ad_budget[0]["VADCOST"] : 0;
                
                //预估其他费用
                $sql3 = "select GETBUGOTHERCOST($case_id) OTHER_FEE from dual";
                $other_fee_budget = $this->model->query($sql3);
                //echo $case_id;
                $other_fee_budget = $other_fee_budget[0]["OTHER_FEE"] ? $other_fee_budget[0]["OTHER_FEE"] : 0;
            }
            else if($case_type == 4)//活动
            {
                $act_id = D("Erp_activities")->where("CASE_ID = ".$case_id)->field("ID")->find();
                $activities_id = $act_id["ID"];
                $sql1 = "SELECT PRINCOME,BUDGET FROM ERP_ACTIVITIES WHERE ID = ".$activities_id;
                $fee_info = $this->model->query($sql1);
                
                //项目收入
                $prj_income_budget = $fee_info[0]["PRINCOME"];
                
                //线下费用
                $prj_budget_vcost = $fee_info[0]["BUDGET"];
                
                //付现利润
                $pay_profit_budget = $fee_info[0]["PRINCOME"] - $fee_info[0]["BUDGET"];   
                
                //付现利润率
                $pay_profit_rate_budget = round(($fee_info[0]["PRINCOME"] - $fee_info[0]["BUDGET"]) * 100 /$fee_info[0]["PRINCOME"],2);
                
                //预估其他费用
                $other_fee_budget = 0;
                
                //预估折后广告费
                //$sql2 = "select GETVADCOST($activities_id,$case_type) AD_BUDGET from dual";
				$sql2 = "select nvl(sum(B.AMOUNT),0) vadcost from erp_actibudgetfee B where B.activities_id = $activities_id  and b.fee_id = 98 and b.isvalid=0";
                $discount_ad_budget = $this->model->query($sql2);
				
                $discount_ad_budget = $discount_ad_budget ? $discount_ad_budget[0]["VADCOST"] : 0;
               //综合利润率
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
            
            //项目执行状况相关费用     
            if(in_array($case_type, array(1, 2)))
            {
                //项目收入
                if ($case_type == 1) {
                    // 电商是开票收入
                    $prj_income_e =  $this->model->query("select getprjdata($case_id,2,null) PRJ_INCOME_E from dual");
                } else {
                    // 分销是回款收入
                    $prj_income_e =  $this->model->query("select getCaseInvoiceAndReturned($case_id, 2, 2) PRJ_INCOME_E from dual");
                }
                $prj_income_e = $prj_income_e ? $prj_income_e[0]["PRJ_INCOME_E"] : 0;

                //实际线下费用
                $cost_fee_e = $this->model->query("select GETOFFLINECOST($case_id) COST_FEE_E from dual");
                $cost_fee_e = $cost_fee_e ? $cost_fee_e[0]["COST_FEE_E"] : 0;
                
                //付现利润
                $pay_profit_e = floatval($prj_income_e) - floatval($cost_fee_e);

                //付现利润率
                $pay_profit_rate_e = 0;
                if (floatval($prj_income_e) != 0) {
                    $pay_profit_rate_e = round($pay_profit_e * 100 / floatval($prj_income_e), 2);
                }

                //折后广告
                $discount_ad_e = $discount_ad;
                
                //执行（实际）其他费用
                $sql4 = "select SUM(AMOUNT) OTHER_FEE_E from erp_benefits where CASE_ID = $case_id and TYPE = 1 AND STATUS=3";
                $other_fee_e = $this->model->query($sql4);
                $other_fee_e = $other_fee_e[0]["OTHER_FEE_E"] ? $other_fee_e[0]["OTHER_FEE_E"] : 0;
                
               //执行（实际）综合利润率
                $Comprehensive_profit_rate_e = ($pay_profit_e - floatval($discount_ad_e)) / floatval($prj_income_e);
            }
            else if($case_type == 4)
            {
                //实际项目收入
                $sql = "select sum(INVOICE_MONEY) SUM_INVOICE_MONEY from erp_billing_record where CASE_ID = $case_id and status = 4";
                $prj_income_e = $this->model->query($sql);
                $prj_income_e = $prj_income_e[0]["SUM_INVOICE_MONEY"] ? $prj_income_e[0]["SUM_INVOICE_MONEY"] : 0;
                
                //实际线下费用
                $cost_fee_e = $this->model->query("select GETOFFLINECOST($case_id) COST_FEE_E from dual");
                $cost_fee_e = $cost_fee_e ? $cost_fee_e[0]["COST_FEE_E"] : 0;
                
                //付现利润
                $pay_profit_e = floatval($prj_income_e) - floatval($cost_fee_e);

                // 付现利润率
                $pay_profit_rate_e = null;
                if (floatval($prj_income_e) > 0) {
                    $pay_profit_rate_e = round($pay_profit_e * 100 / $prj_income_e, 2);
                }

                //折后广告
                $discount_ad_e = $discount_ad;
                //执行（实际）其他费用
                $other_fee_e = 0;
                //综合利润率
                $Comprehensive_profit_rate_e = round(($prj_income_e - $cost_fee_e - 0) * 100 / $prj_income_e, 2);
            }
            
            $project_exe_fee["prj_income_e"] = $prj_income_e;
            $project_exe_fee["cost_fee_e"] = $cost_fee_e;
            $project_exe_fee["pay_profit_e"] = $pay_profit_e;
            $project_exe_fee["pay_profit_rate_e"] = $pay_profit_rate_e;
            $project_exe_fee["discount_ad_e"] = $discount_ad_e;
            $project_exe_fee["other_fee_e"] = $other_fee_e;
            $project_exe_fee["Comprehensive_profit_rate_e"] = $Comprehensive_profit_rate_e; 
                       
            //硬广项目实际执行相关费用            
            //开票金额
            $sql5 = "select sum(INVOICE_MONEY) SUM_INVOICE_MONEY from erp_billing_record where CASE_ID = $case_id and status = 4";
            //echo $sql5;
            $invoice_money_ad = $this->model->query($sql5);
            $invoice_money_ad = $invoice_money_ad[0]["SUM_INVOICE_MONEY"] ? $invoice_money_ad[0]["SUM_INVOICE_MONEY"] : 0;
            
            //回款金额
            $sql6 = "SELECT SUM(t.INCOME) SUM_MONEY FROM erp_income_list t WHERE t.case_id = {$case_id} AND t.status = 4 ";
            $refund_money_ad = $this->model->query($sql6);
            $refund_money_ad= $refund_money_ad[0]["SUM_MONEY"] ? $refund_money_ad[0]["SUM_MONEY"] : 0;
           
            //实际线下费用
            $cost_fee_ad = $this->model->query("select GETOFFLINECOST($case_id) COST_FEE_E from dual");
            $cost_fee_ad = $cost_fee_ad ? $cost_fee_ad[0]["COST_FEE_E"] : 0;

            //付现利润
            $pay_profit_ad = $refund_money_ad - $cost_fee_ad;
            
            //付现利润率
            $pay_profit_rate_ad = round(($refund_money_ad - $cost_fee_ad)/$refund_money_ad * 100,2);
            
            $contract_discount_ad = $discount_ad;//合同折扣
            $contract_exe_fee["invoice_money_ad"] = $invoice_money_ad;
            $contract_exe_fee["refund_money_ad"] = $refund_money_ad;
            $contract_exe_fee["cost_fee_ad"] = $cost_fee_ad;
            $contract_exe_fee["pay_profit_ad"] = $pay_profit_ad;
            $contract_exe_fee["pay_profit_rate_ad"] = $pay_profit_rate_ad;
            $contract_exe_fee["contract_discount_ad"] = $contract_discount_ad;

            //控制类参数
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
            
            //津贴信息参数
            $this->assign('apply_amount',$apply_amount);
            $this->assign('contract_no',$contract_no);
            $this->assign('company',$company);
            $this->assign('contract_money',$contract_money);
            $this->assign('project_name',$project_name);
            $this->assign('sum_money',$sum_money);
            $this->assign('auser',$auser);
            
            //项目详情对比参数
            $this->assign("project_budget_fee",$project_budget_fee);//项目预算相关数据
            $this->assign("project_exe_fee",$project_exe_fee);//项目执行情况相关数据
            $this->assign("contract_exe_fee",$contract_exe_fee);//硬广执行情况相关数据
            
            $this->assign('discount_ad',$discount_ad);           
            $this->display('benefits_info');
         }
         
         /**
         +----------------------------------------------------------
         * 业务津贴审批意见
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
            
            $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;//项目ID
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
                            js_alert('办理成功',U('Flow/workStep'));
                        }else{
                            js_alert('办理失败');
                        }
                    }elseif($_REQUEST['flowPass']){
                        $str = $workflow->passWorkflow($_REQUEST);
                        if($str){
                            js_alert('同意成功',U('Flow/workStep'));
                        }else{
                            js_alert('同意失败');
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
                                // 业务津贴业务中ISVALID: 0=尚未审核 -1=已审核
                               D('erp_actibudgetfee')->where("CASE_ID = {$case_id} AND ISVALID = 0 AND FEE_ID = 98")->save(array(
                                    'ISVALID' => 0
                                ));
                            }
                            js_alert('否决成功',U('Flow/workStep'));
                        }else{
                            js_alert('否决失败');
                        }

                    }
                    elseif($_REQUEST['flowStop'])
                    {
                        $case_id = $_REQUEST["CASEID"];
                        $case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
                        $scale_type = $case_info[0]["SCALETYPE"];

                        //如果项目成本（即已垫资金额） > 立预算总收益*垫资比例
                        $is_overtop_limit = is_overtop_payout_limit($case_id,$benefits_info[0]['AMOUNT']);
                        if($is_overtop_limit)
                        {
                            js_alert("该项目成本已超出垫资额度或超出费用预算（总费用>开票回款收入*付现成本率），流程不允许备案通过！",
                            U("Benefits/opinionFlow",$this->_merge_url_param),1);
                            die;
                        }
                        
                        $auth = $workflow->flowPassRole($flowId);
                        if(!$auth)
                        {
                            js_alert('未经过必经角色');exit;
                        }
                        
                        $str = $workflow->finishworkflow($_REQUEST);
                        if($str)
                        {
                            $sql = " UPDATE ERP_BENEFITS SET STATUS = 3 WHERE ID=".$_REQUEST["RECORDID"];
                            $res = D("Benefits")->execute($sql);
                            //工作流通过，添加成本记录                            
                            $search_arr = array("TYPE","CASE_ID","AMOUNT");
                            $benefits_info = $benefits_model->get_info_by_id($_REQUEST["RECORDID"],$search_arr);
                            $benefits_type = $benefits_info[0]["TYPE"];
                            
                            //往成本表中添加记录
                            $cost_info['CASE_ID'] = $benefits_info[0]["CASE_ID"];            //案例编号 【必填】       
                            $cost_info['ENTITY_ID'] = $_REQUEST["RECORDID"];                 //业务实体编号 【必填】
                            $cost_info['EXPEND_ID'] = $_REQUEST["RECORDID"];                //成本明细编号 【必填】
                            
                            $cost_info['ORG_ENTITY_ID'] = $_REQUEST["RECORDID"];                 //业务实体编号 【必填】
                            $cost_info['ORG_EXPEND_ID'] = $_REQUEST["RECORDID"];
                            
                            $cost_info['FEE'] = $benefits_info[0]["AMOUNT"];                // 成本金额 【必填】 
                            $cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];             //操作用户编号 【必填】
                            $cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());        //发生时间 【必填】
                            $cost_info['ISFUNDPOOL'] = 0;                                 //是否资金池（0否，1是） 【必填】
                            $cost_info['ISKF'] = 1;                                     //是否扣非 【必填】
                            $cost_info['FEE_REMARK'] = "业务津贴申请";             //费用描述 【选填】
                            $cost_info['INPUT_TAX'] = 0;                                //进项税 【选填】
                            $cost_info['FEE_ID'] = 60;                                  //成本类型ID 【必填】
                            //成本来源
                            if($benefits_type == 0)
                            {
                               $cost_info['EXPEND_FROM'] = 17;
                                if ($scale_type == self::HD) {
                                    D()->startTrans();
                                    // 业务津贴业务中ISVALID: 0=尚未审核 -1=已审核
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
                            js_alert('备案成功',U('Flow/workStep'));
                        }else{
                            js_alert('备案失败');
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
                    js_alert('暂无权限');
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
                            js_alert('提交成功',U('Benefits/benefits',$this->_merge_url_param));
                        }else if($benefits_type === 1){
                            js_alert('提交成功',U('Benefits/otherBenefits',$this->_merge_url_param));
                        }
                        exit;
                    }
                    else
                    {
                        js_alert('提交失败',U('Benefits/opinionFlow',$this->_merge_url_param));
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
         * 预算外费用审批意见
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
            
            $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;//项目ID
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
                            js_alert('办理成功',U('Flow/workStep'));
                        }else{
                            js_alert('办理失败');
                        }
                    }elseif($_REQUEST['flowPass']){
                        $str = $workflow->passWorkflow($_REQUEST);
                        if($str){
                            js_alert('同意成功',U('Flow/workStep'));
                        }else{
                            js_alert('同意失败');
                        }
                    }elseif($_REQUEST['flowNot']){

                        $str = $workflow->notWorkflow($_REQUEST);
                        if($str){
                            $sql = " UPDATE ERP_BENEFITS SET STATUS = 4 WHERE ID=".$_REQUEST["RECORDID"];
                            $res = D("Benefits")->execute($sql);
                            js_alert('否决成功',U('Flow/workStep'));
                        }else{
                            js_alert('否决失败');
                        }

                    }elseif($_REQUEST['flowStop']){
                        $case_id = $_REQUEST["CASEID"];
                        $case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
                        $scale_type = $case_info[0]["SCALETYPE"];

                        //如果项目成本（即已垫资金额） > 立预算总收益*垫资比例
                        $is_overtop_limit = is_overtop_payout_limit($case_id,$benefits_info[0]['AMOUNT']);
                        if($is_overtop_limit)
                        {
                            js_alert("该项目成本已超出垫资额度或超出费用预算（总费用>开票回款收入*付现成本率），流程不允许备案通过！",
                            U("Benefits/otherBenefits",$this->_merge_url_param),1);
                            die;
                        }
                        
                        $auth = $workflow->flowPassRole($flowId);
                        if(!$auth){
                            js_alert('未经过必经角色');exit;
                        }
                        
                        $str = $workflow->finishworkflow($_REQUEST);
                        if($str){
                            $sql = " UPDATE ERP_BENEFITS SET STATUS = 3 WHERE ID=".$_REQUEST["RECORDID"];
                            $res = D("Benefits")->execute($sql);
                            //工作流通过，添加成本记录                            
                            $search_arr = array("TYPE","CASE_ID","AMOUNT");
                            $benefits_info = $benefits_model->get_info_by_id($_REQUEST["RECORDID"],$search_arr);
                            $benefits_type = $benefits_info[0]["TYPE"];
                            
                            //往成本表中添加记录
                            $cost_info['CASE_ID'] = $benefits_info[0]["CASE_ID"];            //案例编号 【必填】       
                            $cost_info['ENTITY_ID'] = $_REQUEST["RECORDID"];                 //业务实体编号 【必填】
                            $cost_info['EXPEND_ID'] = $_REQUEST["RECORDID"];                //成本明细编号 【必填】
                            
                            $cost_info['ORG_ENTITY_ID'] = $_REQUEST["RECORDID"];                 //业务实体编号 【必填】
                            $cost_info['ORG_EXPEND_ID'] = $_REQUEST["RECORDID"];
                            
                            $cost_info['FEE'] = $benefits_info[0]["AMOUNT"];                // 成本金额 【必填】 
                            $cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];             //操作用户编号 【必填】
                            $cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());        //发生时间 【必填】
                            $cost_info['ISFUNDPOOL'] = 0;                                 //是否资金池（0否，1是） 【必填】
                            $cost_info['ISKF'] = 1;                                     //是否扣非 【必填】
                            $cost_info['FEE_REMARK'] = "预算外费用申请";             //费用描述 【选填】
                            $cost_info['INPUT_TAX'] = 0;                                //进项税 【选填】
                            $cost_info['FEE_ID'] = 60;                                  //成本类型ID 【必填】
                            //成本来源
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
                            js_alert('备案成功',U('Flow/workStep'));
                        }else{
                            js_alert('备案失败');
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
                    js_alert('暂无权限');
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
                            js_alert('提交成功',U('Benefits/benefits',$this->_merge_url_param));
                        }else if($benefits_type === 1){
                            js_alert('提交成功',U('Benefits/otherBenefits',$this->_merge_url_param));
                        }
                        exit;
                    }
                    else
                    {
                        js_alert('提交失败',U('Benefits/opinionFlow',$this->_merge_url_param));
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
         * 报销申请
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
            //当前用户编号
            $uid = intval($_SESSION['uinfo']['uid']);
            //当前用户姓名
            $user_truename = $_SESSION['uinfo']['tname'];
            //当前城市编号
            $city_id = intval($this->channelid);
             
            //报销单类型
            $reimburse_type = 2;
             
            //津贴ID
            $benefits_id = $_REQUEST["benefits_id"]; 
            //var_dump($benefits_id);die;            
            $field_arr = array("AMOUNT","STATUS","CASE_ID");
            $benefits_info = $benefits_model->get_info_by_id($benefits_id,$field_arr);  
           // echo $benefits_model->_sql();die;
            $amount = $benefits_info[0]["AMOUNT"];            
            $status = $benefits_info[0]["STATUS"];
            $case_id = $benefits_info[0]["CASE_ID"];
            
            if($status != $benefits_status["passed"]){//津贴尚未申请 或还在审核过程中，不允许报销
                $result['state'] = 0;
                $result["msg"] = "已通过审核的津贴才可以申请报销, 该津贴尚未申请审核或还在审核过程中，不允许报销！";
                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }
            
            $is_exist = $benefits_model->get_info_by_id($benefits_id, array("ISCOST"));
            //echo M()->_sql();die;
            if( $is_exist[0]["ISCOST"] != 1 ) //所选记录已申请报销，不能重复申请
            {
                $result['state'] = 0;
                $result["msg"] = "该津贴已申请报销，不能重复申请！";
                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit();
            }
            else
            {
                $reim_list_status = $reim_list_model->get_conf_reim_list_status();                  
                //生成新的报销单             
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
                //报销明细添加成功，将金额累加到报销单中
                $sql = "update ERP_REIMBURSEMENT_LIST set AMOUNT = AMOUNT + '$amount' where ID = $last_id";
                $up_num = $this->model->execute($sql);
                //修改报销状态
                $benefits_arr["ISCOST"] = $benefits_reim_status["applied_reim"];
                $res1 = $benefits_model->update_info_by_id($benefits_id,$benefits_arr);
                if($res1 && $up_num){
                    $this->model->commit();
                    $result['state'] = 1;
                    $result["msg"] = "报销申请成功！";
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit();
                }else{
                    $this->model->rollback();
                    $result['state'] = 0;
                    $result["msg"] = "报销申请失败！！！";
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit();
                }
             }             
         }
         
         //查看流程图
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
                     $result["msg"] = "对不起，未找到相关流程信息";
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
                $data["ISVALID"] = 0;  // 0=尚未审核 -1=已审核
                $data["MARK"] = "折后广告费";

                $caseID = D('ProjectCase')->where("PROJECT_ID = {$_REQUEST['prjid']} AND SCALETYPE = {$_REQUEST['scale_type']}")->getField('ID');
                if ($caseID !== false) {
                    $data['CASE_ID'] = $caseID;
                }
                
                //根据活动id查看是否已经有记录
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
                    $result["msg"] = "数据保存成功！";
                }
                else
                {
                    $result['state'] = 0;
                    $result["msg"] = "数据保存失败！";
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
                $result["msg"] = "数据保存成功！";
            }
            else
            {
                $result['state'] = 0;
                $result["msg"] = "数据保存失败！";
            }
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        
       /**
        * 报销单列表
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
            
            //删除报销单
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
                    //删除关联借款关系
                    $loan_model = D('Loan');
//                    $up_num_loan = $loan_model->cancle_related_loan_by_reim_ids($reim_list_id);
                    $up_num_loan = $loan_model->cancleRelatedLoan($reim_list_id);
                    $iscost_status = $benefits_iscost_status["no_apply_reim"];
                    $benefits_up_num = $bebefits_model->update_info_by_id($benefits_ids,array("ISCOST"=>$iscost_status));
                    
                    if($detail_up_num && $benefits_up_num)
                    {
                        $this->model->commit();
                        //删除报销单 修改对应津贴的报销状态为未申请报销
                        
                        $result["status"] = "success";
                        $result["msg"] = "报销单删除成功！";
                        $result["msg"] = g2u($result["msg"]);
                        echo json_encode($result);
                        exit;
                    }
                    else
                    {
                        $this->model->rollback();
                        $result["status"] = "error";
                        $result["msg"] = "报销单删除失败！";
                        $result["msg"] = g2u($result["msg"]);
                        echo json_encode($result);
                        exit;
                    }
                }
                else
                {
                    $this->model->rollback();
                    $result["status"] = "error";
                    $result["msg"] = "报销单删除失败！";
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
            //根据case_id 查找报销明细中的报销单id（LIST_ID）
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
                array('报销明细',U('/Benefits/reim_detail')),
                array('关联借款',U('/Loan/related_loan')),
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
           $form->GABTN = '<a href="javascript:;" id="sub_reim_apply" class="btn btn-info btn-sm">提交报销单</a>'
               . '<a href="javascript:;" id="related_my_loan" class="btn btn-info btn-sm">关联借款</a>';

            $formHtml = $form->getResult();
            $this->assign('isShowOptionBtn', $this->isProjectContextMenuRunnable($prjid, 'otherBenefits'));
           $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
            $this->assign("form",$formHtml);
            $this->assign("paramUrl",$this->_merge_url_param);
            $this->display("benefits_reim_list");
       }
       
       /**
        * 报销明细
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
            
            //项目下累计已通过的业务津贴
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
           $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
           $this->assign("paramUrl",$this->_merge_url_param);
            $this->display("reim_detail");
        
       }
       
        /**
         +----------------------------------------------------------
         * 提交报销申请
         +----------------------------------------------------------
         * @param none
         +----------------------------------------------------------
         * @return none
         +----------------------------------------------------------
         */
        public function sub_reim_to_apply()
        {
            //申请报销单编号
            $reim_list_id_arr = $_GET['reim_list_id'];

            if(is_array($reim_list_id_arr) && !empty($reim_list_id_arr))
            {
                $this->model->startTrans();
                //报销申请单MODEL
                $reim_list_model = D('ReimbursementList');
                $update_num = $reim_list_model->sub_reim_list_to_aduit($reim_list_id_arr);

                if($update_num > 0)
                {
                    //提交成功，修改津贴报销状态
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
                        $info['msg']  = '报销申请提交成功';
                    }
                    else
                    {
                        $this->model->rollback();
                        $info['state']  = 0;
                        $info['msg']  = '报销申请提交失败';
                    }
                   
                }
                else
                {
                    $this->model->rollback();
                    $info['state']  = 0;
                    $info['msg']  = '报销申请提交失败';
                }
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '报销申请提交失败';
            }

            $info['msg'] = g2u($info['msg']);
            echo json_encode($info);
            exit;
        }

    /**
     * 获取benefit的sql语句
     * @param $benefitId
     * @param $scaleType
     * @return string
     */
    private function getBenefitSql($benefitId, $scaleType) {
        if (empty($benefitId) || empty($scaleType)) {
            return '';
        }

        // 如果是电商项目
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
     * 获取津贴添加时间
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
                "BSTATUS" => array('TYPE' => 1, 'DESC' => '电商'),
                "MSTATUS" => array('TYPE' => 2, 'DESC' => '分销'),
                "ASTATUS" => array('TYPE' => 3, 'DESC' => '硬广'),
                "ACSTATUS" => array('TYPE' => 4, 'DESC' => '活动'),
                "CPSTATUS" => array('TYPE' => 5, 'DESC' => '产品'),
                "SCSTATUS" => array('TYPE' => 8, 'DESC' => '非我方收筹')
            );

            // 电商
            // 5=业务终止 3=办结
            if ($project_scale_type["BSTATUS"] && (intval($project_scale_type["BSTATUS"]) != 5)) {
                $response[$SCALETYPE_CONFIG["BSTATUS"]['TYPE']] = $SCALETYPE_CONFIG["BSTATUS"]['DESC'];
            }

            // 分销
            // 5=业务终止 3=办结
            if ($project_scale_type["MSTATUS"] && (intval($project_scale_type["MSTATUS"]) != 5 )) {
                $response [$SCALETYPE_CONFIG["MSTATUS"]['TYPE']] = $SCALETYPE_CONFIG["MSTATUS"]['DESC'];
            }

            // 硬广
            // 5=业务终止 3=办结
            if ($project_scale_type["ASTATUS"] && (intval($project_scale_type["ASTATUS"]) != 5 )) {
                $response[$SCALETYPE_CONFIG["ASTATUS"]['TYPE']] = $SCALETYPE_CONFIG["ASTATUS"]['DESC'];
            }

            // 活动
            // 5=业务终止 3=办结
            if ($project_scale_type["ACSTATUS"] && (intval($project_scale_type["ACSTATUS"]) != 5 /*&& intval($project_scale_type["ACSTATUS"] != 3*/)) {
                $response[$SCALETYPE_CONFIG["ACSTATUS"]['TYPE']] = $SCALETYPE_CONFIG["ACSTATUS"]['DESC'];
            }

            // 非我方收筹
            // 5=业务终止 3=办结
            if ($project_scale_type['SCSTATUS'] && (intval($project_scale_type["SCSTATUS"]) != 5 )) {
                $response [$SCALETYPE_CONFIG['SCSTATUS']['TYPE']] = $SCALETYPE_CONFIG['SCSTATUS']['DESC'];
            }
        }

        return $response;
    }

    /**
     * 资金池费用页面
     */
    public function fundPoolCost() {
        $prjId = intval($_REQUEST['prjId']);
        if ($prjId) {
            $this->project_case_auth($prjId);  // 项目权限判断
            // 检查项目信息
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

            //供应商
            if($showForm != 3 && $showForm != 1) {
                $form->setMyField('SUPPLIER', 'EDITTYPE', 21, FALSE);
            }
            if($showForm == 1){
                $supplierId = M("Erp_benefits")->where("ID=".$_REQUEST['ID'])->getField("SUPPLIER");
                $supplierName = M("Erp_supplier")->where("ID=".$supplierId)->getField("NAME");
                $form = $form->setMyFieldVal('SUPPLIER', $supplierName,false);
            }

            $form = $form->setMyField('SUPPLIER', 'LISTSQL', 'SELECT ID,NAME FROM ERP_SUPPLIER', FALSE);

            // 显示项目的业务类型
            $benefitsScaleTypeList = $this->benefitsScaleTypeList($prjInfo);
            if (count($benefitsScaleTypeList) == 1) {
                $keyList = array_keys($benefitsScaleTypeList);
                $form->setMyFieldVal("SCALE_TYPE", $keyList[0], true);
            } else {
                $form->setMyField("SCALE_TYPE",'LISTCHAR', array2listchar($benefitsScaleTypeList));
            }

            // 根据申请状态设置按钮是否可见
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
                ajaxReturnJSON(false, g2u('资金池比例为0，调整比例后才可支付'));
            }
        }

        if (floatval($data['MAX_APPLY_COST']) <= 0) {
            ajaxReturnJSON(false, g2u("当前最大可支付金额是0，不能申请"));
        }

        if (floatval($data['AMOUNT']) > floatval($data['MAX_APPLY_COST'])) {
            ajaxReturnJSON(false, g2u('申请金额超过了最大可支付金额，最大可支付金额为' . $data['MAX_APPLY_COST']));
        }

        if (strlen($_REQUEST['DESRIPT']) > 480) {
            ajaxReturnJSON(false, g2u('说明超过了最大可输入字符数，最多可输入160个字'));
        }
        $where = sprintf("PROJECT_ID = %d AND SCALETYPE = %d", $prjId, $scaleType);
        $caseId = D('ProjectCase')->where($where)->getField('ID');
        if($ret_loan_limit = is_overtop_payout_limit($caseId, floatval($data['AMOUNT']),1)) {
            ajaxReturnJSON(false, g2u('超出垫资比例或超出费用预算（总费用>开票回款收入*付现成本率，不能申请'));
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
                $bizData['ISCOST'] = 1;  // 未申请报销
                $status = 2; // 新增

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
                $status = 1; // 修改

                if ($data['ID']) {
                    $dbResult = D('Benefits')->where("ID = {$data['ID']}")->save($bizData);
                } else {
                    $dbResult = false;
                }
            }

            if ($dbResult !== false) {
                D()->commit();
                $msg = '保存成功';
            } else {
                D()->rollback();
                $msg = '保存失败';
                $status = 0;
            }
            ajaxReturnJSON($status, g2u($msg));
        }
    }

    /**
     * 检查资金池项目信息
     * @param $prjId
     */
    private function checkFundPoolProject($prjId, &$maxApplyCost) {
        
  
        if (intval($prjId)) {
            $fundPool = D('Project')->getFundPoolRatio($prjId);
            if ($fundPool['result']) {
                $ratio = floatval($fundPool['ratio']);
                
                if ($fundPool['type'] == 1) {
                    ajaxReturnJSON(false, g2u('非资金池项目，不能申请资金池费用'));
                }
                
                if ($ratio > 0) {
                    $ratio = $ratio / 100;
                }
                // 资金池比例
                $this->assign('fundPoolRatio', $ratio * 100);
            }

            // 项目确认收入
            $prjAffirmIncome = D('Project')->getProjectAffirmIncome($prjId);
            $this->assign('prjAffirmIncome', $prjAffirmIncome);

            // 最大可申请额度
            $appliedCost = D('Project')->getProjectAppliedFundPoolCost($prjId);

            $maxApplyCost = round($prjAffirmIncome * $ratio - $appliedCost, 2);
            $this->assign('maxApplyCost', sprintf("%.2f", $maxApplyCost));
        }
    }
}