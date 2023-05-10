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


$ruta_raiz = "../..";

require_once "$ruta_raiz/funciones.php";
require_once("$ruta_raiz/funciones_interfaz.php");

session_start();
include_once "$ruta_raiz/rec_session.php";
$flag_login = true;
$ciu_codigo = limpiar_sql($_SESSION["usua_codi"]);


    //array para recibir codigo de anexo guardado
    $ok = array();
    $mensaje = "";
    $band = 0;
    $subirArch="1";

    //Si es aceptacion, favorable u otro tipo de archivo
    $tipoAnexo = $_GET["tipo_anexo"];
    //Nombre del control tipo File
    $userfile = $_GET["userfile"];
    $nombreTipoAnexo = $_GET["descripcion"];
    //$firma = $_GET["firma"];
    

// Obtener el codigo anterior del ciudadano $old_codigo si la actualización se realiza desde buscar de/para
if($_GET['buscar'] == 'S')
    if(isset($old_codigo))
        $ciu_codigo = $old_codigo;

$sql = "select * from ciudadano where ciu_codigo=$ciu_codigo";
$rs = $db->conn->query($sql);
if ($rs->EOF) {
    echo html_error("No se encont&oacute; el usuario en el sistema.");
    die("");
}

//Bandera para determinar si el rgistro de solicitud de firma para ciudadano existe o no
$banExisteSol = 0;
//campos adicionales para solicitud
$recordsolicitud = array();
unset ($recordsolicitud);

//Para editar
//Consultar si el registro en la tabla solicitud_firma_ciudadano existe
$sqlSol = "select * from solicitud_firma_ciudadano where ciu_codigo = $ciu_codigo";
$rsSol = $db->conn->query($sqlSol);

if(!$rsSol->EOF){
    $recordsolicitud["SOL_CODIGO"] = $rsSol->fields["SOL_CODIGO"];
    $cedula_estado = $rsSol->fields["SOL_CEDULA"];
    $planilla_estado = $rsSol->fields["SOL_PLANILLA"];
    $acuerdo_estado = $rsSol->fields["SOL_ACUERDO"];
    $banExisteSol = 1;
}
//Si el registro existe actualiza
if($banExisteSol==1)
    $whereSol = "SOL_CODIGO";
else //Si el registro no existe inserta
    {
    $whereSol = "";
    //$recordsolicitud["SOL_ESTADO"]       = 1;
    $recordsolicitud["SOL_FIRMA"]       = 0;
    }
