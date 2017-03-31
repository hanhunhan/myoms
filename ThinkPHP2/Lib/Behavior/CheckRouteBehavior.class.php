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
// $Id: CheckRouteBehavior.class.php 2840 2012-03-23 05:56:20Z liu21st@gmail.com $

/**
 +------------------------------------------------------------------------------
 * ϵͳ��Ϊ��չ ·�ɼ��
 +------------------------------------------------------------------------------
 */
class CheckRouteBehavior extends Behavior {
    // ��Ϊ�������壨Ĭ��ֵ�� ������Ŀ�����и���
    protected $options   =  array(
        'URL_ROUTER_ON'         => false,   // �Ƿ���URL·��
        'URL_ROUTE_RULES'       => array(), // Ĭ��·�ɹ���ע�����������޷����
        );

    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$return){
        // ���ȼ���Ƿ����PATH_INFO
        $regx = trim($_SERVER['PATH_INFO'],'/');
        if(empty($regx)) return $return = true;
        // �Ƿ���·��ʹ��
        if(!C('URL_ROUTER_ON')) return $return = false;
        // ·�ɶ����ļ�������config�е����ö���
        $routes = C('URL_ROUTE_RULES');
        // ·�ɴ���
        if(!empty($routes)) {
            $depr = C('URL_PATHINFO_DEPR');
            // �ָ����滻 ȷ��·�ɶ���ʹ��ͳһ�ķָ���
            $regx = str_replace($depr,'/',$regx);
            foreach ($routes as $rule=>$route){
                if(0===strpos($rule,'/') && preg_match($rule,$regx,$matches)) { // ����·��
                    return $return = $this->parseRegex($matches,$route,$regx);
                }else{ // ����·��
                    $len1=   substr_count($regx,'/');
                    $len2 =  substr_count($rule,'/');
                    if($len1>=$len2) {
                        if('$' == substr($rule,-1,1)) {// ����ƥ��
                            if($len1 != $len2) {
                                continue;
                            }else{
                                $rule =  substr($rule,0,-1);
                            }
                        }
                        $match  =  $this->checkUrlMatch($regx,$rule);
                        if($match)  return $return = $this->parseRule($rule,$route,$regx);
                    }
                }
            }
        }
        $return = false;
    }

    // ���URL�͹���·���Ƿ�ƥ��
    private function checkUrlMatch($regx,$rule) {
        $m1 = explode('/',$regx);
        $m2 = explode('/',$rule);
        $match = true; // �Ƿ�ƥ��
        foreach ($m2 as $key=>$val){
            if(':' == substr($val,0,1)) {// ��̬����
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
        return $match;
    }

    // �����淶��·�ɵ�ַ
    // ��ַ��ʽ [����/ģ��/����?]����1=ֵ1&����2=ֵ2...
    private function parseUrl($url) {
        $var  =  array();
        if(false !== strpos($url,'?')) { // [����/ģ��/����?]����1=ֵ1&����2=ֵ2...
            $info   =  parse_url($url);
            $path = explode('/',$info['path']);
            parse_str($info['query'],$var);
        }elseif(strpos($url,'/')){ // [����/ģ��/����]
            $path = explode('/',$url);
        }else{ // ����1=ֵ1&����2=ֵ2...
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

    // ��������·��
    // '·�ɹ���'=>'[����/ģ��/����]?�������1=ֵ1&�������2=ֵ2...'
    // '·�ɹ���'=>array('[����/ģ��/����]','�������1=ֵ1&�������2=ֵ2...')
    // '·�ɹ���'=>'�ⲿ��ַ'
    // '·�ɹ���'=>array('�ⲿ��ַ','�ض������')
    // ·�ɹ����� :��ͷ ��ʾ��̬����
    // �ⲿ��ַ�п����ö�̬���� ���� :1 :2 �ķ�ʽ
    // 'news/:month/:day/:id'=>array('News/read?cate=1','status=1'),
    // 'new/:id'=>array('/new.php?id=:1',301), �ض���
    private function parseRule($rule,$route,$regx) {
        // ��ȡ·�ɵ�ַ����
        $url   =  is_array($route)?$route[0]:$route;
        // ��ȡURL��ַ�еĲ���
        $paths = explode('/',$regx);
        // ����·�ɹ���
        $matches  =  array();
        $rule =  explode('/',$rule);
        foreach ($rule as $item){
            if(0===strpos($item,':')) { // ��̬������ȡ
                if($pos = strpos($item,'^') ) {
                    $var  =  substr($item,1,$pos-1);
                }elseif(strpos($item,'\\')){
                    $var  =  substr($item,1,-2);
                }else{
                    $var  =  substr($item,1);
                }
                $matches[$var] = array_shift($paths);
            }else{ // ����URL�еľ�̬����
                array_shift($paths);
            }
        }
        if(0=== strpos($url,'/') || 0===strpos($url,'http')) { // ·���ض�����ת
            if(strpos($url,':')) { // ���ݶ�̬����
                $values  =  array_values($matches);
                $url  =  preg_replace('/:(\d)/e','$values[\\1-1]',$url);
            }
            header("Location: $url", true,(is_array($route) && isset($route[1]))?$route[1]:301);
            exit;
        }else{
            // ����·�ɵ�ַ
            $var  =  $this->parseUrl($url);
            // ����·�ɵ�ַ����Ķ�̬����
            $values  =  array_values($matches);
            foreach ($var as $key=>$val){
                if(0===strpos($val,':')) {
                    $var[$key] =  $values[substr($val,1)-1];
                }
            }
            $var   =   array_merge($matches,$var);
            // ����ʣ���URL����
            if($paths) {
                preg_replace('@(\w+)\/([^,\/]+)@e', '$var[strtolower(\'\\1\')]=strip_tags(\'\\2\');', implode('/',$paths));
            }
            // ����·���Զ����˲���
            if(is_array($route) && isset($route[1])) {
                parse_str($route[1],$params);
                $var   =   array_merge($var,$params);
            }
            $_GET   =  array_merge($var,$_GET);
        }
        return true;
    }

    // ��������·��
    // '·������'=>'[����/ģ��/����]?����1=ֵ1&����2=ֵ2...'
    // '·������'=>array('[����/ģ��/����]?����1=ֵ1&����2=ֵ2...','�������1=ֵ1&�������2=ֵ2...')
    // '·������'=>'�ⲿ��ַ'
    // '·������'=>array('�ⲿ��ַ','�ض������')
    // ����ֵ���ⲿ��ַ�п����ö�̬���� ���� :1 :2 �ķ�ʽ
    // '/new\/(\d+)\/(\d+)/'=>array('News/read?id=:1&page=:2&cate=1','status=1'),
    // '/new\/(\d+)/'=>array('/new.php?id=:1&page=:2&status=1','301'), �ض���
    private function parseRegex($matches,$route,$regx) {
        // ��ȡ·�ɵ�ַ����
        $url   =  is_array($route)?$route[0]:$route;
        $url   =  preg_replace('/:(\d)/e','$matches[\\1]',$url);
        if(0=== strpos($url,'/') || 0===strpos($url,'http')) { // ·���ض�����ת
            header("Location: $url", true,(is_array($route) && isset($route[1]))?$route[1]:301);
            exit;
        }else{
            // ����·�ɵ�ַ
            $var  =  $this->parseUrl($url);
            // ����ʣ���URL����
            $regx =  substr_replace($regx,'',0,strlen($matches[0]));
            if($regx) {
                preg_replace('@(\w+)\/([^,\/]+)@e', '$var[strtolower(\'\\1\')]=strip_tags(\'\\2\');', $regx);
            }
            // ����·���Զ����˲���
            if(is_array($route) && isset($route[1])) {
                parse_str($route[1],$params);
                $var   =   array_merge($var,$params);
            }
            $_GET   =  array_merge($var,$_GET);
        }
        return true;
    }
}