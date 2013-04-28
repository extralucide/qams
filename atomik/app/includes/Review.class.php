<?php
/* This function select review */
class Review {
	public $id;
	public $review_id;
	private $subject;
	public $comment;
	public $managed_by;
	public $type_id;
	public $type;
	public $date;
	public $date_start;
	public $small_date;
	public $date_sql;
	public $date_end;
	public $date_end_sql;	
	public $lru;
	public $project;
	public $lru_id;
	public $project_id;
	public $title;
	public $description;
	public $objective;
	public $link;
	public $link_mime;
	public $report_link_id;
	public $reference;
	public $attendees;
	public $person_copy;	
	public $scope;
	public $status;
	public $status_id;
	public $previous_id;
	public $mime;
	public $mime_link;
	private $extension;
	private $uploaded_id;
	private $sql_query;
	private $result;
	private $row;
	private $db;
	
	public function testExistence() {
		/* Test if the review exist */
		$exist = true;
		if ($this->sub_project_id != ""){
			$exist = Atomik_Db::find("reviews",array("id"=>$this->id,"project"=>$this->project_id,"lru"=>$this->sub_project_id));
		}
		return($exist);
	}	
	public function __construct(&$context = null) {
		Atomik::needed("Db.class");
		$db = new Db;
		$this->setDb($db);
		if ($context != null){
			$this->id				= isset($context['review_id'])? $context['review_id'] : "";
			$this->aircraft_id 		= isset($context['aircraft_id'])? $context['aircraft_id'] : "";
			$this->project_id 		= isset($context['project_id'])? $context['project_id'] : "";
			$this->sub_project_id 	= isset($context['sub_project_id'])? $context['sub_project_id'] : "";	
			$this->status_id		= isset($context['status_id'])? $context['status_id'] : "";	
			/* The review is related to project and sub project ? */
			if ($this->testExistence()){			
				$this->review_id = isset($context['review_id'])? $context['review_id'] : "";
			}
			else{
				$this->review_id = "";
				$context['review_id'] = "";
			}
			$this->baseline_id		= isset($context['baseline_id'])? $context['baseline_id'] : "";	
			$this->type_id			= isset($context['type_id'])? $context['type_id'] : "";				
			Atomik::needed("Tool.class");
			$this->which_aircraft		= Tool::setFilter("aircrafts.id",$this->aircraft_id);
			$this->which_project 		= Tool::setFilter("projects.id",$this->project_id);
			$this->which_sub_project 	= Tool::setFilter("lrus.id",$this->sub_project_id);
			$this->which_status 	   	= Tool::setFilter("actions.status",$this->status_id);
			$this->which_review 		= Tool::setFilter("reviews.id",$this->review_id);	
			$this->which_review_type    = Tool::setFilter("reviews.type",$this->type_id);			
			$this->which_baseline		= Tool::setFilter("baselines.id",$this->baseline_id);
		}
		else{
			$this->clear();		
		}		
	}
	private function clear(){
		$this->id = "";
		$this->subject = "";
		$this->scope = "";
		$this->managed_by = "";
		$this->comment = "";
		$this->type_id = "";
		$this->type = "";
		$this->project = "";
		$this->lru = "";
		$this->aircraft_id = "";
		$this->project_id = "";
		$this->lru_id = "";
		require_once("Date.class.php");
		$this->date_start = Date::convert_date(Date::getTodayDate());
		$this->date = Date::convert_date(Date::getTodayDate());
		$this->small_date = Date::convert_date_small(Date::getTodayDate());
		$this->date_sql = Date::getTodayDate();
		$this->date_start_sql = Date::getTodayDate();
		$this->date_end = Date::convert_date(Date::getTodayDate());
		$this->date_end_sql = Date::getTodayDate();			
		$this->status = "";
		$this->status_id = "";
		$this->objective = "";
		$this->description = "";
		$this->link = "";
		$this->link_mime = "";
		$this->report_link_id = "";
		$this->reference = "";
		$this->previous_id = "";
		$this->title = "";	
		$this->report_link_id = "";	
		$this->uploaded_id = "";	
		$this->extension = "";				
		$this->attendees = array();
		$this->which_aircraft 		= "";
		$this->which_project 		= "";
		$this->which_sub_project 	= "";
		$this->which_status 	   	= "";
		$this->which_review 		= "";
		$this->which_review_type    = "";
		$this->which_baseline		= "";
	}

