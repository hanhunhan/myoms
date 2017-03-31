<?php
//模块属性节点widget
class HomeLayoutWidget extends  Widget{
    public function __construct(){
        C('LAYOUT_ON',false);
    }

    public function render($data){
        if($data['widgetName']){
            $data['extSelectArr'] = HomemoduleattrModel::$extSelectArr;
            $content = $this->renderFile($data['widgetName'],$data);
            return $content;
        }
    }
}