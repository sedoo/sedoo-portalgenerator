package org.sedoo.utils.ames;

import java.io.File;
import java.io.IOException;

public class Ames1001 extends AmesBase {


	public Ames1001(File file) throws IOException{
		super(file);
		setFfi("1001");
	}

	@Override
	protected int getBodySize() {
		return HEADER_X_SIZE + HEADER_BODY_V_SIZE + getPrimaryVariables().size();
	}
	
	@Override
	public int getDimNum() {
		return 1;
	}
	
	@Override
	public void addAuxiliaryVariable(AmesDataVar var) throws AmesException{
		throw new AmesException("This file cannot contain any auxiliary variable.");
	}
	@Override
	protected void writeAuxiliaryVars(){}
	
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
