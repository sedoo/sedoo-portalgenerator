package org.sedoo.mistrals.extract.sortie.ames;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.PrintStream;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collection;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;
import java.util.SortedSet;
import java.util.TreeSet;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.beans.Dataset;
import org.sedoo.mistrals.bd.beans.DatsData;
import org.sedoo.mistrals.bd.beans.Flag;
import org.sedoo.mistrals.bd.beans.GcmdInstrumentKeyword;
import org.sedoo.mistrals.bd.beans.GcmdScienceKeyword;
import org.sedoo.mistrals.bd.beans.Localisation;
import org.sedoo.mistrals.bd.beans.Manufacturer;
import org.sedoo.mistrals.bd.beans.Param;
import org.sedoo.mistrals.bd.beans.Sensor;
import org.sedoo.mistrals.bd.beans.Unit;
import org.sedoo.mistrals.bd.beans.Valeur;
import org.sedoo.mistrals.bd.beans.Variable;
import org.sedoo.mistrals.bd.dao.DatasetDAO;
import org.sedoo.mistrals.bd.dao.DatsDataDAO;
import org.sedoo.mistrals.bd.dao.FlagDAO;
import org.sedoo.mistrals.bd.dao.GcmdInstrumentKeywordDAO;
import org.sedoo.mistrals.bd.dao.GcmdScienceKeywordDAO;
import org.sedoo.mistrals.bd.dao.ManufacturerDAO;
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
import org.sedoo.utils.Constantes;
import org.sedoo.utils.LocalisationConverter;
import org.sedoo.utils.ames.AmesDataFlagVar;
import org.sedoo.utils.ames.AmesDataRecord1D;
import org.sedoo.utils.ames.AmesException;
import org.sedoo.utils.ames.AmesFile;
import org.sedoo.utils.ames.AmesFileFactory;
import org.sedoo.utils.ames.AmesTimeVar;
import org.sedoo.utils.ames.AmesDataVar;
import org.sedoo.utils.ames.AmesUtils;
import org.sedoo.utils.exceptions.DataNotFoundException;

/**
 * 
 * @author brissebr
 */
public abstract class FichierAmesBase extends FichierSortie {

	private static Logger logger = Logger.getLogger(FichierAmesBase.class);
		
	private static final String COMMENT_SEPARATOR = "---------------------------------------------------------------------------------------------------";
		
	private static final double MISSING_LAT = 99.9;
	private static final double MISSING_LON = 999.9;
	private static final double MISSING_ALT = 9999.9;
	private static final double MISSING_HEIGHT = 999999.9;
	
	
	protected AmesFile amesFile;

	protected RequeteBase requete;

	protected Set<ContactsJeu> contactsList;
	
	protected String insDatsIdStr;
	protected Map<Integer, Set<Integer>> insDatsIdToDatsIds;
	
	// Contient les flags trouvés dans les données
	protected SortedSet<Integer> flags;
	// Contient les jeux trouvés
	SortedSet<Integer> datasets;
	
	boolean writeReadme;
	
	protected FichierAmesBase(Connection dbCon,int requeteId, RequeteBase requete) throws SQLException{
		super(dbCon,requeteId);
		
		this.requete = requete;
		
		this.flags = new TreeSet<Integer>();
		this.datasets = new TreeSet<Integer>();	
		this.writeReadme = true;
		
		this.insDatsIdStr = "";
		this.insDatsIdToDatsIds = new HashMap<Integer, Set<Integer>>();
		this.contactsList = new HashSet<ContactsJeu>();
		for (int datsId: requete.getDatsIds()){
			//Contacts
			Dataset dats = DatasetDAO.getService().getById(dbCon, datsId);
			ContactsJeu contacts = ContactsJeuFactory.getContacts(dbCon, dats);
			/*ContactsJeu contacts = new ContactsJeu(dats);
			PreparedStatement stmt = dbCon.prepareStatement(
					"select dats_id,contact_type_id,pers_name,pers_email_1,coalesce(org_fname,org_sname,'Unknown Affiliation') as org_name, contact_type_name " +
					"from dats_data join dats_originators using (dats_id) join personne using (pers_id) join organism using (org_id) join contact_type using (contact_type_id) " +
			"where dats_id = ?");
			stmt.setInt(1, contacts.getJeu().getDatsId());
			ResultSet rs = stmt.executeQuery();
			while (rs.next()) {
				contacts.getContacts().add(new Contact(rs.getString("pers_name"), rs.getString("pers_email_1"),rs.getString("org_name"),rs.getString("contact_type_name"),false));
			}
			*/
			contactsList.add(contacts);
			//stmt.close();
			
			//Inserted datasets
			Collection<DatsData> dds = DatsDataDAO.getService().getByDatsId(dbCon, datsId);
			for (DatsData dd: dds){
				insDatsIdStr += "," + dd.getInsDatsId();
				
				if (!insDatsIdToDatsIds.containsKey(dd.getInsDatsId())){
					insDatsIdToDatsIds.put(dd.getInsDatsId(), new HashSet<Integer>());
				}
				insDatsIdToDatsIds.get(dd.getInsDatsId()).add(datsId);
			}
		}
		insDatsIdStr = insDatsIdStr.substring(1);
	}
	
