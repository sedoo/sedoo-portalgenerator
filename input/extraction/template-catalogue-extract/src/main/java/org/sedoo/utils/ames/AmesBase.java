package org.sedoo.utils.ames;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.PrintStream;
import java.sql.Timestamp;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.List;

import org.sedoo.utils.DateUtils;

public abstract class AmesBase implements AmesFile{
		
	protected PrintStream out;
	
	private String ffi;

	private String originator;
	private String originatorOrganism;

	private String database;
	private String dataset;

	private int ivol, nvol;

	private Timestamp obsDate;
	private Timestamp prodDate;

	private List<AmesIndependentVar> independentVariables;
	private List<AmesDataVar> primaryVariables;	
	private List<AmesDataVar> auxiliaryVariables;
	private List<String> columnHeaders;
	
	private List<String> normalComments;
	private List<String> specialComments;
	
	private boolean headerOk;
	
	protected AmesBase(File file) throws IOException{
		this.out = new PrintStream(new FileOutputStream(file), true, ENCODING);
		this.primaryVariables = new ArrayList<AmesDataVar>();
		this.independentVariables = new ArrayList<AmesIndependentVar>();
		this.columnHeaders = new ArrayList<String>();
		this.auxiliaryVariables = new ArrayList<AmesDataVar>();
		this.normalComments = new ArrayList<String>();
		this.specialComments = new ArrayList<String>();
		this.headerOk = false;
	}
			
	public void init(String originator, String originatorOrganism, String database, String dataset,Timestamp obsDate, Timestamp prodDate) {
		this.originator = originator;
		this.originatorOrganism = originatorOrganism;
		this.database = database;
		this.dataset = dataset;
		this.obsDate = obsDate;
		this.prodDate = prodDate;
		this.ivol = 1;
		this.nvol = 1;
	}
	
	public void init(String database, String dataset,Timestamp obsDate, Timestamp prodDate) {
		init("Unknown Originator", "Unknown Affiliation",database, dataset, obsDate, prodDate);
	}
	
	public void close() {
		out.close();
	}
	
	protected int getTailSize(){
		return 1 + getSpecialComments().size() + 1 + getNormalComments().size() + getColumnHeaders().size();
	}
	
	public int getHeaderSize() {
		return HEADER_TOP_SIZE + getBodySize() + getTailSize(); 
	}
	
	protected abstract int getBodySize();
	public abstract int getDimNum();	
	public abstract void write(AmesDataRecord record, boolean last) throws AmesException;
	
	
	protected void writeIndependentVars() throws AmesException{
		String ligneInter = "";
		for (AmesIndependentVar indeVar: getIndependentVariables()){
			ligneInter += " " + indeVar.getInterval();
		}
		out.println(ligneInter.substring(1));
		for (AmesIndependentVar indeVar: getIndependentVariables()){
			out.println(indeVar);
		}
	}
	
	protected void writeAuxiliaryVars(){
		writeVars(auxiliaryVariables);
	}
	
	
	
	public void writeHeader() throws AmesException{
		if (primaryVariables.size() == 0){
			throw new AmesException("Empty dataset");
		}
		if (getDimNum() != independentVariables.size()){
			throw new AmesException("Wrong number of dimensions. Expected: " + getDimNum() + ", Found: " + independentVariables.size());
		}
		writeHeaderTopLines();
		
		writeIndependentVars();
		writeVars(primaryVariables);
		writeAuxiliaryVars();
				
		writeHeaderTail();
		this.headerOk = true;
	}
	
	/**
	 * Ecrit les 7 premières lignes du fichier ames.
	 * @throws AmesException
	 */
	private void writeHeaderTopLines() throws AmesException{		
		out.println(getHeaderSize() + " " + getFfi());
		out.println(AmesUtils.supprimerAccents(getOriginator()));
		out.println(AmesUtils.supprimerAccents(getOriginatorOrganism()));
		out.println(AmesUtils.supprimerAccents(getDataset()));
		out.println(AmesUtils.supprimerAccents(getDatabase()));
		out.println(getIvol() + " " + getNvol());
		if (getIvol() > getNvol()){
			throw new AmesException("IVOL > NVOL");
		}
		try{
			out.println(DateUtils.dateToString(getObsDate(), FORMAT_DATE)	+ " " + DateUtils.dateToString(getProdDate(), FORMAT_DATE));
		}catch(ParseException e){
			throw new AmesException("Error while parsing dates. Cause: " + e);
		}
	}
	
