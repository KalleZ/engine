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
styleinfo	a:1:{i:1;a:6:{s:2:"id";s:1:"1";s:4:"name";s:7:"Default";s:9:"developer";s:28:"Tuxxedo Software Development";s:8:"styledir";s:7:"default";s:9:"isdefault";s:1:"t";s:11:"templateids";s:9:"1,2,4,5,6";}}
usergroups	a:2:{i:1;a:4:{s:2:"id";s:1:"1";s:5:"title";s:14:"Administrators";s:11:"permissions";i:1;s:5:"users";i:1;}i:2;a:4:{s:2:"id";s:1:"2";s:5:"title";s:13:"Regular users";s:11:"permissions";i:0;s:5:"users";i:0;}}
timezones	a:417:{s:17:"Pacific/Pago Pago";s:3:"-11";s:12:"Pacific/Niue";s:3:"-11";s:14:"Pacific/Midway";s:3:"-11";s:17:"Pacific/Rarotonga";s:3:"-10";s:12:"America/Adak";s:3:"-10";s:16:"Pacific/Johnston";s:3:"-10";s:16:"Pacific/Honolulu";s:3:"-10";s:14:"Pacific/Tahiti";s:3:"-10";s:17:"Pacific/Marquesas";s:4:"-9.5";s:15:"America/Yakutat";s:2:"-9";s:12:"America/Nome";s:2:"-9";s:14:"America/Juneau";s:2:"-9";s:13:"America/Sitka";s:2:"-9";s:17:"America/Anchorage";s:2:"-9";s:15:"Pacific/Gambier";s:2:"-9";s:17:"America/Vancouver";s:2:"-8";s:18:"America/Whitehorse";s:2:"-8";s:14:"America/Dawson";s:2:"-8";s:15:"America/Tijuana";s:2:"-8";s:16:"Pacific/Pitcairn";s:2:"-8";s:20:"America/Santa Isabel";s:2:"-8";s:18:"America/Metlakatla";s:2:"-8";s:19:"America/Los Angeles";s:2:"-8";s:16:"America/Mazatlan";s:2:"-7";s:15:"America/Ojinaga";s:2:"-7";s:17:"America/Chihuahua";s:2:"-7";s:13:"America/Boise";s:2:"-7";s:21:"America/Cambridge Bay";s:2:"-7";s:15:"America/Phoenix";s:2:"-7";s:20:"America/Dawson Creek";s:2:"-7";s:16:"America/Edmonton";s:2:"-7";s:14:"America/Denver";s:2:"-7";s:18:"America/Hermosillo";s:2:"-7";s:14:"America/Inuvik";s:2:"-7";s:15:"America/Creston";s:2:"-7";s:19:"America/Yellowknife";s:2:"-7";s:15:"America/Managua";s:2:"-6";s:19:"America/Mexico City";s:2:"-6";s:14:"America/Beulah";s:2:"-6";s:17:"America/New Salem";s:2:"-6";s:17:"America/Tell City";s:2:"-6";s:12:"America/Knox";s:2:"-6";s:14:"America/Center";s:2:"-6";s:14:"America/Merida";s:2:"-6";s:14:"America/Cancun";s:2:"-6";s:17:"America/Matamoros";s:2:"-6";s:15:"America/Chicago";s:2:"-6";s:17:"America/Menominee";s:2:"-6";s:19:"America/El Salvador";s:2:"-6";s:18:"America/Costa Rica";s:2:"-6";s:14:"America/Belize";s:2:"-6";s:17:"America/Guatemala";s:2:"-6";s:22:"America/Bahia Banderas";s:2:"-6";s:17:"America/Monterrey";s:2:"-6";s:16:"America/Winnipeg";s:2:"-6";s:21:"America/Swift Current";s:2:"-6";s:20:"America/Rankin Inlet";s:2:"-6";s:19:"America/Rainy River";s:2:"-6";s:17:"Pacific/Galapagos";s:2:"-6";s:19:"America/Tegucigalpa";s:2:"-6";s:14:"America/Regina";s:2:"-6";s:16:"America/Resolute";s:2:"-6";s:15:"America/Marengo";s:2:"-5";s:18:"America/Petersburg";s:2:"-5";s:14:"America/Bogota";s:2:"-5";s:13:"America/Vevay";s:2:"-5";s:20:"America/Indianapolis";s:2:"-5";s:14:"America/Havana";s:2:"-5";s:18:"America/Grand Turk";s:2:"-5";s:15:"America/Toronto";s:2:"-5";s:16:"America/Eirunepe";s:2:"-5";s:15:"America/Detroit";s:2:"-5";s:19:"America/Thunder Bay";s:2:"-5";s:15:"America/Winamac";s:2:"-5";s:17:"America/Guayaquil";s:2:"-5";s:14:"America/Cayman";s:2:"-5";s:17:"America/Vincennes";s:2:"-5";s:16:"America/New York";s:2:"-5";s:18:"America/Rio Branco";s:2:"-5";s:14:"America/Nassau";s:2:"-5";s:15:"America/Nipigon";s:2:"-5";s:14:"America/Panama";s:2:"-5";s:22:"America/Port-au-Prince";s:2:"-5";s:19:"America/Pangnirtung";s:2:"-5";s:18:"America/Monticello";s:2:"-5";s:18:"America/Louisville";s:2:"-5";s:16:"America/Atikokan";s:2:"-5";s:15:"America/Iqaluit";s:2:"-5";s:14:"Pacific/Easter";s:2:"-5";s:12:"America/Lima";s:2:"-5";s:15:"America/Jamaica";s:2:"-5";s:15:"America/Caracas";s:4:"-4.5";s:14:"America/Manaus";s:2:"-4";s:21:"America/Lower Princes";s:2:"-4";s:17:"America/Goose Bay";s:2:"-4";s:14:"America/Guyana";s:2:"-4";s:16:"Atlantic/Bermuda";s:2:"-4";s:17:"America/Glace Bay";s:2:"-4";s:15:"America/Halifax";s:2:"-4";s:18:"America/Kralendijk";s:2:"-4";s:14:"America/La Paz";s:2:"-4";s:18:"America/Guadeloupe";s:2:"-4";s:15:"America/Grenada";s:2:"-4";s:18:"America/Martinique";s:2:"-4";s:16:"America/Barbados";s:2:"-4";s:13:"America/Aruba";s:2:"-4";s:20:"America/Blanc-Sablon";s:2:"-4";s:17:"America/Boa Vista";s:2:"-4";s:15:"America/Marigot";s:2:"-4";s:15:"America/Moncton";s:2:"-4";s:18:"America/Montserrat";s:2:"-4";s:15:"America/Antigua";s:2:"-4";s:21:"America/Port of Spain";s:2:"-4";s:19:"America/Porto Velho";s:2:"-4";s:19:"America/Puerto Rico";s:2:"-4";s:21:"America/St Barthelemy";s:2:"-4";s:21:"America/Santo Domingo";s:2:"-4";s:16:"America/Anguilla";s:2:"-4";s:15:"America/Curacao";s:2:"-4";s:13:"America/Thule";s:2:"-4";s:16:"America/Dominica";s:2:"-4";s:15:"America/Tortola";s:2:"-4";s:18:"America/St Vincent";s:2:"-4";s:17:"America/St Thomas";s:2:"-4";s:16:"America/St Lucia";s:2:"-4";s:16:"America/St Kitts";s:2:"-4";s:16:"America/St Johns";s:4:"-3.5";s:16:"America/Santiago";s:2:"-3";s:16:"Atlantic/Stanley";s:2:"-3";s:16:"America/Santarem";s:2:"-3";s:17:"Antarctica/Palmer";s:2:"-3";s:18:"Antarctica/Rothera";s:2:"-3";s:14:"America/Recife";s:2:"-3";s:16:"America/Miquelon";s:2:"-3";s:18:"America/Paramaribo";s:2:"-3";s:20:"America/Rio Gallegos";s:2:"-3";s:13:"America/Salta";s:2:"-3";s:16:"America/San Juan";s:2:"-3";s:16:"America/San Luis";s:2:"-3";s:15:"America/Mendoza";s:2:"-3";s:16:"America/La Rioja";s:2:"-3";s:17:"America/Araguaina";s:2:"-3";s:17:"America/Catamarca";s:2:"-3";s:15:"America/Cordoba";s:2:"-3";s:13:"America/Jujuy";s:2:"-3";s:15:"America/Tucuman";s:2:"-3";s:20:"America/Buenos Aires";s:2:"-3";s:14:"America/Cuiaba";s:2:"-3";s:17:"America/Fortaleza";s:2:"-3";s:15:"America/Godthab";s:2:"-3";s:15:"America/Ushuaia";s:2:"-3";s:15:"America/Cayenne";s:2:"-3";s:14:"America/Maceio";s:2:"-3";s:20:"America/Campo Grande";s:2:"-3";s:13:"America/Bahia";s:2:"-3";s:16:"America/Asuncion";s:2:"-3";s:13:"America/Belem";s:2:"-3";s:17:"America/Sao Paulo";s:2:"-2";s:22:"Atlantic/South Georgia";s:2:"-2";s:15:"America/Noronha";s:2:"-2";s:18:"America/Montevideo";s:2:"-2";s:19:"Atlantic/Cape Verde";s:2:"-1";s:15:"Atlantic/Azores";s:2:"-1";s:20:"America/Scoresbysund";s:2:"-1";s:14:"Atlantic/Faroe";s:1:"0";s:16:"Atlantic/Madeira";s:1:"0";s:13:"Europe/London";s:1:"0";s:15:"Atlantic/Canary";s:1:"0";s:12:"Africa/Accra";s:1:"0";s:18:"Atlantic/St Helena";s:1:"0";s:18:"Atlantic/Reykjavik";s:1:"0";s:17:"Africa/Casablanca";s:1:"0";s:14:"Africa/Abidjan";s:1:"0";s:13:"Africa/Bissau";s:1:"0";s:13:"Africa/Banjul";s:1:"0";s:11:"Africa/Lome";s:1:"0";s:16:"Antarctica/Troll";s:1:"0";s:14:"Africa/Conakry";s:1:"0";s:15:"Africa/El Aaiun";s:1:"0";s:3:"UTC";s:1:"0";s:12:"Africa/Dakar";s:1:"0";s:15:"Africa/Freetown";s:1:"0";s:13:"Europe/Jersey";s:1:"0";s:17:"Africa/Nouakchott";s:1:"0";s:18:"Africa/Ouagadougou";s:1:"0";s:13:"Europe/Dublin";s:1:"0";s:20:"America/Danmarkshavn";s:1:"0";s:15:"Africa/Monrovia";s:1:"0";s:15:"Africa/Sao Tome";s:1:"0";s:13:"Europe/Lisbon";s:1:"0";s:13:"Africa/Bamako";s:1:"0";s:15:"Europe/Guernsey";s:1:"0";s:18:"Europe/Isle of Man";s:1:"0";s:16:"Europe/Stockholm";s:1:"1";s:13:"Europe/Tirane";s:1:"1";s:13:"Europe/Skopje";s:1:"1";s:15:"Europe/Sarajevo";s:1:"1";s:11:"Europe/Rome";s:1:"1";s:17:"Europe/San Marino";s:1:"1";s:12:"Europe/Vaduz";s:1:"1";s:14:"Europe/Vatican";s:1:"1";s:13:"Europe/Zagreb";s:1:"1";s:17:"Europe/Copenhagen";s:1:"1";s:13:"Europe/Warsaw";s:1:"1";s:13:"Europe/Vienna";s:1:"1";s:17:"Europe/Luxembourg";s:1:"1";s:15:"Europe/Busingen";s:1:"1";s:13:"Europe/Zurich";s:1:"1";s:13:"Europe/Prague";s:1:"1";s:17:"Europe/Bratislava";s:1:"1";s:13:"Europe/Madrid";s:1:"1";s:13:"Europe/Berlin";s:1:"1";s:15:"Europe/Belgrade";s:1:"1";s:16:"Europe/Amsterdam";s:1:"1";s:14:"Europe/Andorra";s:1:"1";s:12:"Europe/Malta";s:1:"1";s:15:"Europe/Brussels";s:1:"1";s:16:"Europe/Podgorica";s:1:"1";s:16:"Europe/Gibraltar";s:1:"1";s:12:"Europe/Paris";s:1:"1";s:16:"Europe/Ljubljana";s:1:"1";s:13:"Europe/Monaco";s:1:"1";s:11:"Europe/Oslo";s:1:"1";s:15:"Europe/Budapest";s:1:"1";s:14:"Africa/Algiers";s:1:"1";s:12:"Africa/Lagos";s:1:"1";s:17:"Africa/Libreville";s:1:"1";s:15:"Africa/Kinshasa";s:1:"1";s:13:"Africa/Douala";s:1:"1";s:12:"Africa/Ceuta";s:1:"1";s:13:"Africa/Luanda";s:1:"1";s:13:"Africa/Malabo";s:1:"1";s:12:"Africa/Tunis";s:1:"1";s:17:"Africa/Porto-Novo";s:1:"1";s:13:"Africa/Niamey";s:1:"1";s:15:"Africa/Ndjamena";s:1:"1";s:18:"Africa/Brazzaville";s:1:"1";s:19:"Arctic/Longyearbyen";s:1:"1";s:13:"Africa/Bangui";s:1:"1";s:13:"Africa/Kigali";s:1:"2";s:19:"Africa/Johannesburg";s:1:"2";s:11:"Asia/Hebron";s:1:"2";s:9:"Asia/Gaza";s:1:"2";s:17:"Africa/Lubumbashi";s:1:"2";s:13:"Africa/Harare";s:1:"2";s:11:"Europe/Kiev";s:1:"2";s:11:"Europe/Riga";s:1:"2";s:16:"Africa/Bujumbura";s:1:"2";s:16:"Europe/Mariehamn";s:1:"2";s:15:"Africa/Gaborone";s:1:"2";s:10:"Asia/Amman";s:1:"2";s:15:"Europe/Istanbul";s:1:"2";s:16:"Europe/Bucharest";s:1:"2";s:13:"Asia/Damascus";s:1:"2";s:14:"Africa/Tripoli";s:1:"2";s:15:"Europe/Chisinau";s:1:"2";s:15:"Africa/Windhoek";s:1:"2";s:13:"Europe/Athens";s:1:"2";s:14:"Africa/Mbabane";s:1:"2";s:12:"Europe/Sofia";s:1:"2";s:15:"Europe/Helsinki";s:1:"2";s:13:"Africa/Maputo";s:1:"2";s:13:"Africa/Maseru";s:1:"2";s:13:"Africa/Lusaka";s:1:"2";s:12:"Africa/Cairo";s:1:"2";s:14:"Europe/Vilnius";s:1:"2";s:17:"Europe/Zaporozhye";s:1:"2";s:15:"Europe/Uzhgorod";s:1:"2";s:12:"Asia/Nicosia";s:1:"2";s:15:"Africa/Blantyre";s:1:"2";s:11:"Asia/Beirut";s:1:"2";s:14:"Europe/Tallinn";s:1:"2";s:14:"Asia/Jerusalem";s:1:"2";s:18:"Europe/Kaliningrad";s:1:"3";s:15:"Africa/Djibouti";s:1:"3";s:20:"Africa/Dar es Salaam";s:1:"3";s:15:"Africa/Khartoum";s:1:"3";s:11:"Asia/Kuwait";s:1:"3";s:18:"Africa/Addis Ababa";s:1:"3";s:11:"Africa/Juba";s:1:"3";s:10:"Asia/Qatar";s:1:"3";s:13:"Indian/Comoro";s:1:"3";s:12:"Europe/Minsk";s:1:"3";s:12:"Asia/Bahrain";s:1:"3";s:16:"Africa/Mogadishu";s:1:"3";s:19:"Indian/Antananarivo";s:1:"3";s:16:"Antarctica/Syowa";s:1:"3";s:14:"Africa/Nairobi";s:1:"3";s:12:"Asia/Baghdad";s:1:"3";s:14:"Africa/Kampala";s:1:"3";s:11:"Asia/Riyadh";s:1:"3";s:14:"Indian/Mayotte";s:1:"3";s:9:"Asia/Aden";s:1:"3";s:13:"Africa/Asmara";s:1:"3";s:11:"Asia/Tehran";s:3:"3.5";s:9:"Asia/Baku";s:1:"4";s:14:"Indian/Reunion";s:1:"4";s:10:"Asia/Dubai";s:1:"4";s:16:"Europe/Volgograd";s:1:"4";s:11:"Asia/Muscat";s:1:"4";s:12:"Asia/Tbilisi";s:1:"4";s:13:"Europe/Moscow";s:1:"4";s:13:"Europe/Samara";s:1:"4";s:17:"Europe/Simferopol";s:1:"4";s:12:"Asia/Yerevan";s:1:"4";s:11:"Indian/Mahe";s:1:"4";s:16:"Indian/Mauritius";s:1:"4";s:10:"Asia/Kabul";s:3:"4.5";s:13:"Asia/Tashkent";s:1:"5";s:14:"Asia/Samarkand";s:1:"5";s:12:"Asia/Karachi";s:1:"5";s:9:"Asia/Oral";s:1:"5";s:16:"Indian/Kerguelen";s:1:"5";s:11:"Asia/Aqtobe";s:1:"5";s:13:"Asia/Dushanbe";s:1:"5";s:10:"Asia/Aqtau";s:1:"5";s:15:"Indian/Maldives";s:1:"5";s:13:"Asia/Ashgabat";s:1:"5";s:17:"Antarctica/Mawson";s:1:"5";s:12:"Asia/Colombo";s:3:"5.5";s:12:"Asia/Kolkata";s:3:"5.5";s:14:"Asia/Kathmandu";s:4:"5.75";s:13:"Indian/Chagos";s:1:"6";s:17:"Antarctica/Vostok";s:1:"6";s:14:"Asia/Qyzylorda";s:1:"6";s:12:"Asia/Thimphu";s:1:"6";s:18:"Asia/Yekaterinburg";s:1:"6";s:10:"Asia/Dhaka";s:1:"6";s:12:"Asia/Bishkek";s:1:"6";s:11:"Asia/Almaty";s:1:"6";s:12:"Asia/Rangoon";s:3:"6.5";s:12:"Indian/Cocos";s:3:"6.5";s:16:"Indian/Christmas";s:1:"7";s:17:"Asia/Novokuznetsk";s:1:"7";s:9:"Asia/Hovd";s:1:"7";s:14:"Asia/Pontianak";s:1:"7";s:15:"Asia/Phnom Penh";s:1:"7";s:16:"Asia/Novosibirsk";s:1:"7";s:9:"Asia/Omsk";s:1:"7";s:16:"Asia/Ho Chi Minh";s:1:"7";s:12:"Asia/Jakarta";s:1:"7";s:12:"Asia/Bangkok";s:1:"7";s:14:"Asia/Vientiane";s:1:"7";s:16:"Antarctica/Davis";s:1:"7";s:13:"Asia/Makassar";s:1:"8";s:11:"Asia/Manila";s:1:"8";s:16:"Antarctica/Casey";s:1:"8";s:15:"Australia/Perth";s:1:"8";s:10:"Asia/Macau";s:1:"8";s:17:"Asia/Kuala Lumpur";s:1:"8";s:15:"Asia/Choibalsan";s:1:"8";s:14:"Asia/Chongqing";s:1:"8";s:11:"Asia/Brunei";s:1:"8";s:12:"Asia/Kashgar";s:1:"8";s:11:"Asia/Harbin";s:1:"8";s:16:"Asia/Krasnoyarsk";s:1:"8";s:12:"Asia/Kuching";s:1:"8";s:14:"Asia/Hong Kong";s:1:"8";s:13:"Asia/Shanghai";s:1:"8";s:14:"Asia/Singapore";s:1:"8";s:11:"Asia/Taipei";s:1:"8";s:16:"Asia/Ulaanbaatar";s:1:"8";s:11:"Asia/Urumqi";s:1:"8";s:15:"Australia/Eucla";s:4:"8.75";s:13:"Pacific/Palau";s:1:"9";s:14:"Asia/Pyongyang";s:1:"9";s:10:"Asia/Tokyo";s:1:"9";s:12:"Asia/Irkutsk";s:1:"9";s:9:"Asia/Dili";s:1:"9";s:13:"Asia/Jayapura";s:1:"9";s:10:"Asia/Seoul";s:1:"9";s:16:"Australia/Darwin";s:3:"9.5";s:13:"Pacific/Chuuk";s:2:"10";s:12:"Pacific/Guam";s:2:"10";s:20:"Pacific/Port Moresby";s:2:"10";s:14:"Pacific/Saipan";s:2:"10";s:18:"Australia/Brisbane";s:2:"10";s:25:"Antarctica/DumontDUrville";s:2:"10";s:12:"Asia/Yakutsk";s:2:"10";s:18:"Australia/Lindeman";s:2:"10";s:13:"Asia/Khandyga";s:2:"10";s:18:"Australia/Adelaide";s:4:"10.5";s:21:"Australia/Broken Hill";s:4:"10.5";s:19:"Pacific/Guadalcanal";s:2:"11";s:20:"Antarctica/Macquarie";s:2:"11";s:14:"Pacific/Kosrae";s:2:"11";s:16:"Asia/Vladivostok";s:2:"11";s:19:"Australia/Melbourne";s:2:"11";s:15:"Pacific/Pohnpei";s:2:"11";s:13:"Pacific/Efate";s:2:"11";s:16:"Australia/Currie";s:2:"11";s:16:"Australia/Sydney";s:2:"11";s:13:"Asia/Sakhalin";s:2:"11";s:16:"Australia/Hobart";s:2:"11";s:19:"Australia/Lord Howe";s:2:"11";s:14:"Pacific/Noumea";s:2:"11";s:13:"Asia/Ust-Nera";s:2:"11";s:15:"Pacific/Norfolk";s:4:"11.5";s:14:"Pacific/Tarawa";s:2:"12";s:14:"Pacific/Wallis";s:2:"12";s:12:"Pacific/Wake";s:2:"12";s:16:"Pacific/Funafuti";s:2:"12";s:14:"Asia/Kamchatka";s:2:"12";s:13:"Pacific/Nauru";s:2:"12";s:17:"Pacific/Kwajalein";s:2:"12";s:14:"Pacific/Majuro";s:2:"12";s:11:"Asia/Anadyr";s:2:"12";s:12:"Asia/Magadan";s:2:"12";s:16:"Pacific/Auckland";s:2:"13";s:17:"Pacific/Tongatapu";s:2:"13";s:15:"Pacific/Fakaofo";s:2:"13";s:18:"Antarctica/McMurdo";s:2:"13";s:12:"Pacific/Fiji";s:2:"13";s:17:"Pacific/Enderbury";s:2:"13";s:15:"Pacific/Chatham";s:5:"13.75";s:12:"Pacific/Apia";s:2:"14";s:18:"Pacific/Kiritimati";s:2:"14";}
languages	a:1:{i:1;a:6:{s:2:"id";s:1:"1";s:5:"title";s:7:"English";s:9:"developer";s:28:"Tuxxedo Software Development";s:8:"isotitle";s:2:"en";s:9:"isdefault";s:1:"t";s:7:"charset";s:5:"UTF-8";}}
optioncategories	a:4:{i:0;s:8:"datetime";i:1;s:8:"language";i:2;s:7:"session";i:3;s:5:"style";}
options	a:12:{s:13:"cookie_domain";a:2:{s:8:"category";s:7:"session";s:5:"value";s:0:"";}s:14:"cookie_expires";a:2:{s:8:"category";s:7:"session";s:5:"value";i:1800;}s:11:"cookie_path";a:2:{s:8:"category";s:7:"session";s:5:"value";s:0:"";}s:13:"cookie_prefix";a:2:{s:8:"category";s:7:"session";s:5:"value";s:8:"tuxxedo_";}s:13:"cookie_secure";a:2:{s:8:"category";s:7:"session";s:5:"value";b:0;}s:11:"date_format";a:2:{s:8:"category";s:8:"datetime";s:5:"value";s:14:"H:i:s, j/n - Y";}s:13:"date_timezone";a:2:{s:8:"category";s:8:"datetime";s:5:"value";s:3:"UTC";}s:20:"date_timezone_offset";a:2:{s:8:"category";s:8:"datetime";s:5:"value";i:0;}s:19:"language_autodetect";a:2:{s:8:"category";s:8:"language";s:5:"value";b:0;}s:11:"language_id";a:2:{s:8:"category";s:8:"language";s:5:"value";i:1;}s:8:"style_id";a:2:{s:8:"category";s:5:"style";s:5:"value";i:1;}s:13:"style_storage";a:2:{s:8:"category";s:5:"style";s:5:"value";s:8:"database";}}
permissions	a:1:{s:13:"administrator";i:1;}
phrasegroups	a:1:{i:1;a:3:{s:6:"global";a:2:{s:2:"id";s:1:"1";s:7:"phrases";i:0;}s:12:"datamanagers";a:2:{s:2:"id";s:1:"2";s:7:"phrases";i:60;}s:8:"devtools";a:2:{s:2:"id";s:1:"3";s:7:"phrases";i:0;}}}
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
k5msrp93dejajnpuc522mv9cq1	0	/engine/index.php	Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36 OPR/26.0.1656.60	1421280560	f
hk38l3p5jc64go7nkgvolj5g06	0	/engine/index.php	Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36 OPR/26.0.1656.60	1421281832	t
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
6	error_listbit	<li>{$error}</li>	<li>{$error}</li>	<li>{$error}</li>	1	f	1
1	header	<?xml version="1.0" encoding="UTF-8"?>\n<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\n<html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">\n\t<head>\n\t\t<title>Tuxxedo Engine</title>\n\n\t\t<style type="text/css">\n\t\t*\n\t\t{\n\t\t\tfont-family: \t\t"Helvetica Neue", Helvetica, Trebuchet MS, Verdana, Tahoma, Arial, sans-serif;\n\t\t}\n\t\ta\n\t\t{\n\t\t\tcolor:\t\t\t#021420;\n\t\t\tfont-weight:\t\tbold;\n\t\t}\n\t\tbody\n\t\t{\n\t\t\tbackground-color: \t#021420;\n\t\t\tfont-size: \t\t82%;\n\t\t\tcolor: \t\t\t#3B7286;\n\t\t\tpadding: \t\t0px 30px;\n\t\t}\n\t\th1\n\t\t{\n\t\t\tcolor: \t\t\t#FFFFFF;\n\t\t}\n\t\tinput, .link-button\n\t\t{\n\t\t\tbackground-color:\t#EAEAEA;\n\t\t\tborder:\t\t\t0px;\n\t\t\tborder-radius: \t\t4px;\n\t\t\tpadding:\t\t3px 10px;\n\t\t}\n\t\t\tinput[type=password], input[type=text]\n\t\t\t{\n\t\t\t\tbackground-color:\t#FFFFFF;\n\t\t\t\tborder:\t\t\t1px solid #EAEAEA;\n\t\t\t}\n\t\t.box\n\t\t{\n\t\t\tbackground-color: \t#D2D2D2;\n\t\t\tborder: \t\t3px solid #D2D2D2;\n\t\t\tborder-radius: \t\t4px;\n\t\t}\n\t\t\t.box .inner\n\t\t\t{\n\t\t\t\tbackground-color: \t#FFFFFF;\n\t\t\t\tborder-radius: \t\t4px;\n\t\t\t\tpadding: \t\t6px;\n\t\t\t}\n\t\t.wrapper\n\t\t{\n\t\t\tmargin:\t\t\t0px auto;\n\t\t\twidth:\t\t\t80%;\n\t\t}\n\t\t</style>\n\t</head>\n\t<body>\n\t\t<div class="wrapper">	<?xml version=\\"1.0\\" encoding=\\"UTF-8\\"?>\n<!DOCTYPE html PUBLIC \\"-//W3C//DTD XHTML 1.0 Strict//EN\\" \\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\\">\n<html dir=\\"ltr\\" xmlns=\\"http://www.w3.org/1999/xhtml\\" xml:lang=\\"en\\" lang=\\"en\\">\n\t<head>\n\t\t<title>Tuxxedo Engine</title>\n\n\t\t<style type=\\"text/css\\">\n\t\t*\n\t\t{\n\t\t\tfont-family: \t\t\\"Helvetica Neue\\", Helvetica, Trebuchet MS, Verdana, Tahoma, Arial, sans-serif;\n\t\t}\n\t\ta\n\t\t{\n\t\t\tcolor:\t\t\t#021420;\n\t\t\tfont-weight:\t\tbold;\n\t\t}\n\t\tbody\n\t\t{\n\t\t\tbackground-color: \t#021420;\n\t\t\tfont-size: \t\t82%;\n\t\t\tcolor: \t\t\t#3B7286;\n\t\t\tpadding: \t\t0px 30px;\n\t\t}\n\t\th1\n\t\t{\n\t\t\tcolor: \t\t\t#FFFFFF;\n\t\t}\n\t\tinput, .link-button\n\t\t{\n\t\t\tbackground-color:\t#EAEAEA;\n\t\t\tborder:\t\t\t0px;\n\t\t\tborder-radius: \t\t4px;\n\t\t\tpadding:\t\t3px 10px;\n\t\t}\n\t\t\tinput[type=password], input[type=text]\n\t\t\t{\n\t\t\t\tbackground-color:\t#FFFFFF;\n\t\t\t\tborder:\t\t\t1px solid #EAEAEA;\n\t\t\t}\n\t\t.box\n\t\t{\n\t\t\tbackground-color: \t#D2D2D2;\n\t\t\tborder: \t\t3px solid #D2D2D2;\n\t\t\tborder-radius: \t\t4px;\n\t\t}\n\t\t\t.box .inner\n\t\t\t{\n\t\t\t\tbackground-color: \t#FFFFFF;\n\t\t\t\tborder-radius: \t\t4px;\n\t\t\t\tpadding: \t\t6px;\n\t\t\t}\n\t\t.wrapper\n\t\t{\n\t\t\tmargin:\t\t\t0px auto;\n\t\t\twidth:\t\t\t80%;\n\t\t}\n\t\t</style>\n\t</head>\n\t<body>\n\t\t<div class=\\"wrapper\\">	<?xml version="1.0" encoding="UTF-8"?>\n<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">\n<html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">\n\t<head>\n\t\t<title>Tuxxedo Engine</title>\n\n\t\t<style type="text/css">\n\t\t*\n\t\t{\n\t\t\tfont-family: \t\t"Helvetica Neue", Helvetica, Trebuchet MS, Verdana, Tahoma, Arial, sans-serif;\n\t\t}\n\t\ta\n\t\t{\n\t\t\tcolor:\t\t\t#021420;\n\t\t\tfont-weight:\t\tbold;\n\t\t}\n\t\tbody\n\t\t{\n\t\t\tbackground-color: \t#021420;\n\t\t\tfont-size: \t\t82%;\n\t\t\tcolor: \t\t\t#3B7286;\n\t\t\tpadding: \t\t0px 30px;\n\t\t}\n\t\th1\n\t\t{\n\t\t\tcolor: \t\t\t#FFFFFF;\n\t\t}\n\t\tinput, .link-button\n\t\t{\n\t\t\tbackground-color:\t#EAEAEA;\n\t\t\tborder:\t\t\t0px;\n\t\t\tborder-radius: \t\t4px;\n\t\t\tpadding:\t\t3px 10px;\n\t\t}\n\t\t\tinput[type=password], input[type=text]\n\t\t\t{\n\t\t\t\tbackground-color:\t#FFFFFF;\n\t\t\t\tborder:\t\t\t1px solid #EAEAEA;\n\t\t\t}\n\t\t.box\n\t\t{\n\t\t\tbackground-color: \t#D2D2D2;\n\t\t\tborder: \t\t3px solid #D2D2D2;\n\t\t\tborder-radius: \t\t4px;\n\t\t}\n\t\t\t.box .inner\n\t\t\t{\n\t\t\t\tbackground-color: \t#FFFFFF;\n\t\t\t\tborder-radius: \t\t4px;\n\t\t\t\tpadding: \t\t6px;\n\t\t\t}\n\t\t.wrapper\n\t\t{\n\t\t\tmargin:\t\t\t0px auto;\n\t\t\twidth:\t\t\t80%;\n\t\t}\n\t\t</style>\n\t</head>\n\t<body>\n\t\t<div class="wrapper">	1	f	2
4	error	{$header}\n\n<h1>Error</h1>\n<div class="box">\n\t<div class="inner">\n\t\t{$message}\n\n\t\t<if expression="isset($error_list)">\n\t\t<ul>\n\t\t\t{$error_list}\n\t\t</ul>\n\t\t</if>\n\t</div>\n</div>\n\n<if expression="isset($go_back) && $go_back">\n<br />\n\n<div class="box">\n\t<div class="inner">\n\t\t<input type="button" onclick="history.back(-1);" value="Go back" />\n\t</div>\n</div>\n</if>\n\n{$footer}	{$header}\n\n<h1>Error</h1>\n<div class=\\"box\\">\n\t<div class=\\"inner\\">\n\t\t{$message}\n\n\t\t" . ((isset($error_list)) ? ("\n\t\t<ul>\n\t\t\t{$error_list}\n\t\t</ul>\n\t\t") : '') . "\n\t</div>\n</div>\n\n" . ((isset($go_back) && $go_back) ? ("\n<br />\n\n<div class=\\"box\\">\n\t<div class=\\"inner\\">\n\t\t<input type=\\"button\\" onclick=\\"history.back(-1);\\" value=\\"Go back\\" />\n\t</div>\n</div>\n") : '') . "\n\n{$footer}	{$header}\n\n<h1>Error</h1>\n<div class="box">\n\t<div class="inner">\n\t\t{$message}\n\n\t\t<if expression="isset($error_list)">\n\t\t<ul>\n\t\t\t{$error_list}\n\t\t</ul>\n\t\t</if>\n\t</div>\n</div>\n\n<if expression="isset($go_back) && $go_back">\n<br />\n\n<div class="box">\n\t<div class="inner">\n\t\t<input type="button" onclick="history.back(-1);" value="Go back" />\n\t</div>\n</div>\n</if>\n\n{$footer}	1	f	2
2	footer	\t\t</div>\n\t</body>\n</html>	\t\t</div>\n\t</body>\n</html>	\t\t</div>\n\t</body>\n</html>	1	f	2
5	index	{$header}\n\n<h1>Tuxxedo Engine</h1>\n<div class="box">\n\t<div class="inner">\n\t\tThank you for choosing Tuxxedo Engine, version {$version} is installed \n\t\tand ready to use.\n\n\t\t<p>\n\t\t\tTo begin developing, head over to the DevTools component, this component \n\t\t\tis as the name sounds, designed to ease development of Engine based \n\t\t\tapplications. If you are interested in how Engine is developed, head over to \n\t\t\tour blog and our project site.\n\t\t</p>\n\n\t\t<p>\n\t\t\tRemember to checkout the 'configuration.php' file, to define the application \n\t\t\tvariables. Debugging mode can also be enabled/disabled here. The debug mode \n\t\t\tshould always be enabled when working on a development server to give to \n\t\t\tmore expressive error messages, and always turned off when the application \n\t\t\tis deployed to production servers.\n\t\t</p>\n\n\t\t<a class="link-button" href="./dev/tools/">DevTools</a> \n\t\t<a class="link-button" href="http://www.tuxxedo.net/devblog/">Blog</a> \n\t\t<a class="link-button" href="http://code.google.com/p/tuxxedo">Project</a>\n\t</div>\n</div>\n\n{$footer}	{$header}\n\n<h1>Tuxxedo Engine</h1>\n<div class=\\"box\\">\n\t<div class=\\"inner\\">\n\t\tThank you for choosing Tuxxedo Engine, version {$version} is installed \n\t\tand ready to use.\n\n\t\t<p>\n\t\t\tTo begin developing, head over to the DevTools component, this component \n\t\t\tis as the name sounds, designed to ease development of Engine based \n\t\t\tapplications. If you are interested in how Engine is developed, head over to \n\t\t\tour blog and our project site.\n\t\t</p>\n\n\t\t<p>\n\t\t\tRemember to checkout the 'configuration.php' file, to define the application \n\t\t\tvariables. Debugging mode can also be enabled/disabled here. The debug mode \n\t\t\tshould always be enabled when working on a development server to give to \n\t\t\tmore expressive error messages, and always turned off when the application \n\t\t\tis deployed to production servers.\n\t\t</p>\n\n\t\t<a class=\\"link-button\\" href=\\"./dev/tools/\\">DevTools</a> \n\t\t<a class=\\"link-button\\" href=\\"http://www.tuxxedo.net/devblog/\\">Blog</a> \n\t\t<a class=\\"link-button\\" href=\\"http://code.google.com/p/tuxxedo\\">Project</a>\n\t</div>\n</div>\n\n{$footer}	{$header}\n\n<h1>Tuxxedo Engine</h1>\n<div class=\\"box\\">\n\t<div class=\\"inner\\">\n\t\tThank you for choosing Tuxxedo Engine, version {$version} is installed \n\t\tand ready to use.\n\n\t\t<p>\n\t\t\tTo begin developing, head over to the DevTools component, this component \n\t\t\tis as the name sounds, designed to ease development of Engine based \n\t\t\tapplications. If you are interested in how Engine is developed, head over to \n\t\t\tour blog and our project site.\n\t\t</p>\n\n\t\t<p>\n\t\t\tRemember to checkout the 'configuration.php' file, to define the application \n\t\t\tvariables. Debugging mode can also be enabled/disabled here. The debug mode \n\t\t\tshould always be enabled when working on a development server to give to \n\t\t\tmore expressive error messages, and always turned off when the application \n\t\t\tis deployed to production servers.\n\t\t</p>\n\n\t\t<a class=\\"link-button\\" href=\\"./dev/tools/\\">DevTools</a> \n\t\t<a class=\\"link-button\\" href=\\"http://www.tuxxedo.net/devblog/\\">Blog</a> \n\t\t<a class=\\"link-button\\" href=\\"http://code.google.com/p/tuxxedo\\">Project</a>\n\t</div>\n</div>\n\n{$footer}	1	f	2
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

