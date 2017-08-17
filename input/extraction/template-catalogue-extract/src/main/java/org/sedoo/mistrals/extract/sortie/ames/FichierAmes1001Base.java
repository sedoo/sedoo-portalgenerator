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
import java.util.SortedSet;
import java.util.TreeSet;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.beans.Localisation;
import org.sedoo.mistrals.bd.beans.Mesure;
import org.sedoo.mistrals.bd.beans.Place;
import org.sedoo.mistrals.bd.beans.Valeur;
import org.sedoo.mistrals.bd.dao.LocalisationDAO;
import org.sedoo.mistrals.bd.dao.MesureDAO;
import org.sedoo.mistrals.bd.dao.PlaceDAO;
import org.sedoo.mistrals.bd.dao.ValeurDAO;
import org.sedoo.mistrals.extract.Props;
import org.sedoo.mistrals.extract.requetes.RequeteBase;
import org.sedoo.mistrals.extract.requetes.RequeteDataset;
import org.sedoo.utils.DateUtils;
import org.sedoo.utils.ames.AmesDataRecord1D;
import org.sedoo.utils.ames.AmesDataVar;
import org.sedoo.utils.ames.AmesException;
import org.sedoo.utils.ames.AmesFile;
import org.sedoo.utils.exceptions.DataNotFoundException;

public class FichierAmes1001Base extends FichierAmesBase {

	private static Logger logger = Logger.getLogger(FichierAmes1001Base.class);
	
	private SortedSet<Integer> places;
	private boolean splitByDataset;
	
	protected FichierAmes1001Base(Connection dbCon, int requeteId,RequeteBase requete) throws SQLException {
		super(dbCon, requeteId, requete);
		this.places = new TreeSet<Integer>();
		this.splitByDataset = false;
	}
	
	protected FichierAmes1001Base(RequeteDataset requete, Connection dbCon,int requeteId) throws IOException, SQLException {
		super(requete, dbCon, requeteId);
		this.places = new TreeSet<Integer>();
		this.splitByDataset = true;
	}
	
	@Override
	protected String getFFI() {
		return "1001";
	}

	@Override
	protected String getFilename() {
		return "data" + AmesFile.EXTENSION;
	}
	
	@Override
	protected void addAdditionalIndependentVariables() throws AmesException {}

	@Override
	protected void addAuxiliaryVariables() throws AmesException {}

	@Override
	protected void addSpecialComments() throws AmesException {}
	
	@Override
	protected void addAdditionalPrimaryVariables() throws AmesException {
		if (!splitByDataset){
			amesFile.addPrimaryVariable(new AmesDataVar("Dataset id",null,"dts_id",1,9999999));
		}
		amesFile.addPrimaryVariable(new AmesDataVar("Station id","see STATIONS.txt","sta_id",1,9999999));
		addCompleteLocalisationPrimaryVariables();
	}

	@Override
	protected int executeRequete() throws SQLException, IOException, DataNotFoundException {		
		PreparedStatement stmt = requete.toSQLMesures(dbCon, "mesure_date, mesure_id"); 
		
		logger.debug("queryMesures: " + stmt);
		ResultSet rs = stmt.executeQuery();
		int cptMes = 0;
		while (rs.next()) {
			Mesure mesure = MesureDAO.buildMesure(rs);
			Collection<Valeur> valeurs = ValeurDAO.getService().getByMesureIdAndVarIds(dbCon,mesure.getMesureId(),requete.getVarIds());
			AmesDataRecord1D record = new AmesDataRecord1D();
			
			if (valeurs.isEmpty()) {
				continue;
			}else{
				if (!splitByDataset){
					record.getPrimaryVariables().add(mesure.getDatsId());
					datasets.addAll(insDatsIdToDatsIds.get(mesure.getDatsId()));
				}
				record.getPrimaryVariables().add(mesure.getPlaceId());								
				
				places.add(mesure.getPlaceId());
								
				Localisation localisation = LocalisationDAO.buildLocalisation(rs);
				addCompleteLocalisation(record, localisation);
				record.setIndependentVariable(DateUtils.distanceSecondes(requete.getDateMin(), mesure.getMesureDate()));
				
				cptMes++;
				
				addValeurs(valeurs, record);
								
			}
						
			amesFile.write(record, rs.isLast());
			
			if (testKilled(cptMes)){
				break;
			}
		}
				
		stmt.close();
		
		
		//Stations
		File stationsFile = new File(destPath +  "/STATIONS.txt");
		logger.info("Create " + stationsFile.getAbsolutePath());
		PrintStream out = new PrintStream(new FileOutputStream(stationsFile), true, Props.FILE_ENCODING);
		for (Integer placeId: places){
			Place place = PlaceDAO.getService().getById(dbCon, placeId);
			if (place != null){
				out.println(place.getPlaceId() + ": " + place.getPlaceName());
			}
		}
		out.close();
		addDocumentationFile(stationsFile);	
		return cptMes;
	}
	
}
