package org.sedoo.mistrals.extract.utils;

import java.util.Collection;
import java.util.HashSet;

public class ContactPI implements Contact {

	private String nom;
	private String mail;
	private String organisme;
	private String type;
				
	protected ContactPI(String nom, String mail, String organisme, String type) {
		super();
		this.nom = nom;
		this.mail = mail;
		this.organisme = organisme;
		this.type = type;
	}

	public String toString() {
		String res = type + ": " + nom;
		if (organisme != null){
			res += " (" + organisme + ")";
		}
		res += ", email: " + mail;
		return res; 
	}
	
	public String getNom() {
		return nom;
	}

	public String getMail() {
		return mail;
	}

	public String getOrganisme() {
		return organisme;
	}

	public String getType() {
		return type;
	}

	public String getAbstract() {
		return null;
	}

	public Collection<String> getRoles() {
		return new HashSet<String>();
	}
	public boolean isRegistered() {
		return false;
	}
}