$recordsolicitud["CIU_CODIGO"]       =  limpiar_sql($ciu_codigo);


        //arch_com_codi primary key del anexo
        $codigo_arch = $_POST["hd_id_".$userfile];

        //accion Insertar o Eliminar archivo
        $accion = $_POST["hd_accion_".$userfile];

        //$visible_enviar =0;
    if (isset($_GET))        
        $codigoCiuAr=trim(limpiar_sql($_GET["codigo"]));
    if($accion==0)
    {
        if($userfile == 'cedula')
            {
        unlink("$ruta_raiz/bodega/ciudadanos/".$codigoCiuAr.'_cedula.pdf');        
        //$recordsolicitud["SOL_ESTADO"]       = 1;
        $recordsolicitud["SOL_CEDULA"]       = $_POST["hd_accion_cedula"];
        }
        else if($userfile == 'planilla')
            {
        unlink("$ruta_raiz/bodega/ciudadanos/".$codigoCiuAr.'_planilla.pdf');
        //$recordsolicitud["SOL_ESTADO"]       = 1;
        $recordsolicitud["SOL_PLANILLA"]       = $_POST["hd_accion_planilla"];
        }
        else if($userfile == 'acuerdo')
            {
        unlink("$ruta_raiz/bodega/ciudadanos/".$codigoCiuAr.'_acuerdo.pdf.p7m');
        //unlink("$ruta_raiz/bodega/ciudadanos/".$codigoCiuAr.'_acuerdo.pdf');
        //$recordsolicitud["SOL_ESTADO"]       = 1;
        $recordsolicitud["SOL_ACUERDO"]       = $_POST["hd_accion_acuerdo"];        
        }
    }
    else
      {
    if($_FILES['cedula']!= NULL)
    {
        $solicitud_cedula  = $_FILES['cedula']['tmp_name'];
        $archivo_cedula = "$ruta_raiz/bodega/ciudadanos/".$codigoCiuAr.'_cedula.pdf';
	$bien1 = move_uploaded_file($solicitud_cedula,$archivo_cedula);	//Grabamos el archivo
        $nomb_arch = $codigoCiuAr.'_cedula.pdf';
        if($planilla_estado == 1 && $acuerdo_estado == 1)
            $visible_enviar      = 1;

        $recordsolicitud["SOL_CEDULA"]       = $_POST["hd_accion_cedula"];

        $recordsolicitud["CIU_NOMBRE"]       = $db->conn->qstr(limpiar_sql(trim($_POST["nombre_usu_cedula"])));
        $recordsolicitud["CIU_CEDULA"]       = $db->conn->qstr(limpiar_sql(trim($_POST["cedula_usu_cedula"])));
        $recordsolicitud["CIU_DOCUMENTO"]    = $db->conn->qstr(limpiar_sql(trim($_POST["documento_usu_cedula"])));
        $recordsolicitud["CIU_APELLIDO"]     = $db->conn->qstr(limpiar_sql(trim($_POST["apellido_usu_cedula"])));
        $recordsolicitud["CIU_TITULO"]       = $db->conn->qstr(limpiar_sql(trim($_POST["titulo_usu_cedula"])));
        $recordsolicitud["CIU_ABR_TITULO"]   = $db->conn->qstr(limpiar_sql(trim($_POST["abr_titulo_usu_cedula"])));
        $recordsolicitud["CIU_EMPRESA"]      = $db->conn->qstr(limpiar_sql(trim($_POST["empresa_usu_cedula"])));
        $recordsolicitud["CIU_CARGO"]        = $db->conn->qstr(limpiar_sql(trim($_POST["cargo_usu_cedula"])));
        $recordsolicitud["CIU_DIRECCION"]    = $db->conn->qstr(limpiar_sql(trim($_POST["direccion_usu_cedula"])));
        $recordsolicitud["CIU_EMAIL"]        = $db->conn->qstr(limpiar_sql(trim($_POST["mail_usu_cedula"])));
        $recordsolicitud["CIU_TELEFONO"]     = $db->conn->qstr(limpiar_sql(trim($_POST["telefono_usu_cedula"])));
        $recordsolicitud["SOL_OBSERVACIONES"]     = $db->conn->qstr(limpiar_sql(trim($_POST["observaciones_usu_cedula"])));
        $recordsolicitud["SOL_FIRMA"]     = $db->conn->qstr(limpiar_sql(trim($_POST["firma_usu_cedula"])));
        $recordsolicitud["CIUDAD_CODI"]     = $db->conn->qstr(limpiar_sql(trim($_POST["ciudad_usu_cedula"])));
    }
        
    if($_FILES['planilla']!= NULL)
    {

        $solicitud_planilla  = $_FILES['planilla']['tmp_name'];
        $archivo_planilla = "$ruta_raiz/bodega/ciudadanos/".$codigoCiuAr.'_planilla.pdf';
        $bien2 = move_uploaded_file($solicitud_planilla,$archivo_planilla);
        $nomb_arch = $codigoCiuAr.'_planilla.pdf';
        if($cedula_estado == 1 && $acuerdo_estado == 1)
            $visible_enviar      = 1;

        $recordsolicitud["SOL_PLANILLA"]       = $_POST["hd_accion_planilla"];

        $recordsolicitud["CIU_NOMBRE"]       = $db->conn->qstr(limpiar_sql(trim($_POST["nombre_usu_planilla"])));
        $recordsolicitud["CIU_CEDULA"]       = $db->conn->qstr(limpiar_sql(trim($_POST["cedula_usu_planilla"])));
        $recordsolicitud["CIU_DOCUMENTO"]    = $db->conn->qstr(limpiar_sql(trim($_POST["documento_usu_planilla"])));
        $recordsolicitud["CIU_APELLIDO"]     = "initcap(".$db->conn->qstr(limpiar_sql(trim($_POST["apellido_usu_planilla"]))).")";
        $recordsolicitud["CIU_TITULO"]       = $db->conn->qstr(limpiar_sql(trim($_POST["titulo_usu_planilla"])));
        $recordsolicitud["CIU_ABR_TITULO"]   = $db->conn->qstr(limpiar_sql(trim($_POST["abr_titulo_usu_planilla"])));
        $recordsolicitud["CIU_EMPRESA"]      = $db->conn->qstr(limpiar_sql(trim($_POST["empresa_usu_planilla"])));
        $recordsolicitud["CIU_CARGO"]        = $db->conn->qstr(limpiar_sql(trim($_POST["cargo_usu_planilla"])));
        $recordsolicitud["CIU_DIRECCION"]    = $db->conn->qstr(limpiar_sql(trim($_POST["direccion_usu_planilla"])));
        $recordsolicitud["CIU_EMAIL"]        = $db->conn->qstr(limpiar_sql(trim($_POST["mail_usu_planilla"])));
        $recordsolicitud["CIU_TELEFONO"]     = $db->conn->qstr(limpiar_sql(trim($_POST["telefono_usu_planilla"])));
        $recordsolicitud["SOL_OBSERVACIONES"]     = $db->conn->qstr(limpiar_sql(trim($_POST["observaciones_usu_planilla"])));
        $recordsolicitud["SOL_FIRMA"]     = $db->conn->qstr(limpiar_sql(trim($_POST["firma_usu_planilla"])));
        $recordsolicitud["CIUDAD_CODI"]     = $db->conn->qstr(limpiar_sql(trim($_POST["ciudad_usu_planilla"])));

    }

    if($_FILES['acuerdo']!= NULL)
    {
        $tamanio = array();
        $tamanio = $_FILES['acuerdo']['size'];
        
        $solicitud_acuerdo  = $_FILES["acuerdo"]['tmp_name'];
        $archivo_acuerdo = "$ruta_raiz/bodega/ciudadanos/".$codigoCiuAr.'_acuerdo.pdf.p7m';
        if(is_file($solicitud_acuerdo))
            if ($tamanio>0){
             $bien3 = move_uploaded_file($solicitud_acuerdo,$archivo_acuerdo);
        $path_descarga = "$ruta_raiz/bodega/ciudadanos/";

//        $archivo_acuerdo_pdf = "$ruta_raiz/bodega/ciudadanos/".$codigoCiuAr.'_acuerdo.pdf';
//        copy($ruta_raiz."/bodega/ciudadanos/", $archivo_acuerdo_pdf);
        //if(is_file($solicitud_acuerdo))
          //  $bien4 = move_uploaded_file($solicitud_acuerdo,$archivo_acuerdo_pdf);

        //Copiar el archivo .p7m como pdf para poder visualizar, copia mediante ejecución de comandos
//        if(is_file($archivo_acuerdo))
//        {
//            $cmd = "cp $ruta_raiz/bodega/ciudadanos/".$codigoCiuAr."_acuerdo.pdf.p7m $ruta_raiz/bodega/ciudadanos/".$_GET["codigo"].'_acuerdo.pdf';
//            exec($cmd,$salida);
//        }

        $nomb_arch = $codigoCiuAr.'_acuerdo.pdf.p7m';
        if($planilla_estado == 1 && $cedula_estado == 1)
            $visible_enviar = 1;

        $recordsolicitud["SOL_ACUERDO"] = $_POST["hd_accion_acuerdo"];

        $recordsolicitud["CIU_NOMBRE"]       = $db->conn->qstr(limpiar_sql(trim($_POST["nombre_usu_acuerdo"])));
        $recordsolicitud["CIU_CEDULA"]       = $db->conn->qstr(limpiar_sql(trim($_POST["cedula_usu_acuerdo"])));
        $recordsolicitud["CIU_DOCUMENTO"]    = $db->conn->qstr(limpiar_sql(trim($_POST["documento_usu_acuerdo"])));
        $recordsolicitud["CIU_APELLIDO"]     = "initcap(".$db->conn->qstr(limpiar_sql(trim($_POST["apellido_usu_acuerdo"]))).")";
        $recordsolicitud["CIU_TITULO"]       = $db->conn->qstr(limpiar_sql(trim($_POST["titulo_usu_acuerdo"])));
        $recordsolicitud["CIU_ABR_TITULO"]   = $db->conn->qstr(limpiar_sql(trim($_POST["abr_titulo_usu_acuerdo"])));
        $recordsolicitud["CIU_EMPRESA"]      = $db->conn->qstr(limpiar_sql(trim($_POST["empresa_usu_acuerdo"])));
        $recordsolicitud["CIU_CARGO"]        = $db->conn->qstr(limpiar_sql(trim($_POST["cargo_usu_acuerdo"])));
        $recordsolicitud["CIU_DIRECCION"]    = $db->conn->qstr(limpiar_sql(trim($_POST["direccion_usu_acuerdo"])));
        $recordsolicitud["CIU_EMAIL"]        = $db->conn->qstr(limpiar_sql(trim($_POST["mail_usu_acuerdo"])));
        $recordsolicitud["CIU_TELEFONO"]     = $db->conn->qstr(limpiar_sql(trim($_POST["telefono_usu_acuerdo"])));
        $recordsolicitud["SOL_OBSERVACIONES"]     = $db->conn->qstr(limpiar_sql(trim($_POST["observaciones_usu_acuerdo"])));
        $recordsolicitud["SOL_FIRMA"]     = $db->conn->qstr(limpiar_sql(trim($_POST["firma_usu_acuerdo"])));
        $recordsolicitud["CIUDAD_CODI"]     = $db->conn->qstr(limpiar_sql(trim($_POST["ciudad_usu_acuerdo"])));
         $url = "$ruta_raiz/bodega/ciudadanos/".$ciu_codigo."_acuerdo.pdf.p7m";
         if (is_file($url))
        $db->conn->Replace("solicitud_firma_ciudadano", $recordsolicitud, $whereSol, false,false,false,false);
            }
        }

     }

    //Insertar o actualizar registro


