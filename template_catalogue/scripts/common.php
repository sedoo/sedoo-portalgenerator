<?php
/*
 * Created on 15 juil. 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
	function doubleCoord2int($coord)
	{
						
		if (!isset($coord) || strlen($coord) == 0)
			return null;
		
		return (int) round(round($coord,4) * 10000.0);
	}
	
	function intCoord2double($coord)
	{
		
		if (!isset($coord) || strlen($coord) == 0)
			return null;
			
		return $coord / 10000.0;
	}
	
	function doubleAlt2int($alt)
	{
		
		if (!isset($alt) || strlen($alt) == 0)
			return null;
			
		return (int) round(round($alt,2) * 100.0); 
		
	}
	
	function intAlt2double($alt)
	{
		
		if (!isset($alt) || strlen($alt) == 0)
			return null;
			
		return $alt / 100.0;
	}
?>
