/*
 * AJAX 
 */
 
/* XMLHttpRequest objects */ 
var http;
var http_prr;
var http_internal_prr;
var http_diag;
var http_pr;
var http_baseline;
var http_diagram;

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
/* 
 * AJAX handler
 */
function handleAJAXReturn_baseline_backup()
{
    if(http.readyState == 4)
    {
        if(http.status == 200){
            document.getElementById('messages').innerHTML = http.responseText;
        }
        else{
            document.getElementById('messages').innerHTML = "<li class=\"failed\" style=\"list-style-type: none;\">Baseline backup failed. " + http.status + "</li>";
        }
    }
}
function handleAJAXReturn()
{
    if(http.readyState == 4)
    {
        if(http.status == 200)
        {
            var e = document.getElementById('nbr_clics');
			e.innerHTML = http.responseText;
			var scripts = e.getElementsByTagName('script');
			for (var i=0; i < scripts.lenght; i++)
			{
				if (windows.execScript) {
					windows.execScript(scripts[i].text.replace('<!--',''));
				}
				else {
					window.eval(scripts[i].text);
				}
			}
        }
        else
        {
            document.getElementById('nbr_clics').innerHTML = "<strong>N/A</strong>";
        }
    }
}
function handleAJAXStatusReturn()
{
    if(http.readyState == 4)
    {
        if(http.status == 200)
        {
            document.getElementById('messages').innerHTML = http.responseText;
        }
        else
        {
            document.getElementById('messages').innerHTML = "<strong>N/A</strong>";
        }
    }
}
function handleAJAXReturn_prr()
{
	if(http_prr.readyState == 4)
	{
		if(http_prr.status == 200)
		{
			document.getElementById('prr_table').innerHTML = http_prr.responseText;
		}
		else
		{
			document.getElementById('prr_table').innerHTML = "<strong>N/A</strong>";
		}
		http_prr = null;
	}
}
function handleAJAXReturn_internal_prr()
{
	if(http_internal_prr.readyState == 4)
	{
		if(http_internal_prr.status == 200)
		{
			document.getElementById('internal_prr_table').innerHTML = http_internal_prr.responseText;
		}
		else
		{
			document.getElementById('internal_prr_table').innerHTML = "<strong>N/A</strong>";
		}
		http_internal_prr = null;
	}
}
function handleAJAXReturn_diag()
{
	if(http_diag.readyState == 4)
	{
		if(http_diag.status == 200)
		{
			var e = document.getElementById('display_diag_tree');
			e.innerHTML = http_diag.responseText;
			var scripts = e.getElementsByTagName('script');
			for (var i=0; i < scripts.lenght; i++)
			{
				if (windows.execScript) {
					windows.execScript(scripts[i].text.replace('<!--',''));
				}
				else {
					window.eval(scripts[i].text);
				}
			}
		}
		else
		{
			document.getElementById('display_diag_tree').innerHTML = "<strong>N/A</strong>";
		}
	}
}
function handleAJAXReturn_pr()
{
	if(http_pr.readyState == 4)
	{
		if(http_pr.status == 200)
		{
			var e = document.getElementById('pr_table');
			e.innerHTML = http_pr.responseText;
			var scripts = e.getElementsByTagName('script');
			for (var i=0; i < scripts.lenght; i++)
			{
				if (windows.execScript) {
					windows.execScript(scripts[i].text.replace('<!--',''));
				}
				else {
					window.eval(scripts[i].text);
				}
			}
		}
		else
		{
			document.getElementById('pr_table').innerHTML = "<strong>N/A</strong>";
		}
	}
}	
function handleAJAXReturn_baseline()
{
	if(http_baseline.readyState == 4)
	{
		if(http_baseline.status == 200)
		{
			var e = document.getElementById('baseline_table');
			e.innerHTML = http_baseline.responseText;
			var scripts = e.getElementsByTagName('script');
			for (var i=0; i < scripts.lenght; i++)
			{
				if (windows.execScript) {
					windows.execScript(scripts[i].text.replace('<!--',''));
				}
				else {
					window.eval(scripts[i].text);
				}
			}
		}
		else
		{
			document.getElementById('baseline_table').innerHTML = "<strong>N/A</strong>";
		}
	}
}
function handleAJAXReturn_diagram()
{
	if(http_diagram.readyState == 4)
	{
		if(http_diagram.status == 200)
		{
			document.getElementById('diagram_tree_img').innerHTML = http_diagram.responseText;
		}
		else
		{
			document.getElementById('diagram_tree_img').innerHTML = "<strong>N/A</strong>";
		}
		http_diagram = null;
	}
}
function gestion_diagram(id,target){
	document.getElementById(id).innerHTML = '<li class=\"warning\" style=\"list-style-type: none\">Creating diagram image ... Please wait ...</li>';
	http_diagram = createRequestObject();
	http_diagram.open('get', target, true);
	http_diagram.onreadystatechange = handleAJAXReturn_diagram;
	http_diagram.send(null);
}
function gestion_prr(target)
{
	document.getElementById('prr_table').innerHTML = '<li class=\"warning\" style=\"list-style-type: none\">Creating peer review table and computing validation status ... Please wait ...</li>';
	http_prr = createRequestObject();
	http_prr.open('get', target, true);
	http_prr.onreadystatechange = handleAJAXReturn_prr;
	http_prr.send(null);
}
function gestion_internal_prr(target)
{
	document.getElementById('internal_prr_table').innerHTML = '<li class=\"warning\" style=\"list-style-type: none\">Creating peer review table and computing validation status ... Please wait ...</li>';
	http_internal_prr = createRequestObject();
	http_internal_prr.open('get', target, true);
	http_internal_prr.onreadystatechange = handleAJAXReturn_internal_prr;
	http_internal_prr.send(null);
}
function gestion_diag(target)
{
	document.getElementById('display_diag_tree').innerHTML = '<li class=\"warning\" style=\"list-style-type: none\">Creating table ... Please wait ...</li>';
	http_diag = createRequestObject();
	http_diag.open('get', target, true);
	http_diag.onreadystatechange = handleAJAXReturn_diag;
	http_diag.send(null);
}
function gestion_pr(target)
{
	document.getElementById('pr_table').innerHTML = '<li class=\"warning\" style=\"list-style-type: none\">Creating table ... Please wait ...</li>';
	http_pr = createRequestObject();
	http_pr.open('get', target, true);
	http_pr.onreadystatechange = handleAJAXReturn_pr;
	http_pr.send(null);
}
function gestion_baseline(target)
{
	document.getElementById('baseline_table').innerHTML = '<li class=\"warning\" style=\"list-style-type: none\">Creating table ... Please wait ...</li>';
	http_baseline = createRequestObject();
	http_baseline.open('get', target, true);
	http_baseline.onreadystatechange = handleAJAXReturn_baseline;
	http_baseline.send(null);
}
function get_baseline_backup(target)
{
	document.getElementById('messages').innerHTML = "<li class=\"warning\" style=\"list-style-type: none\">Backup in progress.... </li>";
	http = createRequestObject();
	http.open('get', target, true);
	http.onreadystatechange = handleAJAXReturn_baseline_backup;
	http.send(null);
}
function gestionClic(target,nb_items)
{
	document.getElementById('nbr_clics').innerHTML = '<li class=\"warning\" style=\"list-style-type: none\">Creating data table with '+nb_items+' items ... Please wait ...</li>';
	http = createRequestObject();
	http.open('get', target, true);
	http.onreadystatechange = handleAJAXReturn;
	http.send(null);
}
function gestionClicStatus(target)
{
	document.getElementById('javascript_dialog').innerHTML = '<em style="margin:10px;">Creating data table ... Please wait ...</em> <img src="assets/images/loading.gif" />';
	http = createRequestObject();
	http.open('get', target, true);
	http.onreadystatechange = handleAJAXStatusReturn;
	http.send(null);
}
function send_mail(data_id){
	var target = "mail/send_data?id=" + data_id + "&draft=yes";
	document.getElementById('messages').innerHTML = "<li class=\"warning\" style=\"list-style-type: none;\">Send data ... Please wait ...</li>";
	http = createRequestObject();
	http.open('get', target, true);
	http.onreadystatechange = handleAJAXStatusReturn;
	http.send(null);
}
function send_minutes(data_id){
	var target = "mail/send_minutes?review_id=" + data_id + "&draft=yes";
	document.getElementById('messages').innerHTML = "<li class=\"warning\" style=\"list-style-type: none;\">Send minute ... Please wait ...</li>";
	http = createRequestObject();
	http.open('get', target, true);
	http.onreadystatechange = handleAJAXStatusReturn;
	http.send(null);
}
function send_wiki(data_id){
	var target = "mail/send_wiki?id=" + data_id + "&draft=yes";
	document.getElementById('messages').innerHTML = "<li class=\"warning\" style=\"list-style-type: none;\">Send article ... Please wait ...</li>";
	http = createRequestObject();
	http.open('get', target, true);
	http.onreadystatechange = handleAJAXStatusReturn;
	http.send(null);
}
function uploadRun(text) {
		document.getElementById("messages").innerHTML = "<li class=\"warning\" style=\"list-style-type: none;\">" + text + "</li>";
		document.getElementById("export_data_list_frame").style.display = 'block';	
		return true;
}	
function uploadEnd(sError,text) {
		if(sError == 'OK') {
				document.getElementById("messages").innerHTML = "<li class=\"success\" style=\"list-style-type: none;\">" + text + "</li>";
		} else {
				document.getElementById("messages").innerHTML = "<li class=\"failed\" style=\"list-style-type: none;\">" + sError + "/li>";
		}
}	
function PRR_uploadRun() {
	document.getElementById("messages").innerHTML = "<li class=\"success\" style=\"list-style-type: none;\">Upload is running ...</li>";
	document.getElementById("submit_peer_review").disabled = true;
	return true;
}
		
