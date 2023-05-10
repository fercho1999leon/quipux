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
/*************************************************************************************************
**  En esta pagina se maneja la interfaz para crear, modificar y responder radicados		**
**												**
**  PARAMETROS:											**
**    	$accion		Accion a realizar: Nuevo - Responder - Editar				**
**    	$ent		(opcional, nuevo) si es un documento de entrada=2 o de salida=1 	**
**    	$nurad		(opcional, edicion o modificacion) es el numero de radicado		**
**    	$textrad	(opcional, edicion o modificacion) es el texto del radicado		**
**												**
**  INCLUDES:											**
**	../include/db/ConnectionHandler.php	Maneja las conexiones con la BDD		**
**	secciones_tipos_doc.php			Funcion Javascript que define q campos se 	**
**						mostraran en la pagina				**
**												**
**												**
**												**
**												**
**                                                                                              **
**                                                                                              **
**************************************************************************************************/

session_start();
$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
require_once "$ruta_raiz/tx/tx_actualiza_opcion_imp.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/seguridad/obtener_nivel_seguridad.php";
// se incluyo por register globals.
$ent=limpiar_numero($_GET['ent']);
$accion = limpiar_sql($_GET['accion']);
$krd = $_SESSION['krd'];
$mensaje = $_GET['mensaje'];
$nurad  = limpiar_numero($_GET['nurad']);
$textrad = limpiar_sql($_GET['textrad']);
//PARA INSERTAR EN HISTORICO AL MOMENTO DE RESPONDER DE BANDEJA COMPARTIDA.
$compResponder = $_GET['compResponder'];
if ($compResponder==1 and $nurad!=''){
    $jefe = array();
    $jefe = ObtenerDatosUsuario($_SESSION['usua_codi_jefe'],$db);
    $observacion = '';
    //INSERTAR EN TABLA HISTORICO
   include_once "$ruta_raiz/include/tx/Tx.php";
   $tx = new Tx($db);
   $numrad = array();
   //$numrad[0] = $nurad;
   $numrad[0] = $nurad;
   //print_r($numrad);
    $tx->reasignar( $numrad, $_SESSION['usua_codi_jefe'], $_SESSION['usua_codi'], $observacion, date("Y-m-d H:i:s"), false, $carpeta);            
}


//Se incluyo para prevenir  CSRF ( Cross-Site Request Forgeries)
$token = md5(uniqid(rand(), true));
$_SESSION['token'] = $token;


//En el caso de no existir $nurad (por cualquier error) se lo manejara como nuevo
if (trim($nurad)=="") $accion="Nuevo";
if ($accion=="Nuevo") {$nurad=""; $textrad="";}

/**
* Verifica si el documento es de entrada o salida, ingresa al if solo cuando es responder o editar radicado.
**/

if($nurad) {
    $textrad = trim($textrad);
    $nurad=trim($nurad);

    if (substr($nurad,-1)==2)
	$ent = 2;
    else
	$ent = 1;
    //$raditipo = $ent;
}

if($ent == 1)
    $raditipo = 1;
else
    $raditipo = 2;
if ($_SESSION["tipo_usuario"]==2) $raditipo = 7; // Ciudadanos
$optipoNotaBusq = ObtenerDatosOpcImpresion($nurad,$db);


$opc_nota_inicial=$optipoNotaBusq['OPC_IMP_TIPO_NOTA'];



echo "<html>".html_head();
?>

<link rel="stylesheet" type="text/css" href="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.css">

<script type="text/javascript" src="<?=$ruta_raiz?>/js/crea_combos_2.js"></script>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/funciones.js"></script>

<? include_once "$ruta_raiz/js/ajax.js"; ?>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/base64.js"></script>
<script type="text/javascript" src="<?=$ruta_raiz?>/js/md5.js"></script>
<script type="text/javascript">
 var objetoImpresion=new Array();
 
    function vista_previa() {
        windowprops = "top=100,left=100,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=500,height=300";
        url = <?="'$ruta_raiz/VistaPrevia.php?verrad=$nurad&archivo=&textrad=$textrad'"?>;
        window.open(url , "Vista_Previa_<?=$noRad?>", windowprops);
        return;
    }


     contador_tiempo = 0;
     function timer_activar_session() {
        if (contador_tiempo < 3) {
            if (contador_tiempo > 0 && contador_tiempo < 3) {
                nuevoAjax('div_activar_sesion', 'POST', 'activar_session.php', '');
            }
            ++contador_tiempo;
            timerID = setTimeout("timer_activar_session()", 1200000); // 20*60*1000 = 1200000
        } else {
            clearTimeout(timerID);
            document.formulario.opc_grab.value = '2';
            if (document.formulario.documento_us1.value.length > 0 && document.formulario.documento_us2.value.length > 0 )
                document.formulario.submit();
            else
                alert('Su sesión ha expirado.\nPor favor seleccione y copie el texto introducido en su editor \ny peguelo en otro sitio para que este no se pierda.');
            return;
        }
    }
    timer_activar_session();

    function validarfecha()
    {
        // Controla que la informacion ingresada en la fecha del oficio sea correcta
        var fechaActual = new Date();
        fecha_doc = document.formulario.fecha_doc.value;
        dias_doc=fecha_doc.substring(0,2);
        mes_doc=fecha_doc.substring(3,5);
        ano_doc=fecha_doc.substring(6,10);
        var fecha = new Date(ano_doc,mes_doc-1, dias_doc);
        var tiempoRestante = fechaActual.getTime() - fecha.getTime();
        var dias = Math.floor(tiempoRestante / (1000 * 60 * 60 * 24));
        fecha_doc = 'no';
        if (dias >366 && dias < 1500)
        {
            alert("El documento tiene fecha anterior a un año!!");
        }
        else
        {
            if (dias > 1500) {
                    alert("Verifique la fecha del documento!!");
            } else {
                if (dias < 0 && <?=$ent?>==2)	{
                    alert("Verifique la fecha del documento, no puede ser superior a la fecha de hoy.");
                }
                else
                    fecha_doc = "ok";
            }
        }
        return fecha_doc;
    }

