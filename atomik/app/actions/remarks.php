<?php session_start();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<script type="text/javascript" src="script.js"></script>
<!--  <link rel="stylesheet" href="style.css" type="text/css" media="screen" /> -->
<link rel="stylesheet" type="text/css" href="tundra.css" />
<link rel="stylesheet" href="style_div.css" type="text/css" media="screen" />
<link rel="stylesheet" href="form.css" type="text/css" media="screen" />
<link rel="stylesheet" type="text/css" href="atomik/assets/css/main.css" />
<link rel="stylesheet" type="text/css" href="atomik/assets/css/home.css" />
<style type="text/css">
		.tundra table.dijitCalendarContainer { margin: 25px auto; } #formatted
		{ text-align: center; }
</style>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script src="ckeditor/_samples/sample.js" type="text/javascript"></script>
<!--[if IE 6]><link rel="stylesheet" href="style.ie6.css" type="text/css" media="screen" /><![endif]-->
<!--[if IE 7]><link rel="stylesheet" href="style.ie7.css" type="text/css" media="screen" /><![endif]-->
<script type="text/javascript" src="script.js"></script>
<script type="text/javascript" src="includes/JSfunctions.js"></script>
<script type="text/javascript" src="includes/dojo/dojo.js" djConfig="parseOnLoad: true , locale: &#39;en&#39;"></script>
<script type="text/javascript" src="includes/NCleanGrey_standard.js"></script>
<script type="text/javascript">
	dojo.require("dijit.dijit"); // loads the optimized dijit layer
	dojo.require("dijit._Calendar");
</script>
<?php
$tab_select = "inspection";
include "inc/Db.class.php";
include "menu_generic.php";
include "inc/Remark.class.php";
include "inc/Data.class.php";
include "inc/Baseline.class.php";
include "inc/PeerReviewer.class.php";
include "inc/Project.class.php";
include "inc/User.class.php";
?>
<?php $param="page={$page}&limite={$limite}&show_application={$show_application}&show_project={$show_project}&show_lru={$show_lru}&show_status={$show_status}&show_poster={$show_poster}&show_category={$show_category}&show_type={$show_type}"; ?>
<script type="text/javascript" src="includes/remarks.js"></script>
</head>
<body>
<div style="width:1124px">
<div class="default_left" style="width:280px;min-height:800px;">
<h3 class="inspection" >Inspections</h3>
<span style="color:#FFF">Reset filter
<a href="<?php echo $_SERVER['PHP_SELF'];?>" >
<img border=0 src='images/silk_icons/arrow_rotate_clockwise.png' alt="reset" title='reset' />
</a>
<?php
/* select project */
show_project();
/* select project */
show_lru();
/* select baseline */
show_baseline();
show_application();
show_type();
show_poster();
show_category ();
show_criticality ();
show_status();
$current_date = date("Y")."-".date("m")."-".date("d");
$filter_param = 		"&show_project=".$show_project.
						"&show_application=".$show_application.
						"&show_lru=".$show_lru.
						"&show_status=".$show_status.
						"&show_poster=".$show_poster.
						"&show_category=".$show_category.
						"&show_type=".$show_type.
						"&show_baseline=".$show_baseline.
						"&search=".$search.
						"&page=".$page.
						"&limite=".$limite;
if ($show_project != "") {
    $where = " AND project = ".$show_project;
}
else {
    $where = "";
}
$sql = "SELECT id,project,Description as description FROM actions WHERE criticality != 14 {$where}";
$result = do_query($sql);
?>
<!--
<form method="POST" name="multi_modify_action_id" action="" >
<label for='lru'>Action ID:</label>
<select name='action_id' style='width:300px'>
<option value=''>--All--
<?php
while ($row_action = mysql_fetch_object($result)) {
    print "<option value='{$row_action->id}'";
    if ($row_action->project  == $show_project) {
        print " SELECTED";
    }
    print">".$row_action->id.":".substr($row_action->description,0,60);
}
?>  
</select><br />        
</form>
<div class="my_menu" style="margin-left:-40px;padding-bottom:30px">
<ul>
      <li class="action"><h2><a href="" onClick="return get_checkbox_value_for_link_action()">Link action</a></h2></li>
