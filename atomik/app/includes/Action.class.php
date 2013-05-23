<?php
/**
 * QAMS Framework
 * Copyright (c) 2009-2012 Olivier Appere
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     Action.class
 * @author      Olivier Appere
 * @copyright   2009-2013(c) Olivier Appere
 * @license     http://www.opensource.org/licenses/mit-license.php
 * @link        
 */

/**
 * Handle action
 *
 * @package Action.class
 */
class Action {
	private static $id_deadline;
	private static $id_close;
	private static $id_open;
	private static $id_propose;
	private $env_context;
	private $db;
	private $aircraft_id;
	private $project_id;
	private $sub_project_id;
	private $status_id;
	private $criticality_id;
	private $criticality;
	private $assignee_id;
	private $submitter_id;
	private $review_id;
	private $baseline_id;	
	private $search;
	private $order;
	private $which_aircraft;
	private $which_project;
	private $which_sub_project;
	private $which_status;
	private $which_criticality;
	private $which_assignee;
	private $which_review;
	private $which_baseline;
	private $search_query;
	private $today_date;
	private $description;
	public $who_is_logged; 	
    public $id;
	public $context;
	public $date_open_dojo;
	public $date_expected_dojo;
	private $date_open_sql;
	public $date_open;
	public $date_expected;
	public $date_closure;
	private $attendee;
	private $poster;
    public $status;
    public $project;
    public $lru;
	public $review;
	public $response;
	public $deadline_over;
	public $link;
	public $link_mime;
	public $attachment_name;
	private $prepare;
	private $review_type;
	
