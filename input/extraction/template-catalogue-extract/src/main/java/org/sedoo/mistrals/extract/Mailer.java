package org.sedoo.mistrals.extract;

import java.text.SimpleDateFormat;
import java.util.HashSet;
import java.util.Set;

import org.apache.log4j.Logger;
import org.medias.utils.mail.MailSimple;
import org.sedoo.mistrals.bd.beans.Requete;
import org.sedoo.mistrals.extract.utils.Contact;
import org.sedoo.mistrals.extract.utils.ContactsJeu;
import org.sedoo.mistrals.extract.utils.FichierResultat;
import org.sedoo.utils.log.LogUtils;

public final class Mailer {
	private static Logger logger = Logger.getLogger(Mailer.class);
	
	private static SimpleDateFormat myFormat = new SimpleDateFormat("dd/MM/yyyy hh:mm");
	
	private Mailer(){}
	
	
	
	/**
	 * Mail à envoyer en cas de succès de l'extraction.
	 * @param requete
	 * @param reponse
	 */
	public static void sendMailOk(Requete requete, ReponseXml reponse){
		logger.debug("sendMailOk()");
		logger.debug("reponse: " + reponse.getURL());
				
		String subject = "[" + reponse.getProjectName() + "-DATABASE] Data download";
		String body = "Dear database user,\n\n" +
				"Your Request to SGBD " + reponse.getProjectName() + " sent on " + myFormat.format(requete.getDateDeb()) + " with the number " + requete.getRequeteId() +
				" has successfully ended on " + myFormat.format(requete.getDateFin()) + ".\n\n" +
				"The results of this request are available at the following address: " + reponse.getURL() + "\n\n" +
				"To minimize the space used by the results, they will only be stored during 2 weeks.\n\n" +
				"The results contain data from the following datasets:\n";
						
		Set<Integer> jeux = new HashSet<Integer>();
		
		boolean piTrouve = false;
		
		for (FichierResultat fichier: reponse.getFichiers()){
			if (fichier.getType() == FichierResultat.TYPE_DATA){
				for (ContactsJeu contacts: fichier.getContacts()){
					if (!jeux.contains(contacts.getJeu().getDatsId())){
						body += "\n*" + contacts.getJeu().getDatsTitle();
						body += "\n  Metadata: " + Props.HTTP_DOWNLOAD_UI + "?datsId=" + contacts.getJeu().getDatsId();
						jeux.add(contacts.getJeu().getDatsId());
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
			logger.debug("mail envoyé à " + reponse.getUser().getMail());
			String headerAdmin = "Sent to " + reponse.getUser().getMail() + "\n\n";
			mail.send(Props.MAIL_FROM,Props.MAIL_ADMIN,subject,headerAdmin + body);
		}catch (Exception e){
			logger.debug("pb dans send mail");
			LogUtils.logException(logger,e);
		}
		
	}
	
	/**
	 * Mail à envoyer si aucune donnée n'a été trouvée.
	 * @param requete
	 * @param reponse
	 */
	public static void sendMailEmptyResult(Requete requete, ReponseXml reponse){
		logger.debug("sendMailEmptyResult()");
		String subject = "[" + reponse.getProjectName() + "-DATABASE] Data download";
		String body = "Dear database user,\n\n" +
			"No data is corresponding to the request sent on " + myFormat.format(requete.getDateDeb()) + " with the number " + requete.getRequeteId() + ".\n\n" +
			"Don't hesitate to contact us ("+Props.MAIL_ADMIN+") in case of any question." +
			"\n\nRegards,\nThe " + reponse.getProjectName() + " database service";
		
		
		MailSimple mail= new MailSimple(Props.MAIL_SMTPHOST);
		try{
			logger.debug("envoi mails");
			mail.send(Props.MAIL_FROM,reponse.getUser().getMail(),subject,body);
			logger.debug("mail envoyé à " + reponse.getUser().getMail());
			String headerAdmin = "Sent to " + reponse.getUser().getMail() + "\n\n";
			mail.send(Props.MAIL_FROM,Props.MAIL_ADMIN,subject,headerAdmin + body);
		}catch (Exception e){
			logger.debug("pb dans send mail");
			LogUtils.logException(logger,e);
		}
		
		
	}
	
	/**
	 * Mail à envoyer à l'utilisateur en cas d'erreur lors de l'extraction.
	 * @param e
	 * @param requete
	 * @param reponse
	 */
	public static void sendMailErreur(Exception e,Requete requete, ReponseXml reponse){
		logger.debug("sendMailErreur()");
		
		MailSimple mail = new MailSimple(Props.MAIL_SMTPHOST);
		String mailContent = "Dear database user,\n\n" +
		"Your Request (id: " + requete.getRequeteId() + ") was not processed due to technical reasons.\n\n" +
		"Please contact the database administrator " +
		"("+Props.MAIL_ADMIN+").\n\n" +
		"Regards,\n" +
		"The " + reponse.getProjectName() + " database service";
		
		logger.debug("caracteristiques requete");
		logger.debug("requete email : " +  requete.getMail());
		logger.debug("requete date deb : " + requete.getDateDeb());
		
		String mailAdministrateur = "Erreur survenue dans une requete mistrals : \n" +
		"utilisateur : " + requete.getMail() + "\n" +
		"date requete : " + requete.getDateDeb() + "\n" +
		"Exception : \n" + e.getMessage();
		logger.debug("impression stack trace");
		StackTraceElement[] stack = e.getStackTrace();
		logger.debug("impression stack trace");
		for (int i = 0 ; i< stack.length ; i++){
			mailAdministrateur += "\n\t" + stack[i].getClassName() + " : " + stack[i].getMethodName() +
			" (line " + stack[i].getLineNumber() + ")";
		}
		
		String subject = "[" + reponse.getProjectName() + "-DATABASE] Error";
		
		try{
			logger.debug("envoi mails");
			logger.debug("Envoi d'un mail d'erreur à " + requete.getMail());
			mail.send(Props.MAIL_FROM,requete.getMail(),subject,mailContent);
			mail.send(Props.MAIL_FROM,Props.MAIL_ADMIN,subject,mailAdministrateur);
		}catch (Exception ex){
			logger.debug("pb dans send mail");
			LogUtils.logException(logger,e);
		}
	}
	
	/**
	 * Mail à envoyer à l'administrateur si une erreur est survenue au démarrage de l'extracteur.
	 * @param e exception rencontrée
	 */
	public static void sendMailFatal(Exception e, String requete){
		logger.debug("envoi mail fatal");
		MailSimple mail= new MailSimple(Props.MAIL_SMTPHOST);
		String subject = Props.MAIL_DEFAULT_PREFIX + " Erreur extracteur";
		String mailAdministrateur = "Une erreur est survenue au démarrage de l'extracteur : \n";
		mailAdministrateur += e +"\n";
		StackTraceElement[] stack = e.getStackTrace();
		logger.debug("impression stack trace");
		for (int i = 0 ; i< stack.length ; i++){
			mailAdministrateur += "\n\t" + stack[i].getClassName() + " : " + stack[i].getMethodName() +
			" (line " + stack[i].getLineNumber() + ")";
		}
		mailAdministrateur += "\n\nRequete :\n" + requete;
		
		try{
			logger.debug("envoi mails");
			mail.send(Props.MAIL_FROM,Props.MAIL_ADMIN,subject,mailAdministrateur);
		}catch (Exception ex){
			logger.debug("pb dans send mail");
			LogUtils.logException(logger,e);
		}
	}
	
}
