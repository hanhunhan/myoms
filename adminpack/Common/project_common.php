<?php
/**
 * Created by PhpStorm.
 * User: superkemi
 * Date: 2015/10/23
 * Time: 9:32
 */

/**
 * 获取项目的业务类型
 * -----------------------------------
 * @param $proid 项目ID，多项目逗号分隔
 *
 */
function getProjectClass($proids){

    //获取项目类型
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
 * 获取项目的创建人等信息
 * -----------------------------------
 * @param $proid 项目ID
 *
 */
function getProjectInfo($proid){
    $pro_info = M('erp_project')->field('cuser')->where("id = $proid")->find();
    return $pro_info;
}

/**
 * 判断是否是资金池项目
 *
 * @param $proid  项目ID
 *
 */
function  isFundPoolPro($proid){
    //如果非数据直接返回false
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
 * 获取业务类型名称
 *
 * @param $typeid  项目类型ID
 *
 */
  function getScaleTypeName($typeid){
      $return = '';
      switch($typeid){
          case 1:
                $return = '电商';
                break;
          case 2:
                $return = '分销';
                break;
          case 3:
                $return = '硬广';
                break;
          case 4:
                $return = '活动';
                break;
          case 8:
              $return = '非我方收筹';
              break;
          default:
                  $return = '电商';
                  break;
      }
      return $return;
  }

?>