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
*  Acceso Administrador
*  adm_usuario_ext_confirmar.php
 * adm_ciudadano_confirmar.php
 * adm_ciudadano_solconfirmar.php
 * adm_usuario_ext_combinar.php	
* *****************************************************************************************/
$ruta_raiz = "../..";
session_start();
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/rec_session.php";

include_once "../ciudadanos/util_ciudadano.php";
$ciud = New Ciudadano($db);

    $_POST["new_codigo"] = 0 + $_POST["new_codigo"];
    $_POST["old_codigo"] = 0 + $_POST["old_codigo"];    
if (isset($_POST["new_cedula"])) { //Creacion de ciudadanos
    $flag_comparar = false;
    $new_cedula     = limpiar_sql($_POST["new_cedula"]);
    $new_sincedula  = limpiar_sql($_POST["new_sincedula"]);
    $new_documento  = limpiar_sql($_POST["new_documento"]);
    $new_nombre     = limpiar_sql($_POST["new_nombre"]);
    $new_apellido   = limpiar_sql($_POST["new_apellido"]);
    $new_titulo     = limpiar_sql($_POST["new_titulo"]);
    $new_abr_titulo = limpiar_sql($_POST["new_abr_titulo"]);
    $new_empresa    = limpiar_sql($_POST["new_empresa"]);
    $new_cargo      = limpiar_sql($_POST["new_cargo"]);
    $new_direccion  = limpiar_sql($_POST["new_direccion"]);
    $new_email      = limpiar_sql($_POST["new_email"]);
    $new_telefono   = limpiar_sql($_POST["new_telefono"]);
    $new_ciudad     = limpiar_sql($_POST["new_ciudad"]);
} else { // Comparar datos editados por los usuarios
    
    if (!isset($_POST["flag_desactivar"]) && isset($_POST["old_codigo"])) // Para comparar datos modificados por el ciudadano
        $_POST["new_codigo"] = $_POST["old_codigo"];
    if (isset($_POST["new_codigo"])) {
        $flag_comparar = true;
        if (isset($_POST["flag_desactivar"])) //Para comparar datos entre ciudadanos ya creados (para eliminacion de ciudadanos)
            $sql = "select * from ciudadano where ciu_codigo=".limpiar_sql($_POST["new_codigo"]);
        else
            $sql = "select * from ciudadano_tmp where ciu_codigo=".limpiar_sql($_POST["new_codigo"]);    
        //echo $sql;
        $rs = $db->conn->Execute($sql);
        if ($rs->EOF) 
            die("");
        
        $new_cedula     = $rs->fields["CIU_CEDULA"];
        $new_codigo     = $rs->fields["CIU_CODIGO"];
        $new_documento  = $rs->fields["CIU_DOCUMENTO"];
        $new_nombre     = $rs->fields["CIU_NOMBRE"];
        $new_apellido   = $rs->fields["CIU_APELLIDO"];
        $new_titulo     = $rs->fields["CIU_TITULO"];
        $new_abr_titulo = $rs->fields["CIU_ABR_TITULO"];
        $new_empresa    = $rs->fields["CIU_EMPRESA"];
        $new_cargo      = $rs->fields["CIU_CARGO"];
        $new_direccion  = $rs->fields["CIU_DIRECCION"];
        $new_email      = $rs->fields["CIU_EMAIL"];
        $new_telefono   = $rs->fields["CIU_TELEFONO"];
        $new_ciudad     = $rs->fields["CIUDAD_CODI"];
        $new_pais     = $rs->fields["PAIS_CODI"];
        $new_provincia     = $rs->fields["PROVINCIA_CODI"];
        $new_canton     = $rs->fields["CANTON_CODI"];
        $new_referencia     = $rs->fields["CIU_REFERENCIA"];
        
    }
}

 
if (isset($_POST["old_codigo"])) {
    $sql = "select * from ciudadano where ciu_codigo=".limpiar_sql($_POST["old_codigo"]);
    //echo $sql;
    $rs = $db->conn->Execute($sql);
    if ($rs->EOF) {
        die("");
    }
    $old_cedula     = $rs->fields["CIU_CEDULA"];
    if (substr($old_cedula,0,2)=="99") $old_cedula = "";
    $old_codigo     = $rs->fields["CIU_CODIGO"];
    $old_documento  = $rs->fields["CIU_DOCUMENTO"];
    $old_nombre     = $rs->fields["CIU_NOMBRE"];
    $old_apellido   = $rs->fields["CIU_APELLIDO"];
    $old_titulo     = $rs->fields["CIU_TITULO"];
    $old_abr_titulo = $rs->fields["CIU_ABR_TITULO"];
    $old_empresa    = $rs->fields["CIU_EMPRESA"];
    $old_cargo      = $rs->fields["CIU_CARGO"];
    $old_direccion  = $rs->fields["CIU_DIRECCION"];
    $old_email      = $rs->fields["CIU_EMAIL"];
    $old_telefono   = $rs->fields["CIU_TELEFONO"];
    $old_nuevo      = $rs->fields["CIU_NUEVO"];
    $old_ciudad     = $rs->fields["CIUDAD_CODI"];
    $old_pais     = $rs->fields["PAIS_CODI"];
    
    $old_provincia     = $rs->fields["PROVINCIA_CODI"];
        $old_canton     = $rs->fields["CANTON_CODI"];
        $old_referencia     = $rs->fields["CIU_REFERENCIA"];
//echo $old_canton;
}
?>

