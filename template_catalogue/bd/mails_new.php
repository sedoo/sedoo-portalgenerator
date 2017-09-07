<?php

require_once("bd/dataset.php");
require_once("mail.php");
require_once("ldap/ldapConnect.php");
require_once("sortie/fiche2pdf_functions.php");
require_once("/sites/kernel/#MainProject/conf.php");


class mails {

	static function test(){
		echo "Test OK.";
	}

	static function sendMailUser($user, $jeu, $project = MainProject, $sendMailPis = true, $from = ROOT_EMAIL, $cc = ROOT_EMAIL){
		mails::sendMailUser2($user, array($jeu), $sendMailPis, $from, $cc, $project);
	}

	static function sendMailUser2($user, $jeux, $sendMailPis = true, $from = ROOT_EMAIL, $cc = ROOT_EMAIL, $project = MainProject){
		$sujet = "$project Data Access";


		$corps = "Dear database user,\n\nYou downloaded data corresponding to the following dataset(s):";

		$fichesPdf = array();

		foreach ($jeux as $jeu){
			$fichePdf =	fiche2pdf($jeu->dats_id,true);
			$fichesPdf[] = $fichePdf;
				
			//echo $fichePdf;
			
			$corps .= "\n\n* ".$jeu->dats_title;
			$pisList = '';
			$cptPis = 0;
			$contactsList = '';
			foreach ($jeu->originators as $pi){
				$contact = "\n  ".ucwords(strtolower($pi->pers_name)).' ('.$pi->organism->getName().'), email: '.$pi->pers_email_1;
				if ($pi->isPI()){
					$pisList .= $contact;
					$cptPis++;
					if ($sendMailPis)
						mails::sendMailPi($pi->pers_email_1,$jeu->dats_title,$user,$from,$cc,$project);
				}else{
					$contactsList .= $contact;
					//$cptContacts++;
				}
			}

			if ( !empty($pisList) ){
				if ($cptPis > 1)
					$corps .= "\nThe PIs of this dataset are:";
				else
					$corps .= "\nThe PI of this dataset is:";

				$corps .= $pisList."\nWe remind you that you are expected to contact him (them) in order to propose collaboration.";
				if ( !empty($contactsList) ){
					$corps .= "\nYou can also contact:".$contactsList;
				}
			}else{
				$corps .= "\nContact(s):"
				.$contactsList;
			}

			if ( isset($jeu->dats_use_constraints) && ! empty($jeu->dats_use_constraints) )
				$corps .= "\nUse constraints:\n  ".$jeu->dats_use_constraints;
		}

		$corps .= "\n\nRegards,\n"
		."The $project database service";
				
		sendMail($user->mail,$sujet,$corps,$fichesPdf,$from);
		sendMail($cc,"[$project-DATABASE] Data download",'Sent to '.$user->mail."\n\n".$corps,$fichesPdf,$from);
	}

	static function sendMailPi($mail, $jeuNom, $user, $from = ROOT_EMAIL, $cc = ROOT_EMAIL, $project = MainProject){
				global $MainProjects;
                $sujet = "$project Data Access";

                $corps = "Dear PI,\n\n"
                        ."Data corresponding to the dataset '$jeuNom' have been asked and received by the following user:\n\n"
                        .ucwords(strtolower($user->cn)).' ('.$user->mail.')';

                if (isset($user->abstract) && !empty($user->abstract)){
                        $corps .= "\nPlanned Work: ".$user->abstract;
                }
                foreach($MainProjects as $pro){
                if(isset($user->attrs[strtolower($pro).'Abstract'][0]) && $user->attrs[strtolower($pro).'Abstract'][0] != $user->abstract)
                	 $corps .= "\n".$user->attrs[strtolower($pro).'Abstract'][0];
                }

                $corps .= "\n\nYou are pleased to inform him/her of any evolution in the dataset."
			."\n\nRegards,\n"
                        ."The $project database service";
                sendMailSimple($mail,$sujet,$corps,$from);
                //Copie
                sendMailSimple($cc,"[$project-DATABASE] Mail Pi","Sent to $mail\n\n".$corps,$from);
        }

}

?>