	public FichierAmesBase(RequeteDataset requete,Connection dbCon,int requeteId) throws IOException,SQLException {
		this(dbCon,requeteId,requete);
				
		this.destPath = new File(destPath + "/" + requete.getDatsId());
				
		if (destPath.mkdir()){
			logger.info("Directory " + destPath + " successfully created.");
		}
		
		if ( !destPath.isDirectory() ){
			throw new IOException("Directory " + destPath + " doesn't exist");
		}
	}
		
	@Override
	protected void open() throws IOException {
		logger.debug("open()");
		amesFile = AmesFileFactory.getAmesFile(getFile(), getFFI());
	}
	
	@Override
	protected void closeFile() {
		logger.debug("closeFile()");
		amesFile.close();
	}

	protected abstract String getFilename() ;
	
	protected abstract String getFFI();
	protected abstract void addSpecialComments() throws AmesException;
	
	protected abstract void addAdditionalPrimaryVariables() throws AmesException;
	protected abstract void addAdditionalIndependentVariables() throws AmesException;
	protected abstract void addAuxiliaryVariables() throws AmesException;
		
	
	protected void addCompleteLocalisationPrimaryVariables() throws AmesException {
		amesFile.addPrimaryVariable(new AmesDataVar("Longitude","degrees_east","lon",1,MISSING_LON));
		amesFile.addPrimaryVariable(new AmesDataVar("Latitude","degrees_north","lat",1,MISSING_LAT));
		amesFile.addPrimaryVariable(new AmesDataVar("Altitude","m","alt",1,MISSING_ALT));
		amesFile.addPrimaryVariable(new AmesDataVar("Height above ground","m","height",1,MISSING_HEIGHT));
	}
	
	protected void addCompleteLocalisation(AmesDataRecord1D record, Localisation loc){
		record.getPrimaryVariables().add(LocalisationConverter.latLonIntToDouble(loc.getBoundings().getEast()));
		record.getPrimaryVariables().add(LocalisationConverter.latLonIntToDouble(loc.getBoundings().getNorth()));
		if (loc.getAlt() != Constantes.INT_NULL){
			record.getPrimaryVariables().add(LocalisationConverter.altIntToDouble(loc.getAlt()));
		}else{
			record.getPrimaryVariables().add(null);
		}				
		if (loc.getHauteurSol() != Constantes.INT_NULL){
			record.getPrimaryVariables().add(LocalisationConverter.altIntToDouble(loc.getHauteurSol()));
		}else{
			record.getPrimaryVariables().add(null);
		}
	}
	
	/* GESTION DES FLAGS */
	
	/**
	 * Ecrit le fichier contenant les flags sur les données (code: flag).
	 */
	private void createFlagFile() throws IOException,SQLException{
		if (requete.isWithFlag()){
			File flagsFile = new File(destPath +  "/FLAGS.txt");
			logger.info("Create " + flagsFile.getAbsolutePath());
			PrintStream out = new PrintStream(new FileOutputStream(flagsFile), true, Props.FILE_ENCODING);
			
			for (Integer flagId: flags){
				Flag flag = FlagDAO.getService().getById(dbCon, flagId);
				if (flag != null){
					out.println(flag.getFlagId() + ": " + flag.getFlagName());
				}
			}
			
			out.println(AmesDataFlagVar.MISSING + ": No flag");
			out.close();
			addDocumentationFile(flagsFile);
		}
	}
	
