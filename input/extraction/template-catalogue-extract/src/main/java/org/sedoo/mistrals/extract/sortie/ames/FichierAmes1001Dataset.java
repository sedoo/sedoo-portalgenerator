package org.sedoo.mistrals.extract.sortie.ames;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;

import org.sedoo.mistrals.extract.requetes.RequeteDataset;

/**
 * Fichier Ames contenant plusieurs stations d'un seul jeu.
 * @author brissebr
 */
public class FichierAmes1001Dataset extends FichierAmes1001Base {
	
	public FichierAmes1001Dataset(RequeteDataset requete, Connection dbCon,	int requeteId) throws SQLException, IOException {
		super(requete, dbCon, requeteId);
	}

}
