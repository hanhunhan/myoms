<?php
/**
 * Created by PhpStorm.
 * User: wangmingcha
 * Date: 15-1-28
 * Time: ����2:16
 */

class HomeLayoutAction extends ExtendAction{
    static $curdType = array(
        'add'=>'����',
        'edit'=>'�޸�',
        'del'=>'ɾ��'
    );

    static $statusType = array(
        '0'=>'����',
        '1'=>'ͣ��'
    );
    const  AJAX_SUCCESS = 1;
    const  AJAX_ERROR = 0 ;
    public $city;
    function _initialize(){
        layout('Layout/mains');
       // $this->city = 'nj'; //TODO Ŀǰд����������Ҫ�ĳɶ�ȡ�־û��洢��ʽ
        $this->city = $_COOKIE['loan_city_en'];
        $this->assign('statusType',self::$statusType);
    }

    protected function jumpUrl($url,$msg,$status=true){
        if($status){
            $this->success($msg, U($url));
        }else{
            $this->error($msg,U($url));
        }
        die();
    }

    //ģ���б�ҳ
    public function moduleList(){
        $model = D('Homemodule');
        $where = "1=1 ";
        if(isset($_POST['del']) && $_POST['del']!=''){
            $where.=" and del={$_POST['del']}";
        }

        if($_POST['moduleName']){
            $where.=" and moduleName like '%{$_POST['moduleName']}%'";
        }
        $count =  $model->where($where)->count();
        $listData = $model->where($where)->order('moduleId asc')->select();
        $this->assign('page',$model->search($count));
        $this->assign('listData',$listData);
        $this->display();
    }

    //ģ��CURD
    public function moduleCurd(){
        $subTag = $_POST['sub']; //���ύ��ʶ
        $curdType = (isset($_GET['curdType']))?$_GET['curdType']:$_POST['curdType']; //curd����
        $moduleId = (isset($_GET['moduleId']))?$_GET['moduleId']:$_POST['moduleId']; //����ID
        $resData = array();
        if(array_key_exists($curdType,self::$curdType)){
            $this->assign('curdType',self::$curdType);
            $model = new HomemoduleModel();
            $extClassifyMod = new HomeextclassifyModel();
            $extList = $extClassifyMod->where("moduleId=$moduleId")->select();
            $this->assign('extList',$extList);
            try{
                switch($curdType){
                    case 'add':
                        //�������ύ����insert����
                        if($subTag){
                            if($_POST['Homemoduel']){
                                $model->setFormAttributes($_POST['Homemoduel']);
                                $moduleId = $model->add();
                                $extClassifyMod->postHandle($moduleId);
                                $this->jumpUrl('HomeLayout/moduleList','�����ɹ�');
                            }
                        }
                        break;

                    case 'edit':
                        $resData = $model->find($moduleId);
                        //�������ύ����update����
                        if($resData){
                            if($subTag){
                                if($_POST['Homemoduel']){
                                    $model->setFormAttributes($_POST['Homemoduel']);
                                    $model->save();
                                    $extClassifyMod->postHandle($moduleId);
                                    $this->jumpUrl('HomeLayout/moduleList','�޸ĳɹ�');
                                }
                            }
                        }else{
                            $this->jumpUrl('HomeLayout/moduleList','�ü�¼������',false);
                        }
                        break;

                    case 'del':
                        $resData  = $model->find($moduleId);
                        if($resData){
                            $model->del = (!$model->del)?1:0; //״̬ȡ��
                            $model->save();
                            $this->jumpUrl('HomeLayout/moduleList','״̬�Ѹ���');
                        }else{
                            $this->jumpUrl('HomeLayout/moduleList','�ü�¼������',false);
                        }
                        break;
                }
            }catch (Exception $e){
                $this->error('����ʧ��',U("System/desktop"));
            }
        }else{
            $this->error('�Ƿ�����',U("System/desktop"));
        }

        if(!$subTag){
            $this->assign('typeName',self::$curdType[$curdType]);
            $this->assign('resData',$resData);
            $this->display();
        }
    }

    //ģ���������� CURDҳ
    public function moduleAttrCurd(){
        $moduleId = (isset($_GET['moduleId']))?$_GET['moduleId']:$_POST['moduleId'];
        $model = new HomemoduleattrModel();

        //ȡdb���ݲ���װ
        $listData = $model->getFormData($moduleId);

        //ajax���ύ
        if($this->isAjax()){
            $ajaxRes = $model->ajax_form_save();
            if($ajaxRes){
                echo self::AJAX_SUCCESS;
            }else{
                echo self::AJAX_ERROR;
            }
            die();
        }

        $moduleMod = new HomemoduleModel();
        $moduleRes = $moduleMod->find($moduleId);
        $this->assign('moduleRes',$moduleRes);
        $this->assign('listData',$listData);
        $this->assign('moduleId',$moduleId);
        $this->display();
    }

