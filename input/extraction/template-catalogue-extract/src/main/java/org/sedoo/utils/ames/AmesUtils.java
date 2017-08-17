package org.sedoo.utils.ames;

import java.text.Normalizer;

public final class AmesUtils {

	private AmesUtils() {}
	
	/**
	 * Supprime les accents pour rendre la chaine compatible avec le format Ames.
	 * @param source
	 * @return
	 */
	public static String supprimerAccents(String source) {
		return Normalizer.normalize(source, Normalizer.Form.NFD).replaceAll("[\u0300-\u036F]", "").replaceAll("°", "deg");
	}
	
	/**
	 * Détermine le code valeur absente à partir de la valeur max d'une variable.
	 * @param valMax
	 * @return
	 */
	public static double calculValAbs(double valMax){
		return calculValAbs(valMax,9.9);
	}
	
	private static double calculValAbs(double valMax, double valAbs){
		if (valAbs > ( valMax * 100.0) ){
			return valAbs;
		}else{
			return calculValAbs(valMax, (valAbs * 10.0) + 0.9);
		}
	}
	
}
