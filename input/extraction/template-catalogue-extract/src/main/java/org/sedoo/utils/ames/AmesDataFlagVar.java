package org.sedoo.utils.ames;

public class AmesDataFlagVar extends AmesDataVar {

	public static final String PREFIX_LABEL = "q_";
	public static final String PREFIX_NAME = "Flag ";
	public static final int MISSING = 99;
	
	public AmesDataFlagVar(AmesDataVar variable) {
		super(PREFIX_NAME + variable.getName(),"quality flag",PREFIX_LABEL + variable.getLabel(),1,MISSING);
	}
	
}