    //ģ��ڵ��б�ҳ
    public function moduleItemList(){
        $moduleId =  (isset($_GET['moduleId']))?$_GET['moduleId']:$_POST['moduleId'];
        $extId = (isset($_GET['extId']))?$_GET['extId']:$_POST['extId'];
        $model = D('Homemoduleitem');
        $where = "city='{$this->city}' and a.moduleId=$moduleId ";
        if($extId)
            $where.=" and a.extId=$extId";
        if(isset($_POST['del']) && $_POST['del']!=''){
            $where.=" and a.del={$_POST['del']}";
        }
        $sql = "select a.*,b.moduleName,c.extName  from tf_home_module_item as a left join tf_home_module as b on a.moduleId=b.moduleId left  join tf_home_ext_classify as c on a.extId=c.extId where {$where} order by rank asc";
        $listData = $model->query($sql);
        $count = count($listData);
        $this->assign('page',$model->search($count));
        $this->assign('listData',$listData);
        //ģ����չ����
        $widgetData= D('Homeextclassify')->getDropDownList($moduleId,$extId);

        //ģ������
        $moduleMod = D('Homemodule');
        $moduleRes = $moduleMod->find($moduleId);

        //����$moduleId��ȡ�ڵ�����
        $moduleAttrObj = new HomemoduleattrModel();
        $attrList = $moduleAttrObj->getAttrList($moduleId);

        $this->assign('attrList',$attrList);
        $this->assign('moduleRes',$moduleRes);
        $this->assign('widgetData',$widgetData);
        $this->display();
    }

    //ajax��ʽ�ı�״̬
    public function moduleItemDel(){
        if($this->isAjax()){
            $itemId = $_POST['itemId'];
            $model = D('Homemoduleitem');
            $resData = $model->find($itemId);
            if($resData){
                $ajaxDel = $model->del = (!$model->del)?1:0; //״̬ȡ��
                $res = $model->save();
                $ajaxRet = array();
                if($res){
                    $ajaxRet['status'] = self::AJAX_SUCCESS;
                    $ajaxRet['del'] = $ajaxDel;
                }else{
                    $ajaxRet['status'] = self::AJAX_ERROR;
                    $ajaxRet['del'] = $ajaxDel;
                }
                echo json_encode($ajaxRet);
            }
            die();
        }
    }

    //ģ��ڵ�CURD
    public function moduleItemCurd(){
        $itemId = (isset($_GET['itemId']))?$_GET['itemId']:$_POST['itemId'];
        $moduleId =  (isset($_GET['moduleId']))?$_GET['moduleId']:$_POST['moduleId'];
        $moduleAttrObj = new HomemoduleattrModel();
        $moduleItemMod = new HomemoduleitemModel();

        //ajax���ύ
        if($this->isAjax()){
            $ajaxRes = $moduleItemMod->ajax_form_save($itemId,$moduleId);
            if($ajaxRes){
                echo self::AJAX_SUCCESS;
            }else{
                echo self::AJAX_ERROR;
            }
            die();
        }
        $itemList = array();

        //��$itemId����ʱ��ʾΪupdate״̬����Ҫ��ȡ��Ӧ�Ľڵ�����
        if($itemId){
            $itemData = $moduleItemMod->getItemRenderData($itemId);
            $moduleId = $itemData['moduleId'];
            $itemList = $itemData['itemList'];
        }
        //����$moduleId��ȡ�ڵ�����
        $attrList = $moduleAttrObj->getAttrList($moduleId);

        //ģ����չ����
        $widgetData= D('Homeextclassify')->getDropDownList($moduleId,$itemData['extId']);
        $this->assign('widgetData',$widgetData);
        $this->assign('itemId',$itemId);
        $this->assign('moduleId',$moduleId);
        $this->assign('attrList',$attrList);
        $this->assign('itemList',$itemList);
        $this->assign('itemData',$itemData);
        $this->display();
    }


    public function ajaxUploadFile(){
        if($_FILES['Filedata']['error']==0){
            Import("ORG.Util.UploadFile");
            $city = $this->city;
            $uf = new UploadFile('Filedata');
            $uf->setMaxSize(2048);
            $uf->setResizeImage(true);
            $uf->setUploadType("ftp");
            $uf->setSaveDir("/$city/");
            $uf->setShowAsChinese(true);
            $retMsg = $uf->upload();
            if($retMsg=='success'){
                $saveFileUrl =  $uf->getSaveFileURL();
                $retArr = array('path'=>$saveFileUrl);
                echo json_encode($retArr);
                die();
            }
        }
    }
}