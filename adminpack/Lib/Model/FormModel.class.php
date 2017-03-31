<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-29
 * Time: 上午9:24
 */
class FormModel extends  Model{
   // protected $tablePrefix  =   'tf_';
    protected $tableName ='form';
    protected $pk  = 'FORMNO';
	//protected $fields = array('FORMNO', 'FORMTITLE', 'FORMTYPE', 'SQLTEXT'  );
	protected $_validate = array(
		array('FORMTITLE','require','界面标题不能为空！','0','regex','3'),
		array('SQLTEXT','require','数据源不能为空！','0','regex','3'),
		array('PKFIELD','require','主键不能为空！','0','regex','3'),
		array('FORMTITLE','checkString/','界面标题必须以中文开头!','2','regex','3'),
		array('SQLTEXT','checkString','数据源必须为字符串！','2','function','3'),
		array('PKFIELD','checkString','主键必须为字符串！','2','function','3')
		
	);

    function checkString($string){
		if(is_string($string)) {
			return true;
		} else {
			return false;
		}
	}
    
}