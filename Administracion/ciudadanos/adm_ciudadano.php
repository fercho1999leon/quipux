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

include_once "util_ciudadano.php";
$ciud = New Ciudadano($db);
$ciu_ciudad = 0;
//Busca ciudadano por login
$ciu_codi = limpiar_sql($_SESSION["usua_codi"]);

 $sql = "select * from ciudadano where ciu_codigo=" . $ciu_codi . " and ciu_estado = 1";
$rs = $db->conn->query($sql);
if (!$rs or $rs->EOF) {
    //Si no existe lo busca por codigo
   $sql = "select * from ciudadano where ciu_cedula='" . substr($ciu_login, 1) . "'";
    $rs = $db->conn->query($sql);
    if (!$rs or $rs->EOF) {
        echo html_error("No se encontr&oacute; el usuario en el sistema.");
        die("");
    }
}

$ciu_nuevo = $rs->fields["CIU_NUEVO"];

//buscar ciudadano si esta en tempora, depliega la pantalla de datos temporales
$ciud->consultar_ciudadano_tmp($_SESSION["usua_codi"],'',2);

$sql = "select sol_estado from solicitud_firma_ciudadano where ciu_codigo=" . $_SESSION["usua_codi"]."";

$rs2 = $db->conn->query($sql);
if ($rs2 && !$rs2->EOF) {    
    $sol_estado = $rs2->fields["SOL_ESTADO"];    
        
}
//comprobar si el ciudadano está en temporal   
$ciu_codigo     = $rs->fields["CIU_CODIGO"];
$ciu_cedula     = $rs->fields["CIU_CEDULA"];
if (substr($ciu_cedula, 0, 2) == 99)
    $ciu_cedula = "";
$ciu_documento  = $rs->fields["CIU_DOCUMENTO"];
$ciu_nombre     = $rs->fields["CIU_NOMBRE"];
$ciu_apellido   = $rs->fields["CIU_APELLIDO"];
$ciu_titulo     = $rs->fields["CIU_TITULO"];
$ciu_abr_titulo = $rs->fields["CIU_ABR_TITULO"];
$ciu_empresa    = $rs->fields["CIU_EMPRESA"];
$ciu_cargo      = $rs->fields["CIU_CARGO"];
$ciu_direccion  = $rs->fields["CIU_DIRECCION"];
$ciu_email      = $rs->fields["CIU_EMAIL"];
$ciu_telefono   = $rs->fields["CIU_TELEFONO"];
$ciu_ciudad     = $rs->fields["CIUDAD_CODI"];
if ($ciu_ciudad =='')
    $ciu_ciudad=0;

$ciu_referencia  = $rs->fields["CIU_REFERENCIA"];


require_once "$ruta_raiz/js/ajax.js";
include_once "$ruta_raiz/funciones_interfaz.php";

echo "<html>".html_head();
?>
<script type="text/javascript" src="jquerysubir/jquery-1.3.2.min.js"></script>
<script type="text/javascript" language="JavaScript" src="adm_ciudadanos.js"></script>
<script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
<script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/validar_cedula.js"></script>




<script type="text/javascript" src="<?= $ruta_raiz ?>/js/validar_datos_usuarios.js"></script>
<script type="text/javascript" language="javascript">


    function ValidarInformacion()
    {
        msg = '';

        //Evalúa, en base a la condición, si agrega el mensaje entre los errores o no:
        function e(condicion, mensaje) {
            msg = (condicion) ? msg + mensaje + ' \n' : msg;
        }

        e(!validarCedula(trim(document.forms[0].ciu_cedula.value)), "Verifique su número de cédula.");
        e(trim(document.forms[0].ciu_nombre.value) == '', "Ingrese los nombres.");
        e(trim(document.forms[0].ciu_apellido.value) == '', "Ingrese los apellidos.");
        e(!isEmail(document.forms[0].ciu_email.value,true), "El campo Email no tiene formato correcto.");
        e(trim(document.forms[0].ciu_ciudad.value) == 0, "Seleccione la Ciudad.");
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
        
        nuevoAjax('div_datos_registro_civil', 'POST', '../usuarios/validar_datos_registro_civil.php', 'cedula='+cedula);
        if ('<?=$_SESSION["tipo_usuario"]?>' == '1'){            
            nuevoAjax('div_datos_usuario_multiple', 'POST', '../usuarios/validar_datos_usuario_multiple.php', 'usr_codigo=<?=$ciu_codigo?>&cedula='+cedula);
        }else
            document.getElementById("div_datos_usuario_multiple").innerHTML="<font color='blue' align='center'>No disponible</font>";
    }

    function copiar_datos_registro_civil(campo_rc, campo_usr) {
        try {
            document.getElementById(campo_usr).value = document.getElementById('lbl_datos_rc_'+campo_rc).innerHTML;
        } catch (e) {}
    }
    function descargar_contenido() {
        path='<?=$path_acuerdo?>';
        windowprops = "top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=500,height=300";

        url = "<?=$ruta_raiz?>/descargar_contenidos.php?archivo="+path;
        window.open(url , "Vista_Previa_Acuerdo", windowprops);
        return;
    }
    
