/*
 * Created on 24 janv. 2006
 */
package org.sedoo.mistrals.extract.fichiers;

import java.io.InputStream;
import java.io.StringReader;

import org.apache.log4j.Logger;
import org.medias.utils.cgi.CGIHandler;
import org.sedoo.mistrals.extract.Mailer;
import org.sedoo.mistrals.extract.Props;
import org.sedoo.utils.log.LogUtils;
import org.sedoo.utils.xml.ValidateXmlException;
import org.sedoo.utils.xml.XMLValidator;

/**
 * Fonction main de l'extracteur CGI. La requete utilisée pour l'invoquer doit avoir les paramètres suivants :
 * <br>- requete : la requete d'extraction au format xml
 * <br>- wait : indique si la cgi doit attendre la fin du traitement avant de rendre la main (si oui, l'url du résultat est retourné par le cgi)
 * <p>
 * Les autres informations n�cessaires au lancement de l'extraction doivent �tre plac�es dans le fichier 'extract.conf', situ� dans le r�pertoire depuis lequel est invoqu� la jvm (celui o� se trouve le script cgi) :
 * <br>- Envoi des mails : mail.smtpHost=... &  mail.from=adresse pour le champ 'from'
 * <br>- R�pertoire o� vont �tre stock�s les fichiers : download.path=chemin absolu se terminant par '/'
 * <br>- D�but de l'url a utiliser pour t�l�charger les fichiers g�n�r�s par l'extracteur : download.url=url se terminant par '/' � laquelle sera ajout� le nom du fichier g�n�r�
 * <br>- Driver JDBC � utiliser : db.driver=... 
 * <p>
 * Codes retour (1X pour les erreurs) :
 * <br>- 00: OK (wait=false)
 * <br>- 01: &lt;url&gt; (wait=true)
 * <br>- 10: Fichier xml non conforme
 * <br>- 11: Erreur au d�marrage
 * <p>
 * En cas d'erreur, l'administrateur re�oit un mail contenant l'erreur et la requ�te re�ue par l'extracteur.
 * 
 * @author brissebr
 * @see org.medias.utils.cgi.CGIHandler
 */
public class ExtractFilesCGI {
	
	private final static Logger logger = Logger.getLogger(ExtractFilesCGI.class);
	
	/**
	 * 
	 * @param args
	 */
	public static void main(String[] args) {
				
		CGIHandler cgi = null;
		String requeteXml = null;
		try{
			cgi = new CGIHandler();
			
			InputStream is = ClassLoader.getSystemClassLoader().getResourceAsStream("extract.conf" );
			Props.init(is);
			
			cgi.printHeader();
			requeteXml = cgi.getParam("requete");
			
			logger.info("requeteXml: " + requeteXml);
									
			if (Props.VALIDATE){
				XMLValidator validator = new XMLValidator();
				validator.validate(new StringReader(requeteXml));
												
				logger.debug("Requete Valide");
			}
									
			ExtracteurFichiers extracteur = new ExtracteurFichiers();
						
			String result = extracteur.performExtraction(requeteXml);		
			logger.info("Result: " + result);
			cgi.print(result);
		}catch (ValidateXmlException e){
			LogUtils.logException(logger,e);
			cgi.print("10: Fichier xml non conforme");
			cgi.print("Cause: "+e.getMessage());
			Mailer.sendMailFatal(e,requeteXml);
		}catch(Exception e){
			logger.fatal(e);
			LogUtils.logException(logger,e);
			cgi.print("11: Erreur au démarrage");
			cgi.print("Cause: "+e.getMessage());
			Mailer.sendMailFatal(e,requeteXml);
		}finally{
			cgi.end();
		}
	}
	
}
