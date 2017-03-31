<?php
	function js_show($control,$style=1,$tip=''){//��Ҫ����iframe
		echo "<script>";
		if($style=1){
			$tip = "<font style=color:red>".$tip."</font>";
		}else{
			$tip = '';
		}
		echo "parent.document.getElementById('".$control."').innerHTML = '".$tip."';";
		echo "</script>";
	}

	function js_alert($tip='',$href='',$sty=1){//js����
		echo "<script>";
		if($sty==1){
			if($tip)  echo "parent.alert('".$tip."');";
			if($href) echo "parent.location.href='".$href."'";
		}else{
			if($tip)  echo "alert('".$tip."');";
			if($href) echo "location.href='".$href."'";
		}
		echo "</script>";
	}
    
    function js_alert_layer($tip='',$href='',$sty=1){
        echo "<script>";
		if($sty==1){
			if($tip)  echo "layer.alert('".$tip."');";
			if($href) echo "parent.location.href='".$href."'";
		}else{
			if($tip)  echo "layer.alert('".$tip."');";
			if($href) echo "location.href='".$href."'";
		}
		echo "</script>";
    }

	function set_cookie($name,$value,$timestr='+30 day',$host='house365.com',$path = '/'){
		$timeout = strtotime($timestr);
		$issec = $_SERVER['SERVER_PORT']=='443' ? 1:0;//�ж��Ƿ���� 443 ����������˿�
		$re = setCookie($name,$value,$timeout,$path,$host,$issec);
		Return $re;
	}

	function clear_cookie($name,$host='house365.com',$path='/') {//���COOKIE
		$re = setcookie($name,"",time()-3600*24,$path,$host);
		Return $re;
	}

	function create_htmlable($name,$label,$value='',$val='',$class='',$style='',$js=''){//����html��ǩ
		//name��ǩ����|label��ǩ����|value��ǩ��ֵ(��ΪcheckboxʱΪ����)|val��Ҫ����radio��checkbox
		$html = '';
		if($class) $cls = 'class="'.$class.'"';
		if($style) $sty = 'style="'.$style.'"';
		if($name)  $nam = 'name="'.$name.'"';

		if($label=='text' || $label=='password'){//�ı���
			if($name) $id = 'id="'.$name.'"';
			$html = '<input '.$cls.' type="'.$label.'" '.$nam.' '.$id.' value="'.$value.'" '.$sty.' '.$js.'>';			
		}elseif($label=='radio' ){//radio��checkbox�� 
			if(is_array($val)){
				foreach($val as $key=>$item){
					if($key==$value) $checked='checked';else $checked = '';
					$html.='<input type="'.$label.'" '.$nam.' '.$checked.' value="'.$key.'" '.$sty.' '.$js.'>'.$item;
				}
			}
		}elseif($label=='checkbox'){//checkbox��
			if(is_array($val)){
				!is_array($value) ? $value = array() : 0;
				foreach($val as $key=>$item){
					if(in_array($key,$value)) $checked='checked';else $checked = '';
					$html.='<input type="'.$label.'" '.$nam.' '.$checked.' value="'.$key.'" '.$sty.' '.$js.'>'.$item;
				}
			}
		}elseif($label=='select'){//������
			if(is_array($val)){
				if($name) $id = 'id="'.$name.'"';
				$html.='<select '.$nam.' '.$id.'  '.$sty.' '.$js.'>';
				foreach($val as $key=>$item){
					if($key==$value) $selected = 'selected'; else $selected='';						
					$html.='<option '.$selected.' value="'.$key.'">'.$item.'</option>';
					
				}
				$html.='</select>';
			}			
		}
		return $html;
	}

	function GetIP(){//��ȡIP����
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$onlineip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$onlineip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$onlineip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$onlineip = $_SERVER['REMOTE_ADDR'];
		}
		preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
		$onlineip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
		return $onlineip ;
	}

	/************execl������*************************/
	function xlsBOF() {
		echo pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
		return;
	}
	function xlsEOF() {
		echo pack("ss", 0x0A, 0x00);
		return;
	}
	function xlsWriteNumber($Row, $Col, $Value) {
		echo pack("sssss", 0x203, 14, $Row, $Col, 0x0);
		echo pack("d", $Value);
		return;
	}
	function xlsWriteLabel($Row, $Col, $Value ) {
		$L = strlen($Value);
		echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
		echo $Value;
		return;
	}
	function exportexcel($filename,$fields=array(),$records=array()){
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-type: application/vnd.ms-excel;charset=gb2312");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");;
		header("Content-Disposition: attachment;filename=$filename.xls ");
		header("Content-Transfer-Encoding: binary ");//*/		
		xlsBOF();
		foreach ($fields as $key=>$item){
			$name = $item;
			xlsWriteLabel(0,$key,$name);
		}			
		foreach ($records as $key=>$item){
			$I=0;
			for($j=0;$j<count($item);$j=$j+1){
				$value = $item[$j];
				xlsWriteLabel($key+1,$I,"$value");
				$I++;
			}																					
		}
		xlsEOF();	
	}//excel�ļ�����

	/************execl������*************************/
	function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
		$ckey_length = 4;
		$key = md5($key ? $key : 'J1WdW4D6S4GbM9387aYe0ag1M4H2oeD388p4T9m7U16afaYbD95dB3c4NfA4I5s4');
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
		$cryptkey = $keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
		$result = '';
		$box = range(0, 255);
		$rndkey = array();
		for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}
		for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
		}
		for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
		return substr($result, 26);
		} else {
		return '';
		}
		} else {
		return $keyc.str_replace('=', '', base64_encode($result));
		}
	}

	function keyChange($record){ //Fun:�˷���Ϊ���м����Keyת��ΪСд
		if(is_array($record)){
			foreach($record as $key=>$item){
				$tmp = '';
				$arr_key=array_keys($item);
				for($i=0;$i<count($arr_key);$i++){
					$tmp[strtolower($arr_key[$i])]=$item[$arr_key[$i]];
				}
				if(is_array($tmp)) $result[$key] = $tmp;	
			}
		}
		return $result;
	}

	 
	function msubstr($str, $start=0, $length, $charset="gb2312", $suffix=true) {
		if(function_exists("mb_substr"))
			$slice = mb_substr($str, $start, $length, $charset);
		elseif(function_exists('iconv_substr')) {
			$slice = iconv_substr($str,$start,$length,$charset);
			if(false === $slice) {
				$slice = '';
			}
		}else{
			$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			preg_match_all($re[$charset], $str, $match);
			$slice = join("",array_slice($match[0], $start, $length));
		}
		return $suffix ? $slice.'...' : $slice;
	}

	function gethttpvar($httpvar){//���һ�����ڶ��post������ȡ�ļ�
		if(is_array($httpvar)){
			foreach($httpvar as $value){
				$val = $_POST[$value];
				if(is_array($val) && count($val)){
					$val=array_unique($val);
					$val = join(',',$val);
				}else{
					$val =trim($val);
					$val = htmlspecialchars(addslashes($val));
				}
				$re[$value] = $val;
			}
		}
		return $re;
	}
	/*
	 *$data ��Ҫ�����ϵ�����
	 *$data2	��Ҫ���Ͻ�$data������
	 *$processkey	��Ҫ��������ֶ�
	*/
	function datamerge($data,$data2,$processkey){
		foreach($data as $key=>$value){
			if($value){//�������ݵ�ʱ���Ҫ���д���
				if($data2[$key] && in_array($key,$processkey)){
					$data2[$key]=explode(",",$data2[$key]);
					$value=explode(",",$value);
					$value=array_merge($value,$data2[$key]);
					$value=array_unique($value);
					$data[$key]=implode(",",$value);
				}
			}else{
				unset($data[$key]);
			}
		}
		return $data;
	}

	function get_microtime()
	{
		list($usec, $sec) = explode(' ', microtime()); 
		return ((float)$usec + (float)$sec); 
	}
	
	function exportTxt($filename, $fields=array(),$records=array()){
	    header("Content-type: text/plain");
        header("Accept-Ranges: bytes");
        header("Content-Disposition: attachment; filename=$filename.txt");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header("Pragma: no-cache" );
        header("Expires: 0" ); 
        
        echo implode("\t",$fields)."\r\n";
        foreach ($records as $_record){
            echo implode("\t",$_record)."\r\n";
        }
        exit();
	}
	//�ֻ��������
	function phone_Encrypt($numbers){
		$lenter1="";
		$lenter2="";
		$result="";
		$numbers=strval($numbers);
		if(strlen($numbers)==11){
			$numbers=strrev($numbers);
			$array =str_split($numbers);
			foreach($array as $key=> $value){
				  $array[$key]=str_pad($value*2,2,"0",STR_PAD_LEFT);
			}
			foreach($array as $key=> $value){
				$str_array[$key]=str_split($value);
				$lenter1.=$str_array[$key][0];
				$lenter2.=$str_array[$key][1];	
			}
		   $first =bindec($lenter1);
		   $result =$first.$lenter2;
		   $result =strrev(str_replace("=","",base64_encode($result)));
		}
	  return $result;
	}
	//�ֻ��������
	function phone_Decrypt($str){
		$result="";
		if($str){
			$str=base64_decode(strrev($str)."==");
			$lenter1_array =str_split(str_pad(decbin(substr($str,0,-11)),11,"0",STR_PAD_LEFT));
			$lenter2_array =str_split(substr($str,-11));
			$result_array=array();
			for($i=0;$i<11;$i++){
				$result_array[$i]=intval($lenter1_array[$i].$lenter2_array[$i])/2;
			}	
			foreach($result_array as $value){
			$result.=$value;
			}
			$result=strrev($result);
		}
		return  $result;
	}
	
	function excelDatas($file){
	    Vendor('PhpExcel.PHPExcel');
        $PHPExcel = new PHPExcel();
        $PHPReader = new PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($file)){
            $PHPReader = new PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($file)){
                echo 'no Excel';
                return ;
            }
        }
        $PHPExcel = $PHPReader->load($file);
        /**��ȡexcel�ļ��еĵ�һ��������*/
        $currentSheet = $PHPExcel->getSheet(0);
        /**ȡ�������к�*/
        $allColumn = $currentSheet->getHighestColumn();
        /**ȡ��һ���ж�����*/
        $allRow = $currentSheet->getHighestRow();
        /**�ӵڶ��п�ʼ�������Ϊexcel���е�һ��Ϊ����*/
        $excel_datas = array();
        for($currentRow = 1; $currentRow <= $allRow; $currentRow++){
            $_excel_row = array();
            /**�ӵ�A�п�ʼ���*/
            for($currentColumn= 'A';$currentColumn<= $allColumn; $currentColumn++){
                $val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65,$currentRow)->getValue();/**ord()���ַ�תΪʮ������*/
				if(is_numeric($val)){//by lxx �����ѧ������ǿ��ת�������ָ�ʽ
					$val = number_format($val,'','','');
				}
                $_excel_row[] = iconv('utf-8','gb2312',$val);
            }
            $excel_datas[] = $_excel_row;
        }
        return $excel_datas;
	} 
	/**
	 * ����ά�����������Ϊ �ڶ�άĳ����
	 * 
	 * @param array $arr
	 * @param int $key
	 */
	function change_array_key($arr, $index){
		$arr1 = array();
		foreach ($arr as $key => $val) {
			$arr1[$val[$index]] = $val;
		}
		return $arr1;
	}

	function JJ($type){
		$config = array();
		$re = M('conf')->where("conf_type='$type' and conf_del=0")->select();
		if(is_array($re)){
			foreach($re as $item){
				$config[$item['conf_value']] = $item['conf_name'];
			}
		}
		return $config;

	}
    
    
 /**
* GBKתUFT-8��֧��������ַ���
*
* @param unknown_type $array
* @return unknown
*/
function g2u($array)
{
	if (!is_array($array)) {return iconv("GBK","UTF-8", $array);}
	if (count($array) == 0) {return $array;}
	foreach ($array as $key=>$value)
	{
		$key=iconv("GBK","UTF-8", $key);
		if (!is_object($value)) {
		  if(!is_array($value)){
		      $value = iconv("GBK","UTF-8", $value);
		  }else{
		      $value = g2u($value);
		  }
			//$value = !is_array($value) ? iconv("GBK","UTF-8", $value); : g2u($value);
		} 
		$temparray[$key]=$value;
	}
	return $temparray;
}

