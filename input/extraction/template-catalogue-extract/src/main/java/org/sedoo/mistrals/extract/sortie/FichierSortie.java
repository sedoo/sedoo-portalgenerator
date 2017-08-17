package org.sedoo.mistrals.extract.sortie;

import java.io.File;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.Set;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.dao.RequeteDAO;
import org.sedoo.mistrals.extract.Props;
import org.sedoo.mistrals.extract.utils.ContactsJeu;
import org.sedoo.mistrals.extract.utils.FichierResultat;
import org.sedoo.utils.exceptions.DataNotFoundException;


/**
 * 
 * @author brissebr
 */
public abstract class FichierSortie {
	private static Logger logger = Logger.getLogger(FichierSortie.class);
	
	private final int DB_CURSOR = Props.DB_CURSOR;
	
	private FichierResultat resultFile;
	private Collection<FichierResultat> associatedFiles;
	
	private File file;
	
	protected File destPath;
	
	protected Connection dbCon;
	
	private int requeteId;
	
	/**
	 * Construit le flux sur le fichier résultat pret à être rempli avec le résultat de la requête.
	 * @param requete requete d'extraction
	 * @throws IOException
	 */
	public FichierSortie(Connection dbCon,int requeteId) {
		this.dbCon = dbCon;
		this.requeteId = requeteId;
		this.associatedFiles = new ArrayList<FichierResultat>(); 
		this.destPath = new File(Props.FILE_DEST_PATH + requeteId);
		if (!destPath.exists() && destPath.mkdir()){
			logger.info("Directory " + destPath + " successfully created.");
		}
	}
	
	protected abstract String getFilename() ;	
				
	protected void init() throws IOException{
		this.file = new File(destPath +  "/" + getFilename());
		logger.info("File: " + file);
		open();
	}
			
	protected abstract void open() throws IOException;
	
	/**
	 * 
	 * @return nombre de mesures écrites
	 * @throws Exception
	 */
	public int write() throws IOException,SQLException,DataNotFoundException{
		init();
		printHeader();
		int cptMes = writeData();
		close();
		return cptMes;
	}
	
	/**
	 * Ecrit l'entete du fichier.
	 * @throws Exception
	 */
	protected abstract void printHeader() throws IOException,SQLException;
	/**
	 * Execute la requete et ecrit le resultat dans le fichier.
	 * @return nombre de mesures écrites
	 * @throws Exception
	 */
	protected abstract int writeData() throws IOException,SQLException,DataNotFoundException;
	/**
	 * Ferme le fichier.
	 */
	protected abstract void closeFile() throws IOException;
	
	/**
	 * Pour le suivi de l'avancement de la requete et l'arrêt anticipé.
	 * @param cptMes
	 * @return true si la requete doit être arrêtée avant la fin
	 * @throws DataNotFoundException
	 * @throws SQLException
	 */
	protected boolean testKilled(int cptMes) throws DataNotFoundException, SQLException{
		if (cptMes % DB_CURSOR == 0){
			logger.info("Mesures: " + cptMes);
			RequeteDAO.getService().update(dbCon, requeteId, cptMes);
			return RequeteDAO.getService().isKilled(dbCon, requeteId);
		}else{
			return false;
		}
	}
	
	public abstract Set<ContactsJeu> getContacts();
	
	protected void close() throws IOException {
		closeFile();
		
		resultFile = new FichierResultat(file,getContacts());
		resultFile.getAssociatedFiles().addAll(associatedFiles);
	}
		
	public FichierResultat getResultFile() {
		return resultFile;
	}
	
	public File getFile() {
		return file;
	}
	
	public void addDocumentationFile(File file){
		associatedFiles.add(new FichierResultat(file, FichierResultat.TYPE_DOCUMENTATION));
	}
	
	protected int getRequeteId() {
		return requeteId;
	}
	
}
