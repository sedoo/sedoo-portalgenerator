package org.sedoo.mistrals.extract.fichiers;

import java.io.StringReader;
import java.util.TimeZone;

import org.apache.log4j.Logger;
import org.jdom.input.SAXBuilder;
import org.jdom.output.DOMOutputter;
import org.sedoo.mistrals.extract.Props;


public class ExtracteurFichiers {

private final static Logger logger = Logger.getLogger(ExtracteurFichiers.class);
	
	/**
	 * Lance l'extraction dans un nouveau thread.
	 * @param requeteXml requete au format xml
	 * @return 
	 * @throws Exception
	 */
	public String performExtraction(String requeteXml) throws Exception{
		logger.debug("performExtraction()");
		logger.debug("requete:"+requeteXml);
		
		TimeZone.setDefault(TimeZone.getTimeZone("UTC"));
		
		//Construit un DOM
		SAXBuilder builder = new SAXBuilder();
		org.jdom.Document doc = builder.build(new StringReader(requeteXml));
		
		DOMOutputter domOut = new DOMOutputter();
		org.w3c.dom.Document dom = domOut.output(doc);
			
		//Lance l'extraction
        RequeteXmlFichiers req = new RequeteXmlFichiers(dom);
        
        if (!req.isAuthorized()){
        	return "15: Unauthorized access.";
        }
        
        ExtractEngine extractor = new ExtractEngine(req);
        extractor.start();

        extractor.join(Props.TIMEOUT * 1000);
        if (extractor.isAlive()){
        	return "00: OK";
        }else{
        	if (extractor.errorDetected())
        		return "13: Error while processing request.";
        	else
        		return "01: " + extractor.getReponse().getURL();
        }
          /*      
        if (wait){
        	logger.info("Attend la fin de l'extraction...");
        	extractor.join();
        	if (extractor.errorDetected())
        		return "13: Error while processing request.";
        	else
        		return "01: " + extractor.getReponse().getURL();
        }
        return "00: OK";*/
	}
	
}
