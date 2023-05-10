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
/*************************************************************************************/
/*                                                                                   */
/*************************************************************************************/
$ruta_raiz = "../..";

session_start();
include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/funciones.php";
include_once "$ruta_raiz/obtenerdatos.php";
$lista_codi = 0+$_POST["txt_lista_codi"];
$lista_nombre = limpiar_sql($_POST["txt_lista_nombre"]);
$lista_descripcion = limpiar_sql($_POST["txt_lista_descripcion"]);
$lista_orden = 0+$_POST["txt_lista_orden"];
$usuarios_lista = limpiar_sql($_POST["txt_usuarios_lista"]);
$lista_tipo = $_SESSION["usua_codi"];
//echo $_POST["txt_lista_estado"];
$lista_estado = 0+$_POST["txt_lista_estado"];



if($_SESSION["usua_admin_sistema"]==1) $lista_tipo = 0+$_POST["txt_lista_tipo"];

$record = array();


if ($lista_codi==0){
    $lista_codi = $db->conn->nextId('lista_lista_id_seq');
    $record["LISTA_USUA_CODI"] = $_SESSION["usua_codi"];
}else{
    $sql = "select * from lista where lista_codi=$lista_codi";  
    //echo $sql;
    $rs = $db->conn->Execute($sql);
    $record["LISTA_USUA_CODI"] = $rs->fields["USUA_CODI"];
}
$record["LISTA_CODI"] = $lista_codi;
$record["INST_CODI"] = $_SESSION["inst_codi"];
$record["LISTA_NOMBRE"] = $db->conn->qstr($lista_nombre);
$record["LISTA_DESCRIPCION"] = $db->conn->qstr($lista_descripcion);
$record["USUA_CODI"] = $lista_tipo;
$record["LISTA_FECHA"] = $db->conn->sysTimeStamp; // Fecha de última modificación de la lista
$record["LISTA_ORDEN"] = $lista_orden;



//PROCESO PARA BORRAR
//verifico si tiene permiso de admin
if ($lista_estado==0){
$msj_elim = "Usted no tiene permisos de eliminación de Listas.<br>";
$msj_elim2 ="Consulte con el Administrador.";
if($_SESSION["usua_admin_sistema"]==1)//puede hacer lo que desee   
   if ($_SESSION['usua_perm_listas']==1)//si tiene el permiso de listas
    $record["LISTA_ESTADO"] = $lista_estado;
   else
       $mensaje = $msj_elim;
else{
    if ($_SESSION['usua_perm_listas']==1)//si tiene el permiso de admin de listas
        $record["LISTA_ESTADO"] = $lista_estado;
    else{
        //verificamos si el usuario es dueño
            $sql="select * from listas where usua_codi = ".$_SESSION["usua_codi"]."
                and lista_codi = $lista_codi";
            $rs=$db->conn->Execute($sql);
            if (!$rs->EOF)
                    $record["LISTA_ESTADO"] = $lista_estado;
            else{
                $mensaje = $msj_elim.$msj_elim2;            
            }
        }        
    }
}else{
    $record["LISTA_ESTADO"] = 1;
}
$ok1 = $db->conn->Replace("LISTA", $record, "LISTA_CODI", false,false,true,false);

unset($record);
$sql = "delete from lista_usuarios where lista_codi=$lista_codi";
$db->conn->Execute($sql);
//echo "<hr>".$sql;

if ($lista_orden == 0) { // Si se debe ordenar alfabeticamente
    $sql = "select usua_codi, usua_nombre from usuario where usua_codi in (".str_replace("-", "", str_replace("--", ",", $usuarios_lista)).") order by usua_nombre";
    $rs = $db->conn->Execute($sql);
    if ($rs) {
        $record["LISTA_CODI"] = $lista_codi;
        $orden = 0;
        while (!$rs->EOF) {
            $record["USUA_CODI"] = $rs->fields["USUA_CODI"];
            $record["ORDEN"] = $orden;
            $ok1 = $db->conn->Replace("LISTA_USUARIOS", $record, "", false,false,true,false);
            ++$orden;
            $rs->MoveNext();
        }
    }
} else { // Si se debe ordenar segun el orden de selección
    $record["LISTA_CODI"] = $lista_codi;
    $usuarios = explode("-",$usuarios_lista);
    $orden = 0;
    foreach ($usuarios as $usr)
    {
        if (trim($usr)!="") {
            $record["USUA_CODI"] = $usr;
            $record["ORDEN"] = $orden;
            $ok1 = $db->conn->Replace("LISTA_USUARIOS", $record, "", false,false,true,false);
            ++$orden;
        }
    }
}

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>
<body>
    <br><br>
    <center>
	<table width="40%" border="2" align="center" class="t_bordeGris">
	    <tr> 
		<td width="100%" height="30" class="listado2">
                    <?php 
                    if ($mensaje=="")
                        echo '<span class=etexto><center><B>Los cambios en la lista '.$lista_nombre.' se realizaron correctamente</B></center></span>';
                    else
                         echo '<font color="blue"><b><center>'.$mensaje.'</b></center></font>';
                    ?>
		</td> 
	    </tr>
	    <tr>	
		<td height="30" class="listado2">
                    <center><input class="botones" type="button" name="btn_aceptar" value="Aceptar" onclick="window.location='./listas.php'"></center>
		</td> 
	    </tr>
	</table>
    </center>
</body>
</html>