function refrescar_pagina(origen,cod_impresion)
{

    // Muestra la lista de usuarios Remitente, Destinatario y Con Copia
    ent = document.formulario.hidd_ent.value;    
    radi_lista_nombre = document.formulario.radi_lista_nombre.value;
    remitente = document.formulario.documento_us1.value;
    destinatario = document.formulario.documento_us2.value
    copia = document.formulario.concopiaa.value;
    radi_lista_dest = document.formulario.radi_lista_dest.value;
    //nuevoAjax('ifr_usr', 'GET', 'lista_concopiaa.php', 'documento_us1='+ remitente + '&documento_us2=' + destinatario + '&concopiaa=' + copia);
    document.getElementById('ifr_usr').src='lista_concopiaa.php?documento_us1='+ remitente + '&documento_us2=' + destinatario + '&concopiaa=' + copia+'&radi_lista_nombre='+radi_lista_nombre+'&ent='+ent+'&radi_lista_dest='+radi_lista_dest;
    //CambiaTipoDocu();
    /**CAmbiar opcion tipo radicado**/
    raditipo = document.formulario.raditipo.value;
    flag_inst = document.formulario.flag_inst.value;
    flag_inst_m = document.formulario.flag_inst_m.value;
    //borrar opciones impresion
    if (document.formulario.hidden_actualiza_opciones.value==1){
        datos="codigo_opc="+document.formulario.codiOpcImp.value;
        nuevoAjax('div_borrar_opc_imp', 'GET', '<?=$ruta_raiz?>/tx/tx_borrar_opcion_imp.php', datos); 
        pestanas(1);
    }       
   
    if(origen=="")
    {
	if(raditipo==1 && flag_inst_m==0)
	{
	/**Cambio a Memo tipo documento**/
            for(i=0;i<document.formulario.raditipo.length;i++)
                if(document.formulario.raditipo.options[i].value=='3')
                {
                    dibujar_confirm('Los Destinatarios pertenecen a su instituci&oacute;n, <br>desea cambiar su tipo de documento a Memorando?',
                                    'cambiar_raditipo_confirm('+i+')', '');
//                    if(confirm("Los Destinatarios pertenecen a su institución, \ndesea cambiar su tipo de documento a Memorando? ")){
//                        document.formulario.raditipo.options[i].selected=true;
//                        document.formulario.hidden_radi_actual.value = document.formulario.raditipo.options[i].value;
//                    }
//                    else return false;
                }
        }
	if (raditipo==3 && flag_inst==1)
	{
	/**Cambio a Oficio tipo documento**/
            for(i=0;i<document.formulario.raditipo.length;i++)
                if(document.formulario.raditipo.options[i].value=='1')
                {
                    dibujar_confirm('Los Destinatarios no pertenecen a su instituci&oacute;n, <br>desea cambiar su tipo de documento a Oficio?',
                                    'cambiar_raditipo_confirm('+i+')', '');
//                    if(confirm("Los Destinatarios no pertenecen a su institución, \ndesea cambiar su tipo de documento a Oficio? "))
//                        document.formulario.raditipo.options[i].selected=true;
//                    else return false;
                }
	}
	if(raditipo==2 && flag_inst==1)	{
	/**Si no selecciono un funcionario para registrar un documento externo**/
		alert("Ningún Destinatario pertenece a su institución.\nPor favor revise la lista de destinatarios.");
                document.getElementById('div_alerta_externo').style.display='';
                
                
	}else{
            document.getElementById('div_alerta_externo').style.display='none';
            
        }
    }
    //origen = OI (Opciones de Impresión) Ejecutar funcion para cargar estilos de impresion
    if(document.getElementById("raditipo") != null){        
        document.getElementById('rad_tipo_imp').value = cod_impresion;
        cargarEstiloImpresion();
    }
    if (flag_inst_m==0)
        if (document.getElementById('tr_redirigir'))
        document.getElementById('tr_redirigir').style.display='';
    else
        if(document.getElementById('tr_redirigir'))
        document.getElementById('tr_redirigir').style.display='none';
    return;

}

    function cambiar_raditipo_confirm(opcion) {
        document.formulario.raditipo.options[opcion].selected=true;
        document.formulario.hidden_radi_actual.value = document.formulario.raditipo.options[opcion].value;
        cargarEstiloImpresion();
    }

    function dibujar_confirm(mensaje, func_aceptar, func_cancelar) {
        document.getElementById('div_confirm_bloquear_pantalla').style.display='';
        document.getElementById('div_confirm_pantalla_pequena').style.display='';
        document.getElementById('spn_confirm_mensaje').innerHTML = mensaje;
        document.getElementById('btn_confirm_aceptar').onclick = function() {ocultar_confirm(); if (func_aceptar!='') eval (func_aceptar);};
        document.getElementById('btn_confirm_cancelar').onclick = function() {ocultar_confirm(); if (func_cancelar!='') eval (func_cancelar);};
        return;
    }
    function ocultar_confirm() {
        document.getElementById('div_confirm_bloquear_pantalla').style.display='none';
        document.getElementById('div_confirm_pantalla_pequena').style.display='none';
    }

    function cambiar(){
        
        listDestinatarios = document.getElementById("documento_us1").value.split("--");
        document.getElementById("NumDest").value = listDestinatarios.length;
           
        try {
            if(document.getElementById("NumDest").value>1)
            {
                document.getElementById("D").style.display='none';
            }
            else
            {
                
                if(document.getElementById("radi_tipo_impresion").value==1 )
                {
                    document.getElementById("D").style.display='';
                    document.getElementById("T").style.display='';
                    document.getElementById("N").style.display='';
                    document.getElementById("trCargo").style.display='';
                    document.getElementById("trInstitucion").style.display='';
                    
                }
                if(document.getElementById("radi_tipo_impresion").value==2)
                {
                    document.getElementById("D").style.display='';
                    document.getElementById("T").style.display='none';
                    document.getElementById("N").style.display='none';
                    document.getElementById("trCargo").style.display='';
                    document.getElementById("trInstitucion").style.display='';
                    
                }

                if(document.getElementById("radi_tipo_impresion").value==3 )
                {
                    document.getElementById("D").style.display='none';
                }

                if(document.getElementById("radi_tipo_impresion").value==4 )
                {
                    document.getElementById("D").style.display='';
                    document.getElementById("T").style.display='';
                    document.getElementById("N").style.display='';
                    document.getElementById("trCargo").style.display='';
                    document.getElementById("trInstitucion").style.display='none';
                }

                if(document.getElementById("radi_tipo_impresion").value==5 )
                {
                    document.getElementById("D").style.display='';
                    document.getElementById("T").style.display='';
                    document.getElementById("N").style.display='';
                    document.getElementById("trCargo").style.display='none';
                    document.getElementById("trInstitucion").style.display='';
                }
                 if(document.getElementById("radi_tipo_impresion").value==6 )
                {
                    document.getElementById("D").style.display='';
                    document.getElementById("T").style.display='';
                    document.getElementById("N").style.display='none';
                    document.getElementById("trCargo").style.display='';
                    document.getElementById("trInstitucion").style.display='';
                }
               
                  
            }
        } catch (e) {}
    }

    function mostrar_botones (flag) {
        deshabilitar = true;
        if (flag) deshabilitar = false;
        <? if ($ent != 2) { ?>
            document.formulario.Submit1.disabled = deshabilitar;
        <? } ?>
        document.formulario.Submit2.disabled = deshabilitar;
    }

    var cuerpo_documento = '';
    function cambio_cuerpo(codi_texto) { 
        var texto = '';
        try {
            var oEditor = CKEDITOR.instances.raditexto;
            var texto = (trim(oEditor.getData()));
        }catch (e) {}
        if (codi_texto>0 && cuerpo_documento != texto) {
            if (!confirm('Los cambios realizados en el texto del documento aún no han sido grabados.\n'+
                         'Si realiza esta acción los cambios se perderán.\n¿Desea continuar con la acción solicitada?'))
                return false;
        }
        if (codi_texto==0) {
            if(cuerpo_documento != texto) return true;
        }
        mostrar_botones (false);
        try {
            tipo_docu = document.getElementById('raditipo').value;
            referencia = document.getElementById('referencia').value;
        } catch (e) {
            tipo_docu = '<?=$ent?>';
        }
        
        if (detectarPhone()==1)
            nuevoAjax('div_cuerpo', 'POST', 'cargar_editor.php', 'esphone=1&codi_texto='+ codi_texto + '&referencia=' + referencia + '&tipo_docu='+tipo_docu+'&accion=<?=$accion?>','cambio_cuerpo_editor();mostrar_botones (true);');
        else
            nuevoAjax('div_cuerpo', 'POST', 'cargar_editor.php', 'esphone=0&codi_texto='+ codi_texto + '&referencia=' + referencia + '&tipo_docu='+tipo_docu+'&accion=<?=$accion?>','cambio_cuerpo_editor();mostrar_botones (true);');
        // Desactivamos los botones para guardar por 5 segundos
//        timerID = setTimeout("mostrar_botones (true)", 5000);
    }

    function cambio_cuerpo_editor() {
        try {
            var texto = document.getElementById('div_cuerpo').innerHTML;
            if (texto.indexOf("<")>0) texto = trim(texto.substring(0,texto.indexOf("<")));
            texto = Base64.decode(texto);
            document.getElementById('raditexto').value = texto;
            var oEditor = CKEDITOR.instances.raditexto;
            oEditor.setData( texto );
            texto = oEditor.getData();
            setTimeout("obtener_texto_ckeditor();", 1000);
            //cuerpo_documento = (trim(oEditor.getData()));
        } catch (e) {}
    }

    function obtener_texto_ckeditor() {
        var oEditor = CKEDITOR.instances.raditexto;
        cuerpo_documento = (trim(oEditor.getData()));
        return cuerpo_documento;
    }

    function cargar_editor() {
        CKEDITOR.replace('raditexto');

    }

    function cancelar()
{
        <? if ($nurad) $var_envio = "window.location='$ruta_raiz/verradicado.php?verrad=$nurad&menu_ver=3&irVerRad=1&tipo_ventana=popup';";
           else $var_envio = "history.back();";
        echo $var_envio;
        ?>
    return;
}
function validarDoc(){
    resp = 1;
    flag_inst = document.getElementById("flag_inst").value;
    ent = document.getElementById("hidd_ent").value;
    
    if (ent==2){
        if (flag_inst==1){
            resp = 0;
            document.getElementById('div_alerta_externo').style.display='none';
        }
        else
            resp = 1;
    }else{
          resp = 1;  
        }
        
      return resp;  
}
function grabar_doc(dato)
{
    
       if (validarDoc()==1){
            if (<?=$ent?>==2) {
                if(validarfecha()!="ok") return;
            }

            document.formulario.opc_grab.value=dato;

            if (trim(document.formulario.documento_us1.value) != '' && trim(document.formulario.documento_us2.value) != '' ) {
                if (trim(document.formulario.asunto.value) != '')
                    document.formulario.submit();
                else
                    alert("El Asunto del documento es obligatorio.");
            } else
                alert("El Remitente y Destinatario son obligatorios.");
            return;
        }else
            document.getElementById("div_alerta_externo").style.display='';
       
}
function pestanas(valor)
{
    if (valor==1) {
        document.getElementById('etiqueta1').style.display = "none";
        document.getElementById('etiqueta1_R').style.display = "";
        document.getElementById('etiqueta2').style.display = "";
        document.getElementById('etiqueta2_R').style.display = "none";
         <?if($ent!= 2){?>
            document.getElementById('etiqueta3').style.display = "";
            document.getElementById('etiqueta3_R').style.display = "none";
        <?}?>

        document.getElementById('cuerpo_documento').style.display = "";
        document.getElementById('cuerpo_anexos').style.display = "none";
        document.getElementById('cuerpo_anexos2').style.display = "none";
        document.getElementById('opciones_impresion').style.display = "none";
        document.getElementById('bandera_cambiarop').value=0;
    }
    if (valor==2) {
        document.getElementById('etiqueta1').style.display = "";
        document.getElementById('etiqueta1_R').style.display = "none";
        document.getElementById('etiqueta2').style.display = "none";
        document.getElementById('etiqueta2_R').style.display = "";
        <?if($ent!= 2){?>
            document.getElementById('etiqueta3').style.display = "";
            document.getElementById('etiqueta3_R').style.display = "none";
        <?}?>
        document.getElementById('cuerpo_documento').style.display = "none";
        document.getElementById('cuerpo_anexos').style.display = "";
        document.getElementById('cuerpo_anexos2').style.display = "";
        document.getElementById('opciones_impresion').style.display = "none";
    }
    if (valor==3) {        
        document.getElementById('etiqueta1').style.display = "";
        document.getElementById('etiqueta1_R').style.display = "none";
        document.getElementById('etiqueta2').style.display = "";
        document.getElementById('etiqueta2_R').style.display = "none";
        document.getElementById('etiqueta3').style.display = "none";
        document.getElementById('etiqueta3_R').style.display = "";
        document.getElementById('cuerpo_documento').style.display = "none";
        document.getElementById('cuerpo_anexos').style.display = "none";
        document.getElementById('cuerpo_anexos2').style.display = "none";
        document.getElementById('opciones_impresion').style.display = ""; 
        document.getElementById('bandera_cambiarop').value=1;
        cargarEstiloImpresion();        
    }
    
}

    function Start(URL) {
        var x = (screen.width - 1100) / 2;
        var y = (screen.height - 740) / 2;
        windowprops = "top=0,left=0,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=1100,height=740";
        URL = URL + '?documento_us1=' + document.formulario.documento_us1.value + '&documento_us2=' + document.formulario.documento_us2.value + '&concopiaa=' + document.formulario.concopiaa.value + "&ent=<?=$ent?>"
              + "&radi_lista_dest=" + document.formulario.radi_lista_dest.value
              + "&radi_lista_nombre=" + document.formulario.radi_lista_nombre.value
              + "&lista_modificada="+document.formulario.hidden_lista_modificada.value;
        preview = window.open(URL , "preview", windowprops);
        preview.moveTo(x, y);
        preview.focus();
    }

