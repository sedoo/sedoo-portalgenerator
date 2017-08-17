package org.sedoo.utils.ames;

import java.io.File;
import java.io.IOException;

public final class AmesFileFactory {

	private AmesFileFactory() {}
	
	public static AmesFile getAmesFile(File file, String ffi) throws IOException{
		if ("1001".equals(ffi)){
			return new Ames1001(file);
		}else if ("1010".equals(ffi)){
			return new Ames1010(file);
		}else if ("2160".equals(ffi)){
			return new Ames2160(file);
		}else{
			throw new AmesException("Unknown ffi " + ffi);
		}
	}
	
}
