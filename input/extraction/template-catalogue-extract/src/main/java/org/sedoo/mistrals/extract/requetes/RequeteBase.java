package org.sedoo.mistrals.extract.requetes;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.sql.Timestamp;
import java.util.Date;
import java.util.List;

import org.sedoo.mistrals.bd.utils.Periode;
import org.sedoo.mistrals.bd.utils.Zone;
import org.sedoo.utils.LocalisationConverter;

public class RequeteBase{
	
	private Timestamp date;

	private List<Integer> datsIds;
	private List<Integer> placeIds;
	private List<Integer> varIds;

	private Periode periode;
	private Zone zone;

	private String compression;

	private boolean withFlag;
	private boolean withDelta;

	private String projet;

	public RequeteBase(List<Integer> datsIds, List<Integer> placeIds, List<Integer> varIds, Periode periode, Zone zone,
			String compression, boolean withFlag, boolean withDelta, String projet) {
		super();
		this.datsIds = datsIds;
		this.placeIds = placeIds;
		this.varIds = varIds;
		this.periode = periode;
		this.zone = zone;
		this.compression = compression;
		this.withFlag = withFlag;
		this.withDelta = withDelta;
		this.projet = projet;
		this.date = new Timestamp(new Date().getTime());
	}

	public PreparedStatement toSQLMesures(Connection dbCon, String orderBy) throws SQLException{
		return toSQLMesures(dbCon, "*", orderBy, false, true, false);
	}

	public PreparedStatement toSQLMesures(Connection dbCon, String orderBy, boolean withPlace) throws SQLException{
		return toSQLMesures(dbCon, "*", orderBy, withPlace, true, false);
	}

	public PreparedStatement toSQLMesures(Connection dbCon, String orderBy, boolean withPlace, boolean excludeSequences) throws SQLException{
		return toSQLMesures(dbCon, "*", orderBy, withPlace, true, excludeSequences);
	}
	
	public PreparedStatement toSQLMesures(Connection dbCon, String orderBy, boolean withPlace, boolean withLocalisation, boolean excludeSequences) throws SQLException{
		return toSQLMesures(dbCon, "*", orderBy, withPlace, withLocalisation, excludeSequences);
	}

	public PreparedStatement toSQLSequences(Connection dbCon) throws SQLException{
		String query = "SELECT * FROM sequence JOIN localisation on (sequence_loc_begin_id = localisation_id)" +
				" JOIN boundings USING (bound_id)" +
				" WHERE " + getWhereDataset() + " AND sequence_date_begin BETWEEN ? AND ? AND " + getWhereBoundings();
		
		PreparedStatement stmt = dbCon.prepareStatement(query);
		stmt.setTimestamp(1, getDateMin());
		stmt.setTimestamp(2, getDateMax());
		setWhereBoundingsParameters(stmt,3);
		return stmt;
	}

	/**
	 * 
	 * @param dbCon
	 * @param orderBy colonnes à utiliser pour le tri
	 * @param withPlace jointure sur la table place
	 * @return
	 * @throws SQLException
	 */
	public PreparedStatement toSQLMesures(Connection dbCon, String select , String orderBy, boolean withPlace, boolean withLocalisation, boolean excludeSequences) throws SQLException{
		String query = "SELECT " + select +" FROM mesure ";
				
		if (withLocalisation){
			query += "JOIN localisation USING (localisation_id) JOIN boundings USING (bound_id) ";
		}
		
		if (withPlace){
			query += "JOIN place USING (place_id) ";
		}

		String wherePlace = "";				
		if (getPlaceIds() != null && !getPlaceIds().isEmpty()){
			wherePlace = " AND place_id IN (";
			for (int i = 0;i < getPlaceIds().size();i++){
				wherePlace += getPlaceIds().get(i);
				if (i < getPlaceIds().size() - 1){
					wherePlace += ",";	
				}
			}
			wherePlace += ")";
		}

		query += "WHERE " + getWhereDataset()  + wherePlace +  " AND mesure_date BETWEEN ? AND ? ";
		
		if (withLocalisation){
			query += "AND " + getWhereBoundings();
		}

		if (excludeSequences){
			query += " AND sequence_id is null";
		}

		if (orderBy != null){
			query += " ORDER BY " + orderBy;
		}
		
		PreparedStatement stmt = dbCon.prepareStatement(query);
		stmt.setTimestamp(1, getDateMin());
		stmt.setTimestamp(2, getDateMax());
		if (withLocalisation){
			setWhereBoundingsParameters(stmt, 3);
		}
		
		return stmt;
	}
	
	public String getWhereDataset(){			
		String whereDataset = "ins_dats_id IN (SELECT DISTINCT ins_dats_id FROM dats_data WHERE dats_id IN (";
		for (int i = 0;i < getDatsIds().size();i++){
			whereDataset += getDatsIds().get(i);
			if (i < getDatsIds().size() - 1){
				whereDataset += ",";	
			}
		}
		whereDataset += "))";
		return whereDataset;
	}								
	private String getWhereBoundings(){
		String where = "";
		if (!getZone().isNull()){
			where += "west_bounding_coord = east_bounding_coord AND north_bounding_coord = south_bounding_coord " +
					"AND west_bounding_coord between ? AND ? AND north_bounding_coord BETWEEN ? AND ? ";
		}

		return where;
	}

	private void setWhereBoundingsParameters(PreparedStatement stmt, int paramIndex) throws SQLException{
		if (!getZone().isNull()){
			stmt.setInt(paramIndex++, LocalisationConverter.latLonDoubleToInt(getZone().getLonMin()));
			stmt.setInt(paramIndex++, LocalisationConverter.latLonDoubleToInt(getZone().getLonMax()));
			stmt.setInt(paramIndex++, LocalisationConverter.latLonDoubleToInt(getZone().getLatMin()));
			stmt.setInt(paramIndex, LocalisationConverter.latLonDoubleToInt(getZone().getLatMax()));
		}
	}
	
	public Timestamp getDate() {
		return date;
	}

	public List<Integer> getDatsIds() {
		return datsIds;
	}

	public List<Integer> getPlaceIds() {
		return placeIds;
	}

	public List<Integer> getVarIds() {
		return varIds;
	}

	public Periode getPeriode() {
		return periode;
	}

	public Timestamp getDateMin() {
		return periode.getDateMin();
	}

	public Timestamp getDateMax() {
		return periode.getDateMax();
	}

	public Zone getZone() {
		return zone;
	}

	public String getCompression() {
		return compression;
	}

	public boolean isWithDelta() {
		return withDelta;
	}

	public boolean isWithFlag() {
		return withFlag;
	}

	public String getProjet() {
		return projet;
	}
}
