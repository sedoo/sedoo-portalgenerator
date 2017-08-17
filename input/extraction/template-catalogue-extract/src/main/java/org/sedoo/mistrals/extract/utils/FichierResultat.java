package org.sedoo.mistrals.extract.utils;

import java.io.File;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashSet;
import java.util.Set;

public class FichierResultat {

	public static final int TYPE_DOCUMENTATION = 1;
	public static final int TYPE_DATA = 2;
	
	private File file;
	private Set<ContactsJeu> contacts;
	
	private Collection<FichierResultat> associatedFiles;
	
	private int type;
		
	public FichierResultat(File file) {
		this(file, new HashSet<ContactsJeu>(), TYPE_DATA);
	}
	
	public FichierResultat(File file,Set<ContactsJeu> contacts) {
		this(file, contacts, TYPE_DATA);
	}
	
	public FichierResultat(File file, int type) {
		this(file, new HashSet<ContactsJeu>(), type);
	}
	
	public FichierResultat(File file, Set<ContactsJeu> contacts, int type) {
		super();
		this.file = file;
		this.contacts = contacts;
		this.type = type;
		this.associatedFiles = new ArrayList<FichierResultat>();
	}
			
	public File getFile() {
		return file;
	}
	public Set<ContactsJeu> getContacts() {
		return contacts;
	}
	public int getType() {
		return type;
	}
		
	public Collection<FichierResultat> getAssociatedFiles() {
		return associatedFiles;
	}
}
