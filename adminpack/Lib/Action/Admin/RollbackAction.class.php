<?php

/**
 * Created by PhpStorm.
 * User: superkemi
 * Date: 2016/12/6
 * Time: 8:45
 */
class RollbackAction  extends ExtendAction
{
    /*�ϲ���ǰģ���URL����*/
    private $_merge_url_param = array();

    private $city = 0;
    private $uid = 0;
    private $deptId = 0;

    //���캯��
    public function __construct() {
        // Ȩ��ӳ���
        $this->authorityMap = array(
        );

        parent::__construct();

        $this->uid = intval($_SESSION['uinfo']['uid']); //�û�ID
        $this->city = intval($_SESSION['uinfo']['city']); //����ID
        $this->deptId = intval($_SESSION['uinfo']['deptid']); //����ID
    }


    /**
     * ��Ŀ���
     */
    public function proManChange(){

        //���ղ���
        $act = isset($_GET['act'])?trim($_GET['act']):'';

        if($act=='actManChange'){
            //���ز���
            $response = array(
                'status'=>false,
                'msg'=>'',
                'data'=>null,
            );

            //���ղ���
            $uId = isset($_GET['uid'])?intval($_GET['uid']):0;
            $proId = isset($_GET['proId'])?intval($_GET['proId']):0;

            if($uId == -1 || $proId == -1){
                $response['msg'] = '��ѡ����Ŀ���û���';
                die(json_encode(g2u($response)));
            }

            D()->startTrans();
            //ҵ�����
            $modelRet = D('ProjectCase')->updateProMan($proId,$uId);

            $sql = 'select scaletype from erp_case where project_Id = ' . $proId;
            $scaleType = D()->query($sql);

            if(!empty($scaleType)){
                foreach($scaleType as $key=>$val){
                    $sql = "insert into erp_prorole(USE_ID,PRO_ID,ERP_ID,ISVALID) VALUES({$uId},{$proId},{$val['SCALETYPE']},-1)";
                    $flag = D()->query($sql);
                    if($flag===false)
                        break;
                }
            }

            //Ȩ�����
            if($flag===false || $modelRet===false){
                D()->rollback();
                $response['msg'] = '�ף�����ʧ�ܣ�������!';
            }else{
                D()->commit();
                $response['msg'] = '�ף������ɹ�!';
            }

            die(@json_encode(g2u($response)));
        }

        //��ȡ�û���Ϣ
        $sql = 'SELECT A.ID, A.NAME,B.DEPTNAME FROM ERP_USERS A LEFT JOIN ERP_DEPT B ON A.DEPTID = B.ID WHERE A.CITY = ' . $this->city . ' AND A.ISVALID = -1';
        $allUsers = D()->query($sql);

        //��ȡ��Ŀ��Ϣ
        $allPro = M('Erp_project')
            ->field('ID, PROJECTNAME,CONTRACT')
            ->where("CITY_ID=" . $this->city . ' AND STATUS != 2 AND PROJECTNAME IS NOT NULL')
            ->order('PROJECTNAME desc')
            ->select();

        $this->assign('allUsers', $allUsers);
        $this->assign('allPro', $allPro);
        $this->display('proManChange');
    }

}