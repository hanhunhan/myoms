<?php
class UploadFile
{
    /**
    * Description: ??????
    * @Copyright: HOUSE365 (c) 2008
    * Author: chiwm
    * Create: 2008-6-11
    * Amendment Record:

    * sampel
    include_once($strBaseSite."common/uploadFile.php");
    $uf = new UploadFile("upfile");//upfile???????file??name????
    $rtnMSG=$uf->upload();
    var_dump($uf->getSaveFileURL());//??????url???
    var_dump($rtnMSG);//$rtnMSG="success" ?????????

    <input name="upfile" type="file" size="20">
    */
    var $strInputName;//?????file?????name????
    var $intMaxSize=204800;//200k //?????????????????§³(??¦Ë??B)
    var $strFileType="image";//???????????????????|??????image???????????????office??????????
    var $strSaveDir;//??????¡¤?????????????????/database/webroot/upload/+?????+/+?????+/+?????;???»Ç/database/webroot/upload/2008/06/11/??
    var $strUploadType="ftp";//???????????????ftp???????????????????????????????????ftp??
    var $isShowAsChinese=false;//?????????????????????

    var $aryFileType;
    var $ftpConn;
    var $strExtention;//???????????
    var $strSavaFileName;//??????????????????
    var $strYMDDir;
    var $strRelativeSaveDir;//??????¡¤???????web??ftp????????
    var $strSaveFileURL="";//?????????URL???

    //??ftp?????????????
    var $strServerSite;//????ftp????????????img1.house365.com?§Ö??????????
    var $strServer="172.17.1.184";//ftp??????????
    var $intPort=21;//ftp???
    var $intTimeout=90;//ftp???
    var $strUsername="imguser";//???ftp?????
    var $strPassword="doucaretrx";//???ftp????
    var $hasSetedServer=false;//??????????????????

    //???????????????????
    var $needResizeImage=false;//???????????
    var $needForceResizeImage=false;//??????????????by zain
    var $intResizeImageSize=150;//????????
    var $intResizeWidth=0;//????????
    var $intResizeHeight=0;//????????
    var $needResizeCut=false;//?????????????????????
    var $needResizeReality=true;//????????????????ÖÎ???¦²?????????????????
    var $strResizeImagePrefixion;//???????
    var $strResizeImageURL;//????????

    //?????????????
    var $needWatermark=false;//????????
    var $intWatermarkType=1;//?????????(1?????,2???)
    var $strWatermarkPosition="rb";//??¦Ë?? lt?????lb???¡ê?rt?????rb???¡ê?ct?????cb???¡ê?lc???§µ?rc???§µ?cc?§Þ?sj???
    var $strWatermarkString="HOUSE365.COM"; //???????
    var $strWatermarkImage="";//????
    var $intWatermarkSize=24;//???????????§³
    var $strWatermarkFont="";//??????????


    var $strImageType="jpg|jpeg|png|pjpeg|gif|bmp|dib|x-png|tif|tiff|wmf|dwg|dxf|svg|svgz|emf|emz";
    var $strOfficeType="doc|dot|rtf|txt|pwi|psw|pwd|wps|wtf|xls|xlt|csv|xlw|wk4|wk3|wk1|wd1|wks|wq1|dbf|prn|dif|slk|xla|mdb|adp|mda|mdw|mde|ade|dbf|tab|asc|ppt|pot|pps|ppa|rtf|mpp|mpt|vsd|vtx|vss|vsx|vst|vtx|vsw|pdf";
    var $strRarType="rar|zip";

	//2011-9-1 ?????
	var $intImageWidth;//?????????
	var $intImageHeight;//?????????
	var $needImageCut;//???????

    function UploadFile($strInputName="")
    {
        $this->setWatermarkImage();
        $this->setWatermarkFont();
        $this->setServerSite();
        $this->strInputName=$strInputName;
        $this->strYMDDir=date("Y")."/".date("m")."/".date("d")."/";
    }

    //????????file?????name????
    function getInputName()
    {
        return $this->strInputName;
    }

    //?????????file?????name????
    function setInputName($strInputName)
    {
        $this->strInputName=$strInputName;
    }

    //????????????????????§³(??¦Ë??KB)
    function getMaxSize()
    {
        return $this->intMaxSize/1024;
    }

    //?????????????????????§³(??¦Ë??KB)
    function setMaxSize($intMaxSize=200)
    {
        $this->intMaxSize=round($intMaxSize)*1024;
    }

    //???????????????????
    function getFileType()
    {
        return $this->strFileType;
    }

    //????????????????????
    function setFileType($strFileType="image")
    {
        $this->strFileType=$strFileType;
    }

/*-------------------------------------------------------------------------------------------------------*/
/* Fisher 2011-9-1 ????????????????????*/
/*-------------------------------------------------------------------------------------------------------*/
	//?????????
    function setImageWidth($intImageWidth=0)
    {
        $this->intImageWidth=$intImageWidth;
    }

    //?????????
    function setImageHeight($intImageHeight=0)
    {
        $this->intImageHeight=$intImageHeight;
    }

