document.writeln(" <form action=\"\" method=\"post\"><div class=\"alert-bg j-alertbg\"></div>");
document.writeln("        <!-- 排序start -->");
document.writeln("        <textarea id='last_filter_result' style='display: none' name='last_filter_result'></textarea>");
document.writeln("        <div class=\"sequence j-sequence-alert\">");
document.writeln("            <div class=\"alert-tit\"><span><i class=\"glyphicon glyphicon-sort\"></i>&nbsp;&nbsp;排序</span><a href=\"javascript:;\" class=\"alert-close fright j-close\"></a></div>");
document.writeln("            <div class=\"alert-cont\">");
document.writeln("                <ul class=\"sequence-cont\">");
document.writeln(" <li>");
document.writeln("                        <select class=\"sequence-first\" name=\"sort1\">");
document.writeln("                            <option> </option>");
document.writeln("                        </select>");
document.writeln("                        <select class=\"sequence-second\" name=\"sort1_t\" >");
document.writeln("                            <option value=\"\">请选择</option>");
document.writeln("                            <option value=\"1\">升序</option>");
document.writeln("                            <option value=\"2\">降序</option>");
document.writeln("                        </select>");
document.writeln("                    </li>");
document.writeln("                    <li>");
document.writeln("                        <select class=\"sequence-first\" name=\"sort2\">");
document.writeln("                            <option> </option>");
document.writeln("                        </select>");
document.writeln("                        <select class=\"sequence-second\" name=\"sort2_t\">");
document.writeln("                            <option value=\"1\">升序</option>");
document.writeln("                            <option value=\"2\">降序</option>");
document.writeln("                        </select>");
document.writeln("                    </li>");
document.writeln("                    <li>");
document.writeln("                        <select class=\"sequence-first\" name=\"sort3\">");
document.writeln("                            <option> </option>");
document.writeln("                        </select>");
document.writeln("                        <select class=\"sequence-second\" name=\"sort3_t\">");
document.writeln("                            <option value=\"1\">升序</option>");
document.writeln("                            <option value=\"2\">降序</option>");
document.writeln("                        </select>");
document.writeln("                    </li>");
document.writeln("                </ul>");
document.writeln("            </div>");
document.writeln("            <div class=\"alert-foot\">");
document.writeln("           <input type=\"hidden\" name=\"page\" value=\"1\"/> ");
document.writeln("                <input type=\"submit\" class=\"btn btn-primary btn-sm\"   value=\"排&nbsp;序\"/>");
document.writeln("                <input type=\"button\" class=\"j-close btn btn-default btn-sm\" value=\"关&nbsp;闭\"/>");
document.writeln("            </div>");
document.writeln("        </div></form>");
document.writeln("        <!-- 排序end -->");
document.writeln("        <!-- 搜索start -->");
document.writeln("       <form action=\"\" method=\"post\"> <div class=\"search j-search-alert\">");
document.writeln("            <div class=\"alert-tit\"><span><i class=\"glyphicon glyphicon-search\"></i>&nbsp;&nbsp;搜索</span><a href=\"javascript:;\" class=\"alert-close fright j-close\"></a></div>");
document.writeln("            <div class=\"alert-cont\">");
document.writeln("                <ul class=\"search-cont\">");
document.writeln("<li>");
document.writeln("                        <select class=\"search-first\" name=\"search1\">");
document.writeln("                            <option> </option>");
document.writeln("                        </select>");
document.writeln("                        <select class=\"search-second\"  name=\"search1_s\">");
document.writeln("                            <option value=\"3\">=</option>");
document.writeln("                            <option value=\"1\">模糊</option>");
document.writeln("                            <option value=\"2\">为空</option>");

document.writeln("                            <option value=\"4\">非空</option>");
document.writeln("                            <option value=\"5\"> >= </option>");
document.writeln("                            <option value=\"6\"> <= </option>");
document.writeln("                            <option value=\"7\"> > </option>");
document.writeln("                            <option value=\"8\"> < </option>");

