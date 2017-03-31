<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-29
 * Time: ионГ9:24
 */
class Erp_roleModel extends  Model{
   // protected $tablePrefix  =   'tf_';
    protected $tableName ='erp_role';
    protected $pk  = 'LOAN_ROLEID';
	protected $fields = array('LOAN_ROLEID', 'LOAN_ROLENAME', 'LOAN_ROLECONTROL', 'LOAN_ROLEACTION','LOAN_ROLEPARENTID','LOAN_ROLEDISPLAY','LOAN_ROLEORDER','LOAN_ROLEMEM','LOAN_CREATED','LOAN_UPDATED');
    
}