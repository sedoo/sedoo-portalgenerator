package org.sedoo.mistrals.extract.sortie;

import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.Collection;
import java.util.HashSet;
import java.util.Set;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.dao.RequeteDAO;
import org.sedoo.mistrals.extract.RequeteXml;
import org.sedoo.mistrals.extract.utils.ContactsJeu;
import org.sedoo.mistrals.extract.utils.FichierResultat;
import org.sedoo.utils.compress.Archive;
import org.sedoo.utils.compress.ArchiveTar;
import org.sedoo.utils.compress.ArchiveZip;
import org.sedoo.utils.exceptions.DataNotFoundException;

/**
 * 
 * @author brissebr
 */
public class ArchiveSortie extends FichierSortie {
	private static Logger logger = Logger.getLogger(ArchiveSortie.class);
	
	private Collection<FichierSortie> fichiers;
	private Archive archive;
	
	private String compression;

	private Set<ContactsJeu> contacts;
	
	public ArchiveSortie(Connection dbCon,int requeteId,String compression,Collection<FichierSortie> fichiers)  {
		super(dbCon,requeteId);
		this.compression = compression;
		this.fichiers = fichiers;
		this.contacts = new HashSet<ContactsJeu>();
	}
	
	@Override
	protected String getFilename()  {
		return "result" + getExtension();
	}
	
	private String getExtension()  {
		if (RequeteXml.COMPRESSION_GZ.equals(compression)){
			return ".tar.gz";
		}else{
			//Zip par défaut
			return ".zip";
		}
	}
	
	@Override
	public void closeFile() throws IOException {
		logger.debug("close()");
		archive.close();
	}

	@Override
	public Set<ContactsJeu> getContacts() {
		return contacts;
	}
	
	@Override
	public int writeData() throws IOException,SQLException,DataNotFoundException {
		logger.debug("executeRequete()");
		int cptTotal = 0;
		for (FichierSortie fichier: fichiers){
			logger.debug(" - " + fichier.getFilename());
			
			int cptMes = fichier.write();
			
			logger.debug("=> " + cptMes + " mesures");
			
			if (cptMes > 0){
				archive.addEntry(fichier.getFile(),destPath);
				
				for (FichierResultat assocFile: fichier.getResultFile().getAssociatedFiles()){
					archive.addEntry(assocFile.getFile(),destPath);
				}
				
				contacts.addAll(fichier.getContacts());
				cptTotal += cptMes;
				RequeteDAO.getService().update(dbCon, getRequeteId(), cptTotal);
			}else{
				fichier.getFile().delete();
			}
			
			if (RequeteDAO.getService().isKilled(dbCon, getRequeteId())){
				logger.debug("Arrêt demandé");
				break;
			}
			
		}
		
		return cptTotal;
	}
	
	@Override
	public void open() throws IOException {
		logger.debug("open()");
		if (RequeteXml.COMPRESSION_GZ.equals(compression)){
			archive = new ArchiveTar(getFile(),true);
		}else{
			//Zip par défaut
			archive = new ArchiveZip(getFile());
		}
		archive.openWrite();
	}

	@Override
	public void printHeader() throws IOException,SQLException {
	}

}
