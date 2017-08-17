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
-- Name: contact_type_contact_type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wwwadm
--

SELECT pg_catalog.setval('contact_type_contact_type_id_seq', 4, true);


--
-- Name: data_format_data_format_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wwwadm
--

SELECT pg_catalog.setval('data_format_data_format_id_seq', 26, true);


--
-- Name: dataset_type_dats_type_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wwwadm
--

SELECT pg_catalog.setval('dataset_type_dats_type_id_seq', 2, true);


--
-- Name: gcmd_instrument_keyword_gcmd_sensor_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wwwadm
--

SELECT pg_catalog.setval('gcmd_instrument_keyword_gcmd_sensor_id_seq', 172, true);


--
-- Name: gcmd_plateform_keyword_gcmd_plat_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wwwadm
--

SELECT pg_catalog.setval('gcmd_plateform_keyword_gcmd_plat_id_seq', 514, true);


--
-- Name: gcmd_science_keyword_gcmd_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wwwadm
--

SELECT pg_catalog.setval('gcmd_science_keyword_gcmd_id_seq', 976, true);


--
-- Name: organism_org_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wwwadm
--

SELECT pg_catalog.setval('organism_org_id_seq', 197, true);


--
-- Name: type_journal_type_journal_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wwwadm
--

SELECT pg_catalog.setval('type_journal_type_journal_id_seq', 7, true);


--
-- Name: unit_unit_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wwwadm
--

SELECT pg_catalog.setval('unit_unit_id_seq', 169, true);


--
-- Data for Name: contact_type; Type: TABLE DATA; Schema: public; Owner: wwwadm
--

COPY contact_type (contact_type_id, contact_type_name) FROM stdin;
3	Database contact
4	User
2	Dataset contact
1	PI or Lead scientist
\.


--
-- Data for Name: data_format; Type: TABLE DATA; Schema: public; Owner: wwwadm
--

COPY data_format (data_format_id, data_format_name) FROM stdin;
1	CSV
2	NetCDF
3	ASCII
4	NASA AMES
5	HDF
6	GRIB
7	BUFR
8	RAW BINARY
18	Binary
9	McIDAS area files
16	GRIB2
14	JPG
17	LFI
19	IRIS
20	HDF5
21	XLS
22	xlsx
23	Matlab
\.


--
-- Data for Name: dataset_type; Type: TABLE DATA; Schema: public; Owner: wwwadm
--

COPY dataset_type (dats_type_id, dats_type_title, dats_type_desc) FROM stdin;
1	SATELLITE	\N
2	MODEL	\N
3	VALUE-ADDED DATASET	\N
\.


--
-- Data for Name: gcmd_instrument_keyword; Type: TABLE DATA; Schema: public; Owner: wwwadm
--

COPY gcmd_instrument_keyword (gcmd_sensor_id, gcmd_sensor_name, gcm_gcmd_id, gcmd_level, thesaurus_id, uid) FROM stdin;
1	ACOUSTIC SOUNDERS	\N	\N	\N	\N
2	AEROSOL COLLECTORS	\N	\N	\N	\N
3	AEROSOL/CLOUD PARTICLE SIZER	\N	\N	\N	\N
5	ANEMOMETERS	\N	\N	\N	\N
6	AUTOANALYZER	\N	\N	\N	\N
7	AWS > Automated Weather System	\N	\N	\N	\N
8	BALANCE	\N	\N	\N	\N
9	BATHYTHERMOGRAPHS	\N	\N	\N	\N
10	CARBON ANALYZERS	\N	\N	\N	\N
11	CAMERAS	\N	\N	\N	\N
12	CEILOMETERS	\N	\N	\N	\N
13	CHEMILUMINESCENCE	\N	\N	\N	\N
14	CHN ANALYZERS > Carbon, Hydrogen, Nitrogen Analyzers	\N	\N	\N	\N
15	CO2 ANALYZERS	\N	\N	\N	\N
16	CONDUCTIVITY METERS	\N	\N	\N	\N
17	CURRENT METERS	\N	\N	\N	\N
18	DISDROMETERS	\N	\N	\N	\N
19	DRIFTING BUOYS	\N	\N	\N	\N
20	DROPSONDES	\N	\N	\N	\N
21	EDDY CORRELATION DEVICES	\N	\N	\N	\N
22	GAS CHROMATOGRAPHS	\N	\N	\N	\N
23	GAS SENSORS	\N	\N	\N	\N
24	GPS RECEIVERS	\N	\N	\N	\N
25	HUMIDITY SENSORS	\N	\N	\N	\N
26	INTERFEROMETERS	\N	\N	\N	\N
27	LIDAR > Light Detection and Ranging	\N	\N	\N	\N
28	LIP > Lightning Instrument Package	\N	\N	\N	\N
29	LYSIMETERS	\N	\N	\N	\N
30	MAGNETOMETERS	\N	\N	\N	\N
31	MICROSCOPES	\N	\N	\N	\N
32	NEPHELOMETERS	\N	\N	\N	\N
33	OXYGEN ANALYZERS	\N	\N	\N	\N
34	OZONE SENSOR	\N	\N	\N	\N
35	PARTICLE DETECTORS	\N	\N	\N	\N
36	PH METERS	\N	\N	\N	\N
37	PRESSURE SENSORS	\N	\N	\N	\N
38	RADAR > Radio Detection and Ranging	\N	\N	\N	\N
39	RADIOMETERS	\N	\N	\N	\N
40	RADIOSONDES	\N	\N	\N	\N
41	RAIN GAUGES	\N	\N	\N	\N
42	SALINOMETERS	\N	\N	\N	\N
43	SEDIMENT METERS	\N	\N	\N	\N
44	SEISMOMETERS	\N	\N	\N	\N
45	SODAR > Sound Detection and Ranging	\N	\N	\N	\N
46	SOIL MOISTURE PROBE	\N	\N	\N	\N
47	SOIL SAMPLER	\N	\N	\N	\N
48	SOIL TEMPERATURE PROBE	\N	\N	\N	\N
49	SPECTROMETERS	\N	\N	\N	\N
51	STREAM GAUGES	\N	\N	\N	\N
53	TEMPERATURE SENSORS	\N	\N	\N	\N
54	TEOM > Tapered Element Oscillating Microbalance	\N	\N	\N	\N
55	TETHERSONDES	\N	\N	\N	\N
56	THERMOSALINOGRAPHS	\N	\N	\N	\N
57	TIDE GAUGES	\N	\N	\N	\N
58	VIDEO CAMERA	\N	\N	\N	\N
59	VISUAL OBSERVATIONS	\N	\N	\N	\N
60	WATER LEVEL GAUGES	\N	\N	\N	\N
61	WET DEPOSITION COLLECTORS	\N	\N	\N	\N
62	WET/DRY PRECIPITATION SAMPLERS	\N	\N	\N	\N
4	AETHALOMETER	\N	\N	\N	\N
63	ELECTRIC FIELD MILL	\N	\N	\N	\N
64	WAVE HEIGHT GAUGES	\N	\N	\N	\N
65	WIND PROFILERS	\N	\N	\N	\N
52	PHOTOMETERS	\N	\N	\N	\N
66	PYRANOMETERS	\N	\N	\N	\N
67	PYRGEOMETERS	\N	\N	\N	\N
69	WAVE MOTION SENSORS	\N	\N	\N	\N
71	INDUCTION RING	\N	\N	\N	\N
70	SOIL HEAT FLUX TRANSDUCER	\N	\N	\N	\N
75	DRY DEPOSITION COLLECTORS	\N	\N	\N	\N
76	WATER BOTTLE	\N	\N	\N	\N
77	NDIR Gas Analyzer > Nondispersive Infrared Gas Analyser	\N	\N	\N	\N
78	HVAS > High Volume Air Sampler	\N	\N	\N	\N
80	VOC Sampler	\N	\N	\N	\N
79	HVPS > High Volume Particle Sampler	\N	\N	\N	\N
81	PLANKTON NETS	\N	\N	\N	\N
82	SEDIMENT TRAPS	\N	\N	\N	\N
83	ADCP > Acoustic Doppler Current Profiler	\N	\N	\N	\N
50	CTD > Conductivity, Temperature, Depth	\N	\N	\N	\N
84	ALTIMETERS	\N	\N	\N	\N
92	Earth Remote Sensing Instruments	\N	2	\N	
93	Active Remote Sensing	92	3	\N	
94	Altimeters	93	4	\N	
95	Imaging Radars	93	4	\N	
96	Profilers/Sounders	93	4	\N	
97	Acoustic Sounders	96	5	\N	
98	Lidar/Laser Sounders	96	5	\N	
101	Radar Sounders	96	5	\N	
108	Radio Sounders	96	5	\N	
109	Scatterometers	93	4	\N	
110	Passive Remote Sensing	92	3	\N	
111	Magnetic Field/Electric Field Instruments	110	4	\N	
113	Photon/Optical Detectors	110	4	\N	
118	Photometers	113	5	\N	
119	Positioning/Navigation	110	4	\N	
120	GPS	119	5	\N	
128	Spectrometers/Radiometers	110	4	\N	
129	Imaging Spectrometers/Radiometers	128	5	\N	
136	Interferometers	128	5	\N	
137	Radiometers	128	5	\N	
139	Spectrometers	128	5	\N	
140	Thermal/Radiation Detectors	110	4	\N	
143	In Situ/Laboratory Instruments	\N	2	\N	
144	Chemical Meters/Analyzers	143	3	\N	
163	Conductivity Sensors	143	3	\N	
164	Current/Wind Meters	143	3	\N	
171	Electrical Meters	143	3	\N	
\.


--
-- Data for Name: gcmd_plateform_keyword; Type: TABLE DATA; Schema: public; Owner: wwwadm
--

