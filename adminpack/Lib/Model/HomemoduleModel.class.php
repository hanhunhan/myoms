<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-29
 * Time: 上午9:24
 */
class HomemoduleModel extends  Model{
    protected $tablePrefix  =   'tf_';
    protected $tableName ='home_module';
    protected $pk  = 'moduleId';

    //表单提交数据设置
    public function setFormAttributes($postData){
        foreach($postData as $key=>$val){
            if($val){
                $this->data[$key] = $val;
            }
        }
    }

    public function search($count){
        //分页
        import("ORG.Util.Page");
        $p = new Page($count,C('PAGESIZE'));
        $page = $p->show();
        return $page;
    }

    protected function _before_insert(&$data,$options) {
        $data['createTime'] = date("Y-m-d H:i:s");
        $data['updateTime'] = date("Y-m-d H:i:s");
    }

    protected function _before_update(&$data,$options) {
        $data['updateTime'] = date("Y-m-d H:i:s");
    }
}