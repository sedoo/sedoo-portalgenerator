package org.sedoo.utils.compress;

import java.io.File;
import java.io.IOException;
import java.io.RandomAccessFile;
import java.net.MalformedURLException;
import java.net.URL;
import java.net.URLConnection;

public final class ArchiveUtils {

	private ArchiveUtils() {}

	/**
	 * Lit dans le fichier compressé sa taille non compressée (les 4 derniers octets du fichier).  
	 * @param gzFile fichier gzip
	 * @return taille non compressée
	 * @throws IOException
	 */
	public static long getGZipUncompressedSize(File gzFile) throws IOException{
		RandomAccessFile raf = new RandomAccessFile(gzFile, "r");
		raf.seek(raf.length() - 4);
		int b4 = raf.read();
		int b3 = raf.read();
		int b2 = raf.read();
		int b1 = raf.read();
		raf.close();
		return (b1 << 24) | (b2 << 16) + (b3 << 8) + b4;
	}

	public static String getMIMEType(File file){
		if(file.isDirectory()){
			return "repertoire";
		}
		if(!file.exists()){
			return "fichier inexistant";
		}
		try{
			URL url = file.toURL();
			URLConnection connection = url.openConnection();
			return connection.getContentType();
		}catch(MalformedURLException mue){
			return mue.getMessage();
		}catch(IOException ioe){
			return ioe.getMessage();
		}
	}

}
