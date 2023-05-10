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
** Permite crear los metadatos para la institución y/o áreas                        **
**                                                                                  **
** Desarrollado por:                                                                **
**      Lorena Torres J.                                                            **
*************************************************************************************/

$ruta_raiz = isset($ruta_raiz) ? $ruta_raiz : "..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

/*  Función para consultar datos de una tabla recursiva (LT)
    $db:          Conección con la BDD
    $depe_actu:   Código de la dependencia
    $es_consulta: Determina si ejecuta el query para consulta o edición
    $lista:       Retorna una lista con los datos consultados
*/
 function ConsultarMetadatos($db, $depe_actu, $es_consulta)
 {
    $inst_actual = $_SESSION["inst_codi"];    
    if($depe_actu == 0)
        $where = "where inst_codi= $inst_actual and depe_codi is null";
    else
        $where = "where depe_codi= $depe_actu";
    
    $where = $where . " and met_estado <> 2";
    
    //Se consulta datos de tabla de carpetas virtuales
    if($es_consulta == 1)
        $consulta = "select * from metadatos
        left outer join (select distinct met_padre as met_padre_cv from metadatos $where) as cv on metadatos.met_codi = cv.met_padre_cv
        $where
        order by met_padre, met_nivel, met_nombre";
    else
        $consulta = "select * from metadatos
        left outer join (select met_codi as met_codi_doc, count(1) as met_cant_doc from metadatos_radicado where depe_codi = $depe_actu and estado = 1 group by 1) as doc on metadatos.met_codi = doc.met_codi_doc
        left outer join (select distinct met_padre as met_padre_cv from metadatos $where) as cv on metadatos.met_codi = cv.met_padre_cv
        $where
        order by met_padre, met_nivel, met_nombre";   

    //echo $consulta;
    $rs=$db->conn->query($consulta);
    $i=0;
    $lista = array();
    $estado = "";

    //Se arma el listado
    while (!$rs->EOF) {
        $lista[$i]["codigo"] = $rs->fields["MET_CODI"];
        $lista[$i]["codigo_padre"] = $rs->fields["MET_PADRE"];
        $lista[$i]["nombre"] = $rs->fields["MET_NOMBRE"];
        $lista[$i]["nivel"] = $rs->fields["MET_NIVEL"];
        if($rs->fields["MET_CODI_DOC"] != "") $tiene_doc = 1; else $tiene_doc = 0;
        if($rs->fields["MET_PADRE_CV"] != "") $tiene_carpeta = 1; else $tiene_carpeta = 0;
        $lista[$i]["tiene_doc"] = $tiene_doc;
        $lista[$i]["tiene_carpeta"] = $tiene_carpeta;
        //echo "</br> db".$rs->fields["MET_CODI_DOC"]. " - doc:". $lista[$i]["tiene_doc"] . " - cv:". $lista[$i]["tiene_carpeta"];
        if ($rs->fields["MET_ESTADO"]==1)
                $estado="Activo";
        else if($rs->fields["MET_ESTADO"]==0)
            $estado="Inactivo";
        else if($rs->fields["MET_ESTADO"]==2)
            $estado="Eliminado";
        $lista[$i]["estado"] = $estado;
        $lista[$i]["estado_valor"] = $rs->fields["MET_ESTADO"];
        $lista[$i]["arch_gestion"] = $rs->fields["MET_ARCH_GESTION"];
        $lista[$i]["arch_central"] = $rs->fields["MET_ARCH_CENTRAL"];
        $lista[$i]["cant_doc"] = $rs->fields["MET_CANT_DOC"];        
	$rs->MoveNext();
        $i++;
    }
    return $lista;
}

