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
 * STATE state = 0 ��δ�ύ��ˣ�1��ʾ��˹�����,state = 2 ��ʾ���ͨ�� ,  state = 3 ��ʾ���δͨ��
 * CSTATE state = 0 ��ʾδȷ�ϣ�state = 1 ��ʾ����ȷ�ϣ�state = 2 ��ʾ���ȷ��
 * ERP_TRANSFEROUT_DETAIL
 * STATE state = 1 δȷ�ϣ�state = 2 �Ѿ�ȷ��
 *
 */
class CostAction extends ExtendAction{
    /**
     * �ɱ������ύ���Ȩ��
     */
    const COMMIT_ALLOCATION = 404;

    /**
     * �ɱ������༭Ȩ��
     */
    const EDIT_ALLOCATION = 403;

    /**
     * �ɱ�����ɾ��Ȩ��
     */
    const DEL_ALLOCATION = 405;

    /**
     * ȷ�ϻ���Ȩ��
     */
    const CONFIRM_ALLOCATION = 406;

    //���캯��
    private $_merge_url_param = array();

    public function __construct()
    {
        parent::__construct();
        // Ȩ��ӳ���
        $this->authorityMap = array(
            'commit_allocation' => self::COMMIT_ALLOCATION,
            'edit_allocation' => self::EDIT_ALLOCATION,
            'del_allocation' => self::DEL_ALLOCATION,
            'confirm_allocation' => self::CONFIRM_ALLOCATION,
            'allocation_examine'=>616,
        );
        $this->model = new Model();
        //�û�ID
        $this->uid = intval($_SESSION['uinfo']['uid']);
        //����
        $this->city = intval($_SESSION['uinfo']['city']);

        //TAB URL����
        $this->_merge_url_param['FLOWTYPE'] = isset($_GET['FLOWTYPE']) ? $_GET['FLOWTYPE'] : 13;
        $this->_merge_url_param['CASEID'] = isset($_GET['CASEID']) ?  $_GET['CASEID'] : 0;
        $this->_merge_url_param['RECORDID'] = isset($_GET['RECORDID']) ?  $_GET['RECORDID'] : 0;
        $this->_merge_url_param['flowId'] = isset($_GET['flowId']) ?  $_GET['flowId'] : 0;
		!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;

    }

    /**
    +----------------------------------------------------------
     * �ɱ����� -  ����
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function allocationApply()
    {
		//������Ŀ�����ļ�
        load('@.project_common');

        //��Ŀid��ҵ������id  ��������ID
        $projectid = intval($_REQUEST['project_id']);
        $project_type_id = intval($_REQUEST['project_type_id']);

        //�Ƿ�۷�
        $koufei  = intval($_REQUEST['koufei']);

        $this->project_case_auth($projectid);//��Ŀҵ��Ȩ���ж�
        //����Ĳ�������
        $step = isset($_REQUEST['step'])?intval($_REQUEST['step']):1;

        //������Ϊ
        $act = $this->_post('act');

        /*****��������༭����*******/
        $transfer_id = intval($_REQUEST['transfer_id']);

        if($transfer_id){
            $transfer_obj  = M("erp_transfer");
            $transfer_status = $transfer_obj
                ->field('STATUS')
                ->where('id = ' . $transfer_id)
                ->select();

            //�ɱ༭��ʾλ
            $edit_flag = false;

            //����м�¼  �������״̬����δ�ύ״̬ʱ���Ա༭
            if($transfer_status && ($transfer_status[0]['STATUS'] == 0))
                $edit_flag = true;

            if(!$edit_flag)
                halt_http_referer("�Բ��𣬸�����¼���ܽ��б༭!");

            //��ȡ�༭������
            $selected_project = null;
            $selected_project = M("erp_transfer")
                ->join("inner join erp_case on erp_case.id = erp_transfer.caseid")
                ->join("inner join erp_transferout_detail on erp_transfer.id = erp_transferout_detail.transfer_id")
                ->field('erp_case.scaletype,erp_case.project_id,erp_transfer.info,erp_transferout_detail.projectid,erp_transferout_detail.outcost,erp_transferout_detail.profit,erp_transferout_detail.fundpoolcost')
                ->where('erp_transfer.id = ' . $transfer_id)
                ->select();

            if($selected_project){

                //��������
                $projectid = $selected_project[0]['PROJECT_ID'];
                $project_type_id = $selected_project[0]['SCALETYPE'];
                $transfer_info  = $selected_project[0]['INFO'];

                //���� ��Ҫҵ������
                $modify_data = null;
                foreach($selected_project as $key=>$val){
                    $modify_data[$key]['PROJECTID'] = $val['PROJECTID'];
                    $modify_data[$key]['OUTCOST'] = $val['OUTCOST'];
                    $modify_data[$key]['PROFIT'] = $val['PROFIT'];
                    $modify_data[$key]['FUNDPOOLCOST'] = $val['FUNDPOOLCOST'];
                }
            }
        }
        /*****��������༭����*******/

