<?php
/**
 * @version $Id: ad.class.php v1.00 build 120713
 * @author lvtao
 */
class Ad{

	var $load = "jQuery.inpop365({l:'duilian_l',r:'duilian_r',box:'eMeng',content:'eContents',pop:'close_pop',sec:'0'});";
	var $script = array();
	var $style = array();
	var $global_sec = 0;

	function Ad(){
	    $city = $_COOKIE['jinrong_city'];
        !$city && $city='nj';
        $this->city = $city;
    
		$this->api = ($city=='nj'?'http://www':'http://'.$city).".house365.com/api/advertise/adjs.php";
		$this->js =  ($city=='nj'?'http://www':'http://'.$city).".house365.com/api/advertise/advertise.js";
		$this->style[] = "* html,* html body{background-image:url(about:blank);background-attachment:fixed}\r\n";
        
        $ad_id_conf = array(
            'nj' => array(
                'a'=>2257,'b'=>2259,'c'=>2261,'d'=>2263,'e'=>2267,'f'=>2265,'g'=>2269,
            ),
            'sz' => array(
                'a'=>1448,'b'=>1449,'c'=>1450,'d'=>1451,'e'=>1452,'f'=>1453,'g'=>1454,
            ),
            'wh' => array(
                'a'=>1533,'b'=>1534,'c'=>1535,'d'=>1536,'e'=>1537,'f'=>1538,'g'=>1539,
            ),
            'hf' => array(
                'a'=>1656,'b'=>1657,'c'=>1658,'d'=>1659,'e'=>1660,'f'=>1661,'g'=>1662,
            ),
            'wx' => array(
                'a'=>1425,'b'=>1426,'c'=>1427,'d'=>1428,'e'=>1429,'f'=>1430,'g'=>1431,
            ),
            'hz' => array(
                'a'=>1026,'b'=>1027,'c'=>1028,'d'=>1029,'e'=>1030,'f'=>1031,'g'=>1032,
            ),
            'cz' => array(
                'a'=>1243,'b'=>1244,'c'=>1245,'d'=>1246,'e'=>1247,'f'=>1248,'g'=>1249,
            ),
            'ks' => array(
                'a'=>1303,'b'=>1304,'c'=>1305,'d'=>1306,'e'=>1307,'f'=>1308,'g'=>1309,
            ),
            'xa' => array(
                'a'=>1825,'b'=>1826,'c'=>1827,'d'=>1828,'e'=>1829,'f'=>1830,'g'=>1831,
            ),
            'cq' => array(
                'a'=>1627,'b'=>1628,'c'=>1629,'d'=>1630,'e'=>1631,'f'=>1632,'g'=>1633,
            ),
            'sy' => array(
                'a'=>1618,'b'=>1619,'c'=>1620,'d'=>1621,'e'=>1622,'f'=>1623,'g'=>1624,
            ),
        );
        $this->ad_id_arr = $ad_id_conf[$this->city];
        
        //echo $this->api;
	}

	 function select_sql($sortid,$limit){
	   
        $info = api_get_contents($this->api,"nocache=1&action=array&sortid=".$this->ad_id_arr[$sortid]."&limit=".$limit);
        if($info){
            foreach($info as $key=>$value){
                 $id = $value['id'];
                 $link = ($this->city=='nj'?'http://www':'http://'.$this->city).".house365.com/adclick.php?id=".$id;
                 $info[$key]['link'] = $link;
            }
            
        }
        return $info;
	}

	function out_script(){
		$js = "";
		$total = count($this->script);
		if(is_array($this->script) && $total>0){
			$js = "<script>\r\njQuery(function(){\r\n";
			foreach($this->script as $key=>$value){
				if($key == $count-1){
					$js .= $value."\r\n";
				}else{
					$js .= $value.";\r\n";
				}
			}
			$js .= "});</script>\r\n";
		}
		$js .= "<script>jQuery(function(){".$this->load."});</script>";
		
		return $js;
	}

	function out_style(){
		$css = "";
		if(is_array($this->style)){
			$css = "<style>\r\n";
			foreach($this->style as $value){
				$css .= $value;
			}
			$css .= "</style>\r\n";
		}
		return $css;
	}

	/**
	*	???????
	*	$sec	??????
	*/
	function get_top($sortid,$sec=15,$class=""){
			$info = $this->select_sql($sortid,1);
			if(!$info) return '';

			$div = '';
			foreach($info as $key=>$value){
				$id=$value[id];
				$width=$value[width];
				$height=$value[height];
				$src=$value[src];
				$istransparent=$value[istransparent];
				$link=P_WWW."adclick.php?id=".$id;
				$pics2=$value[src_2];
				if(!$pics2) return ;

				$div = '<div style="width:960px; height:'.$height.'px; margin:0px auto; position:relative; z-index:9999">';
				if($value[filetype]=="f"){
					$div.='<div style="position:absolute; width:960px; height:'.$height.'px;"><embed width="'.$width.'" height="'.$height.'" wmode="transparent" menu="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" menu="false"'.($istransparent?' womde="transparent"':'').' src="'.$src.'"></embed></div><a href="#" class="replay" style="width:20px; display:block;height:35px;background:#999;color:#000; position:absolute;right:-20px; text-align:center; display:none">???</a>';
				}else{
					$div='<div style="position:absolute; width:960px; height:'.$height.'px;"><a href="'.$link.'" target="_blank"><img src="'.$src.'" width="960" height="35" /></a></div><a href="#" class="replay" style="width:20px; display:block;height:35px;background:#999;color:#000; position:absolute;right:-20px; text-align:center; display:none">???</a>';
				}


				$div.='<div class="td365_duilian" style="width:175px;background:url('.$pics2.') no-repeat 0 0;height:450px;position:absolute;top:0;left:-175px;"></div><div class="td365_duilian" style="width:175px;background:url('.$pics2.') no-repeat -1135px 0;height:450px;position:absolute;top:0;right:-175px;"></div>';

				$div.='</div>';

				$this->script[] = "jQuery.hat365({id:'hat".$sortid."',hideTime:'".($sec*1000)."'})";
			}

		return '<div id="hat'.$sortid.'" class="'.$class.'">'.$div.'</div>';
	}

