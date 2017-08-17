package org.sedoo.mistrals.extract.sortie.netcdf;

import java.io.File;
import java.io.IOException;
import java.text.ParseException;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.apache.log4j.Logger;
import org.sedoo.utils.DateUtils;
import org.sedoo.utils.exceptions.DataNotFoundException;

import ucar.ma2.Array;
import ucar.ma2.ArrayObject;
import ucar.ma2.DataType;
import ucar.ma2.InvalidRangeException;
import ucar.nc2.Attribute;
import ucar.nc2.Dimension;
import ucar.nc2.Group;
import ucar.nc2.NetcdfFileWriter;
import ucar.nc2.NetcdfFileWriter.Version;
import ucar.nc2.Variable;

public class FichierNetCdf {
	
	private static Logger logger = Logger.getLogger(FichierNetCdf.class);

	public static final String EXTENSION = ".nc";
	public static final float MISSING_VALUE = -99999.9f;
	public static final int MISSING_VALUE_INT = -99999;

	private NetcdfFileWriter ncWriter;
	private Group rootGroup;

	private Map<String,Dimension> dimensions;

	private Map<String, VariableNetCdf.D1> variables1;
	private Map<String, VariableNetCdf.D2> variables2;
	//private Map<String, ArrayDouble.D1> data;

	public FichierNetCdf(File fichier) throws IOException {
		this.ncWriter = NetcdfFileWriter.createNew(Version.netcdf3, fichier.getAbsolutePath());
		this.rootGroup = ncWriter.getNetcdfFile().getRootGroup();
		this.variables1 = new HashMap<String, VariableNetCdf.D1>();
		this.variables2 = new HashMap<String, VariableNetCdf.D2>();
		this.dimensions = new HashMap<String, Dimension>();
	}

	public void addUnlimitedDimension(String nom){
		dimensions.put(nom, ncWriter.addUnlimitedDimension(nom));
	}

	public void addDimension(String nom, int taille){
		dimensions.put(nom, ncWriter.addDimension(rootGroup, nom, taille));
	}

	public void addGlobalAttribute(String nom, String valeur){
		ncWriter.addGroupAttribute(rootGroup, new Attribute(nom,valeur));
	}

	public void addVariable(String nom, DataType type, String dims) throws IOException, DataNotFoundException{
		Variable var = ncWriter.addVariable(rootGroup, nom, type , dims);
		if (!dims.isEmpty()){
			String[] dimNames = dims.split(" ");
			Dimension dim = dimensions.get(dimNames[0]);
			if (dim == null){
				throw new DataNotFoundException("Dimension", dimNames[0]);
			}
			if (dim.isUnlimited()){
				if (dimNames.length == 1){
					variables1.put(nom,  new VariableNetCdf.D1(var, ncWriter));
				}else if (dimNames.length == 2){
					Dimension dim2 = dimensions.get(dimNames[1]);
					if (dim2 == null){
						throw new DataNotFoundException("Dimension", dimNames[1]);
					}
					variables2.put(nom,  new VariableNetCdf.D2(var, dim2.getLength(), ncWriter));
				}else{
					throw new IOException("Variable à " + dimNames.length + " dimensions non gérées");
				}
			}
		}
	}

	public void addVariableAttribute(String variable, String nom, String valeur) throws DataNotFoundException{
		Variable var = ncWriter.findVariable(variable);
		if (var == null){
			throw new DataNotFoundException("Variable", variable);
		}else{
			ncWriter.addVariableAttribute(var, new Attribute(nom, valeur));
		}
	}

	public void addVariableAttribute(String variable, String nom, Number valeur) throws DataNotFoundException{
		Variable var = ncWriter.findVariable(variable);
		if (var == null){
			throw new DataNotFoundException("Variable", variable);
		}else{
			ncWriter.addVariableAttribute(var, new Attribute(nom, valeur));
		}
	}

	/**
	 * Ajoute une variable de type String.
	 * @param nom nom de la variable
	 * @param dims dimensions (séparées par un espace)
	 * @param maxLen longueur max des chaines
	 * @throws DataNotFoundException
	 */
	public void addStringVariable(String nom, String dims, int maxLen) throws DataNotFoundException{
		List<Dimension> dimsvar = new ArrayList<Dimension>();
		if ( dims != null && dims.length() > 1){
			String[] dimNames = dims.split(" ");
			for (String dimName: dimNames){
				Dimension dimension = dimensions.get(dimName);
				if (dimension == null){
					throw new DataNotFoundException("Dimension", dimName);
				}else{
					dimsvar.add(dimension);
				}
			}
		}
		ncWriter.addStringVariable(rootGroup, nom, dimsvar, maxLen);
	}

	/**
	 * Ecrit les données d'une variable String
	 * @param variable nom de la variable
	 * @param data
	 * @throws DataNotFoundException si la variable n'a pas été créée
	 * @throws IOException
	 * @throws InvalidRangeException
	 */
	public void addStringValues(String variable, ArrayObject data) throws DataNotFoundException, IOException, InvalidRangeException{
		Variable var = ncWriter.findVariable(variable);
		if (var == null){
			throw new DataNotFoundException("Variable", variable);
		}else{
			ncWriter.writeStringData(var, data);
		}
	}

