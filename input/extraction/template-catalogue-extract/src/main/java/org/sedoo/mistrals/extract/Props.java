package org.sedoo.mistrals.extract;

import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;
import java.util.Properties;

import org.apache.log4j.Logger;

/**
 * 
 * @author brissebr
 */
public final class Props {

	private static Logger logger = Logger.getLogger(Props.class);
	
	private Props() {}
	
	/**
	 * Repertoire d'execution.
	 */
	public static String ROOT;
	/**
	 * Répertoire dans lequel sont stockés les résultats (download/, non configurable).
	 */
	public static String FILE_DEST_PATH;
	
	public static String ROLE_PUBLIC = "public";
	
	
	/**
	 * Indique si le fichier xml doit être validé avant de le traiter (xml.validate, true par défaut).
	 */
	public static boolean VALIDATE;
	/**
	 * Emplacement du schema XML de la requete (non configurable, le fichier est dans le repertoire schema/).
	 */
		
	public static String DB_DRIVER;
	public static String DB_URL;
	public static String DB_USER;
	public static String DB_PASSWD;
	public static String DB_ENCODING;
	
	/**
	 * Curseur à utiliser pour le parcours du ResultSet (db.cursor, 1000 par défaut). Toutes les n lignes lues, la table requete sera mise à jour.
	 */
	public static int DB_CURSOR;
			
	public static String LDAP_HOST;
	public static String LDAP_REFERRAL;
	public static String LDAP_BASE;
	public static String LDAP_ADMIN;
	public static String LDAP_PASSWD;
	
	/**
	 * Serveur SMTP a utiliser (mail.smtpHost).
	 */
	public static String MAIL_SMTPHOST;
	/**
	 * Adresse a utiliser dans le champ from des mail envoyes par l'appli (mail.from).
	 */
	public static String MAIL_FROM;
	/**
	 * Mail de l'administrateur du site (mail.admin).
	 */
	public static String MAIL_ADMIN;
	
	/**
	 * Sujet par déut (mail.topic.prefix).
	 */
	public static String MAIL_DEFAULT_PREFIX = "[MISTRALS-DATABASE]";
	
	/**
	 * URL de l'interface de téléchargement (ui.dl).
	 */
	public static String HTTP_DOWNLOAD_UI;
	/**
	 * URL de l'interface de téléchargement (ui.dl.pub).
	 */
	public static String HTTP_DOWNLOAD_UI_PUB;
	
	/**
	 * Encodage des fichiers ascii générés par l'extracteur.
	 */
	public static String FILE_ENCODING;
	
	/**
	 * En secondes, 0 pour désactiver.
	 */
	public static int TIMEOUT;
	
	public static String DEFAULT_AUTHOR;
	public static String DEFAULT_ORGANISM;
	
	public static String RESPONSE_XML_SCHEMA_URI;
	public static String RESPONSE_XML_SCHEMA_XSD;
	
	/**
	 * Charge les propriétés à partir d'un fichier de configuration. 
	 * Le fichier est recherché dans le répertoire depuis lequel la jvm à été lancée.
	 * 
	 * @param filename chemin relatif du fichier de conf
	 * @throws FileNotFoundException
	 * @throws IOException
	 * @see org.medias.amma.extract.commun.Props#init(String)
	 */
	public static void init(InputStream is) throws IOException{
		logger.debug("Init properties");
		
		Props.ROOT = System.getProperty("user.dir");
		
		logger.debug("user.dir: " + Props.ROOT);
											
		Properties properties = new Properties();
		properties.load(is);
		
		Props.FILE_DEST_PATH = properties.getProperty("result.path");
		if (!Props.FILE_DEST_PATH.endsWith("/")){
			Props.FILE_DEST_PATH += "/";
		}
		logger.debug("FILE_DEST_PATH: " + Props.FILE_DEST_PATH);
				
		Props.VALIDATE = Boolean.parseBoolean(properties.getProperty("xml.validate","true"));
				
		Props.DB_DRIVER = properties.getProperty("db.driver");
		Props.DB_URL = properties.getProperty("db.url");
		Props.DB_USER = properties.getProperty("db.username");
		Props.DB_PASSWD = properties.getProperty("db.password");
		Props.DB_CURSOR = Integer.parseInt(properties.getProperty("db.cursor","1000"));
		Props.DB_ENCODING = properties.getProperty("db.encoding","UTF-8");
		
		LDAP_HOST = properties.getProperty("ldap.host","localhost")+":"+properties.getProperty("ldap.port","389");
		LDAP_REFERRAL = properties.getProperty("ldap.referral","ignore");
		LDAP_BASE = properties.getProperty("ldap.base");
		LDAP_ADMIN = properties.getProperty("ldap.admin");
		LDAP_PASSWD = properties.getProperty("ldap.passwd");
						
		MAIL_SMTPHOST = properties.getProperty("mail.smtpHost");
		MAIL_FROM = properties.getProperty("mail.from");
		MAIL_ADMIN = properties.getProperty("mail.admin");
		MAIL_DEFAULT_PREFIX = properties.getProperty("mail.topic.prefix");
		
		RESPONSE_XML_SCHEMA_URI = properties.getProperty("xml.response.schema.uri");
		RESPONSE_XML_SCHEMA_XSD = properties.getProperty("xml.response.schema.xsd");
		
		TIMEOUT = Integer.parseInt(properties.getProperty("extract.fichiers.timeout", "30"));
		
		Props.HTTP_DOWNLOAD_UI = properties.getProperty("ui.dl");
		Props.HTTP_DOWNLOAD_UI_PUB = properties.getProperty("ui.dl.pub");
		
		Props.FILE_ENCODING = properties.getProperty("result.encoding");
		
		Props.DEFAULT_AUTHOR = properties.getProperty("result.default.author");
		Props.DEFAULT_ORGANISM = properties.getProperty("result.default.organism");
	}
	
}
