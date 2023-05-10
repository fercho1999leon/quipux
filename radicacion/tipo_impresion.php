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
$ruta_raiz = isset($ruta_raiz) ? $ruta_raiz : "..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

//Archivo que permite buscar el destinatario y remitente para los documentos
session_start();
if (!$ruta_raiz)	$ruta_raiz="..";
include_once "$ruta_raiz/rec_session.php";
include_once("$ruta_raiz/obtenerdatos.php");
include_once "$ruta_raiz/funciones.php";
//Se incluyo por register_globals

/*$buscar_tipo = $_POST['buscar_tipo'];
$buscar_nom  = $_POST['buscar_nom'];
$buscar_car  = $_POST['buscar_car'];
$buscar_inst  = $_POST['buscar_inst'];
$buscar_depe  = $_POST['buscar_depe'];

$lista_usr  = $_POST['lista_usr'];*/
   // Las siguientes lineas se incluyeron porque, hay dos fuentes de esta variable cuando se llama desde NEW.php viene por GET
 /*if ( !isset( $_GET['documento_us1'] ) )
   $documento_us1  = $_POST['documento_us1'];
 else
   $documento_us1  = $_GET['documento_us1'] ;

  if ( !isset( $_GET['documento_us2'] ) )
   $documento_us2  = $_POST['documento_us2'];
 else
   $documento_us2  = $_GET['documento_us2'] ;

  if ( !isset( $_GET['concopiaa'] ) )
   $concopiaa  = $_POST['concopiaa'];
 else
   $concopiaa  = $_GET['concopiaa'] ;

$flag_inst  = $_POST['concopiaa'];*/
//$krd = $_GET['krd'];
/*$ent=$_GET['ent'];

if (!$buscar_inst) $buscar_inst="0";
if (!$buscar_depe) $buscar_depe="0";
if (!$lista_usr) $lista_usr="0";
*/
?>
<html>
<head>
<title>Tipo de Documento a Generar</title>
<link rel="stylesheet" href="../estilos/orfeo.css" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<SCRIPT Language="JavaScript" SRC="../js/crea_combos_2.js"></SCRIPT>

<!-- LIBRERIAS PARA GENERADOR DE ARBOL AJAX -->
<link rel="StyleSheet" href="../js/nornix-treemenu-2.2.0/example/style/menu.css" type="text/css" media="screen" />
<script type="text/javascript" src="../js/nornix-treemenu-2.2.0/treemenu/nornix-treemenu.js"></script>
<!--<script type="text/javascript" src="../js/nornix-treemenu-2.2.0/example/script/nornix-treemenu.js"></script>-->
<? require_once "$ruta_raiz/js/ajax.js";?>
<script LANGUAGE="JavaScript">

documento=new Array();
tipo_us=new Array();

function pasar_datos()
{
    var seleccion='';
    for(i=0; i <document.formuImp.tipo_impresion.length; i++){
        if(document.formuImp.tipo_impresion[i].checked){
            seleccion = 's';
            opener.document.formu1.tipo_impresion.value = document.formuImp.tipo_impresion[i].value;
        }
    }
    if(seleccion=='s')
        window.close();
    else
        alert("Por favor, seleccione el tipo de documento que desea generar.");
}

</script>
</head>
<body bgcolor="#FFFFFF">
<?$varenvio = "tipo_impresion.php";?>
<form method="post" name="formuImp" id="formuImp" action="<?=$varenvio?>" >
    <table class="borde_tab" align="center" width="100%">
        <tr valign="center">
            <td class="listado5">
                <input type="radio" name='tipo_impresion' value="1" checked><font class="tituloListado">&nbsp;&nbsp;Generar un documento con datos de los destinatarios (titulo, nombre, puesto, institucion).</font>
            </td>
        </tr>
        <tr valign="center">
            <td class="listado5">
                <input type="radio" name='tipo_impresion' value="2"><font class="tituloListado">&nbsp;&nbsp;Generar un documento con datos de los destinatarios (puesto, institucion).</font>
            </td>
        </tr>
        <tr valign="center">
            <td class="listado5">
                <input type="radio" name='tipo_impresion' value="3"><font class="tituloListado">&nbsp;&nbsp;Generar un documento con el Nombre de la lista.</font>
            </td>
        </tr>
        <!--<tr valign="center">
            <td class="listado5">
                <input type="radio" name='tipo_impresion' value="4"><font class="tituloListado">&nbsp;&nbsp;Generar un documento para cada destinatario.</font>
            </td>
        </tr>-->
        <tr>
            <td height="30"><center><input type='button' value='Aceptar' class="botones_largo" onclick='pasar_datos()'></center></td>
        </tr>
    </table>
</form>
</body>
</html>