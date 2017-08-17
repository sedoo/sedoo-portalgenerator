package org.sedoo.mistrals.extract.sortie.netcdf;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.Collections;
import java.util.Date;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.Set;
import java.util.SortedSet;
import java.util.TreeSet;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.beans.Dataset;
import org.sedoo.mistrals.bd.beans.ExtractConfig;
import org.sedoo.mistrals.bd.beans.GcmdScienceKeyword;
import org.sedoo.mistrals.bd.beans.Param;
import org.sedoo.mistrals.bd.beans.Unit;
import org.sedoo.mistrals.bd.dao.DatasetDAO;
import org.sedoo.mistrals.bd.dao.GcmdScienceKeywordDAO;
import org.sedoo.mistrals.bd.dao.ParamDAO;
import org.sedoo.mistrals.bd.dao.UnitDAO;
import org.sedoo.mistrals.bd.dao.VariableDAO;
import org.sedoo.mistrals.extract.Props;
import org.sedoo.mistrals.extract.requetes.RequeteBase;
import org.sedoo.mistrals.extract.requetes.RequeteDataset;
import org.sedoo.mistrals.extract.sortie.FichierSortie;
import org.sedoo.mistrals.extract.utils.Contact;
import org.sedoo.mistrals.extract.utils.ContactsJeu;
import org.sedoo.mistrals.extract.utils.ContactsJeuFactory;
import org.sedoo.utils.exceptions.DataNotFoundException;


public abstract class FichierNetCdfBase extends FichierSortie {

	private static Logger logger = Logger.getLogger(FichierNetCdfBase.class);

	protected RequeteBase requete;

	protected FichierNetCdf fichierNetcdf;
	
	protected Dataset dats;
	private ContactsJeu contacts;

	protected Map<Integer,String> varNames = new HashMap<Integer, String>();
	
	protected ExtractConfig conf;
	
	public FichierNetCdfBase(RequeteDataset requete,Connection dbCon,int requeteId,ExtractConfig conf) throws SQLException {
		super(dbCon,requeteId);

		logger.debug("<init>");
		
		this.requete = requete;
		this.dats = DatasetDAO.getService().getById(dbCon, requete.getDatsId());

		this.conf = conf;
		
		//requete.filtrePlaces(dbCon);
		this.contacts = ContactsJeuFactory.getContacts(dbCon, dats);
		/*
		this.contacts = new ContactsJeu(dats);
		PreparedStatement stmt = dbCon.prepareStatement(
				"select dats_id,contact_type_id,pers_name,pers_email_1,coalesce(org_fname,org_sname,'Unknown Affiliation') as org_name, contact_type_name " +
						"from dats_data join dats_originators using (dats_id) join personne using (pers_id) join organism using (org_id) join contact_type using (contact_type_id) " +
				"where dats_id = ?");
		stmt.setInt(1, contacts.getJeu().getDatsId());
		ResultSet rs = stmt.executeQuery();
		while (rs.next()) {
			this.contacts.getContacts().add(new Contact(rs.getString("pers_name"), rs.getString("pers_email_1"),rs.getString("org_name"),rs.getString("contact_type_name"),false));
		}
		stmt.close();
*/
	}

	@Override
	protected String getFilename()  {
		return conf.getDatsShortName() + FichierNetCdf.EXTENSION;
	}

	@Override
	protected void open() throws IOException {
		logger.debug("open()");
		this.fichierNetcdf = new FichierNetCdf(getFile());
	}
	
	@Override
	public void closeFile() throws IOException {
		logger.debug("closeFile()");
		fichierNetcdf.close();		
	}

	@Override
	public Set<ContactsJeu> getContacts() {
		logger.debug("getContacts()");
		return Collections.singleton(contacts);
	}
	
	protected abstract void addDimensions();
	protected abstract String getDimensions();
	
	protected abstract void addCoordinateVars() throws IOException ;
	
	protected abstract String getFeatureType();
	protected abstract String getCoordinates();

	/**
	 * Pour ajouter des attributs supplémentaires.
	 * @throws IOException
	 */
	protected abstract void addGlobalAttributes() throws IOException ;
	
	
	/**
	 * Pour ajouter des variables precise_lat et precise_lon (pour les stations "fixes" dont la position varie légèrement autour d'un point, ex : Bouée).
	 */
	protected void addPreciseLatLon() throws IOException{
		try{
			fichierNetcdf.addMeasuredParam("precise_lat", "latitude", "station latitude", "degrees_north", getDimensions());
			fichierNetcdf.addMeasuredParam("precise_lon", "longitude", "station longitude", "degrees_east", getDimensions());
		}catch(DataNotFoundException e){
			throw new IOException("Error while creating variables precise lat/lon.",e);
		}
	}

