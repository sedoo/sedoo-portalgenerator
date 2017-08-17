package org.sedoo.mistrals.extract.requetes;

import java.util.Collections;
import java.util.List;

import org.sedoo.mistrals.bd.utils.Periode;
import org.sedoo.mistrals.bd.utils.Zone;


/**
 * Requete d'extraction pour 1 dataset et 1 station.
 * @author brissebr
 */
public class RequeteDatasetStation extends RequeteDataset {

	public RequeteDatasetStation(int datsId, int placeId, List<Integer> varIds, Periode periode, Zone zone, 
			String compression, boolean withFlag, boolean withDelta, String projet) {
		super(datsId,Collections.singletonList(placeId),varIds,periode,zone,compression,withFlag,withDelta,projet);
	}
	
	public int getPlaceId() {
		return getPlaceIds().get(0);
	}
	
}
