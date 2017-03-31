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
// $Id: TagLibCx.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * CX��ǩ�������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Template
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: TagLibCx.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class TagLibCx extends TagLib {

    // ��ǩ����
    protected $tags   =  array(
        // ��ǩ���壺 attr �����б� close �Ƿ�պϣ�0 ����1 Ĭ��1�� alias ��ǩ���� level Ƕ�ײ��
        'php'=>array(),
        'volist'=>array('attr'=>'name,id,offset,length,key,mod','level'=>3),
        'foreach' =>array('attr'=>'name,item,key','level'=>3),
        'if'=>array('attr'=>'condition','level'=>2),
        'elseif'=>array('attr'=>'condition','close'=>0),
        'else'=>array('attr'=>'','close'=>0),
        'switch'=>array('attr'=>'name','level'=>2),
        'case'=>array('attr'=>'value,break'),
        'default'=>array('attr'=>'','close'=>0),
        'compare'=>array('attr'=>'name,value,type','level'=>3,'alias'=>'eq,equal,notequal,neq,gt,lt,egt,elt,heq,nheq'),
        'range'=>array('attr'=>'name,value,type','level'=>3,'alias'=>'in,notin,between,notbetween'),
        'empty'=>array('attr'=>'name','level'=>3),
        'notempty'=>array('attr'=>'name','level'=>3),
        'present'=>array('attr'=>'name','level'=>3),
        'notpresent'=>array('attr'=>'name','level'=>3),
        'defined'=>array('attr'=>'name','level'=>3),
        'notdefined'=>array('attr'=>'name','level'=>3),
        'import'=>array('attr'=>'file,href,type,value,basepath','close'=>0,'alias'=>'load,css,js'),
        'assign'=>array('attr'=>'name,value','close'=>0),
        'define'=>array('attr'=>'name,value','close'=>0),
    	'for'=>array('attr'=>'start,end,name,comparison,step', 'level'=>3),
        );

    /**
     +----------------------------------------------------------
     * php��ǩ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _php($attr,$content) {
        $parseStr = '<?php '.$content.' ?>';
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * volist��ǩ���� ѭ��������ݼ�
     * ��ʽ��
     * <volist name="userList" id="user" empty="" >
     * {user.username}
     * {user.email}
     * </volist>
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function _volist($attr,$content) {
        static $_iterateParseCache = array();
        //����Ѿ�����������ֱ�ӷ��ر���ֵ
        $cacheIterateId = md5($attr.$content);
        if(isset($_iterateParseCache[$cacheIterateId]))
            return $_iterateParseCache[$cacheIterateId];
        $tag      = $this->parseXmlAttr($attr,'volist');
        $name   = $tag['name'];
        $id        = $tag['id'];
        $empty  = isset($tag['empty'])?$tag['empty']:'';
        $key     =   !empty($tag['key'])?$tag['key']:'i';
        $mod    =   isset($tag['mod'])?$tag['mod']:'2';
        // ����ʹ�ú����趨���ݼ� <volist name=":fun('arg')" id="vo">{$vo.name}</volist>
        $parseStr   =  '<?php ';
        if(0===strpos($name,':')) {
            $parseStr   .= '$_result='.substr($name,1).';';
            $name   = '$_result';
        }else{
            $name   = $this->autoBuildVar($name);
        }
        $parseStr  .=  'if(is_array('.$name.')): $'.$key.' = 0;';
		if(isset($tag['length']) && '' !=$tag['length'] ) {
			$parseStr  .= ' $__LIST__ = array_slice('.$name.','.$tag['offset'].','.$tag['length'].',true);';
		}elseif(isset($tag['offset'])  && '' !=$tag['offset']){
            $parseStr  .= ' $__LIST__ = array_slice('.$name.','.$tag['offset'].',count($__LIST__),true);';
        }else{
            $parseStr .= ' $__LIST__ = '.$name.';';
        }
        $parseStr .= 'if( count($__LIST__)==0 ) : echo "'.$empty.'" ;';
        $parseStr .= 'else: ';
        $parseStr .= 'foreach($__LIST__ as $key=>$'.$id.'): ';
        $parseStr .= '$mod = ($'.$key.' % '.$mod.' );';
        $parseStr .= '++$'.$key.';?>';
        $parseStr .= $this->tpl->parse($content);
        $parseStr .= '<?php endforeach; endif; else: echo "'.$empty.'" ;endif; ?>';
        $_iterateParseCache[$cacheIterateId] = $parseStr;

        if(!empty($parseStr)) {
            return $parseStr;
        }
        return ;
    }

    public function _foreach($attr,$content) {
        static $_iterateParseCache = array();
        //����Ѿ�����������ֱ�ӷ��ر���ֵ
        $cacheIterateId = md5($attr.$content);
        if(isset($_iterateParseCache[$cacheIterateId]))
            return $_iterateParseCache[$cacheIterateId];
        $tag   = $this->parseXmlAttr($attr,'foreach');
        $name= $tag['name'];
        $item  = $tag['item'];
        $key   =   !empty($tag['key'])?$tag['key']:'key';
        $name= $this->autoBuildVar($name);
        $parseStr  =  '<?php if(is_array('.$name.')): foreach('.$name.' as $'.$key.'=>$'.$item.'): ?>';
        $parseStr .= $this->tpl->parse($content);
        $parseStr .= '<?php endforeach; endif; ?>';
        $_iterateParseCache[$cacheIterateId] = $parseStr;
        if(!empty($parseStr)) {
            return $parseStr;
        }
        return ;
    }

    /**
     +----------------------------------------------------------
     * if��ǩ����
     * ��ʽ��
     * <if condition=" $a eq 1" >
     * <elseif condition="$a eq 2" />
     * <else />
     * </if>
     * ���ʽ֧�� eq neq gt egt lt elt == > >= < <= or and || &&
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _if($attr,$content) {
        $tag          = $this->parseXmlAttr($attr,'if');
        $condition   = $this->parseCondition($tag['condition']);
        $parseStr  = '<?php if('.$condition.'): ?>'.$content.'<?php endif; ?>';
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * else��ǩ����
     * ��ʽ����if��ǩ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _elseif($attr,$content) {
        $tag          = $this->parseXmlAttr($attr,'elseif');
        $condition   = $this->parseCondition($tag['condition']);
        $parseStr   = '<?php elseif('.$condition.'): ?>';
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * else��ǩ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _else($attr) {
        $parseStr = '<?php else: ?>';
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * switch��ǩ����
     * ��ʽ��
     * <switch name="a.name" >
     * <case value="1" break="false">1</case>
     * <case value="2" >2</case>
     * <default />other
     * </switch>
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _switch($attr,$content) {
        $tag = $this->parseXmlAttr($attr,'switch');
        $name = $tag['name'];
        $varArray = explode('|',$name);
        $name   =   array_shift($varArray);
        $name = $this->autoBuildVar($name);
        if(count($varArray)>0)
            $name = $this->tpl->parseVarFunction($name,$varArray);
        $parseStr = '<?php switch('.$name.'): ?>'.$content.'<?php endswitch;?>';
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * case��ǩ���� ��Ҫ���switch����Ч
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _case($attr,$content) {
        $tag = $this->parseXmlAttr($attr,'case');
        $value = $tag['value'];
        if('$' == substr($value,0,1)) {
            $varArray = explode('|',$value);
            $value	=	array_shift($varArray);
            $value  =  $this->autoBuildVar(substr($value,1));
            if(count($varArray)>0)
                $value = $this->tpl->parseVarFunction($value,$varArray);
            $value   =  'case '.$value.': ';
        }elseif(strpos($value,'|')){
            $values  =  explode('|',$value);
            $value   =  '';
            foreach ($values as $val){
                $value   .=  'case "'.addslashes($val).'": ';
            }
        }else{
            $value	=	'case "'.$value.'": ';
        }
        $parseStr = '<?php '.$value.' ?>'.$content;
        $isBreak  = isset($tag['break']) ? $tag['break'] : '';
        if('' ==$isBreak || $isBreak) {
            $parseStr .= '<?php break;?>';
        }
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * default��ǩ���� ��Ҫ���switch����Ч
     * ʹ�ã� <default />ddfdf
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _default($attr) {
        $parseStr = '<?php default: ?>';
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * compare��ǩ����
     * ����ֵ�ıȽ� ֧�� eq neq gt lt egt elt heq nheq Ĭ����eq
     * ��ʽ�� <compare name="" type="eq" value="" >content</compare>
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _compare($attr,$content,$type='eq') {
        $tag      = $this->parseXmlAttr($attr,'compare');
        $name   = $tag['name'];
        $value   = $tag['value'];
        $type    =   isset($tag['type'])?$tag['type']:$type;
        $type    =   $this->parseCondition(' '.$type.' ');
        $varArray = explode('|',$name);
        $name   =   array_shift($varArray);
        $name = $this->autoBuildVar($name);
        if(count($varArray)>0)
            $name = $this->tpl->parseVarFunction($name,$varArray);
        if('$' == substr($value,0,1)) {
            $value  =  $this->autoBuildVar(substr($value,1));
        }else {
            $value  =   '"'.$value.'"';
        }
        $parseStr = '<?php if(('.$name.') '.$type.' '.$value.'): ?>'.$content.'<?php endif; ?>';
        return $parseStr;
    }

    public function _eq($attr,$content) {
        return $this->_compare($attr,$content,'eq');
    }

    public function _equal($attr,$content) {
        return $this->_compare($attr,$content,'eq');
    }

    public function _neq($attr,$content) {
        return $this->_compare($attr,$content,'neq');
    }

    public function _notequal($attr,$content) {
        return $this->_compare($attr,$content,'neq');
    }

    public function _gt($attr,$content) {
        return $this->_compare($attr,$content,'gt');
    }

    public function _lt($attr,$content) {
        return $this->_compare($attr,$content,'lt');
    }

    public function _egt($attr,$content) {
        return $this->_compare($attr,$content,'egt');
    }

    public function _elt($attr,$content) {
        return $this->_compare($attr,$content,'elt');
    }

    public function _heq($attr,$content) {
        return $this->_compare($attr,$content,'heq');
    }

    public function _nheq($attr,$content) {
        return $this->_compare($attr,$content,'nheq');
    }

    /**
     +----------------------------------------------------------
     * range��ǩ����
     * ���ĳ������������ĳ����Χ ��������� type= in ��ʾ�ڷ�Χ�� �����ʾ�ڷ�Χ��
     * ��ʽ�� <range name="var|function"  value="val" type='in|notin' >content</range>
     * example: <range name="a"  value="1,2,3" type='in' >content</range>
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     * @param string $type  �Ƚ�����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _range($attr,$content,$type='in') {
        $tag      = $this->parseXmlAttr($attr,'range');
        $name   = $tag['name'];
        $value   = $tag['value'];
        $varArray = explode('|',$name);
        $name   =   array_shift($varArray);
        $name = $this->autoBuildVar($name);
        if(count($varArray)>0)
            $name = $this->tpl->parseVarFunction($name,$varArray);

        $type    =   isset($tag['type'])?$tag['type']:$type;

        if('$' == substr($value,0,1)) {
            $value  =  $this->autoBuildVar(substr($value,1));
            $str  =   'is_array('.$value.')?'.$value.':explode(\',\','.$value.')';
        }else{
            $value  =   '"'.$value.'"';
            $str  =   'explode(\',\','.$value.')';
        }
        if($type=='between') {
            $parseStr = '<?php $_RANGE_VAR_='.$str.';if('.$name.'>= $_RANGE_VAR_[0] && '.$name.'<= $_RANGE_VAR_[1]):?>'.$content.'<?php endif; ?>';
        }elseif($type=='notbetween'){
            $parseStr = '<?php $_RANGE_VAR_='.$str.';if('.$name.'<$_RANGE_VAR_[0] && '.$name.'>$_RANGE_VAR_[1]):?>'.$content.'<?php endif; ?>';
        }else{
            $fun  =  ($type == 'in')? 'in_array'    :   '!in_array';
            $parseStr = '<?php if('.$fun.'(('.$name.'), '.$str.')): ?>'.$content.'<?php endif; ?>';
        }
        return $parseStr;
    }

    // range��ǩ�ı��� ����in�ж�
    public function _in($attr,$content) {
        return $this->_range($attr,$content,'in');
    }

    // range��ǩ�ı��� ����notin�ж�
    public function _notin($attr,$content) {
        return $this->_range($attr,$content,'notin');
    }

    public function _between($attr,$content){
        return $this->_range($attr,$content,'between');
    }

    public function _notbetween($attr,$content){
        return $this->_range($attr,$content,'notbetween');
    }

    /**
     +----------------------------------------------------------
     * present��ǩ����
     * ���ĳ�������Ѿ����� ���������
     * ��ʽ�� <present name="" >content</present>
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _present($attr,$content) {
        $tag      = $this->parseXmlAttr($attr,'present');
        $name   = $tag['name'];
        $name   = $this->autoBuildVar($name);
        $parseStr  = '<?php if(isset('.$name.')): ?>'.$content.'<?php endif; ?>';
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * notpresent��ǩ����
     * ���ĳ������û�����ã����������
     * ��ʽ�� <notpresent name="" >content</notpresent>
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _notpresent($attr,$content) {
        $tag      = $this->parseXmlAttr($attr,'notpresent');
        $name   = $tag['name'];
        $name   = $this->autoBuildVar($name);
        $parseStr  = '<?php if(!isset('.$name.')): ?>'.$content.'<?php endif; ?>';
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * empty��ǩ����
     * ���ĳ������Ϊempty ���������
     * ��ʽ�� <empty name="" >content</empty>
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _empty($attr,$content) {
        $tag      = $this->parseXmlAttr($attr,'empty');
        $name   = $tag['name'];
        $name   = $this->autoBuildVar($name);
        $parseStr  = '<?php if(empty('.$name.')): ?>'.$content.'<?php endif; ?>';
        return $parseStr;
    }

    public function _notempty($attr,$content) {
        $tag      = $this->parseXmlAttr($attr,'notempty');
        $name   = $tag['name'];
        $name   = $this->autoBuildVar($name);
        $parseStr  = '<?php if(!empty('.$name.')): ?>'.$content.'<?php endif; ?>';
        return $parseStr;
    }
    /**
     * �ж��Ƿ��Ѿ������˸ó���
     * <defined name='TXT'>�Ѷ���</defined>
     * @param <type> $attr
     * @param <type> $content
     * @return string
     */
    public function _defined($attr,$content) {
        $tag        = $this->parseXmlAttr($attr,'defined');
        $name     = $tag['name'];
        $parseStr = '<?php if(defined("'.$name.'")): ?>'.$content.'<?php endif; ?>';
        return $parseStr;
    }

    public function _notdefined($attr,$content) {
        $tag        = $this->parseXmlAttr($attr,'_notdefined');
        $name     = $tag['name'];
        $parseStr = '<?php if(!defined("'.$name.'")): ?>'.$content.'<?php endif; ?>';
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * import ��ǩ���� <import file="Js.Base" /> <import file="Css.Base" type="css" />
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     * @param boolean $isFile  �Ƿ��ļ���ʽ
     * @param string $type  ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _import($attr,$content,$isFile=false,$type='') {
        $tag  = $this->parseXmlAttr($attr,'import');
        $file   = isset($tag['file'])?$tag['file']:$tag['href'];
        $parseStr = '';
        $endStr   = '';
        // �ж��Ƿ���ڼ������� ����ʹ�ú����ж�(Ĭ��Ϊisset)
        if (isset($tag['value'])) {
            $varArray  = explode('|',$tag['value']);
            $name      = array_shift($varArray);
            $name      = $this->autoBuildVar($name);
            if (!empty($varArray))
                $name  = $this->tpl->parseVarFunction($name,$varArray);
            else
                $name  = 'isset('.$name.')';
            $parseStr .= '<?php if('.$name.'): ?>';
            $endStr    = '<?php endif; ?>';
        }
        if($isFile) {
            // �����ļ�����׺�Զ�ʶ��
            $type = $type?$type:(!empty($tag['type'])?strtolower($tag['type']):null);
            // �ļ���ʽ����
            $array =  explode(',',$file);
            foreach ($array as $val){
                if (!$type || isset($reset)) {
                    $type = $reset = strtolower(substr(strrchr($val, '.'),1));
                }
                switch($type) {
                case 'js':
                    $parseStr .= '<script type="text/javascript" src="'.$val.'"></script>';
                    break;
                case 'css':
                    $parseStr .= '<link rel="stylesheet" type="text/css" href="'.$val.'" />';
                    break;
                case 'php':
                    $parseStr .= '<?php require_cache("'.$val.'"); ?>';
                    break;
                }
            }
        }else{
            // �����ռ䵼��ģʽ Ĭ����js
            $type       = $type?$type:(!empty($tag['type'])?strtolower($tag['type']):'js');
            $basepath   = !empty($tag['basepath'])?$tag['basepath']:__ROOT__.'/Public';
            // �����ռ䷽ʽ�����ⲿ�ļ�
            $array =  explode(',',$file);
            foreach ($array as $val){
                switch($type) {
                case 'js':
                    $parseStr .= '<script type="text/javascript" src="'.$basepath.'/'.str_replace(array('.','#'), array('/','.'),$val).'.js"></script>';
                    break;
                case 'css':
                    $parseStr .= '<link rel="stylesheet" type="text/css" href="'.$basepath.'/'.str_replace(array('.','#'), array('/','.'),$val).'.css" />';
                    break;
                case 'php':
                    $parseStr .= '<?php import("'.$val.'"); ?>';
                    break;
                }
            }
        }
        return $parseStr.$endStr;
    }

    // import���� �����ļ���ʽ����(Ҫʹ�������ռ������import) ���� <load file="__PUBLIC__/Js/Base.js" />
    public function _load($attr,$content) {
        return $this->_import($attr,$content,true);
    }

    // import����ʹ�� ����css�ļ� <css file="__PUBLIC__/Css/Base.css" />
    public function _css($attr,$content) {
        return $this->_import($attr,$content,true,'css');
    }

    // import����ʹ�� ����js�ļ� <js file="__PUBLIC__/Js/Base.js" />
    public function _js($attr,$content) {
        return $this->_import($attr,$content,true,'js');
    }

    /**
     +----------------------------------------------------------
     * assign��ǩ����
     * ��ģ���и�ĳ��������ֵ ֧�ֱ�����ֵ
     * ��ʽ�� <assign name="" value="" />
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _assign($attr,$content) {
        $tag      = $this->parseXmlAttr($attr,'assign');
        $name   = $this->autoBuildVar($tag['name']);
        if('$'==substr($tag['value'],0,1)) {
            $value   =  $this->autoBuildVar(substr($tag['value'],1));
        }else{
            $value   =   '\''.$tag['value']. '\'';
        }
        $parseStr  = '<?php '.$name.' = '.$value.'; ?>';
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * define��ǩ����
     * ��ģ���ж��峣�� ֧�ֱ�����ֵ
     * ��ʽ�� <define name="" value="" />
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _define($attr,$content) {
        $tag      = $this->parseXmlAttr($attr,'define');
        $name   =  '\''.$tag['name']. '\'';
        if('$'==substr($tag['value'],0,1)) {
            $value   =  $this->autoBuildVar(substr($tag['value'],1));
        }else{
            $value   =   '\''.$tag['value']. '\'';
        }
        $parseStr  = '<?php define('.$name.', '.$value.'); ?>';
        return $parseStr;
    }
    
    /**
     +----------------------------------------------------------
     * for��ǩ����
     * ��ʽ�� <for start="" end="" comparison="" step="" name="" />
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $attr ��ǩ����
     * @param string $content  ��ǩ����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
	public function _for($attr, $content){
    	//����Ĭ��ֵ
    	$start 		= 0;
    	$end   		= 0;
    	$step 		= 1;
    	$comparison = 'lt';
    	$name		= 'i';
    	//��ȡ����
    	foreach ($this->parseXmlAttr($attr, 'for') as $key => $value){
    		$value = trim($value);
    		if(':'==substr($value,0,1))
    			$value = substr($value,1);
    		elseif('$'==substr($value,0,1))
    			$value = $this->autoBuildVar(substr($value,1));
    		switch ($key){
    			case 'start': $start = $value; break;
    			case 'end' : $end = $value; break;
    			case 'step': $step = $value; break;
    			case 'comparison':$comparison = $value;break;
    			case 'name':$name = $value;break;
    		}
    	}
    	
    	$parseStr   = '<?php $__FOR_START__='.$start.';$__FOR_END__='.$end.';';
    	$parseStr  .= 'for($'.$name.'=$__FOR_START__;'.$this->parseCondition('$'.$name.' '.$comparison.' $__FOR_END__').';$'.$name.'+='.$step.'){ ?>';
    	$parseStr  .= $content;
    	$parseStr  .= '<?php } ?>';
    	return $parseStr;
    }

}