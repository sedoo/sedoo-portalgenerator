<?php

class DatePickerUtils{

	function addScriptPeriod($from = 'date_min', $to = 'date_max', $minDate = null, $maxDate = null){
		
		if ($minDate){
			$minDateConf = "minDate: '$minDate',";
		}else{
			$minDateConf = "";
		}
		
		if ($maxDate){
			$maxDateConf = "maxDate: '$maxDate',";
		}else{
			$maxDateConf = "maxDate: '0',";
		}
		
		echo " <script>
				  $(function() {
				    $( \"#$from\" ).datepicker({ $minDateConf $maxDateConf
				        dateFormat: 'yy-mm-dd',
				        showButtonPanel: true,
				        closeText: 'Close',
				        changeMonth: true,
				        changeYear: true,
				        yearRange: '1979:+0',
				        showOn: 'both',
				        buttonImage: '/img/calendrier.png',
				        buttonImageOnly: true,
				        onClose: function( selectedDate ) {
				        	$( \"#$to\" ).datepicker( 'option', 'minDate', selectedDate );
				      	}
				     });
				    $( \"#$to\" ).datepicker({ $minDateConf $maxDateConf
				        dateFormat: 'yy-mm-dd',
				        showButtonPanel: true,
				        closeText: 'Close',
				        changeMonth: true,
				        changeYear: true,
				        yearRange: '1979:+0',
				        showOn: 'both',
				        buttonImage: '/img/calendrier.png',
				        buttonImageOnly: true,
				        onClose: function( selectedDate ) {
				        	$( \"#$from\" ).datepicker( 'option', 'maxDate', selectedDate );
				      	}             
				    });
				  });
				 </script>";
	}

}

?>
