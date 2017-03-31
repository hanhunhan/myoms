<?php
 
class TestAction extends Action {

	public $prj_offline_cost = array(
			'1'=>array(
				'name'=>'���ͷ����',
				'smallclass'=>array(
					array(
						'name'=>'�н��',
						'inputname'=>'agency',
					),
				),
			),
			'2'=>array(
				'name'=>'����Ӫ����',
				'smallclass'=>array(
					array(
						'name'=>'���ŷ�',
						'inputname'=>'sms',
					),
					array(
						'name'=>'�绰��',
						'inputname'=>'phone',
					),
				),
			),
			'3'=>array(
				'name'=>'������',
				'smallclass'=>array(
					array(
						'name'=>'���ط�',
						'smallclass'=>array(
							array(
								'name'=>'����/�̳�',
								'inputname'=>'market',
							),
							array(
								'name'=>'��С��',
								'inputname'=>'into_village',
							),
							array(
								'name'=>'��д��¥',
								'inputname'=>'into_office',
							),
						),
					),
					array(
						'name'=>'�⳵��(����)',
						'smallclass'=>array(
							array(
								'name'=>'��ͳ�',
								'inputname'=>'bus',
							),
							array(
								'name'=>'���⳵',
								'inputname'=>'taxi',
							),
						),
					),
					array(
						'name'=>'�����(����)',
						'inputname'=>'transportation',
					),
					array(
						'name'=>'�ƹ��',
						'smallclass'=>array(
							array(
								'name'=>'SEO/SEM�ƹ�',
								'inputname'=>'seo',
							),
						),
					),
					array(
						'name'=>'����ů����',
						'inputname'=>'field_warmup',
					),
					array(
						'name'=>'����ʳƷ��',
						'inputname'=>'netfriend_foot',
					),
				),
			),
			'4'=>array(
				'name'=>'��Ա����',
				'smallclass'=>array(
					array(
						'name'=>'��˾Ա��',
						'inputname'=>'employees',
					),
					array(
						'name'=>'��ְ��Ա',
						'inputname'=>'parttime_staff',
					),
				),
			),
			'5'=>array(
				'name'=>'ҵ���',
				'smallclass'=>array(
					array(
						'name'=>'ҵ�����',
						'inputname'=>'business_benefits',
					),
					array(
						'name'=>'��������',
						'inputname'=>'business_other',
					),
					array(
						'name'=>'ʵ��Ӧ��',
						'inputname'=>'actual_entertainment',
					),
					array(
						'name'=>'���÷�',
						'inputname'=>'travel_expenses',
					),
				),
			),
			'6'=>array(
				'name'=>'������',
				'smallclass'=>array(
					array(
						'name'=>'����Ʒ',
						'inputname'=>'propaganda',
					),
					array(
						'name'=>'��չ��',
						'inputname'=>'exhibition',
					),
					array(
						'name'=>'��ҳ',
						'inputname'=>'onesheet',
					),
					array(
						'name'=>'Xչ��',
						'inputname'=>'xdisplay',
					),
				),
			),
			'7'=>array(
				'name'=>'�ⲿ����',
				'smallclass'=>array(
					array(
						'name'=>'����',
						'inputname'=>'major_suit',
					),
					array(
						'name'=>'LED',
						'inputname'=>'led',
					),
					array(
						'name'=>'����/����',
						'inputname'=>'bus_sub',
					),
					array(
						'name'=>'��̨',
						'inputname'=>'radio',
					),
					array(
						'name'=>'��ֽ/��־',
						'inputname'=>'newspaper',
					),
				),
			),
			'8'=>array(
				'name'=>'������',
				'smallclass'=>array(
					array(
						'name'=>'����',
						'inputname'=>'net_friend',
					),
					array(
						'name'=>'��ҵ����',
						'inputname'=>'home_buyers',
					),
					array(
						'name'=>'�ͻ�',
						'inputname'=>'customer',
					),
					array(
						'name'=>'����',
						'inputname'=>'publicity_other',
					),
				),
			),
			'9'=>array(
				'name'=>'֧������������',
				'inputname'=>'third_party',
			),
			'10'=>array(
				'name'=>'��Ŀ�ֳ�',
				'smallclass'=>array(
					array(
						'name'=>'����ֳ�',
						'inputname'=>'profit_sharing',
					),
				),
			),
			'11'=>array(
				'name'=>'������',
				'smallclass'=>array(
					array(
						'name'=>'�ϴ���',
						'inputname'=>'old_new',
					),
					array(
						'name'=>'�´���',
						'inputname'=>'new_new',
					),
					array(
						'name'=>'�н����',
						'inputname'=>'intermediary_watch',
					),
					array(
						'name'=>'��������',
						'inputname'=>'channel_watch',
					),
				),
			),
			'12'=>array(
				'name'=>'�ɽ���',
				'smallclass'=>array(
					array(
						'name'=>'�ɽ�����',
						'inputname'=>'transaction_rewards',
					),
				),
			),
			'13'=>array(
				'name'=>'�ڲ�Ӷ��',
				'smallclass'=>array(
					array(
						'name'=>'�ڲ����',
						'inputname'=>'internal_commission',
					),
				),
			),
			'14'=>array(
				'name'=>'�ⲿӶ��',
				'smallclass'=>array(
					array(
						'name'=>'�ⲿ����',
						'inputname'=>'external_rewards',
					),
				),
			),
			'15'=>array(
				'name'=>'POS������',
				'smallclass'=>array(
					array(
						'name'=>'POS������',
						'inputname'=>'pos',
					),
				),
			),
			'16'=>array(
				'name'=>'˰��',
				'inputname'=>'taxes',
			),
			'17'=>array(
				'name'=>'����',
				'inputname'=>'other',
			),
		);