	private void writeHeaderTail() throws AmesException{
		out.println(getSpecialComments().size());
		for (String line: getSpecialComments()){
			out.println(AmesUtils.supprimerAccents(line));
		}
		
		out.println(getNormalComments().size() + getColumnHeaders().size());
		for (String line: getNormalComments()){
			out.println(AmesUtils.supprimerAccents(line));
		}
		for (String line: getColumnHeaders()){
			out.println(AmesUtils.supprimerAccents(line));
		}
	}
	
	private String writeVars(List<AmesDataVar> vars){
		String labels = "";
		out.println(vars.size());
		String ligneCoef = "";
		String ligneAbs = "";
		for (AmesDataVar var: vars){
			ligneCoef += " " + var.getScalingFactor();
			ligneAbs += " " + var.getMissingValue();
			labels += SEPARATOR + var.getLabel();
		}
		if (vars.size() > 0){
			out.println(ligneCoef.substring(1));
			out.println(ligneAbs.substring(1));
		}
		for (AmesDataVar var: vars){
			out.println(var);
		}
		return labels;
	}
	
	public void write(AmesDataRecord record) throws AmesException {
		write(record, false);
	}
	
	public void addIndependentVariable(AmesIndependentVar var) throws AmesException{		
		int nbDim = getDimNum();
		if (getIndependentVariables().size() == nbDim){
			throw new AmesException("This file cannot contain more than " + nbDim + "independent variables.");
		}
					
		getIndependentVariables().add(var);
	}
	
	public void addAuxiliaryVariable(AmesDataVar var) throws AmesException{
		getAuxiliaryVariables().add(var);
	}
	
	public void addPrimaryVariable(AmesDataVar var) throws AmesException{				
		getPrimaryVariables().add(var);
	}
	
	public void addNormalComment(String line) throws AmesException{
		getNormalComments().add(line);
	}
	
	public void addSpecialComment(String line) throws AmesException{
		getSpecialComments().add(line);
	}
	
	public String getFfi() {
		return ffi;
	}
	public void setFfi(String ffi) {
		this.ffi = ffi;
	}
	public String getOriginator() {
		return originator;
	}
	public void setOriginator(String originator) {
		this.originator = originator;
	}
	public String getOriginatorOrganism() {
		return originatorOrganism;
	}
	public void setOriginatorOrganism(String originatorOrganism) {
		this.originatorOrganism = originatorOrganism;
	}
	public String getDatabase() {
		return database;
	}
	public void setDatabase(String database) {
		this.database = database;
	}
	public String getDataset() {
		return dataset;
	}
	public void setDataset(String dataset) {
		this.dataset = dataset;
	}
	public int getIvol() {
		return ivol;
	}
	public void setIvol(int ivol) {
		this.ivol = ivol;
	}
	public int getNvol() {
		return nvol;
	}
	public void setNvol(int nvol) {
		this.nvol = nvol;
	}
	public Timestamp getObsDate() {
		return obsDate;
	}
	public void setObsDate(Timestamp obsDate) {
		this.obsDate = obsDate;
	}
	public Timestamp getProdDate() {
		return prodDate;
	}
	public void setProdDate(Timestamp prodDate) {
		this.prodDate = prodDate;
	}
	public List<String> getNormalComments() {
		return normalComments;
	}
	public void setNormalComments(List<String> normalComments) {
		this.normalComments = normalComments;
	}
	public List<String> getSpecialComments() {
		return specialComments;
	}
	public void setSpecialComments(List<String> specialComments) {
		this.specialComments = specialComments;
	}	
	public List<AmesDataVar> getPrimaryVariables() {
		return primaryVariables;
	}
	public List<String> getColumnHeaders() {
		return columnHeaders;
	}
	public List<AmesDataVar> getAuxiliaryVariables() {
		return auxiliaryVariables;
	}
	public List<AmesIndependentVar> getIndependentVariables() {
		return independentVariables;
	}
	protected boolean isHeaderOk() {
		return headerOk;
	}
}