/**
* UFT-8תGBK��֧��������ַ���
*
* @param unknown_type $array
* @return unknown
*/
function u2g($array)
{
	if (!is_array($array)) {return iconv("UTF-8","GBK", $array);}
	if (count($array) == 0) {return $array;}
	foreach ($array as $key=>$value)
	{
		$key=iconv("GBK","UTF-8", $key);
		if (!is_object($value)) {
		  if(!is_array($value)){
		      $value = iconv("UTF-8","GBK", $value);
		  }else{
		      $value = u2g($value);
		  }
	
		} 
		$temparray[$key]=$value;
	}
	return $temparray;
}


/**
+----------------------------------------------------------
* �ӿ�������ͨѶ����
+----------------------------------------------------------
* @param $url string �ӿڻ��߷�����URL
* @param $method string   ����ʽGET/POST��Ĭ��GET��ʽ����
* @param $t_url string    ��HTTP�����а���һ����referer��ͷ���ַ���
+----------------------------------------------------------
* @return mixed �ɹ����ؽӿ����ݣ�ʧ�ܷ���FALSE
+----------------------------------------------------------
*/
function curl_get_contents($url , $method = 'get' , $t_url = '')
{   
    $ch = curl_init();
    $t_url = $t_url ? $t_url : "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    
    if(strtolower($method) == 'post')
    {
        $arr_url = explode("?",$url);
        $url = $arr_url[0];
        $postfield = $arr_url[1];        
        curl_setopt($ch, CURLOPT_URL, $url);  
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfield);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_REFERER, $t_url); 
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    }
    else if(strtolower($method) == 'get')
    {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $t_url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    }
    
    $content = curl_exec($ch);
    curl_close($ch);
    return $content; 
}