	public function fee(){
		$arr = $this->prj_offline_cost;		
		foreach($arr as $k=>$v){
			$this->getfee($v);
		}

	}
	function getfee($arr,$parentid=0){
		$data = array();
		$data['NAME'] = $arr['name'];
		$data['INPUTNAME'] = $arr['inputname'];
		$data['PARENTID'] = $parentid; 
		//$id = D("l_fee")->add($data);
		var_dump($data);
		 
		if( is_array($arr['smallclass']) ){
			 
			foreach($arr['smallclass'] as $k=>$v){
				$this->getfee($v,$id);
			}
		}
	}
    public function index(){
       header('Content-Type:text/html; charset=gb2312');
	   $s = $this->getQu('nj');var_dump($s);
       // $this->display();
    }

	static public $userApi = 'http://api.house365.com/passport/passport_getuser_byuid.php';
	static public $qushuApi = 'http://api.house365.com/xf/newhouse/get_config.php';
	static public $crmApi = 'http://crm.house365.com:81/index.php/Simulate/sea';
    
    
    /**
     * ��ȡ�û���Ϣ
     *
     * @param string $passport_uid
     * @return array
     */
    static public function getUser($uid ){
        $user = self::curl_get_contents( self::$userApi."?passport_uid=$uid" );
        $user = json_decode ($user);
        return $user;
    }
	    /**
     * ��ȡ������Ϣ
     *
    
     * @param int $channel
     * @return array
     */
    static public function getQu( $channel='nj'){
        $qushu = self::curl_get_contents( self::$qushuApi."?city=$channel" );
        $qushu = unserialize($qushu);
        return $qushu;
    }

	    /**
     * CRM����
     *
     * @param array $param
     
     * @return array
     */
    static public function addCrm( $param ){
        $qushu = self::curl_get_contents( self::$crmApi );
        $qushu = unserialize($qushu);
        return $qushu;
    }
    
    
    
   
    