// Para mostrar algunas opciones de impresion
function mostrarOpcImp(){
    document.getElementById('hidden_radi_actual').value = document.formulario.raditipo.value;
    try {

        if(document.formulario.raditipo.value == 1) // Para oficios
        {
                document.getElementById('opcCargo').style.display = "";
                document.getElementById('opcInstitucion').style.display = "";
                document.getElementById('opcListas').style.display = "";
            document.getElementById('div_fecha_documento').style.display = "none";
        }
        else
        {

            document.getElementById('opcCargo').style.display = "none";
            document.getElementById('opcInstitucion').style.display = "none";
            if (document.formulario.raditipo.value == 3)
               document.getElementById('opcListas').style.display = "none";
            else
                if (document.formulario.raditipo.value == 6)
                document.getElementById('opcListas').style.display = "none";
                else
                document.getElementById('opcListas').style.display = "";
                document.getElementById('div_fecha_documento').style.display = "";
            //document.getElementById('radi_tipo_impresion').value = 1;
        }
    } catch (e) {}
}

function mostrar_div_sumillas(accion) {
    if (accion == 1)
        document.getElementById('div_sumillas').style.display = '';
    else
        document.getElementById('div_sumillas').style.display = 'none';
}

function seleccionar_anexos_copiar_anexos() {
    try {
        for(i=1 ; i < document.formulario.elements.length ; i++) {
            if (document.formulario.elements[i].value == 'chk_copiar_anexos') {
                document.formulario.elements[i].checked=1;
            }
        }
    } catch (e) {}
}

/*********************** INICIO FUNCIONES PARA ESTILO DE IMPRESION  *************************/

    function ltrim(s) {
       return s.replace(/^\s+/, "");
    }

    function cargarEstiloImpresion(){
        
        
        var parametros = "";
        var listDestinatarios = new Array();
        var numDestinatarios = 0;
        // Si el documento es tipo oficio se muestra las opciones de impresión
        if(document.getElementById("raditipo").value == 5) {
            document.getElementById("div_estilo_impresion").style.display="";
            parametros = "radi_nume=<?=$nurad?>";
            nuevoAjax('div_estilo_impresion', 'POST', 'estilo_impresion_acuerdos.php', parametros);
            return;
        }
        if(document.getElementById("raditipo").value == 1 || document.getElementById("raditipo").value == 6)
        {
            document.getElementById("div_estilo_impresion").style.display="";
            parametros = "verRadicado=" + "<?=$nurad?>" + "&radiTipoDoc=" + document.getElementById("raditipo").value;
            parametros += "&codiDest=" + document.getElementById("documento_us1").value;
            parametros += "&cmb_tipo_imp=" + document.getElementById("rad_tipo_imp").value;
            
            listDestinatarios = document.getElementById("documento_us1").value.split("--");
            numDestinatarios = listDestinatarios.length;
            //para cambiar el tipo de impresion que quedo anterior
            if (numDestinatarios==1){
             if (document.getElementById("hidden_actualiza_opciones").value==1)
                     parametros+="&blanquear_cambiar_titulo=1";                 
            }
            // Si es un destinatario se muestra Información del Destinatario para modificar información
            opc_tipo_imp=document.getElementById('rad_tipo_imp').value;
            if (opc_tipo_imp==3)
                parametros += "&verDest=none";
            else{
                if(numDestinatarios==1)
                parametros += "&verDest=";
            else // Si es mas de un destinatario no se muestra la Información del Destinario unicamente se muestra Saludo y Despedida del firmante
                parametros += "&verDest=none";
            }
            
            if (document.getElementById("documento_us1").value!='')
             parametros +="&editarCiudadano=1";
            if (document.getElementById("radi_lista_nombre").value!='')
                parametros +="&opcion_lista=1";
            document.getElementById("div_estilo_impresion").style.display="";
            parametros += "&accion=" + "<?=$accion?>";
            nuevoAjax('div_estilo_impresion', 'POST', 'estilo_impresion.php', parametros);
            
        }
        else // Si el documento no es un oficio no se muestra las opciones de impresión
            document.getElementById("div_estilo_impresion").style.display="none";
    }

    function muestraBtnRemitente(opc){
        if (opc==1){//pie pagina se deshabilita la extension de la institucion
            document.getElementById('ImgInst').style.visibility='hidden';
            document.getElementById("txt_ext_institucion").style.visibility='hidden';
            document.getElementById('ImgFirm').style.visibility='hidden';
            document.getElementById("txt_opcFirmantes").style.visibility='hidden';
            //document.getElementById('txt_cab').value="PIE PÁGINA";
        }else{//cabecera
            document.getElementById('ImgInst').style.visibility='visible';
            document.getElementById("txt_ext_institucion").style.visibility='visible';
            document.getElementById('ImgFirm').style.visibility='visible';
            document.getElementById("txt_opcFirmantes").style.visibility='visible';

            //document.getElementById('txt_cab').value="CABECERA";
        }
        return true;
    }

    function habilitaObj(opcT){
       
        if (opcT=="tit"){//Título
            document.getElementById('txt_opcTitulo').readOnly = false;
            document.getElementById('txt_opcTitulo').className = 'caja_texto';
            if(ltrim(document.getElementById('txt_opcTitulo').value) == 'Sin título')
                document.getElementById('txt_opcTitulo').value = '';
            document.getElementById('txt_opcTitulo').focus();
        }else  if (opcT=="carg"){//cargo
            document.getElementById('txt_opcCargo').readOnly = false;
            document.getElementById('txt_opcCargo').className = 'caja_texto';
            if(ltrim(document.getElementById('txt_opcCargo').value) == 'Sin Cargo Cabecera')
                document.getElementById('txt_opcCargo').value = '';
            document.getElementById('txt_opcCargo').focus();
        }else if (opcT=="firm"){// y mas firmantes
            document.getElementById('txt_opcFirmantes').readOnly = false;
            document.getElementById('txt_opcFirmantes').className = 'caja_texto';
            if(ltrim(document.getElementById('txt_opcFirmantes').value) == '')
                document.getElementById('txt_opcFirmantes').value = '';
            document.getElementById('txt_opcFirmantes').focus();
        }else if (opcT=="ins"){
            document.getElementById('txt_ext_institucion').readOnly = false;
            document.getElementById('txt_ext_institucion').className = 'caja_texto';
            if(ltrim(document.getElementById('txt_ext_institucion').value) == '')
                document.getElementById('txt_ext_institucion').value = '';
            document.getElementById('txt_ext_institucion').focus();
        }else if (opcT=="sal"){
            document.getElementById('txt_opcSaludo').readOnly = false;
            document.getElementById('txt_opcSaludo').className = 'caja_texto';
            document.getElementById('txt_opcSaludo').focus();
        }else if (opcT=="des"){
            document.getElementById('txt_opcDespedida').readOnly = false;
            document.getElementById('txt_opcDespedida').className = 'caja_texto';
            document.getElementById('txt_opcDespedida').focus();
        }else if (opcT=="fra"){
            document.getElementById('txt_opcFrasedespedida').readOnly = false;
            document.getElementById('txt_opcFrasedespedida').className = 'caja_texto';
            document.getElementById('txt_opcFrasedespedida').focus();
        }else if (opcT=="dir"){
            if(document.getElementById('txt_direccion').value==""){
                if(document.getElementById('tipouser').value==2)
                    alert("El ciudadano no tiene dirección ingresada");
                else
                    alert("El funcionario no tiene ciudad ingresada");
            }else{
                document.getElementById('txt_opcSaludo').style.visibility = 'visible';
                document.getElementById('txt_opcSaludo').value=document.getElementById('txt_direccion').value;
            }
        }else if (opcT=="ciu_ori"){
            document.getElementById('txt_opc_ciudad_dado_en').readOnly = false;
            document.getElementById('txt_opc_ciudad_dado_en').className = 'caja_texto';
            document.getElementById('txt_opc_ciudad_dado_en').focus();
        }
        return true;
    }

    function deshabilitaObj(opcT){
        if (opcT=="tit"){
            document.getElementById('txt_opcTitulo').readOnly = true;
            document.getElementById('txt_opcTitulo').className = 'text_transparente';
            if(ltrim(document.getElementById('txt_opcTitulo').value) == '')
                document.getElementById('txt_opcTitulo').value = document.getElementById('txt_titulo').value;
            document.getElementById('txt_opcTitulo').focus();
        }if (opcT=="carg"){
            document.getElementById('txt_opcCargo').readOnly = true;
            document.getElementById('txt_opcCargo').className = 'text_transparente';
            if(ltrim(document.getElementById('txt_opcCargo').value) == '')
                document.getElementById('txt_opcCargo').value = document.getElementById('txt_Cargo').value;
            document.getElementById('txt_opcCargo').focus();
        }else if(opcT=="firm"){
            document.getElementById('txt_opcFirmantes').readOnly = true;
            document.getElementById('txt_opcFirmantes').className = 'text_transparente';
            document.getElementById('txt_opcFirmantes').focus();
        }else if(opcT=="ins"){
            document.getElementById('txt_ext_institucion').readOnly = true;
            document.getElementById('txt_ext_institucion').className = 'text_transparente';
            document.getElementById('txt_ext_institucion').focus();
        }else if(opcT=="sal"){
            document.getElementById('txt_opcSaludo').readOnly = true;
            document.getElementById('txt_opcSaludo').className = 'text_transparente';
            if(ltrim(document.getElementById('txt_opcSaludo').value) == '')
                document.getElementById('txt_opcSaludo').value = document.getElementById('txt_saludo').value;
            document.getElementById('txt_opcSaludo').focus();            
            txtSaludo=document.getElementById('txt_opcSaludo').value;
            numradop = document.getElementById("num_rad").value;
//            if(numradop!=''){
//            nuevoAjax('div_modificar_op', 'GET', 'opciones_impresion_mod.php', 'txtSaludo='+txtSaludo+'&num_radicado=' + numradop);
//            }
             
        }else if (opcT=="des"){
            document.getElementById('txt_opcDespedida').readOnly = true;
            document.getElementById('txt_opcDespedida').className = 'text_transparente';
            if(ltrim(document.getElementById('txt_opcDespedida').value) == '')
                document.getElementById('txt_opcDespedida').value = document.getElementById('txt_despedida').value;
            document.getElementById('txt_opcDespedida').focus();
        }else if (opcT=="fra"){
            document.getElementById('txt_opcFrasedespedida').readOnly = true;
            document.getElementById('txt_opcFrasedespedida').className = 'text_transparente';
            if(ltrim(document.getElementById('txt_opcFrasedespedida').value) == '')
                document.getElementById('txt_opcFrasedespedida').value = document.getElementById('txt_frasedespedida').value;
            document.getElementById('txt_opcFrasedespedida').focus();
        }else if (opcT=="ciu_ori"){
            document.getElementById('txt_opc_ciudad_dado_en').readOnly = true;
            document.getElementById('txt_opc_ciudad_dado_en').className = 'text_transparente';
            if(ltrim(document.getElementById('txt_opc_ciudad_dado_en').value) == '')
                document.getElementById('txt_opc_ciudad_dado_en').value = document.getElementById('txt_ciudad_dado_en').value;
            document.getElementById('txt_opc_ciudad_dado_en').focus();
        }
    }

    function editarCiudadano(){        
        var url = "";
	var x = (screen.width - 1100) / 2;
	var y = (screen.height - 575) / 2;
        cod_impresion = document.getElementById("radi_tipo_impresion").value;
        url = "<?=$ruta_raiz?>" + "/Administracion/ciudadanos/adm_usuario_ext.php?cerrar=Si&accion=2&ciu_codigo=" + document.getElementById("usuaCodi").value+"&cod_impresion="+cod_impresion;
	ventana=window.open(url,"Editar_Ciudadano","toolbar=no,directories=no,menubar=no,status=no,scrollbars=yes, width=1100, height=575");
	ventana.moveTo(x, y);
        ventana.focus();
    }