//die();
      
       
       if (is_file($userfile))
         $nombre_archivo = "<table width='100%' align='center' border='1' class='t_bordeGris'><tr><td class='titulos3' width='13%'>$nombreTipoAnexo</td><td><a href='#' class='vinculos'>$nomb_arch</a>&nbsp;&nbsp;&nbsp;<img src='$ruta_raiz/imagenes/close_button.gif' width='20' height='18' onClick=\\\"anexo_borrar_archivo('$userfile');\\\" title='Eliminar Archivo' style='border: 0px solid gray;cursor:pointer;' alt='Eliminar Archivo'></td></tr></table>";
       else
           $nombre_archivo = "<table width='100%' align='center' border='1' class='t_bordeGris'><tr><td class='titulos2' width='11%'>* Acuerdo: </td><td class='listado3'><font color='black'>Error al subir archivo</font> <br> Intentar nuevamente &nbsp;<a href='$ruta_raiz/Administracion/usuarios/adm_solicitud.php' class='vinculos'>aquí</a></td></tr></table>";
       

        if($userfile == 'cedula')
            $nombre = $ciu_codigo."_cedula.pdf";
        else if($userfile == 'planilla')
            $nombre = $ciu_codigo."_planilla.pdf";
        else if($userfile == 'acuerdo')
        {
            //$nombre = $ciu_codigo."_acuerdo.pdf.p7m";
            
            $subirArch="1";
            $nombre = $ciu_codigo.'_acuerdo.pdf';
            $nombre_desc = $ciu_codigo.'_acuerdo.pdf.p7m';
            $nombre_firma = "/ciudadanos/".$ciu_codigo."_acuerdo.pdf.p7m";
            $path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=/ciudadanos/$nombre_desc&nomb_arch=$nombre";
           
     
            //compruebo si hay path
            if (is_file($url)){//url
     
             $nombre_archivo = "<table width='100%' align='center' border='1' class='t_bordeGris'><tr><td class='titulos3' width='13%'>* Acuerdo</td><td>";
             $nombre_archivo .= "<a href=\\\"javascript:window.open('$path_descarga','_self','');\\\" class='vinculos'>$nombre</a>&nbsp;&nbsp;&nbsp;&nbsp;";
             $nombre_archivo .= "<a href='javascript:;' onclick=\\\"verificar_firma('$nombre_firma','$nombre');\\\" class='vinculos'>Verificar Firma</a>";
             $nombre_archivo .= "&nbsp;&nbsp;&nbsp;<img src='$ruta_raiz/imagenes/close_button.gif' width='20' height='18' onClick=\\\"anexo_borrar_archivo('acuerdo');\\\" title='Eliminar Archivo' style='border: 0px solid gray;cursor:pointer;' alt='Eliminar Archivo'>";
             
             $nombre_archivo .= "</td></tr></table>";  
            }
        }
 else {
     
        $path_descarga = "$ruta_raiz/archivo_descargar.php?path_arch=/ciudadanos/$nombre&nomb_arch=$nombre";
         $nombre_archivo = "<table width='100%' align='center' border='1' class='t_bordeGris'><tr><td class='titulos3' width='13%'>$nombreTipoAnexo</td><td>";
         $nombre_archivo .= "<a href=\\\"javascript:window.open('$path_descarga','_self','');\\\" class='vinculos'>$nomb_arch</a>";
         $nombre_archivo .= "&nbsp;&nbsp;&nbsp;<img src='$ruta_raiz/imagenes/close_button.gif' width='20' height='18' onClick=\\\"anexo_borrar_archivo('$userfile');\\\" title='Eliminar Archivo' style='border: 0px solid gray;cursor:pointer;' alt='Eliminar Archivo'>";
         $nombre_archivo .= "</td></tr></table>";
      }
         

         echo "<script>
            window.top.window.mainFrame.anexo_respuesta_archivo('$userfile', \"$nombre_archivo\",\"$codArch\",\"$subirArch\");
            window.top.window.mainFrame.visible_enviar('$visible_enviar');
          </script>";
         
         
         

?>