<html>
<? 
    echo html_head(); /*Imprime el head definido para el sistema*/
    $tmp_txt1 = "<br>Datos Personales ingresados por el Ciudadano (otros usuarios)";
    $tmp_txt2 = "<br>Datos Personales Finales del Ciudadano";
    if (isset($_POST["flag_desactivar"])) {
        $tmp_txt1 = "Ciudadano por Desactivar";
        $tmp_txt2 = "Ciudadano Final";
    }   
?>
 <?php
 function imagen_imp($ruta_raiz,$cedula,$tipo){
         $funJava="ver_datos($tipo)";
        
  if (substr($cedula,0,2)=="99" || $cedula=='')
          $estilo="style='display: none;'";
  
  $html= '&nbsp<a href="javascript:;" onClick='.$funJava.' class="Ntooltip" >
                    <img name="img_reg'.$tipo.'" id="img_reg'.$tipo.'" '.$estilo.' src="'.$ruta_raiz.'/imagenes/icono_buscar.png" width="40" height="40" alt="Consultar Datos de Registro Civil" title="Consultar Datos de Registro Civil" border="0">
                        </span></a>';
  return $html;
 }
 ?>   
   
    <body onload="cargarGeografia();">        
        <center>

            <input type='hidden' name='old_codigo' id='old_codigo' value="<?=$old_codigo?>">
            <input type='hidden' name='old_nuevo' id='old_nuevo' value="<?=$old_nuevo?>">

            <br />
           
           
           <table width="100%" border="1" align="center" cellpadding="0" cellspacing="0" class="listado2">
                <tr>
                    <td class='titulos3' width="20%">&nbsp;</td>
                    <td bgcolor="#ffffff" width="35%"><center><?php if (substr($new_cedula,0,2)=="99" or $new_sincedula == 1) echo "No tiene No. de c&eacute;dula"."<br>"; else echo imagen_imp($ruta_raiz,$new_cedula,1);?>&nbsp;<?=$tmp_txt1?></center></td>
                    <td class='titulos3' width="10%">&nbsp;</td>
                    <td bgcolor="#ffffff" width="35%"><center><?php echo imagen_imp($ruta_raiz,$old_cedula,2);?>&nbsp;<?=$tmp_txt2?></center></td>
                </tr>
                <tr>
                    <td class='titulos3'>C&eacute;dula</td>
                    <td class='listado3'>
                        <?php 
                        if (substr($new_cedula,0,2)=="99" or $new_sincedula == 1){
                                echo "No tiene No. de c&eacute;dula";?>
                        <input type="hidden" name="new_cedula" id="new_cedula" value="" size="20" maxlength="13" onblur=''/>
                        <?php }
                            else { ?>
                            <input type="text" name="new_cedula" id="new_cedula" value="<?=$new_cedula?>" size="20" maxlength="13" onblur='cambio_cedulajs(this,1)'>
                        <?php } ?>
                    </td>
                    <td class='titulos3'>
                        <? if (substr($new_cedula,0,2)!="99" and $new_sincedula != 1) { ?>
                            <center><input type='button' name='btn_accion' class='botones_2' value='&gt;&gt;' onclick="mover_dato('cedula')"/></center></td>
                        <? } ?>
                    <td class='listado3'>
                        <input type="text" name="old_cedula" id="old_cedula" value="<?=$old_cedula?>" onblur='cambio_cedulajs(this,2)' size="20" maxlength="13"
