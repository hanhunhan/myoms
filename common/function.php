<?php
function Jalert($msg,$url=''){
	echo "<script>alert('$msg');</script>";
	if($url) echo "<script>window.location.href='$url';</script>";
	//exit;
}



/**
 * GBK转UFT-8，支付数组和字符串
 *
 * @param unknown_type $array
 * @return unknown
 */
function g2u($array) {
    if (!is_array($array)) {
        return iconv("GBK", "UTF-8", $array);
    }
    if (count($array) == 0) {
        return $array;
    }
    foreach ($array as $key => $value) {
        $key = iconv("GBK", "UTF-8", $key);
        if (!is_object($value)) {
            if (!is_array($value)) {
                $value = iconv("GBK", "UTF-8", $value);
            } else {
                $value = g2u($value);
            }
            //$value = !is_array($value) ? iconv("GBK","UTF-8", $value); : g2u($value);
        }
        $temparray[$key] = $value;
    }
    return $temparray;
}

/**
 * UFT-8转GBK，支付数组和字符串
 *
 * @param unknown_type $array
 * @return unknown
 */
function u2g($array) {
    if (!is_array($array)) {
        return iconv("UTF-8", "GBK", $array);
    }
    if (count($array) == 0) {
        return $array;
    }
    foreach ($array as $key => $value) {
        $key = iconv("GBK", "UTF-8", $key);
        if (!is_object($value)) {
            if (!is_array($value)) {
                $value = iconv("UTF-8", "GBK", $value);
            } else {
                $value = u2g($value);
            }

        }
        $temparray[$key] = $value;
    }
    return $temparray;
}

function getChildrens($db,$pid){
	global $DB_PREFIX;
	$temp  = array();
	if(is_array($pid)){
		$pids = implode(',',$pid);
		$sql = "SELECT * FROM ".$DB_PREFIX."new_department where DEPT_PARENT in( $pids ) and status<>-1";
		$temp = array_merge($temp,$pid);
	}else{
		$sql = "SELECT * FROM ".$DB_PREFIX."new_department where DEPT_PARENT in ($pid) and status<>-1 ";
		$temp[] = $pid;
	}
	$row = $db->getAll($sql); 
	
	foreach($row as $val){
		 
		$temp[] =$val['DEPT_ID'];
		 
		$children = getChildrens($db,$val['DEPT_ID']);
		if($children)$temp = array_merge($temp,$children);
		 
	}
	return $temp;

}
function geParents($db,$id){
	global $DB_PREFIX;
	$temp  = array();
 
	$sql = "SELECT * FROM ".$DB_PREFIX."new_department where DEPT_ID = $id and status<>-1 ";
	$row = $db->getOne($sql);
	if($row){
		$temp[] = $row['DEPT_NAME'];
		$sql = "SELECT * FROM ".$DB_PREFIX."new_department where DEPT_PARENT = '".$row['DEPT_PARENT']."' and status<>-1 ";
		$row = $db->getOne($sql); 
		if($row['DEPT_PARENT']){
			$parent = geParents($db,$row['DEPT_PARENT']);
			if($parent)$temp = array_merge($temp,$parent);
		}
	}
	return $temp;

}
function getTab($db,$itemid){
	global $DB_PREFIX;
	$temp  = array();
	if($itemid){
		if(is_array($itemid)){
			$itemid = implode(',',$itemid);
		}
		$sql = "SELECT * FROM ".$DB_PREFIX."new_department where DEPT_ID in( $itemid )";
		 
	} 
	$row = $db->getAll($sql); 
	$res = array();
	$temp = array('field'=>'question','title'=>g2u('问题'),'width'=>'280');
	$res[] = $temp;
	foreach($row as $val){
		$temp = array('field'=>'dept_'.$val['DEPT_ID'],'title'=>g2u($val['DEPT_NAME']),'width'=>'100'); 
		$res[] = $temp;
		 
	}
	$temp = array('field'=>'average','title'=>g2u('平均分值'),'width'=>'100');
	$res[] = $temp;
	$temp = array('field'=>'quartile','title'=>g2u('75分位'),'width'=>'100');
	$res[] = $temp;
	return $res;

}
function getTab_info($db,$itemid){
	global $DB_PREFIX;
	//$temp  = array();
	if(is_array($itemid)){
		$pids = implode(',',$itemid);
		$sql = "SELECT * FROM ".$DB_PREFIX."new_department where DEPT_ID in( $pids )";
		//$temp = array_merge($temp,$itemid);
	}else{
		$sql = "SELECT * FROM ".$DB_PREFIX."new_department where DEPT_ID = $itemid ";
		//$temp[] = $itemid;
	}
	$row = $db->getAll($sql); 
	$res = array();
	 
 
	foreach($row as $val){
		 
		$res[$val['DEPT_ID']] = $val;
		 
	}
 
	return $res;

} 

