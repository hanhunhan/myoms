/* 
 * ���ڲ��
 * ����ѡȡ���ڣ��꣬�£��գ�
 * V1.1
 */
(function ($) {
    $.fn.select1 = function (options,Ycallback,Ncallback) {
        //���Ĭ��ѡ��

        var that = $(this);
        var docType = $(this).is('input');
        var indexY=0;
        var initY=0;
        var selectScroll=null;

        $.fn.date.defaultOptions = {
            curdate:false,                   //���Ƿ�λ��
            mode:null,                       //����ģʽ������ģʽ��
            event:"click",                    //�����ڲ��Ĭ�Ϸ�ʽΪ�����󵯳����� 
            show:true
        }
        //�û�ѡ��ǲ��Ĭ��ѡ��   
        var opts = $.extend( true, {datasource:options}, $.fn.date.defaultOptions, options );
        if(opts.theme === "datetime"){datetime = true;}
        if(!opts.show){
            that.unbind('click');
        }
        else{
            //���¼���Ĭ���¼�Ϊ��ȡ���㣩
            that.bind(opts.event,function () {
                createUL();      //��̬��ɿؼ���ʾ������
                init_iScrll();   //��ʼ��iscrll
                extendOptions(); //��ʾ�ؼ�
                that.blur();
                refreshDate();
                bindButton();
            })
        };
        function refreshDate(){
            selectScroll.refresh();
            resetInitDete();
         //   alert(indexY);
            selectScroll.scrollTo(0, initY*40, 100, true);
        }

        function resetIndex(){
            indexY=1
        }
        function resetInitDete(){
            if(opts.curdate){return false;}
            else if(that.val()===""){return false;}

            initY = indexY-1;
        }
        function bindButton(){
            resetIndex();
            $("#dateconfirm_select").unbind('click').click(function () {
                var datestr = $("#selectwrapper ul li:eq("+indexY+")").html()

                if(Ycallback===undefined){
                    if(docType){that.val(datestr);}else{that.html(datestr);}
                }else{
                    Ycallback(datestr);
                }
                $("#datePage_select").hide();
                $("#dateshadow").hide();
            });
            $("#datecancle_select").click(function () {
                $("#datePage_select").hide();
                $("#dateshadow").hide();
                Ncallback(false);
            });
        }
        function extendOptions(){
            $("#datePage_select").show();
            $("#dateshadow").show();
        }
        //���ڻ���
        function init_iScrll() {
            var strY = $("#selectwrapper ul li:eq("+indexY+")").html();

            selectScroll = new iScroll("selectwrapper",{snap:"li",vScrollbar:false,
                onScrollEnd:function () {
                    $("#selectwrapper ul li:eq("+indexY+")").css("color","#4c4c4c");

                    indexY = (this.y/40)*(-1)+1;
                    selectScroll.refresh();
                    $("#selectwrapper ul li:eq("+indexY+")").css("color","#41a0e0");
                }});
        };


        function  createUL(){
            CreateDateUI();
            $("#selectwrapper ul").html(createYEAR_UL());
        }
        function CreateDateUI(){
            var str = ''+
                '<div id="dateshadow"></div>'+
                '<div id="datePage_select" class="page">'+
                '<section>'+
                '<div id="datescroll_select">'+
                '<div id="selectwrapper">'+
                '<ul></ul>'+
                '</div>'+
                '</div>'+
                '</section>'+
                '<footer id="dateFooter">'+
                '<div id="setcancle">'+
                '<ul>'+
                '<li id="dateconfirm_select">ȷ��</li>'+
                '<li id="datecancle_select">ȡ��</li>'+
                '</ul>'+
                '</div>'+
                '</footer>'+
                '</div>'
            $("#selectPlugin").html(str);
        }
        function addTimeStyle(){
            $("#datePage_select").css("height","380px");
            $("#datePage_select").css("top","60px");
            $("#selectwrapper").css("position","absolute");
            $("#selectwrapper").css("bottom","200px");
        }
        //���� --��-- �б�
        function createYEAR_UL(){
            var str="<li>&nbsp;</li>";
            var len = opts.datasource.length;

            for(var i=0; i<=len-1;i++){
                str+='<li data_id='+ opts.datasource[i].id +'>'+opts.datasource[i].value+'</li>'
            }
            return str+"<li>&nbsp;</li>";;
        }
    }
})(jQuery);  
