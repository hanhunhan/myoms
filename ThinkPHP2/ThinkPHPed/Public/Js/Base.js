// +---------------------------------------------------------------------------+
// | FCS -- Fast,Compatible & Simple OOP PHP Framework                         |
// | FCS JS ?????                                                             |
// +---------------------------------------------------------------------------+
// | Copyright (c) 2005-2006 liu21st.com.  All rights reserved.                |
// | Website: http://www.fcs.org.cn/                                           |
// | Author : Liu21st ???? <liu21st@gmail.com>                                 |
// +---------------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify it   |
// | under the terms of the GNU General Public License as published by the     |
// | Free Software Foundation; either version 2 of the License,  or (at your   |
// | option) any later version.                                                |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,  but      |
// | WITHOUT ANY WARRANTY; without even the implied warranty of                |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General |
// | Public License for more details.                                          |
// +---------------------------------------------------------------------------+

/**
 +------------------------------------------------------------------------------
 * ??????????????????????????
 +------------------------------------------------------------------------------
 * @package    Core
 * @link       http://www.fcs.org.cn
 * @copyright  Copyright (c) 2005-2006 liu21st.com.  All rights reserved. 
 * @author     liu21st <liu21st@gmail.com>
 * @version    $Id$
 +------------------------------------------------------------------------------
 */

var ImportBasePath = location.protocol+'//'+location.hostname+'/Public/';
/**
 +----------------------------------------------------------
 * ?§Ø????????
 * 
 +----------------------------------------------------------
 * @param mixed $obj ???????
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function is_array(obj) {
    return (obj.constructor.toString().indexOf("Array")!= -1);
}

function is_function(obj) {
    return (obj.constructor.toString().indexOf("Function")!= -1);
}

function is_object(obj) {
    return (obj.constructor.toString().indexOf("Object")!= -1);
}

function is_string(obj) {
    return (obj.constructor.toString().indexOf("String")!= -1);
}

function is_number(obj) {
    return (obj.constructor.toString().indexOf("Number")!= -1);
}

function is_boolean(obj) {
    return (obj.constructor.toString().indexOf("Boolean")!= -1);
}

/**
 +----------------------------------------------------------
 * ???????Js?????? ??? ???????? 
 * ???????????????
 +----------------------------------------------------------
 * @param string jsFile ?????Js??????????¡¤??
 * @param string basePath ??????¡¤?? ??????URL¡¤?? 
 +----------------------------------------------------------
 * @return void
 +----------------------------------------------------------
 */

function _import(jsFile,basePath) 
{             
	 var head = document.getElementsByTagName('HEAD').item(0); 
	 var script = document.createElement('SCRIPT'); 
	 if (basePath == undefined)
	 {
		 basePath = ImportBasePath;
	 }
	 
	 jsFile = basePath + jsFile.replace(/\./g, '/') + '.js';
	 //alert(jsFile);
	 script.src = jsFile; 
	 script.type = "text/javascript"; 
	 head.appendChild(script); 
 }

	//---------------------------------------------------
	//	getElementById ???????
	//---------------------------------------------------
	/*
	function $() {
	  var elements = new Array();

	  for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')
		  element = document.getElementById(element);

		if (arguments.length == 1)
		  return element;

		elements.push(element);
	  }

	  return elements;
	}*/

	//---------------------------------------------------
	//	???¡ä???
	//---------------------------------------------------
	function PopWindow(pageUrl,WinWidth,WinHeight) 
	{ 
	var popwin=window.open(pageUrl,"_blank","scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable=no,width="+WinWidth+",height="+WinHeight); 
	return false; 
	} 

	//---------------------------------------------------
	//	????????
	//---------------------------------------------------
	function PopRemoteWindow(url) 
	{ 
	var remote=window.open(url,"RemoteWindow","scrollbars=yes,toolbar=yes,location=yes,directories=yes,status=yes,menubar=yes,resizable=yes"); 
	if(remote.opener==null) 
	{ 
	remote.opener=window; 
	} 
	} 

	//+---------------------------------------------------
	//|	??????????????¡ä????????
	//+---------------------------------------------------
	function PopModalWindow(url,width,height)
	{
		var result=window.showModalDialog(url,"win","dialogWidth:"+width+"px;dialogHeight:"+height+"px;center:yes;status:no;scroll:no;dialogHide:no;resizable:no;help:no;edge:sunken;");
		return result;
	}

	//+---------------------------------------------------
	//|	???????????????????????
	//+---------------------------------------------------
	function PopModelessWindow(url,width,height)
	{
		var win=window.showModelessDialog(url,"win","dialogWidth:"+width+"px;dialogHeight:"+height+"px;center:yes;status:no;scroll:no;dialogHide:no;resizable:no;help:no;edge:sunken;");
		return win;
	}

	//+---------------------------------------------------
	//|	?????????CSS??JS???
	//+---------------------------------------------------
	function ImportCss(cssFile) 
        {             
             document.createStyleSheet(cssFile); 
        } 
      
	function ImportJS(jsFile) 
	{             
		 var head = document.getElementsByTagName('HEAD').item(0); 
		 var script = document.createElement('SCRIPT'); 
		 script.src = jsFile; 
		 script.type = "text/javascript"; 
		 head.appendChild(script); 
	 }

	//+---------------------------------------------------
	//|	??????????
	//+---------------------------------------------------
	 function CreateElement(type,owner)
	 {
		 var element = document.createElement(type); 
		 owner.appendChild(element);
	 }
	 
	//+---------------------------------------------------
	//|	???HTML?????? flag ?1 ??????????
	//+---------------------------------------------------
	function getHTMLParm(flag)
	{
		var parastr = window.location.search;
		if (flag)
		{
			var parm= Array();
			var tempstr="";
			if (str.indexOf("&")>0){
				 para = parastr.split("&");
				 for(i=0;i<para.length;i++)
				 {
					 tempstr1 = para[i];
					 
					 pos = tempstr1.indexOf("=");
					 parm[i] = [tempstr1.substring(0,pos),tempstr1.substring(pos+1)];
				 }
			 }
			 return parm;
		}
		 return parastr;
	}

	function getPageScroll(){

	var yScroll;

	if (self.pageYOffset) {
		yScroll = self.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
		yScroll = document.documentElement.scrollTop;
	} else if (document.body) {// all other Explorers
		yScroll = document.body.scrollTop;
	}

	arrayPageScroll = new Array('',yScroll) 
	return arrayPageScroll;
}



//
// getPageSize()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.org
// Edit for Firefox by pHaez
//
function getPageSize(){
	
	var xScroll, yScroll;
	
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	
	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}	
	
	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){	
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}


	arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight) 
	return arrayPageSize;
}