//
function Quartile($arr,$count){
	if(is_array($arr)){
		sort($arr); //var_dump($arr);
		$b = 1+($count-1)*0.75;
		$a = intval($b);
		$c = $b - $a;
		
		$res = $arr[$a-1] + ( $arr[$a] - $arr[$a-1] ) * $c;
	}else $res = 0;
	return $res;

}
/**
 * curl post 提交数据
 * @param str 请求地址 $url
 * @param array 发送数据 $param
 * @param string $t_url
 * @return boolean|mixed
 * @author ZXJ
 */
function curlPost($url, $param, $t_url = '') {
    $t_url = empty($t_url) ? "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : $t_url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_REFERER, $t_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);//编码问题解决，吊炸天的gbk与utf-8
    $content = curl_exec($ch);
    curl_close($ch);
    if ($content === false) {
        return false;
    }
    return $content;
}
/**
 * +----------------------------------------------------------
 * 接口连接与通讯函数
 * +----------------------------------------------------------
 * @param $url string 接口或者服务器URL
 * @param $method string   请求方式GET/POST，默认GET方式请求
 * @param $t_url string    在HTTP请求中包含一个”referer”头的字符串
 * +----------------------------------------------------------
 * @return mixed 成功返回接口内容，失败返回FALSE
 * +----------------------------------------------------------
 */
function curl_get_contents($url, $method = 'get', $t_url = '') {
    $ch = curl_init();
    $t_url = $t_url ? $t_url : "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    if (strtolower($method) == 'post') {
        $arr_url = explode("?", $url);
        $url = $arr_url[0];
        $postfield = $arr_url[1];
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfield);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_REFERER, $t_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    } else if (strtolower($method) == 'get') {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $t_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    }

    $content = curl_exec($ch);
    curl_close($ch);
    return $content;
}
// oa 邮件
function oa_notice($toid, $fromid, $subject, $content, $copy_uids = '') {
    /*$post = "&a=sendmail";

   $post .= "&toid=".urlencode($toid);
   $post .= "&fromid=".urlencode($fromid);
   $post .= "&subject=".urlencode($subject);
   $post .= "&content=".urlencode($content);

   $api_url = "http://oa.house365.com/api/api_el.php?k=".md5("el_key").$post;

   $content = @curl_get_contents($api_url);
   $arr_content = @unserialize($content);

   if(!is_array($arr_content) && sizeof($arr_content) != 3){
       return '接口有误,请联系管理员~~';
   }
   if($arr_content[result] == 0){
       return $arr_content[msg];
   }
    return $arr_content[info];*/

    $url = "http://oa.house365.com/api/api_el.php";
    $data = array(
        'k' => md5('el_key'),
        'a' => 'sendmail',
        'toid' => urlencode($toid),
        'fromid' => urlencode($fromid),
        'subject' => $subject,
        'content' => $content,
        'copy_uids' => $copy_uids
    );


    $ch = curlPost($url, $data);
    $arr_content = @unserialize($ch);

    if (!is_array($arr_content) && sizeof($arr_content) != 3) {
        return '接口有误,请联系管理员~~';
    }
    if ($arr_content['result'] == 0) {
        return $arr_content['msg'];
    }
    return $arr_content['info'];

}

