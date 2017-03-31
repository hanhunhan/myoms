<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * 会员退票操作类
 *
 * @author xuyemei
 */
class InvoiceRecycleAction extends ExtendAction{
    /**
     * 会员退票申请加入审核单权限
     */
    const ADD_TO_INVOICE_RECYCLE_AUDIT_LIST = 303;

    /**
     * 会员退票申请提交审核单权限
     */
    const VIEW_INVOICE_RECYCLE_AUDIT_LIST = 308;

    private $model;
    
    private $_merge_url_param = array();
    
    public function __construct() {
        $this->model = new Model();
        parent::__construct();
        // 权限映射表
        $this->authorityMap = array(
            'add_to_invoice_recycle_audit_list' => self::ADD_TO_INVOICE_RECYCLE_AUDIT_LIST,
            'view_invoice_recycle_audit_list' => self::VIEW_INVOICE_RECYCLE_AUDIT_LIST
        );
        //TAB URL参数
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : '';
        !empty($_GET['invoice_recycle_list_id']) ? $this->_merge_url_param['invoice_recycle_list_id'] = $_GET['invoice_recycle_list_id'] : '';
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = $_GET['flowId'] : '';
		!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;
    }
    
    /**
     * 申请退票,添加退票明细记录
     * 
     */
    public function apply_invoice_recycle(){
        $invoice_recycle_model = D("InvoiceRecycle");
        //会员退票申请状态数组
        $conf_invoice_recycle_status  = $invoice_recycle_model->get_conf_invoice_recycle_status();
        
        //当前城市编号
    	$city_id = intval($this->channelid);
        
        $member_model = D("Member");
        $member_ids = array();
        $need_agency_award = array();//需要追回中介佣金的会员ID
        $member_ids = $_POST["memberid"];
        $member_id_str = implode(",", $member_ids);
        $cond_where = "MID IN($member_id_str) AND STATUS NOT IN (".$conf_invoice_recycle_status["invoice_recycle_delete"].",". $conf_invoice_recycle_status["invoice_recycle_stop"] .")";
        $is_exist = $invoice_recycle_model->get_invoice_recycle_detail_info_by_cond($cond_where,array("ID"));
        if( $is_exist )
        {
            $result["status"] = 0;
            $result["msg"] = "退票申请添加失败，您所选会员中包含有已经申请过退票的会员，不能重复申请！";
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }
        
        //符合退票条件的用户加入到退票申请中
        $invoice_status = $member_model ->get_conf_invoice_status();
               
        foreach ($member_ids as $key=>$val)
        {
            $member_info = $member_model->get_info_by_id($val,array("AGENCY_REWARD","AGENCY_DEAL_REWARD","PROPERTY_DEAL_REWARD","INVOICE_STATUS"));
            
            //发票状态为已开未领或已领的会员才可以申请退票
            if($member_info["INVOICE_STATUS"] == $invoice_status["has_taken"] ||
                $member_info["INVOICE_STATUS"] == $invoice_status["invoiced"])
            {                
                if($member_info["AGENCY_REWARD"] || $member_info["AGENCY_DEAL_REWARD"] || $member_info["PROPERTY_DEAL_REWARD"])
                {
                    //$cond_where = "BUSINESS_ID = ".$val." AND TYPE IN(3,4,5,6)";
                    //$search_arr = array("LIST_ID");
                    //根据当前所选择的会员是否有已经提交报销的佣金，判断是否需要提示索回佣金
                    $sql = "SELECT A.ID,A.STATUS FROM ERP_REIMBURSEMENT_LIST A "
                        . "LEFT JOIN ERP_REIMBURSEMENT_DETAIL B ON A.ID=B.LIST_ID "
                        . "LEFT JOIN ERP_CARDMEMBER C ON C.ID=B.BUSINESS_ID "
                        . "WHERE A.TYPE IN(3,4,5,6) AND B.BUSINESS_ID = ".$val." AND A.STATUS IN(1,2) AND B.STATUS != 4";

                    $reim_list_info = $this->model->query($sql);
                    if($reim_list_info)
                    {
                        //需要追回佣金
                        $data["IS_AGENCY_REWARD"] = 1;

                        //中介佣金是否已追回
                        $data["AGENCY_REWARD_STATUS"] = 0;

                        $need_agency_award[]= $val;
                    }
                               
                }
                else
                {

                    $data["IS_AGENCY_REWARD"] = 0;

                    $data["AGENCY_REWARD_STATUS"] = "";
                }

                //会员编号
                $data["MID"] = $val;

                //退票申请人ID
                $data["APPLY_USER"] = $_SESSION["uinfo"]["uid"];

                //退票申请时间
                $data["APPLY_TIME"] = date("Y-m-d H:i:s");   

                //退票申请状态
                $data["STATUS"] = $conf_invoice_recycle_status["invoice_recycle_no_sub"]; 

                //城市
                $data["CITY_ID"] = $city_id;
                //添加退票申请记录
                $this->model->startTrans();
                $insertid[] = $invoice_recycle_model->add_invoice_recycle_details($data);

                } 
            }
            
            if(is_array($insertid) && !empty($insertid)){
                $result["status"] = 1;
                if(is_array($need_agency_award) && !empty($need_agency_award)){
                    //添加佣金索回会员记录
                    $commission_model = D("CommissionBack");
                    $commission_status = $commission_model->get_conf_commission_status();
                    foreach($need_agency_award as $k=>$v)
                    {
                        $commission_info["MID"] = $v;
                        $commission_info["STATUS"] = $commission_status["no_back"];
                        //佣金类型
                        $sql = "SELECT A.ID,A.TYPE FROM ERP_REIMBURSEMENT_LIST A "
                        . "LEFT JOIN ERP_REIMBURSEMENT_DETAIL B ON A.ID=B.LIST_ID "
                        . "LEFT JOIN ERP_CARDMEMBER C ON C.ID=B.BUSINESS_ID "
                        . "WHERE A.TYPE IN(3,4,5,6) AND B.BUSINESS_ID = ".$v." AND A.STATUS IN(1,2) AND B.STATUS != 4";
                        $reim_list_info = $this->model->query($sql);
                        //$reim_type_arr = array2new($reim_list_info);
                        //var_dump($reim_list_info);die;
                        foreach($reim_list_info as $reim_type_val)
                        {
                           switch ($reim_type_val["TYPE"])
                           {
                                //中介佣金索回
                                case 3:
                                    $commission_info["TYPE"] = 1; 
                                    $commission_info["REIM_LIST_ID"] = $reim_type_val["ID"];
                                    $commission_insert_id = $commission_model->add_commission_info($commission_info);
                                  break;
                                //中介成交奖索回
                                case 4:
                                    $commission_info["TYPE"] = 2; 
                                    $commission_info["REIM_LIST_ID"] = $reim_type_val["ID"];
                                    $commission_insert_id = $commission_model->add_commission_info($commission_info);
                                    break;
                                //置业顾问成交奖索回
                                case 6:
                                    $commission_info["TYPE"] = 3; 
                                    $commission_info["REIM_LIST_ID"] = $reim_type_val["ID"];
                                    $commission_insert_id = $commission_model->add_commission_info($commission_info);
                                   break;
                               default :
                                   break;                               
                               
                            }
                            if(!$commission_insert_id)
                            {
                                $this->model->rollback();
                                $result["status"] = 0;
                                $result["msg"] = "退票申请添加失败！请重新尝试";       
                                $result["msg"] = g2u($result["msg"]);
                                echo json_encode($result);
                                exit;
                            }
                        }
                        
                    }
                    $this->model->commit();
                    $need_agency_award_str = implode(",", $need_agency_award);
                    $result["msg"] = "退票申请添加成功,会员编号为".$need_agency_award_str."的会员"
                        . "已提交中介佣金，或中介成交奖，或置业顾问成交奖的报销申请，需要索回！";
                }
                else
                {
                    $this->model->commit();
                    $result["msg"] = "退票申请添加成功!";
                }            
            }
            else
            {
                $result["status"] = 0;
                $result["msg"] = "退票申请添加失败，发票状态为已开未领或已领的会员才可以申请退票，"
                    . "所选择的会员中没有符合退票条件的用户！";
            }
           
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
        
    }
    
