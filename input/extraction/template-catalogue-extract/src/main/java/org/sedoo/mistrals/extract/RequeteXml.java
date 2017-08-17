package org.sedoo.mistrals.extract;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.List;

import org.apache.log4j.Logger;
import org.apache.xml.serialize.OutputFormat;
import org.apache.xml.serialize.XMLSerializer;
import org.sedoo.utils.exceptions.InvalidDataException;
import org.sedoo.utils.xml.XMLElement;
import org.sedoo.mistrals.bd.utils.Periode;
import org.sedoo.mistrals.bd.utils.Zone;
import org.sedoo.mistrals.extract.utils.Contact;
import org.sedoo.mistrals.extract.utils.ContactFactory;
import org.w3c.dom.Document;
import org.w3c.dom.Element;
import org.w3c.dom.NodeList;

public class RequeteXml {
	
	public static final String FORMAT_AMES = "ames";
	public static final String FORMAT_NETCDF = "netcdf";
	
	public static final String FORMAT_AMES_2160 = "2160";
	public static final String FORMAT_AMES_1001 = "1001";
	public static final String FORMAT_AMES_1001_DATASET = "1001d";
	public static final String FORMAT_AMES_1001_FIXE = "1001f";
	public static final String FORMAT_AMES_1001_MOBILE = "1001m";
	public static final String FORMAT_AMES_1010 = "1010";
	
	public static final String COMPRESSION_ZIP = "zip";
	public static final String COMPRESSION_GZ = "gzip";
	
	private final Logger logger = Logger.getLogger(RequeteXml.class);
			
	private Document xml;
	
	//private Timestamp dateMin = null;
	//private Timestamp dateMax = null;
	
	private Periode periode;
	private Zone zone;
	
	private List<Integer> datsIds;
	private List<Integer> placeIds;
	private List<Integer> varIds;
	
	private String projet;
	
	private String format;
	private String formatVersion;
	
	private String compression;
	private String valeursAbsentes;
	private String separateur;

	private boolean withFlag;
	private boolean withDelta;
	
	private Contact utilisateur;

	/**
	 * TODO
	 * @param xml
	 */
	public RequeteXml (Document xml) throws InvalidDataException{
		logger.debug("RequeteXml.<init>");
		this.xml = xml;
		readXML(xml);		
	}
	
	public String xmlToString() throws IOException{
		ByteArrayOutputStream os = new ByteArrayOutputStream();
		OutputFormat outputFormat = new OutputFormat(xml);
		outputFormat.setPreserveSpace(true);
		outputFormat.setIndenting(true);
		XMLSerializer serializer = new XMLSerializer(os, outputFormat);
		serializer.serialize(xml);
		return new String(os.toByteArray(),Props.DB_ENCODING);
	}
		
	/* ******* Lecture requete Xml ******* */

	private void readXML(Document xml) throws InvalidDataException{
		
		readSelection((Element) xml.getElementsByTagName("selection").item(0));
		readOptions((Element) xml.getElementsByTagName("options").item(0));
		readUtilisateur((Element) xml.getElementsByTagName("utilisateur").item(0));
		
		Element root = (Element) xml.getElementsByTagName("requete").item(0);
		this.projet = XMLElement.getStringValue(root,"projet");
		logger.debug("projet: " + this.projet);
		
	}
			
