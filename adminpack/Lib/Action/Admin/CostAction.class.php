<?php
/**
 * Created by PhpStorm.
 * User: superkemi
 * Date: 2015/10/25
 * Time: 16:11
 */


/**
 *
 * ERP_TRANSFER
 * STATE state = 0 暂未提交审核，1表示审核过程中,state = 2 表示审核通过 ,  state = 3 表示审核未通过
 * CSTATE state = 0 表示未确认，state = 1 表示部分确认，state = 2 表示完成确认
 * ERP_TRANSFEROUT_DETAIL
 * STATE state = 1 未确认，state = 2 已经确认
 *
 */
class CostAction extends ExtendAction{
    /**
     * 成本划拨提交审核权限
     */
    const COMMIT_ALLOCATION = 404;

    /**
     * 成本划拨编辑权限
     */
    const EDIT_ALLOCATION = 403;

    /**
     * 成本划拨删除权限
     */
    const DEL_ALLOCATION = 405;

    /**
     * 确认划入权限
     */
    const CONFIRM_ALLOCATION = 406;

    //构造函数
    private $_merge_url_param = array();

    public function __construct()
    {
        parent::__construct();
        // 权限映射表
        $this->authorityMap = array(
            'commit_allocation' => self::COMMIT_ALLOCATION,
            'edit_allocation' => self::EDIT_ALLOCATION,
            'del_allocation' => self::DEL_ALLOCATION,
            'confirm_allocation' => self::CONFIRM_ALLOCATION,
            'allocation_examine'=>616,
        );
        $this->model = new Model();
        //用户ID
        $this->uid = intval($_SESSION['uinfo']['uid']);
        //城市
        $this->city = intval($_SESSION['uinfo']['city']);

        //TAB URL参数
        $this->_merge_url_param['FLOWTYPE'] = isset($_GET['FLOWTYPE']) ? $_GET['FLOWTYPE'] : 13;
        $this->_merge_url_param['CASEID'] = isset($_GET['CASEID']) ?  $_GET['CASEID'] : 0;
        $this->_merge_url_param['RECORDID'] = isset($_GET['RECORDID']) ?  $_GET['RECORDID'] : 0;
        $this->_merge_url_param['flowId'] = isset($_GET['flowId']) ?  $_GET['flowId'] : 0;
		!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;

    }

    /**
    +----------------------------------------------------------
     * 成本划拨 -  申请
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function allocationApply()
    {
		//加载项目公共文件
        load('@.project_common');

        //项目id和业务类型id  案例类型ID
        $projectid = intval($_REQUEST['project_id']);
        $project_type_id = intval($_REQUEST['project_type_id']);

        //是否扣非
        $koufei  = intval($_REQUEST['koufei']);

        $this->project_case_auth($projectid);//项目业务权限判断
        //申请的操作步骤
        $step = isset($_REQUEST['step'])?intval($_REQUEST['step']):1;

        //操作行为
        $act = $this->_post('act');

        /*****如果是做编辑操作*******/
        $transfer_id = intval($_REQUEST['transfer_id']);

        if($transfer_id){
            $transfer_obj  = M("erp_transfer");
            $transfer_status = $transfer_obj
                ->field('STATUS')
                ->where('id = ' . $transfer_id)
                ->select();

            //可编辑标示位
            $edit_flag = false;

            //如果有记录  并且审核状态处于未提交状态时可以编辑
            if($transfer_status && ($transfer_status[0]['STATUS'] == 0))
                $edit_flag = true;

            if(!$edit_flag)
                halt_http_referer("对不起，该条记录不能进行编辑!");

            //获取编辑的数据
            $selected_project = null;
            $selected_project = M("erp_transfer")
                ->join("inner join erp_case on erp_case.id = erp_transfer.caseid")
                ->join("inner join erp_transferout_detail on erp_transfer.id = erp_transferout_detail.transfer_id")
                ->field('erp_case.scaletype,erp_case.project_id,erp_transfer.info,erp_transferout_detail.projectid,erp_transferout_detail.outcost,erp_transferout_detail.profit,erp_transferout_detail.fundpoolcost')
                ->where('erp_transfer.id = ' . $transfer_id)
                ->select();

            if($selected_project){

                //公共数据
                $projectid = $selected_project[0]['PROJECT_ID'];
                $project_type_id = $selected_project[0]['SCALETYPE'];
                $transfer_info  = $selected_project[0]['INFO'];

                //步骤 需要业务数据
                $modify_data = null;
                foreach($selected_project as $key=>$val){
                    $modify_data[$key]['PROJECTID'] = $val['PROJECTID'];
                    $modify_data[$key]['OUTCOST'] = $val['OUTCOST'];
                    $modify_data[$key]['PROFIT'] = $val['PROFIT'];
                    $modify_data[$key]['FUNDPOOLCOST'] = $val['FUNDPOOLCOST'];
                }
            }
        }
        /*****如果是做编辑操作*******/

