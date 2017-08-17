<?php

include('http://mistrals.sedoo.fr/squelettes/test/datasetSimple.inc');
include('http://mistrals.sedoo.fr/squelettes/test/userSimple.inc');
include('http://mistrals.sedoo.fr/squelettes/test/mail.inc');

define('MAIL_SUPPORT_IPSL','HyMeX.Data-Support@ipsl.polytechnique.fr');
define('ROOT_EMAIL','mistralsdb-admin@sedoo.fr');

class mails{

static function sendMailSat($mail, $dats_id, $dats_title,
                $pi_name,$pi_organism, $pi_mail, $is_pi = true,
                $dats_use_constraints = null){

        $jeu = new datasetSimple;
        $jeu->dats_id = $dats_id;
        $jeu->dats_title = $dats_title;
        $jeu->dats_use_constraints = $dats_use_constraints;
        $jeu->originators = array();
        $pi = new userSimple;
        $pi->mail = $pi_mail;
        $pi->cn = $pi_name;
        $pi->organism = $pi_organism;
        $pi->pi = $is_pi;

        $jeu->originators[] = $pi;

        $user = new userSimple;
        $user->mail = $mail;
        $user->cn = 'Nom complet';

        //mails::sendMailUser($user,$jeu,true,ROOT_EMAIL,MAIL_SUPPORT_IPSL);
	mails::sendMailUser($user,$jeu);
}

static function sendMailUser($user, $jeu, $sendMailPis = true, $from = ROOT_EMAIL, $cc = ROOT_EMAIL){
        $sujet = 'HyMeX Data Access';
        $corps = "Dear database user,\n\nYou just asked for data corresponding to the dataset '".$jeu->dats_title."'.";
        $pisList = '';
        $cptPis = 0;
        $contactsList = '';
        foreach ($jeu->originators as $pi){
                $contact = "\n  ".ucwords(strtolower($pi->cn)).' ('.$pi->organism.'), email: '.$pi->mail;
                if ($pi->isPI()){
                       $pisList .= $contact;
                       $cptPis++;
                       if ($sendMailPis)
                            mails::sendMailPi($pi->mail,$jeu,$user,$from,$cc);
                }else{
                       $contactsList .= $contact;
                }
        }

        if ( !empty($pisList) ){
                if ($cptPis > 1)
                       $corps .= "\n\nThe PIs of this dataset are:";
                else
                       $corps .= "\n\nThe PI of this dataset is:";

                         //$corps .= "\n\nThe PI(s) of this dataset are:"
                        //      .$pisList;
                $corps .= $pisList."\n\nWe remind you that you are expected to contact him (them) in order to propose collaboration.";
                if ( !empty($contactsList) ){
                        $corps .= "\n\nYou can also contact:".$contactsList;
                }
        }else{
                $corps .= "\n\nContact(s):"
                        .$contactsList;
        }

        if ( isset($jeu->dats_use_constraints) && ! empty($jeu->dats_use_constraints) )
                $corps .= "\n\nUse constraints:\n  ".$jeu->dats_use_constraints;

        $corps .= "\n\nRegards,\n"
                .'The HyMeX database service';

        sendMailSimple($user->mail,$sujet,$corps,$from);
        //Copie
        sendMailSimple($cc,'[HyMeX-DATABASE] Data download','Sent to '.$user->mail."\n\n".$corps,$from);

}

        static function sendMailPi($mail, $jeu, $user, $from = ROOT_EMAIL, $cc = ROOT_EMAIL){
                $sujet = 'HyMeX Data Access';

                $corps = "Dear PI,\n\n"
                        .'Data corresponding to the dataset \''.$jeu->dats_title."' have been asked and received by the following user:\n\n"
                        .ucwords(strtolower($user->cn)).' ('.$user->mail.')';

                if (isset($user->abstract) && !empty($user->abstract)){
                        $corps .= "\nPlanned Work: ".$user->abstract;
                }

                $corps .= "\n\nYou are pleased to inform him/her of any evolution in the dataset."
                        ."\n\nRegards,\n"
                        .'The HyMeX database service';
                sendMailSimple($mail,$sujet,$corps,$from);
                //Copie
                sendMailSimple($cc,'[HyMeX-DATABASE] Mail Pi',"Sent to $mail\n\n".$corps,$from);
        }


}

?>
