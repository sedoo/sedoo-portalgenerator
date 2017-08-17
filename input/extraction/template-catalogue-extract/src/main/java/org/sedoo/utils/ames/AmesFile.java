package org.sedoo.utils.ames;

import java.sql.Timestamp;

public interface AmesFile {

	String EXTENSION = ".txt";
	
	String ENCODING = "US-ASCII";
	String SEPARATOR = " ";
	String FORMAT_DATE = "yyyy MM dd";
	
	double DEFAULT_VMISS = 99999.9;
	
	/**
	 * 7 premières lignes du header communes à tous les formats Ames.
	 * NLHEAD FFI
	 * ONAME
	 * ORG
	 * SNAME
	 * MNAME
	 * IVOL NVOL
	 * DATE RDATE
	 */
	int HEADER_TOP_SIZE = 7;
	
	/**
	 * NV
	 * [ VSCALn, n=1,NV ]
	 * [ VMISSn, n=1,NV ] 
	 */
	int HEADER_BODY_V_SIZE = 3;
	/**
	 * NAUXV
	 * [ ASCALa a=1,NAUXV ]
	 * [ AMISSa a=1,NAUXV ]
	 */
	int HEADER_BODY_AUXV_SIZE = 3;
	
	/**
	 * DX
	 * XNAME
	 */
	int HEADER_X_SIZE = 2;
	
	
	void init(String originator, String originatorOrganism, String database, String dataset,Timestamp obsDate, Timestamp prodDate);
	void init(String database, String dataset,Timestamp obsDate, Timestamp prodDate);
	
	void close();
	
	void addIndependentVariable(AmesIndependentVar var) throws AmesException;
	void addPrimaryVariable(AmesDataVar var) throws AmesException;
	void addAuxiliaryVariable(AmesDataVar var) throws AmesException;
	void addColumnHeaders() throws AmesException;
			
	void addNormalComment(String line) throws AmesException;
	void addSpecialComment(String line) throws AmesException;
	
	void writeHeader() throws AmesException;
	
	void write(AmesDataRecord record) throws AmesException;
	void write(AmesDataRecord record, boolean last) throws AmesException;
	
	String getFfi();
}
