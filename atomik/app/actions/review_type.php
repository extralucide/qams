<?php
Atomik::needed('Review.class');
$fill = false;
$page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;  
$limite = isset($_REQUEST['limite']) ? $_REQUEST['limite'] : 8;
$company_id= Atomik::get('session/company_id');
$scope_id= Atomik::get('session/scope_id');

Atomik::needed('Company.class');
$list_type = Review::getAllReviewType($company_id,$scope_id);
$sql_query = "SELECT id,scope FROM scope ORDER BY scope ASC";
$list_scope = A('db:'.$sql_query);
$html=  '<form method="POST" action="'.Atomik::url('review_type', false).'">';
$html.= '<fieldset class="medium">';
$html.= Company::getSelectCompany($company_id,"active");
$html.= '</fieldset >';
$html.= '</form>';
$html .= '<form action="" method="post" >';
$html .= '<fieldset class="medium">';
$html .= '<label for="show_scope">Scope:</label>';
$html .= '<select class="combobox" onchange="this.form.submit()" name="scope_id" id="scope_id">';
$html .= '<option value="">--All--';
foreach ($list_scope as $row):
    $html .= "<option value='".$row['id']."'";
    if ($row['id'] == $scope_id) {
        $html .= " SELECTED";
    }  
    $html .= ">".$row['scope'];
endforeach;	
$html .= '</select>';
$html .= '</fieldset>';
$html .= '</form>';

Atomik::set('title',"Reviews type");
Atomik::set('css_title',"data");
Atomik::set('css_reset',"no_show");
Atomik::set('url',"review_type");
Atomik::set('url_add',Atomik::url('edit_review_type'));
Atomik::set('title_add',"Add a type");
Atomik::set('page',$page);
Atomik::set('limite',$limite);
Atomik::set('css_page_previous','no_show');	
Atomik::set('css_page_next','no_show');	
Atomik::set('css_page',"no_show");
Atomik::set('select_menu',$html);
