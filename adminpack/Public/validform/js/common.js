//所有的JS文件  
var jsM = {  
	page         : false, 
	dhtmlXTree  : false,  
	photo_tree  : false  
};  
function getJSM(f)  
{  
	var reg = /(\w+)\./;  //多个点时有问题
	jF = f.match(reg);  
	return jF[jF.length-1];  
}  
function loadJS(js)  
{  
	id = getJSM(js);  
	var scriptId = document.getElementById(id);  
	var head = document.getElementsByTagName('head').item(0);  
	if(scriptId)  
	{  
		//head.removeChild(id);  
	}  
	else  
	{  
		script  = document.createElement('script');  
		script.src = js;  
		script.type = 'text/javascript';  
		script.id = id;  
		head.appendChild(script);  
	}  
}
function loadStyle(css)  
{  
	id = getJSM(css);  
	var styleId = document.getElementById(id);  
	var head = document.getElementsByTagName('head').item(0);  
	if(styleId)  
	{  
		//head.removeChild(id);  
	}  
	else  
	{  
		style  = document.createElement('link');  
		style.href = css;  
		style.type = 'text/css';  
		style.id = id;  
		style.rel = 'stylesheet';  
		head.appendChild(style);  
	}  
} 
//JS时候，判断jsM中，代表其模块的标识是否为true，如果为false，则尚未加载  
//loadJS("Public/uploadify/jquery.uploadify.js");
//loadStyle("Public/uploadify/uploadify.css");
$(function(){
	
	$(".registerform").Validform({
		tiptype:function(msg,o,cssctl){
			//msg：提示信息;
			//o:{obj:*,type:*,curform:*}, obj指向的是当前验证的表单元素（或表单对象），type指示提示的状态，值为1、2、3、4， 1：正在检测/提交数据，2：通过验证，3：验证失败，4：提示ignore状态, curform为当前form对象;
			//cssctl:内置的提示信息样式控制函数，该函数需传入两个参数：显示提示信息的对象 和 当前提示的状态（既形参o中的type）;

				if(!o.obj.is("form") && msg){  //验证表单元素时o.obj为该表单元素，全部验证通过提交表单时o.obj为该表单对象;
					o.obj.parents("td").append('<div class="info"><span class="Validform_checktip"> </span><span class="dec"><s class="dec1">&#9670;</s><s class="dec2">&#9670;</s></span></div>');

					var objtip=o.obj.parents("td").find(".Validform_checktip");
					cssctl(objtip,o.type);
					objtip.text(msg);

					var infoObj=o.obj.parents("td").find(".info");
					if(o.type==2){
						infoObj.fadeOut(200);
					}else{
						if(infoObj.is(":visible")){return;}
						var left=o.obj.offset().left,
						top=o.obj.offset().top;

						infoObj.css({
							left:left+20,
							top:top-45
						}).show().animate({
							top:top-35
						},200);
					}

				}	
			},
			ignoreHidden:true,
			ajaxPost:true,
			beforeSubmit:function(curform){
				if($("#formtype").val()=='grid'){
					var str = '';
					 $(".fcanel").each(function(){
						str += ','+$(this).attr('fid');
					 });
					var addids  = $('.newadds').length;  
					 if(str){
						$("#IDS").val(str);  
					 }else if(addids){
					 }else{ alert('请编辑或者添加！'); return false;}
				}
				/*var filedname = $("#thumbnails").attr("filedname");
				if(filedname){
					var urls = '';
					$(".uploadUrlS").each(function(){
						urls += ',' + $(this).attr("url");
					});  // alert(urls);
					$("input[name='"+filedname+"']").val(urls);
					 
				}*/
				
				$("input[name='filesvalue']").each(function(){
					var urll = new Array();
					var fieldName = $(this).attr('tfield'); 
					$("[name='filename_"+fieldName+"']").each(function(){
						var filename = $(this).attr('filename');
						filename = filename.replace("-",'');
						filename = filename.replace(",",'');
						var strr = $(this).attr('id')+'-'+filename.substr(0,50)+'-'+$(this).attr('filesize');
						urll.push(strr); 
					});
					var filecode = urll.join(','); 
					 
					if(filecode){
						//$("input[name='"+fieldName+"']").val(filecode);
						if($("input[name='"+fieldName+"']").length>0){
							$("input[name='"+fieldName+"']").val(filecode);
						}else {
							var inputt = "<input name='"+fieldName+"' type='hidden' value='"+filecode+"' />";
							$(this).after(inputt);
						}
					}
				});
				 
				
			},
			beforeCheck:function(){
				
			},
			callback:function(data)
                        {
                            if(data.status == 1) 
                            {   
                                if(data.msg == '')
                                {
                                    alert('保存成功！'); 
                                }
                                else
                                {
                                    alert(data.msg);
                                }

                                if (parent.layer) {
                                    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                    if (index) {
                                        var href = window.location.href;
                                        // todo 针对一些窗体，如立项变更，当提交完表单之后无需关闭弹出窗体
                                        if (!/House\/projectDetail/i.test(href) &&!/House\/projectBudget/i.test(href)&&!/House\/budgetSale/i.test(href)&& !/Activ\/activPro/i.test(href) && !/Activ\/activBudget/i.test(href)) {
                                            parent.layer.close(index);
                                        }
                                    }
                                }

                                if(data.forward  ) {
                                    self.location = decodeURIComponent(data.forward);
                                } else { 
                                    self.location = document.referrer;
									//history.go(-1);location.reload(); 
                                }
                            }
                            else if(data.status == 2)
                            { 
                                if(data.msg == '')
                                {
                                    alert('添加成功！'); 
                                }
                                else
                                {
                                    alert(data.msg); 
                                }

                                if(data.forward) {
                                    //decodeURIComponent(data.forward);
                                    self.location = decodeURIComponent(data.forward);
                                }  else {
                                    self.location = document.referrer;
									//history.go(-1);location.reload(); 
                                }
                            }
                            else
                            { 
                                alert(data.msg);
                                if(data.forward) self.location = data.forward;
                            }
			},
			usePlugin:{
				/*swfupload:{
					file_post_name: "resume_file",
					upload_url: appUrl+'/Upload/save2oracle/?uid=',//window.location.href+"&faction=uploadFile",
					button_image_url: "./Public/validform/plugin/swfupload/XPButtonUploadText_61x22.png",
					flash_url: "./Public/validform/plugin/swfupload/swfupload.swf",
					upload_complete_handler:function(obj){
						//文件上传完成后触发表单提交事件，通过this.customSettings.form可取得当前表单对象;
						//this.customSettings.form.get(0).submit();
						//alert( appUrl + '/Upload/uploadFile/' );
						this.customSettings.showmsg("已成功上传文件！",2);
						var url = $("#hidFileID").val(); 
						addReadyFileInfo(url,obj.name,"文件上传成功！");
					}
					 
					 
				},*/
				datepicker:{
					format:"yyyy-mm-dd",//指定输出的时间格式;
					firstDayOfWeek:1,//指定每周开始的日期，0、1-6 对应 周日、周一到周六;
					callback:function(date,obj){
						//date:选中的日期;
						//obj:当前表单元素;
						$("#msgdemo2").text( date + " is selected" );
					},
					//以上三个参数是在Validform插件内调用Datepicker时可传入的参数;
					//下面几个参数是Datepicker插件本身初始化时可接收的参数，还有更多请查看页面说明;
					clickInput:true,
					startDate:"1970-00-00",
					createButton:false
			    }
		   } 
		   

	});
	$.Datatype.carno = function(gets,obj,curform,regxp){
							//15位和18位身份证号码的正则表达式
							var regIdCard = /^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/;
							//如果通过该验证，说明身份证格式正确，但准确性还需计算
							if (regIdCard.test(gets)) {
								if (gets.length == 18) {
									var idCardWi = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); //将前17位加权因子保存在数组里
									var idCardY = new Array(1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2); //这是除以11后，可能产生的11位余数、验证码，也保存成数组
									var idCardWiSum = 0; //用来保存前17位各自乖以加权因子后的总和
									for (var i = 0; i < 17; i++) {
										idCardWiSum += gets.substring(i, i + 1) * idCardWi[i];
									}
									var idCardMod = idCardWiSum % 11;//计算出校验码所在数组的位置
									var idCardLast = gets.substring(17);//得到最后一位身份证号码
									//如果等于2，则说明校验码是10，身份证号码最后一位应该是X
									if (idCardMod == 2) {
										if (idCardLast == "X" || idCardLast == "x") {
											return true;
										} else {
											return false;
										}
									} else {
										//用计算出的验证码与最后一位身份证号码匹配，如果一致，说明通过，否则是无效的身份证号码
										if (idCardLast == idCardY[idCardMod]) {
											return true;
										} else {
											return false;
										}
									}
								}
							} else {
								return false;
							}
	};
	$.Datatype.money = function(gets,obj,curform,regxp){
			//参数gets是获取到的表单元素值，obj为当前表单元素，curform为当前验证的表单，regxp为内置的一些正则表达式的引用;
			var reg1=/^\d+(\.\d{0,2})?$/;
 
			if(reg1.test(gets)){return true;}
			 
			return false;
 
			//注意return可以返回true 或 false 或 字符串文字，true表示验证通过，返回字符串表示验证失败，字符串作为错误提示显示，返回false则用errmsg或默认的错误提示;
	};
	$(".fedit").siblings('td').each(function(){
			$(this).children("span").first().show().next('span').hide();
	}) ; 
 
	/*$(".caseinfo-table").find('td').each(function(){ 
		 
			if($(this).children("span").first().show().next('span').hide().find('input').attr('readonly')!='readonly' &&$(this).children("span").first().show().next('span').hide().children().attr('readonly')!='readonly' ){
				$(this).children("span").first().hide().next('span').show();
			}
			 
			 
	}) ;*/
	$(".pageSize").bind('change',function(){
                      var pageSize = $(this).val();
                     
                      $(this).parent().next('div').find('a').each(function(){
                          var href = $(this).attr('href');
                         
                          if(href!='' && href!='javascript:void(0);')var newHref = href.substr(0,href.lastIndexOf("pageSize"))+"pageSize="+pageSize; 
                          $(this).attr('href',newHref);
                      });
     });

	/* var dfFile = $("#thumbnails").attr("dfvalue");
	 if(dfFile){
		 var arr =dfFile.split(',');
		 for(var i=0;i<arr.length;i++){
			 if(arr[i]){
				addReadyFileInfo(arr[i],arr[i],"文件 ");
			 }
		 }
	 }
   */
	 
	$("input[name='filesvalue']").each(function(){
		 
		var fieldName = $(this).attr('tfield'); 
		var values = $(this).val();  
		if(values){
			var filesarr = values.split(','); 
			for(i in filesarr){
				var item = filesarr[i].split('-');
				var filecode = item[0]; 
				var filename = item[1];
				var filesize = item[2];
				uploadify_uploadfilelist(filename,filesize,filecode,fieldName);

			}
		}
		
		 
				 
	});
 
});
function ofdel(obj ){
    var fid = $(obj).attr('fid');
	 
    if (confirm("确定删除吗？")) {
       $.ajax({
           type:'GET',
           url:'',
           data:'faction=delData&ID='+fid,
           success:function(obj){  
              var res = eval("("+obj+")");
               if(res.status === 'success') 
               {
                    if(res.msg == '')
                    {
                        alert('删除成功！');
                    }
                    else
                    {
                        alert(res.msg);
                    }

                  window.location.reload();
               } 
               else
               {
                    if(res.msg == '')
                    {
                        alert('不允许删除，删除失败！');
                    }
                    else
                    {
                        alert(res.msg);
                    }
               }
           }
       });
    }
    else
    {
        return false;
    }
}

