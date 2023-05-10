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
  include_once "respaldo_funciones.php";
  include_once "$ruta_raiz/js/ajax.js";
  echo "<html>".html_head();
  
  $txt_resp_soli_codi = trim(limpiar_sql($_GET["resp_soli_codi"]));
  $txt_accion_form = trim(limpiar_sql($_POST["txt_accion_form"]));  
  $txt_comentario= trim(limpiar_sql($_POST["txt_comentario"]));
  
  if($txt_accion_form == "1"){
    //Se consulta datos de solicitud
    $txt_accion = 9;
    $datos = ObtenerSolicitudPorCodigo($txt_resp_soli_codi,$db);
    $destinatario = 0; //Se envía el correo al equipo de soporte.
    $remitente = $datos["usua_codi_solicita"];
    $datos["comentario_solicita"] = $txt_comentario;
    //Se envía correo
    EnviarCorreo($txt_accion, $destinatario, $remitente, $datos, $ruta_raiz, $db);

    //Se guarda histórico de la acción
    $accion = array();
    unset($accion);
    $accion["RESP_SOLI_CODI"] = $datos["resp_soli_codi"];
    $accion["USUA_CODI"] = $_SESSION["usua_codi"];
    $accion["ACCION"] = 86;
    $accion["COMENTARIO"] = "Solicitud de descarga de respaldo enviada al correo electrónico de la STI. Motivo: $txt_comentario";
    $accion["ESTADO_SOLICITUD"] = $datos["estado_solicitud"];
    $accion["ESTADO_RESPALDO"] = $datos["estado_respaldo"]; 
    GuardarHistoricoSolicitud($accion,$db);  
  }
  
?>

<link rel="stylesheet" type="text/css" href="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.css">
<script type="text/javascript" src="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="JavaScript" type="text/javascript" >
    var dateAvailable1 = new ctlSpiffyCalendarBox('dateAvailable1', 'formulario', 'txt_fecha_ejecutar','btnDate1','<?=$fecha_ejecutar?>',scBTNMODE_CUSTOMBLUE);
</script>

<script language="JavaScript" type="text/javascript" >
 function metodoGuardar(accion)
 {
    //var motivo = document.getElementById("txt_comentario").value;    
    if(document.getElementById("txt_comentario").value == "")
        alert("Por favor ingrese el motivo de la solicitud de descarga.");
    else{
        document.getElementById("txt_accion_form").value = accion;
        solicitud = document.getElementById("txt_resp_soli_codi").value;
        document.formulario.action = 'respaldo_notificacion.php?resp_soli_codi='+solicitud;
        document.formulario.submit();
    }
 }
</script> 
<body>
 <div id="spiffycalendar" class="text"></div>
 <center>
     <form name="formulario" action="" method="post">       
        <input type="hidden" name="txt_resp_soli_codi" id="txt_resp_soli_codi" value="<?php echo $txt_resp_soli_codi; ?>">
        <input type="hidden" name="txt_accion_form" id="txt_accion_form" value="<?php echo $txt_accion_form; ?>">
        <br>
        <table width="40%" border="2" align="center" class="t_bordeGris">            
            <?php if($txt_accion_form == "1"){ ?>
                <tr>
                    <td width="100%" height="30" class="listado2">
                    <span class=etexto><center><B>La descarga de su respaldo fue solicitada a la STI.</B></center></span>
                    </td>
                </tr>
                <tr>
                    <td height="30" class="listado2">
                        <center><input type='button' name='btn_aceptar' value='Aceptar' class='botones' onClick='window.close();'></center>
                    </td>
                </tr>            
            <?php  }
            else{ ?>
                 <tr>
                    <td width="100%" class="titulos3" colspan ="2">Solicitud para descargar respaldo</td>                    
                </tr>
                <tr>
                    <td width="10%" class="titulos2">  Motivo:</td>
                    <td width="40%" class="listado2" ><textarea name="txt_comentario" id="txt_comentario" cols="70" rows="2" class="ecajasfecha"></textarea></td>
                </tr>
                <tr>
                    <td height="100%" class="listado2" colspan="2" align="center">
                        <input type='button' name='btn_guardar' value='Guardar' class='botones' onClick='metodoGuardar(1);'>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <input type='button' name='btn_cancelar' value='Regresar' class='botones' onClick='window.close();'>
                    </td>
                </tr>
         <?php }?>
                
        </table>
     </form>
     </center>
 </body>