	//??????????????????
    function setImageCut($needImageCut=false)
    {
        $this->needImageCut=$needImageCut;
    }
/*-------------------------------------------------------------------------------------------------------*/

    //???????????
    function getExtention()
    {
        if($this->strExtention=="")
        {
            $strFileName=$_FILES[$this->strInputName][name];
            $this->strExtention = strtolower(preg_replace('/.*\.(.*[^\.].*)*/iU','\\1',$strFileName));
        }
        return $this->strExtention;
    }

    //????????????
    function setExtention($strExtention="")
    {
        if($strExtention=="") $strExtention=$_FILES[$this->strInputName][name];
        $this->strExtention = strtolower(preg_replace('/.*\.(.*[^\.].*)*/iU','\\1',$strExtention));
    }

    //???????????§³
    function getFileSize()
    {
        return $_FILES[$this->strInputName][size];
    }

    //???????????
    function getFileName()
    {
        return $_FILES[$this->strInputName][name];
    }

    //??¡Â?????????????????
    function getSaveFileName()
    {
        return $this->strSavaFileName;
    }

    //???????????
    function getSaveDir()
    {
        if($this->strUploadType=="ftp")
        {
            $this->ftpConn = @ftp_connect($this->strServer);
            if(!$this->ftpConn)
            {
                return "connError";
            }
            if(!@ftp_login($this->ftpConn,$this->strUsername,$this->strPassword))
            {
                ftp_quit($this->ftpConn); //???ftp????
                return "loginError";
            }
            if(!@ftp_chdir($this->ftpConn,$this->strSaveDir))
            {
                $aryDirs=explode("/",substr($this->strSaveDir,1,strlen($this->strSaveDir)-2));
                $strDir="/";
                foreach($aryDirs as $value)
                {
                    $strDir.=$value."/";
                    if(!@ftp_chdir($this->ftpConn,$strDir))
                    {
                        if(!@ftp_mkdir($this->ftpConn,$strDir))
                        {
                            ftp_quit($this->ftpConn); //???ftp????
                            return "mkdirError";
                        }
                    }
                }
            }
        }
        else
        {
            if(!file_exists($this->strSaveDir))
            {
                $aryDirs=explode("/",substr($this->strSaveDir,1,strlen($this->strSaveDir)-2));
                $strDir="/";
                foreach($aryDirs as $value)
                {
                    $strDir.=$value."/";
                    if($strDir!="/database/" && !@file_exists($strDir))
                    {
                        if(!@mkdir($strDir,0777))
                        {
                            return "mkdirError";
                        }
                    }
                }
            }
        }
        return $this->strSaveDir;
    }

    //????????????
    function setSaveDir($strSaveDir="",$flag=0)
    {
        $strPath=dirname(__FILE__);
        $strPath=substr($strPath,0,strrpos($strPath,"/"));

        if($strSaveDir=="")
        {
            if($this->strUploadType=="ftp")
            {
                $this->strRelativeSaveDir="/upload/".$this->strYMDDir;
                $this->strSaveDir="/img".$this->strServerSite."/upload/".$this->strYMDDir;
            }
            else
            {
                $this->strRelativeSaveDir="/upload/".$this->strYMDDir;
                $this->strSaveDir=$strPath."/upload/".$this->strYMDDir;
            }
        }
        else
        {
            if(substr($strSaveDir,0,1)!="/")
                $strSaveDir="/".$strSaveDir;
            if(substr($strSaveDir,-1,1)!="/")
                $strSaveDir.="/";
            if($this->strUploadType=="ftp")
            {
				if($flag==0){
                $this->strRelativeSaveDir=$strSaveDir.$this->strYMDDir;
                $this->strSaveDir="/img".$this->strServerSite.$strSaveDir.$this->strYMDDir;
				}else{
                $this->strRelativeSaveDir=$strSaveDir.$this->strYMDDir;
                $this->strSaveDir=$strSaveDir.$this->strYMDDir;
				}
            }
            else
            {
                $this->strRelativeSaveDir=$strSaveDir.$this->strYMDDir;
                $this->strSaveDir=$strPath.$strSaveDir.$this->strYMDDir;
            }
        }
        if(substr($this->strSaveDir,-1,1)!="/")
            $this->strSaveDir.="/";
        if(substr($this->strRelativeSaveDir,-1,1)!="/")
            $this->strRelativeSaveDir.="/";
    }

    //??????????url???
    function getSaveFileURL()
    {
        return $this->strSaveFileURL;
    }

    //??????????????????????????????
    function getImageInfo()
    {
        $objFile=$_FILES[$this->strInputName];
        $aryImageType = explode("|",$this->strImageType);
        $this->getExtention();
        if(!in_array($this->strExtention,$aryImageType))
        {
            return  "imageTypeError";
        }
        return getimagesize($objFile[tmp_name]);
    }

    //?????????????????????
    function setShowAsChinese($isShowAsChinese=false)
    {
        $this->isShowAsChinese=$isShowAsChinese;
    }