        //�������
        if($act=='examine'){
            $ids = trim($this->_post('ids'),",");
            $formdata_str = $_POST['formdata'];
            $koufei = $_POST['koufei'];
            $ids = explode(",",$ids);

            parse_str($formdata_str,$formdata);

            //��������
            $allocation_info = trim($_POST['allocation_info']);

            //�������ݽṹ
            $return = array(
                'status' => 0,
                'msg' => '',
                'data' => null,
            );

            //������֤
            //����������֤
            $return_str = '';
            if(!$allocation_info){
                $return_str .= "�Բ��𣬻��������ɱ�����д��<br />";
            }

            //��֤��Ŀ
            if(!$project_type_id || !$projectid){
                $return_str .= "�Բ��𣬱���������Ŀδѡ��<br />";
            }

            //������֤
            $flag = false;
            foreach($ids as $key=>$val) {
                //������������ɱ����ʽ�سɱ� ��Ϊ0;
                if ($formdata[$val . '_share_profit'] || $formdata[$val . '_share_cost'] || $formdata[$val . '_share_fundpool_cost']) {
                    $flag = true;
                    break;
                }
            }

            if(!$flag)
                $return_str .= "�Բ��𣬻����Ľ��ܶ�Ϊ�գ�<br />";


            if($return_str){
                $return['msg'] = g2u($return_str);
                die(@json_encode($return));
            }

            //ҵ������Ŀ�ʼ
            $this->model->startTrans();

            //���гɹ���ʶ
            $flag = true;

            //��ȡcaseid
            $data = $this->model->table('erp_case')->field("ID")->where("scaletype=$project_type_id and project_id = $projectid")->find();
            $caseid = $data['ID'];

            $date = date("Y-m-d H:i:s",time());
            $insert['CASEID'] = $caseid;
            $insert['APPLYTIME'] = $date;
            //δ�ύ���
            $insert['STATUS'] = 0;
            //�۷�
            $insert['KOUFEI'] = $koufei;
            //�����
            $insert['ADD_UID'] = $this->uid;
            //����˵��
            $insert['INFO'] = u2g($allocation_info);

            $allocation_id = $this->model->table('erp_transfer')->add($insert);

            if(!$allocation_id)
                $flag = false;

            //�Ƿ���������ύ
            foreach($ids as $key=>$val) {
                //������������ɱ����ʽ�سɱ� ��Ϊ0;
                if ($formdata[$val . '_share_profit'] || $formdata[$val . '_share_cost'] || $formdata[$val . '_share_fundpool_cost']) {
                    $apply_info = array();
                    $apply_info['PROJECTID'] = $val;
                    $apply_info['OUTCOST'] = $formdata[$val . '_share_cost'];
                    $apply_info['STATUS'] = 1;
                    $apply_info['TRANSFER_ID'] = $allocation_id;
                    $apply_info['PROFIT'] = $formdata[$val . '_share_profit'];
                    $apply_info['FUNDPOOLCOST'] = $formdata[$val . '_share_fundpool_cost'];
                    //�������
                    $allocation_detail = $this->model->table('erp_transferout_detail')->add($apply_info);
                    if(!$allocation_detail)
                        $flag = false;
                }
            }

            //�����֤
            if($caseid && $flag){
                $this->model->commit();
                //�ɹ���ֵ
                $return['status'] = 1;
                $return['data']['caseid'] = $caseid;
                $return['data']['allocation_id'] = $allocation_id;
            }else{
                $return['msg'] = g2u('�Բ����ύʧ��!');
                $this->model->rollback();
            }

            die(@json_encode($return));
        }


        //form����
        Vendor('Oms.Form');
        $form = new Form();

        //�����ֲ�����(��������)
        $form->setAttribute('NOPERATE',1);

