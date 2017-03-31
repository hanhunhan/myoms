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
// $Id: common.php 2702 2012-02-02 12:35:01Z liu21st $

/**
  +------------------------------------------------------------------------------
 * Think ??????????
  +------------------------------------------------------------------------------
 * @category   Think
 * @package  Common
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id: common.php 2702 2012-02-02 12:35:01Z liu21st $
  +------------------------------------------------------------------------------
 */

// ??????????????
function G($start,$end='',$dec=4) {
    static $_info = array();
    if(is_float($end)) { // ??????
        $_info[$start]  =  $end;
    }elseif(!empty($end)){ // ??????
        if(!isset($_info[$end])) $_info[$end]   =  microtime(TRUE);
        return number_format(($_info[$end]-$_info[$start]),$dec);
    }else{ // ??????
        $_info[$start]  =  microtime(TRUE);
    }
}

// ???¨²??????????
function N($key, $step=0) {
    static $_num = array();
    if (!isset($_num[$key])) {
        $_num[$key] = 0;
    }
    if (empty($step))
        return $_num[$key];
    else
        $_num[$key] = $_num[$key] + (int) $step;
}

/**
  +----------------------------------------------------------
 * ???????????????
 * type
 * =0 ??Java???????C????
 * =1 ??C???????Java????
  +----------------------------------------------------------
 * @access protected
  +----------------------------------------------------------
 * @param string $name ?????
 * @param integer $type ???????
  +----------------------------------------------------------
 * @return string
  +----------------------------------------------------------
 */