    /**���ؽ�ȡ��Ӧ�����ַ���--���ش�html��ǩ�ַ��������ݲ��ֶ�Ӧ�������ݣ�����������html��ǩ��
     * @param strInput �����ַ���
     * @param intLength ��ȡ����
     * @return String ������ַ���
     * @exception Exception ���쳣����
    */
    static public function Csubstring($strInput,$intLength=0){
        if($intLength==0 || strlen($strInput)<$intLength) return $strInput;
        $strInput = $strInput."<chiwmTag>";
        preg_match_all("/(.*?)<[a-z\/]+.*?>/i",$strInput,$ary);
        $output = "";
        foreach($ary[1] as $key=>$value)
        {
            $len = strlen($value);
            if($intLength<=0){
                $output .= substr($ary[0][$key],$len);
            }else{
                if($len>$intLength){
                    $output .= self::Csubstr($value,$intLength).substr($ary[0][$key],$len);
                    $intLength = 0;
                }else{
                    $output .= $ary[0][$key];
                    $intLength -= $len;
                }
            }
        }
        return substr($output,0,strlen($output)-10);
    }
    
    /**���ؽ�ȡ��Ӧ�����ַ���
     * @param strInput �����ַ���
     * @param intLength ��ȡ�ַ�����
     * @return String ��ȡ�������ַ���
     * @exception Exception ���쳣����
    */
    static public function Csubstr($strInput,$intLength=0){
        if($intLength<=0) return $strInput;
        if($strInput=="") return;
        $strInput=str_replace("&nbsp;"," ",$strInput);
        if(strlen($strInput)>$intLength){
            for($i=0;$i<$intLength;$i++) {
                if(ord($strInput[$i])>128) {
                    $i++;
                    if($i==$intLength) {$i--;break;}
                }
            }
            $strInput=substr($strInput,0,$i);
        }
        return $strInput;
    }
    
    static public function curl_get_contents($str,$t_url=""){
        global $url_domin;
        $ch = curl_init();
        $t_url = $t_url ? $t_url : "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		if($_GET['test'])
			var_dump($t_url);
        curl_setopt($ch, CURLOPT_URL, $str);
        curl_setopt($ch, CURLOPT_REFERER, $t_url);
        curl_setopt($ch,CURLOPT_TIMEOUT ,10);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
    
        $str = curl_exec($ch);
        curl_close($ch);
        return $str;
    }
	public function getdb(){
		//Vendor('Oms.Report');
		//$report = new Report();
		//$r = $report->initReport(2)->getDb(4)->query('select * from FLOW');
	 
			
			Vendor('Oms.Form');
			$form = new Form();
		 
			
			$form =  $form->initForminfo(100)->getResult();
		echo $form;
	}

	public function gettable(){
		Vendor('Oms.Report');
		$report = new Report();
		echo $r = $report->initReport(4)->getReport();
		//var_dump($r);
//		$list = array('a','b','c');
//		$temp = &$list[1];
//		$temp = 'ww';
//		var_dump($list);
	}
	
