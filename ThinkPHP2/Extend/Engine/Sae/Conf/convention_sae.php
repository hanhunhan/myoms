<?php
//sae�µĹ̶�����,�������ý��Ḳ����Ŀ���á�
return array(
        'DB_TYPE'=> 'mysql',     // ���ݿ�����
	'DB_HOST'=> SAE_MYSQL_HOST_M.','.SAE_MYSQL_HOST_S, // ��������ַ
	'DB_NAME'=> SAE_MYSQL_DB,        // ���ݿ���
	'DB_USER'=> SAE_MYSQL_USER,    // �û���
	'DB_PWD'=> SAE_MYSQL_PASS,         // ����
	'DB_PORT'=> SAE_MYSQL_PORT,        // �˿�
	'DB_RW_SEPARATE'=>true,
        'DB_DEPLOY_TYPE'=> 1, // ���ݿⲿ��ʽ:0 ����ʽ(��һ������),1 �ֲ�ʽ(���ӷ�����)
        'SAE_SPECIALIZED_FILES'=>array(
            //SAEϵͳר���ļ���
            'UploadFile.class.php'=>SAE_PATH.'Lib/Extend/Library/ORG/Net/UploadFile_sae.class.php',
            'Image.class.php'=>SAE_PATH.'Lib/Extend/Library/ORG/Util/Image_sae.class.php'
         )
        );
