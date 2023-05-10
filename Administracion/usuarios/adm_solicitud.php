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
** Administración de datos por parte del ciudadano										**
*****************************************************************************************/
$ruta_raiz = "../..";

require_once "$ruta_raiz/funciones.php";
require_once("$ruta_raiz/funciones_interfaz.php");
session_start();
include_once "$ruta_raiz/rec_session.php";
$ciu_login = limpiar_sql($_SESSION["krd"]);


 $accion_btn_cancelar =  "window.location='$ruta_raiz/cuerpo.php?nomcarpeta=$nombre&carpeta=$carp_codi&adodb_next_page=1'";
$flag_login = true;

$carp_codi = 80;
$nombre = "Documentos Enviados";

$sql = "select * from ciudadano where ciu_cedula='".substr($ciu_login,1)."'";
$rs = $db->conn->query($sql);
if ($rs->EOF) {
    //Si no existe lo busca por codigo
    $sql = "select * from ciudadano where ciu_codigo=" . $_SESSION["usua_codi"] . "";
    
    $rs = $db->conn->query($sql);
    if (!$rs or $rs->EOF) {
        echo html_error("No se encontr&oacute; el usuario en el sistema.");
        die("");
    }
}
//comprobar si el ciudadano está en temporal
$sql = "select * from ciudadano_tmp where ciu_codigo = ".$rs->fields["CIU_CODIGO"]." and ciu_estado=1";

$rsTmp = $db->conn->query($sql);
if (!$rsTmp->EOF){   
    $ciuCodigoTmp = $rsTmp->fields["CIU_CODIGO"];
}
if ($ciuCodigoTmp!='')
echo "<script>window.location='adm_datos_temporales.php'</script>";    


$ced1 = substr($rs->fields["CIU_CEDULA"], 0,5);
if ($ced1 == '99999'){
    //echo("<td class='titulos3' width='20%'> Por favor autorice primero los cambios en los datos del ciudadano </td>");
   echo "<html>".html_head();

    echo "<center>
        <br />
        <table width='40%' border='2' align='center' class='borde_tab'>
            <tr>
            <td width='100%' height='30' class='listado2'>
                <span class='listado5'><center><B>Por favor edite sus datos personales antes de ingresar una solicitud.</B></center></span>
            </td>
            </tr>
            <tr>
            <td height='30' class='listado2'>
                <center><input class='botones' type='button' value='Regresar' onClick=\"$accion_btn_cancelar\"></center>
            </td>
            </tr>
        </table>
    </center>";
    die ();
}

$ciu_nuevo   	= $rs->fields["CIU_NUEVO"];
// Si el ciudadano ya había cambiado sus datos anteriormente se muestran los que el cambió
$sql = "select * from solicitud_firma_ciudadano where ciu_codigo = ".$rs->fields["CIU_CODIGO"];

$rs2 = $db->conn->query($sql);
//var_dump($rs2);
if (!$rs2->EOF) {
    $rs = $rs2;
    $ciu_codigo 	= $rs->fields["CIU_CODIGO"];
        $ciu_cedula 	= $rs->fields["CIU_CEDULA"];
        if (substr($ciu_cedula,0,2)==99) $ciu_cedula="";
        $ciu_documento 	= $rs->fields["CIU_DOCUMENTO"];
        $ciu_nombre 	= $rs->fields["CIU_NOMBRE"];
        $ciu_apellido 	= $rs->fields["CIU_APELLIDO"];
        $ciu_titulo     = $rs->fields["CIU_TITULO"];
        $ciu_abr_titulo	= $rs->fields["CIU_ABR_TITULO"];
        $ciu_empresa 	= $rs->fields["CIU_EMPRESA"];
        $ciu_cargo      = $rs->fields["CIU_CARGO"];
        $ciu_direccion 	= $rs->fields["CIU_DIRECCION"];
        $ciu_email      = $rs->fields["CIU_EMAIL"];
        $ciu_telefono 	= $rs->fields["CIU_TELEFONO"];
        $ciu_ciudad        = $rs->fields["CIUDAD_CODI"];
        //echo("solicitud");
}
else{
    $sqlciudadano = "select * from ciudadano where ciu_codigo=".$rs->fields["CIU_CODIGO"];
    
    $rsciudadano = $db->conn->query($sqlciudadano);

    if (!$rsciudadano->EOF) {
        $rs = $rsciudadano;
    }
    $ciu_codigo 	= $rs->fields["CIU_CODIGO"];
    $ciu_cedula 	= $rs->fields["CIU_CEDULA"];
    if (substr($ciu_cedula,0,2)==99) $ciu_cedula="";
    $ciu_documento 	= $rs->fields["CIU_DOCUMENTO"];
    $ciu_nombre 	= $rs->fields["CIU_NOMBRE"];
    $ciu_apellido 	= $rs->fields["CIU_APELLIDO"];
    $ciu_titulo     = $rs->fields["CIU_TITULO"];
    $ciu_abr_titulo	= $rs->fields["CIU_ABR_TITULO"];
    $ciu_empresa 	= $rs->fields["CIU_EMPRESA"];
    $ciu_cargo      = $rs->fields["CIU_CARGO"];
    $ciu_direccion 	= $rs->fields["CIU_DIRECCION"];
    $ciu_email      = $rs->fields["CIU_EMAIL"];
    $ciu_telefono 	= $rs->fields["CIU_TELEFONO"];
    $ciu_ciudad        = $rs->fields["CIUDAD_CODI"];
//echo("ciudadano");
}
//nuevo solicitud

