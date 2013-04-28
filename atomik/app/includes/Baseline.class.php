<?php
class Baseline {
	public $array = array();	public $data = array();	
	private $db;
	public $project_id;
	public $sub_project_id;
	public $review_id;
	public $baseline;
	private $project;
	private $sub_project;	
	private $baseline_id;	
	private $today_date;
	private $server_dir;
  public static function delete_baseline_link($id) {
	  if ($id != ""){
		$result = Atomik_Db::delete('baseline_join_data',array('id'=>$id));
	  }
	  else{
		$result = false;
	  }
	  return ($result);
  }	
	public static function delete_baseline_review_link($id) {
		$result = Atomik_Db::delete('baseline_join_review',array('id'=>$id));
		return($result);
	}	  
   public function delete_baseline_src_link($id,$table) {
      $param = restore_context_param();
      $sql_query = "DELETE FROM ".$table." WHERE id = {$id}";
      $result = $this->db->exec($sql_query);
      if(!$result) {	  
          print "Could not delete baseline source link: ".mysql_error();
      }
      else {
          print "Baseline source link {$id} with source deleted!";
        	?>
        	<script language='javascript' type='text/javascript'>
          document.location='<?php echo $_SERVER['HTTP_REFERER']."?".$param ?>';
          </script>
          <?php
      }
  }	   
	public function compute_deadline ($baseline_date) {
	  require_once("inc/Date.class.php");
		/* check if the review is in the past or in the future */
		if (Date::convert_date($baseline_date) < $this->today_date){
		    return(true);
		}
		else{
		    return(false);
		}
	}
	private function get_baseline_query ($data_id) {
		$sql = "SELECT description,".
				"baseline_join_data.id ".
				"FROM baselines ".
				"LEFT OUTER JOIN baseline_join_data ON baseline_join_data.baseline_id = baselines.id ".
				"WHERE baseline_join_data.data_id  = {$data_id}";		    	    
		return($sql);		   
	}
	public function getData(){
		$data = A("db:SELECT DISTINCT(bug_applications.id), projects.project,".				" lrus.lru, ".				" data_cycle_type.name,".				" data_cycle_type.description as type_description, ".				"bug_applications.application, ".
              "bug_applications.description, ".
              "bug_applications.version, ".
			  "baseline_join_data.id as link_id ".			  
              "FROM bug_applications ".
              "LEFT OUTER JOIN baseline_join_data ON bug_applications.id = data_id ".
              "LEFT OUTER JOIN projects ON bug_applications.project = projects.id ".
              "LEFT OUTER JOIN lrus ON bug_applications.lru     = lrus.id ".
              "LEFT OUTER JOIN data_cycle_type ON bug_applications.type    = data_cycle_type.id ".
              " WHERE baseline_join_data.baseline_id = {$this->baseline_id} ORDER BY description ASC ");			return ($data);	  
	}
	public function exportPeerReview(){
		Atomik::needed("Remark.class");
		$list_all_prr = null;
		if ($this->db->getOS() == "unix"){
			$copy_cmd="cp";
		}
		else{
			$copy_cmd="copy";
		}
		$remark = new Remark;
		$data = new Data;
		$root_doc = $this->db->backup_dir.DIRECTORY_SEPARATOR."docs".DIRECTORY_SEPARATOR;
		$baseline_dir = $this->getBaseline();
		foreach($this->data as $doc):
			unset($list_prr);
			$remark->setDocument($doc->id);
			/* internal peer reviews */
			$internal_file = $remark->exportXlsx($this->getBaseline().DIRECTORY_SEPARATOR."peer_review");
			if ($internal_file !== false){
				$new_src = $this->server_dir.DIRECTORY_SEPARATOR."peer_review".DIRECTORY_SEPARATOR.$internal_file;
				$new_dest = $root_doc.$baseline_dir.DIRECTORY_SEPARATOR."peer_review".DIRECTORY_SEPARATOR.$internal_file;
				$copy = "{$copy_cmd} {$new_src} {$new_dest}";
				exec($copy,$retval,$code);
				$list_prr[] = $internal_file; // $list_all_prr[$row->id]
			}
			$data->get($doc->id);
			/* external peer reviews */
			$list_external_prr = $data->getExternalPeerReviewList();
			if($list_external_prr != null){
				foreach($list_external_prr as $prr):
					$src = "../docs/peer_reviews/".$prr->id.".".$prr->ext;
					Atomik::needed("Tool.class");
					$filename = Tool::cleanFilename($prr->name);
					if (file_exists($src)){
						$dest = $this->server_dir.DIRECTORY_SEPARATOR."peer_review".DIRECTORY_SEPARATOR.$filename;
						$res_copy_server = copy($src, $dest);
						if ($res_copy_server === true){
							$new_src = $dest;
							$new_dest = $root_doc.$baseline_dir.DIRECTORY_SEPARATOR."peer_review".DIRECTORY_SEPARATOR.$filename;
							$copy = "{$copy_cmd} {$new_src} {$new_dest}";
							exec($copy,$retval,$code);
							$list_prr[] = $filename;
						}
						echo "Found peer review report {$prr->name}<br/>";
					}
					else{
						echo "Failed to copy {$prr->id}.{$prr->ext} document ({$prr->name}}). It does not exist on the QAMS server.<br/>";
					}					
				endforeach;	
				$list_all_prr[$doc->id] = $list_prr;
			}
		endforeach;
		return($list_all_prr);
	}
	public function createExportDir(){
		/* create directory */
		$baseline_dir = $this->getBaseline();
		Atomik::needed('User.class');
		$user = new User;
		$root_doc = $user->getFolder().DIRECTORY_SEPARATOR.
					"docs".DIRECTORY_SEPARATOR;
		// Create directory docs on target disk.
		Atomik::needed('Db.class');
		$db = new Db;
		if ($db->getOS() == "windows"){
			$create_dir = "MD {$root_doc}{$baseline_dir}";
			exec($create_dir,$retval,$code);
			$create_dir = "MD {$root_doc}{$baseline_dir}".DIRECTORY_SEPARATOR."peer_review";
			exec($create_dir,$retval,$code);
		}
		$result_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR.
						"..".DIRECTORY_SEPARATOR."result";	
		$server_dir = $result_path.DIRECTORY_SEPARATOR.$baseline_dir;	
		if (!file_exists($server_dir)){		
			/* create directory on server */
			mkdir($server_dir, 0700);
			mkdir($server_dir.DIRECTORY_SEPARATOR."peer_review", 0700);
		}
		else {
			/* delete directory on server */
			$objects = scandir($server_dir);
			foreach ($objects as $object):
				try {
					unlink($server_dir."/".$object);
				} catch (Exception $e) {
					echo "Fail to clean result directory on server.<br/>";
					break;
				}			
			endforeach;
		}	
		$this->server_dir = $server_dir;
		return($server_dir);
	}	public function exportData($backup_filename="no_name",$baseline_id=""){
		$server_dir = $this->server_dir;		if ($this->db->getOS() == "unix"){			$copy_cmd="cp";		}		else{			$copy_cmd="copy";		}		Atomik::needed("Data.class");
		$context_array['baseline_id'] = $baseline_id;		$data=new Data(&$context_array);
		$list_data = $data->getData();		$base_path = dirname(__FILE__).DIRECTORY_SEPARATOR.							"..".DIRECTORY_SEPARATOR.							"..".DIRECTORY_SEPARATOR.							"..".DIRECTORY_SEPARATOR;
		$txt = "";	
		$root_doc = $this->db->backup_dir.DIRECTORY_SEPARATOR.
					"docs".DIRECTORY_SEPARATOR;
		$baseline_dir = $this->getBaseline();
			foreach($list_data as $row):			$data->get($row['id']);			if ($data->link!="empty"){ 				$filename = $data->smart_filename;				$src=$base_path."docs".DIRECTORY_SEPARATOR.$data->filename;
				$dest=$root_doc.$baseline_dir.DIRECTORY_SEPARATOR.$filename;				if (file_exists($src)){										// $txt .= "Copy {$data->filename} to {$dest}.<br/>";	
					$res_copy_server = true;
					$res_copy_server = copy($src, $server_dir.DIRECTORY_SEPARATOR.$filename);
					if ($res_copy_server === true){
						/* copy DOS */
						$copy = "{$copy_cmd} {$src} {$dest}";
						exec($copy,$retval,$code);
					}
					$list[$data->id] = $data->smart_filename;				}
				else{
					$txt .= "Failed to copy {$data->filename} document ({$data->smart_filename}). It does not exist on the QAMS server.<br/>";
				}			}
			else {
				$txt .= "Failed to copy {$data->filename} document ({$data->smart_filename}). Attachment is missing.<br/>";	
			}		endforeach;
		if (isset($list)){
			$txt .= "<b>Zip file contains the following files:</b><br/>";
			$txt .= "<ul>";
			foreach($list as $name):
				$txt .= "<li>".$name."</li>";
			endforeach;
			$txt .= "</ul>";
			$result_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
				"..".DIRECTORY_SEPARATOR.
				"..".DIRECTORY_SEPARATOR.
				"..".DIRECTORY_SEPARATOR."result";
			$zip = "{$this->db->sept_zip_path}7z a -r -tzip {$backup_filename} {$server_dir}* ";
			exec($zip,$retval,$code);
			// foreach($retval as $row){
				// echo $row."<br/>";
			// }
			$copy = "copy {$backup_filename} {$result_path}";
			exec($copy,$retval,$code);
			// foreach($retval as $row){
				// echo $row."<br/>";
			// }			
		}
		else {
			$txt .= "<b>Zip file contains no files.</b><br/>";
		}
		return($txt);			} 	public function exportPeerReviews(){	}
	public function getBaselineList(){
		Atomik::needed("Tool.class");
		$which_project 		= Tool::setFilter("projects.id",$this->project_id);
		$which_sub_project 	= Tool::setFilter("lrus.id",$this->sub_project_id);
		$which_review 		= Tool::setFilter("baseline_join_review.review",$this->review_id);
		$sql_query = 'SELECT baselines.id ,'.
						'baselines.date,'.
						'baseline_join_data.id as link_data_id,'.
						'baselines.description as description,'.
						'projects.project,'.
						'lrus.lru '.
						'FROM baselines '.
						'LEFT OUTER JOIN baseline_join_data ON baselines.id = baseline_join_data.baseline_id '.
						'LEFT OUTER JOIN baseline_join_review ON baselines.id = baseline_join_review.baseline_id '.
						'LEFT OUTER JOIN baseline_join_project ON baselines.id = baseline_join_project.baseline_id '.
						'LEFT OUTER JOIN projects ON projects.id = baseline_join_project.project_id '.
						'LEFT OUTER JOIN lrus ON lrus.id = baseline_join_project.lru_id '.
						'WHERE baselines.id IS NOT NULL '. 
						$which_project.
						$which_sub_project.
						$which_review.
						' GROUP BY baselines.id '.
					   ' ORDER BY date DESC, project ASC,lru ASC';    		
		// $sql_query = "SELECT DISTINCT(baselines.id as id),".
			// "description,lru ".
            // "FROM baselines ".
			// "LEFT OUTER JOIN baseline_join_project ON baselines.id = baseline_join_project.baseline_id ".
			// "LEFT OUTER JOIN lrus ON lrus.id = baseline_join_project.lru_id ".			
			// "{$filter} ORDER BY date DESC, description ASC ";
		$result = $this->db->db_query($sql_query);
		$list   = $result->fetchAll(PDO::FETCH_ASSOC);
		return($list);	
	}
	public function getBaseline(){
		Atomik::needed("Tool.class");
		if ($this->sub_project != ""){
			$project = $this->project."_";
		}
		else{
			$project = $this->project;		
		}	
		if ($this->baseline != ""){
			$sub_project=$this->sub_project."_";
		}
		else {
			$sub_project=$this->sub_project;
		}	
		// $this->baseline_id = "";	
		// $this->baseline = "";
		$baseline_dir = Tool::cleanFilename($project.$sub_project.$this->baseline);
		return($baseline_dir);
	}
	public function getId(){
		return($this->baseline_id);
	}
	public function get($baseline_id){
		if ($baseline_id != ""){
			$this->baseline_id = $baseline_id;
			$sql_query = "SELECT baselines.description,".
						" projects.project,".
						" projects.id as project_id,".
						" lrus.lru, ".
						" lrus.id as lru_id ".
						" FROM baselines ".
						" LEFT OUTER JOIN baseline_join_project ON baseline_join_project.baseline_id = baselines.id".
						" LEFT OUTER JOIN projects ON baseline_join_project.project_id = projects.id".
						" LEFT OUTER JOIN lrus ON baseline_join_project.lru_id = lrus.id".
						" WHERE baselines.id = {$baseline_id}";
			$result = $this->db->db_query($sql_query);
			$get_query = $result->fetch(PDO::FETCH_OBJ);
			$this->baseline = $get_query->description;
			$this->project = $get_query->project;
			$this->sub_project = $get_query->lru;
			$this->project_id = $get_query->project_id;
			$this->sub_project_id = $get_query->lru_id;					$list = $this->getData();			$this->data = $list->fetchALl(PDO::FETCH_OBJ);			return($this->data);
		}
	}
	function  __construct ($context=null) {		Atomik::needed("Db.class");
		if ($context != null){
			$this->project_id = isset($context['project_id'])? $context['project_id'] : Atomik::get('session/current_project_id');
			$this->sub_project_id = isset($context['sub_project_id'])? $context['sub_project_id'] : Atomik::get('session/sub_project_id');
		}
		else {
			
		}
		$this->db = new Db;
    }
	public static function update_baseline_application ($update_id,$baseline) {
		/* Add baseline */
		$result=false;
		$sql_query = "SELECT * FROM `baseline_join_data` WHERE `data_id` = '$update_id' AND `baseline_id` = '$baseline'";
		$db = new Db;			
		$result = $db->db_query($sql_query)->fetch();	
		if ($result===false) {
			/* link do not exists, add an entry */
			$sql_query = "INSERT INTO baseline_join_data (data_id,baseline_id) VALUES ('$update_id', '$baseline')";
			/* link already exists, update it */
			// echo $sql_query."<BR>";
			$result = $db->db_query($sql_query);	
		}
		if ($result) {
			Atomik::needed('Data.class');
			$data = new Data;
			$data->get($update_id);
			$baseline_info = new Baseline;
			$baseline_info->get($baseline);
			$display["error"] = "Set document <b>".$data->small_ident."</b> with baseline <b>".$baseline_info->baseline."</b>";
			$display["status"] = "success";
		}
		else {
			$display["error"] = "Baseline set failed !";
			$display["status"] = "failed";
		}		
		return($display);
	}
	public static function update_baseline_review($review_id,
												  $baseline_id){
		$result = Atomik_Db::insert('baseline_join_review',array('review_id'=>$review_id,
									'baseline_id'=>$baseline_id));
		return($result);							
	}
}