        //保存操作
        if($act=='examine'){
            $ids = trim($this->_post('ids'),",");
            $formdata_str = $_POST['formdata'];
            $koufei = $_POST['koufei'];
            $ids = explode(",",$ids);

            parse_str($formdata_str,$formdata);

            //划拨理由
            $allocation_info = trim($_POST['allocation_info']);

            //返回数据结构
            $return = array(
                'status' => 0,
                'msg' => '',
                'data' => null,
            );

            //数据验证
            //划拨理由验证
            $return_str = '';
            if(!$allocation_info){
                $return_str .= "对不起，划拨的理由必须填写！<br />";
            }

            //验证项目
            if(!$project_type_id || !$projectid){
                $return_str .= "对不起，被划拨的项目未选择！<br />";
            }

            //数据验证
            $flag = false;
            foreach($ids as $key=>$val) {
                //如果收益或自身成本或资金池成本 不为0;
                if ($formdata[$val . '_share_profit'] || $formdata[$val . '_share_cost'] || $formdata[$val . '_share_fundpool_cost']) {
                    $flag = true;
                    break;
                }
            }

            if(!$flag)
                $return_str .= "对不起，划拨的金额不能都为空！<br />";


            if($return_str){
                $return['msg'] = g2u($return_str);
                die(@json_encode($return));
            }

            //业务事务的开始
            $this->model->startTrans();

            //运行成功标识
            $flag = true;

            //获取caseid
            $data = $this->model->table('erp_case')->field("ID")->where("scaletype=$project_type_id and project_id = $projectid")->find();
            $caseid = $data['ID'];

            $date = date("Y-m-d H:i:s",time());
            $insert['CASEID'] = $caseid;
            $insert['APPLYTIME'] = $date;
            //未提交审核
            $insert['STATUS'] = 0;
            //扣非
            $insert['KOUFEI'] = $koufei;
            //添加人
            $insert['ADD_UID'] = $this->uid;
            //划拨说明
            $insert['INFO'] = u2g($allocation_info);

            $allocation_id = $this->model->table('erp_transfer')->add($insert);

            if(!$allocation_id)
                $flag = false;

            //是否存在数据提交
            foreach($ids as $key=>$val) {
                //如果收益或自身成本或资金池成本 不为0;
                if ($formdata[$val . '_share_profit'] || $formdata[$val . '_share_cost'] || $formdata[$val . '_share_fundpool_cost']) {
                    $apply_info = array();
                    $apply_info['PROJECTID'] = $val;
                    $apply_info['OUTCOST'] = $formdata[$val . '_share_cost'];
                    $apply_info['STATUS'] = 1;
                    $apply_info['TRANSFER_ID'] = $allocation_id;
                    $apply_info['PROFIT'] = $formdata[$val . '_share_profit'];
                    $apply_info['FUNDPOOLCOST'] = $formdata[$val . '_share_fundpool_cost'];
                    //添加数据
                    $allocation_detail = $this->model->table('erp_transferout_detail')->add($apply_info);
                    if(!$allocation_detail)
                        $flag = false;
                }
            }

            //结果验证
            if($caseid && $flag){
                $this->model->commit();
                //成功赋值
                $return['status'] = 1;
                $return['data']['caseid'] = $caseid;
                $return['data']['allocation_id'] = $allocation_id;
            }else{
                $return['msg'] = g2u('对不起，提交失败!');
                $this->model->rollback();
            }

            die(@json_encode($return));
        }


        //form对象
        Vendor('Oms.Form');
        $form = new Form();

        //不出现操作列(属性设置)
        $form->setAttribute('NOPERATE',1);

