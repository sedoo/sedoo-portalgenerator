package org.sedoo.mistrals.extract;

import java.io.File;
import java.io.FileInputStream;
import java.io.InputStream;
import java.io.StringReader;

import org.apache.log4j.Logger;
import org.jdom.Document;
import org.jdom.input.SAXBuilder;
import org.jdom.output.XMLOutputter;
import org.medias.utils.db.AccessToDB;
import org.sedoo.mistrals.bd.dao.RequeteDAO;
import org.sedoo.utils.log.LogUtils;
import org.sedoo.utils.xml.ValidateXmlException;
import org.sedoo.utils.xml.XMLValidator;
import org.xml.sax.InputSource;

public final class ExtractMain {

	private static Logger logger = Logger.getLogger(ExtractMain.class);
	
	private ExtractMain() {}
	
	/**
	 * Arrêter une requete avant la fin.
	 * @param requeteId
	 */
	private static void kill(int requeteId) {
		try{
			InputStream is = ClassLoader.getSystemClassLoader().getResourceAsStream("extract.conf" );
			Props.init(is);
			AccessToDB dbAccess = new AccessToDB(Props.DB_DRIVER,Props.DB_URL,Props.DB_USER,Props.DB_PASSWD);
			dbAccess.autoCommit();
			RequeteDAO.getService().kill(dbAccess.get_con(), requeteId);
			return;
		}catch (Exception e){
			logger.error(e);
			return;
		}
	}
	
	private static void launch(String file) {
		File xmlFile = new File(file);
		if (!xmlFile.exists()){
			logger.error("File " + xmlFile + " doesn't exist");
		}else{
			try{
				InputStream is = ClassLoader.getSystemClassLoader().getResourceAsStream("extract.conf" );
				Props.init(is);

				logger.debug("Target database: " + Props.DB_URL);

				logger.debug("System Properties: " + System.getProperties());
				FileInputStream xmlIs = new FileInputStream(xmlFile);

				SAXBuilder builder = new SAXBuilder();
				Document doc = builder.build(new InputSource(xmlIs));
				XMLOutputter outXml = new XMLOutputter(); 	
				String requeteXml = outXml.outputString(doc);

				logger.debug("requeteXml: " + requeteXml);

				XMLValidator validator = new XMLValidator();
				validator.validate(new StringReader(requeteXml));
				logger.debug("Requete Valide");

				ExtracteurMistrals extracteur = new ExtracteurMistrals();

				String retour = extracteur.performExtraction(requeteXml,true);
				logger.info("retour: " + retour);
			}catch (ValidateXmlException e){
				LogUtils.logException(logger,e);
				logger.info("10: Fichier xml non conforme");
				logger.info("Cause: "+e.getMessage());
			}catch(Exception e){
				logger.fatal(e);
				logger.info("11: Erreur au démarrage");
				logger.info("Cause: "+e.getMessage());
				logger.debug("Properties: " + System.getProperties());
			}finally{
				logger.debug("Fin extract");
			}
		}
	}
	
	/**
	 * Valide et exécute la requete xml (fichier xml passé en argument).
	 * @param args
	 */
	public static void main(String[] args) {		
		if (args.length == 0){
			logger.error("Arguments are missing");
			return;
		}else if (args.length == 1){
			launch(args[0]);
		}else  if (args.length == 2 && "kill".equalsIgnoreCase(args[0])){
			kill(Integer.parseInt(args[1]));
		}
				
		
	}
	
}
