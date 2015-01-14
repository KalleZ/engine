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

SET default_with_oids = true;

--
-- Name: phrasegroups; Type: TABLE; Schema: public; Owner: tuxxedo; Tablespace: 
--

CREATE TABLE phrasegroups (
    id integer NOT NULL,
    title character varying(128) NOT NULL,
    languageid integer NOT NULL
);


ALTER TABLE phrasegroups OWNER TO tuxxedo;

--
-- Name: phrasegroups_id_seq; Type: SEQUENCE; Schema: public; Owner: tuxxedo
--

CREATE SEQUENCE phrasegroups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE phrasegroups_id_seq OWNER TO tuxxedo;

--
-- Name: phrasegroups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: tuxxedo
--

ALTER SEQUENCE phrasegroups_id_seq OWNED BY phrasegroups.id;


--
-- Name: phrases; Type: TABLE; Schema: public; Owner: tuxxedo; Tablespace: 
--

CREATE TABLE phrases (
    id integer NOT NULL,
    title character varying(128) NOT NULL,
    translation text NOT NULL,
    defaulttranslation text NOT NULL,
    changed boolean DEFAULT false NOT NULL,
    languageid integer NOT NULL,
    phrasegroup character varying(128) NOT NULL
);


ALTER TABLE phrases OWNER TO tuxxedo;

--
-- Name: phrases_id_seq; Type: SEQUENCE; Schema: public; Owner: tuxxedo
--

CREATE SEQUENCE phrases_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE phrases_id_seq OWNER TO tuxxedo;

--
-- Name: phrases_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: tuxxedo
--

ALTER SEQUENCE phrases_id_seq OWNED BY phrases.id;


SET default_with_oids = false;

--
-- Name: sessions; Type: TABLE; Schema: public; Owner: tuxxedo; Tablespace: 
--

CREATE TABLE sessions (
    sessionid character varying(32) NOT NULL,
    userid integer NOT NULL,
    location text NOT NULL,
    useragent character varying(255) NOT NULL,
    lastactivity integer NOT NULL,
    rehash boolean DEFAULT false NOT NULL
);


ALTER TABLE sessions OWNER TO tuxxedo;

SET default_with_oids = true;

--
-- Name: styles; Type: TABLE; Schema: public; Owner: tuxxedo; Tablespace: 
--

CREATE TABLE styles (
    id integer NOT NULL,
    name character varying(128) NOT NULL,
    developer character varying(128) NOT NULL,
    styledir character varying(128) NOT NULL,
    isdefault boolean DEFAULT false NOT NULL
);


ALTER TABLE styles OWNER TO tuxxedo;

--
-- Name: styles_id_seq; Type: SEQUENCE; Schema: public; Owner: tuxxedo
--

CREATE SEQUENCE styles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE styles_id_seq OWNER TO tuxxedo;

--
-- Name: styles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: tuxxedo
--

ALTER SEQUENCE styles_id_seq OWNED BY styles.id;


--
-- Name: templates; Type: TABLE; Schema: public; Owner: tuxxedo; Tablespace: 
--

CREATE TABLE templates (
    id integer NOT NULL,
    title character varying(128) NOT NULL,
    source text NOT NULL,
    compiledsource text NOT NULL,
    defaultsource text NOT NULL,
    styleid integer NOT NULL,
    changed boolean NOT NULL,
    revision integer DEFAULT 1 NOT NULL
);


ALTER TABLE templates OWNER TO tuxxedo;

--
-- Name: templates_id_seq; Type: SEQUENCE; Schema: public; Owner: tuxxedo
--

CREATE SEQUENCE templates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE templates_id_seq OWNER TO tuxxedo;

--
-- Name: templates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: tuxxedo
--

ALTER SEQUENCE templates_id_seq OWNED BY templates.id;


SET default_with_oids = false;

--
-- Name: usergroups; Type: TABLE; Schema: public; Owner: tuxxedo; Tablespace: 
--

