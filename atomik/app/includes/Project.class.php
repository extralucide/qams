<?php
/**
 * QAMS Framework
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
 * @package     Project.class
 * @author      Olivier Appere
 * @copyright   2009-2013 (c) Olivier Appere
 * @license     http://www.opensource.org/licenses/mit-license.php
 * @link        
 */

/**
 * Handle project
 *
 * @package Project.class
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
				$this->project_name=$row['aircraft']." ".$row['project'];
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
			if($this->scope == "Software"){
				$this->photo_file=Atomik::asset("assets/images/SW.png");	
				$this->thumbnail=Atomik::asset("assets/images/SW.png");
			}
			else if($this->scope == "PLD"){
				$this->photo_file=Atomik::asset("assets/images/fpga.jpg");	
				$this->thumbnail=Atomik::asset("assets/images/fpga.jpg");
			}			
			else{
				$this->photo_file=Atomik::asset("assets/images/systems/board.png");	
				$this->thumbnail=Atomik::asset("assets/images/systems/board_tb.png");			
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
			$this->aircraft_id = (Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"");
			$this->project_id = "";
			$this->sub_project_id = "";
			$this->review_id = "";
		}
		$this->description = "";
		$this->photo_file=Atomik::asset("assets/images/systems/coeur.png");	
		$this->thumbnail=Atomik::asset("assets/images/systems/coeur_tb.png");		
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
										$company_id="",
										$nb_projects=0){
		Atomik::needed('Tool.class');
		Atomik::needed('User.class');
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
		$nb_projects = count($list);
		/* Get pictures */
		$system_w_photo = new Project;
		if (User::getCompanyUserLogged() != "ECE"){
	        foreach($list as $id => &$system):
	            $system_w_photo->get($system['id']);
	            $system['photo_file'] = $system_w_photo->photo_file;
	            $system['thumbnail'] = $system_w_photo->thumbnail;
	            $system['project'] = str_rot13($system['project']);
	            $system['description'] = str_rot13($system['description']);
	        endforeach;
		}
		else{
	        foreach($list as $id => &$system):
	            $system_w_photo->get($system['id']);
	            $system['photo_file'] = $system_w_photo->photo_file;
	            $system['thumbnail'] = $system_w_photo->thumbnail;
	        endforeach;		
		}
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
		if (($this->project_id== NULL) || ($this->project_id == 0)){
			$which_project = "";
		}
		else {
			$which_project = "AND (list_lrus.project = {$this->project_id} OR list_lrus.project = 0)";
		}		
		$which_aircraft = Tool::setFilter("aircrafts.id",$this->aircraft_id);
		$which_company = Tool::setFilter("enterprises.id",$this->company_id);
		$sql_query = "SELECT list_lrus.id, ".
							"list_lrus.lru, ".
							"parent_item.lru as parent_lru, ".
							"list_lrus.abstract, ".
							"list_lrus.part_number, ".
							"list_lrus.dal, ".
							"list_lrus.manager_id, ".
							"scope.abrvt as scope, ".
							"bug_users.lname as manager, ".
							"list_lrus.description_lru as description, ".
							"projects.project, ".
							"aircrafts.name as aircraft, ".
							"parent_id ".
							"FROM lrus list_lrus INNER JOIN (".
							"SELECT lrus.id,lru FROM lrus ".
							"INNER JOIN projects ON projects.id = lrus.project ".
							"INNER JOIN aircrafts ON projects.aircraft_id = aircrafts.id {$which_aircraft} ".
							") parent_item ON parent_item.id = list_lrus.parent_id ".
							"LEFT OUTER JOIN projects ON projects.id = list_lrus.project ".
							"LEFT OUTER JOIN aircrafts ON projects.aircraft_id = aircrafts.id {$which_aircraft}".
							"LEFT OUTER JOIN enterprises ON enterprises.id = aircrafts.company_id {$which_company}".
							"LEFT OUTER JOIN scope ON scope.id = list_lrus.scope_id ".
							"LEFT OUTER JOIN bug_users ON bug_users.id = list_lrus.manager_id ".
							"WHERE list_lrus.id IS NOT NULL ".
							$which_project.
							" ORDER BY `aircrafts`.`name` ASC,`scope` ASC,`projects`.`project` ASC,`list_lrus`.`parent_id` ASC,`list_lrus`.`lru` ASC";
		// echo $sql_query."<br/>";
		$result = A('db:'.$sql_query);
		if ($result !== false){
			$list   = $result->fetchAll(PDO::FETCH_ASSOC);
			// var_dump($list);
			$item_w_photo = new Project;
			foreach($list as $id => &$item):
				$item_w_photo->getSubProject($item['id']);
				$item['photo_file'] = $item_w_photo->photo_file;
				$item['thumbnail'] = $item_w_photo->thumbnail;
				/* Looking for items with multiple parents */
				$sql_query = "SELECT lrus.id FROM lrus LEFT OUTER JOIN lru_join_project ON lru_join_project.item_id = lrus.id WHERE lru_join_project.item_id = {$item['id']} AND lrus.id != lrus.parent_id";
				$result = A('db:'.$sql_query);
				if ($result !== false){
					$row   = $result->fetchAll(PDO::FETCH_ASSOC);
					// var_dump($row);
					if ($row !== false){
						if (count($row)>0){
							$item['parent_lru'] = $item['lru'];
						}
					}
				}
				endforeach;			
		}
		else{
			$list = array();
		}		
		return($list);
	}
	public static function getParentsList($item_id){
		$sql_query = "SELECT lru_join_project.id as link_id,lrus.id as id,lru,description_lru as description FROM lrus LEFT OUTER JOIN lru_join_project ON lru_join_project.parent_id = lrus.id ".
					"WHERE lru_join_project.item_id = {$item_id}";
		$sql_query .= " UNION SELECT NULL,parent_item.id as id,parent_item.lru,parent_item.description FROM lrus list_lrus INNER JOIN (SELECT id,parent_id,lru,description_lru as description FROM lrus) parent_item ON parent_item.id = list_lrus.parent_id ".
					"WHERE list_lrus.id = {$item_id} AND list_lrus.id != list_lrus.parent_id";	
		// echo $sql_query;					
		$result = A('db:'.$sql_query);
		if ($result !== false){
			$list   = $result->fetchAll(PDO::FETCH_ASSOC);
			if ($list !== false){

			}
			else{
				$list = null;
			}
		}
		else{
			$list = null;
		}
		return($list);
	}
	public function getAllParentsList($item_id){
		$sql_query = "SELECT lru_join_project.id as link_id,lrus.id as id,lru,description_lru as description FROM lrus LEFT OUTER JOIN lru_join_project ON lru_join_project.parent_id = lrus.id ".
					"WHERE lru_join_project.item_id = {$item_id}";
		$sql_query .= " UNION SELECT NULL,parent_item.id as id,parent_item.lru,parent_item.description FROM lrus list_lrus INNER JOIN (SELECT id,parent_id,lru,description_lru as description FROM lrus) parent_item ON parent_item.id = list_lrus.parent_id ".
					"WHERE list_lrus.id = {$item_id} AND list_lrus.id != list_lrus.parent_id";	
		// echo $sql_query;					
		$result = A('db:'.$sql_query);
		if ($result !== false){
			$list   = $result->fetchAll(PDO::FETCH_ASSOC);
			if ($list !== false){

			}
			else{
				$list = null;
			}
		}
		else{
			$list = null;
		}
		return($list);	
	}	
	public static function getSelectProject($selected,$onchange="inactive",$aircraft_id="",$company_id=""){
		$html='<label for="show_project">System:</label>';
		$html.='<select class="combobox"';
		if ($onchange=="active") {
			$html .= 'onchange="this.form.submit()"';
		}
		$html .= ' name="show_project">';
		$html_tmp = "";
		$nb_projects = 0;
		$list_project = Project::getProject($aircraft_id,$company_id,&$nb_projects);
		foreach($list_project as $row):
			$html_tmp .= '<option value="'.$row['id'].'"';
			if ($row['id'] == $selected){ 
				$html_tmp .= " SELECTED ";
			}
			$html_tmp .="/>".$row['aircraft']." ".$row['project'];
		endforeach;
		if ($nb_projects > 1){
			$html .= '<option value=""/> --All--';
			$html .= $html_tmp;
		}
		else{
			/* do not display -- All -- because only one project exists */
			$html .= $html_tmp;
		}
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
	public static function getSelectSubProject($project,$selected="",$onchange="inactive"){
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
				$html .= ">".$row['scope']." ".$row['lru'];
			}
			else{
				$html .= ">".$row['scope']." ".$row['parent_lru']." ".$row['lru'];
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
	private static function getListDownstreamItems($parent_id,$up=false){
		if ($up === false){
			$sql_query = "SELECT lrus.id,lru,description_lru as description,part_number as pn,abrvt as scope FROM lrus LEFT OUTER JOIN scope ON scope.id = lrus.scope_id WHERE parent_id = {$parent_id} AND lrus.id != parent_id";
			$sql_query .= " UNION SELECT lrus.id,lru,description_lru as description,part_number as pn,abrvt as scope FROM lrus LEFT OUTER JOIN lru_join_project ON lru_join_project.item_id = lrus.id LEFT OUTER JOIN scope ON scope.id = lrus.scope_id WHERE lru_join_project.parent_id = {$parent_id} AND lrus.id != lrus.parent_id";
		}
		else{
			$sql_query = "SELECT lrus.id,lru,description_lru as description,part_number as pn,abrvt as scope FROM lrus LEFT OUTER JOIN scope ON scope.id = lrus.scope_id WHERE project = {$parent_id} AND lrus.id = parent_id";
		}
		$result = A("db:".$sql_query);
		// echo $sql_query."<br/>";
		if ($result != false){
			$list = $result->fetchAll(PDO::FETCH_OBJ);
		}
		else{
			$list = array();
		}
		return($list);
	}
	private function display_downstream_data($id,$fhandle,$up=false){
		$downstream_items_list = Project::getListDownstreamItems($id,$up);
		if ($downstream_items_list !== false){
			foreach ($downstream_items_list as $item) :
				fputs($fhandle,'<node name="'.$item->scope.' '.$item->lru.' ('.$item->pn.')" id="'.$item->id.'" connectionname="" connectioncolor="#526e88" namecolor="#f" bgcolor="#d9e3ed" bgcolor2="#f" namebgcolor="#d9e3ed" namebgcolor2="#526e88" bordercolor="#526e88">');
				fputs($fhandle,Tool::cleanDescription($item->description));
				$this->display_downstream_data($item->id,&$fhandle);
				fputs($fhandle,'</node>');
			endforeach;
		}		
	}
	private function echo_map(&$node, $selected) {
		$output = "";
		$x = $node['x'];
		$y = $node['y'];
		$output .= "<a href=\"\" onclick=\"window.top.window.ouvrir('".Atomik::url('edit_eqpt',array('id'=>$node['id']))."','_blank')\">";
		$output .= "<div style=\"position:absolute;left:{$x};top:{$y};width:{$node['w']};height:{$node['h']};" . ($selected == $node['id'] ? "background-color:red;filter:alpha(opacity=40);opacity:0.4;" : "") . "\">&nbsp;</div></a>\n";
		for ($i = 0; $i < count($node['childs']); $i++) {
			$output .= $this->echo_map($node['childs'][$i], $selected);
		}
		$output .= "<a href='".Atomik::url('edit_eqpt',array('id'=>$node['id']))."'>open</a>";
		return($output);
	}  	
	public function createDiagram($diagram_filename){
		require_once 'diagram/class.diagram.php';
		require_once 'diagram/class.diagram-ext.php';
		Atomik::needed('Tool.class');
		$output = "";
		$diagram_file = dirname(__FILE__).DIRECTORY_SEPARATOR.
					"..".DIRECTORY_SEPARATOR.
					"..".DIRECTORY_SEPARATOR.
					"..".DIRECTORY_SEPARATOR.
					'result'.DIRECTORY_SEPARATOR.$diagram_filename.'.xml';
		$fhandle = fopen($diagram_file,'w');
		fputs($fhandle,'<?xml version="1.0" encoding="UTF-8"?>');
		fputs($fhandle,'<diagram bgcolor="#f" bgcolor2="#d9e3ed">');

		/* current system */
		fputs($fhandle,'<node name="'.$this->project_name.'" id="'.$this->id.'" connectionname="" connectioncolor="#526e88" namecolor="#f" bgcolor="#d9e3ed" bgcolor2="#f" namebgcolor="#d9e3ed" namebgcolor2="#526e88" bordercolor="#526e88">');
		fputs($fhandle,Tool::cleanDescription($this->description));
		/* 
		 * find downstream items 
		 */
		$this->display_downstream_data($this->id,&$fhandle,true);

		fputs($fhandle,'</node>');
		/* */    
		fputs($fhandle,'</diagram>');
		fclose($fhandle);
		$diagram = new DiagramExtended($diagram_file);
		$diagram_display = new Diagram(realpath($diagram_file));
		$diagram_png="../result/".$diagram_filename.'.png';
		$diagram_display->Draw($diagram_png);
	
		$selected = (isset($_GET['id']) ? $_GET['id'] : $id);
		$diagram_node_position = $diagram->getNodePositions();
		$output .= $this->echo_map($diagram_node_position, $selected); 
		return ($output);
	}
	/*
	* deprecated functions 
	*/
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
