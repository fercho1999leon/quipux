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

$ruta_raiz = "../..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();
include_once "../tbasicas/listaAreas.php";

?>

<!-- LIBRERIAS PARA GENERADOR DE ARBOL AJAX -->
<link rel="StyleSheet" href="<?=$ruta_raiz?>/js/nornix-treemenu-2.2.0/example/style/menu_uno.css" type="text/css" media="screen" />
<script type="text/javascript" src="<?=$ruta_raiz?>/js/nornix-treemenu-2.2.0/treemenu/nornix-treemenu.js"></script>
<? require_once "$ruta_raiz/js/ajax.js";?>
<link rel="stylesheet" href="<?=$ruta_raiz?>/estilos/orfeo.css">

<!-- Para utilizacion de Ajax -->
<script language="JavaScript" src="<?=$ruta_raiz?>/js/prototype.js" type ="text/javascript"></script>
<script language="JavaScript" src="<?=$ruta_raiz?>/js/general1.js"  type="text/javascript"></script> 

<script type='text/JavaScript'>
    var ok_area=0;
    var ok_sigla=0;
    function datosArea(depeCodi){
        var nomDivInfoArea = 'info_area';
        var nomDiv = 'compartir';
        var nomDivJefe = 'div_jefe'
        var datos = "";
        <?php if($_GET['accion'] == 1) {?>
            datos = "1&padre=" + depeCodi ;
        <?php } else if($_GET['accion'] == 2) { ?>
            datos = "2&dependencia="+ depeCodi;
        <?php } ?>
            
        nuevoAjax(nomDivInfoArea, 'GET', 'admDependencias_ajax.php', 'accion=' + datos);
        nuevoAjax(nomDiv, 'GET', 'compartirBandeja_ajax.php', 'accion=' + datos);
        nuevoAjax(nomDivJefe, 'GET', 'administrar_jefe_ajax.php', 'accion=' + datos);
    }
    function desactivarArea(depeCodi,des_activar){           
        //nuevoAjax('div_des_activar', 'GET', 'adm_dependencias_eliminar.php', 'depe_codi=' + depeCodi+'&estado='+des_activar+'&accion='+<?=$_GET['accion']?>);
        datos = '?depe_codi=' + depeCodi+'&estado='+des_activar+'&accion='+<?=$_GET['accion']?>;
        URL="adm_dependencias_eliminar.php"+datos;
         var x = (screen.width - 500) / 2;
        var y = (screen.height - 740) / 2;
        windowprops = "top=0,left=0,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=1100,height=740";
        window.open(URL,'Desactivar Area','left=350, top=300, width=550, height=150')
       
    }
    function ltrim(s) {
       return s.replace(/^\s+/, "");
    }

    /**
    * Validación de campos obligatorios que se debe ingresar para creación de la dependencia.
    * Campos obligatorios:
    * Nombre y Siglas.
    **/
    function ValidarInformacion()
    {
        
        if (ltrim(document.form2.txt_nombre.value) == '')
        {
            alert('Ingrese el Nombre del área.');
            document.form2.txt_nombre.focus();            
            return false;
        }        
        if (ltrim(document.form2.txt_sigla.value) == '')
        {
            alert('Ingrese las Siglas para el área.');            
            document.form2.txt_sigla.focus();
            return false;
        }        
        if (ltrim(document.form2.txt_ciudad.value) == '0')
        {
            alert('Ingrese la Ciudad a la que pertence el área.');
            
            document.form2.txt_ciudad.focus();
            return false;
        }        
        
        txt_ok=document.form2.txt_ok.value='1';
        
        ok_b = validarRepetidos();
        if (ok_b==1)
          document.form2.submit();
        else
            alert("Existe Área o Sigla con el mismo nombre");               
        return true;
    }
    //valida repetidos
    function validarRepetidos(){
       //area
        tex_no = document.getElementById('txt_nombre').value;
        buscar_area_ajax(<?=$_SESSION["inst_codi"];?>,tex_no);        
        if ( document.getElementById('txt_modifica_area') ) 
        ok_area = document.getElementById('txt_modifica_area').value; 
        else
            ok_area = 0;        
        hid_no = document.getElementById('hidden_nombre').value;        
        //Sigla
        tex_si = document.getElementById('txt_sigla').value;
        buscar_sigla_ajax(<?=$_SESSION["inst_codi"];?>,tex_si);
        if ( document.getElementById('txt_modifica_sigla') ) 
        ok_sigla = document.getElementById('txt_modifica_sigla').value;
        else
            ok_sigla=0;
        hid_si = document.getElementById('hidden_sigla').value;
        if (ok_area == 0 && ok_sigla== 0)
        ok_b=1;
        else
            ok_b=0;
        
        return ok_b;
            //ValidarInformacion(); 
    }
    
         
    
    /**
    * Llamar a la página ../tbasicas/listados.php para desplegar listado.
    **/
    function ver_listado()
    {
        var x = (screen.width - 900) / 2;
        var y = (screen.height - 540) / 2;
        preview = window.open('../tbasicas/listados.php?var=dpc&verLista=2&inst=<?=$_SESSION["inst_codi"]?>','', 'scrollbars=yes,menubar=no,height=540,width=900,resizable=yes,toolbar=no,location=no,status=no');
        preview.moveTo(x, y);
        preview.focus();
    }

    /**
    * Valida la extensión de la plantilla
    **/

    function valida_extension()
    {
        cadena=document.getElementById('arch_plantilla').value;
        cadena= cadena.substr(-3).toLowerCase();
        if (cadena!='pdf') {
        alert ('Solo se permite subir archivos con extensión "pdf".');
        document.getElementById('arch_plantilla').value = '';
        }
        return;
    }

    /**
    * Permite escoger la plantilla en formato pdf, que se generará cuando se cree un documento
    **/
    function SeleccionarPlantilla(depeCodi)
    {
        if (document.getElementById('slc_plantilla').value == depeCodi || document.getElementById('slc_plantilla').value == 0)
        {
            document.getElementById('tr_plantilla').style.display='';
        } else {
            document.getElementById('tr_plantilla').style.display='none';
        }
        return;
    }

    function imprimirAreas(){
        // Generar pdf de areas
        var x = (screen.width - 20) / 2;
        var y = (screen.height - 20) / 2;
        preview = window.open('generarPDFAreas.php','', 'scrollbars=yes,menubar=no,height=20,width=20,resizable=yes,toolbar=no,location=no,status=no');
        preview.moveTo(x, y);
        preview.focus();
    }
   
    function buscarArea(){
         paginador_reload_div('');
//        busqueda_texto = document.getElementById('txt_nombre_buscar').value;
//        des_activar = document.getElementById('accion_acdec').value;
//        nuevoAjax('div_busqueda_area', 'GET', 'adm_dependencias_busqueda.php', 'busqueda_texto=' + busqueda_texto + '&des_activar='+des_activar);
    }
    //VALIDA ESPACIOS EN BLANCO
    function pulsar_espacio(e) {
    tecla = (document.all) ? e.keyCode : e.which;
    if (tecla==8) return true;
    patron =/\s/;
    te = String.fromCharCode(tecla);
    return !patron.test(te);
    }
    function refrescar_pagina(){
        window.location='adm_dependencias_nuevo.php?accion=2&des_activar=3';     
        //buscarArea();
        
    }
    
    function validar_plantilla(id_plantilla) {
        document.getElementById('ifr_descargar_plantilla').src = 'validar_plantilla.php?id_plantilla='+id_plantilla;
    }