document.writeln("                        </select>");
document.writeln("                        <span name=\"search1_t_span\"  ><input type=\"text\" value=\"\" class=\"search-input\" name=\"search1_t\" /></span>");
document.writeln("                    </li>");
document.writeln("                    <li>");
document.writeln("                        <select class=\"search-first\" name=\"search2\" >");
document.writeln("                            <option> </option>");
document.writeln("                        </select>");
document.writeln("                        <select class=\"search-second\" name=\"search2_s\">");
document.writeln("                            <option value=\"3\">=</option>");
document.writeln("                            <option value=\"1\">模糊</option>");
document.writeln("                            <option value=\"2\">为空</option>");

document.writeln("                            <option value=\"4\">非空</option>");
document.writeln("                            <option value=\"5\"> >= </option>");
document.writeln("                            <option value=\"6\"> <= </option>");
document.writeln("                            <option value=\"7\"> > </option>");
document.writeln("                            <option value=\"8\"> < </option>");
document.writeln("                        </select>");
document.writeln("                       <span name=\"search2_t_span\"  > <input type=\"text\" value=\"\" class=\"search-input\" name=\"search2_t\"/></span>");
document.writeln("                    </li>");
document.writeln("                    <li>");
document.writeln("                        <select class=\"search-first\" name=\"search3\">");
document.writeln("                            <option> </option>");
document.writeln("                        </select>");
document.writeln("                        <select class=\"search-second\" name=\"search3_s\">");
document.writeln("                            <option value=\"3\">=</option>");
document.writeln("                            <option value=\"1\">模糊</option>");
document.writeln("                            <option value=\"2\">为空</option>");

document.writeln("                            <option value=\"4\">非空</option>");
document.writeln("                            <option value=\"5\"> >= </option>");
document.writeln("                            <option value=\"6\"> <= </option>");
document.writeln("                            <option value=\"7\"> > </option>");
document.writeln("                            <option value=\"8\"> < </option>");
document.writeln("                        </select>");
document.writeln("                        <span name=\"search3_t_span\"  ><input type=\"text\" value=\"\" class=\"search-input\" name=\"search3_t\"/></span>");
document.writeln("                    </li>");
document.writeln("                    <li>");
document.writeln("                        <select class=\"search-first\" name=\"search4\">");
document.writeln("                            <option> </option>");
document.writeln("                        </select>");
document.writeln("                        <select class=\"search-second\" name=\"search4_s\">");
document.writeln("                            <option value=\"3\">=</option>");
document.writeln("                            <option value=\"1\">模糊</option>");
document.writeln("                            <option value=\"2\">为空</option>");

document.writeln("                            <option value=\"4\">非空</option>");
document.writeln("                            <option value=\"5\"> >= </option>");
document.writeln("                            <option value=\"6\"> <= </option>");
document.writeln("                            <option value=\"7\"> > </option>");
document.writeln("                            <option value=\"8\"> < </option>");
document.writeln("                        </select>");
document.writeln("                        <span name=\"search4_t_span\"  ><input type=\"text\" value=\"\" class=\"search-input\"  name=\"search4_t\"/></span>");
document.writeln("                    </li>");
document.writeln("                </ul>");
document.writeln("            </div>");
document.writeln("            <div class=\"alert-foot\">  ");
document.writeln("           <input type=\"hidden\" name=\"page\" value=\"1\"/> ");

document.writeln("                <input type=\"submit\" class=\"btn btn-sm btn-primary\" value=\"搜&nbsp;索\"/>");
document.writeln("                <input type=\"button\" class=\"j-close btn btn-sm btn-default\" value=\"关&nbsp;闭\" />");
document.writeln("            </div>");
document.writeln("        </div></form>");


