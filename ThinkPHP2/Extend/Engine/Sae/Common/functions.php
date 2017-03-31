<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: functions.php 2821 2012-03-16 06:17:49Z luofei614@gmail.com $

/**
  +------------------------------------------------------------------------------
 * Think ��׼ģʽ����������
  +------------------------------------------------------------------------------
 * @category   Think
 * @package  Common
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id: functions.php 2821 2012-03-16 06:17:49Z luofei614@gmail.com $
  +------------------------------------------------------------------------------
 */

// �������
function halt($error) {
    $e = array();
    if (APP_DEBUG) {
        //����ģʽ�����������Ϣ
        if (!is_array($error)) {
            $trace = debug_backtrace();
            $e['message'] = $error;
            $e['file'] = $trace[0]['file'];
            $e['class'] = $trace[0]['class'];
            $e['function'] = $trace[0]['function'];
            $e['line'] = $trace[0]['line'];
            $traceInfo = '';
            $time = date('y-m-d H:i:m');
            foreach ($trace as $t) {
                $traceInfo .= '[' . $time . '] ' . $t['file'] . ' (' . $t['line'] . ') ';
                $traceInfo .= $t['class'] . $t['type'] . $t['function'] . '(';
                $traceInfo .= implode(', ', $t['args']);
                $traceInfo .=')<br/>';
            }
            $e['trace'] = $traceInfo;
        } else {
            $e = $error;
        }
        // �����쳣ҳ��ģ��
        include C('TMPL_EXCEPTION_FILE');
    } else {
        //�����򵽴���ҳ��
        $error_page = C('ERROR_PAGE');
        if (!empty($error_page)) {
            redirect($error_page);
        } else {
            if (C('SHOW_ERROR_MSG'))
                $e['message'] = is_array($error) ? $error['message'] : $error;
            else
                $e['message'] = C('ERROR_MESSAGE');
            // �����쳣ҳ��ģ��
            include C('TMPL_EXCEPTION_FILE');
        }
    }
    exit;
}

// �Զ����쳣����
function throw_exception($msg, $type='ThinkException', $code=0) {
    if (class_exists($type, false))
        throw new $type($msg, $code, true);
    else
        halt($msg);        // �쳣���Ͳ����������������Ϣ�ִ�
}

// ������Ѻõı������
function dump($var, $echo=true, $label=null, $strict=true) {
    $label = ($label === null) ? '' : rtrim($label) . ' ';
    if (!$strict) {
        if (ini_get('html_errors')) {
            $output = print_r($var, true);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        } else {
            $output = $label . print_r($var, true);
        }
    } else {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace("/\]\=\>\n(\s+)/m", '] => ', $output);
            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
        }
    }
    if ($echo) {
        echo($output);
        return null;
    }else
        return $output;
}

 // ������Կ�ʼ
function debug_start($label='') {
    $GLOBALS[$label]['_beginTime'] = microtime(TRUE);
    if (MEMORY_LIMIT_ON)
        $GLOBALS[$label]['_beginMem'] = memory_get_usage();
}

// ������Խ�������ʾָ����ǵ���ǰλ�õĵ���
function debug_end($label='') {
    $GLOBALS[$label]['_endTime'] = microtime(TRUE);
    echo '<div style="text-align:center;width:100%">Process ' . $label . ': Times ' . number_format($GLOBALS[$label]['_endTime'] - $GLOBALS[$label]['_beginTime'], 6) . 's ';
    if (MEMORY_LIMIT_ON) {
        $GLOBALS[$label]['_endMem'] = memory_get_usage();
        echo ' Memories ' . number_format(($GLOBALS[$label]['_endMem'] - $GLOBALS[$label]['_beginMem']) / 1024) . ' k';
    }
    echo '</div>';
}