        //划拨操作步骤1
        if($step == 1) {
            $form = $form->initForminfo(148);

            //重新设置sql等属性
            $form->SQLTEXT = "(select distinct A.ID,A.CITY_ID,A.CUSER,A.PROJECTNAME,A.CONTRACT,A.ETIME from ERP_PROJECT A left join ERP_CASE B ON A.id = B.Project_Id where (B.FSTATUS = 2 OR B.FSTATUS = 4) AND A.STATUS != 2 AND A.CITY_ID = {$this->channelid}  AND A.id != $projectid)";

            //设置特有字段的数据转换
            $form->setMyField('CUSER', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', TRUE);
            $form->setMyField('CITY_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_CITY', TRUE);
            $formhtml = $form->getResult();

            //页面渲染
            $this->assign('form', $formhtml);
            //项目ID 项目业务类型
            $this->assign('projectid', $projectid);
            $this->assign('project_type_id', $project_type_id);
            $this->assign('transfer_info',$transfer_info);
            $this->assign('transfer_id',$transfer_id);
            //是否扣非
            $this->assign('koufei',$koufei);

            //页面修改
            $this->assign('modify_data',@json_encode($modify_data));
            $this->display('allocation_apply_1');
        }
        //划拨操作步骤2
        elseif($step == 2){
            //获取项目ID
            $sel_pro_id = $this->_get('allocation_id');
            $allocation_info = $this->_get('info');

            //获取表单
            $form->initForminfo(149)->where("ID in (".$sel_pro_id.")");

            //资金池项目标示
            // 1 表示电商 (起始项目)   电商类型并且是资金池项目
            if($project_type_id==1 && isFundPoolPro($projectid)){
                $bus_project = 1;
            }

            //划入的电商项目标示 (目标项目)
            $sel_pro_id = explode(",",$sel_pro_id);
            if(!empty($sel_pro_id)) {
                foreach ($sel_pro_id as $key=>$val){
                    $sel_pro[$val] = 0;
                    if(isFundPoolPro($val)){
                        $sel_pro[$val] = 1;
                    }
                }
            }

            //设置特有字段的数据转换
            $form = $form->setMyField('CUSER', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', TRUE);
            $form = $form->setMyField('CITY_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_CITY', TRUE);

            //新增字段
            $input_arr = array(
                array('TDNAME' => '分摊利润', 'INPUTNAME' => 'share_profit','TYPE'=>'INPUT'),
                array('TDNAME' => '分摊自身成本', 'INPUTNAME' => 'share_cost','TYPE'=>'INPUT'),
                array('TDNAME' => '分摊资金池成本', 'INPUTNAME' => 'share_fundpool_cost','TYPE'=>'INPUT'),
            );
            $form->addNewTd($input_arr);
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置

            //获取渲染页面
            $formhtml = $form->getResult();

            //页面渲染
            $this->assign('project_id', $projectid);
            $this->assign('project_type_id', $project_type_id);
            //是否扣非
            $this->assign('koufei',$koufei);
            //页面修改
            $this->assign('modify_data', @json_encode($modify_data));
            $this->assign('bus_project', $bus_project);
            $this->assign('allocation_info', $allocation_info);
            $this->assign('bus_sel_pro', @json_encode($sel_pro));
            $this->assign('form', $formhtml);
            $this->display('allocation_apply_2');

        }
    }


    /**
    +----------------------------------------------------------
     * 划拨明细
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function allocationDetails()
    {
        Vendor('Oms.Form');
        $form = new Form();

        $form =  $form->initForminfo(146);
        //SQL重新赋值
        $form->SQLTEXT = '(SELECT A.INFO,A.APPLYTIME,A.CSTATUS,A.STATUS,A.ID AS TID,C.ID,C.CITY_ID,C.CONTRACT,C.PROJECTNAME,C.CUSER,B.SCALETYPE,SUM(D.OUTCOST) AS OUTCOST,SUM(D.PROFIT) as PROFIT,SUM(D.FUNDPOOLCOST) as FUNDPOOLCOST FROM ERP_TRANSFER A LEFT JOIN ERP_CASE B ON A.CASEID = B.ID LEFT JOIN ERP_PROJECT C ON B.PROJECT_ID = C.ID
RIGHT JOIN ERP_TRANSFEROUT_DETAIL D ON D.TRANSFER_ID = A.ID WHERE C.CITY_ID = ' . $this->city  . ' AND ISDEL=0  AND  (A.STATUS = 2 OR (A.STATUS != 2 AND C.CUSER = ' . $this->uid. ')) GROUP BY D.TRANSFER_ID,C.ID,C.CITY_ID,C.CONTRACT,C.PROJECTNAME,C.CUSER,B.SCALETYPE,A.ID,A.APPLYTIME,A.CSTATUS,A.STATUS,A.INFO)';

        //如果是工作流流程中
        if($this->_merge_url_param['RECORDID'] && $this->_merge_url_param['flowId']) {
            $form->SQLTEXT = '(SELECT A.INFO,A.APPLYTIME,A.CSTATUS,A.STATUS,A.ID AS TID,C.ID,C.CITY_ID,C.CONTRACT,C.PROJECTNAME,C.CUSER,B.SCALETYPE,SUM(D.OUTCOST) AS OUTCOST,SUM(D.PROFIT) as PROFIT,SUM(D.FUNDPOOLCOST) as FUNDPOOLCOST FROM ERP_TRANSFER A LEFT JOIN ERP_CASE B ON A.CASEID = B.ID LEFT JOIN ERP_PROJECT C ON B.PROJECT_ID = C.ID
RIGHT JOIN ERP_TRANSFEROUT_DETAIL D ON D.TRANSFER_ID = A.ID WHERE C.CITY_ID = ' . $this->city . ' AND ISDEL=0 AND A.ID = ' . $this->_merge_url_param['RECORDID'] . ' GROUP BY D.TRANSFER_ID,C.ID,C.CITY_ID,C.CONTRACT,C.PROJECTNAME,C.CUSER,B.SCALETYPE,A.ID,A.APPLYTIME,A.CSTATUS,A.STATUS,A.INFO)';
            //按钮
            $form->GABTN = " ";
        }
        //不展现操作行(属性设置)
        $form->setAttribute('NOPERATE',1);

        //添加人
        $form->setMyField('CUSER', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        //城市
        $form->setMyField('CITY_ID', 'LISTSQL','SELECT ID, NAME FROM ERP_CITY', TRUE);
        //业务类型
        $form->setMyField('SCALETYPE', 'LISTSQL','SELECT ID, YEWU FROM ERP_BUSINESSCLASS', TRUE);
        //审核状态
        $form->setMyField('STATUS', 'LISTCHAR', array2listchar(array("0"=>'未提交审核',"1"=>'审核过程中',"2"=>'审核通过',"3"=>'未审核通过')), FALSE);
        //确认状态
        $form->setMyField('CSTATUS', 'LISTCHAR',array2listchar(array("0"=>'未确认',"1"=>'部分确认',"2"=>'完全确认')), FALSE);

        $children_data = array(
            array('划出明细', U('/Cost/showProAllocation',$this->_merge_url_param)),
        );

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
        $formhtml =  $form->setChildren($children_data)->getResult();
        $this->assign('paramUrl', $this->_merge_url_param);
        // 向页面传递上次检索条件
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->assign('form',$formhtml);
        $this->display('allocation_details');

    }


    /**
    +----------------------------------------------------------
     * 划拨流程的操作
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function opAllocation(){
        //操作行为
        $act = trim($_REQUEST["act"]);
        $transfer_id = intval($_REQUEST["transfer_id"]);

        //返回结构
        $return = array(
            'state'=>0,
            'msg'=>'',
            'data'=>null,
        );

        if(!$transfer_id){
            $return['msg'] = g2u('对不起，请选择一条记录操作！');
            die(@json_encode($return));
        }

        //删除操作
        if($act=='del'){
            //查询划拨流程的状态
            $transfer_status = M("erp_transfer")
                ->field("status")
                ->where("id =$transfer_id")
                ->select();

            //只有未提交审核的才能删除
            if($transfer_status && $transfer_status[0]['STATUS']==0) {

                $data['ISDEL'] = 1;
                $ret = M("erp_transfer")
                    ->where("id = $transfer_id")
                    ->save($data);

                if ($ret) {
                    $return['state'] = 1;
                    $return['msg'] = g2u("删除成功");
                }
            }
        }

        die(@json_encode($return));
    }


    /**
    +----------------------------------------------------------
     * 划出明细
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function showProAllocation(){
        //加载项目公共文件
        load('@.project_common');

        //操作行为
        $act = $this->_post("act");

        //确认划拨操作
        if($act=='save_pro_allocation'){

            //返回结构
            $return = array(
                'state'=>0,
                'msg'=>'',
                'data'=>null,
            );

            //表单数据
            $formdata_str = $_POST['formdata'];
            parse_str($formdata_str,$formdata);

            //划拨明细ID
            $allocation_id = $_POST['allocation_id'];

            $detail_obj = M('erp_transferout_detail');
            $transfer_obj = M('erp_transfer');
            $prj_obj = M('erp_project');

            //数据合法性验证
            //1.资金池不能转入非电商[资金池项目]
            //2.状态的判断不能转

            $error_array = array();
            foreach($allocation_id as $key=>$val){
                //获取数据
                $ret = $detail_obj->join('erp_transfer on erp_transferout_detail.transfer_id = erp_transfer.id')
                    ->join("erp_case on erp_case.id = erp_transfer.caseid")
                    ->field("erp_case.cuser,erp_transferout_detail.fundpoolcost,erp_transferout_detail.status as DSTATUS,erp_transfer.status as FSTATUS")
                    ->where("erp_transferout_detail.id = $val")->find();

                //转入项目的业务类型
                $allocation_yewu = $formdata[$val . '_allocation_bc'];
                $allocation_projectid = $formdata[$val . '_PROJECTID'];
                //扣非
                $koufei = $formdata[$val . '_KOUFEI'];

                //资金池不能转入非电商[资金池项目]
                if($ret['FUNDPOOLCOST'] && $allocation_yewu != 1 && isFundPoolPro($allocation_projectid)){
                    $error_array[$val] .=  g2u("编号{$val}资金池不能转入非资金池项目！");
                    continue;
                }

                //确认过的不能再次确认
                if($ret['DSTATUS']==2){
                    $error_array[$val] .=  g2u("编号{$val}已经确认！");
                    continue;
                }

                //审核通过的方可确认
                if($ret['FSTATUS']!=2){
                    $error_array[$val] .=  g2u("编号{$val}审核通过之后方可确认！");
                    continue;
                }

                //判断该项目是否处于执行之中
                $prj_state = $prj_obj->join('inner join erp_case on erp_case.project_id = erp_project.id')
                                                ->where('erp_case.project_id = ' . $allocation_projectid . ' and erp_case.scaletype = ' . $allocation_yewu)
                                                ->field('erp_case.fstatus')
                                                ->select();

                if($prj_state[0]['FSTATUS'] != 2  && $prj_state[0]['FSTATUS'] != 4){
                    $error_array[$val] .=  g2u("编号{$val}项目状态必须是‘执行’或者‘周期结束’状态！");
                    continue;
                }

                //是否拥有查看全部权限
                if(!$this->p_auth_all) {
                    //没有该项目-业务类型下的权限不能操作
                    $query_ret = M("erp_prorole")
                        ->field('ID')
                        ->where("use_id = {$this->uid} and pro_id = $allocation_projectid and erp_id = $allocation_yewu and isvalid = -1")
                        ->select();

                    if (empty($query_ret)) {
                        $yewu_name = getScaleTypeName($allocation_yewu);
                        $error_array[$val] .= g2u("编号{$val},对不起，您没有该项目的{$yewu_name}业务权限！");
                        continue;
                    }
                }
            }

            //错误信息返回
            if($error_array){
                $error_str = implode("<br />",$error_array);
                $return['msg'] = $error_str;
                die(@json_encode($return));
            }

            //业务事务的开始
            //处理各方面数据
            $this->model->startTrans();

            //回滚标示
            $flag = true;
            foreach($allocation_id as $key=>$val) {
                //业务类型
                $allocation_yewu = $formdata[$val . '_allocation_bc'];

                //更新业务类型，更新确认状态
                $data['PROJECT_TYPE_ID'] = intval($allocation_yewu);
                $data['STATUS'] = 2;
                //是否扣非
                $data['KOUFEI'] = $koufei;
                $data['ETIME'] = date("Y-m-d H:i:s",time());

                $ret = $detail_obj->where("id = $val")->save($data);

                if(!$ret){
                    $flag = false;
                    $this->model->rollback();
                    break;
                }

                //获取该条明细的划拨值
                $ret = $detail_obj
                    ->field("projectid,transfer_id,outcost,profit,fundpoolcost,erp_transfer.caseid")
                    ->join("erp_transfer on erp_transferout_detail.transfer_id = erp_transfer.id")
                    ->where("erp_transferout_detail.id=$val")
                    ->find();

                //划拨ID
                $transfer_id = $ret['TRANSFER_ID'];
                //划拨成本值
                $outcost = $ret['OUTCOST'];
                //划拨收益值
                $profit = $ret['PROFIT'];
                //划拨资金池成本
                $fundpoolcost = $ret['FUNDPOOLCOST'];
                //划出案例ID
                $from_caseId = $ret['CASEID'];
                //划入项目ID
                $to_projectid = $ret['PROJECTID'];

                //获取被划拨对象的业务类型
                $from_case_scaletype = M("erp_case")->field("scaletype")
                    ->where("id=$from_caseId")->find();
                $from_case_scaletype = $from_case_scaletype['SCALETYPE'];

                $to_erp_case = M("erp_case")->where("project_id=$to_projectid and scaletype=$allocation_yewu")->find();
                //划入案例ID
                $to_caseId = $to_erp_case['ID'];

                //存在垫资比例的业务类型
                $loan_case = D("ProjectCase")->get_conf_case_Loan();
                $loan_case_arr = array_keys($loan_case);
                //判断是否超过垫资比例
                /*是垫资比例的业务类型 并且 成本大于收益  并且  超过垫资比例*/
                if(in_array($allocation_yewu,$loan_case_arr) && ($current_cost = ($outcost+$fundpoolcost)-$profit)>0 && is_overtop_payout_limit($to_caseId,$current_cost)){
                    $this->model->rollback();
                    $return['msg'] =  g2u("编号{$val},对不起，划拨后成本超过项目的垫资比例或超出费用预算（总费用>开票回款收入*付现成本率）！");
                    die(@json_encode($return));
                }

                //更新划拨流线的状态
                $ret = $detail_obj->where("status=1 and transfer_id=$transfer_id")->find();
                //划拨流线的状态
                $cstatus = 2;  //完全确认
                if(!empty($ret))
                    $cstatus = 1;  //部分确认

                //确认时间
                $ret = $transfer_obj->where("id=$transfer_id")->save(array('CSTATUS'=>$cstatus));

                if(!$ret){
                    $flag = false;
                    $this->model->rollback();
                    break;
                }

                //进入收益表
                if($profit) {
                    //划拨对象
                    $income_info['CASE_ID'] = $to_caseId;
                    $income_info['ENTITY_ID'] = $transfer_id;
                    $income_info['ORG_ENTITY_ID'] = $transfer_id;
                    $income_info['PAY_ID'] = $val;
                    $income_info['ORG_PAY_ID'] = $val;
                    if($allocation_yewu==1)
                        $income_info['INCOME_FROM'] = 19;//成本划拨收益 - 电商
                    else
                        $income_info['INCOME_FROM'] = 21;//成本划拨收益 - 非电商
                    $income_info['INCOME'] = $profit;
                    $income_info['INCOME_REMARK'] = '划拨收益';
                    $income_info['ADD_UID'] = $this->uid;
                    $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());
                    $income_model = D('ProjectIncome');
                    $ret = $income_model->add_income_info($income_info);

                    if(!$ret){
                        $flag = false;
                        $this->model->rollback();
                        break;
                    }

                    //被划拨对象
                    $income_info['CASE_ID'] = $from_caseId;
                    $income_info['INCOME'] = -$profit;
                    $income_info['INCOME_REMARK'] = '被划拨收益';

                    if($from_case_scaletype==1)
                        $income_info['INCOME_FROM'] = 19;//成本划拨收益 - 电商
                    else
                        $income_info['INCOME_FROM'] = 21;//成本划拨收益 - 非电商

                    $ret = $income_model->add_income_info($income_info);

                    if(!$ret){
                        $flag = false;
                        $this->model->rollback();
                        break;
                    }
                }