function PRR_uploadEnd(sError, sPath,data_id) {
	if(sError == 'OK') {
			document.getElementById("messages").innerHTML = "<li class=\"success\" style=\"list-style-type: none;\">Upload done.</li>";
	} else {
			document.getElementById("messages").innerHTML = "<li class=\"failed\" style=\"list-style-type: none;\">" + sError + "/li>";
	}
	document.getElementById("submit_peer_review").disabled = false;
	gestion_prr("peer_review/display_peer_review.php?id="+data_id);
}
function PRR_reuploadRun() {
	document.getElementById("messages").innerHTML = "<img src=\"assets/images/loading.gif\" alt=\"Update is running...\" width=\"220\" height=\"19\" />";
	document.getElementById("resubmit_peer_review").disabled = true;
	return true;
}
		
function PRR_reuploadEnd(sError, sPath,data_id) {
	if(sError == 'OK') {
			document.getElementById("messages").innerHTML = "<li class=\"success\" style=\"list-style-type: none;\">Update done.</li>";
	} else {
			document.getElementById("messages").innerHTML = "<li class=\"failed\" style=\"list-style-type: none;\">" + sError + "/li>";
	}
	document.getElementById("resubmit_peer_review").disabled = false;
	gestion_prr("peer_review/display_peer_review.php?id="+data_id);
	/* show upload new peer review */
	document.getElementById("update_upload_peer_review_div").style.display = 'none';
	/* hide upload and update peer review */
	document.getElementById("upload_peer_review_div").style.display = 'block';		
}
function Update_Peer_Review(id,arrow) {
	if (document.getElementById("update_upload_peer_review_div").style.display  == "none")
	{
		/* show upload and update peer review */
		document.getElementById("update_upload_peer_review_div").style.display = 'block';
		/* hide upload new peer review */
		document.getElementById("upload_peer_review_div").style.display = 'none';	
		document.getElementById('update_prr_id').value = id;
		document.getElementById('prr_link_id_in_title').innerHTML = id;
		arrow.style.background='url(assets/images/up_arrow_2.png) no-repeat'
	}
	else{
		/* hide update new peer review */
		document.getElementById("update_upload_peer_review_div").style.display = 'none';
		/* show upload peer review */
		document.getElementById("upload_peer_review_div").style.display = 'block';
		arrow.style.background='url(assets/images/down_arrow_2.png) no-repeat'
	}
}
function New_Peer_Review() {
	/* hide update new peer review */
	document.getElementById("update_upload_peer_review_div").style.display = 'none';
	/* show upload peer review */
	document.getElementById("upload_peer_review_div").style.display = 'block';	
}
function Compute_Peer_Review(prr_id) {
	gestion_prr("peer_review/compute_prr.php?prr_id="+prr_id);	
}
function PRR_computeEnd(sError,data_id) {
	if(sError == 'OK') {
			document.getElementById("messages").innerHTML = "<li class=\"success\" style=\"list-style-type: none;\">Update done.</li>";
	} else {
			document.getElementById("messages").innerHTML = "<li class=\"failed\" style=\"list-style-type: none;\">" + sError + "/li>";
	}
	gestion_prr("peer_review/display_peer_review.php?id="+data_id);	
}
function confime_delete_data(data_id){
	var agree=confirm("Are you sure you want to delete this data ?");
	if (agree)
		document.location='data/remove_data?id=' + data_id;
	else
		return false ;
}

