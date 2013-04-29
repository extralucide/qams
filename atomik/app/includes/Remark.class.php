<?php
/**
 * Quality Assuance Management System
 * Copyright (c) 2009-2013 Olivier Appere
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     Remark.class
 * @author      Olivier Appere
 * @copyright   2009-2013 (c) Olivier Appere
 * @license     http://www.opensource.org/licenses/mit-license.php
 * @link        
 */

/**
 * Handle remarks
 *
 * @package Remark.class
 */
/* Data status */ 
define('REVIEWED', 10);
define('NEW_DOC', 11);
define('UNDER_REVIEW', 12);
/* Peer reviews status */
define('SUBMITTED', 15);
define('ACCEPTED', 3); 	  	  /* IS:ACCEPTED */
define('REJECTED', 1);	  	  /* IS:REJECTED */
define('CORRECTED', 4); 	  /* IS:CORRECTED */
define('QA_ACCEPTANCE', 5);   /* IS:CORRECTED + Verification Version */
define('POSTPONED', 6);
define('TO_BE_REVIEWED', 2);  /* IS:TO BE DISCUSSED*/
define('ACTION_CLOSED', 9);
define('HPR_CLOSED', 52);
/* Proof reading type */
define('PEER_REVIEW', 1);
define('SOFTWARE_INSPECTION', 2);
define('SYSTEM_VALIDATION', 3);
define('EQPT_VALIDATION', 4);
define('OR_EUROCOPTER', 5);

class Remark {
	/**
	 * Id of the remark
	 *
	 * @var id
	 */
	private $db; 
	public $id;
	public $reply_id;
	public $project;
	public $lru;
	public $reference;
	public $ref;
	public $version;
	public $data_id;
	public $type;
	public $description;
	public $status;
	public $status_id;
	public $poster;
	public $action_id;
	public $assignee_id;	
	public $email;
	public $date;
	public $date_dojo;
	public $small_date;
	public $paragraph;
	public $line;
	public $subject;
	public $item;
	public $see_remark;
	public $color_status;
	public $color_action;
	public $criticality;
	public $justification;
	public $category;
	public $response;
	private $email_suject;
	private $test;
	private $filename;
	public $aircraft_id;
	public $project_id;
	public $sub_project_id;		
	public $category_id;				
	public $baseline_id;				
	public $search;
	public $search_query;
	public $order;
	public $which_aircraft;
	public $which_project;
	public $which_sub_project;
	public $which_data;
	public $which_category;
	public $which_status;
	public $which_assignee;
	public $which_baseline;
	public  $amount_remarks;
	public  $remark_tab;
	public  $stats;	
	
