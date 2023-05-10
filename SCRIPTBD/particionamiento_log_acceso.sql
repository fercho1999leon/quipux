--CREACION DE TABLAS DE ACCESO POR AÑO
LOG_ACCESO
create table log_acceso2014 (check (extract(year from fecha)=2014)) inherits (log_acceso);
--CREACION DE INDICE PARA LA TABLA
create index log_acceso2014x on log_acceso2014 using btree (fecha);
--CREACION DE FUNCION PARA INSERTAR

CREATE OR REPLACE FUNCTION crear_registro_log()
RETURNS TRIGGER AS
$BODY$
BEGIN 
	if (extract (year from NEW.fecha)=2014) then
		insert into log_acceso2014 values (NEW.*);
	else
		RAISE EXCEPTION 'agrege mas tablas para el log';
	end if;
	return NULL;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;
ALTER FUNCTION crear_registro_log()
owner TO postgres;
--CREACION DE TRIGGER

DROP TRIGGER generar_registros ON log_acceso;
create trigger generar_registros
BEFORE INSERT on log_acceso
for each row
execute procedure crear_registro_log();


LOG_ARCHIVO_DESCARGA


create table log_archivo_descarga2014 (check (extract(year from fecha)=2014)) inherits (log_archivo_descarga);
--CREACION DE INDICE PARA LA TABLA
create index log_archivo_descarga2014x on log_archivo_descarga2014 using btree (fecha);
--CREACION DE FUNCION PARA INSERTAR

CREATE OR REPLACE FUNCTION crear_registro_log_archivo_descarga()
RETURNS TRIGGER AS
$BODY$
BEGIN 
	if (extract (year from NEW.fecha)=2014) then
		insert into log_archivo_descarga2014 values (NEW.*);
	else
		RAISE EXCEPTION 'agrege mas tablas para el log de descarga';
	end if;
	return NULL;
END;
$BODY$
LANGUAGE plpgsql VOLATILE
COST 100;
ALTER FUNCTION crear_registro_log_archivo_descarga()
owner TO postgres;
--CREACION DE TRIGGER

DROP TRIGGER generar_registros_log_archivo_descarga ON log_archivo_descarga;
create trigger generar_registros_log_archivo_descarga
BEFORE INSERT on log_archivo_descarga
for each row
execute procedure crear_registro_log_archivo_descarga();


