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
/*****************************************************************************************
**											**
*****************************************************************************************/
$ruta_raiz = "..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
include_once "$ruta_raiz/rec_session.php";
include_once "obtener_datos_archivo.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/include/tx/Historico.php";

  $sql = "select coalesce(dep_central,depe_codi) as archivo from dependencia where depe_codi=".$_SESSION['depe_codi']."";
  $rs=$db->conn->query($sql);
  $depe_archivo = $rs->fields["ARCHIVO"];

$mensaje = "";
$record = array();
$where = array();
$arr_radicados = explode(",",$_POST["txt_radicado"]);
$nombre_ubicacion = ObtenerUbicacionFisica($_POST["txt_codigo"],$db);

foreach($arr_radicados as $tmp_radicado) {
    if (trim($tmp_radicado)!="") {
	unset($record);
	unset($where);
	$tmp = explode("-",$tmp_radicado);
	$record["RADI_NUME_RADI"] = $tmp[0];
	$record["ANEX_NUMERO"] = $tmp[1];
	$record["ARCH_CODI"] = $_POST["txt_codigo"];
    	$record["USUA_CODI"] = $_SESSION['usua_codi'];
    	$record["DEPE_CODI"] = $depe_archivo;
    	$record["FECHA"] = $db->conn->sysTimeStamp;
    	$where[]="RADI_NUME_RADI";
    	$where[]="ANEX_NUMERO";
    	$where[]="DEPE_CODI";
    
    	$ok = $db->conn->Replace("ARCHIVO_RADICADO", $record, $where, false,false,true,false);
    	$mensaje .= ObtenerCampoRadicado("RADI_NUME_TEXT",$record["RADI_NUME_RADI"],$db).", ";

        $hist = new Historico($db);
        $hist->insertarHistorico($tmp[0], $_SESSION["usua_codi"], $_SESSION["usua_codi"], $nombre_ubicacion, 57, "");

    }
}
//include_once "incluir_doc_archivo.php";
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>

<body>
    <form name="frmConfirmaCreacion" action="./incluir_doc_archivo_cuerpo.php" method="post">
    <br><br>
    <center>
	<table width="40%"  class="borde_tab">
	    <tr> 
		<td width="100%" height="30" class="listado2"><center>
		<? if ($ok==1) { ?>
		   Se ha cambiado la ubicaci&oacute;n de los documentos <br/>&quot;<?=substr($mensaje,0,-2)?>&quot;
			<br/>a la Ubicaci&oacute;n F&iacute;sica &quot;<?=$nombre_ubicacion?>&quot;
		   
		<? } else { ?>
		  Se han colocado los documentos <br/>&quot;<?=substr($mensaje,0,-2)?>&quot;
			<br/>en la Ubicaci&oacute;n F&iacute;sica &quot;<?=$nombre_ubicacion?>&quot;
		   
		<? } ?>
        </center>
		</td> 
	    </tr>
        </table>
        <table  width="40%">
	    <tr>	
		<td height="30" >
			<center><input class="botones" type="submit" name="Submit" value="Aceptar"></center>
		</td> 
	    </tr>
	</table>
    </center>
    </form>
</body>
</html>