/* for data baseline not displayed by atomik */
function display_data_baseline (menu,arrow)
{
    /* l'?l?ment poss?de t'il un identifiant ? */
    if (document.getElementById)
    {
        /* oui, lecture de l'?l?ment */
        thisMenu = document.getElementById(menu)
        /* est-il visible ? */
        if (thisMenu.style.display == "table-row-group")
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
            thisMenu.style.display = "table-row-group"
        }
        return false
    }
    else
    {
        /* non, pas d'identifiant */
        return true
    }
}
function ouvrir(fichier,fenetre) {
    ff=window.open(fichier,fenetre,"width=1124,height=600,left=30,top=20,scrollbars=yes");
	ff.focus();
}
function display_folder (menu,arrow)
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
            arrow.style.background='url(../assets/images/down_arrow_2.png) no-repeat'
            /* oui, on le cache */
            thisMenu.style.display = "none"
        }
        else
        {
            /* on met la fl?che vers le haut */
            arrow.style.background='url(../assets/images/up_arrow_2.png) no-repeat'
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
function confirmFileAttachRemove()
{
	var agree=confirm("Remove file attachment ?");
	if (agree)
		return true ;
	else
		return false ;
	document.location=my_query;
}
function data_create_query ()
{
	 var query_string;

	query_string = "data/add_source_link.php?multi_modify=yes&page=<?php echo $page ?>&limite=<?php echo $limite ?>";
	query_string = query_string + "&show_project=<?php echo $show_project ?>";
	query_string = query_string + "&show_lru=<?php echo $show_lru ?>";
	query_string = query_string + "&show_type=<?php echo $show_type ?>";
	query_string = query_string + "&show_baseline=<?php echo $show_baseline ?>";
	query_string = query_string + "&show_application=<?php echo $show_application ?>"
	query_string = query_string + "&show_status=<?php echo $show_status ?>";
	return query_string;
}
function get_checkbox_value()
{
 var checkboxes="";
 var table;
 var my_modify_data_form = document.multi_modify_data;
 var my_query = data_create_query ();
 
 if (document.select_source_data.source_id.length != undefined) {
  for (var i=0; i < document.select_source_data.source_id.length; i++)
  {
	 if (document.select_source_data.source_id[i].checked) {
		var rad_val = document.select_source_data.source_id[i].value;
		checkboxes += rad_val + ';';
	  }
  }  
  //alert("The checkboxes button you chose has the value: " + checkboxes);
 }
 else {
	/* case for a form with 1 element */
	if (document.select_source_data.source_id.checked) {
		var rad_val = document.select_source_data.source_id.value;
		checkboxes += rad_val + ';';
		//alert("The radio button you chose has the value: " + rad_val);
	  } 
 }
 table = document.select_source_data.table.value;
 document.select_source_data.multiple_data_id.value=checkboxes;
 //alert("Test: " + my_query + '&table=' + table + checkboxes);
 //document.location=my_query + '&table=' + table + checkboxes;
}	
function CuteWebUI_AjaxUploader_OnTaskComplete(task)
{
	var div=document.createElement("DIV");
	var link=document.createElement("A");
	link.setAttribute("href","savefiles/"+task.FileName);
	link.innerHTML="You have uploaded file : savefiles/"+task.FileName;
	link.target="_blank";
	div.appendChild(link);
	document.body.appendChild(div);
}
//<![CDATA[

// Uncomment the following code to test the "Timeout Loading Method".
// CKEDITOR.loadFullCoreTimeout = 5;

var editor;

function replaceDiv( div )
{
	if ( editor )
		editor.destroy();

	editor = CKEDITOR.replace( div );
}

function submitFichier(formulaire){
	 var iframe;
	 if(!document.getElementById('hiddenuploadiframe')){
		  iframe=document.createElement("iframe");
		  iframe.setAttribute("name","hiddenuploadiframe");
		  iframe.setAttribute("id","hiddenuploadiframe");
		  iframe.style.display="none";
		  formulaire.parentNode.insertBefore(iframe,formulaire);
	 }else{
		  iframe=document.getElementById('hiddenuploadiframe');
	 }
	 formulaire.setAttribute("target","hiddenuploadiframe");
	 return true;
}	

function uploadInit() {
	// Je pré-charge l'image
	var oLoading = new Image();
	oLoading.src = "assets/images/loading.gif";
}
function confirm_suppress (data_id,prr_id)
  {

	agree_remove=confirm("Are you sure you want to remove peer review report "+prr_id+" from data "+ data_id);
	if (agree_remove) {
	  gestionClic("peer_review/display_peer_review.php?id="+data_id+"&peer_review_highlight=active&remove_prr=yes&remove_prr_id="+prr_id);
	}
	else {
	  return false;
	}
  }
function confirm_suppress_pr (pr_id,link_id)
  {

	agree_remove=confirm("Are you sure you want to remove link to data with id "+link_id+" from PR "+ pr_id);
	if (agree_remove) {
	  gestion_pr("data/pr_link.php?id="+pr_id+"&peer_review_highlight=active&remove_pr=yes&remove_link_id="+link_id);
	}
	else {
	  return false;
	}
  }  

function getfile(){
    var file_browser = document.getElementById('hiddenfile');
	//file_browser.click();
    // document.getElementById('selectedfile').value=document.getElementById('hiddenfile').value
    document.getElementById('peer_review_location').value=file_browser.value;
	//peer_review_location.appendChild(hiddenfile);
	document.getElementById('form_edit_prr_location').submit();
}
function get_prr_path(){
  var file_browser = document.getElementById('hiddenfile');
  alert('value:'+ file_browser.value); //affiche 'value:' + le chemin de votre fichier
  var newfile = file_browser.cloneNode(true);
  alert('new value:'+newfile.value);  //affiche 'new value:' uniquement car newfile.value == ''
}
/* Script "z'experts" : http://perso.wanadoo.fr/coin.des.experts/
   delivre sans aucune garantie, ni des auteurs, ni du gouvernement. 
   Diffusion libre, mais merci de conserver cette signature :-) */
 
/* La fonction bulle() qui ouvre la bulle d'aide a 3 arguments possibles:
   - le premier est le message a faire apparaitre. 
   - LE DEUXIEME EST OBLIGATOIREMENT "event" (sans les guillemets) 
   c.a.d. un mot cle du javascript.
   - Le 3eme argument est facultatif. Il permet d'ajuster 
   le decalage vertical afin de ne pas tronquer les bulles trop 
   longues ouvertes vers le bas de l'ecran; partez de
       hauteur=1,2 x taille police x nombre de lignes +10
   
   Enfin, mettre le bloc <DIV id="tip">...</DIV> en tete du bloc BODY. 
   NE PAS CHANGER LE NOM "tip";  sinon, vous pouvez modifier le style 
   qui suit ou le message d'erreur ? votre gr? (mais laissez le
   position:absolute et un z-index tres grand)
  */

var bulleStyle=null
if (!document.layers && !document.all && !document.getElementById)
    event="chut";  //pour apaiser NN3 et autres antiquites

function bulle(msg,evt,hauteur){
 
     
    var xfenetre,yfenetre,xpage,ypage,element=null;
    var offset= 15;           // decalage par defaut
    var offset_x= -200;           // decalage par defaut
    var bulleWidth=200;       // largeur par defaut
    if (!hauteur) hauteur=40; // hauteur par d?faut

    if (document.layers) {
        bulleStyle=document.layers['tip'];
        bulleStyle.document.write('<layer bgColor="#ffffdd" '
            +'style="width:200px;border:1px solid black;color:black">'
            + msg + '</layer>' );
        bulleStyle.document.close();
        xpage = evt.pageX ;
        ypage  = evt.pageY;
        xfenetre = xpage ;
        yfenetre = ypage ;
    } else if (document.all) {
        element=document.all['tip']
        xfenetre = evt.x;
        yfenetre = evt.y ;
        xpage=xfenetre ;
        ypage=yfenetre	;
        if (document.body.scrollLeft) xpage = xfenetre + document.body.scrollLeft ;
        if (document.body.scrollTop) ypage = yfenetre + document.body.scrollTop;
    } else if (document.getElementById) {
        element=document.getElementById('tip')
        xfenetre = evt.clientX ;
        yfenetre = evt.clientY ;
        xpage=xfenetre ;
        ypage=yfenetre	;
        if(evt.pageX) xpage = evt.pageX ;
        if(evt.pageY) ypage  = evt.pageY ;
    }
    
    if(element) {
        bulleStyle=element.style;
        element.innerHTML=msg;
    }
		 	
    if(bulleStyle) {
        /* on met la bulle ? gauche du pointeur (si c'est possible)
        et en haut du pointeur si on est assez bas dans l'?cran */
				
        if (xfenetre > bulleWidth+offset) xpage=xpage-bulleWidth-offset_x;
        else xpage=xpage+15;
        if ( yfenetre > hauteur+offset ) ypage=ypage-hauteur-offset;
        bulleStyle.width=bulleWidth;
        if(typeof(bulleStyle.left)=='string') {
            bulleStyle.left=xpage+'px';
            bulleStyle.top=ypage+'px';
        } else {
            bulleStyle.left=xpage     ;
            bulleStyle.top=ypage ;
        }
        bulleStyle.visibility="visible";
    }
}
 
function couic(){
    if(bulleStyle)  bulleStyle.visibility="hidden";
}



function openMenu(menu)
{
    /* l'?l?ment poss?de t'il un identifiant ? */
    if (document.getElementById)
    {
        /* oui, lecture de l'?l?ment */
        thisMenu = document.getElementById(menu)
        /* est-il visible ? */
        if (thisMenu.style.display == "table-row-group")
        {
            /* oui, on le cache */
            thisMenu.style.display = "none"
        }
        else
        {
            /* non, on l'affiche */
            thisMenu.style.display = "table-row-group"
        }
        return false
    }
    else
    {
        /* non, pas d'identifiant */
        return true
    }
}
function hideMenu(menu)
{
    /* l'?l?ment poss?de t'il un identifiant ? */
    if (document.getElementById)
    {
        /* oui, lecture de l'?l?ment */
        thisMenu = document.getElementById(menu)
        /* est-il visible ? */
        if (thisMenu.style.display == "")
        {
            /* oui, on le cache */
            thisMenu.style.display = "none"
        }
        else
        {
            /* non, on l'affiche */
            thisMenu.style.display = ""
        }
        return false
    }
    else
    {
        /* non, pas d'identifiant */
        return true
    }
}		
function openMenu2(menu1,menu2,arrow)
{
    /* l'?l?ment poss?de t'il un identifiant ? */
    if (document.getElementById)
    {
        /* oui, lecture de l'?l?ment */
        thisMenu = document.getElementById(menu1)
        /* est-il visible ? */
        if (thisMenu.style.display == "table-row")
        {
            /* on remet la fl?che vers le bas */
            arrow.style.background='url(assets/images/down_arrow_2.png) no-repeat'
            /* oui, on le cache */
            thisMenu.style.display = "none"
            /* on cache ?galement les r?ponses */
            /* lecture de l'?l?ment 2 */
            thisMenu = document.getElementById(menu2)
            /* on le cache */
            thisMenu.style.display = "none"
		
        }
        else
        {
            /* non, on l'affiche */
            thisMenu.style.display = "table-row"
            /* on met la fl?che vers le haut */
            arrow.style.background='url(assets/images/up_arrow_2.png) no-repeat'
        }
        return false
    }
    else
    {
        /* non, pas d'identifiant */
        return true
    }
}

function newPost(whichPage)
{
    messageWindow = window.open(whichPage, "postwin", "toolbar=no,scrollbars=no,width=424,height='70%'")
}

function postMessage()
{
    messageWindow = window.open("message_post.php", "postwin", "toolbar=no,scrollbars=no,width=424,height='70%'")
}

function userLogin(whichPage)
{
    messageWindow = window.open(whichPage, "messagewin", "toolbar=no,scrollbars=no,width=250,height=200")
}

function createWriting()
{
    messageWindow = window.open("submission_upload2.php", "messagewin", "toolbar=no,scrollbars=no,width=424,height='70%'")
}

function confirmDelete()
{
    var agree=confirm("Are you sure you want to delete this?");
    if (agree)
        return true ;
    else
        return false ;
}
function confirmDataPromotionInspect()
{
    var agree=confirm("Are you sure you want to promote this data to 'ready to be validated' ?");
    if (agree)
        return true ;
    else
        return false ;
}
	
function confirmDataPromotionValid()
{
    var agree=confirm("Are you sure you want to promote this data to 'validated' ?");
    if (agree)
        return true ;
    else
        return false ;
}	
function confirmDataPromotionSuperseded() {
    alert("This data is obsolete.");
}	
function confirmDataPromotionReleased() {
    var agree=confirm("Are you sure you want to promote this data to 'obsolete' ?");
    if (agree)
        return true ;
    else
        return false ;
}	
function confirmDataPromotionAvailable() {
    var agree=confirm("Are you sure you want to promote this data to available ?");
    if (agree)
        return true ;
    else
        return false ;
}	
function confirmEditData() {
    var agree=confirm("Are you sure you want to edit this data ?");
    if (agree)
        return true ;
    else
        return false ;
}		
function modify_remark()
{
    //document.write("cliked");
    var agree=confirm("Are you sure you want to delete this?");
}
	
function InvalidPassword()
{
    var agree=alert("Password is not valid. Please try again.");
}	
	
function InvalidUsername()
{
    var agree=alert("Username is not valid.");
}

function function_not_implemented()
{
    var agree=alert('Function not available yet !');
}
function Confirm_Change_Remark_Status()
{
    var agree=confirm("Are you sure you want to change status?");
    if (agree)
    {
        var remark_id = document.orderform.remark_id.value;
        var status_id = document.orderform.status_id.value;
        document.location='edit_remark.php?chgt_status=yes&remark_id=' + remark_id + '&status_id=' + status_id;
    }
    else
        return false ;
}
/*
 * NCleanGrey_standard.js
 */
function cms_page_tab_style() {
	linksExternal(); 
	defaultFocus();
 	if (document.getElementById('navt_tabs')) {
		var el = document.getElementById('navt_tabs');
		_add_show_handlers(el);
	}
 	if (document.getElementById('page_tabs')) {
		var el = document.getElementById('page_tabs');
		_add_show_handlers(el);
	}
}

function IEhover() {
		if (document.getElementById('nav')) {
			cssHover('nav','LI');	
		}
	 	if (document.getElementById('navt_tabs')) {
			cssHover('navt_tabs','DIV');
		}
	 	if (document.getElementById('page_tabs')) {
			cssHover('page_tabs','DIV');
		}
}

function cssHover(tagid,tagname) {
	var sfEls = document.getElementById(tagid).getElementsByTagName(tagname);
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" cssHover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" cssHover\\b"), "");
		}
	}
}

