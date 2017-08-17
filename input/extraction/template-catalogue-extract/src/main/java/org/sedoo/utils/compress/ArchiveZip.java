package org.sedoo.utils.compress;

import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.zip.ZipEntry;
import java.util.zip.ZipInputStream;
import java.util.zip.ZipOutputStream;

import org.apache.log4j.Logger;

/**
 * Classe pour la manipulation d'une archive zip.
 * @author brissebr
 */
public class ArchiveZip extends ArchiveBase<ZipInputStream,ZipOutputStream> {

	private static Logger logger = Logger.getLogger(ArchiveZip.class);
	
	public ArchiveZip(String filename){
		this(new File(filename));
	}

	public ArchiveZip(File file){
		super(file);
	}

	@Override
	public void openWrite() throws IOException{
		close();
		this.out = new ZipOutputStream(new BufferedOutputStream(new FileOutputStream(file)));
		cptEntry = 0;
	}
	
	private void openRead() throws IOException{
		close();
		this.in = new ZipInputStream(new BufferedInputStream(new FileInputStream(file)));
	}
			
	@Override
	protected void addEntry(BufferedInputStream origin, String entryName,long entrySize) throws IOException {
		ZipEntry zipEntry = new ZipEntry(entryName);

		out.putNextEntry(zipEntry);
		int count;
		while( ( count = origin.read(data, 0, BUFFER_SIZE ) ) != -1 ){
			out.write(data, 0, count);
		}
	}
	
	@Override
	public void extract(String directory) throws IOException{
		logger.debug("extract()");
		openRead();		
		int count;
		ZipEntry entry;
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
