package org.sedoo.utils.ames;

import java.io.File;
import java.io.IOException;

/**
 * Fichier Ames 2160 simplifié (1 seule variable aux qui contient le nombre de valeur d'un bloc).
 * @author brissebr
 *
 */
public class Ames2160 extends AmesBase {

	/**
	 * DX
	 * LENX
	 * [ XNAMEs ] s=1,2 
	 */
	private final int HEADER_X_2160_SIZE = 4;
	
	/**
	 * NAUXV (=1)
	 * NAUXC (=0)
	 * [ ASCALa, a=1,NAUXV-NAUXC ]
	 * [ AMISSa, a=1,NAUXV-NAUXC ]
	 * [ LENAa, a=NAUXV-NAUXC+1,NAUXV ] (ligne inutile ici)
	 * [ AMISSa ] a=NAUXV-NAUXC+1,NAUXV  (ligne inutile ici)
	 * [ ANAMEa ] a=1,NAUXV 
	 */
	private final int HEADER_AUXV_2160_SIZE = 5;
	
	private final int AUXV_MISSING_VALUE = 9999999;
	
	private String strVarName;
	private int strVarLength;
	
	public Ames2160(File file,String strVarName, int strVarLength) throws IOException{
		super(file);
		setFfi("2160");
		setStringVar(strVarName, strVarLength);
		getAuxiliaryVariables().add(new AmesDataVar("Number of measurements"));
	}

	public Ames2160(File file) throws IOException{
		this(file,null,0);
	}
	
	public final void setStringVar(String strVarName, int strVarLength){
		this.strVarName = strVarName;
		this.strVarLength = strVarLength;
	}
	
	@Override
	protected void writeIndependentVars() throws AmesException{		
		if (strVarName == null){
			throw new AmesException("String variable is missing");
		}
		out.println(getIndependentVariables().get(0).getInterval());
		out.println(strVarLength);
		out.println(getIndependentVariables().get(0));
		out.println(strVarName);
	}
	
	@Override
	protected int getBodySize() {
		return HEADER_X_2160_SIZE + HEADER_BODY_V_SIZE + getPrimaryVariables().size() + HEADER_AUXV_2160_SIZE;
	}
	
	@Override
	public int getDimNum() {
		return 1;
	}
	
	public void addColumnHeaders() throws AmesException{
		
		String header = getIndependentVariables().get(0).getLabel();
		
		for (AmesDataVar var: getPrimaryVariables()){
			header += SEPARATOR + var.getLabel();
		}
				 
		getColumnHeaders().add("");
		getColumnHeaders().add(header);
		getColumnHeaders().add("");
	}
	
	@Override
	public void addAuxiliaryVariable(AmesDataVar var) throws AmesException{
		throw new AmesException("Not implemented. This file cannot contain any auxiliary variable.");
	}
	@Override
	protected void writeAuxiliaryVars(){
		out.println(1);
		out.println(0);
		out.println(1);
		out.println(AUXV_MISSING_VALUE);
		out.println(getAuxiliaryVariables().get(0).getName());
	}
	
	public void writeSectionHeader(String strVar, int sectionSize) throws AmesException{
		out.println(strVar);
		out.println(sectionSize);
	}
	
	@Override
	public void write(AmesDataRecord record, boolean last) throws AmesException {
		if ( !isHeaderOk() ){
			throw new AmesException("No header");
		}
		if (record.getPrimaryVariables().size() != getPrimaryVariables().size()){
			throw new AmesException("Bad number of values. Found: " + record.getPrimaryVariables().size() + ", Expected: " + getPrimaryVariables().size());
		}
		String prim = "" + record.getIndependentVariable();
				
		for (int i = 0;i < record.getPrimaryVariables().size();i++){
			if (record.getPrimaryVariables().get(i) == null){
				prim += SEPARATOR + getPrimaryVariables().get(i).getMissingValue();
			}else{
				prim += SEPARATOR + record.getPrimaryVariables().get(i);
			}
		}
			
		out.print(prim);
		
		if (!last){
			out.println();
		}
	}

}
