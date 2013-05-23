<?php
/*
 * Class User
 */   
class User {
	private $db;
	public $company_id;
	private $aircraft_id;
	private $project_id;
	private $sub_project_id;
	private $status_id;
	private $criticality_id;
	private $assignee_id;
	private $review_id;
	private $baseline_id;	
	private $search;
	private $order;
	private $first_letter;
	private $which_company;
	private $which_aircraft;
	private $which_project;
	private $which_assigned_project;
	private $which_sub_project;
	private $which_status;
	private $which_criticality;
	private $which_assignee;
	private $which_review;
	private $which_letter;
	private $which_baseline;
	private $search_query;
	public $fname;
	public $lname;
	public $name;
	public $username;
	public $dismissed;
	public $user_function;
	public $service;
	public $service_acronym;
	public $company_name;
	private $user_logged_company;
	private $overview;
	private $property;
	public $email;
	public $phone;
	public $folder;
	public $user_right;
	public $photo_file;
	public $thumbnail;
	public $password;
  	public $projects;
  	public $id;
  	public $nb;
	public $nb_closed;
	public $poster_tab;
	public $poster_nb_tab;
	public $index_poster;
	public $name_serial;
	public $nb_serial;
	public $nb_actions;
	public $nb_closed_serial;
      
	function __construct($context=null) {
		Atomik::needed('Db.class');
		$this->db = new Db;
		if($context!= null){
			$this->company_id = isset($context['company_id'])?$context['company_id']:"";
			$this->aircraft_id = isset($context['aircraft_id'])?$context['aircraft_id']:"";
			$this->project_id = isset($context['project_id'])?$context['project_id']:"";
			$this->sub_project_id = isset($context['sub_project_id'])?$context['sub_project_id']:"";	
			$this->status_id= isset($context['status_id'])?$context['status_id']:"";	
			$this->criticality_id= isset($context['criticality_id'])?$context['criticality_id']:"";	
			$this->assignee_id= isset($context['assignee_id'])?$context['assignee_id']:"";	
			$this->review_id= isset($context['review_id'])?$context['review_id']:"";	
			$this->baseline_id= isset($context['baseline_id'])?$context['baseline_id']:"";	
			$this->search= isset($context['user_search'])?$context['user_search']:Atomik::get('session/search');;	
			$this->order= isset($context['order'])?$context['order']:"";
			$this->first_letter		= isset($context['first_letter']) ? $context['first_letter'] : "";
			Atomik::needed('Tool.class');
			$this->which_company 		= Tool::setFilter("enterprise_id",$this->company_id);
			$this->which_assigned_project 	= Tool::setFilter("project_id",$this->project_id);
			$this->which_aircraft		= Tool::setFilter("aircrafts.id",$this->aircraft_id);
			$this->which_project 		= Tool::setFilter("projects.id",$this->project_id);
			$this->which_sub_project 	= Tool::setFilter("lrus.id",$this->sub_project_id);
			$this->which_status 	   	= Tool::setFilter("actions.status",$this->status_id);
			$this->which_criticality 	= Tool::setFilter("criticality",$this->criticality_id);
			$this->which_assignee 		= Tool::setFilter("posted_by",$this->assignee_id);
			$this->which_review 		= Tool::setFilter("review",$this->review_id);
			$this->which_baseline 		= Tool::setFilter("baselines.id",$this->baseline_id);
			if ($this->first_letter == ""){
				$this->which_letter = "";
			}
			else {
				$this->which_letter  = " AND (bug_users.lname REGEXP '^".$this->first_letter."')";
			}				
			if ($this->search != ""){
				$this->search_query = " AND ((bug_users.fname LIKE '%$this->search%') OR (bug_users.lname LIKE '%$this->search%') OR (bug_users.function LIKE '%$this->search%')) ";
			}
		}
		else{
			$id = self::getIdUserLogged();
			$this->get_user_info($id);
		}
		$this->user_logged_company = self::getCompanyUserLogged();
	}
	public function prepare(){
		$sql_query = $this->get_list_poster();
		// cancelled actions are not shown but still exists in the db
		$sql_query .= "  LIMIT :debut,:nombre";  //id ASC id ASC
		unset($this->prepare);
		$this->prepare = $this->db->db_prepare($sql_query);	
	}
	public function execute($start,$how_many){
		$this->prepare->bindParam(':debut', $start, PDO::PARAM_INT);
		$this->prepare->bindParam(':nombre', $how_many, PDO::PARAM_INT);
		$this->prepare->execute(); //array($start,$how_many)
		$list = $this->prepare->fetchAll(PDO::FETCH_ASSOC);
		return($list);
	}	

