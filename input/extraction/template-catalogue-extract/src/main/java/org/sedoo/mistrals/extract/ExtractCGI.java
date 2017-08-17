/*
 * Created on 24 janv. 2006
 */
package org.sedoo.mistrals.extract;

import java.io.InputStream;
import java.io.StringReader;

import org.apache.log4j.Logger;
import org.medias.utils.cgi.CGIHandler;
import org.sedoo.utils.log.LogUtils;
import org.sedoo.utils.xml.ValidateXmlException;
import org.sedoo.utils.xml.XMLValidator;

/**
 * Fonction main de l'extracteur CGI. La requete utilisée pour l'invoquer doit avoir le paramètre suivant :
 * <br>- requete : la requete d'extraction au format xml
 * <p>
 * Les autres informations nécessaires au lancement de l'extraction doivent être placées dans le fichier 'extract.conf', situé dans le répertoire depuis lequel est invoqué la jvm (celui où se trouve le script cgi) :
 * <br>- Envoi des mails : mail.smtpHost=... &  mail.from=adresse pour le champ 'from'
 * <br>- Répertoire où vont être stockés les fichiers : download.path=chemin absolu se terminant par '/'
 * <br>- Début de l'url à utiliser pour télécharger les fichiers générés par l'extracteur : download.url=url se terminant par '/' à laquelle sera ajouté le nom du fichier généré
 * <br>- Driver JDBC à utiliser : db.driver=... 
 * <p>
 * Codes retour (1X pour les erreurs) :
 * <br>- 00: OK
 * <br>- 10: Fichier xml non conforme
 * <br>- 11: Erreur au démarrage
 * <p>
 * En cas d'erreur, l'administrateur reçoit un mail contenant l'erreur et la requête reçue par l'extracteur.
 * 
 * @author brissebr
 * @see org.medias.utils.cgi.CGIHandler
 */
public final class ExtractCGI {
	
	private static Logger logger = Logger.getLogger(ExtractCGI.class);
	
	private ExtractCGI() {}
	
	/**
	 * Valide la requete xml reçue (sauf si désactivé) et rend la main au browser. L'exécution de la requête est ensuite lancée.
	 * @param args
	 */
	public static void main(String[] args) {
				
		CGIHandler cgi = null;
		String requeteXml = null;
		try{
			cgi = new CGIHandler();
									
			InputStream is = ClassLoader.getSystemClassLoader().getResourceAsStream("extract.conf" );
			Props.init(is);
			
			logger.debug("System Properties: " + System.getProperties());
			
			cgi.printHeader();
			requeteXml = cgi.getParam("requete");
					
			logger.debug("requeteXml: " + requeteXml);
			
			if (Props.VALIDATE){
				XMLValidator validator = new XMLValidator();
				validator.validate(new StringReader(requeteXml));
												
				logger.debug("Requete Valide");
			}
						
			ExtracteurMistrals extracteur = new ExtracteurMistrals();
						
			String retour = extracteur.performExtraction(requeteXml, false);
			logger.info("retour: " + retour);
			cgi.print(retour);
		}catch (ValidateXmlException e){
			LogUtils.logException(logger,e);
			cgi.print("10: Fichier xml non conforme");
			cgi.print("Cause: "+e.getMessage());
			Mailer.sendMailFatal(e,requeteXml);
		}catch(Exception e){
			logger.fatal(e);
			if (cgi != null){
				cgi.print("11: Erreur au démarrage");
				cgi.print("Cause: "+e.getMessage());
			}
			logger.debug("Properties: " + System.getProperties());
			Mailer.sendMailFatal(e,requeteXml);
		}finally{
			cgi.end();
			logger.debug("Fin CGI");
		}
	}
	
}
