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
session_start();
$ruta_raiz= "..";
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/seguridad/obtener_nivel_seguridad.php";
include_once "$ruta_raiz/include/tx/Historico.php";
$hist = new Historico($db);

$anexo = limpiar_sql($_POST["anexo"]);
$accion = limpiar_numero($_POST["accion"]);
$radi_nume = limpiar_numero($_POST["radi_nume"]);

$datos_radicado = ObtenerDatosRadicado($radi_nume,$db);
$nivel_seguridad_documento = obtener_nivel_seguridad_documento($db, $radi_nume);
if (!in_array($nivel_seguridad_documento, array(5,7)) and !($_SESSION["usua_perm_digitalizar"]==1 and isset($_POST["asocImgRad"])))
    die ("Usted no tiene los permisos suficientes para visualizar estos archivos");


$sql = "select a.anex_nombre, a.anex_path, a.anex_datos_firma, a.anex_fecha_firma
            , r.radi_nume_radi, r.radi_path, r.esta_codi, r.radi_usua_actu, r.radi_nume_temp, r.radi_fech_firma, r.radi_imagen
        from anexos a left outer join radicado r on a.anex_radi_nume=r.radi_nume_radi
        where anex_codigo = '$anexo'";
$rs = $db->conn->query($sql);
if (!$rs or $rs->EOF) $accion = "Error";

