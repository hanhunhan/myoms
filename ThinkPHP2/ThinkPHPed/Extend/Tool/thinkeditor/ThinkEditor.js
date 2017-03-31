(function ($) {

    var ie = $.browser.msie,
		iOS = /iphone|ipad|ipod/i.test(navigator.userAgent);

    $.TE = {
		version:'1.0', // �汾��
        debug: 1, //���Կ���
        timeOut: 3000, //���ص����ļ���ʱʱ�䣬��λΪ���롣
        defaults: {
            //Ĭ�ϲ���controls,noRigths,plugins,������ز��
            controls: "source,|,undo,redo,|,cut,copy,paste,pastetext,selectAll,blockquote,|,image,flash,table,hr,pagebreak,face,code,|,link,unlink,|,print,fullscreen,|,eq,|,style,font,fontsize,|,fontcolor,backcolor,|,bold,italic,underline,strikethrough,unformat,|,leftalign,centeralign,rightalign,blockjustify,|,orderedlist,unorderedlist,indent,outdent,|,subscript,superscript",
            //noRights:"underline,strikethrough,superscript",
            width: 740,
            height: 500,
            skins: "default",
            resizeType: 2,
            face_path: ['qq_face', 'qq_face'],
            minHeight: 200,
            minWidth: 500,
            uploadURL: 'about:blank',
            theme: 'default'
        },
        buttons: {
            //��ť����
            //eq: {title: '����',cmd: 'bold'},
            bold: { title: "�Ӵ�", cmd: "bold" },
            pastetext: { title: "ճ���޸�ʽ", cmd: "bold" },
            pastefromword: { title: "ճ��word��ʽ", cmd: "bold" },
            selectAll: { title: "ȫѡ", cmd: "selectall" },
            blockquote: { title: "����" },
            find: { title: "����", cmd: "bold" },
            flash: { title: "����flash", cmd: "bold" },
            media: { title: "�����ý��", cmd: "bold" },
            table: { title: "������" },
            pagebreak: { title: "�����ҳ��" },
            face: { title: "�������", cmd: "bold" },
            code: { title: "����Դ��", cmd: "bold" },
            print: { title: "��ӡ", cmd: "print" },
            about: { title: "����", cmd: "bold" },
            fullscreen: { title: "ȫ��", cmd: "fullscreen" },
            source: { title: "HTML����", cmd: "source" },
            undo: { title: "����", cmd: "undo" },
            redo: { title: "ǰ��", cmd: "redo" },
            cut: { title: "����", cmd: "cut" },
            copy: { title: "����", cmd: "copy" },
            paste: { title: "ճ��", cmd: "paste" },
            hr: { title: "�������", cmd: "inserthorizontalrule" },
            link: { title: "��������", cmd: "createlink" },
            unlink: { title: "ɾ������", cmd: "unlink" },
            italic: { title: "б��", cmd: "italic" },
            underline: { title: "�»���", cmd: "underline" },
            strikethrough: { title: "ɾ����", cmd: "strikethrough" },
            unformat: { title: "�����ʽ", cmd: "removeformat" },
            subscript: { title: "�±�", cmd: "subscript" },
            superscript: { title: "�ϱ�", cmd: "superscript" },
            orderedlist: { title: "�����б�", cmd: "insertorderedlist" },
            unorderedlist: { title: "�����б�", cmd: "insertunorderedlist" },
            indent: { title: "��������", cmd: "indent" },
            outdent: { title: "��������", cmd: "outdent" },
            leftalign: { title: "�����", cmd: "justifyleft" },
            centeralign: { title: "���ж���", cmd: "justifycenter" },
            rightalign: { title: "�Ҷ���", cmd: "justifyright" },
            blockjustify: { title: "���˶���", cmd: "justifyfull" },
            font: { title: "����", cmd: "fontname", value: "΢���ź�" },
            fontsize: { title: "�ֺ�", cmd: "fontsize", value: "4" },
            style: { title: "�������", cmd: "formatblock", value: "" },
            fontcolor: { title: "ǰ����ɫ", cmd: "forecolor", value: "#ff6600" },
            backcolor: { title: "������ɫ", cmd: "hilitecolor", value: "#ff6600" },
            image: { title: "����ͼƬ", cmd: "insertimage", value: "" }
        },
        defaultEvent: {
            event: "click mouseover mouseout",
            click: function (e) {
                this.exec(e);
            },
            mouseover: function (e) {
                var opt = this.editor.opt;
                this.$btn.addClass(opt.cssname.mouseover);
            },
            mouseout: function (e) { },
            noRight: function (e) { },
            init: function (e) { },
            exec: function () {
                this.editor.restoreRange();
                //ִ������
                if ($.isFunction(this[this.cmd])) {
                    this[this.cmd](); //������ѵ�ǰcmdΪ���ķ�������ִ��
                } else {
                    this.editor.doc.execCommand(this.cmd, 0, this.value || null);
                }
                this.editor.focus();
                this.editor.refreshBtn();
                this.editor.hideDialog();
            },
            createDialog: function (v) {
                //�����Ի���
                var editor = this.editor,
				opt = editor.opt,
				$btn = this.$btn,
				_self = this;
                var defaults = {
                    body: "", //�Ի�������
                    closeBtn: opt.cssname.dialogCloseBtn,
                    okBtn: opt.cssname.dialogOkBtn,
                    ok: function () {
                        //���ok��ť��ִ�к���
                    },
                    setDialog: function ($dialog) {
                        //���öԻ���λ�ã�
                        var y = $btn.offset().top + $btn.outerHeight();
                        var x = $btn.offset().left;
                        $dialog.offset({
                            top: y,
                            left: x
                        });
                    }
                };
                var options = $.extend(defaults, v);
                //��ʼ���Ի���
                editor.$dialog.empty();
                //��������
                $body = $.type(options.body) == "string" ? $(options.body) : options.body;
                $dialog = $body.appendTo(editor.$dialog);
                $dialog.find("." + options.closeBtn).click(function () { _self.hideDialog(); });
                $dialog.find("." + options.okBtn).click(options.ok);
                //���öԻ���
                editor.$dialog.show();
                options.setDialog(editor.$dialog);
            },
            hideDialog: function () {
                this.editor.hideDialog();
            }
            //getEnable:function(){return false},
            //disable:function(e){alert('disable')}
        },
        plugin: function (name, v) {
            //�������޸Ĳ����
            $.TE.buttons[name] = $.extend($.TE.buttons[name], v);
        },
        config: function (name, value) {
            var _fn = arguments.callee;
            if (!_fn.conf) _fn.conf = {};

            if (value) {
                _fn.conf[name] = value;
                return true;
            } else {
                return name == 'default' ? $.TE.defaults : _fn.conf[name];
            }
        },
        systemPlugins: ['system', 'upload_interface'], //ϵͳ�Դ����
        basePath: function () {
            var jsFile = "ThinkEditor.js";
            var src = $("script[src$='" + jsFile + "']").attr("src");
            return src.substr(0, src.length - jsFile.length);
        }
    };

    $.fn.extend({
        //���ò��
        ThinkEditor: function (v) {
            //���ô���
            var conf = '',
				temp = '';

            conf = v ? $.extend($.TE.config(v.theme ? v.theme : 'default'), v) : $.TE.config('default');

            v = conf;
            //���ô������

            //����Ƥ��
            var skins = v.skins || $.TE.defaults.skins; //���Ƥ������
            var skinsDir = $.TE.basePath() + "skins/" + skins + "/",
			jsFile = "@" + skinsDir + "config.js",
			cssFile = "@" + skinsDir + "style.css";

            var _self = this;
            //���ز��
            if ($.defined(v.plugins)) {
                var myPlugins = $.type(v.plugins) == "string" ? [v.plugins] : v.plugins;
                var files = $.merge($.merge([], $.TE.systemPlugins), myPlugins);
            } else {
                var files = $.TE.systemPlugins;
            }
            $.each(files, function (i, v) {
                files[i] = v + ".js";
            })

            files.push(jsFile, cssFile);
            files.push("@" + skinsDir + "dialog/css/base.css");
            files.push("@" + skinsDir + "dialog/css/te_dialog.css");

            $.loadFile(files, function () {
                //����css����
                v.cssname = $.extend({}, TECSS, v.cssname);
                //�����༭��,�洢����
                $(_self).each(function (idx, elem) {
                    var data = $(elem).data("editorData");
                    if (!data) {
                        data = new ThinkEditor(elem, v);
                        $(elem).data("editorData", data);
                    }
                });

            });
        }

    });
    //�༭������
    function ThinkEditor(area, v) {

        //����������������ͻ
        var _fn = arguments.callee;
        this.guid = !_fn.guid ? _fn.guid = 1 : _fn.guid += 1;

        //���ɲ���
        var opt = this.opt = $.extend({}, $.TE.defaults, v);
        var _self = this;
        //�ṹ�����㣬���߲㣬����㣬��ť��,�ײ�,dialog��
        var $main = this.$main = $("<div></div>").addClass(opt.cssname.main),
			$toolbar_box = $('<div></div>').addClass(opt.cssname.toolbar_box).appendTo($main),
			$toolbar = this.$toolbar = $("<div></div>").addClass(opt.cssname.toolbar).appendTo($toolbar_box),
        /*$toolbar=this.$toolbar=$("<div></div>").addClass(opt.cssname.toolbar).appendTo($main),*/
			$group = $("<div></div>").addClass(opt.cssname.group).appendTo($toolbar),
			$bottom = this.$bottom = $("<div></div>").addClass(opt.cssname.bottom),
			$dialog = this.$dialog = $("<div></div>").addClass(opt.cssname.dialog),
			$area = $(area).hide(),
			$frame = $('<iframe frameborder="0"></iframe>');

        opt.noRights = opt.noRights || "";
        var noRights = opt.noRights.split(",");
        //�����ṹ
        $main.insertBefore($area)
		.append($area);
        //����frame
        $frame.appendTo($main);
        //����bottom
        if (opt.resizeType != 0) {
            //�϶��ı�༭���߶�
            $("<div></div>").addClass(opt.cssname.resizeCenter).mousedown(function (e) {
                var y = e.pageY,
				x = e.pageX,
				height = _self.$main.height(),
				width = _self.$main.width();
                $(document).add(_self.doc).mousemove(function (e) {
                    var mh = e.pageY - y;
                    _self.resize(width, height + mh);
                });
                $(document).add(_self.doc).mouseup(function (e) {
                    $(document).add(_self.doc).unbind("mousemove");
                    $(document).add(_self.doc).unbind("mousemup");
                });
            }).appendTo($bottom);
        }
        if (opt.resizeType == 2) {
            //�϶��ı�༭���߶ȺͿ��
            $("<div></div>").addClass(opt.cssname.resizeLeft).mousedown(function (e) {
                var y = e.pageY,
				x = e.pageX,
				height = _self.$main.height(),
				width = _self.$main.width();
                $(document).add(_self.doc).mousemove(function (e) {
                    var mh = e.pageY - y,
					mw = e.pageX - x;
                    _self.resize(width + mw, height + mh);
                });
                $(document).add(_self.doc).mouseup(function (e) {
                    $(document).add(_self.doc).unbind("mousemove");
                    $(document).add(_self.doc).unbind("mousemup");
                });
            }).appendTo($bottom);
        }
        $bottom.appendTo($main);
        $dialog.appendTo($main);
        //ѭ����ť����
        //TODO Ĭ�ϲ�������
        $.each(opt.controls.split(","), function (idx, bname) {
            var _fn = arguments.callee;
            if (_fn.count == undefined) {
                _fn.count = 0;
            }

            //�������
            if (bname == "|") {
                //�趨�����
                if (_fn.count) {
                    $toolbar.find('.' + opt.cssname.group + ':last').css('width', (opt.cssname.btnWidth * _fn.count + opt.cssname.lineWidth) + 'px');
                    _fn.count = 0;
                }
                //��������
                $group = $("<div></div>").addClass(opt.cssname.group).appendTo($toolbar);
                $("<div>&nbsp;</div>").addClass(opt.cssname.line).appendTo($group);

            } else {
                //����ͳ����
                _fn.count += 1;
                //��ȡ��ť����
                var btn = $.extend({}, $.TE.defaultEvent, $.TE.buttons[bname]);
                //�����Ȩ��
                var noRightCss = "", noRightTitle = "";
                if ($.inArray(bname, noRights) != -1) {
                    noRightCss = " " + opt.cssname.noRight;
                    noRightTitle = "(��Ȩ��)";
                }
                $btn = $("<div></div>").addClass(opt.cssname.btn + " " + opt.cssname.btnpre + bname + noRightCss)
				.data("bname", bname)
				.attr("title", btn.title + noRightTitle)
				.appendTo($group)
				.bind(btn.event, function (e) {
				    //�����ô���
				    if ($(this).is("." + opt.cssname.disable)) {
				        if ($.isFunction(btn.disable)) btn.disable.call(btn, e);
				        return false;
				    }
				    //�ж�Ȩ�޺��Ƿ����
				    if ($(this).is("." + opt.cssname.noRight)) {
				        //���ʱ������Ȩ��˵��
				        btn['noRight'].call(btn, e);
				        return false;
				    }
				    if ($.isFunction(btn[e.type])) {
				        //�����¼�
				        btn[e.type].call(btn, e);
				        //TODO ˢ�°�ť
				    }
				});
                if ($.isFunction(btn.init)) btn.init.call(btn); //��ʼ��
                if (ie) $btn.attr("unselectable", "on");
                btn.editor = _self;
                btn.$btn = $btn;
            }
        });
        //���ú���
        this.core = new editorCore($frame, $area);
        this.doc = this.core.doc;
        this.$frame = this.core.$frame;
        this.$area = this.core.$area;
        this.restoreRange = this.core.restoreRange;
        this.selectedHTML = function () { return this.core.selectedHTML(); }
        this.selectedText = function () { return this.core.selectedText(); }
        this.pasteHTML = function (v) { this.core.pasteHTML(v); }
        this.sourceMode = this.core.sourceMode;
        this.focus = this.core.focus;
        //��ر仯
        $(this.core.doc).click(function () {
            //���ضԻ���
            _self.hideDialog();
        }).bind("keyup mouseup", function () {
            _self.refreshBtn();
        })
        this.refreshBtn();
        //������С
        this.resize(opt.width, opt.height);

        //��ȡDOM�㼶
        this.core.focus();
    }
    //end ThinkEditor
    ThinkEditor.prototype.resize = function (w, h) {
        //��С�߶ȺͿ��
        var opt = this.opt,
		h = h < opt.minHeight ? opt.minHeight : h,
		w = w < opt.minWidth ? opt.minWidth : w;
        this.$main.width(w).height(h);
        var height = h - (this.$toolbar.parent().outerHeight() + this.$bottom.height());
        this.$frame.height(height).width("100%");
        this.$area.height(height).width("100%");
    };
    //���ضԻ���
    ThinkEditor.prototype.hideDialog = function () {
        var opt = this.opt;
        $("." + opt.cssname.dialog).hide();
    };
    //ˢ�°�ť
    ThinkEditor.prototype.refreshBtn = function () {
        var sourceMode = this.sourceMode(); // ���״̬��
        var opt = this.opt;
        if (!iOS && $.browser.webkit && !this.focused) {
            this.$frame[0].contentWindow.focus();
            window.focus();
            this.focused = true;
        }
        var queryObj = this.doc;
        if (ie) queryObj = this.core.getRange();
        //ѭ����ť
        //TODO undo,redo���ж�
        this.$toolbar.find("." + opt.cssname.btn + ":not(." + opt.cssname.noRight + ")").each(function () {
            var enabled = true,
			btnName = $(this).data("bname"),
			btn = $.extend({}, $.TE.defaultEvent, $.TE.buttons[btnName]),
			command = btn.cmd;
            if (sourceMode && btnName != "source") {
                enabled = false;
            } else if ($.isFunction(btn.getEnable)) {
                enabled = btn.getEnable.call(btn);
            } else if ($.isFunction(btn[command])) {
                enabled = true; //�������Ϊ�Զ������Ĭ��Ϊ����
            } else {
                if (!ie || btn.cmd != "inserthtml") {
                    try {
                        enabled = queryObj.queryCommandEnabled(command);
                        $.debug(enabled.toString(), "����:" + command);
                    }
                    catch (err) {
                        enabled = false;
                    }
                }

                //�жϸù����Ƿ���ʵ�� @TODO ���뽺��
                if ($.TE.buttons[btnName]) enabled = true;
            }
            if (enabled) {
                $(this).removeClass(opt.cssname.disable);
            } else {
                $(this).addClass(opt.cssname.disable);
            }
        });
    };
    //core code start
    function editorCore($frame, $area, v) {
        //TODO ������Ϊȫ�ֵġ�
        var defaults = {
            docType: '<!DOCTYPE HTML>',
            docCss: "",
            bodyStyle: "margin:4px; font:10pt Arial,Verdana; cursor:text",
            focusExt: function (editor) {
                //�����༭����ý���ʱִ�У�����ˢ�°�ť
            },
            //textarea���ݸ��µ�iframe�Ĵ�����
            updateFrame: function (code) {
                //��תflashΪռλ��
                code = code.replace(/(<embed[^>]*?type="application\/x-shockwave-flash" [^>]*?>)/ig, function ($1) {
                    var ret = '<img class="_flash_position" src="' + $.TE.basePath() + 'skins/default/img/spacer.gif" style="',
						_width = $1.match(/width="(\d+)"/),
						_height = $1.match(/height="(\d+)"/),
						_src = $1.match(/src="([^"]+)"/),
						_wmode = $1.match(/wmode="(\w+)"/),
						_data = '';

                    _width = _width && _width[1] ? parseInt(_width[1]) : 0;
                    _height = _height && _height[1] ? parseInt(_height[1]) : 0;
                    _src = _src && _src[1] ? _src[1] : '';
                    _wmode = _wmode && _wmode[1] ? true : false;
                    _data = "{'src':'" + _src + "','width':'" + _width + "','height':'" + _height + "','wmode':" + (_wmode) + "}";


                    if (_width) ret += 'width:' + _width + 'px;';
                    if (_height) ret += 'height:' + _height + 'px;';

                    ret += 'border:1px solid #DDD; display:inline-block;text-align:center;line-height:' + _height + 'px;" ';
                    ret += '_data="' + _data + '"';
                    ret += ' alt="flashռλ��" />';

                    return ret;
                });

                return code;
            },
            //iframe���µ�text�ģ� TODO ȥ��
            updateTextArea: function (html) {
                //��תռλ��Ϊflash
                html = html.replace(/(<img[^>]*?class=(?:"|)_flash_position(?:"|)[^>]*?>)/ig, function ($1) {
                    var ret = '',
						data = $1.match(/_data="([^"]*)"/);

                    if (data && data[1]) {
                        data = eval('(' + data + ')');
                    }

                    ret += '<embed type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" ';
                    ret += 'src="' + data.src + '" ';
                    ret += 'width="' + data.width + '" ';
                    ret += 'height="' + data.height + '" ';
                    if (data.wmode) ret += 'wmode="transparent" ';
                    ret += '/>';

                    return ret;
                });

                return html;
            }
        };
        options = $.extend({}, defaults, v);
        //�洢����
        this.opt = options;
        this.$frame = $frame;
        this.$area = $area;
        var contentWindow = $frame[0].contentWindow,
		doc = this.doc = contentWindow.document,
		$doc = $(doc);

        var _self = this;

        //��ʼ��
        doc.open();
        doc.write(
			options.docType +
			'<html>' +
			((options.docCss === '') ? '' : '<head><link rel="stylesheet" type="text/css" href="' + options.docCss + '" /></head>') +
			'<body style="' + options.bodyStyle + '"></body></html>'
			);
        doc.close();
        //����frame�༭ģʽ
        try {
            if (ie) {
                doc.body.contentEditable = true;
            }
            else {
                doc.designMode = "on";
            }
        } catch (err) {
            $.debug(err, "�����༭ģʽ����");
        }

        //ͳһ IE FF �ȵ� execCommand ��Ϊ
        try {
            this.e.execCommand("styleWithCSS", 0, 0)
        }
        catch (e) {
            try {
                this.e.execCommand("useCSS", 0, 1);
            } catch (e) { }
        }

        //����
        if (ie)
            $doc.click(function () {
                _self.focus();
            });
        this.updateFrame(); //��������

        if (ie) {
            $doc.bind("beforedeactivate beforeactivate selectionchange keypress", function (e) {
                if (e.type == "beforedeactivate")
                    _self.inactive = true;

                else if (e.type == "beforeactivate") {
                    if (!_self.inactive && _self.range && _self.range.length > 1)
                        _self.range.shift();
                    delete _self.inactive;
                }

                else if (!_self.inactive) {
                    if (!_self.range)
                        _self.range = [];
                    _self.range.unshift(_self.getRange());

                    while (_self.range.length > 2)
                        _self.range.pop();
                }

            });

            // Restore the text range when the iframe gains focus
            $frame.focus(function () {
                _self.restoreRange();
            });
        }

        ($.browser.mozilla ? $doc : $(contentWindow)).blur(function () {
            _self.updateTextArea(true);
        });
        this.$area.blur(function () {
            // Update the iframe when the textarea loses focus
            _self.updateFrame(true);
        });

        /*
        * //�Զ����p��ǩ
        * $doc.keydown(function(e){
        * 	if(e.keyCode == 13){
        * 		//_self.pasteHTML('<p>&nbsp;</p>');
        * 		//this.execCommand( 'formatblock', false, '<p>' );
        * 	}
        * });
        */

    }
    //�Ƿ�ΪԴ��ģʽ
    editorCore.prototype.sourceMode = function () {
        return this.$area.is(":visible");
    };
    //�༭����ý���
    editorCore.prototype.focus = function () {
        var opt = this.opt;
        if (this.sourceMode()) {
            this.$area.focus();
        }
        else {
            this.$frame[0].contentWindow.focus();
        }
        if ($.isFunction(opt.focusExt)) opt.focusExt(this);
    };
    //textarea���ݸ��µ�iframe
    editorCore.prototype.updateFrame = function (checkForChange) {
        var code = this.$area.val(),
		options = this.opt,
		updateFrameCallback = options.updateFrame,
		$body = $(this.doc.body);
        //�ж��Ƿ��Ѿ��޸�
        if (updateFrameCallback) {
            var sum = checksum(code);
            if (checkForChange && this.areaChecksum == sum)
                return;
            this.areaChecksum = sum;
        }

        //�ص���������
        var html = updateFrameCallback ? updateFrameCallback(code) : code;

        // ��ֹscript��ǩ

        html = html.replace(/<(?=\/?script)/ig, "&lt;");

        // TODO���ж��Ƿ�������
        if (options.updateTextArea)
            this.frameChecksum = checksum(html);

        if (html != $body.html()) {
            $body.html(html);
        }
    };
    editorCore.prototype.getRange = function () {
        if (ie) return this.getSelection().createRange();
        return this.getSelection().getRangeAt(0);
    };

    editorCore.prototype.getSelection = function () {
        if (ie) return this.doc.selection;
        return this.$frame[0].contentWindow.getSelection();
    };
    editorCore.prototype.restoreRange = function () {
        if (ie && this.range)
            this.range[0].select();
    };

    editorCore.prototype.selectedHTML = function () {
        this.restoreRange();
        var range = this.getRange();
        if (ie)
            return range.htmlText;
        var layer = $("<layer>")[0];
        layer.appendChild(range.cloneContents());
        var html = layer.innerHTML;
        layer = null;
        return html;
    };


    editorCore.prototype.selectedText = function () {
        this.restoreRange();
        if (ie) return this.getRange().text;
        return this.getSelection().toString();
    };

    editorCore.prototype.pasteHTML = function (value) {
        this.restoreRange();
        if (ie) {
            this.getRange().pasteHTML(value);
        } else {
            this.doc.execCommand("inserthtml", 0, value || null);
        }
        //��ý���
        this.$frame[0].contentWindow.focus();
    }

    editorCore.prototype.updateTextArea = function (checkForChange) {
        var html = $(this.doc.body).html(),
		options = this.opt,
		updateTextAreaCallback = options.updateTextArea,
		$area = this.$area;


        if (updateTextAreaCallback) {
            var sum = checksum(html);
            if (checkForChange && this.frameChecksum == sum)
                return;
            this.frameChecksum = sum;
        }


        var code = updateTextAreaCallback ? updateTextAreaCallback(html) : html;
        // TODO �ж��Ƿ��б�Ҫ
        if (options.updateFrame)
            this.areaChecksum = checksum(code);
        if (code != $area.val()) {
            $area.val(code);
        }

    };
    function checksum(text) {
        var a = 1, b = 0;
        for (var index = 0; index < text.length; ++index) {
            a = (a + text.charCodeAt(index)) % 65521;
            b = (b + a) % 65521;
        }
        return (b << 16) | a;
    }
    $.extend({
        teExt: {
        //��չ����
    },
    debug: function (msg, group) {
        //�ж��Ƿ���console����
        if ($.TE.debug && window.console !== undefined) {
            //���鿪ʼ
            if (group) console.group(group);
            if ($.type(msg) == "string") {
                //�Ƿ�Ϊִ�����⺯��,��˫ð�Ÿ���
                if (msg.indexOf("::") != -1) {
                    var arr = msg.split("::");
                    eval("console." + arr[0] + "('" + arr[1] + "')");
                } else {
                    console.debug(msg);
                }
            } else {
                if ($(msg).html() == null) {
                    console.dir(msg); //������������
                } else {
                    console.dirxml($(msg)[0]); //���dom����
                }

            }
            //��¼trace��Ϣ
            if ($.TE.debug == 2) {
                console.group("trace ��Ϣ:");
                console.trace();
                console.groupEnd();
            }
            //�������
            if (group) console.groupEnd();
        }
    },
    //end debug
    defined: function (variable) {
        return $.type(variable) == "undefined" ? false : true;
    },
    isTag: function (tn) {
        if (!tn) return false;
        return $(this)[0].tagName.toLowerCase() == tn ? true : false;
    },
    //end istag
    include: function (file) {
        if (!$.defined($.TE.loadUrl)) $.TE.loadUrl = {};
        //����Ƥ��·���Ͳ��·����
        var basePath = $.TE.basePath(),
			skinsDir = basePath + "skins/",
			pluginDir = basePath + "plugins/";
        var files = $.type(file) == "string" ? [file] : file;
        for (var i = 0; i < files.length; i++) {
            var loadurl = name = $.trim(files[i]);
            //�ж��Ƿ��Ѿ����ع�
            if ($.TE.loadUrl[loadurl]) {
                continue;
            }
            //�ж��Ƿ���@
            var at = false;
            if (name.indexOf("@") != -1) {
                at = true;
                name = name.substr(1);
            }
            var att = name.split('.');
            var ext = att[att.length - 1].toLowerCase();
            if (ext == "css") {
                //����css
                var filepath = at ? name : skinsDir + name;
                var newNode = document.createElement("link");
                newNode.setAttribute('type', 'text/css');
                newNode.setAttribute('rel', 'stylesheet');
                newNode.setAttribute('href', filepath);
                $.TE.loadUrl[loadurl] = 1;
            } else {
                var filepath = at ? name : pluginDir + name;
                //$("<scri"+"pt>"+"</scr"+"ipt>").attr({src:filepath,type:'text/javascript'}).appendTo('head');
                var newNode = document.createElement("script");
                newNode.type = "text/javascript";
                newNode.src = filepath;
                newNode.id = loadurl; //ʵ����������
                newNode.onload = function () {
                    $.TE.loadUrl[this.id] = 1;
                };
                newNode.onreadystatechange = function () {
                    //���ie
                    if ((newNode.readyState == 'loaded' || newNode.readyState == 'complete')) {
                        $.TE.loadUrl[this.id] = 1;
                    }
                };
            }
            $("head")[0].appendChild(newNode);
        }
    },
    //end include
    loadedFile: function (file) {
        //�ж��Ƿ����
        if (!$.defined($.TE.loadUrl)) return false;
        var files = $.type(file) == "string" ? [file] : file,
			result = true;
        $.each(files, function (i, name) {
            if (!$.TE.loadUrl[name]) result = false;
			//alert(name+':'+result);
        });
		
        return result;		
    },
    //end loaded

    loadFile: function (file, fun) {
        //�����ļ���������Ϻ�ִ��fun������
        $.include(file);
		
        var time = 0;
        var check = function () {
			//alert($.loadedFile(file));
            if ($.loadedFile(file)) {
                if ($.isFunction(fun)) fun();
            } else {
				//alert(time);
                if (time >= $.TE.timeOut) {
                    // TODO ϸ����Щ�ļ�����ʧ�ܡ�
                    $.debug(file, "�ļ�����ʧ��");
                } else {
					//alert('time:'+time);
                    setTimeout(check, 50);
                    time += 50;
                }
            }
        };
        check();
    }
    //end loadFile
});

})(jQuery);

jQuery.TE.config( 'mini', {
	'controls' : 'font,fontsize,fontcolor,backcolor,bold,italic,underline,unformat,leftalign,centeralign,rightalign,orderedlist,unorderedlist',
	'width':498,
	'height':400,
	'resizeType':1
} );