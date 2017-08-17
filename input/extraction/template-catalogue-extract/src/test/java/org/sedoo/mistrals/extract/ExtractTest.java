package org.sedoo.mistrals.extract;

import java.io.InputStream;
import java.io.StringReader;

import junit.framework.TestCase;

import org.apache.log4j.Logger;
import org.jdom.Document;
import org.jdom.input.SAXBuilder;
import org.jdom.output.XMLOutputter;
import org.sedoo.utils.log.LogUtils;
import org.sedoo.utils.xml.ValidateXmlException;
import org.sedoo.utils.xml.XMLValidator;
import org.xml.sax.InputSource;

public class ExtractTest extends TestCase {

	private final static Logger logger = Logger.getLogger(ExtractTest.class);
	
	/*
	public void testOk(){
		assertTrue(true);
	}
	
	public void testVide(){
		assertEquals("15", test("test_vide.xml"));
	}
	
	
	public void test2160(){
		assertEquals("01", test("test.xml"));
	}*/
	/*
	public void test1001(){
		assertEquals("01", test("test2.xml"));
	}
	
	public void test1010(){
		assertEquals("01", test("test3.xml"));
	}
	
	
	public void testNc(){
		assertEquals("01", test("testNc.xml"));
	}
	*/
	public void testNcTimeSeries(){
		assertEquals("01", test("test_nc_timeSeries.xml"));
	}
	/*
	public void testNa2160(){
		assertEquals("01", test("test_na_2160.xml"));
	}*/
	
	private String test(String xmlTest) {

		InputStream isTest = getClass().getResourceAsStream( "/" + xmlTest );
		String retour = null;
		try{			
			
			InputStream is = ClassLoader.getSystemClassLoader().getResourceAsStream("test.conf" );
			Props.init(is);
												
			SAXBuilder builder = new SAXBuilder();
			Document doc = builder.build(new InputSource(isTest));
			XMLOutputter outXml = new XMLOutputter(); 	
			String requeteXml = outXml.outputString(doc);
									
			XMLValidator validator = new XMLValidator();
			validator.validate(new StringReader(requeteXml));
			
			ExtracteurMistrals extracteur = new ExtracteurMistrals();
						
			retour = extracteur.performExtraction(requeteXml,true);
						
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
