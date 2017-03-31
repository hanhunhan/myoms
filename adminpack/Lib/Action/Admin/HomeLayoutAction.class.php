<?php
/**
 * Created by PhpStorm.
 * User: wangmingcha
 * Date: 15-1-28
 * Time: 下午2:16
 */

class HomeLayoutAction extends ExtendAction{
    static $curdType = array(
        'add'=>'新增',
        'edit'=>'修改',
        'del'=>'删除'
    );

    static $statusType = array(
        '0'=>'启用',
        '1'=>'停用'
    );
    const  AJAX_SUCCESS = 1;
    const  AJAX_ERROR = 0 ;
    public $city;
    function _initialize(){
        layout('Layout/mains');
       // $this->city = 'nj'; //TODO 目前写死，后面需要改成读取持久化存储方式
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

    //模块列表页
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

    //模块CURD
    public function moduleCurd(){
        $subTag = $_POST['sub']; //表单提交标识
        $curdType = (isset($_GET['curdType']))?$_GET['curdType']:$_POST['curdType']; //curd类型
        $moduleId = (isset($_GET['moduleId']))?$_GET['moduleId']:$_POST['moduleId']; //主键ID
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
                        //表单数据提交，做insert操作
                        if($subTag){
                            if($_POST['Homemoduel']){
                                $model->setFormAttributes($_POST['Homemoduel']);
                                $moduleId = $model->add();
                                $extClassifyMod->postHandle($moduleId);
                                $this->jumpUrl('HomeLayout/moduleList','新增成功');
                            }
                        }
                        break;

                    case 'edit':
                        $resData = $model->find($moduleId);
                        //表单数据提交，做update操作
                        if($resData){
                            if($subTag){
                                if($_POST['Homemoduel']){
                                    $model->setFormAttributes($_POST['Homemoduel']);
                                    $model->save();
                                    $extClassifyMod->postHandle($moduleId);
                                    $this->jumpUrl('HomeLayout/moduleList','修改成功');
                                }
                            }
                        }else{
                            $this->jumpUrl('HomeLayout/moduleList','该记录不存在',false);
                        }
                        break;

                    case 'del':
                        $resData  = $model->find($moduleId);
                        if($resData){
                            $model->del = (!$model->del)?1:0; //状态取反
                            $model->save();
                            $this->jumpUrl('HomeLayout/moduleList','状态已更新');
                        }else{
                            $this->jumpUrl('HomeLayout/moduleList','该记录不存在',false);
                        }
                        break;
                }
            }catch (Exception $e){
                $this->error('操作失败',U("System/desktop"));
            }
        }else{
            $this->error('非法操作',U("System/desktop"));
        }

        if(!$subTag){
            $this->assign('typeName',self::$curdType[$curdType]);
            $this->assign('resData',$resData);
            $this->display();
        }
    }

    //模块属性设置 CURD页
    public function moduleAttrCurd(){
        $moduleId = (isset($_GET['moduleId']))?$_GET['moduleId']:$_POST['moduleId'];
        $model = new HomemoduleattrModel();

        //取db数据并组装
        $listData = $model->getFormData($moduleId);

        //ajax表单提交
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

    //模块节点列表页
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
        //模块扩展下拉
        $widgetData= D('Homeextclassify')->getDropDownList($moduleId,$extId);

        //模块主表
        $moduleMod = D('Homemodule');
        $moduleRes = $moduleMod->find($moduleId);

        //根据$moduleId获取节点属性
        $moduleAttrObj = new HomemoduleattrModel();
        $attrList = $moduleAttrObj->getAttrList($moduleId);

        $this->assign('attrList',$attrList);
        $this->assign('moduleRes',$moduleRes);
        $this->assign('widgetData',$widgetData);
        $this->display();
    }

    //ajax方式改变状态
    public function moduleItemDel(){
        if($this->isAjax()){
            $itemId = $_POST['itemId'];
            $model = D('Homemoduleitem');
            $resData = $model->find($itemId);
            if($resData){
                $ajaxDel = $model->del = (!$model->del)?1:0; //状态取反
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

    //模块节点CURD
    public function moduleItemCurd(){
        $itemId = (isset($_GET['itemId']))?$_GET['itemId']:$_POST['itemId'];
        $moduleId =  (isset($_GET['moduleId']))?$_GET['moduleId']:$_POST['moduleId'];
        $moduleAttrObj = new HomemoduleattrModel();
        $moduleItemMod = new HomemoduleitemModel();

        //ajax表单提交
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

        //当$itemId存在时表示为update状态，需要获取对应的节点数据
        if($itemId){
            $itemData = $moduleItemMod->getItemRenderData($itemId);
            $moduleId = $itemData['moduleId'];
            $itemList = $itemData['itemList'];
        }
        //根据$moduleId获取节点属性
        $attrList = $moduleAttrObj->getAttrList($moduleId);

        //模块扩展下拉
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