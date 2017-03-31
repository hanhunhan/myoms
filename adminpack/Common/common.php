<?php
/**
 * 365���뷴��
 * @param $string �ַ���
 * @param string $operation
 * @param string $key
 * @param int $expiry
 * @return string
 */
function get_authcode($string, $operation = "DECODE", $key = "", $expiry = 0) {
    // ��̬�ܳ׳��ȣ���ͬ�����Ļ����ɲ�ͬ���ľ���������̬�ܳ�
    $ckey_length = 4;

    // �ܳ�
    $key = md5($key ? $key : "house365");

    // �ܳ�a�����ӽ���
    $keya = md5(substr($key, 0, 16));

    // �ܳ�b��������������������֤
    $keyb = md5(substr($key, 16, 16));

    // �ܳ�c���ڱ仯���ɵ�����
    $keyc = $ckey_length ? ($operation == "DECODE" ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : "";

    // ����������ܳ�
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    // ���ģ�ǰ10λ��������ʱ���������ʱ��֤������Ч�ԣ�10��26λ��������$keyb(�ܳ�b)������ʱ��ͨ������ܳ���֤����������
    // ����ǽ���Ļ�����ӵ�$ckey_lengthλ��ʼ����Ϊ����ǰ$ckey_lengthλ���� ��̬�ܳף��Ա�֤������ȷ
    $string = $operation == "DECODE" ? base64_decode(substr($string, $ckey_length)) : sprintf("%010d", $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = "";
    $box = range(0, 255);
    $rndkey = array();

    // �����ܳײ�
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    // �ù̶����㷨�������ܳײ�����������ԣ�����ܸ��ӣ�ʵ���϶Բ������������ĵ�ǿ��
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    // ���ļӽ��ܲ���
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;

        // ���ܳײ��ó��ܳ׽��������ת���ַ�
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == "DECODE") {
        // substr($result,0,10)==0 ��֤������Ч��
        // substr($result,0,10)-time()>0 ��֤������Ч��
        // substr($result,10,16)==substr(md5(substr($result,26).$keyb),0,16) ��֤����������
        // ��֤������Ч�ԣ��뿴δ�������ĵĸ�ʽ
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return "";
        }
    } else {
        // �Ѷ�̬�ܳױ������������Ҳ��Ϊʲôͬ�������ģ�������ͬ���ĺ��ܽ��ܵ�ԭ��
        // ��Ϊ���ܺ�����Ŀ�����һЩ�����ַ������ƹ��̿��ܻᶪʧ��������base64����
        return $keyc . str_replace("=", "", base64_encode($result));
    }
}

function js_show($control, $style = 1, $tip = '') {//��Ҫ����iframe
    echo "<script>";
    if ($style = 1) {
        $tip = "<font style=color:red>" . $tip . "</font>";
    } else {
        $tip = '';
    }
    echo "parent.document.getElementById('" . $control . "').innerHTML = '" . $tip . "';";
    echo "</script>";
}

function js_alert($tip = '', $href = '', $type = '') {//js����
    echo "<script>";
    if ($tip) echo "alert('" . $tip . "');";
    if ($type == 'close') {
        echo "layer.closeAll();";
    } else {
        if ($href) {
            echo "location.href='" . $href . "'";
        } else {
            echo "window.history.back();";
        }
    }
    echo "</script>";
}

function js_alert_layer($tip = '', $href = '', $sty = 1) {
    echo "<script>";
    if ($sty == 1) {
        if ($tip) echo "layer.alert('" . $tip . "');";
        if ($href) echo "parent.location.href='" . $href . "'";
    } else {
        if ($tip) echo "layer.alert('" . $tip . "');";
        if ($href) echo "location.href='" . $href . "'";
    }
    echo "</script>";
}

function set_cookie($name, $value, $timestr = '+30 day', $host = 'house365.com', $path = '/') {
    $timeout = strtotime($timestr);
    $issec = $_SERVER['SERVER_PORT'] == '443' ? 1 : 0;//�ж��Ƿ���� 443 ����������˿�
    $re = setCookie($name, $value, $timeout, $path, $host, $issec);
    Return $re;
}

function clear_cookie($name, $host = 'house365.com', $path = '/') {//���COOKIE
    $re = setcookie($name, "", time() - 3600 * 24, $path, $host);
    Return $re;
}

function GetIP() {//��ȡIP����
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $onlineip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $onlineip = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $onlineip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $onlineip = $_SERVER['REMOTE_ADDR'];
    }
    preg_match("/[\d\.]{7,15}/", $onlineip, $onlineipmatches);
    $onlineip = $onlineipmatches[0] ? $onlineipmatches[0] : 'unknown';
    return $onlineip;
}

