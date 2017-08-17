<?php
/*
 * Created on 27 janv. 2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
//require('utils/sphinxapi.php');
require_once("bd/bdConnect.php");
require_once("HTML/QuickForm.php");
require_once("scripts/filtreProjets.php");
require_once("scripts/lstDataUtils.php");
// require_once 'utils/SphinxAutocompleteAndcorrection/common.php';
// require_once 'utils/SphinxAutocompleteAndcorrection/functions.php';
// require_once 'utils/SphinxAutocompleteAndcorrection/sphinx_keyword_insertion.php';


class search_form_menu extends HTML_QuickForm{
	
	var $keywords;
	
	function createForm()
	{
		$this->addElement ( 'text', 'keywords_search_menu', 'Keywords: ', array (
				'size' => '15',
				'name' => 'q',
				'value' => isset ( $_GET ['q'] ) ? htmlentities ( $_GET ['q'] ) : '',
				'id' => 'suggest',
				'placeholder' => 'Search by keyword',
		) );
		
		$this->addElement('image','bouton_search_menu','/scripts/images/loupe-16.gif',array('title' => 'Search'));
	}
	
	function displayForm(){
		global $project_name;
		$reqUri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
		$reqUri .= "?project_name=$project_name";

		echo "<form action='$reqUri' method='post' name='frsearchmenu' id='frsearchmenu' >";
		echo $this->getElement('keywords_search_menu')->toHTML().'&nbsp;'.$this->getElement('bouton_search_menu')->toHTML();
		echo '</form>';
	}
	
	function search($project_name)	{	
		if ( isset($_REQUEST['q']) ){
			$this->keywords = explode(' ',$_REQUEST['q']);
		}else{
			$keys = $this->exportValue('keywords_search_menu');
			$this->keywords = explode(' ',$keys);
		}

		//$_SESSION['search_form_menu_keywords'] = serialize($this->keywords);

		$projects = get_filtre_projets($project_name);

		$query = "SELECT DISTINCT dats_id,dats_title FROM dataset JOIN dats_proj using (dats_id) WHERE project_id IN ($projects)";

		foreach($this->keywords as $keyword){
			if (strlen($keyword) > 1){
				$query .=" AND dats_xml ILIKE '%$keyword%'";
			}
		}
		$query .= ' ORDER BY dats_title';

		//echo "<br>$query";

		$q = implode('+',$this->keywords);
		
		lstQueryData($query, array('q' => $q));
	}
	
	function SmartSearch($project_name) {
		if (SPHINX_LOG){
			echo "Sphinx smart search<br/>";
		}
		
		
		//global $ln_sph,$ln_s,$ln;
		global $ln_sph,$ln;
		
		$docs = array();
		$indexes = 'dats_xml_#MainProject,dats_title_#MainProject';
		$projects = get_filtre_projets ( $project_name );
		
		//insertion des keywords doit se faire une seule fois pour remplir la bdd
		//insert_keywords_docs_suggest();
		
		//insert_keyword("température");
		
		
		if (isset ( $_REQUEST ['q'] )) {
			$this->keywords = $_REQUEST ['q'];
		} else {
			$this->keywords = $this->exportValue ( 'keywords_search_menu' );
		}
		
		
		
		//$query = trim($this->keywords);
		$projects = get_filtre_projets ( $project_name );
		/*
		$cl = new SphinxClient ();
		$cl->SetServer ( '127.0.0.1', 9312 );
		// $cl->setMatchMode(SPH_MATCH_EXTENDED2);
		$cl->setMatchMode ( SPH_MATCH_ALL );
		$result = $cl->Query ( '%'.$this->keywords.'%' );
		$dats_ids = implode ( ",", array_keys ( $result ["matches"] ) );
		// --------------------added-----------------------
		//$meta = $ln_sph->query ( "SHOW META" )->fetchAll ();
		$meta= $cl->query( "SHOW META" );
		*/
		$stmt = $ln_sph->prepare("SELECT * FROM $indexes WHERE MATCH(:match) LIMIT 0, 1000");
		$stmt->bindValue(':match', mb_convert_encoding($this->keywords, "UTF-8", mb_detect_encoding($this->keywords)),PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetchAll();
		$meta = $ln_sph->query("SHOW META")->fetchAll();

		foreach ( $meta as $m ) {
			$meta_map [$m ['Variable_name']] = $m ['Value'];
		}
		$total_found = $meta_map ['total_found'];
		$total = $meta_map ['total'];
		$ids = array ();
		$tmpdocs = array ();
		if (count ($result) > 0) {
			foreach ( $result as $v ) {
				$ids [] = $v ['id'];
			}
			$dats_ids = implode ( ",", $ids );
			$q = "SELECT DISTINCT dats_id,dats_title FROM dataset JOIN dats_proj using (dats_id) WHERE project_id IN ($projects) AND dats_id in ($dats_ids) ORDER BY dats_title ";
			// $q = "SELECT id, title , content FROM docs WHERE id IN (" . implode(',', $ids) . ")";
			foreach ( $ln->query ( $q ) as $row ) {
				$tmpdocs [$row ['id']] = array (
						'title' => $row ['dats_title']
				);
			}
			foreach ( $ids as $id ) {
				$docs [] = $tmpdocs [$id];
			}
			
			$keys = explode(' ',$this->keywords);
			lstQueryData($q, array('q' => implode('+',$keys)));	
		} else {
			$words = array ();
			foreach ( $meta_map as $k => $v ) {
				if (preg_match ( '/keyword\[\d+]/', $k )) {
					preg_match ( '/\d+/', $k, $key );
					$key = $key [0];
					$words [$key] ['keyword'] = mb_convert_encoding($v, "UTF-8", mb_detect_encoding($v));
				}
				if (preg_match ( '/docs\[\d+]/', $k )) {
					preg_match ( '/\d+/', $k, $key );
					$key = $key [0];
					$words [$key] ['docs'] = mb_convert_encoding($v, "UTF-8", mb_detect_encoding($v));
				}
			}
			$suggest = MakePhaseSuggestion ( $words, mb_convert_encoding($this->keywords, "UTF-8", mb_detect_encoding($this->keywords)), $ln_sph );
		}
		// -------------------------------------------------
		// $query = "SELECT DISTINCT dats_id,dats_title FROM dataset JOIN dats_proj using (dats_id) WHERE project_id IN ($projects) AND dats_id in ($dats_ids) ORDER BY dats_title ";
		//lstQueryData ( $query );
		if (isset ($this->keywords) && count ( $docs ) <= 0 ) {
			echo '<p class="lead">Nothing found!</p>';
		}
		
		if (isset ( $suggest ) && $suggest != '' && $suggest != ' ' && $suggest != $this->keywords ) {
			echo	'<p>'.
						"Did you mean <i><a href='?q=$suggest'>$suggest</a>"."</i>?
					</p>";
		}
		
		$ajax_url = 'utils/SphinxAutocompleteAndcorrection/ajax_suggest_excerpts.php';
	}
	
}
?>
