<?php
class FinancialAction extends ExtendAction{
    /**
     * 导入开票权限
     */
    const IMPORTINVOICE = 175;

    /**
     * 导出开票权限
     */
    const EXPORTINVOICE = 176;

    /**
     * 确认报销权限
     */
    const REIM_CONFIRM = 733;

    /**
     * 打回权限
     */
    const REIM_REFUSE = 332;

    /**
     * 非付现成本确认权限
     */
    const NONCASHCOST = 408;

    private $model;
    private $_merge_url_param = array();
    //构造函数
    public function __construct()
    {
        $this->model = new Model();
        parent::__construct();
        // 权限映射表
        $this->authorityMap = array(
            'importInvoice' => self::IMPORTINVOICE,
            'exportInvoice' => self::EXPORTINVOICE,
            'reim_confirm' => self::REIM_CONFIRM,
            'reim_refuse' => self::REIM_REFUSE,
            'noncashcost' => self::NONCASHCOST
        );

        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = $_GET['CASEID'] : '';
        !empty($_GET['parentchooseid']) ? $this->_merge_url_param['contract_id'] = $_GET['parentchooseid'] : $_GET['contract_id'];
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = $_GET['RECORDID'] : '';
		!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;

        /**用户相关信息**/
        //用户ID
        $this->uid = intval($_SESSION['uinfo']['uid']);
        //城市
        $this->city = intval($_SESSION['uinfo']['city']);
        //城市拼音
        $this->user_city_py = trim($_SESSION['uinfo']['city_py']);
    }

    /**
    *收款确认
    *会员表中财务确认状态 ：1未确认   2部分确认   3已确认
    * 收益表中财务确认状态 ：1未确认  2已确认
    */
    public function financialConfirm()
    {
        $city_channel = $this->channelid;
        if($_POST["confirmMethod"])
        {
            //财务确认收款后 修改办卡会员表中财务确认状态
            $cardmember = D("Erp_cardmember");
            $payment = D("Erp_member_payment");

            $payment_model = D("MemberPay");
            $cardmember_model = D("Member");
            $financial_status = $cardmember_model->get_conf_confirm_status();
            $payment_status = $payment_model->get_conf_status();

            $res = 0;
            //支持同时选择多条记录  批量进行财务确认
            $memberId = $_REQUEST["memberId"];
            $confirmMethod = $_REQUEST["confirmMethod"];// 1表示通过会员ID进行确认  2表示通过收益明细ID进行确认
            $paymentId = $_REQUEST["paymentId"];//收益明细记录的ID

            //如果是针对用户的确认
            if($confirmMethod == 1)
            {
                if(isset($memberId) && count($memberId) >= 1)
                {
                    foreach($memberId as $val)
                    {
                       $where = "MID = ".$val;
                       $no_payment_record = $payment->where($where)->find();
                       if(!$no_payment_record)
                       {
                           echo 0;
                           userLog()->writeLog($val, $_SERVER["REQUEST_URI"], '预收确认:不存在相关记录：0', serialize($_POST));
                           exit();
                       }
                    }
                    $memberIdstr = implode(",",$memberId);
                    $where = "ID in (".$memberIdstr.") and FINANCIALCONFIRM=3";
                    $r = $cardmember->where($where)->select();
                    if($r)//$r 为真 表示有已经被确认过的
                    {
                        echo 0;
                        userLog()->writeLog($memberIdstr, $_SERVER["REQUEST_URI"], '预收确认:已确认：0', serialize($_POST));
                        exit();
                    }
                    else
                    {
                        //所选记录全部为未确认或部分确认的 进行批量确认 STATUS=1表示财务未确认  STATUS=2表示财务部分确认 3已确认
                        $sql = "select A.ID from ERP_MEMBER_PAYMENT A where A.MID in(".$memberIdstr.") and STATUS = ".$payment_status["wait_confirm"];
                        //echo $sql;die;
                        //找出付款明细表中对应会员的所有未确认的记录进行财务确认
                        $listId = $this->model->query($sql);//$listId 为二位索引数组
                        if($listId){
                                $listIdstr = implode(",",array2new($listId));
                                $where = "ID in(".$listIdstr.")";
                                $this->model->startTrans();
                                $res = $payment->where($where)->setField("STATUS",$payment_status["confirmed"]);//执行成功 返回被影响的行数
                                $some_confirm_member = "";//部分确认会员
                                $all_confirm_member = "";//全部确认会员
                                foreach ($memberId as $key=>$val)
                                {
                                    $member_info = $cardmember->where("ID=".$val)->field(array("UNPAID_MONEY"))->find();
                                    $unpaid_money = $member_info["UNPAID_MONEY"];
                                    if($unpaid_money > 0)
                                    {
                                        $some_confirm_member .= $val.",";
                                    }
                                    else
                                    {
                                        //$res1 = $cardmember->where($where1)->setField("FINANCIALCONFIRM",$financial_status["confirmed"]);
                                        $all_confirm_member .= $val.",";
                                        //echo $all_confirm_member;
                                    }
                                }

                                //修改存在未缴纳金额的会员财务确认状态为部分确认
                                if($some_confirm_member)
                                {
                                    $some_confirm_member = rtrim($some_confirm_member,",");
                                    $where1 = "ID IN(".$some_confirm_member.")";
                                    $some_up_num = $cardmember->where($where1)->setField("FINANCIALCONFIRM",$financial_status["part_confirmed"]);
                                    //echo $this->model->_sql();
                                    if(!$some_up_num)
                                    {
                                        $this->model->rollback();
                                        userLog()->writeLog($some_confirm_member, $_SERVER["REQUEST_URI"], '预收确认:部分确认:失败:4', serialize($_POST));
                                        echo 4;exit;
                                    }
                                }

                                //修改不存在未缴纳金额的财务确认状态为已确认
                                if($all_confirm_member)
                                {

                                    $all_confirm_member = rtrim($all_confirm_member,",");
                                    $where1 = "ID IN(".$all_confirm_member.")";
                                    $all_up_num = $cardmember->where($where1)->setField("FINANCIALCONFIRM",$financial_status["confirmed"]);

                                   // echo $this->model->_sql();
                                    if(!$all_up_num)
                                    {
                                        $this->model->rollback();
                                        userLog()->writeLog($all_up_num, $_SERVER["REQUEST_URI"], '预收确认:全部确认:失败:4', serialize($_POST));
                                        echo 4;exit;
                                    }
                                }
                                //die;
                                //财务确认收入后，往收益表中增加收益明细(部分确认，依然这么进入)
                                $res2 = $this->add_income_after_financial_confirm(2,0,array(),array2new($listId));
                                if(!$res2){
                                    $this->model->rollback();
                                    userLog()->writeLog($memberIdstr, $_SERVER["REQUEST_URI"], '预收确认:添加至收益表:失败:4', serialize($_POST));
                                    echo 4;exit;
                                }

                                if($res){
                                    $this->model->commit();
                                    userLog()->writeLog($memberIdstr, $_SERVER["REQUEST_URI"], '预收确认::成功:3', serialize($_POST));
                                    echo 3;exit();
                                }else{
                                    $this->model->rollback();
                                    userLog()->writeLog($memberIdstr, $_SERVER["REQUEST_URI"], '预收确认::失败:4', serialize($_POST));
                                    echo 4;exit();
                                }
                        }else{
                            echo 5;
                            userLog()->writeLog($memberIdstr, $_SERVER["REQUEST_URI"], '预收确认::失败:5', serialize($_POST));
                            exit();
                        }
                    }
                }
                elseif(count($memberId) < 1)//未选择任何一条记录
                {
                    echo 1;
                    userLog()->writeLog('', $_SERVER["REQUEST_URI"], '预收确认::失败:1', serialize($_POST));
                    exit();
                }
            }
            elseif($confirmMethod == 2)//通过付款明细的ID进行确认
            {
                if(isset($paymentId) && count($paymentId) >= 1)
                {
                    $paymentIdstr = implode(",",$paymentId);
                    $where = "ID in(".$paymentIdstr.") and STATUS = ".$payment_status["confirmed"];
                    $r = $payment->where($where)->select();
                    if($r)
                    {
                        echo 0;
                        userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], '预收确认::失败:0', serialize($_POST));
                        exit();
                    }
                    else
                    {
                        $where = "ID in(".$paymentIdstr.")";
                        $this->model->startTrans();
                        $res = D("Erp_member_payment")->where($where)->setField("STATUS",$payment_status["confirmed"]);

                        //财务确认收入后，往收益表中增加收益明细
                        $res2 = $this->add_income_after_financial_confirm($confirmMethod,0,array(),$paymentId);
                        if(!$res2)
                        {
                            userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], '预收确认:添加至收益表:失败:4', serialize($_POST));
                            $this->model->rollback();
                            echo 4;exit;
                        }
                        if($res)//如果执行成功 修改会员表中状态
                        {
                            $where = "ID in(".$paymentIdstr.")";
                            $mid = $payment->where($where)->field("MID")->find();
                            $mid = $mid['MID'];
                            $where1 = "MID = $mid and STATUS = ".$payment_status["wait_confirm"];

                            $someConf = $payment->where($where1)->field("ID")->find();
                            //echo $this->model->_sql();
                            //var_dump($someConf);
                            $member_info = $cardmember->where("ID=".$mid)->field(array("UNPAID_MONEY"))->find();
                            $unpaid_money = intval($member_info["UNPAID_MONEY"]);

                            if(!empty($someConf) || $unpaid_money > 0)//该会员状态改为部分确认2
                            {
                                $sql = "update ERP_CARDMEMBER set FINANCIALCONFIRM=".$financial_status["part_confirmed"]." where ID = ".$mid;
                                $res1 = $this->model->execute($sql);
                                //var_dump($res1);die;
                                 if($res1){
                                     $this->model->commit();
                                     userLog()->writeLog($mid, $_SERVER["REQUEST_URI"], '预收确认:部分确认:成功:3', serialize($_POST));
                                     echo 3;exit();
                                 }else{
                                     $this->model->rollback();
                                     userLog()->writeLog($mid, $_SERVER["REQUEST_URI"], '预收确认:部分确认:失败:4', serialize($_POST));
                                     echo 4;exit();
                                }
                            }
                            else
                            {
                                 $sql = "update ERP_CARDMEMBER set FINANCIALCONFIRM=".$financial_status["confirmed"]." where ID = ".$mid;
                                 $res2 = $this->model->execute($sql);
                                 if($res2){
                                     $this->model->commit();
                                     userLog()->writeLog($mid, $_SERVER["REQUEST_URI"], '预收确认:全部确认:成功:3', serialize($_POST));
                                     echo 3;exit();
                                 }else{
                                      $this->model->rollback();
                                     userLog()->writeLog($mid, $_SERVER["REQUEST_URI"], '预收确认:全部确认:失败:4', serialize($_POST));
                                      echo 4;exit();
                                 }
                             }
                         }
                        else
                        {
                             $this->model->rollback();
                            userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], '预收确认::失败:4', serialize($_POST));
                             echo 4;exit();
                         }
                    }
                }
                else
                {
                    echo 1;
                    exit();
                }
            }
            else
            {
                echo 2;
                exit();
            }
        }
        else
        {
            Vendor('Oms.Form');
            $form = new Form();
            $arr_param = array(
                    array('2','CARDSTATUS') ,
                    array('3','RECEIPTSTATUS'),
                    array('4','INVOICE_STATUS'),
                    array('5','FINANCIALCONFIRM')
                );
            $children = array(array('付款明细',U('/Financial/payment')));
            $where = "FINANCIALCONFIRM IN(1,2)";
            $form->initForminfo(117);
            if($_POST["search1_t"] || $_POST["search2_t"] || $_POST["search3_t"] || $_POST["search4_t"])
            {
                $sqltext = "(SELECT DISTINCT A.*  FROM ERP_CARDMEMBER A LEFT JOIN ERP_MEMBER_PAYMENT B ON B.MID=A.ID)";
                $form->SQLTEXT = $sqltext;
            }
            $form->where("CITY_ID = ".$city_channel." AND FINANCIALCONFIRM IN(1,2) AND PAID_MONEY != 0 AND STATUS=1");
            //设置付款方式
            $member_pay = D('MemberPay');
            $pay_arr = $member_pay->get_conf_pay_type();
            $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE);
