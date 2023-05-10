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
/* FUNCION RECURSIVA QUE PERMITE OBTENER LA DIRECCION FISICA COMPLETA DE UN EXPEDIENTE
	$item	:	Codigo del item fisico
	$db	:	Coneccion con la BDD
	$campo	:	"C" Despliega los Codigos, "N" Despliega los nombres, "S" Despliega las siglas
	$separador:	Despliega un separador especifico, util para desplegar en tablas
*/
$ruta_raiz = isset($ruta_raiz) ? $ruta_raiz : "..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

  function ObtenerNombreCompletoTRD($item,$db,$campo="N",$separador=" - ")
  {

    if ($item==0) return 0;
    $dato= "TRD_CODI";
    if ($campo=="N") $dato= "TRD_NOMBRE";
    $sql = "select trd_padre, $dato from trd where trd_codi=$item";
    $rs=$db->conn->query($sql);
    $codigo=$rs->fields["TRD_PADRE"];
    $dato=$rs->fields["$dato"];
    if ($codigo==0)
	return $dato;
    else
        $resp=ObtenerNombreCompletoTRD($codigo,$db,$campo,$separador).$separador.$dato;
    return $resp;

  }


/*  Función para consultar datos de una tabla recursiva (LT)
    $db:          Conección con la BDD
    $depe_actu:   Código de la dependencia
    $es_consulta: Determina si ejecuta el query para consulta o edición
    $lista:       Retorna una lista con los datos consultados
*/
 function ConsultarCarpetaVirtual($db, $depe_actu, $es_consulta)
 {
    //Se consulta datos de tabla de carpetas virtuales
    if($es_consulta == 1)
        $consulta = "select * from trd
        left outer join (select distinct trd_padre as trd_padre_cv from trd where depe_codi = $depe_actu and trd_estado <> 2) as cv on trd.trd_codi = cv.trd_padre_cv
        where depe_codi=$depe_actu and trd_estado <> 2
        order by trd_padre, trd_nivel, trd_nombre";
    else
        $consulta = "select * from trd
        left outer join (select trd_codi as trd_codi_doc, count(1) as trd_cant_doc from trd_radicado where depe_codi = $depe_actu group by 1) as doc on trd.trd_codi = doc.trd_codi_doc
        left outer join (select distinct trd_padre as trd_padre_cv from trd where depe_codi = $depe_actu and trd_estado <> 2) as cv on trd.trd_codi = cv.trd_padre_cv
        where depe_codi=$depe_actu and trd_estado <> 2
        order by trd_padre, trd_nivel, trd_nombre";

    $rs=$db->conn->query($consulta);
    $i=0;
    $lista = array();
    $estado = "";

    //Se arma el listado
    while (!$rs->EOF) {
        $lista[$i]["codigo"] = $rs->fields["TRD_CODI"];
        $lista[$i]["codigo_padre"] = $rs->fields["TRD_PADRE"];
        $lista[$i]["nombre"] = $rs->fields["TRD_NOMBRE"];
        $lista[$i]["nivel"] = $rs->fields["TRD_NIVEL"];
        if($rs->fields["TRD_CODI_DOC"] != "") $tiene_doc = 1; else $tiene_doc = 0;
        if($rs->fields["TRD_PADRE_CV"] != "") $tiene_carpeta = 1; else $tiene_carpeta = 0;
        $lista[$i]["tiene_doc"] = $tiene_doc;
        $lista[$i]["tiene_carpeta"] = $tiene_carpeta;
        //echo "</br> db".$rs->fields["TRD_CODI_DOC"]. " - doc:". $lista[$i]["tiene_doc"] . " - cv:". $lista[$i]["tiene_carpeta"];
        if ($rs->fields["TRD_ESTADO"]==1)
                $estado="Activo";
        else if($rs->fields["TRD_ESTADO"]==0)
            $estado="Inactivo";
        else if($rs->fields["TRD_ESTADO"]==2)
            $estado="Eliminado";
        $lista[$i]["estado"] = $estado;
        $lista[$i]["estado_valor"] = $rs->fields["TRD_ESTADO"];
        $lista[$i]["arch_gestion"] = $rs->fields["TRD_ARCH_GESTION"];
        $lista[$i]["arch_central"] = $rs->fields["TRD_ARCH_CENTRAL"];
        $lista[$i]["cant_doc"] = $rs->fields["TRD_CANT_DOC"];        
	$rs->MoveNext();
        $i++;
    }
    return $lista;
}