    //?????ftp?????????
    //????????????
    function getUploadType()
    {
        if($this->strUploadType=="ftp")
            return "ftp";
        else
            return "normal";
    }

    //????????????????????ftp????????????????ftp????
    function setUploadType($strUploadType="")
    {
        $this->strUploadType=$strUploadType;
    }

    //???ftp??????
    function getServer()
    {
        return $this->strServer;
    }

    //????ftp??????
    function setServer($strServer="")
    {
        $this->strServer=$strServer;
        $this->hasSetedServer=true;
    }

    //???ftp???
    function getPort()
    {
        return $this->intPort;
    }

    //????ftp???
    function setPort($intPort=21)
    {
        $this->intPort=$intPort;
    }

    //???ftp???
    function getTimeout()
    {
        return $this->intTimeout;
    }

    //????ftp???
    function setTimeout($intTimeout=90)
    {
        $this->intTimeout=$intTimeout;
    }

    //??????ftp?????
    function getUsername()
    {
        return $this->strUsername;
    }

    //??????ftp?????
    function setUsername($strUsername="")
    {
        $this->strUsername=$strUsername;
        $this->hasSetedServer=true;
    }

    //??????ftp????
    function getPassword()
    {
        return $this->strPassword;
    }

    //??????ftp????
    function setPassword($strPassword="")
    {
        $this->strPassword=$strPassword;
        $this->hasSetedServer=true;
    }

    //???ftp??????????
    function getServerSite()
    {
        return $this->strServerSite;
    }

    //????ftp??????????
    function setServerSite($strServerSite="")
    {
		if($strServerSite!="")
        {
            $this->strServerSite=$strServerSite;
        }
        elseif($this->strServerSite=="")
        {
            $this->strServerSite=rand(11,20);
        }

        if(!$this->hasSetedServer)
        {
            switch($this->strServerSite)
            {
                case 1:
                    $this->strServer="172.17.1.57";
                    $this->strUsername="imguser";
                    $this->strPassword="doucaretrx";
                    break;
                case 2:
                    $this->strServer="172.17.1.184";
                    $this->strUsername="imguser";
                    $this->strPassword="doucaretrx";
                    break;
                case 3:
                    $this->strServer="172.17.1.184";
                    $this->strUsername="imguser";
                    $this->strPassword="doucaretrx";
                    break;
                case 4:
                    $this->strServer="172.17.1.184";
                    $this->strUsername="imguser";
                    $this->strPassword="doucaretrx";
                    break;
                case 5:
                    $this->strServer="172.17.1.184";
                    $this->strUsername="imguser";
                    $this->strPassword="doucaretrx";
                    break;
                case 6:
                    $this->strServer="172.17.1.184";
                    $this->strUsername="imguser";
                    $this->strPassword="doucaretrx";
                    break;
                case 7:
                    $this->strServer="172.17.1.184";
                    $this->strUsername="imguser";
                    $this->strPassword="doucaretrx";
                    break;
                case 8:
                    $this->strServer="172.17.1.184";
                    $this->strUsername="imguser";
                    $this->strPassword="doucaretrx";
                    break;
                case 9:
                    $this->strServer="172.17.1.184";
                    $this->strUsername="imguser";
                    $this->strPassword="doucaretrx";
                    break;
                case 10:
                default:
                    $this->strServer="172.17.1.184";
                    $this->strUsername="imguser";
                    $this->strPassword="doucaretrx";
                    break;
            }
        }
    }

    //???????????????????
    //???????????
    //???????????????
    function setResizeImage($needResizeImage=false)
    {
        $this->needResizeImage=$needResizeImage;
    }


    function setForceResizeImage($needForceResizeImage=false)
    {
        $this->needForceResizeImage=$needForceResizeImage;
    }


    function getResizeImageSize()
    {
        return $this->intResizeImageSize;
    }

    //???????????
    function setResizeImageSize($intResizeImageSize=150)
    {
        $this->intResizeImageSize=$intResizeImageSize;
    }

    //???????????
    function setResizeWidth($intResizeWidth=0)
    {
        $this->intResizeWidth=$intResizeWidth;
    }

    //???????????
    function setResizeHeight($intResizeHeight=0)
    {
        $this->intResizeHeight=$intResizeHeight;
    }

    //?????????????????????
    function setResizeCut($needResizeCut=false)
    {
        $this->needResizeCut=$needResizeCut;
    }

    //????????????????ÖÎ???¦²?????????????????
    function setResizeReality($needResizeReality=true)
    {
        $this->needResizeReality=$needResizeReality;
    }

    //????????????????
    function setResizeImagePrefixion($strResizeImagePrefixion="")
    {
        //$this->strResizeImagePrefixion=$strResizeImagePrefixion;
        //?????????
    }

    //?????????????url???
    function getResizeImageURL()
    {
        return $this->strResizeImageURL;
    }

    //?????????????
    //????????
    function setWatermark($needWatermark=false)
    {
        $this->needWatermark=$needWatermark;
    }