	public static function test_remark_existence($qams_id) {
		/* Test if the remark exist */
		$exist = Atomik_Db::find("bug_messages",array("reply_id"=>$qams_id));
		return($exist);
	}
	public function update_remark_status ($qams_id,$status_id) {
		Atomik_Db::update("bug_messages",
						array("status"=>$status_id),
						array("reply_id"=>$qams_id));
	}
	private static function get_diff($first,$second,&$diff=null){
		set_time_limit(0);
		Atomik::needed('FineDiff.class');
		$granularity = 3;
		$granularityStacks = array(FineDiff::$paragraphGranularity,
									FineDiff::$sentenceGranularity,
									FineDiff::$wordGranularity,
									FineDiff::$characterGranularity);
		/* check paragraph */
		$diff = new FineDiff($first, 
							 $second, 
							 $granularityStacks[$granularity]);							
		 
		$diff_len = strlen($diff->getOpcodes());
		return($diff_len);
	}
	public static function getLastId(){
		$sql_query = "SELECT MAX(id) from bug_messages";
		$result = A('db:'.$sql_query)->fetch();
		$max_id=$result['MAX(id)'];	
		return($max_id);
	}
	public static function get_qams_id_remark($application_id,
												$poster_id,
												$paragraph,
												$line,
												$text_from_xls,
												$author_response_from_xls,
												&$res) {
		$qams_id=false;
		$res['paragraph_check']=true;
		$res['response_check']=true;
		$res['description_check']=true;
		if ($poster_id != ""){
			$which_poster = "AND `posted_by` = '".$poster_id."' ";
		}
		else {
			$which_poster = "";
		}
		if ($paragraph != ""){
			$which_paragraph = " AND `paragraph` LIKE '".$paragraph."' ";
		}
		else{
			$which_paragraph = "";
		}
		if ($line != ""){
			$which_line = " AND `line` LIKE '".$line."' ";
		}
		else{
			$which_line = "";
		}	
		/* Get last id of remarks table */	
		$sql_get_id = "SELECT id,description,paragraph,line FROM bug_messages WHERE `application` = '".$application_id."' ".
						$which_poster.
						// $which_paragraph.
						// $which_line.
						" AND id <= ".Remark::getLastId();
		// echo $sql_get_id."<br/>";
		$result = A("db:".$sql_get_id);	
		// var_dump($result);		
		//echo "Text lu ds excel: ".$text."<br/>";
		if ($result !== false){
			Atomik::needed('Tool.class');
			$remark_import_fail = array();
			foreach ($result as $table):
				$id = $table['id'];
				// echo $table['id']."<br/>";
				//echo "Scan remark ".$id." ...<br/>";
				// $plain_text_from_db = Tool::shrink(Tool::convert_html2txt($table['description']));
				$plain_text_from_db = Tool::shrink($table['description']);
				$text_from_xls =  Tool::shrink($text_from_xls);

				/* check paragraph */
				$diff_len_parag = Remark::get_diff($table['paragraph'],$paragraph);
				if ($diff_len_parag <= 5) {
					/* paragraph are identical*/
					$res['paragraph_check']=true;
					/* check line */
					$diff_len_line = Remark::get_diff($table['line'],$line);
					if ($diff_len_line <= 5) {
						/* check description */
						$diff_len = Remark::get_diff($plain_text_from_db,$text_from_xls,&$test_diff);
						if ($diff_len <= 10) {
							$qams_id = $table['id'];
							/* description is identical */
							// $diff_len = Remark::get_diff($plain_author_response__from_db,$author_response_from_xls);
							if ((strlen($author_response_from_xls) > 0) && (!Remark::remark_response_exist($qams_id))) {
								$res['response_check']=false;
							}
							break;
						}
						else if($diff_len <= 20) {
							$qams_id = $table['id'];
							$edits = $test_diff->getOps();
							// $rendered_diff = $test_diff->renderDiffToHTML();
							// echo $rendered_diff;
							$check_spaces = true;/* check spaces */
							foreach ($edits as $row){
								if (($row instanceof FineDiffCopyOp)||
									($row instanceof FineDiffDeleteOp)){
								}
								else{
									if ($row->text != " "){
										$check_spaces = false;
									}
									// echo "Txt:".$row->text."<br/>";
								}
							}
							if ($check_spaces){
							}
							else{
								$res['description_check']=false;
							}
							/* description is nearly identical */
							// $diff_len = Remark::get_diff($plain_author_response__from_db,$author_response_from_xls);
							// echo "<pre>DB: ###".$plain_text_from_db."###".$table['description']."";
							// echo "XL: ###".$text_from_xls."###</pre><br/><br/>";
							$remark_import_fail[$id] = $diff_len;
							if ((strlen($author_response_from_xls) > 0) && (!Remark::remark_response_exist($qams_id))) {
								$res['response_check']=false;
							}
							// if ($table['id'] == 9835){
								// echo "DIFF:".$diff_len."<br/>";
								// echo "<pre>";
								// echo $plain_text_from_db;
								// echo "<br/>-------------------------------------";
								// echo "<br/><br/>";
								// echo $text_from_xls;
								// echo "<br/>-------------------------------------";
								// echo "</pre>";

								// var_dump($edits);
							// }
							break;
						}
						else{
							// if ($table['id'] == 9835){
								// echo "DIFF:".$diff_len."<br/>";
								// echo "<pre>";
								// echo $plain_text_from_db;
								// echo "<br/>-------------------------------------";
								// echo "<br/><br/>";
								// echo $text_from_xls;
								// echo "<br/>-------------------------------------";
								// echo "</pre>";
								// $edits = $test_diff->getOps();
								// $rendered_diff = $test_diff->renderDiffToHTML();
								// echo $rendered_diff;
								// var_dump($edits);
							// }
							// $remark_import_fail[$id] = $diff_len;
						}
					}
				}
				else{
						// if ($table['id'] == 9835){
							// echo "DIFF:".$diff_len_parag."<br/>";
							// echo "<pre>";
							// echo $table['paragraph'];
							// echo "<br/>-------------------------------------";
							// echo "<br/><br/>";
							// echo $paragraph;
							// echo "<br/>-------------------------------------";
							// echo "</pre>";
						// }
				}
				// if ($table['id'] == 9838){
				// }
				/* paragraph are not identical*/
				/* check description */
				$diff_len = Remark::get_diff($plain_text_from_db,$text_from_xls);
				if ($diff_len <= 5) {
					/* description is identical */				
					$qams_id = $table['id'];
					$res['paragraph_check']=false;
					// $diff_len = Remark::get_diff($plain_author_response__from_db,$author_response_from_xls);
					if ((strlen($author_response_from_xls) > 0) && (!Remark::remark_response_exist($qams_id))) {
						$res['response_check']=false;
					}
					// echo "For remark ".$qams_id." description is identical but paragraph is different<br/><br/>";
					break;
				}
			endforeach;
		}
		// ksort($remark_import_fail,SORT_NUMERIC);
		// foreach ($remark_import_fail as $key => $val) {
			// echo "remark[" . $key . "] = " . $val . "<br/>";
		// }
		return ($qams_id);
	}
	public static function find_remark ($qams_id,$ref_id) {
		$find=false;
		if(!preg_match("#[0-9]{1,4}#",$qams_id)) {
			//print "For remark ".$ref_id." could not read QAMS id in excel file: ".$qams_id."<BR>";
			$find=false;
		}
		else {
			$nb_row_response = Atomik_Db::count("bug_messages",array("id"=>$qams_id));
			if ($nb_row_response == 0) {
				//print "For remark ".$ref_id." could not find QAMS id in db: ".$qams_id."<BR>";
				$find=false;
			}
			else {
				$find=Remark::test_remark_existence($qams_id);
			}
		}
		return ($find);
	}
	public static function create_response ($author_response,
											$poster_id,
											$application,
											$status_id,
											$replyValue) {
		/* yes, already input, update remark */
		/* add new status in the description */
		// $description = $description." - ".$remark_status;
		$reponse_id	= Atomik_Db::insert("bug_messages",
										array("description"=>stripslashes($author_response),
												"posted_by"=>$poster_id,
												"application"=>$application,
												"status"=>$status_id,
												"reply_id"=>$replyValue));
		return($reponse_id);
	}
	public static function create_remark ($description,
									$poster_id,
									$abstract_remark,
									$category_id,
									$application,
									$status_id,
									$remark_date,
									$paragraph,
									$line,
									$justification,
									$action_id="") {
      /* no, input remark for the firt time*/
		$updateReply	= Atomik_Db::insert("bug_messages",
								array("description"=>$description,
										"posted_by"=>$poster_id,
										"subject"=>$abstract_remark,
										"category"=>$category_id,
										"criticality"=>1,
										"application"=>$application,
										"status"=>$status_id,
										"date"=>$remark_date,
										"paragraph"=>$paragraph,
										"line"=>$line,
										"reply_id"=>0,
										"justification"=>$justification,
										"action_id"=>$action_id));				
		Atomik_Db::update("bug_messages",
					array("reply_id"=>$updateReply),
					array("id"=>$updateReply,"reply_id"=>0));			
		return ($updateReply);
  }
	public static function update_remark ($description,
									$poster_id,
									$abstract_remark,
									$category_id,
									$application,
									$status_id,
									$remark_date,
									$paragraph,
									$line,
									$justification,
									$update_id,
									$action_id="") {			

			$result_response = Atomik_Db::update("bug_messages",
						array("description"=>$description,
								"posted_by"=>$poster_id,
								"subject"=>$abstract_remark,
								"category"=>$category_id,
								"criticality"=>1,
								"application"=>$application,
								"status"=>$status_id,
								"date"=>$remark_date,
								"paragraph"=>$paragraph,
								"line"=>$line,
								"justification"=>$justification,
								"action_id"=>$action_id),
						array("id"=>$update_id));

  }  
	/* convert date to SQL format */
	public function convert_date ($or_date) {
		preg_match ("#([0-9]{2})\/([0-9]{2})\/([0-9]{4})#",$or_date,$date_regexp);
		$sql_date = $date_regexp[3]."-".$date_regexp[2]."-".$date_regexp[1];
		return($sql_date); 	
	}
	/**
	 * Get color of the status
	 *
	 * @return color
	 */
	private function get_status_color () {
		$color ="";
		switch ($this->status_id)
		{
			case ACCEPTED:
				/* Remark Accepted */
				$color ="red";
				break;
			case QA_ACCEPTANCE:
			case ACTION_CLOSED:
			case HPR_CLOSED: /* HPR closed */
				/* Remark QA Acceptance */
				$color ="green";
				break;
			case POSTPONED:
				/* Remark Postponed */
				$color ="yellow";
				break;
			case TO_BE_REVIEWED:
				/* Remark to be reviewed */
				$color ="orange";
				break;				
			case REJECTED:
				/* Remark Rejected */
				$color ="grey";
				break;				
			case SUBMITTED:
				if (!Remark::remark_response_exist ($this->id)) {
					$color = "pastel_red";
				}
				else{
					$color = "white";	
				}
				break;				
			default:
				$color ="";
				break;
		}
		return ($color);
	}
	/**
	 * Get color of the status
	 *
	 * @return color
	 */
	private function get_action_color () {
		$color ="";
		if (($this->action_id == 0) &&($this->justification !=""))
		{
      $color ="red";
    }
    else {
			$color ="";
		}
		return ($color);
	}	
	/**
	 * Check if a response to a remark exists
	 *
	 * @return boolean
	 */
	private static function remark_response_exist ($id){
		if ($id != ""){
			$sql_query = "SELECT reply_id, date, ".
				"bug_messages.id as id, ".
				"description,fname,lname ".
				"FROM bug_messages LEFT OUTER JOIN bug_users ON bug_messages.posted_by = bug_users.id ".
				"WHERE reply_id != bug_messages.id AND reply_id = ".$id." ORDER BY reply_id ASC, id ASC";
			$result_response = A('db:'.$sql_query)->fetchAll();
			// if ($id==9806)echo $sql_query;
			/* amount of rows */
			$nb_row_response=count($result_response);
			// echo "TEST:".$nb_row_response."<br/>";
			if ($nb_row_response != 0)
				return (true);
			else
				return (false);
		}
		else {
				return (false);		
		}
	}
	/**
	 * Create array of responses for a specific remark
	 * @param array $response_list 
	 * @returnn int nb responses
	 */
	public function get_response($response_list = array()) {
		/* build author response */
		$sql_query = "SELECT ".
						"date, ".
						"bug_messages.id as id, ".
						"description, ".
						"fname, ".
						"lname ".
						"FROM bug_messages LEFT OUTER JOIN bug_users ON bug_messages.posted_by = bug_users.id ".
						"WHERE reply_id != bug_messages.id AND reply_id = ".$this->id." ORDER BY reply_id ASC, id ASC";
		$result_response = $this->db->db_query($sql_query)->fetchAll();
		/* amount of rows */
		$nb_responses=count($result_response);
		
		if ($nb_responses != 0) {
			Atomik::needed("Date.class");
			foreach($result_response as $row_response):
				$date_response = Date::convert_date($row_response['date']);
			    /* Author response*/
			    $response_list[] = "<u>Response posted by <b>".$row_response['fname']." ".$row_response['lname']."</b> on <b>".$date_response."</b></u><br />".
					"<p>".$row_response['description']."</p>";
		    endforeach;
		}
		return ($nb_responses);
	}
   	public function prepare(){
		$sql_query = $this->sort_remarks()."  LIMIT :debut,:nombre";  //id ASC id ASC
		// echo $sql_query."<br/>";
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
	public static function setStatus($remark_id,
									 $status_id){
		/* update remark status */
		$sql_query = "UPDATE `bug_messages` SET `status`='$status_id' WHERE `reply_id`='$remark_id'";
		$result = A("db:".$sql_query);
		return($result);
	}
	public function getRemarks(){
		$sql_query = $this->sort_remarks();
		//echo $sql_query."<br/>";exit();
		$result = $this->db->db_query($sql_query);
		$list_data = $result->fetchAll(PDO::FETCH_ASSOC);
		return($list_data);
	}
	public static function getStatusName($status_id){
		$list = Atomik_Db::find('bug_status',array("`type`" => 'peer review',"id"=>$status_id),"`name` ASC");
		return($list['name']);		
	}
	public static function getTransitions($status_id){
		$list = Atomik_Db::find('bug_status',array("`type`" => 'peer review',"id"=>$status_id),"`name` ASC");
		$list_array = explode(",",$list['transition']);
		return($list_array);		
	}	
	public static function getStatusList(){
		$list = Atomik_Db::findAll('bug_status',"`type` = 'peer review'","`name` ASC");
		return($list);		
	}	
	public static function getSelectStatus($selected,$onchange="inactive",$label="show_status"){
		$html ='<label for="'.$label.'">Status:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html.= ' name="'.$label.'">';
		$html.='<option value=""/> --All--';
		foreach(Remark::getStatusList() as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['name'];
		endforeach;
		$html .='</select>';
		return($html);		
	}	
	public static function getCategoryList($type="generic"){
		$list = Atomik_Db::findAll('bug_category',"`type` = '{$type}'","`name` ASC");
		return($list);		
	}	
	public static function getSelectCategory($selected,$onchange="inactive",$type="generic"){
		$html ='<label for="show_category">Category:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		else{
			$html .= 'onchange="category_explain(this)"';
		}
		$html.= ' name="show_category">';
		foreach(Remark::getCategoryList($type) as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['name'];
		endforeach;
		$html .='</select>';
		return($html);		
	}	
	public function count() {
		$sql_query = "SELECT DISTINCT(bug_messages.id) ".
					"FROM bug_messages ".
					"LEFT OUTER JOIN bug_status ON bug_status.id = bug_messages.status ".
					"LEFT OUTER JOIN bug_category ON bug_category.id = bug_messages.category ".
					"LEFT OUTER JOIN bug_criticality ON bug_criticality.level = bug_messages.criticality ".
					"LEFT OUTER JOIN baseline_join_data ON baseline_join_data.data_id = bug_messages.application ".
					"LEFT OUTER JOIN bug_users ON bug_users.id = bug_messages.posted_by ".
					"LEFT OUTER JOIN bug_applications ON bug_applications.id = bug_messages.application ".
					"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
					"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
					"LEFT OUTER JOIN aircrafts ON aircrafts.id = projects.aircraft_id ".
					"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".				
					"WHERE bug_messages.reply_id = bug_messages.id ".
					$this->which_aircraft.
					$this->which_project.
					$this->which_sub_project.
					$this->which_status.
					$this->which_data.
					$this->which_category.
					$this->which_assignee.	
					$this->which_baseline.
					$this->search_query	.			
					" GROUP BY id";			
		// echo $sql_query; 
		$result = $this->db->db_query($sql_query);
		$nb_tab = $result->fetchAll(PDO::FETCH_ASSOC);
		$nb = count($nb_tab);
	    return($nb);	
	}	
	public function sort_remarks(){
		$sql_query = "SELECT bug_messages.description as remark, ".
					"bug_users.fname, ".
					"bug_users.lname, ".
					"bug_category.name as category, ".
					"bug_criticality.name as criticality, ".
					"bug_status.name as status, ".
					"bug_messages.posted_by, ".
					"bug_messages.id as id, ".
					"bug_messages.reply_id as reply_id, ".
					"bug_messages.date, ".
					"bug_messages.subject, ".
					"bug_messages.paragraph, ".
					"bug_messages.line, ".
					"bug_messages.status as status_id, ".
					"bug_messages.justification, ".
					"bug_messages.action_id, ".
					"bug_messages.application as id_data, ".
					"bug_applications.application, ".
					"bug_applications.version, ".
					"bug_applications.id as data_id, ".
					"projects.project, ".
					"lrus.lru, ".
					"data_cycle_type.name as type  ".
					"FROM bug_messages ".
					"LEFT OUTER JOIN bug_status ON bug_status.id = bug_messages.status ".
					"LEFT OUTER JOIN bug_category ON bug_category.id = bug_messages.category ".
					"LEFT OUTER JOIN bug_criticality ON bug_criticality.level = bug_messages.criticality ".
					"LEFT OUTER JOIN baseline_join_data ON baseline_join_data.data_id = bug_messages.application ".
					"LEFT OUTER JOIN bug_users ON bug_users.id = bug_messages.posted_by ".
					"LEFT OUTER JOIN bug_applications ON bug_applications.id = bug_messages.application ".
					"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
					"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
					"LEFT OUTER JOIN aircrafts ON aircrafts.id = projects.aircraft_id ".
					"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".				
					"WHERE bug_messages.reply_id = bug_messages.id ".
					$this->which_aircraft.					
					$this->which_project.
					$this->which_sub_project.
					$this->which_status.
					$this->which_data.
					$this->which_category.
					$this->which_baseline.
					$this->which_assignee.	
					$this->search_query.					
					" GROUP BY id ";	
		return ($sql_query);
	}	
	public function getPlainDescription(){
		Atomik::needed('Tool.class');	
		return(Tool::convert_html2txt($this->remark));
	}
	public static function getRemarkDocument($id){
		/* Read previous status */
		$sql_query = "SELECT application".
					" FROM bug_messages ".
					"WHERE bug_messages.id = {$id}";
		// $response = do_query($sql_query);
		$application_id = A("db:".$sql_query)->fetch(PDO::FETCH_OBJ)->application;
		return($application_id);
	}	
	public static function getRemarkStatus($id){
		$status = "";
		/* Read previous status */
		$sql_query = "SELECT bug_status.name as status".
					" FROM bug_messages ".
					"LEFT OUTER JOIN bug_status ON bug_status.id = bug_messages.status ".
					"WHERE bug_messages.id = {$id}";
		// $response = do_query($sql_query);
		// echo $sql_query."<br/>";
		$result = A("db:".$sql_query)->fetch(PDO::FETCH_OBJ);
		if ($result){
			$status = $result->status;
			if ($status == "") {
				$status = "Submitted";
			}	
		}		
		return($status);
	}
	public function get ($id) {
		$sql_query = "SELECT bug_messages.description as remark, ".
					"bug_users.id as poster_id, ".
					"bug_users.fname, ".
					"bug_users.lname, ".
					"bug_category.name as category, ".
					"bug_criticality.name as criticality, ".
					"bug_status.name as status, ".
					"bug_messages.posted_by, ".
					"bug_messages.category as category_id, ".
					"bug_messages.id as id, ".
					"bug_messages.reply_id as reply_id, ".
					"bug_messages.date, ".
					"bug_messages.subject, ".
					"bug_messages.paragraph, ".
					"bug_messages.line, ".
					"bug_messages.status as status_id, ".
					"bug_messages.justification, ".
					"bug_messages.action_id, ".
					"bug_messages.application as id_data, ".
					"bug_applications.application, ".
					"bug_applications.version, ".
					"bug_applications.id as data_id, ".
					"projects.id as project_id, ".
					"projects.project, ".
					"lrus.id as sub_project_id, ".
					"lrus.lru, ".
					"data_cycle_type.name as type  ".
					"FROM bug_messages ".
					"LEFT OUTER JOIN bug_status ON bug_status.id = bug_messages.status ".
					"LEFT OUTER JOIN bug_category ON bug_category.id = bug_messages.category ".
					"LEFT OUTER JOIN bug_criticality ON bug_criticality.level = bug_messages.criticality ".
					"LEFT OUTER JOIN baseline_join_data ON baseline_join_data.data_id = bug_messages.application ".
					"LEFT OUTER JOIN bug_users ON bug_users.id = bug_messages.posted_by ".
					"LEFT OUTER JOIN bug_applications ON bug_applications.id = bug_messages.application ".
					"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
					"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
					"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".				
					"WHERE bug_messages.reply_id = bug_messages.id ".
					" AND bug_messages.id = {$id}";
		// echo $sql_query;			
		$result = $this->db->db_query($sql_query);
		$row = $result->fetch(PDO::FETCH_ASSOC);			
		$this->id=$row['id'];
		$this->reply_id=$row['reply_id'];
		$this->project=$row['project'];
		$this->lru=$row['lru'];
		$this->project_id = $row['project_id'];;
		$this->sub_project_id = $row['sub_project_id'];;	
		$this->version=$row['version'];
		$this->type=$row['type'];
		$this->reference=$row['application']." ".$row['type']." i".$row['version'];
		$this->ref=$row['application'];
		$this->data_id=$row['data_id'];
		if ($row['action_id'] != 0) {
			  $this->action_id=$row['action_id'];
			}
			else {
			  $this->action_id="";
		}
		if ($row['status_id'] == 0) {
			$this->status="Submitted";
			$this->status_id=15; // Submitted
		}
		else {
			$this->status=$row['status'];
			$this->status_id=$row['status_id'];
		}
		$this->criticality=$row['criticality'];
		$this->justification = $row['justification'];
		$this->category_id=$row['category_id'];
		$this->category=$row['category'];
		$this->poster = $row['fname']." ".$row['lname'];
		$this->assignee_id = $row['poster_id'];
		$this->subject = $row['subject'];
		$this->remark = $row['remark'];
		$paragraph_tmp = $row['paragraph'];
		if ($this->subject == "") {
		    $this->subject = substr($this->getPlainDescription(),0,100);
		    $this->subject.= "...";  
		}
		  /* test if paragraph is normal or not */
		  if (preg_match ("#^[0-9]#",$paragraph_tmp)) {
			  $this->paragraph = " &sect;".$paragraph_tmp;
		  }
		  else {
			  $this->paragraph = " ".$paragraph_tmp;
		  }
		  /* test if line is normal or not */
		  if (preg_match ("#^[0-9]#",$row['line'])) {
			  $this->line = " line ".$row['line'];
		  }
		  else {
			  $this->line = " ".$row['line'];
		  }
	    if ($row['date'] != "0000-00-00") {
            /* Convert date to display nicely */
            Atomik::needed("Date.class");
            $this->small_date=Date::SmallDate($row['date']);
            $this->date=Date::convert_date($row['date']);
			$this->date_dojo = substr($row['date'],0,10);
        }
        else {
            $this->date="undefined";
	        $this->small_date="undefined";
			$this->date_dojo = Date::getTodayDate();
        }
		$this->item = "{$this->project} {$this->lru} {$this->reference} {$this->type} issue {$this->version}";
		$this->see_remark = "{$this->paragraph} {$this->line} {$this->remark}";

		$this->color_status = $this->get_status_color ();
		$this->color_action = $this->get_action_color ();
	}	
	public function reset(){
		$this->status_id = 15;/* Submitted */	
		$this->category_id= 1;	/* Missing */		
		$this->assignee_id= User::getIdUserLogged(); /* User logged in */
		$this->data_id = "";
	}
	public function setDocument($id,$one=true){
		Atomik::needed("Tool.class");
		Atomik::needed('Data.class');
		$this->data_id = $id;
		//$this->which_data = Tool::setFilter("bug_applications.id",$this->data_id);
		$this->which_project = "";
		$this->which_sub_project = "";
		$this->which_status = "";
		$this->which_category = "";
		$this->which_baseline = "";
		$this->which_assignee = "";	
		$this->search_query = "";
		$data = new Data;
		$data->get($id);
		$this->project_id=$data->project_id;
		$this->sub_project_id=$data->lru_id;
    	$this->data_id = $id;
		$this->version = $data->version;
		/* $this->reference = $document->reference;	*/
		$this->project_id = "";
		$this->lru_id = "";
		$this->poster_id = "";
		$this->category_id = "";
		$this->criticality_id ="";
		$this->data_type = $data->type;
		$this->baseline_id = "";
		/*
		if ($this->data_id != "") {	    
			// check final version   	
			if (($this->check_final_version())&&(!$one)){
				$this->which_data = " AND bug_applications.application = '{$this->reference}' AND bug_applications.version REGEXP '^".$this->version."$' ";
			}
			else {*/
				$this->which_data = " AND bug_messages.application = {$this->data_id} ";
			/*}
		}
		else {
			$this->which_data = "";		
		}*/
		$this->amount_remarks = $this->count_sort_remarks();		
	}
	public function resetDate(){
		Atomik::needed("Date.class");
		$this->date_dojo = Date::getTodayDate();
	}
	private function echo_map(&$node) {
	
		$selected = self::getStatusName($this->status_id);
		// echo $node['name']." : ".$selected;exit();
		$output = "";
		$x = $node['x'];
		$y = $node['y'];
		$output .= "<a href=\"?remark_id={$this->id}&status_id={$node['id']}&current_status_id={$this->status_id}\" onclick=\"window.top.window.ouvrir('".Atomik::url('edit_data',array('id'=>$node['id']))."','_blank')\">";
		$output .= "<div style=\"position:absolute;left:{$x};top:{$y};width:{$node['w']};height:{$node['h']};" . ($selected == $node['name'] ? "background-color:red;filter:alpha(opacity=40);opacity:0.4;" : "") . "\">&nbsp;</div></a>\n";
		for ($i = 0; $i < count($node['childs']); $i++) {
			$output .= $this->echo_map($node['childs'][$i], $selected);
		}
		$output .= "<a href='".Atomik::url('edit_data',array('id'=>$node['id']))."'>open</a>";
		return($output);
	}   
   private static function concatDocName($reference,
									  $version){
   		if ($version != ""){
			$name = $reference.' issue '.$version;
		}
		else{
			$name = $reference;
		}
		return($name);
   }
   public function createDiagram(){
		require_once 'diagram/class.diagram.php';
		require_once 'diagram/class.diagram-ext.php';
		//$diagram_file ='../result/diagram.xml';
		$output = "";
		$diagram_file = dirname(__FILE__).DIRECTORY_SEPARATOR.
										"..".DIRECTORY_SEPARATOR.
										'actions'.
										DIRECTORY_SEPARATOR.
										'peer_review'.
										DIRECTORY_SEPARATOR.					
										'peer_review_workflow.xml';
		var_dump($diagram_file);			
		$diagram = new DiagramExtended($diagram_file);
		$diagram_display = new Diagram(realpath($diagram_file));
		$diagram_png="../result/diagram.png";
		$diagram_display->Draw($diagram_png);
	
		$output = '<img src="../'.$diagram_png.'" border="0" style="position:absolute;left:0;top:0" />';

		$diagram_node_position = $diagram->getNodePositions();
		$output .= $this->echo_map($diagram_node_position); 
		return ($output);
   }	
	/**
	 * Read remark in db
	 *
	 * @param array $row remark characteristics
	 */
	public function __construct ($context=null) {
		if (isset($context)){
			$this->aircraft_id = isset($context['aircraft_id'])? $context['aircraft_id'] : "";
			$this->project_id = isset($context['project_id'])? $context['project_id'] : "";
			$this->sub_project_id = isset($context['sub_project_id'])? $context['sub_project_id'] : "";	
			$this->status_id= isset($context['remark_status_id'])? $context['remark_status_id'] : "";
			$this->category_id= isset($context['category_id'])? $context['category_id'] : "";	
			$this->assignee_id= isset($context['user_id'])? $context['user_id'] : "";		
			$this->data_id= isset($context['data_id'])? $context['data_id'] : "";
			$this->baseline_id= isset($context['baseline_id'])? $context['baseline_id'] : "";				
			$search= isset($context['remarks_search'])? $context['remarks_search'] : Atomik::get('session/search');	
			$this->search = "AND ((bug_messages.subject LIKE '%$search%') ".
								"OR (bug_messages.description LIKE '%$search%') ".
								"OR (bug_messages.paragraph LIKE '%$search%') ".
								"OR (bug_messages.id LIKE '%$search%') ".
								"OR (bug_applications.application LIKE '%$search%')) ";	
			$this->order= isset($context['order'])? $context['order'] : "";
		
		}
		else {
			$this->aircraft_id = "";
			$this->project_id = "";
			$this->sub_project_id = "";	
			$this->status_id= "";
			$this->category_id= "";	
			$this->assignee_id= "";		
			$this->data_id= "";
			$this->baseline_id= "";				
			$this->search = "";	
			$this->search_query ="";
			$this->order= "";		
		}
		$this->action_id = "";
		$this->remark = "";
		$this->justification = "";
		Atomik::needed("Date.class");
		$this->date = Date::getTodayDate();
		$this->date_dojo = Date::getTodayDate();
		Atomik::needed("Tool.class");
		$this->which_aircraft 		= Tool::setFilter("aircrafts.id",$this->aircraft_id);
		$this->which_project 		= Tool::setFilter("projects.id",$this->project_id);
		$this->which_sub_project 	= Tool::setFilter("lrus.id",$this->sub_project_id);
		$this->which_data 			= Tool::setFilter("bug_applications.id",$this->data_id);
		//$this->which_reference 		= Tool::setFilter("bug_applications.application",$this->reference);
		$this->which_category 		= Tool::setFilter("bug_messages.category",$this->category_id);
		$this->which_status 	   	= Tool::setFilter("bug_messages.status",$this->status_id);
		$this->which_assignee 		= Tool::setFilter("bug_messages.posted_by",$this->assignee_id);
		$this->which_baseline 		= Tool::setFilter("baseline_join_data.baseline_id",$this->baseline_id);
		$this->amount_remarks	 = $this->count_sort_remarks();
		$this->count_all_remarks();			
		if ($this->search != ""){
			$this->search_query = $this->search;
		}
		Atomik::needed("Db.class");	
		$this->db =new Db;
	}
	public function insert($info){ 
		   $description = isset($info['description'])?$info['description']:"";
		   $poster_id 	= isset($info['poster_id'])?$info['poster_id']:""; 
		   $subject 	= isset($info['subject'])?$info['subject']:""; 
		   $category 	= isset($info['category_id'])?$info['category_id']:"";
		   $criticality = isset($info['criticality_id'])?$info['criticality_id']:"";
		   $application = isset($info['application'])?$info['application']:"";
		   $status 		= isset($info['status_id'])?$info['status_id']:"";
		   Atomik::needed("Date.class");
		   if (!isset($info['date'])){
				$date_sql = Date::getTodayDate();
		   }
		   else{
				$date_sql = Date::convert_dojo_date($info['date']);  
		   }		   
		   $paragraph 		= isset($info['paragraph'])?$info['paragraph']:"";  
		   $line 			= isset($info['line'])?$info['line']:"";
		   $justification 	= isset($info['justification'])?$info['justification']:"";
		   $action_id 		= isset($info['action_id'])?$info['action_id']:"";
			$new_remark_id = Atomik_Db::insert('bug_messages',array('description'=>$description,
											  'posted_by'=>$poster_id,
											  'subject'=>$subject,
											  'category'=>$category,
											  'criticality'=>$criticality,
											  'application'=>$application,
											  'status'=>$status,
											  'date'=>$date_sql,
											  'paragraph'=>$paragraph,
											  'line'=>$line,
											  'reply_id'=>0,
											  'justification'=>$justification,
											  'action_id'=>$action_id));					
			// $new_remark_id = $this->db->db_query($sql_query);
            // $sql_query = "UPDATE `bug_messages` SET `reply_id`='$new_remark_id' WHERE `id` = '$new_remark_id' AND `reply_id` = '0' LIMIT 1";
            $result = Atomik_Db::update('bug_messages',array('reply_id'=>$new_remark_id),array('id'=>$new_remark_id,'reply_id'=>0));    		
			if ($result) {
				ob_start("manage_log");
				$text = "New remark {$new_remark_id} added by ".User::getNameUserLogged()." at ".date('H:i:s')." on ".date('Y/m/d');
				echo $text;
				ob_end_clean();
			}
			return($new_remark_id);
	}
	public function update($info){
	   $id 			= $info['id'];
	   $description = $info['description'];
	   $poster_id 	= $info['poster_id']; 
	   $subject 	= $info['subject']; 
	   $category 	= $info['category_id'];
	   $criticality = $info['criticality_id'];
	   $application = $info['application'];
	   $status 		= $info['status_id'];
	   Atomik::needed("Date.class");
	   $date_sql 	= Date::convert_dojo_date($info['date']);    
	   $paragraph 	= $info['paragraph'];  
	   $line 		= $info['line'];
	   $justification 	= $info['justification'];
	   $action_id 		= $info['action_id'];
		
		$result = $this->db->update('bug_messages',array('description'=>$description,
													  'posted_by'=>$poster_id,
													  'subject'=>$subject,
													  'category'=>$category,
													  'criticality'=>$criticality,
													  'application'=>$application,
													  'status'=>$status,
													  'date'=>$date_sql,
													  'paragraph'=>$paragraph,
													  'line'=>$line,
													  'justification'=>$justification,
													  'action_id'=>$action_id),array('id' => $id));
													  
		// $result = $this->db->update('bug_messages',array('posted_by'=>$poster_id),array('id' => $id));						  
		if ($result) {
			ob_start("manage_log");
			$text = "New remark {$id} updated by ".User::getNameUserLogged()." at ".date('H:i:s')." on ".date('Y/m/d');
			echo $text;
			ob_end_clean();
		}
		return($result);
	}	
	public function set($remark_id){
		$this->id = $remark_id;
	}
	public function getExportFilename(){
		Atomik::needed('Project.class');
		$today_date_underscore = date("Y").'_'.date("M").'_'.date("d");
		$project = new Project;
		$project_name = $project->get_project_name($this->project_id);
		$sub_project_name = $project->get_sub_project_name($this->sub_project_id);
		if ($sub_project_name != "")$sub_project_name .= "_";
		$filename = $project_name."_".$sub_project_name."Peer_Review_".$today_date_underscore.".xlsx";
		return($filename);
	}
	public function getExportTitle(){
		Atomik::needed('Project.class');
		$project = new Project;
		$project_name = $project->get_project_name($this->project_id);
		$sub_project_name = $project->get_sub_project_name($this->sub_project_id);
		if ($sub_project_name != "")$sub_project_name .= " ";
		$title = $project_name." ".$sub_project_name."Peer_Review";
		return($title);
	}		
	public function exportXlsx($directory=""){
		require_once "../excel/Classes/PHPExcel.php";
		require_once '../excel/Classes/PHPExcel/IOFactory.php';
		require_once '../excel/Classes/PHPExcel/Worksheet/RowIterator.php';
		Atomik::needed("ExportXls.class");
		include("app/includes/ExportXls.class.php");
		require_once("pChart/pData.class");  
		require_once("pChart/pChart.class");  
		
		Atomik::needed("Data.class");
		$data = new Data;
		$list_remarks = $this->getRemarks();
		$amount_remarks=count($list_remarks);
		if ($this->data_id != "") {
			$data->get($this->data_id);
			if ($data->version != ""){
				$reference = $data->reference." issue ".$data->version;
			}
			else{
				$reference = $data->reference;
			}
		}
		if ($amount_remarks > 0){
			// Set the enviroment variable for GD
			putenv('GDFONTPATH=' . realpath('.'));
			error_reporting(E_ALL);
			$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
			PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
			$file_template = dirname(__FILE__).
							DIRECTORY_SEPARATOR."..".
							DIRECTORY_SEPARATOR."..".
							DIRECTORY_SEPARATOR."assets".
							DIRECTORY_SEPARATOR."template".
							DIRECTORY_SEPARATOR."SAQ225_3.xlsx";
			if (!file_exists($file_template)) {
				echo "Warning: Excel peer review template is missing.<br/>".$file_template;
				exit();
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
			Atomik::needed("PeerReviewer.class");
			$peer_reviewers = new PeerReviewer;
			//$remarks = new StatRemarks;
			$bar_filename = '../result/remarks_bar.png';
			if ($this->data_id != "") {
				$type_data = $data->type;
				$title = $data->project.' '.$data->lru.' '.$data->type;
				$author = $data->author;
				$author_email = $data->email;
				/* Check upper documents */
				$data_id = $this->data_id;
				$parent_data_reference = "";
				$found_upper_data = Data::Get_List_Upper_Data($data_id,&$list_upper);
				if ($found_upper_data){  
					foreach ($list_upper as $parent_data) {
						/* Parent document */
						// var_dump($parent_data);
						$parent_data_reference = $parent_data_reference.$parent_data['reference'].' issue '.$parent_data['version']."\n";
					}  
				}
				else{
					$parent_data_reference = "NA";
				}
				$table_type_data = array(
										"SSCS" => "SAQ234",
										"HWRD" => "SAQ234",
										"HWDD" => "SAQ235");
				foreach ($table_type_data as $type_spec => $saq_checklist) {
					//echo $type_spec." ".$saq_checklist."<br/>";	
					if ($type_data == $type_spec) {
						$checklist_reference = $saq_checklist;
						break;
					}
				}
				$checklist_reference ="NA";
				switch($type_data){
					case "SQAP":
						$file_checklist = "checklist/xlsx/SAQ127_SQAP.xlsx";
						$checklist_reference = "SAQ127";
						break;
					case "PSAC":
						$file_checklist = "checklist/xlsx/SAQ125_PSAC.xlsx";
						break;
					case "SMP":
						$file_checklist = "checklist/xlsx/SAQ126_SMP.xlsx";
						break;	
					case "SAS":
						$file_checklist = "checklist/xlsx/SAQ128_SAS.xlsx";
						break;	
					case "SwRD":
						$file_checklist = "checklist/xlsx/SAQ129_SwRD.xlsx";
						break;
					case "SwDD":
						$file_checklist = "checklist/xlsx/SAQ130_SwDD.xlsx";
						$checklist_reference = "SAQ130";
						break;
					case "STDR":
						$file_checklist = "checklist/xlsx/SAQ132_STDR-UT.xlsx";
						break;
					case "SVR":
						$file_checklist = "checklist/xlsx/SAQ135_SVR.xlsx";
						break;
					case "SCI":
						$file_checklist = "checklist/xlsx/SAQ136_SVR.xlsx";
						break;
					case "SES":
						$file_checklist = "checklist/xlsx/SAQ234_SES.xlsx";
						break;    	
					default:
						$file_checklist = "checklist/xlsx/generic_checklist.xlsx";
						break;
				}	
				Atomik::needed("Tool.class");
				$filename = $data->small_ident."_PRR.xlsx";
				$filename = Tool::cleanFilename($filename);
				$filename_hyperlink = $filename;
				$filename = "../result/{$directory}".$filename;
				/* find reader */
				$peer_reviewers->get($data->id);
				/* create bar graphic */
				$this->setDocument($data->id);
				$this->count_all_remarks();
			}	
			else{
				$title= $this->getExportTitle();
				$reference = "";
				$parent_data_reference = "";
				$checklist_reference = "";
				$author = "";		
			}
			$this->drawBar($bar_filename,"Statistics");
			$gd_img = @imagecreatefrompng($bar_filename);
			$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
			$objDrawing->setName('Remarks stats');
			$objDrawing->setDescription('Remarks stats');
			$objDrawing->setImageResource($gd_img);
			$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
			$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
			$objDrawing->setHeight(250);
			$objDrawing->setCoordinates('F3');
			$objDrawing->setOffsetX(20);
			$objDrawing->getShadow()->setVisible(true);
			$objDrawing->getShadow()->setDirection(45);	
			Atomik::needed("User.class");
			$username = User::getNameUserLogged();
			$reader = $username;		
			/* Review report */
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('C10', $title);
			$objPHPExcel->getActiveSheet()->setCellValue('C11', $reference);
			$objPHPExcel->getActiveSheet()->setCellValue('C12', $parent_data_reference);
			$objPHPExcel->getActiveSheet()->setCellValue('C13', $checklist_reference);
			$objPHPExcel->getActiveSheet()->setCellValue('C14', "Walkthrought");
			$objPHPExcel->getActiveSheet()->setCellValue('C22', $author);
			$objPHPExcel->getActiveSheet()->setCellValue('H14', $amount_remarks);
			$objPHPExcel->getActiveSheet()->setCellValue('H16', $peer_reviewers->index_peer_reviewer);

			if ($peer_reviewers->index_peer_reviewer > 0) {
				$index_peer_reviewer = 3;
				foreach ($peer_reviewers->peer_reviewer_tab as $name => $function) {
					$objPHPExcel->getActiveSheet()->setCellValue('C2'.$index_peer_reviewer, $name);
					$objPHPExcel->getActiveSheet()->setCellValue('F2'.$index_peer_reviewer, "X");
					$index_peer_reviewer++;
				}
			}
			$objPHPExcel->getActiveSheet()->getStyle('A1:J62')->applyFromArray($style_blank);
			/* Register sheet */
			$objPHPExcel->setActiveSheetIndex(1);
			$objPHPExcel->getActiveSheet()->setCellValue('C8', $title);
			$objPHPExcel->getActiveSheet()->setCellValue('C9', $reference);		
			$objPHPExcel->getActiveSheet()->setCellValue('C10', $parent_data_reference);
			$objPHPExcel->getActiveSheet()->setCellValue('C11', $checklist_reference);
			$objPHPExcel->getActiveSheet()->setCellValue('D17', $data->date_published);		
			$objPHPExcel->getActiveSheet()->setCellValue('F17', $author);

			/* find reader and function */
			if ($peer_reviewers->index_peer_reviewer > 0) {
				$index_peer_reviewer = 18;
				Atomik::needed("Date.class");
				foreach ($peer_reviewers->peer_reviewer_tab as $name => $function) {
					$objPHPExcel->getActiveSheet()->setCellValue('D'.$index_peer_reviewer, Date::convert_date_conviviale(Date::getTodayDate()));
					$objPHPExcel->getActiveSheet()->setCellValue('E'.$index_peer_reviewer,$function);
					$objPHPExcel->getActiveSheet()->setCellValue('F'.$index_peer_reviewer,$name);
					$objPHPExcel->getActiveSheet()->setCellValue('G'.$index_peer_reviewer, "In progress");
					$index_peer_reviewer++;
					echo date('H:i:s') . " Find reader <b>".$name."</b> ".$function."<br />";
				}
			}
			$objPHPExcel->getProperties()->setCreator($author)
										 ->setLastModifiedBy($username)
										 ->setTitle("peer ".$title." review")
										 ->setSubject("Ref:".$reference)
										 ->setDescription("Peer review report for ".$title." ".$reference)
										 ->setKeywords("PRR openxml php")
										 ->setCategory("Peer Review Report");
										 
			$row_header_table = 28;
			$row_counter = $row_header_table;
			$counter = 0;
			/* Header is white */
			$objPHPExcel->getActiveSheet()->getStyle('A1:I'.strval($row_header_table - 2))->applyFromArray($style_blank);
			/* Draw stats graphic for remarks */
			$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
			/* draw thick border around the remarks */
			$begin = $row_header_table; 
			$end = $amount_remarks + $row_header_table - 1;
			$objPHPExcel->getActiveSheet()->getStyle('A'.strval($begin).':I'.strval($end))->applyFromArray($style_encadrement);
			foreach($list_remarks as $row):
				if ($row_counter % 2) {
					/* alternate white and grey line color */
					$objPHPExcel->getActiveSheet()->getStyle('A'.$row_counter.':I'.$row_counter)->applyFromArray($style_white_line_prr);
				}
				$counter++;
				$user = $row['fname']." ".$row['lname'];
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$row_counter, "R".$counter);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$row_counter, $user);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$row_counter, $row['paragraph']);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$row_counter, $row['line']);
				/* Remark */
				if ($this->data_id == "") {
					$remark= " [".$row['criticality']."] ".$row['application']." ".$row['type']." ".$row['version']." ".$row['remark']." [#".$row['id']."#] ";
				}
				else {
					$remark= $row['remark'];
				}
				$text= Tool::convert_html2txt($remark,"UTF-8");
				//if ($counter == 21){
					// var_dump($row['remark']);
					//var_dump($text);
				//	exit();
				//}
				$text = Tool::filter($text);

				$objPHPExcel->getActiveSheet()->setCellValue('E'.$row_counter, $text);
				// $objPHPExcel->getActiveSheet()->getStyle('E'.$row_counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				/* Author response */
				/* build author response */
				$sql = "SELECT * ".
						"FROM bug_messages ".
						"LEFT OUTER JOIN bug_users ON bug_users.id = bug_messages.posted_by".
						" WHERE reply_id != bug_messages.id AND reply_id = ".$row['id']." ORDER BY reply_id ASC, bug_messages.id ASC";
				$result_response = A("db:".$sql);
				/* erase author response buffer */
				$author_response="";
				foreach($result_response as $row_response) {
					$date_response = Date::convert_date_conviviale($row_response['date']);
					$text = Tool::clean_text($row_response['description']);
					$text = Tool::filter($text);
					$author_response = $author_response."[".$date_response."] ".$row_response['fname']." ".$row_response['lname']."\n".$text."\n\n";
				}
				Tool::clean_author_response(&$author_response);
				//echo $author_response;	
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$row_counter, $author_response);
				/* Defect class */
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$row_counter, $row['category']);
				/* Status */
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$row_counter, $row['status']);
				if (preg_match("#Accepted#", $row['status'])) {
						//$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
						//$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
						//$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->getStartColor()->setARGB('FFFF0000');
				}
				/* justification */
				$objPHPExcel->getActiveSheet()->setCellValue('I'.$row_counter, $row['justification']);
				/* id */
				$objPHPExcel->getActiveSheet()->setCellValue('J'.$row_counter, $row['id']);
				/* border inside */
				$objPHPExcel->getActiveSheet()->getStyle('A'.$row_counter.':J'.$row_counter)->getBorders()->getInside()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$row_counter++;
				// if ($counter == 7){var_dump($row['remark']);continue;}
			endforeach;
			if ($row_counter > $row_header_table) {
			  /* To apply an autofilter to a range of cells */
			  $row_header_table = $row_header_table-1;
			  $objPHPExcel->getActiveSheet()->setAutoFilter('A'.$row_header_table.':I'.$row_header_table);
			  /* To set a worksheet?s column visibility, hides column D */
			  $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setVisible(false);
			}		
			echo date('H:i:s') . " Find <b>".$amount_remarks."</b> remarks.<br />";
			  /* draw thick border around the remarks */
			// $objPHPExcel->getActiveSheet()->getStyle('A'.$row_header_table.':I'.strval($amount_remarks + $row_header_table - 1))->applyFromArray($style_encadrement);

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$result_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
										"..".DIRECTORY_SEPARATOR.
										"..".DIRECTORY_SEPARATOR.
										"..".DIRECTORY_SEPARATOR."result";
			// chmod($result_path.DIRECTORY_SEPARATOR.$filename_tmp, 0777);
			// var_dump(stat($result_path.DIRECTORY_SEPARATOR.$filename_tmp));
			$objWriter->save($filename);	
			$html ='<p>Peer review report for <b>'.$reference.'</b> document:<a href="../'.$filename.
			'" ><img alt="Export openxml" title="Export openxml" border="0" src="../assets/images/32x32/Excel2007.png" class="img_button" style="margin:8px;width:48px;height:48px" /></a></p>';
		}
		else {
			$html ="No internal remarks found for <b>{$reference}</b> document.";
			$filename_hyperlink = false;
		}
		echo $html."<br/>";
		return($filename_hyperlink);		
	}
	public function exportInspectionXlsx($directory=""){
		require_once "../excel/Classes/PHPExcel.php";
		require_once '../excel/Classes/PHPExcel/IOFactory.php';
		require_once '../excel/Classes/PHPExcel/Worksheet/RowIterator.php';
		Atomik::needed("ExportXls.class");
		include("app/includes/ExportXls.class.php");
		require_once("pChart/pData.class");  
		require_once("pChart/pChart.class");  
		
		Atomik::needed("User.class");
		$reader = User::getNameUserLogged();
		Atomik::needed("Data.class");
		$data = new Data;
		$list_remarks = $this->getRemarks();
		$amount_remarks=count($list_remarks);
		if ($this->data_id != "") {
			$data->get($this->data_id);
			if ($data->version != ""){
				$reference = $data->reference." issue ".$data->version;
			}
			else{
				$reference = $data->reference;
			}
		}
		if ($amount_remarks > 0){
			// Set the enviroment variable for GD
			putenv('GDFONTPATH=' . realpath('.'));
			error_reporting(E_ALL);
			$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
			PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
			$file_template = dirname(__FILE__).
							DIRECTORY_SEPARATOR."..".
							DIRECTORY_SEPARATOR."..".
							DIRECTORY_SEPARATOR."assets".
							DIRECTORY_SEPARATOR."template".
							DIRECTORY_SEPARATOR."IS.xlsx";
			if (!file_exists($file_template)) {
				echo "Warning: Excel inspection template is missing.<br/>".$file_template;
				// exit();
				$objPHPExcel = new PHPExcel;
				$objPHPExcel->getActiveSheet()->setTitle('Header');
				$objWorksheet = $objPHPExcel->createSheet();
				$objWorksheet->setTitle('CONTEXT');
				$objWorksheet = $objPHPExcel->createSheet();
				$objWorksheet->setTitle('REVIEW');
				$objWorksheet = $objPHPExcel->createSheet();
				$objWorksheet->setTitle('REMARKS');				
			}
			else {
				$objPHPExcel = PHPExcel_IOFactory::load($file_template);
			}
			Atomik::needed("PeerReviewer.class");
			$peer_reviewers = new PeerReviewer;
			$bar_filename = '../result/remarks_bar.png';
			if ($this->data_id != "") {
				$type_data = $data->type;
				$title = $data->project.' '.$data->lru.' '.$data->type;
				$author = $data->author;
				$author_email = $data->email;
				$found_upper_data = Data::Get_List_Upper_Data($this->data_id,&$list_upper);
				Atomik::needed("Tool.class");
				$filename = "IS_".$reference.".xlsx";
				$filename = Tool::cleanFilename($filename);
				$filename_hyperlink = $filename;
				$filename = "../result/{$directory}".$filename;
				/* find reader */
				$peer_reviewers->get($data->id);
				/* create bar graphic */
				$this->setDocument($data->id);
				$this->count_all_remarks();
			}	
			else{
				$title= $this->getExportTitle();
				$reference = "";
				$parent_data_reference = "";
				$checklist_reference = "";
				$found_upper_data = false;
				$author = "";		
			}
			$this->drawBar($bar_filename,"Statistics");
			$gd_img = @imagecreatefrompng($bar_filename);
			$objPHPExcel->setActiveSheetIndex(0);
			$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
			$objDrawing->setName('Remarks stats');
			$objDrawing->setDescription('Remarks stats');
			$objDrawing->setImageResource($gd_img);
			$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
			$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
			$objDrawing->setHeight(350);
			$objDrawing->setCoordinates('D9');
			$objDrawing->setOffsetX(20);
			$objDrawing->getShadow()->setVisible(true);
			$objDrawing->getShadow()->setDirection(45);
			$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());		
			/* Review report */
			$objPHPExcel->getActiveSheet()->setCellValue('B8', $reader);
			$objPHPExcel->getActiveSheet()->setCellValue('B9', date('Y-m-d'));
			$objPHPExcel->getActiveSheet()->setCellValue('B11', Tool::cleanDescription(($data->description)));
			$objPHPExcel->getActiveSheet()->setCellValue('B12', $reference);
			/* Check upper documents */

			$index_parent = 18;
			if ($found_upper_data){  
				foreach ($list_upper as $parent_data) {
					/* Parent document */
					$parent_data_reference = $parent_data['reference'].' issue '.$parent_data['version'];
					$objPHPExcel->getActiveSheet()->setCellValue('B'.$index_parent++, $parent_data_reference);
				}  
			}
			else{
				$parent_data_reference = "NA";
				$objPHPExcel->getActiveSheet()->setCellValue('B18', $parent_data_reference);
			}			
			// $objPHPExcel->getActiveSheet()->setCellValue('C13', $checklist_reference);
			// $objPHPExcel->getActiveSheet()->setCellValue('C14', "Walkthrought");
			$objPHPExcel->getActiveSheet()->setCellValue('B13', $author);
			$objPHPExcel->getActiveSheet()->setCellValue('E8', $amount_remarks);
			// $objPHPExcel->getActiveSheet()->setCellValue('H16', $peer_reviewers->index_peer_reviewer);
			/*
			if ($peer_reviewers->index_peer_reviewer > 0) {
				$index_peer_reviewer = 3;
				foreach ($peer_reviewers->peer_reviewer_tab as $name => $function) {
					$objPHPExcel->getActiveSheet()->setCellValue('C2'.$index_peer_reviewer, $name);
					$objPHPExcel->getActiveSheet()->setCellValue('F2'.$index_peer_reviewer, "X");
					$index_peer_reviewer++;
				}
			}*/
			// $objPHPExcel->getActiveSheet()->getStyle('A1:J62')->applyFromArray($style_blank);
			/* Register sheet */
			$objPHPExcel->setActiveSheetIndex(2);
			/*
			$objPHPExcel->getActiveSheet()->setCellValue('C8', $title);
			$objPHPExcel->getActiveSheet()->setCellValue('C9', $reference);		
			$objPHPExcel->getActiveSheet()->setCellValue('C10', $parent_data_reference);
			$objPHPExcel->getActiveSheet()->setCellValue('C11', $checklist_reference);
			$objPHPExcel->getActiveSheet()->setCellValue('D17', $data->date_published);		
			$objPHPExcel->getActiveSheet()->setCellValue('F17', $author);
			*/
			/* find reader and function */
			/*
			if ($peer_reviewers->index_peer_reviewer > 0) {
				$index_peer_reviewer = 18;
				Atomik::needed("Date.class");
				foreach ($peer_reviewers->peer_reviewer_tab as $name => $function) {
					$objPHPExcel->getActiveSheet()->setCellValue('D'.$index_peer_reviewer, Date::convert_date_conviviale(Date::getTodayDate()));
					$objPHPExcel->getActiveSheet()->setCellValue('E'.$index_peer_reviewer,$function);
					$objPHPExcel->getActiveSheet()->setCellValue('F'.$index_peer_reviewer,$name);
					$objPHPExcel->getActiveSheet()->setCellValue('G'.$index_peer_reviewer, "In progress");
					$index_peer_reviewer++;
					echo date('H:i:s') . " Find reader <b>".$name."</b> ".$function."<br />";
				}
			}*/
			$objPHPExcel->getProperties()->setCreator($author)
										 ->setLastModifiedBy($reader)
										 ->setTitle($title." inspection")
										 ->setSubject("Ref:".$reference)
										 ->setDescription("Inspection report for ".$title." ".$reference)
										 ->setKeywords("inspection openxml php")
										 ->setCategory("Inspection Report");
										 
			$row_header_table = 28;
			$row_counter = 2;
			$counter = 0;
			/* Draw stats graphic for remarks */
			
			/* draw thick border around the remarks */
			$begin = $row_header_table; 
			$end = $amount_remarks + $row_header_table - 1;
			// $objPHPExcel->getActiveSheet()->getStyle('A'.strval($begin).':I'.strval($end))->applyFromArray($style_encadrement);
			foreach($list_remarks as $row):
				// if ($row_counter % 2) {
					/* alternate white and grey line color */
					// $objPHPExcel->getActiveSheet()->getStyle('A'.$row_counter.':I'.$row_counter)->applyFromArray($style_white_line_prr);
				// }
				$counter++;
				$user = $row['fname']." ".$row['lname'];
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$row_counter, $counter);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$row_counter, $user);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$row_counter, $row['paragraph']." ".$row['line']);
				/* Remark */
				if ($this->data_id == "") {
					$remark= " [".$row['criticality']."] ".$row['application']." ".$row['type']." ".$row['version']." ".$row['remark']." [#".$row['id']."#] ";
				}
				else {
					$remark= $row['remark'];
				}
				$text= Tool::convert_html2txt($remark,"UTF-8");
				//if ($counter == 21){
					// var_dump($row['remark']);
					//var_dump($text);
				//	exit();
				//}
				$text = Tool::filter($text);

				$objPHPExcel->getActiveSheet()->setCellValue('B'.$row_counter, $text);
				// $objPHPExcel->getActiveSheet()->getStyle('E'.$row_counter)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
				/* Author response */
				/* build author response */
				$sql = "SELECT * ".
						"FROM bug_messages ".
						"LEFT OUTER JOIN bug_users ON bug_users.id = bug_messages.posted_by".
						" WHERE reply_id != bug_messages.id AND reply_id = ".$row['id']." ORDER BY reply_id ASC, bug_messages.id ASC";
				$result_response = A("db:".$sql);
				/* erase author response buffer */
				$author_response="";
				foreach($result_response as $row_response) {
					$date_response = Date::convert_date_conviviale($row_response['date']);
					$text = Tool::clean_text($row_response['description']);
					$text = Tool::filter($text);
					$author_response = $author_response."[".$date_response."] ".$row_response['fname']." ".$row_response['lname']."\n".$text."\n\n";
				}
				Tool::clean_author_response(&$author_response);
				/* Origin */
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$row_counter,$data->version);
				/* Author response */
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$row_counter, $author_response);
				/* Defect class */
				// $objPHPExcel->getActiveSheet()->setCellValue('G'.$row_counter, $row['category']);
				/* Status */
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$row_counter, $row['status']);
				if (preg_match("#Accepted#", $row['status'])) {
						//$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
						//$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
						//$objPHPExcel->getActiveSheet()->getStyle('H'.$row_counter)->getFill()->getStartColor()->setARGB('FFFF0000');
				}
				/* justification */
				// $objPHPExcel->getActiveSheet()->setCellValue('I'.$row_counter, $row['justification']);
				/* id */
				$objPHPExcel->getActiveSheet()->setCellValue('K'.$row_counter, $row['id']);
				/* border inside */
				// $objPHPExcel->getActiveSheet()->getStyle('A'.$row_counter.':J'.$row_counter)->getBorders()->getInside()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				$row_counter++;
				// if ($counter == 7){var_dump($row['remark']);continue;}
			endforeach;
			/* Header is white */
			$objPHPExcel->getActiveSheet()->getStyle('A2:J'.strval($row_counter - 2))->applyFromArray($style_blank);
		
			// if ($row_counter > $row_header_table) {
			  /* To apply an autofilter to a range of cells */
			  // $row_header_table = $row_header_table-1;
			  // $objPHPExcel->getActiveSheet()->setAutoFilter('A'.$row_header_table.':I'.$row_header_table);
			  /* To set a worksheet?s column visibility, hides column K */
			  // $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setVisible(false);
			// }		
			echo date('H:i:s') . " Find <b>".$amount_remarks."</b> remarks.<br />";
			  /* draw thick border around the remarks */
			// $objPHPExcel->getActiveSheet()->getStyle('A'.$row_header_table.':I'.strval($amount_remarks + $row_header_table - 1))->applyFromArray($style_encadrement);

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$result_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
										"..".DIRECTORY_SEPARATOR.
										"..".DIRECTORY_SEPARATOR.
										"..".DIRECTORY_SEPARATOR."result";
			// chmod($result_path.DIRECTORY_SEPARATOR.$filename_tmp, 0777);
			// var_dump(stat($result_path.DIRECTORY_SEPARATOR.$filename_tmp));
			$objWriter->save($filename);	
			$html ='<p>Peer review report for <b>'.$reference.'</b> document:<a href="../'.$filename.
			'" ><img alt="Export openxml" title="Export openxml" border="0" src="../assets/images/32x32/Excel2007.png" class="img_button" style="margin:8px;width:48px;height:48px" /></a></p>';
		}
		else {
			$html ="No internal remarks found for <b>{$reference}</b> document.";
			$filename_hyperlink = false;
		}
		echo $html."<br/>";
		return($filename_hyperlink);		
	}	
    public static function read_ece_prr(&$objWorksheet,
										$remarks=array(),
										$data_id="") {
	    $test_stat = new Status;
		$test_defect_class = new Defect_Class;
		Atomik::needed('PeerReviewer.class');
		$test_poster = new User;
        /*
        * Read each lines
        */ 
        $highestRow = $objWorksheet->getHighestRow(); // e.g. 10     
        $find_remark_begin = false;
        $open_remarks = 0;
        $nb_remarks = 0;
		$nb_new_remarks = 0;
		$nb_known_remarks = 0;
		$nb_response_remarks = 0;
        unset($res);
        for ($row = 25; $row <= $highestRow; ++$row) {
            /* get ref ID */
            $col = 0;
            $ref_id = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            if (preg_match("/^=A/i", $ref_id)) {
                    $ref_id = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            } 
            /* test begin of excel register */
            if ($find_remark_begin === false)
            {
                if ($ref_id == "Ref")  {
                    $find_remark_begin = true;
                }
            }
            else if ($find_remark_begin === true) {
                $col++;
				$author 		= $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue(); /* author */
				$paragraph 		= $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();/* get paragraph */
				$line 			= $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();/* get line */
                $description 	= $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();/* get description */
				if (($author == "")&&($description == "")) {
					/* empty remarks, stop loop */
                    break;
                }
				$author_response = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();/* get author response */
                $remarks[$nb_remarks]['author_response'] 	= $author_response;
                $remarks[$nb_remarks]['defect_class'] 		= $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();/* get defect class */
                $remarks[$nb_remarks]['status'] 			= $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();/* get status */
                $remarks[$nb_remarks]['justification'] 		= $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();/* get justification */
                $remarks[$nb_remarks]['qams_id'] 			= $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();/* get qams_id */
                $test_stat->get_status ($remarks[$nb_remarks]['status']);			
                $status_id = $test_stat->id;
                $remark_status = $test_stat->name;
                if (($status_id != QA_ACCEPTANCE) && ($status_id != REJECTED)) {
                    /* Not QA acceptance or not Rejected */
                    if ($status_id == POSTPONED) {
                        /* Postponed */
                        if ($remarks[$nb_remarks]['justification'] != "") {
                            /* justification exists */
                            /* Remark closed */
                        }
                        else {
                            $open_remarks++;
                        }
                    }
                    else {
                        $open_remarks++;
                    }
                }
                else {
                    /* Remark closed */
                }
				$remarks[$nb_remarks]['status_id'] = $status_id;
				/* check poster */
				$customer = array(1);
				$test_poster->find_poster ($author,$customer);
				$poster_id = $test_poster->id;
				$reader = $test_poster->name;	
				/* check defect class */
				$test_defect_class->get_defect_class ($remarks[$nb_remarks]['defect_class']);
				$remarks[$nb_remarks]['category_id'] = $test_defect_class->id;
				$defect_class_name = $test_defect_class->name;
				//var_dump((int)$remarks[$nb_remarks]['qams_id']);
				/* get QAMS ID [#1234*] indesrciption field */
				if (preg_match("[\#([0-9]{1,6})\#]", $description,$qams_id_array)) {
					$qams_id = isset($qams_id_array[1])?$qams_id_array[1]:false;
					/* remove tag */
					$description=preg_replace('/[\#[0-9]{1,6}\#]/' , '',$description);
					/* find if remark already input in db */
					if (Remark::find_remark($qams_id,$ref_id)) {
						//echo "remark".$ref_id."find in db <BR>";
						$remarks[$nb_remarks]['exists'] = true;
						$nb_known_remarks++;
					}
					if ($author_response !=""){
						$nb_response_remarks++;
					}					
				}
				else if (preg_match("([0-9]{1,6})", $remarks[$nb_remarks]['qams_id'],$qams_id_array)){
					//var_dump($qams_id_array);exit();
					$qams_id = isset($qams_id_array[0])?$qams_id_array[0]:false;
					/* find if remark already input in db */
					if (Remark::find_remark($qams_id,$ref_id)) {
						//echo "remark".$ref_id."find in db <BR>";
						$remarks[$nb_remarks]['exists'] = true;
						$nb_known_remarks++;
					}
					if ($author_response !=""){
						$nb_response_remarks++;
					}
				}
				else {
					$qams_id = false;
					if ($data_id != ""){
						/* compare remark in excel with remark in db */
						$qams_id = Remark::get_qams_id_remark($data_id,
															$poster_id,
															$paragraph,
															$line,
															$description,
															$author_response,
															&$res);
						if (!$res['response_check']){
							$nb_response_remarks++;
						}
						if ($qams_id !== false){
							$remarks[$nb_remarks]['exists'] = true;
							$nb_known_remarks++;
						}
					}									
				}
				if ($qams_id === false){
					$nb_new_remarks++;			
				}
				$remarks[$nb_remarks]['id'] = $ref_id;
                $remarks[$nb_remarks]['poster_id'] = $poster_id;
				$remarks[$nb_remarks]['author'] = $author;
                $remarks[$nb_remarks]['paragraph'] = $paragraph;
				$remarks[$nb_remarks]['paragraph_check'] = (isset($res['paragraph_check'])?$res['paragraph_check']:"");
				$remarks[$nb_remarks]['description_check'] = (isset($res['description_check'])?$res['description_check']:"");
				$remarks[$nb_remarks]['response_check'] = (isset($res['response_check'])?$res['response_check']:"");
                $remarks[$nb_remarks]['line'] = $line;				
				$remarks[$nb_remarks]['description'] = $description;
				$remarks[$nb_remarks]['qams_id'] = $qams_id;
				$nb_remarks++;			
            }
        }		
		$res['type_id']=2;
        $res['nb_remarks']=$nb_remarks;
        $res['open_remarks']=$open_remarks;
		$res['nb_known_remarks']=$nb_known_remarks;
		$res['nb_response_remarks']=$nb_response_remarks;
        return($res);
    }
    public static function read_ece_derived(&$objWorksheet) {
        /*
        * Read each lines
        */ 
        $highestRow = $objWorksheet->getHighestRow(); // e.g. 10     
        $find_remark_begin = false;
        $open_remarks = 0;
        $nb_remarks = 0;
        unset($res);
        for ($row = 14; $row <= $highestRow; ++$row) {
            /* get ref ID */
            $col = 1;
            $current_cell = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            if (preg_match("/^=A/i", $current_cell)) {
                    $current_cell = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            } 
            $ref_id = $current_cell;
            //echo "Test2:".$current_cell."<br/>";
            /* test begin of excel register */
            if ($find_remark_begin == false)
            {
                if ($ref_id == "Id")  {
                    //echo "enter loop<br/>";
                    $find_remark_begin = true;
                }
            }
            else if ($ref_id == "") {
                    /* test end of excel register */
                    //echo "exit loop<br/>";
                    //break;
            }
            else if ($find_remark_begin == true) {
                $col++;
                /* derived requirements */
                $derived_req = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get rationales */
                $rationales = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get dal */
                $dal = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get function */
                $function = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get dr status */
                $dr_status = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get technical impact */  
                $technical_impact = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();             
                /* get functional impact */
                $functional_impact = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get inspector validation */
                $inspector_validation = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get system impact */
                $system_impact = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get safety impact */
                $safety_impact = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get consistency */
                $consistency = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get safety rationales */
                $safety_rationales = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get validation status */
                $validation_status = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get action */
                $action = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();                   
                /* test derived requirements */
                if ($derived_req == "") {
                /* empty remarks, stop loop */
                    break;
                }
                else {
                    $nb_remarks++;
                }
                if (preg_match("/Yes/i", $validation_status)) {
                    /* requirement is validated */
                }
                else {
                    /* requirement not validated*/
                    $open_remarks++;
                }
            }
        }		
		$res['type_id']=3;
        $res['nb_remarks']=$nb_remarks;
        $res['open_remarks']=$open_remarks;
        return($res);
    }
    public static function read_eurocopter_or(&$objWorksheet) {
        /*
        * Read each lines
        */ 
        $highestRow = $objWorksheet->getHighestRow(); // e.g. 10     
        $find_remark_begin = false;
        $open_remarks = 0;
        $nb_remarks = 0;
        unset($res);
        for ($row = 10; $row <= $highestRow; ++$row) {
            /* get ref ID */
            $col = 0;
            /* date of comment */
            $date_of_comment = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            //echo "Test2:".$current_cell."<br/>";
            /* test begin of excel register */
            if ($find_remark_begin == false)
            {
                if (preg_match("/Date of comment/i", $date_of_comment)) {
                    $find_remark_begin = true;
                }
            }
            else if ($find_remark_begin == true) {
                $col++;
                /* get reviewer name */
                $cell[0] = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get comment ref */
                $cell[1] = $objWorksheet->getCellByColumnAndRow($col++, $row)->getCalculatedValue();
                /* get comment category */
                $cell[2] = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get page etc ... */
                $cell[3] = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get ECG comment */
                $cell[4] = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
				$comment = $cell[4];
                /* get supplier snswer */   
                $cell[5] = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();             
                /* get consecutive action */
                $cell[6] = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get comment status */
                $cell[7] = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
				$validation_status = $cell[7];
                /* test comment */
                if ($comment == "") {
                /* empty remarks, stop loop */
                    break;
                }
                else {
                    $nb_remarks++;
                }
                if (preg_match("/closed?/i", $validation_status)) {
                    /* requirement is validated */
                }
                else {
                    /* requirement not validated*/
                    $open_remarks++;
                }
            }
        }
		$res['type_id']=4;
        $res['nb_remarks']=$nb_remarks;
        $res['open_remarks']=$open_remarks;
        return($res);
    }
    public static function read_ece_validation_matrix(&$objWorksheet) {
        /*
        * Read each lines
        */ 
        $highestRow = $objWorksheet->getHighestRow(); // e.g. 10     
        $find_remark_begin = false;
        $open_remarks = 0;
        $nb_remarks = 0;
		$previous_req = "";
        unset($res);
        for ($row = 0; $row <= $highestRow; ++$row) {
            /* get ref ID */
            $col = 0;
            /* Column A: get upper id */
            $cell = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
            /* Column B: get upper requirement */
            $cell = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
            /* Column C: get id */
            $id = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
            //echo "Test2:".$id."<br/>";
            /* test begin of excel register */
            if ($find_remark_begin == false)
            {
                if (preg_match("/ID/i", $id)) {
                    $find_remark_begin = true;
                }
            }
            else if ($find_remark_begin == true) {
                // $col++;
                /* Column D: get requirement  */
                $requirement = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
				if ($previous_req == $requirement){
					/* discard line */
					continue;
				}
				$previous_req = $requirement;
                /* get justification */
                $cell = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get allocation */
                $comment = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get reading axes */  
                $cell = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();             
                /* get C1,C2,C3,C4,C5,C6,C7,C8 */
                $criteria[] = array();
                $validation_status = true;
                for ($index=0;$index<8;$index++) {
                    $criteria[$index] = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                    if (preg_match("/NOK/i", $criteria[$index])) {
                        $validation_status = false;
						// var_dump($criteria);
                    }   
                    // echo $index.$validation_status;
                }
				
				unset($criteria);
                /* get action */
                $cell = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get comments */
                $comment = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();                
                /* test comment */
                if ($requirement == "") {
                /* empty remarks, stop loop */
                    break;
                }
                else {
                    $nb_remarks++;
                }
                
                if ($validation_status) {
                    /* requirement is validated */
                }
                else {
                    /* requirement not validated*/
                    $open_remarks++;
                }
            }
        }
		$res['type_id']=1;
        $res['nb_remarks']=$nb_remarks;
        $res['open_remarks']=$open_remarks;
        return($res);
    }
    public static function read_ece_eqpt_validation_matrix(&$objWorksheet) {
        /*
        * Read each lines
        */ 
        $highestRow = $objWorksheet->getHighestRow(); // e.g. 10    
        $find_remark_begin = false;
        $open_remarks = 0;
        $nb_remarks = 0;
        unset($res);
        for ($row = 2; $row <= $highestRow; ++$row) {
            /* get ref ID */
            $col = 0;
            /* get upper id */
            $cell = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
            /* get upper requirement */
            $cell = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
            /* get id */
            $id = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
            //echo "Test2:".$id."<br/>";
            /* test begin of excel register */
            if ($find_remark_begin == false)
            {
                if (preg_match("/ID/i", $id)) {
                    $find_remark_begin = true;
                }
            }
            else if ($find_remark_begin == true) {
                $col++;
                /* get requirement  */
                $requirement = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get justification */
                $cell = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get allocation */
                $comment = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get reading axes */  
                $cell = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();             
                /* get C1,C2,C3,C4,C5,C6,C7,C8 */
                $criteria[] = array();
                $validation_status = true;
                for ($indx=0;$index<8;$index++) {
                    $criteria[$index] = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                    if (preg_match("/NOK/i", $criteria[$index])) {
                        $validation_status = $false;
                    }   
                    //echo $id.$index.$validation_status
                }
                /* get action */
                $cell = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();
                /* get comments */
                $validation_status = $objWorksheet->getCellByColumnAndRow($col++, $row)->getValue();                
                /* test comment */
                if ($id == "") {
                /* empty remarks, stop loop */
                    break;
                }
                else {
                    $nb_remarks++;
                }
                
                if ($validation_status) {
                    /* requirement is validated */
                }
                else {
                    /* requirement not validated*/
                    $open_remarks++;
                }
            }
        }
		$res['type_id']=1;
        $res['nb_remarks']=$nb_remarks;
        $res['open_remarks']=$open_remarks;
        return($res);
    }
	private static function getWorksheet($uploadName,$type,$sheet_to_load){
		if (preg_match("/xls/i", $type))
		{
			/* Detect type of peer review */ 
			if (($type == "xlsx")||($type == "xlsm")){
				/* read worksheet names */        
				require_once("../excel_176/Classes/PHPExcel/Reader/Excel2007.php");
				$objReader_xls = new PHPExcel_Reader_Excel2007();
			}
			else if ($type == "xls"){
				/* read worksheet names */
				require_once("../excel_176/Classes/PHPExcel/Reader/Excel5.php");
				$objReader_xls = new PHPExcel_Reader_Excel5();
			}
			$objReader_xls->setReadDataOnly(true);
			$objReader_xls->setLoadSheetsOnly( $sheet_to_load );
			$objPHPExcel = $objReader_xls->load($uploadName);
			// $objPHPExcel->setActiveSheetIndex($sheet_index);
			$objWorksheet = $objPHPExcel->getActiveSheet();			
		}
		return($objWorksheet);
	}	
	private  static function getWorksheetName($uploadName,$type){
		if (preg_match("/xls/i", $type))
		{
			/* Detect type of peer review */ 
			if (($type == "xlsx")||($type == "xlsm")){
				/* read worksheet names */        
				require_once("../excel_176/Classes/PHPExcel/Reader/Excel2007.php");
				$objReader = PHPExcel_IOFactory::createReader('Excel2007');
			}		
			elseif ($type == "xls"){
				/* read worksheet names */
				require_once("../excel_176/Classes/PHPExcel/Reader/Excel5.php");
				$objReader = PHPExcel_IOFactory::createReader('Excel5');		
			}
			$worksheet = $objReader->listWorksheetNames($uploadName);	
		}
		return($worksheet);
	}
	public static function scanPeerReviewFull($uploadName,$type,$sheet=array('Register')){
		if ((file_exists($uploadName))&&(preg_match("/xls/i", $type))){
			// $worksheet_names = Remark::getWorksheetName($uploadName,$type);
			$objWorksheet = Remark::getWorksheet($uploadName,$type,$sheet);
		}
		return($objWorksheet);
	}
    public static function scanPeerReview($uploadName,$type){
		$res = array();
        if ((file_exists($uploadName))&&(preg_match("/xls/i", $type))){
			/* Check type of peer review register and amount of remarks */
			$worksheet_names = Remark::getWorksheetName($uploadName,$type);
			/* Read only Register sheet */
			/* For Eurocopter OR */
			if (isset($worksheet_names[1])){
				$objWorksheet = Remark::getWorksheet($uploadName,$type,array($worksheet_names[1]));
				$current_cell = $objWorksheet->getCellByColumnAndRow(2, 1)->getValue();
				if ((preg_match("/Observation Record/i", $current_cell))||
					(preg_match("/comments?/i", $worksheet_names[1]))) {
					/* Eurocopter Observation Record */
					$proof_reading_type = OR_EUROCOPTER;
					$res = Remark::read_eurocopter_or ($objWorksheet);  
				}
			}
			/*
			else {
			    $res['nb_remarks']=0; 
				$res['open_remarks']=0;
				$res['type_id']=0;
				return;
			}*/
			/* is there a Validation Matrix sheet somewhere ? */
			$proof_reading_type = 0;
			foreach($worksheet_names as $id => $sheet):
				if (preg_match("/Validation Matrix/i", $sheet)){
					$proof_reading_type = SYSTEM_VALIDATION;
				}
				else if(preg_match("/REMARKS/", $sheet)){
					$proof_reading_type = SOFTWARE_INSPECTION;
				}				
			endforeach;
			switch ($proof_reading_type){
				case SYSTEM_VALIDATION:
					$objWorksheet = Remark::getWorksheet($uploadName,$type,array("Validation Matrix"));
					$res = Remark::read_ece_validation_matrix ($objWorksheet);
					break;
				case SOFTWARE_INSPECTION:
					$objWorksheet = Remark::getWorksheet($uploadName,$type,array("REMARKS"));
					$highestRow = $objWorksheet->getHighestRow(); // e.g. 10     
					$find_remark_begin = false;
					$open_remarks = 0;
					$nb_remarks = 0;
					$nb_new_remarks = 0;
					$nb_known_remarks = 0;
					$nb_response_remarks = 0;
					unset($res);
					$data= array();
					/* check poster */
					Atomik::needed("User.class");
					$poster = new User;
					$customer = array(1);
					for ($row = 2; $row <= $highestRow; ++$row) {				
						for ($col = 0; $col <= 9; ++$col) {
							$data[$row][] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
						}
						if ($data[$row][0] == "") {
							/* empty remarks, stop loop */
							break;
						}
						$ref_id= $data[$row][0];
						$description = $data[$row][1];
						$paragraph = $data[$row][2];
						$author = $data[$row][3];
						$poster->find_poster ($author,
												$customer);
						$poster_id = $poster->id;
						$reader = $poster->name;						
						$status = $data[$row][7];
						$status_id = ""; /* TBD*/
						$verif_version = $data[$row][9];
						
						if (($status != "CORRECTED") && 
							($status != "REJECTED")) {
							/* Not CORRECTED or REJECTED */
							$open_remarks++;
						}
						else {
							if ($verif_version == ""){
								$open_remarks++;
							}
							/* Remark closed */
						}
						$remarks[$nb_remarks]['id'] = $ref_id;
						$remarks[$nb_remarks]['poster_id'] = $poster_id;
						$remarks[$nb_remarks]['author'] = $author;
						$remarks[$nb_remarks]['category_id'] = "";
						$remarks[$nb_remarks]['status_id'] = $status_id;
						$remarks[$nb_remarks]['paragraph'] = $paragraph;
						$remarks[$nb_remarks]['paragraph_check'] = "";
						$remarks[$nb_remarks]['description_check'] = "";
						$remarks[$nb_remarks]['response_check'] = "";
						$remarks[$nb_remarks]['line'] = "";				
						$remarks[$nb_remarks]['description'] = $description;
						$remarks[$nb_remarks]['qams_id'] = "";						
						$nb_remarks++;
					}
					// var_dump($data);
					$res['nb_remarks']=$nb_remarks; 
					$res['open_remarks']=$open_remarks;
					$res['type_id']=2;
					var_dump($remarks);
					// exit();
					break;
				default:				
			}
			if(preg_match("/Validation - Req/i", $worksheet_names[0])){
				/* Equipment validation matrix */
				$objWorksheet = Remark::getWorksheet($uploadName,$type,array("Validation - Req"));
				$res = Remark::read_ece_eqpt_validation_matrix ($objWorksheet);             
			}
			else if (preg_match("/Register/i", $worksheet_names[1])) {
				/* Read only Register sheet */  
				$objWorksheet = Remark::getWorksheet($uploadName,$type,array($worksheet_names[1]));
				$current_cell = $objWorksheet->getCellByColumnAndRow(1, 8)->getValue(); /* B8 */
				if (preg_match("/Document title/i", $current_cell))
				{
					/* ECE Peer review register */  
					$res = Remark::read_ece_prr ($objWorksheet);                
				}
				$current_cell = $objWorksheet->getCellByColumnAndRow(0, 13)->getValue(); /* A13 */
				if (preg_match("/Derived Requirements details/i", $current_cell))
				{
					/* Derived requirement analysis */
					$res = Remark::read_ece_derived ($objWorksheet);                 
				}               
			}
			else if (preg_match("/REMARKS/i", $worksheet_names[2])) {
				/* New software peer review*/
			}
			else{
				/* Unknown format */
				$res['nb_remarks']=0; 
				$res['open_remarks']=0;
				$res['type_id']=0;
			}
        }
        else{
            $res['nb_remarks']=0; 
            $res['open_remarks']=0;
            $res['type_id']=0;
        }
        return($res);
    }
	public function count_all_remarks(){
		if ($this->amount_remarks > 0) {
			/* get amount of remarks rejected */
			$this->remark_tab['rejected'] = $this->count_sort_remarks(REJECTED);
			/* get amount of remarks to be reviewed */
			$this->remark_tab['to be reviewed'] = $this->count_sort_remarks(2) +
													$this->count_sort_remarks(8);		
			/* get amount of remarks accepted */
			$this->remark_tab['accepted'] = $this->count_sort_remarks(ACCEPTED);		
			/* get amount of remarks corrected */
			$this->remark_tab['corrected'] = $this->count_sort_remarks(CORRECTED);			
			/* get amount of remarks validated */
			$this->remark_tab['validated'] = $this->count_sort_remarks(5) + 
												$this->count_sort_remarks(9);			
			/* get amount of remarks postponed */
			$this->remark_tab['postponed'] = $this->count_sort_remarks(POSTPONED);			
			/* get amount of entered remarks  */
			$this->remark_tab['entered'] = $this->amount_remarks - 
												$this->remark_tab['rejected'] - 
												$this->remark_tab['to be reviewed'] - 
												$this->remark_tab['accepted'] -
												$this->remark_tab['corrected'] -
												$this->remark_tab['validated'] -
												$this->remark_tab['postponed'];
										
			// $data[0] = $this->remark_tab['rejected'];
			// $data[1] = $this->remark_tab['to be reviewed'];
			// $data[2] = $this->remark_tab['accepted'];
			// $data[3] = $this->remark_tab['corrected'];
			// $data[4] = $this->remark_tab['validated'];
			// $data[5] = $this->remark_tab['postponed'];
			// $data[6] = $this->remark_tab['entered'];

			// $this->name_serial = urlencode(serialize($this->remark_tab));
			$this->stats = $this->remark_tab;
			// $this->nb_serial = urlencode(serialize($this->amount_remarks));
	    }
		else {
			$this->remark_tab = array();
			// $this->name_serial = "";
			// $this->nb_serial = "";		
		}
		//var_dump($this->remark_tab);
	}
	/**
	 * function to count remarks
	 * @param status
	 * @return remarks counted
	 */	
	public function count_sort_remarks ($status_id = "") {
		Atomik::needed('Tool.class');
		$which_status 	= Tool::setFilter("bug_messages.status",$status_id);
		$sql_query = "SELECT DISTINCT (bug_messages.id) ".
							"FROM bug_messages ".
							"LEFT OUTER JOIN bug_status ON bug_status.id = bug_messages.status ".
							"LEFT OUTER JOIN bug_category ON bug_category.id = bug_messages.category ".
							"LEFT OUTER JOIN bug_criticality ON bug_criticality.level = bug_messages.criticality ".
							"LEFT OUTER JOIN bug_users ON bug_users.id = bug_messages.posted_by ".
							"LEFT OUTER JOIN baseline_join_data ON baseline_join_data.data_id = bug_messages.application ".
							"LEFT OUTER JOIN bug_applications ON bug_applications.id = bug_messages.application ".
							"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
							"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
							"LEFT OUTER JOIN aircrafts ON aircrafts.id = projects.aircraft_id ".
							"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".
							"WHERE bug_messages.reply_id = bug_messages.id ".
							$which_status.
							$this->which_data.
							$this->which_aircraft.
							$this->which_project.
							$this->which_sub_project.
							$this->which_baseline;
							//$this->which_reference.
							//$this->which_group;				
		if ($this->db != null){	
			$sql_query = "SELECT bug_messages.id ".
					"FROM bug_messages ".
					"WHERE bug_messages.reply_id = bug_messages.id ".
					$which_status.
					$this->which_data;	

			$statement = $this->db->pdo_query($sql_query,true);
			$nb_tab = $statement->fetchAll();
		}
		else{
			$list = A('db:'.$sql_query);
			$nb_tab = $list->fetchAll(PDO::FETCH_ASSOC);
		}
		// echo $sql_query;exit();
		return (count($nb_tab));
	}
	public function drawBar($bar_filename = 'remarks_bar.png',$title="  Remarks statistics"){
		require_once("pChart/pData.class");  
		require_once("pChart/pChart.class");
		/* require_once("pChart2.1.3/class/pData.class.php"); 
		require_once("pChart2.1.3/class/pDraw.class.php");  
		require_once("pChart2.1.3/class/pImage.class.php"); */
		$dir_font = "app/includes/pChart/Fonts/";
		$dir_palette = "app/includes/pChart/";	
		$DataSet = new pData;
		$DataSet->AddPoint($this->stats,"Serie2");

		$labels = array('Rejected',
						'To be reviewed',
						'Accepted',
						'Corrected',
						'Closed',
						'Postponed',
						'Entered'
		);	
		var_dump($labels);
		exit();
		$DataSet->AddPoint($labels,"Labels");
		$DataSet->AddAllSeries();
		$DataSet->RemoveSerie("Labels");
		$DataSet->SetAbsciseLabelSerie("Labels");
		$DataSet->SetSerieName("Remarks","Serie2");

		$DataSet->SetXAxisName("Types");
		$DataSet->SetSerieSymbol("Serie1",Atomik::asset('assets/images/Point_Asterisk.gif'));
		// Initialise the graph
		$graph_width = 600;
		$graph_height = 400;
		$x1 = 40;
		$y1 = 40;
		$x2 = $graph_width - 30;
		$y2 = $graph_height - 40;
		$chart = new pChart($graph_width,$graph_height);
		$chart->loadColorPalette($dir_palette."hardtones.txt");
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

		$chart->drawTextBox(0,0,600,30,$title,0,255,255,255,ALIGN_BOTTOM_CENTER,TRUE,0,0,0,30);   
		$chart->setFontProperties($dir_font."pf_arma_five.ttf",6);
		
		// Write the legend
		$chart->drawLegend(-2,3,$DataSet->GetDataDescription(),0,0,0,0,0,0,255,255,255,FALSE);
		/* $chart->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); */
		
		// Draw the bar graph
		$chart->drawStackedBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),70);
		/* Draw the bottom black area */ 
		/* $chart->setShadow(FALSE); */
		/* $chart->drawFilledRectangle(0,174,700,230,array("R"=>0,"G"=>0,"B"=>0)); */
		/* Do the mirror effect */ 
		/* $chart->drawAreaMirror(0,174,700,48); */
		/* Draw the horizon line */ 
		/* $chart->drawLine(1,174,698,174,array("R"=>80,"G"=>80,"B"=>80)); */

		// Finish the graph
		$chart->addBorder(1);
		// Add an image  
		$chart->drawFromPNG(Atomik::asset('assets/images/logo.png'),484,35);
		$chart->Render($bar_filename);			
		$gdImage_poster = @imagecreatefrompng($bar_filename);  			
	}		
}
class StatRemarks {
	/**
	 * get amount of remarks
	 * @var amount_remarks
	 */
	public  $amount_remarks;
	public  $remark_tab;
	public  $name_serial;
	public  $stats;	
	public  $nb_serial;	
	public  $description;
	private $data_id;
	private $aircraft_id;	
	private $project_id;
	private $sub_project_id;	
	private $lru_id;
	private $poster_id;
	private $category_id ;
	private $criticality_id;
	private $status_id;
	private $type_id;
	private $version;
	private $data_type;
	private $final_status_id;
	private $baseline_id;
	private $reference;
	private $which_data;
	private $which_baseline;
	private $which_reference;	
	private $which_project;
	private $which_aircraft;	
	private $which_sub_project;	
	private $db;

