<?php
/*
 * Created on 15 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
	function doubleCoord2int($coord)
	{
		//echo 'doubleCoord2int, coords: '.$coord.'<br>';
						
		if (!isset($coord) || strlen($coord) == 0)
			return null;
		
		//return (int) round($coord * 10000.0,4);
		return (int) round(round($coord,4) * 10000.0);
		//return (int)(($coord + 0.0005) * 10000.0);
	}
	
	function intCoord2double($coord)
	{
		//echo 'intCoord2double, coords: '.$coord.'<br>';
		
		if (!isset($coord) || strlen($coord) == 0)
			return null;
			
		return $coord / 10000.0;
		//return (($coord / 10000.0) - 0.0005);
	}
	
	function doubleAlt2int($alt)
	{
		//echo 'doubleAlt2int, coords: '.$alt.'<br>';
		
		if (!isset($alt) || strlen($alt) == 0)
			return null;
			
		//return (int) round($alt * 100.0,2);
		return (int) round(round($alt,2) * 100.0); 
		
		//return (int)(($alt + 0.05) * 100.0);
	}
	
	function intAlt2double($alt)
	{
		//echo 'intAlt2double, coords: '.$alt.'<br>';
		
		if (!isset($alt) || strlen($alt) == 0)
			return null;
			
		return $alt / 100.0;
				
		//return (($alt / 100.0) - 0.05);
	}
?>