// ���Ӻͻ�ȡҳ��Trace��¼
function trace($title='',$value='') {
    if(!C('SHOW_PAGE_TRACE')) return;
    static $_trace =  array();
    if(is_array($title)) { // ������ֵ
        $_trace   =  array_merge($_trace,$title);
    }elseif('' !== $value){ // ��ֵ
        $_trace[$title] = $value;
    }elseif('' !== $title){ // ȡֵ
        return $_trace[$title];
    }else{ // ��ȡȫ��Trace����
        return $_trace;
    }
}

// ���õ�ǰҳ��Ĳ���
function layout($layout) {
    if(false !== $layout) {
        // ��������
        C('LAYOUT_ON',true);
        if(is_string($layout)) {
            C('LAYOUT_NAME',$layout);
        }
    }
}

// URL��װ ֧�ֲ�ͬģʽ
// ��ʽ��U('[����/ģ��/����]?����','����','α��̬��׺','�Ƿ���ת','��ʾ����')
function U($url,$vars='',$suffix=true,$redirect=false,$domain=false) {
    // ����URL
    $info =  parse_url($url);
    $url   =  !empty($info['path'])?$info['path']:ACTION_NAME;
    // ����������
    if($domain===true){
        $domain = $_SERVER['HTTP_HOST'];
        if(C('APP_SUB_DOMAIN_DEPLOY') ) { // ��������������
            $domain = $domain=='localhost'?'localhost':'www'.strstr($_SERVER['HTTP_HOST'],'.');
            // '������'=>array('��Ŀ[/����]');
            foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule) {
                if(false === strpos($key,'*') && 0=== strpos($url,$rule[0])) {
                    $domain = $key.strstr($domain,'.'); // ���ɶ�Ӧ������
                    $url   =  substr_replace($url,'',0,strlen($rule[0]));
                    break;
                }
            }
        }
    }

    // ��������
    if(is_string($vars)) { // aaa=1&bbb=2 ת��������
        parse_str($vars,$vars);
    }elseif(!is_array($vars)){
        $vars = array();
    }
    if(isset($info['query'])) { // ������ַ������� �ϲ���vars
        parse_str($info['query'],$params);
        $vars = array_merge($params,$vars);
    }

    // URL��װ
    $depr = C('URL_PATHINFO_DEPR');
    if($url) {
        if(0=== strpos($url,'/')) {// ����·��
            $route   =  true;
            $url   =  substr($url,1);
            if('/' != $depr) {
                $url   =  str_replace('/',$depr,$url);
            }
        }else{
            if('/' != $depr) { // ��ȫ�滻
                $url   =  str_replace('/',$depr,$url);
            }
            // �������顢ģ��Ͳ���
            $url   =  trim($url,$depr);
            $path = explode($depr,$url);
            $var  =  array();
            $var[C('VAR_ACTION')] = !empty($path)?array_pop($path):ACTION_NAME;
            $var[C('VAR_MODULE')] = !empty($path)?array_pop($path):MODULE_NAME;
            if(C('URL_CASE_INSENSITIVE')) {
                $var[C('VAR_MODULE')] =  parse_name($var[C('VAR_MODULE')]);
            }
            if(C('APP_GROUP_LIST')) {
                if(!empty($path)) {
                    $group   =  array_pop($path);
                    $var[C('VAR_GROUP')]  =   $group;
                }else{
                    if(GROUP_NAME != C('DEFAULT_GROUP')) {
                        $var[C('VAR_GROUP')]  =   GROUP_NAME;
                    }
                }
            }
        }
    }

    if(C('URL_MODEL') == 0) { // ��ͨģʽURLת��
        $url   =  __APP__.'?'.http_build_query($var);
        if(!empty($vars)) {
            $vars = http_build_query($vars);
            $url   .= '&'.$vars;
        }
    }else{ // PATHINFOģʽ���߼���URLģʽ
        if(isset($route)) {
            $url   =  __APP__.'/'.$url;
        }else{
            $url   =  __APP__.'/'.implode($depr,array_reverse($var));
        }
        if(!empty($vars)) { // ���Ӳ���
            $vars = http_build_query($vars);
            $url .= $depr.str_replace(array('=','&'),$depr,$vars);
        }
        if($suffix) {
            $suffix   =  $suffix===true?C('URL_HTML_SUFFIX'):$suffix;
            if($suffix) {
                $url  .=  '.'.ltrim($suffix,'.');
            }
        }
    }
    if($domain) {
        $url   =  'http://'.$domain.$url;
    }
    if($redirect) // ֱ����תURL
        redirect($url);
    else
        return $url;
}

