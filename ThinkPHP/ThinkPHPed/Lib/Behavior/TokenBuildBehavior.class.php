<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: TokenBuildBehavior.class.php 2659 2012-01-23 15:04:24Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ???????? ???????????
 +------------------------------------------------------------------------------
 */
class TokenBuildBehavior extends Behavior {
    // ???????????
    protected $options   =  array(
        'TOKEN_ON'              => true,     // ???????????
        'TOKEN_NAME'            => '__hash__',    // ??????????????????????
        'TOKEN_TYPE'            => 'md5',   // ??????????????
        'TOKEN_RESET'               =>   true, // ???????????????
    );

    public function run(&$content){
        if(C('TOKEN_ON')) {
            if(strpos($content,'{__TOKEN__}')) {
                // ????????????????¦Ë??
                $content = str_replace('{__TOKEN__}',$this->buildToken(),$content);
            }elseif(preg_match('/<\/form(\s*)>/is',$content,$match)) {
                // ????????????????????
                $content = str_replace($match[0],$this->buildToken().$match[0],$content);
            }
        }
    }

    // ???????????
    private function buildToken() {
        $tokenName   = C('TOKEN_NAME');
        $tokenType = C('TOKEN_TYPE');
        if(!isset($_SESSION[$tokenName])) {
            $_SESSION[$tokenName]  = array();
        }
        // ?????????¦·???
        $tokenKey  =  md5($_SERVER['REQUEST_URI']);
        if(isset($_SESSION[$tokenName][$tokenKey])) {// ?????ÖÎ???????session
            $tokenValue = $_SESSION[$tokenName][$tokenKey];
        }else{
            $tokenValue = $tokenType(microtime(TRUE));
            $_SESSION[$tokenName][$tokenKey]   =  $tokenValue;
        }
        // ?????¦Æ???????????????
        if($action   =  C('TOKEN_ACTION')){
            $_SESSION[$action($tokenKey)] = true;
        }
        $token   =  '<input type="hidden" name="'.$tokenName.'" value="'.$tokenKey.'_'.$tokenValue.'" />';
        return $token;
    }
}