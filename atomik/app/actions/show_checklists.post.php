<?php
$postArray = &$_POST;
if (isset($_POST['show_company'])) {
    Atomik::set('session/company_id',$_POST['show_company']);
}
if (isset($_POST['checklist_id'])) {
    Atomik::set('session/checklist_id',$_POST['checklist_id']);
}
Atomik::redirect('show_checklists',false);
