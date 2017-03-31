<?php
/**
 * �Է���ҳģ�����Ա�
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-29
 * Time: ����9:24
 */
class HomemoduleattrModel extends  Model{
    protected $tablePrefix  =   'tf_';
    protected $tableName ='home_module_attr';
    protected $pk  = 'aId';

    static $extSelectArr = array(
        '1'=>'����',
        '2'=>'ͼƬ'
    );
    protected function _before_insert(&$data,$options) {
        $data['createTime'] = date("Y-m-d H:i:s");
        $data['updateTime'] = date("Y-m-d H:i:s");
    }

    protected function _before_update(&$data,$options) {
        $data['updateTime'] = date("Y-m-d H:i:s");
    }

    //���ύ��������
    public function setFormAttributes($postData){
        foreach($postData as $key=>$val){
            if($val){
                $this->data[$key] = $this->utfToGbk($val);
            }
        }
    }


    public  function ajax_form_save(){
        $moduleId = $_POST['moduleId'];
        $model = $this;
        try{
            $del_ids = explode(',',$_POST['del_ids']);
            //������������update ��insert����
            if($_POST['formData']){
                foreach($_POST['formData'] as $postData){
                    $postData['extSelect'] = implode(',',$postData['extSelect']);
                    if($postData['aId']){
                        //���¼�¼
                        $this->update_record($postData);
                    }else{
                        //������¼
                        $postData['moduleId'] = $moduleId;
                        $this->setFormAttributes($postData);
                        $model->add();
                    }
                }
            }

            //���$del_ids����,��ʾ������Ҫɾ������del״̬λ��Ϊ1
            if($del_ids){
                foreach($del_ids as $id){
                    $postData['aId'] = $id;
                    $postData['del'] = 1;
                    $this->delete($id);
                }
            }
        }catch (Exception $e){
            return false;
        }
        return true;
    }

    //�������ݱ��Ӧpost����
    protected function update_record($postData){
        $resData = $this->find($postData['aId']);
        if($resData && $this->is_need_update($resData,$postData)){
            $this->save($resData);
            $this->data = array();
        }
    }

    //����moduleIdȥdb��ȡ��¼
    public function getFormData($moduleId){
        $sql = "select * from tf_home_module_attr where moduleId=$moduleId  and del=0 order by aId asc";
        $resData = $this->query($sql);
        if($resData){
            foreach($resData as $key=>$data){
                $resData[$key]['extSelect'] = explode(',',$data['extSelect']);
            }
        }
        return $resData;
    }

    //���ݻ�ȡ����db�����ж��Ƿ�Ҫ���±�����¼
    protected function is_need_update(&$dbData,$formData){
        $need_update = false;
        foreach($formData as $key=>$data){
            if(isset($dbData[$key]) && $dbData[$key]!=$data){
                $dbData[$key] = $this->utfToGbk($data); //����ֵ
                $need_update = true;
            }
        }
        return $need_update;
    }

    protected function utfToGbk($str){
        if(!is_numeric($str) && mb_detect_encoding($str)=='UTF-8'){
            $str =  iconv('UTF-8','GBK//IGNORE',$str);
        }
        return $str;
    }


    public function getAttrList($moduleId){
        $where  = "del=0 and moduleId=$moduleId";
        $resData = $this->where($where)->select();
        if($resData){
            foreach($resData as $key=>$val){
                $resData[$key]['extSelect'] = explode(',',$val['extSelect']);
            }
        }
        return $resData;
    }
}