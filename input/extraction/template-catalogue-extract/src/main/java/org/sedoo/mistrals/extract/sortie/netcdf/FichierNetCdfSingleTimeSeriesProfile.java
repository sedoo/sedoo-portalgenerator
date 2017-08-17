package org.sedoo.mistrals.extract.sortie.netcdf;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.Collections;
import java.util.List;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.beans.ExtractConfig;
import org.sedoo.mistrals.bd.beans.Localisation;
import org.sedoo.mistrals.bd.beans.Mesure;
import org.sedoo.mistrals.bd.beans.Place;
import org.sedoo.mistrals.bd.beans.Valeur;
import org.sedoo.mistrals.bd.dao.LocalisationDAO;
import org.sedoo.mistrals.bd.dao.MesureDAO;
import org.sedoo.mistrals.bd.dao.PlaceDAO;
import org.sedoo.mistrals.bd.dao.ValeurDAO;
import org.sedoo.mistrals.extract.requetes.RequeteDatasetStation;
import org.sedoo.utils.Constantes;
import org.sedoo.utils.DateUtils;
import org.sedoo.utils.LocalisationConverter;
import org.sedoo.utils.exceptions.DataNotFoundException;
import org.sedoo.utils.log.LogUtils;

import ucar.ma2.ArrayDouble;
import ucar.ma2.ArrayObject;
import ucar.ma2.InvalidRangeException;

/**
 * Série temporelle de profils effectués en 1 point et toujours aux même niveaux.
 * 
 * TODO Cas où c'est une variable qui contient les niveaux verticaux (pressure)
 * 
 * @author brissebr
 */
public class FichierNetCdfSingleTimeSeriesProfile extends FichierNetCdfBase {

	private static Logger logger = Logger.getLogger(FichierNetCdfSingleTimeSeriesProfile.class);
	
	private Place place;
	
	//private List<Sequence> sequences;
	
	private List<Double> levels;
	private boolean negativeLevels = false;
	
	/**
	 * 
	 * @return true si le nombre de niveaux est nul.
	 */
	public boolean isEmpty(){
		return levels.size() == 0;
	}
	
	public FichierNetCdfSingleTimeSeriesProfile(RequeteDatasetStation requete,Connection dbCon, int requeteId, ExtractConfig conf) throws SQLException {
		super(requete, dbCon, requeteId, conf);
		
		this.place = PlaceDAO.getService().getById(dbCon, requete.getPlaceId());
						
		//Déterminer les niveaux		
		levels = new ArrayList<Double>();
		PreparedStatement stmt = dbCon.prepareStatement(
				"SELECT DISTINCT localisation_alt FROM mesure JOIN localisation USING (localisation_id) " +
				"WHERE " + requete.getWhereDataset() + " AND place_id IN (?) " +
				"AND sequence_id IS NOT NULL");
		stmt.setInt(1, place.getPlaceId());
		logger.debug(stmt);
		ResultSet rs = stmt.executeQuery();
		int cptNegative = 0;
		while(rs.next()){
			double alt = LocalisationConverter.altIntToDouble(rs.getInt("localisation_alt"));
			if (alt <= 0){
				cptNegative++;
			}
			levels.add(alt);
		}
		Collections.sort(levels);
		
		//Si toutes les valeurs sont négatives, on inverse l'ordre
		if (cptNegative == levels.size()){
			negativeLevels = true;
			Collections.reverse(levels);
		}
		
		if (levels.size() == 0){
			logger.info("Empty profile");
		}
			
		rs.close();
		stmt.close();
		
		
		//Séquences
	/*	stmt = dbCon.prepareStatement(
				"SELECT DISTINCT sequence_id FROM mesure JOIN localisation USING (localisation_id) " +
				"WHERE " + requete.getWhereDataset() + " AND place_id IN (?) " +
				"AND sequence_id IS NOT NULL");
		stmt.setInt(1, place.getPlaceId());
		logger.debug(stmt);
		rs = stmt.executeQuery();
		while(rs.next()){
			
			sequences.add(SequenceDAO.buildSequence(rs));
			
		}*/
		
		/*PreparedStatement stmt = dbCon.prepareStatement(
				"SELECT sequence_id, count(*) as nbMesures FROM mesure " +
				"WHERE " + requete.get + "ins_dats_id = 45 AND place_id = 9188 AND sequence_id IS NOT NULL GROUP BY sequence_id;")
		*/
				
			
				
		
	}
	
