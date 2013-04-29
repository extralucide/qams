<?php
Atomik::needed('Db.class');
Atomik::needed('Date.class');	
Atomik::needed('Data.class');	
Atomik::needed('Review.class');
Atomik::needed('Project.class');	
Atomik::needed('Baseline.class');
Atomik::set('tab_select','review');
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;

$context_array['aircraft_id']= Atomik::has('session/current_aircraft_id')?Atomik::get('session/current_aircraft_id'):"";
$context_array['project_id']= isset($show_project) ? isset($show_project) : (Atomik::has('session/current_project_id')?Atomik::get('session/current_project_id'):Atomik::get('session/project_id'));
$context_array['sub_project_id']=Atomik::has('session/sub_project_id')?Atomik::get('session/sub_project_id'):"";
$context_array['review_id']=isset($show_review) ? isset($show_review) : "";
$context_array['baseline_id']=isset($show_baseline) ? isset($show_baseline) : "";
$context_array['type_id']=Atomik::has('session/review_type_id')?Atomik::get('session/review_type_id'):"";

$context = urlencode(serialize($context_array));
$fill = false;
if (isset($_POST['latest'])) {
	$latest = $_POST['latest'];
}
else {
	$latest = "off";
}

if ($latest == "on") {
	$where = " AND date >= '".Date::getTodayDate()."'";
	$checked = "checked";
}
else {
	$where = "";
	$checked = "";
}
// var_dump($context_array);
$review = new Review(&$context_array);
$review_list = $review->getReviewList();
$baseline_list = $review->getBaseline();
$project = new Project(&$context_array);
$count_review = count($review_list);
Atomik::set('nb_entries',$count_review);
$sql_query = A('db:show columns from reviews ');
$column=$sql_query->fetchall();
$header_fields = array("id", "managed by", "project","component" ,"type","description", "status","date","MoM",""); 
$checklists = A("db:SELECT id,description FROM checklists ORDER BY `description` ASC");
Atomik::set('url',"show_reviews");
Atomik::set('url_add',Atomik::url('post_review'));
Atomik::set('title_add',"Add a meeting");
Atomik::set('page',$page);
Atomik::set('limite',$limite);
Atomik::set('title',"Meetings");
Atomik::set('css_title',"data");
/* menu project */
$html=  '<form method="POST" action="'.Atomik::url('show_reviews', false).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectProject($context_array['project_id'],"active");
$html.= '</fieldset >';
// $html.= '<input type="hidden" name="context" value="'.$context.'">';
$html.= '</form>';

/* menu sub project */
Atomik::set('menu',array('equipment' => 'Equipment'));
$html.= '<form method="POST" action="'.Atomik::url('show_reviews', false).'">';
$html.= '<fieldset class="medium">';
$html.= Project::getSelectSubProject(&$project,$context_array['sub_project_id'],"active");
$html.= '</fieldset >';
// $html.= '<input type="hidden" name="context" value="'.$context.'">';
$html.= '</form>';

/* menu review */
$html.= '<form method="POST" action="'.Atomik::url('show_reviews', false).'">';
$html.= '<fieldset class="medium">';
$html.= "<label for='review_type'>Review type</label>";
$html.= "<select class='combobox' onchange='this.form.submit()' name='show_type' id='show_type'>";
$html.= "<option value='' /> --All--";
foreach($review->getAllReviewType() as $row):
	$html.= "<option value='".$row['id']."'";
	if ($row['id'] == $context_array['type_id']){ 
		$html.= " SELECTED ";
	}
	$html.= ">".$row['type']." ".$row['name']." ".$row['description'];
