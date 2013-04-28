<?php
function cb_rtrim ( &$str ) {

    $str = rtrim($str);

}

$postArray = &$_POST ;

$rule = array(
        'application' => array('required' => true),
        'posted_by' => array('required' => true)
);

/* on ne peut pas utiliser cette fonction filter car elle supprime les balises html */
if (($data_tmp = Atomik::filter($_POST, $rule)) === false) {
    Atomik::flash(A('app/filters/messages'), 'error');
    return;
}

//foreach ( $postArray as $sForm => $value ) {
//    if ( get_magic_quotes_gpc() )
//        $data[$sForm] = stripslashes( $value );
//    else
//$data[$sForm] = $value;
//echo "field:".$sForm."=>".$value."<br />";
//}
$nom_fichier = $_FILES["import_proof_readings"]["tmp_name"];
//echo $nom_fichier;
echo '<div style="background-color:#FFF">';
if (is_uploaded_file($nom_fichier)) {
    // le fichier existe, on l'ouvre
    //$handle = fopen($nom_fichier,  "r");
    echo "fichier trouvé<br/>";
    $row = 1;
     ini_set('auto_detect_line_endings', true);
    if (($handle = fopen($nom_fichier, "r")) !== FALSE) {
        while (($data_csv = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $num = count($data_csv);
            //for ($c=0; $c < $num; $c++) {
            //    echo $data_csv[$c] . " : ";
            //}
            //echo "<br />\n";
            echo "lecture de ".$num." champs <br/>";
            if (11 == 11) {
            //if ($num == 11) {
                $rem_no = $data_csv[0];
                $reader = $data_csv[1];
                $chapter = $data_csv[2];
                $sub_chapter = $data_csv[3];
                $observations= $data_csv[4];
                $criticality = $data_csv[5];
                $decision = $data_csv[6];
                $comment = $data_csv[7];
                $closing = $data_csv[8];
                $final_decision = $data_csv[9];
                $category = "";
                $data['application'] = $data_tmp['application'];
                $data['posted_by'] = $data_tmp['posted_by'];
                $data['category'] = $category;
                switch ($criticality) {
                    case "M":
                    case "Maj":
                        $data['criticality'] = 13; /* Major */
                        break;
                    case "m":
                    case "min":
                        $data['criticality'] = 1; /* Minor */
                        break;
                    default:
                        $data['criticality'] = 13; /* Major */
                        break;
                }
                //echo $decision."<br/>";
                switch ($decision) {
                    case "OK":
                        $data['status'] = 42; /* OK */
                        break;
                    case "No action":
                        $data['status'] = 43; /* Mo action */
                        break;
                    default:
                        $data['status'] = 2; /* To be reviewed */
                        break;
                }
                $data['subject'] = "";
                $data['description'] = "R".$rem_no.":".$observations;
                $data['paragraph'] = $chapter;
                $data['line'] = $sub_chapter;

                echo $rem_no."  ";
                foreach ( $data as $sForm => $value ) {
                    echo $sForm." = ".$value."   ";
                }
                echo "<br />";
                if ($rem_no != "") {
                    $insert_remark_id = Atomik_Db::set('bug_messages', $data);
                    //$insert_remark_id = false;
                    if ($insert_remark_id != false) {
                        echo "add in DB remark R".$rem_no."<br />";
                        $reply_id['reply_id'] = $insert_remark_id;
                        $update_reply_id = Atomik_Db::update('bug_messages', $reply_id,'id ='.$insert_remark_id);
                        if ($comment != "") {
                            $reply['description'] = $comment." Closing: ".$closing;
                            $reply['reply_id'] = $insert_remark_id;
                            $reply['posted_by'] = 22; /* ECE */
                            $add_reply_id = Atomik_Db::insert('bug_messages', $reply,'id ='.$insert_remark_id);
                        }
                        //Atomik::flash('Baseline successfully added!', 'success');
                    }
                    else
                        Atomik::flash('Remark R'.$rem_no.'not added!', 'failed');
                }
            }
        }
    }
    // discard first line which is table header
//    $ligne = fgets($fp,4096);
//    while (!feof($fp))  // On parcours le fichier
//    {
//        $ligne = fgets($fp,4096);  // On se d�place d'une ligne
//        $ligne_trimmed = trim ($ligne,"\n");
//        echo $ligne_trimmed;
//        $ligne=str_replace ("\"" , " ",$ligne);
//        $ligne=str_replace ("&nbsp" , " ",$ligne);
//        //$ligne=str_replace ("?" , " ",$ligne);
//        $ligne=strip_tags ($ligne);
//        list($rem_no,$reader,$chapter,$sub_chapter,$observations,$criticality,$decision,$comment,$closing,$final_decision) = explode(";",$ligne);  // Champs s�par�s par |
//        $chapter=str_replace ("§" , "'§",$chapter);
//        $sub_chapter=str_replace ("§" , "'§",$sub_chapter);
//        /* This gets the id of severity */
//        cb_rtrim (&$final_decision); // remove space
//
//        /* insert SPR in DB */
//        //$check_spr_existence = Atomik_Db::has('sprs','cr_id = '.$cr_id);
//        //if (($cr_id != "") && !($check_spr_existence)) {
//
//        // $data['posted_by']
//        //$data['application']
//        $data['category'] = "";
//        //echo $criticality.":";
//        switch ($criticality) {
//            case "'M'":
//            case "'Maj'":
//                $data['criticality'] = 13; /* Major */
//                break;
//            case "'m'":
//            case "'min'":
//                $data['criticality'] = 1; /* Minor */
//                break;
//            default:
//                $data['criticality'] = 13; /* Major */
//                break;
//        }
//        //echo $decision."<br/>";
//        switch ($decision) {
//            case "'OK'":
//                $data['status'] = 42; /* OK */
//                break;
//            case "'No action'":
//                $data['status'] = 43; /* Mo action */
//                break;
//            default:
//                $data['status'] = 2; /* To be reviewed */
//                break;
//        }
//        $data['subject'] = "Airbus proof reading";
//        $data['description'] = $observations;
//        $data['paragraph'] = $chapter;
//        $data['line'] = $sub_chapter;
//        foreach ( $data as $sForm => $value ) {
//            //echo $sForm." = ".$value."   ";
//        }
//        echo "<br />";
//        //break;
////            $insert_result = Atomik_Db::set('bug_messages', $data);
////            if ($insert_result != false) {
////                echo "add in DB remarks: ".$cr_id."<br />";
////                //Atomik::flash('Baseline successfully added!', 'success');
////            }
////            else
////                Atomik::flash('SPR not added!', 'failed');
//        //}
//    }
    fclose($handle);
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
echo '</div>';