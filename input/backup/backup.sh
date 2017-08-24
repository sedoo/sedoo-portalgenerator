#! /bin/sh

date=$(date +%Y%m%d)

pathBackup=#PortalPath/backup

echo "backup: DEBUT"
echo "backup: $date"

/usr/pgsql-9.0/bin/pg_dump -T mesure -T valeur -T sequence -T file -T localisation -T data_availability #MainProject_catalogue | gzip > $pathBackup/#MainProject_catalogue_meta_$date.dump.gz

#Annuaire
ldapsearch -xLLLw pro001 -D cn=manager,dc=#MainProject,dc=sedoo,dc=fr -h localhost -b dc=#MainProject,dc=sedoo,dc=fr > $pathBackup/#MainProject_users_$date.ldif

echo "backup: suppression des vieux dumps"

find $pathBackup -xdev -mtime +30 -type f -print -exec rm {} \;

echo "backup: FIN"
