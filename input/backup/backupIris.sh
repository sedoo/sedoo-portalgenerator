#!/bin/sh
#-----------------------------------------------------------------------
# modification:   8 Avril 2006
# origine:        MeteoFrance CNRM     <michel.tyteca@meteo.fr>
# Objet:          test de la presence d'un verrou pour eviter d'activer
#                 +sieurs fois la synchro.
#                 Par precaution, le verrou n'est valable qu'un temps donne:
#                 ici 6H = 6x3600=21600 secondes
# modif2:	  Le fichier /www/etc/ORIGINE designe le maitre
#                 Par defaut aero (TOULOUSE)
# ----------------------------------------------------------------------

#set -x
SECMAX=21600		        # Nbre de secondes de validite du verrou
RACINE=#PortalPath

run_synchro ()
{
  ORIGINE=sedoo
  CIBLE=sedoo@iris.obs-mip.fr:/mnt2/sedoo/sedoo/campagnes 
  date > $VERROU
  echo "====================================================="      > $LOG 2>&1
  echo "Synchronisation depuis '$ORIGINE' Start: " `date '+%F %T'` >> $LOG 2>&1
  echo "====================================================="     >> $LOG 2>&1
# site de campagne
    echo "Synchronisation "${RACINE} >> $LOG 2>&1
  rsync -ax --delete --exclude tmp/ ${RACINE} ${CIBLE} >> $LOG 2>&1
  echo " End Synchronisation " `date '+%F %T'`  >> $LOG 2>&1

  rm -f $VERROU
}

cd $RACINE
# date + jour de la semaine pour ne pas saturer le repertoire de logs
LOG=${RACINE}/tmp/backup_`date '+%Y%m%d'`
VERROU=${RACINE}/tmp/synchro_Iris.lock
if [ ! -f $VERROU ] ; then
  run_synchro
else
  AGE=`stat -c '%Y' $VERROU`
  HH=`date '+%s'`
  OLD=`expr $AGE + $SECMAX`
  if [ $OLD -lt $HH ] ; then
   run_synchro
  else
   echo " End SynchronisatioO Error: lock in use " `date '+%F %T'`  >> $LOG 2>&1
  fi
fi
 
