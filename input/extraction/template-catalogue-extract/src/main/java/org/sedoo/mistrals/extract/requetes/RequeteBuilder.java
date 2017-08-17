package org.sedoo.mistrals.extract.requetes;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

import org.apache.log4j.Logger;
import org.sedoo.mistrals.bd.beans.DataAvailability;
import org.sedoo.mistrals.bd.beans.DatsData;
import org.sedoo.mistrals.bd.dao.DataAvailabilityDAO;
import org.sedoo.mistrals.bd.dao.DatsDataDAO;
import org.sedoo.mistrals.bd.utils.Periode;
import org.sedoo.mistrals.bd.utils.Zone;
import org.sedoo.mistrals.extract.RequeteXml;
import org.sedoo.utils.LocalisationConverter;

/**
 * Pour découper une requeteXml (par exemple, 1 requete par jeu). 
 * @author brissebr
 */
public class RequeteBuilder {

	private static Logger logger = Logger.getLogger(RequeteBuilder.class);
	
	private Connection dbCon;
	
	public RequeteBuilder(Connection con) {
		this.dbCon = con;
	}
			
	private List<Integer> getAvailablePlacesOld(int datsId, Zone zone, List<Integer> placeIds) throws SQLException{
		/*String query = "SELECT distinct place_id FROM data_availability";	
		if (!zone.isNull()){
			query += " JOIN place USING (place_id) JOIN boundings USING (bound_id)";
		}
		query += " WHERE ins_dats_id in (SELECT ins_dats_id FROM dats_data WHERE dats_id = ?)";
		if (!zone.isNull()){
			query += " AND west_bounding_coord <= ? AND east_bounding_coord >= ? " +
					"AND north_bounding_coord >= ? AND south_bounding_coord <= ?";
		}*/
		
		String query = "SELECT distinct place_id FROM data_availability" +
				" WHERE ins_dats_id in (SELECT ins_dats_id FROM dats_data WHERE dats_id = ?)";
		if (!zone.isNull()){
			query += " AND lon_min <= ? AND lon_max >= ? AND lat_max >= ? AND lat_min <= ?";
		}
		
		PreparedStatement stmt = dbCon.prepareStatement(query);
		
		stmt.setInt(1, datsId);
		if (!zone.isNull()){
			stmt.setInt(2, LocalisationConverter.latLonDoubleToInt(zone.getLonMax()));
			stmt.setInt(3, LocalisationConverter.latLonDoubleToInt(zone.getLonMin()));
			stmt.setInt(4, LocalisationConverter.latLonDoubleToInt(zone.getLatMin()));
			stmt.setInt(5, LocalisationConverter.latLonDoubleToInt(zone.getLatMax()));
		}
				
		logger.debug(stmt);
		ResultSet rs = stmt.executeQuery();
		List<Integer> places = new ArrayList<Integer>();
		while (rs.next()) {
			places.add(rs.getInt("place_id"));
		}
			
		if (!placeIds.isEmpty()){
			places.retainAll(placeIds);
		}
		rs.close();
		stmt.close();
		return places;
	}
	/*
	private List<Integer> getAvailablePlaces(String datsIds, List<Integer> placeIds) throws SQLException{
		if (datsIds == null || datsIds.isEmpty()){
			return null;
		}
		
		Statement stmt = dbCon.createStatement();
		String query = "SELECT distinct place_id FROM data_availability WHERE ins_dats_id in (" +
		"SELECT ins_dats_id FROM dats_data WHERE dats_id in (" + datsIds + "))";
		logger.debug(query);
		ResultSet rs = stmt.executeQuery(query);
		List<Integer> places = new ArrayList<Integer>();
		while (rs.next()) {
			places.add(rs.getInt("place_id"));
		}
			
		if (!placeIds.isEmpty()){
			places.retainAll(placeIds);
		}
		rs.close();
		stmt.close();
		return places;
	}
	
	private List<Integer> getAvailableVariables(int datsId, List<Integer> varIds) throws SQLException{
		PreparedStatement stmt = dbCon.prepareStatement(
				"SELECT distinct var_id FROM data_availability WHERE ins_dats_id in (" +
				"SELECT ins_dats_id FROM dats_data WHERE dats_id = ?)");

		stmt.setInt(1, datsId);
						
		logger.debug(stmt);
		ResultSet rs = stmt.executeQuery();
		List<Integer> vars = new ArrayList<Integer>();
		while (rs.next()) {
			vars.add(rs.getInt("var_id"));
		}

		if (!varIds.isEmpty()){
			vars.retainAll(varIds);
		}
		rs.close();
		stmt.close();
		return vars;
	}
	
	private List<Integer> getAvailableVariables(String datsIds, List<Integer> varIds) throws SQLException{
		if (datsIds == null || datsIds.isEmpty()){
			return null;
		}
		Statement stmt = dbCon.createStatement();
		String query = "SELECT distinct var_id FROM data_availability WHERE ins_dats_id in (" +
				"SELECT ins_dats_id FROM dats_data WHERE dats_id in (" + datsIds + "))";
		logger.debug(query);
		ResultSet rs = stmt.executeQuery(query);
		List<Integer> vars = new ArrayList<Integer>();
		while (rs.next()) {
			vars.add(rs.getInt("var_id"));
		}

		if (!varIds.isEmpty()){
			vars.retainAll(varIds);
		}
		rs.close();
		stmt.close();
		return vars;
	}*/
	/*
	private Periode getAvailablePeriod(String datsIds) throws SQLException{
		if (datsIds == null || datsIds.isEmpty()){
			return null;
		}
		
		Statement stmt = dbCon.createStatement();
		String query = "SELECT min(date_begin) AS t_min, max(date_end) AS t_max FROM data_availability " +
				"WHERE ins_dats_id in (SELECT ins_dats_id FROM dats_data WHERE dats_id in (" + datsIds + "))";
		logger.debug(query);
		ResultSet rs = stmt.executeQuery(query);

		if (rs.next()) {
			Timestamp tMin = rs.getTimestamp("t_min");
			Timestamp tMax = rs.getTimestamp("t_max");
			
			Calendar cal = Calendar.getInstance();
			cal.setTime(tMax);
			cal.add(Calendar.DATE, 1);
			tMax = new Timestamp(cal.getTimeInMillis());
			
			return new Periode(tMin, tMax);
		}
		
		return null;
	}
		*/
	/*
	private Periode getAvailablePeriod(int datsId) throws SQLException{
		PreparedStatement stmt = dbCon.prepareStatement(
				"SELECT min(date_begin) AS t_min, max(date_end) AS t_max FROM data_availability " +
				"WHERE ins_dats_id in (SELECT ins_dats_id FROM dats_data WHERE dats_id = ?);");
		
		stmt.setInt(1, datsId);
		
		logger.debug(stmt);
		ResultSet rs = stmt.executeQuery();

		if (rs.next()) {
			Timestamp tMin = rs.getTimestamp("t_min");
			Timestamp tMax = rs.getTimestamp("t_max");
			
			Calendar cal = Calendar.getInstance();
			cal.setTime(tMax);
			cal.add(Calendar.DATE, 1);
			tMax = new Timestamp(cal.getTimeInMillis());
			
			return new Periode(tMin, tMax);
		}
		return null;
	}
			*/
	
	
	/**
	 * Filtre la liste des jeux demandés par l'utilisateur en utilisant les droits d'accès.
	 * @param datsIds jeux demandés
	 * @param roles roles de l'utilisateur
	 * @return liste des jeux autorisés pour cette requête
	 * @throws SQLException
	 */
	private List<Integer> filtreDats(List<Integer> datsIds, Collection<String> roles) throws SQLException{
		
		String whereRole = "";
		for (int i = 0;i < roles.size();i++){
			whereRole += ",?";
		}
		
		PreparedStatement stmt = dbCon.prepareStatement(
				"SELECT DISTINCT dats_id from dats_role JOIN role USING (role_id) JOIN dats_data using (dats_id)" +
				" WHERE role_name IN (" + whereRole.substring(1) + ")");
		
		int i = 1;
		for (String role: roles){
			stmt.setString(i++, role);
		}
				
		logger.debug(stmt);
		ResultSet rs = stmt.executeQuery();
		List<Integer> dats = new ArrayList<Integer>();
		while (rs.next()) {
			dats.add(rs.getInt("dats_id"));
		}
	
		if (!datsIds.isEmpty()){
			dats.retainAll(datsIds);
		}
		return dats;
	}
	
