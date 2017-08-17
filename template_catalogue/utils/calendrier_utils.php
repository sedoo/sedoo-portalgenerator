<?php
require_once('utils/calendrier.php');
require_once('bd/data_availability.php');

function afficheListeAnnees($year,$yearMin,$yearMax,$url){
	echo "<a href='$url'>Back</a><br/><br/>";
	echo '<center><b>';
	if ($yearMin < $year)
		echo "<a href='$url&year=".($year-1)."'>&lt;&lt;</a>";
	for ($y = $yearMin;$y <= $yearMax;$y++){
		if ($y == $year)
			echo "&nbsp;&nbsp;<b>$year</b>&nbsp;&nbsp;";
		else
			echo "<a href='$url&year=$y'>&nbsp;$y&nbsp;</a></b>";
		if ($y < $yearMax)
			echo '-';
	}

	if ($yearMax > $year)
		echo "<a href='$url&year=".($year+1)."'>&gt;&gt;</a></b>";
	echo '</center><br/><br/>';
}

function afficheCalendriers($ins_dats_id,$var_id,$place_id,$year){

	$da = new data_availability;
	$das = $da->getByDatsVarPlace($ins_dats_id,$var_id,$place_id,$year);

	if (empty($das)){
		echo '<b>No data</b>';
	}else{

		$da = reset($das);
				
		$dateBegin = new DateTime($da->date_begin);
		$dateEnd = new DateTime($da->date_end);
		
		if ($dateBegin->format('Y') < $year) 
			$dateBegin = new DateTime($year."-01-01");
		
		if ($dateEnd->format('Y') > $year)
			$dateEnd = new DateTime($year."-12-31");

		//}while();

		for ($month = 1;$month <= 12;$month++){
			$cal = new calendrier($year,$month);
			while( $month == $dateBegin->format('n')){
				$dMin = $dateBegin->format('j');
				if ( $dateEnd->format('n') == $month )
					$dMax = $dateEnd->format('j');
				else if ( $dateEnd->format('n') > $month )
					$dMax = 32;
				$cal->setAvailableMinMax($dMin,$dMax);
				if ($dMax == 32){
					$dateBegin = new DateTime($year."-".($month+1)."-01");
				}else{
					$da = next($das);
					if ($da === false)
					break;
					$dateBegin = new DateTime($da->date_begin);
					$dateEnd = new DateTime($da->date_end);
					if ($dateEnd->format('Y') > $year)
						$dateEnd = new DateTime($year."-12-31");					
				}
			}
			$cal->display();
		}

	}
}

?>