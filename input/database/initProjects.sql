--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- Name: project_project_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wwwadm
--

SELECT pg_catalog.setval('project_project_id_seq', 1, true);


--
-- Data for Name: project; Type: TABLE DATA; Schema: public; Owner: wwwadm
--

COPY project (project_id, pro_project_id, project_name, project_url) FROM stdin;
1	\N	#ProjectName	#ProjectUrl
\.


--
-- PostgreSQL database dump complete
--

