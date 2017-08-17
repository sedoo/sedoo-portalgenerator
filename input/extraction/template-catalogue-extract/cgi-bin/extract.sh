#!/bin/sh

#Définir ici l'emplacement de l'exécutable java
export JAVA_BIN=${java.bin}/java
export CLASSPATH=$CLASSPATH:lib/*

$JAVA_BIN org.sedoo.mistrals.extract.ExtractMain $1 $2 &
