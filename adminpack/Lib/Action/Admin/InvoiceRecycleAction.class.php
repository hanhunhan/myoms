<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * ��Ա��Ʊ������
 *
 * @author xuyemei
 */
class InvoiceRecycleAction extends ExtendAction{
    /**
     * ��Ա��Ʊ���������˵�Ȩ��
     */
    const ADD_TO_INVOICE_RECYCLE_AUDIT_LIST = 303;

    /**
     * ��Ա��Ʊ�����ύ��˵�Ȩ��
     */
    const VIEW_INVOICE_RECYCLE_AUDIT_LIST = 308;

    private $model;
    
    private $_merge_url_param = array();
    
    public function __construct() {
        $this->model = new Model();
        parent::__construct();
        // Ȩ��ӳ���
        $this->authorityMap = array(
            'add_to_invoice_recycle_audit_list' => self::ADD_TO_INVOICE_RECYCLE_AUDIT_LIST,
            'view_invoice_recycle_audit_list' => self::VIEW_INVOICE_RECYCLE_AUDIT_LIST
        );
        //TAB URL����
        !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : '';
        !empty($_GET['invoice_recycle_list_id']) ? $this->_merge_url_param['invoice_recycle_list_id'] = $_GET['invoice_recycle_list_id'] : '';
        !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = $_GET['flowId'] : '';
		!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;
    }
    
