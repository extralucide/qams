<?php
function cb_rtrim ( &$str ) {

    $str = rtrim($str);

}

$postArray = &$_POST ;

$rule = array(
        'project_id' => array('required' => true),
        'lru_id' => array('required' => true)
);

/* on ne peut pas utiliser cette fonction filter car elle supprime les balises html */
if (($data = Atomik::filter($_POST, $rule)) === false) {
    Atomik::flash(A('app/filters/messages'), 'error');
    return;
}

foreach ( $postArray as $sForm => $value ) {
//    if ( get_magic_quotes_gpc() )
//        $data[$sForm] = stripslashes( $value );
//    else
    $data[$sForm] = $value;
    //echo "field:".$sForm."=>".$value."<br />";
}
$nom_fichier = $_FILES["import_file_spr"]["tmp_name"];
//echo $nom_fichier;
if (is_uploaded_file($nom_fichier)) {
    // le fichier existe, on l'ouvre
    $fp = fopen($nom_fichier,  "r");
    // discard first line which is table header
    $ligne = fgets($fp,4096);
    while (!feof($fp))  // On parcours le fichier
    {
        $ligne = fgets($fp,4096);  // On se d�place d'une ligne
        list($cr_id,$synopsis,$description,$impact_analysis,$severity,$status) = explode(";",$ligne);  // Champs s�par�s par |
        $description=str_replace ("§" , "'§",$description);
        $impact_analysis=str_replace ("§" , "'§",$impact_analysis);
        $cr_id=str_replace ("ECE3#" , " ",$cr_id);
        /*
         * This gets the id of severity
        */
        cb_rtrim (&$severity); // remove space
        $severity_db = Atomik_Db::findAll('bug_criticality','name = "'.$severity.'"',null,null,'level');
        if (!($severity_db)) {
            print "Could not establish severity id";
            $severity=0;
        }
        else {
            $severity=$severity_db[0]['level'];
        }
        /*
         * This gets the id of status 
        */
        cb_rtrim (&$status); // remove space
        $status_db = Atomik_Db::findAll('spr_status','status = "'.$status.'"',null,null,'id');
        if (!($status_db)) {
            print "Could not establish status id";
            $status=0;
        }
        else {
            $status=$status_db[0]['id'];
        }
        //echo $cr_id." ".$synopsis." ".$description." ".$impact_analysis." ".$severity." ".$status."<br/>";

        /* insert SPR in DB */
        $check_spr_existence = Atomik_Db::has('sprs','cr_id = '.$cr_id);
        if (($cr_id != "") && !($check_spr_existence)) {
            //echo "add in DB SPR: ".$cr_id."<br />";
            /* CR ID is not an empty string and CR ID is new in the DB */
            $data['cr_id'] = $cr_id;
            $data['synopsis'] = $synopsis;
            $data['description'] = html_entity_decode ($description,ENT_QUOTES,"UTF-8");
            $data['impact_analysis'] = html_entity_decode ($impact_analysis,ENT_QUOTES,"UTF-8");
            $data['severity_id'] = $severity;
            $data['status_id'] = $status;
            $true_cr_id = $cr_id;
//            foreach ( $data as $sForm => $value ) {
//                echo $sForm." = ".$value."<br />";
//            }
            //echo $data;
            //break;
            /* complete impact analysis and description */
            while (!feof($fp))  // On parcours le fichier
            {
                // Où en sommes-nous ?
                $fp_handle = ftell($fp);
                $ligne = fgets($fp,4096);  // On se d�place d'une ligne
                list($cr_id,$synopsis,$description,$impact_analysis,$severity,$status) = explode(";",$ligne);  // Champs s�par�s par |
                $description=str_replace ("§" , "'§",$description);
                $impact_analysis=str_replace ("§" , "'§",$impact_analysis);
                $cr_id=str_replace ("#" , " ",$cr_id);
                if ($cr_id == "") {
                    if ($description != "")
                        $data['description'].= html_entity_decode ($description,ENT_QUOTES,"UTF-8")."<br/>";
                    if ($impact_analysis != "")
                        $data['impact_analysis'].= html_entity_decode ($impact_analysis,ENT_QUOTES,"UTF-8")."<br/>";
                }
                else {
                    /* Next SPR */
                    fseek($fp, $fp_handle);
                    break;

                }
            }
            $insert_result = Atomik_Db::set('sprs', $data);//,'cr_id = '.$true_cr_id);
            if ($insert_result != false) {
                echo "add in DB SPR: ".$cr_id."<br />";
                //Atomik::flash('Baseline successfully added!', 'success');
            }
            else
                Atomik::flash('SPR not added!', 'failed');
        }
    }
}
else                                   // sinon error
{
    echo  "Fichier introuvable <br>";
    exit();
}
//if $data['description'] =
//$data['maj'] = date('Y-m-d h:i:s');
//$insert_result = Atomik_Db::insert('baseline_join_review', $data);
//if ($insert_result)
//    Atomik::flash('Baseline successfully added!', 'success');
//else
//    Atomik::flash('Baseline not added!', 'failed');
//Atomik::redirect('show_spr');
