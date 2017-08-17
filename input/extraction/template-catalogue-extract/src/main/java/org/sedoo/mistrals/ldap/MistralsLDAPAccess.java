package org.sedoo.mistrals.ldap;

import java.util.Collection;
import java.util.Collections;
import java.util.HashSet;

import javax.naming.NamingEnumeration;
import javax.naming.NamingException;
import javax.naming.directory.Attribute;
import javax.naming.directory.Attributes;
import javax.naming.directory.SearchResult;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.extract.Props;
import org.sedoo.utils.ldap.LDAPAccess;

public class MistralsLDAPAccess {

	private static Logger logger = Logger.getLogger(MistralsLDAPAccess.class);
	
	public static final String LDAP_ATTR_ROLE = "memberOf";
	public static final String LDAP_ATTR_DESCRIPTION = "description";
	
	/**
	 * Constructeur.
	 * @param fichier de conf contenant les valeurs pour ldap.host, ldap.port, ldap.referral, ldap.admin et ldap.passwd
	 */
	public MistralsLDAPAccess() {
		logger.debug("LDAP_SERVER: " + Props.LDAP_HOST);
		logger.debug("LDAP_BASE: " + Props.LDAP_BASE);
		logger.debug("LDAP_ADMIN: " + Props.LDAP_ADMIN);
		logger.debug("LDAP_PASSWD: " + ((Props.LDAP_PASSWD != null)?"****":Props.LDAP_PASSWD));
		logger.debug("LDAPAccess.REFERRAL: " + Props.LDAP_REFERRAL);
	}
	
	public static String getValue(Attributes attrs,String attrName){
		try{
			Attribute attr = attrs.get(attrName);
			if (attr != null){
				return (String)attr.get(0);
			}
		}catch(NamingException e){
			logger.error("Error in getValue(): " + e);
		}
		return null;
	}
	
	public static Collection<String> getValues(Attributes attrs,String attrName){
		Collection<String> list = new HashSet<String>();
		try{
			Attribute attr = attrs.get(attrName);
			if (attr != null){
				list.addAll(Collections.list((NamingEnumeration<String>)attr.getAll()));
			}
		}catch(NamingException e){
			logger.error("Error in getValues(): " + e);
		}
		return list;
	}
	
	public Attributes getUserAttributes(String userEmail) {
		LDAPAccess ldap = null;
		Attributes attrs = null;
		try{
			ldap = new LDAPAccess(Props.LDAP_HOST, Props.LDAP_BASE, Props.LDAP_ADMIN, Props.LDAP_PASSWD, Props.LDAP_REFERRAL);
			
			NamingEnumeration<SearchResult> users = ldap.listEntries("mail", userEmail);
			SearchResult user = users.next();
			attrs = user.getAttributes();
		}catch(NamingException e){
			logger.error(e);
		}finally{
			if (ldap != null){
				ldap.close();
			}
		}
		return attrs;
	}
	
	
}