function change(id, newClass, oldClass) {
	identity=document.getElementById(id);
	if (identity.className == oldClass) {
		identity.className=newClass;
	} else {
		identity.className=oldClass;
	}
}

function _add_show_handlers(navbar) {
    var tabs = navbar.getElementsByTagName('div');
    for (var i = 0; i < tabs.length; i += 1) {
	tabs[i].onmousedown = function() {
	    for (var j = 0; j < tabs.length; j += 1) {
		tabs[j].className = '';
		document.getElementById(tabs[j].id + "_c").style.display = 'none';
	    }
	    this.className = 'active';
	    document.getElementById(this.id + "_c").style.display = 'block';
	    return true;
	};
    }
    var activefound=0;
    for (var i = 0; i < tabs.length; i += 1) {
    	if (tabs[i].className=='active') activefound=i;
    }
    tabs[activefound].onmousedown();
}

function activatetab(index) {
	var el=0;
	if (document.getElementById('navt_tabs')) {
		el = document.getElementById('navt_tabs');
		
	} else {
 	  if (document.getElementById('page_tabs')) {
		  el = document.getElementById('page_tabs');
	  }
	}
	if (el==0) return;
	var tabs = navbar.getElementsByTagName('div');
	tabs[index].onmousedown();
}

function linksExternal()	{
	if (document.getElementsByTagName)	{
		var anchors = document.getElementsByTagName("a");
		for (var i=0; i<anchors.length; i++)	{
			var anchor = anchors[i];
			if (anchor.getAttribute("rel") == "external")	{
				anchor.target = "_blank";
			}
		}
	}
}