function create_htmlable($name, $label, $value = '', $val = '', $class = '', $style = '', $js = '') {//����html��ǩ
    //name��ǩ����|label��ǩ����|value��ǩ��ֵ(��ΪcheckboxʱΪ����)|val��Ҫ����radio��checkbox
    $html = '';
    if ($class) $cls = 'class="' . $class . '"';
    if ($style) $sty = 'style="' . $style . '"';
    if ($name) $nam = 'name="' . $name . '"';

    if ($label == 'text' || $label == 'password') {//�ı���
        if ($name) $id = 'id="' . $name . '"';
        $html = '<input ' . $cls . ' type="' . $label . '" ' . $nam . ' ' . $id . ' value="' . $value . '" ' . $sty . ' ' . $js . '>';
    } elseif ($label == 'radio') {//radio��checkbox��
        if (is_array($val)) {
            foreach ($val as $key => $item) {
                if ($key == $value) $checked = 'checked'; else $checked = '';
                $html .= '<input type="' . $label . '" ' . $nam . ' ' . $checked . ' value="' . $key . '" ' . $sty . ' ' . $js . '>' . $item;
            }
        }
    } elseif ($label == 'checkbox') {//checkbox��
        if (is_array($val)) {
            !is_array($value) ? $value = array() : 0;
            foreach ($val as $key => $item) {
                if (in_array($key, $value)) $checked = 'checked'; else $checked = '';
                $html .= '<input type="' . $label . '" ' . $nam . ' ' . $checked . ' value="' . $key . '" ' . $sty . ' ' . $js . '>' . $item;
            }
        }
    } elseif ($label == 'select') {//������
        if (is_array($val)) {
            if ($name) $id = 'id="' . $name . '"';
            $html .= '<select ' . $nam . ' ' . $id . '  ' . $sty . ' ' . $js . '>';
            foreach ($val as $key => $item) {
                if ($key == $value) $selected = 'selected'; else $selected = '';
                $html .= '<option ' . $selected . ' value="' . $key . '">' . $item . '</option>';

            }
            $html .= '</select>';
        }
    }
    return $html;
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

function xlsWriteLabel($Row, $Col, $Value) {
    $L = strlen($Value);
    echo pack("ssssss", 0x204, 8 + $L, $Row, $Col, 0x0, $L);
    echo $Value;
    return;
}

function exportexcel($filename, $fields = array(), $records = array()) {
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
    foreach ($fields as $key => $item) {
        $name = $item;
        xlsWriteLabel(0, $key, $name);
    }
    foreach ($records as $key => $item) {
        $I = 0;
        for ($j = 0; $j < count($item); $j = $j + 1) {
            $value = $item[$j];
            xlsWriteLabel($key + 1, $I, "$value");
            $I++;
        }
    }
    xlsEOF();
}//excel�ļ�����

//����html
function exportHtml($excelTitle, $html) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
    header("Content-Type:application/force-download");
    header("Content-Type:application/vnd.ms-execl");
    header("Content-Type:application/octet-stream");
    header("Content-Type:application/download");
    header("Content-Disposition:attachment;filename=" . $excelTitle . date("YmdHis") . ".xls");
    exit($html);
}

/************execl������*************************/
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    $ckey_length = 4;
    $key = md5($key ? $key : 'J1WdW4D6S4GbM9387aYe0ag1M4H2oeD388p4T9m7U16afaYbD95dB3c4NfA4I5s4');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

function keyChange($record) { //Fun:�˷���Ϊ���м����Keyת��ΪСд
    if (is_array($record)) {
        foreach ($record as $key => $item) {
            $tmp = '';
            $arr_key = array_keys($item);
            for ($i = 0; $i < count($arr_key); $i++) {
                $tmp[strtolower($arr_key[$i])] = $item[$arr_key[$i]];
            }
            if (is_array($tmp)) $result[$key] = $tmp;
        }
    }
    return $result;
}


function msubstr($str, $start = 0, $length, $charset = "gb2312", $suffix = true) {
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice . '...' : $slice;
}