	function compute_deadline () {
	    /* remove time */
		//$cut_text = substr($this->date_expected,0,10);
		$cut_text = $this->date_expected;
		/* convert from string to time format */
		$deadline_convert = strtotime($cut_text);
		$today = date("Y").date("m").date("d");
		$Jour = date("d", $deadline_convert);
		$Mois = date("m", $deadline_convert);
		$Annee = date("Y", $deadline_convert);
		$deadline = $Annee.$Mois.$Jour;
		/* a t'on depasse la deadline ?*/
		if (($today>$deadline) && ($this->status != "Closed"))  {
		    $this->deadline_over = true;
		}
			else{
		    $this->deadline_over = false;
		}
	}
	public function getDeadlineOver(){
		$this->compute_deadline();
		return($this->deadline_over);
	}	
	function __construct($context=null) {
		Atomik::needed("Tool.class");
		Atomik::needed('User.class');
		Atomik::needed('Project.class');
		if ($context != null) {
			$this->aircraft_id = isset($context['aircraft_id'])? $context['aircraft_id'] : "";
			$this->project_id = isset($context['project_id'])? $context['project_id'] : "";
			$this->sub_project_id = isset($context['sub_project_id'])? $context['sub_project_id'] : "";	
			$this->status_id= isset($context['action_status_id'])? $context['action_status_id'] : "";		
			$this->criticality_id= isset($context['criticality_id'])? $context['criticality_id'] : "";			
			$this->assignee_id= isset($context['user_id'])? $context['user_id'] : "";
			$this->submitter_id= isset($context['submitter_id'])? $context['submitter_id'] : "";			
			$this->review_id= isset($context['review_id'])? $context['review_id'] : "";	
			$this->baseline_id= isset($context['baseline_id'])? $context['baseline_id'] : "";	
			$this->search= isset($context['action_search'])? $context['action_search'] : "";
			$this->order= isset($context['order'])? $context['order'] : "";
			
			$this->which_aircraft 		= Tool::setFilter("aircrafts.id",$this->aircraft_id);
			$this->which_project 		= Tool::setFilter("projects.id",$this->project_id);
			$this->which_sub_project 	= Tool::setFilter("lrus.id",$this->sub_project_id);
			$this->which_status 	   	= Tool::setFilter("actions.status",$this->status_id);
			$this->which_criticality 	= Tool::setFilter("criticality",$this->criticality_id);
			$this->which_assignee 		= Tool::setFilter("posted_by",$this->assignee_id);
			$this->which_submitter 		= Tool::setFilter("assignee",$this->submitter_id);
			$this->which_review 		= Tool::setFilter("review",$this->review_id);
			$this->which_baseline 		= Tool::setFilter("baselines.id",$this->baseline_id);
			if ($this->search != ""){
				$this->search_query 		= "AND ((actions.context LIKE '%".$this->search."%') OR (actions.id LIKE '%".$this->search."%') OR (actions.Description LIKE '%".$this->search."%')) ";
			}	
			self::$id_deadline 	= "cid:id_deadline";
			self::$id_close 	= "cid:id_close";
			self::$id_open 		= "cid:id_open";
			self::$id_propose	= "cid:id_propose";
			$this->today_date 	= date("d M Y");	
			$this->env_context 	= $context;
		}
		else{
			$this->review_id="";
		}
		if ($this->status_id == "")$this->status_id = 8; /* OPEN */	
		if ($this->criticality_id == "")$this->criticality_id = 10; /* ACTION */
		if ($this->assignee_id == ""){
			if ($this->sub_project_id != ""){
				/* get ID of the manager of the board */
				$project = new Project;
				$project->getSubProject($this->sub_project_id);
				$this->assignee_id = $project->manager_id;
			}
			else{
				$this->assignee_id = (int)User::getIdUserLogged();
			}
		}
		Atomik::needed('Db.class');
		$this->db = new Db;
	}
	public function prepare(){
		// cancelled actions are not shown but still exists in the db
		$sql_query = $this->sort_actions()."  LIMIT :debut,:nombre";  //id ASC id ASC
		unset($this->prepare);
		$this->prepare = $this->db->db_prepare($sql_query);	
	}
	public function execute($start,$how_many){
		$this->prepare->bindParam(':debut', $start, PDO::PARAM_INT);
		$this->prepare->bindParam(':nombre', $how_many, PDO::PARAM_INT);
		$this->prepare->execute(); //array($start,$how_many)
		$list_actions = $this->prepare->fetchAll(PDO::FETCH_ASSOC);
		return($list_actions);
	}
	public function getActions($option=null){
		$sql_query = $this->sort_actions($option);
		$result = $this->db->db_query($sql_query);
		//echo $sql_query."<br/>";
		$list_actions = $result->fetchAll(PDO::FETCH_ASSOC);
		return($list_actions);
	}
	public function getActionsClosed($date){
	/* get actions where date of creation is before input date */
	/* nb actions */
	/* nb actions closed */
		$sql_query = "SELECT DISTINCT(actions.id) ".
			   " FROM actions".
			   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
			   " LEFT OUTER JOIN aircrafts ON aircrafts.id = reviews.aircraft ".
			   " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
			   " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
			   " LEFT OUTER JOIN bug_users ON bug_users.id = actions.posted_by ".
			   " LEFT OUTER JOIN lrus ON lrus.id = actions.lru ".
			   " LEFT OUTER JOIN projects ON projects.id = actions.project ".
			   " LEFT OUTER JOIN bug_status ON bug_status.id = actions.status ".
			   " LEFT OUTER JOIN bug_criticality ON bug_criticality.level = actions.criticality ".
			   "WHERE actions.status = 9 AND `date_open` <= '{$date}' AND `date_closure` <= '{$date}' ".
			   $this->which_status.
			   $this->which_aircraft.
			   $this->which_project.
			   $this->which_assignee.
			   $this->which_submitter.
			   $this->which_sub_project.
			   $this->which_review.
			   $this->which_criticality.
			   $this->which_baseline.
			   $this->search_query.
			   " GROUP BY actions.id ";  
		// echo $sql_query;
		$result = A("db:".$sql_query);
		if ($result){
			$actions = $result->fetchall();
		}
		else {
			$actions = false;
		}   
		return($actions);	
	}	
	public function getActionsOpen($date){
	/* get actions where date of creation is before input date */
	/* nb actions */
	/* nb actions open */
		$sql_query = "SELECT DISTINCT(actions.id) ".
			   " FROM actions".
			   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
			   " LEFT OUTER JOIN aircrafts ON aircrafts.id = reviews.aircraft ".
			   " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
			   " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
			   " LEFT OUTER JOIN bug_users ON bug_users.id = actions.posted_by ".
			   " LEFT OUTER JOIN lrus ON lrus.id = actions.lru ".
			   " LEFT OUTER JOIN projects ON projects.id = actions.project ".  
			   " LEFT OUTER JOIN bug_status ON bug_status.id = actions.status ".
			   " LEFT OUTER JOIN bug_criticality ON bug_criticality.level = actions.criticality ".
			   "WHERE (((actions.status = 8) AND (`date_open` <= '{$date}')) OR ((actions.status = 9) AND (`date_open` <= '{$date}') AND (`date_closure` > '{$date}'))) ".		   
			   $this->which_status.
			   $this->which_aircraft.
			   $this->which_project.
			   $this->which_assignee.
			   $this->which_submitter.
			   $this->which_sub_project.
			   $this->which_review.
			   $this->which_criticality.
			   $this->which_baseline.
			   $this->search_query.
			   " GROUP BY actions.id ";  
		// echo $sql_query;
		$result = A("db:".$sql_query);
		if ($result){
			$actions = $result->fetchall();
		}
		else {
			$actions = false;
		}   
		return($actions);	
	}
	public function getActionsDeadline($date){
	$today = date("Y-m-d");
	/* get actions where date of creation is before input date */
	/* nb actions */
	/* nb actions deadline */
		$sql_query = "SELECT DISTINCT(actions.id) ".
			   " FROM actions".
			   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
			   " LEFT OUTER JOIN aircrafts ON aircrafts.id = reviews.aircraft ".
			   " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
			   " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
			   " LEFT OUTER JOIN bug_users ON bug_users.id = actions.posted_by ".
			   " LEFT OUTER JOIN lrus ON lrus.id = actions.lru ".
			   " LEFT OUTER JOIN projects ON projects.id = actions.project ".  
			   " LEFT OUTER JOIN bug_status ON bug_status.id = actions.status ".
			   " LEFT OUTER JOIN bug_criticality ON bug_criticality.level = actions.criticality ".
			   "WHERE ((actions.status = 8) AND (`date_open` <= '{$date}') AND (`date_expected` <= '{$date}')) ".		   
			   $this->which_status.
			   $this->which_aircraft.
			   $this->which_project.
			   $this->which_assignee.
			   $this->which_submitter.
			   $this->which_sub_project.
			   $this->which_review.
			   $this->which_criticality.
			   $this->which_baseline.
			   $this->search_query.
			   " GROUP BY actions.id ";  
		// echo $sql_query;
		$result = A("db:".$sql_query);
		if ($result){
			$actions = $result->fetchall();
		}
		else {
			$actions = false;
		}   
		return($actions);	
	}	
	public static function getHotActions($aircraft_id="",$project_id=""){
		$today = date("Y-m-d");
		Atomik::needed('Tool.class');
		Atomik::needed('User.class');
		$which_project = Tool::setFilter("projects.id",$project_id);
		$which_aircraft = Tool::setFilter("aircrafts.id",$aircraft_id);		
		$sql = "SELECT actions.comment,review,actions.id,posted_by,context,actions.Description as description,".
				   " projects.project, lrus.lru, fname, lname, bug_criticality.name as criticality,bug_status.name as status,date_open,date_expected,date_closure".
				   " FROM actions".
				   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
				   " LEFT OUTER JOIN aircrafts ON aircrafts.id = reviews.aircraft ".
				   " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
				   " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
				   " LEFT OUTER JOIN bug_users ON bug_users.id = actions.posted_by ".
				   " LEFT OUTER JOIN lrus ON lrus.id = actions.lru ".
				   " LEFT OUTER JOIN projects ON projects.id = actions.project ".
				   " LEFT OUTER JOIN bug_status ON bug_status.id = actions.status ".
				   " LEFT OUTER JOIN bug_criticality ON bug_criticality.level = actions.criticality ".
				   "WHERE (actions.status != 16 AND actions.status !=9) AND `date_expected` <= '{$today}' ".
				   $which_project.
				   $which_aircraft;
		//echo "TEST: ".$sql."<br/>";
		$result = A("db:".$sql);
		if ($result !== false){
		   $actions_list = $result->fetchall();
		}	
		else{
		   $actions_list = array();
		}	
		/* Make obscured */
		if (User::getCompanyUserLogged() != "ECE"){
			foreach($actions_list as $key => &$action):
				$action['fname'] = str_rot13($action['fname']);
				$action['lname'] = str_rot13($action['lname']);
				$action['description'] = str_rot13($action['description']);
				$action['project'] = str_rot13($action['project']);
				$action['lru'] = str_rot13($action['lru']);
			endforeach;
		}		
		return($actions_list);
	}
	public static function getStatusList(){
		$list = Atomik_Db::findAll('bug_status',"`type` = 'action'","`name` ASC");
		return($list);		
	}
	public function getSeverityList(){
		$list = A("db:SELECT level as id,name,description FROM bug_criticality WHERE `type` = 'action' ORDER BY `name` ASC");
		return($list);		
	}	
	public function new_count_actions($status="") {
		/* Take into account either open actions, closed actions or all actions */
		switch($status){
			case "open":
				$which_status = " AND actions.status != 9 ";
				break;
			case "closed":
				$which_status = " AND actions.status = 9 ";
				break;
			case "all":
			default:
				$which_status = $this->which_status;
				break;
	  	}
		// cancelled actions are not shown but still exists in the db
	    $sql_query = "SELECT DISTINCT(actions.id) ".
				   " FROM actions".
				   " LEFT OUTER JOIN lrus ON lrus.id = actions.lru ".
				   " LEFT OUTER JOIN projects ON projects.id = actions.project ".
				   " LEFT OUTER JOIN bug_users ON bug_users.id = actions.posted_by ".
				   " LEFT OUTER JOIN bug_status ON bug_status.id = actions.status ".
				   " LEFT OUTER JOIN bug_criticality ON bug_criticality.level = actions.criticality ".
				   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
				   " LEFT OUTER JOIN aircrafts ON aircrafts.id = reviews.aircraft ".
				   " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
				   " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
				   " WHERE actions.status !=16 ".
				   $which_status.
				   $this->which_aircraft.
				   $this->which_project.
				   $this->which_assignee.
				   $this->which_submitter.
				   $this->which_sub_project.
				   $this->which_review.
				   $this->which_criticality.
				   $this->which_baseline.
				   $this->search_query; 
		$result = $this->db->db_query($sql_query);
		$nb_actions_tab = $result->fetchAll(PDO::FETCH_ASSOC);
		$nb_actions = count($nb_actions_tab);
	    return($nb_actions);	
	}
	public function countActions($status="") {
		if ($this->review_id != ""){
			$review = new Review;
			$review->get($this->review_id);
			$which_date = " AND '".$review->date_start_sql."' <= reviews.date ";
		}
		else {
			$which_date = "";
		}	
		/* Take into account either open actions, closed actions or all actions */
		switch($status){
			case "open":
				$which_status = " AND actions.status != 9 ";
				break;
			case "closed":
				$which_status = " AND actions.status = 9 ";
				break;
			case "all":
			default:
				$which_status = $this->which_status;
				break;
	  	}
		// cancelled actions are not shown but still exists in the db
	    $sql_query = "SELECT DISTINCT(actions.id) ".
				   " FROM actions".
				   " LEFT OUTER JOIN lrus ON lrus.id = actions.lru ".
				   " LEFT OUTER JOIN projects ON projects.id = actions.project ".
				   " LEFT OUTER JOIN aircrafts ON aircrafts.id = projects.aircraft_id ".
				   " LEFT OUTER JOIN bug_users ON bug_users.id = actions.posted_by ".
				   " LEFT OUTER JOIN bug_status ON bug_status.id = actions.status ".
				   " LEFT OUTER JOIN bug_criticality ON bug_criticality.level = actions.criticality ".
				   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
				   " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
				   " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
				   " WHERE actions.status !=16 ".
				   $which_status.
				   $this->which_aircraft.
				   $this->which_project.
				   $this->which_assignee.
				   $this->which_submitter.
				   $this->which_sub_project.
				   $which_date.
				   $this->which_criticality.
				   $this->which_baseline.
				   $this->search_query; 
		$result = $this->db->db_query($sql_query);
		$nb_actions_tab = $result->fetchAll(PDO::FETCH_ASSOC);
		$nb_actions = count($nb_actions_tab);
	    return($nb_actions);	
	}	
	public function setReview($review_id){
		$this->review_id=$review_id;
		$this->env_context['review_id']=$this->review_id;
		require_once("Tool.class.php");
		$this->which_review = Tool::setFilter("review",$this->review_id);
	}
	public function getReviewId(){
		return($this->review_id);
	}
	public static function getMinutes($id){
		$sql_query = "SELECT application as reference FROM bug_applications LEFT OUTER JOIN data_join_review ON bug_applications.id = data_join_review.data_id LEFT OUTER JOIN reviews ON reviews.id = data_join_review.review_id  LEFT OUTER JOIN actions ON actions.review = reviews.id WHERE actions.id = {$id}";
		$result = A('db:'.$sql_query);
		if ($result != false){
			$row = $result->fetch(PDO::FETCH_OBJ);
			if ($row){
				$minutes = $row->reference;
			}
			else{
				$minutes = "";
			}
		}
		else{
			$minutes = "";
		}
		return($minutes);		
	}
	public function getSeverityId(){
		return($this->criticality_id);
	}
	public function getSeverity(){
		return($this->criticality);
	}	
	public function setStatusLogo($img=null){
		if ($img == null) {
			self::$id_deadline = "../atomik/assets/images/32x32/agt_update_critical.png";
			self::$id_close = "../atomik/assets/images/32x32/agt_action_success.png";
			self::$id_open = "../atomik/assets/images/32x32/run.png";
			self::$id_propose = "../atomik/assets/images/32x32/agt_runit.png";
		}
		else {
			self::$id_deadline = $img['deadline'];
			self::$id_close = $img['close'];
			self::$id_open = $img['open'];
			self::$id_propose = $img['propose'];			
		}
	}
	public function getProjectId(){
		return($this->project_id);
	}
	public function getSubProjectId(){
		return($this->sub_project_id);
	}	
	public function getDescription(){
		return($this->description);
	}
	public function getContext(){
		return($this->context);
	}	
	public function getComment(){
		return($this->response);
	}	
	public function getStatusId(){
		return($this->status_id);	
	}
	public static function getStatusName($id){
		$result = Atomik_Db::find('bug_status',array('id'=>$id));
		if ($result){
			$status_name = $result['name'];
		}
		else{
			$status_name = "";
		}
		return($status_name);	
	}
	public static function findStatusId($name){
		$result = Atomik_Db::find('bug_status',array('name'=>$name,'type'=>'action'));
		if ($result){
			$status_id = $result['id'];
		}
		else{
			$status_id = 0;
		}
		return($status_id);		
	}
	private function setAssignee($id,$fname,$lname){
		/* manage e cute */
		Atomik::needed('User.class');
		$this->assignee_id = $id;
		$this->attendee = str_replace("Ã©","&#233;",User::getLiteName($fname,$lname));
		if (User::getCompanyUserLogged() != "ECE"){
			$this->attendee = str_rot13($this->attendee);
		}
	}
	public function getAssignee($option=false){
		if($option == false){
			$attendee = $this->attendee;
		}
		else{
			/* For PDF document generation. */
			// $attendee = preg_replace(array("/Ã©/s","/ÃƒÂ©/s"),array("&#233;","&#233;"),$this->attendee);
			// $attendee = utf8_encode(str_replace("Ã©","&#233;",$this->attendee));
			// $attendee = utf8_decode($this->attendee);
			// $attendee = str_replace("&#233;","Ã©",$this->attendee);
			$attendee = html_entity_decode($this->attendee,ENT_COMPAT,"UTF-8");
			// echo $attendee."<br/>";
		}
		if (User::getCompanyUserLogged() != "ECE"){
			$attendee = str_rot13($attendee);

		}
		return($attendee);
	}
	public function getPoster(){
		return($this->poster);
	}	
	public function getAssigneeId(){
		return($this->assignee_id);
	}
	public function getSubmitterId(){
		return($this->submitter_id);
	}
	public function getSubmitter(){
		Atomik::needed('User.class');
		$sql_query = "SELECT fname,lname FROM bug_users WHERE id = {$this->submitter_id} LIMIT 1";
		$result = A("db:".$sql_query);
		if ($result != false){
			$row = $result->fetch(PDO::FETCH_OBJ);
			if ($row != false){
				if (User::getCompanyUserLogged() != "ECE"){
					$submitter = str_rot13($row->fname." ".$row->lname);

				}
				else{			
					$submitter = $row->fname." ".$row->lname;
				}
			}
			else{
				$submitter = "";
			}
		}
		else{
			$submitter = "";
		}
		return($submitter);
	}			
	public function getDateOpen(){
		return($this->date_open_sql);
	}	
	public function set($id){
		$this->id = $id;
	}
   public static function getAttachmentAliasFilename($data_id) {
   		// $db = new Db;
		$query = "SELECT id,ext FROM `actions_attachment` WHERE `data_id` = '$data_id' ";
		
		$result = A('db:'.$query);
		if ($result != false){
			$row = $result->fetch(PDO::FETCH_OBJ);
			if ($row){
				$filename = "docs/actions/".$row->id.".".$row->ext;
			}
			else{
				$filename = "empty";
			}
		}
		else{
			$filename = "empty";
		}
		return($filename);
	}
   public static function getAttachmentRealFilename($data_id) {
   		// $db = new Db;
		$query = "SELECT real_name FROM `actions_attachment` WHERE `data_id` = '$data_id' ";
		$row = A('db:'.$query)->fetch(PDO::FETCH_OBJ);
		if ($row){
			$filename = $row->real_name;
		}
		else{
			$filename = "empty";
		}
		return($filename);
	}
	
