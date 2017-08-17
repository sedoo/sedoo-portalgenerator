package org.sedoo.mistrals.extract;

import java.io.IOException;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;

import org.apache.log4j.Logger;
import org.medias.utils.db.AccessToDB;
import org.sedoo.utils.exceptions.DataNotFoundException;
import org.sedoo.utils.log.LogUtils;
import org.sedoo.mistrals.bd.beans.Requete;
import org.sedoo.mistrals.bd.dao.RequeteDAO;
import org.sedoo.mistrals.extract.sortie.FichierSortie;
import org.sedoo.mistrals.extract.sortie.FichierSortieFactory;
import org.sedoo.mistrals.extract.utils.NoDataException;


/**
 * Extracteur de données de la base MISTRALS. 
 */
public class ExtractEngine extends Thread {
	private static Logger logger = Logger.getLogger(ExtractEngine.class);
	
	/* ******* ATTRIBUTS ******* */

	private ReponseXml reponse;
	
	//Requete dans la base
	Requete requete;
	
	private Collection<FichierSortie> sortie;
			
	private AccessToDB dbAccess;
	
	private boolean errorDetected;
	
	/* ******* CONSTRUCTEUR ******* */		
	
	/**
	 * Construit l'objet.
	 * @param requete requete à lancer sur la base
	 */
	public ExtractEngine(RequeteXml requeteXml) throws SQLException, IOException, DataNotFoundException, NoDataException {
		super();
		
		this.dbAccess = new AccessToDB(Props.DB_DRIVER,Props.DB_URL,Props.DB_USER,Props.DB_PASSWD);
		dbAccess.autoCommit();
				
		this.errorDetected = false;
						
		//enregistre la requete dans la base
		this.requete = new Requete(requeteXml.getUtilisateur().getMail(), requeteXml.xmlToString());
		RequeteDAO.getService().insert(dbAccess.get_con(), requete);
				
		try{
			this.reponse = new ReponseXml(requeteXml.getUtilisateur(),requete.getRequeteId(), requeteXml.getProjet());

			//Fichiers à générer en sortie
			this.sortie = new ArrayList<FichierSortie>();
			sortie.addAll(FichierSortieFactory.getFichiersSortie(requeteXml,dbAccess.get_con(),requete.getRequeteId()));
		}catch(Exception e){
			logger.error(e);
			LogUtils.logException(logger,e);
			RequeteDAO.getService().close(dbAccess.get_con(), requete, Requete.CODE_ECHEC);
			throw new IOException(e);
		}
		if (sortie.isEmpty()){
			RequeteDAO.getService().close(dbAccess.get_con(), requete, Requete.CODE_VIDE);
			throw new NoDataException();
		}
		
	}
	
			
	/**
	 * Lance l'extraction. Selon le déroulement de l'extraction, un mail de succès ou d'échec sera envoyé à l'utilisateur.
	 */
	public void run(){
		logger.debug("ExtractEngine.run()");
		try{
			//Nombre de mesures écrites dans les fichiers
			int cptTotal = 0;
			for (FichierSortie out:sortie){
				int cptMes = out.write();
				
				if (cptMes > 0){
					reponse.addFile(out.getResultFile());
					cptTotal += cptMes;
					RequeteDAO.getService().update(dbAccess.get_con(), requete.getRequeteId(), cptTotal);
				}else{
					out.getFile().delete();
				}
				
				if (RequeteDAO.getService().isKilled(dbAccess.get_con(), requete.getRequeteId())){
					logger.debug("Arrêt demandé");
					
					break;
				}
			}
			
			//Mail confirmation
			if ( reponse.isEmpty() ){
				RequeteDAO.getService().close(dbAccess.get_con(), requete, Requete.CODE_VIDE);
				Mailer.sendMailEmptyResult(requete,reponse);
			}else{
				reponse.write();
				RequeteDAO.getService().close(dbAccess.get_con(), requete, Requete.CODE_SUCCES);
				Mailer.sendMailOk(requete,reponse);
			}
		}
		catch (Exception e){
			logger.error("probleme dans process requete");
			this.errorDetected = true;
			LogUtils.logException(logger,e);
			Mailer.sendMailErreur(e,requete,reponse);
			try{
				RequeteDAO.getService().close(dbAccess.get_con(), requete, Requete.CODE_ECHEC);
			}catch(Exception er){
				LogUtils.logException(logger,er);
			}
		}
	}
	
	public boolean errorDetected() {
		return errorDetected;
	}
	
	public ReponseXml getReponse() {
		return reponse;
	}
	
}
