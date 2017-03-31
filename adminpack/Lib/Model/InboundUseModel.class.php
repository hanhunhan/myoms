<?php
/**
 * ��������������MODEL
 */
class InboundUseModel extends Model{

    protected $tablePrefix  =   'ERP_';
    protected $tableApplyList = 'DISPLACE_APPLYLIST';
    protected $tableWareHouse = 'DISPLACE_WAREHOUSE';
    protected $tableApplyDetail = 'DISPLACE_APPLYDETAIL';

    const INCOME_PERCENT = 0.4; //�ڲ����ú������Ŀ�������

    /***������״̬***/
    private  $_conf_requisition_status = array(
        0 => 'δ�ύ',
        1 => '�����',
        2 => '���ͨ��',
        3 => '���δͨ��',
        4 => '���',
    );

    /***�û��ֿ�״̬***/
    private $_conf_list_status = array(
        1 => 'δ���',
        2 => '�����',
        3 => '��Ŀ����',
        4 => '��˾�ڲ�����',
        5 => '������',
        6 => '�ѱ���',
    );

    private $_conf_flow_displace_type = array(
        1 => 'shoumai', //����
        2 => 'neibulingyong', //�ڲ�����
        3 => 'baosun', //����
       // 4 => 'baosun', //�������
    );

    private $_conf_flow_displace_desc = array(
        1 => '����',
        2 => '�ڲ�����',
        3 => '����',
    );

    /***�������ñ���***/


    //���캯��
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * ��ȡ�û���״̬
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_requisition_status(){
        return $this->_conf_requisition_status;
    }

    /**
     * ��ȡ�ֿ���ϸ״̬����
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_list_status()
    {
        return $this->_conf_list_status;
    }


    /**
     * ��ȡ����������
     * @return array
     */
    public function get_flow_displace_type(){
        return $this->_conf_flow_displace_type;
    }



    /**
     * �����û���ID��ȡ��Ϣ
     * @param $id
     * @param array $searchField
     * @return array
     */
    public function getDisplaceById($id, $searchField = array()){

        $return = array();

        //��ȡ��ֵ
        if(is_array($id) && !empty($id))
        {
            $idStr = implode(',', $id);
            $condWhere = "ID IN (".$idStr.")";
        }
        else
        {
            $id = intval($id);
            $condWhere = "ID = '".$id."'";
        }

        if(!empty($searchField))
            $searchFieldStr = implode(',',$searchField);

        $return = M($this->tablePrefix . $this->tableNameMain)
            ->field($searchFieldStr)
            ->where($condWhere)->query();

        return $return;

    }

    /**
     * �����û���ϸID��ȡ��Ϣ
     * @param $id
     * @param array $searchField
     * @return array
     */
    public function getDisplaceDetailById($id, $searchField = array()){

        $return = array();

        //��ȡ��ֵ
        if(is_array($id) && !empty($id))
        {
            $idStr = implode(',', $id);
            $condWhere = "ID IN (".$idStr.")";
        }
        else
        {
            $id = intval($id);
            $condWhere = "ID = '".$id."'";
        }

        if(!empty($searchField))
            $searchFieldStr = implode(',',$searchField);

        $return = M($this->tablePrefix . $this->tableNameList)
            ->field($searchFieldStr)
            ->where($condWhere)->query();

        return $return;

    }

    /**
     * ��ȡ��Ŀ״̬
     * @param $lId
     * @return array|mixed
     */
    public function getApplyListStatusById($lId){

        $sql = 'select status from ' . $this->tablePrefix . $this->tableApplyList . ' where id = ' . $lId;
        $queryRet = D()->query($sql);

        return $queryRet;
    }


    /**
     * �������������״̬Ϊ������
     * @param $appId
     * @return bool
     */
    public function submitInboundUseById($appId,$status){
        $return = false;

        //�����������ܱ�״̬
        $updateSql = 'update '. $this->tablePrefix . $this->tableApplyList . ' set status = ' . $status . ' where id = ' . $appId;
        $updateMainRet = M($this->tablePrefix . $this->tableApplyList)->query($updateSql);

        if($updateMainRet !== false)
            $return = true;

        return $return;
    }

    /**
     * ɾ��������
     * @param $lId
     * @return bool
     */
    public function delDisplaceApplyById($lId){

        //ɾ����ϸ
        $sql = 'delete from ' . $this->tablePrefix . $this->tableApplyDetail . ' where list_id = ' . $lId;
        $delRet = D()->query($sql);

        //ɾ��������
        $sql = 'delete from ' . $this->tablePrefix . $this->tableApplyList . ' where id = ' . $lId;
        $delDeatailRet = D()->query($sql);

        if($delRet===false || $delDeatailRet===false){
            return false;
        }
        return true;
    }