endforeach;
$html.= "</select>";	  
$html .='</fieldset >';
// $html .='<input type="hidden" name="context" value="'.$context.'">';
$html .='</form>';
Atomik::set('select_menu',$html);
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
/*
$menu = <<<EOD
  <form id="project" method="POST" action="<?= Atomik::url('show_reviews', false) ?>">
    <fieldset class="medium">
      <label for='show_project'>
Project
      </label>
      <select class='combobox' onchange="this.form.submit()" name='show_project' id='show_project'>
	<option value=''/> --All--
	<?php foreach($project->getProject() as $row):?>
		<option value="<?= $row['id'] ?>"
		<?php if ($row['id'] == $context_array['project_id']){ print " SELECTED ";}?>
		><?= $row['project']?>
	<?php endforeach ?>	  
	  <input type="hidden" name="context" value="<?= $context?>">
      </fieldset>
	  </form>
	  <form method="POST" action="<?= Atomik::url('show_reviews', false) ?>">
		<fieldset class="medium">
		<label for='show_lru'>
Component
		</label>
		<select class='combobox' onchange="this.form.submit()" name='show_lru' id="show_lru">
        <option value=''> --All--
		<?php foreach($project->getSubProject() as $row):?>
			<option value="<?= $row['id'] ?>"
			<?php if ($row['id'] == $context_array['sub_project_id']){ print " SELECTED ";}?>
			><?= $row['lru']?>
		<?php endforeach ?>		
      </select>
	  <input type="hidden" name="context" value="<?= $context?>">
      </fieldset>
  </form>
    <form method="post" action="<?= Atomik::url('show_reviews', false) ?>">
    <fieldset class="medium">
    <label for='show_review'>
Review
	</label>
    <select class='combobox' onchange="this.form.submit()" name='show_review' id='show_review'>
    <option value=''>--All--
		<?php foreach($review_list as $row):?>
			<option value="<?= $row['id'] ?>"
			<?php if ($row['id'] == $context_array['review_id']){ print " SELECTED ";}?>
			><?= $row['managed_by']." ".$row['lru']." ".$row['type']." ".Date::convert_date($row['date']) ?>
		<?php endforeach ?>		
    </select>
	  <input type="hidden" name="context" value="<?= $context?>">
    </fieldset>
    </form>
    <br/>  
  <form method="post" action="<?= Atomik::url('show_reviews', false) ?>">
    <fieldset class="medium">
      <label for='review_type'>
Review type
      </label>
		<select class='combobox' onchange="this.form.submit()" name='show_type' id='show_type'>
		<option value='' /> --All--
		<?php foreach($review->getAllReviewType() as $row):?>
			<option value="<?= $row['id'] ?>"
			<?php if ($row['id'] == $context_array['type_id']){ print " SELECTED ";}?>
			><?= $row['type']." ".$row['name']." ".$row['description'] ?>
		<?php endforeach ?>
		</select>	  
	  <input type="hidden" name="context" value="<?= $context?>">
      </fieldset>
  </form>
  <form id="baseline" method="post" action="<?= Atomik::url('show_reviews', false) ?>">
    <fieldset class="medium">
      <label for='show_baseline'>
Baseline
      </label>
      <select class='combobox' onchange="this.form.submit()" name='show_baseline' id='show_baseline' >
        <option value=''> --All--
		<? foreach ($baseline_list as $row):?>
		    <option value="<?= $row->id ?>"
		    <?php if ($row->id == $context_array['baseline_id'])print " SELECTED";?>
		    ><?= $row->lru." ".$row->baseline_name ?>
       <? endforeach; ?>
      </select>
	  <input type="hidden" name="context" value="<?= $context?>">
      </fieldset>
  </form>
  <form method="POST"  action="<?= Atomik::url('show_reviews', false) ?>">
    <fieldset class="medium">
      <label for='preview'>
Latest
      </label>
      <input name="latest" id="latest" onchange="this.form.submit()" type="checkbox" <?= $checked ?> /><br />
	  <input type="hidden" name="context" value="<?= $context?>">
      </fieldset>
  </form>
  <form id='gen_mom' name="gen_mom" method="POST" action="../create_mom_select.php" >
    <fieldset>
	  <input type="hidden" name="context" value="<?= $context?>">
      </fieldset>
  </form>
  <form id="create_review" name="create_review" method="POST" action="../post_review.php" >
    <fieldset>
	  <input type="hidden" name="context" value="<?= $context?>">
      <input type="hidden" name="new_review" value="yes">
    </fieldset>
  </form>
  <div class="my_menu" style="margin-left:-45px;width:180px;">
    <ul>
	  <li class="review">
        <h2>
          <a href="#" onclick="create_review.submit();" border="0" >Add review</a></h2></li>
      <li class="delete">
        <h2>
          <a href="#" onclick="return review_delete_get_checkbox_value();" border="0" >Delete review</a></h2></li>	
      <li class="baseline">
        <h2>
          <a href="#" onclick="return get_checkbox_value()" border="0" >Apply baseline</a></h2></li>	
		  <br/><br/> 
    </ul>
  </div>
	
  <div class="spacer">
  </div>
  <form method="post" name="multi_modify_data" id="multi_modify_data" action="" onSubmit="return get_checkbox_value()">
    <fieldset class="medium">
	  <label for='show_baseline'>Set Baseline</label>
      <select class='combobox' onchange="this.form.submit()" name='show_baseline' id='show_baseline' >
        <option value=''> --All--
		<? foreach ($baseline_list as $row):?>
		    <option value="<?= $row->id ?>"
		    <?php if ($row->id == $context_array['baseline_id'])print " SELECTED";?>
		    ><?= $row->lru." ".$row->baseline_name ?>
       <? endforeach; ?>
      </select>
	  <input type="hidden" name="context" value="<?= $context?>">	
      </fieldset>
  </form>
EOD;
*/