    //?????????(1?????,2???)
    function setWatermarkType($intWatermarkType=1)
    {
        $this->intWatermarkType=$intWatermarkType;
    }

    //??????????(1?????,2???)
    function setWatermarkPosition($strWatermarkPosition="rb")
    {
        $this->strPosition=$strWatermarkPosition;
    }

    //???????????
    function setWatermarkString($strWatermarkString="HOUSE365.COM")
    {
        $this->strWatermarkString=$strWatermarkString;
    }

    //????????
    function setWatermarkImage($strWatermarkImage="")
    {
        if($strWatermarkImage=="") $strWatermarkImage=dirname(__FILE__)."/logo.png";
        $this->strWatermarkImage=$strWatermarkImage;
    }

    //??????????????
    function setWatermarkFont($strWatermarkFont="")
    {
        if($strWatermarkFont=="") $strWatermarkFont=dirname(__FILE__)."/ariblk.ttf";
        $this->strWatermarkFont=$strWatermarkFont;
    }

    //???????????????§³
    function setWatermarkSize($intWatermarkSize=24)
    {
        $this->intWatermarkSize=$intWatermarkSize;
    }

    //check
    function checkFile($objFile)
    {
        //check?????????
        if (!is_uploaded_file($objFile[tmp_name]))
        {
            if($objFile[name]!="")
                return "sysSizeLimit";
            return  "noneFile";
        }

        //check?????§³
        if($objFile[size]>$this->intMaxSize)
        {
            return "sizeLimit";
        }
        if($objFile[size]==0)
        {
            return "sizeZero";
        }

        //??????????
        $this->strFileType = strtolower($this->strFileType);
        $this->strFileType = str_replace("image",$this->strImageType,$this->strFileType);
        $this->strFileType = str_replace("office",$this->strOfficeType,$this->strFileType);
        $this->strFileType = str_replace("rar",$this->strRarType,$this->strFileType);
        $this->aryFileType = explode("|",$this->strFileType);
        //check???????
        if(!in_array($this->strExtention,$this->aryFileType))
        {
            return  "typeLimit";
        }

        //????????????
        if($this->strSaveDir=="") $this->setSaveDir();
        $strSaveDir=$this->getSaveDir();
        if(substr($strSaveDir,-5)=="Error") return $strSaveDir;
    }

    //??????
    function upload()
    {
        //$this->getExtention();
		$exten=$this->getExtention();
		/**/
        if($exten=='jpg'){
			if($this->needImageCut)
			{
				$file=$_FILES[$this->strInputName]["tmp_name"];
				list($intWidth,$intHeight) = getimagesize($file);//?????????????
				
				$new_width = $intWidth;
				$new_height = $intHeight;

				//???????????????????????????????????????????????
				if($this->intImageWidth < $intWidth)
				{
					$new_width = $this->intImageWidth;
					$new_height= round($new_width * $intHeight / $intWidth);
				}
				//?????????????????????????????????????????????????
				if($this->intImageHeight < $new_height)
				{
					$new_width = round($intWidth * $this->intImageHeight / $intHeight);
					$new_height = $this->intImageHeight;
				}

				$img=ImageCreateTrueColor($new_width,$new_height);

				$white=imagecolorallocate($img,   255,   255,   255);
				imagecolortransparent($img,$white);
				imagecopyresampled($img,imagecreatefromjpeg($_FILES[$this->strInputName]["tmp_name"]),0,0,0,0,$new_width,$new_height,$intWidth,$intHeight);
				imagejpeg($img,$file,100);
				imagedestroy($img);
			}
		}

        $this->strSavaFileName=uniqid(time()).".".$this->strExtention;
        $objFile=$_FILES[$this->strInputName];
        $strRtn=$this->checkFile($objFile);
        if($strRtn!="")
        {
            if($this->isShowAsChinese)
            {
                $strRtn=$this->getChineseReturn($strRtn);
            }
            return $strRtn;
            exit;
        }

        //???????
        if($this->needWatermark)
        {
            $strRtn=$this->watermark($objFile);
            if($strRtn!="success")
            {
                if($this->isShowAsChinese)
                {
                    $strRtn=$this->getChineseReturn($strRtn);
                }
                return $strRtn;
                exit;
            }
        }

        //?????????
        if($this->needResizeImage)
        {
            $strRtn = $this->resizeImage($objFile);
            if($strRtn=="success")
            {
                $strResizeDir="thumb/";
                if($this->strUploadType=="ftp")
                    $this->strResizeImageURL = "http://img".$this->strServerSite.".house365.com".$this->strRelativeSaveDir.
                                            $strResizeDir.$this->strResizeImagePrefixion.$this->strSavaFileName;
                else
                    $this->strResizeImageURL = $this->strRelativeSaveDir.$strResizeDir.$this->strResizeImagePrefixion.$this->strSavaFileName;
            }
            elseif($strRtn=="noNeedResize")
                $this->strResizeImageURL = &$this->strSaveFileURL;
            else
            {
                $this->strResizeImageURL = "";
                if($this->isShowAsChinese)
                {
                    $strRtn=$this->getChineseReturn($strRtn);
                }
                return $strRtn;
                exit;
            }
        }
        //?????
        if($this->strUploadType=="ftp")
        {
            $strRtn = $this->ftpUpload($objFile);
            if($strRtn=="success")
                $this->strSaveFileURL="http://img".$this->strServerSite.".house365.com".$this->strRelativeSaveDir.$this->strSavaFileName;
            else
                $this->strSaveFileURL="";
        }
        else
        {
            $strRtn = $this->normalUpload($objFile);
            if($strRtn=="success")
                $this->strSaveFileURL=$this->strRelativeSaveDir.$this->strSavaFileName;
            else
                $this->strSaveFileURL="";
        }

        /*if($strUploadRtn=="success")
        {
            $conn=new ConnDB("njhouse","NJDOMAIN");
            $strSql="insert into uploadfile(channel,username,uploadtime,savedir,filename,savefilename,fileurl,resizeimage,description) values('".
                ."','".
                ."','".
                ."','".
                ."','".
                ."','".
                ."','".
                ."','".
                ."','".
                ."','".
                ."','".
                ."','".
                ."','".
                ."')";
        }*/
        if($strRtn!="success" && $this->isShowAsChinese)
        {
            $strRtn=$this->getChineseReturn($strRtn);
        }
        return $strRtn;
    }

