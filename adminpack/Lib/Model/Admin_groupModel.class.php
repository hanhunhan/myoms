<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-29
 * Time: ионГ9:24
 */
class Admin_groupModel extends  Model{
   // protected $tablePrefix  =   'tf_';
    protected $tableName ='admin_group';
    protected $pk  = 'LOAN_GROUPID';
	protected $fields = array('LOAN_GROUPID', 'LOAN_GROUPNAME', 'LOAN_GROUPVAL', 'LOAN_GROUPSTATUS','LOAN_GROUPCUSTOM','LOAN_GROUPDEL','LOAN_GROUPCREATED','LOAN_GROUPUPDATED' );
    
}