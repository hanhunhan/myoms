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
// $Id: ReadHtmlCacheBehavior.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ???????? ?????????
 +------------------------------------------------------------------------------
 */
class ReadHtmlCacheBehavior extends Behavior {
    protected $options   =  array(
            'HTML_CACHE_ON'=>false,
            'HTML_CACHE_TIME'=>60,
            'HTML_CACHE_RULES'=>array(),
            'HTML_FILE_SUFFIX'=>'.html',
        );

    // ???????????????????run
    public function run(&$params){
        // ??????????
        if(C('HTML_CACHE_ON'))  {
            if($cacheTime = $this->requireHtmlCache() && $this->checkHTMLCache(HTML_FILE_NAME,$cacheTime)) { //????????��
                // ????????????
                readfile(HTML_FILE_NAME);
                exit();
            }
        }
    }

    // ?��??????????????
    static private function requireHtmlCache() {
        // ???????????????
         $htmls = C('HTML_CACHE_RULES'); // ??????????
         if(!empty($htmls)) {
            // ???????????????? actionName=>array(?????????,?????????,?????????')
            // 'read'=>array('{id},{name}',60,'md5') ?????????????��??? ?? ???��???
            // ????????
            $moduleName = strtolower(MODULE_NAME);
            if(isset($htmls[$moduleName.':'.ACTION_NAME])) {
                $html   =   $htmls[$moduleName.':'.ACTION_NAME];   // ??????????????????
            }elseif(isset($htmls[$moduleName.':'])){// ?????????????
                $html   =   $htmls[$moduleName.':'];
            }elseif(isset($htmls[ACTION_NAME])){
                $html   =   $htmls[ACTION_NAME]; // ???��???????????
            }elseif(isset($htmls['*'])){
                $html   =   $htmls['*']; // ?????????
            }elseif(isset($htmls['empty:index']) && !class_exists(MODULE_NAME.'Action')){
                $html   =    $htmls['empty:index']; // ??????????
            }elseif(isset($htmls[$moduleName.':_empty']) && $this->isEmptyAction(MODULE_NAME,ACTION_NAME)){
                $html   =    $htmls[$moduleName.':_empty']; // ????????????
            }
            if(!empty($html)) {
                // ??????????
                $rule    = $html[0];
                // ??$_???????????
                $rule  = preg_replace('/{\$(_\w+)\.(\w+)\|(\w+)}/e',"\\3(\$\\1['\\2'])",$rule);
                $rule  = preg_replace('/{\$(_\w+)\.(\w+)}/e',"\$\\1['\\2']",$rule);
                // {ID|FUN} GET???????��
                $rule  = preg_replace('/{(\w+)\|(\w+)}/e',"\\2(\$_GET['\\1'])",$rule);
                $rule  = preg_replace('/{(\w+)}/e',"\$_GET['\\1']",$rule);
                // ??????????
                $rule  = str_ireplace(
                    array('{:app}','{:module}','{:action}','{:group}'),
                    array(APP_NAME,MODULE_NAME,ACTION_NAME,defined('GROUP_NAME')?GROUP_NAME:''),
                    $rule);
                // {|FUN} ??????��???
                $rule  = preg_replace('/{|(\w+)}/e',"\\1()",$rule);
                if(!empty($html[2])) $rule    =   $html[2]($rule); // ?????????
                $cacheTime = isset($html[1])?$html[1]:C('HTML_CACHE_TIME'); // ??????��??
                // ??????????
                define('HTML_FILE_NAME',HTML_PATH . $rule.C('HTML_FILE_SUFFIX'));
                return $cacheTime;
            }
        }
        // ???�U??
        return false;
    }

    /**
     +----------------------------------------------------------
     * ????HTML????????��
     * ?????��??????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $cacheFile  ????????
     * @param integer $cacheTime  ??????��??
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    static public function checkHTMLCache($cacheFile='',$cacheTime='') {
        if(!is_file($cacheFile)){
            return false;
        }elseif (filemtime(C('TEMPLATE_NAME')) > filemtime($cacheFile)) {
            // ?????????????????????????
            return false;
        }elseif(!is_numeric($cacheTime) && function_exists($cacheTime)){
            return $cacheTime($cacheFile);
        }elseif ($cacheTime != 0 && time() > filemtime($cacheFile)+$cacheTime) {
            // ??????????��??
            return false;
        }
        //????????��
        return true;
    }

    //????????????
    static private function isEmptyAction($module,$action) {
        $className =  $module.'Action';
        $class=new $className;
        return !method_exists($class,$action);
    }

}