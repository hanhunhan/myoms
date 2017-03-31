<?php
import('Org.Io.Mylog');
class UploadAction extends ExtendAction{

		 function uploadFile(){
				import('ORG.Net.UploadFile');
				$upload = new UploadFile();// 实例化上传类
				$upload->maxSize  = 10000000000;// 设置附件上传大小
				$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg','rar','zip','doc','docx','xls');// 设置附件上传类型
				$upload->saveRule = time();
			 
				$path = './Public/Uploads/'.date('Y').'/'.date('m').'/'.date('d').'/';
				//if(is_dir($path))mkdir($path);
				mkdirs($path);
				$upload->savePath = $path ;// 设置附件上传目录
				if(!$upload->upload()) {// 上传错误提示错误信息
					exit();//$this->error($upload->getErrorMsg());
				}else{// 上传成功
					$f =$upload->getUploadFileInfo();
					echo $f[0]['savepath'].$f[0]['savename'];
					exit();
				}
				 
		 }

		 function save2oracle2(){
			$lob_upload = $_FILES['Filedata'];
			$fp = fopen($lob_upload['tmp_name'],'rb');
			$FILE_TYPE = $lob_upload['type'];
			$FILE_NAME = $lob_upload['name'];  
			$FILE_SIZE = $lob_upload['size']; 
			$FILE_CODE = md5($FILE_TYPE .$FILE_NAME.$FILE_SIZE.time() );
			// Now let's insert an entry into the database
			try {
				$model  =D('Erp_files');
				$model->startTrans(); 
				$data['FILE_TYPE'] = $FILE_TYPE;
				$data['FILE_NAME'] = $FILE_NAME;
				$data['FILE_SIZE'] = $FILE_SIZE;
				$data['FILE_CODE'] = $FILE_CODE;
				$data['FILE_DATA'] = $fp;
				$model->add($data); 
				$model->commit();


			  
				echo $FILE_CODE;
			} catch (PDOException $e) {
				die('ERROR: ' . $e->getMessage() . "\n");
			}
			 
			 
			 
		 }
		 function save2oracle1(){
			define('DBUSERNAME','oms');
			define('DBPASSWORD','oms');
			//define('DBHOST','192.168.105.94');
			define('DBNAME','oms');
			define('DATASOURCE','oci');
			$lob_upload = $_FILES['Filedata'];
			$conn = NULL;

			// Enable Database connection
			function getConnectionString() {
				$options = array();
				if(DBHOST != "") {
					$options[] = "host=" . DBHOST;
				}
				if(DBNAME != "") {
					$options[] = "dbname=" . DBNAME;
				}
				return implode(";", $options);
			}

			try {
				$conn = new PDO("oci:dbname= oms", DBUSERNAME, DBPASSWORD);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch(PDOException $e) {
				// If we get an exception then we need to terminate
				die('ERROR: ' . $e->getMessage() . "\n");
			}
			//Header("content-type:image/jpg");
			//$PSize = filesize($lob_upload['tmp_name']);
			//$fp = fread(fopen($lob_upload['tmp_name'], "r"), $PSize);
			
			
			//$data = fread($data, filesize($lob_upload['tmp_name'])); 
			//echo length($data);
			 

			//$handle=fopen($lob_upload['tmp_name'],"rb");//使用打开模式为r

			//$data=stream_get_contents($handle );//读为二进制
			//fclose($handle);
			 
			//$data = fopen($lob_upload['tmp_name'], 'rb');
			//move_uploaded_file($lob_upload['tmp_name'],'new.jpg');
			//$fp =  addslashes(  file_get_contents($lob_upload['tmp_name']) ) ;
			$fp = fopen($lob_upload['tmp_name'],'rb');
			$FILE_TYPE = $lob_upload['type'];
			$FILE_NAME = $lob_upload['name'];  
			$FILE_SIZE = $lob_upload['size']; 
			$FILE_CODE = md5($FILE_TYPE .$FILE_NAME.$FILE_SIZE.time() );
			// Now let's insert an entry into the database
			try {
				$sql = "insert into ERP_FILES(FILE_TYPE,FILE_NAME,FILE_SIZE,FILE_CODE,FILE_DATA)values(:FILE_TYPE,:FILE_NAME,:FILE_SIZE,:FILE_CODE,:blob) returning FILE_DATA into :FILE_DATA ";
	    //$sql = "insert into ERP_FILES(FILE_TYPE,FILE_NAME,FILE_SIZE,FILE_CODE,FILE_DATA)values('$FILE_TYPE','$FILE_NAME','$FILE_SIZE','$FILE_CODE',EMPTY_BLOB())";
				$stmt = $conn->prepare($sql);
				$stmt->bindParam(":FILE_TYPE",$FILE_TYPE);
				$stmt->bindParam(":FILE_NAME",$FILE_NAME);
				$stmt->bindParam(":FILE_SIZE",$FILE_SIZE);
				$stmt->bindParam(":FILE_CODE",$FILE_CODE);
				//$conn->prepare();
				$stmt->bindParam(":blob",$fp,PDO::PARAM_LOB);
				
				
				$conn->beginTransaction(); 
				$stmt->execute($sql);
				$conn->commit(); 
				echo $FILE_CODE;
			} catch (PDOException $e) {
				die('ERROR: ' . $e->getMessage() . "\n");
			}
			 
			 
			 
		 }
		 
		 function save2oracle(){  
				ini_set('display_errors',1);  
				if ( $_POST['token'] == md5('nr234n9i92n2' . $_POST['timestamp']) ){
					$lob_upload = $_FILES['Filedata'];
					// Mylog::write("三十四");
					$FILE_TYPE = $lob_upload['type'];
					//$FILE_NAME=$lob_upload['name']; 
					//$encode = mb_detect_encoding($lob_upload['name'], array("ASCII","GB2312","GBK","UTF-8")); 
					$FILE_NAME=$lob_upload['name'];
					$FILE_NAME= iconv('UTF-8','GBK',$FILE_NAME);
					if(strlen($FILE_NAME)>=50 ){
						/*$file_path = pathinfo($FILE_NAME,PATHINFO_EXTENSION);
						if($file_path['extension']){
						$extend = '.'.$file_path['extension'];
						}else{
							$extend =explode(".",$FILE_NAME); 
							$va=count($extend)-1;
							$extend = '.'.$extend[$va]; 
						}*/
						$FILE_NAME= substr($FILE_NAME,-50);
					}
					
					//$FILE_NAME= substr($FILE_NAME,0,50).$extend; 
					$FILE_SIZE = $lob_upload['size']; 
					$FILE_CODE = md5($FILE_TYPE .$FILE_NAME.$FILE_SIZE.time() );
					//$db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = 202.102.83.186)(PORT = 1521)))(CONNECT_DATA=(SID=OMS2)))";
					//$conn =  oci_connect(C('DB_USER'),C('DB_PWD'),$db); 
					$conn = oci_connect(C('DB_USER'), C('DB_PWD'), C('DB_NAME'));
					
					//$conn = oci_connect($user, $password);
					$lobb = oci_new_descriptor($conn, OCI_D_LOB); 
					$sql = "insert into ERP_FILES(FILE_TYPE,FILE_NAME,FILE_SIZE,FILE_CODE,FILE_DATA)values('$FILE_TYPE','$FILE_NAME','$FILE_SIZE','$FILE_CODE',EMPTY_BLOB()) returning FILE_DATA into :blobb";
					$stmt = oci_parse($conn, $sql);
					//Mylog::write( '$sql;'.$sql);
					oci_bind_by_name($stmt, ':blobb', $lobb, -1, OCI_B_BLOB);
					oci_execute($stmt, OCI_DEFAULT);
					if ($lobb->saveFile($lob_upload['tmp_name'])){
						oci_commit($conn);
						/*$sql = "SELECT seq_erp_files.currval currval FROM dual";
						$ora_test = oci_parse($conn,$sql);
						oci_execute($ora_test,OCI_DEFAULT);  
						//echo "Blob successfully uploaded ";
						$r=oci_fetch_row($ora_test);
						echo $r[0];*/
						echo $FILE_CODE;
						//Mylog::write( '$FILE_CODE;'.$FILE_CODE);

					}else{
						//echo "error";
					}
					oci_free_descriptor($lobb);
					oci_free_statement($stmt);
					oci_close($conn);
					//exit();
				}else{exit('nopower');}
		}
		function showfile1(){ 
			if (!isset($_GET['filecode'])) {
				return;
			  }

			  $empid = $_GET['filecode'];

			  $conn = oci_connect(C('DB_USER'), C('DB_PWD'),'//'.C('DB_HOST').'/'.C('DB_NAME'));

			  if (!$conn) {
				return;
			  }
			 
			  $query = "select * from ERP_FILES where FILE_CODE=:eid";

			  $stid = oci_parse($conn, $query);
			  $r = oci_bind_by_name($stid, ":eid", $empid, -1);
			  if (!$r) {
				return;
			  }
			  $r = oci_execute($stid, OCI_DEFAULT);
			  if (!$r) {
				return;
			  }

			  //$result = oci_fetch_row($stid); 
			  OCIFetchInto($stid, $result, OCI_ASSOC+OCI_RETURN_LOBS);
			  if (!$result) {
				return;                     // photo not found
			  }


			    if( headers_sent() ) 
				die('Headers Sent'); 

				  // Required for some browsers 
				  if(ini_get('zlib.output_compression')) 
					ini_set('zlib.output_compression', 'Off'); 

				  // File Exists? 
				   
				  if( $result ){ 
					
					// Parse Info / Get Extension 
					$fsize = filesize($result["FILE_SIZE"]); 
					//$path_parts = pathinfo($fullPath); 
					$ext =  (strtolower($result["FILE_NAME"])); 
					$ext = explode('.' , $ext); 
					$num = count($ext); 
					$ext = $ext[$num-1];
					  
					// Determine Content Type 
					switch ($ext) { 
					  case "pdf": $ctype="application/pdf"; break; 
					  case "exe": $ctype="application/octet-stream"; break; 
					  case "zip": $ctype="application/zip"; break; 
					  case "rar": $ctype="application/rar"; break;
					  case "doc": $ctype="application/msword"; break;
					  case "docx": $ctype="application/msword"; break; 
					  case "xls": $ctype="application/vnd.ms-excel"; break;
					  case "xlsx": $ctype="application/vnd.ms-excel"; break; 
					  case "ppt": $ctype="application/vnd.ms-powerpoint"; break; 
					  case "gif": $ctype="image/gif"; break; 
					  case "png": $ctype="image/png"; break; 
					  case "jpeg": 
					  case "jpg": $ctype="image/jpg"; break; 
					  default: $ctype="application/force-download"; 
					} 
					
					/*header("Pragma: public"); // required 
					header("Expires: 0"); 
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
					header("Cache-Control: private",false); // required for certain browsers 
					header("Content-Type: $ctype"); 
					header("Content-Disposition: attachment; filename=\"".basename($result["FILE_NAME"])."\";" ); 
					header("Content-Transfer-Encoding: binary"); 
					header("Content-Length: ".$fsize); */
					Header("content-type:$ctype");
					header("Content-Disposition: attachment; filename=".basename($result["FILE_NAME"]) );
					 

					ob_clean(); 
					flush();
					 
					 
					echo $result["FILE_DATA"];

				  } else  echo 'File Not Found'; 

			
		}

		function showfile(){
			$result = M('Erp_files')->where("FILE_CODE='".$_REQUEST['filecode']."'")->find();

				  if( headers_sent() ) 
				  die('Headers Sent'); 

				  // Required for some browsers 
				  if(ini_get('zlib.output_compression')) 
					ini_set('zlib.output_compression', 'Off'); 

				  // File Exists? 
				   
				  if( $result ){ 
					
					// Parse Info / Get Extension 
					$fsize = filesize($result["FILE_SIZE"]); 
					//$path_parts = pathinfo($fullPath); 
					$ext =   $result["FILE_NAME"] ; 
					$ext = explode('.' , $ext); 
					$num = count($ext); 
					$ext = strtolower($ext[$num-1]);
					  
					// Determine Content Type 
					switch ($ext) { 
					  case "pdf": $ctype="application/pdf"; break; 
					  case "exe": $ctype="application/octet-stream"; break; 
					  case "zip": $ctype="application/zip"; break; 
					  case "rar": $ctype="application/rar"; break;
					  case "doc": $ctype="application/msword"; break;
					  case "docx": $ctype="application/msword"; break; 
					  case "xls": $ctype="application/vnd.ms-excel"; break;
					  case "xlsx": $ctype="application/vnd.ms-excel"; break; 
					  case "ppt": $ctype="application/vnd.ms-powerpoint"; break; 
					  case "gif": $ctype="image/gif"; break; 
					  case "png": $ctype="image/png"; break; 
					  case "jpeg": 
					  case "jpg": $ctype="image/jpg"; break; 
					  default: $ctype="application/force-download"; 
					} 
					
					/*header("Pragma: public"); // required 
					header("Expires: 0"); 
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
					header("Cache-Control: private",false); // required for certain browsers 
					header("Content-Type: $ctype"); 
					header("Content-Disposition: attachment; filename=\"".basename($result["FILE_NAME"])."\";" ); 
					header("Content-Transfer-Encoding: binary"); 
					header("Content-Length: ".$fsize); */
					Header("content-type:$ctype");
					header("Content-Disposition: attachment; filename=" . ($result["FILE_NAME"]) );
					 

					ob_clean(); 
					flush();
					 
					 
					echo $result["FILE_DATA"];

				  } else  echo 'File Not Found';  

		}

	 
         
		 
}