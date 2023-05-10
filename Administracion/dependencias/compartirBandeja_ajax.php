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
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());
session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
    include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "../usuarios_dependencias/refrescarArbol.php";
if ($_GET['dependencia'] != 0)
    $txt_depe_codi = $_GET['dependencia'];
else
    $slc_padre = $_GET['padre'];

if($txt_depe_codi!='')
{
    $tituloJefe = 'Datos del Jefe de &Aacute;rea';
    $depeCodi = $txt_depe_codi;
}
else
{
    $tituloJefe = 'Datos del Jefe del &Aacute;rea Padre';
    $depeCodi = $slc_padre;
}
//Obtener datos del Jefe de Área
$datosJefe = ObtenerJefeArea($_SESSION["inst_codi"], $depeCodi, '1', $db);

if($datosJefe['ciudad'])
{
    $codigoCiu = $datosJefe['ciudad'];
    $ciudad = ObtenerCiudadUsua(' ciudad ', ' id = '. $codigoCiu, $db);
}
?>
<br>
<?php //Comentado por nueva funcinalidad de administrar_jefe_ajax.php 
?>
 
<br>
<?php if(trim($datosJefe['usua_codi'])!='' and $_GET['accion'] == '2') { ?>
<table width="100%" class="borde_tab">
    <tr>
        <td align="center" class="titulos4" colspan="3"><font size="2">Compartir Bandeja de Documentos Recibidos</font></td>
    </tr>
    <tr>
        <td width="25%" align="center" class="titulos2">Compartir con</td>
        
        <td width="60%" align="center" class="titulos2">Lista de Usuarios </td>
    </tr>
    <tr>
        <td align="center" class="listado2">
        <?php
            $sql = "select
                         usua_nomb || ' ' || usua_apellido || ' ' ||
              case when usua_codi in 
              (select usua_subrogado from usuarios_subrogacion where usua_visible=1) = true then
              '(Subrogado)' else '' end || ' ' ||
              --Subrogante
              case when usua_codi in 
              (select usua_subrogante from usuarios_subrogacion where usua_visible=1) = true then
              '(Subrogante)' else '' end as usua_nombre,
                        usua_codi
                    from
                        usuario
                    where
                        usua_esta=1
                        and depe_codi=$depeCodi
                        and cargo_tipo <> 1 and usua_codi not in (select usua_codi from bandeja_compartida where usua_codi_jefe = ".$datosJefe['usua_codi'].")
                    order by 1 asc";
            //echo $sql;
            $rs = $db->conn->Execute($sql);
            
             $slMultiple = $rs->GetMenu2('usuarioSel[]', 0, false, true, 8, " id='usuario' class='select' ");             
             echo $slMultiple;
        ?>
        </td>
        <td valign="top" class="listado2">
            <table width="100%" class="borde_tab">
                <tr>
                    <td width="60%" align="center" class="titulos2">Nombre</td>
                    <td width="45%" align="center" class="titulos2">Puesto</td>
                    <td width="15%" align="center" class="titulos2">Acci&oacute;n</td>
                </tr>
                <?php

    $editar_area = obtenerCodigos($_SESSION['usua_codi'],$depeCodi,$db);
                $sql = 'select * from bandeja_compartida where usua_codi_jefe = '.$datosJefe['usua_codi'];
                //echo $sql;
                $rs = $db->conn->query($sql);
                while (!$rs->EOF) {
                    $datosUsua = ObtenerDatosUsuario($rs->fields["USUA_CODI"], $db);
                    echo '<tr>';
                        echo '<td align="left" class="listado2_ver">'.$datosUsua['nombre'].'</td>';
                        echo '<td align="left" class="listado2_ver">'.$datosUsua['cargo'].'</td>';
                          if (trim($editar_area)==1 || $_SESSION['usua_codi']==0 || $_SESSION['perm_admin_institucional']==1) 
                        echo '<td align="left" class="listado2"><input type="button" value="eliminar" onclick="compartir_bandeja('.$rs->fields["BAN_COM_CODI"].','.$depeCodi.',2);" class="botones"></td>';
                    echo '</tr>';
                    $rs->MoveNext();
                }
                ?>
            </table>
        </td>
    </tr>
    <tr>
        <td width="15%" align="center" class="listado2">
            <?php
            $fnjava= "compartir_bandeja('".$datosJefe['usua_codi']."','".$depeCodi."',1);";
              if (trim($editar_area)==1 || $_SESSION['usua_codi']==0 || $_SESSION['perm_admin_institucional']==1) 
            echo '<input type="button" class="botones" value="Aceptar" onclick="'.$fnjava.'">';
            ?>
        </td>
        <td colspan="2" class="listado2"></td>
    </tr>
</table>
<?php } ?>