COPY gcmd_plateform_keyword (gcmd_plat_id, gcmd_plat_name, gcmd_level, gcm_gcmd_id, thesaurus_id, uid) FROM stdin;
3	Ships	\N	\N	\N	\N
4	Balloons	\N	\N	\N	\N
8	Satellites	\N	\N	\N	\N
1	Geographic Regions	\N	\N	\N	\N
11	Operational Model	\N	\N	\N	\N
13	Radars	\N	\N	\N	\N
14	Ground networks	\N	\N	\N	\N
15	Atmospheric sites	\N	\N	\N	\N
16	Hydrometeorological sites	\N	\N	\N	\N
17	Gliders	\N	\N	\N	\N
19	Climate projections	\N	\N	\N	\N
20	Re-analyses	\N	\N	\N	\N
12	Research model simulation	\N	\N	\N	\N
18	Drifters, buoys and moorings	\N	\N	\N	\N
22	Ocean sites	\N	\N	\N	\N
23	Fixed Observation Stations	\N	\N	\N	\N
21	Post-event surveys	\N	\N	\N	\N
24	Reanalysis	\N	\N	\N	\N
25	Forecast	\N	\N	\N	\N
26	MED-CORDEX hindcast	\N	\N	\N	\N
27	HyMeX hindcast	\N	\N	\N	\N
28	MED-CORDEX scenario	\N	\N	\N	\N
29	Case study	\N	\N	\N	\N
30	Value-added dataset	\N	\N	\N	\N
10	Aircraft	1	\N	\N	\N
442	Balloons/Rockets	2	\N	\N	2196cc92-a5da-4233-9509-5523385da1d7
447	Earth Observation Satellites	2	\N	\N	3466eed1-2fbb-49bf-ab0b-dc08731d502b
448	ADEOS (Advanced Earth Observing Satellite)	3	447	\N	119b40ad-749c-4ff6-af9b-1e9696f78dd8
449	ADEOS-I	4	448	\N	359bcfa1-966f-4d2b-a48d-ac12165d250f
450	ADEOS-II	4	448	\N	5d00fc17-cf10-4d1b-b871-07099d0b728a
451	DMSP (Defense Meteorological Satellite Program)	3	447	\N	1cf8cbcd-c1be-4c78-9272-b62adad59aa1
452	METEOSAT	3	447	\N	28eac19a-5500-4a21-af30-ab7a364ff8d0
453	MSG	4	452	\N	5aac06ef-6ade-49b6-a98c-45516a9a646a
454	NASA Earth System Science Pathfinder	3	447	\N	de1e0fd4-d865-4726-9bde-96804cf455b7
455	AQUARIUS	4	454	\N	6a8c37b7-88f2-453c-a5b7-a7bc2a49db40
456	CALIPSO	4	454	\N	01b319ce-cbe2-4894-bb33-04c43ceef23b
457	CLOUDSAT	4	454	\N	f3a724fa-5d0c-4ca1-872b-41ef08ab7b5d
458	GRACE	4	454	\N	2e7aa2e6-9d25-4c6e-aef3-6e86d3773bac
459	OCO-2	4	454	\N	6d5f222a-7750-4fd3-aa14-3c0d0059bc85
466	Megha-Tropiques	3	447	\N	7bca3532-02ec-4ab7-a01b-14185479c209
467	METOP	3	447	\N	8c192c86-d07c-4e7b-af8f-92aa4b40fca7
468	SPOT	3	447	\N	5615d18d-4217-42a0-a53d-77298834fc2e
469	In Situ Land-based Platforms	2	\N	\N	4f396ff6-7bea-4ba4-afa3-198ebd914a4a
470	AIR MONITORING STATIONS/NETWORKS	3	469	\N	76ba9890-0da6-4567-8b8b-0deff9108ef2
475	HYDROLOGICAL STATIONS	3	469	\N	73d106f1-2ba9-47db-ae92-6550a024744c
476	STREAMFLOW STATION	4	475	\N	7b335954-929b-4568-a758-1640d15c2504
478	MOBILE STATIONS/VEHICLES	3	469	\N	c76b3744-6047-4ba9-9364-ebe1a0e3c502
480	OCEAN PLATFORM/OCEAN STATIONS	3	469	\N	62e9613a-6e40-41cf-838a-ed6ac0d4871b
481	COASTAL STATIONS	4	480	\N	897f64c0-14e3-48d8-99fe-a589f57133d0
483	WEATHER STATIONS/NETWORKS	3	469	\N	57b7373d-5c21-4abb-8097-a410adc2a074
484	METEOROLOGICAL STATIONS	4	483	\N	9b51d8b7-1ad3-4ca4-985b-e178bb17f745
485	SOLAR RADIATION STATIONS	4	483	\N	30778eeb-9fab-4503-a230-1fc470f297ed
486	WEATHER STATIONS	4	483	\N	1551f765-cbb8-479f-a796-87c61868c509
487	X-POW	4	483	\N	8ba138b3-efea-491a-8595-e06bd53f7e2e
488	In Situ Ocean-based Platforms	2	\N	\N	e50b2a1a-7d9d-4c09-ac7e-dc29f0c08fc7
489	BUOYS	3	488	\N	e36481f3-5507-428b-a870-67f6d96ae389
490	FLOATS	3	488	\N	6e59f4bf-41dd-4ade-9070-4efcae4628fb
491	PALACE FLOAT	4	490	\N	b4d40e77-a862-418e-a8dc-f7b7e704b4cc
492	PROTEUS	4	490	\N	c9bfbe86-064a-4d64-875b-cb36bff3f9e9
493	MOORINGS	3	488	\N	1468d86c-f2b8-4fbf-8e8b-8831fd598801
494	ATLAS MOORINGS	4	493	\N	d52d296b-370a-4741-8f07-e6b6873191c6
497	C-MAN	4	480	\N	7fdf83a9-e0b3-4bb2-a6f4-801078f62cc9
498	DRILLING PLATFORMS	4	480	\N	cd14c407-881b-4fc1-8222-f1eeed77f4e2
499	OCEAN PLATFORMS	4	480	\N	5a4e787b-55e4-47d4-9520-ee74d6efdb6e
500	OCEAN WEATHER STATIONS	4	480	\N	d26f4894-667e-4e29-8e0b-5db476c98464
502	SHIPS	3	488	\N	82a67b12-e99d-4c90-8a6a-a6f79d4c3c7b
503	Maps/Charts/Photographs	2	\N	\N	af11dd2a-e514-4329-bbc5-0f36f2776a26
508	Models/Analyses	2	\N	\N	113ecbc2-ab36-4d58-a96c-a6ce0106e749
513	Navigation Platforms	2	\N	\N	1506fb17-7ac4-44ce-bde5-074885bdb2d2
514	GPS (Global Positioning System)	3	513	\N	7bf16419-1047-4902-a4fa-38c74bceb3bd
\.


--
-- Data for Name: gcmd_science_keyword; Type: TABLE DATA; Schema: public; Owner: wwwadm
--