//            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
            $formHtml = $form->setChildren($children)->showStatusTable($arr_param)->getResult();
            $this->assign('form',$formHtml);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
            $this->assign('paramUrl',$this->_merge_url_param);
            $this->display('financial_confirm');
        }
    }


    /**
     *  财务取消确认
    * 取消确认
    */
    public function cancleConfirm()
    {
        //返回结果集
        $return  =  array(
            'status'=>false,
            'msg'=>'',
            'data'=>null,
        );

        if($_POST)
        {
            //model
            $payment_model = D("MemberPay");
            $cardmember_model = D("Member");
            $cardmember = D("Erp_cardmember");
            $payment = D("Erp_member_payment");

            //财务状态、付款状态
            $financial_status = $cardmember_model->get_conf_confirm_status();
            $payment_status = $payment_model->get_conf_status();

            $memberId = $_REQUEST["memberId"];
            $paymentId = $_REQUEST["paymentId"];
            $cancleMethod = $_REQUEST["cancleMethod"];

            //通过会员ID进行取消确认
            if($cancleMethod == 1)
            {
                if(isset($memberId) && count($memberId) >= 1)
                {
                    foreach($memberId as $val)
                    {
                       $where = "MID = ".$val;
                       $no_payment_record = $payment->where($where)->find();
                       if(!$no_payment_record)
                       {
                           echo 5;
                           userLog()->writeLog($val, $_SERVER["REQUEST_URI"], '取消确认:查询erp_member_payment:失败:5', serialize($_POST));
                           exit();
                       }
                    }
                    $memberIdstr = implode(",",$memberId);
                    $where = "ID in(".$memberIdstr.") and FINANCIALCONFIRM = 1";
                    $r = $cardmember->where($where)->select();
                    if($r)
                    {
                        echo 0;//提示有未确认状态 不能进行取消操作
                        userLog()->writeLog($memberIdstr, $_SERVER["REQUEST_URI"], '取消确认:查询Erp_cardmember:失败:0', serialize($_POST));
                        exit();
                    }
                    else
                    {
                        $sql = "select ID from ERP_MEMBER_PAYMENT where MID in(".$memberIdstr.") and status = 1";
                        $listId = $this->model->query($sql);
                        if(!$listId)
                        {
                            echo 5;//提示所选的所有记录中没有任何一条中有被确认的收款
                            userLog()->writeLog($val, $_SERVER["REQUEST_URI"], '取消确认:查询erp_member_payment:失败:5', serialize($_POST));
                            exit();
                        }
                        else
                        {
                            $listIdstr ="";
                            $listIdstr = implode(",",array2new($listId));
                            $where = "ID in(".$listIdstr.") AND STATUS != 4";
                            $this->model->startTrans();
                            $res = $payment->where($where)->setField("STATUS",$payment_status["wait_confirm"]);//修改明细表中状态为未确认（0）
                            //同时修改会员表中状态为未确认
                            $res1 = $cardmember->where("ID in(".$memberIdstr.")")->setField("FINANCIALCONFIRM",$financial_status["no_confirm"]);

                            //财务取消确认后，往收益表中增加负收益明细
                            $res2 = $this->add_income_after_financial_confirm(2,1,array(),array2new($listId));
                            if(!$res2){
                                $this->model->rollback();
                                userLog()->writeLog($listIdstr, $_SERVER["REQUEST_URI"], '取消确认:往收益表中增加负收益明细:失败:4', serialize($_POST));
                                echo 4;exit;
                            }

                            if($res && $res1){
                                $this->model->commit();
                                userLog()->writeLog($listIdstr, $_SERVER["REQUEST_URI"], '取消确认::成功:3', serialize($_POST));
                                echo 3;exit();
                            }else{
                                $this->model->rollback();
                                userLog()->writeLog($listIdstr, $_SERVER["REQUEST_URI"], '取消确认::失败:4', serialize($_POST));
                                echo 4;exit();
                            }
                        }
                    }

                }
                else
                {
                    echo 1;//提示请选择记录
                    exit();
                }
            }
            elseif($cancleMethod == 2)//通过收款明细ID进行取消
            {
                if(isset($paymentId) && count($paymentId) >= 1)
                {
                    $paymentIdstr = implode(",",$paymentId);
                    $where = "ID in(".$paymentIdstr.") and STATUS = ".$payment_status["confirmed"];
                    $r = $payment->where($where)->field("ID")->find();
                    //echo $payment->_sql();die;
                    if(empty($r)){
                        userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], '取消确认:查询Erp_member_payment:失败:6', serialize($_POST));
                       echo 6;
                       exit();
                    }else{

                        //如果存在等待确认状态则打回
                        $where = "ID in(".$paymentIdstr.") and STATUS = ".$payment_status["wait_confirm"];
                        $r = $payment->where($where)->field("ID")->find();
                        if($r){
                            echo 0;
                            userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], '取消确认:查询Erp_member_payment:失败:0', serialize($_POST));
                            exit();
                        }

                        $where = "ID in(".$paymentIdstr.")";
                        $this->model->startTrans();
                        $res = $payment->where($where)->setField("STATUS",$payment_status["wait_confirm"]);  //修改选中收款的状态
                        //财务取消确认后，往收益表中增加负收益明细
                        if($res)
                        {
                            $res3 = $this->add_income_after_financial_confirm($cancleMethod,1,array(),$paymentId);
                        }
                        if(!$res3){
                            $this->model->rollback();
                            userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], '取消确认:往收益表中增加负收益明细:失败:4', serialize($_POST));
                            echo 4;exit;
                        }

                         $mid = $payment->where($where)->field("MID")->find(); //根据被修改的记录 找出被修改记录中包含的所有MID
                         $mid = $mid["MID"];
                         $where = "MID = $mid and STATUS = ".$payment_status["confirmed"];
                         $someConf = $payment->where($where)->field("MID")->find();//如果找到 这些会员的状态改为部分确认，剩下的会员为未确认

                         if(!empty($someConf)){
                             $someConf = $someConf["MID"];
                             $res1 = $cardmember->where("ID = ".$someConf)->setField("FINANCIALCONFIRM",$financial_status["part_confirmed"]);
                             if($res1){
                                 $this->model->commit();
                                 userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], '取消确认:部分取消:成功:3', serialize($_POST));
                                 echo 3;exit();
                             }else{
                                 $this->model->rollback();
                                 userLog()->writeLog($paymentIdstr, $_SERVER["REQUEST_URI"], '取消确认:部分取消:失败:4', serialize($_POST));
                                 echo 4;exit();
                             }

                         }else{
                             $res2 = $cardmember->where("ID = ".$mid)->setField("FINANCIALCONFIRM",$financial_status["no_confirm"]);
                             if($res2){
                                 $this->model->commit();
                                 echo 3;exit();
                             }else{
                                 $this->model->rollback();
                                 echo 4;exit();
                             }
                         }
                    }
                }
                else
                {
                    echo 1;
                    exit();
                }
            }
            else
            {
                echo 2;
                exit();
            }
         }
    }

    /**
     * 会员开票列表页
     * @param none
     * return
     */
    public function invoice()
    {
        $city_channel = $this->channelid;
        Vendor('Oms.Form');
        $form = new Form();
        $arr_param = array(
                array('2','CARDSTATUS') ,
                array('3','RECEIPTSTATUS'),
                array('4','INVOICE_STATUS'),
                array('5','FINANCIALCONFIRM')
            );
        $GABTN = '<a id="importInvoice" href="javascript:;" class="btn btn-info btn-sm">导入开票</a>'
             . '<a id="exportInvoice" href="javascript:;" class="btn btn-info btn-sm">导出开票</a>';
        $form->initForminfo(117)->where("CITY_ID = ".$city_channel);
        $form->GABTN = $GABTN;
        //设置付款方式
        $member_pay = D('MemberPay');
        $pay_arr = $member_pay->get_conf_pay_type();
        $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE);
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
        $formhtml = $form
            ->where("(INVOICE_STATUS = 5 OR CHANGE_INVOICE_STATUS = 2)")
            ->showStatusTable($arr_param)
            ->getResult();
        $this->assign('form',$formhtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        //添加搜索条件
        $this->assign('filter_sql',$form->getFilterSql());
        $this->display('financial_invoice');
    }


    /**
    +----------------------------------------------------------
    * 财务页面导入开票
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function importInvoice()
    {
        //初始化
        $member_model = D("Member");
        $billing_mode =D("BillingRecord");

        //如果取消
        if($_POST["cancle"])
        {
            $this->redirect("Financial/invoice");
        }
        //如果上传
        if( $_FILES )
        {
            //限制
            if($_FILES["upfile"]["size"] > 5000000) {
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], '开票:导入开票:文件大小不能超过 5M !:失败', serialize($_FILES['upfile']));
                die("文件过大，注意：文件大小不能超过 5M !");
            }


            //获取文件后缀名
            $file_name = $_FILES["upfile"]["name"];
            $file_name_arr = explode(".",$file_name);
            $file_ext = $file_name_arr[count($file_name_arr)-1];

            if($file_ext != "xls" && $file_ext != "xlsx") {
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], '开票:导入开票:文件大小不能超过 5M !:失败', serialize($_FILES['upfile']));
                die("上传文件不是excel表格，请重新上传！");
            }


            //获取文件
            $file = $_FILES["upfile"]["tmp_name"];

            //获取发票的开票状态
            $member_invoice_status = $member_model->get_conf_invoice_status();

            Vendor('phpExcel.PHPExcel');
            Vendor('phpExcel.IOFactory.php');
            Vendor('phpExcel.Reader.Excel5.php');

            $PHPExcel = new PHPExcel();
            $PHPReader = new PHPExcel_Reader_Excel2007();
            if(!$PHPReader->canRead($file)){
                $PHPReader = new PHPExcel_Reader_Excel5();
                if(!$PHPReader->canRead($file)){
                    echo 'no Excel !';
                    userLog()->writeLog('', $_SERVER["REQUEST_URI"], '开票:导入开票:非excel文件:失败', serialize($_FILES['upfile']));
                    return ;
                }
            }

            $PHPExcel = $PHPReader->load($file);
            /**读取excel文件中的第一个工作表*/
            $currentSheet = $PHPExcel->getSheet(0);
            /**取得最大的列号*/
            $allColumn = $currentSheet->getHighestColumn();
            /**取得一共有多少行*/
            $allRow = $currentSheet->getHighestRow();
            /**从第二行开始输出，因为excel表中第一行为列名*/

            $data = array();
            //循环EXCEL
            for($currentRow = 3; $currentRow <= $allRow; $currentRow++){
                //单据号
                $receiptno = $currentSheet->getCellByColumnAndRow((ord(A) - 65),$currentRow)->getValue();
                $receiptno = str_replace(","," ",$receiptno);

                //发票编号
                $invoiceno = $currentSheet->getCellByColumnAndRow((ord(R) - 65),$currentRow)->getValue();
                $invoiceno = u2g($invoiceno);

                //金额
                $money = $currentSheet->getCellByColumnAndRow((ord(L) - 65),$currentRow)->getValue();

                //发票备注
                $remark = $currentSheet->getCellByColumnAndRow((ord(F) - 65),$currentRow)->getValue();
                $remark = u2g($remark);

                //发票税率
                $taxrate = get_taxrate_by_citypy($this->user_city_py);

                //发票税额
                $tax = ($currentSheet->getCellByColumnAndRow((ord(N)-65),$currentRow)->getValue());

                //赋值
                $data[] =array("receiptno"=>$receiptno,"invoiceno"=>$invoiceno,"money"=>$money,"remark"=>$remark,"taxrate"=>$taxrate,'tax'=>$tax);
            }

            //计数
            $i = 0;
            //返回的结果集合
            $return_error = "";

            foreach($data as $key=>$val)
            {
                //如果不存在发票编号
                if(empty($val["invoiceno"])) {
                    $return_error .= "第" . ($key + 1) . "条，发票编号没有填写.<br />";
                    continue;
                }

                $cond_where = "RECEIPTNO='".$val['receiptno']."' AND CITY_ID = ".$_SESSION["uinfo"]["city"]." AND "
                    . "(INVOICE_STATUS = 5 OR CHANGE_INVOICE_STATUS=2)";
                //获取会员信息
                $member_info = $member_model->get_info_by_cond($cond_where,array("ID","CASE_ID","CHANGE_INVOICE_STATUS","INVOICE_NO","PRJ_ID","PRJ_NAME"));

                if(empty($member_info) || !$member_info[0]["ID"])
                {
                    $return_error .= "第" . ($key + 1) . "条，状态不符合要求.<br />";
                    continue;
                }

                //换票会员的开票（红冲发票）
                if($member_info[0]["CHANGE_INVOICE_STATUS"] == 2)
                {
                    $id = $member_info[0]["ID"];
                    $caseid = $member_info[0]["CASE_ID"];
                    $prj_id = $member_info[0]["PRJ_ID"];

                    //获取合同编号
                    $contract_num = M("erp_project")
                        ->field("CONTRACT")
                        ->where('ID = ' . $prj_id)
                        ->find();
                    $contract_num = $contract_num['CONTRACT'];

                    $update_arr["INVOICE_NO"] = $val["invoiceno"];
                    $update_arr["CONFIRMTIME"] = date("Y-m-d H:i:s",time());
                    //会员换票状态为成功状态
                    $update_arr["CHANGE_INVOICE_STATUS"] = 3;

                    $this->model->startTrans();

                    $up_num = $member_model->update_info_by_id($id,$update_arr);


                    if(!$up_num) {
                        $return_error .= "第" . ($key + 1) . "条，开票失败1.<br />";
                        $this->model->rollback();
                        continue;
                    }


                    $remark = $val["remark"] ? $val["remark"] : "无";
                    //往开票记录表中添加红冲开票数据
                    $insert_arr["CASE_ID"] = $caseid;
                    $insert_arr["CONTRACT_ID"] = $id;
                    $insert_arr["INVOICE_NO"] = $member_info[0]["INVOICE_NO"];
                    $insert_arr["INVOICE_MONEY"] = 0-$val['money'];
                    $insert_arr["TAX"] = round((0-$val['money'])/(1+$val["taxrate"]) * $val["taxrate"],2);
                    $insert_arr["USER_ID"] = $_SESSION['uinfo']['uid'];
                    $insert_arr["CREATETIME"] = date("Y-m-d H:i:s",time());
                    $insert_arr["INVOICE_TIME"] = date("Y-m-d H:i:s",time());
                    $insert_arr["REMARK"] = "红冲发票";
                    $insert_arr["INVOICE_TYPE"] = 2;

                    $insert_billing_id_e = $billing_mode->add_billing_info($insert_arr);

                    if(!$insert_billing_id_e){
                        $return_error .= "第" . ($key + 1) . "条，开票失败2.<br />";
                        $this->model->rollback();
                        continue;
                    }

                    //往收益表中添加红冲收益
                    $this->add_income_after_financial_invoice(array($id),1,$insert_billing_id_e);

                    //同步合同系统需要的数据(换发票有两次)
                    $invoiceno_arr[$contract_num][] = array(
                        'invoice_no' => $member_info[0]["INVOICE_NO"],
                        'invoice_money' => -$val['money'],
                        'invoice_tax' => $insert_arr["TAX"],
                        'invoice_note' => "红冲发票",
                    );

                    //往开票记录表中添加新的开票数据
                    $insert_arr["CASE_ID"] = $caseid;
                    $insert_arr["CONTRACT_ID"] = $id;
                    $insert_arr["INVOICE_NO"] = $val['invoiceno'];
                    $insert_arr["INVOICE_MONEY"] = $val['money'];
                    $insert_arr["TAX"] = round($val['money']/(1+$val["taxrate"]) * $val["taxrate"],2);
                    $insert_arr["USER_ID"] = $_SESSION['uinfo']['uid'];
                    $insert_arr["CREATETIME"] =  date("Y-m-d H:i:s",time());
                    $insert_arr["INVOICE_TIME"] = date("Y-m-d H:i:s",time());
                    $insert_arr["REMARK"] = $remark;
                    $insert_arr["INVOICE_TYPE"] = 2;
                    $insert_billing_id = $billing_mode->add_billing_info($insert_arr);

                    if(!$insert_billing_id){
                        $return_error .= "第" . ($key + 1) . "条，开票失败3.<br />";
                        $this->model->rollback();
                        continue;
                    }

                    $res_add = $this->add_income_after_financial_invoice(array($id),1,$insert_billing_id);

                    if(!$res_add){
                        $return_error .= "第" . ($key + 1) . "条，开票失败4.<br />";
                        $this->model->rollback();
                        continue;
                    }

                    //同步合同系统需要的数据(换发票有两次)
                    $invoiceno_arr[$contract_num][] = array(
                        'invoice_no' => $val['invoiceno'],
                        'invoice_money' => $val['money'],
                        'invoice_tax' => $insert_arr["TAX"],
                        'invoice_note' => $remark,
                    );

                    //提交
                    $this->model->commit();
                    $i++;

                }
                //正常会员的开票
                else if($member_info[0]["CHANGE_INVOICE_STATUS"] == 1)
                {
                    $id = $member_info[0]["ID"];
                    $caseid = $member_info[0]["CASE_ID"];
                    $prj_id = $member_info[0]["PRJ_ID"];

                    $update_arr["INVOICE_STATUS"] = $member_invoice_status["invoiced"];
                    $update_arr["INVOICE_NO"] = $val["invoiceno"];
                    $update_arr["CONFIRMTIME"] = date("Y-m-d H:i:s",time());
                    $update_arr["CONFIRM_UID"] = $_SESSION["uinfo"]["uid"];

                    $this->model->startTrans();
                    $up_num = $member_model->update_info_by_id($id,$update_arr);

                    if(!$up_num) {
                        $return_error .= "第" . ($key + 1) . "条，开票失败1.<br />";
                        $this->model->rollback();
                        continue;
                    }

                    $remark = $val["remark"] ? $val["remark"] : "无";
                    $insert_arr["CASE_ID"] = $caseid;
                    $insert_arr["CONTRACT_ID"] = $id;
                    $insert_arr["INVOICE_NO"] = $val['invoiceno'];
                    $insert_arr["INVOICE_MONEY"] = $val['money'];
                    $insert_arr["TAX"] = round($val['money']/(1+$val["taxrate"]) * $val["taxrate"],2);
                    $insert_arr["USER_ID"] = $_SESSION['uinfo']['uid'];
                    $insert_arr["CREATETIME"] = date("Y-m-d H:i:s",time());
                    $insert_arr["INVOICE_TIME"] = date("Y-m-d H:i:s",time());
                    $insert_arr["REMARK"] = $remark;
                    $insert_arr["INVOICE_TYPE"] = 2;

                    $insert_billing_id = $billing_mode->add_billing_info($insert_arr);

                    if(!$insert_billing_id){
                        $return_error .= "第" . ($key + 1) . "条，开票失败2.<br />";
                        $this->model->rollback();
                        continue;
                    }

                    $ret_add = $this->add_income_after_financial_invoice(array($id),1,$insert_billing_id);

                    if(!$ret_add){
                        $return_error .= "第" . ($key + 1) . "条，开票失败3.<br />";
                        $this->model->rollback();
                        continue;
                    }


                    $project_cost_model = D("ProjectCost");
					//$house_model = D("House");
					//$house_data = $house_model->get_house_info_by_prjid($prj_id,array('ISFUNDPOOL' ));
					//$ispool_arr = array('1'=>0,'0'=>1,'-1'=>1);//是否资金池对应关系数组
                    $paymentlist = M('Erp_member_payment')->where("STATUS=1 and MID=$id and PAY_TYPE=1")->select();
					if($paymentlist){
						$cost_insert_id = 1;
						foreach($paymentlist as $pone){
							if($pone['PAY_TYPE']==1){
								//案例编号 【必填】
								$cost_info['CASE_ID'] = $caseid;
								//业务实体编号 【必填】
								$cost_info['ENTITY_ID'] =  $id;
								$cost_info['EXPEND_ID'] = $insert_billing_id;
								$cost_info['ORG_ENTITY_ID'] = $id;
								$cost_info['ORG_EXPEND_ID'] = $insert_billing_id;

								// 成本金额 【必填】
								$cost_info['FEE'] = get_pos_fee($_SESSION["uinfo"]["city"],$pone['TRADE_MONEY'],$pone['MERCHANT_NUMBER']);
								//操作用户编号 【必填】
								$cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
								//发生时间 【必填】
								$cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());
								//是否资金池（0否，1是） 【必填】
								$cost_info['ISFUNDPOOL'] = 0;
								//成本类型ID 【必填】
								$cost_info['ISKF'] = 1;
								//进项税 【选填】
								$cost_info['INPUT_TAX'] = 0;
								//成本类型ID 【必填】
								//$cost_info['FEE_ID'] = $v["FEE_ID"];
								$cost_info['EXPEND_FROM'] = 28;
								$cost_info['FEE_REMARK'] = "会员开票POS机手续费";
								$cost_info['FEE_ID'] = 95;

								$cost_insert_id = $project_cost_model->add_cost_info($cost_info);
								if(!$cost_insert_id){

									break;
								}
							}
						}

						if(!$cost_insert_id){
							$return_error .= "第" . ($key + 1) . "条，开票失败4.<br />";
							$this->model->rollback();
							continue;
						}
					}

                    //获取合同编号
                    $contract_num = M("erp_project")
                        ->field("CONTRACT")
                        ->where('ID = ' . $prj_id)
                        ->find();
                    $contract_num = $contract_num['CONTRACT'];

                    //同步合同系统需要的数据
                    $invoiceno_arr[$contract_num][] = array(
                        'invoice_no' => $val['invoiceno'],
                        'invoice_money' => $val['money'],
                        'invoice_tax' => round($val['money']/(1+$val["taxrate"]) * $val["taxrate"],2),
                        'invoice_note' => $remark,
                    );

                    $this->model->commit();
                    userLog()->writeLog('', $_SERVER["REQUEST_URI"], '开票:导入开票::成功', serialize($_FILES['upfile']));
                    $i++;
                }
            }

            /****异步同步到合同系统****/
            if($invoiceno_arr){
                foreach($invoiceno_arr as $key=>$val){
                    $cur_invoiceno = array();
                    $invoice_moneys = $invoice_taxs = 0;
                    foreach($val as $k=>$v){
                        //发票编号
                        $cur_invoiceno[] = $v['invoice_no'];
                        //金额
                        $invoice_moneys += $v['invoice_money'];
                        //税费
                        $invoice_taxs += $v['invoice_tax'];
                        //说明
                        $invoice_notes = $v['invoice_note'];

                        //如果不是连票
                        if(($v['invoice_no']+1) != $val[$k+1]['invoice_no']){
                            if(count($cur_invoiceno) == 1){
                                $invoice_nos = $cur_invoiceno[0];
                            }else{
                                $invoice_nos = $cur_invoiceno[0].'-'.end($cur_invoiceno);
                            }

                            //同步合同开票数据
                            $tongji_url =  CONTRACT_API . 'sync_ct_invoice.php?city=' . $this->user_city_py  . '###contractnum=' . $key . '###money='.$invoice_moneys.'###tax='.$invoice_taxs.'###invono='.$invoice_nos.'###type=2###date='.date('Y-m-d').'###note='.urlencode('经管系统自动同步');
                            api_log($this->city,$tongji_url,0,$this->uid,1);

                            unset($cur_invoiceno,$invoice_moneys,$invoice_taxs);
                        }
                    }
                }
            }

            //匹配结果
            $result["state"] = $i > 0?1:0;

            //匹配失败原因
            //1、会员记录的发票编号未填写；
            //2、会员记录已经被匹配过，并且该会员也没有申请通过的换发票操作；
            //3、会员的收据编号有误，无法找到与之匹配的会员。

            $result["msg"] .= "亲,共匹配了 ".$i ."条数据！<br />";
            $result["msg"] .= $return_error;
            die($result["msg"]);
        }
        else
        {
            $this->display("financial_import");
        }
    }

    /**
    +----------------------------------------------------------
    * 财务页面导出开票
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function exportInvoice()
    {
        //获取搜索条件
        $filter_sql = isset($_GET['Filter_Sql'])?trim($_GET['Filter_Sql']):'';

        Vendor('phpExcel.PHPExcel');
        $Exceltitle = '团立方监控开票表';//
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->setTitle(iconv("gbk//ignore","utf-8//ignore",$Exceltitle));
        $objActSheet->getDefaultRowDimension()->setRowHeight(16);//默认行宽
        $objActSheet->getDefaultColumnDimension()->setWidth(12);//默认列宽
        $objActSheet->getDefaultStyle()->getFont()->setName(iconv("gbk//ignore","utf-8//ignore",'宋体'));
        $objActSheet->getDefaultStyle()->getFont()->setSize(10);
        $objActSheet->getDefaultStyle()->getAlignment()->setWrapText(true);//自动换行
        $objActSheet->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objActSheet->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objActSheet->getRowDimension('1')->setRowHeight(40);
        $objActSheet->getRowDimension('2')->setRowHeight(26);

        $objActSheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $objActSheet->getStyle('A2:R2')->getFont()->setBold(true);

        $styleArray = array(
            'borders' => array (
                'allborders' => array (
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array ('argb' => 'FF000000'),//设置border颜色
                ),
            ),
        );

        $objActSheet->setCellValue('A1', iconv("gbk//ignore","utf-8//ignore",'团立方监管开票'));
        $objActSheet->setCellValue('A2', iconv("gbk//ignore","utf-8//ignore",'单据号'));
        $objActSheet->setCellValue('B2', iconv("gbk//ignore","utf-8//ignore",'购方名称'));
        $objActSheet->setCellValue('C2', iconv("gbk//ignore","utf-8//ignore",'购方税号'));
        $objActSheet->setCellValue('D2', iconv("gbk//ignore","utf-8//ignore",'购方地址电话'));
        $objActSheet->setCellValue('E2', iconv("gbk//ignore","utf-8//ignore",'购方开户行账号'));
        $objActSheet->setCellValue('F2', iconv("gbk//ignore","utf-8//ignore",'发票备注'));
        $objActSheet->setCellValue('G2', iconv("gbk//ignore","utf-8//ignore",'商品名称'));
        $objActSheet->setCellValue('H2', iconv("gbk//ignore","utf-8//ignore",'计量单位'));
        $objActSheet->setCellValue('I2', iconv("gbk//ignore","utf-8//ignore",'规格型号'));
        $objActSheet->setCellValue('J2', iconv("gbk//ignore","utf-8//ignore",'单价'));
        $objActSheet->setCellValue('K2', iconv("gbk//ignore","utf-8//ignore",'数量'));
        $objActSheet->setCellValue('L2', iconv("gbk//ignore","utf-8//ignore",'金额'));
        $objActSheet->setCellValue('M2', iconv("gbk//ignore","utf-8//ignore",'税率'));
        $objActSheet->setCellValue('N2', iconv("gbk//ignore","utf-8//ignore",'税额'));
        $objActSheet->setCellValue('O2', iconv("gbk//ignore","utf-8//ignore",'开票人'));
        $objActSheet->setCellValue('P2', iconv("gbk//ignore","utf-8//ignore",'复核人'));
        $objActSheet->setCellValue('Q2', iconv("gbk//ignore","utf-8//ignore",'收款人'));
        $objActSheet->setCellValue('R2', iconv("gbk//ignore","utf-8//ignore",'发票编号'));
        $objActSheet->mergeCells('A1:R1');

        $member_id = $_REQUEST["member_id"];

        if($member_id) {
            $sql = "select * from ERP_CARDMEMBER where ID IN($member_id) AND (INVOICE_STATUS = 5 OR CHANGE_INVOICE_STATUS = 2)";
        }
        else{
            $sql = "select * from ERP_CARDMEMBER where  (INVOICE_STATUS = 5 OR CHANGE_INVOICE_STATUS = 2) " . $filter_sql . ' AND CITY_ID = ' . $this->channelid;
        }

        $res = $this->model->query($sql);
        //var_dump($res);DIE;
        if(is_array($res)){
            $i = 3;
            foreach($res as $k => $r){
                #做替换
                $receiptno = str_replace(","," ",$r['RECEIPTNO']);
                $objActSheet->setCellValueExplicit('A'.$i, iconv("gbk//ignore","utf-8//ignore",$receiptno), PHPExcel_Cell_DataType::TYPE_STRING);
                $realname = str_replace(array("/","、",",","，")," ",$r['REALNAME']);
                $realname = iconv("gbk//ignore","utf-8//ignore",$realname);
                $objActSheet->setCellValue('B'.$i, $realname);
                $objActSheet->setCellValue('G'.$i, iconv("gbk//ignore","utf-8//ignore",'服务费'));
                $objActSheet->setCellValue('L'.$i, $r['PAID_MONEY']);
                $objActSheet->setCellValue('M'.$i, 6);

                //$objActSheet->getRowDimension($i)->setRowHeight(-1);
                $objActSheet->getRowDimension($i)->setRowHeight(24);
                $i++;
                if($objActSheet->getRowDimension($i)->getRowHeight() > 0){
                    $objActSheet->getRowDimension($i)->setRowHeight($objActSheet->getRowDimension($i)->getRowHeight()+20);
                }
            }
        }
        $objActSheet->getStyle('A1:R'.($i-1))->applyFromArray($styleArray);
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
        userLog()->writeLog('', $_SERVER["REQUEST_URI"], '开票:导出开票:成功', serialize($_FILES['upfile']));
        exit;
    }

    /**
    +----------------------------------------------------------
    * 报销确认
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function reimConfirm()
    {
        $city_channel = $this->channelid;
        //报销单Model
        $reim_list_model = D("ReimbursementList");
        //报销明细Model
        $reim_detail_model = D("ReimbursementDetail");
        //报销类型Model
        $reim_type_model = D("ReimbursementType");
        //项目成本model
        $project_cost_model = D("ProjectCost");

        $purchase_list_model = D("PurchaseList");
        $purchase_requisition_model = D("PurchaseRequisition");
        $warehouse_model = D("Warehouse");

        $uid = $_SESSION["uinfo"]["uid"];
        $reim_list_status = $reim_list_model->get_conf_reim_list_status();
        $reim_list_status_remark = $reim_list_model->get_conf_reim_list_status_remark();
        $reim_detail_status = $reim_detail_model->get_conf_reim_detail_status();
        $reim_list_type = $reim_type_model->get_reim_type();

        //返回结果集
        $result = array(
            'state'=>0,
            'msg'=>'',
        );

        $error_str = '';
        //选中报销单  进行报销确认
        if($_POST["reim_id"])
        {
            $reim_id = !empty($_POST["reim_id"]) ? $_POST["reim_id"] : 0;

            if(is_array($reim_id) && !empty($reim_id))
            {
                $fail_confirm = array();//确认失败的报销单的编号

                //垫资比例是否超出判断 --- 已经在提交的时候判断
                foreach ($reim_id as $key=>$val){

                    $loan_case = D("ProjectCase")->get_conf_case_Loan();
                    $loan_case_str = implode(",",array_keys($loan_case));

                    //1,2,14,15  采购   预算外费用   大宗采购    小蜜蜂采购 支付第三方费用   不做判断
                    $reim_sql = "select  C.projectname,A.case_id,A.type,sum(money) as money from erp_reimbursement_detail A left join erp_case B on A.case_id = B.id";
                    $reim_sql .= " left join erp_project C on B.project_id = C.id";
                    $reim_sql .= " where A.status = 0 AND A.type not in(1,2,14,15,16) and list_id = $val and B.scaletype in ($loan_case_str)";
                    $reim_sql .= " group by C.projectname,A.case_id,A.type";

                    $reim_data = M("erp_reimbursement_detail")->query($reim_sql);

                    foreach($reim_data as $k=>$v){
                        //现金带看奖（已发生金额=已发生金额+已签约客户）
                        if($v['TYPE'] == 7){
                            if($ret_loan_limit = is_overtop_payout_limit($v['CASE_ID'], $v['MONEY'],1)){
                                $error_str .= "报销编号为：$val,项目“" . $v['PROJECTNAME'] . "”超出垫资比例或超出费用预算（总费用>开票回款收入*付现成本率） " . "<br />";
                            }
                        }else{
                            if($ret_loan_limit = is_overtop_payout_limit($v['CASE_ID'], $v['MONEY'],0,1)){
                                $error_str .= "报销编号为：$val,项目“" . $v['PROJECTNAME'] . "”超出垫资比例或超出费用预算（总费用>开票回款收入*付现成本率） " . "<br />";
                            }
                        }

                    }
                }
                //如果有错误直接打回
                if(!empty($error_str)){
                    $result['msg'] = g2u($error_str);
                    die(json_encode($result));
                }
                //垫资比例是否超出判断 --- 结束

                foreach ($reim_id as $key=>$val)
                {
                    $reim_type = $reim_list_model->get_info_by_id($val,array("TYPE"));

                    $this->model->startTrans();
                    //财务确认报销
                    $list_up_num = $reim_list_model->sub_reim_list_to_completed($val, $uid);
                    $detail_up_num = $reim_detail_model->sub_reim_detail_to_completed($val);

                    //财务报销确认成功后 添加相应成本到成本记录中                    
                    if($list_up_num && $detail_up_num)
                    {
                        //根据报销单ID找到所有的报销明细
                        $search_arr = array("ID","CITY_ID","CASE_ID","BUSINESS_ID","INPUT_TAX","MONEY","STATUS",
                                            "ISFUNDPOOL","ISKF", "TYPE","FEE_ID","BUSINESS_PARENT_ID","PURCHASER_BEE_ID","DEPT_ID","NCTYPE");
                        $reim_detail_info = $reim_detail_model->get_detail_info_by_listid($val,$search_arr);


                        foreach ($reim_detail_info as $k => $v)
                        {
                            if($v["STATUS"] == $reim_detail_status["reim_detail_completed"] && in_array($reim_type[0]['TYPE'],array(3,4,6,9,10,12, 21, 22, 23, 24, 25))) {
                                //更新状态
                                $status_up_array = array();
                                switch (intval($reim_type[0]['TYPE'])) {
                                    case 3:
                                        $status_up_array['AGENCY_REWARD_STATUS'] = 5;
                                        break;
                                    case 4:
                                        $status_up_array['AGENCY_DEAL_REWARD_STATUS'] = 5;
                                        break;
                                    case 6:
                                        $status_up_array['PROPERTY_DEAL_REWARD_STATUS'] = 5;
                                        break;
                                    case 9:
                                        $status_up_array['AGENCY_REWARD_STATUS'] = 5;
                                        break;
                                    case 10:
                                    case 22:
                                        $status_up_array['AGENCY_DEAL_REWARD_STATUS'] = 5;
                                        break;
                                    case 12:
                                    case 23:
                                        $status_up_array['PROPERTY_DEAL_REWARD_STATUS'] = 5;
                                        break;
                                    case 21:
                                    case 24:
                                    case 25:
                                        $status_up_array['OUT_REWARD_STATUS'] = 5;
                                        break;
                                }

                               // $member_table = in_array($reim_type[0]['TYPE'],array(3,4,6))?"erp_cardmember":"erp_member_distribution";
                                $reim_status_up = M("erp_cardmember")->where("ID = {$v['BUSINESS_ID']}")->save($status_up_array);
                            }

                           if($v["STATUS"] == $reim_detail_status["reim_detail_completed"])//已报销的明细录入成本
                           {
                                switch ($v["TYPE"])
                                {
                                    case "1":
                                        $purchase_cotract_model = D("PurchaseContract");
                                        $res = $purchase_cotract_model->sub_contract_to_reimbursed_by_reim_listid($val);
                                        //echo $this->model->_sql(); die;
                                        $purchase_list_model->update_to_reimbursed_by_id($v["BUSINESS_ID"]);
                                        $prId = $purchase_list_model->where("ID = {$v['BUSINESS_ID']}")->getField('pr_id');

                                        //插入置换池原项目收益
                                        $dbPurchase = D('PurchaseList')->getPurchaseJoinReq($v["BUSINESS_ID"]);
                                        $returnIncome = D('PurchaseList')->insertDisplaceIncome($dbPurchase[0]);

                                    case "16":  // 支付第三方费用
                                    case "2":
                                        $benefits_model = D("Benefits");
                                        $bus_id = $reim_detail_model->get_detail_info_by_id($v["ID"],array("BUSINESS_ID"));
                                        $benefits_status_arr = $benefits_model->get_cost_status();
                                        $benefits_status = $benefits_status_arr["have_reimed"];
                                        $res = $benefits_model->
                                            update_info_by_id($bus_id[0]["BUSINESS_ID"],array("ISCOST"=>$benefits_status));
                                        break;
                                    case "7":
                                        $locale_granted_model = D("LocaleGranted");
                                        $res = $locale_granted_model->sub_granted_to_reimbursed_by_id($val);
                                        //sub_granted_to_reimbursed_by_id
                                        break;
                                    case "13":
                                        $cost_suplement_model = D("CostSupplement");
                                        $bus_id = $reim_detail_model->get_detail_info_by_id($v["ID"],array("BUSINESS_ID"));
                                        $cost_status_arr = $cost_suplement_model->get_cost_supplement_status();
                                        $cost_status = $cost_status_arr["have_reim"];
                                        $res = $cost_suplement_model->
                                            update_cost_supplement_info_by_ids($bus_id[0]["BUSINESS_ID"],array("STATUS"=>$cost_status));
                                        continue;
                                        break;
                                    case "14":
                                        $from_arr = $warehouse_model->get_conf_from();
                                        $satus_arr = $warehouse_model->get_conf_status();
                                        $purchase_cotract_model = D("PurchaseContract");
                                        $res = $purchase_cotract_model->sub_contract_to_reimbursed_by_reim_listid($val);

                                        //根据采购明细id找到明细
                                        $purchase_info = $purchase_list_model->get_purchase_list_by_id($v["BUSINESS_ID"]);

                                        if(!empty($purchase_info) && $purchase_info[0]['NUM'] > 0)
                                        {
                                            $ware_info = array();
                                            $ware_info['PL_ID'] = $purchase_info[0]['ID'];
                                            $ware_info['BRAND'] = $purchase_info[0]['BRAND'];
                                            $ware_info['MODEL'] = $purchase_info[0]['MODEL'];
                                            $ware_info['PRODUCT_NAME'] = $purchase_info[0]['PRODUCT_NAME'];
                                            $ware_info['FEE_ID'] = $purchase_info[0]['FEE_ID'];
                                            $ware_info['IS_KF'] = $purchase_info[0]['IS_KF'];
                                            $ware_info['INPUT_TAX'] = $v["INPUT_TAX"]; ///$purchase_info[0]['INPUT_TAX'];
                                            $ware_info['IS_FUNDPOOL'] = $purchase_info[0]['IS_FUNDPOOL'];
                                            $ware_info['ADDTIME'] = date('Y-m-d H:i:s');
                                            $ware_info['IS_FROM'] = $from_arr['bulk_purchase'];
                                            $ware_info['STATUS'] = $satus_arr['audited'];
                                            $ware_info['CITY_ID'] = $v['CITY_ID'];

                                            //根据规则计算入库单价
                                            $reim_total_cost = floatval($v["MONEY"]);
                                            $warehouse_num = floatval($purchase_info[0]['NUM']);
                                            $warehouse_unit_price = self::_get_avg_price($reim_total_cost, $warehouse_num);

                                            if($reim_total_cost / $warehouse_num != $warehouse_unit_price)
                                            {
                                                $high_price = $reim_total_cost - ($warehouse_num - 1 ) * $warehouse_unit_price;
                                                $ware_info['PRICE'] = $high_price;
                                                $ware_info['NUM'] = 1;
                                                //添加入库信息
                                                $cost_insert_id_high = $warehouse_model->add_warehouse_info($ware_info);
                                                $warehouse_num = $warehouse_num - $ware_info['NUM'];
                                            }

                                            if($warehouse_num > 0)
                                            {
                                                $ware_info['PRICE'] = $warehouse_unit_price;
                                                $ware_info['NUM'] = $warehouse_num;

                                                //添加入库信息
                                                $cost_insert_id = $warehouse_model->add_warehouse_info($ware_info);
                                            }

                                            if($cost_insert_id_high || $cost_insert_id)
                                            {
                                               $purchase_list_model->update_to_in_warehouse_by_id($v["BUSINESS_ID"]);
                                            }
                                        }
                                        continue;
                                        break;
                                    case "15":
                                         //小蜜蜂采购任务报销
                                         $bee_detail_model = D('PurchaseBeeDetails');
                                         //更改小蜜蜂任务明细状态
                                         $update_result = $bee_detail_model->where('ID='.$v['PURCHASER_BEE_ID'])->save(array('STATUS' => 2));
										 if($update_result) send_result_to_zk($v['PURCHASER_BEE_ID'],$this->channelid );//同步到众客
                                         break;
                                    case "17":
                                        // 分销中介后佣报销
                                        $dbResult = D('erp_commission_reim_detail')->where("POST_COMMISSION_ID = {$v['BUSINESS_ID']}")->save(array(
                                            'STATUS' => 3
                                        ));
                                        if ($dbResult !== false) {
                                            $remainPostComisAmount = D('ReimbursementList')->getRemainFxPostComisReimAmount($v['BUSINESS_PARENT_ID'], $v['BUSINESS_ID']);
                                            if ($remainPostComisAmount <= 0) {
                                                // 完全报销
                                                $dbResult = D('erp_post_commission')->where("ID = {$v['BUSINESS_ID']}")->save(array(
                                                    'POST_COMMISSION_STATUS' => 3
                                                ));
                                            } else {
                                                // 部分报销
                                                $dbResult = D('erp_post_commission')->where("ID = {$v['BUSINESS_ID']}")->save(array(
                                                    'POST_COMMISSION_STATUS' => 2
                                                ));
                                            }
                                        }
                                        if ($dbResult === false) {
                                            $fail_confirm[] = $val;
                                        }
                                        break;
                                    default :
                                        $res = 1;
                                        break;
                                }

                                $cost_info['CASE_ID'] = $v["CASE_ID"];                          //案例编号 【必填】       
                                $cost_info['ENTITY_ID'] = $val;       //报销申请单id                          
                                $cost_info['EXPEND_ID'] = $v["ID"];   //报销明细id                         
                                $cost_info['ORG_ENTITY_ID'] = $v["BUSINESS_PARENT_ID"];     //  业务实体 （项目id 。。。）
                                $cost_info['ORG_EXPEND_ID'] = $v["BUSINESS_ID"];                //业务明细编号 【必填】(采购单id)
                                $cost_info['FEE'] = $v["MONEY"];                               // 成本金额 【必填】 
                                $cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];            //操作用户编号 【必填】
                                $cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());        //发生时间 【必填】
                                $cost_info['ISFUNDPOOL'] = $v["ISFUNDPOOL"];                  //是否资金池（0否，1是） 【必填】
                                $cost_info['ISKF'] = $v["ISKF"];                             //成本类型ID 【必填】
                                $cost_info['INPUT_TAX'] = $v["INPUT_TAX"];                  //进项税 【选填】
                                $cost_info['FEE_ID'] = $v["FEE_ID"];                         //成本类型ID 【必填】
								if($v["DEPT_ID"])$cost_info['DEPT_ID'] = $v["DEPT_ID"]; 
								if( $v["NCTYPE"] )$cost_info['NCTYPE'] = $v["NCTYPE"]; 


                                switch ($reim_type[0]["TYPE"])
                                {
                                    case "1":
                                    case "15":
                                        $cost_info['EXPEND_FROM'] = 4;
                                        $cost_info['FEE_REMARK'] = "采购报销";
                                        break;
                                    case "2":
                                        $cost_info['EXPEND_FROM'] = 20;
                                        $cost_info['FEE_REMARK'] = "预算外费用报销";
                                        break;
                                    case "3":
                                        $cost_info['EXPEND_FROM'] = 7   ;
                                        $cost_info['FEE_REMARK'] = "电商会员中介佣金报销";
                                        break;
                                    case "4":
                                        $cost_info['EXPEND_FROM'] = 10;
                                        $cost_info['FEE_REMARK'] = "电商会员中介成交奖励报销";
                                        break;
                                    case "5":
                                        $cost_info['EXPEND_FROM'] = 13;
                                        $cost_info['FEE_REMARK'] = "电商会员置业顾问佣金报销";
                                        break;
                                    case "6":
                                        $cost_info['EXPEND_FROM'] = 16;
                                        $cost_info['FEE_REMARK'] = "电商会员置业顾问成交奖励报销";
                                        break;
                                    case "7":
                                        $cost_info['EXPEND_FROM'] = 24;
                                        $cost_info['FEE_REMARK'] = "现金发放报销";
                                        break;
                                    case "8":
                                        $cost_info['EXPEND_FROM'] = 16;
                                        $cost_info['FEE_REMARK'] = "带看奖报销";
                                        break;
                                    case "9":
                                    case "17":
                                        $cost_info['EXPEND_FROM'] = 7;
                                        $cost_info['FEE_REMARK'] = "分销会员中介佣金报销";
                                        break;
                                    case "10":
                                    case "22":
                                        $cost_info['EXPEND_FROM'] = 10;
                                        $cost_info['FEE_REMARK'] = "分销会员中介成交奖励报销";
                                        break;
                                    case "11":
                                        $cost_info['EXPEND_FROM'] = 13;
                                        $cost_info['FEE_REMARK'] = "分销会员置业顾问佣金报销";
                                        break;
                                    case "12":
                                    case "23":
                                        $cost_info['EXPEND_FROM'] = 16;
                                        $cost_info['FEE_REMARK'] = "分销会员置业顾问成交奖励报销";
                                        break;
                                    case "13":
                                        $cost_info['EXPEND_FROM'] = 26;
                                        $cost_info['FEE_REMARK'] = "成本填充报销";
                                        break;
                                    case '16':
                                        $cost_info['EXPEND_FROM'] = 33;
                                        $cost_info['FEE_REMARK'] = "支付第三方费用报销通过";
                                        break;
                                    case "21":
                                    case "24":
                                    case "25":
                                        $cost_info['EXPEND_FROM'] = 35;
                                        $cost_info['FEE_REMARK'] = '分销外部成交奖励报销';
                                    default :
                                        break;
                                }
                                if( $v["TYPE"] != "14" && $v["TYPE"] != "13")
                                {//var_dump($cost_info);
                                    $cost_insert_id = $project_cost_model->add_cost_info($cost_info);
                                }
								if($v["ISFUNDPOOL"]){
									$ruleArr = array('3','4','5','6','7','9','10','11','12','17','21','22','23','24','25');
									//待支付业务费处理
									$tprice = $v["MONEY"];
									$sql = "select * from ERP_FINALACCOUNTS t where CASE_ID='".$v['CASE_ID']."' and TYPE=1";
									$finalaccounts = M()->query($sql);
									$xgfee = $finalaccounts[0]['TOBEPAID_FUNDPOOL'] > $tprice ? $finalaccounts[0]['TOBEPAID_FUNDPOOL']-$tprice  : 0;
									if($xgfee!=$finalaccounts[0]['TOBEPAID_FUNDPOOL'] && $finalaccounts[0]['STATUS']==2 && in_array($v['TYPE'],$ruleArr)){
										D('Erp_finalaccounts')->where("CASE_ID='".$v['CASE_ID']."' and TYPE=1")->save(array('TOBEPAID_FUNDPOOL'=>$xgfee) );
									}
								}

                                if(!$cost_insert_id && $returnIncome!==false)
                                {
                                    $this->model->rollback();
                                    $fail_confirm[] = $val;
                                }
                                else
                                {
                                    $this->model->commit();
                                }
                            }

                        }
                    }
                    else
                    {
                        $this->model->rollback();
                        $fail_confirm[] = $val;
                    }
                }
                //var_dump($fail_confirm);die;
                if(is_array($fail_confirm) && !empty($fail_confirm))
                {
                    $fail_str = implode(",", $fail_confirm);
                    $result["state"] = 0;
                    $result["msg"] = "编号为 $fail_str 的报销申请确认失败，请重新尝试！";
                }
                else
                {
                    $result["state"] = 1;
                    $result["msg"] = "报销确认成功！";
                }
            }

            //日志
            userLog()->writeLog(implode(',', $_POST["reim_id"]), $_SERVER["REQUEST_URI"], '报销确认:确认报销:' . $result["msg"] , serialize($_POST));
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }
        //进入页面，显示报销单
        else
        {
            Vendor('Oms.Form');
            $form = new Form();
            $children = array(
                            array('报销明细',U('/Financial/reimDetail')),
                            array('关联借款',U('/Financial/loanMoney')),
                );
            $conf_where = "STATUS = ".$reim_list_status['reim_list_sub']." and CITY_ID = ".$city_channel;
            $form->initForminfo(176)
                ->where($conf_where)
                ->setMyField("TYPE", "LISTCHAR", array2listchar($reim_list_type))
                ->setMyField("STATUS", "LISTCHAR", array2listchar($reim_list_status_remark))
                ->setChildren($children);

            $form->SQLTEXT = '(select a.*,b.LOANMONEY  from ERP_REIMBURSEMENT_LIST a
        left join (select  APPLICANT,sum(UNREPAYMENT) as LOANMONEY from ERP_LOANAPPLICATION where STATUS IN(2,6) AND CITY_ID = '. $this->channelid . ' group by APPLICANT) b
        on a.APPLY_UID = b.APPLICANT )';

            //$form->DELCONDITION = "%STATUS%==0";
            $form->DELABLE = 0;
            $form->EDITABLE = 0;  // 不可编辑
            $form->SHOWCHECKBOX = -1;
            $form->GABTN = '<a id="reim_confirm" href="javascript:;" onclick="reim_confirm()" class="btn btn-info btn-sm">确认报销</a>'
                         . '<a id="reim_refuse" href="javascript:;" onclick="reim_refuse()" class="btn btn-info btn-sm">打回</a>  <a id="reim_confirm_time" href="javascript:;"   class="btn btn-info btn-sm" operate_type="edit_purchase">编辑确认时间</a>';

            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
            $formHtml = $form->getResult();
            $this->assign("form",$formHtml);
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
            $this->assign("paramUrl",$this->_merge_url_param);
            $this->display('financial_reimconfirm');
        }
    }

    /**
     * 财务打回报销单
     *@param none
     * return boolean TRUE成功\FALSE失败
     */
    public function reim_refuse()
    {
        $reim_list_model = D("ReimbursementList");
        $reim_detail_model = D("ReimbursementDetail");
        $reim_type_model = D("ReimbursementType");
		//项目成本model
        $project_cost_model = D("ProjectCost");

        //回退失败的报销单编号 数组
        $fail_reim_arr = array();

        //回退失败的报销单编号，连接后的字符串
        $fail_reim_str = "";

        $reim_list_id = !empty($_POST["reim_id"]) ? $_POST["reim_id"] : "";
        $amount = !empty($_POST["amount"]) ? $_POST["amount"] : "";
        $reim_list_id_str = implode(',', $reim_list_id);
        $reim_lists = $reim_list_model->field('ID,TYPE')->where('ID IN ('.$reim_list_id_str.')')->select();
        foreach ($reim_lists as $key=>$val){
            $money = 0-$amount[$key];
            $bee_reim = false;
            //如果是小蜜蜂采购流程则单独作驳回处理
            if ($val['TYPE']==15){
                $bee_reim = true;
            }

            if ($val['TYPE'] == 16) {
                // 撤回第三方支付费用报销申请
                $this->revertFundPoolCostApply($val, $fail_reim_arr);
            } else {
                $this->model->startTrans();
                $refuse_result = $reim_list_model->sub_reim_list_backto_apply($val['ID'], $money,$bee_reim);
                if(!$refuse_result){
                    $this->model->rollback();
                    $fail_reim_arr[] = $val['ID'];
                }else{
                    //小蜜蜂采购流程驳回处理
                    if ($bee_reim){
                        //获取所有报销详情
                        $reim_details = $reim_detail_model->where('LIST_ID='.$val['ID'])->select();
                        if (!empty($reim_details)){
                            $need_change_status = array();
                            $cost_insert_id  = true;
                            foreach ($reim_details as $v){
                                $need_change_status[] = $v['PURCHASER_BEE_ID'];
                                $cost_info = array();
                                $cost_info['CASE_ID'] = $v["CASE_ID"];                          //案例编号 【必填】
                                $cost_info['ENTITY_ID'] = $val['ID'];       //报销申请单id
                                $cost_info['EXPEND_ID'] = $v["ID"];   //报销明细id
                                $cost_info['ORG_ENTITY_ID'] = $v["BUSINESS_PARENT_ID"];     //  业务实体 （项目id 。。。）
                                $cost_info['ORG_EXPEND_ID'] = $v["BUSINESS_ID"];                //业务明细编号 【必填】(采购单id)
                                $cost_info['FEE'] = - $v["MONEY"];                               // 成本金额 【必填】
                                $cost_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];            //操作用户编号 【必填】
                                $cost_info['OCCUR_TIME'] = date("Y-m-d H:m:s",time());        //发生时间 【必填】
                                $cost_info['ISFUNDPOOL'] = $v["ISFUNDPOOL"];                  //是否资金池（0否，1是） 【必填】
                                $cost_info['ISKF'] = $v["ISKF"];                             //成本类型ID 【必填】
                                $cost_info['INPUT_TAX'] = $v["INPUT_TAX"];                  //进项税 【选填】
                                $cost_info['FEE_ID'] = $v["FEE_ID"];
                                $cost_info['EXPEND_FROM'] = 31;
                                $cost_info['FEE_REMARK'] = "采购报销申请驳回";//var_dump($cost_info);
                                $cost_insert_id = $project_cost_model->add_cost_info($cost_info);
                                if(!$cost_insert_id){
                                    //$this->model->rollback();
                                    break;
                                }

                            }
                            $model_bee_work = D('PurchaseBeeDetails');
                            $need_change_status = implode(',', $need_change_status);
                            $update = $model_bee_work->where("ID IN ($need_change_status)")->save(array('STATUS'=>3));

                            //var_dump( $need_change_status);


                            if (!$update || !$cost_insert_id){
                                $this->model->rollback();
                                $fail_reim_arr[] = $val['ID'];
                            }else{


                                send_result_to_zk($need_change_status,$this->channelid );//同步到众客
                            }
                        }
                    }
                    $dbResult = true;
                    // 如果是分销中介后佣报销，则将关联明细设置为未报销
                    if ($val['TYPE'] == 17) {
                        $dbResult = D('erp_commission_reim_detail')->where("REIM_LIST_ID = {$val['ID']}")->save(array('STATUS' => 1));
                    }
                    if ($dbResult !== false) {
                        $this->model->commit();
                    } else {
                        $this->model->rollback();
                    }

                }
            }

        }

        if(is_array($fail_reim_arr) && !empty($fail_reim_arr))
        {
            $fail_reim_str = implode(",",$fail_reim_arr);
            $result["state"] = 0;
            $result["msg"] = "编号为 ".$fail_reim_str ."的报销单回退失败！！";
        }
        else
        {
            $result["state"] = 1;
            $result["msg"] = "所有报销单均回退成功！！";
        }

        userLog()->writeLog(implode(',', $_POST["reim_id"]), $_SERVER["REQUEST_URI"], '报销确认:打回:' . $result["msg"] , serialize($_POST));
        $result["msg"] = g2u($result["msg"]);

        echo json_encode($result);
        exit;
    }


    /**
    +----------------------------------------------------------
    * 财务页面付款明细展示列表
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function payment()
    {
        Vendor('Oms.Form');
        $form = new Form();
        $form =  $form->initForminfo(126);
        //设置付款方式
        $member_pay = D('MemberPay');
        $member = D("Member");
        $where = "STATUS != 4";
        $pay_arr = $member_pay->get_conf_pay_type();
        $_conf_status_remark = $member->get_conf_all_status_remark();
        $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE)->where($where);
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->assign('form',$formHtml);
        $this->display('payment');
    }

    /**
    +----------------------------------------------------------
    * 财务页面报销明细展示列表
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function reimDetail()
    {
        if($_REQUEST['jinxianshui']){
			Vendor('Oms.Form');
			$form = new Form();
			$form->initForminfo(200);
			$form= $form->getResult();
            $this->assign('form',$form);

            $this->display('reim_details_tax');
			exit;
		}
		//报销申请单MODEL
        $reim_list_model = D('ReimbursementList');
        //报销MODEL
        $reim_detail_model = D('ReimbursementDetail');
        //报销类型
        $reim_type_model = D('ReimbursementType');

        //报销明细状态标志数组
        $reim_detail_status_remark = $reim_detail_model->get_conf_reim_detail_status_remark();

        //成本填充Model
        $cost_supplement_model = D("CostSupplement");
        $cost_supplement_status = $cost_supplement_model->get_cost_supplement_status();

        Vendor('Oms.Form');
		$form = new Form();
        $form2 = new Form();
		$form2->initForminfo(75);
		$form2->setMyField('DEPT_ID', 'EDITTYPE', '23', FALSE);//
		$form2->setMyField('DEPT_ID', 'LISTSQL', 'SELECT ID, DEPTFULLNAME, PARENTID FROM MV_ERP_DEPTFULLNAME  where ISVALID=-1', FALSE);
		 
		$form3 = new Form();
		$form3->initForminfo(137);
		$form3->setMyField('FEE_ID', 'EDITTYPE', '23', FALSE);
        $form3->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME, PARENTID FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
		$feeOptions = addslashes(u2g($form3->getSelectTreeOption('FEE_ID', '', -1)));
		 $this->assign('feeOptions',$feeOptions); 
		$deptOptions = addslashes(u2g($form2->getSelectTreeOption('DEPT_ID', '', -1)));
		 $this->assign('deptOptions',$deptOptions);
	 
		$nclist = D("Erp_nctype")->select();
		foreach($nclist as $key=>$one){
			$nctyeOptions .="<option value=\"".$one['ID']."\">".$one['TYPENAME']."</option>"; 

		}
 
		 $this->assign('nctyeOptions',$nctyeOptions); 
		 
        //根据报销单Id获取报销类型
        $reim_list_id = $_REQUEST["parentchooseid"];
        $reim_list_info = $reim_list_model->get_info_by_id($reim_list_id,array("TYPE"));
        $reim_list_type = $reim_list_info[0]["TYPE"];
        $this->assign('reim_list_type',$reim_list_type);
        switch($reim_list_type)
        {
            case "1":
            case "14":
                $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
                $uid = intval($_SESSION['uinfo']['uid']);
                $city = $this->channelid;
                $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
                $showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';

                $cond_where = "LIST_ID = '".$list_id."' AND STATUS != 4";
                $form->initForminfo(182)
                    ->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1')
                    ->where($cond_where);
                $form->SQLTEXT = <<<SQLTEXT
                (SELECT L.BRAND,
                      L.MODEL,
                      L.NUM,
                      L.NUM_LIMIT,
                      L.PRICE,
                      L.PRICE_LIMIT,
                      L.PRODUCT_NAME,
                      L.P_ID,
                      L.ADD_TIME,
                      getReimPurchaseContractId(L.CONTRACT_ID, B.ID) AS CONTRACT_ID,
                      L.COST_OCCUR_TIME,
                      L.PURCHASE_OCCUR_TIME,
                      D.*,
                      B.PROJECTNAME,
                      S.NAME SUPPLIER_NAME
               FROM ERP_REIMBURSEMENT_DETAIL D
               LEFT JOIN ERP_CASE A ON D.CASE_ID = A.ID
               LEFT JOIN ERP_PROJECT B ON A.PROJECT_ID = B.ID
               INNER JOIN ERP_PURCHASE_LIST L ON D.BUSINESS_ID = L.ID
               INNER JOIN ERP_PURCHASE_REQUISITION PR ON D.CASE_ID = PR.CASE_ID
               INNER JOIN ERP_SUPPLIER S ON S.ID = L.S_ID
               AND PR.ID = L.PR_ID)
SQLTEXT;

                //设置报销单类型
                $type_arr = $reim_type_model->get_reim_type();
                $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);

                //报销明细状态数组
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
                $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);

                //采购人
                $user_sql = "select ID,NAME from erp_users";
                $form->setMyField('P_ID', 'LISTSQL',$user_sql, FALSE);

                //费用类型
                $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                    . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);

                //合同号
//                $form->setMyField('CONTRACT_ID', 'LISTSQL', 'SELECT ID, CONTRACTID '
//                    . ' FROM ERP_CONTRACT', FALSE);

                //根据状态控制删除按钮是否显示
                $form->EDITABLE = 0;
                $form->ADDABLE = 0;
                $form->DELABLE = 0;
                $form->SHOWCHECKBOX = -1;

                $form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>编辑进项税</a>". "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>编辑报销明细</a>";
                $formHtml= $form->getResult();
                $this->assign('form',$formHtml);
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
                $this->assign('paramUrl',$paramUrl);
                $this->display('reim_details');
                break;
            case "2":
            case "16":
                $reim_detail_id = $reim_detail_model->get_detail_info_by_listid($reim_list_id, array("ID"));
                $reim_detail_id_str = "(";
                foreach ($reim_detail_id as $key => $val) {
                    $reim_detail_id_str .= $val["ID"] . ",";
                }
                $reim_detail_id_str = rtrim($reim_detail_id_str, ",");
                $reim_detail_id_str .= ")";
                //echo $reim_detail_id_str;die;
                $conf_where = "ID IN" . $reim_detail_id_str . " AND ISCOST != 4";
                if ($reim_list_type == 16) {
                    $form->initForminfo(203);
                    $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                    $form->SQLTEXT = <<<SQL
                        (SELECT A.ID,
                               A.MONEY AMOUNT,
                               A.STATUS ISCOST,
                               A.INPUT_TAX,
                               B.PROJECT_NAME,
                               B.SCALE_TYPE,
                               B.ADDTIME,
                               B.AUSER_ID NAME,
                               B.CASE_ID,
                               B.SUPPLIER,
                                P.contract,
                                B.attachment,
                                B.desript
                        FROM ERP_REIMBURSEMENT_DETAIL A
                        LEFT JOIN ERP_BENEFITS B ON A.BUSINESS_ID=B.ID
                        LEFT JOIN erp_project P ON P.id = B.project_id
                        WHERE A.TYPE={$reim_list_type})
SQL;
                } else {
                    $form->initForminfo(115);
                    $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                    $form->SQLTEXT = "(SELECT A.ID,A.MONEY AMOUNT,A.STATUS ISCOST,A.INPUT_TAX,B.PROJECT_NAME,B.SCALE_TYPE,B.ADDTIME,B.AUSER_ID NAME,B.CASE_ID, B.DESRIPT,B.SUPPLIER FROM ERP_REIMBURSEMENT_DETAIL A LEFT JOIN ERP_BENEFITS B ON A.BUSINESS_ID=B.ID WHERE A.TYPE=" . $reim_list_type . ")";
                }

                $form->where($conf_where);
                $form->GABTN = "";
//                $form->GCBTN = '<a id="j-sequence" class="j-showalert btn btn-info btn-sm"  href="javascript:;">排序</a>
//                                <a id="j-search" class="j-showalert btn btn-info btn-sm" href="javascript:;">搜索</a>
//                                <a class="j-refresh btn btn-warning btn-sm" onclick="window.location.reload();" href="javascript:;">刷新</a>';
                $form->EDITABLE = 0;
                $form->ADDABLE = 0;
                $form->DELABLE = 0;
                $form->SHOWDETAIL = 0;
                $form->setMyField("STATUS", "GRIDVISIBLE", "0")
                    ->setMyField("CASE_ID", "GRIDVISIBLE", '-1')
                    ->setMyField("PROJECT_NAME", "GRIDQUEUE", '2')
                    ->setMyField("NAME", "LISTSQL", "SELECT ID,NAME FROM ERP_USERS")
                    ->setMyField("ISCOST", "LISTCHAR", array2listchar($reim_detail_status_remark))
                    ->setMyField('SUPPLIER', 'EDITTYPE', 21, FALSE)
            ->setMyField('SUPPLIER', 'LISTSQL', 'SELECT ID,NAME FROM ERP_SUPPLIER', FALSE);
                $form->SHOWCHECKBOX = -1;
                $form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>编辑进项税</a>". "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>编辑报销明细</a>";
                $form = $form->getResult();
                $this->assign('form', $form);
                $this->assign('paramUrl', $paramUrl);
                $this->display('reim_details');
                break;
            case "3":
            case "4":
            case "5":
            case "6":
            case "7":
            case "21":
                $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
                $uid = intval($_SESSION['uinfo']['uid']);
                $city = $this->channelid;
                $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
                $showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';

                //根据LIST查询报销单类型
                $list_info = $reim_list_model->get_info_by_id($list_id, array('TYPE'));
                $reim_type = !empty($list_info[0]['TYPE']) ? intval($list_info[0]['TYPE']) : 0;

                //echo $reim_type;
                if(in_array($reim_type, array(3,4,5,6,21)) )
                {
                    $form_id = 177;
                }
                else if( $reim_type == 7)
                {
                    $form_id = 178;
                }

                $cond_where = "LIST_ID = '".$list_id."' AND STATUS != 4";
                $form = $form->initForminfo($form_id)->where($cond_where);
                $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                //如果报销类型是3,4,5,6 （获取统计数据）
                switch($reim_type){
                    case "3":
                        $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.AGENCY_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                        break;
                    case "4":
                        $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.AGENCY_DEAL_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                        break;
                    case "5":
                        $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.PROPERTY_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                        break;
                    case "6":
                        $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.PROPERTY_DEAL_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                        break;
                    case "21":
                        $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.OUT_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                        break;

                }

                if(in_array($reim_type,array(3,4,5,6,21)) && $showForm != 2){
                    $total_pro = array();
                    $total_pro = D()->query($sql);
                    $maxCount = count($total_pro);
                    $agency_reward = 0;
                    $agency_count = 0;
                    if($total_pro){
                        foreach($total_pro as $key=>$val){
                            $agency_reward = $agency_reward + $val['AGENCY_REWARD'];
                            $agency_count = $agency_count +  $val['AGENCY_COUNT'];
                        }
                        $total_pro[$maxCount]['PRJ_ID'] = 0;
                        $total_pro[$maxCount]['PRJ_NAME'] = '合计';
                        $total_pro[$maxCount]['AGENCY_REWARD'] = $agency_reward;
                        $total_pro[$maxCount]['AGENCY_COUNT'] = $agency_count;
                    }

                    $this->assign('total_pro',$total_pro);
                }

                //根据状态控制删除按钮是否显示
                //$form->DELCONDITION = '%STATUS% == 0';

                if(in_array($reim_type, array(3,4,5,6,21)) )
                {
                    $member_model = D('Member');

                    //设置会员来源
                    $source_arr = $member_model->get_conf_member_source_remark();
                    $form = $form->setMyField('SOURCE', 'LISTCHAR',
                            array2listchar($source_arr), FALSE);

                    //经办人
                    $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                    $form->setMyField("ROOMNO", "GRIDVISIBLE", '-1')
                        ->setMyField("HOUSETOTAL", "GRIDVISIBLE", '-1')
                        ->setMyField("HOUSEAREA", "GRIDVISIBLE", '-1');

                    switch($reim_type)
                    {
                        case 3:
                            $form->setMyField("AGENCY_REWARD", "GRIDVISIBLE", '-1');
                            break;
                        case 4:
                            $form->setMyField("AGENCY_DEAL_REWARD", "GRIDVISIBLE", '-1');
                            break;
                        case 5:
                            $form->setMyField("PROPERTY_REWARD", "GRIDVISIBLE", '-1');
                            break;
                        case 6:
                            $form->setMyField("PROPERTY_DEAL_REWARD", "GRIDVISIBLE", '-1');
                            break;
                        case 21:
                            $form->setMyField("OUT_REWARD", "GRIDVISIBLE", '-1');
                            break;
                    }
                    //设置证件类型
                    $certificate_type_arr = $member_model->get_conf_certificate_type();
                    $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR',
                            array2listchar($certificate_type_arr), FALSE);

                    //设置付款方式
                    $member_pay = D('MemberPay');
                    $pay_arr = $member_pay->get_conf_pay_type();
                    $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE);

                    //设置报销单类型
                    $type_arr = $reim_type_model->get_reim_type();
                    $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);

                    //报销明细状态数组
                    $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
                    $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);
                }
                else if( $reim_type == 7)
                {
                    //项目名称
                    $form = $form->setMyField('PRJ_ID', 'LISTSQL', 'SELECT ID, PROJECTNAME FROM ERP_PROJECT', TRUE);
                    $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                    $form->setMyField("ISKF", "GRIDVISIBLE", '0')
                        ->setMyField("TYPE", "GRIDVISIBLE", '0')
                        ->setMyField("STATUS", "GRIDVISIBLE", '0')
                        ->setMyField("ADD_UID", "GRIDVISIBLE", '-1');
                    $form->DELABLE = 0;

                    //设置报销单类型
                    $type_arr = $reim_type_model->get_reim_type();
                    $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);

                    //报销明细状态数组
                    $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
                    $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);
                }
                //控制删除按钮是否显示
                $form->EDITABLE = 0;
                $form->ADDABLE = 0;
                $form->DELABLE = 0;
				$form->SHOWCHECKBOX = -1;
				$form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>编辑进项税</a>". "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>编辑报销明细</a>";
                $formHtml= $form->getResult();
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
                $this->assign('form',$formHtml);
                $this->assign('paramUrl',$paramUrl);
                $this->display('reim_details');

                break;
            case "8":
                break;
            case "9":
            case "10":
            case "11":
            case "12":
            case "25":
            $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
            $uid = intval($_SESSION['uinfo']['uid']);
            $city = $this->channelid;
            $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
            $showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';

            //根据LIST查询报销单类型
            $list_info = $reim_list_model->get_info_by_id($list_id, array('TYPE'));
            $reim_type = !empty($list_info[0]['TYPE']) ? intval($list_info[0]['TYPE']) : 0;

            $cond_where = "LIST_ID = '".$list_id."' AND STATUS != 4";
            $form = $form->initForminfo(177)->where($cond_where);
            $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
            //如果报销类型是9,10,11,12（获取统计数据）
            switch($reim_type){
                case "9":
                    $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.AGENCY_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                    break;
                case "10":
                    $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.AGENCY_DEAL_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                    break;
                case "11":
                    $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.PROPERTY_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                    break;
                case "12":
                    $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.PROPERTY_DEAL_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;
                    break;
                case "25":
                    $sql = <<< TOTAL_SQL
    SELECT C.PRJ_ID, C.PRJ_NAME , SUM(C.OUT_REWARD) AS AGENCY_REWARD,COUNT(1) AS AGENCY_COUNT
    FROM ERP_REIMBURSEMENT_DETAIL D
    INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID AND D.CASE_ID = C.CASE_ID AND D.CITY_ID = C.CITY_ID
    WHERE LIST_ID = '{$list_id}' AND D.STATUS != 4
    GROUP BY C.PRJ_ID,C.PRJ_NAME
TOTAL_SQL;

            }

            if(in_array($reim_type,array(9,10,11,12,25)) && $showForm != 2){
                $total_pro = array();
                $total_pro = D()->query($sql);
                $maxCount = count($total_pro);
                $agency_reward = 0;
                $agency_count = 0;
                if($total_pro){
                    foreach($total_pro as $key=>$val){
                        $agency_reward = $agency_reward + $val['AGENCY_REWARD'];
                        $agency_count = $agency_count +  $val['AGENCY_COUNT'];
                    }
                    $total_pro[$maxCount]['PRJ_ID'] = 0;
                    $total_pro[$maxCount]['PRJ_NAME'] = '合计';
                    $total_pro[$maxCount]['AGENCY_REWARD'] = $agency_reward;
                    $total_pro[$maxCount]['AGENCY_COUNT'] = $agency_count;
                }

                $this->assign('total_pro',$total_pro);
            }

            //根据状态控制删除按钮是否显示
            //$form->DELCONDITION = '%STATUS% == 0';

            if(in_array($reim_type, array(9,10,11,12,25)) )
            {
                $member_model = D('Member');

                //设置会员来源
                $source_arr = $member_model->get_conf_member_source_remark();
                $form = $form->setMyField('SOURCE', 'LISTCHAR',
                    array2listchar($source_arr), FALSE);

                //经办人
                $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
                $form->setMyField("ROOMNO", "GRIDVISIBLE", '-1')
                    ->setMyField("HOUSETOTAL", "GRIDVISIBLE", '-1')
                    ->setMyField("HOUSEAREA", "GRIDVISIBLE", '-1');

                switch($reim_type)
                {
                    case 9:
                        $form->setMyField("AGENCY_REWARD", "GRIDVISIBLE", '-1');
                        break;
                    case 10:
                        $form->setMyField("AGENCY_DEAL_REWARD", "GRIDVISIBLE", '-1');
                        break;
                    case 11:
                        $form->setMyField("PROPERTY_REWARD", "GRIDVISIBLE", '-1');
                        break;
                    case 12:
                        $form->setMyField("PROPERTY_DEAL_REWARD", "GRIDVISIBLE", '-1');
                        break;
                    case 25:
                        $form->setMyField("OUT_REWARD", "GRIDVISIBLE", '-1');
                        break;
                }
                //设置证件类型
                $certificate_type_arr = $member_model->get_conf_certificate_type();
                $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR',
                    array2listchar($certificate_type_arr), FALSE);

                //设置付款方式
                $member_pay = D('MemberPay');
                $pay_arr = $member_pay->get_conf_pay_type();
                $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE);

                //设置报销单类型
                $type_arr = $reim_type_model->get_reim_type();
                $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);

                //报销明细状态数组
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
                $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);
            }

            //控制删除按钮是否显示
            $form->EDITABLE = 0;
            $form->ADDABLE = 0;
            $form->DELABLE = 0;
            $form->SHOWCHECKBOX = -1;
            $form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>编辑进项税</a>". "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>编辑报销明细</a>";
            $formHtml= $form->getResult();
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
            $this->assign('form',$formHtml);
            $this->assign('paramUrl',$paramUrl);
            $this->display('reim_details');

            break;
            case "22":
            case "23":
            case "24":
                $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
                $uid = intval($_SESSION['uinfo']['uid']);
                $city = $this->channelid;
                $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
                $showForm = isset($_GET['showForm']) ? strip_tags($_GET['showForm']) : '';

                //根据LIST查询报销单类型
                $list_info = $reim_list_model->get_info_by_id($list_id, array('TYPE'));
                $reim_type = !empty($list_info[0]['TYPE']) ? intval($list_info[0]['TYPE']) : 0;

                $arrFeeScale = D('ReimbursementDetail')->getFeeScalesByListID($list_id);
                $cond_where = "LIST_ID = '".$list_id."' AND STATUS!=4";
                $form = $form->initForminfo(179)->where($cond_where);
                $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                //单套收费标准
                $form->setMyField('TOTAL_PRICE', 'LISTCHAR', array2listchar($arrFeeScale['1']), FALSE);
                //中介佣金
                $form->setMyField('AGENCY_REWARD', 'EDITTYPE', 22, FALSE);
                $form->setMyField('AGENCY_REWARD', 'LISTCHAR', array2listchar($arrFeeScale['2']), FALSE);
                //外部成交奖励
                $form->setMyField('OUT_REWARD', 'EDITTYPE', 22, FALSE);
                $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($arrFeeScale['3']), FALSE);
                // 外部成交奖励
                 $form->setMyField('OUT_REWARD', 'LISTCHAR', array2listchar($arrFeeScale['3']), FALSE);

                //中介成交奖
                $form->setMyField('AGENCY_DEAL_REWARD', 'EDITTYPE', 22, FALSE);
                $form->setMyField('AGENCY_DEAL_REWARD', 'LISTCHAR', array2listchar($arrFeeScale['4']), FALSE);
                //置业成交奖金
                $form->setMyField('PROPERTY_DEAL_REWARD', 'EDITTYPE', 22, FALSE);
                $form->setMyField('PROPERTY_DEAL_REWARD', 'LISTCHAR', array2listchar($arrFeeScale['5']), FALSE);
                $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                $form->setMyField("MONEY", "GRIDVISIBLE", '0')
                        ->setMyField("TYPE", "GRIDVISIBLE", '0')
                        ->setMyField("ISFUNDPOOL", "GRIDVISIBLE", '0')
                        ->setMyField("ISKF", "GRIDVISIBLE", '0')
                        ->setMyField("STATUS", "GRIDVISIBLE", '0');
                switch ($reim_type)
                {
                    case 9:
                        $form->setMyField("AGENCY_REWARD", "GRIDVISIBLE", '-1');
                        break;
                    case 10:
                    case 22:
                        $form->setMyField("AGENCY_DEAL_REWARD", "GRIDVISIBLE", '-1');
                        $form->setMyField("MONEY", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISFUNDPOOL", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISKF", "GRIDVISIBLE", '-1');
                        break;
                    case 11:
                        $form->setMyField("PROPERTY_REWARD", "GRIDVISIBLE", '-1');
                        break;
                    case 12:
                    case 23:
                        $form->setMyField("PROPERTY_DEAL_REWARD", "GRIDVISIBLE", '-1');
                        $form->setMyField("MONEY", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISFUNDPOOL", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISKF", "GRIDVISIBLE", '-1');
                        break;
                    case 21:
                        $form->setMyField("MONEY", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISFUNDPOOL", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISKF", "GRIDVISIBLE", '-1');
                    case 24:
                        $form->setMyField("OUT_REWARD", "GRIDVISIBLE", '-1');
                        $form->setMyField("MONEY", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISFUNDPOOL", "GRIDVISIBLE", '-1');
                        $form->setMyField("ISKF", "GRIDVISIBLE", '-1');
                        break;
                    default :
                        break;
                }

                $form->EDITABLE = 0;
                $form->ADDABLE = 0;
                $form->DELABLE = 0;

                $member_model = D('Member');

                //设置会员来源
                $source_arr = $member_model->get_conf_member_source_remark();
                $form = $form->setMyField('SOURCE', 'LISTCHAR',
                        array2listchar($source_arr), FALSE);

                //经办人
                $form = $form->setMyField('ADD_UID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);

                //设置证件类型
                $certificate_type_arr = $member_model->get_conf_certificate_type();
                $form = $form->setMyField('CERTIFICATE_TYPE', 'LISTCHAR',
                        array2listchar($certificate_type_arr), FALSE);

                /***发票状态***/
                $conf_invoice_status = $member_model->get_conf_invoice_status_remark();
                if (in_array($reim_type, array(17, 22, 23, 24))) {
                    // 如果是从分销佣金管理报销的，则显示后佣的发票状态及回款状态
                    $form->setMyField('INVOICE_STATUS', 'LISTCHAR', array2listchar(array(
                        1 => '未开票',
                        2 => '部分开票',
                        3 => '完成开票'
                    )));
                } else {
                    $form = $form->setMyField('INVOICE_STATUS', 'LISTCHAR',
                        array2listchar($conf_invoice_status['INVOICE_STATUS']), FALSE);
                    $form->setMyField("PAYMENT_STATUS", "FORMVISIBILE", 0)
                        ->setMyField("PAYMENT_STATUS", "GRIDVISIBILE", 0);
                }


                //设置付款方式
                $member_pay = D('MemberPay');
                $pay_arr = $member_pay->get_conf_pay_type();
                $form = $form->setMyField('PAY_TYPE', 'LISTCHAR', array2listchar($pay_arr), TRUE);

                //设置报销单类型
                $type_arr = $reim_type_model->get_reim_type();
                $form = $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);

                //报销明细状态数组
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
                $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);
                $form->SHOWCHECKBOX = -1;
				$form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>编辑进项税</a>". "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>编辑报销明细</a>";
                $formHtml = $form->getResult();
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
                $this->assign('form',$formHtml);
                $this->assign('paramUrl',$paramUrl);
                $this->display('reim_details');
                break;
            //分销会员中介后佣
            case "17":
                $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
                $form->initforminfo(207);
                $form->SQLTEXT = "(SELECT m.REALNAME,
                                  m.HOUSETOTAL,
                                  m.TOTAL_PRICE,
                                  concatUnit(m.AGENCY_REWARD_AFTER, f1.Stype) AGENCY_REWARD_AFTER,
                                  m.PRJ_NAME,
                                  m.ID AS MEMBER_ID,
                                  m.MOBILENO,
                                  d.amount,
                                  d.percent,
                                  d.status,
                                  d.reim_list_id,
                                  d.reim_detail_id,
                                  d.id,
                                  c.INVOICE_STATUS,
                                  c.PAYMENT_STATUS,
                                  r.isfundpool,
                                  r.iskf,
                                  r.INPUT_TAX
                           FROM erp_commission_reim_detail d
                           LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
                           LEFT JOIN erp_reimbursement_detail r ON r.id = d.reim_detail_id
                           LEFT JOIN erp_cardmember m ON m.id = c.card_member_id
                           LEFT JOIN ERP_FEESCALE f1 ON f1.case_id = m.case_id
                           AND f1.amount = m.AGENCY_REWARD_AFTER
                           AND f1.SCALETYPE = 2
                           AND f1.ISVALID = -1
                           AND f1.MTYPE = 1)";
                $cond_where = "REIM_LIST_ID = '".$list_id."' AND STATUS!=4";
                $form->where($cond_where)
                    ->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1')
                    ->setMyField("PAYMENT_STATUS", "GRIDVISIBLE", '-1')
                    ->setMyField("PAYMENT_AMOUNT", "GRIDVISIBLE", '-1');
                $form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>编辑进项税</a>" . "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>编辑报销明细</a>";
                $form->FKFIELD="";
                $form->SHOWCHECKBOX = -1;
                $form->SHOWSEQUENCE = 0;
                $form->EDITABLE=0;
                $form->DELABLE=0;
                $formHtml= $form->getResult();
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
                $this->assign('form',$formHtml);
                $this->assign('paramUrl',$paramUrl);
                $this->display('reim_details');
                break;
            //项目下成本填充报销
            case "13":
                $form->initForminfo(183);
                $form->SQLTEXT = "(SELECT A.BUSINESS_ID ID,A.STATUS,A.LIST_ID,A.INPUT_TAX,"
                  . "B.CASE_ID,B.PRODUCT_NAME,B.BRAND,B.MODEL,B.PRICE,B.NUM,B.FEE_ID, B.IS_FUNDPOOL,B.IS_KF,B.PUR_DATE,B.SUP_TYPE,"
                    . "B.NUM*B.PRICE SUM_MONEY FROM ERP_REIMBURSEMENT_DETAIL A ,ERP_COST_SUPPLEMENT B"
                    . " WHERE A.BUSINESS_ID = B.ID and A.TYPE=13)";
                $cond_where = "LIST_ID = ".$reim_list_id;
                $form->where($cond_where);
                //是否资金池
                $form = $form->setMyField('IS_FUNDPOOL', 'LISTCHAR', array2listchar(array(1 => '是', 0 => '否')), FALSE);

                //是否扣非
                $form = $form->setMyField('IS_KF', 'LISTCHAR', array2listchar(array(1 => '是', 0 => '否')), FALSE);
                $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                //设置状态
                //状态标识
                $reim_status_remark = $reim_detail_model->get_conf_reim_detail_status_remark();
                $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_status_remark), FALSE);
                $form->SHOWCHECKBOX = -1;
				$form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>编辑进项税</a>" . "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>编辑报销明细</a>";;
                           // . "<a id='save_input_tax'href='javascript:;' class='btn btn-info btn-sm'>保存进项税</a>";
                $formHtml= $form->getResult();
                $this->assign('form',$formHtml);
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
                $this->assign('paramUrl',$paramUrl);
                $this->display('reim_details');
                break;
