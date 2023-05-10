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
/**
*	Autor			Iniciales		Fecha (dd/mm/aaaa)                     Comentario
*
*
*	Modificado por		Iniciales		Fecha (dd/mm/aaaa)
* Paulina Rodas        PR               25/05/2009                             Se cambia el estilo de la pagina y aumenta opcion listar areas
*
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
*	Sylvia Velasco		SV			03-12-2008
**/

/**
* Presenta menú para administración de dependencias, opciones:
* Crear dependencia.
* Editar dependencia.
**/
$ruta_raiz = "../..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>
<script>
function llamaCuerpo(parametros){    
    top.frames['mainFrame'].location.href=parametros;

}
</script>
<body>
<form name='frmMnuUsuarios' action='../formAdministracion.php' method="post">
<br>
<br>
<?php if($_SESSION['inst_codi']==$acceso_ciudadano_inst){ ?>
<center>
<table width="32%" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
  <tr >
    <td colspan="2" class="titulos4"><div align="center"><strong>No tiene acceso</strong></div></td>
  </tr>
    <tr>
    <td align="center" class="listado2">
        <center><input align="middle" class="botones" type="submit" name="Submit" value="Regresar"></center>
    </td>
    </tr>
</table>
</center>
<?php }else{ ?>
<center>
  <table width="32%" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
  <tr >
    <td colspan="2" class="titulos4"><div align="center"><strong>Administraci&oacute;n de <?=trim(($descDependencia))."s"?></strong></div></td>
  </tr>
  <tr>
    <td  class="listado2" width="98%">
        <?php
                $parametrosFuncion = "adm_dependencias_nuevo.php?accion=1&des_activar=3";
                $parametrosFuncion = "'".$parametrosFuncion."'";
            ?>
	<a onclick="llamaCuerpo(<?=$parametrosFuncion?>);" href='javascript:void(0);' class="vinculos" target='mainFrame'>&nbsp;1. Crear <?=$descDependencia?></a>
    </td>
  </tr>
  <tr >
    <td  class="listado2" width="98%">
        <?php
                $parametrosFuncion = "adm_dependencias_nuevo.php?accion=2&des_activar=3";
                $parametrosFuncion = "'".$parametrosFuncion."'";
            ?>
	<a onclick="llamaCuerpo(<?=$parametrosFuncion?>);" href='javascript:void(0);' class="vinculos" target='mainFrame'>&nbsp;2. Editar <?=$descDependencia?></a>
    </td>
  </tr>
 
    <tr>
    <td  class="listado2" width="98%">
        <?php
                $inst_codiv = $_SESSION['inst_codi'];
                $parametrosFuncion = "../tbasicas/listados.php?var=dpc&de=menu&inst=$inst_codiv";
                $parametrosFuncion = "'".$parametrosFuncion."'";
            ?>
	<a onclick="llamaCuerpo(<?=$parametrosFuncion?>);" href='javascript:void(0);' class="vinculos" target='mainFrame'>&nbsp;3. Lista de <?=$descDependencia?></a>
    </td>
  </tr>  
  <tr> 
    <td align="center" class="listado2">
	<center><input align="middle" class="botones" type="submit" name="Submit" value="Regresar"></center>
    </td> 
  </tr>
</table>
</center>
<?php } ?>
</form>
</body>
</html>