</script>

<html>
<body>
 
<?php
$paginador = new ADODB_Pager_Ajax($ruta_raiz, "div_busqueda_area", "busqueda_paginador_areas.php",
                                  "txt_nombre_buscar,accion_acdec","carpeta=$carpeta");
?>
    <?php if ($_GET['des_activar']==3){ ?>
    <input type="hidden" name="accion_acdec" id="accion_acdec" value="<?=$_GET['des_activar'];?>"/>    
    <?php } ?>
    <table width="100%">
        <tr><td align="center" class="titulos4" colspan="2"><font size="2">Administraci&oacute;n de &Aacute;reas</font></td></tr>
            <tr>
                <td valign="top" width="10%">
                   
                <table class="borde_tab">
                    <tr>
                    <?php $listAreas = obtenerAreas($_SESSION['inst_codi'], $db); ?>
                    <td valign="middle" width="10%">
                        <script>
                        </script>
                        <div id="menu" class="menu"><a href="javascript:;" title="Exportar &aacute;reas a pdf" onclick="imprimirAreas();"><br>&nbsp;&nbsp;&nbsp;&nbsp;Exportar &Aacute;reas a pdf&nbsp;&nbsp;</a>
                            <?php
                                echo $listAreas;
                            ?>
                        </div>
                    </td>
                    </tr>
                </table>
                </td>
                <td width="90%" valign="top">
                    <div id="div_des_activar"></div>
                        <div id="info_area"></div>
                        <?php if($accion==2){?>
                        <div id="div_jefe"></div>
                        <div id="compartir"></div>                        
                        <?php } ?>
                </td>
            </tr>
        </table>

