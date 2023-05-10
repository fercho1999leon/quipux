<?
/**  Programa para el manejo de gestion documental, oficios, memorandus, circulares, acuerdos
 *    Desarrollado y en otros Modificado por la SubSecretaría de Informática del Ecuador
 *    Quipux    www.gestiondocumental.gov.ec
 * ------------------------------------------------------------------------------
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
 * ------------------------------------------------------------------------------
 * */
/* *****************************************************************************************
 * * Administración de datos por parte del ciudadano                                      **
 * ****************************************************************************************/
$ruta_raiz = "../..";
session_start();
include_once "$ruta_raiz/rec_session.php";
$ciu_login = limpiar_sql($_SESSION["krd"]);

require_once "$ruta_raiz/funciones_interfaz.php";

//Busca ciudadano por login
$sql = "select * from ciudadano where ciu_cedula='" . substr($ciu_login, 1) . "'";
$rs = $db->conn->query($sql);
if (!$rs or $rs->EOF) {
    //Si no existe lo busca por codigo
    $sql = "select * from ciudadano where ciu_codigo=" . $_SESSION["usua_codi"] . "";
    $rs = $db->conn->query($sql);
    if (!$rs or $rs->EOF) {
        echo html_error("No se encontr&oacute; el usuario en el sistema.");
        die("");
    }
}

$ciu_nuevo = $rs->fields["CIU_NUEVO"];
// Si el ciudadano ya había cambiado sus datos anteriormente se muestran los que el cambió
$sql = "select * from ciudadano_tmp where ciu_codigo=" . $rs->fields["CIU_CODIGO"]." and ciu_estado = 1";
$rs2 = $db->conn->query($sql);
if ($rs2 && !$rs2->EOF) {
    $rs = $rs2;
    $ciuCodigoTmp = $rs2->fields["CIU_CODIGO"];
}
$sql = "select sol_estado from solicitud_firma_ciudadano where ciu_codigo=" . $rs->fields["CIU_CODIGO"]."";

$rs2 = $db->conn->query($sql);
if ($rs2 && !$rs2->EOF) {    
    $sol_estado = $rs2->fields["SOL_ESTADO"];    
        
}
//comprobar si el ciudadano está en temporal
if ($ciuCodigoTmp!='')
echo "<script>window.location='adm_datos_temporales.php'</script>";    
   
$ciu_codigo = $rs->fields["CIU_CODIGO"];
$ciu_cedula = $rs->fields["CIU_CEDULA"];
if (substr($ciu_cedula, 0, 2) == 99)
    $ciu_cedula = "";
$ciu_documento = $rs->fields["CIU_DOCUMENTO"];
$ciu_nombre = $rs->fields["CIU_NOMBRE"];
$ciu_apellido = $rs->fields["CIU_APELLIDO"];
$ciu_titulo = $rs->fields["CIU_TITULO"];
$ciu_abr_titulo = $rs->fields["CIU_ABR_TITULO"];
$ciu_empresa = $rs->fields["CIU_EMPRESA"];
$ciu_cargo = $rs->fields["CIU_CARGO"];
$ciu_direccion = $rs->fields["CIU_DIRECCION"];
$ciu_email = $rs->fields["CIU_EMAIL"];
$ciu_telefono = $rs->fields["CIU_TELEFONO"];
$ciu_ciudad = $rs->fields["CIUDAD_CODI"];