	/**
	 * Ajoute la coordinate variable time(time).
	 * @param dateReference
	 * @throws IOException
	 */
	public void addTimeVariable(Date dateReference) throws IOException, DataNotFoundException{
		addTimeVariable(dateReference, "time");
	}

	/**
	 * Ajoute une variable time exprimée en secondes depuis dateReference.
	 * @param dateReference
	 * @param dims dimensions séparées par un espace
	 * @throws IOException
	 */
	public void addTimeVariable(Date dateReference, String dims) throws IOException, DataNotFoundException{
		addVariable("time", DataType.DOUBLE , dims);
		try{
			addVariableAttribute("time","standard_name","time");
			addVariableAttribute("time","long_name","time of measurement");
			addVariableAttribute("time","calendar","standard");
			addVariableAttribute("time","units",
					"seconds since " + DateUtils.dateToString(dateReference,"yyyy-MM-dd HH:mm:ss") + " +00:00");
		}catch(ParseException e){
			throw new IOException("Error while parsing reference date.", e);
		}catch(DataNotFoundException e){
			throw new IOException("Error while adding attributes.", e);
		}
	}

	/**
	 * Ecrit les données d'une variable.
	 * @param variable nom de la variable
	 * @param data
	 * @throws DataNotFoundException si la variable n'a pas été créée
	 * @throws IOException
	 * @throws InvalidRangeException
	 */
	public void addValues(String variable, Array data) throws DataNotFoundException, IOException, InvalidRangeException{
		Variable var = ncWriter.findVariable(variable);
		if (var == null){
			throw new DataNotFoundException("Variable", variable);
		}else{
			ncWriter.write(var, data);
		}		
	}

	public void addMeasuredParam(String nom, String nomStandard, String nomLong, String unite, String dims) throws IOException, DataNotFoundException{
		addVariable(nom, DataType.FLOAT , dims);
		try{
			if (nomStandard != null){
				addVariableAttribute(nom,"standard_name",nomStandard);	
			}
			addVariableAttribute(nom,"long_name",nomLong);
			addVariableAttribute(nom,"units",unite);
			addVariableAttribute(nom,"_FillValue", MISSING_VALUE);
			addVariableAttribute(nom,"missing_value", MISSING_VALUE);
		}catch(DataNotFoundException e){
			throw new IOException("Error while adding attributes.", e);
		}
	}

	/**
	 * Ecrit le header et permet l'écriture de la section data
	 * @throws IOException
	 */
	public void writeHeader() throws IOException{
		ncWriter.create();
	}



	/**
	 * Ajoute une valeur à une variable à 1 dimension.
	 * @param variable
	 * @param valeur
	 * @throws IOException
	 * @throws DataNotFoundException
	 */
	public void setValue(String variable, double valeur) throws IOException, DataNotFoundException{
		logger.debug("setValue-" + variable + ", " + valeur);
		
		VariableNetCdf.D1 var = variables1.get(variable);
		if (var != null){
			var.setValue(valeur);
		}else{
			throw new DataNotFoundException("Variable", variable);
		}
	}
/*
	public void addMissingValue(String variable) throws IOException, DataNotFoundException{
		addValue(variable, MISSING_VALUE);
	}
*/
	/**
	 * Ajoute une valeur à une variable à 2 dimensions.
	 * @param variable
	 * @param valeur
	 * @throws IOException
	 * @throws DataNotFoundException
	 */
	public void setValue(String variable, int dim1, double valeur) throws IOException, DataNotFoundException{
		logger.debug("setValue-" + variable + ", " + dim1 + ", " + valeur);
		VariableNetCdf.D2 var = variables2.get(variable);
		if (var != null){
			var.setValue(dim1,valeur);
		}else{
			throw new DataNotFoundException("Variable", variable);
		}
	}
	
	/**
	 * Récupère la valeur courante de la variable.
	 * @param variable
	 * @return
	 * @throws IOException
	 * @throws DataNotFoundException
	 */
	public double getValue(String variable) throws IOException, DataNotFoundException{
		VariableNetCdf.D1 var = variables1.get(variable);
		if (var != null){
			return var.getValue();
		}else{
			throw new DataNotFoundException("Variable", variable);
		}
	}
	
	/**
	 * Récupère la valeur courante de la variable.
	 * @param variable 
	 * @param dim1
	 * @throws IOException
	 * @throws DataNotFoundException
	 */
	public double getValue(String variable, int dim1) throws IOException, DataNotFoundException{
		VariableNetCdf.D2 var = variables2.get(variable);
		if (var != null){
			return var.getValue(dim1);
		}else{
			throw new DataNotFoundException("Variable", variable);
		}
	}
	
/*
	public void addMissingValue(String variable, int dim1) throws IOException, DataNotFoundException{
		addValue(variable, dim1, MISSING_VALUE);
	}*/

	/**
	 * Passe à l'indice suivant de la dimension 'unlimited'. 
	 * @throws IOException
	 */
	public void next() throws IOException{
		logger.debug("next()");
		for (VariableNetCdf.D1 var: variables1.values()){
			var.next();
		}
		for (VariableNetCdf.D2 var: variables2.values()){
			var.next();
		}
	}

	public void close() throws IOException{
		logger.debug("close()");
		for (VariableNetCdf.D1 var: variables1.values()){
			var.ecrire();
		}
		for (VariableNetCdf.D2 var: variables2.values()){
			var.ecrire();
		}

		ncWriter.close();
	}

}
