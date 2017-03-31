<?php
class HelpAction extends  ExtendAction{
		
		public function sort(){
                $w['hs_city'] = $this->city;
				$sortList = M("help_sort")->where($w)->order("hs_ord desc")->select();
               
				$this->assign("sortList",$sortList);
				$this->display();
			}
			
		public function editSort(){
				$act = $this->_request("act");
				$id = $this->_request("id","intval");
				switch($act){
						case 'add'://新增
							$ss['hs_name'] = $this->_post("name");
                            $ss['hs_ord'] = time();
							$ss['hs_city'] = $this->city;
							if($ss['hs_name']=="")
								$this->error("请填写分类名称");
							M("Help_sort")->add($ss);
							//echo M('Help_sort')->getLastSql();
							$this->success("新增成功");
						break;
						case 'edit':
							$mod = $this->_request("mod");
							!$id && $this->error("访问错误"); 
							$w['hs_id'] = $id;
							if($mod=="edit"){
									//更新操作
									$ss['hs_name'] = $this->_post("name");
									M("Help_sort")->where($w)->save($ss);
									$this->success("编辑成功");exit;
							}
							
							$info = M("Help_sort")->where($w)->find();
                            $info = g2u($info);
							$this->ajaxReturn(json_encode($info),"",1);
						break;
						case 'ord'://排序
							$mod = $this->_get("mod");
							!$id && $this->error("访问错误");
							$w['hs_id'] = $id;
							$info = M("Help_sort")->where($w)->find();unset($w);
							if($mod=="up"){
									$w['hs_ord'] = array("GT",$info['hs_ord']);
									$order = "hs_ord asc";
							}else{
									$w['hs_ord'] = array("LT",$info['hs_ord']);
									$order = "hs_ord desc";
								}
							$target = M("Help_sort")->where($w)->order($order)->find();unset($w);
							if($target){
								$w['hs_id'] = $info['hs_id'];
								$s['hs_ord'] = $target['hs_ord'];
								M("Help_sort")->where($w)->save($s);
								$w['hs_id'] = $target['hs_id'];
								$s['hs_ord'] = $info['hs_ord'];
								M("Help_sort")->where($w)->save($s);
							}
							$this->redirect("Help/sort");
						break;
                        case 'del':
                            $w['hs_id'] = $id;
                            M("Help_sort")->where($w)->delete();
                            $this->success("删除成功");exit;
                        break;
					}
			}
			
		public function helpList(){
                Import("ORG.Util.Page");
                $w['h_city'] = $this->city;
                $count = M("Help")->where($w)->count();
                $page = new Page($count,30);
                $helpList = M("Help")->where($w)->order("h_ord desc")->limit($page->firstRow,$page->listRows)->select();
                foreach($helpList as $k=>$v){
                    $sortInfo = M("Help_sort")->where("hs_id=".$v['h_sortid'])->find();
                    $sortName = $sortInfo['hs_name'];
                    !$sortName && $sortName="<span style='color:red'>未命名</span>";
                    $helpList[$k]['sortName'] = $sortName;
                }
				$this->assign("page",$page->show());
                $this->assign("helpList",$helpList);
                $this->display();
			}
            
        public function helpEdit(){
            $act = $this->_request("act","","add");
            $mod = $this->_request("mod");
			$id = $this->_request("id","intval");
			switch($act){
					case 'add'://新增
                        if($mod=="add"){
                        	$w = array();
                        	$w['h_city'] = $this->city;
                        	$w['h_sortid'] = $this->_post("sortid");
                        	$helpList = M("Help")->where($w)->order("h_ord asc")->find();
                            
    						$ss['h_title'] = $this->_post("title");
                            $ss['h_sortid'] = $this->_post("sortid");
                            $ss['h_content'] = $this->_post("hcontent");
    						$ss['h_ord'] = $helpList['h_ord']-1;
                            $ss['h_city'] = $this->city;
    						if($ss['h_title']=="")
    							$this->error .= "--------请填写名称\\r\\n";
                            if($ss['h_sortid']=="")
    							$this->error .= "--------请选择分类\\r\\n";
                            if($ss['h_content']=="")
    							$this->error .= "--------请填写内容\\r\\n";
                            if($this->error){
                                js_alert("有以下错误:\\r\\n".$this->error,"",1);
                                die();
                            }
                            
    						M("Help")->add($ss);
    						js_alert("新增成功",U("Help/helpList"),1);exit;
                        }
                        $this->assign("sortSelectOption",$this->sortSelectOption());
                        $this->display();
					break;
					case 'edit':
						
						!$id && $this->error("访问错误"); 
						$w['h_id'] = $id;
						if($mod=="edit"){
								//更新操作
								$ss['h_title'] = $this->_post("title");
                                $ss['h_sortid'] = $this->_post("sortid");
                                $ss['h_content'] = $this->_post("hcontent");
        						
        						if($ss['h_title']=="")
        							$this->error .= "--------请填写名称\\r\\n";
                                if($ss['h_sortid']=="")
        							$this->error .= "--------请选择分类\\r\\n";
                                if($ss['h_content']=="")
        							$this->error .= "--------请填写内容\\r\\n";
                                if($this->error){
                                    js_alert("有以下错误:\\r\\n".$this->error,"",1);
                                    die();
                                }
								M("Help")->where($w)->save($ss);
								js_alert("编辑成功",U("Help/helpList"),1);exit;
						}
						
						$info = M("Help")->where($w)->find();
                        
                        $this->assign("info",$info);
                        $this->assign("act","edit");
                        $this->assign("mod","edit");
                        $this->assign("id",$id);
                        $this->assign("sortSelectOption",$this->sortSelectOption($info['h_sortid']));
                        $this->display();
					break;
					case 'ord'://排序
						$mod = $this->_get("mod");
						!$id && $this->error("访问错误");
						$w['h_id'] = $id;
						$info = M("Help")->where($w)->find();unset($w);
						if($mod=="up"){
								$w['h_ord'] = array("GT",$info['h_ord']);
								$order = "h_ord asc";
						}else{
								$w['h_ord'] = array("LT",$info['h_ord']);
								$order = "h_ord desc";
							}
						$target = M("Help")->where($w)->order($order)->find();unset($w);
						if($target){
							$w['h_id'] = $info['h_id'];
							$s['h_ord'] = $target['h_ord'];
							M("Help")->where($w)->save($s);
							$w['h_id'] = $target['h_id'];
							$s['h_ord'] = $info['h_ord'];
							M("Help")->where($w)->save($s);
						}
						$this->redirect("Help/helpList");
					break;
                    case 'del':
                        $w['h_id'] = $id;
                        M("Help")->where($w)->delete();
                        $this->success("删除成功");exit;
                    break;
				}
        }
        
        private function sortSelectOption($val=""){
            $w['hs_city'] = $this->city;
            $re = M("Help_sort")->where($w)->order("hs_ord desc")->select();
            if($re){
                foreach($re as $k=>$v){
                    if($val==$v['hs_id'])
                        $selected = "selected";
                    else
                        $selected = "";
                    $option .= "<option value='".$v['hs_id']."' ".$selected.">".$v['hs_name']."</option>";
                }
            }
            
           
            return $option;
        }
	}
?>