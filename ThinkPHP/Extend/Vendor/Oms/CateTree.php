<?php
/**
* 创建无限级分类树和表
*/
class CateTree {
        
        //集合或数组 子分类的 最底层分类是第几层
        private $pas;
        private $table;
        private $tree;
		public $catelist;
		public $X;
		public $Y;
        
        /**
         * 返回数结构
         */
        public function getTree(){
                $this->createTree();
                return $this->tree;
        }
        
        /**
         * 返回表结构
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
         * 对数组按要求排序
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
         * 创建分类列表tree
         * @param array $catelist
         */
     /*   protected function createTree(){
                $catelist = null;// CateList::getCateList();
                $this->tree = "";//用于返回的html字符串
                $precount = 0;//获取前一条数据的count数值
                $count = 0;//获取前一条数据count数值和当前count数值的差，以确定当前数据之后的</div>的个数
                foreach($catelist as $key => $value){
                        $nbsp = "";
                        //根据count数值来缩进
                        for ($i = 2; $i < $value['count']; $i++) {
                                $nbsp .= '     ';
                        }
                        //获取前一条数据count数值和当前count数值的差$count
                        //如果precount是0，那说明是$precount默认定义的数值，也就是第一条数据，无需去获取末尾的div
                        if($precount!=0){
                                $count = $precount-$value['count'];
                        }
                        //如果相差大于0,那说明已经到最后一级子分类，需要用</div>结尾了
                        if($count>0){
                                for ($ci = 0; $ci < $count; $ci++) {
                                        //这里需要两个</div>,注解在下边
                                        $this->tree.="</div></div>";
                                }
                        }
                        if ($value['hason']) {
                                //此处为了实现jquery缩进效果，有两个开始的<div>，每增加一个子分类，就产生两个</div>
                                //而子分类里边，<div>都是封闭的，所以，没有额外的<div>产生，不需要额外的</div>来封闭
                                //因此，每产生一个子分类就产生两个<div><div>，所以上边就产生两个</div></div>
                                $this->tree .= "<div><span>{$nbsp}<abbr>+</abbr>{$value['cname']} <em><a href=#>                         }else{
                                //子分类中再没有子分类的就产生如下html代码
                                $this->tree .= "<div><span>{$nbsp}     {$value['cname']} <em><a href=#>                         }
                        //获取当前的count值作为下次循环的前一次的count值
                        $precount = $value['count'];
                        //当循环到最后一个分类的时候，获取最后的count数值，因为之后就没有分类了，所以用最后一个count的数值来判断</div>的个数
                        if($key==(count($catelist)-1)){
                                $count = $value['count'];
                                for ($ci = 0; $ci < $count; $ci++) {
                                        $this->tree.="</div></div>";
                                }
                        }
                }
        }*/
        /**
         * 创建表结构table
         * 行和列的约定：
         * 行：有多少分类是没有子类的，则有多少行  <tr>
         *     因此，每一个父类，都会根据自己下属分类中最后一级分类个数来确定合并的行数
         * 列：分类中最深一层是第几层，则有多少列，即当前分类只要有下级则创建列<td>
         *     因此，每个分类最后一级分类都会根据自己据最深层次的分类的差值来确定合并的列数
         */
        protected function createTab($array){
                $arraySize = count($array);
                $this->table .= "<table border='1'>";
                //array已经做好列表的排序工作，因此数组从上到下是包含关系，可打印数组结构查看数组结构
                //根据行的约定，父类会根据数组中son_num来确定会合并行rowspan
                //根据只要下级有分类，都会创建一个列，作为字符串存入到$tab变量中
                //直到最后一级，才会被<tr></tr>包含作为一个整体行
                $tab = "";
                for($i = 0; $i < $arraySize; $i++){
                        if($array[$i]['hason']=='0'){//如果没有子分类的情况
                                
                                //获得合并列的个数，更详细注解请看函数createCount()的注解
                                $colspan = $this->pas - $array[$i]['count']+1;
                                if($colspan!=0){
                                        $colspanHtml = " colspan=".$colspan;
                                }else{
                                        $colspanHtml = "";
                                }
                                $this->table .="<tr>".$tab."<td".$colspanHtml.">".$array[$i]['NAME'] ."</td></tr>";
                                $tab="";
                        }else{//有子分类的情况
                                
                                //通过子分类个数来确定合并行数
                                $tab.="<td rowspan=".$array[$i]['son_num'].">".$array[$i]['NAME'] ."</td>";
                        }
                } 
                $this->table .= "</table>";
        }
		/*
		*生成tab的表头
		*/
		 protected function createTabth($array){
                $arraySize = count($array);
                $this->table .= "<table border='1'>";
                //array已经做好列表的排序工作，因此数组从上到下是包含关系，可打印数组结构查看数组结构
                //根据行的约定，父类会根据数组中son_num来确定会合并行rowspan
                //根据只要下级有分类，都会创建一个列，作为字符串存入到$tab变量中
                //直到最后一级，才会被<tr></tr>包含作为一个整体行
                
                for($i = 1; $i <= $this->pas; $i++){
					$tab = "";
						 for($ii = 0; $ii < $arraySize; $ii++){
								 $colspan = $rowspan = 0;
							
								if($array[$ii]['count'] == $i){
										//获得合并列的个数，更详细注解请看函数createCount()的注解
										$colspan =  $array[$ii]['son_num'] ;
										if($colspan!=0){
											$colspanHtml = " colspan=".$colspan;
										}else{
											$colspanHtml = "";
										}
										

										if($array[$ii]['hason']=='0'){//如果没有子分类的情况
											 //获得合并行的个数 
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
         * 检查数组元素是否齐全
         * 数组元素除了数据库字段，还需要包含以下字段
         * hason：是否有下级分了(如果数据库没有);
         * count：路径分割成数组后的长度
         * son_num：下级子栏目的个数
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
         * 如果数据库没有hason字段，则增加创建hason字段
         * @param $array
         * @param $size
         * @param $i
         */
        protected function createHason($array,$size,$i){
                
                //首先需要判断是否有子栏目，重新构建数组或集合
                for($j = 0; $j < $size; $j++){
                        if($array[$i]['cid'] == $array[$j]['fid']){
                                //如果有子栏目，则给数组或集合添加hason = 1;
                                $newArr[$i] = $array[$i];
                                $newArr[$i]['hason'] = "1";
                                break;
                        }else if($j == ($size-1)){
                                //如果没有子栏目，则给数组或集合添加hason = 0;
                                $newArr[$i] = $array[$i];
                                $newArr[$i]['hason'] = "0";
                        }
                }
                return $newArr[$i];
        }
        /**
         * 获取路径长度
         +--------------------------------------------------------------------------------------+
         * 此路径长度规则是"上级分类个数+本类+根目录"，在数组元素中表现为tpath元素值，请自行打印观察
         * 因为所有分类均属于根目录，并没有和根目录同层次的分类，所以，所有路径的长度默认加上根目录
         +--------------------------------------------------------------------------------------+
         * 即包含上级分类、本类以及根目录在内的所有分类个数，如果上级有两层，包含自己
         * 则count值应该是"上级分类个数+本类+根目录"即1+2+1=4
         +--------------------------------------------------------------------------------------+
         * 在建立table表格的显示形式的时候，需要计算合并单元格的个数
         * 如果所有分类最深一级是5级，count(加上根目录后)应为6，
         * 而自己上级有两层，所以当前分类(第三层)据最后一层为6-4=2
         * 即当前分类后会有2个空余单元格需要合并，包括单前分类的单元格，共需要合并3个单元格
         +--------------------------------------------------------------------------------------+
         * 在程序中$this->pas是最深一层
         * 因此在合并单元格的时候，需要合并自己后边的
         * @param $path
         */
        protected function createCount($path){
                $pathLength = $this->getPathLength();
                $this->setPas($pathLength);
                return $pathLength;
        }
        /**
         * 设置pas值，保存tree结构中最深的一层
         * @param $pathLength
         */
        protected function setPas($pathLength){
                if (empty($this->pas))
                        $this->pas = 0;
                //获取pathArr数组或集合的长度，如果数组或集合size不大于上次，则保持不变，
                //如果大于上次size，则保存当前数组或集合size
                if ($this->pas<$pathLength)
                        $this->pas = $pathLength;
        }
        /**
         * 获取$path的长度
         * @param $path
         */
        protected function getPathLength($path){
                $pathArr = explode('-',$path);
                $pathLength = count($pathArr);
                return $pathLength+1;
        }
        
        /**
         * 获取子分类的个数，确定合并单元格的个数
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