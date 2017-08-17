package org.sedoo.utils.ames;

import java.util.ArrayList;
import java.util.List;

public class AmesDataRecord1D implements AmesDataRecord{
	
	private Double independentVariable;
	private List<Number> primaryVariables;	
	private List<Number> auxiliaryVariables;
	
	public AmesDataRecord1D() {
		this.auxiliaryVariables = new ArrayList<Number>();
		this.independentVariable = null;
		this.primaryVariables = new ArrayList<Number>();
	}
				
	public AmesDataRecord1D(Double independentVariable,List<Number> primaryVariables, List<Number> auxiliaryVariables) {
		super();
		this.independentVariable = independentVariable;
		this.primaryVariables = primaryVariables;
		this.auxiliaryVariables = auxiliaryVariables;
	}

	public AmesDataRecord1D(Double independentVariable,List<Number> primaryVariables) {
		this(independentVariable,primaryVariables,new ArrayList<Number>());
	}

	public Double getIndependentVariable() {
		return independentVariable;
	}
	public void setIndependentVariable(Double independentVariable) {
		this.independentVariable = independentVariable;
	}
	public List<Number> getPrimaryVariables() {
		return primaryVariables;
	}
	public void setPrimaryVariables(List<Number> primaryVariables) {
		this.primaryVariables = primaryVariables;
	}
	public List<Number> getAuxiliaryVariables() {
		return auxiliaryVariables;
	}
	public void setAuxiliaryVariables(List<Number> auxiliaryVariables) {
		this.auxiliaryVariables = auxiliaryVariables;
	}
}
