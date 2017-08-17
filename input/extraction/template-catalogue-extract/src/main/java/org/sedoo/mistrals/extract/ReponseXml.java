package org.sedoo.mistrals.extract;

import java.io.FileReader;
import java.util.ArrayList;
import java.util.Collection;
import java.util.UUID;

import org.apache.log4j.Logger;
import org.jdom.Document;
import org.jdom.Element;
import org.sedoo.mistrals.extract.utils.Contact;
import org.sedoo.mistrals.extract.utils.ContactsJeu;
import org.sedoo.mistrals.extract.utils.FichierResultat;
import org.sedoo.utils.xml.XMLBuilderNS;
import org.sedoo.utils.xml.XMLValidator;

public class ReponseXml {

	private static Logger logger = Logger.getLogger(ReponseXml.class);
				
	private String projectName;
	private String filename;
	
	private Contact user;
	private int requeteId;
	private Collection<FichierResultat> fichiers;
	
	private boolean isPublic;
	
	public ReponseXml(Contact user, int requeteId, String projectName) {
		this.filename = UUID.randomUUID() + ".xml";
		logger.debug("Create file " + filename);
		this.requeteId = requeteId;
		this.fichiers = new ArrayList<FichierResultat>();
		this.user = user;
		this.projectName = projectName;
		this.isPublic = false;
	}
		
	public void addFile(FichierResultat file){
		fichiers.add(file);
	}
	
	public void addFiles(Collection<FichierResultat> files){
		fichiers.addAll(files);
	}
	
	public boolean isEmpty(){
		return fichiers.isEmpty();
	}
	
	public void setPublic(boolean isPublic) {
		this.isPublic = isPublic;
	}
	public boolean isPublic() {
		return isPublic;
	}
	
	/**
	 * TODO à compléter
	 */
	public void write(){
		XMLBuilderNS resultXml = new XMLBuilderNS(Props.RESPONSE_XML_SCHEMA_URI, Props.RESPONSE_XML_SCHEMA_XSD);
						
		Element root = resultXml.createRoot();
		resultXml.addXMLAttribute(root,"requestId",""+requeteId);
		resultXml.addXMLAttribute(root,"public", ""+isPublic);
		
		Document docXml = new Document(root);

		resultXml.createXMLRepresentation(root, "project", projectName);
		resultXml.createXMLRepresentation(root, "user", user.getNom());
		resultXml.createXMLRepresentation(root, "mail", user.getMail());
		
		Element roles = resultXml.createElement("roles");
		for (String role: user.getRoles()){
			resultXml.createXMLRepresentation(roles, "role", role);
		}
		resultXml.createXMLRepresentation(root,roles);
		
		resultXml.createXMLRepresentation(root, "abstract", user.getAbstract());
							
		for (FichierResultat file: fichiers){
			Element fileElt = resultXml.createElement("file");	
			resultXml.createXMLRepresentation(fileElt, "filename", file.getFile().getAbsolutePath());
			for (ContactsJeu contacts: file.getContacts()){
				Element datasetElt = resultXml.createElement("dataset");
				resultXml.createXMLRepresentation(datasetElt, "dataset_id", ""+contacts.getJeu().getDatsId());
				resultXml.createXMLRepresentation(datasetElt, "dataset_title", contacts.getJeu().getDatsTitle());
								
				Element contactsElt = resultXml.createElement("contacts");
				for (Contact contact: contacts.getContacts()){
					Element contactElt = resultXml.createElement("contact");
					resultXml.createXMLRepresentation(contactElt, "contact_name", contact.getNom());
					resultXml.createXMLRepresentation(contactElt, "contact_mail", contact.getMail());
					resultXml.createXMLRepresentation(contactElt, "contact_organism", contact.getOrganisme());
					resultXml.createXMLRepresentation(contactElt, "contact_type", contact.getType());
					resultXml.createXMLRepresentation(contactsElt, contactElt);
				}
				resultXml.createXMLRepresentation(datasetElt, contactsElt);
				resultXml.createXMLRepresentation(fileElt, datasetElt);
			}

			for (FichierResultat assocFile: file.getAssociatedFiles()){
				Element assocFileElt = resultXml.createElement("associated_file");	
				resultXml.createXMLRepresentation(assocFileElt, "filename", assocFile.getFile().getAbsolutePath());
				resultXml.createXMLRepresentation(fileElt, assocFileElt);
			}
			
			resultXml.createXMLRepresentation(root, fileElt);
		}
								
		resultXml.setDocument(docXml);
		
		String file = Props.FILE_DEST_PATH + "/" + filename;
		resultXml.writeXml(file);
		
		
		//Validation
		XMLValidator validator = new XMLValidator();
		try{
			validator.validate(new FileReader(file));
			logger.info("Reponse valide");
		}catch (Exception e) {
			logger.warn("Reponse incorrecte");
			logger.warn("Cause: "+e.getMessage());
		}
	}
		
	public String getURL() {
		if (isPublic()){
			return Props.HTTP_DOWNLOAD_UI_PUB + "?resultId=" + filename.replaceAll(".xml", "") + "&project_name=" + projectName;
		}else{
			return Props.HTTP_DOWNLOAD_UI + "?resultId=" + filename.replaceAll(".xml", "") + "&project_name=" + projectName;
		}
	}
	
	public Collection<FichierResultat> getFichiers() {
		return fichiers;
	}
	
	public Contact getUser() {
		return user;
	}
	
	public String getProjectName() {
		return projectName;
	}
}
