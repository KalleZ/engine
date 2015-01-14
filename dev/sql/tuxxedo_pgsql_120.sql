--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: engine_option_type; Type: TYPE; Schema: public; Owner: tuxxedo
--

CREATE TYPE engine_option_type AS ENUM (
    's',
    'i',
    'b'
);


ALTER TYPE engine_option_type OWNER TO tuxxedo;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: datastore; Type: TABLE; Schema: public; Owner: tuxxedo; Tablespace: 
--

CREATE TABLE datastore (
    name character varying(128),
    data text
);


ALTER TABLE datastore OWNER TO tuxxedo;

SET default_with_oids = true;

--
-- Name: languages; Type: TABLE; Schema: public; Owner: tuxxedo; Tablespace: 
--

CREATE TABLE languages (
    id integer NOT NULL,
    title character varying(128),
    developer character varying(128),
    isotitle character varying(5),
    isdefault boolean,
    charset character varying(12)
);


ALTER TABLE languages OWNER TO tuxxedo;

--
-- Name: languages_id_seq; Type: SEQUENCE; Schema: public; Owner: tuxxedo
--

CREATE SEQUENCE languages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE languages_id_seq OWNER TO tuxxedo;

--
-- Name: languages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: tuxxedo
--

ALTER SEQUENCE languages_id_seq OWNED BY languages.id;


SET default_with_oids = false;

--
-- Name: optioncategories; Type: TABLE; Schema: public; Owner: tuxxedo; Tablespace: 
--

CREATE TABLE optioncategories (
    name character varying(128) NOT NULL
);


ALTER TABLE optioncategories OWNER TO tuxxedo;

--
-- Name: options; Type: TABLE; Schema: public; Owner: tuxxedo; Tablespace: 
--

CREATE TABLE options (
    option character varying(128) NOT NULL,
    value text NOT NULL,
    defaultvalue text NOT NULL,
    type engine_option_type NOT NULL,
    category character varying(128) NOT NULL
);


ALTER TABLE options OWNER TO tuxxedo;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: tuxxedo; Tablespace: 
--

CREATE TABLE permissions (
    name character varying(255) NOT NULL,
    bits integer NOT NULL
);


ALTER TABLE permissions OWNER TO tuxxedo;

--
-- Name: id; Type: DEFAULT; Schema: public; Owner: tuxxedo
--

ALTER TABLE ONLY languages ALTER COLUMN id SET DEFAULT nextval('languages_id_seq'::regclass);


--
-- Data for Name: datastore; Type: TABLE DATA; Schema: public; Owner: tuxxedo
--

COPY datastore (name, data) FROM stdin;
\.


--
-- Data for Name: languages; Type: TABLE DATA; Schema: public; Owner: tuxxedo
--

COPY languages (id, title, developer, isotitle, isdefault, charset) FROM stdin;
1	English	Tuxxedo Software Development	en	t	UTF-8
\.


--
-- Name: languages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: tuxxedo
--

SELECT pg_catalog.setval('languages_id_seq', 1, true);


--
-- Data for Name: optioncategories; Type: TABLE DATA; Schema: public; Owner: tuxxedo
--

COPY optioncategories (name) FROM stdin;
datetime
language
session
style
\.


--
-- Data for Name: options; Type: TABLE DATA; Schema: public; Owner: tuxxedo
--

COPY options (option, value, defaultvalue, type, category) FROM stdin;
style_id	1	1	i	style
cookie_domain			s	session
cookie_path			s	session
cookie_expires	1800	1800	i	session
cookie_prefix	tuxxedo_	tuxxedo_	s	session
date_format	H:i:s, j/n - Y	H:i:s, j/n - Y	s	datetime
date_timezone	UTC	UTC	s	datetime
date_timezone_offset	0	0	i	datetime
language_id	1	1	i	language
style_storage	database	database	s	style
cookie_secure	0	0	b	session
language_autodetect	0	0	b	language
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: tuxxedo
--

COPY permissions (name, bits) FROM stdin;
administrator	1
\.


--
-- Name: datastore_name_key; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY datastore
    ADD CONSTRAINT datastore_name_key UNIQUE (name);


--
-- Name: languages_pkey; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY languages
    ADD CONSTRAINT languages_pkey PRIMARY KEY (id);


--
-- Name: optioncategories_pkey; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY optioncategories
    ADD CONSTRAINT optioncategories_pkey PRIMARY KEY (name);


--
-- Name: options_option_key; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY options
    ADD CONSTRAINT options_option_key UNIQUE (option);


--
-- Name: permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (name);


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