	/**
	 * Ajoute les valeurs au fichier (+ flag et delta si demandés dans la requête).
	 * @param valeurs
	 * @param record
	 */
	protected void addValeurs(Collection<Valeur> valeurs, AmesDataRecord1D record){
		for (int varId : requete.getVarIds()) {
			boolean valeurTrouvee = false;
			for (Valeur valeur: valeurs){
				if (varId == valeur.getVarId()){
					record.getPrimaryVariables().add(valeur.getValeur());

					if ( requete.isWithDelta() ){
						if (valeur.getDelta() != Constantes.DOUBLE_NULL){
							record.getPrimaryVariables().add(valeur.getDelta());
						}else{
							record.getPrimaryVariables().add(null);
						}
					}

					if ( requete.isWithFlag() ){
						if (valeur.getFlagQualityId() != Constantes.INT_NULL){
							record.getPrimaryVariables().add(valeur.getFlagQualityId());
							flags.add(valeur.getFlagQualityId());
						}else{
							record.getPrimaryVariables().add(null);
						}
					}

					valeurTrouvee = true;
					break;
				}
			}
			if (!valeurTrouvee){
				record.getPrimaryVariables().add(null);
				
				if (requete.isWithDelta()){
					record.getPrimaryVariables().add(null);
				}
				if (requete.isWithFlag()){
					record.getPrimaryVariables().add(null);
				}
			}
		}
	}
		
	@Override
	protected void printHeader() throws AmesException,SQLException {
		ContactsJeu contacts = null;
		if (contactsList.size() == 1){
			contacts = contactsList.iterator().next();
			this.writeReadme = false;
		}
		
		if (contacts != null){
			Contact firstContact;
			if (contacts.getContacts().isEmpty()){
				logger.warn("No Contact for dataset " + contacts.getJeu().getDatsId());
				amesFile.init(Props.DEFAULT_AUTHOR,Props.DEFAULT_ORGANISM,requete.getProjet() + " database", contacts.getJeu().getDatsTitle(), requete.getDateMin(), requete.getDate());
			}else{
				firstContact = contacts.getContacts().iterator().next();
				amesFile.init(firstContact.getNom(), firstContact.getOrganisme(), 
						requete.getProjet() + " database", contacts.getJeu().getDatsTitle(), requete.getDateMin(), requete.getDate());
			}
		}else{
			amesFile.init(Props.DEFAULT_AUTHOR, Props.DEFAULT_ORGANISM, requete.getProjet() + " database", "Multiple datasets (see README)", requete.getDateMin(), requete.getDate());
		}
				
		addAdditionalIndependentVariables();
		amesFile.addIndependentVariable(new AmesTimeVar(requete.getDateMin()));
						
		//Variables
		//TODO flags
		PreparedStatement stmt = dbCon.prepareStatement(
		"SELECT max(val_max) FROM data_availability WHERE var_id = ?");
		
		addAdditionalPrimaryVariables();
		if (contacts != null){	
			amesFile.addNormalComment(COMMENT_SEPARATOR);

			if (contacts.getJeu().getDoi() != null){
				amesFile.addNormalComment("DOI: " + contacts.getJeu().getDoi());
			}
			amesFile.addNormalComment("Contacts:");
			for (Contact contact: contacts.getContacts()){
				amesFile.addNormalComment("- " + contact.toString());
			}
			if (contacts.getJeu().getUseConstraints() != null){
				amesFile.addNormalComment("Use Constraints:");
				amesFile.addNormalComment(contacts.getJeu().getUseConstraints());
			}
		}
		amesFile.addNormalComment(COMMENT_SEPARATOR);
		amesFile.addNormalComment("GCMD science keywords:");
		
		SortedSet<String> keywords = new TreeSet<String>();
		
		for (int varId : requete.getVarIds()) {
			Param param = ParamDAO.getService().getById(dbCon, varId);
			Variable var = VariableDAO.getService().getById(dbCon,varId);
			
			stmt.setInt(1, varId);
			
			logger.debug("query: " + stmt);
			
			ResultSet rs = stmt.executeQuery();
			double valAbs = AmesFile.DEFAULT_VMISS;
			if (rs.next()) {
				double max = rs.getDouble("max");
				logger.debug("max " + varId + ": " + max);
				valAbs = AmesUtils.calculValAbs(max);
			}
			
			GcmdScienceKeyword gcmd = GcmdScienceKeywordDAO.getService().getById(dbCon, var.getGcmdId(), true);
			Unit unit = UnitDAO.getService().getById(dbCon, param.getUnitId());
			String varName = null;
			if (var.getVarName() != null && !var.getVarName().isEmpty()){
				varName = var.getVarName();
			}else{
				varName = gcmd.getGcmdName();
			}
			
			AmesDataVar amesVar = new AmesDataVar(varName, unit.getUnitCode(),param.getParamCode(),1,valAbs);
			amesFile.addPrimaryVariable(amesVar);		
			
			if (requete.isWithDelta()){
				amesFile.addPrimaryVariable(new AmesDataVar(varName, unit.getUnitCode(),"delta_" + param.getParamCode(),1,valAbs));
			}
			
			if (requete.isWithFlag()){
				amesFile.addPrimaryVariable(new AmesDataFlagVar(amesVar));
			}
									
			keywords.add(gcmd.toString());
		}			
		stmt.close();
		for(String keyword: keywords){
			amesFile.addNormalComment(keyword);
		}
		addAuxiliaryVariables();
		amesFile.addColumnHeaders();
						
		addSpecialComments();
				
		if (contacts != null){
			int nbCapteurs = contacts.getJeu().getCapteurs().size(); 
			if (nbCapteurs > 0){
				amesFile.addNormalComment(COMMENT_SEPARATOR);
			}
			
			if (nbCapteurs > 1){
				amesFile.addNormalComment("Instrument information");
			}
			int cpt = 1;
			for (Sensor sensor: contacts.getJeu().getCapteurs()){
				if (nbCapteurs > 1){
					amesFile.addNormalComment("");
					amesFile.addNormalComment("Instrument " + cpt);
					cpt++;
				}

				if (sensor.getGcmdSensorId() != Constantes.INT_NULL){
					GcmdInstrumentKeyword gcmd = GcmdInstrumentKeywordDAO.getService().getById(dbCon, sensor.getGcmdSensorId());
					amesFile.addNormalComment("Instrument type: " + gcmd.getGcmdSensorName());
				}

				if (sensor.getManufacturerId() != Constantes.INT_NULL){
					Manufacturer man = ManufacturerDAO.getService().getById(dbCon, sensor.getManufacturerId());
					amesFile.addNormalComment("Manufacturer: " + man.getManufacturerName());
				}

				if (sensor.getSensorModel() != null){
					amesFile.addNormalComment("Model: " + sensor.getSensorModel());
				}

			}
		}
		amesFile.addNormalComment(COMMENT_SEPARATOR);
		
		if (requete.isWithFlag()){
			amesFile.addNormalComment("Quality flags: see FLAGS.txt");
			amesFile.addNormalComment(COMMENT_SEPARATOR);
		}
		
		amesFile.writeHeader();
	}
			