	/**
	 * Get color of the status
	 *
	 * @return color
	 */
	private static function getPrColor ($status_id="") {
		$color ="";
		switch ($status_id)
		{
			case 46 :
				/* PR raised */
				$color ="red";
				break;
			case 51 : /* PR fixed */
				$color ="green";
				break;				
			case 52 : /* PR closed */
				$color ="green";
				break;
			case 47 :
				/* PR in progress */
				$color ="yellow";
				break;
			case 49 :
				$color = "gris_fonce";
				break;				
			default:
				$color ="";
				break;
		}
		return ($color);
	}		
	private function check_final_version () {
		if ($this->data_type != "CSCI") {
		
			if (preg_match("#^[0-9]+$#",$this->version)) { /* ECE 1D1 .. 1D2 .. 1 */
				$check = true;
			}	
			else if (isfloat($this->version)){	/* IN 1.0 .. 1.1 .. */
				$check = true;
			}
			else {
				$check = false;
			}
		}
		else {
				$check = false;
		}		
		return ($check);
	}
	/**
	 * Get color of the status
	 *
	 * @return color
	 */
	public static function getStatusColor ($type,
											$status_id,
											$final_status_id="") {
		$color ="";
		switch ($type)
		{
			case "MOM" :
			case "NOTE" :
			case "HBK" :
			case "ORDER" :
			case "CAST" :
			case "CRI" :
			case "ABD" :
			case "DO" :
			case "CM" :
				/*  No validation needed */
				$color ="white";
				break;
			case "EPR":
			case "HPR":	
			case "SPR":	
				$color = StatRemarks::getPrColor($status_id);
				break;
			default:
				switch ($status_id) { /* $final_status_id ? */
					case 45 :
						/* document signed*/
						$color ="green"; 
						break;
					case 11 :
						/* No remarks */
						$color ="red";
						break;
					case 10 :
						/* Remark treated */
						$color ="blue";
						break;
					case 12 :
						/* Remark not validated */
						$color ="yellow";
						break;
					default:
						$color ="";
						break;
				}			
				break;
		}
		return ($color);
	}		
	
