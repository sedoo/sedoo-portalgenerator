package org.sedoo.utils.ames;

/**
 * Variable Ames. 
 * @author brissebr
 */
public class AmesVar {

	private String name;
	private String unit;
	private String label;
	
	protected AmesVar() {}
	
	public AmesVar(String name) {
		this(name,null,null);
	}
	
	public AmesVar(String name, String unit) {
		this(name,unit,null);
	}
	
	/**
	 * 
	 * @param name nom de la variable
	 * @param unit unité
	 * @param label nom court à utiliser comme header de colonne (si null c'est le nom qui sera utilisé)
	 */
	public AmesVar(String name, String unit, String label) {
		super();
		this.name = name;
		this.unit = unit;
		this.label = label;
	}
		
	public String toString() {
		return ((label == null)?"":label + ": ") + AmesUtils.supprimerAccents(name) + ((unit == null)?"":" (" + AmesUtils.supprimerAccents(unit) + ")");
	}
	
	public String getName() {
		return name;
	}
	public void setName(String name) {
		this.name = name;
	}
	public String getUnit() {
		return unit;
	}
	public void setUnit(String unit) {
		this.unit = unit;
	}
	public String getLabel() {
		if (label == null){
			return name.replaceAll(" ", "_");
		}
		return label;
	}
	public void setLabel(String label) {
		this.label = label;
	}
	
}
