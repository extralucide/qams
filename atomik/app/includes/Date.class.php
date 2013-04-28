<?php
class Date {
	private static  $today_date;
	public static  function nbDays($debut, $fin) {
	  // $tDeb = explode("-", $debut);
	  // $tFin = explode("-", $fin);

	  // $diff = mktime(0, 0, 0, $tFin[1], $tFin[2], $tFin[0]) - 
			  // mktime(0, 0, 0, $tDeb[1], $tDeb[2], $tDeb[0]);
	  // $delta = ($diff / 86400)+1;
	  $delta = round((strtotime($fin) - strtotime($debut))/(60*60*24));
	  return($delta);
	}	
	public static function SmallDate($QuelleDate)
	  {
		  $NomJour = date("D", strtotime($QuelleDate));  /* 'l' for the complete word */
		  $Jour = date("j", strtotime($QuelleDate));     
		  $Mois = date("M", strtotime($QuelleDate));  /* 'F' for the complete word */
		  $Annee = date("y", strtotime($QuelleDate));    /* 'Y' for the complete word */
		  return $Jour." ".$Mois." ".$Annee;
	  }
	public static function DateConviviale($QuelleDate)
	  {
		  $NomJour = date("D", strtotime($QuelleDate));  /* 'l' for the complete word */
		  $Jour = date("j", strtotime($QuelleDate));     
		  $NomMois = date("M", strtotime($QuelleDate));  /* 'F' for the complete word */
		  $Annee = date("y", strtotime($QuelleDate));    /* 'Y' for the complete word */
		  return $NomJour." ".$Jour." ".$NomMois." ".$Annee;
	  }
	public static function PrettyDate($QuelleDate)
	  {
		  $NomJour = date("l", strtotime($QuelleDate));  /* 'l' for the complete word */
		  $Jour = date("j", strtotime($QuelleDate));
		  $NomMois = date("F", strtotime($QuelleDate));  /* 'F' for the complete word */
		  $Annee = date("Y", strtotime($QuelleDate));    /* 'Y' for the complete word */
		  return $NomJour." ".$Jour." ".$NomMois." ".$Annee;
	  }
	public static function convert_date($date_sql)  {
		/* Convert date to display nicely */
	    $cut_text   = substr($date_sql,0,10);
		if ($cut_text != "0000-00-00") {
			$date = self::PrettyDate ($cut_text);
		}
		else {
			$date ="undefined";
		}
	    return ($date);
	}	
	public static function convert_date_conviviale($date_sql)  {
		/* Convert date to display nicely */
		if (($date_sql != "0000-00-00")&& ($date_sql != "")) {
			$cut_text   = substr($date_sql,0,10);
			$date       = self::DateConviviale ($cut_text);
		}
		else {
			$date="undefined";
		}
	    return ($date);
	}
	public static function convert_date_small($date_sql)  {
		/* Convert date to display nicely */
	    $cut_text   = substr($date_sql,0,10);
	    $date       = self::SmallDate ($cut_text);
	    return ($date);
	}	
   public static function convert_date_to_dojo ($date_sql) {

    $date_dojo   = substr($date_sql,0,10);
    return ($date_dojo);
  }  	
  public static function convert_dojo_date ($date_dojo) {
    /* Convert date from dojo format to sql format */
    $month = strtok($date_dojo,'/');
    $day = strtok('/');
    $year = strtok('/');

    $sql_date = "20".$year."-".sprintf("%1$02d",$month)."-".sprintf("%1$02d",$day);
    return ($sql_date);
	} 
  public static function new_convert_dojo_date ($date_dojo) {
    /* Convert date from dojo format to sql format */
    $month = strtok($date_dojo,'/');
    $day = strtok('/');
    $year = strtok('/');

    $sql_date = $year."-".sprintf("%1$02d",$month)."-".sprintf("%1$02d",$day);
    return ($sql_date);
	} 	
  public static function align_date_end(&$date_start_sql,&$date_end_sql) {
	/* date of the end of the meeting should be after startof the meeting */
	if (strtotime($date_end_sql) < strtotime($date_start_sql)) {
	//if (strcmp($date_end_sql,$date_start_sql) >= 0) {
		$date_end_sql = $date_start_sql;
	}	
  }
  public static function compute_expired($date){
	$color[] = array();
	if (strtotime($date) < strtotime(date("Y-m-d"))){
		$color_claire='pastel_red';
	}	
	else {
		$color_claire='pastel_grey';
	}
	/* get darker color */
	$color_part = explode("_", $color_claire);
	if (isset($color_part[1])) {
		$color_fonce = $color_part[1];
	}
	else {
		$color_fonce = $color;
	}	
	$color[0] = $color_claire;
	$color[1] = $color_fonce;
	return($color);
  }
  public static function getTodayDate(){
	return(date("Y-m-d"));
	/* return(self::$today_date); */
  }
  function __construct(){
	self::$today_date = date("Y-m-d");
  }
}
