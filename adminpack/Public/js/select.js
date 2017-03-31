(function(JQ){
	var defaults={
		selectClassName:'.select',
		selectList:'.selectList',
		hiddeninput:'.hiddeninput'
	};

	var cache = [];
	JQ.fn.selectBox=function(options){
		options = JQ.extend({},defaults,options);
		this.each(function(){
			var self = JQ(this);
			var _select = self.find(options['selectClassName']);
			var list = self.find(options['selectList']);
			var hiddeninput = self.find(options['hiddeninput']);

			var timer = null;
			cache.push(list);
			var func = function(){
				list.hide();
				JQ(document).unbind('click', func);
			};
			_select.bind('click', function(event){
				JQ.each(cache, function(i,n){
					n.hide();
				});
				list.show();
				JQ(document).bind('click', func);
				event.stopPropagation();
			});
			list.find('li').live('click', function(event){
				_select.val(JQ(this).text());
				hiddeninput.val(JQ(this).attr('id'));

				if(typeof(options_click) == "function"){
					options_click(JQ(this).attr('name'),JQ(this).attr('id'));
				}

				list.hide();
				event.stopPropagation();
			})/*.bind('mouseover mouseout',function(event){
				if(event.type == 'mouseover'){
					list.show();
				}else{
					list.hide()
				}
			})*/
		})
	}
})(jQuery);

// È«Ñ¡²å¼þ
(function($) {         
    $.fn.checkall = function(options) {  
        var defaults = {chname:"checkname[]"};  
        var options = $.extend(defaults, options);  
        this.click(function(){  
            if(this.checked){  
                $("input[name='"+options.chname+"']").attr("checked",true);  
            }else{  
                $("input[name='"+options.chname+"']").attr("checked",false);  
            }  
        });  
    }  
})(jQuery); 