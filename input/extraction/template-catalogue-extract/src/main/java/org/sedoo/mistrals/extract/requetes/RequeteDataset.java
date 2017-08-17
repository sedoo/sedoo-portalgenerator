package org.sedoo.mistrals.extract.requetes;

import java.util.Collections;
import java.util.List;

import org.sedoo.mistrals.bd.utils.Periode;
import org.sedoo.mistrals.bd.utils.Zone;

/**
 * Requete d'extraction pour 1 dataset.
 * @author brissebr
 */
public class RequeteDataset extends RequeteBase {

	public RequeteDataset(int datsId, List<Integer> placeIds, List<Integer> varIds, Periode periode, Zone zone, 
			String compression, boolean withFlag, boolean withDelta, String projet) {
		super(Collections.singletonList(datsId),placeIds,varIds,periode,zone,compression,withFlag,withDelta,projet);
	}
		
	public int getDatsId() {
		return getDatsIds().get(0);
	}
			
}
