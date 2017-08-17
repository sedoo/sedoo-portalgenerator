package org.sedoo.mistrals.extract.sortie.ames;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

import org.sedoo.mistrals.extract.requetes.RequeteDatasetStation;
import org.sedoo.utils.ames.AmesDataRecord1D;
import org.sedoo.utils.ames.AmesDataVar;
import org.sedoo.utils.ames.AmesException;

/**
 * Fichier Ames contenant les données d'une seule station fixe.
 * @author brissebr
 */
public class FichierAmesStationFixe1001 extends FichierAmesStationFixe {
	
	public FichierAmesStationFixe1001(RequeteDatasetStation requete,Connection dbCon,int requeteId) throws SQLException,IOException {
		super(requete, dbCon, requeteId);
	}
	
	@Override
	protected String getFFI() {
		return "1001";
	}
				
	@Override
	protected void addAdditionalPrimaryVariables() throws AmesException {
		amesFile.addPrimaryVariable(new AmesDataVar("Height above ground","m","Height",1,999999.9));
	}
	
	@Override
	protected void addAuxiliaryVariables() throws AmesException {}
	
	@Override
	protected void addHauteurSol(AmesDataRecord1D record, Double hs) {
		record.getPrimaryVariables().add(hs);
	}
	
}
