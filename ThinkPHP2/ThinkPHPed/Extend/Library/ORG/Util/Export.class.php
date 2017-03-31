<?php
	class Export {
		public function excelExport($tabhead,$tabcolum,$result,$filename) {
		Vendor('PhpExcel.PHPExcel');
        // Create new PHPExcel object  
        $objPHPExcel = new PHPExcel();  

		
        // Set properties  
		//print_r(toUtf8($result));
       /*$objPHPExcel->getProperties()->setCreator("Yao")  
            ->setLastModifiedBy("Yao")  
            ->setTitle("Office 2007 XLSX Test Document")  
            ->setSubject("Office 2007 XLSX Test Document")  
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")  
            ->setKeywords("office 2007 openxml php")  
            ->setCategory("Test result file"); */
		for($i=0;$i<count($tabhead);$i++){
			$objPHPExcel->getActiveSheet()->getColumnDimension(chr($i+65))->setWidth(10);
		}
		//?????§Ú?
		$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);
		 //set font size bold  
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);
		//????§á?? A1:F1
		$wlen=chr(65)."1:".chr(65+count($tabhead)-1)."1";
        $objPHPExcel->getActiveSheet()->getStyle($wlen)->getFont()->setBold(true);  
  
        $objPHPExcel->getActiveSheet()->getStyle($wlen)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
        $objPHPExcel->getActiveSheet()->getStyle($wlen)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
  
        //$objPHPExcel->getActiveSheet()->mergeCells('A1:F1');  ????????
  
        // ??????
		for($j=0;$j<count($tabhead);$j++){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(65+$j).'1', toUtf8($tabhead[$j]));
		}

		$j = 1;
		foreach($result as $v){
			
			//$aa=$col;
			$j++;
			
			$k=0;
			
			//$objPHPExcel->getActiveSheet(0)->setCellValue(chr(65+$k).($k+2), $v[$aa]);

			//$objPHPExcel->getActiveSheet(0)->getStyle('A'.($k+2).':'.chr(65+count($tabhead)-1).($k+2))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
			//$objPHPExcel->getActiveSheet(0)->getStyle('A'.($k+2).':'.chr(65+count($tabhead)-1).($k+2))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
			//$objPHPExcel->getActiveSheet(0)->getRowDimension($k+2)->setRowHeight(16);
			foreach($tabcolum as $col){
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(65+$k).$j, toUtf8($v[$col]));
				$k++;
			}

			
		}

		
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet  
        $objPHPExcel->setActiveSheetIndex(0);  
		
        // Rename sheet  
		//$f= $objPHPExcel->getActiveSheet();

		
        $objPHPExcel->getActiveSheet()->setTitle('?????????');  
  

        // Redirect output to a client??s web browser (Excel5)  
        header('Content-Type: application/vnd.ms-excel,charset=utf-8');  
        header('Content-Disposition: attachment;filename='.$filename.date('Ymd-His').'.xls');  
        header('Cache-Control: max-age=0');  
  
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  

		
        $objWriter->save('php://output');
	}
	}
?>