package org.sedoo.mistrals.extract.fichiers;

import java.io.File;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.Date;
import java.util.List;


import org.apache.log4j.Logger;


import org.medias.utils.db.AccessToDB;
import org.sedoo.mistrals.bd.beans.Role;
import org.sedoo.mistrals.bd.dao.RoleDAO;
import org.sedoo.mistrals.extract.Props;
import org.sedoo.mistrals.extract.RequeteXml;
import org.sedoo.mistrals.extract.utils.Contact;
import org.sedoo.mistrals.extract.utils.ContactFactory;
import org.sedoo.utils.exceptions.InvalidDataException;
import org.sedoo.utils.xml.XMLElement;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;

/**
 * Requete pour la commande de fichiers.
 * @author brissebr
 */
public class RequeteXmlFichiers {

	private final static Logger logger = Logger.getLogger(RequeteXmlFichiers.class);
	
	public final static String COMPRESSION_ZIP = RequeteXml.COMPRESSION_ZIP;
	public final static String COMPRESSION_GZ = RequeteXml.COMPRESSION_GZ;
			
	private int requeteId = 0;
			
	private String projet;
	
	private Contact utilisateur;
			
	private String compression;
	
	private File racine;
	
	private int datsId;
	private List<File> files;
	
	Date dateDeb = null,
	dateFin = null;
			
	public RequeteXmlFichiers (Document xml) throws InvalidDataException{
		logger.debug("Requete.<init>");
		this.dateDeb = new Date();
		readXML(xml);		
	}
	
	/**
	 * Vérifie que l'utilisateur a bien le droit d'accéder aux fichiers du jeu demandé.
	 * @return
	 * @throws SQLException
	 */
	public boolean isAuthorized() throws SQLException{
		AccessToDB dbAccess = null;
		try{
			dbAccess = new AccessToDB(Props.DB_DRIVER,Props.DB_URL,Props.DB_USER,Props.DB_PASSWD);
			Collection<Role> roles = RoleDAO.getService().getByDatsId(dbAccess.get_con(), datsId);
			for (Role role: roles){
				for (String userRole: utilisateur.getRoles()){
					if (role.getRoleName().equals(userRole)){
						return true;
					}
				}
			}
			return false;
		}catch(SQLException e){
			throw e;
		}finally{
			if (dbAccess != null){
				dbAccess.close();
			}
		}
	}
	

	public boolean isPublic(){
		return !this.utilisateur.isRegistered();
	}
	
	private void readXML(Document xml) throws InvalidDataException{
		Element root = (Element) xml.getElementsByTagName("requete_files").item(0);
		
		readUtilisateur((Element) xml.getElementsByTagName("utilisateur").item(0));
		this.projet = XMLElement.getStringValue(root,"projet");
		logger.debug("projet: " + this.projet);
		
		this.compression = XMLElement.getStringValue(root,"compression");
				
		readSelection((Element) xml.getElementsByTagName("selection").item(0));
			
	}
	
	private void readUtilisateur(Element utilisateur){
		String requeteEmail = XMLElement.getStringValue(utilisateur,"utilisateur_email");
		String utilisateurNom = XMLElement.getStringValue(utilisateur,"utilisateur_nom");
		String utilisateurInstitute = XMLElement.getStringValue(utilisateur,"utilisateur_institute");
		
		/*if (utilisateurNom.equals(Props.USER_PUBLIC)){
			//Accès public => pas de recherche dans l'annuaire, le contact a le role public.
			this.utilisateur = new Contact(utilisateurNom,requeteEmail,utilisateurInstitute,"User",false);
			this.isPublic = true;
		}else{		
			this.utilisateur = new Contact(utilisateurNom,requeteEmail,utilisateurInstitute,"User");
			this.isPublic = false;
		}*/
		this.utilisateur = ContactFactory.getUser(utilisateurNom,requeteEmail,utilisateurInstitute);
	}
	
	private void readSelection(Element selection){
		this.datsId = XMLElement.getIntValue(selection, "datsId");

		this.racine = new File(XMLElement.getStringValue(selection,"racine"));
		
		NodeList listFiles = selection.getElementsByTagName("file");

		this.files = new ArrayList<File>();
		for (int i = 0; i<listFiles.getLength();i++){
			File file = new File(listFiles.item(i).getFirstChild().getNodeValue());
			this.files.add(file);
			logger.debug("file: " + file);
		}
		
	}
	
	
	public Contact getUtilisateur() {
		return utilisateur;
	}
	
	public List<File> getFiles() {
		return files;
	}
	
	public String getProjet() {
		return projet;
	}
	
	public int getRequeteId() {
		return requeteId;
	}
	
	public String getCompression() {
		return compression;
	}
	
	public int getDatsId() {
		return datsId;
	}
	
	public Date getDateDeb() {
		return dateDeb;
	}
	public Date getDateFin() {
		return dateFin;
	}
	public void setDateFin() {
		this.dateFin = new Date();
	}
	public File getRacine() {
		return racine;
	}
	
}