</ul>
</div>
-->
<!--
<h3 class="edit" >Modify peer reviews</h3>
<form method="post" name="multi_modify_data" id="multi_modify_data" action="" onSubmit="return get_checkbox_value()">
<fieldset class="medium">
<h4>Date of publication</h4>
<div class="tundra" style="color:#000;width:280px;">
	<div dojoType="dijit._Calendar" value="<?php echo $current_date ?>" onChange="dojo.byId('remark_date2').innerHTML=dojo.date.locale.format(arguments[0], {formatLength: 'short', selector:'date'})">
</div></div>
 	<label for='remark_date2'></label>
  	<textarea  name="remark_date2" id="remark_date2" class="no_show" cols="10" rows="1" ><?php echo $current_date?></textarea>
<div class="my_menu">
<a href="#" ><img alt="Apply modifications" title="Apply modifications" border=0 src="images/32x32/antivirus.png" class='img_button'
   onclick="return get_checkbox_value()" />
</a>
<a href="#" ><img alt="Remove" title="Remove" border=0 src="images/64x64/db_remove.png" class='img_button'
   onclick="return delete_checkbox_value()" />
</a>
<a href="post2.php?order=application&<?php echo $param;?>" ><img alt="Post" title="Post" border=0 src="images/64x64/db_add.png" class='img_button'/>
</a> 
<a href="#" ><img alt="Duplicate or modify" title="Duplicate or modify" border=0 src="images/48x48/Printers-Faxes-icon.png" class='img_button'
   onclick="return duplicate_radio_value()" />