</body>
</html>

<script type='text/JavaScript'>
    //refrescar_arbol();
    nuevoAjax('info_area', 'GET', 'admDependencias_ajax.php', 'accion=' + <?=$accion?>);

    // Funcionalidad para bandeja compartida (Bandeja de documentos recibidos)
	function compartir_bandeja(id_jefe, depeCodi, accion) {
        var i = 0;
        var id_usuario;
        var data;
        var clazz = "usuario";
        var action = "";
        if(accion == 1)
        {
            action = "compartirUsuarioBandeja";
            //alert(id_jefe);
            if (document.getElementById("usuario").selectedIndex  == -1 ){
                alert("No ha seleccionando Usuarios");
            }
            if ( document.getElementById("usuario").selectedIndex >= 0 ){
                for (i=0;i< document.getElementById("usuario").length;i++) {
                    if ( document.getElementById('usuario').options[i].selected ) {
                        id_usuario = document.getElementById('usuario').options[i].value;
                        data=id_usuario+','+id_jefe+','+depeCodi;
                        //alert(data);
                        ajax_call_bandeja ( data, clazz, action, ver_datos );
                    }
                }
            }
        }
        else if (accion == 2)
        {
            action = "elim_compartirUsuarioBandeja";
            data=id_jefe+',0,'+depeCodi;
            //alert(data);
            ajax_call_bandeja ( data, clazz, action, ver_datos );
        }
    }
    function grabar_jefe(id_jefe, depeCodi, accion) {
        
        var i = 0;
        var id_usuario;
        var data;
        var clazz = "usuario";
        var action = "";
        if(accion == 'grabar')
        {
            action = "grabar_jefe";            
            if (document.getElementById("usuario_jefe").selectedIndex  == -1 ){
                alert("No ha seleccionando Usuarios");
            }
            if ( document.getElementById("usuario_jefe").selectedIndex >= 0 ){
                for (i=0;i< document.getElementById("usuario_jefe").length;i++) {
                    if ( document.getElementById('usuario_jefe').options[i].selected ) {
                        id_usuario = document.getElementById('usuario_jefe').options[i].value;
                        data=id_usuario+','+id_jefe+','+depeCodi;
                        //alert(data);
                        ajax_call_bandeja ( data, clazz, action, ver_datos );
                    }
                }
            }
        }else{
            action = "elimina_jefe_area";
            data=id_jefe+',0,'+depeCodi;
            //alert(data);
            ajax_call_bandeja ( data, clazz, action, ver_datos );
        }
    }
    function ver_datos(result,resp,depeCodi){
        if (resp!="")  	{
            alert(resp);// si hay errores se mostrar� el alert
        }
        else {
            datosArea(depeCodi);
        }
    }
    function eliminarArea(parametros){        
        top.frames['mainFrame'].location.href=parametros;
        //window.open('generarPDFAreas.php','', 'scrollbars=yes,menubar=no,height=20,width=20,resizable=yes,toolbar=no,location=no,status=no');
        //nuevoAjax('info_area_eliminar', 'GET', 'adm_dependencias_eliminar.php', 'depe_codi='+depe_codi);
    }
    function buscar_area_ajax(instituto,nombre) {           
            ok_area = 0;
            var i = 0;
            var modifica;
            modifica = nombre;
            inst_codi = instituto;            
            nombDiv = 'div_nombre';
            dep_codigo_js = document.getElementById('txt_depe_codi').value;            
            nuevoAjax(nombDiv, 'GET', 'adm_dependencias_nom_area.php', 'valor='+modifica+'&inst_codigo='+inst_codi+'&dep_codigo_js='+dep_codigo_js);                
            document.form2.txt_sigla.focus();
        }    
    function buscar_sigla_ajax(instituto,nombre) {
            ok_sigla=0;
            var i = 0;
            var modifica;
            modifica = nombre;
            inst_codi = instituto;            
            nombDiv = 'div_nombre1';
            dep_codigo_js = document.getElementById('txt_depe_codi').value;
            nuevoAjax(nombDiv, 'GET', 'adm_dependencias_nom_sigla.php', 'valor='+modifica+'&inst_codigo='+inst_codi+'&dep_codigo_js='+dep_codigo_js);            
            document.form2.txt_ciudad.focus();
        }
function pulsar(e) {
  tecla = (document.all) ? e.keyCode : e.which;
  if (tecla==13) {
    buscarArea();
    // aquí el código que quieras ejecutar
  }
}
</script>