<?php
	 
	class TestAction extends Action{
		//public $dbstr="mysql://root:111111@localhost:3306/tlferp";
		//public $dbstr2="oracle://oms:oms@lorcl:1521";
		public $citys = '1';
		public $model;
		public $oraclemodel;
		public function __construct(){
			//$this->model = D('Erp_project');
			//$this->oraclemodel = new Model();
			//$this->mysqlmodel->db(11,$this->dbstr);
			//$this->oraclemodel->db(11,$this->dbstr2);
			 
		}

		public function test(){
			 
		//var_dump( get_magic_quotes_gpc());
		//$str = "ert44'42'fff<>";
		//echo $str = str_replace("'","''",$str);
		$str = '<script src= >aa</script><a></b><dsdd>a\'d<style>';
		// $str = str_replace("'","''",$str); 
		echo $str = htmlspecialchars($str);
		echo '<textarea>'.$str.'</textarea>';
		}
		 //菜单数据更新 
		public function role_data(){
			$roles = $this->oraclemodel->query("SELECT * FROM ERP_ROLE ");
			foreach($roles as $one){
				$temp =array();
				$temp['LOAN_MENUSHOW'] = $one['LOAN_MENUSHOW'];
				$temp['LOAN_ROLEORDER'] = $one['LOAN_ROLEORDER'];
				//M('Erp_role')->where("LOAN_ROLEID=".$one['LOAN_ROLEID'])->save($temp);
			}

		}
		public function testt(){
			$offline_cost = unserialize('a:86:{s:6:"agency";i:0;s:11:"agency_info";s:0:"";s:3:"sms";i:0;s:8:"sms_info";s:0:"";s:5:"phone";i:0;s:10:"phone_info";s:0:"";s:6:"market";i:0;s:11:"market_info";s:0:"";s:12:"into_village";i:0;s:17:"into_village_info";s:0:"";s:11:"into_office";i:0;s:16:"into_office_info";s:0:"";s:3:"bus";i:0;s:8:"bus_info";s:0:"";s:4:"taxi";i:0;s:9:"taxi_info";s:0:"";s:14:"transportation";i:0;s:19:"transportation_info";s:0:"";s:3:"seo";i:0;s:8:"seo_info";s:0:"";s:12:"field_warmup";i:0;s:17:"field_warmup_info";s:0:"";s:14:"netfriend_foot";i:0;s:19:"netfriend_foot_info";s:0:"";s:9:"employees";i:0;s:14:"employees_info";s:0:"";s:14:"parttime_staff";i:0;s:19:"parttime_staff_info";s:0:"";s:17:"business_benefits";i:15600;s:22:"business_benefits_info";s:0:"";s:14:"business_other";i:0;s:19:"business_other_info";s:0:"";s:20:"actual_entertainment";i:0;s:25:"actual_entertainment_info";s:0:"";s:15:"travel_expenses";i:0;s:20:"travel_expenses_info";s:0:"";s:10:"propaganda";i:0;s:15:"propaganda_info";s:0:"";s:10:"exhibition";i:0;s:15:"exhibition_info";s:0:"";s:8:"onesheet";i:0;s:13:"onesheet_info";s:0:"";s:8:"xdisplay";i:0;s:13:"xdisplay_info";s:0:"";s:10:"major_suit";i:0;s:15:"major_suit_info";s:0:"";s:3:"led";i:0;s:8:"led_info";s:0:"";s:7:"bus_sub";i:0;s:12:"bus_sub_info";s:0:"";s:5:"radio";i:0;s:10:"radio_info";s:0:"";s:9:"newspaper";i:0;s:14:"newspaper_info";s:0:"";s:10:"net_friend";i:0;s:15:"net_friend_info";s:0:"";s:11:"home_buyers";i:0;s:16:"home_buyers_info";s:0:"";s:8:"customer";i:0;s:13:"customer_info";s:0:"";s:15:"publicity_other";i:0;s:20:"publicity_other_info";s:0:"";s:11:"third_party";i:0;s:16:"third_party_info";s:0:"";s:14:"profit_sharing";i:0;s:19:"profit_sharing_info";s:0:"";s:7:"old_new";i:0;s:12:"old_new_info";s:0:"";s:7:"new_new";i:0;s:12:"new_new_info";s:0:"";s:18:"intermediary_watch";i:0;s:23:"intermediary_watch_info";s:0:"";s:13:"channel_watch";i:0;s:18:"channel_watch_info";s:0:"";s:19:"transaction_rewards";i:0;s:24:"transaction_rewards_info";s:0:"";s:19:"internal_commission";i:0;s:24:"internal_commission_info";s:0:"";s:16:"external_rewards";i:0;s:21:"external_rewards_info";s:0:"";s:3:"pos";i:1000;s:8:"pos_info";s:0:"";s:5:"taxes";i:0;s:10:"taxes_info";s:0:"";s:5:"other";i:43000;s:10:"other_info";s:12:"案场活动费用";}');

			var_dump($offline_cost);
					
		}

		 


	}
?>