</a>
</div>
</fieldset>
</form>
-->
<!-- <div class="spacer" ></div>-->
</div>
<div style="width:824px;float:right" >
<p style="max-width:200px"><a href="post2.php?application=<?php echo $show_application?>&show_project=<?php echo $show_project;?>" style="text-decoration: none;outline-width: medium;outline-style: none;" title="Add a remark"><img src="images/newobject.gif" class="systemicon" alt="Add a remark" title="Add a remark" border="no" /> Add a remark</a></p>
<div id="page_tabs" style="padding-top:5px;padding-left:5px">
<div id="remarks_tab" class="active">Remarks</div>
<div id="summary_tab" >Stats</div>
<div id="import_tab" >Import</div>
<div id="export_tab" >Export</div>
</div>
<div class="clearb"></div>
<div id="remarks_tab_c">
<?php
$sql = Remark::get_remarks();
//echo $sql."<BR>";
$result = do_query($sql);
/* amount of rows */
$nbtotal=mysql_num_rows($result);
if ($nbtotal != 0){ 						
	/* Computes nb of pages */
	$nbpage = ceil($nbtotal / $limite);
	if ($page > $nbpage) {
		/* on rectifie la page courante pour qu'elle 
		   ne depasse pas le nombre de pages */
		$page = $nbpage;
	}
    $line = 0;
    $debut=($page-1)*$limite; // $debut à partir de quel enregistrement commence la selection dans notre cas si $page=1 $debut=0 / si $page=2 $debut=(2-1)*3 = 3
    $query = $sql." LIMIT ".$debut.",".$limite;

    $result = do_query($query);
    $today_date = date("Y")."-".date("m")."-".date("d");
    $param = restore_context_param ();
    ?>
    <!-- radio button form -->
    <!-- <form name="orderform"> -->
    <!--  start of the table -->
    <table class="art-article pagetable" style="width:820px;table-layout: fixed;">
    <thead><tr class='vert'>
    <?php
	/* ici, si on clique sur Id on fait un tri */
    $header_fields = array("Id","Date","Poster", "Data","","Description","","","","Category", "Status","Action ID","" );
    foreach( $header_fields as $value ){?>
       	<th><?php echo $value; }?></th>
    </tr>
    </thead>
    <tbody>
    <?php // on balaye les lignes par groupe
    while($row = mysql_fetch_array($result)) {
		$remark = new Remark ($row);
        if ($line++ % 2 == 0) {
            $line_color = 'rouge';
        }
        else {
            $line_color = 'vert';
        }
	?>
	<tr class="<?php echo $line_color;?>">
        <td style="width:20px"><a href="post2.php?show_application=<?php echo $show_application;?>&<?php echo $filter_param;?>&task_on_action=duplicate&task_on_action_id=<?php echo $remark->id;?>&modify_remark=checked"><?php echo $remark->id;?></a>
	<input type='checkbox' class='styled' name='modify_action_id' value="<?php echo $remark->id;?>"  /></td>
    	
	<td ><?php echo $remark->small_date; ?></td>
	<td style="word-wrap:break-word;max-width:20px" ><?php echo $remark->poster; ?></td>
	<td colspan="2" class="reference_click" style="word-wrap: break-word;max-width:60px;overflow : hidden;"><a href="data_cycle2.php?show_application=<?php echo $show_application;?>&show_project=<?php echo $show_project;?>"><?php echo $remark->reference; ?></a></td>
	<td colspan="4" class="subject_click_" style="word-wrap: break-word;max-width:50px"><b><?php echo $remark->paragraph; ?></b> <?php echo $remark->remark; ?></td>
	<td ><?php echo $remark->category; ?></td>
	<!-- <td ><?php echo $remark->criticality; ?></td> -->
	<!-- <td ><?php echo $remark->justification; ?></td> -->
	<td class="<?php echo $remark->color_status?>" ><a class="remark_status_click" href="review/select_remarks_status.php?remark_id=<?php echo $remark->id ?>&status_id=<?php echo $remark->status_id ?>" TARGET="popup" onClick="ouvrir(this.href,this.target);return false">
    <?php echo $remark->status; ?>
	</a>
    </td>
	<td class="<?php echo $remark->color_action ?>"><a href="post_action.php?task_on_action=edit&task_on_action_id=<?php echo $remark->action_id ?>"><?php echo $remark->action_id ?><a></td>
	<td style="max-width:16px;width:16px"><a>
        <span class='down_arrow' onClick="return openMenu2(<?php echo $remark->id;?>,'reply_<?php echo $remark->id;?>',this)">
        </span>
        </a></td>
        <!-- the class of this line must be menu to be hidden -->
        <tr class='menu' id="<?php echo $remark->id;?>" style='background:#C4C4C4' >
        <td colspan="13" style="border: none;">
        <div id="bande_gauche" >
		<div class="my_menu">
		<ul>
			<li class="write"><h2><a href="post2.php?application=<?php echo $show_application;?>&<?php echo $filter_param;?>&modify_remark=no_checked">Add remark</a></h2></li>
			<li class="write"><h2><a href="post2.php?show_application=<?php echo $show_application;?>&<?php echo $filter_param;?>&task_on_action=duplicate&task_on_action_id=<?php echo $remark->id;?>&modify_remark=no_checked">Copy remark</a></h2></li>
			<li class="edit"><h2><a href="post2.php?show_application=<?php echo $show_application;?>&<?php echo $filter_param;?>&task_on_action=duplicate&task_on_action_id=<?php echo $remark->id;?>&modify_remark=checked">Edit remark</a></h2></li>
			<li class="action"><h2><a href="post_action.php?remark_id=<?php echo $remark->id;?>&origin=bug_list2&order=id&show_application=<?php echo $show_application?>&<?php echo $filter_param;?>" >Open an action</a></h2></li>
			<li class="delete"><h2><a href="post2.php?<?php echo $filter_param;?>&task_on_action=delete&task_on_action_id=<?php echo $remark->id;?>" >Delete remark</a></h2></li>
		</ul>
		</div>
		</div>
		<div class="spacer"></div>
		<div class="contenue midsize" style="margin-left:20px">
		<h1><u>Text of the remark</u></h1>
        <div class="art-BlockContent">
                      <div class="art-BlockContent-tl"></div>
                      <div class="art-BlockContent-tr"></div>
                      <div class="art-BlockContent-bl"></div>
                      <div class="art-BlockContent-br"></div>
                      <div class="art-BlockContent-tc"></div>
                      <div class="art-BlockContent-bc"></div>
                      <div class="art-BlockContent-cl"></div>
                      <div class="art-BlockContent-cr"></div>
                      <div class="art-BlockContent-cc"></div>
                      <div class="art-BlockContent-body">
                      </div>
	<blockquote><h4>Posted by <?php echo $remark->poster;?> on <?php echo $remark->date;?></h4>
	<p><?php echo $remark->see_remark;?></p><div class="spacer"></div>
	</blockquote>
        <div class='indent'>

    </div></div>
		<h1><u>Justification</u></h1>
        <div class="art-BlockContent">
                      <div class="art-BlockContent-tl"></div>
                      <div class="art-BlockContent-tr"></div>
                      <div class="art-BlockContent-bl"></div>
                      <div class="art-BlockContent-br"></div>
                      <div class="art-BlockContent-tc"></div>
                      <div class="art-BlockContent-bc"></div>
                      <div class="art-BlockContent-cl"></div>
                      <div class="art-BlockContent-cr"></div>
                      <div class="art-BlockContent-cc"></div>
                      <div class="art-BlockContent-body">
                      </div>
	<blockquote>
	<p><?php echo $remark->justification;;?></p><div class="spacer"></div>
	</blockquote>
        <div class='indent'>

    </div></div>
    <div class="reply_under">
	<h1><u>Replies to the remark</u></h1>
	<div style='width:800px;padding:10px;font-size:10pt;' >
	<?php
	$response_list = array();
	$nb_response = $remark->get_response(&$response_list);
	//if ($nb_response > 0) {
	?>
        <a href="javascript:alert('No reply so far !')" onClick="return hideMenu('reply_<?php echo $remark->reply_id;?>')" class='art-button'>
	Show/Hide Replies</a>
		<div class="menu_" id="reply_<?php echo $remark->id;?>" style="background:#D1D1D1;">
		<?php 
		foreach( $response_list as $response ){
		?>
		<div class="art-BlockContent">
			  <div class="art-BlockContent-tl"></div>
			  <div class="art-BlockContent-tr"></div>
			  <div class="art-BlockContent-bl"></div>
			  <div class="art-BlockContent-br"></div>
			  <div class="art-BlockContent-tc"></div>
			  <div class="art-BlockContent-bc"></div>
			  <div class="art-BlockContent-cl"></div>
			  <div class="art-BlockContent-cr"></div>
			  <div class="art-BlockContent-cc"></div>
			  <div class="art-BlockContent-body">
			  </div>
				<blockquote>
				<p><?php echo $response;?></p><div class="spacer"></div>
				</blockquote>
				<div class='indent'></div>		
		</div>
		<?php
		}
		?>
	</div>
	</div>
	<h1><u>Add a response</u></h1>
     <form class='_post' action="post2.php?show_application=<?php echo $show_application;?><?php echo $filter_param;?>" method="post">
     <fieldset class="medium">
	 <?php select_status($remark->status_id,"peer review");?>
	 <?php select_poster($userLogID);?>
    <label for='reply'></label><br />
			<div class="art-BlockContent">
			  <div class="art-BlockContent-tl"></div>
			  <div class="art-BlockContent-tr"></div>
			  <div class="art-BlockContent-bl"></div>
			  <div class="art-BlockContent-br"></div>
			  <div class="art-BlockContent-tc"></div>
			  <div class="art-BlockContent-bc"></div>
			  <div class="art-BlockContent-cl"></div>
			  <div class="art-BlockContent-cr"></div>
			  <div class="art-BlockContent-cc"></div>
			  <div class="art-BlockContent-body">
			  </div>
				<blockquote>
				<textarea style="width:600px;" cols="100" id="reply" name="description" rows="10"></textarea><br />
				</blockquote>
				<div class='indent'></div>		
		</div>
    

    <input type="hidden" name="name" 		value="<?php echo $userLogID ?>"/>
    <input type="hidden" name="doloop" 		value="yes"/>
    <input type="hidden" name="replyValue"  value="<?php echo $remark->id ?>"/>
    <input type="hidden" name="application" value="<?php echo $remark->reference?>"/>
	<input type="hidden" name="date"	    value="<?php echo $today_date ?>"/>
    <input type="hidden" name="criticality" value="<?php echo $remark->criticality?>"/>
    <input type="hidden" name="category"    value="<?php echo $remark->category?>"/>
	<input type="hidden" name="isReply"     value="yes"/>
    <input class='art-button' type='submit' name="submit_post" value="Reply"/>
    </fieldset></form>
		<h1><u>Action linked</u></h1>
		<?php
		if ($show_project != "") {
			$where = " AND project = ".$show_project;
		}
		else {
			$where = "";
		}
		$sql = "SELECT id,project,Description as description FROM actions WHERE criticality != 14 {$where}";
		$result_action = do_query($sql);
		?>
		<form method="POST" name="multi_modify_action_id" action="action/set_link_action_remark.php?multi_modify=yes" >
		<label for='lru'></label>
		<select name='action_id' style='width:600px'>
		<option value=''>--All--
		<?php
		while ($row_action = mysql_fetch_object($result_action)) {
			print "<option value='{$row_action->id}'";
			if ($row_action->id  == $remark->action_id) {
				print " SELECTED";
			}
			print">".$row_action->id.":".substr($row_action->description,0,60)."[...] etc ...";
		}
		?>  
		</select><br /> 
		<input type="hidden" name="multiple_data_id"    value="<?php echo $remark->id?>;"/>		
        <span class="art-button-wrapper">
            <span class="l"> </span>
            <span class="r"> </span>
            <label for='button' style='padding: 0 0 0 0'></label>
			<input class="art-button" type="submit" value="Link action" name="link_action" />
		</span>			
		</form>	
	</div>
	</div>
        </td>
        </tr>
<?php
    } //ends while loop ?>
    <tr><td style="width:60px"><input type='checkbox' class='no_styled' name='checkall' value="Check All" onClick='test();'/></td>
    <td colspan="10"></td></tr>
    </tbody>
    </table>
<?php	
}
else {
    echo "no remarks !<br/>";
	?>
	<a href="data_cycle2.php?show_application=<?php echo $show_application;?><?php echo $filter_param;?>">Go to data view</a>
    <?php
	$nbpage = 1;
    $nbtotal = 0;
    //display_remarks($sql);
}?>	
</div>
<div id="summary_tab_c" >
        <!-- </form> -->
   <div class="contenu" style="margin-left_:280px">
		<h2>Peer Review Register summary</h2>
		<?php
		if ($show_application == "") {
			echo "Please select a data in the menu on the left side of the screen to compute statistics.";
		}
		else {
		$remarks = new StatRemarks($remark->data_id,
									$remark->ref,
									$remark->version);
		$peer_reviewers = new PeerReviewer($remark->data_id,
											$remark->ref,
											$remark->version);
		?>
		<p>The amount of remarks is <b><?php echo $remarks->amount_remarks; ?></b></p>
		<div id="peer_review_content">
		<div id="peer_review_sidebar">
		<?php if ($remarks->amount_remarks > 0) {?>	
		<img class="pie" src="bar.php?data=<?php echo $remarks->name_serial?>&nb_remarks=<?php echo $remarks->nb_serial?>" alt="Type of remarks bar chart"/>
		<?php  }?>	
		<?php if ($peer_reviewers->nb > 0) {?>
		<img class="pie" src="data/pie.php?data=<?php echo $peer_reviewers->name_serial?>&nb_remarks=<?php echo $peer_reviewers->nb_serial?>" alt="Peer reviewer pie chart"/>	
		<?php  }?>
		</div>		
		<div id="peer_review_center">
		<!-- <h2>Remarks type</h2> -->
		<table class="art-article">
		<thead>
		<tr class='vert'>
		<th align="left">Type</th><th>nb</th>
		</tr>
		</thead>
		<tbody>
		<?php 
        if ($remarks->amount_remarks > 0){
          foreach ($remarks->remark_tab as $name => $amount) {
			?>
        	 <tr><td><?php echo $name; ?></td><td><?php echo $amount; ?></td></tr>
			<?php
          }
        }
        ?>		
		</tbody>
		</table>
        <h2>Peer reviewers</h2>
        <table class="art-article">
		<thead>
		<tr class='vert'>
		<th>Peer reviewer</th><th>Function</th><th>Nb</th>
		</tr>
		</thead>
		<tbody>
        <?php       
        //print_r($peer_reviewers->peer_reviewer_nb_tab);
        if ($peer_reviewers->index_peer_reviewer > 0){
          foreach ($peer_reviewers->peer_reviewer_tab as $name => $function) {?>
        	 <tr><td><?php echo $name; ?></td><td><?php echo $function; ?></td><td><?php echo $peer_reviewers->peer_reviewer_nb_tab[$name]; ?></td></tr>
          <?php
          }
        }
        ?>
        </tbody>
		</table>
		</div>
		</div>
		<!-- </div> -->
    <?php
	}
