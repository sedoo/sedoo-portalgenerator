<?php 
require_once ('utils/phpwkhtmltopdf/WkHtmlToPdf.php');
require_once ('scripts/editDataset.php');
require_once ('conf/conf.php');

$root = $_SERVER['DOCUMENT_ROOT'];

function fiche2pdf_new($datsId){
	global $project_name;
	$content = null;
	$content .= getDatasetInfos($datsId, $project_name);
	if (isset ( $datsId ) && ! empty ( $datsId ))
		$dataset = dataset_factory::createDatasetById ( $datsId );
	genPDF($content,$dataset->dats_title);
}

function genPDF($content,$fileTitle){
	global $project_name,$root;
	ob_end_clean();
	$stylesheet = file_get_contents('css/layout_text.css',FILE_USE_INCLUDE_PATH);
	$stylesheet .= file_get_contents('css/aide.css',FILE_USE_INCLUDE_PATH);
	$stylesheet .= file_get_contents('css/news.css',FILE_USE_INCLUDE_PATH);
	$pdf_content =<<<EOD
	              <html>
					<head>
						<title>$fileTitle</title>
						<style type ="text/css">
							$stylesheet
						</style>
					</head>
					<body>
						<div class='main-content' style='text-align: center;'>
							<center><h1>$fileTitle</h2></center>
							<br><br>
							$content
	                    </div>
					</body>
				</html>
EOD;

	$pdf = new WkHtmlToPdf(array('encoding' => 'UTF-8','zoom' => '0.75','page-size' => 'A4','binPath' => WKHTML_BIN_PATH,'margin-top'    => 10,'margin-right'  => 10,'margin-bottom' => 10,'margin-left'   => 10,'no-background', 'outline-depth' => '2'));	
	$pdf->addPage($pdf_content);
	if (!$pdf->send($fileTitle.".pdf",'D')) {
		throw new Exception('Could not create PDF: '.$pdf->getError());
	}
}









































?>