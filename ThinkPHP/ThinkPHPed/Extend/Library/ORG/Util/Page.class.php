<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: Page.class.php 2712 2012-02-06 10:12:49Z liu21st $

class Page {
    // ???????????????
    public $rollPage = 5;
    // ???????????????
    public $parameter  ;
    // ????§Ò??????????
    public $listRows = 20;
    // ???????
    public $firstRow	;
    // ??????????
    protected $totalPages  ;
    // ??????
    protected $totalRows  ;
    // ??????
    protected $nowPage    ;
    // ??????????????
    protected $coolPages   ;
    // ??????????
    //protected $config  =	array('header'=>'?????','prev'=>'????','next'=>'????','first'=>'????','last'=>'?????','theme'=>' %totalRow% %header% %nowPage%/%totalPage% ? %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%');
	protected $pagesizeArr = array("10","20","30","40","50","100");

	protected $config  =	array('header'=>'?????','prev'=>'????','next'=>'????','first'=>'???','last'=>'¦Â?','theme'=>' 
	<span>??%totalRow%%header%</span><span>??%nowPage%/%totalPage%?</span><span>%first%</span><span>%upPage%</span> <span>%downPage%</span> <span>%end%</span><span>%pageform%</span><span>?????%pagesizearr%??</span>
	
	');
    // ???????????
    protected $varPage;

    /**
     +----------------------------------------------------------
     * ???????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $totalRows  ???????
     * @param array $listRows  ??????????
     * @param array $parameter  ???????????
     +----------------------------------------------------------
     */
    public function __construct($totalRows,$listRows='',$parameter='') {
        $this->totalRows = $totalRows;
        $this->parameter = $parameter;
        $this->varPage = C('VAR_PAGE') ? C('VAR_PAGE') : 'p' ;
        if(!empty($listRows)) {
            $this->listRows = intval($listRows);
        }
		/* ?? 2012/4/11 ?????  ??????????listRows ???? ???????????pageize ?????????*/
		 if(!empty($_REQUEST['pagesize'])) {
			$this->listRows = intval($_REQUEST['pagesize']);
		 }

        $this->totalPages = ceil($this->totalRows/$this->listRows);     //?????
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = !empty($_GET[$this->varPage])?intval($_GET[$this->varPage]):1;
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    /**
     +----------------------------------------------------------
     * ?????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function show() {
        if(0 == $this->totalRows) return '';
        $p = $this->varPage;
        $nowCoolPage      = ceil($this->nowPage/$this->rollPage);
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'&':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        //???¡¤???????
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
       // if ($upRow>0){
         //   $upPage="<a href='".$url."&".$p."=$upRow'>".$this->config['prev']."</a>";
       // }else{
        //    $upPage="";
       // }

	   if ($upRow<=0){
           $upRow = $upRow+1;
       }
	   $upPage="<a href='".$url."&".$p."=$upRow'>".$this->config['prev']."</a>";

        //if ($downRow <= $this->totalPages){
         //   $downPage="<a href='".$url."&".$p."=$downRow'>".$this->config['next']."</a>";
        //}else{
         //   $downPage="";
        //}


		if ($downRow > $this->totalPages){
            $downRow = $this->totalPages;
        }
		$downPage="<a href='".$url."&".$p."=$downRow'>".$this->config['next']."</a>";
        // << < > >>
       // if($nowCoolPage == 1){
       //     $theFirst = "";
       //     $prePage = "";
       // }else{
            $preRow =  $this->nowPage-$this->rollPage;
            $prePage = "<a href='".$url."&".$p."=$preRow' >??".$this->rollPage."?</a>";
            $theFirst = "<a href='".$url."&".$p."=1' >".$this->config['first']."</a>";
        //}
        //if($nowCoolPage == $this->coolPages){
        //    $nextPage = "";
        //    $theEnd="";
        //}else{
            $nextRow = $this->nowPage+$this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "<a href='".$url."&".$p."=$nextRow' >??".$this->rollPage."?</a>";
            $theEnd = "<a href='".$url."&".$p."=$theEndRow' >".$this->config['last']."</a>";
        //}
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->rollPage;$i++){
            $page=($nowCoolPage-1)*$this->rollPage+$i;
            if($page!=$this->nowPage){
                if($page<=$this->totalPages){
                    $linkPage .= "&nbsp;<a href='".$url."&".$p."=$page'>&nbsp;".$page."&nbsp;</a>";
                }else{
                    break;
                }
            }else{
                if($this->totalPages != 1){
                    $linkPage .= "&nbsp;<span class='current'>".$page."</span>";
                }
            }
        }
        $pageStr	 =	 str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%first%','%prePage%','%linkPage%','%nextPage%','%end%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$theFirst,$prePage,$linkPage,$nextPage,$theEnd),$this->config['theme']);
		/* ?? 2012/4/11 ????? */

		$pageStr	 =	 str_replace(
            array('%pagesizearr%','%pageform%'),
            array($this->setSelectPageSize($url),$this->setFormPage($url)),$pageStr);
        return $pageStr;
    }

	/* ?? 2012/4/11 ????? */
	public function setSelectPageSize($url){
		foreach($this->pagesizeArr as $pagesize){
			if($this->listRows == $pagesize)
				$selected= "selected";
			else
				$selected= "";
			$option .= "<option value=".$pagesize." ".$selected.">".$pagesize."</option>";
		}
		return '<select onchange="location.href=\''.$url.'&pagesize=\'+this.value">'.$option.'</select>';
	}

	public function setFormPage($url){
		return '?????<input type="text" name="page_form_input" id="page_form_input" class="go-input" value="'.$this->nowPage.'">? <a href="#" class="go-page" onclick="location.href=\''.$url.'&'.$this->varPage.'=\'+document.getElementById(\'page_form_input\').value;return false" >GO</a>';
	}


}