//flash插件安装提示
document.writeln("<div id=\"thp_notf_div\" class=\"hpn_top_container\" style=\"display:none;top:0px;\">");
document.writeln("	<span class=\"hpn_top_icon\">");
//document.writeln("		<img class=\"rms_img\" src=\"./public/images/xixi.png\">");
document.writeln("	</span>");
document.writeln("	<span class=\"hpn_top_desc\">您的浏览器未安装flash插件,无法使用上传附件功能!</span>");
document.writeln("	<a href=\"javascript:void(0);\" class=\"hpn_top_link\" >安装flash player</a>");
document.writeln("	<a href=\"#\" class=\"hpn_top_close\">我要拒绝</a>");
document.writeln("	");
document.writeln("</div>");


//Powered By smvv @hi.baidu.com/smvv21
function flashChecker() {
    var hasFlash = 0; //是否安装了flash
    var flashVersion = 0; //flash版本

    if (document.all) {
        if (!!window.ActiveXObject || "ActiveXObject" in window) {
            //if(swf) {
            var swf = new ActiveXObject('ShockwaveFlash.ShockwaveFlash');
            hasFlash = 1;
            VSwf = swf.GetVariable("$version");
            flashVersion = parseInt(VSwf.split(" ")[1].split(",")[0]);
        }
    } else {
        if (navigator.plugins && navigator.plugins.length > 0) {
            var swf = navigator.plugins["Shockwave Flash"];
            if (swf) {
                hasFlash = 1;
                var words = swf.description.split(" ");
                for (var i = 0; i < words.length; ++i) {
                    if (isNaN(parseInt(words[i]))) continue;
                    flashVersion = parseInt(words[i]);
                }
            }
        }
    }
    return {f: hasFlash, v: flashVersion};
}

var fls = flashChecker();
if (fls.f) {

} else {
    $("#thp_notf_div").slideDown();
}

$(document).ready(function () {
    $(".hpn_top_close").click(function () {
        $("#thp_notf_div").slideUp();
    });
    $(".hpn_top_link").click(function () {

        window.open("https://get.adobe.com/cn/flashplayer");
    });
    $(".j-refresh").click(function () {
        location.reload();
    });
    $(".j-showalert").click(function () {
        $(".j-alertbg").show();
        var tag = "." + $(this).attr("id") + "-alert";

        if ($(this).attr("id") == 'j-sequence') getSequenceHtml();
        if ($(this).attr("id") == 'j-search') getSearchHtml();
        var h = $(document).scrollTop();
        $(tag).show().css('margin-top', -170 + h);
    });
    $(".j-close").click(function () {
        $(this).parent().parent().hide();
        $(".j-alertbg").hide();
    });
    $(".j-formclose").click(function () {
        if (document.referrer) {
            self.location = document.referrer;
        } else window.close();
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.layer.close(index);
    });


    $("#checkall").click(
        function () {
            if (this.checked) {
                $("input[name='checkedtd']").each(function () {
                    this.checked = true;
                });
            } else {
                $("input[name='checkedtd']").each(function () {
                    this.checked = false;
                });
            }
        }
    );
    $(".search-first").change(function () {
        var attrname = $(this).attr('name');
        getSelectOption($(this).val(), attrname);
    });
});

function getSequenceHtml() {
    $.ajax({
        type: 'GET',
        url: '',
        data: '&faction=getSortCols',
        beforeSend: function () {

        },
        success: function (d) {

            var data = eval("(" + d + ")");
            if (data) {
                var html = '<option value="">请选择</option>';
                for (var i in data) {
                    html += '<option value="' + data[i]["FIELDNAME"] + '">' + data[i]["FIELDMEANS"] + '</option>';
                }
                $(".sequence-first").html(html);
            }

        }
    });
}