// URL�ض���
function redirect($url, $time=0, $msg='') {
    //����URL��ַ֧��
    $url = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg))
        $msg = "ϵͳ����{$time}��֮���Զ���ת��{$url}��";
    if (!headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo($msg);
        }
        exit();
    } else {
        $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}

// ȫ�ֻ������úͶ�ȡ
//[sae] ��sae��S����̶���memcacheʵ�֡�
function S($name, $value='', $expire=0, $type='', $options=null) {
    static $_cache = array();
    static $mc;
    //ȡ�û������ʵ��
    if (!is_object($mc)) {
        $mc = memcache_init();
    }
    if ('' !== $value) {
        if (is_null($value)) {
            // ɾ������
            $result = $mc->delete($_SERVER['HTTP_APPVERSION'] . '/' . $name);
            if ($result)
                unset($_cache[$name]);
            return $result;
        }else {
            // ��������
            $mc->set($_SERVER['HTTP_APPVERSION'] . '/' . $name, $value, MEMCACHE_COMPRESSED, $expire);
            $_cache[$name] = $value;
            //[sae]  ʵ���ж�
            if (!is_null($options['length']) && $options['length'] > 0) {
                $queue = F('think_queue');
                if (!$queue) {
                    $queue = array();
                }
                array_push($queue, $name);
                if (count($queue) > $options['length']) {
                    $key = array_shift($queue);
                    $mc->delete($key);
                    //[sae] �ڵ���ģʽ�£�ͳ�Ƴ��Ӵ���
                    if (APP_DEBUG) {
                        $counter = Think::instance('SaeCounter');
                        if ($counter->exists('think_queue_out_times'))
                            $counter->incr('think_queue_out_times');
                        else
                            $counter->create('think_queue_out_times', 1);
                    }
                }
                F('think_queue', $queue);
            }
        }
        return;
    }
    if (isset($_cache[$name]))
        return $_cache[$name];
    // ��ȡ��������
    $value = $mc->get($_SERVER['HTTP_APPVERSION'] . '/' . $name);
    $_cache[$name] = $value;
    return $value;
}

// �����ļ����ݶ�ȡ�ͱ��� ��Լ��������� �ַ���������
//[sae] ��sae��F����ʹ��KVDBʵ��
function F($name, $value='', $path=DATA_PATH) {
    //saeʹ��KVDBʵ��F����
    static $_cache = array();
    static $kv;
    if (!is_object($kv)) {
        $kv = Think::instance('SaeKVClient');
        if(!$kv->init()) halt('��û�г�ʼ��KVDB������SAEƽ̨���г�ʼ��');
    }
    if ('' !== $value) {
        if (is_null($value)) {
            // ɾ������
            return $kv->delete($_SERVER['HTTP_APPVERSION'] . '/' . $name);
        } else {
            return $kv->set($_SERVER['HTTP_APPVERSION'] . '/' . $name, $value);
        }
    }
    if (isset($_cache[$name]))
        return $_cache[$name];
    // ��ȡ��������
    $value = $kv->get($_SERVER['HTTP_APPVERSION'] . '/' . $name);
    return $value;
}
// ȡ�ö���ʵ�� ֧�ֵ�����ľ�̬����
function get_instance_of($name, $method='', $args=array()) {
    static $_instance = array();
    $identify = empty($args) ? $name . $method : $name . $method . to_guid_string($args);
    if (!isset($_instance[$identify])) {
        if (class_exists($name)) {
            $o = new $name();
            if (method_exists($o, $method)) {
                if (!empty($args)) {
                    $_instance[$identify] = call_user_func_array(array(&$o, $method), $args);
                } else {
                    $_instance[$identify] = $o->$method();
                }
            }
            else
                $_instance[$identify] = $o;
        }
        else
            halt(L('_CLASS_NOT_EXIST_') . ':' . $name);
    }
    return $_instance[$identify];
}

