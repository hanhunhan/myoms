<?php

/**
 * Created by PhpStorm.
 * User: superkemi
 * Date: 2016/12/6
 * Time: 8:45
 */
class RollbackAction  extends ExtendAction
{
    /*合并当前模块的URL参数*/
    private $_merge_url_param = array();

    private $city = 0;
    private $uid = 0;
    private $deptId = 0;

    //构造函数
    public function __construct() {
        // 权限映射表
        $this->authorityMap = array(
        );

        parent::__construct();

        $this->uid = intval($_SESSION['uinfo']['uid']); //用户ID
        $this->city = intval($_SESSION['uinfo']['city']); //城市ID
        $this->deptId = intval($_SESSION['uinfo']['deptid']); //部门ID
    }


    /**
     * 项目变更
     */
    public function proManChange(){

        //接收参数
        $act = isset($_GET['act'])?trim($_GET['act']):'';

        if($act=='actManChange'){
            //返回参数
            $response = array(
                'status'=>false,
                'msg'=>'',
                'data'=>null,
            );

            //接收参数
            $uId = isset($_GET['uid'])?intval($_GET['uid']):0;
            $proId = isset($_GET['proId'])?intval($_GET['proId']):0;

            if($uId == -1 || $proId == -1){
                $response['msg'] = '请选择项目和用户！';
                die(json_encode(g2u($response)));
            }

            D()->startTrans();
            //业务操作
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

            //权限添加
            if($flag===false || $modelRet===false){
                D()->rollback();
                $response['msg'] = '亲，操作失败，请重试!';
            }else{
                D()->commit();
                $response['msg'] = '亲，操作成功!';
            }

            die(@json_encode(g2u($response)));
        }

        //获取用户信息
        $sql = 'SELECT A.ID, A.NAME,B.DEPTNAME FROM ERP_USERS A LEFT JOIN ERP_DEPT B ON A.DEPTID = B.ID WHERE A.CITY = ' . $this->city . ' AND A.ISVALID = -1';
        $allUsers = D()->query($sql);

        //获取项目信息
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