?>
<span id="prr_table"></span>
</div><div class="spacer" ></div>
</div>
<div id="import_tab_c" >
<h3 class="edit"style="color:#000" >Import Peer Review Register</h3>
<form class="post_" id="import_prr" name="import_prr" method="post" action="peer_review/treat_reply_prr.php?show_application=<?php echo $show_application;?>&show_poster=<?php echo $show_poster;?>" enctype="multipart/form-data">   
<fieldset class="medium_">	
    <input type=hidden name='show_project' 		value='<?php echo $show_project ?>'/>
    <input type=hidden name='show_lru' 			value='<?php echo $show_lru ?>'/>
    <input type=hidden name='show_poster' 		value='<?php echo $show_poster ?>'/>
    <input type=hidden name='show_type' 		value='<?php echo $show_type ?>'/>
    <input type=hidden name='show_category' 	value='<?php echo $show_category ?>'/>
    <input type=hidden name='show_criticality' 	value='<?php echo $show_criticality ?>'/>
    <input type=hidden name='search' 			value='<?php echo $search ?>'/>
    <input type=hidden name='show_status' 		value='<?php echo $show_status ?>'/>
    <input type=hidden name='show_id' 			value='<?php echo $show_id ?>'/>
    <input type=hidden name='show_application' 	value='<?php echo $show_application ?>'/>
    <input type=hidden name='show_baseline' 	value='<?php echo $show_baseline ?>'/>
    <input type=hidden name='page' 				value='<?php echo $page ?>'/>
    <input type=hidden name='limite' 			value='<?php echo $limite ?>'/>
    <input type="hidden" name="MAX_FILE_SIZE" value="2097152"/>
	<label for='filename'>Filename</label>
    <input class="no_file" type="file" name="filename" id="filename"/><br/>
	<label for="type_remark">Type </label>
  	<select class='combobox' name='type_remark' id="type_remark">
    <option value="">--All--</option>
    <option value="1" selected="selected" >ECE</option>
    <option value="2">Eurocopter</option>
    <option value="3">Airbus</option>
    </select><br/>
       <div> 
	  <!-- <label for='preview'>Preview</label>
         <input name="preview" id="preview" type="checkbox"  checked /></div>
	  <div><label for='author_response'>Update response</label>
         <input name="author_response" id="author_response" type="checkbox" checked /></div> -->
         <div class="spacer" ></div>
	   <!-- <a href="atomik/index.php?action=proof_readings">Import Airbus Remarks</a> -->
	   <h4>Date of publication</h4>
