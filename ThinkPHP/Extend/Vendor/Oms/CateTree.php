<?php
/**
* �������޼��������ͱ�
*/
class CateTree {
        
        //���ϻ����� �ӷ���� ��ײ�����ǵڼ���
        private $pas;
        private $table;
        private $tree;
		public $catelist;
		public $X;
		public $Y;
        
        /**
         * �������ṹ
         */
        public function getTree(){
                $this->createTree();
                return $this->tree;
        }
        
        /**
         * ���ر�ṹ
         */
        public function getTable(){
                //$catelist = $this->getCateList();
                $x= $this->checkArrayElements($this->X );
				$y = $this->checkArrayElements($this->Y );
                $this->createTabth($x);   // var_dump($newArr);
				$this->createTab($y);	
                return $this->table;
        }
        /**
         * �����鰴Ҫ������
         */
		public function getCatelist($data ,$parentId=0,$count=1){
			if($data){
					foreach($data as $key=>$val){ 
						$id =current($val);
						next($val); 
						$pid = next($val);
						$val['cid'] = $id;
						$val['fid'] = $pid;
						if($pid == $parentId) {
							$val['count'] = $count;
							
							$this->TreeData[] = $val;
							unset($data[$key]);
							$this->getCatelist($data,$id,$count+1);
						}
					}
			
			} 
			return $this->TreeData;
		}
        /**
         * ���������б�tree
         * @param array $catelist
         */
     /*   protected function createTree(){
                $catelist = null;// CateList::getCateList();
                $this->tree = "";//���ڷ��ص�html�ַ���
                $precount = 0;//��ȡǰһ�����ݵ�count��ֵ
                $count = 0;//��ȡǰһ������count��ֵ�͵�ǰcount��ֵ�Ĳ��ȷ����ǰ����֮���</div>�ĸ���
                foreach($catelist as $key => $value){
                        $nbsp = "";
                        //����count��ֵ������
                        for ($i = 2; $i < $value['count']; $i++) {
                                $nbsp .= '     ';
                        }
                        //��ȡǰһ������count��ֵ�͵�ǰcount��ֵ�Ĳ�$count
                        //���precount��0����˵����$precountĬ�϶������ֵ��Ҳ���ǵ�һ�����ݣ�����ȥ��ȡĩβ��div
                        if($precount!=0){
                                $count = $precount-$value['count'];
                        }
                        //���������0,��˵���Ѿ������һ���ӷ��࣬��Ҫ��</div>��β��
                        if($count>0){
                                for ($ci = 0; $ci < $count; $ci++) {
                                        //������Ҫ����</div>,ע�����±�
                                        $this->tree.="</div></div>";
                                }
                        }
                        if ($value['hason']) {
                                //�˴�Ϊ��ʵ��jquery����Ч������������ʼ��<div>��ÿ����һ���ӷ��࣬�Ͳ�������</div>
                                //���ӷ�����ߣ�<div>���Ƿ�յģ����ԣ�û�ж����<div>����������Ҫ�����</div>�����
                                //��ˣ�ÿ����һ���ӷ���Ͳ�������<div><div>�������ϱ߾Ͳ�������</div></div>
                                $this->tree .= "<div><span>{$nbsp}<abbr>+</abbr>{$value['cname']} <em><a href=#>                         }else{
                                //�ӷ�������û���ӷ���ľͲ�������html����
                                $this->tree .= "<div><span>{$nbsp}     {$value['cname']} <em><a href=#>                         }
                        //��ȡ��ǰ��countֵ��Ϊ�´�ѭ����ǰһ�ε�countֵ
                        $precount = $value['count'];
                        //��ѭ�������һ�������ʱ�򣬻�ȡ����count��ֵ����Ϊ֮���û�з����ˣ����������һ��count����ֵ���ж�</div>�ĸ���
                        if($key==(count($catelist)-1)){
                                $count = $value['count'];
                                for ($ci = 0; $ci < $count; $ci++) {
                                        $this->tree.="</div></div>";
                                }
                        }
                }
        }*/
        /**
         * ������ṹtable
         * �к��е�Լ����
         * �У��ж��ٷ�����û������ģ����ж�����  <tr>
         *     ��ˣ�ÿһ�����࣬��������Լ��������������һ�����������ȷ���ϲ�������
         * �У�����������һ���ǵڼ��㣬���ж����У�����ǰ����ֻҪ���¼��򴴽���<td>
         *     ��ˣ�ÿ���������һ�����඼������Լ��������εķ���Ĳ�ֵ��ȷ���ϲ�������
         */
        protected function createTab($array){
                $arraySize = count($array);
                $this->table .= "<table border='1'>";
                //array�Ѿ������б�������������������ϵ����ǰ�����ϵ���ɴ�ӡ����ṹ�鿴����ṹ
                //�����е�Լ������������������son_num��ȷ����ϲ���rowspan
                //����ֻҪ�¼��з��࣬���ᴴ��һ���У���Ϊ�ַ������뵽$tab������
                //ֱ�����һ�����Żᱻ<tr></tr>������Ϊһ��������
                $tab = "";
                for($i = 0; $i < $arraySize; $i++){
                        if($array[$i]['hason']=='0'){//���û���ӷ�������
                                
                                //��úϲ��еĸ���������ϸע���뿴����createCount()��ע��
                                $colspan = $this->pas - $array[$i]['count']+1;
                                if($colspan!=0){
                                        $colspanHtml = " colspan=".$colspan;
                                }else{
                                        $colspanHtml = "";
                                }
                                $this->table .="<tr>".$tab."<td".$colspanHtml.">".$array[$i]['NAME'] ."</td></tr>";
                                $tab="";
                        }else{//���ӷ�������
                                
                                //ͨ���ӷ��������ȷ���ϲ�����
                                $tab.="<td rowspan=".$array[$i]['son_num'].">".$array[$i]['NAME'] ."</td>";
                        }
                } 
                $this->table .= "</table>";
        }
		/*
		*����tab�ı�ͷ
		*/
		 protected function createTabth($array){
                $arraySize = count($array);
                $this->table .= "<table border='1'>";
                //array�Ѿ������б�������������������ϵ����ǰ�����ϵ���ɴ�ӡ����ṹ�鿴����ṹ
                //�����е�Լ������������������son_num��ȷ����ϲ���rowspan
                //����ֻҪ�¼��з��࣬���ᴴ��һ���У���Ϊ�ַ������뵽$tab������
                //ֱ�����һ�����Żᱻ<tr></tr>������Ϊһ��������
                
                for($i = 1; $i <= $this->pas; $i++){
					$tab = "";
						 for($ii = 0; $ii < $arraySize; $ii++){
								 $colspan = $rowspan = 0;
							
								if($array[$ii]['count'] == $i){
										//��úϲ��еĸ���������ϸע���뿴����createCount()��ע��
										$colspan =  $array[$ii]['son_num'] ;
										if($colspan!=0){
											$colspanHtml = " colspan=".$colspan;
										}else{
											$colspanHtml = "";
										}
										

										if($array[$ii]['hason']=='0'){//���û���ӷ�������
											 //��úϲ��еĸ��� 
											$rowspan = $this->pas - $array[$ii]['count']+1;
											if($rowspan!=0){
												$rowspanHtml = " rowspan=".$rowspan;
											}else{
												$rowspanHtml = "";
											}
											$tab .= "<td $rowspanHtml >".$array[$ii]['NAME'] ."</td>";
										}else{
											 $tab .= "<td $colspanHtml >".$array[$ii]['NAME'] ."</td>";
										}

								
								 }
							}
					

						 $this->table .="<tr>".$tab."</tr>";
                } 
                $this->table .= "</table>";
        }
        
        
        /**
         * �������Ԫ���Ƿ���ȫ
         * ����Ԫ�س������ݿ��ֶΣ�����Ҫ���������ֶ�
         * hason���Ƿ����¼�����(������ݿ�û��);
         * count��·���ָ�������ĳ���
         * son_num���¼�����Ŀ�ĸ���
         * 
         */
        protected function checkArrayElements($array){
                
                $arrSize = count($array);
                
                for($i = 0 ; $i <$arrSize; $i++){
                        if (!isset($array[$i]['hason'])){
                                $array[$i] = $this->createHason($array,$arrSize,$i);
                        }
                        
                }
				for($i = 0 ; $i <$arrSize; $i++){
                        
                        if(!isset($array[$i]['son_num'])){
                                $array[$i]['son_num'] = $this->createSonNum($array,$array[$i]['cid']);
                        }
                         
                }
				for($i = 0 ; $i <$arrSize; $i++){
                        
                        if(!isset($array[$i]['count'])){
                               //$array[$i]['count'] = $this->createCount($array[$i]['path']);
                        }else{
                               // $this->setPas($this->getPathLength($array[$i]['path']));
							   $this->setPas($array[$i]['count']);
                        }
                }
                return $array;
                
        }
        
