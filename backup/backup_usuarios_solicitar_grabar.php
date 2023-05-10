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

**************************************************************************************
** Graba las solicitudes de respaldos de la documentacion de los usuarios           **
**                                                                                  **
** Desarrollado por:                                                                **
**      Mauricio Haro A. - mauricioharo21@gmail.com                                 **
*************************************************************************************/

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";

if($_SESSION["usua_perm_backup"]!=1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}

$txt_usua_codi = trim(limpiar_sql($_POST["txt_usua_codi"]));

// guarda solo nuevas solicitudes, si la solicitud ya ha sido eliminada o ha finalizado
$sql = "select * from respaldo_usuario where usua_codi=$txt_usua_codi and fecha_fin is null and fecha_eliminado is null";
$rs = $db->query($sql);

if ($rs->EOF) {
    $record = array();
    unset($record);
    $record["USUA_CODI"] = $txt_usua_codi;
    $record["FECHA_SOLICITA"] = $db->conn->sysTimeStamp;
    $db->conn->Replace("RESPALDO_USUARIO", $record, "", false,false,true,false);
}

echo "<html>".html_head();
$rs = $db->query("select usua_nombre from usuario where usua_codi=$txt_usua_codi");
echo "<center><br>
        Se ha solicitado un respaldo de la documentaci&oacute;n del usuario &quot;".$rs->fields["USUA_NOMBRE"]."&quot;.
      </center>";
?>
  </body>
</html>
