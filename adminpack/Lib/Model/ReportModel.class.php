<?php

/**
 *  ����ͳ��
 */
class ReportModel extends Model {
    protected $tbl_prj = "ERP_PROJECT";     //P
    protected $tbl_case = "ERP_CASE";        //C
    protected $tbl_cost_list = "ERP_COST_LIST";    //CL
    protected $tbl_income_list = "ERP_INCOME_LIST";    //IL
    protected $tbl_budget = "ERP_PRJBUDGET";    //PB
    protected $tbl_fee = "ERP_BUDGETFEE";    //BF

    //����
    protected $tbl = "ERP_CARDMEMBER";    //A
    protected $tbl_pay_ment = "ERP_MEMBER_PAYMENT";   //PA

    //����
    protected $tbl_md = "ERP_MEMBER_DISTRIBUTION";   //MD

    //Ӳ�� + �
    protected $tbl_ic = "ERP_INCOME_CONTRACT";   //IC


    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }

    /***
     * @param $scaletype  ҵ������ID
     * @param $is_city_search  true�����в�ѯ   false����Ŀ��ѯ
     * @return array
     */
    function scale_custom($scaletype,$is_city_search){
        $scale_custom_checked = array();

        switch($scaletype){
            case 1:
                $scale_custom_checked = array(
                    'card_count' => '�쿨��',
                    'card_profit' => '�쿨����',
                    'avg_card' => '����Ŀƽ���쿨��',
                    'est_cost' => 'Ԥ�����·���',
                    'act_cost' => 'ʵ�����·���',
                    'est_income' => 'Ԥ������',
                    'act_income' => 'ʵ������',
                    'cfm_income' => 'ȷ������',
                    'run_income_rate' => 'ִ�������뿿����',
                    'income_rate' => '���뿿����',
                    'cost_rate' => 'Ԥ�����ʹ����',
                    'est_profit' => 'Ԥ����������',
                    'run_profit' => 'ִ���и�������',
                    'final_profit' => '���㸶������',
                    'run_avg_profit' => 'ִ���е���Ŀƽ����������',
                    'final_avg_profit' => '���㵥��Ŀƽ����������',
                    'nodeal_client' => 'δ�ɽ��ͻ�',
                    'ad_cost' => '�ۺ���',
                    'final_profit_rate' => '���㸶��������',
                    'profit_rate' => '����������',
                    'est_client' => 'Ԥ������',
                    'act_client' => 'ʵ�ʵ���',
                    'est_deal' => 'Ԥ���ɽ�����',
                    'act_deal' => 'ʵ�ʳɽ�����',
                    'cfm_deal' => 'ȷ�ϳɽ�����',
                    'reim_uncfm_money' => '����δȷ�Ͻ��',
                    'reim_cfm_money' => '����ȷ�Ͻ��',
                    'financial_cfm_card_count' => '����ȷ�ϰ쿨��',
                    'financial_cfm_deposit' => '����ȷ��Ԥ�տ�',
                );
                if(!$is_city_search){
                    unset($scale_custom_checked['avg_card']);
                    unset($scale_custom_checked['run_avg_profit']);
                    unset($scale_custom_checked['final_avg_profit']);
                }
                break;
            case 2:
                $scale_custom_checked = array(
                    'fx_est_cost' => 'Ԥ�����·���',
                    'fx_act_cost' => 'ʵ�����·���',
                    'fx_est_income' => 'Ԥ������',
                    'fx_act_income' => '��Ʊ����',
                    'fx_cfm_income' => '�ؿ�����',
                    'fx_income_rate' => '���뿿����',
                    'fx_cost_rate' => 'Ԥ�����ʹ����',
                    'fx_est_profit' => 'Ԥ����������',
                    'fx_final_profit' => '���㸶������',
                    'fx_final_avg_profit' => '���㵥��Ŀƽ����������',
                    'fx_ad_cost' => '�ۺ���',
                    'fx_final_profit_rate' => '���㸶��������',
                    'fx_profit_rate' => '����������',
                    'fx_est_deal' => 'Ԥ���ɽ�����',
                    'fx_act_deal' => 'ʵ�ʳɽ�����',
                    'fx_cfm_deal' => 'ȷ�ϳɽ�����',
                    'fx_reim_uncfm_money' => '����δȷ�Ͻ��',
                    'fx_reim_cfm_money' => '����ȷ�Ͻ��',
                    'fx_reim_noget_money' => '��Ʊδ�ؿ�����',
                );
                if(!$is_city_search){
                    unset($scale_custom_checked['fx_final_avg_profit']);
                }
                break;
            case 3:
                $scale_custom_checked = array(
                    'yg_est_sign_money' => 'ǩԼ���',
                    'yg_act_sign_money' => 'ʵ��ǩԼ���',
                    'yg_act_put_moeny' => 'ʵ��Ͷ�Ž��',
                    'yg_act_invoice_money' => '��Ʊ���',
                    'yg_payment_money' => '�ؿ���',
                    'yg_noinvoice_money' => 'δ��Ʊ���',
                    'yg_invoice_nopayment_money' => '��Ʊδ�ؿ���',
                    'yg_est_cost' => 'Ԥ�����·���',
                    'yg_act_cost' => 'ʵ�����·���',
                    'yg_no_reim' => '�ѷ���δ����',
                    'yg_finconfirm_cost' => '������ȷ�����·���',
                );
                break;
            case 4:
                $scale_custom_checked = array(
                    'hd_est_invest_money' => '���̽��',
                    'hd_act_invest_money' => 'ʵ�����̽��',
                    'hd_act_invoice_money' => '��Ʊ���',
                    'hd_payment_money' => '�ؿ���',
                    'hd_noinvoice_money' => 'δ��Ʊ���',
                    'hd_invoice_nopayment_money' => '��Ʊδ�ؿ���',
                    'hd_est_cost' => 'Ԥ�����·���',
                    'hd_act_cost' => 'ʵ�����·���',
                    'hd_no_reim' => '�ѷ���δ����',
                    'hd_finconfirm_cost' => '������ȷ�����·���',
                );
                break;
        }

        return $scale_custom_checked;
    }

    /**
     * @param $search_btime   ��ʼʱ��
     * @param $search_etime   ����ʱ��
     * @param $fin_confirm     �Ƿ����ȷ��
     * @param $search_state    ��Ŀ״̬
     * @param $arr_search_checked    ��ѯ�ֶ�
     * @param $arr_search_city         ��ѯ����
     * @param $arr_search_prj          ��ѯ��Ŀ
     * @param $is_city_search           �Ƿ���в�ѯ
     * @param $page         ҳ��
     * @param $pagesize           ҳ���С
     */
    function ds_html($search_btime,$search_etime,$fin_confirm,$search_state,$arr_search_checked,$arr_search_city,$arr_search_prj,$is_city_search,$page,$pagesize){

        $between = $where_card = $where_reim = $where_cost_list = $where_prj = '';
        $where_card = " AND A.STATUS = 1";
        $where_prj = " AND P.STATUS != 2";
        $where_prj .= " AND C.SCALETYPE = 1";

        $type_name = "����";

        //������ʼʱ��
        if ($search_btime) {
            $where_card .= " AND to_char(A.CARDTIME,'yyyymmdd') >= " . @date("Ymd", strtotime($search_btime));
            $where_reim .= " AND to_char(IL.OCCUR_TIME,'yyyymmdd')>=" . @date("Ymd", strtotime($search_btime));
            $where_cost_list .= " AND to_char(CL.OCCUR_TIME,'yyyymmdd')>=" . @date("Ymd", strtotime($search_btime));
            //����ɱ�ר��
            $between .= " AND to_char(OCCUR_TIME,'yyyymmdd')>=" . @date("Ymd", strtotime($search_btime));
        }

        //��������ʱ��
        if ($search_etime) {
            $where_card .= " AND  to_char(A.CARDTIME,'yyyymmdd') < " . @date("Ymd", strtotime($search_etime));
            $where_reim .= " AND to_char(IL.OCCUR_TIME,'yyyymmdd')<" . @date("Ymd", strtotime($search_etime));
            $where_cost_list .= " AND to_char(CL.OCCUR_TIME,'yyyymmdd')<" . @date("Ymd", strtotime($search_etime));
            //����ɱ�ר��
            $between .= " AND to_char(OCCUR_TIME,'yyyymmdd')<" . @date("Ymd", strtotime($search_etime));
        }

        //����ȷ�����ݣ���ȫȷ�ϣ�
        if ($fin_confirm == 2)
            $where_card .= " AND  A.FINANCIALCONFIRM = 3 ";

        //��Ŀ״̬
        if ($search_state)
            $where_prj .= " AND C.FSTATUS=$search_state";


        //���в�ѯ
        if($is_city_search) {
            foreach ($arr_search_city as $_city) {

                //��ȡ��Ŀ����
                $prj_info = $this->get_proinfo($_city,$where_prj);
                $prj_count = $prj_info['prj_count'];
                $prjs_range = $prj_info['prjs_range'];
                $case_range = $prj_info['case_range'];

                //��Ŀ��
                $data[$_city]['prj_count'] = $prj_count;

                //�쿨��
                if ($arr_search_checked['card_count'] || $arr_search_checked['card_profit'] || $arr_search_checked['avg_card'])
                    $data[$_city]['card_count'] = $this->get_pro_card(2,$_city,$prjs_range,$where_card);

                //�쿨����
                if ($arr_search_checked['card_profit'])
                    $data[$_city]['card_profit'] = $this->get_card_profit(2,$_city,$prjs_range,$where_card);

                //δ�ɽ��ͻ�����Ա״̬Ϊ�˿���δǩԼ״̬��
                if ($arr_search_checked['nodeal_client'])
                    $data[$_city]['nodeal_client'] = $this->get_pro_card(1,$_city,$prjs_range,$where_card);

                //Ԥ�����·���/���ֳɱ�
                if ($arr_search_checked['est_cost'] || $arr_search_checked['cost_rate'] || $arr_search_checked['est_profit'])
                    $data[$_city]['est_cost'] = $this->ds_est_cost($_city,$prjs_range,$where_prj);

                //ʵ�����·���
                if ($arr_search_checked['act_cost'] || $arr_search_checked['run_profit'] || $arr_search_checked['avg_profit'])
                    $data[$_city]['act_cost'] = $this->ds_act_cost(1,$case_range,$between,$where_cost_list);

                //����δ�������·���
                if ($arr_search_checked['cost_rate'] || $arr_search_checked['final_profit'] || $arr_search_checked['final_avg_profit'] || $arr_search_checked['final_profit_rate'] || $arr_search_checked['profit_rate'])
                    $data[$_city]['whole_cost_money'] = $this->ds_act_cost(2,$case_range,$between,$where_cost_list);

                //���������·���
                if ($arr_search_checked['cost_rate'] || $arr_search_checked['final_profit'] || $arr_search_checked['final_avg_profit'] || $arr_search_checked['final_profit_rate'] || $arr_search_checked['profit_rate']) {
                    $data[$_city]['reim_cfm_money'] = $this->ds_act_cost(3, $case_range, $between, $where_cost_list);
                    $data[$_city]['reim_cost'] = $data[$_city]['reim_cfm_money'];
                }


                //Ԥ������
                if ($arr_search_checked['est_income'] || $arr_search_checked['run_income_rate'] || $arr_search_checked['income_rate'] || $arr_search_checked['est_profit'])
                    $data[$_city]['est_income']  = $this->ds_est_income(1,$case_range);

                //ʵ������
                if ($arr_search_checked['act_income'] || $arr_search_checked['run_income_rate'] || $arr_search_checked['run_profit'] || $arr_search_checked['run_avg_profit'])
                    $data[$_city]['act_income']  = $this->ds_income(1,$case_range,$where_reim);


                //ȷ�����루��Ա�쿨״̬Ϊ�Ѱ���ǩԼ�ҷ�Ʊ״̬Ϊ�ѿ�δ�������״̬���������Ը���Ŀ�����շѱ�׼��
                if ($arr_search_checked['cfm_income'] || $arr_search_checked['income_rate'] || $arr_search_checked['final_profit'] || $arr_search_checked['final_avg_profit'] || $arr_search_checked['final_profit_rate'] || $arr_search_checked['profit_rate'])
                    $data[$_city]['cfm_income']  = $this->ds_income(2,$case_range,$where_reim);

                //�ۺ������
                if ($arr_search_checked['ad_cost'] || $arr_search_checked['profit_rate'])
                    $data[$_city]['ad_cost']  = $this->ds_ad_cost($_city,$where_prj);


                //ʵ�ʳɽ��������Ѱ���ǩԼ��Ա��
                if ($arr_search_checked['act_deal'])
                    $data[$_city]['act_deal'] = $this->get_pro_card(3,$_city,$prjs_range,$where_card);

                //ȷ�ϳɽ��������Ѱ���ǩԼ���ѿ�Ʊ��Ա��
                if ($arr_search_checked['cfm_deal'])
                    $data[$_city]['cfm_deal'] = $this->get_pro_card(4,$_city,$prjs_range,$where_card);

                //����ȷ�ϰ쿨��������δ��Ʊ�����Ҳ����Ѿ�ȷ�ϵĻ�Ա����
                if ($arr_search_checked['financial_cfm_card_count'])
                    $data[$_city]['financial_cfm_card_count'] = $this->get_pro_card(5,$_city,$prjs_range,$where_card);

                #����ȷ��Ԥ�տ����δ��Ʊ�����Ҳ����Ѿ�ȷ�ϵ��ѽɽ�����ֵ��
                if ($arr_search_checked['financial_cfm_deposit'])
                    $data[$_city]['financial_cfm_deposit'] = $this->get_card_profit(5,$_city,$prjs_range,$where_card);


                #Ԥ�����ͣ�����Ԥ�����Ԥ����������
                if ($arr_search_checked['est_client'])
                    $data[$_city]['est_client'] = $this->ds_est_client($prjs_range);

                #ʵ�ʵ��ͣ���Ŀ��Ա������
                if ($arr_search_checked['act_client'])
                    $data[$_city]['act_client'] = $this->get_pro_card(6,$_city,$prjs_range,$where_card);

                #Ԥ���ɽ�����������Ԥ�����Ԥ���ɽ���
                if ($arr_search_checked['est_deal'])
                    $data[$_city]['est_deal'] = $this->ds_est_deal($prjs_range);

                //-----------------------------------------------------------------------------------//
                //-----------------------------------------------------------------------------------//
                //-----------------------------------------------------------------------------------//

                //����Ŀƽ���쿨���������а쿨����������Ŀ������
                if ($arr_search_checked['avg_card'])
                    $data[$_city]['avg_card'] = $data[$_city]['prj_count']?round($data[$_city]['card_count'] / $data[$_city]['prj_count']):"-";

                //����δȷ�Ͻ���Ŀ�����Ѿ����룬��������δȷ�ϵ���ֵ��
                if($arr_search_checked['reim_uncfm_money'])
                    $data[$_city]['reim_uncfm_money'] = $data[$_city]['whole_cost_money'] - $data[$_city]['reim_cfm_money'];

                #ִ�������뿿���ʣ�ʵ���������Ԥ�����룩
                if ($arr_search_checked['run_income_rate'])
                    $data[$_city]['run_income_rate'] = $data[$_city]['est_income']?round($data[$_city]['act_income'] / $data[$_city]['est_income'] * 100, 2) . '%':'-';

                #���뿿���ʣ�ȷ���������Ԥ�����룩
                if ($arr_search_checked['income_rate'])
                    $data[$_city]['income_rate'] = $data[$_city]['est_income']?round($data[$_city]['cfm_income'] / $data[$_city]['est_income'] * 100, 2) . '%':'-';


                #Ԥ�����ʹ���ʣ����������·��ó���Ԥ�����·��ã�
                if ($arr_search_checked['cost_rate'])
                    $data[$_city]['cost_rate'] = $data[$_city]['est_cost']?round($data[$_city]['reim_cost'] / $data[$_city]['est_cost'] * 100, 2) . '%':'-';

                #Ԥ����������Ԥ������-Ԥ�����·��ã�
                if ($arr_search_checked['est_profit'])
                    $data[$_city]['est_profit'] = $data[$_city]['est_income'] - $data[$_city]['est_cost'];

                #ִ���и�������ʵ������-ʵ�����·��ã�
                if ($arr_search_checked['run_profit'])
                    $data[$_city]['run_profit'] = $data[$_city]['act_income'] - $data[$_city]['act_cost'];

                #���㸶������ȷ������-���������·��ã�
                if ($arr_search_checked['final_profit'])
                    $data[$_city]['final_profit'] = $data[$_city]['cfm_income'] - $data[$_city]['reim_cost'];

                #ִ���е���Ŀƽ���������󣨸ó���ִ�����ܸ������������Ŀ������
                if ($arr_search_checked['run_avg_profit'])
                    $data[$_city]['run_avg_profit'] = $data[$_city]['prj_count']?round(($data[$_city]['act_income'] - $data[$_city]['act_cost'])  / $data[$_city]['prj_count'], 2):'-';

                #���㵥��Ŀƽ���������󣨸ó��о����ܸ������������Ŀ������
                if ($arr_search_checked['final_avg_profit'])
                    $data[$_city]['final_avg_profit'] = $data[$_city]['prj_count']?round(($data[$_city]['cfm_income'] - $data[$_city]['reim_cost'])  / $data[$_city]['prj_count'], 2):'-';


                #����׶θ��������ʣ�ȷ������-���������·���/ȷ�����룩
                if ($arr_search_checked['final_profit_rate'])
                    $data[$_city]['final_profit_rate'] = $data[$_city]['cfm_income']?round(($data[$_city]['cfm_income'] - $data[$_city]['reim_cost'])  / $data[$_city]['cfm_income'] * 100, 2) . '%':'-';


                #���������ʣ�ȷ������-���������·���-���Ϲ�����/ȷ�����룩
                if ($arr_search_checked['profit_rate'])
                    $data[$_city]['profit_rate'] = $data[$_city]['cfm_income']?round(($data[$_city]['cfm_income'] - $data[$_city]['reim_cost'] - $data[$_city]['ad_cost'])  / $data[$_city]['cfm_income'] * 100, 2) . '%':'-';

            }
        }
        //��Ŀ��ѯ
        else
        {
            $prjs_range = $case_range = '';

            //ҵ��ID����ĿIDӳ���ϵ
            $case_prjid = array();

            $search_city = implode(",",$arr_search_city);
            $search_prj = implode(",",$arr_search_prj);

            //����ǲ�ѯȫ����Ŀ  ---- ��������ѯ
            $sql = "SELECT C.PROJECT_ID,C.ID,P.CITY_ID,P.PROJECTNAME FROM $this->tbl_case C LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID = P.ID WHERE P.CITY_ID IN($search_city) $where_prj ORDER BY P.ID DESC";
            if ($arr_search_prj[0])
                $sql = "SELECT C.PROJECT_ID,C.ID,P.CITY_ID,P.PROJECTNAME FROM $this->tbl_case C LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID = P.ID WHERE P.ID IN($search_prj) $where_prj ORDER BY P.ID DESC";

            $sql = "SELECT * FROM ( SELECT A.*, ROWNUM RN FROM ($sql) A WHERE ROWNUM <= $page*$pagesize ) WHERE RN >  ($page-1)*$pagesize";
            $ret = M()->query($sql);

            foreach ($ret as $key => $val) {
                $data[$val['PROJECT_ID']]['id'] = $val['PROJECT_ID'];
                $data[$val['PROJECT_ID']]['case_id'] = $val['ID'];
                $data[$val['PROJECT_ID']]['prj_city'] = $val['CITY_ID'];
                $data[$val['PROJECT_ID']]['prj_name'] = $val['PROJECTNAME'];

                $case_prjid[$val['ID']] = $val['PROJECT_ID'];

                $prjs_range .= $val['PROJECT_ID'] . ',';
                $case_range .= $val['ID'] . ',';
            }

            $prjs_range = trim($prjs_range, ",");
            $prjs = explode(",", $prjs_range);
            $case_range = trim($case_range, ",");

            //��Ŀ�쿨��
            if ($arr_search_checked['card_count'] || $arr_search_checked['card_profit']) {
                $sql = "SELECT COUNT(1) COUNTS,PRJ_ID FROM $this->tbl A WHERE   A.CARDSTATUS<4 AND A.PRJ_ID IN($prjs_range) $where_card  GROUP BY A.PRJ_ID";
                $res = M()->query($sql);
                foreach ($res as $key => $val) {
                    $data[$val['PRJ_ID']]['card_count'] = intval($val['COUNTS']);
                }
            }

            //�쿨����
            if ($arr_search_checked['card_profit']) {
                $sql = "SELECT SUM(TRADE_MONEY) AS  TRADE_MONEY,A.PRJ_ID FROM $this->tbl_pay_ment PA LEFT JOIN $this->tbl A ON PA.MID = A.ID WHERE   A.CARDSTATUS<4 AND PRJ_ID  IN($prjs_range)  $where_card   GROUP BY A.PRJ_ID";
                $res = M()->query($sql);
                foreach ($res as $key => $val) {
                    $data[$val['PRJ_ID']]['card_profit'] = floatval($val['TRADE_MONEY']);
                }
            }

            //Ԥ�����·���/���ֳɱ�
            if ($arr_search_checked['est_cost'] || $arr_search_checked['cost_rate'] || $arr_search_checked['est_profit']) {
                $sql = "SELECT SUM(AMOUNT) AS AMOUNT,P.ID AS PRJ_ID  FROM $this->tbl_fee BF LEFT JOIN $this->tbl_budget PB ON BF.BUDGETID=PB.ID LEFT JOIN $this->tbl_case C ON PB.CASE_ID=C.ID LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID=P.ID WHERE C.PROJECT_ID IN($prjs_range) AND BF.ISONLINE=0 AND BF.FEEID != 98 GROUP  BY P.ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $data[$val['PRJ_ID']]['est_cost'] = floatval($val['AMOUNT']);
                }
            }


            //ʵ�����·���
            if ($arr_search_checked['act_cost'] || $arr_search_checked['run_profit']) {
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM ERP_COST_LIST
						   WHERE CASE_ID IN ($case_range) $between AND  STATUS>1
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID  $where_cost_list GROUP BY CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['act_cost'] = floatval($val['VCOST']);
                }
            }

            //���������·���
            if ($arr_search_checked['cost_rate'] || $arr_search_checked['final_profit'] || $arr_search_checked['final_profit_rate'] || $arr_search_checked['profit_rate']) {
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM $this->tbl_cost_list
						   WHERE CASE_ID IN ($case_range) $between AND  STATUS=4
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID $where_cost_list GROUP BY  CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['reim_cost'] = floatval($val['VCOST']);
                }
            }

            //Ԥ������
            if ($arr_search_checked['est_income'] || $arr_search_checked['run_income_rate'] || $arr_search_checked['income_rate'] || $arr_search_checked['est_profit']) {
                $sql = "SELECT SUM(SUMPROFIT) AS SUMPROFIT,CASE_ID FROM $this->tbl_budget WHERE CASE_ID IN ($case_range) GROUP BY CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['est_income'] = floatval($val['SUMPROFIT']);
                }
            }

            //ʵ������ ��Ŀ״ֵ̬Ϊ1������
            if ($arr_search_checked['act_income'] || $arr_search_checked['run_income_rate'] || $arr_search_checked['run_profit']) {
                $sql = "SELECT NVL(SUM(INCOME), 0) AS SUMINVOICES,CASE_ID
						FROM $this->tbl_income_list
						WHERE CASE_ID IN ($case_range) $between
						  AND STATUS = 1 GROUP  BY CASE_ID";

                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['act_income'] = floatval($val['SUMINVOICES']);
                }
            }

            //ȷ������
            if ($arr_search_checked['cfm_income'] || $arr_search_checked['income_rate'] || $arr_search_checked['final_profit'] || $arr_search_checked['final_profit_rate'] || $arr_search_checked['profit_rate']) {
                $sql = "SELECT NVL(SUM(INCOME), 0) AS SUMINVOICES,CASE_ID
						FROM $this->tbl_income_list
						WHERE CASE_ID IN ($case_range) $between
						  AND STATUS = 2 GROUP  BY CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['cfm_income'] = floatval($val['SUMINVOICES']);
                }
            }

            //δ�ɽ��ͻ�����Ա״̬Ϊ�˿���δǩԼ״̬��
            if ($arr_search_checked['nodeal_client']) {
                $sql = "SELECT COUNT(1) AS  COUNTS,A.PRJ_ID FROM $this->tbl A WHERE  A.CARDSTATUS IN (1,2,4)  AND A.PRJ_ID IN($prjs_range) $where_card GROUP BY A.PRJ_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $data[$val['PRJ_ID']]['nodeal_client'] = intval($val['COUNTS']);
                }
            }

            //�ۺ������
            if ($arr_search_checked['ad_cost'] || $arr_search_checked['profit_rate']) {
                $sql = "SELECT SUM(AMOUNT) AS AMOUNT,P.ID AS PRJ_ID  FROM $this->tbl_fee FB LEFT JOIN $this->tbl_budget PB ON FB.BUDGETID=PB.ID LEFT JOIN $this->tbl_case C ON PB.CASE_ID=C.ID LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID=P.ID WHERE C.PROJECT_ID IN($prjs_range) $where_prj AND FB.ISONLINE=-1 AND FB.ISVALID =-1 AND FB.FEEID=98 GROUP BY P.ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $data[$val['PRJ_ID']]['ad_cost'] = floatval($val['AMOUNT']);
                }
            }

            //Ԥ�����ͣ�����Ԥ�����Ԥ����������
            //Ԥ���ɽ�����������Ԥ�����Ԥ���ɽ���
            if ($arr_search_checked['est_client']) {
                $sql = "SELECT SUM(CUSTOMERS) AS CUSTOMERS,SUM(SETS) AS SETS,PROJECTT_ID FROM ERP_BUDGETSALE WHERE PROJECTT_ID IN($prjs_range) GROUP  BY PROJECTT_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $data[$val['PROJECTT_ID']]['est_client'] = intval($val['CUSTOMERS']);
                    $data[$val['PROJECTT_ID']]['est_deal'] = intval($val['SETS']);
                }
            }

            //ʵ�ʵ��ͣ���Ŀ��Ա������
            if ($arr_search_checked['act_client']) {
                $sql = "SELECT COUNT(1) AS  COUNTS,A.PRJ_ID FROM $this->tbl A where  A.PRJ_ID IN($prjs_range)  $where_card GROUP  BY A.PRJ_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $data[$val['PRJ_ID']]['act_client'] = intval($val['COUNTS']);
                }
            }

            //ʵ�ʳɽ��������Ѱ���ǩԼ��Ա��
            if ($arr_search_checked['act_deal']) {
                $sql = "SELECT COUNT(*) AS  COUNTS,A.PRJ_ID FROM $this->tbl A WHERE  A.PRJ_ID IN($prjs_range)  AND CARDSTATUS=3 $where_card GROUP  BY A.PRJ_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $data[$val['PRJ_ID']]['act_deal'] = intval($val['COUNTS']);
                }
            }

            //ȷ�ϳɽ��������Ѱ���ǩԼ���ѿ�Ʊ��Ա��
            if ($arr_search_checked['cfm_deal']) {
                $sql = "SELECT COUNT(*) AS  COUNTS,A.PRJ_ID FROM $this->tbl A WHERE   A.PRJ_ID IN($prjs_range)  AND CARDSTATUS=3 AND INVOICE_STATUS IN(2,3) $where_card  GROUP  BY A.PRJ_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $data[$val['PRJ_ID']]['cfm_deal'] = intval($val['COUNTS']);
                }
            }


            //����δȷ�Ͻ���Ŀ�����Ѿ����룬��������δȷ�ϵ���ֵ��
            if ($arr_search_checked['reim_uncfm_money']) {
                //����δ��������
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM $this->tbl_cost_list
						   WHERE CASE_ID IN ($case_range) $between AND STATUS  IN (1,2,4)
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID $where_cost_list GROUP BY CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['reim_uncfm_money_total'] = floatval($val['VCOST']);
                }

                //�����ѱ�������
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM $this->tbl_cost_list
						   WHERE CASE_ID IN ($case_range) $between AND STATUS = 4
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID $where_cost_list GROUP BY CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['reim_cfm_money'] = floatval($val['VCOST']);
                }

            }


            #����ȷ�ϰ쿨��������δ��Ʊ�����Ҳ����Ѿ�ȷ�ϵĻ�Ա����
            if ($arr_search_checked['financial_cfm_card_count']) {
                $sql = "SELECT COUNT(1) AS  COUNTS,A.PRJ_ID FROM $this->tbl A WHERE   A.PRJ_ID IN($prjs_range) AND A.INVOICE_STATUS IN(1,5) AND A.FINANCIALCONFIRM=3 $where_card GROUP BY A.PRJ_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $data[$val['PRJ_ID']]['financial_cfm_card_count'] = intval($val['COUNTS']);
                }
            }

            //����ȷ��Ԥ�տ����δ��Ʊ�����Ҳ����Ѿ�ȷ�ϵ��ѽɽ�����ֵ��
            if ($arr_search_checked['financial_cfm_deposit']) {
                $sql = "SELECT SUM(TRADE_MONEY) AS  TRADE_MONEY,A.PRJ_ID FROM $this->tbl_pay_ment PA LEFT JOIN $this->tbl A ON PA.MID = A.ID WHERE   A.CARDSTATUS<4 AND A.INVOICE_STATUS IN(1,5) AND A.FINANCIALCONFIRM=3  AND PRJ_ID  IN($prjs_range)  $where_card   GROUP BY A.PRJ_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $data[$val['PRJ_ID']]['financial_cfm_deposit'] = floatval($val['TRADE_MONEY']);
                }
            }


            foreach ($prjs as $key => $val) {
                if($arr_search_checked['run_income_rate'])
                    $data[$val]['run_income_rate'] = $data[$val]['est_income'] ? round($data[$val]['act_income'] / $data[$val]['est_income'] * 100, 2) . '%' : '-';

                //���뿿���ʣ�ȷ���������Ԥ�����룩
                if($arr_search_checked['income_rate'])
                    $data[$val]['income_rate'] = $data[$val]['est_income'] ? round($data[$val]['cfm_income'] / $data[$val]['est_income'] * 100, 2) . '%' : '-';

                #Ԥ�����ʹ���ʣ����������·��ó���Ԥ�����·��ã�
                if($arr_search_checked['cost_rate'])
                    $data[$val]['cost_rate'] = $data[$val]['est_cost'] ? round($data[$val]['reim_cost'] / $data[$val]['est_cost'] * 100, 2) . '%' : '-';

                #Ԥ����������Ԥ������-Ԥ�����·��ã�
                if($arr_search_checked['est_profit'])
                    $data[$val]['est_profit'] = $data[$val]['est_income'] - $data[$val]['est_cost'];

                #ִ���и�������ʵ������-ʵ�����·��ã�
                if($arr_search_checked['run_profit'])
                    $data[$val]['run_profit'] = $data[$val]['act_income'] - $data[$val]['act_cost'];

                #���㸶������ȷ������-���������·��ã�
                if($arr_search_checked['final_profit'])
                    $data[$val]['final_profit'] = $data[$val]['cfm_income'] - $data[$val]['reim_cost'];

                //����δȷ�Ͻ���Ŀ�����Ѿ����룬��������δȷ�ϵ���ֵ��
                if($arr_search_checked['reim_uncfm_money'])
                    $data[$val]['reim_uncfm_money'] = $data[$val]['reim_uncfm_money_total'] - $data[$val]['reim_cfm_money'];

                //����׶θ��������ʣ�ȷ������-���������·���/ȷ�����룩
                if($arr_search_checked['final_profit_rate'])
                    $data[$val]['final_profit_rate'] = $data[$val]['cfm_income'] ? round(($data[$val]['cfm_income'] - $data[$val]['reim_cost']) / $data[$val]['cfm_income'] * 100, 2) . '%' : '-';
                #���������ʣ�ȷ������-���������·���-���Ϲ�����/ȷ�����룩
                if($arr_search_checked['profit_rate'])
                    $data[$val]['profit_rate'] = $data[$val]['cfm_income'] ? round(($data[$val]['cfm_income'] - $data[$val]['reim_cost'] - $data[$val]['ad_cost']) / $data[$val]['cfm_income'] * 100, 2) . '%' : '-';
            }
        }

        $html = $this->get_table_html($type_name,$arr_search_checked,$is_city_search,$data);

        return $html;
    }

    /**
     * @param $search_btime
     * @param $search_etime
     * @param $fin_confirm
     * @param $search_state
     * @param $arr_search_field
     * @param $arr_search_city
     * @param $arr_search_prj
     * @param $is_city_search
     * @param $page
     * @param $pagesize
     */
    function fx_html($search_btime,$search_etime,$fin_confirm,$search_state,$arr_search_checked,$arr_search_city,$arr_search_prj,$is_city_search,$page,$pagesize){
        $between = $where_distribution = $where_reim = $where_cost_list = $where_prj = '';
        $where_prj = " AND P.STATUS != 2";
        $where_prj .= " AND C.SCALETYPE = 2";

        $type_name = "����";

        //������ʼʱ��
        if ($search_btime) {
            $where_distribution .= " AND to_char(MD.CREATETIME,'yyyymmdd') >= " . @date("Ymd", strtotime($search_btime));
            $where_reim .= " AND to_char(IL.OCCUR_TIME,'yyyymmdd')>=" . @date("Ymd", strtotime($search_btime));
            $where_cost_list .= " AND to_char(CL.OCCUR_TIME,'yyyymmdd')>=" . @date("Ymd", strtotime($search_btime));
            //����ɱ�ר��
            $between .= " AND to_char(OCCUR_TIME,'yyyymmdd')>=" . @date("Ymd", strtotime($search_btime));
        }

        //��������ʱ��
        if ($search_etime) {
            $where_distribution .= " AND  to_char(MD.CREATETIME,'yyyymmdd') < " . @date("Ymd", strtotime($search_etime));
            $where_reim .= " AND to_char(IL.OCCUR_TIME,'yyyymmdd')<" . @date("Ymd", strtotime($search_etime));
            $where_cost_list .= " AND to_char(CL.OCCUR_TIME,'yyyymmdd')<" . @date("Ymd", strtotime($search_etime));
            //����ɱ�ר��
            $between .= " AND to_char(OCCUR_TIME,'yyyymmdd')<" . @date("Ymd", strtotime($search_etime));
        }

        //��Ŀ״̬
        if ($search_state)
            $where_prj .= " AND C.FSTATUS=$search_state";


        //���в�ѯ
        if($is_city_search) {
            foreach ($arr_search_city as $_city) {

                //��ȡ��Ŀ����
                $prj_info = $this->get_proinfo($_city,$where_prj);
                $prj_count = $prj_info['prj_count'];
                $prjs_range = $prj_info['prjs_range'];
                $case_range = $prj_info['case_range'];

                //��Ŀ��
                $data[$_city]['prj_count'] = $prj_count;

                //Ԥ�����·���
                if ($arr_search_checked['fx_est_cost'])
                    $data[$_city]['fx_est_cost'] = $this->ds_est_cost($_city,$prjs_range,$where_prj);

                //ʵ�����·���
                if ($arr_search_checked['fx_act_cost'])
                    $data[$_city]['fx_act_cost'] = $this->ds_act_cost(1,$case_range,$between,$where_cost_list);

                //Ԥ������
                if ($arr_search_checked['fx_est_income'])
                    $data[$_city]['fx_est_income'] =  $this->ds_est_income(1,$case_range);

                //Ԥ����������
                if($arr_search_checked['fx_est_profit'])
                    $data[$_city]['fx_est_profit'] =  $this->ds_est_income(2,$case_range);

                //��Ʊ����
                if ($arr_search_checked['fx_act_income'])
                    $data[$_city]['fx_act_income'] =  $this->ds_income(3,$case_range,$where_reim);

                //�ؿ�����
                if ($arr_search_checked['fx_cfm_income'])
                    $data[$_city]['fx_cfm_income'] = $this->ds_income(4,$case_range,$where_reim);


                //�ۺ���
                if ($arr_search_checked['fx_ad_cost'])
                    $data[$_city]['fx_ad_cost']  = $this->ds_ad_cost($_city,$where_prj);

                //Ԥ���ɽ�����
                if ($arr_search_checked['fx_est_deal'])
                    $data[$_city]['fx_est_deal']  = $this->ds_est_deal($prjs_range);

                //ʵ�ʳɽ�����
                if ($arr_search_checked['fx_act_deal'])
                    $data[$_city]['fx_act_deal']  = $this->fx_act_deal(1,$case_range);


                //ȷ�ϳɽ�����
                if ($arr_search_checked['fx_cfm_deal'])
                    $data[$_city]['fx_cfm_deal']  = $this->fx_act_deal(2,$case_range);

                //����ȷ�Ͻ��
                if ($arr_search_checked['fx_reim_cfm_money'])
                    $data[$_city]['fx_reim_cfm_money'] = $this->ds_act_cost(3, $case_range, $between, $where_cost_list);

                //���ý��
                $data[$_city]['fx_whole_money'] = $this->ds_act_cost(2, $case_range, $between, $where_cost_list);

                /*------------------------------------------------------------------------------
                --------------------------------------------------------------------------------
                --------------------------------------------------------------------------------*/

                //���뿿����
                if($arr_search_checked['fx_income_rate'])
                    $data[$_city]['fx_income_rate'] = $data[$_city]['fx_est_income']?round($data[$_city]['fx_cfm_income'] / $data[$_city]['fx_est_income'] * 100, 2) . '%':'-';

                #Ԥ�����ʹ���ʣ����������·��ó���Ԥ�����·��ã�
                if ($arr_search_checked['fx_cost_rate'])
                    $data[$_city]['fx_cost_rate'] = $data[$_city]['fx_est_cost']?round($data[$_city]['fx_act_cost'] / $data[$_city]['fx_est_cost'] * 100, 2) . '%':'-';

                #���㸶������ȷ������-���������·��ã�
                if ($arr_search_checked['fx_final_profit'])
                    $data[$_city]['fx_final_profit'] = $data[$_city]['fx_cfm_income'] - $data[$_city]['fx_est_cost'];

                #���㵥��Ŀƽ���������󣨸ó��о����ܸ������������Ŀ������
                if ($arr_search_checked['fx_final_avg_profit'])
                    $data[$_city]['fx_final_avg_profit'] = $data[$_city]['prj_count']?round($data[$_city]['fx_final_profit']  / $data[$_city]['prj_count'], 2):'-';

                #����׶θ��������ʣ�ȷ������-���������·��� - ���Ϲ��/ȷ�����룩
                if ($arr_search_checked['fx_final_profit_rate'])
                    $data[$_city]['fx_final_profit_rate'] = $data[$_city]['fx_cfm_income']?round(($data[$_city]['fx_cfm_income'] - $data[$_city]['fx_act_cost'] - $data[$_city]['fx_ad_cost'])  / $data[$_city]['fx_cfm_income'] * 100, 2) . '%':'-';

                #���������ʣ�ȷ������-���������·���-���Ϲ�����/ȷ�����룩
                if($arr_search_checked['fx_profit_rate'])
                    $data[$_city]['fx_profit_rate'] = $data[$_city]['fx_cfm_income'] ? round(($data[$_city]['fx_cfm_income'] - $data[$_city]['fx_act_cost'] - $data[$_city]['fx_ad_cost']) / $data[$_city]['fx_cfm_income'] * 100, 2) . '%' : '-';

                #����δȷ�Ͻ��
                if($arr_search_checked['fx_reim_uncfm_money'])
                    $data[$_city]['fx_reim_uncfm_money'] = $data[$_city]['fx_whole_money'] - $data[$_city]['fx_reim_cfm_money'];

                //��Ʊδ�ؿ�����
                if($arr_search_checked['fx_reim_noget_money'])
                    $data[$_city]['fx_reim_noget_money'] = $data[$_city]['fx_act_income'] - $data[$_city]['fx_cfm_income'];

            }
        }
        //��Ŀ��ѯ
        else
        {
            $prjs_range = $case_range = '';

            //ҵ��ID����ĿIDӳ���ϵ
            $case_prjid = array();

            $search_city = implode(",",$arr_search_city);
            $search_prj = implode(",",$arr_search_prj);

            //����ǲ�ѯȫ����Ŀ  ---- ��������ѯ
            $sql = "SELECT C.PROJECT_ID,C.ID,P.CITY_ID,P.PROJECTNAME FROM $this->tbl_case C LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID = P.ID WHERE P.CITY_ID IN($search_city) $where_prj ORDER BY P.ID DESC";
            if ($arr_search_prj[0])
                $sql = "SELECT C.PROJECT_ID,C.ID,P.CITY_ID,P.PROJECTNAME FROM $this->tbl_case C LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID = P.ID WHERE P.ID IN($search_prj) $where_prj ORDER BY P.ID DESC";

            $sql = "SELECT * FROM ( SELECT A.*, ROWNUM RN FROM ($sql) A WHERE ROWNUM <= $page*$pagesize ) WHERE RN >  ($page-1)*$pagesize";
            $ret = M()->query($sql);

            foreach ($ret as $key => $val) {
                $data[$val['PROJECT_ID']]['id'] = $val['PROJECT_ID'];
                $data[$val['PROJECT_ID']]['case_id'] = $val['ID'];
                $data[$val['PROJECT_ID']]['prj_city'] = $val['CITY_ID'];
                $data[$val['PROJECT_ID']]['prj_name'] = $val['PROJECTNAME'];

                $case_prjid[$val['ID']] = $val['PROJECT_ID'];

                $prjs_range .= $val['PROJECT_ID'] . ',';
                $case_range .= $val['ID'] . ',';
            }

            $prjs_range = trim($prjs_range, ",");
            $prjs = explode(",", $prjs_range);
            $case_range = trim($case_range, ",");


            //Ԥ�����·���/���ֳɱ�
            if ($arr_search_checked['fx_est_cost']) {
                $sql = "SELECT SUM(AMOUNT) AS AMOUNT,P.ID AS PRJ_ID  FROM $this->tbl_fee BF LEFT JOIN $this->tbl_budget PB ON BF.BUDGETID=PB.ID LEFT JOIN $this->tbl_case C ON PB.CASE_ID=C.ID LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID=P.ID WHERE C.PROJECT_ID IN($prjs_range) AND BF.ISONLINE=0 AND BF.FEEID != 98 GROUP  BY P.ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $data[$val['PRJ_ID']]['fx_est_cost'] = floatval($val['AMOUNT']);
                }
            }

            //ʵ�����·���
            if ($arr_search_checked['fx_act_cost']) {
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM ERP_COST_LIST
						   WHERE CASE_ID IN ($case_range) $between AND  STATUS>1
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID  $where_cost_list GROUP BY CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['fx_act_cost'] = floatval($val['VCOST']);
                }
            }

            //Ԥ������
            if ($arr_search_checked['fx_est_income']) {
                $sql = "SELECT SUM(SUMPROFIT) AS SUMPROFIT,CASE_ID FROM $this->tbl_budget WHERE CASE_ID IN ($case_range) GROUP BY CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['fx_est_income'] = floatval($val['SUMPROFIT']);
                }
            }

            //��Ʊ����
            if ($arr_search_checked['fx_act_income']) {
                $sql = "SELECT NVL(SUM(INCOME), 0) AS SUMINVOICES,CASE_ID
						FROM $this->tbl_income_list
						WHERE CASE_ID IN ($case_range) $between
						  AND STATUS = 3 GROUP  BY CASE_ID";

                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['fx_act_income'] = floatval($val['SUMINVOICES']);
                }
            }

            //�ؿ�����
            if ($arr_search_checked['fx_cfm_income']) {
                $sql = "SELECT NVL(SUM(INCOME), 0) AS SUMINVOICES,CASE_ID
						FROM $this->tbl_income_list
						WHERE CASE_ID IN ($case_range) $between
						  AND STATUS = 4 GROUP  BY CASE_ID";

                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['fx_cfm_income'] = floatval($val['SUMINVOICES']);
                }

                $sql = "SELECT NVL(SUM(INCOME), 0) AS SUMINVOICES,CASE_ID
						FROM $this->tbl_income_list
						WHERE CASE_ID IN ($case_range) $between
						  AND STATUS = 3 AND (INCOME_FROM = 3 OR INCOME_FROM = 20) GROUP  BY CASE_ID";
                $res = M()->query($sql);
                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['fx_cfm_income'] += floatval($val['SUMINVOICES']);
                }
            }

            //Ԥ����������
            if($arr_search_checked['fx_est_profit']){
                $sql = "SELECT  CASE_ID,OFFLINE_COST_SUM_PROFIT  FROM ERP_PRJBUDGET WHERE CASE_ID IN ($case_range)";

                $res = M()->query($sql);

                foreach ($res as $key=>$val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['fx_est_profit'] = floatval($val['OFFLINE_COST_SUM_PROFIT']);
                }

            }

            //�ۺ������
            if ($arr_search_checked['fx_ad_cost']) {
                $sql = "SELECT SUM(AMOUNT) AS AMOUNT,P.ID AS PRJ_ID  FROM $this->tbl_fee FB LEFT JOIN $this->tbl_budget PB ON FB.BUDGETID=PB.ID LEFT JOIN $this->tbl_case C ON PB.CASE_ID=C.ID LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID=P.ID WHERE C.PROJECT_ID IN($prjs_range) $where_prj AND FB.ISONLINE=-1 AND FB.ISVALID =-1 AND FB.FEEID=98 GROUP BY P.ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $data[$val['PRJ_ID']]['fx_ad_cost'] = floatval($val['AMOUNT']);
                }
            }

            //Ԥ���ɽ�����
            if ($arr_search_checked['fx_est_deal']) {
                $sql = "SELECT SUM(CUSTOMERS) AS CUSTOMERS,SUM(SETS) AS SETS,PROJECTT_ID FROM ERP_BUDGETSALE WHERE PROJECTT_ID IN($prjs_range) GROUP  BY PROJECTT_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $data[$val['PROJECTT_ID']]['fx_est_deal'] = intval($val['SETS']);
                }
            }


            //ʵ�ʳɽ�����
            if ($arr_search_checked['fx_act_deal']){
                $sql = "SELECT COUNT(1) COUNTS,MD.CASE_ID AS CASE_ID FROM $this->tbl_md MD WHERE MD.CASE_ID IN($case_range) GROUP BY MD.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['fx_act_deal'] = floatval($val['COUNTS']);
                }
            }

            //ȷ�ϳɽ�����
            if ($arr_search_checked['fx_cfm_deal']) {
                $sql = "SELECT COUNT(1) COUNTS,MD.CASE_ID AS CASE_ID FROM $this->tbl_md MD WHERE MD.CASE_ID IN($case_range) AND INVOICE_STATUS IN(2,3) GROUP BY MD.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['fx_cfm_deal'] = floatval($val['COUNTS']);
                }
            }


            //����δȷ�Ͻ���Ŀ�����Ѿ����룬��������δȷ�ϵ���ֵ��
            if ($arr_search_checked['fx_reim_uncfm_money']) {
                //����δ��������
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM $this->tbl_cost_list
						   WHERE CASE_ID IN ($case_range) $between AND STATUS  IN (1,2,4)
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID $where_cost_list GROUP BY CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['fx_reim_uncfm_money_total'] = floatval($val['VCOST']);
                }

                //�����ѱ�������
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM $this->tbl_cost_list
						   WHERE CASE_ID IN ($case_range) $between AND STATUS = 4
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID $where_cost_list GROUP BY CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['fx_reim_cfm_money'] = floatval($val['VCOST']);
                }

            }

            foreach ($prjs as $key => $val) {
                //���뿿���ʣ��ؿ��������Ԥ�����룩
                if($arr_search_checked['fx_income_rate'])
                    $data[$val]['fx_income_rate'] = $data[$val]['fx_est_income'] ? round($data[$val]['fx_cfm_income'] / $data[$val]['fx_est_income'] * 100, 2) . '%' : '-';

                #Ԥ�����ʹ���ʣ����������·��ó���Ԥ�����·��ã�
                if($arr_search_checked['fx_cost_rate'])
                    $data[$val]['fx_cost_rate'] = $data[$val]['fx_est_cost'] ? round($data[$val]['fx_act_cost'] / $data[$val]['fx_est_cost'] * 100, 2) . '%' : '-';

                #���㸶������ȷ������-���������·��ã�
                if($arr_search_checked['fx_final_profit'])
                    $data[$val]['fx_final_profit'] = $data[$val]['fx_cfm_income'] - $data[$val]['fx_est_cost'];

                //����δȷ�Ͻ���Ŀ�����Ѿ����룬��������δȷ�ϵ���ֵ��
                if($arr_search_checked['reim_uncfm_money'])
                    $data[$val]['reim_uncfm_money'] = $data[$val]['reim_uncfm_money_total'] - $data[$val]['reim_cfm_money'];

                //���㸶�������ʣ�ȷ������-���������·���/ȷ�����룩
                if($arr_search_checked['fx_final_profit_rate'])
                    $data[$val]['fx_final_profit_rate'] = $data[$val]['fx_cfm_income'] ? round(($data[$val]['fx_cfm_income'] - $data[$val]['fx_act_cost']) / $data[$val]['fx_cfm_income'] * 100, 2) . '%' : '-';

                #���������ʣ�ȷ������-���������·���-���Ϲ�����/ȷ�����룩
                if($arr_search_checked['fx_profit_rate'])
                    $data[$val]['fx_profit_rate'] = $data[$val]['fx_cfm_income'] ? round(($data[$val]['fx_cfm_income'] - $data[$val]['fx_act_cost'] - $data[$val]['fx_ad_cost']) / $data[$val]['fx_cfm_income'] * 100, 2) . '%' : '-';

                #��Ʊδ�ؿ����루��Ʊ����-�ؿ����룩
                if($arr_search_checked['fx_reim_noget_money'])
                    $data[$val]['fx_reim_noget_money'] = $data[$val]['fx_act_income'] - $data[$val]['fx_cfm_income'];

            }
        }

        $html = $this->get_table_html($type_name,$arr_search_checked,$is_city_search,$data);

        return $html;

    }


    /**
     * @param $search_btime
     * @param $search_etime
     * @param $fin_confirm
     * @param $search_state
     * @param $arr_search_field
     * @param $arr_search_city
     * @param $arr_search_prj
     * @param $is_city_search
     * @param $page
     * @param $pagesize
     */
    function yg_html($search_btime,$search_etime,$fin_confirm,$search_state,$arr_search_checked,$arr_search_city,$arr_search_prj,$is_city_search,$page,$pagesize){
        $between = $where_card = $where_reim = $where_cost_list = $where_prj = '';
        $where_prj = " AND P.STATUS != 2";
        $where_prj .= " AND C.SCALETYPE = 3";

        $type_name = "Ӳ��";

        //������ʼʱ��
        if ($search_btime) {
            $where_reim .= " AND to_char(IL.OCCUR_TIME,'yyyymmdd')>=" . @date("Ymd", strtotime($search_btime));
            $where_cost_list .= " AND to_char(CL.OCCUR_TIME,'yyyymmdd')>=" . @date("Ymd", strtotime($search_btime));
            //����ɱ�ר��
            $between .= " AND to_char(OCCUR_TIME,'yyyymmdd')>=" . @date("Ymd", strtotime($search_btime));
        }

        //��������ʱ��
        if ($search_etime) {
            $where_reim .= " AND to_char(IL.OCCUR_TIME,'yyyymmdd')<" . @date("Ymd", strtotime($search_etime));
            $where_cost_list .= " AND to_char(CL.OCCUR_TIME,'yyyymmdd')<" . @date("Ymd", strtotime($search_etime));
            //����ɱ�ר��
            $between .= " AND to_char(OCCUR_TIME,'yyyymmdd')<" . @date("Ymd", strtotime($search_etime));
        }

        //��Ŀ״̬
        if ($search_state)
            $where_prj .= " AND C.FSTATUS=$search_state";

        //���в�ѯ
        if($is_city_search) {
            foreach ($arr_search_city as $_city) {

                //��ȡ��Ŀ����
                $prj_info = $this->get_proinfo($_city,$where_prj);
                $prj_count = $prj_info['prj_count'];
                $prjs_range = $prj_info['prjs_range'];
                $case_range = $prj_info['case_range'];

                //��Ŀ��
                $data[$_city]['prj_count'] = $prj_count;

                //ǩԼ���
                if ($arr_search_checked['yg_est_sign_money'])
                    $data[$_city]['yg_est_sign_money'] = $this->get_yg_sign_money(1,$case_range);

                //ʵ��ǩԼ���
                if ($arr_search_checked['yg_act_sign_money'])
                    $data[$_city]['yg_act_sign_money'] = $this->get_yg_sign_money(2,$case_range);

                //ʵ��Ͷ�Ž��
                if ($arr_search_checked['yg_act_put_moeny'])
                    $data[$_city]['yg_act_put_moeny'] = '�ݲ�����';

                //��Ʊ���
                if ($arr_search_checked['yg_act_invoice_money'])
                    $data[$_city]['yg_act_invoice_money']  = $this->ds_income(3,$case_range,$where_reim);

                //�ؿ���
                if ($arr_search_checked['yg_payment_money'])
                    $data[$_city]['yg_payment_money']  = $this->ds_income(4,$case_range,$where_reim);

                //Ԥ�����·���/���ֳɱ�
                if ($arr_search_checked['yg_est_cost'])
                    $data[$_city]['yg_est_cost'] = "�ݲ�����";

                //ʵ�����·���
                if ($arr_search_checked['yg_act_cost'])
                    $data[$_city]['yg_act_cost'] = $this->ds_act_cost(1,$case_range,$between,$where_cost_list);

                //����δ�������·���
                $data[$_city]['whole_cost_money'] = $this->ds_act_cost(2,$case_range,$between,$where_cost_list);

                //���������·���
                if ($arr_search_checked['yg_finconfirm_cost']) {
                    $data[$_city]['yg_finconfirm_cost'] = $this->ds_act_cost(3, $case_range, $between, $where_cost_list);
                }

                //-----------------------------------------------------------------------------------//
                //-----------------------------------------------------------------------------------//
                //-----------------------------------------------------------------------------------//

                //��Ʊδ�ؿ���
                if ($arr_search_checked['yg_invoice_nopayment_money'])
                    $data[$_city]['yg_invoice_nopayment_money'] = $data[$_city]['yg_act_invoice_money'] - $data[$_city]['yg_payment_money'];

                //δ��Ʊ���
                if($arr_search_checked['yg_noinvoice_money'])
                    $data[$_city]['yg_noinvoice_money'] = '�ݲ�����';

                //�ѷ���δ����
                if($arr_search_checked['yg_no_reim'])
                    $data[$_city]['yg_no_reim'] = $data[$_city]['whole_cost_money'] - $data[$_city]['yg_finconfirm_cost'];

            }
        }
        //��Ŀ��ѯ
        else
        {
            $prjs_range = $case_range = '';

            //ҵ��ID����ĿIDӳ���ϵ
            $case_prjid = array();

            $search_city = implode(",",$arr_search_city);
            $search_prj = implode(",",$arr_search_prj);

            //����ǲ�ѯȫ����Ŀ  ---- ��������ѯ
            $sql = "SELECT C.PROJECT_ID,C.ID,P.CITY_ID,P.PROJECTNAME FROM $this->tbl_case C LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID = P.ID WHERE P.CITY_ID IN($search_city) $where_prj ORDER BY P.ID DESC";
            if ($arr_search_prj[0])
                $sql = "SELECT C.PROJECT_ID,C.ID,P.CITY_ID,P.PROJECTNAME FROM $this->tbl_case C LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID = P.ID WHERE P.ID IN($search_prj) $where_prj ORDER BY P.ID DESC";

            $sql = "SELECT * FROM ( SELECT A.*, ROWNUM RN FROM ($sql) A WHERE ROWNUM <= $page*$pagesize ) WHERE RN >  ($page-1)*$pagesize";
            $ret = M()->query($sql);

            foreach ($ret as $key => $val) {
                $data[$val['PROJECT_ID']]['id'] = $val['PROJECT_ID'];
                $data[$val['PROJECT_ID']]['case_id'] = $val['ID'];
                $data[$val['PROJECT_ID']]['prj_city'] = $val['CITY_ID'];
                $data[$val['PROJECT_ID']]['prj_name'] = $val['PROJECTNAME'];

                $case_prjid[$val['ID']] = $val['PROJECT_ID'];

                $prjs_range .= $val['PROJECT_ID'] . ',';
                $case_range .= $val['ID'] . ',';
            }

            $prjs_range = trim($prjs_range, ",");
            $prjs = explode(",", $prjs_range);
            $case_range = trim($case_range, ",");

            //ǩԼ���
            if($arr_search_checked['yg_est_sign_money']){
                $sql = "SELECT SUM(MONEY) AS MONEY,CASE_ID FROM $this->tbl_ic IC WHERE IC.CASE_ID IN($case_range) GROUP BY IC.CASE_ID";

                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['yg_est_sign_money'] = floatval($val['MONEY']);
                }
            }

            //ʵ��ǩԼ���
            if($arr_search_checked['yg_act_sign_money']){
                $sql = "SELECT SUM(MONEY) AS MONEY,CASE_ID FROM $this->tbl_ic IC WHERE IC.CASE_ID IN($case_range) AND STATUS=5 GROUP BY IC.CASE_ID";

                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['yg_act_sign_money'] = floatval($val['MONEY']);
                }
            }

            //��Ʊ����
            if ($arr_search_checked['yg_act_invoice_money']) {
                $sql = "SELECT NVL(SUM(INCOME), 0) AS SUMINVOICES,CASE_ID
						FROM $this->tbl_income_list
						WHERE CASE_ID IN ($case_range) $between
						  AND STATUS = 3 GROUP  BY CASE_ID";

                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['yg_act_invoice_money'] = floatval($val['SUMINVOICES']);
                }
            }

            //�ؿ�����
            if ($arr_search_checked['yg_payment_money']) {
                $sql = "SELECT NVL(SUM(INCOME), 0) AS SUMINVOICES,CASE_ID
						FROM $this->tbl_income_list
						WHERE CASE_ID IN ($case_range) $between
						  AND STATUS = 4 GROUP  BY CASE_ID";

                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['yg_payment_money'] = floatval($val['SUMINVOICES']);
                }
            }

            //ʵ�����·���
            if ($arr_search_checked['yg_act_cost']) {
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM ERP_COST_LIST
						   WHERE CASE_ID IN ($case_range) $between AND  STATUS>1
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID  $where_cost_list GROUP BY CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['yg_act_cost'] = floatval($val['VCOST']);
                }
            }

            //����δȷ�Ͻ���Ŀ�����Ѿ����룬��������δȷ�ϵ���ֵ��
            if ($arr_search_checked['yg_finconfirm_cost']) {
                //����δ��������
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM $this->tbl_cost_list
						   WHERE CASE_ID IN ($case_range) $between AND STATUS  IN (1,2,4)
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID $where_cost_list GROUP BY CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['yg_reim_uncfm_money_total'] = floatval($val['VCOST']);
                }

                //�����ѱ�������
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM $this->tbl_cost_list
						   WHERE CASE_ID IN ($case_range) $between AND STATUS = 4
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID $where_cost_list GROUP BY CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['yg_finconfirm_cost'] = floatval($val['VCOST']);
                }

            }

            foreach ($prjs as $key => $val) {
                //ʵ��Ͷ�Ž��
                if($arr_search_checked['yg_act_put_moeny'])
                    $data[$val]['yg_act_put_moeny'] = '�ݲ�����';

                #δ��Ʊ���
                if($arr_search_checked['yg_noinvoice_money'])
                    $data[$val]['yg_noinvoice_money'] = '�ݲ�����';

                #��Ʊδ�ؿ���
                if($arr_search_checked['yg_invoice_nopayment_money'])
                    $data[$val]['yg_invoice_nopayment_money'] = $data[$val]['yg_act_invoice_money'] - $data[$val]['yg_payment_money'];

                #Ԥ������ͳ��
                if($arr_search_checked['yg_est_cost'])
                    $data[$val]['yg_est_cost'] = '�ݲ�����';

                //�ѷ���δ����
                if($arr_search_checked['yg_no_reim'])
                    $data[$prj_id]['yg_no_reim'] = $data[$prj_id]['yg_reim_uncfm_money_total'] - $data[$prj_id]['yg_finconfirm_cost'];
            }
        }

        $html = $this->get_table_html($type_name,$arr_search_checked,$is_city_search,$data);

        return $html;
    }

    /**
     * @param $search_btime
     * @param $search_etime
     * @param $fin_confirm
     * @param $search_state
     * @param $arr_search_field
     * @param $arr_search_city
     * @param $arr_search_prj
     * @param $is_city_search
     * @param $page
     * @param $pagesize
     */
    function  hd_html($search_btime,$search_etime,$fin_confirm,$search_state,$arr_search_checked,$arr_search_city,$arr_search_prj,$is_city_search,$page,$pagesize){
        $between = $where_card = $where_reim = $where_cost_list = $where_prj = '';
        $where_card = " AND A.STATUS = 1";
        $where_prj = " AND P.STATUS != 2";
        $where_prj .= " AND C.SCALETYPE = 4";

        $type_name = "�";

        //������ʼʱ��
        if ($search_btime) {
            $where_reim .= " AND to_char(IL.OCCUR_TIME,'yyyymmdd')>=" . @date("Ymd", strtotime($search_btime));
            $where_cost_list .= " AND to_char(CL.OCCUR_TIME,'yyyymmdd')>=" . @date("Ymd", strtotime($search_btime));
            //����ɱ�ר��
            $between .= " AND to_char(OCCUR_TIME,'yyyymmdd')>=" . @date("Ymd", strtotime($search_btime));
        }

        //��������ʱ��
        if ($search_etime) {
            $where_reim .= " AND to_char(IL.OCCUR_TIME,'yyyymmdd')<" . @date("Ymd", strtotime($search_etime));
            $where_cost_list .= " AND to_char(CL.OCCUR_TIME,'yyyymmdd')<" . @date("Ymd", strtotime($search_etime));
            //����ɱ�ר��
            $between .= " AND to_char(OCCUR_TIME,'yyyymmdd')<" . @date("Ymd", strtotime($search_etime));
        }

        //��Ŀ״̬
        if ($search_state)
            $where_prj .= " AND C.FSTATUS=$search_state";


        //���в�ѯ
        if($is_city_search) {
            foreach ($arr_search_city as $_city) {

                //��ȡ��Ŀ����
                $prj_info = $this->get_proinfo($_city,$where_prj);

                $prj_count = $prj_info['prj_count'];
                $prjs_range = $prj_info['prjs_range'];
                $case_range = $prj_info['case_range'];

                //��Ŀ��
                $data[$_city]['prj_count'] = $prj_count;

                //���̽��
                if ($arr_search_checked['hd_est_invest_money'])
                    $data[$_city]['hd_est_invest_money'] = $this->get_yg_sign_money(1,$case_range);

                //ʵ�����̽��
                if ($arr_search_checked['hd_act_invest_money'])
                    $data[$_city]['hd_act_invest_money'] = $this->get_yg_sign_money(2,$case_range);

                //��Ʊ���
                if ($arr_search_checked['hd_act_invoice_money'])
                    $data[$_city]['hd_act_invoice_money']  = $this->ds_income(3,$case_range,$where_reim);

                //�ؿ���
                if ($arr_search_checked['hd_payment_money'])
                    $data[$_city]['hd_payment_money']  = $this->ds_income(4,$case_range,$where_reim);

                //Ԥ�����·���/���ֳɱ�
                if ($arr_search_checked['hd_est_cost'])
                    $data[$_city]['hd_est_cost'] = "�ݲ�����";

                //ʵ�����·���
                if ($arr_search_checked['hd_act_cost'])
                    $data[$_city]['hd_act_cost'] = $this->ds_act_cost(1,$case_range,$between,$where_cost_list);

                //����δ�������·���
                $data[$_city]['whole_cost_money'] = $this->ds_act_cost(2,$case_range,$between,$where_cost_list);

                //���������·���
                if ($arr_search_checked['hd_finconfirm_cost']) {
                    $data[$_city]['hd_finconfirm_cost'] = $this->ds_act_cost(3, $case_range, $between, $where_cost_list);
                }

                //-----------------------------------------------------------------------------------//
                //-----------------------------------------------------------------------------------//
                //-----------------------------------------------------------------------------------//

                //δ��Ʊ���
                if($arr_search_checked['hd_noinvoice_money'])
                    $data[$_city]['hd_noinvoice_money'] = '�ݲ�����';

                //��Ʊδ�ؿ���
                if ($arr_search_checked['hd_invoice_nopayment_money'])
                    $data[$_city]['hd_invoice_nopayment_money'] = $data[$_city]['hd_act_invoice_money'] - $data[$_city]['hd_payment_money'];

                //�ѷ���δ����
                if($arr_search_checked['hd_no_reim'])
                    $data[$_city]['hd_no_reim'] = $data[$_city]['whole_cost_money'] - $data[$_city]['hd_finconfirm_cost'];

            }
        }
        //��Ŀ��ѯ
        else
        {
            $prjs_range = $case_range = '';

            //ҵ��ID����ĿIDӳ���ϵ
            $case_prjid = array();

            $search_city = implode(",",$arr_search_city);
            $search_prj = implode(",",$arr_search_prj);

            //����ǲ�ѯȫ����Ŀ  ---- ��������ѯ
            $sql = "SELECT C.PROJECT_ID,C.ID,P.CITY_ID,P.PROJECTNAME FROM $this->tbl_case C LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID = P.ID WHERE P.CITY_ID IN($search_city) $where_prj ORDER BY P.ID DESC";
            if ($arr_search_prj[0])
                $sql = "SELECT C.PROJECT_ID,C.ID,P.CITY_ID,P.PROJECTNAME FROM $this->tbl_case C LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID = P.ID WHERE P.ID IN($search_prj) $where_prj ORDER BY P.ID DESC";

            $sql = "SELECT * FROM ( SELECT A.*, ROWNUM RN FROM ($sql) A WHERE ROWNUM <= $page*$pagesize ) WHERE RN >  ($page-1)*$pagesize";
            $ret = M()->query($sql);

            foreach ($ret as $key => $val) {
                $data[$val['PROJECT_ID']]['id'] = $val['PROJECT_ID'];
                $data[$val['PROJECT_ID']]['case_id'] = $val['ID'];
                $data[$val['PROJECT_ID']]['prj_city'] = $val['CITY_ID'];
                $data[$val['PROJECT_ID']]['prj_name'] = $val['PROJECTNAME'];

                $case_prjid[$val['ID']] = $val['PROJECT_ID'];

                $prjs_range .= $val['PROJECT_ID'] . ',';
                $case_range .= $val['ID'] . ',';
            }

            $prjs_range = trim($prjs_range, ",");
            $prjs = explode(",", $prjs_range);
            $case_range = trim($case_range, ",");

            //���̽��
            if($arr_search_checked['hd_est_invest_money']){
                $sql = "SELECT SUM(MONEY) AS MONEY,CASE_ID FROM $this->tbl_ic IC WHERE IC.CASE_ID IN($case_range) GROUP BY IC.CASE_ID";

                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['hd_est_invest_money'] = floatval($val['MONEY']);
                }
            }

            //ʵ�����̽�� (��ȷ��״̬)
            if($arr_search_checked['hd_act_invest_money']){
                $sql = "SELECT SUM(MONEY) AS MONEY,CASE_ID FROM $this->tbl_ic IC WHERE IC.CASE_ID IN($case_range) AND STATUS = 5 GROUP BY IC.CASE_ID";

                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['hd_act_invest_money'] = floatval($val['MONEY']);
                }
            }

            //��Ʊ����
            if ($arr_search_checked['hd_act_invoice_money']) {
                $sql = "SELECT NVL(SUM(INCOME), 0) AS SUMINVOICES,CASE_ID
						FROM $this->tbl_income_list
						WHERE CASE_ID IN ($case_range) $between
						  AND STATUS = 3 GROUP  BY CASE_ID";

                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['hd_act_invoice_money'] = floatval($val['SUMINVOICES']);
                }
            }

            //�ؿ�����
            if ($arr_search_checked['hd_payment_money']) {
                $sql = "SELECT NVL(SUM(INCOME), 0) AS SUMINVOICES,CASE_ID
						FROM $this->tbl_income_list
						WHERE CASE_ID IN ($case_range) $between
						  AND STATUS = 4 GROUP  BY CASE_ID";

                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['hd_payment_money'] = floatval($val['SUMINVOICES']);
                }
            }

            //ʵ�����·���
            if ($arr_search_checked['hd_act_cost']) {
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM ERP_COST_LIST
						   WHERE CASE_ID IN ($case_range) $between AND  STATUS>1
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID  $where_cost_list GROUP BY CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['hd_act_cost'] = floatval($val['VCOST']);
                }
            }

            //����δȷ�Ͻ���Ŀ�����Ѿ����룬��������δȷ�ϵ���ֵ��
            if ($arr_search_checked['hd_finconfirm_cost']) {
                //����δ��������
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM $this->tbl_cost_list
						   WHERE CASE_ID IN ($case_range) $between AND STATUS  IN (1,2,4)
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID $where_cost_list GROUP BY CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['hd_reim_uncfm_money_total'] = floatval($val['VCOST']);
                }

                //�����ѱ�������
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST,CL.CASE_ID
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID,CASE_ID
						   FROM $this->tbl_cost_list
						   WHERE CASE_ID IN ($case_range) $between AND STATUS = 4
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID $where_cost_list GROUP BY CL.CASE_ID";
                $res = M()->query($sql);

                foreach ($res as $key => $val) {
                    $prj_id = $case_prjid[$val['CASE_ID']];
                    $data[$prj_id]['hd_finconfirm_cost'] = floatval($val['VCOST']);
                }

            }

            foreach ($prjs as $key => $val) {
                //ʵ��Ͷ�Ž��
                if($arr_search_checked['hd_act_put_moeny'])
                    $data[$val]['hd_act_put_moeny'] = '�ݲ�����';

                //δ��Ʊ���
                if($arr_search_checked['hd_noinvoice_money'])
                    $data[$val]['hd_noinvoice_money'] = '�ݲ�����';

                //��Ʊδ�ؿ���
                if($arr_search_checked['hd_invoice_nopayment_money'])
                    $data[$val]['hd_invoice_nopayment_money'] = $data[$val]['hd_act_invoice_money'] - $data[$val]['hd_payment_money'];

                //Ԥ������ͳ��
                if($arr_search_checked['hd_est_cost'])
                    $data[$val]['hd_est_cost'] = '�ݲ�����';

                //�ѷ���δ����
                if($arr_search_checked['hd_no_reim'])
                    $data[$val]['hd_no_reim'] = $data[$val]['hd_reim_uncfm_money_total'] - $data[$val]['hd_finconfirm_cost'];

            }
        }

        $html = $this->get_table_html($type_name,$arr_search_checked,$is_city_search,$data);

        return $html;
    }


    /**
     * @param $type_name  type����
     * @param $arr_search_checked
     * @param $is_city_search
     * @param $data
     */
    private function get_table_html($type_name,$arr_search_checked,$is_city_search,$data){
        /****ҳ����Ⱦ*****/
        $cfg = $this->get_city_info();

        $thstr = '<tr>';

        //���в�ѯ
        if ($is_city_search) {
                $thstr .= '<th width="45"><input id="checkrecord" type="checkbox" class="allx" /><span>ȫѡ</span></th><th>����</th><th>��Ŀ��</th>';
        }
        //��Ŀ��ѯ
        else
        {
               $thstr .= '<th width="45"><input id="checkrecord" type="checkbox" class="allx" /><span>ȫѡ</span></th><th>����</th><th>��Ŀ</th>';
        }

        #��ѡ��/չʾ��
        foreach ($arr_search_checked as $_checked) {
            $thstr .= '<th>' . $_checked . '</th>';
        }
        $thstr .= '</tr>';

        $trstr = '';

        if ($data) {
            foreach ($data as $k => $v) {
                if ($is_city_search) { //����
                    $trstr .= '<tr><td><input type="checkbox" class="floatnone" name="arr_record[]" value="' . $k . '" /></td><td width="100px">' . $cfg['city'][$k] . '<font color="red">[' . $type_name . ']</font>' . '</td>';
                    $trstr .= '<td>' . $v['prj_count'] . '</td>';
                }
                else
                {
                    $trstr .= '<tr><td><input type="checkbox" class="floatnone" name="arr_record[]" value="' . $k . '" /></td><td>' . $cfg['city'][$v['prj_city']] . '<font color="red">[' . $type_name . ']</font>' . '</td>';
                    $trstr .= '<td>' . $v['prj_name'] . '</td>';
                }

                #��ѡ��/չʾ��
                foreach ($arr_search_checked as $key=>$_checked) {
                    $trstr .= '<td>' . ($v[$key]?$v[$key]:0) . '</td>';
                }

                $trstr .= '</tr>';
            }
        }

        return $thstr . $trstr;
    }

    /**
     * @param $city  ��������
     * @param $where   where����
     * @return array   ��������
     */
    private function get_proinfo($city,$where){

        $return = array(
          'prj_count'=>0,
          'prjs_range'=>0,
          'case_range'=>0,
        );

        //��Ŀ��Ϣ
        $sql = "SELECT C.PROJECT_ID,C.ID FROM $this->tbl_case C  LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID = P.ID WHERE P.CITY_ID = " . $city . $where;
        $prj_info = M()->query($sql);

        //��Ŀ��
        $return['prj_count'] = count($prj_info);

        //��Ŀ�ַ�����
        $prjs_range = '';
        $case_range = '';
        if (is_array($prj_info)) {
            foreach ($prj_info as $k => $r) {
                $prjs_range .= $r['PROJECT_ID'] . ',';
                $case_range .= $r['ID'] . ',';
            }
        }
        $prjs_range = substr($prjs_range, 0, -1);
        $case_range = substr($case_range, 0, -1);

        $return['prjs_range'] = $prjs_range;
        $return['case_range'] = $case_range;

        return $return;
    }


    /**
     * @param $type  ����  1��δ�ɽ�����   2���쿨��   3��ʵ�ʳɽ�����  4��ȷ�ϳɽ�����  5������ȷ�ϰ쿨��  6: �ܰ쿨������������
     * @param $city
     * @param $prjs_range
     * @param $where
     * @return int
     */
    function  get_pro_card($type,$city,$prjs_range,$where)
    {
        $return = 0;

        switch($type){
            case 1:
                $sql = "SELECT COUNT(1) COUNTS FROM $this->tbl A  WHERE  A.CITY_ID='" . $city . "' AND A.CARDSTATUS IN (1,2,4) AND A.PRJ_ID IN (" . $prjs_range . ") $where  ";
                break;
            case 2:
                $sql = "SELECT COUNT(1) COUNTS FROM $this->tbl A  WHERE  A.CITY_ID='" . $city . "' AND A.CARDSTATUS IN (1,2,3) AND A.PRJ_ID IN (" . $prjs_range . ") $where  ";
                break;
            case 3:
                $sql = "SELECT COUNT(1) COUNTS FROM $this->tbl A  WHERE  A.CITY_ID='" . $city . "' AND A.CARDSTATUS IN (3) AND A.PRJ_ID IN (" . $prjs_range . ") $where  ";
                break;
            case 4:
                $sql = "SELECT COUNT(1) COUNTS FROM $this->tbl A  WHERE  A.CITY_ID='" . $city . "' AND A.CARDSTATUS = 3 AND A.INVOICE_STATUS IN(2,3) AND A.PRJ_ID IN (" . $prjs_range . ") $where  ";
                break;
            case 5:
                $sql = "SELECT COUNT(1) COUNTS FROM $this->tbl A  WHERE  A.CITY_ID='" . $city . "' AND INVOICE_STATUS IN(1,5) AND A.FINANCIALCONFIRM=3 AND A.CARDSTATUS IN (1,2,3) AND A.PRJ_ID IN (" . $prjs_range . ") $where  ";
                break;
            case 6:
                $sql = "SELECT COUNT(1) COUNTS FROM $this->tbl A  WHERE  A.CITY_ID='" . $city . "' AND A.PRJ_ID IN (" . $prjs_range . ") $where  ";
                break;
            default:
                $sql = "SELECT COUNT(1) COUNTS FROM $this->tbl A  WHERE  A.CITY_ID='" . $city . "' AND A.CARDSTATUS IN (1,2,4) AND A.PRJ_ID IN (" . $prjs_range . ") $where  ";
        }

        $res = M()->query($sql);

        $return = intval($res[0]['COUNTS']);

        return $return;
    }


    /**
     * @param $type  ����  1��δ�ɽ�����-����   2���쿨�� -����  3��ʵ�ʳɽ�����-����  4��ȷ�ϳɽ�����-����  5������ȷ�ϰ쿨��-����
     * @param $city
     * @param $prjs_range
     * @param $where
     * @return int
     */
    function get_card_profit($type,$city,$prjs_range,$where){

        $return = 0;

        switch($type){
            case 1:
                $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY FROM  $this->tbl_pay_ment PA  INNER JOIN  $this->tbl A ON PA.MID = A.ID  WHERE   A.CITY_ID='" . $city . "'  AND A.PRJ_ID IN (" . $prjs_range . ") AND A.CARDSTATUS IN (1,2,4) $where";
                break;
            case 2:
                $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY FROM  $this->tbl_pay_ment PA  INNER JOIN  $this->tbl A ON PA.MID = A.ID  WHERE   A.CITY_ID='" . $city . "'  AND A.PRJ_ID IN (" . $prjs_range . ") AND A.CARDSTATUS IN (1,2,3) $where";
                break;
            case 3:
                $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY FROM  $this->tbl_pay_ment PA  INNER JOIN  $this->tbl A ON PA.MID = A.ID  WHERE   A.CITY_ID='" . $city . "'  AND A.PRJ_ID IN (" . $prjs_range . ") AND A.CARDSTATUS IN (3) $where";
                break;
            case 4:
                $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY FROM  $this->tbl_pay_ment PA  INNER JOIN  $this->tbl A ON PA.MID = A.ID  WHERE   A.CITY_ID='" . $city . "'  AND A.PRJ_ID IN (" . $prjs_range . ") AND A.CARDSTATUS = 3 AND A.INVOICE_STATUS IN(2,3) $where";
                break;
            case 5:
                $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY FROM  $this->tbl_pay_ment PA  INNER JOIN  $this->tbl A ON PA.MID = A.ID  WHERE   A.CITY_ID='" . $city . "'  AND A.PRJ_ID IN (" . $prjs_range . ") AND A.INVOICE_STATUS IN(1,5) AND A.CARDSTATUS IN (1,2,3) AND A.FINANCIALCONFIRM=3 $where";
                break;
            default:
                $sql = "SELECT SUM(TRADE_MONEY) AS TRADE_MONEY FROM  $this->tbl_pay_ment PA  INNER JOIN  $this->tbl A ON PA.MID = A.ID  WHERE   A.CITY_ID='" . $city . "'  AND A.PRJ_ID IN (" . $prjs_range . ") AND A.CARDSTATUS IN (1,2,4) $where";
        }

        $res = M()->query($sql);

        $return = intval($res[0]['TRADE_MONEY']);

        return $return;

    }


    /**
     * ��ȡԤ�����·��� (98�۳�����)
     * @param $city
     * @param $prjs_range
     * @param $where
     * @return float|int
     */
    function ds_est_cost($city,$prjs_range,$where){

        $return = 0;

        $sql = "SELECT SUM(AMOUNT) AS AMOUNT  FROM $this->tbl_budget PB LEFT JOIN $this->tbl_fee BF ON BF.BUDGETID = PB.ID  LEFT JOIN $this->tbl_case C ON PB.CASE_ID=C.ID LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID=P.ID WHERE P.CITY_ID= '" . $city . "' AND P.ID IN($prjs_range) $where AND BF.ISONLINE=0 AND BF.FEEID != 98";

        $res = M()->query($sql);
        $return = floatval($res[0]['AMOUNT']);

        return $return;
    }

    /**
     * @param $type  1:  ʵ�����·���   2: δ�������·���   3: ����ȷ�����·���  4: ����δȷ�Ͻ��
     * @param $case_range
     * @param $between
     * @param $where_cost_list
     * @return int
     */
    function ds_act_cost($type,$case_range,$between,$where_cost_list){
        $return = 0;

        switch($type)
        {
            case 1:
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID
						   FROM $this->tbl_cost_list
						   WHERE CASE_ID IN ($case_range) $between AND  STATUS > 1
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID $where_cost_list ";
                break;
            case 2:
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID
						   FROM $this->tbl_cost_list
						   WHERE CASE_ID IN ($case_range) $between AND  STATUS IN (1,2,4)
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID $where_cost_list ";
                break;
            case 3:
                $sql = "SELECT NVL(SUM(FEE),0) AS VCOST
						   FROM $this->tbl_cost_list CL,(
						   SELECT MAX(ID) ID
						   FROM $this->tbl_cost_list
						   WHERE CASE_ID IN ($case_range) $between AND  STATUS = 4
						   GROUP BY CASE_ID,ORG_ENTITY_ID,ORG_EXPEND_ID,TYPE) B
						   WHERE CL.ID=B.ID $where_cost_list ";
                break;
        }

        $res = M()->query($sql);
        $return = floatval($res[0]['VCOST']);

        return $return;
    }

    /**
     * @param $type  1: Ԥ��������   2��Ԥ����������
     * @param $case_range
     * @return float|int
     */
    function ds_est_income($type,$case_range){
        $return = 0;

        switch($type) {
            case 1:
                $sql = "SELECT SUM(SUMPROFIT) AS SUMPROFIT FROM $this->tbl_budget WHERE CASE_ID IN ($case_range)";
                break;
            case 2:
                $sql = "SELECT SUM(OFFLINE_COST_SUM_PROFIT) AS SUMPROFIT FROM $this->tbl_budget WHERE CASE_ID IN ($case_range)";
                break;
        }

        $res = M()->query($sql);
        $return = floatval($res[0]['SUMPROFIT']);

        return $return;
    }

    /**
     * @param $type  1: ʵ������  2��ȷ������  3����Ʊ����  4���ؿ�����
     * @param $case_range
     * @param $where_reim
     * @return float|int
     */
    function ds_income($type,$case_range,$where_reim){
        $return = 0;

        switch($type){
            case 1:
                $sql = "SELECT NVL(SUM(IL.INCOME), 0) AS SUMINVOICES
						FROM ERP_INCOME_LIST IL
						WHERE IL.CASE_ID IN ($case_range)
						  AND IL.STATUS = 1 $where_reim";
                break;
            case 2:
                $sql = "SELECT NVL(SUM(IL.INCOME), 0) AS SUMINVOICES
						FROM ERP_INCOME_LIST IL
						WHERE IL.CASE_ID IN ($case_range)
						  AND IL.STATUS = 2 $where_reim";
                break;
            case 3:
                $sql = "SELECT NVL(SUM(IL.INCOME), 0) AS SUMINVOICES
						FROM ERP_INCOME_LIST IL
						WHERE IL.CASE_ID IN ($case_range)
						  AND IL.STATUS = 3 $where_reim";
                break;
            case 4:
                $sql = "SELECT NVL(SUM(IL.INCOME), 0) AS SUMINVOICES
						FROM ERP_INCOME_LIST IL
						WHERE IL.CASE_ID IN ($case_range)
						  AND IL.STATUS = 4 $where_reim";
                break;

        }

        $res = M()->query($sql);
        $return = floatval($res[0]['SUMINVOICES']);

        return $return;
    }

    /**
     * @param $city
     * @param $where_prj
     * @return float|int
     */
    function ds_ad_cost($city,$where_prj)
    {
     //�ۺ������
        $return = 0;

        $sql = "SELECT SUM(AMOUNT) AS AMOUNT  FROM $this->tbl_budget PB LEFT JOIN $this->tbl_fee BF ON PB.ID = BF.BUDGETID  LEFT JOIN $this->tbl_case C ON PB.CASE_ID=C.ID LEFT JOIN $this->tbl_prj P ON C.PROJECT_ID=P.ID WHERE P.CITY_ID= '" . $city . "' $where_prj AND BF.ISONLINE=-1 AND BF.ISVALID = -1 AND BF.FEEID = 98";
        $res = M()->query($sql);
        $return = floatval($res[0]['AMOUNT']);

        return $return;
    }

    /**
     * @param $prjs_range
     * @return int
     */
    function ds_est_client($prjs_range)
    {
        //Ԥ��������
        $return = 0;

        $return = M("Erp_budgetsale")->where("PROJECTT_ID IN ($prjs_range)")->sum('CUSTOMERS');

        return $return;
    }

    /**
     * @param $prjs_range
     * @return int
     */
    function ds_est_deal($prjs_range){
        //Ԥ���ɽ�����
        $return = 0;

        $return = M("Erp_budgetsale")->where("PROJECTT_ID IN ($prjs_range)")->sum('SETS');

        return $return;
    }


    /**
     * @param $type  1: ʵ�ʳɽ�����    2: ȷ�ϳɽ�����
     * @param $case_range
     * @return int
     */
    function fx_act_deal($type,$case_range){
        $return = 0;

        switch($type){
            case 1:
                $sql = "SELECT COUNT(1) COUNTS FROM $this->tbl_md MD WHERE MD.CASE_ID IN($case_range)";
                break;
            case 2:
                $sql = "SELECT COUNT(1) COUNTS FROM $this->tbl_md MD WHERE MD.CASE_ID IN($case_range) AND INVOICE_STATUS IN(2,3)";
                break;
        }

        $res = M()->query($sql);
        $return = intval($res[0]['COUNTS']);

        return $return;
    }


    /**
     * @param $type  1 : ǩԼ���  2��ʵ��ǩԼ���
     * @param $case_range
     * @return float|int
     */
    function get_yg_sign_money($type,$case_range){
        $return = 0;

        switch($type){
            case 1:
                $sql = "SELECT SUM(MONEY) AS MONEY FROM $this->tbl_ic AD WHERE AD.CASE_ID IN($case_range)";
                break;
            case 2:
                $sql = "SELECT SUM(MONEY) AS MONEY  FROM $this->tbl_ic AD WHERE AD.CASE_ID IN($case_range) AND AD.STATUS  = 5";
                break;
        }

        $res = M()->query($sql);
        $return = floatval($res[0]['MONEY']);

        return $return;
    }

    /**
     * ��ȡ������Ϣ
     * @return array
     */
    private function get_city_info(){
        $cfg = array();

        $citylist = M('Erp_city')->where("ISVALID=-1")->select();
        foreach ($citylist as $k => $one) {
            $cfg['city'][$one['ID']] = $one['NAME'];
        }

        return $cfg;
    }


}