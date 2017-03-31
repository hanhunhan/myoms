// +----------------------------------------------------------------------+
// | ThinkPHP                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2006 liu21st.com All rights reserved.                  |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the 'License');      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an 'AS IS' BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: liu21st <liu21st@gmail.com>                                  |
// +----------------------------------------------------------------------+
// $Id$

// Ajax for ThinkPHP
document.write("<div id='ThinkAjaxResult' class='ThinkAjax' ></div>");
var m = {
	'\b': '\\b',
	'\t': '\\t',
	'\n': '\\n',
	'\f': '\\f',
	'\r': '\\r'
};
var ThinkAjax = {
	method:'POST',			// ?????????
	bComplete:false,			// ??????
	updateTip:'Loading...',	// ???????????????
	updateEffect:{'opacity': [0.1,0.85]},			// ????§¹??
	image:['','',''], // ??????????? ??? ????? ???????
	tipTarget:'ThinkAjaxResult',	// ??????????
	showTip:true,	 // ????????????????????
	status:0, //????????
	info:'',	//???????
	data:'',	//????????
	type:'', // JSON EVAL XML ...
	intval:0,
	options:{},
	debug:false,
	activeRequestCount:0,
	// Ajax????????
	getTransport: function() {
		return Try.these(
		 function() {return new XMLHttpRequest()},
		  function() {return new ActiveXObject('Msxml2.XMLHTTP')},
		  function() {return new ActiveXObject('Microsoft.XMLHTTP')}
		 
		) || false;
	},
	tip:function (tips){
		this.options['tip']	=	tips;
		return this;
	},
	effect:function (effect){
		this.options['effect']	=	effect;
		return this;
	},
	target:function (taget){
		this.options['target']	=	target;
		return this;
	},
	response:function (response){
		this.options['response']	=	response;
		return this;
	},
	url:function (url){
		this.options['url']	=	url;
		return this;
	},
	params:function (vars){
		this.options['var']	=	vars;
		return this;
	},
	loading:function (target,tips,effect){
		if ($(target))
		{
			//var arrayPageSize = getPageSize();
			var arrayPageScroll = getPageScroll();
			$(target).style.display = 'block';
			$(target).style.top = (arrayPageScroll[1] +  'px');
			$(target).style.right = '5px';
			// ??????????
			if ($('loader'))
			{
				$('loader').style.display = 'none';
			}
			if ('' != this.image[0])
			{
				$(target).innerHTML = '<IMG SRC="'+this.image[0]+'"  BORDER="0" ALT="loading..." align="absmiddle"> '+tips;
			}else{
				$(target).innerHTML = tips;
			}
			//??????§¹??
			var myEffect = $(target).effects();
			myEffect.custom(effect);
		}
	},
	ajaxResponse:function(request,target,response){
		// ???ThinkPHP???????Ajax?????????
		// ?????ThinkPHP?????
		//alert(request.responseText);
		var str	=	request.responseText;
		str  = str.replace(/([\x00-\x1f\\"])/g, function (a, b) {
                    var c = m[b];
                    if (c) {
                        return c;
                    }else{
						return b;
					}
                     }) ;
      try{
            $return =  eval('(' + str + ')');
            if (this.debug)
            {
                alert(str);
            }
        }catch(ex){
            if (this.debug)
            {
                alert("???????????JS????:\n\n"+str.substr(0,100));
            }
            if ($(target) && this.showTip)
            {
                $(target).innerHTML    = "?????????????????!";
                this.intval = window.setTimeout(function (){
                    var myFx = new Fx.Style(target, 'opacity',{duration:1000}).custom(1,0);
                    $(target).style.display='none';
                    },3000);
            }
            return ;
        }
		/*
		if (this.debug)
		{
			// ?????????????eval????????
			alert(str);
		}		
		try{
			$return =  eval('(' + str + ')');
		}
		catch(e){alert('?????????§Õ???');return;}
		*/
		this.status = $return.status;
		this.info	 =	 $return.info;
		this.data = $return.data;
		this.type	=	$return.type;
		
		if (this.type == 'EVAL' )
		{
			// ?????§Ù??????
			eval($this.data);
		}else{
			// ???????????
			// ????????????ajaxReturn????
			if (response == undefined)
			{
				try	{(ajaxReturn).apply(this,[this.data,this.status,this.info,this.type]);}
				catch (e){}
				 
			}else {
				try	{ (response).apply(this,[this.data,this.status,this.info,this.type]);}
				catch (e){}
			}
		}

		if ($(target))
		{
			// ?????????
			if (this.showTip && this.info!= undefined && this.info!=''){
				if (this.status==1)
				{
					if ('' != this.image[1])
					{
						$(target).innerHTML	= '<IMG SRC="'+this.image[1]+'"  BORDER="0" ALT="success..." align="absmiddle"> <span style="color:blue">'+this.info+'</span>';
					}else{
						$(target).innerHTML	= '<span style="color:blue">'+this.info+'</span>';
					}
					
				}else{
					if ('' != this.image[2])
					{
						$(target).innerHTML	= '<IMG SRC="'+this.image[2]+'"  BORDER="0" ALT="error..." align="absmiddle"> <span style="color:red">'+this.info+'</span>';
					}else{
						$(target).innerHTML	= '<span style="color:red">'+this.info+'</span>';
					}
				}
			}
			// ?????????5??
			if (this.showTip)
			this.intval = window.setTimeout(function (){
				var myFx = new Fx.Style(target, 'opacity',{duration:1000}).custom(1,0);
				$(target).style.display='none';
				},3000);
		}
	},
	// ????Ajax????
	send:function(url,pars,response,target,tips,effect)
	{
		var xmlhttp = this.getTransport();
		url = (url == undefined)?this.options['url']:url;
		pars = (pars == undefined)?this.options['var']:pars;
		if (target == undefined)	{
			target = (this.options['target'])?this.options['target']:this.tipTarget;
		}
		if (effect == undefined)	{
			effect = (this.options['effect'])?this.options['effect']:this.updateEffect;
		}
		if (tips == undefined) {
			tips = (this.options['tip'])?this.options['tip']: this.updateTip;
		}
		if (this.showTip)
		{
			this.loading(target,tips,effect);
		}
		if (this.intval)
		{
			window.clearTimeout(this.intval);
		}
		this.activeRequestCount++;
		this.bComplete = false;
		try {
			if (this.method == "GET")
			{
				xmlhttp.open(this.method, url+"?"+pars, true);
				pars = "";
			}
			else
			{
				xmlhttp.open(this.method, url, true);
				xmlhttp.setRequestHeader("Method", "POST "+url+" HTTP/1.1");
				xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			}
			var _self = this;
			xmlhttp.onreadystatechange = function (){
				if (xmlhttp.readyState == 4 ){
					if( xmlhttp.status == 200 && !_self.bComplete)
					{
						_self.bComplete = true;
						_self.activeRequestCount--;
						_self.ajaxResponse(xmlhttp,target,response);
					}
				}
			}
			xmlhttp.send(pars);
		}
		catch(z) { return false; }
	},
	// ??????Ajax????????????????????
	sendForm:function(formId,url,response,target,tips,effect)
	{
		vars = Form.serialize(formId);
		this.send(url,vars,response,target,tips,effect);
	},
	// ??Ajax??HTML???????
	// event ??????????????? 
	// ???? focus blur mouseover mouseout mousedown mouseup submit click dblclick load change keypress keydown keyup
	bind:function(source,event,url,vars,response,target,tips,effect)
	{
		var _self = this;
	   $(source).addEvent(event,function (){_self.send(url,vars,response,target,tips,effect)});
	},
	// ?????????????Ajax????
	load:function(url,vars,response,target,tips,effect)
	{
		var _self = this;
	   window.addEvent('load',function (){_self.send(url,vars,response,target,tips,effect)});
	},
	// ??????Ajax????
	time:function(url,vars,time,response,target,tips,effect)
	{
		var _self = this;
		myTimer =  window.setTimeout(function (){_self.send(url,vars,response,target,tips,effect)},time);
	},
	// ???????Ajax????
	repeat:function(url,vars,intervals,response,target,tips,effect)
	{
		var _self = this;
		_self.send(url,vars,response,target,effect);
		myTimer = window.setInterval(function (){_self.send(url,vars,response,target,tips,effect)},intervals);
	},
	sendFile:function(id,url){
			var frame	=		this.createUploadIframe(id);
			var form		=		this.createUploadForm(id,url);
            if(form.encoding)
			{
                form.encoding = 'multipart/form-data';				
            }
            else
			{				
                form.enctype = 'multipart/form-data';
            }			
            form.submit();
	},
	// ?????????IFrame
    createUploadIframe: function(id, uri)
	{
			//create frame
            var frameId = 'ThinkUploadFrame' + id;
            if(window.ActiveXObject) {
                var io = document.createElement('<iframe id="' + frameId + '" name="' + frameId + '" />');
                io.src = 'javascript:false';
            }else {
                var io = document.createElement('iframe');
                io.id = frameId;
                io.name = frameId;
            }
            io.style.position		= 'absolute';
            io.style.top			= '-1000px';
            io.style.left			= '-1000px';
			io.style.display		=	'none';	
            document.body.appendChild(io);
            return io;			
    },
	// ??????????
    createUploadForm: function(id,url)
	{
		//create form	
		var formId				=		'ThinkUploadForm' + id;
		var fileId					=		'ThinkUploadFile' + id;
		var form					=		document.createElement('form');
		form.method			=		'POST';
		form.url					=		url;
		form.name				=		formId;
		form.id					=		formId;
		form.enctype			=		"multipart/form-data";
		form.target				=		'ThinkUploadFrame' + id;
		//set attributes
		form.style.position		=		'absolute';
		form.style.top			=		'-1200px';
		form.style.left			=		'-1200px';
		form.style.display		=		'none';
		var fileElement			=		document.createElement('input');
		fileElement.type		=		'file';
		fileElement.
		form.appendChild(fileElement);
		document.body.appendChild(form);
		return form;
    }
}
