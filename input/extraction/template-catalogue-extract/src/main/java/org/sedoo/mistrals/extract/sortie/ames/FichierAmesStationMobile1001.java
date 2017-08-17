package org.sedoo.mistrals.extract.sortie.ames;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

import org.sedoo.mistrals.bd.beans.Localisation;
import org.sedoo.mistrals.extract.requetes.RequeteDatasetStation;
import org.sedoo.utils.ames.AmesDataRecord1D;
import org.sedoo.utils.ames.AmesException;

/**
 * Fichier Ames contenant les données d'une seule station mobile.
 * @author brissebr
 */
public class FichierAmesStationMobile1001 extends FichierAmesStation {
	
	public FichierAmesStationMobile1001(RequeteDatasetStation requete,Connection dbCon,int requeteId) throws IOException,SQLException {
		super(requete, dbCon, requeteId);
	}
	
	@Override
	protected String getFFI() {
		return "1001";
	}
		
	@Override
	protected void addAdditionalIndependentVariables() throws AmesException {}
			
	@Override
	protected void addAdditionalPrimaryVariables() throws AmesException {
		addCompleteLocalisationPrimaryVariables();
	}
	
	@Override
	protected void addAuxiliaryVariables() throws AmesException {}
	
	@Override
	protected void addLocalisation(AmesDataRecord1D record, Localisation loc){
		addCompleteLocalisation(record, loc);
	}
	
		
}