	/**
	*	???
	*	$hide	???????
	*	$sec	??????
	*	$callback ????§Ø????????????
	*/
	function get_ad_screen($sortid,$limit=1,$hide="1",$sec="15",$callback="1",$class=""){

			$hide = explode(",",$hide);
			$sec = explode(",",$sec);
			$callback = explode(",",$callback);

			$info = $this->select_sql($sortid,$limit);

			if(!$info) return array();

			$div = array();
			foreach($info as $key=>$value){
				$id=$value[id];
				$width=$value[width];
				$height=$value[height];
				$src=$value[src];
				$istransparent=$value[istransparent];
				$link=P_WWW."adclick.php?id=".$id;

				if($value[filetype]=="f"){
					$div[]='<div id="screen'.$sortid.$key.'" align="center" class="'.$class.'"><embed width="'.$width.'" height="'.$height.'" wmode="transparent" menu="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" menu="false"'.($istransparent?' womde="transparent"':'').' src="'.$src.'"></embed></div>';
				}
				else{
					$div[]='<div id="screen'.$sortid.$key.'" align="center" class="'.$class.'"><a href="'.$link.'" target="_blank"><img src="'.$src.'" width="'.$width.'" height="'.$height.'" border="0"></a></div>';
				}

				$this->get_js_screen($sortid,$key,$hide[$key],$sec[$key],$callback[$key]);
			}

		return $div;
	}

	/**
	*	?????
	*	$sec	??????
	*	$callback ????§Ø????????????
	*/
 	function get_screen_change($sortid,$sec=10,$callback=1,$class=""){

			$info = $this->select_sql($sortid,1);
			if(!$info) return array();

			$div = array();
			foreach($info as $key=>$value){
				$id=$value[id];
				$width=$value[width];
				$height=$value[height];
				$src=$value[src];
				$istransparent=$value[istransparent];
				$link=P_WWW."adclick.php?id=".$id;
				$pics2=$value[src_2];
				if (!$src && !$pics2) {
					return ;
				}

				if($value[filetype]=="f"){
                    if($pics2){
					$div[]='<div class="'.$class.'" id="hiddeAD'.$sortid.'" align="center"><div class="bwarp"><embed width="'.$width.'" height="'.$height.'" wmode="transparent" menu="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" menu="false"'.($istransparent?' womde="transparent"':'').' src="'.$src.'"></embed></div><div class="swarp" style="display:none"><a href="'.$link.'" target="_blank"><img src="'.$pics2.'" width="'.$width.'" height="35" border="0"></a></div></div>';
                    }else{
                        $div[]='<div class="'.$class.'" id="hiddeAD'.$sortid.'" align="center"><div class="bwarp"><embed width="'.$width.'" height="'.$height.'" wmode="transparent" menu="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" menu="false"'.($istransparent?' womde="transparent"':'').' src="'.$src.'"></embed></div><div class="swarp" style="display:none"><a href="'.$link.'" target="_blank"></a></div></div>';
                    }
				}
				else{
                    if($pics2){
					$div[]='<div class="'.$class.'" id="hiddeAD'.$sortid.'" align="center"><div class="bwarp"><a href="'.$link.'" target="_blank"><img src="'.$src.'" width="'.$width.'" height="'.$height.'" border="0"></a></div><div class="swarp" style="display:none"><a href="'.$link.'" target="_blank"><img src="'.$pics2.'" width="'.$width.'" height="35" border="0"></a></div></div>';
                    }else{
                        $div[]='<div class="'.$class.'" id="hiddeAD'.$sortid.'" align="center"><div class="bwarp"><a href="'.$link.'" target="_blank"><img src="'.$src.'" width="'.$width.'" height="'.$height.'" border="0"></a></div><div class="swarp" style="display:none"><a href="'.$link.'" target="_blank"></a></div></div>';
                    }
				}

				if($callback == 1){
					$callback = "jQuery.inpop365({l:'duilian_l',r:'duilian_r',box:'eMeng',content:'eContents',pop:'close_pop',sec:'".$sec."'});";
					$this->load = "";
					$this->global_sec = $sec>$this->global_sec?$sec:$this->global_sec;
				}
				$this->script[] = "jQuery.topHide365({id:'hiddeAD".$sortid."',hideTime:".$sec."*1000,callback:function(){".$callback."}})";
			}

		return $div;
	}

