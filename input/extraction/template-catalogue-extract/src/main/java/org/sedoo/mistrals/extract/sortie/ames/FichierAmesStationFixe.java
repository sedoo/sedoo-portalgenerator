package org.sedoo.mistrals.extract.sortie.ames;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.beans.Localisation;
import org.sedoo.mistrals.bd.dao.LocalisationDAO;
import org.sedoo.mistrals.extract.requetes.RequeteDatasetStation;
import org.sedoo.utils.Constantes;
import org.sedoo.utils.LocalisationConverter;
import org.sedoo.utils.ames.AmesDataRecord1D;
import org.sedoo.utils.ames.AmesException;

public abstract class FichierAmesStationFixe extends FichierAmesStation {

	private static Logger logger = Logger.getLogger(FichierAmesStationFixe.class);
	
	private Localisation localisation;
	
	public FichierAmesStationFixe(RequeteDatasetStation requete,Connection dbCon,int requeteId) throws SQLException, IOException {
		super(requete, dbCon, requeteId);
		//Localisation
		PreparedStatement stmt = dbCon.prepareStatement(
				"select min(localisation_id) as localisation_id from localisation where localisation_id in (" +
				"select distinct localisation_id from mesure where ins_dats_id in (" + insDatsIdStr + ") and mesure_date between ? AND ? AND place_id = ?" +
				") group by bound_id,localisation_alt");
		
		stmt.setTimestamp(1, requete.getDateMin());
		stmt.setTimestamp(2, requete.getDateMax());
		stmt.setInt(3, requete.getPlaceId());
		
		logger.debug(stmt.toString());
		
		ResultSet rs = stmt.executeQuery();
		if (rs.next()){
			this.localisation = LocalisationDAO.getService().getById(dbCon, rs.getInt("localisation_id"));
		}
		if (rs.next()){
			throw new AmesException("La localisation n'est pas fixe!");
		}
		
		stmt.close();
	}
	
	@Override
	protected void addSpecialComments() throws AmesException{
		super.addSpecialComments();
		amesFile.addSpecialComment("longitude: " + LocalisationConverter.latLonIntToDouble(localisation.getBoundings().getEast()) + " degrees_east");
		amesFile.addSpecialComment("latitude: " + LocalisationConverter.latLonIntToDouble(localisation.getBoundings().getNorth()) + " degrees_north");
		amesFile.addSpecialComment("altitude: " + LocalisationConverter.altIntToDouble(localisation.getAlt()) + " m");		
	}
	
	@Override
	protected abstract String getFFI();
	@Override
	protected abstract void addAdditionalPrimaryVariables() throws AmesException;
	@Override
	protected abstract void addAuxiliaryVariables() throws AmesException ;

	protected abstract void addHauteurSol(AmesDataRecord1D record, Double hs);

	@Override
	protected void addLocalisation(AmesDataRecord1D record, Localisation loc){
		if (loc.getHauteurSol() != Constantes.INT_NULL){
			addHauteurSol(record,LocalisationConverter.altIntToDouble(loc.getHauteurSol()));
		}else{
			addHauteurSol(record,null);
		}
	}
	
	
	
}
