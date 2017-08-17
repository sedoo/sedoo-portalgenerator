package org.sedoo.utils.ames;

import java.sql.Timestamp;
import java.text.ParseException;

import org.sedoo.utils.DateUtils;


public class AmesTimeVar extends AmesIndependentVar {

	public AmesTimeVar(Timestamp date) throws AmesException{
		this(date,0.0);
	}
	
	public AmesTimeVar(Timestamp date, double interval) throws AmesException{
		super();
		this.setName("Time");
		this.setLabel("time");
		try{
			this.setUnit("seconds since " + DateUtils.dateToString(date,"yyyy-MM-dd HH:mm:ss") + " +00:00");
		}catch(ParseException e){
			throw new AmesException("Error while parsing dates. Cause: " + e);
		}
		this.setInterval(interval);
	}

}