//use <input class="defaultfocus" ...>
function defaultFocus() {

   if (!document.getElementsByTagName) {
        return;
   }

   var anchors = document.getElementsByTagName("input");
   for (var i=0; i<anchors.length; i++) {
      var anchor = anchors[i];
      var classvalue;

      //IE is broken! 
      if(navigator.appName == 'Microsoft Internet Explorer') {
            classvalue = anchor.getAttribute('className');
      } else {
            classvalue = anchor.getAttribute('class');
      }

      if (classvalue!=null) {
                var defaultfocuslocation = classvalue.indexOf("defaultfocus");
                if (defaultfocuslocation != -1) {
                	anchor.focus();
			var defaultfocusselect = classvalue.indexOf("selectall");
			if (defaultfocusselect != -1) {
				anchor.select();
			}
                }
        }
   }
}

function togglecollapse(cid)
{
  document.getElementById(cid).style.display=(document.getElementById(cid).style.display!="block")? "block" : "none";
}
/*
 * Mail
 */
function MailSent(status,text) {
		if(status == 'success') {
				document.getElementById("messages").innerHTML = "<li class=\"success\" style=\"list-style-type: none;\">" + text + "</li>";
		} else {
				document.getElementById("messages").innerHTML = "<li class=\"failed\" style=\"list-style-type: none;\">" + text + "</li>";
		}
		//document.getElementById("submit_peer_review").disabled = false;
		//gestionClic("peer_review/display_peer_review.php?id="+data_id);
}
function exportPRR() {
		document.getElementById("messages").innerHTML = "<li class=\"warning\" style=\"list-style-type: none;\">Exporting Peer Review Report ... Please wait ...</li>";
		document.getElementById("export_prr_frame").style.display = 'block';	
		//document.getElementById("submit_peer_review").disabled = true;
		return true;
}	
function SendScriptMail(mToMail,mSub,mMsg) 
{ 
    var Maildb; 
    var UserName; 
    var MailDbName; 
    var MailDoc; 
    var AttachME; 
    var Session; 
    var EmbedObj; 
    var server; 
    try 
    { 
        // Create the Activex object for NotesSession 
				alert('test:1');
        Session = new ActiveXObject('Notes.NotesSession'); 
				alert('test:2');
        if(Session !=null) 
        { 
            // Get the user name to retrieve database 
            UserName = Session.UserName; 
            // Retrieve database from username 
            MailDbName = UserName.substring(0,1) + UerName.substring(
                UserName.indexOf( " " ,1) + 1 ,UserName.length) + ".nsf" 
            // Get database 
			alert('test:' +  MailDbName);
            Maildb = Session.GetDatabase("", MailDbName); 
            // open the database 
            if(Maildb.IsOpen != true) 
            { 
                Maildb.OPENMAIL(); 
            } 
            // Create the mail document 
            MailDoc = Maildb.CREATEDOCUMENT(); 
            // From email id (Username) 
            MailDoc.Form = 'Memo'; 
            // To email id 
            MailDoc.sendto = mToMail; 
            // Subject of the mail 
            MailDoc.Subject = mSub; 
            // Content of the mail 
            MailDoc.Body = mMsg 
                // if you want to save message on send, give true here 
                MailDoc.SAVEMESSAGEONSEND = false; 
            // send the mail (check ensure the internet connection) 
            MailDoc.Send(true); 
            // save the mail in draft (no need of internet connection) 
            MailDoc.Save(false, true); 
            // destroy the objects 
            Maildb = null; 
            MailDoc = null; 
            AttachME = null; 
            EmbedObj = null; 
            Session.Close(); 
            Session = null; 
            alert('Mail sent successfully'); 
        } 
        else 
        { 
            alert('Mail not sent'); 
        } 
    } 
    catch(err) 
    { 
        if(err == '[object Error]') 
        { 
            alert('Error while sending mail,Please check Lotus Notes installed in your system'); 
        } 
        else 
        { 
            alert('Error while sending mail: ' + err); 
        } 
    } 
}
/*
 * Baseline
 */