	public function getData(){
		if ($this->id != ""){
			/* List of documents linked to the baseline */
			$sql_query = "SELECT data_location.id as link_id,".
						  "data_location.name as link_extension,".
						  "baselines.description as baseline_name, ".
						  "baselines.id as baseline_id, ".
						  "projects.project, ".
						  "lrus.lru, ".
						  "data_cycle_type.name, ".
						  "data_cycle_type.description as type_description, ".
						  "bug_applications.application, ".
						  "bug_applications.description, ".
						  "bug_applications.version, ".
						  "baseline_join_data.id ".
						  "FROM bug_applications ".
						  "LEFT OUTER JOIN projects ON bug_applications.project = projects.id ".
						  "LEFT OUTER JOIN data_location ON bug_applications.id = data_location.data_id AND data_location.name LIKE 'pdf'".
						  "LEFT OUTER JOIN lrus ON bug_applications.lru     = lrus.id ".
						  "LEFT OUTER JOIN data_cycle_type ON bug_applications.type    = data_cycle_type.id ".
						  "LEFT OUTER JOIN baseline_join_data ON bug_applications.id = baseline_join_data.data_id ".
						  "LEFT OUTER JOIN baseline_join_review ON baseline_join_review.baseline_id = baseline_join_data.baseline_id ".
						  "LEFT OUTER JOIN baselines ON baselines.id = baseline_join_review.baseline_id ".
						  " WHERE baseline_join_review.review_id = {$this->id} ORDER BY description ASC ";
			$result = $this->db->db_query($sql_query);
			$list   = $result->fetchAll(PDO::FETCH_ASSOC);
		}
		else{
			$list = false;
		}
		return($list);
	}
	public function countActions(){
		Atomik::needed("Tool.class");
		$which_review = Tool::setFilter("reviews.id",$this->id);
		$sql_query = "SELECT actions.id ".
						   " FROM actions ".
						   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
						   " WHERE actions.id IS NOT NULL ".
						   $which_review.
						   " GROUP BY actions.id ORDER BY id ASC";
		// echo $sql_query;		   
		$result = A('db:'.$sql_query);
		if ($result !== false){
			$nb = count($result->fetchAll());
		}
		else{
			$nb=0;
		}
		return($nb);	
	}
	public function countOpenActions(){
		Atomik::needed("Tool.class");
		$which_review = Tool::setFilter("reviews.id",$this->id);
		$sql_query = "SELECT actions.id ".
						   " FROM actions ".
						   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
						   " WHERE actions.status != 9 ".
						   $which_review.
						   " GROUP BY actions.id ORDER BY id ASC";
		// echo $sql_query;		   
		$result = A('db:'.$sql_query);
		if ($result !== false){
			$nb = count($result->fetchAll());
		}
		else{
			$nb=0;
		}
		return($nb);	
	}
	public function getActions(){
		Atomik::needed("Tool.class");
		$which_review = Tool::setFilter("review",$this->id);
		$sql_query = "SELECT actions.comment,".
							"actions.id,".
							"actions.review as review_id,".
							"actions.status as status_id,".
							"actions.posted_by,".
							"actions.criticality as criticality_id,".
							"actions.context,".
							"actions.Description,".
						   " projects.project,".
						   " projects.id as project_id,".
						   " lrus.lru, ".
						   " lrus.id as sub_project_id,".
						   " fname, ".
						   " lname,".
						   " bug_criticality.name as criticality,".
						   " bug_status.name as status,".
						   " date_open,".
						   " date_expected,".
						   " date_closure ".
						   " FROM actions ".
						   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
						   " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
						   " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
						   " LEFT OUTER JOIN bug_users ON bug_users.id = actions.posted_by ".
						   " LEFT OUTER JOIN lrus ON lrus.id = actions.lru ".
						   " LEFT OUTER JOIN projects ON projects.id = actions.project ".
						   " LEFT OUTER JOIN bug_status ON bug_status.id = actions.status ".
						   " LEFT OUTER JOIN bug_criticality ON bug_criticality.level = actions.criticality ".
						   "WHERE actions.id IS NOT NULL ".		   
						   $which_review.
						   " GROUP BY actions.id ORDER BY id ASC";
		$result = A('db:'.$sql_query);
		return($result);				   	
	}
	public static function getHotReviews ($aircraft_id="",$project_id=""){
		$which_project = Tool::setFilterWhere("projects.id",$project_id);
		$which_aircraft = Tool::setFilterWhere("aircrafts.id",$aircraft_id);
		$sql_query = 'SELECT event,'.
						'fname,'.
						'lname,'.
						'title,'.
						'projects.project,'.
						'lrus.lru,'.
						'status,'.
						'reviews.id,'.
						'mom_id,'.
						'reviews.comment,'.
						'objective,'.
						'reviews.description as description,'.
						'bug_status.name ,'.
						'date,'.
						'managed_by, '.
						'review_type.description as type_description,'.
						'objectives, '.
						'reviews.type as type_id,'.
						'review_type.type '.
						 "FROM reviews ".
						 "left outer join bug_status on reviews.status = bug_status.id ".
						 "left outer join projects on projects.id = reviews.project ".
						 "LEFT OUTER JOIN aircrafts ON aircrafts.id = reviews.aircraft ".
						 "left outer join lrus on lrus.id = reviews.lru ".
						 "left outer join review_type on review_type.id = reviews.type ".
						 "left outer join bug_users on bug_users.id=reviews.attendee ".
						 $which_project.
						 $which_aircraft.
						 " ORDER BY date DESC, project ASC,lru ASC LIMIT 0,10";
		$all_posts = A('db:'.$sql_query);
		return($all_posts);
	}
	public function getBaseline($all_project=false){
		/* List of baseline */
		Atomik::needed("Tool.class");
		if($all_project){
			$which_project = "";
		}
		else{
			$which_project = Tool::setFilter("baseline_join_project.project_id",$this->project_id);
		}		
		$which_review = Tool::setFilter("review_id",$this->id);
		$sql_query = "SELECT baselines.id,".
							"baselines.description as baseline_name,".
							"baseline_join_review.id as baseline_id,".
							"baseline_join_review.review_id,".
							"lrus.lru ".
							"FROM baseline_join_review ".
							" LEFT OUTER JOIN baselines ON baselines.id = baseline_join_review.baseline_id ".
							" LEFT OUTER JOIN baseline_join_project ON baselines.id = baseline_join_project.baseline_id ".
							" LEFT OUTER JOIN lrus ON lrus.id = baseline_join_project.lru_id ".
							" WHERE baselines.id IS NOT NULL {$which_review} {$which_project} GROUP BY baselines.id ORDER BY description ASC ";
		$result = $this->db->db_query($sql_query);
		$list   = $result->fetchAll(PDO::FETCH_OBJ);
		return($list);
	}
	public function getAttendees(){
		if ($this->id != ""){
			/* List of attendees */
			$sql_query = "SELECT user_join_review.user_id as id ,".
								 "user_join_review.id as link_id, ".
								 "copy, ".
								 "fname,".
								 "lname,".
								 "email,".
								 "telephone as phone,".					 
								 "function ,".
								 "enterprises.name as company ".
								 "FROM bug_users ".
								 "LEFT OUTER JOIN enterprises ON enterprises.id = enterprise_id ".
								 "LEFT OUTER JOIN user_join_review ON bug_users.id = user_join_review.user_id ".
								 "LEFT OUTER JOIN reviews ON reviews.id = user_join_review.review_id ".
								 "WHERE user_join_review.copy = 0 AND reviews.id = {$this->id} ORDER BY company ASC, lname ASC";			 
			$result = $this->db->db_query($sql_query);	
			$list   = $result->fetchAll(PDO::FETCH_ASSOC);	
		}
		else {
			$list = false;
		}
		return($list);
	}	
	public function getPersonCopy(){
		if ($this->id != ""){
			/* List of attendees */
			$sql_query = "SELECT user_join_review.user_id as id ,".
						 "user_join_review.id as link_id, ".
						 "copy, ".
						 "fname,".
						 "lname,".
						 "email,".
						 "telephone as phone,".					 
						 "function ,".
						 "enterprises.name as company ".
						 "FROM bug_users ".
						 "LEFT OUTER JOIN enterprises ON enterprises.id = enterprise_id ".
						 "LEFT OUTER JOIN user_join_review ON bug_users.id = user_join_review.user_id ".
						 "LEFT OUTER JOIN reviews ON reviews.id = user_join_review.review_id ".
						 "WHERE user_join_review.copy = 1 AND reviews.id = {$this->id}";			 
			$result = $this->db->db_query($sql_query);
			$list   = $result->fetchAll(PDO::FETCH_ASSOC);	
		}
		else {
			$list = false;
		}
		return($list);
	}		
	public function getMinutes(){
		Atomik::needed("Tool.class");
		$filter = Tool::setFilter("data_join_review.review_id",$this->id);
		$sql_query = "SELECT data_join_review.id,".
					"bug_applications.id as data_id,".
					"bug_applications.application as reference ".
					"FROM bug_applications ".
					"LEFT OUTER JOIN data_join_review ON data_join_review.data_id = bug_applications.id ".
					"WHERE type = 28 {$filter}";
		
		$result = $this->db->db_query($sql_query);
		$list   = $result->fetchAll(PDO::FETCH_OBJ);
		return($list);
	}	
	public function getAllAttached(){
		$which_review = Tool::setFilterWhere("data_id",$this->id);
		$sql_query = "SELECT * FROM reviews_attachment {$which_review} ";
		$list = A('db:'.$sql_query);
		return($list);
	}
	public function getAllMinutes(){
		$sql_query = "SELECT bug_applications.id,".
			"date_published,".
			"bug_applications.application as reference,".
			"version,".
			"bug_applications.description, ".
		   "data_cycle_type.name as type,".
		   "data_cycle_type.description as type_description ".
		   "FROM bug_applications ".
		   "LEFT OUTER JOIN data_cycle_type ON bug_applications.type = data_cycle_type.id ".
		   "LEFT OUTER JOIN projects ON bug_applications.project = projects.id ".
		   "WHERE type = 28 {$this->which_project} GROUP BY bug_applications.id ORDER BY date_published DESC";
		$result = $this->db->db_query($sql_query);
		$list   = $result->fetchAll(PDO::FETCH_ASSOC);		
		return($list);
	}	
	public static function getAllReviewType($company_id="",$scope_id=""){
		Atomik::needed('Tool.class');
		$which_scope = Tool::setFilter("scope_id",$scope_id);
		$which_company = Tool::setFilter("company_id",$company_id);
		$sql_query = "SELECT review_type.id,".
					"type,".
					"review_type.description,".
					"objectives,".
					"enterprises.name FROM review_type ".
					" LEFT OUTER JOIN enterprises ON enterprises.id=review_type.company_id ".
					" LEFT OUTER JOIN scope ON scope.id = review_type.scope_id ".
					"WHERE review_type.id IS NOT NULL ".
					$which_scope.
					$which_company.
					" ORDER BY enterprises.name ASC, scope ASC, `type` ASC";	
					//echo $sql_query;
		$list   = A('db:'.$sql_query)->fetchAll(PDO::FETCH_ASSOC);		
		return($list);
	}
	public function getStatusList(){
		$sql_query = "SELECT * FROM bug_status WHERE `type` = 'review' ORDER BY `name` ASC";
		$result = $this->db->db_query($sql_query);
		$list   = $result->fetchAll(PDO::FETCH_ASSOC);
		return($list);
	}
	public function getReviewList($type = PDO::FETCH_ASSOC) {
		if ($this->review_id != ""){
			$this->get($this->review_id);
			$which_date = " AND '".$this->date_start_sql."' <= reviews.date ";
		}
		else {
			$which_date = "";
		}
		$sql_query = 'SELECT DISTINCT reviews.id, '.
						'reviews.title,'.
						'reviews.status,'.
						'reviews.mom_id,'.
						'reviews.comment,'.
						'reviews.description as description,'.
						'reviews.date,'.
						'reviews.date_end,'.
						'reviews.managed_by,'.
						'reviews.previous_id,'.
						'reviews.objective,'.
						'reviews.type as type_id,'.
						'review_type.type as type_abbreviation,'.
						'review_type.description as type_description,'.
						'review_type.objectives,'.
						'review_type.activities,'.
						'review_type.type,'.
						'review_type.scope_id, '.
						'data_join_review.data_id as link, '.
						"data_location.id as uploaded_id, ".
						"data_location.name as extension, ".
						"bug_applications.application as reference, ".
						'projects.project,'.
						'lrus.lru, '.
						'bug_users.fname,'.
						'bug_users.lname, '.
						'bug_status.name as status_name, '.
						'enterprises.name as company '.
						"FROM reviews ".
						 "LEFT OUTER JOIN bug_status ON reviews.status = bug_status.id ".
						 /*"LEFT OUTER JOIN actions ON actions.review = reviews.id ".*/
						 "LEFT OUTER JOIN projects ON projects.id = reviews.project ".
						 "LEFT OUTER JOIN aircrafts ON reviews.aircraft = aircrafts.id ".
						 "LEFT OUTER JOIN lrus ON lrus.id = reviews.lru ".
						 "LEFT OUTER JOIN review_type ON review_type.id = reviews.type ".
						 "LEFT OUTER JOIN enterprises ON review_type.company_id = enterprises.id ".
						 "LEFT OUTER JOIN bug_users ON bug_users.id=reviews.attendee ".
						 "LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
						 "LEFT OUTER JOIN baselines ON baselines.id = baseline_join_review.baseline_id ". 
						 "LEFT OUTER JOIN data_join_review ON reviews.id = data_join_review.review_id ". 
						 "LEFT OUTER JOIN data_location ON data_location.data_id = data_join_review.data_id ".
						 "LEFT OUTER JOIN bug_applications ON bug_applications.id = data_join_review.data_id ".
						 " WHERE reviews.event = 0 ".
						   $this->which_status.
						   $this->which_aircraft.
						   $this->which_project.
						   $this->which_sub_project.
						   $this->which_review_type.
						   $this->which_baseline.
						   $which_date.
						 ' GROUP BY reviews.id ORDER BY date DESC, project ASC,lru ASC';
			 $result = $this->db->db_query($sql_query);
		     $list   = $result->fetchAll($type);
			 return ($list);
	}
	public function getAllReviewList($type = PDO::FETCH_ASSOC) {
		$sql_query = 'SELECT DISTINCT reviews.id, '.
						'reviews.title,'.
						'reviews.status,'.
						'reviews.mom_id,'.
						'reviews.comment,'.
						'reviews.description as description,'.
						'reviews.date,'.
						'reviews.date_end,'.
						'reviews.managed_by,'.
						'reviews.previous_id,'.
						'reviews.objective,'.
						'reviews.type as type_id,'.
						'review_type.type as type_abbreviation,'.
						'review_type.description as type_description,'.
						'review_type.objectives,'.
						'review_type.activities,'.
						'review_type.type,'.
						'review_type.scope_id, '.
						'data_join_review.data_id as link, '.
						"data_location.id as uploaded_id, ".
						"data_location.name as extension, ".
						"bug_applications.application as reference, ".
						'projects.project,'.
						'lrus.lru, '.
						'bug_users.fname,'.
						'bug_users.lname, '.
						'bug_status.name as status_name, '.
						'enterprises.name as company '.
						"FROM reviews ".
						 "LEFT OUTER JOIN bug_status ON reviews.status = bug_status.id ".
						 /*"LEFT OUTER JOIN actions ON actions.review = reviews.id ".*/
						 "LEFT OUTER JOIN projects ON projects.id = reviews.project ".
						 "LEFT OUTER JOIN aircrafts ON projects.aircraft_id = aircrafts.id ".
						 "LEFT OUTER JOIN lrus ON lrus.id = reviews.lru ".
						 "LEFT OUTER JOIN review_type ON review_type.id = reviews.type ".
						 "LEFT OUTER JOIN enterprises ON review_type.company_id = enterprises.id ".
						 "LEFT OUTER JOIN bug_users ON bug_users.id=reviews.attendee ".
						 "LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
						 "LEFT OUTER JOIN baselines ON baselines.id = baseline_join_review.baseline_id ". 
						 "LEFT OUTER JOIN data_join_review ON reviews.id = data_join_review.review_id ". 
						 "LEFT OUTER JOIN data_location ON data_location.data_id = data_join_review.data_id ".
						 "LEFT OUTER JOIN bug_applications ON bug_applications.id = data_join_review.data_id ".
						 " WHERE reviews.event = 0 ".
						   $this->which_status.
						   $this->which_aircraft.						   
						   $this->which_project.
						   $this->which_sub_project.
						   $this->which_review_type.
						   $this->which_baseline.
						 ' GROUP BY reviews.id ORDER BY date DESC, project ASC,lru ASC';			 
			 $result = $this->db->db_query($sql_query);
		     $list   = $result->fetchAll($type);
			 return ($list);
	}	
	public function get($id="") {
		if ($id !=""){
			$sql_query = 
				'SELECT review_type.scope_id, '.
						'review_type.type as type_description, '.
						'review_type.description as type, '.
						'review_type.objectives as default_objectives, '.
						'objectives, '.					
						'reviews.id, '.
						'reviews.subject, '.
						'reviews.previous_id, '.
						'reviews.project as project_id, '.
						'reviews.lru as lru_id,'.
						'reviews.status as status_id, '.
						'reviews.type as type_id, '.
						'reviews.comment, '.
						'reviews.description as description, '.	
						"reviews.objective, ".
						'managed_by, '.
						'scope.scope, '.
						'fname, '.
						'lname, '.
						'title, '.
						'projects.project, '.
						'lrus.lru, '.
						'bug_status.name as status, '.
						'bug_status.name, '.
						'mom_id, '.
						'date, '.
						'date_end, '.
						'data_join_review.data_id as link, '.
						"data_location.id as uploaded_id, ".
						"data_location.name as extension, ".
						"bug_applications.application as reference ".
						"FROM reviews ".
						"LEFT OUTER JOIN bug_status on reviews.status = bug_status.id ".
						"LEFT OUTER JOIN projects on projects.id = reviews.project ".
						"LEFT OUTER JOIN lrus on lrus.id = reviews.lru ".
						"LEFT OUTER JOIN review_type on review_type.id = reviews.type ".
						"LEFT OUTER JOIN scope ON review_type.scope_id = scope.id ".
						"LEFT OUTER JOIN bug_users on bug_users.id=reviews.attendee ".
						"LEFT OUTER JOIN data_join_review ON reviews.id = data_join_review.review_id ".
						"LEFT OUTER JOIN bug_applications ON bug_applications.id = data_join_review.data_id ". 		 
						"LEFT OUTER JOIN data_location ON data_location.data_id = data_join_review.data_id ".
						"WHERE reviews.id = {$id}".
				' order by date ASC, project ASC,lru ASC LIMIT 0,1';
			try { 
			   $result = $this->db->db_query($sql_query);
			   $row   = $result->fetch(PDO::FETCH_ASSOC);
			} 
			catch (PDOException $e) {
				die( "Erreur ! : " . $e->getMessage() );			
			}	
			$this->id = $row['id'];
			$this->scope = $row['scope'];
			$this->subject = $row['subject'];
			$this->managed_by = $row['managed_by'];
			$this->comment = $row['comment'];
			$this->type_id = $row['type_id'];
			Atomik::needed("Tool.class");
			$this->type = Tool::clean_text($row['type']);
			$this->project = $row['project'];
			$this->lru = $row['lru'];
			$this->project_id = $row['project_id'];
			$this->lru_id = $row['lru_id'];
			Atomik::needed("Date.class");
			$this->date_start = Date::convert_date($row['date']);
			$this->date = Date::convert_date($row['date']);
			$this->small_date = Date::convert_date_small($row['date']);
			$this->date_sql = $row['date'];
			$this->date_start_sql = $row['date'];
			if ($row['date_end'] != "0000-00-00") {
				$this->date_end = Date::convert_date($row['date_end']);
				$this->date_end_sql = $row['date_end'];
			}
			else {
				$this->date_end_sql = $this->date_sql;
				$this->date_end_sql = $this->date_start_sql;
			}				
			$this->status = $row['status'];
			$this->status_id = $row['status_id'];
			if (strlen($row['objective']) < 10) {
				$this->objective = $row['default_objectives'];
			}
			else {
				$this->objective = $row['objective'];
			}
			  Atomik::needed("Tool.class");
			  $description = Tool::cleanDescription($row['description']);
			  if ($description != ""){
					$this->description = $row['description'];
				}
				else{
					$this->description = $row['type_description'];
				}			
		    $this->link = Tool::Get_Filename($row['uploaded_id'],$row['extension'],"atomik");
			$this->link_mime = Tool::Get_Mime($this->link);
			$this->report_link_id = $row['link'];
			$this->reference = $row['reference'];
			$this->previous_id = $row['previous_id'];
			$this->title = $this->managed_by." ".$this->lru." ".$this->type." ".$this->date;
			if (isset($row['link']))		
				$this->report_link_id = $row['link'];
			else
				$this->report_link_id = "";	
			if (isset($row['uploaded_id']))		
				$this->uploaded_id = $row['uploaded_id'];
			else
				$this->uploaded_id = "";	
			if (isset($row['extension']))		
				$this->extension = $row['extension'];
			else
				$this->extension = "";				
			/*
			 * List of attendees
			 */
			$this->attendees = $this->getAttendees();
			$this->person_copy = $this->getPersonCopy();			
		}
	}
	public function getDateSQL(){
		return($this->date_start_sql);
	}
	public function getSubject(){
		return($this->subject);
	}
	public function getContext(){
		$context = $this->type." ".$this->project." ".$this->lru." du ".$this->small_date;
		return($context);
	}
	public function getPerfomedOn(){
		if (($this->date_end < Date::getTodayDate()) || 
			($this->status != "TBD")){ // # from TBD (review not performed)
			/* Meeting has been performed */
			$line = 'Perfomed on ';
			$line .= "<strong>".$this->date_start."</strong>";
		}				
		else if (
				(($this->date_start_sql <= Date::getTodayDate()) && 
				(Date::getTodayDate() <= $this->date_end)) || 
				($this->status != "TBD")) {
			/* Meeting has begun but is not finished */
			$line= 'In progress since ';
			$line .= "<strong>".$this->date_start."</strong>";
		}
		else {
			/* Meeting has been performed */
			$line = 'Planned on ';
			$line .= "<strong>".$this->date_start."</strong>";
		}	
		return($line);
	}
	private function setDb($db){
		$this->db = $db;
	}
	public function getDb(){
		return($this->db);
	}	
	public function delete($id){
		echo "remove review ".$id."<br/>";
		$sql_query = "DELETE FROM reviews WHERE id = ".$id;	
		$result = $this->_db->db_query($sql_query);
	}
	public function getObjective($option=false){
		if($option == false){
			$objective = $this->objective;
		}
		else{
			$objective = html_entity_decode($this->objective,ENT_COMPAT,"UTF-8");
		}
		return($objective);
	}
	public function getConclusion($option=false){
		if($option == false){
			$conclusion = $this->comment;
		}
		else{
			$conclusion = html_entity_decode($this->comment,ENT_COMPAT,"UTF-8");
		}
		return($conclusion);
	}
	public function getStatus(){
		switch ($this->status) {
			case "Accepted":
				$line = "<span style='background-color:green'>".$this->status."</span>";
				break;
			case "Partially Accepted":
				$line = "<span style='background-color:orange'>".$this->status."</span>";
				break;
			case "Not Accepted":
				$line = "<span style='background-color:red'>".$this->status."</span>";
				break;
			default:
				if ($this->status == ""){
					$line = "<span>None</span>";
				}
				else{
					$line = "<span>".$this->status."</span>";
				}
		}
		return ($line);
	}
	public function getPrevious(){
		if (($this->previous_id != "") && 
			($this->previous_id != 0)) {
			$line  = '<td class="td_arrow"><a href="'.Atomik::url("post_review",array("id"=>$this->previous_id)).'">';
			$line .= '<img src="assets/images/toundra/tooltipConnectorLeft.png" width="16" height="16" border="0" title="Previous Review '.$this->previous_id.'"></a></td>';
		}
		else{
			$line="";
		}
		return ($line);
	}
	public function getNext(){
		/* search next review */
		$sql_query ="SELECT id FROM reviews WHERE ".$this->id." = previous_id LIMIT 1";
		$result = $this->db->db_query($sql_query);
		$next_review_found = $result->fetch(PDO::FETCH_OBJ);
		if($next_review_found){
			$line = '<td class="td_arrow"><a href="'.Atomik::url("post_review",array("id"=>$next_review_found->id)).'">';
		    $line .= '<img src="assets/images/toundra/tooltipConnectorRight.png" width="16" height="16" border="0" title="Next Review '.$next_review_found->id.'"></a></td>';
		}
		else {
			$line = "";
		}
		return ($line);
	}
	public function getPreviousReview($id,
										$project_id,
										$sub_project_id){
		if ($id != ""){
			$where = "WHERE reviews.id != {$id}";
		}
		else {
			$where = "";
		}		
		Atomik::needed("Tool.class");
		if (($sub_project_id != "")&&($sub_project_id != 0)) {
			$where .= Tool::setFilter("reviews.lru",$sub_project_id);
		}
		else if ($project_id != "") {
			$where .= Tool::setFilter("reviews.project",$project_id);
		}
	
		$sql_query = "SELECT reviews.date, ".
					"reviews.id as id, ".
					"managed_by, ".
					"objective, ".
					"title, ".
					"review_type.type as type, ".
					"review_type.id as type_id, ".
					"lrus.lru ".
					"FROM reviews ".
					"LEFT OUTER JOIN projects ON projects.id = reviews.project ".
					"LEFT OUTER JOIN lrus ON lrus.id = reviews.lru ".
					"LEFT OUTER JOIN review_type ON reviews.type = review_type.id ".
					"{$where} ".
					"ORDER BY date DESC, review_type.description ASC";
			// echo $sql_query;
			$result = $this->db->db_query($sql_query);
			$list = $result->fetchAll(PDO::FETCH_OBJ);

		return($list);
	}
   public function update($info) {
 		Atomik::needed('Data.class');	
		if ($info['date'] == "")    {
			// today date
			$date_sql = date('Y-m-d');
			$date_end_sql = $info['date'];
		}
		else {
			$date_sql = Date::convert_dojo_date ($info['date']);
			$date_end_sql = Date::convert_dojo_date ($info['date_end']);
		}
		/* date of the end of the meeting should be after start of the meeting */
		Date::align_date_end($date_sql,
							$date_end_sql);	  					
	   $result = $this->db->update("reviews",
										array("aircraft"=>$info['aircraft'],
											  "project"=>$info['project'],
											  "lru"=>$info['equipment'],
											  "type"=>$info['type'],
											  "status"=>$info['status'],
											  "objective"=>$info['objective'],
											  "description"=>$info['description'],
											  "comment"=>$info['comment'],
											  "date"=>$date_sql,
											  "date_end"=>$date_end_sql,
											  "managed_by"=>$info['managed_by'] ,
											  "previous_id"=>$info['previous_id']
						  ),array('id' => $info['id']));		  
	   return($result);
   }	
	public function add	($aircraft,
						$project,
						$lru,
						$type,
						$status,
						$objective,
						$description,
						$comment,
						$date_sql,
						$date_end_sql,
						$managed_by,
						$previous_review_id) {

		  $new_review_id = $this->db->db_insert("reviews",
										array("aircraft"=>$aircraft,
											  "project"=>$project,
											  "lru"=>$lru,
											  "type"=>$type,
											  "status"=>$status,
											  "objective"=>$objective,
											  "description"=>$description,
											  "comment"=>$comment,
											  "date"=>$date_sql,
											  "date_end"=>$date_end_sql,
											  "managed_by"=>$managed_by ,
											  "previous_id"=>$previous_review_id
											  ));			
		return ($new_review_id);
	}		
	/* 
	 * This function processes a submitted review 
	 * Here we add a review
	 */
	public function create($info) {
		Atomik::needed('Data.class');	
		
		if ($info['date'] == "")    {
			// today date
			$date_sql = date('Y-m-d');
			$date_end_sql = $info['date'];
		}
		else {
			$date_sql = Date::convert_dojo_date ($info['date']);
			$date_end_sql = Date::convert_dojo_date ($info['date_end']);
		}
		/* date of the end of the meeting should be after start of the meeting */
		Date::align_date_end($date_sql,
							$date_end_sql);	
		
		/* Input Review in database */
		if (!isset($info['previous_review_id'])){
			$info['previous_review_id']="";	
		}
		$new_review_id = $this->add ($info['aircraft'],
									$info['project'],
									$info['equipment'],
									$info['type'],
									$info['status'],
									$info['objective'],
									$info['description'],
									$info['comment'],
									$date_sql,
									$date_end_sql,
									$info['managed_by'],
									$info['previous_review_id']);
	
			/* Input MoM in database */
			$data = new Data;
			$new_data_id = $data->createMinutes($info['project'],
												$info['equipment']);

			/* Create link between review and MoM */								 
			$sql_query = "INSERT INTO data_join_review (`data_id`, `review_id`) VALUES('{$new_data_id}','{$new_review_id}')";
			//echo "TsT:".$sql_query."<br/>";
			//exit("New link created ($sql_query)");
			$result = A('db:'.$sql_query);
		return($new_review_id);
	}
	public function setId($id){
		$this->id = $id;
	}
	public function createIndicator($review_list,$title="Reviews held"){
		/* pChart library inclusions */
		require_once("pChart2.1.3/class/pData.class.php");
		require_once("pChart2.1.3/class/pDraw.class.php");  
		require_once("pChart2.1.3/class/pImage.class.php");
		require_once("pChart2.1.3/class/pIndicator.class.php");
		require_once("pChart2.1.3/class/pIndicator_special.class.php");
		$dir_font = "app/includes/pChart/Fonts/";
		$dir_palette = "app/includes/pChart/";
		
		$x_graph = 80;
		$y_graph = 50;
		/* Define the indicator sections */
		$IndicatorSections   = "";
		$indicator_width = 40;//50;
		$start = 0;
		$end = $indicator_width - 1;
		$index = 0;
		$previous_meeting_date = Date::getTodayDate();
		$next_review = " now";
		foreach($review_list as $review):
			$this->setId($review->id);
			$nb_actions = $this->countActions();
			$nb_actions_open = $this->countOpenActions();
			/* Computes nb days from the previous meeting */
			$nb_days = Date::nbDays($review->date,$previous_meeting_date);
			// echo $review->date.":".$previous_meeting_date.":".$nb_days."<br/>";
			$previous_meeting_date = $review->date;
			$width = $indicator_width + $nb_days * 5;
			if ($review->type_abbreviation == "CCB"){
				$R = 150;
				$G = 162;
				$B = 69;
			}
			else{
				$R = 69;
				$G = 142;
				$B = 49;			
			}
			if ($width > 100){
				$width=100;
			}
			$end += $width;//$indicator_width;
			if ($nb_days > 1){
				$time_elapsed = $nb_days." days until ".$next_review;
			}
			else if ($nb_days == 1){
				$time_elapsed = " the day before ".$next_review;
			}
			else if ($nb_days == 0){
				$time_elapsed = " same day as ".$next_review;
			}			
			else {
				$time_elapsed = "planned";
			}

			$IndicatorSections[] = array("Date"=>Date::convert_date_small($review->date),
										"Days"=>$time_elapsed,
											"Start"=>$start,
											"End"=>$end,
											"Caption"=>Tool::cleanDescription($review->type_description." (".$review->managed_by.")"),
											"R"=>$R,
											"G"=>$G,
											"B"=>$B,
											"Review"=>$review->type_abbreviation,
											"Actions"=>$nb_actions,
											"Open"=>$nb_actions_open);								
			$start = $end + 1;
			$next_review = $review->type_abbreviation;
			$index++;
			// echo $review->type_abbreviation." => ";
			// if ($index> 3)break;	
		endforeach;
		// var_dump($IndicatorSections);
		/* Draw the 1st indicator */
		$IndicatorSettings = array("CaptionPosition"=>INDICATOR_CAPTION_INSIDE,
									"DrawLeftHead"=>TRUE,
									"DrawRightHead"=>FALSE,
									"ValueDisplay"=>INDICATOR_VALUE_BUBBLE,
									"ValueFontName"=>$dir_font."Forgotte.ttf",
									"ValueFontSize"=>15, 
									"IndicatorSections"=>$IndicatorSections,
									"SubCaptionColorFactor"=>-40);
		$ybackground = pIndicator_special::computeBackgroundHeight($x_graph,$y_graph,&$IndicatorSections);
		
		$x_max = 700;
		$y_max = $ybackground;

		/* Create and populate the pData object */
		$MyData = new pData;
		/* Create the pChart object */
		$myPicture = new pImage($x_max,$y_max,$MyData);
		/* Enable shadow support */
		$myPicture->setShadow(TRUE,array("X"=>10,"Y"=>10,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>5));
		/* Draw the background */
		$Settings = array("R"=>170, "G"=>183, "B"=>87, "Dash"=>1, "DashR"=>190, "DashG"=>203, "DashB"=>107);
		$myPicture->drawFilledRectangle(0,0,$x_max,$y_max,$Settings);

		/* Overlay with a gradient */
		$Settings = array("StartR"=>219, "StartG"=>231, "StartB"=>139, "EndR"=>1, "EndG"=>138, "EndB"=>68, "Alpha"=>50);
		$myPicture->drawGradientArea(0,0,$x_max,$y_max,DIRECTION_VERTICAL,$Settings);
		$myPicture->drawGradientArea(0,0,$x_max,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>80));