$mensaje="No se pudo realizar la aci&oacute;n solicitada.";
switch ($accion) {
    case 1: //borrar anexos
        if ($nivel_seguridad_documento == 7) {
            // Marcamos el anexo como eliminado
            $db->query("update anexos set anex_borrado='S' where anex_codigo = '$anexo'");
            // Eliminamos el link en radicado si era una imagen asociada
            if ($rs->fields["ANEX_PATH"]==$rs->fields["RADI_PATH"] and trim($rs->fields["RADI_PATH"])!="") {
                $path = "null";
                $sql = "update radicado set radi_path=null, radi_tipo_archivo=0
                            , radi_nomb_usua_firma=null, radi_fech_firma=null
                        where radi_nume_radi=".$rs->fields["RADI_NUME_RADI"];
                $db->query($sql);
            }
            if ($rs->fields["RADI_IMAGEN"]==$anexo) {
                $sql = "update radicado set radi_imagen=null, radi_tipo_archivo=0
                        where radi_nume_radi=".$rs->fields["RADI_NUME_RADI"];
                $db->query($sql);
            }
            $observacion = "Se Eliminó Anexo: ".$rs->fields["ANEX_NOMBRE"].".<br> Eliminado por: ".$_SESSION["usua_nomb"];
            $hist->insertarHistorico($rs->fields["RADI_NUME_RADI"], $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observacion, 31);
            $mensaje="El archivo &quot;".$rs->fields["ANEX_NOMBRE"]."&quot; se elimin&oacute; correctamente.";
        }
        break;
    case 2: // poner anexo como imágen del documento
        if ((substr($radi_nume, -1)=="2" and ($nivel_seguridad_documento==7 or ($nivel_seguridad_documento==5 and $datos_radicado["estado"]==1)))
        or ($_SESSION["usua_perm_digitalizar"]==1 and isset($_POST["asocImgRad"]))) {
            $where = "where (radi_nume_radi=$radi_nume or radi_nume_temp=$radi_nume)
                        and radi_inst_actu=".$_SESSION["inst_codi"]."
                        and radi_fech_firma is null and esta_codi not in (0)
                        and not (radi_nume_radi::text like '%0' and esta_codi in (1,3,4,7,8))";

            $rs_img = $db->query("select radi_nume_radi from radicado $where");
            if (!$rs_img or $rs_img->EOF) {
                $mensaje = 'No se pudo colocar el anexo: "'.$rs->fields["ANEX_NOMBRE"].'" como imagen del documento.
                            <br>Por favor verifique sus permisos sobre el mismo.';
                break;
            }
            $ok = $db->query("update radicado set radi_imagen='$anexo', radi_tipo_archivo=1 $where");
            $observacion = 'Se colocó el anexo: "'.$rs->fields["ANEX_NOMBRE"].'" como imagen del documento';
            $mensaje = 'Se ha colocado el anexo: "'.$rs->fields["ANEX_NOMBRE"].'" como imagen del documento';
            while (!$rs_img->EOF) {
                $hist->insertarHistorico($rs_img->fields["RADI_NUME_RADI"], $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observacion, 42);
                $rs_img->MoveNext();
            }
        }
        break;
    case 3: //Quitar anexo como imagen del documento
        if ((substr($radi_nume, -1)=="2" and ($nivel_seguridad_documento==7 or ($nivel_seguridad_documento==5 and $datos_radicado["estado"]==1)))
        or ($_SESSION["usua_perm_digitalizar"]==1 and isset($_POST["asocImgRad"]))) { //OJO: Verificar para usr con permisos de digitalización y archivo
            $path = "null";
            if ($rs->fields["ANEX_PATH"]==$rs->fields["RADI_PATH"] and trim($rs->fields["RADI_PATH"])!="") {
                if (in_array($rs->fields["ESTA_CODI"], array(0,2,5,6))) {
                    $path = "'/".substr($rs->fields["RADI_NUME_TEMP"],0,4)."/".substr($rs->fields["RADI_NUME_TEMP"],4,6)."/".$rs->fields["RADI_NUME_TEMP"].".pdf'";
                    if (!is_file("$ruta_raiz/bodega$path")) $path = "null";
                }
            }
            $where = "where (radi_nume_radi=$radi_nume or radi_nume_temp=$radi_nume)
                        and radi_inst_actu=".$_SESSION["inst_codi"]."
                        and radi_fech_firma is null and esta_codi not in (0)
                        and not (radi_nume_radi::text like '%0' and esta_codi in (1,3,4,7,8))";

            $rs_img = $db->query("select radi_nume_radi from radicado $where");
            if (!$rs_img or $rs_img->EOF) {
                $mensaje = 'No se pudo quitar la imagen digitalizada del documento.
                            <br>Por favor verifique sus permisos sobre el mismo.';
                break;
            }

            $sql = "update radicado set radi_path=$path, radi_tipo_archivo=0
                        , radi_imagen=null, radi_nomb_usua_firma=null, radi_fech_firma=null
                    $where";
            $db->query($sql);
            $observacion = 'Se quitó el anexo: "'.$rs->fields["ANEX_NOMBRE"].'" como imagen del documento';
            $mensaje = 'El anexo: "'.$rs->fields["ANEX_NOMBRE"].'" ha dejado de ser la imagen del documento';
            while (!$rs_img->EOF) {
                $hist->insertarHistorico($rs->fields["RADI_NUME_RADI"], $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observacion, 42);
                $rs_img->MoveNext();
            }
        }

        break;

    case 4: // Cambiar medio de almacenamiento
    case 5:
        if ($nivel_seguridad_documento == 7) { //OJO: Verificar para usr con permisos de digitalización y archivo
            if ($accion==4) {
                $anex_fisico = 0;
                $observacion = "Electrónico";
            } else {
                $anex_fisico = 1;
                $observacion = "Físico";
            }

            $sql = "update anexos set anex_fisico=$anex_fisico where anex_codigo = '$anexo'";
            $ok = $db->query($sql);
            if ($ok){
                $observacion = 'Se ha cambiado el medio de almacenamiento del anexo: "'.$rs->fields["ANEX_NOMBRE"].'" a '.$observacion;
                $hist->insertarHistorico($rs->fields["RADI_NUME_RADI"], $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observacion, 30);
                $mensaje="<span class='alarmas'>$observacion<span> ";
            }
        }
        break;
    case 6: // Editar descripción del anexo
        if ($nivel_seguridad_documento == 7) { //OJO: Verificar para usr con permisos de digitalización y archivo
            $descripcion = $db->conn->qstr(substr(limpiar_sql($_POST["txt_descripcion"]),0,500));
            $sql = "update anexos set anex_desc=$descripcion where anex_codigo = '$anexo'";
            $ok = $db->query($sql);
            if ($ok){
                $observacion = 'Se ha modificado la descripción del anexo: "'.$rs->fields["ANEX_NOMBRE"].'"';
                $hist->insertarHistorico($rs->fields["RADI_NUME_RADI"], $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observacion, 30);
                $mensaje="<span class='alarmas'>$observacion<span> ";
            }
        }

        break;
    default:

        break;
}

echo "<center><br><span class='alarmas'>$mensaje<span><br></center>";


?>