    /**
     * �������ͨ����ʱ��ԭ�������
     * @param $appId
     * @return bool
     */
    public function updateInboundUseById($appId){

        $return = false;

        $sql = "SELECT
        C.*,A.AMOUNT,A.ID AS DETAILID,
        to_char(C.INBOUND_TIME,'YYYY-MM-DD hh24:mi:ss') as NEW_INBOUND_TIME
        FROM ERP_DISPLACE_APPLYDETAIL A LEFT JOIN ERP_DISPLACE_APPLYLIST B ON A.LIST_ID = B.ID
 LEFT JOIN ERP_DISPLACE_WAREHOUSE C ON A.DID = C.ID WHERE B.ID = " . $appId;
        $queryRet = D()->query($sql); //��ȡ����

        foreach($queryRet as $key=>$val){ //���¿��ֵ

            $data = array();
            if($val['INBOUND_STATUS'] != 2){ //���״̬�Ѿ�������2��ʱ����Ҫ����һ������

                //�����µ�����
                $data = $val;
                $data['UPDATE_USERID'] = null; //��Ϊ��
                $data['UPDATE_TIME'] = null; //��Ϊʱ��
                $data['PARENTID'] = $val['ID'];
                $data['NUM'] = $val['AMOUNT']; //����
                $data['INBOUND_STATUS'] = 2;
                //ʱ��ת��
                $data['LIVETIME'] = oracle_date_format($data['LIVETIME'],'Y-m-d H:i:s');
                $data['ALARMTIME'] = oracle_date_format($data['ALARMTIME'],'Y-m-d H:i:s');
                $data['ADD_TIME'] = oracle_date_format($data['ADD_TIME'],'Y-m-d H:i:s');
                $data['INBOUND_TIME'] = $data['NEW_INBOUND_TIME'];

                unset($data['ID']);
                $resLess = M("Erp_displace_warehouse")
                    ->add($data);

                if ($resLess === false) {
                    return $return;
                }

                //�滻���µĿ��ID ---- �Ա����������������
                $updateData = array(
                    'DID' => $resLess,
                );

                $updateRet = M("Erp_displace_applydetail")->where('ID = ' . $val['DETAILID'])->save($updateData);
                if ($updateRet === false) {
                    return $return;
                }

            }else{
                $sql = 'UPDATE ERP_DISPLACE_WAREHOUSE SET NUM = NUM + ' . intval($val['AMOUNT']) . ' WHERE ID = ' . $val['ID'];
                $updateRet = D()->query($sql);

                if($updateRet===false)
                    return $return;
            }
        }

        return true;
    }


