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
/**
*	Autor			Iniciales		Fecha (dd/mm/aaaa)
*       David Gamboa            DG			12-05-2011
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
*	David Gamboa            DG			19-05-2011
**/
$ruta_raiz = "../..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "../usuarios_dependencias/refrescarArbol.php";
echo "<html>".html_head();
?>

<script type='text/JavaScript'>
function cerrar(){
    window.close();
    opener.refrescar_pagina();
}
</script>

<html>
<body>
<?php


$txt_depe_codi = 0+$_GET['depe_codi'];

$txt_opcion_activar=0+$_GET['estado']; 
$accion = $_GET['accion'];

$gd=obtenerCodigos($_SESSION['usua_codi'],$txt_depe_codi,$db,1);

if ($gd==0){
    if ($_SESSION["perm_admin_institucional"]==1)
    $gd=1;
}

$error = "";
//Permite grabar el area en la base de datos con todos sus atributos
if ($txt_depe_codi){

    $record = array();
    $db->conn->BeginTrans();
    $txtIdDep = $txt_depe_codi;
    //echo $txtIdDep;
    $record['DEPE_CODI'] = limpiar_sql($txtIdDep);
    if ($txt_opcion_activar==0)
    $record['DEPE_ESTADO'] = 0;
    else
        $record['DEPE_ESTADO'] = 1;
    //COMPRUEBA SI TIENE USUARIOS
     $sqlDependencia = "select count(*) as nroudependencia from usuarios where depe_codi =".$record['DEPE_CODI']." and usua_esta = 1";         
     $rs1=$db->conn->query($sqlDependencia);         
     $sql_n = "select depe_nomb,dep_sigla from dependencia where depe_codi = ".$record['DEPE_CODI'];
     
   $rst = $db->conn->query($sql_n);
   if ($rs1->fields['NROUDEPENDENCIA']==0){//cuantos usuarios existen en el area
     if ($txt_opcion_activar==1){//si deseo activar
       //verifico k no existan areas con el mismo nombre y sigla
       $sqlRepetido = "select depe_nomb,dep_sigla,depe_codi,depe_estado from dependencia";
       $sqlRepetido.= " where (depe_nomb = '".$rst->fields["DEPE_NOMB"]."'";
       $sqlRepetido.= " or dep_sigla = '".$rst->fields["DEP_SIGLA"]."')";
       $sqlRepetido.= " and depe_codi<>".$txtIdDep;
       $sqlRepetido.= " and depe_estado = 1";
       $sqlRepetido.= " and inst_codi = ".$_SESSION['inst_codi'];
       //echo $sqlRepetido;
       $rsRepetido = $db->conn->query($sqlRepetido);
       $codigoEncontrado = $rsRepetido->fields["DEPE_CODI"];       
       
       if (trim($codigoEncontrado)==''){
            
            if ($gd==1)//si tiene permisos para modificar
            $ok1 = $db->conn->Replace("DEPENDENCIA", $record, "DEPE_CODI", false,false,true,false);
       }
       else          
           $Arearepetida = 1;
     }else{//si deseo desactivar
         //verifico el area que no tenga subareas, si tiene subareas que tenga con usuarios
         //deseactivados
         //consulta subareas
        $sqlSubareas = "select depe_codi,depe_estado from dependencia where depe_codi_padre = ".$txtIdDep;
        $sqlSubareas.= " and depe_codi <> ".$txtIdDep;
        $sqlSubareas.= "and inst_codi = ".$_SESSION['inst_codi'];
        $rsSubareas=$db->conn->query($sqlSubareas);
        $okSub=0;
        while(!$rsSubareas->EOF){
          $codigoSubarea=$rsSubareas->fields["DEPE_CODI"];
          $estadoSubarea=$rsSubareas->fields["DEPE_ESTADO"];
          if ($estadoSubarea==1)
              $okSub = 1;//encontro subareas activas.
          $rsSubareas->MoveNext();
        }
        if ($okSub==0){//si es cero, hay subareas desactivadas
            if ($gd==1)//si tiene permisos para modificar            
            $ok1 = $db->conn->Replace("DEPENDENCIA", $record, "DEPE_CODI", false,false,true,false);
        }
     }
     
       if(!$ok1) $error = "Error al eliminar el área";

       if ($ok1) {
       
       
        if ($txt_opcion_activar==0)
            $mensaje="El área <font size='2' color='black'><u>".$rst->fields["DEPE_NOMB"]." </u></font>ha sido Desactivada satisfactoriamente";
        else
            $mensaje="El área <font size='2' color='black'><u>".$rst->fields["DEPE_NOMB"]." </u></font>ha sido Activada satisfactoriamente";
	$db->conn->CommitTrans();
        }else{           
            if ($Arearepetida == 1)
                $mensaje = "Existe un área ACTIVA con el mismo nombre (".$rst->fields["DEPE_NOMB"].") o misma sigla (".$rst->fields["DEP_SIGLA"].")";
            elseif($okSub==1)
                $mensaje = "No se puede Desactivar El área (".$rst->fields["DEPE_NOMB"].") tiene subáreas Activas.";
               else
                   if ($gd!=1)
                       $mensaje = "No se puede modificar El área  (".$rst->fields["DEPE_NOMB"]."), usted no tiene permisos para modificar el área.";
                   else
                        $mensaje = "Ha ocurrido un problema, comuníquese con el Administrador General del Sistema";
            $db->conn->RollbackTrans();
        }
   }else{
       $mensaje=" No se puede desactivar el área <u>".$rst->fields["DEPE_NOMB"]." </u>Tiene <font size='2' color='blue'>".$rs1->fields['NROUDEPENDENCIA']." Usuarios Registrados </font>";
   }
}
    echo '<form name="frmConfirmaCreacion" action="adm_dependencias_nuevo.php?accion='.$accion.'&des_activar=3" method="post">';
    echo '<center>
            <table width="100%" border="0" align="center" class="t_bordeGris">
	    <tr><td colspan="2"></td></tr>
            <tr> 
		<td width="100%" height="30" class="listado2">
		    <font size="1" color="black">'.$mensaje.'</font> 
		</td> 
	    	
		<td height="30" class="listado2">
			<center><input type="button" value="Cerrar" class="botones_largo" onclick="cerrar()"></center>
		</td> 
	    </tr>
	</table>
    </center>
    </form>';
    ?>
    </body>
    </html>