                //进入支出表
                if($outcost) {
                    //划拨对象
                    $cost_info['CASE_ID'] = $to_caseId;
                    $cost_info['ENTITY_ID'] = $transfer_id;
                    //原始大ID
                    $cost_info['ORG_ENTITY_ID'] = $transfer_id;
                    $cost_info['EXPEND_ID'] = $val;
                    //原始小ID
                    $cost_info['ORG_EXPEND_ID'] = $val;
                    $cost_info['EXPEND_FROM'] = 21;//申请采购
                    $cost_info['FEE'] = $outcost;
                    $cost_info['FEE_REMARK'] = '划拨支出';
                    $cost_info['ISFUNDPOOL'] = false;
                    $cost_info['ADD_UID'] = $this->uid;
                    $cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());
                    $cost_info['INPUT_TAX'] = 0;  //进项税
                    $cost_info['ISKF'] = $koufei; //是否扣非


                    $cost_model = D('ProjectCost');
                    $ret = $cost_model->add_cost_info($cost_info);

                    if(!$ret){
                        $flag = false;
                        $this->model->rollback();
                        break;
                    }


                    //被划拨对象
                    $cost_info['CASE_ID'] = $from_caseId;
                    $cost_info['FEE'] = -$outcost;
                    $cost_info['FEE_REMARK'] = '被划拨支出';
                    $ret = $cost_model->add_cost_info($cost_info);
                    if(!$ret){
                        $flag = false;
                        $this->model->rollback();
                        break;
                    }

                }

                //进入支出表(资金池)
                if($fundpoolcost) {
                    //划拨对象
                    $cost_info['CASE_ID'] = $to_caseId;
                    $cost_info['ENTITY_ID'] = $transfer_id;
                    $cost_info['ORG_ENTITY_ID'] = $transfer_id;
                    $cost_info['EXPEND_ID'] = $val;
                    $cost_info['ORG_EXPEND_ID'] = $val;
                    $cost_info['EXPEND_FROM'] = 21;//申请采购
                    $cost_info['FEE'] = $fundpoolcost;
                    $cost_info['ISFUNDPOOL'] = true;
                    $cost_info['FEE_REMARK'] = '资金池划拨支出';
                    $cost_info['ADD_UID'] = $this->uid;
                    $cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());
                    $cost_info['INPUT_TAX'] = 0;  //进项税
                    $cost_info['ISKF'] = $koufei;  //是否扣非

                    $cost_model = D('ProjectCost');
                    $ret = $cost_model->add_cost_info($cost_info);
                    if(!$ret){
                        $flag = false;
                        $this->model->rollback();
                        break;
                    }

                    //被划拨对象
                    $cost_info['CASE_ID'] = $from_caseId;
                    $cost_info['FEE'] = -$fundpoolcost;
                    $cost_info['FEE_REMARK'] = '资金池被划拨支出';
                    $ret = $cost_model->add_cost_info($cost_info);
                    if(!$ret){
                        $flag = false;
                        $this->model->rollback();
                        break;
                    }
                }

            }

            if($flag){
                $this->model->commit();
                //成功赋值
                $return['state'] = 1;
                $return['msg'] = g2u('划拨确认成功');
            }

            die(@json_encode($return));
        }

        //明细数据展现
        Vendor('Oms.Form');
        $form = new Form();
        //form对象初始化
        $form = $form->initForminfo(161);

        //不展现操作行(设置属性)
        $form->setAttribute('NOPERATE',1);

        //添加人
        $form->setMyField('CUSER', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        //城市
        $form->setMyField('CITY_ID', 'LISTSQL','SELECT ID, NAME FROM ERP_CITY', TRUE);
        //状态
        $form->setMyField('STATUS', 'LISTCHAR', array2listchar(array("1"=>'未确认',"2"=>'已确认')), FALSE);
        //扣非
        $form->setMyField('KOUFEI', 'LISTCHAR', array2listchar(array("1"=>'是',"0"=>'否')), FALSE);
        //业务类型
        $form->setMyField('PROJECT_TYPE_ID', 'LISTSQL', "SELECT ID,YEWU FROM ERP_BUSINESSCLASS", FALSE);

        //如果是工作流流程中
        if($this->_merge_url_param['RECORDID'] && $this->_merge_url_param['flowId']) {
            $form->GABTN = " ";
        }

        //新增字段(展现)
        $input_arr = array(
            array(
                'TDNAME' => '选择业务类型',
                'INPUTNAME' => 'allocation_bc',
                'LISTSQL'=>'SELECT B.ID,B.YEWU FROM ERP_CASE A LEFT JOIN ERP_BUSINESSCLASS B ON A.SCALETYPE = B.ID WHERE (A.SCALETYPE <=4 OR A.SCALETYPE=8) AND A.PROJECT_ID = LISTSQL_VAL',
                'LISTSQL_VAL'=> 'PROJECTID',
                'TYPE'=>'SELECT',
                'SELECTED'=>'PROJECT_TYPE_ID',
            ),
        );
        $form->addNewTd($input_arr);
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // 权限前置
        $formHtml = $form->getResult();

        //页面展现是否可操作权限
        $transferid  = $_REQUEST['parentchooseid'];
        $auth_transfer_data = M("erp_transfer")
                                    ->field("status")
                                    ->where('id = ' . $transferid)
                                    ->select();

        $auth_transfer = false;

        //如果工作流审核通过
        if($auth_transfer_data && $auth_transfer_data[0]['STATUS'] ==2)
            $auth_transfer = true;

        $this->assign('uid',$this->uid);
        $this->assign('auth_transfer',$auth_transfer);
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // 向页面传递上次检索条件
        $this->display('show_pro_allocation');

    }


    /**
    +----------------------------------------------------------
     * 审批意见 - 成本划拨
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

        //权限判断
        //成本划拨类型
        $type = 'chengbenhuabo';
        $auth = $workflow->start_authority($type);

        //工作流ID
        $flowId = isset($_REQUEST['flowId'])?intval($_REQUEST['flowId']):0;
        //业务关联ID
        $recordId = isset($_REQUEST['RECORDID'])?intval($_REQUEST['RECORDID']):0;
        //項目案列ID
        $caseId = isset($_REQUEST['CASEID'])?intval($_REQUEST['CASEID']):0;

        $transfer_obj = M("erp_transfer");

        //行为验证
        $ret = $transfer_obj->field("status,add_uid,caseid")->where("id=$recordId")->find();

        $fstatus = $ret['STATUS'];
        $add_uid = $ret['ADD_UID'];
        //获取项目案例ID
        if(!$caseId)
            $caseId = $ret['CASEID'];

        if($flowId){
            //状态判断
            if($fstatus != 1){
                js_alert("对不起，该划拨请求状态不对");
            }
            //todo:权限判断  暂无

        }
        else{
            //状态判断
            if($fstatus != 0){
                js_alert("对不起，该划拨请求状态不对！");
            }

            //权限判断
            //if($this->uid != $add_uid){
            //    $this->error("对不起，您没有权限操作该工作！");
            //}

        }

        //做相关的办理操作
        if($flowId)
        {
            //工作流保存
            if($_REQUEST['savedata']){
                //转交下一步
                if($_REQUEST['flowNext']){
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str){
                        js_alert('办理成功',U('Flow/workStep'));
                    }else{
                        js_alert('办理失败');
                    }
                    //同意操作
                }elseif($_REQUEST['flowPass']){

                    $str = $workflow->passWorkflow($_REQUEST);
                    if($str){
                        js_alert('同意成功',U('Flow/workStep'));
                    }else{
                        js_alert('同意失败');
                    }
                    //否则操作
                }elseif($_REQUEST['flowNot']){

                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str){
                        js_alert('否决成功',U('Flow/workStep'));
                    }else{
                        js_alert('否决失败');
                    }
                    //终止操作
                }elseif($_REQUEST['flowStop']){

                    $auth = $workflow->flowPassRole($flowId);
                    if(!$auth){
                        js_alert('未经过必经角色');exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str){
                        js_alert('备案成功',U('Flow/workStep'));
                    }else{
                        js_alert('备案失败');
                    }
                }
                exit;
            }
            //渲染页面
            else{
                //点击操作(未提交)
                $click  = $workflow->nextstep($flowId);
                $form=$workflow->createHtml($flowId);
            }
        }
        //新增操作
        else
        {
            if($_REQUEST['savedata'])
            {
                //创造工作流（caseid 和 recordid 要调整）
                $_REQUEST['type'] = $type;
                $_REQUEST['CASEID'] = $caseId;
                $_REQUEST['RECORDID'] = $recordId;

                //更新状态
                $ret = $workflow->createworkflow($_REQUEST);

                if(!empty($ret))
                {
                    js_alert('提交成功',U('Flow/workStep',$this->_merge_url_param));
                    exit;
                }
                else
                {
                    js_alert('提交失败',U('Cost/opinionFlow',$this->_merge_url_param));
                    exit;
                }
            }
            else
            {
                $auth = $workflow->start_authority($type);
                if(!$auth)
                {
                    js_alert('暂无权限');
                }
                $form = $workflow->createHtml();
            }
        }

        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('current_url', U('Cost/opinionFlow',$this->_merge_url_param));
        $this->display('opinionFlow');
    }
}
?>