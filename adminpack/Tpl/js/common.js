	/*��Ա��Ϣ---js--start-*/
	var timeout = "";
	function searchAutoComplete(){
		$("#memdistrict_zh").bind("keyup",function(){
				clearTimeout(timeout);
				timeout = setTimeout("submit_district_form()",500);
		})
		$("#buy_floor").bind("keyup",function(){
				clearTimeout(timeout);
				timeout = setTimeout("submit_loupan_form()",500);
		})
		$("#active_name").bind("keyup",function(){
				clearTimeout(timeout);
				timeout = setTimeout("submit_active_form()",500);
		})
		$(document).bind("click",function(){
			$(".seac").remove();
		})
	}

	function submit_district_form(){
		$('#ifrm_district_zh').val($('#memdistrict_zh').val())
		document.district_form.submit();
	}
	function submit_loupan_form(){
		$('#ifrm_loupan').val($('#buy_floor').val())
		document.loupan_form.submit();
	}
	function submit_active_form(){
		$('#ifrm_active').val($('#active_name').val())
		document.active_form.submit();
	}
	function getworkname(url){
		var A_top=jQuery("#lastworkname").offset().top+jQuery("#lastworkname").outerHeight(true);
		var A_left=jQuery("#lastworkname").offset().left;
		jQuery("body").append("<div class='seac'>������Ϣ��ȡ��...</div>");
		jQuery(".seac").css({"position":"absolute","top":A_top+"px","left":A_left+"px"});

		jQuery.post(url,{'workname':jQuery('#lastworkname').val()},function(date){
			
			if(date){
				/* ���ݴ��� */
				var strArr=date.split("##");
				var showCon="";

				if(strArr.length<=0){
					jQuery(".seac").html("�Ҳ�������")
				}
				
			
				if(strArr.length==1){
					showOther3(strArr[0]);
					return;
				}
				
				for(var n=0;n<strArr.length;n++){				
					showCon +="<div class='searli' id='"+strArr[n]+"' >"+strArr[n]+"</div>";
				}
				/* ������ʾ */
				jQuery(".seac").html(showCon);
				jQuery(".searli").bind("click",function(){
					/* �ֲ���ʾ */
					showOther3(jQuery(this).html());
				});
			}else
				jQuery(".seac").remove();
		})
	}
	function showOther3(name){
		if(name){
			$("#lastworkname").val(name);
		}
		$(".seac").remove();
	}
	function getdistrictinfobyifrm(date){
		if(date){
				var A_top=$("#memdistrict_zh").offset().top+$("#memdistrict_zh").outerHeight(true);
				var A_left=$("#memdistrict_zh").offset().left;
				$("body").append("<div class='seac'>������Ϣ��ȡ��...</div>");
				$(".seac").css({"position":"absolute","top":A_top+"px","left":A_left+"px"});
				/* ���ݴ��� */
				
				var strArr=date.split("##");
				var showCon="";
				if(strArr.length<=0){
					$(".seac").html("�Ҳ�������")
				}
				
				if(strArr.length==1){
					var ps = strArr[0].split("->");	
					showOther(ps[1],ps[0]);
					return;
				}
				
				for(var n=0;n<strArr.length;n++){
					var ps = strArr[n].split("->");					
					showCon +="<div class='searli' id='"+ps[0]+"' >"+ps[1]+"</div>";
				}
				/* ������ʾ */
				$(".seac").html(showCon);
				$(".searli").bind("click",function(){
					/* �ֲ���ʾ */
					showOther($(this).html(),$(this).attr("id"));
				});
			}else{
				$(".seac").remove();
				$("#check_district").html("�������С�������ڣ����������룡");
				$("#memdistrict_zh").val('');
				$("#memdistrict").val('');
			}
	}
	function showOther(name,id){
		if(name && id>0){
			$("#memdistrict_zh").val(name);
			$("#memdistrict").val(id);
			$("#check_district").html("");
		}else{
			$("#check_district").html("�������С�������ڣ����������룡");
			$("#memdistrict_zh").val('');
			$("#memdistrict").val('');
		}
		$(".seac").remove();
	}
	function getloupaninfobyifrm(date){
		if(date){
				var A_top=$("#buy_floor").offset().top+$("#buy_floor").outerHeight(true);
				var A_left=$("#buy_floor").offset().left;
				$("body").append("<div class='seac'>������Ϣ��ȡ��...</div>");
				$(".seac").css({"position":"absolute","top":A_top+"px","left":A_left+"px"});
				/* ���ݴ��� */
				
				var strArr=date.split("##");
				var showCon="";
				if(strArr.length<=0){
					$(".seac").html("�Ҳ�������")
				}
				
				if(strArr.length==1){
					var ps = strArr[0].split("->");	
					showOther2(ps[1],ps[0]);
					return;
				}
				
				for(var n=0;n<strArr.length;n++){
					var ps = strArr[n].split("->");					
					showCon +="<div class='searli' id='"+ps[0]+"' >"+ps[1]+"</div>";
				}
				/* ������ʾ */
				$(".seac").html(showCon);
				$(".searli").bind("click",function(){
					/* �ֲ���ʾ */
					showOther2($(this).html(),$(this).attr("id"));
				});
			}else{
				$(".seac").remove();
				$("#check_floor").html("�������¥�̲���ϵͳ�У�ȷ�������Կɱ��棡");
				$("#buy_floor_id").val('0');
			}
	}
	function showOther2(name,id){
		if(name && id>0){
			$("#buy_floor").val(name);
			$("#buy_floor_id").val(id);
			$("#check_floor").html("");
		}else{
			$("#check_floor").html("�������¥�̲���ϵͳ�У�ȷ�������Կɱ��棡");
			$("#buy_floor_id").val('0');
		}
		$(".seac").remove();
	}
	function getactivebyifrm(date){
		if(date){
				var A_top=$("#active_name").offset().top+$("#active_name").outerHeight(true);
				var A_left=$("#active_name").offset().left;
				$("body").append("<div class='seac'>������Ϣ��ȡ��...</div>");
				$(".seac").css({"position":"absolute","top":A_top+"px","left":A_left+"px"});
				/* ���ݴ��� */
				
				var strArr=date.split("##");
				var showCon="";
				if(strArr.length<=0){
					$(".seac").html("�Ҳ�������")
				}
				
				if(strArr.length==1){
					var ps = strArr[0].split("->");	
					showOther4(ps[1],ps[0]);
					return;
				}
				
				for(var n=0;n<strArr.length;n++){
					var ps = strArr[n].split("->");					
					showCon +="<div class='searli' id='"+ps[0]+"' >"+ps[1]+"</div>";
				}
				/* ������ʾ */
				$(".seac").html(showCon);
				$(".searli").bind("click",function(){
					/* �ֲ���ʾ */
					showOther4($(this).html(),$(this).attr("id"));
				});
		}else{
			$(".seac").remove();
			$("#check_active").css("color","red");
			$("#check_active").html("������Ļ����ϵͳ�У��Զ�����'ϵͳ¼��'����棡");
			$("#active_id").val('0');
		}
	}
	function showOther4(name,id){
		if(name && id>0){
			$("#check_active").css("color","blue");
			$("#check_active").html("�Զ�¼�뵽�û��");
			$("#active_name").val(name);
			$("#active_id").val(id);			
		}else{
			$("#check_active").css("color","red");
			$("#check_active").html("������Ļ����ϵͳ�У��Զ�����'ϵͳ¼��'����棡");
			$("#active_id").val('0');
		}
		$(".seac").remove();
	}
	function getactiveinfo(url){
		var active_name = $("#active_name").val();
		$.ajax({
			url:url,
			data:"active_name="+active_name,
			type:'get',
			success:function(date){
				alert(date);
				exit;
				if(date){
					var A_top=$("#active_name").offset().top+$("#active_name").outerHeight(true);
					var A_left=$("#active_name").offset().left;
					$("body").append("<div class='seac'>������Ϣ��ȡ��...</div>");
					$(".seac").css({"position":"absolute","top":A_top+"px","left":A_left+"px"});
					/* ���ݴ��� */
						
					var strArr=date.split("##");
					var showCon="";
					if(strArr.length<=0){
						$(".seac").html("�Ҳ�������")
					}
						
					if(strArr.length==1){
						var ps = strArr[0].split("->");	
						showOther5(ps[1],ps[0]);
						return;
					}
						
					for(var n=0;n<strArr.length;n++){
						var ps = strArr[n].split("->");					
						showCon +="<div class='searli' id='"+ps[0]+"' >"+ps[1]+"</div>";
					}
					/* ������ʾ */
					$(".seac").html(showCon);
					$(".searli").bind("click",function(){
						/* �ֲ���ʾ */
						showOther5($(this).html(),$(this).attr("id"));
					});
				}else{
					$(".seac").remove();
					$("#check_active").html("������Ļ����ϵͳ�У�ȷ�������Կɱ��棡");
					$("#active_id").val('0');
				}
			}
		})

	}
	function showOther5(name,id){
		if(name && id>0){
			$("#active_name").val(name);
			$("#active_id").val(id);
			$("#check_active").html("");
		}else{
			$("#check_active").html("������Ļ����ϵͳ�У�ȷ�������Կɱ��棡");
			$("#active_id").val('0');
		}
		$(".seac").remove();
	}
	function updatestatus(memid,status,url){
		$.ajax({
		   type: "POST",
		   url: url,
		   dataType :"json",
		   data: "memid="+memid+"&status="+status,
		   success: function(msg){
			 if(msg.status==1){
				if(msg.data==1){
					visitstatus=0;
				}else{
					visitstatus=4;
				}
				$("select[name^=visitresult] option").each(function(){
					var val = $(this).val();
					if(val==visitstatus){
						$(this).attr("selected","selected");
					}
				})
				$('li[id^=status_]').each(function(){
					var id = $(this).attr('id');
					if(id=="status_"+msg.data){
						 $(this).removeClass();
						 $(this).addClass("cor-9d");
					}else{
						 $(this).removeClass();
						 $(this).addClass("cor-ff");
					}
				})
			 }
		   },
		   error: function(){
			alert("���������ݳ���!");
		   }
		})
	}
	function showaction(n){
		if(n=='0'){
			alert("���û���δ�μӻ��");
			return false;
		}
		 var box=new Boxy($("#showaction"), {
		  modal: true,
		  title:"�μӵĻ",
		  closeText:"�ر�" 
		  });
	}
	function showlog(n){
		 var box=new Boxy($("#"+n), {
		  modal: false,
		  title:"�޸ļ�¼",
		  closeText:"�ر�" 
		  });
	}
	function loupanfilter(url){
		var number = '';
		$('input[name^=buy_area_filter]').each(function(){
			if($(this).attr("checked")==true){
				number+=$(this).val()+',';
			}
		})
		if(number!='') number = number.substr(0,number.length-1);
		floor_filter=$("#buy_floor_filter").val();
		$.ajax({
			url:url,
			data:'type='+encodeURIComponent(number)+"&floor_filter="+floor_filter,
			type:'get',
			success:function(option){				
				$('.loupan option').remove();										
				if(option) $('.loupan').append(option);
			}
		})
	}
	function blockfilter(url){
		var district = '';
		$('input[name^=buy_area_zh_filter]').each(function(){
			if($(this).attr("checked")==true){
				district+=$(this).val()+',';
			}
		})
		if(district!='') district = district.substr(0,district.length-1);
		block_filter=$("#buy_block_filter").val();
		$.ajax({
			url:url,
			data:'district='+district+"&block_filter="+block_filter,
			type:'get',
			success:function(option){
				$('._block option').remove();										
				if(option) $('._block').append(option);
			}
		})
	}
	/*��Ա��Ϣ---js--end-*/
	/*�绰Ӫ��---js---start-*/
	function showCreatActive(){
		if(checkcount("dhyx")==false){
			alert('��ѡ����Ҫ���ɵ绰Ӫ�������ݣ�');
			return false;
		 }
		 $('#is_all').html("noall");
		 var box=new Boxy($("#showdhyx"), {
		  modal: true,
		  title:"���ɵ绰Ӫ���",
					closeText:"�ر�",
					fixed:false
		  });
		  $("#dhyx_result1").html('');
		  $("#dhyx_result2").html('');
	}
	function showCreatActive2(memids,i,maxnums){
		i = Number(i);
		maxnums = Number(maxnums);
		if(i<=0){
			alert("�������Ϊ�㣬����������");
			return false;
		}
		if(i>maxnums){
			alert("�����������"+maxnums+"�������������");
			return false;
		}
		 $('#dhyx_memids').val(memids);
		 $('#dhyx_count').html(i);
		 $('#is_all').html("all");
		 var box=new Boxy($("#showdhyx"), {
		  modal: true,
		  title:"���ɵ绰Ӫ���",
					closeText:"�ر�",
					fixed:false
		  });
		  $("#dhyx_result1").html('');
		  $("#dhyx_result2").html('');
	}
	/*�绰Ӫ��---js---end-*/
	/*Ͷ�߲���js---start*/
	function add(){
		 var box=new Boxy($("#showmodel"), {
		  modal: true,
		  title:"���Ͷ��",
					closeText:"�ر�" 
		  });
		  
	}
	function plaint(){
		var plaintel = $('#plaintel').val();
		var reg = /^\d{11}$/
		if(!reg.test(plaintel)) { $('#plaintinfo').html('�ֻ���ʽ����');return false}
		else{ $('#plaintinfo').html(''); }

		var plaintext = $('#plaintext').val();
		plaintext = plaintext.replace(/^\s+|\s+$/,'');
		if(plaintext=='') { $('#plaintinfo').html('Ͷ�����ݲ���Ϊ�գ�');return false }
		else { $('#plaintinfo').html(''); }
	}

	function dealcheck(){
		var plaintreply = $('#plaintreply').val();
		plaintreply = plaintreply.replace(/^\s+|\s+$/,'');
		if(plaintreply=='') { $('#dealinfo').html('����ע����Ϊ�գ�');return false }
		else { $('#dealinfo').html(''); }
	}
	var box='';
	function deal(){
		 if(box){
			box.hide();
		 }
		 box=new Boxy($("#showdeal"), {
		  modal: true,
		  title:"����Ͷ��",
					closeText:"�ر�",
			  fixed:false
		  });
	}
	function showview(){
		if(box){
			box.hide();
		 }
		 box=new Boxy($("#showview"), {
		  modal: true,
		  title:"�鿴Ͷ��",
					closeText:"�ر�",
			  fixed:false
		  });
	}
	/*Ͷ�߲���js---start*/