// ����PHP�������ͱ�������Ψһ��ʶ��
function to_guid_string($mix) {
    if (is_object($mix) && function_exists('spl_object_hash')) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}

// xml����
function xml_encode($data, $encoding='utf-8', $root='think') {
    $xml = '<?xml version="1.0" encoding="' . $encoding . '"?>';
    $xml.= '<' . $root . '>';
    $xml.= data_to_xml($data);
    $xml.= '</' . $root . '>';
    return $xml;
}

function data_to_xml($data) {
    $xml = '';
    foreach ($data as $key => $val) {
        is_numeric($key) && $key = "item id=\"$key\"";
        $xml.="<$key>";
        $xml.= ( is_array($val) || is_object($val)) ? data_to_xml($val) : $val;
        list($key, ) = explode(' ', $key);
        $xml.="</$key>";
    }
    return $xml;
}

// session��������
function session($name,$value='') {
    $prefix   =  C('SESSION_PREFIX');
    if(is_array($name)) { // session��ʼ�� ��session_start ֮ǰ����
        if(isset($name['prefix'])) C('SESSION_PREFIX',$name['prefix']);
        if(isset($_REQUEST[C('VAR_SESSION_ID')])){
            session_id($_REQUEST[C('VAR_SESSION_ID')]);
        }elseif(isset($name['id'])) {
            session_id($name['id']);
        }
        //ini_set('session.auto_start', 0);//[sae] ��saeƽ̨��������
        if(isset($name['name'])) session_name($name['name']);
        if(isset($name['path'])) session_save_path($name['path']);
        if(isset($name['domain'])) ini_set('session.cookie_domain', $name['domain']);
        if(isset($name['expire'])) ini_set('session.gc_maxlifetime', $name['expire']);
        if(isset($name['use_trans_sid'])) ini_set('session.use_trans_sid', $name['use_trans_sid']?1:0);
        if(isset($name['use_cookies'])) ini_set('session.use_cookies', $name['use_cookies']?1:0);
        if(isset($name['type'])) C('SESSION_TYPE',$name['type']);
        if(C('SESSION_TYPE')) { // ��ȡsession����
            $class = 'Session'. ucwords(strtolower(C('SESSION_TYPE')));
            // ���������
            if(require_cache(EXTEND_PATH.'Driver/Session/'.$class.'.class.php')) {
                $hander = new $class();
                $hander->execute();
            }else {
                // ��û�ж���
                throw_exception(L('_CLASS_NOT_EXIST_').': ' . $class);
            }
        }
        // ����session
        if(C('SESSION_AUTO_START'))  session_start();
    }elseif('' === $value){ 
        if(0===strpos($name,'[')) { // session ����
            if('[pause]'==$name){ // ��ͣsession
                session_write_close();
            }elseif('[start]'==$name){ // ����session
                session_start();
            }elseif('[destroy]'==$name){ // ����session
                $_SESSION =  array();
                session_unset();
                session_destroy();
            }elseif('[regenerate]'==$name){ // ��������id
                session_regenerate_id();
            }
        }elseif(0===strpos($name,'?')){ // ���session
            $name   =  substr($name,1);
            if($prefix) {
                return isset($_SESSION[$prefix][$name]);
            }else{
                return isset($_SESSION[$name]);
            }
        }elseif(is_null($name)){ // ���session
            if($prefix) {
                unset($_SESSION[$prefix]);
            }else{
                $_SESSION = array();
            }
        }elseif($prefix){ // ��ȡsession
            return $_SESSION[$prefix][$name];
        }else{
            return $_SESSION[$name];
        }
    }elseif(is_null($value)){ // ɾ��session
        if($prefix){
            unset($_SESSION[$prefix][$name]);
        }else{
            unset($_SESSION[$name]);
        }
    }else{ // ����session
        if($prefix){
            if (!is_array($_SESSION[$prefix])) {
                $_SESSION[$prefix] = array();
            }
            $_SESSION[$prefix][$name]   =  $value;
        }else{
            $_SESSION[$name]  =  $value;
        }
    }
}

