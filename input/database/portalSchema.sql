--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

--
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: -; Owner: postgres
--

CREATE OR REPLACE PROCEDURAL LANGUAGE plpgsql;


ALTER PROCEDURAL LANGUAGE plpgsql OWNER TO postgres;

SET search_path = public, pg_catalog;

--
-- Name: delete_dataset(integer); Type: FUNCTION; Schema: public; Owner: wwwadm
--

CREATE FUNCTION delete_dataset(datsid integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE
	sensorId integer;
	countSensor integer;
	sensorRec RECORD;
BEGIN

DELETE FROM dats_role WHERE dats_id = datsId;
DELETE FROM journal WHERE dats_id = datsId;
DELETE FROM url WHERE dats_id = datsId;
DELETE FROM dats_data_format WHERE dats_id = datsId;
DELETE FROM dats_required_data_format WHERE dats_id = datsId;
DELETE FROM dats_originators WHERE dats_id = datsId;
DELETE FROM dats_place WHERE dats_id = datsId;
DELETE FROM dats_proj WHERE dats_id = datsId;
DELETE FROM dats_type WHERE dats_id = datsId;
DELETE FROM dats_var WHERE dats_id = datsId;

DELETE FROM dats_sensor WHERE dats_id = datsId;
/*
FOR sensorRec IN SELECT * FROM dats_sensor WHERE dats_id = datsId LOOP
	sensorId := sensorRec.sensor_id
	SELECT count(*) INTO countSensor FROM dats_sensor WHERE sensor_id = sensorId;
	IF countSensor <> 1 THEN
		DELETE FROM sensor_place WHERE sensor_id = sensorId;
		DELETE FROM sensor_var WHERE sensor_id = sensorId;
		DELETE FROM dats_sensor WHERE dats_id = datsId AND sensor_id = sensorId;
		DELETE FROM sensor WHERE sensor_id = sensorId;
	END IF;
END LOOP;
*/
DELETE FROM dataset WHERE dats_id = datsId;

RETURN FOUND;

END
$$;


ALTER FUNCTION public.delete_dataset(datsid integer) OWNER TO wwwadm;

--
-- Name: duplicate_dataset(integer, character varying); Type: FUNCTION; Schema: public; Owner: wwwadm
--

CREATE FUNCTION duplicate_dataset(datsid integer, suffix character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
	newDatsId integer;
	sensorId integer;
	newSensorId integer;
BEGIN

INSERT INTO dataset (status_final_id,database_id,period_id,status_progress_id,bound_id,data_policy_id,org_id,dats_title,dats_pub_date,dats_version,dats_process_level,dats_other_cit,dats_abstract,dats_purpose,dats_elevation_min,dats_elevation_max,dats_date_begin,dats_date_end,dats_use_constraints,dats_access_constraints,dats_reference,dats_quality,dats_date_end_not_planned,is_requested) SELECT status_final_id,database_id,period_id,status_progress_id,bound_id,data_policy_id,org_id,dats_title || suffix,now(),dats_version,dats_process_level,dats_other_cit,dats_abstract,dats_purpose,dats_elevation_min,dats_elevation_max,dats_date_begin,dats_date_end,dats_use_constraints,dats_access_constraints,dats_reference,dats_quality,dats_date_end_not_planned,is_requested from dataset WHERE dats_id = datsId RETURNING dats_id INTO newDatsId;

IF FOUND THEN

	INSERT INTO dats_type (dats_type_id,dats_id) SELECT dats_type_id, newDatsId FROM dats_type WHERE dats_id = datsId;
	INSERT INTO dats_data_format (dats_id,data_format_id) SELECT newDatsId,data_format_id FROM dats_data_format WHERE dats_id = datsId;
	INSERT INTO dats_required_data_format (dats_id,data_format_id) SELECT newDatsId,data_format_id FROM dats_required_data_format WHERE dats_id = datsId;
	INSERT INTO dats_proj (project_id,dats_id) SELECT project_id,newDatsId FROM dats_proj WHERE dats_id = datsId;
	INSERT INTO dats_place (dats_id,place_id) SELECT newDatsId,place_id FROM dats_place WHERE dats_id = datsId;
	INSERT INTO dats_originators (dats_id,pers_id,contact_type_id,no_email) SELECT newDatsId,pers_id,contact_type_id,no_email FROM dats_originators WHERE dats_id = datsId;
	INSERT INTO dats_var (var_id,dats_id,unit_id,vert_level_type_id,flag_param_calcule,min_value,max_value,methode_acq,date_min,date_max,level_type) SELECT var_id,newDatsId,unit_id,vert_level_type_id,flag_param_calcule,min_value,max_value,methode_acq,date_min,date_max,level_type FROM dats_var WHERE dats_id = datsId;

	SELECT sensor_id INTO sensorId FROM dats_sensor WHERE dats_id = datsId;

	INSERT INTO sensor (manufacturer_id,gcmd_sensor_id,bound_id,sensor_model,sensor_calibration,sensor_date_begin,sensor_date_end,sensor_url,sensor_elevation) SELECT manufacturer_id,gcmd_sensor_id,bound_id,sensor_model,sensor_calibration,sensor_date_begin,sensor_date_end,sensor_url,sensor_elevation FROM sensor WHERE sensor_id = sensorId RETURNING sensor_id INTO newSensorId;
	INSERT INTO dats_sensor (sensor_id,dats_id,nb_sensor,sensor_resol_temp,sensor_lat_resolution,sensor_lon_resolution,sensor_vert_resolution,grid_original,grid_process) SELECT newSensorId,newDatsId,nb_sensor,sensor_resol_temp,sensor_lat_resolution,sensor_lon_resolution,sensor_vert_resolution,grid_original,grid_process FROM dats_sensor WHERE dats_id = datsId AND sensor_id = sensorId;
	INSERT INTO sensor_place (sensor_id,place_id,environment) SELECT newSensorId,place_id,environment FROM sensor_place WHERE sensor_id = sensorId;
	INSERT INTO sensor_var (sensor_id,var_id,sensor_precision) SELECT newSensorId,var_id,sensor_precision FROM sensor_var WHERE sensor_id = sensorId;

	RETURN newDatsId;
ELSE
	RETURN -1;
END IF;

END
$$;


ALTER FUNCTION public.duplicate_dataset(datsid integer, suffix character varying) OWNER TO wwwadm;

--
-- Name: duplicate_dataset_multi(integer, character varying); Type: FUNCTION; Schema: public; Owner: wwwadm
--

CREATE FUNCTION duplicate_dataset_multi(datsid integer, suffix character varying) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
        newDatsId integer;
        sensorIds RECORD;
sensorId integer;
        newSensorId integer;
BEGIN

INSERT INTO dataset (status_final_id,database_id,period_id,status_progress_id,bound_id,data_policy_id,org_id,dats_title,dats_pub_date,dats_version,dats_process_level,dats_other_cit,dats_abstract,dats_purpose,dats_elevation_min,dats_elevation_max,dats_date_begin,dats_date_end,dats_use_constraints,dats_access_constraints,dats_reference,dats_quality,dats_date_end_not_planned,is_requested) SELECT status_final_id,database_id,period_id,status_progress_id,bound_id,data_policy_id,org_id,dats_title || suffix,now(),dats_version,dats_process_level,dats_other_cit,dats_abstract,dats_purpose,dats_elevation_min,dats_elevation_max,dats_date_begin,dats_date_end,dats_use_constraints,dats_access_constraints,dats_reference,dats_quality,dats_date_end_not_planned,is_requested from dataset WHERE dats_id = datsId RETURNING dats_id INTO newDatsId;

IF FOUND THEN

INSERT INTO dats_type (dats_type_id,dats_id) SELECT dats_type_id, newDatsId FROM dats_type WHERE dats_id = datsId;
        INSERT INTO dats_data_format (dats_id,data_format_id) SELECT newDatsId,data_format_id FROM dats_data_format WHERE dats_id = datsId;
        INSERT INTO dats_required_data_format (dats_id,data_format_id) SELECT newDatsId,data_format_id FROM dats_required_data_format WHERE dats_id = datsId;
        INSERT INTO dats_proj (project_id,dats_id) SELECT project_id,newDatsId FROM dats_proj WHERE dats_id = datsId;
        INSERT INTO dats_place (dats_id,place_id) SELECT newDatsId,place_id FROM dats_place WHERE dats_id = datsId;
        INSERT INTO dats_originators (dats_id,pers_id,contact_type_id,no_email) SELECT newDatsId,pers_id,contact_type_id,no_email FROM dats_originators WHERE dats_id = datsId;
        INSERT INTO dats_var (var_id,dats_id,unit_id,vert_level_type_id,flag_param_calcule,min_value,max_value,methode_acq,date_min,date_max,level_type) SELECT var_id,newDatsId,unit_id,vert_level_type_id,flag_param_calcule,min_value,max_value,methode_acq,date_min,date_max,level_type FROM dats_var WHERE dats_id = datsId;

FOR sensorIds IN SELECT sensor_id FROM dats_sensor WHERE dats_id = datsId LOOP
sensorId := sensorIds.sensor_id;
        RAISE NOTICE 'Sensor %', sensorId;

INSERT INTO sensor (manufacturer_id,gcmd_sensor_id,bound_id,sensor_model,sensor_calibration,sensor_date_begin,sensor_date_end,sensor_url,sensor_elevation) SELECT manufacturer_id,gcmd_sensor_id,bound_id,sensor_model,sensor_calibration,sensor_date_begin,sensor_date_end,sensor_url,sensor_elevation FROM sensor WHERE sensor_id = sensorId RETURNING sensor_id INTO newSensorId;
        INSERT INTO dats_sensor (sensor_id,dats_id,nb_sensor,sensor_resol_temp,sensor_lat_resolution,sensor_lon_resolution,sensor_vert_resolution,grid_original,grid_process) SELECT newSensorId,newDatsId,nb_sensor,sensor_resol_temp,sensor_lat_resolution,sensor_lon_resolution,sensor_vert_resolution,grid_original,grid_process FROM dats_sensor WHERE dats_id = datsId AND sensor_id = sensorId;
        INSERT INTO sensor_place (sensor_id,place_id,environment) SELECT newSensorId,place_id,environment FROM sensor_place WHERE sensor_id = sensorId;
        INSERT INTO sensor_var (sensor_id,var_id,sensor_precision,methode_acq) SELECT newSensorId,var_id,sensor_precision,methode_acq FROM sensor_var WHERE sensor_id = sensorId;

END LOOP;
RETURN newDatsId;
ELSE
        RETURN -1;
END IF;

END
$$;


ALTER FUNCTION public.duplicate_dataset_multi(datsid integer, suffix character varying) OWNER TO wwwadm;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: boundings; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE boundings (
    bound_id integer NOT NULL,
    west_bounding_coord bigint NOT NULL,
    east_bounding_coord bigint NOT NULL,
    north_bounding_coord bigint NOT NULL,
    south_bounding_coord bigint NOT NULL
);


ALTER TABLE public.boundings OWNER TO wwwadm;

--
-- Name: boundings_bound_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE boundings_bound_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.boundings_bound_id_seq OWNER TO wwwadm;

--
-- Name: boundings_bound_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE boundings_bound_id_seq OWNED BY boundings.bound_id;


--
-- Name: contact_type; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE contact_type (
    contact_type_id integer NOT NULL,
    contact_type_name character varying(30) NOT NULL
);


ALTER TABLE public.contact_type OWNER TO wwwadm;

--
-- Name: contact_type_contact_type_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE contact_type_contact_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.contact_type_contact_type_id_seq OWNER TO wwwadm;

--
-- Name: contact_type_contact_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE contact_type_contact_type_id_seq OWNED BY contact_type.contact_type_id;


--
-- Name: country; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE country (
    country_id integer NOT NULL,
    country_name character varying(50) NOT NULL
);


ALTER TABLE public.country OWNER TO wwwadm;

--
-- Name: country_country_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE country_country_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.country_country_id_seq OWNER TO wwwadm;

--
-- Name: country_country_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE country_country_id_seq OWNED BY country.country_id;


--
-- Name: country_place; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE country_place (
    country_id integer NOT NULL,
    place_id integer NOT NULL
);


ALTER TABLE public.country_place OWNER TO wwwadm;

--
-- Name: data_availability; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE data_availability (
    ins_dats_id integer NOT NULL,
    var_id integer NOT NULL,
    place_id integer NOT NULL,
    date_begin date NOT NULL,
    date_end date NOT NULL,
    val_min double precision,
    val_max double precision,
    nb_valeurs integer NOT NULL,
    period integer,
    lat_min bigint,
    lat_max bigint,
    lon_min bigint,
    lon_max bigint
);


ALTER TABLE public.data_availability OWNER TO wwwadm;

--
-- Name: data_format; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE data_format (
    data_format_id integer NOT NULL,
    data_format_name character varying(100) NOT NULL
);


ALTER TABLE public.data_format OWNER TO wwwadm;

--
-- Name: data_format_data_format_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE data_format_data_format_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.data_format_data_format_id_seq OWNER TO wwwadm;

--
-- Name: data_format_data_format_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE data_format_data_format_id_seq OWNED BY data_format.data_format_id;


--
-- Name: data_policy; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE data_policy (
    data_policy_id integer NOT NULL,
    data_policy_name character varying(100) NOT NULL,
    data_policy_url character varying(255)
);


ALTER TABLE public.data_policy OWNER TO wwwadm;

--
-- Name: data_policy_data_policy_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE data_policy_data_policy_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.data_policy_data_policy_id_seq OWNER TO wwwadm;

--
-- Name: data_policy_data_policy_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE data_policy_data_policy_id_seq OWNED BY data_policy.data_policy_id;


--
-- Name: database; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE database (
    database_id integer NOT NULL,
    database_name character varying(250) NOT NULL,
    database_url character varying(250)
);


ALTER TABLE public.database OWNER TO wwwadm;

--
-- Name: database_database_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE database_database_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.database_database_id_seq OWNER TO wwwadm;

--
-- Name: database_database_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE database_database_id_seq OWNED BY database.database_id;


--
-- Name: dataset; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dataset (
    dats_id integer NOT NULL,
    status_final_id integer,
    database_id integer,
    period_id integer,
    status_progress_id integer,
    bound_id integer,
    data_policy_id integer,
    org_id integer,
    dats_title character varying(100) NOT NULL,
    dats_pub_date date NOT NULL,
    dats_version character varying(50),
    dats_process_level character varying(50),
    dats_other_cit character varying(250),
    dats_abstract text,
    dats_purpose text,
    dats_elevation_min bigint,
    dats_elevation_max bigint,
    dats_date_begin date,
    dats_date_end date,
    dats_use_constraints text,
    dats_access_constraints text,
    dats_reference text,
    dats_quality text,
    dats_image character varying(100),
    dats_date_end_not_planned boolean,
    dats_xml text,
    is_requested boolean,
    dats_doi character varying(250),
    dats_att_file character varying(100),
    dats_creator character varying(250),
    is_archived boolean,
    dats_funding character varying(50),
    dats_dmetmaj date,
    code character varying(50),
    dats_uuid character varying(50) NOT NULL
);


ALTER TABLE public.dataset OWNER TO wwwadm;

--
-- Name: dataset_dats_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE dataset_dats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dataset_dats_id_seq OWNER TO wwwadm;

--
-- Name: dataset_dats_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE dataset_dats_id_seq OWNED BY dataset.dats_id;


--
-- Name: dataset_type; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dataset_type (
    dats_type_id integer NOT NULL,
    dats_type_title character varying(100) NOT NULL,
    dats_type_desc character varying(250)
);


ALTER TABLE public.dataset_type OWNER TO wwwadm;

--
-- Name: dataset_type_dats_type_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE dataset_type_dats_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.dataset_type_dats_type_id_seq OWNER TO wwwadm;

--
-- Name: dataset_type_dats_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE dataset_type_dats_type_id_seq OWNED BY dataset_type.dats_type_id;


--
-- Name: dats_data; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_data (
    dats_id integer NOT NULL,
    ins_dats_id integer NOT NULL,
    date_begin timestamp without time zone,
    date_end timestamp without time zone
);


ALTER TABLE public.dats_data OWNER TO wwwadm;

--
-- Name: dats_data_format; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_data_format (
    dats_id integer NOT NULL,
    data_format_id integer NOT NULL
);


ALTER TABLE public.dats_data_format OWNER TO wwwadm;

--
-- Name: dats_link; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_link (
    dats_id integer,
    dats_dats_id integer,
    type_id integer
);


ALTER TABLE public.dats_link OWNER TO wwwadm;

--
-- Name: dats_loc; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_loc (
    dats_id integer NOT NULL,
    gcmd_loc_id integer NOT NULL
);


ALTER TABLE public.dats_loc OWNER TO wwwadm;

--
-- Name: dats_method; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_method (
    dats_id integer NOT NULL,
    method_id integer NOT NULL
);


ALTER TABLE public.dats_method OWNER TO wwwadm;

--
-- Name: dats_originators; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_originators (
    dats_id integer NOT NULL,
    pers_id integer NOT NULL,
    contact_type_id integer NOT NULL,
    no_email boolean
);


ALTER TABLE public.dats_originators OWNER TO wwwadm;

--
-- Name: dats_place; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_place (
    dats_id integer NOT NULL,
    place_id integer NOT NULL
);


ALTER TABLE public.dats_place OWNER TO wwwadm;

--
-- Name: dats_proj; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_proj (
    project_id integer NOT NULL,
    dats_id integer NOT NULL
);


ALTER TABLE public.dats_proj OWNER TO wwwadm;

--
-- Name: dats_required_data_format; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_required_data_format (
    dats_id integer NOT NULL,
    data_format_id integer NOT NULL
);


ALTER TABLE public.dats_required_data_format OWNER TO wwwadm;

--
-- Name: dats_role; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_role (
    dats_id integer NOT NULL,
    role_id integer NOT NULL
);


ALTER TABLE public.dats_role OWNER TO wwwadm;

--
-- Name: dats_sensor; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_sensor (
    sensor_id integer NOT NULL,
    dats_id integer NOT NULL,
    nb_sensor integer,
    sensor_resol_temp character varying(100),
    sensor_lat_resolution character varying(100),
    sensor_lon_resolution character varying(100),
    sensor_vert_resolution character varying(100),
    grid_original text,
    grid_process text
);


ALTER TABLE public.dats_sensor OWNER TO wwwadm;

--
-- Name: dats_type; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_type (
    dats_type_id integer NOT NULL,
    dats_id integer NOT NULL
);


ALTER TABLE public.dats_type OWNER TO wwwadm;

--
-- Name: dats_var; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE dats_var (
    var_id integer NOT NULL,
    dats_id integer NOT NULL,
    unit_id integer,
    vert_level_type_id integer,
    flag_param_calcule integer NOT NULL,
    min_value double precision,
    max_value double precision,
    methode_acq text,
    date_min date,
    date_max date,
    level_type character varying(100)
);


ALTER TABLE public.dats_var OWNER TO wwwadm;

--
-- Name: docs; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE docs (
    id integer NOT NULL,
    title character varying(100) NOT NULL
);


ALTER TABLE public.docs OWNER TO wwwadm;

--
-- Name: docs_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE docs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.docs_id_seq OWNER TO wwwadm;

--
-- Name: docs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE docs_id_seq OWNED BY docs.id;


--
-- Name: event; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE event (
    event_id integer NOT NULL,
    event_name character varying(250) NOT NULL,
    event_date_begin date,
    event_date_end date
);


ALTER TABLE public.event OWNER TO wwwadm;

--
-- Name: event_event_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE event_event_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.event_event_id_seq OWNER TO wwwadm;

--
-- Name: event_event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE event_event_id_seq OWNED BY event.event_id;


--
-- Name: extract_config; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE extract_config (
    dats_id integer NOT NULL,
    feature_type_id integer NOT NULL,
    dats_short_name character varying(50) NOT NULL,
    mobile_stations boolean,
    single_station boolean,
    var_id integer
);


ALTER TABLE public.extract_config OWNER TO wwwadm;

--
-- Name: feature_type; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE feature_type (
    feature_type_id integer NOT NULL,
    feature_type_name character varying(50) NOT NULL
);


ALTER TABLE public.feature_type OWNER TO wwwadm;

--
-- Name: feature_type_feature_type_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE feature_type_feature_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.feature_type_feature_type_id_seq OWNER TO wwwadm;

--
-- Name: feature_type_feature_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE feature_type_feature_type_id_seq OWNED BY feature_type.feature_type_id;


--
-- Name: file; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE file (
    file_id integer NOT NULL,
    integrateur_id integer NOT NULL,
    file_name character varying(50) NOT NULL,
    file_path character varying(250) NOT NULL,
    file_date_insertion timestamp without time zone NOT NULL
);


ALTER TABLE public.file OWNER TO wwwadm;

--
-- Name: file_file_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE file_file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.file_file_id_seq OWNER TO wwwadm;

--
-- Name: file_file_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE file_file_id_seq OWNED BY file.file_id;


--
-- Name: flag; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE flag (
    flag_id integer NOT NULL,
    flag_name character varying(50) NOT NULL
);


ALTER TABLE public.flag OWNER TO wwwadm;

--
-- Name: flag_flag_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE flag_flag_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.flag_flag_id_seq OWNER TO wwwadm;

--
-- Name: flag_flag_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE flag_flag_id_seq OWNED BY flag.flag_id;


--
-- Name: gcmd_science_keyword; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE gcmd_science_keyword (
    gcmd_id integer NOT NULL,
    gcm_gcmd_id integer,
    gcmd_name character varying(250) NOT NULL,
    gcmd_level smallint NOT NULL,
    thesaurus_id integer,
    uid character varying
);


ALTER TABLE public.gcmd_science_keyword OWNER TO wwwadm;

--
-- Name: gcmd; Type: VIEW; Schema: public; Owner: wwwadm
--

CREATE VIEW gcmd AS
    SELECT COALESCE(q4.gcmd_id, q3.gcmd_id, q2.gcmd_id, q1.gcmd_id) AS gcmd_id, q1.gcmd_id AS topic_id, q2.gcmd_id AS term_id, q3.gcmd_id AS var1_id, q4.gcmd_id AS var2_id, q1.gcmd_name AS topic, q2.gcmd_name AS term, q3.gcmd_name AS var1, q4.gcmd_name AS var2 FROM ((((SELECT gcmd_science_keyword.gcmd_id, gcmd_science_keyword.gcm_gcmd_id, gcmd_science_keyword.gcmd_name, gcmd_science_keyword.gcmd_level FROM gcmd_science_keyword WHERE (gcmd_science_keyword.gcmd_level = 1)) q1 JOIN (SELECT gcmd_science_keyword.gcmd_id, gcmd_science_keyword.gcm_gcmd_id, gcmd_science_keyword.gcmd_name, gcmd_science_keyword.gcmd_level FROM gcmd_science_keyword WHERE (gcmd_science_keyword.gcmd_level = 2)) q2 ON ((q1.gcmd_id = q2.gcm_gcmd_id))) JOIN (SELECT gcmd_science_keyword.gcmd_id, gcmd_science_keyword.gcm_gcmd_id, gcmd_science_keyword.gcmd_name, gcmd_science_keyword.gcmd_level FROM gcmd_science_keyword WHERE (gcmd_science_keyword.gcmd_level = 3)) q3 ON ((q2.gcmd_id = q3.gcm_gcmd_id))) LEFT JOIN (SELECT gcmd_science_keyword.gcmd_id, gcmd_science_keyword.gcm_gcmd_id, gcmd_science_keyword.gcmd_name, gcmd_science_keyword.gcmd_level FROM gcmd_science_keyword WHERE (gcmd_science_keyword.gcmd_level = 4)) q4 ON ((q3.gcmd_id = q4.gcm_gcmd_id))) ORDER BY q1.gcmd_name, q2.gcmd_name, q3.gcmd_name, q4.gcmd_name;


ALTER TABLE public.gcmd OWNER TO wwwadm;

--
-- Name: gcmd_instrument_keyword; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE gcmd_instrument_keyword (
    gcmd_sensor_id integer NOT NULL,
    gcmd_sensor_name character varying(100) NOT NULL,
    gcm_gcmd_id integer,
    gcmd_level smallint,
    thesaurus_id integer,
    uid character varying(250)
);


ALTER TABLE public.gcmd_instrument_keyword OWNER TO wwwadm;

--
-- Name: gcmd_instrument_keyword_gcmd_sensor_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE gcmd_instrument_keyword_gcmd_sensor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gcmd_instrument_keyword_gcmd_sensor_id_seq OWNER TO wwwadm;

--
-- Name: gcmd_instrument_keyword_gcmd_sensor_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE gcmd_instrument_keyword_gcmd_sensor_id_seq OWNED BY gcmd_instrument_keyword.gcmd_sensor_id;


--
-- Name: gcmd_location_keyword; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE gcmd_location_keyword (
    gcmd_loc_id integer NOT NULL,
    gcm_gcmd_id integer,
    gcmd_loc_name character varying(250),
    gcmd_level smallint,
    thesaurus_id integer,
    uid character varying(250)
);


ALTER TABLE public.gcmd_location_keyword OWNER TO wwwadm;

--
-- Name: gcmd_location_keyword_gcmd_loc_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE gcmd_location_keyword_gcmd_loc_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gcmd_location_keyword_gcmd_loc_id_seq OWNER TO wwwadm;

--
-- Name: gcmd_location_keyword_gcmd_loc_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE gcmd_location_keyword_gcmd_loc_id_seq OWNED BY gcmd_location_keyword.gcmd_loc_id;


--
-- Name: gcmd_plateform_keyword; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE gcmd_plateform_keyword (
    gcmd_plat_id integer NOT NULL,
    gcmd_plat_name character varying(100) NOT NULL,
    gcmd_level integer,
    gcm_gcmd_id integer,
    thesaurus_id integer,
    uid character varying(250)
);


ALTER TABLE public.gcmd_plateform_keyword OWNER TO wwwadm;

--
-- Name: gcmd_plateform_keyword_gcmd_plat_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE gcmd_plateform_keyword_gcmd_plat_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gcmd_plateform_keyword_gcmd_plat_id_seq OWNER TO wwwadm;

--
-- Name: gcmd_plateform_keyword_gcmd_plat_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE gcmd_plateform_keyword_gcmd_plat_id_seq OWNED BY gcmd_plateform_keyword.gcmd_plat_id;


--
-- Name: gcmd_plateform_keyword_insitu; Type: VIEW; Schema: public; Owner: wwwadm
--

CREATE VIEW gcmd_plateform_keyword_insitu AS
    SELECT gcmd_plateform_keyword.gcmd_plat_id, gcmd_plateform_keyword.gcmd_plat_name FROM gcmd_plateform_keyword WHERE (gcmd_plateform_keyword.gcmd_plat_id <> ALL (ARRAY[1, 8, 9, 11, 12]));


ALTER TABLE public.gcmd_plateform_keyword_insitu OWNER TO wwwadm;

--
-- Name: gcmd_science_keyword_gcmd_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE gcmd_science_keyword_gcmd_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gcmd_science_keyword_gcmd_id_seq OWNER TO wwwadm;

--
-- Name: gcmd_science_keyword_gcmd_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE gcmd_science_keyword_gcmd_id_seq OWNED BY gcmd_science_keyword.gcmd_id;


--
-- Name: inserted_dataset; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE inserted_dataset (
    ins_dats_id integer NOT NULL,
    ins_dats_name character varying(50) NOT NULL,
    date_insertion timestamp without time zone NOT NULL,
    date_last_update timestamp without time zone
);


ALTER TABLE public.inserted_dataset OWNER TO wwwadm;

--
-- Name: inserted_dataset_ins_dats_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE inserted_dataset_ins_dats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.inserted_dataset_ins_dats_id_seq OWNER TO wwwadm;

--
-- Name: inserted_dataset_ins_dats_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE inserted_dataset_ins_dats_id_seq OWNED BY inserted_dataset.ins_dats_id;


--
-- Name: journal; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE journal (
    journal_id integer NOT NULL,
    date timestamp without time zone NOT NULL,
    type_journal_id integer NOT NULL,
    contact character varying(250) NOT NULL,
    dats_id integer NOT NULL,
    comment text,
    publier boolean DEFAULT false NOT NULL
);


ALTER TABLE public.journal OWNER TO wwwadm;

--
-- Name: journal_journal_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE journal_journal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.journal_journal_id_seq OWNER TO wwwadm;

--
-- Name: journal_journal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE journal_journal_id_seq OWNED BY journal.journal_id;


--
-- Name: localisation; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE localisation (
    localisation_id integer NOT NULL,
    flag_loc_id integer,
    flag_alt_id integer,
    bound_id integer NOT NULL,
    localisation_alt integer NOT NULL,
    localisation_hs integer
);


ALTER TABLE public.localisation OWNER TO wwwadm;

--
-- Name: localisation_localisation_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE localisation_localisation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.localisation_localisation_id_seq OWNER TO wwwadm;

--
-- Name: localisation_localisation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE localisation_localisation_id_seq OWNED BY localisation.localisation_id;


--
-- Name: manufacturer; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE manufacturer (
    manufacturer_id integer NOT NULL,
    manufacturer_name character varying(250) NOT NULL,
    manufacturer_url character varying(250)
);


ALTER TABLE public.manufacturer OWNER TO wwwadm;

--
-- Name: manufacturer_manufacturer_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE manufacturer_manufacturer_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.manufacturer_manufacturer_id_seq OWNER TO wwwadm;

--
-- Name: manufacturer_manufacturer_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE manufacturer_manufacturer_id_seq OWNED BY manufacturer.manufacturer_id;


--
-- Name: mesure; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE mesure (
    mesure_id integer NOT NULL,
    sequence_id integer,
    mesure_date timestamp without time zone NOT NULL,
    localisation_id integer NOT NULL,
    place_id integer NOT NULL,
    ins_dats_id integer NOT NULL
);


ALTER TABLE public.mesure OWNER TO wwwadm;

--
-- Name: mesure_mesure_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE mesure_mesure_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mesure_mesure_id_seq OWNER TO wwwadm;

--
-- Name: mesure_mesure_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE mesure_mesure_id_seq OWNED BY mesure.mesure_id;


--
-- Name: method; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE method (
    method_id integer NOT NULL,
    method_name character varying(50),
    method_desc text
);


ALTER TABLE public.method OWNER TO wwwadm;

--
-- Name: method_method_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE method_method_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.method_method_id_seq OWNER TO wwwadm;

--
-- Name: method_method_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE method_method_id_seq OWNED BY method.method_id;


--
-- Name: obs_length; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE obs_length (
    obs_length_id integer NOT NULL,
    obs_length_name character varying(10),
    obs_length_seconds integer NOT NULL
);


ALTER TABLE public.obs_length OWNER TO wwwadm;

--
-- Name: obs_length_obs_length_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE obs_length_obs_length_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.obs_length_obs_length_id_seq OWNER TO wwwadm;

--
-- Name: obs_length_obs_length_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE obs_length_obs_length_id_seq OWNED BY obs_length.obs_length_id;


--
-- Name: organism; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE organism (
    org_id integer NOT NULL,
    org_sname character varying(50),
    org_fname character varying(250) NOT NULL,
    org_url character varying(250)
);


ALTER TABLE public.organism OWNER TO wwwadm;

--
-- Name: organism_org_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE organism_org_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.organism_org_id_seq OWNER TO wwwadm;

--
-- Name: organism_org_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE organism_org_id_seq OWNED BY organism.org_id;


--
-- Name: param; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE param (
    var_id integer NOT NULL,
    unit_id integer NOT NULL,
    param_code character varying(10),
    standard_name character varying(100)
);


ALTER TABLE public.param OWNER TO wwwadm;

--
-- Name: period; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE period (
    period_id integer NOT NULL,
    period_name character varying(50) NOT NULL,
    period_begin date,
    period_end date
);


ALTER TABLE public.period OWNER TO wwwadm;

--
-- Name: period_period_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE period_period_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.period_period_id_seq OWNER TO wwwadm;

--
-- Name: period_period_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE period_period_id_seq OWNED BY period.period_id;


--
-- Name: period_project; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE period_project (
    project_id integer NOT NULL,
    period_id integer NOT NULL
);


ALTER TABLE public.period_project OWNER TO wwwadm;

--
-- Name: personne; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE personne (
    pers_id integer NOT NULL,
    org_id integer,
    pers_name character varying(250) NOT NULL,
    pers_email_1 character varying(250) NOT NULL,
    pers_email_2 character varying(250)
);


ALTER TABLE public.personne OWNER TO wwwadm;

--
-- Name: personne_pers_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE personne_pers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.personne_pers_id_seq OWNER TO wwwadm;

--
-- Name: personne_pers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE personne_pers_id_seq OWNED BY personne.pers_id;


--
-- Name: place; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE place (
    place_id integer NOT NULL,
    pla_place_id integer,
    bound_id integer,
    gcmd_plat_id integer,
    place_name character varying(100) NOT NULL,
    place_elevation_min bigint,
    place_elevation_max bigint,
    place_level smallint,
    wmo_code character varying(10),
    gcmd_loc_id integer
);


ALTER TABLE public.place OWNER TO wwwadm;

--
-- Name: place_insitu; Type: VIEW; Schema: public; Owner: wwwadm
--

CREATE VIEW place_insitu AS
    SELECT place.place_id, place.pla_place_id, place.bound_id, place.gcmd_plat_id, place.place_name, place.place_elevation_min, place.place_elevation_max FROM place WHERE (place.gcmd_plat_id <> ALL (ARRAY[1, 8, 9, 11, 12]));


ALTER TABLE public.place_insitu OWNER TO wwwadm;

--
-- Name: place_place_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE place_place_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.place_place_id_seq OWNER TO wwwadm;

--
-- Name: place_place_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE place_place_id_seq OWNED BY place.place_id;


--
-- Name: place_var; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE place_var (
    var_id integer NOT NULL,
    place_id integer NOT NULL
);


ALTER TABLE public.place_var OWNER TO wwwadm;

--
-- Name: proj_inst_keyword; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE proj_inst_keyword (
    project_id integer NOT NULL,
    gcmd_sensor_id integer NOT NULL
);


ALTER TABLE public.proj_inst_keyword OWNER TO wwwadm;

--
-- Name: proj_loc_keyword; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE proj_loc_keyword (
    gcmd_loc_id integer NOT NULL,
    project_id integer NOT NULL
);


ALTER TABLE public.proj_loc_keyword OWNER TO wwwadm;

--
-- Name: proj_plat_keyword; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE proj_plat_keyword (
    project_id integer NOT NULL,
    gcmd_plat_id integer NOT NULL
);


ALTER TABLE public.proj_plat_keyword OWNER TO wwwadm;

--
-- Name: proj_scie_keyword; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE proj_scie_keyword (
    project_id integer NOT NULL,
    gcmd_id integer NOT NULL
);


ALTER TABLE public.proj_scie_keyword OWNER TO wwwadm;

--
-- Name: project; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE project (
    project_id integer NOT NULL,
    pro_project_id integer,
    project_name character varying(100) NOT NULL,
    project_url character varying(255)
);


ALTER TABLE public.project OWNER TO wwwadm;

--
-- Name: project_project_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE project_project_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.project_project_id_seq OWNER TO wwwadm;

--
-- Name: project_project_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE project_project_id_seq OWNED BY project.project_id;


--
-- Name: requete; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE requete (
    requete_id integer NOT NULL,
    requete_email character varying(50) NOT NULL,
    requete_xml text NOT NULL,
    requete_date_debut timestamp without time zone NOT NULL,
    requete_date_active timestamp without time zone NOT NULL,
    requete_date_fin timestamp without time zone,
    requete_etat smallint NOT NULL,
    requete_kill boolean NOT NULL,
    requete_nb_val integer NOT NULL
);


ALTER TABLE public.requete OWNER TO wwwadm;

--
-- Name: requete_requete_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE requete_requete_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.requete_requete_id_seq OWNER TO wwwadm;

--
-- Name: requete_requete_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE requete_requete_id_seq OWNED BY requete.requete_id;


--
-- Name: role; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE role (
    role_id integer NOT NULL,
    role_name character varying(50) NOT NULL
);


ALTER TABLE public.role OWNER TO wwwadm;

--
-- Name: role_role_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE role_role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.role_role_id_seq OWNER TO wwwadm;

--
-- Name: role_role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE role_role_id_seq OWNED BY role.role_id;


--
-- Name: sensor; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE sensor (
    sensor_id integer NOT NULL,
    manufacturer_id integer,
    gcmd_sensor_id integer,
    bound_id integer,
    sensor_model character varying(100),
    sensor_calibration character varying(250),
    sensor_date_begin date,
    sensor_date_end date,
    sensor_url text,
    sensor_elevation bigint
);


ALTER TABLE public.sensor OWNER TO wwwadm;

--
-- Name: sensor_place; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE sensor_place (
    sensor_id integer NOT NULL,
    place_id integer NOT NULL,
    environment text
);


ALTER TABLE public.sensor_place OWNER TO wwwadm;

--
-- Name: sensor_sensor_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE sensor_sensor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sensor_sensor_id_seq OWNER TO wwwadm;

--
-- Name: sensor_sensor_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE sensor_sensor_id_seq OWNED BY sensor.sensor_id;


--
-- Name: sensor_var; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE sensor_var (
    sensor_id integer NOT NULL,
    var_id integer NOT NULL,
    sensor_precision character varying(500) NOT NULL,
    methode_acq text
);


ALTER TABLE public.sensor_var OWNER TO wwwadm;

--
-- Name: sensorid; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE sensorid (
    sensor_id integer
);


ALTER TABLE public.sensorid OWNER TO wwwadm;

--
-- Name: sequence; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE sequence (
    sequence_id integer NOT NULL,
    sequence_date_begin timestamp without time zone NOT NULL,
    sequence_date_end timestamp without time zone NOT NULL,
    sequence_loc_begin_id integer NOT NULL,
    sequence_loc_end_id integer NOT NULL,
    ins_dats_id integer NOT NULL,
    sequence_name character varying(50)
);


ALTER TABLE public.sequence OWNER TO wwwadm;

--
-- Name: sequence_sequence_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE sequence_sequence_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.sequence_sequence_id_seq OWNER TO wwwadm;

--
-- Name: sequence_sequence_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE sequence_sequence_id_seq OWNED BY sequence.sequence_id;


--
-- Name: status_final; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE status_final (
    status_final_id integer NOT NULL,
    status_final_name character varying(50) NOT NULL
);


ALTER TABLE public.status_final OWNER TO wwwadm;

--
-- Name: status_final_status_final_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE status_final_status_final_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.status_final_status_final_id_seq OWNER TO wwwadm;

--
-- Name: status_final_status_final_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE status_final_status_final_id_seq OWNED BY status_final.status_final_id;


--
-- Name: status_progress; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE status_progress (
    status_progress_id integer NOT NULL,
    status_progress_name character varying(50) NOT NULL
);


ALTER TABLE public.status_progress OWNER TO wwwadm;

--
-- Name: status_progress_status_progress_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE status_progress_status_progress_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.status_progress_status_progress_id_seq OWNER TO wwwadm;

--
-- Name: status_progress_status_progress_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE status_progress_status_progress_id_seq OWNED BY status_progress.status_progress_id;


--
-- Name: suggest; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE suggest (
    id integer NOT NULL,
    keyword character varying(255) NOT NULL,
    trigrams character varying(255) NOT NULL,
    freq integer NOT NULL
);


ALTER TABLE public.suggest OWNER TO wwwadm;

--
-- Name: suggest_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE suggest_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.suggest_id_seq OWNER TO wwwadm;

--
-- Name: suggest_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE suggest_id_seq OWNED BY suggest.id;


--
-- Name: thesaurus; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE thesaurus (
    thesaurus_id integer NOT NULL,
    name character varying(250),
    url text
);


ALTER TABLE public.thesaurus OWNER TO wwwadm;

--
-- Name: thesaurus_thesaurus_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE thesaurus_thesaurus_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.thesaurus_thesaurus_id_seq OWNER TO wwwadm;

--
-- Name: thesaurus_thesaurus_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE thesaurus_thesaurus_id_seq OWNED BY thesaurus.thesaurus_id;


--
-- Name: type_journal; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE type_journal (
    type_journal_id integer NOT NULL,
    type_journal_name character varying(50) NOT NULL
);


ALTER TABLE public.type_journal OWNER TO wwwadm;

--
-- Name: type_journal_type_journal_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE type_journal_type_journal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.type_journal_type_journal_id_seq OWNER TO wwwadm;

--
-- Name: type_journal_type_journal_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE type_journal_type_journal_id_seq OWNED BY type_journal.type_journal_id;


--
-- Name: type_link; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE type_link (
    type_id integer NOT NULL,
    source character varying(250)
);


ALTER TABLE public.type_link OWNER TO wwwadm;

--
-- Name: unit; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE unit (
    unit_id integer NOT NULL,
    unit_code character varying(20),
    unit_name character varying(50) NOT NULL
);


ALTER TABLE public.unit OWNER TO wwwadm;

--
-- Name: unit_unit_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE unit_unit_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unit_unit_id_seq OWNER TO wwwadm;

--
-- Name: unit_unit_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE unit_unit_id_seq OWNED BY unit.unit_id;


--
-- Name: url; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE url (
    url_id integer NOT NULL,
    url text NOT NULL,
    dats_id integer NOT NULL,
    url_type character varying(10)
);


ALTER TABLE public.url OWNER TO wwwadm;

--
-- Name: url_event; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE url_event (
    url_event_id integer NOT NULL,
    event_id integer NOT NULL,
    url_event text NOT NULL,
    url_descript text NOT NULL
);


ALTER TABLE public.url_event OWNER TO wwwadm;

--
-- Name: url_event_role; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE url_event_role (
    url_event_id integer NOT NULL,
    role_id integer NOT NULL
);


ALTER TABLE public.url_event_role OWNER TO wwwadm;

--
-- Name: url_event_url_event_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE url_event_url_event_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.url_event_url_event_id_seq OWNER TO wwwadm;

--
-- Name: url_event_url_event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE url_event_url_event_id_seq OWNED BY url_event.url_event_id;


--
-- Name: url_url_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE url_url_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.url_url_id_seq OWNER TO wwwadm;

--
-- Name: url_url_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE url_url_id_seq OWNED BY url.url_id;


--
-- Name: valeur; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE valeur (
    valeur_id integer NOT NULL,
    mesure_id integer NOT NULL,
    var_id integer NOT NULL,
    sensor_id integer NOT NULL,
    valeur double precision NOT NULL,
    valeur_delta double precision,
    flag_qual_id integer,
    flag_calc_id integer,
    file_id integer NOT NULL,
    obs_length_id integer
);


ALTER TABLE public.valeur OWNER TO wwwadm;

--
-- Name: valeur_valeur_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE valeur_valeur_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.valeur_valeur_id_seq OWNER TO wwwadm;

--
-- Name: valeur_valeur_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE valeur_valeur_id_seq OWNED BY valeur.valeur_id;


--
-- Name: variable; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE variable (
    var_id integer NOT NULL,
    gcmd_id integer,
    var_name character varying(100),
    code character varying(50)
);


ALTER TABLE public.variable OWNER TO wwwadm;

--
-- Name: variable_var_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE variable_var_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.variable_var_id_seq OWNER TO wwwadm;

--
-- Name: variable_var_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE variable_var_id_seq OWNED BY variable.var_id;


--
-- Name: vertical_level_type; Type: TABLE; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE TABLE vertical_level_type (
    vert_level_type_id integer NOT NULL,
    vert_level_type_name character varying(100) NOT NULL
);


ALTER TABLE public.vertical_level_type OWNER TO wwwadm;

--
-- Name: vertical_level_type_vert_level_type_id_seq; Type: SEQUENCE; Schema: public; Owner: wwwadm
--

CREATE SEQUENCE vertical_level_type_vert_level_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.vertical_level_type_vert_level_type_id_seq OWNER TO wwwadm;

--
-- Name: vertical_level_type_vert_level_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wwwadm
--

ALTER SEQUENCE vertical_level_type_vert_level_type_id_seq OWNED BY vertical_level_type.vert_level_type_id;


--
-- Name: bound_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY boundings ALTER COLUMN bound_id SET DEFAULT nextval('boundings_bound_id_seq'::regclass);


--
-- Name: contact_type_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY contact_type ALTER COLUMN contact_type_id SET DEFAULT nextval('contact_type_contact_type_id_seq'::regclass);


--
-- Name: country_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY country ALTER COLUMN country_id SET DEFAULT nextval('country_country_id_seq'::regclass);


--
-- Name: data_format_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY data_format ALTER COLUMN data_format_id SET DEFAULT nextval('data_format_data_format_id_seq'::regclass);


--
-- Name: data_policy_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY data_policy ALTER COLUMN data_policy_id SET DEFAULT nextval('data_policy_data_policy_id_seq'::regclass);


--
-- Name: database_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY database ALTER COLUMN database_id SET DEFAULT nextval('database_database_id_seq'::regclass);


--
-- Name: dats_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dataset ALTER COLUMN dats_id SET DEFAULT nextval('dataset_dats_id_seq'::regclass);


--
-- Name: dats_type_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dataset_type ALTER COLUMN dats_type_id SET DEFAULT nextval('dataset_type_dats_type_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY docs ALTER COLUMN id SET DEFAULT nextval('docs_id_seq'::regclass);


--
-- Name: event_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY event ALTER COLUMN event_id SET DEFAULT nextval('event_event_id_seq'::regclass);


--
-- Name: feature_type_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY feature_type ALTER COLUMN feature_type_id SET DEFAULT nextval('feature_type_feature_type_id_seq'::regclass);


--
-- Name: file_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY file ALTER COLUMN file_id SET DEFAULT nextval('file_file_id_seq'::regclass);


--
-- Name: flag_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY flag ALTER COLUMN flag_id SET DEFAULT nextval('flag_flag_id_seq'::regclass);


--
-- Name: gcmd_sensor_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY gcmd_instrument_keyword ALTER COLUMN gcmd_sensor_id SET DEFAULT nextval('gcmd_instrument_keyword_gcmd_sensor_id_seq'::regclass);


--
-- Name: gcmd_loc_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY gcmd_location_keyword ALTER COLUMN gcmd_loc_id SET DEFAULT nextval('gcmd_location_keyword_gcmd_loc_id_seq'::regclass);


--
-- Name: gcmd_plat_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY gcmd_plateform_keyword ALTER COLUMN gcmd_plat_id SET DEFAULT nextval('gcmd_plateform_keyword_gcmd_plat_id_seq'::regclass);


--
-- Name: gcmd_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY gcmd_science_keyword ALTER COLUMN gcmd_id SET DEFAULT nextval('gcmd_science_keyword_gcmd_id_seq'::regclass);


--
-- Name: ins_dats_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY inserted_dataset ALTER COLUMN ins_dats_id SET DEFAULT nextval('inserted_dataset_ins_dats_id_seq'::regclass);


--
-- Name: journal_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY journal ALTER COLUMN journal_id SET DEFAULT nextval('journal_journal_id_seq'::regclass);


--
-- Name: localisation_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY localisation ALTER COLUMN localisation_id SET DEFAULT nextval('localisation_localisation_id_seq'::regclass);


--
-- Name: manufacturer_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY manufacturer ALTER COLUMN manufacturer_id SET DEFAULT nextval('manufacturer_manufacturer_id_seq'::regclass);


--
-- Name: mesure_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY mesure ALTER COLUMN mesure_id SET DEFAULT nextval('mesure_mesure_id_seq'::regclass);


--
-- Name: method_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY method ALTER COLUMN method_id SET DEFAULT nextval('method_method_id_seq'::regclass);


--
-- Name: obs_length_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY obs_length ALTER COLUMN obs_length_id SET DEFAULT nextval('obs_length_obs_length_id_seq'::regclass);


--
-- Name: org_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY organism ALTER COLUMN org_id SET DEFAULT nextval('organism_org_id_seq'::regclass);


--
-- Name: period_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY period ALTER COLUMN period_id SET DEFAULT nextval('period_period_id_seq'::regclass);


--
-- Name: pers_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY personne ALTER COLUMN pers_id SET DEFAULT nextval('personne_pers_id_seq'::regclass);


--
-- Name: place_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY place ALTER COLUMN place_id SET DEFAULT nextval('place_place_id_seq'::regclass);


--
-- Name: project_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY project ALTER COLUMN project_id SET DEFAULT nextval('project_project_id_seq'::regclass);


--
-- Name: requete_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY requete ALTER COLUMN requete_id SET DEFAULT nextval('requete_requete_id_seq'::regclass);


--
-- Name: role_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY role ALTER COLUMN role_id SET DEFAULT nextval('role_role_id_seq'::regclass);


--
-- Name: sensor_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY sensor ALTER COLUMN sensor_id SET DEFAULT nextval('sensor_sensor_id_seq'::regclass);


--
-- Name: sequence_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY sequence ALTER COLUMN sequence_id SET DEFAULT nextval('sequence_sequence_id_seq'::regclass);


--
-- Name: status_final_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY status_final ALTER COLUMN status_final_id SET DEFAULT nextval('status_final_status_final_id_seq'::regclass);


--
-- Name: status_progress_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY status_progress ALTER COLUMN status_progress_id SET DEFAULT nextval('status_progress_status_progress_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY suggest ALTER COLUMN id SET DEFAULT nextval('suggest_id_seq'::regclass);


--
-- Name: thesaurus_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY thesaurus ALTER COLUMN thesaurus_id SET DEFAULT nextval('thesaurus_thesaurus_id_seq'::regclass);


--
-- Name: type_journal_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY type_journal ALTER COLUMN type_journal_id SET DEFAULT nextval('type_journal_type_journal_id_seq'::regclass);


--
-- Name: unit_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY unit ALTER COLUMN unit_id SET DEFAULT nextval('unit_unit_id_seq'::regclass);


--
-- Name: url_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY url ALTER COLUMN url_id SET DEFAULT nextval('url_url_id_seq'::regclass);


--
-- Name: url_event_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY url_event ALTER COLUMN url_event_id SET DEFAULT nextval('url_event_url_event_id_seq'::regclass);


--
-- Name: valeur_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY valeur ALTER COLUMN valeur_id SET DEFAULT nextval('valeur_valeur_id_seq'::regclass);


--
-- Name: var_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY variable ALTER COLUMN var_id SET DEFAULT nextval('variable_var_id_seq'::regclass);


--
-- Name: vert_level_type_id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY vertical_level_type ALTER COLUMN vert_level_type_id SET DEFAULT nextval('vertical_level_type_vert_level_type_id_seq'::regclass);


--
-- Name: ak_ak_country_country; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY country
    ADD CONSTRAINT ak_ak_country_country UNIQUE (country_name);


--
-- Name: ak_ak_file_file; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY file
    ADD CONSTRAINT ak_ak_file_file UNIQUE (file_path);


--
-- Name: ak_ak_flag_flag; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY flag
    ADD CONSTRAINT ak_ak_flag_flag UNIQUE (flag_name);


--
-- Name: ak_ak_ins_dats_inserted; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY inserted_dataset
    ADD CONSTRAINT ak_ak_ins_dats_inserted UNIQUE (ins_dats_name);


--
-- Name: ak_ak_local_localisa; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY localisation
    ADD CONSTRAINT ak_ak_local_localisa UNIQUE (localisation_hs, localisation_alt, bound_id);


--
-- Name: ak_ak_obs_length_name_obs_leng; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY obs_length
    ADD CONSTRAINT ak_ak_obs_length_name_obs_leng UNIQUE (obs_length_name);


--
-- Name: ak_ak_obs_length_obs_leng; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY obs_length
    ADD CONSTRAINT ak_ak_obs_length_obs_leng UNIQUE (obs_length_seconds);


--
-- Name: ak_ak_param_param; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY param
    ADD CONSTRAINT ak_ak_param_param UNIQUE (param_code);


--
-- Name: ak_ak_valeur_valeur; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY valeur
    ADD CONSTRAINT ak_ak_valeur_valeur UNIQUE (mesure_id, var_id);


--
-- Name: ak_bound_coords_bounding; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY boundings
    ADD CONSTRAINT ak_bound_coords_bounding UNIQUE (west_bounding_coord, east_bounding_coord, north_bounding_coord, south_bounding_coord);


--
-- Name: ak_data_format_name_data_for; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY data_format
    ADD CONSTRAINT ak_data_format_name_data_for UNIQUE (data_format_name);


--
-- Name: ak_data_policy_name_data_pol; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY data_policy
    ADD CONSTRAINT ak_data_policy_name_data_pol UNIQUE (data_policy_name);


--
-- Name: ak_database_name_database; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY database
    ADD CONSTRAINT ak_database_name_database UNIQUE (database_name);


--
-- Name: ak_dats_title_pub_dat_dataset; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dataset
    ADD CONSTRAINT ak_dats_title_pub_dat_dataset UNIQUE (dats_title, dats_pub_date);


--
-- Name: ak_dats_type_name_dataset_; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dataset_type
    ADD CONSTRAINT ak_dats_type_name_dataset_ UNIQUE (dats_type_title);


--
-- Name: ak_event_name; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY event
    ADD CONSTRAINT ak_event_name UNIQUE (event_name);


--
-- Name: ak_gcmd_plat_name_gcmd_pla; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY gcmd_plateform_keyword
    ADD CONSTRAINT ak_gcmd_plat_name_gcmd_pla UNIQUE (gcmd_plat_name);


--
-- Name: ak_gcmd_sensor_name_gcmd_ins; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY gcmd_instrument_keyword
    ADD CONSTRAINT ak_gcmd_sensor_name_gcmd_ins UNIQUE (gcmd_sensor_name);


--
-- Name: ak_manufacturer_name_manufact; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY manufacturer
    ADD CONSTRAINT ak_manufacturer_name_manufact UNIQUE (manufacturer_name);


--
-- Name: ak_period_name_period; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY period
    ADD CONSTRAINT ak_period_name_period UNIQUE (period_name);


--
-- Name: ak_project_name_project; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY project
    ADD CONSTRAINT ak_project_name_project UNIQUE (project_name);


--
-- Name: ak_role_name_role; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY role
    ADD CONSTRAINT ak_role_name_role UNIQUE (role_name);


--
-- Name: ak_status_final_name_status_f; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY status_final
    ADD CONSTRAINT ak_status_final_name_status_f UNIQUE (status_final_name);


--
-- Name: ak_status_progress_na_status_p; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY status_progress
    ADD CONSTRAINT ak_status_progress_na_status_p UNIQUE (status_progress_name);


--
-- Name: ak_type_journal_name; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY type_journal
    ADD CONSTRAINT ak_type_journal_name UNIQUE (type_journal_name);


--
-- Name: ak_unit_name_unit; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY unit
    ADD CONSTRAINT ak_unit_name_unit UNIQUE (unit_name);


--
-- Name: ak_vert_level_type_name; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY vertical_level_type
    ADD CONSTRAINT ak_vert_level_type_name UNIQUE (vert_level_type_name);


--
-- Name: docs_pkey; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY docs
    ADD CONSTRAINT docs_pkey PRIMARY KEY (id);


--
-- Name: gcmd_location_keyword_pkey; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY gcmd_location_keyword
    ADD CONSTRAINT gcmd_location_keyword_pkey PRIMARY KEY (gcmd_loc_id);


--
-- Name: pk_boundings; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY boundings
    ADD CONSTRAINT pk_boundings PRIMARY KEY (bound_id);


--
-- Name: pk_contact_type; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY contact_type
    ADD CONSTRAINT pk_contact_type PRIMARY KEY (contact_type_id);


--
-- Name: pk_country; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY country
    ADD CONSTRAINT pk_country PRIMARY KEY (country_id);


--
-- Name: pk_country_place; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY country_place
    ADD CONSTRAINT pk_country_place PRIMARY KEY (country_id, place_id);


--
-- Name: pk_data_availability; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY data_availability
    ADD CONSTRAINT pk_data_availability PRIMARY KEY (ins_dats_id, var_id, place_id, date_begin, date_end);


--
-- Name: pk_data_format; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY data_format
    ADD CONSTRAINT pk_data_format PRIMARY KEY (data_format_id);


--
-- Name: pk_data_policy; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY data_policy
    ADD CONSTRAINT pk_data_policy PRIMARY KEY (data_policy_id);


--
-- Name: pk_database; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY database
    ADD CONSTRAINT pk_database PRIMARY KEY (database_id);


--
-- Name: pk_dataset; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dataset
    ADD CONSTRAINT pk_dataset PRIMARY KEY (dats_id);


--
-- Name: pk_dataset_type; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dataset_type
    ADD CONSTRAINT pk_dataset_type PRIMARY KEY (dats_type_id);


--
-- Name: pk_dats_data; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dats_data
    ADD CONSTRAINT pk_dats_data PRIMARY KEY (dats_id, ins_dats_id);


--
-- Name: pk_dats_data_format; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dats_data_format
    ADD CONSTRAINT pk_dats_data_format PRIMARY KEY (dats_id, data_format_id);


--
-- Name: pk_dats_loc; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dats_loc
    ADD CONSTRAINT pk_dats_loc PRIMARY KEY (dats_id, gcmd_loc_id);


--
-- Name: pk_dats_method; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dats_method
    ADD CONSTRAINT pk_dats_method PRIMARY KEY (dats_id, method_id);


--
-- Name: pk_dats_originators; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dats_originators
    ADD CONSTRAINT pk_dats_originators PRIMARY KEY (dats_id, pers_id);


--
-- Name: pk_dats_place; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dats_place
    ADD CONSTRAINT pk_dats_place PRIMARY KEY (dats_id, place_id);


--
-- Name: pk_dats_proj; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dats_proj
    ADD CONSTRAINT pk_dats_proj PRIMARY KEY (project_id, dats_id);


--
-- Name: pk_dats_req_data_format; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dats_required_data_format
    ADD CONSTRAINT pk_dats_req_data_format PRIMARY KEY (dats_id, data_format_id);


--
-- Name: pk_dats_role; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dats_role
    ADD CONSTRAINT pk_dats_role PRIMARY KEY (dats_id, role_id);


--
-- Name: pk_dats_sensor; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dats_sensor
    ADD CONSTRAINT pk_dats_sensor PRIMARY KEY (sensor_id, dats_id);


--
-- Name: pk_dats_type; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY dats_type
    ADD CONSTRAINT pk_dats_type PRIMARY KEY (dats_type_id, dats_id);


--
-- Name: pk_event; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY event
    ADD CONSTRAINT pk_event PRIMARY KEY (event_id);


--
-- Name: pk_feature_type; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY feature_type
    ADD CONSTRAINT pk_feature_type PRIMARY KEY (feature_type_id);


--
-- Name: pk_file; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY file
    ADD CONSTRAINT pk_file PRIMARY KEY (file_id);


--
-- Name: pk_flag; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY flag
    ADD CONSTRAINT pk_flag PRIMARY KEY (flag_id);


--
-- Name: pk_gcmd_instrument_keyword; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY gcmd_instrument_keyword
    ADD CONSTRAINT pk_gcmd_instrument_keyword PRIMARY KEY (gcmd_sensor_id);


--
-- Name: pk_gcmd_plateform_keyword; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY gcmd_plateform_keyword
    ADD CONSTRAINT pk_gcmd_plateform_keyword PRIMARY KEY (gcmd_plat_id);


--
-- Name: pk_gcmd_science_keyword; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY gcmd_science_keyword
    ADD CONSTRAINT pk_gcmd_science_keyword PRIMARY KEY (gcmd_id);


--
-- Name: pk_inserted_dataset; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY inserted_dataset
    ADD CONSTRAINT pk_inserted_dataset PRIMARY KEY (ins_dats_id);


--
-- Name: pk_journal; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY journal
    ADD CONSTRAINT pk_journal PRIMARY KEY (journal_id);


--
-- Name: pk_localisation; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY localisation
    ADD CONSTRAINT pk_localisation PRIMARY KEY (localisation_id);


--
-- Name: pk_manufacturer; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY manufacturer
    ADD CONSTRAINT pk_manufacturer PRIMARY KEY (manufacturer_id);


--
-- Name: pk_mesure; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY mesure
    ADD CONSTRAINT pk_mesure PRIMARY KEY (mesure_id);


--
-- Name: pk_method; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY method
    ADD CONSTRAINT pk_method PRIMARY KEY (method_id);


--
-- Name: pk_obs_length; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY obs_length
    ADD CONSTRAINT pk_obs_length PRIMARY KEY (obs_length_id);


--
-- Name: pk_organism; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY organism
    ADD CONSTRAINT pk_organism PRIMARY KEY (org_id);


--
-- Name: pk_param; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY param
    ADD CONSTRAINT pk_param PRIMARY KEY (var_id);


--
-- Name: pk_period; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY period
    ADD CONSTRAINT pk_period PRIMARY KEY (period_id);


--
-- Name: pk_period_project; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY period_project
    ADD CONSTRAINT pk_period_project PRIMARY KEY (project_id, period_id);


--
-- Name: pk_personne; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY personne
    ADD CONSTRAINT pk_personne PRIMARY KEY (pers_id);


--
-- Name: pk_place; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY place
    ADD CONSTRAINT pk_place PRIMARY KEY (place_id);


--
-- Name: pk_place_var; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY place_var
    ADD CONSTRAINT pk_place_var PRIMARY KEY (var_id, place_id);


--
-- Name: pk_pro_loc_keyword; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY proj_loc_keyword
    ADD CONSTRAINT pk_pro_loc_keyword PRIMARY KEY (gcmd_loc_id, project_id);


--
-- Name: pk_proj_inst_keyword; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY proj_inst_keyword
    ADD CONSTRAINT pk_proj_inst_keyword PRIMARY KEY (project_id, gcmd_sensor_id);


--
-- Name: pk_proj_plat_keyword; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY proj_plat_keyword
    ADD CONSTRAINT pk_proj_plat_keyword PRIMARY KEY (project_id, gcmd_plat_id);


--
-- Name: pk_proj_scie_keyword; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY proj_scie_keyword
    ADD CONSTRAINT pk_proj_scie_keyword PRIMARY KEY (project_id, gcmd_id);


--
-- Name: pk_project; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY project
    ADD CONSTRAINT pk_project PRIMARY KEY (project_id);


--
-- Name: pk_requete; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY requete
    ADD CONSTRAINT pk_requete PRIMARY KEY (requete_id);


--
-- Name: pk_role; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY role
    ADD CONSTRAINT pk_role PRIMARY KEY (role_id);


--
-- Name: pk_sensor; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY sensor
    ADD CONSTRAINT pk_sensor PRIMARY KEY (sensor_id);


--
-- Name: pk_sensor_place; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY sensor_place
    ADD CONSTRAINT pk_sensor_place PRIMARY KEY (sensor_id, place_id);


--
-- Name: pk_sensor_var; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY sensor_var
    ADD CONSTRAINT pk_sensor_var PRIMARY KEY (sensor_id, var_id, sensor_precision);


--
-- Name: pk_sequence; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY sequence
    ADD CONSTRAINT pk_sequence PRIMARY KEY (sequence_id);


--
-- Name: pk_status_final; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY status_final
    ADD CONSTRAINT pk_status_final PRIMARY KEY (status_final_id);


--
-- Name: pk_status_progress; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY status_progress
    ADD CONSTRAINT pk_status_progress PRIMARY KEY (status_progress_id);


--
-- Name: pk_type_journal; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY type_journal
    ADD CONSTRAINT pk_type_journal PRIMARY KEY (type_journal_id);


--
-- Name: pk_type_link; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY type_link
    ADD CONSTRAINT pk_type_link PRIMARY KEY (type_id);


--
-- Name: pk_unit; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY unit
    ADD CONSTRAINT pk_unit PRIMARY KEY (unit_id);


--
-- Name: pk_url; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY url
    ADD CONSTRAINT pk_url PRIMARY KEY (url_id);


--
-- Name: pk_url_event; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY url_event
    ADD CONSTRAINT pk_url_event PRIMARY KEY (url_event_id);


--
-- Name: pk_url_event_role; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY url_event_role
    ADD CONSTRAINT pk_url_event_role PRIMARY KEY (url_event_id, role_id);


--
-- Name: pk_valeur; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY valeur
    ADD CONSTRAINT pk_valeur PRIMARY KEY (valeur_id);


--
-- Name: pk_variable; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY variable
    ADD CONSTRAINT pk_variable PRIMARY KEY (var_id);


--
-- Name: pk_vertical_level_type; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY vertical_level_type
    ADD CONSTRAINT pk_vertical_level_type PRIMARY KEY (vert_level_type_id);


--
-- Name: suggest_keyword_key; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY suggest
    ADD CONSTRAINT suggest_keyword_key UNIQUE (keyword);


--
-- Name: suggest_pkey; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY suggest
    ADD CONSTRAINT suggest_pkey PRIMARY KEY (id);


--
-- Name: index_gcmd_parent; Type: INDEX; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE INDEX index_gcmd_parent ON gcmd_science_keyword USING btree (gcm_gcmd_id);


--
-- Name: index_mesure_dats_place; Type: INDEX; Schema: public; Owner: wwwadm; Tablespace: 
--

CREATE INDEX index_mesure_dats_place ON mesure USING btree (ins_dats_id, place_id);


--
-- Name: dats_method_method_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_method
    ADD CONSTRAINT dats_method_method_id_fkey FOREIGN KEY (method_id) REFERENCES method(method_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_country__reference_country; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY country_place
    ADD CONSTRAINT fk_country__reference_country FOREIGN KEY (country_id) REFERENCES country(country_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_country__reference_place; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY country_place
    ADD CONSTRAINT fk_country__reference_place FOREIGN KEY (place_id) REFERENCES place(place_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_data_ava_reference_inserted; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY data_availability
    ADD CONSTRAINT fk_data_ava_reference_inserted FOREIGN KEY (ins_dats_id) REFERENCES inserted_dataset(ins_dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_data_ava_reference_place; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY data_availability
    ADD CONSTRAINT fk_data_ava_reference_place FOREIGN KEY (place_id) REFERENCES place(place_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_data_ava_reference_variable; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY data_availability
    ADD CONSTRAINT fk_data_ava_reference_variable FOREIGN KEY (var_id) REFERENCES variable(var_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dataset_dats_boun_bounding; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dataset
    ADD CONSTRAINT fk_dataset_dats_boun_bounding FOREIGN KEY (bound_id) REFERENCES boundings(bound_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dataset_dats_data_data_pol; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dataset
    ADD CONSTRAINT fk_dataset_dats_data_data_pol FOREIGN KEY (data_policy_id) REFERENCES data_policy(data_policy_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dataset_dats_data_database; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dataset
    ADD CONSTRAINT fk_dataset_dats_data_database FOREIGN KEY (database_id) REFERENCES database(database_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dataset_dats_fina_status_f; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dataset
    ADD CONSTRAINT fk_dataset_dats_fina_status_f FOREIGN KEY (status_final_id) REFERENCES status_final(status_final_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dataset_dats_org_organism; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dataset
    ADD CONSTRAINT fk_dataset_dats_org_organism FOREIGN KEY (org_id) REFERENCES organism(org_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dataset_dats_peri_period; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dataset
    ADD CONSTRAINT fk_dataset_dats_peri_period FOREIGN KEY (period_id) REFERENCES period(period_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dataset_dats_stat_status_p; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dataset
    ADD CONSTRAINT fk_dataset_dats_stat_status_p FOREIGN KEY (status_progress_id) REFERENCES status_progress(status_progress_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_dat_dats_data_data_for; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_data_format
    ADD CONSTRAINT fk_dats_dat_dats_data_data_for FOREIGN KEY (data_format_id) REFERENCES data_format(data_format_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_dat_dats_data_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_data_format
    ADD CONSTRAINT fk_dats_dat_dats_data_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_dat_dats_req_data_data_for; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_required_data_format
    ADD CONSTRAINT fk_dats_dat_dats_req_data_data_for FOREIGN KEY (data_format_id) REFERENCES data_format(data_format_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_dat_dats_req_data_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_required_data_format
    ADD CONSTRAINT fk_dats_dat_dats_req_data_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_dat_reference_inserted; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_data
    ADD CONSTRAINT fk_dats_dat_reference_inserted FOREIGN KEY (ins_dats_id) REFERENCES inserted_dataset(ins_dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_data_reference_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_data
    ADD CONSTRAINT fk_dats_data_reference_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_link_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_link
    ADD CONSTRAINT fk_dats_link_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_link_type; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_link
    ADD CONSTRAINT fk_dats_link_type FOREIGN KEY (type_id) REFERENCES type_link(type_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_loc_dataset_gcmd_location_keyword; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_loc
    ADD CONSTRAINT fk_dats_loc_dataset_gcmd_location_keyword FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_loc_gcmd_location_keyword; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_loc
    ADD CONSTRAINT fk_dats_loc_gcmd_location_keyword FOREIGN KEY (gcmd_loc_id) REFERENCES gcmd_location_keyword(gcmd_loc_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_method; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_method
    ADD CONSTRAINT fk_dats_method FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_ori_dats_orig_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_originators
    ADD CONSTRAINT fk_dats_ori_dats_orig_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_ori_dats_orig_personne; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_originators
    ADD CONSTRAINT fk_dats_ori_dats_orig_personne FOREIGN KEY (pers_id) REFERENCES personne(pers_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_ori_reference_contact_; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_originators
    ADD CONSTRAINT fk_dats_ori_reference_contact_ FOREIGN KEY (contact_type_id) REFERENCES contact_type(contact_type_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_pla_dats_plac_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_place
    ADD CONSTRAINT fk_dats_pla_dats_plac_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_pla_dats_plac_place; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_place
    ADD CONSTRAINT fk_dats_pla_dats_plac_place FOREIGN KEY (place_id) REFERENCES place(place_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_pro_dats_proj_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_proj
    ADD CONSTRAINT fk_dats_pro_dats_proj_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_pro_dats_proj_project; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_proj
    ADD CONSTRAINT fk_dats_pro_dats_proj_project FOREIGN KEY (project_id) REFERENCES project(project_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_rol_dats_role_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_role
    ADD CONSTRAINT fk_dats_rol_dats_role_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_rol_dats_role_role; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_role
    ADD CONSTRAINT fk_dats_rol_dats_role_role FOREIGN KEY (role_id) REFERENCES role(role_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_sen_dats_sens_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_sensor
    ADD CONSTRAINT fk_dats_sen_dats_sens_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_sen_dats_sens_sensor; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_sensor
    ADD CONSTRAINT fk_dats_sen_dats_sens_sensor FOREIGN KEY (sensor_id) REFERENCES sensor(sensor_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_typ_dats_type_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_type
    ADD CONSTRAINT fk_dats_typ_dats_type_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_typ_dats_type_dataset_; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_type
    ADD CONSTRAINT fk_dats_typ_dats_type_dataset_ FOREIGN KEY (dats_type_id) REFERENCES dataset_type(dats_type_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_var_dats_var2_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_var
    ADD CONSTRAINT fk_dats_var_dats_var2_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_var_dats_var3_unit; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_var
    ADD CONSTRAINT fk_dats_var_dats_var3_unit FOREIGN KEY (unit_id) REFERENCES unit(unit_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_var_dats_var4_vertical_level_type; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_var
    ADD CONSTRAINT fk_dats_var_dats_var4_vertical_level_type FOREIGN KEY (vert_level_type_id) REFERENCES vertical_level_type(vert_level_type_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_dats_var_dats_var_variable; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY dats_var
    ADD CONSTRAINT fk_dats_var_dats_var_variable FOREIGN KEY (var_id) REFERENCES variable(var_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_event; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY url_event
    ADD CONSTRAINT fk_event FOREIGN KEY (event_id) REFERENCES event(event_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_file_reference_personne; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY file
    ADD CONSTRAINT fk_file_reference_personne FOREIGN KEY (integrateur_id) REFERENCES personne(pers_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_gcmd_sci_gcmd_pare_gcmd_sci; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY gcmd_science_keyword
    ADD CONSTRAINT fk_gcmd_sci_gcmd_pare_gcmd_sci FOREIGN KEY (gcm_gcmd_id) REFERENCES gcmd_science_keyword(gcmd_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_journal_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY journal
    ADD CONSTRAINT fk_journal_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_journal_type_journal; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY journal
    ADD CONSTRAINT fk_journal_type_journal FOREIGN KEY (type_journal_id) REFERENCES type_journal(type_journal_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_localisa_reference_bounding; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY localisation
    ADD CONSTRAINT fk_localisa_reference_bounding FOREIGN KEY (bound_id) REFERENCES boundings(bound_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_localisa_reference_flag1; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY localisation
    ADD CONSTRAINT fk_localisa_reference_flag1 FOREIGN KEY (flag_loc_id) REFERENCES flag(flag_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_localisa_reference_flag2; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY localisation
    ADD CONSTRAINT fk_localisa_reference_flag2 FOREIGN KEY (flag_alt_id) REFERENCES flag(flag_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_mesure_reference_inserted; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY mesure
    ADD CONSTRAINT fk_mesure_reference_inserted FOREIGN KEY (ins_dats_id) REFERENCES inserted_dataset(ins_dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_mesure_reference_localisa; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY mesure
    ADD CONSTRAINT fk_mesure_reference_localisa FOREIGN KEY (localisation_id) REFERENCES localisation(localisation_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_mesure_reference_place; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY mesure
    ADD CONSTRAINT fk_mesure_reference_place FOREIGN KEY (place_id) REFERENCES place(place_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_mesure_reference_sequence; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY mesure
    ADD CONSTRAINT fk_mesure_reference_sequence FOREIGN KEY (sequence_id) REFERENCES sequence(sequence_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_param_reference_unit; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY param
    ADD CONSTRAINT fk_param_reference_unit FOREIGN KEY (unit_id) REFERENCES unit(unit_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_param_reference_variable; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY param
    ADD CONSTRAINT fk_param_reference_variable FOREIGN KEY (var_id) REFERENCES variable(var_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_period_p_reference_period; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY period_project
    ADD CONSTRAINT fk_period_p_reference_period FOREIGN KEY (period_id) REFERENCES period(period_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_period_p_reference_project; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY period_project
    ADD CONSTRAINT fk_period_p_reference_project FOREIGN KEY (project_id) REFERENCES project(project_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_personne_pers_org_organism; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY personne
    ADD CONSTRAINT fk_personne_pers_org_organism FOREIGN KEY (org_id) REFERENCES organism(org_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_place_gcmd_loc_key; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY place
    ADD CONSTRAINT fk_place_gcmd_loc_key FOREIGN KEY (gcmd_loc_id) REFERENCES gcmd_location_keyword(gcmd_loc_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_place_place_bou_bounding; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY place
    ADD CONSTRAINT fk_place_place_bou_bounding FOREIGN KEY (bound_id) REFERENCES boundings(bound_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_place_place_key_gcmd_pla; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY place
    ADD CONSTRAINT fk_place_place_key_gcmd_pla FOREIGN KEY (gcmd_plat_id) REFERENCES gcmd_plateform_keyword(gcmd_plat_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_place_place_sub_place; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY place
    ADD CONSTRAINT fk_place_place_sub_place FOREIGN KEY (pla_place_id) REFERENCES place(place_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_place_va_place_var_place; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY place_var
    ADD CONSTRAINT fk_place_va_place_var_place FOREIGN KEY (place_id) REFERENCES place(place_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_place_va_place_var_variable; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY place_var
    ADD CONSTRAINT fk_place_va_place_var_variable FOREIGN KEY (var_id) REFERENCES variable(var_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_pro_inst_keyword_instrument; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY proj_inst_keyword
    ADD CONSTRAINT fk_pro_inst_keyword_instrument FOREIGN KEY (gcmd_sensor_id) REFERENCES gcmd_instrument_keyword(gcmd_sensor_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_pro_loc_keyword_gcmdloc; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY proj_loc_keyword
    ADD CONSTRAINT fk_pro_loc_keyword_gcmdloc FOREIGN KEY (gcmd_loc_id) REFERENCES gcmd_location_keyword(gcmd_loc_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_pro_loc_keyword_project; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY proj_loc_keyword
    ADD CONSTRAINT fk_pro_loc_keyword_project FOREIGN KEY (project_id) REFERENCES project(project_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_proj_inst_keyword_project; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY proj_inst_keyword
    ADD CONSTRAINT fk_proj_inst_keyword_project FOREIGN KEY (project_id) REFERENCES project(project_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_proj_plat_keyword_plateform; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY proj_plat_keyword
    ADD CONSTRAINT fk_proj_plat_keyword_plateform FOREIGN KEY (gcmd_plat_id) REFERENCES gcmd_plateform_keyword(gcmd_plat_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_proj_plat_keyword_project; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY proj_plat_keyword
    ADD CONSTRAINT fk_proj_plat_keyword_project FOREIGN KEY (project_id) REFERENCES project(project_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_proj_scie_keyword_project; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY proj_scie_keyword
    ADD CONSTRAINT fk_proj_scie_keyword_project FOREIGN KEY (project_id) REFERENCES project(project_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_proj_scie_keyword_science; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY proj_scie_keyword
    ADD CONSTRAINT fk_proj_scie_keyword_science FOREIGN KEY (gcmd_id) REFERENCES gcmd_science_keyword(gcmd_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_project_project_s_project; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY project
    ADD CONSTRAINT fk_project_project_s_project FOREIGN KEY (pro_project_id) REFERENCES project(project_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_sensor_p_sensor_pl_place; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY sensor_place
    ADD CONSTRAINT fk_sensor_p_sensor_pl_place FOREIGN KEY (place_id) REFERENCES place(place_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_sensor_p_sensor_pl_sensor; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY sensor_place
    ADD CONSTRAINT fk_sensor_p_sensor_pl_sensor FOREIGN KEY (sensor_id) REFERENCES sensor(sensor_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_sensor_sensor_bo_bounding; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY sensor
    ADD CONSTRAINT fk_sensor_sensor_bo_bounding FOREIGN KEY (bound_id) REFERENCES boundings(bound_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_sensor_sensor_ke_gcmd_ins; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY sensor
    ADD CONSTRAINT fk_sensor_sensor_ke_gcmd_ins FOREIGN KEY (gcmd_sensor_id) REFERENCES gcmd_instrument_keyword(gcmd_sensor_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_sensor_sensor_ma_manufact; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY sensor
    ADD CONSTRAINT fk_sensor_sensor_ma_manufact FOREIGN KEY (manufacturer_id) REFERENCES manufacturer(manufacturer_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_sensor_v_sensor_va_sensor; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY sensor_var
    ADD CONSTRAINT fk_sensor_v_sensor_va_sensor FOREIGN KEY (sensor_id) REFERENCES sensor(sensor_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_sensor_v_sensor_va_variable; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY sensor_var
    ADD CONSTRAINT fk_sensor_v_sensor_va_variable FOREIGN KEY (var_id) REFERENCES variable(var_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_sequence_reference_inserted; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY sequence
    ADD CONSTRAINT fk_sequence_reference_inserted FOREIGN KEY (ins_dats_id) REFERENCES inserted_dataset(ins_dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_sequence_reference_local1; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY sequence
    ADD CONSTRAINT fk_sequence_reference_local1 FOREIGN KEY (sequence_loc_end_id) REFERENCES localisation(localisation_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_sequence_reference_local2; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY sequence
    ADD CONSTRAINT fk_sequence_reference_local2 FOREIGN KEY (sequence_loc_begin_id) REFERENCES localisation(localisation_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_url_dataset; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY url
    ADD CONSTRAINT fk_url_dataset FOREIGN KEY (dats_id) REFERENCES dataset(dats_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_url_event_rol_url_event_role_role; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY url_event_role
    ADD CONSTRAINT fk_url_event_rol_url_event_role_role FOREIGN KEY (role_id) REFERENCES role(role_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_url_event_rol_url_event_role_url_event; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY url_event_role
    ADD CONSTRAINT fk_url_event_rol_url_event_role_url_event FOREIGN KEY (url_event_id) REFERENCES url_event(url_event_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_valeur_reference_file; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY valeur
    ADD CONSTRAINT fk_valeur_reference_file FOREIGN KEY (file_id) REFERENCES file(file_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_valeur_reference_flag1; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY valeur
    ADD CONSTRAINT fk_valeur_reference_flag1 FOREIGN KEY (flag_qual_id) REFERENCES flag(flag_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_valeur_reference_flag2; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY valeur
    ADD CONSTRAINT fk_valeur_reference_flag2 FOREIGN KEY (flag_calc_id) REFERENCES flag(flag_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_valeur_reference_mesure; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY valeur
    ADD CONSTRAINT fk_valeur_reference_mesure FOREIGN KEY (mesure_id) REFERENCES mesure(mesure_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_valeur_reference_obs_leng; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY valeur
    ADD CONSTRAINT fk_valeur_reference_obs_leng FOREIGN KEY (obs_length_id) REFERENCES obs_length(obs_length_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_valeur_reference_param; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY valeur
    ADD CONSTRAINT fk_valeur_reference_param FOREIGN KEY (var_id) REFERENCES param(var_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_valeur_reference_sensor; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY valeur
    ADD CONSTRAINT fk_valeur_reference_sensor FOREIGN KEY (sensor_id) REFERENCES sensor(sensor_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: fk_variable_var_key_gcmd_sci; Type: FK CONSTRAINT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY variable
    ADD CONSTRAINT fk_variable_var_key_gcmd_sci FOREIGN KEY (gcmd_id) REFERENCES gcmd_science_keyword(gcmd_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- Name: boundings; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE boundings FROM PUBLIC;
REVOKE ALL ON TABLE boundings FROM wwwadm;
GRANT ALL ON TABLE boundings TO wwwadm;


--
-- Name: boundings_bound_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE boundings_bound_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE boundings_bound_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE boundings_bound_id_seq TO wwwadm;


--
-- Name: contact_type; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE contact_type FROM PUBLIC;
REVOKE ALL ON TABLE contact_type FROM wwwadm;
GRANT ALL ON TABLE contact_type TO wwwadm;


--
-- Name: country; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE country FROM PUBLIC;
REVOKE ALL ON TABLE country FROM wwwadm;
GRANT ALL ON TABLE country TO wwwadm;


--
-- Name: country_place; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE country_place FROM PUBLIC;
REVOKE ALL ON TABLE country_place FROM wwwadm;
GRANT ALL ON TABLE country_place TO wwwadm;


--
-- Name: data_availability; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE data_availability FROM PUBLIC;
REVOKE ALL ON TABLE data_availability FROM wwwadm;
GRANT ALL ON TABLE data_availability TO wwwadm;


--
-- Name: data_format; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE data_format FROM PUBLIC;
REVOKE ALL ON TABLE data_format FROM wwwadm;
GRANT ALL ON TABLE data_format TO wwwadm;


--
-- Name: data_format_data_format_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE data_format_data_format_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE data_format_data_format_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE data_format_data_format_id_seq TO wwwadm;


--
-- Name: data_policy; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE data_policy FROM PUBLIC;
REVOKE ALL ON TABLE data_policy FROM wwwadm;
GRANT ALL ON TABLE data_policy TO wwwadm;


--
-- Name: data_policy_data_policy_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE data_policy_data_policy_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE data_policy_data_policy_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE data_policy_data_policy_id_seq TO wwwadm;


--
-- Name: database; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE database FROM PUBLIC;
REVOKE ALL ON TABLE database FROM wwwadm;
GRANT ALL ON TABLE database TO wwwadm;


--
-- Name: database_database_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE database_database_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE database_database_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE database_database_id_seq TO wwwadm;


--
-- Name: dataset; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE dataset FROM PUBLIC;
REVOKE ALL ON TABLE dataset FROM wwwadm;
GRANT ALL ON TABLE dataset TO wwwadm;


--
-- Name: dataset_dats_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE dataset_dats_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE dataset_dats_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE dataset_dats_id_seq TO wwwadm;


--
-- Name: dataset_type; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE dataset_type FROM PUBLIC;
REVOKE ALL ON TABLE dataset_type FROM wwwadm;
GRANT ALL ON TABLE dataset_type TO wwwadm;


--
-- Name: dataset_type_dats_type_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE dataset_type_dats_type_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE dataset_type_dats_type_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE dataset_type_dats_type_id_seq TO wwwadm;


--
-- Name: dats_data; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE dats_data FROM PUBLIC;
REVOKE ALL ON TABLE dats_data FROM wwwadm;
GRANT ALL ON TABLE dats_data TO wwwadm;


--
-- Name: dats_data_format; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE dats_data_format FROM PUBLIC;
REVOKE ALL ON TABLE dats_data_format FROM wwwadm;
GRANT ALL ON TABLE dats_data_format TO wwwadm;


--
-- Name: dats_originators; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE dats_originators FROM PUBLIC;
REVOKE ALL ON TABLE dats_originators FROM wwwadm;
GRANT ALL ON TABLE dats_originators TO wwwadm;


--
-- Name: dats_place; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE dats_place FROM PUBLIC;
REVOKE ALL ON TABLE dats_place FROM wwwadm;
GRANT ALL ON TABLE dats_place TO wwwadm;


--
-- Name: dats_proj; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE dats_proj FROM PUBLIC;
REVOKE ALL ON TABLE dats_proj FROM wwwadm;
GRANT ALL ON TABLE dats_proj TO wwwadm;


--
-- Name: dats_required_data_format; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE dats_required_data_format FROM PUBLIC;
REVOKE ALL ON TABLE dats_required_data_format FROM wwwadm;
GRANT ALL ON TABLE dats_required_data_format TO wwwadm;


--
-- Name: dats_role; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE dats_role FROM PUBLIC;
REVOKE ALL ON TABLE dats_role FROM wwwadm;
GRANT ALL ON TABLE dats_role TO wwwadm;


--
-- Name: dats_sensor; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE dats_sensor FROM PUBLIC;
REVOKE ALL ON TABLE dats_sensor FROM wwwadm;
GRANT ALL ON TABLE dats_sensor TO wwwadm;


--
-- Name: dats_type; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE dats_type FROM PUBLIC;
REVOKE ALL ON TABLE dats_type FROM wwwadm;
GRANT ALL ON TABLE dats_type TO wwwadm;


--
-- Name: dats_var; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE dats_var FROM PUBLIC;
REVOKE ALL ON TABLE dats_var FROM wwwadm;
GRANT ALL ON TABLE dats_var TO wwwadm;


--
-- Name: event; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE event FROM PUBLIC;
REVOKE ALL ON TABLE event FROM wwwadm;
GRANT ALL ON TABLE event TO wwwadm;


--
-- Name: file; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE file FROM PUBLIC;
REVOKE ALL ON TABLE file FROM wwwadm;
GRANT ALL ON TABLE file TO wwwadm;


--
-- Name: flag; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE flag FROM PUBLIC;
REVOKE ALL ON TABLE flag FROM wwwadm;
GRANT ALL ON TABLE flag TO wwwadm;


--
-- Name: gcmd_science_keyword; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE gcmd_science_keyword FROM PUBLIC;
REVOKE ALL ON TABLE gcmd_science_keyword FROM wwwadm;
GRANT ALL ON TABLE gcmd_science_keyword TO wwwadm;


--
-- Name: gcmd; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE gcmd FROM PUBLIC;
REVOKE ALL ON TABLE gcmd FROM wwwadm;
GRANT ALL ON TABLE gcmd TO wwwadm;


--
-- Name: gcmd_instrument_keyword; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE gcmd_instrument_keyword FROM PUBLIC;
REVOKE ALL ON TABLE gcmd_instrument_keyword FROM wwwadm;
GRANT ALL ON TABLE gcmd_instrument_keyword TO wwwadm;


--
-- Name: gcmd_instrument_keyword_gcmd_sensor_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE gcmd_instrument_keyword_gcmd_sensor_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE gcmd_instrument_keyword_gcmd_sensor_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE gcmd_instrument_keyword_gcmd_sensor_id_seq TO wwwadm;


--
-- Name: gcmd_plateform_keyword; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE gcmd_plateform_keyword FROM PUBLIC;
REVOKE ALL ON TABLE gcmd_plateform_keyword FROM wwwadm;
GRANT ALL ON TABLE gcmd_plateform_keyword TO wwwadm;


--
-- Name: gcmd_plateform_keyword_gcmd_plat_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE gcmd_plateform_keyword_gcmd_plat_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE gcmd_plateform_keyword_gcmd_plat_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE gcmd_plateform_keyword_gcmd_plat_id_seq TO wwwadm;


--
-- Name: gcmd_plateform_keyword_insitu; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE gcmd_plateform_keyword_insitu FROM PUBLIC;
REVOKE ALL ON TABLE gcmd_plateform_keyword_insitu FROM wwwadm;
GRANT ALL ON TABLE gcmd_plateform_keyword_insitu TO wwwadm;


--
-- Name: gcmd_science_keyword_gcmd_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE gcmd_science_keyword_gcmd_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE gcmd_science_keyword_gcmd_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE gcmd_science_keyword_gcmd_id_seq TO wwwadm;


--
-- Name: inserted_dataset; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE inserted_dataset FROM PUBLIC;
REVOKE ALL ON TABLE inserted_dataset FROM wwwadm;
GRANT ALL ON TABLE inserted_dataset TO wwwadm;


--
-- Name: journal; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE journal FROM PUBLIC;
REVOKE ALL ON TABLE journal FROM wwwadm;
GRANT ALL ON TABLE journal TO wwwadm;


--
-- Name: localisation; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE localisation FROM PUBLIC;
REVOKE ALL ON TABLE localisation FROM wwwadm;
GRANT ALL ON TABLE localisation TO wwwadm;


--
-- Name: manufacturer; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE manufacturer FROM PUBLIC;
REVOKE ALL ON TABLE manufacturer FROM wwwadm;
GRANT ALL ON TABLE manufacturer TO wwwadm;


--
-- Name: manufacturer_manufacturer_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE manufacturer_manufacturer_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE manufacturer_manufacturer_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE manufacturer_manufacturer_id_seq TO wwwadm;


--
-- Name: mesure; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE mesure FROM PUBLIC;
REVOKE ALL ON TABLE mesure FROM wwwadm;
GRANT ALL ON TABLE mesure TO wwwadm;


--
-- Name: obs_length; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE obs_length FROM PUBLIC;
REVOKE ALL ON TABLE obs_length FROM wwwadm;
GRANT ALL ON TABLE obs_length TO wwwadm;


--
-- Name: organism; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE organism FROM PUBLIC;
REVOKE ALL ON TABLE organism FROM wwwadm;
GRANT ALL ON TABLE organism TO wwwadm;


--
-- Name: organism_org_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE organism_org_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE organism_org_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE organism_org_id_seq TO wwwadm;


--
-- Name: param; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE param FROM PUBLIC;
REVOKE ALL ON TABLE param FROM wwwadm;
GRANT ALL ON TABLE param TO wwwadm;


--
-- Name: period; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE period FROM PUBLIC;
REVOKE ALL ON TABLE period FROM wwwadm;
GRANT ALL ON TABLE period TO wwwadm;


--
-- Name: period_period_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE period_period_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE period_period_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE period_period_id_seq TO wwwadm;


--
-- Name: period_project; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE period_project FROM PUBLIC;
REVOKE ALL ON TABLE period_project FROM wwwadm;
GRANT ALL ON TABLE period_project TO wwwadm;


--
-- Name: personne; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE personne FROM PUBLIC;
REVOKE ALL ON TABLE personne FROM wwwadm;
GRANT ALL ON TABLE personne TO wwwadm;


--
-- Name: personne_pers_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE personne_pers_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE personne_pers_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE personne_pers_id_seq TO wwwadm;


--
-- Name: place; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE place FROM PUBLIC;
REVOKE ALL ON TABLE place FROM wwwadm;
GRANT ALL ON TABLE place TO wwwadm;


--
-- Name: place_insitu; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE place_insitu FROM PUBLIC;
REVOKE ALL ON TABLE place_insitu FROM wwwadm;
GRANT ALL ON TABLE place_insitu TO wwwadm;


--
-- Name: place_place_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE place_place_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE place_place_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE place_place_id_seq TO wwwadm;


--
-- Name: place_var; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE place_var FROM PUBLIC;
REVOKE ALL ON TABLE place_var FROM wwwadm;
GRANT ALL ON TABLE place_var TO wwwadm;


--
-- Name: project; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE project FROM PUBLIC;
REVOKE ALL ON TABLE project FROM wwwadm;
GRANT ALL ON TABLE project TO wwwadm;


--
-- Name: project_project_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE project_project_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE project_project_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE project_project_id_seq TO wwwadm;


--
-- Name: requete; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE requete FROM PUBLIC;
REVOKE ALL ON TABLE requete FROM wwwadm;
GRANT ALL ON TABLE requete TO wwwadm;


--
-- Name: role; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE role FROM PUBLIC;
REVOKE ALL ON TABLE role FROM wwwadm;
GRANT ALL ON TABLE role TO wwwadm;


--
-- Name: role_role_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE role_role_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE role_role_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE role_role_id_seq TO wwwadm;


--
-- Name: sensor; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE sensor FROM PUBLIC;
REVOKE ALL ON TABLE sensor FROM wwwadm;
GRANT ALL ON TABLE sensor TO wwwadm;


--
-- Name: sensor_place; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE sensor_place FROM PUBLIC;
REVOKE ALL ON TABLE sensor_place FROM wwwadm;
GRANT ALL ON TABLE sensor_place TO wwwadm;


--
-- Name: sensor_sensor_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE sensor_sensor_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE sensor_sensor_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE sensor_sensor_id_seq TO wwwadm;


--
-- Name: sensor_var; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE sensor_var FROM PUBLIC;
REVOKE ALL ON TABLE sensor_var FROM wwwadm;
GRANT ALL ON TABLE sensor_var TO wwwadm;


--
-- Name: sequence; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE sequence FROM PUBLIC;
REVOKE ALL ON TABLE sequence FROM wwwadm;
GRANT ALL ON TABLE sequence TO wwwadm;


--
-- Name: status_final; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE status_final FROM PUBLIC;
REVOKE ALL ON TABLE status_final FROM wwwadm;
GRANT ALL ON TABLE status_final TO wwwadm;


--
-- Name: status_final_status_final_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE status_final_status_final_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE status_final_status_final_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE status_final_status_final_id_seq TO wwwadm;


--
-- Name: status_progress; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE status_progress FROM PUBLIC;
REVOKE ALL ON TABLE status_progress FROM wwwadm;
GRANT ALL ON TABLE status_progress TO wwwadm;


--
-- Name: status_progress_status_progress_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE status_progress_status_progress_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE status_progress_status_progress_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE status_progress_status_progress_id_seq TO wwwadm;


--
-- Name: type_journal; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE type_journal FROM PUBLIC;
REVOKE ALL ON TABLE type_journal FROM wwwadm;
GRANT ALL ON TABLE type_journal TO wwwadm;


--
-- Name: unit; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE unit FROM PUBLIC;
REVOKE ALL ON TABLE unit FROM wwwadm;
GRANT ALL ON TABLE unit TO wwwadm;


--
-- Name: unit_unit_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE unit_unit_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE unit_unit_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE unit_unit_id_seq TO wwwadm;


--
-- Name: url; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE url FROM PUBLIC;
REVOKE ALL ON TABLE url FROM wwwadm;
GRANT ALL ON TABLE url TO wwwadm;


--
-- Name: url_event; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE url_event FROM PUBLIC;
REVOKE ALL ON TABLE url_event FROM wwwadm;
GRANT ALL ON TABLE url_event TO wwwadm;


--
-- Name: url_event_role; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE url_event_role FROM PUBLIC;
REVOKE ALL ON TABLE url_event_role FROM wwwadm;
GRANT ALL ON TABLE url_event_role TO wwwadm;


--
-- Name: url_url_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE url_url_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE url_url_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE url_url_id_seq TO wwwadm;


--
-- Name: valeur; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE valeur FROM PUBLIC;
REVOKE ALL ON TABLE valeur FROM wwwadm;
GRANT ALL ON TABLE valeur TO wwwadm;


--
-- Name: variable; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE variable FROM PUBLIC;
REVOKE ALL ON TABLE variable FROM wwwadm;
GRANT ALL ON TABLE variable TO wwwadm;


--
-- Name: variable_var_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE variable_var_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE variable_var_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE variable_var_id_seq TO wwwadm;


--
-- Name: vertical_level_type; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON TABLE vertical_level_type FROM PUBLIC;
REVOKE ALL ON TABLE vertical_level_type FROM wwwadm;
GRANT ALL ON TABLE vertical_level_type TO wwwadm;


--
-- Name: vertical_level_type_vert_level_type_id_seq; Type: ACL; Schema: public; Owner: wwwadm
--

REVOKE ALL ON SEQUENCE vertical_level_type_vert_level_type_id_seq FROM PUBLIC;
REVOKE ALL ON SEQUENCE vertical_level_type_vert_level_type_id_seq FROM wwwadm;
GRANT ALL ON SEQUENCE vertical_level_type_vert_level_type_id_seq TO wwwadm;


--
-- PostgreSQL database dump complete
--

