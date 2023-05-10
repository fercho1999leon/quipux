<?php


$ruta_raiz = "../..";
$ruta_raiz2= "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
//if($_SESSION["usua_admin_sistema"]!=1) die("");

require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
include_once "$ruta_raiz/funciones_interfaz.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "../ciudadanos/util_ciudadano.php";
$ciud = New Ciudadano($db);
if (isset($_GET['usr_codigo'])){
        $usr_codigo = 0 + $_GET['usr_codigo'];
        $usr_codigo = limpiar_sql($usr_codigo);
        
        if ($usr_codigo!=0){
        $datosUsrCambio=array();
        $datosUsrCambio=ObtenerDatosUsuario($usr_codigo,$db);
        $nombre=$datosUsrCambio['nombre'];
        }
}  else{ 
    $usr_codigo = 0;
    $nombre="";
}
if (isset($_GET['tabla_modificada']))
            $tabla_cod = 0+limpiar_sql($_GET['tabla_modificada']);
       
        if ($tabla_cod!='' and $usr_codigo!=''){  
            
           
             //busco el log del usuario o ciudadano
             if ($_SESSION['admin_institucion']==1){
             /*$sqlc=" select count(*) as total from 
                 log_usr_ciudadanos where usua_codi = $usr_codigo 
                     and logc_tabla_modificada in ($tabla_cod)";              
             $rsc=$db->conn->query($sqlc);
             $total = $rsc->fields['TOTAL'];*/
             //--limit 18 offset (18-3)
             $sql="select * from log_usr_ciudadanos 
                 where usua_codi = $usr_codigo and logc_tabla_modificada in ($tabla_cod)";
             $sql.=" order by logc_codi desc";
             //if ($total!='' && $total > 10)
             //$sql.= " limit $total offset ($total - 10)";
             
             }else{
                 if ($tabla_cod=='1,4'){//ciudadano_tmp y usuario
                 /*$sqlc=" select count(*) as total from 
                 log_usr_ciudadanos where usua_codi = $usr_codigo and 
                     logc_tabla_modificada in (1,4)";              
                 $rsc=$db->conn->query($sqlc);
                 $total = $rsc->fields['TOTAL'];*/
             
                 $sql="select * from log_usr_ciudadanos where usua_codi = $usr_codigo";                 
                 $sql.=" and logc_tabla_modificada in (1,4)";
                 $sql.=" order by logc_codi desc";
                    //if ($total!='' && $total > 10)
                    //$sql.= " limit $total offset ($total - 10)";
                 
                 }else{
                     /*$sqlc=" select count(*) as total from 
                 log_usr_ciudadanos where usua_codi = $usr_codigo and logc_tabla_modificada 
                     in ($tabla_cod)";              
                     $rsc=$db->conn->query($sqlc);
                 $total = $rsc->fields['TOTAL'];*/
                     $sql="select * from log_usr_ciudadanos  
                         where usua_codi = $usr_codigo";                 
                      $sql.=" and logc_tabla_modificada in ($tabla_cod)";
                      $sql.=" order by logc_codi desc";
                       //if ($total!='' && $total > 10)
                    //$sql.= " limit $total offset ($total - 10)";
                 }
             }
             //echo $sql;
             $rs=$db->conn->query($sql);
             $datosUsr=array();
             if (!$rs->EOF){
                  $html.= "<table width='100%' border='0' class='border_tab'>";
             $html.= "<tr><td colspan='5' align='center' class='titulos4'>Actualizaciones a $nombre</td></tr>";
             $html.= "<tr>
                 <td width='15%' class='titulos2'>Institución</td>
                 <td width='15%' class='titulos2'>Usuario Responsable</td>
                 <td width='15%' class='titulos2'>Fecha de Cambio</td>
                 <td width='15%' class='titulos2'>Acción</td>
                 <td width='65%' class='titulos2'>Detalle</td></tr>";
                 while(!$rs->EOF){
                        $usua_actualiza = $rs->fields['USUA_CODI_ORI'];                    
                        $datosUsr=ObtenerDatosUsuario($usua_actualiza,$db);                    
                        $log_cambio = $rs->fields['LOGC_OBSERVACION'];                    
                        $log_cambio = str_replace('<br>',' /&nbsp;',$log_cambio);
                        $institucion=$datosUsr['institucion'];
                        $nombre=$datosUsr['nombre'];
                        $fecha_cambio=$rs->fields['FECHA_CAMBIO'];  
                        $tabla=$rs->fields['LOGC_TABLA_MODIFICADA'];
                        $tipo_transaccion=$rs->fields['ID_TRANSACCION'];
                        if ($tipo_transaccion==1)
                            $descTransaccion = "Edición";
                        else
                            $descTransaccion = "Nuevo";
                        if (trim(mensajeLog($log_cambio,$tabla,$ciud))!='')
                        $html.="<tr class='listado2'><td>$institucion</td><td>".$nombre."</td><td>".substr($fecha_cambio,0,19)." ".$descZonaHoraria."</td>
                            <td>".$descTransaccion."</td>
                            <td>".mensajeLog($log_cambio,$tabla,$ciud)."</td></tr>";

                        $rs->MoveNext();
                 }
             }else
                 $html.="<table width='100%' border='0' class='border_tab'>
                     <tr><td align='center'>No Existe Registros</td></tr>";
             //foreach (=> $error) {
             $html.= "</table>";
        }else 
            return 0;
            
