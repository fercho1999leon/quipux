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
    include_once "respaldo_funciones.php";
    include_once "$ruta_raiz/obtenerdatos.php";

    include_once "$ruta_raiz/js/ajax.js";
    //  if($_SESSION["usua_perm_backup"]!=1) {
    //      echo html_error("Lo sentimos, usted no tiene permisos suficientes para acceder a esta p&aacute;gina.");
    //      die("");
    //  }  
    echo "<html>".html_head();

    $txt_resp_soli_codi = trim(limpiar_sql($_GET["txt_resp_soli_codi"]));
    $txt_tipo_lista= trim(limpiar_sql($_GET["txt_tipo_lista"]));
    $txt_tipo_ventana= trim(limpiar_sql($_GET["tipo_ventana"]));
    $usua_codi_actual=$_SESSION["usua_codi"];    

    //Codigo de usuario cuando el Administrador solicita respaldo de otra persona   
    $txt_usuario_codi= trim(limpiar_sql($_GET["usr_codigo"]));

    if($txt_tipo_lista == 5 or $txt_tipo_lista == 8){

        //Se consulta datos de destinatario
        $usuario = ObtenerDatosUsuario($txt_usuario_codi, $db);

        //Datos de usuario
        $usr_cedula = $usuario["cedula"];
        $usr_nombre = $usuario["usua_nombre"];
        $usr_apellido = $usuario["usua_apellido"];
        $usr_depe = $usuario["depe_codi"];
        $usr_titulo = $usuario["titulo"];
        $usr_email = $usuario["email"] ;
        $usr_cargo = $usuario["cargo"];
        $usr_depe_nombre = $usuario["dependencia"];
        $usua_codi_solicita=$usuario["usua_codi"];
        $usr_institucion= $usuario["institucion"];
        $usr_cargo_tipo = $usuario["cargo_tipo"];
        $usr_perfil = $usuario["perfil"];

        $fecha_solicita = date("Y-m-d");
        $fecha_inicio_doc = date("Y-m-d");
        $fecha_fin_doc = date("Y-m-d");
        $fecha_inicio_res = date("Y-m-d");

    }
    else{

        //Se consulta datos de la solicitud
        $solicitud = ObtenerDatosSolicitud($txt_resp_soli_codi, $usua_codi_actual, $db);

        //Datos de usuario
        $usr_cedula = $solicitud["usua_cedula"];
        $usr_nombre = $solicitud["usua_nomb_sol"];
        $usr_apellido = $solicitud["usua_ape_sol"];
        $usr_depe = $solicitud["usua_depe_codi"];
        $usr_titulo = $solicitud["usua_titulo_sol"];
        $usr_email = $solicitud["usua_email_sol"] ;
        $usr_cargo = $solicitud["usua_cargo_sol"];
        $usr_depe_nombre = $solicitud["usua_depe_nombre"];
        $usua_codi_solicita=$solicitud["usua_codi_solicita"];
        $usr_institucion= $solicitud["usua_inst_nombre"];
        $usr_cargo_tipo = $solicitud["usua_cargo_tipo"];
        $usr_perfil = $solicitud["usua_perfil"];
        
        //Datos de solicitud
        $resp_soli_codi = $solicitud["resp_soli_codi"];
        $estado_solicitud = $solicitud["estado_solicitud"];
        $estado_resp =  $solicitud["estado_respaldo"];
        $comentario = $solicitud["comentario"];
        $fecha_solicita = $solicitud["fecha_solicita"];
        $estado_nombre_sol = $solicitud["estado_nombre_sol"];
        $estado_nombre_resp =$solicitud["estado_nombre_resp"];
        $fecha_inicio_doc = $solicitud["fecha_inicio_doc"];
        $fecha_fin_doc = $solicitud["fecha_fin_doc"];
        $fecha_inicio_res = $solicitud["fecha_inicio_ejec"];
        $fecha_fin_res = $solicitud["fecha_fin_ejec"];
        $fecha_ejecutar =  $solicitud["fecha_ejecutar"];        
        $txt_codigo_documento = $solicitud["radi_nume_radi"];
        $txt_numero_documento = $solicitud["radi_nume_text"];
        $observacion = $solicitud["observacion_hist"];
    }
   
    $read = "readonly";

    //Datos a guardar
    if($estado_nombre == "Nueva")
        $estado_solicitud = 1;
   
    if($estado_solicitud != 1 and $estado_solicitud != "")
        $read_C = "readonly";

    if($estado_solicitud == 3 and $txt_tipo_lista == 11)
        $read_C1 = "";
    else
        $read_C1 = "readonly";

    //Se consulta usuario que autoriza
    $var_aprueba = 0;
    $usua_codi_autoriza = ObtenerCodigoUsuarioAutoriza(33,0,0,$_SESSION["usua_codi"],$db);
    if ($usua_codi_autoriza == $_SESSION["usua_codi"])
        $var_aprueba = 1;

    //Se valida link para seleccionar usuario AIQ y SA
    if($txt_tipo_lista == 5 or $txt_tipo_lista == 8)
        $link_selecciona_usuario = '<a href="javascript:;" onClick="metodoBuscarUsuario();" class="Ntooltip"><font color="blue">Seleccionar Servidor Público</font></a>';
    
    //Se valida link para seleccionar documento AIQ
    $usua_codi_hist = -1;
    $link_selecciona_documento = "";
    if($txt_tipo_lista == 5 or ($txt_tipo_lista == 6 and $estado_solicitud == 1)){
        
        $link_selecciona_documento = '&nbsp<a href="javascript:;" onClick="metodoBuscarDocumento();" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/document_attach.jpg" width="17" height="17" alt="Seleccionar Documento" border="0"><font color="blue">Seleccionar Documento</font></a>';

        $var_codigo_documento= trim(limpiar_sql($_GET["codigo_documento"]));
        $var_numero_documento= trim(limpiar_sql($_GET["numero_documento"]));

        if($var_codigo_documento!="")
            $txt_codigo_documento = $var_codigo_documento;
        if($var_numero_documento!="")
            $txt_numero_documento = $var_numero_documento;

        //Se consulta usuario que realiza la acción    
        if($resp_soli_codi!="" and $resp_soli_codi != 0)
            $usua_codi_hist = ConsultarUsuarioAccion($resp_soli_codi, $db);
        
    }
    
    //popup
    if($txt_codigo_documento != "" && $txt_numero_documento != "")
        $link_ver_documento = '&nbsp<a href="javascript:;" onClick="metodoVerDocumento();" class="Ntooltip"><img src="'.$ruta_raiz.'/iconos/popup.png" width="17" height="17" alt="Ver Documento" border="0"><font color="blue"></font></a>';
    
    //Propiedades
    $ancho1 = "14%";
    $ancho2 = "33%";
    $size_txt = "28";