    /**
     * ��������ʱ��ҵ������صĲ���
     * @param $applyId ������list_ID
     * @param $flowType ����������
     * @return bool
     */
    public function updateBusinessOperate($applyId,$flowType){

        $return = false;

        if($flowType == 4) { //��Ŀ���ô����ó���������
            $sql = "select B.*,A.USE_PRICE,A.USE_NUM AS PURCHASE_USE_NUM,A.ID AS USEID,to_char(B.INBOUND_TIME,'YYYY-MM-DD HH24:MI:SS') AS NEW_INBOUND_TIME from erp_warehouse_use_details A left join erp_displace_warehouse B ON A.wh_id = B.id where A.type = 2 and A.PL_ID = " . $applyId;
        }
        else  //�����߹�������˻�ȡID
        {
            $sql = "SELECT  A.ID AS DETAILID,C.*,A.AMOUNT,to_char(c.INBOUND_TIME,'YYYY-MM-DD HH24:MI:SS') AS NEW_INBOUND_TIME,A.MONEY FROM ERP_DISPLACE_APPLYDETAIL A LEFT JOIN ERP_DISPLACE_APPLYLIST B ON A.LIST_ID = B.ID
 LEFT JOIN ERP_DISPLACE_WAREHOUSE C ON A.DID = C.ID WHERE B.ID = " . $applyId;
        }
        $queryRet = D()->query($sql); //��ȡ����
        if(!empty($queryRet)){
            foreach($queryRet as $key=>$val){
                //��ȡ�ڿ�״̬
                switch($flowType){
                    case 1:
                        $inboundStatus = 5; //������
                        break;
                    case 2:
                        $inboundStatus = 4; //�ڲ�����
                        break;
                    case 3:
                        $inboundStatus = 6; //����
                        break;
                    case 4:
                        $inboundStatus = 3; //��Ŀ����
                        break;
                    default:
                        break;
                }

                $data = array(); //��ʼ��
                if($flowType==4){  //�������Ŀ����

                    if($val['NUM'] > 0 || ($val['USE_NUM'] - $val['PURCHASE_USE_NUM']) > 0) { //���û�д�����ȫ
                        //step 1
                        //�����µ�����
                        $data = $queryRet[$key];
                        $data['UPDATE_USERID'] = $_SESSION['uinfo']['uid']; //��Ϊ��
                        $data['UPDATE_TIME'] = date('Y-m-d H:i:s'); //��Ϊʱ��
                        $data['PARENTID'] = $val['ID'];
                        $data['NUM'] = $val['PURCHASE_USE_NUM']; //����
                        $data['INBOUND_STATUS'] = $inboundStatus;
                        //ʱ��ת��
                        $data['LIVETIME'] = oracle_date_format($data['LIVETIME'], 'Y-m-d H:i:s');
                        $data['ALARMTIME'] = oracle_date_format($data['ALARMTIME'], 'Y-m-d H:i:s');
                        $data['ADD_TIME'] = oracle_date_format($data['ADD_TIME'], 'Y-m-d H:i:s');
                        $data['INBOUND_TIME'] = $data['NEW_INBOUND_TIME'];
                        $data['USE_NUM'] = 0; //USE_NUM ��Ϊ0
                        unset($data['ID']);

                        $resLess = M("Erp_displace_warehouse")
                            ->add($data);

                        if ($resLess === false) {
                            return $return;
                        }

                        //step 2
                        //erp_warehouse_use_details ���ñ��滻���µ��û��ֿ�ID ---- �Ա���������ɾ������
                        $updateData = array(
                            'WH_ID' => $resLess,
                        );

                        $updateRet = M("erp_warehouse_use_details")->where('ID = ' . $val['USEID'])->save($updateData);
                        if ($updateRet === false) {
                            return $return;
                        }

                        //step 3
                        //����ԭ�ȵĿ���
                        $sql = 'update erp_displace_warehouse set use_num = use_num - ' . $val['PURCHASE_USE_NUM'] . ' where id = ' . $val['ID'];
                        $updateRet = D()->query($sql);

                        if ($updateRet === false) {
                            return $return;
                        }
                    }else{ //�������ö�Ϊ0ʱ
                        $data['UPDATE_USERID'] = $_SESSION['uinfo']['uid']; //��Ϊ��
                        $data['UPDATE_TIME'] = date('Y-m-d H:i:s'); //��Ϊʱ��
                        $data['NUM'] = $val['USE_NUM']; //����
                        $data['INBOUND_STATUS'] = $inboundStatus;
                        $data['USE_NUM'] = 0; //ʹ������

                        $res = M("Erp_displace_warehouse")
                            ->where("ID=" . $val['ID'])
                            ->save($data);

                        if ($res === false) {
                            return $return;
                        }
                    }

                }else { //����Ƿ���Ŀ����

                    if ($val['NUM'] == 0 && $val['USE_NUM'] == 0) {  //����Ǵ���ȫ��
                        $data['UPDATE_USERID'] = $_SESSION['uinfo']['uid']; //��Ϊ��
                        $data['UPDATE_TIME'] = date('Y-m-d H:i:s'); //��Ϊʱ��
                        $data['NUM'] = $val['AMOUNT']; //����
                        $data['INBOUND_STATUS'] = $inboundStatus;

                        $res = M("Erp_displace_warehouse")
                            ->where("ID=" . $val['ID'])
                            ->save($data);

                        if ($res === false) {
                            return $return;
                        }

                    } else  { //�������

                        //�����µ�����
                        $data = $queryRet[$key];
                        $data['UPDATE_USERID'] = $_SESSION['uinfo']['uid']; //��Ϊ��
                        $data['UPDATE_TIME'] = date('Y-m-d H:i:s'); //��Ϊʱ��
                        $data['PARENTID'] = $val['ID'];
                        $data['NUM'] = $val['AMOUNT']; //����
                        $data['INBOUND_STATUS'] = $inboundStatus;
                        //ʱ��ת��
                        $data['LIVETIME'] = oracle_date_format($data['LIVETIME'],'Y-m-d H:i:s');
                        $data['ALARMTIME'] = oracle_date_format($data['ALARMTIME'],'Y-m-d H:i:s');
                        $data['ADD_TIME'] = oracle_date_format($data['ADD_TIME'],'Y-m-d H:i:s');
                        $data['INBOUND_TIME'] = $data['NEW_INBOUND_TIME'];
                        unset($data['ID']);
                        $resLess = M("Erp_displace_warehouse")
                            ->add($data);

                        if ($resLess === false) {
                            return $return;
                        }

                        //�滻���µĿ��ID ---- �Ա����������������
                        $updateData = array(
                            'DID' => $resLess,
                        );

                        $updateRet = M("Erp_displace_applydetail")->where('ID = ' . $val['DETAILID'])->save($updateData);
                        if ($updateRet === false) {
                            return $return;
                        }
                    }

                    //������ڲ����ã�����Ҫ������
                    if ($flowType == 2) {
                        //��������
                        $income_info['CASE_ID'] = $val['CASE_ID'];
                        $income_info['ENTITY_ID'] = $val['DR_ID'];
                        $income_info['ORG_ENTITY_ID'] = $val['DR_ID'];
                        $income_info['PAY_ID'] = $val['ID'];
                        $income_info['ORG_PAY_ID'] = $val['ID'];
                        $income_info['INCOME_FROM'] = 26;//�û���Ʒ����
                        $income_info['INCOME'] = floatval(self::INCOME_PERCENT * $val['AMOUNT'] * $val['PRICE']);
                        $income_info['INCOME_REMARK'] = '�û���Ʒ�ڲ���������';
                        $income_info['ADD_UID'] = $_SESSION['uinfo']['uid'];
                        $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s", time());
                        $income_model = D('ProjectIncome');
                        $ret = $income_model->add_income_info($income_info);

                        if ($ret === false) {
                            return $return;
                        }
                    }
                }
            }
        }
        $return = true;

        return $return;
    }