//            case 14:
//                break;
            case 15:
                $list_id = !empty($_GET['parentchooseid']) ? intval($_GET['parentchooseid']) : 0;
                $reim_details_status = $reim_detail_model->get_conf_reim_detail_status();
                $cond_where = "LIST_ID = '".$list_id."' AND STATUS != '".$reim_details_status['reim_detail_deleted']."'";
                $form = $form->initForminfo(198)->where($cond_where);
                $form->setMyField("INPUT_TAX", "GRIDVISIBLE", '-1');
                //是否资金池
                $form = $form->setMyField('ISFUNDPOOL', 'LISTCHAR', array2listchar(array(1 => '是', 0 => '否')), FALSE);
                //是否扣非
                $form = $form->setMyField('ISKF', 'LISTCHAR', array2listchar(array(1 => '是', 0 => '否')), FALSE);
                //$file1_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="1" href="javascript:void(0);">汇总表</a>';
               // $file2_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="2" href="javascript:void(0);">明细表</a>';
				$file1_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="1" href="javascript:void(0);">汇总表</a>';
				$file2_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="2" href="javascript:void(0);">明细表</a>';
                $file3_btn = '<a target="_blank" class="contrtable-link fedit J-export-file" data-file="3" href="javascript:void(0);">带看奖明细</a>';
                $form->CZBTN = $file1_btn.' '.$file2_btn.' '.$file3_btn;
                //设置报销明细类型
                $type_arr = $reim_type_model->get_reim_type();
                $form->setMyField('TYPE', 'LISTCHAR', array2listchar($type_arr), FALSE);
                //报销明细状态
                $reim_detail_statu_arr = $reim_detail_model->get_conf_reim_detail_status_remark();
                $form->setMyField('STATUS', 'LISTCHAR', array2listchar($reim_detail_statu_arr), FALSE);
                //进项税只读
                $form->setMyField('INPUT_TAX', 'READONLY', '-1', TRUE);
                //费用类型
                $form->setMyField('FEE_ID', 'LISTSQL', 'SELECT ID, NAME '
                        . ' FROM ERP_FEE WHERE ISVALID = -1 AND ISONLINE = 0', FALSE);
				$form->SHOWCHECKBOX = -1;
				$form->GABTN = "<a id='edit_input_tax' href='javascript:;' class='btn btn-info btn-sm'>编辑进项税</a>" . "<a id='save_input_reimDetail'href='javascript:;' class='btn btn-info btn-sm'>编辑报销明细</a>";;
                            //. "<a id='save_input_tax'href='javascript:;' class='btn btn-info btn-sm'>保存进项税</a>";
                $formHtml = $form->getResult();
                $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
                $this->assign('form', $formHtml);
                $this->display('reim_details');
        }
    }


    /**
    +----------------------------------------------------------
    * 财务页面关联借款展示列表
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function loanMoney()
    {
        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(209);

        //获取报销ID
        $listId = isset($_GET['parentchooseid'])?intval($_GET['parentchooseid']):0;
        $rlId = isset($_GET['RLID'])?intval($_GET['RLID']):0;

        //关联借款SQL
        $form->SQLTEXT = '(SELECT R.ID AS RLID,L.ID,L.CITY_ID,R.MONEY AS LOANMONEY,R.REIMID,L.PAYTYPE,T.NAME AS CITYNAME,P.ID AS PID,P.CONTRACT,L.STATUS,L.AMOUNT,L.REPAY_TIME,L.UNREPAYMENT,L.RESON,L.APPLICANT,U.NAME AS USERNAME,APPDATE FROM ERP_LOANAPPLICATION L
LEFT JOIN ERP_CITY T ON L.CITY_ID = T.ID
LEFT JOIN ERP_PROJECT P ON L.PID = P.ID
LEFT JOIN ERP_USERS U ON L.APPLICANT = U.ID
RIGHT JOIN ERP_REIMLOAN R ON L.ID = R.LOANID WHERE R.REIMID = ' . $listId . ')';

        //变更主键
        $form->PKFIELD = 'RLID';
        $form->PKVALUE = $rlId;

        $form->ADDABLE = 0;
        $form->EDITABLE = 0;
        $form->DELABLE = 0;
        $form->GABTN = "";
        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 将本次的检索结果显示在页面上
        $this->assign('form',$formHtml);
        $this->display("loan_money");
    }


    /**
    +----------------------------------------------------------
    * 导入银联数据进行对比 匹配到进行自动确认收款
    +----------------------------------------------------------
    * @param $file 要读取的文件
    +----------------------------------------------------------
    * @return $data
    +----------------------------------------------------------
    */
    public function importBankData()
    {

        //返回的数组格式
        $return = array(
            'status' => false,
            'msg' => '',
            'data' => null,
        );

        if($_FILES)
        {
            if($_FILES["upfile"]["size"] > 5000000)
            {
                $return['msg'] = g2u("文件过大，注意：文件大小不能超过 5M !");
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], '预收确认:导入银联数据:' . $return["msg"] . ':成功', serialize($_FILES['upfile']));
                die(json_encode($return));
            }

            $file = $_FILES["upfile"]["tmp_name"];

            Vendor('phpExcel.PHPExcel');
            Vendor('phpExcel.IOFactory.php');
            Vendor('phpExcel.Reader.Excel5.php');
            $PHPExcel = new PHPExcel();
            $PHPReader = new PHPExcel_Reader_Excel2007();

            if(!$PHPReader->canRead($file))
            {
                $PHPReader = new PHPExcel_Reader_Excel5();
                if(!$PHPReader->canRead($file))
                {
                    echo 'no Excel !';
                    userLog()->writeLog('', $_SERVER["REQUEST_URI"], '预收确认:导入银联数据:不存在相关记录:失败', serialize($_FILES['upfile']));
                    return ;
                }
            }

            /*****获取excel的数据*****/
            $objPHPExcel = $PHPReader->load($file,"UTF-8");
            $currentSheet = $objPHPExcel->getSheet(0);
            $allColumn = $currentSheet->getHighestColumn();

            /**取得一共有多少行*/
            $allRow = $currentSheet->getHighestRow();

            //判断支持最大记录数
            if($allRow>102){
                $return['msg'] = g2u('对不起，最大支持导入100条记录！');
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], '预收确认:导入银联数据:最大支持导入100条记录:失败', serialize($_FILES['upfile']));
                die(json_encode($return));
            }

            $paylist =  D("Erp_member_payment");
            $member_model = D('Member');

            //商户编号
            $shbh =  trim($currentSheet->getCellByColumnAndRow((ord(A) - 65),2)->getValue());
            $shbh = substr(trim($shbh),-15);

            $data = array();
            for($currentRow = 5; $currentRow <= $allRow; $currentRow++){
                //时间
                $date = $currentSheet->getCell("B".$currentRow)->getValue();
                $time = $currentSheet->getCell("C".$currentRow)->getValue();
                $tradetime = $date. ' ' .date('H:i:s', $time);
                //终端号
                $terminalnum = $currentSheet->getCell("D".$currentRow)->getValue();
                //交易金额
                $trademoney = $currentSheet->getCell("E".$currentRow)->getValue();
                //交易参考号
                $alljsnum = trim($currentSheet->getCell("H".$currentRow)->getValue());
                //6位检索号
                $jsnum = substr($alljsnum,-6);
                //交易类型
                $tradetype = $currentSheet->getCell("I".$currentRow)->getValue();
                $tradetype = iconv("utf-8","gbk",$tradetype);
                //卡号后4位
                $allcardnum = $currentSheet->getCell("J".$currentRow)->getValue();//卡号
                $cardnum = substr($allcardnum,-4);
                //发卡银行
                $cardbank = $currentSheet->getCell("K".$currentRow)->getValue();
                $cardbank = iconv("utf-8","gbk",$cardbank);
                //卡类型
                $cardtype = $currentSheet->getCell("L".$currentRow)->getValue();
                $cardtype = iconv("utf-8","gbk",$cardtype);

                //如果存在数据
                if($jsnum && $cardnum) {
                    $data[] = array("shbh" => $shbh,
                        "cardnum" => $cardnum,
                        "jsnum" => $jsnum,
                        "trademoney" => $trademoney,
                        "tradetime" => $tradetime,
                        "terminalnum" => $terminalnum,
                        "alljsnum" => $alljsnum,
                        "tradetype" => $tradetype,
                        "allcardnum" => $allcardnum,
                        "cardbank" => $cardbank,
                        "cardtype" => $cardtype,
                    );
                }
            }

            $i = 0;
            $error_str = '';

            //循环处理数据
            foreach($data as $key=>$val)
            {
                $res = $paylist->where("substr(CVV2,-4) = '{$val["cardnum"]}' and RETRIEVAL = '{$val["jsnum"]}' and TRADE_MONEY = {$val["trademoney"]} AND MERCHANT_NUMBER = '{$val["shbh"]}' AND STATUS = 0")
                    ->field(array("ID","MID"))->select();

                if(!$res){
                    $error_str .= "第" . ($key+1) . "行,未匹配到相关的付款明细, 请检查商户编号，卡号后四位，六位检索号，及金额是否填写正确~<br />";
                    continue;
                }

                //付款明细ID
                $id = $res[0]["ID"];
                //用户ID
                $mid = $res[0]["MID"];

                //先判断该付款明细的状态
                $ret_member_payment = M("erp_member_payment")
                    ->field("STATUS")->where("ID = " . $id)->find();

                if($ret_member_payment['STATUS'] == 1)
                {
                    $error_str .= "第" . ($key+1) . "行,该条付款明细，财务已经确认~<br />";
                    continue;
                }

                //如果处于未确认状态
                $this->model->startTrans();

                $sql = "update ERP_MEMBER_PAYMENT set STATUS = 1 where ID=" . $id;
                $r[$i] = $this->model->execute($sql);
                //修改对应会员状态

                //判断该会员是否有未确认的收款项
                $pay_info = $paylist->where("MID = " . $mid . " AND STATUS = 0")->field("ID")->select();
                $member_info = $member_model->get_info_by_ids($mid, array("UNPAID_MONEY"));

                $unpaidmoney = $member_info[0]['UNPAID_MONEY'];

                //该会员还存在未确认款项，状态修改为部分确认
                if ($pay_info[0]["ID"] || $unpaidmoney>0) {
                    $sql = "UPDATE ERP_CARDMEMBER SET FINANCIALCONFIRM = 2 WHERE ID = " . $mid;
                    $up_num = $this->model->execute($sql);
                }
                //该会员不存在未确认款项，状态修改为已确认
                else {
                    $sql = "UPDATE ERP_CARDMEMBER SET FINANCIALCONFIRM = 3 WHERE ID = " . $mid;
                    $up_num = $this->model->execute($sql);
                }

                //财务确认收益
                $res_income = $this->add_income_after_financial_confirm(2, 0, array(), array($id));

                if ($up_num && $r[$i] && $res_income) {
                    $i += 1;
                    $this->model->commit();
                    $conftime = date("Y-m-d H:i:s");
                    $confuser = $_SESSION["uinfo"]["uname"];
                    $dealres = 1;
                    //记录银联对账日志
                    $sql = "insert into ERP_BANKDATA_LOG(SHBH,TRADE_TIME,TERMINAL_NUM,TRADE_MONEY,"
                        . "JS_NUM,TRADE_TYPE,CARD_NUM,CARD_BANK,CARD_TYPE,CONFIRM_TIME,CONFIRM_USER,"
                        . "DEAL_RESULT,MID) values('" . $val["shbh"] . "',to_date('" . $val["tradetime"] . "','yyyy/mm/dd'),'" . $val["terminalnum"]
                        . "'," . $val["trademoney"] . ",'" . $val["alljsnum"] . "','" . $val["tradetype"] . "','" . $val["allcardnum"]
                        . "','" . $val["cardbank"] . "','" . $val["cardtype"] . "',to_date('" . $conftime . "','yyyy/mm/dd'),'" . $confuser . "',"
                        . $dealres . "','" . $mid . ")";
                    $up_num1 = $this->model->execute($sql);
                }
                else
                {
                    $this->model->rollback();
                    $conftime = "";
                    $confuser = "";
                    $dealres = 0;
                    $sql = "insert into ERP_BANKDATA_LOG(SHBH,TRADE_TIME,TERMINAL_NUM,TRADE_MONEY,"
                        . "JS_NUM,TRADE_TYPE,CARD_NUM,CARD_BANK,CARD_TYPE,CONFIRM_TIME,CONFIRM_USER,"
                        . "DEAL_RESULT,MID) values('" . $val["shbh"] . "','" . $val["tradetime"] . "','" . $val["terminalnum"]
                        . "'," . $val["trademoney"] . ",'" . $val["alljsnum"] . "','" . $val["tradetype"] . "','" . $val["allcardnum"]
                        . "','" . $val["cardbank"] . "','" . $val["cardtype"] . "','" . $conftime . "','" . $confuser . "',"
                        . $dealres . "','" . $mid . ")";
                    $this->model->execute($sql);

                    $error_str .= "第" . ($key+1) . "行,对比失败~<br />";
                    continue;
                }
            }

            //最终返回
            if($i>0){
                $return["status"] = 1;
                $return["msg"] = "亲，此次对比共匹配了 ".$i ."条数据<br />";
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], '预收确认:导入银联数据:' . $return["msg"] . ':成功', serialize($_FILES['upfile']));
            }
            else
            {
                $return["status"] = 0;
                $return["msg"] = "对不起，亲，对比失败<br />";
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], '预收确认:导入银联数据:' . $return["msg"] . ':失败', serialize($_FILES['upfile']));
            }
            if($error_str) {
                $return["msg"] .= "此次对比还存在如下问题：<br />" . $error_str;
                userLog()->writeLog('', $_SERVER["REQUEST_URI"], '预收确认:导入银联数据:' . $return["msg"] . ':失败', serialize($_FILES['upfile']));
            }
            $return["msg"] = g2u($return["msg"]);
            die(json_encode($return));

        }else{
            $this->display('financial_importbankdata');
        }
    }

    /**
    +----------------------------------------------------------
    * 退款工作流
    +----------------------------------------------------------
    * @param  none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function refundProgress()
    {
        $this->model = new model();
        vendor("Oms.Form");
        $form = new Form();
        $sql = "select b.maxstep,b.addtime,b.status,c.flowtype,e.name from erp_flowset a 
                left join erp_flows b on a.id= b.flowsetid 
                left join erp_flowtype c on a.flowtype = c.id
                left join erp_users e on e.id=b.adduser where c.id=10";
        $flow = $this->model->query($sql);
        $totalrecode = count($flow);
        $totalrecode = ($totalrecode > 0 )?$totalrecode : 0;
        $page = ($_REQUEST['page']>0)?intval($_REQUEST['page']):1;
        $pageSize = $_REQUEST['pageSize']?intval($_REQUEST['pageSize']):20;
        $pages = ceil($totalrecode/$pageSize);
        $pagehtml = $form->getPage($totalrecode,$page,$pageSize,$pages);
        $this->assign("flow",$flow);
        $this->assign("pagehtml",$pagehtml);
        $this->display("financial_refund");
    }


    /**
    +----------------------------------------------------------
    * 财务项目决算
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function prjSummary()
    {
       if($_REQUEST["save"]){//财务备案
           $sql = "update ERP_HOUSE set ISRECORD=2 where PRO_NAME='".$_SESSION["prjname"]."' and ISRECORD !=2";
           $res = $this->model->execute($sql);
           if($res){
               js_alert("备案成功","",0);
           }else{
               js_alert("该项目已经备案","",0);
           }

       }
       if($_REQUEST["flowid"]){
           $_SESSION["prjname"] = $_REQUEST["prjname"];
           Vendor('Oms.workflow');
           $flow = new workflow();
           $ID = $_REQUEST['flowid'];
           $html = $flow->createHtml($ID);
           $this->assign('html',$html);

       }
       Vendor('Oms.Form');
       $form = new Form();
       $form =  $form->initForminfo(131)->getResult();
       $this->assign('form',$form);
       $this->display('financial_prjsummary');
    }

    /**
    +----------------------------------------------------------
    * 业务开票
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function yw_invoice()
    {

        Vendor('Oms.Form');
        $form = new Form();

        $where = "IS_NEED_INVOICE = 1 AND CITY_ID = ". $this->channelid;
        $children = array(
                       array("开票记录",U("/Financial/InvoiceRecord&from=invoice",$this->_merge_url_param)),
                       array("回款记录",U("/Financial/refundRecords&from=invoice",$this->_merge_url_param)),
                   );
        $form->initForminfo(124);

        //设置formsql
        $form->SQLTEXT = "(SELECT A.ID,A.CASE_ID,A.CONTRACT_NO,A.SIGN_USER,A.COMPANY,A.STATUS,B.SCALETYPE,C.PROJECTNAME,C.ID PROJECT_ID,to_char(A.START_TIME,'YYYY-MM-DD') START_TIME,to_char(A.END_TIME,'YYYY-MM-DD')
                    END_TIME,to_char(A.PUB_TIME,'YYYY-MM-DD') PUB_TIME,A.CONF_TIME,A.MONEY,A.IS_NEED_INVOICE,A.CITY_ID,A.INCOME_TYPE
                    FROM ERP_INCOME_CONTRACT A
                    LEFT JOIN ERP_CASE B ON A.CASE_ID=B.ID
                    LEFT JOIN ERP_PROJECT C ON B.PROJECT_ID=C.ID
                    WHERE (C.PSTATUS=3 OR (C.ASTATUS=2 OR C.ASTATUS=4)) AND B.SCALETYPE !=7 AND B.SCALETYPE !=1)";

        //设置表单按钮
        $form->DELABLE = 0;
        $form->ADDABLE = 0;


        // 城市
        $form->setMyField('CITY_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_CITY', FALSE);

        //设置form内容
        $formHtml = $form ->setChildren($children)
            ->where($where)
            ->getResult();

        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->display("yw_invoice_refund");
    }

     /**
    +----------------------------------------------------------
    * 业务开票回款
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function yw_refund()
    {
        Vendor('Oms.Form');
        $form = new Form();

        $where = "CITY_ID = ".$this->channelid;

        $children = array(
                        array("回款记录",U("/Financial/refundRecords&from=refund",$this->_merge_url_param)),
                        array("开票记录",U("/Financial/InvoiceRecord&from=refund",$this->_merge_url_param)),
                    );

        $form->initForminfo(124);

        //设置formsql
        $form->SQLTEXT = "(SELECT A.ID,A.CASE_ID,A.CONTRACT_NO,A.SIGN_USER,A.COMPANY,A.STATUS,B.SCALETYPE,C.PROJECTNAME,C.ID PROJECT_ID,to_char(A.START_TIME,'YYYY-MM-DD') START_TIME,to_char(A.END_TIME,'YYYY-MM-DD')
                    END_TIME,to_char(A.PUB_TIME,'YYYY-MM-DD') PUB_TIME,A.CONF_TIME,A.MONEY,A.IS_NEED_INVOICE,A.CITY_ID,A.INCOME_TYPE
                    FROM ERP_INCOME_CONTRACT A
                    LEFT JOIN ERP_CASE B ON A.CASE_ID=B.ID
                    LEFT JOIN ERP_PROJECT C ON B.PROJECT_ID=C.ID
                    WHERE (C.PSTATUS=3 OR (C.ASTATUS=2 OR C.ASTATUS=4)) AND B.SCALETYPE !=7 AND B.SCALETYPE !=1)";


        $form->DELABLE = 0;
        $form->ADDABLE = 0;

        //获取业务类型
        $businessclass = M('erp_businessclass')->select();
        $scaletype_arr = array();
        foreach ($businessclass as $key=>$val) {
            $scaletype_arr[$val['ID']] = $val['YEWU'];
        }
        $form->setMyField('SCALETYPE', 'LISTCHAR', array2listchar($scaletype_arr), FALSE);
        $form->setMyField('CITY_ID','LISTSQL','SELECT ID,NAME FROM ERP_CITY WHERE ISVALID = -1', FALSE);

        $form = $form ->setChildren($children)
            ->setMyField("PROJECT_ID", "GRIDVISIBLE", "-1")
            ->setMyField("SCALETYPE", "GRIDVISIBLE", "-1")
            ->where($where)
            ->getResult();

        $this->assign('form',$form);
        $this->display("yw_refund");
    }

    /**
    +----------------------------------------------------------
    * 业务开票记录
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function InvoiceRecord()
    {
       $billing_model = D("BillingRecord");
       $contract_model = D("Contract");
       $billing_status_remark = $billing_model->get_invoice_status_remark();
       $contract_id = $_REQUEST["parentchooseid"];
       $contract_info = $contract_model->get_contract_info_by_id($contract_id,array("CASE_ID"));
       $case_id = $contract_info[0]["CASE_ID"];

       Vendor('Oms.Form');
       $form = new Form();
       $form->initForminfo(136);
       $form->orderField = "STATUS ASC";

        $from = isset($_GET['from'])?trim($_GET["from"]):'';
        $act = isset($_GET['act'])?trim($_GET["act"]):'';

        //获取发票状态值
        $invoice_status = D("BillingRecord")->get_invoice_status();

        $invoice_show = '';
        if($from == 'invoice') {
            switch ($act) {
                case 'change_invoice':
                    $form->GABTN = '<a onclick="confirm_change_invoice()" href="javascript:;" class="btn btn-info btn-sm">确认换票</a>';
                    $invoice_show = $invoice_status['change_vote'];
                    break;
                case 'refund_invoice':
                    $form->GABTN = '<a onclick="confirm_refund_invoice()" href="javascript:;" class="btn btn-info btn-sm">确认退票</a>';
                    $invoice_show = $invoice_status['refund_vote'];
                    break;
                default:
                    $form->GABTN = '<a onclick="save_data()" href="javascript:;" class="btn btn-info btn-sm">保存数据</a>'
                        . '<a onclick="do_invoice()" href="javascript:;" class="btn btn-info btn-sm">确认开票</a>';
                    $invoice_show = "3,4,6,7,8,9";
                    break;
            }
        }

        //如果是回款过来
        if($from == 'refund')
            $invoice_show = "3,4,6,7,8,9";

        $form->EDITABLE = 0;
        $form->DELABLE = 0;
        $form->ADDABLE = 0;
        $where = "STATUS IN($invoice_show) AND CASE_ID = '" . $case_id . "'";

        $form = $form->where($where)
            ->setMyField("TAX", "GRIDVISIBLE", "-1")
            ->setMyField("STATUS", "LISTCHAR", array2listchar($billing_status_remark))
            ->setMyField("INVOICE_MONEY", "FIELDMEANS", "税费合计");

//        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
        $formHtml = $form->getResult(); //显示该硬广下对应的开票记录
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->assign("case_id", $case_id);
        //行为
        $this->assign("act", $act);
        $this->assign('form', $formHtml);

        $this->assign('paramUrl', $this->_merge_url_param);
        $this->display('financial_invoice_records');
    }

    /**
    +----------------------------------------------------------
     * 业务换票
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function business_change_invoice(){
        Vendor('Oms.Form');
        $form = new Form();

        $form->initForminfo(124);

        $formsql  = <<<INVOICE_SQL
        (SELECT F.*,C.SCALETYPE,P.PROJECTNAME,P.ID PROJECT_ID FROM
        (
        SELECT DISTINCT B.ID,B.CASE_ID,B.CONTRACT_NO,B.SIGN_USER,B.COMPANY,B.STATUS,to_char(B.START_TIME,'YYYY-MM-DD') START_TIME,to_char(B.END_TIME,'YYYY-MM-DD') END_TIME,to_char(B.PUB_TIME,'YYYY-MM-DD') PUB_TIME,B.CONF_TIME,B.MONEY,B.CITY_ID FROM ERP_BILLING_RECORD A INNER JOIN ERP_INCOME_CONTRACT B ON A.CONTRACT_ID = B.ID WHERE A.STATUS = %d
        ) F
        LEFT JOIN ERP_CASE C ON F.CASE_ID=C.ID
        LEFT JOIN ERP_PROJECT P ON C.PROJECT_ID = P.ID
        WHERE (P.PSTATUS=3 OR (P.ASTATUS=2 OR P.ASTATUS=4)) AND C.SCALETYPE !=7 AND C.SCALETYPE !=1)
INVOICE_SQL;

        //退票状态
        $invoice_status = D("BillingRecord")->get_invoice_status();
        $formsql = sprintf($formsql,$invoice_status['change_vote']);

        //设置formsql
        $form->SQLTEXT = $formsql;

        $children = array(
            array("申请换票记录",U("/Financial/InvoiceRecord&from=invoice&act=change_invoice",$this->_merge_url_param)),
        );

        $where = " CITY_ID = ". $this->channelid;

        //设置表单按钮
        $form->DELABLE = 0;
        $form->ADDABLE = 0;

        // 城市
        $form->setMyField('CITY_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_CITY', FALSE);

        //设置form内容
        $formHtml = $form ->setChildren($children)
            ->where($where)
            ->getResult();

        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->display("business_change_invoice");
    }

    /**
    +----------------------------------------------------------
     * 业务退票
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function business_refund_invoice(){
        Vendor('Oms.Form');
        $form = new Form();

        $form->initForminfo(124);

$formsql  = <<<INVOICE_SQL
        (SELECT F.*,C.SCALETYPE,P.PROJECTNAME,P.ID PROJECT_ID FROM
        (
        SELECT DISTINCT B.ID,B.CASE_ID,B.CONTRACT_NO,B.SIGN_USER,B.COMPANY,B.STATUS,to_char(B.START_TIME,'YYYY-MM-DD') START_TIME,to_char(B.END_TIME,'YYYY-MM-DD') END_TIME,to_char(B.PUB_TIME,'YYYY-MM-DD') PUB_TIME,B.CONF_TIME,B.MONEY,B.CITY_ID FROM ERP_BILLING_RECORD A INNER JOIN ERP_INCOME_CONTRACT B ON A.CONTRACT_ID = B.ID WHERE A.STATUS = %d
        ) F
        LEFT JOIN ERP_CASE C ON F.CASE_ID=C.ID
        LEFT JOIN ERP_PROJECT P ON C.PROJECT_ID = P.ID
        WHERE (P.PSTATUS=3 OR (P.ASTATUS=2 OR P.ASTATUS=4)) AND C.SCALETYPE !=7 AND C.SCALETYPE !=1)
INVOICE_SQL;

        //退票状态
        $invoice_status = D("BillingRecord")->get_invoice_status();
        $formsql = sprintf($formsql,$invoice_status['refund_vote']);

        //设置formsql
        $form->SQLTEXT = $formsql;

        //设置表单按钮
        $form->DELABLE = 0;
        $form->ADDABLE = 0;

        $where = " CITY_ID = ". $this->channelid;
        // 城市列表
        $form->setMyField('CITY_ID', 'LISTSQL', 'SELECT ID, NAME FROM ERP_CITY', FALSE);

        $children = array(
            array("申请退票记录",U("/Financial/InvoiceRecord&from=invoice&act=refund_invoice",$this->_merge_url_param)),
        );

        //设置form内容
        $formHtml = $form ->setChildren($children)
            ->where($where)
            ->getResult();

        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->display("business_refund_invoice");
    }

    /**
    +----------------------------------------------------------
     * 确认换票
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function confirm_change_invoice(){

        /***保留多张票一起换票的入口***/

        //返回结果集
        $result = array(
            'status' => false,
            'msg'=>'',
            'data'=>null,
        );

        $invoice_ids = $_POST['invoiceid'];
        $invoice_time = $_POST['invoice_time'];
        $invoice_no = $_POST['invoice_no'];
		$invoice_no_old = $_POST['invoice_no_old'];
        $invoice_biz_type = $_POST['invoice_biz_type'];

        $error_str = '';

        if(count($invoice_ids)<1) {
            $result['msg'] = g2u('亲,请至少选择一条记录！');
            die(@json_encode($result));
        }

        foreach($invoice_ids as $key=>$val){
            if(!$invoice_time[$key] || !$invoice_no[$key] || !$invoice_biz_type[$key])
                $error_str .= "第" . ($key+1) . "条，开票时间、发票编号或者发票类型未填写!<br />";
			if($invoice_no_old[$key] != $invoice_no[$key]){
				$sqll = "select * from  ERP_BILLING_RECORD  A left join ERP_CASE B on A.CASE_ID=B.ID left join ERP_PROJECT C on C.ID=B.PROJECT_ID  where A.INVOICE_NO='".$invoice_no[$key]."' and C.CITY_ID=".$this->channelid;
				$resss = M()->query($sqll);
				if($resss){
					$error_str .= "第" . ($key+1) . "条， 发票编号在当前城市下已存在!<br />";
				}
			}
        }

        if($error_str)
        {
            $result['msg'] = g2u($error_str);
            die(json_encode($result));
        }
 
        $case_model = D("ProjectCase");
        $billing_model = D("BillingRecord");
        $contract_model = D("Contract");

        //发票状态
        $invoice_status = D("BillingRecord")->get_invoice_status();

        //业务
        $this->model->startTrans();

        load("@.contract_common");
        foreach($invoice_ids as $key=>$val)
        {
            //获取开票信息
            $billing_info = $billing_model->get_info_by_id($val,array("INVOICE_TIME","INVOICE_NO","CONTRACT_ID","CASE_ID", "INVOICE_BIZ_TYPE"));

            $case_id =  $billing_info[0]['CASE_ID'];
            $contract_id =  $billing_info[0]['CONTRACT_ID'];

            $contract_info =  $contract_model->get_contract_info_by_id($contract_id,array("CONTRACT_NO"));

            /*-------------------插入负值记录----------------------*/

            //获取业务类型
            $case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
            $scale_type = $case_info[0]["SCALETYPE"];

            //新增负收益
            $is_positive = false;
            $res_income_negative = $this->add_income_after_financial_invoice(array($contract_id),$scale_type,$val,$is_positive,true);

            if(!$res_income_negative)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }

            //更新换发票的状态
            $up_val['STATUS'] = $invoice_status['have_change_voted'];
            $update_id = $this->model->table("erp_billing_record")->where("ID = " . $val)->save($up_val);

            if(!$update_id)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }

            //根据发票ID找到发票信息
            $billing_info = $billing_model->get_info_by_id($val,array("INVOICE_MONEY","TAX","INVOICE_NO","REMARK","INVOICE_TIME", "INVOICE_BIZ_TYPE"));

            $format_date = oracle_date_format($billing_info[0]["INVOICE_TIME"]);
            $data_arr["date"] = $format_date?$format_date:$billing_info[0]["INVOICE_TIME"];
            $data_arr["money"] = -$billing_info[0]["INVOICE_MONEY"];

            //发票税率
            $taxrate = get_taxrate_by_citypy($this->user_city_py);
            $data_arr["tax"] = round((0-$billing_info[0]["INVOICE_MONEY"])/(1+$taxrate) * $taxrate,2);

            $data_arr["invono"] = $billing_info[0]["INVOICE_NO"];
            //硬广 1  非硬广 2
