<?php
/**
 * ?????
 * @author  xiaojiong & 290747680@qq.com
 * @date 2011-08-17
 * 
 * show(2)  1 ... 62 63 64 65 66 67 68 ... 150
 * ?????? 
 * #page{font:12px/16px arial}
 * #page span{float:left;margin:0px 3px;}
 * #page a{float:left;margin:0 3px;border:1px solid #ddd;padding:3px 7px; text-decoration:none;color:#666}
 * #page a.now_page,#page a:hover{color:#fff;background:#05c}
*/

class Core_Lib_Page
{
	public     $first_row; 		  //???????

	public     $list_rows;  	  //?งา??????????
	
	protected  $total_pages;	  //?????

	protected  $total_rows;		  //??????
	
	protected  $now_page;         //??????
	
	protected  $method  = 'defalut'; //??????? Ajax??? Html???(??????) ???get??? 
	
	protected  $parameter = '';
	
	protected  $page_name;        //?????????????
	
	protected  $ajax_func_name;
	
	public 	   $plus = 3;         //????????
	
	protected  $url;
	
	
	/**
	 * ??????
	 * @param unknown_type $data
	 */
	public function __construct($data = array())
	{
		$this->total_rows = $data['total_rows'];

		$this->parameter 	    = !empty($data['parameter']) ? $data['parameter'] : '';
		$this->list_rows 		= !empty($data['list_rows']) && $data['list_rows'] <= 100 ? $data['list_rows'] : 15;
		$this->total_pages		= ceil($this->total_rows / $this->list_rows);
		$this->page_name  		= !empty($data['page_name']) ? $data['page_name'] : 'p';
		$this->ajax_func_name	= !empty($data['ajax_func_name']) ? $data['ajax_func_name'] : '';
		
		$this->method           = !empty($data['method']) ? $data['method'] : '';
		
		
		/* ?????? */
		if(!empty($data['now_page']))
		{
			$this->now_page = intval($data['now_page']);
		}else{
			$this->now_page   = !empty($_GET[$this->page_name]) ? intval($_GET[$this->page_name]):1;
		}
		$this->now_page   = $this->now_page <= 0 ? 1 : $this->now_page;
	
		
		if(!empty($this->total_pages) && $this->now_page > $this->total_pages)
		{
			$this->now_page = $this->total_pages;
		}
		$this->first_row = $this->list_rows * ($this->now_page - 1);
	}	
	
