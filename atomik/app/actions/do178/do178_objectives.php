<?php
Class Objectives{
	public $show_search_obj;
	public $dal_column;
	public $dal;
	
	public function __construct($dal='B'){
		$this->dal = $dal;
		switch ($dal) {
			case "A":
				$this->dal_column = 'dal_a';
				break;
			case "B":
				$this->dal_column = 'dal_b';
				break;
			case "C":
				$this->dal_column = 'dal_c';
				break;
			case "D":
				$this->dal_column = 'dal_d';
				break;
			default:
				$this->dal_column = 'dal_b';
		}		
	}
	public function sort_objectives() {

		$sql = "SELECT * FROM do_178b_tables WHERE (({$this->dal_column} = 'i') OR ({$this->dal_column} = 'm')) ".$this->show_search_obj."ORDER BY "." id "." ASC, id ASC";
		return($sql);
	}
	public static function create_objective_line ($table_a,
													$objective,
													$description,$dal) {

		static $fill=0;
		/* m means mandatory according to DAL level */
		/* i means independence according to DAL level */
		if (($dal == "m") || ($dal == "i")) {
			if ($dal == "i") {
				$event_color='rouge_bleu';
				$odd_color='vert_bleu';
			}
			else {
				$event_color='rouge';
				$odd_color='vert';
			}

			if ($fill) {
				echo "<tr id='hide_line' class='$event_color'>";
			}
			else {
				echo "<tr class='$odd_color'>";
			}
			echo "<td>A-".$table_a.".".$objective."</td>";
			echo '<td>'.$description."</td>";
			echo '<td>mandatory</td>';
			/* bouton fleche pour voir le commentaire de cloture de l'action */
			echo "<td><a><span class='down_arrow' style='margin-left:10px' ".
					" onClick=\"return display_folder('do178b_chapter_{$table_a}{$objective}',this)\">".
					"</a>";
			echo "<br/>";
			echo '</td>';
			print "</tr>";
			$fill = !$fill;
			/* chapitre associ   s */
			echo "<tr class='menu' id='do178b_chapter_{$table_a}{$objective}' >";
			echo '<td colspan="4">';
			//echo "<div class='entry' id='first_entry'>";
			Objectives::write_chapters ($table_a,$objective);
			echo '</td>';
			print "</tr>";
		}
	}
	private static function write_chapters ($table_a,$objective) {
		$new_get_chapter = "SELECT do_178b_join_obj_chap.paragraph,description FROM do_178b_chapters,do_178b_join_obj_chap ".
				"WHERE do_178b_join_obj_chap.table_a = ".$table_a." AND ".
				"do_178b_join_obj_chap.objective = ".$objective." AND do_178b_join_obj_chap.paragraph = do_178b_chapters.paragraph";
		$result = A('db:'.$new_get_chapter);
		print '<div class="my_text">';
		foreach($result as $text) {
			echo "<b>section ".$text['paragraph']."</b>: ".$text['description'];
			echo "<br/><br/>";
		}
		print "</div>";
	}	
}
if (!(isset($_POST['search']))) {
	$search="";
    $show_search = "";
}
else {
  /* search .... */
  $search=$_POST['search'];
  $show_search = " ((paragraph LIKE '%$search%') OR (description LIKE '%$search%') OR (do_178b_section LIKE '%$search%')) ";
}
$fill = false;
$dal = isset($_GET['dal'])?$_GET['dal']:'B';
$do178_objectives = new Objectives($dal);
$list_objectives = A('db:'.$do178_objectives->sort_objectives())->fetchAll();
$nbtotal=count($list_objectives);
$fill = 0;
$table = Atomik_Db::findAll('do_178b_chapters',$show_search,'paragraph');
/* result of the sql query to get field of the header of the table */
$sql_query = A('db:show columns from do_178b_chapters ');
/* get the array related to the sql query */
$column=$sql_query->fetchall();
$do_178b_section		= array (0 => "do_178b_section");
$paragraph		= array (0 => "paragraph");
$description	= array (0 => "description");
$column		= array (0 => $paragraph,1 => $do_178b_section, 2 => $description);
Atomik::set('title',"DO-178B Objectives");
Atomik::set('css_title',"action");
Atomik::set('css_add',"no_show");
Atomik::set('css_reset',"no_show");
Atomik::set('css_page',"no_show");
