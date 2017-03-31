<?php
// 
class UpfileAction extends ExtendAction {
	public $image;
    public $thumb;
    public $error;
    public function index(){
		//$this->display();
    }
    
    
    public function upFile($inputname,$filetype="image",$ImageSize=0){
        Import("ORG.Util.UploadFile");

        $uf = new UploadFile($inputname);
        $uf->setFileType($filetype);
		$uf->setMaxSize("2000");
		$uf->setUploadType("ftp");
		$uf->setSaveDir("/loan/");
        if($ImageSize){
    		$uf->setResizeImage(true);//是否生成调整图
    		$uf->setResizeImageSize($ImageSize);//设置缩略图大小
    		$uf->setForceResizeImage(true);//是否强制生成调整图
        }

		$rtnMSG=$uf->upload();
        //die($rtnMSG);
        if($rtnMSG=="success"){
            $this->image = $uf->getSaveFileURL();
            if($ImageSize)
			 $this->thumb = $uf->getResizeImageURL(); 
            return true;    
        }
        $this->error = $rtnMSG;
        return false;
    }
    
    public function ueditorUpImage(){
         $editorId=$_GET['editorid'];
         $re = $this->upFile("upfile");
         if($re)
            $state = "SUCCESS";
         else
            $state = $this->error;
         /**
         * 返回数据，调用父页面的ue_callback回调
         */
             
        if($type == "ajax"){
            echo $info[ "url" ];
        }else{
            echo "<script>parent.UM.getEditor('". $editorId ."').getWidgetCallback('image')('" . $this->image . "','" . $state . "')</script>";
        }
    }
}