	/**
	*	??????????"???"????????,???base.js?§Ö?getCookie??setCookie????
	*	$limit	?????????????
	*	$hide	???????
	*	$sec	??????
	*	$callback ????§Ø????????????
	*/
	function get_round_screen($sortid,$limit=1,$hide=1,$sec=15,$callback=1,$class=""){

			$info = $this->select_sql($sortid,$limit);

			if(!$info) return "";
            $limit = count($info);
            $write = "var ads = new Array(".$limit.");\r\n";
			$write .= "var adk = getCookie('".$sortid.$limit."')?getCookie('".$sortid.$limit."'):Math.floor(Math.random()*".$limit.");\r\n";

			$write .= "adk=adk>=".$limit."?0:adk;\r\n";


			$div = array();
			foreach($info as $key=>$value){
				$id=$value[id];
				$width=$value[width];
				$height=$value[height];
				$src=$value[src];
				$istransparent=$value[istransparent];
				$link=P_WWW."adclick.php?id=".$id;

				if($value[filetype]=="f"){
					$ads='<embed width="'.$width.'" height="'.$height.'" wmode="transparent" menu="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" menu="false"'.($istransparent?' womde="transparent"':'').' src="'.$src.'"></embed>';
                    $write .= "ads[".$key."] = '".$ads."';\r\n";
				}
				else{
					$ads='<a href="'.$link.'" target="_blank"><img src="'.$src.'" width="'.$width.'" height="'.$height.'" border="0"></a>';
                     $write .= "ads[".$key."] = '".$ads."';\r\n";

				}

			}

            $write .= "document.write(\"<div id='screen".$sortid.$limit."' class='".$class."' align='center'></div>\");\r\n";

            $write .= "jQuery('#screen".$sortid.$limit."').html(ads[adk]);\r\n";

			$write .= "var expdate = new Date();\r\n";

			$write .= "expdate.setTime(expdate.getTime()+(24*60*60*1000*365));\r\n";

			$write .= "setCookie('".$sortid.$limit."',(parseInt(adk)+1),expdate,'/',null,false);\r\n";

            $this->get_js_screen($sortid,$limit,$hide,$sec,$callback);

		return "<script>".$write."</script>\r\n";
	}

	function get_js_screen($sortid,$total,$hide,$sec,$callback){
		$hide = $hide==1?"true":"false";
		if($callback == 1){
			$callback = "jQuery.inpop365({l:'duilian_l',r:'duilian_r',box:'eMeng',content:'eContents',pop:'close_pop',sec:'".$sec."'});";
			$this->load = "";
			$this->global_sec = $sec>$this->global_sec?$sec:$this->global_sec;
		}
		$this->script[] = "jQuery.auto365({id:'screen".$sortid.$total."',hide:".$hide.",time:".$sec."*1000,callback:function(){".$callback."}})";
	}

	/**
	*	?????§¹??????????
	*	$sec	??????
	*/
	function get_stad($sortid,$sec="15",$class=""){

		$ad="";
		$info = $this->select_sql($sortid,1);
		if(!$info) return false;

		foreach($info as $value){
			$id=$value[id];
			$type=$value[filetype];
			$src=$value[src];
			$istransparent=$value[istransparent];
			$link=P_WWW."adclick.php?id=".$id;
			$w=$value[width];
			$h=$value[height];

			if($value[filetype]=="f"){
				$ad='<div id="stad'.$sortid.'" align="center" class="'.$class.'"><embed src="'.$src.'" width="'.$w.'" height="'.$h.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" menu="false"'.($istransparent?' womde="transparent"':'').'></embed></div>';
			}
			else{
				$ad='<div id="stad'.$sortid.'" align="center" class="'.$class.'"><a href="'.$link.'" target="_blank"><img src="'.$src.'" width="'.$w.'" height="'.$h.'" border="0"></a></div>';

			}

			$this->get_js_stad($sortid,$sec);

			if($class) $ad = "<div class='".$class."'>".$ad."</div>";

		}

		return $ad;
	}

	function get_js_stad($sortid,$sec){

		$this->script[] = "jQuery.auto365({id:'stad".$sortid."',hide:true,time:".$sec."*1000,callback:function(){}})";
	}

