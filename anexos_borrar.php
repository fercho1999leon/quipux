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
$ruta_raiz= ".";
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/include/tx/Historico.php";
$hist = new Historico($db);

if ($accion==1) { //borrar anexos
    $isql = "select a.anex_nombre, a.anex_path, a.anex_radi_nume, r.radi_path
		from anexos a, radicado r
		where a.anex_radi_nume=r.radi_nume_radi and anex_codigo like '$anexo'";
    $rs=$db->conn->query($isql);
    if (!$rs->EOF) {
        $archivo = $rs->fields["ANEX_PATH"];
        $radi_path = $rs->fields["RADI_PATH"];
        $nombre_arch = $rs->fields["ANEX_NOMBRE"];
        $verrad = $rs->fields["ANEX_RADI_NUME"];
        $isql = "update anexos set anex_borrado='S' where anex_codigo like '$anexo'";
        $bien= $db->query($isql);
        //exec("rm $ruta_raiz/bodega$archivo",$output,$returnS);
        if ($archivo==$radi_path)
            $db->query("update radicado set radi_path=null where radi_nume_radi=$verrad");

        if ($bien) {
            $usr=ObtenerDatosUsuario($usua_codi,$db);
            $observa = "Se Eliminó Anexo: $nombre_arch.<br> Eliminado por: ".$usr["nombre"];
            $codTx = 31;
            $hist->insertarHistorico($verrad, $usr["usua_codi"], $usr["usua_codi"], $observa, $codTx);
            $mensaje="<span class='alarmas'>El archivo &quot;$nombre_arch&quot; se elimin&oacute; correctamente.<span> ";
        } else {
            $mensaje="<span class='alarmas'>No fue posible eliminar Archivo &quot;$nombre_arch&quot;<span>";
        }
    }
} 
if ($accion==2) {// poner anexo como imágen del documento
    $isql = "select anex_nombre, anex_path, anex_radi_nume, anex_datos_firma, anex_fecha_firma
                from anexos
                where anex_codigo like '$anexo'";
    $rs=$db->conn->query($isql);
    if (!$rs->EOF)    {
        $archivo=$rs->fields["ANEX_PATH"];
        $nombre_arch = $rs->fields["ANEX_NOMBRE"];
        $verrad = $rs->fields["ANEX_RADI_NUME"];
        $nomb_firma = $rs->fields["ANEX_DATOS_FIRMA"];
        if (trim($nomb_firma)=="") $nomb_firma="null"; else $nomb_firma = "'$nomb_firma'";
        $fecha_firma = $rs->fields["ANEX_FECHA_FIRMA"];
        if (trim($fecha_firma)=="") $fecha_firma="null"; else $fecha_firma = "'$fecha_firma'";
        $isql = "update radicado set radi_path='$archivo', radi_tipo_archivo=1, radi_nomb_usua_firma=$nomb_firma
                , radi_fech_firma=$fecha_firma where radi_nume_radi=$verrad";
//die ($isql);
        $bien= $db->query($isql);
        if ($bien){
            $usr=ObtenerDatosUsuario($usua_codi,$db);
            $observa = "Se colocó el Anexo: \"$nombre_arch\". Como imagen del documento";
            $codTx = 42;
            $hist->insertarHistorico($verrad, $usr["usua_codi"], $usr["usua_codi"], $observa, $codTx);
            $mensaje="<span class='alarmas'>Se ha colocado el Anexo: $nombre_arch. Como imagen del documento<span> ";
        } else {
            $mensaje="<span class='alarmas'>Ocurrio un error al asociar la imagen del documento<span>";
        }
    }
}
if ($accion==3 or $accion==4) { // cambio de medio de almacenamiento
    $isql = "select anex_nombre, anex_fisico, anex_radi_nume
                from anexos
                where anex_codigo like '$anexo'";
    $rs=$db->conn->query($isql);
    if (!$rs->EOF)    {
        $archivo = $rs->fields["ANEX_FISICO"];
        $nombre_arch = $rs->fields["ANEX_NOMBRE"];
        $verrad = $rs->fields["ANEX_RADI_NUME"];
        if ($archivo==0) $archivo = 1; else $archivo = 0;
        $isql = "update anexos set anex_fisico=$archivo where anex_codigo like '$anexo'";
        $bien= $db->query($isql);
        if ($bien){
            $usr=ObtenerDatosUsuario($usua_codi,$db);
            $observa = "Se ha cambiado el medio de almacenamiento del documento: \"$nombre_arch\" a ";
            if ($archivo==0)
                $observa .= "Electrónico";
            else
                $observa .= "Físico";
            $codTx = 30;
            $hist->insertarHistorico($verrad, $usr["usua_codi"], $usr["usua_codi"], $observa, $codTx);
            $mensaje="<span class='alarmas'>Se ha cambiado el medio de almacenamiento del documento: $nombre_arch<span> ";
        } else {
            $mensaje="<span class='alarmas'>Ocurrio un error al cambiar el medio de almacenamiento del documento<span>";
        }
    }
}
echo "<center><br>$mensaje<br></center>";
?>