	public function get($data_id){
		$this->setDocument($data_id,
						   false);
		/* get amount of remarks  */
		$this->amount_remarks=$this->count_sort_remarks();
		$this->count_all_remarks();	
		/* data inspection summary for final document version */
		if ($this->check_final_version()) {
			if ($this->amount_remarks == 0) {
				/* no remarks, data not inspected */
				$this->final_status_id = NEW_DOC;
			}
			else if (($this->remark_tab['to be reviewed'] > 0) || 
					($this->remark_tab['accepted'] > 0) || 
					($this->remark_tab['corrected'] > 0) ||
					($this->remark_tab['entered'] > 0))
			{
				/* remarks exists but not closed, data inspection in progress */
				$this->final_status_id = UNDER_REVIEW;
			}
			else {
				/* remarks exists and are closed, data inspection done */
				$this->final_status_id = REVIEWED;
			}		
		}		
	}
	/**
	 * constructor
	 * TBD: Passage par référence de l'objet $document	 
	 */	
	public function __construct($context=null){
		if ($context != null){
			$this->aircraft_id = isset($context['aircraft_id'])? $context['aircraft_id'] : "";
			$this->project_id = isset($context['project_id'])? $context['project_id'] : Atomik::get('session/current_project_id');;
			$this->sub_project_id = isset($context['sub_project_id'])? $context['sub_project_id'] : (Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"");
			$this->baseline_id = isset($context['baseline_id'])? $context['baseline_id'] : (Atomik::has('session/baseline_id')?Atomik::get('session/baseline_id'):"");	
			$this->reference = isset($context['reference'])? $context['reference'] : "";	
			$this->group_id= isset($context['group_id'])? $context['group_id'] : "";
			Atomik::needed("Tool.class");
			$this->which_project 	= Tool::setFilter("projects.id",$this->project_id);
			$this->which_aircraft 	= Tool::setFilter("aircrafts.id",$this->aircraft_id);
			$this->which_sub_project = Tool::setFilter("lrus.id",$this->sub_project_id);
			$this->which_baseline	 = Tool::setFilter("baseline_join_data.baseline_id",$this->baseline_id);
			$this->which_reference	 = " AND (bug_applications.application LIKE '%$this->reference%')";
			$this->which_group 		 = Tool::setFilter("data_cycle_type.group_id",$this->group_id);		
			$this->amount_remarks	 = $this->count_sort_remarks();
			$this->count_all_remarks();
		}
		else{
			$this->project_id 			= "";
			$this->sub_project_id 		= "";
			$this->baseline_id 			= "";
			$this->which_aircraft		= "";		
			$this->which_project 		= "";
			$this->which_sub_project 	= ""; 
			$this->which_data 			= "";
			$this->which_baseline 		= "";
			$this->which_group 			= "";
			$this->amount_remarks = 0;
		}
	}
	public function setBaseline($baseline_id){
	
	}
	public function setDocument($data_id="",$one=true){
		Atomik::needed('Data.class');
		$document = new Data;
		$document->get($data_id);
    	$this->data_id = $data_id;
		$this->version = $document->version;
		$this->reference = $document->reference;	
		$this->project_id = "";
		$this->lru_id = "";
		$this->poster_id = "";
		$this->category_id = "";
		$this->criticality_id ="";
		$this->data_type = $document->type;
		$this->baseline_id = "";
		if ($this->data_id != "") {	    
			// check final version   	
			if (($this->check_final_version())&&(!$one)){
				$this->which_data = " AND bug_applications.application = '{$this->reference}' AND bug_applications.version REGEXP '^".$this->version."$' ";
			}
			else {
				$this->which_data = " AND bug_messages.application = {$this->data_id} ";
			}
		}
		else {
			$this->which_data = "";		
		}
		$this->amount_remarks = $this->count_sort_remarks();
		// $this->count_all_remarks();
	}
	public function count_all_remarks(){
		if ($this->amount_remarks > 0) {
			/* get amount of remarks rejected */
			$this->remark_tab['rejected'] = $this->count_sort_remarks(REJECTED);
			/* get amount of remarks to be reviewed */
			$this->remark_tab['to be reviewed'] = $this->count_sort_remarks(2) +
													$this->count_sort_remarks(8);		
			/* get amount of remarks accepted */
			$this->remark_tab['accepted'] = $this->count_sort_remarks(ACCEPTED);		
			/* get amount of remarks corrected */
			$this->remark_tab['corrected'] = $this->count_sort_remarks(CORRECTED);			
			/* get amount of remarks validated */
			$this->remark_tab['validated'] = $this->count_sort_remarks(5) + 
												$this->count_sort_remarks(9);			
			/* get amount of remarks postponed */
			$this->remark_tab['postponed'] = $this->count_sort_remarks(POSTPONED);			
			/* get amount of entered remarks  */
			$this->remark_tab['entered'] = $this->amount_remarks - 
												$this->remark_tab['rejected'] - 
												$this->remark_tab['to be reviewed'] - 
												$this->remark_tab['accepted'] -
												$this->remark_tab['corrected'] -
												$this->remark_tab['validated'] -
												$this->remark_tab['postponed'];
										
			$this->stats = $this->remark_tab;
	    }
		else {
			$this->remark_tab = array();	
		}
	}
	/**
	 * function to count remarks
	 * @param status
	 * @return remarks counted
	 */	
	public function count_sort_remarks ($status_id = "") {
		$which_status 	= Tool::setFilter("bug_messages.status",$status_id);
		$sql_query = "SELECT DISTINCT (bug_messages.id) ".
							"FROM bug_messages ".
							"LEFT OUTER JOIN bug_status ON bug_status.id = bug_messages.status ".
							"LEFT OUTER JOIN bug_category ON bug_category.id = bug_messages.category ".
							"LEFT OUTER JOIN bug_criticality ON bug_criticality.level = bug_messages.criticality ".
							"LEFT OUTER JOIN bug_users ON bug_users.id = bug_messages.posted_by ".
							"LEFT OUTER JOIN baseline_join_data ON baseline_join_data.data_id = bug_messages.application ".
							"LEFT OUTER JOIN bug_applications ON bug_applications.id = bug_messages.application ".
							"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
							"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
							"LEFT OUTER JOIN aircrafts ON aircrafts.id = projects.aircraft_id ".
							"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".
							"WHERE bug_messages.reply_id = bug_messages.id ".
							$which_status.
							$this->which_data.
							$this->which_aircraft.
							$this->which_project.
							$this->which_sub_project.
							$this->which_baseline.
							$this->which_reference.
							$this->which_group;				
		if ($this->db != null){	
			$sql_query = "SELECT bug_messages.id ".
					"FROM bug_messages ".
					"WHERE bug_messages.reply_id = bug_messages.id ".
					$which_status.
					$this->which_data;	

			$statement = $this->db->pdo_query($sql_query,true);
			$nb_tab = $statement->fetchAll();
		}
		else{
			$list = A('db:'.$sql_query);
			$nb_tab = $list->fetchAll(PDO::FETCH_ASSOC);
		}
		// echo $sql_query;exit();
		return (count($nb_tab));
	}
	public function list_draft_data () {
		$list = array();
		// $remark = new Remarks;
		if ($this->data_id != 0) {	    
			// /* check final version */    	
			if ($this->check_final_version()){
				/* */
				$which_data = "bug_applications.application = '{$this->reference}' AND bug_applications.version REGEXP '^".$this->version."' ";
			}
			else {
				$which_data = "bug_applications.id = {$this->data_id} ";
			}
			$sql_query = "SELECT bug_applications.id, ".
						"bug_applications.application, ".
						"bug_applications.version,".
						"data_cycle_type.name as type_name ".
						"FROM bug_applications ".
						"LEFT OUTER JOIN baseline_join_data ON baseline_join_data.data_id = bug_applications.id ".
						"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
						"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
						"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".
						" WHERE ".
						$which_data;
			// echo $sql_query;	
			$result = A('db:'.$sql_query);			
			// $result = Atomik_Db::findAll("bug_applications",$which_data);
			$nb_responses=count($result);	
		}
		else{
			$sql_query = "SELECT bug_applications.id, ".
						"bug_applications.application, ".
						"bug_applications.version,".
						"data_cycle_type.name as type_name ".
						"FROM bug_applications ".
						"LEFT OUTER JOIN baseline_join_data ON baseline_join_data.data_id = bug_applications.id ".
						"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
						"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
						"LEFT OUTER JOIN aircrafts ON aircrafts.id = projects.aircraft_id ".
						"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".
						" WHERE bug_applications.id IS NOT NULL ".
						$this->which_data.
						$this->which_project.
						$this->which_aircraft.
						$this->which_sub_project.
						$this->which_baseline.
						$this->which_reference.
						$this->which_group.
						" ORDER BY type ASC,application ASC, version ASC ";
			$result = A('db:'.$sql_query);	
			$nb_responses=count($result);		
		}				
		// $query = "SELECT id,application as reference,version FROM bug_applications {$which_data}";
		// echo $this->which_data."<br/>";

		if ($nb_responses != 0) {
			foreach($result as $data) {
				//$list['id'] = "<a href='".Atomik::url('inspection',array("show_application"=>$data['id']))."' title='See remarks.' >".$data['application']." issue ".$data['version']."</a><br/>";
				$this->setDocument($data['id']);
				$nb_remarks = $this->amount_remarks;
				if ($nb_remarks > 0){
					if($data['version'] != ""){
						$version = " issue ".$data['version'];
					}
					else{
						$version = "";
					}
					$list[$data['id']] = array('name'=>"<a href='".Atomik::url('inspection',array("show_application"=>$data['id'],
																									"show_poster"=>"",
																									"search"=>"",
																									"show_status"=>""))."' title='See remarks.' >".$data['application']." ".$data['type_name'].$version."</a><br/>",'nb_remarks'=>$nb_remarks);
				}
			}
		}
		// var_dump($list);
		return($list);
	}
	public function drawBar($bar_filename = 'remarks_bar.png',$title="  Remarks statistics"){
		require_once("pChart/pData.class");  
		require_once("pChart/pChart.class");
		/* require_once("pChart2.1.3/class/pData.class.php"); 
		require_once("pChart2.1.3/class/pDraw.class.php");  
		require_once("pChart2.1.3/class/pImage.class.php"); */
		$dir_font = "app/includes/pChart/Fonts/";
		$dir_palette = "app/includes/pChart/";	
		$DataSet = new pData;
		$DataSet->AddPoint($this->stats,"Serie2");

		$labels = array('Rejected',
						'To be reviewed',
						'Accepted',
						'Corrected',
						'Closed',
						'Postponed',
						'Entered'
		);		
		$DataSet->AddPoint($labels,"Labels");
		$DataSet->AddAllSeries();
		$DataSet->RemoveSerie("Labels");
		$DataSet->SetAbsciseLabelSerie("Labels");
		$DataSet->SetSerieName("Remarks","Serie2");

		$DataSet->SetXAxisName("Types");
		$DataSet->SetSerieSymbol("Serie1",Atomik::asset('assets/images/Point_Asterisk.gif'));
		// Initialise the graph
		$graph_width = 600;
		$graph_height = 400;
		$x1 = 40;
		$y1 = 40;
		$x2 = $graph_width - 30;
		$y2 = $graph_height - 40;
		$chart = new pChart($graph_width,$graph_height);
		$chart->loadColorPalette($dir_palette."hardtones.txt");
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

		$chart->drawTextBox(0,0,600,30,$title,0,255,255,255,ALIGN_BOTTOM_CENTER,TRUE,0,0,0,30);   
		$chart->setFontProperties($dir_font."pf_arma_five.ttf",6);
		
		// Write the legend
		$chart->drawLegend(-2,3,$DataSet->GetDataDescription(),0,0,0,0,0,0,255,255,255,FALSE);
		/* $chart->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); */
		
		// Draw the bar graph
		$chart->drawStackedBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),70);
		/* Draw the bottom black area */ 
		/* $chart->setShadow(FALSE); */
		/* $chart->drawFilledRectangle(0,174,700,230,array("R"=>0,"G"=>0,"B"=>0)); */
		/* Do the mirror effect */ 
		/* $chart->drawAreaMirror(0,174,700,48); */
		/* Draw the horizon line */ 
		/* $chart->drawLine(1,174,698,174,array("R"=>80,"G"=>80,"B"=>80)); */

		// Finish the graph
		$chart->addBorder(1);
		// Add an image  
		$chart->drawFromPNG(Atomik::asset('assets/images/logo.png'),484,35);
		$chart->Render($bar_filename);			
		$gdImage_poster = @imagecreatefrompng($bar_filename);  			
	}	
}

