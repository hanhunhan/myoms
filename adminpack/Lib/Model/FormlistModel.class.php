<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-29
 * Time: 上午9:24
 */
class FormlistModel extends  Model{
   // protected $tablePrefix  =   'tf_';
    protected $tableName ='formlist';
    protected $pk  = 'FIELDNAME';
	protected $fk  = 'FORMNO';
	//protected $fields = array('FORMNO', 'FIELDNAME', 'FIELDMEANS', 'LINENO','COLNO' ,'FIELDTYPE' );
    
}