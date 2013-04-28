function checkAll(){
	 for (var i=0; i < document.orderform.data_check.length; i++)
	 {
		 if (document.orderform.data_check[i].checked) {
			document.orderform.data_check[i].checked = false;
		 }
		 else{
		 	document.orderform.data_check[i].checked = true;
		 }
	 }	
}
function create_query (action)
{
     /* var poster       = document.orderform.show_poster.value;
     var project      = document.orderform.show_project.value;
     var equipment    = document.orderform.show_lru.value;
     var review       = document.orderform.show_review.value;
     var criticality  = document.orderform.show_criticality.value;
     var status       = document.orderform.show_status.value;
	 var order_action = document.orderform.order_action.value;
     var page         = document.orderform.page.value;
     var limite       = document.orderform.limite.value;
	 var context      = document.orderform.context.value;*/
     var my_orderform = document.orderform;
     var query_string;
	 
	 query_string = "post_action?task_on_action="+action;
	 /* if (my_orderform.context.value != undefined) {
		query_string = query_string + "&context=" + context;
	 }*/

   query_string = query_string + "&task_on_action_id=";
   return query_string;
}

function close_action(action_id){
    var query_string;
	query_string = "comment_action?id="+action_id;
	document.location = query_string;
}

function action_radio_value(action)
{
   var my_query = create_query (action);
   var agree_delete;
   if (action == "close"){
	agree_delete=true;
	my_query = "comment_action?id=";
   }	
   else if (action == "delete")
   	agree_delete=confirm("Are you sure you want to delete this action ?");
   else
  	agree_delete=true;
   if (agree_delete) {
  	 if ((document.orderform.modify_action_id.length != undefined) && 
  	     (document.orderform.modify_action_id.length > 1) && 
  		 (document.orderform.modify_action_id.length != null)) {
  		  for (var i=0; i < document.orderform.modify_action_id.length; i++)
  		  {
  			 if (document.orderform.modify_action_id[i].checked) {
  				var rad_val_multi = document.orderform.modify_action_id[i].value;
  				document.location=my_query + rad_val_multi;
  			  }
  		  }
  	 }
  	 else {
  		/* case for a form with 1 element */
  		if (document.orderform.modify_action_id.checked) {
  			var rad_val_one = document.orderform.modify_action_id.value;
  			document.location=my_query + rad_val_one;
  		  }
  	 }
   }
   else {
     return false;
  }
}
function ActionExportRun() {
	document.getElementById("messages").innerHTML = "<li class=\"warning\" style=\"list-style-type: none;\">Export in progress ...</li>";
	document.getElementById("export_actions_list_frame").style.display = 'block';	
	return true;
}	
function ActionExportEnd(sError) {
	if(sError == 'OK') {
			document.getElementById("messages").innerHTML = "<li class=\"success\" style=\"list-style-type: none;\">Export succeeded.</li>";
	} else {
			document.getElementById("messages").innerHTML = sError;
	}
}
function display_action_comment (menu,arrow)
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
/******************************************************
FONCTION QUI DETERMINE LE NUMERO DE LA SEMAINE EN COURS
 ******************************************************/
function DefSemaineNum(mm_jj_aaaa)
{
	//initialisation des variables
	//----------------------------
	var dojo_date;
	var aaaa;
	var mm;
	var jj;
	var MaDate;         //date a traiter
	var annee;          //année de la date à traiter
	var NumSemaine;     //numéro de la semaine

	dojo_date   = mm_jj_aaaa.split('/');
	aaaa        = parseInt(dojo_date[2]);
	mm          = parseInt(dojo_date[0]) - 1;
	jj          = parseInt(dojo_date[1]);
	MaDate      = new Date(aaaa,mm,jj);//date a traiter
	annee       = MaDate.getFullYear();//année de la date à traiter
	NumSemaine  = 0;
	//
	// calcul du nombre de jours écoulés entre le 1er janvier et la date à traiter.
	// ----------------------------------------------------------------------------
	// initialisation d'un tableau avec le nombre de jours pour chaque mois
	ListeMois = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
	// si l'année est bissextile alors le mois de février vaut 29 jours
	if (annee %4 == 0 && annee %100 !=0 || annee %400 == 0) {ListeMois[1]=29};
	// on parcours tous les mois précédants le mois à traiter
	// et on calcul le nombre de jour écoulé depuis le 1er janvier dans TotalJour
	var TotalJour=0;
	for(cpt=0; cpt<mm; cpt++){
		TotalJour+=ListeMois[cpt];
	}
	TotalJour += jj;
	//var var_debug = TotalJour;
	//Calcul du nombre de jours de la première semaine de l'année à retrancher de TotalJour
	//-------------------------------------------------------------------------------------
	//on initialise dans DebutAn le 1er janvier de l'année à traiter
	DebutAn = new Date(annee,0,1);
	//on determine ensuite le jour correspondant au 1er janvier
	//de 1 pour un lundi à 7 pour un dimanche/
	var JourDebutAn;
	JourDebutAn=DebutAn.getDay();
	if(JourDebutAn==0){JourDebutAn=7};

	//Calcul du numéro de semaine
	//----------------------------------------------------------------------
	//on retire du TotalJour le nombre de jours que dure la première semaine
	TotalJour-=8-JourDebutAn;
	//on comptabilise cette première semaine
	//NumSemaine = 1;
	//on ajoute le nombre de semaine compléte (sans tenir compte des jours restants)
	NumSemaine+=Math.floor(TotalJour/7);
	// s'il y a un reste alors le n° de semaine est incrémenté de 1
	if(TotalJour%7!=0){NumSemaine+=1};

	return(NumSemaine);
	//return(TotalJour);
}