	/**
	*	???
	*	$time	?????????
	*/
	function get_beitou($sortid,$w=0,$h=0,$time=15){
		$info = $this->select_sql($sortid,1);
		if(!$info) return "";

		$id=$info[0][id];
		$w=$w==0?$info[0][width]:$w;
		$h=$h==0?$info[0][height]:$h;
		$pics=$info[0][src];
		$pics2=$info[0][src_2];
		$istransparent=$info[0][istransparent];
		$links=P_WWW."adclick.php?id=".$id;

		if($info[0][filetype]=="f"){
			$ads = "<embed src='".$pics."' quality='high' wmode='transparent' width='".$w."' height='".$h."' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer'></embed>";
		}
		else{
			$ads = "<a href='".$links."' target='_blank'><img src='".$pics."' width='".$w."' height='".$h."' border='0'></a>";
		}

		if(!$pics2) return "";
		$ads2 = "<img src='".$pics2."' width='35' border='0'>";

		$div = "<div id='AD-sp' style='display:block'><div class='AD-sp-con'><a href='javascript:;' class='closed' id='AD-sp-close'>???</a>".$ads."</div></div>";
		$div .= "<div class='AD-sp-small' id='AD-sp-left' style='display:none'>".$ads2."</div>";

		$this->style[] = "#AD-sp{width:100%;height:".$h."px; position:fixed;top:50%;margin-top:-".($h/2)."px;z-index:1000; text-align:center;*left:0px;}\r\n#AD-sp .AD-sp-con{width:".$w."px;margin:0 auto;height:".$h."px; position:relative}\r\n#AD-sp a.closed{display:block;color:#fff; position:absolute;right:2px;top:2px;background:#000}\r\n*html #AD-sp{position:absolute;top:expression(eval((document.documentElement.clientHeight ? document.documentElement : document.body).scrollTop+((document.documentElement.clientHeight ? document.documentElement : document.body).clientHeight/2)))}\r\n.AD-sp-small img{cursor:pointer;}\r\n.AD-sp-small {top:50%;margin-top:-".($h/2)."px;left: 50%;margin-left:-515px; position: fixed;width: 35px;}\r\n*html .AD-sp-small {position:absolute;left:50%;margin-left:-515px;top:expression(eval((document.documentElement.clientHeight ? document.documentElement : document.body).scrollTop+((document.documentElement.clientHeight ? document.documentElement : document.body).clientHeight/2)))}\r\n";

		$this->script[] = "jQuery.beitou365({bigid :'AD-sp',bigclose:'AD-sp-close',smallid:'AD-sp-left',time:'".($time*1000)."'})";


		return $div;
	}

	//	???????(¦·?)
	function get_ad_center($sortid,$w=0,$h=0,$class=""){


		$ad="";
		$info = $this->select_sql($sortid,1);
		if(!$info) return $ad;

		foreach($info as $value){
			$id=$value[id];
			$type=$value[filetype];
			$src=$value[src];
			$istransparent=$value[istransparent];
			$link=P_WWW."adclick.php?id=".$id;
			$pics2=$info[0][src_2];

			if($w==0) $w=$value[width];
			if($h==0) $h=$value[height];

			if($type=='f'){
				$ad='<embed src="'.$src.'" width="'.$w.'" height="'.$h.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" '.$objname.' menu="false"'.($istransparent?' wmode="transparent"':'').'></embed>';
			}
			else{
				$ad='<a href="'.$link.'" target="_blank"><img src="'.$src.'" width="'.$w.'" height="'.$h.'" border="0"></a>';
			}

			$ad = '<div id="ad_small_img'.$sortid.'">'.$ad.'</div>';

			if($pics2) $ad .= '<div id="ad_big_img'.$sortid.'" style="display:none;left:-'.((960-$w)/2).'px;top:-'.((130-$h)/2).'px;"><a href="'.$link.'" target="_blank"><img src="'.$pics2.'" width="960" height="130" border="0"></a></div>';

			$ad = "<div style='position:relative' class='".$class."'>".$ad."</div>";

		}
		if($pics2){
			$this->script[] = 'jQuery.center365({sid:"ad_small_img'.$sortid.'",bid:"ad_big_img'.$sortid.'"})';
		}

		return $ad;
	}

	//????(¦·?)
	function get_ad_pop($sortid,$w=320,$h=240){
            
			$info = $this->select_sql($sortid,1);
			if(!$info) return false;
			$id=$info[0][id];
			$w=$w==0?$info[0][width]:$w;
			$h=$h==0?$info[0][height]:$h;
			$pics=$info[0][src];
			$istransparent=$info[0][istransparent];
			$links=P_WWW."adclick.php?id=".$id;

			if($info[0][filetype]=="f"){
				$ads = "<embed src='".$pics."' quality='high' wmode='transparent' width='".$w."' height='".$h."' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer'></embed>";
			}
			else{
				$ads = "<a href='".$links."' target='_blank'><img src='".$pics."' width='".$w."' height='".$h."' border='0'></a>";

			}


			$div = "<div id='eMeng'><div style=\"height:24px;width:".$w."px;background:url('".P_WWW."api/advertise/titlebg.gif') no-repeat;\"><span style='float:right;margin-top:2px;margin-right:10px;'><img id='close_pop' style='cursor:pointer' src='".P_WWW."api/advertise/close.gif' /></span></div><div id='eContents'>".$ads."</div></div>";

			$this->get_js_pop("close_pop");
			return $div;
	}

	function get_js_pop($id){
		$this->script[] = "jQuery.box365({id :'".$id."',content:'eContents'})";
		$this->style[] = "#eMeng{display:none;border-left: #a6b4cf 1px solid; border-bottom: #455690 1px solid;background-color:#c9d3f3;\r\nposition:fixed;bottom:0px;right:0px; z-index:100000}\r\n*html #eMeng{position:absolute;left:expression(eval((document.documentElement.clientHeight ? document.documentElement : document.body).scrollLeft+(document.documentElement.clientHeight ? document.documentElement : document.body).clientWidth-this.offsetWidth)-(parseInt(this.currentStyle.marginLeft,10)||0)-(parseInt(this.currentStyle.marginRight,10)||0));top:expression(eval((document.documentElement.clientHeight ? document.documentElement : document.body).scrollTop+(document.documentElement.clientHeight ? document.documentElement : document.body).clientHeight-this.offsetHeight-(parseInt(this.currentStyle.marginTop,10)||0)-(parseInt(this.currentStyle.marginBottom,10)||0)))}\r\n";
	}