	@Override
	protected String getFilename() {
		return conf.getDatsShortName() + "-" + place.getPlaceName().replaceAll("/", "_").replaceAll(" ", "_") + "-profile" + FichierNetCdf.EXTENSION;
	}

	@Override
	protected void addDimensions() {
		//fichierNetcdf.addDimension("profile",sequences.size());
		fichierNetcdf.addUnlimitedDimension("time");
		fichierNetcdf.addDimension("z", levels.size());
	}

	@Override
	protected String getDimensions() {
		return "time z";
	}

	@Override
	protected void addCoordinateVars() throws IOException {
		try{
									
			//Infos sur la station
			fichierNetcdf.addMeasuredParam("lat", "latitude", "station latitude", "degrees_north", "");
			fichierNetcdf.addMeasuredParam("lon", "longitude", "station longitude", "degrees_east", "");
			
			fichierNetcdf.addStringVariable("station_name", "", 100);
			fichierNetcdf.addVariableAttribute("station_name", "long_name", "station name");
			fichierNetcdf.addVariableAttribute("station_name", "cf_role", "timeseries_id");
			
			//Id du profil TODO
						
			//Heure premier point
			//TODO conserver l'heure pour chaque niveau ?
			fichierNetcdf.addTimeVariable(requete.getDateMin());
			fichierNetcdf.addVariableAttribute("time", "cf_role", "profile_id");
									
			if (negativeLevels){
				fichierNetcdf.addMeasuredParam("z","depth","vertical distance below the surface","m","z");				
				fichierNetcdf.addVariableAttribute("z", "positive", "down");
			}else{
				fichierNetcdf.addMeasuredParam("z","altitude","height above mean sea level","m","z");
				fichierNetcdf.addVariableAttribute("z", "positive", "up");
			}
			fichierNetcdf.addVariableAttribute("z", "axis", "Z");
			
			if (conf.isMobileStations()){
				addPreciseLatLon();
			}
			
		}catch(DataNotFoundException e){
			LogUtils.logException(logger, e);
			throw new IOException("Error while creating time and location variables",e);
		}
	}

	@Override
	protected String getFeatureType() {
		return "timeSeriesProfile";
	}

	@Override
	protected String getCoordinates() {
		return "time lon lat alt";
	}

	@Override
	protected void addGlobalAttributes() throws IOException {
		//TODO cdm_data_type
	}

