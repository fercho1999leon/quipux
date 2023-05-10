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

//BUSCAR LOG PERMISOS DEL USUARIO
$sql="select id_transaccion,id_permiso,usua_codi,usua_codi_actualiza,fecha_actualiza,accion from log_usr_permisos 
                where usua_codi = $usr_codigo and usua_codi <> 0 order by 1 desc limit 100 offset 0";
//echo $sql;
            $rs=$db->conn->query($sql);
if (!$rs->EOF){
            $html.= "<table width='100%' border='0' class='border_tab'>";
            $html.= "<tr><td colspan='4' align='center' class='titulos4'>Permisos Modificados a $nombre</td></tr>";
            $html.= "<tr><td width='15%' class='titulos2'>Institución</td>
                <td width='15%' class='titulos2'>Usuario Responsable</td>
                 <td width='15%' class='titulos2'>Fecha de Cambio</td>
                 <td width='65%' class='titulos2'>Detalle</td></tr>";
    while ($rs && !$rs->EOF) {
        $usua_codi_actualiza = $rs->fields["USUA_CODI_ACTUALIZA"];
        $datosUsrCambio=ObtenerDatosUsuario($usua_codi_actualiza,$db);
        $nombre=$datosUsrCambio['nombre'];
        $institucion=$datosUsrCambio['institucion'];
        $accion = $rs->fields["ACCION"];       
        if ($rs->fields["ID_PERMISO"]==0)
            $acciondesc = "Se reinicia la contraseña";
        else{
            if ($accion==1){
                $acciondesc = "Se Agrega el Permiso:";
                $color = "class='listado2'";
            }else{
                $acciondesc = "Se Elimina el Permiso: ";
                $color = "class='listado2'";//$color = "bgcolor = #CEE3F6";
            }
         }
        $html.="<tr $color><td width='15%' >$institucion</td>
            <td width='15%' >$nombre</td>";
        $fecha_cambio = $rs->fields["FECHA_ACTUALIZA"];
        $html.="<td width='15%' >".substr($fecha_cambio,0,19)." ".$descZonaHoraria."</td>";

        $id_permiso = $rs->fields["ID_PERMISO"];

        $nombrePermiso = $ciud->nombrePermiso($id_permiso);

        $html.="<td >$acciondesc $nombrePermiso</td></tr>";
        $rs->MoveNext();
    }
}else
    $html.="<table width='100%' border='0' class='border_tab'>
        <tr align='center'><td><font color='black'>No Existe Registros</font></td></tr>";
$html.="</table>";
echo $html;
?>