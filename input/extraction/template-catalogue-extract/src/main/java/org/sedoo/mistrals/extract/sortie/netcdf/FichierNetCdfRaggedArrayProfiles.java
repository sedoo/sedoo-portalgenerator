package org.sedoo.mistrals.extract.sortie.netcdf;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.beans.ExtractConfig;
import org.sedoo.mistrals.bd.beans.Localisation;
import org.sedoo.mistrals.bd.beans.Mesure;
import org.sedoo.mistrals.bd.beans.Sequence;
import org.sedoo.mistrals.bd.beans.Valeur;
import org.sedoo.mistrals.bd.dao.LocalisationDAO;
import org.sedoo.mistrals.bd.dao.MesureDAO;
import org.sedoo.mistrals.bd.dao.SequenceDAO;
import org.sedoo.mistrals.bd.dao.ValeurDAO;
import org.sedoo.mistrals.extract.requetes.RequeteDataset;
import org.sedoo.utils.DateUtils;
import org.sedoo.utils.LocalisationConverter;
import org.sedoo.utils.exceptions.DataNotFoundException;

import ucar.ma2.ArrayDouble;
import ucar.ma2.ArrayInt;
import ucar.ma2.DataType;
import ucar.ma2.InvalidRangeException;

public class FichierNetCdfRaggedArrayProfiles extends FichierNetCdfBase {

	private static Logger logger = Logger.getLogger(FichierNetCdfRaggedArrayProfiles.class);
	
	private List<Sequence> sequences;
	
	public FichierNetCdfRaggedArrayProfiles(RequeteDataset requete,
			Connection dbCon, int requeteId, ExtractConfig conf) throws SQLException {
		super(requete, dbCon, requeteId,conf);
		
		PreparedStatement stmt = requete.toSQLSequences(dbCon);
		logger.debug("querySequences: " + stmt);
		this.sequences = new ArrayList<Sequence>();
		ResultSet rs = stmt.executeQuery();
		
		int i = 0;
		
		while (rs.next()){
			Sequence seq = SequenceDAO.buildSequence(rs);
			sequences.add(seq);
			/*if ( ++i == 2 ){
				break;
			}*/
			
		}
		stmt.close();
		
	}

	@Override
	protected void addDimensions() {
		fichierNetcdf.addUnlimitedDimension("obs");
		fichierNetcdf.addDimension("profile", sequences.size());
	}

	@Override
	protected String getDimensions() {
		return "obs";
	}

	@Override
	protected void addCoordinateVars() throws IOException {
		try{
			fichierNetcdf.addTimeVariable(requete.getDateMin(), "profile");
			fichierNetcdf.addMeasuredParam("lat", "latitude", "latitude of the observation", "degrees_north", "profile");
			fichierNetcdf.addMeasuredParam("lon", "longitude", "longitude of the observation", "degrees_east", "profile");
			
			fichierNetcdf.addMeasuredParam("height", "altitude", "height above mean sea level", "m", "obs");
			fichierNetcdf.addVariableAttribute("height", "axis", "Z");
			fichierNetcdf.addVariableAttribute("height", "positive", "up");
			
			fichierNetcdf.addVariable("profile", DataType.INT, "profile");
			fichierNetcdf.addVariableAttribute("profile", "cf_role", "profile_id");
			
			fichierNetcdf.addVariable("rowSize", DataType.INT, "profile");
			fichierNetcdf.addVariableAttribute("rowSize", "long_name", "number of obs for this profile");
			fichierNetcdf.addVariableAttribute("rowSize", "sample_dimension", "obs");
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
		return "time lon lat height";
	}

	@Override
	protected void addGlobalAttributes() throws IOException {
		
	}

	@Override
	protected int writeData() throws IOException, SQLException,
			DataNotFoundException {
		
		
		ArrayDouble.D1 lat = new ArrayDouble.D1(sequences.size());
		ArrayDouble.D1 lon = new ArrayDouble.D1(sequences.size());
		ArrayDouble.D1 time = new ArrayDouble.D1(sequences.size());
		ArrayInt.D1 profId = new ArrayInt.D1(sequences.size()); 
		ArrayInt.D1 rowSize = new ArrayInt.D1(sequences.size());
		
		boolean arretDemande = false;
		int indiceProfil = 0;
		int cptMes = 0;
		for (Sequence profil: sequences){
			int size = 0;
			
			profId.set(indiceProfil, profil.getSequenceId());
			time.set(indiceProfil, DateUtils.distanceSecondes(requete.getDateMin(), profil.getDateDeb()));
			
			Localisation localisation = LocalisationDAO.getService().getById(dbCon, profil.getLocDebId());
			lat.set(indiceProfil,LocalisationConverter.latLonIntToDouble(localisation.getBoundings().getNorth()));
			lon.set(indiceProfil,LocalisationConverter.latLonIntToDouble(localisation.getBoundings().getEast()));
						
			List<Mesure> mesures = MesureDAO.getService().queryForList(dbCon, "SELECT * FROM mesure WHERE sequence_id = " + profil.getSequenceId() + " ORDER BY mesure_id");
			for (Mesure mesure: mesures){
				Collection<Valeur> valeurs = ValeurDAO.getService().getByMesureIdAndVarIds(dbCon,mesure.getMesureId(),requete.getVarIds());
				if (valeurs.isEmpty()) {
					continue;
				}else{
					//Remarque : dans fichierNetCdf, les variables sont initialisées à valeur absente => pas utile de les gérer ici
					for (int varId : requete.getVarIds()) {
						for (Valeur valeur: valeurs){
							if (varId == valeur.getVarId()){
								//Vérifier qu'il n'y a pas déjà une valeur
								if (fichierNetcdf.getValue(varNames.get(varId)) != FichierNetCdf.MISSING_VALUE){
									logger.warn("Multiple values for mesure " + mesure.getMesureId() + " and variable " + varId);
								}
								
								fichierNetcdf.setValue(varNames.get(varId),valeur.getValeur());
								//TODO flag, delta
								break;	
							}
						}
					}
					size++;
					fichierNetcdf.next();
					
					cptMes++;
					//Pour le suivi de l'avancement de la requete et l'arrêt anticipé.
					//On termine le profil en cours d'ériture avant d'arrêter.
					if (testKilled(cptMes)){
						logger.info("Arrêt demandé");
						arretDemande = true;
					}
					
				}
			}
			rowSize.set(indiceProfil, size);
			indiceProfil++;
			if (arretDemande){
				break;
			}
		}
					

		try{
			fichierNetcdf.addValues("lat", lat);
			fichierNetcdf.addValues("lon", lon);
			fichierNetcdf.addValues("time", time);
			fichierNetcdf.addValues("profile", profId);
			fichierNetcdf.addValues("rowSize", rowSize);
						
		}catch(InvalidRangeException e){
			throw new IOException("Error while writing data",e);
		}
		
		
		return cptMes;
	}

}
