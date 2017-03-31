<?php
if (is_file(dirname(__FILE__).'/Collection.php')){
	include dirname(__FILE__).'/Collection.php';
}else {
	die('Sorry. Not load Collection file.');
}
class Mycols extends CU_Collection { 
	 
	 /**
     +----------------------------------------------------------
     * 读取字段信息
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	 public function getMycols($Model,$formno){
		$data = $Model->query("select * from FORMLIST where FORMNO =".$formno." order by GRIDQUEUE ASC");
		//$mycols = new CU_Collection(Field);
		if($data){
			foreach($data as $k=>$info){
				//$field = clone  $field;
				$field = new Field();
				foreach($info as $key=>$val){
					if(!is_null($val)){
						if(property_exists('Field',$key))$field->$key = $val;
					}
				}
				//$field->creatFormColsHtml();
				$this->add($field);
			}
		}
		return $this;
		 
	}
	/**
     +----------------------------------------------------------
     * 添加单个字段 
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
	  public function addItem($field){
		 
		$this->add($field);
		return $this;
	}

} 
