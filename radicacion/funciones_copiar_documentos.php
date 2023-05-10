<?php
/**  Programa para el manejo de gestion documental, oficios, memorandus, circulares, acuerdos
*    Desarrollado y en otros Modificado por la SubSecretaría de Informática del Ecuador
*    Quipux    www.gestiondocumental.gov.ec
*------------------------------------------------------------------------------
*    This program is free software: you can redistribute it and/or modify
*    it under the terms of the GNU Affero General Public License as
*    published by the Free Software Foundation, either version 3 of the
*    License, or (at your option) any later version.
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Affero General Public License for more details.
*
*    You should have received a copy of the GNU Affero General Public License
*    along with this program.  If not, see http://www.gnu.org/licenses.
*------------------------------------------------------------------------------
**/


function &obtener_tipo_columna_tabla ($db, $tabla) {
    $sql = "SELECT
                c.relname as \"tabla\",
                a.attname as \"columna\",
                pg_catalog.format_type(a.atttypid, a.atttypmod) as \"tipo\"
            FROM
                pg_catalog.pg_attribute a
                INNER JOIN pg_catalog.pg_class c ON a.attrelid = c.oid
                    LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
            WHERE
                a.attnum > 0
                AND NOT a.attisdropped
                AND n.nspname = 'public'
                and upper(c.relname) = upper('$tabla')";
    $rs = $db->conn->Execute($sql);
    return $rs;
}

function &copiar_registro_en_arreglo($db, $tabla, $where) {
    $respuesta = array();

    $sql = "select * from $tabla where $where";
    $rs = $db->conn->Execute($sql);
    $rs_cols = obtener_tipo_columna_tabla($db, $tabla);
    
    while (!$rs_cols->EOF) {
        $col_tipo = strtolower(substr($rs_cols->fields["TIPO"],0,6));
        $col_nombre = strtoupper($rs_cols->fields["COLUMNA"]);
        switch ($col_tipo) {
            case "numeri":
            case "intege":
            case "smalli":
            case "bigint":
                $respuesta[$col_nombre] = $rs->fields[$col_nombre];
                break;
            case "charac":
            case "timest":
                $respuesta[$col_nombre] = $db->conn->qstr($rs->fields[$col_nombre]);
                break;
            default:
                break;
        }
        if (trim($rs->fields[$col_nombre]) == "") {
            unset($respuesta[$col_nombre]);
        }
        $rs_cols->MoveNext();
    }
    return $respuesta;
}

function copiar_documentos_temporales($db, $radi_nume, $tx, $hist) {
    // Ponemos los registros de la tablas que se copiaran en arreglos.
    $radicado = copiar_registro_en_arreglo($db, "radicado", "radi_nume_radi=$radi_nume");

    $opciones_impresion = copiar_registro_en_arreglo($db, "opciones_impresion", "radi_nume_radi=$radi_nume");

    $dest = str_replace("E", "", str_replace("'", "", $radicado["RADI_USUA_DEST"]));
    $dest = str_replace("-", "", str_replace("--", ",", $dest));
    $destinatarios = explode(",",$dest);

    for ($i=1 ; $i < count($destinatarios) ; ++$i) {
        //Generamos el radi_nume_radi y el radi_nume_text
        $tpRad = substr($radi_nume, -1);
//        $SecName = "SECU_" . $_SESSION["depe_codi"] . "_" . $tpRad;
        $secNew = $db->nextId("sec_radi_nume_radi");
        if ($secNew==0) return 0;

        $newRadicado = date("Y") . str_pad($_SESSION["depe_codi"], 6, "0", STR_PAD_LEFT) . str_pad($secNew,9,"0", STR_PAD_LEFT) . $tpRad;

        if ($tpRad == "0")
            $radicado["RADI_NUME_TEXT"] = $db->conn->qstr($tx->GenerarTextRadicado($newRadicado, $radicado["RADI_TIPO"], "T"));
        else
            $radicado["RADI_NUME_TEXT"] = $db->conn->qstr($tx->GenerarTextRadicado($newRadicado, $radicado["RADI_TIPO"], "N"));

        $radicado["RADI_NUME_RADI"] = $newRadicado;
        $radicado["RADI_NUME_TEMP"] = $newRadicado;
        $radicado["RADI_USUA_DEST"] = $db->conn->qstr("-".$destinatarios[$i]."-");
        $radicado["RADI_FECH_RADI"] = $db->conn->sysTimeStamp;
        $radicado["RADI_FECH_OFIC"] = $db->conn->sysTimeStamp;

        $opciones_impresion["RADI_NUME_RADI"] = $newRadicado;
        unset($opciones_impresion["OPC_IMP_CODI"]);

        $insertSQL = $db->conn->Replace("RADICADO", $radicado, "", false, false, true, false);
        if (count($opciones_impresion)>1)
            $insertSQL = $db->conn->Replace("OPCIONES_IMPRESION", $opciones_impresion, "", false, false, false, false);
        $hist->insertarHistorico($newRadicado, $_SESSION["usua_codi"], $_SESSION["usua_codi"], "", 2, "");

        $sql = "select anex_codigo from anexos where anex_radi_nume=$radi_nume and anex_borrado='N'";
        $rs = $db->conn->Execute($sql);
        while (!$rs->EOF) {
            $anexos = copiar_registro_en_arreglo($db, "anexos", "anex_codigo='".trim($rs->fields["ANEX_CODIGO"])."'");
            $anexos["ANEX_RADI_NUME"] = $newRadicado;
            $anexos["ANEX_CODIGO"] = str_replace($radi_nume, $newRadicado, $anexos["ANEX_CODIGO"]);
            $insertSQL = $db->conn->Replace("ANEXOS", $anexos, "", false, false, true, false);
            $anex_nombre = str_replace("'", "", str_replace("E'", "", $anexos["ANEX_NOMBRE"]));
            $hist->insertarHistorico($newRadicado, $_SESSION["usua_codi"], $_SESSION["usua_codi"], $anex_nombre, 66, "");
            $rs->MoveNext();
        }
    }
    $sql = "update radicado set radi_usua_dest=".$db->conn->qstr("-".$destinatarios[0]."-")." where radi_nume_radi=$radi_nume";
    $db->conn->Execute($sql);

    return 1;
}

?>