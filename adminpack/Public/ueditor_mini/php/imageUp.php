<?php
exit;
    header("Content-Type:text/html;charset=utf-8");
    error_reporting( E_ERROR | E_WARNING );
    date_default_timezone_set("Asia/chongqing");
    include "Uploader.class.php";
    //�ϴ�����
    $config = array(
        "savePath" => "upload/" ,             //�洢�ļ���
        "maxSize" => 1000 ,                   //������ļ����ߴ磬��λKB
        "allowFiles" => array( ".gif" , ".png" , ".jpg" , ".jpeg" , ".bmp" )  //������ļ���ʽ
    );
    //�ϴ��ļ�Ŀ¼
    $Path = "upload/";

    //������������ʱĿ¼��
    $config[ "savePath" ] = $Path;
    $up = new Uploader( "upfile" , $config );
    $type = $_REQUEST['type'];
    $editorId=$_GET['editorid'];

    $info = $up->getFileInfo();
    /**
     * �������ݣ����ø�ҳ���ue_callback�ص�
     */
    if($type == "ajax"){
        echo $info[ "url" ];
    }else{
        echo "<script>parent.UM.getEditor('". $editorId ."').getWidgetCallback('image')('" . $info[ "url" ] . "','" . $info[ "state" ] . "')</script>";
    }



