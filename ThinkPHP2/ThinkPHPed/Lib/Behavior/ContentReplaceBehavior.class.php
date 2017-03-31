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
// $Id: ContentReplaceBehavior.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ???????? ???????????ùI
 +------------------------------------------------------------------------------
 */
class ContentReplaceBehavior extends Behavior {
    // ???????????
    protected $options   =  array(
        'TMPL_PARSE_STRING'=>array(),
    );

    // ???????????????????run
    public function run(&$content){
        $content = $this->templateContentReplace($content);
    }

    /**
     +----------------------------------------------------------
     * ????????ùI
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $content ???????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function templateContentReplace($content) {
        // ??????????????ùI
        $replace =  array(
            '__TMPL__'      => APP_TMPL_PATH,  // ????????
            '__ROOT__'      => __ROOT__,       // ?????????
            '__APP__'       => __APP__,        // ?????????
            '__GROUP__'   =>   defined('GROUP_NAME')?__GROUP__:__APP__,
            '__ACTION__'    => __ACTION__,     // ??????????
            '__SELF__'      => __SELF__,       // ????????
            '__URL__'       => __URL__,
            '../Public'   => APP_TMPL_PATH.'Public',// ????????????
            '__PUBLIC__'  => __ROOT__.'/Public',// ???????
        );
        // ??????????????????????ùI
        if(is_array(C('TMPL_PARSE_STRING')) )
            $replace =  array_merge($replace,array_change_key_case(C('TMPL_PARSE_STRING'),CASE_UPPER));
        $content = str_replace(array_keys($replace),array_values($replace),$content);
        return $content;
    }

}