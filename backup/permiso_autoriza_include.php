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

 
 $usr_inst_actual = $_SESSION["inst_codi"];
 $usr_usua_codi = $usr_codigo;
 $permiso = 33;
// $usr_usua_codi = limpiar_numero($_GET["dat"]);
// $permiso = limpiar_numero($_GET["per"]);

include_once "$ruta_raiz/Administracion/usuarios/mnuUsuariosH.php";
  //Se consulta áreas
  $sql = "select dep.DEPE_CODI, dep.DEPE_NOMB, permiso_usr_dep.* from dependencia as dep
          left outer join (select depe_codi as permiso_asignado
          from permiso_usuario_dep
	  where usua_codi = $usr_usua_codi
	  and id_permiso = $permiso) as permiso_usr_dep
          on  dep.depe_codi = permiso_usr_dep.permiso_asignado
          where dep.inst_codi = $usr_inst_actual and dep.depe_estado = 1
          order by dep.DEPE_NOMB";
  //echo $sql;
   $rs = $db->conn->Execute($sql);
   
?>
<script type="text/javascript" src="<?=$ruta_raiz?>/ciudadanos/adm_ciudadanos.js"></script>
<script language="JavaScript" type="text/javascript" >
   function metodoSeleccionarTodo(accion)
   {   
       permiUsr = document.getElementById('txt_depe_guardar')
       elms = document.getElementsByName("dependencia[]");
       for(i=0;i<elms.length;i++){
        if(elms[i].type=="checkbox")
            elms[i].checked = accion;
            cargarPermiso(elms[i].checked,elms[i].value,'txt_depe_guardar','txt_depe_guardar_eli');
        }
   }
   
</script>
<center>
<form id="formulario" name="formulario" action="" method="POST">
    <table width="100%" class="borde_tab" border="1">
        <tr>
            <td>
                <?php echo graficarTabsMenuUsr($usr_codigo,$tiene_subrogacion,$usr_perfil,$usr_depe);?>
            </td>
        </tr>
    </table>
    <?php 
    
            if ($ciud->buscarPermisoUsr(33, $usr_codigo)==0){ 
               ?>
    <table border="1" class="borde_tab" width="100%">
                <tr>
                    <td class="listado2" align="center">
                        <font color="black" size="2">Para ingresar a esta opción, agregue el permiso 
                        <i><b><?=$ciud->nombrePermiso(33)?></b></i>.
                        </font>
                    </td>
                </tr>
            </table>
    <?php
            }else{
?>
    
    
    <input type="hidden" name="txt_usua_codi" id="txt_usua_codi" value="<?php echo $usr_usua_codi; ?>" maxlength="10">
    <input type="hidden" name="txt_permiso" id="txt_permiso" value="<?php echo $permiso; ?>" maxlength="10">
    <div id="div_permiso_especial" name="div_permiso_especial" style="width:100%; height:350px; overflow:scroll;">
    <table width="100%" align="center" border="0" cellpadding="0" cellspacing="3" class="borde_tab">
        <tr><td class="titulos2" align="center">Permiso para aprobar solicitudes de respaldos</td></tr>
        <tr><td class="listado2" >Seleccione las áreas en las que el usuario aprobará solicitudes de respaldos:</td></tr>
        <tr><td class="listado2" align="right">
            <a href="javascript:;" onClick="metodoSeleccionarTodo(1);" class="Ntooltip"><font color="black">Marcar Todo </font></a>
            &nbsp;&nbsp;
            <a href="javascript:;" onClick="metodoSeleccionarTodo(0);" class="Ntooltip"><font color="black">Quitar Todo </font></a>
            </td>
        <tr><td class="listado1" >
        <?while (!$rs->EOF) {
            $fecha = date('Y-m-d h:m:s', strtotime($rs->fields["FECHA"])) . " " . $descZonaHoraria;
            $depe_codi = $rs->fields["DEPE_CODI"];
            $depe_nomb = $rs->fields["DEPE_NOMB"];
            $asignado = $rs->fields["PERMISO_ASIGNADO"];
            $checked = "";
            if($asignado != ""){
                $checked = "checked";                
                    $permisos_backup = $permisos_backup.",".$depe_codi.",";
            }
            $htmlfunper="onclick='cargarPermiso(this,$depe_codi,\"txt_depe_guardar\",\"txt_depe_guardar_eli\");'";
           ?>
        <input type="checkbox" id="dependencia" name="dependencia[]" value=<? echo $depe_codi ?>  <? echo $checked ?> <?=$htmlfunper?>/><? echo $depe_nomb ?>
        <br>       
        <? $rs->MoveNext();
         }
        ?>
        </td></tr>
     </table>
    </div>
    <br>    
    <input type="hidden" name="txt_depe_guardar" id="txt_depe_guardar" value="<?=$permisos_backup?>" />
    <input type="hidden" name="txt_depe_guardar_eli" id="txt_depe_guardar_eli" value="" />
<!--    <input type='submit' name='btn_grabar' value='Grabar' class='botones'>-->
<!--    <input  name="btn_accion" type="button" class="botones" value="Grabar" onclick="guardar();"/>
    <input type='button' name='btn_cancelar' value='Regresar' class='botones' onClick='window.close()'>-->
    <br><br>
    <?php } ?>
</form>
</center>