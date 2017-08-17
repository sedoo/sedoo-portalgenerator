package org.sedoo.mistrals.extract.utils;

import java.util.Collection;
import java.util.Collections;

import org.sedoo.mistrals.extract.Props;

/**
 * 
 * @author brissebr
 * @deprecated
 */
public class ContactPublic implements Contact {

	public static String USER_PUBLIC = "guest";
	
	String mail;
	
	protected ContactPublic(String mail) {
		this.mail = mail;
	}

	@Override
	public String toString() {
		return getMail();
	}
	
	public Collection<String> getRoles() {
		return Collections.singleton(Props.ROLE_PUBLIC);
	}

	public String getAbstract() {
		return null;
	}

	public String getNom() {
		return USER_PUBLIC;
	}

	public String getMail() {
		return mail;
	}

	public String getOrganisme() {
		return null;
	}

	public String getType() {
		return "Unregistered user";
	}
	
	public boolean isRegistered() {
		return false;
	}
	
}
