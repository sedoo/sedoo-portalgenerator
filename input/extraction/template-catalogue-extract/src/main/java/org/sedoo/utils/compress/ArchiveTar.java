package org.sedoo.utils.compress;

import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.zip.GZIPInputStream;
import java.util.zip.GZIPOutputStream;

import org.apache.log4j.Logger;
import org.apache.tools.tar.TarEntry;
import org.apache.tools.tar.TarInputStream;
import org.apache.tools.tar.TarOutputStream;

/**
 * Classe pour la manipulation d'une archive Tar.
 * @author brissebr
 */
public class ArchiveTar extends ArchiveBase<TarInputStream,TarOutputStream> {

	private static Logger logger = Logger.getLogger(ArchiveTar.class);
		
	private boolean compress;
	
	/**
	 * Constructeur.
	 * @param filename nom de l'archive
	 * @param compress indique si l'archive doit être compressée avec gzip.
	 */
	public ArchiveTar(String filename, boolean compress){
		this(new File(filename),compress);
	}
	/**
	 * Constructeur (archive non compressée).
	 * @param filename
	 */
	public ArchiveTar(String filename){
		this(filename,false);
	}
	/**
	 * Constructeur.
	 * @param file archive
	 * @param compress indique si l'archive est compressée avec gzip.
	 */
	public ArchiveTar(File file, boolean compress){
		super(file);
		this.compress = compress;
	}
	/**
	 * Constructeur (archive non compressée).
	 * @param file archive
	 */
	public ArchiveTar(File file){
		this(file,false);
	}
	
	@Override
	public void openWrite() throws IOException {
		close();
		if (compress){
			this.out = new TarOutputStream(new GZIPOutputStream(new BufferedOutputStream(new FileOutputStream(file))));
		}else{
			this.out = new TarOutputStream(new BufferedOutputStream(new FileOutputStream(file)));
		}
		//Pour résoudre le problème suivant : file name '...' is too long ( > 100 bytes)
		out.setLongFileMode(TarOutputStream.LONGFILE_GNU);
		cptEntry = 0;
	}
	
	private void openRead() throws IOException {
		close();
		if (compress){
			this.in = new TarInputStream(new GZIPInputStream(new BufferedInputStream(new FileInputStream(file))));
		}else{
			this.in = new TarInputStream(new BufferedInputStream(new FileInputStream(file)));
		}
	}
	
	@Override
	protected void addEntry(BufferedInputStream origin, String entryName,long entrySize) throws IOException {
		TarEntry tarEntry = new TarEntry(entryName);
		tarEntry.setSize(entrySize);

		out.putNextEntry(tarEntry);
		int count;
		while( ( count = origin.read(data, 0, BUFFER_SIZE ) ) != -1 ){
			out.write(data, 0, count);
		}
		origin.close();		
		out.closeEntry();
	}
		
	@Override
	public void extract(String directory) throws IOException{
		logger.debug("extract()");
		openRead();		
		int count;
		TarEntry entry;
		while( ( entry = in.getNextEntry() ) != null ){
			logger.debug("Entry: "+entry.getName());
			if( !entry.isDirectory() ){
				if (!directory.endsWith(File.separator)){
					directory += File.separator;
				}
				File destFile = new File(directory + entry.getName());
				if (destFile.getParentFile() != null){
					destFile.getParentFile().mkdirs();
				}
				BufferedOutputStream dest = new BufferedOutputStream(new FileOutputStream(destFile), BUFFER_SIZE );
				while( (count = in.read( data, 0, BUFFER_SIZE ) ) != -1 ) {
					dest.write( data, 0, count );
				}
				dest.close();
			}
		}

	}

}
