<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 15-1-29
 * Time: ионГ9:24
 */
class Admin_userModel extends  Model{
   // protected $tablePrefix  =   'tf_';
    protected $tableName ='admin_user';
    protected $pk  = 'LOAN_USERID';
	protected $fields = array('LOAN_USERID', 'LOAN_USERNAME', 'LOAN_USERPWD', 'LOAN_USERCITY','LOAN_POWERCITY','LOAN_POWERFROM','LOAN_USERGROUP','LOAN_USERDEPART','LOAN_POS','LOAN_MOBILE','LOAN_QQ','LOAN_EMAIL','LOAN_TRUENAME','LOAN_UPLOG','LOAN_LOCK','LOAN_CREATED','LOAN_LOGINTIME','LOAN_UPDATED');
    
}