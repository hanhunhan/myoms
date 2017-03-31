<?php
class BXCalculate {
    
    private $a;//贷款总额
    private $n;//贷款期数
    private $i;//贷款利率
    private $p;//当前期数
    
    //每月应还金额：a*[i*(1+i)^n]/[(1+i)^n-1]
  
    //第n个月还贷本金：B=a*i(1+i)^(n-1)/[(1+i)^N-1]
    //第n个月还贷利息：X=BX-B
    public function getBxById(){

		/*$this->a = $re['quota'];
		$this->n = $re['periods'];
		$this->i = $re['rates'];*/
		//$this->a = 10000;
		// $this->n=20*12;
		// $this->i = 4.95/1000;
		for($i=1;$i<=$this->n;$i++){
			$b[$i]['bx'] = $this->round($this->getBx());
			$b[$i]['b'] = $this->round($this->getB($i));
			$b[$i]['x'] = $this->round($this->getX($i));
			$b[$i]['remainb'] = $this->round($this->getRemainB($i));
		}
	   return $b;
       
    }
    public function setA($val){
		$this->a = $val;
	}
	public function setN($val){
		$this->n = $val;
	}
	public function setI($val){
		$this->i = $val/100;
	}
	public function setP($val){
		$this->p = $val;
	}
    //本息
    public function getBx(){
        $a = $this->a;
        $n = $this->n;
        $i = $this->i;
        $st1 = pow(1+$i,$n);
        $st2 = $st1-1;
		if($st2==0){ return $a/$n;}
        return ($a*$i*$st1)/$st2;
    }
    //$p期本金
     public function getB($p){
        $a = $this->a;
        $n = $this->n;
        $i = $this->i;
        $this->p = $p;
        $st1= pow(1+$i,$p-1);
        $st2 = pow(1+$i,$n)-1;
		if($st2==0){ return $a/$n;}
        return ($a*$i*$st1)/$st2;
     }
     
     //$p期利息
     public function getX($p){
        $a = $this->a;
        $n = $this->n;
        $i = $this->i;
        $this->p = $p;
        return $this->getBx()-$this->getB($p);
     }
     
     //$p期后本金剩余
      public function getRemainB($p){
		$r = '';
        for($i=1;$i<=$p;$i++){
            $r += $this->getB($i);
        }
        return $this->a-$r;
      }
      
      private function round($q,$n=2){
        $r= round($q,$n);
		return $r;
        //return number_format($r,2,".",",");
      }
      
}
?>