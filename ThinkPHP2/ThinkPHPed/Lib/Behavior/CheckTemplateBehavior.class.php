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
// $Id: CheckTemplateBehavior.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ???????? ?????
 +------------------------------------------------------------------------------
 */
class CheckTemplateBehavior extends Behavior {
    // ??????????‰Í?????? ????????????§Ú???
    protected $options   =  array(
            'VAR_TEMPLATE'          => 't',		// ???????§Ý?????
            'TMPL_DETECT_THEME'     => false,       // ?????????????
            'DEFAULT_THEME'    => '',	// ??????????????
            'TMPL_TEMPLATE_SUFFIX'  => '.html',     // ????????????
            'TMPL_FILE_DEPR'=>'/', //??????MODULE_NAME??ACTION_NAME??????????????????÷•????§¹
        );

    // ???????????????????run
    public function run(&$params){
        // ??????????
        $this->checkTemplate();
    }

    /**
     +----------------------------------------------------------
     * ????ï…???????????????
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    private function checkTemplate() {
        /* ?????????????? */
        $templateSet =  C('DEFAULT_THEME');
        if(C('TMPL_DETECT_THEME')) {// ?????????????
            $t = C('VAR_TEMPLATE');
            if (isset($_GET[$t])){
                $templateSet = $_GET[$t];
            }elseif(cookie('think_template')){
                $templateSet = cookie('think_template');
            }
            // ???????????????????????
            if(!is_dir(TMPL_PATH.$templateSet))
                $templateSet = C('DEFAULT_THEME');
            cookie('think_template',$templateSet);
        }

        /* ???????????? */
        define('THEME_NAME',   $templateSet);                  // ??????????????
        $group   =  defined('GROUP_NAME')?GROUP_NAME.'/':'';
        define('THEME_PATH',   TMPL_PATH.$group.(THEME_NAME?THEME_NAME.'/':''));
        define('APP_TMPL_PATH',__ROOT__.'/'.APP_NAME.(APP_NAME?'/':'').'Tpl/'.$group.(THEME_NAME?THEME_NAME.'/':''));
        C('TEMPLATE_NAME',THEME_PATH.MODULE_NAME.(defined('GROUP_NAME')?C('TMPL_FILE_DEPR'):'/').ACTION_NAME.C('TMPL_TEMPLATE_SUFFIX'));
        C('CACHE_PATH',CACHE_PATH.$group);
        return ;
    }
}