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

SET default_tablespace = '';

SET default_with_oids = false;

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
-- Name: id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY docs ALTER COLUMN id SET DEFAULT nextval('docs_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: wwwadm
--

ALTER TABLE ONLY suggest ALTER COLUMN id SET DEFAULT nextval('suggest_id_seq'::regclass);


--
-- Name: docs_pkey; Type: CONSTRAINT; Schema: public; Owner: wwwadm; Tablespace: 
--

ALTER TABLE ONLY docs
    ADD CONSTRAINT docs_pkey PRIMARY KEY (id);


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
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

