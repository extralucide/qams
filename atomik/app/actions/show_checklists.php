<?php
Atomik::needed('Company.class');
Atomik::needed('Tool.class');
Atomik::needed('Review.class');
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;
$fill = 0;
$count = 0;
// echo Atomik::get('checklist_id'); 
$company_id= Atomik::get('session/company_id');
$checklist_id= Atomik::get('session/checklist_id');

$which_company = Tool::setFilter("company_id",$company_id);

$checklist_query = "SELECT review_type.id,".
						"type,".
						"enterprises.name as company, ".
						"review_type.description,objectives,last_item ".
						"FROM review_type LEFT OUTER JOIN enterprises ON enterprises.id = review_type.company_id ".
						"WHERE review_type.id IS NOT NULL ".
						$which_company.
						"ORDER BY `company`,`type` ASC";
$checklist = A("db:".$checklist_query);
$questions = Question::getQuestionsList($checklist_id,$company_id)->fetchAll();
$nb_entries = count($questions);
Atomik::set('nb_entries',$nb_entries);
//echo $checklist_query."<br/>";
//echo $questions_query."<br/>";
$header_fields = array("Id", "Checklist","Question");
$html=  '<form method="POST" action="'.Atomik::url('show_checklists', false).'">';
$html.= '<fieldset class="medium">';
$html.= Company::getSelectCompany($company_id,"active");
$html.= '</fieldset >';
$html.= '</form>';
$html .= '<form action="" method="post" >';
$html .= '<fieldset class="medium">';
$html .= '<label for="show_checklist">Review Type:</label>';
$html .= '<select class="combobox" onchange="this.form.submit()" name="checklist_id" id="checklist_id">';
$html .= '<option value="">--All--';
foreach ($checklist as $row):
    $html .= "<option value='".$row['id']."'";
    if ($row['id'] == $checklist_id) {
        $html .= " SELECTED";
    }  
    $html .= ">".$row['company']." ".$row['type']." ".$row['description'];
endforeach;	
$html .= '</select>';
$html .= '</fieldset>';
$html .= '</form>';
/*
$html .= '<div class="my_menu" style="margin-left:-45px">';
$html .= '<ul><li class="edit_action"><h2><a href="'.Atomik::url('edit_checklist', array('checklist_id' => $checklist_id)).'" >Edit checklist</h2></a></li></ul>';
$html .= '</div>';  
*/
Atomik::set('title',"Checklists");
Atomik::set('css_title',"checklist");
Atomik::set('css_reset',"no_show");
Atomik::set('url',"show_checklists");
Atomik::set('url_add',Atomik::url('edit_checklist', array('checklist_id' => $checklist_id)));
Atomik::set('title_add',"Add a question to the checklist");
Atomik::set('page',$page);
Atomik::set('limite',$limite);
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);