/*  Función recursiva para armar un árbol según niveles y editar sus datos (LT)
    $listaDatos: Lista con los datos
    $codigo:     Valor del código de cada dato
    $ruta_raiz:  Ruta de archivos
    $oculta:     Determina si se visualiza el árbol expandido
    $nombre_completo: Variable que maneja el nombre completo de la capeta y subcarpetas
*/
function ArmarArbolCarpetaVirtual($listaDatos, $codigo, $ruta_raiz, $ocultar="S", $nombre_completo= "", $accion="") {

    $tam = sizeof($listaDatos);
    if($ocultar=="S")
        $imgCarpeta = "agregar.png";
    else
        $imgCarpeta = "quitar.png";
    
    for($i = 0; $i < $tam; $i++){

        if(($listaDatos[$i]["codigo_padre"] == $codigo))
        {
            $cod = $listaDatos[$i]["codigo"];
            $cod_padre = $listaDatos[$i]["codigo_padre"];
            $nombre = $listaDatos[$i]["nombre"];
            $nivel = $listaDatos[$i]["nivel"];           
            $estado = $listaDatos[$i]["estado"];
            $estado_valor = $listaDatos[$i]["estado_valor"];
            $espacio = str_repeat("&nbsp;", $nivel*10);
            $imagen = "";
            $arch_gestion = $listaDatos[$i]["arch_gestion"];
            $arch_central = $listaDatos[$i]["arch_central"];
            $nombre_c= "$nombre_completo - $nombre";
            $tiene_doc = $listaDatos[$i]["tiene_doc"];
            $tiene_carpeta = $listaDatos[$i]["tiene_carpeta"];
            $cant_doc = $listaDatos[$i]["cant_doc"];
            
            $borrar = 1;
            if($tiene_doc==1 or $tiene_carpeta==1)
                $borrar = 0;
            //Datos a modificar
            $tmp_ed = "$cod,'$nombre',$arch_gestion, $arch_central, $estado_valor, $nivel, $borrar, '$nombre_c'";
            //Datos a crear
            $tmp_cr = "$cod,$nivel,'$nombre_c'";
            $valor_accion = "";
            
            if($accion == "Editar"){
                $accion_editar ="<td class='listado2' colspan='2' width='10%' align='center'><a href='#' onClick=\"EditarItem($tmp_ed)\" class='vinculos'>Editar</a></td>";
                if($tiene_doc==0 and $estado_valor == 1)
                    $valor_accion = "<a href='javascript:;' onClick=\"CrearItem($tmp_cr)\" class='vinculos'>Crear</a>";
            }
            else if($accion == "Seleccionar"){   //Selección de carpeta virtual para asignar documento
                if($tiene_carpeta!=1 and $estado_valor == 1){
                    $nombre_c = substr($nombre_c, 2);
                    $seleccion = "$cod, '$nombre_c'";
                    $valor_accion = "<a href='javascript:;' onClick=\"SeleccionarTRD($seleccion)\">Seleccionar</a>";
                    $accion_editar = "";                    
                }
            }
            else if($accion == "SeleccionarDoc"){ //Consulta de documentos por carpeta virtual
                if($tiene_carpeta!=1){
                    $nombre_c = substr($nombre_c, 2);
                    $seleccion = "$cod, '$nombre_c'";
                    if($cant_doc == 0) $cant_doc=0;
                    $valor_accion = "<a href='javascript:;' onClick=\"SeleccionarTRD($seleccion)\" style='color: blue;'>Seleccionar ($cant_doc)</a>";
                    $accion_editar = "";
                }
            }

            //Indica si la carpeta tiene subcarpetas
            if($tiene_carpeta==1){
                $imagen = "<a href='javascript:;' onClick=\"MostrarFila('tr_$cod','$ruta_raiz');\"><spam id='spam_tr_$cod'><img src='$ruta_raiz/imagenes/$imgCarpeta' border='0' height='15px' width='15px'></spam></a>";
            }

            if($nivel > 0){
                if ($ocultar=="N") $ver_fila="";
                else $ver_fila="style='display:none'";
            }           
            $fila_datos = "<tr name='tr_$cod_padre' id='tr_$cod' $ver_fila>
                        <td class='listado2' width='60%'>$espacio $imagen $nombre</td>
                        <td class='listado2' width='15%' align='center'>".$estado."</td>
                        $accion_editar
                        <td class='listado2' width='15%' align='center'>$valor_accion</td>
                      </tr>";
            
            if($accion == "Seleccionar" and $estado_valor==1){
                echo $fila_datos;
            }
            else if($accion == "Editar" or $accion == "SeleccionarDoc" or $accion == "Consultar")
                echo $fila_datos;

            ArmarArbolCarpetaVirtual($listaDatos,$cod, "..", $ocultar, $nombre_c,$accion);
        }
    }
}

