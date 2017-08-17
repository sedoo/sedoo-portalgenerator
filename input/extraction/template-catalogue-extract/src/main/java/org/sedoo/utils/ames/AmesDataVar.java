package org.sedoo.utils.ames;


public class AmesDataVar extends AmesVar {
	
	private Number scalingFactor;
	private Number missingValue;
			
	public AmesDataVar(String name, String unit) {
		this(name,unit,null,1.0,AmesFile.DEFAULT_VMISS);
	}
	
	public AmesDataVar(String name) {
		this(name,null,null,1.0,AmesFile.DEFAULT_VMISS);
	}
	
	public AmesDataVar(String name, String unit, String label) {
		this(name,unit,label,1.0,AmesFile.DEFAULT_VMISS);
	}
	
	public AmesDataVar(String name, String unit, String label,Number scalingFactor, Number missingValue) {
		super(name,unit,label);
		this.scalingFactor = scalingFactor;
		this.missingValue = missingValue;
	}
		
	public Number getScalingFactor() {
		return scalingFactor;
	}
	public void setScalingFactor(Number scalingFactor) {
		this.scalingFactor = scalingFactor;
	}
	public Number getMissingValue() {
		return missingValue;
	}
	public void setMissingValue(Number missingValue) {
		this.missingValue = missingValue;
	}
			
}
