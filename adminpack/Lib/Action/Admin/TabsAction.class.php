<?php
/**
 * ҳǩ���ù��ܿ�����
 *
 *  
 */
class TabsAction extends ExtendAction{
    /**
    +----------------------------------------------------------
    *   
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
	public function index(){
		Vendor('Oms.Form');			
		$form = new Form();
		$form->initForminfo(167);
		 
		$form->CZBTN = '<a href="javascript:void(0);" class="btn btn-info btn-xs" onclick="openrolelist(this)">����</a><a href="javascript:void(0);" onclick="fthisedit(this,\''.U('Tabs/index').'\')" class="btn btn-primary btn-xs" title="�༭"><i class="glyphicon glyphicon-edit"></i></a>  <!--a href="javascript:void(0);" onclick="fdel(this,\'\')">ɾ��</a-->' ;
		$form = $form->getResult();
		$this->assign('form',$form);
		$this->display('index');
	}
	/**
    +----------------------------------------------------------
    *   
    +----------------------------------------------------------
    * @param none
    +----------------------------------------------------------
    * @return none
    +----------------------------------------------------------
    */
	public function rolelist(){
		$tabid = $this->_get('tabid');
		Vendor('Oms.Form');			
		$form = new Form();
		$form->initForminfo(168);
		$form->setMyFieldVal('TABSID',$tabid,false);
		$form->where("TABSID=".$tabid );
		$form = $form->getResult();
		$this->assign('form',$form);
		$this->display('rolelist');
	}
	 


    
}

 