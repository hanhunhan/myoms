function te_upload_interface() {
    //��ʼ������
    var _args = arguments,
    _fn   = _args.callee,
    _data = '';

    if( _args[0] == 'reg' ) {
        //ע��ص�
        _data = _args[1];
        _fn.curr = _data['callid'];
        _fn.data = _data;
        jQuery('#temaxsize').val(_data['maxsize']);
    } else if( _args[0] == 'get' ) {
        //��ȡ����
        return _fn.data || false;

    } else if( _args[0] == 'call' ) {
        //����ص���ʵ����һ��
        if( _args[1] != _fn.curr ) {
            alert( '�ϴ������벻Ҫͬʱ�򿪶���ϴ�����' );
            return false;
        }
        //�ϴ��ɹ�
        if( _args[2] == 'success' ) {
            _fn.data['callback']( _args[3] );
        }
        //�ϴ�ʧ��
        else if( _args[2] == 'failure' ) {
            alert( '[�ϴ�ʧ��]\n������Ϣ:'+_args[3] );
        }
        //�ļ����ͼ�����
        else if( _args[2] == 'filetype' ) {
            alert( '[�ϴ�ʧ��]\n������Ϣ�����ϴ����ļ���������' );
        }
        //����״̬�ı�
        else if( _args[2] == 'change' ) {
            // TODO ��ϸ�µĻص�ʵ��,�˴�����true�Զ��ύ
            return true;
        }
    }
}
//�û�ѡ���ļ�ʱ
function checkTypes(id){
    //У���ļ�����
    var filename  = document.getElementById( 'teupload' ).value,
    filetype  = document.getElementById( 'tefiletype' ).value.split( ',' );

    currtype  = filename.split( '.' ).pop(),
    checktype = false;

    if( filetype[0] == '*' ) {
        checktype = true;
    } else {
        for(var i=0; i<filetype.length; i++) {
            if( currtype ==  filetype[i] ) {
                checktype = true;
                break;
            }
        }
    }
    if( !checktype ) {
        alert( '[�ϴ�ʧ��]\n������Ϣ�����ϴ����ļ���������' );
        return false;
    } else {
        //У��ͨ�����ύ
        jQuery('#'+id).submit()
    }
}