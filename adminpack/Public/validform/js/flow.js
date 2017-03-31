$(function(){	
		$("#DEAL_USER").autocomplete({	
			source:function(request, response){
				$.ajax({
					url:"index.php?s=/Api/getFlowPeople",
					dataType:"json",
					data:{
						"search":request.term,
						"roleId":$("#roleId").val()
					},
					success:function(obJect){
						response($.map(obJect,function(item){
							return {
								label:item.name,
								value:item.name,
								USERID: item.id,	
								PHONE: item.phone,
								CITY:item.city
							}
						}));
					}
				});
			},
			minLength:1,
			select:function(event,ui){
				$("#DEAL_USERID").val(ui.item.USERID);		
				$("#PHONE").val(ui.item.PHONE);	
				$("#CITY").val(ui.item.CITY);		
			}
		});
		$("#COPY_USER").tokenInput("index.php?s=/Api/getFlowPeople", {
                theme: "facebook",
				tokenLimit: 20,
				preventDuplicates: true
				
				
        });

		  // ȡ������Ĭ���ύ�¼�
    $(window).keydown(function (event) {
        if (event.keyCode == 13) {    
			if(document.activeElement.tagName !='TEXTAREA'){ 
				event.preventDefault();
				return false;
			}
			 
        }
    });


		
})

function delFile(obj){
	
	var currentVal = $(obj).parent().parent().attr('fileName');
	var currentArr = $("#fileVal").val().split(",").splice($.inArray(obj.value,currentVal),1);
	$(obj).parent().parent().remove();
	$("#fileVal").val(currentArr);
}

function validateForm(){
	if($('#checksubmitflag').val()==0){
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
		var src = document.activeElement;

		if(src.name=="flowNot"){
			if(!confirm("�ף���ȷ��Ҫ������������������𣿣���\n\n�����������ͬ�⣬���̷�����������")){
				return false;
			}
		}

		if(src.name=="flowStop"){
			if(!confirm("�ף���ȷ��Ҫ�������������������𣿣���\n\n������������������������ǹ��������һ��������")){
				return false;
			}
		}

		if(src.name=="flowPass"){
			if(!confirm("�ף���ȷ��Ҫ��ͬ�⡱�����������𣿣���\n\n��ͬ�⡱����ǰ����������̣������Ա������¼�룡")){
				return false;
			}
		}

		if(src.name == "flowPass" || src.name == "flowNext" ){
			if ($("input[name='INFO']").val()=='') {
				alert('����д����/˵����');
				return false;
			}

			if( !$(":input[name=DEAL_USER]").val() )
			{
				alert("������ת����������");
				return false;
			}

			if( !$("#DEAL_USERID").val() )
			{
				alert("��ͨ�������ȡ�û���");
				return false;
			}
		}
		
		if(!$(".suggestion").val()){
			alert("���������������");
			return false;
		}
		$('#checksubmitflag').val(1);
		return true;
	}else{
		alert("�Ѿ��ύ,�벻Ҫ�ظ������");
		return false;
	}
}
//��ȡ�̶������û�
function Choose_Fixed_User(roleId)
{
	$.ajax({
		url:"index.php?s=/Api/Choose_Fixed_User",
		dataType:"json",
		data:{
			"roleId":roleId
		},
		success:function(obJect){
			var html ="<div class='contractinfo-table' style='padding:0 20px;'><table >";
			if(obJect){
				
				html += "<tr><td width='10%' align='center'>��ɫ</td><td>"+obJect.roleName+"</td></tr>";

				html += "<tr><td width='10%' align='center'>��Ա</td><td><div style='width:95%'>";
				$.each(obJect.users,function(k,v){
					html += "<span style='float:left;white-space:nowrap;'><input type='radio' name='users' value='"+v.ID+"' style='vertical-align:middle;margin-top:-2px;' /><label style='padding-right:10px;' name = '"+v.NAME+"'>"+v.NAME+"("+v.CITY+")</label></span>";


				});
				html += "</div></td></tr>";
			}
			html += "</table></div>";

			layer.open({
				type: 1,
				title: '�����Ա',
				shadeClose: true,
				shade: 0.8,
				area: ['70%', '60%'],
				content: html,
				btn:['ȷ��','ȡ��'],
				yes:function(index, layero){

					var DEAL_USERID = $(":radio[name='users']:checked").val();
					var DEAL_USER = $(":radio[name='users']:checked").siblings("label").attr("name");

					$("#DEAL_USERID").val(DEAL_USERID);
					$("#DEAL_USER").val(DEAL_USER);

					layer.close(index); 
				}
			});	
		}
	});
}