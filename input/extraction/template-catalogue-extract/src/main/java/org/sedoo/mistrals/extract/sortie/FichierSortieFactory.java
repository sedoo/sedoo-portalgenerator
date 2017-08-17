package org.sedoo.mistrals.extract.sortie;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.extract.RequeteXml;
import org.sedoo.mistrals.extract.sortie.ames.FichierAmesFactory;
import org.sedoo.mistrals.extract.sortie.netcdf.FichierNetCdfFactory;

public final class FichierSortieFactory {

	private static Logger logger = Logger.getLogger(FichierSortieFactory.class);
		
	private FichierSortieFactory() {}
		
	public static Collection<FichierSortie> getFichiersSortie(RequeteXml requete, Connection dbCon, int requeteId) throws IOException, SQLException{
		logger.debug("getFichiersSortie(" + requete.getFormat() + ")");
		Collection<FichierSortie> list = new ArrayList<FichierSortie>();
						
		if (RequeteXml.FORMAT_AMES.equals(requete.getFormat())){
			list.addAll(FichierAmesFactory.getFichiersAmes(requete, dbCon, requeteId));
		}else if (RequeteXml.FORMAT_NETCDF.equals(requete.getFormat())){
			list.addAll(FichierNetCdfFactory.getFichiersNetCdf(requete, dbCon, requeteId));
		}else{
			throw new IOException("format inconnu: " + requete.getFormat());
		}
		logger.debug("Fichiers: " + list.size());
		if ( (list.size() > 0) && 
				( RequeteXml.COMPRESSION_GZ.equals(requete.getCompression()) || RequeteXml.COMPRESSION_ZIP.equals(requete.getCompression()) ) ){
			Collection<FichierSortie> result = new ArrayList<FichierSortie>();
			result.add(new ArchiveSortie(dbCon, requeteId, requete.getCompression(), list));
			return result; 
		}
		return list;
	}
	
}
