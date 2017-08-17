package org.sedoo.mistrals.extract.utils;

import java.util.Collection;

public interface Contact {

	String getNom();
	String getMail();
	String getOrganisme();
	String getType();
	String getAbstract();
	Collection<String> getRoles();
			
	boolean isRegistered();
	
}
