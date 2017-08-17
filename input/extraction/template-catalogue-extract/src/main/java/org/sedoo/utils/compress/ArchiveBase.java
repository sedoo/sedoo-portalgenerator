package org.sedoo.utils.compress;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.util.zip.GZIPInputStream;

import org.apache.log4j.Logger;


public abstract class ArchiveBase<I extends InputStream,O extends OutputStream> implements Archive {

	private static Logger logger = Logger.getLogger(ArchiveBase.class);
	
	protected File file;
	
	protected I in;
	protected O out;
		
	protected byte[] data = new byte[ BUFFER_SIZE ];
	
	protected int cptEntry = 0;
	
	public ArchiveBase(String filename){
		this(new File(filename));
	}

	public ArchiveBase(File file){
		this.file = file;
	}
	
	public abstract void extract(String directory) throws IOException;
	public abstract void openWrite() throws IOException;
	
	protected abstract void addEntry(BufferedInputStream origin,String entryName,long entrySize) throws IOException;
	
	public boolean addEntry(File entry, boolean storePath, File rootPath, boolean delete) throws IOException {
		if (out == null){
			throw new IOException("Archive is not opened for writing");
		}
		try{
			BufferedInputStream origin = null;
			String entryName = null;
			long entrySize = entry.length();
			if (storePath){
				if (rootPath == null){
					entryName = entry.getPath();
				}else{
					entryName = entry.getAbsolutePath().replaceFirst(rootPath.getAbsolutePath(), "");
				}
			}else{
				entryName = entry.getName();
			}
			if (entry.getName().endsWith(".gz")){
				try{
					origin = new BufferedInputStream(new GZIPInputStream(new FileInputStream(entry), BUFFER_SIZE));
					entryName = entryName.substring(0,entryName.length()-3);
					entrySize = ArchiveUtils.getGZipUncompressedSize(entry);
					logger.debug("Uncompressed size: " + entrySize);
				}catch(IOException e){
					logger.warn(e);
					logger.warn("MIME Type: " + ArchiveUtils.getMIMEType(entry));
					origin = new BufferedInputStream( new FileInputStream(entry), BUFFER_SIZE);
				}				
			}else{
				origin = new BufferedInputStream( new FileInputStream(entry), BUFFER_SIZE);
			}

			addEntry(origin,entryName,entrySize);

			origin.close();		
						
			//Suppression du fichier
			if (delete){
				if(entry.delete()){
					logger.debug("File successfully deleted");
				}else{
					logger.warn("File not deleted");
				}
			}
		}catch(IOException e){
			logger.warn("Error while adding file to archive. Cause: "+e);
			return false;
		}
		cptEntry++;
		return true;
	}

	public boolean addEntry(File entry) throws IOException{
		return addEntry(entry,true,false);
	}
	
	public boolean addEntry(File entry, File rootPath) throws IOException{
		return addEntry(entry,rootPath,false);
	}

	public boolean addEntry(File entry, boolean storePath) throws IOException{
		return addEntry(entry,storePath,false);
	}
	
	public boolean addEntry(File entry, boolean storePath, boolean delete) throws IOException {
		return addEntry(entry,storePath,null,delete);		
	}
	
	public boolean addEntry(File entry, File rootPath, boolean delete) throws IOException {
		return addEntry(entry,true,rootPath,delete);		
	}
	
	public void close() throws IOException {
		if (out != null && cptEntry > 0){
			this.out.close();
		}
		if (in != null){
			this.in.close();
		}
		this.out = null;
		this.in = null;
	}
	
}
