<?php
/**
 * Qams Framework
 * Copyright (c) 2009-2010 Olivier Appere
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     Qams
 * @author      Olivier Appere
 * @copyright   2009-2010 (c) Olivier Appere
 * @license     http://www.opensource.org/licenses/mit-license.php
 * @link        
 */

/**
 * Handle action
 *
 * @package Qams
 */
class Task extends Action {
	public $duration;
	
	/* This function sorts the life cycle data by various user requests */
	function count_tasks($baseline="",$status="open") {
	    /* Globals */
	    global $order_act;
	    global $show_id;
	    global $show_post;
	    global $show_proj;
	    global $show_rev;
	    global $show_crit;
	    global $show_actions_stat;
	    global $show_equip;
	    global $show_search_action;
 
	    if (($baseline != NULL) && ($baseline != 0)) {
	    	$show_bas = "AND baselines.id = '{$baseline}' ";
	  	}
	  	else {
	  		$show_bas = "";
	  	}	
	  	switch($status){
	  		case "open":
	  		$which_status = " AND actions.status != 9 ";
	  		break;
	  		case "closed":
	  		$which_status = " AND actions.status = 9 ";
	  		break;
	  		case "all":
	  		default:
	  		$which_status = "";
	  		break;
	  	}
	    // cancelled actions are not shown but still exists in the db
	    $sql = "SELECT COUNT(*) as counter ".
			   " FROM projects, lrus, bug_users, bug_status,bug_criticality,actions".
			   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
			   " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
			   " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
			   " WHERE lrus.id = actions.lru ".
			   "AND projects.id = actions.project ".
			   "AND bug_users.id = actions.posted_by ".
			   "AND bug_status.id = actions.status ".
			   "AND bug_criticality.level = actions.criticality ".
			   "AND actions.status != 16 AND actions.criticality = 14 ".
			   $which_status.
			   $show_id.$show_post.$show_equip.$show_proj.$show_rev.$show_crit.$show_bas.$show_actions_stat.$show_search_action."ORDER BY ".$order_act." actions.id ASC";
	    //echo $sql;
	    $result = do_query($sql);
		$nb_actions_tab=mysql_fetch_array($result) ;
		$nb_actions=$nb_actions_tab['counter'];
	    return($nb_actions);
	}	

