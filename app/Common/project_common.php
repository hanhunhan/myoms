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



?>