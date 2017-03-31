<?php
class BXCalculate {
    
    private $a;//�����ܶ�
    private $n;//��������
    private $i;//��������
    private $p;//��ǰ����
    
    //ÿ��Ӧ����a*[i*(1+i)^n]/[(1+i)^n-1]
  
    //��n���»�������B=a*i(1+i)^(n-1)/[(1+i)^N-1]
    //��n���»�����Ϣ��X=BX-B
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
    //��Ϣ
    public function getBx(){
        $a = $this->a;
        $n = $this->n;
        $i = $this->i;
        $st1 = pow(1+$i,$n);
        $st2 = $st1-1;
		if($st2==0){ return $a/$n;}
        return ($a*$i*$st1)/$st2;
    }
    //$p�ڱ���
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
     
     //$p����Ϣ
     public function getX($p){
        $a = $this->a;
        $n = $this->n;
        $i = $this->i;
        $this->p = $p;
        return $this->getBx()-$this->getB($p);
     }
     
     //$p�ں󱾽�ʣ��
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