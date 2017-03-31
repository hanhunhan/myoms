<?php
/**
 +------------------------------------------------------------------------------
 * Field �Զ����ֶ���
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Field.php  2015-04-15   $
 +------------------------------------------------------------------------------
 */
class Field{
	 
    protected $FORMNO           = 0; //������
	protected $FIELDNAME           = null;//�ֶ�����
	protected $FIELDMEANS            = null ;//�ֶκ���
	protected $LINENO            = 1 ;//�к�
	protected $COLNO            = 1 ;//�к�
	protected $FIELDTYPE            = 1 ;//�ֶ�����  1-���� 2-���� 3-���� 31-ʱ��  4-�߼� 5-��ע 6-���֤ 7-���� 8-�ֻ����� 9 checkbox raido  select ��

	protected $READONLY            = 0;//ֻ��  -1 ����0 ��Ĭ�ϣ�
	protected $NOTNULL            = 0 ;// �ǿ� -1 ����0 ��Ĭ�ϣ�
	protected $GRIDVISIBLE            = -1 ;//����ɼ�  -1 ����Ĭ�ϣ� 0 ��
	protected $FORMVISIBLE            = -1 ;//ҳ��ɼ�  -1 ����Ĭ�ϣ� 0 ��
	protected $SORT            = -1 ;//������  -1 ����Ĭ�ϣ� 0 ��  
	protected $FILTER            = -1 ;//�ɹ���  -1 ����Ĭ�ϣ� 0 ��    
	 


	protected $FIELDLENGTH            = 0 ;//�ֶγ���
	protected $EDITLENGTH            = 0 ;//�༭�򳤶�
	protected $EDITMAXLENGTH            = 0 ;//�༭�����¼�볤��
	protected $EDITROWS                    =3;//�༭��߶�
	protected $DEFAULTVALUE            = null ;//Ĭ��ֵ 
	protected $EDITTYPE            = 1 ; /*1-textbox   
												'12-���ֱ༭ 
												'13-���ڱ༭ 
												'131-ʱ��༭
												21-listbox(sql) 22-listbox(array)
												23-listbox�༭(sql������ʾ) 
												'211-listbox�༭(sql) 
												'221-listbox�༭(araay)
												31-checkbox sql 32 checkbox array
												41-radio(sql) 42-radio(array)
												5-textarea '
												6-password
												'Ĭ�ϣ�1
												'��������       �༭����
												1        1(default),21,22,41,42,6
												2        1(default),21,22,41,42,6
												3        1(default),21,22,41,42
												4        21,22,3(default),41,42
												5           5(default),51


												*/
	protected $LISTSQL            = null ;//�����б�SQL select
	protected $DECLENGTH            = 0 ;//С�����λ�� ?
	protected $ALIGN            = 'center' ;//���뷽ʽ left-�� center-�� right-��
	protected $EDITFORMAT            = null ;//�༭��ʽ  �������ڣ�ȥ�������������ֶΣ�����"###,###.##"��"yyyy/mm/dd"  ?

	protected $LISTCHAR            = null ;// �����б��ַ���  ��^�ָ�������^��^1^Ů^0 ��Ů����ʾֵ��1��0�Ǳ���ֵ