//ҳ����ת����
function halt2($msg='',$url='',$parent=0){
    $output = '';
    $output .= '<script type="text/javascript">';
    $output .= $msg?'alert("'.$msg.'");':'';

    if($parent){
        for($i=0;$i<$parent;$i++){
            $output .= 'parent.';
        }
    }

    $output .= $url?'document.location.href="'.$url.'";':'';
    $output .= '</script>';
    echo $output;
    exit;
}

function halt_http_referer($msg='')
{
    $m =  $GET['_URL_'][0];  
	$a =  $GET['_URL_'][1];
    $referer = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : "index.php?s=/".$m/$a;
    halt2($msg,$referer);
}


/**
+----------------------------------------------------------
* ���Ͷ��ź���
+----------------------------------------------------------
* @param $msg string ��������
* @param $mobile string   ��Ҫ���͵��ֻ�����
* @param $p_city string   ����ƴ����д
+----------------------------------------------------------
* @return boolean �Ƿ�ɹ�
+----------------------------------------------------------
*/
function send_sms($msg, $mobile, $p_city)
{   
    $result = FALSE;
    
    if(!empty($msg) && !empty($mobile) && !empty($p_city))
    {
        $sms_url = "http://mysms.house365.com:81/index.php/Interface/apiSendMobil"
                    ."/jid/8/depart/1/city/".$p_city."/mobileno/".$mobile."/?msg=".urlencode($msg);
        $result = curl_get_contents($sms_url);
    }
    
    return $result;
}


