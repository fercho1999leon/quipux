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
$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once("$ruta_raiz/funciones.php");
include_once "$ruta_raiz/Administracion/ciudadanos/util_ciudadano.php";
$ciud = new Ciudadano($db);
p_register_globals();

if ($codTx == 30) { //Tareas
    include_once "$ruta_raiz/tareas/tareas_form_tx.php";
    die ("");
}

$ver="0";
$firma=0;
$docExterno=0;

//Para validar que no se reasigne a sí mismo
if(trim($carpeta) == "")
    $carpeta = -1;

// Se incluyo por register globals
//$fechaAgenda = $_POST['fechaAgenda'];
//$depsel8 = $_POST['depsel8'];
//$depsel = $_POST['depsel'];

$mensaje_error = "";
//$db = new ConnectionHandler("$ruta_raiz","busqueda");
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/seguridad_documentos.php"; // Valida estados de los documentos y otras reglas dependiendo de la transacción realizada
$mensaje_error = "";
$whereFiltro= "0";

/**
* FILTRO DE DATOS
*/
if(isset($_POST['checkValue'])) {               //Si se escogieron radicados de la lista
    foreach ($_POST['checkValue'] as $radi_nume => $chk) {
        if (trim($radi_nume)!="") {
            $flag = validar_transacciones($codTx, $radi_nume, $db);
            if ($flag == "")
                $whereFiltro .= ",$radi_nume";
            else
                $mensaje_error .= $flag;
        }
    }
    if ($mensaje_error != "") 
            $mensaje_error = "<br><center><span style='color: red; font-weight: bold;'>Existieron inconvenientes al realizar esta acci&oacute;n con los siguientes documentos:<br><br></span></center>" . $mensaje_error . "<br>";
} else {        //Si no se escogio ningun radicado
        $mensaje_error .= "<br><center><span style='color: red; font-weight: bold;'>No hay documentos seleccionados.</span></center><br>";
}
include_once "$ruta_raiz/tipo_documental/obtener_datos_trd.php";
include_once "$ruta_raiz/funciones_interfaz.php";       
echo "<html>".html_head();
require_once "$ruta_raiz/js/ajax.js";

//echo "---".$_SESSION["existe_radi_path"];
if ($codTx == 70) { //Imprimir sobres
    include_once "accion_imprimir_sobre.php";
    die ("");
}

//Se consulta si existen documentos antecedentes al reasignar el o los documentos de respuesta
if ($codTx == 9) {
    $isql = "select radi_nume_asoc, esta_codi from radicado where radi_nume_radi in ($whereFiltro)";
    $rs = $db->conn->Execute($isql);
    while (!$rs->EOF) {
        $radi_nume_asoc = $rs->fields["RADI_NUME_ASOC"];
        $estado = $rs->fields["ESTA_CODI"];       
        if($radi_nume_asoc != "" and $estado ==1)
        {           
            $flag = validar_transacciones($codTx, $radi_nume_asoc, $db);
            if ($flag == "")
                $radiNumeAsociados[] = $rs->fields["RADI_NUME_ASOC"];
        }
        $rs->MoveNext();
    }
}

