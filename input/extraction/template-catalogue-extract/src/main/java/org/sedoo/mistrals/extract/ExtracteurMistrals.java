/*
 * Created on 8 nov. 2005
 */
package org.sedoo.mistrals.extract;

import java.io.IOException;
import java.io.StringReader;
import java.sql.SQLException;
import java.util.TimeZone;

import org.apache.log4j.Logger;
import org.jdom.JDOMException;
import org.jdom.input.SAXBuilder;
import org.jdom.output.DOMOutputter;
import org.sedoo.mistrals.extract.utils.NoDataException;
import org.sedoo.utils.exceptions.DataNotFoundException;
import org.sedoo.utils.exceptions.InvalidDataException;

/**
 * Récupère la requête et lance l'extraction.
 * 
 * @see org.sedoo.mistrals.extract.ExtractEngine
 * @author brissebr
 */
public class ExtracteurMistrals {
	
	private static Logger logger = Logger.getLogger(ExtracteurMistrals.class);
	
	/**
	 * Lance l'extraction dans un nouveau thread.
	 * @param requeteXml requete au format xml
	 * @return "Ok" si tout c'est bien passé
	 * @throws Exception
	 */
	public String performExtraction(String requeteXml, boolean wait) throws SQLException, IOException, InvalidDataException, JDOMException, DataNotFoundException, NoDataException{
		logger.debug("ExtracteurMistrals.performExtraction()");
		logger.debug("requete:"+requeteXml);
		
		TimeZone.setDefault(TimeZone.getTimeZone("UTC"));
		
		//Construit un DOM
		SAXBuilder builder = new SAXBuilder();
		org.jdom.Document doc = builder.build(new StringReader(requeteXml));
		
		DOMOutputter domOut = new DOMOutputter();
		org.w3c.dom.Document dom = domOut.output(doc);
			
		//Lance l'extraction
		RequeteXml req = new RequeteXml(dom);
       
		try{
			ExtractEngine extractor = new ExtractEngine(req);
			extractor.start();

			if (wait){
				logger.info("Attend la fin de l'extraction...");
				extractor.join();
				if (extractor.errorDetected()){
					return "13: Error while processing request.";
				}else{
					if (extractor.getReponse().isEmpty()){
						return "15: Result is empty";
					}else{
						return "01: " + extractor.getReponse().getURL();
					}
				}	
			}
		}catch(NoDataException e){
			return "15: Result is empty";
		}catch(InterruptedException e){
			return "16: Interrupted. " + e;
		}
				
		return "00: OK";
	}
	
}
