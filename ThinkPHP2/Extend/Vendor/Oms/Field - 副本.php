<?php
/**
 +------------------------------------------------------------------------------
 * Field 自定义字段类
 +------------------------------------------------------------------------------
 * @category   hhh
 
 * @author    hhh  
 * @version   $Id: Field.php  2015-04-15   $
 +------------------------------------------------------------------------------
 */
class Field{
	 
    protected $FORMNO           = 0; //界面编号
	protected $FIELDNAME           = null;//字段名称
	protected $FIELDMEANS            = null ;//字段含义
	protected $LINENO            = 1 ;//行号
	protected $COLNO            = 1 ;//列号
	protected $FIELDTYPE            = 1 ;//字段类型  1-文字 2-数字 3-日期 31-时间  4-逻辑 5-备注 6-身份证 7-邮箱 8-手机号码 9 checkbox raido  select 等

	protected $READONLY            = 0;//只读  -1 允许0 否（默认）
	protected $NOTNULL            = 0 ;// 非空 -1 允许0 否（默认）
	protected $GRIDVISIBLE            = -1 ;//网格可见  -1 允许（默认） 0 否
	protected $FORMVISIBLE            = -1 ;//页面可见  -1 允许（默认） 0 否
	protected $SORT            = -1 ;//可排序  -1 允许（默认） 0 否  
	protected $FILTER            = -1 ;//可过滤  -1 允许（默认） 0 否    
	 


	protected $FIELDLENGTH            = 0 ;//字段长度
	protected $EDITLENGTH            = 0 ;//编辑框长度
	protected $EDITMAXLENGTH            = 0 ;//编辑框最大录入长度
	protected $EDITROWS                    =3;//编辑框高度
	protected $DEFAULTVALUE            = null ;//默认值 
	protected $EDITTYPE            = 1 ; /*1-textbox   
												'12-数字编辑 
												'13-日期编辑 
												'131-时间编辑
												21-listbox(sql) 22-listbox(array)
												23-listbox编辑(sql树形显示) 
												'211-listbox编辑(sql) 
												'221-listbox编辑(araay)
												31-checkbox sql 32 checkbox array
												41-radio(sql) 42-radio(array)
												5-textarea '
												6-password
												'默认：1
												'数据类型       编辑类型
												1        1(default),21,22,41,42,6
												2        1(default),21,22,41,42,6
												3        1(default),21,22,41,42
												4        21,22,3(default),41,42
												5           5(default),51


												*/
	protected $LISTSQL            = null ;//下拉列表SQL select
	protected $DECLENGTH            = 0 ;//小数点后位数 ?
	protected $ALIGN            = 'center' ;//对齐方式 left-左 center-中 right-右
	protected $EDITFORMAT            = null ;//编辑格式  用于日期（去掉）和数字型字段，例如"###,###.##"、"yyyy/mm/dd"  ?

	protected $LISTCHAR            = null ;// 下拉列表字符串  用^分隔，例如^男^1^女^0 男女是显示值，1，0是保存值

