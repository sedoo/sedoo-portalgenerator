#! /bin/sh

date=$(date +%Y%m%d)

pathBackup=/export1/pedro/backup

echo "backupKascade: DEBUT"

echo "backupKascade: $date"

/usr/pgsql-9.0/bin/pg_dump -T mesure -T valeur -T sequence -T file -T localisation -T data_availability pedro_catalogue | gzip > $pathBackup/pedro_catalogue_meta_$date.dump.gz

cd /www/pedro
tar -czf $pathBackup/pedro_catalogue_web_$date.tar.gz *

#Annuaire
ldapsearch -xLLLw pro001 -D cn=manager,dc=pedro,dc=sedoo,dc=fr -h localhost -b dc=pedro,dc=sedoo,dc=fr > $pathBackup/pedro_users_$date.ldif

#Documents attachés
cd /export1/pedro/work/attached
tar -czf $pathBackup/pedro_catalogue_attached_$date.tar.gz *

cp /export1/pedro/work/log/dl.log $pathBackup/dl.log.$date

echo "backuppedro: suppression des vieux dumps"

find $pathBackup -xdev -mtime +30 -type f -print -exec rm {} \;

echo "backuppedro: FIN"
