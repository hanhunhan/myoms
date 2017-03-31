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
// $Id: ThinkTemplate.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP?????????????
 * ???XML???????????????????
 * ????????????? ?????????
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Template
 * @author liu21st <liu21st@gmail.com>
 * @version  $Id: ThinkTemplate.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class  ThinkTemplate {

    // ??????????????????ß“?
    protected $tagLib          =  array();
    // ?????????
    protected $templateFile  =  '';
    // ??????
    public $tVar                 = array();
    public $config  =  array();
    private   $literal = array();

    /**
     +----------------------------------------------------------
     * ?????????????
     * ???????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return ThinkTemplate
     +----------------------------------------------------------
     */
    static public function  getInstance() {
        return get_instance_of(__CLASS__);
    }

    /**
     +----------------------------------------------------------
     * ???????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $config ???????????????
     +----------------------------------------------------------
     */
    public function __construct(){
        $this->config['cache_path']        =  C('CACHE_PATH');
        $this->config['template_suffix']   =  C('TMPL_TEMPLATE_SUFFIX');
        $this->config['cache_suffix']       =  C('TMPL_CACHFILE_SUFFIX');
        $this->config['tmpl_cache']        =  C('TMPL_CACHE_ON');
        $this->config['cache_time']        =  C('TMPL_CACHE_TIME');
        $this->config['taglib_begin']        =  $this->stripPreg(C('TAGLIB_BEGIN'));
        $this->config['taglib_end']          =  $this->stripPreg(C('TAGLIB_END'));
        $this->config['tmpl_begin']         =  $this->stripPreg(C('TMPL_L_DELIM'));
        $this->config['tmpl_end']           =  $this->stripPreg(C('TMPL_R_DELIM'));
        $this->config['default_tmpl']       =  C('TEMPLATE_NAME');
        $this->config['tag_level']            =  C('TAG_NESTED_LEVEL');
        $this->config['layout_item']        = C('TMPL_LAYOUT_ITEM');
    }

    private function stripPreg($str) {
        return str_replace(array('{','}','(',')','|','[',']'),array('\{','\}','\(','\)','\|','\[','\]'),$str);
    }

    // ???????????????
    public function get($name) {
        if(isset($this->tVar[$name]))
            return $this->tVar[$name];
        else
            return false;
    }

    public function set($name,$value) {
        $this->tVar[$name]= $value;
    }

    // ???????
    public function fetch($templateFile,$templateVar) {
        $this->tVar = $templateVar;
        $templateCacheFile  =  $this->loadTemplate($templateFile);
        // ??????ß“????????????????
        extract($templateVar, EXTR_OVERWRITE);
        //??????ùH?????
        include $templateCacheFile;
    }

    /**
     +----------------------------------------------------------
     * ????????·≥????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $tmplTemplateFile ??????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function loadTemplate ($tmplTemplateFile) {
        $this->templateFile    =  $tmplTemplateFile;
        // ??????????????¶À???????
        $tmplCacheFile = $this->config['cache_path'].md5($tmplTemplateFile).$this->config['cache_suffix'];
        // ?????????????
        $tmplContent = file_get_contents($tmplTemplateFile);
        // ?ßÿ???????®∞???
        if(C('LAYOUT_ON')) {
            if(false !== strpos($tmplContent,'{__NOLAYOUT__}')) { // ??????????çI??®∞???
                $tmplContent = str_replace('{__NOLAYOUT__}','',$tmplContent);
            }else{ // ?ùI?????????????
                $layoutFile  =  THEME_PATH.C('LAYOUT_NAME').$this->config['template_suffix'];
                $tmplContent = str_replace($this->config['layout_item'],$tmplContent,file_get_contents($layoutFile));
            }
        }
        //???????????
        $tmplContent = $this->compiler($tmplContent);
        // ????????
        if(!is_dir($this->config['cache_path']))
            mk_dir($this->config['cache_path']);
        //??ß’Cache???
        if( false === file_put_contents($tmplCacheFile,trim($tmplContent)))
            throw_exception(L('_CACHE_WRITE_ERROR_').':'.$tmplCacheFile);
        return $tmplCacheFile;
    }

    /**
     +----------------------------------------------------------
     * ??????????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $tmplContent ???????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function compiler($tmplContent) {
        //??????
        $tmplContent = $this->parse($tmplContent);
        // ??????ùI??Literal???
        $tmplContent = preg_replace('/<!--###literal(\d)###-->/eis',"\$this->restoreLiteral('\\1')",$tmplContent);
        // ?????????
        $tmplContent  =  '<?php if (!defined(\'THINK_PATH\')) exit();?>'.$tmplContent;
        if(C('TMPL_STRIP_SPACE')) {
            /* ???html??????? */
            $find     = array("~>\s+<~","~>(\s+\n|\r)~");
            $replace  = array("><",">");
            $tmplContent = preg_replace($find, $replace, $tmplContent);
        }
        // ????????php????
        $tmplContent = str_replace('?><?php','',$tmplContent);
        return strip_whitespace($tmplContent);
    }

    /**
     +----------------------------------------------------------
     * ?????????
     * ???????????TagLib???? ????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $content ??????????????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function parse($content) {
        // ????????????
        if(empty($content)) return '';
        $begin = $this->config['taglib_begin'];
        $end   = $this->config['taglib_end'];
        // ?????ùIliteral???????
        $content = preg_replace('/'.$begin.'literal'.$end.'(.*?)'.$begin.'\/literal'.$end.'/eis',"\$this->parseLiteral('\\1')",$content);

        // ???include??
        $content  = $this->parseInclude($content);
        // ???PHP??
        $content    =  $this->parsePhp($content);

        // ????????????????ß“?
        // ???????????????¶≤??????????????
        // ????????????????
        // ?????<taglib name="html,mytag..." />
        // ??TAGLIB_LOAD?????true??????ßﬁ??
        if(C('TAGLIB_LOAD')) {
            $this->getIncludeTagLib($content);
            if(!empty($this->tagLib)) {
                // ??????TagLib???ßﬂ???
                foreach($this->tagLib as $tagLibName) {
                    $this->parseTagLib($tagLibName,$content);
                }
            }
        }
        // ??????????? ?????????????????taglib??????? ?????????????XML??
        if(C('TAGLIB_PRE_LOAD')) {
            $tagLibs =  explode(',',C('TAGLIB_PRE_LOAD'));
            foreach ($tagLibs as $tag){
                $this->parseTagLib($tag,$content);
            }
        }
        // ???????? ???????taglib?????????????? ??????????????XML??
        $tagLibs =  explode(',',C('TAGLIB_BUILD_IN'));
        foreach ($tagLibs as $tag){
            $this->parseTagLib($tag,$content,true);
        }
        //???????????? {tagName}
        $content = preg_replace('/('.$this->config['tmpl_begin'].')(\S.+?)('.$this->config['tmpl_end'].')/eis',"\$this->parseTag('\\2')",$content);
        return $content;
    }

    // ???PHP??
    protected function parsePhp($content) {
        // PHP?????
        if(C('TMPL_DENY_PHP') && false !== strpos($content,'<?php')) {
            throw_exception(L('_NOT_ALLOW_PHP_'));
        }elseif(ini_get('short_open_tag')){
            // ???????????????<??????echo?????? ??????????????xml???
            $content = preg_replace('/(<\?(?!php|=|$))/i', '<?php echo \'\\1\'; ?>'."\n", $content );
        }
        return $content;
    }

    // ????????ß÷??????
    protected function parseLayout($content) {
        // ???????ß÷??????
        $find = preg_match('/'.$this->config['taglib_begin'].'layout\s(.+?)\s*?\/'.$this->config['taglib_end'].'/is',$content,$matches);
        if($find) {
            //?ùILayout???
            $content = str_replace($matches[0],'',$content);
            //????Layout???
            $layout = $matches[1];
            $xml =  '<tpl><tag '.$layout.' /></tpl>';
            $xml = simplexml_load_string($xml);
            if(!$xml)
                throw_exception(L('_XML_TAG_ERROR_'));
            $xml = (array)($xml->tag->attributes());
            $array = array_change_key_case($xml['@attributes']);
            if(!C('LAYOUT_ON') || C('LAYOUT_NAME') !=$array['name'] ) {
                // ??????????
                $layoutFile  =  THEME_PATH.$array['name'].$this->config['template_suffix'];
                $replace =  isset($array['replace'])?$array['replace']:$this->config['layout_item'];
                // ?ùI?????????????
                $content = str_replace($replace,$content,file_get_contents($layoutFile));
            }
        }
        return $content;
    }

    // ????????ß÷?include???
    protected function parseInclude($content) {
        // ????????
        $content    =  $this->parseLayout($content);
        // ???????ß÷??????
        $find = preg_match_all('/'.$this->config['taglib_begin'].'include\s(.+?)\s*?\/'.$this->config['taglib_end'].'/is',$content,$matches);
        if($find) {
            for($i=0;$i<$find;$i++) {
                $include = $matches[1][$i];
                $xml =  '<tpl><tag '.$include.' /></tpl>';
                $xml = simplexml_load_string($xml);
                if(!$xml)
                    throw_exception(L('_XML_TAG_ERROR_'));
                $xml = (array)($xml->tag->attributes());
                $array = array_change_key_case($xml['@attributes']);
                $file  =  $array['file'];
                unset($array['file']);
                $content = str_replace($matches[0][$i],$this->parseIncludeItem($file,$array),$content);
            }
        }
        return $content;
    }

    /**
     +----------------------------------------------------------
     * ?ùI????ß÷?literal???
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @param string $content  ???????
     +----------------------------------------------------------
     * @return string|false
     +----------------------------------------------------------
     */
    private function parseLiteral($content) {
        if(trim($content)=='')
            return '';
        $content = stripslashes($content);
        $i  =   count($this->literal);
        $parseStr   =   "<!--###literal{$i}###-->";
        $this->literal[$i]  = $content;
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * ??????ùI??literal???
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @param string $tag  literal??????
     +----------------------------------------------------------
     * @return string|false
     +----------------------------------------------------------
     */
    private function restoreLiteral($tag) {
        // ???literal???
        $parseStr   =  $this->literal[$tag];
        // ????literal???
        unset($this->literal[$tag]);
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * ???????????ß—?????TagLib??
     * ???????ß“?
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $content  ???????
     +----------------------------------------------------------
     * @return string|false
     +----------------------------------------------------------
     */
    public function getIncludeTagLib(& $content) {
        //?????????TagLib???
        $find = preg_match('/'.$this->config['taglib_begin'].'taglib\s(.+?)(\s*?)\/'.$this->config['taglib_end'].'\W/is',$content,$matches);
        if($find) {
            //?ùITagLib???
            $content = str_replace($matches[0],'',$content);
            //????TagLib???
            $tagLibs = $matches[1];
            $xml =  '<tpl><tag '.$tagLibs.' /></tpl>';
            $xml = simplexml_load_string($xml);
            if(!$xml)
                throw_exception(L('_XML_TAG_ERROR_'));
            $xml = (array)($xml->tag->attributes());
            $array = array_change_key_case($xml['@attributes']);
            $this->tagLib = explode(',',$array['name']);
        }
        return;
    }

    /**
     +----------------------------------------------------------
     * TagLib?????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $tagLib ???????????
     * @param string $content ??????????????
     * @param boolen $hide ?????????????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function parseTagLib($tagLib,&$content,$hide=false) {
        $begin = $this->config['taglib_begin'];
        $end   = $this->config['taglib_end'];
        $className = 'TagLib'.ucwords($tagLib);
        if(!import($className)) {
            if(is_file(EXTEND_PATH.'Driver/TagLib/'.$className.'.class.php')) {
                // ???????????????
                $file   = EXTEND_PATH.'Driver/TagLib/'.$className.'.class.php';
            }else{
                // ?????????????
                $file   = CORE_PATH.'Driver/TagLib/'.$className.'.class.php';
            }
            require_cache($file);
        }
        $tLib =  Think::instance($className);
        foreach ($tLib->getTags() as $name=>$val){
            $tags = array($name);
            if(isset($val['alias'])) {// ????????
                $tags = explode(',',$val['alias']);
                $tags[]  =  $name;
            }
            $level = isset($val['level'])?$val['level']:1;
            $closeTag = isset($val['close'])?$val['close']:true;
            foreach ($tags as $tag){
                $parseTag = !$hide? $tagLib.':'.$tag: $tag;// ????????????????
                $n1 = empty($val['attr'])?'(\s*?)':'\s(.*?)';
                if (!$closeTag){
                    $patterns = '/'.$begin.$parseTag.$n1.'\/(\s*?)'.$end.'/eis';
                    $replacement = "\$this->parseXmlTag('$tagLib','$tag','$1','')";
                    $content = preg_replace($patterns, $replacement,$content);
                }else{
                    $patterns = '/'.$begin.$parseTag.$n1.$end.'(.*?)'.$begin.'\/'.$parseTag.'(\s*?)'.$end.'/eis';
                    $replacement = "\$this->parseXmlTag('$tagLib','$tag','$1','$2')";
                    for($i=0;$i<$level;$i++) $content=preg_replace($patterns,$replacement,$content);
                }
            }
        }
    }

    /**
     +----------------------------------------------------------
     * ????????????
     * ????????????????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $tagLib  ?????????
     * @param string $tag  ?????
     * @param string $attr  ???????
     * @param string $content  ???????
     +----------------------------------------------------------
     * @return string|false
     +----------------------------------------------------------
     */
    public function parseXmlTag($tagLib,$tag,$attr,$content) {
        //if (MAGIC_QUOTES_GPC) {
            $attr = stripslashes($attr);
            $content = stripslashes($content);
        //}
        if(ini_get('magic_quotes_sybase'))
            $attr =  str_replace('\"','\'',$attr);
        $tLib =  Think::instance('TagLib'.ucwords(strtolower($tagLib)));
        $parse = '_'.$tag;
        $content = trim($content);
        return $tLib->$parse($attr,$content);
    }

    /**
     +----------------------------------------------------------
     * ?????????
     * ????? {TagName:args [|content] }
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $tagStr ???????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function parseTag($tagStr){
        //if (MAGIC_QUOTES_GPC) {
            $tagStr = stripslashes($tagStr);
        //}
        //??????????
        if(preg_match('/^[\s|\d]/is',$tagStr))
            //?????????????????
            return C('TMPL_L_DELIM') . $tagStr .C('TMPL_R_DELIM');
        $flag =  substr($tagStr,0,1);
        $name   = substr($tagStr,1);
        if('$' == $flag){ //?????????? ??? {$varName}
            return $this->parseVar($name);
        }elseif('-' == $flag || '+'== $flag){ // ???????
            return  '<?php echo '.$flag.$name.';?>';
        }elseif(':' == $flag){ // ??????????????
            return  '<?php echo '.$name.';?>';
        }elseif('~' == $flag){ // ??????????
            return  '<?php '.$name.';?>';
        }elseif(substr($tagStr,0,2)=='//' || (substr($tagStr,0,2)=='/*' && substr($tagStr,-2)=='*/')){
            //?????
            return '';
        }
        // ¶ƒ????????????
        return C('TMPL_L_DELIM') . $tagStr .C('TMPL_R_DELIM');
    }

    /**
     +----------------------------------------------------------
     * ??????????,?????®≤???
     * ????? {$varname|function1|function2=arg1,arg2}
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $varStr ????????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function parseVar($varStr){
        $varStr = trim($varStr);
        static $_varParseList = array();
        //??????????????????????????????????
        if(isset($_varParseList[$varStr])) return $_varParseList[$varStr];
        $parseStr ='';
        $varExists = true;
        if(!empty($varStr)){
            $varArray = explode('|',$varStr);
            //??????????
            $var = array_shift($varArray);
            //??????????? ?????????????????? ->
            //TODO???????????????
            if(preg_match('/->/is',$var))
                return '';
            if('Think.' == substr($var,0,6)){
                // ??????Think.????????????????? ??????é§?????????
                $name = $this->parseThinkVar($var);
            }elseif( false !== strpos($var,'.')) {
                //??? {$var.property}
                $vars = explode('.',$var);
                $var  =  array_shift($vars);
                switch(strtolower(C('TMPL_VAR_IDENTIFY'))) {
                    case 'array': // ????????
                        $name = '$'.$var;
                        foreach ($vars as $key=>$val)
                            $name .= '["'.$val.'"]';
                        break;
                    case 'obj':  // ????????
                        $name = '$'.$var;
                        foreach ($vars as $key=>$val)
                            $name .= '->'.$val;
                        break;
                    default:  // ????ßÿ????????? ??????
                        $name = 'is_array($'.$var.')?$'.$var.'["'.$vars[0].'"]:$'.$var.'->'.$vars[0];
                }
            }elseif(false !==strpos($var,':')){
                //??? {$var:property} ???????????????
                $vars = explode(':',$var);
                $var  =  str_replace(':','->',$var);
                $name = "$".$var;
                $var  = $vars[0];
            }elseif(false !== strpos($var,'[')) {
                //??? {$var['key']} ??????????
                $name = "$".$var;
                preg_match('/(.+?)\[(.+?)\]/is',$var,$match);
                $var = $match[1];
            }else {
                $name = "$$var";
            }
            //???????®≤???
            if(count($varArray)>0)
                $name = $this->parseVarFunction($name,$varArray);
            $parseStr = '<?php echo ('.$name.'); ?>';
        }
        $_varParseList[$varStr] = $parseStr;
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * ??????????®≤???
     * ??? {$varname|function1|function2=arg1,arg2}
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ??????
     * @param array $varArray  ?????ß“?
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function parseVarFunction($name,$varArray){
        //???????®≤???
        $length = count($varArray);
        //??????????®≤????ß“?
        $template_deny_funs = explode(',',C('TMPL_DENY_FUNC_LIST'));
        for($i=0;$i<$length ;$i++ ){
            $args = explode('=',$varArray[$i],2);
            //??éÔ??????
            $fun = strtolower(trim($args[0]));
            switch($fun) {
            case 'default':  // ??????éÔ??
                $name   = '('.$name.')?('.$name.'):'.$args[1];
                break;
            default:  // ?????éÔ??
                if(!in_array($fun,$template_deny_funs)){
                    if(isset($args[1])){
                        if(strstr($args[1],'###')){
                            $args[1] = str_replace('###',$name,$args[1]);
                            $name = "$fun($args[1])";
                        }else{
                            $name = "$fun($name,$args[1])";
                        }
                    }else if(!empty($args[0])){
                        $name = "$fun($name)";
                    }
                }
            }
        }
        return $name;
    }

    /**
     +----------------------------------------------------------
     * ??????????????
     * ??? ?? $Think. ??????????????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $varStr  ?????????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function parseThinkVar($varStr){
        $vars = explode('.',$varStr);
        $vars[1] = strtoupper(trim($vars[1]));
        $parseStr = '';
        if(count($vars)>=3){
            $vars[2] = trim($vars[2]);
            switch($vars[1]){
                case 'SERVER':
                    $parseStr = '$_SERVER[\''.strtoupper($vars[2]).'\']';break;
                case 'GET':
                    $parseStr = '$_GET[\''.$vars[2].'\']';break;
                case 'POST':
                    $parseStr = '$_POST[\''.$vars[2].'\']';break;
                case 'COOKIE':
                    if(isset($vars[3])) {
                        $parseStr = '$_COOKIE[\''.$vars[2].'\'][\''.$vars[3].'\']';
                    }else{
                        $parseStr = '$_COOKIE[\''.$vars[2].'\']';
                    }break;
                case 'SESSION':
                    if(isset($vars[3])) {
                        $parseStr = '$_SESSION[\''.$vars[2].'\'][\''.$vars[3].'\']';
                    }else{
                        $parseStr = '$_SESSION[\''.$vars[2].'\']';
                    }
                    break;
                case 'ENV':
                    $parseStr = '$_ENV[\''.strtoupper($vars[2]).'\']';break;
                case 'REQUEST':
                    $parseStr = '$_REQUEST[\''.$vars[2].'\']';break;
                case 'CONST':
                    $parseStr = strtoupper($vars[2]);break;
                case 'LANG':
                    $parseStr = 'L("'.$vars[2].'")';break;
				case 'CONFIG':
                    if(isset($vars[3])) {
                        $vars[2] .= '.'.$vars[3];
                    }
                    $parseStr = 'C("'.$vars[2].'")';break;
                default:break;
            }
        }else if(count($vars)==2){
            switch($vars[1]){
                case 'NOW':
                    $parseStr = "date('Y-m-d g:i a',time())";
                    break;
                case 'VERSION':
                    $parseStr = 'THINK_VERSION';
                    break;
                case 'TEMPLATE':
                    $parseStr = "'".$this->templateFile."'";//'C("TEMPLATE_NAME")';
                    break;
                case 'LDELIM':
                    $parseStr = 'C("TMPL_L_DELIM")';
                    break;
                case 'RDELIM':
                    $parseStr = 'C("TMPL_R_DELIM")';
                    break;
                default:
                    if(defined($vars[1]))
                        $parseStr = $vars[1];
            }
        }
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * ?????????·≥???? ???????????°§??????????????°§??
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $tmplPublicName  ????????????
     * @param array $vars  ??????????ß“?
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseIncludeItem($tmplPublicName,$vars=array()){
        if(substr($tmplPublicName,0,1)=='$')
            //??????????????
            $tmplPublicName = $this->get(substr($tmplPublicName,1));

        if(false === strpos($tmplPublicName,$this->config['template_suffix'])) {
            // ????????? ???????:???:???? ????? ??????????????
            $path   =  explode(':',$tmplPublicName);
            $action = array_pop($path);
            $module = !empty($path)?array_pop($path):MODULE_NAME;
            if(!empty($path)) {// ???????????
                $path = dirname(THEME_PATH).'/'.array_pop($path).'/';
            }else{
                $path = THEME_PATH;
            }
            $depr = defined('GROUP_NAME')?C('TMPL_FILE_DEPR'):'/';
            $tmplPublicName  =  $path.$module.$depr.$action.$this->config['template_suffix'];
        }
        // ?????????????
        $parseStr = file_get_contents($tmplPublicName);
        foreach ($vars as $key=>$val) {
            $parseStr = str_replace('['.$key.']',$val,$parseStr);
        }
        //??¶∆?????????????????
        return $this->parseInclude($parseStr);
    }

}