?>

<link rel="stylesheet" type="text/css" href="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.css">
<script type="text/javascript" src="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.js"></script>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/funciones.js"></script>

<script language="JavaScript" type="text/javascript" >
    var dateAvailable1 = new ctlSpiffyCalendarBox('dateAvailable1', 'formulario', 'txt_fecha_inicio_doc','btnDate1','<?=$fecha_inicio_doc?>',scBTNMODE_CUSTOMBLUE);
    var dateAvailable2 = new ctlSpiffyCalendarBox('dateAvailable2', 'formulario', 'txt_fecha_fin_doc','btnDate2','<?=$fecha_fin_doc?>',scBTNMODE_CUSTOMBLUE);  
</script>

<script language="JavaScript" type="text/javascript" >

    function valida_datos(accion){
        valido = 1;
        mensaje = "";
        if(esRangoFechaValido(document.formulario.txt_fecha_inicio_doc.value, document.formulario.txt_fecha_fin_doc.value)==0){
            valido = 0;
            mensaje += "La Fecha Inicio debe ser menor o igual que la Fecha Fin. \n";
        }
        
        //Validación de Fecha fin
        var fecha = new Date();
        var mes = fecha.getMonth()+1;       
        if(mes<10) mes = "0"+mes;
        var dia = fecha.getDate();
        if(dia<10) dia = "0"+dia;
        var fecha_actual =  fecha.getFullYear() + "-" + mes + "-" + dia;
        if(esRangoFechaValido(document.formulario.txt_fecha_fin_doc.value, fecha_actual)==0){
            valido = 0;
            mensaje += "La Fecha Fin debe ser menor o igual que la Fecha Actual. \n";
        }
        
        if (document.getElementById('txt_usua_codi_solicita').value == ""){
            valido = 0;
            mensaje += "Debe seleccionar el usuario solicitante de los respaldos. \n";
        }
        if(accion == 5 && document.formulario.txt_fecha_ejecutar.value == ""){
            valido = 0;
            mensaje += "Debe ingresar una fecha válida. \n";
        }
        
        //Se valida documento de solicitud asociado
        tipo_lista = document.getElementById("txt_tipo_lista").value;        
        usua_codi_hist=document.getElementById("txt_usua_codi_hist").value;
        usua_codi_solicita=document.getElementById("txt_usua_codi_solicita").value;        
        if((accion == 2) && (tipo_lista == 5 || tipo_lista == 6) && (document.formulario.txt_codigo_documento.value == "") && (usua_codi_solicita != usua_codi_hist)){
            valido = 0;
            mensaje += "Debe ingresar el documento con la solicitud de respaldo del usuario. \n";
        }

        if(mensaje != "")
            alert(mensaje);
        return valido;
    }

   function metodoGuardar(accion)
   {     
      if(valida_datos(accion) == 1)
      {
        document.getElementById("txt_accion").value = accion;
        document.formulario.submit();
      }
   }
   
   function metodoGuardarAccion(accion)
   {   
      if(valida_datos(accion) == 1) //Para acción de Eliminar y Rechazar.
      {
        document.getElementById("txt_accion").value = accion;
        document.formulario.action = "respaldo_acciones.php";
        document.formulario.submit();
      }
   }
    
   function metodoCerrar()
   {
      tipo_ventana = document.getElementById("txt_tipo_ventana").value;
      tipo_lista = document.getElementById("txt_tipo_lista").value;
      if(tipo_ventana == "popup")
        window.close();
      else{
        if(tipo_lista == 1 || tipo_lista == 2 || tipo_lista == 6 || tipo_lista == 9 || tipo_lista == 11)
            window.location="respaldo_lista.php?txt_tipo_lista="+tipo_lista;
        else if(tipo_lista == 8 || tipo_lista == 10)
            window.location="backup_usuarios_menu.php";
        else
            window.location="respaldo_menu.php";
      }
   }

   function metodoBuscarUsuario()
   {
        tipo_lista = document.getElementById("txt_tipo_lista").value;
        location.href ='respaldo_usuario_buscar.php?txt_tipo_lista=' + tipo_lista;
        
   }
   
   function metodoBuscarDocumento()
   {
        tipo_lista = document.getElementById("txt_tipo_lista").value;
        usuario_codigo = document.getElementById("txt_usua_codi_consulta").value;
        usuario_seleccionado = document.getElementById("txt_usua_codi_solicita").value;
        solicitud_codi = document.getElementById("txt_resp_soli_codi").value;
        if(usuario_codigo == "" && usuario_seleccionado == "")
            alert("Seleccione al Servidor Público que solicita el respaldo");
        else
            location.href ='respaldo_documento.php?txt_tipo_lista='+tipo_lista+'&cod_usuario='+usuario_codigo+'&txt_resp_soli_codi='+solicitud_codi;
        
   }
   
    function metodoVerDocumento(){   
        
        numdoc = document.getElementById("txt_codigo_documento").value;
        txtdoc = document.getElementById("txt_doc_solicitud").value;
        var_envio='<?=$ruta_raiz?>/verradicado.php?verrad='+numdoc+'&textrad='+txtdoc+'&menu_ver_tmp=3&tipo_ventana=popup';
        window.open(var_envio,numdoc,"height=450,width=750,scrollbars=yes");
    }
