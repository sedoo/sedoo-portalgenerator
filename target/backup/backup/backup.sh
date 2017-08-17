#! /bin/sh

date=$(date +%Y%m%d)

pathBackup=/export1/parcs/backup

echo "backupKascade: DEBUT"

echo "backupKascade: $date"

/usr/pgsql-9.0/bin/pg_dump -T mesure -T valeur -T sequence -T file -T localisation -T data_availability parcs_catalogue | gzip > $pathBackup/parcs_catalogue_meta_$date.dump.gz

cd /www/parcs
tar -czf $pathBackup/parcs_catalogue_web_$date.tar.gz *

#Annuaire
ldapsearch -xLLLw pro001 -D cn=manager,dc=parcs,dc=sedoo,dc=fr -h localhost -b dc=parcs,dc=sedoo,dc=fr > $pathBackup/parcs_users_$date.ldif

#Documents attachés
cd /export1/parcs/work/attached
tar -czf $pathBackup/parcs_catalogue_attached_$date.tar.gz *

cp /export1/parcs/work/log/dl.log $pathBackup/dl.log.$date

echo "backupparcs: suppression des vieux dumps"

find $pathBackup -xdev -mtime +30 -type f -print -exec rm {} \;

echo "backupparcs: FIN"
