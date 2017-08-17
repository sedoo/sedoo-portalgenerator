package org.sedoo.mistrals.extract.sortie.ames;

import java.sql.Connection;
import java.sql.SQLException;

import org.sedoo.mistrals.extract.requetes.RequeteBase;

/**
 * Fichier Ames contenant les stations de plusieurs jeux.
 * @author brissebr
 */
public class FichierAmes1001AllDatasets extends FichierAmes1001Base {
		
	public FichierAmes1001AllDatasets(RequeteBase requete,Connection dbCon,int requeteId) throws SQLException {
		super(dbCon,requeteId,requete);
	}
	
}
