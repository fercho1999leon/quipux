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
/*************************************************************************************
** Permite a cada usuario solicitar respaldos de la documentación                   **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
*************************************************************************************/

  $ruta_raiz = "..";
  session_start();
  include_once "$ruta_raiz/rec_session.php";
  require_once "$ruta_raiz/funciones.php"; //para traer funciones p_get y p_post
  include_once "$ruta_raiz/funciones_interfaz.php";
  include_once "$ruta_raiz/js/ajax.js";
//  if($_SESSION["usua_perm_backup"]!=1) {
//      echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
//      die("");
//  }
  //echo "<link rel='stylesheet' type='text/css' href='$ruta_raiz/js/spiffyCal/spiffyCal_v2_1.css'>";
  echo "<html>".html_head();
  if (!$menu_ver) $menu_ver=1;	//define la pestaña de vista general por defecto  
?>

<?php

$tipo_ventana = trim(limpiar_sql($_GET["tipo_ventana"]));;

if ($tipo_ventana=='popup'){
?>
<script type="text/javascript">
function llamaCuerpo(parametros){
     location.href = parametros;
}
</script>
<?php }else{
    ?>
<script type="text/javascript">
function llamaCuerpo(parametros){    
    top.frames['mainFrame'].location.href=parametros;
}
</script>
<?php }?>

<body>
<center>
<table width="90%" border="0" cellpadding="0" cellspacing="1">
    <tr>
        <td class="titulos5" align="center">
            <br>Solicitud de Respaldos<br>&nbsp;
        </td>
    </tr>
</table>
</center>
<center>
    <table border=0 align='center' cellpadding="0" cellspacing="0" width="90%" >
      <?
        $txt_resp_soli_codi = trim(limpiar_sql($_GET["txt_resp_soli_codi"]));
        $txt_tipo_lista= trim(limpiar_sql($_GET["txt_tipo_lista"]));
	$datos1 = "";$datos2 = "";
	${"datos".$menu_ver} = "_R";	//Pone la pestaña resaltada que el usuario eligio        
      ?>
      <tr>
      <td height="25" width="100%" class="listado2">
          <table border=0 width=100% cellpadding="0" cellspacing="0">
              <tr>
                <td height="99" rowspan="4" width="1%" valign="top" class="listado2">&nbsp;</td>
                <td valign="bottom" class="" >
                     <?php                       
                        $parametrosFuncion = "respaldo_informacion.php?txt_resp_soli_codi=$txt_resp_soli_codi&txt_tipo_lista=$txt_tipo_lista";
                        $parametrosFuncion = "'".$parametrosFuncion."&menu_ver=1"."&tipo_ventana=$tipo_ventana'";
                        $funcionjava = "llamaCuerpo($parametrosFuncion);";
                    ?>
                    <a onclick="<?php echo $funcionjava;?>" href='javascript:void(0);' >
                        <img alt="" src="<?=$ruta_raiz?>/imagenes/infoGeneral<?=$datos1?>.gif" border=0 width="110" height="25">
                    </a>
                    <?php                       
                        $parametrosFuncion = "respaldo_informacion.php?txt_resp_soli_codi=$txt_resp_soli_codi&txt_tipo_lista=$txt_tipo_lista";
                        $parametrosFuncion = "'".$parametrosFuncion."&menu_ver=2"."&tipo_ventana=$tipo_ventana'";
                        $funcionjava = "llamaCuerpo($parametrosFuncion);";
                    ?>
                    <a onclick="<?php echo $funcionjava;?>" href='javascript:void(0);' >
                        <img alt=""  src="<?=$ruta_raiz?>/imagenes/historico<?=$datos2?>.gif" border=0 width="110" height="25">
                    </a>
                </td>
             </tr>
         </table>
      </td>
      <td height="149" rowspan="4" class=""><td>
      <tr>
        <td  bgcolor="" width="100%" height="100">
        <?
            error_reporting(7);

            switch ($menu_ver) {
                case 1://Solicitud
                    include "./respaldo_solicitud.php";
                    break;
                case 2://Recorrido 
                    include "./respaldo_historico_lista.php";
                    break;              
                default:
                    break;
            }
        ?>
      </td>
    </tr>   
</table></center>

</body>
</html>
