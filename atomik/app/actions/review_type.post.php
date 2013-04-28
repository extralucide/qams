<?php
$postArray = &$_POST;
if (isset($_POST['show_company'])) {
    Atomik::set('session/company_id',$_POST['show_company']);
}
if (isset($_POST['scope_id'])) {
    Atomik::set('session/scope_id',$_POST['scope_id']);
}
Atomik::redirect('review_type',false);