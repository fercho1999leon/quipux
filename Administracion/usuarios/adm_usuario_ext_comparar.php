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
require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";

session_start();
//Cambio para desadocs VJ
 /*if($_SESSION["usua_admin_sistema"]!=1 and $_SESSION["usua_perm_ciudadano"]!=1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}*/

include_once "$ruta_raiz/rec_session.php";

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
        if ($rs->EOF) {
            die("<center><h3>No se encontró el usuario.</h3></center>");
        }
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
    }
}

 $sqlciudad = "select * from ciudad where id = $new_ciudad";
 $rsciudad = $db->conn->Execute($sqlciudad);
 $new_nombre_ciudad = $rsciudad->fields["NOMBRE"];
if (isset($_POST["old_codigo"])) {
    $sql = "select * from ciudadano where ciu_codigo=".limpiar_sql($_POST["old_codigo"]);
    $rs = $db->conn->Execute($sql);
    if ($rs->EOF) {
        die("<center><h3>No se encontró el usuario.</h3></center>");
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

    $sqlciudad = "select * from ciudad where id = $old_ciudad";
    $rsciudad = $db->conn->Execute($sqlciudad);
    $old_nombre_ciudad = $rsciudad->fields["NOMBRE"];
}

function comparar_campo($campo, $label, $tamano, $opciones="") {
    $opciones = "onKeyPress='if (event.keyCode==13) return false;'";
    global ${'new_'.$campo};
    global ${'old_'.$campo};    
    $cad = "<tr>
        <td width='10%' class='titulos3'>$label</td>
        <td width='35%' class='listado3'><input type='text' name='new_$campo' readonly='readonly' id='new_$campo' value='".${'new_'.$campo}."' size='50' maxlength='$tamano' $opciones></td>";
//        if ($campo=='nombre' || $campo=='apellido' || $campo=='direccion'){
//          $cad.="<td width='10%' class='tooltip' class='titulos3'>
//            <center><input type='button' name='btn_accion' class='botones_2' value='&gt;&gt;' onclick=\"mover_dato('$campo')\"/ title='De click para mover los datos..'>
//            <img src='../../iconos/copy.gif' alt='copiar' title='Copiar datos del Registro Civil' onclick=\"copiar_datos_registro_civil('$campo', 'old_$campo')\"/></center></td>";  
//        }
//        else
            $cad.="<td width='10%' class='tooltip' class='titulos3'><center><input type='button' name='btn_accion' class='botones_2' value='&gt;&gt;' onclick=\"mover_dato('$campo')\"/ title='De click para mover los datos..'></center></td>";
        
        $cad.="<td width='35%' class='listado3'><input type='text' name='old_$campo' id='old_$campo' value='".${'old_'.$campo}."' size='50' maxlength='$tamano' $opciones></td>";
      $cad.="</tr>";
    
    echo $cad;
    return;
//
}

function comparar_campo_ciudad($campo, $label, $tamano, $opciones="",$db) {

    global ${'new_'.$campo};
    global ${'old_'.$campo};
    global ${'new_nombre_'.$campo};
    global ${'old_nombre_'.$campo};
    /*$cad = "<tr>
        <td class='titulos3'>$label</td>
        <input type='text' name='new_$campo' id='new_$campo' value='".${'new_'.$campo}."' size='50' maxlength='$tamano' $opciones>
        <td class='listado3'><input type='text' name='new_nombre$campo' readonly='readonly' id='new_nombre$campo' value='".${'new_nombre_'.$campo}."' size='50' maxlength='$tamano' $opciones></td>
        <td class='tooltip' class='titulos3'><center><input type='button' name='btn_accion' class='botones_2' value='&gt;&gt;' onclick=\"mover_dato('$campo')\"/ title='De click para mover los datos..'></center></td>
        <input type='text' name='old_$campo' id='old_$campo' value='".${'old_'.$campo}."' size='50' maxlength='$tamano' $opciones>
        <td class='listado3'><input type='text' name='old_nombre$campo' readonly='readonly' id='old_nombre$campo' value='".${'old_nombre_'.$campo}."' size='50' maxlength='$tamano' $opciones></td>
      </tr>";

    echo $cad;*/
    $id_ciudad_new = ${'new_'.$campo};
    $sql = "select * from ciudad";
    $rs = $db->conn->Execute($sql);
    $cad_new = "<select name='new_$campo' id='new_$campo' class='select' disabled='disabled'>";
    while(!$rs->EOF){
    $id_ciudad = $rs->fields['ID'];
    $nombre_ciudad = $rs->fields['NOMBRE'];
                if ($id_ciudad_new==$id_ciudad)
                $cad_new.="<option value='$id_ciudad' selected>$nombre_ciudad</option>";
                else
                    $cad_new.="<option value='$id_ciudad'>$nombre_ciudad</option>";
             $rs->MoveNext();
    }
    $cad_new.="</select>";
    //
    $id_ciudad_old = ${'old_'.$campo};
    $cad_old = "<select name='old_$campo' id='old_$campo' class='select'>";
    //$sql = "select * from ciudad";
    $rs = $db->conn->Execute($sql);
    while(!$rs->EOF){
    $id_ciudad = $rs->fields['ID'];
    $nombre_ciudad = $rs->fields['NOMBRE'];
                if ($id_ciudad_old==$id_ciudad)
                $cad_old.="<option value='$id_ciudad' selected>$nombre_ciudad</option>";
                else
                    $cad_old.="<option value='$id_ciudad'>$nombre_ciudad</option>";
             $rs->MoveNext();
    }
    $cad_old.="</select>";
    echo "<tr>
            <td class='titulos3'>$label</td>
            <td class='listado3'>".$cad_new."</td>
            <td class='tooltip' class='titulos3'><center>
    <input type='button' name='btn_accion' class='botones_2' value='&gt;&gt;' onclick=\"mover_dato('$campo')\"/ title='De click para mover los datos..'></center></td>
            <td class='listado3'>".$cad_old."</td>
    </tr>";
    return;
//
}
?>

<html>
<? 
    echo html_head(); /*Imprime el head definido para el sistema*/
    $tmp_txt1 = "Datos ingresados para Nuevo Ciudadano";
    $tmp_txt2 = "Datos del Ciudadano a Editar";
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
    
        
   
    <body>        
        <center>
<!--            <h3>Comparar datos de los ciudadanos</h3>-->
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
                            <input type="text" name="new_cedula" id="new_cedula" value="<?=$new_cedula?>" size="20" maxlength="13" onblur='cambio_cedula(this,1)'>
                        <?php } ?>
                    </td>
                    <td class='titulos3'>
                        <? if (substr($new_cedula,0,2)!="99" and $new_sincedula != 1) { ?>
                            <center><input type='button' name='btn_accion' class='botones_2' value='&gt;&gt;' onclick="mover_dato('cedula')"/></center></td>
                        <? } ?>
                    <td class='listado3'>
                        <input type="text" name="old_cedula" id="old_cedula" value="<?=$old_cedula?>" onblur='cambio_cedula(this,2)' size="20" maxlength="13"
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
<?
                comparar_campo("documento", "Documento", 50);
                comparar_campo("nombre", "Nombre", 150); //, "onKeyUp='this.value=this.value.toUpperCase();'");
                comparar_campo("apellido", "Apellido", 150); //, "onKeyUp='this.value=this.value.toUpperCase();'");
                comparar_campo("titulo", "T&iacute;tulo", 100);
                comparar_campo("abr_titulo", "Abr. T&iacute;tulo", 30);
                comparar_campo_ciudad("ciudad", "Ciudad", 50,"",$db);
                comparar_campo("empresa", "Instituci&oacute;n", 150);
                comparar_campo("cargo", "Cargo", 150);
                comparar_campo("direccion", "Direcci&oacute;n", 150);
                comparar_campo("email", "Email", 50);
                comparar_campo("telefono", "Tel&eacute;fono", 50);
?>
            </table>

<?
        if (!isset($_POST["flag_desactivar"])) {
?>
            <br />
            <table width="100%" border="0">
              <tr>
                <?php if ($flag_comparar) { ?>
                <td align="center">
                    <input name="btn_cancelar" type="button" class="botones_largo" value="Rechazar Cambios" onClick="rechazar_cambios();"/>
                </td>
                <?php }
                if($_SESSION["usua_codi"]==0) {
                ?>
                <td>
                    <center>
                        <input name="btn_aceptar" type="button" class="botones_largo"  value="Crear Ciudadano" onClick="return crear_nuevo();"/>
                    </center>
                </td>
                <?php
                }
                ?>
                <td align="center">
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
