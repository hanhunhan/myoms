//���е�JS�ļ�  
var jsM = {  
	page         : false, 
	dhtmlXTree  : false,  
	photo_tree  : false  
};  
function getJSM(f)  
{  
	var reg = /(\w+)\./;  //�����ʱ������
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
//JSʱ���ж�jsM�У�������ģ��ı�ʶ�Ƿ�Ϊtrue�����Ϊfalse������δ����  
//loadJS("Public/uploadify/jquery.uploadify.js");
//loadStyle("Public/uploadify/uploadify.css");
$(function(){
	
	$(".registerform").Validform({
		tiptype:function(msg,o,cssctl){
			//msg����ʾ��Ϣ;
			//o:{obj:*,type:*,curform:*}, objָ����ǵ�ǰ��֤�ı�Ԫ�أ�������󣩣�typeָʾ��ʾ��״̬��ֵΪ1��2��3��4�� 1�����ڼ��/�ύ���ݣ�2��ͨ����֤��3����֤ʧ�ܣ�4����ʾignore״̬, curformΪ��ǰform����;
			//cssctl:���õ���ʾ��Ϣ��ʽ���ƺ������ú����贫��������������ʾ��ʾ��Ϣ�Ķ��� �� ��ǰ��ʾ��״̬�����β�o�е�type��;

				if(!o.obj.is("form") && msg){  //��֤��Ԫ��ʱo.objΪ�ñ�Ԫ�أ�ȫ����֤ͨ���ύ��ʱo.objΪ�ñ�����;
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
					 }else{ alert('��༭������ӣ�'); return false;}
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
                                    alert('����ɹ���'); 
                                }
                                else
                                {
                                    alert(data.msg);
                                }

                                if (parent.layer) {
                                    var index = parent.layer.getFrameIndex(window.name); //�ȵõ���ǰiframe�������
                                    if (index) {
                                        var href = window.location.href;
                                        // todo ���һЩ���壬�������������ύ���֮������رյ�������
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
                                    alert('��ӳɹ���'); 
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
						//�ļ��ϴ���ɺ󴥷����ύ�¼���ͨ��this.customSettings.form��ȡ�õ�ǰ������;
						//this.customSettings.form.get(0).submit();
						//alert( appUrl + '/Upload/uploadFile/' );
						this.customSettings.showmsg("�ѳɹ��ϴ��ļ���",2);
						var url = $("#hidFileID").val(); 
						addReadyFileInfo(url,obj.name,"�ļ��ϴ��ɹ���");
					}
					 
					 
				},*/
				datepicker:{
					format:"yyyy-mm-dd",//ָ�������ʱ���ʽ;
					firstDayOfWeek:1,//ָ��ÿ�ܿ�ʼ�����ڣ�0��1-6 ��Ӧ ���ա���һ������;
					callback:function(date,obj){
						//date:ѡ�е�����;
						//obj:��ǰ��Ԫ��;
						$("#msgdemo2").text( date + " is selected" );
					},
					//����������������Validform����ڵ���Datepickerʱ�ɴ���Ĳ���;
					//���漸��������Datepicker��������ʼ��ʱ�ɽ��յĲ��������и�����鿴ҳ��˵��;
					clickInput:true,
					startDate:"1970-00-00",
					createButton:false
			    }
		   } 
		   

	});
	$.Datatype.carno = function(gets,obj,curform,regxp){
							//15λ��18λ���֤�����������ʽ
							var regIdCard = /^(^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$)|(^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$)$/;
							//���ͨ������֤��˵�����֤��ʽ��ȷ����׼ȷ�Ի������
							if (regIdCard.test(gets)) {
								if (gets.length == 18) {
									var idCardWi = new Array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); //��ǰ17λ��Ȩ���ӱ�����������
									var idCardY = new Array(1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2); //���ǳ���11�󣬿��ܲ�����11λ��������֤�룬Ҳ���������
									var idCardWiSum = 0; //��������ǰ17λ���Թ��Լ�Ȩ���Ӻ���ܺ�
									for (var i = 0; i < 17; i++) {
										idCardWiSum += gets.substring(i, i + 1) * idCardWi[i];
									}
									var idCardMod = idCardWiSum % 11;//�����У�������������λ��
									var idCardLast = gets.substring(17);//�õ����һλ���֤����
									//�������2����˵��У������10�����֤�������һλӦ����X
									if (idCardMod == 2) {
										if (idCardLast == "X" || idCardLast == "x") {
											return true;
										} else {
											return false;
										}
									} else {
										//�ü��������֤�������һλ���֤����ƥ�䣬���һ�£�˵��ͨ������������Ч�����֤����
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
			//����gets�ǻ�ȡ���ı�Ԫ��ֵ��objΪ��ǰ��Ԫ�أ�curformΪ��ǰ��֤�ı���regxpΪ���õ�һЩ������ʽ������;
			var reg1=/^\d+(\.\d{0,2})?$/;
 
			if(reg1.test(gets)){return true;}
			 
			return false;
 
			//ע��return���Է���true �� false �� �ַ������֣�true��ʾ��֤ͨ���������ַ�����ʾ��֤ʧ�ܣ��ַ�����Ϊ������ʾ��ʾ������false����errmsg��Ĭ�ϵĴ�����ʾ;
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
				addReadyFileInfo(arr[i],arr[i],"�ļ� ");
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
	 
    if (confirm("ȷ��ɾ����")) {
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
                        alert('ɾ���ɹ���');
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
                        alert('������ɾ����ɾ��ʧ�ܣ�');
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
    
    if (confirm("ȷ��ɾ����")) 
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
                    alert('ɾ���ɹ���');
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
                    alert('ɾ���ɹ���');
                  }
               }
               else 
               {
                   alert('������ɾ����ɾ��ʧ�ܣ�');
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
	//�ñ����ʾ
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
	col4.innerHTML = "<a href='javascript:deleteFile(\""+fileid+"\")'>ɾ��</a>";
	col1.style.width="150";
	col2.style.width="250";
	col3.style.width="80";
	col4.style.width="50";
}

function deleteFile(fileId){
	if(confirm('ȷ��ɾ����')){
		//�ñ����ʾ
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
