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
 * @copyright   2009-2010 (c) Olivier Appere
 * @license     http://www.opensource.org/licenses/mit-license.php
 * @link        
 */

/**
 * Handle peer reviewers
 *
 * @package Qams
 */
class PeerReviewer {
	private $db;
	private $data_id;
	private $project_id;
	private $lru_id;
	private $poster_id;
	private $category_id;
	private $baseline_id;
	private $criticality_id;
	private $status_id;
	private $type_id;
	private $reference;
    private $version;
	private $which_status;
	private $which_data;
	private $which_baseline;
	private $which_reference;
	private $which_project;
	private $which_equip;
	private $which_type;
	private $which_poster;
	private $peers;
	private $remarks;
	public $name_serial;
	public $nb_serial;
	public $nb;
	public $peer_reviewer_tab;
	public $peer_reviewer_nb_tab;
	public $index_peer_reviewer;
	
	function get_sort_peer_reviewers () {
				
			$sql = "SELECT DISTINCT (bug_users.id),bug_users.fname,bug_users.lname,bug_users.function ".
					"FROM bug_messages ".
					"LEFT OUTER JOIN bug_status ON bug_status.id = bug_messages.status ".
					"LEFT OUTER JOIN bug_category ON bug_category.id = bug_messages.category ".
					"LEFT OUTER JOIN bug_criticality ON bug_criticality.level = bug_messages.criticality ".
					"LEFT OUTER JOIN bug_users ON bug_users.id = bug_messages.posted_by ".
					"LEFT OUTER JOIN bug_applications ON bug_applications.id = bug_messages.application ".
					"LEFT OUTER JOIN baseline_join_data ON baseline_join_data.data_id = bug_messages.application ".
					"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
					"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
					"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".					
					"WHERE bug_messages.reply_id = bug_messages.id ".	
					$this->which_data.
					$this->which_status.
					$this->which_poster.
					$this->which_type.
					$this->which_group.
					$this->which_equip.
					$this->which_project.
					$this->which_baseline.
					$this->which_reference;
            // echo $sql."<br><br>";
            $result = A('db:'.$sql);
			return($result);		
	}
	function get_peer_reviewers ($data_id=0) {
		if ($data_id != 0)
				$which_data = "AND bug_messages.application = {$data_id} ";
			else
				$which_data = " ";
		$sql = "SELECT DISTINCT bug_users.fname,bug_users.lname,bug_users.id,function ".
						"FROM bug_messages,bug_users ".
						"WHERE bug_users.id = bug_messages.posted_by ".
						$which_data.
						"AND bug_messages.id = bug_messages.reply_id ORDER BY lname ASC";
		return($sql);
	}
	function get_nb_remarks ($user_id) {
		
		    if ($this->poster_id != NULL) {
		        $which_poster = " AND bug_messages.posted_by = {$this->poster_id} ";
		    }
		    else if ($user_id != NULL) {
		        $which_poster =" AND bug_messages.posted_by = {$user_id}";	
			}
			else {
				$which_poster =" AND bug_messages.posted_by = '' ";
			}
		$sql_query = "SELECT DISTINCT (bug_messages.id) FROM bug_messages ".
					"LEFT OUTER JOIN bug_status ON bug_status.id = bug_messages.status ".
					"LEFT OUTER JOIN bug_category ON bug_category.id = bug_messages.category ".
					"LEFT OUTER JOIN bug_criticality ON bug_criticality.level = bug_messages.criticality ".
					"LEFT OUTER JOIN bug_users ON bug_users.id = bug_messages.posted_by ".
					"LEFT OUTER JOIN bug_applications ON bug_applications.id = bug_messages.application ".
					"LEFT OUTER JOIN baseline_join_data ON baseline_join_data.data_id = bug_messages.application ".
					"LEFT OUTER JOIN data_cycle_type ON data_cycle_type.id = bug_applications.type ".
					"LEFT OUTER JOIN projects ON projects.id = bug_applications.project ".
					"LEFT OUTER JOIN lrus ON lrus.id = bug_applications.lru ".	
					"WHERE bug_messages.reply_id = bug_messages.id ".
					$this->which_data.
					$which_poster.
					$this->which_status.
					$this->which_type.
					$this->which_group.
					$this->which_equip.
					$this->which_project.
					$this->which_baseline.
					$this->which_reference;
					//echo $sql_query."<br>";

		$result = A('db:'.$sql_query);
		$nb_remarks_tab = $result->fetchAll(PDO::FETCH_ASSOC);
		$nb_remarks = count($nb_remarks_tab);
		return($nb_remarks);
	}
	public function drawPie($pie_filename = 'peer_reviewers_pie.png',$title="Status of actions"){	
		require_once("pChart/pData.class");  
		require_once("pChart/pChart.class");  
		$dir_font = "app/includes/pChart/Fonts/";
		$dir_palette = "app/includes/pChart/";
		// Dataset definition 
		$DataSet = new pData;	
		$DataSet->AddPoint($this->remarks,"Serie1");
		$DataSet->AddPoint($this->peers,"Serie2");
		$DataSet->AddAllSeries();
		$DataSet->SetAbsciseLabelSerie("Serie2");

		// Initialise the graph
		$width = 520;
		$length = 350;
		$chart = new pChart($width,$length);
		$chart->loadColorPalette($dir_palette."softtones.txt");
		$chart->drawFilledRoundedRectangle(7,7, /* X1,Y1 */
											$width - 7,$length - 7, /* X2,Y2 */
											5, /* Radius */
											240,240,240 /* RGB */
											);
		$chart->drawRoundedRectangle(5,5, /* X1,Y1 */
									$width - 5,$length - 5, /* X2,Y2 */
									5,
									230,230,230 /* RGB */
									);
		$chart->createColorGradientPalette(195,204,56,223,110,41,5);

		// Draw the pie chart
		$chart->setFontProperties($dir_font."tahoma.ttf",8);
		$chart->AntialiasQuality = 0;
		$chart->drawPieGraph($DataSet->GetData(), /* amount of remarks */
							$DataSet->GetDataDescription(), /* Peer reviewers */
							200, /* X pos */
							230, /* Y pos */
							130, /* Radius */
							PIE_PERCENTAGE_LABEL, /* Label types */
							TRUE, /* EnhanceColors */
							50, /* Skew */
							20, /* SpliceHeight */
							5   /* SpliceDistance */
							);
		$chart->drawPieLegend(400,/* X pos */
							15,/* Y pos */
							$DataSet->GetData(),
							$DataSet->GetDataDescription(),
							250,
							250,
							250);

		// Write the title
		$chart->setFontProperties($dir_font."MankSans.ttf",10);
		$chart->drawTitle(10,20,$title,100,100,100);

		$chart->Render($pie_filename);		  
		unset($chart);	
	}
	public function get($data_id="",$one=true){
		Atomik::needed('Data.class');
		Atomik::needed('User.class');
		//$this->db = new Db;
		$this->data_id = $data_id;
		$document = new Data;
		$document->get($data_id);
		$this->reference = $document->reference;
		$this->version = $document->version;
			
		if (($this->data_id != 0) && ($this->reference != "")) {	    
				/* check final version */ 
				if ((preg_match("#^[0-9]+$#",$this->version))&&(!$one)){
					/* get all drafts version */
					$this->which_data = " AND bug_applications.application = '{$this->reference}' AND bug_applications.version REGEXP '^".$this->version."' ";
				}
				else {
					/* get only one document */
					$this->which_data = " AND bug_messages.application = {$this->data_id} ";
				}
		}	
		else
			$this->which_data = "";	

        $list_peers = $this->get_sort_peer_reviewers ();
		$nb[] = array();
		$data[] = array();
		$this->peer_reviewer_tab = array();
		$this->index_peer_reviewer = 0;
		foreach($list_peers as $row_response):
			$peer_reviewer = User::getLiteName($row_response['fname'],$row_response['lname']);	
			$this->peer_reviewer_tab[$peer_reviewer]=$row_response['function'];			
			$data[$this->index_peer_reviewer] = $peer_reviewer;
			$nb[$this->index_peer_reviewer] = $this->get_nb_remarks($row_response['id']);
			$this->peer_reviewer_nb_tab[$peer_reviewer]=$nb[$this->index_peer_reviewer];	
			$peer_reviewer.= ":".$row_response['function'];
			$this->index_peer_reviewer++;
		endforeach;
		$this->peers = $data;
		$this->remarks = $nb;
		$this->nb = $this->index_peer_reviewer;		
	}
	public function __construct ($context=null){
		if($context!= null){
			$this->project_id = isset($context['project_id'])? $context['project_id'] : "";
			$this->lru_id = isset($context['sub_project_id'])? $context['sub_project_id'] : "";
			$this->poster_id = isset($context['user_id'])? $context['user_id'] : "";
			$this->category_id = isset($context['category_id'])? $context['category_id'] : "";
			$this->criticality_id = isset($context['criticality_id'])? $context['criticality_id'] : "";
			$this->status_id = isset($context['status_id'])? $context['status_id'] : "";
			$this->baseline_id = isset($context['baseline_id'])? $context['baseline_id'] : "";
			$this->reference = isset($context['reference'])? $context['reference'] : "";
			$this->type_id = isset($context['type_id'])? $context['type_id'] : "";	
			$this->group_id= isset($context['group_id'])? $context['group_id'] : "";
			Atomik::needed("Tool.class");
			$this->which_reference	 = " AND (bug_applications.application LIKE '%$this->reference%')";
			$this->which_status 	= Tool::setFilter("bug_messages.status",$this->status_id);
			$this->which_project 	= Tool::setFilter("projects.id",$this->project_id);
			$this->which_equip 		= Tool::setFilter("lrus.id",$this->lru_id);
			$this->which_baseline 	= Tool::setFilter("baseline_join_data.baseline_id",$this->baseline_id);
			$this->which_type 		= Tool::setFilter("bug_applications.type",$this->type_id);
			$this->which_group 		= Tool::setFilter("data_cycle_type.group_id",$this->group_id);
			$this->which_poster 	= Tool::setFilter("bug_messages.posted_by",$this->poster_id);
		}
		else{
			$this->project_id = "";
			$this->lru_id = "";
			$this->poster_id = "";
			$this->category_id = "";
			$this->criticality_id = "";
			$this->status_id = "";
			$this->baseline_id = "";
			$this->type_id = "";	
			$this->which_group = "";
			$this->which_status = "";	
			$this->which_baseline = "";
			$this->which_project = "";        
			$this->which_equip = "";
			$this->which_type = "";
			$this->which_poster = "";		
		}
		$this->which_data = "";
		$this->peers = array();
		$this->remarks = array();
		$this->nb = 0;
    }    
}