	/**
	*	?????????????30px,?????????10px
	*	$float	????¦Ë??
	*/
	var $d_left = 30;
	var $d_right = 30;
	function get_duilian($sortid,$float="left",$w=100,$h=260,$d_left=30,$d_right=30){
			//???????????????????
			$d_left ? $this->d_left=$d_left : '';
			$d_right ? $this->d_right = $d_right : '';

			$info = $this->select_sql($sortid,1);

			if(!$info) return false;
			$id=$info[0][id];
			$w=$w==0?$info[0][width]:$w;
			$h=$h==0?$info[0][height]:$h;
			$pics=$info[0][src];
			$pics2=$info[0][src_2];
			$istransparent=$info[0][istransparent];
			$links=P_WWW."adclick.php?id=".$id;

			if($info[0][filetype]=="f"){
				$ads = "<embed src='".$pics."' quality='high' wmode='transparent' width='".$w."' height='".$h."' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer'></embed>";
			}
			else{
				$ads = "<a href='".$links."' target='_blank'><img src='".$pics."' width='".$w."' height='".$h."' border='0'></a>";
			}
			if(!$pics2) return "";
			$ads2 = "<img src='".$pics2."' width='20' height='".$h."' border='0'>";

			if($float == "left"){
				$div = "<div class='duilian_l' id='coup-left".$sortid."' style='display:none;'><div class='coup-small' id='small".$sortid.$float."'>".$ads2."</div><div class='coup-big' id='big".$sortid.$float."'><a href='javascript:;' class='closed' id='big_close".$sortid.$float."' style='right:0px;'>???</a>".$ads."</div></div>";
			}
			if($float == "right"){
				$div = "<div class='duilian_r' id='coup-right".$sortid."' style='display:none;'><div class='coup-small' id='small".$sortid.$float."'>".$ads2."</div><div class='coup-big' id='big".$sortid.$float."'><a href='javascript:;' class='closed' id='big_close".$sortid.$float."' style='left:0px;'>???</a>".$ads."</div></div>";
			}
		
			$this->get_js_duilian($sortid,$float,$w,$h);
			return $div;
	}

	var $duilian = 0;
	function get_js_duilian($sortid,$float,$w,$h){
		if($this->duilian == 0){
			$this->style[] = ".duilian_l,.duilian_r{position:fixed;z-index:99999}\r\n.duilian_l{left:0}\r\n.duilian_r{right:0}\r\n.coup-small{width:20px;height:".$h."px;display:none;}\r\n.coup-small img{cursor:pointer;}\r\n.coup-big{width:".$w."px;height:".$h."px;}\r\n.coup-big a.closed{ width:40px;height:15px; line-height:15px;display:block;color:#000;position:absolute;color:#FFF;right:0px;top:".($h+2)."px;color:#000;background:url(".P_WWW."api/advertise/closeIco.jpg) no-repeat right 0px;}\r\n";
			$this->duilian = 1;
		}

		if($float == "left"){
			$this->style[] = "#coup-left".$sortid."{top:".$this->d_left."px}\r\n*html #coup-left".$sortid."{position:absolute;top:".$this->d_left."px;".$float.":expression(eval((document.documentElement.clientHeight ? document.documentElement : document.body).scrollLeft));top:expression(eval((document.documentElement.clientHeight ? document.documentElement : document.body).scrollTop+".$this->d_left."))}\r\n";
			$this->d_left = $this->d_left+$h+23;
		}
		if($float == "right"){
			$this->style[] = "#coup-right".$sortid."{top:".$this->d_right."px}\r\n*html #coup-right".$sortid."{position:absolute;top:".$this->d_right."px;".$float.":expression(eval((document.documentElement.clientHeight ? document.documentElement : document.body).scrollLeft));top:expression(eval((document.documentElement.clientHeight ? document.documentElement : document.body).scrollTop+".$this->d_right."))}\r\n";
			$this->d_right = $this->d_right+$h+23;
		}

		$this->script[] = "jQuery.duilian365({bigParent:'big".$sortid.$float."',bigClose:'big_close".$sortid.$float."',smallParent:'small".$sortid.$float."'})";
	}


	/**
	*	????,????????????
	*	$float	????¦Ë??
	*/
	function get_menlian($sortid,$float="left",$w=100,$h=260){
			$info = $this->select_sql($sortid,1);
			if(!$info) return false;
			$id=$info[0][id];
			$w=$w==0?$info[0][width]:$w;
			$h=$h==0?$info[0][height]:$h;
			$pics=$info[0][src];
			$pics2=$info[0][src_2];
			$istransparent=$info[0][istransparent];
			$links=P_WWW."adclick.php?id=".$id;

			if($info[0][filetype]=="f"){
				$ads = "<embed src='".$pics."' quality='high' wmode='transparent' width='".$w."' height='".$h."' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer'></embed>";
			}
			else{
				$ads = "<a href='".$links."' target='_blank'><img src='".$pics."' width='".$w."' height='".$h."' border='0'></a>";
			}

			if(!$pics2) return "";
			$ads2 = "<img src='".$pics2."' width='20' height='".$h."' border='0'>";

			if($float == "left"){
				$div = "<div class='menlian_l' id='m_coup-left".$sortid."'><div class='m_coup-small' id='m_small".$sortid.$float."' style='display:none;'>".$ads2."</div><div class='m_coup-big' id='m_big".$sortid.$float."'><a href='javascript:;' class='closed' id='m_big_close".$sortid.$float."' style='right:0px;'>???</a>".$ads."</div></div>";
			}
			if($float == "right"){
				$div = "<div class='menlian_r' id='m_coup-right".$sortid."'><div class='m_coup-small' id='m_small".$sortid.$float."' style='display:none;'>".$ads2."</div><div class='m_coup-big' id='m_big".$sortid.$float."'><a href='javascript:;' class='closed' id='m_big_close".$sortid.$float."' style='left:0px;'>???</a>".$ads."</div></div>";
			}

			$this->get_js_menlian($sortid,$float,$w,$h);
			return $div;
	}