function getSearchHtml() {
    var FILTER_CONDITION_ROW = 4;
    var text = $('#last_filter_result').text();
    var lastFilterObj;
    if (text) {
        lastFilterObj = JSON.parse(text);
    }

    function updateSearchBox() {
        var suffixes = ['', '_s', '_t', '_t_select'];
        var conditions = [];
        for (var i = 1; i <= FILTER_CONDITION_ROW; i++) {
            var group = [];
            for (var j = 0; j < 4; j++) {
                var key = '.j-search-alert:eq(0) .search-cont > li:eq(' + (i - 1) + ') [name="search' + i + suffixes[j] + '"]';
                var val = lastFilterObj['search' + i + suffixes[j]];
                group.push({
                    suffix: suffixes[j],
                    key:key,
                    val: val
                });
            }
            conditions.push(group);
        }

        for(var i = 0; i < FILTER_CONDITION_ROW; i++) {
            // 检索条件
            if (conditions[i][0] && conditions[i][0]['val']) {
                $(conditions[i][1]['key']).val(conditions[i][1]['val']);


                var appendData;  // 附加上次数据
                if (conditions[i][3]['val']) {
                    appendData = [conditions[i][2], conditions[i][3]];
                } else {
                    appendData = [conditions[i][2]];
                }

                getSelectOption(conditions[i][0]['val'], 'search' + (i + 1), appendData);
                $(conditions[i][0]['key']).val(conditions[i][0]['val']);
            }
        }
    }

    $.ajax({
        type: 'GET',
        url: '',
        data: '&faction=getFilterCols',
        beforeSend: function () {

        },
        success: function (d) {
            var data = eval("(" + d + ")");
            if (data) {
                var html = '<option value="">请选择</option>';
                for (var i in data) {
                    html += '<option value="' + data[i]["FIELDNAME"] + '">' + data[i]["FIELDMEANS"] + '</option>';
                }
                $(".search-first").html(html);

                if (lastFilterObj) {
                    updateSearchBox();
                }
            }
        }
    });
}

//获得select 
function getSelectOption(field, attrname) {
    var thirdArgumentArr = [];
    if (arguments.length > 2) {
        thirdArgumentArr = arguments[arguments.length - 1];
    }

    $.ajax({
        type: 'GET',
        url: '',
        data: '&faction=getSelectOption&selectfield=' + field,
        beforeSend: function () {

        },
        success: function (d) {
            if (d) {
                var data = eval("(" + d + ")");
                if (data) {
                    if (data.type == 'autocomplete') {
                        var datas = data.list;
                        datas = $.map(data.list, function (item) {
                            return {
                                label: item.label,
                                id: item.id
                            }
                        });
                        $("[name='" + attrname + "_t_span']").html('<input type="text"   class="search-input"  name="' + attrname + '_t_select"/><input type="hidden"  name="' + attrname + '_t"/>');
                        completeData(attrname, datas, field);
                    }
                    if (data.type == 'select') {
                        var html = '<option value="">请选择</option>';
                        var d = data.list
                        for (var i in d) {
                            html += '<option value="' + i + '">' + d[i] + '</option>';
                        }
                        $("[name='" + attrname + "_t_span']").html('<select  class="search-input" name="' + attrname + '_t">' + html + '</select>');

                    } else if (data.type == 'date') {
                        $("[name='" + attrname + "_t_span']").html('<input type="text" onFocus="WdatePicker({dateFmt:\'yyyy-MM-dd\',alwaysUseStartDate:true})" class="search-input"  name="' + attrname + '_t"/> <input type="hidden"  name="' + attrname + '_t_type" value="date"/>');
                    } else if (data.type == 'tree') {
                        $("[name='" + attrname + "_t_span']").html('<select  class="search-input" name="' + attrname + '_t">' + data.list + '</select>');
                    } else if (data.type == 'input') {
                        $("[name='" + attrname + "_t_span']").html('<input type="text"   class="search-input"  name="' + attrname + '_t"/>');
                    }

                    // 附加上默认值
                    if (thirdArgumentArr.length > 0) {
                        for(var j = 0; j < thirdArgumentArr.length; j++) {
                            $(thirdArgumentArr[j]['key']).val(thirdArgumentArr[j]['val']);
                        }
                    }
                }
            } else {
                $("[name='" + attrname + "_t_span']").html('<input type="text"   class="search-input"  name="' + attrname + '_t"/>');
            }
        }
    });
}