/**/
//Por David Gamboa
function ventanaNueva(url){
	window.open(url,'nuevaVentana','width=600, height=600,top=200');
}

    function fjs_radicado_descargar_archivo(radicado, anex_codigo, arch_tipo, tipo_descarga) {
        path_descarga = '../anexos/anexos_descargar_archivo.php?radi_nume='+radicado+'&anex_codigo=' + anex_codigo + '&arch_tipo=' + arch_tipo + '&tipo_descarga=' + tipo_descarga;
        if (tipo_descarga=='embeded')
            document.getElementById('ifr_mostrar_archivo').src=path_descarga;
        else
            document.getElementById('ifr_descargar_archivo').src=path_descarga;
        return;
    }


function buscarDatosDestinatario(tipo_busqueda)
{     
  if (document.getElementById("radi_lista_dest").value ==''){
       tipo_impresion=document.getElementById('radi_tipo_impresion').value;       
       
       if (tipo_impresion==6 || tipo_impresion==2 || tipo_impresion==1 || tipo_impresion==4 || tipo_impresion==5){
         destinatario = document.getElementById('documento_us1').value;
         nuevoAjax('div_ver_datos', 'GET', 'ver_datos_usuario.php', 'destinatario='+destinatario+'&tipo_busqueda='+tipo_busqueda);
       }
  }
}
function verOpcionNota(opcion){
    
    try {
    //segun opcion
        switch (opcion) {
            case 3: //verbal
                
                document.getElementById("div_despedida_firmante").style.display="none";
                document.getElementById("radio_cabecera").checked = true;
                document.getElementById("radio_cabecera").value = 1;
                document.getElementById("txt_pie_cabecera").value = 'Pie de Página';
                
                document.getElementById("div_fecha_documento").style.display='';
                
                
                if (document.getElementById("tituloDest").value=='Sin título')
                    document.getElementById("txt_opcTitulo").value = 'A la Honorable,';
                else if(document.getElementById("txt_opcTitulo").value!=document.getElementById("tituloDest").value)
                 document.getElementById("txt_opcTitulo").value = 'A la Honorable,';
                else if(document.getElementById("txt_titulo").value==document.getElementById("txt_opcTitulo").value)
                    document.getElementById("txt_opcTitulo").value = 'A la Honorable,';
                
                muestraBtnRemitente(0);
                break;

            case 1: //diplomatica
                document.getElementById("div_despedida_firmante").style.display='';
                document.getElementById("radio_cabecera").value = 0;
                document.getElementById("radio_cabecera").checked = true;
                document.getElementById("txt_pie_cabecera").value = 'Cabecera';
                document.getElementById("div_fecha_documento").style.display="none";
                //document.getElementById("ub_fecha").style.display="none";
                
                document.getElementById("txt_opcTitulo").value = document.getElementById("tituloDest").value;
                muestraBtnRemitente(0);
                break;

            case 2: //reverso
                document.getElementById("div_despedida_firmante").style.display='';
                document.getElementById("radio_cabecera").checked = true;
                document.getElementById("radio_cabecera").value = 1;
                document.getElementById("txt_pie_cabecera").value = 'Pie de Página';
                document.getElementById("div_fecha_documento").style.display="none";
                document.getElementById("txt_opcTitulo").value = document.getElementById("tituloDest").value;
                //document.getElementById("ub_fecha").style.display="none";
                muestraBtnRemitente(0);
                break;            
            default:
                
                break;
        }
    } catch (e) {}
}

function limpiardiv(){
    document. getElementById('div_ver_datos'). innerHTML='';
    
}

function copia(){
    document.getElementById("hidden_titulo_anterior").value=document.getElementById("txt_opcTitulo").value;
}
//tipo de transaccion: id donde hace clic
//obj: lo que ingresa el usuario en caja de texto, check o radio
//valor: el valor anterior
function histop(tipo_transaccion,obj,valor){     
    existe=0;       
    if (valor==obj.value)
    existe=1;

    if (existe==0){
        document.getElementById('hidden_acciones_datos').value+=tipo_transaccion+","+obj.value+","+valor+":";        
    }
}    
 function AsociarDocumento(nurad,refepadre,modificar) {
       if (trim(document.formulario.documento_us1.value) != '' && trim(document.formulario.documento_us2.value) != '' ) {
            if (trim(document.formulario.asunto.value) != ''){
             ventana = window.open("../asociar_documentos/asociar_documento.php?radi_nume="+nurad+"&radi_refe="+refepadre+"&cerrar=SI&modificar="+modificar, "asociar_documentos", "height=600,width=900,scrollbars=yes");
        ventana.focus();   
            }                
            else
                alert("El Asunto del documento es obligatorio.");
        } else
            alert("El Remitente y Destinatario son obligatorios.");
        
       
    }    
function detectarPhone(){
    var navegador = navigator.userAgent.toLowerCase();
    if ( navigator.userAgent.match(/iPad/i) != null)//detectar ipad
      return 2;
    else{//detectar phone        
        if( navegador.search(/iphone|ipod|blackberry|android/) > -1 )
           return 1;    
        else 
            return 0;
    }
}
function limita(elEvento, txtElemento, txtResultado, maxCaracteres) {
    var elemento = document.getElementById(txtElemento);   
    formEnvio_contador_caracteres_TimerId = setTimeout("formEnvio_contador_caracteres('"+txtElemento+"','"+ txtResultado+"',"+ maxCaracteres+")", 50);
    // Obtener la tecla pulsada
    var evento = elEvento || window.event;
    var codigoCaracter = evento.charCode || evento.keyCode;
    // Permitir utilizar las teclas con flecha horizontal
    if(codigoCaracter >= 37 && codigoCaracter <= 40) return true;
    // Permitir borrar con la tecla Backspace y con la tecla Supr.
    if(codigoCaracter == 8 || codigoCaracter == 46) return true;

    if(elemento.value.length >= maxCaracteres ) return false;

    document.getElementById(txtResultado).innerHTML = elemento.value.length.toString() + ' de ' + maxCaracteres.toString();
    return true;
}
function formEnvio_contador_caracteres(txtElemento, txtResultado, maxCaracteres) {
    var elemento = document.getElementById(txtElemento);    
    elemento.value = elemento.value.substr(0, maxCaracteres);
    document.getElementById(txtResultado).innerHTML = elemento.value.length.toString() + ' de ' + maxCaracteres.toString();
    return;
}

function mostrar_div_usuarios() {
    ocultar = document.getElementById('txt_ocultar').value;
   if (ocultar==0)
       ocultar=1;
   else
       ocultar=0;
    if (document.getElementById('txt_ocultar').value == 0){        
        lista_dest = document.getElementById('radi_lista_dest').value;
        usuarios_doc = document.getElementById('documento_us1').value;
        
        document.getElementById('div_lista_modificada').style.display = '';
        datos="radi_lista_dest="+lista_dest+"&usuarios_radi="+usuarios_doc;
        nuevoAjax('div_lista_modificada', 'GET', 'usuarios_lista_modificada.php', datos);
    }
    else{
        
        document.getElementById('div_lista_modificada').style.display = 'none';
    }
    document.getElementById('txt_ocultar').value = ocultar;
}

</script>


<style type="text/css">

/*Tool Tip*/
a.Ntooltip {
position: relative; /* es la posición normal */
text-decoration: none !important; /* forzar sin subrayado */
color:#0080C0 !important; /* forzar color del texto */
font-weight:bold !important; /* forzar negritas */
}

