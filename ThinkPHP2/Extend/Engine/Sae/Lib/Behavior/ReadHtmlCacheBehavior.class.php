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
// $Id: ReadHtmlCacheBehavior.class.php 2766 2012-02-20 15:58:21Z luofei614@gmail.com $

/**
 +------------------------------------------------------------------------------
 * ϵͳ��Ϊ��չ ��̬�����ȡ
 +------------------------------------------------------------------------------
 */
class ReadHtmlCacheBehavior extends Behavior {
    protected $options   =  array(
            'HTML_CACHE_ON'=>false,
            'HTML_CACHE_TIME'=>60,
            'HTML_CACHE_RULES'=>array(),
            'HTML_FILE_SUFFIX'=>'.html',
        );
    static protected $html_content='';//[sae] �洢html����

        // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$params){
        // ������̬����
        if(C('HTML_CACHE_ON'))  {
            if(($cacheTime = $this->requireHtmlCache()) && $this->checkHTMLCache(HTML_FILE_NAME,$cacheTime)) { //��̬ҳ����Ч
                //[sae] ��ȡ��̬ҳ�����
                exit(self::$html_content);
            }
        }
    }

    // �ж��Ƿ���Ҫ��̬����
    static private function requireHtmlCache() {
        // ������ǰ�ľ�̬����
         $htmls = C('HTML_CACHE_RULES'); // ��ȡ��̬����
         if(!empty($htmls)) {
            // ��̬�����ļ������ʽ actionName=>array(����̬����,������ʱ�䡯,�����ӹ���')
            // 'read'=>array('{id},{name}',60,'md5') ���뱣֤��̬�����Ψһ�� �� ���ж���
            // ��⾲̬����
            $moduleName = strtolower(MODULE_NAME);
            if(isset($htmls[$moduleName.':'.ACTION_NAME])) {
                $html   =   $htmls[$moduleName.':'.ACTION_NAME];   // ĳ��ģ��Ĳ����ľ�̬����
            }elseif(isset($htmls[$moduleName.':'])){// ĳ��ģ��ľ�̬����
                $html   =   $htmls[$moduleName.':'];
            }elseif(isset($htmls[ACTION_NAME])){
                $html   =   $htmls[ACTION_NAME]; // ���в����ľ�̬����
            }elseif(isset($htmls['*'])){
                $html   =   $htmls['*']; // ȫ�־�̬����
            }elseif(isset($htmls['empty:index']) && !class_exists(MODULE_NAME.'Action')){
                $html   =    $htmls['empty:index']; // ��ģ�龲̬����
            }elseif(isset($htmls[$moduleName.':_empty']) && $this->isEmptyAction(MODULE_NAME,ACTION_NAME)){
                $html   =    $htmls[$moduleName.':_empty']; // �ղ�����̬����
            }
            if(!empty($html)) {
                // �����̬����
                $rule    = $html[0];
                // ��$_��ͷ��ϵͳ����
                $rule  = preg_replace('/{\$(_\w+)\.(\w+)\|(\w+)}/e',"\\3(\$\\1['\\2'])",$rule);
                $rule  = preg_replace('/{\$(_\w+)\.(\w+)}/e',"\$\\1['\\2']",$rule);
                // {ID|FUN} GET�����ļ�д
                $rule  = preg_replace('/{(\w+)\|(\w+)}/e',"\\2(\$_GET['\\1'])",$rule);
                $rule  = preg_replace('/{(\w+)}/e',"\$_GET['\\1']",$rule);
                // ����ϵͳ����
                $rule  = str_ireplace(
                    array('{:app}','{:module}','{:action}','{:group}'),
                    array(APP_NAME,MODULE_NAME,ACTION_NAME,defined('GROUP_NAME')?GROUP_NAME:''),
                    $rule);
                // {|FUN} ����ʹ�ú���
                $rule  = preg_replace('/{|(\w+)}/e',"\\1()",$rule);
                if(!empty($html[2])) $rule    =   $html[2]($rule); // Ӧ�ø��Ӻ���
                $cacheTime = isset($html[1])?$html[1]:C('HTML_CACHE_TIME'); // ������Ч��
                // ��ǰ�����ļ�
                define('HTML_FILE_NAME',HTML_PATH . $rule.C('HTML_FILE_SUFFIX'));
                return $cacheTime;
            }
        }
        // ���軺��
        return false;
    }

    /**
     +----------------------------------------------------------
     * ��龲̬HTML�ļ��Ƿ���Ч
     * �����Ч��Ҫ���¸���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $cacheFile  ��̬�ļ���
     * @param integer $cacheTime  ������Ч��
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    //[sae] ��龲̬����
    static public function checkHTMLCache($cacheFile='',$cacheTime='') {
        $kv=Think::instance('SaeKVClient');
        if(!$kv->init()) halt('��û�г�ʼ��KVDB������SAEƽ̨���г�ʼ��');
        $content=$kv->get($cacheFile);
        if(!$content)
            return false;
        $mtime=  substr($content,0,10);
        self::$html_content=substr($content,10);
        if (filemtime(C('TEMPLATE_NAME')) > $mtime) {
            // ģ���ļ�������¾�̬�ļ���Ҫ����
            return false;
        }elseif(!is_numeric($cacheTime) && function_exists($cacheTime)){
            return $cacheTime($cacheFile);
        }elseif ($cacheTime != 0 && time() > $mtime+$cacheTime) {
            // �ļ��Ƿ�����Ч��
            return false;
        }
        //��̬�ļ���Ч
        return true;
    }

    //����Ƿ��ǿղ���
    static private function isEmptyAction($module,$action) {
        $className =  $module.'Action';
        $class=new $className;
        return !method_exists($class,$action);
    }

}