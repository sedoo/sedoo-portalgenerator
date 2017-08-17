package org.sedoo.mistrals.extract.sortie.netcdf;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.text.ParseException;
import java.util.Collection;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.sedoo.mistrals.bd.beans.ExtractConfig;
import org.sedoo.mistrals.bd.beans.Localisation;
import org.sedoo.mistrals.bd.beans.Mesure;
import org.sedoo.mistrals.bd.beans.Sequence;
import org.sedoo.mistrals.bd.beans.Valeur;
import org.sedoo.mistrals.bd.dao.LocalisationDAO;
import org.sedoo.mistrals.bd.dao.MesureDAO;
import org.sedoo.mistrals.bd.dao.ValeurDAO;
import org.sedoo.mistrals.extract.requetes.RequeteDataset;
import org.sedoo.utils.Constantes;
import org.sedoo.utils.DateUtils;
import org.sedoo.utils.LocalisationConverter;
import org.sedoo.utils.exceptions.DataNotFoundException;

import ucar.ma2.ArrayDouble;
import ucar.ma2.ArrayInt;
import ucar.ma2.DataType;
import ucar.ma2.InvalidRangeException;

public class FichierNetCdfSingleProfile extends FichierNetCdfBase {

	private Sequence profil;
	private Localisation startLocalisation;
	
	private String filename;
			
	public FichierNetCdfSingleProfile(RequeteDataset requete, Connection dbCon,	int requeteId, Sequence seq, ExtractConfig conf) throws SQLException {
		super(requete, dbCon, requeteId, conf);
		this.profil = seq;
			
		List<Mesure> mesures = MesureDAO.getService().queryForList(dbCon, "SELECT * FROM mesure WHERE sequence_id = " + profil.getSequenceId() + " ORDER BY mesure_id");
				
		this.startLocalisation = LocalisationDAO.getService().getById(dbCon, profil.getLocDebId());
		
			
		for (Mesure mesure: mesures){
			this.profil.addMesure(mesure);
		}
		
		
		try{
			this.filename =  DateUtils.dateToString(profil.getDateDeb(),"yyyyMMdd") 
					+ "_" + startLocalisation.getBoundings().getWest() 
					+ "_"  + startLocalisation.getBoundings().getNorth()
					+ "_" + profil.getSequenceId()
					+ FichierNetCdf.EXTENSION;
		}catch (ParseException e){
			throw new SQLException("Error while generating filename from sequence " + profil.getSequenceId() + ".",e);
		}
	}

	@Override
	protected String getFilename() {
		return filename;
	}
	
	@Override
	protected void addDimensions() {
		fichierNetcdf.addDimension("z",profil.getMesures().size());
	}

	@Override
	protected String getDimensions() {
		return "z";
	}

	@Override
	protected void addCoordinateVars() throws IOException {
		try{
			fichierNetcdf.addTimeVariable(profil.getDateDeb(), "");
			fichierNetcdf.addMeasuredParam("lat", "latitude", "latitude of the observation", "degrees_north", "");
			fichierNetcdf.addMeasuredParam("lon", "longitude", "longitude of the observation", "degrees_east", "");
			fichierNetcdf.addMeasuredParam("z", "altitude", "height above mean sea level", "m", "z");
			fichierNetcdf.addVariableAttribute("z", "axis", "Z");
			fichierNetcdf.addVariableAttribute("z", "positive", "up");
			
			fichierNetcdf.addVariable("profile", DataType.INT, "");
			fichierNetcdf.addVariableAttribute("profile", "cf_role", "profile_id");
			
			//TODO precise_lat precise_lon ?
			
		}catch(DataNotFoundException e){
			throw new IOException("Error while creating time and localisation variables",e);
		}
	}

	@Override
	protected String getFeatureType() {
		return "profile";
	}

	@Override
	protected String getCoordinates() {
		return "time lat lon";
	}

	@Override
	protected void addGlobalAttributes() throws IOException {

	}

	@Override
	protected int writeData() throws IOException, SQLException, DataNotFoundException {

		ArrayDouble.D0 lat = new ArrayDouble.D0();
		ArrayDouble.D0 lon = new ArrayDouble.D0();
		ArrayDouble.D0 time = new ArrayDouble.D0();
		ArrayInt.D0 profId = new ArrayInt.D0(); 
		lat.set(LocalisationConverter.latLonIntToDouble(startLocalisation.getBoundings().getNorth()));
		lon.set(LocalisationConverter.latLonIntToDouble(startLocalisation.getBoundings().getEast()));
		time.set(0);
		profId.set(profil.getSequenceId());
				
		ArrayDouble.D1 z = new ArrayDouble.D1(profil.getMesures().size());
		
		Map<Integer, ArrayDouble.D1> vars = new HashMap<Integer, ArrayDouble.D1>();
		for (int varId : requete.getVarIds()) {
			vars.put(varId, new ArrayDouble.D1(profil.getMesures().size()));
		}

		//Mesure mesSol = MesureDAO.getService().search(dbCon, profil.getDatsId(), profil.getDateDeb(), profil.getLocDebId(), requete.getPlaceId());
				
		int cptMes = 0;
		int i = 0;
		for (Mesure mesure: profil.getMesures()){

			Localisation loc = LocalisationDAO.getService().getById(dbCon, mesure.getLocalisationId());

			if (loc.getAlt() != Constantes.INT_NULL){
				z.set(i, LocalisationConverter.altIntToDouble(loc.getAlt()));
			}else{
				throw new DataNotFoundException("Missing z coordinate for measurement " + mesure.getMesureId());
			}

			Collection<Valeur> valeurs = ValeurDAO.getService().getByMesureIdAndVarIds(dbCon,mesure.getMesureId(),requete.getVarIds());
			
			/*if (mesSol.getLocalisationId() == mesure.getLocalisationId() ){
				valeurs.addAll(ValeurDAO.getService().getByMesureIdAndVarIds(dbCon,mesSol.getMesureId(),requete.getVarIds()));
			}*/
			
			if (!valeurs.isEmpty()){
				cptMes++;
			}
			
			for (int varId : requete.getVarIds()) {
				boolean valeurTrouvee = false;
				for (Valeur valeur: valeurs){
					if (varId == valeur.getVarId()){
						vars.get(varId).set(i, valeur.getValeur());
						//TODO flag, delta

						valeurTrouvee = true;
						break;	
					}
				}
				if (!valeurTrouvee){
					vars.get(varId).set(i, FichierNetCdf.MISSING_VALUE);
					//TODO flag, delta
				}
			}
			i++;
		}
		
		try{
			fichierNetcdf.addValues("lat", lat);
			fichierNetcdf.addValues("lon", lon);
			fichierNetcdf.addValues("time", time);
			fichierNetcdf.addValues("profile", profId);
			fichierNetcdf.addValues("z", z);
			
			for (int varId : requete.getVarIds()) {
				fichierNetcdf.addValues(varNames.get(varId), vars.get(varId));
			}
			
		}catch(InvalidRangeException e){
			throw new IOException("Error while writing data",e);
		}
		
		
		return cptMes;
	}

}