    /**
     * �����û�����Ϣ
     * @param $listId ������ID
     * @return bool
     */
    public function backDisplaceUse($listId){

        //��ȡ�ɹ���ID
        $purchaseIds = D('ReimbursementDetail')->get_detail_info_by_listid($listId,array('BUSINESS_ID'));

        //��ȡ�û����ID
        if(!empty($purchaseIds)){
            foreach ($purchaseIds as $purchase) {
                $useInfo = D('WarehouseUse')->getDisplaceUseByPurchaseId($purchase['BUSINESS_ID']);
                if(notEmptyArray($useInfo)){
                    foreach($useInfo as $useInfoItem) {
                        $whId = $useInfoItem['WH_ID'];
                        $USE_NUM = $useInfoItem['USE_NUM'];

                        //���µ�ǰ���״̬�Ϳ������
                        $updateArr = array(
                            'INBOUND_STATUS' => 2, //�����
                            'USE_NUM' => $USE_NUM,
                            'NUM' => 0,
                            'UPDATE_TIME'=>null,
                        );

                        $return = M('erp_displace_warehouse')->where('ID = ' . $whId)->save($updateArr);

                        if ($return === false) {
                            return false;
                        }
                    }

                }
            }
        }
        return true;
    }

    /**
     * ������Ŀ���� ����Ŀ�ɹ����ã�
     * @param $useInfo
     * @return bool
     */
    public function  insertProIncome($useInfo){
        $return = false;
        $displaceInfo = D('Displace')->getDisplaceDetailById($useInfo['WH_ID'],array('ID','CASE_ID','DR_ID'));

        if(notEmptyArray($displaceInfo)){
            //��������
            $income_info['CASE_ID'] = $displaceInfo[0]['CASE_ID'];
            $income_info['ENTITY_ID'] = $displaceInfo[0]['DR_ID'];
            $income_info['ORG_ENTITY_ID'] = $displaceInfo[0]['DR_ID'];
            $income_info['PAY_ID'] = $displaceInfo[0]['ID'];
            $income_info['ORG_PAY_ID'] = $displaceInfo[0]['ID'];
            $income_info['INCOME_FROM'] = 27;//�û���Ʒ����
            $income_info['INCOME'] = floatval(self::INCOME_PERCENT * $useInfo['USE_PRICE'] * $useInfo['USE_NUM']);
            $income_info['INCOME_REMARK'] = '�û���Ʒ��Ŀ��������';
            $income_info['ADD_UID'] = $_SESSION['uinfo']['uid'];
            $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s", time());

            $income_model = D('ProjectIncome');
            $return = $income_model->add_income_info($income_info);
        }

        return $return;
    }

    /**
     * ����ID������Ϣ
     *
     * @access	public
     * @param	string  $ids Ҫ���µļ�¼
     * @param array $update_arr Ҫ���µ��ֶ�
     * @return
     */
    public function update_info_by_id($ids, $update_arr)
    {
        if(is_array($ids) && !empty($ids))
        {
            $id_str = implode(",", $ids);
            $cond_where = "ID IN ($id_str)";
        }
        else
        {
            $cond_where = "ID = $ids";
        }

        $res = self::update_info_by_cond($cond_where, $update_arr);

        return $res;
    }


    /**
     * ��������������Ϣ
     *
     * @access	public
     * @param	string  $cond_where Ҫ���µļ�¼
     * @param array $update_arr Ҫ���µ��ֶεļ�ֵ��
     * @return
     */
    public function update_info_by_cond($cond_where, $update_arr)
    {
        $up_num = 0;

        if(is_array($update_arr) && !empty($update_arr) && $cond_where != '')
        {
            $up_num = $this->where($cond_where)->save($update_arr);
        }

        return $up_num > 0  ? $up_num : FALSE;
    }

    /**
     * ���������Ϣ
     */

}

/* End of file DisplaceModel.class.php */
/* Location: ./Lib/Model/DisplaceModel.class.php */