/*  FUNCION RECURSIVA QUE PERMITE MODIFICAR EL ESTADO DE UNA CARPETA VIRTUAL (LT)
    $cod:    Código de la carpeta virtual
    $estado: Estado de la carpeta virtual
    $db:     Coneccion con la BDD
*/
function ModificarEstadoTRD($cod, $estado, $db)
{
    if ($cod==0) return;
    
    if($estado==1) //Activa carpeta virtual
        $sql = "trd_estado=1, trd_fecha_hasta=null";
    else if($estado==0){ //Inactiva carpeta virtual
        $fecha = $db->conn->sysTimeStamp;
        $sql = "trd_estado=0, trd_fecha_hasta=$fecha";
    }
    else if($estado==2){ //Elimina carpeta virtual
        $fecha = $db->conn->sysTimeStamp;
        $sql = "trd_estado=2, trd_fecha_hasta=$fecha";
    }
    $sql = "update trd set $sql where trd_codi=$cod";
    $db->conn->Execute($sql);

    //Consulta subcarpetas
    $sql = "select trd_codi, trd_padre from trd where trd_padre=$cod and trd_estado <> 2";
    $rs=$db->conn->query($sql);

    //Actualiza estado de carpetas hijas con método recursivo
    foreach ($rs as $carpeta)
    {
        $codigo=$carpeta['TRD_CODI'];
        ModificarEstadoTRD($codigo, $estado, $db);
    }
    return;
}
//funcion radio button carpeta
function ArmarArbolCarpetaVirtualRb($listaDatos, $codigo, $ruta_raiz, $ocultar="S", $nombre_completo= "", $accion="") {

    $tam = sizeof($listaDatos);
    if($ocultar=="S")
        $imgCarpeta = "agregar.png";
    else
        $imgCarpeta = "quitar.png";
    
    for($i = 0; $i < $tam; $i++){

        if(($listaDatos[$i]["codigo_padre"] == $codigo))
        {
            $cod = $listaDatos[$i]["codigo"];
            $cod_padre = $listaDatos[$i]["codigo_padre"];
            $nombre = $listaDatos[$i]["nombre"];
            $nivel = $listaDatos[$i]["nivel"];           
            $estado = $listaDatos[$i]["estado"];
            $estado_valor = $listaDatos[$i]["estado_valor"];
            $espacio = str_repeat("&nbsp;", $nivel*10);
            $imagen = "";
            $arch_gestion = $listaDatos[$i]["arch_gestion"];
            $arch_central = $listaDatos[$i]["arch_central"];
            $nombre_c= "$nombre_completo - $nombre";
            $tiene_doc = $listaDatos[$i]["tiene_doc"];
            $tiene_carpeta = $listaDatos[$i]["tiene_carpeta"];
            $cant_doc = $listaDatos[$i]["cant_doc"];
            
            $borrar = 1;
            if($tiene_doc==1 or $tiene_carpeta==1)
                $borrar = 0;
            //Datos a modificar
            $tmp_ed = "$cod,'$nombre',$arch_gestion, $arch_central, $estado_valor, $nivel, $borrar, '$nombre_c'";
            //Datos a crear
            $tmp_cr = "$cod,$nivel,'$nombre_c'";
            $valor_accion = "";
            
            if($accion == "Seleccionar"){   //Selección de carpeta virtual para asignar documento
                if($tiene_carpeta!=1 and $estado_valor == 1){
                    $nombre_c = substr($nombre_c, 2);
                    $seleccion = "$cod, '$nombre_c'";
                    
                    $valor_accion = " <input type='radio' name='check_carpeta' id='check_carpeta' value='$cod'><br>";
                    $accion_editar = "";                    
                }
            }
            

            //Indica si la carpeta tiene subcarpetas
            if($tiene_carpeta==1){
                $imagen = "<a href='javascript:;' onClick=\"MostrarFila('tr_$cod','$ruta_raiz');\"><spam id='spam_tr_$cod'><img src='$ruta_raiz/imagenes/$imgCarpeta' border='0' height='15px' width='15px'></spam></a>";
            }

            if($nivel > 0){
                if ($ocultar=="N") $ver_fila="";
                else $ver_fila="style='display:none'";
            }           
            $fila_datos = "<tr name='tr_$cod_padre' id='tr_$cod' $ver_fila>
                        <td class='listado2' width='60%'>$espacio $imagen $nombre</td>
                        <td class='listado2' width='15%' align='center'>".$estado."</td>
                        $accion_editar
                        <td class='listado2' width='15%' align='center'>$valor_accion</td>
                      </tr>";
            
            if($accion == "Seleccionar" and $estado_valor==1){
                
                echo $fila_datos;
            }
            ArmarArbolCarpetaVirtualRb($listaDatos,$cod, "..", $ocultar, $nombre_c,$accion);
        }
    }
}
/*
 * Devolver sql
 * 
 * 
 */