	/**
	 * Vérifie et affine une requête (en utilisant les roles et la table data_availability).
	 * @param reqXml
	 * @return requête dans laquelle il ne reste plus que ce qui est disponible, null si rien n'a été trouvé dans la base.
	 * @throws SQLException
	 */
	public RequeteBase buildRequete(RequeteXml reqXml) throws SQLException{
		
		List<Integer> dats = filtreDats(reqXml.getDatsIds(),reqXml.getUtilisateur().getRoles());
		if (dats.isEmpty()){
			logger.info("No data (dats is empty)");
		}else{
			Periode periode = DataAvailabilityDAO.getService().getAvailablePeriod(dbCon, reqXml.getDatsIds(), reqXml.getZone(), reqXml.getPeriode(), reqXml.getVariablesIds(), reqXml.getPlacesIds());
			if (periode == null){
				logger.warn("No data for this request (periode is null)");
			}else{
				Collection<DataAvailability> das = DataAvailabilityDAO.getService().search(dbCon, reqXml.getDatsIds(), reqXml.getZone(), periode, reqXml.getVariablesIds(), reqXml.getPlacesIds());

				if (das.isEmpty()){
					logger.warn("No data for this request (nothing found in data_availability)");
				}else{
					Set<Integer> places = new HashSet<Integer>();
					Set<Integer> variables = new HashSet<Integer>();
					Set<Integer> insDatasets = new HashSet<Integer>();
					for (DataAvailability da: das){
						places.add(da.getPlaceId());
						variables.add(da.getVarId());
						insDatasets.add(da.getInsDatsId());
					}
					
					//On ne garde que les jeux pour lesquels il y a des données
					Set<Integer> datasets = new HashSet<Integer>();
					for (Integer insDatsId: datasets){
						Collection<DatsData> dds = DatsDataDAO.getService().getByInsDatsId(dbCon, insDatsId);
						for(DatsData dd: dds){
							datasets.add(dd.getDatsId());
						}
					}
					dats.retainAll(datasets);
					
					return new RequeteBase(dats, new ArrayList<Integer>(places), new ArrayList<Integer>(variables), periode, reqXml.getZone(), 
							reqXml.getCompression(), reqXml.isWithFlag(), reqXml.isWithDelta(), reqXml.getProjet());
				}
			}
		}
		return null;
	}
	
			
	/**
	 * Vérifie et affine une requête (en utilisant les roles et la table data_availability) puis découpe la requête par jeu de données (table dataset).
	 * @param reqXml
	 * @return requêtes portant sur un seul jeu, vide si rien n'a été trouvé.
	 * @throws SQLException
	 */
	public Collection<RequeteDataset> splitRequeteXml(RequeteXml reqXml) throws SQLException{
		List<Integer> dats = filtreDats(reqXml.getDatsIds(),reqXml.getUtilisateur().getRoles());
		
		Collection<RequeteDataset> reqs = new ArrayList<RequeteDataset>();
		for (int datsId: dats){
			Periode periode = DataAvailabilityDAO.getService().getAvailablePeriod(dbCon, datsId, reqXml.getZone(), reqXml.getPeriode(), reqXml.getVariablesIds(), reqXml.getPlacesIds());
						
			if (periode == null){
				logger.warn("No data for dataset " + datsId + " (periode is null)");
			}else{
				Collection<DataAvailability> das = DataAvailabilityDAO.getService().search(dbCon, datsId, reqXml.getZone(), periode, reqXml.getVariablesIds(), reqXml.getPlacesIds());
				if (das.isEmpty()){
					logger.warn("No data for this request (nothing found in data_availability)");
				}else{
					Set<Integer> places = new HashSet<Integer>();
					Set<Integer> variables = new HashSet<Integer>();
					for (DataAvailability da: das){
						places.add(da.getPlaceId());
						variables.add(da.getVarId());
					}
					
					if (places.size() == 1){
						reqs.add(new RequeteDatasetStation(datsId, places.iterator().next(), new ArrayList<Integer>(variables), periode, reqXml.getZone(), 
								reqXml.getCompression(), reqXml.isWithFlag(), reqXml.isWithDelta(), reqXml.getProjet()));
					}else{
						reqs.add(new RequeteDataset(datsId, new ArrayList<Integer>(places), new ArrayList<Integer>(variables), periode, reqXml.getZone(), 
							reqXml.getCompression(), reqXml.isWithFlag(), reqXml.isWithDelta(), reqXml.getProjet()));
					}
				}
			}
		}
		return reqs;
	}
		
}