//            $data_arr["type"] = $scale_type==3?1:2;
            $data_arr["type"] = $billing_info[0]["INVOICE_BIZ_TYPE"];
            $data_arr["note"] = urlencode($billing_info[0]["REMARK"]);
            $data_arr["city"] = $this->user_city_py;
            $data_arr["contractnum"] = $contract_info[0]["CONTRACT_NO"];

            //同步开票数据到合同系统
            $save_result_negative = saveInvoice2Con($data_arr);

            if(!$save_result_negative)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }
            /*-------------------插入负值记录----------------------*/


            /*-------------------插入更新记录----------------------*/

            $insert_arr = $this->model->table("erp_billing_record")
                ->field("CASE_ID,CONTRACT_ID,INVOICE_NO,USER_ID,CREATETIME,REMARK,APPLY_USER_ID,TAX,STATUS,INVOICE_TIME,INVOICE_TYPE,FLOW_ID,INVOICE_MONEY,INVOICE_CLASS,INVOICE_BIZ_TYPE")
                ->where("ID = " .  $val)
                ->find();

            $insert_arr['INVOICE_MONEY'] = $insert_arr['INVOICE_MONEY'];
            $insert_arr['REMARK'] = '编号：' . $val . '申请换发票';
            $insert_arr['INVOICE_NO'] = $invoice_no[$key];
            $insert_arr['INVOICE_TIME'] = $invoice_time[$key];
            $insert_arr['INVOICE_BIZ_TYPE'] = $invoice_biz_type[$key];
            $insert_arr['STATUS'] =  $invoice_status['have_invoiced'];
            unset($insert_arr['NUMROW']);

            $insert_id = $this->model->table("erp_billing_record")->add($insert_arr);

            if(!$insert_id)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }

            //如果是分销会员开票 修改分销会员的状态并填写发票号码
            $up_dis_member_success = true;

            if( $scale_type == 2 )
            {
                /*$member_distribution_model = D("MemberDistribution");

                $cond_where = "RELATE_INVOICE_ID = ".$val;

                $update_arr['INVOICE_STATUS'] = 2;
                $update_arr['RELATE_INVOICE_ID'] = $insert_id;
                $update_arr['INVOICE_NO'] = $invoice_no[$key];
				
                //更新分销状态
                $up_dis_member_num =$member_distribution_model->update_info_by_cond($update_arr, $cond_where);*/
				$temp = array();
				$temp['BILLING_RECORD_ID'] =  $insert_id;
				$temp['INVOICE_NO'] =  $invoice_no[$key];

				
				$up_dis_member_num = M("Erp_commission_invoice_detail")->where("BILLING_RECORD_ID=$val")->save($temp);

                if(!$up_dis_member_num)
                    $up_dis_member_success = false;
            }

            if(!$up_dis_member_success)
            {
                //$this->model->rollback();
                //die(@json_encode($result));
            }

            //新增收益
            $res_income_positive = $this->add_income_after_financial_invoice(array($contract_id),$scale_type,$insert_id,true,true);

            if(!$res_income_positive)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }

            //根据发票ID找到发票信息
            $billing_info = $billing_model->get_info_by_id($insert_id,array("INVOICE_MONEY","TAX","INVOICE_NO","REMARK","INVOICE_TIME", "INVOICE_BIZ_TYPE"));

            $format_date = oracle_date_format($billing_info[0]["INVOICE_TIME"]);
            $data_arr["date"] = $format_date?$format_date:$billing_info[0]["INVOICE_TIME"];
            $data_arr["money"] = $billing_info[0]["INVOICE_MONEY"];

            //发票税率
            $taxrate = get_taxrate_by_citypy($this->user_city_py);
            $data_arr["tax"] = round($billing_info[0]["INVOICE_MONEY"]/(1+$taxrate) * $taxrate,2);

            $data_arr["invono"] = $billing_info[0]["INVOICE_NO"];
            //硬广 1  非硬广 2