<?
                        if ($flag_comparar) {
                            echo ">";
                        } else {
                            if (substr($old_cedula,0,2)=="99") echo "style='display:none'";
                            echo "><input type='checkbox' name='old_sincedula' id='old_sincedula' value='1' onchange='ver_cedula()'";
                            if (substr($old_cedula,0,2)=="99") echo "checked"; 
                            echo ">No tiene No. de c&eacute;dula";
                        }
?>
                    </td>
                </tr>
                
                </tr>
<?
                $ciud->comparar_campo("documento",$new_documento,$old_documento, "Documento", 50);
                $ciud->comparar_campo("nombre", $new_nombre,$old_nombre,"Nombre", 150); //, "onKeyUp='this.value=this.value.toUpperCase();'");
                $ciud->comparar_campo("apellido", $new_apellido,$old_apellido,"Apellido", 150); //, "onKeyUp='this.value=this.value.toUpperCase();'");
                $ciud->comparar_campo("titulo", $new_titulo,$old_titulo,"T&iacute;tulo", 100);
                $ciud->comparar_campo("abr_titulo", $new_abr_titulo,$old_abr_titulo,"Abr. T&iacute;tulo", 30);
               
               
                $ciud->comparar_campo_ciudad("ciudad", $new_ciudad,$old_ciudad,"Ciudad", 50);
                
                $ciud->comparar_campo("empresa", $new_empresa,$old_empresa,"Instituci&oacute;n", 150);
                $ciud->comparar_campo("cargo", $new_cargo,$old_cargo,"Puesto", 150);
                $ciud->comparar_campo("direccion", $new_direccion,$old_direccion,"Direcci&oacute;n", 150);
                $ciud->comparar_campo("referencia", $new_referencia,$old_referencia,"Referencia", 150);
                $ciud->comparar_campo("email", $new_email,$old_email,"Email", 50);
                $ciud->comparar_campo("telefono", $new_telefono,$old_telefono,"Tel&eacute;fono", 50);
?>
                
            
                
                  
                    
           
           
            </table>
 <?php             
            $nuevo_cod = 0+$_POST["new_codigo"];//nuevo usuario
            echo $ciud->verHistorico($nuevo_cod,1,'4,1','div_historico_nuevo');
            ?>
<?
        if (!isset($_POST["flag_desactivar"])) {
?>
            <br />
            <table width="100%" border="0">
              <tr>                  
                  <?php if ($flag_comparar) {//va al menu?>
                <td align="center">
                    <input name="btn_cancelar" type="button" class="botones_largo" value="Rechazar Cambios" onClick="rechazar_cambios();"/>
                </td>
                <?php }?>
                  <td align="center" colspan="2">
                    <input name="btn_aceptar2" id="btn_aceptar2" type="button"  style="visibility: hidden" class="botones_largo" value="Aceptar Cambios" onClick="aceptar_cambios();"/>
                  </td>
              </tr>
            </table>
<?
        }
?>
            
        </center>
    </body>
</html>
