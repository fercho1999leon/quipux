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
$ruta_raiz = "../..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if($_SESSION["usua_admin_sistema"]!=1) die("");

require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post

include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";


if(isset($_GET)){
    $usrCodigo = 0+limpiar_sql($_GET['usrCodigo']);
    $datosUsrI=array();
    $datosUsrI= ObtenerDatosUsuario($usrCodigo, $db);
    
    echo "<table width='100%'>";
    echo dibujarAreas($datosUsrI['institucion'], $db,$usrCodigo);
    echo "</table>";
}
function dibujarAreas($nombre_institucion,$db,$usrCodigo){
   $menu_depeHijo .= '<tr><td colspan="4" align="center" class="titulos4"><font size="2">'.$nombre_institucion.'</font></td></tr>';
    
   $sql ="select * from usuario_dependencia u    
            where usua_codi = $usrCodigo";
   
   $rsDepePadre=$db->conn->query($sql);
   $depeCodiTmp = $rsDepePadre->fields['DEPE_CODI_TMP'];
   $depeOrdenTmp = substr($depeCodiTmp,1);
   if ($depeCodiTmp!=''){
   $sql_orden= "select depe_codi,depe_codi_padre,depe_nomb from dependencia 
            where depe_codi in ($depeOrdenTmp) and depe_estado = 1";
   $sql_orden.=" group by depe_codi,depe_codi_padre,depe_nomb
            order by depe_codi_padre,depe_nomb";
   //echo $sql_orden;
   $rs=$db->conn->query($sql_orden);
   
   while(!$rs->EOF){
              $padre = $rs->fields['DEPE_CODI_PADRE'];
              
               if ($padre!=$padre2)
                   $menu_depeHijo .= '<tr><td class="titulos2"><font size="1">'. mostrarNombre($padre,$db).'</font></td></tr>';
                $menu_depeHijo .= '<tr><td class="listado2"><font size="1">'.$rs->fields["DEPE_NOMB"].'</font></td></tr>';
               $padre2 = $rs->fields['DEPE_CODI_PADRE'];
               $rs->MoveNext();                 
    }
   }
    return $menu_depeHijo; 
}
function mostrarNombre($depe_codi,$db){
    $sql="select depe_nomb from dependencia where depe_codi = $depe_codi";
    $rs=$db->conn->query($sql);
    if (!$rs->EOF)
            return $rs->fields["DEPE_NOMB"];
}
?>