a.Ntooltip:hover {
z-index:999; /* va a estar por encima de todo */
background-color:#000000; /* DEBE haber un color de fondo */
}

a.Ntooltip span {
display: none; /* el elemento va a estar oculto */
}

a.Ntooltip:hover span {
display: block; /* se fuerza a mostrar el bloque */
position: absolute; /* se fuerza a que se ubique en un lugar de la pantalla */
top:2em; left:2em; /* donde va a estar */
width:100px; /* el ancho por defecto que va a tener */
padding:5px; /* la separación entre el contenido y los bordes */
background-color: #FBFBEF; /* el color de fondo por defecto */
color: #000000; /* el color de los textos por defecto */
}
/*Tool Tip*/
/**/
</style>
</head>

<script type="text/javascript" src="<?=$ruta_raiz?>/js/spiffyCal/spiffyCal_v2_1.js"></script>

<?php
////////////////////    CARGO LOS DATOS SI NO ES NUEVO  //////////////////////////////
    $codi_texto = "0";
    $chk_plantilla = "1";
    $cmb_texto = "100";
    $txt_usua_redirigido = 0;
    if($ent==1)
    {        
        $usua=ObtenerDatosUsuario($_SESSION["usua_codi"],$db,"U");
        $documento_us2 = "-".$_SESSION["usua_codi"]."-";
//        if($nurad){
//            $usua=ObtenerDatosUsuario($krd,$db,"L");
//            $documento_us2 = "-".$usua["usua_codi"]."-";
//        }
//        else{
//            $usua=ObtenerDatosUsuario($_SESSION["usua_codi"],$db,"U");
//            $documento_us2 = "-".$usua["usua_codi"]."-";
//        }
        
    }
    
    if(trim($accion)=="Editar" || trim($accion)=="Responder" || trim($accion)=="ResponderTodos")//editar responder
    {
        $radicado=ObtenerDatosRadicado($nurad,$db);
        $asunto         = $radicado["radi_asunto"];
        //Para copiar automaticamente las notas que han sido incluidas en el padre        
        $notas          = $radicado["radi_resumen"];
        $codi_texto     = $radicado["radi_codi_texto"];
        $concopiaa      = str_replace("'","",$radicado["cca"]);        
        $depe_actu      = $radicado["depe_actu"];
        $usua_actu      = $radicado["usua_actu"];
        $raditipo       = $radicado["radi_tipo"];        
        $chk_plantilla  = $radicado["usar_plantilla"];
        $cmb_texto      = $radicado["ajust_texto"];
        $radi_tipo_impresion = $radicado["radi_tipo_impresion"];
        $cod_codi = 0+$radicado["cod_codi"];
        $cat_codi = 0+$radicado["cat_codi"];
        $refe_padre = $radicado["radi_padre"];
        //----RADI NUME TEMP
        $radi_nume_temp = $radicado["radi_nume_temp"];
        //----RADI NUME TEMP
        $radi_asoc = $radicado["radi_nume_asoc"];
        //echo $_GET['radi_lista_nombre'];
        if ($_GET['radi_lista_nombre']=='')
        $radi_lista_dest     = $radicado["radi_lista_dest"];
        else
            $radi_lista_nombre     = '';
        //Verifico nùmero de destinatarios
        $destinatarios=$radicado["usua_dest"];
        
        $cadena1= $destinatarios;
        $cadena2= "--";
        if (strrpos($cadena1, $cadena2))
            $VariosDest="1";
        else
            $VariosDest="0";


        if ($accion=="Responder"){      //usua1=destinatario    usua2=remitente
            $documento_us1 = $radicado["usua_rem"];            
            //$documento_us2  = $radicado["usua_dest"];
            if ($raditipo==2) $raditipo=1; else $radi_tipo=3;
            $nurad_referencia = $nurad;
            $referencia= $textrad;
            $radi_lista_dest   = "";
            $radi_lista_nombre = "";
            
        }
        
        /*Snap, David Gamboa,mantis: 2301,2014-02-11*/
        elseif($accion=="ResponderTodos"){
            include_once "$ruta_raiz/seguridad/obtener_nivel_seguridad.php";
            $nivel_seguridad_documento = obtener_nivel_seguridad_documento($db, $nurad);
            //$documento_us1 = $radicado["usua_rem"];
            
            //Responder a Todos.
            
            $radicadoRT=ObtenerDatosRadicado($radi_nume_temp,$db);
            
            $concopiaa=str_replace("-".$_SESSION["usua_codi"]."-", "", $radicadoRT["cca"]);//copia
            $destinatarios = str_replace("-".$_SESSION["usua_codi"]."-", "", $radicadoRT["usua_dest"]);
            $documento_us1 = $radicado["usua_rem"].$destinatarios;
            
            //$documento_us1 = $radicado["usua_rem"];
            //$documento_us2  = $radicado["usua_dest"];
            
            if ($raditipo==2) $raditipo=1; else $radi_tipo=3;
            $nurad_referencia = $nurad;
            $referencia= $textrad;
            $radi_lista_dest   = "";
            $radi_lista_nombre = "";
        }
        else{
            include_once "$ruta_raiz/seguridad/obtener_nivel_seguridad.php";
            $nivel_seguridad_documento = obtener_nivel_seguridad_documento($db, $nurad);
            $referencia 	= $radicado["radi_referencia"];
            $radi_nume_padre = $radicado["radi_padre"];
            $fecha_doc	= $radicado["radi_fecha"];
            $fecha_doc	= substr($fecha_doc,8 ,2)."-".substr($fecha_doc,5 ,2)."-".substr($fecha_doc,0 ,4);
            $desc_anex	= $radicado["radi_desc_anexos"];
            $estado		= $radicado["estado"];
            $documento_us1 	= $radicado["usua_dest"];
            $documento_us2 	= $radicado["usua_rem"];
            $chk_ocultar_recorrido = $radicado["ocultar_recorrido"];
            $txt_usua_redirigido = $radicado["usua_redirigido"];
            if ($radicado["estado"] != 1 or $_SESSION["usua_codi"] != $radicado["usua_actu"]) {
                die (html_error("Usted no tiene los permisos suficientes para modificar este documento."));
            }
    	}
        
        if(trim($radi_lista_dest)!="")//si tiene listas
        {
            //fecha de actualizacion del documento
            
            
            $ultimaActualizacion = ObtenerUltimaFecha($nurad, '11', $db);
            if ($ultimaActualizacion=='')
            $ultimaActualizacion = ObtenerUltimaFecha($nurad, '2', $db);
            $codList = str_replace('--', ',', $radi_lista_dest);
            $codList = str_replace('-', '', $codList);
            $cuentaListas = count(explode(',',$codList));
            
            $msj_lista = "La(s) lista(s) ";
            if($cuentaListas>1)//si es mas de una lista
            {
            foreach (explode(',',$codList) as $lista) {//for each listas                                 
                    $datosLista = ObtenerDatosLista($lista,$db);                    
                    $radi_lista_nombre .= $datosLista['nombre'] . ', ';                                    
                    if($ultimaActualizacion<$datosLista['fecha']){
                        $msj_lista .= $datosLista['nombre'] . ', ';
                        $listado_listas = $listado_listas."-".$lista."-";
                    }
                }//for ecach listas
                $msj_lista .= " ha(n) sido modificada(s),";
            }//si es mas de una lista
            else
            {//si es una lista
                $codList = str_replace('-', '', $codList);
                $datosLista = ObtenerDatosLista($codList,$db);                
                $radi_lista_nombre .= $datosLista['nombre'];
                //echo '<script>alert("'.$ultimaActualizacion."-".$datosLista['fecha'].'")</script>';                
                if (trim($ultimaActualizacion)!=''){
                    if($ultimaActualizacion<$datosLista['fecha']){
                        $msj_lista .= $datosLista['nombre'] . ', ';
                        $listado_listas = $listado_listas."-".$codList."-";
                    }
                }
                $msj_lista .= " ha(n) sido modificada(s),";
            }//si es una lista
            
            if($msj_lista != 'La(s) lista(s)  ha(n) sido modificada(s),' and $radicado["estado"] == 1){
                echo '<script>alert("'.$msj_lista.' por favor, actualizar los destinatarios.");</script>';
                $lista_modificada = 1;
            }else
                $lista_modificada = 0;
        }//si tiene listas
    }//editar responder
    
    if(trim($accion)=="Copiar")
    {
        $radicado=ObtenerDatosRadicado($nurad,$db);
        $asunto         = $radicado["radi_asunto"];       
        $codi_texto     = $radicado["radi_codi_texto"];
        $radi_padre_copia = $radi_padre;
        $radi_padre = ""; //Se vacía esta variable para que no asocie documentos al copiar
        $raditipo       = $radicado["radi_tipo"];
       
        unset($optipoNotaBusq); 
        
    }

    $estado=$ent;
    if(!$fecha_doc) $fecha_doc = date("d-m-Y");
    
    
    $compResponder = $_GET['compResponder'];
if ($compResponder==1){//VIENE DE LA BANDEJA COMPARTIDA Y DEBE REALIZAR LA REASIGNACION    
    $observaJefe ='Documento tomado por '.$_SESSION["usua_codi"].' de la Bandeja de Documentos Recibidos de '.$_SESSION['usua_codi_jefe'];    
}


////////////////////////////	CABECERA	////////////////////////
?>
<script type="text/javascript">
    var dateAvailable1 = new ctlSpiffyCalendarBox("dateAvailable1", "formulario", "fecha_doc","btnDate1","<?=$fecha_doc?>",scBTNMODE_CUSTOMBLUE);
</script>