echo $html;
//funcion para filtrar palabras clave del log
function mensajeLog($log_cambio,$tabla,$ciud){
    $mensaje="";
    $log_cambio = str_replace('**','',$log_cambio);
    $log_cambio = str_replace('_',' ',$log_cambio);
    if ($tabla != 2)
     $arr_encontrar=array('SOL','CIU','CODIGO','USUA','CODI');
    else
        $arr_encontrar=array('USUA');
    $log_cambio = str_replace($arr_encontrar,'',$log_cambio);
    $log_cambio = str_replace('DAD','CIUDAD',$log_cambio);
    $log_cambio = str_replace('CIU CODI','CIUDAD',$log_cambio);
    
    switch ($tabla)
    {
        case '3':             
            $arr_buscar = explode(":", $log_cambio);           
            foreach ($arr_buscar as $tmp=>$value) {                
                        $mensaje.=$value;
                }
               
                break;
                default;                    
                    $mensaje=$log_cambio;
                    break;
    }
    //reemplazamos para que sea legible los datos
    $mensaje = str_replace('ESTADO de 1 a 2','ESTADO DE SOLICITUD de EDICIÓN A ENVIADO',$mensaje);
    $mensaje = str_replace('ESTADO de 2 a 0','ESTADO DE SOLICITUD de ENVIADO a RECHAZADO',$mensaje);
    $mensaje = str_replace('ESTADO de 0 a 2','ESTADO DE SOLICITUD de RECHAZADO a ENVIADO',$mensaje);
    $mensaje = str_replace('ESTADO de 2 a 3','ESTADO DE SOLICITUD de ENVIADO a AUTORIZADO',$mensaje);
    $mensaje = str_replace('ACUERDO de 1 a 0','ACUERDO DE REGISTRADO a ELIMINADO',$mensaje);
    $mensaje = str_replace('ACUERDO de 0 a 1','ACUERDO REGISTRADO',$mensaje);
    $mensaje = str_replace('ACUERDO ESTADO de 0 a 1','ACUERDO ACEPTADO',$mensaje);
    $mensaje = str_replace('ACUERDO ESTADO de 1 a 0','',$mensaje);
    $mensaje = str_replace('FIRMA de 1 a 2 /','',$mensaje);
    $mensaje = str_replace('FIRMA de 2 a 1 /','',$mensaje);    
    $mensaje = str_replace('CIUDAD 0 /','',$mensaje);
    $mensaje = str_replace('ESTADO: 1 /','',$mensaje);
    $mensaje = str_replace('INST : 2 /','',$mensaje);
    $mensaje = str_replace('NUEVO : 0 /','',$mensaje);
    $mensaje = str_replace('ESTADO: de 0 a 1 /','Se han actualizado datos temprales /',$mensaje);
    $mensaje = str_replace('ESTADO: de 1 a 0 /','Se han actualizado datos temprales /',$mensaje);
    $mensaje = str_replace('ACTUALIZA','',$mensaje);
    $mensaje = str_replace('NUEVO: 0','',$mensaje);
    $mensaje = str_replace('NUEVO: de 1 a 0','Se activa cambiar Contraseña',$mensaje);
    $mensaje = str_replace('/:','',$mensaje);     
    $mensaje = str_replace('PAIS','PAÍS',$mensaje);
    $mensaje = str_replace('CANTON','CANTÓN',$mensaje);
    $mensaje = str_replace('CEDULA','CÉDULA',$mensaje);
    $mensaje = str_replace('TITULO','TÍTULO',$mensaje);
    $mensaje = str_replace('INSTITUCION','INSTITUCIÓN',$mensaje);
    $mensaje = str_replace('TELEFONO','TELÉFONO',$mensaje);
    $mensaje = str_replace('DIRECCION','DIRECCIÓN',$mensaje);
    $mensaje = str_replace('CODI','',$mensaje);
    $mensaje = str_replace('DEPE','ÁREA',$mensaje);
    $mensaje = str_replace('ESTA: 1','',$mensaje);
    $mensaje = str_replace('ESTA: 0','',$mensaje);
    $mensaje = str_replace('ESTADO 1 -> 2','Solicitud Enviada',$mensaje);
    $mensaje = str_replace('ESTADO 0 -> 2','Solicitud Enviada',$mensaje);
    $mensaje = str_replace('ESTADO 2 -> 0','Solicitud Rechazada',$mensaje);
    $mensaje = str_replace('ESTADO: 0 -> 1','Activo',$mensaje);
    $mensaje = str_replace('ESTADO: 1 -> 0','Activo',$mensaje);
    $mensaje = str_replace('ESTA: 0 -> 1','Inactivo -> Activo',$mensaje);
    $mensaje = str_replace('ESTA: 1 -> 0','Activo -> Inactivo',$mensaje);
    $mensaje = str_replace('VISIBLE SUB: 1','',$mensaje);
    $mensaje = str_replace('VISIBLE SUB: 0','',$mensaje);
    $mensaje = str_replace('NUEVO: 1','',$mensaje);
    $mensaje = str_replace('CARGO TIPO: 0','',$mensaje);
    $mensaje = str_replace('CARGO TIPO: 1','',$mensaje);
    $mensaje = str_replace('TIPO IDENTIFICACION: 0','',$mensaje);
    $mensaje = str_replace('TIPO IDENTIFICACION: 1','',$mensaje);
    $mensaje = str_replace('-> 0','Cambio de contraseña',$mensaje);
    $posicion = strpos($mensaje,'PASW:');
    if ($posicion>0)
    $mensajePasw = substr($mensaje,$posicion,32);
    $mensaje = str_replace($mensajePasw,'',$mensaje);
    
    
    
    return $mensaje;
}


?>