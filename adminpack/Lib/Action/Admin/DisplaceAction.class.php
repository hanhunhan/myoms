<?php

/**
 * 置换控制器
 */
class DisplaceAction extends ExtendAction {

    /**
     * 【提交工作流】权限
     */
    const NON_CASH_COST_COMMIT = 500;

    /**
     * 查询采购申请FlowID的SQL
     */
    const DISPLACE_FLOWID_SQL = <<<DISPLACE_FLOWID_SQL
        SELECT ID
        FROM ERP_FLOWS T
        WHERE T.FLOWSETID = 31
          AND T.RECORDID = %d
DISPLACE_FLOWID_SQL;

    /**
     * 售卖列表SQL
     */
    const SALE_MANAGE_LIST_SQL = <<<SALE_MANAGE_LIST
        SELECT
            a.ID,
            a.STATUS,
            a.INVOICE_STATUS,
            a.APPLY_USER_ID,
            a.APPLY_TIME,
            a.APPLY_REASON,
            u.name AS APPLY_USERNAME,
            a.buyer
        FROM ERP_DISPLACE_APPLYLIST a
        LEFT JOIN erp_users u ON u.id = a.APPLY_USER_ID
SALE_MANAGE_LIST;

    const SALE_MANAGE_DETAIL_SQL = <<<SALE_MANAGE_DETAIL_SQL
        SELECT
            d.id,
            d.list_id,
            d.amount,
            RTRIM(to_char(d.money,'fm99999999990.99'),'.') AS money,
            w.brand,
            w.model,
            w.product_name,
            w.source,
            w.inbound_status as status,
            w.alarmtime,
            w.livetime,
            w.num,
            RTRIM(to_char(w.price,'fm99999999990.99'),'.') AS price,
            i.contract_no,
            p.projectname AS project_name,
            w.changetime AS DAMAGETIME
        FROM ERP_DISPLACE_APPLYDETAIL d
        LEFT JOIN ERP_DISPLACE_WAREHOUSE w ON w.id = d.did
        LEFT JOIN ERP_DISPLACE_REQUISITION r ON r.id = w.dr_id
        LEFT JOIN ERP_INCOME_CONTRACT i ON i.id = r.contract_id
        LEFT JOIN erp_case c ON w.case_id = c.id
        LEFT JOIN erp_project p ON p.id = c.project_id
SALE_MANAGE_DETAIL_SQL;

    const EXPORT_SALE_MANAGE_DETAIL_SQL = <<<EXPORT_SALE_MANAGE_DETAIL_SQL
SELECT
            d.id,
            d.list_id,
            d.amount,
            d.money,
            w.brand,
            w.model,
            w.product_name,
            w.source,
            w.inbound_status,
            w.alarmtime,
            w.livetime,
            w.num,
            w.price,
            i.contract_no,
            p.projectname AS project_name,
            w.changetime AS DAMAGETIME,
            l.status
        FROM ERP_DISPLACE_APPLYDETAIL d
        LEFT JOIN ERP_DISPLACE_WAREHOUSE w ON w.id = d.did
        LEFT JOIN ERP_DISPLACE_REQUISITION r ON r.id = w.dr_id
        LEFT JOIN ERP_INCOME_CONTRACT i ON i.id = r.contract_id
        LEFT JOIN erp_case c ON w.case_id = c.id
        LEFT JOIN erp_project p ON p.id = c.project_id
        LEFT JOIN erp_displace_applylist l ON l.id = d.list_id
EXPORT_SALE_MANAGE_DETAIL_SQL;





    /*合并当前模块的URL参数*/
    private $_merge_url_param = array();

    private $city = 0;
    private $uid = 0;
    private $_tab_number = 7;

    private $typeParam = array(); //1：售卖、 2：内部领用  3：报损
    /**
     * 非现金支付类型工作流
     */
    const NON_CASH_COST_TYPE = 'feifuxianchengbenshenqing';

    //构造函数
    public function __construct() {
        // 权限映射表
        $this->authorityMap = array(
            'sub_displace' => array(
                'yg'=>849,
                'hd'=>850,
            ),
            'submit_flow' => array(
                'shoumai' =>873,
                'neibulingyong' => 873,
                'baosun' => 873,
            ),
            'commit_change_apply' =>array(
                'default' =>874,
            )
        );

        parent::__construct();

        //TAB URL参数
        $this->_merge_url_param['prjid'] = !empty($_GET['prjid']) ? intval($_GET['prjid']) : 0;
        !empty($_GET['TAB_NUMBER']) ? $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']) : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = intval($_GET['CASEID']) : '';
        !empty($_GET['CASE_TYPE']) ? $this->_merge_url_param['CASE_TYPE'] = strip_tags($_GET['CASE_TYPE']) : '';
        !empty($_GET['purchase_id']) ? $this->_merge_url_param['purchase_id'] = intval($_GET['purchase_id']) : '';
        !empty($_GET['RECORDID']) ? $this->_merge_url_param['RECORDID'] = intval($_GET['RECORDID']) : '';
        !empty($_GET['CASEID']) ? $this->_merge_url_param['CASEID'] = intval($_GET['CASEID']) : '';
        !empty($_GET['TAB_NUMBER']) ? $this->_merge_url_param['TAB_NUMBER'] = intval($_GET['TAB_NUMBER']) : '';
        !empty($_GET['is_from']) ? $this->_merge_url_param['is_from'] = strip_tags($_GET['is_from']) : '';
        !empty($_GET['operate']) ? $this->_merge_url_param['operate'] = strip_tags($_GET['operate']) : '';
        //!empty($_GET['typeParam']) ? $this->_merge_url_param['typeParam'] = strip_tags($_GET['typeParam']) : ''; //typeparam 类型

        $this->uid = intval($_SESSION['uinfo']['uid']); //用户ID
        $this->city = intval($_SESSION['uinfo']['city']); //城市ID
        $this->deptId = intval($_SESSION['uinfo']['deptid']); //部门ID

        //初始化参数
        $this->typeParam = array(
            'shoumai'=>array(
                'type'=>1,//类型值
                'opreateButton'=>true,//是否展现操作按钮
                'tag'=>'售卖明细', //页签名称
                'isInvoice'=>true,
            ),
            'neibulingyong'=>array(
                'type'=>2,
                'opreateButton'=>false,
                'tag'=>'内部领用明细',
                'isInvoice'=>false,
            ),
            'baosun'=>array(
                'type'=>3,
                'opreateButton'=>false,
                'tag'=>'报损明细',
                'isInvoice'=>false,
            ),
        );

    }