		/* Add a border to the picture */
		$myPicture->drawRectangle(0,
									0,
									$x_max - 1,
									$y_max - 1,
									array("R"=>0,"G"=>0,"B"=>0));

		/* Write the picture title */ 
		$myPicture->setFontProperties(array("FontName"=>$dir_font."Silkscreen.ttf","FontSize"=>6));
		$myPicture->drawText(10,13,$title,array("R"=>255,"G"=>255,"B"=>255));
		
		/* Prepare some nice data & axis config */
		// $MyData->addPoints(array(24,-25,26,25,25),"Temperature");
		// $MyData->setAxisName(0,"Temperatures");
		// $MyData->addPoints(array("Jan","Feb","Mar","Apr","May","Jun"),"Labels");
		// $MyData->setSerieDescription("Labels","Months");
		// $MyData->setAbscissa("Labels");
		/* Define the graph area and do some makeup */
		// $myPicture->setGraphArea(60,60,$x_max - 60 ,$y_max - 60);
		// $myPicture->drawScale(array("DrawSubTicks"=>TRUE,"DrawArrows"=>FALSE,"ArrowSize"=>6));
		
		/* Create the pIndicator object */ 
		$Indicator = new pIndicator_special($myPicture);
		$myPicture->setFontProperties(array("FontName"=>$dir_font."pf_arma_five.ttf","FontSize"=>6));