COPY gcmd_science_keyword (gcmd_id, gcm_gcmd_id, gcmd_name, gcmd_level, thesaurus_id, uid) FROM stdin;
1	\N	Atmosphere	1	\N	\N
2	1	Atmospheric Radiation	2	\N	\N
3	2	Radiative Forcing	3	\N	\N
4	2	Shortwave Radiation	3	\N	\N
5	2	Incoming Solar Radiation	3	\N	\N
6	2	Absorption	3	\N	\N
7	2	Heat Flux	3	\N	\N
8	2	Airglow	3	\N	\N
9	2	Transmittance	3	\N	\N
10	2	Atmospheric Emitted Radiation	3	\N	\N
11	2	Atmospheric Heating	3	\N	\N
12	2	Scattering	3	\N	\N
13	2	Emissivity	3	\N	\N
14	2	Longwave Radiation	3	\N	\N
15	2	Ultraviolet Radiation	3	\N	\N
16	2	Net Radiation	3	\N	\N
17	2	Optical Depth/Thickness	3	\N	\N
18	2	Outgoing Longwave Radiation	3	\N	\N
19	2	Albedo	3	\N	\N
20	2	Radiative Flux	3	\N	\N
21	2	Anisotropy	3	\N	\N
22	1	Altitude	2	\N	\N
23	22	Geopotential Height	3	\N	\N
24	22	Barometric Altitude	3	\N	\N
25	22	Stratopause	3	\N	\N
26	22	Planetary Boundary Layer Height	3	\N	\N
27	22	Tropopause	3	\N	\N
28	22	Station Height	3	\N	\N
29	22	Mesopause	3	\N	\N
30	1	Clouds	2	\N	\N
31	30	Cloud Asymmetry	3	\N	\N
32	30	Cloud Vertical Distribution	3	\N	\N
33	30	Cloud Types	3	\N	\N
34	30	Cloud Precipitable Water	3	\N	\N
35	30	Cloud Top Temperature	3	\N	\N
36	30	Cloud Amount/Frequency	3	\N	\N
37	30	Cloud Base Temperature	3	\N	\N
38	30	Cloud Top Pressure	3	\N	\N
39	30	Cloud Liquid Water/Ice	3	\N	\N
40	30	Cloud Ceiling	3	\N	\N
41	30	Cloud Midlayer Temperature	3	\N	\N
42	30	Cloud Emissivity	3	\N	\N
43	30	Cloud Optical Depth/Thickness	3	\N	\N
44	30	Cloud Base	3	\N	\N
45	30	Cloud Base Pressure	3	\N	\N
46	30	Droplet Concentration/Size	3	\N	\N
47	30	Cloud Reflectance	3	\N	\N
48	30	Cloud Condensation Nuclei	3	\N	\N
49	30	Cloud Height	3	\N	\N
50	30	Cloud Forcing	3	\N	\N
51	30	Cloud Mass Flux	3	\N	\N
52	1	Atmospheric Winds	2	\N	\N
53	52	Flight Level Winds	3	\N	\N
54	52	Turbulence	3	\N	\N
55	52	Upper Level Winds	3	\N	\N
56	52	Vorticity	3	\N	\N
57	52	Wind Shear	3	\N	\N
58	52	Wind Chill	3	\N	\N
59	52	Wind Profiles	3	\N	\N
60	52	Boundary Layer Winds	3	\N	\N
61	52	Streamfunctions	3	\N	\N
62	52	Surface Winds	3	\N	\N
63	52	Wind Tendency	3	\N	\N
64	52	Wind Stress	3	\N	\N
65	52	Convergence/Divergence	3	\N	\N
66	52	Vertical Wind Motion	3	\N	\N
67	52	Convection	3	\N	\N
68	1	Precipitation	2	\N	\N
69	68	Precipitation Rate	3	\N	\N
70	68	Rain	3	\N	\N
71	68	Sleet	3	\N	\N
72	68	Snow	3	\N	\N
73	68	Hydrometeors	3	\N	\N
75	68	Liquid Water Equivalent	3	\N	\N
76	68	Freezing Rain	3	\N	\N
77	68	Precipitation Anomalies	3	\N	\N
78	68	Hail	3	\N	\N
79	68	Droplet Size	3	\N	\N
80	68	Acid Rain	3	\N	\N
81	1	Atmospheric Pressure	2	\N	\N
82	81	Topographic Waves	3	\N	\N
83	81	Pressure Tendency	3	\N	\N
84	81	Anticyclones/Cyclones	3	\N	\N
85	81	Pressure Anomalies	3	\N	\N
86	81	Hydrostatic Pressure	3	\N	\N
87	81	Gravity Wave	3	\N	\N
88	81	Pressure Thickness	3	\N	\N
89	81	Surface Pressure	3	\N	\N
90	81	Differential Pressure	3	\N	\N
91	81	Planetary Boundary Layer Height	3	\N	\N
92	81	Sea Level Pressure	3	\N	\N
93	81	Oscillations	3	\N	\N
94	81	Planetary/Rossby Waves	3	\N	\N
95	81	Atmospheric Pressure Measurements	3	\N	\N
96	81	Static Pressure	3	\N	\N
97	1	Atmospheric Temperature	2	\N	\N
98	97	Deiced Temperature	3	\N	\N
99	97	Inversion Height	3	\N	\N
100	97	Boundary Layer Temperature	3	\N	\N
101	97	Temperature Anomalies	3	\N	\N
102	97	Virtual Temperature	3	\N	\N
103	97	Degree Days	3	\N	\N
104	97	Static Temperature	3	\N	\N
105	97	Surface Air Temperature	3	\N	\N
106	97	Skin Temperature	3	\N	\N
107	97	Maximum/Minimum Temperature	3	\N	\N
108	97	Atmospheric Stability	3	\N	\N
109	97	Potential Temperature	3	\N	\N
110	97	Temperature Tendency	3	\N	\N
111	97	Temperature Profiles	3	\N	\N
112	97	Air Temperature	3	\N	\N
113	1	Atmospheric Water Vapor	2	\N	\N
114	113	Precipitable Water	3	\N	\N
115	113	Sublimation	3	\N	\N
116	113	Evapotranspiration	3	\N	\N
117	113	Condensation	3	\N	\N
118	113	Humidity	3	\N	\N
119	113	Dew Point Temperature	3	\N	\N
120	113	Water Vapor Tendency	3	\N	\N
121	113	Water Vapor Profiles	3	\N	\N
122	113	Evaporation	3	\N	\N
123	113	Water Vapor	3	\N	\N
124	1	Atmospheric Electricity	2	\N	\N
125	124	Atmospheric Conductivity	3	\N	\N
126	124	Total Electron Content	3	\N	\N
127	124	Lightning	3	\N	\N
128	124	Electric Field	3	\N	\N
129	1	Atmospheric Phenomena	2	\N	\N
130	129	Monsoons	3	\N	\N
131	129	Fog	3	\N	\N
132	129	Typhoons	3	\N	\N
133	129	Storms	3	\N	\N
134	129	Freeze	3	\N	\N
135	129	Drought	3	\N	\N
136	129	Frost	3	\N	\N
137	129	Tornadoes	3	\N	\N
138	129	Hurricanes	3	\N	\N
139	129	Cyclones	3	\N	\N
140	1	Aerosols	2	\N	\N
141	140	Sulfate Particles	3	\N	\N
142	140	Aerosol Optical Depth/Thickness	3	\N	\N
143	142	Angstrom Exponent	4	\N	\N
144	140	Organic Particles	3	\N	\N
145	140	Nitrate Particles	3	\N	\N
146	140	Dust/Ash/Smoke	3	\N	\N
147	140	Carbonaceous Aerosols	3	\N	\N
148	140	Particulate Matter	3	\N	\N
149	140	Aerosol Backscatter	3	\N	\N
150	140	Cloud Condensation Nuclei	3	\N	\N
151	140	Aerosol Particle Properties	3	\N	\N
152	140	Aerosol Extinction	3	\N	\N
153	140	Aerosol Radiance	3	\N	\N
154	1	Air Quality	2	\N	\N
155	154	Turbidity	3	\N	\N
156	154	Lead	3	\N	\N
157	154	Smog	3	\N	\N
158	154	Particulates	3	\N	\N
159	154	Visibility	3	\N	\N
160	154	Emissions	3	\N	\N
161	1	Atmospheric Chemistry	2	\N	\N
162	161	Oxygen Compounds	3	\N	\N
163	162	Molecular Oxygen	4	\N	\N
164	162	Ozone	4	\N	\N
165	161	Trace Gases/Trace Species	3	\N	\N
166	161	Trace Elements/Trace Metals	3	\N	\N
167	161	Sulfur Compounds	3	\N	\N
168	167	Carbonyl Sulfide	4	\N	\N
169	167	Dimethyl Sulfide	4	\N	\N
170	167	Sulfate	4	\N	\N
171	167	Sulfur Dioxide	4	\N	\N
172	167	Sulfur Oxides	4	\N	\N
173	161	Hydrogen Compounds	3	\N	\N
174	173	Hydroperoxy	4	\N	\N
175	173	Hydroxyl	4	\N	\N
176	173	Molecular Hydrogen	4	\N	\N
177	161	Halocarbons and Halogens	3	\N	\N
178	177	Bromine Monoxide	4	\N	\N
179	177	Carbon Tetrachloride	4	\N	\N
180	177	Chlorine Dioxide	4	\N	\N
181	177	Chlorine Monoxide	4	\N	\N
182	177	Chlorine Nitrate	4	\N	\N
183	177	Chlorofluorocarbons	4	\N	\N
184	177	Halocarbons	4	\N	\N
185	177	Halons	4	\N	\N
186	177	Hydrochlorofluorocarbons	4	\N	\N
187	177	Hydrofluorocarbons	4	\N	\N
188	177	Hydrogen Chloride	4	\N	\N
189	177	Hydrogen Fluoride	4	\N	\N
190	177	Hypochlorous Acid	4	\N	\N
191	177	Methyl Bromide	4	\N	\N
192	177	Methyl Chloride	4	\N	\N
193	161	Photochemistry	3	\N	\N
194	193	Photolysis Rates	4	\N	\N
195	161	Nitrogen Compounds	3	\N	\N
196	195	Ammonia	4	\N	\N
197	195	Dinitrogen Pentoxide	4	\N	\N
198	195	Molecular Nitrogen	4	\N	\N
199	195	Nitric Acid	4	\N	\N
200	195	Nitric Oxide	4	\N	\N
201	195	Nitrogen Dioxide	4	\N	\N
202	195	Nitrogen Oxides	4	\N	\N
203	195	Nitrous Oxide	4	\N	\N
204	161	Carbon and Hydrocarbon Compounds	3	\N	\N
205	204	Carbon Dioxide	4	\N	\N
206	204	Carbon Monoxide	4	\N	\N
207	204	Chlorinated Hydrocarbons	4	\N	\N
208	204	Hydrogen Cyanide	4	\N	\N
209	204	Hypochlorous Monoxide	4	\N	\N
210	204	Methane	4	\N	\N
211	204	Methyl Cyanide	4	\N	\N
212	204	Non-methane Hydrocarbons/Volatile Organic Compounds	4	\N	\N
213	\N	Solid Earth	1	\N	\N
214	213	Volcanoes	2	\N	\N
215	214	Magma	3	\N	\N
216	214	Lava	3	\N	\N
217	214	Pyroclastics	3	\N	\N
218	214	Eruption Dynamics	3	\N	\N
219	214	Volcanic Gases	3	\N	\N
220	214	Volcanic Ash/Dust	3	\N	\N
221	213	Natural Resources	2	\N	\N
222	221	Coal	3	\N	\N
223	221	Gas Hydrates	3	\N	\N
224	221	Non-metallic Minerals	3	\N	\N
225	221	Petroleum	3	\N	\N
226	221	Metals	3	\N	\N
227	221	Radioactive Elements	3	\N	\N
228	221	Natural Gas	3	\N	\N
229	213	Geothermal	2	\N	\N
230	229	Geothermal Energy	3	\N	\N
231	229	Geothermal Temperature	3	\N	\N
232	213	Seismology	2	\N	\N
233	232	Earthquake Occurrences	3	\N	\N
234	232	Seismic Surface Waves	3	\N	\N
235	232	Seismic Profile	3	\N	\N
236	232	Earthquake Dynamics	3	\N	\N
237	232	Earthquake Predictions	3	\N	\N
238	232	Seismic Body Waves	3	\N	\N
239	\N	Land Surface	1	\N	\N
240	239	Topography	2	\N	\N
241	240	Topographic Effects	3	\N	\N
242	240	Surface Roughness	3	\N	\N
243	240	Topographical Relief	3	\N	\N
244	240	Contours	3	\N	\N
245	240	Terrain Elevation	3	\N	\N
246	240	Landforms	3	\N	\N
247	239	Land Use/Land Cover	2	\N	\N
248	247	Land Use Classes	3	\N	\N
249	247	Land Productivity	3	\N	\N
250	247	Land Cover	3	\N	\N
251	247	Land Resources	3	\N	\N
252	239	Land Temperature	2	\N	\N
253	252	Land Heat Capacity	3	\N	\N
254	252	Skin Temperature	3	\N	\N
255	252	Land Surface Temperature	3	\N	\N
256	239	Soils	2	\N	\N
257	256	Nitrogen	3	\N	\N
258	256	Thermal Conductivity	3	\N	\N
259	256	Soil Erosion	3	\N	\N
260	256	Magnesium	3	\N	\N
261	256	Soil Plasticity	3	\N	\N
262	256	Soil Mechanics	3	\N	\N
263	256	Soil Fertility	3	\N	\N
264	256	Microflora	3	\N	\N
265	256	Soil Horizons/Profile	3	\N	\N
266	256	Micronutrients/Trace Elements	3	\N	\N
267	256	Soil Water Holding Capacity	3	\N	\N
268	256	Hydraulic Conductivity	3	\N	\N
269	256	Soil Respiration	3	\N	\N
270	256	Soil Productivity	3	\N	\N
271	256	Heavy Metals	3	\N	\N
272	256	Soil Color	3	\N	\N
273	256	Macrofauna	3	\N	\N
274	256	Soil Infiltration	3	\N	\N
275	256	Potassium	3	\N	\N
276	256	Soil Temperature	3	\N	\N
277	256	Soil pH	3	\N	\N
278	256	Soil Porosity	3	\N	\N
279	256	Soil Gas/Air	3	\N	\N
280	256	Soil Structure	3	\N	\N
281	256	Soil Moisture/Water Content	3	\N	\N
282	256	Soil Depth	3	\N	\N
283	256	Soil Absorption	3	\N	\N
284	256	Soil Rooting Depth	3	\N	\N
285	256	Soil Heat Budget	3	\N	\N
286	256	Soil Salinity/Soil Sodicity	3	\N	\N
287	256	Organic Matter	3	\N	\N
288	256	Carbon	3	\N	\N
289	256	Soil Classification	3	\N	\N
290	256	Electrical Conductivity	3	\N	\N
291	256	Cation Exchange Capacity	3	\N	\N
292	256	Soil Bulk Density	3	\N	\N
293	256	Microfauna	3	\N	\N
294	256	Soil Impedance	3	\N	\N
295	256	Soil Consistence	3	\N	\N
296	256	Permafrost	3	\N	\N
297	256	Phosphorus	3	\N	\N
298	256	Soil Compaction	3	\N	\N
299	256	Soil Texture	3	\N	\N
300	256	Calcium	3	\N	\N
301	256	Denitrification Rate	3	\N	\N
302	256	Soil Chemistry	3	\N	\N
303	256	Sulfur	3	\N	\N
304	239	Landscape	2	\N	\N
305	304	Reforestation	3	\N	\N
306	304	Landscape Processes	3	\N	\N
307	304	Reclamation/Revegetation/Restoration	3	\N	\N
308	304	Landscape Ecology	3	\N	\N
309	304	Landscape Management	3	\N	\N
310	304	Landscape Patterns	3	\N	\N
311	239	Erosion/Sedimentation	2	\N	\N
312	311	Sediments	3	\N	\N
313	311	Sedimentation	3	\N	\N
314	311	Weathering	3	\N	\N
315	311	Sediment Composition	3	\N	\N
316	311	Stratigraphic Sequence	3	\N	\N
317	311	Suspended Solids	3	\N	\N
318	311	Degradation	3	\N	\N
319	311	Sediment Chemistry	3	\N	\N
320	311	Erosion	3	\N	\N
321	311	Entrainment	3	\N	\N
322	311	Sediment Transport	3	\N	\N
323	311	Landslides	3	\N	\N
324	239	Surface Radiative Properties	2	\N	\N
325	324	Thermal Properties	3	\N	\N
326	324	Albedo	3	\N	\N
327	324	Anisotropy	3	\N	\N
328	324	Emissivity	3	\N	\N
329	\N	Terrestrial Hydrosphere	1	\N	\N
330	329	Surface Water	2	\N	\N
331	330	Runoff	3	\N	\N
332	330	Inundation	3	\N	\N
333	330	Wetlands	3	\N	\N
334	330	Surface Water Chemistry	3	\N	\N
335	330	Water Pressure	3	\N	\N
336	330	Watershed Characteristics	3	\N	\N
337	330	Water Depth	3	\N	\N
338	330	Water Channels	3	\N	\N
339	330	Hydroperiod	3	\N	\N
340	330	Floods	3	\N	\N
341	330	Lakes	3	\N	\N
342	330	Stage Height	3	\N	\N
343	330	Rivers/Streams	3	\N	\N
344	330	Aquifer Recharge	3	\N	\N
345	330	Discharge/Flow	3	\N	\N
346	330	Total Surface Water	3	\N	\N
347	330	Drainage	3	\N	\N
348	330	Water Yield	3	\N	\N
349	330	Hydropattern	3	\N	\N
350	329	Ground Water	2	\N	\N
351	350	Ground Water Discharge/Flow	3	\N	\N
352	350	Dispersion	3	\N	\N
353	350	Water Table	3	\N	\N
354	350	Land Subsidence	3	\N	\N
355	350	Groundwater Chemistry	3	\N	\N
356	350	Infiltration	3	\N	\N
357	350	Saltwater Intrusion	3	\N	\N
358	350	Drainage	3	\N	\N
359	350	Springs	3	\N	\N
360	350	Percolation	3	\N	\N
361	350	Aquifers	3	\N	\N
362	329	Water Quality/Water Chemistry	2	\N	\N
363	362	Carbon Dioxide	3	\N	\N
364	362	Turbidity	3	\N	\N
365	362	Contaminants	3	\N	\N
366	362	Dissolved Gases	3	\N	\N
367	362	Water Potability	3	\N	\N
368	362	Radioisotopes	3	\N	\N
369	362	Stable Isotopes	3	\N	\N
370	362	Oxygen	3	\N	\N
371	362	Trace Metals	3	\N	\N
372	362	Water Ion Concentration	3	\N	\N
373	362	Conductivity	3	\N	\N
374	362	Hydrocarbons	3	\N	\N
375	362	Toxic Chemicals	3	\N	\N
376	362	pH	3	\N	\N
377	362	Water Trace Elements	3	\N	\N
378	362	Acid Deposition	3	\N	\N
379	362	Inorganic Matter	3	\N	\N
380	362	Alkalinity	3	\N	\N
381	362	Suspended Solids	3	\N	\N
382	362	Organic Matter	3	\N	\N
383	362	Nitrogen Compounds	3	\N	\N
384	362	Light Transmission	3	\N	\N
385	362	Carcinogens	3	\N	\N
386	362	Nutrients	3	\N	\N
387	362	Benthic Index	3	\N	\N
388	362	Phosphorous Compounds	3	\N	\N
389	362	Water Temperature	3	\N	\N
390	362	Chlorophyll	3	\N	\N
391	362	Dissolved Solids	3	\N	\N
392	\N	Biosphere	1	\N	\N
393	392	Terrestrial Ecosystems	2	\N	\N
394	393	Shrubland/Scrub	3	\N	\N
395	393	Dunes	3	\N	\N
396	393	Karst Landscape	3	\N	\N
397	393	Deserts	3	\N	\N
398	393	Islands	3	\N	\N
399	393	Alpine/Tundra	3	\N	\N
400	393	Grasslands	3	\N	\N
401	393	Savannas	3	\N	\N
402	393	Urban Lands	3	\N	\N
403	393	Agricultural Lands	3	\N	\N
404	393	Forests	3	\N	\N
405	393	Montane Habitats	3	\N	\N
406	393	Beaches	3	\N	\N
407	392	Vegetation	2	\N	\N
408	407	Nitrogen	3	\N	\N
409	407	Reclamation/Revegetation/Restoration	3	\N	\N
410	407	Litter Characteristics	3	\N	\N
411	407	Plant Characteristics	3	\N	\N
412	407	Crown	3	\N	\N
413	407	Macrophytes	3	\N	\N
414	407	Pollen	3	\N	\N
415	407	Biomass	3	\N	\N
416	407	Deciduous Vegetation	3	\N	\N
417	407	Tree Rings	3	\N	\N
418	407	Forest Composition/Vegetation Structure	3	\N	\N
419	407	Vegetation Index	3	\N	\N
420	407	Vegetation Species	3	\N	\N
421	407	Indigenous Vegetation	3	\N	\N
422	407	Exotic Vegetation	3	\N	\N
423	407	Evergreen Vegetation	3	\N	\N
424	407	Vegetation Cover	3	\N	\N
425	407	Photosynthetically Active Radiation	3	\N	\N
426	407	Pigments	3	\N	\N
427	407	Importance Value	3	\N	\N
428	407	Carbon	3	\N	\N
429	407	Afforestation/Reforestation	3	\N	\N
430	407	Reforestation	3	\N	\N
431	407	Canopy Characteristics	3	\N	\N
432	407	Plant Phenology	3	\N	\N
433	407	Leaf Characteristics	3	\N	\N
434	407	Nutrients	3	\N	\N
435	407	Phosphorus	3	\N	\N
436	407	Herbivory	3	\N	\N
437	407	Dominant Species	3	\N	\N
438	407	Chlorophyll	3	\N	\N
439	392	Ecological Dynamics	2	\N	\N
440	439	Ecosystem Functions	3	\N	\N
441	440	Biogeochemical Cycles	4	\N	\N
442	440	Biomass Dynamics	4	\N	\N
443	440	Chemosynthesis	4	\N	\N
444	440	Consumption Rates	4	\N	\N
445	440	Decomposition	4	\N	\N
446	440	Excretion Rates	4	\N	\N
447	440	Food-web Dynamics	4	\N	\N
448	440	Nutrient Cycling	4	\N	\N
449	440	Oxygen Demand	4	\N	\N
450	440	Photosynthesis	4	\N	\N
451	440	Primary Production	4	\N	\N
452	440	Respiration Rate	4	\N	\N
453	440	Secondary Production	4	\N	\N
454	440	Trophic Dynamics	4	\N	\N
455	439	Fire Ecology	3	\N	\N
456	455	Fire Dynamics	4	\N	\N
457	455	Fire Occurrence	4	\N	\N
458	439	Ecotoxicology	3	\N	\N
459	458	Bioavailability	4	\N	\N
460	458	Species Bioaccumulation	4	\N	\N
461	458	Toxicity Levels	4	\N	\N
462	392	Aquatic Ecosystems	2	\N	\N
463	462	Plankton	3	\N	\N
464	463	Phytoplankton	4	\N	\N
465	463	Zooplankton	4	\N	\N
466	462	Pelagic Habitat	3	\N	\N
467	462	Benthic Habitat	3	\N	\N
468	462	Coastal Habitat	3	\N	\N
469	462	Wetlands	3	\N	\N
470	469	Estuarine Wetlands	4	\N	\N
471	469	Lacustrine Wetlands	4	\N	\N
472	469	Marine	4	\N	\N
473	469	Marshes	4	\N	\N
474	469	Palustrine Wetlands	4	\N	\N
475	469	Peatlands	4	\N	\N
476	469	Riparian Wetlands	4	\N	\N
477	469	Swamps	4	\N	\N
478	462	Estuarine Habitat	3	\N	\N
479	462	Demersal Habitat	3	\N	\N
480	462	Marine Habitat	3	\N	\N
481	462	Rivers/Stream Habitat	3	\N	\N
482	462	Reef Habitat	3	\N	\N
483	462	Lakes	3	\N	\N
484	483	Saline Lakes	4	\N	\N
485	\N	Oceans	1	\N	\N
486	485	Aquatic Sciences	2	\N	\N
487	486	Aquaculture	3	\N	\N
488	486	Fisheries	3	\N	\N
489	485	Ocean Temperature	2	\N	\N
490	489	Sea Surface Temperature	3	\N	\N
491	489	Thermocline	3	\N	\N
492	489	Potential Temperature	3	\N	\N
493	489	Ocean Mixed Layer	3	\N	\N
494	489	Water Temperature	3	\N	\N
495	485	Ocean Chemistry	2	\N	\N
496	495	Carbon Dioxide	3	\N	\N
497	495	Nitrogen	3	\N	\N
498	495	Biogeochemical Cycles	3	\N	\N
499	495	Dissolved Gases	3	\N	\N
500	495	Nitrogen Dioxide	3	\N	\N
501	495	Silicate	3	\N	\N
502	495	Radionuclides	3	\N	\N
503	495	Phosphate	3	\N	\N
504	495	Stable Isotopes	3	\N	\N
505	495	Inorganic Carbon	3	\N	\N
506	495	Ocean Tracers	3	\N	\N
507	495	Oxygen	3	\N	\N
508	495	Trace Elements	3	\N	\N
509	495	Radiocarbon	3	\N	\N
510	495	Ammonia	3	\N	\N
511	495	Nitrous Oxide	3	\N	\N
512	495	Organic Carbon	3	\N	\N
513	495	Hydrocarbons	3	\N	\N
514	495	Carbonate	3	\N	\N
515	495	pH	3	\N	\N
516	495	Nitrite	3	\N	\N
517	495	Suspended Solids	3	\N	\N
518	495	Pigments	3	\N	\N
519	518	Chlorophyll	4	\N	\N
520	495	Inorganic Matter	3	\N	\N
521	495	Alkalinity	3	\N	\N
522	495	Biomedical Chemicals	3	\N	\N
523	495	Organic Matter	3	\N	\N
524	495	Carbon	3	\N	\N
525	495	Nitric Acid	3	\N	\N
526	495	Nutrients	3	\N	\N
527	495	Chlorophyll	3	\N	\N
528	495	Dissolved Solids	3	\N	\N
529	495	Nitrate	3	\N	\N
530	495	Marine Geochemistry	3	\N	\N
531	485	Ocean Acoustics	2	\N	\N
532	531	Acoustic Velocity	3	\N	\N
533	531	Acoustic Scattering	3	\N	\N
534	531	Acoustic Attenuation/Transmission	3	\N	\N
535	531	Acoustic Tomography	3	\N	\N
536	531	Acoustic Reflectivity	3	\N	\N
537	531	Acoustic Frequency	3	\N	\N
538	531	Ambient Noise	3	\N	\N
539	485	Ocean Winds	2	\N	\N
540	539	Turbulence	3	\N	\N
541	539	Wind Shear	3	\N	\N
542	539	Vorticity	3	\N	\N
543	539	Surface Winds	3	\N	\N
544	539	Wind Stress	3	\N	\N
545	539	Convergence/Divergence	3	\N	\N
546	539	Wind Chill	3	\N	\N
547	539	Vertical Wind Motion	3	\N	\N
548	485	Ocean Heat Budget	2	\N	\N
549	548	Advection	3	\N	\N
550	548	Heating Rate	3	\N	\N
551	548	Shortwave Radiation	3	\N	\N
552	548	Conduction	3	\N	\N
553	548	Diffusion	3	\N	\N
554	548	Condensation	3	\N	\N
555	548	Heat Flux	3	\N	\N
556	548	Evaporation	3	\N	\N
557	548	Bowen Ratio	3	\N	\N
558	548	Reflectance	3	\N	\N
559	548	Longwave Radiation	3	\N	\N
560	548	Convection	3	\N	\N
561	485	Marine Sediments	2	\N	\N
562	561	Diagenesis	3	\N	\N
563	561	Terrigenous Sediments	3	\N	\N
564	561	Bioturbation	3	\N	\N
565	561	Sedimentation	3	\N	\N
566	561	Biogenic Sediments	3	\N	\N
567	561	Hydrogenous Sediments	3	\N	\N
568	561	Sedimentary Textures	3	\N	\N
569	561	Suspended Solids	3	\N	\N
570	561	Sediment Chemistry	3	\N	\N
571	561	Particle Flux	3	\N	\N
572	561	Sedimentary Structures	3	\N	\N
573	561	Sediment Composition	3	\N	\N
574	561	Stratigraphic Sequence	3	\N	\N
575	561	Geotechnical Properties	3	\N	\N
576	561	Sediment Transport	3	\N	\N
577	485	Ocean Optics	2	\N	\N
578	577	Turbidity	3	\N	\N
579	577	Photosynthetically Active Radiation	3	\N	\N
580	577	Gelbstoff	3	\N	\N
581	577	Absorption	3	\N	\N
582	577	Attenuation/Transmission	3	\N	\N
583	577	Aphotic/Photic Zone	3	\N	\N
584	577	Water-leaving Radiance	3	\N	\N
585	577	Scattering	3	\N	\N
586	577	Radiance	3	\N	\N
587	577	Ocean Color	3	\N	\N
588	577	Optical Depth	3	\N	\N
589	577	Bioluminescence	3	\N	\N
590	577	Extinction Coefficients	3	\N	\N
591	577	Irradiance	3	\N	\N
592	577	Fluorescence	3	\N	\N
593	577	Reflectance	3	\N	\N
594	577	Secchi Depth	3	\N	\N
595	485	Tides	2	\N	\N
596	595	Storm Surge	3	\N	\N
597	595	Tidal Currents	3	\N	\N
598	595	Tidal Range	3	\N	\N
599	595	Tidal Height	3	\N	\N
600	595	Tidal Components	3	\N	\N
601	485	Water Quality	2	\N	\N
602	601	Ocean Contaminants	3	\N	\N
603	485	Marine Environment Monitoring	2	\N	\N
604	603	Marine Obstructions	3	\N	\N
605	485	Sea Surface Topography	2	\N	\N
606	605	Sea Surface Slope	3	\N	\N
607	605	Sea Surface Height	3	\N	\N
608	485	Ocean Pressure	2	\N	\N
609	608	Water Pressure	3	\N	\N
610	608	Sea Level Pressure	3	\N	\N
611	485	Ocean Waves	2	\N	\N
612	611	Wave Types	3	\N	\N
613	611	Topographic Waves	3	\N	\N
614	611	Wave Spectra	3	\N	\N
615	611	Wave Length	3	\N	\N
616	611	Tsunamis	3	\N	\N
617	611	Surf Beat	3	\N	\N
618	611	Storm Surge	3	\N	\N
619	611	Wave Speed/Direction	3	\N	\N
620	611	Rossby/Planetary Waves	3	\N	\N
621	611	Significant Wave Height	3	\N	\N
622	611	Wave Height	3	\N	\N
623	611	Swells	3	\N	\N
624	611	Sea State	3	\N	\N
625	611	Wave Period	3	\N	\N
626	611	Wave Fetch	3	\N	\N
627	611	Wind Waves	3	\N	\N
628	611	Seiches	3	\N	\N
629	611	Gravity Waves	3	\N	\N
630	611	Wave Frequency	3	\N	\N
631	485	Salinity/Density	2	\N	\N
632	631	Halocline	3	\N	\N
633	631	Pycnocline	3	\N	\N
634	631	Potential Density	3	\N	\N
635	631	Density	3	\N	\N
636	631	Conductivity	3	\N	\N
637	631	Desalinization	3	\N	\N
638	631	Salinity	3	\N	\N
639	631	Salt Transport	3	\N	\N
640	485	Marine Volcanism	2	\N	\N
641	640	Rift Valleys	3	\N	\N
642	640	Benthic Heat Flow	3	\N	\N
643	640	Island Arcs	3	\N	\N
644	640	Hydrothermal Vents	3	\N	\N
645	640	Mid-ocean Ridges	3	\N	\N
646	485	Ocean Circulation	2	\N	\N
647	646	Turbulence	3	\N	\N
648	646	Diffusion	3	\N	\N
649	646	Ocean Mixed Layer	3	\N	\N
650	646	Vorticity	3	\N	\N
651	646	Buoy Position	3	\N	\N
652	646	Fronts	3	\N	\N
653	646	Eddies	3	\N	\N
654	646	Upwelling/Downwelling	3	\N	\N
655	646	Advection	3	\N	\N
656	646	Ocean Currents	3	\N	\N
657	646	Water Masses	3	\N	\N
658	646	Thermohaline Circulation	3	\N	\N
659	646	Gyres	3	\N	\N
660	646	Wind-driven Circulation	3	\N	\N
661	646	Fresh Water Flux	3	\N	\N
662	646	Convection	3	\N	\N
663	485	Bathymetry/Seafloor Topography	2	\N	\N
664	663	Fracture Zones	3	\N	\N
665	663	Continental Margins	3	\N	\N
666	663	Seamounts	3	\N	\N
667	663	Bathymetry	3	\N	\N
668	663	Trenches	3	\N	\N
669	663	Ocean Plateaus/Ridges	3	\N	\N
670	663	Water Depth	3	\N	\N
671	663	Abyssal Hills/Plains	3	\N	\N
672	663	Submarine Canyons	3	\N	\N
673	485	Coastal Processes	2	\N	\N
674	673	Mangroves	3	\N	\N
675	673	Dunes	3	\N	\N
676	673	Saltwater Intrusion	3	\N	\N
677	673	Intertidal Zone	3	\N	\N
678	673	Sea Level Rise	3	\N	\N
679	673	Fjords	3	\N	\N
680	673	Shoals	3	\N	\N
681	673	Erosion	3	\N	\N
682	673	Longshore Currents	3	\N	\N
683	673	Barrier Islands	3	\N	\N
684	673	Shorelines	3	\N	\N
685	673	Marshes	3	\N	\N
686	673	Estuaries	3	\N	\N
687	673	Inlets	3	\N	\N
688	673	Local Subsidence Trends	3	\N	\N
689	673	Beaches	3	\N	\N
690	673	Coastal Elevation	3	\N	\N
691	673	Sediment Transport	3	\N	\N
692	673	Shoreline Displacement	3	\N	\N
693	673	Sea Surface Height	3	\N	\N
694	673	Sedimentation	3	\N	\N
695	673	Storm Surge	3	\N	\N
696	673	Coral Reefs	3	\N	\N
697	673	Lagoons	3	\N	\N
698	673	Deltas	3	\N	\N
699	673	Rocky Coasts	3	\N	\N
700	673	Tidal Height	3	\N	\N
701	\N	Spectral/Engineering	1	\N	\N
702	701	Infrared Wavelengths	2	\N	\N
703	702	Thermal Infrared	3	\N	\N
704	702	Infrared Flux	3	\N	\N
705	702	Infrared Imagery	3	\N	\N
706	702	Sensor Counts	3	\N	\N
707	702	Brightness Temperature	3	\N	\N
708	702	Reflected Infrared	3	\N	\N
709	702	Infrared Radiance	3	\N	\N
710	701	X-ray	2	\N	\N
711	710	X-ray Flux	3	\N	\N
712	701	Lidar	2	\N	\N
713	712	Lidar Depolarization Ratio	3	\N	\N
714	712	Lidar Backscatter	3	\N	\N
715	701	Sensor Characteristics	2	\N	\N
716	715	Sink Temperature	3	\N	\N
717	715	Electrical Properties	3	\N	\N
718	715	Total Pressure	3	\N	\N
719	715	Dome Temperature	3	\N	\N
720	715	Viewing Geometry	3	\N	\N
721	715	Thermal Properties	3	\N	\N
722	715	Phase and Amplitude	3	\N	\N
723	715	Ultraviolet Sensor Temperature	3	\N	\N
724	715	Total Temperature	3	\N	\N
725	701	Platform Characteristics	2	\N	\N
726	725	Data Synchronization Time	3	\N	\N
727	725	Flight Data Logs	3	\N	\N
728	725	Attitude Characteristics	3	\N	\N
729	725	Line Of Sight Velocity	3	\N	\N
730	725	Viewing Geometry	3	\N	\N
731	725	Orbital Characteristics	3	\N	\N
732	725	Airspeed/Ground Speed	3	\N	\N
733	701	Microwave	2	\N	\N
734	733	Microwave Imagery	3	\N	\N
735	733	Microwave Radiance	3	\N	\N
736	733	Sensor Counts	3	\N	\N
737	733	Brightness Temperature	3	\N	\N
738	733	Antenna Temperature	3	\N	\N
739	701	Gamma Ray	2	\N	\N
740	739	Gamma Ray Flux	3	\N	\N
741	701	Radar	2	\N	\N
742	741	Radar Reflectivity	3	\N	\N
743	741	Return Power	3	\N	\N
744	741	Radar Backscatter	3	\N	\N
745	741	Sensor Counts	3	\N	\N
746	741	Doppler Velocity	3	\N	\N
747	741	Sigma Naught	3	\N	\N
748	741	Radar Cross-section	3	\N	\N
749	741	Radar Imagery	3	\N	\N
750	701	Visible Wavelengths	2	\N	\N
751	750	Visible Radiance	3	\N	\N
752	750	Visible Imagery	3	\N	\N
753	750	Visible Flux	3	\N	\N
754	750	Sensor Counts	3	\N	\N
755	701	Ultraviolet Wavelengths	2	\N	\N
756	755	Ultraviolet Flux	3	\N	\N
757	755	Sensor Counts	3	\N	\N
758	755	Ultraviolet Radiance	3	\N	\N
759	701	Radio Wave	2	\N	\N
760	759	Radio Wave Flux	3	\N	\N
761	\N	Human Dimensions	1	\N	\N
762	761	Land Use/Land Cover	2	\N	\N
763	762	Land Tenure	3	\N	\N
764	762	Land Management	3	\N	\N
765	761	Human Health	2	\N	\N
766	765	Diseases/Epidemics	3	\N	\N
767	765	Vital Statistics	3	\N	\N
768	765	Anatomical Parameters	3	\N	\N
769	765	Psychological Parameters	3	\N	\N
770	765	Physiological Parameters	3	\N	\N
771	765	Radiation Exposure	3	\N	\N
772	765	Public Health	3	\N	\N
773	761	Economic Resources	2	\N	\N
774	773	Oil/Gas Production	3	\N	\N
775	773	Agricultural Economics	3	\N	\N
776	761	Attitudes,preferences,behavior	2	\N	\N
777	776	Consumer Behavior	3	\N	\N
778	776	Social Behavior	3	\N	\N
779	776	Recreation	3	\N	\N
780	761	Natural Hazards	2	\N	\N
781	780	Meteorological Hazards	3	\N	\N
782	780	Biological Hazards	3	\N	\N
783	780	Hydrological Hazards	3	\N	\N
784	780	Fires	3	\N	\N
785	780	Geological Hazards	3	\N	\N
786	761	Population	2	\N	\N
787	786	Population Distribution	3	\N	\N
788	786	Population Size	3	\N	\N
789	761	Environmental Impacts	2	\N	\N
790	789	Mine Drainage	3	\N	\N
791	789	Fossil Fuel Burning	3	\N	\N
792	789	Contaminants	3	\N	\N
793	789	Civil Disturbance	3	\N	\N
794	789	Conservation	3	\N	\N
795	789	Biomass Burning	3	\N	\N
796	789	Industrial Emissions	3	\N	\N
797	789	Acid Deposition	3	\N	\N
798	789	Environmental Assessments	3	\N	\N
799	789	Gas Flaring	3	\N	\N
800	789	Nuclear Radiation	3	\N	\N
801	789	Sewage	3	\N	\N
802	789	Heavy Metals	3	\N	\N
803	789	Biochemical Release	3	\N	\N
804	789	Gas Explosions/Leaks	3	\N	\N
805	789	Chemical Spills	3	\N	\N
806	789	Industrialization	3	\N	\N
807	789	Urbanization	3	\N	\N
808	789	Oil Spill	3	\N	\N
809	789	Water Management	3	\N	\N
810	789	Agricultural Expansion	3	\N	\N
811	761	Boundaries	2	\N	\N
812	811	Boundary Surveys	3	\N	\N
813	811	Political Divisions	3	\N	\N
814	811	Administrative Divisions	3	\N	\N
815	761	Habitat Conversion/Fragmentation	2	\N	\N
816	815	Deforestation	3	\N	\N
817	815	Reforestation	3	\N	\N
818	815	Reclamation/Revegetation/Restoration	3	\N	\N
819	815	Irrigation	3	\N	\N
820	815	Eutrophication	3	\N	\N
821	815	Desertification	3	\N	\N
822	761	Infrastructure	2	\N	\N
823	822	Cultural Features	3	\N	\N
824	822	Buildings	3	\N	\N
825	822	Pipelines	3	\N	\N
826	822	Electricity	3	\N	\N
827	822	Transportation	3	\N	\N
828	822	Communications	3	\N	\N
829	\N	Cryosphere	1	\N	\N
830	829	Snow/Ice	2	\N	\N
831	830	Snow Energy Balance	3	\N	\N
832	830	Snow Density	3	\N	\N
833	830	Avalanche	3	\N	\N
834	830	Ice Extent	3	\N	\N
835	830	Ice Motion	3	\N	\N
836	830	Snow/Ice Temperature	3	\N	\N
837	830	Snow/Ice Chemistry	3	\N	\N
838	830	Snow Melt	3	\N	\N
839	830	Ice Velocity	3	\N	\N
840	830	Ice Depth/Thickness	3	\N	\N
841	830	Ice Growth/Melt	3	\N	\N
842	830	Snow Stratigraphy	3	\N	\N
843	830	Depth Hoar	3	\N	\N
844	830	Snow Cover	3	\N	\N
845	830	Snow Facies	3	\N	\N
846	830	Snow Depth	3	\N	\N
847	830	Permafrost	3	\N	\N
848	830	Snow Water Equivalent	3	\N	\N
849	830	River Ice	3	\N	\N
850	830	Frost	3	\N	\N
851	830	Albedo	3	\N	\N
852	830	Lake Ice	3	\N	\N
853	830	Whiteout	3	\N	\N
854	830	Freeze/Thaw	3	\N	\N
855	829	Glaciers/Ice Sheets	2	\N	\N
856	855	Ice Sheets	3	\N	\N
857	855	Firn	3	\N	\N
858	855	Glacier Topography/Ice Sheet Topography	3	\N	\N
859	855	Glaciers	3	\N	\N
860	855	Icebergs	3	\N	\N
861	855	Glacier Thickness/Ice Sheet Thickness	3	\N	\N
862	855	Glacier Elevation/Ice Sheet Elevation	3	\N	\N
863	855	Glacier Facies	3	\N	\N
864	855	Glacier Mass Balance/Ice Sheet Mass Balance	3	\N	\N
865	855	Ablation Zones/Accumulation Zones	3	\N	\N
866	855	Glacier Motion/Ice Sheet Motion	3	\N	\N
867	829	Sea Ice	2	\N	\N
868	867	Ice Floes	3	\N	\N
869	867	Sea Ice Motion	3	\N	\N
870	867	Ice Extent	3	\N	\N
871	867	Snow Melt	3	\N	\N
872	867	Sea Ice Elevation	3	\N	\N
873	867	Leads	3	\N	\N
874	867	Heat Flux	3	\N	\N
875	867	Ice Edges	3	\N	\N
876	867	Icebergs	3	\N	\N
877	867	Salinity	3	\N	\N
878	867	Ice Depth/Thickness	3	\N	\N
879	867	Sea Ice Concentration	3	\N	\N
880	867	Ice Growth/Melt	3	\N	\N
881	867	Sea Ice Age	3	\N	\N
882	867	Ice Types	3	\N	\N
883	867	Polynyas	3	\N	\N
884	867	Ice Temperature	3	\N	\N
885	867	Snow Depth	3	\N	\N
886	867	Ice Deformation	3	\N	\N
887	867	Pack Ice	3	\N	\N
888	867	Reflectance	3	\N	\N
889	867	Ice Roughness	3	\N	\N
890	867	Isotopes	3	\N	\N
891	829	Frozen Ground	2	\N	\N
892	891	Cryosols	3	\N	\N
893	891	Seasonally Frozen Ground	3	\N	\N
894	891	Active Layer	3	\N	\N
895	891	Permafrost	3	\N	\N
896	891	Periglacial Processes	3	\N	\N
897	891	Ground Ice	3	\N	\N
898	891	Soil Temperature	3	\N	\N
899	891	Rock Glaciers	3	\N	\N
900	891	Talik	3	\N	\N
901	\N	Climate Indicators	1	\N	\N
902	901	Teleconnections	2	\N	\N
903	902	Northern Oscillation Index	3	\N	\N
904	902	East Atlantic Pattern	3	\N	\N
905	902	Tropical/Northern Hemisphere Pattern	3	\N	\N
906	902	El Nino Southern Oscillation	3	\N	\N
907	902	Globally Integrated Angular Momentum	3	\N	\N
908	902	Wind and Circulation Indices	3	\N	\N
909	902	Pacific Decadal Oscillation	3	\N	\N
910	902	Madden-Julian Oscillation	3	\N	\N
911	902	East Atlantic Jet Pattern	3	\N	\N
912	902	Quasi-biennial Oscillation	3	\N	\N
913	902	West Pacific Index	3	\N	\N
914	902	Arctic Oscillation	3	\N	\N
915	902	Bivariate Enso Timeseries Index	3	\N	\N
916	902	Eastern Pacific Oscillation	3	\N	\N
917	902	Equatorial Pacific Meridional Wind Anomaly Index	3	\N	\N
918	902	Equatorial Pacific Zonal Wind Anomaly Index	3	\N	\N
919	902	Pacific/North American (PNA) Pattern	3	\N	\N
920	902	North Pacific Oscillation	3	\N	\N
921	902	Antarctic Oscillation	3	\N	\N
922	902	Blocking Index	3	\N	\N
923	902	North Atlantic Oscillation	3	\N	\N
924	901	Drought/Precipitation Indices	2	\N	\N
925	924	Palmer Drought Crop Moisture Index	3	\N	\N
926	924	Satellite Soil Moisture Index	3	\N	\N
927	924	Enso Precipitation Index	3	\N	\N
928	924	Crop Moisture Index	3	\N	\N
929	924	Central Indian Precipitation Index	3	\N	\N
930	924	Surface Moisture Index	3	\N	\N
931	924	Standardized Precipitation Index	3	\N	\N
932	924	Fire Weather Index	3	\N	\N
933	924	Palmer Drought Severity Index	3	\N	\N
934	924	Forest Fire Danger Index	3	\N	\N
935	901	Air Temperature Indices	2	\N	\N
936	935	Common Sense Climate Index	3	\N	\N
937	901	Humidity Indices	2	\N	\N
938	937	Humidity Index	3	\N	\N
939	901	Ocean/SST Indices	2	\N	\N
940	939	North Tropical Atlantic Index	3	\N	\N
941	939	Tropical South Atlantic Index	3	\N	\N
942	939	Nino 4 Index	3	\N	\N
943	939	Nino3.4 Index	3	\N	\N
944	939	Tropical North Atlantic Index	3	\N	\N
945	939	Nino 3 Index	3	\N	\N
946	939	Trans-nino Index	3	\N	\N
947	939	Nino1+2 Index	3	\N	\N
948	939	Caribbean Index	3	\N	\N
949	939	Oceanic Nino Index	3	\N	\N
950	939	Atlantic Multidecadal Oscillation	3	\N	\N
951	939	Western Hemisphere Warm Pool	3	\N	\N
952	939	Kaplan SST Index	3	\N	\N
953	68	Precipitation Amount	3	\N	\N
954	52	Wind Speed	3	\N	\N
955	52	Wind Direction	3	\N	\N
956	2	Sunshine	3	\N	\N
957	2	Solar Radiation	3	\N	\N
\.


