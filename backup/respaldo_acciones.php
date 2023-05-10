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
  include_once "$ruta_raiz/js/ajax.js";
  echo "<html>".html_head();
  
  $txt_resp_soli_codi = trim(limpiar_sql($_POST["txt_resp_soli_codi"]));  
  $txt_lista_soli_codi = $_POST["checkValue"];
  $txt_accion = trim(limpiar_sql($_POST["txt_accion"]));
  $txt_codigo_respaldo = trim(limpiar_sql($_POST["txt_codigo_respaldo"]));
  $txt_usua_codi_solicita = trim(limpiar_sql($_GET["txt_usua_solicita"]));
  $txt_tipo_ventana= trim(limpiar_sql($_GET["txt_tipo_ventana"]));

  //Se establece título
  switch ($txt_accion) {
        case 3:
            $titulo = "Aprobación de Solicitud(es)";
            break;
        case 4:
            $titulo = "Rechazo de Solicitud(es)";
            break;           
        case 5:
            $titulo = "Calendarización de Solicitud.";
            break;
        case 6:
            $titulo = "Eliminación de Solicitud.";
            break;
        default:
            break;
  }
      
  if(!$txt_lista_soli_codi){
      //Se establece mensaje de validación.
      switch ($txt_accion) {
          case 3:
              $mensaje = "Debe seleccionar las solicitudes que requiere aprobar.";
              break;
          case 4:
              $mensaje = "Debe seleccionar las solicitudes que requiere rechazar.";
              break;           
          case 5:
              $mensaje = "Debe seleccionar las solicitudes que requiere calendarizar.";
              break;
          default:
              break;
      }
  }
  
  $fecha_ejecutar = date("Y-m-d");
  
?>

<link rel="stylesheet" type="text/css" href="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.css">
<script type="text/javascript" src="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="JavaScript" type="text/javascript" >
    var dateAvailable1 = new ctlSpiffyCalendarBox('dateAvailable1', 'formulario', 'txt_fecha_ejecutar','btnDate1','<?=$fecha_ejecutar?>',scBTNMODE_CUSTOMBLUE);
</script>

<script language="JavaScript" type="text/javascript" >
    
   function metodoEliminar(accion){       
        codigo = document.getElementById("txt_codigo_respaldo").value;       
        if(accion==6){
           nuevoAjax('div_eliminar_respaldo', 'POST', 'backup_usuarios_eliminar.php', 'txt_resp_codi=' + codigo, 'metodoGuardar('+accion+')');
        }       
   }
    
   function metodoGuardar(accion)
   {
       document.getElementById("txt_accion").value = accion;      
       codigo = document.getElementById("txt_resp_soli_codi").value;
       lista = document.getElementById("txt_lista_soli_codi").value;
       
       if(codigo != ""  && codigo != 0){          
            document.formulario.action = "respaldo_solicitud_grabar.php";
            document.formulario.submit();
       }
       else{       
            if(lista != ""){
                if(accion==5 && document.formulario.txt_fecha_ejecutar.value == "")
                    alert("Debe ingresar una fecha válida.");
                else{
                    document.formulario.action = "respaldo_grabar_masivo.php";
                    document.formulario.submit();
                }
            }
            else{
                if(accion==5)
                    alert("Por favor seleccione las solicitudes que requiere Calendarizar");
                else
                    alert("Por favor seleccione las solicitudes que requiere Aprobar o Rechazar");
            }
       }
   }

   function metodoCerrar()
   {
       tipo_ventana = document.getElementById("txt_tipo_ventana").value;
       resp_soli_codi = document.getElementById("txt_resp_soli_codi").value;
       tipo_lista = document.getElementById("txt_tipo_lista").value;
                 
       if(tipo_ventana == "popup")
           if(tipo_lista == 11)
               window.close();
           else
               window.location="respaldo_solicitud.php?txt_resp_soli_codi="+resp_soli_codi+"&txt_tipo_lista="+tipo_lista;
       else{           
            if(tipo_lista == 13)
               window.location="backup_usuarios_estado.php";
            else
               window.location="respaldo_lista.php?txt_tipo_lista="+tipo_lista;
       }

   }

</script>