</script>

<body onload="validar_cambio_cedula();datosCiudad(<?=$ciu_ciudad?>)">
    <div id="wrapper">
    <center>
        <form name='formulario' action="adm_ciudadano_grabar.php" method="post">
            <input type="hidden" name="usr_codigo" id="usr_codigo" size="40" value="<?=$ciu_codigo?>" />
                <input type="hidden" name="ciu_ciudad" id="ciu_ciudad" size="40" value="<?=$ciu_ciudad?>" />
            <input type='hidden' name='ciu_codigo' value="<?=$_SESSION["usua_codi"]?>"/>
            <br>
            <table width="100%" border="1" class="borde_tab">
                <tr>
                    <th>
                        <center>Administraci&oacute;n de Ciudadanos</center>
                    </th>
                </tr>
            </table>
            <br>            
            <?php echo $ciud->divsInformacionUsrCiud($ciu_cedula);?>
            <table width="100%" border="1" align="center" class="borde_tab">
                
                <tr>          
                <? 
                    $ciud->dibujar_campo ("ciu_cedula", $ciu_cedula,"* C&eacute;dula: ", 13,"onBlur='validar_cambio_cedula()'",1);                
                    $ciud->dibujar_campo ("ciu_documento", $ciu_documento,"Otro Documento: ", 20,"",1);
                ?>
                </tr>
                <tr>
                <?
                    $label = "* Nombre: &nbsp;&nbsp;&nbsp;<img src=\"$ruta_raiz/iconos/copy.gif\" alt=\"copiar\" title=\"Copiar datos del Registro Civil\" onclick=\"copiar_datos_registro_civil('nombre', 'ciu_nombre')\">";                
                    $ciud->dibujar_campo ("ciu_nombre", $ciu_nombre,$label, 20,"",1);
                    $label = "* Apellido: &nbsp;&nbsp;&nbsp;<img src=\"$ruta_raiz/iconos/copy.gif\" alt=\"copiar\" title=\"Copiar datos del Registro Civil\" onclick=\"copiar_datos_registro_civil('nombre', 'ciu_apellido')\">";
                    $ciud->dibujar_campo ("ciu_apellido", $ciu_apellido,$label, 20,"",1);                
                ?>
                </tr>
                <tr>
                <?
                    $ciud->dibujar_campo ("ciu_titulo", $ciu_titulo,"T&iacute;tulo: ", 20,"",1);                
                    $ciud->dibujar_campo ("ciu_abr_titulo", $ciu_abr_titulo,"Abr. T&iacute;tulo", 20,"",1);
                ?>
                </tr><tr>
                <?
                    $ciud->dibujar_campo ("ciu_empresa", $ciu_empresa,"Instituci&oacute;n: ", 50,"",1);
                    $ciud->dibujar_campo ("ciu_cargo", $ciu_cargo,"Puesto: ",20,"",1);
                ?>
                </tr>

                

                <tr>
                <?
                    $ciud->dibujar_campo ("ciu_telefono", $ciu_telefono,"Tel&eacute;fono: ", 13,"",1);                
                    //$sqlCmbCiu = "select nombre, id from ciudad order by 1";
                    //$rsCmbCiu = $db->conn->Execute($sqlCmbCiu);
                    //$usr_ciudad  = $rsCmbCiu->GetMenu2('codi_ciudad',$ciu_ciudad,"0:&lt;&lt seleccione &gt;&gt;",false,"","Class='select'");
                    $ciud->dibujar_campo ("ciu_email", $ciu_email," Email: ", 50,"",1);
                ?>

               
                </tr>
                
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
                
                <tr>
        <td class="titulos2">
		Dirección Principal (Barrio/Número)</td>
        <td class="listado2"><input class="caja_texto" type="text" name="ciu_direccion" id="ciu_direccion" onblur="javascript:changeCase_Articulos(this)" value="<?=$ciu_direccion?>" size="50" maxlength="150">
                   </td>
          <td class="titulos2">Referencia (Calles/Transversales)</td><td class="listado2"><input class="caja_texto" type="text" name="ciu_referencia" id="ciu_referencia" onblur="javascript:changeCase_Articulos(this)" value="<?=$ciu_referencia?>" size="50" maxlength="150">
	    </td></tr>
        <tr><td colspan="4">
                <?php 
                
                include_once "../catalogos/ciudad_buscar.php"?></td>
                </tr>
            </table>
            <br>
           
            <?php   
            
             echo $ciud->verHistorico($_SESSION["usua_codi"],1,'1,4');
            ?>
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