	public function new_get_amount_of_actions ($user_id,$status_id="8") {
	   $sql_query = "SELECT * FROM bug_users ".
					 " LEFT OUTER JOIN actions ON actions.posted_by = bug_users.id ".
					 " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
					 " LEFT OUTER JOIN aircrafts ON reviews.aircraft = aircrafts.id ".
					 " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
					 " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
					 " LEFT OUTER JOIN projects ON actions.project = projects.id ".
					 " LEFT OUTER JOIN lrus ON actions.lru = lrus.id ".
					 " WHERE actions.criticality != 14 AND bug_users.id = {$user_id}".
					 " AND actions.status = {$status_id}".
					   $this->which_aircraft.
					   $this->which_project.
					   $this->which_assignee.
					   $this->which_sub_project.
					   $this->which_review.
					   $this->which_criticality.
					   $this->which_baseline.
					   $this->search_query;
		$result = $this->db->db_query($sql_query);
		$list   = $result->fetchAll(PDO::FETCH_OBJ);	
		$nb_remarks= count($list) ;
		return($nb_remarks);
  }  
 	public function action_get_list_poster (){
		 $sql_query = "SELECT DISTINCT (bug_users.id), ".
					"bug_users.lname,".
					"bug_users.fname,".
					"bug_users.function,".
					"bug_users.enterprise_id,".
					"bug_users.email ".
					"FROM bug_users ".
					 " LEFT OUTER JOIN actions ON actions.posted_by = bug_users.id ".
					 " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
					 " LEFT OUTER JOIN aircrafts ON reviews.aircraft = aircrafts.id ".
					 " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
					 " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
					 " LEFT OUTER JOIN projects ON actions.project = projects.id ".
					 " LEFT OUTER JOIN lrus ON actions.lru = lrus.id ".
					 // " WHERE bug_users.id != NULL ".
					 " WHERE actions.status != 16 ".
					   $this->which_status.
					   $this->which_company.
					   $this->which_aircraft.
					   $this->which_project.
					   $this->which_assignee.
					   $this->which_sub_project.
					   $this->which_review.
					   $this->which_criticality.
					   $this->which_baseline.
					   $this->which_letter.
					   $this->search_query.
					   " ORDER BY lname ASC" ;
		$result = $this->db->db_query($sql_query);
		$list   = $result->fetchAll(PDO::FETCH_OBJ);
		/* Make obscured */
		if ($this->user_logged_company != "ECE"){
			foreach($list as $key => &$user):
				$user->lname = $this->encode_rot13($user->lname);
				$user->fname = $this->encode_rot13($user->fname);
				$user->function = $this->encode_rot13($user->function);
			endforeach;
		}
		return($list);
  } 
 	public function get_list_poster (){
		 $sql_query = "SELECT DISTINCT (bug_users.id)".
										/*"bug_users.lname,".
										"bug_users.fname,".
										"bug_users.function,".
										"bug_users.enterprise_id,".
										"bug_users.email ".*/
										"FROM bug_users ".
										 " LEFT OUTER JOIN user_join_project ON user_join_project.user_id = bug_users.id ".
										 " WHERE bug_users.id IS NOT NULL ".
										   $this->which_company.
										   $this->which_assigned_project.
										   $this->which_letter.
										   $this->search_query.
										   " ORDER BY lname ASC";
		// echo $sql_query;			   
		return($sql_query);
  }   
  public function getUsersList(){
    $sql_query = $this->get_list_poster ();
	$result = $this->db->db_query($sql_query);
	$list   = $result->fetchAll(PDO::FETCH_OBJ);
	return ($list);
  }
   public function get_stat_actions($exclude_users=false) {
		$list = $this->action_get_list_poster ();  
		/* amount of rows */	
		$this->nb=count($list); 
		if ($this->nb != 0) {
			$this->index_poster = 0;
			foreach($list as $row):
					// $poster = $row->fname." ".$row->lname;
					/* get all actions open*/
					$open_actions = $this->new_get_amount_of_actions($row->id);
					/* get all actions closed */
					$closed_actions = $this->new_get_amount_of_actions($row->id,9);
					/* all actions closed */
					if (($open_actions != 0)||($exclude_users == false)){
						$poster = User::getTrigram($row->fname,$row->lname);
						$this->poster_tab[$poster]=$row->function;
						$data[$this->index_poster] = $poster;
						$nb[$this->index_poster] = $open_actions;
						$nb_closed[$this->index_poster] = $closed_actions;
						$this->index_poster++;						
					}
			 endforeach;
			 if ($data == null){
				$this->name = array();
			 }
			 $this->name = $data;
			 $this->nb_actions = $nb;
			 $this->nb_closed = $nb_closed;	
			 $this->name_serial = urlencode(serialize($data));
			 $this->nb_serial = urlencode(serialize($nb));
			 $this->nb_closed_serial = urlencode(serialize($nb_closed));
		}
		else{
			$this->nb_actions = 0;
			$this->nb_closed = 0;	
		}
	} 
	public function getEmail($user_id){
		$sql_query = "SELECT email FROM `bug_users` WHERE `id` = ".$user_id;
		$result = $this->db->db_query($sql_query);
		$row = $result->fetch(PDO::FETCH_OBJ);
		return($row->email);
	}
	public function getTel($user_id){
		$sql_query = "SELECT telephone FROM `bug_users` WHERE `id` = ".$user_id;
		$result = $this->db->db_query($sql_query);
		$row = $result->fetch(PDO::FETCH_OBJ);
		return($row->telephone);
	}	
	public static function getAdminEmail(){
		$result = Atomik_Db::find('bug_users',array('id'=>1));
		return($result['email']);
	}
	public static function getName($id){
		$result = Atomik_Db::find('bug_users',array('id'=>$id));
		if ($result === false){
			$user="Anonymous";
		}
		else{
			$user=$result['fname']." ".$result['lname'];
		}
		return($user);
	}
	public static function getCompanyUserLogged(){
			$sql_query = "SELECT enterprises.name as enterprise FROM bug_users ".
						"LEFT OUTER JOIN enterprises ON enterprises.id = enterprise_id ".
						" WHERE bug_users.id = ".self::getIdUserLogged().
						" LIMIT 1";		
		$result = A("db:".$sql_query);
		if (($result !== false) AND ($result != null)){
			$row = $result->fetch(PDO::FETCH_OBJ);
			if ($row !== false){
				$company = $row->enterprise;
			}
			else{
				$company = "";
			}			
		}
		else{
			$company = "";
		}
		return($company);		
	}		
	public static function getEmailUserLogged(){
		if (isset($_COOKIE["bug_cookie"])){
			$cookie = unserialize(stripslashes($_COOKIE['bug_cookie']));
			$info = $cookie[3];
		}
		else {
			$info = "";
		}
		return($info);
	}
	public static function isAnonymous(){
		if (isset($_COOKIE["bug_cookie"])){
			$cookie = unserialize(stripslashes($_COOKIE['bug_cookie']));
			$login = $cookie[0];
			if ($login == "anonymous"){
				$result= true;
			}
			else{
				$result= false;
			}
		}
		else{
			$result= false;
		}
		return($result);
	}	
	public static function isUserLogged(){
		if (isset($_COOKIE["bug_cookie"])){
			$cookie = unserialize(stripslashes($_COOKIE['bug_cookie']));
			$login = $cookie[0];
//			if ($login == "anonymous"){
//				$result= false;
//			}
//			else{
				$result= true;
//			}
		}
		else{
			$result= false;
		}
		return($result);
	}
	public static function getNameUserLogged(){
		if (isset($_COOKIE["bug_cookie"])){
			$cookie = unserialize(stripslashes($_COOKIE['bug_cookie']));
			$info = $cookie[1]." ".$cookie[2];
		}
		else {
			$info = "Unknown";
		}
		return($info);
	}	
	public static function getIdUserLogged(){
		if (isset($_COOKIE["bug_cookie"])){
			$cookie = unserialize(stripslashes($_COOKIE['bug_cookie']));
			$info = $cookie[6];
		}
		else {
			$info = 1;
		}
		return($info);
	}
	public static function getAdminUserLogged(){
		if (isset($_COOKIE["bug_cookie"])){
			$cookie = unserialize(stripslashes($_COOKIE['bug_cookie']));
			if($cookie[4] == 1) {  
				$info = true;
			}
			else{
				$info = false;
			}
		}
		else {
			$info = false;
		}
		return($info);
	}		
	public function getDatabase($user_id){
		$sql_query = "SELECT lotus_database FROM `bug_users` WHERE `id` = ".$user_id;
		$result = $this->db->db_query($sql_query);
		$row = $result->fetch(PDO::FETCH_OBJ);
		return($row->lotus_database);
	}
	public function getPassword($user_id){
		$sql_query = "SELECT password FROM `bug_users` WHERE `id` = ".$user_id;
		$result = $this->db->db_query($sql_query);
		$row = $result->fetch(PDO::FETCH_OBJ);
		return($row->password);
	}		
	public function getProjects($user_id=""){
		if ($user_id!=""){
			/*
			* Get project whom the user is assigned
			*/
			$sql_query = "SELECT user_join_project.id,projects.project,aircrafts.name as aircraft, enterprises.name as company FROM bug_users ".
						"LEFT OUTER JOIN user_join_project ON bug_users.id = user_join_project.user_id ".
						"LEFT OUTER JOIN projects ON projects.id = user_join_project.project_id ".
						"LEFT OUTER JOIN aircrafts ON aircrafts.id = projects.aircraft_id ".
						"LEFT OUTER JOIN enterprises ON enterprises.id = aircrafts.company_id ".
						" WHERE bug_users.id = {$user_id}".
						" AND user_join_project.id IS NOT NULL ORDER BY company ASC, aircraft ASC";
			// echo  $sql_query;
			$result = $this->db->db_query($sql_query);
			$list_projects = $result->fetchAll();
			if (count($list_projects)==0){
				$list_projects=null;
			}
			else{
				if ($this->user_logged_company != "ECE"){
					foreach($list_projects as $key => &$project):
						$project['company'] = $this->encode_rot13($project['company']);
						$project['aircraft'] = $this->encode_rot13($project['aircraft']);
						$project['project'] = $this->encode_rot13($project['project']);	
					endforeach;
				}
			}
		}
		else{
			$list_projects=null;
		}
	
		return($list_projects);	
	}	
	
