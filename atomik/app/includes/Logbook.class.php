<?php
class Logbook {
	private $ref_sqa_logbook;
	public $project_id;
	public $project;	
	public $sub_project_id;
	public $equipment;
	public $baseline;
	public $filename;	
	public $ref;
	public $issue;
	public $title;
	public $board;
	public $sub_title;
	public $author;
	public $head_office;
	public $email;
	public $phone_number;
	public $fax_number;
	public $memo_subject;
	public $folder;
	/*
	 *  Build title according to project and equipment
	 */
	public function search_logbook_ref() {
		Atomik::needed('Data.class');
		$ref = new Data;
		$ref->application = "";
		$ref->version = "";
		/* search SQA logbook corresponding to the project and the LRU */
        $sql_search_sqa_logbook = "SELECT application,version ".
        							"FROM bug_applications ".
        							"WHERE type = 25 ".
        							"AND project = {$this->project_id} ".
        							"AND lru = {$this->sub_project_id} ".
        							"ORDER BY version DESC LIMIT 0,1";
									
        $result = A('db:'.$sql_search_sqa_logbook);
		if($result){
			 $sqa_ref = $result->fetch(PDO::FETCH_OBJ);
			/* amount of SQA logbook */
			if ($sqa_ref !== false) {
				$ref->application = $sqa_ref->application;
				$ref->version = $sqa_ref->version;		  
			}	
		}
		return($ref);
	}	 
	function __construct($context=null) { 
		Atomik::needed('Project.class');
		$this->company_id 		= isset($context['company_id'])?$context['company_id']:"";
		$this->aircraft_id 		= isset($context['aircraft_id'])?$context['aircraft_id']:"";	
		$this->project_id 		= isset($context['project_id'])? $context['project_id'] : "";
		$this->sub_project_id 	= isset($context['sub_project_id'])? $context['sub_project_id'] : "";
		$this->baseline 		= isset($context['baseline_id'])? $context['baseline_id'] : "";	
		$today_date_underscore = date("Y").'_'.date("M").'_'.date("d");
		$project = new Project(&$context);
		$project ->get($this->project_id);
		$this->folder = $project->getFolder();
		if($this->sub_project_id != ""){
			$project->getSubProject($this->sub_project_id);
			$this->equipment = $project->getSubProjectName();
		}
		else{
			$project->get($this->project_id);
			$this->equipment = "";
		}
		$this->project = $project->getProjectName();
		$this->board = $this->project." ";
		if ($this->equipment != ""){
			$this->board .= $this->equipment." ";
		}
		$this->title = $this->board."Logbook";
		Atomik::needed('Tool.class');
		$this->filename = Tool::cleanFilename($this->title." ".$today_date_underscore.".xlsx");		
		$ref = $this->search_logbook_ref();
		$this->ref 			= $ref->application;
		$this->issue 		= $ref->version;
		Atomik::needed('User.class');
		$user = new User;
		$this->author 		= User::getNameUserLogged();
		$this->email 		= User::getEmailUserLogged();
		$this->head_office 	= "Quality Department";
		$this->phone_number = $user->getTel(User::getIdUserLogged());
		$this->fax_number 	= "";
		$this->memo_subject = "QA logbook";
	}
}
?>
