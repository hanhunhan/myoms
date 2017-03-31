<?php
/**
 * 售卖、报损、领用MODEL
 */
class InboundUseModel extends Model{

    protected $tablePrefix  =   'ERP_';
    protected $tableApplyList = 'DISPLACE_APPLYLIST';
    protected $tableWareHouse = 'DISPLACE_WAREHOUSE';
    protected $tableApplyDetail = 'DISPLACE_APPLYDETAIL';

    const INCOME_PERCENT = 0.4; //内部领用后进入项目收益比例

    /***工作流状态***/
    private  $_conf_requisition_status = array(
        0 => '未提交',
        1 => '审核中',
        2 => '审核通过',
        3 => '审核未通过',
        4 => '完成',
    );

    /***置换仓库状态***/
    private $_conf_list_status = array(
        1 => '未入库',
        2 => '已入库',
        3 => '项目领用',
        4 => '公司内部领用',
        5 => '已售卖',
        6 => '已报损',
    );

    private $_conf_flow_displace_type = array(
        1 => 'shoumai', //售卖
        2 => 'neibulingyong', //内部领用
        3 => 'baosun', //报损
       // 4 => 'baosun', //售卖变更
    );

    private $_conf_flow_displace_desc = array(
        1 => '售卖',
        2 => '内部领用',
        3 => '报损',
    );

    /***售卖领用报损***/


    //构造函数
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * 获取置换单状态
     *
     * @access	public
     * @param	none
     * @return	array
     */
    public function get_conf_requisition_status(){
        return $this->_conf_requisition_status;
    }

    /**
     * 获取仓库明细状态数组
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
     * 获取工作流类型
     * @return array
     */
    public function get_flow_displace_type(){
        return $this->_conf_flow_displace_type;
    }