	/**
	*	????,??????????????"???"????????,???base.js?§Ö?getCookie??setCookie????
	*	$limit	?????????????
	*	$float	????¦Ë??
	*/
	function get_menlian_change($sortid,$limit,$float="left",$w=100,$h=260){
			$info = $this->select_sql($sortid,$limit);

			if(!$info) return false;

			if(count($info) == 1) return $this->get_menlian($sortid,$float,$w,$h);

			$write = "var ads = new Array(".$limit.");\r\nvar ads2 = new Array(".$limit.");\r\n";
			$write .= "var adk = getCookie('".$sortid.$float."')?getCookie('".$sortid.$float."'):Math.round(Math.random()*".($limit-1).");\r\n";

			$write .= "adk=adk>=".$limit."?0:adk;\r\n";

			foreach($info as $key=>$value){
				$id=$value[id];
				$w=$w==0?$value[width]:$w;
				$h=$h==0?$value[height]:$h;
				$pics=$value[src];
				$pics2=$value[src_2];
				$istransparent=$value[istransparent];
				$links=P_WWW."adclick.php?id=".$id;

				if($value[filetype]=="f"){
					$ads = "<embed src='".$pics."' quality='high' wmode='transparent' width='".$w."' height='".$h."' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer'></embed>";
					$write .= "ads[".$key."] = \"".$ads."\";\r\n";
				}
				else{
					$ads = "<a href='".$links."' target='_blank'><img src='".$pics."' width='".$w."' height='".$h."' border='0'></a>";
					$write .= "ads[".$key."] = \"".$ads."\";\r\n";
				}

				if(!$pics2) return "";
				$ads2 = "<img src='".$pics2."' width='20' height='".$h."' border='0'>";
				$write .= "ads2[".$key."] = \"".$ads2."\";\r\n";

			}

			if($float == "left"){
				$write .= "document.write(\"<div class='menlian_l' id='m_coup-left".$sortid."'><div class='m_coup-small' id='m_small".$sortid.$float."' style='display:none;'></div><div class='m_coup-big' id='m_big".$sortid.$float."'><a href='javascript:;' class='closed' id='m_big_close".$sortid.$float."' style='right:0px;'>???</a></div></div>\");\r\n";
			}
			if($float == "right"){
				$write .= "document.write(\"<div class='menlian_r' id='m_coup-right".$sortid."'><div class='m_coup-small' id='m_small".$sortid.$float."' style='display:none;'></div><div class='m_coup-big' id='m_big".$sortid.$float."'><a href='javascript:;' class='closed' id='m_big_close".$sortid.$float."' style='left:0px;'>???</a></div></div>\");\r\n";
			}

			$write .= "jQuery('#m_big".$sortid.$float."').append(ads[adk]);\r\njQuery('#m_small".$sortid.$float."').html(ads2[adk]);\r\n";

			$write .= "setCookie('".$sortid.$float."',(parseInt(adk)+1));\r\n;";

			$this->get_js_menlian($sortid,$float,$w,$h);
			return "<script>".$write."</script>\r\n";
	}

	function get_js_menlian($sortid,$float,$w,$h){

			$this->style[] = ".menlian_l,.menlian_r{position:absolute;z-index:99999}\r\n.menlian_l{left:0}\r\n.menlian_r{right:0}\r\n.m_coup-small{width:20px;height:".$h."px;}\r\n.m_coup-small img{cursor:pointer;}\r\n.m_coup-big{width:".$w."px;height:".$h."px;}\r\n.m_coup-big a.closed{width:40px;height:15px; line-height:15px;display:block;color:#000;position:absolute;color:#FFF;right:0px;top:".($h+2)."px;color:#000;background:url(".P_WWW."api/advertise/closeIco.jpg) no-repeat right 0px;}\r\n";

		$this->script[] = "jQuery.duilian365({bigParent:'m_big".$sortid.$float."',bigClose:'m_big_close".$sortid.$float."',smallParent:'m_small".$sortid.$float."'})";
	}

