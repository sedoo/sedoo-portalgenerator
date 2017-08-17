package org.sedoo.mistrals.extract.sortie.netcdf;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collection;
import java.util.Date;

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

public class FichierNetCdfSingleTimeSeries extends FichierNetCdfBase {

	private static Logger logger = Logger.getLogger(FichierNetCdfSingleTimeSeries.class);
	
	private Place place;
		
	public FichierNetCdfSingleTimeSeries(RequeteDatasetStation requete, Connection dbCon, int requeteId, ExtractConfig conf) throws SQLException {
		super(requete, dbCon, requeteId, conf);
		this.place = PlaceDAO.getService().getById(dbCon, requete.getPlaceId());
	}

	@Override
	protected String getFilename() {
		if (conf.isSingleStation()){
			return super.getFilename();
		}else{
			return conf.getDatsShortName() + "-" + place.getPlaceName().replaceAll("/", "_").replaceAll(" ", "_") + FichierNetCdf.EXTENSION;
		}
	}
	
	@Override
	protected void addDimensions() {
		fichierNetcdf.addUnlimitedDimension("time");
	}

	@Override
	protected String getDimensions() {
		return "time";
	}

	@Override
	protected void addCoordinateVars() throws IOException {
		try{
			fichierNetcdf.addTimeVariable(requete.getDateMin());
			
			fichierNetcdf.addMeasuredParam("lat", "latitude", "station latitude", "degrees_north", "");
			fichierNetcdf.addMeasuredParam("lon", "longitude", "station longitude", "degrees_east", "");
			fichierNetcdf.addMeasuredParam("alt", "altitude", "station altitude", "m", "");
									
			fichierNetcdf.addStringVariable("station_name", "", 100);
			fichierNetcdf.addVariableAttribute("station_name", "long_name", "station name");
			fichierNetcdf.addVariableAttribute("station_name", "cf_role", "timeseries_id");		
			
			if (conf.isMobileStations()){
				addPreciseLatLon();
			}
			
		}catch(DataNotFoundException e){
			LogUtils.logException(logger, e);
			throw new IOException("Error while creating time and location variables",e);
		}

	}

	@Override
	protected void addGlobalAttributes() throws IOException {
		fichierNetcdf.addGlobalAttribute("cdm_data_type", "Station");	
	}
	
	@Override
	protected String getFeatureType() {
		return "timeSeries";
	}

	@Override
	protected String getCoordinates() {
		return "lat lon";
	}

	@Override
	protected int writeData() throws IOException, SQLException, DataNotFoundException {
		logger.debug("writeData()");
	
		PreparedStatement stmt = requete.toSQLMesures(dbCon, "mesure_date",false,false,true); 

		logger.info("queryMesures: " + stmt);
		ResultSet rs = stmt.executeQuery();
		int cptMes = 0;
		Date prevDate = null, currDate = null;
		
		ArrayDouble.D0 lat = new ArrayDouble.D0();
		ArrayDouble.D0 lon = new ArrayDouble.D0();
		ArrayDouble.D0 alt = new ArrayDouble.D0();
		ArrayObject.D0 sta = new ArrayObject.D0(String.class);

		lat.set(FichierNetCdf.MISSING_VALUE);
		lon.set(FichierNetCdf.MISSING_VALUE);
		alt.set(FichierNetCdf.MISSING_VALUE);
		
		while (rs.next()) {
			Mesure mesure = MesureDAO.buildMesure(rs);
			Collection<Valeur> valeurs = ValeurDAO.getService().getByMesureIdAndVarIds(dbCon,mesure.getMesureId(),requete.getVarIds());
			if (valeurs.isEmpty()) {
				continue;
			}else{
				prevDate = currDate;
				currDate = mesure.getMesureDate();
				if (prevDate == null || currDate.after(prevDate)){
					if (prevDate != null){
						fichierNetcdf.next();
					}
					fichierNetcdf.setValue("time", DateUtils.distanceSecondes(requete.getDateMin(), mesure.getMesureDate()));
				}else if (currDate.before(prevDate)){
					throw new IOException("Dates incorrectes.");
				}
				
				//Localisation localisation = LocalisationDAO.buildLocalisation(rs);
				Localisation localisation = LocalisationDAO.getService().getById(dbCon, mesure.getLocalisationId());
				
				double latitude = LocalisationConverter.latLonIntToDouble(localisation.getBoundings().getNorth());
				double longitude = LocalisationConverter.latLonIntToDouble(localisation.getBoundings().getEast());
				
				if (conf.isMobileStations()){
					fichierNetcdf.setValue("precise_lat",latitude);
					fichierNetcdf.setValue("precise_lon",longitude);
				}
				//On renseigne les coordonnées de la station (une seule fois vu que les stations sont fixes)
				if (lat.get() == FichierNetCdf.MISSING_VALUE){
					lat.set(latitude);
					sta.set(place.getPlaceName());
					lon.set(longitude);
					if (localisation.getAlt() != Constantes.INT_NULL){
						alt.set(LocalisationConverter.altIntToDouble(localisation.getAlt()));
					}
				}else if( !conf.isMobileStations() && 
						( (lat.get() != latitude) || (lon.get() != longitude) ) ){
					logger.error("Station " + mesure.getPlaceId() + " is not fixed");
				}										
						
				for (int varId : requete.getVarIds()) {
					for (Valeur valeur: valeurs){
						if (varId == valeur.getVarId()){
							//Vérifier qu'il n'y a pas déjà une valeur
							if (fichierNetcdf.getValue(varNames.get(varId)) != FichierNetCdf.MISSING_VALUE){
								logger.warn("Multiple values for place " + place.getPlaceId() + " and variable " + varId + " (mesure: " + mesure.getMesureId() + ")");
							}
							fichierNetcdf.setValue(varNames.get(varId),valeur.getValeur());
							//TODO flag, delta
							break;	
						}
					}
				}
							
				cptMes++;
								
				//Pour le suivi de l'avancement de la requete et l'arrêt anticipé.
				if (testKilled(cptMes)){
					logger.info("Arrêt demandé");
					break;
				}
			}
		}
		if (cptMes > 0){
			fichierNetcdf.next();
		}
				
		if (sta.get() == null){
			logger.warn("Nom de la station est null");
		}
		
		try{
			fichierNetcdf.addStringValues("station_name", sta);
			fichierNetcdf.addValues("lat", lat);
			fichierNetcdf.addValues("lon", lon);
			fichierNetcdf.addValues("alt", alt);
		}catch(InvalidRangeException e){
			throw new IOException("Error while writing data",e);
		}

		return cptMes;
	}

}
