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

if($_SESSION["usua_admin_sistema"]!=1) {
    echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    die("");
}

include_once "$ruta_raiz/rec_session.php";

 $accion_btn_cancelar = "window.location='$ruta_raiz/Administracion/usuarios/cuerpoSolicitud_ext.php'";
 $flag_login = true;
 
$sql = "select count(1) as num from ciudadano_tmp where ciu_codigo=$ciu_codigo and ciu_estado = 1";

$rs1 = $db->conn->query($sql);
if ((0+$rs1->fields["NUM"])>0){
    //echo("<td class='titulos3' width='20%'> Por favor autorice primero los cambios en los datos del ciudadano </td>");
   echo "<html>".html_head();

    echo "<center>
        <br />
        <table width='40%' border='2' align='center' class='t_bordeGris'>
            <tr>
            <td width='100%' height='30' class='listado2'>
                <span class='listado5'><center><B>Por favor autorice primero los cambios en los datos del ciudadano</B></center></span>
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
      $sql = "select * from solicitud_firma_ciudadano where ciu_codigo=$ciu_codigo";
            //echo $sql;
            $rs = $db->conn->query($sql);
            $ciu_cedula 	= $rs->fields["CIU_CEDULA"];
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
            //$ciu_nuevo   	= $rs->fields["CIU_NUEVO"];
            //$ciu_estado   	= $rs->fields["CIU_ESTADO"];
            //$usua_codi_actualiza = $rs->fields["USUA_CODI_ACTUALIZA"];
            //$ciu_fecha_actualiza = $rs->fields["CIU_FECHA_ACTUALIZA"];
            //$ciu_obs_actualiza = $rs->fields["CIU_OBS_ACTUALIZA"];
            $ciu_ciudad        = $rs->fields["CIUDAD_CODI"];

//nuevo solicitud

$sql = "select * from solicitud_firma_ciudadano where ciu_codigo=".$rs->fields["CIU_CODIGO"];
$rs3 = $db->conn->query($sql);
if (!$rs3->EOF) {
     $rs = $rs3;
}
$sol_codigo = $rs->fields["SOL_CODIGO"];
$sol_observaciones = $rs->fields["SOL_OBSERVACIONES"];
$sol_firma = $rs->fields["SOL_FIRMA"];
$sol_estado = $rs->fields["SOL_ESTADO"];
$sol_planilla = $rs->fields["SOL_PLANILLA"];
$sol_cedula = $rs->fields["SOL_CEDULA"];
$sol_acuerdo = $rs->fields["SOL_ACUERDO"];
$chk_planilla = $rs->fields["SOL_PLANILLA_ESTADO"];
$chk_cedula = $rs->fields["SOL_CEDULA_ESTADO"];
$chk_acuerdo = $rs->fields["SOL_ACUERDO_ESTADO"];


if($sol_planilla ==1 && $sol_cedula ==1 && $sol_acuerdo ==1)
    $visible_enviar = "display:visible";
else
    $visible_enviar = "display:none";

if($sol_estado == 2)
    $visible_autorizar = "display:visible";
else
    $visible_autorizar = "display:none";

?>

<html>
<? echo html_head(); /*Imprime el head definido para el sistema*/
include_once "$ruta_raiz/js/ajax.js"
?>
    <script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/formchek.js"></script>
    <script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/js/validar_cedula.js"></script>
    <script type="text/javascript" language="javascript">
    function ltrim(s) {
       return s.replace(/^\s+/, "");
    }

    function verificar_firma(nombre,n) {
        windowprops = "top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=600,height=400";
        URL = '<?="$ruta_raiz/VerificarFirma.php?archivo="?>'+ nombre +'<?="&nombre_archivo="?>' + n;
        window.open(URL , "Verificar Firma Acuerdo", windowprops);
    }

    function valEmail(valor){
        re=/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,6})$/
        //alert(valor);
        if(!re.exec(valor))    {
          alert("La dirección de correo no tiene el formato adecuado.");
          return false;
        }else{
          return true;
        }
   }

    var bandera = 0;

    function ValidarInformacionRegistroCivil(cedula)
    {
     window.open('./adm_solicitud_validar.php?codigo='+cedula+'&nombre='+document.forms[0].ciu_apellido.value +' '+ document.forms[0].ciu_nombre.value,'Datos','height=165,width=450,left=350,top=300');
     bandera = 1;
    }

    function ValidarInformacion(accion)
    {
        if(accion == 2)
            bandera = 1;
        if(bandera == 1)
        {
        if (!validarCedula(document.forms[0].ciu_cedula)) {
            alert ('Verifique su número de cédula.');
            return false;
        }
        if(ltrim(document.forms[0].ciu_nombre.value)=='' || ltrim(document.forms[0].ciu_apellido.value)=='')
        {	alert("Los campos de Nombres y Apellidos son obligatorios.");
            return false;
        }
        if (!isEmail(document.forms[0].ciu_email.value,true) || ltrim(document.forms[0].ciu_email.value)=='')
        {	alert("El campo email es Obligatorio.");
            return false;
        }
       if ( ltrim(document.forms[0].ciu_email.value)=='')
        {
            alert("El campo email es Obligatorio.");
            return false;
        }
        else
        {
               if(!isEmail(document.forms[0].ciu_email.value,true))
               {
                   alert("La dirección de correo no tiene el formato adecuado.");
                   return false;
               }
        }

        if ( ltrim(document.forms[0].ciu_email.value)!='')
        {
            if(!valEmail(ltrim(document.forms[0].ciu_email.value)))
            {
               return false;
            }
        }
        if ( document.forms[0].codi_ciudad.value==0){
               alert("La ciudad no está especificada");
               return false;
        }
        //document.getElementById("chk_cedulah").value = document.formulario_cedula.chk_cedula.value;
        //document.getElementById("chk_planillah").value = document.formulario_planilla.chk_planilla.value;
        document.getElementById("chk_acuerdoh").value = document.formulario_acuerdo.chk_acuerdo.value;
        if(accion != 3)
           document.getElementById("sol_accion").value = accion;
       
       var obs = document.getElementById("sol_observaciones").value;
       if(accion == 2 && obs == ''){
           alert("Ingresar el campo Observaciones para rechazar la solicitud");
           bandera = 0;
           return false;
        }

       // var ced = document.getElementById("chk_cedulah").value;
        //var pla = document.getElementById("chk_planillah").value;
        var acu = document.getElementById("chk_acuerdoh").value;
       if(accion == 1){
          // if(ced==0 || pla==0 || acu==0){
          if(acu==0){
           alert("El/los documento(s) adjunto(s) no fueron autorizados.");
           return false;
           }
        }

        //alert(accion);
        document.formulario.submit();
        return true;

    }
     else
     {
         alert("Valide los datos con el registro civil.");
           return false;
     }

    }

  
    var tipo = new Array();
    tipo[0] = 'pdf';

    function escogio_archivo(archivo)	//ALMACENA EL NOMBRE DEL ARCHIVO Y MUESTA UNA NUEVA FILA
	{


	    mensaje = '';
	    //if(archivo == 1)
            //arch = document.getElementById("sol_cedula").value.toLowerCase();
            //else if(archivo == 2)
            //arch = document.getElementById("sol_planilla").value.toLowerCase();
            //else 
                if(archivo == 3)
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

//                 if(archivo == 1)
//                    arch = document.getElementById("sol_cedula").value = '';
//                 else if(archivo == 2)
//                    arch = document.getElementById("sol_planilla").value = '';
//                 else 
                 if(archivo == 3)
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
                    + '<form id="formulario_'+id_objeto+'" name="formulario_'+id_objeto+'" action="adm_solicitud_actualizar.php?codigo='+com_codi+'&estado='+'<?=$comEstado?>'+'&userfile='+id_objeto+'&tipo_anexo='+tipo_anexo+'&descripcion='+descripcion+'" target="iframe_'+id_objeto+'">'
                    + '<div id="div_anexo_nombre_'+id_objeto+'" style="display:none"></div>'
                    + '<div id="div_anexo_estado_'+id_objeto+'" style="display:none"></div>'
                    + '<table width="100%" align="center" border="1" cellpadding="0" cellspacing="3" id="anexo_nuevo_'+id_objeto+'" class="t_bordeGris">'
                    + '<tr><td class="titulos3"  width="11%">'+descripcion+' <font size="4"></font></td>'
                    + '<td class="listado1" align="left" width="74%">'
                    + '<input type="hidden" id="hd_accion_'+id_objeto+'" name="hd_accion_'+id_objeto+'" value="1">'
                    + '<input type="hidden" id="hd_id_'+id_objeto+'" name="hd_id_'+id_objeto+'" value="0">'
                    + '</td>'
                    + '</tr></table>'
                    + '<iframe name="'+iframe_nombre+'" id="'+iframe_nombre+'" src="" width="400" height="100" style="display:none"></iframe>'
                    + '</form></div>';

        document.getElementById('div_anexo_nuevo_archivo_'+id_objeto).innerHTML += texto;
    }


    function anexo_cargar_archivo(id_objeto) {
        var archivo = document.getElementById(id_objeto);
        if (anexo_validar_tipo(archivo)) {
            document.getElementById('div_anexo_estado_'+id_objeto).innerHTML ='<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif">&nbsp;Cargando Archivo ';
            document.getElementById('div_anexo_nombre_'+id_objeto).innerHTML = archivo.value;
            document.getElementById('anexo_nuevo_'+id_objeto).style.display = 'none';
            document.getElementById('div_anexo_estado_'+id_objeto).style.display = '';

            document.getElementById('formulario_'+id_objeto).submit();

        }
    }

    function anexo_borrar_archivo(id_objeto) {
        if (confirm("Esta seguro/a que desea eliminar el archivo?")) {
            document.getElementById('div_anexo_estado_'+id_objeto).innerHTML ='<img src="<?=$ruta_raiz?>/imagenes/progress_bar.gif">&nbsp;Eliminando Archivo ';
            document.getElementById('div_anexo_nombre_'+id_objeto).style.display = 'none';
            document.getElementById('hd_accion_'+id_objeto).value = '0';
            document.getElementById('formulario_'+id_objeto).submit();
            document.getElementById('anexo_nuevo_'+id_objeto).style.display = '';


            document.getElementById('div_enviar').style.display = 'none';
        }
    }

    function seleccionar_opcion(id_objeto) {
          document.getElementById('chk_'+id_objeto).value = '0';

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

    function anexo_validar_tipo(objeto) {
        var arch = objeto.value.toLowerCase();
        arch = arch.replace(/.p7m/g, "");
        var arr_ext = arch.split('.');
        var cadena = arr_ext[arr_ext.length-1].toLowerCase();

        if(cadena!="")
            switch (cadena) {
                case 'pdf':
                    return true;
                    break;
                default:
                    alert ('No está permitido anexar archivos con extensión "'+cadena+'".\nConsulte con su administrador del sistema.');
                    objeto.value = '';
                    return false;
                    break;
            }
        return true;
    }

    function coordinador(){

        /*if(document.formulario_cedula.chk_cedula.checked == true){
            document.formulario_cedula.chk_cedula.value = '1';            
            }
        else{
            document.formulario_cedula.chk_cedula.value = '0';            
            }
        if(document.formulario_planilla.chk_planilla.checked == true){
            document.formulario_planilla.chk_planilla.value = '1';            
            }
        else{
            document.formulario_planilla.chk_planilla.value = '0';            
            }*/
        if(document.formulario_acuerdo.chk_acuerdo.checked == true)
            document.formulario_acuerdo.chk_acuerdo.value = '1';
        else
            document.formulario_acuerdo.chk_acuerdo.value = '0';
     }

</script>
<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

</head>
<body>
  <div id="wrapper">
  <? if (!$flag_login) echo html_encabezado(); /*Imprime el encabezado del sistema*/ ?>
  <form name='formulario' action="adm_solicitud_verificar_usuario.php" method="post">
    <table width="100%" border="1" align="center" class="t_bordeGris">
  	  <tr>
        <td class="titulos4">
            <center><p><B><span class=etexto>AUTORIZAR PERMISOS PARA GENERAR Y FIRMAR DOCUMENTOS EN EL SISTEMA</span></B> </p></center>
	    </td>
	  </tr>
    </table>
    <br/>

    <input type='hidden' name='ciu_codigo' value="<?=$ciu_codigo?>">
<!--    <input type='hidden' name='chk_cedulah' id="chk_cedulah" value="0">
    <input type='hidden' name='chk_planillah' id="chk_planillah" value="0">-->
    <input type='hidden' name='chk_acuerdoh' id="chk_acuerdoh" value="0">
    <input type='hidden' name='sol_accion' id="sol_accion" value="0">

    <table width="100%" border="1" align="center" class="t_bordeGris">
<?
        function dibujar_campo ($campo, $label, $tamano,$sol_estadod, $opciones="") {
            global $$campo;
            global $$sol_estadod;
            if ($_SESSION["usua_codi"]==0 && ($$sol_estadod == 3 || $$sol_estadod == 0))
                 $estado_read = "readonly='readonly'";
            else
                $estado_read = "";

            $cad = "<td class='titulos3' width='20%'> $label </td>
                    <td class='listado3' width='30%'>
                    <input type='text' name='$campo' id='$campo' value='".$$campo."' size='50' maxlength='$tamano' $estado_read>
                    </td>";
            echo $cad;
            return;
        }
        function dibujar_campoprueba ($campo, $label, $tamano, $estado,$opciones="") {
            global $$campo;
            $cad = "<td class='titulos3' width='20%'> $label </td>
                    <td class='listado3' width='30%'>
                    <input type='text' name='$campo' id='$campo' value='$estado' size='50' maxlength='$tamano' readonly='readonly'>
                    </td>";
            echo $cad;
            return;
        }
        function dibujar_campoobs ($campo, $label, $tamano,$sol_estadod, $opciones="") {
            global $$campo;
            global $$sol_estadod;
            if ($_SESSION["usua_codi"]==0 && ($$sol_estadod == 3 || $$sol_estadod == 0))
                $estado_read = "readonly='readonly'";
            else
                $estado_read = "";

            $cad = "<td class='titulos3' width='10%'> $label </td>
                    <td class='listado3' width='90%' colspan='3'>
                    <input type='text' name='$campo' id='$campo' value='".$$campo."' size='115' maxlength='$tamano' $estado_read>
                    </td>";
            echo $cad;
            return;
        }
        function dibujar_campocombo ($campo, $label, $tamano, $opciones="") {
            global $$campo;
            $cad = "<td class='titulos3' width='20%'> $label </td>
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

        if (!$rs3->EOF) {

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
            <td class='titulos3' width='20%'> Tengo Firma Electr&oacute;nica </td>
                    <td class='listado3' width='50%'>
<!--                    <select name='sol_firma' id='sol_firma' class='select'>
                         <option value='1' selected style="display:none">Token</option>
                         <option value='2' <? if ($sol_firma == 2) echo "selected"; ?>>Archivo</option>
                   </select>-->
                        <?php echo combo_firma_ciudadano($sol_firma,$db);?>
                   </td>
        <?
    	echo "</tr><tr>";
        echo "<tr>";
            dibujar_campo ("ciu_cedula", "* C&eacute;dula", 13,"sol_estado");
            dibujar_campo ("ciu_documento", "Otro Documento", 50,"sol_estado");
    	echo "</tr><tr>";
            dibujar_campo ("ciu_nombre", "* Nombre", 150,"sol_estado"); //, "onKeyUp='this.value=this.value.toUpperCase();'");
            dibujar_campo ("ciu_apellido", "* Apellido", 150,"sol_estado"); //, "onKeyUp='this.value=this.value.toUpperCase();'");
    	echo "</tr><tr>";
            dibujar_campo ("ciu_titulo", "T&iacute;tulo", 100,"sol_estado");
            dibujar_campo ("ciu_abr_titulo", "Abr. T&iacute;tulo", 30,"sol_estado");
    	echo "</tr><tr>";
            dibujar_campo ("ciu_empresa", "Instituci&oacute;n", 150,"sol_estado");
            dibujar_campo ("ciu_cargo", "Puesto", 150,"sol_estado");
    	echo "</tr><tr>";
            dibujar_campo ("ciu_direccion", "Direcci&oacute;n", 150,"sol_estado");
            dibujar_campo ("ciu_email", "* Email", 50,"sol_estado");
    	echo "</tr><tr>";
            dibujar_campo ("ciu_telefono", "Tel&eacute;fono", 50,"sol_estado");


        $sqlCmbCiu = "select nombre, id from ciudad order by 1";
        $rsCmbCiu = $db->conn->Execute($sqlCmbCiu);
        $usr_ciudad  = $rsCmbCiu->GetMenu2('codi_ciudad',$ciu_ciudad,"0:&lt;&lt seleccione &gt;&gt;",false,"","Class='select'");
        
          ?>
            <td class="titulos3"> * Ciudad </td>
            <td class="listado3">
                <div id='usr_ciu'><?=$usr_ciudad?></div>
            </td>
         <?

    	echo "</tr>";
 echo "</tr><tr>";
            dibujar_campoobs ("sol_observaciones", "Observaciones", 500,"sol_estado");
    	echo "</tr>";

?>

                   <?if($sol_estado == 3) {?>
                   <script type="text/javascript">document.getElementById("sol_firma").disabled = true; </script>
                   <?} else {?>
                   <script type="text/javascript">document.getElementById("sol_firma").disabled = false; </script>
                   <?}?>

    </table>




  </form>
  <? if (!$flag_login) echo html_pie_pagina(); /*Imprime el pie de pagina del sistema*/ ?>
  </div>

 <br/>
<?php
/*
    <div id="div_anexo_nuevo_archivo_cedula"></div>
    <script type="text/javascript">
         anexo_nuevo_archivo ('<?=$ciu_codigo?>','cedula','pdf','* Cédula');
    </script>
    <?php

    if(!$rs3->EOF){
    if($sol_cedula == 1){
            $subirArch="1";
            $nombre = $ciu_codigo."_cedula.pdf";
            $path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=/ciudadanos/$nombre&nomb_arch=$nombre";

         $nombre_archivo = "<table width='100%' align='center' border='1' class='t_bordeGris'><tr><td class='titulos3' width='13%'>* Cédula</td><td>";
         $nombre_archivo .= "<input type='checkbox' name='chk_cedula' id='chk_cedula' value=\\\"$chk_cedula\\\"";
         if($chk_cedula=='1') $nombre_archivo .= " checked ";
         $nombre_archivo .= " onclick=\\\"coordinador();\\\"/>";
         $nombre_archivo .= "<a href=\\\"javascript:window.open('$path_descarga','_self','');\\\" class='vinculos'>$nombre</a>";
         $nombre_archivo .= "</td></tr></table>";

         echo "<script>
            anexo_respuesta_archivo('cedula', \"$nombre_archivo\",\"$codArch\",\"$subirArch\");
          </script>";
       }
    }
   ?>
    <div id="div_anexo_nuevo_archivo_planilla"></div>
    <script type="text/javascript">
         anexo_nuevo_archivo ('<?=$ciu_codigo?>','planilla','pdf','* Planilla');
    </script>
    <?php

    if(!$rs3->EOF){
    if($sol_planilla == 1){
            $subirArch="1";
            $nombre = $ciu_codigo."_planilla.pdf";
            $path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=/ciudadanos/$nombre&nomb_arch=$nombre";

         $nombre_archivo = "<table width='100%' align='center' border='1' class='t_bordeGris'><tr><td class='titulos3' width='13%'>* Planilla</td><td>";
         $nombre_archivo .= "<input type='checkbox' name='chk_planilla' id='chk_planilla' value=\\\"$chk_planilla\\\"";
         if($chk_planilla=='1') $nombre_archivo .= " checked ";
         $nombre_archivo .= " onclick=\\\"coordinador();\\\"/>";
         $nombre_archivo .= "<a href=\\\"javascript:window.open('$path_descarga','_self','');\\\" class='vinculos'>$nombre</a>";         
         $nombre_archivo .= " </td></tr></table>";
         
         echo "<script>
            anexo_respuesta_archivo('planilla', \"$nombre_archivo\",\"$codArch\",\"$subirArch\");
          </script>";
       }
    }*/
   ?>
    <div id="div_anexo_nuevo_archivo_acuerdo"></div>
    <script type="text/javascript">
         anexo_nuevo_archivo ('<?=$ciu_codigo?>','acuerdo','pdf','* Acuerdo');
     </script>
<?php

    if(!$rs3->EOF){
    if($sol_acuerdo == 1){
            $subirArch="1";
            $nombre = $ciu_codigo."_acuerdo.pdf.p7m";
            $nombre_desc = $ciu_codigo."_acuerdo.pdf";
            $nombre_firma = "/ciudadanos/".$ciu_codigo."_acuerdo.pdf.p7m";
            $path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=/ciudadanos/$nombre_desc&nomb_arch=$nombre_desc";


         $nombre_archivo = "<table width='100%' align='center' border='1' class='t_bordeGris'><tr><td class='titulos3' width='13%'>* Acuerdo</td><td>";
         $nombre_archivo .= "<input type='checkbox' name='chk_acuerdo' id='chk_acuerdo' value=\\\"$chk_acuerdo\\\"";
         if($chk_acuerdo=='1') $nombre_archivo .= " checked ";
         $nombre_archivo .= " onclick=\\\"coordinador();\\\"/>";
         $nombre_archivo .= "<a href=\\\"javascript:window.open('$path_descarga','_self','');\\\" class='vinculos'>$nombre</a>&nbsp;&nbsp;&nbsp;&nbsp;";
         $nombre_archivo .= "<a href='javascript:;' onclick=\\\"verificar_firma('$nombre_firma','$nombre');\\\" class='vinculos'>Verificar Firma</a>";
         
         $nombre_archivo .= "</td></tr></table>";


         echo "<script>
            anexo_respuesta_archivo('acuerdo', \"$nombre_archivo\",\"$codArch\",\"$subirArch\");
          </script>";
       }
    }
   ?>

 <br/>
    <table width="100%" align="center" cellpadding="0" cellspacing="0" >

        <tr>
                
            <td><center><input name="btn_autorizar" type="submit" class="botones_largo" value="Autorizar" onClick="return ValidarInformacion(1);" title="Autorizar Solicitud" style="<?=$visible_autorizar?>"/></center></td>
            <td><center><input name="btn_delvolver" type="button" class="botones_largo" value="Rechazar"  onClick="return ValidarInformacion(2);" title="Rechazar Solicitud" style="<?=$visible_autorizar?>"/></center></td>
            <td><center><input name="btn_validar" title="Validar datos del ciudadano" type="submit" class="botones_largo" value="Validar Registro Civil" onclick="ValidarInformacionRegistroCivil(<?=$ciu_codigo?>)"/>      </center></td>
            <td><center><input  name="btn_accion" type="button" class="botones_largo" title="Cancelar" value="Cancelar" onClick="<?=$accion_btn_cancelar?>"/></center></td>

        </tr>
    </table>



</body>
</html>
