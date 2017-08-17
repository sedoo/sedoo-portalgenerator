package org.sedoo.utils.ames;

public class AmesIndependentVar extends AmesVar {
		
	private double interval;

	protected AmesIndependentVar() {}
	
	public AmesIndependentVar(String name) {
		this(name,null,null,0.0);
	}
	
	public AmesIndependentVar(String name, String unit) {
		this(name,unit,null,0.0);
	}
	
	public AmesIndependentVar(String name, String unit, double interval) {
		this(name,unit,null,interval);
	}
	
	public AmesIndependentVar(String name, String unit, String label,double interval) {
		super(name,unit,label);
		this.interval = interval;
	}

	public boolean hasUniformIncrement(){
		return interval != 0.0;
	}

	public double getInterval() {
		return interval;
	}
	public void setInterval(double interval) {
		this.interval = interval;
	}		
}
