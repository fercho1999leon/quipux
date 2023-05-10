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
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_lst_listas_datos_lista!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_lst_listas_datos_lista);

$lista_codi = 0+$_POST["txt_lista_codi"];
$usuarios_lista = "";
//permiso para listas
$usrPermisoAdm = $_SESSION['usua_perm_listas'];
if ($lista_codi != 0) {
    $sql = "select * from lista where lista_codi=$lista_codi";  
    //echo $sql;
    $rs = $db->conn->Execute($sql);
    $lista_nombre = $rs->fields["LISTA_NOMBRE"];
    $lista_descripcion = $rs->fields["LISTA_DESCRIPCION"];
    $lista_orden = $rs->fields["LISTA_ORDEN"];
    $lista_tipo = $rs->fields["USUA_CODI"];
    $lista_estado = $rs->fields["LISTA_ESTADO"];   
    
    $lista_usua_codi = $rs->fields["LISTA_USUA_CODI"];
    
    if (!($rs->fields["INST_CODI"]==$_SESSION["inst_codi"] and (($lista_tipo==0 and $_SESSION["usua_admin_sistema"]==1) or $rs->fields["USUA_CODI"]==$_SESSION["usua_codi"])))
        if ($usrPermisoAdm!=1)
            die ("Usted no tiene permiso para editar esta lista");
    
    $sql = "select usua_codi from lista_usuarios where lista_codi=$lista_codi order by orden asc";
    $rs = $db->conn->Execute($sql);
    while (!$rs->EOF) {
        $usuarios_lista .= "-".$rs->fields["USUA_CODI"]."-";
        $rs->MoveNext();
    }
} else {
    $lista_nombre = "";
    $lista_descripcion = "";
    $lista_orden = 0;
    $lista_tipo = $_SESSION["usua_codi"];
    if ($_SESSION["usua_admin_sistema"]==1) $lista_tipo = 0;
    
}
?>
<?php 


?>
<textarea id="txt_usuarios_lista" name="txt_usuarios_lista" style="display: none" cols="10" rows="1"><?=$usuarios_lista?></textarea>

<table width="100%"class="borde_tab">
    <tr>
        <th colspan="4"><center>Creaci&oacute;n y Modificaci&oacute;n de Listas de Env&iacute;o</center></th>
    </tr>
    <tr>
        <input type="hidden" name="txt_lista_estado" id="txt_lista_estado" value="<?=$lista_estado?>"/>
        <td class="titulos2" width="15%">* Seleccione la lista</td>
        <td class="listado2" colspan="3">
<?
if ($usrPermisoAdm==1){//tiene permisos de administracion de listas
     $sql = "select lista_nombre, lista_codi from lista where inst_codi=".$_SESSION["inst_codi"];
     $sql.=" and lista_estado = 1 order by lista_nombre asc";
    }else{//si no tiene muestra las listas
        $sql = "select lista_nombre, lista_codi from lista where inst_codi=".$_SESSION["inst_codi"]." and (usua_codi=".$_SESSION["usua_codi"];
        if ($_SESSION["usua_admin_sistema"]==1) $sql .= " or usua_codi=0";
        $sql .= ") ";   
            $sql.=" and lista_estado = 1";
        $sql.=" order by lista_nombre asc";
             }
    //echo $sql;
    $rs = $db->conn->Execute($sql);
    echo $rs->GetMenu2("txt_lista_codi", $lista_codi, "0:&lt;&lt; Crear Nueva Lista &gt;&gt;", false, "", "id='txt_lista_codi' class='select' Onchange='cargar_datos_lista(this.value);' style='width: 420px;'" );
?>
        </td>
        
        </tr>
    <tr>
        <td class="titulos2" width="15%">* Nombre </td>
        <td class="listado2" width="35%">
            <input type="text" name="txt_lista_nombre" id="txt_lista_nombre" value="<?= $lista_nombre ?>" size="50" maxlength="200">
        </td>
        <td class="titulos2" width="15%">* Tipo de Lista</td>
        <td class="listado2" width="35%"> 
<?php  
           //si es administrador tiene la opcion de asignar a cualquier persona
           if ($_SESSION["usua_admin_sistema"]==1 || $_SESSION['usua_perm_listas']==1){ ?>
        
        <?php
               $depe_codi_admin = obtenerAreasAdmin($_SESSION["usua_codi"],$_SESSION["inst_codi"],$_SESSION["usua_admin_sistema"],$db);
               
               $sql_usr = "select usua_apellido || ' ' || usua_nomb as usua_nombre,usua_codi 
                   from usuarios where usua_esta = 1";
                if ($depe_codi_admin!=0)
               $sql_usr.=" and depe_codi in ($depe_codi_admin)";
               $sql_usr.=" and usua_codi <> 0 and inst_codi = ".$_SESSION["inst_codi"]." order by usua_apellido";
               $rs_u = $db->conn->Execute($sql_usr);               
               echo $rs_u->GetMenu2("txt_lista_tipo", $lista_tipo, "0: Pública ", false, "", "id='txt_lista_tipo' class='select' style='width: 200px;'" );
               echo "(Personal / Pública)";
           ?>
        
         <?php }else{
               if ($_SESSION["usua_admin_sistema"]==1 || $usrPermisoAdm==1){ ?>
            <select name="txt_lista_tipo" id="txt_lista_tipo" class="select">
                <option value='<?=$_SESSION["usua_codi"]?>' selected>Personal</option>
                    <? echo '<option value="0" ';
                    if ($lista_tipo == 0) echo "selected";
                    echo ">P&uacute;blica</option>";
             ?></select>
                    <?php
        }else{ ?>
             <select name="txt_lista_tipo" id="txt_lista_tipo" class="select">
                <option value='<?=$_SESSION["usua_codi"]?>' selected>Personal</option>
             </select>
        <?php         
            }
            
         } ?>
            </td>
    </tr>
    <tr>
        <td class="titulos2" width="15%">Descripci&oacute;n </td>
        <td colspan="3" class="listado2" width="35%">
            <input type="text" name="txt_lista_descripcion" id="txt_lista_descripcion" value="<?= $lista_descripcion ?>" size="50" maxlength="200">
        </td>

<!--        <td class="titulos2" width="15%">Orden</td>
        <td class="listado2">
            <select name="txt_lista_orden" id="txt_lista_orden" class="select" onchange="cargar_lista_usuarios()">
                <option value='0' selected>Alfab&eacute;tico</option>
                <option value='1' <? if ($lista_orden == 1) echo "selected"; ?>>En orden de selecci&oacute;n</option>
            </select>
             Orden en el que se mostrar&aacute;n los usuarios en el documento
        </td>     -->
    </tr>
    <tr>
        <td class="titulos2" width="15%">Creado Por</td>
        <?php //historico?>
        <td class="listado2" width="15%" colspan="3">
        <?php
            if ($lista_usua_codi=='')
                $lista_usua_codi = $lista_tipo;
            $usr_duenio =array();
            $usr_duenio=ObtenerDatosUsuario($lista_usua_codi, $db);
            echo $usr_duenio["usua_apellido"]." ".$usr_duenio["usua_nombre"];
        ?></td>
    </tr>
    
</table>
<br>
