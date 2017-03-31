<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * 售卖、报损、领用
 * Created by PhpStorm.
 * User: superkemi
 * Date: 2016/11/30
 * Time: 18:00
 */

class InboundUse extends FlowBase {

    /**
     * 需要进行额度检查的项目类型
     * @var array
     */

    public function __construct() {
        $this->workflow = new newWorkFlow();
        $this->model = new Model();

    }

    function nextstep($flowId) {
        return $this->workflow->nextstep($flowId);
    }

    function createHtml($flowId) {
        // TODO: Implement createHtml() method.
    }

    /**
     * 转交
     * @param $data
     * @return bool
     */
    function handleworkflow($data) {
        $this->model->startTrans();
        $result = $this->workflow->handleworkflow($data);
        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }
        return $result;
    }

    /**
     * 通过
     * @param $data
     * @return bool
     */
    function passWorkflow($data) {
        $this->model->startTrans();
        $result = $this->workflow->passWorkflow($data);
        if ($result !== false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    /**
     * 否决
     * @param $data
     * @return bool
     */
    function notWorkflow($data) {

        $flowDisplaceType = $data['flowTypePY'];

        $this->model->startTrans();

        $result = $this->workflow->notWorkflow($data);
        switch($flowDisplaceType){ //todo 暂时不用处理  变更可能用到
            case 'shoumai':
                break;
            case 'neibulingyong':
                break;
            case 'baosun':
                break;
            case 'shoumaibiangeng':
                break;
            default:
                break;
        }
        $updateRet = D('InboundUse')->updateInboundUseById($data['RECORDID']); //库存值复原

        //否决售卖领用报损的(状态置换成3：审核不通过)
        $dbResult = D('InboundUse')->submitInboundUseById($data['RECORDID'],3);

        if ($result !== false && $dbResult!==false && $updateRet!==false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    /**
     * 备案工作流
     * @param $data
     * @return array
     */
    function finishworkflow($data) {

        //返回结果集
        $response = array(
            'status' => false,
            'message' => '',
            'url' => U('Flow/flowList', 'status=1'),
        );

        $flowType = $data['flowDisplaceType']; //工作流类型

        if (is_array($data) && count($data)) {
            $auth = $this->workflow->flowPassRole($data['flowId']);

            if (!$auth) {
                $response['message'] = '未经过必经角色';
                return $response;
            }

            D()->startTrans();
            //完成工作流
            $finishResult = $this->workflow->finishworkflow($data);

            //其他业务操作
            $otherBusinessOperate = D('InboundUse')->updateBusinessOperate($data['RECORDID'],$flowType);

            //备案置换申请(状态置换成2)
            $dbResult = D('InboundUse')->submitInboundUseById($data['RECORDID'],2);

//            var_dump($finishResult);
//            var_dump($otherBusinessOperate);
//            var_dump($dbResult);

            // 工作流完成且成本表插入成功表明备案成功
            if ($finishResult !== false && $dbResult !== false && $otherBusinessOperate!==false) {
                D()->commit();
                $response['status'] = true;
                $response['message'] = '备案成功';
            } else {
                D()->rollback();
                $response['status'] = false;
                $response['message'] = '备案失败';
            }
        }

        return $response;
    }

    /**
     * 创建置换工作流
     * @param $data
     * @return bool
     */
    function createworkflow($data) {
        $response = array(
            'status' => false,
            'message' => '',
            'url' => U('Displace/warehouse'),
        );

        // 检查权限
        $flowDisplaceType = $data['flowDisplaceType'];
        $flowDisplaceTypePY = D("InboundUse")->get_flow_displace_type(); //获取类型数组

        //$auth = $this->workflow->start_authority($flowDisplaceTypePY[$flowDisplaceType]);
        $auth = true;

        if(!$auth) {
            $response['status'] = false;
            $response['message'] = '暂无权限';
        } else {
            $data['INFO'] = strip_tags($data['INFO']);
            $data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
            $data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
            $data['DEAL_USERID'] = intval($data['DEAL_USERID']);

            D()->startTrans();
            $dbResult = $this->workflow->createworkflow($data);

            if ($dbResult !== false) {
                //提交置换申请(状态置换成1)
                $dbResult = D('InboundUse')->submitInboundUseById($data['RECORDID'],1);
            }

            if ($dbResult !== false) {
                $response['status'] = true;
                $response['message'] = '亲，申请提交成功！';
                D()->commit();
            } else {
                $response['status'] = false;
                $response['message'] = '亲，申请提交失败';
                D()->rollback();
            }
            $response['url'] = U('Touch/InboundUse/process', 'RECORDID=' . $data['RECORDID']);
        }
        return $response;
    }

    public function updateDsiplaceData($data)
    {
        $InboundUseSaleSql = "select
                a.* ,d.AMOUNT,d.MONEY
                from erp_displace_applydetail d left join
                erp_displace_warehouse a on d.did = a.id
                where d.list_id = ".$data['RECORDID'];
        $InboundUseSale = D()->query($InboundUseSaleSql);
        foreach($InboundUseSale as $saleDisplace){
            if($data['flowDisplaceType'] == 1){
                //售卖数量等于置换数量
                if($saleDisplace['AMOUNT'] == $saleDisplace['NUM']){
                    $insertData = $saleDisplace;
                    $insertData['PRICE'] = $saleDisplace['MONEY'];
                    $insertData['INBOUND_STATUS'] = 5;
                    $insertData['PARENTID'] = $saleDisplace['ID'];
                    unset ($insertData['ID']);
                    unset ($insertData['AMOUNT']);
                    unset ($insertData['MONEY']);
                    $deleteSql = "delete from Erp_displace_warehouse where ID=".$saleDisplace['ID'];
                    $addResult = M("Erp_displace_warehouse")->add($insertData);
                    $SaleResult = D()->execute($deleteSql);
                }else{
                    //添加新售卖物品
                    $insertData = $saleDisplace;
                    $insertData['PRICE'] = $saleDisplace['MONEY'];
                    $insetData['NUM'] =$saleDisplace['AMOUNT'];
                    $insertData['INBOUND_STATUS'] = 5;
                    unset ($insertData['ID']);
                    unset ($insertData['AMOUNT']);
                    unset ($insertData['MONEY']);
                    $addResult = M("Erp_displace_warehouse")->add($insertData);
                    //更改入库物品的数量
                    $numNew =  $saleDisplace['NUM'] - $saleDisplace['AMOUNT'];
                    $updateSql = "update Erp_displace_warehouse set NUM =".$numNew ." where ID=".$saleDisplace['ID'];
                    $upResult = D()->execute($updateSql);
                }
            }else if($saleDisplace['flowDisplaceType '] == 2){
                //领用数量等于置换数量
                if($saleDisplace['AMOUNT'] == $saleDisplace['NUM']){
                    $insertData = $saleDisplace;
                    $insertData['INBOUND_STATUS'] = 4;
                    $insertData['PARENTID'] = $saleDisplace['ID'];
                    unset ($insertData['AMOUNT']);
                    unset ($insertData['MONEY']);
                    $deleteSql = "delete from Erp_displace_warehouse where ID=".$saleDisplace['ID'];
                    $addResult = M("Erp_displace_warehouse")->add();
                    $SaleResult = D()->execute($deleteSql);
                }else{
                    //添加新内部领用物品
                    $insertData = $saleDisplace;
                    $insetData['NUM'] =$saleDisplace['AMOUNT'];
                    $insertData['INBOUND_STATUS'] = 4;
                    unset ($insertData['AMOUNT']);
                    unset ($insertData['MONEY']);
                    $addResult = M("Erp_displace_warehouse")->add($insertData);
                    //更改入库物品的数量
                    $numNew =  $saleDisplace['NUM'] - $saleDisplace['AMOUNT'];
                    $updateSql = "update Erp_displace_warehouse set NUM =".$numNew . " where ID=".$saleDisplace['ID'];
                    $upResult = D()->execute($updateSql);
                }
            }else if($data['flowDisplaceType'] == 3){
                $updateSql = "update Erp_displace_warehouse set NUM =0 , INBOUND_STATUS = 6 where ID=".$saleDisplace['ID'];
                $upResult = D()->execute($updateSql);
            }
        }
        return true;
    }
}