function completeData(attrname, datas, field) {
    $("[name='" + attrname + "_t_select']").autocomplete({
        source: datas,
        select: function (event, ui) {
            $("[name='" + attrname + "_t']").val(ui.item.id);
            $("[name='" + attrname + "_t_select']").val(ui.item.lable);
        }
    });
}

function fedit(obj, url) {
    if (url) {
        var refer;
        if (arguments.length > 2) {
            refer = arguments[2];
        } else {
            refer = $('#refer_from').val();
        }
        if (refer) {
            url += '&fromUrl=' + encodeURIComponent(refer);
        }
        window.location.href = url;
    } else {
        if ($(obj).hasClass('fedit')) {
            $(obj).removeClass('fedit').addClass('fcanel').html('取消').parent().siblings('td').each(function () {
                $(this).children("span").first().hide().next('span').show();
            });
        } else {
            $(obj).removeClass('fcanel').addClass('fedit').html('编辑').parent().siblings('td').each(function () {
                $(this).children("span").first().show().next('span').hide();
            });
        }
        $("#LOCATIONURL").val(window.location.href);
    }
}

//select联动函数
function getNextcol(name, val, rid, pvalue) {
    $.ajax({
        type: 'GET',
        url: '',
        data: 'fieldname=' + name + '&parentkey=' + val + '&faction=getNextcol',
        beforeSend: function () {

        },
        success: function (d) {
            var html = '<option value="">请选择</option>';
            var data = eval("(" + d + ")");
            if (data) {
                for (var i in data) {
                    if (i == pvalue) {
                        html += '<option value="' + i + '" selected="selected">' + data[i] + '</option>';
                        if (rid) $("select[name='" + rid + '_' + name + "']").parent().siblings().html(data[i]);
                        else $("select[name='" + name + "']").parent().siblings().html(data[i]);
                    } else html += '<option value="' + i + '">' + data[i] + '</option>';
                }
                if (rid) $("select[name='" + rid + '_' + name + "']").html(html);
                else $("select[name='" + name + "']").html(html);
            }

        }
    });
}
//动态加载select
function getDtSelectOption(obj, name, rid, pvalue) {
    /*if($(obj).attr('sbj')=='0'){
     $.ajax({
     type: 'GET',
     url: '',
     data: 'fieldname='+name+'&faction=getNextcol',
     beforeSend :function(){

     },
     success: function(d){

     var html =   '';
     var data = eval("("+d+")");
     if(data){
     for(var i in data){
     if(i==pvalue){
     //html += '<option value="'+i+'" selected="selected">'+data[i]+'</option>';
     if(rid) $("select[name='"+rid+'_'+name+"']").parent().siblings().html(data[i]);
     else $("select[name='"+name+"']").parent().siblings().html(data[i]);
     }else html += '<option value="'+i+'">'+data[i]+'</option>';
     }
     //if(rid) $("select[name='"+rid+'_'+name+"']").append(html);
     //else $("select[name='"+name+"']").append(html);
     //if(rid) var namee = rid+'_'+$(obj).attr();
     $(obj).append(html);
     $(obj).attr('sbj','1');
     }
     }
     });
     }*/
}
//动态加载tree
function getDtSelectTreeOption(obj, name, rid, pvalue) {
    if ($(obj).attr('sbj') == '0') {
        $.ajax({
            type: 'GET',
            url: '',
            data: 'fieldname=' + name + '&faction=getSelectTreeOption',
            beforeSend: function () {

            },
            success: function (d) {


                if (d) {

                    //if(rid) $("select[name='"+rid+'_'+name+"']").html(d);
                    //else $("select[name='"+name+"']").html(d);
                    $(obj).html(d);
                    $(obj).attr('sbj', '1');
                }


            }
        });
    }
}