function gethttpvar($httpvar) {//���һ�����ڶ��post������ȡ�ļ�
    if (is_array($httpvar)) {
        foreach ($httpvar as $value) {
            $val = $_POST[$value];
            if (is_array($val) && count($val)) {
                $val = array_unique($val);
                $val = join(',', $val);
            } else {
                $val = trim($val);
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
function datamerge($data, $data2, $processkey) {
    foreach ($data as $key => $value) {
        if ($value) {//�������ݵ�ʱ���Ҫ���д���
            if ($data2[$key] && in_array($key, $processkey)) {
                $data2[$key] = explode(",", $data2[$key]);
                $value = explode(",", $value);
                $value = array_merge($value, $data2[$key]);
                $value = array_unique($value);
                $data[$key] = implode(",", $value);
            }
        } else {
            unset($data[$key]);
        }
    }
    return $data;
}

function get_microtime() {
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
}

function exportTxt($filename, $fields = array(), $records = array()) {
    header("Content-type: text/plain");
    header("Accept-Ranges: bytes");
    header("Content-Disposition: attachment; filename=$filename.txt");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo implode("\t", $fields) . "\r\n";
    foreach ($records as $_record) {
        echo implode("\t", $_record) . "\r\n";
    }
    exit();
}

//�ֻ��������
function phone_Encrypt($numbers) {
    $lenter1 = "";
    $lenter2 = "";
    $result = "";
    $numbers = strval($numbers);
    if (strlen($numbers) == 11) {
        $numbers = strrev($numbers);
        $array = str_split($numbers);
        foreach ($array as $key => $value) {
            $array[$key] = str_pad($value * 2, 2, "0", STR_PAD_LEFT);
        }
        foreach ($array as $key => $value) {
            $str_array[$key] = str_split($value);
            $lenter1 .= $str_array[$key][0];
            $lenter2 .= $str_array[$key][1];
        }
        $first = bindec($lenter1);
        $result = $first . $lenter2;
        $result = strrev(str_replace("=", "", base64_encode($result)));
    }
    return $result;
}

//�ֻ��������
function phone_Decrypt($str) {
    $result = "";
    if ($str) {
        $str = base64_decode(strrev($str) . "==");
        $lenter1_array = str_split(str_pad(decbin(substr($str, 0, -11)), 11, "0", STR_PAD_LEFT));
        $lenter2_array = str_split(substr($str, -11));
        $result_array = array();
        for ($i = 0; $i < 11; $i++) {
            $result_array[$i] = intval($lenter1_array[$i] . $lenter2_array[$i]) / 2;
        }
        foreach ($result_array as $value) {
            $result .= $value;
        }
        $result = strrev($result);
    }
    return $result;
}

function excelDatas($file) {
    Vendor('PhpExcel.PHPExcel');
    $PHPExcel = new PHPExcel();
    $PHPReader = new PHPExcel_Reader_Excel2007();
    if (!$PHPReader->canRead($file)) {
        $PHPReader = new PHPExcel_Reader_Excel5();
        if (!$PHPReader->canRead($file)) {
            echo 'no Excel';
            return;
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
    for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
        $_excel_row = array();
        /**�ӵ�A�п�ʼ���*/
        for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
            $val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65, $currentRow)->getValue();
            /**ord()���ַ�תΪʮ������*/
            if (is_numeric($val)) {//by lxx �����ѧ������ǿ��ת�������ָ�ʽ
                $val = number_format($val, '', '', '');
            }
            $_excel_row[] = iconv('utf-8', 'gb2312', $val);
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
function change_array_key($arr, $index) {
    $arr1 = array();
    foreach ($arr as $key => $val) {
        $arr1[$val[$index]] = $val;
    }
    return $arr1;
}

function JJ($type) {
    $config = array();
    $re = M('conf')->where("conf_type='$type' and conf_del=0")->select();
    if (is_array($re)) {
        foreach ($re as $item) {
            $config[$item['conf_value']] = $item['conf_name'];
        }
    }
    return $config;

}


/**
 * +----------------------------------------------------------
 * ����ά����ת��Ϊһά���飬��ȥ�����е��ظ���Ԫ��
 * +----------------------------------------------------------
 * @param $array  ����������
+----------------------------------------------------------
 * @return $tmp  ���ص�������
 * +----------------------------------------------------------
 */
function array2new($array) {
    static $tmp;
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            array2new($val);
        } else {
            $tmp[] = $val;
        }
        $tmp = array_unique($tmp);
    }
    return $tmp;
}

/**
 * GBKתUFT-8��֧��������ַ���
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
 * UFT-8תGBK��֧��������ַ���
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


/**
 * +----------------------------------------------------------
 * �ӿ�������ͨѶ����
 * +----------------------------------------------------------
 * @param $url string �ӿڻ��߷�����URL
 * @param $method string   ����ʽGET/POST��Ĭ��GET��ʽ����
 * @param $t_url string    ��HTTP�����а���һ����referer��ͷ���ַ���
 * +----------------------------------------------------------
 * @return mixed �ɹ����ؽӿ����ݣ�ʧ�ܷ���FALSE
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

/**
 * curl post �ύ����
 * @param str �����ַ $url
 * @param array �������� $param
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
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);//��������������ը���gbk��utf-8
    $content = curl_exec($ch);
    curl_close($ch);
    if ($content === false) {
        return false;
    }
    return $content;
}

//ҳ����ת����
function halt2($msg = '', $url = '', $parent = 0) {

    $output = '';
    $output .= '<script type="text/javascript">';
    $output .= $msg ? 'alert("' . $msg . '");' : '';

    if ($parent) {
        for ($i = 0; $i < $parent; $i++) {
            $output .= 'parent.';
        }
    }

    $output .= $url ? 'document.location.href="' . $url . '";' : '';
    $output .= '</script>';
    echo $output;
    exit;
}

function halt_http_referer($msg = '') {
    $m = $GET['_URL_'][0];
    $a = $GET['_URL_'][1];
    $referer = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : "index.php?s=/" . $m / $a;
    halt2($msg, $referer);
}


/**
 * +----------------------------------------------------------
 * ���Ͷ��ź���
 * +----------------------------------------------------------
 * @param $msg string ��������
 * @param $mobile string   ��Ҫ���͵��ֻ�����
 * @param $p_city string   ����ƴ����д
 * +----------------------------------------------------------
 * @return boolean �Ƿ�ɹ�
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
        file_put_contents('send_msg_record.txt', 'sms_url:' . $sms_url . '-mobile:' . $mobile . '-' . date('Y-m-d H:i:s') . 'city:' . $p_city . '-' . 'result:' . $result . PHP_EOL . '\r\n\n\r', FILE_APPEND);

    }

    return $result;
}


// oa �ʼ�
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
       return '�ӿ�����,����ϵ����Ա~~';
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
        return '�ӿ�����,����ϵ����Ա~~';
    }
    if ($arr_content['result'] == 0) {
        return $arr_content['msg'];
    }
    return $arr_content['info'];

}

// ����prjid bulidno ��ȡ���ܾ�״̬�Ա�

function get_Compare_Data($prjid, $buildno) {
    $compareApi = "http://www.njhouse.com.cn/api/project_build_house_stat.php";
    $str = file_get_contents($compareApi . "?prjid=$prjid&buildno=$buildno");

    return unserialize($str);
    exit;
}

// ���� �û�id ��ȡ��¼�˺�
function get_UserName_User_ID($userid) {
    $username = M("Erp_users")->where("ID = $userid")->getField("USERNAME");

    return $username;
}

/**
 * +----------------------------------------------------------
 * oracle date��ʽ����ת����ʾ��ʽ
 * +----------------------------------------------------------
 * @param $or_date string oracle��ʽ�����ַ���
 * @param $format_str string   ��Ҫת����PHP���ڸ�ʽ
 * +----------------------------------------------------------
 * @return sring ���ڸ�ʽ
 * +----------------------------------------------------------
 */
function oracle_date_format($or_date, $format_str = 'Y-m-d') {
    $format_date = '';
    if (!empty($or_date) && $or_date != "") {
        preg_match('/(?<d>\d{2})-(?<m>\d{1,2})��\s*-(?<y>\d{2})/', $or_date, $m);
        if ($m) {
            $format_date = date($format_str, strtotime($m['y'] . '-' . $m['m'] . '-' . $m['d']));
        } else  $format_date = $or_date;

    }

    return $format_date;
}


/**
 * +----------------------------------------------------------
 * �����ʽת��Ϊϵͳ��Ҫ��LISTCHAR��ʽ[�磺�ֽ�^1^POS��^2^����^3]
 * +----------------------------------------------------------
 * @param array $data_arr ��Ҫת��������
 * @param string $separator �ָ����
 * +----------------------------------------------------------
 * @return sring ��ʽ�ַ���
 * +----------------------------------------------------------
 */
function array2listchar($data_arr, $separator = '^') {
    $format_str = '';

    if (is_array($data_arr) && !empty($data_arr) && $separator != '') {
        foreach ($data_arr as $key => $value) {
            $format_value = $value . $separator . $key;

            $format_str .= $format_str != '' ? $separator . $format_value : $format_value;
        }
    }

    return $format_str;
}


//��ȡ��������id
function getFlowTypeId($key) {
    $record = M('Erp_flowtype')->where("pinyin = '{$key}'")->find();

    if ($record) return $record['ID'];
    EXIT;

    return '';
}

// ��������id ��ȡ �������ͼ�д.����info
function get_Flows_Info($flowid) {
    $info = array();

    $Flows = M("Erp_flows")->where("ID=$flowid")->find();

    if ($Flows) {
        $info['data'] = $Flows;

        $typeId = M("Erp_flowset")->where("ID = " . $Flows['FLOWSETID'])->getField("FLOWTYPE");

        $pinyin = M("Erp_flowtype")->where("ID = " . $typeId)->getField("PINYIN");

        if ($pinyin) {
            $info['type'] = $pinyin;
        }
    }

    return $info;
}

//�ж��Ƿ���б༭Ȩ�� ���̷����˲��б༭Ȩ��
function judgeFlowEdit($flowId, $userId) {

    if ($_GET['operate'] && ($_GET['operate'] == 'view')) {//�������鿴
        return false;
    } else {
        $flows = M('Erp_flows')->where("id=" . $flowId . " and adduser=" . $userId)->find();
        $nodes = M("Erp_flownode")->where("flowid = " . $flowId . " and status = 2")->find();
        if (($userId == $flows['ADDUSER']) && ($userId == $nodes['DEAL_USERID'])) {
            return true;
        } else {
            return false;
        }
    }
    exit;
}

//���ַ�����ϳ�����
function srt2arr() {
    $args = func_get_args();
    foreach ($args as $v) {
        if ($v) $str .= ',' . $v;
    }
    $temp = array_filter(explode(',', $str));
    return $temp;
}

//�������
function getProportion($a, $b) {
    return round($a / $b, 2);

}


/**
 *��־��¼������"Ymd.log"���ɵ�����־�ļ�
 * ��־·��Ϊ������ļ�����Ŀ¼/logs/$type/��������.log.php������ /logs/error/20120105.log.php
 * @param string $type ��־���ͣ���ӦlogsĿ¼�µ����ļ�����
 * @param string $content ��־����
 * @return bool true/false д��ɹ��򷵻�true
 */
function writelog($type = "", $content = "") {
    if (!$content || !$type) {
        return FALSE;
    }
    $dir = getcwd() . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $type;
    if (!is_dir($dir)) {
        if (!mkdir($dir)) {
            return false;
        }
    }
    $filename = $dir . DIRECTORY_SEPARATOR . date("Ymd", time()) . '.log.php';
    $logs = include $filename;
    if ($logs && !is_array($logs)) {
        unlink($filename);
        return false;
    }
    $logs[] = array("time" => date("Y-m-d H:i:s"), "content" => $content);
    $str = "<?php \r\n return " . var_export($logs, true) . ";";
    if (!$fp = @fopen($filename, "wb")) {
        return false;
    }
    if (!fwrite($fp, $str)) return false;
    fclose($fp);
    return true;
}

/*���ݳ��л�ȡ���ж�Ӧ˰��
 * @param $citypy ����ƴ��Сд��nj��,�����Ƕ�����е����� Ҳ�����ǵ�������
 * return $taxrate ˰��
*/
function get_taxrate_by_citypy($citypy) {
    $taxrate = array(
        'nj' => 0.06,
        'mas' => 0.03,
        'fy' => 0.03,
        'chuzhou' => 0.03,
        'bblt' =>0.03,
    );

    $taxrate_key = array();
    $taxrate_val = array();
    if (is_array($citypy) && !empty($citypy)) {
        foreach ($citypy as $key => $val) {
            $taxrate_key = array_keys($taxrate);
            if (in_array($val, $taxrate_key)) {
                $taxrate_val["$taxrate_key"] = $taxrate["$taxrate_key"];
            }
        }
    } elseif (strtolower($citypy)) {
        $py = strtolower($citypy);
        //$taxrate_val = $taxrate["$py"];

        if (array_key_exists($py, $taxrate)) {
            $taxrate_val = $taxrate["$py"];
        } else //��������˰��δ��˰�� ���� �Ͼ���˰��
        {
            $taxrate_val = $taxrate["nj"];
        }
    }

    return $taxrate_val;
}

function page($count = 0, $limit = 0, $page = 1, $pageurl = '', $anchor = '') {
    $pages = $count ? ceil($count / $limit) : 1;
    $output = '';

    $output .= '<span>��' . $count . '�� <em class="c-yel">' . $page . '</em>/' . $pages . '</span>';

    if ($pages > 1) {
        if ($page > 1) {
            $output .= '<a href="' . $pageurl . '&pn=1' . $anchor . '">��ҳ</a>';
            $output .= '<a href="' . $pageurl . '&pn=' . ($page > 1 ? $page - 1 : $page) . $anchor . '">��һҳ</a>';
        }

        if ($page < $pages) {
            $output .= '<a href="' . $pageurl . '&pn=' . ($page < $pages ? $page + 1 : $page) . $anchor . '">��һҳ</a>';
            $output .= '<a href="' . $pageurl . '&pn=' . $pages . $anchor . '">ĩҳ</a>';
        }

        $output .= '<input type="text" class="width30" id="page_inp" value="' . ($page < $pages ? $page + 1 : $page) . '" />&nbsp;';
        $output .= '<input type="button" name="��ת" class="tiaoz" onclick="window.location=\'' . $pageurl . '&pn=\'+document.getElementById(\'page_inp\').value+\'' . $anchor . '\'"  value="��ת"/>';
    }
    return $output;
}

function page_new($count = 0, $limit = 0, $page = 1, $pageurl = '', $anchor = '') {
    $pages = $count ? ceil($count / $limit) : 1;
    $output = '';

    $output .= '<ul class="pagination"> <li>��' . $count . '�� <em class="c-yel">' . $page . '</em>/' . $pages . ' </li></ul><ul class="pagination pagination-sm"> ';

    if ($pages > 1) {
        if ($page > 1) {
            $output .= '<li><a href="' . $pageurl . '&pn=1' . $anchor . '">��ҳ</a></li>';
            $output .= '<li><a href="' . $pageurl . '&pn=' . ($page > 1 ? $page - 1 : $page) . $anchor . '">��һҳ</a></li>';
        }

        if ($page < $pages) {
            $output .= '<li><a href="' . $pageurl . '&pn=' . ($page < $pages ? $page + 1 : $page) . $anchor . '">��һҳ</a></li>';
            $output .= '<li><a href="' . $pageurl . '&pn=' . $pages . $anchor . '">ĩҳ</a></li>';
        }

        $output .= ' <li><input    type="text" class="width30" id="page_inp" value="' . ($page < $pages ? $page + 1 : $page) . '" /> <li> ';
        $output .= ' <li><a  href="javascript:void(0);"  onclick="window.location=\'' . $pageurl . '&pn=\'+document.getElementById(\'page_inp\').value+\'' . $anchor . '\'" />��ת</a> </li></ul>';
    }
    return $output;
}

/**
 * �ж�ҵ���Ƿ����ύ����
 * @prjid int ��Ŀid
 * @scaletype int ҵ�����ͱ��(1���� 2����)
 *  */
function is_scale_have_summery($prjid, $scaletype) {
    $case_info = D("ProjectCase")->get_info_by_pid($prjid, $scaletype, array("ID"));
    if (!$case_info) {
        return false;
    } else {
        $case_id = $case_info[0]["ID"];
        //��ǰ�����ľ���״̬
        $final_accouns_info = D("Erp_finalaccounts")->where("CASE_ID=" . $case_id)->field("STATUS")->find();
        $final_status = $final_accouns_info["STATUS"];
        //ҵ���Ѿ�������ھ��������
        if ($final_status == 1 || $final_status == 2) {
            return true;
        } else {
            return false;
        }
    }
}

/*
 * �ж���Ŀ�ɱ��Ƿ��ѳ������ʶ��
 * @param int $case_id ����id
 * @param float $current_cost �������
 * return boolen ����|true δ����|false
 * **/

function is_overtop_payout_limit($case_id, $current_cost = 0 ,$case_sign = 0,$case_total = 0) {
    //�ж�ҵ������
    $scaletype = D("ProjectCase")->get_casetype_by_caseid($case_id);
    //����ҵ������
    $loan_case = D("ProjectCase")->get_conf_case_Loan();
    $case_type = D("ProjectCase")->get_conf_case_type();
    $scaletype = $case_type[$scaletype];

    //�ж��Ƿ���Ҫ�ߵ��ʱ��������߼�
    if (!in_array($scaletype, array_keys($loan_case)))
        return false;

    //���Ȼ�ȡ�Ƿ���Ҫ���ʱ�������
    $sql = "SELECT getloanmoneydif({$case_id},{$current_cost},{$case_total}) AMOUNT from dual";
    $result = M()->query($sql);

    if (is_array($result) && count($result)) {
        $amount = $result[0]['AMOUNT'];
    } else {
        return true;
    }

    //��Ʊ������ڵ���Ԥ��������
    //�������+����������  >  (��Ʊ����*���ֳɱ��ʣ�
    if ($amount == 0) {
        return true;
    } //�������+���������� < (��Ʊ����*���ֳɱ��ʣ�
    else if ($amount == 1) {
        return false;
    }

    //��Ʊ����С�ڵ���Ԥ��������
    if ($amount == 2) {

        //��ȡ�ѵ��ʱ���
        $loan_limit = D("ProjectCase")->getLoanMoney($case_id, $current_cost, 2 ,$case_sign);

        //����Ԥ����ʱ���
        $loan_limit_budget = D("Erp_prjbudget")->where("CASE_ID=" . $case_id)->field(array("PAYOUT"))->find();

        //����Ԥ��
        $loan_limit_budget = $loan_limit_budget["PAYOUT"] * 100;

        //�ɱ��������ʶ��
        if ($loan_limit > $loan_limit_budget) {
            return $loan_limit - $loan_limit_budget;
        } else {
            return false;
        }
    }

    return true;
}

/*
 * ���pos��������
 * @param ciyt_id����id
 * @param amount ���
 * return merchant pos�����
 * **/
function get_pos_fee($ciyt_id, $amount, $merchant) {
    $city = M('Erp_city')->where("ID=$ciyt_id")->find();
    if ($city) {
        $mer = M('Erp_merchant')->where("MERCHANT_NUMBER='$merchant'")->find();
        if ($mer['IS_LARGE'] == 1) {
            if ($city['LARGEDIFF']) {
                $fee = $amount <= $city['LARGEDIFF'] ? $city['LARGE1'] * $amount / 100 : $city['LARGE2'];

            } else $fee = $city['LARGE2'];
        } else $fee = $city['SMALLFEE'] * $amount / 100;
        if ($city['LIMITFEE']) $fee = $fee > $city['LIMITFEE'] ? $city['LIMITFEE'] : $fee;
    }

    return $fee;
}

/**
 * @param $cityid  ����ID
 * @param $api_addr    apiִ�е�ַ
 * @param int $api_state apiִ�е�״ֵ̬
 * @param $userid      �û�ID
 * @param $type   ����   1����ͬϵͳ   2 : CRM
 * @param array $api_data api����
 */
function api_log($cityid, $api_addr, $api_state = 0, $userid, $type, $api_data = array()) {
    $log = array();

    $log['CITY_ID'] = $cityid;
    $log['API_REQUEST'] = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
    $log['API_REQUEST'] = substr($log['API_REQUEST'],0,200);
    $log['API_ADDRESS'] = $api_addr;

    //���oracle & ��������
    $log['API_REQUEST'] = str_replace("&", "###", $log['API_REQUEST']);
    $log['API_ADDRESS'] = str_replace("&", "###", $log['API_ADDRESS']);

    if ($api_data)
        $log['API_PARAM'] = serialize($api_data);

    $log['DTIME'] = date("Y-m-d H:i:s");
    $log['IP'] = get_client_ip();
    $log['STATE'] = $api_state;
    $log['ADD_USER'] = $userid;
    $log['TYPE'] = $type;

    return M('erp_api_log')->add($log);
}

/**
 * ajax ���󷵻�json
 * @param int|string|number $data
 * @param string $info
 * @param int|number $status
 */
function ajaxJsonReturn($data, $info = '', $status = 1) {
    $result = array();
    $result['status'] = $status;
    $result['info'] = $info;
    $result['data'] = $data;
    // ����JSON���ݸ�ʽ���ͻ��� ����״̬��Ϣ
    header('Content-type: application/json');
    echo json_encode($result);
    exit();
}

function ajaxReturnJSON($status = 1, $msg = '', $data = null) {
    $result = array();

    if (mb_detect_encoding($msg,"UTF-8, ISO-8859-1, GBK") != "UTF-8") {
        $msg = g2u($msg);
        $data = g2u($data);
    }

    $result['status'] = $status;
    $result['msg'] = $msg;
    $result['data'] = $data;
    // ����JSON���ݸ�ʽ���ͻ��� ����״̬��Ϣ
    header('Content-type: application/json');
    echo json_encode($result);
    exit();
}



function send_result_to_zk($id_str, $channelid) {


    //��ȡ��Ҫ����������С�۷���������
    $model = D('PurchaseBeeDetails');
    $requestion = $model->where("ID in ($id_str) AND IS_BACK_TO_ZK=0 AND STATUS IN (2,3)")->select();
    if (!$requestion || empty($requestion)) {
        ajaxJsonReturn(false, '', 402);//û����Ҫ����������
    }
    //�ڿͽӿڵ�ַ
    $api = ZKAPI2;//http://zk.house365.com:8008/
    //��ȡ���м�ƴ
    $model_city = D('City');
    $city_id = intval($channelid);
    $city = $model_city->get_city_info_by_id($city_id);
    $citypy = strtolower($city["PY"]);
    //�������������ڿ�
    foreach ($requestion as $v) {
        $param = array(
            'p_id' => $v['P_ID'],
            'task_id' => $v['TASK_ID'],
            'supplier_id' => $v['SUPPLIER_ID'],
            'status' => $v['STATUS'],
            'city' => $citypy,
            'mark' => '',
            'key' => md5(md5($v['P_ID'] . $citypy) . "BEE"),
        );
        //��������
        $result = curlPost($api, $param);
        //����ʧ�ܷ��ش�����
        if (!$result || empty($result)) {
            return false;
        }
        $result = json_decode($result);
        if ($result->code == 200) {
            $model->where('ID=' . $v['ID'])->save(array('IS_BACK_TO_ZK' => 1));
        }
    }
    return true;
}

/**
 * ��ȡ��ǰҳ���URL
 * @return string
 */
function curPageURL() {
    $pageURL = 'http';

    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";

    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

/**
 * ��ȡ��ת��ַ
 * @param $curPageUrl
 * @return string
 */
function getForwardUrl($curPageUrl) {
    $forward = '';
    $pos = strripos($curPageUrl, 'beginforward');
    if ($pos !== false) {
        $forward = substr($curPageUrl, $pos + strlen('beginforward='));
        $pos = strripos($forward, 'endforward');
        if ($pos !== false) {
            $forward = substr($forward, 0, $pos - 1);
        }
    }

    return $forward;
}

/**
 * �Ƿ�Ϊ�ǿ�����
 * @param $arr
 * @return bool
 */
function notEmptyArray($arr) {
    return (is_array($arr) && count($arr));
}

/**
 * �Ƿ�Ϊ�ƶ��豸
 * @return bool
 */
function isMobile() {
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    if (isset($_SERVER['HTTP_VIA'])) {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    if (isset ($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp',
            'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu',
            'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave',
            'nexusone', 'cldc', 'midp', 'wap', 'mobile');
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

function createFolder($path) {
    if (!file_exists($path)) {
        createFolder(dirname($path));
        mkdir($path, 0777);
    }
}

function userLog() {
    global $userLog;
    if (!$userLog instanceof UserLog) {
        vendor('Oms.UserLog');
        $userLog = UserLog::init();
    }

    return $userLog;
}

function getFeeScaleAmount($caseId, $amount, $houseTotal, &$feeType = 0) {
    $response = 0;
    if (intval($caseId) > 0) {
        $feeone = M('Erp_feescale')->where("CASE_ID={$caseId} and AMOUNT='{$amount}' and ISVALID = -1")->find();
        if ($feeone['STYPE'] == 1) {
            $response = $houseTotal * $amount / 100;
            $feeType = 1;
        } else {
            $response = $amount;
            $feeType = 0;
        }
    }

    return round($response, 2);
}

/**
 * ���ݳ���ID��ȡ����ƴ��
 * @param $cityId
 * @return string
 */
function getCityPY($cityId) {
    $response = "";
    if (intval($cityId)) {
        $sql = sprintf("SELECT PY FROM ERP_CITY WHERE ID = %d", $cityId);
        $dbResult = D()->query($sql);
        if (notEmptyArray($dbResult)) {
            $response = strtolower($dbResult[0]['PY']);
        }
    }
    return $response;
}

//function getContractData($citypy, $contractnum, $action = '')
//{
//    //��ȡ��ͬ������Ϣ
//    $citypy = strip_tags($citypy);
//    $contractnum = strip_tags($contractnum);
//    $action = strip_tags($action);
//
//    $url = CONTRACT_API."get_ct_info.php?city=$citypy&contractnum=$contractnum&"
//        . "action=$action";
//    $data = curl_get_contents($url, 'get');
//    $data = unserialize($data);
//    return $data;
//}

function enableRecordReadOnly(&$form) {
    if (empty($form)) {
        return;
    }

    $form->EDITABLE = 0;  // ���ܱ༭
    $form->ADDABLE = 0;  // ��������
    $form->DELABLE = 0;  // ����ɾ��
}

function p($data = array()) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    die;
}

function getTotalMoney($list, $amountFieldName, $priceFieldName) {
    $response = 0;
    if (notEmptyArray($list)) {
        foreach ($list as $item) {
            $response += floatval($item[$amountFieldName] * $item[$priceFieldName]);
        }
    }

    return round($response, 2);
}

?>