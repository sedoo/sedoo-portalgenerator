<?php 

class dataset_json{
	const WITH_INSERTED_DATA = 1;
	const WITH_DATA = 5;
	const NO_DATA = 10;
	
	public $id;
	public $title;
	public $abstract;
	public $purpose;
	/*
	 * "references" : {
        "type" : "string"
	},
	 */
	//public $references;
	public $periodName;
	public $dateBegin;
	public $dateEnd;
	
	public $projects;
	
	public $contacts;
	public $variables;
	public $sensors;
	public $places;
	
	public $dataType;
	
	public $features;
	public $urls;
		
	public $dataAvailability;
	
	/*
	 *"suggest" : {
    	"type" : "completion",
    	"index_analyzer" : "simple",
        "search_analyzer" : "simple"
    }, 
	 */
	//public $suggest;
	
	public function __construct($id, $title){
		$this->id = $id;
		$this->title = $title;
		$this->contacts = array();
		$this->variables = array();
		$this->sensors = array();
		$this->places = array();
		$this->projects = array();
		$this->urls = array();
		$this->dataAvailability = self::NO_DATA;
	}
	
	public function addBoundings($boundings){
		
		if ( !isset($this->features)){
			$this->features['type'] = 'geometrycollection';
			$this->features['geometries'] = array();
		}
		
		if (	$boundings->west_bounding_coord === null
				|| $boundings->north_bounding_coord === null
				|| $boundings->east_bounding_coord === null
				|| $boundings->south_bounding_coord === null ){
			
			return;
					
		}
		
		if ( ($boundings->west_bounding_coord == $boundings->east_bounding_coord) 
				&& ($boundings->north_bounding_coord == $boundings->south_bounding_coord) ){
			//Point
			$this->features['geometries'][] = array('type' => 'point', 'coordinates' => array($boundings->west_bounding_coord, $boundings->south_bounding_coord));
		}else{
			$this->features['geometries'][] = array(
					'type' => 'envelope',
					'coordinates' => array(
							array($boundings->west_bounding_coord, $boundings->north_bounding_coord),
							array($boundings->east_bounding_coord, $boundings->south_bounding_coord)
					)
			);
			/*$this->features['geometries'][] = array(
					'type' => 'polygon', 
					'coordinates' => array(
							array($boundings->west_bounding_coord, $boundings->south_bounding_coord),
							array($boundings->west_bounding_coord, $boundings->north_bounding_coord),
							array($boundings->east_bounding_coord, $boundings->north_bounding_coord),
							array($boundings->east_bounding_coord, $boundings->south_bounding_coord),
							array($boundings->west_bounding_coord, $boundings->south_bounding_coord)
					)
			);*/
		}
	}
	
}

?>