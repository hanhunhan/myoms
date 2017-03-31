	/*会员信息---js--start-*/
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
		jQuery("body").append("<div class='seac'>数据信息获取中...</div>");
		jQuery(".seac").css({"position":"absolute","top":A_top+"px","left":A_left+"px"});

		jQuery.post(url,{'workname':jQuery('#lastworkname').val()},function(date){
			
			if(date){
				/* 数据处理 */
				var strArr=date.split("##");
				var showCon="";

				if(strArr.length<=0){
					jQuery(".seac").html("找不到数据")
				}
				
			
				if(strArr.length==1){
					showOther3(strArr[0]);
					return;
				}
				
				for(var n=0;n<strArr.length;n++){				
					showCon +="<div class='searli' id='"+strArr[n]+"' >"+strArr[n]+"</div>";
				}
				/* 数据显示 */
				jQuery(".seac").html(showCon);
				jQuery(".searli").bind("click",function(){
					/* 分布显示 */
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
				$("body").append("<div class='seac'>数据信息获取中...</div>");
				$(".seac").css({"position":"absolute","top":A_top+"px","left":A_left+"px"});
				/* 数据处理 */
				
				var strArr=date.split("##");
				var showCon="";
				if(strArr.length<=0){
					$(".seac").html("找不到数据")
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
				/* 数据显示 */
				$(".seac").html(showCon);
				$(".searli").bind("click",function(){
					/* 分布显示 */
					showOther($(this).html(),$(this).attr("id"));
				});
			}else{
				$(".seac").remove();
				$("#check_district").html("您输入的小区不存在，请重新输入！");
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
			$("#check_district").html("您输入的小区不存在，请重新输入！");
			$("#memdistrict_zh").val('');
			$("#memdistrict").val('');
		}
		$(".seac").remove();
	}
	function getloupaninfobyifrm(date){
		if(date){
				var A_top=$("#buy_floor").offset().top+$("#buy_floor").outerHeight(true);
				var A_left=$("#buy_floor").offset().left;
				$("body").append("<div class='seac'>数据信息获取中...</div>");
				$(".seac").css({"position":"absolute","top":A_top+"px","left":A_left+"px"});
				/* 数据处理 */
				
				var strArr=date.split("##");
				var showCon="";
				if(strArr.length<=0){
					$(".seac").html("找不到数据")
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
				/* 数据显示 */
				$(".seac").html(showCon);
				$(".searli").bind("click",function(){
					/* 分布显示 */
					showOther2($(this).html(),$(this).attr("id"));
				});
			}else{
				$(".seac").remove();
				$("#check_floor").html("您输入的楼盘不在系统中，确认输入仍可保存！");
				$("#buy_floor_id").val('0');
			}
	}
	function showOther2(name,id){
		if(name && id>0){
			$("#buy_floor").val(name);
			$("#buy_floor_id").val(id);
			$("#check_floor").html("");
		}else{
			$("#check_floor").html("您输入的楼盘不在系统中，确认输入仍可保存！");
			$("#buy_floor_id").val('0');
		}
		$(".seac").remove();
	}
	function getactivebyifrm(date){
		if(date){
				var A_top=$("#active_name").offset().top+$("#active_name").outerHeight(true);
				var A_left=$("#active_name").offset().left;
				$("body").append("<div class='seac'>数据信息获取中...</div>");
				$(".seac").css({"position":"absolute","top":A_top+"px","left":A_left+"px"});
				/* 数据处理 */
				
				var strArr=date.split("##");
				var showCon="";
				if(strArr.length<=0){
					$(".seac").html("找不到数据")
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
				/* 数据显示 */
				$(".seac").html(showCon);
				$(".searli").bind("click",function(){
					/* 分布显示 */
					showOther4($(this).html(),$(this).attr("id"));
				});
		}else{
			$(".seac").remove();
			$("#check_active").css("color","red");
			$("#check_active").html("您输入的活动不在系统中，自动进入'系统录入'活动保存！");
			$("#active_id").val('0');
		}
	}
	function showOther4(name,id){
		if(name && id>0){
			$("#check_active").css("color","blue");
			$("#check_active").html("自动录入到该活动！");
			$("#active_name").val(name);
			$("#active_id").val(id);			
		}else{
			$("#check_active").css("color","red");
			$("#check_active").html("您输入的活动不在系统中，自动进入'系统录入'活动保存！");
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
					$("body").append("<div class='seac'>数据信息获取中...</div>");
					$(".seac").css({"position":"absolute","top":A_top+"px","left":A_left+"px"});
					/* 数据处理 */
						
					var strArr=date.split("##");
					var showCon="";
					if(strArr.length<=0){
						$(".seac").html("找不到数据")
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
					/* 数据显示 */
					$(".seac").html(showCon);
					$(".searli").bind("click",function(){
						/* 分布显示 */
						showOther5($(this).html(),$(this).attr("id"));
					});
				}else{
					$(".seac").remove();
					$("#check_active").html("您输入的活动不在系统中，确认输入仍可保存！");
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
			$("#check_active").html("您输入的活动不在系统中，确认输入仍可保存！");
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
			alert("服务器数据出错!");
		   }
		})
	}
	function showaction(n){
		if(n=='0'){
			alert("该用户还未参加活动！");
			return false;
		}
		 var box=new Boxy($("#showaction"), {
		  modal: true,
		  title:"参加的活动",
		  closeText:"关闭" 
		  });
	}
	function showlog(n){
		 var box=new Boxy($("#"+n), {
		  modal: false,
		  title:"修改记录",
		  closeText:"关闭" 
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
	/*会员信息---js--end-*/
	/*电话营销---js---start-*/
	function showCreatActive(){
		if(checkcount("dhyx")==false){
			alert('请选择需要生成电话营销的数据！');
			return false;
		 }
		 $('#is_all').html("noall");
		 var box=new Boxy($("#showdhyx"), {
		  modal: true,
		  title:"生成电话营销活动",
					closeText:"关闭",
					fixed:false
		  });
		  $("#dhyx_result1").html('');
		  $("#dhyx_result2").html('');
	}
	function showCreatActive2(memids,i,maxnums){
		i = Number(i);
		maxnums = Number(maxnums);
		if(i<=0){
			alert("搜索结果为零，请重新搜索");
			return false;
		}
		if(i>maxnums){
			alert("搜索结果超过"+maxnums+"条，请分批生成");
			return false;
		}
		 $('#dhyx_memids').val(memids);
		 $('#dhyx_count').html(i);
		 $('#is_all').html("all");
		 var box=new Boxy($("#showdhyx"), {
		  modal: true,
		  title:"生成电话营销活动",
					closeText:"关闭",
					fixed:false
		  });
		  $("#dhyx_result1").html('');
		  $("#dhyx_result2").html('');
	}
	/*电话营销---js---end-*/
	/*投诉部分js---start*/
	function add(){
		 var box=new Boxy($("#showmodel"), {
		  modal: true,
		  title:"添加投诉",
					closeText:"关闭" 
		  });
		  
	}
	function plaint(){
		var plaintel = $('#plaintel').val();
		var reg = /^\d{11}$/
		if(!reg.test(plaintel)) { $('#plaintinfo').html('手机格式错误！');return false}
		else{ $('#plaintinfo').html(''); }

		var plaintext = $('#plaintext').val();
		plaintext = plaintext.replace(/^\s+|\s+$/,'');
		if(plaintext=='') { $('#plaintinfo').html('投诉内容不能为空！');return false }
		else { $('#plaintinfo').html(''); }
	}

	function dealcheck(){
		var plaintreply = $('#plaintreply').val();
		plaintreply = plaintreply.replace(/^\s+|\s+$/,'');
		if(plaintreply=='') { $('#dealinfo').html('处理备注不能为空！');return false }
		else { $('#dealinfo').html(''); }
	}
	var box='';
	function deal(){
		 if(box){
			box.hide();
		 }
		 box=new Boxy($("#showdeal"), {
		  modal: true,
		  title:"处理投诉",
					closeText:"关闭",
			  fixed:false
		  });
	}
	function showview(){
		if(box){
			box.hide();
		 }
		 box=new Boxy($("#showview"), {
		  modal: true,
		  title:"查看投诉",
					closeText:"关闭",
			  fixed:false
		  });
	}
	/*投诉部分js---start*/