function data_create_query ()
{
     var query_string;

   query_string = "../link_review_baseline.php?multi_modify=yes";
   return query_string;
}
function get_checkbox_value()
{
 var checkboxes = '&multiple_data_id=';
 var baseline;
 var my_modify_data_form = document.multi_modify_data;
 var my_query = data_create_query ();
 
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
 baseline = document.multi_modify_data.show_baseline.value;
 document.location=my_query + '&show_baseline=' + baseline + '&' + checkboxes;
}
/* for baseline data display by atomik */
function display_baseline_data (menu,arrow)
{
    /* l'?l?ment poss?de t'il un identifiant ? */
    if (document.getElementById)
    {
        /* oui, lecture de l'?l?ment */
        thisMenu = document.getElementById(menu)
        /* est-il visible ? */
        if (thisMenu.style.display == "table-row-group")
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
            thisMenu.style.display = "table-row-group"
        }
        return false
    }
    else
    {
        /* non, pas d'identifiant */
        return true
    }
}
/* HTML 5 multiple upload */
// variables
var dropArea = document.getElementById('dropArea');
var canvas = document.querySelector('canvas');
var context = canvas.getContext('2d');
var count = document.getElementById('count');
var destinationUrl = document.getElementById('url');
var result = document.getElementById('result');
var list = [];
var totalSize = 0;
var totalProgress = 0;