    //?????????
    function normalUpload($objFile)
    {
        if(move_uploaded_file($objFile[tmp_name],$this->strSaveDir.$this->strSavaFileName))
            return "success";
        else
            return "error";
    }

    //??ftp?????????
    function ftpUpload($objFile)
    {
        if(@ftp_put($this->ftpConn,$this->strSaveDir.$this->strSavaFileName,$objFile[tmp_name],FTP_BINARY))
        {
            ftp_quit($this->ftpConn); //???ftp????
            return "success";
        }
        else
        {
            ftp_quit($this->ftpConn); //???ftp????
            return "error";
        }
    }

    //????????§³
    function resizeImage($objFile)
    {
        $aryImageType = explode("|",$this->strImageType);
        if(!in_array($this->strExtention,$aryImageType))
        {
            return  "imageTypeError";
        }

        if($this->strResizeImagePrefixion=="")
            $strResizeDir="thumb/";
        if($this->strUploadType=="ftp")
        {
            list($intWidth,$intHeight) = getimagesize($objFile[tmp_name]);//?????????????

            $intResizeWidth=$this->intResizeWidth;
            $intResizeHeight=$this->intResizeHeight;

            if($intResizeWidth>0 && $intResizeHeight<=0)
                $intResizeHeight=$intHeight*($intResizeWidth/$intWidth);
            elseif($intResizeHeight>0 && $intResizeWidth<=0)
                $intResizeWidth=$intWidth*($intResizeHeight/$intHeight);
            elseif($intResizeWidth<=0 && $intResizeHeight<=0 && $this->intResizeImageSize>0)
            {
                if($intWidth > $intHeight)//?ÕÇ????????????§³
                {
                    $intResizeWidth=$this->intResizeImageSize;
                    $intResizeHeight=$intHeight*($intResizeWidth/$intWidth);
                }
                else
                {
                    $intResizeHeight=$this->intResizeImageSize;
                    $intResizeWidth=$intWidth*($intResizeHeight/$intHeight);
                }
            }
            elseif($intResizeWidth<=0)
            {
                $intResizeWidth=150;
                $intResizeHeight=$intHeight*($intResizeWidth/$intWidth);
            }
            elseif($this->needResizeCut)
            {
                if($intWidth/$intResizeWidth>$intHeight/$intResizeHeight)
                    $intWidth=$intResizeWidth*$intHeight/$intResizeHeight;
                else
                    $intHeight=$intResizeHeight*$intWidth/$intResizeWidth;
            }
            elseif($this->needResizeReality)
            {
                if($intWidth/$intResizeWidth>$intHeight/$intResizeHeight)
                    $intResizeHeight=$intHeight*$intResizeWidth/$intWidth;
                else
                    $intResizeWidth=$intWidth*$intResizeHeight/$intHeight;
            }

            //check the image size
            if($intWidth<=$intResizeWidth && $intHeight<=$intResizeHeight && $this->needForceResizeImage==false)
            {
                return "noNeedResize";
            }

            $image1 = imagecreatetruecolor($intResizeWidth,$intResizeHeight); //???????????
            imagealphablending($image1, false);//???????????
            imagesavealpha($image1,true);//?Ú…?????????? alpha ??????
            $backgroundColor = imagecolorallocatealpha($image1,255,255,255,127);
            imageFilledRectangle($image1,0,0,$intResizeWidth-1,$intResizeHeight-1,$backgroundColor);

            $aryImageInfo=getimagesize($objFile[tmp_name],$aryImageInfo);
            switch ($aryImageInfo[2])
            {
                case 1:
                    $image2 = imagecreatefromgif($objFile[tmp_name]);//????????????image2
                    break;
                case 2:
                    $image2 = imagecreatefromjpeg($objFile[tmp_name]);
                    break;
                case 3:
                    $image2 = imagecreatefrompng($objFile[tmp_name]);
                    break;
                case 6:
                    $image2 = imagecreatefromwbmp($objFile[tmp_name]);
                    break;
                default:
                {
                    ftp_quit($this->ftpConn); //???ftp????
                    return  "imageTypeError";
                }
            }
            //?§Ø????????????
            if(!$image2)
            {
                ftp_quit($this->ftpConn); //???ftp????
                return "imageTypeError";
            }

            imagecopyresampled($image1,$image2,0,0,0,0,$intResizeWidth,$intResizeHeight,$intWidth,$intHeight); //??????

            if(!@ftp_chdir($this->ftpConn,$this->strSaveDir.$strResizeDir))
            {
                if(!@ftp_mkdir($this->ftpConn,$this->strSaveDir.$strResizeDir))
                {
                    ftp_quit($this->ftpConn); //???ftp????
                    return "mkdirError";
                }
            }

            switch ($aryImageInfo[2])
            {
                case 1:
                    //$isOK=@imagegif($image1,"retemp_".$this->strSavaFileName);//????????
                    $isOK=@imagepng($image1,"retemp_".$this->strSavaFileName);//????????
                    break;
                case 2:
                    $isOK=@imagejpeg($image1,"retemp_".$this->strSavaFileName);//????????
                    break;
                case 3:
                    $isOK=@imagepng($image1,"retemp_".$this->strSavaFileName);//????????
                    break;
                case 6:
                    $isOK=@imagewbmp($image1,"retemp_".$this->strSavaFileName);//????????
                    break;
                default:
                {
                    return  "imageTypeError";
                }
            }

            $isOK = @ftp_put($this->ftpConn,$this->strSaveDir.$strResizeDir.$this->strResizeImagePrefixion.$this->strSavaFileName,"retemp_".$this->strSavaFileName,FTP_BINARY);
            @unlink("retemp_".$this->strSavaFileName);
            if($isOK)
            {
                return "success";
            }
            else
            {
                ftp_quit($this->ftpConn); //???ftp????
                return "error";
            }
        }
        else
        {
            if(!@file_exists($objFile[tmp_name]))
            {
                return  "noneFileError";
            }
            list($intWidth,$intHeight) = getimagesize($objFile[tmp_name]);//?????????????

            $intResizeWidth=$this->intResizeWidth;
            $intResizeHeight=$this->intResizeHeight;

            if($intResizeWidth>0 && $intResizeHeight<=0)
                $intResizeHeight=$intHeight*($intResizeWidth/$intWidth);
            elseif($intResizeHeight>0 && $intResizeWidth<=0)
                $intResizeWidth=$intWidth*($intResizeHeight/$intHeight);
            elseif($intResizeWidth<=0 && $intResizeHeight<=0 && $this->intResizeImageSize>0)
            {
                if($intWidth > $intHeight)//?ÕÇ????????????§³
                {
                    $intResizeWidth=$this->intResizeImageSize;
                    $intResizeHeight=$intHeight*($intResizeWidth/$intWidth);
                }
                else
                {
                    $intResizeHeight=$this->intResizeImageSize;
                    $intResizeWidth=$intWidth*($intResizeHeight/$intHeight);
                }
            }
            elseif($intResizeWidth<=0)
            {
                $intResizeWidth=150;
                $intResizeHeight=$intHeight*($intResizeWidth/$intWidth);
            }
            elseif($this->needResizeCut)
            {
                if($intWidth/$intResizeWidth>$intHeight/$intResizeHeight)
                    $intWidth=$intResizeWidth*$intHeight/$intResizeHeight;
                else
                    $intHeight=$intResizeHeight*$intWidth/$intResizeWidth;
            }
            elseif($this->needResizeReality)
            {
                if($intWidth/$intResizeWidth>$intHeight/$intResizeHeight)
                    $intResizeHeight=$intHeight*$intResizeWidth/$intWidth;
                else
                    $intResizeWidth=$intWidth*$intResizeHeight/$intHeight;
            }

            //check the image size
            if($intWidth<=$intResizeWidth && $intHeight<=$intResizeHeight && $this->needForceResizeImage==false)
            {
                return "noNeedResize";
            }

            $image1 = imagecreatetruecolor($intResizeWidth,$intResizeHeight); //???????????
            imagealphablending($image1, false);//???????????
            imagesavealpha($image1,true);//?Ú…?????????? alpha ??????
            $backgroundColor = imagecolorallocatealpha($image1,255,255,255,127);
            imageFilledRectangle($image1,0,0,$intResizeWidth-1,$intResizeHeight-1,$backgroundColor);

            $aryImageInfo=getimagesize($objFile[tmp_name],$aryImageInfo);
            switch ($aryImageInfo[2])
            {
                case 1:
                    $image2 = imagecreatefromgif($objFile[tmp_name]);//????????????image2
                    break;
                case 2:
                    $image2 = imagecreatefromjpeg($objFile[tmp_name]);
                    break;
                case 3:
                    $image2 = imagecreatefrompng($objFile[tmp_name]);
                    break;
                case 6:
                    $image2 = imagecreatefromwbmp($objFile[tmp_name]);
                    break;
                default:
                {
                    return  "imageTypeError";
                }
            }
            //?§Ø????????????
            if(!$image2)
            {
                return "imageTypeError";
            }

            imagecopyresampled($image1,$image2,0,0,0,0,$intResizeWidth,$intResizeHeight,$intWidth,$intHeight); //??????

            if(!@file_exists($this->strSaveDir.$strResizeDir))
            {
                if(!@mkdir($this->strSaveDir.$strResizeDir,0777))
                    return "mkdirError";
            }

            switch ($aryImageInfo[2])
            {
                case 1:
                    //$isOK=@imagegif($image1,$this->strSaveDir.$strResizeDir.$this->strResizeImagePrefixion.$this->strSavaFileName);//????????
                    $isOK=@imagepng($image1,$this->strSaveDir.$strResizeDir.$this->strResizeImagePrefixion.$this->strSavaFileName);//????????
                    break;
                case 2:
                    $isOK=@imagejpeg($image1,$this->strSaveDir.$strResizeDir.$this->strResizeImagePrefixion.$this->strSavaFileName,100);//????????
                    break;
                case 3:
                    $isOK=@imagepng($image1,$this->strSaveDir.$strResizeDir.$this->strResizeImagePrefixion.$this->strSavaFileName);//????????
                    break;
                case 6:
                    $isOK=@imagewbmp($image1,$this->strSaveDir.$strResizeDir.$this->strResizeImagePrefixion.$this->strSavaFileName);//????????
                    break;
                default:
                {
                    return  "imageTypeError";
                }
            }
            if($isOK)
                return "success";
            else
                return "error";
        }
    }

