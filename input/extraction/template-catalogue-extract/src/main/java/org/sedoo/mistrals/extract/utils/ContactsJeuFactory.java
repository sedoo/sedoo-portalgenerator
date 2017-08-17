package org.sedoo.mistrals.extract.utils;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

import org.sedoo.mistrals.bd.beans.Dataset;

public final class ContactsJeuFactory {

	private ContactsJeuFactory() {}
	
	public static ContactsJeu getContacts(Connection dbCon, Dataset dats) throws SQLException{
		ContactsJeu contacts = new ContactsJeu(dats);
		
		PreparedStatement stmt = dbCon.prepareStatement(
				"SELECT dats_id, contact_type_id, pers_name, pers_email_1, coalesce(org_fname,org_sname,'Unknown Affiliation') AS org_name, contact_type_name " +
				"FROM dats_originators JOIN personne USING (pers_id) JOIN organism USING (org_id) JOIN contact_type USING (contact_type_id) " +
				"WHERE dats_id = ?");
		
		stmt.setInt(1, dats.getDatsId());
		ResultSet rs = stmt.executeQuery();
		while (rs.next()) {
			contacts.getContacts().add(ContactFactory.getPi(rs.getString("pers_name"), rs.getString("pers_email_1"),rs.getString("org_name"),rs.getString("contact_type_name")));
		}
		stmt.close();
		
		return contacts;
		
	}
	
}