function SqlCarpetaVirtual($db, $depe_actu, $es_consulta)
 {
    //Se consulta datos de tabla de carpetas virtuales
    
        $consulta = "select * from trd
        left outer join (select distinct trd_padre as trd_padre_cv from trd where depe_codi = $depe_actu and trd_estado <> 0) as cv on trd.trd_codi = cv.trd_padre_cv
        where depe_codi=$depe_actu and trd_estado <> 0";
    return $consulta;

    
}
//**//

///* FUNCION RECURSIVA QUE PERMITE OBTENER LA DIRECCION FISICA COMPLETA DE UN EXPEDIENTE
//	$item	:	Codigo del item fisico
//	$db	:	Coneccion con la BDD
//	$campo	:	"C" Despliega los Codigos, "N" Despliega los nombres, "S" Despliega las siglas
//	$separador:	Despliega un separador especifico, util para desplegar en tablas
//*/
//  function ArbolModificarTRD($item, $nivel, $nivel_act, $dependencia, $nombre, $db, $ruta_raiz="..")
//  {
//
//    if ($nivel<=0) return "";
//    $sql = "select trd_nombre from trd_nivel where trd_codi>=$nivel_act and depe_codi=$dependencia order by trd_codi asc";
//    $rs=$db->conn->query($sql);
//    $nom_nivel = $rs->fields["TRD_NOMBRE"];
//    $rs->MoveNext();
//    if ($rs->EOF) $nom_nivel2=""; else $nom_nivel2 = $rs->fields["TRD_NOMBRE"];
//
//    $sql = "select * from trd where trd_padre=$item and depe_codi=$dependencia order by trd_nombre asc";
////echo $sql;
//    $rs=$db->conn->query($sql);
//    $resp = "";//<tr class='listado5'>";
//    while (!$rs->EOF) {
//	$codi = $rs->fields["TRD_CODI"];
//	$nomb = $rs->fields["TRD_NOMBRE"];
//	$arch1 = $rs->fields["TRD_ARCH_GESTION"];
//	$arch2 = $rs->fields["TRD_ARCH_CENTRAL"];
//	if ($nivel>1) $esta = 2; else $esta = $rs->fields["TRD_ESTADO"];
//
//    	$sql = "select t1+t2 as borrar from (select count(trd_codi) as t1 from trd where trd_padre=$codi) as a,
//	    	(select count(trd_codi) as t2 from trd_radicado where trd_codi=$codi) as b";
//    	$rs2=$db->conn->query($sql);
//    	$borrar = $rs2->fields["BORRAR"];
//
//	$nombre2 = "$nombre - $nomb";
//	$tmp_ed = "$codi,'$nomb',$arch1, $arch2, $esta, $nivel_act, '$nom_nivel' ,$borrar ,'$nombre2'";
//	$tmp_cr = "$codi,$nivel_act, '$nom_nivel2','$nombre2'";
//	$tamanio = ($nivel_act+1)*3;
//
//	$resp .= "<tr><td class='listado2' align='right' width='".$tamanio."%'>";
//	if ($nivel>1)
//	    $resp .= "<a href='javascript:;' onClick=\"MostrarFila('tr_$codi');\">
//			<img src='$ruta_raiz/imagenes/add.gif' border='0' alt='' height='15px' width='15px'></a>";
//	$resp .= "</td>";
//	$resp .= "<td class='listado2' width='". (60-$tamanio) ."%'>$nomb</td>";
//	$resp .= "<td class='listado2' width='15%'>".strtolower($nom_nivel)."</td>";
//	$resp .= "<td class='listado2' width='10%'><a href='#' onClick=\"EditarItem($tmp_ed)\" class='vinculos'>Editar</a></td>";
//	if ($nivel>1)
//	    $resp .= "<td class='listado2' width='15%'><a href='javascript:;' onClick=\"CrearItem($tmp_cr)\" class='vinculos'>Crear ".strtolower($nom_nivel2)."</a></td>";
//	else
//	    $resp .= "<td class='listado2' width='15%'>&nbsp;</td>";
//	$resp .= "</tr>";
//	if ($nivel>1) {
//	    $resp .= "<tr name='tr_$codi' id='tr_$codi' style='display:none'><td colspan='6'><table border='0' width='100%'>";
//	    $resp .= ArbolModificarTRD($codi, $nivel-1, $nivel_act+1, $dependencia, $nombre2, $db, $ruta_raiz);
//	    $resp .= "</table></td></tr>";
//	}
//	$rs->MoveNext();
//    }
//    return $resp;
//  }
//  
///* FUNCION RECURSIVA QUE PERMITE OBTENER LA DIRECCION FISICA COMPLETA DE UN EXPEDIENTE
//	$item	:	Codigo del item fisico
//	$db	:	Coneccion con la BDD
//	$campo	:	"C" Despliega los Codigos, "N" Despliega los nombres, "S" Despliega las siglas
//	$separador:	Despliega un separador especifico, util para desplegar en tablas
//*/
//
//  function ArbolSeleccionarTRD($item, $nivel, $nivel_act, $dependencia, $nombre, $db, $ruta_raiz="..",
//				$accion="L", $campo="T", $campo_valor=0, $id=0, $ocultar="S")
//  {
//
//    if ($nivel<=0) return "";
//    $sql = "select trd_nombre from trd_nivel where trd_codi=$nivel_act and depe_codi=$dependencia";
//    $rs=$db->conn->query($sql);
//    $nom_nivel = $rs->fields["TRD_NOMBRE"];
//
//    if ($ocultar=="N") $ver_fila=""; else $ver_fila="style='display:none'";
//    $tamanio1 = 85;
//    if ($accion=="S") $tamanio1 = 70;
//    if ($campo=="T") $tamanio1 -= 15;
//    $sql = "";
//    if ($campo=="E") $sql .= " and trd_estado=$campo_valor ";
//    if ($campo=="O") $sql .= " and trd_ocupado=$campo_valor ";
//    $sql = "select * from trd where trd_padre=$item and depe_codi=$dependencia $sql order by trd_nombre asc";
////echo $sql;
//    $rs=$db->conn->query($sql);
//    $resp = "";//<tr class='listado5'>";
//    while (!$rs->EOF) {
//	$codi = $rs->fields["TRD_CODI"];
//	$nomb = $rs->fields["TRD_NOMBRE"];
//	if ($rs->fields["TRD_ESTADO"]==1) $estado="Activo"; else $estado="Inactivo";
//	$nombre2 = "$nombre - $nomb";
//	$selec = "$codi,'$nomb', $nivel_act, '$nom_nivel' ,'$nombre2'";
//	$tamanio = ($nivel_act+1)*3;
//
//	$resp .= "<tr><td class='listado2' align='right' width='".$tamanio."%'>";
//	if ($nivel>1)
//	    $resp .= "<a href='javascript:;' onClick=\"MostrarFila('tr".$id."_trd_$codi');\">
//			<img src='$ruta_raiz/imagenes/add.gif' border='0' alt='' height='15px' width='15px'></a>";
//	$resp .= "</td>";
//	$resp .= "<td class='listado2' width='". ($tamanio1-$tamanio) ."%'>$nomb</td>";
//	if ($campo=="T")
//	    $resp .= "<td class='listado2' width='15%'>".$estado."</td>";
//	$resp .= "<td class='listado2' width='15%'>".strtolower($nom_nivel)."</td>";
//	if ($accion=="S") {
//	    if ($nivel<=1)
//	    	$resp .= "<td class='listado2' width='15%'><a href='javascript:;' onClick=\"SeleccionarTRD($selec)\">Seleccionar</a></td>";
//	    else
//	    	$resp .= "<td class='listado2' width='15%'>&nbsp;</td>";
//	}
//	$resp .= "</tr>";
//	if ($nivel>1) {
//	    $resp .= "<tr name='tr".$id."_trd_$codi' id='tr".$id."_trd_$codi' $ver_fila><td colspan='6'><table border='0' width='100%'>";
//	    $resp .= ArbolSeleccionarTRD($codi, $nivel-1, $nivel_act+1, $dependencia,$nombre2,$db,$ruta_raiz,$accion,$campo,$campo_valor,$id,$ocultar);
//	    $resp .= "</table></td></tr>";
//	}
//	$rs->MoveNext();
//    }
//    return $resp;
//  }
//
///* FUNCION RECURSIVA QUE PERMITE OBTENER LA DIRECCION FISICA COMPLETA DE UN EXPEDIENTE
//	$item	:	Codigo del item fisico
//	$db	:	Coneccion con la BDD
//	$campo	:	"C" Despliega los Codigos, "N" Despliega los nombres, "S" Despliega las siglas
//	$separador:	Despliega un separador especifico, util para desplegar en tablas
//*/
//  function ActivarTRD($item, $campo, $db)
//  {
//
//    if ($item==0) return;
//    $sql = "trd_estado=1, trd_fecha_hasta=null";
//    if ($campo=="O") $sql = "trd_ocupado=0";
//    $sql = "update trd set $sql where trd_codi=$item";
//    $db->conn->Execute($sql);
//    $sql = "select trd_padre from trd where trd_codi=$item";
//    $rs=$db->conn->query($sql);
//    $codigo=$rs->fields["TRD_PADRE"];
//    ActivarTRD($codigo, $campo, $db);
//    return;
//  }
//
//  function DesactivarTRD($item, $campo, $db)
//  {
//    if ($item==0) return;
//    $sql = "trd_estado=1";
//    if ($campo=="O") $sql = "trd_ocupado=0";
//    $sql = "select count(trd_codi) as total from trd where $sql and trd_padre=$item";
//    $rs=$db->conn->query($sql);
//    $total = $rs->fields["TOTAL"];
//    if ($total == 0) {
//	$fecha = $db->conn->sysTimeStamp;
//    	$sql = "trd_estado=0, trd_fecha_hasta=$fecha";
//    	if ($campo=="O") $sql = "trd_ocupado=1";
//    	$sql = "update trd set $sql where trd_codi=$item";
//    	$db->conn->Execute($sql);
//    } else
//	return;
//    $sql = "select trd_padre from trd where trd_codi=$item";
//    $rs=$db->conn->query($sql);
//    $codigo=$rs->fields["TRD_PADRE"];
//    DesactivarTRD($codigo, $campo, $db);
//    return;
//  }

?>
