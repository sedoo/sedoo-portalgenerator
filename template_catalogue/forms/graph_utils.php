<?php
require_once ('jpgraph.php');
require_once ('jpgraph_bar.php');
require_once ('jpgraph_line.php');
require_once ('jpgraph_pie.php');
require_once ('jpgraph_date.php');
require_once ('jpgraph_utils.inc.php');
require_once ('jpgraph_text.inc.php');
require_once ('/sites/kernel/#MainProject/conf.php');
require_once ("countries.php");
define ( 'ECHELLE_GRAPHE_MOIS', 0 );
function displayGraph($graph, $image) {
	$img = "/graphs/$image";
	$graph->Stroke ( WEB_PATH . $img );
	echo "<img src='$img' />";
}
function getBarGraph($data, $labels, $titre, $max = 0, $titreY = 'Requests',$showValues = false) {
	$graph = new Graph ( 320, 240 );
	$graph->SetScale ( 'textlin', 0, $max );
	$graph->title->Set ( $titre );
	$graph->xaxis->SetTickLabels ( $labels );
	$graph->yaxis->title->Set ( $titreY );
	$bar = new BarPlot ( $data );
	$graph->Add ( $bar );
	if ($showValues){
        	$bar->value->Show();
        	$bar->value->SetFormat('%d');
        }
	return $graph;
}
function getPieGraph($data, $labels, $titre = '') {
	$graph = new PieGraph ( 620, 420 );
	$graph->SetShadow ();
	$graph->title->Set ( $titre );
	$p1 = new PiePlot ( $data );
	$p1->SetLabels ( $labels );
	$p1->SetLabelPos ( 1 );
	if (count ( $labels ) > 4) {
		$p1->SetGuideLines ( true, false, true );
		$p1->SetGuideLinesAdjust ( 1.4 );
	}
	$graph->Add ( $p1 );
	return $graph;
}
function getGraphByMonth($requetes, $year) {
	$datax = array ();
	$datay = array ();
	
	for($m = 1; $m <= 12; $m ++) {
		$datax [] = $m;
		if (array_key_exists ( $year, $requetes ) && array_key_exists ( $m, $requetes [$year] ))
			$datay [] = $requetes [$year] [$m];
		else
			$datay [] = 0;
	}
	
	return getBarGraph ( $datay, $datax, $year, ECHELLE_GRAPHE_MOIS );
}
function getGraphByYear($requetes, $yDeb, $titre = '') {
	$datax = array ();
	$datay = array ();
	$yFin = date ( 'Y' );
	for($y = $yDeb; $y <= $yFin; $y ++) {
		$nb = 0;
		for($m = 1; $m <= 12; $m ++) {
			if (array_key_exists ( $y, $requetes ) && array_key_exists ( $m, $requetes [$y] ))
				$nb += $requetes [$y] [$m];
		}
		$datax [] = $y;
		$datay [] = $nb;
	}
	return getBarGraph ( $datay, $datax, $titre, 0, 'Requests', true);
}
function getGraphDataTypes($requetes) {
	foreach ( $requetes as $t => $nb ) {
		$data [] = $nb;
		$labels [] = ucwords ( strtolower ( $t ) ) . " ($nb)";
	}
	
	return getPieGraph ( $data, $labels );
}
function getGraphUsers($requetes, $yDeb, $title = '') {
	$datax = array ();
	$datay = array ();
	$yFin = date ( 'Y' );
	$i = 0;
	for($y = $yDeb; $y <= $yFin; $y ++) {
		$mFin = ($y == $yFin) ? date ( 'n' ) : 12;
		for($m = 1; $m <= $mFin; $m ++) {
			$datax [$i] = strtotime ( "$y-$m-01" );
			if ($i == 0){
					$datay [$i] = $requetes [$y] [$m];
			}else{
					$datay [$i] = $datay [$i - 1] + $requetes [$y] [$m];
			}
			$i ++;
		}
	}
	
	$graph = new Graph ( 600, 400 );
	$graph->SetMargin ( 40, 40, 30, 130 );
	if ($title){
		$graph->title->Set ( $title );
	}
	$xmin = $datax [0];
	$xmax = $datax [$i - 1] + (31 * 24 * 3600);
	$graph->SetScale ( 'intlin', 0, 0, $xmin, $xmax );
	list ( $tickPositions, $minTickPositions ) = DateScaleUtils::GetTicks ( $datax, DSUTILS_MONTH2 );
	$graph->yaxis->title->Set ( 'Registered users' );
	$graph->xaxis->SetLabelAngle ( 60 );
	$graph->xaxis->SetTickPositions ( $tickPositions, $minTickPositions );
	$graph->xaxis->SetLabelFormatString ( 'M Y', true );
	$graph->xgrid->Show ();
	$line = new LinePlot ( $datay, $datax );
	$line->SetFillColor ( 'lightblue@0.5' );
	$graph->Add ( $line );
	return $graph;
}
function getGraphRequetesByDataset($datax, $datay) {
	$graph = new Graph ( 600, 400 );
	$graph->SetMargin ( 40, 40, 30, 130 );
	// $graph->title->Set("$datsTitle downloads");
	
	$xmin = $datax [0];
	// $xmax = $datax[$i-1] + (31 * 24 * 3600);
	$xmax = time ();
	$graph->SetScale ( 'intlin', 0, 0, $xmin, $xmax );
	list ( $tickPositions, $minTickPositions ) = DateScaleUtils::GetTicks ( $datax, DSUTILS_MONTH2 );
	
	$graph->yaxis->title->Set ( 'Data downloads' );
	
	$graph->xaxis->SetLabelAngle ( 60 );
	$graph->xaxis->SetTickPositions ( $tickPositions, $minTickPositions );
	$graph->xaxis->SetLabelFormatString ( 'M Y', true );
	
	$graph->xgrid->Show ();
	
	$line = new LinePlot ( $datay, $datax );
	$line->SetFillColor ( 'lightblue@0.5' );
	$graph->Add ( $line );
	
	return $graph;
}
function getGraphRoles($requetes) {
	foreach ( $requetes ['r'] as $r => $nb ) {
		$data [] = $nb;
		$labels [] = "$r ($nb)";
	}
	
	return getPieGraph ( $data, $labels );
}
function getGraphCountries($requetes) {
	foreach ( $requetes ['c'] as $c => $nb ) {
		$cName = countries::getDisplayName ( $c );
		$data [] = $nb;
		$labels [] = "$cName ($nb)";
	}
	
	return getPieGraph ( $data, $labels );
}

?>