<body bgcolor="#FFFFFF" onLoad="pestanas(1); cambiar(); mostrarOpcImp(); cargar_editor(); seleccionar_anexos_copiar_anexos()">
    <div id="div_confirm_bloquear_pantalla" style="width: 100%; height: 100%; z-index: 1000; position: fixed; top: 0; left: 0; opacity:0.3; filter:alpha(opacity=30); background-color: black; display: none;"></div>
    <div id="div_confirm_pantalla_pequena" style="width: 40%; z-index: 1001; position: fixed; top: 20%; left: 30%; background-color: white; border: #333333 2px solid; text-align: center; vertical-align: middle; display: none;">
        <br><br>
        <span id="spn_confirm_mensaje"></span>
        <br><br><br>
        <input type="button" id="btn_confirm_aceptar" value="Aceptar">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="button" id="btn_confirm_cancelar" value="Cancelar">
        <br><br><br>
    </div>
    <input type="hidden" id="rad_tipo_imp" value="<?=$radi_tipo_impresion?>"/>
   <div id="spiffycalendar" class="text"></div>
<?php
$var_envio="ent=$ent&nurad=$nurad&textrad=$textrad&accion=$accion&carpeta=$carpeta";
?>
<form action="funciones_NEW.php?<?=$var_envio?>" ENCTYPE="multipart/form-data" method="post" name="formulario" id="formulario"> <!--onChange="document.formulario.fl_modificar1.value=1;"-->
    <input type="hidden" name="hidden_tipo_anterior" id="hidden_tipo_anterior" value="<?=$raditipo?>"/>
    <input type="hidden" name="hidden_radi_actual" id="hidden_radi_actual" value=""/>
    <input type="hidden" name="hidden_actualiza_opciones" id="hidden_actualiza_opciones" value=""/>
    <input type="hidden" name="hidden_titulo_anterior" id="hidden_titulo_anterior" value=""/>
    <input type="hidden" name="bandera_cambiarop" id="bandera_cambiarop" value="1"/>
    <input type="hidden" name="txt_refeResponder" id="txt_refeResponder" value="<?=$txt_refeResponder?>"/>
    <input type="hidden" name="hidden_lista_modificada" id="hidden_lista_modificada" value="<?=$lista_modificada?>"/>
    <input type="hidden" name="txt_plugins_navegador" id="txt_plugins_navegador" value=""/>

<!--table width="99%"  border="0" align="center" cellpadding="4" cellspacing="5" class="borde_tab">
<tr>
        <td width="94%" class="titulos2" align="center">
            <b>MODULO DE REGISTRO DE <?=trim(strtoupper($_SESSION["descRadicado"]))?>S</b>
    <?php
        if($nurad)
        {
                if ($accion=="Responder") {
                    echo "<br>Respuesta al ".$_SESSION["descRadicado"]." No " .$textrad;
                    $ent=1;
		} else {
                    echo "<br>".$_SESSION["descRadicado"]." No " .$textrad;
	}
	}
        ?>
        </td>
</tr>
</table-->


<?php
//pestañas
	$imgTp1="infoGeneral";
	$imgTp2="documentos";
        $imgTp3="OpcImpresion";
?>
<table width="99%" align="center" border="0" cellspacing="4" cellpadding="0">
<tr>
      <td width="50%" align="left">
          <input type="button" name="btn_usuarios" id="btn_usuarios" value="Buscar De/Para" class="botones_largo" title="Buscar por nombre o cédula, al Remitente, Destinatarios del Documento, y/o Con copia a"
                            onClick="Start('buscar_usuario_nuevo.php');">&nbsp;&nbsp;
        <?php if ($ent != 2) { ?>
            <input type='button' onClick='grabar_doc(1)' name='Submit1' value='Vista Previa' class='botones' title="Graba el documento y genera una vista previa del mismo">&nbsp;&nbsp;
            
        <? } ?>
        <input type='button' onClick='grabar_doc(2)' name='Submit2' value='Aceptar' class="botones" title="Graba el documento y pasa a la página de consulta del documento">&nbsp;&nbsp;
        <input type='button' onClick='cancelar()' name='Submit3' value='Cancelar' class="botones" title="Sale de la creación de documentos sin grabar">

    </td>
</tr>

    <tr valign="bottom" class="listado2">
        <td  width="50%" height="10"  class="listado2">
        <a href="javascript:;"  onClick="pestanas(1);" class="etextomenu" title="Información general del Documento"><img src="../imagenes/<?=$imgTp1?>.gif" width="110" height="25" border="0" id="etiqueta1" alt="informacion"><img src="../imagenes/<?=$imgTp1?>_R.gif" width="110" height="25" border="0" id="etiqueta1_R" alt="informacion"></a>
        <a href="javascript:;"  onClick="pestanas(2);" class="etextomenu" title="Documentos que se colocan como Anexos al Documento"><img src="../imagenes/<?=$imgTp2?>.gif" width="110" border="0"  id="etiqueta2" alt="anexos"><img src="../imagenes/<?=$imgTp2?>_R.gif" width="110" border="0"  id="etiqueta2_R" alt="anexos"></a>
        <?php if($ent!= 2){//|| $raditipo==2?>      
            <a href="javascript:;"  onClick="cambiar(); pestanas(3); buscarDatosDestinatario(<?=$radi_tipo_impresion?>); verOpcionNota(<?=$opc_nota_inicial?>);" class="etextomenu" title="Opciones de Impresión Oficios"><img src="../imagenes/<?=$imgTp3?>.gif" width="110" border="0"  id="etiqueta3" alt="opc. impresion"><img src="../imagenes/<?=$imgTp3?>_R.gif" width="110" border="0"  id="etiqueta3_R" alt="opc. impresion"></a>
        <?php }?>
        </td>
        <td width="50%" class="listado2_ver" align="right">
            <font align="right" size="2" color="black"><?php 
            if (substr($textrad,-4)=='TEMP')
            echo "Número de Documento: ".$textrad;
            ?></font>
        </td>

    </tr>
    <tr valign="top">
        <td height="95" colspan="2">
             <input type="hidden" name="opc_grab" value=""/>
        <div id='cuerpo_documento'>           
            <input type="hidden" name="opc_ver" value=""/>
            <input type="hidden" name="fl_modificar1" value="0"/>
            <input type="hidden" name="fl_modificar2" value="1"/>             
            <textarea id="documento_us1" name="documento_us1" style="display: none" cols="1" rows="1"><?=$documento_us1?></textarea>
            <textarea id="documento_us2" name="documento_us2" style="display: none" cols="1" rows="1"><?=$documento_us2?></textarea>
            <textarea id="concopiaa" name="concopiaa" style="display: none" cols="1" rows="1"><?=$concopiaa?></textarea>
            <input type="hidden" name="radi_padre" value="<?=$radi_padre?>">
            <input type="hidden" name="radi_padre_copia" value="<?=$radi_padre_copia?>">            
            <input type="hidden" name="radi_lista_nombre" id="radi_lista_nombre" value="<?=$radi_lista_nombre?>">
            <input type="hidden" name="hidd_ent" id="hidd_ent" value="<?=$ent?>">

            <!-- Cuando se desea un tipo de impresion diferente en caso de que los destinatarios pertenescan a una lista. -->
            <input type="hidden" id="radi_lista_dest" name="radi_lista_dest" value="<?=$radi_lista_dest?>">
            
            
            <input type="hidden" name="token" value="<?php echo $token; ?>" />

        <table width=100% border="0" class="borde_tab" align="center" id="cuerpo">
          <input type="hidden" name="flag_inst" id="flag_inst" value="<?=$flag_inst?>"/>         
          <div id="div_alerta_externo" style="display: none"><center><font color="red">No se puede guardar el Documento, existen Usuarios que no pertenecen a la Institución</font></center></div>
          <input type="hidden" name="flag_inst_m" id="flag_inst_m" value="<?=$flag_inst_m?>"/>
        <?php
        /////////////////////////////////        USUARIOS        //////////////////////////////
        $ifr_usr_env="lista_concopiaa.php?documento_us1=$documento_us1&documento_us2=$documento_us2&concopiaa=$concopiaa&radi_lista_nombre=$radi_lista_nombre&ent=$ent&radi_lista_dest=$radi_lista_dest";        
        
        ?>
          <tr>
        <td colspan="10" align="right">
        <?php
             
           if ($lista_modificada==1){//si es 1 ?
               //$listado_listas
               ?>
            <input type="hidden" name="txt_ocultar" id="txt_ocultar" value="0"/>
            
            <table width="50%" align="right">                
                <tr>                    
                    <td class="listado2_ver" style="border: thin solid #E3E8EC; width: 100%;">
                        
                        La(s) lista(s) de este documento fueron modificadas, por favor verificar los destinatarios.
                        </a>
                                      
                    </div>
                </td>
                </tr>                
            </table>
                   
        <?php   }//si es uno
              
        ?>
        </td>
    </tr>
        <tr>
            <td colspan="5">              
                <iframe border="0" class="borde_tab" align="center" height="65" width="100%" name="ifr_usr" id="ifr_usr" src="<?=$ifr_usr_env?>">
                    Su navegador no soporta iframes, por favor actualicelo.</iframe>                
                <br />
            </td>
        </tr>