	/**
	 * ??????????
	 * @param $page
	 * @param $text
	 * @return string
	 */
	protected function _get_link($page,$text)
	{
		switch ($this->method) {
			case 'ajax':
				$parameter = '';
				if($this->parameter)
				{
					$parameter = ','.$this->parameter;
				}
				return '<a onclick="' . $this->ajax_func_name . '(\'' . $page . '\''.$parameter.')" href="javascript:void(0)">' . $text . '</a>' . "\n";
			break;
			
			case 'html':
				$url = str_replace('[[pagelist]]', $page,$this->parameter);
				return '<a href="' .$url . '">' . $text . '</a>' . "\n";
			break;
			
			default:
				return '<a href="' . $this->_get_url($page) . '">' . $text . '</a>' . "\n";
			break;
		}
	}
	
    
    /**
     * ?????????????
     */
    protected function _set_url()
    {
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter;
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$this->page_name]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        if(!empty($params))
        {
        	$url .= '&';
        }
        $this->url = $url;
    }
	
    /**
     * ???$page??url
     * @param $page ???
     * @return string
     */
    protected function _get_url($page)
    {
    	if($this->url === NULL)
    	{
    		$this->_set_url();	
    	}
  	//	$lable = strpos('&', $this->url) === FALSE ? '' : '&';
    	return $this->url . $this->page_name . '=' . $page;
    }
    
    
    /**
     * ???????
     * @return string
     */
    public function first_page($name = '????')
    {
 		if($this->now_page > 5)
 		{
 			return $this->_get_link('1', $name);
 		}	
 		return '';
    }
    
    /**
     * ?????
     * @param $name
     * @return string
     */
    public function last_page($name = '?????')
    {
 		if($this->now_page < $this->total_pages - 5)
 		{
 			return $this->_get_link($this->total_pages, $name);
 		}	
 		return '';
    }  
	
    /**
     * ????
     * @return string
     */
    public function up_page($name = '<????')
    {
    	if($this->now_page != 1)
    	{
    		return $this->_get_link($this->now_page - 1, $name);
    	}
    	return '';
    }
	
    /**
     * ????
     * @return string
     */
    public function down_page($name = '????>')
    {
    	if($this->now_page < $this->total_pages)
    	{
    		return $this->_get_link($this->now_page + 1, $name);
    	}
    	return '';
    }

    /**
     * ?????????
     * @param $param
     * @return string
     */
    public function show($param = 1)
    {
    	if($this->total_rows < 1)
    	{
    		return '';
    	}
    	
    	$className = 'show_' . $param;
    	
    	$classNames = get_class_methods($this);

    	if(in_array($className, $classNames))
    	{
    		return $this->$className();
    	}
  		return '';
    }
    
    protected function show_2()
    {
        if($this->total_pages != 1)
    	{
    		$return = '';
    		$return .= $this->up_page('<');
			for($i = 1;$i<=$this->total_pages;$i++)
			{
				if($i == $this->now_page)
				{
					$return .= "<a class='now_page'>$i</a>\n";
				}
				else
				{
					if($this->now_page-$i>=4 && $i != 1)
					{
						$return .="<span class='pageMore'>...</span>\n";
						$i = $this->now_page-3;
					}
					else
					{
						if($i >= $this->now_page+5 && $i != $this->total_pages)
						{
							$return .="<span>...</span>\n"; 
							$i = $this->total_pages;
						}
						$return .= $this->_get_link($i, $i) . "\n";
					}
				}
			}
			$return .= $this->down_page('>');
    		return $return;
    	}
    }
    
    protected function show_1()
    {
		$plus = $this->plus;
    	if( $plus + $this->now_page > $this->total_pages)
    	{
    		$begin = $this->total_pages - $plus * 2;
    	}else{
    		$begin = $this->now_page - $plus;
    	}
    	
    	$begin = ($begin >= 1) ? $begin : 1;
    	$return = '';
    	$return .= $this->first_page();
    	$return .= $this->up_page();
    	for ($i = $begin; $i <= $begin + $plus * 2;$i++)
    	{
    		if($i>$this->total_pages)
    		{
    			break;
    		}
    		if($i == $this->now_page)
    		{
    			$return .= "<a class='now_page'>$i</a>\n";
    		}
    		else
    		{
    			$return .= $this->_get_link($i, $i) . "\n";
    		}
    	}
    	$return .= $this->down_page();
    	$return .= $this->last_page();
    	return $return;
    }
    
    protected function show_3()
    {
    	$plus = $this->plus;
    	if( $plus + $this->now_page > $this->total_pages)
    	{
    		$begin = $this->total_pages - $plus * 2;
    	}else{
    		$begin = $this->now_page - $plus;
    	}    	
    	$begin = ($begin >= 1) ? $begin : 1;
    	$return = '??? ' .$this->total_rows. ' ???????? ' .$this->total_pages. ' ?, ????? ' . $this->now_page . ' ? ';
    	$return .= ',?? ';
    	$return .= '<input type="text" value="'.$this->list_rows.'" id="pageSize" size="3"> ';
    	$return .= $this->first_page()."\n";
    	$return .= $this->up_page()."\n"; 
    	$return .= $this->down_page()."\n";
    	$return .= $this->last_page()."\n";
    	$return .= '<select onchange="'.$this->ajax_func_name.'(this.value)" id="gotoPage">';
       
        for ($i = $begin;$i<=$begin+10;$i++)
        {
            if($i>$this->total_pages)
    		{
    			break;
    		}        	
    		if($i == $this->now_page)
    		{
    			$return .= '<option selected="true" value="'.$i.'">'.$i.'</option>';
    		}
    		else
    		{
    			$return .= '<option value="' .$i. '">' .$i. '</option>';
    		}        	
        }
    	 $return .= '</select>';
    	return $return;
    }
}

