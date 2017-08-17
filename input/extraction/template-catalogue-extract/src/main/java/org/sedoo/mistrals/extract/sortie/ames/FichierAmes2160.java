package org.sedoo.mistrals.extract.sortie.ames;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.Iterator;
import java.util.List;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.beans.Localisation;
import org.sedoo.mistrals.bd.beans.Mesure;
import org.sedoo.mistrals.bd.beans.Valeur;
import org.sedoo.mistrals.bd.dao.LocalisationDAO;
import org.sedoo.mistrals.bd.dao.MesureDAO;
import org.sedoo.mistrals.bd.dao.ValeurDAO;
import org.sedoo.mistrals.extract.requetes.RequeteDataset;
import org.sedoo.utils.DateUtils;
import org.sedoo.utils.ames.Ames2160;
import org.sedoo.utils.ames.AmesDataRecord;
import org.sedoo.utils.ames.AmesDataRecord1D;
import org.sedoo.utils.ames.AmesException;
import org.sedoo.utils.ames.AmesFile;
import org.sedoo.utils.exceptions.DataNotFoundException;

/**
 * Fichier Ames contenant plusieurs stations d'un seul jeu (1 bloc de données par station).
 * @author brissebr
 */
public class FichierAmes2160 extends FichierAmesBase {
	
	private static Logger logger = Logger.getLogger(FichierAmes2160.class);
	
	public FichierAmes2160(RequeteDataset requete, Connection dbCon, int requeteId) throws SQLException, IOException {
		super(requete, dbCon, requeteId);
	}

	@Override
	protected String getFFI() {
		return "2160";
	}

	@Override
	protected String getFilename() {
		return "data" + AmesFile.EXTENSION;
	}
	
	@Override
	protected void addSpecialComments() throws AmesException{}

	@Override
	protected void addAdditionalIndependentVariables() throws AmesException {
		((Ames2160)amesFile).setStringVar("Site name",100);
	}

	@Override
	protected void addAdditionalPrimaryVariables() throws AmesException {
		addCompleteLocalisationPrimaryVariables();
	}

	@Override
	protected void addAuxiliaryVariables() throws AmesException {}

	@Override
	public int executeRequete() throws SQLException,IOException,DataNotFoundException {

		PreparedStatement stmt = requete.toSQLMesures(dbCon, "place_name, mesure_date", true);
		logger.debug("queryMesures: " + stmt);
		ResultSet rs = stmt.executeQuery();
				
		int placeId = -1;
		String placeName = null;
		List<AmesDataRecord> dataSection = new ArrayList<AmesDataRecord>();
		int cptMes = 0;
		while (rs.next()) {
			Mesure mesure = MesureDAO.buildMesure(rs);
						
			if (mesure.getPlaceId() != placeId){
				if (!dataSection.isEmpty()){
					((Ames2160)amesFile).writeSectionHeader(placeName, dataSection.size());
					for (AmesDataRecord record: dataSection){
						amesFile.write(record);
					}
				}
				placeId = mesure.getPlaceId();
				placeName = rs.getString("place_name");
				dataSection.clear();
			}
			
			Collection<Valeur> valeurs = ValeurDAO.getService().getByMesureIdAndVarIds(dbCon,mesure.getMesureId(),requete.getVarIds());
			AmesDataRecord1D record = new AmesDataRecord1D();

			if (valeurs.isEmpty()) {
				continue;
			}else{

				Localisation localisation = LocalisationDAO.buildLocalisation(rs);
				addCompleteLocalisation(record, localisation);
				record.setIndependentVariable(DateUtils.distanceSecondes(requete.getDateMin(), mesure.getMesureDate()));

				cptMes++;

				addValeurs(valeurs, record);
				
			
			}
			dataSection.add(record);
			
			//Pour le suivi de l'avancement de la requete et l'arrêt anticipé.
			if (testKilled(cptMes)){
				logger.debug("Arrêt demandé");
				break;
			}
			
			
		}
		
		if (!dataSection.isEmpty()){
			((Ames2160)amesFile).writeSectionHeader(placeName, dataSection.size());
			for (Iterator<AmesDataRecord> i = dataSection.iterator();i.hasNext();){
				amesFile.write(i.next(),!i.hasNext());
			}
		}

		stmt.close();

				
		return cptMes;
	}

}