<body>
 <div id="spiffycalendar" class="text"></div>
 <center>
     <form name="formulario" action="respaldo_solicitud_grabar.php" method="post">        
        <input type="hidden" name="txt_accion" id="txt_accion" value="" size="20" value="0">
        <input type="hidden" name="txt_resp_soli_codi" id="txt_resp_soli_codi" value="<?php echo $txt_resp_soli_codi; ?>">
        <input type="hidden" name="txt_usua_codi_solicita" id="txt_usua_codi_solicita" value="<?php echo $txt_usua_codi_solicita; ?>">
        <input type="hidden" name="txt_tipo_ventana" id="txt_tipo_ventana" value="<?php echo $txt_tipo_ventana; ?>">
        <input type="hidden" name="txt_tipo_lista" id="txt_tipo_lista" value="<?php echo $txt_tipo_lista; ?>">
        <input type="hidden" name="txt_resp_soli_codi" id="txt_resp_soli_codi" size="20" value="<?php echo $txt_resp_soli_codi; ?>">
        <input type="hidden" name="txt_codigo_respaldo" id="txt_codigo_respaldo" size="20" value="<?php echo $txt_codigo_respaldo; ?>">
        <? if($txt_lista_soli_codi){
           foreach ($txt_lista_soli_codi as $idLista=>$valor) {  ?>
                <input type="hidden" name="txt_lista_soli_codigos[]" id="txt_lista_soli_codi"  value=<? echo $idLista ?> />
       <?  } }?>
                    
        <table width="100%" border="1" align="center" class="t_bordeGris" id="usr_datos">
        <tr><td class="titulos2" colspan="4" align="center"><?php echo $titulo; $txt_resp_soli_codi; ?></td></tr>
       
        <? //Rechazo de solicitud
            if(($txt_accion == 4) and ($txt_lista_soli_codi or $txt_resp_soli_codi)) { ?>
                <input type="hidden" name="txt_lista_soli_codi" id ="txt_lista_soli_codi" value="" />
                <tr>
                    <td width="10%" class="titulos2">  Comentario:</td>
                    <td width="40%" class="listado2" colspan="3"><textarea name="txt_comentario_rechazo" id="txt_comentario_rechazo" cols="110" rows="2" class="ecajasfecha" <?php echo $read_C; ?>></textarea></td>
                </tr>
        <?}?>

       <? //Eliminación de solicitud
            if($txt_accion == 6 and  $txt_resp_soli_codi) { ?>
                <input type="hidden" name="txt_lista_soli_codi" id ="txt_lista_soli_codi" value="" />
                <tr>
                    <td width="10%" class="titulos2">  Comentario:</td>
                    <td width="40%" class="listado2" colspan="3"><textarea name="txt_comentario_cancela" id="txt_comentario_cancela" cols="110" rows="2" class="ecajasfecha" <?php echo $read_C; ?>></textarea></td>
                </tr>
        <?}?>

        <? //Fecha de ejecución solicitud
            if($txt_tipo_lista == 11 and $txt_accion == 5 and ($txt_lista_soli_codi or $txt_resp_soli_codi)) { ?>
                <input type="hidden" name="txt_lista_soli_codi" id ="txt_lista_soli_codi" value="" />
                <tr>
                    <td width="10%" class="titulos2">  Fecha a ejecutar:</td>
                    <td width="40%" class="listado2" colspan="3">
                        <script type="text/javascript">
                            dateAvailable1.dateFormat="yyyy-MM-dd";
                            dateAvailable1.writeControl();
                        </script>
                    </td>
                </tr>
        <?}?>     

        <? //Mensaje de validación
        if (($txt_tipo_lista == 2 or $txt_tipo_lista == 11) and (!$txt_lista_soli_codi and !$txt_resp_soli_codi)){ ?>
            <input type="hidden" name="txt_lista_soli_codi" id ="txt_lista_soli_codi" value="" />
            <tr><td class="listado2" colspan="4" align="center"><? echo $mensaje; ?></td></tr>
        <?  }  ?>
        </table>
                
        <? //Rechazo de solicitud
            if(($txt_accion == 4) and ($txt_lista_soli_codi or $txt_resp_soli_codi)) { ?>
                <input type='button' name='btn_rechazar' value='Rechazar' class='botones' onClick='metodoGuardar(4);'>
        <?}?>

        <? //Eliminación de solicitud
            if($txt_accion == 6){
                if($txt_resp_soli_codi && $txt_codigo_respaldo) { ?>
                    <input type='button' name='btn_fecha_sol' value='Eliminar' class='botones' onClick='metodoEliminar(6);'>                    
        <?      }
                else if($txt_resp_soli_codi){ ?>
                    <input type='button' name='btn_fecha_sol' value='Eliminar' class='botones' onClick='metodoGuardar(6);'>
        <?      }
            }
        ?>

        <? //Fecha de ejecución solicitud
            if($txt_tipo_lista == 11 and $txt_accion == 5 and ($txt_lista_soli_codi or $txt_resp_soli_codi)) { ?>
            <input type='button' name='btn_fecha_sol' value='Grabar' class='botones' onClick='metodoGuardar(5);'>
        <?}?>
        <input type='button' name='btn_cancelar' value='Regresar' class='botones' onClick='metodoCerrar();'>
        <div id='div_eliminar_respaldo'></div>
     </form>
     </center>
 </body>