//快速新增
function quickadd() {

    var html = $('.quickadd').html();
    html = '<tr class="newadds">' + html + '</tr>';
    $('.contractinfo-table .itemlist input').attr('disabled', true);
    $('.contractinfo-table .itemlist select').attr('disabled', true);
    $('.contractinfo-table .itemlist textarea').attr('disabled', true);
    $('.contractinfo-table tbody').append(html);
    var addids = $('.newadds').length;
    $('.contractinfo-table').find(' .newadds :last').find("input,select,textarea").each(function () {
        $(this).attr('name', $(this).attr('name') + addids);
    });
    $("#addids").val(addids);
    $("#LOCATIONURL").val(window.location.href);
    $(".newadds").find('td').each(function () {

        if ($(this).children("span").first().show().next('span').hide().find('input').attr('readonly') != 'readonly' && $(this).children("span").first().show().next('span').hide().children().attr('readonly') != 'readonly') {
            $(this).children("span").first().hide().next('span').show();
        }

    });

    var demo = $(".contractinfo-table").Validform();
    demo.config({
        tiptype: function (msg, o, cssctl) {
            //msg：提示信息;
            //o:{obj:*,type:*,curform:*}, obj指向的是当前验证的表单元素（或表单对象），type指示提示的状态，值为1、2、3、4， 1：正在检测/提交数据，2：通过验证，3：验证失败，4：提示ignore状态, curform为当前form对象;
            //cssctl:内置的提示信息样式控制函数，该函数需传入两个参数：显示提示信息的对象 和 当前提示的状态（既形参o中的type）;

            if (!o.obj.is("form")) {  //验证表单元素时o.obj为该表单元素，全部验证通过提交表单时o.obj为该表单对象;
                o.obj.parents("td").append('<div class="info"><span class="Validform_checktip"> </span><span class="dec"><s class="dec1">&#9670;</s><s class="dec2">&#9670;</s></span></div>');

                var objtip = o.obj.parents("td").find(".Validform_checktip");
                cssctl(objtip, o.type);
                objtip.text(msg);

                var infoObj = o.obj.parents("td").find(".info");
                if (o.type == 2) {
                    infoObj.fadeOut(200);
                } else {
                    if (infoObj.is(":visible")) {
                        return;
                    }
                    var left = o.obj.offset().left,
                        top = o.obj.offset().top;

                    infoObj.css({
                        left: left + 20,
                        top: top - 45
                    }).show().animate({
                        top: top - 35
                    }, 200);
                }

            }
        }
    });
}

//子页面
function toiframe(url, obj, formno, pid) {
    var id = $(".contractinfo-table tbody .selected").attr("fid");
    if (toiframetype__ == 1) {
        pid = pid ? pid : id;
        url = url + '&parentchooseid=' + pid + '&childrenformno=' + formno;
        if (pid)$("#ifm").attr('src', url);
    } else if (toiframetype__ == 2) {
        url = url + '&parentchooseid=' + id;
        if (url)$("#ifm").attr('src', url);

    }

    if (obj) $(obj).parent('li').addClass('on').siblings().removeClass("on");
    else $('.twolevelul').find('.twolevelli').first().addClass('on').siblings().removeClass("on");
}

function __d() {
    $(document).ready(function () {
        $('.showparent').show();  //隐藏父页面专用标签
        var formno = $(".twolevelul .on").attr('fno');
        $(".contractinfo-table tbody tr").click(function () {
            $(this).siblings().removeClass("selected");
            $(this).addClass("selected");
            formno = $("#ifmulli .on").attr('fno');
            //alert(formno);
            toiframe(actionUrl, null, formno, null);
        });

        $(".contractinfo-table tbody tr:first").addClass("selected");
        var pid = $(".contractinfo-table tbody tr:first").attr('fid');
        toiframe(actionUrl, null, formno, pid);
    });
}