		$Indicator->draw($x_graph,$y_graph,$x_max - 150,50,$IndicatorSettings);
		
		/* Left green box */
		// $RectangleSettings = array("R"=>150,"G"=>200,"B"=>170,"Dash"=>TRUE,"DashR"=>170,"DashG"=>220,"DashB"=>190,"BorderR"=>255, "BorderG"=>255,"BorderB"=>255);
		// $myPicture->drawFilledRectangle(20,60,400,170,$RectangleSettings);

		/* Render the picture */
		$bar_filename="../result/test.png";
		$myPicture->Render($bar_filename);			
		$gdImage_poster = @imagecreatefrompng($bar_filename);
		return($bar_filename);
	}
}

class Type{
    public $id;
    public $type;
    public $scope_id;
    public $description;
    public $company_id;
    public $company;
    public $objectives;
    public $inputs;
    public $activities;
    public $outputs;
    public $schedule;

	public function get($id){
		$sql_query = "SELECT review_type.id as id,".
							"type,".
							"scope_id,".
							"review_type.description,".
							"company_id,".
							"name,".
							"objectives,".
							"inputs,".
							"activities,".
							"outputs,".
							"schedule ".
						"FROM review_type LEFT OUTER JOIN enterprises ON review_type.company_id = enterprises.id WHERE review_type.id = {$id} ORDER BY `type` ASC";				
		$review_type_array = A("db:".$sql_query);
		$row = $review_type_array->fetch();
		$this->id = $row['id'];
		$this->type = $row['type'];
		$this->scope_id = $row['scope_id'];
		$this->description = $row['description'];
		$this->company_id = $row['company_id'];
		$this->company = $row['name'];
		$this->objectives = $row['objectives'];
		$this->inputs = $row['inputs'];
		$this->activities = $row['activities'];
		$this->outputs = $row['outputs'];
		$this->schedule = $row['schedule'];		
		return($review_type_array);
	}
    public function __construct(&$context = null) {
		if ($context != null){
			$this->company_id = isset($context['company_id'])? $context['company_id'] : "";
			$this->scope_id = isset($context['scope_id'])? $context['scope_id'] : "";	
		}
		else{
			$this->scope_id = 1;
			$this->company_id = 1;		
		}
		$this->id =0;
		$this->type = "TBD";
		$this->description = "TBD";
		$this->company = "";
		$this->objectives = "N/A";
		$this->inputs = "N/A";
		$this->activities = "N/A";
		$this->outputs = "N/A";
		$this->schedule = "N/A";		
    }
}
class Question{
    public $id;
    public $review_id;
    public $type;
    public $tag;
    public $item;    
    public $text;
	