// main initialization
(function(){

    // init handlers
    function initHandlers() {
        dropArea.addEventListener('drop', handleDrop, false);
        dropArea.addEventListener('dragover', handleDragOver, false);
    }

    // draw progress
    function drawProgress(progress) {
        context.clearRect(0, 0, canvas.width, canvas.height); // clear context

        context.beginPath();
        context.strokeStyle = '#4B9500';
        context.fillStyle = '#4B9500';
        context.fillRect(0, 0, progress * 500, 20);
        context.closePath();

        // draw progress (as text)
        context.font = '16px Verdana';
        context.fillStyle = '#000';
        context.fillText('Progress: ' + Math.floor(progress*100) + '%', 50, 15);
    }

    // drag over
    function handleDragOver(event) {
        event.stopPropagation();
        event.preventDefault();

        dropArea.className = 'hover';
    }

    // drag drop
    function handleDrop(event) {
        event.stopPropagation();
        event.preventDefault();

        processFiles(event.dataTransfer.files);
    }

    // process bunch of files
    function processFiles(filelist) {
        if (!filelist || !filelist.length || list.length) return;

        totalSize = 0;
        totalProgress = 0;
        result.textContent = '';

        for (var i = 0; i < filelist.length && i < 50; i++) {
            list.push(filelist[i]);
            totalSize += filelist[i].size;
        }
        uploadNext();
    }

    // on complete - start next file
    function handleComplete(size) {
        totalProgress += size;
        drawProgress(totalProgress / totalSize);
        uploadNext();
    }

    // update progress
    function handleProgress(event) {
        var progress = totalProgress + event.loaded;
        drawProgress(progress / totalSize);
    }

    // upload file
    function uploadFile(file, status) {

        // prepare XMLHttpRequest
        var xhr = new XMLHttpRequest();
        xhr.open('POST', destinationUrl.value);
        xhr.onload = function() {
            result.innerHTML += this.responseText;
            handleComplete(file.size);
        };
        xhr.onerror = function() {
            result.textContent = this.responseText;
            handleComplete(file.size);
        };
        xhr.upload.onprogress = function(event) {
            handleProgress(event);
        }
        xhr.upload.onloadstart = function(event) {
        }

        // prepare FormData
        var formData = new FormData();  
        formData.append('myfile', file); 
        xhr.send(formData);
    }

    // upload next file
    function uploadNext() {
        if (list.length) {
            count.textContent = list.length - 1;
            dropArea.className = 'uploading';

            var nextFile = list.shift();
            if (nextFile.size >= 26214400) { // 25600kb
                result.innerHTML += '<div class="f">Too big file (max filesize exceeded)</div>';
                handleComplete(nextFile.size);
            } else {
                uploadFile(nextFile, status);
            }
        } else {
            dropArea.className = '';
        }
    }

    initHandlers();
})();