    /**
     * 根据置换单ID获取信息
     * @param $id
     * @param array $searchField
     * @return array
     */
    public function getDisplaceById($id, $searchField = array()){

        $return = array();

        //获取数值
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
     * 根据置换明细ID获取信息
     * @param $id
     * @param array $searchField
     * @return array
     */
    public function getDisplaceDetailById($id, $searchField = array()){

        $return = array();

        //获取数值
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
     * 获取项目状态
     * @param $lId
     * @return array|mixed
     */
    public function getApplyListStatusById($lId){

        $sql = 'select status from ' . $this->tablePrefix . $this->tableApplyList . ' where id = ' . $lId;
        $queryRet = D()->query($sql);

        return $queryRet;
    }


    /**
     * 更新售卖申请的状态为申请中
     * @param $appId
     * @return bool
     */
    public function submitInboundUseById($appId,$status){
        $return = false;

        //更新售卖汇总表状态
        $updateSql = 'update '. $this->tablePrefix . $this->tableApplyList . ' set status = ' . $status . ' where id = ' . $appId;
        $updateMainRet = M($this->tablePrefix . $this->tableApplyList)->query($updateSql);

        if($updateMainRet !== false)
            $return = true;

        return $return;
    }

    /**
     * 删除工作流
     * @param $lId
     * @return bool
     */
    public function delDisplaceApplyById($lId){

        //删除明细
        $sql = 'delete from ' . $this->tablePrefix . $this->tableApplyDetail . ' where list_id = ' . $lId;
        $delRet = D()->query($sql);

        //删除工作流
        $sql = 'delete from ' . $this->tablePrefix . $this->tableApplyList . ' where id = ' . $lId;
        $delDeatailRet = D()->query($sql);

        if($delRet===false || $delDeatailRet===false){
            return false;
        }
        return true;
    }


    /**
     * 当否决不通过的时候复原库存数据
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
        $queryRet = D()->query($sql); //获取数据

        foreach($queryRet as $key=>$val){ //更新库存值

            $data = array();
            if($val['INBOUND_STATUS'] != 2){ //如果状态已经不等于2的时候，需要插入一条进入

                //插入新的数据
                $data = $val;
                $data['UPDATE_USERID'] = null; //行为人
                $data['UPDATE_TIME'] = null; //行为时间
                $data['PARENTID'] = $val['ID'];
                $data['NUM'] = $val['AMOUNT']; //数量
                $data['INBOUND_STATUS'] = 2;
                //时间转换
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

                //替换成新的库存ID ---- 以便后面的售卖变更操作
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
     * 当备案的时候，业余做相关的操作
     * @param $applyId 工作流list_ID
     * @param $flowType 工作流类型
     * @return bool
     */
    public function updateBusinessOperate($applyId,$flowType){

        $return = false;

        if($flowType == 4) { //项目领用从领用池中捞数据
            $sql = "select B.*,A.USE_PRICE,A.USE_NUM AS PURCHASE_USE_NUM,A.ID AS USEID,to_char(B.INBOUND_TIME,'YYYY-MM-DD HH24:MI:SS') AS NEW_INBOUND_TIME from erp_warehouse_use_details A left join erp_displace_warehouse B ON A.wh_id = B.id where A.type = 2 and A.PL_ID = " . $applyId;
        }
        else  //其他走工作流审核获取ID
        {
            $sql = "SELECT  A.ID AS DETAILID,C.*,A.AMOUNT,to_char(c.INBOUND_TIME,'YYYY-MM-DD HH24:MI:SS') AS NEW_INBOUND_TIME,A.MONEY FROM ERP_DISPLACE_APPLYDETAIL A LEFT JOIN ERP_DISPLACE_APPLYLIST B ON A.LIST_ID = B.ID
 LEFT JOIN ERP_DISPLACE_WAREHOUSE C ON A.DID = C.ID WHERE B.ID = " . $applyId;
        }
        $queryRet = D()->query($sql); //获取数据
        if(!empty($queryRet)){
            foreach($queryRet as $key=>$val){
                //获取在库状态
                switch($flowType){
                    case 1:
                        $inboundStatus = 5; //已售卖
                        break;
                    case 2:
                        $inboundStatus = 4; //内部领用
                        break;
                    case 3:
                        $inboundStatus = 6; //报损
                        break;
                    case 4:
                        $inboundStatus = 3; //项目领用
                        break;
                    default:
                        break;
                }

                $data = array(); //初始化
                if($flowType==4){  //如果是项目领用

                    if($val['NUM'] > 0 || ($val['USE_NUM'] - $val['PURCHASE_USE_NUM']) > 0) { //如果没有处理完全
                        //step 1
                        //插入新的数据
                        $data = $queryRet[$key];
                        $data['UPDATE_USERID'] = $_SESSION['uinfo']['uid']; //行为人
                        $data['UPDATE_TIME'] = date('Y-m-d H:i:s'); //行为时间
                        $data['PARENTID'] = $val['ID'];
                        $data['NUM'] = $val['PURCHASE_USE_NUM']; //数量
                        $data['INBOUND_STATUS'] = $inboundStatus;
                        //时间转换
                        $data['LIVETIME'] = oracle_date_format($data['LIVETIME'], 'Y-m-d H:i:s');
                        $data['ALARMTIME'] = oracle_date_format($data['ALARMTIME'], 'Y-m-d H:i:s');
                        $data['ADD_TIME'] = oracle_date_format($data['ADD_TIME'], 'Y-m-d H:i:s');
                        $data['INBOUND_TIME'] = $data['NEW_INBOUND_TIME'];
                        $data['USE_NUM'] = 0; //USE_NUM 置为0
                        unset($data['ID']);

                        $resLess = M("Erp_displace_warehouse")
                            ->add($data);

                        if ($resLess === false) {
                            return $return;
                        }

                        //step 2
                        //erp_warehouse_use_details 领用表替换成新的置换仓库ID ---- 以便后面的领用删除操作
                        $updateData = array(
                            'WH_ID' => $resLess,
                        );

                        $updateRet = M("erp_warehouse_use_details")->where('ID = ' . $val['USEID'])->save($updateData);
                        if ($updateRet === false) {
                            return $return;
                        }

                        //step 3
                        //更新原先的库存表
                        $sql = 'update erp_displace_warehouse set use_num = use_num - ' . $val['PURCHASE_USE_NUM'] . ' where id = ' . $val['ID'];
                        $updateRet = D()->query($sql);

                        if ($updateRet === false) {
                            return $return;
                        }
                    }else{ //库存和领用都为0时
                        $data['UPDATE_USERID'] = $_SESSION['uinfo']['uid']; //行为人
                        $data['UPDATE_TIME'] = date('Y-m-d H:i:s'); //行为时间
                        $data['NUM'] = $val['USE_NUM']; //数量
                        $data['INBOUND_STATUS'] = $inboundStatus;
                        $data['USE_NUM'] = 0; //使用数量

                        $res = M("Erp_displace_warehouse")
                            ->where("ID=" . $val['ID'])
                            ->save($data);

                        if ($res === false) {
                            return $return;
                        }
                    }

                }else { //如果是非项目领用

                    if ($val['NUM'] == 0 && $val['USE_NUM'] == 0) {  //如果是处理全部
                        $data['UPDATE_USERID'] = $_SESSION['uinfo']['uid']; //行为人
                        $data['UPDATE_TIME'] = date('Y-m-d H:i:s'); //行为时间
                        $data['NUM'] = $val['AMOUNT']; //数量
                        $data['INBOUND_STATUS'] = $inboundStatus;

                        $res = M("Erp_displace_warehouse")
                            ->where("ID=" . $val['ID'])
                            ->save($data);

                        if ($res === false) {
                            return $return;
                        }

                    } else  { //其他情况

                        //插入新的数据
                        $data = $queryRet[$key];
                        $data['UPDATE_USERID'] = $_SESSION['uinfo']['uid']; //行为人
                        $data['UPDATE_TIME'] = date('Y-m-d H:i:s'); //行为时间
                        $data['PARENTID'] = $val['ID'];
                        $data['NUM'] = $val['AMOUNT']; //数量
                        $data['INBOUND_STATUS'] = $inboundStatus;
                        //时间转换
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

                        //替换成新的库存ID ---- 以便后面的售卖变更操作
                        $updateData = array(
                            'DID' => $resLess,
                        );

                        $updateRet = M("Erp_displace_applydetail")->where('ID = ' . $val['DETAILID'])->save($updateData);
                        if ($updateRet === false) {
                            return $return;
                        }
                    }

                    //如果是内部领用，则需要入收益
                    if ($flowType == 2) {
                        //划拨对象
                        $income_info['CASE_ID'] = $val['CASE_ID'];
                        $income_info['ENTITY_ID'] = $val['DR_ID'];
                        $income_info['ORG_ENTITY_ID'] = $val['DR_ID'];
                        $income_info['PAY_ID'] = $val['ID'];
                        $income_info['ORG_PAY_ID'] = $val['ID'];
                        $income_info['INCOME_FROM'] = 26;//置换物品收益
                        $income_info['INCOME'] = floatval(self::INCOME_PERCENT * $val['AMOUNT'] * $val['PRICE']);
                        $income_info['INCOME_REMARK'] = '置换物品内部领用收益';
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
     * 回退置换池信息
     * @param $listId 报销单ID
     * @return bool
     */
    public function backDisplaceUse($listId){

        //获取采购单ID
        $purchaseIds = D('ReimbursementDetail')->get_detail_info_by_listid($listId,array('BUSINESS_ID'));

        //获取置换库存ID
        if(!empty($purchaseIds)){
            foreach ($purchaseIds as $purchase) {
                $useInfo = D('WarehouseUse')->getDisplaceUseByPurchaseId($purchase['BUSINESS_ID']);
                if(notEmptyArray($useInfo)){
                    foreach($useInfo as $useInfoItem) {
                        $whId = $useInfoItem['WH_ID'];
                        $USE_NUM = $useInfoItem['USE_NUM'];

                        //更新当前库存状态和库存数量
                        $updateArr = array(
                            'INBOUND_STATUS' => 2, //已入库
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
     * 插入项目收益 （项目采购领用）
     * @param $useInfo
     * @return bool
     */
    public function  insertProIncome($useInfo){
        $return = false;
        $displaceInfo = D('Displace')->getDisplaceDetailById($useInfo['WH_ID'],array('ID','CASE_ID','DR_ID'));

        if(notEmptyArray($displaceInfo)){
            //划拨对象
            $income_info['CASE_ID'] = $displaceInfo[0]['CASE_ID'];
            $income_info['ENTITY_ID'] = $displaceInfo[0]['DR_ID'];
            $income_info['ORG_ENTITY_ID'] = $displaceInfo[0]['DR_ID'];
            $income_info['PAY_ID'] = $displaceInfo[0]['ID'];
            $income_info['ORG_PAY_ID'] = $displaceInfo[0]['ID'];
            $income_info['INCOME_FROM'] = 27;//置换物品收益
            $income_info['INCOME'] = floatval(self::INCOME_PERCENT * $useInfo['USE_PRICE'] * $useInfo['USE_NUM']);
            $income_info['INCOME_REMARK'] = '置换物品项目领用收益';
            $income_info['ADD_UID'] = $_SESSION['uinfo']['uid'];
            $income_info['OCCUR_TIME'] = date("Y-m-d H:i:s", time());

            $income_model = D('ProjectIncome');
            $return = $income_model->add_income_info($income_info);
        }

        return $return;
    }

    /**
     * 根据ID更新信息
     *
     * @access	public
     * @param	string  $ids 要更新的记录
     * @param array $update_arr 要跟新的字段
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
     * 根据条件更新信息
     *
     * @access	public
     * @param	string  $cond_where 要更新的记录
     * @param array $update_arr 要跟新的字段的键值对
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
     * 更改入库信息
     */

}

/* End of file DisplaceModel.class.php */
/* Location: ./Lib/Model/DisplaceModel.class.php */