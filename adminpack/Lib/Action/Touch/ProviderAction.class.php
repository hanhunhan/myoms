<?php
/**
 * �û����������
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/7
 * Time: 13:44
 */

class ProviderAction extends ExtendAction {
    /**
     * ��ȡ�û��б�
     */
    public function getUsers() {
        if (empty($_SESSION['uinfo'])) {
            return false;
        }

        $flowId = $_REQUEST['flowId'];
        $type = $_REQUEST['type'];
        $model = $_REQUEST['model'];
        $response = array(
            'status'=>false,
            'msg'=>'',
            'data'=>null,
        );
        $data = array();
        $groupCondition = '';
        if (intval($flowId) > 0) {  // ��flowId˵�����Ǵ���������
            $flowInfo = $this->getFixedFreeRole($flowId,$type);
            if ($flowInfo['type'] == 1 && !empty($flowInfo['data'])) {
                $groupCondition = sprintf(" and a.ROLEID = %s", $flowInfo['data']);
            }
        } else {  // ����������
            // ����������
            $flowType = $this->tranFlowTypeName($_REQUEST['flowType']);
            $groupId = $this->getFixedFlowGroupByPY($flowType);
            if (intval($groupId) > 0) {
                $groupCondition = sprintf(" and a.ROLEID = %s", $groupId);
            }
        }
        if($model != 'group') {
            $exceptCondition = " A.id not in ({$_SESSION['uinfo']['uid']}) ";
            $todo = "and A.username not in('admin')";

            $sql = <<<USER_SQL
                SELECT A.id,
                       A.username,
                       UPPER(substr(A.username,0,1)) as name_index,
                       A.name,
                       A.citys,
                       A.city,
                       A.DEPTID,
                       B.loan_groupval,
                       B.loan_groupname,
                       C.Deptname,
                       D.NAME CITY_NAME
                FROM erp_users A
                inner JOIN erp_group B ON A.roleid = B.loan_groupid
                left join erp_dept C on A.DEPTID = C.id
                LEFT JOIN erp_city D on A.CITY = D.ID
                where A.Isvalid = -1  {$groupCondition} and {$exceptCondition}
                ORDER BY name_index asc
USER_SQL;

            try {
                $result = D()->query($sql);
                if (is_array($result) && count($result)) {
                    foreach ($result as $user) {
                        $data[g2u($user['NAME_INDEX'])] [] = g2u($user);
                    }
                    $response['status'] = true;
                    $response['msg'] = g2u('��ȡ�û��б�ɹ�');
                    $response['data'] = $data;
                }
            } catch (Exception $e) {
                $response['status'] = false;
                $response['msg'] = $e->getMessage();
            }

            echo json_encode($response);
        }else{
            $SQL = "SELECT * FROM ERP_GROUP_FLOW WHERE USERID=".$_SESSION['uinfo']['uid'];
            $result = D()->query($SQL);
            if (is_array($result) && count($result)){
                foreach($result as $group){
                    $list['GROUP'][] = g2u($group);
                }
                $response['status'] = true;
                $response['msg'] = g2u("��ȡ����������ɹ�");
                $response['data'] = $list;
                $response['type'] = 'group';
            }else{
                $response['status'] = false;
                $response['msg'] = g2u("��ȡ����������ʧ��");
            }
            echo json_encode($response);
        }
    }



    protected function getFixedFlowGroupByPY($py) {
        $response = null;
        $sql = <<<FIXED_FLOW_SQL
            SELECT FF.*
            FROM ERP_FLOWTYPE F
            LEFT JOIN ERP_FLOWSET S ON S.FLOWTYPE = F.ID
            LEFT JOIN ERP_FIXEDFLOW FF ON FF.FLOWSETID = S.ID
            AND FF.CITY = %d
            WHERE F.PINYIN = '%s'
FIXED_FLOW_SQL;

        $dbResult = D()->query(sprintf($sql, $_SESSION['uinfo']['city'], $py));
        if (notEmptyArray($dbResult)) {
            $flowNodes = explode(',', $dbResult[0]['FLOWCURRENT']);
            if (is_array($flowNodes) && count($flowNodes) > 1) {
                $response = $flowNodes[1];
            }
        }

        return $response;
    }

    /**
     * ��ȡ�����ǹ̶�������������
     * @param string $flowId
     * @return array
     */
    protected function getFixedFreeRole($flowId = '',$typeCopy) {
        $response = array(
            'type' => 2,  // 1=�̶��� 2=������
            'data' => ''
        );
        if($typeCopy == "multi"){
            return $response;
        }
        if (intval($flowId)) {  // ����������
            $sql = <<<FLOW_SQL
                SELECT F.MAXSTEP,
                       F.FLOWSETID,
                       F.CITY,
                       W.ID W_ID,
                       W.FLOWCURRENT
                FROM ERP_FLOWS F
                LEFT JOIN ERP_FIXEDFLOW W ON W.CITY = F.CITY
                AND W.FLOWSETID = F.FLOWSETID
                WHERE F.ID = %d
FLOW_SQL;
            $flowInfo = D()->query(sprintf($sql, $flowId));
            if (notEmptyArray($flowInfo)) {
                if (intval($flowInfo[0]['W_ID']) > 0) {
                    $response['type'] = 1;

                    $flowCurrentList = explode(',', $flowInfo[0]['FLOWCURRENT']);
                    if (count($flowCurrentList) >= (intval($flowInfo[0]['MAXSTEP']) + 1)) {
                        $response['data'] = $flowCurrentList[intval($flowInfo[0]['MAXSTEP'])];
                    }
                }
            }
        }

        return $response;
    }

    /**
     * ͳһ�ڹ�����ʹ�õĲ�ͬ����
     * @param $srcName
     * @return string
     */
    private function tranFlowTypeName($srcName) {
        $result = '';
        if (!empty($srcName)) {
            switch($srcName) {
                case 'BenefitFlow':  // Ԥ������������
                    $result = 'yusuanqita';
                    break;
                default:
                    $result = $srcName;
            }
        }

        return $result;
    }
}
