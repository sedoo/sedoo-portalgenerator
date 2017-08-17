package org.sedoo.mistrals.extract.utils;

import java.util.Collection;

import javax.naming.directory.Attributes;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.extract.Props;
import org.sedoo.mistrals.ldap.MistralsLDAPAccess;

public class ContactUser implements Contact {

	private static Logger logger = Logger.getLogger(ContactUser.class);
	
	private String nom;
	private String mail;
	private String organisme;
		
	private Attributes ldapAttributes;
	
	private Collection<String> roles;
			
	protected ContactUser(String nom, String mail, String organisme) {
		super();
		this.nom = nom;
		this.mail = mail;
		this.organisme = organisme;
		
		MistralsLDAPAccess ldap = new MistralsLDAPAccess();
		this.ldapAttributes = ldap.getUserAttributes(mail);
		logger.debug("Ldap Attrs: " + ldapAttributes);
		
	}

	public String toString() {
		String res = nom;
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
		return "User";
	}

	public String getAbstract() {
		return MistralsLDAPAccess.getValue(ldapAttributes, MistralsLDAPAccess.LDAP_ATTR_DESCRIPTION);
	}

	public Collection<String> getRoles() {
		if (roles == null){
			roles = MistralsLDAPAccess.getValues(ldapAttributes, MistralsLDAPAccess.LDAP_ATTR_ROLE);			
			roles.add(Props.ROLE_PUBLIC);
		}
		return roles;
	}

	public boolean isRegistered() {
		return true;
	}

}
