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
    })

    $('.pay_cont .dataplaylist .sanjiao').live("click",function(){
        console.log($(this));
        $(this).toggleClass('sanjiao_on');
        $(this).parent().siblings('.detail_info_cont').toggle();
    });
})



/**** 是否为合法的手机号码，为了兼容国际写法，目前只判断了是否是+数字 ****/
function isMobilePhone(value) {
    if(value.search(/^(\+\d{2,3})?\d{11}$/) == -1)
        return false;
    else
        return true;
}