    //???????
    function watermark($objFile)
    {
        $aryImageInfo=getimagesize($objFile[tmp_name],$aryImageInfo);
        switch ($aryImageInfo[2])
        {
            case 1:
                $sourceImage = imagecreatefromgif($objFile[tmp_name]);
                break;
            case 2:
                $sourceImage = imagecreatefromjpeg($objFile[tmp_name]);
                break;
            case 3:
                $sourceImage = imagecreatefrompng($objFile[tmp_name]);
                break;
            case 6:
                $sourceImage = imagecreatefromwbmp($objFile[tmp_name]);
                break;
            default:
                return "imageTypeError";
                exit;
        }
        //?§Ø????????????
        if(!$sourceImage)
            return "imageTypeError";

        //??????¦Ë??
        if($this->intWatermarkType!=2)//??????
        {
            $ary = imagettfbbox(ceil($this->intWatermarkSize),0,$this->strWatermarkFont,$this->strWatermarkString);//?????? TrueType ???????????¦¶
            $intWaterWidth = $ary[4] - $ary[6];
            $intWaterHeight = $ary[7] - $ary[1];
            unset($ary);
        }
        else//????
        {
            $aryWaterImageInfo=getimagesize($this->strWatermarkImage,$aryWaterImageInfo);
            $intWaterWidth = $aryWaterImageInfo[0];
            $intWaterHeight = $aryWaterImageInfo[1];
        }
        //???????????§³
        if( ($aryImageInfo[0]<$intWaterWidth) || ($aryImageInfo[1]<$intWaterHeight) )
        {
            return "success";
        }

        switch($this->strPosition)
        {
            case 'sj':
                $posX = rand(0,($aryImageInfo[0] - $intWaterWidth));
                $posY = rand(50,($aryImageInfo[1] - $intWaterHeight));
                break;
            case 'lt':
                $posX = 0;
                $posY = 50;
                break;
            case 'rt':
                $posX = $aryImageInfo[0] - $intWaterWidth;
                $posY = 50;
                break;
            case 'lb':
                $posX = 0;
                $posY = $aryImageInfo[1] - $intWaterHeight;
                break;
            case 'ct':
                $posX = ($aryImageInfo[0] - $intWaterWidth) / 2;
                $posY = 50;
                break;
            case 'cb':
                $posX = ($aryImageInfo[0] - $intWaterWidth) / 2;
                $posY = $aryImageInfo[1] - $intWaterHeight;
                break;
            case 'lc':
                $posX = 0;
                $posY = ($aryImageInfo[1] - $intWaterHeight) / 2;
                break;
            case 'rc':
                $posX = $aryImageInfo[0] - $intWaterWidth;
                $posY = ($aryImageInfo[1] - $intWaterHeight) / 2;
                break;
            case 'cc':
                $posX = ($aryImageInfo[0] - $intWaterWidth) / 2;
                $posY = ($aryImageInfo[1] - $intWaterHeight) / 2;
                break;
            case 'rb':
				$posX = $aryImageInfo [0] - $intWaterWidth - 20;
				$posY = $aryImageInfo [1] - $intWaterHeight - 40;
				break;
            default:
                $posX = $aryImageInfo[0] - $intWaterWidth;
                $posY = $aryImageInfo[1] - $intWaterHeight;
                break;
        }

        if($this->intWatermarkType!=2)//??????
        {
            $white=imagecolorallocatealpha($sourceImage,255,255,255,60);
            imagettftext($sourceImage,$this->intWatermarkSize,0,$posX,$posY,$white,$this->strWatermarkFont,$this->strWatermarkString);
        }
        else//??????
        {
            switch ($aryWaterImageInfo[2])
            {
                case 1:
                    $waterImage = imagecreatefromgif($this->strWatermarkImage);
                    break;
                case 2:
                    $waterImage = imagecreatefromjpeg($this->strWatermarkImage);
                    break;
                case 3:
                    $waterImage = imagecreatefrompng($this->strWatermarkImage);
                    break;
                case 6:
                    $waterImage = imagecreatefromwbmp($this->strWatermarkImage);
                    break;
                default:
                    return "typeError";
                    exit;
            }
            //?§Ø????????????
            if(!$waterImage)
                return "imageTypeError";

            imagealphablending($sourceImage, true);
            imagecopy($sourceImage,$waterImage,$posX,$posY,0,0,$aryWaterImageInfo[0],$aryWaterImageInfo[1]);
            imagedestroy($waterImage);
        }

        switch ($aryImageInfo[2])
        {
        case 1:
            //imagegif($sourceImage, $objFile[tmp_name]);
            imagejpeg($sourceImage, $objFile[tmp_name]);
            break;
        case 2:
            imagejpeg($sourceImage, $objFile[tmp_name]);
            break;
        case 3:
            imagepng($sourceImage, $objFile[tmp_name]);
            break;
        case 6:
            imagewbmp($sourceImage, $objFile[tmp_name]);
            //imagejpeg($sourceImage, $objFile[tmp_name]);
        break;
        }

        //???????????
        imagedestroy($sourceImage);
        return "success";
    }

