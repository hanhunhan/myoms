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



?>