//$sql = "select * from solicitud_firma_ciudadano where ciu_codigo=".$rs->fields["CIU_CODIGO"];
//$rs3 = $db->conn->query($sql);
//if (!$rs3->EOF) {
  //   $rs = $rs3;
//}
$sol_codigo = $rs->fields["SOL_CODIGO"];
$sol_observaciones = $rs->fields["SOL_OBSERVACIONES"];
$sol_firma = $rs->fields["SOL_FIRMA"];
$sol_estado = $rs->fields["SOL_ESTADO"];
$sol_planilla = $rs->fields["SOL_PLANILLA"];
$sol_cedula = $rs->fields["SOL_CEDULA"];
$sol_acuerdo = $rs->fields["SOL_ACUERDO"];

 //echo $sol_acuerdo;

if($sol_estado == 2)
    {
    $visible_autorizar = "display:none";
    }
else
    {
    $visible_autorizar = "display:visible";
    }
?>

<html>
<? echo html_head(); /*Imprime el head definido para el sistema*/
include_once "$ruta_raiz/js/ajax.js"
?>
<script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
<script type="text/javascript" language="JavaScript" src="<?= $ruta_raiz ?>/js/validar_datos_usuarios.js"></script>
<script type="text/javascript" language="javascript">

    function EditarUsuario(){
        document.formulario_enviar.nombre_usu.value = document.formulario.ciu_nombre.value;
        document.formulario_enviar.cedula_usu.value = document.formulario.ciu_cedula.value;
        document.formulario_enviar.documento_usu.value = document.formulario.ciu_documento.value;
        document.formulario_enviar.apellido_usu.value = document.formulario.ciu_apellido.value;
        document.formulario_enviar.titulo_usu.value = document.formulario.ciu_titulo.value;
        document.formulario_enviar.abr_titulo_usu.value = document.formulario.ciu_abr_titulo.value;
        document.formulario_enviar.empresa_usu.value = document.formulario.ciu_empresa.value;
        document.formulario_enviar.cargo_usu.value = document.formulario.ciu_cargo.value;
        document.formulario_enviar.direccion_usu.value = document.formulario.ciu_direccion.value;
        document.formulario_enviar.mail_usu.value = document.formulario.ciu_email.value;
        document.formulario_enviar.telefono_usu.value = document.formulario.ciu_telefono.value;
        document.formulario_enviar.observaciones_usu.value = document.formulario.sol_observaciones.value;
        document.formulario_enviar.firma_usu.value = document.formulario.sol_firma.value;
        document.formulario_enviar.ciudad_usu.value = document.formulario.codi_ciudad.value;

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

        if (msg == '' && !validar_datos_registro_civil('ciu_nombre','ciu_apellido')) return false;

        document.formulario.submit();

        return true;
    }

    function verificar_firma(nombre,n) {
        windowprops = "top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=600,height=400";
        URL = '<?="$ruta_raiz/VerificarFirma.php?archivo="?>'+ nombre +'<?="&nombre_archivo="?>' + n;
        window.open(URL , "Verificar Firma Acuerdo", windowprops);
    }


    function ValidarInformacion(tipo)
    {
        //Cola de mensajes de error:
        msg = '';

        //Evalúa, en base a la condición, si agrega el mensaje entre los errores o no:
        function e(condicion, mensaje) {
            msg = (condicion) ? msg + mensaje + ' \n' : msg;
        }

        e(!validarCedula(trim(document.forms[0].ciu_cedula.value)), "Verifique su número de cédula.");
        e(trim(document.forms[0].ciu_nombre.value) == '', "Ingrese los nombres.");
        e(trim(document.forms[0].ciu_apellido.value) == '', "Ingrese los apellidos."); 
        e(trim(document.forms[0].codi_ciudad.value) == 0, "Seleccione la ciudad."); 
        e(!isEmail(document.forms[0].ciu_email.value,true) || trim(document.forms[0].ciu_email.value)=='', "El campo Email no tiene formato correcto.");

        if(msg != '' )
        {
            alert(msg);
            return false;
        }
        
        if (msg == '' && !validar_datos_registro_civil('ciu_nombre','ciu_apellido')) return false;
        
        if (tipo==1)
           document.formulario.submit();
        else
            EditarUsuario();

        return true;
    }


    var tipo = new Array();
    tipo[0] = 'pdf';

    function escogio_archivo(archivo)	//ALMACENA EL NOMBRE DEL ARCHIVO Y MUESTA UNA NUEVA FILA
	{

        
	    mensaje = '';
	    if(archivo == 1)
            arch = document.getElementById("sol_cedula").value.toLowerCase();
            else if(archivo == 2)
            arch = document.getElementById("sol_planilla").value.toLowerCase();
            else if(archivo == 3)
            arch = document.getElementById("sol_acuerdo").value.toLowerCase();

	    arch = arch.replace(/.p7m/g, "");
	    arr_ext = arch.split('.');

	    cadena = arr_ext[arr_ext.length-1].toLowerCase();

	    flag=true;

            for (j = 0;j <= tipo.length; j++) {
		if (tipo[j]==cadena)
                    flag=false;
	    }



	    if (flag) {
		alert ('No está permitido anexar archivos con extensión '+cadena+'.\n'+mensaje+'Solo se permiten archivos con extensión pdf.');

                 if(archivo == 1)
                    arch = document.getElementById("sol_cedula").value = '';
                 else if(archivo == 2)
                    arch = document.getElementById("sol_planilla").value = '';
                 else if(archivo == 3)
                    arch = document.getElementById("sol_acuerdo").value = '';

		return;
	    }
            if(archivo == 1)
               anexo_borrar_archivo(1);
            else if(archivo == 2)
               anexo_borrar_archivo(2);
            else if(archivo == 3)
               anexo_borrar_archivo(3);

	    return;
	}



    //Subir archivos anexos mediante ajax
    // Crea la interfaz para subir un nuevo anexo
    function anexo_nuevo_archivo(com_codi, id_objeto, tipo_anexo, descripcion) {
        var iframe_nombre = "iframe_"+id_objeto;
        var texto = '<div id="div_anexo_'+id_objeto+'">'
                    + '<form id="formulario_'+id_objeto+'" method="post" enctype="multipart/form-data" action="adm_solicitud_grabar_anexo.php?codigo='+com_codi+'&estado='+'<?=$comEstado?>'+'&userfile='+id_objeto+'&tipo_anexo='+tipo_anexo+'&descripcion='+descripcion+'" target="iframe_'+id_objeto+'">'
                    + '<div id="div_anexo_nombre_'+id_objeto+'" style="display:none"></div>'
                    + '<div id="div_anexo_estado_'+id_objeto+'" style="display:none"></div>'
                    + '<table width="100%" align="center" border="1" cellpadding="0" cellspacing="3" id="anexo_nuevo_'+id_objeto+'" class="borde_tab">'
                    + '<tr><td class="titulos2"  width="11%">'+descripcion+' <font size="4"></font></td>'
                    + '<td class="listado1" align="left" width="74%"><input type="file" name="'+id_objeto+'" size="50" class="tex_area" id="'+id_objeto+'" onchange="anexo_cargar_archivo(\''+id_objeto+'\');" />'
                    + '<input type="hidden" id="hd_accion_'+id_objeto+'" name="hd_accion_'+id_objeto+'" value="1">'
                    + '<input type="hidden" id="hd_id_'+id_objeto+'" name="hd_id_'+id_objeto+'" value="0">'

                    + '<input type="hidden" id="nombre_usu_'+id_objeto+'" name="nombre_usu_'+id_objeto+'" value ="'+document.formulario.ciu_nombre.value+'">'
                    + '<input type="hidden" id="cedula_usu_'+id_objeto+'" name="cedula_usu_'+id_objeto+'" value ="'+document.formulario.ciu_cedula.value+'">'
                    + '<input type="hidden" id="documento_usu_'+id_objeto+'" name="documento_usu_'+id_objeto+'" value ="'+document.formulario.ciu_documento.value+'">'
                    + '<input type="hidden" id="apellido_usu_'+id_objeto+'" name="apellido_usu_'+id_objeto+'" value ="'+document.formulario.ciu_apellido.value+'">'
                    + '<input type="hidden" id="titulo_usu_'+id_objeto+'" name="titulo_usu_'+id_objeto+'" value ="'+document.formulario.ciu_titulo.value+'">'
                    + '<input type="hidden" id="abr_titulo_usu_'+id_objeto+'" name="abr_titulo_usu_'+id_objeto+'" value ="'+document.formulario.ciu_abr_titulo.value+'">'
                    + '<input type="hidden" id="empresa_usu_'+id_objeto+'" name="empresa_usu_'+id_objeto+'" value ="'+document.formulario.ciu_empresa.value+'">'
                    + '<input type="hidden" id="cargo_usu_'+id_objeto+'" name="cargo_usu_'+id_objeto+'" value ="'+document.formulario.ciu_cargo.value+'">'
                    + '<input type="hidden" id="direccion_usu_'+id_objeto+'" name="direccion_usu_'+id_objeto+'" value ="'+document.formulario.ciu_direccion.value+'">'
                    + '<input type="hidden" id="mail_usu_'+id_objeto+'" name="mail_usu_'+id_objeto+'" value ="'+document.formulario.ciu_email.value+'">'
                    + '<input type="hidden" id="telefono_usu_'+id_objeto+'" name="telefono_usu_'+id_objeto+'" value ="'+document.formulario.ciu_telefono.value+'">'
                    + '<input type="hidden" id="observaciones_usu_'+id_objeto+'" name="observaciones_usu_'+id_objeto+'" value ="'+document.formulario.sol_observaciones.value+'">'
                    + '<input type="hidden" id="firma_usu_'+id_objeto+'" name="firma_usu_'+id_objeto+'" value ="'+document.formulario.sol_firma.value+'">'
                    + '<input type="hidden" id="ciudad_usu_'+id_objeto+'" name="ciudad_usu_'+id_objeto+'" value ="'+document.formulario.codi_ciudad.value+'">'

                    + '</td>'
                    + '</tr></table>'
                    + '<iframe name="'+iframe_nombre+'" id="'+iframe_nombre+'" src="" width="400" height="100" style="display:none"></iframe>'
                    + '</form></div>';

        document.getElementById('div_anexo_nuevo_archivo_'+id_objeto).innerHTML += texto;
    }


    function anexo_cargar_archivo(id_objeto) {
        
        var archivo = document.getElementById(id_objeto);
        if (anexo_validar_tipo(archivo,id_objeto)) {
            document.getElementById('div_anexo_estado_'+id_objeto).innerHTML ='<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif">&nbsp;Cargando Archivo ';
            document.getElementById('div_anexo_nombre_'+id_objeto).innerHTML = archivo.value;
            document.getElementById('anexo_nuevo_'+id_objeto).style.display = 'none';
            document.getElementById('div_anexo_estado_'+id_objeto).style.display = '';

            document.getElementById('nombre_usu_'+id_objeto).value = document.formulario.ciu_nombre.value;
            document.getElementById('cedula_usu_'+id_objeto).value = document.formulario.ciu_cedula.value;
            document.getElementById('documento_usu_'+id_objeto).value = document.formulario.ciu_documento.value;
            document.getElementById('apellido_usu_'+id_objeto).value = document.formulario.ciu_apellido.value;
            document.getElementById('abr_titulo_usu_'+id_objeto).value = document.formulario.ciu_titulo.value;
            document.getElementById('abr_titulo_usu_'+id_objeto).value = document.formulario.ciu_abr_titulo.value;
            document.getElementById('empresa_usu_'+id_objeto).value = document.formulario.ciu_empresa.value;
            document.getElementById('cargo_usu_'+id_objeto).value = document.formulario.ciu_cargo.value;
            document.getElementById('direccion_usu_'+id_objeto).value = document.formulario.ciu_direccion.value;
            document.getElementById('mail_usu_'+id_objeto).value = document.formulario.ciu_email.value;
            document.getElementById('telefono_usu_'+id_objeto).value = document.formulario.ciu_telefono.value;
            document.getElementById('observaciones_usu_'+id_objeto).value = document.formulario.sol_observaciones.value;
            document.getElementById('firma_usu_'+id_objeto).value = document.formulario.sol_firma.value;
            document.getElementById('ciudad_usu_'+id_objeto).value = document.formulario.codi_ciudad.value;

            document.getElementById('formulario_'+id_objeto).submit();
             datos = 'tipo=a';
            nuevoAjax('div_actualizar_sol', 'GET', 'adm_solicitud_actualizar_campos.php', datos);
            timerID = setTimeout("mosterar_boton()", 200);
        }
    }

    function anexo_borrar_archivo(id_objeto) {    
        if (confirm("Esta seguro/a que desea eliminar el archivo?")) {
            document.getElementById('div_anexo_estado_'+id_objeto).innerHTML ='<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif">&nbsp;Eliminando Archivo ';
            document.getElementById('div_anexo_nombre_'+id_objeto).style.display = 'none';
            document.getElementById('hd_accion_'+id_objeto).value = '0';
            document.getElementById('formulario_'+id_objeto).submit();
            document.getElementById('anexo_nuevo_'+id_objeto).style.display = '';
            
//            if (id_objeto == 'acuerdo')
//            document.getElementById('div_enviar').style.display = 'none';
            
            datos = 'tipo=b';
            nuevoAjax('div_actualizar_sol', 'GET', 'adm_solicitud_actualizar_campos.php', datos);
            
            
        }
    }
   function mosterar_boton(){
   
   datos = 'tipo=c';
            nuevoAjax('div_actualizar_sol', 'GET', 'adm_solicitud_actualizar_campos.php', datos);
   }
    function anexo_respuesta_archivo(id_objeto, mensaje, arch_id, subirArch) {
        document.getElementById('div_anexo_nombre_'+id_objeto).innerHTML = mensaje;
        document.getElementById('div_anexo_estado_'+id_objeto).style.display = 'none';
        if(document.getElementById('hd_accion_'+id_objeto).value == '0' || subirArch == '0')
        {
            document.getElementById('hd_accion_'+id_objeto).value = "1";
            document.getElementById('hd_id_'+id_objeto).value = arch_id;
            document.getElementById('div_anexo_nombre_'+id_objeto).style.display = 'none';
            document.getElementById('anexo_nuevo_'+id_objeto).style.display = '';
            document.getElementById(id_objeto).value = '';
        }
        else
        {
            document.getElementById('anexo_nuevo_'+id_objeto).style.display = 'none';
            document.getElementById('hd_id_'+id_objeto).value = arch_id;
            document.getElementById('div_anexo_nombre_'+id_objeto).style.display = '';
        }


    }

     function visible_enviar(enviar) {         
         if(enviar ==1)
             document.getElementById('div_enviar').style.display = '';
     }

    function anexo_validar_tipo(objeto,id_objeto) {
        var arch = objeto.value.toLowerCase();
        var arr_ext ="";
        var cadena ="";

        if(id_objeto != 'acuerdo'){
        //arch = arch.replace(/.p7m/g, "");
        arr_ext = arch.split('.');
        cadena = arr_ext[arr_ext.length-1].toLowerCase();
        if(cadena!="")
            switch (cadena) {
                case 'pdf':
                    return true;
                    break;
                default:
                    alert ('Solo se permiten archivos con extensión pdf.');
                    objeto.value = '';
                    return false;
                    break;
            }
        }
        else{
        arr_ext = arch.split('.');
        cadena = arr_ext[arr_ext.length-1].toLowerCase();
             if(cadena!="")
            switch (cadena) {
                case 'p7m':
                    document.getElementById('div_enviar').style.display = '';
                    return true;
                    break;
                default:
                    alert ('Solo se permiten archivos con extensión p7m.');
                    objeto.value = '';
                    return false;
                    break;
            }
            }


        return true;
    }

    function validar_cambio_cedula() {
        cedula = document.getElementById('ciu_cedula').value;
        nuevoAjax('div_datos_registro_civil', 'POST', 'validar_datos_registro_civil.php', 'cedula='+cedula);
        nuevoAjax('div_datos_usuario_multiple', 'POST', 'validar_datos_usuario_multiple.php', 'usr_codigo=<?=$ciu_codigo?>&cedula='+cedula);
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

<body onload="validar_cambio_cedula(); mosterar_boton();">
  <div id="wrapper">
  <? if (!$flag_login) echo html_encabezado(); /*Imprime el encabezado del sistema*/ ?>
  <form name='formulario' action="adm_solicitud_grabar.php" method="post">
      <table width="100%" class="borde_tab" rules="rows">
  	  <tr>
        <td class="titulos4">
            <center><p><B><span class=etexto>SOLICITAR PERMISOS PARA GENERAR Y FIRMAR DOCUMENTOS EN EL SISTEMA</span></B> </p></center>
	    </td>
	  </tr>
    </table>
    <br/>
    <div id="div_datos_registro_civil" style="width: 100%;"></div>
    <div id="div_datos_usuario_multiple" style="width: 100%;"></div>
    <input type='hidden' id="ciu_codigo" name='ciu_codigo' value="<?=$ciu_codigo?>">
    <table width="100%" class="borde_tab" border="1">
<?
        function dibujar_campo ($campo, $label, $tamano,$sol_estadod, $opciones="") {
            global $$campo;
            global $$sol_estadod;

             if($$sol_estadod == 3 || $$sol_estadod == 2)
                $estado_read = "readonly='readonly'";
            else
                $estado_read = "";

            $cad = "<td class='titulos2' width='20%'> $label </td>
                    <td class='listado3' width='30%'>
                    <input type='text' name='$campo' id='$campo' value='".$$campo."' size='55' maxlength='$tamano' class='caja_texto' $estado_read>
                    </td>";
            echo $cad;
            return;
        }
        function dibujar_campoprueba ($campo, $label, $tamano, $estado, $opciones="") {
            global $$campo;
            $cad = "<td class='titulos2' width='20%'> $label </td>
                    <td class='listado3' width='30%'>
                    <input type='text' name='$campo' id='$campo' value='$estado' size='63' maxlength='$tamano' class='caja_textoSinBorde' readonly='readonly'>
                    </td>";
            echo $cad;
            return;
        }
        function dibujar_campoobs ($campo, $label, $tamano,$sol_estadod, $opciones="") {
            global $$campo;
            global $$sol_estadod;

             if($$sol_estadod == 3 || $$sol_estadod == 2)
                $estado_read = "readonly='readonly'";
            else
                $estado_read = "";

            $cad = "<td class='titulos2' width='10%'> $label </td>
                    <td class='listado3' width='90%' colspan='3'>
                    <input type='text' name='$campo' id='$campo' value='".$$campo."' size='132' maxlength='$tamano' class='caja_texto' $estado_read>
                    </td>";
            echo $cad;
            return;
        }
        function dibujar_campocombo ($campo, $label, $tamano, $opciones="") {
            global $$campo;
            $cad = "<td class='titulos2' width='20%'> $label </td>
                    <td class='listado3' width='50%'>
                    <select name='$campo' id='$campo' class='select'>
                         <option value='1' selected>Token</option>
                         <option value='2' <? if ($lista_orden == 2) echo selected; ?>>Archivo</option>
                   </select>
                   </td>";
            echo $cad;
            return;
        }


        echo "<tr>";

        if (!$rs2->EOF) {

        if($sol_estado == 0)
           dibujar_campoprueba ("sol_estado", "Estado Solicitud", 13,"Rechazado");
        else if($sol_estado == 1)
           dibujar_campoprueba ("sol_estado", "Estado Solicitud", 13,"En Edición");
        else if($sol_estado == 2)
            dibujar_campoprueba ("sol_estado", "Estado Solicitud", 13,"Enviado");
        else if($sol_estado == 3)
            dibujar_campoprueba ("sol_estado", "Estado Solicitud", 13,"Autorizado");

        }
        else
            dibujar_campoprueba ("sol_estado", "Estado Solicitud", 13,"En Edición");
        ?>
            <td class='titulos2' width='20%'> Tengo Firma Electr&oacute;nica </td>
                    <td class='listado3' width='50%'>                        
<!--                    <select name='sol_firma' id='sol_firma' class='select'>                        
                         <option value='1' selected style="display:none">Token</option>
                         <option value='2' <? if ($sol_firma == 2) echo "selected"; ?>>Archivo</option>
                   </select>-->
                        <?php                         
                        echo combo_firma_ciudadano($sol_firma,$db);?>
                   </td>
        <?
    	echo "</tr><tr>";
        echo "<tr>";
            dibujar_campo ("ciu_cedula", "* C&eacute;dula: ", 13,"sol_estado");
            dibujar_campo ("ciu_documento", "Otro Documento: ", 50,"sol_estado");
    	echo "</tr><tr>";
            $label = "* Nombre: &nbsp;&nbsp;&nbsp;<img src=\"$ruta_raiz/iconos/copy.gif\" alt=\"copiar\" title=\"Copiar datos del Registro Civil\" onclick=\"copiar_datos_registro_civil('nombre', 'ciu_nombre')\">";
            dibujar_campo ("ciu_nombre", $label, 150,"sol_estado"); //, "onKeyUp='this.value=this.value.toUpperCase();'");
            $label = "* Apellido: &nbsp;&nbsp;&nbsp;<img src=\"$ruta_raiz/iconos/copy.gif\" alt=\"copiar\" title=\"Copiar datos del Registro Civil\" onclick=\"copiar_datos_registro_civil('nombre', 'ciu_apellido')\">";
            dibujar_campo ("ciu_apellido", $label, 150,"sol_estado"); //, "onKeyUp='this.value=this.value.toUpperCase();'");
    	echo "</tr><tr>";
            dibujar_campo ("ciu_titulo", "T&iacute;tulo: ", 100,"sol_estado");
            dibujar_campo ("ciu_abr_titulo", "Abr. T&iacute;tulo: ", 30,"sol_estado");
    	echo "</tr><tr>";
            dibujar_campo ("ciu_empresa", "Instituci&oacute;n: ", 150,"sol_estado");
            dibujar_campo ("ciu_cargo", "Puesto: ", 150,"sol_estado");
    	echo "</tr><tr>";
            $label = "Direcci&oacute;n: &nbsp;&nbsp;&nbsp;<img src=\"$ruta_raiz/iconos/copy.gif\" alt=\"copiar\" title=\"Copiar datos del Registro Civil\" onclick=\"copiar_datos_registro_civil('direccion', 'ciu_direccion')\">";
            dibujar_campo ("ciu_direccion", $label, 150,"sol_estado");
            dibujar_campo ("ciu_email", "* Email: ", 50,"sol_estado");
    	echo "</tr><tr>";
            dibujar_campo ("ciu_telefono", "Tel&eacute;fono: ", 50,"sol_estado");

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
        <?
         echo "</tr><tr>";
            dibujar_campoobs ("sol_observaciones", "Observaciones: ", 500,"sol_estado");
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
             <?if($sol_estado == 2) {?>
                   <script type="text/javascript">document.getElementById("sol_firma").disabled = true; </script>
                   <?} else {?>
                   <script type="text/javascript">document.getElementById("sol_firma").disabled = false; </script>
                   <?}?>
    </table>



<br/>





  </form>
  <? if (!$flag_login) echo html_pie_pagina(); /*Imprime el pie de pagina del sistema*/ ?>
  </div>

<?php
/*

    <div id="div_anexo_nuevo_archivo_cedula"></div>
    <script type="text/javascript">
         anexo_nuevo_archivo ('<?=$ciu_codigo?>','cedula','pdf',' Cédula: ');
    </script>
    <?php

    if(!$rs3->EOF){
    if($sol_cedula == 1){
         $subirArch="1";
         $nombre = $ciu_codigo."_cedula.pdf";
         $path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=/ciudadanos/$nombre&nomb_arch=$nombre";

         $nombre_archivo = "<table width='100%' align='center' border='1' class='borde_tab'><tr><td class='titulos3' width='13%'>* Cédula</td><td>";
         $nombre_archivo .= "<a href=\\\"javascript:window.open('$path_descarga','_self','');\\\" class='vinculos'>$nombre</a>";
         if($sol_estado!='3' && $sol_estado!='2') $nombre_archivo .= "&nbsp;&nbsp;&nbsp;<img src='$ruta_raiz/imagenes/close_button.gif' width='20' height='18' onClick=\\\"anexo_borrar_archivo('cedula');\\\" title='Eliminar Archivo' style='border: 0px solid gray;cursor:pointer;' alt='Eliminar Archivo'>";
         //$nombre_archivo .= "&nbsp;&nbsp;&nbsp;<img src='$ruta_raiz/imagenes/close_button.gif' width='20' height='18' onClick=\\\"anexo_borrar_archivo('cedula');\\\" title='Eliminar Archivo' style='border: 0px solid gray;cursor:pointer;' alt='Eliminar Archivo'>";
         $nombre_archivo .= "</td></tr></table>";
         echo "<script>
            anexo_respuesta_archivo('cedula', \"$nombre_archivo\",\"$codArch\",\"$subirArch\");
          </script>";



       }
    }
   ?>
    <div id="div_anexo_nuevo_archivo_planilla"></div>
    <script type="text/javascript">
         anexo_nuevo_archivo ('<?=$ciu_codigo?>','planilla','pdf',' Planilla: ');
    </script>
    <?php

    if(!$rs3->EOF){
    if($sol_planilla == 1){
            $subirArch="1";
            $nombre = $ciu_codigo.'_planilla.pdf';
            $path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=/ciudadanos/$nombre&nomb_arch=$nombre";
         $nombre_archivo = "<table width='100%' align='center' border='1' class='borde_tab'><tr><td class='titulos3' width='13%'>* Planilla</td><td>";
         $nombre_archivo .= "<a href=\\\"javascript:window.open('$path_descarga','_self','');\\\" class='vinculos'>$nombre</a>";
         if($sol_estado!='3' && $sol_estado!='2')$nombre_archivo .= "&nbsp;&nbsp;&nbsp;<img src='$ruta_raiz/imagenes/close_button.gif' width='20' height='18' onClick=\\\"anexo_borrar_archivo('planilla');\\\" title='Eliminar Archivo' style='border: 0px solid gray;cursor:pointer;' alt='Eliminar Archivo'>";
         //$nombre_archivo .= "&nbsp;&nbsp;&nbsp;<img src='$ruta_raiz/imagenes/close_button.gif' width='20' height='18' onClick=\\\"anexo_borrar_archivo('planilla');\\\" title='Eliminar Archivo' style='border: 0px solid gray;cursor:pointer;' alt='Eliminar Archivo'>";
         $nombre_archivo .= "</td></tr></table>";
         echo "<script>
            anexo_respuesta_archivo('planilla', \"$nombre_archivo\",\"$codArch\",\"$subirArch\");
          </script>";
       }
    }*/
   ?>
    
    <div id="div_anexo_nuevo_archivo_acuerdo"></div>
    <script type="text/javascript">
         anexo_nuevo_archivo ('<?=$ciu_codigo?>','acuerdo','pdf.p7m','* Acuerdo: ');
     </script>
<?php

    if(!$rs3->EOF){
    if($sol_acuerdo == 1){
            $subirArch="1";
            $nombre = $ciu_codigo.'_acuerdo.pdf';
            $nombre_desc = $ciu_codigo.'_acuerdo.pdf.p7m';
            $nombre_firma = "/ciudadanos/".$ciu_codigo."_acuerdo.pdf.p7m";
            $path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=/ciudadanos/$nombre_desc&nomb_arch=$nombre";
            $url = "$ruta_raiz/bodega/ciudadanos/".$ciu_codigo."_acuerdo.pdf.p7m";
            
            //compruebo si hay path
            if (is_file($url)){//url
                
                 $nombre_archivo = "<table width='100%' align='center' border='1' class='borde_tab'><tr><td class='titulos3' width='13%'>* Acuerdo</td><td>";
                 $nombre_archivo .= "<a href=\\\"javascript:window.open('$path_descarga','_self','');\\\" class='vinculos'>$nombre</a>&nbsp;&nbsp;&nbsp;&nbsp;";
                 $nombre_archivo .= "<a href='javascript:;' onclick=\\\"verificar_firma('$nombre_firma','$nombre');\\\" class='vinculos'>Verificar Firma</a>";
                 if($sol_estado!='3' && $sol_estado!='2')
                     $nombre_archivo .= "&nbsp;&nbsp;&nbsp;<img src='$ruta_raiz/imagenes/close_button.gif' width='20' height='18' onClick=\\\"anexo_borrar_archivo('acuerdo');\\\" title='Eliminar Archivo' style='border: 0px solid gray;cursor:pointer;' alt='Eliminar Archivo'>";         
                 $nombre_archivo .= "</td></tr></table>";
                 echo "<script>
                    anexo_respuesta_archivo('acuerdo', \"$nombre_archivo\",\"$codArch\",\"$subirArch\");
                  </script>";
            }//url
       }
    }
   ?>

 <br/>
    <table width="100%" align="center" cellpadding="0" cellspacing="0" >

        <tr>
            <td><center><input name="btn_aceptar" type="button" class="botones_largo" title="Aceptar" value="Aceptar"  onClick="return ValidarInformacion(1);" style="<?=$visible_autorizar?>"/></center></td>
           <td><center>
         <form name='formulario_enviar' action="adm_solicitud_enviar.php" method="post">
            <div id="div_actualizar_sol" name="div_actualizar_sol"></div>
            <div id="div_enviar">
                 <?php
           //if ($sol_acuerdo==1){ ?> 
<!--                 <input name="btn_enviar" type="submit" class="botones_largo" title="Enviar" value="Enviar" readonly="<?=$estado_read?>" onclick="return EditarUsuario();"/>-->
                 <?php //} ?>
             </div>
             
             <iframe name="iframe_enviar" id="iframe_enviar" src="" style="display:none"></iframe>

             <input type="hidden" id="nombre_usu" name="nombre_usu" />
             <input type="hidden" id="cedula_usu" name="cedula_usu" />
             <input type="hidden" id="documento_usu" name="documento_usu" />
             <input type="hidden" id="apellido_usu" name="apellido_usu" />
             <input type="hidden" id="titulo_usu" name="titulo_usu" />
             <input type="hidden" id="abr_titulo_usu" name="abr_titulo_usu" />
             <input type="hidden" id="empresa_usu" name="empresa_usu" />
             <input type="hidden" id="cargo_usu" name="cargo_usu" />
             <input type="hidden" id="direccion_usu" name="direccion_usu" />
             <input type="hidden" id="mail_usu" name="mail_usu" />
             <input type="hidden" id="telefono_usu" name="telefono_usu" />
             <input type="hidden" id="observaciones_usu" name="observaciones_usu" />
             <input type="hidden" id="firma_usu" name="firma_usu" />
             <input type="hidden" id="ciudad_usu" name="ciudad_usu" />

         </form>
           </center></td>
            <td><center><input  name="btn_accion" type="button" class="botones_largo" title="Cancelar" value="Cancelar" onClick="<?=$accion_btn_cancelar?>"/></center></td>
        </tr>
    </table>



</body>
</html>