    /**
     +----------------------------------------------------------
     * 申请退票管理列表
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function invoice_recycle_list()
    {
        if($_REQUEST['act'] == "export"){
            $filter_sql = isset($_GET['Filter_Sql'])?trim($_GET['Filter_Sql']):'';

            Vendor('phpExcel.PHPExcel');
            $Exceltitle = '会员退票';//
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);
            $objActSheet = $objPHPExcel->getActiveSheet();
            $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
            $objActSheet->getDefaultRowDimension()->setRowHeight(25);//默认行宽
            $objActSheet->getDefaultColumnDimension()->setWidth(12);//默认列宽
            $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'宋体'));
            $objActSheet->getDefaultStyle()->getFont()->setSize(10);
            $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//自动换行
            $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objActSheet->getRowDimension('1')->setRowHeight(40);
            $objActSheet->getRowDimension('2')->setRowHeight(26);

            $objActSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
            $objActSheet->getStyle('A2:K2')->getFont()->setBold(true);

            $styleArray = array(
                'borders' => array (
                    'allborders' => array (
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array ('argb' => 'FF000000'),//设置border颜色
                    ),
                ),
            );

            $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'会员退票'));
            $objActSheet->setCellValue('A2', iconv("gbk//ignore","utf-8//ignore",'编号'));
            $objActSheet->setCellValue('B2', iconv("gbk//ignore","utf-8//ignore",'合同编号'));
            $objActSheet->setCellValue('C2', iconv("gbk//ignore","utf-8//ignore",'项目名称'));
            $objActSheet->setCellValue('D2', iconv("gbk//ignore","utf-8//ignore",'客户姓名'));
            $objActSheet->setCellValue('E2', iconv("gbk//ignore","utf-8//ignore",'手机号码'));
            $objActSheet->setCellValue('F2', iconv("gbk//ignore","utf-8//ignore",'退票申请人'));
            $objActSheet->setCellValue('G2', iconv("gbk//ignore","utf-8//ignore",'发票编号'));
            $objActSheet->setCellValue('H2', iconv("gbk//ignore","utf-8//ignore",'收据编号'));
            $objActSheet->setCellValue('I2', iconv("gbk//ignore","utf-8//ignore",'申请日期'));
            $objActSheet->setCellValue('J2', iconv("gbk//ignore","utf-8//ignore",'发票状态'));
            $objActSheet->setCellValue('K2', iconv("gbk//ignore","utf-8//ignore",'退票状态'));
            $objActSheet->mergeCells('A1:K1');
            $member_id = $_REQUEST["member_id"];
            if($member_id) {
                $sql = "select a.ID,a.APPLY_USER,a.APPLY_TIME,a.STATUS,a.CITY_ID,a.LIST_ID,
            b.REALNAME,b.MOBILENO,b.INVOICE_NO,b.INVOICE_STATUS,b.PRJ_ID,b.RECEIPTNO as RECEIPT_NO, c.CONTRACT,c.PROJECTNAME
            from erp_invoice_recycle_detail a
            left join erp_cardmember b on a.mid=b.id
            left join erp_project c on b.prj_id=c.id WHERE a.STATUS != 6" . $filter_sql . ' AND a.CITY_ID = ' . $this->channelid . " AND a.ID IN ($member_id) ";
            }
            else{
                $sql = "select a.ID,a.APPLY_USER,a.APPLY_TIME,a.STATUS,a.CITY_ID,a.LIST_ID,
            b.REALNAME,b.MOBILENO,b.INVOICE_NO,b.INVOICE_STATUS,b.PRJ_ID,b.RECEIPTNO as RECEIPT_NO, c.CONTRACT,c.PROJECTNAME
            from erp_invoice_recycle_detail a
            left join erp_cardmember b on a.mid=b.id
            left join erp_project c on b.prj_id=c.id WHERE a.STATUS != 6" . $filter_sql . ' AND a.CITY_ID = ' . $this->channelid;
            }

            $res = $this->model->query($sql);
            //var_dump($res);DIE;
            if(is_array($res)){
                $i = 3;
                foreach($res as $k => $r){

                    $objActSheet->setCellValue('A'.$i, $r['ID']);
                    $objActSheet->setCellValue('B'.$i, $r['CONTRACT']);
                    $projectname = str_replace(array("/","、",",","，")," ",$r['PROJECTNAME']);
                    $projectname = iconv("gbk//ignore","utf-8//ignore",$projectname);
                    $objActSheet->setCellValue('C'.$i, $projectname);
                    $realname = str_replace(array("/","、",",","，")," ",$r['REALNAME']);
                    $realname = iconv("gbk//ignore","utf-8//ignore",$realname);
                    $objActSheet->setCellValue('D'.$i, $realname);
                    $objActSheet->setCellValue('E'.$i, $r['MOBILENO']);
                    $applyUserArr = D()->query("select name from erp_users where id =".$r['APPLY_USER']);
                    $applyUser = $applyUserArr['0']['NAME'];
                    $applyUser = iconv("gbk//ignore","utf-8//ignore",$applyUser);
                    $objActSheet->setCellValue('F'.$i, $applyUser);
                    $objActSheet->setCellValue('G'.$i, $r['INVOICE_NO']);
                    $objActSheet->setCellValue('H'.$i, $r['RECEIPT_NO']);
                    $objActSheet->setCellValue('I'.$i, $r['APPLY_TIME']);
                    $invoice_recycle_model = D("InvoiceRecycle");
                    $invoice_recycle_status = $invoice_recycle_model->get_conf_invoice_recycle_status_remark();
                    $status = $invoice_recycle_status[$r['STATUS']];
                    $status = iconv("gbk//ignore","utf-8//ignore",$status);
                    $objActSheet->setCellValue('K'.$i, $status);
                    $member_model = D("Member");
                    $invoice_status = $member_model->get_conf_invoice_status_remark();
                    foreach($invoice_status as $invoice){
                        $invoiceStatus = $invoice[$r['INVOICE_STATUS']];
                    }
                    $invoiceStatus = iconv("gbk//ignore","utf-8//ignore",$invoiceStatus);
                    $objActSheet->setCellValue('J'.$i, $invoiceStatus);
                    //$objActSheet->getRowDimension($i)->setRowHeight(-1);
                    $objActSheet->getRowDimension($i)->setRowHeight(24);
                    $i++;
                    if($objActSheet->getRowDimension($i)->getRowHeight() > 0){
                        $objActSheet->getRowDimension($i)->setRowHeight($objActSheet->getRowDimension($i)->getRowHeight()+20);
                    }
                }
            }
            $objActSheet->getStyle('A1:K'.($i-1))->applyFromArray($styleArray);
            ob_end_clean();
            ob_start();
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");
            header("Content-Disposition:attachment;filename=".$Exceltitle.date("YmdHis").".xls");
            header("Content-Transfer-Encoding:binary");

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            userLog()->writeLog('', $_SERVER["REQUEST_URI"], '退票:导出退票:成功', serialize($_FILES['upfile']));
            exit;
        }
        //当前操作
        $faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        
    	//当前用户编号
    	$uid = intval($_SESSION['uinfo']['uid']);
    	
    	//当前城市编号
    	$city_id = intval($this->channelid);
    	
    	//退票MODEL
    	$invoice_recycle_model = D("InvoiceRecycle");
    	
    	Vendor('Oms.Form');
    	$form = new Form();
        
        $conf_invoice_recycle_status = $invoice_recycle_model->get_conf_invoice_recycle_status();
        $invoice_recycle_no_sub = isset($conf_invoice_recycle_status['invoice_recycle_no_sub']) ? 
                            $conf_invoice_recycle_status['invoice_recycle_no_sub'] : '';

         $cond_where = "CITY_ID = " . $city_id;
        //如果存在flowid则
        if($_REQUEST["flowId"]) {
            $cond_where = ' 1=1 ';
        }
        $form = $form->initForminfo(174);        
        if(!$_REQUEST["flowId"])
        {
            if(!$this->p_auth_all)
            {
                $cond_where .= "AND PRJ_ID IN (SELECT PRO_ID FROM ERP_PROROLE WHERE USE_ID = ".$uid." AND ISVALID = -1 AND (ERP_ID = 1 OR ERP_ID = 2 ))";
            }
        }
        //由工作流过来，只展示工作流相关的记录
        else
        {
            $invoice_recyle_id = $_REQUEST["RECORDID"];
            $cond_where .= " AND LIST_ID = '".$invoice_recyle_id."'";
        }
        
        $form->where($cond_where);        
        
        $form->setMyField("INVOICE_TIME", "GRIDVISIBLE", "0")
            ->setMyField("INVOICE_MONEY", "GRIDVISIBLE", "0")
            ->setMyField("REALNAME", "FIELDMEANS", "客户姓名")
            ->setMyField("MOBILENO", "FIELDMEANS", "手机号码")
            ->setMyField("APPLY_USER", "FIELDMEANS", "退票申请人")
            ->setMyField("INVOICE_NO", "GRIDVISIBLE", -1)
            ->setMyField("INVOICE_NO", "FORMVISIBLE", -1)
            ->setMyField("RECEIPT_NO", "GRIDVISIBLE", -1)
            ->setMyField("RECEIPT_NO", "FORMVISIBLE", -1)
            ->setMyField("APPLY_TIME", "FIELDMEANS", "申请日期");
        
        //设置退票状态
        $invoice_recycle_status = $invoice_recycle_model->get_conf_invoice_recycle_status_remark();
        //$form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($invoice_recycle_status), FALSE);
        //设置发票状态
        $member_model = D("Member");
        $invoice_ststus = $member_model->get_conf_invoice_status_remark();
       
        $form = $form->setMyField('INVOICE_STATUS', 'LISTCHAR', array2listchar($invoice_ststus["INVOICE_STATUS"]), FALSE);
        
        //退票申请人
        $form = $form->setMyField('APPLY_USER', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS');
       
        //流程处理
        if(intval($_REQUEST["flowId"]))
        {
            //有权限的可以进行处理，无权限隐藏按钮
            $is_edit = judgeFlowEdit(intval($_REQUEST["flowId"]),$_SESSION["uinfo"]["uid"]);
            if(!$is_edit)
            {
                $form->GABTN = "";
                $form->DELABLE = "0";
            }
            else
            {   
                $form->CZBTN = array(
                        '%STATUS% == 1'=>'<a class="cancel_from_details btn btn-danger btn-xs" href="javascript:;" title="删除"><i class="glyphicon glyphicon-trash"></i></a>',
                        '%STATUS% > 1' => '<font style="color:#333">——</font>');                
            }
        }
        //业务处理
        else
        {
            //未提交的退票申请可以删除，否则无法删除
            $form->CZBTN = array(
                        '%STATUS% == 1'=>'<a class="cancel_from_details btn btn-danger btn-xs" href="javascript:;" title="删除"><i class="glyphicon glyphicon-trash"></i></a>',
                        '%STATUS% > 1' => '<font style="color:#333">——</font>');
        }
        $form->GABTN .= "<a id='export_recycle' href='javascript:void(0);' class='btn btn-info btn-sm'>导出退票</a>";

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
        $formHtml =  $form->getResult();
    	$this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->assign('filter_sql',$form->getFilterSql());
        $this->assign('paramUrl',$this->_merge_url_param);
        //$this->assing('invoice_recycle_list_id',$);
    	$this->display('invoice_recycle_list');
    }


    /**
     +----------------------------------------------------------
     * 添加到退票审核单
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function add_to_audit_list()
    {
        $invoice_recycle_model = D("InvoiceRecycle");
        
        //当前用户编号
        $uid = intval($_SESSION['uinfo']['uid']);
        
        //当前城市编号
        $city_id = intval($this->channelid);
        
        //申请加入审核单的退票详情编号
        $invoice_recycle_id = $_POST['invoice_recycle_id'];
        
        if ($uid > 0 && $city_id > 0 && !empty($invoice_recycle_id) && !empty($_POST) )
        { 
            //查询当前用户最新一个退票申请单信息
            $invoice_recycle_info = array();

            $invoice_recycle_info = $invoice_recycle_model->get_last_invoice_recycle_list($uid, $city_id);
            //var_dump($invoice_recycle_info);die;
            if(is_array($invoice_recycle_info) && !empty($invoice_recycle_info) 
                    && $invoice_recycle_info['STATUS'] == 1)
            {
                $last_list_id = $invoice_recycle_info['ID'];
            }
            else
            {
                $invoice_recycle_info = array();
                $invoice_recycle_info['APPLY_USER'] = $uid;
                $invoice_recycle_info['APPLY_TIME'] = date('Y-m-d');
                $invoice_recycle_info['STATUS'] = 1;
                $invoice_recycle_info['CITY_ID'] = $city_id;
                $this->model->startTrans();
                $last_list_id = $invoice_recycle_model->add_invoice_recycle_list($invoice_recycle_info);
            }
            
            $update_num = 0;
            if($last_list_id > 0)
            {  
               $update_num = $invoice_recycle_model->add_details_to_audit_list($invoice_recycle_id, $last_list_id);
               
               if($update_num > 0)
               {
                   $this->model->commit();
                    $info['state']  = 1;
                    $info['msg']  = '加入审核单成功';
               }
               else
               {
                   $this->model->rollback();
                    $info['state']  = 0;
                    $info['msg']  = '加入审核单失败';
               }
            }
            else
            {
                $this->model->rollback();
                $info['state']  = 0;
                $info['msg']  = '生成审核单操作失败';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '参数错误';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
        exit;
    }
    
    /**
     +----------------------------------------------------------
     * (查看)退票审核单
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function invoice_recycle_audit_list()
    {
    	//当前用户编号
    	$uid = intval($_SESSION['uinfo']['uid']);
    	
    	//当前城市编号
    	$city_id = intval($this->channelid);
        
        //退票申请单编号
        $invoice_recycle_list_id = !empty($_GET['invoice_recycle_list_id']) ? intval($_GET['invoice_recycle_list_id']) : 0;
    	
    	Vendor('Oms.Form');
    	$form = new Form();
        
        //退票MODEL
        $invoice_recycle_model = D('InvoiceRecycle');
        $invoice_recycle_list_status = $invoice_recycle_model->get_conf_invoice_recycle_list_status();
        $invoice_recycle_detail_status = $invoice_recycle_model->get_conf_invoice_recycle_status_remark();
            
    	$last_list_id = 0;
        if( $invoice_recycle_list_id > 0 )
        {
            $last_list_id = $invoice_recycle_list_id;//查看该退票单下的退票信息
        }   
        else if ($uid > 0 && $city_id > 0)
        { 
            //查询当前用户最新一个退票申请单信息
            $invoice_recycle_info = array();
            $invoice_recycle_info = $invoice_recycle_model->get_last_invoice_recycle_list($uid, $city_id, 
            		$invoice_recycle_list_status['invoice_recycle_list_no_sub']);
			
            if(is_array($invoice_recycle_info) && !empty($invoice_recycle_info))
            {
            	$last_list_id = $invoice_recycle_info['ID'];
            }            
        }
        $invoice_recycle_status = $invoice_recycle_model->get_conf_invoice_recycle_status();
        
        $invoice_recycle_delete_status = !empty($invoice_recycle_status['invoice_recycle_delete']) ?  
                $invoice_recycle_status['invoice_recycle_delete']: '';
        
        $cond_where = "LIST_ID =  '".$last_list_id."'";
        $cond_where .= " AND STATUS != '".$invoice_recycle_delete_status."'";
        
    	$form = $form->initForminfo(174)
                     ->setMyField("INVOICE_NO", "GRIDVISIBLE", -1)
                     ->setMyField("INVOICE_NO", "FORMVISIBLE", -1)
                     ->setMyField("RECEIPT_NO", "GRIDVISIBLE", -1)
                     ->setMyField("RECEIPT_NO", "FORMVISIBLE", -1)
                     ->where($cond_where);
        
        //当前退票申请单信息
        $invoice_recycle_list_info = $invoice_recycle_model->get_invoice_recycle_list_by_id($last_list_id, array('ID', 'STATUS'));
        
        //未提交审核状态
        if(!empty($invoice_recycle_list_status) && 
                $invoice_recycle_list_status['invoice_recycle_list_no_sub'] == $invoice_recycle_list_info['STATUS'])
        {
            //修改删除按钮
            $form->CZBTN = '<a class = "delete_from_audit_list contrtable-link btn btn-danger btn-xs "'
                    . ' href="javascript:void(0);" title="移除退票单"><i class="glyphicon glyphicon-remove"></i></a>';
            
            //修改底部按钮
            $form->GABTN = '<a id = "sub_audit_list" href="javascript:;" class="btn btn-info btn-sm">提交审核单</a>';
            $form->GABTN .= '<a id = "export_recycle" href="javascript:;" class="btn btn-info btn-sm">导出退票审核单</a>';
        }
        //已提交审核状态
        else if(!empty($invoice_recycle_list_status) && 
                $invoice_recycle_list_status['invoice_recycle_list_sub'] == $invoice_recycle_list_info['STATUS'])
        {
            //撤销按钮
            $form->CZBTN = '<a class = "revoke_invoice_recycle contrtable-link btn btn-danger btn-xs"'
                    . ' href="javascript:void(0);">撤销退票</a>';
            $form->GABTN = '';
        }
        //退票终止或退票已完成状态
        else if(!empty($invoice_recycle_list_status) && 
                ($invoice_recycle_list_status['invoice_recycle_list_stop'] == $invoice_recycle_info['STATUS'] || 
                $invoice_recycle_list_status['invoice_recycle_list_completed'] == $invoice_recycle_info['STATUS']))
        {
            $form->GABTN = '';
            $form->CZBTN = '--';
        }
        //其他
        else
        {
            //无操作
            $form->CZBTN = '--';
            $form->GABTN = '';
        }
        //设置复选框不显示
        $form->SHOWCHECKBOX = 0;                
        
        //设置退票状态
        $form->setMyField('STATUS', 'LISTCHAR', array2listchar($invoice_recycle_detail_status));
        
        //设置发票状态
        // $form->setMyField('INVOICE_STATUS', 'LISTCHAR', array2listchar($invoice_recycle_detail_status));
         
        //退票申请人
        $form = $form->setMyField('APPLY_USER', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        
        $formHtml =  $form->getResult();
    	$this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->assign('invoice_recycle_list_id', $last_list_id);
        $this->assign('flow_url_current', U('InvoiceRecycle/opinionFlow',$this->_merge_url_param));
    	$this->display('invoice_recycle_audit_list');
    }
    
      /**
     +----------------------------------------------------------
     * 撤销退票申请
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function revoke_invoice_recycle()
    {
        $this->delete_from_details();
    }
    
    /**
     +----------------------------------------------------------
     * 从退票明细表中删除退票申请(撤销)
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function delete_from_details()
    {
        //删除的退票明细编号
        $invoice_recycle_details_id = intval($_POST['invoice_recycle_details_id']);
        if($invoice_recycle_details_id > 0)
        {   
            $invoice_recycle_model = D('InvoiceRecycle');
            $update_num = $invoice_recycle_model->del_invoice_recycle_detail_by_id($invoice_recycle_details_id);
            
            if($update_num > 0 )
            {
                $commission_model = D("CommissionBack");
                $invoice_recycle_model = D("InvoiceRecycle");
                
                //根据ID获取退票单会员编号
                $invoice_recycle_info = $invoice_recycle_model->get_invoice_recycle_detail_info_by_id($invoice_recycle_details_id,array("MID"));
                $mid = $invoice_recycle_info["MID"];
                
                $conf_where = "MID = $mid";
                $del_result = $commission_model->del_commission_info_by_conf($conf_where);
                $info['state']  = 1;
                $info['msg']  = '撤销退票成功';
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '撤销退票失败';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '参数错误';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
        exit;
    }
    
    /**
     +----------------------------------------------------------
     * 退票明细从退票单中删除（从退票单中移除退票会员）
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function delete_from_audit_list()
    {
        //删除的退票单编号
        $invoice_recycle_details_id = intval($_POST['invoice_recycle_details_id']);
        
        if($invoice_recycle_details_id > 0)
        {   
            $invoice_recycle_model = D('InvoiceRecycle');
            $update_num = $invoice_recycle_model->delete_details_from_audit_list($invoice_recycle_details_id);
            
            if($update_num > 0)
            {
                $info['state']  = 1;
                $info['msg']  = '删除成功';
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '删除失败';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '参数错误';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
        exit;
    }
    
    
    /**
     +----------------------------------------------------------
     * 进度列表
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function invoice_recycle_progress_list()
    {
        $invoice_recycle_model = D('InvoiceRecycle');
        
    	//当前用户编号
    	$uid = intval($_SESSION['uinfo']['uid']);
    	
    	//当前城市编号
    	$city_id = $this->channelid;
        
        //审核单编号
        $invoice_recycle_list_id = $_REQUEST["invoice_recycle_list_id"];
        
        //提示佣金索回
        $conf_where = "LIST_ID=$invoice_recycle_list_id AND IS_AGENCY_REWARD = 1";
        $field_arr = array("ID");
        $commission_info = $invoice_recycle_model->get_invoice_recycle_detail_info_by_cond($conf_where,$field_arr);
        //var_dump($commission_info);
        if(is_array($commission_info) && !empty($commission_info))
        {
            $mid_str = '';
            foreach($commission_info as $key=>$val)
            {
               $mid_str .= $val["ID"]; 
            }
        }
    	
    	Vendor('Oms.Form');
    	$form = new Form();
        
        $cond_where = " APPLY_USER = '".$uid."'";
    	$form = $form->initForminfo(184);

        $invoice_recycle_list_status = $invoice_recycle_model->get_conf_invoice_recycle_list_status_remark();
        $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($invoice_recycle_list_status), FALSE);
        
        //退票申请人
        $form = $form->setMyField('APPLY_USER', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                
        //流程处理
        if(intval($_REQUEST["flowId"]))
        {
            //有权限的可以进行处理，无权限隐藏按钮
            $is_edit = judgeFlowEdit(intval($_REQUEST["flowId"]),$_SESSION["uinfo"]["uid"]);
            //if(!$is_edit)
            //{
                //$form->CZBTN = "--";
            //}
            
            $cond_where = " ID = '".$_REQUEST['RECORDID']."'";
        }
        $form = $form->where($cond_where);
        $formHtml = $form->getResult();
    	$this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->assign('role',$_SESSION['uinfo']['role']);
        $this->assign('mid_str',$mid_str);
    	$this->display('invoice_recycle_progress_list');
    }
    
    
    /**
     +----------------------------------------------------------
     * 审批意见
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    function opinionFlow()
    {   
        //流程类型
       $workflow_type_info = M('erp_flowtype')->field('ID')->where("PINYIN = 'huiyuantuipiao'")->find();
       $type = !empty($workflow_type_info['ID']) ? intval($workflow_type_info['ID']) : 0;
        
        if($type == 0)
        {
            js_alert('工作流类型不存在');
            exit;
        }
        
        //工作流ID
        $flowId = !empty($_REQUEST['flowId']) ? 
                intval($_REQUEST['flowId']) : 0;
        
        //工作流关联业务ID
        $recordId = !empty($_REQUEST['RECORDID']) ? 
                intval($_REQUEST['RECORDID']) : 0;
        
        Vendor('Oms.workflow');
        $workflow = new workflow();

        if($flowId > 0)
        {
            $click  = $workflow->nextstep($flowId);
            $form = $workflow->createHtml($flowId);

            if($_REQUEST['savedata'])
            {
                if($_REQUEST['flowNext'])
                {
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str)
                    {
                        js_alert('办理成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('办理失败');
                    }
                }
                else if($_REQUEST['flowPass'])
                {
                    $str = $workflow->passWorkflow($_REQUEST);
                    
                    if($str)
                    {                          
                        js_alert('已同意成功', U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('已同意失败');
                    }
                }
                else if($_REQUEST['flowNot'])
                {

                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str)
                    {
                        $invoice_recycle_model = D('InvoiceRecycle');
                        //退票申请单终止
                        $list_update_num = $invoice_recycle_model->sub_invoice_recycle_list_to_stop($recordId);

                        //退票明细终止
                        $update_num = $invoice_recycle_model->sub_invoice_recycle_detail_to_stop($recordId);

                        //佣金索回---状态值变为4
                        //获取mid的字符串
                        $mids = M("erp_invoice_recycle_detail")->query("select mid from erp_invoice_recycle_detail where list_id = $recordId");
                        $mids = array2new($mids);
                        $mids_str = implode(",",$mids);
                        M("erp_commission_back")->query("update ERP_COMMISSION_BACK set status = 4 where  mid in ($mids_str)");

                         js_alert('否决成功',U('Flow/workStep'));

                    }
                    else
                    {
                        js_alert('否决失败');
                    }
                }
                else if($_REQUEST['flowStop'])
                {
					$auth = $workflow->flowPassRole($flowId);
                    
					if(!$auth)
                    {
						js_alert('未经过必经角色');exit;
					}
                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str)
                    {
                        //退票MODEL
                        $invoice_recycle_model = D('InvoiceRecycle');
                        
                       /* //退票申请单完成
                        $list_update_num = $invoice_recycle_model->sub_invoice_recycle_list_to_completed($recordId);

                        //退票明细退票成功
                        $update_num = $invoice_recycle_model->sub_invoice_recycle_detail_to_success($recordId);*/
                        
