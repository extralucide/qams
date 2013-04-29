<?php
/**
 * Qams Framework
 * Copyright (c) 2009-2010 Olivier Appere
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     Qams
 * @author      Olivier Appere
 * @copyright   2009-2013 (c) Olivier Appere
 * @license     http://www.opensource.org/licenses/mit-license.php
 * @link        
 */

/**
 * Handle project
 *
 * @package Qams
 */
class Project {
	private $db;
	private $project_id;
	private $sub_project_id;
	public $aircraft_id;	
	private $aircraft_name;
	private $project_name;
	private $sub_project_name;	
	private $company_id;
	private $company;
	private $folder;
	private $workspace;	
	public $name_serial;
	public $nb_serial;
	private $which_week;
    public $id;
    public $name;
    public $description;
	public $abstract;
	public $part_number;
	public $dal;
	public $scope;
	public $manager_id;	
    public $photo_file;
    public $thumbnail;
    public $list;
    public $parent;  
	
	public function getWorkspace(){
		return($this->workspace);
	}	
	public function getFolder(){
		return($this->folder);
	}
	public function getAircraft(){
		return($this->aircraft_name);
	}
	public function getAircraftId(){
		return($this->aircraft_id);
	}	
	public function getCompany(){
		return($this->company);
	}	
    public function get($id){
		Atomik::needed('Aircraft.class');
		if ($id !=""){
			$sql_query = "SELECT projects.aircraft_id,".
								"projects.project,".
								"projects.description,".
								"projects.folder,".
								"projects.workspace,".
								"aircrafts.name as aircraft, ".
								"enterprises.name as company ".
								"FROM projects ".
								"LEFT OUTER JOIN aircrafts ON projects.aircraft_id = aircrafts.id ".
								"LEFT OUTER JOIN enterprises ON aircrafts.company_id = enterprises.id ".
								"WHERE projects.id=".$id;
			// echo $sql_query;
			$result = A('db:'.$sql_query);
			$img_path = dirname(__FILE__).DIRECTORY_SEPARATOR.
					"..".DIRECTORY_SEPARATOR.
					"..".DIRECTORY_SEPARATOR.
					"assets".DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."systems".DIRECTORY_SEPARATOR;	
			$this->photo_file=Atomik::asset("assets/images/systems/coeur.png");	
			$this->thumbnail=Atomik::asset("assets/images/systems/coeur_tb.png");		
			if ($result != false){
				$row = $result->fetch(PDO::FETCH_ASSOC);	  
				$this->project_id = $id;		
				$this->id=$id;
				$this->folder=$row['folder'];
				$this->workspace=$row['workspace'];
				$this->project_name=$row['project'];
				$this->aircraft_id=$row['aircraft_id'];
				$this->aircraft_name=$row['aircraft'];
				$this->company=$row['company'];
				$this->description=isset($row['description'])?$row['description']:"";
			}
			else{
				$this->project_id = $id;		
				$this->id=$id;
				$this->folder="";
				$this->workspace="";
				$this->project_name="";
				$this->aircraft_id="";
				$this->aircraft_name="";
				$this->company="";
				$this->description="";	
			}
		}
		else{
			/* try aircraft at least */
			$this->project_name = Aircraft::getAircraftName($this->aircraft_id);
		}
	}
	public function getProjectId(){
		return($this->project_id);
	}	
	public function getSubProject($id){
		// $row = Atomik_Db::find('lrus','id='.$id);
		if (($id != "")&&($id != 0)){
			$sql_query = "SELECT projects.aircraft_id,".
								"projects.project as project_name,".
								"lrus.id,".
								"lrus.project,".
								"lrus.lru,".
								"lrus.manager_id,".
								"description_lru as description,".
								"abstract,".
								"part_number,".
								"dal,".
								"scope.scope,".
								"aircrafts.name as aircraft, ".
								"parent_id FROM lrus ".
								"LEFT OUTER JOIN projects ON projects.id = lrus.project ".
								"LEFT OUTER JOIN aircrafts ON projects.aircraft_id = aircrafts.id ".
								"LEFT OUTER JOIN scope ON scope.id = lrus.scope_id ".
								"LEFT OUTER JOIN bug_users ON bug_users.id = lrus.manager_id ".								
								" WHERE lrus.id=".$id;			
			$result = A('db:'.$sql_query);
			if ($result !== false){
				$row = $result->fetch(PDO::FETCH_ASSOC);
				$this->aircraft_id=$row['aircraft_id'];
				$this->project_id = $row['project'];
				$this->sub_project_id = $row['id'];
				$this->sub_project_name=$row['lru'];
				$this->project_name=$row['project_name'];
				$this->aircraft_name=$row['aircraft'];			  
				$this->description=isset($row['description'])?$row['description']:"";
				$this->abstract=isset($row['abstract'])?$row['abstract']:"";
				$this->part_number=isset($row['part_number'])?$row['part_number']:"";
				$this->dal=isset($row['dal'])?$row['dal']:"";
				$this->scope=isset($row['scope'])?$row['scope']:"";
				$this->manager_id=isset($row['manager_id'])?$row['manager_id']:"";				
				$this->parent=$row['parent_id'];
			}
			else{
				$this->aircraft_id="";
				$this->project_id = "";
				$this->sub_project_id = "";
				$this->sub_project_name="";
				$this->project_name="";
				$this->aircraft_name="";
				$this->description="";
				$this->abstract="";
				$this->part_number="";
				$this->dal="";
				$this->scope="";
				$this->manager_id="";				
				$this->parent="";		
			}
		}
	}
    public function __construct($context=null) {
		Atomik::needed("Db.class");
		$this->db = new Db;
		if($context != null){
			$this->company_id = isset($context['company_id'])?$context['company_id']:"";
			$this->aircraft_id = isset($context['aircraft_id'])?$context['aircraft_id']:"";
			$this->project_id = isset($context['project_id'])?$context['project_id']:"";
			$this->sub_project_id = isset($context['sub_project_id'])?$context['sub_project_id']:"";
			$this->review_id = isset($context['review_id'])?$context['review_id']:"";
			if ($this->project_id != ""){
				$this->get($this->project_id);
				$this->getSubProject($this->sub_project_id);
			}
		}
		else {
			$this->company_id = "";
			$this->aircraft_id = "";
			$this->project_id = "";
			$this->sub_project_id = "";
			$this->review_id = "";
		}
	}
	public function getUsers(){
		Atomik::needed('Tool.class');
		$which_aircraft = Tool::setFilter("user_join_project.project_id",$this->project_id);		
		$which_project = Tool::setFilter("projects.aircraft_id",$this->aircraft_id);
		$sql_query = "SELECT DISTINCT bug_users.id,fname,lname,function ".
					"FROM bug_users ".
					"LEFT OUTER JOIN user_join_project ON bug_users.id = user_join_project.user_id ".
					"LEFT OUTER JOIN projects ON projects.id = user_join_project.project_id ".					
					" WHERE bug_users.id IS NOT NULL ".
					$which_aircraft.
					$which_project.
					" ORDER BY `bug_users`.`lname` ASC";
		$list = A("db:".$sql_query);
		return($list);
	}
	public static function getProject($aircraft_id="",
										$company_id=""){
		Atomik::needed('Tool.class');
        $which_company = Tool::setFilter("aircrafts.id",$aircraft_id);
		$which_aircraft = Tool::setFilter("enterprises.id",$company_id);

		$sql_query = "SELECT ". 
					"projects.id, ".
					"project, ".
					"projects.description, ".
					"aircrafts.name as aircraft, ".
					"enterprises.name as company ".
					"FROM projects ".
					"LEFT OUTER JOIN aircrafts ON aircrafts.id = projects.aircraft_id ".
					"LEFT OUTER JOIN enterprises ON aircrafts.company_id = enterprises.id ".
					"WHERE projects.id IS NOT NULL ".
					$which_company.
					$which_aircraft.
					" ORDER BY company ASC,aircraft ASC,`projects`.`project` ASC";	

		$list_data = A("db:".$sql_query);
        $list = $list_data->fetchAll(PDO::FETCH_ASSOC);
		$system_w_photo = new Project;
        foreach($list as $id => $system):
            $system_w_photo->get($system['id']);
            //echo  $id.":".$aircraft['id'].":".$aircraft_w_photo->photo_file."<br/>";
            $list[$id]['photo_file'] = $system_w_photo->photo_file;
            $list[$id]['thumbnail'] = $system_w_photo->thumbnail;
            //echo $aircraft_w_photo->thumbnail."<br/>";
        endforeach;		
		return($list);
	}
	public function setProject($project_id){
		$this->project_id = $project_id;
	}	
	public function setSubProject($sub_project_id){
		$this->sub_project_id = $sub_project_id;
	}
	public function getProjectName(){
		return($this->project_name);
	}
	public function getSubProjectName(){
		return($this->sub_project_name);
	}		
	public function getBaseline(){
		require_once("Tool.class.php");
		$filter = Tool::setFilterWhere("baseline_join_project.project_id",$this->project_id);
		 $sql_query = "SELECT baselines.id as id,".
							"baselines.description,".
							"baselines.date,".
							"lru,".
							"projects.project ".
							"FROM baseline_join_project ".
							"LEFT OUTER JOIN lrus ON lrus.id = baseline_join_project.lru_id ".
							"LEFT OUTER JOIN projects ON projects.id = baseline_join_project.project_id ".
							"LEFT OUTER JOIN baselines ON baselines.id = baseline_join_project.baseline_id ".
							$filter.
							" ORDER BY date DESC, project ASC, lru ASC, description ASC ";
		$result = $this->db->db_query($sql_query);
		$list   = $result->fetchAll(PDO::FETCH_ASSOC);
		return($list);
	}
    public static function getSubProjectAcronym($id){
        $row = Atomik_Db::find('lrus','id='.$id);    
		$acronym = $row['lru'];	
		return ($acronym);
	}	
	public function getSubProjectList(){
		Atomik::needed("Tool.class");
		$which_project = Tool::setFilter("list_lrus.project",$this->project_id);
		$which_aircraft = Tool::setFilter("aircrafts.id",$this->aircraft_id);
		$which_company = Tool::setFilter("enterprises.id",$this->company_id);
		$sql_query = "SELECT list_lrus.id, ".
							"list_lrus.lru, ".
							"t2.lru as parent_lru, ".
							"list_lrus.abstract, ".
							"list_lrus.part_number, ".
							"list_lrus.dal, ".
							"list_lrus.manager_id, ".
							"scope.scope, ".
							"bug_users.lname as manager, ".
							"list_lrus.description_lru as description, ".
							"projects.project, ".
							"aircrafts.name as aircraft, ".
							"parent_id ".
							"FROM lrus list_lrus INNER JOIN (SELECT id,lru FROM lrus) t2 ON t2.id = list_lrus.parent_id ".
							"LEFT OUTER JOIN projects ON projects.id = list_lrus.project ".
							"LEFT OUTER JOIN aircrafts ON projects.aircraft_id = aircrafts.id ".
							"LEFT OUTER JOIN enterprises ON enterprises.id = aircrafts.company_id ".
							"LEFT OUTER JOIN scope ON scope.id = list_lrus.scope_id ".
							"LEFT OUTER JOIN bug_users ON bug_users.id = list_lrus.manager_id ".
							"WHERE list_lrus.id IS NOT NULL ".
							$which_project.
							$which_aircraft.
							$which_company.
							" ORDER BY `aircrafts`.`name` ASC,`projects`.`project` ASC,`list_lrus`.`parent_id` ASC,`list_lrus`.`lru` ASC";					
		// echo $sql_query."<br/>";
		$result = A('db:'.$sql_query);
		if ($result !== false){
			$list   = $result->fetchAll(PDO::FETCH_ASSOC);
		}
		else{
			$list = array();
		}
		return($list);
	}		
	public static function getSelectProject($selected,$onchange="inactive",$aircraft_id="",$company_id=""){
		$html='<label for="show_project">Project:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html .= ' name="show_project">';
		$html .= '<option value=""/> --All--';
		
		foreach(Project::getProject($aircraft_id,$company_id) as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .="/>".$row['aircraft']." ".$row['project'];
		endforeach;
		$html .='</select>';
		return($html);
	}
    public static function getSelectAircraft($company_id,$selected,$onchange="inactive"){
        $html='<label for="show_aircraft">Aircraft:</label>';
        $html.='<select class="combobox"';
        if ($onchange=="active") {
            $html .= 'onchange="this.form.submit()"';
        }
        $html .= ' name="show_aircraft">';
        $html .= '<option value=""/> --All--';
        Atomik::needed('Aircraft.class');
        foreach(Aircraft::getAircrafts($company_id) as $row):
            $html .= '<option value="'.$row['id'].'"';
            if ($row['id'] == $selected){ 
                $html .= " SELECTED ";
            }
            $html .=">".$row['aircraft'];
        endforeach;
        $html .='</select>';
        return($html);
    }
	public static function getSelectSubProject($project,$selected,$onchange="inactive"){
		$html ='<label for="show_lru">'.A('menu/equipment').':</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html.= ' name="show_lru">';
		$html.= '<option value=""/> --All--';
		foreach($project->getSubProjectList() as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			if ($row['parent_lru'] == $row['lru']){			
				$html .= ">".$row['lru'];
			}
			else{
				$html .= ">".$row['parent_lru']." ".$row['lru'];
			}
		endforeach;
		$html .='</select>';
		return($html);		
	}
	public static function getSelectBaseline($project,$selected,$onchange="inactive"){
		$html ='<label for="show_baseline">Baseline:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html.= ' name="show_baseline">';
		$html.= '<option value=""/> --All--';
		foreach($project->getBaseline() as $row):
			$html .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html .= " SELECTED ";
			}
			$html .=">".$row['project']." ".$row['lru']." ".$row['description'];
		endforeach;
		$html .='</select>';
		return($html);		
	}		
	function get_project_name ($project_id){
		if ($project_id != "") {
			$sql_query = "SELECT project FROM projects WHERE id = ".$project_id;
			$result = $this->db->db_query($sql_query);
			$project_row = $result->fetch(PDO::FETCH_OBJ);
			$name = $project_row->project;
		}
		else {
			$name = "";
		}
		return($name);
	}	
	function get_sub_project_name ($sub_project_id){
		if (($sub_project_id != "")&&($sub_project_id != 0)){
			$sql_query = "SELECT lru FROM lrus WHERE id = ".$sub_project_id;
			$result = $this->db->db_query($sql_query);
			$sub_project_row = $result->fetch(PDO::FETCH_OBJ);
			$name = $sub_project_row->lru;
		}
		else {
			$name = "";
		}
		return($name);
	}		
	private function get_list_project ($select_week=""){
		 $sql_query = "SELECT DISTINCT (projects.id), projects.project as name FROM projects ".
		 "LEFT OUTER JOIN actions ON actions.project = projects.id WHERE actions.criticality = 14 ".$this->which_week;
		 return($sql_query);
	}	
	  function get_amount_of_tasks ($project_id) {
		   $sql_query = "SELECT SUM(actions.duration) AS counter FROM projects ".
		 "LEFT OUTER JOIN actions ON actions.project = projects.id ".
		 " WHERE projects.id = {$project_id} AND actions.criticality = 14 ".$this->which_week;
		 //echo "TEST: ".$sql_query."<br>";
		 $result = do_query($sql_query);
			$nb_remarks=mysql_fetch_object($result) ;
			return($nb_remarks->counter);
	  }
	  function get_amount_of_tasks_with_status ($project_id,$status_id="8") {
		   $sql_query = "SELECT SUM(actions.duration) AS counter FROM projects ".
		 "LEFT OUTER JOIN actions ON actions.project = projects.id ".
		 " WHERE projects.id = {$project_id} AND actions.status = {$status_id} {$which_lru}"; 
		 //echo "TEST: ".$sql_query."<br>";
		 $result = do_query($sql_query);
			$nb_remarks=mysql_fetch_object($result) ;
			return($nb_remarks->counter);
	  }
	  function get_stat_tasks($select_week="") {
		$this->which_week = Task::filter_week($select_week);	
		$sql_query = $this->get_list_project ();
		//echo "TEST: ".$sql_query."<br>";
		$result_response = do_query($sql_query);        
		/* amount of rows */	
		$this->nb=mysql_num_rows($result_response); 
			if ($this->nb != 0) {
				//$data[] = array();
				//$this->peer_reviewer_tab[] = array();
				$this->index_poster = 0;
			while($row = mysql_fetch_object($result_response)) {
					$poster = $row->name; //filter($row->fname)." ".filter($row->lname);	
					//$this->poster_tab[$poster]=$row->duration;			
						$data[$this->index_poster] = $poster;
						$nb[$this->index_poster] = $this->get_amount_of_tasks($row->id);
						//$nb_closed[$this->index_poster] = $this->get_amount_of_actions($project_id,$lru_id,$row->id,9);
						$this->poster_nb_tab[$poster]=$nb[$this->index_poster];	
						//$poster.= ": ".$row->duration;
						//echo $poster."<br/>";//":".$nb[$index_poster]."<br/>";
						$this->index_poster++;
			 } //ends sub-while loop
			 //$data=array("toto","titi","tata");
			 $this->name_serial = urlencode(serialize($data));
			 $this->nb_serial = urlencode(serialize($nb));
			 //$this->nb_closed_serial = urlencode(serialize($nb_closed));
		}
	  }	
} 
