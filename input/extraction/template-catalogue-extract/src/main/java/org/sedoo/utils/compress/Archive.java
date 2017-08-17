package org.sedoo.utils.compress;

import java.io.File;
import java.io.IOException;

/**
 * Interface pour la manipulation d'archive (construction et extraction).
 * @author brissebr
 */
public interface Archive {
		
	int BUFFER_SIZE = 4096;
	
	/**
	 * @throws IOException
	 */
	void openWrite() throws IOException;
	
	/**
	 * @throws IOException
	 */
	void close() throws IOException;
	
	
	/**
	 * Ajoute une entrée dans l'archive. Le chemin complet est conservé, et le fichier ajouté n'est pas supprimé.
	 * @param entry
	 * @return true si tout s'est déroulé correctement
	 * @throws IOException si le fichier n'est pas ouvert en écriture
	 */
	boolean addEntry(File entry) throws IOException;
	
	
	/**
	 * Ajoute une entrée dans l'archive. Le fichier ajouté n'est pas supprimé.
	 * @param entry fichier à ajouter
	 * @param storePath indique si on conserve le chemin ou pas
	 * @return true si tout s'est déroulé correctement
	 * @throws IOException si le fichier n'est pas ouvert en écriture
	 */
	boolean addEntry(File entry, boolean storePath) throws IOException;
	/**
	 * Ajoute une entrée dans l'archive. Le fichier ajouté n'est pas supprimé.
	 * @param entry fichier à ajouter
	 * @param rootPath on supprime rootPath du path du fichier (pour ne stocker qu'un chemin relatif)
	 * @return true si tout s'est déroulé correctement
	 * @throws IOException si le fichier n'est pas ouvert en écriture
	 */
	boolean addEntry(File entry, File rootPath) throws IOException;
	/**
	 * Ajoute une entrée dans l'archive.
	 * @param entry fichier à ajouter
	 * @param storePath indique si on conserve le chemin ou pas
	 * @param delete indique si l'on doit supprimer le fichier qui a été ajouté
	 * @return true si tout s'est déroulé correctement
	 * @throws IOException si le fichier n'est pas ouvert en écriture
	 */
	boolean addEntry(File entry, boolean storePath, boolean delete) throws IOException;

	/**
	 * Ajoute une entrée dans l'archive.
	 * @param entry fichier à ajouter
	 * @param storePath indique si on conserve le chemin ou pas
	 * @param rootPath si storePath=true, on supprime rootPath du path du fichier (pour ne stocker qu'un chemin relatif)
	 * @param delete indique si l'on doit supprimer le fichier qui a été ajouté
	 * @return true si tout s'est déroulé correctement
	 * @throws IOException si le fichier n'est pas ouvert en écriture
	 */
	boolean addEntry(File entry, boolean storePath, File rootPath, boolean delete) throws IOException;
		
	/**
	 * Ajoute une entrée dans l'archive.
	 * @param entry fichier à ajouter
	 * @param rootPath on supprime rootPath du path du fichier (pour ne stocker qu'un chemin relatif)
	 * @param delete indique si l'on doit supprimer le fichier qui a été ajouté
	 * @return true si tout s'est déroulé correctement
	 * @throws IOException si le fichier n'est pas ouvert en écriture
	 */
	boolean addEntry(File entry, File rootPath, boolean delete) throws IOException;
	
	/**
	 * Extrait le contenu l'archive dans le répertoire précisé.
	 * @param directory destination
	 */
	void extract(String directory) throws IOException;
}
