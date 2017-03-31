<?php

/**
 * 立项信息MODEl
 *
 * @author liuhu
 */
class HouseModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'HOUSE';
    
	 /***项目业务类型***/
    private  $_conf_case_type_remark = array(
                                            1 => '电商',
                                            2 => '分销',
                                            3 => '硬广',
                                            4 => '活动',
                                            5 => '产品',
                                            7 => '项目活动',
											8 => '非我方收筹',
                                        );
	/** 活动形式**/
	private $_conf_activ_type = array(
									1=>"招商活动",
									2=>"品牌推广",
									3=>"项目推广",
									4=>"研讨活动",
									5=>"培训活动",
									6=>"其他",
								);
	/** 活动形式**/
	private $_conf_flow_status = array(
									1=>"未开始未办理",
									2=>"办理中",
									3=>"办结",
									4=>"办结"	
								);
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * 根据项目编号获取项目是否资金池项目
     * @param int $prjid   项目编号
     * @return boolean TRUE资金池\FALSE非资金池 
     * 
     */
    public function get_isfundpool_by_prjid($prjid)
    {   
        $prjid = intval($prjid);
        $project_info = array();
        
        $isfundpool = FALSE;
        
        if($prjid > 0)
        {   
            $cond_where = "PROJECT_ID = '".$prjid."'";
            $project_info = $this->where($cond_where)->field('ISFUNDPOOL')->find();
            
            if(is_array($project_info) && !empty($project_info))
            {   
                $isfundpool = self::is_fundpool($project_info['ISFUNDPOOL']);
            }
        }
        
        return $isfundpool;
    }
    
    
    /**
     * 判断是否为资金池项目
     * @param int $isfundpool_val   是否资金池数值
     * @return boolean TRUE资金池\FALSE非资金池 
     * 
     */
    public function is_fundpool($isfundpool_val)
    {   
        $isfundpool = FALSE;
        switch ($isfundpool_val)
        {
            case '-1':
                $isfundpool = TRUE;
                break;
            case '0':
                $isfundpool = TRUE;
                break;
            case '1':
                $isfundpool = FALSE;
                break;
        }
        
        return $isfundpool;
    }
    
    
    /**
     * 根据项目编号获楼盘信息
     *
     * @access	public
     * @param	mixed  $prj_ids 楼盘编号
     * @param   array $search_field 查询字段
     * @return	array
     */
    public function get_house_info_by_prjid($prj_ids, $search_field = array())
    {	
    	$info = array();
		
    	if(is_array($prj_ids) && !empty($prj_ids))
    	{
    		$ids_str = implode(',', $prj_ids);
    		$cond_where = " PROJECT_ID IN (".$ids_str.")";
    	}
    	else
    	{
    		$prj_id  = intval($prj_ids);
    		$cond_where = " PROJECT_ID = '".$prj_id."'";
    	}
    	
    	if(is_array($search_field) && !empty($search_field) )
    	{
    		$search_str = implode(',', $search_field);
    		$info = $this->field($search_str)->where($cond_where)->select();
    	}
    	else
    	{
    		$info = $this->where($cond_where)->select();
    	}
    	//echo $this->getLastSql();
    	return $info;
    }

	 /**
     * 获取项目预算信息静态页面
     *
     * @access	public
     * @param	$project_id、$flow_type   html_type（1 默认全部  2 项目信息  3预算信息）
     * @return	html
     */

	public function get_House_Info_Html($flowid,$project_id,$flow_type,$cid=0,$html_type=1)
	{
		$db_arr = array(
			'PROJECT_ID'=>'项目编号',
			'CUSTOMER_MAN'=>'项目经理',
			'CIT_ID'=>'项目所在城市',
			'CONTRACT_NUM'=>'合同编号',
			'REL_PROPERTY'=>'关联楼盘',
			'PRO_LISTID'=>'关联ID',
			'DEV_ENT'=>'开发企业',
			'PROPERTY_CLASS'=>'物业类别',
			'PRO_ADDR'=>'项目地址',
			'PRO_NAME'=>'项目名称',
			'TLF_SOURCE'=>'团立方房源',
			'TLF_DISCOUNT'=>'团立方优惠',
			'SALEPERMIT'=>'项目销售许可证',
			'ISFUNDPOOL'=>'是否资金池项目',
			'PAYMENT_SECURITY'=>'支付保证金',
			'FPSCALE'=>'资金池比例',
			'RETURN_CONTENT'=>'收益内容',
			'SPECIALFPDESCRIPTION'=>'特殊资金池描述',
			
			'PRO_ADV'=>'项目及房源优势',
			'PRO_INF'=>'项目及房源劣势',
			
			

			 'ISCONTRACT'=>'合同能否收回',
			
			
			'MONEY_BET'=>'是否资金对赌',
			'PROPERTIES'=>'电商项目属性',
			'CONDOMINIUM'=>'是否开设公共账户',


			'ONLINE_AD_SCH'=>'广告排期附件',
			'CONTRACT_FILE'=>'项目合同附件',
            'USING_DECORATION_PRODUCT' => '使用装修产品',
            'USING_FINANCIAL_PRODUCT' => '使用金融产品',
			'OTHERINCOME' =>'是否其他收入',
				'PRO_INFO'=>'备注',
			
		);

		$return_tr = array(
			'39'=>'<td rowspan="46" colspan="1">费用类别—线下</td><td colspan="1">经纪服务费</td><td colspan="2">中介费</td>',
			'41'=>'<td rowspan="2" colspan="1">数据营销费</td><td colspan="2">短信费</td>',
			'42'=>'<td colspan="2">电话费</td>',
			'45'=>'<td rowspan="9" colspan="1">渠道费</td><td rowspan="3">场地费</td><td>超市/商场</td>',
			'46'=>'<td>进小区</td>',
			'47'=>'<td>写字楼</td>',
			'49'=>'<td rowspan="2">租车费(载人)</td><td>大巴车</td>',
			'50'=>'<td>出租车</td>',
			'51'=>'<td colspan="2">运输费(载物)</td>',
			'53'=>'<td>推广费</td><td>SEO/SEM推广</td>',
			'54'=>'<td colspan="2">案场暖场费</td>',
			'55'=>'<td colspan="2">网友食品费</td>',
			'57'=>'<td rowspan="2" colspan="1">人员工资</td><td colspan="2">公司员工</td>',
			'58'=>'<td colspan="2">兼职人员</td>',
			'60'=>'<td rowspan="4" colspan="1">业务费</td><td colspan="2">业务津贴</td>',
			'61'=>'<td colspan="2">其他费用</td>',
			'62'=>'<td colspan="2">实际应酬</td>',
			'63'=>'<td colspan="2">差旅费</td>',
			'65'=>'<td rowspan="4" colspan="1">制作费</td><td colspan="2">宣传品</td>',
			'66'=>'<td colspan="2">布展费</td>',
			'67'=>'<td colspan="2">单页</td>',
			'68'=>'<td colspan="2">X展架</td>',
			'70'=>'<td rowspan="5" colspan="1">外部广告费</td><td colspan="2">大牌</td>',
			'71'=>'<td colspan="2">LED</td>',
			'72'=>'<td colspan="2">公交/地铁</td>',
			'73'=>'<td colspan="2">电台</td>',
			'74'=>'<td colspan="2">报纸/杂志</td>',
			'76'=>'<td rowspan="4" colspan="1">宣传费</td><td colspan="2">网友</td>',
			'77'=>'<td colspan="2">置业顾问</td>',
			'78'=>'<td colspan="2">客户</td>',
			'79'=>'<td colspan="2">其他</td>',
			'80'=>'<td colspan="3">支付第三方费用</td>',
			'82'=>'<td colspan="1">项目分成</td><td colspan="2">利润分成</td>',
			'84'=>'<td rowspan="4" colspan="1">带看费</td><td colspan="2">老带新</td>',
			'85'=>'<td colspan="2">新带新</td>',
			'86'=>'<td colspan="2">中介带看</td>',
			'87'=>'<td colspan="2">渠道带看</td>',
			'89'=>'<td colspan="1">成交费</td><td colspan="2">成交奖励</td>',
			'91'=>'<td colspan="1">内部佣金</td><td colspan="2">内部提成</td>',
			'93'=>'<td colspan="1">外部佣金</td><td colspan="2">外部奖励</td>',
			'95'=>'<td colspan="1">POS手续费</td><td colspan="2">POS手续费</td>',
			'96'=>'<td colspan="3">税金(支付第三方费用的10%)</td>',
			'97'=>'<td colspan="3">其他</td>',
			'108'=>'<td colspan="3">付现成本</td>',
			'109'=>'<td colspan="3">付现利润</td>',
			'110'=>'<td colspan="3">付现利润率</td>',

			'101'=>'<td rowspan="3" colspan="1">税后项目情况(供参考)</td><td colspan="3">除资金池外项目税金</td>',
			'102'=>'<td colspan="3">税后项目利润</td>',
			'103'=>'<td colspan="3">税后项目利润率</td>',
			'98'=>'<td rowspan="4" colspan="1">费用类别—线上</td><td colspan="3">广告预算（折后价）</td>',
			'99'=>'<td colspan="3">地产首页配送广告（折后）</td>',
			'106'=>'<td colspan="3">扣除线下+线上支出利润</td>',
			'107'=>'<td colspan="3">扣除线下+线上支出利润率</td>',
		);
		$noinput_arr = array(98,99,101,102,103,106,107,108,109,110);
		/** 项目信息  START**/
		$houseData = M("Erp_house")->where("PROJECT_ID=".$project_id)->find();
		
		Vendor('Oms.Changerecord');
		$changer = new Changerecord();
		$changer->fields = array('PROJECT_ID',
            'CUSTOMER_MAN', 'CIT_ID', 'CONTRACT_NUM', 'REL_PROPERTY',
            'PRO_LISTID', 'DEV_ENT', 'PROPERTY_CLASS', 'PRO_ADDR',
            'PRO_NAME', 'TLF_SOURCE', 'TLF_DISCOUNT', 'SALEPERMIT',
            'ISFUNDPOOL', 'PRO_ADV', 'PRO_INF', 'RETURN_CONTENT',
            'ISCONTRACT', 'PRO_INFO', 'ONLINE_AD_SCH', 'CONTRACT_FILE',
            'USING_DECORATION_PRODUCT', 'USING_FINANCIAL_PRODUCT','FPSCALE','SPECIALFPDESCRIPTION','MONEY_BET','PROPERTIES','CONDOMINIUM','PAYMENT_SECURITY','OTHERINCOME');//'ONLINE_AD_SCH', 'CONTRACT_FILE',


		$optt['TABLE'] = 'ERP_HOUSE';
		$optt['BID'] = $houseData['ID'];//79
		$optt['CID'] = $cid;//53
		$houseChange = $changer->getRecords($optt);		//var_dump($houseChange);

		//判断是否是分销项目
		$project = D('Erp_project')->where("ID=$project_id")->find();

		//$projectType = self::get_Project_Type($project_id,$record_id);
		$html = '';
		$width = $html_type==1 ? '90%' : '900';
		$html = $html . "<table width='$width' cellspacing='0' cellpadding='10' border='1' align='center'  style='border-collapse: collapse;' >";
		$title[1] = '项目信息';
		$title[2] = '项目信息'; 
		$title[3] = '立项预算表';
		//标题
		$html = $html . "<tr><td colspan='12' align='center' ><h1 style='font-weight:600;font-size:16px;' >".self::get_Contrast_Data('PRO_NAME',$houseData['PRO_NAME'],$houseChange['PRO_NAME']).$title[$html_type]. " </h1></td></tr>";
		
		if($html_type==1 or $html_type==2){
			//基本属性
			$i = 0;
			foreach($db_arr as $k=>$v)
			{
				if($flow_type == "lixiangbiangeng")
				{
					$text = self::get_Contrast_Data($k,$houseData[$k],$houseChange[$k]);
				}
				elseif($flow_type == "lixiangshenqing")
				{	
					$text = self::get_Contrast_Data($k,$houseData[$k]);
				}
				if($k=='FPSCALE') $text .= '%';


				if($k=='PRO_INFO') {
					$html = $html . "<tr><td colspan='2' width='10%'>{$v}</td><td colspan='10' width='40%'>" . $text . "</td></tr>";

				}elseif($k=='OTHERINCOME'){
					if($project['MSTATUS'] !== null && $project['MSTATUS'] >=1) {
						$html = $html . "<tr><td colspan='2' width='10%'>{$v}</td><td colspan='10' width='40%'>" . $text . "</td></tr>";
					}
				}else{
					if($i%2==0)
					{
						$html = $html . "<tr><td colspan='2' width='10%'>{$v}</td><td colspan='4' width='40%'>".$text. "</td>";
					}
					else
					{
						$html = $html . "<td colspan='2' width='10%'>{$v}</td><td colspan='4' width='40%'>".$text."</td></tr>";
					}
				}
				$i ++;
			}
		}
		if($html_type==2) return $html . '</table>';
		/** END **/
		
		//项目业务类型
		$projectCase = M("Erp_case")->where("PROJECT_ID = ".$project_id)->select();
		
		foreach($projectCase as $case)
		{
			$CaseArr[] = $case['ID'];
		}
		
		$CaseStr = implode(',',$CaseArr); 
		if($html_type==1 or $html_type==3){
			//立项预算
			//$project_Case_Buget = M("Erp_prjbudget")->where("CASE_ID IN ($CaseStr)")->select();
			$project_Case_Buget =  M()->query("select T.*,to_char(T.FROMDATE,'yyyy-mm-dd hh24:mi:ss') as FROMDATE,to_char(T.TODATE,'yyyy-mm-dd hh24:mi:ss') as TODATE,to_char(T.UNDOTIME,'yyyy-mm-dd hh24:mi:ss') as UNDOTIME from ERP_PRJBUDGET T where CASE_ID IN ($CaseStr)"); //var_dump($project_Case_Buget);
			//print_r($project_Case_Buget);exit;	
			foreach($project_Case_Buget as $keyy=>$CaseRecord)
			{
				
				
				
				//判断电商 常规 or 非常规
				$projectType ='';
				/** 目标分解  START **/

				//变更后导客量、变更后预计成交数量
				$change_Sets = $change_Customers = 0;

				$Budget_Sale_condition = "PROJECTT_ID = ".$project_id." AND ISVALID=-1";
				$Budget_Sale_Change_condition = "PROJECTT_ID = ".$project_id;

				$Sets = M('Erp_budgetsale')->where($Budget_Sale_condition)->sum('SETS');
				
				$Customers = M('Erp_budgetsale')->where($Budget_Sale_condition)->sum('CUSTOMERS');
				
				$change_Budget_Sale_Records = M('Erp_budgetsale')->where($Budget_Sale_Change_condition)->select();
				
				if($change_Budget_Sale_Records)
				{
					$param = array(
							'TABLE' => 'ERP_BUDGETSALE',
							'CID' => $cid
					);
					foreach($change_Budget_Sale_Records as $change)
					{
						$param['BID'] =  $change['ID'];
						$changer->fields=array('SETS','CUSTOMERS');
						$Change_List = $changer->getRecords($param);
						
						if($Change_List)
						{
							$change_Sets += $Change_List['SETS']['VALUEE']; 
							$change_Customers += $Change_List['CUSTOMERS']['VALUEE']; 
						}
					}
				}
				
				/** END**/
				
				if($flow_type == "lixiangbiangeng")
				{
					if($CaseRecord['SCALETYPE'] == 1 or $CaseRecord['SCALETYPE'] == 2 or  $CaseRecord['SCALETYPE'] == 8 ) //电商分销非我方收筹类型判断常规 or 非常规
					{ 
						$projectType = self::get_Project_Type($project_id,$CaseRecord['SCALETYPE'],$cid, $CaseRecord['ID']);
					}
					$Sets_Result = $change_Sets ? $change_Sets."<font style='color:red;'>[原]".$Sets."</font>" : $Sets ;
					$Customers_Result = $change_Customers ? $change_Customers."<font style='color:red;'>[原]".$Customers."</font>" : $Customers ;
					
					$Budget_Fee_condition = "BUDGETID = ".$CaseRecord['ID'] ;
				}
				elseif($flow_type == "lixiangshenqing")
				{	
					if($CaseRecord['SCALETYPE'] == 1 or $CaseRecord['SCALETYPE'] == 2 or  $CaseRecord['SCALETYPE'] == 8)
					{
						$projectType = self::get_Project_Type($project_id,$CaseRecord['SCALETYPE']);
					}
					$Sets_Result = $Sets ? $Sets : 0;
					$Customers_Result = $Customers ? $Customers : 0;

					$Budget_Fee_condition = "BUDGETID = ".$CaseRecord['ID']." AND ISVALID=-1";
				}
				
				//$html .= "<tr><td colspan='2'>业务类型</td><td colspan='10'>".$this->_conf_case_type_remark[$CaseRecord['SCALETYPE']].$projectType."</td></tr>";

				if($html_type==3 or $html_type==1){
					$temp = array();
					$temp['TABLE'] = 'ERP_PRJBUDGET';
					$temp['BID'] = $CaseRecord['ID'];//79
					$temp['CID'] = $cid;//53
					$changer->fields=array('SCALETYPE','FROMDATE','TODATE','UNDOTIME','FIRSTSETS','AVERAGESETS','SUMPROFIT','FEEINFO');
					$budgetChange = $changer->getRecords($temp);	
					$SCALETYPE = self::get_Contrast_Data('SCALETYPE',$CaseRecord['SCALETYPE'],$budgetChange['SCALETYPE']);
					if($keyy>0) $html .=  "<tr><td colspan='12'> </td></tr>";
					$html .=  "<tr><td align='center' colspan='1'>业务类型 </td><td align='center'>执行起始日期 </td><td align='center'>执行终止日期 </td><td align='center'>撤场时间 </td><td align='center'>首次去化套数</td><td align='center'>月均去化套数</td><td align='center'>预估总收入</td><td align='center' colspan='5' width='30%'>收费标准说明</td></tr>";
					if($flow_type == "lixiangbiangeng")
					{ 
						$html .=  "<tr><td align='center' colspan='1'><b>".$this->_conf_case_type_remark[$SCALETYPE].$projectType."</b></td><td align='center'>".self::get_Contrast_Data('FROMDATE',$CaseRecord['FROMDATE'],$budgetChange['FROMDATE'])."</td><td align='center'>".self::get_Contrast_Data('TODATE',$CaseRecord['TODATE'],$budgetChange['TODATE'])."</td><td align='center'>".self::get_Contrast_Data('UNDOTIME',$CaseRecord['UNDOTIME'],$budgetChange['UNDOTIME'])."</td><td align='center'>".self::get_Contrast_Data('FIRSTSETS',$CaseRecord['FIRSTSETS'],$budgetChange['FIRSTSETS'])."</td><td align='center'>".self::get_Contrast_Data('AVERAGESETS',$CaseRecord['AVERAGESETS'],$budgetChange['AVERAGESETS'])."</td><td align='center'>".self::get_Contrast_Data('SUMPROFIT',$CaseRecord['SUMPROFIT'],$budgetChange['SUMPROFIT'])."</td><td align='center' colspan='5'>".self::get_Contrast_Data('FEEINFO',$CaseRecord['FEEINFO'],$budgetChange['FEEINFO'])."</td></tr>";
					}elseif($flow_type == "lixiangshenqing"){
						$html .=  "<tr><td align='center' colspan='1'><b>".$this->_conf_case_type_remark[$SCALETYPE].$projectType." </b></td><td align='center'>".self::get_Contrast_Data('FROMDATE',$CaseRecord['FROMDATE'])."</td><td align='center'>".self::get_Contrast_Data('TODATE',$CaseRecord['TODATE'])." </td><td align='center'>".self::get_Contrast_Data('UNDOTIME',$CaseRecord['UNDOTIME'])."</td><td align='center'>".self::get_Contrast_Data('FIRSTSETS',$CaseRecord['FIRSTSETS'])."</td><td align='center'>".self::get_Contrast_Data('AVERAGESETS',$CaseRecord['AVERAGESETS'])."</td><td align='center'>".self::get_Contrast_Data('SUMPROFIT',$CaseRecord['SUMPROFIT'])."</td><td align='center' colspan='5'>".self::get_Contrast_Data('FEEINFO',$CaseRecord['FEEINFO'])."</td></tr>";

					}

					$html .=  "<tr><td align='center' colspan='1'>标准类型 </td><td align='center'>说明 </td>";
					//分销增加分销标准类型
					if($CaseRecord['SCALETYPE'] == 2){
						$html .= "<td align='center'>分销标准类型</td>";
					}
					$html .=  "<td align='center'>值 </td><td align='center'>类型 </td><td align='center' colspan='7' ></td></tr>";
					if($flow_type == "lixiangbiangeng"){
						$conff = "CID =$cid AND  PRJ_ID=".$CaseRecord['ID'];
					}else{
						$conff = "ISVALID=-1 AND PRJ_ID=".$CaseRecord['ID'];  
					}  
					$scalelist = M('Erp_feescale')->where($conff)->order('SCALETYPE ASC')->select(); 
					$arr_type = array('1'=>'单套收费标准','2'=>'中介佣金','3'=>'外部成交奖励','4'=>'中介成交奖','5'=>'置业顾问成交奖','6'=>'带看奖' );
					foreach($scalelist as $onee){
						if($flow_type == "lixiangbiangeng")
						{

							$temp = array();
							$temp['TABLE'] = 'ERP_FEESCALE';
							$temp['BID'] = $onee['ID'];//79
							$temp['CID'] = $cid;//53
							$changer->fields=array('SCALE','MTYPE','AMOUNT','STYPE');
							$scaleChange = $changer->getRecords($temp);

							$html .=  "<tr><td align='center' colspan='1'> ".$arr_type[$onee['SCALETYPE']] ."</td><td align='center'> ".self::get_Contrast_Data('SCALE',$onee['SCALE'],$scaleChange['SCALE'])."</td>";
							if($CaseRecord['SCALETYPE'] == 2){
								$html .= "<td align='center'>".self::get_Contrast_Data('MTYPE',$onee['MTYPE'],$scaleChange['MTYPE'])."</td>";
							}
							$html .=  "<td align='center'> ".self::get_Contrast_Data('AMOUNT',$onee['AMOUNT'],$scaleChange['AMOUNT'])."</td><td align='center'> ".self::get_Contrast_Data('STYPE',$onee['STYPE'],$scaleChange['STYPE'])."</td></tr>";
						}elseif($flow_type == "lixiangshenqing"){
							$html .=  "<tr><td align='center' colspan='1'> ".$arr_type[$onee['SCALETYPE']] ."</td><td align='center'> ".self::get_Contrast_Data('SCALE',$onee['SCALE'])."</td>";
							if($CaseRecord['SCALETYPE'] == 2){
								$html .= "<td align='center'>".self::get_Contrast_Data('MTYPE',$onee['MTYPE'])."</td>";
							}
							$html .=  "<td align='center'> ".self::get_Contrast_Data('AMOUNT',$onee['AMOUNT'])."</td><td align='center'> ".self::get_Contrast_Data('STYPE',$onee['STYPE'])."</td></tr>";

						}
					}


				}


				
				if($html_type==1)$html = $html . "<tr><td colspan='6' rowspan='2'  align='center'>目标分解</td><td colspan='3' align='center'>预估成交套数</td><td colspan='3' align='center' >预估导客量</td></tr><tr> <td colspan='3' align='center' > ".$Sets_Result."</td><td colspan='2' align='center'>".$Customers_Result." </td></tr>";
				$html = $html . "<tr><td colspan='4' align='center'>费用类型</td><td colspan='1' align='center' >金额(元)</td><td colspan='1' align='center' >费用占比(%)</td><td colspan='6' align='center' >费用说明</td></tr>";
				
				
				
				/** 预算费用 START**/
				$Budget_Fee = D('Erp_budgetfee')->where($Budget_Fee_condition)->select();
				 
				$FeeArr = array();
				foreach($Budget_Fee as $fee){
						$FeeArr[$fee['FEEID'].'_REMARK'] = $fee['REMARK'];
						$FeeArr[$fee['FEEID'].'_AMOUNT'] = $fee['AMOUNT'];
						$FeeArr[$fee['FEEID'].'_RATIO'] = $fee['RATIO'];
						$FeeArr[$fee['FEEID'].'_ID'] = $fee['ID'];
				}
				
				foreach($return_tr as $key=>$val)
				{
					$optt['TABLE'] = 'ERP_BUDGETFEE';
					$optt['BID'] = $FeeArr[$key.'_ID'];
					$optt['CID'] = $cid;//变更版本id
					
					$changer->fields=array('REMARK','AMOUNT','RATIO');
					$Budget_Change_Fee_List = $changer->getRecords($optt);
					
					if($flow_type == "lixiangbiangeng")
					{
						$Amount_Result = self::get_Contrast_Data('AMOUNT',$FeeArr[$key.'_AMOUNT'],$Budget_Change_Fee_List['AMOUNT']);

						$Ratio_Result = self::get_Contrast_Data('RATIO',$FeeArr[$key.'_RATIO'],$Budget_Change_Fee_List['RATIO']);

						$Remark_Result = self::get_Contrast_Data('REMARK',$FeeArr[$key.'_REMARK'],$Budget_Change_Fee_List['REMARK']);
						
					}
					elseif($flow_type == "lixiangshenqing")
					{
						$Amount_Result = self::get_Contrast_Data('AMOUNT',$FeeArr[$key.'_AMOUNT']);

						$Ratio_Result = self::get_Contrast_Data('RATIO',$FeeArr[$key.'_RATIO']);

						$Remark_Result = self::get_Contrast_Data('REMARK',$FeeArr[$key.'_REMARK']);

					}
					//利润率后加%
					$rate_Arr = array('103','107','110');

					if(in_array($key,$noinput_arr))
					{
						$html .= "<tr>".$val."<td colspan='1' align='center'>". $Amount_Result.(in_array($key,$rate_Arr) ? '%':'') ."</td> <td colspan='1' align='center' ></td><td colspan='6' align='center'>".$Remark_Result."</td> </tr>";
					}
					else
					{
						$html .= "<tr>".$val."<td colspan='1' align='center'>". $Amount_Result.(in_array($key,$rate_Arr) ? '%':'')."</td> <td colspan='1' align='center' >".$Ratio_Result."%</td><td colspan='6' align='center'>".$Remark_Result."</td> </tr>";
					}
				}
				/** END**/
			}
		}
		if($html_type==1 ){
			$html .= $this->get_WorkFlow_Html($flowid);
			  
		}
		if($html_type==1 or $html_type==3 ){
			 
			$html .= "</table>"; 
		}
		return $html;	
	}

	 /**
     * 获取独立活动.项目下活动立项信息静态页面
     *
     * @access	public
     * @param	$project_id、$flow_type
     * @return	html
     */

	public function get_Activ_Info_Html($flowid,$project_id,$flow_type,$active_id)
	{
		$db_arr = array(
			'DEPT_ID'=>'部门',
			'APPLICANT'=>'申请人',
			'TITLE'=>'活动主题',
			'ADDRESS'=>'活动地点',
			'HTIME'=>'活动开始时间',
			'HETIME'=>'活动结束时间',
			'HMODE'=>'活动模式',
			'HTYPE'=>'活动形式',
			'PRINCOME'=>'预计收入',
			'PERSONAL'=>'参加人员类型',
			'PRNUMBER'=>'预计到场人数',
			'CHARGE'=>'活动总负责人',
			'CONTENT'=>'活动内容',
			'BUDGET'=>'预算费用',
			'PROFITMARGIN'=>'利润率',
			'MYFEE'=>'我方费用',
			'BUSINESSFEE'=>'电商费用',
			'BUSINESSCLASS_ID'=>'业务类型',
            'CONTRACT_NO' => '合同号'
		);

		/** 活动详情 START **/
		$condition = "
            SELECT
              to_char(HTIME,'YYYY-MM-DD') AS HTIME,
              to_char(HETIME,'YYYY-MM-DD') AS HETIME,
              DEPT_ID,
              APPLICANT,
              TITLE,
              ADDRESS,
              HMODE,
              HTYPE,
              PRINCOME,
              PERSONAL,
              PRNUMBER,
              CHARGE,
              CONTENT,
              BUDGET,
              PROFITMARGIN,
              MYFEE,
              BUSINESSFEE,
              BUSINESSCLASS_ID";
		//项目下活动立项没有合同号
		if($flow_type == "xiangmuxiahuodong"){
			unset($db_arr['CONTRACT_NO']);
			$Activ_Sql = $condition ." FROM erp_activities WHERE ID = " . $active_id;
		}else{
			$Activ_Sql = $condition .", CONTRACT_NO
          FROM erp_activities WHERE ID = " . $active_id;
		}
		$Activ_Info = M()->query($Activ_Sql);
		
		$html = '';
		$html = $html . "<table width='90%' cellspacing='0' cellpadding='10' border='1' align='center'  style='border-collapse: collapse;' >";
		
		//标题
		$html = $html . "<tr><td colspan='12' align='center' ><h1 style='font-weight:600;font-size:16px;' >".$Activ_Info[0]['TITLE']."立项预算表 </h1></td></tr>";
		

		//基本属性
		$i = 0;
		foreach($db_arr as $k=>$v)
		{
			
			if($i%2==0)
			{	
				$html = $html . "<tr><td colspan='2' width='10%'>{$v}</td><td colspan='4' width='40%'>".self::get_Source_Data($k,$Activ_Info[0][$k]). "</td>";	
			}		
			else
			{	
				$html = $html . "<td colspan='2' width='10%'>{$v}</td><td colspan='4' width='40%'>".self::get_Source_Data($k,$Activ_Info[0][$k])."</td></tr>";
			}	
			$i ++;
		}
		
		
		/** END **/
		
		/** 立项预算 START**/

		$budget_sql = "SELECT A.ID,A.AMOUNT,B.NAME FROM erp_actibudgetfee A LEFT JOIN Erp_fee B ON A.FEE_ID = B.ID WHERE A.ACTIVITIES_ID = ".$active_id." AND A.ISVALID = -1"; 
		$Activ_Budget_Fee = M()->query($budget_sql);
		
		if($Activ_Budget_Fee)
		{
			
			foreach($Activ_Budget_Fee as $key=>$fee)
			{
				if($key == 0)
				{
					$html .= "<tr><td rowspan='".count($Activ_Budget_Fee)."' colspan='4' align='center'>预算费用</td><td colspan='4' align='center'>".$fee['NAME']."</td><td colspan='4' align='center'>".$fee['AMOUNT']."</td></tr>";
				}
				else
				{
					$html .= "<tr><td colspan='4' align='center'>".$fee['NAME']."</td><td colspan='4' align='center'>".$fee['AMOUNT']."</td></tr>";
				}
				
			}
		}
		/** END **/
		
		$html .= $this->get_WorkFlow_Html($flowid);

		$html .= "</table>"; 
		
		return $html;
	}

	 /**
     * 获取对比数据
     *
     * @access	public
     * @param	$field,$data,$changeArr=array()
     * @return	$result
     */

	public function get_Contrast_Data($field,$data,$changeArr=array())
	{
		if( $changeArr )
		{   if(in_array($field,array('ONLINE_AD_SCH', 'CONTRACT_FILE') ) )
			{
				$result = self::get_Source_Data($field,$changeArr['VALUEE']);
			}else{
				if($changeArr['ISNEW'])//变更新增
				{
					$result = self::get_Source_Data($field,$changeArr['VALUEE'])."<font style='color:red;'>[新增]</font>";
				}
				else //变更编辑
				{
					$result = self::get_Source_Data($field,$changeArr['VALUEE'])."<font style='color:red;'>[原]".self::get_Source_Data($field,$changeArr['ORIVALUEE'])."</font>";
				}
			}
		}
		else
		{
			$result = self::get_Source_Data($field,$data);
		}
		
		return $result;
		
	}

	 /**
     * 获取对比数据 总和
     *
     * @access	public
     * @param	$field,$data,$changeArr=array()
     * @return	$result
     */

	public function get_Contrast_Data_total($valuee,$orivaluee)
	{ 
		if( $valuee != $orivaluee )
		{		$result = $valuee."<font style='color:red;'>[原	]".$orivaluee."</font>";
		}
		else
		{
			$result = $orivaluee;
		}
		
		return $result;
		
	}

	 /**
     * 获取展示数据
     *
     * @access	public
     * @param	$field
     * @return	$result
     */

	public function get_Source_Data($field,$value)
	{
		
		if($field == "CUSTOMER_MAN" or $field == "APPLICANT")
		{
			$result = M("Erp_users")->where("ID = ".$value)->getField("NAME");
		}
		elseif($field == 'ONLINE_AD_SCH' or $field == 'CONTRACT_FILE')
		{
			if($value)
			{
				$attach = explode(',',$value);
				foreach($attach as $val)
				{
					$fileInfo = explode('-',$val);
					$filecode= $fileInfo[0];
					$filesize= $fileInfo[2];
					$filename= $fileInfo[1];

					$result .= '<a target="_blank" href="'.C('DOMAIN_NAME').'/index.php?s=/Upload/showfile&filecode='.$filecode.'">'.$filename.'</a><br/>';
				}
			}
			else
			{
				$result = '';
			}
		}
		elseif ($field == "CIT_ID")
		{
			$result = M("Erp_city")->where("ID = ".$value)->getField("NAME");
		}
		elseif ($field == "SALEPERMIT")
		{
			$result = $value ? "有" : "否";
		}
		elseif ($field == "ISFUNDPOOL")
		{
			if($value==='0'){
				$result = "特殊";
			}else{
				switch ($value)
				{
					case -1:
						$result = "常规";
						break;
					case 1:
						$result = "否";
						break;
					 
					default:
						$reuslt = "";
				}
			}
		} elseif ($field == "PROPERTIES")
		{
			switch ($value)
			{
				case 1:
					$result = "独家";
					break;
				case 2:
					$result = "PK";
					break;
				case 3:
					$result = "联合";
					break;
			}
		} elseif ($field == "CONDOMINIUM")
		{
			switch ($value)
			{
				case -1:
					$result = "是";
					break;
				case 0:
					$result = "否";
					break;
			}
		} elseif ($field == "MTYPE")
		{
			if($value == 0 && $value != null){
				$result = "前佣";
			}elseif($value == 1){
				$result = "后佣";
			}
		} elseif ($field == "MONEY_BET")  //
		{
			switch ($value)
			{
				case -1:
					$result = "是";
					break;
				case 0:
					$result = "否";
					break;
			 
			}
		} elseif ($field == 'USING_DECORATION_PRODUCT') {
            $result = $value == 1 ? "是" : "否";
        } elseif ($field == 'USING_FINANCIAL_PRODUCT') {
            $result = $value == 1 ? "是" : "否";
        } elseif ($field == "ISCONTRACT")
		{
			$result = $value ? "是" : "否";
		} elseif ($field == "OTHERINCOME")
		{
			$result = $value == -1? "是" : "否";
		}elseif($field == "AMOUNT" || $field == "RATIO" )
		{
			$result = sprintf("%.2f",$value);
		}elseif($field == 'DEPT_ID')
		{
			$result = M("Erp_dept")->where("ID = ".$value)->getField("DEPTNAME");
		}
		elseif($field == 'HMODE')
		{
			$result = $value == 1?"线下":"线上";
		}
		elseif($field == 'HTYPE')
		{
			$result = $this->_conf_activ_type[$value];
		}
		elseif($field == 'PROFITMARGIN')
		{
			$result = $value."%";
		}
		elseif($field == 'BUSINESSCLASS_ID')
		{
			$result = $this->_conf_case_type_remark[$value];
		}
		elseif( in_array($field,array('FROMDATE','TODATE','UNDOTIME') ) )
		{
			$result = oracle_date_format($value);
		}
		elseif($field == 'STYPE')
		{
			if($value) $result = '百分比';
			else $result = '金额';
		 
		}
		elseif($field == 'SALEMETHODID')
		{
			$saleMethod = D('Member')->get_conf_member_source_remark();
			$result = $saleMethod[$value];
		}
		else
		{
			$result = $value;
		}
		
		return $result;
	}


	 /**
     * 判断项目类型
     *
     * @access	public
     * @param	$project_id、$cid
     * @return	html
     */

	public function get_Project_Type($project_id,$scaleType,$cid = 0, $bid = 0)
	{
		$model = new Model();

		if($cid)//变更
		{
			if($scaleType == 1) {
				$result = $model->query('select isroutine(' . $project_id . ',' . $cid . ', ' . $bid . ') as data from dual');
			}else if($scaleType == 2){
				$result = $model->query('select isfxroutine(' . $project_id . ',' . $cid . ', ' . $bid . ') as data from dual');
			}else if($scaleType == 8){
				$result = $model->query('select isfwfscroutine(' . $project_id . ',' . $cid . ', ' . $bid . ') as data from dual');
			}
		}
		else
		{
			if($scaleType == 1) {
				$result = $model->query('select isroutine(' . $project_id . ') as data from dual');
			}else if($scaleType == 2){
				$result = $model->query('select isfxroutine(' . $project_id . ') as data from dual');
			}else if($scaleType == 8) {
				$result = $model->query('select isfwfscroutine(' . $project_id . ') as data from dual');
			}
		}
		
		if($result[0]['DATA'])
		{
			$type = "<font style='color:red;'>[非常规]</font>";
		}
		else
		{
			$type = "<font style='color:red;'>[常规]</font>";
		}

		return $type;
	}

	 /**
     * 获取流程数据
     *
     * @access	public
     * @param	$flowid
     * @return	html
     */

	public function get_WorkFlow_Html($flowid = '')
	{
		Vendor('Oms.workflow');
		$workflow = new workflow();

		$flowStep = $workflow->chartworkflow($flowid);
		
		$html = '';
		if($flowStep)
		{
			$flowInfo = $fileHtml = '';
				
			foreach($flowStep as $step)
			{
				if($step['FILES'])
				{
					$attach = explode(',',$step['FILES']);
					foreach($attach as $key=>$val)
					{
						if($val)
						{
							$fileInfo = explode('-',$val);
							$filecode= $fileInfo[0];
							$filesize= $fileInfo[2];
							$filename= $fileInfo[1];

							$fileHtml .= '<a target="_blank" href="'.C('DOMAIN_NAME').'/index.php?s=/Upload/showfile&filecode='.$filecode.'">'.$filename.'</a><br/>';
						}
					}
				}
				$flowInfo .= '<tr><td width="20%" align="center">第'.$step["STEP"].'</span>步</td>
				<td width="70%"><div style="text-align:left;">'.$this->_conf_flow_status[$step['STATUS']].'<br/>审批意见:'.$step["DEAL_INFO"].'<br/><small>';
				
				if($step["S_TIME"])
				{
					$flowInfo .= "开始时间：".$step["S_TIME"];
				}
				
				if($step["E_TIME"])
				{
					$flowInfo .= "结束时间：".$step["E_TIME"];
				}
				
				$flowInfo .= "经办人：".$step['NAME']."</small></div></td></tr>";
				
			}
			
			$html .= "<tr><td colspan='12'>流程信息</td></tr><tr><td colspan='2'>流程步骤</td><td colspan='10'><table width='90%' cellspacing='0' cellpadding='10' border='1' align='center'  style='border-collapse: collapse;' >".$flowInfo."</table></td></tr><tr><td colspan='2'>附件列表</td><td colspan='10'>".$fileHtml."</td></tr>";	
		}
		
		return $html;
	}


}
