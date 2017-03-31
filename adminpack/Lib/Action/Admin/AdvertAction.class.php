<?php
    class AdvertAction extends ExtendAction{
        /**
         * 硬广CASE
         */
        const YG_CASE_ALIAS = 'yg';

        /**
         * 活动CASE
         */
        const HD_CASE_ALIAS = 'hd';

        /**
         * 非我方收筹CASE
         */
        const FWFSC_CASE_ALIAS = 'fwfsc';

        /**
         * 非我方收筹的SCALETYPE
         */
        const FWFSC_SCALETYPE = 8;

        /**
         * 硬广的SCALETYPE
         */
        const YG_SCALETYPE = 3;

        /**
         * 单位：元
         */
        const UNIT_RMB_YUAN = '元';

        /**
         * 单位：%
         */
        const UNIT_PERCENT = '%';

        /**
         * 【申请开票】权限
         */
        const APPLYINVOICE = 743;

        /**
         * 申请换票权限
         */
        const CHANGEINVOICE = 770;

        /**
         * 申请退票权限
         */
        const REFUNDINVOICE = 772;


        /**
         * 同步合同系统权限
         */
        const SYN_CONTRACT_SYSTEM = 0;

        private $model;
        private $tab_num;
        private $_merge_url_param = array();

        private $contractOptions = array(
            '_check' => array(
                'default' => 737,
                self::HD_CASE_ALIAS => 549,
                self::YG_CASE_ALIAS => 524,
                self::FWFSC_CASE_ALIAS => 550
            ),
            '_add' => array(
                self::YG_CASE_ALIAS => 523
            )
        );

        private $invoiceOptions = array(
            '_add' => array(
                'default' => 738,
            ),
            '_check' => array(
                'default' => 739
            ),
            '_edit' => array(
                'default' => 740
            ),
            '_del' => array(
                'default' => 742
            )
        );

        //构造函数
		public function __construct() 
		{
            // 权限映射表
            $this->authorityMap = array(
                'applyInvoice' => self::APPLYINVOICE,
                'export_members' => 766,
                'changeInvoice' => self::CHANGEINVOICE,
                'refundInvoice' => self::REFUNDINVOICE,
                //变更合同置换属性隐藏按钮，硬广=》844，活动=》871，合同属性进入=》872
                'change_contract_displace' =>array(
                    'yg'=>844,
                    'hd'=>871,
                    'default'=>872,
                )
            );

            $this->model = new Model();
			parent::__construct();
            if($_GET['is_from'] == 1)
            {
                $this->tab_num = 11;
            }
            elseif($_GET['is_from'] == 2 && $_GET["CASE_TYPE"] == 'hd')
            {
                $this->tab_num = 12;
            }
            else if($_GET['is_from'] == 2 && $_GET["CASE_TYPE"] == 'xmxhd')
            {
                $this->tab_num = 17;
            }
            else if ($this->_get('is_from') == 2 && $this->_get('CASE_TYPE') == 'fwfsc') {
                $this->tab_num = 24;
            }
            elseif($_GET['is_from'] == 3)
            {
                $this->tab_num = 13;
            }
                        
            !empty($_GET['prjid']) ? $this->_merge_url_param['prjid'] = $_GET['prjid'] : 0;
            !empty($_GET['FLOWTYPE']) ? $this->_merge_url_param['FLOWTYPE'] = $_GET['FLOWTYPE'] : '';
            !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
            !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : '';
            if(!empty($_GET['TAB_NUMBER'])) 
            { 
                $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']);
            }
            else 
            {
                $this->_merge_url_param['TAB_NUMBER'] = $this->tab_num;

            }
            !empty($_GET['parentchooseid']) ? $this->_merge_url_param['contract_id'] = $_GET['parentchooseid'] :$_GET['contract_id']; 
            !empty($_GET['contract_type']) ? $this->_merge_url_param['contract_type'] = $_GET['contract_type'] : '';            
            !empty($_GET['invoiceId']) ? $this->_merge_url_param['invoiceId'] = $_GET['invoiceId'] : '';
            !empty($_GET['is_from']) ? $this->_merge_url_param['is_from'] = $_GET['is_from'] : '';
            !empty($_GET['scale_type']) ? $this->_merge_url_param['scale_type'] = $_GET['scale_type'] :
                ($_GET["CASE_TYPE"] ? $_GET["CASE_TYPE"] : "");
            !empty($_GET['CASE_TYPE']) ? $this->_merge_url_param['CASE_TYPE'] = $_GET['CASE_TYPE'] : '';
            !empty($_GET['activId']) ? $this->_merge_url_param['activId'] = $_GET['activId'] : '';
            !empty($_GET['flowId']) ? $this->_merge_url_param['flowId'] = $_GET['flowId'] : '';
			!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;
            
		}

        public function index(){
            if ($_REQUEST['CASE_TYPE'] == 'yg') {
                $this->_merge_url_param['tab_num'] = 11;
            } else if($_REQUEST['CASE_TYPE'] == 'fwfsc') {
                $this->_merge_url_param['tab_num'] = 24;
            } else if ($_REQUEST['CASE_TYPE'] == 'hd') {
                $this->_merge_url_param['tab_num'] = 12;
            } else if ($_REQUEST['CASE_TYPE'] == 'xmxhd') {
                $this->_merge_url_param['tab_num'] = 17;
            }else {
                $this->_merge_url_param['tab_num'] = 13;
                $this->_merge_url_param['is_from'] = 3;
            }

            $hasTabAuthority = $this->checkTabAuthority($this->_merge_url_param['tab_num']);
            if ($hasTabAuthority['result']) {
                $roleInfo = D('erp_role')->where("LOAN_ROLEID = {$hasTabAuthority['roleID']}")->find();
                $url = U("{$roleInfo['LOAN_ROLECONTROL']}/{$roleInfo['LOAN_ROLEACTION']}", $this->_merge_url_param);
                halt2('', $url);
                return;
            }
        }
           
        /**
        +----------------------------------------------------------
        *新增合同
        +----------------------------------------------------------
        * @param $file 要读取的文件
        +----------------------------------------------------------
        * @return $data 
        +----------------------------------------------------------
        */
        public function contract()
        {

            $act = isset($_REQUEST['act'])?trim($_REQUEST['act']):'';

            //同步合同系统
            if($act=='syc_contract')
            {
                $return = array(
                    'status'=>false,
                    'msg'=>'',
                    'data'=>null,
                );

                $contract = $_REQUEST['contract'];

                $contract_model = M("Erp_income_contract");

                $error_str = '';

                foreach($contract as $key=>$val){
                    $contract_ret = $contract_model->field("contract_no,city_py")
                        ->where("ID = $val")->find();

                    $contract_url = CONTRACT_API . "get_ct_info.php?city=" . $contract_ret['CITY_PY'] . "&contractnum=" . $contract_ret['CONTRACT_NO'];
                    $contract_data = curl_get_contents($contract_url);

                    $contract_data = unserialize($contract_data);

                    if(empty($contract_data)) {
                        $error_str .= "第" . ($key + 1) . "条合同，从合同系统中未取到数据(可能跟合同状态有关)!\n";
                        continue;
                    }

                    //合作单位
                    $data['COMPANY'] =  $contract_data['contunit'];
                    //开始时间
                    $data['START_TIME'] = date("Y-m-d",$contract_data['contbegintime']);
                    //结束时间
                    $data['END_TIME'] = date("Y-m-d",$contract_data['contendtime']);
                    //合同状态
                    $data['STATUS'] = $contract_data['step'];
                    //合同金额
                    $data['MONEY'] = $contract_data['contmoney'];
                    //合同类型
                    $data['CONTRACT_TYPE'] = $contract_data['type'];
                    //合同签约人
                    $data['SIGN_USER'] = $contract_data['addman'];
                    //已发布金额
                    $data['ISSUEAMOUNT'] = $contract_data['all_fb'];
                    //财务确认时间
                    if($contract_data['confirmtime'])
                        $data['CONF_TIME'] = date("Y-m-d H:i:s",$contract_data['confirmtime']);

                    $update = $contract_model->where("ID = $val")->save($data);

                    if(!$update){
                        $error_str .= "第" . ($key + 1) . "条合同，更新失败!\n";
                        continue;
                    }

                }

                if($error_str)
                {
                    $return['msg'] = g2u($error_str);
                }
                else
                {
                    $return['status'] = true;
                }

                die(@json_encode($return));

            }

            $prjId = $_REQUEST["prjid"] ? $_REQUEST["prjid"] : 0;

            $income_contract_model = D("Contract");
            $project_case_model = D('ProjectCase');
            $scale_type_conf = $project_case_model->get_conf_case_type();//业务类型数组 ds fx yg hd cp
            //var_dump($scale_type_conf);
            //var_dump($this->_merge_url_param['scale_type']);            
            $id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
            $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
            $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';

            $is_from = $_REQUEST["is_from"];//获取来源 1硬广 2活动 3合同菜单
            $activId = $_REQUEST["activId"] ? $_REQUEST["activId"] : 0;

            //由流程审批进入
            if ($_REQUEST["flowId"]) {
                $case_id = $_REQUEST["CASEID"];
                $case_info = $project_case_model->get_info_by_id($case_id, array("SCALETYPE"));
                $scale_type = $case_info[0]["SCALETYPE"];
                if ($scale_type == $scale_type_conf["yg"]) {
                    $is_from = 1;
                } else if ($scale_type == $scale_type_conf["hd"] || $scale_type == $scale_type_conf["ds"]) {
                    $is_from = 2;
                } else {
                    $is_from = 3;
                }

            } //从业务条口进入
            else {
                //从硬广进入
                if ($is_from == 1) {
                    $scale_type = $scale_type_conf["yg"];
                } else if ($is_from == 2) {
                    //从电商下活动执行进入
                    if ($_GET["CASE_TYPE"] == 'xmxhd' && $_GET["businessclass"] == 1) {
                        //$case_id = $_GET["CASEID"];
                        $scale_type = $scale_type_conf["ds"];
                    } //分销下活动执行进入
                    else if ($_GET["CASE_TYPE"] == 'xmxhd' && $_GET["businessclass"] == 2) {
                        $scale_type = $scale_type_conf["fx"];
                    } //硬广下活动执行进入
                    else if ($_GET["CASE_TYPE"] == 'xmxhd' && $_GET["businessclass"] == 3) {
                        $scale_type = $scale_type_conf["yg"];
                    } else if ($_GET['CASE_TYPE'] == 'fwfsc') {  // 从非我方收筹执行进入
                        $scale_type = $scale_type_conf['fwfsc'];
                    } //独立活动执行进入
                    else {
                        $scale_type = $scale_type_conf["hd"];
                    }

                }

                //判断项目权限
                if ($_REQUEST["is_from"] == 1 || $_REQUEST["is_from"] == 2) {
                    $this->project_auth($prjId, $scale_type, $this->_merge_url_param['flowId']);
                }

                //如果是项目下活动的执行，跳转到采购申请tab页
                if ($_GET["CASE_TYPE"] == 'xmxhd') {
                    $this->redirect("Purchase/purchase_manage", $this->_merge_url_param);
                    die;
                }
                //根据项目id和业务类型获取合同案例信息
                if ($scale_type == 3) {
                    $case_info = $project_case_model->get_info_by_pid($prjId, 'yg', array('ID'));
                } else if ($scale_type == 4) {
                    $case_info = $project_case_model->get_info_by_pid($prjId, 'hd', array('ID'));
                } else if ($scale_type == 1) {
                    $case_info = $project_case_model->get_info_by_pid($prjId, 'ds', array('ID'));
                } else if ($scale_type == 8) {
                    $case_info = $project_case_model->get_info_by_pid($prjId, 'fwfsc', array('ID'));
                }

                $case_id = $case_info[0]['ID'];

            }

            $cityid = $_SESSION["uinfo"]["city"];
            $sql = "select PY,NAME from ERP_CITY where ID=" . $cityid;
            $city_info = $this->model->query($sql);

            if ($is_from == 1 && $showForm == 3 && $faction == '')//点击 新增 按钮时判断业务类型 硬广不允许二次添加合同
            {
                $sql = "select ID from ERP_INCOME_CONTRACT where CASE_ID "
                    . "=(select ID from ERP_CASE where PROJECT_ID=" . $prjId . " and SCALETYPE = 3)";
                $res = $this->model->query($sql);
                if ($res) {//如果是硬广合同  则不允许再次添加合同
                    js_alert("该硬广案例已有合同，不允许再次添加", U("Advert/contract", $this->_merge_url_param));
                    exit;
                }
            }

            //添加合同信息到收益合同表（ERP_INCOME_CONTRACT）
            if ($showForm == 3 && $faction == 'saveFormData') {
                if (empty($case_info)) {
                    $result['status'] = 0;
                    $result['msg'] = g2u('添加失败，未查到项目硬广案例信息。');
                    echo json_encode($result);
                    exit;
                }

                $case_id = $case_info[0]['ID'];
                if ($_POST["CONTRACT_NO"]) $data["CONTRACT_NO"] = $_POST["CONTRACT_NO"];
                if ($_POST["COMPANY"]) $data["COMPANY"] = u2g(trim($_POST["COMPANY"]));
                $prjname = u2g(trim($_POST["PROJECTNAME"]));
                if (trim($_POST["START_TIME"])) $data["START_TIME"] = trim($_POST["START_TIME"]);
                if (trim($_POST["END_TIME"])) $data["END_TIME"] = trim($_POST["END_TIME"]);
                if (trim($_POST["PUB_TIME"]) != "") $data["PUB_TIME"] = trim($_POST["PUB_TIME"]);
                if (trim($_POST["CONF_TIME"]) != "") $data["CONF_TIME"] = trim($_POST["CONF_TIME"]);
                if (strip_tags(trim($_POST["CONF_USER"]))) $data["CONF_USER"] = strip_tags(trim($_POST["CONF_USER"]));
                if ($_POST["MONEY"]) $data["MONEY"] = intval($_POST["MONEY"]);
                if ($_POST["STATUS"]) $data["STATUS"] = trim($_POST["STATUS"]);
                if ($_POST["ADDMAN"]) $data["SIGN_USER"] = strip_tags($_POST["ADDMAN"]);
                if ($_POST["CONTRACT_TYPE"]) $data["CONTRACT_TYPE"] = strip_tags($_POST["CONTRACT_TYPE"]);
                $data["ADD_TIME"] = date("Y-m-d H:i:s", time());
                $data["CASE_ID"] = $case_id;
                $data["CITY_PY"] = $city_info[0]["PY"];//用户城市拼音
                $data["CITY_ID"] = $cityid;
                //var_dump($data);die;
                $this->model->startTrans();
				//if($data["MONEY"]>0){
					$insertid = $income_contract_model->add_contract_info($data);
				//}else{  
					//$result['status'] = 0;
					//$result['msg'] = g2u('合同金额为零，保存失败！');
					//echo json_encode($result);
                   // exit;
				//}

                //添加硬广合同成功 回写项目表中的合同编号、项目名称和合同单位,获取合同系统开票、回款
                if ($insertid) {
                    $contractnum = $_POST["CONTRACT_NO"];
                    $cityid = $_SESSION["uinfo"]["city"];
                    $sql = "select PY from ERP_CITY where ID=" . $cityid;
                    $citypy = $this->model->query($sql);
                    $citypy = strtolower($citypy[0]["PY"]);//用户城市拼音

                    $project_model = D("Project");
                    $update_arr["CONTRACT"] = $data["CONTRACT_NO"];
                    $update_arr["PROJECTNAME"] = $prjname;
                    $update_arr["COPANY"] = $data["COMPANY"];
                    $up_num = $project_model->update_prj_info_by_id($prjId, $update_arr);

                    //根据合同号和城市拼音，获取合同的回款记录,并将数据同步到经管系统
                    $insert_refund_id = $this->save_refund_data($contractnum, $insertid, $citypy);

                    //根据合同号和城市拼音，获取合同开票记录，并将数据同步到经管系统
                    $insert_invoice_id = $this->save_invoice_data($contractnum, $insertid, $citypy);

                    //var_dump($insert_invoice_id);die;

                    if ($up_num) {
                        $this->model->commit();
                        $result['status'] = 2;
                        $result['msg'] = '添加合同成功';
                    } else {
                        $this->model->rollback();
                        $result['status'] = 0;
                        $result['msg'] = '添加合同失败';
                    }
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                } else {
                    $result['status'] = 0;
                    $result['msg'] = '添加合同失败,可能原因是：<br />'
                        . '该项目案列以及合同号在系统中已经存在，请检查合同号的填写是否正确';
                    $result['msg'] = g2u($result['msg']);
                    echo json_encode($result);
                    exit;
                }

            } else if ($id > 0 && $faction == 'delData') {

                //删除合同时，同时要删除对应的开票和回款记录，还要修改对应项目的项目名称跟合同号
                $payment_model = D("PaymentRecord");
                $billing_model = D("BillingRecord");
                $contract_case_info = $income_contract_model->get_contract_info_by_id($id, array("CASE_ID"));
                $contract_case_id = $contract_case_info[0]["CASE_ID"];
                $cond_where = "CASE_ID = " . $contract_case_id;
                //删除回款
                $payment_del_num = $payment_model->del_info_by_cond($cond_where);
                //删除开票
                $billing_del_num = $billing_model->del_info_by_cond($cond_where);
                //修改项目名称跟合同号（置空）
                $project_model = D("Project");
                $update_arr["CONTRACT"] = "";
                $update_arr["PROJECTNAME"] = "";
                $update_arr["COPANY"] = "";
                $up_num = $project_model->update_prj_info_by_id($prjId, $update_arr);
            }

            Vendor('Oms.Form');
            $form = new Form();
            $children = array(
                array("开票记录", U("/Advert/InvoiceRecord", $this->_merge_url_param)),
                array("回款记录", U("/Advert/refundRecords", $this->_merge_url_param))
            );
            //echo $is_from;
            $contract_model = D("Contract");
            $displace_status_remark =  $contract_model->get_displace_status_remark();
            $form->initForminfo(124);
            //城市
            $form->setMyField('CITY_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_CITY', FALSE);
            $form->setMyFieldVal("CITY_ID", $cityid, True);
            $form->setMyFieldVal("SCALETYPE", $scale_type, TRUE);
            $form->setMyField("DISPLACE", "GRIDVISIBLE", "-1");
            $form->setMyField("DISPLACE","LISTCHAR",array2listchar($displace_status_remark));
            $form->setMyField("ISSUEAMOUNT", "GRIDVISIBLE", "-1");

            $form->DELABLE = 0;
            if ($is_from == 1 || $is_from == 2) //用户通过硬广或活动进入
            {
                $where = "CASE_ID=" . $case_id;
            } else if ($is_from == 3)//用户通过合同管理菜单进入$is_from=3
            {
                $sign_user = strtolower($_SESSION["uinfo"]["uname"]);
                $where = "SIGN_USER= '" . $sign_user . "' AND CITY_ID = '" . $cityid . "'";
            }
            if ($is_from == 2 || $is_from == 3 || $_REQUEST["flowId"]) {
                $form->ADDABLE = 0;
            }

            //追加同步合同系统功能
            $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='syn_contract_system'>同步合同系统</a>";
            $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='change_contract_displace'>变更合同置换属性</a>";
            $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='save_displace_property'>保存置换属性</a>";
            $form->setChildren($children)->where($where);
            $caseType = !empty($_REQUEST['CASE_TYPE']) ? $_REQUEST['CASE_TYPE'] : 'default';
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->contractOptions, $caseType);
            $formHtml = $form->getResult();
            $this->assign('form', $formHtml);

            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
            $this->assign('prjid', $prjId);
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('contract_type', $contract_type);
            $this->assign('is_from', $is_from);
            $this->assign('scale_type', $scale_type);
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
            $this->display('advert_contract');
        }

        /**
         *$id  广告活动id
         */
        public function changeDisplace()
        {
            $id = intval($_REQUEST["fid"]);
            $sql = "select * from erp_project where id =".$id;
            $projectDisplace = $this->model->query($sql);
            $this->assign("projectDisplace",$projectDisplace);
            $this->display('change_displace');
        }
        /**
        +----------------------------------------------------------
        *根据合同号获取合同基本数据
        +----------------------------------------------------------
        * @param $file 要读取的文件
        +----------------------------------------------------------
        * @return $data 
        +----------------------------------------------------------
        */
        public function get_contract_data()
        {
            $contractnum = $_REQUEST["contractnum"];
			$projectId = $_REQUEST['projectId'];
            $cityid = $this->channelid;//$_SESSION["uinfo"]["city"];
            $sql = "select PY from ERP_CITY where ID=".$cityid;
            $citypy = $this->model->query($sql);
            $citypy = strtolower($citypy[0]["PY"]);//用户城市拼音
			$sql = "select b.CONTRACT_NO  as CONTRACT from ERP_CASE a left join ERP_INCOME_CONTRACT b on a.ID=b.CASE_ID where a.SCALETYPE in (select SCALETYPE from ERP_CASE where PROJECT_ID='$projectId') and b.CONTRACT_NO='$contractnum' and A.FSTATUS<>7 and b.CITY_ID='$cityid'";
			$res = M()->query($sql);  
			if($res){
				$result['msg'] = g2u('该合同号已经被相同业务类型使用！');
				echo $str = json_encode($result);
				  
			}else{
				//获取合同基本信息 
				load("@.contract_common");
				$contractData = getContractData($citypy,$contractnum);
				//var_dump($contractData);
				$contractData["contunit"] = iconv("gbk","utf-8",$contractData["contunit"]);
				$contractData["prjname"] = iconv("gbk", "utf-8", $contractData["prjname"]);
				$contractData["contbegintime"] = date("Y-m-d",$contractData["contbegintime"]);
				$contractData["contendtime"] = date("Y-m-d",$contractData["contendtime"]);
				$contractData["contmoney"] = intval($contractData["contmoney"]);
				$contractData["confirmtime"] =$contractData["confirmtime"] ? date("Y-m-d",$contractData["confirmtime"]):' ';
				$contractData["pubdate"] =$contractData["pubdate"] ? $contractData["pubdate"] : '';   
				echo json_encode($contractData);
			}
        }

        /**
        +----------------------------------------------------------
         * 变更合同置换属性
        +----------------------------------------------------------
         * @return $data
        +----------------------------------------------------------
         */
        public function updateDisplaceProperty(){

            //返回对象
            $return = array(
                'status'=>0,
                'msg'=>'',
                'data'=>null,
            );

            $fId  = isset($_POST['fid']) ? intval($_POST['fid']) : 0; //合同ID
            $data['DISPLACE']  = isset($_POST['displace']) ? intval($_POST['displace']) : ""; //置换属性值

            //更新数据
            $contractModel = D("Contract");
            $updateRet = $contractModel
                ->where('ID = ' . $fId)
                ->save($data);

            if($updateRet===false){
                $return['msg'] = g2u("亲，操作失败，请重试！");
            }else {
                $return['status'] = 1;
                $return['msg'] = g2u("亲，操作成功！");
            }

            //返回结果集
            die(@json_encode($return));

        }
        /**
        +----------------------------------------------------------
        *回款明细记录
        +----------------------------------------------------------
        * @param $file 要读取的文件
        +----------------------------------------------------------
        * @return $data 
        +----------------------------------------------------------
        */
        public function refundRecords(){

          //获取url中的相关数据
          $id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
          $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
          $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
          $add = isset($_GET['add']) ? intval($_GET['add']) : '';
          $contractid = $_REQUEST["parentchooseid"];
          Vendor('Oms.Form');
          $form = new Form();            
          $_REQUEST["prjid"] ? $prjId = $_REQUEST["prjid"] : 0;            
          $sql = "select CASE_ID from ERP_INCOME_CONTRACT where ID=".$contractid;
          $res = $this->model->query($sql);
          $caseId = $res[0]["CASE_ID"];  
          $where = "CASE_ID = '".$caseId."' AND CONTRACT_ID = '".$contractid."'";
          $form->initForminfo(135);
          
          $form->ADDABLE = 0;
          $form->EDITABLE = 0;
          $form->DELABLE = 0;
          $formHtml = $form
                    ->where($where)                      
                    ->getResult();    
          $this->assign('form',$formHtml);
          $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
          $this->display('advert_refund_records');            
        }

         
        
        /**
        +----------------------------------------------------------
        *根据合同号和城市拼音，获取合同的回款记录,并将数据同步到经管系统
        +----------------------------------------------------------
        * @param  none
        +----------------------------------------------------------
        * @return none
        +----------------------------------------------------------
        */
        public function save_refund_data($contractnum, $contract_id,$citypy="nj")
        {
            load("@.contract_common");
            $refundRecords = get_backmoney_data_by_no($citypy,$contractnum);
            if (empty($refundRecords) || (is_array($refundRecords) && count($refundRecords) == 0)) {
                return true;
            }
            //将合同回款记录插入到经管系统的数据库中
            if(!empty($refundRecords))
            {
               $contract_model = D("Contract");
               $payment_model = D("PaymentRecord");
               
               $conf_where = "ID = '".$contract_id."'";
               $field_arr = array("CASE_ID");
               $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
                // 获取项目的类型
                if (is_array($contract_info) && !empty($contract_info[0]['CASE_ID'])) {
                    $scaleType  = D('ProjectCase')->where('ID = ' . $contract_info[0]['CASE_ID'])->getField('SCALETYPE');
                }

               foreach($refundRecords as $key=>$val)
               {
				    $taxrate = get_taxrate_by_citypy($citypy);
					$tax = round($val["money"]/(1 + $taxrate) * $taxrate,2);

                    $refund_data["MONEY"] = $val["money"];
                    $refund_data["CREATETIME"] = $val["date"];
                    $refund_data["REMARK"] = $val["note"];
                    $refund_data["CASE_ID"] = $contract_info[0]["CASE_ID"];
                    $refund_data["CONTRACT_ID"] = $contract_id;
                    $insert_reund_id = $payment_model->add_refund_records($refund_data);
                    if(!$insert_reund_id)
                    {
                        return false;
                    }
                    else
                    {
                        //新增收益明细记录
                        if ($scaleType == self::YG_SCALETYPE) {
                            $income_info['INCOME_FROM'] = 11;
                        } else if ($scaleType == self::FWFSC_SCALETYPE) {
                            $income_info['INCOME_FROM'] = 22;
                        }

                        $income_info['CASE_ID'] = $contract_info[0]["CASE_ID"];
                        $income_info['ENTITY_ID'] = $contract_info[0]["ID"];
                        $income_info['INCOME'] =  $val["money"];
                        $income_info['OUTPUT_TAX'] =  $tax;
                        $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                        $income_info['OCCUR_TIME'] = $val["date"];
                        $income_info['PAY_ID'] = $insert_reund_id;                
                        $income_info['INCOME_REMARK'] = u2g($val["note"]);
                        $income_info['ORG_ENTITY_ID'] = $contract_info[0]["ID"];                
                        $income_info['ORG_PAY_ID'] = $insert_reund_id;

                        $ProjectIncome_model = D("ProjectIncome");
                        $res = $ProjectIncome_model->add_income_info($income_info);
                        if(!$res)
                        {
                            return false;
                        }
                    }
                }
               
            }
            return $insert_reund_id ? $insert_reund_id : false;
        }
          
       /**
        +----------------------------------------------------------
        *开票明细
        +----------------------------------------------------------
        * @param  none
        +----------------------------------------------------------
        * @return none
        +----------------------------------------------------------
        */
        public function InvoiceRecord()
        {   
            $billing_model = D("BillingRecord");
            $is_from = !empty($_REQUEST["is_from"]) ? $_REQUEST["is_from"] : 1;
            $contractid = !empty($_GET["parentchooseid"]) ? $_GET["parentchooseid"] : 0;//广告合同ID
            $city_id = $_SESSION["uinfo"]["city"]; 
            $city_py = D("Erp_city")->field("PY")->find($city_id);
            $city_py = $city_py["PY"];
            $id = isset($_REQUEST['ID']) ? intval($_REQUEST['ID']) : 0;
            $faction = isset($_REQUEST['faction']) ? strip_tags($_REQUEST['faction']) : '';
            $showForm = isset($_REQUEST['showForm']) ? intval($_REQUEST['showForm']) : '';

            //根据当前合同ID 获取对应的案例ID
            $sql = "select CASE_ID from ERP_INCOME_CONTRACT where ID=".$contractid;
            $case_id = $this->model->query($sql);
            $case_id = $case_id[0]["CASE_ID"]; 
            $date = date("Y-m-d H:i:s");
            
            if($showForm == 3 && $faction == 'saveFormData' && $id == 0)//用户点击保存  新增开票记录
            {   
                $taxrate = get_taxrate_by_citypy($city_py); 
				//var_dump($taxrate);die;
                $tax = round($_POST["INVOICE_MONEY"]/(1 + $taxrate) * $taxrate,2);
                if($contractid == 0)
                {
                  $result["status"] = 0;
                  $result["msg"] = "请选择合同记录";
                  echo json_encode(g2u($result));
                  exit;
                }              
                $data["CREATETIME"] = $_POST["CREATETIME"];     
                $data["INVOICE_MONEY"] = $_POST["INVOICE_MONEY"];//金额
                $data["REMARK"] = u2g($_POST["REMARK"]);
                $data["TAX"] = $tax;
                $data["STATUS"] = 1;
                $data["CONTRACT_ID"] = $contractid;
                $data["APPLY_USER_ID"] = $_POST["APPLY_USER_ID"];
                $data["CASE_ID"] = $case_id;
                $data["INVOICE_TYPE"] = 1;
				$data["INVOICE_CLASS"] =  $_POST["INVOICE_CLASS"];
				$data["INVOICE_BIZ_TYPE"] =  $_POST["INVOICE_BIZ_TYPE"];

                //var_dump($data);die;
                $res = $billing_model->add_billing_info($data);
                
                if($res)
                {
                   $result["status"] = 2;
                   $result["msg"] = "新增开票记录成功";
                }
                else
                {
                   $result["status"] = 0;
                   $result["msg"] = "新增开票记录失败";
                }
                echo json_encode(g2u($result));
                exit;
            } 
            elseif($showForm == 1 && $faction == 'saveFormData' && $id > 0)
            {
                $taxrate = get_taxrate_by_citypy($city_py);
                //var_dump($taxrate);die;
                $tax = round($_POST["INVOICE_MONEY"]/(1 + $taxrate) * $taxrate,2);
                $data["INVOICE_MONEY"] = $_POST["INVOICE_MONEY"];//金额
                $data["TAX"] = $tax;
                $data["REMARK"] = u2g($_POST["REMARK"]);
				$data["INVOICE_CLASS"] =  $_POST["INVOICE_CLASS"] ;
                $data["INVOICE_BIZ_TYPE"] = $_POST["INVOICE_BIZ_TYPE"];

                $up_num = $billing_model->update_info_by_id($id,$data);
                if($up_num)
                {
                    $result["status"] = 1;
                    $result["msg"] = "修改开票数据成功！";
                }
                else
                {
                    $result["status"] = 0;
                    $result["msg"] = "修改开票数据失败！！";
                }
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            } else if ($faction === 'delData') { //删除记录

                $delId = intval($_GET['ID']); //删除ID

                //判断状态
                $currentRequisiton = D('BillingRecord')->get_info_by_id($delId, array('FROMLISTID,FROMTYPE,STATUS'));

                if (is_array($currentRequisiton) && !empty($currentRequisiton) &&
                    $currentRequisiton[0]['STATUS'] != 1
                ) {
                    $result['msg'] = '亲，“未申请”的开票记录才能删除哦！';
                    $result['forward'] = U('Advert/displaceApply', $this->_merge_url_param);
                }


                D()->startTrans();
                if ($delId > 0) {

                    //删除明细
                    $delBillingRecord = D("BillingRecord")->del_info_by_id($delId);

                    if($currentRequisiton[0]['FROMTYPE']==2){ //来源是置换物品售卖的话
                        $updateBusinessStatus = D("DisplaceApply")->updateListStatus($currentRequisiton[0]['FROMLISTID'], 1); //更新状态到未申请状态
                    }

                    if ($delBillingRecord && $updateBusinessStatus!==false) {
                        $result['status'] = 'success';
                        $result['msg'] = g2u('亲，删除成功!');
                        D()->commit();
                    } else {
                        $result['msg'] = g2u('亲，删除失败,请重试!');
                        D()->rollback();
                    }
                }

                //输出结果
                die(@json_encode($result));
            }
            
            Vendor('Oms.Form');
            $form = new Form();
            
            $billing_status = $billing_model->get_invoice_status();
            $billing_status_remark = $billing_model->get_invoice_status_remark();
            $where = "CASE_ID = '".$case_id."'";
            $form->initForminfo(136)->where($where)
                ->setMyField("RELATIVE_CUS", "GRIDVISIBLE", "0")
                ->setMyField("TAX", "GRIDVISIBLE", "0")
                ->setMyField("STATUS", "LISTCHAR",array2listchar($billing_status_remark));
            if($showForm == 1 || $showForm == 3)
            {

                $form->setMyFieldVal("CREATETIME", $date,true)
                    ->setMyFieldVal("STATUS", 1,true)
                    ->setMyFieldVal("APPLY_USER_ID", $_SESSION['uinfo']['uid'],true);
                if( $showForm == 3 )
                {
                    $form->setMyFieldVal("STATUS", "1", true);
                    // 根据项目类型设置默认发票类型
                    $invoiceBizType = D('BillingRecord')->get_invoice_biz_type($case_id);  // todo
                    if ($invoiceBizType) {
                        $form->setMyFieldVal("INVOICE_BIZ_TYPE", $invoiceBizType, false);
                    }
                }
            }

            if($is_from == 3)
            {
                $form->ADDABLE = "-1";
                $form->DELABLE = "-1";
                $form->EDITABLE = "-1";
                $form->GABTN = "<a id='applyInvoice' class='btn btn-info btn-sm' href = 'JavaScript:;'>申请开票</a>";
                $form->GABTN .= "<a id='changeInvoice' class='btn btn-info btn-sm' href = 'JavaScript:;'>申请换票</a>";
                $form->GABTN .= "<a id='refundInvoice' class='btn btn-info btn-sm' href = 'JavaScript:;'>申请退票</a>";
                $form->SHOWCHECKBOX = "-1";
                $form->setMyField('FILES', 'FORMVISIBLE', 0)
                    ->setMyField('FILES', 'GRIDVISIBLE', 0);
                
            }

            // 非我方收筹不能新增开票记录
            if ($this->_request('CASE_TYPE') == 'fwfsc') {
                $form->ADDABLE = 0;
            }

            if( $_REQUEST["flowId"])
            {
                $form->DELABLE = 0;
                $form->EDITABLE = 0;   
            }               
            $form->DELCONDITION = '%STATUS% == '.$billing_status["no_apply"];
            $form->EDITCONDITION = '(%FROMTYPE% != 2 AND %STATUS% == '.$billing_status["no_apply"] . ')'; //置换过来的开票，不能编辑 ！！！
            $this->setPageOptionsVisible($this->_request('CASE_TYPE'), $form);  // 设置分页附近的相关操作的可见
            $caseType = !empty($_REQUEST['CASE_TYPE']) ? $_REQUEST['CASE_TYPE'] : 'default';
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, $this->invoiceOptions, $caseType);
            $formHtml =  $form->getResult();
            $this->assign("case_id",$case_id);
            $this->assign('form',$formHtml);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
            $this->assign('contractid',$contractid);
            $this->assign('paramUrl',$this->_merge_url_param);
            $this->display('advert_invoice_records');
        }
        
       /**
        +----------------------------------------------------------
        *发起开票流程
        +----------------------------------------------------------
        * @param  none
        +----------------------------------------------------------
        * @return none
        +----------------------------------------------------------
        */
        public function applyInvoice()
        {
            //判断所选记录是否已经申请开票
            $billing_model = D("BillingRecord");

            //优先POST  其次GET
            $caseid = isset($_POST["CASEID"]) ? $_POST["CASEID"] : $_GET["CASEID"];
            $recordId = isset($_POST["RECORDID"]) ? $_POST["RECORDID"] : $_GET["RECORDID"];
            $invoiceId = isset($_POST["invoiceId"]) ? $_POST["invoiceId"] : $_GET["invoiceId"];

            if ($_GET["flowId"]) {
                $cond_where = "FLOW_ID=" . $_GET["flowId"];
                $invoice_info = $billing_model->get_info_by_cond($cond_where, array("ID"));
                $invoiceId = $invoice_info[0]["ID"];
            }

            $case_info = D("ProjectCase")->get_info_by_id($caseid);
            $scale_type = $case_info[0]['SCALETYPE'];

            $where = " where 1=1 ";
            //如果是活动业务则根据合同号来处理
            $where .= $scale_type == 4?" and  contract_id=$recordId ":" and case_id=$caseid ";

            //根据$caseid 找到该案例下所有的开票记录
            if($scale_type == 4) {
                $sql1 = "select MONEY from erp_income_contract where id = $recordId";
            }
            else{
                $sql1 = "select MONEY from erp_income_contract where case_id = $caseid";
            }

            $money = M("Erp_income_contract")->query($sql1);
            $money = $money[0]["MONEY"];//合同金额

            $invoice_info = $billing_model->get_info_by_id($invoiceId, array("INVOICE_MONEY", "REMARK"));
            $invoice_money = $invoice_info[0]["INVOICE_MONEY"];//本次申请金额
            $remark = $invoice_info[0]["REMARK"];

            $sql3 = "select sum(INVOICE_MONEY) SUM_MONEY from erp_billing_record $where and status IN(4,6,7)";
            $sum_money = $billing_model->query($sql3);
            $sum_money = $sum_money[0]["SUM_MONEY"] ? $sum_money[0]["SUM_MONEY"] : 0;//累计开票金额

            $sql4 = "select sum(INVOICE_MONEY) SUM_MONEY from erp_billing_record $where and status IN(2,3,4,6,7)";
            $sum_money_apply = $billing_model->query($sql4);
            $sum_money_apply = $sum_money_apply[0]["SUM_MONEY"] ? $sum_money_apply[0]["SUM_MONEY"] : 0;//累计开票+申请中金额

            $billing_status = $billing_model->field("STATUS,ID")->where("ID = $invoiceId")->find($invoiceId);

            $billing_status = $billing_status["STATUS"];
            if($_REQUEST["is_ajax"])
            {
                //状态判定
                if($billing_status != 1)
                {
                     $result["state"] = 0;
                     $result["msg"] = "对不起，该记录已申请或已被否决，不能申请开票";
                     $result["msg"] = g2u($result["msg"]);
                     echo json_encode($result);
                     exit;
                }

                if($_REQUEST["CASE_TYPE"] != "fx")
                {
                    if($money < $sum_money_apply + $invoice_money)
                    {
                        $result["state"] = 0;
                        $result["msg"] = "该合同累计已申请的开票金额为 ".$sum_money_apply
                            . " 元；<br />本次申请金额为 ".$invoice_money." 元；<br />"
                            . "本次申请金额 + 累计申请金额 > 合同金额 ，不能申请开票！";
                        $result["msg"] = g2u($result["msg"]);
                        echo json_encode($result);
                        exit;
                    }
                     else
                    {
                        $result["state"] = 1;
                        $result["msg"] = "";
                        $result["msg"] = g2u($result["msg"]);
                        echo json_encode($result);
                        exit;
                    }
                }
                else
                {
                    $result["state"] = 1;
                    $result["msg"] = "";
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }
                
            }
            elseif(!$_REQUEST["is_ajax"])
            {
                Vendor('Oms.Form');
                $form = new Form();
                $sql = "select a.id,a.apply_user_id,to_char(a.createtime,'yyyy-MM-dd HH24:mi:ss') CREATETIME,
                        a.case_id,b.contract_no,c.scaletype,d.projectname,d.city_id
                         from erp_billing_record a 
                         left join erp_income_contract b on a.contract_id=b.id
                         left join erp_case c on a.case_id=c.id
                         left join erp_project d on c.project_id=d.id where a.case_id=$caseid and a.id=$invoiceId";

                $form->initForminfo(164);
                $form->SQLTEXT = "($sql)";

                if($_REQUEST["flowId"])
                {
                    $form->GABTN = " ";
                }

                $case_info = D("ProjectCase")->get_info_by_id($caseid,array("SCALETYPE","PROJECT_ID"));
                $scale_type = $case_info[0]["SCALETYPE"];
                $project_id = $case_info[0]["PROJECT_ID"];

                // 如果是分销业务
                if($scale_type == self::FX)
                {
                    // 如果是分销业务，增加导出会员功能
                    $form->GABTN .= "<a id='export_members' class='btn btn-info btn-sm' href='javascript:void(0);'>导出分销会员</a>";

                    $cond_where = "CASE_ID = '{$caseid}' AND RELATE_INVOICE_ID = '{$invoiceId}'";
                    $form_m = new Form();
                    $form_m->initForminfo(154)->where($cond_where);
                    $form_m->EDITABLE = 0;
                    $form_m->ADDABLE = 0;
                    $form_m->SHOWBOTTOMBTN = 0;
                    $form_m->GABTN = '';
                    $form_m->SHOWCHECKBOX = 0;
                    /***设置证件类型***/
                    $member_model = D('Member');
                    $certificate_type_arr = $member_model->get_conf_certificate_type();
                    $form_m->setMyField('CERTIFICATE_TYPE', 'LISTCHAR',array2listchar($certificate_type_arr), FALSE);
                    $conf_invoice_status = $member_model->get_conf_invoice_status_remark();
                    $form_m->setMyField('INVOICE_STATUS', 'LISTCHAR',array2listchar($conf_invoice_status['INVOICE_STATUS']), FALSE);
                    $form_m->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', TRUE);

                    //设置收费标准
                    $feescale = array();
                    $feescale = D('Project')->get_feescale_by_cid($caseid);

                    $fees_arr = array();
                    if(is_array($feescale) && !empty($feescale) )
                    {
                        foreach($feescale as $key => $value)
                        {
                            $unit = $value['STYPE'] == 0 ? self::UNIT_RMB_YUAN : self::UNIT_PERCENT; // 解决BUG #15383
                            $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'] . $unit;
                        }

                        //单套收费标准
                        $form_m->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($fees_arr['1']), FALSE);
                        //中介佣金
                        $form_m->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($fees_arr['2']), FALSE);
                        //置业顾问佣金
                        $form_m->setMyField('PROPERTY_REWARD', 'LISTCHAR', array2listchar($fees_arr['3']), FALSE);
                        //中介成交奖
                        $form_m->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['4']), FALSE);
                        //置业成交奖金
                        $form_m->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($fees_arr['5']), FALSE);

                        //设置项目名称
                        $project_info = D("PROJECT")->get_info_by_id($project_id);
                        $form_m->setMyFieldVal('PRJ_NAME', $project_info[0]['PROJECTNAME'], 0);
                    }
                    $form_member_distribution = $form_m->getResult();
                }

                //设置城市
                $form->setMyField("CITY_ID", "LISTSQL", "SELECT ID,NAME FROM ERP_CITY");
                $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, array(), $this->scaleTypeAliasMap[$case_info[0]['SCALETYPE']]);

                $formHtml = $form->getResult();

                //如果ID与发票ID一致  展现项目信息 (存在ID的情况下)
                if(!$_REQUEST['ID'] || ($_REQUEST['ID'] && $invoiceId == $_REQUEST['ID'])) {
                    $this->assign('form', $formHtml);
                }
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
                $this->assign('paramUrl',$this->_merge_url_param);
                $this->assign('money',$money);
                $this->assign('invoice_money',$invoice_money);
                $this->assign('sum_money',$sum_money);
                $this->assign('remark',$remark);
                //如果是分销会员开票，展示分销会员的信息
                $this->assign('scale_type',$scale_type);

                //如果ID与发票ID不一致  展现分销会员信息 (存在ID的情况下)
                if(!$_REQUEST['ID'] || ($_REQUEST['ID'] && $invoiceId != $_REQUEST['ID'])) {
                    $this->assign('form_member_distribution', $form_member_distribution);
                }
                $this->display('advert_apply_invoice'); 
            }             
        }




        /**
        +----------------------------------------------------------
         *根据合同号和城市拼音，获取合同的开票记录,并将数据同步到经管系统
        +----------------------------------------------------------
         * @param  $contractnum 合同号
         * @param  $contract_id 合同id
        +----------------------------------------------------------
         * @param $citypy 所在城市拼音
        +----------------------------------------------------------
         */

        public function save_invoice_data($contractnum,$contract_id,$citypy = "nj")
        {
            load("@.contract_common");
            $invoiceRecords = get_invoice_data_by_no($citypy,$contractnum);
            if (empty($invoiceRecords) || (is_array($invoiceRecords) && count($invoiceRecords) == 0)) {
                return true;
            }
            //将合同开票记录插入到经管系统的数据库中
            if(!empty($invoiceRecords))
            {
                $billing_model = D("BillingRecord");
                $billing_status = $billing_model->get_invoice_status();

                $contract_model = D("Contract");
                $conf_where = "ID = '$contract_id'";
                $field_arr = array("CASE_ID");
                $contract_info = $contract_model->get_info_by_cond($conf_where, $field_arr);
                if (is_array($contract_info) && !empty($contract_info[0]['CASE_ID'])) {
                    $scaleType  = D('ProjectCase')->where('ID = ' . $contract_info[0]['CASE_ID'])->getField('SCALETYPE');
                }

                foreach($invoiceRecords as $key=>$val)
                {
                    $taxrate = get_taxrate_by_citypy($citypy);
                    $tax = round($val["money"]/(1 + $taxrate) * $taxrate,2);

                    $invoice_data["INVOICE_MONEY"] = $val["money"];
                    $invoice_data["TAX"] = $tax;
                    $invoice_data["INVOICE_NO"] = $val["invono"];
                    $invoice_data["REMARK"] = $val["note"];
                    $invoice_data["INVOICE_TIME"] = $val["date"];
                    $invoice_data["STATUS"] = $billing_status["have_invoiced"];
                    $invoice_data["CONTRACT_ID"] = $contract_id;
                    $invoice_data["CASE_ID"] = $contract_info[0]["CASE_ID"];
                    $invoice_data["CREATETIME"] = $val["date"];
                    $invoice_data["INVOICE_TYPE"] = 1;
                    if ($val['type']) {
                        // 发票类型，如果发票类型不为1或2，则将发票类型设置为服务费
                        // 否则设置为1（广告费）或2（服务费）
                        if (!in_array($val['type'], array(1, 2))) {
                            $val['type'] = 2;
                        }
                        $invoice_data['INVOICE_BIZ_TYPE'] = $val['type'];
                    }
                    $insert_invoice_id = $billing_model->add_billing_info($invoice_data);
                    if(!$insert_invoice_id)
                    {
                        return false;
                    }
                    else
                    {
                        //新增收益明细记录
                        if ($scaleType == self::YG_SCALETYPE) {
                            $income_info['INCOME_FROM'] = 12;
                        } else if ($scaleType == self::FWFSC_SCALETYPE) {
                            $income_info['INCOME_FROM'] = 23;
                        }
                        $income_info['CASE_ID'] = $contract_info[0]["CASE_ID"];
                        $income_info['ENTITY_ID'] = $contract_info[0]["ID"];
                        $income_info['INCOME'] =  $val["money"];
                        $income_info['OUTPUT_TAX'] =  $tax;
                        $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                        $income_info['OCCUR_TIME'] = $val["date"];
                        $income_info['PAY_ID'] = $insert_invoice_id;
                        $income_info['INCOME_REMARK'] = u2g($val["note"]);
                        $income_info['ORG_ENTITY_ID'] = $contract_info[0]["ID"];
                        $income_info['ORG_PAY_ID'] = $insert_invoice_id;

                        $ProjectIncome_model = D("ProjectIncome");
                        $res = $ProjectIncome_model->add_income_info($income_info);
                        if(!$res)
                        {
                            return false;
                        }
                    }
                }
            }
            return $insert_invoice_id ? $insert_invoice_id : false;
        }

        
        /**
        +----------------------------------------------------------
        *  申请退、换发票 （硬广、分销、活动等）
        +----------------------------------------------------------
        */
        public function change_refund_invoice()
        {
            //返回结果集
            $return = array(
                'status'=>false,
                'msg'=>'',
                'data'=>null,
            );

            $invoiceId = isset($_REQUEST['invoiceId'])?$_REQUEST['invoiceId']:0;
            $type =  isset($_REQUEST['type'])?$_REQUEST['type']:'change_invoice';

            $type_info = $type == 'change_invoice'?"换票":"退票";

            $invoice_status = D("BillingRecord")->get_invoice_status();

            //事务开始
            D()->startTrans();

            $flag = false;
            $error_str= '';
            foreach($invoiceId as $key=>$val){
                $ret = M("erp_billing_record")->field("STATUS")->where("ID = $val")
                    ->find();
                //已经开票
                if($ret && $ret['STATUS']==$invoice_status['have_invoiced']){
                    $refundAmount = floatval(D('PaymentRecord')->where("BILLING_RECORD_ID = {$val}")->sum('MONEY'));
                    if ($refundAmount <= 0) {
                        //换票和退票
                        $data['STATUS'] = $type=='change_invoice'?$invoice_status['change_vote']:$invoice_status['refund_vote'];
                        $update = M("erp_billing_record")->where("ID = $val")->save($data);
                        if(!$update)
                            $flag = true;
                    } else {
                        $error_str .= "第" . ($key+1) . "条开票记录已经回款，不能申请" . $type_info . "! <br />" ;
                    }

                }
                else
                {
                    $error_str .= "第" . ($key+1) . "条，开票记录状态有问题 ! <br />" ;
                }
            }

            //如果存在报错
            if($error_str){
                D()->rollback();
                $return['status'] = false;
                $return['msg'] = g2u($error_str);
                die(@json_encode($return));
            }

            //如果更新失败
            if($flag){
                D()->rollback();
                $return['status'] = false;
                $return['msg'] = g2u("对不起，申请{$type_info}失败！");
                die(@json_encode($return));
            }

            D()->commit();
            $return['status'] = true;
            $return['msg'] = g2u("亲，申请{$type_info}成功！");
            die(@json_encode($return));
        }
        
        //新增回款记录方法
        public function addRefund($data,$invoiceId = "")
        {
            $payment_model = D("PaymentRecord");
            $this->model->startTrans();
            $insertid = $payment_model->add_refund_records($data);
            if($insertid){
                $result['status'] = 2;
                $result['msg'] = '添加回款记录成功';
                $this->model->commit();   
            }else{
                $result['status'] = 0;
                $result['msg'] = '添加回款记录失败';
                $this->model->rollback();   

            }
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;               
        }
    
        
        /**
        +----------------------------------------------------------
        *采购申请
        +----------------------------------------------------------
        * @param  none
        +----------------------------------------------------------
        * @return none
        +----------------------------------------------------------
        */
        public function show_purchase_requestion(){           
            //采购申请Model
            $purchase_requisition_model = D('PurchaseRequisition');
            //采购明细Model
            $purchase_list_model = D("PurchaseList");
            
            $uid = intval($_SESSION['uinfo']['uid']);
            $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
            $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
            $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
            $prj_id = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;//项目ID         
            $scale_type = $_REQUEST["scale_type"];
            
            $case_model = D("ProjectCase");
            $conf_where = "PROJECT_ID=$prj_id and SCALETYPE=$scale_type";
            $field_arr = array("ID");
            $case_id = $case_model->get_info_by_cond($conf_where,$field_arr);
            $case_id = $case_id[0]["ID"]; 
            //echo $this->model->_sql();
            //var_dump($case_id);
            //新增采购申请
            if($this->isPost() && !empty($_POST) && $showForm == 3 && 
                $faction == 'saveFormData' && $id == 0 )
            {   
                $requisition = array();
                $requisition['CASE_ID'] = $case_id;
                $requisition['REASON'] = u2g($_POST['REASON']);
                $requisition['USER_ID'] = $uid;
                $dept_id = intval($_SESSION['uinfo']['DEPTID']);
                $requisition['DEPT_ID'] = $dept_id;
                $requisition['APPLY_TIME'] = date('Y-m-d');
                $requisition['END_TIME'] = $this->_post('END_TIME');
                $requisition['PRJ_ID'] = $prj_id;

                //采购单状态
                $requisition_status = $purchase_requisition_model->get_conf_requisition_status();
                $status = !empty($requisition_status['not_sub']) ? intval($requisition_status['not_sub']) : 0;
                $requisition['STATUS'] = $status;
                $insert_id = $purchase_requisition_model->add_purchase_requisition($requisition);
                
                $result = array();
                if($insert_id > 0)
                {   
                    $result['status'] = 2;
                    $result['forward'] = U('Advert/show_purchase_requestion',$this->_merge_url_param);
                }
                else 
                {   
                    $result['status'] = 0;
                    $result['msg'] = '添加失败';
                    $result['forward'] = U('Advert/show_purchase_requestion',$this->_merge_url_param);
                }

                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }
            //修改采购申请单
            else if($this->isPost() && !empty($_POST) && $showForm == 1 && $faction == 'saveFormData' && $id > 0 )
            {   
                $result = array();
                //当前采购单状态，只有没有提交的采购单才能编辑
                $current_requisiton = array();
                $current_requisiton = $purchase_requisition_model->get_purchase_by_id($id, array('STATUS'));

                //采购单状态
                $requisition_status = $purchase_requisition_model->get_conf_requisition_status();
                $status = !empty($requisition_status['not_sub']) ? intval($requisition_status['not_sub']) : 0;

                if(is_array($current_requisiton) && !empty($current_requisiton) && 
                        $status != $current_requisiton[0]['STATUS'] )
                {
                    $result['status'] = 0;
                    $result['msg'] = '未提交的采购申请才能编辑';
                    $result['forward'] = U('Advert/show_purchase_requestion');
                }
                else
                {
                    $requisition = array();
                    $requisition['REASON'] = u2g($_POST['REASON']);
                    $requisition['END_TIME'] = $this->_post('END_TIME');
                    $up_num = 0;
                    $up_num = $purchase_requisition_model->update_purchase_by_id($id, $requisition);

                    if($up_num > 0)
                    {   
                        $result['status'] = 1;
                        $result['forward'] = U('Advert/show_purchase_requestion', $this->_merge_url_param);
                    }
                    else 
                    {   
                        $result['status'] = 0;
                        $result['msg'] = '修改失败';
                        $result['forward'] = U('Advert/show_purchase_requestion', $this->_merge_url_param);
                    }
                }

                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }
            else
            {
                Vendor('Oms.Form');
                $form = new Form();
                $form->initForminfo(133);
                if($showForm == 3)
                {   
                    $input_arr = array(
                       array('name' => 'PRJ_ID', 'val' => $prj_id, 'class' => 'PRJ_ID' )
                    );
                    $form = $form->addHiddenInput($input_arr);
                    $project = D("Project");
                    //项目名称
                    $project_info = $project->get_info_by_id($prj_id);
                    $form = $form->setMyFieldVal('PRJ_NAME', $project_info[0]['PROJECTNAME'], 0);
                }
                //项目名称
                $form = $form->setMyField('PRJ_ID', 'LISTSQL', 'SELECT ID,PROJECTNAME FROM ERP_PROJECT', TRUE);
                
                //发起人
                $form = $form->setMyField('USER_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', TRUE);
                //状态
                $requisition_status_remark = $purchase_requisition_model->get_conf_requisition_status_remark();
                $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($requisition_status_remark), TRUE);

                //设置按钮展示与否
                $form->EDITCONDITION = '%STATUS% == 0';
                $form->DELCONDITION = '%STATUS% == 0';   
                $children_data = array( array('采购明细', U('/Purchase/purchase_list',$this->_merge_url_param)) );
                $form->setChildren($children_data);
                $form = $form
                    ->where("CASE_ID = $case_id")
                    ->getResult();
                $this->assign("form",$form);
                $this->assign('contract_type',$_REQUEST["contract_type"]);
                $this->assign('case_id',$case_id);
                $this->assign('paramUrl',$this->_merge_url_param);
                $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
                $this->display("advert_show_purchase_requestion");
            }
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
        public function opinionFlow()
        {   
            $prjid = !empty($_GET['prjid']) ? $_GET['prjid'] : 0;//项目ID
            $uid = intval($_SESSION['uinfo']['uid']);
            $id = isset($_POST['ID']) ? intval($_POST['ID']) : 0;
            $invoiceid = !empty($_GET["invoiceId"]) ? $_GET["invoiceId"] : 0;

            Vendor('Oms.workflow');			
            $workflow = new workflow();            
            $flowId = $_REQUEST['flowId'];
            if($flowId)
            { 
                $billing_model = D("BillingRecord");
                $contract_model = D("Contract");
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
                            $cond_where = "FLOW_ID = ".$flowId;
                            $update_arr = array("STATUS"=>5);
                            $billing_model->update_info_by_cond($cond_where,$update_arr);
                            
                            //如果是分销的合同开票，流程否决后，让对应分销会员可以重新被选中开票,并且其发票状态边为未开
                            $case_id = intval($_REQUEST["CASEID"]);
                            $case_model = D("ProjectCase");
                            $cond_where = "ID = ".$case_id;
                            $case_info = $case_model->get_info_by_cond($cond_where,array("SCALETYPE"));
                            if($case_info[0]["SCALETYPE"] == 2)
                            {
                                $this->updateDistribution($flowId, $case_id, 1);
                            }
                            js_alert('否决成功',U('Flow/workStep'));
                        }else{
                            js_alert('否决失败');
                        }

                    }elseif($_REQUEST['flowStop']){

                        $auth = $workflow->flowPassRole($flowId);
                        if(!$auth){
                            js_alert('未经过必经角色');exit;
                        }

                        $str = $workflow->finishworkflow($_REQUEST);
                        if($str){
                            $cond_where1 = "FLOW_ID = ".$flowId;
                            $update_arr1 = array("STATUS"=>3);
                            $billing_model->update_info_by_cond($cond_where1,$update_arr1);                           
                            $contract_model->update_info_by_id($_REQUEST["RECORDID"], array("IS_NEED_INVOICE"=>1));
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
                $flowtype_pinyin = "hetongkaipiao";
                $auth = $workflow->start_authority($flowtype_pinyin);
                
                if(!$auth)
                {
                    js_alert('暂无权限');
                }
                $form = $workflow->createHtml();
                if($_REQUEST['savedata'])
                {   
                    $flow_data['type'] = $flowtype_pinyin;
                    $flow_data['CASEID'] = $_REQUEST['CASEID'];
                    $flow_data['RECORDID'] = $_REQUEST['RECORDID'];                    
                    $flow_data['INFO'] = strip_tags($_POST['INFO']);
                    $flow_data['DEAL_INFO'] = strip_tags($_POST['DEAL_INFO']);
                    $flow_data['DEAL_USER'] = strip_tags($_POST['DEAL_USER']);
                    $flow_data['DEAL_USERID'] = intval($_POST['DEAL_USERID']);
                    $flow_data['FILES'] = $_POST['FILES']; 
                    $flow_data['ISMALL'] =  intval($_POST['ISMALL']); 
                    $flow_data['ISPHONE'] =  intval($_POST['ISPHONE']); 
                    //var_dump($auth);die;                  
                    $str = $workflow->createworkflow($flow_data);
                    if($str)
                    { 
                        $sql = "UPDATE ERP_BILLING_RECORD SET STATUS=2,FLOW_ID = $str WHERE ID=".$invoiceid;
                        $this->model->execute($sql);
                        //如果是分销的合同开票，条转到分销页面
                        $case_id = intval($_REQUEST["CASEID"]);
                        $case_model = D("ProjectCase");
                        $cond_where = "ID = ".$case_id;
                        $case_info = $case_model->get_info_by_cond($cond_where,array("SCALETYPE"));
                        if($case_info[0]["SCALETYPE"] == 2)
                        {
                            js_alert('创建成功',U('MemberDistribution/open_billing_record',$this->_merge_url_param),1);
                        }
                        else
                        {
                            js_alert('创建成功',U('Advert/contract',$this->_merge_url_param),1); 
                        }                          
                        exit;
                    }
                    else
                    {
                        js_alert('创建失败',U('Advert/opinionFlow',$this->_merge_url_param),1);
                        exit;
                    }
                }
            }
            $this->assign('form', $form);
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('current_url', U('Advert/opinionFlow',$this->_merge_url_param));
            $this->assign('contract_type',$_REQUEST["contract_type"]);
            $this->display('advert_opinionFlow');
        }

        /**
         * 更新相应的memeber_distribution表记录状态
         * @param $flowId 工作流id
         * @param $caseId 案例id
         * @param $targetStatus 目标状态
         */
        private function updateDistribution($flowId, $caseId, $targetStatus)
        {
            if (empty($flowId) || empty($caseId)) {
                return;
            }
            $aBillingRec = D('BillingRecord')->where('FLOW_ID = ' . $flowId)->find();
            if (!empty($aBillingRec)) {
                $relateInvoiceID = $aBillingRec['ID'];
                $memberDistributionModel = D("MemberDistribution");
                $cond_where = "CASE_ID = $caseId AND RELATE_INVOICE_ID = $relateInvoiceID";
                $update_arr = array("INVOICE_STATUS" => $targetStatus,"RELATE_INVOICE_ID"=>NULL);
                $memberDistributionModel->startTrans();
                $result = $memberDistributionModel->update_info_by_cond($update_arr,$cond_where);
                if ($result !== false) {
                    $memberDistributionModel->commit();
                } else {
                    $memberDistributionModel->rollback();
                }

            }
        }

        /**
         * 设置分页附近的操作是否可见
         * @param $caseType
         * @param $form
         */
        private function setPageOptionsVisible($caseType, &$form)
        {
            if (empty($caseType) || empty($form)) {
                return;
            }

            // 硬广和独立活动的新增操作不可见
            if ($caseType == self::YG_CASE_ALIAS || $caseType == self::HD_CASE_ALIAS) {
                $form->ADDABLE = 0;
            }
        }
    }