	public function gettable2(){
		Vendor('Oms.Report');
		$report = new Report();
		echo $r = $report->initReport(6)->getReport();
		//var_dump($r);
//		$list = array('a','b','c');
//		$temp = &$list[1];
//		$temp = 'ww';
//		var_dump($list);
	}
	public function gettable3(){
		Vendor('Oms.Report');
		$report = new Report();
		echo $r = $report->initReport(3)->getReport();
		//var_dump($r);
//		$list = array('a','b','c');
//		$temp = &$list[1];
//		$temp = 'ww';
//		var_dump($list);
	}
	public function gettable4(){
		Vendor('Oms.Report');
		$report = new Report();
		echo $r = $report->initReport(7)->getReport();
		//var_dump($r);
//		$list = array('a','b','c');
//		$temp = &$list[1];
//		$temp = 'ww';
//		var_dump($list);
	}
	public function testwhere(){
		Vendor('Oms.Form');
			$form = new Form(); 
			//$form->CZBTN = '<a class="contrtable-link" onclick="paylist(this);"  href="javascript:void(0);"> ÿ��֧��</a> //<a class="contrtable-link" onclick="customerlist(this);"  href="javascript:void(0);">�쿨�û�</a> <a //class="contrtable-link" onclick="thisedit(this);"  href="javascript:void(0);">�༭</a>
			//<a class="contrtable-link" onclick="fdel(this);"   href="javascript:void(0);">ɾ��</a> '; 
			$sql = "select ID,FOLWNAME from FLOW where id in(".$_SESSION['uinfo']['flow'].")";
			if(!$_REQUEST['ID'] && $_REQUEST['showForm']==1){
				
				$form =  $form->initForminfo(95)->setMyFieldVal('CREATOR',$_SESSION['uinfo']['uid'],true)->setMyFieldVal('CLERKID',$_SESSION['uinfo']['uid'],true)->setMyFieldVal('CREATETIME',date('Y-m-d'),true)->setMyField('FLOW','LISTSQL',$sql)->getResult();


			}else $form =  $form->initForminfo(95)->setMyField('FLOW','FORMVISIBLE','0')->getResult();
			$this->assign('form',$form);
			$this->display('caselist');
	}
	public function ttt(){
		echo __ACTION__;
	}

	public function decodeAscii(){
		echo chr('64');
		Vendor('Oms.Ascii');
		$asc = new Ascii();
		$str = $asc->decode($_POST['str']);
		echo '<form action="" method="post"><textarea name="str"></textarea><textarea name="str2">'.$str.'</textarea> <input type="submit" name="" value="�ύ"/></form>';
	}

	public function testTable(){
		 
		Vendor('Oms.CateTree');
		Vendor('Oms.ProReport');
		$report = new ProReport();
		$report->initReport(7);
		//$Y = $report->y;
		$tab = new CateTree();
		$model = new Model();
		//$str="mysql://root:111111@localhost:3306/oms";
		//$model->db(11,$str);
		//$data = $model->query("select ID ,NAME,PARENTID from ERP_FEE where ISVALID=-1");//var_dump($data);
		//$tab->catelist = $tab->getCatelist($Y);  //var_dump($tab->catelist);
		$tab->X = $report->x;
		$tab->Y = $report->y;
		echo $r = $tab->getTable();


	}
	public function getcontract(){
		//load("@.contract_common");
        // $contract_info = getContractData('nj', 'JWG-2016.04.11-0003');var_dump($contract_info);

        $recordId = 1300;

		  $project_case_model = D('ProjectCase');
                            $case_type = 'fx';
                            $isexists = $project_case_model->is_exists_case_type($recordId, $case_type);
                            $contractInfo = D('Contract')->where('CASE_ID = ' . $recordId)->find();
                            if (is_array($contractInfo) && count($contractInfo)) {
                                $hadContract = true;
                            } else {
                                $hadContract = false;
                            }
							
                            if ($isexists && !$hadContract) {
                                //��ѯ��Ŀ��ͬ��Ϣ
                                $cond_where = "PROJECT_ID = '" . $recordId . "'";
                                $house_info = M('erp_house')->field('CONTRACT_NUM')->where($cond_where)->find();
                                $contract_no = !empty($house_info['CONTRACT_NUM']) ?
                                    $house_info['CONTRACT_NUM'] : '';
								$contract_no  = trim($contract_no );
                                $city_model = D('City');
                                $city_info = $city_model->get_city_info_by_id(1, array('PY'));
                                $city_py = !empty($city_info['PY']) ? strtolower(strip_tags($city_info['PY'])) : ''; 
                                load("@.contract_common");
                                $contract_info = getContractData($city_py, $contract_no);var_dump($contract_no);
								var_dump($contract_info );	
                                if (is_array($contract_info) && !empty($contract_info)) {
                                    $info = array();
                                    $case_info = $project_case_model->get_info_by_pid($recordId, $case_type, array('ID'));
                                    $info['CASE_ID'] = $case_info[0]['ID'];
                                    $info['CONTRACT_NO'] = $contract_info['fullcode'];
                                    $info['COMPANY'] = $contract_info['contunit'];
                                    $info['START_TIME'] = date('Y-m-d H:i:s', $contract_info['contbegintime']);
                                    $info['END_TIME'] = date('Y-m-d H:i:s', $contract_info['contendtime']);
                                    $info['PUB_TIME'] = !empty($contract_info['pubdate']) ?
                                        date('Y-m-d H:i:s', strtotime($contract_info['pubdate'])) : '';
                                    $info['CONF_TIME'] = !empty($contract_info['confirmtime']) ?
                                        date('Y-m-d H:i:s', $contract_info['confirmtime']) : '';
                                    $info['STATUS'] = $contract_info['step'];
                                    $info['MONEY'] = $contract_info['contmoney'];
                                    $info['ADD_TIME'] = date('Y-m-d H:i:s', time());
                                    $info['CONTRACT_TYPE'] = $contract_info['type'];
                                    $info['IS_NEED_INVOICE'] = 0;
                                    $info['SIGN_USER'] = $contract_info['addman'];
                                    $info['CITY_PY'] = $city_py;
                                    //ȡ���������������ڳ���
                                    $creator_info = $workflow->get_Flow_Creator_Info($flowId);
                                    $info['CITY_ID'] = $creator_info['CITY'];

                                    $contract_model = D('Contract');
                                    $contract_id = $contract_model->add_contract_info($info);
									//var_dump($contract_id );	
                                    
                                }
                            }
	}

