<?php
/**
 * Created by PhpStorm.
 * User: superkemi
 * Date: 2015/10/23
 * Time: 9:32
 */

/**
 * ��ȡ��Ŀ��ҵ������
 * -----------------------------------
 * @param $proid ��ĿID������Ŀ���ŷָ�
 *
 */
function getProjectClass($proids){

    //��ȡ��Ŀ����
    $scaletype = M('erp_case')->field('scaletype','project_id')->where("project_id in ($proids)")->select();

    $ret = array();
    if(!empty($scaletype)) {
        foreach ($scaletype as $key => $val) {
            $ret[$val['PROJECT_ID']][$key] = $val['SCALETYPE'];
        }
    }
    return $ret;
}

/**
 * ��ȡ��Ŀ�Ĵ����˵���Ϣ
 * -----------------------------------
 * @param $proid ��ĿID
 *
 */
function getProjectInfo($proid){
    $pro_info = M('erp_project')->field('cuser')->where("id = $proid")->find();
    return $pro_info;
}

/**
 * �ж��Ƿ����ʽ����Ŀ
 *
 * @param $proid  ��ĿID
 *
 */
function  isFundPoolPro($proid){
    //���������ֱ�ӷ���false
    if(!is_numeric($proid))
        return false;

    $fundPoolPro = M("erp_project")
        ->join("INNER JOIN erp_case on erp_project.id = erp_case.project_id")
        ->join("INNER JOIN erp_house on erp_project.id = erp_house.project_id")
        ->field('erp_project.id')
        ->where(' erp_project.id = ' . $proid  . '  and  erp_case.scaletype = 1 and erp_house.isfundpool != 1')
        ->select();

    if(!empty($fundPoolPro) && $fundPoolPro[0]['ID'])
    {
        return true;
    }
    return false;
}

/**
 * ��ȡҵ����������
 *
 * @param $typeid  ��Ŀ����ID
 *
 */
  function getScaleTypeName($typeid){
      $return = '';
      switch($typeid){
          case 1:
                $return = '����';
                break;
          case 2:
                $return = '����';
                break;
          case 3:
                $return = 'Ӳ��';
                break;
          case 4:
                $return = '�';
                break;
          case 8:
              $return = '���ҷ��ճ�';
              break;
          default:
                  $return = '����';
                  break;
      }
      return $return;
  }

?>