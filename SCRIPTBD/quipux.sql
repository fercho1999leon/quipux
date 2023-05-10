--
-- PostgreSQL database dump
--

-- Dumped from database version 9.1.3
-- Dumped by pg_dump version 9.1.3
-- Started on 2014-02-21 16:32:56 ECT

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- TOC entry 299 (class 3079 OID 11721)
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- TOC entry 2976 (class 0 OID 0)
-- Dependencies: 299
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- TOC entry 302 (class 3079 OID 52913)
-- Dependencies: 5
-- Name: pg_buffercache; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_buffercache WITH SCHEMA public;


--
-- TOC entry 2977 (class 0 OID 0)
-- Dependencies: 302
-- Name: EXTENSION pg_buffercache; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pg_buffercache IS 'examine the shared buffer cache';


--
-- TOC entry 301 (class 3079 OID 52919)
-- Dependencies: 5
-- Name: pg_trgm; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA public;


--
-- TOC entry 2978 (class 0 OID 0)
-- Dependencies: 301
-- Name: EXTENSION pg_trgm; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pg_trgm IS 'text similarity measurement and index searching based on trigrams';


--
-- TOC entry 300 (class 3079 OID 52966)
-- Dependencies: 5
-- Name: unaccent; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS unaccent WITH SCHEMA public;


--
-- TOC entry 2979 (class 0 OID 0)
-- Dependencies: 300
-- Name: EXTENSION unaccent; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION unaccent IS 'text search dictionary that removes accents';


SET search_path = public, pg_catalog;

--
-- TOC entry 338 (class 1255 OID 52974)
-- Dependencies: 5
-- Name: concat(text, text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION concat(text, text) RETURNS text
    LANGUAGE sql
    AS $_$select case when $1 = '' then $2 else ($1 || ', ' || $2) end$_$;


--
-- TOC entry 2980 (class 0 OID 0)
-- Dependencies: 338
-- Name: FUNCTION concat(text, text); Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON FUNCTION concat(text, text) IS 'Concatena dos cadenas de texto';


--
-- TOC entry 339 (class 1255 OID 52978)
-- Dependencies: 5 1001
-- Name: crear_lista_usuarios_institucion(text, text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION crear_lista_usuarios_institucion(text, text) RETURNS text
    LANGUAGE plpgsql
    AS $_$
DECLARE
    fila RECORD;
    numero integer;
Begin
    numero := 1;
    execute E'delete from lista_usuarios where lista_codi='||$2;
    BEGIN
        for fila in execute E'select usua_codi from usuarios where inst_codi='||$1||' and usua_esta=1 order by usua_apellido' loop
            execute E'insert into lista_usuarios (lista_codi, usua_codi, orden) values ('||$2||','||fila.usua_codi||','||numero||')';
            numero := numero + 1;
         end loop;
    EXCEPTION WHEN OTHERS THEN
        return 'Error';
    END;
    return 'OK';
end;
$_$;


--
-- TOC entry 2981 (class 0 OID 0)
-- Dependencies: 339
-- Name: FUNCTION crear_lista_usuarios_institucion(text, text); Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON FUNCTION crear_lista_usuarios_institucion(text, text) IS 'Añade todos los usuarios de una institución a una lista.
Parámetros: Id de la institución, Id de la lista';


--
-- TOC entry 340 (class 1255 OID 52987)
-- Dependencies: 1001 5
-- Name: func_actualizar_view_usuario_ciudad(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_actualizar_view_usuario_ciudad() RETURNS trigger
    LANGUAGE plpgsql
    AS $$ 
DECLARE 
BEGIN 
    BEGIN 
        UPDATE usuario SET usua_ciudad=NEW.nombre WHERE ciu_codi=NEW.id; 
    EXCEPTION WHEN OTHERS THEN 
        INSERT INTO log_view_usuario (fecha, tabla, accion, codigo, error) VALUES (now(), 'ciudad', TG_OP, NEW.id, SQLERRM); 
    END; 
    RETURN NULL; 
END; 
$$;


--
-- TOC entry 342 (class 1255 OID 52988)
-- Dependencies: 5 1001
-- Name: func_actualizar_view_usuario_ciudadano(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_actualizar_view_usuario_ciudadano() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    var_usua_codi integer;
BEGIN
    BEGIN
        IF TG_OP = 'DELETE' THEN -- Cuando se pasa un ciudadano a la tabla funcionario (ciudadanos con firma electrónica)
            var_usua_codi := OLD.ciu_codigo;
	    DELETE FROM usuario WHERE usua_codi=OLD.ciu_codigo;
	END IF;
	
        IF TG_OP = 'UPDATE' THEN
            var_usua_codi := OLD.ciu_codigo;
	    UPDATE usuario 
	    SET   usua_cedula = NEW.ciu_cedula
	        , usua_nomb   = NEW.ciu_nombre
	        , usua_apellido = NEW.ciu_apellido
	        , usua_nombre = TRIM(COALESCE(NEW.ciu_nombre::text, ''::text) || ' '::text || COALESCE(NEW.ciu_apellido::text, ''::text))
	        , usua_nuevo  = NEW.ciu_nuevo 
	        , usua_login  = CASE WHEN NEW.ciu_estado=1 THEN 'U'::text || NEW.ciu_cedula::text ELSE 'l'::text || OLD.ciu_codigo::text END
	        , usua_pasw   = NEW.ciu_pasw 
	        , usua_esta   = NEW.ciu_estado
	        , usua_cargo  = NEW.ciu_cargo
	        , usua_cargo_cabecera = NEW.ciu_cargo
	        , usua_email  = NEW.ciu_email
	        , usua_titulo = NEW.ciu_titulo
	        , usua_abr_titulo = NEW.ciu_abr_titulo
	        , inst_nombre = NEW.ciu_empresa
	        , usua_direccion = NEW.ciu_direccion
	        , usua_telefono = NEW.ciu_telefono
	        , ciu_codi = COALESCE(NEW.ciudad_codi, 1)
	        , usua_ciudad = (SELECT c.nombre FROM ciudad c WHERE COALESCE(NEW.ciudad_codi, 1) = c.id)
	        , cargo_tipo  = 0
	        , depe_codi   = 0 
	        , depe_nomb   = ''
	        , dep_sigla = NULL
	        , inst_codi   = 0
	        , inst_sigla  = ''
	        , inst_estado = 1
	        , tipo_usuario = 2
	        , usua_tipo_certificado = 0
	        , usua_subrogado = 0
	        , visible_sub = 0
	        , usua_firma_path = ''
                , usua_datos = translate(UPPER(coalesce(NEW.ciu_cedula,'')||' '||coalesce(NEW.ciu_nombre,'')
                  ||' '||coalesce(NEW.ciu_apellido,'')||' '||coalesce(NEW.ciu_cargo,'')||' '||coalesce(NEW.ciu_email,'')
                  ||' '||coalesce(NEW.ciu_empresa,'')),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÂÊÎÔÛÃÕÑ','AEIOUAEIOUAEIOUAEIOUAON')
                , inst_adscrita = 0
                , inst_padre_nombre = 'Ciudadanos'
                , inst_padre_sigla = 'CIUDADANO'
	    WHERE usua_codi = OLD.ciu_codigo;
	END IF;

        IF TG_OP = 'INSERT' THEN
            var_usua_codi := NEW.ciu_codigo;
	    INSERT INTO usuario (
	        usua_codi, usua_cargo, usua_cargo_cabecera, usua_nuevo, usua_login, usua_pasw, usua_esta, usua_cedula, usua_nomb, usua_apellido, usua_nombre
	         , usua_email, usua_titulo, usua_abr_titulo, inst_nombre, usua_direccion, usua_telefono, ciu_codi, usua_ciudad
	         , cargo_tipo, depe_codi, inst_estado, inst_codi, depe_nomb, inst_sigla, tipo_usuario, usua_tipo_certificado
	         , usua_subrogado, visible_sub, dep_sigla, usua_firma_path, usua_datos, inst_adscrita, inst_padre_nombre, inst_padre_sigla
	    ) VALUES (
	        NEW.ciu_codigo, NEW.ciu_cargo, NEW.ciu_cargo, NEW.ciu_nuevo
	        , CASE WHEN NEW.ciu_estado=1 THEN 'U'::text || NEW.ciu_cedula::text ELSE 'l'::text || NEW.ciu_codigo::text END
	        , NEW.ciu_pasw, NEW.ciu_estado, NEW.ciu_cedula, NEW.ciu_nombre, NEW.ciu_apellido
	        , TRIM(COALESCE(NEW.ciu_nombre::text, ''::text) || ' '::text || COALESCE(NEW.ciu_apellido::text, ''::text))
	        , NEW.ciu_email, NEW.ciu_titulo, NEW.ciu_abr_titulo, NEW.ciu_empresa, NEW.ciu_direccion, NEW.ciu_telefono
	        , COALESCE(NEW.ciudad_codi, 1), (SELECT c.nombre FROM ciudad c WHERE COALESCE(NEW.ciudad_codi, 1) = c.id)
	        , 0, 0, 1, 0, '', '', 2, 0, 0, 0, NULL, ''
	        , translate(UPPER(coalesce(NEW.ciu_cedula,'')||' '||coalesce(NEW.ciu_nombre,'')
                    ||' '||coalesce(NEW.ciu_apellido,'')||' '||coalesce(NEW.ciu_cargo,'')||' '||coalesce(NEW.ciu_email,'')
                    ||' '||coalesce(NEW.ciu_empresa,'')),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÂÊÎÔÛÃÕÑ','AEIOUAEIOUAEIOUAEIOUAON')
                , 0, 'Ciudadanos', 'CIUDADANO'
	    );
	END IF;

    EXCEPTION WHEN OTHERS THEN
        INSERT INTO log_view_usuario (fecha, tabla, accion, codigo, error) VALUES (now(), 'ciudadano', TG_OP, var_usua_codi, SQLERRM);
    END;
    RETURN NULL;
END;
$$;


--
-- TOC entry 343 (class 1255 OID 52989)
-- Dependencies: 5 1001
-- Name: func_actualizar_view_usuario_dependencia(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_actualizar_view_usuario_dependencia() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    var_recordset record;
BEGIN
    BEGIN
        -- Consultamos los datos de la institucion adscrita
        SELECT inst_nombre, inst_sigla FROM institucion where inst_codi=NEW.inst_adscrita INTO var_recordset;

        UPDATE usuario 
        SET   depe_nomb=NEW.depe_nomb
            , dep_sigla=NEW.dep_sigla 
            , inst_adscrita=NEW.inst_adscrita
            , inst_nombre = var_recordset.inst_nombre
            , inst_sigla = var_recordset.inst_sigla
            , usua_datos = translate(UPPER(coalesce(usua_cedula,'')||' '||coalesce(usua_nombre,'')||' '||coalesce(usua_cargo,'')
                  ||' '||coalesce(usua_email,'')||' '||coalesce(NEW.depe_nomb,'')
                  ||' '||coalesce(var_recordset.inst_nombre,'')||' '||coalesce(var_recordset.inst_sigla,'')
              ),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÂÊÎÔÛÃÕÑ','AEIOUAEIOUAEIOUAEIOUAON')
        WHERE depe_codi = NEW.depe_codi;
    EXCEPTION WHEN OTHERS THEN
        INSERT INTO log_view_usuario (fecha, tabla, accion, codigo, error) VALUES (now(), 'dependencia', TG_OP, NEW.depe_codi, SQLERRM);
    END;
    RETURN NULL;
END;
$$;


--
-- TOC entry 344 (class 1255 OID 52990)
-- Dependencies: 5 1001
-- Name: func_actualizar_view_usuario_institucion(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_actualizar_view_usuario_institucion() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
BEGIN
    BEGIN
        UPDATE usuario 
        SET   
            -- Si modifico la institución padre
              inst_estado=case when inst_codi=NEW.inst_codi then NEW.inst_estado else inst_estado end
            , inst_padre_nombre=case when inst_codi=NEW.inst_codi then NEW.inst_nombre else inst_padre_nombre end
            , inst_padre_sigla=case when inst_codi=NEW.inst_codi then NEW.inst_sigla else inst_padre_sigla end
            -- Si modifico la institución adscrita
            , inst_nombre=case when inst_adscrita=NEW.inst_codi then NEW.inst_nombre else inst_nombre end
            , inst_sigla=case when inst_adscrita=NEW.inst_codi then NEW.inst_sigla else inst_sigla end
            , usua_datos = translate(UPPER(coalesce(usua_cedula,'')||' '||coalesce(usua_nombre,'')||' '||coalesce(usua_cargo,'')
                  ||' '||coalesce(usua_email,'')||' '||coalesce(depe_nomb,'')||' '||
                  case when inst_adscrita=NEW.inst_codi then coalesce(NEW.inst_nombre,'')||' '||coalesce(NEW.inst_sigla,'') 
                       else coalesce(inst_nombre,'')||' '||coalesce(inst_sigla,'') end
              ),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÂÊÎÔÛÃÕÑ','AEIOUAEIOUAEIOUAEIOUAON')
        WHERE inst_codi = NEW.inst_codi or inst_adscrita=NEW.inst_codi;
    EXCEPTION WHEN OTHERS THEN
        INSERT INTO log_view_usuario (fecha, tabla, accion, codigo, error) VALUES (now(), 'institucion', TG_OP, NEW.depe_codi, SQLERRM);
    END;
    RETURN NULL;
END;
$$;


--
-- TOC entry 345 (class 1255 OID 52991)
-- Dependencies: 5 1001
-- Name: func_actualizar_view_usuario_usuarios(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_actualizar_view_usuario_usuarios() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    var_recordset record;
BEGIN
    BEGIN
        -- Consultamos los datos de la institucion, del área y de la ciudad para insertarlos luego
        SELECT u.usua_codi, d.depe_nomb, d.dep_sigla, i.inst_estado, ia.inst_sigla
            , CASE WHEN i.inst_codi = 1 THEN NEW.inst_nombre ELSE ia.inst_nombre END AS inst_nombre
            , d.inst_adscrita, i.inst_sigla as inst_padre_sigla, i.inst_nombre as inst_padre_nombre
            , COALESCE(NEW.ciu_codi, COALESCE(d.depe_pie1,'1')::integer) AS ciu_codi
	    , (SELECT c.nombre FROM ciudad c WHERE COALESCE(NEW.ciu_codi, COALESCE(d.depe_pie1,'1')::integer)=c.id) AS usua_ciudad
	FROM (SELECT NEW.usua_codi as usua_codi, coalesce(NEW.inst_codi,0) as inst_codi, coalesce(NEW.depe_codi,0) as depe_codi) as u
            LEFT JOIN dependencia d ON u.depe_codi = d.depe_codi
            LEFT JOIN institucion i ON u.inst_codi = i.inst_codi --institucion padre
            LEFT JOIN institucion ia ON d.inst_adscrita = ia.inst_codi --institucion adscrita
        INTO var_recordset;
        
    
        IF TG_OP = 'UPDATE' THEN
	    UPDATE usuario 
	    SET   usua_cedula = NEW.usua_cedula
	        , usua_nomb   = NEW.usua_nomb
	        , usua_apellido = NEW.usua_apellido
	        , usua_nombre = TRIM(COALESCE(NEW.usua_nomb::text, ''::text) || ' '::text || COALESCE(NEW.usua_apellido::text, ''::text))
	        , usua_nuevo  = NEW.usua_nuevo 
	        , usua_login  = NEW.usua_login
	        , usua_pasw   = NEW.usua_pasw 
	        , usua_cargo  = NEW.usua_cargo 
	        , usua_cargo_cabecera = NEW.usua_cargo_cabecera
	        , cargo_tipo  = NEW.cargo_tipo
	        , usua_esta   = NEW.usua_esta
	        , usua_email  = NEW.usua_email
	        , usua_titulo = NEW.usua_titulo
	        , usua_abr_titulo = NEW.usua_abr_titulo
	        , tipo_usuario = CASE WHEN NEW.inst_codi = 1 THEN 2 ELSE 1 END
	        , usua_tipo_certificado = NEW.usua_tipo_certificado
	        , usua_subrogado = NEW.usua_subrogado
	        , visible_sub = NEW.visible_sub
	        , usua_direccion = NEW.usua_direccion
	        , usua_telefono = NEW.usua_telefono
	        , usua_firma_path = NEW.usua_firma_path
	        , depe_codi   = NEW.depe_codi
	        , depe_nomb   = var_recordset.depe_nomb
	        , dep_sigla   = var_recordset.dep_sigla
	        , inst_codi   = NEW.inst_codi
	        , inst_nombre = var_recordset.inst_nombre
	        , inst_sigla  = var_recordset.inst_sigla
	        , inst_estado = var_recordset.inst_estado
	        , ciu_codi = var_recordset.ciu_codi
	        , usua_ciudad = var_recordset.usua_ciudad
                , tipo_identificacion = NEW.tipo_identificacion
                , usua_datos = translate(UPPER(coalesce(NEW.usua_cedula,'')||' '||coalesce(NEW.usua_nomb,'')
                      ||' '||coalesce(NEW.usua_apellido,'')||' '||coalesce(NEW.usua_cargo,'')||' '||coalesce(NEW.usua_email,'')
                      ||' '||coalesce(var_recordset.depe_nomb,'')||' '||coalesce(var_recordset.inst_nombre,'')
                      ||' '||coalesce(var_recordset.inst_sigla,'')),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÂÊÎÔÛÃÕÑ','AEIOUAEIOUAEIOUAEIOUAON')
	        , inst_padre_nombre = var_recordset.inst_padre_nombre
	        , inst_padre_sigla  = var_recordset.inst_padre_sigla
	        , inst_adscrita = var_recordset.inst_adscrita
	    WHERE usua_codi = NEW.usua_codi;
        END IF;

        IF TG_OP = 'INSERT' THEN
            INSERT INTO usuario (
	        usua_codi, usua_cedula, usua_nomb, usua_apellido, usua_nombre, usua_nuevo, usua_login
	        , usua_pasw, usua_cargo, usua_cargo_cabecera, cargo_tipo, usua_esta, usua_email
	        , usua_titulo, usua_abr_titulo, tipo_usuario, usua_tipo_certificado, usua_subrogado
	        , visible_sub, usua_direccion, usua_telefono, usua_firma_path, depe_codi, depe_nomb
	        , dep_sigla, inst_codi, inst_nombre, inst_sigla, inst_estado, ciu_codi, usua_ciudad
	        , tipo_identificacion, usua_datos, inst_padre_nombre, inst_padre_sigla, inst_adscrita
	    ) VALUES (
	        NEW.usua_codi, NEW.usua_cedula, NEW.usua_nomb, NEW.usua_apellido
	        , TRIM(COALESCE(NEW.usua_nomb::text, ''::text) || ' '::text || COALESCE(NEW.usua_apellido::text, ''::text))
	        , NEW.usua_nuevo, NEW.usua_login, NEW.usua_pasw, NEW.usua_cargo, NEW.usua_cargo_cabecera
	        , NEW.cargo_tipo, NEW.usua_esta, NEW.usua_email, NEW.usua_titulo, NEW.usua_abr_titulo
	        , CASE WHEN NEW.inst_codi = 1 THEN 2 ELSE 1 END
	        , NEW.usua_tipo_certificado, NEW.usua_subrogado, NEW.visible_sub, NEW.usua_direccion
	        , NEW.usua_telefono, NEW.usua_firma_path, NEW.depe_codi, var_recordset.depe_nomb
	        , var_recordset.dep_sigla, NEW.inst_codi, var_recordset.inst_nombre, var_recordset.inst_sigla
	        , var_recordset.inst_estado, var_recordset.ciu_codi, var_recordset.usua_ciudad, NEW.tipo_identificacion
	        , translate(UPPER(coalesce(NEW.usua_cedula,'')||' '||coalesce(NEW.usua_nomb,'')||' '||coalesce(NEW.usua_apellido,'')
	              ||' '||coalesce(NEW.usua_cargo,'')||' '||coalesce(NEW.usua_email,'')||' '||coalesce(var_recordset.depe_nomb,'')
                      ||' '||coalesce(var_recordset.inst_nombre,'')||' '||coalesce(var_recordset.inst_sigla,'')
                  ),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÂÊÎÔÛÃÕÑ','AEIOUAEIOUAEIOUAEIOUAON')
                , var_recordset.inst_padre_nombre, var_recordset.inst_padre_sigla, var_recordset.inst_adscrita
	    );

	END IF;
	
    EXCEPTION WHEN OTHERS THEN
        INSERT INTO log_view_usuario (fecha, tabla, accion, codigo, error) VALUES (now(), 'usuarios', TG_OP, NEW.usua_codi, SQLERRM);
    END;
    RETURN NULL;
END;
$$;


--
-- TOC entry 346 (class 1255 OID 52993)
-- Dependencies: 1001 5
-- Name: func_cambiar_dominio_email_usuarios(text, text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_cambiar_dominio_email_usuarios(var_dominio_origen text, var_dominio_destino text) RETURNS integer
    LANGUAGE plpgsql
    AS $$ 
DECLARE 
    var_num_registros integer;
BEGIN 
    select count(1) from usuarios where usua_email ilike '%'||var_dominio_origen and usua_esta=1 into var_num_registros;
    
    update usuarios
    set usua_email=replace(usua_email, var_dominio_origen, var_dominio_destino)
    where usua_email ilike '%'||var_dominio_origen and usua_esta=1;

    RETURN var_num_registros; 
END; 
$$;


--
-- TOC entry 347 (class 1255 OID 52994)
-- Dependencies: 5 1001
-- Name: func_grabar_archivo(text, text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_grabar_archivo(var_nombre_archivo text, var_archivo_base_64 text) RETURNS integer
    LANGUAGE plpgsql STABLE
    AS $$
BEGIN
    return 0;
END;
$$;


--
-- TOC entry 348 (class 1255 OID 52995)
-- Dependencies: 1001 5
-- Name: func_recuperar_archivo(bigint); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_recuperar_archivo(var_arch_codi bigint) RETURNS text
    LANGUAGE plpgsql STABLE
    AS $$
BEGIN
    return '';
END;
$$;


--
-- TOC entry 341 (class 1255 OID 52996)
-- Dependencies: 5 1001
-- Name: func_valor_secuencia(text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION func_valor_secuencia(sec_nombre text) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
DECLARE
    var_recordset record;
Begin
    BEGIN
	execute E'select last_value from '||sec_nombre into var_recordset;
	return var_recordset.last_value;
    EXCEPTION WHEN OTHERS THEN
        return -1;
    END;
end;
$$;


--
-- TOC entry 349 (class 1255 OID 53008)
-- Dependencies: 5 1001
-- Name: ver_usuarios(text, text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION ver_usuarios(text, text) RETURNS text
    LANGUAGE plpgsql
    AS $_$
DECLARE
    fila RECORD;
    cadena text;
    separador text;
    lista_usuarios text;
Begin
    cadena := '';
    separador := '';
    BEGIN
        lista_usuarios := replace(replace($1,'--',','),'-','');
        for fila in execute E'select usua_nombre, coalesce(inst_sigla,\'\') as inst_sigla from usuario where usua_codi in ('||lista_usuarios||')' loop
            cadena := cadena || separador || ' ' || fila.usua_nombre || case when trim(fila.inst_sigla)<>'' then ' ('||fila.inst_sigla||')' else '' end;
            separador := $2;
         end loop;
    EXCEPTION WHEN OTHERS THEN
        cadena := '';
    END;
    return cadena;
end;
$_$;


--
-- TOC entry 2982 (class 0 OID 0)
-- Dependencies: 349
-- Name: FUNCTION ver_usuarios(text, text); Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON FUNCTION ver_usuarios(text, text) IS 'Muestra la lista de nombres de usuarios a partir de la cadena ingresada en radicado.radicca, radicado.radi_usua_rem y radicado.radi_usua_dest';


--
-- TOC entry 350 (class 1255 OID 53010)
-- Dependencies: 5 1001
-- Name: ws_func_validar_login(text, text, text, text); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION ws_func_validar_login(usr_login text, usr_password text, nombre_sistema text, usr_tipo text DEFAULT ''::text) RETURNS integer
    LANGUAGE plpgsql
    AS $$
DECLARE
    var_password text;
    var_where text;
    var_recordset record;
BEGIN
    -- Limpiamos las cadenas de texto
    if strpos(usr_login||usr_password||nombre_sistema, E'\'')>0 then return 0; end if; -- Valida que los datos no tengan \' (ataque SQL Injection)
    var_password := substr(usr_password,2,26);
    -- Validamos el tipo de usuario, por defecto todos (F: Funcionarios - C: Ciudadanos)
    var_where := '';
    if usr_tipo='F' then var_where := ' and tipo_usuario=1'; end if;
    if usr_tipo='C' then var_where := ' and tipo_usuario=2'; end if;
    BEGIN
	-- TODO - VALIDAR LIMITE DE INTENTOS. Retorna 4
    
	execute E'select usua_codi from usuario 
	          where usua_login=\'U'||usr_login||E'\' 
	              and usua_pasw=\''||var_password||E'\' 
	              and usua_esta=1 and inst_estado=1 '||var_where||' 
	          order by tipo_usuario asc, usua_codi asc 
	          limit 1 offset 0' into var_recordset;
	          
	if var_recordset is not null then
	    -- TODO - INICIALIZAR INTENTOS DE ACCESO Y GRABAR LOG
	    return var_recordset.usua_codi; -- Si el usuario puede acceder al sistema. - RETURN 1
	else
	    -- TODO - SUMAR INTENTO DE ACCESO Y GRABAR LOG
            execute E'select * from usuario 
	              where usua_cedula like \''||usr_login||E'%\' '||var_where||' 
	              order by tipo_usuario asc, usua_esta desc, inst_estado desc
	              limit 1 offset 0' into var_recordset;
	              
	    --if var_recordset is null then return -1; end if; -- Si no existe el usuario - RETURN 2
	    if var_recordset.usua_esta<>1 or var_recordset.inst_estado<>1 then return -2; end if; -- Usuario inactivo - RETURN -2
	    return -1;
	end if;
    EXCEPTION WHEN OTHERS THEN
        return -1;
    END;
    return -1;
END;
$$;


--
-- TOC entry 1937 (class 3602 OID 53011)
-- Dependencies: 1920 1917 5
-- Name: es; Type: TEXT SEARCH CONFIGURATION; Schema: public; Owner: -
--

CREATE TEXT SEARCH CONFIGURATION es (
    PARSER = pg_catalog."default" );

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR asciiword WITH spanish_stem;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR word WITH unaccent, spanish_stem;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR numword WITH simple;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR email WITH simple;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR url WITH simple;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR host WITH simple;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR sfloat WITH simple;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR version WITH simple;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR hword_numpart WITH simple;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR hword_part WITH unaccent, spanish_stem;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR hword_asciipart WITH spanish_stem;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR numhword WITH simple;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR asciihword WITH spanish_stem;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR hword WITH unaccent, spanish_stem;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR url_path WITH simple;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR file WITH simple;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR "float" WITH simple;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR "int" WITH simple;

ALTER TEXT SEARCH CONFIGURATION es
    ADD MAPPING FOR uint WITH simple;


SET default_with_oids = false;

--
-- TOC entry 162 (class 1259 OID 53012)
-- Dependencies: 5
-- Name: accion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE accion (
    accion_codi integer NOT NULL,
    accion_nombre character varying(50),
    inst_codi bigint
);


--
-- TOC entry 2983 (class 0 OID 0)
-- Dependencies: 162
-- Name: TABLE accion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE accion IS 'Lista de acciones que se muestran al momento de reasignar un documento para no tener que escribir en observaciones';


--
-- TOC entry 2984 (class 0 OID 0)
-- Dependencies: 162
-- Name: COLUMN accion.accion_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN accion.accion_codi IS 'Id';


--
-- TOC entry 2985 (class 0 OID 0)
-- Dependencies: 162
-- Name: COLUMN accion.accion_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN accion.accion_nombre IS 'Detalle';


--
-- TOC entry 2986 (class 0 OID 0)
-- Dependencies: 162
-- Name: COLUMN accion.inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN accion.inst_codi IS 'Id de la institución a la que pertenece';


--
-- TOC entry 163 (class 1259 OID 53015)
-- Dependencies: 5
-- Name: sec_actualizar_sistema; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_actualizar_sistema
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 2987 (class 0 OID 0)
-- Dependencies: 163
-- Name: sec_actualizar_sistema; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_actualizar_sistema', 1, false);


--
-- TOC entry 164 (class 1259 OID 53017)
-- Dependencies: 2456 2457 2458 2459 2460 5
-- Name: actualizar_sistema; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE actualizar_sistema (
    actu_codi integer DEFAULT nextval('sec_actualizar_sistema'::regclass) NOT NULL,
    sentencia character varying,
    sentencia_verificacion character varying,
    estado smallint DEFAULT 0,
    observacion character varying,
    svn character varying,
    num_registros_total bigint DEFAULT 0,
    num_registros_restantes bigint DEFAULT 0,
    num_registros_bloque integer DEFAULT 0
);


--
-- TOC entry 2988 (class 0 OID 0)
-- Dependencies: 164
-- Name: TABLE actualizar_sistema; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE actualizar_sistema IS 'Gestiona las actualizaciones de tablas que tienen muchos registros, actualizando los registros en bloques';


--
-- TOC entry 2989 (class 0 OID 0)
-- Dependencies: 164
-- Name: COLUMN actualizar_sistema.sentencia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN actualizar_sistema.sentencia IS 'Query de la actualización';


--
-- TOC entry 2990 (class 0 OID 0)
-- Dependencies: 164
-- Name: COLUMN actualizar_sistema.sentencia_verificacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN actualizar_sistema.sentencia_verificacion IS 'Query que sirve para validar el número de registros restantes por actualizar';


--
-- TOC entry 2991 (class 0 OID 0)
-- Dependencies: 164
-- Name: COLUMN actualizar_sistema.estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN actualizar_sistema.estado IS '0 - Pendiente
1 - Ejecutado
2 - Cancelado
3 – Error';


--
-- TOC entry 2992 (class 0 OID 0)
-- Dependencies: 164
-- Name: COLUMN actualizar_sistema.observacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN actualizar_sistema.observacion IS 'Comentario acerca del cambio';


--
-- TOC entry 2993 (class 0 OID 0)
-- Dependencies: 164
-- Name: COLUMN actualizar_sistema.svn; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN actualizar_sistema.svn IS 'Revisión SVN de la que depende el cambio';


--
-- TOC entry 2994 (class 0 OID 0)
-- Dependencies: 164
-- Name: COLUMN actualizar_sistema.num_registros_total; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN actualizar_sistema.num_registros_total IS 'Número de registros a ser actualizados';


--
-- TOC entry 2995 (class 0 OID 0)
-- Dependencies: 164
-- Name: COLUMN actualizar_sistema.num_registros_restantes; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN actualizar_sistema.num_registros_restantes IS 'Númer de registros que restan por actualizar';


--
-- TOC entry 2996 (class 0 OID 0)
-- Dependencies: 164
-- Name: COLUMN actualizar_sistema.num_registros_bloque; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN actualizar_sistema.num_registros_bloque IS 'Número de registros que se actualizarán cada vez que se ejecute el proceso';


--
-- TOC entry 165 (class 1259 OID 53028)
-- Dependencies: 2461 2462 2463 5
-- Name: anexos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE anexos (
    anex_radi_nume numeric(20,0) NOT NULL,
    anex_codigo character varying(50) NOT NULL,
    anex_tipo smallint NOT NULL,
    anex_desc character varying(512),
    anex_numero numeric(5,0) NOT NULL,
    anex_path character varying(200),
    anex_borrado character varying(1) NOT NULL,
    anex_fecha timestamp with time zone,
    anex_nombre character varying(100),
    anex_usua_codi integer,
    anex_tamano numeric,
    anex_fisico smallint DEFAULT 0,
    anex_fecha_firma timestamp with time zone,
    anex_datos_firma character varying,
    arch_codi bigint DEFAULT 0,
    arch_codi_firma bigint DEFAULT 0
);


--
-- TOC entry 2997 (class 0 OID 0)
-- Dependencies: 165
-- Name: TABLE anexos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE anexos IS 'Archivos adjuntos a los documentos';


--
-- TOC entry 2998 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_radi_nume; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_radi_nume IS 'Número de radicado al que está asociado el anexo';


--
-- TOC entry 2999 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_codigo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_codigo IS 'Id del anexo';


--
-- TOC entry 3000 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_tipo IS 'Tipo de documento (.doc, .pdf, etc.) depende de la tabla anexos_tipo';


--
-- TOC entry 3001 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_desc; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_desc IS 'Descripción del anexo';


--
-- TOC entry 3002 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_numero; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_numero IS 'Número de anexo';


--
-- TOC entry 3003 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_path; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_path IS 'Path en el que se encuentra el archivo en la bodega';


--
-- TOC entry 3004 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_borrado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_borrado IS 'Indica si el archivo fue eliminado o no';


--
-- TOC entry 3005 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_fecha IS 'Fecha en la que se subió el anexo';


--
-- TOC entry 3006 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_nombre IS 'Nombre original del archivo con el que lo subió el cliente';


--
-- TOC entry 3007 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_usua_codi IS 'Usuario que anexo el archivo';


--
-- TOC entry 3008 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_tamano; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_tamano IS 'Tamaño del archivo';


--
-- TOC entry 3009 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_fisico; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_fisico IS 'Determina si el origen del documento es fisico o electronico';


--
-- TOC entry 3010 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_fecha_firma; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_fecha_firma IS 'Fecha de la firma digital';


--
-- TOC entry 3011 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.anex_datos_firma; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.anex_datos_firma IS 'Datos del firmante';


--
-- TOC entry 3012 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.arch_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.arch_codi IS 'Código del archivo almacenado en la BDD de documentos';


--
-- TOC entry 3013 (class 0 OID 0)
-- Dependencies: 165
-- Name: COLUMN anexos.arch_codi_firma; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos.arch_codi_firma IS 'Código del archivo firmado electrónicamente, almacenado en la BDD de documentos';


--
-- TOC entry 166 (class 1259 OID 53037)
-- Dependencies: 2464 5
-- Name: anexos_tipo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE anexos_tipo (
    anex_tipo_codi smallint NOT NULL,
    anex_tipo_ext character varying(10) NOT NULL,
    anex_tipo_desc character varying(50),
    anex_tipo_estado numeric(1,0) DEFAULT 1
);


--
-- TOC entry 3014 (class 0 OID 0)
-- Dependencies: 166
-- Name: TABLE anexos_tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE anexos_tipo IS 'Tipos de anexos que se permiten cargar al sistema; por seguridad se suben solo los tipos de archivos permitidos';


--
-- TOC entry 3015 (class 0 OID 0)
-- Dependencies: 166
-- Name: COLUMN anexos_tipo.anex_tipo_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos_tipo.anex_tipo_codi IS 'Id';


--
-- TOC entry 3016 (class 0 OID 0)
-- Dependencies: 166
-- Name: COLUMN anexos_tipo.anex_tipo_ext; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos_tipo.anex_tipo_ext IS 'Extensión del documento';


--
-- TOC entry 3017 (class 0 OID 0)
-- Dependencies: 166
-- Name: COLUMN anexos_tipo.anex_tipo_desc; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos_tipo.anex_tipo_desc IS 'Descripción del tipo de archivo';


--
-- TOC entry 3018 (class 0 OID 0)
-- Dependencies: 166
-- Name: COLUMN anexos_tipo.anex_tipo_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN anexos_tipo.anex_tipo_estado IS 'Estado, activo o inactivo';


--
-- TOC entry 167 (class 1259 OID 53041)
-- Dependencies: 2465 2466 2467 5
-- Name: archivo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE archivo (
    arch_codi bigint NOT NULL,
    arch_padre bigint DEFAULT 0 NOT NULL,
    arch_nombre character varying(40),
    arch_sigla character varying(6),
    depe_codi integer,
    arch_estado smallint DEFAULT 1,
    arch_ocupado smallint DEFAULT 0
);


--
-- TOC entry 3019 (class 0 OID 0)
-- Dependencies: 167
-- Name: TABLE archivo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE archivo IS 'Tabla recursiva, estructura del archivo físico de una institución';


--
-- TOC entry 3020 (class 0 OID 0)
-- Dependencies: 167
-- Name: COLUMN archivo.arch_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo.arch_codi IS 'Id del item';


--
-- TOC entry 3021 (class 0 OID 0)
-- Dependencies: 167
-- Name: COLUMN archivo.arch_padre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo.arch_padre IS 'Id del item padre';


--
-- TOC entry 3022 (class 0 OID 0)
-- Dependencies: 167
-- Name: COLUMN archivo.arch_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo.arch_nombre IS 'Nombre del item';


--
-- TOC entry 3023 (class 0 OID 0)
-- Dependencies: 167
-- Name: COLUMN archivo.arch_sigla; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo.arch_sigla IS 'Abreviatura del item';


--
-- TOC entry 3024 (class 0 OID 0)
-- Dependencies: 167
-- Name: COLUMN archivo.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo.depe_codi IS 'Área a la que pertenece';


--
-- TOC entry 3025 (class 0 OID 0)
-- Dependencies: 167
-- Name: COLUMN archivo.arch_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo.arch_estado IS 'Indica si se pueden seguir añadiendo documentos en la ubicacion actual';


--
-- TOC entry 3026 (class 0 OID 0)
-- Dependencies: 167
-- Name: COLUMN archivo.arch_ocupado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo.arch_ocupado IS 'Indica si el item está relacionado con un expediente virtual';


--
-- TOC entry 168 (class 1259 OID 53047)
-- Dependencies: 5
-- Name: archivo_nivel; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE archivo_nivel (
    arch_codi integer NOT NULL,
    depe_codi integer NOT NULL,
    arch_nombre character varying(50) NOT NULL,
    arch_descripcion character varying(100)
);


--
-- TOC entry 3027 (class 0 OID 0)
-- Dependencies: 168
-- Name: TABLE archivo_nivel; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE archivo_nivel IS 'Niveles que tendrá la estructura del archivo físico';


--
-- TOC entry 3028 (class 0 OID 0)
-- Dependencies: 168
-- Name: COLUMN archivo_nivel.arch_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo_nivel.arch_codi IS 'Id del Item, es un secuencial dependiendo del area funcional';


--
-- TOC entry 3029 (class 0 OID 0)
-- Dependencies: 168
-- Name: COLUMN archivo_nivel.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo_nivel.depe_codi IS 'Area funcional a la que pertenece el archivo';


--
-- TOC entry 3030 (class 0 OID 0)
-- Dependencies: 168
-- Name: COLUMN archivo_nivel.arch_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo_nivel.arch_nombre IS 'Nombre del Item';


--
-- TOC entry 3031 (class 0 OID 0)
-- Dependencies: 168
-- Name: COLUMN archivo_nivel.arch_descripcion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo_nivel.arch_descripcion IS 'Descripcion del Item';


--
-- TOC entry 169 (class 1259 OID 53050)
-- Dependencies: 2468 5
-- Name: archivo_radicado; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE archivo_radicado (
    radi_nume_radi numeric(20,0) NOT NULL,
    arch_codi bigint NOT NULL,
    usua_codi integer,
    fecha timestamp with time zone,
    depe_codi integer,
    anex_numero smallint DEFAULT 0 NOT NULL
);


--
-- TOC entry 3032 (class 0 OID 0)
-- Dependencies: 169
-- Name: TABLE archivo_radicado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE archivo_radicado IS 'Relaciona RADICADO con ARCHIVO, guarda las asociaciones de los documentos en un item específico del archivo';


--
-- TOC entry 3033 (class 0 OID 0)
-- Dependencies: 169
-- Name: COLUMN archivo_radicado.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo_radicado.radi_nume_radi IS 'Id del documento';


--
-- TOC entry 3034 (class 0 OID 0)
-- Dependencies: 169
-- Name: COLUMN archivo_radicado.arch_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo_radicado.arch_codi IS 'Id del item del archivo';


--
-- TOC entry 3035 (class 0 OID 0)
-- Dependencies: 169
-- Name: COLUMN archivo_radicado.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo_radicado.usua_codi IS 'Usuario que archivo el documento';


--
-- TOC entry 3036 (class 0 OID 0)
-- Dependencies: 169
-- Name: COLUMN archivo_radicado.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo_radicado.fecha IS 'Fecha en que se archivó el documento';


--
-- TOC entry 3037 (class 0 OID 0)
-- Dependencies: 169
-- Name: COLUMN archivo_radicado.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo_radicado.depe_codi IS 'Área en la que se archivó el documento';


--
-- TOC entry 3038 (class 0 OID 0)
-- Dependencies: 169
-- Name: COLUMN archivo_radicado.anex_numero; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN archivo_radicado.anex_numero IS 'Numero de anexo que se esta archivando';


--
-- TOC entry 170 (class 1259 OID 53054)
-- Dependencies: 5
-- Name: bandeja_compartida; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE bandeja_compartida (
    ban_com_codi integer NOT NULL,
    usua_codi_jefe bigint,
    usua_codi bigint,
    ban_com_fecha timestamp with time zone
);


--
-- TOC entry 3039 (class 0 OID 0)
-- Dependencies: 170
-- Name: TABLE bandeja_compartida; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE bandeja_compartida IS 'Guarda la relación entre el jefe del área y los usuarios a quienes se les comparte la bandeja de entrada';


--
-- TOC entry 3040 (class 0 OID 0)
-- Dependencies: 170
-- Name: COLUMN bandeja_compartida.ban_com_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN bandeja_compartida.ban_com_codi IS 'Id';


--
-- TOC entry 3041 (class 0 OID 0)
-- Dependencies: 170
-- Name: COLUMN bandeja_compartida.usua_codi_jefe; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN bandeja_compartida.usua_codi_jefe IS 'Código del usuario jefe';


--
-- TOC entry 3042 (class 0 OID 0)
-- Dependencies: 170
-- Name: COLUMN bandeja_compartida.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN bandeja_compartida.usua_codi IS 'Códigop del usuario al que le comparten la bandeja';


--
-- TOC entry 3043 (class 0 OID 0)
-- Dependencies: 170
-- Name: COLUMN bandeja_compartida.ban_com_fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN bandeja_compartida.ban_com_fecha IS 'Fecha en la que se compartió la bandeja';


--
-- TOC entry 171 (class 1259 OID 53057)
-- Dependencies: 5 170
-- Name: bandeja_compartida_ban_com_codi_seq1; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE bandeja_compartida_ban_com_codi_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3044 (class 0 OID 0)
-- Dependencies: 171
-- Name: bandeja_compartida_ban_com_codi_seq1; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE bandeja_compartida_ban_com_codi_seq1 OWNED BY bandeja_compartida.ban_com_codi;


--
-- TOC entry 3045 (class 0 OID 0)
-- Dependencies: 171
-- Name: bandeja_compartida_ban_com_codi_seq1; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('bandeja_compartida_ban_com_codi_seq1', 1, false);


--
-- TOC entry 172 (class 1259 OID 53059)
-- Dependencies: 5
-- Name: sec_bloqueo_sistema; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_bloqueo_sistema
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3046 (class 0 OID 0)
-- Dependencies: 172
-- Name: sec_bloqueo_sistema; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_bloqueo_sistema', 1, false);


--
-- TOC entry 173 (class 1259 OID 53061)
-- Dependencies: 2470 2471 2472 5
-- Name: bloqueo_sistema; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE bloqueo_sistema (
    bloq_codi integer DEFAULT nextval('sec_bloqueo_sistema'::regclass) NOT NULL,
    fecha_inicio timestamp with time zone,
    fecha_fin timestamp with time zone,
    estado integer DEFAULT 0,
    descripcion character varying,
    mensaje_usuario character varying,
    usua_acceso character varying,
    tipo_mensaje integer DEFAULT 0
);


--
-- TOC entry 3047 (class 0 OID 0)
-- Dependencies: 173
-- Name: TABLE bloqueo_sistema; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE bloqueo_sistema IS 'Alertas y bloqueos del sistema; para hacer un bloqueo se debe cambiar la variable correspondiente en el archivo config.php';


--
-- TOC entry 3048 (class 0 OID 0)
-- Dependencies: 173
-- Name: COLUMN bloqueo_sistema.bloq_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN bloqueo_sistema.bloq_codi IS 'Id del bloqueo';


--
-- TOC entry 3049 (class 0 OID 0)
-- Dependencies: 173
-- Name: COLUMN bloqueo_sistema.fecha_inicio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN bloqueo_sistema.fecha_inicio IS 'Fecha y hora cuando inicia el bloqueo o la alerta';


--
-- TOC entry 3050 (class 0 OID 0)
-- Dependencies: 173
-- Name: COLUMN bloqueo_sistema.fecha_fin; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN bloqueo_sistema.fecha_fin IS 'Fecha y hora cuando finaliza el bloqueo o la alerta';


--
-- TOC entry 3051 (class 0 OID 0)
-- Dependencies: 173
-- Name: COLUMN bloqueo_sistema.estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN bloqueo_sistema.estado IS 'Estado de la alerta:
0 - Cancelado
1 - Activo
2 - Eliminado';


--
-- TOC entry 3052 (class 0 OID 0)
-- Dependencies: 173
-- Name: COLUMN bloqueo_sistema.descripcion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN bloqueo_sistema.descripcion IS 'Descripción corta del mensaje';


--
-- TOC entry 3053 (class 0 OID 0)
-- Dependencies: 173
-- Name: COLUMN bloqueo_sistema.mensaje_usuario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN bloqueo_sistema.mensaje_usuario IS 'Mensaje que se muestra al usuario en formato HTML';


--
-- TOC entry 3054 (class 0 OID 0)
-- Dependencies: 173
-- Name: COLUMN bloqueo_sistema.usua_acceso; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN bloqueo_sistema.usua_acceso IS 'Lista de usuarios que tendrán acceso al sistema, separados por guiones';


--
-- TOC entry 3055 (class 0 OID 0)
-- Dependencies: 173
-- Name: COLUMN bloqueo_sistema.tipo_mensaje; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN bloqueo_sistema.tipo_mensaje IS '0 - Bloqueo General
1 - Bloqueo a nuevos usuarios
2 - Mensaje de alerta a todos los usuarios';


--
-- TOC entry 174 (class 1259 OID 53070)
-- Dependencies: 5
-- Name: cargo_cargo_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE cargo_cargo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3056 (class 0 OID 0)
-- Dependencies: 174
-- Name: cargo_cargo_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('cargo_cargo_id_seq', 1, false);


--
-- TOC entry 175 (class 1259 OID 53079)
-- Dependencies: 5
-- Name: carpeta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE carpeta (
    carp_codi smallint NOT NULL,
    carp_nombre character varying(50) NOT NULL,
    carp_descripcion character varying(100),
    carp_orden integer
);


--
-- TOC entry 3057 (class 0 OID 0)
-- Dependencies: 175
-- Name: TABLE carpeta; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE carpeta IS 'Lista de las bandejas que se muestran al usuario en el menú principal';


--
-- TOC entry 3058 (class 0 OID 0)
-- Dependencies: 175
-- Name: COLUMN carpeta.carp_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN carpeta.carp_codi IS 'Id de la bandeja';


--
-- TOC entry 3059 (class 0 OID 0)
-- Dependencies: 175
-- Name: COLUMN carpeta.carp_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN carpeta.carp_nombre IS 'Nombre de la bandeja';


--
-- TOC entry 3060 (class 0 OID 0)
-- Dependencies: 175
-- Name: COLUMN carpeta.carp_descripcion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN carpeta.carp_descripcion IS 'Descripción que se muestra en el tool tip';


--
-- TOC entry 3061 (class 0 OID 0)
-- Dependencies: 175
-- Name: COLUMN carpeta.carp_orden; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN carpeta.carp_orden IS 'Orden en el que se mostraran las bandejas';


--
-- TOC entry 176 (class 1259 OID 53082)
-- Dependencies: 5
-- Name: categoria; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE categoria (
    cat_codi integer NOT NULL,
    cat_descr character varying(150) NOT NULL
);


--
-- TOC entry 3062 (class 0 OID 0)
-- Dependencies: 176
-- Name: TABLE categoria; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE categoria IS 'Catálogo con las categorías de los documentos (Normal, urgente, etc.)';


--
-- TOC entry 3063 (class 0 OID 0)
-- Dependencies: 176
-- Name: COLUMN categoria.cat_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN categoria.cat_codi IS 'Id';


--
-- TOC entry 3064 (class 0 OID 0)
-- Dependencies: 176
-- Name: COLUMN categoria.cat_descr; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN categoria.cat_descr IS 'Descripción';


--
-- TOC entry 177 (class 1259 OID 53085)
-- Dependencies: 176 5
-- Name: categoria_cat_codi_seq1; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE categoria_cat_codi_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3065 (class 0 OID 0)
-- Dependencies: 177
-- Name: categoria_cat_codi_seq1; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE categoria_cat_codi_seq1 OWNED BY categoria.cat_codi;


--
-- TOC entry 3066 (class 0 OID 0)
-- Dependencies: 177
-- Name: categoria_cat_codi_seq1; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('categoria_cat_codi_seq1', 1, false);


--
-- TOC entry 178 (class 1259 OID 53087)
-- Dependencies: 5
-- Name: ciudad; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE ciudad (
    id integer NOT NULL,
    nombre character varying(100) NOT NULL,
    id_padre integer
);


--
-- TOC entry 3067 (class 0 OID 0)
-- Dependencies: 178
-- Name: TABLE ciudad; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE ciudad IS 'Catálogo de ciudades, tabla recursiva';


--
-- TOC entry 3068 (class 0 OID 0)
-- Dependencies: 178
-- Name: COLUMN ciudad.id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudad.id IS 'Id de la ciudad';


--
-- TOC entry 3069 (class 0 OID 0)
-- Dependencies: 178
-- Name: COLUMN ciudad.nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudad.nombre IS 'Nombre de la ciudad';


--
-- TOC entry 3070 (class 0 OID 0)
-- Dependencies: 178
-- Name: COLUMN ciudad.id_padre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudad.id_padre IS 'Código del país o de la provincia a la que pertenece la ciudad';


--
-- TOC entry 179 (class 1259 OID 53090)
-- Dependencies: 2474 2475 2476 5
-- Name: ciudadano; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE ciudadano (
    ciu_nombre character varying(200),
    ciu_direccion character varying(150),
    ciu_empresa character varying(200),
    ciu_cargo character varying(150),
    ciu_telefono character varying(50),
    ciu_email character varying(500),
    ciu_titulo character varying(100),
    ciu_abr_titulo character varying(30),
    ciu_codigo integer DEFAULT nextval(('public.usuarios_usua_codi_seq'::text)::regclass) NOT NULL,
    ciu_apellido character varying(200),
    ciu_cedula character varying(50),
    inst_codi integer,
    ciu_estado integer DEFAULT 1,
    ciu_documento character varying(50),
    ciu_pasw character varying(35),
    ciu_nuevo smallint DEFAULT 0,
    usua_codi_actualiza integer,
    ciu_fecha_actualiza timestamp with time zone,
    ciudad_codi integer,
    ciu_obs_actualiza character varying,
    ciu_referencia character varying
);


--
-- TOC entry 3071 (class 0 OID 0)
-- Dependencies: 179
-- Name: TABLE ciudadano; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE ciudadano IS 'Contiene los usuarios externos (que no pertenecen a una institución pública) y que solo pueden conectarse al sistema para consultar los documentos que dejaron en alguna institución y las respuestas recibidas';


--
-- TOC entry 3072 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_nombre IS 'Nombre de la persona';


--
-- TOC entry 3073 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_direccion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_direccion IS 'Dirección Domiciliaria';


--
-- TOC entry 3074 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_empresa; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_empresa IS 'Nombre de la empresa a la que pertenece';


--
-- TOC entry 3075 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_cargo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_cargo IS 'Cargo que desempeña en su empresa';


--
-- TOC entry 3076 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_telefono; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_telefono IS 'Número telefónico';


--
-- TOC entry 3077 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_email IS 'email; pueden ser varios separados por comas';


--
-- TOC entry 3078 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_titulo IS 'Título o tratamiento (Señor, Ingeniero, etc.)';


--
-- TOC entry 3079 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_abr_titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_abr_titulo IS 'Abreviación del título o tratamiento (Sr., Ing., etc.)';


--
-- TOC entry 3080 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_codigo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_codigo IS 'Id del usuario ciudadano';


--
-- TOC entry 3081 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_apellido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_apellido IS 'Apellido de la persona';


--
-- TOC entry 3082 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_cedula; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_cedula IS 'Número de cédula de ciudadanía';


--
-- TOC entry 3083 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.inst_codi IS 'Institución en la que se creo el ciudadano (log)';


--
-- TOC entry 3084 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_estado IS 'Estado del ciudadano
0 - Inactivo
1 - Activo';


--
-- TOC entry 3085 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_documento; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_documento IS 'Número de identificación adicional (RUC, pasaporte, etc.)';


--
-- TOC entry 3086 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_pasw; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_pasw IS 'Contraseña';


--
-- TOC entry 3087 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_nuevo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_nuevo IS 'Indica si es un usuario nuevo y si se le debe enviar la contraseña a su correo electrónico';


--
-- TOC entry 3088 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.usua_codi_actualiza; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.usua_codi_actualiza IS 'Usuario que realizó la última modificación a los datos del ciudadano';


--
-- TOC entry 3089 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_fecha_actualiza; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_fecha_actualiza IS 'Fecha en que se modificó por última vez al ciudadano';


--
-- TOC entry 3090 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciudad_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciudad_codi IS 'Id de la ciudad en la que se encuentra la persona';


--
-- TOC entry 3091 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_obs_actualiza; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_obs_actualiza IS 'Descripción de los últimos cambios realizados en la información del usuario';


--
-- TOC entry 3092 (class 0 OID 0)
-- Dependencies: 179
-- Name: COLUMN ciudadano.ciu_referencia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano.ciu_referencia IS 'Datos de referencia de la dirección del domicilio';


--
-- TOC entry 180 (class 1259 OID 53099)
-- Dependencies: 2477 5
-- Name: ciudadano_tmp; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE ciudadano_tmp (
    ciu_codigo integer NOT NULL,
    ciu_cedula character varying(50),
    ciu_documento character varying(50),
    ciu_nombre character varying(200),
    ciu_apellido character varying(200),
    ciu_titulo character varying(100),
    ciu_abr_titulo character varying(30),
    ciu_empresa character varying(200),
    ciu_cargo character varying(150),
    ciu_direccion character varying(150),
    ciu_telefono character varying(50),
    ciu_email character varying(500),
    ciudad_codi integer,
    ciu_estado smallint DEFAULT 1,
    ciu_referencia character varying
);


--
-- TOC entry 3093 (class 0 OID 0)
-- Dependencies: 180
-- Name: TABLE ciudadano_tmp; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE ciudadano_tmp IS 'Tabla temporal en la que se guardan los cambios realizados a ciudadanos hasta que sean autorizados por un usuario con los permisos correspondientes';


--
-- TOC entry 3094 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_codigo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_codigo IS 'Id del usuario';


--
-- TOC entry 3095 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_cedula; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_cedula IS 'Número de cédula de ciudadanía';


--
-- TOC entry 3096 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_documento; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_documento IS 'Número de identificación adicional (RUC, pasaporte, etc.)';


--
-- TOC entry 3097 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_nombre IS 'Nombre de la persona';


--
-- TOC entry 3098 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_apellido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_apellido IS 'Apellido de la persona';


--
-- TOC entry 3099 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_titulo IS 'Título o tratamiento (Señor, Ingeniero, etc.)';


--
-- TOC entry 3100 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_abr_titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_abr_titulo IS 'Abreviación del título o tratamiento (Sr., Ing., etc.)';


--
-- TOC entry 3101 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_empresa; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_empresa IS 'Nombre de la empresa a la que pertenece';


--
-- TOC entry 3102 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_cargo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_cargo IS 'Cargo que desempeña en su empresa';


--
-- TOC entry 3103 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_direccion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_direccion IS 'Dirección Domiciliaria';


--
-- TOC entry 3104 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_telefono; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_telefono IS 'Número telefónico';


--
-- TOC entry 3105 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_email IS 'Correo electrónico';


--
-- TOC entry 3106 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciudad_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciudad_codi IS 'Id de la ciudad en la que se encuentra la persona';


--
-- TOC entry 3107 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_estado IS 'Determina si la solicitud de cambio ya fue revisada por un administrador';


--
-- TOC entry 3108 (class 0 OID 0)
-- Dependencies: 180
-- Name: COLUMN ciudadano_tmp.ciu_referencia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN ciudadano_tmp.ciu_referencia IS 'Datos de referencia de la dirección del domicilio';


--
-- TOC entry 181 (class 1259 OID 53106)
-- Dependencies: 5
-- Name: codificacion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE codificacion (
    cod_codi integer NOT NULL,
    cod_descripcion character varying(150) NOT NULL,
    inst_codi bigint
);


--
-- TOC entry 3109 (class 0 OID 0)
-- Dependencies: 181
-- Name: TABLE codificacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE codificacion IS 'Codificación o tipificación de los documentos';


--
-- TOC entry 3110 (class 0 OID 0)
-- Dependencies: 181
-- Name: COLUMN codificacion.cod_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN codificacion.cod_codi IS 'Id';


--
-- TOC entry 3111 (class 0 OID 0)
-- Dependencies: 181
-- Name: COLUMN codificacion.cod_descripcion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN codificacion.cod_descripcion IS 'Descripción';


--
-- TOC entry 3112 (class 0 OID 0)
-- Dependencies: 181
-- Name: COLUMN codificacion.inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN codificacion.inst_codi IS 'Institución a la que pertenece la codificación';


--
-- TOC entry 182 (class 1259 OID 53109)
-- Dependencies: 5 181
-- Name: codificacion_cod_codi_seq1; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE codificacion_cod_codi_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3113 (class 0 OID 0)
-- Dependencies: 182
-- Name: codificacion_cod_codi_seq1; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE codificacion_cod_codi_seq1 OWNED BY codificacion.cod_codi;


--
-- TOC entry 3114 (class 0 OID 0)
-- Dependencies: 182
-- Name: codificacion_cod_codi_seq1; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('codificacion_cod_codi_seq1', 1, false);


--
-- TOC entry 183 (class 1259 OID 53111)
-- Dependencies: 5
-- Name: hist_eventos_hist_codi_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE hist_eventos_hist_codi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3115 (class 0 OID 0)
-- Dependencies: 183
-- Name: hist_eventos_hist_codi_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('hist_eventos_hist_codi_seq', 1, false);


--
-- TOC entry 184 (class 1259 OID 53113)
-- Dependencies: 2479 5
-- Name: hist_eventos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE hist_eventos (
    hist_fech timestamp with time zone NOT NULL,
    usua_codi_ori integer NOT NULL,
    radi_nume_radi numeric(20,0) NOT NULL,
    hist_obse character varying(600) NOT NULL,
    usua_codi_dest integer,
    sgd_ttr_codigo smallint,
    hist_codi bigint DEFAULT nextval('hist_eventos_hist_codi_seq'::regclass) NOT NULL,
    hist_referencia character varying(50)
);


--
-- TOC entry 3116 (class 0 OID 0)
-- Dependencies: 184
-- Name: TABLE hist_eventos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE hist_eventos IS 'Registro de las transacciones realizadas con los documentos';


--
-- TOC entry 3117 (class 0 OID 0)
-- Dependencies: 184
-- Name: COLUMN hist_eventos.hist_fech; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_eventos.hist_fech IS 'Fecha de la transacción';


--
-- TOC entry 3118 (class 0 OID 0)
-- Dependencies: 184
-- Name: COLUMN hist_eventos.usua_codi_ori; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_eventos.usua_codi_ori IS 'Usuario que realizó la transacción';


--
-- TOC entry 3119 (class 0 OID 0)
-- Dependencies: 184
-- Name: COLUMN hist_eventos.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_eventos.radi_nume_radi IS 'Id del documento';


--
-- TOC entry 3120 (class 0 OID 0)
-- Dependencies: 184
-- Name: COLUMN hist_eventos.hist_obse; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_eventos.hist_obse IS 'Observaciones';


--
-- TOC entry 3121 (class 0 OID 0)
-- Dependencies: 184
-- Name: COLUMN hist_eventos.usua_codi_dest; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_eventos.usua_codi_dest IS 'Codigo del usuario destino, en caso que la transacción involucre a más de un usuario';


--
-- TOC entry 3122 (class 0 OID 0)
-- Dependencies: 184
-- Name: COLUMN hist_eventos.sgd_ttr_codigo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_eventos.sgd_ttr_codigo IS 'Id de la transacción';


--
-- TOC entry 3123 (class 0 OID 0)
-- Dependencies: 184
-- Name: COLUMN hist_eventos.hist_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_eventos.hist_codi IS 'Id';


--
-- TOC entry 3124 (class 0 OID 0)
-- Dependencies: 184
-- Name: COLUMN hist_eventos.hist_referencia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_eventos.hist_referencia IS 'Campo adicional para guardar códigos o fechas o datos adicionales dependiendo de la transacción';


--
-- TOC entry 185 (class 1259 OID 53120)
-- Dependencies: 5
-- Name: informados_info_codi_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE informados_info_codi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3125 (class 0 OID 0)
-- Dependencies: 185
-- Name: informados_info_codi_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('informados_info_codi_seq', 1, false);


--
-- TOC entry 186 (class 1259 OID 53122)
-- Dependencies: 2480 2481 5
-- Name: informados; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE informados (
    radi_nume_radi numeric(20,0) NOT NULL,
    info_desc character varying(600),
    info_fech date NOT NULL,
    info_leido smallint DEFAULT 0,
    usua_codi integer,
    usua_info integer,
    info_codi bigint DEFAULT nextval('informados_info_codi_seq'::regclass) NOT NULL
);


--
-- TOC entry 3126 (class 0 OID 0)
-- Dependencies: 186
-- Name: TABLE informados; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE informados IS 'Documentos informados';


--
-- TOC entry 3127 (class 0 OID 0)
-- Dependencies: 186
-- Name: COLUMN informados.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN informados.radi_nume_radi IS 'id del documento';


--
-- TOC entry 3128 (class 0 OID 0)
-- Dependencies: 186
-- Name: COLUMN informados.info_desc; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN informados.info_desc IS 'Comentario';


--
-- TOC entry 3129 (class 0 OID 0)
-- Dependencies: 186
-- Name: COLUMN informados.info_fech; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN informados.info_fech IS 'Fecha';


--
-- TOC entry 3130 (class 0 OID 0)
-- Dependencies: 186
-- Name: COLUMN informados.info_leido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN informados.info_leido IS 'Bandera que indica si ya fue leido por el destinatario';


--
-- TOC entry 3131 (class 0 OID 0)
-- Dependencies: 186
-- Name: COLUMN informados.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN informados.usua_codi IS 'Usuario destinatario';


--
-- TOC entry 3132 (class 0 OID 0)
-- Dependencies: 186
-- Name: COLUMN informados.usua_info; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN informados.usua_info IS 'Usuario Informador';


--
-- TOC entry 3133 (class 0 OID 0)
-- Dependencies: 186
-- Name: COLUMN informados.info_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN informados.info_codi IS 'Id';


--
-- TOC entry 187 (class 1259 OID 53130)
-- Dependencies: 2482 2483 2484 2485 2486 2487 2488 2489 2490 2491 2492 2493 2494 5
-- Name: radicado; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE radicado (
    radi_nume_radi numeric(20,0) NOT NULL,
    radi_nume_text character varying(50),
    radi_nume_temp numeric(20,0) NOT NULL,
    radi_fech_radi timestamp with time zone NOT NULL,
    radi_fech_ofic timestamp with time zone,
    radi_nume_deri numeric(20,0),
    radi_path character varying(150),
    esta_codi smallint,
    radi_usua_actu integer,
    radi_fech_asig timestamp with time zone,
    radi_leido smallint DEFAULT 0,
    radi_fech_agend timestamp with time zone,
    radi_cca character varying,
    radi_cuentai character varying(50),
    radi_asunto character varying(350),
    radi_resumen character varying(1000),
    radi_desc_anex character varying(100),
    radi_flag_impr smallint,
    radi_texto integer,
    radi_tipo smallint,
    radi_usua_rem character varying,
    radi_usua_ante integer,
    radi_usua_dest character varying,
    radi_usua_radi integer,
    radi_permiso smallint DEFAULT 0,
    radi_nomb_usua_firma character varying,
    radi_fech_firma timestamp with time zone,
    radi_inst_actu integer,
    radi_archivo smallint DEFAULT 0,
    usar_plantilla integer DEFAULT 0,
    ajust_texto integer DEFAULT 100,
    radi_tipo_impresion character varying(1) DEFAULT 1,
    radi_lista_dest character varying,
    radi_tipo_archivo smallint DEFAULT 0,
    cod_codi bigint DEFAULT 0,
    cat_codi bigint DEFAULT 0,
    radi_ocultar_recorrido smallint DEFAULT 0,
    radi_usua_redirigido bigint DEFAULT 0,
    radi_text_temp character varying(50),
    radi_nume_asoc numeric(20,0),
    arch_codi bigint DEFAULT 0,
    arch_codi_firma bigint DEFAULT 0,
    radi_imagen character varying(50)
);
ALTER TABLE ONLY radicado ALTER COLUMN radi_nume_text SET STORAGE EXTERNAL;


--
-- TOC entry 3134 (class 0 OID 0)
-- Dependencies: 187
-- Name: TABLE radicado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE radicado IS 'Información de los documentos registrados';


--
-- TOC entry 3135 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_nume_radi IS 'Id del documento';


--
-- TOC entry 3136 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_nume_text; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_nume_text IS 'Número del documento según el formato definido en la institución';


--
-- TOC entry 3137 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_nume_temp; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_nume_temp IS 'Id del documento padre (desde el que se generan las copias para cada destinatario)';


--
-- TOC entry 3138 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_fech_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_fech_radi IS 'Fecha en la que se creó el documento';


--
-- TOC entry 3139 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_fech_ofic; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_fech_ofic IS 'Fecha en la que se firma y se envía el documento o fecha de referencia en el caso de documentos externos';


--
-- TOC entry 3140 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_nume_deri; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_nume_deri IS 'Id del documento al cual se encuentra asociado el documento actual (responder)';


--
-- TOC entry 3141 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_path; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_path IS 'Path donde se encuentra el archivo PDF en la bodega';


--
-- TOC entry 3142 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.esta_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.esta_codi IS 'Estado en el que se encuentra el documento';


--
-- TOC entry 3143 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_usua_actu; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_usua_actu IS 'Id del usuario actual del documento';


--
-- TOC entry 3144 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_fech_asig; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_fech_asig IS 'Fecha máxima para realización de trámite cuando se reasigna un documento';


--
-- TOC entry 3145 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_leido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_leido IS 'bandera que indica si el documento ya fue leido';


--
-- TOC entry 3146 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_fech_agend; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_fech_agend IS 'Campo en desuso';


--
-- TOC entry 3147 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_cca; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_cca IS 'Lista de usuarios para enviar copias, se separan por guiones (-id1--id2-)';


--
-- TOC entry 3148 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_cuentai; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_cuentai IS 'Numero de referencia del documento';


--
-- TOC entry 3149 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_asunto; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_asunto IS 'Asunto del documento';


--
-- TOC entry 3150 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_resumen; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_resumen IS 'Notas adicionales al documento';


--
-- TOC entry 3151 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_desc_anex; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_desc_anex IS 'Descripción general de los anexos';


--
-- TOC entry 3152 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_flag_impr; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_flag_impr IS 'Campo en desuso';


--
-- TOC entry 3153 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_texto; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_texto IS 'Id de la version del texto del documento que se está utilizando';


--
-- TOC entry 3154 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_tipo IS 'Tipo de documento (memo, oficio, etc.)';


--
-- TOC entry 3155 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_usua_rem; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_usua_rem IS 'Lista de usuarios remitentes del documento; se separan por guiones (-id1--id2-)';


--
-- TOC entry 3156 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_usua_ante; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_usua_ante IS 'Id del usuario anterior del documento';


--
-- TOC entry 3157 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_usua_dest; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_usua_dest IS 'Lista de usuarios destinatarios del documento; se separan por guiones (-id1--id2-)';


--
-- TOC entry 3158 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_usua_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_usua_radi IS 'Id del usuario que registro el documento';


--
-- TOC entry 3159 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_permiso; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_permiso IS 'nivel de seguridad del documento (publico o confidencial)';


--
-- TOC entry 3160 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_nomb_usua_firma; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_nomb_usua_firma IS 'Datos de la firma electrónica del documento';


--
-- TOC entry 3161 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_fech_firma; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_fech_firma IS 'Fecha en que se firmó electrónicamente el documento (cuando se validó en quipux)';


--
-- TOC entry 3162 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_inst_actu; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_inst_actu IS 'Institucion actual del documento';


--
-- TOC entry 3163 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_archivo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_archivo IS 'Indica si el documento se encuentra archivado físicamente';


--
-- TOC entry 3164 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.usar_plantilla; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.usar_plantilla IS 'Bandera que indica si el documento se generará con una plantilla o en una hoja en blanco';


--
-- TOC entry 3165 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.ajust_texto; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.ajust_texto IS 'Determina si el archivo se comprime o se expande (Tamaño de letra)';


--
-- TOC entry 3166 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_tipo_impresion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_tipo_impresion IS 'Opciones de impresión - Modo de impresión de los datos del destinatario (combo)';


--
-- TOC entry 3167 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_lista_dest; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_lista_dest IS 'Listado de las listas de usuarios seleccionadas para el envío de los documentos';


--
-- TOC entry 3168 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_tipo_archivo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_tipo_archivo IS 'Define si el archivo de la imagen del documento (almacenado en radi_path) es temporal (generada por el sistema y no firmada) o definitiva.';


--
-- TOC entry 3169 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.cod_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.cod_codi IS 'Id de la codificación del documento (tipificación)';


--
-- TOC entry 3170 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.cat_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.cat_codi IS 'Id de la categoría del documento';


--
-- TOC entry 3171 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_ocultar_recorrido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_ocultar_recorrido IS 'Indica si se ocultará el recorrido del documento';


--
-- TOC entry 3172 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_usua_redirigido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_usua_redirigido IS 'Id del usuario al que se redirigirá el documento (registro de documentos externos)';


--
-- TOC entry 3173 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_text_temp; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_text_temp IS 'Número temporal del documento que se le asigno mientras estaba en elaboración';


--
-- TOC entry 3174 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_nume_asoc; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_nume_asoc IS 'Id del documento antecedente (documentos asociados)';


--
-- TOC entry 3175 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.arch_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.arch_codi IS 'Código del archivo almacenado en la BDD de documentos';


--
-- TOC entry 3176 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.arch_codi_firma; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.arch_codi_firma IS 'Código del archivo firmado electrónicamente, almacenado en la BDD de documentos';


--
-- TOC entry 3177 (class 0 OID 0)
-- Dependencies: 187
-- Name: COLUMN radicado.radi_imagen; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado.radi_imagen IS 'Código del anexo cargado como imágen digitalizada';


--
-- TOC entry 188 (class 1259 OID 53149)
-- Dependencies: 2451 5
-- Name: contadores; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW contadores AS
    SELECT r.radi_usua_actu AS usua_codi, count(CASE WHEN (r.esta_codi = 1) THEN 1 ELSE NULL::integer END) AS "En
elaboracion", count(CASE WHEN (r.esta_codi = 2) THEN 1 ELSE NULL::integer END) AS "En tramite", count(CASE WHEN (r.esta_codi = 7) THEN 1 ELSE NULL::integer END) AS "Eliminados", count(CASE WHEN (r.esta_codi = 3) THEN 1 ELSE NULL::integer END) AS "No enviados", count(CASE WHEN ((r.esta_codi = 6) AND (r.radi_nume_radi = r.radi_nume_temp)) THEN 1 ELSE NULL::integer END) AS "Enviados", count(CASE WHEN (r.esta_codi = 0) THEN 1 ELSE NULL::integer END) AS "Archivados", (SELECT count(hist_eventos.radi_nume_radi) AS count FROM hist_eventos WHERE ((hist_eventos.usua_codi_ori = r.radi_usua_actu) AND (hist_eventos.sgd_ttr_codigo = 9))) AS "Reasignados", (SELECT count(*) AS count FROM informados WHERE (informados.usua_codi = r.radi_usua_actu)) AS "Informados" FROM radicado r GROUP BY r.radi_usua_actu;


--
-- TOC entry 189 (class 1259 OID 53154)
-- Dependencies: 5
-- Name: sec_contenido; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_contenido
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3178 (class 0 OID 0)
-- Dependencies: 189
-- Name: sec_contenido; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_contenido', 1, false);


--
-- TOC entry 190 (class 1259 OID 53156)
-- Dependencies: 2495 5
-- Name: contenido; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE contenido (
    cont_codi integer DEFAULT nextval('sec_contenido'::regclass) NOT NULL,
    cont_tipo_codi integer,
    descripcion character varying,
    texto character varying,
    fecha_crea timestamp with time zone,
    fecha_actualiza timestamp with time zone
);


--
-- TOC entry 3179 (class 0 OID 0)
-- Dependencies: 190
-- Name: TABLE contenido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE contenido IS 'Guarda la información que se muestra en las pantallas index y ayuda del sistema';


--
-- TOC entry 3180 (class 0 OID 0)
-- Dependencies: 190
-- Name: COLUMN contenido.cont_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN contenido.cont_codi IS 'Id';


--
-- TOC entry 3181 (class 0 OID 0)
-- Dependencies: 190
-- Name: COLUMN contenido.cont_tipo_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN contenido.cont_tipo_codi IS 'Tipo de contenido, indica en dónde se muestra la información';


--
-- TOC entry 3182 (class 0 OID 0)
-- Dependencies: 190
-- Name: COLUMN contenido.descripcion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN contenido.descripcion IS 'Descripción de la información';


--
-- TOC entry 3183 (class 0 OID 0)
-- Dependencies: 190
-- Name: COLUMN contenido.texto; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN contenido.texto IS 'Texto en formato HTML que se muestra en las páginas';


--
-- TOC entry 3184 (class 0 OID 0)
-- Dependencies: 190
-- Name: COLUMN contenido.fecha_crea; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN contenido.fecha_crea IS 'Fecha de creación';


--
-- TOC entry 3185 (class 0 OID 0)
-- Dependencies: 190
-- Name: COLUMN contenido.fecha_actualiza; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN contenido.fecha_actualiza IS 'Fecha de actualización';


--
-- TOC entry 191 (class 1259 OID 53163)
-- Dependencies: 5
-- Name: contenido_tipo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE contenido_tipo (
    cont_tipo_codi integer NOT NULL,
    funcionalidad character varying,
    categoria character varying
);


--
-- TOC entry 3186 (class 0 OID 0)
-- Dependencies: 191
-- Name: TABLE contenido_tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE contenido_tipo IS 'Catálogo con los tipos de contenidos que se muestran en las pantallas de index y ayuda del sistema';


--
-- TOC entry 3187 (class 0 OID 0)
-- Dependencies: 191
-- Name: COLUMN contenido_tipo.cont_tipo_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN contenido_tipo.cont_tipo_codi IS 'Id';


--
-- TOC entry 3188 (class 0 OID 0)
-- Dependencies: 191
-- Name: COLUMN contenido_tipo.funcionalidad; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN contenido_tipo.funcionalidad IS 'Página a la que pertenece';


--
-- TOC entry 3189 (class 0 OID 0)
-- Dependencies: 191
-- Name: COLUMN contenido_tipo.categoria; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN contenido_tipo.categoria IS 'Categoría o tipo de contenido';


--
-- TOC entry 193 (class 1259 OID 53176)
-- Dependencies: 2496 2497 2498 2499 2500 2501 2502 5
-- Name: solicitud_firma_ciudadano; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE solicitud_firma_ciudadano (
    sol_codigo integer DEFAULT nextval(('public.solicitud_usua_codi_seq'::text)::regclass) NOT NULL,
    ciu_codigo integer,
    sol_observaciones character varying(700),
    sol_firma smallint,
    sol_estado smallint,
    sol_planilla smallint DEFAULT 0,
    sol_cedula smallint DEFAULT 0,
    sol_acuerdo smallint DEFAULT 0,
    sol_planilla_estado smallint DEFAULT 0,
    sol_cedula_estado smallint DEFAULT 0,
    sol_acuerdo_estado smallint DEFAULT 0,
    ciu_nombre character varying(150),
    ciu_direccion character varying(150),
    ciu_empresa character varying(150),
    ciu_cargo character varying(150),
    ciu_apellido character varying(150),
    ciu_cedula character varying(25),
    ciu_telefono character varying(50),
    ciu_email character varying(50),
    ciu_abr_titulo character varying(30),
    ciu_titulo character varying(100),
    ciu_documento character varying(50),
    ciudad_codi integer,
    sol_fecha_envio timestamp with time zone,
    sol_fecha_autorizado timestamp with time zone,
    ciu_referencia character varying
);


--
-- TOC entry 3190 (class 0 OID 0)
-- Dependencies: 193
-- Name: TABLE solicitud_firma_ciudadano; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE solicitud_firma_ciudadano IS 'Contiene las solicitudes de ciudadanos que desean utilizar el sistema Quipux para envío de documentación electrónica a las instituciones (debe tener firma digital)';


--
-- TOC entry 3191 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.sol_codigo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.sol_codigo IS 'Id de la solicitud';


--
-- TOC entry 3192 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.ciu_codigo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.ciu_codigo IS 'Id del ciudadano';


--
-- TOC entry 3193 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.sol_observaciones; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.sol_observaciones IS 'Observaciones adicionales';


--
-- TOC entry 3194 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.sol_firma; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.sol_firma IS 'Tipo de certificado de firma electrónica';


--
-- TOC entry 3195 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.sol_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.sol_estado IS 'Estado de la solicitud';


--
-- TOC entry 3196 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.sol_planilla; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.sol_planilla IS 'Indica si cargó el archivo de la planilla de servicios';


--
-- TOC entry 3197 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.sol_cedula; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.sol_cedula IS 'Indica si subió archivo con la copia de la cédula';


--
-- TOC entry 3198 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.sol_acuerdo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.sol_acuerdo IS 'Indica si subió el archivo con el acuerdo firmado electrónicamente';


--
-- TOC entry 3199 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.sol_planilla_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.sol_planilla_estado IS 'Indica si el administrador validó el archivo planilla subido';


--
-- TOC entry 3200 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.sol_cedula_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.sol_cedula_estado IS 'Indica si el administrador validó el archivo cédula subido';


--
-- TOC entry 3201 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.sol_acuerdo_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.sol_acuerdo_estado IS 'Indica si el administrador validó el archivo acuerdo subido';


--
-- TOC entry 3202 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.ciu_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.ciu_nombre IS 'Nombre del ciudadano';


--
-- TOC entry 3203 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.ciu_direccion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.ciu_direccion IS 'Dirección domicilaria';


--
-- TOC entry 3204 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.ciu_empresa; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.ciu_empresa IS 'Nombre de la empresa';


--
-- TOC entry 3205 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.ciu_cargo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.ciu_cargo IS 'Cargo que desempeña en su empresa';


--
-- TOC entry 3206 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.ciu_apellido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.ciu_apellido IS 'Apellido del ciudadano';


--
-- TOC entry 3207 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.ciu_cedula; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.ciu_cedula IS 'Número de cédula';


--
-- TOC entry 3208 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.ciu_telefono; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.ciu_telefono IS 'Número telefónico';


--
-- TOC entry 3209 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.ciu_email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.ciu_email IS 'Correo electrónico';


--
-- TOC entry 3210 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.ciu_abr_titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.ciu_abr_titulo IS 'Abreviación del título';


--
-- TOC entry 3211 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.ciu_titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.ciu_titulo IS 'Tratamiento o título académico';


--
-- TOC entry 3212 (class 0 OID 0)
-- Dependencies: 193
-- Name: COLUMN solicitud_firma_ciudadano.ciu_documento; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN solicitud_firma_ciudadano.ciu_documento IS 'Informacion adicional si no se conoce la cédula';


--
-- TOC entry 194 (class 1259 OID 53189)
-- Dependencies: 2452 5
-- Name: datos_solicitud; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW datos_solicitud AS
    SELECT (((solicitud_firma_ciudadano.ciu_nombre)::text || ' '::text) || (solicitud_firma_ciudadano.ciu_apellido)::text) AS ciu_nombre, solicitud_firma_ciudadano.ciu_cedula, solicitud_firma_ciudadano.ciu_email, solicitud_firma_ciudadano.sol_estado, solicitud_firma_ciudadano.ciu_codigo FROM solicitud_firma_ciudadano;


--
-- TOC entry 195 (class 1259 OID 53193)
-- Dependencies: 5
-- Name: dependencia; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE dependencia (
    depe_codi integer NOT NULL,
    depe_nomb character varying(150) NOT NULL,
    depe_codi_padre integer,
    dep_sigla character varying(100),
    dep_central integer,
    dep_direccion character varying(100),
    depe_estado smallint,
    inst_codi integer,
    depe_plantilla integer,
    depe_pie1 character varying(150),
    depe_pie2 character varying(150),
    depe_pie3 character varying(150),
    inst_adscrita integer
);


--
-- TOC entry 3213 (class 0 OID 0)
-- Dependencies: 195
-- Name: TABLE dependencia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE dependencia IS 'Áreas funcionales de las instituciones, estructura orgánica funcional
Tabla recursiva';


--
-- TOC entry 3214 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.depe_codi IS 'Id del área';


--
-- TOC entry 3215 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.depe_nomb; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.depe_nomb IS 'Nombre del área';


--
-- TOC entry 3216 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.depe_codi_padre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.depe_codi_padre IS 'Id del área padre; código del área superior en el orgánico funcional';


--
-- TOC entry 3217 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.dep_sigla; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.dep_sigla IS 'Siglas del área';


--
-- TOC entry 3218 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.dep_central; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.dep_central IS 'Indica en qué area se encuentra el archivo físico donde se guarda la documentación impresa del área';


--
-- TOC entry 3219 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.dep_direccion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.dep_direccion IS 'Campo en desuso';


--
-- TOC entry 3220 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.depe_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.depe_estado IS 'Estado del área';


--
-- TOC entry 3221 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.inst_codi IS 'Codigo de la institución a la que pertenece';


--
-- TOC entry 3222 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.depe_plantilla; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.depe_plantilla IS 'Dependencia de la que se copiará la plantilla con que se generan los documentos ';


--
-- TOC entry 3223 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.depe_pie1; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.depe_pie1 IS 'Ciudad a la que pertenece el área y que se pondrá por defecto a los usuarios del área';


--
-- TOC entry 3224 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.depe_pie2; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.depe_pie2 IS 'Campo en desuso';


--
-- TOC entry 3225 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.depe_pie3; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.depe_pie3 IS 'Campo en desuso';


--
-- TOC entry 3226 (class 0 OID 0)
-- Dependencies: 195
-- Name: COLUMN dependencia.inst_adscrita; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN dependencia.inst_adscrita IS 'Código de la institución adscrita';


--
-- TOC entry 196 (class 1259 OID 53199)
-- Dependencies: 2503 5
-- Name: institucion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE institucion (
    inst_ruc character varying(14),
    inst_nombre character varying(200),
    inst_logo character varying(100),
    inst_sigla character varying(10),
    inst_pie1 character varying(150),
    inst_pie2 character varying(150),
    inst_pie3 character varying(150),
    inst_codi integer NOT NULL,
    inst_estado integer,
    inst_coordinador smallint DEFAULT 0,
    inst_telefono character varying(30),
    inst_despedida_ofi character varying,
    inst_email character varying(50),
    inst_ws_wsdl character varying(500),
    inst_ws_usuario character varying(100),
    inst_ws_contrasena character varying(100)
);


--
-- TOC entry 3227 (class 0 OID 0)
-- Dependencies: 196
-- Name: TABLE institucion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE institucion IS 'Instituciones registradas';


--
-- TOC entry 3228 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_ruc; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_ruc IS 'RUC de la Institución';


--
-- TOC entry 3229 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_nombre IS 'Nombre de la institución';


--
-- TOC entry 3230 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_logo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_logo IS 'Path donde se encuentra la imágen con el logo institucional';


--
-- TOC entry 3231 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_sigla; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_sigla IS 'Siglas de la institución';


--
-- TOC entry 3232 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_pie1; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_pie1 IS 'Campo en desuso';


--
-- TOC entry 3233 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_pie2; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_pie2 IS 'Campo en desuso';


--
-- TOC entry 3234 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_pie3; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_pie3 IS 'Campo en desuso';


--
-- TOC entry 3235 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_codi IS 'Id';


--
-- TOC entry 3236 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_estado IS 'Estado, activa o inactiva';


--
-- TOC entry 3237 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_coordinador; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_coordinador IS 'Id del ministerio coordinador';


--
-- TOC entry 3238 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_telefono; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_telefono IS 'Número telefónico';


--
-- TOC entry 3239 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_despedida_ofi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_despedida_ofi IS 'Frase de despedida por defecto que saldrá en los documentos (Ejm: Dios, Patria y Libertad)';


--
-- TOC entry 3240 (class 0 OID 0)
-- Dependencies: 196
-- Name: COLUMN institucion.inst_email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion.inst_email IS 'email para soporte institucional';


--
-- TOC entry 197 (class 1259 OID 53206)
-- Dependencies: 5
-- Name: usuarios_usua_codi_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE usuarios_usua_codi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3241 (class 0 OID 0)
-- Dependencies: 197
-- Name: usuarios_usua_codi_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('usuarios_usua_codi_seq', 1, false);


--
-- TOC entry 198 (class 1259 OID 53208)
-- Dependencies: 2504 2505 2506 2507 2508 2509 2510 2511 2512 5
-- Name: usuarios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE usuarios (
    usua_login character varying(50),
    usua_pasw character varying(35),
    usua_nomb character varying(200),
    usua_cedula character varying(50),
    usua_email character varying(500),
    usua_titulo character varying(100),
    usua_abr_titulo character varying(30),
    usua_esta smallint DEFAULT 1,
    usua_codi integer DEFAULT nextval('usuarios_usua_codi_seq'::regclass) NOT NULL,
    cargo_tipo smallint DEFAULT 0,
    depe_codi integer,
    usua_nuevo smallint DEFAULT 1,
    usua_tipo smallint DEFAULT 2,
    usua_cargo character varying(200),
    inst_codi integer,
    usua_apellido character varying(200),
    cargo_id integer,
    usua_obs text,
    ciu_codi integer,
    usua_genero character(1),
    usua_firma_path character varying,
    usua_direccion character varying,
    usua_telefono character varying,
    usua_codi_actualiza integer,
    usua_fecha_actualiza timestamp with time zone,
    usua_obs_actualiza character varying,
    usua_cargo_cabecera character varying(200),
    usua_sumilla character varying(50),
    usua_responsable_area integer DEFAULT 0,
    inst_nombre character varying(200),
    usua_tipo_certificado smallint DEFAULT 0,
    visible_sub integer DEFAULT 1,
    usua_subrogado integer,
    usua_celular character varying,
    tipo_identificacion integer DEFAULT 0
);


--
-- TOC entry 3242 (class 0 OID 0)
-- Dependencies: 198
-- Name: TABLE usuarios; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE usuarios IS 'Datos de los usuarios del sistema ';


--
-- TOC entry 3243 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_login; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_login IS 'Login del usuario (deben comenzar con "U"); existen usuarios especiales que comienzan con ''UUSR'' y ''UADM''';


--
-- TOC entry 3244 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_pasw; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_pasw IS 'Contraseña del usuario en md5';


--
-- TOC entry 3245 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_nomb; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_nomb IS 'Nombre del usuario';


--
-- TOC entry 3246 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_cedula; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_cedula IS 'Número de cédula';


--
-- TOC entry 3247 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_email IS 'Email, pueden ser varios separados por comas';


--
-- TOC entry 3248 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_titulo IS 'Tratamiento o título académico';


--
-- TOC entry 3249 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_abr_titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_abr_titulo IS 'Abreviacion del titulo';


--
-- TOC entry 3250 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_esta; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_esta IS 'Estado del usuario, activo o inactivo';


--
-- TOC entry 3251 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_codi IS 'Id del usuario';


--
-- TOC entry 3252 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.cargo_tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.cargo_tipo IS '0 normal  1 jefe     2  asistente';


--
-- TOC entry 3253 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.depe_codi IS 'Área a la que pertenece el usuario';


--
-- TOC entry 3254 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_nuevo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_nuevo IS 'Determina si el usuario ya cambió su clave del sistema o si se debe enviar el email para cambio de clave';


--
-- TOC entry 3255 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_tipo IS 'si el usuario es interno o externo';


--
-- TOC entry 3256 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_cargo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_cargo IS 'Cargo del usuario';


--
-- TOC entry 3257 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.inst_codi IS 'Institución a la que pertenece el usuario';


--
-- TOC entry 3258 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_apellido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_apellido IS 'Apellido del usuario';


--
-- TOC entry 3259 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.cargo_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.cargo_id IS 'Campo en desuso';


--
-- TOC entry 3260 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_obs; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_obs IS 'Observaciones sobre el usuario';


--
-- TOC entry 3261 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.ciu_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.ciu_codi IS 'Id de la ciudad a la que pertenece el usuario';


--
-- TOC entry 3262 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_firma_path; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_firma_path IS 'Path en el que se encuentra la imágen escaneada de la firma';


--
-- TOC entry 3263 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_direccion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_direccion IS 'Dirección domiciliaria';


--
-- TOC entry 3264 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_telefono; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_telefono IS 'Número telefónico';


--
-- TOC entry 3265 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_codi_actualiza; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_codi_actualiza IS 'Id del usuario que realizó la ultima modificación de los datos';


--
-- TOC entry 3266 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_fecha_actualiza; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_fecha_actualiza IS 'Fecha en la que se realizó la última modificación de los datos';


--
-- TOC entry 3267 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_obs_actualiza; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_obs_actualiza IS 'Cambios realizados durante la última modificación del usuario';


--
-- TOC entry 3268 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_cargo_cabecera; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_cargo_cabecera IS 'Cargo que se muestra cuando se selecciona al usuario como destinatario';


--
-- TOC entry 3269 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_sumilla; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_sumilla IS 'Iniciales del usuario utilizadas cuando este tiene responsabilidad en la elaboración de un documento';


--
-- TOC entry 3270 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_responsable_area; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_responsable_area IS 'Indica que el usuario es responsable del area, razón por la cual la inicial de sus sumilla se
visualizará en todos los documentos generados en el área y con mayúsculas';


--
-- TOC entry 3271 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.inst_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.inst_nombre IS 'Nombre de la institución a la que pertenece el usuario';


--
-- TOC entry 3272 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_tipo_certificado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_tipo_certificado IS 'Id del tipo de certificado digital que posee';


--
-- TOC entry 3273 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.visible_sub; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.visible_sub IS 'Indica si el usuario ha sido subrogado';


--
-- TOC entry 3274 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_subrogado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_subrogado IS 'Id del usuario subrogado';


--
-- TOC entry 3275 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.usua_celular; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.usua_celular IS 'No. del teléfono celular';


--
-- TOC entry 3276 (class 0 OID 0)
-- Dependencies: 198
-- Name: COLUMN usuarios.tipo_identificacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios.tipo_identificacion IS '0 cedula  1 pasaporte';


--
-- TOC entry 199 (class 1259 OID 53223)
-- Dependencies: 2453 5
-- Name: datos_usuarios; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW datos_usuarios AS
    SELECT u.usua_codi, u.usua_cedula, u.usua_login, u.usua_nomb, u.usua_apellido, (((COALESCE(u.usua_nomb, ''::character varying))::text || ' '::text) || (COALESCE(u.usua_apellido, ''::character varying))::text) AS usua_nombre, u.inst_codi, CASE WHEN (i.inst_codi = 1) THEN u.inst_nombre ELSE i.inst_nombre END AS inst_nombre, i.inst_estado, i.inst_sigla, u.usua_cargo, u.usua_cargo_cabecera, u.usua_titulo, u.usua_abr_titulo, u.usua_email, u.usua_esta, u.cargo_tipo, u.depe_codi, d.depe_nomb, d.dep_sigla, CASE WHEN (i.inst_codi = 1) THEN 2 ELSE 1 END AS tipo_usuario, u.usua_direccion, u.usua_telefono, (SELECT c.nombre FROM ciudad c WHERE (COALESCE(u.ciu_codi, (COALESCE(d.depe_pie1, '1'::character varying))::integer) = c.id)) AS usua_ciudad, u.usua_firma_path, u.usua_tipo_certificado, u.visible_sub FROM ((usuarios u LEFT JOIN dependencia d ON ((u.depe_codi = d.depe_codi))) LEFT JOIN institucion i ON ((i.inst_codi = u.inst_codi))) UNION ALL SELECT u.ciu_codigo AS usua_codi, u.ciu_cedula AS usua_cedula, CASE WHEN (u.ciu_estado = 1) THEN ('U'::text || (u.ciu_cedula)::text) ELSE ('l'::text || (u.ciu_codigo)::text) END AS usua_login, u.ciu_nombre AS usua_nomb, u.ciu_apellido AS usua_apellido, (((COALESCE(u.ciu_nombre, ''::character varying))::text || ' '::text) || (COALESCE(u.ciu_apellido, ''::character varying))::text) AS usua_nombre, 0 AS inst_codi, u.ciu_empresa AS inst_nombre, 1 AS inst_estado, ''::character varying AS inst_sigla, u.ciu_cargo AS usua_cargo, u.ciu_cargo AS usua_cargo_cabecera, u.ciu_titulo AS usua_titulo, u.ciu_abr_titulo AS usua_abr_titulo, u.ciu_email AS usua_email, u.ciu_estado AS usua_esta, 0 AS cargo_tipo, NULL::integer AS depe_codi, NULL::character varying AS depe_nomb, NULL::character varying AS dep_sigla, 2 AS tipo_usuario, u.ciu_direccion AS usua_direccion, u.ciu_telefono AS usua_telefono, (SELECT c.nombre FROM ciudad c WHERE (COALESCE(u.ciudad_codi, 1) = c.id)) AS usua_ciudad, ''::character varying AS usua_firma_path, 0 AS usua_tipo_certificado, 0 AS visible_sub FROM ciudadano u;


--
-- TOC entry 200 (class 1259 OID 53228)
-- Dependencies: 5
-- Name: estado; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE estado (
    esta_codi smallint NOT NULL,
    esta_desc character varying(100) NOT NULL
);


--
-- TOC entry 3277 (class 0 OID 0)
-- Dependencies: 200
-- Name: TABLE estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE estado IS 'Estados en los que puede estar un documento';


--
-- TOC entry 3278 (class 0 OID 0)
-- Dependencies: 200
-- Name: COLUMN estado.esta_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN estado.esta_codi IS 'ESTA_CODI';


--
-- TOC entry 3279 (class 0 OID 0)
-- Dependencies: 200
-- Name: COLUMN estado.esta_desc; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN estado.esta_desc IS 'ESTA_DESC';


--
-- TOC entry 201 (class 1259 OID 53231)
-- Dependencies: 2513 2514 2515 2516 5
-- Name: formato_numeracion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE formato_numeracion (
    fn_abr_texto character varying(10),
    fn_formato character varying(45),
    depe_codi integer NOT NULL,
    fn_caracter character varying(5) DEFAULT '-'::character varying,
    fn_num_consec smallint DEFAULT 4,
    fn_num_anio smallint DEFAULT 4,
    depe_numeracion integer,
    fn_contador integer DEFAULT 0,
    fn_tiporad smallint NOT NULL
);


--
-- TOC entry 3280 (class 0 OID 0)
-- Dependencies: 201
-- Name: TABLE formato_numeracion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE formato_numeracion IS 'Maneja los formatos de la numeración y los números secuenciales de los documentos';


--
-- TOC entry 3281 (class 0 OID 0)
-- Dependencies: 201
-- Name: COLUMN formato_numeracion.fn_abr_texto; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN formato_numeracion.fn_abr_texto IS 'Abreviatura del tipo de documento';


--
-- TOC entry 3282 (class 0 OID 0)
-- Dependencies: 201
-- Name: COLUMN formato_numeracion.fn_formato; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN formato_numeracion.fn_formato IS 'Formato de la numeración';


--
-- TOC entry 3283 (class 0 OID 0)
-- Dependencies: 201
-- Name: COLUMN formato_numeracion.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN formato_numeracion.depe_codi IS 'Área a la que pertenece ese formato; cada área puede tener su propio formato de numeración';


--
-- TOC entry 3284 (class 0 OID 0)
-- Dependencies: 201
-- Name: COLUMN formato_numeracion.fn_caracter; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN formato_numeracion.fn_caracter IS 'Caracter que servirá para separar la numeración; por defecto "-"';


--
-- TOC entry 3285 (class 0 OID 0)
-- Dependencies: 201
-- Name: COLUMN formato_numeracion.fn_num_consec; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN formato_numeracion.fn_num_consec IS 'Número de digitos con los que se mostrará número secuencial';


--
-- TOC entry 3286 (class 0 OID 0)
-- Dependencies: 201
-- Name: COLUMN formato_numeracion.fn_num_anio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN formato_numeracion.fn_num_anio IS 'Numero de digitos con los que se mostrará el año';


--
-- TOC entry 3287 (class 0 OID 0)
-- Dependencies: 201
-- Name: COLUMN formato_numeracion.depe_numeracion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN formato_numeracion.depe_numeracion IS 'Dependencia de la que se tomará el formato y la numeración, en el caso que se desee utilizar la de otra área';


--
-- TOC entry 3288 (class 0 OID 0)
-- Dependencies: 201
-- Name: COLUMN formato_numeracion.fn_contador; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN formato_numeracion.fn_contador IS 'Secuencia actual del documento';


--
-- TOC entry 3289 (class 0 OID 0)
-- Dependencies: 201
-- Name: COLUMN formato_numeracion.fn_tiporad; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN formato_numeracion.fn_tiporad IS 'Tipo de documento (oficio, memo, etc.)';


--
-- TOC entry 202 (class 1259 OID 53238)
-- Dependencies: 2517 5
-- Name: hist_envio_fisico; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE hist_envio_fisico (
    hist_fech_envio character varying(50),
    hist_codi numeric(30,0) NOT NULL,
    radi_nume_radi numeric(20,0) NOT NULL,
    usua_codi_enviado integer NOT NULL,
    usua_responsable character varying(150) NOT NULL,
    estado character varying NOT NULL,
    estadoenvio bit(1) DEFAULT B'0'::"bit" NOT NULL,
    his_id integer NOT NULL
);


--
-- TOC entry 3290 (class 0 OID 0)
-- Dependencies: 202
-- Name: TABLE hist_envio_fisico; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE hist_envio_fisico IS 'Registro de los traspasos físicos de documentos de una persona a otra';


--
-- TOC entry 3291 (class 0 OID 0)
-- Dependencies: 202
-- Name: COLUMN hist_envio_fisico.hist_fech_envio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_envio_fisico.hist_fech_envio IS 'Fecha del traspaso';


--
-- TOC entry 3292 (class 0 OID 0)
-- Dependencies: 202
-- Name: COLUMN hist_envio_fisico.hist_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_envio_fisico.hist_codi IS 'Id de la transacción en hist_eventos';


--
-- TOC entry 3293 (class 0 OID 0)
-- Dependencies: 202
-- Name: COLUMN hist_envio_fisico.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_envio_fisico.radi_nume_radi IS 'Id del documento';


--
-- TOC entry 3294 (class 0 OID 0)
-- Dependencies: 202
-- Name: COLUMN hist_envio_fisico.usua_codi_enviado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_envio_fisico.usua_codi_enviado IS 'Destinatario del documento';


--
-- TOC entry 3295 (class 0 OID 0)
-- Dependencies: 202
-- Name: COLUMN hist_envio_fisico.usua_responsable; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_envio_fisico.usua_responsable IS 'Responsable del traslado';


--
-- TOC entry 3296 (class 0 OID 0)
-- Dependencies: 202
-- Name: COLUMN hist_envio_fisico.estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_envio_fisico.estado IS 'Estado (material) en el que se encuentra el documento físico enviado
B - Bueno
M - Malo
R - Regular';


--
-- TOC entry 3297 (class 0 OID 0)
-- Dependencies: 202
-- Name: COLUMN hist_envio_fisico.estadoenvio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_envio_fisico.estadoenvio IS 'Indica el estado de la acción de envío, enviado o no enviado';


--
-- TOC entry 3298 (class 0 OID 0)
-- Dependencies: 202
-- Name: COLUMN hist_envio_fisico.his_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_envio_fisico.his_id IS 'Id';


--
-- TOC entry 203 (class 1259 OID 53245)
-- Dependencies: 202 5
-- Name: hist_envio_fisico_his_id_seq1; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE hist_envio_fisico_his_id_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3299 (class 0 OID 0)
-- Dependencies: 203
-- Name: hist_envio_fisico_his_id_seq1; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE hist_envio_fisico_his_id_seq1 OWNED BY hist_envio_fisico.his_id;


--
-- TOC entry 3300 (class 0 OID 0)
-- Dependencies: 203
-- Name: hist_envio_fisico_his_id_seq1; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('hist_envio_fisico_his_id_seq1', 1, false);


--
-- TOC entry 204 (class 1259 OID 53247)
-- Dependencies: 5
-- Name: hist_opc_impresion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE hist_opc_impresion (
    hist_fech_impresion timestamp with time zone,
    hist_codi integer NOT NULL,
    radi_nume_radi numeric(20,0),
    usua_codi_ori integer,
    hist_observacion character varying,
    id_transaccion character varying
);


--
-- TOC entry 3301 (class 0 OID 0)
-- Dependencies: 204
-- Name: TABLE hist_opc_impresion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE hist_opc_impresion IS 'Log de cambios de las opciones de impresión';


--
-- TOC entry 3302 (class 0 OID 0)
-- Dependencies: 204
-- Name: COLUMN hist_opc_impresion.hist_fech_impresion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_opc_impresion.hist_fech_impresion IS 'Fecha que realizó el cambio en opciones de impresión';


--
-- TOC entry 3303 (class 0 OID 0)
-- Dependencies: 204
-- Name: COLUMN hist_opc_impresion.hist_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_opc_impresion.hist_codi IS 'Id';


--
-- TOC entry 3304 (class 0 OID 0)
-- Dependencies: 204
-- Name: COLUMN hist_opc_impresion.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_opc_impresion.radi_nume_radi IS 'Id del documento';


--
-- TOC entry 3305 (class 0 OID 0)
-- Dependencies: 204
-- Name: COLUMN hist_opc_impresion.usua_codi_ori; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_opc_impresion.usua_codi_ori IS 'usuario que realiza el cambio';


--
-- TOC entry 3306 (class 0 OID 0)
-- Dependencies: 204
-- Name: COLUMN hist_opc_impresion.hist_observacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_opc_impresion.hist_observacion IS 'Detalle de los cambios realizados';


--
-- TOC entry 3307 (class 0 OID 0)
-- Dependencies: 204
-- Name: COLUMN hist_opc_impresion.id_transaccion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN hist_opc_impresion.id_transaccion IS 'Si es insert o update';


--
-- TOC entry 205 (class 1259 OID 53253)
-- Dependencies: 5 204
-- Name: hist_opc_impresion_hist_codi_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE hist_opc_impresion_hist_codi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3308 (class 0 OID 0)
-- Dependencies: 205
-- Name: hist_opc_impresion_hist_codi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE hist_opc_impresion_hist_codi_seq OWNED BY hist_opc_impresion.hist_codi;


--
-- TOC entry 3309 (class 0 OID 0)
-- Dependencies: 205
-- Name: hist_opc_impresion_hist_codi_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('hist_opc_impresion_hist_codi_seq', 1, false);


--
-- TOC entry 206 (class 1259 OID 53260)
-- Dependencies: 5
-- Name: institucion_org; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE institucion_org (
    org_id integer NOT NULL,
    org_id_padre integer,
    inst_codi integer NOT NULL,
    fecha_registro timestamp with time zone,
    depe_codi integer
);


--
-- TOC entry 3310 (class 0 OID 0)
-- Dependencies: 206
-- Name: TABLE institucion_org; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE institucion_org IS 'Organigrama de las instituciones del estado';


--
-- TOC entry 3311 (class 0 OID 0)
-- Dependencies: 206
-- Name: COLUMN institucion_org.org_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion_org.org_id IS 'Id';


--
-- TOC entry 3312 (class 0 OID 0)
-- Dependencies: 206
-- Name: COLUMN institucion_org.org_id_padre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion_org.org_id_padre IS 'Id de la entidad padre';


--
-- TOC entry 3313 (class 0 OID 0)
-- Dependencies: 206
-- Name: COLUMN institucion_org.inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion_org.inst_codi IS 'Código de la institución';


--
-- TOC entry 3314 (class 0 OID 0)
-- Dependencies: 206
-- Name: COLUMN institucion_org.fecha_registro; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion_org.fecha_registro IS 'Fecha de registro';


--
-- TOC entry 3315 (class 0 OID 0)
-- Dependencies: 206
-- Name: COLUMN institucion_org.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN institucion_org.depe_codi IS 'Código del área padre';


--
-- TOC entry 208 (class 1259 OID 53275)
-- Dependencies: 5
-- Name: lista_lista_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE lista_lista_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3316 (class 0 OID 0)
-- Dependencies: 208
-- Name: lista_lista_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('lista_lista_id_seq', 1, false);


--
-- TOC entry 209 (class 1259 OID 53277)
-- Dependencies: 2520 2521 2522 5
-- Name: lista; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE lista (
    lista_codi bigint DEFAULT nextval('lista_lista_id_seq'::regclass) NOT NULL,
    lista_nombre character varying(250),
    lista_descripcion character varying(200),
    inst_codi integer,
    usua_codi integer,
    lista_fecha timestamp with time zone,
    lista_orden smallint DEFAULT 0,
    lista_estado smallint DEFAULT 1,
    lista_usua_codi integer
);


--
-- TOC entry 3317 (class 0 OID 0)
-- Dependencies: 209
-- Name: TABLE lista; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE lista IS 'Listas de usuarios para facilitar el envío de documentación';


--
-- TOC entry 3318 (class 0 OID 0)
-- Dependencies: 209
-- Name: COLUMN lista.lista_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN lista.lista_codi IS 'Id';


--
-- TOC entry 3319 (class 0 OID 0)
-- Dependencies: 209
-- Name: COLUMN lista.lista_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN lista.lista_nombre IS 'Nombre de la lista';


--
-- TOC entry 3320 (class 0 OID 0)
-- Dependencies: 209
-- Name: COLUMN lista.lista_descripcion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN lista.lista_descripcion IS 'Descripción de la lista';


--
-- TOC entry 3321 (class 0 OID 0)
-- Dependencies: 209
-- Name: COLUMN lista.inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN lista.inst_codi IS 'Id de la institución';


--
-- TOC entry 3322 (class 0 OID 0)
-- Dependencies: 209
-- Name: COLUMN lista.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN lista.usua_codi IS 'Id del usuario al que pertenece la lista (0 para listas públicas)';


--
-- TOC entry 3323 (class 0 OID 0)
-- Dependencies: 209
-- Name: COLUMN lista.lista_fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN lista.lista_fecha IS 'Fecha de ultima actualizacion';


--
-- TOC entry 3324 (class 0 OID 0)
-- Dependencies: 209
-- Name: COLUMN lista.lista_orden; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN lista.lista_orden IS 'Orden en el que se mostrarán los usuarios de la lista (alfabético o por órden de selección)';


--
-- TOC entry 3325 (class 0 OID 0)
-- Dependencies: 209
-- Name: COLUMN lista.lista_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN lista.lista_estado IS 'Estado de la lista: activo o inactivo';


--
-- TOC entry 3326 (class 0 OID 0)
-- Dependencies: 209
-- Name: COLUMN lista.lista_usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN lista.lista_usua_codi IS 'Id del usuario que modificó la lista';


--
-- TOC entry 210 (class 1259 OID 53283)
-- Dependencies: 5
-- Name: lista_usuarios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE lista_usuarios (
    lista_codi bigint NOT NULL,
    usua_codi integer NOT NULL,
    orden integer
);


--
-- TOC entry 3327 (class 0 OID 0)
-- Dependencies: 210
-- Name: TABLE lista_usuarios; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE lista_usuarios IS 'Relación entre las tablas de usuarios y listas';


--
-- TOC entry 3328 (class 0 OID 0)
-- Dependencies: 210
-- Name: COLUMN lista_usuarios.lista_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN lista_usuarios.lista_codi IS 'Id de la lista';


--
-- TOC entry 3329 (class 0 OID 0)
-- Dependencies: 210
-- Name: COLUMN lista_usuarios.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN lista_usuarios.usua_codi IS 'Id del usuario';


--
-- TOC entry 3330 (class 0 OID 0)
-- Dependencies: 210
-- Name: COLUMN lista_usuarios.orden; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN lista_usuarios.orden IS 'Número en base al cual se ordenan los usuarios';


--
-- TOC entry 211 (class 1259 OID 53286)
-- Dependencies: 5
-- Name: log_log_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE log_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3331 (class 0 OID 0)
-- Dependencies: 211
-- Name: log_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('log_log_id_seq', 1, false);


--
-- TOC entry 212 (class 1259 OID 53288)
-- Dependencies: 2523 5
-- Name: log; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log (
    log_id bigint DEFAULT nextval('log_log_id_seq'::regclass) NOT NULL,
    fecha timestamp with time zone,
    usua_codi integer,
    tabla character varying(100),
    sentencia character varying,
    tipo smallint
);


--
-- TOC entry 3332 (class 0 OID 0)
-- Dependencies: 212
-- Name: TABLE log; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE log IS 'Log de transacciones del sistema, almacena los queries más importantes ejecutados el la BDD';


--
-- TOC entry 3333 (class 0 OID 0)
-- Dependencies: 212
-- Name: COLUMN log.log_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log.log_id IS 'Id';


--
-- TOC entry 3334 (class 0 OID 0)
-- Dependencies: 212
-- Name: COLUMN log.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log.fecha IS 'Fecha en la que se realizo la accion';


--
-- TOC entry 3335 (class 0 OID 0)
-- Dependencies: 212
-- Name: COLUMN log.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log.usua_codi IS 'Id del usuario que realizó la acción';


--
-- TOC entry 3336 (class 0 OID 0)
-- Dependencies: 212
-- Name: COLUMN log.tabla; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log.tabla IS 'Nombre de la tabla que se modifico';


--
-- TOC entry 3337 (class 0 OID 0)
-- Dependencies: 212
-- Name: COLUMN log.sentencia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log.sentencia IS 'Query que se ejecuto';


--
-- TOC entry 3338 (class 0 OID 0)
-- Dependencies: 212
-- Name: COLUMN log.tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log.tipo IS 'Tipo de log:
0 - Error
1 - Update
2 - Insert';


--
-- TOC entry 213 (class 1259 OID 53295)
-- Dependencies: 5
-- Name: sec_log_acceso; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_log_acceso
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3339 (class 0 OID 0)
-- Dependencies: 213
-- Name: sec_log_acceso; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_log_acceso', 1, false);


--
-- TOC entry 214 (class 1259 OID 53297)
-- Dependencies: 2524 5
-- Name: log_acceso; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_acceso (
    log_codi bigint DEFAULT nextval('sec_log_acceso'::regclass) NOT NULL,
    fecha timestamp with time zone,
    usuario character varying(50),
    ip character varying(300),
    intentos integer,
    acceso smallint
);


--
-- TOC entry 3340 (class 0 OID 0)
-- Dependencies: 214
-- Name: TABLE log_acceso; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE log_acceso IS 'Log de los accesos e intentos de acceso de los usuarios al sistema';


--
-- TOC entry 3341 (class 0 OID 0)
-- Dependencies: 214
-- Name: COLUMN log_acceso.log_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_acceso.log_codi IS 'Id';


--
-- TOC entry 3342 (class 0 OID 0)
-- Dependencies: 214
-- Name: COLUMN log_acceso.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_acceso.fecha IS 'Fecha de acceso';


--
-- TOC entry 3343 (class 0 OID 0)
-- Dependencies: 214
-- Name: COLUMN log_acceso.usuario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_acceso.usuario IS 'Login del usuario con el que se intento ingresar';


--
-- TOC entry 3344 (class 0 OID 0)
-- Dependencies: 214
-- Name: COLUMN log_acceso.ip; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_acceso.ip IS 'Ip de la máquina del cliente';


--
-- TOC entry 3345 (class 0 OID 0)
-- Dependencies: 214
-- Name: COLUMN log_acceso.intentos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_acceso.intentos IS 'No. de intento de acceso';


--
-- TOC entry 3346 (class 0 OID 0)
-- Dependencies: 214
-- Name: COLUMN log_acceso.acceso; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_acceso.acceso IS 'Bandera que indica si el usuario ingresó al sistema o no';


--
-- TOC entry 215 (class 1259 OID 53309)
-- Dependencies: 2525 5
-- Name: log_archivo_descarga; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_archivo_descarga (
    log_codi bigint NOT NULL,
    fecha timestamp with time zone,
    usua_codi integer,
    radi_nume_radi numeric(20,0),
    anex_codigo character varying(50),
    arch_tipo smallint DEFAULT 0,
    tipo_descarga character varying(10)
);


--
-- TOC entry 3347 (class 0 OID 0)
-- Dependencies: 215
-- Name: TABLE log_archivo_descarga; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE log_archivo_descarga IS 'Guarda un registro de todos los archivos que son descargados en el sistema';


--
-- TOC entry 3348 (class 0 OID 0)
-- Dependencies: 215
-- Name: COLUMN log_archivo_descarga.log_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_archivo_descarga.log_codi IS 'Id';


--
-- TOC entry 3349 (class 0 OID 0)
-- Dependencies: 215
-- Name: COLUMN log_archivo_descarga.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_archivo_descarga.fecha IS 'Fecha de la descarga';


--
-- TOC entry 3350 (class 0 OID 0)
-- Dependencies: 215
-- Name: COLUMN log_archivo_descarga.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_archivo_descarga.usua_codi IS 'Usuario que descargó el archivo';


--
-- TOC entry 3351 (class 0 OID 0)
-- Dependencies: 215
-- Name: COLUMN log_archivo_descarga.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_archivo_descarga.radi_nume_radi IS 'Número de documento al que pertenece el archivo';


--
-- TOC entry 3352 (class 0 OID 0)
-- Dependencies: 215
-- Name: COLUMN log_archivo_descarga.anex_codigo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_archivo_descarga.anex_codigo IS 'Código del anexo si es que era un adjunto o una imágen digitalizada';


--
-- TOC entry 3353 (class 0 OID 0)
-- Dependencies: 215
-- Name: COLUMN log_archivo_descarga.arch_tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_archivo_descarga.arch_tipo IS 'Define si se descarga un archivo firmado electrónicamente o un archivo sin firma';


--
-- TOC entry 3354 (class 0 OID 0)
-- Dependencies: 215
-- Name: COLUMN log_archivo_descarga.tipo_descarga; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_archivo_descarga.tipo_descarga IS 'Tipo de descarga: descarga, embebido y embebido con acrobat reader';


--
-- TOC entry 216 (class 1259 OID 53313)
-- Dependencies: 5 215
-- Name: log_archivo_descarga_log_codi_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE log_archivo_descarga_log_codi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3355 (class 0 OID 0)
-- Dependencies: 216
-- Name: log_archivo_descarga_log_codi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE log_archivo_descarga_log_codi_seq OWNED BY log_archivo_descarga.log_codi;


--
-- TOC entry 3356 (class 0 OID 0)
-- Dependencies: 216
-- Name: log_archivo_descarga_log_codi_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('log_archivo_descarga_log_codi_seq', 1, false);


--
-- TOC entry 217 (class 1259 OID 53315)
-- Dependencies: 5
-- Name: sec_log_bloqueos_dos; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_log_bloqueos_dos
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3357 (class 0 OID 0)
-- Dependencies: 217
-- Name: sec_log_bloqueos_dos; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_log_bloqueos_dos', 1, false);


--
-- TOC entry 218 (class 1259 OID 53317)
-- Dependencies: 2527 5
-- Name: log_bloqueos_dos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_bloqueos_dos (
    log_codi bigint DEFAULT nextval('sec_log_bloqueos_dos'::regclass) NOT NULL,
    fecha timestamp with time zone,
    usua_codi integer,
    pagina character varying(500),
    navegador character varying(500),
    ip character varying(100),
    num_accesos integer
);


--
-- TOC entry 3358 (class 0 OID 0)
-- Dependencies: 218
-- Name: TABLE log_bloqueos_dos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE log_bloqueos_dos IS 'Registra los usuarios que fueron bloqueados en el sistema por realizar muchas peticiones en un tiempo muy corto';


--
-- TOC entry 3359 (class 0 OID 0)
-- Dependencies: 218
-- Name: COLUMN log_bloqueos_dos.log_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_bloqueos_dos.log_codi IS 'Id';


--
-- TOC entry 3360 (class 0 OID 0)
-- Dependencies: 218
-- Name: COLUMN log_bloqueos_dos.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_bloqueos_dos.fecha IS 'Fecha y hora del bloqueo';


--
-- TOC entry 3361 (class 0 OID 0)
-- Dependencies: 218
-- Name: COLUMN log_bloqueos_dos.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_bloqueos_dos.usua_codi IS 'Código del usuario bloqueado';


--
-- TOC entry 3362 (class 0 OID 0)
-- Dependencies: 218
-- Name: COLUMN log_bloqueos_dos.pagina; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_bloqueos_dos.pagina IS 'Página a la que intentó acceder el usuario';


--
-- TOC entry 3363 (class 0 OID 0)
-- Dependencies: 218
-- Name: COLUMN log_bloqueos_dos.navegador; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_bloqueos_dos.navegador IS 'Datos del navegador del usuario';


--
-- TOC entry 3364 (class 0 OID 0)
-- Dependencies: 218
-- Name: COLUMN log_bloqueos_dos.ip; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_bloqueos_dos.ip IS 'IP desde la que se conectó el usuario';


--
-- TOC entry 3365 (class 0 OID 0)
-- Dependencies: 218
-- Name: COLUMN log_bloqueos_dos.num_accesos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_bloqueos_dos.num_accesos IS 'Número de peticiones realizadas antes del bloqueo';


--
-- TOC entry 219 (class 1259 OID 53324)
-- Dependencies: 5
-- Name: sec_log_full_backup; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_log_full_backup
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3366 (class 0 OID 0)
-- Dependencies: 219
-- Name: sec_log_full_backup; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_log_full_backup', 1, false);


--
-- TOC entry 220 (class 1259 OID 53326)
-- Dependencies: 2528 2529 2530 5
-- Name: log_full_backup; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_full_backup (
    log_codi bigint DEFAULT nextval('sec_log_full_backup'::regclass) NOT NULL,
    sentencia character varying,
    fecha timestamp with time zone DEFAULT now(),
    estado smallint DEFAULT 0
);


--
-- TOC entry 3367 (class 0 OID 0)
-- Dependencies: 220
-- Name: TABLE log_full_backup; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE log_full_backup IS 'Log en el que se guardan todas las sentencias ejecutadas en la BDD.
Se la utilizó temporalmente para sincronizar BDD postgres 9.1 y 8.2 durante el upgrade de versión';


--
-- TOC entry 3368 (class 0 OID 0)
-- Dependencies: 220
-- Name: COLUMN log_full_backup.log_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_full_backup.log_codi IS 'Id';


--
-- TOC entry 3369 (class 0 OID 0)
-- Dependencies: 220
-- Name: COLUMN log_full_backup.sentencia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_full_backup.sentencia IS 'Sentencia ejecutada, almacenada en base 64';


--
-- TOC entry 3370 (class 0 OID 0)
-- Dependencies: 220
-- Name: COLUMN log_full_backup.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_full_backup.fecha IS 'Fecha de ejecución del script';


--
-- TOC entry 3371 (class 0 OID 0)
-- Dependencies: 220
-- Name: COLUMN log_full_backup.estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_full_backup.estado IS 'Bandera utilizada en la sincronización para saber si el query ya se ejecutó en la otra BDD';


--
-- TOC entry 222 (class 1259 OID 53344)
-- Dependencies: 5
-- Name: log_matar_procesos_servidores; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_matar_procesos_servidores (
    log_codi bigint NOT NULL,
    fecha timestamp with time zone,
    servidor character varying(20),
    cliente character varying(20),
    numero_procesos integer,
    pagina character varying(200)
);


--
-- TOC entry 223 (class 1259 OID 53347)
-- Dependencies: 5 222
-- Name: log_matar_procesos_servidores_log_codi_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE log_matar_procesos_servidores_log_codi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3372 (class 0 OID 0)
-- Dependencies: 223
-- Name: log_matar_procesos_servidores_log_codi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE log_matar_procesos_servidores_log_codi_seq OWNED BY log_matar_procesos_servidores.log_codi;


--
-- TOC entry 3373 (class 0 OID 0)
-- Dependencies: 223
-- Name: log_matar_procesos_servidores_log_codi_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('log_matar_procesos_servidores_log_codi_seq', 1, false);


--
-- TOC entry 224 (class 1259 OID 53349)
-- Dependencies: 5
-- Name: sec_log_paginas_visitadas; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_log_paginas_visitadas
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3374 (class 0 OID 0)
-- Dependencies: 224
-- Name: sec_log_paginas_visitadas; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_log_paginas_visitadas', 1, false);


--
-- TOC entry 225 (class 1259 OID 53351)
-- Dependencies: 2532 5
-- Name: log_paginas_visitadas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_paginas_visitadas (
    log_codi bigint DEFAULT nextval('sec_log_paginas_visitadas'::regclass) NOT NULL,
    fecha timestamp with time zone,
    usuario character varying(50),
    ip character varying(300),
    pagina character varying(500)
);


--
-- TOC entry 3375 (class 0 OID 0)
-- Dependencies: 225
-- Name: TABLE log_paginas_visitadas; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE log_paginas_visitadas IS 'Log de las páginas a las que han accedido los usuarios';


--
-- TOC entry 3376 (class 0 OID 0)
-- Dependencies: 225
-- Name: COLUMN log_paginas_visitadas.log_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_paginas_visitadas.log_codi IS 'Id';


--
-- TOC entry 3377 (class 0 OID 0)
-- Dependencies: 225
-- Name: COLUMN log_paginas_visitadas.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_paginas_visitadas.fecha IS 'Fecha de acceso';


--
-- TOC entry 3378 (class 0 OID 0)
-- Dependencies: 225
-- Name: COLUMN log_paginas_visitadas.usuario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_paginas_visitadas.usuario IS 'Id del usuario que llamó a la página';


--
-- TOC entry 3379 (class 0 OID 0)
-- Dependencies: 225
-- Name: COLUMN log_paginas_visitadas.ip; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_paginas_visitadas.ip IS 'Ip del usuario';


--
-- TOC entry 3380 (class 0 OID 0)
-- Dependencies: 225
-- Name: COLUMN log_paginas_visitadas.pagina; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_paginas_visitadas.pagina IS 'página a la que se accedió';


--
-- TOC entry 226 (class 1259 OID 53358)
-- Dependencies: 5
-- Name: log_sesion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_sesion (
    fecha timestamp with time zone NOT NULL,
    usuario character varying(50),
    descripcion character varying(300)
);


--
-- TOC entry 3381 (class 0 OID 0)
-- Dependencies: 226
-- Name: TABLE log_sesion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE log_sesion IS 'Log de pérdidas de sesión';


--
-- TOC entry 3382 (class 0 OID 0)
-- Dependencies: 226
-- Name: COLUMN log_sesion.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_sesion.fecha IS 'Fecha en la que se perdió la sesión';


--
-- TOC entry 3383 (class 0 OID 0)
-- Dependencies: 226
-- Name: COLUMN log_sesion.usuario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_sesion.usuario IS 'Id de la sesión o del usuario';


--
-- TOC entry 3384 (class 0 OID 0)
-- Dependencies: 226
-- Name: COLUMN log_sesion.descripcion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_sesion.descripcion IS 'Descripción del motivo por el que se cerró la sesión';


--
-- TOC entry 227 (class 1259 OID 53361)
-- Dependencies: 5
-- Name: log_tiempo_ws_firma; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_tiempo_ws_firma (
    radi_nume_radi numeric(20,0) NOT NULL,
    t1 timestamp with time zone,
    t2 timestamp with time zone,
    t3 timestamp with time zone,
    t4 timestamp with time zone,
    t5 timestamp with time zone,
    t6 timestamp with time zone
);


--
-- TOC entry 228 (class 1259 OID 53364)
-- Dependencies: 5
-- Name: log_user_permisos_id_transaccion_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE log_user_permisos_id_transaccion_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3385 (class 0 OID 0)
-- Dependencies: 228
-- Name: log_user_permisos_id_transaccion_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('log_user_permisos_id_transaccion_seq', 1, false);


--
-- TOC entry 229 (class 1259 OID 53366)
-- Dependencies: 5
-- Name: log_usr_ciudadanos_logc_codigo_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE log_usr_ciudadanos_logc_codigo_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3386 (class 0 OID 0)
-- Dependencies: 229
-- Name: log_usr_ciudadanos_logc_codigo_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('log_usr_ciudadanos_logc_codigo_seq', 1, false);


--
-- TOC entry 230 (class 1259 OID 53368)
-- Dependencies: 2533 5
-- Name: log_usr_ciudadanos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_usr_ciudadanos (
    usua_codi integer,
    usua_codi_ori integer,
    logc_observacion character varying,
    fecha_cambio timestamp with time zone,
    logc_tabla_modificada integer,
    id_transaccion integer,
    logc_codi integer DEFAULT nextval('log_usr_ciudadanos_logc_codigo_seq'::regclass) NOT NULL
);


--
-- TOC entry 3387 (class 0 OID 0)
-- Dependencies: 230
-- Name: TABLE log_usr_ciudadanos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE log_usr_ciudadanos IS 'Log de modificaciones de las tablas usuarios, ciudadano, ciudadano_tmp y solicitud_firma';


--
-- TOC entry 3388 (class 0 OID 0)
-- Dependencies: 230
-- Name: COLUMN log_usr_ciudadanos.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_ciudadanos.usua_codi IS 'Id del usuario modificado';


--
-- TOC entry 3389 (class 0 OID 0)
-- Dependencies: 230
-- Name: COLUMN log_usr_ciudadanos.usua_codi_ori; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_ciudadanos.usua_codi_ori IS 'Id del usuario que realizó el cambio';


--
-- TOC entry 3390 (class 0 OID 0)
-- Dependencies: 230
-- Name: COLUMN log_usr_ciudadanos.logc_observacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_ciudadanos.logc_observacion IS 'Cambios realizados';


--
-- TOC entry 3391 (class 0 OID 0)
-- Dependencies: 230
-- Name: COLUMN log_usr_ciudadanos.fecha_cambio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_ciudadanos.fecha_cambio IS 'Fecha en la que se realizó el cambio';


--
-- TOC entry 3392 (class 0 OID 0)
-- Dependencies: 230
-- Name: COLUMN log_usr_ciudadanos.logc_tabla_modificada; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_ciudadanos.logc_tabla_modificada IS '1 ciudadanos,2 usuarios,3 solicitud_firma,4 ciudadano_tmp';


--
-- TOC entry 3393 (class 0 OID 0)
-- Dependencies: 230
-- Name: COLUMN log_usr_ciudadanos.id_transaccion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_ciudadanos.id_transaccion IS 'Tipo de transacción (Insert o Update)';


--
-- TOC entry 3394 (class 0 OID 0)
-- Dependencies: 230
-- Name: COLUMN log_usr_ciudadanos.logc_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_ciudadanos.logc_codi IS 'Id de la tabla';


--
-- TOC entry 231 (class 1259 OID 53375)
-- Dependencies: 2534 5
-- Name: log_usr_permisos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_usr_permisos (
    id_transaccion integer DEFAULT nextval('log_user_permisos_id_transaccion_seq'::regclass) NOT NULL,
    usua_codi integer,
    usua_codi_actualiza integer,
    accion integer,
    id_permiso integer,
    fecha_actualiza timestamp with time zone
);


--
-- TOC entry 3395 (class 0 OID 0)
-- Dependencies: 231
-- Name: TABLE log_usr_permisos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE log_usr_permisos IS 'Log de cambios en los permisos de los usuarios';


--
-- TOC entry 3396 (class 0 OID 0)
-- Dependencies: 231
-- Name: COLUMN log_usr_permisos.id_transaccion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_permisos.id_transaccion IS 'Id de la tabla';


--
-- TOC entry 3397 (class 0 OID 0)
-- Dependencies: 231
-- Name: COLUMN log_usr_permisos.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_permisos.usua_codi IS 'Código del usuario modificado';


--
-- TOC entry 3398 (class 0 OID 0)
-- Dependencies: 231
-- Name: COLUMN log_usr_permisos.usua_codi_actualiza; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_permisos.usua_codi_actualiza IS 'Código del usuario que realizó el cambio';


--
-- TOC entry 3399 (class 0 OID 0)
-- Dependencies: 231
-- Name: COLUMN log_usr_permisos.accion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_permisos.accion IS 'Tipo de acción: asignar o quitar el permiso';


--
-- TOC entry 3400 (class 0 OID 0)
-- Dependencies: 231
-- Name: COLUMN log_usr_permisos.id_permiso; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_permisos.id_permiso IS 'Id del permiso';


--
-- TOC entry 3401 (class 0 OID 0)
-- Dependencies: 231
-- Name: COLUMN log_usr_permisos.fecha_actualiza; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_usr_permisos.fecha_actualiza IS 'Fecha en la que se realizó el cambio';


--
-- TOC entry 232 (class 1259 OID 53379)
-- Dependencies: 2535 5
-- Name: log_view_usuario; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE log_view_usuario (
    log_codi bigint NOT NULL,
    fecha timestamp with time zone,
    tabla character varying(50),
    accion character varying(50),
    codigo integer,
    error character varying,
    corregido smallint DEFAULT 0
);


--
-- TOC entry 3402 (class 0 OID 0)
-- Dependencies: 232
-- Name: TABLE log_view_usuario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE log_view_usuario IS 'Log en el que se guardan los errores ocurridos al actualizar la vista materializada USUARIO';


--
-- TOC entry 3403 (class 0 OID 0)
-- Dependencies: 232
-- Name: COLUMN log_view_usuario.log_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_view_usuario.log_codi IS 'Id de la tabla';


--
-- TOC entry 3404 (class 0 OID 0)
-- Dependencies: 232
-- Name: COLUMN log_view_usuario.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_view_usuario.fecha IS 'Fecha de en la que se actualizó el usuario';


--
-- TOC entry 3405 (class 0 OID 0)
-- Dependencies: 232
-- Name: COLUMN log_view_usuario.tabla; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_view_usuario.tabla IS 'Tabla que fue modificada';


--
-- TOC entry 3406 (class 0 OID 0)
-- Dependencies: 232
-- Name: COLUMN log_view_usuario.accion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_view_usuario.accion IS 'Acción realizada (Insert o Update)';


--
-- TOC entry 3407 (class 0 OID 0)
-- Dependencies: 232
-- Name: COLUMN log_view_usuario.codigo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_view_usuario.codigo IS 'Id del usuario, ciudadano, institución, área o ciudad';


--
-- TOC entry 3408 (class 0 OID 0)
-- Dependencies: 232
-- Name: COLUMN log_view_usuario.error; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_view_usuario.error IS 'Descripción del error ocurrido';


--
-- TOC entry 3409 (class 0 OID 0)
-- Dependencies: 232
-- Name: COLUMN log_view_usuario.corregido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN log_view_usuario.corregido IS 'Bandera que indica si se tomaron acciones para corregir el error en los datos de la vista';


--
-- TOC entry 233 (class 1259 OID 53386)
-- Dependencies: 232 5
-- Name: log_view_usuario_log_codi_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE log_view_usuario_log_codi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3410 (class 0 OID 0)
-- Dependencies: 233
-- Name: log_view_usuario_log_codi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE log_view_usuario_log_codi_seq OWNED BY log_view_usuario.log_codi;


--
-- TOC entry 3411 (class 0 OID 0)
-- Dependencies: 233
-- Name: log_view_usuario_log_codi_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('log_view_usuario_log_codi_seq', 1, false);


--
-- TOC entry 234 (class 1259 OID 53388)
-- Dependencies: 5
-- Name: sec_mail_notificacion; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_mail_notificacion
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3412 (class 0 OID 0)
-- Dependencies: 234
-- Name: sec_mail_notificacion; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_mail_notificacion', 1, false);


--
-- TOC entry 235 (class 1259 OID 53390)
-- Dependencies: 2537 2538 5
-- Name: mail_notificacion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE mail_notificacion (
    mail_codi integer DEFAULT nextval('sec_mail_notificacion'::regclass) NOT NULL,
    fecha_registro timestamp with time zone,
    fecha_envio timestamp with time zone,
    usua_remite integer DEFAULT 0,
    asunto character varying,
    mensaje character varying,
    estado smallint
);


--
-- TOC entry 3413 (class 0 OID 0)
-- Dependencies: 235
-- Name: TABLE mail_notificacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE mail_notificacion IS 'Envío de correos electrónicos a los usuarios de Quipux';


--
-- TOC entry 3414 (class 0 OID 0)
-- Dependencies: 235
-- Name: COLUMN mail_notificacion.mail_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN mail_notificacion.mail_codi IS 'Id de la tabla';


--
-- TOC entry 3415 (class 0 OID 0)
-- Dependencies: 235
-- Name: COLUMN mail_notificacion.fecha_registro; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN mail_notificacion.fecha_registro IS 'Fecha en la que se redactó el correo';


--
-- TOC entry 3416 (class 0 OID 0)
-- Dependencies: 235
-- Name: COLUMN mail_notificacion.fecha_envio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN mail_notificacion.fecha_envio IS 'Fecha en la que se envió el correo';


--
-- TOC entry 3417 (class 0 OID 0)
-- Dependencies: 235
-- Name: COLUMN mail_notificacion.usua_remite; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN mail_notificacion.usua_remite IS 'Id del usuarrio remitente';


--
-- TOC entry 3418 (class 0 OID 0)
-- Dependencies: 235
-- Name: COLUMN mail_notificacion.asunto; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN mail_notificacion.asunto IS 'Asunto del mensaje';


--
-- TOC entry 3419 (class 0 OID 0)
-- Dependencies: 235
-- Name: COLUMN mail_notificacion.mensaje; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN mail_notificacion.mensaje IS 'Texto del correo en formato HTML';


--
-- TOC entry 3420 (class 0 OID 0)
-- Dependencies: 235
-- Name: COLUMN mail_notificacion.estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN mail_notificacion.estado IS 'Estado del envío';


--
-- TOC entry 236 (class 1259 OID 53398)
-- Dependencies: 2539 2540 5
-- Name: metadatos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE metadatos (
    met_codi bigint NOT NULL,
    met_padre bigint NOT NULL,
    inst_codi integer,
    depe_codi integer,
    met_nombre character varying(250) NOT NULL,
    met_nivel smallint DEFAULT 0,
    met_estado smallint DEFAULT 0
);


--
-- TOC entry 3421 (class 0 OID 0)
-- Dependencies: 236
-- Name: TABLE metadatos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE metadatos IS 'Almacena una categoría de datos a manera de árbol por cada institución.';


--
-- TOC entry 3422 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN metadatos.met_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos.met_codi IS 'Id primario.';


--
-- TOC entry 3423 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN metadatos.met_padre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos.met_padre IS 'Id del metadato al cual pertenece el registro actual.';


--
-- TOC entry 3424 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN metadatos.inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos.inst_codi IS 'Id de la Institución al que pertenece el metadato.';


--
-- TOC entry 3425 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN metadatos.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos.depe_codi IS 'Id de la dependencia a la que pertenece el metadato.';


--
-- TOC entry 3426 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN metadatos.met_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos.met_nombre IS 'Indica el nombre del metadato';


--
-- TOC entry 3427 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN metadatos.met_nivel; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos.met_nivel IS 'Indica el nivel en el que se encuentra dentro del árbol';


--
-- TOC entry 3428 (class 0 OID 0)
-- Dependencies: 236
-- Name: COLUMN metadatos.met_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos.met_estado IS 'Indica si el metadato está activo';


--
-- TOC entry 237 (class 1259 OID 53403)
-- Dependencies: 5
-- Name: sec_metadatos_radi; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_metadatos_radi
    START WITH 1
    INCREMENT BY 1
    MINVALUE 0
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3429 (class 0 OID 0)
-- Dependencies: 237
-- Name: sec_metadatos_radi; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_metadatos_radi', 1, false);


--
-- TOC entry 238 (class 1259 OID 53405)
-- Dependencies: 2541 2542 5
-- Name: metadatos_radicado; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE metadatos_radicado (
    met_radi_codi bigint DEFAULT nextval('sec_metadatos_radi'::regclass) NOT NULL,
    radi_nume_radi numeric(20,0) NOT NULL,
    met_codi bigint NOT NULL,
    depe_codi integer,
    usua_codi integer,
    texto character varying(250),
    metadato character varying NOT NULL,
    metadato_texto character varying NOT NULL,
    metadato_codi character varying(250) NOT NULL,
    fecha timestamp with time zone,
    estado integer DEFAULT 1
);


--
-- TOC entry 3430 (class 0 OID 0)
-- Dependencies: 238
-- Name: TABLE metadatos_radicado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE metadatos_radicado IS 'Almacena el metadato utilizado por cada documento en la respectiva dependencia.';


--
-- TOC entry 3431 (class 0 OID 0)
-- Dependencies: 238
-- Name: COLUMN metadatos_radicado.met_radi_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos_radicado.met_radi_codi IS 'Id primario.';


--
-- TOC entry 3432 (class 0 OID 0)
-- Dependencies: 238
-- Name: COLUMN metadatos_radicado.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos_radicado.radi_nume_radi IS 'Id del documento asociado con el metadato.';


--
-- TOC entry 3433 (class 0 OID 0)
-- Dependencies: 238
-- Name: COLUMN metadatos_radicado.met_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos_radicado.met_codi IS 'Id del metadato.';


--
-- TOC entry 3434 (class 0 OID 0)
-- Dependencies: 238
-- Name: COLUMN metadatos_radicado.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos_radicado.depe_codi IS 'Id de la dependencia.';


--
-- TOC entry 3435 (class 0 OID 0)
-- Dependencies: 238
-- Name: COLUMN metadatos_radicado.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos_radicado.usua_codi IS 'Id del usuario que asocia el metadato al documento.';


--
-- TOC entry 3436 (class 0 OID 0)
-- Dependencies: 238
-- Name: COLUMN metadatos_radicado.texto; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos_radicado.texto IS 'Descripción personalizada que ingresa el usuario, como adicional al metadato.';


--
-- TOC entry 3437 (class 0 OID 0)
-- Dependencies: 238
-- Name: COLUMN metadatos_radicado.metadato; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos_radicado.metadato IS 'Descripción del metadato seleccionado.';


--
-- TOC entry 3438 (class 0 OID 0)
-- Dependencies: 238
-- Name: COLUMN metadatos_radicado.metadato_texto; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos_radicado.metadato_texto IS 'Descripción del metadato seleccionado y el texto ingresado por el usuario.';


--
-- TOC entry 3439 (class 0 OID 0)
-- Dependencies: 238
-- Name: COLUMN metadatos_radicado.metadato_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos_radicado.metadato_codi IS 'Id''s de los metadatos seleccionados separados por (,) como respaldo.';


--
-- TOC entry 3440 (class 0 OID 0)
-- Dependencies: 238
-- Name: COLUMN metadatos_radicado.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos_radicado.fecha IS 'Fecha en la que se asocia el metadato al documento.';


--
-- TOC entry 3441 (class 0 OID 0)
-- Dependencies: 238
-- Name: COLUMN metadatos_radicado.estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN metadatos_radicado.estado IS 'Estado para eliminado lógico de registro. 1: True o Activo, 2: False o Inactivo.';


--
-- TOC entry 239 (class 1259 OID 53413)
-- Dependencies: 2454 5
-- Name: nombres_usuarios; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW nombres_usuarios AS
    SELECT usuarios.usua_codi, (((COALESCE(usuarios.usua_nomb, ''::character varying))::text || ' '::text) || (COALESCE(usuarios.usua_apellido, ''::character varying))::text) AS usua_nombre, usuarios.usua_cedula, usuarios.depe_codi, usuarios.inst_codi, usuarios.usua_esta FROM usuarios WHERE (usuarios.usua_codi > 0) UNION ALL SELECT ciudadano.ciu_codigo AS usua_codi, (((COALESCE(ciudadano.ciu_nombre, ''::character varying))::text || ' '::text) || (COALESCE(ciudadano.ciu_apellido, ''::character varying))::text) AS usua_nombre, ciudadano.ciu_cedula AS usua_cedula, 0 AS depe_codi, 0 AS inst_codi, ciudadano.ciu_estado AS usua_esta FROM ciudadano;


--
-- TOC entry 240 (class 1259 OID 53418)
-- Dependencies: 2543 2544 2545 2546 2547 2548 2549 2550 2551 2552 2553 2554 5
-- Name: opciones_impresion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE opciones_impresion (
    opc_imp_codi integer NOT NULL,
    radi_nume_radi numeric(20,0) NOT NULL,
    opc_imp_ocultar_nume_radi smallint DEFAULT 0,
    opc_imp_mostrar_para smallint DEFAULT 0,
    opc_imp_justificar_firma smallint DEFAULT 0,
    opc_imp_titulo_natural character varying,
    opc_imp_ext_institucion character varying,
    opc_imp_destino_destinatario character varying,
    opc_imp_frase_remitente character varying,
    opc_imp_despedida character varying,
    opc_imp_firmantes character varying,
    opc_imp_ocultar_frase_rem smallint DEFAULT 0,
    opc_imp_cargo_cabecera character varying,
    opc_imp_tipo_nota smallint DEFAULT 0,
    opc_imp_justificar_fecha smallint DEFAULT 2,
    opc_imp_ocultar_asunto smallint DEFAULT 0,
    opc_imp_letra_italica smallint DEFAULT 0,
    opc_imp_ocultar_atentamente smallint DEFAULT 0,
    opc_imp_ocultar_anexo smallint DEFAULT 0,
    opc_imp_ocultar_referencia smallint DEFAULT 0,
    opc_imp_ocultar_sumillas smallint DEFAULT 0,
    opc_imp_texto_sobre character varying,
    opc_imp_ciudad_dado_en character varying(100)
);


--
-- TOC entry 3442 (class 0 OID 0)
-- Dependencies: 240
-- Name: TABLE opciones_impresion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE opciones_impresion IS 'Guarda los datos de las opciones de impresión';


--
-- TOC entry 3443 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_codi IS 'Id';


--
-- TOC entry 3444 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.radi_nume_radi IS 'Id del documento';


--
-- TOC entry 3445 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_ocultar_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_ocultar_nume_radi IS 'Oculta el número de documento';


--
-- TOC entry 3446 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_mostrar_para; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_mostrar_para IS 'Pone los datos del destinatario al pie de página';


--
-- TOC entry 3447 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_justificar_firma; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_justificar_firma IS 'Pone los datos del firmante centrados o a la izquierda';


--
-- TOC entry 3448 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_titulo_natural; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_titulo_natural IS 'Modifica el título del destinatario (solo para imprimir)';


--
-- TOC entry 3449 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_ext_institucion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_ext_institucion IS 'Añade un texto al final del nombre de la institución';


--
-- TOC entry 3450 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_destino_destinatario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_destino_destinatario IS 'Ubicación geográfica del destinatario, va como parte del saludo (Ejm: Presente, En su despacho, Ciudad, etc.)';


--
-- TOC entry 3451 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_frase_remitente; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_frase_remitente IS 'Frase del remitente (Ejm: DIOS, PATRIA Y LIBERTAD)';


--
-- TOC entry 3452 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_despedida; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_despedida IS 'Frase de despedida (Ejm: "Atentamente,")';


--
-- TOC entry 3453 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_firmantes; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_firmantes IS 'Añade un texto al final del nombre del destinatario';


--
-- TOC entry 3454 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_ocultar_frase_rem; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_ocultar_frase_rem IS 'Oculta la frase del remitente';


--
-- TOC entry 3455 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_cargo_cabecera; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_cargo_cabecera IS 'Modifica el cargo del destinatario (solo para imprimir)';


--
-- TOC entry 3456 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_tipo_nota; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_tipo_nota IS 'Indica que tipo de nota consular es:
0 - Verbal
1 - Diplomática
2 - Reversal';


--
-- TOC entry 3457 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_justificar_fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_justificar_fecha IS 'Campo en deshuso';


--
-- TOC entry 3458 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_ocultar_asunto; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_ocultar_asunto IS 'Oculta la línea del asunto';


--
-- TOC entry 3459 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_letra_italica; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_letra_italica IS 'Imprime el documento con letra cursiva';


--
-- TOC entry 3460 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_ocultar_atentamente; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_ocultar_atentamente IS 'Oculta la frase de despedida';


--
-- TOC entry 3461 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_ocultar_anexo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_ocultar_anexo IS 'Oculta las líneas de anexos';


--
-- TOC entry 3462 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_ocultar_referencia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_ocultar_referencia IS 'Oculta las referencias';


--
-- TOC entry 3463 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_ocultar_sumillas; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_ocultar_sumillas IS 'Oculta las sumillas de los responsables de la elaboración del documento';


--
-- TOC entry 3464 (class 0 OID 0)
-- Dependencies: 240
-- Name: COLUMN opciones_impresion.opc_imp_texto_sobre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion.opc_imp_texto_sobre IS 'Campo en deshuso';


--
-- TOC entry 241 (class 1259 OID 53436)
-- Dependencies: 5 240
-- Name: opciones_impresion_opc_imp_codi_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE opciones_impresion_opc_imp_codi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3465 (class 0 OID 0)
-- Dependencies: 241
-- Name: opciones_impresion_opc_imp_codi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE opciones_impresion_opc_imp_codi_seq OWNED BY opciones_impresion.opc_imp_codi;


--
-- TOC entry 3466 (class 0 OID 0)
-- Dependencies: 241
-- Name: opciones_impresion_opc_imp_codi_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('opciones_impresion_opc_imp_codi_seq', 1, false);


--
-- TOC entry 242 (class 1259 OID 53438)
-- Dependencies: 5
-- Name: opciones_impresion_sobre; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE opciones_impresion_sobre (
    opc_imp_sob_codi integer NOT NULL,
    radi_nume_radi numeric(20,0),
    usua_codi integer,
    opc_imp_sob_direccion character varying,
    opc_imp_sob_ciudad integer,
    opc_imp_sob_telefono character varying
);


--
-- TOC entry 3467 (class 0 OID 0)
-- Dependencies: 242
-- Name: TABLE opciones_impresion_sobre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE opciones_impresion_sobre IS 'Datos para la impresión de sobres';


--
-- TOC entry 3468 (class 0 OID 0)
-- Dependencies: 242
-- Name: COLUMN opciones_impresion_sobre.opc_imp_sob_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion_sobre.opc_imp_sob_codi IS 'Id';


--
-- TOC entry 3469 (class 0 OID 0)
-- Dependencies: 242
-- Name: COLUMN opciones_impresion_sobre.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion_sobre.radi_nume_radi IS 'Id del documento';


--
-- TOC entry 3470 (class 0 OID 0)
-- Dependencies: 242
-- Name: COLUMN opciones_impresion_sobre.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion_sobre.usua_codi IS 'Id del usuario responsable';


--
-- TOC entry 3471 (class 0 OID 0)
-- Dependencies: 242
-- Name: COLUMN opciones_impresion_sobre.opc_imp_sob_direccion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion_sobre.opc_imp_sob_direccion IS 'Dirección del destinatario';


--
-- TOC entry 3472 (class 0 OID 0)
-- Dependencies: 242
-- Name: COLUMN opciones_impresion_sobre.opc_imp_sob_ciudad; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion_sobre.opc_imp_sob_ciudad IS 'Ciudad del destinatario';


--
-- TOC entry 3473 (class 0 OID 0)
-- Dependencies: 242
-- Name: COLUMN opciones_impresion_sobre.opc_imp_sob_telefono; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN opciones_impresion_sobre.opc_imp_sob_telefono IS 'Teléfono del destinatario';


--
-- TOC entry 243 (class 1259 OID 53444)
-- Dependencies: 5 242
-- Name: opciones_impresion_sobre_opc_imp_sob_codi_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE opciones_impresion_sobre_opc_imp_sob_codi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3474 (class 0 OID 0)
-- Dependencies: 243
-- Name: opciones_impresion_sobre_opc_imp_sob_codi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE opciones_impresion_sobre_opc_imp_sob_codi_seq OWNED BY opciones_impresion_sobre.opc_imp_sob_codi;


--
-- TOC entry 3475 (class 0 OID 0)
-- Dependencies: 243
-- Name: opciones_impresion_sobre_opc_imp_sob_codi_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('opciones_impresion_sobre_opc_imp_sob_codi_seq', 1, false);


--
-- TOC entry 244 (class 1259 OID 53446)
-- Dependencies: 5
-- Name: permisos_id_permiso_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE permisos_id_permiso_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3476 (class 0 OID 0)
-- Dependencies: 244
-- Name: permisos_id_permiso_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('permisos_id_permiso_seq', 1, false);


--
-- TOC entry 245 (class 1259 OID 53448)
-- Dependencies: 2557 2558 5
-- Name: permiso; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE permiso (
    id_permiso integer DEFAULT nextval('permisos_id_permiso_seq'::regclass) NOT NULL,
    descripcion character varying(100),
    estado smallint,
    orden smallint,
    nombre character varying(100),
    descripcion_larga character varying,
    perfil smallint DEFAULT 0
);


--
-- TOC entry 3477 (class 0 OID 0)
-- Dependencies: 245
-- Name: TABLE permiso; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE permiso IS 'Catálogo de los permisos de los usuarios en el sistema';


--
-- TOC entry 3478 (class 0 OID 0)
-- Dependencies: 245
-- Name: COLUMN permiso.id_permiso; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN permiso.id_permiso IS 'Id';


--
-- TOC entry 3479 (class 0 OID 0)
-- Dependencies: 245
-- Name: COLUMN permiso.descripcion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN permiso.descripcion IS 'Descripción corta del permiso';


--
-- TOC entry 3480 (class 0 OID 0)
-- Dependencies: 245
-- Name: COLUMN permiso.estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN permiso.estado IS 'Estado:
0 = Inactivo
1 = Activo';


--
-- TOC entry 3481 (class 0 OID 0)
-- Dependencies: 245
-- Name: COLUMN permiso.orden; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN permiso.orden IS 'Orden en el que deben aparecer los permisos en la pantalla de administración';


--
-- TOC entry 3482 (class 0 OID 0)
-- Dependencies: 245
-- Name: COLUMN permiso.nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN permiso.nombre IS 'Nombre del permiso con el que se lo llama en el sistema (se carga en variables de sesión 1 ó 0)';


--
-- TOC entry 3483 (class 0 OID 0)
-- Dependencies: 245
-- Name: COLUMN permiso.descripcion_larga; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN permiso.descripcion_larga IS 'descripción larga del permiso y de la funcionalidad a la que está asociada en el sistema';


--
-- TOC entry 3484 (class 0 OID 0)
-- Dependencies: 245
-- Name: COLUMN permiso.perfil; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN permiso.perfil IS 'Modo en el que se agrupan los permisos en el sistema
0 = General
1 = Asisntentes o Secretarias
2 = Jefes
3 = Bandeja de entrada
4 = Administrador';


--
-- TOC entry 246 (class 1259 OID 53456)
-- Dependencies: 5
-- Name: permiso_usuario; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE permiso_usuario (
    id_permiso integer NOT NULL,
    usua_codi integer NOT NULL
);


--
-- TOC entry 3485 (class 0 OID 0)
-- Dependencies: 246
-- Name: TABLE permiso_usuario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE permiso_usuario IS 'Relación entre la tabla de permisos y de usuarios';


--
-- TOC entry 3486 (class 0 OID 0)
-- Dependencies: 246
-- Name: COLUMN permiso_usuario.id_permiso; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN permiso_usuario.id_permiso IS 'Id del permiso';


--
-- TOC entry 3487 (class 0 OID 0)
-- Dependencies: 246
-- Name: COLUMN permiso_usuario.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN permiso_usuario.usua_codi IS 'Id del usuario';


--
-- TOC entry 247 (class 1259 OID 53459)
-- Dependencies: 5
-- Name: permiso_usuario_dep; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE permiso_usuario_dep (
    id_permiso integer NOT NULL,
    usua_codi integer NOT NULL,
    depe_codi integer NOT NULL
);


--
-- TOC entry 3488 (class 0 OID 0)
-- Dependencies: 247
-- Name: TABLE permiso_usuario_dep; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE permiso_usuario_dep IS 'Lista de usuarios que tienen el permiso de aprobar solicitudes de respaldo de documentos';


--
-- TOC entry 3489 (class 0 OID 0)
-- Dependencies: 247
-- Name: COLUMN permiso_usuario_dep.id_permiso; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN permiso_usuario_dep.id_permiso IS 'Id del permiso';


--
-- TOC entry 3490 (class 0 OID 0)
-- Dependencies: 247
-- Name: COLUMN permiso_usuario_dep.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN permiso_usuario_dep.usua_codi IS 'Id del usuario que tiene el permiso de aprobar las solicitudes de respaldo para los usuarios del área';


--
-- TOC entry 3491 (class 0 OID 0)
-- Dependencies: 247
-- Name: COLUMN permiso_usuario_dep.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN permiso_usuario_dep.depe_codi IS 'Id del área';


--
-- TOC entry 248 (class 1259 OID 53468)
-- Dependencies: 5
-- Name: radi_texto; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE radi_texto (
    text_codi bigint NOT NULL,
    radi_nume_radi numeric(20,0),
    text_fecha timestamp with time zone,
    text_texto character varying
);


--
-- TOC entry 3492 (class 0 OID 0)
-- Dependencies: 248
-- Name: TABLE radi_texto; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE radi_texto IS 'Texto del documento; se mantienen las diferentes versiones del mismo';


--
-- TOC entry 3493 (class 0 OID 0)
-- Dependencies: 248
-- Name: COLUMN radi_texto.text_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radi_texto.text_codi IS 'Id';


--
-- TOC entry 3494 (class 0 OID 0)
-- Dependencies: 248
-- Name: COLUMN radi_texto.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radi_texto.radi_nume_radi IS 'Id del documento';


--
-- TOC entry 3495 (class 0 OID 0)
-- Dependencies: 248
-- Name: COLUMN radi_texto.text_fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radi_texto.text_fecha IS 'fecha de creación';


--
-- TOC entry 3496 (class 0 OID 0)
-- Dependencies: 248
-- Name: COLUMN radi_texto.text_texto; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radi_texto.text_texto IS 'Texto del documento';


--
-- TOC entry 249 (class 1259 OID 53474)
-- Dependencies: 5
-- Name: radicado_sec_temp; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE radicado_sec_temp (
    depe_codi integer NOT NULL,
    secuencia integer
);


--
-- TOC entry 3497 (class 0 OID 0)
-- Dependencies: 249
-- Name: TABLE radicado_sec_temp; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE radicado_sec_temp IS 'Se guardan las secuencias para los numeros temporales de los radicados';


--
-- TOC entry 3498 (class 0 OID 0)
-- Dependencies: 249
-- Name: COLUMN radicado_sec_temp.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado_sec_temp.depe_codi IS 'Área a la que pertenece la secuencia';


--
-- TOC entry 3499 (class 0 OID 0)
-- Dependencies: 249
-- Name: COLUMN radicado_sec_temp.secuencia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN radicado_sec_temp.secuencia IS 'Valor actual de la secuencia';


--
-- TOC entry 250 (class 1259 OID 53477)
-- Dependencies: 5
-- Name: regimen_regimen_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE regimen_regimen_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3500 (class 0 OID 0)
-- Dependencies: 250
-- Name: regimen_regimen_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('regimen_regimen_id_seq', 1, false);


--
-- TOC entry 251 (class 1259 OID 53486)
-- Dependencies: 2559 2560 5
-- Name: respaldo_estado; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE respaldo_estado (
    est_codi integer DEFAULT 0 NOT NULL,
    est_nombre character varying(40),
    est_nombre_estado character varying(40),
    est_desc character varying(300),
    est_tipo integer,
    est_estado integer DEFAULT 1
);


--
-- TOC entry 3501 (class 0 OID 0)
-- Dependencies: 251
-- Name: TABLE respaldo_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE respaldo_estado IS 'Tabla de estados utilizada para solicitudes y ejecución de respaldos.';


--
-- TOC entry 3502 (class 0 OID 0)
-- Dependencies: 251
-- Name: COLUMN respaldo_estado.est_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_estado.est_codi IS 'Id primario.';


--
-- TOC entry 3503 (class 0 OID 0)
-- Dependencies: 251
-- Name: COLUMN respaldo_estado.est_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_estado.est_nombre IS 'Nombre del estado para uso interno de programación.';


--
-- TOC entry 3504 (class 0 OID 0)
-- Dependencies: 251
-- Name: COLUMN respaldo_estado.est_nombre_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_estado.est_nombre_estado IS 'Nombre de estado para mostrar al usuario.';


--
-- TOC entry 3505 (class 0 OID 0)
-- Dependencies: 251
-- Name: COLUMN respaldo_estado.est_desc; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_estado.est_desc IS 'Descripción del significado de estado.';


--
-- TOC entry 3506 (class 0 OID 0)
-- Dependencies: 251
-- Name: COLUMN respaldo_estado.est_tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_estado.est_tipo IS 'Tipo de estado. 1: Estado de la solicitud, 2: Estado de la ejecución del respaldo.';


--
-- TOC entry 3507 (class 0 OID 0)
-- Dependencies: 251
-- Name: COLUMN respaldo_estado.est_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_estado.est_estado IS 'Estado del registro para realizar eliminado lógico. 1: Es True o Activo, 0: False o Inactivo.';


--
-- TOC entry 252 (class 1259 OID 53491)
-- Dependencies: 5
-- Name: sec_resp_hist_eventos; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_resp_hist_eventos
    START WITH 1
    INCREMENT BY 1
    MINVALUE 0
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3508 (class 0 OID 0)
-- Dependencies: 252
-- Name: sec_resp_hist_eventos; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_resp_hist_eventos', 1, false);


--
-- TOC entry 253 (class 1259 OID 53493)
-- Dependencies: 2561 2562 2563 5
-- Name: respaldo_hist_eventos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE respaldo_hist_eventos (
    resp_hist_eventos bigint DEFAULT nextval('sec_resp_hist_eventos'::regclass) NOT NULL,
    resp_soli_codi bigint,
    usua_codi bigint,
    fecha timestamp with time zone,
    accion integer,
    comentario character varying,
    estado_solicitud integer DEFAULT 0,
    estado_respaldo integer DEFAULT 0
);


--
-- TOC entry 3509 (class 0 OID 0)
-- Dependencies: 253
-- Name: TABLE respaldo_hist_eventos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE respaldo_hist_eventos IS 'Tabla que contiene el historial de todas las acciones realizadas sobre la tabla respaldo.';


--
-- TOC entry 3510 (class 0 OID 0)
-- Dependencies: 253
-- Name: COLUMN respaldo_hist_eventos.resp_hist_eventos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_hist_eventos.resp_hist_eventos IS 'Id primario.';


--
-- TOC entry 3511 (class 0 OID 0)
-- Dependencies: 253
-- Name: COLUMN respaldo_hist_eventos.resp_soli_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_hist_eventos.resp_soli_codi IS 'Id de solicitud de respaldo.';


--
-- TOC entry 3512 (class 0 OID 0)
-- Dependencies: 253
-- Name: COLUMN respaldo_hist_eventos.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_hist_eventos.usua_codi IS 'Id del usuario que realiza la acción.';


--
-- TOC entry 3513 (class 0 OID 0)
-- Dependencies: 253
-- Name: COLUMN respaldo_hist_eventos.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_hist_eventos.fecha IS 'Fecha en que se realiza la acción.';


--
-- TOC entry 3514 (class 0 OID 0)
-- Dependencies: 253
-- Name: COLUMN respaldo_hist_eventos.accion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_hist_eventos.accion IS 'Id de la acción, según el catálogo con las transacciones.';


--
-- TOC entry 3515 (class 0 OID 0)
-- Dependencies: 253
-- Name: COLUMN respaldo_hist_eventos.comentario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_hist_eventos.comentario IS 'Descripción de la acción o comentario ingresado por el usuario.';


--
-- TOC entry 3516 (class 0 OID 0)
-- Dependencies: 253
-- Name: COLUMN respaldo_hist_eventos.estado_solicitud; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_hist_eventos.estado_solicitud IS 'Id del estado de la solicitud de respaldo.';


--
-- TOC entry 3517 (class 0 OID 0)
-- Dependencies: 253
-- Name: COLUMN respaldo_hist_eventos.estado_respaldo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_hist_eventos.estado_respaldo IS 'Id del estado de la ejecución de respaldo.';


--
-- TOC entry 254 (class 1259 OID 53502)
-- Dependencies: 5
-- Name: sec_respaldo_solicitud; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_respaldo_solicitud
    START WITH 1
    INCREMENT BY 1
    MINVALUE 0
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3518 (class 0 OID 0)
-- Dependencies: 254
-- Name: sec_respaldo_solicitud; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_respaldo_solicitud', 1, false);


--
-- TOC entry 255 (class 1259 OID 53504)
-- Dependencies: 2564 2565 2566 5
-- Name: respaldo_solicitud; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE respaldo_solicitud (
    resp_soli_codi bigint DEFAULT nextval('sec_respaldo_solicitud'::regclass) NOT NULL,
    resp_codi bigint,
    usua_codi_solicita bigint,
    usua_codi_autoriza bigint,
    usua_codi_accion bigint,
    fecha_solicita timestamp with time zone,
    fecha_inicio_doc timestamp with time zone,
    fecha_fin_doc timestamp with time zone,
    fecha_inicio_ejec timestamp with time zone,
    fecha_fin_ejec timestamp with time zone,
    estado_solicitud integer DEFAULT 0,
    estado_respaldo integer DEFAULT 0,
    comentario character varying,
    num_documentos integer,
    fecha_ejecutar timestamp with time zone,
    radi_nume_radi numeric(20,0)
);


--
-- TOC entry 3519 (class 0 OID 0)
-- Dependencies: 255
-- Name: TABLE respaldo_solicitud; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE respaldo_solicitud IS 'Tabla que contiene las solicitudes de respaldo de documentos.';


--
-- TOC entry 3520 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.resp_soli_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.resp_soli_codi IS 'Id primario.';


--
-- TOC entry 3521 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.resp_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.resp_codi IS 'Id de la tabla respaldo usuario.';


--
-- TOC entry 3522 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.usua_codi_solicita; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.usua_codi_solicita IS 'Id del usuario que solicita el respaldo.';


--
-- TOC entry 3523 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.usua_codi_autoriza; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.usua_codi_autoriza IS 'Id del usuario que autoriza la solicitud de respaldo.';


--
-- TOC entry 3524 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.usua_codi_accion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.usua_codi_accion IS 'Id del usuario que actualiza la solicitud.';


--
-- TOC entry 3525 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.fecha_solicita; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.fecha_solicita IS 'Fecha de la solicitud de respaldo.';


--
-- TOC entry 3526 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.fecha_inicio_doc; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.fecha_inicio_doc IS 'Fecha de inicial de documentos a respaldar.';


--
-- TOC entry 3527 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.fecha_fin_doc; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.fecha_fin_doc IS 'Fecha de final de documentos a respaldar.';


--
-- TOC entry 3528 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.fecha_inicio_ejec; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.fecha_inicio_ejec IS 'Fecha  en que se inicia el proceso de ejecución de respaldos.';


--
-- TOC entry 3529 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.fecha_fin_ejec; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.fecha_fin_ejec IS 'Fecha  en que se termina el proceso de ejecución de respaldos.';


--
-- TOC entry 3530 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.estado_solicitud; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.estado_solicitud IS 'Id del estado de la solicitud de respaldo.';


--
-- TOC entry 3531 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.estado_respaldo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.estado_respaldo IS 'Id del estado de la solicitud de la ejecución del respaldo.';


--
-- TOC entry 3532 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.comentario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.comentario IS 'Campo para ingresar el comentario del usuario.';


--
-- TOC entry 3533 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.num_documentos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.num_documentos IS 'Cantidad de documentos respaldados.';


--
-- TOC entry 3534 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.fecha_ejecutar; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.fecha_ejecutar IS 'Fecha en la que se ejecutará el proceso de obtención de respaldos.';


--
-- TOC entry 3535 (class 0 OID 0)
-- Dependencies: 255
-- Name: COLUMN respaldo_solicitud.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_solicitud.radi_nume_radi IS 'Id del documento asociado con el cual se solicita los respaldos.';


--
-- TOC entry 256 (class 1259 OID 53513)
-- Dependencies: 5
-- Name: sec_respaldo_usuario; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_respaldo_usuario
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3536 (class 0 OID 0)
-- Dependencies: 256
-- Name: sec_respaldo_usuario; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_respaldo_usuario', 1, false);


--
-- TOC entry 257 (class 1259 OID 53515)
-- Dependencies: 2567 2568 5
-- Name: respaldo_usuario; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE respaldo_usuario (
    resp_codi bigint DEFAULT nextval('sec_respaldo_usuario'::regclass) NOT NULL,
    usua_codi bigint,
    usua_codi_solicita bigint,
    fecha_solicita timestamp with time zone,
    fecha_inicio timestamp with time zone,
    fecha_fin timestamp with time zone,
    num_documentos integer DEFAULT 0,
    fecha_eliminado timestamp with time zone
);


--
-- TOC entry 3537 (class 0 OID 0)
-- Dependencies: 257
-- Name: TABLE respaldo_usuario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE respaldo_usuario IS 'Backups de los documentos generados por los usuarios';


--
-- TOC entry 3538 (class 0 OID 0)
-- Dependencies: 257
-- Name: COLUMN respaldo_usuario.resp_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario.resp_codi IS 'Id del respaldo';


--
-- TOC entry 3539 (class 0 OID 0)
-- Dependencies: 257
-- Name: COLUMN respaldo_usuario.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario.usua_codi IS 'Id del usuario';


--
-- TOC entry 3540 (class 0 OID 0)
-- Dependencies: 257
-- Name: COLUMN respaldo_usuario.usua_codi_solicita; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario.usua_codi_solicita IS 'Id del usuario que solicita el respaldo';


--
-- TOC entry 3541 (class 0 OID 0)
-- Dependencies: 257
-- Name: COLUMN respaldo_usuario.fecha_solicita; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario.fecha_solicita IS 'Fecha en la que se solicitó el respaldo';


--
-- TOC entry 3542 (class 0 OID 0)
-- Dependencies: 257
-- Name: COLUMN respaldo_usuario.fecha_inicio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario.fecha_inicio IS 'Fecha en que inició el proceso de backup';


--
-- TOC entry 3543 (class 0 OID 0)
-- Dependencies: 257
-- Name: COLUMN respaldo_usuario.fecha_fin; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario.fecha_fin IS 'fecha en la que finalizó el proceso de backup';


--
-- TOC entry 3544 (class 0 OID 0)
-- Dependencies: 257
-- Name: COLUMN respaldo_usuario.num_documentos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario.num_documentos IS 'número de documentos a respaldar';


--
-- TOC entry 3545 (class 0 OID 0)
-- Dependencies: 257
-- Name: COLUMN respaldo_usuario.fecha_eliminado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario.fecha_eliminado IS 'Fecha en la que se eliminó el respaldo';


--
-- TOC entry 258 (class 1259 OID 53520)
-- Dependencies: 5
-- Name: sec_respaldo_usuario_radicado; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_respaldo_usuario_radicado
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3546 (class 0 OID 0)
-- Dependencies: 258
-- Name: sec_respaldo_usuario_radicado; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_respaldo_usuario_radicado', 1, false);


--
-- TOC entry 259 (class 1259 OID 53522)
-- Dependencies: 2569 2570 5
-- Name: respaldo_usuario_radicado; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE respaldo_usuario_radicado (
    resp_radi_codi bigint DEFAULT nextval('sec_respaldo_usuario_radicado'::regclass) NOT NULL,
    resp_codi bigint,
    radi_nume_radi numeric(20,0),
    fila character varying,
    error character varying,
    num_error integer DEFAULT 0,
    tipo integer
);


--
-- TOC entry 3547 (class 0 OID 0)
-- Dependencies: 259
-- Name: TABLE respaldo_usuario_radicado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE respaldo_usuario_radicado IS 'Tabla temporal en la que se guardan los documentos a respaldar';


--
-- TOC entry 3548 (class 0 OID 0)
-- Dependencies: 259
-- Name: COLUMN respaldo_usuario_radicado.resp_radi_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario_radicado.resp_radi_codi IS 'Id';


--
-- TOC entry 3549 (class 0 OID 0)
-- Dependencies: 259
-- Name: COLUMN respaldo_usuario_radicado.resp_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario_radicado.resp_codi IS 'Id del respaldo';


--
-- TOC entry 3550 (class 0 OID 0)
-- Dependencies: 259
-- Name: COLUMN respaldo_usuario_radicado.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario_radicado.radi_nume_radi IS 'Id del documento';


--
-- TOC entry 3551 (class 0 OID 0)
-- Dependencies: 259
-- Name: COLUMN respaldo_usuario_radicado.fila; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario_radicado.fila IS 'Se guarda el codigo html con los datos del documento para crear los archivos índices';


--
-- TOC entry 3552 (class 0 OID 0)
-- Dependencies: 259
-- Name: COLUMN respaldo_usuario_radicado.error; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario_radicado.error IS 'Si hubo algun error al sacar el respaldo se guarda un detalle';


--
-- TOC entry 3553 (class 0 OID 0)
-- Dependencies: 259
-- Name: COLUMN respaldo_usuario_radicado.num_error; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario_radicado.num_error IS 'Numero de veces que se genero un error en el documento, el sistema intenta respaldar por 3 veces cada documento';


--
-- TOC entry 3554 (class 0 OID 0)
-- Dependencies: 259
-- Name: COLUMN respaldo_usuario_radicado.tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN respaldo_usuario_radicado.tipo IS 'Segun la bandeja donde debe estar
1 - Recibidos
2 – Enviados';


--
-- TOC entry 260 (class 1259 OID 53530)
-- Dependencies: 5
-- Name: sec_archivo; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_archivo
    START WITH 0
    INCREMENT BY 1
    MINVALUE 0
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3555 (class 0 OID 0)
-- Dependencies: 260
-- Name: SEQUENCE sec_archivo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON SEQUENCE sec_archivo IS 'Archivo físico de documentos';


--
-- TOC entry 3556 (class 0 OID 0)
-- Dependencies: 260
-- Name: sec_archivo; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_archivo', 0, false);


--
-- TOC entry 192 (class 1259 OID 53169)
-- Dependencies: 5
-- Name: sec_corregir_usuarios; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_corregir_usuarios
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3557 (class 0 OID 0)
-- Dependencies: 192
-- Name: sec_corregir_usuarios; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_corregir_usuarios', 1, false);


--
-- TOC entry 261 (class 1259 OID 53532)
-- Dependencies: 5
-- Name: sec_dependencia; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_dependencia
    START WITH 0
    INCREMENT BY 1
    MINVALUE 0
    MAXVALUE 999999
    CACHE 1;


--
-- TOC entry 3558 (class 0 OID 0)
-- Dependencies: 261
-- Name: sec_dependencia; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_dependencia', 2, true);


--
-- TOC entry 262 (class 1259 OID 53534)
-- Dependencies: 5
-- Name: sec_institucion; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_institucion
    START WITH 0
    INCREMENT BY 1
    MINVALUE 0
    MAXVALUE 99999
    CACHE 1;


--
-- TOC entry 3559 (class 0 OID 0)
-- Dependencies: 262
-- Name: SEQUENCE sec_institucion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON SEQUENCE sec_institucion IS 'Secuencia de Instituciones';


--
-- TOC entry 3560 (class 0 OID 0)
-- Dependencies: 262
-- Name: sec_institucion; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_institucion', 2, true);


--
-- TOC entry 207 (class 1259 OID 53269)
-- Dependencies: 5
-- Name: sec_interconexion_radicado; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_interconexion_radicado
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3561 (class 0 OID 0)
-- Dependencies: 207
-- Name: sec_interconexion_radicado; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_interconexion_radicado', 1, false);


--
-- TOC entry 221 (class 1259 OID 53335)
-- Dependencies: 5
-- Name: sec_log_interconexion_radicado; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_log_interconexion_radicado
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3562 (class 0 OID 0)
-- Dependencies: 221
-- Name: sec_log_interconexion_radicado; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_log_interconexion_radicado', 1, false);


--
-- TOC entry 263 (class 1259 OID 53536)
-- Dependencies: 5
-- Name: sec_metadatos; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_metadatos
    START WITH 1
    INCREMENT BY 1
    MINVALUE 0
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3563 (class 0 OID 0)
-- Dependencies: 263
-- Name: sec_metadatos; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_metadatos', 1, false);


--
-- TOC entry 264 (class 1259 OID 53538)
-- Dependencies: 5
-- Name: sec_radi_nume_radi; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_radi_nume_radi
    START WITH 111000000
    INCREMENT BY 1
    NO MINVALUE
    MAXVALUE 999999999
    CACHE 1;


--
-- TOC entry 3564 (class 0 OID 0)
-- Dependencies: 264
-- Name: sec_radi_nume_radi; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_radi_nume_radi', 1, true);


--
-- TOC entry 265 (class 1259 OID 53540)
-- Dependencies: 5
-- Name: sec_radi_texto; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_radi_texto
    START WITH 0
    INCREMENT BY 1
    MINVALUE 0
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3565 (class 0 OID 0)
-- Dependencies: 265
-- Name: sec_radi_texto; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_radi_texto', 0, false);


--
-- TOC entry 266 (class 1259 OID 53542)
-- Dependencies: 5
-- Name: sec_tarea; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_tarea
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3566 (class 0 OID 0)
-- Dependencies: 266
-- Name: sec_tarea; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_tarea', 1, false);


--
-- TOC entry 267 (class 1259 OID 53544)
-- Dependencies: 5
-- Name: sec_tarea_hist_eventos; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_tarea_hist_eventos
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3567 (class 0 OID 0)
-- Dependencies: 267
-- Name: sec_tarea_hist_eventos; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_tarea_hist_eventos', 1, false);


--
-- TOC entry 268 (class 1259 OID 53546)
-- Dependencies: 5
-- Name: sec_tarea_radi_respuesta; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_tarea_radi_respuesta
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3568 (class 0 OID 0)
-- Dependencies: 268
-- Name: sec_tarea_radi_respuesta; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_tarea_radi_respuesta', 1, false);


--
-- TOC entry 269 (class 1259 OID 53548)
-- Dependencies: 5
-- Name: sec_trd; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sec_trd
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3569 (class 0 OID 0)
-- Dependencies: 269
-- Name: sec_trd; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sec_trd', 1, false);


--
-- TOC entry 270 (class 1259 OID 53550)
-- Dependencies: 5
-- Name: secu_crecimientobodega2011; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE secu_crecimientobodega2011
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3570 (class 0 OID 0)
-- Dependencies: 270
-- Name: secu_crecimientobodega2011; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('secu_crecimientobodega2011', 1, false);


--
-- TOC entry 271 (class 1259 OID 53552)
-- Dependencies: 5
-- Name: secu_crecimientobodega2012; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE secu_crecimientobodega2012
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3571 (class 0 OID 0)
-- Dependencies: 271
-- Name: secu_crecimientobodega2012; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('secu_crecimientobodega2012', 1, false);


--
-- TOC entry 272 (class 1259 OID 53554)
-- Dependencies: 5
-- Name: secu_tmpcrecimiento; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE secu_tmpcrecimiento
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3572 (class 0 OID 0)
-- Dependencies: 272
-- Name: secu_tmpcrecimiento; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('secu_tmpcrecimiento', 1, false);


--
-- TOC entry 273 (class 1259 OID 53562)
-- Dependencies: 5
-- Name: sgd_ciu_secue; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sgd_ciu_secue
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    MAXVALUE 99999999
    CACHE 1;


--
-- TOC entry 3573 (class 0 OID 0)
-- Dependencies: 273
-- Name: sgd_ciu_secue; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sgd_ciu_secue', 1, false);


--
-- TOC entry 274 (class 1259 OID 53564)
-- Dependencies: 5
-- Name: sgd_dir_secue; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sgd_dir_secue
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    MAXVALUE 99999999
    CACHE 1;


--
-- TOC entry 3574 (class 0 OID 0)
-- Dependencies: 274
-- Name: sgd_dir_secue; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sgd_dir_secue', 1, false);


--
-- TOC entry 275 (class 1259 OID 53566)
-- Dependencies: 5
-- Name: sgd_info_secue; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE sgd_info_secue
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    MAXVALUE 9999999999
    CACHE 1;


--
-- TOC entry 3575 (class 0 OID 0)
-- Dependencies: 275
-- Name: sgd_info_secue; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('sgd_info_secue', 1, false);


--
-- TOC entry 276 (class 1259 OID 53568)
-- Dependencies: 5
-- Name: sgd_ttr_transaccion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE sgd_ttr_transaccion (
    sgd_ttr_codigo smallint NOT NULL,
    sgd_ttr_descrip character varying(100) NOT NULL
);


--
-- TOC entry 3576 (class 0 OID 0)
-- Dependencies: 276
-- Name: TABLE sgd_ttr_transaccion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE sgd_ttr_transaccion IS 'Catálogo con las transacciones que se pueden realizar con cada documento';


--
-- TOC entry 3577 (class 0 OID 0)
-- Dependencies: 276
-- Name: COLUMN sgd_ttr_transaccion.sgd_ttr_codigo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN sgd_ttr_transaccion.sgd_ttr_codigo IS 'Id de la transacción';


--
-- TOC entry 3578 (class 0 OID 0)
-- Dependencies: 276
-- Name: COLUMN sgd_ttr_transaccion.sgd_ttr_descrip; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN sgd_ttr_transaccion.sgd_ttr_descrip IS 'Detalle de la transacción';


--
-- TOC entry 277 (class 1259 OID 53571)
-- Dependencies: 5
-- Name: solicitud_usua_codi_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE solicitud_usua_codi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3579 (class 0 OID 0)
-- Dependencies: 277
-- Name: solicitud_usua_codi_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('solicitud_usua_codi_seq', 1, false);


--
-- TOC entry 278 (class 1259 OID 53573)
-- Dependencies: 2571 5
-- Name: tarea; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE tarea (
    tarea_codi bigint DEFAULT nextval('sec_tarea'::regclass) NOT NULL,
    radi_nume_radi numeric(20,0),
    fecha_inicio timestamp with time zone,
    fecha_fin timestamp with time zone,
    fecha_maxima timestamp with time zone,
    usua_codi_ori bigint,
    usua_codi_dest bigint,
    estado smallint,
    tarea_codi_padre bigint,
    leido smallint,
    avance smallint,
    comentario_fin bigint,
    comentario_inicio bigint
);


--
-- TOC entry 3580 (class 0 OID 0)
-- Dependencies: 278
-- Name: TABLE tarea; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE tarea IS 'Registra las tareas asignadas a los usuarios';


--
-- TOC entry 3581 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.tarea_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.tarea_codi IS 'Id de la tarea';


--
-- TOC entry 3582 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.radi_nume_radi IS 'Id del docuemnto del que depende la tarea';


--
-- TOC entry 3583 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.fecha_inicio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.fecha_inicio IS 'Fecha en la que se asignó la tarea';


--
-- TOC entry 3584 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.fecha_fin; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.fecha_fin IS 'Fecha en la que se finalizó o canceló la tarea';


--
-- TOC entry 3585 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.fecha_maxima; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.fecha_maxima IS 'Fecha máxima asignada para la resolución de la tarea';


--
-- TOC entry 3586 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.usua_codi_ori; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.usua_codi_ori IS 'Id del usuario que asignó la tarea';


--
-- TOC entry 3587 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.usua_codi_dest; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.usua_codi_dest IS 'Id del usuario al que le asignaron la tarea';


--
-- TOC entry 3588 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.estado IS 'estado de la tarea';


--
-- TOC entry 3589 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.tarea_codi_padre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.tarea_codi_padre IS 'Id de la tarea padre en caso de que se haya asignado una tarea a partir de otra tarea';


--
-- TOC entry 3590 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.leido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.leido IS 'Indica si la tarea ya fue leida';


--
-- TOC entry 3591 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.avance; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.avance IS 'Registra el porcentaje de avance de la tarea';


--
-- TOC entry 3592 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.comentario_fin; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.comentario_fin IS 'Registra el id del último comentario realizado sobre la tarea';


--
-- TOC entry 3593 (class 0 OID 0)
-- Dependencies: 278
-- Name: COLUMN tarea.comentario_inicio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea.comentario_inicio IS 'Registra el id del primer comentario de la tarea';


--
-- TOC entry 279 (class 1259 OID 53577)
-- Dependencies: 2572 5
-- Name: tarea_hist_eventos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE tarea_hist_eventos (
    tarea_hist_codi bigint DEFAULT nextval('sec_tarea_hist_eventos'::regclass) NOT NULL,
    tarea_codi bigint,
    radi_nume_radi numeric(20,0),
    usua_codi_ori bigint,
    fecha timestamp with time zone,
    accion integer,
    comentario character varying,
    referencia character varying
);


--
-- TOC entry 3594 (class 0 OID 0)
-- Dependencies: 279
-- Name: TABLE tarea_hist_eventos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE tarea_hist_eventos IS 'Recorrido de las tareas';


--
-- TOC entry 3595 (class 0 OID 0)
-- Dependencies: 279
-- Name: COLUMN tarea_hist_eventos.tarea_hist_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea_hist_eventos.tarea_hist_codi IS 'Id';


--
-- TOC entry 3596 (class 0 OID 0)
-- Dependencies: 279
-- Name: COLUMN tarea_hist_eventos.tarea_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea_hist_eventos.tarea_codi IS 'Id de la tarea';


--
-- TOC entry 3597 (class 0 OID 0)
-- Dependencies: 279
-- Name: COLUMN tarea_hist_eventos.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea_hist_eventos.radi_nume_radi IS 'Id del documento';


--
-- TOC entry 3598 (class 0 OID 0)
-- Dependencies: 279
-- Name: COLUMN tarea_hist_eventos.usua_codi_ori; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea_hist_eventos.usua_codi_ori IS 'Id del usuario que realizó la acción';


--
-- TOC entry 3599 (class 0 OID 0)
-- Dependencies: 279
-- Name: COLUMN tarea_hist_eventos.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea_hist_eventos.fecha IS 'Fecha de la acción';


--
-- TOC entry 3600 (class 0 OID 0)
-- Dependencies: 279
-- Name: COLUMN tarea_hist_eventos.accion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea_hist_eventos.accion IS 'Id de la transacción realizada';


--
-- TOC entry 3601 (class 0 OID 0)
-- Dependencies: 279
-- Name: COLUMN tarea_hist_eventos.comentario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea_hist_eventos.comentario IS 'Comentario realizado por el usuario';


--
-- TOC entry 3602 (class 0 OID 0)
-- Dependencies: 279
-- Name: COLUMN tarea_hist_eventos.referencia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea_hist_eventos.referencia IS 'Campo adicional para guardar códigos o fechas o datos adicionales dependiendo de la transacción';


--
-- TOC entry 280 (class 1259 OID 53584)
-- Dependencies: 2573 5
-- Name: tarea_radi_respuesta; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE tarea_radi_respuesta (
    tarea_codi bigint,
    radi_nume_radi numeric(20,0),
    radi_nume_resp numeric(20,0),
    tarea_resp_codi bigint DEFAULT nextval('sec_tarea_hist_eventos'::regclass) NOT NULL
);


--
-- TOC entry 3603 (class 0 OID 0)
-- Dependencies: 280
-- Name: TABLE tarea_radi_respuesta; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE tarea_radi_respuesta IS 'Asociación de documentos cuando se responde a un documento a partir de una tarea';


--
-- TOC entry 3604 (class 0 OID 0)
-- Dependencies: 280
-- Name: COLUMN tarea_radi_respuesta.tarea_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea_radi_respuesta.tarea_codi IS 'Id de la tarea';


--
-- TOC entry 3605 (class 0 OID 0)
-- Dependencies: 280
-- Name: COLUMN tarea_radi_respuesta.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea_radi_respuesta.radi_nume_radi IS 'Id del documento padre';


--
-- TOC entry 3606 (class 0 OID 0)
-- Dependencies: 280
-- Name: COLUMN tarea_radi_respuesta.radi_nume_resp; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea_radi_respuesta.radi_nume_resp IS 'Id del documento respuesta';


--
-- TOC entry 3607 (class 0 OID 0)
-- Dependencies: 280
-- Name: COLUMN tarea_radi_respuesta.tarea_resp_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tarea_radi_respuesta.tarea_resp_codi IS 'Id';


--
-- TOC entry 281 (class 1259 OID 53591)
-- Dependencies: 5
-- Name: tipo_cargo_id_tipo_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE tipo_cargo_id_tipo_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3608 (class 0 OID 0)
-- Dependencies: 281
-- Name: tipo_cargo_id_tipo_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('tipo_cargo_id_tipo_seq', 1, false);


--
-- TOC entry 282 (class 1259 OID 53600)
-- Dependencies: 2574 5
-- Name: tipo_certificado; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE tipo_certificado (
    tipo_cert_codi smallint NOT NULL,
    descripcion character varying(150),
    estado smallint DEFAULT 1
);


--
-- TOC entry 3609 (class 0 OID 0)
-- Dependencies: 282
-- Name: TABLE tipo_certificado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE tipo_certificado IS 'Almacena los tipos de certificados permitidos (depende de la CA, el dispositivo, el formato, etc.)';


--
-- TOC entry 3610 (class 0 OID 0)
-- Dependencies: 282
-- Name: COLUMN tipo_certificado.tipo_cert_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tipo_certificado.tipo_cert_codi IS 'Id';


--
-- TOC entry 3611 (class 0 OID 0)
-- Dependencies: 282
-- Name: COLUMN tipo_certificado.descripcion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tipo_certificado.descripcion IS 'Descripción del tipo de certificado';


--
-- TOC entry 3612 (class 0 OID 0)
-- Dependencies: 282
-- Name: COLUMN tipo_certificado.estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tipo_certificado.estado IS 'Estado:
1 - Activo
0 - Inactivo';


--
-- TOC entry 283 (class 1259 OID 53604)
-- Dependencies: 2575 2576 2577 5
-- Name: tiporad; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE tiporad (
    trad_codigo numeric(1,0) NOT NULL,
    trad_descr character varying(100),
    trad_tipo character varying(1),
    trad_inst_codi integer DEFAULT 0,
    trad_estado smallint DEFAULT 1,
    trad_abreviatura character varying(5),
    trad_opc_impresion character varying,
    trad_texto_inicio character varying,
    trad_formato character varying,
    trad_formato_tipo smallint DEFAULT 1
);


--
-- TOC entry 3613 (class 0 OID 0)
-- Dependencies: 283
-- Name: TABLE tiporad; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE tiporad IS 'Tipo de documento';


--
-- TOC entry 3614 (class 0 OID 0)
-- Dependencies: 283
-- Name: COLUMN tiporad.trad_codigo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tiporad.trad_codigo IS 'Id del tipo de documento';


--
-- TOC entry 3615 (class 0 OID 0)
-- Dependencies: 283
-- Name: COLUMN tiporad.trad_descr; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tiporad.trad_descr IS 'Nombre del documento (oficio, memo, etc.)';


--
-- TOC entry 3616 (class 0 OID 0)
-- Dependencies: 283
-- Name: COLUMN tiporad.trad_tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tiporad.trad_tipo IS 'Si es documento de entrada o de salida';


--
-- TOC entry 3617 (class 0 OID 0)
-- Dependencies: 283
-- Name: COLUMN tiporad.trad_inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tiporad.trad_inst_codi IS 'Institución a la que pertenece el documento; "0" para todas las instituciones';


--
-- TOC entry 3618 (class 0 OID 0)
-- Dependencies: 283
-- Name: COLUMN tiporad.trad_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tiporad.trad_estado IS 'Estado, activo o inactivo';


--
-- TOC entry 3619 (class 0 OID 0)
-- Dependencies: 283
-- Name: COLUMN tiporad.trad_abreviatura; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tiporad.trad_abreviatura IS 'Abreviatura por defecto para el tipo de documento';


--
-- TOC entry 3620 (class 0 OID 0)
-- Dependencies: 283
-- Name: COLUMN tiporad.trad_opc_impresion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tiporad.trad_opc_impresion IS 'Opciones de impresión que se habilitan para cada documento';


--
-- TOC entry 3621 (class 0 OID 0)
-- Dependencies: 283
-- Name: COLUMN tiporad.trad_texto_inicio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tiporad.trad_texto_inicio IS 'Texto que se carga por defecto al crear un nuevo documento de este tipo';


--
-- TOC entry 3622 (class 0 OID 0)
-- Dependencies: 283
-- Name: COLUMN tiporad.trad_formato; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tiporad.trad_formato IS 'formato del documento, se lo escribe en base a patrones que luego van a ser reemplazados con los datos del documento';


--
-- TOC entry 3623 (class 0 OID 0)
-- Dependencies: 283
-- Name: COLUMN tiporad.trad_formato_tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN tiporad.trad_formato_tipo IS 'Carga ciertas opciones especiales para algunos documentos';


--
-- TOC entry 284 (class 1259 OID 53613)
-- Dependencies: 5
-- Name: titulo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE titulo (
    tit_codi integer NOT NULL,
    tit_nombre character varying(100) NOT NULL,
    tit_abreviatura character varying(50)
);


--
-- TOC entry 3624 (class 0 OID 0)
-- Dependencies: 284
-- Name: TABLE titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE titulo IS 'Lista de títulos académicos admitidos en el sistema';


--
-- TOC entry 3625 (class 0 OID 0)
-- Dependencies: 284
-- Name: COLUMN titulo.tit_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN titulo.tit_codi IS 'Id';


--
-- TOC entry 3626 (class 0 OID 0)
-- Dependencies: 284
-- Name: COLUMN titulo.tit_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN titulo.tit_nombre IS 'Tratamiento o título académico';


--
-- TOC entry 3627 (class 0 OID 0)
-- Dependencies: 284
-- Name: COLUMN titulo.tit_abreviatura; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN titulo.tit_abreviatura IS 'Abreviatura del título';


--
-- TOC entry 285 (class 1259 OID 53616)
-- Dependencies: 5 284
-- Name: titulo_tit_codi_seq1; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE titulo_tit_codi_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3628 (class 0 OID 0)
-- Dependencies: 285
-- Name: titulo_tit_codi_seq1; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE titulo_tit_codi_seq1 OWNED BY titulo.tit_codi;


--
-- TOC entry 3629 (class 0 OID 0)
-- Dependencies: 285
-- Name: titulo_tit_codi_seq1; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('titulo_tit_codi_seq1', 302, true);


--
-- TOC entry 286 (class 1259 OID 53660)
-- Dependencies: 2579 2580 2581 2582 2583 5
-- Name: trd; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE trd (
    trd_codi bigint NOT NULL,
    trd_padre bigint NOT NULL,
    trd_nombre character varying(100),
    depe_codi integer,
    trd_estado smallint DEFAULT 0,
    trd_arch_gestion integer DEFAULT 5,
    trd_arch_central integer DEFAULT 15,
    trd_fecha_desde date,
    trd_fecha_hasta date,
    trd_ocupado smallint DEFAULT 0,
    trd_nivel smallint DEFAULT 0
);


--
-- TOC entry 3630 (class 0 OID 0)
-- Dependencies: 286
-- Name: TABLE trd; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE trd IS 'Tabla recursiva, estructura de las carpetas virtuales';


--
-- TOC entry 3631 (class 0 OID 0)
-- Dependencies: 286
-- Name: COLUMN trd.trd_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd.trd_codi IS 'Id de la carpeta virtual';


--
-- TOC entry 3632 (class 0 OID 0)
-- Dependencies: 286
-- Name: COLUMN trd.trd_padre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd.trd_padre IS 'Id de la carpeta padre';


--
-- TOC entry 3633 (class 0 OID 0)
-- Dependencies: 286
-- Name: COLUMN trd.trd_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd.trd_nombre IS 'Nombre de la carpeta';


--
-- TOC entry 3634 (class 0 OID 0)
-- Dependencies: 286
-- Name: COLUMN trd.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd.depe_codi IS 'Área a la que pertenece la carpeta';


--
-- TOC entry 3635 (class 0 OID 0)
-- Dependencies: 286
-- Name: COLUMN trd.trd_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd.trd_estado IS 'Indica si la carpeta está activa';


--
-- TOC entry 3636 (class 0 OID 0)
-- Dependencies: 286
-- Name: COLUMN trd.trd_arch_gestion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd.trd_arch_gestion IS 'Tiempo que debe mantenerse el documento en el archivo de gestion';


--
-- TOC entry 3637 (class 0 OID 0)
-- Dependencies: 286
-- Name: COLUMN trd.trd_arch_central; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd.trd_arch_central IS 'Tiempo que se deberá mantener el documento en el archivo central';


--
-- TOC entry 3638 (class 0 OID 0)
-- Dependencies: 286
-- Name: COLUMN trd.trd_fecha_desde; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd.trd_fecha_desde IS 'Fecha desde la que se activó la carpeta';


--
-- TOC entry 3639 (class 0 OID 0)
-- Dependencies: 286
-- Name: COLUMN trd.trd_fecha_hasta; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd.trd_fecha_hasta IS 'Fecha en la que se cerró la carpeta';


--
-- TOC entry 3640 (class 0 OID 0)
-- Dependencies: 286
-- Name: COLUMN trd.trd_ocupado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd.trd_ocupado IS 'Indica si el expediente ya se encuentra relacionado con un expediente fisico';


--
-- TOC entry 3641 (class 0 OID 0)
-- Dependencies: 286
-- Name: COLUMN trd.trd_nivel; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd.trd_nivel IS 'Indica el nivel en el que se encuentra la carpeta virtual';


--
-- TOC entry 287 (class 1259 OID 53668)
-- Dependencies: 5
-- Name: trd_nivel; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE trd_nivel (
    trd_codi integer NOT NULL,
    depe_codi integer NOT NULL,
    trd_nombre character varying(50) NOT NULL,
    trd_descripcion character varying(100)
);


--
-- TOC entry 3642 (class 0 OID 0)
-- Dependencies: 287
-- Name: TABLE trd_nivel; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE trd_nivel IS 'Descripción de los niveles que tendrá la estructura de carpetas virtuales';


--
-- TOC entry 3643 (class 0 OID 0)
-- Dependencies: 287
-- Name: COLUMN trd_nivel.trd_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd_nivel.trd_codi IS 'Id del Item, es un secuencial dependiendo del area funcional';


--
-- TOC entry 3644 (class 0 OID 0)
-- Dependencies: 287
-- Name: COLUMN trd_nivel.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd_nivel.depe_codi IS 'Area funcional a la que pertenece el archivo';


--
-- TOC entry 3645 (class 0 OID 0)
-- Dependencies: 287
-- Name: COLUMN trd_nivel.trd_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd_nivel.trd_nombre IS 'Nombre del Item';


--
-- TOC entry 3646 (class 0 OID 0)
-- Dependencies: 287
-- Name: COLUMN trd_nivel.trd_descripcion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd_nivel.trd_descripcion IS 'Descripcion del Item';


--
-- TOC entry 288 (class 1259 OID 53671)
-- Dependencies: 5
-- Name: trd_radicado; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE trd_radicado (
    radi_nume_radi numeric(20,0) NOT NULL,
    trd_codi bigint NOT NULL,
    usua_codi integer,
    fecha timestamp with time zone,
    depe_codi integer
);


--
-- TOC entry 3647 (class 0 OID 0)
-- Dependencies: 288
-- Name: TABLE trd_radicado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE trd_radicado IS 'Relación entre las carpetas virtuales y los documentos que se asocian a ellas';


--
-- TOC entry 3648 (class 0 OID 0)
-- Dependencies: 288
-- Name: COLUMN trd_radicado.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd_radicado.radi_nume_radi IS 'Id del documento';


--
-- TOC entry 3649 (class 0 OID 0)
-- Dependencies: 288
-- Name: COLUMN trd_radicado.trd_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd_radicado.trd_codi IS 'Id de la carpeta';


--
-- TOC entry 3650 (class 0 OID 0)
-- Dependencies: 288
-- Name: COLUMN trd_radicado.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd_radicado.usua_codi IS 'Id del usuario que asoció el documento';


--
-- TOC entry 3651 (class 0 OID 0)
-- Dependencies: 288
-- Name: COLUMN trd_radicado.fecha; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd_radicado.fecha IS 'Fecha en la que se asoció el documento';


--
-- TOC entry 3652 (class 0 OID 0)
-- Dependencies: 288
-- Name: COLUMN trd_radicado.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN trd_radicado.depe_codi IS 'Área a la que pertenece la carpeta';


--
-- TOC entry 289 (class 1259 OID 53674)
-- Dependencies: 2584 5
-- Name: usuario; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE usuario (
    usua_codi integer NOT NULL,
    usua_cedula character varying(50),
    usua_nomb character varying(200),
    usua_apellido character varying(200),
    usua_nombre text,
    cargo_tipo integer,
    usua_cargo character varying,
    usua_nuevo smallint,
    usua_login character varying(50),
    usua_pasw character varying(35),
    usua_esta integer,
    usua_email character varying(500),
    usua_titulo character varying(100),
    usua_abr_titulo character varying(30),
    tipo_usuario integer,
    usua_tipo_certificado integer,
    usua_subrogado integer,
    visible_sub integer,
    usua_cargo_cabecera character varying,
    usua_direccion character varying,
    usua_telefono character varying,
    usua_firma_path character varying,
    depe_codi integer,
    depe_nomb character varying,
    dep_sigla character varying,
    inst_codi integer,
    inst_nombre character varying,
    inst_sigla character varying,
    inst_estado integer,
    ciu_codi integer,
    usua_ciudad character varying(100),
    tipo_identificacion integer DEFAULT 0,
    usua_datos character varying,
    inst_adscrita integer,
    inst_padre_nombre character varying(200),
    inst_padre_sigla character varying(10)
);


--
-- TOC entry 3653 (class 0 OID 0)
-- Dependencies: 289
-- Name: TABLE usuario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE usuario IS 'Vista materializada que contiene la unión de las tablas USUARIOS, CIUDADANO, INSTITUCION, DEPENDENCIA y CIUDAD.
Es actualizada por disparadores sobre las 5 tablas
Guarda cualquier error ocurrido en la tabla LOG_VIEW_USUARIO';


--
-- TOC entry 3654 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_codi IS 'Id del usuario o ciudadano';


--
-- TOC entry 3655 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_cedula; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_cedula IS 'No. de cédula';


--
-- TOC entry 3656 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_nomb; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_nomb IS 'Nombre del usuario';


--
-- TOC entry 3657 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_apellido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_apellido IS 'Apellido del usuario';


--
-- TOC entry 3658 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_nombre IS 'Nombres y apellidos del usuario concatenados';


--
-- TOC entry 3659 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.cargo_tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.cargo_tipo IS 'Tipo de cargo: 
- 0 normal  
- 1 jefe
- 2  asistente';


--
-- TOC entry 3660 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_nuevo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_nuevo IS 'Determina si el usuario ya cambió su clave del sistema o si se debe enviar el email para cambio de clave';


--
-- TOC entry 3661 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_login; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_login IS 'Login del usuario (deben comenzar con "U"); existen usuarios especiales que comienzan con ''UUSR'' y ''UADM''';


--
-- TOC entry 3662 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_pasw; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_pasw IS 'Contraseña del usuario en md5';


--
-- TOC entry 3663 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_esta; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_esta IS 'Estado del usuario, activo o inactivo';


--
-- TOC entry 3664 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_email IS 'Email, pueden ser varios separados por comas';


--
-- TOC entry 3665 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_titulo IS 'Tratamiento o título académico';


--
-- TOC entry 3666 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_abr_titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_abr_titulo IS 'Abreviacion del titulo';


--
-- TOC entry 3667 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.tipo_usuario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.tipo_usuario IS 'Tipo de usuario:
- 1 si es funcionario
- 2 si es ciudadano';


--
-- TOC entry 3668 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_tipo_certificado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_tipo_certificado IS 'Id del tipo de certificado digital que posee';


--
-- TOC entry 3669 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_subrogado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_subrogado IS 'Id del usuario subrogado';


--
-- TOC entry 3670 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.visible_sub; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.visible_sub IS 'Indica si el usuario ha sido subrogado';


--
-- TOC entry 3671 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_cargo_cabecera; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_cargo_cabecera IS 'Cargo que se muestra cuando se selecciona al usuario como destinatario';


--
-- TOC entry 3672 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_direccion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_direccion IS 'Dirección domiciliaria';


--
-- TOC entry 3673 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_telefono; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_telefono IS 'Número telefónico';


--
-- TOC entry 3674 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_firma_path; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_firma_path IS 'Path en el que se encuentra la imágen escaneada de la firma';


--
-- TOC entry 3675 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.depe_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.depe_codi IS 'Id del área a la que pertenece el usuario';


--
-- TOC entry 3676 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.depe_nomb; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.depe_nomb IS 'Nombre del área a la que pertenece el usuario';


--
-- TOC entry 3677 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.dep_sigla; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.dep_sigla IS 'Sigla del área a la que pertenece el usuario';


--
-- TOC entry 3678 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.inst_codi IS 'Id de la institución a la que pertenece el usuario';


--
-- TOC entry 3679 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.inst_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.inst_nombre IS 'Nombre de la institución a la que pertenece el usuario';


--
-- TOC entry 3680 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.inst_sigla; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.inst_sigla IS 'Siglas de la institución a la que pertenece el usuario';


--
-- TOC entry 3681 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.inst_estado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.inst_estado IS 'Estado de la institución a la que pertenece el usuario';


--
-- TOC entry 3682 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.ciu_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.ciu_codi IS 'Id de la diudad del usuario';


--
-- TOC entry 3683 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_ciudad; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_ciudad IS 'Nombre de la diudad del usuario';


--
-- TOC entry 3684 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.tipo_identificacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.tipo_identificacion IS '0 cedula  1 pasaporte';


--
-- TOC entry 3685 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.usua_datos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.usua_datos IS 'Datos concatenados del usuario sin tildes ni eñes y en mayúsculas (cédula, nombres, apellidos, título, cargo, email, área, institución, siglas)';


--
-- TOC entry 3686 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.inst_adscrita; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.inst_adscrita IS 'Id de la institución adscrita';


--
-- TOC entry 3687 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.inst_padre_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.inst_padre_nombre IS 'Nombre de la institución adscrita';


--
-- TOC entry 3688 (class 0 OID 0)
-- Dependencies: 289
-- Name: COLUMN usuario.inst_padre_sigla; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario.inst_padre_sigla IS 'Siglas de la institución adscrita';


--
-- TOC entry 290 (class 1259 OID 53681)
-- Dependencies: 5
-- Name: usuario_dependencia; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE usuario_dependencia (
    usua_codi_actualiza integer,
    usua_codi integer NOT NULL,
    depe_codi_padre integer,
    inst_codi integer,
    depe_codi_tmp character varying,
    usua_codi_depe integer NOT NULL
);


--
-- TOC entry 3689 (class 0 OID 0)
-- Dependencies: 290
-- Name: TABLE usuario_dependencia; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE usuario_dependencia IS 'Administración de usuarios por áreas';


--
-- TOC entry 3690 (class 0 OID 0)
-- Dependencies: 290
-- Name: COLUMN usuario_dependencia.usua_codi_actualiza; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario_dependencia.usua_codi_actualiza IS 'codigo de usuario que realiza la actualizacion';


--
-- TOC entry 3691 (class 0 OID 0)
-- Dependencies: 290
-- Name: COLUMN usuario_dependencia.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario_dependencia.usua_codi IS 'codigo de usuario';


--
-- TOC entry 3692 (class 0 OID 0)
-- Dependencies: 290
-- Name: COLUMN usuario_dependencia.depe_codi_padre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario_dependencia.depe_codi_padre IS 'dependencia padre de la institucion';


--
-- TOC entry 3693 (class 0 OID 0)
-- Dependencies: 290
-- Name: COLUMN usuario_dependencia.inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario_dependencia.inst_codi IS 'institucion que pertenece el usuario';


--
-- TOC entry 3694 (class 0 OID 0)
-- Dependencies: 290
-- Name: COLUMN usuario_dependencia.depe_codi_tmp; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario_dependencia.depe_codi_tmp IS 'dependencias que administra el usuario';


--
-- TOC entry 3695 (class 0 OID 0)
-- Dependencies: 290
-- Name: COLUMN usuario_dependencia.usua_codi_depe; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario_dependencia.usua_codi_depe IS 'clave primaria de la tabla';


--
-- TOC entry 291 (class 1259 OID 53687)
-- Dependencies: 290 5
-- Name: usuario_dependencia_usua_codi_depe_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE usuario_dependencia_usua_codi_depe_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3696 (class 0 OID 0)
-- Dependencies: 291
-- Name: usuario_dependencia_usua_codi_depe_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE usuario_dependencia_usua_codi_depe_seq OWNED BY usuario_dependencia.usua_codi_depe;


--
-- TOC entry 3697 (class 0 OID 0)
-- Dependencies: 291
-- Name: usuario_dependencia_usua_codi_depe_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('usuario_dependencia_usua_codi_depe_seq', 1, false);


--
-- TOC entry 292 (class 1259 OID 53689)
-- Dependencies: 2455 5
-- Name: usuario_nombre; Type: VIEW; Schema: public; Owner: -
--

CREATE VIEW usuario_nombre AS
    SELECT u.usua_codi, (((COALESCE(u.usua_nomb, ''::character varying))::text || ' '::text) || (COALESCE(u.usua_apellido, ''::character varying))::text) AS usua_nombre FROM usuarios u UNION SELECT c.ciu_codigo AS usua_codi, (((COALESCE(c.ciu_nombre, ''::character varying))::text || ' '::text) || (COALESCE(c.ciu_apellido, ''::character varying))::text) AS usua_nombre FROM ciudadano c;


--
-- TOC entry 293 (class 1259 OID 53694)
-- Dependencies: 5
-- Name: usuario_notificacion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE usuario_notificacion (
    id_mail numeric NOT NULL,
    usua_destinatario numeric NOT NULL,
    usua_nombre character varying,
    email character varying
);


--
-- TOC entry 3698 (class 0 OID 0)
-- Dependencies: 293
-- Name: TABLE usuario_notificacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE usuario_notificacion IS 'Usuarios a los que se envió notificaciones por email';


--
-- TOC entry 3699 (class 0 OID 0)
-- Dependencies: 293
-- Name: COLUMN usuario_notificacion.id_mail; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario_notificacion.id_mail IS 'Id del email';


--
-- TOC entry 3700 (class 0 OID 0)
-- Dependencies: 293
-- Name: COLUMN usuario_notificacion.usua_destinatario; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario_notificacion.usua_destinatario IS 'Id del usuario destinatario';


--
-- TOC entry 3701 (class 0 OID 0)
-- Dependencies: 293
-- Name: COLUMN usuario_notificacion.usua_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario_notificacion.usua_nombre IS 'Nombre del usuario';


--
-- TOC entry 3702 (class 0 OID 0)
-- Dependencies: 293
-- Name: COLUMN usuario_notificacion.email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuario_notificacion.email IS 'Email del usuario';


--
-- TOC entry 294 (class 1259 OID 53700)
-- Dependencies: 5
-- Name: usuarios_radicado_usua_radi_codi_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE usuarios_radicado_usua_radi_codi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3703 (class 0 OID 0)
-- Dependencies: 294
-- Name: usuarios_radicado_usua_radi_codi_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('usuarios_radicado_usua_radi_codi_seq', 1, false);


--
-- TOC entry 295 (class 1259 OID 53702)
-- Dependencies: 2586 5
-- Name: usuarios_radicado; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE usuarios_radicado (
    radi_nume_radi numeric(20,0),
    usua_cedula character varying(50),
    usua_nombre character varying(200),
    usua_apellido character varying(200),
    usua_titulo character varying(100),
    usua_cargo character varying(200),
    usua_institucion character varying(200),
    radi_usua_tipo smallint,
    usua_abr_titulo character varying(30),
    usua_email character varying(500),
    usua_radi_codi bigint DEFAULT nextval('usuarios_radicado_usua_radi_codi_seq'::regclass) NOT NULL,
    usua_ciudad character(100),
    usua_area character varying(150),
    usua_area_codi numeric(20,0),
    lista_nombre character varying,
    usua_firma_path character varying,
    usua_codi bigint,
    inst_codi integer
);


--
-- TOC entry 3704 (class 0 OID 0)
-- Dependencies: 295
-- Name: TABLE usuarios_radicado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE usuarios_radicado IS 'Se guardan los datos de los usuarios (de, para y cca) tal y como estaban en la BDD al momento de firmar el documento';


--
-- TOC entry 3705 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.radi_nume_radi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.radi_nume_radi IS 'Id del documento';


--
-- TOC entry 3706 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_cedula; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_cedula IS 'Número de cédula';


--
-- TOC entry 3707 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_nombre IS 'Nombre del usuario';


--
-- TOC entry 3708 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_apellido; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_apellido IS 'Apellido del usuario';


--
-- TOC entry 3709 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_titulo IS 'Tratamiento o título académico';


--
-- TOC entry 3710 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_cargo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_cargo IS 'Cargo del usuario';


--
-- TOC entry 3711 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_institucion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_institucion IS 'Nombre de la institución';


--
-- TOC entry 3712 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.radi_usua_tipo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.radi_usua_tipo IS 'Tipo de usuario: 
1 - Remitente 
2 - Destinatario 
3 - Con copia a';


--
-- TOC entry 3713 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_abr_titulo; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_abr_titulo IS 'Abreviación del título';


--
-- TOC entry 3714 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_email; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_email IS 'Dirección del correo electrónico';


--
-- TOC entry 3715 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_radi_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_radi_codi IS 'Id';


--
-- TOC entry 3716 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_ciudad; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_ciudad IS 'Nombre de la ciudad en la que se encuentra el usuario';


--
-- TOC entry 3717 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_area; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_area IS 'nombre del área a la que pertenece';


--
-- TOC entry 3718 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_area_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_area_codi IS 'Id del área';


--
-- TOC entry 3719 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.lista_nombre; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.lista_nombre IS 'Nombre de la lista (Si el documento fue enviado a una lista, caso contrario campo vacío)';


--
-- TOC entry 3720 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_firma_path; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_firma_path IS 'Path del archivo en caso que tenga escaneada una firma';


--
-- TOC entry 3721 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.usua_codi IS 'Id del usuario';


--
-- TOC entry 3722 (class 0 OID 0)
-- Dependencies: 295
-- Name: COLUMN usuarios_radicado.inst_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_radicado.inst_codi IS 'Id de la institución';


--
-- TOC entry 296 (class 1259 OID 53709)
-- Dependencies: 2587 5
-- Name: usuarios_sesion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE usuarios_sesion (
    usua_codi integer NOT NULL,
    usua_sesion character varying(200),
    usua_fech_sesion timestamp with time zone,
    usua_intentos integer DEFAULT 0,
    ip_cliente character varying(300)
);


--
-- TOC entry 3723 (class 0 OID 0)
-- Dependencies: 296
-- Name: TABLE usuarios_sesion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE usuarios_sesion IS 'Se almacenan los datos de la conexión cuando un usuario ingresa al sistema';


--
-- TOC entry 3724 (class 0 OID 0)
-- Dependencies: 296
-- Name: COLUMN usuarios_sesion.usua_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_sesion.usua_codi IS 'Id del usuario';


--
-- TOC entry 3725 (class 0 OID 0)
-- Dependencies: 296
-- Name: COLUMN usuarios_sesion.usua_sesion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_sesion.usua_sesion IS 'Id de la session';


--
-- TOC entry 3726 (class 0 OID 0)
-- Dependencies: 296
-- Name: COLUMN usuarios_sesion.usua_fech_sesion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_sesion.usua_fech_sesion IS 'Fecha de inicio de la sesion';


--
-- TOC entry 3727 (class 0 OID 0)
-- Dependencies: 296
-- Name: COLUMN usuarios_sesion.usua_intentos; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_sesion.usua_intentos IS 'número de veces que el usuario intentó loguearse en el sistema (luego de 5 intentos fallidos se bloquea el usuario por 5 minutos)';


--
-- TOC entry 3728 (class 0 OID 0)
-- Dependencies: 296
-- Name: COLUMN usuarios_sesion.ip_cliente; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_sesion.ip_cliente IS 'Ip de la máquina del usuario que accede al sistema';


--
-- TOC entry 297 (class 1259 OID 53716)
-- Dependencies: 5
-- Name: usuarios_subrogacion; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE usuarios_subrogacion (
    usua_subrogado integer,
    usua_subrogante integer,
    usua_fecha_inicio timestamp with time zone,
    usua_fecha_fin timestamp with time zone,
    usua_visible integer,
    usua_observacion character varying,
    usua_subrogacion_codi integer NOT NULL,
    usua_fecha_actualizacion timestamp with time zone,
    usua_codi_actualiza integer
);


--
-- TOC entry 3729 (class 0 OID 0)
-- Dependencies: 297
-- Name: TABLE usuarios_subrogacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON TABLE usuarios_subrogacion IS 'Controla la subrogación de usuarios, cuando por algun motivo el titular del cargo debe ausentarse temporalmente y otro lo remplaza en sus funciones';


--
-- TOC entry 3730 (class 0 OID 0)
-- Dependencies: 297
-- Name: COLUMN usuarios_subrogacion.usua_subrogado; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_subrogacion.usua_subrogado IS 'Id del usuario que va a ser reemplazado temporalmente por subrogante';


--
-- TOC entry 3731 (class 0 OID 0)
-- Dependencies: 297
-- Name: COLUMN usuarios_subrogacion.usua_subrogante; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_subrogacion.usua_subrogante IS 'Id del usuario que reemplaza al subrogado';


--
-- TOC entry 3732 (class 0 OID 0)
-- Dependencies: 297
-- Name: COLUMN usuarios_subrogacion.usua_fecha_inicio; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_subrogacion.usua_fecha_inicio IS 'Fecha que inicia la subrogacion';


--
-- TOC entry 3733 (class 0 OID 0)
-- Dependencies: 297
-- Name: COLUMN usuarios_subrogacion.usua_fecha_fin; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_subrogacion.usua_fecha_fin IS 'Fecha que finaliza la subrogacion';


--
-- TOC entry 3734 (class 0 OID 0)
-- Dependencies: 297
-- Name: COLUMN usuarios_subrogacion.usua_visible; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_subrogacion.usua_visible IS 'Estado de subrogacion, activa o terminada';


--
-- TOC entry 3735 (class 0 OID 0)
-- Dependencies: 297
-- Name: COLUMN usuarios_subrogacion.usua_observacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_subrogacion.usua_observacion IS 'observaciones';


--
-- TOC entry 3736 (class 0 OID 0)
-- Dependencies: 297
-- Name: COLUMN usuarios_subrogacion.usua_subrogacion_codi; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_subrogacion.usua_subrogacion_codi IS 'Id';


--
-- TOC entry 3737 (class 0 OID 0)
-- Dependencies: 297
-- Name: COLUMN usuarios_subrogacion.usua_fecha_actualizacion; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_subrogacion.usua_fecha_actualizacion IS 'Fecha que se registra';


--
-- TOC entry 3738 (class 0 OID 0)
-- Dependencies: 297
-- Name: COLUMN usuarios_subrogacion.usua_codi_actualiza; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN usuarios_subrogacion.usua_codi_actualiza IS 'Id del usuario quien hace la subrogacion';


--
-- TOC entry 298 (class 1259 OID 53722)
-- Dependencies: 5 297
-- Name: usuarios_subrogacion_usua_subrogacion_codi_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE usuarios_subrogacion_usua_subrogacion_codi_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TOC entry 3739 (class 0 OID 0)
-- Dependencies: 298
-- Name: usuarios_subrogacion_usua_subrogacion_codi_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE usuarios_subrogacion_usua_subrogacion_codi_seq OWNED BY usuarios_subrogacion.usua_subrogacion_codi;


--
-- TOC entry 3740 (class 0 OID 0)
-- Dependencies: 298
-- Name: usuarios_subrogacion_usua_subrogacion_codi_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('usuarios_subrogacion_usua_subrogacion_codi_seq', 1, false);


--
-- TOC entry 2469 (class 2604 OID 53727)
-- Dependencies: 171 170
-- Name: ban_com_codi; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY bandeja_compartida ALTER COLUMN ban_com_codi SET DEFAULT nextval('bandeja_compartida_ban_com_codi_seq1'::regclass);


--
-- TOC entry 2473 (class 2604 OID 53728)
-- Dependencies: 177 176
-- Name: cat_codi; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY categoria ALTER COLUMN cat_codi SET DEFAULT nextval('categoria_cat_codi_seq1'::regclass);


--
-- TOC entry 2478 (class 2604 OID 53729)
-- Dependencies: 182 181
-- Name: cod_codi; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY codificacion ALTER COLUMN cod_codi SET DEFAULT nextval('codificacion_cod_codi_seq1'::regclass);


--
-- TOC entry 2518 (class 2604 OID 53730)
-- Dependencies: 203 202
-- Name: his_id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY hist_envio_fisico ALTER COLUMN his_id SET DEFAULT nextval('hist_envio_fisico_his_id_seq1'::regclass);


--
-- TOC entry 2519 (class 2604 OID 53731)
-- Dependencies: 205 204
-- Name: hist_codi; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY hist_opc_impresion ALTER COLUMN hist_codi SET DEFAULT nextval('hist_opc_impresion_hist_codi_seq'::regclass);


--
-- TOC entry 2526 (class 2604 OID 53734)
-- Dependencies: 216 215
-- Name: log_codi; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_archivo_descarga ALTER COLUMN log_codi SET DEFAULT nextval('log_archivo_descarga_log_codi_seq'::regclass);


--
-- TOC entry 2531 (class 2604 OID 53735)
-- Dependencies: 223 222
-- Name: log_codi; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_matar_procesos_servidores ALTER COLUMN log_codi SET DEFAULT nextval('log_matar_procesos_servidores_log_codi_seq'::regclass);


--
-- TOC entry 2536 (class 2604 OID 53736)
-- Dependencies: 233 232
-- Name: log_codi; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_view_usuario ALTER COLUMN log_codi SET DEFAULT nextval('log_view_usuario_log_codi_seq'::regclass);


--
-- TOC entry 2555 (class 2604 OID 53737)
-- Dependencies: 241 240
-- Name: opc_imp_codi; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY opciones_impresion ALTER COLUMN opc_imp_codi SET DEFAULT nextval('opciones_impresion_opc_imp_codi_seq'::regclass);


--
-- TOC entry 2556 (class 2604 OID 53738)
-- Dependencies: 243 242
-- Name: opc_imp_sob_codi; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY opciones_impresion_sobre ALTER COLUMN opc_imp_sob_codi SET DEFAULT nextval('opciones_impresion_sobre_opc_imp_sob_codi_seq'::regclass);


--
-- TOC entry 2578 (class 2604 OID 53739)
-- Dependencies: 285 284
-- Name: tit_codi; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY titulo ALTER COLUMN tit_codi SET DEFAULT nextval('titulo_tit_codi_seq1'::regclass);


--
-- TOC entry 2585 (class 2604 OID 53740)
-- Dependencies: 291 290
-- Name: usua_codi_depe; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuario_dependencia ALTER COLUMN usua_codi_depe SET DEFAULT nextval('usuario_dependencia_usua_codi_depe_seq'::regclass);


--
-- TOC entry 2588 (class 2604 OID 53741)
-- Dependencies: 298 297
-- Name: usua_subrogacion_codi; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuarios_subrogacion ALTER COLUMN usua_subrogacion_codi SET DEFAULT nextval('usuarios_subrogacion_usua_subrogacion_codi_seq'::regclass);


--
-- TOC entry 2898 (class 0 OID 53012)
-- Dependencies: 162
-- Data for Name: accion; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2899 (class 0 OID 53017)
-- Dependencies: 164
-- Data for Name: actualizar_sistema; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2900 (class 0 OID 53028)
-- Dependencies: 165
-- Data for Name: anexos; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2901 (class 0 OID 53037)
-- Dependencies: 166
-- Data for Name: anexos_tipo; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (1, 'doc', '.doc (Procesador de texto - Word)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (13, 'csv', 'csv (separado por comas)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (9, 'zip', '.zip (Comprimido)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (12, 'arg', 'Argo(Diagrama)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (16, 'xml', '.xml (XML de Microsoft Word 2003)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (2, 'xls', '.xls (Hoja de calculo)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (8, 'txt', '.txt (Documento texto)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (4, 'tif', '.tif (Imagen)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (10, 'rtf', '.rtf (Rich Text Format)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (3, 'ppt', '.ppt (Presentacion)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (17, 'png', '.png (Imagen)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (7, 'pdf', '.pdf (Acrobat Reader)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (14, 'odt', '.odt (Procesador de Texto - odf)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (15, 'ods', '.ods (Hoja de Calculo - Odf)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (5, 'jpg', '.jpg (Imagen)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (6, 'gif', '.gif (Imagen)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (11, 'dia', '.dia (Diagrama)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (18, 'p7m', 'Archivo firmado digitalmente', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (19, 'mpp', 'Microsoft Poject', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (20, 'vsd', 'Microsoft Visio', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (21, 'rar', '.rar(Comprimido)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (22, 'odt', '.odt (Presentación Open Office)', 1);
INSERT INTO anexos_tipo (anex_tipo_codi, anex_tipo_ext, anex_tipo_desc, anex_tipo_estado) VALUES (23, 'dwg', '.dwg (Autocad)', 1);


--
-- TOC entry 2902 (class 0 OID 53041)
-- Dependencies: 167
-- Data for Name: archivo; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO archivo (arch_codi, arch_padre, arch_nombre, arch_sigla, depe_codi, arch_estado, arch_ocupado) VALUES (0, 0, NULL, NULL, NULL, 1, 0);


--
-- TOC entry 2903 (class 0 OID 53047)
-- Dependencies: 168
-- Data for Name: archivo_nivel; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2904 (class 0 OID 53050)
-- Dependencies: 169
-- Data for Name: archivo_radicado; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2905 (class 0 OID 53054)
-- Dependencies: 170
-- Data for Name: bandeja_compartida; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2906 (class 0 OID 53061)
-- Dependencies: 173
-- Data for Name: bloqueo_sistema; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO bloqueo_sistema (bloq_codi, fecha_inicio, fecha_fin, estado, descripcion, mensaje_usuario, usua_acceso, tipo_mensaje) VALUES (1, '2013-12-31 23:45:00-05', '2014-01-01 00:31:48.015088-05', 0, 'Bloqueo de fin de año', 'Estimado usuario, en este momento se est&aacute; realizando tareas de mantenimiento en el sistema,<br />
por inicio de a&ntilde;o, por favor ingrese a partir de las 4 am del 1 de enero.<br />
<br />
La Secretar&iacute;a Nacional de la Admnistraci&oacute;n P&uacute;blica les agradece por la confianza brindada<br />
y el trabajo desempe&ntilde;ado durante este 2013 y les desea un venturoso a&ntilde;o 2014.<br />', '', 0);


--
-- TOC entry 2907 (class 0 OID 53079)
-- Dependencies: 175
-- Data for Name: carpeta; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO carpeta (carp_codi, carp_nombre, carp_descripcion, carp_orden) VALUES (14, 'Compartida', 'Documentos compartidos de bandeja de recibidos', 9);
INSERT INTO carpeta (carp_codi, carp_nombre, carp_descripcion, carp_orden) VALUES (1, 'En Elaboración', 'Documentos que se encuentran en estado de elaboración (Alt+B)', 1);
INSERT INTO carpeta (carp_codi, carp_nombre, carp_descripcion, carp_orden) VALUES (2, 'Recibidos', 'Documentos recibidos (Alt+R)', 2);
INSERT INTO carpeta (carp_codi, carp_nombre, carp_descripcion, carp_orden) VALUES (6, 'Eliminados', 'Documentos eliminados (Alt+C)', 3);
INSERT INTO carpeta (carp_codi, carp_nombre, carp_descripcion, carp_orden) VALUES (7, 'No Enviados', 'Documentos que no han sido enviados a su destinatario (Alt+N)', 4);
INSERT INTO carpeta (carp_codi, carp_nombre, carp_descripcion, carp_orden) VALUES (8, 'Enviados', 'Documentos Enviados (Alt+E)', 5);
INSERT INTO carpeta (carp_codi, carp_nombre, carp_descripcion, carp_orden) VALUES (10, 'Archivados', 'Documentos archivados (Alt+A)', 7);
INSERT INTO carpeta (carp_codi, carp_nombre, carp_descripcion, carp_orden) VALUES (12, 'Reasignados', 'Documentos reasignados a otros funcionarios (Alt+P)', 6);
INSERT INTO carpeta (carp_codi, carp_nombre, carp_descripcion, carp_orden) VALUES (13, 'Informados', 'Documentos para su información (Alt+I)', 8);
INSERT INTO carpeta (carp_codi, carp_nombre, carp_descripcion, carp_orden) VALUES (15, 'Tareas Recibidas', 'Tareas asignadas a *usuario* (Alt+T)', 10);
INSERT INTO carpeta (carp_codi, carp_nombre, carp_descripcion, carp_orden) VALUES (16, 'Tareas Enviadas', 'Tareas asignadas a otros usuarios por *usuario* (Alt+S)', 11);


--
-- TOC entry 2908 (class 0 OID 53082)
-- Dependencies: 176
-- Data for Name: categoria; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO categoria (cat_codi, cat_descr) VALUES (0, 'Normal');
INSERT INTO categoria (cat_codi, cat_descr) VALUES (1, 'Extemporáneo');
INSERT INTO categoria (cat_codi, cat_descr) VALUES (2, 'Personal');
INSERT INTO categoria (cat_codi, cat_descr) VALUES (3, 'Urgente');


--
-- TOC entry 2909 (class 0 OID 53087)
-- Dependencies: 178
-- Data for Name: ciudad; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO ciudad (id, nombre, id_padre) VALUES (2, 'Ponce Enríquez', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (3, 'Camilo Ponce Enríquez', 2);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (4, 'Cuenca', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (5, 'Cuenca', 4);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (6, 'Chordeleg', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (7, 'Chordeleg', 6);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (8, 'El Pan', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (9, 'El Pan', 8);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (10, 'Girón', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (11, 'Girón', 10);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (12, 'Gualaceo', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (13, 'Gualaceo', 12);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (14, 'Nabón', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (15, 'Nabón', 14);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (16, 'Oña', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (17, 'Oña', 16);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (18, 'Paute', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (19, 'Paute', 18);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (20, 'Pucará', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (21, 'Pucará', 20);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (22, 'San Fernando', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (23, 'San Fernando', 22);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (24, 'Santa Isabel', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (25, 'Santa Isabel', 24);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (26, 'Sevilla de Oro', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (27, 'Sevilla de Oro', 26);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (28, 'Sígsig', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (29, 'Sígsig', 28);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (30, 'Guachapala', 1);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (31, 'Guachapala', 30);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (33, 'Caluma', 32);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (34, 'Caluma', 33);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (35, 'Chillanes', 32);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (36, 'Chillanes', 35);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (37, 'Chimbo', 32);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (38, 'Chimbo', 37);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (39, 'Echéandia', 32);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (40, 'Echeandía', 39);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (41, 'Guaranda', 32);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (42, 'Guaranda', 41);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (43, 'Las Naves', 32);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (44, 'Las Naves', 43);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (45, 'San Miguel', 32);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (46, 'San Miguel', 45);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (48, 'Azogues', 47);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (49, 'Azogues', 48);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (50, 'Biblián', 47);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (51, 'Biblián', 50);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (52, 'Déleg', 47);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (53, 'Déleg', 52);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (54, 'El Tambo', 47);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (55, 'El Tambo', 54);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (56, 'La Troncal', 47);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (57, 'La Troncal', 56);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (58, 'Suscal', 47);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (59, 'Suscal', 58);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (61, 'Bolívar - Carchi', 60);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (62, 'Bolívar', 61);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (63, 'Espejo', 60);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (64, 'El Ángel', 63);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (65, 'Huaca', 60);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (66, 'Huaca', 65);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (67, 'Montúfar', 60);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (68, 'San Gabriel', 67);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (69, 'Mira', 60);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (70, 'Mira', 69);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (71, 'Tulcán', 60);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (72, 'Tulcán', 71);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (74, 'Alausí', 73);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (75, 'Alausí', 74);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (76, 'Colta', 73);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (77, 'Villa La Unión', 76);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (78, 'Cumandá', 73);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (79, 'Cumandá', 78);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (80, 'Chambo', 73);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (81, 'Chambo', 80);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (82, 'Chunchi', 73);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (83, 'Chunchi', 82);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (84, 'Guamote', 73);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (85, 'Guamote', 84);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (86, 'Guano', 73);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (87, 'Guano', 86);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (88, 'Pallatanga', 73);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (89, 'Pallatanga', 88);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (90, 'Penipe', 73);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (91, 'Penipe', 90);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (92, 'Riobamba', 73);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (93, 'Riobamba', 92);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (95, 'Latacunga', 94);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (96, 'Latacunga', 95);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (97, 'La Maná', 94);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (98, 'La Maná', 97);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (99, 'Pangua', 94);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (100, 'El Corazón', 99);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (101, 'Pujilí', 94);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (102, 'Pujilí', 101);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (103, 'Salcedo', 94);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (104, 'San Miguel', 103);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (105, 'Saquisilí', 94);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (106, 'Saquisilí', 105);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (107, 'Sigchos', 94);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (108, 'Sigchos', 107);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (110, 'Arenillas', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (111, 'Arenillas', 110);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (112, 'Atahualpa', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (113, 'Paccha', 112);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (114, 'Balsas', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (115, 'Balsas', 114);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (116, 'Chilla', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (117, 'Chilla', 116);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (118, 'El Guabo', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (119, 'El Guabo', 118);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (120, 'Huaquillas', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (121, 'Huaquillas', 120);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (122, 'Las Lajas', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (123, 'La Victoria', 122);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (124, 'Machala', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (125, 'Machala', 124);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (126, 'Marcabelí', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (127, 'Marcabelí', 126);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (128, 'Pasaje', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (129, 'Pasaje', 128);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (130, 'Piñas', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (131, 'Piñas', 130);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (132, 'Portovelo', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (133, 'Portovelo', 132);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (134, 'Santa Rosa', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (135, 'Santa Rosa', 134);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (136, 'Zaruma', 109);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (137, 'Zaruma', 136);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (139, 'Eloy Alfaro', 138);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (140, 'Valdez', 139);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (141, 'Muisne', 138);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (142, 'Muisne', 141);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (143, 'Quinindé', 138);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (144, 'Rosa Zárate', 143);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (145, 'San Lorenzo', 138);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (146, 'San Lorenzo', 145);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (147, 'Atacames', 138);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (148, 'Atacames', 147);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (149, 'Rioverde', 138);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (150, 'Rioverde', 149);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (152, 'Santa Cruz', 151);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (153, 'Puerto Ayora', 152);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (154, 'San Cristóbal', 151);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (155, 'Puerto Baquerizo Moreno', 154);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (156, 'Isabela', 151);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (157, 'Puerto Villamil', 156);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (159, 'Baquerizo Moreno', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (160, 'Baquerizo Moreno', 159);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (161, 'Balao', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (162, 'Balao', 161);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (163, 'Balzar', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (164, 'Balzar', 163);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (165, 'Colimes', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (166, 'Colimes', 165);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (167, 'Daule', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (168, 'Daule', 167);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (169, 'Durán', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (170, 'Eloy Alfaro', 169);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (171, 'El Empalme', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (172, 'Velasco Ibarra', 171);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (173, 'El Triunfo', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (174, 'El Triunfo', 173);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (175, 'Antonio Elizalde', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (176, 'Antonio Elizalde', 175);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (177, 'Guayaquil', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (178, 'Guayaquil', 177);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (179, 'Isidro Ayora', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (180, 'Isidro Ayora', 179);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (181, 'Lomas de Sargentillo', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (182, 'Lomas de Sargentillo', 181);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (183, 'Marcelino Maridueña', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (184, 'Marcelino Maridueña', 183);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (185, 'Milagro', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (186, 'Milagro', 185);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (187, 'Naranjal', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (188, 'Naranjal', 187);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (189, 'Naranjito', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (190, 'Naranjito', 189);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (191, 'Nobol', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (192, 'Narcisa de Jesús', 191);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (193, 'Palestina', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (194, 'Palestina', 193);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (195, 'Pedro Carbo', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (196, 'Pedro Carbo', 195);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (197, 'Playas', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (198, 'General Villamil', 197);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (199, 'Samborondón', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (200, 'Samborondón', 199);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (201, 'Santa Lucía', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (202, 'Santa Lucía', 201);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (203, 'Simón Bolívar', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (204, 'Simón Bolívar', 203);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (205, 'Salitre', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (206, 'El Salitre', 205);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (207, 'Yaguachi', 158);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (208, 'San Jacinto de Yaguachi', 207);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (210, 'Antonio Ante', 209);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (211, 'Atuntaqui', 210);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (212, 'Cotacachi', 209);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (213, 'Cotacachi', 212);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (214, 'Ibarra', 209);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (215, 'Ibarra', 214);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (216, 'Otavalo', 209);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (217, 'Otavalo', 216);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (218, 'Pimampiro', 209);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (219, 'Pimampiro', 218);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (220, 'Urcuquí', 209);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (221, 'Urcuquí', 220);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (223, 'Calvas', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (224, 'Cariamanga', 223);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (225, 'Catamayo', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (226, 'Catamayo', 225);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (227, 'Celica', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (228, 'Celica', 227);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (229, 'Chaguarpamba', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (230, 'Chaguarpamba', 229);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (231, 'Espíndola', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (232, 'Amaluza', 231);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (233, 'Gonzanamá', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (234, 'Gonzanamá', 233);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (235, 'Macará', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (236, 'Macará', 235);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (237, 'Olmedo - Loja', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (238, 'Olmedo - Loja', 237);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (239, 'Paltas', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (240, 'Catacocha', 239);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (241, 'Pindal', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (242, 'Pindal', 241);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (243, 'Puyango', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (244, 'Alamor', 243);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (245, 'Quilanga', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (246, 'Quilanga', 245);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (247, 'Saraguro', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (248, 'Saraguro', 247);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (249, 'Sozoranga', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (250, 'Sozoranga', 249);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (251, 'Zapotillo', 222);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (252, 'Zapotillo', 251);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (254, 'Babahoyo', 253);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (255, 'Babahoyo', 254);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (256, 'Montalvo', 253);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (257, 'Montalvo', 256);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (258, 'Puebloviejo', 253);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (259, 'Puebloviejo', 258);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (260, 'Quinsaloma', 253);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (261, 'Quinsaloma', 260);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (262, 'Quevedo', 253);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (263, 'Quevedo', 262);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (264, 'Urdaneta', 253);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (265, 'Catarama', 264);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (266, 'Ventanas', 253);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (267, 'Ventanas', 266);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (268, 'Vinces', 253);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (269, 'Vinces', 268);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (270, 'Palenque', 253);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (271, 'Palenque', 270);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (272, 'Buena Fe', 253);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (273, 'San Jacinto de Buena Fe', 272);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (274, 'Valencia', 253);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (275, 'Valencia', 274);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (276, 'Mocache', 253);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (277, 'Mocache', 276);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (279, 'Bolívar - Manabí', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (280, 'Calceta', 279);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (281, 'Chone', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (282, 'Chone', 281);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (283, 'El Carmen', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (284, 'El Carmen', 283);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (285, 'Flavio Alfaro', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (286, 'Flavio Alfaro', 285);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (287, 'Jipijapa', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (288, 'Jipijapa', 287);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (289, 'Puerto López', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (290, 'Puerto López', 289);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (291, 'Junín', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (292, 'Junín', 291);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (293, 'Manta', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (294, 'Manta', 293);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (295, 'Montecristi', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (296, 'Montecristi', 295);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (297, 'Paján', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (298, 'Paján', 297);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (299, 'Pichincha', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (300, 'Pichincha', 299);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (301, 'Portoviejo', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (302, 'Portoviejo', 301);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (303, 'Rocafuerte', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (304, 'Rocafuerte', 303);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (305, 'Santa Ana', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (306, 'Santa Ana', 305);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (307, 'Olmedo - Manabí', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (308, 'Olmedo - Manabí', 307);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (309, 'Sucre', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (310, 'Bahía de Caráquez', 309);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (311, 'Tosagua', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (312, 'Tosagua', 311);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (313, '24 de Mayo', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (314, 'Sucre', 313);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (315, 'Pedernales', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (316, 'Pedernales', 315);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (317, 'Jama', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (318, 'Jama', 317);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (319, 'Jaramijó', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (320, 'Jaramijó', 319);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (321, 'San Vicente', 278);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (322, 'San Vicente', 321);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (324, 'Gualaquiza', 323);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (325, 'Gualaquiza', 324);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (326, 'Huamboya', 323);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (327, 'Huamboya', 326);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (328, 'Limón Indanza', 323);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (329, 'Plaza Gutiérrez', 328);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (330, 'Logroño', 323);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (331, 'Logroño', 330);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (332, 'Macas', 323);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (333, 'Pablo Sexto', 323);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (334, 'Pablo Sexto', 333);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (335, 'Palora', 323);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (336, 'Palora', 335);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (337, 'San Juan Bosco', 323);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (338, 'San Juan Bosco', 337);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (339, 'Santiago de Méndez', 323);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (340, 'Sucúa', 323);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (341, 'Sucúa', 340);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (342, 'Taisha', 323);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (343, 'Taisha', 342);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (344, 'Tiwintza', 323);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (345, 'Santiago', 344);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (347, 'Archidona', 346);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (348, 'Archidona', 347);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (349, 'Arosemena Tola', 346);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (350, 'Arosemena Tola', 349);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (351, 'El Chaco', 346);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (352, 'El Chaco', 351);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (353, 'Quijos', 346);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (354, 'Baeza', 353);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (355, 'Tena', 346);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (356, 'Tena', 355);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (358, 'Aguarico', 357);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (359, 'Nuevo Rocafuerte', 358);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (360, 'Francisco de Orellana', 357);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (361, 'Sachas', 357);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (362, 'La Joya de los Sachas', 361);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (363, 'Loreto', 357);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (364, 'Loreto', 363);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (366, 'Arajuno', 365);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (367, 'Arajuno', 366);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (368, 'Mera', 365);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (369, 'Mera', 368);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (370, 'Puyo', 365);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (371, 'Santa Clara', 365);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (372, 'Santa Clara', 371);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (374, 'Cayambe', 373);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (375, 'Cayambe', 374);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (376, 'Mejía', 373);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (377, 'Machachi', 376);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (378, 'Pedro Moncayo', 373);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (379, 'Tabacundo', 378);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (380, 'Pedro Vicente', 373);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (381, 'Pedro Vicente', 380);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (382, 'Puerto Quito', 373);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (383, 'Puerto Quito', 382);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (384, 'Rumiñahui', 373);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (385, 'Sangolquí', 384);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (386, 'Los Bancos', 373);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (387, 'Los Bancos', 386);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (389, 'La Libertad', 388);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (390, 'La Libertad', 389);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (391, 'Salinas', 388);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (392, 'Salinas', 391);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (394, 'La Concordia', 393);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (395, 'La Concordia', 394);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (397, 'Cascales', 396);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (398, 'El Dorado de Cascales', 397);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (399, 'Cuyabeno', 396);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (400, 'Tarapoa', 399);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (401, 'Gonzalo Pizarro', 396);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (402, 'Lumbaqui', 401);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (403, 'Lago Agrio', 396);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (404, 'Nueva Loja', 403);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (405, 'Putumayo', 396);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (406, 'Puerto El Carmen', 405);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (407, 'Shushufindi', 396);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (408, 'Shushufindi', 407);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (409, 'La Bonita', 396);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (411, 'Ambato', 410);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (412, 'Ambato', 411);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (413, 'Baños de Agua Santa', 410);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (414, 'Baños de Agua Santa', 413);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (415, 'Cevallos', 410);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (416, 'Cevallos', 415);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (417, 'Mocha', 410);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (418, 'Mocha', 417);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (419, 'Patate', 410);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (420, 'Patate', 419);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (421, 'Quero', 410);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (422, 'Quero', 421);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (423, 'Pelileo', 410);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (424, 'Pelileo', 423);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (425, 'Píllaro', 410);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (426, 'Píllaro', 425);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (427, 'Tisaleo', 410);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (428, 'Tisaleo', 427);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (430, 'Centinela del Cóndor', 429);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (431, 'Zumbi', 430);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (432, 'Zumba', 429);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (433, 'El Pangui', 429);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (434, 'El Pangui', 433);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (435, 'Nangaritza', 429);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (436, 'Guayzimi', 435);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (437, 'Palanda', 429);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (438, 'Palanda', 437);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (439, 'Paquisha', 429);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (440, 'Paquisha', 439);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (441, 'Yacuambí', 429);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (442, '28 de Mayo', 441);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (443, 'Yantzaza', 429);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (444, 'Yantzaza', 443);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (1, 'Azuay', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (32, 'Bolívar', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (47, 'Cañar', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (60, 'Carchi', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (73, 'Chimborazo', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (94, 'Cotopaxi', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (109, 'El Oro', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (138, 'Esmeraldas', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (151, 'Galápagos', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (158, 'Guayas', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (209, 'Imbabura', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (222, 'Loja', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (253, 'Los Ríos', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (278, 'Manabí', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (323, 'Morona Santiago', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (346, 'Napo', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (357, 'Orellana', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (365, 'Pastaza', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (373, 'Pichincha', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (388, 'Santa Elena', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (393, 'Santo Domingo de los Tsáchilas', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (396, 'Sucumbíos', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (410, 'Tungurahua', 0);
INSERT INTO ciudad (id, nombre, id_padre) VALUES (429, 'Zamora Chinchipe', 0);


--
-- TOC entry 2910 (class 0 OID 53090)
-- Dependencies: 179
-- Data for Name: ciudadano; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2911 (class 0 OID 53099)
-- Dependencies: 180
-- Data for Name: ciudadano_tmp; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2912 (class 0 OID 53106)
-- Dependencies: 181
-- Data for Name: codificacion; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO codificacion (cod_codi, cod_descripcion, inst_codi) VALUES (0, 'Sin tipificación', 0);


--
-- TOC entry 2916 (class 0 OID 53156)
-- Dependencies: 190
-- Data for Name: contenido; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO contenido (cont_codi, cont_tipo_codi, descripcion, texto, fecha_crea, fecha_actualiza) VALUES (1, 1, 'Implantación del Sistema', '<br />
<p>
	El Sistema &quot;Quipux&quot; es un servicio web que la Presidencia de la Rep&uacute;blica pone a disposici&oacute;n de las instituciones del sector p&uacute;blico.<br />
	<br />
	Para solicitar el acceso al sistema se debe:<br />
	<br />
	- Enviar un oficio solicitando la creaci&oacute;n de la cuenta institucional en el sistema, dirigido al Subsecretario de Tecnolog&iacute;as de Informaci&oacute;n.<br />
	<br />
	- Nombrar a un administrador institucional, el cual se har&aacute; cargo de la administraci&oacute;n del sistema en la instituci&oacute;n.<br />
	<br />
	El uso del sistema no tiene ning&uacute;n costo para la instituci&oacute;n. Vea qu&eacute; Instituciones est&aacute;n utilizando el sistema <a href="http://www.gobiernoelectronico.gob.ec/index.php/component/reporte_usuarios_quipux/?Itemid=240" target="_blank">aqu&iacute;.</a></p>', '2012-08-06 17:21:57.669484-05', '2014-02-07 17:04:16.598829-05');
INSERT INTO contenido (cont_codi, cont_tipo_codi, descripcion, texto, fecha_crea, fecha_actualiza) VALUES (2, 2, 'Procedimientos y Manuales', '<br />
<p>
	Para&nbsp; usar de forma correcta las funcionalidades que brinda el sistema Quipux, se recomienda revisar los siguientes documentos:<br />
	<br />
	1. Ciudadanos<br />
	<br />
	&nbsp;&nbsp;&nbsp;&nbsp; <a href="http://www.gobiernoelectronico.gob.ec/files/ManualCiudadanoSGDQ_201203.pdf" target="_blank">Manual de usuario para ciudadanos con firma electr&oacute;nica</a><br />
	<br />
	2. Servidores P&uacute;blicos<br />
	&nbsp;&nbsp;&nbsp;&nbsp; <a href="http://www.gobiernoelectronico.gob.ec/accordion-b/imp" target="_blank">Parametrizaci&oacute;n del sistema</a><br />
	&nbsp;&nbsp;&nbsp;&nbsp; <a href="http://www.gobiernoelectronico.gob.ec/accordion-b/descargas" target="_blank">Manual de Bandeja de Entrada</a><br />
	&nbsp;&nbsp;&nbsp;&nbsp; <a href="http://www.gobiernoelectronico.gob.ec/accordion-b/descargas" target="_blank">Manual de Bandeja de Salida y Tareas</a><br />
	&nbsp;&nbsp;&nbsp;&nbsp; <a href="http://www.gobiernoelectronico.gob.ec/accordion-b/descargas" target="_blank">Manual de Administraci&oacute;n</a><br />
	&nbsp;&nbsp;&nbsp;&nbsp; <a href="http://www.gobiernoelectronico.gob.ec/accordion-b/descargas" target="_blank">Instructivo para obtenci&oacute;n de respaldos</a> &nbsp;&nbsp;&nbsp;&nbsp;<br />
	&nbsp;&nbsp;&nbsp;&nbsp; <a href="http://youtu.be/mwQ5hw5s8tU" target="_blank">Video para restablecer contrase&ntilde;a</a> &nbsp;&nbsp;&nbsp;&nbsp;<br />
	&nbsp;&nbsp;&nbsp;&nbsp; <a href="http://www.gobiernoelectronico.gob.ec/files/Manual_Firma_51_v1_0.pdf" target="_blank">Manual de Firma Electr&oacute;nica</a></p>
<p>
	&nbsp;</p>', '2012-08-06 17:22:03.403744-05', '2014-02-07 17:07:17.785115-05');
INSERT INTO contenido (cont_codi, cont_tipo_codi, descripcion, texto, fecha_crea, fecha_actualiza) VALUES (3, 3, 'Ayuda, Soporte y Capacitación', '<br />
<p>
	Para cualquier duda o solicitud de nuevos requerimientos, enviar un correo al administrador institucional del sistema.<br />
	<br />
	Si olvid&oacute; la contrase&ntilde;a podr&aacute; recuperarla siguiendo los siguientes pasos:<br />
	<br />
	1.- Ingresar a cap.gestiondocumental.gob.ec y dar clic en el bot&oacute;n &quot;Ingresar al Sistema&quot;.<br />
	2.- Clic en el link &quot;&iquest;Olvid&oacute; su contrase&ntilde;a?&quot;, disponible en la parte inferior de la pantalla de&nbsp;&nbsp;<br />
	&nbsp;&nbsp;&nbsp;&nbsp; autenticaci&oacute;n al sistema.<br />
	3.- Ingresar el n&uacute;mero de c&eacute;dula y el c&oacute;digo que se muestra, dar clic en &quot;Aceptar&quot;.<br />
	4.- A continuaci&oacute;n el sistema enviar&aacute; un link, para el registro de la contrase&ntilde;a a su correo electr&oacute;nico&nbsp;<br />
	&nbsp;&nbsp;&nbsp;&nbsp; registrado.<br />
	<br />
	Para horarios de capacitaci&oacute;n visite la p&aacute;gina <a href="http://www.gobiernoelectronico.gob.ec/accordion-b/soporte" target="_blank">aqu&iacute;</a></p>', '2012-08-06 17:22:09.562414-05', '2014-02-07 17:07:48.238943-05');
INSERT INTO contenido (cont_codi, cont_tipo_codi, descripcion, texto, fecha_crea, fecha_actualiza) VALUES (4, 4, 'Contenido de Ayuda', '<table align="center" bgcolor="#a8bac6" border="0" width="80%">
	<tbody>
		<tr>
			<td>
				<center>
					<p>
						<b><span><font face="Verdana,Arial,Helvetica,sans-serif" size="2">SOPORTE A USUARIOS DEL SISTEMA<br />
						Subsecretar&iacute;a de Tecnolog&iacute;as de la Informaci&oacute;n </font></span></b></p>
				</center>
			</td>
		</tr>
	</tbody>
</table>
<table align="center" bgcolor="#e3e8ec" border="0" width="80%">
	<tbody>
		<tr>
			<td>
				<font color="#086478" face="Verdana,Arial,Helvetica,sans-serif" size="2"><b>Manuales de Usuario:</b></font><br />
				<br />
				<a href="http://sge.administracionpublica.gob.ec/files/ManualBandejaSalidaSGDQ_201203.pdf" target="Manual Bandeja de Salida"><font face="Verdana,Arial,Helvetica,sans-serif" size="2">Descargar Manual Bandeja de Salida </font></a></td>
		</tr>
		<tr>
			<td>
				<a href="http://sge.administracionpublica.gob.ec/files/ManualBandejaEntradaSGDQ_201204.pdf" target="Manual Bandeja de Entrada"><font face="Verdana,Arial,Helvetica,sans-serif" size="2">Descargar Manual Bandeja de Entrada </font></a></td>
		</tr>
		<tr>
			<td>
				&nbsp;</td>
		</tr>
		<tr>
			<td>
				<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Para problemas o incidentes del sistema, por favor comunicarse al siguiente correo electr&oacute;nico:</font></td>
		</tr>
		<tr>
			<td>
				&nbsp;</td>
		</tr>
		<tr>
			<td>
				<center>
					<a href="mailto:**cuenta_correo**">**cuenta_correo**</a></center>
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;</td>
		</tr>
		<tr>
			<td>
				<font color="#086478" face="Verdana,Arial,Helvetica,sans-serif" size="2"><b>Con los siguentes datos:</b></font></td>
		</tr>
		<tr>
			<td>
				<font face="Verdana,Arial,Helvetica,sans-serif" size="2">- Nombre de la Instituci&oacute;n</font></td>
		</tr>
		<tr>
			<td>
				<font face="Verdana,Arial,Helvetica,sans-serif" size="2">- Nombre Completo</font></td>
		</tr>
		<tr>
			<td>
				<font face="Verdana,Arial,Helvetica,sans-serif" size="2">- Cargo</font></td>
		</tr>
		<tr>
			<td>
				<font face="Verdana,Arial,Helvetica,sans-serif" size="2">- Una descripci&oacute;n concisa y precisa sobre el problema (Si es posible enviar como adjunto pantallas que muestren el error)</font></td>
		</tr>
		<tr>
			<td>
				<br />
				<br />
				<font color="#086478" face="Verdana,Arial,Helvetica,sans-serif" size="2"><b>Requerimientos del Sistema:</b></font><br />
				<br />
				<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Hardware </font>
				<ul>
					<li>
						<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Procesador: 2000MHz de velocidad por CPU m&iacute;nimo</font></li>
					<li>
						<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Espacio en disco: 600MB libre m&iacute;nimo, recomendado 1GB</font></li>
					<li>
						<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Memoria f&iacute;sica (RAM): 1GB m&iacute;nimo, 2GB recomendado</font></li>
					<li>
						<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Adaptador de video: 256 colores m&iacute;nimo</font></li>
					<li>
						<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Dispositivo apuntador o rat&oacute;n</font></li>
					<li>
						<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Enlace de acceso a la red Internet de 64kbps m&iacute;nimo</font></li>
					<li>
						<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Dispositivo Token USB de firma digital (solo para funcionarios autorizados)</font></li>
					<li>
						<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Scaner de alta velocidad A4 (para digitalizaci&oacute;n documentos entrada)</font></li>
				</ul>
				<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Software </font>
				<ul>
					<li>
						<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Instalaci&oacute;n programa navegador Mozilla Firefox vesi&oacute;n 3 o superior.</font></li>
				</ul>
				<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Para Firma Digital: </font>
				<ul>
					<li>
						<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Instalaci&oacute;n del programa manejador (driver) del token USB para Microsoft Windows XP o superiormendado</font></li>
					<li>
						<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Sistema operativo: Microsoft Windows XP o superior</font></li>
					<li>
						<font face="Verdana,Arial,Helvetica,sans-serif" size="2">Instalaci&oacute;n y funcionamiento apropiado del programa Maquina Virtual de Java (JVM) versi&oacute;n 1.5</font></li>
				</ul>
			</td>
		</tr>
		<tr>
			<td>
				<font color="#086478" face="Verdana,Arial,Helvetica,sans-serif" size="2"><b>Informaci&oacute;n General:</b></font> <font face="Verdana,Arial,Helvetica,sans-serif" size="2"> Sistema de Gesti&oacute;n Documental se desarrolla y mantiene con el personal de la Subsecretar&iacute;a de Tecnolog&iacute;as de la Informaci&oacute;n. Inicialmente en el 2007 se bas&oacute; en el sistema Orfeo, en su primera versi&oacute;n se adapt&oacute; las necesidades de las instituciones. En el 2008 se inici&oacute; un desarrollo nuevo para cubrir con las necesidades de los usuarios en relaci&oacute;n al &aacute;mbito de Gesti&oacute;n de Documentos. Hasta la fecha se han generado 15 revisiones del sistema y se est&aacute; en continuo cambio.</font></td>
		</tr>
	</tbody>
</table>
<br />', '2012-08-06 17:22:27.451633-05', '2013-09-30 13:05:53.443456-05');


--
-- TOC entry 2917 (class 0 OID 53163)
-- Dependencies: 191
-- Data for Name: contenido_tipo; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO contenido_tipo (cont_tipo_codi, funcionalidad, categoria) VALUES (1, 'Index', 'Implantación');
INSERT INTO contenido_tipo (cont_tipo_codi, funcionalidad, categoria) VALUES (2, 'Index', 'Procedimientos');
INSERT INTO contenido_tipo (cont_tipo_codi, funcionalidad, categoria) VALUES (3, 'Index', 'Soporte');
INSERT INTO contenido_tipo (cont_tipo_codi, funcionalidad, categoria) VALUES (4, 'Ayuda', 'Soporte');


--
-- TOC entry 2919 (class 0 OID 53193)
-- Dependencies: 195
-- Data for Name: dependencia; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO dependencia (depe_codi, depe_nomb, depe_codi_padre, dep_sigla, dep_central, dep_direccion, depe_estado, inst_codi, depe_plantilla, depe_pie1, depe_pie2, depe_pie3, inst_adscrita) VALUES (1, 'Ciudadanos', 1, 'CIU', 1, NULL, 1, 1, 1, '0', NULL, NULL, 1);


--
-- TOC entry 2922 (class 0 OID 53228)
-- Dependencies: 200
-- Data for Name: estado; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO estado (esta_codi, esta_desc) VALUES (1, 'En Edicion');
INSERT INTO estado (esta_codi, esta_desc) VALUES (2, 'En Tramite');
INSERT INTO estado (esta_codi, esta_desc) VALUES (4, 'No Enviado (Electrónicamente)');
INSERT INTO estado (esta_codi, esta_desc) VALUES (5, 'No Enviado (Manualmente)');
INSERT INTO estado (esta_codi, esta_desc) VALUES (6, 'Enviado');
INSERT INTO estado (esta_codi, esta_desc) VALUES (7, 'Eliminado');
INSERT INTO estado (esta_codi, esta_desc) VALUES (0, 'Archivado');
INSERT INTO estado (esta_codi, esta_desc) VALUES (3, 'No Enviado (Original)');
INSERT INTO estado (esta_codi, esta_desc) VALUES (8, 'Eliminado Total');
INSERT INTO estado (esta_codi, esta_desc) VALUES (9, 'Pendientes ciudadanos');


--
-- TOC entry 2923 (class 0 OID 53231)
-- Dependencies: 201
-- Data for Name: formato_numeracion; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2924 (class 0 OID 53238)
-- Dependencies: 202
-- Data for Name: hist_envio_fisico; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2913 (class 0 OID 53113)
-- Dependencies: 184
-- Data for Name: hist_eventos; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2925 (class 0 OID 53247)
-- Dependencies: 204
-- Data for Name: hist_opc_impresion; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2914 (class 0 OID 53122)
-- Dependencies: 186
-- Data for Name: informados; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2920 (class 0 OID 53199)
-- Dependencies: 196
-- Data for Name: institucion; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO institucion (inst_ruc, inst_nombre, inst_logo, inst_sigla, inst_pie1, inst_pie2, inst_pie3, inst_codi, inst_estado, inst_coordinador, inst_telefono, inst_despedida_ofi, inst_email, inst_ws_wsdl, inst_ws_usuario, inst_ws_contrasena) VALUES ('0000000000000', 'Ciudadanos', NULL, 'CIUDADANO', NULL, NULL, NULL, 1, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL);


--
-- TOC entry 2926 (class 0 OID 53260)
-- Dependencies: 206
-- Data for Name: institucion_org; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2927 (class 0 OID 53277)
-- Dependencies: 209
-- Data for Name: lista; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2928 (class 0 OID 53283)
-- Dependencies: 210
-- Data for Name: lista_usuarios; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2929 (class 0 OID 53288)
-- Dependencies: 212
-- Data for Name: log; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2930 (class 0 OID 53297)
-- Dependencies: 214
-- Data for Name: log_acceso; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2931 (class 0 OID 53309)
-- Dependencies: 215
-- Data for Name: log_archivo_descarga; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2932 (class 0 OID 53317)
-- Dependencies: 218
-- Data for Name: log_bloqueos_dos; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2933 (class 0 OID 53326)
-- Dependencies: 220
-- Data for Name: log_full_backup; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2934 (class 0 OID 53344)
-- Dependencies: 222
-- Data for Name: log_matar_procesos_servidores; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2935 (class 0 OID 53351)
-- Dependencies: 225
-- Data for Name: log_paginas_visitadas; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2936 (class 0 OID 53358)
-- Dependencies: 226
-- Data for Name: log_sesion; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2937 (class 0 OID 53361)
-- Dependencies: 227
-- Data for Name: log_tiempo_ws_firma; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2938 (class 0 OID 53368)
-- Dependencies: 230
-- Data for Name: log_usr_ciudadanos; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2939 (class 0 OID 53375)
-- Dependencies: 231
-- Data for Name: log_usr_permisos; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2940 (class 0 OID 53379)
-- Dependencies: 232
-- Data for Name: log_view_usuario; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2941 (class 0 OID 53390)
-- Dependencies: 235
-- Data for Name: mail_notificacion; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2942 (class 0 OID 53398)
-- Dependencies: 236
-- Data for Name: metadatos; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO metadatos (met_codi, met_padre, inst_codi, depe_codi, met_nombre, met_nivel, met_estado) VALUES (0, 0, 0, NULL, '', 0, 1);


--
-- TOC entry 2943 (class 0 OID 53405)
-- Dependencies: 238
-- Data for Name: metadatos_radicado; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2944 (class 0 OID 53418)
-- Dependencies: 240
-- Data for Name: opciones_impresion; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2945 (class 0 OID 53438)
-- Dependencies: 242
-- Data for Name: opciones_impresion_sobre; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2946 (class 0 OID 53448)
-- Dependencies: 245
-- Data for Name: permiso; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (21, 'Enviar notificaciones al correo.', 1, 21, 'usua_perm_notifica', 'El sistema envía notificaciones sobre los documentos recibidos al correo electrónico del usuario', 0);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (1, 'Administrar Archivo', 1, 1, 'usua_admin_archivo', 'Muestra en el menú la opción para administración de archivos físicos: creación de organización física y ubicaciones físicas.', 4);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (3, 'Consultar Documentos', 1, 3, 'usua_perm_consulta', 'Permite al usuario consultar documentos que pertenecen a otros usuarios de la misma área o de áreas con menor jerarquía', 1);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (5, 'Bandeja de Entrada', 1, 5, 'ver_todos_docu', 'Visualiza todos los documentos de la institución, sin verificar en que nivel se encuentre y se activan las opciones "Ver Documentos del Area" y "Ver Documentos del Usuario', 3);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (28, 'Creación de Documentos de Entrada', 1, 28, 'usua_prad_tp2', 'Permite al usuario registrar documentos de entrada. Documentos que llegan a la institución de manera física, se registran y se digitalizan para que fluya internamente en la institución electrónicamente.', 3);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (19, 'Firma Digital', 1, 19, 'firma_digital', 'Define si el usuario puede firmar digitalmente los documentos. ', 2);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (29, 'Usuario Público', 1, 29, 'usua_publico', 'Permite al usuario ser visto desde otras áreas de una misma institución.', 1);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (18, 'Administración de Carpetas Virtuales', 1, 18, 'usua_perm_trd', 'Muestra en el menú la opción de administración de Carpetas Virtuales y tipificación documental.', 4);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (20, NULL, 0, 20, 'perm_20', NULL, 0);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (11, NULL, 0, 11, 'perm_11', NULL, 0);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (22, 'Redireccionar a edición al ingresar a un documento', 1, 22, 'usua_perm_redireccionar_edicion', 'Cuando el usuario accede a un documento en elaboración lo redirecciona automáticamente a la pantalla de edición.', 1);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (6, 'Recibir documentos externos dirigidos', 1, 6, 'perm_recibir_externo', 'Permite al usuario recibir directamente documentos externos dirigidos a otro usuario.', 2);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (4, 'Activar Acciones sobre Documentos', 1, 4, 'perm_acti_accion', 'Visualiza el combo de Acciones requeridas en la bandeja de Reasignados', 0);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (25, 'Solicitar respaldos de documentos', 0, 25, 'usua_perm_backup', 'Permite al usuario sacar respaldos de la documentación de otros usuarios.', 0);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (16, 'Creación de Ciudadanos', 1, 16, 'usua_perm_ciudadano', 'Permite a un usuario ingresar nuevos ciudadanos en el sistema, para el definirlos como destinatarios en sus documentos.', 3);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (15, 'Impresión de Documentos', 1, 14, 'usua_perm_impresion', 'Muestra en el menú la opción para imprimir los documentos que deberán ser enviados manualmente.', 1);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (13, 'Digitalizar Documentos', 1, 13, 'usua_perm_digitalizar', 'Muestra en el menú la opción para asociar documento digital (imágenes),  a los documentos registrados en la mesa de entrada. ', 3);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (12, 'Administración del Sistema', 1, 12, 'usua_admin_sistema', 'Muestra en el menú la opción de administrar el sistema: áreas, usuarios, lista de usuarios, numeración de documentos.', 4);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (27, 'Creación de documentos de Salida', 1, 27, 'usua_prad_tp1', 'Permite al usuario crear documentos de Salida. Documentos que salen de la institución a otra institución o a un ciudadano.', 0);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (17, 'Reportes', 1, 17, 'usua_perm_estadistica', 'Permite visualizar reportes estadísticos de documentos recibidos por los usuarios de la institución.', 2);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (2, 'Manejar el Archivo', 1, 2, 'usua_perm_archivo', 'Permite a los usuarios del archivo buscar y ubicar documentos en el archivo físico de la institución.', 1);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (30, 'Administración de Instituciones', 0, 30, 'admin_institucion', 'Permite crear nuevas instituciones.', 0);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (31, 'Informar a todos los usuarios', 1, 31, 'usua_perm_email_all', 'Permite informar a todos los usuarios activos de la institución', 4);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (9, 'Recibir documentos de ciudadanos', 1, 9, 'perm_recibir_ciudadano', 'Permite al usuario recibir documentos firmados electrónicamente por ciudadanos.', 2);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (10, 'Tramitar documentos de ciudadanos', 1, 10, 'perm_tramitar_docs_ciudadano', 'Permite al usuario ver la bandeja intermedia donde llegan los documentos firmados electrónicamente por ciudadanos.', 3);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (14, 'Confirmar datos ingresados por ciudadanos', 1, 15, 'perm_validar_ciudadano', 'Permite aceptar o rechazar los cambios en los datos personales ingresados por ciudadanos.', 5);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (32, 'Administrar Listas de la Institución', 1, 32, 'usua_perm_listas', 'Permite Editar las listas Públicas y Personales de la Institución', 4);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (35, 'Actualizar Sistema', 0, 35, 'perm_actualizar_sistema', 'Permite ejecutar los queries de actualización del sistema, cuando se requiere modificar muchos registros', 5);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (36, 'Administrador Institucional', 1, 36, 'perm_admin_institucional', 'Permite administrar todas las areas', 4);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (33, 'Aprobar Solicitudes de Respaldo', 0, 33, 'perm_aprobar_respaldo', 'Permite Aprobar la Solicitud de Respaldos de un usuarios', 4);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (23, 'Mostrar documento automáticamente en la información general.', 1, 23, 'usua_perm_mostrar_documento', 'Muestra el documento sin necesidad de hacer click en "Ver Documento" en la parte inferior de la pantalla de información.', 2);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (7, NULL, 0, 7, 'perm_7', NULL, 0);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (26, 'Omitir estructura orgánica funcional (Un usuario por institución)', 1, 26, 'perm_saltar_organico_funcional', 'Permite reasignar documentos a cualquier usuario público, saltandose la estructura orgánica funcional de la institución.', 2);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (8, NULL, 0, 8, 'perm_8', NULL, 0);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (34, NULL, 0, 34, 'perm_34', NULL, 5);
INSERT INTO permiso (id_permiso, descripcion, estado, orden, nombre, descripcion_larga, perfil) VALUES (24, NULL, 0, 24, 'perm_24', NULL, 5);


--
-- TOC entry 2947 (class 0 OID 53456)
-- Dependencies: 246
-- Data for Name: permiso_usuario; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO permiso_usuario (id_permiso, usua_codi) VALUES (1, 0);
INSERT INTO permiso_usuario (id_permiso, usua_codi) VALUES (2, 0);
INSERT INTO permiso_usuario (id_permiso, usua_codi) VALUES (3, 0);
INSERT INTO permiso_usuario (id_permiso, usua_codi) VALUES (12, 0);
INSERT INTO permiso_usuario (id_permiso, usua_codi) VALUES (16, 0);
INSERT INTO permiso_usuario (id_permiso, usua_codi) VALUES (18, 0);
INSERT INTO permiso_usuario (id_permiso, usua_codi) VALUES (21, 0);
INSERT INTO permiso_usuario (id_permiso, usua_codi) VALUES (30, 0);
INSERT INTO permiso_usuario (id_permiso, usua_codi) VALUES (32, 0);
INSERT INTO permiso_usuario (id_permiso, usua_codi) VALUES (33, 0);
INSERT INTO permiso_usuario (id_permiso, usua_codi) VALUES (35, 0);
INSERT INTO permiso_usuario (id_permiso, usua_codi) VALUES (36, 0);


--
-- TOC entry 2948 (class 0 OID 53459)
-- Dependencies: 247
-- Data for Name: permiso_usuario_dep; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2949 (class 0 OID 53468)
-- Dependencies: 248
-- Data for Name: radi_texto; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO radi_texto (text_codi, radi_nume_radi, text_fecha, text_texto) VALUES (0, NULL, '2009-05-04 11:39:20.805463-05', NULL);


--
-- TOC entry 2915 (class 0 OID 53130)
-- Dependencies: 187
-- Data for Name: radicado; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2950 (class 0 OID 53474)
-- Dependencies: 249
-- Data for Name: radicado_sec_temp; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2951 (class 0 OID 53486)
-- Dependencies: 251
-- Data for Name: respaldo_estado; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (1, 'En Edición', 'En Edición', '', 1, 1);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (3, 'Aprobada', 'Aprobada', '', 1, 1);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (6, 'Atendida', 'Atendida', '', 1, 1);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (8, 'Por Generar', 'Por Generar', '', 2, 1);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (9, 'En Ejecución', 'En Ejecución', '', 2, 1);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (12, 'Generado', 'Generado', '', 2, 1);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (13, 'Descargado', 'Descargado', '', 2, 1);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (15, 'Eliminado', 'Eliminado', '', 2, 1);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (14, 'Eliminada', 'Eliminada', '', 1, 1);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (5, 'Detenida', 'Detenida', '', 1, 0);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (7, 'Finalizada', 'Finalizada', '', 1, 0);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (4, 'Rechazada', 'Rechazada', '', 1, 0);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (11, 'Detenido', 'Detenido', '', 2, 0);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (10, 'Rechazado', 'Rechazado', '', 2, 0);
INSERT INTO respaldo_estado (est_codi, est_nombre, est_nombre_estado, est_desc, est_tipo, est_estado) VALUES (2, 'Enviada', 'Enviada', '', 1, 0);


--
-- TOC entry 2952 (class 0 OID 53493)
-- Dependencies: 253
-- Data for Name: respaldo_hist_eventos; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2953 (class 0 OID 53504)
-- Dependencies: 255
-- Data for Name: respaldo_solicitud; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2954 (class 0 OID 53515)
-- Dependencies: 257
-- Data for Name: respaldo_usuario; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2955 (class 0 OID 53522)
-- Dependencies: 259
-- Data for Name: respaldo_usuario_radicado; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2956 (class 0 OID 53568)
-- Dependencies: 276
-- Data for Name: sgd_ttr_transaccion; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (40, 'Firma Digital de Documento');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (23, 'Devolución de documentos');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (10, 'Tareas de administración');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (50, 'Asignar nueva tarea');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (51, 'Finalizar tarea');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (26, 'Asociación de documentos');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (8, 'Informar');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (28, 'Dirigir Documento');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (7, 'Borrar Informado');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (12, 'Responder');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (13, 'Archivar');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (14, 'Agendar');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (15, 'Sacar de la agenda');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (0, '--');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (2, 'Registro');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (27, 'Eliminar asociación de documentos');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (35, 'Revertir Firma Digital de Documento (error en firma)');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (70, 'Imprimir Sobre');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (30, 'Registro Masivo');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (36, 'Eliminar Documento Generado (error al generar PDF)');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (52, 'Cancelar tarea');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (53, 'Comentar tarea');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (54, 'Reabrir tarea');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (31, 'Borrado de Anexo');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (55, 'Editar tarea');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (56, 'Actualizar avance');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (57, 'Cambiar propietario de la tarea');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (39, 'Solicitud de Firma');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (37, 'Envío de Respuesta Firmada Electrónicamente');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (64, 'Ingreso al Archivo Central');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (65, 'Firmar y Enviar');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (66, 'Adjuntar Archivo');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (67, 'Responder');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (16, 'Eliminar Documento');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (17, 'Reestablecer Documento Eliminado');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (38, 'Envío de Respuesta Firmada Manualmente');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (19, 'Envío Manual del Documento');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (20, 'Ordenar Envío Manual del Documento');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (9, 'Reasignar');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (33, 'Eliminar Carpeta Virtual');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (21, 'Comentar Documento');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (25, 'Reestablecer Documento Archivado');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (69, 'Enviar Físico');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (18, 'Envío Electrónico del Documento');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (1, 'Recuperación Registro');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (11, 'Modificación Documento');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (22, 'Digitalización de Documento');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (29, 'Digitalización de Anexo');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (32, 'Asignación Carpeta Virtual');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (41, 'Eliminación solicitud de Firma Digital');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (42, 'Digitalización Documento(Asoc. Imagen Web)');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (63, 'Modificación al Archivo Central');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (24, 'Asociación Imagen fax');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (59, 'Cambiar propietario de tarea por inactivación de usuario');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (71, 'Solicitud creada');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (72, 'Solicitud enviada');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (73, 'Solicitud aprobada');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (74, 'Solicitud rechazada');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (75, 'Respaldo en ejecución');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (76, 'Respaldo detenido');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (77, 'Respaldo generado');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (82, 'Copiar');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (81, 'Solicitud modificada');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (78, 'Respaldo Físico Eliminado');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (79, 'Solicitud eliminada');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (80, 'Respaldo calendarizado');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (85, 'Descarga de respaldo');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (86, 'Solicitud de descarga');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (87, 'Eliminación automática de respaldo');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (84, 'Definición de metadatos');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (83, 'Recuperar Documento desde Reasignación');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (88, 'Incluir Documento en Carpeta Virtual');
INSERT INTO sgd_ttr_transaccion (sgd_ttr_codigo, sgd_ttr_descrip) VALUES (58, 'Generar respuesta al documento');


--
-- TOC entry 2918 (class 0 OID 53176)
-- Dependencies: 193
-- Data for Name: solicitud_firma_ciudadano; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2957 (class 0 OID 53573)
-- Dependencies: 278
-- Data for Name: tarea; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2958 (class 0 OID 53577)
-- Dependencies: 279
-- Data for Name: tarea_hist_eventos; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2959 (class 0 OID 53584)
-- Dependencies: 280
-- Data for Name: tarea_radi_respuesta; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2960 (class 0 OID 53600)
-- Dependencies: 282
-- Data for Name: tipo_certificado; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO tipo_certificado (tipo_cert_codi, descripcion, estado) VALUES (0, 'Sin certificado', 1);
INSERT INTO tipo_certificado (tipo_cert_codi, descripcion, estado) VALUES (1, 'Token', 1);
INSERT INTO tipo_certificado (tipo_cert_codi, descripcion, estado) VALUES (2, 'Archivo', 1);
INSERT INTO tipo_certificado (tipo_cert_codi, descripcion, estado) VALUES (3, 'Biométrico', 1);


--
-- TOC entry 2961 (class 0 OID 53604)
-- Dependencies: 283
-- Data for Name: tiporad; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO tiporad (trad_codigo, trad_descr, trad_tipo, trad_inst_codi, trad_estado, trad_abreviatura, trad_opc_impresion, trad_texto_inicio, trad_formato, trad_formato_tipo) VALUES (2, 'Externo', 'E', 0, 1, 'E', NULL, NULL, NULL, 0);
INSERT INTO tiporad (trad_codigo, trad_descr, trad_tipo, trad_inst_codi, trad_estado, trad_abreviatura, trad_opc_impresion, trad_texto_inicio, trad_formato, trad_formato_tipo) VALUES (7, 'Carta Ciudadano', 'S', 1, 1, NULL, NULL, NULL, '**QUIPUX_DATOS_DOC_ASUNTO**
**QUIPUX_DATOS_DOC_DESTINATARIO**
**QUIPUX_DATOS_DOC_LUGAR_DESTINATARIO**
**QUIPUX_DATOS_DOC_CUERPO**
<br>
**QUIPUX_DATOS_DOC_DESPEDIDA**
&nbsp;<br>
**QUIPUX_DATOS_DOC_FIRMA_DIGITAL**
**QUIPUX_DATOS_DOC_REMITENTE**
**QUIPUX_DATOS_DOC_LINEAS_ESPECIALES**', 1);
INSERT INTO tiporad (trad_codigo, trad_descr, trad_tipo, trad_inst_codi, trad_estado, trad_abreviatura, trad_opc_impresion, trad_texto_inicio, trad_formato, trad_formato_tipo) VALUES (4, 'Circular', 'S', 0, 1, 'C', 'opc_imp_justificar_firma,opc_imp_frase_remitente,opc_imp_despedida,opc_imp_ocultar_frase_rem,opc_imp_ocultar_atentamente,opc_imp_ocultar_anexo,opc_imp_ocultar_referencia,opc_imp_ocultar_sumillas', NULL, '**QUIPUX_DATOS_DOC_ASUNTO**
**QUIPUX_DATOS_DOC_DESTINATARIO**<br>
**QUIPUX_DATOS_DOC_CUERPO**
<br>
**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_INICIO**
**QUIPUX_DATOS_DOC_DESPEDIDA**
<b>**QUIPUX_DATOS_DOC_FRASE_REMITENTE**</b>
&nbsp;<br>
**QUIPUX_DATOS_DOC_FIRMA_DIGITAL**
**QUIPUX_DATOS_DOC_REMITENTE**
**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_FIN**
**QUIPUX_DATOS_DOC_LINEAS_ESPECIALES**', 1);
INSERT INTO tiporad (trad_codigo, trad_descr, trad_tipo, trad_inst_codi, trad_estado, trad_abreviatura, trad_opc_impresion, trad_texto_inicio, trad_formato, trad_formato_tipo) VALUES (3, 'Memorando', 'S', 0, 1, 'M', 'opc_imp_justificar_firma,opc_imp_frase_remitente,opc_imp_despedida,opc_imp_ocultar_frase_rem,opc_imp_ocultar_atentamente,opc_imp_ocultar_anexo,opc_imp_ocultar_referencia,opc_imp_ocultar_sumillas', NULL, '**QUIPUX_DATOS_DOC_DESTINATARIO**
<table border="0" cellpadding="0" cellspacing="0" width="800px">
    <tr>
        <td width="120px" valign="top"><b>ASUNTO:</b></td>
        <td width="680px" valign="top">**QUIPUX_DATOS_DOC_ASUNTO**</td>
    </tr>
</table>
&nbsp;<br>
**QUIPUX_DATOS_DOC_CUERPO**
<br>
**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_INICIO**
**QUIPUX_DATOS_DOC_DESPEDIDA**
<b>**QUIPUX_DATOS_DOC_FRASE_REMITENTE**</b>
&nbsp;<br>
**QUIPUX_DATOS_DOC_FIRMA_DIGITAL**
**QUIPUX_DATOS_DOC_REMITENTE**
**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_FIN**
**QUIPUX_DATOS_DOC_LINEAS_ESPECIALES**', 2);
INSERT INTO tiporad (trad_codigo, trad_descr, trad_tipo, trad_inst_codi, trad_estado, trad_abreviatura, trad_opc_impresion, trad_texto_inicio, trad_formato, trad_formato_tipo) VALUES (8, 'Resolución', 'S', 0, 1, 'R', 'opc_imp_justificar_firma,opc_imp_frase_remitente,opc_imp_despedida,opc_imp_ocultar_frase_rem,opc_imp_ocultar_atentamente,opc_imp_ocultar_anexo,opc_imp_ocultar_referencia,opc_imp_ocultar_sumillas', NULL, '<center><b>**QUIPUX_DATOS_DOC_REMITENTE_INSTITUCION**</b></center>
&nbsp;<br>
**QUIPUX_DATOS_DOC_CUERPO**
<br>
**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_INICIO**
**QUIPUX_DATOS_DOC_FIRMA_DIGITAL**
**QUIPUX_DATOS_DOC_REMITENTE**
**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_FIN**
**QUIPUX_DATOS_DOC_LINEAS_ESPECIALES**', 4);
INSERT INTO tiporad (trad_codigo, trad_descr, trad_tipo, trad_inst_codi, trad_estado, trad_abreviatura, trad_opc_impresion, trad_texto_inicio, trad_formato, trad_formato_tipo) VALUES (1, 'Oficio', 'S', 0, 1, 'O', 'opc_imp_ocultar_nume_radi,opc_imp_mostrar_para,opc_imp_justificar_firma,opc_imp_titulo_natural,opc_imp_ext_institucion,opc_imp_destino_destinatario,opc_imp_frase_remitente,opc_imp_despedida,opc_imp_firmantes,opc_imp_ocultar_frase_rem,opc_imp_cargo_cabecera,opc_imp_letra_italica,opc_imp_ocultar_asunto,opc_imp_ocultar_atentamente,opc_imp_ocultar_anexo,opc_imp_ocultar_referencia,opc_imp_ocultar_sumillas', 'De mi consideraci&oacute;n:<br>
<br>
<br><br>
Con sentimientos de distinguida consideraci&oacute;n.', '**QUIPUX_DATOS_DOC_ASUNTO**
**QUIPUX_DATOS_DOC_DESTINATARIO**
**QUIPUX_DATOS_DOC_LUGAR_DESTINATARIO**
**QUIPUX_DATOS_DOC_CUERPO**
<br>
**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_INICIO**
**QUIPUX_DATOS_DOC_DESPEDIDA**
<b>**QUIPUX_DATOS_DOC_FRASE_REMITENTE**</b>
&nbsp;<br>
**QUIPUX_DATOS_DOC_FIRMA_DIGITAL**
**QUIPUX_DATOS_DOC_REMITENTE**
**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_FIN**
**QUIPUX_DATOS_DOC_DESTINATARIO_AL_PIE**
**QUIPUX_DATOS_DOC_LINEAS_ESPECIALES**', 1);
INSERT INTO tiporad (trad_codigo, trad_descr, trad_tipo, trad_inst_codi, trad_estado, trad_abreviatura, trad_opc_impresion, trad_texto_inicio, trad_formato, trad_formato_tipo) VALUES (5, 'Acuerdo', 'S', 0, 1, 'A', 'opc_imp_ciudad_dado_en', '<center><b>CONSIDERANDO:</b></center>
<br />&nbsp;<br />
<b>Que,</b>&nbsp;<br />
<br />&nbsp;<br />
<b>Que,</b>&nbsp;<br />
&nbsp;<br />
&nbsp;<br />
<center>
    <b>ACUERDA:</b>
</center>
<br />
&nbsp;<br />
<b>Art&iacute;culo 1.-</b>&nbsp;<br />
<br />&nbsp;<br />
<b>Art&iacute;culo 2.-</b>&nbsp;<br />
<br />
<br />', '<center>
    <b>**QUIPUX_DATOS_DOC_NUMERO_DOCUMENTO**</b>
    &nbsp;<br>
    &nbsp;<br>
    <b>**QUIPUX_DATOS_DOC_REMITENTE_NOMBRE**</b>
    <br>
    <b>**QUIPUX_DATOS_DOC_REMITENTE_CARGO**</b>
    <br />&nbsp;<br />
</center>
**QUIPUX_DATOS_DOC_CUERPO**
**QUIPUX_DATOS_DOC_DADO_EN**
&nbsp;<br>
<center>
    **QUIPUX_DATOS_DOC_FIRMA_DIGITAL**
    <b>**QUIPUX_DATOS_DOC_REMITENTE_NOMBRE**</b>
    <br>
    <b>**QUIPUX_DATOS_DOC_REMITENTE_CARGO**</b>
</center>', 5);
INSERT INTO tiporad (trad_codigo, trad_descr, trad_tipo, trad_inst_codi, trad_estado, trad_abreviatura, trad_opc_impresion, trad_texto_inicio, trad_formato, trad_formato_tipo) VALUES (6, 'Nota', 'S', 0, 1, 'N', 'opc_imp_titulo_natural,opc_imp_ext_institucion,opc_imp_destino_destinatario,opc_imp_firmantes,opc_imp_cargo_cabecera,opc_imp_tipo_nota,opc_imp_letra_italica', '<center><b>CONSIDERANDO:</b></center>
<br>&nbsp;<br><br>&nbsp;<br><br>&nbsp;<br>
<center><b>RESUELVE:</b></center>
<br>&nbsp;<br><br>&nbsp;<br>
<center><b>DISPOSICIÓN FINAL</b></center>
<br>&nbsp;<br><br>&nbsp;<br>
Dada y firmada en el Despacho de **QUIPUX_DATOS_DOC_NOMBRE_INSTITUCION** 
en **QUIPUX_DATOS_DOC_REMITENTE_CIUDAD**, **QUIPIX_DATOS_DOC_FECHA_LARGA**', '**QUIPUX_DATOS_DOC_DESTINATARIO**
**QUIPUX_DATOS_DOC_CUERPO**
&nbsp;<br>
**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_INICIO**
**QUIPUX_DATOS_DOC_FIRMA_DIGITAL**
**QUIPUX_DATOS_DOC_REMITENTE**
**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_FIN**
**QUIPUX_DATOS_DOC_DESTINATARIO_AL_PIE**
**QUIPUX_DATOS_DOC_LINEAS_ESPECIALES**', 3);
INSERT INTO tiporad (trad_codigo, trad_descr, trad_tipo, trad_inst_codi, trad_estado, trad_abreviatura, trad_opc_impresion, trad_texto_inicio, trad_formato, trad_formato_tipo) VALUES (9, 'Providencia', 'S', 0, 1, 'P', 'opc_imp_justificar_firma,opc_imp_frase_remitente,opc_imp_despedida,opc_imp_ocultar_frase_rem,opc_imp_ocultar_atentamente,opc_imp_ocultar_anexo,opc_imp_ocultar_referencia,opc_imp_ocultar_sumillas', NULL, '**QUIPUX_DATOS_DOC_CUERPO**
<br>
**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_INICIO**
**QUIPUX_DATOS_DOC_FIRMA_DIGITAL**
**QUIPUX_DATOS_DOC_REMITENTE**
**QUIPUX_DATOS_DOC_JUSTIFICAR_FIRMA_FIN**
**QUIPUX_DATOS_DOC_LINEAS_ESPECIALES**', 4);


--
-- TOC entry 2962 (class 0 OID 53613)
-- Dependencies: 284
-- Data for Name: titulo; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (126, 'Señor Embajador', 'Sr. Emb.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (127, 'Señora Embajadora', 'Sra. Emb.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (13, 'Señorita Ingeniera', 'Srta. Ing.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (59, 'Señor Conscripto', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (9, 'Señorita Secretaria', 'Srta.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (130, 'Señora Teniente Plto. Avc.', 'Sra. Tnte. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (131, 'Señora Subteniente', 'Sra. Subt.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (132, 'Señora Cabo Primero', 'Sra. Cbop.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (17, 'Señorita Arquitecta', 'Srta. Arq.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (135, 'Señor Coronel EMC. Avc.', 'Sr. Crnl. EMC. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (136, 'Señor Coronel EM. Avc.', 'Sr. Crnl. EM. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (137, 'Señor Coronel EMT. Avc.', 'Sr. Crnl. EMT. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (138, 'Señor Coronel CSM. Avc.', 'Sr. Crnl. CSM. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (139, 'Señor Teniente Coronel EM. Avc.', 'Sr. TCrn. EM. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (140, 'Señor Teniente Coronel EMT. Avc.', 'Sr. TCrn. EMT. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (141, 'Señor Teniente Coronel CSM. Avc.', 'Sr. TCrn. CSM. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (142, 'Señor Mayor Plto. Avc.', 'Sr. Mayo. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (143, 'Señor Mayor Téc. Avc.', 'Sr. Mayo. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (144, 'Señor Mayor Esp. Avc.', 'Sr. Mayo. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (145, 'Señora Mayor Téc. Avc.', 'Sra. Mayo. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (146, 'Señora Mayor Plto. Avc.', 'Sra. Mayo. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (147, 'Señora Mayor Esp. Avc.', 'Sra. Mayo. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (148, 'Señor Capitán Plto. Avc.', 'Sr. Capt. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (116, 'Señor General de Distrito (sp)', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (149, 'Señor Capitán Téc. Avc.', 'Sr. Capt. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (150, 'Señor Capitán Esp. Avc.', 'Sr. Capt. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (151, 'Señora Capitán Plto. Avc.', 'Sra. Capt. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (152, 'Señora Capitán Téc. Avc.', 'Sra. Capt. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (153, 'Señora Capitán Esp. Avc.', 'Sra. Capt. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (5, 'Señor Licenciado', 'Sr. Lcdo.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (208, 'Señor Químico', 'Sr. Quim.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (19, 'Señorita Economista', 'Srta. Econ.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (117, 'Señor General Inspector (sp)', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (21, 'Señorita Licenciada', 'Srta. Lcda.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (157, 'Señor Teniente Plto. Avc.', 'Sr. Tnte. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (232, 'Señor Meteorólogo', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (158, 'Señor Teniente Téc. Avc.', 'Sr. Tnte. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (159, 'Señor Teniente Esp. Avc.', 'Sr. Tnte. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (160, 'Señora Teniente Téc. Avc.', 'Sra. Tnte. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (161, 'Señora Teniente Esp. Avc.', 'Sra. Tnte. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (209, 'Señora Química', 'Sra. Quim.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (15, 'Señorita Abogada', 'Srta. Abg.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (118, 'Señor General Superior (sp)', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (87, 'Señor Técnico', 'Sr. Téc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (32, 'Señorita', 'Srta.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (34, 'Señora', 'Sra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (27, 'Señorita Doctora', 'Srta. Dra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (250, 'Señor Médico Veterinario', 'Sr. Mv.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (30, 'Señorita Magíster', 'Srta. Mgs.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (165, 'Señor Subteniente Plto. Avc.', 'Sr. Subt. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (210, 'Señorita Química', 'Srta. Quim');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (41, 'Señorita Capitán', 'Srta. Capt.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (10, 'Señora Secretaria', 'Sra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (166, 'Señor Subteniente Téc. Avc.', 'Sr. Subt. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (167, 'Señor Subteniente Esp. Avc.', 'Sr. Subt. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (168, 'Señora Subteniente Plto. Avc.', 'Sra. Subt. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (169, 'Señora Subteniente Téc. Avc.', 'Sra. Subt. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (170, 'Señora Subteniente Esp. Avc.', 'Sra. Subt. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (44, 'Señorita Teniente', 'Srta. Tnte.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (91, 'Monseñor', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (215, 'Señor Periodista', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (92, 'Cardenal', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (93, 'Sacerdote', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (85, 'Señores', 'Sres.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (46, 'Señorita Subteniente', 'Srta. Subt.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (50, 'Señorita Cadete', 'Srta. Kdte.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (174, 'Señor Cadete Plto. Avc.', 'Sr. Kdte. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (175, 'Señor Cadete Téc. Avc.', 'Sr. Kdte. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (26, 'Señor Secretario', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (97, 'Señor Profesor', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (252, 'Señorita Médico Veterinario', 'Srta. Mv.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (178, 'Señor Suboficial Mayor Avc.', 'Sr. Subm. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (179, 'Señor Suboficial Primero Téc. Avc.', 'Sr. Subp. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (180, 'Señor Suboficial Primero Esp. Avc.', 'Sr. Subp. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (98, 'Señora Profesora', 'Sra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (90, 'Señorita Cabo Segundo', 'Srta. Cbos.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (100, 'Señora Teniente', 'Sra. Tnte.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (96, 'Señorita Bióloga', 'Srta. Blga.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (99, 'Señorita Profesora', 'Srta.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (111, 'Señor General Inspector', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (108, 'Señor General de Distrito', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (112, 'Señor General Superior', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (109, 'Niño', 'Niño.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (110, 'Niña', 'Niña.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (181, 'Señor Suboficial Segundo Téc. Avc.', 'Sr. Subs. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (182, 'Señor Suboficial Segundo Esp. Avc.', 'Sr. Subs. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (183, 'Señor Sargento Primero Téc. Avc.', 'Sr. Sgop. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (184, 'Señor Sargento Primero Esp. Avc.', 'Sr. Sgop. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (185, 'Señor Sargento Segundo Téc. Avc.', 'Sr. Sgos. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (186, 'Señor Sargento Segundo Esp. Avc.', 'Sr. Sgos. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (187, 'Señor Cabo Primero Téc. Avc.', 'Sr. Cbop. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (188, 'Señor Cabo Primero Esp. Avc.', 'Sr. Cbop. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (189, 'Señora Cabo Primero Téc. Avc.', 'Sra. Cbop. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (190, 'Señora Cabo Primero Esp. Avc.', 'Sra. Cbop. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (106, 'Señorita Socióloga', 'Srta. Soc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (107, 'Reverenda Hermana', 'Srta.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (193, 'Señor Cabo Segundo Téc. Avc.', 'Sr. Cbos. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (194, 'Señor Cabo Segundo Esp. Avc.', 'Sr. Cbos. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (195, 'Señora Cabo Segundo Téc. Avc.', 'Sra. Cbos. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (196, 'Señora Cabo Segundo Esp. Avc.', 'Sra. Cbos. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (122, 'Señorita Antropóloga', 'Srta. Antrop.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (125, 'Señorita Matemática', 'Srta. Mat.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (22, 'Señora Tecnóloga', 'Sra. Tlga.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (206, 'Señora Bioquímica Farmaceútica', 'Sra. BQF.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (199, 'Señor Soldado Téc. Avc.', 'Sr. Sldo. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (200, 'Señor Soldado Esp. Avc.', 'Sr. Sldo. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (77, 'Señora Auditora', 'Sra. Aud.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (76, 'Señor Auditor', 'Sr. Aud.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (270, 'Señor Oceonógrafo', 'Sr. Oc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (272, 'Señorita Oceanógrafa', 'Srta. Oc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (128, 'Señorita Embajadora', 'Srta. Emb.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (129, 'Señorita Mayor', 'Srta. Mayo.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (203, 'Señor Marinero', 'Sr. MARO.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (204, 'Señor Grumete', 'Sr. GRUM.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (72, 'Señor Guardiamarina', 'Sr. GAMA.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (86, 'Señor Piloto', 'Sr. Plto.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (64, 'Señor Vicealmirante', 'Sr. VALM.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (211, 'Señor Mayor Ingeniero', 'Sr. Mayo. Ing.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (63, 'Señor Almirante', 'Sr. ALM.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (133, 'Señorita Cabo Primero', 'Srta. Cbop.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (94, 'Señor Biólogo', 'Sr. Blgo.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (95, 'Señora Bióloga', 'Sra. Blga.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (134, 'Señorita Soldado', 'Srta. Sldo.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (1, 'Señor Abogado', 'Sr. Abg.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (2, 'Señor Ingeniero', 'Sr. Ing.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (3, 'Señor Arquitecto', 'Sr. Arq.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (4, 'Señor Economista', 'Sr. Econ.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (7, 'Señor Doctor', 'Sr. Dr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (11, 'Señora Doctora', 'Sra. Dra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (12, 'Señora Ingeniera', 'Sra. Ing.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (154, 'Señorita Capitán Plto. Avc.', 'Srta. Capt. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (14, 'Señora Abogada', 'Sra. Abg.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (155, 'Señorita Capitán Téc. Avc.', 'Srta. Capt. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (16, 'Señora Arquitecta', 'Sra. Arq.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (156, 'Señorita Capitán Esp. Avc.', 'Srta. Capt. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (18, 'Señora Economista', 'Sra. Econ.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (162, 'Señorita Teniente Plto. Avc.', 'Srta. Tnte. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (20, 'Señora Licenciada', 'Sra. Lcda.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (163, 'Señorita Teniente Téc. Avc.', 'Srta. Tnte. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (164, 'Señorita Teniente Esp. Avc.', 'Srta. Tnte. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (171, 'Señorita Subteniente Plto. Avc.', 'Srta. Subt. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (172, 'Señorita Subteniente Téc. Avc.', 'Srta. Subt. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (28, 'Señor Magíster', 'Sr. Mgs.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (29, 'Señora Magíster', 'Sra. Mgs.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (173, 'Señorita Subteniente Esp. Avc.', 'Srta. Subt. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (35, 'Señor General de Ejército', 'Sr. Grae.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (36, 'Señor Coronel', 'Sr. Crnl.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (37, 'Señor Teniente Coronel', 'Sr. TCrn.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (38, 'Señor Mayor', 'Sr. Mayo.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (39, 'Señora Mayor', 'Sra. Mayo.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (40, 'Señor Capitán', 'Sr. Capt.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (176, 'Señorita Cadete Plto. Avc.', 'Srta. Kdte. Plto. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (42, 'Señora Capitán', 'Sra. Capt.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (43, 'Señor Teniente', 'Sr. Tnte.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (177, 'Señorita Cadete Téc. Avc.', 'Srta. Kdte. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (45, 'Señor Subteniente', 'Sr. Subt.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (191, 'Señorita Cabo Primero Téc. Avc.', 'Srta. Cbop. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (47, 'Señor General de División', 'Sr. Grad.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (48, 'Señor General de Brigada', 'Sr. Grab.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (49, 'Señor Cadete', 'Sr. Kdte.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (51, 'Señor Suboficial Mayor', 'Sr. SUBM.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (52, 'Señor Suboficial Primero', 'Sr. SUBP.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (53, 'Señor Suboficial Segundo', 'Sr. SUBS.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (54, 'Señor Sargento Primero', 'Sr. SGOP.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (55, 'Señor Sargento Segundo', 'Sr. SGOS.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (56, 'Señor Cabo Primero', 'Sr. CBOP.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (57, 'Señor Cabo Segundo', 'Sr. CBOS.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (58, 'Señor Soldado', 'Sr. Sldo.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (60, 'Señor Brigadier General', 'Sr. BGrl.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (61, 'Señor Teniente General', 'Sr. TGrl.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (62, 'Señor General del Aire', 'Sr. GRaa.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (266, 'Señorita Marinero', 'Srta. MARO.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (68, 'Señor Capitán de Corbeta', 'Sr. CPCB.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (69, 'Señor Teniente de Navío', 'Sr. TNNV.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (70, 'Señor Teniente de Fragata', 'Sr. TNFG.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (71, 'Señor Alferez de Fragata', 'Sr. ALFG.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (226, 'Señor Zootecnista', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (212, 'Señor Físico', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (213, 'Señora Física', 'Sra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (216, 'Señora Periodista', 'Sra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (220, 'Señor Diseñador', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (218, 'Señor Maestro', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (219, 'Señora Maestra', 'Sra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (221, 'Señora Diseñadora', 'Sra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (227, 'Señora Zootecnista', 'Sra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (65, 'Señor Contralmirante', 'Sr.CALM.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (223, 'Señor Acuicultor', 'Sr. Ac.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (8, 'Señor Contador', 'Sr. CPA.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (24, 'Señora Contadora', 'Sra. CPA.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (25, 'Señorita Contadora', 'Srta. CPA.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (224, 'Señora Acuicultora', 'Sra. Ac.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (192, 'Señorita Cabo Primero Esp. Avc.', 'Srta. Cbop. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (197, 'Señorita Cabo Segundo Téc. Avc.', 'Srta. Cbos. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (198, 'Señorita Cabo Segundo Esp. Avc.', 'Srta. Cbos. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (201, 'Señorita Soldado Téc. Avc.', 'Srta. Sldo. Téc. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (202, 'Señorita Soldado Esp. Avc.', 'Srta. Sldo. Esp. Avc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (104, 'Señor Sociólogo', 'Sr. Soc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (105, 'Señora Socióloga', 'Sra. Soc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (113, 'Señor General de Brigada (sp)', 'Sr. Grab. (sp)');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (114, 'Señor General de División (sp)', 'Sr. Grad. (sp)');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (115, 'Señor General de Ejército (sp)', 'Sr. Grae. (sp)');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (119, 'Señor General del Aire (sp)', 'Sr. GRaa. (sp)');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (120, 'Señor Antropólogo', 'Sr. Antrop.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (121, 'Señora Antropóloga', 'Sra. Antrop.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (123, 'Señor Matemático', 'Sr. Mat.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (124, 'Señora Matemática', 'Sra. Mat.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (6, 'Señor Tecnólogo', 'Sr. Tlgo.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (23, 'Señorita Tecnóloga', 'Srta. Tlga.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (205, 'Señor Bioquímico Farmaceútico', 'Sr. BQF.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (214, 'Señorita Física', 'Srta.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (217, 'Señorita Periodista', 'Srta.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (222, 'Señorita Diseñadora', 'Srta.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (228, 'Señorita Zootecnista', 'Srta.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (225, 'Señorita Acuicultora', 'Srta. Ac.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (229, 'Señor Odontólogo', 'Sr. Od.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (230, 'Señora Odontóloga', 'Sra. Od.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (231, 'Señorita Odontóloga', 'Srita. Od.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (233, 'Señora Meteoróloga', 'Sra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (234, 'Señorita Meteoróloga', 'Srita.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (207, 'Señorita Bioquímica Farmaceútica', 'Srta. BQF.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (236, 'Señorita Química Farmacéutica', 'Srta. QF.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (101, 'Señor Psicólogo', 'Sr. Psic.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (235, 'Señor Químico Farmacéutico', 'Sr. QF.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (238, 'Señora Teniente de Navío', 'Sra. TNNV');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (67, 'Señor Capitán De Fragata', 'Sr. C P F G.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (73, 'Señor Analista', 'Sr. Anl.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (74, 'Señora Analista', 'Sra. Anl.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (75, 'Señorita Analista', 'Srta. Anl.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (243, 'Señor Obstetriz', 'Sr. Obst.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (255, 'Señor Politólogo', 'Sr. Pltgo');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (244, 'Señora Obstetriz', 'Sra. Obst.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (245, 'Señorita Obstetriz', 'Srta. Obst.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (246, 'Señor Obstetra', 'Sr. Obstra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (247, 'Señora Obstetra', 'Sra. Obstra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (248, 'Señorita Obstetra', 'Srta. Obstra.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (88, 'Señora Técnica', 'Sra. Téc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (89, 'Señorita Técnica', 'Srta. Téc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (251, 'Señora Médico Veterinario', 'Sra. Mv.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (242, 'Señor Médico Veterinario Zootecnista', 'Sr. MVz.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (31, 'Señor Ecólogo', 'Sr.Ecól.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (253, 'Señora Médico Veterinario Zootecnista', 'Sra. MVz.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (254, 'Señorita Médico Veterinario Zootecnista', 'Srta. MVz.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (256, 'Señor Especialista', 'Sr. Espc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (257, 'Señora Especialista', 'Sra. Espc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (258, 'Señorita Especialista', 'Srta. Espc.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (259, 'Señor Actuario', 'Sr. Act.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (260, 'Señor Administrador De Empresas', 'Adm. Emp.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (261, 'Señora Administradora De Empresas', 'Adm. Emp.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (262, 'Señorita Administradora De Empresas', 'Adm. Emp.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (249, 'Señor', 'Sr.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (66, 'Señor Capitán De Navío (sp)', 'CPNV (SP)');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (263, 'Señor Capitán De Fragata (sp)', 'CPFG (SP)');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (264, 'Señor Capitán De Corbeta (sp)', 'CPCB (SP)');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (265, 'Señor Teniente De Navío (sp)', 'TNNV (SP)');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (237, 'Señora Química Farmacéutica', 'Sra. Qf.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (267, 'Señor Contador Público Auditor', 'Sr. CPA.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (268, 'Señorita Contadora Pública Auditora', 'Srta. CPA');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (269, 'Señora Contadora Pública Auditora', 'Sra. CPA');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (239, 'Señor Psicologo Industrial', 'Sr.Psic.Ind.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (103, 'Señorita Psicóloga', 'Srta. Psic.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (102, 'Señora Psicóloga', 'Sra. Psic.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (240, 'Señora Psicologa Industrial', 'Sra.Psic.Ind.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (241, 'Señorita Psicologa Industrial', 'Srta.Psic.Ind.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (78, 'Señorita Auditora', 'Srta. Aud.');
INSERT INTO titulo (tit_codi, tit_nombre, tit_abreviatura) VALUES (271, 'Señora Oceanógrafa', 'Sra. Oc.');


--
-- TOC entry 2963 (class 0 OID 53660)
-- Dependencies: 286
-- Data for Name: trd; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO trd (trd_codi, trd_padre, trd_nombre, depe_codi, trd_estado, trd_arch_gestion, trd_arch_central, trd_fecha_desde, trd_fecha_hasta, trd_ocupado, trd_nivel) VALUES (0, 0, NULL, NULL, 0, 5, 15, NULL, NULL, 0, 0);


--
-- TOC entry 2964 (class 0 OID 53668)
-- Dependencies: 287
-- Data for Name: trd_nivel; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2965 (class 0 OID 53671)
-- Dependencies: 288
-- Data for Name: trd_radicado; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2966 (class 0 OID 53674)
-- Dependencies: 289
-- Data for Name: usuario; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO usuario (usua_codi, usua_cedula, usua_nomb, usua_apellido, usua_nombre, cargo_tipo, usua_cargo, usua_nuevo, usua_login, usua_pasw, usua_esta, usua_email, usua_titulo, usua_abr_titulo, tipo_usuario, usua_tipo_certificado, usua_subrogado, visible_sub, usua_cargo_cabecera, usua_direccion, usua_telefono, usua_firma_path, depe_codi, depe_nomb, dep_sigla, inst_codi, inst_nombre, inst_sigla, inst_estado, ciu_codi, usua_ciudad, tipo_identificacion, usua_datos, inst_adscrita, inst_padre_nombre, inst_padre_sigla) VALUES (0, '0000000000', 'Super', 'Administrador', 'Super Administrador', 0, 'Administrador del Sistema', 1, 'UADMINISTRADOR', '02cb962ac59075b964b07152d2', 1, NULL, NULL, NULL, 1, 0, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'Azuay', 0, '0000000000 SUPER ADMINISTRADOR ADMINISTRADOR DEL SISTEMA    ', NULL, NULL, NULL);


--
-- TOC entry 2967 (class 0 OID 53681)
-- Dependencies: 290
-- Data for Name: usuario_dependencia; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2968 (class 0 OID 53694)
-- Dependencies: 293
-- Data for Name: usuario_notificacion; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2921 (class 0 OID 53208)
-- Dependencies: 198
-- Data for Name: usuarios; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO usuarios (usua_login, usua_pasw, usua_nomb, usua_cedula, usua_email, usua_titulo, usua_abr_titulo, usua_esta, usua_codi, cargo_tipo, depe_codi, usua_nuevo, usua_tipo, usua_cargo, inst_codi, usua_apellido, cargo_id, usua_obs, ciu_codi, usua_genero, usua_firma_path, usua_direccion, usua_telefono, usua_codi_actualiza, usua_fecha_actualiza, usua_obs_actualiza, usua_cargo_cabecera, usua_sumilla, usua_responsable_area, inst_nombre, usua_tipo_certificado, visible_sub, usua_subrogado, usua_celular, tipo_identificacion) VALUES ('UADMINISTRADOR', '02cb962ac59075b964b07152d2', 'Super', '0000000000', NULL, NULL, NULL, 1, 0, 0, NULL, 1, 1, 'Administrador del Sistema', NULL, 'Administrador', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 1, NULL, NULL, 0);


--
-- TOC entry 2969 (class 0 OID 53702)
-- Dependencies: 295
-- Data for Name: usuarios_radicado; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2970 (class 0 OID 53709)
-- Dependencies: 296
-- Data for Name: usuarios_sesion; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2971 (class 0 OID 53716)
-- Dependencies: 297
-- Data for Name: usuarios_subrogacion; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- TOC entry 2773 (class 2606 OID 53743)
-- Dependencies: 276 276
-- Name: PK_SGD_TTR_TRANSACCION; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY sgd_ttr_transaccion
    ADD CONSTRAINT "PK_SGD_TTR_TRANSACCION" PRIMARY KEY (sgd_ttr_codigo);


--
-- TOC entry 2698 (class 2606 OID 53745)
-- Dependencies: 204 204
-- Name: hist_opc_impresion_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY hist_opc_impresion
    ADD CONSTRAINT hist_opc_impresion_pkey PRIMARY KEY (hist_codi);


--
-- TOC entry 2700 (class 2606 OID 53747)
-- Dependencies: 206 206 206
-- Name: institucion_org_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY institucion_org
    ADD CONSTRAINT institucion_org_pkey PRIMARY KEY (org_id, inst_codi);


--
-- TOC entry 2729 (class 2606 OID 53749)
-- Dependencies: 231 231
-- Name: log_user_permisos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_usr_permisos
    ADD CONSTRAINT log_user_permisos_pkey PRIMARY KEY (id_transaccion);


--
-- TOC entry 2727 (class 2606 OID 53751)
-- Dependencies: 230 230
-- Name: log_usr_ciudadanos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_usr_ciudadanos
    ADD CONSTRAINT log_usr_ciudadanos_pkey PRIMARY KEY (logc_codi);


--
-- TOC entry 2590 (class 2606 OID 53753)
-- Dependencies: 162 162
-- Name: pkAccion; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY accion
    ADD CONSTRAINT "pkAccion" PRIMARY KEY (accion_codi);


--
-- TOC entry 2592 (class 2606 OID 53755)
-- Dependencies: 164 164
-- Name: pk_actualizar_sistema; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY actualizar_sistema
    ADD CONSTRAINT pk_actualizar_sistema PRIMARY KEY (actu_codi);


--
-- TOC entry 2598 (class 2606 OID 53757)
-- Dependencies: 165 165
-- Name: pk_anex_codigo; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY anexos
    ADD CONSTRAINT pk_anex_codigo PRIMARY KEY (anex_codigo);


--
-- TOC entry 2601 (class 2606 OID 53759)
-- Dependencies: 166 166
-- Name: pk_anex_tipo_codi; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY anexos_tipo
    ADD CONSTRAINT pk_anex_tipo_codi PRIMARY KEY (anex_tipo_codi);


--
-- TOC entry 2604 (class 2606 OID 53761)
-- Dependencies: 167 167
-- Name: pk_archivo; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY archivo
    ADD CONSTRAINT pk_archivo PRIMARY KEY (arch_codi);


--
-- TOC entry 2608 (class 2606 OID 53763)
-- Dependencies: 168 168 168
-- Name: pk_archivo_nivel; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY archivo_nivel
    ADD CONSTRAINT pk_archivo_nivel PRIMARY KEY (arch_codi, depe_codi);


--
-- TOC entry 2611 (class 2606 OID 53765)
-- Dependencies: 169 169 169 169
-- Name: pk_archivo_radicado; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY archivo_radicado
    ADD CONSTRAINT pk_archivo_radicado PRIMARY KEY (radi_nume_radi, arch_codi, anex_numero);


--
-- TOC entry 2613 (class 2606 OID 53767)
-- Dependencies: 170 170
-- Name: pk_bandeja_compartida; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY bandeja_compartida
    ADD CONSTRAINT pk_bandeja_compartida PRIMARY KEY (ban_com_codi);


--
-- TOC entry 2616 (class 2606 OID 53769)
-- Dependencies: 173 173
-- Name: pk_bloqueo_sistema; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY bloqueo_sistema
    ADD CONSTRAINT pk_bloqueo_sistema PRIMARY KEY (bloq_codi);


--
-- TOC entry 2618 (class 2606 OID 53773)
-- Dependencies: 175 175
-- Name: pk_carp_codi; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY carpeta
    ADD CONSTRAINT pk_carp_codi PRIMARY KEY (carp_codi);


--
-- TOC entry 2621 (class 2606 OID 53775)
-- Dependencies: 176 176
-- Name: pk_categoria_cat_codi; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY categoria
    ADD CONSTRAINT pk_categoria_cat_codi PRIMARY KEY (cat_codi);


--
-- TOC entry 2628 (class 2606 OID 53777)
-- Dependencies: 179 179
-- Name: pk_ciu_codigo; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY ciudadano
    ADD CONSTRAINT pk_ciu_codigo PRIMARY KEY (ciu_codigo);


--
-- TOC entry 2671 (class 2606 OID 53779)
-- Dependencies: 193 193
-- Name: pk_ciu_codigo_solicitud; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY solicitud_firma_ciudadano
    ADD CONSTRAINT pk_ciu_codigo_solicitud PRIMARY KEY (sol_codigo);


--
-- TOC entry 2624 (class 2606 OID 53781)
-- Dependencies: 178 178
-- Name: pk_ciudad; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY ciudad
    ADD CONSTRAINT pk_ciudad PRIMARY KEY (id);


--
-- TOC entry 2631 (class 2606 OID 53783)
-- Dependencies: 180 180
-- Name: pk_ciudadano_tmp; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY ciudadano_tmp
    ADD CONSTRAINT pk_ciudadano_tmp PRIMARY KEY (ciu_codigo);


--
-- TOC entry 2634 (class 2606 OID 53785)
-- Dependencies: 181 181
-- Name: pk_codificacion_cod_codi; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY codificacion
    ADD CONSTRAINT pk_codificacion_cod_codi PRIMARY KEY (cod_codi);


--
-- TOC entry 2667 (class 2606 OID 53787)
-- Dependencies: 190 190
-- Name: pk_contenido; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY contenido
    ADD CONSTRAINT pk_contenido PRIMARY KEY (cont_codi);


--
-- TOC entry 2669 (class 2606 OID 53789)
-- Dependencies: 191 191
-- Name: pk_contenido_tipo; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY contenido_tipo
    ADD CONSTRAINT pk_contenido_tipo PRIMARY KEY (cont_tipo_codi);


--
-- TOC entry 2675 (class 2606 OID 53799)
-- Dependencies: 195 195
-- Name: pk_dependencia; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY dependencia
    ADD CONSTRAINT pk_dependencia PRIMARY KEY (depe_codi);


--
-- TOC entry 2691 (class 2606 OID 53803)
-- Dependencies: 200 200
-- Name: pk_esta_codi; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY estado
    ADD CONSTRAINT pk_esta_codi PRIMARY KEY (esta_codi);


--
-- TOC entry 2798 (class 2606 OID 53805)
-- Dependencies: 287 287 287
-- Name: pk_expediente_nivel; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY trd_nivel
    ADD CONSTRAINT pk_expediente_nivel PRIMARY KEY (trd_codi, depe_codi);


--
-- TOC entry 2693 (class 2606 OID 53807)
-- Dependencies: 201 201 201
-- Name: pk_formato_numeracion; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY formato_numeracion
    ADD CONSTRAINT pk_formato_numeracion PRIMARY KEY (depe_codi, fn_tiporad);


--
-- TOC entry 2696 (class 2606 OID 53809)
-- Dependencies: 202 202
-- Name: pk_hist_event_fisico_id; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY hist_envio_fisico
    ADD CONSTRAINT pk_hist_event_fisico_id PRIMARY KEY (his_id);


--
-- TOC entry 2640 (class 2606 OID 53811)
-- Dependencies: 184 184
-- Name: pk_hist_eventos; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY hist_eventos
    ADD CONSTRAINT pk_hist_eventos PRIMARY KEY (hist_codi);


--
-- TOC entry 2746 (class 2606 OID 53813)
-- Dependencies: 245 245
-- Name: pk_id_permiso; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY permiso
    ADD CONSTRAINT pk_id_permiso PRIMARY KEY (id_permiso);


--
-- TOC entry 2645 (class 2606 OID 53815)
-- Dependencies: 186 186
-- Name: pk_informados; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY informados
    ADD CONSTRAINT pk_informados PRIMARY KEY (info_codi);


--
-- TOC entry 2679 (class 2606 OID 53817)
-- Dependencies: 196 196
-- Name: pk_institucion; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY institucion
    ADD CONSTRAINT pk_institucion PRIMARY KEY (inst_codi);


--
-- TOC entry 2703 (class 2606 OID 53823)
-- Dependencies: 209 209
-- Name: pk_lista_id; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY lista
    ADD CONSTRAINT pk_lista_id PRIMARY KEY (lista_codi);


--
-- TOC entry 2706 (class 2606 OID 53825)
-- Dependencies: 210 210 210
-- Name: pk_lista_usuarios; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY lista_usuarios
    ADD CONSTRAINT pk_lista_usuarios PRIMARY KEY (lista_codi, usua_codi);


--
-- TOC entry 2709 (class 2606 OID 53827)
-- Dependencies: 212 212
-- Name: pk_log; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log
    ADD CONSTRAINT pk_log PRIMARY KEY (log_id);


--
-- TOC entry 2711 (class 2606 OID 53829)
-- Dependencies: 214 214
-- Name: pk_log_acceso; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_acceso
    ADD CONSTRAINT pk_log_acceso PRIMARY KEY (log_codi);


--
-- TOC entry 2713 (class 2606 OID 53833)
-- Dependencies: 215 215
-- Name: pk_log_archivo_descarga; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_archivo_descarga
    ADD CONSTRAINT pk_log_archivo_descarga PRIMARY KEY (log_codi);


--
-- TOC entry 2715 (class 2606 OID 53835)
-- Dependencies: 218 218
-- Name: pk_log_bloqueos_dos; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_bloqueos_dos
    ADD CONSTRAINT pk_log_bloqueos_dos PRIMARY KEY (log_codi);


--
-- TOC entry 2717 (class 2606 OID 53837)
-- Dependencies: 220 220
-- Name: pk_log_full_backup1; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_full_backup
    ADD CONSTRAINT pk_log_full_backup1 PRIMARY KEY (log_codi);


--
-- TOC entry 2719 (class 2606 OID 53841)
-- Dependencies: 222 222
-- Name: pk_log_matar_procesos_servidores; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_matar_procesos_servidores
    ADD CONSTRAINT pk_log_matar_procesos_servidores PRIMARY KEY (log_codi);


--
-- TOC entry 2721 (class 2606 OID 53843)
-- Dependencies: 225 225
-- Name: pk_log_paginas_visitadas; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_paginas_visitadas
    ADD CONSTRAINT pk_log_paginas_visitadas PRIMARY KEY (log_codi);


--
-- TOC entry 2723 (class 2606 OID 53845)
-- Dependencies: 226 226
-- Name: pk_log_sesion; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_sesion
    ADD CONSTRAINT pk_log_sesion PRIMARY KEY (fecha);


--
-- TOC entry 2725 (class 2606 OID 53847)
-- Dependencies: 227 227
-- Name: pk_log_tiempo_ws_firma; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_tiempo_ws_firma
    ADD CONSTRAINT pk_log_tiempo_ws_firma PRIMARY KEY (radi_nume_radi);


--
-- TOC entry 2731 (class 2606 OID 53849)
-- Dependencies: 232 232
-- Name: pk_log_view_usuario; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY log_view_usuario
    ADD CONSTRAINT pk_log_view_usuario PRIMARY KEY (log_codi);


--
-- TOC entry 2733 (class 2606 OID 53851)
-- Dependencies: 235 235
-- Name: pk_mail; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY mail_notificacion
    ADD CONSTRAINT pk_mail PRIMARY KEY (mail_codi);


--
-- TOC entry 2735 (class 2606 OID 53853)
-- Dependencies: 236 236
-- Name: pk_met; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY metadatos
    ADD CONSTRAINT pk_met PRIMARY KEY (met_codi);


--
-- TOC entry 2737 (class 2606 OID 53855)
-- Dependencies: 238 238
-- Name: pk_met_radicado; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY metadatos_radicado
    ADD CONSTRAINT pk_met_radicado PRIMARY KEY (met_radi_codi);


--
-- TOC entry 2742 (class 2606 OID 53857)
-- Dependencies: 242 242
-- Name: pk_opc_imp_sob_codi; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY opciones_impresion_sobre
    ADD CONSTRAINT pk_opc_imp_sob_codi PRIMARY KEY (opc_imp_sob_codi);


--
-- TOC entry 2740 (class 2606 OID 53859)
-- Dependencies: 240 240
-- Name: pk_opciones_impresion; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY opciones_impresion
    ADD CONSTRAINT pk_opciones_impresion PRIMARY KEY (opc_imp_codi);


--
-- TOC entry 2750 (class 2606 OID 53861)
-- Dependencies: 246 246 246
-- Name: pk_permiso_cargo; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY permiso_usuario
    ADD CONSTRAINT pk_permiso_cargo PRIMARY KEY (id_permiso, usua_codi);


--
-- TOC entry 2752 (class 2606 OID 53863)
-- Dependencies: 247 247 247
-- Name: pk_permiso_usuario_dep; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY permiso_usuario_dep
    ADD CONSTRAINT pk_permiso_usuario_dep PRIMARY KEY (id_permiso, depe_codi);


--
-- TOC entry 2665 (class 2606 OID 53865)
-- Dependencies: 187 187
-- Name: pk_radi_final; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY radicado
    ADD CONSTRAINT pk_radi_final PRIMARY KEY (radi_nume_radi);


--
-- TOC entry 2755 (class 2606 OID 53869)
-- Dependencies: 248 248
-- Name: pk_radi_text; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY radi_texto
    ADD CONSTRAINT pk_radi_text PRIMARY KEY (text_codi);


--
-- TOC entry 2758 (class 2606 OID 53871)
-- Dependencies: 249 249
-- Name: pk_radicado_sec_temp; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY radicado_sec_temp
    ADD CONSTRAINT pk_radicado_sec_temp PRIMARY KEY (depe_codi);


--
-- TOC entry 2762 (class 2606 OID 53875)
-- Dependencies: 253 253
-- Name: pk_resp_hist_eventos; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY respaldo_hist_eventos
    ADD CONSTRAINT pk_resp_hist_eventos PRIMARY KEY (resp_hist_eventos);


--
-- TOC entry 2764 (class 2606 OID 53877)
-- Dependencies: 255 255
-- Name: pk_resp_soli_codi; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY respaldo_solicitud
    ADD CONSTRAINT pk_resp_soli_codi PRIMARY KEY (resp_soli_codi);


--
-- TOC entry 2760 (class 2606 OID 53879)
-- Dependencies: 251 251
-- Name: pk_respaldo_estado; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY respaldo_estado
    ADD CONSTRAINT pk_respaldo_estado PRIMARY KEY (est_codi);


--
-- TOC entry 2766 (class 2606 OID 53881)
-- Dependencies: 257 257
-- Name: pk_respaldo_usuario; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY respaldo_usuario
    ADD CONSTRAINT pk_respaldo_usuario PRIMARY KEY (resp_codi);


--
-- TOC entry 2770 (class 2606 OID 53883)
-- Dependencies: 259 259
-- Name: pk_respaldo_usuario_radicado; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY respaldo_usuario_radicado
    ADD CONSTRAINT pk_respaldo_usuario_radicado PRIMARY KEY (resp_radi_codi);


--
-- TOC entry 2779 (class 2606 OID 53885)
-- Dependencies: 278 278
-- Name: pk_tarea; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea
    ADD CONSTRAINT pk_tarea PRIMARY KEY (tarea_codi);


--
-- TOC entry 2781 (class 2606 OID 53887)
-- Dependencies: 279 279
-- Name: pk_tarea_hist_eventos; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea_hist_eventos
    ADD CONSTRAINT pk_tarea_hist_eventos PRIMARY KEY (tarea_hist_codi);


--
-- TOC entry 2783 (class 2606 OID 53889)
-- Dependencies: 280 280
-- Name: pk_tarea_radi_respuesta; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea_radi_respuesta
    ADD CONSTRAINT pk_tarea_radi_respuesta PRIMARY KEY (tarea_resp_codi);


--
-- TOC entry 2785 (class 2606 OID 53893)
-- Dependencies: 282 282
-- Name: pk_tipo_certificado; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tipo_certificado
    ADD CONSTRAINT pk_tipo_certificado PRIMARY KEY (tipo_cert_codi);


--
-- TOC entry 2788 (class 2606 OID 53895)
-- Dependencies: 283 283
-- Name: pk_tiporad; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tiporad
    ADD CONSTRAINT pk_tiporad PRIMARY KEY (trad_codigo);


--
-- TOC entry 2792 (class 2606 OID 53897)
-- Dependencies: 284 284
-- Name: pk_titulo_tit_codi; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY titulo
    ADD CONSTRAINT pk_titulo_tit_codi PRIMARY KEY (tit_codi);


--
-- TOC entry 2795 (class 2606 OID 53899)
-- Dependencies: 286 286
-- Name: pk_trd; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY trd
    ADD CONSTRAINT pk_trd PRIMARY KEY (trd_codi);


--
-- TOC entry 2801 (class 2606 OID 53901)
-- Dependencies: 288 288 288
-- Name: pk_trd_radicado; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY trd_radicado
    ADD CONSTRAINT pk_trd_radicado PRIMARY KEY (radi_nume_radi, trd_codi);


--
-- TOC entry 2687 (class 2606 OID 53903)
-- Dependencies: 198 198
-- Name: pk_usua_codi; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuarios
    ADD CONSTRAINT pk_usua_codi PRIMARY KEY (usua_codi);


--
-- TOC entry 2813 (class 2606 OID 53905)
-- Dependencies: 293 293 293
-- Name: pk_usuario_notificacion; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuario_notificacion
    ADD CONSTRAINT pk_usuario_notificacion PRIMARY KEY (id_mail, usua_destinatario);


--
-- TOC entry 2818 (class 2606 OID 53907)
-- Dependencies: 295 295
-- Name: pk_usuario_radicado; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuarios_radicado
    ADD CONSTRAINT pk_usuario_radicado PRIMARY KEY (usua_radi_codi);


--
-- TOC entry 2821 (class 2606 OID 53909)
-- Dependencies: 296 296
-- Name: pk_usuario_sesion; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuarios_sesion
    ADD CONSTRAINT pk_usuario_sesion PRIMARY KEY (usua_codi);


--
-- TOC entry 2809 (class 2606 OID 53911)
-- Dependencies: 289 289
-- Name: pk_view_usuario; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuario
    ADD CONSTRAINT pk_view_usuario PRIMARY KEY (usua_codi);


--
-- TOC entry 2811 (class 2606 OID 53915)
-- Dependencies: 290 290
-- Name: usuario_dependencia_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuario_dependencia
    ADD CONSTRAINT usuario_dependencia_pkey PRIMARY KEY (usua_codi_depe);


--
-- TOC entry 2824 (class 2606 OID 53917)
-- Dependencies: 297 297
-- Name: usuarios_subrogacion_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuarios_subrogacion
    ADD CONSTRAINT usuarios_subrogacion_pkey PRIMARY KEY (usua_subrogacion_codi);


--
-- TOC entry 2593 (class 1259 OID 53918)
-- Dependencies: 165
-- Name: anex_pk_anex_codigo; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX anex_pk_anex_codigo ON anexos USING btree (anex_codigo) WITH (fillfactor=95);


--
-- TOC entry 2680 (class 1259 OID 53919)
-- Dependencies: 198
-- Name: fki_depe_codi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_depe_codi ON usuarios USING btree (depe_codi) WITH (fillfactor=70);


--
-- TOC entry 2635 (class 1259 OID 53920)
-- Dependencies: 184
-- Name: fki_historico_ttr_codigo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_historico_ttr_codigo ON hist_eventos USING btree (sgd_ttr_codigo) WITH (fillfactor=100);


--
-- TOC entry 2636 (class 1259 OID 53921)
-- Dependencies: 184
-- Name: fki_historico_usua_codi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_historico_usua_codi ON hist_eventos USING btree (usua_codi_ori) WITH (fillfactor=100);


--
-- TOC entry 2637 (class 1259 OID 53922)
-- Dependencies: 184
-- Name: fki_historico_usua_codi_dest; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_historico_usua_codi_dest ON hist_eventos USING btree (usua_codi_dest) WITH (fillfactor=100);


--
-- TOC entry 2642 (class 1259 OID 53923)
-- Dependencies: 186
-- Name: fki_informados_radi_nume_radi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_informados_radi_nume_radi ON informados USING btree (radi_nume_radi) WITH (fillfactor=90);


--
-- TOC entry 2643 (class 1259 OID 53924)
-- Dependencies: 186
-- Name: fki_informados_usua_codi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_informados_usua_codi ON informados USING btree (usua_codi) WITH (fillfactor=90);


--
-- TOC entry 2753 (class 1259 OID 53925)
-- Dependencies: 248
-- Name: fki_radi_texto_radi_nume_radi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_radi_texto_radi_nume_radi ON radi_texto USING btree (radi_nume_radi) WITH (fillfactor=98);


--
-- TOC entry 2646 (class 1259 OID 53926)
-- Dependencies: 187
-- Name: fki_radicado_radi_tipo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX fki_radicado_radi_tipo ON radicado USING btree (radi_tipo) WITH (fillfactor=80);


--
-- TOC entry 2594 (class 1259 OID 53927)
-- Dependencies: 165
-- Name: idx_anexos_anex_codigo_varchar; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_anexos_anex_codigo_varchar ON anexos USING btree (anex_codigo varchar_pattern_ops);


--
-- TOC entry 2599 (class 1259 OID 53928)
-- Dependencies: 166
-- Name: idx_anexos_tipo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_anexos_tipo ON anexos_tipo USING btree (anex_tipo_codi) WITH (fillfactor=100);


--
-- TOC entry 2619 (class 1259 OID 53929)
-- Dependencies: 176
-- Name: idx_categoria; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_categoria ON categoria USING btree (cat_codi) WITH (fillfactor=100);


--
-- TOC entry 2625 (class 1259 OID 53930)
-- Dependencies: 179
-- Name: idx_ciudadano_ciu_cedula; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_ciudadano_ciu_cedula ON ciudadano USING btree (ciu_cedula);


--
-- TOC entry 2672 (class 1259 OID 53931)
-- Dependencies: 195
-- Name: idx_dependencia_depe_nombre; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_dependencia_depe_nombre ON dependencia USING btree (depe_nomb);


--
-- TOC entry 2689 (class 1259 OID 53932)
-- Dependencies: 200
-- Name: idx_estado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_estado ON estado USING btree (esta_codi) WITH (fillfactor=90);


--
-- TOC entry 2647 (class 1259 OID 53933)
-- Dependencies: 187 187 187 187 187
-- Name: idx_fechradi_usuaactu_estacodi_instactu_fechaden_estacodi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_fechradi_usuaactu_estacodi_instactu_fechaden_estacodi ON radicado USING btree (radi_fech_radi, radi_usua_actu, esta_codi, radi_inst_actu, radi_fech_agend) WITH (fillfactor=80);


--
-- TOC entry 2638 (class 1259 OID 53934)
-- Dependencies: 184 184
-- Name: idx_hist_eventos_usua_codi_ori_sgd_ttr_codigo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_hist_eventos_usua_codi_ori_sgd_ttr_codigo ON hist_eventos USING btree (usua_codi_ori, sgd_ttr_codigo) WITH (fillfactor=100);


--
-- TOC entry 2676 (class 1259 OID 53935)
-- Dependencies: 196
-- Name: idx_inst_codi; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX idx_inst_codi ON institucion USING btree (inst_codi) WITH (fillfactor=90);


--
-- TOC entry 2681 (class 1259 OID 53936)
-- Dependencies: 198
-- Name: idx_login; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_login ON usuarios USING btree (usua_login) WITH (fillfactor=70);


--
-- TOC entry 2677 (class 1259 OID 53937)
-- Dependencies: 196
-- Name: idx_nombre; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_nombre ON institucion USING btree (inst_nombre) WITH (fillfactor=90);


--
-- TOC entry 2738 (class 1259 OID 53938)
-- Dependencies: 240
-- Name: idx_opciones_impresion_radi_nume_radi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_opciones_impresion_radi_nume_radi ON opciones_impresion USING btree (radi_nume_radi);


--
-- TOC entry 2747 (class 1259 OID 53939)
-- Dependencies: 246
-- Name: idx_permiso_usuario_usua_codi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_permiso_usuario_usua_codi ON permiso_usuario USING btree (usua_codi);


--
-- TOC entry 2648 (class 1259 OID 53940)
-- Dependencies: 187 187 187
-- Name: idx_radi_nume_asoc_radi_nume_radi_esta_codi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radi_nume_asoc_radi_nume_radi_esta_codi ON radicado USING btree (radi_nume_asoc, radi_nume_radi, esta_codi);


--
-- TOC entry 2814 (class 1259 OID 53941)
-- Dependencies: 295 295
-- Name: idx_radi_nume_radi_tipo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radi_nume_radi_tipo ON usuarios_radicado USING btree (radi_nume_radi, radi_usua_tipo);


--
-- TOC entry 2649 (class 1259 OID 53942)
-- Dependencies: 187
-- Name: idx_radicado_esta_codi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_esta_codi ON radicado USING btree (esta_codi) WITH (fillfactor=80);


--
-- TOC entry 2650 (class 1259 OID 53943)
-- Dependencies: 187
-- Name: idx_radicado_inst_actu; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_inst_actu ON radicado USING btree (radi_inst_actu) WITH (fillfactor=80);


--
-- TOC entry 2651 (class 1259 OID 53944)
-- Dependencies: 187 187
-- Name: idx_radicado_radi_cca_array; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_radi_cca_array ON radicado USING gin (string_to_array(btrim((radi_cca)::text, '-'::text), '--'::text));


--
-- TOC entry 2652 (class 1259 OID 53945)
-- Dependencies: 187 187
-- Name: idx_radicado_radi_fech_ofic_radi_fech_firma; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_radi_fech_ofic_radi_fech_firma ON radicado USING btree (radi_fech_ofic, radi_fech_firma) WITH (fillfactor=80);


--
-- TOC entry 2653 (class 1259 OID 53946)
-- Dependencies: 187
-- Name: idx_radicado_radi_fech_radi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_radi_fech_radi ON radicado USING btree (radi_fech_radi) WITH (fillfactor=80);


--
-- TOC entry 2654 (class 1259 OID 53947)
-- Dependencies: 187 187 187
-- Name: idx_radicado_radi_inst_actu_radi_usua_actu_esta_codi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_radi_inst_actu_radi_usua_actu_esta_codi ON radicado USING btree (radi_inst_actu, radi_usua_actu, esta_codi) WITH (fillfactor=80);


--
-- TOC entry 2655 (class 1259 OID 53948)
-- Dependencies: 187
-- Name: idx_radicado_radi_nume_deri; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_radi_nume_deri ON radicado USING btree (radi_nume_deri);


--
-- TOC entry 2656 (class 1259 OID 53949)
-- Dependencies: 187
-- Name: idx_radicado_radi_nume_radi; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX idx_radicado_radi_nume_radi ON radicado USING btree (radi_nume_radi) WITH (fillfactor=80);


--
-- TOC entry 2657 (class 1259 OID 53950)
-- Dependencies: 187
-- Name: idx_radicado_radi_nume_temp; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_radi_nume_temp ON radicado USING btree (radi_nume_temp) WITH (fillfactor=80);


--
-- TOC entry 2658 (class 1259 OID 53951)
-- Dependencies: 187 187 187
-- Name: idx_radicado_radi_nume_temp_radi_inst_actu_radi_usua_actu; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_radi_nume_temp_radi_inst_actu_radi_usua_actu ON radicado USING btree (radi_nume_temp, radi_inst_actu, radi_usua_actu) WITH (fillfactor=80);


--
-- TOC entry 2659 (class 1259 OID 53952)
-- Dependencies: 187
-- Name: idx_radicado_radi_usua_actu; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_radi_usua_actu ON radicado USING btree (radi_usua_actu) WITH (fillfactor=80);


--
-- TOC entry 2660 (class 1259 OID 53953)
-- Dependencies: 187 187
-- Name: idx_radicado_radi_usua_dest_array; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_radi_usua_dest_array ON radicado USING gin (string_to_array(btrim((radi_usua_dest)::text, '-'::text), '--'::text));


--
-- TOC entry 2661 (class 1259 OID 53954)
-- Dependencies: 187 1824
-- Name: idx_radicado_radi_usua_rem; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_radi_usua_rem ON radicado USING gin (radi_usua_rem gin_trgm_ops);


--
-- TOC entry 2662 (class 1259 OID 53955)
-- Dependencies: 187 187
-- Name: idx_radicado_radi_usua_rem_array; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radicado_radi_usua_rem_array ON radicado USING gin (string_to_array(btrim((radi_usua_rem)::text, '-'::text), '--'::text));


--
-- TOC entry 2595 (class 1259 OID 53956)
-- Dependencies: 165 165
-- Name: idx_radinume_borrados; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radinume_borrados ON anexos USING btree (anex_radi_nume, anex_borrado) WITH (fillfactor=95);


--
-- TOC entry 2663 (class 1259 OID 53957)
-- Dependencies: 187
-- Name: idx_radinumtex; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_radinumtex ON radicado USING btree (radi_nume_text) WITH (fillfactor=80);


--
-- TOC entry 2767 (class 1259 OID 53958)
-- Dependencies: 259
-- Name: idx_respaldo_usuario_radicado_radi_nume_radi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_respaldo_usuario_radicado_radi_nume_radi ON respaldo_usuario_radicado USING btree (radi_nume_radi);


--
-- TOC entry 2768 (class 1259 OID 53959)
-- Dependencies: 259
-- Name: idx_respaldo_usuario_radicado_resp_codi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_respaldo_usuario_radicado_resp_codi ON respaldo_usuario_radicado USING btree (resp_codi);


--
-- TOC entry 2774 (class 1259 OID 53960)
-- Dependencies: 276
-- Name: idx_sgd_ttr_trans; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_sgd_ttr_trans ON sgd_ttr_transaccion USING btree (sgd_ttr_codigo) WITH (fillfactor=90);


--
-- TOC entry 2775 (class 1259 OID 53961)
-- Dependencies: 278
-- Name: idx_tarea_estado; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tarea_estado ON tarea USING btree (estado);


--
-- TOC entry 2776 (class 1259 OID 53962)
-- Dependencies: 278
-- Name: idx_tarea_radi_nume_radi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tarea_radi_nume_radi ON tarea USING btree (radi_nume_radi);


--
-- TOC entry 2777 (class 1259 OID 53963)
-- Dependencies: 278
-- Name: idx_tarea_usua_codi_ori; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tarea_usua_codi_ori ON tarea USING btree (usua_codi_ori);


--
-- TOC entry 2786 (class 1259 OID 53964)
-- Dependencies: 283
-- Name: idx_tiporad; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tiporad ON tiporad USING btree (trad_codigo) WITH (fillfactor=98);


--
-- TOC entry 2789 (class 1259 OID 53965)
-- Dependencies: 284
-- Name: idx_tit_nombre; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_tit_nombre ON titulo USING btree (tit_nombre) WITH (fillfactor=90);


--
-- TOC entry 2793 (class 1259 OID 53966)
-- Dependencies: 286
-- Name: idx_trd_depe_codi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_trd_depe_codi ON trd USING btree (depe_codi);


--
-- TOC entry 2682 (class 1259 OID 53967)
-- Dependencies: 198
-- Name: idx_usua_cedula; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usua_cedula ON usuarios USING btree (usua_cedula) WITH (fillfactor=70);


--
-- TOC entry 2683 (class 1259 OID 53968)
-- Dependencies: 198
-- Name: idx_usua_esta; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usua_esta ON usuarios USING btree (usua_esta) WITH (fillfactor=70);


--
-- TOC entry 2802 (class 1259 OID 53969)
-- Dependencies: 289
-- Name: idx_usuario_depe_codi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_depe_codi ON usuario USING btree (depe_codi);


--
-- TOC entry 2803 (class 1259 OID 53970)
-- Dependencies: 289 289
-- Name: idx_usuario_inst_codi_gt0; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_inst_codi_gt0 ON usuario USING btree (inst_codi) WHERE (inst_codi > 0);


--
-- TOC entry 2804 (class 1259 OID 53971)
-- Dependencies: 289
-- Name: idx_usuario_usua_cedula; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_usua_cedula ON usuario USING btree (usua_cedula);


--
-- TOC entry 2805 (class 1259 OID 53972)
-- Dependencies: 289 1824
-- Name: idx_usuario_usua_datos; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_usua_datos ON usuario USING gin (usua_datos gin_trgm_ops);


--
-- TOC entry 2806 (class 1259 OID 53973)
-- Dependencies: 289
-- Name: idx_usuario_usua_login; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_usua_login ON usuario USING btree (usua_login);


--
-- TOC entry 2807 (class 1259 OID 53974)
-- Dependencies: 289
-- Name: idx_usuario_usua_nombre; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuario_usua_nombre ON usuario USING btree (usua_nombre);


--
-- TOC entry 2815 (class 1259 OID 53975)
-- Dependencies: 295 295 295 295 1937
-- Name: idx_usuarios_radicado_ts_vector2; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuarios_radicado_ts_vector2 ON usuarios_radicado USING gin (to_tsvector('es'::regconfig, upper((((((COALESCE(usua_nombre, ''::character varying))::text || ' '::text) || (COALESCE(usua_apellido, ''::character varying))::text) || ' '::text) || (COALESCE(usua_institucion, ''::character varying))::text))));


--
-- TOC entry 2822 (class 1259 OID 53976)
-- Dependencies: 297
-- Name: idx_usuarios_subrogacion_usua_visible; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuarios_subrogacion_usua_visible ON usuarios_subrogacion USING btree (usua_visible);


--
-- TOC entry 2684 (class 1259 OID 53977)
-- Dependencies: 198 1824 198
-- Name: idx_usuarios_translate_usua_cargo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuarios_translate_usua_cargo ON usuarios USING gin (translate(upper((usua_cargo)::text), 'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ'::text, 'AEIOUAEIOUAEIOUN'::text) gin_trgm_ops);


--
-- TOC entry 2685 (class 1259 OID 53978)
-- Dependencies: 198
-- Name: idx_usuarios_usua_cedula; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_usuarios_usua_cedula ON usuarios USING btree (usua_cedula);


--
-- TOC entry 2596 (class 1259 OID 53979)
-- Dependencies: 165
-- Name: ind_anex_radi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ind_anex_radi ON anexos USING btree (anex_radi_nume) WITH (fillfactor=95);


--
-- TOC entry 2606 (class 1259 OID 53980)
-- Dependencies: 168 168
-- Name: ind_archivo_nivel; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ind_archivo_nivel ON archivo_nivel USING btree (arch_codi, depe_codi) WITH (fillfactor=100);


--
-- TOC entry 2609 (class 1259 OID 53981)
-- Dependencies: 169 169 169
-- Name: ind_archivo_radicado; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ind_archivo_radicado ON archivo_radicado USING btree (radi_nume_radi, arch_codi, anex_numero) WITH (fillfactor=100);


--
-- TOC entry 2626 (class 1259 OID 53982)
-- Dependencies: 179
-- Name: ind_ciu_codigo; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ind_ciu_codigo ON ciudadano USING btree (ciu_codigo) WITH (fillfactor=90);


--
-- TOC entry 2622 (class 1259 OID 53983)
-- Dependencies: 178
-- Name: ind_ciudad; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ind_ciudad ON ciudad USING btree (id) WITH (fillfactor=100);


--
-- TOC entry 2629 (class 1259 OID 53984)
-- Dependencies: 180
-- Name: ind_ciudadano_tmp; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ind_ciudadano_tmp ON ciudadano_tmp USING btree (ciu_codigo) WITH (fillfactor=90);


--
-- TOC entry 2796 (class 1259 OID 53985)
-- Dependencies: 287 287
-- Name: ind_expediente_nivel; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ind_expediente_nivel ON trd_nivel USING btree (trd_codi, depe_codi) WITH (fillfactor=100);


--
-- TOC entry 2744 (class 1259 OID 53987)
-- Dependencies: 245
-- Name: ind_id_permiso; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ind_id_permiso ON permiso USING btree (id_permiso) WITH (fillfactor=98);


--
-- TOC entry 2701 (class 1259 OID 53988)
-- Dependencies: 209
-- Name: ind_lista_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ind_lista_id ON lista USING btree (lista_codi) WITH (fillfactor=90);


--
-- TOC entry 2704 (class 1259 OID 53989)
-- Dependencies: 210 210
-- Name: ind_lista_usuarios; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ind_lista_usuarios ON lista_usuarios USING btree (lista_codi, usua_codi) WITH (fillfactor=98);


--
-- TOC entry 2748 (class 1259 OID 53990)
-- Dependencies: 246 246
-- Name: ind_permiso_cargo; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ind_permiso_cargo ON permiso_usuario USING btree (id_permiso, usua_codi) WITH (fillfactor=90);


--
-- TOC entry 2799 (class 1259 OID 53991)
-- Dependencies: 288 288
-- Name: ind_trd_radicado; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ind_trd_radicado ON trd_radicado USING btree (radi_nume_radi, trd_codi) WITH (fillfactor=95);


--
-- TOC entry 2819 (class 1259 OID 53992)
-- Dependencies: 296
-- Name: ind_usuario_sesion; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX ind_usuario_sesion ON usuarios_sesion USING btree (usua_codi) WITH (fillfactor=65);


--
-- TOC entry 2816 (class 1259 OID 53993)
-- Dependencies: 295
-- Name: indradi_nume_radi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX indradi_nume_radi ON usuarios_radicado USING btree (radi_nume_radi) WITH (fillfactor=100);


--
-- TOC entry 2602 (class 1259 OID 53994)
-- Dependencies: 167
-- Name: padre; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX padre ON archivo USING btree (arch_padre) WITH (fillfactor=70);


--
-- TOC entry 2614 (class 1259 OID 53995)
-- Dependencies: 170
-- Name: pk_bandejacompartida; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pk_bandejacompartida ON bandeja_compartida USING btree (ban_com_codi) WITH (fillfactor=100);


--
-- TOC entry 2632 (class 1259 OID 53996)
-- Dependencies: 181
-- Name: pk_codificacion; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pk_codificacion ON codificacion USING btree (cod_codi) WITH (fillfactor=100);


--
-- TOC entry 2673 (class 1259 OID 53997)
-- Dependencies: 195
-- Name: pk_depe; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX pk_depe ON dependencia USING btree (depe_codi) WITH (fillfactor=80);


--
-- TOC entry 2707 (class 1259 OID 53998)
-- Dependencies: 210
-- Name: pk_listausuarios; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pk_listausuarios ON lista_usuarios USING btree (lista_codi) WITH (fillfactor=98);


--
-- TOC entry 2743 (class 1259 OID 53999)
-- Dependencies: 242
-- Name: pk_opciones_impresionsobre; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pk_opciones_impresionsobre ON opciones_impresion_sobre USING btree (opc_imp_sob_codi) WITH (fillfactor=90);


--
-- TOC entry 2790 (class 1259 OID 54000)
-- Dependencies: 284
-- Name: pk_titulo; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pk_titulo ON titulo USING btree (tit_codi) WITH (fillfactor=90);


--
-- TOC entry 2605 (class 1259 OID 54001)
-- Dependencies: 167
-- Name: pkarchivo; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX pkarchivo ON archivo USING btree (arch_codi) WITH (fillfactor=70);


--
-- TOC entry 2694 (class 1259 OID 54002)
-- Dependencies: 201 201
-- Name: pkformatonumeracio; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX pkformatonumeracio ON formato_numeracion USING btree (depe_codi, fn_tiporad) WITH (fillfactor=70);


--
-- TOC entry 2756 (class 1259 OID 54003)
-- Dependencies: 248
-- Name: pkradi_texto; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX pkradi_texto ON radi_texto USING btree (text_codi) WITH (fillfactor=98);


--
-- TOC entry 2771 (class 1259 OID 54004)
-- Dependencies: 259
-- Name: pkrespaldo_usuario_radicado; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX pkrespaldo_usuario_radicado ON respaldo_usuario_radicado USING btree (resp_radi_codi) WITH (fillfactor=75);


--
-- TOC entry 2688 (class 1259 OID 54005)
-- Dependencies: 198
-- Name: pkusuarios; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX pkusuarios ON usuarios USING btree (usua_codi) WITH (fillfactor=70);


--
-- TOC entry 2641 (class 1259 OID 54006)
-- Dependencies: 184
-- Name: radi_nume_radi; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX radi_nume_radi ON hist_eventos USING btree (radi_nume_radi) WITH (fillfactor=100);


--
-- TOC entry 2893 (class 2620 OID 54007)
-- Dependencies: 178 340
-- Name: trig_actualizar_view_usuario_ciudad; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trig_actualizar_view_usuario_ciudad AFTER UPDATE ON ciudad FOR EACH ROW EXECUTE PROCEDURE func_actualizar_view_usuario_ciudad();


--
-- TOC entry 2894 (class 2620 OID 54008)
-- Dependencies: 179 342
-- Name: trig_actualizar_view_usuario_ciudadano; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trig_actualizar_view_usuario_ciudadano AFTER INSERT OR DELETE OR UPDATE ON ciudadano FOR EACH ROW EXECUTE PROCEDURE func_actualizar_view_usuario_ciudadano();


--
-- TOC entry 2895 (class 2620 OID 54009)
-- Dependencies: 343 195
-- Name: trig_actualizar_view_usuario_dependencia; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trig_actualizar_view_usuario_dependencia AFTER UPDATE ON dependencia FOR EACH ROW EXECUTE PROCEDURE func_actualizar_view_usuario_dependencia();


--
-- TOC entry 2896 (class 2620 OID 54010)
-- Dependencies: 344 196
-- Name: trig_actualizar_view_usuario_institucion; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trig_actualizar_view_usuario_institucion AFTER UPDATE ON institucion FOR EACH ROW EXECUTE PROCEDURE func_actualizar_view_usuario_institucion();


--
-- TOC entry 2897 (class 2620 OID 54011)
-- Dependencies: 198 345
-- Name: trig_actualizar_view_usuario_usuarios; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trig_actualizar_view_usuario_usuarios AFTER INSERT OR UPDATE ON usuarios FOR EACH ROW EXECUTE PROCEDURE func_actualizar_view_usuario_usuarios();


--
-- TOC entry 2825 (class 2606 OID 54022)
-- Dependencies: 165 2664 187
-- Name: fk_anexos_anex_radi_nume; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY anexos
    ADD CONSTRAINT fk_anexos_anex_radi_nume FOREIGN KEY (anex_radi_nume) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2827 (class 2606 OID 54027)
-- Dependencies: 167 167 2603
-- Name: fk_archivo_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY archivo
    ADD CONSTRAINT fk_archivo_01 FOREIGN KEY (arch_padre) REFERENCES archivo(arch_codi);


--
-- TOC entry 2828 (class 2606 OID 54032)
-- Dependencies: 2674 168 195
-- Name: fk_archivo_nivel_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY archivo_nivel
    ADD CONSTRAINT fk_archivo_nivel_01 FOREIGN KEY (depe_codi) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2829 (class 2606 OID 54037)
-- Dependencies: 169 2664 187
-- Name: fk_archivo_radicado_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY archivo_radicado
    ADD CONSTRAINT fk_archivo_radicado_01 FOREIGN KEY (radi_nume_radi) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2830 (class 2606 OID 54042)
-- Dependencies: 167 169 2603
-- Name: fk_archivo_radicado_02; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY archivo_radicado
    ADD CONSTRAINT fk_archivo_radicado_02 FOREIGN KEY (arch_codi) REFERENCES archivo(arch_codi);


--
-- TOC entry 2831 (class 2606 OID 54047)
-- Dependencies: 169 2686 198
-- Name: fk_archivo_radicado_03; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY archivo_radicado
    ADD CONSTRAINT fk_archivo_radicado_03 FOREIGN KEY (usua_codi) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2832 (class 2606 OID 54052)
-- Dependencies: 195 2674 169
-- Name: fk_archivo_radicado_04; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY archivo_radicado
    ADD CONSTRAINT fk_archivo_radicado_04 FOREIGN KEY (depe_codi) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2833 (class 2606 OID 54062)
-- Dependencies: 2627 180 179
-- Name: fk_ciudadano_tmp_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY ciudadano_tmp
    ADD CONSTRAINT fk_ciudadano_tmp_01 FOREIGN KEY (ciu_codigo) REFERENCES ciudadano(ciu_codigo);


--
-- TOC entry 2851 (class 2606 OID 54067)
-- Dependencies: 198 2674 195
-- Name: fk_depe_codi; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuarios
    ADD CONSTRAINT fk_depe_codi FOREIGN KEY (depe_codi) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2868 (class 2606 OID 54072)
-- Dependencies: 2674 247 195
-- Name: fk_depe_codi; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY permiso_usuario_dep
    ADD CONSTRAINT fk_depe_codi FOREIGN KEY (depe_codi) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2847 (class 2606 OID 54077)
-- Dependencies: 195 195 2674
-- Name: fk_dependencia_dep_central; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY dependencia
    ADD CONSTRAINT fk_dependencia_dep_central FOREIGN KEY (dep_central) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2848 (class 2606 OID 54082)
-- Dependencies: 2674 195 195
-- Name: fk_dependencia_depe_codi_padre; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY dependencia
    ADD CONSTRAINT fk_dependencia_depe_codi_padre FOREIGN KEY (depe_codi_padre) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2849 (class 2606 OID 54087)
-- Dependencies: 195 195 2674
-- Name: fk_dependencia_depe_plantilla; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY dependencia
    ADD CONSTRAINT fk_dependencia_depe_plantilla FOREIGN KEY (depe_plantilla) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2850 (class 2606 OID 54092)
-- Dependencies: 2678 195 196
-- Name: fk_dependencia_institucion; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY dependencia
    ADD CONSTRAINT fk_dependencia_institucion FOREIGN KEY (inst_codi) REFERENCES institucion(inst_codi);


--
-- TOC entry 2854 (class 2606 OID 54097)
-- Dependencies: 201 2674 195
-- Name: fk_formato_numeracion_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY formato_numeracion
    ADD CONSTRAINT fk_formato_numeracion_01 FOREIGN KEY (depe_codi) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2855 (class 2606 OID 54102)
-- Dependencies: 195 201 2674
-- Name: fk_formato_numeracion_02; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY formato_numeracion
    ADD CONSTRAINT fk_formato_numeracion_02 FOREIGN KEY (depe_numeracion) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2856 (class 2606 OID 54107)
-- Dependencies: 2787 283 201
-- Name: fk_formato_numeracion_03; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY formato_numeracion
    ADD CONSTRAINT fk_formato_numeracion_03 FOREIGN KEY (fn_tiporad) REFERENCES tiporad(trad_codigo);


--
-- TOC entry 2834 (class 2606 OID 54112)
-- Dependencies: 2772 184 276
-- Name: fk_historico_ttr_codigo; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY hist_eventos
    ADD CONSTRAINT fk_historico_ttr_codigo FOREIGN KEY (sgd_ttr_codigo) REFERENCES sgd_ttr_transaccion(sgd_ttr_codigo);


--
-- TOC entry 2835 (class 2606 OID 54117)
-- Dependencies: 2664 184 187
-- Name: fk_hitorico_radicado; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY hist_eventos
    ADD CONSTRAINT fk_hitorico_radicado FOREIGN KEY (radi_nume_radi) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2836 (class 2606 OID 54122)
-- Dependencies: 2664 187 186
-- Name: fk_informados_radi_nume_radi; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY informados
    ADD CONSTRAINT fk_informados_radi_nume_radi FOREIGN KEY (radi_nume_radi) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2837 (class 2606 OID 54127)
-- Dependencies: 198 186 2686
-- Name: fk_informados_usua_codi; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY informados
    ADD CONSTRAINT fk_informados_usua_codi FOREIGN KEY (usua_codi) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2838 (class 2606 OID 54132)
-- Dependencies: 186 2686 198
-- Name: fk_informados_usua_info; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY informados
    ADD CONSTRAINT fk_informados_usua_info FOREIGN KEY (usua_info) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2857 (class 2606 OID 54142)
-- Dependencies: 198 2686 209
-- Name: fk_lista_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY lista
    ADD CONSTRAINT fk_lista_01 FOREIGN KEY (usua_codi) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2858 (class 2606 OID 54147)
-- Dependencies: 2678 209 196
-- Name: fk_lista_02; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY lista
    ADD CONSTRAINT fk_lista_02 FOREIGN KEY (inst_codi) REFERENCES institucion(inst_codi);


--
-- TOC entry 2859 (class 2606 OID 54152)
-- Dependencies: 210 209 2702
-- Name: fk_lista_usuarios_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY lista_usuarios
    ADD CONSTRAINT fk_lista_usuarios_01 FOREIGN KEY (lista_codi) REFERENCES lista(lista_codi);


--
-- TOC entry 2860 (class 2606 OID 54157)
-- Dependencies: 236 236 2734
-- Name: fk_met; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY metadatos
    ADD CONSTRAINT fk_met FOREIGN KEY (met_padre) REFERENCES metadatos(met_codi);


--
-- TOC entry 2861 (class 2606 OID 54162)
-- Dependencies: 195 2674 236
-- Name: fk_met_depe; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY metadatos
    ADD CONSTRAINT fk_met_depe FOREIGN KEY (depe_codi) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2862 (class 2606 OID 54167)
-- Dependencies: 238 187 2664
-- Name: fk_met_radicado_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY metadatos_radicado
    ADD CONSTRAINT fk_met_radicado_01 FOREIGN KEY (radi_nume_radi) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2863 (class 2606 OID 54172)
-- Dependencies: 238 236 2734
-- Name: fk_met_radicado_02; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY metadatos_radicado
    ADD CONSTRAINT fk_met_radicado_02 FOREIGN KEY (met_codi) REFERENCES metadatos(met_codi);


--
-- TOC entry 2864 (class 2606 OID 54177)
-- Dependencies: 198 2686 238
-- Name: fk_met_radicado_03; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY metadatos_radicado
    ADD CONSTRAINT fk_met_radicado_03 FOREIGN KEY (usua_codi) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2866 (class 2606 OID 54182)
-- Dependencies: 246 2745 245
-- Name: fk_permiso_cargo_id_permiso; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY permiso_usuario
    ADD CONSTRAINT fk_permiso_cargo_id_permiso FOREIGN KEY (id_permiso) REFERENCES permiso(id_permiso);


--
-- TOC entry 2869 (class 2606 OID 54187)
-- Dependencies: 2745 247 245
-- Name: fk_permiso_usuario; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY permiso_usuario_dep
    ADD CONSTRAINT fk_permiso_usuario FOREIGN KEY (id_permiso) REFERENCES permiso(id_permiso);


--
-- TOC entry 2839 (class 2606 OID 54192)
-- Dependencies: 200 187 2690
-- Name: fk_radi_final_estado; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY radicado
    ADD CONSTRAINT fk_radi_final_estado FOREIGN KEY (esta_codi) REFERENCES estado(esta_codi);


--
-- TOC entry 2871 (class 2606 OID 54197)
-- Dependencies: 248 2664 187
-- Name: fk_radi_texto_radi_nume_radi; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY radi_texto
    ADD CONSTRAINT fk_radi_texto_radi_nume_radi FOREIGN KEY (radi_nume_radi) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2892 (class 2606 OID 54202)
-- Dependencies: 295 187 2664
-- Name: fk_radi_usua_radi_nume_radi; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuarios_radicado
    ADD CONSTRAINT fk_radi_usua_radi_nume_radi FOREIGN KEY (radi_nume_radi) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2840 (class 2606 OID 54207)
-- Dependencies: 2678 196 187
-- Name: fk_radicado_institucion; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY radicado
    ADD CONSTRAINT fk_radicado_institucion FOREIGN KEY (radi_inst_actu) REFERENCES institucion(inst_codi);


--
-- TOC entry 2841 (class 2606 OID 54212)
-- Dependencies: 187 187 2664
-- Name: fk_radicado_radi_padre; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY radicado
    ADD CONSTRAINT fk_radicado_radi_padre FOREIGN KEY (radi_nume_deri) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2842 (class 2606 OID 54217)
-- Dependencies: 2664 187 187
-- Name: fk_radicado_radi_temp; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY radicado
    ADD CONSTRAINT fk_radicado_radi_temp FOREIGN KEY (radi_nume_temp) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2843 (class 2606 OID 54222)
-- Dependencies: 187 283 2787
-- Name: fk_radicado_radi_tipo; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY radicado
    ADD CONSTRAINT fk_radicado_radi_tipo FOREIGN KEY (radi_tipo) REFERENCES tiporad(trad_codigo);


--
-- TOC entry 2844 (class 2606 OID 54227)
-- Dependencies: 187 198 2686
-- Name: fk_radicado_radi_usua_actu; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY radicado
    ADD CONSTRAINT fk_radicado_radi_usua_actu FOREIGN KEY (radi_usua_actu) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2845 (class 2606 OID 54232)
-- Dependencies: 2686 198 187
-- Name: fk_radicado_radi_usua_ante; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY radicado
    ADD CONSTRAINT fk_radicado_radi_usua_ante FOREIGN KEY (radi_usua_ante) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2846 (class 2606 OID 54237)
-- Dependencies: 198 2686 187
-- Name: fk_radicado_radi_usua_radi; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY radicado
    ADD CONSTRAINT fk_radicado_radi_usua_radi FOREIGN KEY (radi_usua_radi) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2872 (class 2606 OID 54242)
-- Dependencies: 255 187 2664
-- Name: fk_respaldo_solicitud_radicado; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY respaldo_solicitud
    ADD CONSTRAINT fk_respaldo_solicitud_radicado FOREIGN KEY (radi_nume_radi) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2873 (class 2606 OID 54247)
-- Dependencies: 2686 257 198
-- Name: fk_respaldo_usuario_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY respaldo_usuario
    ADD CONSTRAINT fk_respaldo_usuario_01 FOREIGN KEY (usua_codi) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2874 (class 2606 OID 54252)
-- Dependencies: 257 259 2765
-- Name: fk_respaldo_usuario_radicado_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY respaldo_usuario_radicado
    ADD CONSTRAINT fk_respaldo_usuario_radicado_01 FOREIGN KEY (resp_codi) REFERENCES respaldo_usuario(resp_codi);


--
-- TOC entry 2875 (class 2606 OID 54257)
-- Dependencies: 187 259 2664
-- Name: fk_respaldo_usuario_radicado_02; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY respaldo_usuario_radicado
    ADD CONSTRAINT fk_respaldo_usuario_radicado_02 FOREIGN KEY (radi_nume_radi) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2876 (class 2606 OID 54262)
-- Dependencies: 187 278 2664
-- Name: fk_tarea_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea
    ADD CONSTRAINT fk_tarea_01 FOREIGN KEY (radi_nume_radi) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2877 (class 2606 OID 54267)
-- Dependencies: 198 2686 278
-- Name: fk_tarea_02; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea
    ADD CONSTRAINT fk_tarea_02 FOREIGN KEY (usua_codi_ori) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2878 (class 2606 OID 54272)
-- Dependencies: 278 198 2686
-- Name: fk_tarea_03; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea
    ADD CONSTRAINT fk_tarea_03 FOREIGN KEY (usua_codi_dest) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2879 (class 2606 OID 54277)
-- Dependencies: 278 278 2778
-- Name: fk_tarea_04; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea
    ADD CONSTRAINT fk_tarea_04 FOREIGN KEY (tarea_codi_padre) REFERENCES tarea(tarea_codi);


--
-- TOC entry 2880 (class 2606 OID 54282)
-- Dependencies: 2778 278 279
-- Name: fk_tarea_hist_eventos_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea_hist_eventos
    ADD CONSTRAINT fk_tarea_hist_eventos_01 FOREIGN KEY (tarea_codi) REFERENCES tarea(tarea_codi);


--
-- TOC entry 2881 (class 2606 OID 54287)
-- Dependencies: 279 2664 187
-- Name: fk_tarea_hist_eventos_02; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea_hist_eventos
    ADD CONSTRAINT fk_tarea_hist_eventos_02 FOREIGN KEY (radi_nume_radi) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2882 (class 2606 OID 54292)
-- Dependencies: 279 2686 198
-- Name: fk_tarea_hist_eventos_03; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea_hist_eventos
    ADD CONSTRAINT fk_tarea_hist_eventos_03 FOREIGN KEY (usua_codi_ori) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2883 (class 2606 OID 54297)
-- Dependencies: 280 2778 278
-- Name: fk_tarea_radi_respuesta_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea_radi_respuesta
    ADD CONSTRAINT fk_tarea_radi_respuesta_01 FOREIGN KEY (tarea_codi) REFERENCES tarea(tarea_codi);


--
-- TOC entry 2884 (class 2606 OID 54302)
-- Dependencies: 2664 187 280
-- Name: fk_tarea_radi_respuesta_02; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea_radi_respuesta
    ADD CONSTRAINT fk_tarea_radi_respuesta_02 FOREIGN KEY (radi_nume_radi) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2885 (class 2606 OID 54307)
-- Dependencies: 280 2664 187
-- Name: fk_tarea_radi_respuesta_03; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY tarea_radi_respuesta
    ADD CONSTRAINT fk_tarea_radi_respuesta_03 FOREIGN KEY (radi_nume_resp) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2886 (class 2606 OID 54312)
-- Dependencies: 286 2794 286
-- Name: fk_trd_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY trd
    ADD CONSTRAINT fk_trd_01 FOREIGN KEY (trd_padre) REFERENCES trd(trd_codi);


--
-- TOC entry 2887 (class 2606 OID 54317)
-- Dependencies: 195 2674 286
-- Name: fk_trd_02; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY trd
    ADD CONSTRAINT fk_trd_02 FOREIGN KEY (depe_codi) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2888 (class 2606 OID 54322)
-- Dependencies: 187 2664 288
-- Name: fk_trd_radicado_01; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY trd_radicado
    ADD CONSTRAINT fk_trd_radicado_01 FOREIGN KEY (radi_nume_radi) REFERENCES radicado(radi_nume_radi);


--
-- TOC entry 2889 (class 2606 OID 54327)
-- Dependencies: 286 2794 288
-- Name: fk_trd_radicado_02; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY trd_radicado
    ADD CONSTRAINT fk_trd_radicado_02 FOREIGN KEY (trd_codi) REFERENCES trd(trd_codi);


--
-- TOC entry 2890 (class 2606 OID 54332)
-- Dependencies: 2686 288 198
-- Name: fk_trd_radicado_03; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY trd_radicado
    ADD CONSTRAINT fk_trd_radicado_03 FOREIGN KEY (usua_codi) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2891 (class 2606 OID 54337)
-- Dependencies: 2674 195 288
-- Name: fk_trd_radicado_04; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY trd_radicado
    ADD CONSTRAINT fk_trd_radicado_04 FOREIGN KEY (depe_codi) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2865 (class 2606 OID 54342)
-- Dependencies: 238 195 2674
-- Name: fk_trd_radicado_04; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY metadatos_radicado
    ADD CONSTRAINT fk_trd_radicado_04 FOREIGN KEY (depe_codi) REFERENCES dependencia(depe_codi);


--
-- TOC entry 2867 (class 2606 OID 54347)
-- Dependencies: 2686 246 198
-- Name: fk_usua_codi; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY permiso_usuario
    ADD CONSTRAINT fk_usua_codi FOREIGN KEY (usua_codi) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2870 (class 2606 OID 54352)
-- Dependencies: 198 247 2686
-- Name: fk_usua_codi; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY permiso_usuario_dep
    ADD CONSTRAINT fk_usua_codi FOREIGN KEY (usua_codi) REFERENCES usuarios(usua_codi);


--
-- TOC entry 2852 (class 2606 OID 54357)
-- Dependencies: 196 198 2678
-- Name: fk_usuario_institucion; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuarios
    ADD CONSTRAINT fk_usuario_institucion FOREIGN KEY (inst_codi) REFERENCES institucion(inst_codi);


--
-- TOC entry 2853 (class 2606 OID 54367)
-- Dependencies: 282 198 2784
-- Name: fk_usuarios_tipo_certificado; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY usuarios
    ADD CONSTRAINT fk_usuarios_tipo_certificado FOREIGN KEY (usua_tipo_certificado) REFERENCES tipo_certificado(tipo_cert_codi);


--
-- TOC entry 2826 (class 2606 OID 54372)
-- Dependencies: 2600 165 166
-- Name: pk_anexos_anex_tipo; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY anexos
    ADD CONSTRAINT pk_anexos_anex_tipo FOREIGN KEY (anex_tipo) REFERENCES anexos_tipo(anex_tipo_codi);


-- Completed on 2014-02-21 16:33:00 ECT

--
-- PostgreSQL database dump complete
--