--
-- Data for Name: organism; Type: TABLE DATA; Schema: public; Owner: wwwadm
--

COPY organism (org_id, org_sname, org_fname, org_url) FROM stdin;
1	SEDOO	Service Données de l'OMP, UMS 831	http://sedoo.fr
2	IDAEA-CSIC		\N
3	GESA-ENDESA		\N
4	UMH-Elche		\N
43	TBD	To Be Determined	\N
44	CMM/CNRM (Météo-France)	CENTRE DE METEOROLOGIE MARINE	\N
7	CNES		\N
8	RSLab / UPC	Remote Sensing Lab. / Universitat Politècnica de Catalunya	\N
9	IPSL/CNRS	Institut Pierre-Simon Laplace	http://www.ipsl.fr
45	LTHE	Laboratoire d'étude des Transferts en Hydrologie et Environnement	\N
11	ULCO	Université du Littoral Côte d'Opale	\N
12	Cemagref UR HHLY		\N
13	UM - Murcia	University of Murcia	\N
16	LSCE	Laboratoire des Sciences du Climat et de l'Environnement	http://www.lsce.ipsl.fr/
17	LA (UPS/CNRS)	Laboratoire d'Aérologie (Université Paul Sabatier Toulouse III et CNRS)	http://www.aero.obs-mip.fr/
46	Met Office	Met Office	http://www.metoffice.gov.uk/
25	ARPA-SIMC	HydroMeteoClimate Service of Emilia-Romagna, Bologna, Italy	http://www.arpa.emr.it/sim/
26	ISAC CNR		\N
27	IFSTTAR	Institut Francais des Sciences et Technology des Transports de l'Aménagement et des Réseaux	www.ifsttar.fr
47	Universität Hohenheim	Universität Hohenheim	\N
49	ENEA	Italian National agency for new technologies, Energy and sustainable economic development	http://www.enea.it/it
51	IMK	Institut für Meteorologie und Klimaforschung (IMK)	http://www.imk.kit.edu/
52	Dipartimento Protezione Civile	Dipartimento Protezione Civile, Italy	\N
53	Ecole des Mines d’Alès	Ecole des Mines d’Alès	\N
54	OA - Cagliari	Osservatorio Astronomico Cagliari	http://www.oa-cagliari.inaf.it/
55	IERSD	Institute of Environmental Research and Sustainable Development	http://www.meteo.noa.gr/
56	INRA PACA	Inra en région Provence-Alpes-Côte d'Azur	http://www.paca.inra.fr/
57	UM2	Université Montpellier 2	http://www.univ-montp2.fr/
58	EE NMT	Electrical Engineering - New Mexico Tech	http://www.ee.nmt.edu/
59	LAREG - IGN	Laboratoire de Recherche en Geodesie / Institut Geographique National	http://recherche.ign.fr/labos/lareg/page.php
60	IMAA	Institute of Methodologies for Environmental Analysis	http://www.imaa.cnr.it/
61	KIT	Karlsruher Institut für Technologie	http://www.kit.edu/index.php
62	ARPA	Agenzia Regionale per la Protezione dell'Ambiente del Piemonte	www.arpa.piemonte.it/
63	OVE	Österreichischer Verband für Elektrotechnik	http://www.ove.at/index.html
64	Météorage	Météorage	http://www.meteorage.com/
65	DLR	Deutschen Zentrums für Luft- und Raumfahrt	www.dlr.de
10	Università del Salento	Università del Salento, Lecce, Italy	\N
18	METEO-FRANCE	METEO-FRANCE	\N
15	University of Washington	University of Washington	\N
28	MetORBS	Met European Research Observatory	\N
66	ONERA	Office national d'études et recherches aérospatiales	http://www.onera.fr
67	CEA	Commissariat à l’énergie atomique	http://www.cea.fr
68	Univ. Corse	Università di Corsica Pasquale Paoli	www.univ-corse.fr/
29	Geography Dept. HUJI	Geography Department at the Hebrew University of Jerusalem, Israel	\N
31	Meteorological Service, Israel		\N
32	Israeli Hydrological Service	Israeli Hydrological Service, Israeli Water Authority, Israel	\N
34	LISA	Laboratoire Interuniversitaire des Systèmes Atmosphériques	\N
36	LOA (CNRS)	Lab. Optique Atmosphérique	\N
69	nowcast	nowcast GmbH - high precision lightning detection	https://www.nowcast.de
70	SHOM	Service Hydrographique et Océanographique de la Marine	www.shom.fr/
50	INOE	NATIONAL INSTITUTE of RESEARCH and DEVELOPMENT for OPTOELECTRONICS	http://www.inoe.ro/
41	LSEET (CNRS)		\N
73	GAME/CNRM	GAME/CNRM (Météo France)	\N
75	AEMET	Agencia Estatal de Meteorología	http://www.aemet.es
76	LMD/CNRS	Laboratoire de Météorologie Dynamique	http://www.lmd.jussieu.fr/
80	Centro Funzionale d'Abruzzo	Centro Funzionale d'Abruzzo	\N
81	Centro Funzionale Multirischi	Agenzia Regionale per la Protezione dell'Ambiente della Calabria	\N
85	CNRS 7300 ESPACE		\N
88	EPFL-LTE 	École Polytechnique Fédérale de Lausanne. LTE - Laboratoire de télédétection environnementale	http://lte.epfl.ch/
90	LaMMA	Consorzio LaMMA - CNR	www.lamma.rete.toscana.it
91	EMD	Ecole des Mines de Douai	\N
92	Meteorolgical Service of Catalonia		\N
94	Meteotrentino		\N
96	AMU	Aix-Marseille Université	http://www.univ-amu.fr
97	UPVD	Université de Perpignan	http://www.univ-perp.fr/
98	LATMOS		\N
99	TU Delft	Delft University of Technology	grs.citg.tudelft.nl
100	Campania Region		\N
101	Italian Met Center-CNMCA	Centro Nazionale di Meteorologia e Climatologia Aeronautica	www.meteoam.it
106	CMHS	Croatian Meteorological and Hydrological Service	meteo.hr
110	University of Barcelona		\N
103	LaMP/OPGC	Laboratoire de Météorologie Physique	http://wwwobs.univ-bpclermont.fr/atmos
33	CNRS/UPMC-Villefranche-sur-Mer	Observatoire Oceanologique de Villefranche-sur-Mer	http://www.obs-vlfr.fr/
86	ARPAL	Agenzia Regionale per la Protezione dell'Ambiente Ligure	\N
79	WMO	World Meteorological Organization	http://www.wmo.int
115	NASA		\N
131	CETEMPS	Center of Excellence CETEMPS, Univ. of L'Aquila, Italy	cetemps.aquila.infn.it
133	HIMET	HIMET srl, L'Aquila, Italy	www.himet.it
134	INERIS		\N
135	CNRS-UPVD, Perpignan		\N
136	DT/INSU		\N
137	LOCEAN		\N
138	LPC2E	Laboratoire de physique et chimie de l'environnement et de l'Espace	http://lpce.cnrs-orleans.fr
139	Irstea	 Institut national de recherche en sciences et technologies pour l'environnement et l'agriculture 	http://www.irstea.fr/
141	MERCATOR-ocean		http://www.mercator-ocean.fr/eng
142	PREVIMER		http://www.previmer.org/en
143	CNR-ISMAR		\N
145	CNRS-LERMA		http://lerma.obspm.fr/
146	HSM	HydroSciences Montpellier	\N
148	Qualitair Corse		http://www.qualitaircorse.org/
149	UNIBAS-Scuola di Ingegneria		\N
150	SAFIRE	Service des avions français instrumentés pour la recherche en environnement (UMS 2859)	http://www.safire.fr/web/
151	CMS/Météo-France	Centre de Météorologie Spatiale / Météo-France	http://www.meteo-spatiale.fr/src/accueil.php
156	NOAA-NSSL		\N
157	ISPRA	Istituto Superiore per la Protezione e la Ricerca Ambientale	http://www.isprambiente.gov.it
159	University of Frankfurt		\N
160	IMEDEA – UIB/CSIC	Institut Mediterrani d’Estudis Avançats	marine-climate.uib.es
161	CNRS MIO AMU	CNRS Institut Méditerranéen d'Océanographie Aix- MArseille Université	\N
162	CNRS MIO AMU	CNRS Institut Méditerranéen d'Océanographie Aix- MArseille Université	\N
163	UPMC/CNRS-Paris		\N
164	CNR-IMAA	CNR-IMAA	www.imaa.cnr.it
165	BSC-CNS		\N
166	LOMIC		\N
167	INSTM	Institut National des Sciences et Technologies de la Mer	\N
168			\N
169	ESE	Ecologie, Systématique et Evolution -Universite Paris Sud	http://www.ese.u-psud.fr/
170	University of Ferrara		\N
171	LEGOS	Laboratoire d'Etudes en Géophysique et Océanographie Spatiale	http://www.legos.obs-mip.fr
172	CEREGE	Centre de Recherche et d’Enseignement de Géosciences de l’Environnement 	https://www.cerege.fr/
173	UCLM		\N
174	LER Provence Azur Corse, IFREMER	Laboratoires Environnement Ressources Provence Azur Corse 	\N
175	TOTO		\N
195		LTHE	\N
196		World Meteorological Organization (WMO)	\N
197		LTHE	\N
\.