	public static function getFirstLetter($text){
		if (preg_match("#^[A-Za-z]#",$text,$match)){
			/* Uppper case */
			$first_letter = isset($match[0])?ucfirst($match[0]):"";
		}
		else{
			$first_letter = "";
		}	
		return($first_letter);
	}
	public static function getSecondLetter($text){
		if (preg_match("#^[A-Za-z]([A-Za-z])#",$text,$match)){
			/* Uppper case */
			$first_letter = isset($match[1])?ucfirst($match[1]):"";
		}
		else{
			$first_letter = "";
		}	
		return($first_letter);
	}	
	public static function getAcronym($fname,$lname){
		$first_letter = User::getFirstLetter($fname);
		$second_letter = User::getFirstLetter($lname);
		$acronym = $first_letter.$second_letter;
		return($acronym);
	}
	public static function getTrigram($fname,$lname){
		$first_letter = User::getFirstLetter($fname);
		$second_letter = User::getFirstLetter($lname);
		$third_letter = User::getSecondLetter($lname);
		$trigram = $first_letter.$second_letter.$third_letter;
		return($trigram);
	}
	public function encode_rot13($txt){
		if ($this->user_logged_company != "ECE"){
			$result = str_rot13($txt);
		}
		else{
			$result = $txt;
		}
		return $result;
	}
	public function get_user_info($user_id=""){
		$result = false;
		if ($user_id != ""){
			$sql_query = "SELECT is_admin,".
						"fname,".
						"lname,".
						"username,".
						"password,".
						"dismissed,".
						"email,".
						"telephone,".
						"folder,".
						"enterprise_id,".
						"function ,".
						"overview ,".
						"property ,".
						"enterprises.name as enterprise,".
						"departments.name as service,".
						"departments.acronym ".
						"FROM bug_users ".
						"LEFT OUTER JOIN enterprises ON enterprises.id = enterprise_id ".
						"LEFT OUTER JOIN departments ON departments.id = bug_users.department_id ".
						" WHERE bug_users.id = {$user_id}".
						" ORDER BY `bug_users`.`lname` ASC";
			$result = $this->db->db_query($sql_query);
		}
		if ($result !== false) {
			$row = $result->fetch(PDO::FETCH_ASSOC);
			$this->id = $user_id;
			$this->company_name = $this->encode_rot13($row['enterprise']);		
			$this->fname = $this->encode_rot13($row['fname']);
			$this->lname = $this->encode_rot13($row['lname']);
			$this->name = $this->fname." ".$this->lname;
			/* acronym */
			$this->acronym = User::getAcronym($this->fname,$this->lname);			
			$this->username = $row['username'];
			$this->dismissed = $row['dismissed'];
			$this->password = $row['password'];
			$this->user_function = $this->encode_rot13($row['function']);
			$this->overview = $row['overview'];
			$this->property = $row['property'];
			
			$this->company_id = $row['enterprise_id'];
			$this->service = $row['service'];
			$this->service_acronym = $row['acronym'];
			$this->email = $this->encode_rot13($row['email']);
			$this->phone = $row['telephone'];
			$this->folder = $row['folder'];
			$this->user_right = $row['is_admin'];
			/*
			 * Get picture 
			 */
			 $img_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"assets".DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."photos".DIRECTORY_SEPARATOR;
						
			 if ($this->user_logged_company != "ECE"){
				$image_name = "90px-Lakeyboy_Silhouette.PNG";
				$thumbnail_name = "90px-Lakeyboy_Silhouette.PNG";				 	
			 }
			 else if (file_exists($img_path.DIRECTORY_SEPARATOR.$user_id.".png")) {
				$image_name = $user_id.".png";
				$thumbnail_name = $user_id."_tb.png";									
			 }
			 else if (file_exists($img_path.DIRECTORY_SEPARATOR.$user_id.".jpg")){
				$image_name = $user_id.".jpg";
				$thumbnail_name = $user_id."_tb.jpg";				
			 }
			 else if (file_exists($img_path.DIRECTORY_SEPARATOR.$user_id.".jpeg")){
				$image_name = $user_id.".jpeg";
				$thumbnail_name = $user_id."_tb.jpeg";				
			 }			 
			 else {
				$image_name = "90px-Lakeyboy_Silhouette.PNG";
				$thumbnail_name = "90px-Lakeyboy_Silhouette.PNG";			 	
			 }
			 $this->photo_file=Atomik::asset("assets/images/photos/".$image_name);
			 $this->thumbnail=Atomik::asset("assets/images/photos/".$thumbnail_name);
			/*
			* Get project whom the user is assigned
			*/
			$this->projects=$this->getProjects($user_id);
        }
		else {
			$this->id = "";
			$this->fname = "";
			$this->lname = "";
			$this->username = "";
			$this->password = "";
			$this->dismissed = "";
			$this->user_function = "";
			$this->overview = "";
			$this->property = "";
			$this->company_id = 1;
			$this->email = "";
			$this->phone = "";
			$this->folder = "";
			$this->user_right = 0;
			$this->photo_file=Atomik::asset("assets/images/90px-Lakeyboy_Silhouette.PNG");			
		}
	}
	public function getFullName(){
		return($this->name);
	}	
	public function getProperty(){
		return($this->property);
	}	
	public function getOverview(){
		return($this->overview);
	}
	public function getAdmin(){
		return($this->user_right);
	}
	public function getFolder(){
		return(utf8_decode($this->folder));
	}	
	public function getActive(){
		if ($this->dismissed == 0){
			$result = true;
		}
		else{
			$result = false;
		}
		return($result);
	}
	public function getUserByName($username){
		$sql_query = "SELECT * FROM bug_users WHERE username = '$username'";
		$user = $this->db->db_query($sql_query)->fetch();
		return($user);
	}
	/* This function adds a new user to the database */
	public static function add_user($info) {
	    $fname 				= isset($info['add_user_fname'])?$info['add_user_fname']:"";
	    $lname 				= isset($info['add_user_lname'])?$info['add_user_lname']:"";
	    $uname 				= isset($info['add_user_username'])?$info['add_user_username']:"";
		$enterprise 		= isset($info['add_user_enterprise'])?$info['add_user_enterprise']:"";
		$filter_letter 		= isset($info['filter_letter'])?$info['filter_letter']:"";
		$filter_company 	= isset($info['filter_company'])?$info['filter_company']:"";
		$function 			= isset($info['add_user_function'])?$info['add_user_function']:"";
	    $email 				= isset($info['add_user_email'])?$info['add_user_email']:"";;
	    $pass 				= isset($info['add_user_pass'])?$info['add_user_pass']:"";
	    $is_admin 			= isset($info['add_user_admin'])?$info['add_user_admin']:"";
	    $is_active 			= isset($info['user_activity'])?!$info['user_activity']:"";
		$phone 				= isset($info['add_user_tel'])?$info['add_user_tel']:"";
		$folder 			= stripslashes(isset($info['folder'])?$info['folder']:A('db_config/backup_dir'));
		$overview 			= isset($info['overview'])?$info['overview']:"";
	
	    //$sql_query = "INSERT INTO bug_users (fname, lname, username, enterprise_id,function, email, password, is_admin, telephone, dismissed, overview,folder) ".
	    //						 "VALUES ('$fname', '$lname', '$uname', '$enterprise', '$function', '$email', '$pass', '$is_admin', '$phone','$is_active','$overview','$folder')";
	    // echo $sql_query;
		// $result = A("db:".$sql_query);
		$result = Atomik_Db::insert('bug_users',array('fname'=>$fname,
													'lname'=>$lname,											
													'username'=>$uname,
													'enterprise_id'=>$enterprise,
													'function'=>$function,
													'email'=>$email,
													'password'=>$pass,
													'is_admin'=>$is_admin,
													'telephone'=>$phone,
													'dismissed'=>$is_active,
													'overview'=>$overview,
													'folder'=>$folder));
		return($result);
	}
	/* This function update an user to the database */
	public static function update_user($info) {
		$user_id			= isset($info['edit_user_id'])?$info['edit_user_id']:"";
	    $fname 				= isset($info['add_user_fname'])?$info['add_user_fname']:"";
	    $lname 				= isset($info['add_user_lname'])?$info['add_user_lname']:"";
	    $uname 				= isset($info['add_user_username'])?$info['add_user_username']:"";
		$enterprise 		= isset($info['add_user_enterprise'])?$info['add_user_enterprise']:"";
		$filter_letter 		= isset($info['filter_letter'])?$info['filter_letter']:"";
		$filter_company 	= isset($info['filter_company'])?$info['filter_company']:"";
		$function 			= isset($info['add_user_function'])?$info['add_user_function']:"";
	    $email 				= isset($info['add_user_email'])?$info['add_user_email']:"";;
	    $pass 				= isset($info['add_user_pass'])?$info['add_user_pass']:"";
	    $is_admin 			= isset($info['add_user_admin'])?$info['add_user_admin']:"";
		$is_active 			= isset($info['user_activity'])?!$info['user_activity']:"";
		$phone 				= isset($info['add_user_tel'])?$info['add_user_tel']:"";
		$folder 			= stripslashes(isset($info['folder'])?$info['folder']:"");
		$overview 			= isset($info['overview'])?$info['overview']:"";		

		$result = Atomik_Db::update('bug_users',array('fname'=>$fname,
											'lname'=>$lname,
											'username'=>$uname,
											'enterprise_id'=>$enterprise,
											'function'=>$function,
											'email'=>$email,
											'password'=>$pass,
											'is_admin'=>$is_admin,
											'telephone'=>$phone,
											'dismissed'=>$is_active,
											'overview'=>$overview,
											'folder'=>$folder),array('id'=>$user_id));

		return($result);
	}	
	/* This function removes an application form the menu */
	public static function delete_user($id) {
	    $sql = "DELETE FROM bug_users WHERE $id=id";
		$result = A("db:".$sql);
	    if(!$result) {
	        print "Could not delete application: ".mysql_error();
			$res=false;
	    }
	    else {
	        //print "User deleted!";
			$res=true;
	        //print "<script language='javascript' type='text/javascript'>document.location='display_users.php'</script>";
	    }
		return($res);	
	}
	public static function getSelectAssignee ($project,$selected,$onchange="inactive"){
		$html ='<label for="show_poster">'.A('menu/assignee').':</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html.= ' name="show_poster">';
		$html.='<option value=""/> --All--';
		foreach($project->getUsers() as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['lname']." ".$row['fname'].":  ".$row['function'];
		endforeach;
		$html .='</select>';
		return($html);
	}
	public static function getLiteName($first_name,$last_name){
		  if ($first_name!=""){
				if ($last_name!=""){
					$author_lite = self::getFirstLetter($first_name).". ".$last_name;
				}
				else{
					$author_lite = $first_name;
				}
		  }
		  else{
				$author_lite = $last_name;
		  }
		  if (self::getCompanyUserLogged() != "ECE"){
			$author_lite = str_rot13($author_lite);
		  }
		  return($author_lite);	
	}
	public function find_poster($reader,$client=array()) {
		
		/* filter companies */
		$counter = 0;
		$found_poster = false;
		$list = array();
		foreach ($client as $company):
			if ($counter == 0) {
				$where_company=" AND (enterprise_id = ".$company;
			}
			else {
				$where_company .=" OR enterprise_id = ".$company;
			}
			$counter++;
		endforeach;
		$where_company .=")";
		/* This gets the poster's id  from name */
		$result = preg_match("#[\w|é|è|\(|\)]+$#", $reader,$reader_lite);
		if ($result) {
			/* Is this a digram ? */
			$result = preg_match("#^([A-Z])([A-Z])$#", $reader,$match);
			if ($result) {
				/* Yes */
				/* select reader in priority from a company */
				$sql_get_id = "SELECT id,fname,lname FROM bug_users WHERE (lname LIKE '%".$match[2]."%' OR fname LIKE '%".$match[1]."%')".$where_company;
				$sql_get_id.= " UNION SELECT id,fname,lname FROM bug_users WHERE (lname LIKE '%".$match[2]."%' OR fname LIKE '%".$match[1]."%')";					
			}
			else{
				/* No - La recherche est sensible à la casse il faudrait la rendre insensible */
				/* select reader in priority from a company */
				$sql_get_id = "SELECT id,fname,lname FROM bug_users WHERE (lname LIKE '%".$reader_lite[0]."%' OR fname LIKE '%".$reader_lite[0]."%')".$where_company;
				$sql_get_id.= " UNION SELECT id,fname,lname FROM bug_users WHERE (lname LIKE '%".$reader_lite[0]."%' OR fname LIKE '%".$reader_lite[0]."%')";			
			}
	  		// echo $sql_get_id."<BR>";
			$result = A('db:'.$sql_get_id);
			if ($result !== false){
				$list = $result->fetch();
				if ($list !== false){
					/* amount of rows */
					$found_poster=count($list);
				}
				else{
					$found_poster = false;
				}				
			}
			else{
				$list = false;
				$found_poster = false;
			}
		}
		else {
			if(preg_match("#(^\w)(\w)(\w?)$#", $reader,$reader_lite))
			{
				//foreach ($reader_lite as $val) {
				//echo "2)".$reader_lite[0]."<BR>";
				//}
			}
			if ((isset($reader_lite[1]))&&($reader_lite[1] != "")) {
				$sql_get_id = "SELECT id,fname,lname FROM bug_users WHERE fname REGEXP '^".$reader_lite[1]."' AND lname REGEXP '^".$reader_lite[2]."'".$where_company;
				if ($result !== false){
					$list = $result->fetch();		
					if ($list !== false){
						/* amount of rows */
						$found_poster=count($list);
					}
					else{
						$found_poster = false;
					}
				}
				else{
					$list = false;
					$found_poster = false;
				}
			}
			else {
				$found_poster = false;
			}				
    	}
		if ($found_poster !== false) {
			$this->id=$list['id'];
			$this->name = $list['fname']." ".$list['lname'];
			//echo "1)".$reader."<BR>";
		}
		else {
				$this->name=$reader." is unknown";
				//print "For remark ".$ref_id." could not establish poster id for ".$reader.", select ".$fuser[fname]." ".$luser[lname]." by default : ".mysql_error()."<BR>";
				$this->id="";
		}	
		return($found_poster);
	}	
}
