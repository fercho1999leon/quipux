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
$ruta_raiz = "..";
session_start();
require_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/funciones.php";
require_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/include/tx/Historico.php";

$Historico = new Historico($db);

$radi_nume = limpiar_numero($_POST["txt_radi_nume"]);
$opcion = limpiar_sql($_POST["txt_opcion"]);
$dato = 0+limpiar_sql(base64_decode(base64_decode(limpiar_sql($_POST["txt_dato"]))));

$radicado = ObtenerDatosRadicado($radi_nume, $db);

switch ($opcion) {
    case "categoria":
        if (($radicado["estado"]==1 and $radicado["usua_actu"]==$_SESSION["usua_codi"]) or
            ($radicado["estado"]==9 and $_SESSION["perm_tramitar_docs_ciudadano"]==1 and $_SESSION["inst_codi"]==$radicado["inst_actu"])) {
            $sql = "update radicado set cat_codi=$dato where radi_nume_radi=$radi_nume";
            if($db->conn->Execute($sql)) {
                $rs = $db->conn->Execute("select cat_descr, cat_codi from categoria where cat_codi in ($dato,".(0+$radicado["cat_codi"]).")");
                $nomb[$rs->fields["CAT_CODI"]] = $rs->fields["CAT_DESCR"];
                $rs->MoveNext();
                $nomb[$rs->fields["CAT_CODI"]] = $rs->fields["CAT_DESCR"];

                $observa = "Cambió de categoría de ".$nomb[(0+$radicado["cat_codi"])]." a ".$nomb[$dato];
                $Historico->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observa, 11);
            }
        }
        break;

    case "tipificacion":
        if (($radicado["estado"]==1 and $radicado["usua_actu"]==$_SESSION["usua_codi"]) or
            ($radicado["estado"]==9 and $_SESSION["perm_tramitar_docs_ciudadano"]==1 and $_SESSION["inst_codi"]==$radicado["inst_actu"])) {
            $sql = "update radicado set cod_codi=$dato where radi_nume_radi=$radi_nume";
            if($db->conn->Execute($sql)) {
                $rs = $db->conn->Execute("select cod_descripcion, cod_codi from codificacion where cod_codi in ($dato,".(0+$radicado["cod_codi"]).")");
                $nomb[$rs->fields["COD_CODI"]] = $rs->fields["COD_DESCRIPCION"];
                $rs->MoveNext();
                $nomb[$rs->fields["COD_CODI"]] = $rs->fields["COD_DESCRIPCION"];

                $observa = "Cambió la tipificación del documento de ".$nomb[(0+$radicado["cod_codi"])]." a ".$nomb[$dato];
                $Historico->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observa, 11);
            }
        }
        break;

    case "tipo_doc":
        
        if (($radicado["estado"]==1 and $radicado["usua_actu"]==$_SESSION["usua_codi"]) or
            ($radicado["estado"]==9 and $_SESSION["perm_tramitar_docs_ciudadano"]==1 and $_SESSION["inst_codi"]==$radicado["inst_actu"])) {
            $sql = "update radicado set radi_tipo=$dato where radi_nume_radi=$radi_nume";
            if($db->conn->Execute($sql)) {
                $rs = $db->conn->Execute("select trad_descr, trad_codigo from tiporad where trad_codigo in ($dato,".$radicado["radi_tipo"].")");
                $nomb[$rs->fields["TRAD_CODIGO"]] = $rs->fields["TRAD_DESCR"];
                $rs->MoveNext();
                $nomb[$rs->fields["TRAD_CODIGO"]] = $rs->fields["TRAD_DESCR"];

                $observa = "Cambió el tipo de documento de ".$nomb[$radicado["radi_tipo"]]." a ".$nomb[$dato];
                $Historico->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observa, 11);
                //Eliminar las opciones de impresion
                if ($radi_nume!=''){
                    $sql_exit = "select * from opciones_impresion where radi_nume_radi=$radi_nume";
                    $rs_ex=$db->conn->Execute($sql_exit);
                    if(!$rs_ex->EOF){
                        $sqlDel = "delete from opciones_impresion where radi_nume_radi = $radi_nume";
                        $db->conn->Execute($sqlDel);
                        $observa = "Se eliminó las opciones de impresion por cambio de tipo de Documento de  ".$nomb[$radicado["radi_tipo"]]." a ".$nomb[$dato];
                        $Historico->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observa, 11);
                    }
                }
            }
        }
        break;

    case "nivel_seguridad":
        if ((($radicado["estado"] == 1 or $radicado["estado"] == 2) and $radicado["usua_actu"]==$_SESSION["usua_codi"]) or
            ($radicado["estado"]==9 and $_SESSION["perm_tramitar_docs_ciudadano"]==1 and $_SESSION["inst_codi"]==$radicado["inst_actu"])) {
            $sql = "update radicado set radi_permiso=$dato where radi_nume_radi=$radi_nume";
            if($db->conn->Execute($sql)) {
                $nomb[0] = "Público";
                $nomb[1] = "Confidencial";

                $observa = "Cambió el nivel de seguridad del documento de ".$nomb[$radicado["seguridad"]]." a ".$nomb[$dato];
                $Historico->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observa, 11);
            }
        }
        break;

    case "usua_redirigido":
        if (($radicado["estado"]==1 and $radicado["usua_actu"]==$_SESSION["usua_codi"]) or
            ($radicado["estado"]==9 and $_SESSION["perm_tramitar_docs_ciudadano"]==1 and $_SESSION["inst_codi"]==$radicado["inst_actu"])) {
            $sql = "update radicado set radi_usua_redirigido=$dato where radi_nume_radi=$radi_nume";
            if($db->conn->Execute($sql)) {
                $rs = $db->conn->Execute("select usua_nombre from usuario where usua_codi = $dato");
                $observa = "El documento será enviado a ".$rs->fields["USUA_NOMBRE"];
                $Historico->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observa, 11);
            }
        }
        break;

    case "radi_resumen":
        if ((($radicado["estado"]==2 or $radicado["estado"]==1 or $radicado["estado"]==6) and $radicado["usua_actu"]==$_SESSION["usua_codi"]) or
            ($radicado["estado"]==9 and $_SESSION["perm_tramitar_docs_ciudadano"]==1 and $_SESSION["inst_codi"]==$radicado["inst_actu"])) {
            $dato = $db->conn->qstr(substr(limpiar_sql(base64_decode(base64_decode(limpiar_sql($_POST["txt_dato"])))),0,998));
            $sql = "update radicado set radi_resumen=$dato where radi_nume_radi=$radi_nume";
            if($db->conn->Execute($sql)) {
                $observa = "Se cambió/añadió una nota al documento.";
                $Historico->insertarHistorico($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observa, 11);
            }
        }
        break;

}
?>