	protected $PARENTCOL            = null ;// ���������ֶ� ˵���������ݹ�������(��Ϊ�¼�)���ֶ�  
	protected $HELPTEXT            = null ;//��������
	protected $TRANSFER            = null ;//�ֶ�ת��  
	protected $GRIDTD            = null ;//GRID  TD����
	protected $INPUTPROPERTY            = null ;//INPUT����
	protected $DBBOUND            = -1 ;//���ݿ���� -1 ����Ĭ�ϣ� 0 ��   
	protected $CHKADD            = null ;//�ֶ�У��ű�
	protected $CHKADDERRMSG            = null ;//�ֶ�У�������ʾ
	protected $UNIT            = null ;//������λ
	protected $SETVALUE        =null; //��̬��ֵ ***���� ��
	protected $ISVIRTUAL        =null; //�Ƿ������ֶ� -1���� 0 ��ʵ ***����
	protected $CHILDREN        =null; // ���������ֶ�***���� ��
	protected $TreeData         =null;// ���νṹ ***���� ��
	//protected $INPUTHTML            = null ;//�ֶα༭��html
	protected $GRIDTDWIDTH     = null;
	protected $ENCRY          =null;//�ֶ�ֵ��*�ŵ���ֹλ�� ,��ֵֹ�м��ö��Ÿ���
	protected $UPLOADURL          ='index.php?s=/Upload/save2oracle/';//�Զ����ļ��ϴ�����URL 
	 