    /**
     * ������Ʊ,�����Ʊ��ϸ��¼
     * 
     */
    public function apply_invoice_recycle(){
        $invoice_recycle_model = D("InvoiceRecycle");
        //��Ա��Ʊ����״̬����
        $conf_invoice_recycle_status  = $invoice_recycle_model->get_conf_invoice_recycle_status();
        
        //��ǰ���б��
    	$city_id = intval($this->channelid);
        
        $member_model = D("Member");
        $member_ids = array();
        $need_agency_award = array();//��Ҫ׷���н�Ӷ��Ļ�ԱID
        $member_ids = $_POST["memberid"];
        $member_id_str = implode(",", $member_ids);
        $cond_where = "MID IN($member_id_str) AND STATUS NOT IN (".$conf_invoice_recycle_status["invoice_recycle_delete"].",". $conf_invoice_recycle_status["invoice_recycle_stop"] .")";
        $is_exist = $invoice_recycle_model->get_invoice_recycle_detail_info_by_cond($cond_where,array("ID"));
        if( $is_exist )
        {
            $result["status"] = 0;
            $result["msg"] = "��Ʊ�������ʧ�ܣ�����ѡ��Ա�а������Ѿ��������Ʊ�Ļ�Ա�������ظ����룡";
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }
        
        //������Ʊ�������û����뵽��Ʊ������
        $invoice_status = $member_model ->get_conf_invoice_status();
               
        foreach ($member_ids as $key=>$val)
        {
            $member_info = $member_model->get_info_by_id($val,array("AGENCY_REWARD","AGENCY_DEAL_REWARD","PROPERTY_DEAL_REWARD","INVOICE_STATUS"));
            
            //��Ʊ״̬Ϊ�ѿ�δ�������Ļ�Ա�ſ���������Ʊ
            if($member_info["INVOICE_STATUS"] == $invoice_status["has_taken"] ||
                $member_info["INVOICE_STATUS"] == $invoice_status["invoiced"])
            {                
                if($member_info["AGENCY_REWARD"] || $member_info["AGENCY_DEAL_REWARD"] || $member_info["PROPERTY_DEAL_REWARD"])
                {
                    //$cond_where = "BUSINESS_ID = ".$val." AND TYPE IN(3,4,5,6)";
                    //$search_arr = array("LIST_ID");
                    //���ݵ�ǰ��ѡ��Ļ�Ա�Ƿ����Ѿ��ύ������Ӷ���ж��Ƿ���Ҫ��ʾ����Ӷ��
                    $sql = "SELECT A.ID,A.STATUS FROM ERP_REIMBURSEMENT_LIST A "
                        . "LEFT JOIN ERP_REIMBURSEMENT_DETAIL B ON A.ID=B.LIST_ID "
                        . "LEFT JOIN ERP_CARDMEMBER C ON C.ID=B.BUSINESS_ID "
                        . "WHERE A.TYPE IN(3,4,5,6) AND B.BUSINESS_ID = ".$val." AND A.STATUS IN(1,2) AND B.STATUS != 4";

                    $reim_list_info = $this->model->query($sql);
                    if($reim_list_info)
                    {
                        //��Ҫ׷��Ӷ��
                        $data["IS_AGENCY_REWARD"] = 1;

                        //�н�Ӷ���Ƿ���׷��
                        $data["AGENCY_REWARD_STATUS"] = 0;

                        $need_agency_award[]= $val;
                    }
                               
                }
                else
                {

                    $data["IS_AGENCY_REWARD"] = 0;

                    $data["AGENCY_REWARD_STATUS"] = "";
                }

                //��Ա���
                $data["MID"] = $val;

                //��Ʊ������ID
                $data["APPLY_USER"] = $_SESSION["uinfo"]["uid"];

                //��Ʊ����ʱ��
                $data["APPLY_TIME"] = date("Y-m-d H:i:s");   

                //��Ʊ����״̬
                $data["STATUS"] = $conf_invoice_recycle_status["invoice_recycle_no_sub"]; 

                //����
                $data["CITY_ID"] = $city_id;
                //�����Ʊ�����¼
                $this->model->startTrans();
                $insertid[] = $invoice_recycle_model->add_invoice_recycle_details($data);

                } 
            }
            
            if(is_array($insertid) && !empty($insertid)){
                $result["status"] = 1;
                if(is_array($need_agency_award) && !empty($need_agency_award)){
                    //���Ӷ�����ػ�Ա��¼
                    $commission_model = D("CommissionBack");
                    $commission_status = $commission_model->get_conf_commission_status();
                    foreach($need_agency_award as $k=>$v)
                    {
                        $commission_info["MID"] = $v;
                        $commission_info["STATUS"] = $commission_status["no_back"];
                        //Ӷ������
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
                                //�н�Ӷ������
                                case 3:
                                    $commission_info["TYPE"] = 1; 
                                    $commission_info["REIM_LIST_ID"] = $reim_type_val["ID"];
                                    $commission_insert_id = $commission_model->add_commission_info($commission_info);
                                  break;
                                //�н�ɽ�������
                                case 4:
                                    $commission_info["TYPE"] = 2; 
                                    $commission_info["REIM_LIST_ID"] = $reim_type_val["ID"];
                                    $commission_insert_id = $commission_model->add_commission_info($commission_info);
                                    break;
                                //��ҵ���ʳɽ�������
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
                                $result["msg"] = "��Ʊ�������ʧ�ܣ������³���";       
                                $result["msg"] = g2u($result["msg"]);
                                echo json_encode($result);
                                exit;
                            }
                        }
                        
                    }
                    $this->model->commit();
                    $need_agency_award_str = implode(",", $need_agency_award);
                    $result["msg"] = "��Ʊ������ӳɹ�,��Ա���Ϊ".$need_agency_award_str."�Ļ�Ա"
                        . "���ύ�н�Ӷ�𣬻��н�ɽ���������ҵ���ʳɽ����ı������룬��Ҫ���أ�";
                }
                else
                {
                    $this->model->commit();
                    $result["msg"] = "��Ʊ������ӳɹ�!";
                }            
            }
            else
            {
                $result["status"] = 0;
                $result["msg"] = "��Ʊ�������ʧ�ܣ���Ʊ״̬Ϊ�ѿ�δ�������Ļ�Ա�ſ���������Ʊ��"
                    . "��ѡ��Ļ�Ա��û�з�����Ʊ�������û���";
            }
           
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
        
    }
    
    /**
     +----------------------------------------------------------
     * ������Ʊ�����б�
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
            $Exceltitle = '��Ա��Ʊ';//
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->setActiveSheetIndex(0);
            $objActSheet = $objPHPExcel->getActiveSheet();
            $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
            $objActSheet->getDefaultRowDimension()->setRowHeight(25);//Ĭ���п�
            $objActSheet->getDefaultColumnDimension()->setWidth(12);//Ĭ���п�
            $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'����'));
            $objActSheet->getDefaultStyle()->getFont()->setSize(10);
            $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//�Զ�����
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
                        'color' => array ('argb' => 'FF000000'),//����border��ɫ
                    ),
                ),
            );

            $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'��Ա��Ʊ'));
            $objActSheet->setCellValue('A2', iconv("gbk//ignore","utf-8//ignore",'���'));
            $objActSheet->setCellValue('B2', iconv("gbk//ignore","utf-8//ignore",'��ͬ���'));
            $objActSheet->setCellValue('C2', iconv("gbk//ignore","utf-8//ignore",'��Ŀ����'));
            $objActSheet->setCellValue('D2', iconv("gbk//ignore","utf-8//ignore",'�ͻ�����'));
            $objActSheet->setCellValue('E2', iconv("gbk//ignore","utf-8//ignore",'�ֻ�����'));
            $objActSheet->setCellValue('F2', iconv("gbk//ignore","utf-8//ignore",'��Ʊ������'));
            $objActSheet->setCellValue('G2', iconv("gbk//ignore","utf-8//ignore",'��Ʊ���'));
            $objActSheet->setCellValue('H2', iconv("gbk//ignore","utf-8//ignore",'�վݱ��'));
            $objActSheet->setCellValue('I2', iconv("gbk//ignore","utf-8//ignore",'��������'));
            $objActSheet->setCellValue('J2', iconv("gbk//ignore","utf-8//ignore",'��Ʊ״̬'));
            $objActSheet->setCellValue('K2', iconv("gbk//ignore","utf-8//ignore",'��Ʊ״̬'));
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
                    $projectname = str_replace(array("/","��",",","��")," ",$r['PROJECTNAME']);
                    $projectname = iconv("gbk//ignore","utf-8//ignore",$projectname);
                    $objActSheet->setCellValue('C'.$i, $projectname);
                    $realname = str_replace(array("/","��",",","��")," ",$r['REALNAME']);
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
            userLog()->writeLog('', $_SERVER["REQUEST_URI"], '��Ʊ:������Ʊ:�ɹ�', serialize($_FILES['upfile']));
            exit;
        }
        //��ǰ����
        $faction = !empty($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        
    	//��ǰ�û����
    	$uid = intval($_SESSION['uinfo']['uid']);
    	
    	//��ǰ���б��
    	$city_id = intval($this->channelid);
    	
    	//��ƱMODEL
    	$invoice_recycle_model = D("InvoiceRecycle");
    	
    	Vendor('Oms.Form');
    	$form = new Form();
        
        $conf_invoice_recycle_status = $invoice_recycle_model->get_conf_invoice_recycle_status();
        $invoice_recycle_no_sub = isset($conf_invoice_recycle_status['invoice_recycle_no_sub']) ? 
                            $conf_invoice_recycle_status['invoice_recycle_no_sub'] : '';

         $cond_where = "CITY_ID = " . $city_id;
        //�������flowid��
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
        //�ɹ�����������ֻչʾ��������صļ�¼
        else
        {
            $invoice_recyle_id = $_REQUEST["RECORDID"];
            $cond_where .= " AND LIST_ID = '".$invoice_recyle_id."'";
        }
        
        $form->where($cond_where);        
        
        $form->setMyField("INVOICE_TIME", "GRIDVISIBLE", "0")
            ->setMyField("INVOICE_MONEY", "GRIDVISIBLE", "0")
            ->setMyField("REALNAME", "FIELDMEANS", "�ͻ�����")
            ->setMyField("MOBILENO", "FIELDMEANS", "�ֻ�����")
            ->setMyField("APPLY_USER", "FIELDMEANS", "��Ʊ������")
            ->setMyField("INVOICE_NO", "GRIDVISIBLE", -1)
            ->setMyField("INVOICE_NO", "FORMVISIBLE", -1)
            ->setMyField("RECEIPT_NO", "GRIDVISIBLE", -1)
            ->setMyField("RECEIPT_NO", "FORMVISIBLE", -1)
            ->setMyField("APPLY_TIME", "FIELDMEANS", "��������");
        
        //������Ʊ״̬
        $invoice_recycle_status = $invoice_recycle_model->get_conf_invoice_recycle_status_remark();
        //$form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($invoice_recycle_status), FALSE);
        //���÷�Ʊ״̬
        $member_model = D("Member");
        $invoice_ststus = $member_model->get_conf_invoice_status_remark();
       
        $form = $form->setMyField('INVOICE_STATUS', 'LISTCHAR', array2listchar($invoice_ststus["INVOICE_STATUS"]), FALSE);
        
        //��Ʊ������
        $form = $form->setMyField('APPLY_USER', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS');
       
        //���̴���
        if(intval($_REQUEST["flowId"]))
        {
            //��Ȩ�޵Ŀ��Խ��д�����Ȩ�����ذ�ť
            $is_edit = judgeFlowEdit(intval($_REQUEST["flowId"]),$_SESSION["uinfo"]["uid"]);
            if(!$is_edit)
            {
                $form->GABTN = "";
                $form->DELABLE = "0";
            }
            else
            {   
                $form->CZBTN = array(
                        '%STATUS% == 1'=>'<a class="cancel_from_details btn btn-danger btn-xs" href="javascript:;" title="ɾ��"><i class="glyphicon glyphicon-trash"></i></a>',
                        '%STATUS% > 1' => '<font style="color:#333">����</font>');                
            }
        }
        //ҵ����
        else
        {
            //δ�ύ����Ʊ�������ɾ���������޷�ɾ��
            $form->CZBTN = array(
                        '%STATUS% == 1'=>'<a class="cancel_from_details btn btn-danger btn-xs" href="javascript:;" title="ɾ��"><i class="glyphicon glyphicon-trash"></i></a>',
                        '%STATUS% > 1' => '<font style="color:#333">����</font>');
        }
        $form->GABTN .= "<a id='export_recycle' href='javascript:void(0);' class='btn btn-info btn-sm'>������Ʊ</a>";

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��
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
     * ��ӵ���Ʊ��˵�
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function add_to_audit_list()
    {
        $invoice_recycle_model = D("InvoiceRecycle");
        
        //��ǰ�û����
        $uid = intval($_SESSION['uinfo']['uid']);
        
        //��ǰ���б��
        $city_id = intval($this->channelid);
        
        //���������˵�����Ʊ������
        $invoice_recycle_id = $_POST['invoice_recycle_id'];
        
        if ($uid > 0 && $city_id > 0 && !empty($invoice_recycle_id) && !empty($_POST) )
        { 
            //��ѯ��ǰ�û�����һ����Ʊ���뵥��Ϣ
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
                    $info['msg']  = '������˵��ɹ�';
               }
               else
               {
                   $this->model->rollback();
                    $info['state']  = 0;
                    $info['msg']  = '������˵�ʧ��';
               }
            }
            else
            {
                $this->model->rollback();
                $info['state']  = 0;
                $info['msg']  = '������˵�����ʧ��';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '��������';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
        exit;
    }
    
    /**
     +----------------------------------------------------------
     * (�鿴)��Ʊ��˵�
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function invoice_recycle_audit_list()
    {
    	//��ǰ�û����
    	$uid = intval($_SESSION['uinfo']['uid']);
    	
    	//��ǰ���б��
    	$city_id = intval($this->channelid);
        
        //��Ʊ���뵥���
        $invoice_recycle_list_id = !empty($_GET['invoice_recycle_list_id']) ? intval($_GET['invoice_recycle_list_id']) : 0;
    	
    	Vendor('Oms.Form');
    	$form = new Form();
        
        //��ƱMODEL
        $invoice_recycle_model = D('InvoiceRecycle');
        $invoice_recycle_list_status = $invoice_recycle_model->get_conf_invoice_recycle_list_status();
        $invoice_recycle_detail_status = $invoice_recycle_model->get_conf_invoice_recycle_status_remark();
            
    	$last_list_id = 0;
        if( $invoice_recycle_list_id > 0 )
        {
            $last_list_id = $invoice_recycle_list_id;//�鿴����Ʊ���µ���Ʊ��Ϣ
        }   
        else if ($uid > 0 && $city_id > 0)
        { 
            //��ѯ��ǰ�û�����һ����Ʊ���뵥��Ϣ
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
        
        //��ǰ��Ʊ���뵥��Ϣ
        $invoice_recycle_list_info = $invoice_recycle_model->get_invoice_recycle_list_by_id($last_list_id, array('ID', 'STATUS'));
        
        //δ�ύ���״̬
        if(!empty($invoice_recycle_list_status) && 
                $invoice_recycle_list_status['invoice_recycle_list_no_sub'] == $invoice_recycle_list_info['STATUS'])
        {
            //�޸�ɾ����ť
            $form->CZBTN = '<a class = "delete_from_audit_list contrtable-link btn btn-danger btn-xs "'
                    . ' href="javascript:void(0);" title="�Ƴ���Ʊ��"><i class="glyphicon glyphicon-remove"></i></a>';
            
            //�޸ĵײ���ť
            $form->GABTN = '<a id = "sub_audit_list" href="javascript:;" class="btn btn-info btn-sm">�ύ��˵�</a>';
            $form->GABTN .= '<a id = "export_recycle" href="javascript:;" class="btn btn-info btn-sm">������Ʊ��˵�</a>';
        }
        //���ύ���״̬
        else if(!empty($invoice_recycle_list_status) && 
                $invoice_recycle_list_status['invoice_recycle_list_sub'] == $invoice_recycle_list_info['STATUS'])
        {
            //������ť
            $form->CZBTN = '<a class = "revoke_invoice_recycle contrtable-link btn btn-danger btn-xs"'
                    . ' href="javascript:void(0);">������Ʊ</a>';
            $form->GABTN = '';
        }
        //��Ʊ��ֹ����Ʊ�����״̬
        else if(!empty($invoice_recycle_list_status) && 
                ($invoice_recycle_list_status['invoice_recycle_list_stop'] == $invoice_recycle_info['STATUS'] || 
                $invoice_recycle_list_status['invoice_recycle_list_completed'] == $invoice_recycle_info['STATUS']))
        {
            $form->GABTN = '';
            $form->CZBTN = '--';
        }
        //����
        else
        {
            //�޲���
            $form->CZBTN = '--';
            $form->GABTN = '';
        }
        //���ø�ѡ����ʾ
        $form->SHOWCHECKBOX = 0;                
        
        //������Ʊ״̬
        $form->setMyField('STATUS', 'LISTCHAR', array2listchar($invoice_recycle_detail_status));
        
        //���÷�Ʊ״̬
        // $form->setMyField('INVOICE_STATUS', 'LISTCHAR', array2listchar($invoice_recycle_detail_status));
         
        //��Ʊ������
        $form = $form->setMyField('APPLY_USER', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        
        $formHtml =  $form->getResult();
    	$this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->assign('invoice_recycle_list_id', $last_list_id);
        $this->assign('flow_url_current', U('InvoiceRecycle/opinionFlow',$this->_merge_url_param));
    	$this->display('invoice_recycle_audit_list');
    }
    
      /**
     +----------------------------------------------------------
     * ������Ʊ����
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
     * ����Ʊ��ϸ����ɾ����Ʊ����(����)
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function delete_from_details()
    {
        //ɾ������Ʊ��ϸ���
        $invoice_recycle_details_id = intval($_POST['invoice_recycle_details_id']);
        if($invoice_recycle_details_id > 0)
        {   
            $invoice_recycle_model = D('InvoiceRecycle');
            $update_num = $invoice_recycle_model->del_invoice_recycle_detail_by_id($invoice_recycle_details_id);
            
            if($update_num > 0 )
            {
                $commission_model = D("CommissionBack");
                $invoice_recycle_model = D("InvoiceRecycle");
                
                //����ID��ȡ��Ʊ����Ա���
                $invoice_recycle_info = $invoice_recycle_model->get_invoice_recycle_detail_info_by_id($invoice_recycle_details_id,array("MID"));
                $mid = $invoice_recycle_info["MID"];
                
                $conf_where = "MID = $mid";
                $del_result = $commission_model->del_commission_info_by_conf($conf_where);
                $info['state']  = 1;
                $info['msg']  = '������Ʊ�ɹ�';
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = '������Ʊʧ��';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '��������';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
        exit;
    }
    
    /**
     +----------------------------------------------------------
     * ��Ʊ��ϸ����Ʊ����ɾ��������Ʊ�����Ƴ���Ʊ��Ա��
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function delete_from_audit_list()
    {
        //ɾ������Ʊ�����
        $invoice_recycle_details_id = intval($_POST['invoice_recycle_details_id']);
        
        if($invoice_recycle_details_id > 0)
        {   
            $invoice_recycle_model = D('InvoiceRecycle');
            $update_num = $invoice_recycle_model->delete_details_from_audit_list($invoice_recycle_details_id);
            
            if($update_num > 0)
            {
                $info['state']  = 1;
                $info['msg']  = 'ɾ���ɹ�';
            }
            else
            {
                $info['state']  = 0;
                $info['msg']  = 'ɾ��ʧ��';
            }
        }
        else
        {
            $info['state']  = 0;
            $info['msg']  = '��������';
        }
        
        $info['msg'] = g2u($info['msg']);
        echo json_encode($info);
        exit;
    }
    
    
    /**
     +----------------------------------------------------------
     * �����б�
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    public function invoice_recycle_progress_list()
    {
        $invoice_recycle_model = D('InvoiceRecycle');
        
    	//��ǰ�û����
    	$uid = intval($_SESSION['uinfo']['uid']);
    	
    	//��ǰ���б��
    	$city_id = $this->channelid;
        
        //��˵����
        $invoice_recycle_list_id = $_REQUEST["invoice_recycle_list_id"];
        
        //��ʾӶ������
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
        
        //��Ʊ������
        $form = $form->setMyField('APPLY_USER', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                
        //���̴���
        if(intval($_REQUEST["flowId"]))
        {
            //��Ȩ�޵Ŀ��Խ��д�����Ȩ�����ذ�ť
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
     * �������
     +----------------------------------------------------------
     * @param none
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    function opinionFlow()
    {   
        //��������
       $workflow_type_info = M('erp_flowtype')->field('ID')->where("PINYIN = 'huiyuantuipiao'")->find();
       $type = !empty($workflow_type_info['ID']) ? intval($workflow_type_info['ID']) : 0;
        
        if($type == 0)
        {
            js_alert('���������Ͳ�����');
            exit;
        }
        
        //������ID
        $flowId = !empty($_REQUEST['flowId']) ? 
                intval($_REQUEST['flowId']) : 0;
        
        //����������ҵ��ID
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
                        js_alert('��ͬ��ɹ�', U('Flow/workStep'));
                    }
                    else
                    {
                        js_alert('��ͬ��ʧ��');
                    }
                }
                else if($_REQUEST['flowNot'])
                {

                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str)
                    {
                        $invoice_recycle_model = D('InvoiceRecycle');
                        //��Ʊ���뵥��ֹ
                        $list_update_num = $invoice_recycle_model->sub_invoice_recycle_list_to_stop($recordId);

                        //��Ʊ��ϸ��ֹ
                        $update_num = $invoice_recycle_model->sub_invoice_recycle_detail_to_stop($recordId);

                        //Ӷ������---״ֵ̬��Ϊ4
                        //��ȡmid���ַ���
                        $mids = M("erp_invoice_recycle_detail")->query("select mid from erp_invoice_recycle_detail where list_id = $recordId");
                        $mids = array2new($mids);
                        $mids_str = implode(",",$mids);
                        M("erp_commission_back")->query("update ERP_COMMISSION_BACK set status = 4 where  mid in ($mids_str)");

                         js_alert('����ɹ�',U('Flow/workStep'));

                    }
                    else
                    {
                        js_alert('���ʧ��');
                    }
                }
                else if($_REQUEST['flowStop'])
                {
					$auth = $workflow->flowPassRole($flowId);
                    
					if(!$auth)
                    {
						js_alert('δ�����ؾ���ɫ');exit;
					}
                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str)
                    {
                        //��ƱMODEL
                        $invoice_recycle_model = D('InvoiceRecycle');
                        
                       /* //��Ʊ���뵥���
                        $list_update_num = $invoice_recycle_model->sub_invoice_recycle_list_to_completed($recordId);

                        //��Ʊ��ϸ��Ʊ�ɹ�
                        $update_num = $invoice_recycle_model->sub_invoice_recycle_detail_to_success($recordId);*/
                        
                        //�޸���ϸ��Ʊ��ϸ�ж�Ӧ��Ա�ķ�Ʊ״̬Ϊ�ѻ���

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
                        $invoice_status_arr = $member_model->get_conf_invoice_status();//��Ա��Ʊ״̬����
                        $member_up_num = $member_model->update_info_by_id($mids,array("INVOICE_STATUS"=>$invoice_status_arr["callback"]));
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
            $flow_type_pinyin = "huiyuantuipiao";
            $auth = $workflow->start_authority($flow_type_pinyin);
           if(!$auth)
           {
               $this->error('����Ȩ��');
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
                
                //������Ʊ������
                $str = $workflow->createworkflow($flow_data);
                if($str)
                {
                    js_alert('��Ʊ�����ύ�ɹ�', U('InvoiceRecycle/opinionFlow', $this->_merge_url_param));
                }
                else
                {
                    js_alert('��Ʊ�����ύʧ��', U('InvoiceRecycle/opinionFlow', $this->_merge_url_param));
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
        $Exceltitle = '��Ա��Ʊ';//
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(25);//Ĭ���п�
        $objActSheet->getDefaultColumnDimension()->setWidth(12);//Ĭ���п�
        $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'����'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//�Զ�����
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
                    'color' => array ('argb' => 'FF000000'),//����border��ɫ
                ),
            ),
        );

        $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'��Ա��Ʊ'));
        $objActSheet->setCellValue('A2', iconv("gbk//ignore","utf-8//ignore",'���'));
        $objActSheet->setCellValue('B2', iconv("gbk//ignore","utf-8//ignore",'��ͬ���'));
        $objActSheet->setCellValue('C2', iconv("gbk//ignore","utf-8//ignore",'��Ŀ����'));
        $objActSheet->setCellValue('D2', iconv("gbk//ignore","utf-8//ignore",'�ͻ�����'));
        $objActSheet->setCellValue('E2', iconv("gbk//ignore","utf-8//ignore",'�ֻ�����'));
        $objActSheet->setCellValue('F2', iconv("gbk//ignore","utf-8//ignore",'��Ʊ������'));
        $objActSheet->setCellValue('G2', iconv("gbk//ignore","utf-8//ignore",'�վݱ��'));
        $objActSheet->setCellValue('H2', iconv("gbk//ignore","utf-8//ignore",'��������'));
        $objActSheet->setCellValue('I2', iconv("gbk//ignore","utf-8//ignore",'��Ʊ���'));
        $objActSheet->setCellValue('J2', iconv("gbk//ignore","utf-8//ignore",'��Ʊ״̬'));
        $objActSheet->setCellValue('K2', iconv("gbk//ignore","utf-8//ignore",'��Ʊ״̬'));
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
                $projectname = str_replace(array("/","��",",","��")," ",$r['PROJECTNAME']);
                $projectname = iconv("gbk//ignore","utf-8//ignore",$projectname);
                $objActSheet->setCellValue('C'.$i, $projectname);
                $realname = str_replace(array("/","��",",","��")," ",$r['REALNAME']);
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
        userLog()->writeLog('', $_SERVER["REQUEST_URI"], '��Ʊ:������Ʊ:�ɹ�', serialize($_FILES['upfile']));

    }
}
