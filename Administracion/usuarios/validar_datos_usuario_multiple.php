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

$ruta_raiz = "../..";
session_start();
require_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_adm_validar_usuario_multiple!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_adm_validar_usuario_multiple);

$usr_cedula = trim(limpiar_sql($_POST['cedula']));
$usr_codigo = 0 + $_POST['usr_codigo'];
$usr_tipo = 0 + $_POST['usr_tipo'];

$sql = "select --Administracion/usuarios/validar_datos_usuario_multiple - usr=".$_SESSION['usua_codi']."
            case when tipo_usuario='2' then '<i>(Ciu.)</i>' else '<i>(Serv.)</i>' end as \"Tipo\"
            , usua_nombre as \"Nombre\"
            , usua_cargo as \"Puesto\"
            , depe_nomb as \"$descDependencia\"
            , inst_nombre as \"$descEmpresa\"
        from usuario where usua_cedula like '$usr_cedula' and usua_codi<>$usr_codigo
        order by tipo_usuario asc, inst_codi asc, usua_codi asc";

$rs = $db->conn->Execute($sql);
if (!$rs or $rs->EOF) die("");

//    echo str_replace("<", "&lt;", $isql)."<br>";

echo "<center><blink><img src='$ruta_raiz/iconos/img_alerta_2.gif'>&nbsp;&nbsp;&nbsp;Existen usuarios registrados con el mismo n&uacute;mero de c&eacute;dula.</blink></center>";
$pager = new ADODB_Pager($db,$sql,'adodb', true,$orderNo,$orderTipo);
$pager->checkAll = false;
$pager->checkTitulo = false;
$pager->toRefLinks = $linkPagina;
$pager->toRefVars = $encabezado;
$pager->descCarpetasGen=$descCarpetasGen;
$pager->descCarpetasPer=$descCarpetasPer;
$pager->Render($rows_per_page=20,$linkPagina,$checkbox=chkAnulados);

?>
<br>