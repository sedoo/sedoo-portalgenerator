package org.sedoo.mistrals.extract.utils;



public final class ContactFactory {
	
	private ContactFactory(){}
	
	public static Contact getUser(String nom, String email, String organisme){
		return new ContactUser(nom, email, organisme);
	}
	
	public static Contact getPi(String nom, String email, String organisme, String type){
		return new ContactPI(nom, email, organisme, type);
	}
	
}
