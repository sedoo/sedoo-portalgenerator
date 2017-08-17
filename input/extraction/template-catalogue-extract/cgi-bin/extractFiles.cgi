#!/bin/sh

export JAVA_BIN=${java.bin}/java
export CLASSPATH=$CLASSPATH:lib/*

#Lit stdin (cas de la méthode post)
if [ "$REQUEST_METHOD" = "POST" ]; then
    read QUERY_STRING
fi 
export FICHIER_TEMP=/tmp/mistrals_files_request_`date +%m%d%Y_%H%M%S_%N`.tmp
echo $QUERY_STRING > $FICHIER_TEMP

$JAVA_BIN \
    -Dcgi.content_type=$CONTENT_TYPE \
    -Dcgi.content_length=$CONTENT_LENGTH \
    -Dcgi.request_method=$REQUEST_METHOD \
	-Dcgi.query_file=$FICHIER_TEMP \
    -Dcgi.server_name=$SERVER_NAME \
    -Dcgi.server_port=$SERVER_PORT \
    -Dcgi.script_name=$SCRIPT_NAME \
    -Dcgi.document_root=$DOCUMENT_ROOT \
    -Dcgi.script_filename=$SCRIPT_FILENAME \
    -Dcgi.path_info=$PATH_INFO \
   org.sedoo.mistrals.extract.fichiers.ExtractFilesCGI &
