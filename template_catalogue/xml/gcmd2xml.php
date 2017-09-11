<?php
/*
 * Created on 6 juil. 2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 set_include_path('.:/usr/share/pear:/usr/share/php:/home/mastrori/workspace/mistrals_catalogue/');
 require_once ("bd/gcmd_science_keyword.php");
 require_once ("xml/xmlTemplate2.php");
 
 
 $key = new gcmd_science_keyword;
 $xml = simplexml_load_string($xmlstr);
 
  		
 $query = "select * from gcmd_science_keyword where gcmd_level = 1 order by gcmd_name";
 $liste_topic = $key->getByQuery($query);
			
 for ($i = 0; $i < count($liste_topic); $i++){
      	$j = $liste_topic[$i]->gcmd_id;
	    $topic_xml = $xml->addChild('topic');
	    $topic_xml->addChild('topic_name',$liste_topic[$i]->gcmd_name);     	
       	$query2 = "select * from gcmd_science_keyword where gcm_gcmd_id = ".$j." order by gcmd_name";
       	$liste_categ = $key->getByQuery($query2);
	          	
	    for ($k = 0; $k < count($liste_categ); $k++)
		{
        	$l = $liste_categ[$k]->gcmd_id;
        	$categ_xml = $topic_xml->addChild('category');
        	$categ_xml->addChild('category_name',$liste_categ[$k]->gcmd_name);
         	$query3 = "select * from gcmd_science_keyword where gcm_gcmd_id = ".$l." order by gcmd_name";
            $liste_param = $key->getByQuery($query3);
            for ($m = 0; $m < count($liste_param); $m++)
            {
            	$n = $liste_param[$m]->gcmd_id;
                $var_level1_xml = $categ_xml->addChild('var_level1');
                $var_level1_xml->addChild('var_level1_name',$liste_param[$m]->gcmd_name);
                $query4 = "select * from gcmd_science_keyword where gcm_gcmd_id = ".$n." order by gcmd_name";
                $liste_param2 = $key->getByQuery($query4);
                for ($o = 0; $o < count($liste_param2);$o++)
                {
                	$p = $liste_param2[$o]->gcmd_id;
                	$var_level2_xml = $var_level1_xml->addChild('var_level_2');
                	$var_level2_xml->addChild('var_level2_name',$liste_param2[$o]->gcmd_name);
                }
            }
        }
 }
 printf($xml->asXML());
			        	
?>
