  -- Function: func_grabar_archivo(text, text)

-- DROP FUNCTION func_grabar_archivo(text, text);
select indi_codi, nombre_tabla from indice where esta_codi = 2 order by indi_codi
CREATE OR REPLACE FUNCTION func_grabar_archivo(var_nombre_archivo text, var_archivo_base_64 text)
  RETURNS integer AS
$BODY$
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
BEGIN
    BEGIN
        var_md5 := md5(var_archivo_base_64);
        SELECT arch_codi from archivo where arch_md5=var_md5 and estado=1 limit 1 INTO var_recordset;
        IF var_recordset is not null THEN return var_recordset.arch_codi; END IF;
       
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
           
        var_sql := 'INSERT INTO '||var_nombre_tabla||' (arch_codi, archivo)
                    VALUES ('||var_arch_codi::text||', '||quote_literal(var_archivo_base_64)||')';
        EXECUTE var_sql;
       
        return var_arch_codi;
    EXCEPTION WHEN OTHERS THEN
        PERFORM func_log_archivo (var_sql, SQLERRM);
        return 0;
    END;
END;
$BODY$
  LANGUAGE plpgsql VOLATILE
  COST 100;
ALTER FUNCTION func_grabar_archivo(text, text)
  OWNER TO postgres;
/**

  CREATE TABLESPACE documentos_1
  OWNER postgres
  LOCATION '/var/lib/pgsql/data/documentos_1';



 
