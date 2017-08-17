package org.sedoo.mistrals.extract.sortie;

import java.io.File;
import java.io.IOException;
import java.sql.Connection;
import java.sql.SQLException;
import java.text.SimpleDateFormat;
import java.util.Collections;
import java.util.Date;
import java.util.Set;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.beans.Dataset;
import org.sedoo.mistrals.bd.beans.Journal;
import org.sedoo.mistrals.bd.dao.DatasetDAO;
import org.sedoo.mistrals.extract.fichiers.RequeteXmlFichiers;
import org.sedoo.mistrals.extract.utils.ContactsJeu;
import org.sedoo.mistrals.extract.utils.ContactsJeuFactory;
import org.sedoo.utils.compress.Archive;
import org.sedoo.utils.compress.ArchiveTar;
import org.sedoo.utils.compress.ArchiveZip;
import org.sedoo.utils.exceptions.DataNotFoundException;

public class CommandeFichiers extends FichierSortie {

	private static Logger logger = Logger.getLogger(CommandeFichiers.class);

	private Archive archive;
	
	private RequeteXmlFichiers requete;
	private ContactsJeu contacts;
	
	private Journal journal;
	
	public CommandeFichiers(Connection dbCon, RequeteXmlFichiers requete, Journal journal) throws SQLException {
		super(dbCon, requete.getRequeteId());
		
		this.requete = requete;
		
		Dataset dats = DatasetDAO.getService().getById(dbCon, requete.getDatsId());
		this.contacts = ContactsJeuFactory.getContacts(dbCon, dats);
		
		this.journal = journal;		
	}
	

	@Override
	public Set<ContactsJeu> getContacts() {
		return Collections.singleton(contacts);
	}

	@Override
	protected String getFilename() {
		Date d = new Date();
		SimpleDateFormat myformat = new SimpleDateFormat("yyyyMMddhhmmss");
		return "commande_" + myformat.format(d) + getExtension();
	}

	private String getExtension()  {
		if (RequeteXmlFichiers.COMPRESSION_GZ.equals(requete.getCompression())){
			return ".tar.gz";
		}else{
			//Zip par défaut
			return ".zip";
		}
	}
	
	@Override
	protected void open() throws IOException {
		if (RequeteXmlFichiers.COMPRESSION_GZ.equals(requete.getCompression())){
			archive = new ArchiveTar(getFile(),true);
		}else{
			//Zip par défaut
			archive = new ArchiveZip(getFile());
		}
		archive.openWrite();
	}

	@Override
	protected void closeFile() throws IOException {
		logger.debug("close()");
		archive.close();
	}
	
	@Override
	protected void printHeader() throws IOException, SQLException {
		// TODO Readme à ajouter à l'archive

	}

	@Override
	protected int writeData() throws IOException, SQLException,	DataNotFoundException {
		String resume = "";
		for (File file : requete.getFiles()){
			resume += file.getAbsolutePath() + "\n";
			addFile(archive,file);
        }
		journal.setComment(resume);
		return requete.getFiles().size();
	}

	/**
	 * 
	 * @param archive
	 * @param fichier
	 * @throws IOException
	 */
	private void addFile(Archive archive, File fichier) throws IOException{
		logger.debug("Add " + fichier.getAbsolutePath());
		if (fichier.isDirectory()){
			File [] fichiers = fichier.listFiles();
			for (int i = 0;i < fichiers.length;i++){
				addFile(archive, fichiers[i]);
			}
		}else{
			archive.addEntry(fichier, requete.getRacine());
		}
	}
	
}
