#! /bin/sh

date=$(date +%Y%m%d)

pathBackup=#PortalPath/backup

echo "backupKascade: DEBUT"

echo "backupKascade: $date"

/usr/pgsql-9.0/bin/pg_dump -T mesure -T valeur -T sequence -T file -T localisation -T data_availability #MainProject_catalogue | gzip > $pathBackup/#MainProject_catalogue_meta_$date.dump.gz

cd /www/#MainProject
tar -czf $pathBackup/#MainProject_catalogue_web_$date.tar.gz *

#Annuaire
ldapsearch -xLLLw pro001 -D cn=manager,dc=#MainProject,dc=sedoo,dc=fr -h localhost -b dc=#MainProject,dc=sedoo,dc=fr > $pathBackup/#MainProject_users_$date.ldif

#Documents attachés
cd #PortalWorkPath/attached
tar -czf $pathBackup/#MainProject_catalogue_attached_$date.tar.gz *

cp #PortalWorkPath/log/dl.log $pathBackup/dl.log.$date

echo "backup#MainProject: suppression des vieux dumps"

find $pathBackup -xdev -mtime +30 -type f -print -exec rm {} \;

echo "backup#MainProject: FIN"