        //������������1
        if($step == 1) {
            $form = $form->initForminfo(148);

            //��������sql������
            $form->SQLTEXT = "(select distinct A.ID,A.CITY_ID,A.CUSER,A.PROJECTNAME,A.CONTRACT,A.ETIME from ERP_PROJECT A left join ERP_CASE B ON A.id = B.Project_Id where (B.FSTATUS = 2 OR B.FSTATUS = 4) AND A.STATUS != 2 AND A.CITY_ID = {$this->channelid}  AND A.id != $projectid)";

            //���������ֶε�����ת��
            $form->setMyField('CUSER', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', TRUE);
            $form->setMyField('CITY_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_CITY', TRUE);
            $formhtml = $form->getResult();

            //ҳ����Ⱦ
            $this->assign('form', $formhtml);
            //��ĿID ��Ŀҵ������
            $this->assign('projectid', $projectid);
            $this->assign('project_type_id', $project_type_id);
            $this->assign('transfer_info',$transfer_info);
            $this->assign('transfer_id',$transfer_id);
            //�Ƿ�۷�
            $this->assign('koufei',$koufei);

            //ҳ���޸�
            $this->assign('modify_data',@json_encode($modify_data));
            $this->display('allocation_apply_1');
        }
        //������������2
        elseif($step == 2){
            //��ȡ��ĿID
            $sel_pro_id = $this->_get('allocation_id');
            $allocation_info = $this->_get('info');

            //��ȡ��
            $form->initForminfo(149)->where("ID in (".$sel_pro_id.")");

            //�ʽ����Ŀ��ʾ
            // 1 ��ʾ���� (��ʼ��Ŀ)   �������Ͳ������ʽ����Ŀ
            if($project_type_id==1 && isFundPoolPro($projectid)){
                $bus_project = 1;
            }

            //����ĵ�����Ŀ��ʾ (Ŀ����Ŀ)
            $sel_pro_id = explode(",",$sel_pro_id);
            if(!empty($sel_pro_id)) {
                foreach ($sel_pro_id as $key=>$val){
                    $sel_pro[$val] = 0;
                    if(isFundPoolPro($val)){
                        $sel_pro[$val] = 1;
                    }
                }
            }

            //���������ֶε�����ת��
            $form = $form->setMyField('CUSER', 'LISTSQL', 'SELECT ID,NAME FROM ERP_USERS', TRUE);
            $form = $form->setMyField('CITY_ID', 'LISTSQL', 'SELECT ID,NAME FROM ERP_CITY', TRUE);

            //�����ֶ�
            $input_arr = array(
                array('TDNAME' => '��̯����', 'INPUTNAME' => 'share_profit','TYPE'=>'INPUT'),
                array('TDNAME' => '��̯����ɱ�', 'INPUTNAME' => 'share_cost','TYPE'=>'INPUT'),
                array('TDNAME' => '��̯�ʽ�سɱ�', 'INPUTNAME' => 'share_fundpool_cost','TYPE'=>'INPUT'),
            );
            $form->addNewTd($input_arr);
            $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��

            //��ȡ��Ⱦҳ��
            $formhtml = $form->getResult();

            //ҳ����Ⱦ
            $this->assign('project_id', $projectid);
            $this->assign('project_type_id', $project_type_id);
            //�Ƿ�۷�
            $this->assign('koufei',$koufei);
            //ҳ���޸�
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
     * ������ϸ
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
        //SQL���¸�ֵ
        $form->SQLTEXT = '(SELECT A.INFO,A.APPLYTIME,A.CSTATUS,A.STATUS,A.ID AS TID,C.ID,C.CITY_ID,C.CONTRACT,C.PROJECTNAME,C.CUSER,B.SCALETYPE,SUM(D.OUTCOST) AS OUTCOST,SUM(D.PROFIT) as PROFIT,SUM(D.FUNDPOOLCOST) as FUNDPOOLCOST FROM ERP_TRANSFER A LEFT JOIN ERP_CASE B ON A.CASEID = B.ID LEFT JOIN ERP_PROJECT C ON B.PROJECT_ID = C.ID
RIGHT JOIN ERP_TRANSFEROUT_DETAIL D ON D.TRANSFER_ID = A.ID WHERE C.CITY_ID = ' . $this->city  . ' AND ISDEL=0  AND  (A.STATUS = 2 OR (A.STATUS != 2 AND C.CUSER = ' . $this->uid. ')) GROUP BY D.TRANSFER_ID,C.ID,C.CITY_ID,C.CONTRACT,C.PROJECTNAME,C.CUSER,B.SCALETYPE,A.ID,A.APPLYTIME,A.CSTATUS,A.STATUS,A.INFO)';

        //����ǹ�����������
        if($this->_merge_url_param['RECORDID'] && $this->_merge_url_param['flowId']) {
            $form->SQLTEXT = '(SELECT A.INFO,A.APPLYTIME,A.CSTATUS,A.STATUS,A.ID AS TID,C.ID,C.CITY_ID,C.CONTRACT,C.PROJECTNAME,C.CUSER,B.SCALETYPE,SUM(D.OUTCOST) AS OUTCOST,SUM(D.PROFIT) as PROFIT,SUM(D.FUNDPOOLCOST) as FUNDPOOLCOST FROM ERP_TRANSFER A LEFT JOIN ERP_CASE B ON A.CASEID = B.ID LEFT JOIN ERP_PROJECT C ON B.PROJECT_ID = C.ID
RIGHT JOIN ERP_TRANSFEROUT_DETAIL D ON D.TRANSFER_ID = A.ID WHERE C.CITY_ID = ' . $this->city . ' AND ISDEL=0 AND A.ID = ' . $this->_merge_url_param['RECORDID'] . ' GROUP BY D.TRANSFER_ID,C.ID,C.CITY_ID,C.CONTRACT,C.PROJECTNAME,C.CUSER,B.SCALETYPE,A.ID,A.APPLYTIME,A.CSTATUS,A.STATUS,A.INFO)';
            //��ť
            $form->GABTN = " ";
        }
        //��չ�ֲ�����(��������)
        $form->setAttribute('NOPERATE',1);