	/**
	 * Créé un fichier contenant la liste des contacts associés aux données.
	 * @throws IOException
	 * @throws SQLException
	 */
	private void createReadme() throws IOException,SQLException{
		File readme = new File(destPath +  "/README");
		logger.info("Create " + readme.getAbsolutePath());
		PrintStream out = new PrintStream(new FileOutputStream(readme), true, Props.FILE_ENCODING);
		
		out.println("The results contain data from the following datasets:");
		out.println();
		
		for (ContactsJeu contacts: contactsList){
			if (datasets.contains(contacts.getJeu().getDatsId())){
				out.println(" * " + contacts.getJeu().getDatsTitle());
				
				if (contacts.getJeu().getDoi() != null){
					out.println("DOI: " + contacts.getJeu().getDoi());
				}
				out.println("Contacts:");
				for (Contact contact: contacts.getContacts()){
					out.println("- " + contact.toString());
				}
								
				if (contacts.getJeu().getUseConstraints() != null){
					out.println("Use Constraints:");
					out.println(contacts.getJeu().getUseConstraints());
				}
				out.println();				
			}
		}
		
		out.close();
		addDocumentationFile(readme);
	}
	
	@Override
	protected int writeData() throws SQLException,IOException,DataNotFoundException {
		int cptMes = executeRequete();
		logger.info("Mesures: " + cptMes);
		
		createFlagFile();
		if (writeReadme){
			createReadme();
		}
		return cptMes;
	}
		
	protected abstract int executeRequete() throws SQLException,IOException,DataNotFoundException;

	@Override
	public Set<ContactsJeu> getContacts() {
		return contactsList;
	}

}