echo html_head(); /* Imprime el head definido para el sistema */
require_once "$ruta_raiz/js/ajax.js"; // teya 20110421
?>
<script type="text/javascript" src="<?= $ruta_raiz ?>/js/formchek.js"></script>
<script type="text/javascript" src="<?= $ruta_raiz ?>/js/validar_datos_usuarios.js"></script>
<script type="text/javascript">

    function ValidarInformacion()
    {
//        if (!validarCedula(document.getElementById('ciu_cedula').value)) {
//            alert ('Verifique su número de cédula.');
//            return false;
//        }
//        if(ltrim(document.forms[0].ciu_nombre.value)=='' || ltrim(document.forms[0].ciu_apellido.value)=='')
//        {	alert("Los campos de Nombres y Apellidos son obligatorios.");
//            return false;
//        }
//        if (!isEmail(document.forms[0].ciu_email.value,true) || ltrim(document.forms[0].ciu_email.value)=='')
//        {	alert("El campo Email no tiene formato correcto.");
//            return false;
//        }

        //Cola de mensajes de error:
        msg = '';

        //Evalúa, en base a la condición, si agrega el mensaje entre los errores o no:
        function e(condicion, mensaje) {
            msg = (condicion) ? msg + mensaje + ' \n' : msg;
        }

        e(!validarCedula(trim(document.forms[0].ciu_cedula.value)), "Verifique su número de cédula.");
        e(trim(document.forms[0].ciu_nombre.value) == '', "Ingrese los nombres.");
        e(trim(document.forms[0].ciu_apellido.value) == '', "Ingrese los apellidos.");
        e(!isEmail(document.forms[0].ciu_email.value,true) || trim(document.forms[0].ciu_email.value)=='', "El campo Email no tiene formato correcto.");

        if(msg != '' )
        {
            alert(msg);
            return false;
        }

        if (msg == '' && !validar_datos_registro_civil('ciu_nombre','ciu_apellido')) return false;

        document.formulario.submit();

        return true;
    }

    function validar_cambio_cedula() {
        cedula = document.getElementById('ciu_cedula').value;
        nuevoAjax('div_datos_registro_civil', 'POST', 'validar_datos_registro_civil.php', 'cedula='+cedula);
        if ('<?=$_SESSION["tipo_usuario"]?>' == '1')
            nuevoAjax('div_datos_usuario_multiple', 'POST', 'validar_datos_usuario_multiple.php', 'usr_codigo=<?=$ciu_codigo?>&cedula='+cedula);
    }

    function copiar_datos_registro_civil(campo_rc, campo_usr) {
        try {
            document.getElementById(campo_usr).value = document.getElementById('lbl_datos_rc_'+campo_rc).innerHTML;
        } catch (e) {}
    }
    function descargar_contenido() {
        path='<?=$path_acuerdo?>';
        windowprops = "top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=500,height=300";
//
        url = "<?=$ruta_raiz?>/descargar_contenidos.php?archivo="+path;
        window.open(url , "Vista_Previa_Acuerdo", windowprops);
        return;
    }
</script>

