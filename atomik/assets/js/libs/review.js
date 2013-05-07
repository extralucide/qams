// JavaScript Document
function review_delete_query ()
{
     var query_string;

   query_string = "review/remove_review.php?multi_modify=yes";

   return query_string;
}
function review_word_data_create_query ()
{
     var query_string;

   query_string = "export/export_docx_review_template";

   return query_string;
}
function review_pdf_data_create_query ()
{
     var query_string;

   query_string = "export/export_review_pdf_tcpdf.php?multi_modify=yes";

   return query_string;
}
function review_full_word_data_create_query ()
{
     var query_string;

   query_string = "export/export_docx_review.php?multi_modify=yes";//&page=<?php echo $page ?>&limite=<?php echo $limite ?>";
   //if (my_orderform.show_project.value != undefined) {
      //query_string = query_string + "&show_project=<?php echo $show_project ?>";
   //}
   return query_string;
}
function review_excel_data_create_query ()
{
     var query_string;

   query_string = "export/export_xlsx_review";
   return query_string;
}
function review_word_get_checkbox_value()
{
 var checkboxes = '&multiple_review_id=';
 var baseline;
 var my_modify_data_form = document.multi_modify_data;
 var my_query = review_word_data_create_query ();
 
 if (document.orderform.modify_action_id.length != undefined) {
  for (var i=0; i < document.orderform.modify_action_id.length; i++)
  {
   	 if (document.orderform.modify_action_id[i].checked) {
        var rad_val = document.orderform.modify_action_id[i].value;
        checkboxes += rad_val;
      }
  }  
  //alert("The checkboxes button you chose has the value: " + checkboxes);
 }
 else {
    /* case for a form with 1 element */
	if (document.orderform.modify_action_id.checked) {
        var rad_val = document.orderform.modify_action_id.value;
	    checkboxes += rad_val;
	    //alert("The radio button you chose has the value: " + rad_val);
      } 
 }
 document.location=my_query + checkboxes;
}
function review_pdf_get_checkbox_value()
{
 var checkboxes = '&multiple_review_id=';
 var baseline;
 var my_modify_data_form = document.multi_modify_data;
 var my_query = review_pdf_data_create_query ();
 
 if (document.orderform.modify_action_id.length != undefined) {
  for (var i=0; i < document.orderform.modify_action_id.length; i++)
  {
   	 if (document.orderform.modify_action_id[i].checked) {
        var rad_val = document.orderform.modify_action_id[i].value;
        checkboxes += rad_val;
      }
  }  
  //alert("The checkboxes button you chose has the value: " + checkboxes);
 }
 else {
    /* case for a form with 1 element */
	if (document.orderform.modify_action_id.checked) {
        var rad_val = document.orderform.modify_action_id.value;
	    checkboxes += rad_val;
	    //alert("The radio button you chose has the value: " + rad_val);
      } 
 }
 document.location=my_query + checkboxes;
}
function review_full_word_get_checkbox_value()
{
 var checkboxes = '&multiple_review_id=';
 var baseline;
 var my_modify_data_form = document.multi_modify_data;
 var my_query = review_full_word_data_create_query ();
 
 if (document.orderform.modify_action_id.length != undefined) {
  for (var i=0; i < document.orderform.modify_action_id.length; i++)
  {
   	 if (document.orderform.modify_action_id[i].checked) {
        var rad_val = document.orderform.modify_action_id[i].value;
        checkboxes += rad_val;
      }
  }  
  //alert("The checkboxes button you chose has the value: " + checkboxes);
 }
 else {
    /* case for a form with 1 element */
	if (document.orderform.modify_action_id.checked) {
        var rad_val = document.orderform.modify_action_id.value;
	    checkboxes += rad_val;
	    //alert("The radio button you chose has the value: " + rad_val);
      } 
 }
 document.location=my_query + checkboxes;
}
function review_excel_get_checkbox_value()
{
 var checkboxes = '&multiple_review_id=';
 var baseline;
 var my_modify_data_form = document.multi_modify_data;
 var my_query = review_excel_data_create_query ();
 
 if (document.orderform.modify_action_id.length != undefined) {
  for (var i=0; i < document.orderform.modify_action_id.length; i++)
  {
   	 if (document.orderform.modify_action_id[i].checked) {
        var rad_val = document.orderform.modify_action_id[i].value;
        checkboxes += rad_val;
      }
  }  
  //alert("The checkboxes button you chose has the value: " + checkboxes);
 }
 else {
    /* case for a form with 1 element */
	if (document.orderform.modify_action_id.checked) {
        var rad_val = document.orderform.modify_action_id.value;
	    checkboxes += rad_val;
	    //alert("The radio button you chose has the value: " + rad_val);
      } 
 }
 document.location=my_query + checkboxes;
}
function review_delete_get_checkbox_value()
{
 var checkboxes = '&multiple_review_id=';
 var baseline;
 var my_modify_data_form = document.multi_modify_data;
 var my_query = review_delete_query ();
 
 if (document.orderform.modify_action_id.length != undefined) {
  for (var i=0; i < document.orderform.modify_action_id.length; i++)
  {
   	 if (document.orderform.modify_action_id[i].checked) {
        var rad_val = document.orderform.modify_action_id[i].value;
        checkboxes += rad_val + ';';
      }
  }  
  //alert("The checkboxes button you chose has the value: " + checkboxes);
 }
 else {
    /* case for a form with 1 element */
	if (document.orderform.modify_action_id.checked) {
        var rad_val = document.orderform.modify_action_id.value;
	    checkboxes += rad_val + ';';
	    //alert("The radio button you chose has the value: " + rad_val);
      } 
 }
 document.location=my_query + checkboxes;
}
function review_html_data_create_query (type)
{
   var query_string;
   
   if (type == "display") {
	query_string = "review/display_mom";
   }
   else {
	query_string = "mail/send_minutes.php?m=y";
   }
   return query_string;
}
function review_html_get_checkbox_value(type)
{
 var checkboxes = '&review_id=';
 var baseline;
 var my_modify_data_form = document.multi_modify_data;
 var my_query = review_html_data_create_query (type);
 
 if (document.orderform.modify_action_id.length != undefined) {
  for (var i=0; i < document.orderform.modify_action_id.length; i++)
  {
   	 if (document.orderform.modify_action_id[i].checked) {
        var rad_val = document.orderform.modify_action_id[i].value;
        checkboxes += rad_val;
      }
  }  
  //alert("The checkboxes button you chose has the value: " + checkboxes);
 }
 else {
    /* case for a form with 1 element */
	if (document.orderform.modify_action_id.checked) {
        var rad_val = document.orderform.modify_action_id.value;
	    checkboxes += rad_val;
	    //alert("The radio button you chose has the value: " + rad_val);
      } 
 }
 document.location=my_query + checkboxes;
}
var http; // Notre objet XMLHttpRequest

