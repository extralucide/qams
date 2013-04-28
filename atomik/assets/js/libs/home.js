/* JQuery inside */
/*
$(function() {   
 $('.summaryImage').cycle({    
 fx:    'scrollUp',    
 delay: -6000,    
 timeout: 6000,     
 pager:  '#navigation',    
 pagerAnchorBuilder: function(idx, slide) {   
 return '#navigation li:eq(' + (idx) + ') a';} });   
$('.summaryContent').cycle({     
 fx:    'fade',     
 delay: -6000,    
 timeout: 6000,     
 pager:  '#navigation',    
 pagerAnchorBuilder: function(idx, slide) {   
 return '#navigation li:eq(' + (idx) + ') a';} 
});
//adding some opacity to content box 
$('div.summaryContent').css({ opacity: 0.80 } );});
*/
/* Ajoute la pseudo methode hover sur un élément, via CSS, utilise la classe CSS .hover */
function addHover(elm) {
elm.style.behavior = " ";
elm.onmouseenter = function() {
 this.className+= ' hover';
}
elm.onmouseleave = function() {
 this.className = this.className.replace(/\bhover\b/,"" );
}
}

function handleAJAXReturn_ug()
{
    if(http.readyState == 4)
    {
        if(http.status == 200)
        {
            document.getElementById('user_guide_page').innerHTML = http.responseText;
        }
        else
        {
            document.getElementById('user_guide_page').innerHTML = "<strong>Version loading failed.</strong> Status: " + http.status;
        }
    }
}
function handleAJAXReturn_qams_backup()
{
    if(http.readyState == 4)
    {
        if(http.status == 200)
        {
            document.getElementById('qams_backup').innerHTML = http.responseText;
        }
        else
        {
            document.getElementById('qams_backup').innerHTML = "<strong>QAMS backup failed.</strong> Status: " + http.status;
        }
    }
}

function handleAJAXReturn_intranet()
{
    if(http_intranet.readyState == 4)
    {
        if(http_intranet.status == 200)
        {
            document.getElementById('output').innerHTML = http_intranet.responseText;
        }
        else
        {
            document.getElementById('output').innerHTML = "<strong>Loading failed.</strong> Status: " + http_intranet.status;
        }
    }
}
var root_project_dir;
function handleAJAXReturn_working_dir()
{
    if(http_intranet.readyState == 4)
    {
        if(http_intranet.status == 200)
        {
			/* need to replace anchor target */
			var reg=new RegExp('href="([^"]*")', "g");
			//var reg = str.match(/href="([^"]*")/g)
			//var reg2=new RegExp("(/)", "g");
			var receive_text = http_intranet.responseText;
			pre_reg_text = "mon_test";/* root_project_dir.replace(reg2,"\\$1"); */
            document.getElementById('project_front_page_id').innerHTML = receive_text.replace(reg,'href="' + root_project_dir + '/$1"');
        }
        else
        {
            document.getElementById('project_front_page_id').innerHTML = "<strong>Loading failed.</strong> Status: " + http_intranet.status;
        }
    }
}

function get_user_guide(target)
	{
		document.getElementById('user_guide_page').innerHTML = '<em>Chargement...</em>';
		http = createRequestObject();
		http.open('get', target, true);
		http.onreadystatechange = handleAJAXReturn_ug;
		http.send(null);
	}
function get_qams_backup(target)
	{
		document.getElementById('qams_backup').innerHTML = '<em>Backup in progress...</em>';
		http = createRequestObject();
		http.open('get', target, true);
		http.onreadystatechange = handleAJAXReturn_qams_backup;
		http.send(null);
	}	
function get_intranet(target)
	{
		document.getElementById('output').innerHTML = '<em>Chargement...</em>';
		http_intranet = createRequestObject();
		//alert(target);
		http_intranet.open('GET', target, true);
		http_intranet.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		http_intranet.send("variable1=truc&variable2=bidule");
		http_intranet.onreadystatechange = handleAJAXReturn_intranet;
		http_intranet.send(null);
	}	
function get_working_dir(target)
	{
		root_project_dir = target;
		document.getElementById('project_front_page_id').innerHTML = '<em>Chargement...</em>';
		http_intranet = createRequestObject();
		//alert(target);
		http_intranet.open('GET', target, true);
		http_intranet.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		http_intranet.onreadystatechange = handleAJAXReturn_working_dir;
		http_intranet.send(null);
	}	
	/*
	 * essai AJAX a approfondir
	 * - utiliser des iframes ?
	 * - les images ne sont pas chargées sauf si défini en CSS
	 *
	 */
var http; // Notre objet XMLHttpRequest
var http_intranet;
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
	
function gestionClic(target)
{
	document.getElementById('nbr_clics').innerHTML = '<em>Chargement...</em>';
	http = createRequestObject();
	http.open('get', target, true);
	http.onreadystatechange = handleAJAXReturn;
	http.send(null);
}
function handleAJAXReturn()
{
    if(http.readyState == 4)
    {
        if(http.status == 200)
        {
            document.body.innerHTML = http.responseText;
        }
        else
        {
            document.body.innerHTML = "<strong>N/A</strong>";
        }
    }
}		