	/**
	*	???
	*	$display	?????????
	*/
	var $bottom = 0;
	function get_ditong($sortid,$w=0,$h=0,$display="none"){
		$info = $this->select_sql($sortid,1);
		if(!$info) return false;

		$id=$info[0][id];
		$w=$w==0?$info[0][width]:$w;
		$h=$h==0?$info[0][height]:$h;
		$pics=$info[0][src];
		$pics2=$info[0][src_2];
		$istransparent=$info[0][istransparent];
		$links=P_WWW."adclick.php?id=".$id;

		if($info[0][filetype]=="f"){
			$ads = "<embed src='".$pics."' quality='high' wmode='transparent' width='".$w."' height='".$h."' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer'></embed>";
		}
		else{
			$ads = "<a href='".$links."' target='_blank'><img src='".$pics."' width='".$w."' height='".$h."' border='0'></a>";

		}

		if(!$pics2) return "";
		$ads2 = "<img src='".$pics2."' width='20' border='0'>";


		$div = "<div id='AD-bottom".$sortid."' style='display:".$display."'><div class='AD-bottom-con'><a href='javascript:;' class='closed' id='AD-bottom-close".$sortid."'>???</a>".$ads."</div></div>";
		$display = $display=="none"?"block":"none";
		$div .= "<div class='AD-bottom-small' id='AD-bottom-left".$sortid."' style='display:".$display."'>".$ads2."</div>";

		$this->style[] = "#AD-bottom".$sortid."{width:100%;height:".$h."px; position:fixed;bottom:".$this->bottom."px; z-index:1000; text-align:center;*left:0px;}\r\n#AD-bottom".$sortid." .AD-bottom-con{width:960px;margin:0 auto;height:".$h."px; position:relative}\r\n#AD-bottom".$sortid." a.closed{display:block;color:#fff; position:absolute;right:2px;top:2px;background:#000}\r\n*html #AD-bottom".$sortid."{position:absolute;top:expression(eval((document.documentElement.clientHeight ? document.documentElement : document.body).scrollTop+(document.documentElement.clientHeight ? document.documentElement : document.body).clientHeight-this.offsetHeight-(parseInt(this.currentStyle.marginTop,10)||0)-(parseInt(this.currentStyle.marginBottom,10)||0)))}\r\n.AD-bottom-small img{cursor:pointer;}\r\n#AD-bottom-left".$sortid." {bottom: ".($this->bottom+20)."px;left: 0; position: fixed;width: 20px;}\r\n*html #AD-bottom-left".$sortid." {position:absolute;left:expression(eval((document.documentElement.clientHeight ? document.documentElement : document.body).scrollLeft)-(parseInt(this.currentStyle.marginLeft,10)||0)-(parseInt(this.currentStyle.marginRight,10)||0));top:expression(eval((document.documentElement.clientHeight ? document.documentElement : document.body).scrollTop+(document.documentElement.clientHeight ? document.documentElement : document.body).clientHeight-this.offsetHeight-(parseInt(this.currentStyle.marginTop,10)||0)-(parseInt(this.currentStyle.marginBottom,10)||0)-".($this->bottom+20)."))}\r\n";

		$this->script[] = "jQuery.ditong365({bigid :'AD-bottom".$sortid."',bigclose:'AD-bottom-close".$sortid."',smallid:'AD-bottom-left".$sortid."',pop:'close_pop'})";
		$this->bottom = $this->bottom+$h+5;

		return $div;
	}

	//??????
	function get_ad($sortid,$w=0,$h=0,$class="",$objname=""){

		$ad="";
		$info = $this->select_sql($sortid,1);
		if(!$info) return false;

		foreach($info as $value){
			$id=$value[id];
			$type=$value[filetype];
			$src=strstr($value[src],'http://') ? $value[src] : P_WWW.$value[src];
			$istransparent=$value[istransparent];
			$link=$value['link'];

			if($w==0) $w=$value[width];
			if($h==0) $h=$value[height];

			if($type=='f'){
				$objname = $objname ? " name='".$objname."'" : "";
				$ad='<embed src="'.$src.'" width="'.$w.'" height="'.$h.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" '.$objname.' menu="false"'.($istransparent?' wmode="transparent"':'').'></embed>';
			}
			else{
				$ad='<a href="'.$link.'" target="_blank"><img src="'.$src.'" width="'.$w.'" height="'.$h.'" border="0"></a>';
			}

			if($class) $ad = "<div class='".$class."'>".$ad."</div>";

		}

		return $ad;
	}

	//??????????
	function get_ad_data($sortid){
		$info  = '';
		$arr = $this->select_sql($sortid,1);
		
		if(!$arr) return array();
		foreach($arr as $value){

			$type=$value[filetype];

			if($type=='i'){
				$info=array();
				$info[title] = $value[txt];
				$info[img] = $value[src];
				$info[width] = $value[width];
				$info[height] = $value[height];
				$info[links] = P_WWW."adclick.php?id=".$value[id];
			}


			if($type=='f'){
				$info=array();
				$info[title] = $value[txt];
				$info[swf] = $value[src];
				$info[pic] = $value[src_2];
				$info[width] = $value[width];
				$info[height] = $value[height];
				$info[links] = P_WWW."adclick.php?id=".$value[id];
			}
		}

		return $info;
	}
	//??????????all
	function get_ad_data_all($sortid,$limit=1){
		$info  = '';
		$arr = $this->select_sql($sortid,$limit);
		//print_r($arr);
		if(!$arr) return array();
		$all = array();
		foreach($arr as $value){
				$info=array();
				$info[title] = $value[txt];
				$info[img] = $value[src];
				$info[width] = $value[width];
				$info[height] = $value[height];
				$info[links] = P_WWW."adclick.php?id=".$value[id];
				$info[content] = $value[content];
				$info[href] = $value[href];
				$all[] = $info;
		}

		return $all;
	}