function createRequestObject()
{
    var http;
    if(window.XMLHttpRequest)
    { // Mozilla, Safari, ...
        http = new XMLHttpRequest();
    }
    else if(window.ActiveXObject)
    { // Internet Explorer
        http = new ActiveXObject("Microsoft.XMLHTTP");
    }
    return http;
}
function uploadEnd(sError) {
		if(sError == 'OK') {
				document.getElementById("status_change").innerHTML = "";
		} else {
				document.getElementById("status_change").innerHTML = sError;
		}
}	
function send_mail(review_id){
	var target = "../mail/send_minutes.php?review_id=" + review_id;
	document.getElementById('status_change').innerHTML = '<em style="margin:10px;">Send minutes ... Please wait ...</em> <img src="../images/loading.gif" />';
	http = createRequestObject();
	http.open('get', target, true);
	http.onreadystatechange = handleAJAXStatusReturn;
	http.send(null);
}

function handleAJAXStatusReturn()
{
    if(http.readyState == 4)
    {
        if(http.status == 200)
        {
            document.getElementById('status_change').innerHTML = http.responseText;
			//document.getElementById('nb_data').innerHTML = "1234";
        }
        else
        {
            document.getElementById('status_change').innerHTML = "<strong>N/A</strong>";
        }
    }
}
function display_atomik_review (menu,arrow)
{
    /* l'?l?ment poss?de t'il un identifiant ? */
    if (document.getElementById)
    {
        /* oui, lecture de l'?l?ment */
        thisMenu = document.getElementById(menu)
        /* est-il visible ? */
        if (thisMenu.style.display == "table-row")
        {
            /* on remet la fl?che vers le bas */
            arrow.style.background='url(assets/images/down_arrow_2.png) no-repeat'
            /* oui, on le cache */
            thisMenu.style.display = "none"
        }
        else
        {
            /* on met la fl?che vers le haut */
            arrow.style.background='url(assets/images/up_arrow_2.png) no-repeat'
            /* non, on l'affiche */
            thisMenu.style.display = "table-row"
        }
        return false
    }
    else
    {
        /* non, pas d'identifiant */
        return true
    }
}