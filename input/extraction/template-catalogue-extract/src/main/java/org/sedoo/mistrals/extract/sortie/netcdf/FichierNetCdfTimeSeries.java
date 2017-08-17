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
import org.sedoo.mistrals.extract.requetes.RequeteDataset;
import org.sedoo.utils.Constantes;
import org.sedoo.utils.DateUtils;
import org.sedoo.utils.LocalisationConverter;
import org.sedoo.utils.exceptions.DataNotFoundException;

import ucar.ma2.ArrayDouble;
import ucar.ma2.ArrayObject;
import ucar.ma2.InvalidRangeException;

/**
 * Pour un ensemble de stations fixes (N stations d'un même jeu dans le même fichier) :
 * - Feature Type : timeSeries
 * - 2 dimensions : station, time.
 * - localisations : variables lat(station) et lon(station)
 * - 1 variable pour conserver le nom de chaque station (utilisé comme timeseries_id)
 * - coordinates : lat lon (time étant une dimension, pas utile de le repréciser ici)
 * 
 * TODO Si les stations ne sont pas complètement fixes (exemple : BUOYS), utiliser des variables precise_lat(time,station) et precise_lon(time,station).
 * 
 * @see <a href="http://cf-pcmdi.llnl.gov/documents/cf-conventions/1.6/cf-conventions.html#idp8307552" />
 * 
 * @author brissebr
 */
public class FichierNetCdfTimeSeries extends FichierNetCdfBase {

	private static Logger logger = Logger.getLogger(FichierNetCdfTimeSeries.class);
	
	public FichierNetCdfTimeSeries(RequeteDataset requete, Connection dbCon, int requeteId, ExtractConfig conf) throws SQLException {
		super(requete, dbCon, requeteId, conf);		
	}

	@Override
	protected void addDimensions() {
		fichierNetcdf.addDimension("station",requete.getPlaceIds().size());
		fichierNetcdf.addUnlimitedDimension("time");
	}

	@Override
	protected String getDimensions() {
		//TODO incorrect, à remplacer par "station time"
		return "time station";
	}

	@Override
	protected void addCoordinateVars() throws IOException {
		try{
			fichierNetcdf.addTimeVariable(requete.getDateMin());
			
			fichierNetcdf.addMeasuredParam("lat", "latitude", "station latitude", "degrees_north", "station");
			fichierNetcdf.addMeasuredParam("lon", "longitude", "station longitude", "degrees_east", "station");
			fichierNetcdf.addMeasuredParam("alt", "altitude", "station altitude", "m", "station");
			
			
			fichierNetcdf.addStringVariable("station_name", "station", 100);
			fichierNetcdf.addVariableAttribute("station_name", "long_name", "station name");
			fichierNetcdf.addVariableAttribute("station_name", "cf_role", "timeseries_id");
			
			if (conf.isMobileStations()){
				addPreciseLatLon();
			}
		}catch(DataNotFoundException e){
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
	public int writeData() throws IOException,SQLException,DataNotFoundException {
		logger.debug("writeData()");

		//TODO Ne pas filtrer selon les localisations (déjà fait par les place_id)
		PreparedStatement stmt = requete.toSQLMesures(dbCon, "mesure_date",false,false,true); 

		logger.info("queryMesures: " + stmt);
		ResultSet rs = stmt.executeQuery();
		int cptMes = 0;
		Date prevDate = null, currDate = null;
		
		ArrayDouble.D1 lat = new ArrayDouble.D1(requete.getPlaceIds().size());
		ArrayDouble.D1 lon = new ArrayDouble.D1(requete.getPlaceIds().size());
		ArrayDouble.D1 alt = new ArrayDouble.D1(requete.getPlaceIds().size());
		ArrayObject.D1 sta = new ArrayObject.D1(String.class,requete.getPlaceIds().size());

		//Init à valeur absente
		for (int i = 0;i < requete.getPlaceIds().size();i++) {
			lat.set(i, FichierNetCdf.MISSING_VALUE);
			lon.set(i, FichierNetCdf.MISSING_VALUE);
			alt.set(i, FichierNetCdf.MISSING_VALUE);
		}
		
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
				
				int indiceStation = -1;
				for (int i = 0;i < requete.getPlaceIds().size();i++) {
					if (requete.getPlaceIds().get(i) == mesure.getPlaceId()){
						indiceStation = i;
						double latitude = LocalisationConverter.latLonIntToDouble(localisation.getBoundings().getNorth());
						double longitude = LocalisationConverter.latLonIntToDouble(localisation.getBoundings().getEast());
						
						if (conf.isMobileStations()){
							fichierNetcdf.setValue("precise_lat",indiceStation,latitude);
							fichierNetcdf.setValue("precise_lon",indiceStation,longitude);
						}
						
						//On renseigne les coordonnées de la station (une seule fois vu que les stations sont fixes)
						if (lat.get(i) == FichierNetCdf.MISSING_VALUE){						
							lat.set(i, latitude);
							Place place = PlaceDAO.getService().getById(dbCon, mesure.getPlaceId());
							sta.set(i, place.getPlaceName());

							lon.set(i,  longitude);
							if (localisation.getAlt() != Constantes.INT_NULL){
								alt.set(i,  LocalisationConverter.altIntToDouble(localisation.getAlt()));
							}
						}else if( !conf.isMobileStations() &&
								( (lat.get(i) != latitude) || (lon.get(i) != longitude) ) ){
							logger.error("Station " + mesure.getPlaceId() + " is not fixed");
						}										
						break;
					}
				}
				
				//Remarque : dans fichierNetCdf, les variables sont initialisées à valeur absente => pas utile de les gérer ici
				for (int varId : requete.getVarIds()) {
					for (Valeur valeur: valeurs){
						if (varId == valeur.getVarId()){
							//Vérifier qu'il n'y a pas déjà une valeur
							if (fichierNetcdf.getValue(varNames.get(varId),indiceStation) != FichierNetCdf.MISSING_VALUE){
								logger.warn("Multiple values for place " + mesure.getPlaceId() + " and variable " + varId + " (mesure: " + mesure.getMesureId() + ")");
							}
							
							fichierNetcdf.setValue(varNames.get(varId),indiceStation,valeur.getValeur());
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
		
		for (int i = 0;i < sta.getSize();i++){
			logger.debug(i + ": " + sta.get(i));
			if (sta.get(i) == null){
				Place place = PlaceDAO.getService().getById(dbCon, requete.getPlaceIds().get(i));
				sta.set(i, place.getPlaceName());
			}
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
