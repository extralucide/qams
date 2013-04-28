<?php
$style_first_page = array(
					'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
						'rotation' => 90,
						'startcolor' => array('argb' => 'FFFFFFFF'),
						'endcolor' => array('argb' => 'FFFFFFFF')
					)				
);
$style_encadrement = array(
				'borders' => array(
					'top' => array(
						'style' => PHPExcel_Style_Border::BORDER_THICK,
						),
					'bottom' => array(
						'style' => PHPExcel_Style_Border::BORDER_THICK,
						),
					'left' => array(
						'style' => PHPExcel_Style_Border::BORDER_THICK,
						),
					'right' => array(
						'style' => PHPExcel_Style_Border::BORDER_THICK,
						),
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'=>true,
					'shrinkToFit'=>true,
				),
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
					'rotation' => 90,
					'startcolor' => array(
					'argb' => 'FFC5C5C5',
					),
					'endcolor' => array(
						'argb' => 'FFE8E8E8'
					)
				),			
    		);
$style_white_line = array(
				'borders' => array(
					'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					),
					'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					),
					'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
					'right' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'=>true,
					'shrinkToFit'=>true,
				),
					'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
					'rotation' => 90,
					'startcolor' => array(
					'argb' => 'F8F8F8F8',
					),
					'endcolor' => array(
						'argb' => 'FFFFFFFF',
					),
				),				
    		);
$style_white_line_prr = array(
				'borders' => array(
					'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					),
					'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					),
					'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
					'right' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'=>true,
					'shrinkToFit'=>true,
				),
					'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
					'rotation' => 90,
					'startcolor' => array(
					'argb' => 'F8F8F8F8',
					),
					'endcolor' => array(
						'argb' => 'FFFFFFFF',
					),
				),				
    		);			
$style_blank = array(
				'borders' => array(
					'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_NONE,
					),
					'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_NONE
					),
					'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_NONE,
					),
					'right' => array(
					'style' => PHPExcel_Style_Border::BORDER_NONE,
					),
				),
				'alignment' => array(
					// 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					// 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'=>true,
					'shrinkToFit'=>true,
				),
					'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
					'rotation' => 90,
					'startcolor' => array(
					'argb' => 'FFFFFFFF',
					),
					'endcolor' => array(
						'argb' => 'FFFFFFFF',
					),
				),				
    		);			
$style_test_action_open = array(
				'font' => array(
						'bold' => true,
						'color' => 'YELLOW',
					),
				'borders' => array(
					'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
					'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
					'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
					'right' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'=>true,
					'shrinkToFit'=>true,
				),
					'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'argb' => '0000FF',
				),				
    		);
  $style_table_array = array (
					'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'wrap'=>true,
				),
					'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
					'rotation' => 90,
					'startcolor' => array(
					'argb' => 'FFA0A0A0',
				),
					'endcolor' => array(
					'argb' => 'FFFFFFFF',
				),
	),

  );  
$styleArray = array(
		'font' => array(
		'bold' => false,
	),
	'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
		'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		'wrap'=>true,
		'shrinkToFit'=>true,
	),
	'borders' => array(
			'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THICK,
			'color'=> array('rgb'=>'FF000000'),
		),
	),
);

$style_title = array (
	'font' => array(
			'bold' => true,
			'name' => 'Arial',
			'size' => 16
		),
		'alignment' => array(
		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
		'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		'wrap'=>true,
		'shrinkToFit'=>true,
	),
);
$style_array = array (
					'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'wrap'=>true,
				),
);
$style_test = array(
				'borders' => array(
					'top' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
					'bottom' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
					'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
					'right' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
				),
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'=>true,
					'shrinkToFit'=>true,
				),
    		);