        //�����
        $form->setMyField('CUSER', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        //����
        $form->setMyField('CITY_ID', 'LISTSQL','SELECT ID, NAME FROM ERP_CITY', TRUE);
        //ҵ������
        $form->setMyField('SCALETYPE', 'LISTSQL','SELECT ID, YEWU FROM ERP_BUSINESSCLASS', TRUE);
        //���״̬
        $form->setMyField('STATUS', 'LISTCHAR', array2listchar(array("0"=>'δ�ύ���',"1"=>'��˹�����',"2"=>'���ͨ��',"3"=>'δ���ͨ��')), FALSE);
        //ȷ��״̬
        $form->setMyField('CSTATUS', 'LISTCHAR',array2listchar(array("0"=>'δȷ��',"1"=>'����ȷ��',"2"=>'��ȫȷ��')), FALSE);

        $children_data = array(
            array('������ϸ', U('/Cost/showProAllocation',$this->_merge_url_param)),
        );

        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��
        $formhtml =  $form->setChildren($children_data)->getResult();
        $this->assign('paramUrl', $this->_merge_url_param);
        // ��ҳ�洫���ϴμ�������
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));
        $this->assign('form',$formhtml);
        $this->display('allocation_details');

    }


    /**
    +----------------------------------------------------------
     * �������̵Ĳ���
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function opAllocation(){
        //������Ϊ
        $act = trim($_REQUEST["act"]);
        $transfer_id = intval($_REQUEST["transfer_id"]);

        //���ؽṹ
        $return = array(
            'state'=>0,
            'msg'=>'',
            'data'=>null,
        );

        if(!$transfer_id){
            $return['msg'] = g2u('�Բ�����ѡ��һ����¼������');
            die(@json_encode($return));
        }

        //ɾ������
        if($act=='del'){
            //��ѯ�������̵�״̬
            $transfer_status = M("erp_transfer")
                ->field("status")
                ->where("id =$transfer_id")
                ->select();

            //ֻ��δ�ύ��˵Ĳ���ɾ��
            if($transfer_status && $transfer_status[0]['STATUS']==0) {

                $data['ISDEL'] = 1;
                $ret = M("erp_transfer")
                    ->where("id = $transfer_id")
                    ->save($data);

                if ($ret) {
                    $return['state'] = 1;
                    $return['msg'] = g2u("ɾ���ɹ�");
                }
            }
        }

        die(@json_encode($return));
    }


    /**
    +----------------------------------------------------------
     * ������ϸ
    +----------------------------------------------------------
     * @param none
    +----------------------------------------------------------
     * @return none
    +----------------------------------------------------------
     */
    public function showProAllocation(){
        //������Ŀ�����ļ�
        load('@.project_common');

        //������Ϊ
        $act = $this->_post("act");

        //ȷ�ϻ�������
        if($act=='save_pro_allocation'){

            //���ؽṹ
            $return = array(
                'state'=>0,
                'msg'=>'',
                'data'=>null,
            );

            //������
            $formdata_str = $_POST['formdata'];
            parse_str($formdata_str,$formdata);

            //������ϸID
            $allocation_id = $_POST['allocation_id'];

            $detail_obj = M('erp_transferout_detail');
            $transfer_obj = M('erp_transfer');
            $prj_obj = M('erp_project');

            //���ݺϷ�����֤
            //1.�ʽ�ز���ת��ǵ���[�ʽ����Ŀ]
            //2.״̬���жϲ���ת

            $error_array = array();
            foreach($allocation_id as $key=>$val){
                //��ȡ����
                $ret = $detail_obj->join('erp_transfer on erp_transferout_detail.transfer_id = erp_transfer.id')
                    ->join("erp_case on erp_case.id = erp_transfer.caseid")
                    ->field("erp_case.cuser,erp_transferout_detail.fundpoolcost,erp_transferout_detail.status as DSTATUS,erp_transfer.status as FSTATUS")
                    ->where("erp_transferout_detail.id = $val")->find();

                //ת����Ŀ��ҵ������
                $allocation_yewu = $formdata[$val . '_allocation_bc'];
                $allocation_projectid = $formdata[$val . '_PROJECTID'];
                //�۷�
                $koufei = $formdata[$val . '_KOUFEI'];

                //�ʽ�ز���ת��ǵ���[�ʽ����Ŀ]
                if($ret['FUNDPOOLCOST'] && $allocation_yewu != 1 && isFundPoolPro($allocation_projectid)){
                    $error_array[$val] .=  g2u("���{$val}�ʽ�ز���ת����ʽ����Ŀ��");
                    continue;
                }

                //ȷ�Ϲ��Ĳ����ٴ�ȷ��
                if($ret['DSTATUS']==2){
                    $error_array[$val] .=  g2u("���{$val}�Ѿ�ȷ�ϣ�");
                    continue;
                }

                //���ͨ���ķ���ȷ��
                if($ret['FSTATUS']!=2){
                    $error_array[$val] .=  g2u("���{$val}���ͨ��֮�󷽿�ȷ�ϣ�");
                    continue;
                }

                //�жϸ���Ŀ�Ƿ���ִ��֮��
                $prj_state = $prj_obj->join('inner join erp_case on erp_case.project_id = erp_project.id')
                                                ->where('erp_case.project_id = ' . $allocation_projectid . ' and erp_case.scaletype = ' . $allocation_yewu)
                                                ->field('erp_case.fstatus')
                                                ->select();

                if($prj_state[0]['FSTATUS'] != 2  && $prj_state[0]['FSTATUS'] != 4){
                    $error_array[$val] .=  g2u("���{$val}��Ŀ״̬�����ǡ�ִ�С����ߡ����ڽ�����״̬��");
                    continue;
                }

                //�Ƿ�ӵ�в鿴ȫ��Ȩ��
                if(!$this->p_auth_all) {
                    //û�и���Ŀ-ҵ�������µ�Ȩ�޲��ܲ���
                    $query_ret = M("erp_prorole")
                        ->field('ID')
                        ->where("use_id = {$this->uid} and pro_id = $allocation_projectid and erp_id = $allocation_yewu and isvalid = -1")
                        ->select();

                    if (empty($query_ret)) {
                        $yewu_name = getScaleTypeName($allocation_yewu);
                        $error_array[$val] .= g2u("���{$val},�Բ�����û�и���Ŀ��{$yewu_name}ҵ��Ȩ�ޣ�");
                        continue;
                    }
                }
            }

            //������Ϣ����
            if($error_array){
                $error_str = implode("<br />",$error_array);
                $return['msg'] = $error_str;
                die(@json_encode($return));
            }

            //ҵ������Ŀ�ʼ
            //�������������
            $this->model->startTrans();

            //�ع���ʾ
            $flag = true;
            foreach($allocation_id as $key=>$val) {
                //ҵ������
                $allocation_yewu = $formdata[$val . '_allocation_bc'];

                //����ҵ�����ͣ�����ȷ��״̬
                $data['PROJECT_TYPE_ID'] = intval($allocation_yewu);
                $data['STATUS'] = 2;
                //�Ƿ�۷�
                $data['KOUFEI'] = $koufei;
                $data['ETIME'] = date("Y-m-d H:i:s",time());

                $ret = $detail_obj->where("id = $val")->save($data);

                if(!$ret){
                    $flag = false;
                    $this->model->rollback();
                    break;
                }

                //��ȡ������ϸ�Ļ���ֵ
                $ret = $detail_obj
                    ->field("projectid,transfer_id,outcost,profit,fundpoolcost,erp_transfer.caseid")
                    ->join("erp_transfer on erp_transferout_detail.transfer_id = erp_transfer.id")
                    ->where("erp_transferout_detail.id=$val")
                    ->find();

                //����ID
                $transfer_id = $ret['TRANSFER_ID'];
                //�����ɱ�ֵ
                $outcost = $ret['OUTCOST'];
                //��������ֵ
                $profit = $ret['PROFIT'];
                //�����ʽ�سɱ�
                $fundpoolcost = $ret['FUNDPOOLCOST'];
                //��������ID
                $from_caseId = $ret['CASEID'];
                //������ĿID
                $to_projectid = $ret['PROJECTID'];

                //��ȡ�����������ҵ������
                $from_case_scaletype = M("erp_case")->field("scaletype")
                    ->where("id=$from_caseId")->find();
                $from_case_scaletype = $from_case_scaletype['SCALETYPE'];

                $to_erp_case = M("erp_case")->where("project_id=$to_projectid and scaletype=$allocation_yewu")->find();
                //���밸��ID
                $to_caseId = $to_erp_case['ID'];

                //���ڵ��ʱ�����ҵ������
                $loan_case = D("ProjectCase")->get_conf_case_Loan();
                $loan_case_arr = array_keys($loan_case);
                //�ж��Ƿ񳬹����ʱ���
                /*�ǵ��ʱ�����ҵ������ ���� �ɱ���������  ����  �������ʱ���*/
                if(in_array($allocation_yewu,$loan_case_arr) && ($current_cost = ($outcost+$fundpoolcost)-$profit)>0 && is_overtop_payout_limit($to_caseId,$current_cost)){
                    $this->model->rollback();
                    $return['msg'] =  g2u("���{$val},�Բ��𣬻�����ɱ�������Ŀ�ĵ��ʱ����򳬳�����Ԥ�㣨�ܷ���>��Ʊ�ؿ�����*���ֳɱ��ʣ���");
                    die(@json_encode($return));
                }

                //���»������ߵ�״̬
                $ret = $detail_obj->where("status=1 and transfer_id=$transfer_id")->find();
                //�������ߵ�״̬
                $cstatus = 2;  //��ȫȷ��
                if(!empty($ret))
                    $cstatus = 1;  //����ȷ��

                //ȷ��ʱ��
                $ret = $transfer_obj->where("id=$transfer_id")->save(array('CSTATUS'=>$cstatus));

                if(!$ret){
                    $flag = false;
                    $this->model->rollback();
                    break;
                }

                //���������
                if($profit) {
                    //��������
                    $income_info['CASE_ID'] = $to_caseId;
                    $income_info['ENTITY_ID'] = $transfer_id;
                    $income_info['ORG_ENTITY_ID'] = $transfer_id;
                    $income_info['PAY_ID'] = $val;
                    $income_info['ORG_PAY_ID'] = $val;
                    if($allocation_yewu==1)
                        $income_info['INCOME_FROM'] = 19;//�ɱ��������� - ����
                    else
                        $income_info['INCOME_FROM'] = 21;//�ɱ��������� - �ǵ���
                    $income_info['INCOME'] = $profit;
                    $income_info['INCOME_REMARK'] = '��������';
                    $income_info['ADD_UID'] = $this->uid;
                    $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());
                    $income_model = D('ProjectIncome');
                    $ret = $income_model->add_income_info($income_info);

                    if(!$ret){
                        $flag = false;
                        $this->model->rollback();
                        break;
                    }

                    //����������
                    $income_info['CASE_ID'] = $from_caseId;
                    $income_info['INCOME'] = -$profit;
                    $income_info['INCOME_REMARK'] = '����������';

                    if($from_case_scaletype==1)
                        $income_info['INCOME_FROM'] = 19;//�ɱ��������� - ����
                    else
                        $income_info['INCOME_FROM'] = 21;//�ɱ��������� - �ǵ���

                    $ret = $income_model->add_income_info($income_info);

                    if(!$ret){
                        $flag = false;
                        $this->model->rollback();
                        break;
                    }
                }

                //����֧����
                if($outcost) {
                    //��������
                    $cost_info['CASE_ID'] = $to_caseId;
                    $cost_info['ENTITY_ID'] = $transfer_id;
                    //ԭʼ��ID
                    $cost_info['ORG_ENTITY_ID'] = $transfer_id;
                    $cost_info['EXPEND_ID'] = $val;
                    //ԭʼСID
                    $cost_info['ORG_EXPEND_ID'] = $val;
                    $cost_info['EXPEND_FROM'] = 21;//����ɹ�
                    $cost_info['FEE'] = $outcost;
                    $cost_info['FEE_REMARK'] = '����֧��';
                    $cost_info['ISFUNDPOOL'] = false;
                    $cost_info['ADD_UID'] = $this->uid;
                    $cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());
                    $cost_info['INPUT_TAX'] = 0;  //����˰
                    $cost_info['ISKF'] = $koufei; //�Ƿ�۷�


                    $cost_model = D('ProjectCost');
                    $ret = $cost_model->add_cost_info($cost_info);

                    if(!$ret){
                        $flag = false;
                        $this->model->rollback();
                        break;
                    }


                    //����������
                    $cost_info['CASE_ID'] = $from_caseId;
                    $cost_info['FEE'] = -$outcost;
                    $cost_info['FEE_REMARK'] = '������֧��';
                    $ret = $cost_model->add_cost_info($cost_info);
                    if(!$ret){
                        $flag = false;
                        $this->model->rollback();
                        break;
                    }

                }

                //����֧����(�ʽ��)
                if($fundpoolcost) {
                    //��������
                    $cost_info['CASE_ID'] = $to_caseId;
                    $cost_info['ENTITY_ID'] = $transfer_id;
                    $cost_info['ORG_ENTITY_ID'] = $transfer_id;
                    $cost_info['EXPEND_ID'] = $val;
                    $cost_info['ORG_EXPEND_ID'] = $val;
                    $cost_info['EXPEND_FROM'] = 21;//����ɹ�
                    $cost_info['FEE'] = $fundpoolcost;
                    $cost_info['ISFUNDPOOL'] = true;
                    $cost_info['FEE_REMARK'] = '�ʽ�ػ���֧��';
                    $cost_info['ADD_UID'] = $this->uid;
                    $cost_info['OCCUR_TIME'] = date("Y-m-d H:i:s",time());
                    $cost_info['INPUT_TAX'] = 0;  //����˰
                    $cost_info['ISKF'] = $koufei;  //�Ƿ�۷�

                    $cost_model = D('ProjectCost');
                    $ret = $cost_model->add_cost_info($cost_info);
                    if(!$ret){
                        $flag = false;
                        $this->model->rollback();
                        break;
                    }

                    //����������
                    $cost_info['CASE_ID'] = $from_caseId;
                    $cost_info['FEE'] = -$fundpoolcost;
                    $cost_info['FEE_REMARK'] = '�ʽ�ر�����֧��';
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
                //�ɹ���ֵ
                $return['state'] = 1;
                $return['msg'] = g2u('����ȷ�ϳɹ�');
            }

            die(@json_encode($return));
        }

        //��ϸ����չ��
        Vendor('Oms.Form');
        $form = new Form();
        //form�����ʼ��
        $form = $form->initForminfo(161);

        //��չ�ֲ�����(��������)
        $form->setAttribute('NOPERATE',1);

        //�����
        $form->setMyField('CUSER', 'LISTSQL', 'SELECT ID, NAME FROM ERP_USERS', TRUE);
        //����
        $form->setMyField('CITY_ID', 'LISTSQL','SELECT ID, NAME FROM ERP_CITY', TRUE);
        //״̬
        $form->setMyField('STATUS', 'LISTCHAR', array2listchar(array("1"=>'δȷ��',"2"=>'��ȷ��')), FALSE);
        //�۷�
        $form->setMyField('KOUFEI', 'LISTCHAR', array2listchar(array("1"=>'��',"0"=>'��')), FALSE);
        //ҵ������
        $form->setMyField('PROJECT_TYPE_ID', 'LISTSQL', "SELECT ID,YEWU FROM ERP_BUSINESSCLASS", FALSE);

        //����ǹ�����������
        if($this->_merge_url_param['RECORDID'] && $this->_merge_url_param['flowId']) {
            $form->GABTN = " ";
        }

        //�����ֶ�(չ��)
        $input_arr = array(
            array(
                'TDNAME' => 'ѡ��ҵ������',
                'INPUTNAME' => 'allocation_bc',
                'LISTSQL'=>'SELECT B.ID,B.YEWU FROM ERP_CASE A LEFT JOIN ERP_BUSINESSCLASS B ON A.SCALETYPE = B.ID WHERE (A.SCALETYPE <=4 OR A.SCALETYPE=8) AND A.PROJECT_ID = LISTSQL_VAL',
                'LISTSQL_VAL'=> 'PROJECTID',
                'TYPE'=>'SELECT',
                'SELECTED'=>'PROJECT_TYPE_ID',
            ),
        );
        $form->addNewTd($input_arr);
        $form->refineGAButtons($this->getUserAuthorities(), $this->authorityMap);  // Ȩ��ǰ��
        $formHtml = $form->getResult();

        //ҳ��չ���Ƿ�ɲ���Ȩ��
        $transferid  = $_REQUEST['parentchooseid'];
        $auth_transfer_data = M("erp_transfer")
                                    ->field("status")
                                    ->where('id = ' . $transferid)
                                    ->select();

        $auth_transfer = false;

        //������������ͨ��
        if($auth_transfer_data && $auth_transfer_data[0]['STATUS'] ==2)
            $auth_transfer = true;

        $this->assign('uid',$this->uid);
        $this->assign('auth_transfer',$auth_transfer);
        $this->assign('form',$formHtml);
        $this->assign('lastFilter', json_encode(g2u($form->lastFilterArr)));  // ��ҳ�洫���ϴμ�������
        $this->display('show_pro_allocation');

    }


    /**
    +----------------------------------------------------------
     * ������� - �ɱ�����
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

        //Ȩ���ж�
        //�ɱ���������
        $type = 'chengbenhuabo';
        $auth = $workflow->start_authority($type);

        //������ID
        $flowId = isset($_REQUEST['flowId'])?intval($_REQUEST['flowId']):0;
        //ҵ�����ID
        $recordId = isset($_REQUEST['RECORDID'])?intval($_REQUEST['RECORDID']):0;
        //�Ŀ����ID
        $caseId = isset($_REQUEST['CASEID'])?intval($_REQUEST['CASEID']):0;

        $transfer_obj = M("erp_transfer");

        //��Ϊ��֤
        $ret = $transfer_obj->field("status,add_uid,caseid")->where("id=$recordId")->find();

        $fstatus = $ret['STATUS'];
        $add_uid = $ret['ADD_UID'];
        //��ȡ��Ŀ����ID
        if(!$caseId)
            $caseId = $ret['CASEID'];

        if($flowId){
            //״̬�ж�
            if($fstatus != 1){
                js_alert("�Բ��𣬸û�������״̬����");
            }
            //todo:Ȩ���ж�  ����

        }
        else{
            //״̬�ж�
            if($fstatus != 0){
                js_alert("�Բ��𣬸û�������״̬���ԣ�");
            }

            //Ȩ���ж�
            //if($this->uid != $add_uid){
            //    $this->error("�Բ�����û��Ȩ�޲����ù�����");
            //}

        }

        //����صİ������
        if($flowId)
        {
            //����������
            if($_REQUEST['savedata']){
                //ת����һ��
                if($_REQUEST['flowNext']){
                    $str = $workflow->handleworkflow($_REQUEST);
                    if($str){
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('����ʧ��');
                    }
                    //ͬ�����
                }elseif($_REQUEST['flowPass']){

                    $str = $workflow->passWorkflow($_REQUEST);
                    if($str){
                        js_alert('ͬ��ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('ͬ��ʧ��');
                    }
                    //�������
                }elseif($_REQUEST['flowNot']){

                    $str = $workflow->notWorkflow($_REQUEST);
                    if($str){
                        js_alert('����ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('���ʧ��');
                    }
                    //��ֹ����
                }elseif($_REQUEST['flowStop']){

                    $auth = $workflow->flowPassRole($flowId);
                    if(!$auth){
                        js_alert('δ�����ؾ���ɫ');exit;
                    }

                    $str = $workflow->finishworkflow($_REQUEST);
                    if($str){
                        js_alert('�����ɹ�',U('Flow/workStep'));
                    }else{
                        js_alert('����ʧ��');
                    }
                }
                exit;
            }
            //��Ⱦҳ��
            else{
                //�������(δ�ύ)
                $click  = $workflow->nextstep($flowId);
                $form=$workflow->createHtml($flowId);
            }
        }
        //��������
        else
        {
            if($_REQUEST['savedata'])
            {
                //���칤������caseid �� recordid Ҫ������
                $_REQUEST['type'] = $type;
                $_REQUEST['CASEID'] = $caseId;
                $_REQUEST['RECORDID'] = $recordId;

                //����״̬
                $ret = $workflow->createworkflow($_REQUEST);

                if(!empty($ret))
                {
                    js_alert('�ύ�ɹ�',U('Flow/workStep',$this->_merge_url_param));
                    exit;
                }
                else
                {
                    js_alert('�ύʧ��',U('Cost/opinionFlow',$this->_merge_url_param));
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
            }
        }

        $this->assign('form', $form);
        $this->assign('paramUrl', $this->_merge_url_param);
        $this->assign('current_url', U('Cost/opinionFlow',$this->_merge_url_param));
        $this->display('opinionFlow');
    }
}
?>