function oa_notice2($toid, $fromid, $subject, $content, $copy_uids = '',$param ) {
    /*$post = "&a=sendmail";

   $post .= "&toid=".urlencode($toid);
   $post .= "&fromid=".urlencode($fromid);
   $post .= "&subject=".urlencode($subject);
   $post .= "&content=".urlencode($content);

   $api_url = "http://oa.house365.com/api/api_el.php?k=".md5("el_key").$post;

   $content = @curl_get_contents($api_url);
   $arr_content = @unserialize($content);

   if(!is_array($arr_content) && sizeof($arr_content) != 3){
       return '接口有误,请联系管理员~~';
   }
   if($arr_content[result] == 0){
       return $arr_content[msg];
   }
    return $arr_content[info];*/

    $url = "http://oa.house365.com/api/api_el.php";
    $data = array(
        'k' => md5('el_key'),
        'a' => 'sendmailparam',
        'toid' => $toid,
        'fromid' => urlencode($fromid),
        'subject' => $subject,
        'content' => $content,
        'copy_uids' => $copy_uids,
		'param' =>$param
    );


    $ch = curlPost($url, $data);
    $arr_content = @unserialize($ch);

	file_put_contents('oa_notice_record.txt', 'url:' . $url . '-toid:' . $toid . '-fromid:' . $fromid . '-' . date('Y-m-d H:i:s') . 'subject:' . $subject . '-' . 'content:' . $content . 'copy_uids:' . $copy_uids  . 'arr_content:' . serialize($arr_content) . PHP_EOL . '\r\n\n\r', FILE_APPEND);

    if (!is_array($arr_content) && sizeof($arr_content) != 3) {
        return '接口有误,请联系管理员~~';
    }
    if ($arr_content['result'] == 0) {
        return $arr_content['msg'];
    }

	

    return $arr_content['info'];

}


/**
 * +----------------------------------------------------------
 * 发送短信函数
 * +----------------------------------------------------------
 * @param $msg string 短信内容
 * @param $mobile string   需要发送的手机号码
 * @param $p_city string   城市拼音缩写
 * +----------------------------------------------------------
 * @return boolean 是否成功
 * +----------------------------------------------------------
 */
function send_sms($msg, $mobile, $p_city) {
    $result = FALSE;

    if (!empty($msg) && !empty($mobile) && !empty($p_city)) {
        $sms_url = "http://mysms.house365.com:81/index.php/Interface/apiSendMobil"
            . "/jid/8/depart/1/city/" . $p_city . "/mobileno/" . $mobile . "/?msg=" . urlencode($msg);
		$result = curl_get_contents($sms_url);



        //$myfile = fopen("sendmsgrecord.txt", "w") or die("Unable to open file!");
        //$txt = $mobile.'-'.date('Y-m-d H:i:s').'city:'.$p_city.'-'.'result:'.$result.'\r\n';
        //fwrite($myfile, $txt);
        //fclose($myfile);
        file_put_contents('send_msg_record.txt', 'sms_url:' . $sms_url . '-mobile:' . $mobile . '-' . date('Y-m-d H:i:s') . 'city:' . $p_city . '-' .'-msg:'.$msg. 'result:' . serialize($result) . PHP_EOL . '\r\n\n\r', FILE_APPEND);

    }

    return $result;
}

function send_sms_arr($msg, $mobile_arr, $p_city){
	foreach($mobile_arr as $val){
		if($val){
			$mobiles = implode(',',$val);
			$res = send_sms($msg, $mobiles, $p_city) ;
		}
	}

}


function is_mobile(){

// returns true if one of the specified mobile browsers is detected
// 如果监测到是指定的浏览器之一则返回true

$regex_match="/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";

$regex_match.="htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";

$regex_match.="blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";

$regex_match.="symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";   

$regex_match.="jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";

$regex_match.=")/i";

// preg_match()方法功能为匹配字符，既第二个参数所含字符是否包含第一个参数所含字符，包含则返回1既true
return preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT']));
}
?>