--
-- Data for Name: type_journal; Type: TABLE DATA; Schema: public; Owner: wwwadm
--

COPY type_journal (type_journal_id, type_journal_name) FROM stdin;
1	Abonnement
3	Download
2	New dataset
4	Update dataset
5	Database change
6	Archive
7	Unarchive
\.


--
-- Data for Name: unit; Type: TABLE DATA; Schema: public; Owner: wwwadm
--

COPY unit (unit_id, unit_code, unit_name) FROM stdin;
1	m3/m3	cubic metre per cubic metre
2	m3/s3	cubic metre per cubic second
3	m3/s	cubic metre per second
4	dB	decibel
5	dBZ	decibel Z
6	cd	candela
7	°C	Degree Celsius
8	K	Kelvin
10	g/m-3	gram per cubic metre
11	g/kg	gram per kilogram
12	g/cm^2	gram per square centimetre
13	g/m2	gram per square metre
14	h	hour
16	J/m2	Joule per metre square
17	Km/s	Kelvin metre per second
18	A	ampere
19	kg	kilogram
20	kg/m3	kilogram per cubic metre
21	kg/m2	kilogram per square metre
22	kohm	kilohm
23	m	metre
24	m/s	metre per second
25	m/m2	metre per square metre
26	m/s2	metre per square second
27	µg/m3	microgram per cubic metre
28	µmol/kg	micromole per kilogram
29	µmol/(m2.s)	micromole per square metre per second
30	µs	microsecond
31	µS/m	microsiemens per metre
32	mbar	millibar
33	mg/m3	milligram per cubic metre
34	mm	millimetre
35	mm/h	millimetre per hour
36	mmol/m3	millimole per cubic metre
37	mmol/(m2.s)	millimole per square metre per second
39	ms	millisecond
40	mS	millisiemens
41	mn	minute
42	ng/m3	nanogram per cubic metre
43		no unit
44		octa
45	%	percent
47	‰	per mil
48		pH unit
49	ppbv	parts per billion by volume
50	ppmv	parts per million by volume
51	pptv	parts per trillion by volume
52	P.S.U.	Practical Salinity Unit
53	s	second
54	S/m	Siemens per metre
57	km2	square kilometre
58	m2/s	square metre per second
59	m2/s2	square metre per square second
61	# cm-3	particles per cubic centimetre
62	1/(m·sr)	metre -1 steradian -1
63	1/m	metre -1
80	\N	m2.s-3
65	kg/kg	kilogram per kilogram
64	kg/m2/s	kilogram per square metre per second
74	\N	degrees
75	\N	code
76	Pa	Pascal
77	J/cm2	Joule per centimetre square
79	\N	MJ mm/ha/h/day
82	vol %	volume percent
83	1/ mm m-3	number per millimetre and cubic metre
85	kg/m2/week	kilogram per suqre meter per week
86	g/(sm2)	grams per second per square meter
87	pvu	potential vorticity unit
88	cm	centimetre
81	hPa	hectopascal
90	mV	millivolt
91	mS/cm	millisiemens per centimeter
92	µg.m-2	microgram per square meter
93	mg.l-1	microgram per litre
94	µmoles.L-1	micromole per litre
95	mg.m-2	milligram per square meter
96	kg/m2/day	kilogram per square meter per day
97	\N	dBm
98	µA	microampere
99	°	degree
101	m2/m2	square meter per square meter
102	m/3h	metre 3-hourly accumulated
106	µg/g	microgram per gram
105	dbar	decibar
104	g[C]/m2/day	gram of carbon per square meter per day
60	W/m2	Watt per square metre
110	Mm-1	Megamètre-1
111	W/m2/sr	Watt per square meter per steradian
115	\N	mm-1
116	Mm-1	Inverse megameters
117	\N	µg/l
118	1/s	second-1
119	J/kg	Joule per kg
121	\N	K
122	\N	l/s
123	\N	pers/km2
124	g/l SiO2	g/l SiO2
125	\N	g/l
126	\N	square metre per square second - m2/s2
127	m2/s2	square meter per square second
128	\N	cm-3
129	mPa	milli Pascal
130	knts	knts
131	1/10 hPa	1/10 hPa
132	1/10 m/s	1/10 m/s
133	1/100 °C	1/100 °C
134	1/10 m	1/10 m
135	\N	1/1000°
136	0.01	0.01
137	1/10°C	1/10°C
138	\N	mm
139	\N	dbZ
140	\N	number per cm3
141	\N	Watt per square meter per µm
142	l/min	litre par minute
143	cc/sec	cubic centimeter per second
144	\N	degree and meter
145	\N	cm/s
146	[0-1]	fraction
147	\N	Arbitrary Unit
148	\N	/km
149	NTU	Nephelometric Turbidity Unit
150	\N	micron
\.


--
-- PostgreSQL database dump complete
--

