<?
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
$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
if ($_SESSION["usua_codi"] != "0") die ("");
require_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/include/tx/Historico.php";
$hist = new Historico($db);

$radi_nume = trim(limpiar_sql($_POST["txt_radi_nume"]));
$observacion = trim(limpiar_sql($_POST["txt_observacion"]));
$radicado = ObtenerDatosRadicado($radi_nume, $db);

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

if ($radicado["arch_codi"]==0 and $radicado["arch_codi_firma"]==0) {
    $flag = true;
    $i = 0;
    while ($flag) {
        $path_archivo = substr($radicado["radi_path"], 0,33) . "_e$i" . substr($radicado["radi_path"], 33);
        if (!is_file("$ruta_raiz/bodega$path_archivo")) $flag = false;
        ++$i;
    }

    if (trim($radicado["radi_path"])!="")
        rename("$ruta_raiz/bodega".$radicado["radi_path"], "$ruta_raiz/bodega$path_archivo");
} else {
    if ($radicado["arch_codi_firma"]!=0)
        $path_archivo = $radicado["arch_codi_firma"];
    else
        $path_archivo = $radicado["arch_codi"];
}

$datos = array();
$datos["RADI_NUME_TEMP"] = $radi_nume;
$datos["RADI_PATH"] = "null";
$datos["ARCH_CODI"] = "0";

if (trim($radicado["fecha_firma"]) == "") {
    $accion = "Volver a generar archivo PDF.";
    $mensaje = "Se volvi&oacute; a generar el archivo PDF para el documento No. ".$radicado["radi_nume_text"];
    $insertSQL = $db->conn->Replace("RADICADO", $datos, "RADI_NUME_TEMP", false, false, true, false);
    $hist->insertarHistoricoTemporal($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observacion, 36, $path_archivo);

} else {
    $accion = "Revertir firma digital del documento. ";
    $mensaje = "Se revirti&oacute; la firma electr&oacute;nica del documento No.".$radicado["radi_nume_text"];
    $datos["RADI_FECH_FIRMA"] = "null";
    $datos["RADI_NOMB_USUA_FIRMA"] = "null";
    $datos["RADI_USUA_ACTU"] = str_replace("-", "", $radicado["usua_rem"]);
    $datos["ARCH_CODI_FIRMA"] = "0";
    $sql = "select radi_nume_radi
            from radicado
            where radi_nume_temp=$radi_nume
                and (radi_nume_radi=$radi_nume or replace(radi_usua_dest,'-','')::integer in (select usua_codi from usuarios))";
    $rs = $db->conn->Execute($sql);
    while (!$rs->EOF) {
        $datos["RADI_NUME_RADI"] = $rs->fields["RADI_NUME_RADI"];
        $datos["ESTA_CODI"] = "3";
        if (substr($rs->fields["RADI_NUME_RADI"], -1) == "1")
            $datos["ESTA_CODI"] = "4";
        $insertSQL = $db->conn->Replace("RADICADO", $datos, "RADI_NUME_RADI", false, false, true, false);
        $rs->MoveNext();
    }
    $hist->insertarHistoricoTemporal($radi_nume, $_SESSION["usua_codi"], $_SESSION["usua_codi"], $observacion, 35, $path_archivo);

}

?>
<body>
  <br/>
  <center>
    <table border="0" cellpadding="0" cellspacing="5" class="borde_tab" width="98%">
        <tr>
            <td class="titulos4" align='center'>Acci&oacute;n: <?=$accion?></td>
        </tr>
        <tr>
            <td class="listado1" align='center' valign='top' colspan="2">
                <br>
                <?=$mensaje?>
                <br><br>
                <input type='button' name="btn_accion" value='Aceptar' onClick="window.location='revertir_firma_digital.php'" class='botones'>
                <br><br>
            </td>
        </tr>
    </table>
  </center>
</body>
</html>
