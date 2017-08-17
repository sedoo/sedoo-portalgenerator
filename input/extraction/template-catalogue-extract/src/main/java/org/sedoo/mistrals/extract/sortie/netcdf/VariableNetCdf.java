package org.sedoo.mistrals.extract.sortie.netcdf;

import java.io.IOException;
import java.util.Arrays;

import org.apache.log4j.Logger;

import ucar.ma2.ArrayDouble;
import ucar.ma2.InvalidRangeException;
import ucar.nc2.NetcdfFileWriter;
import ucar.nc2.Variable;

/**
 * Variable NetCdf avec une dimension 'unlimited'.
 * @author brissebr
 *
 */
public abstract class VariableNetCdf {

	protected static Logger logger = Logger.getLogger(VariableNetCdf.class);
	
	public static final int BUFFER_SIZE = 100;

	protected int index = 0;
	protected int passage = 0;

	protected Variable var;
		
	protected NetcdfFileWriter ncWriter;
	
	public VariableNetCdf(Variable var, NetcdfFileWriter ncWriter) {
		this.index = 0;
		this.passage = 0;
		this.var = var;
		this.ncWriter = ncWriter;
	}
		
	public Variable getVar() {
		return var;
	}
	
	/**
	 * Passe à la valeur suivante. 
	 * @throws IOException
	 */
	public void next() throws IOException{
		logger.debug("next()");
		index++;
		if (index == BUFFER_SIZE){
			ecrire();
		}
	}
	
	public abstract void ecrire() throws IOException;
	
		
	/** 1 dimension unlimited **/
	public static class D1 extends VariableNetCdf{

		private ArrayDouble.D1 data;
		
		public D1(Variable var, NetcdfFileWriter ncWriter) {
			super(var, ncWriter);
			this.data = new ArrayDouble.D1(BUFFER_SIZE);
			fill(FichierNetCdf.MISSING_VALUE);
		}
		
		private void fill(double fillValue){
			Arrays.fill((double[])this.data.getStorage(),fillValue);
		}
		
		public void setValue(double valeur) throws IOException{
			data.setDouble(index, valeur);
			/*if (index == BUFFER_SIZE){
				ecrire();
			}*/
		}

		public double getValue(){
			return data.get(index);
		}
		
		/*public void setMissingValue(String variable) throws IOException{
			setValue(FichierNetCdf.MISSING_VALUE);
		}*/

		@Override
		public void ecrire() throws IOException{
			logger.debug("D1.ecrire()");
			try{
				int[] origin = {passage * BUFFER_SIZE};
				if ( (index == BUFFER_SIZE) ){
					ncWriter.write(var, origin, data);
				}else if (index > 0 && index < BUFFER_SIZE){
					int [] shape = {index};
					ncWriter.write(var, origin, data.sectionNoReduce(new int[]{0},shape,null));
				}else if (index > BUFFER_SIZE){
					throw new ArrayIndexOutOfBoundsException(index);
				}
				this.passage++;
				this.index = 0;
				fill(FichierNetCdf.MISSING_VALUE);
			}catch(InvalidRangeException e){
				throw new IOException("Erreur lors de l'écriture de la variable " + var.getFullName(), e);
			}
		}		
	}
	
	/**
	 * Une dimension fixe et une dimension 'unlimited'.
	 * @author brissebr
	 *
	 */
	public static class D2 extends VariableNetCdf{

		private ArrayDouble.D2 data;
						
		public D2(Variable var, int size, NetcdfFileWriter ncWriter) {
			super(var, ncWriter);
			this.data = new ArrayDouble.D2(BUFFER_SIZE,size); 
			fill(FichierNetCdf.MISSING_VALUE);
		}
		
		public void setValue(int j, double valeur) {
			//logger.debug("addValue- (" + index + "," + j + ")= " + valeur);
			data.set(index, j, valeur);
			/*if (index == BUFFER_SIZE){
				ecrire();
			}*/
		}
		
		public double getValue(int j) {
			return data.get(index, j);
		}
						
		private void fill(double fillValue){
			Arrays.fill((double[])this.data.getStorage(),fillValue);
		}
		
		/*public void setMissingValue(int j, String variable) throws IOException{
			setValue(j, FichierNetCdf.MISSING_VALUE);
		}*/
		
		@Override
		public void ecrire() throws IOException{
			logger.debug("D2.ecrire()");
			try{
				int[] origin = {passage * BUFFER_SIZE, 0};
				if ( (index == BUFFER_SIZE) ){
					ncWriter.write(var, origin, data);
				}else if (index > 0 && index < BUFFER_SIZE){
					int [] shape = data.getShape();
					shape[0] = index;
					ncWriter.write(var, origin, data.sectionNoReduce(new int[]{0,0},shape,null));
				}else if (index > BUFFER_SIZE){
					throw new ArrayIndexOutOfBoundsException(index);
				}
				this.passage++;
				this.index = 0;
				fill(FichierNetCdf.MISSING_VALUE);
			}catch(InvalidRangeException e){
				throw new IOException("Erreur lors de l'écriture de la variable " + var.getFullName(), e);
			}
		}

	}
	

}
