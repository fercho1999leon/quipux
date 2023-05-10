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

/*************************************************************************************
** Permite crear los metadatos para la institución y/o áreas                        **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
*************************************************************************************/

$ruta_raiz = "..";

session_start();
include "$ruta_raiz/rec_session.php";

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

?>
<script language="JavaScript" type="text/javascript" >
function llamaCuerpo(parametros){
    top.frames['mainFrame'].location.href=parametros;

}
</script>
<body>
<center>
<form name='formulario' action='../Administracion/formAdministracion.php' method="post">
<br>
<?php if($_SESSION['inst_codi']==$acceso_ciudadano_inst){ ?>
<center>
<table width="32%" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
  <tr >
    <td colspan="2" class="titulos4"><div align="center"><strong>No tiene acceso</strong></div></td>
  </tr>   
</table>
</center>
<?php }else{ ?>
<table border="0" width="40%" cellpadding="0" cellspacing="5" class="borde_tab">
    <tr>
	<td class=titulos4 align="center" >Administraci&oacute;n de Metadatos</td>
    </tr>   
    <tr>
	<td class="listado2">
            <?php
                 $parametrosFuncion = "metadatos.php";
                 $parametrosFuncion = "'".$parametrosFuncion."'";
                 ?>
	    <a onclick="llamaCuerpo(<?=$parametrosFuncion?>);" href="javascript:void(0);" target='mainFrame' class="vinculos">
		<b>1. Administraci&oacute;n de Metadatos</b></a>
	</td>
    </tr>
    <tr>
	<td class="listado2">
            <?php
                 $parametrosFuncion = "metadatos_lista.php";
                 $parametrosFuncion = "'".$parametrosFuncion."'";
                 ?>
	    <a onclick="llamaCuerpo(<?=$parametrosFuncion?>);" href="javascript:void(0);" target='mainFrame' class="vinculos">
		<b>2. Consultar Metadatos</b></a>
	</td>
    </tr>   
    <tr>
        <td align="center" class="listado2">
            <center><input align="middle" class="botones" type="submit" name="Submit" value="Regresar"></center>
        </td>
  </tr>
</table>
<?php } ?>
</form>
</center>
</body>
</html>