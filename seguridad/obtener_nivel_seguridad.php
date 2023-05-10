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

******************************************************************************************
** Devuelve el nivel de seguridad del documento.                                        **
** Se han definido 5 niveles de seguridad dependiendo del tipo, el estado, los permisos **
** y el usuario que accede al documento.                                                **
*****************************************************************************************/

function obtener_nivel_seguridad_documento($db, $radi_nume) {
    try {
        // Si es el superadministrador o tiene permisos de auditor
        if ($_SESSION["usua_codi"]==0) return 2;

        $sql = "select * from radicado where radi_nume_radi=$radi_nume";
        $rs_rad = $db->query($sql);

        $estado = $rs_rad->fields["ESTA_CODI"];
        $reservado = $rs_rad->fields["RADI_PERMISO"];
        $radi_tipo = substr($radi_nume,-1);
        if ($rs_rad->fields["RADI_USUA_ACTU"]==$_SESSION["usua_codi"]) $usr_actual = 1; else $usr_actual = 0;
        if (strpos($rs_rad->fields["RADI_USUA_REM"],'-'.$_SESSION["usua_codi"].'-')!== false) $usr_rem = 1; else $usr_rem = 0;
        if (strpos($rs_rad->fields["RADI_USUA_DEST"].$rs_rad->fields["RADI_CCA"],'-'.$_SESSION["usua_codi"].'-')!== false) $usr_dest = 1; else $usr_dest = 0;
        if ($rs_rad->fields["RADI_INST_ACTU"]==$_SESSION["inst_codi"] and $_SESSION["inst_codi"]>0) $inst_actu = 1; else $inst_actu = 0;

        // Documento eliminado
        if ($estado==8) return 0;

        // Si es un documento padre y el usuario no pertenece a la institución
        if ($radi_tipo!=1 and $inst_actu==0 and $_SESSION["perm_buscar_doc_adscritas"]!=1) return 0;

        // Documento en elaboración
        if ($estado==1 and $usr_actual) return 7;

        // Documento en trámite
        if ($estado==2 and $usr_actual) return 6;

        // si es un documento con firma electronica firmado por un ciudadano
        if ($estado==9 and $_SESSION["perm_tramitar_docs_ciudadano"]) return 6;

//        // si es un documento firmado manualmente para usuarios de otras instituciones
//        if ($estado==10 and $_SESSION["usua_prad_tp2"]==1) {
//            if ($reservado) return 0;
//            return 6;
//        }
//

        // Si es el usuario actual del documento
        if ($usr_actual) return 4;

        // Documento reservado
        if ($reservado and $usr_rem and $estado!=7 and $estado!=1) return 3;
        if ($reservado and $usr_dest and $radi_tipo==1 and ($estado==2 or $estado==0)) return 3;
        if ($reservado) return 0;


        // Si tiene alguna tarea asignada
        if ($estado==2 or $estado==1) {
            $sql = "select tarea_codi as num from tarea where estado = 1 and radi_nume_radi=$radi_nume and usua_codi_dest=".$_SESSION["usua_codi"];
            $rs_tarea = $db->query($sql);
            if ($rs_tarea && !$rs_tarea->EOF) return 5;
        }

        // Si tiene algun tipo de responsabilidad (registro, reasignado, informado)
        $sql = "select hist_codi as num from hist_eventos where radi_nume_radi=$radi_nume and sgd_ttr_codigo in (2,8,9,28,50) ".
               "and ".$_SESSION["usua_codi"]." in (usua_codi_ori, usua_codi_dest) limit 1 offset 0";
        $rs_hist = $db->query($sql);
        if ($rs_hist && !$rs_hist->EOF) {
            if ($inst_actu==1)
                return 3;
            else
                return 3;
        }

        // Si es el remitente o destinatario del documento
        if ($radi_tipo==1 and ($usr_rem or $usr_dest)) return 3;
        if (($_SESSION["usua_admin_archivo"] == 1 or $_SESSION["usua_perm_archivo"] == 1) and $inst_actu) return 3;

        if ($_SESSION["usua_perm_impresion"]==1 and $estado==5 and $inst_actu) {
            $sql = "select depe_codi from usuarios where usua_codi=".$rs_rad->fields["RADI_USUA_ACTU"];
            $rs_usr = $db->query($sql);
            if ($rs_usr->fields["DEPE_CODI"] == $_SESSION["depe_codi"]) return 3;
        }

        // Si es jefe o tiene permisos de consulta
        if (($_SESSION["cargo_tipo"]==1 or $_SESSION["usua_perm_consulta"]==1) and $inst_actu) {
            $areas = buscar_areas_dependientes($_SESSION["depe_codi"], "N");
            $usuarios = "select usua_codi from usuarios where depe_codi in ($areas)";
            $radicado = $radi_nume;
            if ($radi_tipo==1) $radicado .= ",".$rs_rad->fields["RADI_NUME_TEMP"];
            $sql = "select hist_codi as num from hist_eventos where radi_nume_radi in ($radicado) and sgd_ttr_codigo in (2,8,9,19,28) ".
                   "and (usua_codi_ori in ($usuarios) or usua_codi_dest in ($usuarios)) limit 1 offset 0";
            $rs_hist = $db->query($sql);
            if ($rs_hist && !$rs_hist->EOF) return 3;
        }

        //Si tiene bandeja compartida y el jefe es el usuario del documento
        if (isset($_SESSION["usua_codi_jefe"]) && 0+$_SESSION["usua_codi_jefe"]>0) {
            if ($rs_rad->fields["RADI_USUA_ACTU"]==$_SESSION["usua_codi_jefe"] and $estado==2) return 3;
            if ($radi_tipo==1 and strpos($rs_rad->fields["RADI_USUA_REM"],'-'.$_SESSION["usua_codi_jefe"].'-')!== false) return 3;
            if ($radi_tipo==1 and strpos($rs_rad->fields["RADI_USUA_DEST"].$rs_rad->fields["RADI_CCA"],'-'.$_SESSION["usua_codi_jefe"].'-')!== false) return 3;

            // Si tiene algun tipo de responsabilidad (registro, reasignado, informado)
            $sql = "select hist_codi as num from hist_eventos where radi_nume_radi=$radi_nume and sgd_ttr_codigo in (2,8,9,28,50) ".
                   "and ".$_SESSION["usua_codi_jefe"]." in (usua_codi_ori, usua_codi_dest) limit 1 offset 0";
            $rs_hist = $db->query($sql);
            if ($rs_hist && !$rs_hist->EOF) {
                return 3;
            }
        }

        // Permiso para buscar documentos en cualquier institucion
        if ($_SESSION["perm_buscar_doc_adscritas"] == 1 and in_array($estado, array(0,2,6))) return 2;

        // Permiso de bandeja de entrada
        if ($_SESSION["ver_todos_docu"] == 1 and $inst_actu) return 1;

    } catch (Exception $e) {
        return 0;
    }

    return 0;

}

?>