	public function get($id=""){
		Atomik::needed("Date.class");
		Atomik::needed('Tool.class');
		if ($id==""){
			$id = $this->id;
		}
		$sql_query = "SELECT actions.comment,".
					"actions.id,".
					"actions.review as review_id,".
					"actions.status as status_id,".
					"actions.posted_by,".
					"actions.assignee as submitter_id,".
					"actions.criticality as criticality_id,".
					"actions.context,".
					"actions.Description,".
				   " projects.project,".
				   " projects.id as project_id,".
				   " lrus.lru, ".
				   " lrus.id as sub_project_id,".
				   " fname, lname,".
				   " bug_criticality.name as criticality,".
				   " bug_status.name as status,".
				   " date_open,".
				   " date_expected,".
				   " date_closure, ".
				   " review_type.type as type, ".
				   " reviews.managed_by, ".
				   " reviews.date ".
				   " FROM actions ".
				   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
				   " LEFT OUTER JOIN aircrafts ON aircrafts.id = reviews.aircraft ".
				   " LEFT OUTER JOIN review_type ON reviews.type = review_type.id ".
				   " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
				   " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
				   " LEFT OUTER JOIN bug_users ON bug_users.id = actions.posted_by ".
				   " LEFT OUTER JOIN lrus ON lrus.id = actions.lru ".
				   " LEFT OUTER JOIN projects ON projects.id = actions.project ".
				   " LEFT OUTER JOIN bug_status ON bug_status.id = actions.status ".
				   " LEFT OUTER JOIN bug_criticality ON bug_criticality.level = actions.criticality ".
				   "WHERE actions.status != 16 ".
				   /* "AND actions.criticality != 14 ". */ /* no task */
				   "AND actions.id = ".$id." ".
				   $this->which_status.
				   $this->which_aircraft.
				   $this->which_project.
				   $this->which_assignee.
				   $this->which_submitter.
				   $this->which_sub_project.
				   $this->which_review.
				   $this->which_criticality.
				   $this->which_baseline.
				   $this->search_query; 
				   " GROUP BY actions.id ORDER BY ".$this->order." id ASC";	
		$result = $this->db->db_query($sql_query);
		$row   = $result->fetch(PDO::FETCH_ASSOC);
		if ($row !==false){
			$this->id = $row['id'];
			$this->setAssignee($row['posted_by'],
								$row['fname'],
								$row['lname']);
			$this->submitter_id = $row['submitter_id'];
			if 	($row['criticality_id'] != 0){
				$this->criticality_id = $row['criticality_id'];
				$this->criticality = $row['criticality'];
			}
			else{
				$this->criticality_id = 10; /* ACTION */
				$this->criticality == "Action";
			}			
			if 	($row['status_id'] != 0){
				$this->status_id = $row['status_id'];
				$this->status = $row['status'];
			}
			else{
				$this->status_id = 8; /* OPEN */
				$this->status == "Open";
			}
			$this->project = $row['project'];
			$this->project_id = $row['project_id'];		
			$this->sub_project_id = $row['sub_project_id'];	
			$this->lru = $row['lru'];
			//$this->review = $row['review'];
			$this->review_id = $row['review_id'];
			$this->review_type = $row['type'];
			$this->description = $row['Description'];
			$this->response = $row['comment'];
			if (User::getCompanyUserLogged() != "ECE"){
				$this->project = str_rot13($this->project);
				$this->lru = str_rot13($this->lru);
				$this->description = str_rot13($this->description);
				$this->response = str_rot13($this->response);
			}			
			/* date */
			/* Convert date to display nicely */
			$this->date_open = Date::convert_date_conviviale ($row['date_open']);
			$this->date_open_sql = $row['date_open'];
			$this->date_open_dojo = Date::convert_date_to_dojo($row['date_open']);
			$this->date_expected = Date::convert_date_conviviale ($row['date_expected']);
			$this->date_expected_dojo = Date::convert_date_to_dojo($row['date_expected']);
			$this->compute_deadline();
			if ($this->status == "Closed" ) {
				$this->date_closure = Date::convert_date_conviviale ($row['date_closure']);
			}
			else {
				$this->date_closure = "";
			}
			/* attachment */
			$this->link = Action::getAttachmentAliasFilename($this->id);
			$this->attachment_name = Action::getAttachmentRealFilename($this->id);
			$this->link_mime = Tool::Get_Mime($this->link);			
			/* context */
			if (($this->review_id != "") && ($this->review_id != 0)) {
				/* Action is linked to a review, ignore context */
				// $this->context = '<a href="post_review?id='.$this->review_id.'">'.$row['managed_by']." ".$row['type']." ".Date::convert_date($row['date'])."</a>";
				$this->context = '<a href="'.Atomik::url('post_review',array('id'=>$this->review_id)).'">'.$row['type']." ".Date::convert_date($row['date'])."</a>";
			}
			else{
				$this->context = $row['context'];
			}
			$message = "";
		}
		else{
			$this->id = $id;
			$message = "<li class='failed' style='list-style-type: none;margin-top:40px;margin-right:10px'>Action not found</li>";
		}
		return($message);
	}
	private function sort_actions($option=null){
		if($option != null){
			$limit = " LIMIT {$option['start']},{$option['how_many']}";
		}
		else {
			$limit="";
		}
		// cancelled actions are not shown but still exists in the db
		$sql = "SELECT DISTINCT(actions.id) ".
			   " FROM actions".
			   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
			   " LEFT OUTER JOIN aircrafts ON aircrafts.id = reviews.aircraft ".
			   " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
			   " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
			   " LEFT OUTER JOIN bug_users ON bug_users.id = actions.posted_by ".
			   " LEFT OUTER JOIN lrus ON lrus.id = actions.lru ".
			   " LEFT OUTER JOIN projects ON projects.id = actions.project ".
			   " LEFT OUTER JOIN bug_status ON bug_status.id = actions.status ".
			   " LEFT OUTER JOIN bug_criticality ON bug_criticality.level = actions.criticality ".
			   "WHERE actions.status != 16 ".
			   $this->which_status.
			   $this->which_aircraft.
			   $this->which_project.
			   $this->which_assignee.
			   $this->which_submitter.
			   $this->which_sub_project.
			   $this->which_review.
			   $this->which_criticality.
			   $this->which_baseline.
			   $this->search_query.
			   " GROUP BY actions.id ORDER BY ".$this->order." id ASC ".$limit;  
		//echo $sql;	   
		return($sql);	
	}
	private function setDb($db){
		$this->db = $db;
	}
	public function setOrder($order){
		$this->order=$order;
	}
	public function close($info){
		Atomik::needed('User.class');
		$id = $this->id;
		$this->get($this->id);
		if ($this->getStatusId() == 8){ /* Open */
			$status = 13; /* Propose to close */
		}
		else {
			$status = 9; /* Closed */
		}
		$comment = $info['comment'];
		$comment.= "<p>Action closed by <b".User::getNameUserLogged()."</b></p>";
		if (isset($info['date_open'])){
			$date_open = $info['date_open']; 
			$date_closure = Date::convert_dojo_date($info['date_closure']);  
			$query = "UPDATE `actions` SET `status`='$status', `date_open`='$date_open' , `date_closure`='$date_closure' , `comment`='$comment' WHERE `id` = '{$id}' ";
		}
		else{
			$query = "UPDATE `actions` SET `status`='{$status}', `comment`='{$comment}' WHERE `id` = '{$id}' ";
		}	   
		echo "<p>".$query."</p>";
		$result = $this->db->exec($sql_query);		
		return($result);
	}
	public function update($info){	
		Atomik::needed("Date.class");
		$date_open = Date::convert_dojo_date($info['date_open']); 
		$date_expected = Date::convert_dojo_date($info['date_expected']);   
		$date_closure = Date::convert_dojo_date($info['date_expected']);  	   
		$result = Atomik_Db::update('actions',
								array('project'=>$info['project_id'] ,
										'review'=>$info['review_id'],
										'context'=>$info['context'] , 
										'LRU'=>$info['sub_project_id'] , 
										'posted_by'=>$info['user_id'] ,
										"assignee" 	=> $info['submitter_id'] ,										
										'Description'=>$info['description'] , 
										'date_open'=>$date_open,
										'date_closure'=>$date_closure,
										'criticality'=>$info['criticality_id'],
										'status'=>$info['status_id'],
										'date_expected'=>$date_expected ),
								array('id'=>$info['id'])		
								);
		return($result);
	}
	public function insert($info){
		Atomik::needed('User.class');
		Atomik::needed("Date.class");
		$context = $info['context'];
		$date_open = Date::convert_dojo_date($info['date_open']); 
		$date_expected = Date::convert_dojo_date($info['date_expected']);   
		$result = $this->db->db_insert('actions',
										array("project" 		=> $info['project_id'],
											  "context" 		=> $context,
											  "lru"		 		=> $info['sub_project_id'],
											  "review" 			=> $info['review_id'],
											  "posted_by" 		=> $info['user_id'],
											  "assignee" 		=> $info['submitter_id'],
											  "Description" 	=> $info['description'],
											  "criticality" 	=> $info['criticality_id'],
											  "status"			=> 8, /* OPEN */	 
											  "date_expected" 	=> $date_expected,
											  "date_open" 		=> $date_open));	
		return($result);
	}
	public function comment($new_comment){
		Atomik::needed("User.class");
		Atomik::needed("Date.class");
		/* Read previous comments */
		$this->get();
		$comment = $this->getComment();
		/* New comment */
        $comment.= "<p><u>Comment by <b>".User::getNameUserLogged()."</b> on <b>".Date::getTodayDate()."</b></u>: ".$new_comment."</p>";
		if ($new_comment!=""){
			$result = $this->db->update('actions',array('comment'=>$comment),array('id'=>$this->id));
		}
		else {
			$result=false;
		}
		return ($result);
	}
	public static function getLastId(){
		$sql_query = "SELECT MAX(id) from actions";
		$result = A('db:'.$sql_query)->fetch();
		$max_id=$result['MAX(id)'];	
		return($max_id);
	}
    public static function readItemsSheet(&$objWorksheet,
											$data=array()) {
		// Atomik::needed('PeerReviewer.class');
		Atomik::needed('User.class');
	    // $test_stat = new Status;
		// $test_defect_class = new Defect_Class;
		$test_poster = new User;
        /*
        * Read each lines
        */ 
        $highestRow = $objWorksheet->getHighestRow(); // e.g. 10     
        $find_remark_begin = false;
        $nb_items = 0;
        unset($res);
		$data= array();
		/* get ID column */
		$col = 0;
		$row = 1;
		/* get ID header */
		$header_value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
		if ($header_value == ""){
			/* empty action, stop loop */
			break;
		}					
		$column= array();
		for ($row = 2; $row <= 100; $row++) {
			$value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
			if ($value == ""){
				/* empty action, stop loop */
				break;
			}
			$column[] = $value;
		}
		$data[$header_value] = $column;
		// array_push($list_items,$data);
		$nb_action_items = count($column);
		/* get others columns */
        for ($col = 1; $col <= 10; $col++) {
            /* get header row */
			$row = 1;
			$header_value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
			if ($header_value == ""){
				/* empty header, stop loop */
				break;
			}
			$column= array();
			/* get others rows */			
			for ($row = 2; $row <= $nb_action_items+1; $row++) {
				$value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
				if ($value == null){
					$column[] = "";
				}
				else{				
					$column[] = $value;
				}
			}
			$data[$header_value] = $column;
			// var_dump($data);
			// array_push($list_items,$data);
		}
        $res['nb_items']=$nb_action_items;
        return($res);
    }	
	public function setStatus($status){	
		Atomik::needed("Date.class");	
		if ($status == 9){
			$result = $this->db->update('actions',array('status'=>$status,'date_closure'=>Date::getTodayDate()),array('id'=>$this->id));		
		}
		else{
			$result = $this->db->update('actions',array('status'=>$status),array('id'=>$this->id));
		}
		return ($result);
	}	
	public function buildActionTable () {
		// global $server_path;
		/* serialize context*/
		$context_array=serialize($this->env_context);
		
		$sql_query = $this->sort_actions();
		$result = $this->db->db_query($sql_query);
		//echo $sql_query."<br/>";
		$list_actions = $result->fetchAll(PDO::FETCH_ASSOC);
		$amount_actions = count($list_actions);
		if ($amount_actions > 0){
			$header=array('Status','Date open','Due Date','Date close');
			$html_body = '<table class="art-article pagetable">'; //'<table border="1">';
			$html_body .= '<thead>';
			$html_body .= "<tr bgcolor='#748386'>";
			$html_body .= '<th><font size="1.1" color="#666"><b>Id</b></font></th>';
			$html_body .= '<th><font size="1.1" color="#666"><b>Assignee</b></font></th>';
			$html_body .= '<th colspan="5"><font size="1" color="#666"><b>Description</b></font></th>';
			for($i=0;$i<count($header);$i++) {
				$html_body .= '<th><font size="1" color="#666"><b>'.$header[$i].'</b></font></th>';
			}
			$html_body .= '</tr></thead><tbody>';
			$action_counter = 0;
			$fill = false;
			foreach($list_actions as $row){
			//while($row = mysql_fetch_array($result)) {
				//echo "TEST:".$row['id']."<br/>";
				$this->get($row['id']);
				if ($fill) {
					$html_body .= "<tr bgcolor='#f0f0f0'>";
				}
				else {
					$html_body .= "<tr bgcolor='#e2e2e2'>";
				}
				$html_body .= '<td><a href="qams/actions?id='.$this->id.'"><font size="1" color="#808080" face="Arial">'.$this->id."</font></a></td>";
				$html_body .= '<td>'.$this->getAssignee()."</td>";
				$html_body .= '<td colspan="5"><font size="1" color="#000" face="Arial">'.$this->description."</font></td>";
				if ($this->status == "Open" ) {
					$html_body .= "<td>".
									"<!--[if IE 6]>Open<![endif]-->".
									"<!--[if !IE 6]><!-->";
					if ($this->deadline_over) {
						$html_body .= '<img class="action_opened" src="'.self::$id_deadline.'" border="0" alt ="Open" title="Open" />';
					} else {
						$html_body .= '<img class="action_opened" src="'.self::$id_open.'" border="0" alt ="Open" title="Open" />';
					}
					$html_body .= '<!--><![endif]--></td>';
					$html_body .= "<td>".$this->date_open."</td>";
					$html_body .= "<td>".$this->date_expected."</td>";
					$html_body .= "<td></td>";
				}
				else if ($this->status == "Propose to close" ){
					$html_body .= "<td>".
									"<!--[if IE 6]>Open<![endif]-->".
									"<!--[if !IE 6]><!-->";
					$html_body .= '<img class="action_opened" src="'.self::$id_propose.'" border="0" alt ="propose to close" title="propose to close" />';
					$html_body .= '<!--><![endif]-->';
					$html_body .= "</td>";
					$html_body .= "<td>".$this->date_open."</td>";
					$html_body .= "<td>".$this->date_expected."</td>";
					$html_body .= "<td></td>";
				}
				else {
					$html_body .= "<td>".
									"<!--[if IE 6]>Closed<![endif]-->".
									"<!--[if !IE 6]><!-->";
					$html_body .=  '<img class="action_opened" src="'.self::$id_close.'" alt ="Closed" title="Closed"></td>';
					$html_body .= '<!--><![endif]-->';
					/* bouton fleche pour voir le commentaire de cloture de l'action */
					$html_body .=  "<td>".$this->date_open."</td>";
					$html_body .=  "<td>".$this->date_expected."</td>";
					$html_body .=  "<td>".$this->date_closure."</td>";
				}
				$html_body .=  "</tr>";		
				$fill=!$fill;
				$action_counter++;
			}
			$html_body .= "</tbody>";
			$html_body .= "</table>";
		}
		else {
			$html_body = "No actions raised.";
		}
		return($html_body);
	}	
	public function getExportFilename(){
		Atomik::needed('Tool.class');
		$today_date_underscore = date("Y_M_d");
		$logbook = new Logbook(&$this->env_context);
		$filename = $logbook->board."Action_Items_List_".$today_date_underscore."_".uniqid().".xlsx";		

		return(Tool::cleanFilename($filename));
	}
	public function getExportTitle(){
		Atomik::needed('Tool.class');
		$today_date_underscore = date("Y_M_d");
		$logbook = new Logbook(&$this->env_context);
		$title = $logbook->board."Actions List";
		/*
		if($this->env_context != null){
			$project = new Project($this->env_context);
			$aircraft_name = $project->getAircraft();
			$project_name = $project->getProjectName();
			$sub_project_name = $project->getSubProjectName();
			$title = $aircraft_name.' '.$project_name." ".$sub_project_name." Actions List";
		}
		else {
			$title = "Actions_list_".$this->today_date.".xlsx";
		}*/
		return($title);
	}		
	public function getSelectReview($review,$selected,$onchange="inactive"){
		$html = '<label for="show_review">Review:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html.= ' name="show_review">';
		$html.= '<option value=""/> --All--';
		foreach($review->getReviewList() as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['reference']." ".$row['managed_by']." ".$row['lru']." ".$row['type']." ".Date::convert_date($row['date']);
		endforeach;
		$html .='</select>';
		return($html);
	}
	public function getSelectStatus($selected,$onchange="inactive"){
		$html ='<label for="show_status">Status:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html.= ' name="show_status">';
		$html.='<option value=""/> --All--';
		foreach(Action::getStatusList() as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['name'];
		endforeach;
		$html .='</select>';
		return($html);		
	}

	public function getSelectSeverity ($selected,$onchange="inactive"){
		$html ='<label for="show_criticality">Severity:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html.= ' name="show_criticality">';		
		$html.='<option value=""/> --All--';
		foreach(Action::getSeverityList() as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['name'];
		endforeach;
		$html .='</select>';
		return($html);
	}
	public function getPlainContext(){
		Atomik::needed('Tool.class');
		$minutes = self::getMinutes($this->id);
		// $context = '<a href="'.Atomik::url('post_review',array('id'=>$this->review_id)).'">'.$this->review_type;
		// $context = $minutes." ".Tool::convert_html2txt($this->context);		
		$context = $minutes." ".$this->review_type;
		return($context);
	}
	public function getPlainDescription(){
		Atomik::needed('Tool.class');	
		return(Tool::convert_html2txt($this->description));
	}
	public function drawPie($actions,$pie_filename = 'actions_pie.png'){	
		require_once("pChart/pData.class");  
		require_once("pChart/pChart.class");  
		$dir_font = "app/includes/pChart/Fonts/";
		// Dataset definition 
		$DataSet = new pData;
		$DataSet->AddPoint($actions,"Serie1");
		$DataSet->AddPoint(array("Closed","Open"),"Serie2");
		$DataSet->AddAllSeries();
		$DataSet->SetAbsciseLabelSerie("Serie2");

		// Initialise the graph
		$chart = new pChart(420,250);
		$chart->drawFilledRoundedRectangle(7,7,413,243,5,240,240,240);
		$chart->drawRoundedRectangle(5,5,415,245,5,230,230,230);
		$chart->createColorGradientPalette(195,204,56,223,110,41,5);

		// Draw the pie chart
		$chart->setFontProperties($dir_font."tahoma.ttf",8);
		$chart->AntialiasQuality = 0;
		$chart->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),180,130,110,PIE_PERCENTAGE_LABEL,FALSE,50,20,5);
		$chart->drawPieLegend(330,15,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);

		// Write the title
		$chart->setFontProperties($dir_font."MankSans.ttf",10);
		$chart->drawTitle(10,20,"Status of actions",100,100,100);

		$chart->Render($pie_filename);		  
		unset($chart);	
	}	
	public function new_drawPie($actions,$pie_filename = 'actions_pie.png',$title='Status of actions'){	
		/* pChart library inclusions */
 		require_once("pChart2.1.3/class/pData.class.php");
		require_once("pChart2.1.3/class/pDraw.class.php"); 
		require_once("pChart2.1.3/class/pPie.class.php"); 
		require_once("pChart2.1.3/class/pImage.class.php"); 
		
		$dir_font = "app/includes/pChart/Fonts/";
		/* Create and populate the pData object */
		// Dataset definition 
		$MyData = new pData;

		$MyData->addPoints($actions,"Serie1");
		$MyData->addPoints(array("Closed","Open"),"Labels");
		$MyData->setAbscissa("Labels");
		
		/* Create the pChart object */
		$x_canvas = 650;
		$y_canvas = 230;
		$myPicture = new pImage($x_canvas,$y_canvas,$MyData,TRUE);

		/* Draw a solid background */
		$Settings = array("R"=>173, "G"=>152, "B"=>217, "Dash"=>1, "DashR"=>193, "DashG"=>172, "DashB"=>237);
		$myPicture->drawFilledRectangle(0,0,$x_canvas,$y_canvas,$Settings);

		/* Draw a gradient overlay */
		$Settings = array("StartR"=>209, "StartG"=>150, "StartB"=>231, "EndR"=>111, "EndG"=>3, "EndB"=>138, "Alpha"=>50);
		$myPicture->drawGradientArea(0,0,$x_canvas,$y_canvas,DIRECTION_VERTICAL,$Settings);
		$myPicture->drawGradientArea(0,0,$x_canvas,20,DIRECTION_VERTICAL,array("StartR"=>0,"StartG"=>0,"StartB"=>0,"EndR"=>50,"EndG"=>50,"EndB"=>50,"Alpha"=>100));

		/* Add a border to the picture */
		$myPicture->drawRectangle(0,0,$x_canvas-1,$y_canvas-1,array("R"=>0,"G"=>0,"B"=>0));

		/* Write the picture title */ 
		$myPicture->setFontProperties(array("FontName"=>$dir_font."Silkscreen.ttf","FontSize"=>6));
		$myPicture->drawText(10,13,$title,array("R"=>255,"G"=>255,"B"=>255));
		$myPicture->setFontProperties(array("FontName"=>$dir_font."Forgotte.ttf","FontSize"=>14));
		/* Enable shadow computing on a (+1,+1) basis */
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));
		/* Draw the right area */
		$RectangleSettings = array("R"=>189,"G"=>130,"B"=>221,"Alpha"=>100,"Surrounding"=>20,"Ticks"=>2);
		$myPicture->drawFilledRectangle(390,10,640,219,$RectangleSettings);
		/* Write the legend */
		$TextSettings = array("R"=>255,"G"=>255,"B"=>255,"Align"=>TEXT_ALIGN_MIDDLEMIDDLE);
		$myPicture->drawText(435,30,"Actions Chart",$TextSettings);
		$TextSettings = array("R"=>106,"G"=>125,"B"=>3,"Align"=>TEXT_ALIGN_TOPLEFT,"FontSize"=>11);
		$myPicture->drawText(400,45,"The  actions  shown  here  has been",$TextSettings);
		$myPicture->drawText(400,60,"collected from reviews or audits.",$TextSettings);
		$myPicture->drawFromPNG(420,90,'assets/images/64x64/kchart.png');
		