    public function updateInvoiceBizType() {
        $querySql = <<<BILLING_RECORD_SQL
            SELECT t.id AS billing_id,
                   c.id AS case_id,
                   c.scaletype
            FROM erp_billing_record t
            LEFT JOIN erp_case c ON c.id = t.case_id
            ORDER BY t.id DESC
BILLING_RECORD_SQL;


        $dbResult = D()->query($querySql);

        echo '���м�¼��' . count($dbResult) . '<br/>';
        D()->startTrans();

        $start = 5000 * 11;
        for ($i = $start; $i < $start + 10000; $i++) {
            if (empty($dbResult[$i])) {
                break;
            }

            $k = $i;
            $v = $dbResult[$i];
            if (intval($v['SCALETYPE']) == 3) {
                $bizType = 1;
            } else {
                $bizType = 2;
            }

            $dbBizType = D('BillingRecord')->where("ID = {$v['BILLING_ID']}")->getField('INVOICE_BIZ_TYPE');
            if (!empty($dbBizType)) {
                echo sprintf("��%d����¼��Ϊ�գ�: ID = %d, ������<br/>", intval($k) + 1, $v['BILLING_ID']);
                continue;
            }

            $updated = D('BillingRecord')->where("ID = {$v['BILLING_ID']}")->save(array(
                'INVOICE_BIZ_TYPE' => $bizType
            ));
            if ($updated !== false) {
                echo sprintf("��%d����¼���³ɹ���: ID = %d<br/>", intval($k) + 1, $v['BILLING_ID']);
            } else {
                echo sprintf("��%d����¼����ʧ�ܣ�: ID = %d<br/>", intval($k) + 1, $v['BILLING_ID']);
                break;
            }
        }

        if ($updated != false) {
            D()->commit();
            echo '��¼���³ɹ���<br/>';
        } else {
            D()->rollback();
            echo "��¼����ʧ�ܣ�<br/>";
        }
    }

    public function testInc() {
        $diffAmount = -2;
        $dbResult = D('erp_displace_warehouse')->where("ID = 82")->setInc('USE_NUM', $diffAmount);
        echo $dbResult === false ? 'ʧ��' : '�ɹ�';
    }
	 
}