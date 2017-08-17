package org.sedoo.mistrals.extract.sortie.ames;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;

import org.sedoo.mistrals.extract.RequeteXml;
import org.sedoo.mistrals.extract.requetes.RequeteBase;
import org.sedoo.mistrals.extract.requetes.RequeteBuilder;
import org.sedoo.mistrals.extract.requetes.RequeteDataset;
import org.sedoo.mistrals.extract.requetes.RequeteDatasetStation;

public final class FichierAmesFactory {

	private FichierAmesFactory() {}
	
	public static Collection<FichierAmesBase> getFichiersAmes(RequeteXml requeteXml, Connection dbCon, int requeteId) throws SQLException, IOException{
		Collection<FichierAmesBase> fichiers = new ArrayList<FichierAmesBase>();
		RequeteBuilder builder = new RequeteBuilder(dbCon);

		if (RequeteXml.FORMAT_AMES_1001.equals(requeteXml.getFormatVersion())){
			RequeteBase req = builder.buildRequete(requeteXml);
			if (req != null){
				if (requeteXml.getPlacesIds() == null || requeteXml.getPlacesIds().isEmpty()){
					req.getPlaceIds().clear();
				}
				fichiers.add(new FichierAmes1001AllDatasets(req, dbCon, requeteId));
			}
		}else{
			Collection<RequeteDataset> requetes = builder.splitRequeteXml(requeteXml);
			if (RequeteXml.FORMAT_AMES_1001_FIXE.equals(requeteXml.getFormatVersion())){
				for (RequeteDataset req: requetes){
					for (Integer placeId: req.getPlaceIds()){
						RequeteDatasetStation reqSta = new RequeteDatasetStation(req.getDatsId(), placeId, req.getVarIds(), req.getPeriode(), req.getZone(), req.getCompression(), req.isWithFlag(), req.isWithDelta(), req.getProjet());
						fichiers.add(new FichierAmesStationFixe1001(reqSta, dbCon, requeteId));
					}
				}
			}else if (RequeteXml.FORMAT_AMES_1001_MOBILE.equals(requeteXml.getFormatVersion())){
				for (RequeteDataset req: requetes){
					for (Integer placeId: req.getPlaceIds()){
						RequeteDatasetStation reqSta = new RequeteDatasetStation(req.getDatsId(), placeId, req.getVarIds(), req.getPeriode(), req.getZone(), req.getCompression(), req.isWithFlag(), req.isWithDelta(), req.getProjet());
						fichiers.add(new FichierAmesStationMobile1001(reqSta, dbCon, requeteId));
					}
				}
			}else if (RequeteXml.FORMAT_AMES_1010.equals(requeteXml.getFormatVersion())){
				for (RequeteDataset req: requetes){
					for (Integer placeId: req.getPlaceIds()){
						RequeteDatasetStation reqSta = new RequeteDatasetStation(req.getDatsId(), placeId, req.getVarIds(), req.getPeriode(), req.getZone(), req.getCompression(), req.isWithFlag(), req.isWithDelta(), req.getProjet());
						fichiers.add(new FichierAmesStationFixe1010(reqSta, dbCon, requeteId));
					}
				}
			}else if (RequeteXml.FORMAT_AMES_1001_DATASET.equals(requeteXml.getFormatVersion())){
				for (RequeteDataset req: requetes){
					if (requeteXml.getPlacesIds() == null || requeteXml.getPlacesIds().isEmpty()){
						req.getPlaceIds().clear();
					}
					fichiers.add(new FichierAmes1001Dataset(req, dbCon, requeteId));
				}
			}else {
				//Défaut: 2160
				for (RequeteDataset req: requetes){
					if (requeteXml.getPlacesIds() == null || requeteXml.getPlacesIds().isEmpty()){
						req.getPlaceIds().clear();
					}
					fichiers.add(new FichierAmes2160(req, dbCon, requeteId));
				}		
			}

		}
		return fichiers;
	}

}
