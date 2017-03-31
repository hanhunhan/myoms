$(function(){
    var cur_obj_btn=$(".work");
    $(".buttongroup .btnDiv").click(function(){
        if(cur_obj_btn!=null)
        {
            cur_obj_btn.removeClass('on');
        }
        cur_obj_btn=$(this);
        $(this).addClass("on");
    })

    $(".comfirmbtn").click(function(){
        $("#comfirmArrival").fadeIn();
    })
    $(".btn").click(function(){
        $(".popup_comfirm").fadeOut();
    })

    $(".noCodeBtn").click(function(){
        $("#noCode").fadeIn();
    })


    $('.dataplay_cont .dataplaylist .sanjiao').click(function(){
        $(this).toggleClass('sanjiao_on');
        $(this).parent().siblings('.detail_info_cont').toggle();
          if($(this).parent().siblings('.detail_info_cont').find('.dataplaylist ').length==0){
            return;
        }
        var isshow = $(this).parent().siblings('.detail_info_cont').css('display');

        if(isshow==='block' && $(this).parent().siblings('.detail_info_cont').find('.dataplaylist ').length>0){
            $(this).parents('.title').css({'border-bottom':'1px dashed #ccc'});
            $(this).parents('.title').siblings('.detail_info_cont').find('.dataplaylist').css({'border-bottom':'1px dashed #ccc'});
            $(this).parents('.title').siblings('.detail_info_cont').find('.dataplaylist').last().css({'border-bottom':'0px'});
        }
        else{
            $(this).parents('.title').css({'border-bottom':'0px'});
        }
    });

    $('.pay_cont .dataplaylist .sanjiao').live("touchstart",function(){
        $(this).toggleClass('sanjiao_on');
        $(this).parent().siblings('.detail_info_cont').toggle();
    });

	//    新增部分
    var btn_01 = document.getElementById('btn01');
	function touch(event){
			$('.btn02_cont').css({'display':'none'});
			var id = 'btn01';
			if($('.'+ id+'_cont').css('display')=='block'){
				$('.'+ id+'_cont').css({'display':'none'});
			}
			else {
				$('.'+ id+'_cont').css({'display':'block'});
			}

	}
	if(btn_01 ){
		btn_01.addEventListener('touchstart',touch, false);
		btn_01.addEventListener('click',touch, false);
		
	}
    var btn_02 = document.getElementById('btn02');
	function touch1(event){
			$('.btn01_cont').css({'display':'none'});
			var id = 'btn02';
			if($('.'+ id+'_cont').css('display')=='block'){
				$('.'+ id+'_cont').css({'display':'none'});
			}
			else {
				$('.'+ id+'_cont').css({'display':'block'});
			}
	}
	if(btn_02){
		btn_02.addEventListener('touchstart',touch1, false);
		btn_02.addEventListener('click',touch1, false);
	}

    var dataplay = document.getElementById('dataplay_cont');
	function touch2(event){
			$('.btncont').css({'display':'none'});
	}
	if(dataplay){
		dataplay.addEventListener('touchstart',touch2, false);
		dataplay.addEventListener('click',touch2, false);
		
	}
})



/**** 是否为合法的手机号码，为了兼容国际写法，目前只判断了是否是+数字 ****/
function isMobilePhone(value) {
    if(value.search(/^(\+\d{2,3})?\d{11}$/) == -1)
        return false;
    else
        return true;
}
//替换指定传入参数的值,paramName为参数,replaceWith为新值
function replaceParamVal(paramName,replaceWith) { 
    var oUrl = this.location.href.toString();
	//var res = /('+ paramName+'=)([^&]*)/gi;
    var re=eval('/('+ paramName+'=)([^&]*)/gi'); 
	if(RegExp(re).test(oUrl) ){
		
		var nUrl = oUrl.replace(re,paramName+'='+replaceWith);


	}else nUrl  = oUrl+ '&'+paramName+'='+replaceWith;
    this.location = nUrl;
}




