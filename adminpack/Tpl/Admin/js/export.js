$(function(){
$('.SysExportButton').click(function(){
        var winW = $(window).width();
        var winH = $(window).height();
        $(this).nextAll('.SysExportDiv').css('left', winW/2-350+'px');
        $(this).nextAll('.SysExportDiv').css('top', winH/2-160+'px');
		if($(".SysExportBackground").val()=="")
		{
			$(".SysExportBackground").append('<iframe src="about:blank" style="width:100%;height:'+jQuery(document).height()+'px;filter:alpha(opacity=0);opacity:0;scrolling=no;z-index:99999">');
		}
        $('.SysExportBackground').show();
        $('.SysExportDiv').show();
        var index = $(this).index('.SysExportButton');
        $('.SysExportDiv form').hide();
        $('.SysExportDiv form:eq('+index+')').show();
    });
    
    $('.SysExportClose').click(function(){
        $('.SysExportDiv').hide();
		$('.SysExportBackground').hide();
       	//$('.SysExportDiv').prev().hide();
        return false;
    });
    
    $('.checkAll').click(function(){
        var cBox = $(this).parent().next().find('input[type=checkbox]');
        for(var i=0; i<cBox.length; i++){
            if( $(cBox[i]).attr('checked')==false ){
                $(cBox[i]).attr('checked',true);
            }else{
                $(cBox[i]).attr('checked',false);
            }
        }
    });

    //tabÇÐ»»
    $('.SysExportTab').click(function(){
        $(this).parent().children('.SysExportTab').removeClass('on');
        $(this).addClass('on');
        var index = $(this).index();
        $(this).parent().next().find('.SysExportPad').removeClass('on');
        $(this).parent().next().find('.SysExportPad:eq('+index+')').addClass('on');
    });
});