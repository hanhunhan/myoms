<?php
class HomeextclassifyModel extends  Model{
    protected $tablePrefix  =   'tf_';
    protected $tableName ='home_ext_classify';
    protected $pk  = 'extId';

    public function postHandle($moduleId){
        $extPostArr = $_POST['Ext'];
        $del_ids = $_POST['del_ext_ids'];
        //删除记录
        if($del_ids){
            $this->delete($del_ids);
        }
        if($extPostArr){
            $tmpArr = array();
            foreach($extPostArr['extId'] as $key=>$extId){
                $tmpArr['extId'] = $extId;
                $tmpArr['moduleId'] = $moduleId;
                $tmpArr['extName'] = $extPostArr['extName'][$key];
                $tmpArr['extLink'] = $extPostArr['extLink'][$key];
                $tmpArr['rank'] = $extPostArr['rank'][$key];
                if(!$extId){
                    $this->add($tmpArr);
                }else{
                    $this->save($tmpArr);
                }
            }
        }
    }

    public function getDropDownList($moduleId,$extId=null){
        //模块扩展下拉
        $extList = $this->where("moduleId=$moduleId")->select();
        $widgetData = array();
        $widgetData['extList'] = $extList;
        $widgetData['extId'] = $extId;
        $widgetData['widgetName'] = 'getExtDropDownList';
        return $widgetData;
    }

}