// Cookie ���á���ȡ��ɾ��
function cookie($name, $value='', $option=null) {
    // Ĭ������
    $config = array(
        'prefix' => C('COOKIE_PREFIX'), // cookie ����ǰ׺
        'expire' => C('COOKIE_EXPIRE'), // cookie ����ʱ��
        'path' => C('COOKIE_PATH'), // cookie ����·��
        'domain' => C('COOKIE_DOMAIN'), // cookie ��Ч����
    );
    // ��������(�Ḳ���a������)
    if (!empty($option)) {
        if (is_numeric($option))
            $option = array('expire' => $option);
        elseif (is_string($option))
            parse_str($option, $option);
        $config = array_merge($config, array_change_key_case($option));
    }
    // ���ָ��ǰ׺������cookie
    if (is_null($name)) {
        if (empty($_COOKIE))
            return;
        // Ҫɾ����cookieǰ׺����ָ����ɾ��config���õ�ָ��ǰ׺
        $prefix = empty($value) ? $config['prefix'] : $value;
        if (!empty($prefix)) {// ���ǰ׺Ϊ���ַ�������������ֱ�ӷ���
            foreach ($_COOKIE as $key => $val) {
                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], $config['domain']);
                    unset($_COOKIE[$key]);
                }
            }
        }
        return;
    }
    $name = $config['prefix'] . $name;
    if ('' === $value) {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null; // ��ȡָ��Cookie
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], $config['domain']);
            unset($_COOKIE[$name]); // ɾ��ָ��cookie
        } else {
            // ����cookie
            $expire = !empty($config['expire']) ? time() + intval($config['expire']) : 0;
            setcookie($name, $value, $expire, $config['path'], $config['domain']);
            $_COOKIE[$name] = $value;
        }
    }
}

// ������չ�����ļ�
function load_ext_file() {
    // �����Զ����ⲿ�ļ�
    if(C('LOAD_EXT_FILE')) {
        $files =  explode(',',C('LOAD_EXT_FILE'));
        foreach ($files as $file){
            $file   = COMMON_PATH.$file.'.php';
            if(is_file($file)) include $file;
        }
    }
    // �����Զ���Ķ�̬�����ļ�
    if(C('LOAD_EXT_CONFIG')) {
        $configs =  C('LOAD_EXT_CONFIG');
        if(is_string($configs)) $configs =  explode(',',$configs);
        foreach ($configs as $key=>$config){
            $file   = CONF_PATH.$config.'.php';
            if(is_file($file)) {
                is_numeric($key)?C(include $file):C($key,include $file);
            }
        }
    }
}

// ��ȡ�ͻ���IP��ַ
function get_client_ip() {
    static $ip = NULL;
    if ($ip !== NULL) return $ip;
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos =  array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip   =  trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    // IP��ַ�Ϸ���֤
    $ip = (false !== ip2long($ip)) ? $ip : '0.0.0.0';
    return $ip;
}

function send_http_status($code) {
    static $_status = array(
        // Success 2xx
        200 => 'OK',
        // Redirection 3xx
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',  // 1.1
        // Client Error 4xx
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        // Server Error 5xx
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    );
    if(isset($_status[$code])) {
        header('HTTP/1.1 '.$code.' '.$_status[$code]);
        // ȷ��FastCGIģʽ������
        header('Status:'.$code.' '.$_status[$code]);
    }
}