class Criticality{
	var $id;

	var $name;

	function get_eurocopter_criticality ($criticality){
		/* This gets the criticality */
		//echo "Avant:".$defect_class."<BR>";
		preg_match("#\"?(\w+)#", $defect_class,$result);

		//$defect_class=str_replace ("\"" , "",$defect_class);
		$defect_class = $result[1];
		//echo "Apres:".$defect_class."<BR>";
		$sql_get_id = "SELECT DISTINCT level,name FROM bug_criticality WHERE name Regexp '^{$criticality}'";
		//echo $sql_get_id."<BR>";
		$result_response = do_query($sql_get_id);
		/* amount of rows */
		$nb_row_response=mysql_num_rows($result_response);
		if ($nb_row_response != 0) {
			$row = mysql_fetch_object($result_response);
			$criticality_id=$row->level;
			$criticality_name = $row->name;
		}
		else {
			$criticality_id = 0;
			$criticality_name = "<b>unknown</b>";
		}
		$this->id = $criticality_id;
		$this->name=$criticality_name;	
	}
}
class Status{
	var $id;
	var $name;

	function get_eurocopter_status ($status){
		/* This gets the status */
		//echo ":".$status."<br/>";
		if (preg_match("#^\s+$#", $status)) {
			$status_id = 15;
			$status_name = "<b>unknown</b>";
		}
		else {
			/* take only word, no space nor quotes */
			preg_match("#\"?(\w+)#", $status,$result);
			//$defect_class=str_replace ("\"" , "",$defect_class);
			$status_lite = $result[1];
			$sql_get_id = "SELECT id,name FROM bug_status WHERE `name` REGEXP '^{$status_lite}'";
			//echo $sql_get_id."<BR>";
			$result_response = do_query($sql_get_id);
			/* amount of rows */
			$nb_row_response=mysql_num_rows($result_response);
			if ($nb_row_response != 0) {
				$row = mysql_fetch_object($result_response);
				$status_id=$row->id;
				$status_name = $row->name;
			}
			else {
				$status_id = 15;
				$status_name = "<b>unknown</b>";
			}
		}
		$this->id = $status_id;
		$this->name=$status_name;	
	}
	function get_status ($remark_status){
		if (preg_match("/[H|S]QA acceptance/i", $remark_status)) {
			$remark_status = "QA acceptance";
		}
		else{
			// $nb = preg_match("/(^(\w+)\W?(\w+)?\W?(\w+)?)\W?$/i", $remark_status,$remark_status_tab);
			// var_dump($remark_status_tab);
			// $remark_status_wo_space = $remark_status_tab[2];
			// if ($remark_status_tab[3] != "")
				// $remark_status_wo_space .= " ".$remark_status_tab[3];
			// if ($remark_status_tab[4] != "")
				// $remark_status_wo_space .= " ".$remark_status_tab[4];
		}	
		//$remark_status_wo_space = $remark_status_tab[1];
		// $sql_get_id = "SELECT id FROM bug_status WHERE name='$remark_status_wo_space'";
		// $db = new Db;
		// $result_response = $db->db_query($sql_get_id)->fetch();
		$row = Atomik_Db::find('bug_status',array('name'=>$remark_status));
		//echo $ref_id." ".$sql_get_id."<br/>";
		/* amount of rows */
		// $nb_row_response=count($result_response);
		if ($row) {
			// $row = mysql_fetch_object($result_response);
			$status_id=$row['id'];
		}
		else {
			//print "For remark ".$ref_id." could not establish status id for ".$remark_status.", select <b>Entered</b> by default:".mysql_error()."<BR>";
			$status_id=15; // entered
			$remark_status = "unknown (".$remark_status.")";
		}
		$this->id = $status_id;
		$this->name=$remark_status;		
	}			
}
class Defect_Class{
	var $id;
	var $name;