function parse_name($name, $type=0) {
    if ($type) {
        return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
    } else {
        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}

// ?????require_once
function require_cache($filename) {
    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (file_exists_case($filename)) {
            require $filename;
            $_importFiles[$filename] = true;
        } else {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}

// ?????§³§Õ??????????§Ø?
function file_exists_case($filename) {
    if (is_file($filename)) {
        if (IS_WIN && C('APP_FILE_CASE')) {
            if (basename(realpath($filename)) != basename($filename))
                return false;
        }
        return true;
    }
    return false;
}

/**
  +----------------------------------------------------------
 * ???????????? ?java??Import
 * ???????§Ý??œ¤??
  +----------------------------------------------------------
 * @param string $class ???????????????
 * @param string $baseUrl ???¡¤??
 * @param string $ext ?????????????
  +----------------------------------------------------------
 * @return boolen
  +----------------------------------------------------------
 */
function import($class, $baseUrl = '', $ext='.class.php') {
    static $_file = array();
    $class = str_replace(array('.', '#'), array('/', '.'), $class);
    if ('' === $baseUrl && false === strpos($class, '/')) {
        // ??????????
        return alias_import($class);
    }
    if (isset($_file[$class . $baseUrl]))
        return true;
    else
        $_file[$class . $baseUrl] = true;
    $class_strut = explode("/", $class);
    if (empty($baseUrl)) {
        if ('@' == $class_strut[0] || APP_NAME == $class_strut[0]) {
            //???????????????
            $baseUrl = dirname(LIB_PATH);
            $class = substr_replace($class, basename(LIB_PATH).'/', 0, strlen($class_strut[0]) + 1);
        }elseif ('think' == strtolower($class_strut[0])){ // think ????????
            $baseUrl = CORE_PATH;
            $class = substr($class,6);
        }elseif (in_array(strtolower($class_strut[0]), array('org', 'com'))) {
            // org ????????????? com ??????????
            $baseUrl = LIBRARY_PATH;
        }else { // ?????????????????
            $class = substr_replace($class, '', 0, strlen($class_strut[0]) + 1);
            $baseUrl = APP_PATH . '../' . $class_strut[0] . '/'.basename(LIB_PATH).'/';
        }
    }
    if (substr($baseUrl, -1) != "/")
        $baseUrl .= "/";
    $classfile = $baseUrl . $class . $ext;
    if (!class_exists(basename($class),false)) {
        // ????????? ??????????
        return require_cache($classfile);
    }
}

/**
  +----------------------------------------------------------
 * ????????????????????
 * load('@.Util.Array')
  +----------------------------------------------------------
 * @param string $name ??????????????????
 * @param string $baseUrl ???¡¤??
 * @param string $ext ?????????????
  +----------------------------------------------------------
 * @return void
  +----------------------------------------------------------
 */
function load($name, $baseUrl='', $ext='.php') {
    $name = str_replace(array('.', '#'), array('/', '.'), $name);
    if (empty($baseUrl)) {
        if (0 === strpos($name, '@/')) {
            //???????????????
            $baseUrl = COMMON_PATH;
            $name = substr($name, 2);
        } else {
            //????ThinkPHP ????????
            $baseUrl = EXTEND_PATH . 'Function/';
        }
    }
    if (substr($baseUrl, -1) != "/")
        $baseUrl .= "/";
    require_cache($baseUrl . $name . $ext);
}

// ??????????????????
// ???§Ö???????????????????? ????Vendor??????
// ????????????.php???????
function vendor($class, $baseUrl = '', $ext='.php') {
    if (empty($baseUrl))
        $baseUrl = VENDOR_PATH;
    return import($class, $baseUrl, $ext);
}

// ??????????????
function alias_import($alias, $classfile='') {
    static $_alias = array();
    if (is_string($alias)) {
        if(isset($_alias[$alias])) {
            return require_cache($_alias[$alias]);
        }elseif ('' !== $classfile) {
            // ???????????
            $_alias[$alias] = $classfile;
            return;
        }
    }elseif (is_array($alias)) {
        $_alias   =  array_merge($_alias,$alias);
        return;
    }
    return false;
}

/**
  +----------------------------------------------------------
 * D?????????????Model ??? ???://????/???
 +----------------------------------------------------------
 * @param string name Model??????
  +----------------------------------------------------------
 * @return Model
  +----------------------------------------------------------
 */
function D($name='') {
    if(empty($name)) return new Model;
    static $_model = array();
    if(isset($_model[$name]))
        return $_model[$name];
    if(strpos($name,'://')) {// ??????
        $name   =  str_replace('://','/Model/',$name);
    }else{
        $name   =  C('DEFAULT_APP').'/Model/'.$name;
    }
    import($name.'Model');
    $class   =   basename($name.'Model');
    if(class_exists($class)) {
        $model = new $class();
    }else {
        $model  = new Model(basename($name));
    }
    $_model[$name]  =  $model;
    return $model;
}

/**
  +----------------------------------------------------------
 * M???????????????????????????Model
  +----------------------------------------------------------
 * @param string name Model???? ????????????? ???? MongoModel:User
 * @param string tablePrefix ????
 * @param mixed $connection ????????????
  +----------------------------------------------------------
 * @return Model
  +----------------------------------------------------------
 */
function M($name='', $tablePrefix='',$connection='') {
    static $_model = array();
    if(strpos($name,':')) {
        list($class,$name)    =  explode(':',$name);
    }else{
        $class   =   'Model';
    }
    if (!isset($_model[$name . '_' . $class]))
        $_model[$name . '_' . $class] = new $class($name,$tablePrefix,$connection);
    return $_model[$name . '_' . $class];
}

/**
  +----------------------------------------------------------
 * A?????????????Action ????????://????/????
  +----------------------------------------------------------
 * @param string name Action??????
  +----------------------------------------------------------
 * @return Action
  +----------------------------------------------------------
 */
function A($name) {
    static $_action = array();
    if(isset($_action[$name]))
        return $_action[$name];
    if(strpos($name,'://')) {// ??????
        $name   =  str_replace('://','/Action/',$name);
    }else{
        $name   =  '@/Action/'.$name;
    }
    import($name.'Action');
    $class   =   basename($name.'Action');
    if(class_exists($class,false)) {
        $action = new $class();
        $_action[$name]  =  $action;
        return $action;
    }else {
        return false;
    }
}

// ?????????????????
// URL ??????? [???://][????/]???/???? 
function R($url,$vars=array()) {
    $info =  pathinfo($url);
    $action  =  $info['basename'];
    $module =  $info['dirname'];
    $class = A($module);
    if($class)
        return call_user_func_array(array(&$class,$action),$vars);
    else
        return false;
}

// ????????????????(???????§³§Õ)
function L($name=null, $value=null) {
    static $_lang = array();
    // ????????????§Ø???
    if (empty($name))
        return $_lang;
    // ?§Ø???????(??????)
    // ???????,?????????§Õ$name
    if (is_string($name)) {
        $name = strtoupper($name);
        if (is_null($value))
            return isset($_lang[$name]) ? $_lang[$name] : $name;
        $_lang[$name] = $value; // ???????
        return;
    }
    // ????????
    if (is_array($name))
        $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
    return;
}

// ????????
function C($name=null, $value=null) {
    static $_config = array();
    // ?????????????
    if (empty($name))
        return $_config;
    // ????????????????
    if (is_string($name)) {
        $name = strtolower($name);
        if (!strpos($name, '.')) {
            if (is_null($value))
                return isset($_config[$name]) ? $_config[$name] : null;
            $_config[$name] = is_array($value)?array_change_key_case($value):$value;
            return;
        }
        // ??????????¨²??????
        $name = explode('.', $name);
        if (is_null($value))
            return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
        $_config[$name[0]][$name[1]] = $value;
        return;
    }
    // ????????
    if (is_array($name)){
        foreach ($name as $key=>$val){
            if(is_array($val)) {
                $name[$key]  =  array_change_key_case($val);
            }
        }
        return $_config = array_merge($_config, array_change_key_case($name));
    }
    return null; // ??????????
}

// ?????????
function tag($tag, &$params=NULL) {
    // ????????
    $extends = C('extends.' . $tag);
    // ????????
    $tags = C('tags.' . $tag);
    if (!empty($tags)) {
        if(empty($tags['_overlay']) && !empty($extends)) { // ??????
            $tags = array_unique(array_merge($extends,$tags));
        }elseif(isset($tags['_overlay'])){ // ??????? '_overlay'=>1 ?????????
            unset($tags['_overlay']);
        }
    }elseif(!empty($extends)) {
        $tags = $extends;
    }
    if($tags) {
        if(APP_DEBUG) {
            G($tag.'Start');
            Log::record('Tag[ '.$tag.' ] --START--',Log::DEBUG);
        }
        // ??????
        foreach ($tags as $key=>$name) {
            if(!is_int($key)) { // ?????????????¡¤?? ?????????
                $name   = $key;
            }
            B($name, $params);
        }
        if(APP_DEBUG) { // ??????????????
            Log::record('Tag[ '.$tag.' ] --END-- [ RunTime:'.G($tag.'Start',$tag.'End',6).'s ]',Log::DEBUG);
        }
    }else{ // ¦Ä????¦Ê???? ????false
        return false;
    }
}

// ????????????????????
function add_tag_behavior($tag,$behavior,$path='') {
    $array   =  C('tags.'.$tag);
    if(!$array) {
        $array   =  array();
    }
    if($path) {
        $array[$behavior] = $path;
    }else{
        $array[] =  $behavior;
    }
    C('tags.'.$tag,$array);
}

// ??????????
function filter($name, &$content) {
    $class = $name . 'Filter';
    require_cache(LIB_PATH . 'Filter/' . $class . '.class.php');
    $filter = new $class();
    $content = $filter->run($content);
}

// ??????
function B($name, &$params=NULL) {
    $class = $name.'Behavior';
    G('behaviorStart');
    $behavior = new $class();
    $behavior->run($params);
    if(APP_DEBUG) { // ??????????????
        G('behaviorEnd');
        Log::record('Run '.$name.' Behavior [ RunTime:'.G('behaviorStart','behaviorEnd',6).'s ]',Log::DEBUG);
    }
}

// ??????Widget
function W($name, $data=array(), $return=false) {
    $class = $name . 'Widget';
    require_cache(LIB_PATH . 'Widget/' . $class . '.class.php');
    if (!class_exists($class))
        throw_exception(L('_CLASS_NOT_EXIST_') . ':' . $class);
    $widget = Think::instance($class);
    $content = $widget->render($data);
    if ($return)
        return $content;
    else
        echo $content;
}

// ????????§Ö???????
function strip_whitespace($content) {
    $stripStr = '';
    //????php???
    $tokens = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                //???????PHP???
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                //??????
                case T_WHITESPACE:
                    if (!$last_space) {
                        $stripStr .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<THINK\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "THINK;\n";
                    for($k = $i+1; $k < $j; $k++) {
                        if(is_string($tokens[$k]) && $tokens[$k] == ";") {
                            $i = $k;
                            break;
                        } else if($tokens[$k][0] == T_CLOSE_TAG) {
                            break;
                        }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

// ?????????
function mk_dir($dir, $mode = 0777) {
    if (is_dir($dir) || @mkdir($dir, $mode))
        return true;
    if (!mk_dir(dirname($dir), $mode))
        return false;
    return @mkdir($dir, $mode);
}

//[RUNTIME]
// ???????
function compile($filename) {
    $content = file_get_contents($filename);
    // ?I????????
    $content = preg_replace('/\/\/\[RUNTIME\](.*?)\/\/\[\/RUNTIME\]/s', '', $content);
    $content = substr(trim($content), 5);
    if ('?>' == substr($content, -2))
        $content = substr($content, 0, -2);
    return $content;
}

// ???????????????????
function array_define($array,$check=true) {
    $content = "\n";
    foreach ($array as $key => $val) {
        $key = strtoupper($key);
        if($check)   $content .= 'if(!defined(\'' . $key . '\')) ';
        if (is_int($val) || is_float($val)) {
            $content .= "define('" . $key . "'," . $val . ");";
        } elseif (is_bool($val)) {
            $val = ($val) ? 'true' : 'false';
            $content .= "define('" . $key . "'," . $val . ");";
        } elseif (is_string($val)) {
            $content .= "define('" . $key . "','" . addslashes($val) . "');";
        }
        $content    .= "\n";
    }
    return $content;
}
//[/RUNTIME]