	private void readSelection(Element selection) throws InvalidDataException{
						
		Element selectionPeriode = (Element) selection.getElementsByTagName("periode").item(0);
		
		Timestamp dateMin = XMLElement.getTimestampValue(selectionPeriode,"date_min");
		Timestamp dateMax = XMLElement.getTimestampValue(selectionPeriode,"date_max");
		
		logger.debug("date_min: " + dateMin);
		logger.debug("date_max: " + dateMax);
		
		periode = new Periode(dateMin, dateMax);
		
		//Zone
		Element selectionZone = (Element) selection.getElementsByTagName("zone").item(0);
		zone = new Zone(XMLElement.getDoubleValue(selectionZone, "lat_min"),
				XMLElement.getDoubleValue(selectionZone, "lat_max"),
				XMLElement.getDoubleValue(selectionZone, "lon_min"),
				XMLElement.getDoubleValue(selectionZone, "lon_max"));
				
		logger.debug("zone: " + zone);

		//Fiches
		Element selectionDatasets = (Element) selection.getElementsByTagName("datasets").item(0);
		NodeList listFiches = selectionDatasets.getElementsByTagName("dats_id");

		datsIds = new ArrayList<Integer>();
		for (int i = 0; i<listFiches.getLength();i++){
			int datsId = Integer.parseInt(listFiches.item(i).getFirstChild().getNodeValue());
			logger.debug("dats_id: " + datsId);
			datsIds.add(i,datsId);
		}
		
		//Sites
		Element selectionSites = (Element) selection.getElementsByTagName("places").item(0);
		NodeList listSites = selectionSites.getElementsByTagName("place_id");
		
		placeIds = new ArrayList<Integer>();
		for (int i = 0; i<listSites.getLength();i++){
			 int placeId = Integer.parseInt(listSites.item(i).getFirstChild().getNodeValue());
			logger.debug("place_id: " + placeId);
			placeIds.add(i,placeId);
        }
		
		//Params
		Element selectionParams = (Element) selection.getElementsByTagName("variables").item(0);
		NodeList listParams = selectionParams.getElementsByTagName("var_id");
				
		varIds = new ArrayList<Integer>();
		for (int i = 0; i<listParams.getLength();i++){
			 int varId = Integer.parseInt(listParams.item(i).getFirstChild().getNodeValue());
			logger.debug("var_id: " + varId);
			varIds.add(i,varId);
         }
	}

	private void readOptions(Element options){
		format = XMLElement.getStringValue(options,"format");
		logger.debug("format: " + format);
		formatVersion = XMLElement.getStringValue(options,"format_version");
		logger.debug("format_version: " + formatVersion);
				
		compression = XMLElement.getStringValue(options,"compression");
		logger.debug("compression: " + compression);
		valeursAbsentes = XMLElement.getStringValue(options,"valeurs_absentes");
		if (valeursAbsentes == null || valeursAbsentes.equalsIgnoreCase("none")){
			valeursAbsentes = "";
		}
		logger.debug("valeurs_absentes: " + valeursAbsentes);
		separateur = XMLElement.getStringValue(options,"separateur");
		logger.debug("separateur: " + separateur);
		
		withFlag = getBooleanValue(XMLElement.getStringValue(options, "valeur_flag"));
		logger.debug("valeur_flag: " + withFlag);
		withDelta = getBooleanValue(XMLElement.getStringValue(options, "valeur_delta"));
		logger.debug("valeur_delta: " + withDelta);
	}

	private boolean getBooleanValue(String str){
		return "1".equals(str) || "true".equals(str);
	}
	
	private void readUtilisateur(Element utilisateur){
		String requeteEmail = XMLElement.getStringValue(utilisateur,"utilisateur_email");
		String utilisateurNom = XMLElement.getStringValue(utilisateur,"utilisateur_nom");
		String utilisateurInstitute = XMLElement.getStringValue(utilisateur,"utilisateur_institute");
		this.utilisateur = ContactFactory.getUser(utilisateurNom,requeteEmail,utilisateurInstitute);
	}
	
	public List<Integer> getDatsIds() {
		return datsIds;
	}
	
	public String getFormat() {
		return format;
	}
	public String getFormatVersion() {
		return formatVersion;
	}
	
	public String getCompression() {
		return compression;
	}

	public String getValeursAbsentes() {
		return valeursAbsentes;
	}

	public String getSeparateur() {
		return separateur;
	}

	public Contact getUtilisateur() {
		return utilisateur;
	}
	
	public Periode getPeriode() {
		return periode;
	}
/*
	public Timestamp getDateMin() {
		return dateMin;
	}

	public Timestamp getDateMax() {
		return dateMax;
	}
	*/
	public List<Integer> getVariablesIds() {
		return varIds;
	}
	public Zone getZone() {
		return zone;
	}
	public List<Integer> getPlacesIds() {
		return placeIds;
	}

	public boolean isWithFlag() {
		return withFlag;
	}

	public boolean isWithDelta() {
		return withDelta;
	}
	
	public String getProjet() {
		return projet;
	}
	
}