// oa �ʼ�
function oa_notice($toid,$fromid,$subject,$content){
    $post = "&a=sendmail";

    $post .= "&toid=".urlencode($toid);
    $post .= "&fromid=".urlencode($fromid);
    $post .= "&subject=".urlencode($subject);
    $post .= "&content=".urlencode($content);

    $api_url = P_OA_API.$post;
    $info = get_api_content($api_url,0);
}


/**
+----------------------------------------------------------
* oracle date��ʽ����ת����ʾ��ʽ
+----------------------------------------------------------
* @param $or_date string oracle��ʽ�����ַ���
* @param $format_str string   ��Ҫת����PHP���ڸ�ʽ
+----------------------------------------------------------
* @return sring ���ڸ�ʽ
+----------------------------------------------------------
*/
function oracle_date_format($or_date, $format_str = 'Y-m-d')
{   
    $format_date = '';
    if(!empty($or_date) && $or_date != "")
    {
        preg_match('/(?<d>\d{2})-(?<m>\d{1,2})��\s*-(?<y>\d{2})/', $or_date , $m);
        $format_date = date($format_str, strtotime($m['y'].'-'.$m['m'].'-'.$m['d']));
    }
    
    return $format_date;
}


/**
 +----------------------------------------------------------
 * �����ʽת��Ϊϵͳ��Ҫ��LISTCHAR��ʽ[�磺�ֽ�^1^POS��^2^����^3]
 +----------------------------------------------------------
 * @param array $data_arr	��Ҫת��������
 * @param string $separator �ָ����
 +----------------------------------------------------------
 * @return sring ��ʽ�ַ���
 +----------------------------------------------------------
 */
