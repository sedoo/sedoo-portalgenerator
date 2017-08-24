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
-- Name: role_role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wwwadm
--

SELECT pg_catalog.setval('role_role_id_seq', 3, true);


--
-- Data for Name: role; Type: TABLE DATA; Schema: public; Owner: wwwadm
--

COPY role (role_id, role_name) FROM stdin;
1	public
2	#MainProject
3	#MainProjectAdm
\.


--
-- PostgreSQL database dump complete
--

