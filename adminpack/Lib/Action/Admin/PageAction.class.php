<?php
    class PageAction extends ExtendAction{
		public function _initialize(){	
		parent::_initialize();
		}
		      
		public $klist = array(
			  '1'=> array('name'=>'����¥��X4',
				'input'=>array('¥������'=>array('txt','name'),'¥��URL'=>array('txt','url')),
                'num'=>4
			   ),
			  '2'=> array('name'=>'��̬���λX3',
			   'input'=>array('���url'=>array('txt','url'),'���ͼƬ'=>array('file','pic')),
               'num'=>3
			   ),
			   '3'=> array('name'=>'����˹�ע��¥��X4',
				'input'=>array('¥��id'=>array('txt','id'),'¥��url'=>array('txt','url'),'¥��ͼƬ'=>array('file','pic')),
                'num'=>4
			   ),
               '4'=> array('name'=>'������õ�¥��X4',
				'input'=>array('¥��id'=>array('txt','id'),'¥��url'=>array('txt','url'),'¥��ͼƬ'=>array('file','pic')),
                'num'=>4
			   ),
			   );
			
       
	   public function index(){ 
	       $tage=1;
		//��ȡform	   
		   if($this->_get("ft")=="kk"){
			   $id=$this->_get("id");
			   $this->getForm($id);
			   return;
		   }
        //��ȡlist
           if($this->_get("ft")=="yy"){
			   $id=$this->_get("id");
			   $this->show($id);
			   return;
		   }
        //����
		   if($this->_post("inputT")){
     		 if($this->_post("edid")){
     		     $tage=$_POST[inputT];
		         $this->save();
		      }
            else{
                $tage=$_POST[inputT];
                $this->add();
            }   
            }
        //ɾ��
            if($this->_get("ft")=="del"){
                $id=$this->_get("id");
                $this->delet($id);
                $tage=$this->_get("tag");
            } 
        //�༭ 
             if($this->_get("ft")=="zz"){
                $id=$this->_get("id");
                $this->getEform($id);
                return;
            } 
        $this->assign('kk',$tage);
		$List=$this->klist;
		$this->assign('list',$List);
		$this->display();
       }
       //ɾ��
       public function delet($id){  
            M('page')->where('id='.$id)->delete();
       }
       public function getEform($id){
            $aa=M('page')->where('id='.$id)->find();
            $arr1 = (Array)json_decode($aa[content]);
            $arr1=u2g($arr1);
		   $arr=$this->klist[$aa[tag]][input];
		   $htmlstr='<table class="table2" width="98%">';
		   foreach($arr as $key=>$value){
			if($value['0']=='txt'){
				$htmlstr=$htmlstr.'<tr>
						<td><em class="xing">*</em>'.$key.'��</td>
						<td><input type="text"  value="'.$arr1[$value['1']].'"name="'.$value['1'].'" style="width:600px;"></td>
					</tr>';
            }
			if($value['0']=='file'){
				$htmlstr=$htmlstr.'
                    <tr>
						<td><em class="xing">*</em>ԭͼƬ��</td>
						<td><img width="80" height="80" src="'.$arr1[$value['1']].'" /></td>
					</tr>
                    <tr>
						<td><em class="xing">*</em>'.$key.'��</td>
						<td><input type="file" name="'.$value['1'].'" id="'.$value['1'].'" style="width:600px;"></td>
					</tr>';
			}
			}
			$htmlstr=$htmlstr
					.'<tr>
						<td><em class="xing">*</em>�Ƿ���ʾ��</td>
						<td>
						<input name="rb"type="radio" ';
            if($aa[status]==1){
                $htmlstr=$htmlstr.'checked ';
            }
            $htmlstr=$htmlstr
                       .' value="1"/>��ʾ
						<input name="rb"type="radio" ';
            if($aa[status]==2){
                $htmlstr=$htmlstr.'checked ';                  
            }
            $htmlstr=$htmlstr
                       .
                        'value="2"/>����<label id="city_name_info" style="margin-left:10px;"></label>
						</td>
					</tr>
					<tr>
						<td><em class="xing">*</em>��ʾ����</td>
						<td><input type="txt" name="ord" value="'.$aa['ord'].'" id="" style="width:600px;"><label id="city_name_info" style="margin-left:10px;"></label></td>
					</tr>';
			$htmlstr=$htmlstr.'<input id="edid" type="hidden" name="edid"  value="'.$id.'"></table>';
			echo $htmlstr;exit;
	}     
	   public function getForm($id){
		   $arr=$this->klist[$id][input];
		   $htmlstr='<table class="table2" width="98%">';
		   foreach($arr as $key=>$value){
			if($value['0']=='txt'){
				$htmlstr=$htmlstr.'<tr>
						<td><em class="xing">*</em>'.$key.'��</td>
						<td><input type="text" name="'.$value['1'].'" id="city_name" style="width:600px;"><label id="city_name_info" style="margin-left:10px;"></label></td>
					</tr>';
            }
			if($value['0']=='file'){
				$htmlstr=$htmlstr.'<tr>
						<td><em class="xing">*</em>'.$key.'��</td>
						<td><input type="file" name="'.$value['1'].'" id="'.$value['1'].'" style="width:600px;"><label id="city_name_info" style="margin-left:10px;"></label></td>
					</tr>';
			}
			}
			$htmlstr=$htmlstr
					.'<tr>
						<td><em class="xing">*</em>�Ƿ���ʾ��</td>
						<td>
						<input name="rb"type="radio" checked value="1"/>��ʾ
						<input name="rb"type="radio" value="2"/>����<label id="city_name_info" style="margin-left:10px;"></label>
						</td>
					</tr>
					<tr>
						<td><em class="xing">*</em>��ʾ����</td>
						<td><input type="txt" name="ord" id="" style="width:600px;"><label id="city_name_info" style="margin-left:10px;"></label></td>
					</tr>';
			$htmlstr=$htmlstr.'</table>';
			echo $htmlstr;exit;
	}
	 public function add(){     
	       $tag1=$_POST[inputT];
           $num=M('page')->where('tag='.$tag1.' '.' AND status=1'.' '.' AND city='.'"'.$this->city.'"')->count();
            $num1=$arr=$this->klist[$tag1]['num']-1;
            if($num>$num1&&$_POST[rb]==1){
                $this->error('���������������');exit();
            }
	       $arr3=$this->klist[$_POST[inputT]][input];
            array_pop($_POST);
			$data[tag]=$_POST[inputT];
			array_pop($_POST);
			$data[ord]=$_POST[ord];
			array_pop($_POST);
			$data[status]=$_POST[rb];
			array_pop($_POST);
            //�ϴ�ͼƬ
            $po=$_POST;                                              
            foreach($arr3 as $ke=>$va){               
                if($va[0]=="file"){
                    if(!empty($_FILES[$va[1]]['name'])){
                       $po[$va[1]]=$this->upFile($va[1]);
                    }
                }            
			}
            $po=g2u($po);                       
			$jsond= json_encode($po);
			$data[content]=$jsond;
			$data[updatetime]=time();
            $data[city]=$this->city;
			M('page')->add($data);
            return $tag1; 
	 }
     public function save(){
            $id=$this->_post("edid");
            $tag1=$_POST[inputT];
            $num=M('page')->where('tag='.$tag1.' '.' AND status=1 id<>'.$id.' '.' AND city='.'"'.$this->city.'"')->count();
            $num1=$arr=$this->klist[$tag1]['num']-1;
            if($num>$num1&&$_POST[rb]==1){
                $this->error('���������������');exit();
            }
            $data=M('page')->where('id='.$id)->find();
	       $tag1=$_POST[inputT];
	       $arr3=$this->klist[$_POST[inputT]][input];
            array_pop($_POST);
			$data[tag]=$_POST[inputT];
			array_pop($_POST);
			$data[ord]=$_POST[ord];
			array_pop($_POST);
			$data[status]=$_POST[rb];
			array_pop($_POST);
            array_pop($_POST);
            //�ϴ�ͼƬ
            $po=$_POST;                                              
            foreach($arr3 as $ke=>$va){               
                if($va[0]=="file"){
                    if(!empty($_FILES[$va[1]]['name'])){
                       $po[$va[1]]=$this->upFile($va[1]);
                    }
                    else{
                        $content=(Array)json_decode($data[content]);
                        $po[$va[1]]=$content[$va[1]];
                    }
                }            
			}
             $po=g2u($po);                             
			$jsond=json_encode($po);
			$data[content]=$jsond;
			$data[updatetime]=time();
            $data[city]=$this->city;
			M('page')->where('id='.$id)->save($data); 
            return $tag1; 
	 }
	  public function show($tag){
		  $list1=M('page')->where("tag=".$tag.' '.' AND city='.'"'.$this->city.'"')->select();
          
			$htmlstr='<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table-a">
            <tr>';
            $arr=$this->klist[$tag][input];
            foreach($arr as $key=>$value){
                $htmlstr=$htmlstr.'<th>'.$key.'</th>';
			}
            $htmlstr=$htmlstr.'
                        <th>�Ƿ���ʾ</th>
                        <th>��ʾ˳��</th>
                        <th>����</th>
                    </tr>';
		foreach($list1 as $key=>$value){
            $htmlstr=$htmlstr.'<tr>';          
            $arr1 = (Array)json_decode($value[content]);
            $arr1=u2g($arr1);
            foreach($arr as $k=>$va){
                if($va['0']=='txt'){
                    $htmlstr=$htmlstr.'<td>'.$arr1[$va[1]].'</td>';  
                }
                if($va['0']=='file'){
                    $htmlstr=$htmlstr.'<td><img width="80" height="80" src="'.$arr1[$va[1]].'" /></td>';  
                }         
			}
			$htmlstr=$htmlstr.'<td>'.$value[status].'</td>';
            $htmlstr=$htmlstr.'<td>'.$value['ord'].'</td>';
			$htmlstr=$htmlstr.'<td><a href="/adminpack/index.php?s=/Page/index/ft/del/id/'.$value[id].'/tag/'.$value[tag].'">ɾ��</a> | <a href="#" onclick="edit('.$value[id].')">�༭</a></td></tr>';
		}
             $htmlstr=$htmlstr.'
                    </volist>
                </table>';
				echo $htmlstr;
				exit;
	}
    public function upFile($inputname,$filetype="image",$ImageSize=0){
        Import("ORG.Util.UploadFile");

        $uf = new UploadFile($inputname);
        $uf->setFileType($filetype);
		$uf->setMaxSize("2000");
		$uf->setUploadType("ftp");
		$uf->setSaveDir("/loan111/");
        if($ImageSize){
    		$uf->setResizeImage(true);//�Ƿ����ɵ���ͼ
    		$uf->setResizeImageSize($ImageSize);//��������ͼ��С
    		$uf->setForceResizeImage(true);//�Ƿ�ǿ�����ɵ���ͼ
        }

		$rtnMSG=$uf->upload();
        //die($rtnMSG);
        if($rtnMSG=="success"){
            $image = $uf->getSaveFileURL();
            return $image;    
        }
        return $rtnMSG;
    }
    }
?>