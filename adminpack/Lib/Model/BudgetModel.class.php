<?php

/**
 * 预算类
 *
 * @author 
 */

class BudgetModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'PRJBUDGET';
    
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    public function update_statistics($id){

		$data = array();
		$data['OFFLINE_COST_SUM'] = $this->get_budgetfee($id,108);
		$data['OFFLINE_COST_SUM_PROFIT'] = $this->get_budgetfee($id,109);
		$data['OFFLINE_COST_SUM_PROFIT_RATE'] = $this->get_budgetfee($id,110);
		$data['PRO_TAXES'] = $this->get_budgetfee($id,101);
		$data['PRO_TAXES_PROFIT'] = $this->get_budgetfee($id,102);
		$data['PRO_TAXES_PROFIT_RATE'] = $this->get_budgetfee($id,103);
		$data['ONLINE_COST'] = $this->get_budgetfee($id,106);
		$data['ONLINE_COST_RATE'] = $this->get_budgetfee($id,107);
		 
		$conf_where = "ID=$id";
		$res = $this->where($conf_where)->save($data);
		return $res;

	}
	public function get_budgetfee($budgetid,$feeid){
		$table_name = $this->tablePrefix.'BUDGETFEE';
		$conf_where = "BUDGETID=$budgetid and FEEID=$feeid and ISVALID=-1";
		$res = $this->table($table_name)->where($conf_where)->find();
		return $res['AMOUNT'];
	}
    //设置统计数据
	public function set_budgetfee($budgetid,$data){
		
		$res = $this->where("ID=$budgetid")->save($data);
		return $res;
	}
    
}

 