function array2listchar($data_arr, $separator = '^')
{
	$format_str = '';
	
	if(is_array($data_arr) && !empty($data_arr) && $separator != '')
	{
		foreach($data_arr as $key=>$value)
		{	
			$format_value = $value.$separator.$key;
			
			$format_str .= $format_str != '' ? $separator.$format_value : $format_value;
		}
	}
	
	return $format_str;
}
//��ȡ��������id
function getFlowTypeId($key){
	$record = M('Erp_flowtype')->where("pinyin = '{$key}'")->find();
	
	if($record) return $record['ID'];EXIT;
	
	return '';
}
//�������
function getProportion($a,$b){
	return round($a/$b,2);

}


/**
 *��־��¼������"Ymd.log"���ɵ�����־�ļ�
 * ��־·��Ϊ������ļ�����Ŀ¼/logs/$type/��������.log.php������ /logs/error/20120105.log.php
 * @param string $type ��־���ͣ���ӦlogsĿ¼�µ����ļ�����
 * @param string $content ��־����
 * @return bool true/false д��ɹ��򷵻�true
 */
 function writelog($type="",$content=""){
    if(!$content || !$type){
        return FALSE;
    }    
    $dir=getcwd().DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.$type;
    if(!is_dir($dir)){ 
        if(!mkdir($dir)){
            return false;
        }
    }
    $filename=$dir.DIRECTORY_SEPARATOR.date("Ymd",time()).'.log.php';   
    $logs=include $filename;
    if($logs && !is_array($logs)){
        unlink($filename);
        return false;
    }
    $logs[]=array("time"=>date("Y-m-d H:i:s"),"content"=>$content);
    $str="<?php \r\n return ".var_export($logs, true).";";
    if(!$fp=@fopen($filename,"wb")){
        return false;
    }           
    if(!fwrite($fp, $str))return false;
    fclose($fp);
    return true;
 }


?>