	public function get_defect_class ($defect_class) {
		/* This gets the defect class id from class type */
		//echo "Avant:".$defect_class."<BR>";
		preg_match("#\"?(\w+)#", $defect_class,$result);
		//$defect_class=str_replace ("\"" , "",$defect_class);
		$defect_class = isset($result[1])?$result[1]:"unknown";
		//echo "Apres:".$defect_class."<BR>";
		$sql_get_id = "SELECT DISTINCT (id) FROM bug_category WHERE name LIKE '%{$defect_class}%'";
		//echo $sql_get_id."<BR>";
		// $result_response = do_query($sql_get_id);
		$result = A("db:".$sql_get_id)->fetch();
		/* amount of rows */
		$nb_response=count($result);
		if ($nb_response != 0) {
			$this->id=$result['id'];
			$this->name=$defect_class;
		}
		else {
			print "For remark ".$ref_id." could not establish category id for ".$defect_class.", select <b>0</b> by default: ".mysql_error()."<BR>";
			$$this->id = 0;
			$defect_class = "unknown";
		}
		return($nb_response);
	}
}

function update_date_remark ($update_id,$date_dojo) {
	$date_sql = convert_dojo_date($date_dojo);
	$sql_query = "UPDATE `bug_messages` SET `date`			='$date_sql'
										WHERE `id` 		='$update_id' LIMIT 1";
	//echo $sql_query."<br/>";
	$response = A("db:".$sql_query);
}
function update_status_remark ($update_id,$status) {
	$sql_query = "UPDATE `bug_messages` SET `status`			='$status'
										WHERE `id` 		='$update_id' LIMIT 1";
	//echo $sql_query."<br/>";
	$response = A("db:".$sql_query);
}
function update_action_link_remark ($update_id,$action_id) {
	$sql_query = "UPDATE `bug_messages` SET `action_id`			='$action_id'
										WHERE `id` 		='$update_id' LIMIT 1";
	//echo $sql_query."<br/>";
	$response = A("db:".$sql_query);
}
