<?php
if (is_file(dirname(__FILE__).'/FlowBase.php')){
    include dirname(__FILE__).'/FlowBase.php';
}else {
    die('Sorry. Not load FlowBase file.');
}
/**
 * ��������������
 * Created by PhpStorm.
 * User: superkemi
 * Date: 2016/11/30
 * Time: 18:00
 */

class InboundUse extends FlowBase {

    /**
     * ��Ҫ���ж�ȼ�����Ŀ����
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
     * ת��
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
     * ͨ��
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
     * ���
     * @param $data
     * @return bool
     */
    function notWorkflow($data) {

        $flowDisplaceType = $data['flowTypePY'];

        $this->model->startTrans();

        $result = $this->workflow->notWorkflow($data);
        switch($flowDisplaceType){ //todo ��ʱ���ô���  ��������õ�
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
        $updateRet = D('InboundUse')->updateInboundUseById($data['RECORDID']); //���ֵ��ԭ

        //����������ñ����(״̬�û���3����˲�ͨ��)
        $dbResult = D('InboundUse')->submitInboundUseById($data['RECORDID'],3);

        if ($result !== false && $dbResult!==false && $updateRet!==false) {
            $this->model->commit();
        } else {
            $this->model->rollback();
        }

        return $result;
    }

    /**
     * ����������
     * @param $data
     * @return array
     */
    function finishworkflow($data) {

        //���ؽ����
        $response = array(
            'status' => false,
            'message' => '',
            'url' => U('Flow/flowList', 'status=1'),
        );

        $flowType = $data['flowDisplaceType']; //����������

        if (is_array($data) && count($data)) {
            $auth = $this->workflow->flowPassRole($data['flowId']);

            if (!$auth) {
                $response['message'] = 'δ�����ؾ���ɫ';
                return $response;
            }

            D()->startTrans();
            //��ɹ�����
            $finishResult = $this->workflow->finishworkflow($data);

            //����ҵ�����
            $otherBusinessOperate = D('InboundUse')->updateBusinessOperate($data['RECORDID'],$flowType);

            //�����û�����(״̬�û���2)
            $dbResult = D('InboundUse')->submitInboundUseById($data['RECORDID'],2);

//            var_dump($finishResult);
//            var_dump($otherBusinessOperate);
//            var_dump($dbResult);

            // ����������ҳɱ������ɹ����������ɹ�
            if ($finishResult !== false && $dbResult !== false && $otherBusinessOperate!==false) {
                D()->commit();
                $response['status'] = true;
                $response['message'] = '�����ɹ�';
            } else {
                D()->rollback();
                $response['status'] = false;
                $response['message'] = '����ʧ��';
            }
        }

        return $response;
    }

    /**
     * �����û�������
     * @param $data
     * @return bool
     */
    function createworkflow($data) {
        $response = array(
            'status' => false,
            'message' => '',
            'url' => U('Displace/warehouse'),
        );

        // ���Ȩ��
        $flowDisplaceType = $data['flowDisplaceType'];
        $flowDisplaceTypePY = D("InboundUse")->get_flow_displace_type(); //��ȡ��������

        //$auth = $this->workflow->start_authority($flowDisplaceTypePY[$flowDisplaceType]);
        $auth = true;

        if(!$auth) {
            $response['status'] = false;
            $response['message'] = '����Ȩ��';
        } else {
            $data['INFO'] = strip_tags($data['INFO']);
            $data['DEAL_INFO'] = strip_tags($data['DEAL_INFO']);
            $data['DEAL_USER'] = strip_tags($data['DEAL_USER']);
            $data['DEAL_USERID'] = intval($data['DEAL_USERID']);

            D()->startTrans();
            $dbResult = $this->workflow->createworkflow($data);

            if ($dbResult !== false) {
                //�ύ�û�����(״̬�û���1)
                $dbResult = D('InboundUse')->submitInboundUseById($data['RECORDID'],1);
            }

            if ($dbResult !== false) {
                $response['status'] = true;
                $response['message'] = '�ף������ύ�ɹ���';
                D()->commit();
            } else {
                $response['status'] = false;
                $response['message'] = '�ף������ύʧ��';
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
                //�������������û�����
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
                    //�����������Ʒ
                    $insertData = $saleDisplace;
                    $insertData['PRICE'] = $saleDisplace['MONEY'];
                    $insetData['NUM'] =$saleDisplace['AMOUNT'];
                    $insertData['INBOUND_STATUS'] = 5;
                    unset ($insertData['ID']);
                    unset ($insertData['AMOUNT']);
                    unset ($insertData['MONEY']);
                    $addResult = M("Erp_displace_warehouse")->add($insertData);
                    //���������Ʒ������
                    $numNew =  $saleDisplace['NUM'] - $saleDisplace['AMOUNT'];
                    $updateSql = "update Erp_displace_warehouse set NUM =".$numNew ." where ID=".$saleDisplace['ID'];
                    $upResult = D()->execute($updateSql);
                }
            }else if($saleDisplace['flowDisplaceType '] == 2){
                //�������������û�����
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
                    //������ڲ�������Ʒ
                    $insertData = $saleDisplace;
                    $insetData['NUM'] =$saleDisplace['AMOUNT'];
                    $insertData['INBOUND_STATUS'] = 4;
                    unset ($insertData['AMOUNT']);
                    unset ($insertData['MONEY']);
                    $addResult = M("Erp_displace_warehouse")->add($insertData);
                    //���������Ʒ������
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