	@Override
	protected int writeData() throws IOException, SQLException,DataNotFoundException {

		if (isEmpty()){
			return 0;
		}
		
		if (levels.size() == 1){
			logger.warn("Size of profile: " + levels.size());
		}
		
		ArrayObject.D0 sta = new ArrayObject.D0(String.class);
		sta.set(place.getPlaceName());
		
		ArrayDouble.D0 lat = new ArrayDouble.D0();
		ArrayDouble.D0 lon = new ArrayDouble.D0();
		lat.set(FichierNetCdf.MISSING_VALUE);
		lon.set(FichierNetCdf.MISSING_VALUE);		
		
		ArrayDouble.D1 alt = new ArrayDouble.D1(levels.size());
		int l = 0;
		for (double level:levels){
			if (negativeLevels){
				alt.set(l++, Math.abs(level));
			}else{
				alt.set(l++, level);
			}
		}
		
		PreparedStatement stmt = dbCon.prepareStatement("" +
				"SELECT * FROM mesure WHERE " + requete.getWhereDataset() + " AND place_id = ? " +
						"AND mesure_date BETWEEN ? AND ? AND sequence_id IS NOT NULL " +
						"ORDER BY sequence_id, mesure_date, mesure_id");
		stmt.setInt(1, place.getPlaceId());
		stmt.setTimestamp(2, requete.getDateMin());
		stmt.setTimestamp(3, requete.getDateMax());
		logger.debug(stmt);
		ResultSet rs = stmt.executeQuery();
		
		int prevSequenceId = -1;
		int cptMes = 0;
		while (rs.next()) {
			Mesure mesure = MesureDAO.buildMesure(rs);
			cptMes++;
			
			Collection<Valeur> valeurs = ValeurDAO.getService().getByMesureIdAndVarIds(dbCon,mesure.getMesureId(),requete.getVarIds());
			if (valeurs.isEmpty()) {
				continue;
			}else{
				if (prevSequenceId != mesure.getSequenceId()){
					if (prevSequenceId != -1){
						fichierNetcdf.next();
					}
					prevSequenceId = mesure.getSequenceId();
					fichierNetcdf.setValue("time", DateUtils.distanceSecondes(requete.getDateMin(), mesure.getMesureDate()));
					//TODO profile_id
					//fichierNetcdf.setValue("profile",mesure.getSequenceId());
				}
				
				Localisation localisation = LocalisationDAO.getService().getById(dbCon, mesure.getLocalisationId());
				double latitude = LocalisationConverter.latLonIntToDouble(localisation.getBoundings().getNorth());
				double longitude = LocalisationConverter.latLonIntToDouble(localisation.getBoundings().getEast());
				
				if (localisation.getAlt() == Constantes.INT_NULL){
					throw new DataNotFoundException("Altitude is missing for mesure " + mesure.getMesureId());
				}
				
				double altitude = LocalisationConverter.altIntToDouble(localisation.getAlt());
				
				int indiceLevel = -1;
				for (int i = 0;i < levels.size();i++) {
					if (levels.get(i) == altitude){
						indiceLevel = i;
						break;
					}
				}
				
				if (indiceLevel == -1){
					throw new DataNotFoundException("Altitude is incorrect for mesure " + mesure.getMesureId());
				}
				
				if (conf.isMobileStations()){
					fichierNetcdf.setValue("precise_lat",indiceLevel,latitude);
					fichierNetcdf.setValue("precise_lon",indiceLevel,longitude);
				}
				//On renseigne les coordonnées de la station (une seule fois vu que les stations sont fixes)
				if (lat.get() == FichierNetCdf.MISSING_VALUE){
					lat.set(latitude);
					lon.set(longitude);
				}else if( !conf.isMobileStations() 
						&& ( (lat.get() != latitude) || (lon.get() != longitude) ) ){
					logger.error("Station " + mesure.getPlaceId() + " is not fixed");
				}		
										
				for (int varId : requete.getVarIds()) {
					for (Valeur valeur: valeurs){
						if (varId == valeur.getVarId()){
							//Vérifier qu'il n'y a pas déjà une valeur
							if (fichierNetcdf.getValue(varNames.get(varId),indiceLevel) != FichierNetCdf.MISSING_VALUE){
								logger.warn("Sequence: " + mesure.getSequenceId() + ": multiple values for level " + levels.get(indiceLevel) + " and variable " + varId);
							}
							
							fichierNetcdf.setValue(varNames.get(varId),indiceLevel,valeur.getValeur());
							//TODO flag, delta
							break;	
						}
					}
				}
				
			}
		}
		
		if (cptMes > 0){
			fichierNetcdf.next();
		}
				
		try{
			fichierNetcdf.addStringValues("station_name", sta);
			fichierNetcdf.addValues("lat", lat);
			fichierNetcdf.addValues("lon", lon);
			//fichierNetcdf.addValues("profile", profId);
			fichierNetcdf.addValues("z", alt);
		}catch(InvalidRangeException e){
			throw new IOException("Error while writing data",e);
		}
		
		return cptMes;
	}

}
