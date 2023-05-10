--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.3
-- Dumped by pg_dump version 9.1.3
-- Started on 2014-02-21 15:31:15 ECT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- TOC entry 175 (class 3079 OID 11721)
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- TOC entry 2022 (class 0 OID 0)
-- Dependencies: 175
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- TOC entry 187 (class 1255 OID 55846)
-- Dependencies: 5 538
-- Name: func_grabar_archivo(text, text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_grabar_archivo(var_nombre_archivo text, var_archivo_base_64 text) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE	
    var_sql text;
    var_md5 text;
    var_tamanio numeric;
    var_arch_codi bigint;
    var_nombre_tabla text;
    var_indi_codi integer;
    var_recordset record;
    arr_tablas text[];
    var_num_tablas integer := 0;
    var_num_tabla_rr integer;
    var_t1 record; var_t2 record; var_t3 record; var_t4 record; var_t5 record; var_t6 record;
BEGIN
    BEGIN
	EXECUTE 'select clock_timestamp() as t' into var_t1;
        var_md5 := ''; --md5(var_archivo_base_64);
--        var_md5 := md5(var_archivo_base_64);
--        SELECT arch_codi from archivo where arch_md5=var_md5 and estado=1 limit 1 INTO var_recordset;
--        IF var_recordset is not null THEN return var_recordset.arch_codi; END IF;
        
        FOR var_recordset IN select indi_codi, nombre_tabla from indice where esta_codi = 2 order by indi_codi asc LOOP
            IF arr_tablas is null THEN
                arr_tablas := ARRAY[[var_recordset.indi_codi::text, var_recordset.nombre_tabla]];
            ELSE
                arr_tablas := array_cat(arr_tablas, ARRAY[var_recordset.indi_codi::text, var_recordset.nombre_tabla]);
            END IF;
            var_num_tablas := var_num_tablas + 1;
        END LOOP;
        IF var_num_tablas=0 THEN return 0; END IF;

        var_tamanio := length(var_archivo_base_64)/8*6;
        var_arch_codi := nextval('sec_archivo'::regclass);
        
        -- Calculamos la tabla en la que se va a insertar el registro (tipo round robin) y validamos que esté activa
        var_num_tabla_rr = (var_arch_codi % var_num_tablas) + 1;
        IF NOT func_validar_bloqueo_tabla(arr_tablas[var_num_tabla_rr][2]) THEN
            var_num_tabla_rr := 1;
            WHILE var_num_tabla_rr <= var_num_tablas and NOT func_validar_bloqueo_tabla(arr_tablas[var_num_tabla_rr][2]) LOOP
                var_num_tabla_rr := var_num_tabla_rr + 1;
            END LOOP;
        END IF;
        IF trim(arr_tablas[var_num_tabla_rr][2]) is null THEN return 0; END IF;
        var_nombre_tabla := arr_tablas[var_num_tabla_rr][2];
        var_indi_codi := arr_tablas[var_num_tabla_rr][1];

        var_sql := 'INSERT INTO archivo (arch_codi, indi_codi, nombre, fecha_creacion, tamanio, arch_md5)
                    VALUES ('||var_arch_codi::text||', '||var_indi_codi::text||', '
                             ||quote_literal(var_nombre_archivo)||', now(), '
                             ||var_tamanio::text||', '||quote_literal(var_md5)||')';
        EXECUTE var_sql;
--	EXECUTE 'select clock_timestamp() as t' into var_t5;
            
        var_sql := 'INSERT INTO '||var_nombre_tabla||' (arch_codi, archivo) 
                    VALUES ('||var_arch_codi::text||', '||quote_literal(var_archivo_base_64)||')';
        EXECUTE var_sql;
	EXECUTE 'select clock_timestamp() as t' into var_t6;

--	insert into log_tiempo_guardar (arch_codi, tamanio, t1, t2, t3, t4, t5, t6) values (var_arch_codi, var_tamanio, var_t1.t, var_t2.t, var_t3.t, var_t4.t, var_t5.t, var_t6.t);
	insert into log_tiempo_guardar (arch_codi, tamanio, t1, t6) values (var_arch_codi, var_tamanio, var_t1.t, var_t6.t);
        
        return var_arch_codi;
    EXCEPTION WHEN OTHERS THEN
        PERFORM func_log_archivo (var_sql, SQLERRM);
        return 0;
    END;
END;
$$;


--
-- TOC entry 188 (class 1255 OID 55847)
-- Dependencies: 5 538
-- Name: func_log_archivo(text, text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_log_archivo(var_sentencia text, var_error text DEFAULT NULL::text) RETURNS integer
    LANGUAGE plpgsql
    AS $$
BEGIN
    BEGIN
        insert into log_archivo (sentencia, error, fecha) values (var_sentencia, var_error, now());
    EXCEPTION WHEN OTHERS THEN
        RAISE NOTICE 'Error al guardar en el log. %',SQLERRM;
    END;
    return 1;
END;
$$;


--
-- TOC entry 189 (class 1255 OID 55848)
-- Dependencies: 538 5
-- Name: func_recuperar_archivo(bigint); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_recuperar_archivo(var_arch_codi bigint) RETURNS text
    LANGUAGE plpgsql
    AS $$
DECLARE	
    var_sql text;
    var_recordset record;
BEGIN
    BEGIN
        SELECT i.nombre_tabla
        FROM archivo a LEFT OUTER JOIN indice i ON a.indi_codi=i.indi_codi
        WHERE a.arch_codi=var_arch_codi
        INTO var_recordset;
        IF var_recordset is null THEN return ''; END IF;
    
        var_sql := 'select archivo from '||coalesce(var_recordset.nombre_tabla,'')||' where arch_codi='||var_arch_codi::text;
        EXECUTE var_sql INTO var_recordset;
        
        RETURN var_recordset.archivo;
    EXCEPTION WHEN OTHERS THEN
        PERFORM func_log_archivo ( quote_literal(var_sql), quote_literal(SQLERRM));
        return '';
    END;
END;
$$;


--
-- TOC entry 190 (class 1255 OID 55849)
-- Dependencies: 538 5
-- Name: func_validar_bloqueo_tabla(text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_validar_bloqueo_tabla(var_nombre_tabla text) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
DECLARE	
    var_sql text;
    var_bloqueo record;
BEGIN
    BEGIN
        select mode as tipo_bloqueo from pg_locks 
	where relation=(select relfilenode from pg_class where relname=var_nombre_tabla) 
	    and mode not in ('AccessShareLock','RowShareLock','RowExclusiveLock') limit 1 INTO var_bloqueo;
        IF var_bloqueo is not null THEN 
            var_sql := 'insert into log_bloqueo (tabla, tipo_bloqueo, fecha) values ('||quote_literal(var_nombre_tabla)||', '||quote_literal(var_bloqueo.tipo_bloqueo)||', now());';
            EXECUTE var_sql;
	    RETURN FALSE;
        END IF;
        RETURN TRUE;
    EXCEPTION WHEN OTHERS THEN
        PERFORM func_log_archivo (var_sql, SQLERRM);
        RETURN FALSE;
    END;
END;
$$;


SET default_with_oids = false;

--
-- TOC entry 161 (class 1259 OID 55850)
-- Dependencies: 1978 5
-- Name: archivo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE archivo (
    arch_codi bigint NOT NULL,
    nombre character varying(500),
    fecha_creacion timestamp with time zone,
    tamanio bigint,
    arch_md5 character(32),
    indi_codi integer,
    estado smallint DEFAULT 1
);


--
-- TOC entry 162 (class 1259 OID 55959)
-- Dependencies: 5
-- Name: estado_indice; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE estado_indice (
    esta_codi smallint NOT NULL,
    nombre character varying(500)
);


--
-- TOC entry 163 (class 1259 OID 55962)
-- Dependencies: 1979 1980 1981 1982 1983 1984 5
-- Name: indice; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE indice (
    indi_codi integer NOT NULL,
    arch_codi_inicio bigint DEFAULT 0,
    arch_codi_fin bigint DEFAULT 0,
    tamanio bigint DEFAULT 0,
    tamanio_maximo bigint DEFAULT 2097152,
    esta_codi smallint DEFAULT 0,
    nombre_tabla character varying(100),
    nombre_tablespace character varying(100),
    fecha_creacion timestamp with time zone DEFAULT now(),
    fecha_activacion timestamp with time zone,
    fecha_cierre timestamp with time zone,
    usua_codi_crea integer,
    usua_codi_activa integer,
    usua_codi_cierra integer
);


--
-- TOC entry 164 (class 1259 OID 55971)
-- Dependencies: 5
-- Name: log; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log (
    log_id bigint NOT NULL,
    fecha timestamp with time zone,
    usua_codi integer,
    tabla character varying(100),
    sentencia character varying,
    tipo smallint
);


--
-- TOC entry 165 (class 1259 OID 55977)
-- Dependencies: 5
-- Name: sec_log_archivo; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_log_archivo
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2023 (class 0 OID 0)
-- Dependencies: 165
-- Name: sec_log_archivo; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_log_archivo', 1, false);


--
-- TOC entry 166 (class 1259 OID 55979)
-- Dependencies: 1986 5
-- Name: log_archivo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_archivo (
    log_id bigint DEFAULT nextval('sec_log_archivo'::regclass) NOT NULL,
    sentencia character varying,
    error character varying,
    fecha timestamp with time zone
);


--
-- TOC entry 167 (class 1259 OID 55986)
-- Dependencies: 5
-- Name: sec_log_bloqueo; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_log_bloqueo
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2024 (class 0 OID 0)
-- Dependencies: 167
-- Name: sec_log_bloqueo; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_log_bloqueo', 1, false);


--
-- TOC entry 168 (class 1259 OID 55988)
-- Dependencies: 1987 5
-- Name: log_bloqueo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_bloqueo (
    log_id bigint DEFAULT nextval('sec_log_bloqueo'::regclass) NOT NULL,
    tabla character varying,
    tipo_bloqueo character varying,
    fecha timestamp with time zone
);


--
-- TOC entry 169 (class 1259 OID 55995)
-- Dependencies: 5 164
-- Name: log_log_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE log_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2025 (class 0 OID 0)
-- Dependencies: 169
-- Name: log_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE log_log_id_seq OWNED BY log.log_id;


--
-- TOC entry 2026 (class 0 OID 0)
-- Dependencies: 169
-- Name: log_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('log_log_id_seq', 1, false);


--
-- TOC entry 170 (class 1259 OID 55997)
-- Dependencies: 5
-- Name: log_tiempo_guardar; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_tiempo_guardar (
    arch_codi bigint NOT NULL,
    tamanio bigint,
    t1 timestamp without time zone,
    t2 timestamp without time zone,
    t3 timestamp without time zone,
    t4 timestamp without time zone,
    t5 timestamp without time zone,
    t6 timestamp without time zone
);


--
-- TOC entry 171 (class 1259 OID 56000)
-- Dependencies: 5
-- Name: sec_archivo; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_archivo
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2027 (class 0 OID 0)
-- Dependencies: 171
-- Name: sec_archivo; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_archivo', 1, false);


--
-- TOC entry 172 (class 1259 OID 56002)
-- Dependencies: 5
-- Name: tmp_revertir; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE tmp_revertir (
    arch_codi bigint NOT NULL
);


--
-- TOC entry 173 (class 1259 OID 56005)
-- Dependencies: 1988 5
-- Name: tmp_tiempo_insert; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE tmp_tiempo_insert (
    arch_codi bigint,
    tiempo double precision,
    fecha timestamp without time zone DEFAULT now()
);


--
-- TOC entry 174 (class 1259 OID 56009)
-- Dependencies: 1989 5
-- Name: tmp_tiempo_read; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE tmp_tiempo_read (
    arch_codi bigint,
    tiempo double precision,
    fecha timestamp without time zone DEFAULT now()
);


--
-- TOC entry 1985 (class 2604 OID 56013)
-- Dependencies: 169 164
-- Name: log_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY log ALTER COLUMN log_id SET DEFAULT nextval('log_log_id_seq'::regclass);


--
-- TOC entry 2008 (class 0 OID 55850)
-- Dependencies: 161
-- Data for Name: archivo; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2009 (class 0 OID 55959)
-- Dependencies: 162
-- Data for Name: estado_indice; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO estado_indice (esta_codi, nombre) VALUES (1, 'Nuevo repositorio');
INSERT INTO estado_indice (esta_codi, nombre) VALUES (2, 'Repositorio activo');
INSERT INTO estado_indice (esta_codi, nombre) VALUES (3, 'Repositorio cerrado');


--
-- TOC entry 2010 (class 0 OID 55962)
-- Dependencies: 163
-- Data for Name: indice; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2011 (class 0 OID 55971)
-- Dependencies: 164
-- Data for Name: log; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2012 (class 0 OID 55979)
-- Dependencies: 166
-- Data for Name: log_archivo; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2013 (class 0 OID 55988)
-- Dependencies: 168
-- Data for Name: log_bloqueo; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2014 (class 0 OID 55997)
-- Dependencies: 170
-- Data for Name: log_tiempo_guardar; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2015 (class 0 OID 56002)
-- Dependencies: 172
-- Data for Name: tmp_revertir; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2016 (class 0 OID 56005)
-- Dependencies: 173
-- Data for Name: tmp_tiempo_insert; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2017 (class 0 OID 56009)
-- Dependencies: 174
-- Data for Name: tmp_tiempo_read; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 1992 (class 2606 OID 56015)
-- Dependencies: 161 161
-- Name: pk_archivo; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY archivo
    ADD CONSTRAINT pk_archivo PRIMARY KEY (arch_codi);


--
-- TOC entry 1994 (class 2606 OID 56051)
-- Dependencies: 162 162
-- Name: pk_estado_indice; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY estado_indice
    ADD CONSTRAINT pk_estado_indice PRIMARY KEY (esta_codi);


--
-- TOC entry 1996 (class 2606 OID 56053)
-- Dependencies: 163 163
-- Name: pk_indice; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY indice
    ADD CONSTRAINT pk_indice PRIMARY KEY (indi_codi);


--
-- TOC entry 1998 (class 2606 OID 56055)
-- Dependencies: 164 164
-- Name: pk_log; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log
    ADD CONSTRAINT pk_log PRIMARY KEY (log_id);


--
-- TOC entry 2000 (class 2606 OID 56057)
-- Dependencies: 166 166
-- Name: pk_log_archivo; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_archivo
    ADD CONSTRAINT pk_log_archivo PRIMARY KEY (log_id);


--
-- TOC entry 2002 (class 2606 OID 56059)
-- Dependencies: 168 168
-- Name: pk_log_bloqueo; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_bloqueo
    ADD CONSTRAINT pk_log_bloqueo PRIMARY KEY (log_id);


--
-- TOC entry 2004 (class 2606 OID 56061)
-- Dependencies: 170 170
-- Name: pk_log_tiemp_grabar; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_tiempo_guardar
    ADD CONSTRAINT pk_log_tiemp_grabar PRIMARY KEY (arch_codi);


--
-- TOC entry 2006 (class 2606 OID 56063)
-- Dependencies: 172 172
-- Name: pk_tmp_revertir; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tmp_revertir
    ADD CONSTRAINT pk_tmp_revertir PRIMARY KEY (arch_codi);


--
-- TOC entry 1990 (class 1259 OID 56064)
-- Dependencies: 161
-- Name: idx_archivo_md5; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_archivo_md5 ON archivo USING btree (arch_md5);


--
-- TOC entry 2007 (class 2606 OID 56125)
-- Dependencies: 1993 162 163
-- Name: fk_indice_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY indice
    ADD CONSTRAINT fk_indice_01 FOREIGN KEY (esta_codi) REFERENCES estado_indice(esta_codi);


-- Completed on 2014-02-21 15:31:15 ECT

--
-- PostgreSQL database dump complete
--