function fdel(obj, url)
{
    var fid = $(obj).parent().attr('fid');
    
    if (confirm("确定删除吗？")) 
    {
       $.ajax({
           type:'GET',
           url:'',
           data:'faction=delData&ID='+fid,
           success:function(obj){  
              var res = eval("("+obj+")");
               if(res.status === 'success') {
                  if(res.msg != '')
                  {
                    alert(res.msg);
                  }
                  else
                  {
                    alert('删除成功！');
                  }
                  
                  if(url)
                  {
                    window.location.href = url ;
                  }
                  else
                  {
                    window.location.reload();
                  }
               }
               else if(res.status === 'error')
               {
                   if(res.msg != '')
                  {
                    alert(res.msg);
                  }
                  else
                  {
                    alert('删除成功！');
                  }
               }
               else 
               {
                   alert('不允许删除，删除失败！');
               }
           }
       });
    } 
    else 
    {
        return false;
    }
}
 
function fthisedit(obj,url){  
		var url = url+'&showForm=1&ID='+$(obj).parent().attr('fid');
		window.location.href =url;
}
function fthisShow(obj,url){
		var url = url+'&showForm=2&ID='+$(obj).parent().attr('fid');
		window.location.href =url;
}

function addReadyFileInfo(fileid,fileName,message,status){
	//用表格显示
	var infoTable = document.getElementById("infoTable");
	  var rowNum=infoTable.rows.length;
     for (i=0;i<rowNum;i++)
     {
        // infoTable.deleteRow(i);
         rowNum=rowNum-1;
         i=i-1;
     } 
	var row = infoTable.insertRow();
	row.id = fileid;
	row.setAttribute("url", fileid);
	row.setAttribute("filename", fileName);
	row.setAttribute("class", 'uploadUrlS');
	var col1 = row.insertCell();
	var col2 = row.insertCell();
	var col3 = row.insertCell();
	var col4 = row.insertCell();
	col4.align = "right";
	col1.innerHTML = message+" : ";
	col2.innerHTML = "<a href='"+fileid+"' target='_blank'>"+fileName+"</a>";
	if(status!=null&&status!=""){
		col3.innerHTML="<font color='red'>"+status+"</font>";
	}else{
		col3.innerHTML="";
	}
	col4.innerHTML = "<a href='javascript:deleteFile(\""+fileid+"\")'>删除</a>";
	col1.style.width="150";
	col2.style.width="250";
	col3.style.width="80";
	col4.style.width="50";
}

function deleteFile(fileId){
	if(confirm('确定删除？')){
		//用表格显示
		var infoTable = document.getElementById("infoTable");
		var row = document.getElementById(fileId);
		infoTable.deleteRow(row.rowIndex);
	}
	//swfu.cancelUpload(fileId,false);
}

function uploadify_uploadfilelist(filename,filesize,filecode,field){   
	var itemTemplate = '<div id="'+filecode+'" filename="'+filename+'" filesize="'+filesize+'"   name="filename_'+field+'" class="uploadify-queue-item">\
					<div class="cancel">\
						<a href="javascript:uploadify_uploaddel(\''+filecode+'\')">X</a>\
					</div>\
					<span class="fileName"><a target="_blank" href="index.php?s=/Upload/showfile&filecode='+filecode+'">'+filename+' ('+filesize+')</a></span><span class="data"></span>\
					<div class="uploadify-progress">\
						<div  > </div>\
					</div>\
				</div>';
	$("#"+field+"").parent().parent().append(itemTemplate);
	
}
function uploadify_uploaddel(filecode){
	$("#"+filecode).remove();

}
