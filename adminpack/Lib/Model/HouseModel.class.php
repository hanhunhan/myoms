<?php

/**
 * ������ϢMODEl
 *
 * @author liuhu
 */
class HouseModel extends Model {
    
    protected $tablePrefix  =   'ERP_';
    protected $tableName = 'HOUSE';
    
	 /***��Ŀҵ������***/
    private  $_conf_case_type_remark = array(
                                            1 => '����',
                                            2 => '����',
                                            3 => 'Ӳ��',
                                            4 => '�',
                                            5 => '��Ʒ',
                                            7 => '��Ŀ�',
											8 => '���ҷ��ճ�',
                                        );
	/** ���ʽ**/
	private $_conf_activ_type = array(
									1=>"���̻",
									2=>"Ʒ���ƹ�",
									3=>"��Ŀ�ƹ�",
									4=>"���ֻ",
									5=>"��ѵ�",
									6=>"����",
								);
	/** ���ʽ**/
	private $_conf_flow_status = array(
									1=>"δ��ʼδ����",
									2=>"������",
									3=>"���",
									4=>"���"	
								);
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }

    /**
     * ������Ŀ��Ż�ȡ��Ŀ�Ƿ��ʽ����Ŀ
     * @param int $prjid   ��Ŀ���
     * @return boolean TRUE�ʽ��\FALSE���ʽ�� 
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
     * �ж��Ƿ�Ϊ�ʽ����Ŀ
     * @param int $isfundpool_val   �Ƿ��ʽ����ֵ
     * @return boolean TRUE�ʽ��\FALSE���ʽ�� 
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
     * ������Ŀ��Ż�¥����Ϣ
     *
     * @access	public
     * @param	mixed  $prj_ids ¥�̱��
     * @param   array $search_field ��ѯ�ֶ�
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
     * ��ȡ��ĿԤ����Ϣ��̬ҳ��
     *
     * @access	public
     * @param	$project_id��$flow_type   html_type��1 Ĭ��ȫ��  2 ��Ŀ��Ϣ  3Ԥ����Ϣ��
     * @return	html
     */

	public function get_House_Info_Html($flowid,$project_id,$flow_type,$cid=0,$html_type=1)
	{
		$db_arr = array(
			'PROJECT_ID'=>'��Ŀ���',
			'CUSTOMER_MAN'=>'��Ŀ����',
			'CIT_ID'=>'��Ŀ���ڳ���',
			'CONTRACT_NUM'=>'��ͬ���',
			'REL_PROPERTY'=>'����¥��',
			'PRO_LISTID'=>'����ID',
			'DEV_ENT'=>'������ҵ',
			'PROPERTY_CLASS'=>'��ҵ���',
			'PRO_ADDR'=>'��Ŀ��ַ',
			'PRO_NAME'=>'��Ŀ����',
			'TLF_SOURCE'=>'��������Դ',
			'TLF_DISCOUNT'=>'�������Ż�',
			'SALEPERMIT'=>'��Ŀ�������֤',
			'ISFUNDPOOL'=>'�Ƿ��ʽ����Ŀ',
			'PAYMENT_SECURITY'=>'֧����֤��',
			'FPSCALE'=>'�ʽ�ر���',
			'RETURN_CONTENT'=>'��������',
			'SPECIALFPDESCRIPTION'=>'�����ʽ������',
			
			'PRO_ADV'=>'��Ŀ����Դ����',
			'PRO_INF'=>'��Ŀ����Դ����',
			
			

			 'ISCONTRACT'=>'��ͬ�ܷ��ջ�',
			
			
			'MONEY_BET'=>'�Ƿ��ʽ�Զ�',
			'PROPERTIES'=>'������Ŀ����',
			'CONDOMINIUM'=>'�Ƿ��蹫���˻�',


			'ONLINE_AD_SCH'=>'������ڸ���',
			'CONTRACT_FILE'=>'��Ŀ��ͬ����',
            'USING_DECORATION_PRODUCT' => 'ʹ��װ�޲�Ʒ',
            'USING_FINANCIAL_PRODUCT' => 'ʹ�ý��ڲ�Ʒ',
			'OTHERINCOME' =>'�Ƿ���������',
				'PRO_INFO'=>'��ע',
			
		);

		$return_tr = array(
			'39'=>'<td rowspan="46" colspan="1">�����������</td><td colspan="1">���ͷ����</td><td colspan="2">�н��</td>',
			'41'=>'<td rowspan="2" colspan="1">����Ӫ����</td><td colspan="2">���ŷ�</td>',
			'42'=>'<td colspan="2">�绰��</td>',
			'45'=>'<td rowspan="9" colspan="1">������</td><td rowspan="3">���ط�</td><td>����/�̳�</td>',
			'46'=>'<td>��С��</td>',
			'47'=>'<td>д��¥</td>',
			'49'=>'<td rowspan="2">�⳵��(����)</td><td>��ͳ�</td>',
			'50'=>'<td>���⳵</td>',
			'51'=>'<td colspan="2">�����(����)</td>',
			'53'=>'<td>�ƹ��</td><td>SEO/SEM�ƹ�</td>',
			'54'=>'<td colspan="2">����ů����</td>',
			'55'=>'<td colspan="2">����ʳƷ��</td>',
			'57'=>'<td rowspan="2" colspan="1">��Ա����</td><td colspan="2">��˾Ա��</td>',
			'58'=>'<td colspan="2">��ְ��Ա</td>',
			'60'=>'<td rowspan="4" colspan="1">ҵ���</td><td colspan="2">ҵ�����</td>',
			'61'=>'<td colspan="2">��������</td>',
			'62'=>'<td colspan="2">ʵ��Ӧ��</td>',
			'63'=>'<td colspan="2">���÷�</td>',
			'65'=>'<td rowspan="4" colspan="1">������</td><td colspan="2">����Ʒ</td>',
			'66'=>'<td colspan="2">��չ��</td>',
			'67'=>'<td colspan="2">��ҳ</td>',
			'68'=>'<td colspan="2">Xչ��</td>',
			'70'=>'<td rowspan="5" colspan="1">�ⲿ����</td><td colspan="2">����</td>',
			'71'=>'<td colspan="2">LED</td>',
			'72'=>'<td colspan="2">����/����</td>',
			'73'=>'<td colspan="2">��̨</td>',
			'74'=>'<td colspan="2">��ֽ/��־</td>',
			'76'=>'<td rowspan="4" colspan="1">������</td><td colspan="2">����</td>',
			'77'=>'<td colspan="2">��ҵ����</td>',
			'78'=>'<td colspan="2">�ͻ�</td>',
			'79'=>'<td colspan="2">����</td>',
			'80'=>'<td colspan="3">֧������������</td>',
			'82'=>'<td colspan="1">��Ŀ�ֳ�</td><td colspan="2">����ֳ�</td>',
			'84'=>'<td rowspan="4" colspan="1">������</td><td colspan="2">�ϴ���</td>',
			'85'=>'<td colspan="2">�´���</td>',
			'86'=>'<td colspan="2">�н����</td>',
			'87'=>'<td colspan="2">��������</td>',
			'89'=>'<td colspan="1">�ɽ���</td><td colspan="2">�ɽ�����</td>',
			'91'=>'<td colspan="1">�ڲ�Ӷ��</td><td colspan="2">�ڲ����</td>',
			'93'=>'<td colspan="1">�ⲿӶ��</td><td colspan="2">�ⲿ����</td>',
			'95'=>'<td colspan="1">POS������</td><td colspan="2">POS������</td>',
			'96'=>'<td colspan="3">˰��(֧�����������õ�10%)</td>',
			'97'=>'<td colspan="3">����</td>',
			'108'=>'<td colspan="3">���ֳɱ�</td>',
			'109'=>'<td colspan="3">��������</td>',
			'110'=>'<td colspan="3">����������</td>',

			'101'=>'<td rowspan="3" colspan="1">˰����Ŀ���(���ο�)</td><td colspan="3">���ʽ������Ŀ˰��</td>',
			'102'=>'<td colspan="3">˰����Ŀ����</td>',
			'103'=>'<td colspan="3">˰����Ŀ������</td>',
			'98'=>'<td rowspan="4" colspan="1">�����������</td><td colspan="3">���Ԥ�㣨�ۺ�ۣ�</td>',
			'99'=>'<td colspan="3">�ز���ҳ���͹�棨�ۺ�</td>',
			'106'=>'<td colspan="3">�۳�����+����֧������</td>',
			'107'=>'<td colspan="3">�۳�����+����֧��������</td>',
		);
		$noinput_arr = array(98,99,101,102,103,106,107,108,109,110);
		/** ��Ŀ��Ϣ  START**/
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

		//�ж��Ƿ��Ƿ�����Ŀ
		$project = D('Erp_project')->where("ID=$project_id")->find();

		//$projectType = self::get_Project_Type($project_id,$record_id);
		$html = '';
		$width = $html_type==1 ? '90%' : '900';
		$html = $html . "<table width='$width' cellspacing='0' cellpadding='10' border='1' align='center'  style='border-collapse: collapse;' >";
		$title[1] = '��Ŀ��Ϣ';
		$title[2] = '��Ŀ��Ϣ'; 
		$title[3] = '����Ԥ���';
		//����
		$html = $html . "<tr><td colspan='12' align='center' ><h1 style='font-weight:600;font-size:16px;' >".self::get_Contrast_Data('PRO_NAME',$houseData['PRO_NAME'],$houseChange['PRO_NAME']).$title[$html_type]. " </h1></td></tr>";
		
		if($html_type==1 or $html_type==2){
			//��������
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
		
		//��Ŀҵ������
		$projectCase = M("Erp_case")->where("PROJECT_ID = ".$project_id)->select();
		
		foreach($projectCase as $case)
		{
			$CaseArr[] = $case['ID'];
		}
		
		$CaseStr = implode(',',$CaseArr); 
		if($html_type==1 or $html_type==3){
			//����Ԥ��
			//$project_Case_Buget = M("Erp_prjbudget")->where("CASE_ID IN ($CaseStr)")->select();
			$project_Case_Buget =  M()->query("select T.*,to_char(T.FROMDATE,'yyyy-mm-dd hh24:mi:ss') as FROMDATE,to_char(T.TODATE,'yyyy-mm-dd hh24:mi:ss') as TODATE,to_char(T.UNDOTIME,'yyyy-mm-dd hh24:mi:ss') as UNDOTIME from ERP_PRJBUDGET T where CASE_ID IN ($CaseStr)"); //var_dump($project_Case_Buget);
			//print_r($project_Case_Buget);exit;	
			foreach($project_Case_Buget as $keyy=>$CaseRecord)
			{
				
				
				
				//�жϵ��� ���� or �ǳ���
				$projectType ='';
				/** Ŀ��ֽ�  START **/

				//����󵼿����������Ԥ�Ƴɽ�����
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
					if($CaseRecord['SCALETYPE'] == 1 or $CaseRecord['SCALETYPE'] == 2 or  $CaseRecord['SCALETYPE'] == 8 ) //���̷������ҷ��ճ������жϳ��� or �ǳ���
					{ 
						$projectType = self::get_Project_Type($project_id,$CaseRecord['SCALETYPE'],$cid, $CaseRecord['ID']);
					}
					$Sets_Result = $change_Sets ? $change_Sets."<font style='color:red;'>[ԭ]".$Sets."</font>" : $Sets ;
					$Customers_Result = $change_Customers ? $change_Customers."<font style='color:red;'>[ԭ]".$Customers."</font>" : $Customers ;
					
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
				
				//$html .= "<tr><td colspan='2'>ҵ������</td><td colspan='10'>".$this->_conf_case_type_remark[$CaseRecord['SCALETYPE']].$projectType."</td></tr>";

				if($html_type==3 or $html_type==1){
					$temp = array();
					$temp['TABLE'] = 'ERP_PRJBUDGET';
					$temp['BID'] = $CaseRecord['ID'];//79
					$temp['CID'] = $cid;//53
					$changer->fields=array('SCALETYPE','FROMDATE','TODATE','UNDOTIME','FIRSTSETS','AVERAGESETS','SUMPROFIT','FEEINFO');
					$budgetChange = $changer->getRecords($temp);	
					$SCALETYPE = self::get_Contrast_Data('SCALETYPE',$CaseRecord['SCALETYPE'],$budgetChange['SCALETYPE']);
					if($keyy>0) $html .=  "<tr><td colspan='12'> </td></tr>";
					$html .=  "<tr><td align='center' colspan='1'>ҵ������ </td><td align='center'>ִ����ʼ���� </td><td align='center'>ִ����ֹ���� </td><td align='center'>����ʱ�� </td><td align='center'>�״�ȥ������</td><td align='center'>�¾�ȥ������</td><td align='center'>Ԥ��������</td><td align='center' colspan='5' width='30%'>�շѱ�׼˵��</td></tr>";
					if($flow_type == "lixiangbiangeng")
					{ 
						$html .=  "<tr><td align='center' colspan='1'><b>".$this->_conf_case_type_remark[$SCALETYPE].$projectType."</b></td><td align='center'>".self::get_Contrast_Data('FROMDATE',$CaseRecord['FROMDATE'],$budgetChange['FROMDATE'])."</td><td align='center'>".self::get_Contrast_Data('TODATE',$CaseRecord['TODATE'],$budgetChange['TODATE'])."</td><td align='center'>".self::get_Contrast_Data('UNDOTIME',$CaseRecord['UNDOTIME'],$budgetChange['UNDOTIME'])."</td><td align='center'>".self::get_Contrast_Data('FIRSTSETS',$CaseRecord['FIRSTSETS'],$budgetChange['FIRSTSETS'])."</td><td align='center'>".self::get_Contrast_Data('AVERAGESETS',$CaseRecord['AVERAGESETS'],$budgetChange['AVERAGESETS'])."</td><td align='center'>".self::get_Contrast_Data('SUMPROFIT',$CaseRecord['SUMPROFIT'],$budgetChange['SUMPROFIT'])."</td><td align='center' colspan='5'>".self::get_Contrast_Data('FEEINFO',$CaseRecord['FEEINFO'],$budgetChange['FEEINFO'])."</td></tr>";
					}elseif($flow_type == "lixiangshenqing"){
						$html .=  "<tr><td align='center' colspan='1'><b>".$this->_conf_case_type_remark[$SCALETYPE].$projectType." </b></td><td align='center'>".self::get_Contrast_Data('FROMDATE',$CaseRecord['FROMDATE'])."</td><td align='center'>".self::get_Contrast_Data('TODATE',$CaseRecord['TODATE'])." </td><td align='center'>".self::get_Contrast_Data('UNDOTIME',$CaseRecord['UNDOTIME'])."</td><td align='center'>".self::get_Contrast_Data('FIRSTSETS',$CaseRecord['FIRSTSETS'])."</td><td align='center'>".self::get_Contrast_Data('AVERAGESETS',$CaseRecord['AVERAGESETS'])."</td><td align='center'>".self::get_Contrast_Data('SUMPROFIT',$CaseRecord['SUMPROFIT'])."</td><td align='center' colspan='5'>".self::get_Contrast_Data('FEEINFO',$CaseRecord['FEEINFO'])."</td></tr>";

					}

					$html .=  "<tr><td align='center' colspan='1'>��׼���� </td><td align='center'>˵�� </td>";
					//�������ӷ�����׼����
					if($CaseRecord['SCALETYPE'] == 2){
						$html .= "<td align='center'>������׼����</td>";
					}
					$html .=  "<td align='center'>ֵ </td><td align='center'>���� </td><td align='center' colspan='7' ></td></tr>";
					if($flow_type == "lixiangbiangeng"){
						$conff = "CID =$cid AND  PRJ_ID=".$CaseRecord['ID'];
					}else{
						$conff = "ISVALID=-1 AND PRJ_ID=".$CaseRecord['ID'];  
					}  
					$scalelist = M('Erp_feescale')->where($conff)->order('SCALETYPE ASC')->select(); 
					$arr_type = array('1'=>'�����շѱ�׼','2'=>'�н�Ӷ��','3'=>'�ⲿ�ɽ�����','4'=>'�н�ɽ���','5'=>'��ҵ���ʳɽ���','6'=>'������' );
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


				
				if($html_type==1)$html = $html . "<tr><td colspan='6' rowspan='2'  align='center'>Ŀ��ֽ�</td><td colspan='3' align='center'>Ԥ���ɽ�����</td><td colspan='3' align='center' >Ԥ��������</td></tr><tr> <td colspan='3' align='center' > ".$Sets_Result."</td><td colspan='2' align='center'>".$Customers_Result." </td></tr>";
				$html = $html . "<tr><td colspan='4' align='center'>��������</td><td colspan='1' align='center' >���(Ԫ)</td><td colspan='1' align='center' >����ռ��(%)</td><td colspan='6' align='center' >����˵��</td></tr>";
				
				
				
				/** Ԥ����� START**/
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
					$optt['CID'] = $cid;//����汾id
					
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
					//�����ʺ��%
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
     * ��ȡ�����.��Ŀ�»������Ϣ��̬ҳ��
     *
     * @access	public
     * @param	$project_id��$flow_type
     * @return	html
     */

	public function get_Activ_Info_Html($flowid,$project_id,$flow_type,$active_id)
	{
		$db_arr = array(
			'DEPT_ID'=>'����',
			'APPLICANT'=>'������',
			'TITLE'=>'�����',
			'ADDRESS'=>'��ص�',
			'HTIME'=>'���ʼʱ��',
			'HETIME'=>'�����ʱ��',
			'HMODE'=>'�ģʽ',
			'HTYPE'=>'���ʽ',
			'PRINCOME'=>'Ԥ������',
			'PERSONAL'=>'�μ���Ա����',
			'PRNUMBER'=>'Ԥ�Ƶ�������',
			'CHARGE'=>'��ܸ�����',
			'CONTENT'=>'�����',
			'BUDGET'=>'Ԥ�����',
			'PROFITMARGIN'=>'������',
			'MYFEE'=>'�ҷ�����',
			'BUSINESSFEE'=>'���̷���',
			'BUSINESSCLASS_ID'=>'ҵ������',
            'CONTRACT_NO' => '��ͬ��'
		);

		/** ����� START **/
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
		//��Ŀ�»����û�к�ͬ��
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
		
		//����
		$html = $html . "<tr><td colspan='12' align='center' ><h1 style='font-weight:600;font-size:16px;' >".$Activ_Info[0]['TITLE']."����Ԥ��� </h1></td></tr>";
		

		//��������
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
		
		/** ����Ԥ�� START**/

		$budget_sql = "SELECT A.ID,A.AMOUNT,B.NAME FROM erp_actibudgetfee A LEFT JOIN Erp_fee B ON A.FEE_ID = B.ID WHERE A.ACTIVITIES_ID = ".$active_id." AND A.ISVALID = -1"; 
		$Activ_Budget_Fee = M()->query($budget_sql);
		
		if($Activ_Budget_Fee)
		{
			
			foreach($Activ_Budget_Fee as $key=>$fee)
			{
				if($key == 0)
				{
					$html .= "<tr><td rowspan='".count($Activ_Budget_Fee)."' colspan='4' align='center'>Ԥ�����</td><td colspan='4' align='center'>".$fee['NAME']."</td><td colspan='4' align='center'>".$fee['AMOUNT']."</td></tr>";
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
     * ��ȡ�Ա�����
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
				if($changeArr['ISNEW'])//�������
				{
					$result = self::get_Source_Data($field,$changeArr['VALUEE'])."<font style='color:red;'>[����]</font>";
				}
				else //����༭
				{
					$result = self::get_Source_Data($field,$changeArr['VALUEE'])."<font style='color:red;'>[ԭ]".self::get_Source_Data($field,$changeArr['ORIVALUEE'])."</font>";
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
     * ��ȡ�Ա����� �ܺ�
     *
     * @access	public
     * @param	$field,$data,$changeArr=array()
     * @return	$result
     */

	public function get_Contrast_Data_total($valuee,$orivaluee)
	{ 
		if( $valuee != $orivaluee )
		{		$result = $valuee."<font style='color:red;'>[ԭ	]".$orivaluee."</font>";
		}
		else
		{
			$result = $orivaluee;
		}
		
		return $result;
		
	}

	 /**
     * ��ȡչʾ����
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
			$result = $value ? "��" : "��";
		}
		elseif ($field == "ISFUNDPOOL")
		{
			if($value==='0'){
				$result = "����";
			}else{
				switch ($value)
				{
					case -1:
						$result = "����";
						break;
					case 1:
						$result = "��";
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
					$result = "����";
					break;
				case 2:
					$result = "PK";
					break;
				case 3:
					$result = "����";
					break;
			}
		} elseif ($field == "CONDOMINIUM")
		{
			switch ($value)
			{
				case -1:
					$result = "��";
					break;
				case 0:
					$result = "��";
					break;
			}
		} elseif ($field == "MTYPE")
		{
			if($value == 0 && $value != null){
				$result = "ǰӶ";
			}elseif($value == 1){
				$result = "��Ӷ";
			}
		} elseif ($field == "MONEY_BET")  //
		{
			switch ($value)
			{
				case -1:
					$result = "��";
					break;
				case 0:
					$result = "��";
					break;
			 
			}
		} elseif ($field == 'USING_DECORATION_PRODUCT') {
            $result = $value == 1 ? "��" : "��";
        } elseif ($field == 'USING_FINANCIAL_PRODUCT') {
            $result = $value == 1 ? "��" : "��";
        } elseif ($field == "ISCONTRACT")
		{
			$result = $value ? "��" : "��";
		} elseif ($field == "OTHERINCOME")
		{
			$result = $value == -1? "��" : "��";
		}elseif($field == "AMOUNT" || $field == "RATIO" )
		{
			$result = sprintf("%.2f",$value);
		}elseif($field == 'DEPT_ID')
		{
			$result = M("Erp_dept")->where("ID = ".$value)->getField("DEPTNAME");
		}
		elseif($field == 'HMODE')
		{
			$result = $value == 1?"����":"����";
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
			if($value) $result = '�ٷֱ�';
			else $result = '���';
		 
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
     * �ж���Ŀ����
     *
     * @access	public
     * @param	$project_id��$cid
     * @return	html
     */

	public function get_Project_Type($project_id,$scaleType,$cid = 0, $bid = 0)
	{
		$model = new Model();

		if($cid)//���
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
			$type = "<font style='color:red;'>[�ǳ���]</font>";
		}
		else
		{
			$type = "<font style='color:red;'>[����]</font>";
		}

		return $type;
	}

	 /**
     * ��ȡ��������
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
				$flowInfo .= '<tr><td width="20%" align="center">��'.$step["STEP"].'</span>��</td>
				<td width="70%"><div style="text-align:left;">'.$this->_conf_flow_status[$step['STATUS']].'<br/>�������:'.$step["DEAL_INFO"].'<br/><small>';
				
				if($step["S_TIME"])
				{
					$flowInfo .= "��ʼʱ�䣺".$step["S_TIME"];
				}
				
				if($step["E_TIME"])
				{
					$flowInfo .= "����ʱ�䣺".$step["E_TIME"];
				}
				
				$flowInfo .= "�����ˣ�".$step['NAME']."</small></div></td></tr>";
				
			}
			
			$html .= "<tr><td colspan='12'>������Ϣ</td></tr><tr><td colspan='2'>���̲���</td><td colspan='10'><table width='90%' cellspacing='0' cellpadding='10' border='1' align='center'  style='border-collapse: collapse;' >".$flowInfo."</table></td></tr><tr><td colspan='2'>�����б�</td><td colspan='10'>".$fileHtml."</td></tr>";	
		}
		
		return $html;
	}


}