//Se consulta si los documentos a Archivar tienen tareas pendientes
$cantidad_tareas = 0;
if ($codTx == 13) {
    $isq_tarea = "select count(tarea_codi) as cant_tareas from tarea where radi_nume_radi in ($whereFiltro) and estado = 1";
    $rs_tarea = $db->conn->Execute($isq_tarea);
    $cantidad_tareas = $rs_tarea->fields["CANT_TAREAS"];     
}
require_once "$ruta_raiz/js/ajax.js";
?>
<script type="text/javascript" language="JavaScript" src="<?=$ruta_raiz?>/Administracion/ciudadanos/adm_ciudadanos.js"></script>
<script type="text/javascript">
    var cont = new Array (0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
    function selOperacion(fin,init){
        var listIdex=0;
        var escribe=false;

        for(i=0;i<document.getElementById('Accion').options.length;i++){
            if(document.getElementById('Accion').options[i].selected && document.getElementById('Accion').options[i].value!='0'){
                //Almacena
                listIdex=document.getElementById('Accion').options[i].value;
                

                for (j=init;j<=fin;j++){//1;27
                    if  (cont[j]==listIdex){
                        //alert('repetido');
                        escribe=true;
                    }else{
                        //alert('no repetido')
                        if (j==listIdex)
                            cont[document.getElementById('Accion').options[i].value]=listIdex;
                          //  alert(cont[j]);
                    }
                }
             if (escribe==false)
                 document.realizarTx.observa.value+="*" +document.getElementById('Accion').options[i].text + " ";
                 
            }
        }
        formEnvio_contador_caracteres();
    }

    function borrarCaja(){
        document.realizarTx.observa.value="";
        cont = new Array (0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
        formEnvio_contador_caracteres();
    }

    var marcado = 0,marcadoF="";
    function Obtener_val(formulario){
        marcado=formulario.value
        document.getElementById("opcDoc").value=marcado;
        marcadoF=marcado;
        //alert(marcado);
    }


    function verificar_chk() {
        for(i=0;i<document.realizarTx.elements.length;i++) {
            if(document.realizarTx.elements[i].checked==1 )
                return true;
        }
        return false;
    }

    function verificar_combo(nombre)
    {
        for(i=0;i<document.getElementById(nombre).options.length;i++)
        {

            if(document.getElementById(nombre).options[i].selected && document.getElementById(nombre).options[i].value!='0')
                return true;
        }
        return false;
    }

    var accion="";

    function guarda_combo_accion(nombre)
    {
        var j=1;
        for(i=0;i<document.getElementById(nombre).options.length;i++)
        {
            if(document.getElementById(nombre).options[i].selected && document.getElementById(nombre).options[i].value!='0')
                accion+= j++ +".-"+document.getElementById(nombre).options[i].text + " ";
        }
        return true;
    }

    function markAll(noRad) {
        if( noRad >=1) {
            for(i=3;i<document.realizarTx.elements.length;i++)
                document.realizarTx.elements[i].checked=1;
        } else {
            for(i=3;i<document.realizarTx.elements.length;i++)
                document.realizarTx.elements[i].checked=0;
        }
        
        document.realizarTx.chk_reasigna_padre.checked=0;
    }

var estado='';
var estadoF='';
var esExterno=true;

var var_ejecutar_okTx = true;
    function okTx(var1,var4) {
        if (!var_ejecutar_okTx) return;     
         // Verificamos que existan documentos seleccionados
        if(!verificar_chk()) {
            alert ('No existen documentos seleccionados.');
            return false;
        }

        if(<?=$codTx?> == 11  && var1=='0' && var4=='2' ){
            var resultado = confirm("Este documento no tiene imagen asociada, ¿Está seguro de enviar?");
        }else if(<?=$codTx?> == 3 && var1=='0' && var4=='0' ){
              var resultado = confirm("Este documento no tiene imagen asociada, ¿Está seguro de enviar?");
        }else{
            var resultado=esExterno;
        }

        if(resultado==true){
            //Si es Enviar Físico
            if (<?=$codTx?> == 69){
                // Verificamos que existan usuarios seleccionados
                if(!verificar_combo('usCodSelect')) {
                    alert ('Seleccione el usuario al que enviara el archivo físico');
                    return false;
                }
                //Verifico que hayan ingresado Responsable de Traslado
                if(trim(document.getElementById('nombre').value) =='') {
                    document.getElementById('nombre').value='';
                    alert("Ingrese el responsable del traslado");
                    return false;
                }
                //Armo estado del documento
                if (marcadoF=='B'){
                    estado="Bueno";
                }
                else if (marcadoF=='M' || marcadoF==''){
                    estado="Malo";
                }
                else if (marcadoF=='R'){
                    estado="Regular";
                }
                estadoF="/Estado del archivo enviado físicamente :"+estado;
            }
            // Si es reasignar
            if (<?=$codTx?> == 9) {
                    // Verificamos que existan usuarios seleccionados
                    if(!verificar_combo('usCodSelect')) {
                        alert ('Seleccione el usuario al que reasignará el documento.');
                        return false;
                    }                    
     
                    //Se valida que no se reasigne el documento al mismo usuario
                    if(<?=$carpeta?> == "14"){ //Bandeja Compartida
                        if(document.getElementById("usCodSelect").value == <?=$_SESSION["usua_codi_jefe"]?>){
                           alert ('Esta tratando de reasignar el documento al usuario actual del mismo, por favor seleccione uno diferente.');
                           return false;    
                        }
                    }
//                      else{
//                        if(document.getElementById("usCodSelect").value == $_SESSION["usua_codi"]){
//                            alert ('Esta tratando de reasignar el documento a su propio usuario, por favor seleccione uno diferente.');
//                            return false;  
//                        }
//                    } 
                 
                    //Para tomar valor al reasignar documentos padre
                    document.getElementById("chk_reasigna_padre").value = document.getElementById("chk_reasigna_padre").checked;
                    
                    // Verificamos la fecha de reasignación
                    var fechaActual = new Date(<?=date("Y")?>,<?=date("n")?>,<?=date("d")?>);
                    fecha_doc = document.realizarTx.fecha_doc.value;
                    var fecha = new Date(fecha_doc.substring(6,10),fecha_doc.substring(3,5), fecha_doc.substring(0,2));
                    var tiempoRestante = fecha.getTime() - fechaActual.getTime();
                    var dias = Math.floor(tiempoRestante / (1000 * 60 * 60 * 24));
                    if (dias < 0) {
                    alert ("La fecha máxima de trámite debe ser mayor a la fecha actual");
                        return false;
                    }                  
                }

                if (<?=$codTx?> == 8)
                {
                    // Verificamos que existan usuarios seleccionados
                    if(!verificar_combo('usCodSelect') && !verificar_combo('slc_lista')) {
                        alert ('Seleccione el usuario al que desea informar sobre este documento.');
                        return false;
                    }
                }
                
                // Se valida si el documento a Archivar tiene tareas pendientes
                if (<?=$codTx?> == 13 && <?=$cantidad_tareas?> > 0)
                {
                    if(!confirm ('El documento seleccionado tiene tareas pendientes. ¿Está seguro que desea archivar?'))
                        return false;
                }
                if (document.getElementById("inputString"))
                if (document.getElementById("inputString").value=='')
                    document.getElementById("txt_check_carpeta").value=0;
                if (<?=$codTx?> == 88){                    
                    txtcodTrd = document.getElementById("txt_check_carpeta").value;                   
                        if(txtcodTrd<=0){
                        alert("Seleccione la Carpeta Virtual de la lista");
                        return false;
                    }
                }
                var_ejecutar_okTx = false;  
                document.realizarTx.observa.value = document.realizarTx.observa.value.substr(0,550)+estadoF;
                document.realizarTx.action = "realizarTx.php";
                document.realizarTx.submit();
            } else if (resultado==false) {
                return false;
            }
                    
    }

        function cambiar_combo_usuarios() {
            var area = '';
            var coma = '';
            for(i=0;i<document.getElementById('depsel').options.length;i++) {
                if (document.getElementById('depsel').options[i].selected) {
                    area += coma +document.getElementById('depsel').options[i].value;
                    coma = ',';
        }
    }
            if (area != '')
                nuevoAjax('mnu_usr', 'GET', 'formEnvio_ajax.php', 'area='+area+'&codTx=<?=$codTx?>');
            return;
   }

        function Start(URL,ci) {
            var x = (screen.width - 1100) / 2;
            var y = (screen.height - 540) / 2;
            var nombre ='';
            //if(document.formu1.lista_usr.value!='0')
            //{
                //nombre = document.formu1.lista_usr.options[document.formu1.lista_usr.selectedIndex].text;
                //alert(nombre);
                windowprops = "top=0,left=0,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=1100,height=540";
                //URL = URL + '?lst_codigo=' + document.formu1.lista_usr.value + '&lst_nombre=' + nombre + '&accion=2';
                //URL = URL + '?codigo=ciu_s'+'&buscar_nom=' + document.getElementById('nomCiuFun').value +  '&accion=2';
                URL = URL + '?codigo=ciu_s'+'&buscar_nom='+ci+'&accion=2';
                //alert(URL);
                preview = window.open(URL , "editar_ciudadano", windowprops);
                preview.moveTo(x, y);
                preview.focus();
            //}
            //else
              //  alert("Por favor, seleccione una lista");
        }


function llamarListado(nombreCarpeta, codigoCarpeta){
     location.href= '<?=$ruta_raiz?>/cuerpo.php?nomcarpeta='+nombreCarpeta+'&carpeta='+codigoCarpeta+'&adodb_next_page=1';
     document.getElementById('btn_Buscar').focus();
}

var formEnvio_contador_caracteres_TimerId = 0;

function limita(elEvento) {
    var elemento = document.getElementById("observa");
    var maxCaracteres=550;
    if ('<?=$codTx?>'=='69') maxCaracteres=110;
    formEnvio_contador_caracteres_TimerId = setTimeout("formEnvio_contador_caracteres()", 50);
    // Obtener la tecla pulsada
    var evento = elEvento || window.event;
    var codigoCaracter = evento.charCode || evento.keyCode;
    // Permitir utilizar las teclas con flecha horizontal
    if(codigoCaracter >= 37 && codigoCaracter <= 40) return true;
    // Permitir borrar con la tecla Backspace y con la tecla Supr.
    if(codigoCaracter == 8 || codigoCaracter == 46) return true;

    if(elemento.value.length >= maxCaracteres ) return false;

    document.getElementById("spn_numero_caracteres_disponibles").innerHTML = elemento.value.length.toString() + ' de ' + maxCaracteres.toString();
    return true;
}
function formEnvio_contador_caracteres() {
    var elemento = document.getElementById("observa");
    var maxCaracteres=550;
    if ('<?=$codTx?>'=='69') maxCaracteres=110;
    if(elemento.value.length >= maxCaracteres) 
        elemento.value = elemento.value.substr(0, maxCaracteres);
    document.getElementById("spn_numero_caracteres_disponibles").innerHTML = elemento.value.length.toString() + ' de ' + maxCaracteres.toString();
    return;
}


function init() {

    var nomCarpeta = ""; //Nombre de la bandeja que esta en la base de datos
    var codCarpeta = ""; //Codigo de la bandeja que esta en la base de datos (Primary Key)
    shortcut.add("Alt+b", function() {
        nomCarpeta = "En Elaboración";
        codCarpeta = "1";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+r", function() {
        nomCarpeta = "Recibidos";
        codCarpeta = "2";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+c", function() {
        nomCarpeta = "Eliminados";
        codCarpeta = "6";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+n", function() {
        nomCarpeta = "No Enviados";
        codCarpeta = "7";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+e", function() {
        nomCarpeta = "Enviados";
        codCarpeta = "8";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+p", function() {
        nomCarpeta = "Reasignados";
        codCarpeta = "12";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+a", function() {
        nomCarpeta = "Archivados";
        codCarpeta = "10";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+i", function() {
        nomCarpeta = "Informados";
        codCarpeta = "13";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+t", function() {
        nomCarpeta = "Tareas Recibidas";
        codCarpeta = "15";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
    shortcut.add("Alt+s", function() {
        nomCarpeta = "Tareas Enviadas";
        codCarpeta = "16";
        llamarListado(nomCarpeta, codCarpeta)
        window.top.window.leftFrame.cambioMenuAsociado(nomCarpeta);
    });
}
function agregarTodos()
{
    var optn = document.createElement("OPTION");
    optn.text = 'Todos los Usuarios Activos de la Institución';
    optn.value = -1;
    slc_lista.options.add(optn);
}
function MostrarFila(fila, ruta_raiz){
            var elemento=document.getElementsByName(fila);
            imgAgregar = "agregar.png";
            imgQuitar = "quitar.png";

            for (var i=0; i<elemento.length; i++){

                if (elemento[i].style.display=='none')
                {
                    if(document.getElementById("spam_"+fila)!=null)
                       document.getElementById("spam_"+fila).innerHTML = '<img src='+ruta_raiz+'/imagenes/'+  imgQuitar +' border="0" height="15px" width="15px">';
                    elemento[i].style.display='';
                }
                else{
                   if(document.getElementById("spam_"+fila)!=null)
                        document.getElementById("spam_"+fila).innerHTML = '<img src='+ruta_raiz+'/imagenes/'+ imgAgregar  +' border="0" height="15px" width="15px">';
                   elemento[i].style.display='none';
                }
               MostrarFila(elemento[i].id, ruta_raiz);
            }
	}
window.onload=init();
var contador = 0;//para que haga la busqueda cada 3 caracteres
function lookupTrd(obj,e) {
           inputString = obj.value+" ";
        contador++;
        if (!e) var e = window.event;
	if (e.keyCode) code = e.keyCode;
	else if (e.which) code = e.which;
	
      
                     if (contador==3 || code==32 || code==13) //palabra es igual a 3 caracteres ejecuta y es barra espaciadora
			
                        $.post("../tipo_documental/ajax_obtener_trd.php", {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
                                        contador=0;//si encuentra se pone en 0 para que realice nuevamente la busqueda                                    
					$('#suggestions').show();
					$('#autoSuggestionsList').html(data);
                                        //document.getElementById('carpeta_seleccionada').value="";
                                        document.getElementById('carpeta_seleccionada').innerHTML="";
                                }
			});
                        
//		}
} // lookup
function fill(thisValue) { 
    
        $('#inputString').val(thisValue);
        //$('#carpeta_seleccionada').val(thisValue);
         document.getElementById('carpeta_seleccionada').innerHTML=thisValue;
        setTimeout("$('#suggestions').hide();", 10);
}
function codigoFusC(idTrd){    
    document.getElementById("txt_check_carpeta").value=idTrd;

}
function limpiar(){ 
    if (document.getElementById('inputString').value=='')
         document.getElementById('carpeta_seleccionada').innerHTML="";
        //document.getElementById('carpeta_seleccionada').value="";
}
</script>
<?php

$usrPermiso = $_SESSION['usua_perm_email_all'];//= ObtenerPermisoUsuario($_SESSION["usua_codi"], 31, $db);//ObtenerDatosUsuario($_SESSION["usua_codi"], $db);

?>
<body onLoad="markAll(1); <?php if ($usrPermiso==1 and $codTx==8){ ?> agregarTodos();<?php } ?>">
  <div id="spiffycalendar" class="text"></div>
  <link rel="stylesheet" type="text/css" href="../js/spiffyCal/spiffyCal_v2_1.css">
  <script type="text/javascript" src="../js/spiffyCal/spiffyCal_v2_1.js"></script>
  <script type="text/javascript" src="../Administracion/ciudadanos/jquerysubir/jquery-1.3.2.min.js"></script>
  <script type="text/javascript">
    <?  if(!$fecha_doc) $fecha_doc = date("d-m-Y");  ?>
        var dateAvailable1 = new ctlSpiffyCalendarBox("dateAvailable1", "realizarTx", "fecha_doc","btnDate1","<?=$fecha_doc?>",scBTNMODE_CUSTOMBLUE);
  </script>
  <br/>

  <center>

<?
    //Si hay algun error, se muestra mensaje donde se indica que no se puede archivar el(los) radicado(s)
    if ($mensaje_error != "" )
        echo ("<table class='borde_tab' width='100%' cellspacing=0><tr class='listado2'><td width='30%'>&nbsp;</td><td width='40%'>$mensaje_error</td><td width='30%'>&nbsp;</td></tr></table></center>");
    if ($codTx == 9 or $codTx == 69) {  //Buscamos las áreas que se desplegarán en los combos de reasignar e informar
        if ($_SESSION["perm_saltar_organico_funcional"]==1)
            $where_area = "inst_codi=".$_SESSION["inst_codi"];
        elseif($_SESSION["cargo_tipo"]!=1 && $_SESSION["usua_publico"] !=1)
            $where_area = "depe_codi=".$_SESSION["depe_codi"];
        else {
            // Obtenermos el área padre del área actual
            $sql = "select coalesce(depe_codi_padre, depe_codi) as depe_codi from DEPENDENCIA WHERE depe_codi=".$_SESSION["depe_codi"];
            $rs = $db->conn->Execute($sql);
            $where_area = $rs->fields["DEPE_CODI"];
            if ($where_area != $_SESSION["depe_codi"]) {
                $where_area .= "," . $_SESSION["depe_codi"];
            }
//            if ($_SESSION["perm_saltar_organico_funcional"]==1) {
//                // Si el usuario tiene permisos para saltar el organico funcional, muestra un nivel mas.
//                $sql = "select depe_codi from dependencia where depe_codi_padre=".$_SESSION["depe_codi"];
//                
//                $rs = $db->conn->Execute($sql);
//                while(!$rs->EOF) {
//                    $where_area .= "," . $rs->fields['DEPE_CODI'];
//                    $rs->MoveNext();
//                }
//            }
            $where_area = "coalesce(depe_codi_padre, depe_codi) in ($where_area) or depe_codi in ($where_area)";
        }
        $sql = "select distinct depe_nomb, depe_codi from dependencia where depe_estado=1 and ($where_area) order by 1";
        
        $rs_area = $db->query($sql);
        //Por David Gamboa
        //$sql = "select usua_nombre, usua_codi from datos_usuarios where usua_esta=1 and depe_codi=".$_SESSION["depe_codi"]." order by 1";
        //El cambio lo hago por la incidencia 2049
//        $sql = "select usua_nomb || ' ' || usua_apellido || 
//                    case when usua_subrogado<>1 then ' (Subrogante)' else '' 
//                    end as usua_nombre
//                    , usua_codi from usuario where usua_esta=1 and usua_login not like 'UADM%' and depe_codi=".$_SESSION["depe_codi"]." order by 1";
        //echo $sql;
        //SUBROGADO SUBROGANTE
        $sql=utilSqlSubrogacion($_SESSION["depe_codi"]);
        $rs_usr = $db->conn->Execute($sql);
    }
switch ($codTx)
{
        case 2:
            $accion = "Acci&oacute;n: Eliminar Documentos ";
                break;
        case 4:
            $accion = "Env&iacute;o Electr&oacute;nico de Documentos ";
                break;
        case 3:
            $ver=$_SESSION["existe_radi_path"];
            $firma=$_SESSION["firma_digital"];
            $accion = "Acci&oacute;n: Enviar Documentos Manualmente";
                break;
        case 5:
            $accion = "Acci&oacute;n: Env&iacute;o Manual de Documentos ";
                break;
        case 6:
            $accion = "Acci&oacute;n: Reestablecer Documentos Eliminados ";
                break;
        case 7:
            $accion = "ACCI&Oacute;N: Borrar Informados ";
                break;
        case 8: //Informar
            $sql = "select distinct depe_nomb, depe_codi from dependencia where depe_estado=1 and inst_codi=".$_SESSION["inst_codi"]." order by 1";            
            $rs_area = $db->query($sql);
            $sql=utilSqlSubrogacion($_SESSION["depe_codi"]);
            //echo $sql;
            $rs_usr = $db->conn->Execute($sql);
            $sql = "select lista_nombre, lista_codi from lista where lista_estado = 1 and (usua_codi=0 and inst_codi=".$_SESSION["inst_codi"].") or usua_codi=".$_SESSION["usua_codi"]." order by 1";
            $rs_lista = $db->conn->Execute($sql);
            $menu_area = $rs_area->GetMenu2('depsel[]', $_SESSION["depe_codi"], false, true, 8, " id='depsel' class='select' style='height:85px;' onChange='cambiar_combo_usuarios()' ");            
            //usuarios
            $menu_usr  = $rs_usr->GetMenu2("usCodSelect[]", 0, false, true, 8," id='usCodSelect' class='select' style='height:85px; overflow: auto'" );            
            $menu_lista  = $rs_lista->GetMenu2("slc_lista[]", 0, false, true, 8," id='slc_lista' class='select' style='height:85px;'" );            
            $accion = "<table width='100%' border='0' cellspacing='1' class='borde_tab_blanco'>";
            $accion .= "<tr class='titulos4'><td colspan='4'><center>Acci&oacute;n: Informar Documentos</center></td></tr>";
            $accion .= "<tr><td>Área:</td><td>&nbsp;</td><td>Servidor Público:</td><td>&nbsp;</td></tr>";
            $accion .= "<tr><td colspan=2>$menu_area</td><td colspan=2><div name='mnu_usr' id='mnu_usr'>$menu_usr</div></td></tr>
                        <tr><td colspan=4><hr></td></tr>
                        <table width='100%' border='0' cellspacing='1' class='borde_tab_blanco'>
                        <tr><td width='35%'>&nbsp;</td><td colspan=2>Listas: </td><td>&nbsp;</td></tr>
                        <tr><td width='35%'>&nbsp;</td><td colspan=2>$menu_lista</td><td>&nbsp;</td></tr>
                        </tr><tr><td colspan=4><hr></td></tr></table>";
            //$accion .= "<tr class='listado1'><tr>";
                break;
        case 9: // Reasignar
            $menu_area = $rs_area->GetMenu2('depsel', $_SESSION["depe_codi"], false, false, 0,
                                            " id='depsel' class='select' onChange='cambiar_combo_usuarios()' ");
            if($carpeta == 14)
                $codi_usuario = $_SESSION['usua_codi'];
            else
                $codi_usuario = 0;
            $menu_usr  = $rs_usr->GetMenu2("usCodSelect", $codi_usuario, "0:&lt;&lt; Seleccione Usuario &gt;&gt;", false,""," id='usCodSelect' class='select'" );
            $accion = "<table width='100%' border='0' cellspacing='1'>";
            $accion .= "<tr class='titulos4'><td>Acci&oacute;n:</td><td>Área:</td><td>Usuario:</td></tr>";
            $accion .= "<tr class='listado1'><td valign='top'>Reasignar Documentos</td><td>$menu_area</td><td>
                        <div name='mnu_usr' id='mnu_usr'>$menu_usr</div></td><tr></table>";
                break;
        case 11:
            
            $ver=$_SESSION["existe_radi_path"];
            $firma=$_SESSION["firma_digital"];
            $docExterno=$_SESSION["radi_tipo_doc"];
            $accion = "Acci&oacute;n: Firmar y Enviar Documentos";
                break;
        case 13:
            $accion = "Acci&oacute;n: Archivar Documentos";
            break;
        case 17:
            $accion = "Acci&oacute;n: Reestablecer Documentos Archivados";
            break;
        case 18:
            $accion = "Acci&oacute;n: Comentar Documentos";
            break;
        case 20:
            $accion = "Acci&oacute;n: Devoluci&oacute;n de Documentos";
            break;
         case 69://Enviar Físico
            $accion = "Acci&oacute;n: Enviar F&iacutesico";
            //Defino permiso de Bandeja de Entrada
            $permiso="";
            $sql="select id_permiso from permiso_usuario where id_permiso=5 and usua_codi=".$_SESSION["usua_codi"];            
            $rs_perm = $db->query($sql);
            while(!$rs_perm->EOF) {
                $permiso=$rs_perm->fields['ID_PERMISO'];
                $rs_perm->MoveNext();
            }
            if(isset($permiso) and ($permiso=="")){
                $menu_area = $rs_area->GetMenu2('depsel',$_SESSION["depe_codi"],false,false,0," id='depsel' class='select' onChange='cambiar_combo_usuarios()' ");
                $permiso="";
            }else{
                $sqlP = "select distinct depe_nomb, depe_codi from dependencia where depe_nomb<>'' and depe_estado = 1 and inst_codi=".$_SESSION["inst_codi"]." order by depe_nomb";
                //echo $sqlP;
                $rs_area = $db->query($sqlP);
                $menu_area = $rs_area->GetMenu2('depsel',$_SESSION["depe_codi"],false,false,0," id='depsel' class='select' onChange='cambiar_combo_usuarios()' ");
                $sqlP="";
            }
            $menu_usr  = $rs_usr->GetMenu2("usCodSelect", 0, "0:&lt;&lt; Seleccione Usuario &gt;&gt;", false,""," id='usCodSelect' class='select'" );
//            echo "</td></tr>";
            $accion = "<table width='100%' border='0' cellspacing='1'>";            
            $accion .= "<tr class='titulos4'><td>Acci&oacute;n:</td><td>Area:</td><td>Usuario:</td><td>Responsable Traslado:</td><td>Estado Documento:</td></tr>";
            $accion .= "<tr class='listado1'><td valign='top'>Enviar Físico</td><td>$menu_area</td><td><div name='mnu_usr' id='mnu_usr'>$menu_usr</div></td><td><input type=\"text\" name=\"nombre\" id=\"nombre\" maxlength=\"100\"><input type=\"hidden\" name=\"texto\" id=\"texto\"value=\"\"></td><td><input type=\"radio\" value=\"B\" checked name=\"estadoF\" onclick=\"Obtener_val(this)\" >Bueno<input type=\"radio\" value=\"R\" name=\"estadoF\" onclick=\"Obtener_val(this)\" checked>Regular<input type=\"radio\" value=\"M\" name=\"estadoF\" onclick=\"Obtener_val(this)\" checked>Mala</td><tr></table> <input type=\"hidden\" name=\"opcDoc\" id=\"opcDoc\" value=\"\" checked>" ;            
            
            break;
        case 83: //Recupera Documentos
            $accion = "Acci&oacute;n: Recuperar Documentos";
            break;
         case 88: //Asociar a Carpetas Virtuales
            $accion = "Acci&oacute;n: Incluir en Carpeta Virtual";  
            
            //echo $accion2;
            break;
        case 90:
            $accion = "Acci&oacute;n: Enviar Documentos Firmados Electr&oacute;nicamente por Ciudadanos";
            break;
        }

  ?>

<style>a:link, a:visited, a:hover {color: blue;}</style>
    <form action="javascript:;" name="realizarTx" method='post' >
        <input type='hidden' name="carpeta" value="<?=$carpeta?>">
        <input type='hidden' name="codTx" value="<?=$codTx?>">  
        <!-- <input type='hidden' name="optEstado" value="<?$_POST['optEstado']?>">-->
        <?if($codTx != 9){?>  
            <input type="hidden" name="chk_reasigna_padre" id="chk_reasigna_padre" value="0" />
        <?}?> 
            
        <table width="100%" border="0" cellpadding="0" cellspacing="5" class="borde_tab">
            <tr>
                <td class="titulos4" colspan="4" width='100%' align='center'><?=$accion?></td>
            </tr>
        <?if ($codTx==88){
            
            echo '<input type="hidden" name="txt_check_carpeta" id="txt_check_carpeta" value="0" />';
            //  comento la funcionalidad de arbol y reemplazo por autocomplete
            
            ?>
                        <tr>
                            <td width="100%">
                                
                                <table width="100%" class="borde_tab" border="0">
                                <tr>
                                    <td class="titulos2" width="25%">
                                    Buscar Carpeta Virtual (Nombre):</td>
                                    <td class="listado2" colspan="3">
                                    <input type="text" size="30" value="" id="inputString" name="inputString" onkeypress="lookupTrd(this,event);" onblur="limpiar()" autocomplete="off"/>                                    
                                    <font size="1">Ingrese los primeros caracteres del nombre de la Carpeta y seleccione de la lista.</font>
                                    <div class="suggestionsBox" id="suggestions" style="display:none; width:300px; height:100px; overflow-x:hidden; autoflow-y:scroll;">
                                    <div class="suggestionList" id="autoSuggestionsList">
                                    &nbsp;
                                    </div>
                                    </div>
                                    </td>                                    
                                    </tr>
                                    <tr><td class="titulos2" width="25%">
                                    Agregar en la Carpeta:</td>
                                        <td class="listado2" colspan="3">
                                            <div id="carpeta_seleccionada" name="carpeta_seleccionada"/></div>
                                        </td>
                                        
                                    </tr>
                                </table>
                            </td>
                        </tr>
                
            
        <?php }?>


<?      if ($codTx==9) {        //Muestra la fecha maxima de tramite para reasignar documentos y firmar y enviar ?>
            <tr align="center">
                <td colspan="2" align=center>
                    <br /><span ><b>Fecha M&aacute;xima de Tr&aacute;mite dd/mm/aaaa: </b></span>
                    <script type="text/javascript">
                        dateAvailable1.date = "<?=date('Y-m-d');?>";
                        dateAvailable1.writeControl();
                        dateAvailable1.dateFormat="dd-MM-yyyy";
                    </script><br>
                </td>
            </tr>
<?	}
	if($_SESSION["firma_digital"]==1 and $codTx == 11) { //Solicita campos necesarios para firma digital
            if ($_SESSION["tipo_usuario"]==2) {
                echo '<input type="hidden" name="chk_firma" id="chk_firma" value="1">';
            } else {
?>
            <tr align="center">
                <td colspan="2" class="celdaGris" align=center>
                    <br />
                    <input type="checkbox" name="chk_firma" id="chk_firma"  class="ebutton" value="0"> <!--VALUE 1 PARA FIRMA ELECTRONICA-->
                        <span><b>&#191;Documento?</b></span><br/>
                </td>
            </tr>
<?          }
        }

if ($codTx==9){
    $contAcc=0;
    //Verifico permiso de acceso
/*    $sql="SELECT A.ID_PERMISO FROM PERMISO_USUARIO A,PERMISO B
     WHERE A.ID_PERMISO=B.ID_PERMISO AND A.ID_PERMISO=4 AND B.ESTADO=1 AND A.USUA_CODI=".$_SESSION["usua_codi"];
    $rs1=$db->query($sql); /* */
    if($_SESSION["perm_acti_accion"]==1){

            //while(!$rs1->EOF){
            //Cargo acción de documentos para FFAA, se aplica en reenviados
            $sql_accion = "select accion_nombre,accion_codi,inst_codi from accion where inst_codi=".$_SESSION['inst_codi']." order by accion_codi";
            
            $rs_accion = $db->conn->Execute($sql_accion);
            //Verifico existencia de inf
            while(!$rs_accion->EOF) {
                //$acc=$rs_accion->fields["ACCION_CODI"];
                $rs_accion->MoveNext();
                $contAcc+=1;
            }

            if($contAcc > 1){
                $sqlMin="select min(accion_codi) as init ,max(accion_codi) as Fin from accion where inst_codi=".$_SESSION['inst_codi'];
                $rs_min= $db->conn->Execute($sqlMin);
                $inicio=$rs_min->fields["INIT"];
                $final=$rs_min->fields["FIN"];
            }

            $rs_accion = $db->conn->Execute($sql_accion);

            if($contAcc > 1){
                $menu_accion  = $rs_accion->GetMenu2("Accion[]", 0, false, false,25," id='Accion' class='select' size =10 onclick='selOperacion($final,$inicio);'");
            }?>
    <? if($contAcc > 1){?>
        <table border="1" align="center" width="100%">
        <tr>
            <td WIDTH=5%>Operaciones:</td>
            <td WIDTH=15%> <b></b><?echo $menu_accion;?></td>
            <td WIDTH=80% align='center' valign='middle'>
                <b>Comentario: &nbsp;</b>
            <textarea id="observa" name=observa cols=70 rows=3 class=ecajasfecha onkeypress="return limita(event);"></textarea>            
            <span id="spn_numero_caracteres_disponibles"></span>
            <table >
                <? if(sizeof($radiNumeAsociados) > 0){?>                
                <tr>
                    <td align ="center">
                        <input type="checkbox" name="chk_reasigna_padre" id="chk_reasigna_padre" value="0" />¿Desea reasignar los documentos antecedentes?                        
                    </td>
                </tr>     
                 <?} else{?>  
                    <input type="hidden" name="chk_reasigna_padre" id="chk_reasigna_padre" value="0" />
                <?}?>     
                <tr>&nbsp;</tr>
                <tr>
                <td>
                <input type='button' value='Aceptar' onClick="okTx('<?=$ver?>','<?=$docExterno?>');" name='enviardoc' class='botones' id='REALIZAR'>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type='button' value='Regresar' onClick='history.back();' name='enviardoc' class='botones' id='Cancelar'>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type='button' value='Borrar' onClick='borrarCaja();' name='enviardoc' class='botones' id='Borrar'>
                </td>
                </tr>
            </table>
            </td>
        </tr>
        </table>
   <?}else{?>
             <table border="1" align="center" width="100%">
            <tr>
            
            <td WIDTH=80% align='center' valign='middle'>
            <b>Comentario: &nbsp;</b>
            <textarea id="observa" name=observa cols=70 rows=3 class=ecajasfecha onkeypress="return limita(event);"></textarea>
            <span id="spn_numero_caracteres_disponibles"></span>
            <table >
                <? if(sizeof($radiNumeAsociados) > 0){?>                
                <tr>
                    <td align ="center">
                        <input type="checkbox" name="chk_reasigna_padre" id="chk_reasigna_padre" value="0" />¿Desea reasignar los documentos antecedentes?                        
                    </td>
                </tr>     
                 <?} else{?>  
                    <input type="hidden" name="chk_reasigna_padre" id="chk_reasigna_padre" value="0" />
                <?}?>     
                <tr>&nbsp;</tr>
                <tr>
                <td>
                <input type='button' value='Aceptar' onClick="okTx('<?=$ver?>','<?=$docExterno?>');" name='enviardoc' class='botones' id='REALIZAR'>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type='button' value='Regresar' onClick='history.back();' name='enviardoc' class='botones' id='Cancelar'>
                <!--&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input type='button' value='Borrar' onClick='borrarCaja();' name='enviardoc' class='botones' id='Borrar'>-->
                </td>
                </tr>
            </table>
            </td>
        </tr>
        </table>

      <?}
   }

    if($_SESSION["perm_acti_accion"]!=1 && ($codTx!==9)){ ?>
           <tr align="center">
                <td width='25%' align='right' valign='middle'><br/>
                    <b>Comentario: &nbsp;</b>
                </td>
                <td width='75%' align='left' valign='middle'><br/>
                    <textarea id="observa" name=observa cols=70 rows=3 class=ecajasfecha onkeypress="return limita(event);"></textarea>
                    <span id="spn_numero_caracteres_disponibles"></span>
                </td>
            </tr>           
                <? if(sizeof($radiNumeAsociados) > 0){?>                
                <tr>
                     <td  colspan="2" align='center'>
                        <input type="checkbox" name="chk_reasigna_padre" id="chk_reasigna_padre" value="0" />¿Desea reasignar los documentos antecedentes?                        
                    </td>
                </tr>     
                 <?} else{?>  
                 <tr>
                     <td  colspan="2" align='center'>
                        <input type="hidden" name="chk_reasigna_padre" id="chk_reasigna_padre" value="0" />
                     </td>
                </tr>   
                <?}?>     
                <tr>&nbsp;</tr>
                 
            <tr>
                    <td  colspan="2" align='center'>
<? if ($whereFiltro !=="0") { ?>
                        <input type='button' value='Aceptar' onClick="okTx('<?=$ver?>','<?=$docExterno?>');" name='enviardoc' class='botones' id='REALIZAR'>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<? } ?>
                        <input type='button' value='Regresar' onClick='history.back();' name='enviardoc' class='botones' id='Cancelar'>
                    </td>
            </tr>
   <?}

}
elseif ($codTx!=9){

    if($codTx==18){
          
            //Datos de acciones
            if($_SESSION["perm_acti_accion"]==1){

            //while(!$rs1->EOF){
            //Cargo acción de documentos para FFAA, se aplica en reenviados
            $sql_accion = "select accion_nombre,accion_codi,inst_codi from accion where inst_codi=".$_SESSION['inst_codi']." order by accion_codi";
            
            $rs_accion = $db->conn->Execute($sql_accion);
            //Verifico existencia de inf
            while(!$rs_accion->EOF) {
                //$acc=$rs_accion->fields["ACCION_CODI"];
                $rs_accion->MoveNext();
                $contAcc+=1;
            }

            if($contAcc > 1){
                $sqlMin="select min(accion_codi) as init ,max(accion_codi) as Fin from accion where inst_codi=".$_SESSION['inst_codi'];
                $rs_min= $db->conn->Execute($sqlMin);
                $inicio=$rs_min->fields["INIT"];
                $final=$rs_min->fields["FIN"];
            }

            $rs_accion = $db->conn->Execute($sql_accion);

            if($contAcc > 1){
                $menu_accion  = $rs_accion->GetMenu2("Accion[]", 0, false, false,25," id='Accion' class='select' size =10 onclick='selOperacion($final,$inicio);'");
            }
            
     }
    }?>
    <tr align="center">
    <? if($contAcc > 1){?>   
        <td width='80%' align='right' valign='middle'>
        <table border="0" align="center" width="100%">
        <tr>
            <td WIDTH=5%>Operaciones:</td>
            <td WIDTH=15%> <b></b><?echo $menu_accion;?></td>
            <td width='10%' align='right' valign='middle'>
                <br/>
                    <b>Comentario: &nbsp;</b>
                </td>
                <td width='65%' align='left' valign='middle'><br/>
                    <textarea id="observa" name=observa cols=70 rows=3 class=ecajasfecha onkeypress="return limita(event);"></textarea>
                    <span id="spn_numero_caracteres_disponibles"></span>
                </td>
        </tr>
        </table>
        </td>
    <? }else{ ?>    
        <tr>        
        <td align="center">
              <table border="0" align="center" width="100%">  
                  <tr><td>
                <br/>
                    <b>Comentario: &nbsp;</b>
                </td>
       
                <td width='75%' align='left' valign='middle'><br/>
                    <textarea id="observa" name=observa cols=70 rows=3 class=ecajasfecha onkeypress="return limita(event);"></textarea>
                    <span id="spn_numero_caracteres_disponibles"></span>
                </td>
        </table>
            </tr>
            <? } ?>
            <tr>
                <td  colspan="2" align='center'>
<? if ($whereFiltro !=="0") { ?>
                    <input type='button' value='Aceptar' onClick="okTx('<?=$ver?>','<?=$docExterno?>');" name='enviardoc' class='botones' id='REALIZAR'>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<? } ?>
                    <input type='button' value='Regresar' onClick='history.back();' name='enviardoc' class='botones' id='Cancelar'>
                </td>
            </tr>
<? } ?>
        </table>
    	<br />
<?
	/*  GENERACION LISTADO DE RADICADOS
	 *  Aqui utilizamos la clase adodb para generar el listado de los radicados
         *  Esta clase cuenta con una adaptacion a las clases utiilzadas de orfeo.
         *  el archivo original es adodb-pager.inc.php la modificada es adodb-paginacion.inc.php
         */

        include_once "../include/query/tx/queryFormEnvio.php";

        $pager = new ADODB_Pager($db,$isql,'adodb', false, $orderNo,$orderTipo);
        $pager->toRefLinks = $linkPagina;
        $pager->toRefVars = $encabezado;
        $pager->checkAll = true;
        $pager->checkTitulo = false;
        $pager->Render($rows_per_page=200,$linkPagina,$checkbox=chkAnulados);

?>
    </form>
</center>
</body>
</html>
