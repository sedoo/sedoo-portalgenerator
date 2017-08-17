package org.sedoo.mistrals.extract.fichiers;

import java.text.SimpleDateFormat;

import org.apache.log4j.Logger;
import org.medias.utils.mail.MailSimple;
import org.sedoo.mistrals.extract.Props;
import org.sedoo.mistrals.extract.ReponseXml;
import org.sedoo.mistrals.extract.utils.Contact;
import org.sedoo.mistrals.extract.utils.ContactsJeu;
import org.sedoo.mistrals.extract.utils.FichierResultat;
import org.sedoo.utils.log.LogUtils;

public final class Mailer {

private static Logger logger = Logger.getLogger(Mailer.class);
	
	private static SimpleDateFormat myFormat = new SimpleDateFormat("dd/MM/yyyy hh:mm");
	
	private Mailer(){}
	
	/**
	 * Mail à envoyer en cas de succès de la commande de fichiers.
	 * @param requete
	 * @param reponse
	 */
	public static void sendMailOk(RequeteXmlFichiers requete, ReponseXml reponse){
		logger.info("sendMailOk()");
		logger.info("reponse: " + reponse.getURL());
		
		String subject = "[" + reponse.getProjectName() + "-DATABASE] Data download";
		String body = "Dear database user,\n\n" +
				"Your Request to SGBD " + reponse.getProjectName() + " sent on " + myFormat.format(requete.getDateDeb()) +
				" has successfully ended on " + myFormat.format(requete.getDateFin()) + ".\n\n" +
				"The results of this request are available at the following address: " + reponse.getURL() + "\n\n" +
				"To minimize the space used by the results, they will only be stored during 2 weeks.\n\n" +
				"The results contain data from the following dataset:\n";

		boolean piTrouve = false;
		
		for (FichierResultat fichier: reponse.getFichiers()){
			for (ContactsJeu contacts: fichier.getContacts()){
				body += "\n*" + contacts.getJeu().getDatsTitle();
				body += "\n  Metadata: " + Props.HTTP_DOWNLOAD_UI + "?datsId=" + contacts.getJeu().getDatsId();
								
				for (Contact contact: contacts.getContacts()){
					body += "\n  " + contact.toString();
					if (contact.getType().contains("PI")){
						piTrouve = true;
					}
				}
				
				if (contacts.getJeu().getUseConstraints() != null){
					body += "\n  Use constraints:\n    " + contacts.getJeu().getUseConstraints();
				}
			}
		}

		if (piTrouve){
			body += "\n\nWe remind you that you are expected to contact the PI(s) of the data in order to propose collaboration.";
		}
		
		body += "\n\nRegards,\nThe " + reponse.getProjectName() + " database service";

		MailSimple mail= new MailSimple(Props.MAIL_SMTPHOST,"UTF-8");
		
		try{
			logger.debug("envoi mails");
			mail.send(Props.MAIL_FROM,reponse.getUser().getMail(),subject,body);
			logger.info("mail envoyé à " + reponse.getUser().getMail());
			String headerAdmin = "Sent to " + reponse.getUser().getMail() + "\n\n";
			mail.send(Props.MAIL_FROM,Props.MAIL_ADMIN,subject,headerAdmin + body);
		}catch (Exception e){
			logger.debug("pb dans send mail");
			LogUtils.logException(logger,e);
		}
		
	}
	
	/**
	 * Mail à envoyer à l'utilisateur en cas d'erreur lors de la commande.
	 * @param e
	 * @param requete
	 * @param reponse
	 */
	public static void sendMailErreur(Exception e,RequeteXmlFichiers requete, ReponseXml reponse){
		logger.debug("sendMailErreur()");
	}
	
}
