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
// $Id: CheckRouteBehavior.class.php 2720 2012-02-08 13:32:58Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ???????? ¡¤????
 +------------------------------------------------------------------------------
 */
class CheckRouteBehavior extends Behavior {
    // ??????????‰Í?????? ????????????§Ú???
    protected $options   =  array(
        'URL_ROUTER_ON'         => false,   // ?????URL¡¤??
        'URL_ROUTE_RULES'       => array(), // ???¡¤??????????????????????
        );

    // ???????????????????run
    public function run(&$return){
        // ????????????PATH_INFO?????????????????
        $regx = trim($_SERVER['PATH_INFO'],'/');
        if(empty($regx)) return $return = true;// ?true??????????§Ø?
        // ?????¡¤?????
        if(!C('URL_ROUTER_ON')) return $return = false;// ???return????????????
        // ¡¤??????????????config?§Ö????????
        $routes = C('URL_ROUTE_RULES');
        // ¡¤?????
        if(!empty($routes)) {
            $depr = C('URL_PATHINFO_DEPR');
            // ?????I ???¡¤???????????????
            $regx = str_replace($depr,'/',$regx);
            $rules = array_keys($routes);
            foreach ($rules as $rule){
                if(0===strpos($rule,'/') && preg_match($rule,$regx,$matches)) { // ????¡¤??
                    return $return = $this->parseRegex($matches,$routes[$rule],$regx);
                }elseif(substr_count($regx,'/') >= substr_count($rule,'/')){ // ????¡¤??
                    $m1 = explode('/',$regx);
                    $m2 = explode('/',$rule);
                    $match = true; // ??????
                    foreach ($m2 as $key=>$val){
                        if(':' == substr($val,0,1)) {// ???????
                            if(strpos($val,'\\')) {
                                $type = substr($val,-1);
                                if('d'==$type && !is_numeric($m1[$key])) {
                                    $match = false;
                                    break;
                                }
                            }elseif(strpos($val,'^')){
                                $array   =  explode('|',substr(strstr($val,'^'),1));
                                if(in_array($m1[$key],$array)) {
                                    $match = false;
                                    break;
                                }
                            }
                        }elseif(0 !== strcasecmp($val,$m1[$key])){
                            $match = false;
                            break;
                        }
                    }
                    if($match)  return $return = $this->parseRule($rule,$routes[$rule],$regx);
                }
            }
        }
        $return = false;
    }

    // ?????œZ??¡¤????
    // ?????? [????/???/?????]????1=?1&????2=?2...
    private function parseUrl($url) {
        $var  =  array();
        if(false !== strpos($url,'?')) { // [????/???/?????]????1=?1&????2=?2...
            $info   =  parse_url($url);
            $path = explode('/',$info['path']);
            parse_str($info['query'],$var);
        }elseif(strpos($url,'/')){ // [????/???/????]
            $path = explode('/',$url);
        }else{ // ????1=?1&????2=?2...
            parse_str($url,$var);
        }
        if(isset($path)) {
            $var[C('VAR_ACTION')] = array_pop($path);
            if(!empty($path)) {
                $var[C('VAR_MODULE')] = array_pop($path);
            }
            if(!empty($path)) {
                $var[C('VAR_GROUP')]  = array_pop($path);
            }
        }
        return $var;
    }

    // ????????¡¤??
    // '¡¤?????'=>'[????/???/????]????????1=?1&???????2=?2...'
    // '¡¤?????'=>array('[????/???/????]','???????1=?1&???????2=?2...')
    // '¡¤?????'=>'?????'
    // '¡¤?????'=>array('?????','????????')
    // ¡¤??????? :??? ??????????
    // ??????§á??????????? ???? :1 :2 ????
    // 'news/:month/:day/:id'=>array('News/read?cate=1','status=1'),
    // 'new/:id'=>array('/new.php?id=:1',301), ?????
    private function parseRule($rule,$route,$regx) {
        // ???¡¤????????
        $url   =  is_array($route)?$route[0]:$route;
        // ???URL????§Ö????
        $paths = explode('/',$regx);
        // ????¡¤?????
        $matches  =  array();
        $rule =  explode('/',$rule);
        foreach ($rule as $item){
            if(0===strpos($item,':')) { // ??????????
                if($pos = strpos($item,'^') ) {
                    $var  =  substr($item,1,$pos-1);
                }elseif($pos = strpos($item,'\\')){
                    $var  =  substr($item,1,-2);
                }else{
                    $var  =  substr($item,1);
                }
                $matches[$var] = array_shift($paths);
            }else{ // ????URL?§Ö???????
                array_shift($paths);
            }
        }
        if(0=== strpos($url,'/') || 0===strpos($url,'http')) { // ¡¤??????????
            if(strpos($url,':')) { // ??????????
                $values  =  array_values($matches);
                $url  =  preg_replace('/:(\d)/e','$values[\\1-1]',$url);
            }
            header("Location: $url", true,(is_array($route) && isset($route[1]))?$route[1]:301);
            exit;
        }else{
            // ????¡¤????
            $var  =  $this->parseUrl($url);
            // ????¡¤???????????????
            $values  =  array_values($matches);
            foreach ($var as $key=>$val){
                if(0===strpos($val,':')) {
                    $var[$key] =  $values[substr($val,1)-1];
                }
            }
            $var   =   array_merge($matches,$var);
            // ????????URL????
            if($paths) {
                preg_replace('@(\w+)\/([^,\/]+)@e', '$var[strtolower(\'\\1\')]="\\2";', implode('/',$paths));
            }
            // ????¡¤????????????
            if(is_array($route) && isset($route[1])) {
                parse_str($route[1],$params);
                $var   =   array_merge($var,$params);
            }
            $_GET   =  array_merge($var,$_GET);
        }
        return true;
    }

    // ????????¡¤??
    // '¡¤??????'=>'[????/???/????]?????1=?1&????2=?2...'
    // '¡¤??????'=>array('[????/???/????]?????1=?1&????2=?2...','???????1=?1&???????2=?2...')
    // '¡¤??????'=>'?????'
    // '¡¤??????'=>array('?????','????????')
    // ?????????????§á??????????? ???? :1 :2 ????
    // '/new\/(\d+)\/(\d+)/'=>array('News/read?id=:1&page=:2&cate=1','status=1'),
    // '/new\/(\d+)/'=>array('/new.php?id=:1&page=:2&status=1','301'), ?????
    private function parseRegex($matches,$route,$regx) {
        // ???¡¤????????
        $url   =  is_array($route)?$route[0]:$route;
        $url   =  preg_replace('/:(\d)/e','$matches[\\1]',$url);
        if(0=== strpos($url,'/') || 0===strpos($url,'http')) { // ¡¤??????????
            header("Location: $url", true,(is_array($route) && isset($route[1]))?$route[1]:301);
            exit;
        }else{
            // ????¡¤????
            $var  =  $this->parseUrl($url);
            // ????????URL????
            $regx =  substr_replace($regx,'',0,strlen($matches[0]));
            if($regx) {
                preg_replace('@(\w+)\/([^,\/]+)@e', '$var[strtolower(\'\\1\')]="\\2";', $regx);
            }
            // ????¡¤????????????
            if(is_array($route) && isset($route[1])) {
                parse_str($route[1],$params);
                $var   =   array_merge($var,$params);
            }
            $_GET   =  array_merge($var,$_GET);
        }
        return true;
    }
}