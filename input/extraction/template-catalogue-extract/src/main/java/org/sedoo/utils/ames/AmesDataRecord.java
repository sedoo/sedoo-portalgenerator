package org.sedoo.utils.ames;

import java.util.List;

public interface AmesDataRecord {

	Number getIndependentVariable();
	
	List<Number> getPrimaryVariables();
	List<Number> getAuxiliaryVariables();
	
}