//状态颜色
function findcolor(data, status) {
    var temp = new Array();

    for (x in data) {
        temp[data[x]['STATUS']] = data[x]['COLOR'];
    }
    return temp[status];
}

//按钮居中
$(document).ready(function () {
    $(window).resize(function () {
        $('.buttons').css({
            position: 'absolute',
            left: ($(window).width() - $('.buttons').outerWidth()) / 2,
            top: ($(window).height() - $('.buttons').outerHeight()) / 2 + $(document).scrollTop()
        });
    });
    //初始化函数
    $(window).resize();
});

//fixed定位浮动按钮
$(document).ready(function () {
    $(".itemlist").on("click", function () {
        var table_top = $(this).offset().top + 28 + "px";
        var tr_index = $("tr").index($(this));
        var list_length = $(".itemlist").length;
        var ab_length = list_length - tr_index;
        var tab_top = $("table").height() + 22 + "px";
        $('.buttons').css("position", "absolute");
        $(".buttons").css("top", table_top);
        $(".buttons").toggleClass("btn_show");
    })
});

//重置pagesize
function form_resetpagesize(url, pagesize) {
    window.location.href = url + '&pageSize=' + pagesize;
}


$("#right_frame_h").load(function(){

});

//设置IFRAME的高度
//function SetWinHeight(obj) {
//    var mainheight = $("#ifm").contents().find(".registerform").height()+30;
//    console.log(mainheight);
//    $("#ifm").height(mainheight);
//
//    var win = obj;
//    if (document.getElementById) {
//        if (win && !window.opera) {
//            if (win.contentDocument && win.contentDocument.body.offsetHeight) {
//                console.log(win.contentDocument);
//                console.log(win.contentDocument.body.offsetHeight);
//                console.log(win.contentDocument.body.clientHeight);
//                win.height = win.contentDocument.body.offsetHeight + 100;
//            }
//            else if (win.Document && win.Document.body.scrollHeight) {
//                win.height = win.Document.body.scrollHeight + 100;
//            }
//        }
//    }
//}

//无列表按钮显示
$(document).ready(function () {
    var tb_h = $(".contractinfo-table").height();

    if (tb_h <= 30) {
        $(".buttons").css({
            display: 'block',
            position: 'relative',
            left: 0 + 'px',
            top: 0 + 'px'
        })
    }

    // 取消表单的默认提交事件
    $(window).keydown(function (event) {
        if (event.keyCode == 13) {
            //event.preventDefault();
            //return false;
        }
    });

    //iframe 框架
    $("#ifm").load(function(){
        var mainheight = $("#ifm").contents().find(".registerform").height();
        var before_registerform = $("#ifm").contents().find(".before-registerform").height();


        if(!mainheight)
            mainheight = $("#ifm").contents().find(".registerform2").height();


        if(before_registerform)
            mainheight = mainheight + before_registerform;

        mainheight = mainheight + 20;
        $("#ifm").height(mainheight);
    });

});

var DISPLAY_OPTION_BTN = {
    SHOW: 1,
    HIDE: 2
};

var REG_EXPS = {
    ADD: /\u65b0\u589e/,  // 新增
    COMMIT: /\u63d0\u4ea4/,  // 提交
    DECLARE: /\u7533\u62a5/,  // 申报
    APPLY: /\u7533\u8bf7/, // 申请
    CHECK: /\u67e5\u770b/,  // 查看
    ORDER: /\u6392\u5e8f/, // 排序
    SEARCH: /\u641c\u7d22/,  // 搜索
    REFRESH: /\u5237\u65b0/,  // 刷新
    REIM: /\u62a5\u9500/  // 报销
};

function save2DigsAfterPoint(num) {
    return Math.round(num * 100) / 100;
}