CREATE TABLE usergroups (
    id integer NOT NULL,
    title character varying(127) NOT NULL,
    permissions integer NOT NULL
);


ALTER TABLE usergroups OWNER TO tuxxedo;

--
-- Name: usergroups_id_seq; Type: SEQUENCE; Schema: public; Owner: tuxxedo
--

CREATE SEQUENCE usergroups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE usergroups_id_seq OWNER TO tuxxedo;

--
-- Name: usergroups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: tuxxedo
--

ALTER SEQUENCE usergroups_id_seq OWNED BY usergroups.id;


SET default_with_oids = true;

--
-- Name: users; Type: TABLE; Schema: public; Owner: tuxxedo; Tablespace: 
--

CREATE TABLE users (
    id integer NOT NULL,
    username character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    password character varying(40) NOT NULL,
    usergroupid integer NOT NULL,
    salt character varying(8) NOT NULL,
    style_id integer,
    language_id integer,
    timezone text NOT NULL,
    timezone_offset integer NOT NULL,
    permissions integer DEFAULT 0 NOT NULL
);


ALTER TABLE users OWNER TO tuxxedo;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: tuxxedo
--

CREATE SEQUENCE users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE users_id_seq OWNER TO tuxxedo;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: tuxxedo
--

ALTER SEQUENCE users_id_seq OWNED BY users.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: tuxxedo
--