//            $data_arr["type"] = $scale_type==3?1:2;
            $data_arr["type"] = $billing_info[0]["INVOICE_BIZ_TYPE"];
            $data_arr["note"] = urlencode("申请换发票");
            $data_arr["city"] = $this->user_city_py;
            $data_arr["contractnum"] = $contract_info[0]["CONTRACT_NO"];

            $save_result_positive = saveInvoice2Con($data_arr);//同步开票数据到合同系统

            if(!$save_result_positive)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }
            /*-------------------插入更新记录----------------------*/
        }

        $this->model->commit();
        $result["status"] = true;
        $result["msg"] = g2u("换票成功！");
        die(json_encode($result));

    }

    /**
    +----------------------------------------------------------
     * 确认退票
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function confirm_refund_invoice(){
        /***保留多张票一起退票的入口***/

        //返回结果集
        $result = array(
            'status' => false,
            'msg'=>'',
            'data'=>null,
        );

        $invoice_ids = $_POST['invoiceid'];

        if(count($invoice_ids)<1) {
            $result['msg'] = g2u('请至少选择其中一条记录！');
            die(@json_encode($result));
        }

        $case_model = D("ProjectCase");
        $billing_model = D("BillingRecord");
        $contract_model = D("Contract");

        //发票状态
        $invoice_status = D("BillingRecord")->get_invoice_status();

        //业务
        $this->model->startTrans();

        load("@.contract_common");
        foreach($invoice_ids as $key=>$val)
        {
            //获取开票信息
            $billing_info = $billing_model->get_info_by_id($val,array("INVOICE_TIME","INVOICE_NO","CONTRACT_ID","CASE_ID"));

            $case_id =  $billing_info[0]['CASE_ID'];
            $contract_id =  $billing_info[0]['CONTRACT_ID'];

            $contract_info =  $contract_model->get_contract_info_by_id($contract_id,array("CONTRACT_NO"));

            /*------------------更新记录----------------------*/

            //获取业务类型
            $case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
            $scale_type = $case_info[0]["SCALETYPE"];

            //新增负收益
            $is_p = false;
            $res_income_negative = $this->add_income_after_financial_invoice(array($contract_id),$scale_type,$val,$is_p,true);

            if(!$res_income_negative)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }

            //如果是分销业务
            $up_dis_member_success = true;
            if( $scale_type == 2 )
            {
               /*$member_distribution_model = D("MemberDistribution");

                $cond_where = "RELATE_INVOICE_ID = ".$val;
                $update_arr['INVOICE_STATUS'] = 4;

                //更新分销状态
                $up_dis_member_num =$member_distribution_model->update_info_by_cond($update_arr, $cond_where);*/

				$temp = array();
				//$temp['BILLING_RECORD_ID'] =  $insert_id;
				//$temp['INVOICE_NO'] =  $invoice_no[$key];
				$temp['INVOICE_STATUS'] = 9;

				
				$up_dis_member_num = M("Erp_commission_invoice_detail")->where("BILLING_RECORD_ID=$val")->save($temp);

                if(!$up_dis_member_num)
                    $up_dis_member_success = false;
				if($up_dis_member_num){
					$comm_invo_detail = M("Erp_commission_invoice_detail")->where("BILLING_RECORD_ID=$val")->select();
					foreach($comm_invo_detail as $cval){
						$POST_COMMISSION_ID = $cval['POST_COMMISSION_ID'];
						$comlist = M("Erp_commission_invoice_detail")->where("POST_COMMISSION_ID=$POST_COMMISSION_ID")->select();
						$invoice_nums = 0;
						$b_counts = count($comlist);
						$tttemp = array();
						foreach($comlist as $vall){
							if($vall['INVOICE_STATUS']==3)
								$invoice_nums++;
							
						}
						if($invoice_nums==0 ){//未开票
							$tttemp['INVOICE_STATUS'] = 1;
							
						}elseif($invoice_nums<$b_counts){//部分开票
							$tttemp['INVOICE_STATUS'] = 2;
						}elseif($invoice_nums==$b_counts){//开票
							$tttemp['INVOICE_STATUS'] = 3;
						}
						$ressss = M("Erp_post_commission")->where("ID=$POST_COMMISSION_ID")->save($tttemp);
						if(!$ressss)
						$up_dis_member_success2 = false;
					}

					

				}

            }

            if(!$up_dis_member_success)
            {
               // $this->model->rollback();
               // die(@json_encode($result));
            }
			 if(!$up_dis_member_success2)
            {
               // $this->model->rollback();
                //die(@json_encode($result));
            }

            //更新状态
            $up_val['STATUS'] = $invoice_status['have_refund_voted'];
            $update_id = $this->model->table("erp_billing_record")->where("ID = " . $val)->save($up_val);

            if(!$update_id)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }

            //根据发票ID找到发票信息
            $billing_info = $billing_model->get_info_by_id($val,array("INVOICE_MONEY","TAX","INVOICE_NO","REMARK","INVOICE_TIME", "INVOICE_BIZ_TYPE"));

            $format_date = oracle_date_format($billing_info[0]["INVOICE_TIME"]);
            $data_arr["date"] = $format_date?$format_date:$billing_info[0]["INVOICE_TIME"];
            $data_arr["money"] = -$billing_info[0]["INVOICE_MONEY"];

            //发票税率
            $taxrate = get_taxrate_by_citypy($this->user_city_py);
            $data_arr["tax"] = round((0-$billing_info[0]["INVOICE_MONEY"])/(1+$taxrate) * $taxrate,2);

            $data_arr["invono"] = $billing_info[0]["INVOICE_NO"];
            //硬广 1  非硬广 2