	/**
	*	??????,????????????
	*	$limit	?????§Ý?
	*/
	function get_fzhuan($sortid,$limit,$w=0,$h=0,$class=''){

		$ad = "";
		$info = $this->select_sql($sortid,$limit);
		if(!$info) return false;
		$write = "var pic = new Array();\r\nvar link = new Array();\r\n";
		foreach($info as $key=>$value){
			$id=$value[id];
			$type=$value[filetype];
			$src=$value[src];
			$link=P_WWW."adclick.php?id=".$id;

			if($w==0) $w=$value[width];
			if($h==0) $h=$value[height];

			if($type=="i"){
				$write .= "pic[".$key."] = '".$src."';\r\n";
				$write .= "link[".$key."] = '".$link."';\r\n";
			}else{
				return ;
			}
		}
		$write .= 'var number = pic.length;'."\n";
		$write .= 'var index = Math.floor(Math.random()*number);'."\n";
		$write .= 'var tmp_pic = [], tmp_link = [];'."\n";
		$write .= '
		for(var i=index; i < number; i++){
			tmp_pic.push(pic[i]);
			tmp_link.push(link[i]);
		}
		for(i=0; i < index; i++){
			tmp_pic.push(pic[i]);
			tmp_link.push(link[i]);
		}'."\n";
		$write .= 'var url = "images="+tmp_pic.join("*")+"&url="+tmp_link.join("*");'."\n";

		$write .= "document.write(\"<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0' width='960' height='70' border='0'>\");";
		$write .= "document.write(\"<param name='movie' value='".P_WWW."api/advertise/ad960x70.swf?\"+url+\"' />\");";
		$write .= "document.write(\"<param name='quality' value='high' /><param name='wmode' value='opaque' />\");";
		$write .= "document.write(\"<embed src='".P_WWW."api/advertise/ad960x70.swf?\"+url+\"' quality='high' wmode='opaque' pluginspage='http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash' type='application/x-shockwave-flash' width='960' height='70' name='a' />\");";
		$write .= "document.write(\"</object>\");\r\n";

		if($class){
			$ad = "<div class='".$class."'><script>\r\n".$write."</script></div>";
		}else{
			$ad = "<script>\r\n".$write."</script>";
		}
		return $ad;
	}

	/**
	*	???????
	*	$str	?????????????
	*	$links	???????????????????
	*/
	function get_guangming($sortid,$w=80,$h=30,$str,$links){
		$ad="";
		$arr = $this->select_sql($sortid,1);
		if(is_array($arr) && !empty($arr)){
			foreach($arr as $value){
				$id=$value[id];
				$type=$value[filetype];
				$src=$value[src];
				$istransparent=$value[istransparent];
				$link=P_WWW."adclick.php?id=".$id;

				if($w==0) $w=$value[width];
				if($h==0) $h=$value[height];

				if($type=='f'){
					$objname = $objname ? " name='".$objname."'" : "";
					$ad='<embed src="'.$src.'" width="'.$w.'" height="'.$h.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" '.$objname.' menu="false"'.($istransparent?' wmode="transparent"':'').'></embed>';
				}
				else{
					$ad='<a href="'.$link.'" target="_blank"><img src="'.$src.'" width="'.$w.'" height="'.$h.'" border="0"></a>';
				}

				if($class) $ad = "<div class='".$class."'>".$ad."</div>";

			}
		}

		if(!$ad) $ad = "<a target='_blank' href='".$links."'>".$str."</a>";

		return $ad;
	}

    /**
     * add by shangzhan at 20131206
     * @param $sortid
     * @param $limit
     * @param int $w
     * @param int $h
     * @param string $class
     * @return bool|string
     */
    function get_js_fanzhuan($sortid,$limit,$w=0,$h=0,$class=''){
        $ad = "";
        $info = $this->select_sql($sortid,$limit);
        if(!$info) return false;
        $write = <<<EOF1
<style type="text/css">
.jsfanzhuan{margin:10px 0;padding:0;}
.cut-fzpic{width:960px;height:70px;overflow:hidden;position:relative;}
.fzImg{width:960px;height:70px;}
</style>
<div class="jsfanzhuan">
<div class="cut-fzpic">
<div class="fz-pic">
EOF1;
        foreach($info as $key=>$value){
            $write.='<div class="fzImg"><a href="'.$value['href'].'" target="_blank"><img src="'.$value['src'].'" width="'.$w.'" height="'.$h.'" /></a></div>';
        }
        $write.=<<<EOF2
</div>
</div>
<script type="text/javascript">
var sec = 3000;
(function($){
    var defaults = {};
	$.fn.fzAD = function(options){
		options = $.extend({}, defaults, options);
		return this.each(function(){
			var self = $(this);
			var _timer = null;
			var _len = self.find(".fzImg").length;
			var mMoveDiv = self.find(".fz-pic");

			if ( _len<=1 ) {
				return;
			}

			_timer = setInterval(autoPlay, sec);
			mMoveDiv.hover(function(){
				clearInterval(_timer)
			}, function(){
				_timer = setInterval(autoPlay, sec);
			})
			function autoPlay()
			{
				mMoveDiv.animate({
					"marginTop" : "-=" + 70 + "px"
				},function(){
					mMoveDiv.find(".fzImg:first").appendTo(mMoveDiv);
					mMoveDiv.css({"margin-top":0})
				})
			};
		});
	}
})(jQuery);
jQuery(".cut-fzpic").fzAD();
</script>
</div>
EOF2;

        $ad = $write;
        return $ad;
    }


}
?>