	protected $PARENTCOL            = null ;// 关联下拉字段 说明下拉内容关联的上(改为下级)级字段  
	protected $HELPTEXT            = null ;//帮助内容
	protected $TRANSFER            = null ;//字段转换  
	protected $GRIDTD            = null ;//GRID  TD属性
	protected $INPUTPROPERTY            = null ;//INPUT属性
	protected $DBBOUND            = -1 ;//数据库关联 -1 允许（默认） 0 否   
	protected $CHKADD            = null ;//字段校验脚本
	protected $CHKADDERRMSG            = null ;//字段校验错误提示
	protected $UNIT            = null ;//计量单位
	protected $SETVALUE        =null; //动态赋值 ***新增 虚
	protected $ISVIRTUAL        =null; //是否虚拟字段 -1虚拟 0 真实 ***新增
	protected $CHILDREN        =null; // 关联的子字段***新增 虚
	protected $TreeData         =null;// 树形结构 ***新增 虚
	//protected $INPUTHTML            = null ;//字段编辑框html
	 
	 
	/**
     +----------------------------------------------------------
     * 架构函数 取得模板对象实例
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct() {
        
    }
	 
	/**
     +----------------------------------------------------------
     *  生成编辑框
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function creatFormColsHtml($model,$param=null) {
		
		$attr = $param['attr']  ? $param['attr']:'';
		$align = $this->ALIGN ? " align='$this->ALIGN' " : '';
        $gridtd = $this->GRIDTD ? " class='$this->GRIDTD' " :  '';
		$GRIDTD .= $align;
		//$readOnly =  $this->READONLY==-1 && $param['NEWADD']=='edit' ? " readonly='readonly' " : '';
		$readOnly =  $this->READONLY==-1  ? " readonly='readonly' " : '';
		$inputproperty= $this->INPUTPROPERTY ? " class='$this->INPUTPROPERTY' " :  '';
		$helpText = $this->HELPTEXT ? " tip='$this->HELPTEXT' altercss='gray' " : '';
		$notNull = $this->NOTNULL == -1 ? '' :' ignore="ignore" ';
		$chkaddErrmsg = $this->CHKADDERRMSG ? " errormsg='$this->CHKADDERRMSG' ": '';
		$chkadd = $this->CHKADD ? " datatype='$this->CHKADD'" :'';
		$childrenCol = $this->CHILDREN ? "onchange=\"getNextcol('$this->CHILDREN',this.options[this.options.selectedIndex].value,'$param[PKFIELD]','')\" " :'';
		$inputproperty .= $readOnly.$helpText.$notNull.$chkaddErrmsg.$chkadd.$childrenCol;
		 
		$defaultValue =  is_null($param['value']) ?$this->DEFAULTVALUE  :$param['value']   ;  
		$defaultValue = $this->SETVALUE ? $this->SETVALUE :$defaultValue;  //var_dump($this->FIELDMEANS).var_dump($defaultValue);
		if($param['FormeditType']==1 && $this->READONLY!=-1) {
			$spanfirst = 'spanhidden';
			$spansecond = 'spanshow';
			
		}else {
			
			$spanfirst = 'spanshow';
			$spansecond = 'spanhidden';
		}  
		$versionC = $param['ISNEW']==-1?'[增]':'[原]'.(is_null($param['ORIVALUEE'])?'未设置':'');
		
		$this->EDITLENGTH = $param['MAXLENGTH'] && $param['MAXLENGTH']<$this->EDITLENGTH ? $param['MAXLENGTH']:$this->EDITLENGTH;
		switch($this->FIELDTYPE){
			case 1:
				$FIELDTYPE = 'datatype="s"';
				break;
			case 2:
				$FIELDTYPE = 'datatype="n"';
				break;
			case 3:
				$FIELDTYPE = 'datatype="/\d{4}-\d{2}-\d{2}/"';
				break;
			case 31:
				$FIELDTYPE = 'datatype="/\d{1,2}:\d{1,2}:\d{1,2}/"';
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
		
		if($param['FORMTYPE'] =='FORM' ){
			$INPUTHTML =   "<td $GRIDTD class='leftlable'  >".$this->FIELDMEANS.'</td>';
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
			switch($this->EDITTYPE){
				case 1://文本
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$param['ORIVALUEE'].'</span>':'';
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ; //echo $defaultValue;
					 //$defaultValue=htmlspecialchars($defaultValue,ENT_QUOTES);

					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'" style="display:block;width:100px;overflow: hidden;white-space:nowrap;text-overflow:ellipsis;">'.$defaultValue.'</span><span class="fclos '.$spansecond.'"><input '.$inputproperty.' type="text" name="'.$fieldName.'"  value="'.$defaultValue.'" size="'.$this->EDITLENGTH.'" maxlength="'.$this->EDITMAXLENGTH.'" /></span>'.$orivalue .$this->UNIT.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';

					break;
				case 12://数字
					$orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$param['ORIVALUEE'].'</span>':'';
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE ;
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$defaultValue.'</span><span class="fclos '.$spansecond.'"><input '.$inputproperty.' type="text" size="'.$this->EDITLENGTH.'" name="'.$fieldName.'" value="'.$defaultValue.'" maxlength="'.$this->EDITMAXLENGTH.'" /></span>'.$orivalue.$this->UNIT.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 13://日期编辑
					$orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$param['ORIVALUEE'].'</span>':'';
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ;
					// $defaultValue =$defaultValue  ? date("Y-m-d",strtotime($defaultValue)):'' ;
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$defaultValue.'</span><span class="fclos '.$spansecond.' "><input '.$inputproperty.' type="text" size="'.$this->EDITLENGTH.'" name="'.$fieldName.'" onFocus="WdatePicker({dateFmt:\'yyyy-MM-dd\',alwaysUseStartDate:true})" value="'.$defaultValue.'" /></span>'.$orivalue.$this->UNIT.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 131://时间编辑
					$orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$param['ORIVALUEE'].'</span>':'';
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ;
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$defaultValue.'</span><span class="fclos '.$spansecond.'"><input '.$inputproperty.' type="text" size="'.$this->EDITLENGTH.'"  name="'.$fieldName.'" onFocus="WdatePicker({dateFmt:\'H:mm:ss\',alwaysUseStartDate:true})"  value="'.$defaultValue.'" /></span>'.$orivalue.$this->UNIT.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 21://listbox(sql)  ...multiple="true"
					 $selectList = $this->LISTSQL ? $this->transforListsqlone($model,$defaultValue,$param['ORIVALUEE']) : '';   
					 $options ='<option value="">请选择</option>';
					 foreach($selectList as $key=>$val){
						$selected = ($defaultValue==$key && $defaultValue !='') ? 'selected="selected"':'';
						if($defaultValue==$key ) $Dfv =$val;
						if($param['ORIVALUEE']==$key && !is_null($param['ORIVALUEE']) ) $Dfv2 =$val; 
						$options .= "<option value='$key' $selected >$val</option>";
					 }
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE ; 
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML .= '<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'"><select  '.$inputproperty.' size="'.$this->EDITLENGTH.'" name="'.$fieldName.'" onfocus=\'getDtSelectOption(this,"'.$this->FIELDNAME.'","'.$param['PKFIELD'].'","'.$defaultValue.'");\' sbj="0"> '.$options.'</select></span>'.$orivalue.$this->UNIT.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>'; //
					 $INPUTHTML .= $this->CHILDREN ? "<script> getNextcol('".$this->CHILDREN."','".$defaultValue."','".$param['PKFIELD']."','".$param['CHILDREN']."'); </script>" :'';
					break;
				case 22://listbox(array)
					 $selectList = $this->LISTCHAR ? $this->transforListchar() : '';
					 $options ='<option value="">请选择</option>';
					 foreach($selectList as $key=>$val){
						$selected = ($defaultValue==$key && $defaultValue !='') ? 'selected="selected"':'';
						if($defaultValue==$key  ) $Dfv =$val;  
						if($param['ORIVALUEE']==$key && !is_null($param['ISNEW']) ) $Dfv2 =$val; 
						$options .= "<option value='$key' $selected  >$val</option>";
					 }
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ; 
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'"><select '.$inputproperty.' size="'.$this->EDITLENGTH.'" name="'.$fieldName.'"  > '.$options.' </select></span>'.$orivalue.$this->UNIT.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 23://listbox(sql树形显示)
					 $selectList = $this->LISTSQL ? $this->transforListsqlTreeone($model,$defaultValue) : ''; 
					 //$selectList = $this->transforListTree($selectList,0,1);
					 //$selectList = $this->LISTSQL ? $this->transforListsqlone($model,$defaultValue) : '';  
					 $options ='<option value="">请选择</option>';
					 foreach($selectList as $key=>$val){  
						//reset($val);
						//$value = current($val);
						//$name = next($val);

						//$selected = ($defaultValue==$value&& $defaultValue !='') ? 'selected="selected"':'';
						//if($defaultValue==$value ) $Dfv =$name;$count=$val['count']>1? '├':'';
						//$bq = $this->getXbq(2*($val['count']-1),'&nbsp');
						//$options .= "<option value='".$value."' $selected  >$bq  $count ".$name."</option>"; ////style='padding-left:".(20*($val['count']-1)) ."px;'
						$selected = ($defaultValue==$key && $defaultValue !='') ? 'selected="selected"':'';
						if($defaultValue==$key ) $Dfv =$val;
						if($param['ORIVALUEE']==$key  && !is_null($param['ISNEW'])) $Dfv2 =$val; 
						$options .= "<option value='$key' $selected >$val</option>";
					 }
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE ; 
					  $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML .= '<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'"><select  '.$inputproperty.' size="'.$this->EDITLENGTH.'" name="'.$fieldName.'"  onfocus=\'getDtSelectTreeOption(this,"'.$this->FIELDNAME.'","'.$param['PKFIELD'].'","'.$defaultValue.'");\' sbj="0"> '.$options.'</select></span>'. $orivalue.$this->UNIT.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>'; //
					 $INPUTHTML .= $this->CHILDREN ? "<script> getNextcol('".$this->CHILDREN."','".$defaultValue."','".$param['PKFIELD']."','".$param['CHILDREN']."'); </script>" :'';
					
					break;
				case 31://checkbox sql
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ; 
					 $selectList = $this->LISTSQL ? $this->transforListsql($model) : '';
					 $varr = explode(',',$defaultValue);
					 foreach($selectList as $key=>$val){
						$checked = in_array($key,$varr) ? 'checked="checked"':'';
						if(in_array($key,$varr) ) $Dfv .=' '.$val;
						if($param['ORIVALUEE']==$key && !is_null($param['ISNEW']) ) $Dfv2 .= ' '.$val; 
						$options .= " $val <input $inputproperty type='checkbox' $checked  name='".$fieldName."[]' value='$key'  /> ";
					 }
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'">'.$options.'</span>'.$orivalue.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>  ';
					break;
				case 32://checkbox array
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ; 
					 $selectList = $this->LISTCHAR ? $this->transforListchar() : '';
					 $varr = explode(',',$defaultValue);
					 foreach($selectList as $key=>$val){
						$checked =in_array($key,$varr) ? 'checked="checked"':'';
						if( in_array($key,$varr) ) $Dfv .= ' '.$val;
						if($param['ORIVALUEE']==$key && !is_null($param['ISNEW']) ) $Dfv2 =$val; 
						$options .= " $val <input $inputproperty type='checkbox'  $checked name='".$fieldName."[]' value='$key'  /> ";
					 }
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'">'.$options.'</span>'.$orivalue.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>  ';
					break;
				case 41://radio(sql)
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ; 
					 $selectList = $this->LISTSQL ? $this->transforListsql($model) : '';
					 foreach($selectList as $key=>$val){
						if($defaultValue==$key ) $Dfv =$val; 
						if($param['ORIVALUEE']==$key && !is_null($param['ISNEW']) ) $Dfv2 =$val; 
						$checked = ($defaultValue==$key && $defaultValue !='') ? 'checked="checked"':'';
						$options .= " <label> <input $inputproperty type='radio'  $checked  name='$fieldName' value='$key'  /> $val</label> ";
					 }
					 $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$Dfv2.'</span>':'';
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$Dfv.'</span><span class="fclos '.$spansecond.'">'.$options.'</span>'.$orivalue.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 42://radio(array)
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ;  
					 $selectList = $this->LISTCHAR ? $this->transforListchar() : ''; 
					 foreach($selectList as $key=>$val){  
						//if($defaultValue==$key )  $Dfv =$val;  
						//$checked = ($defaultValue==$key && isset( $defaultValue)) ? 'checked="checked"':'';
						if($defaultValue==$key && isset( $defaultValue)){ 
							$Dfv =$val; 
							$checked ="checked='checked'";
						}elseif($param['ORIVALUEE']==$key && !is_null($param['ISNEW']) ) {
							 $Dfv2 =$val;
		
						}else $checked ="";
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

					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'" style="display:block;width:150px;overflow: hidden;white-space:nowrap;text-overflow:ellipsis;">'.$defaultValue.'</span><span class="fclos '.$spansecond.'"><textarea '.$inputproperty.' cols="'.$this->EDITLENGTH.'" rows="'.$rows.'" maxlength="'.$this->FIELDLENGTH.'"  name="'.$fieldName.'" >'.$defaultValue.'</textarea></span>'.$orivalue.$this->UNIT.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';

					break;
				case 6://password
					 $inputproperty .= $CHKADD ? '' : $FIELDTYPE  ; 
					  $orivalue = !is_null($param['ISNEW'])?'<span class="fclos fred">'.$versionC.''.$param['ORIVALUEE'].'</span>':'';
					 $INPUTHTML.='<td '.$GRIDTD.$attr.'><span class="fclos '.$spanfirst.'">'.$defaultValue.'</span><span class="fclos '.$spansecond.'"><input '.$inputproperty.' type="password" name="'.$fieldName.'" size="'.$this->EDITLENGTH.'" maxlength="'.$this->EDITMAXLENGTH.'" value="'.$defaultValue.'" /></span>'. $orivalue.$this->UNIT.'<input type="hidden" value="'.$defaultValue.'" name="'.$fieldName.'_OLD" />'.'</td>';
					break;
				case 7:
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
     *  转换下拉框数据 Array
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
     *  转换下拉框数据 SQL
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
     *  转换下拉框数据 one SQL
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     */
	 public function transforListsqlone($model,$fvalue=null,$orivalue=null){
		$arr =  array();
		if($this->LISTSQL){
			//$sql = str_replace('$parentKey',$parentKey,$this->LISTSQL);
			preg_match('/\\s+(\w+),\\s*(\w+)\\s+/',$this->LISTSQL,$matchs);
			$field = $matchs[1];
			if($matchs[1]){
				if(stristr($this->LISTSQL,'where') ){
					$sql = $orivalue ? $this->LISTSQL . " and ($field='$fvalue' or $field='$orivalue')": $this->LISTSQL . " and $field='$fvalue'";
					 
				}else{
					$sql = $orivalue ? $this->LISTSQL . " where ($field='$fvalue' or $field='$orivalue')": $this->LISTSQL . " where $field='$fvalue'";
				}
				
			}
			   // echo $sql ;
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
     *  转换下拉框数据 SQL
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
     *  转换下拉框数据 SQL
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
     *  转换下拉框数据 SQL 树形显示
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
	 public function getXbq($n,$bq){//获取N个标签
		 for($i=0;$i<$n;$i++){
			 $str .= $bq;
		 }
		 return $str;
	 }

	 /**
     +----------------------------------------------------------
     *获取 筛选 搜索的 select下拉列表
     +----------------------------------------------------------
     * @access public
	 +----------------------------------------------------------
     * @return   array   
     +----------------------------------------------------------
     */
	public function getSelectOption($model){
		 
		$sqlarr = array(21,23,31,41);
		$aarr = array(22,32,42);
		if(in_array($this->EDITTYPE,$sqlarr) ){  
			$data = $this->transforListsql($model); 
		}elseif(in_array($this->EDITTYPE,$aarr)){
			$data = $this->transforListchar();
		}
		 
		return $data;
	}
	 /**
     +----------------------------------------------------------
     * 魔术方法 有不存在的操作的时候执行
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method 方法名
     * @param array $args 参数
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$args) {
         
    }
	 /**
     +----------------------------------------------------------
     * 自动变量设置
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $name 属性名称
     * @param $value  属性值
     +----------------------------------------------------------
     */
    public function __set($name ,$value) {
        if(property_exists($this,$name))
            $this->$name = $value;
    }

    /**
     +----------------------------------------------------------
     * 自动变量获取
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $name 属性名称
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __get($name) {
        return isset($this->$name)?$this->$name:null;
    }
	/**
     +----------------------------------------------------------
     * 析构方法
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __destruct() {
        // 释放查询
        
    }
}