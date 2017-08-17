package org.sedoo.mistrals.extract.fichiers;


import org.apache.log4j.Logger;
import org.medias.utils.db.AccessToDB;
import org.sedoo.mistrals.bd.beans.Journal;
import org.sedoo.mistrals.bd.dao.JournalDAO;
import org.sedoo.mistrals.extract.Props;
import org.sedoo.mistrals.extract.ReponseXml;
import org.sedoo.mistrals.extract.sortie.CommandeFichiers;
import org.sedoo.mistrals.extract.sortie.FichierSortie;
import org.sedoo.utils.log.LogUtils;




/**
 * Commande de fichiers de données. 
 * Les fichiers sont cr��s dans le r�pertoire d�fini dans {@link Props#FILE_DEST_PATH}.
 * @author brissebr
 * @see Props
 * @see Extracteur
 */
public class ExtractEngine extends Thread
{
	private final static Logger logger = Logger.getLogger(ExtractEngine.class);
	
	//TODO déplacer
	public static final int TYPE_JOURNAL = 3;
	
	/* ******* ATTRIBUTS ******* */
	
	private AccessToDB dbAccess;
	
	private FichierSortie sortie;
	
	private RequeteXmlFichiers requete;
	private ReponseXml reponse;
			
	private Journal journal;
	
	private boolean errorDetected;
	
	/* ******* CONSTRUCTEUR ******* */		
	

	public ExtractEngine(RequeteXmlFichiers requete) throws Exception {
		logger.debug("<init>");
		this.requete = requete;
		this.errorDetected = false;
		this.dbAccess = new AccessToDB(Props.DB_DRIVER,Props.DB_URL,Props.DB_USER,Props.DB_PASSWD);
		dbAccess.autoCommit();
		this.reponse = new ReponseXml(requete.getUtilisateur(),requete.getRequeteId(), requete.getProjet());
		this.journal = new Journal(0, requete.getDatsId(), TYPE_JOURNAL, requete.getUtilisateur().getMail());
		this.sortie = new CommandeFichiers(dbAccess.get_con(), requete, journal);
	}
		
	public boolean errorDetected() {
		return errorDetected;
	}
			
	public ReponseXml getReponse() {
		return reponse;
	}
		
	/**
	 * Lance l'extraction. Selon le déroulement de l'extraction, un mail de succès ou d'échec sera envoyé à l'utilisateur (et à l'admin).
	 */
	public void run(){
		logger.debug("run()");
		try{			
			sortie.write();
			reponse.addFile(sortie.getResultFile());
			reponse.setPublic(requete.isPublic());
			reponse.write();
			requete.setDateFin();
						
			long intervalle = (requete.getDateFin().getTime() - requete.dateDeb.getTime())/1000;		
			long sec = intervalle % 60;
			long min = intervalle/60;
			long h = min/60;						
			min = min % 60;				
			logger.info("Temps d'exécution : " + h + " heure(s) " + min + " minute(s) " + sec + " seconde(s)");
			
			JournalDAO.getService().insert(dbAccess.get_con(), journal);
			
			Mailer.sendMailOk(requete,reponse);
		}
		catch (Exception e){
			logger.error("probleme dans process requete");
			LogUtils.logException(logger,e);
			this.errorDetected = true;
			LogUtils.logException(logger,e);
			Mailer.sendMailErreur(e,requete,reponse);
		}
	}
	
}