<div class="tundra" style="color:#000;width:280px;">
	<div dojoType="dijit._Calendar" value="<?php echo $current_date ?>" onChange="dojo.byId('remark_date').innerHTML=dojo.date.locale.format(arguments[0], {formatLength: 'short', selector:'date'})">
</div></div>
 	<label for='remark_date'></label>
  	<textarea  name="remark_date" id="remark_date" class="no_show" cols="10" rows="1" ><?php echo $current_date?></textarea>
<!--	
<div class="my_menu" style="margin-left:-20px;">
<ul>
      <li class="import"><h2><a href="#" onclick="import_prr.submit()">Import PRR</a></h2></li>
</ul>
</div>
-->
<span class="art-button-wrapper">
<span class="l"> </span>
<span class="r"> </span>
<input class="art-button" type="submit" name="submit" value="Import PRR"/>
</span>
</fieldset>
</form>
</div>
<div id="export_tab_c" >
<div style="width:350px">
<h3 class="edit" style="color:#000">Export Peer Review Register</h3> 
<div class="my_menu" >
<ul>
      <li class="export_pdf" style="float:none;"><h2><a href="export_remarks_pdf_tcpdf.php?order=application<?php echo $filter_param;?>" >Export PDF</a></h2></li>
      <li class="export_excel"><h2><a href="export_xlsx_peer_review_report.php?order=application<?php echo $filter_param;?>" >Export Excel</a></h2></li>
</ul>
</div>
</div><div class="spacer" ></div>
</div>
<script type="text/javascript">
cms_page_tab_style();
</script>
</div>
<?php
/* get page and limit user selection*/
include "includes/change_page.php";
include "includes/pages.php";
?> 
</div> <!-- content -->
<?php include "includes/footer.php"; ?>
</body>
</html>