	@Override
	public void printHeader() throws IOException,SQLException {
		logger.debug("printHeader()");
		addDimensions();
		
		addCoordinateVars();
						
		SortedSet<String> keywords = new TreeSet<String>();
		for (int varId : requete.getVarIds()) {
			Param param = ParamDAO.getService().getById(dbCon, varId);
			org.sedoo.mistrals.bd.beans.Variable var = VariableDAO.getService().getById(dbCon,varId);

			GcmdScienceKeyword gcmd = GcmdScienceKeywordDAO.getService().getById(dbCon, var.getGcmdId(), true);
			Unit unit = UnitDAO.getService().getById(dbCon, param.getUnitId());
			String longName = null;
			if (var.getVarName() != null && !var.getVarName().isEmpty()){
				longName = var.getVarName();
			}else{
				longName = gcmd.getGcmdName();
			}

			String varName = param.getParamCode();
			if (varName == null || varName.isEmpty()){
				varName = longName.replaceAll(" ", "_").replaceAll("[()]", "");
			}
			
			String unite = unit.getUnitCode();
			if ( unite == null){
				unite = unit.getUnitName();
			}

			keywords.add(gcmd.toString());

			logger.debug("Var " + varName + ", " + unite + ", " + gcmd.toString());
			try{
				fichierNetcdf.addMeasuredParam(varName, param.getStandardName(), longName, unite, getDimensions());
				fichierNetcdf.addVariableAttribute(varName, "coordinates", getCoordinates());
			}catch(DataNotFoundException e){
				throw new IOException("Error while creating variable " + varName,e);
			}
			
			
			//ncWriter.addVariableAttribute(paramVar, new Attribute("coordinates","time lat lon"));

			//TODO flag, delta

			
			varNames.put(varId,varName);

		}

		/* Attributs globaux */
		fichierNetcdf.addGlobalAttribute("featureType",getFeatureType());
		
		//Attributs CF
		fichierNetcdf.addGlobalAttribute("Conventions","CF-1.5");
		fichierNetcdf.addGlobalAttribute("title", dats.getDatsTitle());
		fichierNetcdf.addGlobalAttribute("institution", Props.DEFAULT_AUTHOR);
		//TODO à changer
		fichierNetcdf.addGlobalAttribute("source","surface observation");
		fichierNetcdf.addGlobalAttribute("history", new Date() + ": data extracted from " + requete.getProjet() + " database");
		fichierNetcdf.addGlobalAttribute("references", Props.HTTP_DOWNLOAD_UI + "?datsId=" + dats.getDatsId());
		fichierNetcdf.addGlobalAttribute("comment","");
		
		//Metadata discovery convention
		//http://www.unidata.ucar.edu/software/netcdf-java/formats/DataDiscoveryAttConvention.html
		fichierNetcdf.addGlobalAttribute("Metadata_Conventions","Unidata Dataset Discovery v1.0");
		if (dats.getDatsAbstract() != null){
			fichierNetcdf.addGlobalAttribute("summary", dats.getDatsAbstract());
		}
		
		//Mots clés GCMD
		String keywordsList = "";
		for (Iterator<String> i = keywords.iterator();i.hasNext();){
			keywordsList += i.next();
			if (i.hasNext()){
				keywordsList += ",";
			}
		}
		fichierNetcdf.addGlobalAttribute("keywords", keywordsList);
		if (!keywords.isEmpty()){
			fichierNetcdf.addGlobalAttribute("keywords_vocabulary", "GCMD Science Keywords");
		}
		fichierNetcdf.addGlobalAttribute("standard_name_vocabulary", "CF-1.0");
		
		
		//Contact
		fichierNetcdf.addGlobalAttribute("publisher_name",Props.DEFAULT_AUTHOR);
		fichierNetcdf.addGlobalAttribute("publisher_email",Props.DEFAULT_ORGANISM);
		String datsContacts = "";
		//TODO pis
		String pis = "";
		for (Contact contact: contacts.getContacts()){
			datsContacts += contact.toString() + "\n";
		}
		fichierNetcdf.addGlobalAttribute("dataset_contact", datsContacts);
		
		//Use Constraints
		if (contacts.getJeu().getUseConstraints() != null){
			fichierNetcdf.addGlobalAttribute("license", requete.getProjet() + " data policy. " + contacts.getJeu().getUseConstraints());
		}
				
		//DOI
		if (contacts.getJeu().getDoi() != null){
			fichierNetcdf.addGlobalAttribute("doi",contacts.getJeu().getDoi());
		}	
		
		addGlobalAttributes();
		
		fichierNetcdf.writeHeader();
	}

}