</script>

<body>
<div id="spiffycalendar" class="text"></div>
 <center>
    <form name="formulario" action="respaldo_solicitud_grabar.php" method="post">
        <input type="hidden" name="txt_resp_soli_codi" id="txt_resp_soli_codi" value="<?php echo $resp_soli_codi; ?>">
        <input type="hidden" name="txt_usua_codi_solicita" id="txt_usua_codi_solicita" value="<?php echo $usua_codi_solicita; ?>">
        <input type="hidden" name="txt_estado_solicitud" id="txt_estado_solicitud" value="<?php echo $estado_solicitud; ?>">
        <input type="hidden" name="txt_accion" id="txt_accion" value="" value="0">
        <input type="hidden" name="txt_estado_respaldo" id="txt_estado_respaldo" value="<?php echo $estado_resp; ?>">
        <input type="hidden" name="txt_tipo_lista" id="txt_tipo_lista" value="<?php echo $txt_tipo_lista; ?>">
        <input type="hidden" name="txt_tipo_ventana" id="txt_tipo_ventana" value="<?php echo $txt_tipo_ventana; ?>">
        <input type="hidden" name="txt_cargo_tipo" id="txt_cargo_tipo" value="<?php echo $usr_cargo_tipo; ?>">
        <input type="hidden" name="txt_usr_depe" id="txt_usr_depe" value="<?php echo $usr_depe; ?>">
        <input type="hidden" name="txt_usua_codi_consulta" id="txt_usua_codi_consulta" value="<?php echo $txt_usuario_codi; ?>">
        <input type="hidden" name="txt_codigo_documento" id="txt_codigo_documento" value="<?php echo $txt_codigo_documento; ?>">
        <input type="hidden" name="txt_usua_codi_hist" id="txt_usua_codi_hist" value="<?php echo $usua_codi_hist; ?>">
        <br>
        <table width="100%" border="1" align="center" class="t_bordeGris">
            <tr><td class="titulos3" colspan="4" align="center">Solicitud No. <?php echo $resp_soli_codi; ?></td></tr>
            <tr><td class="titulos2" colspan="4">DATOS DE SOLICITANTE</td></tr>
            <tr>
                <td class="titulos2" width="<?php echo $ancho1; ?>">C&eacute;dula:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <input type="text" name="txt_usr_cedula" id="txt_usr_cedula" value="<?php echo $usr_cedula; ?>" size="<?php echo $size_txt; ?>" maxlength="10" <?php echo $read; ?>>
                </td>
                <td class="titulos2" width="<?php echo $ancho1; ?>"></td>
                <td class="listado2" width="<?php echo $ancho2; ?>"><?php echo $link_selecciona_usuario;?></td>                
            </tr>
            <tr>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Nombre:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <input type="text" name="txt_usr_nombre" id="txt_usr_nombre" value="<?php echo $usr_nombre; ?>" size="<?php echo $size_txt; ?>" <?php echo $read; ?>>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Apellido:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <input type="text" name="txt_usr_apellido" id="txt_usr_apellido" value="<?php echo $usr_apellido; ?>" size="<?php echo $size_txt; ?>" <?php echo $read; ?>>
                </td>
            </tr>
            <tr>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Institución:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <input type="text" name="txt_usr_institucion" id="txt_usr_institucion" value="<?php echo $usr_institucion; ?>" size="<?php echo $size_txt; ?>" <?php echo $read; ?>>
                </td>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Área:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <input type="text" name="txt_usr_depe_nombre" id="txt_usr_depe_nombre" value="<?php echo $usr_depe_nombre; ?>" size="<?php echo $size_txt; ?>" <?php echo $read; ?>>
            </tr>
            <tr>           
                <td class="titulos2" width="<?php echo $ancho1; ?>">Puesto:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <input type="text" name="txt_usr_cargo" id="txt_usr_cargo" value="<?php echo $usr_cargo; ?>" size="<?php echo $size_txt; ?>" <?php echo $read; ?>>
                </td>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Perfil:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <input type="text" name="txt_usr_perfil" id="txt_usr_perfil" value="<?php echo $usr_perfil; ?>" size="<?php echo $size_txt; ?>" <?php echo $read; ?>>
                </td>
            </tr>
        </table>        
        <table width="100%" border="1" align="center" class="t_bordeGris">
            <tr><td class="titulos2" colspan="4">DATOS DE SOLICITUD</td></tr>
            <?php if($link_selecciona_documento == "" && $txt_numero_documento == ""){ ?>
                    <input type="hidden" name='txt_doc_solicitud' id="txt_doc_solicitud" value="" size="<?php echo $size_txt; ?>" readonly>
            <?php } else{ ?>
                <tr>
                    <td class="titulos2" width="<?php echo $ancho1; ?>">Documento</td>
                    <td class="listado2" width="<?php echo $ancho2; ?>" colspan ="3">                    
                        <input type="text" name='txt_doc_solicitud' id="txt_doc_solicitud" value="<?php echo $txt_numero_documento; ?>" size="<?php echo $size_txt; ?>"" readonly><?php echo $link_ver_documento; ?><?php echo $link_selecciona_documento;?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Fecha Solic.:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <input type="text" name='txt_fecha_solicita' value='<?=$fecha_solicita?>' size="<?php echo $size_txt; ?>" <?php echo $read; ?>>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Estado:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <input type="text" name="txt_estado_nombre" id="txt_estado_nombre" value="<?php echo $estado_nombre_sol; ?>" size="<?php echo $size_txt; ?>" <?php echo $read; ?>>
                </td>
            </tr>
            <tr>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Fecha Inicio:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <script type="text/javascript">
                        dateAvailable1.dateFormat="yyyy-MM-dd";
                        dateAvailable1.writeControl();
                        lectura = "<?=$read_C;?>";
                        if(lectura == "readonly")
                                dateAvailable1.disable();
                </script>
                </td>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Fecha Fin:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                   <script type="text/javascript">
                    dateAvailable2.dateFormat="yyyy-MM-dd";
                    dateAvailable2.writeControl();
                    lectura = "<?=$read_C;?>";
                    if(lectura == "readonly")
                            dateAvailable2.disable();
                   </script>
            </tr>
            <tr>           
                <td class="titulos2" width="<?php echo $ancho1; ?>">Comentario:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>" colspan="3">
                    <textarea name="txt_comentario" id="txt_comentario" cols="80" rows="2" class="ecajasfecha" <?php echo $read_C; ?>><?php echo $comentario; ?></textarea>
                </td>               
            </tr>            
        </table>        
        <?php if($estado_solicitud != "" and  $estado_solicitud != "1" and $estado_solicitud != "2") {?>
            <script language="JavaScript" type="text/javascript" >
                var dateAvailable3 = new ctlSpiffyCalendarBox('dateAvailable3', 'formulario', 'txt_fecha_inicio_res','btnDate3','<?=$fecha_inicio_res?>',scBTNMODE_CUSTOMBLUE);
                var dateAvailable4 = new ctlSpiffyCalendarBox('dateAvailable4', 'formulario', 'txt_fecha_fin_res','btnDate4','<?=$fecha_fin_res?>',scBTNMODE_CUSTOMBLUE);
                var dateAvailable5 = new ctlSpiffyCalendarBox('dateAvailable5', 'formulario', 'txt_fecha_ejecutar','btnDate5','<?=$fecha_ejecutar?>',scBTNMODE_CUSTOMBLUE);
            </script>        
            <table width="100%" border="1" align="center" class="t_bordeGris">
            <tr><td class="titulos2" colspan="4">DATOS DE RESPALDO</td></tr>
            <tr>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Fecha Inicio:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <script type="text/javascript">
                       dateAvailable3.dateFormat="yyyy-MM-dd";
                       dateAvailable3.writeControl();
                       dateAvailable3.width = 250;
                       lectura = "<?=$read_C;?>";
                       if(lectura == "readonly")
                            dateAvailable3.disable();
                    </script>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Fecha Fin: </td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <script type="text/javascript">
                       dateAvailable4.dateFormat="yyyy-MM-dd";
                       dateAvailable4.writeControl();
                       lectura = "<?=$read_C;?>";
                       if(lectura == "readonly")
                            dateAvailable4.disable();
                    </script>
                </td>
            </tr>
             <tr>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Estado:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                    <input type="text" name="txt_estado_nombre_resp" id="txt_estado_nombre_resp" value="<?php echo $estado_nombre_resp; ?>" size="<?php echo $size_txt; ?>" <?php echo $read; ?>>
                </td>
                <td class="titulos2" width="<?php echo $ancho1; ?>">Fecha Ejecución:</td>
                <td class="listado2" width="<?php echo $ancho2; ?>">
                     <script type="text/javascript">
                       dateAvailable5.dateFormat="yyyy-MM-dd";
                       dateAvailable5.writeControl();
                       lectura = "<?=$read_C1;?>";
                      if(lectura == "readonly")
                            dateAvailable5.disable();
                    </script>
                </td>
            </tr>
            </table>
        <?php } ?>
        <?php if($estado_solicitud == "4" or  $estado_resp == "15") {?>
            <table width="100%" border="1" align="center" class="t_bordeGris">
<!--            <tr><td class="titulos2" colspan="4">DATOS DE ESTADO DE RESPALDO</td></tr>            -->
            <tr>                    
                <td class="titulos2" width="15%">Observación:</td>
                <td class="listado2" colspan="3">
                    <textarea name="txt_observacion" id="txt_observacion" cols="80" rows="1" class="ecajasfecha" readonly><?php echo $observacion; ?></textarea>
                </td> 
            </tr>
            </table>
        <?php } ?>
        <div id='div_grabar_solicitud'></div>
        <div id='div_buscar_usuarios'></div>
        <br>
        <?php if($read_C != "readonly" and ($txt_tipo_lista == 1 or $txt_tipo_lista == 4 or $txt_tipo_lista == 5 or $txt_tipo_lista == 6)){?>
            <input type='button' name='btn_guardar' value='Guardar' class='botones' onClick='metodoGuardar(1);'> 
            <input type='button' name='btn_enviar' value='Enviar' class='botones' onClick='metodoGuardar(2);'>
         <?php }            
            if($txt_tipo_lista == 2 and $var_aprueba == 1 and $estado_solicitud == 2){ ?>
             <input type='button' name='btn_aprobar' value='Aprobar' class='botones' onClick='metodoGuardar(3);'> 
             <input type='button' name='btn_rechazar' value='Rechazar' class='botones' onClick='metodoGuardarAccion(4);'>
         <?php }
            if($read_C != "readonly" and ($txt_tipo_lista == 8 or $txt_tipo_lista == 9)){ ?>
                <input type='button' name='btn_guardar_sol' value='Guardar' class='botones' onClick='metodoGuardar(1);'>
                <input type='button' name='btn_aprobar_sol' value='Solicitar' class='botones' onClick='metodoGuardar(7);'>                
         <?php  }
            if($estado_solicitud == 3 and $txt_tipo_lista == 11){ ?>
                <input type='button' name='btn_calendarizar_sol' value='Guardar' class='botones' onClick='metodoGuardar(5);'>
         <?php }?>
         <?php
            if($estado_solicitud == 1){ ?>
                <input type='button' name='btn_eliminar_sol' value='Eliminar' class='botones' onClick='metodoGuardarAccion(6);'>
         <?php }?>
        <!--input type='button' name='btn_pru' value='Prueba' class='botones' onClick='metodoGuardar(6);' -->
        <input type='button' name='btn_cancelar' value='Regresar' class='botones' onClick='metodoCerrar();'>
      </form>
     </center>
</body>
</html>