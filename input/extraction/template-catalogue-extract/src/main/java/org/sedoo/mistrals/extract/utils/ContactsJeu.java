package org.sedoo.mistrals.extract.utils;

import java.util.ArrayList;
import java.util.Collection;

import org.apache.commons.lang.builder.EqualsBuilder;
import org.apache.commons.lang.builder.HashCodeBuilder;
import org.sedoo.mistrals.bd.beans.Dataset;


public class ContactsJeu implements Comparable<ContactsJeu>{

	private Collection<Contact> contacts;
	private Dataset jeu;
			
	public ContactsJeu(Dataset jeu) {
		this(new ArrayList<Contact>(),jeu);
	}
	
	protected ContactsJeu(Collection<Contact> contacts, Dataset jeu) {
		super();
		this.contacts = contacts;
		this.jeu = jeu;
	}
	public Collection<Contact> getContacts() {
		return contacts;
	}
	public Dataset getJeu() {
		return jeu;
	}

	public int compareTo(ContactsJeu o) {
		return o.getJeu().getDatsId() - jeu.getDatsId();
	}
	
	/*@Override
	public boolean equals(Object o) {
		if (o instanceof ContactsJeu){
			return this.compareTo((ContactsJeu)o) == 0;
		}
		return false;
	}*/
	@Override
	public boolean equals(Object other) {
        if ( !(other instanceof ContactsJeu) ){
        	return false;
        }
        ContactsJeu castOther = (ContactsJeu) other;
        return new EqualsBuilder()
            .append(this.getJeu().getDatsId(), castOther.getJeu().getDatsId())
            .isEquals();
    }
	@Override
    public int hashCode() {
        return new HashCodeBuilder()
            .append(getJeu().getDatsId())
            .toHashCode();
    }
	
}
