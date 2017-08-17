package org.sedoo.mistrals.extract.sortie.ames;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Collection;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.beans.Localisation;
import org.sedoo.mistrals.bd.beans.Mesure;
import org.sedoo.mistrals.bd.beans.Place;
import org.sedoo.mistrals.bd.beans.Valeur;
import org.sedoo.mistrals.bd.dao.LocalisationDAO;
import org.sedoo.mistrals.bd.dao.MesureDAO;
import org.sedoo.mistrals.bd.dao.PlaceDAO;
import org.sedoo.mistrals.bd.dao.ValeurDAO;
import org.sedoo.mistrals.extract.requetes.RequeteDatasetStation;
import org.sedoo.utils.DateUtils;
import org.sedoo.utils.ames.AmesDataRecord1D;
import org.sedoo.utils.ames.AmesException;
import org.sedoo.utils.ames.AmesFile;
import org.sedoo.utils.exceptions.DataNotFoundException;

/**
 * Format pour les données d'une seule station.
 * @author brissebr
 *
 */
public abstract class FichierAmesStation extends FichierAmesBase {

	private static Logger logger = Logger.getLogger(FichierAmesStation.class);
	
	private Place site;
		
	public FichierAmesStation(RequeteDatasetStation requete,Connection dbCon,int requeteId) throws SQLException, IOException {
		super(requete, dbCon, requeteId);
		this.site = PlaceDAO.getService().getById(dbCon, requete.getPlaceId());
	}
		
	@Override
	protected String getFilename() {
		return site.getPlaceName().replaceAll("/", "_") + AmesFile.EXTENSION;
	}
	
	@Override
	protected void addAdditionalIndependentVariables() throws AmesException {}
	
	@Override
	protected void addSpecialComments() throws AmesException {
		amesFile.addSpecialComment("site_name: " + site.getPlaceName());
	}
	
	
	protected abstract void addLocalisation(AmesDataRecord1D record, Localisation loc);
	
	@Override
	public int executeRequete() throws SQLException,IOException,DataNotFoundException {
		
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
												
				Localisation loc = LocalisationDAO.buildLocalisation(rs);
				addLocalisation(record, loc);
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
		
		return cptMes;
	}
	
}
