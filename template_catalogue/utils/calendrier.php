<?php

require_once('calendrier_cellule.php');

class calendrier{

	var $titre;
	var $table;

	function calendrier($year,$month, $availableDays = array()){
		$date = new DateTime("$year-$month-01");
		
		$this->table = array();
		$this->titre = $date->format('M Y');


		while ( $date->format('n') == $month ){
			$ligne = array(7);
			for ($i = 0;$i <= 6;$i++){
				if ( $i < $date->format('w') || $date->format('n') != $month){
					$ligne[$i] = new calendrier_cellule();
				}else{
					$ligne[$i] = new calendrier_cellule($date->format('j'));
					if (in_array($ligne[$i]->jour, $availableDays))
						$ligne[$i]->setAvailable();
					else
						$ligne[$i]->setMissing();
					$date->add(new DateInterval('P1D'));
//					if ( $date->format('n') != $month ) break;
				}
			}
			$this->table[] = $ligne;
		}
		if ( count($this->table) < 6){
			$ligne = array();
			for ($i = 0;$i <= 6;$i++){
				$ligne[] = new calendrier_cellule();
			}
			$this->table[] = $ligne;
		}

	}

	function setAvailableDays($days){
		foreach($this->table as $ligne){
                        foreach($ligne as $cell){
                                if ( in_array($cell->jour,$days) )
                                        $cell->setAvailable();
                        }
                }
	}

	function setAvailableMinMax($dMin,$dMax){
		foreach($this->table as $ligne){
                        foreach($ligne as $cell){
				if ( ($cell->jour >= $dMin) && ($cell->jour <= $dMax) )
					$cell->setAvailable();
				if ($cell->jour > $dMax)
					return;
                        }
                }
	}

	function display(){
		echo '<div style="float:left;margin:10px;padding: 10px 10px 10px 10px;border:1px solid;border-radius:10px;-moz-border-radius:10px;">';
		echo "<table ><tr><th colspan='7' style='height:120%;text-align:center;'>$this->titre</th></tr>";
		foreach($this->table as $ligne){
			echo '<tr>';
			foreach($ligne as $cell){
				echo "<td style='background-color: $cell->color;text-align:center;padding:0px;height:100%;'>";
				if (isset($cell->jour))
					echo $cell->jour;
				else
					echo '&nbsp;';
				echo '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
		echo '</div>';
	}

}

?>