function ArmarArbolMetadatos($listaDatos, $codigo, $ruta_raiz, $ocultar="S", $nombre_completo= "", $accion="") {

    $tam = sizeof($listaDatos);
    if($ocultar=="S")
        $imgCarpeta = "agregar.png";
    else
        $imgCarpeta = "quitar.png";    
    
    for($i = 0; $i < $tam; $i++){
        if(($listaDatos[$i]["codigo_padre"] == $codigo))
        {
//            echo "<br>". $listaDatos[$i]["codigo_padre"] . " - " . $listaDatos[$i]["nombre"] . " - " . $accion . " - " . $listaDatos[$i]["tiene_carpeta"]. "<br>";
            $cod = $listaDatos[$i]["codigo"];
            $cod_padre = $listaDatos[$i]["codigo_padre"];
            $nombre = $listaDatos[$i]["nombre"];
            $nivel = $listaDatos[$i]["nivel"];           
            $estado = $listaDatos[$i]["estado"];
            $estado_valor = $listaDatos[$i]["estado_valor"];
            $espacio = str_repeat("&nbsp;", $nivel*10);
            $imagen = "";          
            $nombre_c= "$nombre_completo - $nombre";
            $tiene_doc = $listaDatos[$i]["tiene_doc"];
            $tiene_carpeta = $listaDatos[$i]["tiene_carpeta"];
            $cant_doc = $listaDatos[$i]["cant_doc"];
            
            $borrar = 1;
            if($tiene_doc==1 or $tiene_carpeta==1)
                $borrar = 0;
            //Datos a modificar
            $tmp_ed = "$cod,$cod_padre,'$nombre', $estado_valor, $nivel, $borrar, '$nombre_c'";
            //Datos a crear
            $tmp_cr = "$cod,$nivel,'$nombre_c'";
            $valor_accion = "";
            
            if($accion == "Editar"){
                $accion_editar ="<td class='listado2' colspan='2' width='10%' align='center'><a href='#' onClick=\"EditarItem($tmp_ed)\" class='vinculos'>Editar</a></td>";
                if($tiene_doc==0 and $estado_valor == 1)
                    $valor_accion = "<a href='javascript:;' onClick=\"CrearItem($tmp_cr)\" class='vinculos'>Crear</a>";
            }
            else if($accion == "Seleccionar"){   //Selección de metadato para asignar documento
                if($tiene_carpeta!=1 and $estado_valor == 1){
                    $nombre_c = substr($nombre_c, 2);
                    $seleccion = "$cod, '$nombre_c'";                    
                    $valor_accion = "<a href='javascript:;' onClick=\"SeleccionarMET($seleccion)\">$nombre</a>";
                    $accion_editar = "";                    
                }
                else
                    $valor_accion = $nombre;
            }
            else if($accion == "SeleccionarDoc"){ //Consulta de documentos por metadato
                if($tiene_carpeta!=1){
                    $nombre_c = substr($nombre_c, 2);
                    $seleccion = "$cod, '$nombre_c'";
                    if($cant_doc == 0) $cant_doc=0;
                    $valor_accion = "<a href='javascript:;' onClick=\"SeleccionarMET($seleccion)\" style='color: blue;'>Seleccionar ($cant_doc)</a>";
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
           
            
            if($accion == "Seleccionar" and $estado_valor==1){
                $fila_datos = "<tr name='tr_$cod_padre' id='tr_$cod' $ver_fila>
                        <td class='listado2' width='60%'>$espacio $imagen 
                        $valor_accion
                        </td>                        
                        $accion_editar                       
                      </tr>";
                echo $fila_datos;
            }
            else if($accion == "Editar" or $accion == "SeleccionarDoc"){
                $fila_datos = "<tr name='tr_$cod_padre' id='tr_$cod' $ver_fila>
                        <td class='listado2' width='60%'>$espacio $imagen $nombre</td>
                        <td class='listado2' width='15%' align='center'>".$estado."</td>
                        $accion_editar
                        <td class='listado2' width='15%' align='center'>$valor_accion</td>
                      </tr>";
                echo $fila_datos;
            }
            else if($accion == "Consultar"){
                $fila_datos = "<tr name='tr_$cod_padre' id='tr_$cod' $ver_fila>
                        <td class='listado2' width='85%'>$espacio $imagen $nombre</td>
                        <td class='listado2' width='15%' align='center'>".$estado."</td>                       
                      </tr>";
                echo $fila_datos;
            }

            ArmarArbolMetadatos($listaDatos,$cod, "..", $ocultar, $nombre_c,$accion);
        }
    }
}

  function ObtenerNombreCompletoMet($item,$db,$campo="N",$separador=" - ")
  {

    if ($item==0) return 0;
    $dato= "MET_CODI";
    if ($campo=="N") $dato= "MET_NOMBRE";
    $sql = "select met_padre, $dato from metadatos where met_codi=$item";
    $rs=$db->conn->query($sql);
    $codigo=$rs->fields["MET_PADRE"];
    $dato=$rs->fields["$dato"];
    if ($codigo==0)
	return $dato;
    else
        $resp=ObtenerNombreCompletoMet($codigo,$db,$campo,$separador).$separador.$dato;
    return $resp;

  }
  
 /*  FUNCION RECURSIVA QUE PERMITE MODIFICAR EL ESTADO DE UN METADATO (DESCENDENTE)
    $cod:    Código del metadato
    $estado: Estado del metadato
    $db:     Coneccion con la BDD
*/
function ModificarEstadoMet($cod, $estado, $db)
{
    if ($cod==0) return;
    
    if($estado==1) //Activa metadato
        $sql = "met_estado=1";
    else if($estado==0){ //Inactiva metadato
        $sql = "met_estado=0";
    }
    else if($estado==2){ //Elimina metadato       
        $sql = "met_estado=2";
    }
    $sql = "update metadatos set $sql where met_codi=$cod";
    $db->conn->Execute($sql);

    //Consulta metadatos hijos
    $sql = "select met_codi, met_padre from metadatos where met_padre=$cod and met_estado <> 2";
    $rs=$db->conn->query($sql);

    //Actualiza estado de carpetas hijas con método recursivo
    foreach ($rs as $carpeta)
    {
        $codigo=$carpeta['MET_CODI'];
        ModificarEstadoMet($codigo, $estado, $db);
    }
    return;
}

/*  FUNCION RECURSIVA QUE PERMITE MODIFICAR EL ESTADO DE UN METADATO (ASCENDENTE)
    $cod:    Código del metadato
    $estado: Estado del metadato
    $db:     Coneccion con la BDD
*/
function ModificarEstadoMetAsc($cod, $cod_padre, $estado, $db)
{
    if ($cod==0) return;
    
    if($estado==1) //Activa metadato
        $sql = "met_estado=1";
    else if($estado==0){ //Inactiva metadato
        $sql = "met_estado=0";
    }
    else if($estado==2){ //Elimina metadato       
        $sql = "met_estado=2";
    }
    $sql = "update metadatos set $sql where met_codi=$cod";   
    $db->conn->Execute($sql);

    //Consulta metadatos padre
    $sql = "select met_codi, met_padre from metadatos where met_codi=$cod_padre and met_estado <> 2";    
    $rs=$db->conn->query($sql);

    //Actualiza estado de carpetas hijas con método recursivo
    foreach ($rs as $carpeta)
    {
        $codigo=$carpeta['MET_CODI'];
        $codigo_padre=$carpeta['MET_PADRE'];
        ModificarEstadoMetAsc($codigo,$codigo_padre, $estado, $db);
    }
    return;
}

/*  Función para consultar datos de un metadato por código
    $db:          Conección con la BDD
    $depe_actu:   Código de la dependencia
    $es_consulta: Determina si ejecuta el query para consulta o edición
    $lista:       Retorna una lista con los datos consultados
*/
 function ConsultarMetadatosRadi($db, $radi_codi)
 {
    $consulta = "select * from metadatos_radicado 
    where radi_nume_radi = $radi_codi and depe_codi = ". $_SESSION["depe_codi"].
    " and estado = 1";

    //echo $consulta;
    $rs=$db->conn->query($consulta);   

    //Se arma el listado
    $datos["met_radi_codi"] = $rs->fields["MET_RADI_CODI"];
    $datos["radi_nume_radi"] = $rs->fields["RADI_NUME_RADI"];
    $datos["met_codi"] = $rs->fields["MET_CODI"];
    $datos["depe_codi"] = $rs->fields["DEPE_CODI"];
    $datos["usua_codi"] = $rs->fields["USUA_CODI"];
    $datos["texto"] = $rs->fields["TEXTO"];
    $datos["metadato"] = $rs->fields["METADATO"];
    $datos["metadato_texto"] = $rs->fields["METADATO_TEXTO"];    
    $datos["metadato_codi"] = $rs->fields["METADATO_CODI"];
    $datos["fecha"] = $rs->fields["FECHA"];   
      
    return $datos;
}


 function ConsultarMetadatosRadiDoc($db, $radi_codi)
 {
    $sql = "select mr.met_radi_codi, mr.met_codi, mr.metadato_texto, d.dep_sigla, d.depe_codi, d.depe_nomb
            from metadatos_radicado mr
            left outer join dependencia d on d.depe_codi=mr.depe_codi 
            where mr.radi_nume_radi=$radi_codi and estado = 1
            order by d.depe_nomb";
           $rs=$db->conn->query($sql);   
    return $rs;
 }
?>