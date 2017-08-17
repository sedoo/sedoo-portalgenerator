package org.sedoo.utils.ames;

import java.io.File;
import java.io.IOException;

public class Ames1010 extends AmesBase {

	public Ames1010(File file) throws IOException{
		super(file);
		setFfi("1010");
	}

	@Override
	protected int getBodySize() {
		return HEADER_X_SIZE + HEADER_BODY_V_SIZE + getPrimaryVariables().size() + ((getAuxiliaryVariables().size() == 0)?1:HEADER_BODY_AUXV_SIZE + getAuxiliaryVariables().size());
	}
	
	@Override
	public int getDimNum() {
		return 1;
	}
			
	public void addColumnHeaders() throws AmesException{
				
		String header = getIndependentVariables().get(0).getLabel();
		
		for (AmesDataVar var: getAuxiliaryVariables()){
			header += SEPARATOR + var.getLabel();
		}
		getColumnHeaders().add("");
		getColumnHeaders().add(header);
		
		header = "";
		for (AmesDataVar var: getPrimaryVariables()){
			header += SEPARATOR + var.getLabel();
		}
		getColumnHeaders().add(header);		
		getColumnHeaders().add("");
	}
	
	@Override
	public void write(AmesDataRecord record, boolean last) throws AmesException {
		if ( !isHeaderOk() ){
			throw new AmesException("No header");
		}
		if (record.getPrimaryVariables().size() != getPrimaryVariables().size()){
			throw new AmesException("Bad number of primary values. Found: " + record.getPrimaryVariables().size() + ", Expected: " + getPrimaryVariables().size());
		}
		if (record.getAuxiliaryVariables().size() != getAuxiliaryVariables().size()){
			throw new AmesException("Bad number of auxiliary values. Found: " + record.getAuxiliaryVariables().size() + ", Expected: " + getAuxiliaryVariables().size());
		}
		String aux = "" + record.getIndependentVariable();
		String prim = "";
		
		for (int i = 0;i < record.getAuxiliaryVariables().size();i++){
			if (record.getAuxiliaryVariables().get(i) == null){
				aux += SEPARATOR + getAuxiliaryVariables().get(i).getMissingValue();
			}else{
				aux += SEPARATOR + record.getAuxiliaryVariables().get(i);
			}
		}
			
		for (int i = 0;i < record.getPrimaryVariables().size();i++){
			if (record.getPrimaryVariables().get(i) == null){
				prim += SEPARATOR + getPrimaryVariables().get(i).getMissingValue();
			}else{
				prim += SEPARATOR + record.getPrimaryVariables().get(i);
			}
		}

		out.println(aux);
		out.print(prim);
		
		if (!last){
			out.println();		
		}
	}	
	
}