	function __construct($row) {
        $this->id = $row['id'];
		$this->attendee = $row['fname']." ".$row['lname'];
        $this->attendee_id = $row['posted_by'];
		$this->crit = $row['criticality'];
        $this->status = $row['status'];
        $this->project = $row['project'];
        $this->lru = $row['lru'];
		$this->review = $row['review'];
		$this->description = $row['Description'];
		$this->response = $row['comment'];
		$this->duration = $row['duration'];		
		/* date */
		/* Convert date to display nicely */
	  $date = new Date;
    $this->date_open = $date->convert_date_conviviale ($row['date_open']);
    $this->date_open_dojo = $date->convert_date_to_dojo($row['date_open']);
    $this->date_expected = $date->convert_date_conviviale ($row['date_expected']);
    $this->date_expected_dojo = $date->convert_date_to_dojo($row['date_expected']);
		$this->compute_deadline();
    if ($this->status == "Closed" ) {
  		$this->date_closure = $date->convert_date_conviviale ($row['date_closure']);
  	}
  	else {
      $this->date_closure = "";
	  }
		/* context */
        $this->context = $row['context'];
        if ($this->context == "") {
        	if ($row['review'] !=0) {
	            $query = "SELECT date,managed_by,review_type.type as type ".
	                    "FROM reviews, review_type WHERE reviews.type = review_type.id AND reviews.id = {$row['review']} LIMIT 1";
	            $result_type = do_query($query);
	            //echo $query;
	            $row_type = mysql_fetch_array($result_type);
	            require_once("inc/Date.class.php");
	            $date = new Date;
	            //$date=convert_date($row_type['date']); 
	            $this->context = $row_type['managed_by']." ".$row_type['type']." ".$date->convert_date($row_type['date']);
            }
        }
	}
	function Get_Week ($date_sql) {
				$cut_text   = substr($date_sql,0,10);
				$Jour = date("j", strtotime($cut_text));     
				$Mois = date("m", strtotime($cut_text));  /* 'F' for the complete word */
				$Annee = date("Y", strtotime($cut_text));    /* 'Y' for the complete word */
	            //echo "Test Get_Week:".$Jour."-".$Mois."-".$Annee;
                $week = date("W", mktime(0, 0, 0,$Mois , $Jour, $Annee)); 
				return ($week);
    }
   function Get_Current_Week () {
   	    $today = date("Y").date("m").date("d");
        $week = Task::Get_Week($today);
				return ($week);
    }
	function Get_Days_Of_Week ($leNumSemaineSaisi,$lAnneeSaisie) {
		$jour1erJanvierTS=mktime(0,0,0,1,1,$lAnneeSaisie);
		(date("N",$jour1erJanvierTS)==4) ? $jeudiSemaine1TS=$jour1erJanvierTS : $jeudiSemaine1TS=strtotime("thursday",$jour1erJanvierTS);
		$jeudiSemaineNTS=strtotime("+".($leNumSemaineSaisi-1)." weeks",$jeudiSemaine1TS);
		$lundiTS=strtotime("last monday",$jeudiSemaineNTS);
		$samediTS=strtotime("saturday",$jeudiSemaineNTS);
		$lesJours=array();
		$lesJours["lundi"]=date("d/m/Y",$lundiTS);
		$lesJours["samedi"]=date("d/m/Y",$samediTS);
		$lesJours["timestampLundi"]=$lundiTS;
		$lesJours["lundiPHP"]=date("Y-m-d",$lundiTS);
		$lesJours["samediPHP"]=date("Y-m-d",$samediTS);
		return $lesJours;
	}
	function filter_week($select_week=""){
	    if (($select_week!= NULL) && ($select_week != 0)) {
		$les2Jours=array();
		$les2Jours=Task::Get_Days_Of_Week($select_week,2011);
    	$which_week = "AND ((actions.date_open >= '".$les2Jours['lundiPHP']."') AND (actions.date_open <= '".$les2Jours['samediPHP']."' )) ";
		//echo "Test Week days:".$les2Jours["lundiPHP"]." ".$les2Jours["samediPHP"]."<br>";
		}
		else {
			$which_week = "";
		}
		return ($which_week);
	}	
/* This function creates the form for users to duplicate actions */
function duplicate($id=0,$update="") {
    global $order;
    global $show_id;
    global $userLogID;
	
    $show_lru 				      = $_REQUEST['show_lru'];
    $show_poster 			      = $_REQUEST['show_poster'];
    $show_poster_save       = $show_poster;
    $show_project 			    = $_REQUEST['show_project'];
    $show_status			      = $_REQUEST['show_status'];
    $show_criticality		    = $_REQUEST['show_criticality'];
	  $show_application		    = $_REQUEST['show_application'];
    $show_review      		  = $_REQUEST['show_review'];
	  $remark_id      		    = $_REQUEST['remark_id'];
    $page					          = $_REQUEST['page'];
    $limite					        = $_REQUEST['limite'];
    $project_selected     	= $_REQUEST['project_selected'];
    $task_on_remark       	= $_REQUEST['task_on_action'];
    $task_on_remark_id    	= $_REQUEST['task_on_action_id'];
    $week                   = $_POST['week'];

    $same_page=$_SERVER['PHP_SELF']."?task_on_action_id=".
            $task_on_remark_id."&task_on_action=".
            $task_on_remark;
	  $today_date = date("Y")."-".date("m")."-".date("d");

    if ($id != 0) {
        if ($update == "update") {
            print "<h2>Modify task {$id}</h2>";
        }
        else {
            print "<h2>Copy task {$id}</h2>";
        }
        
        $query = "SELECT * FROM `actions` WHERE `id` ={$id}";

        $result         = do_query($query);
        $row            = mysql_fetch_array($result);
		    $post_action    = new Task($row);
        $status         = $post_action->status;
        $review         = $post_action->review;
        $date_closure 	= $post_action->date_closure_cut;
        $lru_selected   = $post_action->lru;
        $review_selected = $post_action->review;
        $project_selected = $post_action->project;
        $duration = $post_action->duration;
        $date_open      = $post_action->date_open_dojo;
        $date_expected  = $post_action->date_expected_dojo;
		    $show_poster     = $post_action->attendee_id;
    }
    else {
        print "<h2>Post a task</h2>";
		    $default_description = "";
    		if ($remark_id != "") {
      			$item = get_name ("application","bug_applications","id",$show_application);
      			$version = get_name ("version","bug_applications","id",$show_application);
      			$default_description = "Peer data ".$item['application']." issue ".$version['version']." review remark ".$remark_id;
    		}
        $row=array("context"=>"$default_description",
                "Description"=>"",
                "criticality"=>"1");
        $status 	= 8; /* OPEN */
        $date_closure   = "";
        $duration = "";
        $date_open      = $today_date;
        $date_expected  = $date_open;
        $lru_selected = $show_lru;
        $review_selected = $show_review;
		if ($project_selected == "")
			$project_selected = $show_project;
    }
    ?>
<form class='post' name='copy_action' action='post_action.php' method='post'>
    <fieldset>
            <?php
            /* project */
            $sql = "SELECT * FROM projects ORDER BY `projects`.`project` ASC";
            $result_project = do_query($sql);

            print "<label for='project'>Project:</label>";
            print "<select class='no_styled' name='project' style='width:300px'".
                    "onchange='project_selected.innerHTML=document.forms[\"copy_action\"].elements[\"project\"].value;
    document.location=\"".$same_page."&project_selected=\" + this.form.project.value'>";
            print "<option value=''>--All--";
            while ($row_project = mysql_fetch_array($result_project)) {
                print "<option value='{$row_project['id']}'";
                if ($row_project['id'] == $project_selected) {
                    print " SELECTED";
                    $current_description=$row_cat['description'];
                }
                print ">".$row_project['project'];
            }
            print "</select><br />";
            /*
   			* Which LRU
            */
            /* create combo-box selection from lru db table */
            if ($project_selected != "") {
                $where = "WHERE project = ".$project_selected;
            }
            else {
                $where = "";
            }
            $sql = "SELECT * FROM lrus {$where} ORDER BY lrus.lru ASC";
            $result = do_query($sql);
            print "<label for='lru'>Which LRU:</label>";
            print "<select name='lru' style='width:300px'>";
			      print "<option value=''>--All--";
            while ($row_lru = mysql_fetch_array($result)) {
                $id_lru       = $row_lru['id'];
                print "<option value='{$id_lru}'";
                if ($id_lru  == $lru_selected) {
                    print " SELECTED";
                }
                print">{$row_lru['lru']}";
            }
            print "</select><br />";
            print "<label for='duration' >Duration:</label>";
            print '<input type="text" name="duration" size="2" value="'.$duration.'" /><br/>';
                     
            print "<label for='action_type_selected'></label>";
            print "<textarea class='no_show' cols=3 rows=1 id='action_type_selected'  >"."..."."</textarea><br />";

            print "<label for='description'>Action:</label>";
            print '<div style="width:80%;float:left;margin-left:180px;">'.
                    '<textarea class="ckeditor" cols="80" id="editor1" name="description" rows="10">'.$row['Description'].'</textarea></div>';
            //require_once("calendrier/calendrier.php");
            /*
   * Calendar for data opening expected date closure
            */
            ?>
        <div>
        <style>
        textarea {
        	border: none;
        	height: 16px;
			//border-color: black;
			//border-style: solid;
			//border-width: thin;
			//padding: 3px;
		}
		</style>
            <div class="tundra" id="calendar2" style="margin-top:5px;margin-left:200px;width:30%;float:left">
                <div ><h3>Date opening</h3>Week 
				<textarea  name="no_week_open" id="no_week_open" class="no_sho" cols="2" rows="1" style="font-size:large" ></textarea>
            	<script language='javascript' type='text/javascript'>no_week_open.innerHTML=DefSemaineNum(<?php echo $date_open?>)</script>
                <div dojoType="dijit._Calendar" value='<?php echo $date_open ?>' onChange="dojo.byId('date_opening_formatted').innerHTML=dojo.date.locale.format(arguments[0], {formatLength: 'short', selector:'date'});dojo.byId('no_week_open').innerHTML=DefSemaineNum(dojo.date.locale.format(arguments[0], {formatLength: 'short', selector:'date',fullYear:'true'}))">
                </div>
                </div>
            </div>
            <div class="tundra" id="calendar" style="margin-top:5px;margin-left:10px;width:30%;float:left">
                <div >
                <h3>Expected date closure</h3>Week 
                <textarea  name="no_week_close" id="no_week_close" class="no_sho" cols="2" rows="1" style="font-size:large" ></textarea>
                <script language='javascript' type='text/javascript'>no_week_close.innerHTML=DefSemaineNum(<?php echo $date_open?>)</script>
                <div dojoType="dijit._Calendar" value='<?php echo $date_expected ?>' onChange="dojo.byId('formatted').innerHTML=dojo.date.locale.format(arguments[0], {formatLength: 'short', selector:'date'});dojo.byId('no_week_close').innerHTML=DefSemaineNum(dojo.date.locale.format(arguments[0], {formatLength: 'short', selector:'date',fullYear:'true'}))">
                </div>
                </div>
            </div>
            <div style="width:40%;clear:both;">
            </div>
        </div>
        <label for='date_open'></label>
        <textarea cols=11 rows=1 name=date_open id='date_opening_formatted' class='no_show' ></textarea>
        <label for='date_expected'></label>
        <textarea cols=11 rows=1 name=date_expected id='formatted' class='no_show' ></textarea>
        <label for='project_selected'></label>
        <textarea id='project_selected' class='no_show' cols=0 rows=0  name='project_selected' ></textarea><br />
        <span style='margin-left:50px' >
        <span class="art-button-wrapper">
            <span class="l"> </span>
            <span class="r"> </span>
            <label for='button' style='padding: 0 0 0 0'></label></span>
        <input class="art-button" type="submit" value="Post" name="button" />

        <input type=hidden value='task' name=doloop>
        <input type=hidden value='<?php echo $date_closure ?>' name='date_closure' />
        <input type=hidden value='<?php echo $status ?>' name='status' />
        <input type=hidden value='<?php echo $id ?>' name='update_id' />
        <input type=hidden name='isReply' value='<?php echo $update ?>'/>
        <input type=hidden name='username' value='<?php echo $userLogID ?>' />
        <input type=hidden name='show_poster' value='<?php echo $show_poster_save ?>' />
        <input type=hidden name='show_project' value='<?php echo $show_project ?>' />
        <input type=hidden name='show_lru' value='<?php echo $show_lru ?>' />
        <input type=hidden name='show_review' value='<?php echo $show_review ?>' />
        <input type=hidden name='show_criticality' value='<?php echo $show_criticality ?>' />
        <input type=hidden name='criticality' value='<?php echo 14 ?>' />
        <input type=hidden name='search' value='<?php echo $search ?>' />
        <input type=hidden name='show_status' value='<?php echo $show_status ?>' />
        <input type=hidden name='show_id' value='<?php echo $show_id ?>' />
        <input type=hidden name='page' value='<?php echo $page ?>' />
        <input type=hidden name='limite' value='<?php echo $limite ?>' />
        <input type=hidden name='select_week' value='<?php echo $week ?>' />
    </fieldset>
</form>
    <?php

}
/* This function creates the form for users to duplicate actions */
function forward($id=0) {
    global $order;
    global $show_id;
    global $userLogID;
	
    $show_lru 				      = $_REQUEST['show_lru'];
    $show_poster 			      = $_REQUEST['show_poster'];
    $show_poster_save       = $show_poster;
    $show_project 			    = $_REQUEST['show_project'];
    $show_status			      = $_REQUEST['show_status'];
    $show_criticality		    = $_REQUEST['show_criticality'];
	  $show_application		    = $_REQUEST['show_application'];
    $show_review      		  = $_REQUEST['show_review'];
	  $remark_id      		    = $_REQUEST['remark_id'];
    $page					          = $_REQUEST['page'];
    $limite					        = $_REQUEST['limite'];
    $project_selected     	= $_REQUEST['project_selected'];
    $task_on_remark       	= $_REQUEST['task_on_action'];
    $task_on_remark_id    	= $_REQUEST['task_on_action_id'];
    $week                   = $_REQUEST['week'];

    $same_page=$_SERVER['PHP_SELF']."?task_on_action_id=".
            $task_on_remark_id."&task_on_action=".
            $task_on_remark;
	  $today_date = date("Y")."-".date("m")."-".date("d");

    if ($id != 0) {
        print "<h2>Forward task {$id}</h2>";

        $query = "SELECT * FROM `actions` WHERE `id` ={$id}";
        $result           = do_query($query);
        $row              = mysql_fetch_array($result);
		    $post_action      = new Task($row);
    }
    ?>
<form class='post' name='copy_action' action='post_action.php' method='post'>
    <fieldset>
            <input type=hidden name='project' value='<?php echo $post_action->project ?>'/>
            <input type=hidden name='lru' value='<?php echo $post_action->lru ?>'/>
            <input type=hidden name='duration' value='<?php echo $post_action->duration ?>'/>
            <input type=hidden name='description' value='<?php echo $post_action->description ?>'/>
            <input type=hidden name='date_open' value='<?php echo $post_action->date_open_dojo ?>'/>
            <input type=hidden name='date_expected' value='<?php echo $post_action->date_expected_dojo ?>'/>
            <input type=hidden name='description' value='<?php echo $post_action->description ?>'/>
            <div>
            <label for='description'>Action:</label>
            <div style="width:80%;float:left;margin-left:180px;">
            <textarea class="ckeditor" cols="80" id="editor1" name="description" rows="10"><?php echo $post_action->description;?></textarea></div> 
            </div><br/>
            <!-- 
            Previous date : <?php echo $post_action->date_open_dojo ?></br>
            New date : <?php echo $date_open_one_week_later ?></br>
            -->
            <label for='weeks_to_go' >Weeks to go:</label>
            <input type="text" name="weeks_to_go" size="2" value="1" /><br/>
        <span style='margin-left:50px' >
        <span class="art-button-wrapper">
            <span class="l"> </span>
            <span class="r"> </span>
            <label for='button' style='padding: 0 0 0 0'></label></span>
        <input class="art-button" type="submit" value="Post" name="button" />
        
        <input type=hidden value='task' name=doloop>
        <input type=hidden value='<?php echo $post_action->date_closure_cut ?>' name='date_closure' />
        <input type=hidden value='<?php echo $post_action->status ?>' name='status' />
        <input type=hidden value='<?php echo $id ?>' name='update_id' />
        <input type=hidden name='isReply' value=''/>
        <input type=hidden name='username' value='<?php echo $userLogID ?>' />
        <input type=hidden name='show_poster' value='<?php echo $show_poster_save ?>' />
        <input type=hidden name='show_project' value='<?php echo $show_project ?>' />
        <input type=hidden name='show_lru' value='<?php echo $show_lru ?>' />
        <input type=hidden name='show_review' value='<?php echo $show_review ?>' />
        <input type=hidden name='show_criticality' value='<?php echo $show_criticality ?>' />
        <input type=hidden name='criticality' value='<?php echo 14 ?>' />
        <input type=hidden name='search' value='<?php echo $search ?>' />
        <input type=hidden name='show_status' value='<?php echo $show_status ?>' />
        <input type=hidden name='show_id' value='<?php echo $show_id ?>' />
        <input type=hidden name='page' value='<?php echo $page ?>' />
        <input type=hidden name='limite' value='<?php echo $limite ?>' />
        <input type=hidden name='select_week' value='<?php echo $week ?>' />
    </fieldset>
</form>
    <?php

}
function db_update() {
        $project            = $_REQUEST['project'];
        $context            = $_REQUEST['context'];
        $lru                = $_REQUEST['lru'];
        $review             = $_REQUEST['review'];
        $who                = $_REQUEST['username'];
        $description        = $_REQUEST['description'];
        $criticality        = $_REQUEST['criticality'];
        $status             = $_REQUEST['status'];
        $date_closure       = $_REQUEST['date_closure'];
        $date_dojo_open     = $_REQUEST['date_open'];
        $date_dojo_expected = $_REQUEST['date_expected'];
        $isReply            = $_REQUEST['isReply'];
        $update_id          = $_REQUEST['update_id'];
        $duration           = $_REQUEST['duration'];
        $weeks_to_go         = $_POST['weeks_to_go'];

        /* Convert date from dojo format to sql format */
        if ($weeks_to_go != "") {
            $days_to_go = $weeks_to_go * 7;
            $date_expected =strftime("%Y-%m-%d", strtotime($date_dojo_expected." + ".$days_to_go." day"));
            $date_open    = strftime("%Y-%m-%d", strtotime($date_dojo_open." +".$days_to_go." day"));
        }
        else {
            $date_expected = convert_dojo_date($date_dojo_expected);
            $date_open = convert_dojo_date($date_dojo_open);
        }
        if ($isReply == "update") {
            $update_messages2 = "UPDATE `actions` SET `project`='$project' , `review`='$review' ,".
                    "`context`='$context' , `LRU`='$lru' , `posted_by`='$who' , `Description`='$description' , ".
                    "`date_open`='$date_open' ,  `date_closure`='$date_closure' , `duration`='$duration' ,".
                    "`criticality`='$criticality' , `status`='$status' , `date_expected`='$date_expected' WHERE `id` = '$update_id' LIMIT 1";
            //echo $update_messages2;
            $update_action_result = mysql_query($update_messages2);
            if ($update_action_result) {
                $param=restore_context_param();
                //echo "test:".$param;
                print("<script language='javascript' type='text/javascript'>document.location='tasks.php?{$param}'</script>");
            }
            else {
                $errorCode = mysql_error();
                print("Fuck it!:<br>Error = $errorCode");
            }
        }
        else {
            $status = 8; /* OPEN */
            $query = "INSERT INTO `actions`".
                    " (`project`, `context`, `LRU`, `review`, `posted_by`, `Description`, `criticality`, `status`, `date_expected`, `date_open`, `duration`)".
                    " VALUES('$project', '$context', '$lru','$review', '$who', '$description', '$criticality', '$status', '$date_expected', '$date_open','$duration')";
            //echo "test:".$query."</br>";
            //$update_result_messages = 0;
            $update_result_messages = mysql_query($query);
            $updateReply = mysql_insert_id();

            if ($update_result_messages) {
                print("<br>action {$description} has been successfully added!");

                if($isClose != "yes") {
                    print("Post complete!");
                    $param=restore_context_param();
                    print("<script language='javascript' type='text/javascript'>document.location='tasks.php?{$param}'</script>");
                }
                else {
                    $query = "UPDATE `actions` SET `status`=$status, `comment`=$comment WHERE `id` = '$updateReply' ";
                    $update_result_messages2 = mysql_query($query);

                    if ($update_result_messages2) {
                        print("Closure of action complete!");
                        $param=restore_context_param();
                        print("<script language='javascript' type='text/javascript'>document.location='actions2.php?{$param}'</script>");
                    }
                }
            }

            else {
                $errorCode = mysql_error();
                print("Fuck it!:<br>Error = $errorCode");
                print("???");
            }
        }
    }	
  function close($id) {
    global $userLogID;

    $sql = "SELECT comment,date_open FROM actions WHERE id=$id";
    $result = do_query($sql);
    $row = mysql_fetch_array($result);
    $date_open = $row['date_open'];
    if ($row['comment'] == "") {
        $reply_action_text = "Task performed.";
    }
    else {
        $reply_action_text = $row['comment'];

    }
    print "<form action=post_action.php?{$_SERVER['QUERY_STRING']} method=post>";
    print "<fieldset>";

    print "<label for='comment'>Action</label>".
            "<textarea cols=70 rows=5 name='comment' style='margin-top:20px'>{$reply_action_text}</textarea><br />";
    /* Calendar */
    /* Compute current date to be stored in database */
    $date_closure="CURRENT_TIMESTAMP";
    print "<label for='formatted'>Date closure:</label>";
    /* Compute current date to be stored in database */
    print '<div class="tundra" id="calendar"">';
    print "<div style='width:30%'>";
    print "<div dojoType=\"dijit._Calendar\" onChange=\"dojo.byId('formatted').innerHTML=dojo.date.locale.format(arguments[0], {formatLength: 'short', selector:'date'})\">";
    print '</div>';
    print '</div>';
    print '</div><br />';
    print "<label for='button'></label>";
    print '<span class="art-button-wrapper" style="margin-left:500px" >';
    print '<span class="l"> </span>';
    print '<span class="r"> </span>';
    print '<input class="art-button" type="submit" name="button" value="Close"/>';
    print '</span>';
    print "<textarea cols=11 rows=1 name='date_closure' id='formatted' class='no_show' >".$a."-".$m."-".date("d")."</textarea>";
    print "<input type=hidden value='task_close' name=doloop>";
    print "<input type=hidden value=".$date_open." name=date_open>";
    print "<input type=hidden value='9' name='status'>"; /* Closed */
    print "<input type=hidden name=id_action_to_be_closed value=$id>";
    print "<input type=hidden name=isClose value='yes'>";
    print "</fieldset>";
    print "</form>";
}
function db_close() {

        $date_dojo_closure 	  = $_REQUEST['date_closure'];
        $date_closure = convert_dojo_date ($date_dojo_closure);
        $comment	 	  = $_REQUEST['comment'];
        $status 		  = $_REQUEST['status'];
        $date_open 		  = $_REQUEST['date_open'];
        $close_id 		  = $_REQUEST['id_action_to_be_closed'];
        $update_messages2 = "UPDATE `actions` SET `status`='$status', `date_open`='$date_open' , `date_closure`='$date_closure' , `comment`='$comment' WHERE `id` = '$close_id' ";
        //echo $update_messages2;
        $update_result_messages2 = mysql_query($update_messages2);

        if ($update_result_messages2) {
            print("Closure of action complete!");
            $param=restore_context_param();
            print("<script language='javascript' type='text/javascript'>document.location='tasks.php?{$param}'</script>");
        }
    }    
}
/* This function sorts the life cycle data by various user requests */
function sort_tasks($select_week="") {
    /* Globals */
    global $order_act;
    global $show_id;
    global $show_post;
    global $show_proj;
    global $show_rev;
    global $show_crit;
    global $show_actions_stat;
    global $show_equip;
    global $show_search_action;
    
  	$which_week = Task::filter_week($select_week);	
    // cancelled actions are not shown but still exists in the db
    $sql = "SELECT actions.duration,actions.comment,review,actions.id,posted_by,context,actions.Description,".
	       " projects.project, lrus.lru, fname, lname, bug_criticality.name as criticality,bug_status.name as status,date_open,date_expected,date_closure".
		   " FROM actions".
		   " LEFT OUTER JOIN reviews ON actions.review = reviews.id ".
		   " LEFT OUTER JOIN baseline_join_review ON baseline_join_review.review_id = reviews.id ".
		   " LEFT OUTER JOIN baselines ON baseline_join_review.baseline_id = baselines.id ".
		   " LEFT OUTER JOIN bug_users ON bug_users.id = actions.posted_by ".
		   " LEFT OUTER JOIN projects ON projects.id = actions.project ".
		   " LEFT OUTER JOIN bug_status ON bug_status.id = actions.status ".
		   " LEFT OUTER JOIN bug_criticality ON bug_criticality.level = actions.criticality ".
		   " LEFT OUTER JOIN lrus ON lrus.id = actions.lru ".
		   " WHERE actions.status != 16 AND actions.criticality = 14 ".$which_week.$show_id.$show_post.$show_equip.$show_proj.$show_rev.$show_crit.$show_bas.$show_actions_stat.$show_search_action."ORDER BY ".$order_act." id ASC";
    //echo $sql."<br>";
    return($sql);
}

?>
