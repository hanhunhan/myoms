<?php
/**
 * 淘房首页模块节点表
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-2-3
 * Time: 下午5:42
 */
class HomemoduleitemModel extends Model{
    protected $tablePrefix  =   'tf_';
    protected $tableName ='home_module_item';
    protected $pk  = 'itemId';
    public  $dbRecord;

    public function search($count){
        //分页
        import("ORG.Util.Page");
        $p = new Page($count,C('PAGESIZE'));
        $page = $p->show();
        return $page;
    }

    protected function _before_insert(&$data,$options) {
        $data['city'] =$_COOKIE['loan_city_en'];
    }

    //根据itemId获取对应的节点数据
    public function getItemRenderData($itemId){
        $retData = array();
        $this->dbRecord = $retData = $this->find($itemId);
        if($this->dbRecord){
            $retData['itemList'] = unserialize($retData['jsonContent']);
        }else{
            throw new Exception('this item  record is not exist');
        }
        return $retData;
    }



    public function ajax_form_save($itemId,$moduleId){
        if($_POST['formData']){
            $jsonContent = array();
            foreach($_POST['formData'] as $data){
                $aId = $data['aId'];
                foreach($data as $k=>$d){
                    $jsonContent[$aId][$k] = $this->utfToGbk($d);
                }
            }
            $jsonContent = serialize($jsonContent);
            try{
                if($itemId){
                    //update数据
                    $itemRes = $this->find($itemId);
                    if(!$itemRes){
                        throw new Exception('this item  record is not exist');
                    }
                    $this->data['jsonContent'] = $jsonContent;
                    $this->setBasePostValue();
                    $this->save();
                }else{
                    //insert数据
                    $this->data['jsonContent'] = $jsonContent;
                    $this->setBasePostValue();

                    $this->add();
                }
            }catch (Exception $e){
                return false;
            }
            return true;
        }
    }

    //为$this->post设置基本属性值
    protected function setBasePostValue(){
        $baseAttr = array('moduleId','rank','city','createTime','updateTime','extId');
        foreach($baseAttr as $attr){
            if(array_key_exists($attr,$_POST)){
                $this->data[$attr] = $this->utfToGbk($_POST[$attr]);
            }
        }
    }

    protected function utfToGbk($str){
        if(!is_numeric($str) && mb_detect_encoding($str)=='UTF-8'){
            $str =  iconv('UTF-8','GBK//IGNORE',$str);
        }
        return $str;
    }
}