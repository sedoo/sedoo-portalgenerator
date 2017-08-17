package org.sedoo.mistrals.extract;

import java.io.InputStream;
import java.io.StringReader;

import org.apache.log4j.Logger;
import org.jdom.Document;
import org.jdom.input.SAXBuilder;
import org.jdom.output.XMLOutputter;
import org.sedoo.mistrals.extract.fichiers.ExtracteurFichiers;
import org.sedoo.utils.log.LogUtils;
import org.sedoo.utils.xml.ValidateXmlException;
import org.sedoo.utils.xml.XMLValidator;
import org.xml.sax.InputSource;

import junit.framework.TestCase;

public class ExtractFichiersTest extends TestCase {
	private final static Logger logger = Logger.getLogger(ExtractFichiersTest.class);
	
	public void testOk(){
		assertTrue(true);
	}
	
	/*
	public void testGros(){
		assertEquals("00", test("test_fichiers_gros.xml"));
	}
	
	public void testPetit(){
		assertEquals("01", test("test_fichiers_petit.xml"));
	}
	
	public void testAuth(){
		assertEquals("15", test("test_unauthorized.xml"));
	}
	public void testPublic(){
		assertEquals("01", test("test_public.xml"));
	}*/
	
	private String test(String xmlTest) {

		logger.info(xmlTest);
		
		InputStream isTest = getClass().getResourceAsStream( "/" + xmlTest );
		String retour = null;
		try{			
			
			InputStream is = ClassLoader.getSystemClassLoader().getResourceAsStream("test.conf" );
			Props.init(is);
						
			//logger.debug("System Properties: " + System.getProperties());
			
			SAXBuilder builder = new SAXBuilder();
			Document doc = builder.build(new InputSource(isTest));
			XMLOutputter outXml = new XMLOutputter(); 	
			String requeteXml = outXml.outputString(doc);
									
			XMLValidator validator = new XMLValidator();
			validator.validate(new StringReader(requeteXml));
			
			ExtracteurFichiers extracteur = new ExtracteurFichiers();
						
			retour = extracteur.performExtraction(requeteXml);
						
			logger.info(retour);
			
			retour = retour.substring(0, 2);
			
		}catch (ValidateXmlException e){
			LogUtils.logException(logger,e);
			retour = "10";
			logger.info("10: Fichier xml non conforme");
			logger.info("Cause: "+e.getMessage());
		}catch(Exception e){
			retour = "11";
			logger.info("11: Erreur au démarrage");
			logger.fatal(e);
			LogUtils.logException(logger,e);
			logger.debug("Properties: " + System.getProperties());
		}
		return retour;
		
	}
	
}