ALTER TABLE ONLY languages ALTER COLUMN id SET DEFAULT nextval('languages_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: tuxxedo
--

ALTER TABLE ONLY phrasegroups ALTER COLUMN id SET DEFAULT nextval('phrasegroups_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: tuxxedo
--

ALTER TABLE ONLY phrases ALTER COLUMN id SET DEFAULT nextval('phrases_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: tuxxedo
--

ALTER TABLE ONLY styles ALTER COLUMN id SET DEFAULT nextval('styles_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: tuxxedo
--

ALTER TABLE ONLY templates ALTER COLUMN id SET DEFAULT nextval('templates_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: tuxxedo
--

ALTER TABLE ONLY usergroups ALTER COLUMN id SET DEFAULT nextval('usergroups_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: tuxxedo
--

ALTER TABLE ONLY users ALTER COLUMN id SET DEFAULT nextval('users_id_seq'::regclass);


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
-- Data for Name: phrasegroups; Type: TABLE DATA; Schema: public; Owner: tuxxedo
--

COPY phrasegroups (id, title, languageid) FROM stdin;
1	global	1
2	datamanagers	1
3	devtools	1
\.


--
-- Name: phrasegroups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: tuxxedo
--

SELECT pg_catalog.setval('phrasegroups_id_seq', 1, false);


--
-- Data for Name: phrases; Type: TABLE DATA; Schema: public; Owner: tuxxedo
--

COPY phrases (id, title, translation, defaulttranslation, changed, languageid, phrasegroup) FROM stdin;
1	dm_style_name	Style name	Style name	f	1	datamanagers
2	dm_style_developer	Style developer	Style developer	f	1	datamanagers
3	dm_style_styledir	Style directory	Style directory	f	1	datamanagers
4	dm_user_email	Email address	Email address	f	1	datamanagers
5	dm_user_name	Name	Name	f	1	datamanagers
6	dm_user_password	Password hash	Password hash	f	1	datamanagers
7	dm_user_usergroupid	Usergroup identifier	Usergroup identifier	f	1	datamanagers
8	dm_user_salt	Password salt	Password salt	f	1	datamanagers
9	dm_user_timezone	Timezone	Timezone	f	1	datamanagers
10	dm_user_username	Username	Username	f	1	datamanagers
11	dm_user_style_id	Style identifier	Style identifier	f	1	datamanagers
12	dm_user_language_id	Language identifier	Language identifier	f	1	datamanagers
13	dm_usergroup_title	Usergroup title	Usergroup title	f	1	datamanagers
14	dm_usergroup_type	Usergroup type	Usergroup type	f	1	datamanagers
15	dm_usergroup_permissions	Permission mask	Permission mask	f	1	datamanagers
16	dm_session_sessionid	Session identifier	Session identifier	f	1	datamanagers
17	dm_session_userid	User identifier	User identifier	f	1	datamanagers
18	dm_session_location	User location	User location	f	1	datamanagers
19	dm_session_useragent	User agent string	User agent string	f	1	datamanagers
20	dm_session_lastactivity	Last activity	Last activity	f	1	datamanagers
21	dm_usergroup_id	Usergroup identifier	Usergroup identifier	f	1	datamanagers
22	dm_style_id	Style identifier	Style identifier	f	1	datamanagers
23	dm_style_isdefault	Default style setting	Default style setting	f	1	datamanagers
24	dm_user_id	User identifier	User identifier	f	1	datamanagers
25	dm_user_timezone_offset	User timezone offset	User timezone offset	f	1	datamanagers
26	dm_user_permissions	User permissions	User permissions	f	1	datamanagers
27	dm_template_id	Template identifier	Template identifier	f	1	datamanagers
28	dm_template_title	Template title	Template title	f	1	datamanagers
29	dm_template_source	Template source	Template source	f	1	datamanagers
30	dm_template_compiledsource	Template compiled source	Template compiled source	f	1	datamanagers
31	dm_template_defaultsource	Template default source	Template default source	f	1	datamanagers
32	dm_template_styleid	Template style identifier	Template style identifier	f	1	datamanagers
33	dm_template_revision	Template revision	Template revision	f	1	datamanagers
34	dm_template_changed	Template customization status	Template customization status	f	1	datamanagers
35	dm_permission_name	Permission name	Permission name	f	1	datamanagers
36	dm_permission_bits	Permission bitmask	Permission bitmask	f	1	datamanagers
37	dm_option_option	Option name	Option name	f	1	datamanagers
38	dm_option_value	Option value	Option value	f	1	datamanagers
39	dm_option_defaultvalue	Option default value	Option default value	f	1	datamanagers
40	dm_option_type	Option data type	Option data type	f	1	datamanagers
41	validation_failed	Failed validation of datamanager fields	Failed validation of datamanager fields	f	1	datamanagers
42	dm_option_category	Option category	Option category	f	1	datamanagers
43	dm_optioncategory_name	Option category name	Option category name	f	1	datamanagers
44	dm_datastore_name	Datastore cache name	Datastore cache name	f	1	datamanagers
45	dm_datastore_data	Datastore cache data	Datastore cache data	f	1	datamanagers
46	dm_phrasegroup_id	Phrasegroup identifier	Phrasegroup identifier	f	1	datamanagers
47	dm_phrasegroup_title	Phrasegroup title	Phrasegroup title	f	1	datamanagers
48	dm_phrasegroup_languageid	Phrasegroup language identifier	Phrasegroup language identifier	f	1	datamanagers
49	dm_language_id	Language identifier	Language identifier	f	1	datamanagers
50	dm_language_title	Language title	Language title	f	1	datamanagers
51	dm_language_developer	Language developer	Language developer	f	1	datamanagers
52	dm_language_isotitle	Language ISO code value	Language ISO code value	f	1	datamanagers
53	dm_language_isdefault	Language default setting	Language default setting	f	1	datamanagers
54	dm_language_charset	Language character set	Language character set	f	1	datamanagers
55	dm_phrase_id	Phrase identifier	Phrase identifier	f	1	datamanagers
56	dm_phrase_title	Phrase title	Phrase title	f	1	datamanagers
57	dm_phrase_translation	Phrase translation	Phrase translation	f	1	datamanagers
58	dm_phrase_languageid	Phrase language identifier	Phrase language identifier	f	1	datamanagers
59	dm_phrase_phrasegroup	Phrase phrasegroup	Phrase phrasegroup	f	1	datamanagers
60	dm_session_rehash	Session rehashing attribute	Session rehashing attribute	f	1	datamanagers
\.


--
-- Name: phrases_id_seq; Type: SEQUENCE SET; Schema: public; Owner: tuxxedo
--

SELECT pg_catalog.setval('phrases_id_seq', 1, false);


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: tuxxedo
--

COPY sessions (sessionid, userid, location, useragent, lastactivity, rehash) FROM stdin;
\.


--
-- Data for Name: styles; Type: TABLE DATA; Schema: public; Owner: tuxxedo
--

COPY styles (id, name, developer, styledir, isdefault) FROM stdin;
1	Default	Tuxxedo Software Development	default	t
\.


--
-- Name: styles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: tuxxedo
--

SELECT pg_catalog.setval('styles_id_seq', 1, false);


--
-- Data for Name: templates; Type: TABLE DATA; Schema: public; Owner: tuxxedo
--

COPY templates (id, title, source, compiledsource, defaultsource, styleid, changed, revision) FROM stdin;
1	header	<?xml version="1.0" encoding="UTF-8"?>\\r\\n<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\\r\\n<html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">\\r\\n\t<head>\\r\\n\t\t<title>Tuxxedo Engine</title>\\r\\n\\r\\n\t\t<style type="text/css">\\r\\n\t\t*\\r\\n\t\t{\\r\\n\t\t\tfont-family: \t\t"Helvetica Neue", Helvetica, Trebuchet MS, Verdana, Tahoma, Arial, sans-serif;\\r\\n\t\t}\\r\\n\t\ta\\r\\n\t\t{\\r\\n\t\t\tcolor:\t\t\t#021420;\\r\\n\t\t\tfont-weight:\t\tbold;\\r\\n\t\t}\\r\\n\t\tbody\\r\\n\t\t{\\r\\n\t\t\tbackground-color: \t#021420;\\r\\n\t\t\tfont-size: \t\t82%;\\r\\n\t\t\tcolor: \t\t\t#3B7286;\\r\\n\t\t\tpadding: \t\t0px 30px;\\r\\n\t\t}\\r\\n\t\th1\\r\\n\t\t{\\r\\n\t\t\tcolor: \t\t\t#FFFFFF;\\r\\n\t\t}\\r\\n\t\tinput, .link-button\\r\\n\t\t{\\r\\n\t\t\tbackground-color:\t#EAEAEA;\\r\\n\t\t\tborder:\t\t\t0px;\\r\\n\t\t\tborder-radius: \t\t4px;\\r\\n\t\t\tpadding:\t\t3px 10px;\\r\\n\t\t}\\r\\n\t\t\tinput[type=password], input[type=text]\\r\\n\t\t\t{\\r\\n\t\t\t\tbackground-color:\t#FFFFFF;\\r\\n\t\t\t\tborder:\t\t\t1px solid #EAEAEA;\\r\\n\t\t\t}\\r\\n\t\t.box\\r\\n\t\t{\\r\\n\t\t\tbackground-color: \t#D2D2D2;\\r\\n\t\t\tborder: \t\t3px solid #D2D2D2;\\r\\n\t\t\tborder-radius: \t\t4px;\\r\\n\t\t}\\r\\n\t\t\t.box .inner\\r\\n\t\t\t{\\r\\n\t\t\t\tbackground-color: \t#FFFFFF;\\r\\n\t\t\t\tborder-radius: \t\t4px;\\r\\n\t\t\t\tpadding: \t\t6px;\\r\\n\t\t\t}\\r\\n\t\t.wrapper\\r\\n\t\t{\\r\\n\t\t\tmargin:\t\t\t0px auto;\\r\\n\t\t\twidth:\t\t\t80%;\\r\\n\t\t}\\r\\n\t\t</style>\\r\\n\t</head>\\r\\n\t<body>\\r\\n\t\t<div class="wrapper">	<?xml version=\\\\"1.0\\\\" encoding=\\\\"UTF-8\\\\"?>\\r\\n<!DOCTYPE html PUBLIC \\\\"-//W3C//DTD XHTML 1.0 Strict//EN\\\\" \\\\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\\\\">\\r\\n<html dir=\\\\"ltr\\\\" xmlns=\\\\"http://www.w3.org/1999/xhtml\\\\" xml:lang=\\\\"en\\\\" lang=\\\\"en\\\\">\\r\\n\t<head>\\r\\n\t\t<title>Tuxxedo Engine</title>\\r\\n\\r\\n\t\t<style type=\\\\"text/css\\\\">\\r\\n\t\t*\\r\\n\t\t{\\r\\n\t\t\tfont-family: \t\t\\\\"Helvetica Neue\\\\", Helvetica, Trebuchet MS, Verdana, Tahoma, Arial, sans-serif;\\r\\n\t\t}\\r\\n\t\ta\\r\\n\t\t{\\r\\n\t\t\tcolor:\t\t\t#021420;\\r\\n\t\t\tfont-weight:\t\tbold;\\r\\n\t\t}\\r\\n\t\tbody\\r\\n\t\t{\\r\\n\t\t\tbackground-color: \t#021420;\\r\\n\t\t\tfont-size: \t\t82%;\\r\\n\t\t\tcolor: \t\t\t#3B7286;\\r\\n\t\t\tpadding: \t\t0px 30px;\\r\\n\t\t}\\r\\n\t\th1\\r\\n\t\t{\\r\\n\t\t\tcolor: \t\t\t#FFFFFF;\\r\\n\t\t}\\r\\n\t\tinput, .link-button\\r\\n\t\t{\\r\\n\t\t\tbackground-color:\t#EAEAEA;\\r\\n\t\t\tborder:\t\t\t0px;\\r\\n\t\t\tborder-radius: \t\t4px;\\r\\n\t\t\tpadding:\t\t3px 10px;\\r\\n\t\t}\\r\\n\t\t\tinput[type=password], input[type=text]\\r\\n\t\t\t{\\r\\n\t\t\t\tbackground-color:\t#FFFFFF;\\r\\n\t\t\t\tborder:\t\t\t1px solid #EAEAEA;\\r\\n\t\t\t}\\r\\n\t\t.box\\r\\n\t\t{\\r\\n\t\t\tbackground-color: \t#D2D2D2;\\r\\n\t\t\tborder: \t\t3px solid #D2D2D2;\\r\\n\t\t\tborder-radius: \t\t4px;\\r\\n\t\t}\\r\\n\t\t\t.box .inner\\r\\n\t\t\t{\\r\\n\t\t\t\tbackground-color: \t#FFFFFF;\\r\\n\t\t\t\tborder-radius: \t\t4px;\\r\\n\t\t\t\tpadding: \t\t6px;\\r\\n\t\t\t}\\r\\n\t\t.wrapper\\r\\n\t\t{\\r\\n\t\t\tmargin:\t\t\t0px auto;\\r\\n\t\t\twidth:\t\t\t80%;\\r\\n\t\t}\\r\\n\t\t</style>\\r\\n\t</head>\\r\\n\t<body>\\r\\n\t\t<div class=\\\\"wrapper\\\\">	<?xml version="1.0" encoding="UTF-8"?>\\r\\n<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\\r\\n<html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">\\r\\n\t<head>\\r\\n\t\t<title>Tuxxedo Engine</title>\\r\\n\\r\\n\t\t<style type="text/css">\\r\\n\t\t*\\r\\n\t\t{\\r\\n\t\t\tfont-family: \t\t"Helvetica Neue", Helvetica, Trebuchet MS, Verdana, Tahoma, Arial, sans-serif;\\r\\n\t\t}\\r\\n\t\ta\\r\\n\t\t{\\r\\n\t\t\tcolor:\t\t\t#021420;\\r\\n\t\t\tfont-weight:\t\tbold;\\r\\n\t\t}\\r\\n\t\tbody\\r\\n\t\t{\\r\\n\t\t\tbackground-color: \t#021420;\\r\\n\t\t\tfont-size: \t\t82%;\\r\\n\t\t\tcolor: \t\t\t#3B7286;\\r\\n\t\t\tpadding: \t\t0px 30px;\\r\\n\t\t}\\r\\n\t\th1\\r\\n\t\t{\\r\\n\t\t\tcolor: \t\t\t#FFFFFF;\\r\\n\t\t}\\r\\n\t\tinput, .link-button\\r\\n\t\t{\\r\\n\t\t\tbackground-color:\t#EAEAEA;\\r\\n\t\t\tborder:\t\t\t0px;\\r\\n\t\t\tborder-radius: \t\t4px;\\r\\n\t\t\tpadding:\t\t3px 10px;\\r\\n\t\t}\\r\\n\t\t\tinput[type=password], input[type=text]\\r\\n\t\t\t{\\r\\n\t\t\t\tbackground-color:\t#FFFFFF;\\r\\n\t\t\t\tborder:\t\t\t1px solid #EAEAEA;\\r\\n\t\t\t}\\r\\n\t\t.box\\r\\n\t\t{\\r\\n\t\t\tbackground-color: \t#D2D2D2;\\r\\n\t\t\tborder: \t\t3px solid #D2D2D2;\\r\\n\t\t\tborder-radius: \t\t4px;\\r\\n\t\t}\\r\\n\t\t\t.box .inner\\r\\n\t\t\t{\\r\\n\t\t\t\tbackground-color: \t#FFFFFF;\\r\\n\t\t\t\tborder-radius: \t\t4px;\\r\\n\t\t\t\tpadding: \t\t6px;\\r\\n\t\t\t}\\r\\n\t\t.wrapper\\r\\n\t\t{\\r\\n\t\t\tmargin:\t\t\t0px auto;\\r\\n\t\t\twidth:\t\t\t80%;\\r\\n\t\t}\\r\\n\t\t</style>\\r\\n\t</head>\\r\\n\t<body>\\r\\n\t\t<div class="wrapper">	1	f	2
2	footer	\t\t</div>\\r\\n\t</body>\\r\\n</html>	\t\t</div>\\r\\n\t</body>\\r\\n</html>	\t\t</div>\\r\\n\t</body>\\r\\n</html>	1	f	2
4	error	{$header}\\r\\n\\r\\n<h1>Error</h1>\\r\\n<div class="box">\\r\\n\t<div class="inner">\\r\\n\t\t{$message}\\r\\n\\r\\n\t\t<if expression="isset($error_list)">\\r\\n\t\t<ul>\\r\\n\t\t\t{$error_list}\\r\\n\t\t</ul>\\r\\n\t\t</if>\\r\\n\t</div>\\r\\n</div>\\r\\n\\r\\n<if expression="isset($go_back) && $go_back">\\r\\n<br />\\r\\n\\r\\n<div class="box">\\r\\n\t<div class="inner">\\r\\n\t\t<input type="button" onclick="history.back(-1);" value="Go back" />\\r\\n\t</div>\\r\\n</div>\\r\\n</if>\\r\\n\\r\\n{$footer}	{$header}\\r\\n\\r\\n<h1>Error</h1>\\r\\n<div class=\\\\"box\\\\">\\r\\n\t<div class=\\\\"inner\\\\">\\r\\n\t\t{$message}\\r\\n\\r\\n\t\t" . ((isset($error_list)) ? ("\\r\\n\t\t<ul>\\r\\n\t\t\t{$error_list}\\r\\n\t\t</ul>\\r\\n\t\t") : '') . "\\r\\n\t</div>\\r\\n</div>\\r\\n\\r\\n" . ((isset($go_back) && $go_back) ? ("\\r\\n<br />\\r\\n\\r\\n<div class=\\\\"box\\\\">\\r\\n\t<div class=\\\\"inner\\\\">\\r\\n\t\t<input type=\\\\"button\\\\" onclick=\\\\"history.back(-1);\\\\" value=\\\\"Go back\\\\" />\\r\\n\t</div>\\r\\n</div>\\r\\n") : '') . "\\r\\n\\r\\n{$footer}	{$header}\\r\\n\\r\\n<h1>Error</h1>\\r\\n<div class="box">\\r\\n\t<div class="inner">\\r\\n\t\t{$message}\\r\\n\\r\\n\t\t<if expression="isset($error_list)">\\r\\n\t\t<ul>\\r\\n\t\t\t{$error_list}\\r\\n\t\t</ul>\\r\\n\t\t</if>\\r\\n\t</div>\\r\\n</div>\\r\\n\\r\\n<if expression="isset($go_back) && $go_back">\\r\\n<br />\\r\\n\\r\\n<div class="box">\\r\\n\t<div class="inner">\\r\\n\t\t<input type="button" onclick="history.back(-1);" value="Go back" />\\r\\n\t</div>\\r\\n</div>\\r\\n</if>\\r\\n\\r\\n{$footer}	1	f	2
5	index	{$header}\\r\\n\\r\\n<h1>Tuxxedo Engine</h1>\\r\\n<div class="box">\\r\\n\t<div class="inner">\\r\\n\t\tThank you for choosing Tuxxedo Engine, version {$version} is installed \\r\\n\t\tand ready to use.\\r\\n\\r\\n\t\t<p>\\r\\n\t\t\tTo begin developing, head over to the DevTools component, this component \\r\\n\t\t\tis as the name sounds, designed to ease development of Engine based \\r\\n\t\t\tapplications. If you are interested in how Engine is developed, head over to \\r\\n\t\t\tour blog and our project site.\\r\\n\t\t</p>\\r\\n\\r\\n\t\t<p>\\r\\n\t\t\tRemember to checkout the 'configuration.php' file, to define the application \\r\\n\t\t\tvariables. Debugging mode can also be enabled/disabled here. The debug mode \\r\\n\t\t\tshould always be enabled when working on a development server to give to \\r\\n\t\t\tmore expressive error messages, and always turned off when the application \\r\\n\t\t\tis deployed to production servers.\\r\\n\t\t</p>\\r\\n\\r\\n\t\t<a class="link-button" href="./dev/tools/">DevTools</a> \\r\\n\t\t<a class="link-button" href="http://www.tuxxedo.net/devblog/">Blog</a> \\r\\n\t\t<a class="link-button" href="http://code.google.com/p/tuxxedo">Project</a>\\r\\n\t</div>\\r\\n</div>\\r\\n\\r\\n{$footer}	{$header}\\r\\n\\r\\n<h1>Tuxxedo Engine</h1>\\r\\n<div class=\\\\"box\\\\">\\r\\n\t<div class=\\\\"inner\\\\">\\r\\n\t\tThank you for choosing Tuxxedo Engine, version {$version} is installed \\r\\n\t\tand ready to use.\\r\\n\\r\\n\t\t<p>\\r\\n\t\t\tTo begin developing, head over to the DevTools component, this component \\r\\n\t\t\tis as the name sounds, designed to ease development of Engine based \\r\\n\t\t\tapplications. If you are interested in how Engine is developed, head over to \\r\\n\t\t\tour blog and our project site.\\r\\n\t\t</p>\\r\\n\\r\\n\t\t<p>\\r\\n\t\t\tRemember to checkout the 'configuration.php' file, to define the application \\r\\n\t\t\tvariables. Debugging mode can also be enabled/disabled here. The debug mode \\r\\n\t\t\tshould always be enabled when working on a development server to give to \\r\\n\t\t\tmore expressive error messages, and always turned off when the application \\r\\n\t\t\tis deployed to production servers.\\r\\n\t\t</p>\\r\\n\\r\\n\t\t<a class=\\\\"link-button\\\\" href=\\\\"./dev/tools/\\\\">DevTools</a> \\r\\n\t\t<a class=\\\\"link-button\\\\" href=\\\\"http://www.tuxxedo.net/devblog/\\\\">Blog</a> \\r\\n\t\t<a class=\\\\"link-button\\\\" href=\\\\"http://code.google.com/p/tuxxedo\\\\">Project</a>\\r\\n\t</div>\\r\\n</div>\\r\\n\\r\\n{$footer}	{$header}\\r\\n\\r\\n<h1>Tuxxedo Engine</h1>\\r\\n<div class=\\\\"box\\\\">\\r\\n\t<div class=\\\\"inner\\\\">\\r\\n\t\tThank you for choosing Tuxxedo Engine, version {$version} is installed \\r\\n\t\tand ready to use.\\r\\n\\r\\n\t\t<p>\\r\\n\t\t\tTo begin developing, head over to the DevTools component, this component \\r\\n\t\t\tis as the name sounds, designed to ease development of Engine based \\r\\n\t\t\tapplications. If you are interested in how Engine is developed, head over to \\r\\n\t\t\tour blog and our project site.\\r\\n\t\t</p>\\r\\n\\r\\n\t\t<p>\\r\\n\t\t\tRemember to checkout the 'configuration.php' file, to define the application \\r\\n\t\t\tvariables. Debugging mode can also be enabled/disabled here. The debug mode \\r\\n\t\t\tshould always be enabled when working on a development server to give to \\r\\n\t\t\tmore expressive error messages, and always turned off when the application \\r\\n\t\t\tis deployed to production servers.\\r\\n\t\t</p>\\r\\n\\r\\n\t\t<a class=\\\\"link-button\\\\" href=\\\\"./dev/tools/\\\\">DevTools</a> \\r\\n\t\t<a class=\\\\"link-button\\\\" href=\\\\"http://www.tuxxedo.net/devblog/\\\\">Blog</a> \\r\\n\t\t<a class=\\\\"link-button\\\\" href=\\\\"http://code.google.com/p/tuxxedo\\\\">Project</a>\\r\\n\t</div>\\r\\n</div>\\r\\n\\r\\n{$footer}	1	f	2
6	error_listbit	<li>{$error}</li>	<li>{$error}</li>	<li>{$error}</li>	1	f	1
\.


--
-- Name: templates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: tuxxedo
--

SELECT pg_catalog.setval('templates_id_seq', 1, false);


--
-- Data for Name: usergroups; Type: TABLE DATA; Schema: public; Owner: tuxxedo
--

COPY usergroups (id, title, permissions) FROM stdin;
1	Administrators	1
2	Regular users	0
\.


--
-- Name: usergroups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: tuxxedo
--

SELECT pg_catalog.setval('usergroups_id_seq', 1, false);


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: tuxxedo
--

COPY users (id, username, email, name, password, usergroupid, salt, style_id, language_id, timezone, timezone_offset, permissions) FROM stdin;
1	Tuxxedo	blackhole@tuxxedo.net	Tuxxedo Test Account	cc95587719affd4460394d1a0311d1c11040fe69	1	%-C?;_wj	\N	\N	UTC	0	0
\.


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: tuxxedo
--

SELECT pg_catalog.setval('users_id_seq', 1, false);


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
-- Name: phrasegroups_pkey; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY phrasegroups
    ADD CONSTRAINT phrasegroups_pkey PRIMARY KEY (id);


--
-- Name: phrases_pkey; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY phrases
    ADD CONSTRAINT phrases_pkey PRIMARY KEY (id);


--
-- Name: sessions_sessionid_key; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY sessions
    ADD CONSTRAINT sessions_sessionid_key UNIQUE (sessionid);


--
-- Name: styles_pkey; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY styles
    ADD CONSTRAINT styles_pkey PRIMARY KEY (id);


--
-- Name: templates_pkey; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY templates
    ADD CONSTRAINT templates_pkey PRIMARY KEY (id);


--
-- Name: usergroups_pkey; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY usergroups
    ADD CONSTRAINT usergroups_pkey PRIMARY KEY (id);


--
-- Name: users_email_key; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users_pkey; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users_username_key; Type: CONSTRAINT; Schema: public; Owner: tuxxedo; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_username_key UNIQUE (username);


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

