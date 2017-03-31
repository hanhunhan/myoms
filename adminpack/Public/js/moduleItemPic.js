function updateProgress(file,orderNum) {
    $('.progress-box'+orderNum+' .progress-bar > div').css('width', parseInt(file.percentUploaded) + '%');
    $('.progress-box'+orderNum+' .progress-num > b').html(SWFUpload.speed.formatPercent(file.percentUploaded));
}

function initProgress(orderNum) {
    var pg = $(".progress-box"+orderNum);
    pg.show();
    pg.find('.progress-bar > div').css('width', '0%');
    pg.find('.progress-num > b').html('0%');
}

//��ͼ�ɹ�ʱ��������
function singleSuccessAction(fileInfo,orderNum){
    setHeadImage(fileInfo,orderNum);
    // ����ϴ������
    $(".logoupload[tag="+orderNum+"]").find('.new-progress-box').hide();
}


//��ͷͼ����
function setHeadImage(fileInfo,orderNum){
    var up_path = fileInfo.path;
    var logobox = $('.logoupload[tag='+orderNum+']').prev();
    var img  = logobox.find('.p-pic img');
    if(img.length>0){
        logobox.find('.p-pic img').attr('src',up_path);
    }else{
        logobox.find('.p-pic').append('<img src="'+up_path+'" width="200" alt="" />');
    }
}


function initImageListFn() {
    $('.delete-pic').each(function(){
        $(this).unbind('click').click(function(){
            $(this).next().find('img').remove();
        });
    });
}

var swfUpload;
var settings;

$(document).ready(function() {
    swfUpload = {};
    settings = {
        flash_url : "./Public/swfupload/SWFUpload/diysubject_swfupload/swfupload.swf",
        flash9_url : "./Public/swfupload/SWFUpload/diysubject_swfupload/swfupload_fp9.swf",
        upload_url: "index.php?s=/HomeLayout/ajaxUploadFile",// �����ϴ��ĵ�ַ
        file_size_limit : "2 MB",// �ļ���С����
        file_types : "*.jpg;*.gif;*.png;*.jpeg;",// �����ļ�����
        file_types_description : "Image Files",// ˵�����Լ�����
        file_upload_limit : 100,
        file_queue_limit : 0,
        custom_settings : {orderNum:""},
        debug: false,
// Button settings
        button_image_url: "./Public/swfupload/SWFUpload/images/upload-btn.png",
        button_width: "143",
        button_height: "45 ",
        button_placeholder_id: 'uploadBtnHolder',
        button_window_mode : SWFUpload.WINDOW_MODE.TRANSPARENT,
        button_cursor : SWFUpload.CURSOR.HAND,
        button_action: SWFUpload.BUTTON_ACTION.SELECT_FILE,

        moving_average_history_size: 40,

// The event handler functions are defined in handlers.js
        swfupload_preload_handler : preLoad,
        file_queued_handler : fileQueued,
        file_dialog_complete_handler: fileDialogComplete,
        upload_start_handler : function (file) {
            initProgress(this.customSettings.orderNum);
            updateProgress(file,this.customSettings.orderNum);
        },
        upload_progress_handler : function(file, bytesComplete, bytesTotal) {
            updateProgress(file,this.customSettings.orderNum);
        },
        upload_error_handler : function(file, errorCode, message) {
            alert('�ϴ������˴���');
        },
        file_queue_error_handler : function(file, errorCode, message) {
            if(errorCode == -110) {
                alert('��ѡ����ļ�̫���ˡ�');
            }
        }
    };

    $(".logoupload").each(function(k,v){
        var swfSettings = jQuery.extend(true, {}, settings);
        var index = k;
        swfUpload['swf_instance_'+index] = new SWFUpload(swfSettings);
//��ͼʵ��������
        swfSettings.button_placeholder_id = $(this).find(".btnbox a").attr('id');
        swfSettings.custom_settings.orderNum = index;
        swfSettings.upload_success_handler = function(file, data, response){
            // �ϴ��ɹ�������
            var fileInfo = eval("(" + data + ")");
            singleSuccessAction(fileInfo,swfSettings.custom_settings.orderNum);
        }
        $(this).attr('tag',swfSettings.custom_settings.orderNum);
        swfUpload[index] = new SWFUpload(swfSettings);
        initImageListFn();
    });
});