//            $data_arr["type"] = $scale_type==3?1:2;
            $data_arr["type"] = $billing_info[0]["INVOICE_BIZ_TYPE"];
            $data_arr["note"] = urlencode("申请退票");
            $data_arr["city"] = $this->user_city_py;
            $data_arr["contractnum"] = $contract_info[0]["CONTRACT_NO"];

            //同步开票数据到合同系统
            $save_result_negative = saveInvoice2Con($data_arr);

            if(!$save_result_negative)
            {
                $this->model->rollback();
                die(@json_encode($result));
            }
            /*-------------------更新记录----------------------*/
        }

        $this->model->commit();
        $result["status"] = true;
        $result["msg"] = g2u("退票成功！");
        die(json_encode($result));

    }


    /**
    +----------------------------------------------------------
    * 合同开票确认
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
    public function do_invoice()
    {
        //合同编号
        $contractid = trim($_REQUEST["contract_id"]);
        //发票ID
        $invoice_ids = $_POST['invoiceid'];
        //发票时间
        $invoice_time = $_POST['invoice_time'];
        //发票编号
        $invoice_no = $_POST['invoice_no'];
        $invoice_biz_type = $_POST['invoice_biz_type'];  // 发票类型

        //返回结果集
        $result = array(
            'state' => 0,
            'msg'=>null,
            'data'=>null,
        );

        $income_model = D("Contract");
        $case_model = D("ProjectCase");
        $billing_model = D("BillingRecord");

        $contract_info = $income_model->get_contract_info_by_id($contractid,array("CONTRACT_TYPE","CONTRACT_NO","CASE_ID"));

        if(empty($contract_info)){
            die(json_encode($result));
        }

        $case_id = $contract_info[0]["CASE_ID"];

        //已开票
        $field_arr["STATUS"] = 4;

        //成功标识
        $succes_count = 0;
        $error_str = '';

        //业务
        $this->model->startTrans();
        foreach($invoice_ids as $key=>$val)
        {
            $id = $val;
            if (D('BillingRecord')->isDuplicateInvoiceNo($invoice_no[$key], $contractid, $this->channelid)) {
                D()->rollback();
                echo json_encode(array(
                    'state' => 0,
                    'msg' => g2u(sprintf('发票号码重复，重复发票号为%s', $invoice_no[$key]))
                ));
                exit;
            }

            //通过传值获取
            $billing_info[0]["INVOICE_NO"] = u2g($invoice_no[$key]);
            $billing_info[0]["INVOICE_TIME"] = $invoice_time[$key];
            $billing_info[0]["INVOICE_BIZ_TYPE"] = $invoice_biz_type[$key];  // 开票类型

            //如果没有填写发票号和发票时间
            if(!$billing_info[0]["INVOICE_NO"] || !$billing_info[0]["INVOICE_TIME"] || !$billing_info[0]["INVOICE_BIZ_TYPE"])
            {
                $result["state"] = 0;
                $result["msg"] = g2u("开票时间、发票号码和发票类型未填写或未保存，请填写并保存！");
                die(json_encode($result));
            }
            else
            {
                $field_arr['INVOICE_TIME'] = $invoice_time[$key];
                $field_arr['INVOICE_NO'] = u2g($invoice_no[$key]);
                $field_arr['INVOICE_BIZ_TYPE'] = u2g($invoice_biz_type[$key]);
                $res = $billing_model->update_info_by_id($id,$field_arr);

                //判断该合同下是否还有已申请通过的发票
                $where = "CONTRACT_ID = ".$contractid." AND STATUS=3";
                $invoice_info = $billing_model->get_info_by_cond($where,array("ID"));

                //如果合同下没有待开票的发票记录 修改合同的IS_NEED_INVOICE=0
                $up_num_contract = true;
                if(!$invoice_info)
                    $up_num_contract = $income_model->update_info_by_id($contractid,array("IS_NEED_INVOICE"=>0));

                //新增收益明细记录;
                $case_info = $case_model->get_info_by_id($case_id,array("SCALETYPE"));
                $scale_type = $case_info[0]["SCALETYPE"];

                //如果是分销会员开票 修改分销会员的状态并填写发票号码
                if( $scale_type == 2 )
                {
                    $up_dis_member_num_fail = false;

//                    $member_distribution_model = D("MemberDistribution");
//
//                    $cond_where = "RELATE_INVOICE_ID = ".$id;
//                    $update_arr = array("INVOICE_STATUS"=>2,"INVOICE_NO"=>$billing_info[0]["INVOICE_NO"]);
//
//                    //更新分销状态
//                    $up_dis_member_num =$member_distribution_model->update_info_by_cond($update_arr, $cond_where);

                    // 分销记录财务开票确认之后的操作
                    $up_dis_member_num = $this->fxAfterInvoiceConfirm($id, $billing_info[0]["INVOICE_NO"]);

                    if ($up_dis_member_num === false) {
                        $up_dis_member_num_fail = true;
                    }
                }

                //新增收益
                $res_1 = $this->add_income_after_financial_invoice(array($contractid),$scale_type,$id);

                //根据发票Id找到发票信息
                $billing_info = $billing_model->get_info_by_id($id,array("INVOICE_MONEY","TAX","INVOICE_NO","REMARK","INVOICE_TIME", "INVOICE_BIZ_TYPE","FROMTYPE","FROMLISTID"));

                $format_date = oracle_date_format($billing_info[0]["INVOICE_TIME"]);
                $data_arr["date"] = $format_date?$format_date:$billing_info[0]["INVOICE_TIME"];
                $data_arr["money"] = $billing_info[0]["INVOICE_MONEY"];
                $data_arr["tax"] = $billing_info[0]["TAX"];
                $data_arr["invono"] = $billing_info[0]["INVOICE_NO"];
                //硬广 1  非硬广 2
//                $data_arr["type"] = $scale_type==3?1:2;
                $data_arr["type"] = $billing_info[0]["INVOICE_BIZ_TYPE"];  // 开票类型，1=广告费，2=服务费
                $data_arr["note"] = urlencode($billing_info[0]["REMARK"]);
                $data_arr["city"] = $this->user_city_py;
                $data_arr["contractnum"] = $contract_info[0]["CONTRACT_NO"];

                load("@.contract_common");
                //同步开票数据到合同系统
                $save_result = saveInvoice2Con($data_arr);

                //如果是置换物品售卖过来的开票变更状态
                if ($billing_info[0]['FROMTYPE'] == 2) {
                    $updateBusinessStatus = D("DisplaceApply")->updateListStatus($billing_info[0]['FROMLISTID'], 3); //更新状态到未申请状态
                }

                //如果以上操作都成功
                if($res && $res_1 && $save_result && !$up_dis_member_num_fail && $up_num_contract && $updateBusinessStatus!==false)
                {
                    $succes_count += 1;
                }
                else
                {
                    $error_str .= "第" . $k+1 . "行，编号为<$id>开票确认失败!<br />";

                }
            }
        }

        if(count($invoice_ids) == $succes_count){
            $this->model->commit();
            $result["state"] = 1;
            $result["msg"] = g2u("开票成功！");
        }
        else
        {
            $this->model->rollback();
            $result["state"] = 0;
            $result["msg"] = g2u($error_str);
        }
        die(json_encode($result));
    }



    /**保存开票数据（开票日期，发票号码，税额）
     *@param none
     *return none
     */
    public function save_data()
    {
        $contract_id = intval($_REQUEST['contract_id']);

        $invoice_ids = $_POST['invoiceid'];
        $invoice_time = $_POST['invoice_time'];
        $invoice_no = $_POST['invoice_no'];
        $invoice_biz_type = $_POST['invoice_biz_type'];
        $billing_model = D("BillingRecord");

        D()->startTrans();
        foreach($invoice_ids as $key=>$val)
        {
            $id = $val;
            $field_arr['INVOICE_NO'] = u2g($invoice_no[$key]);
            if (D('BillingRecord')->isDuplicateInvoiceNo($field_arr['INVOICE_NO'],$contract_id, $this->channelid)) {
                D()->rollback();
                echo json_encode(array(
                    'state' => 0,
                    'msg' => g2u(sprintf('发票号码重复，重复发票号为%s', $field_arr['INVOICE_NO']))
                ));
                exit;
            }

            $field_arr['INVOICE_TIME'] = $invoice_time[$key];
            $field_arr['INVOICE_BIZ_TYPE'] = $invoice_biz_type[$key];
            $res = $billing_model->update_info_by_id($id,$field_arr);

            // 如果是分销开票，则修改相应的开票明细
            if ($res !== false) {
                $fxInvoiceDetailCount = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$val}")->count();
                if ($fxInvoiceDetailCount) {
                    // 更改开票明细
                    $res = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$val}")->save(array('INVOICE_NO' => $field_arr['INVOICE_NO']));
                }
            }

            if($res === false)
            {
                D()->rollback();
                $result["state"] = 0;
                $result["msg"] = "数据保存出错，请重新尝试！";
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
        }
        D()->commit();  // 提交事务
        $result["state"] = 1;
        $result["msg"] = "保存成功！";
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
    }



    /**
   +----------------------------------------------------------
   * 业务回款记录
   +----------------------------------------------------------
   * @param none
   +----------------------------------------------------------
   * @return none
   +----------------------------------------------------------
   */
    public function refundRecords()
    {
        $income_model = D("Contract");
        $payment_model =D("PaymentRecord");
        $project_income_model = D("ProjectIncome");
        $project_case_model = D("ProjectCase");
        $contractid = $this->_merge_url_param['contract_id'];
        //获取url中的相关数据
        $id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        $add = isset($_GET['add']) ? intval($_GET['add']) : '';

        $city_id = $_SESSION["uinfo"]["city"];
        $city = M("Erp_city")->find($city_id);
        $city_py = strtolower($city["PY"]);

        $contract_info = $income_model->get_contract_info_by_id($contractid,array("CONTRACT_TYPE","CONTRACT_NO","CASE_ID","MONEY"));
        $case_id = $contract_info[0]["CASE_ID"];
        //根据case_id找到业务类型
        $case_info = $project_case_model->get_info_by_id($case_id,array("SCALETYPE"));
        $scale_type = $case_info[0]["SCALETYPE"];

        $this->assign('caseId', $case_id);
        $this->assign('scaleType', $scale_type);

        //新增
        if($showForm == 3 && $faction == 'saveFormData' && $id == 0)
        {
            if($scale_type != 2)//分销类型的合同不需要判断回款金额是否超过合同金额
            {
                //查询出该合同下所有的回款金额总和
                $sql = "SELECT sum(MONEY) SUM_MONEY FROM ERP_PAYMENT_RECORDS WHERE CONTRACT_ID = ".$contractid;
                $sum_money = $this->model->query($sql);
                $sum_money = $sum_money ? $sum_money[0]["SUM_MONEY"] : 0;

                //判断已回款金额加上本次回款金额是否超过合同金额
                if(bccomp($sum_money + $_REQUEST["MONEY"],$contract_info[0]["MONEY"],2) > 0)
                {
                    $result["status"] = 0;
                    $result["msg"] = "已回款金额（".$sum_money."）"
                        . "+ 本次回款金额（".$_REQUEST["MONEY"]."）"
                        . "超过合同的总金额（".$contract_info[0]["MONEY"]."），不能添加回款";
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }
            }

            //验证
            $hasBilling = intval($_REQUEST['HAS_BILLING']); //是否关联发票

            if($_REQUEST['HAS_BILLING'] && empty($_REQUEST['BILLING_RECORD_ID'])){
                $result["msg"] = g2u('对不起，亲，需要关联发票时，请选择需要关联的发票号！');
                die(@json_encode($result));
            }

            //保存回款数据             
            $data["CASE_ID"] = $contract_info[0]["CASE_ID"];
            $data["MONEY"] = $_REQUEST["MONEY"];
            $data["CREATETIME"] = $_REQUEST["CREATETIME"];
            $data["REMARK"] = u2g($_REQUEST["REMARK"]);
            $data['BILLING_RECORD_ID'] = $_REQUEST['BILLING_RECORD_ID'];  // 发票号码对应的开票记录ID
            $data["PAYMENT_METHOD"] = intval($_REQUEST["PAYMENT_METHOD"]);
            $data["CONTRACT_ID"] = $contractid;
            $data["HAS_BILLING"] = $_REQUEST['HAS_BILLING']; //是否需要发票号

            $this->model->startTrans();
            $refundid = $payment_model->add_refund_records($data);//新增回款记录
            //var_dump($refundid);die;
            if(!$refundid)
            {   $this->model->rollback();
                $result["status"] = 0;
                $result["msg"] = "添加回款记录失败！";
                $result["msg"] = u2g($result["msg"]);
                echo json_encode($result);
                exit;
            }
            else
            {
                //新增收益明细记录   
                switch($scale_type)
                {
                    case 3:
                        $income_info['INCOME_FROM'] = 11;
                        break;
                    case 2:
                        $income_info['INCOME_FROM'] = 7;
                        // 获取发票对应的开票金额，如果相等则更新对应的开票状态 (如果必须关联)
                        if($hasBilling) {
                            $res1 = D('PaymentRecord')->updateFxCommissionPaymentStatus($data);
                        }
                        break;
                    case 4:
                        $income_info['INCOME_FROM'] = 15;
                        break;
                    case 8:
                        $income_info['INCOME_FROM'] = 22;
                        break;
                    default :
                        break;

                }
                $income_info['CASE_ID'] = $data["CASE_ID"];
                $income_info['ENTITY_ID'] = $contractid;
                $income_info['ORG_ENTITY_ID'] = $contractid;
                $income_info['INCOME'] =  $data["MONEY"];
                $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s");
                $income_info['PAY_ID'] = $refundid;
                $income_info['ORG_PAY_ID'] = $refundid;
                $income_info['INCOME_REMARK'] = $data["REMARK"];
                $income_info['PAYMENT_METHOD'] = $data["PAYMENT_METHOD"];

                //计算销项税
                $taxrate = get_taxrate_by_citypy($city_py);
                $output_tax = round($data["MONEY"]/(1 + $taxrate) * $taxrate,2);
                $income_info['OUTPUT_TAX'] = $output_tax;

                if ($res1 !== false) {
                    $res1 = $this->add_income_after_financial_refund($income_info);
                }

                //保存回款数据到合同管理系统 
                $data_arr = array(
                            "city"=>$city_py,
                            "contractnum"=>$contract_info[0]["CONTRACT_NO"],
                            "money"=>$_REQUEST["MONEY"],
                            "type"=> $scale_type == 3 ? 1 : 2,
                            "date"=>$_REQUEST["CREATETIME"],
                            "note"=>urlencode(u2g($_REQUEST["REMARK"])),
                            //"omsid"=>$refundid               //回款记录id
                        );

                load("@.contract_common");
                $resarr = saveRefund2Con($data_arr);//同步合同回款数据 
                $bakck_id = !empty($resarr["backid"]) ? intval($resarr["backid"]) : 0;
                $up_num = $payment_model->update_info_by_id($refundid , array("BACKID"=>$bakck_id));
                if($res1 && $bakck_id > 0 && $up_num)
//                if(true)  // 正式版本需要用上面的一行代码，这行代码是测试用的
                {
                    $this->model->commit();
                    $result["status"] = 2;
                    $result["msg"] = "添加回款记录成功";
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }
                else
                {
                    $this->model->rollback();
                    $result["status"] = 0;
                    $result["msg"] = "添加回款记录失败！".$res1."--".$bakck_id."--".$up_num;
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }

            }
        } elseif( $showForm == 1 && $faction == 'saveFormData' && $id > 0 ) { //修改回款记录数据

            $hasBilling = intval($_REQUEST['HAS_BILLING']); //是否关联发票

            //验证
            if($hasBilling && empty($_REQUEST['BILLING_RECORD_ID'])){
                $result["msg"] = g2u('对不起，亲，需要关联发票时，请选择关联的发票号！');
                die(@json_encode($result));
            }

            if($hasBilling) {
                $remainPayAmount = D('PaymentRecord')->getRemainPayAmount(intval($_POST['BILLING_RECORD_ID']));
                $maxApplyAmount = $remainPayAmount + floatval($_POST['MONEY_OLD']);
                if ($remainPayAmount < 0 || $maxApplyAmount < $_POST['MONEY']) {
                    $msg = sprintf('修改失败，最大可回款金额为%s', $maxApplyAmount);
                    if ($remainPayAmount < 0) {
                        $msg = sprintf('已回款的金额已经超过超过最大可回款金额%s元，请联系管理员进行处理', -$remainPayAmount);
                    }
                    echo json_encode(array(
                        'status' => 0,
                        'msg' => g2u($msg)
                    ));
                    exit;
                }
            }

            $current_day = intval(date("d"));//当前日期
            $current_mouth = date("Y-m");//当前月份

            //获取到修改的记录的月份
            $payment_records_ctime = D("Erp_payment_records")->field("CREATETIME")->where("ID=$id")->find();
            $format_date = oracle_date_format($payment_records_ctime["CREATETIME"]);
            if($format_date)
            {
                $payment_records_cm = substr($format_date,0,7);
            }
            else
            {
                $payment_records_cm = substr($payment_records_ctime["CREATETIME"],0,7);
            }
            if($current_day > 5)//五号以后只能修改当月的数据
            {
                if(strcmp($current_mouth,$payment_records_cm) == 1)
                {
                    $result["status"] = 0;
                    $result["msg"] = "对不起！每月五号之后只能修改当月数据，不能修改上月回款数据";
                    $result["msg"] = g2u($result["msg"]);
                    echo json_encode($result);
                    exit;
                }
            }

            $data["CASE_ID"] = $contract_info[0]["CASE_ID"];
            $data["MONEY"] = $_REQUEST["MONEY"];
            $data["CREATETIME"] = $_REQUEST["CREATETIME"];
            $data["PAYMENT_METHOD"] = intval($_REQUEST["PAYMENT_METHOD"]);
            $data["REMARK"] = u2g($_REQUEST["REMARK"]);
            $data["HAS_BILLING"] = intval($_REQUEST["HAS_BILLING"]);
            $this->model->startTrans();
            $up_num = $payment_model->update_info_by_id($id,$data);//修改回款记录
            $data['BILLING_RECORD_ID'] = trim($_REQUEST['BILLING_RECORD_ID']);  // 开票记录号

            //同步修改收益表中的数据   
            switch($scale_type)
            {
                case 3:
                    $income_from = 11;
                    break;
                case 2:
                    $income_from = 7;
                    if($hasBilling) {
                        $updated = D('PaymentRecord')->updateFxCommissionPaymentStatus($data, $msg);
                        if ($updated === false) {
                            $msg = empty($msg) ? '开票失败' : $msg;
                            ajaxReturnJSON(false, g2u($msg));  // todo
                        }
                    }
                    break;
                case 4:
                    $income_from = 15;
                    break;
                default :
                    break;

            }

            //计算销项税
            $taxrate = get_taxrate_by_citypy($city_py);
            $output_tax = round($data["MONEY"]/(1 + $taxrate) * $taxrate,2);
            $update_arr = array("INCOME"=>$data["MONEY"],"OUTPUT_TAX"=>$output_tax,"PAYMENT_METHOD"=>$data["PAYMENT_METHOD"]);
            $payment_up_num = $project_income_model
                ->update_income_info($update_arr, $contract_info[0]["CASE_ID"],$contractid,$id,$income_from);
            //ECHO M()->_sql();die;
            //同步修改合同系统中的数据
            load("@.contract_common");
            $backid = $payment_model->get_info_by_id($id,array("BACKID"));
            $ht_arr = array(
                            "city"=>$city_py,
                            "contractnum"=>$contract_info[0]["CONTRACT_NO"],
                            "money"=>$_REQUEST["MONEY"],
							"type"=>$scale_type == 3 ? 1 : 2,
                            "date"=>$_REQUEST["CREATETIME"],
                            "note"=>urlencode(u2g($_REQUEST["REMARK"])),
                            "backid"=>$backid[0]["BACKID"]
                        );
            $resarr = saveRefund2Con($ht_arr);//同步合同回款数据 
            //var_dump($up_num);var_dump($payment_up_num);var_dump($resarr);DIE;
            if($up_num && $payment_up_num && $resarr)
            {
                $this->model->commit();
                $result["status"] = 1;
                $result["msg"] = "修改回款数据成功！";
            }
            else
            {
                $this->model->rollback();
                $result["status"] = 0;
                $result["msg"] = "修改回款数据失败！！";
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }

        Vendor('Oms.Form');
        $form = new Form();
        $form->initForminfo(135);
        if($_GET["from"] == "invoice")
        {
            $form->ADDABLE = 0;
            $form->EDITABLE = 0;
        }
        $form = $form->where("CONTRACT_ID = ".$contractid);
        $form = $form->where("CASE_ID = {$case_id}");

        if (in_array($scale_type, array(2, 3, 4 , 8))) {
                $ofInvoiceNoSql = <<<SQL
                SELECT B.ID,
                       B.INVOICE_NO
                FROM ERP_BILLING_RECORD B
                WHERE B.STATUS = 4
                  AND B.CASE_ID = {$case_id}
                  AND B.CONTRACT_ID={$contractid}

SQL;
            if ($showForm == 3) {
                $ofInvoiceNoSql .= " AND B.INVOICE_MONEY > (
                  SELECT nvl(SUM(P.MONEY), 0)
                    FROM ERP_PAYMENT_RECORDS P
                    WHERE P.BILLING_RECORD_ID = B.ID)";
            }
            $form->setMyField('BILLING_RECORD_ID', 'LISTSQL', $ofInvoiceNoSql);
        } else {
            $form->setMyField('BILLING_RECORD_ID', 'FORMVISIBLE', 0);
        }

        // 发票号码不能修改
        if ($showForm == 1) {
            $form->setMyField('BILLING_RECORD_ID', 'READONLY', -1);
            $form->setMyField('HAS_BILLING', 'READONLY', -1);
            $paymentRecord = D("Erp_payment_records")->field("CREATETIME","BILLING_RECORD_ID")->where("ID=$id")->find();

            if(!$paymentRecord['BILLING_RECORD_ID']){ //没有关联发票号的显示否
                $form = $form->setMyFieldVal('HAS_BILLING', 0, TRUE);
            }
        }

        //设置回款方式
        $form->setMyField('PAYMENT_METHOD', 'LISTCHAR', array2listchar(D('PaymentRecord')->payment_method()));

        $formHtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件

        $this->assign('form',$formHtml);
        $this->assign('paramUrl',$this->_merge_url_param);
        $this->display('financial_invoice_records');
    }

    //判断进项税所属类型
    //保存进项税
    public function save_input_tax()
    {
        $ids = $_REQUEST["fid"]; //报销明细编号数组
        $purchase = D("PurchaseList");
        $reim_detail_model = D("ReimbursementDetail");
        $this->model->startTrans();
        foreach($_REQUEST["input_tax"] as $key=>$val)
        {
            $data["INPUT_TAX"] = $val;
            $reim_detail_up_num = $reim_detail_model->update_reim_detail_by_id($ids["$key"],$data);
            //$purchase_id = $reim_detail_model->get_detail_info_by_id($ids["$key"],array("BUSINESS_ID"));
            //$purchase_up_num = $purchase ->update_purchase_list_by_id($purchase_id[0]["BUSINESS_ID"],$data);
            if(!is_int($reim_detail_up_num) )
            {
                $this->model->rollback();
                $result["state"] = 0;
                $result["msg"] = "进项税保存出错，请重试！";
                userLog()->writeLog(implode(',', $_POST["fid"]), $_SERVER["REQUEST_URI"], '报销确认:保存进项税:' . json_encode($result) , serialize($_POST));
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
        }
        $this->model->commit();
        $result["state"] = 1;
        $result["msg"] = "进项税保存成功！";
        userLog()->writeLog(implode(',', $_POST["fid"]), $_SERVER["REQUEST_URI"], '报销确认:保存进项税:' . json_encode($result) , serialize($_POST));
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
    }

	//判断进项税所属类型
    //保存进项税
    public function save_input_reimDetail()
    {
        $ids = $_REQUEST["fid"]; //报销明细编号数组
        $purchase = D("PurchaseList");
        $reim_detail_model = D("ReimbursementDetail");
		if($_REQUEST['action']=='getReimDtail'){
			//$ids = implode(',', $_POST["fid"]);
			$res = $reim_detail_model->get_detail_info_by_id($_POST["fid"]);
			foreach($res as $key=>$one){
				$temp[$one['ID']]['DEPT_ID'] = $one['DEPT_ID'];
				$temp[$one['ID']]['NCTYPE'] = $one['NCTYPE'];
				$temp[$one['ID']]['ISKF'] = $one['ISKF'];
				$temp[$one['ID']]['MONEY'] = $one['MONEY'];
				$temp[$one['ID']]['RID'] = $one['ID'];
				$temp[$one['ID']]['FEE_ID'] = $one['FEE_ID'];



			}
			echo json_encode($temp);
            exit;

		}
        $this->model->startTrans();
		foreach($_REQUEST["fid"] as $key=>$val){
			$dd[$key]["DEPT_ID"] = $_REQUEST["DEPTID"][$key] ;
			$dd[$key]["NCTYPE"] = $_REQUEST["NCTYPE"][$key] ;
			$dd[$key]["FEE_ID"] = $_REQUEST["FEEID"][$key] ;
			$dd[$key]["MONEY"] = $_REQUEST["MONEY"][$key] ;
			$dd[$key]["ISKF"] = $_REQUEST["ISKF"][$key] ;
			 
			
		}
        foreach($dd as $key=>$val)
        {
            $data["DEPT_ID"] = $val['DEPT_ID'];
			$data["NCTYPE"] = $val['NCTYPE'];
			$data["FEE_ID"] = $val['FEE_ID'];
			$data["MONEY"] = $val['MONEY'];
			$data["ISKF"] = $val['ISKF'];
            $reim_detail_up_num = $reim_detail_model->update_reim_detail_by_id($ids["$key"],$data);
			if($reim_detail_up_num){
				$remOne = $reim_detail_model->get_detail_info_by_id($ids["$key"]);
				if($remOne[0]['TYPE']==1 ||$remOne[0]['TYPE']==14 ){
					$temp['FEE_ID'] = $data["FEE_ID"];
					$purchase_up = D('Erp_purchase_list')->where("ID=".$remOne[0]['BUSINESS_ID'])->save($temp);
				}

			}
            //$purchase_id = $reim_detail_model->get_detail_info_by_id($ids["$key"],array("BUSINESS_ID"));
            //$purchase_up_num = $purchase ->update_purchase_list_by_id($purchase_id[0]["BUSINESS_ID"],$data);
            if(!is_int($reim_detail_up_num) )
            {
                $this->model->rollback();
                $result["state"] = 0;
                $result["msg"] = "报销明细保存出错，请重试！";
                userLog()->writeLog(implode(',', $_POST["fid"]), $_SERVER["REQUEST_URI"], '报销确认:保存报销明细:' . json_encode($result) , serialize($_POST));
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
        }
        $this->model->commit();
        $result["state"] = 1;
        $result["msg"] = "报销明细保存成功！";
        userLog()->writeLog(implode(',', $_POST["fid"]), $_SERVER["REQUEST_URI"], '报销确认:编辑报销明细:' . json_encode($result) , serialize($_POST));
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
    }


     
    //保存报销确认时间
    public function ajax_update_reimtime()
    {
        $ids = $_REQUEST["reimDetailId"]; //报销明细编号数组
        //var_dump($ids);
        //$reim_list_model = D("ReimbursementList");
        //$this->model->startTrans();
        foreach($_REQUEST["REIMTIME"] as $key=>$val)
        {
          //  $data["REIM_TIME"] = $val;
           // $reim_list_up_num = $reim_list_model->update_reim_list_by_cond($data,"ID=".$ids["$key"]);
		  $reim_list_up_num =  D()->execute("update ERP_REIMBURSEMENT_LIST set REIM_TIME=to_date('$val','yyyy-mm-dd hh24:mi:ss') where ID=".$ids["$key"]);
		//  echo "update ERP_REIMBURSEMENT_LIST set REIM_TIME=(to_date('$val','yyyy-mm-dd hh24:mi:ss') where ID=".$ids["$key"];
            //$purchase_id = $reim_detail_model->get_detail_info_by_id($ids["$key"],array("BUSINESS_ID"));
            //$purchase_up_num = $purchase ->update_purchase_list_by_id($purchase_id[0]["BUSINESS_ID"],$data);
            if(!is_int($reim_list_up_num) )
            {
                //$this->model->rollback();
                $result["status"] = 0;
                $result["msg"] = "报销确认时间保存出错，请重试！";
                userLog()->writeLog(implode(',', $_POST["fid"]), $_SERVER["REQUEST_URI"], '报销确认:报销确认时间:' . json_encode($result) , serialize($_POST));
                $result["msg"] = g2u($result["msg"]);
                echo json_encode($result);
                exit;
            }
        }
        //$this->model->commit();
        $result["status"] = 1;
        $result["msg"] = "报销确认时间保存成功！";
        userLog()->writeLog(implode(',', $_POST["fid"]), $_SERVER["REQUEST_URI"], '报销确认:报销确认时间:' . json_encode($result) , serialize($_POST));
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
    }


    /*
     * 财务确认收入后 增加收益表中的记录
     * @param $memberid array() 会员ID
     * @param $paymentid array()会员支付id
     * @param $method 财务确认方式
     * @param $iscancle 是否是取消确认操作 0非取消确认 1取消确认
     * @param return 返回结果 成功返回插入的id 失败返回false
     */
    public function add_income_after_financial_confirm($method,$iscancle = 0,
            $memberid=array(),$paymentid=array())
    {
        $member_model = D("Member");
        $member_payment_model = D("MemberPay");
        $ProjectIncome_model = D("ProjectIncome");
        if($method == 1 && empty($paymentid) && !empty($memberid)){
            foreach ($memberid as $key=>$val){
                //根据会员ID( MID )找到案例ID（CASEID）                                        
                $member_info =$member_model->get_info_by_id($val,array("ID","CASE_ID"));
               // var_dump($member_info);die;
                $income_info['CASE_ID'] = $member_info["CASE_ID"];
                $income_info['ENTITY_ID'] = $member_info["ID"];
                $income_info['ORG_ENTITY_ID'] = $member_info["ID"];
                $income_info['INCOME_FROM'] = 2;
                $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s");

                $field_arr = array("ID","TRADE_MONEY");
                $cond_where = "MID = '".$val."' AND STATUS != 4";
                $payment_info = $member_payment_model->get_payinfo_by_cond($cond_where,$field_arr);
                foreach ($payment_info as $k=>$v){
                    $income_info['PAY_ID'] = $v["ID"];
                    $income_info['ORG_PAY_ID'] = $v["ID"];
                    if($iscancle == 0){
                        $income_info['INCOME'] = $v["TRADE_MONEY"];
                        $income_info['INCOME_REMARK'] = '会员预收确认';
                    }elseif($iscancle == 1){//取消确认，插入负的金额值
                        $income_info['INCOME'] = 0-$v["TRADE_MONEY"];
                        $income_info['INCOME_REMARK'] = '会员取消预收确认';
                    }
                    //var_dump($income_info);
                    $res = $ProjectIncome_model->add_income_info($income_info);
                    if(!$res){
                        return false;
                    }
                }
            }
        }
        else if($method == 2 && empty($memberid) && !empty($paymentid))
        {
            foreach ($paymentid as $k=>$v){
                $field_arr = array("MID","TRADE_MONEY");
                $payment_info = $member_payment_model->get_payinfo_by_id($v,$field_arr);
                $member_info =$member_model->get_info_by_id($payment_info[0]["MID"],array("CASE_ID"));

                $income_info['CASE_ID'] = $member_info["CASE_ID"];
                $income_info['ENTITY_ID'] = $payment_info[0]["MID"];
                $income_info['ORG_ENTITY_ID'] = $payment_info[0]["MID"];
                $income_info['INCOME_FROM'] = 2;
                $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
                $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s");
                $income_info['PAY_ID'] = $v;
                $income_info['ORG_PAY_ID'] = $v;
                if($iscancle == 0){
                    $income_info['INCOME'] = $payment_info[0]["TRADE_MONEY"];
                }elseif($iscancle == 1){//取消确认，插入负的金额值
                    $income_info['INCOME'] = 0-$payment_info[0]["TRADE_MONEY"];
                }

                $res = $ProjectIncome_model->add_income_info($income_info);
                if(!$res){
                    return false;
                }
            }
        }

        return $res;
    }


    /*
    * 财务对会员进行开票后 新增收益明细记录
    * @param $entityid array() 业务实体编号（会员编号、广告合同编号、划拨申请单编号……）
    * @param $scale_type 业务类型 1电商会员 2分销 3硬广 4活动
    * @pay_id 发票id
    * @is_positive 是否是正值
    * @tax 销项税  true 用数据库中  false用0
    * @param return 返回结果 成功返回插入的id 失败返回false
    */
    public function add_income_after_financial_invoice($entityid,$scale_type,$pay_id,$is_positive=true,$tax=true)
    {
        $member_model = D("Member");
        $BillingRecord_model = D("BillingRecord");
        $ProjectIncome_model = D("ProjectIncome");
        //var_dump($scale_type);die;
        foreach($entityid as $key=>$val)
        {
            $income_info['ENTITY_ID'] = $val;
            $income_info['ORG_ENTITY_ID'] = $val;
            $income_info['ADD_UID'] = $_SESSION["uinfo"]["uid"];
            $income_info['CITY_ID'] = $_SESSION["uinfo"]["city"];
            $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s");

            switch($scale_type)
            {
                //电商会员
                case "1":
                    $income_info['INCOME_FROM'] = 3;
                    //根据合同ID或会员ID找到发票记录信息
                    $cond_where = "CONTRACT_ID=$val and INVOICE_TYPE = 2 and ID = ".$pay_id;
                    $field_arr = array("ID","INVOICE_MONEY","CASE_ID","TAX");
                    $billing_record_info = $BillingRecord_model->get_info_by_cond($cond_where,$field_arr);
                    break;
                //分销会员
                case "2":
                    $income_info['INCOME_FROM'] = 8;
                    $field_arr = array("ID","INVOICE_MONEY","CASE_ID","TAX");
                    $cond_where = "CONTRACT_ID=$val and INVOICE_TYPE=3  and ID = ".$pay_id;
                    $billing_record_info = $BillingRecord_model->get_info_by_cond($cond_where,$field_arr);
                    break;
                //硬广合同
                case "3":
                    $income_info['INCOME_FROM'] = 12;

                    //根据合同编号 并且发票状态为已开 发票类型为合同开票的所有发票记录
                    $field_arr = array("ID","INVOICE_MONEY","CASE_ID","TAX");
                    $cond_where = "CONTRACT_ID=$val and INVOICE_TYPE=1  and ID = ".$pay_id;
                    $billing_record_info = $BillingRecord_model->get_info_by_cond($cond_where,$field_arr);
                    break;
                //活动合同
                case "4":
                    $income_info['INCOME_FROM'] = 16;

                    //根据合同编号 并且发票状态为已开 发票类型为合同开票的所有发票记录
                    $field_arr = array("ID","INVOICE_MONEY","CASE_ID","TAX");
                    $cond_where = "CONTRACT_ID=$val and INVOICE_TYPE=1  and ID = ".$pay_id;
                    $billing_record_info = $BillingRecord_model->get_info_by_cond($cond_where,$field_arr);
                    break;

                //非我方收筹
                case "8":
                    $income_info['INCOME_FROM'] = 23;

                    //根据合同编号 并且发票状态为已开 发票类型为合同开票的所有发票记录
                    $field_arr = array("ID","INVOICE_MONEY","CASE_ID","TAX");
                    $cond_where = "CONTRACT_ID=$val and INVOICE_TYPE=1  and ID = ".$pay_id;
                    $billing_record_info = $BillingRecord_model->get_info_by_cond($cond_where,$field_arr);
                    break;
            }

            if(is_array($billing_record_info) && !empty($billing_record_info))
            {
                foreach ($billing_record_info as $k=>$v)
                {
                    $income_info['PAY_ID'] = $v["ID"];
                    $income_info['ORG_PAY_ID'] = $v["ID"];
                    //是否是插入正收益还是负收益
                    $income_info['INCOME'] = $is_positive?$v["INVOICE_MONEY"]:-$v["INVOICE_MONEY"];
                    $income_info['OUTPUT_TAX'] = $tax?$v["TAX"]:0;
                    if($tax) {
                        $income_info['OUTPUT_TAX'] = $is_positive?$v["TAX"]:-$v["TAX"];
                    }
                    if($v["INVOICE_MONEY"] < 0)
                    {
                        $income_info['INCOME_REMARK'] = "会员换票红冲";
                    }
                    $income_info['CASE_ID'] = $v["CASE_ID"];
                    $res = $ProjectIncome_model->add_income_info($income_info);
                    if(!$res)
                    {
                       return false;
                    }
                }
            }
            else
            {
                return false;
            }

        }
        return $res;
    }


    /*
    * 财务对硬广/活动进行回款后 新增收益明细记录
    * @param $income_info array() 插入收益明细表字段的键值对
    * @param return 返回结果 成功返回插入的id 失败返回false
    */
    public function add_income_after_financial_refund($income_info){
        $ProjectIncome_model = D("ProjectIncome");
        $res = $ProjectIncome_model->add_income_info($income_info);
        return $res;
    }

    /**
     * 佣金索回
     * @param
     */
    public function callback_commission()
    {
        $city_channel = $this->channelid;
        Vendor('Oms.Form');
        $form = new Form();
        $commission_model = D("CommissionBack");

        //获取url中的相关数据
        $id = isset($_GET['ID']) ? intval($_GET['ID']) : 0;
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : '';
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';
        if($faction && $showForm ==1 && $id > 0){//
            if( !empty($_REQUEST["CALLBACK_DATE"]) ) $update_data["CALLBACK_DATE"] = strip_tags(trim($_REQUEST['CALLBACK_DATE']));
            if( !empty($_REQUEST["STATUS"]) ) $update_data["STATUS"] = strip_tags(trim($_REQUEST['STATUS']));
            $res = $commission_model->update_commission_info_by_id($id,$update_data);
            if($res)
            {
                $result["status"] = 1;
                $result["msg"] = "保存成功！";
            }
            else
            {
                $result["status"] = 0;
                $result["msg"] = "保存失败！";
            }
            $result["msg"] = g2u($result["msg"]);
            echo json_encode($result);
            exit;
        }

        $form->initForminfo(175)->where("CITY_ID = ".$city_channel." AND STATUS != 4");
        $sql = "(select a.ID,a.STATUS,a.CALLBACK_DATE,a.TYPE,b.CITY_ID,b.PRJ_NAME,
                    b.REALNAME,b.MOBILENO,b.PAID_MONEY,d.MONEY AGENCY_REWARD,C.STATUS REIM_STATUS
                    FROM ERP_COMMISSION_BACK a
                    left join ERP_CARDMEMBER b ON a.MID=b.ID 
                    LEFT JOIN ERP_REIMBURSEMENT_LIST c ON a.REIM_LIST_ID=c.ID 
                    left join erp_reimbursement_detail d on d.list_id = c.id
                    WHERE b.STATUS=1 AND c.TYPE IN(3,4,5,6) AND c.STATUS !=4 AND d.STATUS != 4 and a.mid = d.business_id)";
        $form->SQLTEXT = $sql;
        //设置佣金退回状态
        $commission_status_remark = $commission_model->get_conf_commission_status_remark();
        $form->setMyField('STATUS', 'LISTCHAR', array2listchar($commission_status_remark), FALSE);
        $form->DELCONDITION = "%STATUS% == 0";
        $form->EDITCONDITION = "%STATUS% == 0";
        $formHtml = $form->getResult();
        $this->assign("form",$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->display("callback_commission");
    }

    /**
     * 保存拥挤索回时间
     */
    public function save_commssion_callback_data()
    {
        if( !empty($_REQUEST["callback_date"]) )
        {
            $update_data["CALLBACK_DATE"] = strip_tags(trim($_REQUEST['callback_date']));
        }
        if( !empty($_REQUEST["status"]) )
        {
            $update_data["STATUS"] = strip_tags(trim($_REQUEST['status']));
        }
		!empty($_REQUEST["fid"])?$id = strip_tags(intval($_REQUEST['fid'])):0;
		$commission_model = D("CommissionBack");
        $res = $commission_model->update_commission_info_by_id($id,$update_data);
        if($res)
        {
            $result["status"] = 1;
            $result["msg"] = "保存成功！";
        }
        else
        {
            $result["status"] = 0;
            $result["msg"] = "保存失败！";
        }
        $result["msg"] = g2u($result["msg"]);
        echo json_encode($result);
        exit;
    }

    /*
     *银联对账表格模板下载
     * @param none
     * @return none 
     */
    public function downloadMoudle($file="", $down_name = '银联商务对账明细查询表')
    {
        $file = "./Public/Uploads/down_template/银联商务对账明细查询表.xls";
        $suffix = substr($file,strrpos($file,'.')); //获取文件后缀
        $down_name = $down_name.$suffix; //新文件名，就是下载后的名字
        //判断给定的文件存在与否 
        if(!file_exists($file))
        {
            die("您要下载的文件已不存在，可能是被删除");
        }
        $fp = fopen($file,"r");
        $file_size = filesize($file);
        //下载文件需要用到的头
        header("Content-type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length:".$file_size);
        header("Content-Disposition: attachment; filename=".$down_name);
        $buffer = 1024;
        $file_count = 0;
        //向浏览器返回数据 
        while(!feof($fp) && $file_count < $file_size)
        {
          $file_con = fread($fp,$buffer);
          $file_count += $buffer;
          echo $file_con;
        }
            fclose($fp);
        }


    /**
     * 非付现成本确认
     * @param none
     * return none
     */
    public function no_outlaycoat_confirm()
    {
        //操作行为
        $act = isset($_POST['act'])?trim($_POST['act']):'';

        //确认
        if($act=='confirm'){
            $Ids = $_POST['Ids'];

            //返回结果集
            $return = array(
              'status'=>false,
              'data'=>'',
              'msg'=>'',
            );

            //操作循环
            $i = 0;
            foreach($Ids as $key=>$val){
                $ret = M('erp_noncashcost')->where("ID=$val AND STATUS = 2")->save(array('STATUS'=>5));
                if(!$ret){
                    $return['msg'] .= '编号为' . $val . "确认失败!\n";
                }
                else
                {
                    //回款插入到合同系统
                    //获取合同编号
                    $noncashcost_data = M("erp_noncashcost")
                        ->field("contract_no,amount")
                        ->where("id = " . $val)
                        ->find();

                    //合同编号
                    $contractnum = $noncashcost_data['CONTRACT_NO'];
                    //资金池冲抵金额
                    $zjccd_money = $noncashcost_data['AMOUNT'];

                    //插入到api运行log表中
                    $tongji_url =  CONTRACT_API . 'sync_ct_backmoney.php?city=' . $this->channelid_py  . '###type=1###contractnum=' . $contractnum  .'###zjccd_money='.$zjccd_money.'###date='.date('Y-m-d').'###note='.urlencode('经管系统自动同步');
                    api_log($this->channelid,$tongji_url,0,$this->uid,1);

                    $i++;
                }
            }

            if($i>0){
                $return['status'] = true;
                $return['msg'] = "亲，共确认".$i."条！\n" . $return['msg'];

            }
            $return['msg'] = g2u($return['msg']);

            die(@json_encode($return));
        }


        Vendor('Oms.Form');
        $form = new Form();

        $form =  $form->initForminfo(197);
        //SQL重新赋值
        $form->SQLTEXT = '((SELECT A.*,B.PROJECTNAME,B.CITY_ID,A.CONTRACT_NO CONTRACT  FROM ERP_NONCASHCOST A LEFT JOIN ERP_PROJECT B  ON A.PROJECT_ID = B.ID  WHERE (A.STATUS = 2 OR A.STATUS = 5)  AND B.CITY_ID=' . $this->channelid . ') ORDER BY A.ID DESC)';

        //不展现操作行(属性设置)
        $form->setAttribute('NOPERATE',1);

        //申请人
        $form->setMyField('APPLY_USER', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        //城市
        $form->setMyField('CITY_ID', 'LISTSQL','SELECT ID, NAME FROM ERP_CITY', TRUE);
        //审核状态
        $form->setMyField('STATUS', 'LISTCHAR', array2listchar(array("0"=>'未提交审核',"1"=>'审核过程中',"2"=>'审核通过',"3"=>'未审核通过',"5"=>'已确认')), FALSE);
        //类型
        $form->setMyField('TYPE', 'LISTCHAR', array2listchar(array("1"=>'广告',"2"=>'差价',"3"=>'活动差价',"4"=>'其他')), TRUE);

//        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
        //获取渲染页面
        $formhtml = $form->getResult();
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->assign('form',$formhtml);
        $this->display('noncashcost');
    }

    /**
     +----------------------------------------------------------
     * 获取退库价格
     +----------------------------------------------------------
     * @param float $total_price 总成本
     * @param int $total_num 总数量
     +----------------------------------------------------------
     * @return none
     +----------------------------------------------------------
     */
    private function _get_avg_price($total_price , $total_num)
    {
        $return_price = 0;

        if($total_price % $total_num == 0)
        {
            $return_price = $total_price / $total_num;
        }
        else
        {
            $unit_price = round($total_price /  $total_num , 2);
            $remain_price = $total_price - ($total_num - 1) * $unit_price;
            $return_price = $unit_price < $remain_price ? $unit_price : $remain_price;
        }

        return $return_price;
    }

    /**
     * 打回资金池费用申请
     * @param $reimListData
     * @return bool
     */
    private function revertFundPoolCostApply($reimListData, &$fail_reim_arr) {
        $dbResult = false;
        if (notEmptyArray($reimListData)) {
            D()->startTrans();
            $reimDetailList = D("ReimbursementDetail")->where("LIST_ID = {$reimListData['ID']}")->select();
            if (notEmptyArray($reimDetailList)) {
                foreach ($reimDetailList as $k => $v) {
                    $costData = D("Benefits")->getFundPoolCost($v['BUSINESS_ID']);
                    $costData['FEE'] = -$costData['FEE'];
                    $costData['EXPEND_FROM'] = 34;
                    $dbResult = D('ProjectCost')->add_cost_info($costData);  // 添加一条负的采购成本
                    if ($dbResult !== false) {
                        $dbResult = D('ReimbursementDetail')->where("ID = {$v['ID']}")->save(array('STATUS' => 3));  // 更新报销明细
                    }

                    if ($dbResult !== false ) {
                        // 更新资金池费用状态
                        $dbResult = D('Benefits')->where("ID = {$v['BUSINESS_ID']}")->save(array(
                            "ISCOST" => 1,
                            "STATUS" => 1
                        ));
                    }

                    if ($dbResult === false) {
                        break;
                    }
                }
            }

            if ($dbResult !== false) {  // 更新报销列表
                $dbResult = D('ReimbursementList')->where("ID = {$reimListData['ID']}")->save(array('STATUS' => 3));
            }

            if ($dbResult === false) {
                D()->rollback();
                $fail_reim_arr[] = $reimListData['ID'];
            } else {
                D()->commit();
            }
        }
    }

    private function fxAfterInvoiceConfirm($id, $invoiceNo) {
        $response = false;
        if (intval($id) && !empty($invoiceNo)) {
            // 需要更新的数据
            // 1：更新开票明细 commission_invoice_detail
            // 2：更新佣金记录 post_commission
            $response = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$id}")->save(array(
                'INVOICE_STATUS' => 3,  // 已开票
                'INVOICE_NO' => $invoiceNo
            ));

            if ($response !== false) {
                $memberIdSql = <<<SQL
                    SELECT c.card_member_id,
                    d.post_commission_id,
                    m.case_id,
                    m.agency_reward_after amount,
                    m.housetotal
                    FROM erp_commission_invoice_detail d
                    LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
                    LEFT JOIN erp_cardmember m ON m.id = c.card_member_id
                    WHERE d.billing_record_id = %d
SQL;
                $memberIdList = D()->query(sprintf($memberIdSql, $id));
                if (count($memberIdList)) {
                    foreach ($memberIdList as $item) {
                        // 获取剩余可申请后佣数目
                        $remainMoney = D('BillingRecord')->getRemainFxPostComisInvoice($item['CARD_MEMBER_ID'], $item['POST_COMMISSION_ID']);

                        if (abs($remainMoney) < 1) {
                            $response = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->save(array('INVOICE_STATUS' => 3));

                        } else {
                            // 如果收费标准未开票金额大于0，则说明是部分开票, INVOICE_STATUS = 2说明是部分开票
                            $response = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->save(array('INVOICE_STATUS' => 2));
                        }
//                        if ($remainMoney > 0) {
//                            // 如果收费标准未开票金额大于0，则说明是部分开票, INVOICE_STATUS = 2说明是部分开票
//                            $response = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->save(array('INVOICE_STATUS' => 2));
//                        } else {
//                            // INVOICE_STATUS = 3，完成开票
//                            $response = D('erp_post_commission')->where("ID = {$item['POST_COMMISSION_ID']}")->save(array('INVOICE_STATUS' => 3));
//                        }

                        if ($response === false) {
                            break;
                        }
                    }
                }
            }
        }

        return $response;
    }

    public function ajaxRemainInvoicePayAmount() {
        $billingRecordId = intval($_GET['billing_record_id']);
        $scaleType = intval($_GET['scale_type']);
        $data = array();
        if ($billingRecordId) {
            $remainAmount = D('PaymentRecord')->getRemainPayAmount($billingRecordId);
            if ($remainAmount > 0) {
                $data['remain_amount'] = $remainAmount;
                if ($scaleType == 2) { // 如果是分销确定是否关联发票号
                    $invoiceCount = D('erp_commission_invoice_detail')->where("BILLING_RECORD_ID = {$billingRecordId} AND INVOICE_STATUS != 9")->count();
                    if ($invoiceCount) {
                        $data['contain_member'] = 1;  // 该开票记录下挂有会员
                    } else {
                        $data['contain_member'] = -1; // 该开票记录下没有会员
                    }
                }

                ajaxReturnJSON(true, g2u('获取剩余回款金额成功'), $data);
            } else {
                ajaxReturnJSON(false, g2u('该发票已经全部回款'));
            }
        }
        ajaxReturnJSON(false, g2u('获取剩余开票金额失败'));
    }
}
/* End of file FinancialAction.class.php */
/* Location: ./Lib/Action/FinancialAction.class.php */