    /**
     * 置换汇总管理
     * +----------------------------------------------------------
     * @param none
     * +----------------------------------------------------------
     * @return none
     */
    public function displaceApply(){

        //返回格式
        $result = array(
            'status'=>0,
            'msg'=>'',
            'forward'=>'',
            'data'=>null,
        );

        //接收参数
        $caseType = $this->_merge_url_param['CASE_TYPE']; //业务类型
        $prjId = $this->_merge_url_param['prjid']; //项目ID
        $caseId = $this->_merge_url_param['CASEID']; //案列ID
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : 0; //展现形式
        $faction = isset($_GET['faction']) ? trim($_GET['faction']) : ''; //操作行为
        $postId = isset($_POST['ID']) ? intval($_POST['ID']) : 0; //采购单ID

        //案例MODEL
        $caseModel = D('ProjectCase');
        $displaceModel = D('Displace');

        //添加置换单
        if (!empty($_POST) && $faction == 'saveFormData' && $postId == 0) {
            $requisition = array();
            //获取案列ID
            $caseInfo = $caseModel->get_info_by_pid($prjId, $caseType);
            $caseId = !empty($caseInfo[0]['ID']) ? intval($caseInfo[0]['ID']) : 0;

            $requisition['CASE_ID'] = $caseId;
            $requisition['REASON'] = u2g($_POST['REASON']);
            $requisition['USER_ID'] = $this->uid;
            $requisition['DEPT_ID'] = $this->deptId;
            $requisition['APPLY_TIME'] = date('Y-m-d H:i:s');
            $requisition['END_TIME'] = $this->_post('END_TIME');
            $requisition['PRJ_ID'] = $prjId;
            $requisition['CONTRACT_ID'] = $this->_post('CONTRACT_ID');
            $requisition['CITY_ID'] = $this->city;
            $requisition['STATUS'] = 0; //未提交

            $insertId = M('Erp_displace_requisition')->add($requisition);

            if ($insertId > 0) {
                $result['status'] = 1;
                $result['msg'] = '亲,置换申请添加成功!';
                $result['forward'] = U('Displace/displaceApply', $this->_merge_url_param);
                //日志
                userLog()->writeLog( $insertId, $_SERVER["REQUEST_URI"],  '置换申请添加成功', serialize($requisition));
            } else {
                $result['msg'] = '亲,置换申请添加失败！';
                $result['forward'] = U('Displace/displaceApply', $this->_merge_url_param);
                userLog()->writeLog($insertId, $_SERVER["REQUEST_URI"],  '置换申请添加失败', serialize($requisition));
            }

            //返回结果集
            $result['msg'] = g2u($result['msg']);
            die(@json_encode($result));

        } else if (!empty($_POST) && $faction == 'saveFormData' && $postId > 0) { //修改置换单

            //判断状态
            $currentRequisiton = $displaceModel->getDisplaceById($postId, array('CASE_ID,STATUS'));

            //只有未提交的才能编辑
            if (is_array($currentRequisiton) && !empty($currentRequisiton) &&
                $currentRequisiton[0]['STATUS'] != 0
            ) {
                $result['msg'] = '亲，“未提交”的置换申请才能编辑哦！';
                $result['forward'] = U('Displace/displaceApply', $this->_merge_url_param);
            } else {
                $requisition = array();

                $requisition['REASON'] = u2g($_POST['REASON']);
                $requisition['USER_ID'] = $this->uid;
                $requisition['DEPT_ID'] = $this->deptId;
                $requisition['APPLY_TIME'] = date('Y-m-d H:i:s');
                $requisition['END_TIME'] = $this->_post('END_TIME');
                $requisition['PRJ_ID'] = $prjId;
                $requisition['CONTRACT_ID'] = $this->_post('CONTRACT_ID');
                $requisition['CITY_ID'] = $this->city;
                $requisition['STATUS'] = $this->_post('STATUS');

                $updateRet = M('Erp_displace_requisition')
                    ->where('ID = ' . $postId)->save($requisition);

                if ($updateRet !== false) {
                    $result['status'] = 1;
                    $result['forward'] = U('Displace/displaceApply', $this->_merge_url_param);
                } else {
                    $result['status'] = 0;
                    $result['msg'] = '亲，修改失败，请检查数据！';
                    $result['forward'] = U('Displace/displaceApply', $this->_merge_url_param);
                }
            }

            //返回结果集
            $result['msg'] = g2u($result['msg']);
            die(@json_encode($result));

        } else if ($faction === 'delData') { //删除记录

            $delId = intval($_GET['ID']); //删除ID

            //判断状态
            $currentRequisiton = $displaceModel->getDisplaceById($delId, array('CASE_ID,STATUS'));

            if (is_array($currentRequisiton) && !empty($currentRequisiton) &&
                $currentRequisiton[0]['STATUS'] != 0
            ) {
                $result['msg'] = '亲，“未提交”的置换申请才能删除哦！';
                $result['forward'] = U('Displace/displaceApply', $this->_merge_url_param);
            }

            if ($delId > 0) {

                //删除明细
                $delDisplaceRet = $displaceModel->delDisplaceById($delId);

                if ($delDisplaceRet) {
                    $result['status'] = 'success';
                    $result['msg'] = g2u('亲，删除成功!');
                } else {
                    $result['msg'] = g2u('亲，删除失败,请重试!');
                }
            }

            //输出结果
            die(@json_encode($result));

        } else { //列表展现
            Vendor('Oms.Form');
            $form = new Form();

            //获取where条件
            $condWhere = "CASE_ID = '" . $caseId . "'";
            if (!$caseId) {
                $caseInfo = $caseModel->get_info_by_pid($prjId, $caseType);
                $caseId = !empty($caseInfo[0]['ID']) ? intval($caseInfo[0]['ID']) : 0;
                $condWhere = "CASE_ID = '" . $caseId . "'";
            }

            $form = $form->initForminfo(204)->where($condWhere);
            //初始化表格
            $form->SQLTEXT = '(SELECT L.*,M.TOTAL_MONEY FROM ERP_DISPLACE_REQUISITION L LEFT JOIN
(SELECT SUM(NUM * PRICE) AS TOTAL_MONEY,DR_ID FROM ERP_DISPLACE_WAREHOUSE GROUP BY DR_ID) M ON L.ID = M.DR_ID)';

            //字段展现
            $form = $form->setMyField('CONTRACT_ID', 'LISTSQL', 'SELECT ID,CONTRACT_NO FROM ERP_INCOME_CONTRACT WHERE DISPLACE IN(1,2) AND CASE_ID = ' . $caseId, FALSE); //displace 部分置换，完全置换
            $requisitionStatus = $displaceModel->get_conf_requisition_status();
            $form = $form->setMyField('STATUS', 'LISTCHAR', array2listchar($requisitionStatus), TRUE);
            $form = $form->setMyField('PRJ_ID', 'LISTSQL', 'SELECT ID,PROJECTNAME FROM ERP_PROJECT WHERE ID = ' . $prjId, FALSE);
            $form = $form->setMyField('USER_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', FALSE);

            if ($showForm >= 1) {
                $form = $form->setMyFieldVal('PRJ_ID', $prjId, TRUE);
                $form = $form->setMyFieldVal('USER_ID', $this->uid, TRUE);

                if($showForm==3){ //添加 隐藏申请时间
                    $form->setMyField('APPLY_TIME','FORMVISIBLE',0,false);
                }
            } else {
                //状态为0时
                $form->EDITCONDITION = '%STATUS% == 0';
                $form->DELCONDITION = '%STATUS% == 0';
            }

            $childrenData = array(array('置换明细', U('/Displace/displaceDetail', $this->_merge_url_param)));
            //按钮前置
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap,array(),$caseType);  // 权限前置
            //表格渲染
            $formHtml = $form->setChildren($childrenData)->getResult();
            $this->assign('form', $formHtml);