<?php
        if ($_SESSION["tipo_usuario"]==2) { // Si es un ciudadano el que genera el documento
            echo "<input type='hidden' name='raditipo' id='raditipo' value='7'>";
            echo "<input type='hidden' name='fecha_doc' id='fecha_doc' value='$fecha_doc'>";
            echo "<input type='hidden' name='cat_codi' id='cat_codi' value='0'>";
            echo "<input type='hidden' name='cod_codi' id='cod_codi' value='0'>";
        } else {
?>
        <tr valign="middle">
            <?php  if ($ent==2) { ?>
                    <td width="15%" class="listado1_ver">Fecha Doc: (dd/mm/aaaa)</td>
                    <td width="60%" class="listado1" colspan="4">
                        <input type="hidden" name='raditipo' id='raditipo' value='2'>
                        <script type="text/javascript">
                                dateAvailable1.date = "<?=$fecha_doc?>";
                                dateAvailable1.writeControl();
                                dateAvailable1.dateFormat="dd-MM-yyyy";
                        </script>

            <?php  } else {?>
                    <td width="15%" class="listado1_ver">Tipo de Documento:</td>
                    <td width="55%" class="listado1" colspan="4">
                        <input type="hidden" name='fecha_doc' value='<?=$fecha_doc?>'>
            <?php
                    $query = "Select trad_descr, trad_codigo from tiporad where trad_tipo='S' and trad_estado=1 and trad_inst_codi in (0,".$_SESSION["inst_codi"].") order by 1";
                    $rs=$db->conn->query($query);
                    $tmp = "";
                    if(!$rs->EOF)
                        print $rs->GetMenu2("raditipo", $raditipo, "", false,"","class='select' id='raditipo' onChange='mostrarOpcImp(); cambio_cuerpo(0)'" );
                 }
            ?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <font class="listado1_ver">Categor&iacute;a:</font>&nbsp;&nbsp;
                <?php
                    $queryCat = "Select cat_descr, cat_codi from categoria order by 1";
                    $rsCat=$db->conn->query($queryCat);
                    if(!$rsCat->EOF)
                        print $rsCat->GetMenu2("cat_codi", 0+$cat_codi,  "", false,"","class='select' " );
                ?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <?php
                    $queryCod = "Select cod_descripcion, cod_codi from codificacion where inst_codi in (0,".(0+$_SESSION["inst_codi"]).") order by 1";
                    $rsCod=$db->conn->query($queryCod);
                    if(!$rsCod->EOF)
                    {
                        ?>
                        <font class="listado1_ver">Tipificaci&oacute;n:</font>&nbsp;&nbsp;
                        <?php
                        print $rsCod->GetMenu2("cod_codi", 0+$cod_codi,  "", false,"","class='select' style='width:220px' " );
                    }
                ?>
                </td>
                </tr>
<?php  } //IF CIUDADANO  ?>
                <tr>
                <td width="15%" class="listado1_ver">No. Referencia:</td>
                <td width="55%" class="listado1" colspan="4">
                <?php
                //CONTROL CAJA DE TEXTO REFERENCIA READONLY
                if ($ent==2)
                    if ($referencia=='')
                        $readReferencia ='';
                    else
                        if($raditipo==2)
                            $readReferencia="";
                        else
                            $readReferencia = "readonly";  
                else{    
                    if($raditipo==2)
                        $readReferencia="";
                    else
                        $readReferencia = "readonly";                        
                }
                ?>
                <input alt="Ingrese un Documento asociado" name="referencia" id="referencia" type="text" maxlength="80" size="60" class="tex_area"
                value="<?=$referencia?>" <?=$readReferencia?> onkeyup="validaEspacio(event, this, '<?=$readReferencia?>');" onblur="quitarCaracter(this, ' ');"/>

                <?php
                // MOSTRAR ICONOS DE REFERENCIA
                if ($radicado["radi_padre"]!='') {
                    $nurad_referencia = $radicado["radi_padre"];
                }

                //si existe numero de referencia consultar nivel de seguridad del documento de referencia
                if ($nurad_referencia != '') {
                    $radRefe=ObtenerDatosRadicado($nurad_referencia, $db);
                    //echo "seguridad: ".$nsd;
                    if (file_exists("$ruta_raiz/bodega".$radRefe["radi_path"]) or $radRefe["arch_codi"]>0 or $radRefe["radi_imagen"]!=''){//si existe eel archivo
                        $ventana = "$ruta_raiz/documento_online.php?verrad=$nurad_referencia";
                        $nivel_seguridad_refe = obtener_nivel_seguridad_documento($db, $nurad_referencia);
                        if ($nivel_seguridad_refe >= 2) {
                            echo "&nbsp;<img src='$ruta_raiz/imagenes/zoom_in.png' width='17' height='17' alt='Vista Previa' border='0'
                                        title='Ver en l&iacute;nea Documento de Referencia' onClick='ventanaNueva(\"$ventana\");'>
                                    &nbsp;<img src='$ruta_raiz/imagenes/document_down.jpg' width='17' height='17' alt='Vista Previa' border='0'
                                        title='Descargar Documento de Referencia'
                                        onClick=\"fjs_radicado_descargar_archivo('$nurad_referencia', '".$radRefe["radi_imagen"]."', 0, 'download');\">";
                        }
                    }
                }
                
                        if ($_SESSION["tipo_usuario"]==1){//solo funcionarios
                             //REFERENCIA MOSTRAR ICONO
                             $nuradAso="'".$nurad."'";
                             $refe_padreAso="'".$refe_padre."'";
                             if ($ent==2){//externo
                                 if ($radi_tipo==2)
                                    echo '&nbsp<a href="javascript:;" onClick="AsociarDocumento('.$nuradAso.','.$refe_padreAso.','.$ent.');" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/document_attach.jpg" width="17" height="17" alt="Asociar Documento" border="0"><span>Asociar Documento</span></a>';
                                 elseif($radi_tipo==''){
                                     if($textrad!='')
                                        echo '&nbsp<a href="javascript:;" onClick="AsociarDocumento('.$nuradAso.','.$refe_padreAso.','.$ent.');" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/document_attach.jpg" width="17" height="17" alt="Asociar Documento" border="0"><span>Asociar Documento</span></a>';
                                 }
                            }else{                             
                                    if ($refe_padre==''){//NUEVO
                                        if (substr(trim($textrad),-4)=='TEMP')//SI YA ESTA GUARDADO
                                            echo '&nbsp<a href="javascript:;" onClick="AsociarDocumento('.$nuradAso.','.$refe_padreAso.',2);" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/document_attach.jpg" width="17" height="17" alt="Asociar Documento" border="0"><span>Asociar Documento</span></a>';
                                    }//SI ES DE REFERENCIA
                                    else                                    
                                        echo '&nbsp<a href="javascript:;" onClick="AsociarDocumento('.$nuradAso.','.$refe_padreAso.',3);" class="Ntooltip"><img src="'.$ruta_raiz.'/imagenes/document_attach.jpg" width="17" height="17" alt="Asociar Documento" border="0"><span>Asociar Documento</span></a>';
                                
                            }//FIN ELSE
                        }
                         ?>
                </td>
                </tr>
                <tr>
                    <td class="listado1_ver">Asunto:</td>
                    <td class="listado1" colspan="4">
                        <textarea id="asunto" name="asunto" cols="150" class="tex_area" rows="1" onkeypress="return limita(event,'asunto','spn_numero_caracteres_disponibles',250);" ><?php echo $asunto ?></textarea>
                        <span id="spn_numero_caracteres_disponibles"></span>
                        <!-- Copiar notas cuando en un doc nuevo onKeyUp='this.value=this.value.substring(0,250)'-->
                        <input id="notas" name="notas" type="hidden" value="<?php echo $notas ?>">  
                    </td>
                </tr>

            <?php  if ($ent==2) {                    
                    $mostrarRedirigido = verificarInstitucion($documento_us1,$_SESSION['inst_codi'],$db);
                    $sql = "select usua_apellido || ' ' || usua_nomb || 
                case when usua_subrogado<>1 then ' (Subrogante)' else '' end || ' /' || usua_cargo 
                || ' /' || depe_nomb  as usua_nombre  , 
                usua_codi from usuario where inst_codi = ".$_SESSION["inst_codi"].
                           " and usua_esta=1 and usua_codi 
                               in (select usua_codi from permiso_usuario where id_permiso=6) 
                               order by 1";
                    $rs=$db->conn->query($sql);

                    if($rs and !$rs->EOF)
                    { ?>
                        <tr id="tr_redirigir" <?php if ($mostrarRedirigido==0) echo 'style="display: none"';?>>
                            <td class="listado1_ver">Dirigir documento a:</td>
                            <td class="listado1" colspan="4">
                        <?php print $rs->GetMenu2("txt_usua_redirigido", $txt_usua_redirigido,  "0:No redirigir", false,"","class='select' style='width:220px' " );?>
                        </td></tr>
                    <?php }                    
                    else{ ?>
                        <tr id="tr_redirigir" style="display: none"></tr>
                    <?php }
                    
              } ?>

                <!--<tr>
                    <td class="listado1_ver">Descripci&oacute;n de Anexos:</td>
                    <td class="listado1" colspan="4">
                        <textarea name="desc_anex" cols="100" class="tex_area" rows="1" onKeyUp='this.value=this.value.substring(0,100)'><?php echo $desc_anex ?></textarea>
                    </td>
                </tr>-->
                <?php
                if ($ent==1) {
                ?>
               </tr>
                <?php
                }
                ?>
                <tr>
                    <td valign="top">
                        <!--<p style="font-weight : bold; font-family: Arial, Helvetica, sans-serif; font-size: 10px;">-->
                        <?php if ($ent==2) echo "<font size='1' color='black'><b>&nbsp;&nbsp;Resumen:</b></font>"; else echo "<font size='1' color='black'><b>&nbsp;&nbsp;Cuerpo del Documento:</b></font>"?>
                        <!--</p>-->
                        


                    </td>
                    <td align="left" valign="middle">
                        <table border="0" width="100%">
                            <tr>
                                <td width="35%" align="left" class="listados1" valign="middle">&nbsp;
            <?php
            if ($nurad != "") {
                $query = "select TO_CHAR(text_fecha,'YYYY-MM-DD HH24:MI AM'), text_codi from radi_texto where radi_nume_radi=$nurad";
                $rs=$db->conn->query($query);
                if(!$rs->EOF) {
                    echo "<b>Versi&oacute;n: &nbsp;&nbsp;&nbsp;</b>";
                    print $rs->GetMenu2("codi_texto", $codi_texto, "", false,"","class='select' onChange='cambio_cuerpo(this.value)'" );
                }
            }
            ?>
                                </td>
                                <td width="35%" align="left" class="listados1" valign="middle">&nbsp;
            <?php
            if ($_SESSION["perm_borrar_recorrido"] == 1) {
                echo '<input type="checkbox" name="chk_ocultar_recorrido" id="chk_ocultar_recorrido" value="1"';
                if ($chk_ocultar_recorrido == "1") echo ' checked';
                echo '><b>Borrar recorrido luego de la firma del documento.</b>';
            }
            ?>
                                </td>
                                <td width="30%" align="right" class="listados1" valign="middle">&nbsp;
            <?php
            // Texto de la última sumilla
            $txt_sumilla = "";
            ?>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                
                <tr>
                    <td class="listado1" colspan="4">
                        <div id="div_cuerpo" style="display: none;"></div>
                        <center>
                            <textarea id='raditexto' name='raditexto' cols='100' rows='10'></textarea>
                        </center>
                    </td>
                </tr>
        </table>
        </div>
        

        <div id="opciones_impresion">
        <?php if ($ent!=2) {?>
        <table width=100% border="0" >
        <tr>
                <td align="left" class="listado1_ver" <? if ($_SESSION["tipo_usuario"]==2) echo "style='display:none'" ?>>Tipo de Impresi&oacute;n: </td>
                <td colspan="1" <? if ($_SESSION["tipo_usuario"]==2) echo "style='display:none'" ?>>
                    <select name="radi_tipo_impresion" id="radi_tipo_impresion" class='select' style="width:490px"  onchange="cambiar(); histop('n',this,<?="'".$radi_tipo_impresion."'"?>);" onclick="limpiardiv()">
                        <option value="1" <?php if($radi_tipo_impresion=="1") echo "selected"; ?> onclick="buscarDatosDestinatario(1);">
                            <?php 
                            //funcion de tx/tx_actualiza_opcion_impresion
                            echo descimpresion(1);?> 
                        </option>
                        <option id="opcCargo" style="display:none" value="4" <?php if($radi_tipo_impresion=="4") echo "selected"; ?> onclick="buscarDatosDestinatario(4);">
                            <?php echo descimpresion(4);?>
                        </option>
                        <option id="opcInstitucion" style="display:none" value="5" <?php if($radi_tipo_impresion=="5") echo "selected"; ?> onclick="buscarDatosDestinatario(5);">
                            <?php echo descimpresion(5);?>
                        </option>                        
                        <option value="6" <?php if($radi_tipo_impresion=="6") echo "selected"; ?> onclick="buscarDatosDestinatario(6);">
                            <?php echo descimpresion(6);?>
                        </option>
                        <option value="2" <?php if($radi_tipo_impresion=="2") echo "selected='selected'"; ?>  onclick="buscarDatosDestinatario(2);">
                        <?php echo descimpresion(2);?>
                        </option>
                        <option id="opcListas" value="3" <?php if($radi_tipo_impresion=="3") echo "selected"; ?> onclick="limpiardiv();">
                        <?php echo descimpresion(3);?>
                        </option>
                        <option value="999" <?php if($radi_tipo_impresion=="999") echo "selected"; ?> onclick="limpiardiv()">
                        <?php echo descimpresion(999);?>
                        </option>
                    </select>
                </td>        
               <td class="listado1_ver" colspan="3">
                    <?php
                    //if ($ent==1) {
                    $funcionjavaat = 'histop(\'m\',this,'.$cmb_texto.');';
                    ?>
                    Ajustar Texto: &nbsp;&nbsp;&nbsp;&nbsp;<select name="cmb_texto" id="cmb_texto" class='select' onchange="<?=$funcionjavaat?>">
                        <?php
                        $valTxt=120;
                        
                        for($i=0; $i<9; $i++)
                        {
                            if($cmb_texto==$valTxt)
                                echo "<option value='$valTxt' $funcionjava selected>$valTxt %</option>";
                            else
                                 echo "<option value='$valTxt' $funcionjava >$valTxt %</option>";
                            $valTxt = $valTxt - 5;
                        }
                        ?>
                    </select>                    
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php
                        if ($ent==1) {
                            if ($chk_plantilla==1)
                                $con_plantilla='checked';
                            else
                                $con_plantilla="0";
                            ?>
                            <input type="checkbox" name="chk_plantilla" id="chk_plantilla" value="1"  <?php echo $con_plantilla?> onclick="histop('l',this,<?="'".$con_plantilla."'"?>);"/> Utilizar Plantilla
                        <?php }
                    ?>
                </td>
            </tr>
            
            <tr><td colspan="3">                    
                    <div id="div_ver_datos" name="div_ver_datos"></div>
                </td>                
            </tr>
            
        </table>
        <?}?>

        <div id="div_estilo_impresion" style="display: none"></div>
         <div id="div_modificar_op"></div>
        <input type="hidden" name="NumDest" id="NumDest"  value="<?=$VariosDest?>">
        </div>
    <?php $numradop = $nurad; ?>
        <input type="hidden" id="num_rad" name="num_rad" value="<?=$numradop;?>"/>
        <div id="div_borrar_opc_imp" style="display: none"></div>
        <div id="div_historico_imp" style="display: none"></div>
    <?php
        if ($numradop!=''){
            $rsOpcImprBusq = ObtenerDatosOpcImpresion($numradop,$db);
    }?>
        <input type="hidden" id="codiOpcImp" name="codiOpcImp" value="<?=$rsOpcImprBusq['OPC_IMP_CODI']?>"/>
        <input type="hidden" id="hidden_acciones_datos" name="hidden_acciones_datos"/>



        <div id='cuerpo_anexos'>
        <table width=100% border="0" class="borde_tab">
            <tr>
                <td class="listado2_ver" width="15%">&nbsp;&nbsp;Descripci&oacute;n de Anexos:</td>
                <td class="listado1" colspan="4">
                    <textarea name="desc_anex" cols="150" class="tex_area" rows="1" onKeyUp='this.value=this.value.substring(0,100)'><?php echo $desc_anex ?></textarea>
                </td>
            </tr>
        </table>
        <?php
        ///////////////////////////     ANEXOS  ///////////////////////////
        if ($accion == "Responder") {
            echo "<br>";
            //include a otro archivo, consultar del arreglo $radicado
            include "cargar_anexos_responder.php";
        }
        ?>
        </div>
        </td>
   </tr>
