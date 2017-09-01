# Création de dats_uuid
alter table dataset add dats_uuid VARCHAR(50);

#Passy 74cde792-584b-11e3-XXXX-ce3f5508acd9
update dataset set dats_uuid = '74cde792-584b-11e3' || to_char(-1 * dats_id, '0009') || '-ce3f5508acd9' ;

#Mistrals 74bbe692-584b-11e3-XXXX-ce3f5508acd9
update dataset set dats_uuid = '74bbe692-584b-11e3' || to_char(-1 * dats_id, '0009') || '-ce3f5508acd9' ;


alter table dataset alter COLUMN dats_uuid set not null;