            $this->assign('isShowOptionBtn', $this->isShowOptionBtn($caseId));
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
            $this->assign('paramUrl', $this->_merge_url_param);
            $this->assign('caseType', $caseType); //业务类型
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
            $this->display('displaceApply');
        }

    }


    /**
     * 置换明细管理
     * +----------------------------------------------------------
     * @param none
     * +----------------------------------------------------------
     * @return none
     */
    public function displaceDetail(){

        //返回格式
        $result = array(
            'status'=>0,
            'msg'=>'',
            'data'=>null,
        );

        //获取参数
        $postId = isset($_POST['ID']) ? intval($_POST['ID']) : 0; //明细ID
        $faction = isset($_GET['faction']) ? strip_tags($_GET['faction']) : ''; //执行参数
        $showForm = isset($_GET['showForm']) ? intval($_GET['showForm']) : '';  //展现形式
        $displaceRequisitionId = isset($_GET['parentchooseid']) ?
            intval($_GET['parentchooseid']) : 0;  //置换汇总ID
        $caseType = $this->_merge_url_param['CASE_TYPE']; //业务类型
        $prjId = $this->_merge_url_param['prjid']; //项目ID
        $caseId = $this->_merge_url_param['CASEID']; //案列ID

        //置换单MODEL
        $displaceModel = D('Displace');
        $caseModel = D('ProjectCase');

        //添加置换明细
        if (!empty($_POST) && $faction == 'saveFormData' && $postId == 0) {
            $displaceWarehouse = array();

            //获取案列ID
            $caseInfo = $caseModel->get_info_by_pid($prjId, $caseType);
            $caseId = !empty($caseInfo[0]['ID']) ? intval($caseInfo[0]['ID']) : 0;

            $displaceWarehouse['DR_ID'] = $displaceRequisitionId; //置换单ID
            $displaceWarehouse['BRAND'] = u2g(strip_tags($_POST['BRAND'])); //品牌
            $displaceWarehouse['MODEL'] = u2g(strip_tags($_POST['MODEL'])); //型号
            $displaceWarehouse['PRODUCT_NAME'] = u2g(strip_tags($_POST['PRODUCT_NAME'])); //品名
            $displaceWarehouse['NUM'] = intval($_POST['NUM']); //置换数量
            $displaceWarehouse['PRICE'] = floatval($_POST['PRICE']); //置换价
            $displaceWarehouse['SOURCE'] = u2g(strip_tags($_POST['SOURCE'])); //来源
            $displaceWarehouse['LIVETIME'] = $_POST['LIVETIME']; //来源
            $displaceWarehouse['STATUS'] = 0; //未审核
            $displaceWarehouse['ADD_USERID'] = $this->uid; //添加人
            $displaceWarehouse['INBOUND_NUM'] = intval($_POST['NUM']); //在库数量
            $displaceWarehouse['INBOUND_STATUS'] = 1; //未入库状态
            $displaceWarehouse['INVOICE_STATUS'] = 1; //未开票
            $displaceWarehouse['ALARMTIME'] = $_POST['ALARMTIME']; //提醒时间
            $displaceWarehouse['CASE_ID'] = intval($caseId);
            $displaceWarehouse['ADD_TIME'] = date('Y-m-d H:i:s');
            $displaceWarehouse['CITY_ID'] = $this->city;


            //添加置换明细信息
            $insertId = M('Erp_displace_warehouse')->add($displaceWarehouse);

            if ($insertId) {
                $result['status'] = 1;
                $result['msg'] = '亲，置换明细添加成功！';
            } else {
                $result['msg'] = '亲，置换明细添加失败，请重试！';
            }

            //返回结果集
            $result['msg'] = g2u($result['msg']);
            die(@json_encode($result));

        } else if ($faction == 'saveFormData' && $postId > 0) { //更新数据

            //获取案列ID
            $caseInfo = $caseModel->get_info_by_pid($prjId, $caseType);
            $caseId = !empty($caseInfo[0]['ID']) ? intval($caseInfo[0]['ID']) : 0;

            //判断状态
            $currentRequisiton = $displaceModel->getDisplaceById($postId, array('CASE_ID,STATUS'));

            if (is_array($currentRequisiton) && !empty($currentRequisiton) &&
                $currentRequisiton[0]['STATUS'] != 0
            ) {
                $result['msg'] = '亲，“未提交”的置换申请明细才能编辑哦！';
                $result['forward'] = U('Displace/displaceDetail', $this->_merge_url_param);
            }

            $displaceWarehouse = array();
            $displaceWarehouse['DR_ID'] = $displaceRequisitionId; //置换单ID
            $displaceWarehouse['BRAND'] = u2g(strip_tags($_POST['BRAND'])); //品牌
            $displaceWarehouse['MODEL'] = u2g(strip_tags($_POST['MODEL'])); //型号
            $displaceWarehouse['PRODUCT_NAME'] = u2g(strip_tags($_POST['PRODUCT_NAME'])); //品名
            $displaceWarehouse['NUM'] = intval($_POST['NUM']); //置换数量
            //$displaceWarehouse['INBOUND_NUM'] = intval($_POST['NUM']); //在库数量
            $displaceWarehouse['PRICE'] = floatval($_POST['PRICE']); //置换价
            $displaceWarehouse['SOURCE'] = u2g(strip_tags($_POST['SOURCE'])); //来源
            $displaceWarehouse['LIVETIME'] = $_POST['LIVETIME']; //来源
            //$displaceWarehouse['STATUS'] = $_POST['STATUS']; //审核状态
            $displaceWarehouse['ADD_USERID'] = $this->uid; //添加人
            $displaceWarehouse['ALARMTIME'] = $_POST['ALARMTIME']; //提醒时间
            $displaceWarehouse['CASE_ID'] = intval($caseId);

            //更新置换明细信息
            $updateRet = M('Erp_displace_warehouse')
                ->where('ID = ' . $postId)->save($displaceWarehouse);

            if ($updateRet !== false) {
                $result['status'] = 1;
                $result['msg'] = '亲，修改成功!';
            } else {
                $result['msg'] = '亲，修改失败!';
            }

            //返回结果集
            $result['msg'] = g2u($result['msg']);
            die(@json_encode($result));

        } if ($faction == 'delData') { //删除记录

            $delId = intval($_GET['ID']); //删除ID

            //判断状态
            $currentRequisiton = $displaceModel->getDisplaceDetailById($delId, array('CASE_ID,STATUS'));

            if (is_array($currentRequisiton) && !empty($currentRequisiton) &&
                $currentRequisiton[0]['STATUS'] != 0
            ) {
                $result['msg'] = '亲，“未提交”的置换明细才能删除哦！';
            }

            if ($delId > 0) {

                //删除明细
                $delDisplaceRet = $displaceModel->delDisplaceDetailById($delId);

                if ($delDisplaceRet) {
                    $result['status'] = 'success';
                    $result['msg'] = g2u('亲，删除成功!');
                } else {
                    $result['status'] = 'error';
                    $result['msg'] = g2u('亲，删除失败,请重试!');
                }
            }

            //输出结果
            die(@json_encode($result));

        } else { //数据展现

            Vendor('Oms.Form');
            $form = new Form(); //初始化

            $condWhere = " DR_ID = '" . $displaceRequisitionId . "'"; //条件
            $form = $form->initForminfo(210)->where($condWhere);

            //获取案列ID
            $caseInfo = $caseModel->get_info_by_pid($prjId, $caseType);
            $caseId = !empty($caseInfo[0]['ID']) ? intval($caseInfo[0]['ID']) : 0;

            //字段展现
            $requisitionStatus = $displaceModel->get_conf_list_status();
            $form = $form->setMyField('INBOUND_STATUS', 'LISTCHAR', array2listchar($requisitionStatus), TRUE);
            $form = $form->setMyField('CASE_ID', 'LISTSQL', 'SELECT C.ID,PROJECTNAME FROM ERP_PROJECT P INNER JOIN ERP_CASE C ON P.ID = C.PROJECT_ID WHERE C.ID = ' . $caseId, FALSE);
            $form = $form->setMyField('ADD_USERID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', FALSE);

            //展现为列表
            if($showForm == 0){
                //状态为0时
                $form->EDITCONDITION = '%STATUS% == 0';
                $form->DELCONDITION = '%STATUS% == 0';

                //未提交的时候才可以添加
                $displaceStatus = $displaceModel->getDisplaceById($displaceRequisitionId,array('CASE_ID','STATUS'));
                if($displaceStatus[0]['STATUS'] != 0)
                    $form->ADDABLE = 0;
            }else{
                $form = $form->setMyFieldVal('CASE_ID', $caseId, TRUE);
                $form = $form->setMyFieldVal('ADD_USERID', $this->uid, TRUE);
                if($showForm==3){ //添加 隐藏申请时间
                    $form->setMyField('ADD_TIME','FORMVISIBLE',0,false);
                }
                if($showForm==1){
                    $form->setMyfield('ADD_TIME','EDITABLE',0,TRUE);
                }
            }

            // 如果是编辑或新增状态，则预先获取费用类型列表
            //todo 下拉展现列表
            if ($showForm == 1 || ($showForm == 3 && empty($faction))) {
                if (!empty($_REQUEST['CASE_TYPE'])) {
                    $this->assign('product_name_autocomplete', '1');  // 品名可以联想
                } else {
                    $this->assign('product_name_autocomplete', '-1');  // 品名不可以联想
                }
            }

            //审核后编辑删除按钮不显示
            $RequisitionStatus = M("Erp_displace_requisition")->where("ID=".$displaceRequisitionId)->getField('STATUS');
            if($RequisitionStatus != 0){
                $form->EDITABLE = '0';
                $form->DELABLE = '0';
            }
            $formHtml = $form->getResult();
            $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
            $this->assign('form', $formHtml);
            $this->assign('tabs', $this->getTabs($this->_merge_url_param['TAB_NUMBER'], $this->_merge_url_param));
            $this->display('displaceDetail');
        }
    }


    /**
     * +----------------------------------------------------------
     * 置换申请是否符合提交申请
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function checkDisplaceDetailById() {

        //返回格式
        $result = array(
            'status'=>0,
            'msg'=>'',
            'forward'=>'',
            'data'=>null,
        );


        //置换ID
        $drId = !empty($_GET['drId']) ? intval($_GET['drId']) : 0;

        //数据检测
        if(!$drId){
            $result['msg'] = g2u('亲，请选择置换申请单!');
            die(@json_encode($result));
        }

        //明细数据
        $displaceWarehouse = M('Erp_displace_warehouse')->where('DR_ID = ' . $drId)->select();

        if(!$displaceWarehouse){
            $result['msg'] = g2u('亲，无置换明细,无法提交置换申请！');
        }
        else{
            $result['status'] = 1;
            $result['msg'] = g2u('亲，提交置换申请成功！');
        }


        //var_dump($result);
        //返回结果
        die(@json_encode($result));
    }

    /**
     * +----------------------------------------------------------
     * 置换仓库
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function wareHouse(){
        //接收参数
        $showForm = isset($_REQUEST['showForm'])?intval($_REQUEST['showForm']):0;

        $displaceModel = D('Displace');

        Vendor('Oms.Form');
        $form = new Form(); //初始化

        $form = $form->initForminfo(211);

        $sql = "SELECT A.ID,A.BRAND,A.MODEL,A.PRODUCT_NAME,A.SOURCE,A.INBOUND_STATUS AS INBOUND_STATUS,A.ALARMTIME,
                A.LIVETIME,DECODE(SUBSTR(A.PRICE,1,1),'.','0'||A.PRICE,A.PRICE) AS PRICE,
                A.NUM,I.CONTRACT_NO AS CONTRACT_ID,P.PROJECTNAME AS PROJECT_NAME,
                TO_CHAR(A.CHANGETIME,'YYYY-MM-DD ') DAMAGETIME,A.INBOUND_TIME,A.UPDATE_TIME,
                GETDISPLACEORDERID(A.ALARMTIME, A.LIVETIME, A.ID, A.INBOUND_STATUS) AS ORDER_ID
                FROM ERP_DISPLACE_WAREHOUSE A
                LEFT JOIN ERP_CASE C  ON A.CASE_ID = C.ID
                LEFT JOIN ERP_DISPLACE_REQUISITION R ON R.ID = A.DR_ID
                LEFT JOIN ERP_INCOME_CONTRACT I ON I.ID = R.CONTRACT_ID
                LEFT JOIN ERP_PROJECT P ON P.ID = C.PROJECT_ID WHERE A.STATUS =2 AND A.CITY_ID =".$this->channelid;;  //status = 2 表示置换申请审核通过

        $form->SQLTEXT = "($sql)";
        $form->orderField = "ORDER_ID DESC";

        $displaceStatus = $displaceModel->get_conf_list_status();
        $form->setMyField("INBOUND_STATUS","LISTCHAR",array2listchar($displaceStatus));
        //按钮权限
        $form->GABTN = "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='export_excel'>导出报表</a>";
        $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='confirm_inbound'>确认入库</a>";
        $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='discount_sale'>折（溢）价售卖</a>";
        $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='inner_use'>公司内部领用</a>";
        $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='damage_report'>报损</a>";

        //屏蔽编辑和删除和添加
        enableRecordReadOnly($form);

        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
        // 向页面传递上次检索条件
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        // 添加搜索条件
        $this->assign('filter_sql',$form->getFilterSql());
        // 添加排序条件
        $this->assign('sort_sql',$form->getSortSql());
        $tab_num = !empty($this->_merge_url_param['TAB_NUMBER']) ?
            $this->_merge_url_param['TAB_NUMBER'] : $this->_tab_number;
        $this->assign('tabs', $this->getTabs($tab_num, $this->_merge_url_param));

        //页面展现
        $this->display('displace_warehouse');
    }

    /**
     * +----------------------------------------------------------
     * 置换仓库确认入库
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function confirmInbound(){

        //返回对象
        $result = array(
            'status'=>0,
            'msg'=>'',
            'data'=>null,
        );

        //验证参数
        $fId =  $_REQUEST["fId"];
        $numList = $_REQUEST["num"];

        $errorStr = '';
        foreach($fId as $key=>$val){

            if(intval($numList[$key])==0){
                $errorStr .= '第' . ($key + 1) . '行，亲,请填写相应的入库数量' . "\n";
                continue;
            }

            $displaceNum = M('Erp_displace_warehouse')
                ->field('NUM')
                ->where('ID = ' . $val)
                ->find();

            //如果超出置换池数量
            if($numList[$key] > $displaceNum['NUM']){
                $errorStr .= '第' . ($key + 1) . '行，亲,入库数量大于实际置换数量!' . "\n";
                continue;
            }
        }

        //数据验证结束
        if(!empty($errorStr)){
            $result['msg'] = $errorStr;
            die(json_encode(g2u($result)));
        }

        //入库操作
        D()->startTrans();
        foreach($numList as $key=>$num){

            $data = array();

            //获取原始数据
            $queryRet = M("Erp_displace_warehouse")
                ->where("id =".$fId[$key])
                ->select();

            $numOld = $queryRet[0]['NUM']; //未入库数量
            //如果相等入库
            if($num == $numOld){
                $data['INBOUND_TIME'] = date('Y-m-d H:i:s'); //入库时间
                $data['INBOUND_STATUS'] = 2; //已入库状态
                $data['INBOUND_USERID'] = $this->uid; //入库人
                $res = M("Erp_displace_warehouse")
                    ->where("id =".$fId[$key])
                    ->save($data);

            //如果小于置换申请数量
            }else if($num < $numOld){
                $data = $queryRet[0];
                $data['INBOUND_TIME'] = date('Y-m-d H:i:s'); //入库时间
                $data['INBOUND_STATUS'] = 2; //入库状态：已入库
                $data['INBOUND_USERID'] = $this->uid; //入库人
                $data['PARENTID'] = $fId[$key];
                $data['NUM'] = $num; //入库数量

                //时间转换
                $data['LIVETIME'] = oracle_date_format($data['LIVETIME']);
                $data['ALARMTIME'] = oracle_date_format($data['ALARMTIME']);
                $data['ADD_TIME'] = oracle_date_format($data['ADD_TIME']);
                unset($data['ID']);

                $resLess = M("Erp_displace_warehouse")
                    ->add($data);

                $numNew = $numOld - $num; //剩余数量
                $updateRet = D()->query("update erp_displace_warehouse set NUM ='{$numNew}' where ID =".$fId[$key]);
            }

            if($res===false || $resLess===false || $updateRet===false){
                $result['msg'] = g2u('亲，入库失败，请重试!');
                D()->rollback();
                die(json_encode($result));
            }
        }
        //提交
        D()->commit();

        $result['status'] = 1;
        $result['msg'] = g2u('入库成功!');
        die(json_encode($result));
    }

    /**
     * @param null $form
     */
    private function enableRecordReadOnly(&$form = null) {
        if (empty($form)) {
            return;
        }

        $form->EDITABLE = 0;  // 不能编辑
        $form->ADDABLE = 0;  // 不能新增
        $form->DELABLE = 0;  // 不能删除
    }

    /**
     * 售卖管理
     */
    public function saleMange() {

        //接收参数
        $typeOpreate = isset($_REQUEST['typeParam'])?trim($_REQUEST['typeParam']):'';
        $faction = isset($_REQUEST['faction'])?trim($_REQUEST['faction']):'';


        if ($faction == 'delData') { //删除记录

            $delId = intval($_GET['ID']); //删除ID

            //判断状态
            $currentApplyStatus = D("InboundUse")->getApplyListStatusById($delId);

            if (is_array($currentApplyStatus) && !empty($currentApplyStatus) &&
                $currentApplyStatus[0]['STATUS'] != 0
            ) {
                $result['msg'] = '亲，“未提交”的申请才能删除哦！';
                $result['forward'] = U('Displace/saleMange', $this->_merge_url_param);
            }

            if ($delId > 0) {

                D()->startTrans();

                //更新库存值
                $updateApplyRet = D("InboundUse")->updateInboundUseById($delId);

                //删除明细
                $delApplyRet = D("InboundUse")->delDisplaceApplyById($delId);

                if ($delApplyRet !== false && $updateApplyRet!==false) {
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
        $form = new Form(); //初始化

        $form = $form->initForminfo(213);
        enableRecordReadOnly($form);  // 记录只能查看
        $form->DELABLE = -1;  //放开删除
        $form->DELCONDITION = '%STATUS% == 0';

        $where = " WHERE A.TYPE = " . $this->typeParam[$typeOpreate]['type'] ." AND A.CITY_ID = ".$this->city;  // todo

        $form->SQLTEXT = sprintf("(%s%s)", self::SALE_MANAGE_LIST_SQL, $where);;

        if($this->typeParam[$typeOpreate]['opreateButton']) {
            $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='change_sale'>变更售卖</a>";
            $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='apply_invoice'>申请开票</a>";
        }
        //提交工作流
        $form->GABTN .= "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='submit_flow'>提交工作流</a>";

        if(!$this->typeParam[$typeOpreate]['isInvoice']) {
            $form->setMyField('INVOICE_STATUS', 'GRIDVISIBLE', '0', FALSE);
            $form->setMyField('INVOICE_STATUS', 'FORMVISIBLE', '0', FALSE);
        }

        //报损和内部领用查看买家不显示
        if($typeOpreate == "baosun" || $typeOpreate == "neibulingyong"){
            $form->setmyfield('BUYER','GRIDVISIBLE',0);
            $form->setmyfield('BUYER','FORMVISIBLE',0);
            $form->setmyfield('BUYER','SORT',0);
            $form->setmyfield('BUYER','FILTER',0);

        }
        // 子页面
        $form->setChildren(array(
            array($this->typeParam[$typeOpreate]['tag'], U('Displace/saleManageDetail', $this->_merge_url_param))
        ));
        $typeParam = $caseType = !empty($_REQUEST['typeParam']) ? $_REQUEST['typeParam'] : 'default';
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap, array(),$typeParam);
        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
        $this->assign('typeOpreate', $typeOpreate);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        // 添加搜索条件
        $this->assign('filter_sql',$form->getFilterSql());
        // 添加排序条件
        $this->assign('sort_sql',$form->getSortSql());
        $tab_num = !empty($this->_merge_url_param['TAB_NUMBER']) ?
        $this->_merge_url_param['TAB_NUMBER'] : $this->_tab_number;

        $this->assign('tabs', $this->getTabs($tab_num, $this->_merge_url_param));

        $this->display('sale_manage');
    }

    /**
     * 售卖管理的明细列表
     */
    public function saleManageDetail() {

        //接收参数
        $typeOpreate = isset($_REQUEST['typeParam'])?trim($_REQUEST['typeParam']):'';
        $parentChooseId = $_REQUEST['parentchooseid'];

        Vendor('Oms.Form');
        $form = new Form(); //初始化

        $form->initForminfo(214);
        $sql = self::SALE_MANAGE_DETAIL_SQL;
        $form->FKFIELD = 'LIST_ID';
        $form->SQLTEXT = "($sql)";
        $type =M("Erp_displace_applylist")->where("ID=".$parentChooseId)->getField("TYPE");
        $form->setMyField('NUM','GRIDVISIBLE',0);
        //1=>售卖，2=>内部领用 3=>报损 4=>变更售卖
        if($type == 3){
            $form->setMyField('MONEY','GRIDVISIBLE',0);
        }

        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
		// 向页面传递上次检索条件
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->display('sale_manage_detail');
    }

    /**
     * 获取售卖明细
     */
    public function getApplyDetail() {
        $listId = $_REQUEST['list_id'];
        if (intval($listId) <= 0) {
            ajaxReturnJSON(false, g2u('缺少参数'));
        }

        try {
            $response = array();
            $dbResult = D('DisplaceApply')->getSaleDetailList($listId);
            if ($dbResult !== false) {
                $response['list'] = $dbResult;
                $response['total_money'] = getTotalMoney($dbResult, 'AMOUNT', 'MONEY');
                ajaxReturnJSON(1, '获取售卖明细成功', $response);
            } else {
                ajaxReturnJSON(0, '获取售卖明细失败');
            }
        } catch (Exception $e) {
            ajaxReturnJSON(0, $e->getMessage());
        }
    }

    /**
     * 做售卖变更
     */
    public function doSaleChange() {
        $post = $_POST;
        $post['city_id'] = $this->channelid; //赋值城市

        if (notEmptyArray($post)) {
            D()->startTrans();
            $dbResult = D('DisplaceApply')->saveSaleChange($post);
            if ($dbResult !== false) {
                D()->commit();
                ajaxReturnJSON(1, '申请变更成功', $dbResult);
            } else {
                D()->rollback();
                ajaxReturnJSON(0, '申请变更失败');
            }
        } else {
            ajaxReturnJSON(0, '缺失参数');
        }
    }

    /**
     * 售卖变更管理
     */
    public function saleChangeManage() {
        // 删除售卖变更
        $faction = $_REQUEST['faction'];
        if ($faction == 'delData') {
            $id = $_REQUEST['ID'];
            if (intval($id) <= 0) {
                ajaxReturnJSON(0, '缺少参数');
            }

            try {
                D()->startTrans();
                $msg = '';
                $dbResult = D('DisplaceApply')->deleteSaleChange($id, $msg);
                if ($dbResult !== false) {
                    D()->commit();
                    $response['status'] = 'success';
                    $response['msg'] = g2u('删除成功');
                } else {
                    D()->rollback();
                    $msg = empty($msg) ? '删除失败' : $msg;
                    $response['status'] = 'error';
                    $response['msg'] = g2u($msg);
                }

            } catch (Exception $e) {
                D()->rollback();
                $response['status'] = 'error';
                $response['msg'] = g2u('服务器内部错误');
            }

            echo json_encode($response);
            exit;
        }

        Vendor('Oms.Form');
        $form = new Form(); //初始化
        $displaceModel = D('Displace');
        $form = $form->initForminfo(213);
        $form->setMyField('BUYER', 'GRIDVISIBLE', -1)
            ->setMyField('BUYER', 'FORMVISIBLE', -1)
             ->setMyField('INVOICE_STATUS', 'GRIDVISIBLE', 0)
             ->setMyField('INVOICE_STATUS', 'FORMVISIBLE', 0);
        $form->EDITABLE = 0;  // 不能编辑
        $form->DELABLE = -1;  // 可以删除
        $form->DELCONDITION = '%STATUS% == 0';
        $where = " WHERE a.status = 5 ";
        $where = " WHERE a.type = 4 and a.city_id = ".$this->channelid;  // todo
        $form->SQLTEXT = sprintf("(%s%s)", self::SALE_MANAGE_LIST_SQL, $where);
        $form->GABTN = "<a href= 'javascript:;' class = 'syn_contract_system btn btn-info btn-sm' id='commit_change_apply'>提交变更申请</a>";

        // 子页面
        $form->setChildren(array(
            array('售卖变更明细', U('Displace/saleManageDetail', $this->_merge_url_param))
        ));
        $requisitionStatus = $displaceModel->get_conf_requisition_status();
        $form->setMyField('STATUS', 'LISTCHAR', array2listchar($requisitionStatus), TRUE);
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);
        $formHtml = $form->getResult();
        $this->assign('form', $formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        // 添加搜索条件
        $this->assign('filter_sql',$form->getFilterSql());
        // 添加排序条件
        $this->assign('sort_sql',$form->getSortSql());
        $tab_num = !empty($this->_merge_url_param['TAB_NUMBER']) ?
            $this->_merge_url_param['TAB_NUMBER'] : $this->_tab_number;
        $this->assign('tabs', $this->getTabs($tab_num, $this->_merge_url_param));


        $this->display('sale_manage');
    }

    /**
     * 导出置换资源记录
     */
    public function export() {
        $sql = self::EXPORT_SALE_MANAGE_DETAIL_SQL;
        $sql = '(' . $sql . ')';
        $sql = " SELECT * FROM {$sql} WHERE 1 = 1";

        if ($_GET['filter']) {
            $sql .= sprintf(" %s ", $_GET['filter']);
        }

        if ($_GET['sort']) {
            $sql .= sprintf(" %s ", $_GET['sort']);
        }
        $records = D()->query($sql);
        $dataFormat = array(
            'ID' => array(
                'name' => '编号'
            ),
            'PROJECT_NAME' => array(
                'name' => '项目名称'
            ),
            'BRAND' => array(
                'name' => '品牌'
            ),
            'MODEL' => array(
                'name' => '型号'
            ),
            'PRODUCT_NAME' => array(
                'name' => '品名'
            ),
            'SOURCE' => array(
                'name' => '来源'
            ),
            'PRICE' => array(
                'name' => '单价'
            ),
            'NUM' => array(
                'name' => '数量'
            ),
            'ALARMTIME' => array(
                'name' => '报警时间'
            ),
            'LIVETIME' => array(
                'name' => '有效时间'
            ),
            'DAMAGETIME' => array(
                'name' => '报损时间'
            )
        );

        $this->initExport($objPHPExcel, $objActSheet, '置换资源售卖记录列表', self::DEFAULT_EXCEL_COLUMN_WIDTH, self::DEFAULT_EXCEL_ROW_HEIGHT);
        $row = 1;
        $this->commonExportAction($objActSheet, $records, $row, '置换资源售卖记录列表', $dataFormat, array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        ));
        ob_end_clean();
        ob_start();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=" . '置换资源售卖记录列表' . date("YmdHis") . ".xls");
        header("Content-Transfer-Encoding:binary");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }

    /**
     * 导出置换仓库记录
     */
    public function exportDisplace() {

        //获取数据的sql语句
        $sql = "SELECT * FROM (SELECT A.ID,A.BRAND,A.MODEL,A.PRODUCT_NAME,A.SOURCE,A.INBOUND_STATUS AS INBOUND_STATUS,
                TO_CHAR(A.ALARMTIME,'YYYY-MM-DD') NEW_ALARMTIME,A.ALARMTIME,
                TO_CHAR(A.LIVETIME,'YYYY-MM-DD') NEW_LIVETIME,A.LIVETIME,
                A.INBOUND_TIME,A.UPDATE_TIME,
                DECODE(SUBSTR(A.PRICE,1,1),'.','0'||A.PRICE,A.PRICE) AS PRICE,
                to_number(A.NUM) AS NUM,I.CONTRACT_NO AS CONTRACT_ID,P.PROJECTNAME AS PROJECT_NAME,
                TO_CHAR(A.CHANGETIME,'YYYY-MM-DD') DAMAGETIME,
                TO_CHAR(A.INBOUND_TIME,'YYYY-MM-DD HH24:mi:ss') NEW_INBOUND_TIME,
                TO_CHAR(A.UPDATE_TIME,'YYYY-MM-DD HH24:mi:ss') NEW_UPDATE_TIME,
                GETDISPLACEORDERID(A.ALARMTIME, A.LIVETIME, A.ID, A.INBOUND_STATUS) AS ORDER_ID
                FROM ERP_DISPLACE_WAREHOUSE A
                LEFT JOIN ERP_DISPLACE_REQUISITION  R ON R.ID = A.DR_ID
                LEFT JOIN ERP_INCOME_CONTRACT I ON I.ID = R.CONTRACT_ID
                LEFT JOIN ERP_CASE C  ON A.CASE_ID = C.ID
                LEFT JOIN ERP_PROJECT P ON P.ID = C.PROJECT_ID WHERE A.STATUS =2 AND R.CITY_ID =".$this->channelid. ") WHERE 1=1 ";

        //过滤条件
        if ($_GET['filter'])
            $sql .= sprintf(" %s ", $_GET['filter']);

        //排序条件
        if ($_GET['sort'])
            $sql .= sprintf(" %s ", $_GET['sort']);

        $records = D()->query($sql);

        $inboundStatus = D("Displace")->get_conf_list_status(); //获取在库状态

        $dataFormat = array(
            'ID' => array(
                'name' => '编号'
            ),
            'CONTRACT_ID' => array(
                'name' => '合同编号'
            ),
            'PROJECT_NAME' => array(
                'name' => '项目名称'
            ),
            'BRAND' => array(
                'name' => '品牌'
            ),
            'MODEL' => array(
                'name' => '型号'
            ),
            'PRODUCT_NAME' => array(
                'name' => '品名'
            ),
            'SOURCE' => array(
                'name' => '货源'
            ),
            'PRICE' => array(
                'name' => '单价'
            ),
            'NUM' => array(
                'name' => '数量',
                'dataType'=>'number',
            ),
            'NEW_ALARMTIME' => array(
                'name' => '报警时间'
            ),
            'NEW_LIVETIME' => array(
                'name' => '有效时间'
            ),
            'NEW_INBOUND_TIME' => array(
                'name' => '入库时间'
            ),
            'NEW_UPDATE_TIME' => array(
                'name' => '出库时间'
            ),
            'INBOUND_STATUS' => array(
                'name' => '物品状态',
                'map' => $inboundStatus,
            ),
        );

        $this->initExport($objPHPExcel, $objActSheet, '置换资源仓库记录列表', self::DEFAULT_EXCEL_COLUMN_WIDTH, self::DEFAULT_EXCEL_ROW_HEIGHT);
        $row = 1;
        $this->commonExportAction($objActSheet, $records, $row, '置换资源仓库记录列表', $dataFormat, array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        ));

        ob_end_clean();
        ob_start();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename=" . '置换资源仓库记录列表' . date("YmdHis") . ".xls");
        header("Content-Transfer-Encoding:binary");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }


    /**
     * 获取选中置换仓库明细
     */
    public function  ajaxGetDisplaceData(){
        //返回对象
        $response = array(
            'status' => false,
            'msg' => "",
            'total' => 0,
            'list' => array()
        );

        //获取数据
        $act = trim($_POST['act_name']); //操作行为
        $DisplaceIds = $_POST['displace_ids']; //置换ID

        if(notEmptyArray($DisplaceIds)){
            $DisplaceIdStr = sprintf('(%s)', implode(',', $DisplaceIds));
            $sql = <<<DISPLACE_SQL
                SELECT A.ID,
                       R.CONTRACT_ID AS CONTRACT,
                       P.PROJECTNAME,
                       A.BRAND,
                       A.MODEL,
                       A.PRODUCT_NAME,
                       A.SOURCE,
                       RTRIM(to_char(A.PRICE,'fm99999999990.99'),'.') AS PRICE,
                       A.NUM,
                       A.INBOUND_STATUS
                FROM ERP_DISPLACE_WAREHOUSE A
                LEFT JOIN ERP_DISPLACE_REQUISITION R ON R.ID = A.DR_ID
                LEFT JOIN ERP_CASE C ON A.CASE_ID = C.ID
                LEFT JOIN ERP_PROJECT P ON P.ID = C.PROJECT_ID
                WHERE A.ID IN {$DisplaceIdStr}
DISPLACE_SQL;
            $dbDisplaceList = D()->query($sql);

            $mapDisplaceList = array();

            //循环判断
            foreach($dbDisplaceList as $key=>$displace ){
                if($displace['INBOUND_STATUS'] != 2){
                    ajaxReturnJSON(false, g2u(sprintf('编号为%d的置换物品，状态不是“已入库”,不能进行该操作！', $displace['ID'])));
                }
                if($act == "discount_sale"){
                    if($key < count($dbDisplaceList) -1 ){
                        if($dbDisplaceList[$key]['CONTRACT'] !== $dbDisplaceList[$key + 1]['CONTRACT'] ||
                            $dbDisplaceList[$key]['PROJECTNAME'] !== $dbDisplaceList[$key + 1]['PROJECTNAME']){
                            ajaxReturnJSON(false, g2u("只有相同的项目名称和合同才能进行售卖"));
                        }
                    }
                }
                if($displace['NUM'] == 0){
                    ajaxReturnJSON(false, g2u(sprintf('编号为%d的置换物品，库存数量为0，不能提交进行该操作！', $displace['ID'])));
                }

                //合同号
                $displace['CONTRACT'] = M("Erp_income_contract")->where("ID=".$displace['CONTRACT'])->getField('CONTRACT_NO');
                $mapDisplaceList[] = $displace;
                $response['total'] += 1;
                $response['total_money'] += round($displace['PRICE'] * $displace['NUM'],2);
            }

            $response['status'] = true;
            $response['list'] = $mapDisplaceList;
        }

        ajaxReturnJSON(true, g2u('success'), g2u($response));
    }

    /**
     * 入库物品折（溢）价售卖 状态1
     * 内部领用 状态2
     * 报损 状态3
     */
    public function ajaxPostInboundUse(){

        //返回对象
        $response = array(
            'status'=>0,
            'msg'=>'',
            'flowType'=>null,
            'flowId'=>'',
        );

        //参数接受
        $displaceData = $_REQUEST['displace_data']; //插入申请明细
        $inboundUse = $_REQUEST['Inbound_status']; //状态

        //数据验证
        $errorStr = '';
        if($displaceData['list']){
            foreach($displaceData['list'] as $key=>$val){
                $sql = 'select num,price from erp_displace_warehouse where id = ' . $val['id'];
                $queryRet = D()->query($sql);

                if(empty($queryRet)){
                    $errorStr = '亲，提交失败，请重试!';
                    break;
                }

                if(intval($val['amount'])<=0 || floatval($val['money'])<=0){
                    $errorStr = '亲，金额或者数量不能为空并且不能小于0!';
                    break;
                }

                if($queryRet[0]['NUM'] && $queryRet[0]['NUM'] < $val['amount']){
                    $errorStr = '亲，请核对提交数量!';
                    break;
                }
            }
        }

        if($errorStr) {
            $response['msg'] = g2u($errorStr);
            die(json_encode($response));
        }

        $flag = false; //操作标示

        D()->startTrans(); //事务开始
        $insertData = array();
        $insertData['APPLY_TIME'] = date('Y-m-d H:i:s');
        $insertData['APPLY_USER_ID'] = $this->uid;
        $insertData['TYPE'] =  $inboundUse;  //工作流类型
        $insertData['APPLY_REASON'] = u2g($displaceData['reason']);
        $insertData['STATUS'] = 0; //状态未提交
        $insertData['BUYER'] = u2g($displaceData['buyer']); //买家
        $insertData['CITY_ID'] = $this->city;

        $insertId = M("Erp_displace_applylist")
            ->add($insertData);

        if($insertId > 0){
            foreach ($displaceData['list'] as $displaceSale){
                //插入明细数据
                $itemData['DID'] = $displaceSale['id'];
                $itemData['LIST_ID'] = $insertId;
                $itemData['AMOUNT'] = $displaceSale['amount'];
                $itemData['MONEY'] = $displaceSale['money'];

                $insertDetailId = M("Erp_displace_applydetail")
                    ->add($itemData);
                if ($insertDetailId === false ) {
                    break;
                }

                //其他业务操作,库存做相应的减少
                $sql = 'UPDATE ERP_DISPLACE_WAREHOUSE SET NUM = NUM - ' . $displaceSale['amount'] . ' WHERE ID = ' . $displaceSale['id'];

                $dbResult = M("Erp_displace_warehouse")->query($sql);
                if ($dbResult === false ) {
                    break;
                }
            }

        }

        $flowDisplayTypePY = D("InboundUse")->get_flow_displace_type(); //获取TYPE类型

        //返回结果集
        if(!$insertId || $dbResult === false){
            D()->rollback();
            $response['msg'] = g2u("亲，操作失败，请重试！");
        }else{
            D()->commit();
            $response['status'] = 1;
            $response['msg'] = g2u("亲，操作成功！");
            $response['flowTypePinYin'] = $flowDisplayTypePY[$inboundUse];
            $response['flowId'] = $insertId;
        }
        echo json_encode($response);
    }


    /**
     * 获取开票金额
     */
    function getTotalMoney() {

        //返回结果集
        $response = array(
            'status'=>0,
            'msg'=>'',
            'data'=>null,
        );

        $listId = intval($_REQUEST['list_id']);

        $totalMoney = D('Displace')->getSaleTotal($listId);

        if($totalMoney){
            $response['status'] = 1;
            $response['data']['totalMoney'] = $totalMoney;
        }

        die(@json_encode($response));
    }

    /**
     * 售卖申请开票
     */
    public function applyInvoice() {
        $request = $_POST['request'];

        //验证数据

        $listId = intval($request['list_id']);

        $sql = 'SELECT INVOICE_STATUS FROM ERP_DISPLACE_APPLYLIST WHERE ID = '. $listId;
        $saleListInfo = D()->query($sql);

        if(!$saleListInfo || $saleListInfo[0]['INVOICE_STATUS']!=1){
            ajaxReturnJSON(0, '开票状态必须是未申请状态！');
        }

        try {
            D()->startTrans();
            $dbResult = D('DisplaceApply')->doAddInvoice($request);
            if ($dbResult !== false) {
                D()->commit();
                ajaxReturnJSON(1, '提交开票申请成功', $dbResult);
            } else {
                D()->rollback();
                ajaxReturnJSON(0, '服务器内部错误');
            }
        } catch (Exception $e) {
            D()->rollback();
            ajaxReturnJSON(0, '服务器内部错误');
        }
    }

    /**
     * 提交售卖变更申请
     */
    public function commitSaleChange() {
        $request = $_POST['request'];
        try {
            $dbResult = D('DisplaceApply')->getApplyList($request);
            if (notEmptyArray($dbResult)) {
                ajaxReturnJSON(1, '申请成功', $dbResult);
            } else {
                ajaxReturnJSON(0, '服务器内部错误');
            }
        } catch (Exception $e) {
            ajaxReturnJSON(0, '服务器内部错误');
        }
    }

    /**
     * +----------------------------------------------------------
     * 查询工作流是否符合提交申请
     * +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function checkDisplaceFlow() {

        //返回对象
        $return = array(
            'status'=>0,
            'msg'=>'',
            'data'=>null,
        );


        $dId = !empty($_GET['dId']) ? intval($_GET['dId']) : 0;

        //数据验证
        if(!$dId){
            $return['msg'] = '请选择其中一条记录';
            die(json_encode($return));
        }

        //状态核对
        $sql = 'SELECT STATUS FROM ERP_DISPLACE_APPLYLIST WHERE ID = ' . $dId;
        $queryRet = D()->query($sql);

        if($queryRet[0]['STATUS']==0){
            $return['status'] = 1;
            $return['msg'] = g2u('提交申请成功');
        }

        die(@json_encode($return));
    }


    /**
     * 获取置换申请的工作流ID
     */
    public function getFlowId() {
        $response = array(
            'status' => false,
            'message' => '参数错误',
            'data' => ''
        );
        $displaceId = $_REQUEST['displaceId'];
        if (intval($displaceId) > 0) {
            try {
                $result = D()->query(sprintf(self::DISPLACE_FLOWID_SQL, $displaceId));
                if (notEmptyArray($result)) {
                    $response['status'] = true;
                    $response['message'] = '获取工作流ID成功';
                    $response['data'] = $result[0]['ID'];
                } else {
                    $response['message'] = '该置换申请尚未发起工作流!';
                }
            } catch (Exception $e) {
                $response['status'] = false;
                $response['message'] = $e->getMessage();
            }
        }

        echo json_encode(g2u($response));
    }

}

/* End of file PurchaseAction.class.php */