	/**
     +----------------------------------------------------------
     * �ܹ����� ȡ��ģ�����ʵ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct() {
        
    }
	 
	/**
     +----------------------------------------------------------
     *  ���ɱ༭��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function creatFormColsHtml($model,$param=null) {
		$timestamp = time();
		$attr = $param['attr']  ? $param['attr']:'';
		$align = $this->ALIGN ? " align='$this->ALIGN' " : '';
        $GRIDTD = $this->GRIDTD ? " class='$this->GRIDTD' " :  '';
		$GRIDTD .= $align;
		$readOnly =  $this->READONLY==-1  ? " readonly='readonly' " : '';

        // 31, 32 = checkbox�༭���ͣ�
        // 41, 42 = radio�༭����
        if (in_array($this->EDITTYPE, array(31, 32, 41, 42))) {
            $inputproperty= $this->INPUTPROPERTY ? " class='$this->INPUTPROPERTY' " :  '';
        } else {
            $inputproperty= $this->INPUTPROPERTY ? " class='$this->INPUTPROPERTY form-control' " :  ' class="form-control" ';
        }
		$helpText = $this->HELPTEXT ? " tip='$this->HELPTEXT' altercss='gray' " : '';
		$notNull = $this->NOTNULL == -1 ? '' :' ignore="ignore" ';
		$chkaddErrmsg = $this->CHKADDERRMSG ? " errormsg='$this->CHKADDERRMSG' ": '';
		$chkadd = $this->CHKADD ? " datatype='$this->CHKADD'" :'';
		$childrenCol = $this->CHILDREN ? "onchange=\"getNextcol('$this->CHILDREN',this.options[this.options.selectedIndex].value,'$param[PKFIELD]','')\" " :'';
		$inputproperty .= $readOnly.$helpText.$notNull.$chkaddErrmsg.$chkadd.$childrenCol;
		 
		$defaultValue =  is_null($param['value']) ?$this->DEFAULTVALUE  :$param['value']   ;  
		$defaultValue = !is_null($this->SETVALUE) ? $this->SETVALUE :$defaultValue; 
		
		//var_dump($this->FIELDMEANS).var_dump($defaultValue);
		if($param['FormeditType']==1 && $this->READONLY!=-1) {
			$spanfirst = 'spanhidden';
			$spansecond = 'spanshow';
			
		}else {
			
			$spanfirst = 'spanshow';
			$spansecond = 'spanhidden';
		}  
		$versionC = $param['ISNEW']==-1?'[��]':'[ԭ]'.(is_null($param['ORIVALUEE'])?'δ����':'');
		
		$this->EDITLENGTH = $param['MAXLENGTH'] && $param['MAXLENGTH']<$this->EDITLENGTH ? $param['MAXLENGTH']:$this->EDITLENGTH;
		switch($this->FIELDTYPE){
			case 1:
                $FIELDTYPE = 'datatype="s"';
				break;
			case 2:
				$FIELDTYPE = 'datatype="n"';
				if(substr($defaultValue ,0,1)=='.')$defaultValue = '0'.$defaultValue ;
				break;
			case 3:
				$FIELDTYPE = 'datatype="/\d{4}-\d{2}-\d{2}/"';
				break;
			case 31:
				$FIELDTYPE = 'datatype="/\d{1,2}:\d{1,2}:\d{1,2}/"';
				break;
			case 32:
				$FIELDTYPE = 'datatype="/\d{4}-\d{2}-\d{2}\s+\d{1,2}:\d{1,2}:\d{1,2}/"';
				break;
			case 4:
				$FIELDTYPE = 'datatype="/[-1|0]/"';
				break;
			case 5:
				$FIELDTYPE = 'datatype="s"';
				break;
			case 6:
				$FIELDTYPE = 'datatype="carno"';
				break;
			case 7:
				$FIELDTYPE = 'datatype="e"';
				break;
			case 8:
				$FIELDTYPE = 'datatype="m"';
				break;
			case 9:
				$FIELDTYPE= 'datatype="*"';
				break;
		}
		if($param['FieldEncry']==-1 &&  $this->ENCRY){   
			$defaultValue = (string)$defaultValue;  
			list($ENCRY_statr,$ENCRY_end) = explode(',',$this->ENCRY); 
		 
			for($ii=0;$ii<strlen($defaultValue);$ii++){  
				if($ii>=$ENCRY_statr && $ii<=$ENCRY_end){ 
					$defaultValue[$ii] = '*'; 
				}
			}
		}
		if($param['FORMTYPE'] =='FORM' ){
			if($this->NOTNULL == -1) $xing  = '<font style="color:#f00;font-size:18px; ">*</font>';
			$INPUTHTML =   "<td $GRIDTD class='leftlable'  >".$this->FIELDMEANS.$xing .'</td>';
			$fieldName =   $this->FIELDNAME.$param['houzui'];
			
		}else{
			$INPUTHTML = '';
			$fieldName =  $param['PKFIELD'].'_'.$this->FIELDNAME.$param['houzui'];
		}
		//$hidden_old_val = '<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'">';
		if($this->TRANSFER && $this->DBBOUND==-1){
			 $INPUTHTML.='<td '.$GRIDTD.$attr.'> '.$this->TRANSFER.'</td>';
		}else{
			$param['ORIVALUEE'] = is_null($param['ORIVALUEE'])?'':$param['ORIVALUEE'];

			if($param['FORMTYPE'] == 'FORM') //���� or ���� 
			{
				$breakCss = "word-wrap:break-word;width:90%";
			}
			else
			{
				$breakCss = "word-break:normal; width:auto; display:block; white-space:pre-wrap;word-wrap : break-word;overflow:hidden;width:".$this->GRIDTDWIDTH."px";
			}

			switch($this->EDITTYPE){
				case 1://�ı�
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$param['ORIVALUEE'].'</span>':'';
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ; //echo $defaultValue;
					 //$defaultValue=htmlspecialchars($defaultValue,ENT_QUOTES);
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'" style=" display:block;'.$breakCss.'">'.$defaultValue.'</span><span class="fclos '.$spansecond.'"><input '.$inputproperty.' type="text" name="'.$fieldName.'"  value="'.$defaultValue.'" size="'.$this->EDITLENGTH.'" maxlength="'.$this->EDITMAXLENGTH.'" /></span>'.$orivalue .$this->UNIT.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';

					break;
				case 12://����
					$orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$param['ORIVALUEE'].'</span>':'';
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE ;
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$defaultValue.'</span><span class="fclos '.$spansecond.'"><input '.$inputproperty.' type="text" size="'.$this->EDITLENGTH.'" name="'.$fieldName.'" value="'.$defaultValue.'" maxlength="'.$this->EDITMAXLENGTH.'" /></span>'.$orivalue.$this->UNIT.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 13://���ڱ༭
					$orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$param['ORIVALUEE'].'</span>':'';
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ;
					// $defaultValue =$defaultValue  ? date("Y-m-d",strtotime($defaultValue)):'' ;
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'  ><span class="fclos '.$spanfirst.'">'.$defaultValue.'</span><span class="fclos '.$spansecond.' "><input '.$inputproperty.' type="text" size="'.$this->EDITLENGTH.'" name="'.$fieldName.'" onFocus="WdatePicker({dateFmt:\'yyyy-MM-dd\',alwaysUseStartDate:true})" value="'.$defaultValue.'" /></span>'.$orivalue.$this->UNIT.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 131://ʱ��༭
					$orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$param['ORIVALUEE'].'</span>':'';
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ;
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'  ><span class="fclos '.$spanfirst.'">'.$defaultValue.'</span><span class="fclos '.$spansecond.'"><input '.$inputproperty.' type="text" size="'.$this->EDITLENGTH.'"  name="'.$fieldName.'" onFocus="WdatePicker({dateFmt:\'H:mm:ss\',alwaysUseStartDate:true})"  value="'.$defaultValue.'" /></span>'.$orivalue.$this->UNIT.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 132://����ʱ��༭
					$orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$param['ORIVALUEE'].'</span>':'';
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ;
					// $defaultValue =$defaultValue  ? date("Y-m-d",strtotime($defaultValue)):'' ;
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'  ><span class="fclos '.$spanfirst.'" style=" display:block;'.$breakCss.'">'.$defaultValue.'</span><span class="fclos '.$spansecond.' "><input '.$inputproperty.' type="text" size="'.$this->EDITLENGTH.'" name="'.$fieldName.'" onFocus="WdatePicker({dateFmt:\'yyyy-MM-dd HH:mm:ss\',alwaysUseStartDate:true})" value="'.$defaultValue.'" /></span>'.$orivalue.$this->UNIT.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 21://listbox(sql)  ...multiple="true"
					 if($param['FORMTYPE'] == 'FORM'){
						 $selectList = $this->LISTSQL ? $this->transforListsql($model ) : '';
					 }else{
						$selectList = $this->LISTSQL ? $this->transforListsqlone($model,$defaultValue,$param['ORIVALUEE']) : ''; 
					 }
					 $options ='<option value="">��ѡ��</option>';
					 foreach($selectList as $key=>$val){
						$selected = ($defaultValue==$key && $defaultValue !='') ? 'selected="selected"':'';
						if($defaultValue==$key ) $Dfv =$val;
						if($param['ORIVALUEE']==$key && !is_null($param['ORIVALUEE']) ) {
							if($param['ISNEW']!=-1)$Dfv2 =$val; 
						}
						$options .= "<option value='$key' $selected >$val</option>";
					 }
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE ; 
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML .= '<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'"><select '.$inputproperty.' size="'.$this->EDITLENGTH.'" name="'.$fieldName.'" onfocus=\'getDtSelectOption(this,"'.$this->FIELDNAME.'","'.$param['PKFIELD'].'","'.$defaultValue.'");\' sbj="0"> '.$options.'</select></span>'.$orivalue.$this->UNIT.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>'; //
					 $INPUTHTML .= $this->CHILDREN ? "<script> getNextcol('".$this->CHILDREN."','".$defaultValue."','".$param['PKFIELD']."','".$param['CHILDREN']."'); </script>" :'';
					break;
				case 22://listbox(array)
					 $selectList = $this->LISTCHAR ? $this->transforListchar() : '';
					 $options ='<option value="">��ѡ��</option>';
					 foreach($selectList as $key=>$val){
						$selected = ($defaultValue==$key && $defaultValue !='') ? 'selected="selected"':'';
						if($defaultValue==$key  ) $Dfv =$val;  
						if($param['ORIVALUEE']==$key && !is_null($param['ISNEW']) ) {
							if($param['ISNEW']!=-1)$Dfv2 =$val; 
						}
						$options .= "<option value='$key' $selected  >$val</option>";
					 }
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ; 
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'"><select '.$inputproperty.' size="'.$this->EDITLENGTH.'" name="'.$fieldName.'"  > '.$options.' </select></span>'.$orivalue.$this->UNIT.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 23://listbox(sql������ʾ)
                    if($param['FORMTYPE'] == 'FORM'){
                        $selectList = $this->LISTSQL ? $this->transforListsqlTreeone($model,$defaultValue) : '';
                    }else{
                        $selectList = $this->LISTSQL ? $this->transforListsqlone($model,$defaultValue,$param['ORIVALUEE']) : '';
                    }
 
					 $options ='<option value="">��ѡ��</option>';
					 foreach($selectList as $key=>$val){  
						//reset($val);
						//$value = current($val);
						//$name = next($val);

						//$selected = ($defaultValue==$value&& $defaultValue !='') ? 'selected="selected"':'';
						//if($defaultValue==$value ) $Dfv =$name;$count=$val['count']>1? '��':'';
						//$bq = $this->getXbq(2*($val['count']-1),'&nbsp');
						//$options .= "<option value='".$value."' $selected  >$bq  $count ".$name."</option>"; ////style='padding-left:".(20*($val['count']-1)) ."px;'
						$selected = ($defaultValue==$key && $defaultValue !='') ? 'selected="selected"':'';
						if($defaultValue==$key ) $Dfv =$val;
						if($param['ORIVALUEE']==$key  && !is_null($param['ISNEW'])){
							if($param['ISNEW']!=-1)$Dfv2 =$val; 
						}
						$options .= "<option value='$key' $selected >$val</option>";
					 }
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE ; 
					  $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML .= '<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'"><select '.$inputproperty.' size="'.$this->EDITLENGTH.'" name="'.$fieldName.'"  onfocus=\'getDtSelectTreeOption(this,"'.$this->FIELDNAME.'","'.$param['PKFIELD'].'","'.$defaultValue.'");\' sbj="0"> '.$options.'</select></span>'. $orivalue.$this->UNIT.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>'; //
					 $INPUTHTML .= $this->CHILDREN ? "<script> getNextcol('".$this->CHILDREN."','".$defaultValue."','".$param['PKFIELD']."','".$param['CHILDREN']."'); </script>" :'';
					
					break;
				case 31://checkbox sql
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ; 
					 $selectList = $this->LISTSQL ? $this->transforListsql($model) : '';
					 $varr = explode(',',$defaultValue);
					 foreach($selectList as $key=>$val){
						$checked = in_array($key,$varr) ? 'checked="checked"':'';
						if(in_array($key,$varr) ) $Dfv .=' '.$val;
						if($param['ORIVALUEE']==$key && !is_null($param['ISNEW']) ) {
							if($param['ISNEW']!=-1)$Dfv2 .= ' '.$val; 
						}
						$options .= " $val <input $inputproperty type='checkbox' $checked  name='".$fieldName."[]' value='$key'  /> &nbsp ";
					 }
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'">'.$options.'</span>'.$orivalue.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>  ';
					break;
				case 32://checkbox array
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ; 
					 $selectList = $this->LISTCHAR ? $this->transforListchar() : '';
					 $varr = explode(',',$defaultValue); 
					 foreach($selectList as $key=>$val){
						$checked =(in_array($key,$varr) && isset($defaultValue) )? 'checked="checked"':'';
						if( in_array($key,$varr) ) $Dfv .= ' '.$val;
						if($param['ORIVALUEE']==$key && !is_null($param['ISNEW']) ) {
							if($param['ISNEW']!=-1)$Dfv2 =$val; 
						}
						$options .= " $val <input $inputproperty type='checkbox'  $checked name='".$fieldName."[]' value='$key'  /> ";
					 }
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'">'.$options.'</span>'.$orivalue.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>  ';
					break;
				case 41://radio(sql)
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ; 
					 $selectList = $this->LISTSQL ? $this->transforListsql($model) : '';
					 foreach($selectList as $key=>$val){
						if($defaultValue==$key ) $Dfv =$val; 
						if($param['ORIVALUEE']==$key && !is_null($param['ISNEW']) ) {
							if($param['ISNEW']!=-1)$Dfv2 =$val; 
						}
						$checked = ($defaultValue==$key && $defaultValue !='') ? 'checked="checked"':'';
						$options .= " <label> <input $inputproperty type='radio'  $checked  name='$fieldName' value='$key'  /> $val</label> ";
					 }
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'">'.$options.'</span>'.$orivalue.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 42://radio(array)
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ;  
					 $selectList = $this->LISTCHAR ? $this->transforListchar() : ''; 
					 foreach($selectList as $key=>$val){  
						//if($defaultValue==$key )  $Dfv =$val;  
						//$checked = ($defaultValue==$key && isset( $defaultValue)) ? 'checked="checked"':'';
						if($defaultValue !== null  && $defaultValue==$key ){
							$Dfv =$val; 
							$checked ="checked='checked'";
						}elseif($param['ORIVALUEE']==$key && !is_null($param['ISNEW']) ) {
							 if($param['ISNEW']!=-1)$Dfv2 =$val;
                             $checked = '';
						}else {
                            $checked ="";
                        }
						$options .= "<label> <input $inputproperty type='radio'  $checked  name='$fieldName' value='$key'  /> $val</label>";
					 }
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'">'.$options.'</span>'.$orivalue.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 5://textarea
					 //$rows =ceil ( $this->EDITMAXLENGTH / $this->EDITLENGTH);
					 $rows = $this->EDITROWS;
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ;  //$defaultValue=htmlspecialchars($defaultValue,ENT_QUOTES);
					 $orivalue =!is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$param['ORIVALUEE'].'</span>':'';

					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'" style="display:block;'.$breakCss.'">'.$defaultValue.'</span><span class="fclos '.$spansecond.'"><textarea '.$inputproperty.' cols="'.$this->EDITLENGTH.'" rows="'.$rows.'" maxlength="'.$this->FIELDLENGTH.'"  name="'.$fieldName.'" >'.$defaultValue.'</textarea></span>'.$orivalue.$this->UNIT.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';

					break;
				case 6://password
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ; 
					  $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$param['ORIVALUEE'].'</span>':'';
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$defaultValue.'</span><span class="fclos '.$spansecond.'"><input '.$inputproperty.' type="password" name="'.$fieldName.'" size="'.$this->EDITLENGTH.'" maxlength="'.$this->EDITMAXLENGTH.'" value="'.$defaultValue.'" /></span>'. $orivalue.$this->UNIT.'<input type="hidden" class="form-control" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 7:
					$INPUTHTML .=$param['FormeditType']==2 ? '<style> .caseinfo-table .cancel a{display:none !important;}</style>':'';
					$INPUTHTML .='<td '.$GRIDTD.$attr.' > <span class="fclos '.$spanfirst.'"></span> <span class="fclos '.$spansecond.'"><input class="form-control" id="'.$fieldName.'" name="'.$fieldName.'" type="file" multiple="true" /></span> <input  name="filesvalue" class="form-control"  tfield="'.$fieldName.'" type="hidden" value="'.$defaultValue.'"/><script>$(function(){$(\'#'.$fieldName.'\').uploadify({\'uploader\':\''.$this->UPLOADURL.'\',\'onUploadSuccess\':function(file,data){uploadify_uploadfilelist(file.name,file.size,data,\''.$fieldName.'\');},\'formData\':{\'timestamp\':\''.$timestamp.'\',\'token\':\''.md5('nr234n9i92n2' . $timestamp).'\'}  });});</script> </td>';
					break;
				case 77:
					$INPUTHTML .='<td '.$GRIDTD.$attr.' style="color:#FF9999; "><input   type="text" name="'.$fieldName.'"  value="'.$defaultValue.'" size="'.$this->EDITLENGTH.'" maxlength="'.$this->EDITMAXLENGTH.'"  autocomplete="off"   plugin="swfupload" /> <span id="spanButtonPlaceholder"></span>   '.$this->HELPTEXT.'  <input type="hidden" pluginhidden="swfupload" name="hidFileID" id="hidFileID" value="" /> <div id="thumbnails" filedname="'.$fieldName.'"  dfvalue="'.$defaultValue.'">
					<table id="infoTable" border="0"  style="width:50%;display:block; border: solid 1px #7FAAFF; background-color: #C5D9FF; padding: 2px;margin-top:8px;">
					</table>
				</div> </td>';
					break;
				default:
					$INPUTHTML='';
				 
			}
		}
		
		return $INPUTHTML;
    }
	 /**
     +----------------------------------------------------------
     *  ת������������ Array
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	 public function transforListchar(){
		$arr =  array();
		if($this->LISTCHAR){
			$tempArr = explode('^',$this->LISTCHAR); 
			if(is_array($tempArr)){
				foreach($tempArr as $key=>$val){
					if($key%2==1  ) $arr[$val] = $tempArr[$key-1];
				}
			}
		}
		return $arr;
	 }
	  /**
     +----------------------------------------------------------
     *  ת������������ SQL
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	 public function transforListsql($model,$parentKey=''){
		$arr =  array();
		if($this->LISTSQL){
			$sql = str_replace('$parentKey',$parentKey,$this->LISTSQL);
			 
			//$data = F(md5($sql)); 
			if(!$data){
				$data = $model->query($sql);
				//F(md5($sql),$data);
			}
			foreach($data as $key=>$val){ 
				$arr[current($val)] = next($val); 
			}
		} 
		return $arr;
	 }
  /**
     +----------------------------------------------------------
     *  ת������������ one SQL
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     */
	 public function transforListsqlone($model,$fvalue=null,$orivalue=null){
		$arr =  array();
		if($this->LISTSQL){
			//$sql = str_replace('$parentKey',$parentKey,$this->LISTSQL);
			preg_match('/\\s+(\w+),\\s*(\w+),*\\s*(\w+)*\\s+/',$this->LISTSQL,$matchs);
			$field = $matchs[1];
			if($matchs[1]){
				if(stristr($this->LISTSQL,'where') ){
					$sql = $orivalue ? $this->LISTSQL . " and ($field='$fvalue' or $field='$orivalue')": $this->LISTSQL . " and $field='$fvalue'";
					 
				}else{
					$sql = $orivalue ? $this->LISTSQL . " where ($field='$fvalue' or $field='$orivalue')": $this->LISTSQL . " where $field='$fvalue'";
				}
			} else {
                $sql = $this->LISTSQL;  // ����ʹ��Ĭ�ϵ�
            }
			//$data = F(md5($sql));
			$data = $model->query($sql);

			foreach($data as $key=>$val){
				$arr[current($val)] = next($val); 
			}
            //var_dump($arr);die;
		} 
		return $arr;
	 }
	   /**
     +----------------------------------------------------------
     *  ת������������ SQL
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	 public function transforListsqlTree($model,$parentKey=''){
		 
		if($this->LISTSQL){
			$sql = str_replace('$parentKey',$parentKey,$this->LISTSQL);
			//$data = F(md5($sql));
			if(!$data){
				$data = $model->query($sql);
				//F(md5($sql),$data);
			}
			//$data = $model->cache(true)->query(); 
			 
		} 
		return $data;
	 }
	   /**
     +----------------------------------------------------------
     *  ת������������ SQL
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	 public function transforListsqlTreeone($model,$fvalue=null){
		 
		if($this->LISTSQL){
			//$sql = str_replace('$parentKey',$parentKey,$this->LISTSQL);
			preg_match('/\\s+(\w+),\\s*(\w+),\\s*(\w+)\\s+/',$this->LISTSQL,$matchs);
			$field = $matchs[1];
			/*if(stristr($this->LISTSQL,'where')){
				$sql = $this->LISTSQL . " and $field=$fvalue";
			}else{
				$sql = $this->LISTSQL . " where $field=$fvalue";
			} echo $sql;
			$data = F(md5($sql));
			if(!$data){
				$data = $model->query($sql);
				F(md5($sql),$data);
			}
			return $data;
			*/