<body onload="validar_cambio_cedula()">
    <div id="wrapper">
    <center>
        <form name='formulario' action="adm_ciudadano_grabar.php" method="post">
            <input type='hidden' name='ciu_codigo' value="<?= $ciu_codigo ?>">

            <br>
            <table width="100%" border="1" class="borde_tab">
                <tr>
                    <th>
                        <center>Administraci&oacute;n de Ciudadanos</center>
                    </th>
                </tr>
            </table>
            <br>

            <div id="div_datos_registro_civil" style="width: 100%;"></div>
            <div id="div_datos_usuario_multiple" style="width: 100%;"></div>

            <!--<div id="div_validar_email" style="display: none;" ><? if (validar_mail($ciu_email)) echo "OK"; ?></div>-->
            <table width="100%" border="1" align="center" class="borde_tab">
                <?

                function dibujar_campo($campo, $label, $tamano, $opciones="") {
                    global $$campo;

                    $cad = "<td class='titulos2' width='20%' align='left'> $label </td>
                            <td class='listado3' width='30%'>
                            <input type='text' name='$campo' id='$campo' value='" . $$campo . "' size='55' maxlength='$tamano' class='caja_texto' $opciones>
                            </td>";

                    echo $cad;
                    return;
                }

                echo "<tr>";
                dibujar_campo("ciu_cedula", "* C&eacute;dula/RUC: ", 13, "onChange='validar_cambio_cedula()'");
                dibujar_campo("ciu_documento", "Otro Documento", 50);
                echo "</tr><tr>";
                $label = "* Nombre: &nbsp;&nbsp;&nbsp;<img src=\"$ruta_raiz/iconos/copy.gif\" alt=\"copiar\" title=\"Copiar datos del Registro Civil\" onclick=\"copiar_datos_registro_civil('nombre', 'ciu_nombre')\">";
                dibujar_campo("ciu_nombre", $label, 150); //, "onKeyUp='this.value=this.value.toUpperCase();'");
                $label = "* Apellido: &nbsp;&nbsp;&nbsp;<img src=\"$ruta_raiz/iconos/copy.gif\" alt=\"copiar\" title=\"Copiar datos del Registro Civil\" onclick=\"copiar_datos_registro_civil('nombre', 'ciu_apellido')\">";
                dibujar_campo("ciu_apellido", $label, 150); //, "onKeyUp='this.value=this.value.toUpperCase();'");
                echo "</tr><tr>";
                dibujar_campo("ciu_titulo", "T&iacute;tulo: ", 100);
                dibujar_campo("ciu_abr_titulo", "Abr. T&iacute;tulo", 30);
                echo "</tr><tr>";
                dibujar_campo("ciu_empresa", "Instituci&oacute;n: ", 150);
                dibujar_campo("ciu_cargo", "Puesto", 150);
                echo "</tr><tr>";
                $label = "Direcci&oacute;n: &nbsp;&nbsp;&nbsp;<img src=\"$ruta_raiz/iconos/copy.gif\" alt=\"copiar\" title=\"Copiar datos del Registro Civil\" onclick=\"copiar_datos_registro_civil('direccion', 'ciu_direccion')\">";
                dibujar_campo("ciu_direccion", $label, 150);
                dibujar_campo("ciu_email", "* Email: ", 50);
                echo "</tr><tr>";
                dibujar_campo("ciu_telefono", "Tel&eacute;fono: ", 50);
                //echo "<td class='titulos2'>&nbsp;</td><td class='listado3'>&nbsp;</td>";
                $sqlCmbCiu = "select nombre, id from ciudad order by 1";
                $rsCmbCiu = $db->conn->Execute($sqlCmbCiu);
                $usr_ciudad  = $rsCmbCiu->GetMenu2('codi_ciudad',$ciu_ciudad,"0:&lt;&lt seleccione &gt;&gt;",false,"","Class='select'");
                ?>

                <td class="titulos2"> * Ciudad </td>
                <td class="listado3">
                <div id='usr_ciu'><?=$usr_ciudad?></div>
                </td>
                <?
                 echo "</tr>";
                ?>
                <tr>
                <td class="titulos2">Acuerdo de uso del Sistema: </td>
            <td class="listado2" colspan="3">
                <?php                
                
                echo '&nbsp<a href="javascript:;" onClick="descargar_contenido();" class="Ntooltip">
                    <img src="'.$ruta_raiz.'/imagenes/document_down.jpg" width="17" height="17" alt="Descargar Acuerdo" border="0">
                        <span><font color="black">
                        Descargar Acuerdo
                        </font></span></a>';
                ?>
            </td>
                </tr>
            </table>
            <br>
            <table width="100%" align="center" cellpadding="0" cellspacing="0" >
                <tr>
                    
                    <?php                     
                    if ($sol_estado==0 || $sol_estado=='' || $sol_estado==1){ ?>
                    <td><center><input name="btn_aceptar" type="button" class="botones_largo" value="Aceptar"  onClick="return ValidarInformacion();"/></center></td>
                    
                    <td><center><input  name="btn_accion" type="button" class="botones_largo" value="Cancelar" onClick="history.back();"/></center></td>
                    <?php }else{
                        ?>
                        <td colspan="2"><center><font color="red">No puede modificar sus datos en vista de que tiene una solicitud de permiso de firma electrónica que ha sido enviada.</font></center></td>
                    <?php } ?>
                </tr>
            </table>
        </form>
    </center>
    </div>
</body>
</html>