        /**
         * ������ݿ�û��hason�ֶΣ������Ӵ���hason�ֶ�
         * @param $array
         * @param $size
         * @param $i
         */
        protected function createHason($array,$size,$i){
                
                //������Ҫ�ж��Ƿ�������Ŀ�����¹�������򼯺�
                for($j = 0; $j < $size; $j++){
                        if($array[$i]['cid'] == $array[$j]['fid']){
                                //���������Ŀ���������򼯺����hason = 1;
                                $newArr[$i] = $array[$i];
                                $newArr[$i]['hason'] = "1";
                                break;
                        }else if($j == ($size-1)){
                                //���û������Ŀ���������򼯺����hason = 0;
                                $newArr[$i] = $array[$i];
                                $newArr[$i]['hason'] = "0";
                        }
                }
                return $newArr[$i];
        }
        /**
         * ��ȡ·������
         +--------------------------------------------------------------------------------------+
         * ��·�����ȹ�����"�ϼ��������+����+��Ŀ¼"��������Ԫ���б���ΪtpathԪ��ֵ�������д�ӡ�۲�
         * ��Ϊ���з�������ڸ�Ŀ¼����û�к͸�Ŀ¼ͬ��εķ��࣬���ԣ�����·���ĳ���Ĭ�ϼ��ϸ�Ŀ¼
         +--------------------------------------------------------------------------------------+
         * �������ϼ����ࡢ�����Լ���Ŀ¼���ڵ����з������������ϼ������㣬�����Լ�
         * ��countֵӦ����"�ϼ��������+����+��Ŀ¼"��1+2+1=4
         +--------------------------------------------------------------------------------------+
         * �ڽ���table������ʾ��ʽ��ʱ����Ҫ����ϲ���Ԫ��ĸ���
         * ������з�������һ����5����count(���ϸ�Ŀ¼��)ӦΪ6��
         * ���Լ��ϼ������㣬���Ե�ǰ����(������)�����һ��Ϊ6-4=2
         * ����ǰ��������2�����൥Ԫ����Ҫ�ϲ���������ǰ����ĵ�Ԫ�񣬹���Ҫ�ϲ�3����Ԫ��
         +--------------------------------------------------------------------------------------+
         * �ڳ�����$this->pas������һ��
         * ����ںϲ���Ԫ���ʱ����Ҫ�ϲ��Լ���ߵ�
         * @param $path
         */
        protected function createCount($path){
                $pathLength = $this->getPathLength();
                $this->setPas($pathLength);
                return $pathLength;
        }
        /**
         * ����pasֵ������tree�ṹ�������һ��
         * @param $pathLength
         */
        protected function setPas($pathLength){
                if (empty($this->pas))
                        $this->pas = 0;
                //��ȡpathArr����򼯺ϵĳ��ȣ��������򼯺�size�������ϴΣ��򱣳ֲ��䣬
                //��������ϴ�size���򱣴浱ǰ����򼯺�size
                if ($this->pas<$pathLength)
                        $this->pas = $pathLength;
        }
        /**
         * ��ȡ$path�ĳ���
         * @param $path
         */
        protected function getPathLength($path){
                $pathArr = explode('-',$path);
                $pathLength = count($pathArr);
                return $pathLength+1;
        }
        
        /**
         * ��ȡ�ӷ���ĸ�����ȷ���ϲ���Ԫ��ĸ���
         * @param $array
         * @param $cid
         */
        protected function createSonNum($array,$cid){
                $sonNum = 0;
                $size = count($array);
                for($i = 0; $i < $size; $i++){
                        if($cid==$array[$i]['fid']){  
                                if($array[$i]['hason']=="0"){
                                        $sonNum++;
                                }
                                $sonNum+=$this->createSonNum($array,$array[$i]['cid']);
                        }
                }
                return $sonNum;
        }
}	 