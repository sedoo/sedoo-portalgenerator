package org.sedoo.mistrals.extract.sortie.netcdf;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collection;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.beans.ExtractConfig;
import org.sedoo.mistrals.bd.beans.Localisation;
import org.sedoo.mistrals.bd.beans.Mesure;
import org.sedoo.mistrals.bd.beans.Valeur;
import org.sedoo.mistrals.bd.dao.LocalisationDAO;
import org.sedoo.mistrals.bd.dao.MesureDAO;
import org.sedoo.mistrals.bd.dao.ValeurDAO;
import org.sedoo.mistrals.extract.requetes.RequeteDataset;
import org.sedoo.utils.Constantes;
import org.sedoo.utils.DateUtils;
import org.sedoo.utils.LocalisationConverter;
import org.sedoo.utils.exceptions.DataNotFoundException;

/**
 * Pour un ensemble de points indépendants :
 * - Feature Type : point
 * - 1 dimensions : nb de mesures.
 * - time : variable time(obs)
 * - localisations : variables lat(obs) et lon(obs)
 * - coordinates : time lat lon 
 * 
 * Format le plus général, à utiliser par défaut.
 * 
 * @see <a href="http://cf-pcmdi.llnl.gov/documents/cf-conventions/1.6/cf-conventions.html#idp8294224" />
 * 
 * @author brissebr
 */
public class FichierNetCdfPoint extends FichierNetCdfBase {

	private static Logger logger = Logger.getLogger(FichierNetCdfPoint.class);
	
	public FichierNetCdfPoint(RequeteDataset requete, Connection dbCon,	int requeteId,ExtractConfig conf) throws SQLException {
		super(requete, dbCon, requeteId, conf);
	}

	@Override
	protected String getFeatureType() {
		return "point";
	}
	
	@Override
	protected void addGlobalAttributes() throws IOException {
		fichierNetcdf.addGlobalAttribute("cdm_data_type", "Point");		
	}
	
	@Override
	protected String getCoordinates() {
		return "time lat lon";
	}
	
	@Override
	protected void addDimensions() {
		fichierNetcdf.addUnlimitedDimension("obs");
	}
	
	@Override
	protected String getDimensions() {
		return "obs";
	}

	@Override
	protected void addCoordinateVars() throws IOException{
		try{
			fichierNetcdf.addTimeVariable(requete.getDateMin(), "obs");

			fichierNetcdf.addMeasuredParam("lat", "latitude", "latitude of the observation", "degrees_north", "obs");
			fichierNetcdf.addMeasuredParam("lon", "longitude", "longitude of the observation", "degrees_east", "obs");
			fichierNetcdf.addMeasuredParam("alt", "altitude", "altitude", "m", "obs");
			fichierNetcdf.addMeasuredParam("height", "height", "vertical distance above the surface", "m", "obs");
		}catch(DataNotFoundException e){
			throw new IOException("Error while creating time and localisation variables",e);
		}
	}
	
	@Override
	public int writeData() throws IOException,SQLException,DataNotFoundException {
		logger.debug("writeData()");
	
		PreparedStatement stmt = requete.toSQLMesures(dbCon, "mesure_date, mesure_id"); 

		logger.debug("queryMesures: " + stmt);
		ResultSet rs = stmt.executeQuery();
		int cptMes = 0;
		while (rs.next()) {
			Mesure mesure = MesureDAO.buildMesure(rs);
			Collection<Valeur> valeurs = ValeurDAO.getService().getByMesureIdAndVarIds(dbCon,mesure.getMesureId(),requete.getVarIds());
			if (valeurs.isEmpty()) {
				continue;
			}else{
				fichierNetcdf.setValue("time", DateUtils.distanceSecondes(requete.getDateMin(), mesure.getMesureDate()));
				
				Localisation localisation = LocalisationDAO.buildLocalisation(rs);
				fichierNetcdf.setValue("lat", LocalisationConverter.latLonIntToDouble(localisation.getBoundings().getNorth()));
				fichierNetcdf.setValue("lon", LocalisationConverter.latLonIntToDouble(localisation.getBoundings().getEast()));
				
				if (localisation.getAlt() != Constantes.INT_NULL){
					fichierNetcdf.setValue("alt", LocalisationConverter.altIntToDouble(localisation.getAlt()));
				}/*else{
					fichierNetcdf.addMissingValue("alt");
				}	*/			
				if (localisation.getHauteurSol() != Constantes.INT_NULL){
					fichierNetcdf.setValue("height", LocalisationConverter.altIntToDouble(localisation.getHauteurSol()));
				}/*else{
					fichierNetcdf.addMissingValue("height");
				}*/

				//TODO place name 
				
				for (int varId : requete.getVarIds()) {
					//boolean valeurTrouvee = false;
					for (Valeur valeur: valeurs){
						if (varId == valeur.getVarId()){
							fichierNetcdf.setValue(varNames.get(varId),valeur.getValeur());
							//TODO flag, delta

							//valeurTrouvee = true;
							break;	
						}
					}
					/*if (!valeurTrouvee){
						fichierNetcdf.addMissingValue(varNames.get(varId));
						//TODO flag, delta
					}*/
				}
				cptMes++;
				fichierNetcdf.next();
			}


		}
						
		return cptMes;
	}

	
	
}