                        //修改明细退票明细中对应会员的发票状态为已回收

                        $invoice_recycle_status = $invoice_recycle_model->get_conf_invoice_recycle_status();

                        $invoice_recycle_success_status = !empty($invoice_recycle_status['invoice_recycle_success']) ?
                            $invoice_recycle_status['invoice_recycle_success']: '';

                        $cond_where = "LIST_ID =  '".$recordId."'";
                        $cond_where .= " AND STATUS = '".$invoice_recycle_success_status."'";

                        $mid = $invoice_recycle_model->get_invoice_recycle_detail_info_by_cond($cond_where,array("MID"));

                        foreach($mid as $key=>$val)
                        {
                            $mids[] = $val["MID"];
                        }
                        $member_model = D("Member");
                        $invoice_status_arr = $member_model->get_conf_invoice_status();//会员发票状态数组
                        $member_up_num = $member_model->update_info_by_id($mids,array("INVOICE_STATUS"=>$invoice_status_arr["callback"]));
                        js_alert('备案成功',U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('备案失败');
                    }
                }
				exit;
            }
        }
        else
        {
            $flow_type_pinyin = "huiyuantuipiao";
            $auth = $workflow->start_authority($flow_type_pinyin);
           if(!$auth)
           {
               $this->error('暂无权限');
           }
            $form = $workflow->createHtml();

            if($_REQUEST['savedata'])
            {   
                $invoice_recycle_list_id = !empty($_GET['invoice_recycle_list_id']) ? intval($_GET['invoice_recycle_list_id']) : 0; 
                $flow_data['type'] = $flow_type_pinyin; 
                $flow_data['CASEID'] = '';
                $flow_data['RECORDID'] = $invoice_recycle_list_id;
                $flow_data['INFO'] = strip_tags($_POST['INFO']);
                $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                $flow_data['FILES'] = $_POST['FILES'];
                $flow_data['ISMALL'] =  intval($_POST['ISMALL']);
                $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']);
                
                //创建退票工作流
                $str = $workflow->createworkflow($flow_data);
                if($str)
                {
                    js_alert('退票申请提交成功', U('InvoiceRecycle/opinionFlow', $this->_merge_url_param));
                }
                else
                {
                    js_alert('退票申请提交失败', U('InvoiceRecycle/opinionFlow', $this->_merge_url_param));
                }
                exit;
            }
        }
        
        $this->assign('form', $form);
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->display('opinionFlow');
    }

    public function export_recycle(){
        $filter_sql = isset($_GET['Filter_Sql'])?trim($_GET['Filter_Sql']):'';

        Vendor('phpExcel.PHPExcel');
        $Exceltitle = '会员退票';//
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(25);//默认行宽
        $objActSheet->getDefaultColumnDimension()->setWidth(12);//默认列宽
        $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'宋体'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//自动换行
        $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objActSheet->getRowDimension('1')->setRowHeight(40);
        $objActSheet->getRowDimension('2')->setRowHeight(26);

        $objActSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $objActSheet->getStyle('A2:K2')->getFont()->setBold(true);

        $styleArray = array(
            'borders' => array (
                'allborders' => array (
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array ('argb' => 'FF000000'),//设置border颜色
                ),
            ),
        );

        $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'会员退票'));
        $objActSheet->setCellValue('A2', iconv("gbk//ignore","utf-8//ignore",'编号'));
        $objActSheet->setCellValue('B2', iconv("gbk//ignore","utf-8//ignore",'合同编号'));
        $objActSheet->setCellValue('C2', iconv("gbk//ignore","utf-8//ignore",'项目名称'));
        $objActSheet->setCellValue('D2', iconv("gbk//ignore","utf-8//ignore",'客户姓名'));
        $objActSheet->setCellValue('E2', iconv("gbk//ignore","utf-8//ignore",'手机号码'));
        $objActSheet->setCellValue('F2', iconv("gbk//ignore","utf-8//ignore",'退票申请人'));
        $objActSheet->setCellValue('G2', iconv("gbk//ignore","utf-8//ignore",'收据编号'));
        $objActSheet->setCellValue('H2', iconv("gbk//ignore","utf-8//ignore",'申请日期'));
        $objActSheet->setCellValue('I2', iconv("gbk//ignore","utf-8//ignore",'发票编号'));
        $objActSheet->setCellValue('J2', iconv("gbk//ignore","utf-8//ignore",'发票状态'));
        $objActSheet->setCellValue('K2', iconv("gbk//ignore","utf-8//ignore",'退票状态'));
        $objActSheet->mergeCells('A1:K1');
        $member_id = $_REQUEST["member_id"];
        $list_id = $_REQUEST["list_id"];
        if($member_id) {
            $sql = "select * from (select a.ID,a.APPLY_USER,a.APPLY_TIME,a.STATUS,a.CITY_ID,a.LIST_ID,
            b.REALNAME,b.MOBILENO,b.INVOICE_NO,b.INVOICE_STATUS,b.PRJ_ID,b.RECEIPTNO as RECEIPT_NO, c.CONTRACT,c.PROJECTNAME
            from erp_invoice_recycle_detail a
            left join erp_cardmember b on a.mid=b.id
            left join erp_project c on b.prj_id=c.id) WHERE STATUS != 6 " . $filter_sql . ' AND CITY_ID = ' . $this->channelid . " AND ID IN ($member_id)  order by ID DESC";
        }
        else
            if($list_id) {
            $sql = "select * from (select a.ID,a.APPLY_USER,a.APPLY_TIME,a.STATUS,a.CITY_ID,a.LIST_ID,
            b.REALNAME,b.MOBILENO,b.INVOICE_NO,b.INVOICE_STATUS,b.PRJ_ID,b.RECEIPTNO as RECEIPT_NO, c.CONTRACT,c.PROJECTNAME
            from erp_invoice_recycle_detail a
            left join erp_cardmember b on a.mid=b.id
            left join erp_project c on b.prj_id=c.id ) WHERE STATUS != 6 " . $filter_sql . ' AND CITY_ID = ' . $this->channelid . " AND LIST_ID IN ($list_id)  order by ID DESC";
        }else{
                $sql = "select * from (select a.ID,a.APPLY_USER,a.APPLY_TIME,a.STATUS,a.CITY_ID,a.LIST_ID,
            b.REALNAME,b.MOBILENO,b.INVOICE_NO,b.INVOICE_STATUS,b.PRJ_ID,b.RECEIPTNO as RECEIPT_NO, c.CONTRACT,c.PROJECTNAME
            from erp_invoice_recycle_detail a
            left join erp_cardmember b on a.mid=b.id
            left join erp_project c on b.prj_id=c.id ) WHERE STATUS != 6 " . $filter_sql . ' AND CITY_ID = ' . $this->channelid . ' order by ID DESC';

            }
        $res = $this->model->query($sql);
        //var_dump($res);DIE;
        if(is_array($res)){
            $i = 3;
            foreach($res as $k => $r){

                $objActSheet->setCellValue('A'.$i, $r['ID']);
                $objActSheet->setCellValue('B'.$i, $r['CONTRACT']);
                $projectname = str_replace(array("/","、",",","，")," ",$r['PROJECTNAME']);
                $projectname = iconv("gbk//ignore","utf-8//ignore",$projectname);
                $objActSheet->setCellValue('C'.$i, $projectname);
                $realname = str_replace(array("/","、",",","，")," ",$r['REALNAME']);
                $realname = iconv("gbk//ignore","utf-8//ignore",$realname);
                $objActSheet->setCellValue('D'.$i, $realname);
                $objActSheet->setCellValue('E'.$i, $r['MOBILENO']);
                $applyUserArr = D()->query("select name from erp_users where id =".$r['APPLY_USER']);
                $applyUser = $applyUserArr['0']['NAME'];
                $applyUser = iconv("gbk//ignore","utf-8//ignore",$applyUser);
                $objActSheet->setCellValue('F'.$i, $applyUser);
                $receiptno = str_replace(","," ",$r['RECEIPT_NO']);
                $objActSheet->setCellValueExplicit('G'.$i, iconv("gbk//ignore","utf-8//ignore",$receiptno), PHPExcel_Cell_DataType::TYPE_STRING);
                $objActSheet->setCellValue('H'.$i,oracle_date_format($r['APPLY_TIME']));
                $objActSheet->setCellValueExplicit('I'.$i, iconv("gbk//ignore","utf-8//ignore",$r['INVOICE_NO']), PHPExcel_Cell_DataType::TYPE_STRING);
                $invoice_recycle_model = D("InvoiceRecycle");
                $invoice_recycle_status = $invoice_recycle_model->get_conf_invoice_recycle_status_remark();
                $status = $invoice_recycle_status[$r['STATUS']];
                $status = iconv("gbk//ignore","utf-8//ignore",$status);
                $objActSheet->setCellValue('K'.$i, $status);
                $member_model = D("Member");
                $invoice_status = $member_model->get_conf_invoice_status_remark();
                foreach($invoice_status as $invoice){
                    $invoiceStatus = $invoice[$r['INVOICE_STATUS']];
                }
                $invoiceStatus = iconv("gbk//ignore","utf-8//ignore",$invoiceStatus);
                $objActSheet->setCellValue('J'.$i, $invoiceStatus);
                //$objActSheet->getRowDimension($i)->setRowHeight(-1);
                $objActSheet->getRowDimension($i)->setRowHeight(24);
                $i++;
                if($objActSheet->getRowDimension($i)->getRowHeight() > 0){
                    $objActSheet->getRowDimension($i)->setRowHeight($objActSheet->getRowDimension($i)->getRowHeight()+20);
                }
            }
        }
        $objActSheet->getStyle('A1:K'.($i-1))->applyFromArray($styleArray);
        ob_end_clean();
        ob_start();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=".$Exceltitle.date("YmdHis").".xls");
        header("Content-Transfer-Encoding:binary");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        userLog()->writeLog('', $_SERVER["REQUEST_URI"], '退票:导出退票:成功', serialize($_FILES['upfile']));

    }
}