</table>
</form>
    <center>
    <div id='cuerpo_anexos2' style="width: 99%; text-align: center;">
<?php
        $boton_anexos="No";
        if ($accion=="Responder" || $accion=="Nuevo" || $accion=="Copiar") $nurad="";
        $verrad = $nurad;
        if ($nurad != "") {
            include "$ruta_raiz/anexos/anexos.php";
        } else {
            echo "<center><font color='blue'><h4>Por favor de clic en Vista Previa o Aceptar, para poder cargar archivos anexos.</h4></font></center>";
        }
?>
   </div>
   </center>
    <?php if (isset($mensaje)) { ?>
        <br />
        <table width="100%">
                    <tr>
                        <td class="listado5" align="center"><img src='../iconos/img_alerta_2.gif' alt="alerta">
                            <font color="red" face='Arial' size='3'><?=$mensaje?></font>
                        </td>
           </tr>
        </table>
    <?php      }       ?>
        <script type="text/javascript">
            //refrescar_pagina();
            cambio_cuerpo('<?=$codi_texto?>');
        </script>
    <iframe name="ifr_descargar_archivo" id="ifr_descargar_archivo" style="display: none;" src="">
            Su navegador no soporta iframes, por favor actualicelo.</iframe>

    <div id="div_popup_bloquear_pantalla" style="width: 100%; height: 100%; z-index: 1000; position: fixed; top: 0; left: 0; opacity:0.3; filter:alpha(opacity=30); background-color: black; display: none;"></div>
    <div id="div_popup_pantalla_pequena" style="width: 80%; height: 80%; z-index: 1001; position: fixed; top: 5%; left: 10%; background-color: white; border: #333333 2px solid; display: none">
        <div id="div_popup_titulo" style="font-weight: bold; text-align: center; font-size: small; color: #FFFFFF; background-color:#006394; width: 100%; height: 20px; position: relative;">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
               <tr height="18px">
                   <td width="3%">&nbsp;</td>
                   <td width="94%" style="font-weight: bold; text-align: center; font-size: small; color: #FFFFFF; vertical-align: middle"><span id="span_popup_titulo"></span></td>
                   <td width="3%" align="right" valign="bottom"><img src="data:image/gif;base64,R0lGODlhDwAPANU/AOh1ceFlZPKKf8G6uO64ublNVMR5gbVKU+JqaM1VWvF8dqeWlOlradNVWcFybNNaXqNEQqycmuRubPvZsMmJdfSzne6vmbNnXv36+8JqaalAUJo+OuOSgveNhKlBT7xRWN1kZMd+e+BoZ4R1dMtOUt98gKuKiLFDVa0/Sr1IS7tLWvWTiMuCiPbBwNRdYvvYv/a0sv/etPO+valMSMVTXc9aXuJrav+0ndlgYuWhpM5aXsF0bKlAT7pKWv///////yH5BAEAAD8ALAAAAAAPAA8AAAa1wB8r49gZjztHhvULCW6T2GQ6ld4EoYKgw6FULOAKhdMRFD4KRWh0WQlWl1Eo/SkAAjDfYqaYLXwwAQAFBRISNjI+ERsRPjI2hoQINjYBLT4DPi0BlAiEIiIIDBCZAxAMCKAFByAgo40viqetBwc4IBB/BA0EehAgOLUuKSY+uw+8PiYpLrU1CSU5DQ/UDTklCTUHKDQJCSQ64eEk3jQoBicqPevs7ConBj8GGjwe9Tz4+BrxQQA7" onclick="fjs_popup_cerrar();" alt="X">&nbsp;</td>
               </tr>
            </table>
        </div>
        <div id="div_popup_pantalla_tabajo" style="background-color:white; width: 100%; height: 94%; position: relative; overflow: auto;"></div>
    </div>
  </body>
</html>