	public function get($question_id){
		$questions_query = "SELECT review_type.type as acronym ,".
								"checklist_questions.id,".
								"review_id,".
								"question,".
								"type,".
								"item_order,".
								"tag ".
							"FROM checklist_questions LEFT OUTER JOIN review_type ON review_type.id = review_id WHERE checklist_questions.id = ".$question_id." ORDER BY review_id ASC";
    	$result = A("db:".$questions_query)->fetch(PDO::FETCH_OBJ);
    	$this->id = $result->id;
    	$this->item = $result->item_order;
    	$this->type = "";
    	$this->tag = $result->tag;
    	$this->text = $result->question;
    	$this->review_id = $result->review_id;
	}
	public static function getQuestionsList($checklist_id,$company_id=""){
		Atomik::needed('Tool.class');
		$which_checklist = Tool::setFilter("review_id",$checklist_id);
		$which_company = Tool::setFilter("company_id",$company_id);
		$questions_query = "SELECT checklist_questions.id,".
								"review_type.description,".
								"tag,".
								"question,".
								"review_type.type as acronym,".
								"item_order FROM checklist_questions ".
								"LEFT OUTER JOIN review_type ON review_type.id = review_id ".
								"LEFT OUTER JOIN enterprises ON enterprises.id = review_type.company_id ".
								"WHERE checklist_questions.id IS NOT NULL ".
								$which_checklist.
								$which_company.
								" ORDER BY review_id ASC,item_order ASC";						
		$list_questions = A('db:'.$questions_query);						
		return($list_questions);						
	}
    public function __construct() {
			$this->id ="";
			$this->tag = "";
			$this->item = "";
			$this->type = "";
			$this->review_id = "";
			$this->text = "Add your question here.";	
    }
}
class ReviewTypeHtml extends Type {
    public function html_display (){
        $this->objectives = str_replace("\n","<br/>",$this->objectives);
        $this->inputs = str_replace("\n","<br/>",$this->inputs);
        $this->activities = str_replace("\n","<br/>",$this->activities);
        $this->outputs = str_replace("\n","<br/>",$this->outputs);
    }
}
?>
