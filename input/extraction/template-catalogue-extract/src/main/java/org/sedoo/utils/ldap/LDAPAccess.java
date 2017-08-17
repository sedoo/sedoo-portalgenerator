/*
 * Created on 1 mars 2006
 */
package org.sedoo.utils.ldap;

import java.util.Properties;

import javax.naming.Context;
import javax.naming.NamingEnumeration;
import javax.naming.NamingException;
import javax.naming.directory.Attributes;
import javax.naming.directory.SearchControls;
import javax.naming.directory.SearchResult;
import javax.naming.ldap.InitialLdapContext;
import javax.naming.ldap.LdapContext;

import org.apache.log4j.Logger;


/**
 * Classe pour accéder à l'annuaire LDAP.
 * @author brissebr
 */
public class LDAPAccess {

	private static Logger logger = Logger.getLogger(LDAPAccess.class);
			
	public static final int OK = 0;
	public static final int FAILED = -1;
	public static final int ALREADY_INSERTED = 1;
	
	private int searchScope = SearchControls.SUBTREE_SCOPE;
		
	private LdapContext ctx;
    
	private String suffix;
	private String server;
			
	/* ******* CONSTRUCTEURS ******* */
	
	/**
	 * Se connecte à l'annuaire spécifié.
	 * Utilise une url de la forme suivante:
	 * ldap://&ltserver&gt/&ltsuffix&gt
	 *   
	 * @param server nom du serveur
	 * @param suffix base de l'annuaire
	 * @param userDn dn de l'utilisateur qui se connecte
	 * @param userPasswd mot de passe de l'utilisateur
	 * @param referral valeur de la variable d'environnement java.naming.referral
	 * @throws NamingException connexion à l'annuaire impossible
	 */
	public LDAPAccess(String server,String suffix,String userDn,String userPasswd,String referral) throws NamingException{
		Properties props = new Properties();
		
		props.put( Context.INITIAL_CONTEXT_FACTORY,"com.sun.jndi.ldap.LdapCtxFactory" );
		props.put( Context.PROVIDER_URL, "ldap://" + server + "/" + suffix);
		props.put( Context.SECURITY_PRINCIPAL, userDn);
		props.put( Context.SECURITY_CREDENTIALS, userPasswd);
		props.put( Context.SECURITY_AUTHENTICATION, "simple");
		
		ctx = new InitialLdapContext(props,null);
		
		if (referral != null){
			ctx.addToEnvironment(Context.REFERRAL,referral);
		}
		this.server = server;
		this.suffix = suffix;
				
		logger.debug("Connecté en tant que: " + userDn);
	}
		
	/**
	 * Se connecte à l'annuaire spécifié de manière anonyme.
	 * Utilise une url de la forme suivante:
	 * ldap://&ltserver&gt/&ltsuffix&gt
	 *   
	 * @param server nom du serveur
	 * @param suffix base de l'annuaire
	 * @param referral valeur de la variable d'environnement java.naming.referral
	 * @throws NamingException connexion à l'annuaire impossible
	 */
	public LDAPAccess(String server,String suffix,String referral) throws NamingException{
		Properties props = new Properties();
		
		props.put( Context.INITIAL_CONTEXT_FACTORY,"com.sun.jndi.ldap.LdapCtxFactory" );
		props.put( Context.PROVIDER_URL, "ldap://" + server + "/" + suffix);
		
		ctx = new InitialLdapContext(props,null);
		
		if (referral != null){
			ctx.addToEnvironment(Context.REFERRAL,referral);
		}
		this.server = server;
		this.suffix = suffix;
				
		logger.debug("Connecté en tant que: anonymous");
	}

	/**
	 * Ferme la connexion à l'annuaire.
	 */
	public void close(){
		try{
			ctx.close();
		}catch(NamingException e){
			logger.error(e.getMessage());
		}
	}
	
	
	
	/* ******* MANIPULATION DES ENTREES ******* */
	
	/**
	 * Liste les entrées du type spécifié.
	 * @param objectclass type à récupérer
	 * @return  liste des entrées trouvées
	 */
	public NamingEnumeration<SearchResult> listEntries(String objectclass){
		try{
			SearchControls cons = new SearchControls();
			cons.setSearchScope(searchScope);
			
			String filter = "(objectClass="+objectclass+")";
			
			NamingEnumeration<SearchResult> result = ctx.search("",filter,cons);
		
			return result;
		}
		catch (NamingException e){
			logger.error(e.getMessage());
			return null;
		}
	}
	
	/**
	 * Recherche dans l'annuaire
	 * @param attribute attribut à tester
	 * @param value valeur recherchée
	 * @return liste des entrées trouvées
	 */
	public NamingEnumeration<SearchResult> listEntries(String attribute, String value){
		try{
			SearchControls cons = new SearchControls();
			cons.setSearchScope(searchScope);
			
			String filter = "("+attribute+"="+value+")";
			
			NamingEnumeration<SearchResult> result = ctx.search("",filter,cons);
		
			return result;
		}
		catch (NamingException e){
			logger.error(e.getMessage());
			return null;
		}
	}
		
	public void removeEntry(String dn){
		try{
			ctx.unbind(dn);
		}
		catch (NamingException e){
			logger.error(e.getMessage());
		}
	}
		
	/**
	 * Renvoie les attributs de l'entrée spécifiée.
	 * @param dn dn de l'entrée
	 * @return liste d'attributs
	 */
	public Attributes getEntryAttributes(String dn){
		return getEntryAttributes(dn,"*");
	}
	
	/**
	 * Renvoie les attributs de l'entrée spécifiée.
	 * @param dn dn de l'entrée
	 * @param objectClass classe de l'objet
	 * @return liste d'attributs, null si aucune entrée trouvée
	 */
	public Attributes getEntryAttributes(String dn,String objectClass){
		try{
			logger.info("Search for entry "+dn);
			
			String rdn = getRdn(dn);
			logger.debug("Use RDN "+rdn);
			
			SearchControls cons = new SearchControls();
			cons.setSearchScope(SearchControls.OBJECT_SCOPE);
			
			String filter = "(objectClass="+objectClass+")";
			
			NamingEnumeration<SearchResult> result = ctx.search(rdn,filter,cons);
			
			if (result.hasMoreElements()){
				SearchResult res = result.next();
				return res.getAttributes();
			}
			else{
				return null;
			}
		}
		catch (NamingException e){
			logger.error(e.getMessage());
			return null;
		}
	}
			
				
	/* ******* DIVERS ******* */
			
	/**
	 * Supprime suffix du dn spécifié afin d'obtenir un dn relatif (rdn).
	 * @param dn dn
	 * @return dn relatif
	 */
	private String getRdn(String dn){
		if (!"".equals(suffix)){
			return dn.replaceFirst(","+suffix,"");
		}else{
			return dn;
		}
	}
	
	/* ******* GETTERS & SETTERS ******* */

	public String getServer() {
		return server;
	}
	
	/**
	 * 
	 * @return dn de base de l'annuaire
	 */
	public String getSuffix() {
		return suffix;
	}
	/**
	 * @return portée des recherches
	 */
	public int getSearchScope() {
		return searchScope;
	}
	/**
	 * Définit la profondeur de la recherche (subtree par défaut). LDAP définit trois valeurs : base, onelevel et subtree.
	 * @param searchScope nouvelle portée à utiliser
	 * @see SearchControls
	 */
	public void setSearchScope(int searchScope) {
		this.searchScope = searchScope;
	}
	
}