		/* Set the default font properties */ 
		$myPicture->setFontProperties(array("FontName"=>$dir_font."tahoma.ttf","FontSize"=>10,"R"=>80,"G"=>80,"B"=>80));

		/* Create the pPie object */ 
		$PieChart = new pPie($myPicture,$MyData);

		/* Define the slice color */
		$PieChart->setSliceColor(0,array("R"=>143,"G"=>197,"B"=>0));
		$PieChart->setSliceColor(1,array("R"=>97,"G"=>77,"B"=>63));

		/* Draw a splitted pie chart */ 
		$PieChart->draw3DPie(170,125,array("WriteValues"=>TRUE,"DrawLabels"=>TRUE,"DataGapAngle"=>10,"DataGapRadius"=>6,"Border"=>TRUE));

		/* Write the legend */
		$myPicture->setFontProperties(array("FontName"=>$dir_font."tahoma.ttf","FontSize"=>6));
		$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));

		/* Do the mirror effect */
		$myPicture->drawAreaMirror(0,174,550,48); 
		$myPicture->Render($pie_filename);	
	}

	public function getAeraStats($filename="actions_aera.png",$granularity_selected=1,$iterations_selected=20){
		/* draw aera chart */
		$date = date("Y-m-d");
		$open = count($this->getActionsOpen($date)); /* Closed */
		$closed = count($this->getActionsClosed($date)); /* Open */
		$deadline_over = count($this->getActionsDeadline($date)); /* Deadline over */
		
		$stats_open[] = $open - $deadline_over;
		$stats_closed[] = $closed;
		$stats_total[] = $open + $closed;
		$stats_deadline[] = $deadline_over;	
		if ($granularity_selected == 1){
			$gran = "W";
			$sub = "week";
			$abscissa = "Weeks";
		}
		else{
			$gran = "M";
			$sub = "month";
			$abscissa = "Months";
		}
		$stats_week[] = date($gran);
		$store_date = $date;
		for ($index=0;$index<$iterations_selected;$index++){
			$date = strtotime ( '-1 '.$sub , strtotime ( $store_date ) ) ;
			$stats_week[] = date($gran,$date);
			$date = date ( 'Y-m-j' , $date );
			$store_date = $date;
			try{
				$open = count($this->getActionsOpen($date)); /* Closed */
				$closed = count($this->getActionsClosed($date)); /* Open */
				$deadline_over = count($this->getActionsDeadline($date)); /* Deadline over */
			}
			catch (PDOException $erreur){
				echo 'Erreur : '.$erreur->getMessage();
			}
			$stats_open[] = $open - $deadline_over;
			$stats_closed[] = $closed;
			$stats_total[] = $open + $closed;
			$stats_deadline[] = $deadline_over;
			
			// echo $store_date."<br/>";
		}
		$stats = array('deadline'=>array_reverse($stats_deadline),
						'open'=>array_reverse($stats_open),
						'closed'=>array_reverse($stats_closed),
						'week'=>array_reverse($stats_week));	
		$this->drawArea($stats,$abscissa,$filename);
	}
	
	public function drawArea($stat_data,$abscissa="Weeks",$filename="actions_aera.png"){
		 /* pChart library inclusions */ 
 		require_once("pChart2.1.3/class/pData.class.php");
		require_once("pChart2.1.3/class/pDraw.class.php"); 
		require_once("pChart2.1.3/class/pImage.class.php"); 
		
		$dir_font = "app/includes/pChart/Fonts/";
		 /* Create and populate the pData object */
		$MyData = new pData(); 		  
		 $MyData->addPoints($stat_data['closed'],"Closed");		 
		 $MyData->addPoints($stat_data['open'],"Open");
		 $MyData->addPoints($stat_data['deadline'],"Deadline");
		 $MyData->setPalette("Closed",array("R"=>0,"G"=>255,"B"=>0));
		 $MyData->setPalette("Open",array("R"=>255,"G"=>255,"B"=>0));
		 $MyData->setPalette("Deadline",array("R"=>255,"G"=>0,"B"=>0));
		 $MyData->setAxisName(0,"Actions");
		 /* Create the abscissa serie */ 
		 $MyData->addPoints($stat_data['week'],"Time"); 		 
		 $MyData->setAbscissa("Time");
		 $MyData->setAbscissaName($abscissa);
		 // $MyData->loadpalette($dir_palette."kitchen.color");
		 /* Create the pChart object */ 
		 $myPicture = new pImage(700,330,$MyData); 
		 $myPicture->drawGradientArea(0,0,700,330,DIRECTION_VERTICAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100)); 
		 $myPicture->drawGradientArea(0,0,700,330,DIRECTION_HORIZONTAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>20)); 
		 
		 /* Turn on Antialiasing */ 
		 $myPicture->Antialias = FALSE; 
		
		 /* Draw a background */ 
		 // $Settings = array("R"=>190, "G"=>213, "B"=>107, "Dash"=>1, "DashR"=>210, "DashG"=>223, "DashB"=>127);  
		 // $myPicture->drawFilledRectangle(0,0,700,330,$Settings);  
		 	 
		 /* Add a border to the picture */ 
		 $myPicture->drawRectangle(0,0,699,329,array("R"=>0,"G"=>0,"B"=>0)); 
		  
		 /* Write the chart title */  
		 $myPicture->setFontProperties(array("FontName"=>$dir_font."Forgotte.ttf","FontSize"=>11)); 
		 $myPicture->drawText(150,35,"Action items follow-up",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); 
		
		 /* Set the default font */ 
		 $myPicture->setFontProperties(array("FontName"=>$dir_font."pf_arma_five.ttf","FontSize"=>6)); 
		
		 /* Define the chart area */ 
		 $myPicture->setGraphArea(60,40,650,300); 
		
		 /* Draw the scale */ 
		 // $scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE);
		 $scaleSettings = array("DrawSubTicks"=>TRUE,"Mode"=>SCALE_MODE_ADDALL);
		 $myPicture->drawScale($scaleSettings); 
		
		 /* Turn on Antialiasing */ 
		 $myPicture->Antialias = TRUE; 
		
		 /* Draw the line chart */ 
		 $myPicture->drawStackedAreaChart(array("DisplayValues"=>TRUE,"DisplayColor"=>DISPLAY_AUTO,"Surrounding"=>20,"ForceTransparency"=>50)); /* drawFilledSplineChart */
		 // $myPicture->drawFilledStepChart(array("ForceTransparency"=>40));
		 // $myPicture->drawStackedAreaChart(array("Surrounding"=>10));
		 // $myPicture->drawPlotChart(array("PlotBorder"=>TRUE,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>80)); 
		
		 /* Write the chart legend */ 
		 $myPicture->drawLegend(80,40,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_VERTICAL)); 
		// $myPicture->drawAreaMirror(0,290,650,90); 
		 /* Render the picture (choose the best way) */ 
		 //$myPicture->autoOutput("pictures/example.drawSplineChart.simple.png");
		 $myPicture->Render($filename);	
 	}
	public function drawBar($user,$bar_filename = 'actions_bar.png'){
		require_once("pChart/pData.class");  
		require_once("pChart/pChart.class");  	
		$dir_font = "app/includes/pChart/Fonts/";	
		$DataSet = new pData;
		$DataSet->AddPoint($user->nb_closed,"Serie2");
		$DataSet->AddPoint($user->nb_actions,"Serie1");
		//$DataSet->AddPoint(array(3,2,2),"Serie2");
		//$DataSet->AddPoint(array(3,4,1),"Serie3");
		$DataSet->AddPoint($user->name,"Labels");
		$DataSet->AddAllSeries();
		$DataSet->RemoveSerie("Labels");
		$DataSet->SetAbsciseLabelSerie("Labels");
		$DataSet->SetSerieName("Closed Actions","Serie2");
		$DataSet->SetSerieName("Open Actions","Serie1");
		//$DataSet->SetSerieName("Beta","Serie2");
		//$DataSet->SetSerieName("Gama","Serie3");
		$DataSet->SetXAxisName("Attendees");
		$DataSet->SetYAxisName("Actions");
		$DataSet->SetSerieSymbol("Serie1",Atomik::asset('assets/images/Point_Asterisk.gif'));
		//$DataSet->SetYAxisUnit("Âµm");

		// Initialise the graph
		$graph_width = 1024;
		$graph_height = 600;
		$x1 = 40;
		$y1 = 40;
		$x2 = $graph_width - 30;
		$y2 = $graph_height - 40;
		$chart = new pChart($graph_width,$graph_height);
		$chart->drawGraphAreaGradient(140,140,140,90,TARGET_BACKGROUND);

		// Graph area setup
		$chart->setFontProperties($dir_font."pf_arma_five.ttf",6);
		$chart->setGraphArea($x1,$y1,$x2,$y2);
		$chart->drawGraphArea(213,217,221,FALSE);
		$chart->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_ADDALLSTART0,213,217,221,TRUE,0,2,TRUE);
		$chart->drawGraphAreaGradient(40,40,40,-50);
		$chart->drawGrid(4,TRUE,230,230,230,5);

		// Draw the title   
		$chart->setFontProperties($dir_font."GeosansLight.ttf",24);
		$Title = "  Amount of actions";   
		//$Test->setLineStyle(2);
		//$Test->drawLine(51,-2,51,402,0,0,0);   
		//$Test->setLineStyle(1);
		//$Test->setShadowProperties(1,1,0,0,0); 
		//$Test->drawTitle(0,0,$Title,255,255,255,660,30,TRUE);  
		//$Test->clearShadow(); 
		$chart->drawTextBox(0,0,1024,30,$Title,0,255,255,255,ALIGN_BOTTOM_CENTER,TRUE,0,0,0,30);   
		$chart->setFontProperties($dir_font."pf_arma_five.ttf",6);

		// Draw the bar graph
		$chart->drawStackedBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),70);

		// Write the legend
		$chart->drawLegend(-2,3,$DataSet->GetDataDescription(),0,0,0,0,0,0,255,255,255,FALSE);

		// Finish the graph
		$chart->addBorder(1);
		// Add an image  
		$chart->drawFromPNG(Atomik::asset('assets/images/logo.png'),484,35);
		$chart->Render($bar_filename);			
		$gdImage_poster = @imagecreatefrompng($bar_filename);  	
	}
		
	public function new_drawBar($user,$filename = 'actions_bar.png'){
		 /* pChart library inclusions */ 
 		require_once("pChart2.1.3/class/pData.class.php");
		require_once("pChart2.1.3/class/pDraw.class.php"); 
		require_once("pChart2.1.3/class/pImage.class.php"); 
		$dir_font = "app/includes/pChart/Fonts/";
		 /* Create and populate the pData object */ 
		 $MyData = new pData();   
		 $MyData->addPoints($user->nb_closed,"Closed Actions"); 
		 $MyData->addPoints($user->nb_actions,"Open Actions"); 
		 $MyData->setAxisName(0,"Actions"); 
		 $MyData->addPoints($user->name,"Attendees"); 
		 $MyData->setSerieDescription("Attendees","Months"); 
		 $MyData->setAbscissa("Attendees");
		 $MyData->setAbscissaName("Assignees");		 
		
		 /* Normalize all the data series to 100% */ 
		// $MyData->normalize(100,"%"); 
		
		 /* Create the pChart object */ 
		 $myPicture = new pImage(700,230,$MyData); 
		 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_VERTICAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>100)); 
		 $myPicture->drawGradientArea(0,0,700,230,DIRECTION_HORIZONTAL,array("StartR"=>240,"StartG"=>240,"StartB"=>240,"EndR"=>180,"EndG"=>180,"EndB"=>180,"Alpha"=>20)); 
		
		 /* Write the chart title */  
		 $myPicture->setFontProperties(array("FontName"=>$dir_font."Forgotte.ttf","FontSize"=>11)); 
		 $myPicture->drawText(150,35,"Action items assignees",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); 
		 
		 /* Set the default font properties */ 
		 $myPicture->setFontProperties(array("FontName"=>$dir_font."pf_arma_five.ttf","FontSize"=>6)); 
		 
		 /* Draw the scale and the chart */ 
		 $myPicture->setGraphArea(60,50,680,190); 
		 $myPicture->drawScale(array("DrawSubTicks"=>TRUE,"Mode"=>SCALE_MODE_ADDALL)); 
		 $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); 
		 $myPicture->drawStackedBarChart(array("DisplayValues"=>TRUE,"DisplayColor"=>DISPLAY_AUTO,"Gradient"=>TRUE,"GradientMode"=>GRADIENT_EFFECT_CAN,"Surrounding"=>30)); 
		 $myPicture->setShadow(FALSE); 

		 
		 /* Write the chart legend */ 
		 $myPicture->drawLegend(480,210,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));	
		 $myPicture->Render($filename);	
	}
	public function exportXlsx(){
		require_once "../excel/Classes/PHPExcel.php";
		require_once '../excel/Classes/PHPExcel/IOFactory.php';
		require_once '../excel/Classes/PHPExcel/Worksheet/RowIterator.php';
		Atomik::needed("ExportXls.class");
		Atomik::needed('Tool.class');
		include("app/includes/ExportXls.class.php");
 
		// Set the enviroment variable for GD
		putenv('GDFONTPATH=' . realpath('.'));
		error_reporting(E_ALL);
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
		$qams_path = Atomik::url("qams");
		$file_template = dirname(__FILE__).
                        DIRECTORY_SEPARATOR."..".
                        DIRECTORY_SEPARATOR."..".
                        DIRECTORY_SEPARATOR."assets".
                        DIRECTORY_SEPARATOR."template".
                        DIRECTORY_SEPARATOR."Actions_list_template_02.xlsx";
		if (!file_exists($file_template)) {
			echo "Warning: Excel actions list template is missing.<br/>".$file_template;
			// exit();
			$objPHPExcel = new PHPExcel;
			$objPHPExcel->getActiveSheet()->setTitle('Header');
			$objWorksheet = $objPHPExcel->createSheet();
			$objWorksheet->setTitle('Action list');			
			$sheet_tab=array('Header'=>0,'Action list'=>1,'Summary'=>3);
		}
		else {
			$objPHPExcel = PHPExcel_IOFactory::load($file_template);
			$sheet_tab=array('Header'=>0,
			'Action list'=>1,
			'Summary'=>3);
		}
		$filename_simple = $this->getExportFilename();
		$dir_path_result = "..".DIRECTORY_SEPARATOR."result".DIRECTORY_SEPARATOR;
		$filename= $dir_path_result.$filename_simple;
		/*
		*  Intro
		*/   
		$objPHPExcel->setActiveSheetIndex($sheet_tab['Header']);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', $this->getExportTitle());
		$objPHPExcel->getActiveSheet()->setCellValue('A4', $this->getExportTitle());
		$objPHPExcel->getActiveSheet()->setCellValue('A2', "Reference: ");
		$objPHPExcel->getActiveSheet()->setCellValue('C2', "Issue: ");
		$objPHPExcel->getActiveSheet()->setCellValue('C12', "");
		$objPHPExcel->getActiveSheet()->setCellValue('E12', $this->today_date);
		$objPHPExcel->getActiveSheet()->getStyle('A1:E19')->applyFromArray($style_first_page);
		$objPHPExcel->setActiveSheetIndex($sheet_tab['Action list']);
		$header=array('Id','System','Item','Context','Description','Submitter','Assignee','Date open','Date expected','Date closed','Status','Criticality','Comment');
		for($i=0;$i<count($header);$i++) {
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 2, $header[$i]);
		}
		$row_counter = 3;
		/* draw thick border around the actions */
		$list_actions = $this->getActions();
		$amount_actions=count($list_actions);
		$objPHPExcel->getActiveSheet()->getStyle('A2:M'.strval($amount_actions + 2))->applyFromArray($style_encadrement);
		foreach($list_actions as $row) {
			if ($row_counter % 2) {
				/* alternate white and grey line color */
				$objPHPExcel->getActiveSheet()->getStyle('A'.$row_counter.':M'.$row_counter)->applyFromArray($style_white_line);
			}
			/* border inside */
			$objPHPExcel->getActiveSheet()->getStyle('A'.$row_counter.':M'.$row_counter)->getBorders()->getInside()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
			$this->get($row['id']);
			// var_dump($this->getReviewId());
			$data = array('id'=>$this->id,
						'project'=>$this->project,
						'lru'=>$this->lru,
						'context'=>$this->getPlainContext(),
						'description'=>$this->getPlainDescription(),
						'submitter'=>$this->getSubmitter(),
						'assignee'=>$this->getAssignee(),
						'open'=>$this->date_open,
						'expected'=>$this->date_expected,
						'closure'=>$this->date_closure,
						'status'=>$this->status,
						'criticality'=>$this->criticality,
						'response'=>Tool::convert_html2txt($this->response));
			if ($this->status == "Closed" ) {
				$data['status'] = "Closed";
				$objPHPExcel->getActiveSheet()->getStyle('J'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$objPHPExcel->getActiveSheet()->getStyle('J'.$row_counter)->getFill()->getStartColor()->setARGB('00FF00');
			}
			else if ($this->status == "Propose to close" ) {
				$data['status'] = "ECE Closed";
				$objPHPExcel->getActiveSheet()->getStyle('J'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$objPHPExcel->getActiveSheet()->getStyle('J'.$row_counter)->getFill()->getStartColor()->setARGB('99FF00');
			}	
			else {
				$data['status'] = "Open";
				if ($this->deadline_over) {
					$objPHPExcel->getActiveSheet()->getStyle('J'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
					$objPHPExcel->getActiveSheet()->getStyle('J'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
					$objPHPExcel->getActiveSheet()->getStyle('J'.$row_counter)->getFill()->getStartColor()->setARGB('FF0000'); /* Red */
				}
				else {
					$objPHPExcel->getActiveSheet()->getStyle('J'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_YELLOW);
					$objPHPExcel->getActiveSheet()->getStyle('J'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
					$objPHPExcel->getActiveSheet()->getStyle('J'.$row_counter)->getFill()->getStartColor()->setARGB('FFA500'); /* Orange */
				}
			}
			$index = 0;
			foreach ($data as $val) {
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($index++, $row_counter, $val);
			}
			/* Add hyperlink toward review */
			$url_context = Tool::getClientUrl();
			$url_context .= Atomik::appUrl('post_review',array('id'=>$this->review_id));
			$objPHPExcel->getActiveSheet()->getCell('D'.$row_counter)->getHyperlink()->setUrl($url_context);//rawurlencode($url_context)
			$row_counter++;	
		}
		/*
		 * Summary
		 */ 
		
		/* Actions exist */
		if ($row_counter > 3) {
			$nb_actions = $row_counter - 3;
			/* Apply an autofilter to a range of cells */
			$objPHPExcel->getActiveSheet()->setAutoFilter('A2:M2');
			if (Atomik::has('session/actions_graph')){
				$graphs_encoded = Atomik::get('session/actions_graph');
				$graphs_file_list=unserialize(urldecode(stripslashes(stripslashes($graphs_encoded))));
				$pie_filename = $dir_path_result.$graphs_file_list['actions_pie'];
				$bar_filename = $dir_path_result.$graphs_file_list['actions_bar'];
				$aera_filename = $dir_path_result.$graphs_file_list['actions_spline'];
			}
			else{
				$pie_filename = $dir_path_result."actions_pie_".uniqid().".png";
				$bar_filename = $dir_path_result."actions_bar_".uniqid().".png";
				$aera_filename = $dir_path_result."actions_aera_".uniqid().".png";
				$user = new User(&$this->env_context);
				$user->get_stat_actions (true);
				/* count actions */
				$actions_closed = Action::new_count_actions("closed");
				$actions_open = Action::new_count_actions("open");
				$actions = array('closed'=>$actions_closed,'open'=>$actions_open);
				$this->new_drawPie($actions,
									$pie_filename);
				if ($user->nb != 0) {
					/* Bar */  	 
					$this->new_drawBar(&$user,
										$bar_filename);	
				}
				else {
					$bar_filename = 'artichow/images/error.png';
				}
				$this->getAeraStats($aera_filename);				
			}

			/*
			* Pie poster
			*/
			$objWorksheet1 = $objPHPExcel->createSheet();
			$objWorksheet1->setTitle('Summary');
			$objPHPExcel->setActiveSheetIndex($sheet_tab['Summary']);
			$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
			$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
			$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

			// Add a drawing to the worksheet

			$row_counter = 4;
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, $row_counter, "Status of ".$nb_actions." actions");
			$objPHPExcel->getActiveSheet()->getStyle('B'.strval($row_counter))->getFont()->setName('Candara');
			$objPHPExcel->getActiveSheet()->getStyle('B'.strval($row_counter))->getFont()->setSize(20);
			$objPHPExcel->getActiveSheet()->getStyle('B'.strval($row_counter))->getFont()->setBold(true);
			//$objPHPExcel->getActiveSheet()->getStyle('B'.strval($row_counter+36))->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
			$objPHPExcel->getActiveSheet()->getStyle('B'.strval($row_counter))->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLUE);
			
			/* display pie chart */
			$gdPie_img = @imagecreatefrompng($pie_filename);
			$objDrawingPie = new PHPExcel_Worksheet_MemoryDrawing;
			$objDrawingPie->setWorksheet($objPHPExcel->getActiveSheet());
			$objDrawingPie->setName('Actions status');
			$objDrawingPie->setDescription('Actions status');
			$objDrawingPie->setImageResource($gdPie_img);
			$objDrawingPie->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
			$objDrawingPie->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
			$objDrawingPie->setHeight(400);
			$objDrawingPie->setCoordinates('B'.strval($row_counter+2));
			$objDrawingPie->setOffsetX(20);
			$objDrawingPie->getShadow()->setVisible(true);
			$objDrawingPie->getShadow()->setDirection(45);
			/*
			* Table of actions attendees
			*/
			/*
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(12);
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 31, "Name");
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 31, "Function");
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 31, "Amount of open actions");
			$row_counter = 32;
			$objPHPExcel->getActiveSheet()->getStyle('E31:G'.strval($row_counter - 1))->applyFromArray($style_table_array);
			if ($row_counter < 10)
				$row_counter = 10;	
			if ($user->index_poster > 0){
				foreach ($user->poster_tab as $name => $function) {
					$amount_of_actions = $user->poster_nb_tab[$name];
					if ($amount_of_actions > 0) {
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, $row_counter, $name);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, $row_counter, $function);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, $row_counter, $user->poster_nb_tab[$name]);
						$row_counter++;
					}
				}
			}
			*/
			$gdBar_img = @imagecreatefrompng($bar_filename);
			$objDrawingBar = new PHPExcel_Worksheet_MemoryDrawing;
			$objDrawingBar->setWorksheet($objPHPExcel->getActiveSheet());
			$objDrawingBar->setName('Assignees stat');
			$objDrawingBar->setDescription('Assignees stat');
			$objDrawingBar->setImageResource($gdBar_img);
			$objDrawingBar->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
			$objDrawingBar->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
			$objDrawingBar->setHeight(500);
			$objDrawingBar->setCoordinates('A'.strval($row_counter+30));
			$objDrawingBar->setOffsetX(400);
			$objDrawingBar->getShadow()->setVisible(true);
			$objDrawingBar->getShadow()->setDirection(45);
			
			$gdAera_img = @imagecreatefrompng($aera_filename); 
			$objDrawingAera = new PHPExcel_Worksheet_MemoryDrawing;
			$objDrawingAera->setWorksheet($objPHPExcel->getActiveSheet());
			$objDrawingAera->setName('Actions follow-up');
			$objDrawingAera->setDescription('Actions follow-up');
			$objDrawingAera->setImageResource($gdAera_img);
			$objDrawingAera->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
			$objDrawingAera->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
			$objDrawingAera->setHeight(500);
			$objDrawingAera->setCoordinates('A'.strval($row_counter+60));
			$objDrawingAera->setOffsetX(400);
			$objDrawingAera->getShadow()->setVisible(true);
			$objDrawingAera->getShadow()->setDirection(45);			
			/* count actions and display pie chart */  
			$objPHPExcel->setActiveSheetIndex(0);
		}
		else {
	
		}
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($filename);
		return($filename_simple);
	}
}
function qams_log($text){
	ob_start("manage_log");
	echo $text;
	ob_end_clean();
}
