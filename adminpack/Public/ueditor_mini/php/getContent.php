
<meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
<?php
exit;
    //��ȡ����
    error_reporting(E_ERROR|E_WARNING);
    $content =  htmlspecialchars(stripslashes($_POST['myEditor']));


    //�������ݿ������������

    //��ʾ
    echo "��1���༭����ֵ";
    echo  "<div class='content'>".htmlspecialchars_decode($content)."</div>";
