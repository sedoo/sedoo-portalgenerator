package org.sedoo.mistrals.extract.sortie.netcdf;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.List;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.beans.ExtractConfig;
import org.sedoo.mistrals.bd.dao.ExtractConfigDAO;
import org.sedoo.mistrals.extract.RequeteXml;
import org.sedoo.mistrals.extract.requetes.RequeteBuilder;
import org.sedoo.mistrals.extract.requetes.RequeteDataset;
import org.sedoo.mistrals.extract.requetes.RequeteDatasetStation;

public final class FichierNetCdfFactory {

	private static Logger logger = Logger.getLogger(FichierNetCdfFactory.class);
	
	private FichierNetCdfFactory() {}
		
	public static Collection<FichierNetCdfBase> getFichiersNetCdf(RequeteXml requeteXml, Connection dbCon, int requeteId) throws SQLException, IOException{
		Collection<FichierNetCdfBase> fichiers = new ArrayList<FichierNetCdfBase>();
		RequeteBuilder builder = new RequeteBuilder(dbCon);
				
		Collection<RequeteDataset> requetes = builder.splitRequeteXml(requeteXml);

		logger.debug("requetes: " + requetes.size());
		
		for (RequeteDataset req: requetes){
			List<ExtractConfig> confs = ExtractConfigDAO.getService().getById(dbCon, req.getDatsId());
			if (confs.isEmpty()){
				throw new IOException("No config found for dataset " + req.getDatsId());
			}
			for(ExtractConfig conf: confs){
				logger.debug("id: " + conf.getDatsId() + ", featureType: " + conf.getFeatureType());
				//TODO short name et mobile

				if (conf.getVariables().isEmpty()){
					conf.setVariables(req.getVarIds());
				}else{
					conf.getVariables().retainAll(req.getVarIds());
				}
				
				if (conf.getVariables().isEmpty()){
					logger.debug("No data");
					break;
				}
				
				if ("timeSeries".equals(conf.getFeatureType())){
					if (req instanceof RequeteDatasetStation){
						fichiers.add(new FichierNetCdfSingleTimeSeries((RequeteDatasetStation)req, dbCon, requeteId,conf));
					}else{
						fichiers.add(new FichierNetCdfTimeSeries(req, dbCon, requeteId, conf));
					}
				}else if ("timeSeriesProfile".equals(conf.getFeatureType())){
					if (req instanceof RequeteDatasetStation){
						FichierNetCdfSingleTimeSeriesProfile f = new FichierNetCdfSingleTimeSeriesProfile((RequeteDatasetStation)req, dbCon, requeteId, conf);
						if (!f.isEmpty()){
							fichiers.add(f);
						}
					}else{
						for (int placeId: req.getPlaceIds()){
							RequeteDatasetStation r = new RequeteDatasetStation(req.getDatsId(), placeId, req.getVarIds(), req.getPeriode(), req.getZone(), 
									req.getCompression(), req.isWithFlag(), req.isWithDelta(), req.getProjet());
							FichierNetCdfSingleTimeSeriesProfile f = new FichierNetCdfSingleTimeSeriesProfile(r, dbCon, requeteId, conf);
							if (!f.isEmpty()){
								fichiers.add(f);
							}
						}
					}
				} else if ("profile".equals(conf.getFeatureType())){
					fichiers.add(new FichierNetCdfRaggedArrayProfiles(req, dbCon, requeteId, conf));
				}else if ("point".equals(conf.getFeatureType())){
					fichiers.add(new FichierNetCdfPoint(req, dbCon, requeteId, conf));
				}else {
					throw new IOException("featureType " + conf.getFeatureType() + " unimplemented.");
				}
			}
			//point par défaut
			
		}
		/*
		logger.debug("version: " + requeteXml.getFormatVersion());
		
		if ("timeSeries".equals(requeteXml.getFormatVersion())){
			for (RequeteDataset req: requetes){
				if (req instanceof RequeteDatasetStation){
					fichiers.add(new FichierNetCdfSingleTimeSeries((RequeteDatasetStation)req, dbCon, requeteId));
				}else{
					fichiers.add(new FichierNetCdfTimeSeries(req, dbCon, requeteId));
				}
			}
		}else if ("singleProfile".equals(requeteXml.getFormatVersion())){
			for (RequeteDataset req: requetes){
				
				PreparedStatement stmt = req.toSQLSequences(dbCon);
				logger.debug("querySequences: " + stmt);
				ResultSet rs = stmt.executeQuery();
				while (rs.next()){
					Sequence seq = SequenceDAO.buildSequence(rs);
					fichiers.add(new FichierNetCdfSingleProfile(req, dbCon, requeteId, seq));
				}
				stmt.close();
			}
		}else{
			//Point par défaut
			for (RequeteDataset req: requetes){
				fichiers.add(new FichierNetCdfPoint(req, dbCon, requeteId));			
			}
		}*/
		return fichiers;
	}
	
}