    //???????????
    function remove($strSaveFileURL,$strBaseDir="/database/webroot")
    {
        if($this->strUploadType=="ftp")
        {
            preg_match_all("/http:\/\/img(\d+).house365.com\/(.*)/i",$strSaveFileURL,$ary);
            $strFilePath="img".$ary[1][0]."/".$ary[2][0];
            if($this->hasSetedServer)
                $this->strServerSite=$ary[1][0];
            else
                $this->setServerSite($ary[1][0]);
            //ftp???
            $this->ftpConn = @ftp_connect($this->strServer);
            if(!$this->ftpConn)
            {
                $strRtn="connError";
            }
            elseif(!@ftp_login($this->ftpConn,$this->strUsername,$this->strPassword))
            {
                ftp_quit($this->ftpConn); //???ftp????
                $strRtn="loginError";
            }
            elseif(@ftp_size($this->ftpConn,$strFilePath)==-1)
                $strRtn="noneFile";
            elseif(@ftp_delete($this->ftpConn,$strFilePath))
            {
                $intRPos=strrpos($strFilePath,"/");
                $strResizeImageURL=substr($strFilePath,0,$intRPos+1)."thumb/".substr($strFilePath,$intRPos+1);
                @ftp_delete($this->ftpConn,$strResizeImageURL);
                $strRtn="success";
            }
            else
                $strRtn="error";
            ftp_quit($this->ftpConn); //???ftp????
        }
        else
        {
            if(@file_exists($strBaseDir.$strSaveFileURL))
            {
                unlink($strBaseDir.$strSaveFileURL);
                $intRPos=strrpos($strSaveFileURL,"/");
                $strResizeImageURL=substr($strSaveFileURL,0,$intRPos+1)."thumb/".substr($strSaveFileURL,$intRPos+1);
                if(@file_exists($strBaseDir.$strResizeImageURL))
                    unlink($strBaseDir.$strResizeImageURL);
                $strRtn="success";
            }
            else
                $strRtn="noneFile";
        }

        if($strRtn!="success" && $this->isShowAsChinese)
        {
            $strRtn=$this->getChineseReturn($strRtn);
        }
        return $strRtn;
    }

    //???????????
    function getChineseReturn($strRtn)
    {
        switch($strRtn)
        {
            case "noneFile":
                return "?????????";
                break;
            case "sizeLimit":
                return "?????§³????????";
                break;
            case "sysSizeLimit":
                return "?????§³??????????";
                break;
            case "typeLimit":
                return "???????????";
                break;
            case "mkdirError":
                return "????????§Ô?????????????????????§Ý?¡¤??????????????????";
                break;
            case "connError":
                return "ftp???????";
                break;
            case "loginError":
                return "ftp??????";
                break;
            case "sizeZero":
                return "?????§³?0?????????????";
                break;
            case "imageTypeError":
                if($this->needWatermark)
                    $rtnM = "??????????????";
                elseif($this->needResizeImage)
                    $rtnM = "??????????????????";
                return "??????§¹???????".$rtnM;
                break;
            case "noneFileError":
                return "??????????????????";
                break;
            default:
                return "¦Ä?????";
                break;
        }
    }
}
?>