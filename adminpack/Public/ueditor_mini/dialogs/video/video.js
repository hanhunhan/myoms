
(function(){
    var domUtils = UM.dom.domUtils;
    var widgetName = 'insertvideo';

    UM.registerWidget( widgetName,{

        tpl: "<link rel=\"stylesheet\" type=\"text/css\" href=\"<%=video_url%>video.css\" />" +
            "<div class=\"edui-video-wrapper\">" +
            "<div id=\"eduiVideoTab\">" +
            "<div id=\"eduiVideoTabHeads\" class=\"edui-video-tabhead\">" +
            "<span tabSrc=\"video\" class=\"edui-video-focus\"><%=lang_tab_insertV%></span>" +
            "</div>" +
            "<div id=\"eduiVideoTabBodys\" class=\"edui-video-tabbody\">" +
            "<div id=\"eduiVideoPanel\" class=\"edui-video-panel\">" +
            "<table><tr><td><label for=\"eduiVideoUrl\" class=\"edui-video-url\"><%=lang_video_url%></label></td><td><input id=\"eduiVideoUrl\" type=\"text\"></td></tr></table>" +
            "<div id=\"eduiVideoPreview\"></div>" +
            "<div id=\"eduiVideoInfo\">" +
            "<fieldset>" +
            "<legend><%=lang_video_size%></legend>" +
            "<table>" +
            "<tr><td><label for=\"eduiVideoWidth\"><%=lang_videoW%></label></td><td><input class=\"edui-video-txt\" id=\"eduiVideoWidth\" type=\"text\"/></td></tr>" +
            "<tr><td><label for=\"eduiVideoHeight\"><%=lang_videoH%></label></td><td><input class=\"edui-video-txt\" id=\"eduiVideoHeight\" type=\"text\"/></td></tr>" +
            "</table>" +
            "</fieldset>" +
            "<fieldset>" +
            "<legend><%=lang_alignment%></legend>" +
            "<div id=\"eduiVideoFloat\"></div>" +
            "</fieldset>" +
            "</div>" +
            "</div>" +
            "</div>" +
            "</div>" +
            "</div>",
        initContent:function( editor, $widget ){

            var me = this,
                lang = editor.getLang( widgetName),
                video_url = UMEDITOR_CONFIG.UMEDITOR_HOME_URL + 'dialogs/video/';

            me.lang = lang;
            me.editor = editor;
            me.root().html( $.parseTmpl( me.tpl, $.extend( { video_url: video_url }, lang['static'] ) ) );

            me.initController( lang );

        },
        initEvent:function(){

            var me = this,
                url = $("#eduiVideoUrl")[0];

            if( 'oninput' in url ) {
                url.oninput = function(){
                    me.createPreviewVideo( this.value );
                };
            } else {
                url.onpropertychange = function () {
                    me.createPreviewVideo( this.value );
                }
            }

        },
        initController: function( lang ){

            var me = this,
                img = me.editor.selection.getRange().getClosedNode(),
                url;

            me.createAlignButton( ["eduiVideoFloat"] );

            //�༭��Ƶʱ��ʼ�������Ϣ
            if(img && img.className == "edui-faked-video"){
                $("#eduiVideoUrl")[0].value = url = img.getAttribute("_url");
                $("#eduiVideoWidth")[0].value = img.width;
                $("#eduiVideoHeight")[0].value = img.height;
                var align = domUtils.getComputedStyle(img,"float"),
                    parentAlign = domUtils.getComputedStyle(img.parentNode,"text-align");
                me.updateAlignButton(parentAlign==="center"?"center":align);
            }
            me.createPreviewVideo(url);

        },
        /**
         * ����url������ƵԤ��
         */
        createPreviewVideo: function(url){

            if ( !url )return;
            var matches = url.match(/youtu.be\/(\w+)$/) || url.match(/youtube\.com\/watch\?v=(\w+)/) || url.match(/youtube.com\/v\/(\w+)/),
                youku = url.match(/youku\.com\/v_show\/id_(\w+)/),
                youkuPlay = /player\.youku\.com/ig.test(url),
                me = this,
                lang = me.lang;

            if(!youkuPlay){
                if (matches){
                    url = "https://www.youtube.com/v/" + matches[1] + "?version=3&feature=player_embedded";
                }else if(youku){
                    url = "http://player.youku.com/player.php/sid/"+youku[1]+"/v.swf"
                }else if(!me.endWith(url,[".swf",".flv",".wmv"])){
                    $("#eduiVideoPreview").html( lang.urlError );
                    return;
                }
            }else{
                url = url.replace(/\?f=.*/,"");
            }
            $("#eduiVideoPreview")[0].innerHTML = '<embed type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"' +
                ' src="' + url + '"' +
                ' width="' + 420  + '"' +
                ' height="' + 280  + '"' +
                ' wmode="transparent" play="true" loop="false" menu="false" allowscriptaccess="never" allowfullscreen="true" ></embed>';

        },
        /**
         * ��������Ƶ��Ϣ����༭����
         */
        insertSingle: function(){

            var me = this,
                width = $("#eduiVideoWidth")[0],
                height = $("#eduiVideoHeight")[0],
                url=$('#eduiVideoUrl')[0].value,
                align = this.findFocus("eduiVideoFloat","name");

            if(!url) return false;
            if ( !me.checkNum( [width, height] ) ) return false;
            this.editor.execCommand('insertvideo', {
                url: me.convert_url(url),
                width: width.value,
                height: height.value,
                align: align
            });

        },
        /**
         * URLת��
         */
        convert_url: function(s){
            return s.replace(/http:\/\/www\.tudou\.com\/programs\/view\/([\w\-]+)\/?/i,"http://www.tudou.com/v/$1")
                .replace(/http:\/\/www\.youtube\.com\/watch\?v=([\w\-]+)/i,"http://www.youtube.com/v/$1")
                .replace(/http:\/\/v\.youku\.com\/v_show\/id_([\w\-=]+)\.html/i,"http://player.youku.com/player.php/sid/$1")
                .replace(/http:\/\/www\.56\.com\/u\d+\/v_([\w\-]+)\.html/i, "http://player.56.com/v_$1.swf")
                .replace(/http:\/\/www.56.com\/w\d+\/play_album\-aid\-\d+_vid\-([^.]+)\.html/i, "http://player.56.com/v_$1.swf")
                .replace(/http:\/\/v\.ku6\.com\/.+\/([^.]+)\.html/i, "http://player.ku6.com/refer/$1/v.swf");
        },
        /**
         * ��⴫�������input��������ĳ����Ƿ�������
         */
        checkNum: function checkNum( nodes ) {

            var me = this;

            for ( var i = 0, ci; ci = nodes[i++]; ) {
                var value = ci.value;
                if ( !me.isNumber( value ) && value) {
                    alert( me.lang.numError );
                    ci.value = "";
                    ci.focus();
                    return false;
                }
            }
            return true;
        },
        /**
         * �����ж�
         * @param value
         */
        isNumber: function( value ) {
            return /(0|^[1-9]\d*$)/.test( value );
        },
        updateAlignButton: function( align ) {
            var aligns = $( "#eduiVideoFloat" )[0].children;

            for ( var i = 0, ci; ci = aligns[i++]; ) {
                if ( ci.getAttribute( "name" ) == align ) {
                    if ( ci.className !="edui-video-focus" ) {
                        ci.className = "edui-video-focus";
                    }
                } else {
                    if ( ci.className =="edui-video-focus" ) {
                        ci.className = "";
                    }
                }
            }

        },
        /**
         * ����ͼƬ����ѡ��ť
         * @param ids
         */
        createAlignButton: function( ids ) {

            var lang = this.lang,
                vidoe_home = UMEDITOR_CONFIG.UMEDITOR_HOME_URL + 'dialogs/video/';

            for ( var i = 0, ci; ci = ids[i++]; ) {
                var floatContainer = $( "#" + ci ) [0],
                    nameMaps = {"none":lang['default'], "left":lang.floatLeft, "right":lang.floatRight};
                for ( var j in nameMaps ) {
                    var div = document.createElement( "div" );
                    div.setAttribute( "name", j );
                    if ( j == "none" ) div.className="edui-video-focus";
                    div.style.cssText = "background:url("+ vidoe_home +"images/" + j + "_focus.jpg);";
                    div.setAttribute( "title", nameMaps[j] );
                    floatContainer.appendChild( div );
                }
                this.switchSelect( ci );
            }
        },
        /**
         * ѡ���л�
         */
        switchSelect: function( selectParentId ) {
            var selects = $( "#" + selectParentId )[0].children;
            for ( var i = 0, ci; ci = selects[i++]; ) {
                domUtils.on( ci, "click", function () {
                    for ( var j = 0, cj; cj = selects[j++]; ) {
                        cj.className = "";
                        cj.removeAttribute && cj.removeAttribute( "class" );
                    }
                    this.className = "edui-video-focus";
                } )
            }
        },
        /**
         * �ҵ�id�¾���focus��Ľڵ㲢���ظýڵ��µ�ĳ������
         * @param id
         * @param returnProperty
         */
        findFocus: function( id, returnProperty ) {
            var tabs = $( "#" + id )[0].children,
                property;
            for ( var i = 0, ci; ci = tabs[i++]; ) {
                if ( ci.className=="edui-video-focus" ) {
                    property = ci.getAttribute( returnProperty );
                    break;
                }
            }
            return property;
        },
        /**
         * ĩβ�ַ����
         */
        endWith: function(str,endStrArr){
            for(var i=0,len = endStrArr.length;i<len;i++){
                var tmp = endStrArr[i];
                if(str.length - tmp.length<0) return false;

                if(str.substring(str.length-tmp.length)==tmp){
                    return true;
                }
            }
            return false;
        },
        width:610,
        height:498,
        buttons: {
            ok: {
                exec: function( editor ){
                    $("#eduiVideoPreview").html("");
                    editor.getWidgetData(widgetName).insertSingle();
                }
            },
            cancel: {
                exec: function(){
                    //�����Ƶ
                    $("#eduiVideoPreview").html("");
                }
            }
        }
    });

})();