			//$data = $model->cache(true)->query(); 
			$field = $matchs[1];
			if($matchs[1]){
				if(stristr($this->LISTSQL,'where')){
					$sql = $this->LISTSQL . " and $field=$fvalue";
				}else{
					$sql = $this->LISTSQL . " where $field=$fvalue";
				}
			}
			   //echo $this->LISTSQL ;
			//$data = F(md5($sql)); 
			if(!$data){
				$data = $model->query($sql);
				//F(md5($sql),$data);
			}
			foreach($data as $key=>$val){ 
				$arr[current($val)] = next($val); 
			}
			 
		} 
		return $arr;
	 }
	   /**
     +----------------------------------------------------------
     *  ת������������ SQL ������ʾ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	 public function transforListTree($data ,$parentId=0,$count){
		if($data){
				foreach($data as $key=>$val){ 
					$id =current($val);
					next($val); 
					$pid = next($val);
					if($pid == $parentId) {
						$val['count'] = $count;
						$this->TreeData[] = $val;
						unset($data[$key]);
						$this->transforListTree($data,$id,$count+1);
					}
				}
		
		} 
		return $this->TreeData;
	 }
	 public function getXbq($n,$bq){//��ȡN����ǩ
		 for($i=0;$i<$n;$i++){
			 $str .= $bq;
		 }
		 return $str;
	 }

	 /**
     +----------------------------------------------------------
     *��ȡ ɸѡ ������ select�����б�
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array   
     +----------------------------------------------------------
     */
	public function getSelectOption($model){
		 
		$sqlarr = array(21,23,31,41);
		$aarr = array(22,32,42);
		$treearr = array(23);
		if(in_array($this->EDITTYPE,$treearr)){
			$data = $this->transforListsqlTree($model);
		}elseif(in_array($this->EDITTYPE,$aarr)){
			$data = $this->transforListchar();
		} 
		 
		return $data;
	}
	public function getautocompleteOption($model){
		 
		$sqlarr = array(21,23,31,41);
		$aarr = array(22,32,42);
		$treearr = array(23);
		if(in_array($this->EDITTYPE,$sqlarr) ){  
			$data = $this->transforListsql($model); 
		}elseif(in_array($this->EDITTYPE,$treearr)){
			$data = $this->transforListsqlTree($model);
		}
		foreach($data as $key=>$val){
			//$one['value'] =iconv('GBK', 'UTF-8', $val);
			$one['label'] =iconv('GBK', 'UTF-8', $val);
			$one['id'] =$key;
			$result[] =$one;
		}
		return $result;
	}
	 /**
     +----------------------------------------------------------
     * ħ������ �в����ڵĲ�����ʱ��ִ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method ������
     * @param array $args ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$args) {
         
    }
	 /**
     +----------------------------------------------------------
     * �Զ���������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $name ��������
     * @param $value  ����ֵ
     +----------------------------------------------------------
     */
    public function __set($name ,$value) {
        if(property_exists($this,$name))
            $this->$name = $value;
    }

    /**
     +----------------------------------------------------------
     * �Զ�������ȡ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $name ��������
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __get($name) {
        return isset($this->$name)?$this->$name:null;
    }
	/**
     +----------------------------------------------------------
     * ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __destruct() {
        // �ͷŲ�ѯ
        
    }
}