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
// $Id: CheckRestRouteBehavior.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ϵͳ��Ϊ��չ REST·�ɼ��
 +------------------------------------------------------------------------------
 */
class CheckRestRouteBehavior extends Behavior {
    // ��Ϊ�������壨Ĭ��ֵ�� ������Ŀ�����и���
    protected $options   =  array(
        'URL_ROUTER_ON'         => false,   // �Ƿ���URL·��
        'URL_ROUTE_RULES'       => array(), // Ĭ��·�ɹ���ע�����������޷����
        );

    /**
     +----------------------------------------------------------
     * ·�ɼ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function run(&$return) {
        $regx = trim($_SERVER['PATH_INFO'],'/');
        // �Ƿ���·��ʹ��
        if(empty($regx) || !C('URL_ROUTER_ON')) $return =  false;
        // ·�ɶ����ļ�������config�е����ö���
        $routes = C('URL_ROUTE_RULES');
        if(is_array(C('routes')))  $routes = C('routes');
        // ·�ɴ���
        if(!empty($routes)) {
            $depr = C('URL_PATHINFO_DEPR');
            foreach ($routes as $rule=>$route){
                // �����ʽ�� array('·�ɹ����������','·�ɵ�ַ','·�ɲ���','�ύ����','��Դ����')
                if(isset($route[3]) && strtolower($_SERVER['REQUEST_METHOD']) != strtolower($route[3])) {
                    continue; // ����������ύ���������
                }
                if(isset($route[4]) && !in_array(__EXT__,explode(',',$route[4]),true)) {
                    continue; // �����������չ�������
                }
                if(0===strpos($route[0],'/') && preg_match($route[0],$regx,$matches)) { // ����·��
                    return self::parseRegex($matches,$route,$regx);
                }elseif(substr_count($regx,'/') >= substr_count($route[0],'/')){ // ����·��
                    // ��һ��ƥ�����
                    $match1 = explode('/',$regx);
                    $match2 = explode('/',$route[0]);
                    $match = true; // �Ƿ�ƥ��
                    foreach ($match2 as $key=>$val){
                        if(':' != substr($val,0,1) && $match2[$key] != $match1[$key])
                            $match = false;
                    }
                    if($match)  return self::parseRule($route,$regx);
                }
            }
        }
        $return  =  false;
    }

    static private function parseUrl($url) {
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
    // array('·�ɹ���','[����/ģ��/����]','�������1=ֵ1&�������2=ֵ2...','��������','��Դ����')
    // array('·�ɹ���','�ⲿ��ַ','�ض������','��������','��Դ����')
    // ·�ɹ����� :��ͷ ��ʾ��̬����
    // �ⲿ��ַ�п����ö�̬���� ���� :1 :2 �ķ�ʽ
    // array('news/:month/:day/:id','News/read?cate=1','status=1','post','html,xml'), 
    // array('new/:id','/new.php?id=:1',301,'get','xml'), �ض���
    static private function parseRule($route,$regx) {
        // ��ȡ·�ɵ�ַ����
        $url   =  $route[1];
        // ��ȡURL��ַ�еĲ���
        $paths = explode('/',$regx);
        // ����·�ɹ���
        $matches  =  array();
        $rule =  explode('/',$route[0]);
        foreach ($rule as $item){
            if(0===strpos($item,':')) { // ��̬������ȡ
                $matches[substr($item,1)] = array_shift($paths);
            }else{ // ����URL�еľ�̬����
                array_shift($paths);
            }
        }
        if(0=== strpos($url,'/') || 0===strpos($url,'http')) { // ·���ض�����ת
            if(strpos($url,':')) { // ���ݶ�̬����
                $values  =  array_values($matches);
                $url  =  preg_replace('/:(\d)/e','$values[\\1-1]',$url);
            }
            header("Location: $url", true,isset($route[2])?$route[2]:301);
            exit;
        }else{
            // ����·�ɵ�ַ
            $var  =  self::parseUrl($url);
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
                preg_replace('@(\w+)\/([^,\/]+)@e', '$var[strtolower(\'\\1\')]="\\2";', implode('/',$paths));
            }
            // ����·���Զ����˲���
            if(isset($route[2])) {
                parse_str($route[2],$params);
                $var   =   array_merge($var,$params);
            }
            $_GET   =  array_merge($var,$_GET);
        }
        return true;
    }

    // ��������·��
    // array('·������','[����/ģ��/����]?����1=ֵ1&����2=ֵ2...','�������','��������','��Դ����')
    // array('·������','�ⲿ��ַ','�ض������','��������','��Դ����')
    // ����ֵ���ⲿ��ַ�п����ö�̬���� ���� :1 :2 �ķ�ʽ
    // array('/new\/(\d+)\/(\d+)/','News/read?id=:1&page=:2&cate=1','status=1','post','html,xml'),
    // array('/new\/(\d+)/','/new.php?id=:1&page=:2&status=1','301','get','html,xml'), �ض���
    static private function parseRegex($matches,$route,$regx) {
        // ��ȡ·�ɵ�ַ����
        $url   =  preg_replace('/:(\d)/e','$matches[\\1]',$route[1]);
        if(0=== strpos($url,'/') || 0===strpos($url,'http')) { // ·���ض�����ת
            header("Location: $url", true,isset($route[1])?$route[2]:301);
            exit;
        }else{
            // ����·�ɵ�ַ
            $var  =  self::parseUrl($url);
            // ����ʣ���URL����
            $regx =  substr_replace($regx,'',0,strlen($matches[0]));
            if($regx) {
                preg_replace('@(\w+)\/([^,\/]+)@e', '$var[strtolower(\'\\1\')]="\\2";', $regx);
            }
            // ����·���Զ����˲���
            if(isset($route[2])) {
                parse_str($route[2],$params);
                $var   =   array_merge($var,$params);
            }
            $_GET   =  array_merge($var,$_GET);
        }
        return true;
    }
}