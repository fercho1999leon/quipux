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
$ruta_raiz = "..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

  function ObtenerUbicacionFisica($item,$db,$campo="N",$separador=" - ")
  {

    if ($item==0) return 0;
    $dato= "ARCH_CODI";
    if ($campo=="N") $dato= "ARCH_NOMBRE";
    if ($campo=="S") $dato= "ARCH_SIGLA";
    $sql = "select arch_padre, $dato from archivo where arch_codi=$item";
    $rs=$db->conn->query($sql);
    $codigo=$rs->fields["ARCH_PADRE"];
    $dato=$rs->fields["$dato"];
    if ($codigo==0)
	return $dato;
    else
        $resp=ObtenerUbicacionFisica($codigo,$db,$campo,$separador).$separador.$dato;
    return $resp;

  }

/* FUNCION RECURSIVA QUE PERMITE OBTENER LA DIRECCION FISICA COMPLETA DE UN EXPEDIENTE
	$item	:	Codigo del item fisico
	$db	:	Coneccion con la BDD
	$campo	:	"C" Despliega los Codigos, "N" Despliega los nombres, "S" Despliega las siglas
	$separador:	Despliega un separador especifico, util para desplegar en tablas
*/

  function ArbolModificarArchivo($item, $nivel, $nivel_act, $dependencia, $nombre, $db, $ruta_raiz="..")
  {

    if ($nivel<=0) return "";
    $sql = "select arch_nombre from archivo_nivel where arch_codi>=$nivel_act and depe_codi=$dependencia order by arch_codi asc";
    $rs=$db->conn->query($sql);
    $nom_nivel1 = $rs->fields["ARCH_NOMBRE"];
    $rs->MoveNext();
    if ($rs->EOF) $nom_nivel2=""; else $nom_nivel2 = $rs->fields["ARCH_NOMBRE"];

    $sql = "select * from archivo where arch_padre=$item and depe_codi=$dependencia order by arch_nombre asc";
//echo $sql;
    $rs=$db->conn->query($sql);
    $resp = "";//<tr class='listado5'>";

    while (!$rs->EOF) {
	$codi = $rs->fields["ARCH_CODI"];
	$nomb = $rs->fields["ARCH_NOMBRE"];
	$sigla = $rs->fields["ARCH_SIGLA"];
	$tmp = $rs->fields["ARCH_CODI"];

    	$sql = "select t1+t2 as total from (select count(arch_codi) as t1 from archivo where arch_padre=$codi) as a,
	    	(select count(arch_codi) as t2 from archivo_radicado where arch_codi=$codi) as b";
    	$rs2=$db->conn->query($sql);
    	$borrar = $rs2->fields["TOTAL"];
	if ($nivel>1) $esta = 2; else $esta = $rs->fields["ARCH_ESTADO"];

	$nombre2 = $nombre . " - " . $rs->fields["ARCH_NOMBRE"];
	$tmp_ed = "$codi,'$nomb','$sigla',$nivel_act,'$nom_nivel1',$esta,$borrar,'$nombre2'";
	$tmp_cr = "$codi,$nivel_act,'$nom_nivel2','$nombre2'";
	$tamanio = ($nivel_act+1)*3;

	$resp .= "<tr><td class='listado2' align='right' width='".$tamanio."%'>";
	if ($nivel>1)
	    $resp .= "<a href='javascript:;' onClick=\"MostrarFila('tr_$codi')\">
			<img src='$ruta_raiz/imagenes/add.gif' border='0' alt='' height='15px' width='15px'></a>";
	$resp .= "</td>";
	$resp .= "<td class='listado2' width='". (58-$tamanio) ."%'>".$rs->fields["ARCH_NOMBRE"]."</td>";
	$resp .= "<td class='listado2' width='10%'>".$rs->fields["ARCH_SIGLA"]."</td>";
	$resp .= "<td class='listado2' width='10%'>$nom_nivel1</td>";
	$resp .= "<td class='listado2' width='10%'><a class='grid' href='javascript:;' onClick=\"EditarItem($tmp_ed)\">Editar</a></td>";
	if ($nivel>1)
	    $resp .= "<td class='listado2' width='12%'><a class='grid' href='javascript:;' onClick=\"CrearItem($tmp_cr)\">Crear ".strtolower($nom_nivel2)."</a></td>";
	else
	    $resp .= "<td class='listado2' width='12%'>&nbsp;</td>";
	$resp .= "</tr>";
	if ($nivel>1) {
	    $resp .= "<tr name='tr_$codi' id='tr_$codi' style='display:none'><td colspan='6'><table width='100%'>";
	    $resp .= ArbolModificarArchivo($codi, $nivel-1, $nivel_act+1, $dependencia, $nombre2, $db, $ruta_raiz);
	    $resp .= "</table></td></tr>";
	}
	$rs->MoveNext();
    }
    return $resp;
  }

/* FUNCION RECURSIVA QUE PERMITE OBTENER LA DIRECCION FISICA COMPLETA DE UN EXPEDIENTE
	$item	:	Codigo del item fisico
	$nivel	:	Numero de niveles que 
	$db	:	Coneccion con la BDD
	$campo	:	"C" Despliega los Codigos, "N" Despliega los nombres, "S" Despliega las siglas
	$separador:	Despliega un separador especifico, util para desplegar en tablas
*/

  function ArbolSeleccionarArchivo($item, $nivel_act, $dependencia, $nombre, $db, $ruta_raiz="..", $accion="L", $campo="T", $campo_valor=0,$id=0, $ocultar="S")
  {
    $rs=$db->conn->query("select count(1) as \"num\" from archivo_nivel where depe_codi=$dependencia");
    if ($nivel_act<0) $nivel_act = $rs->fields["NUM"];
    $nivel = $rs->fields["NUM"]-$nivel_act;
    if ($nivel<=0) return "";
    $sql = "select arch_nombre from archivo_nivel where arch_codi=$nivel_act and depe_codi=$dependencia";
    $rs=$db->conn->query($sql);
    $nom_nivel = $rs->fields["ARCH_NOMBRE"];

    if ($ocultar=="N") $ver_fila=""; else $ver_fila="style='display:none'";
    $tamanio1 = 85;
    if ($accion=="S") $tamanio1 -= 15;
    if ($campo=="T") $tamanio1 -= 15;
    $sql = "";
    if ($campo=="E") $sql .= " and arch_estado=$campo_valor ";
    if ($campo=="O") $sql .= " and arch_ocupado=$campo_valor ";
    $sql = "select * from archivo where arch_padre=$item and depe_codi=$dependencia $sql order by arch_nombre asc";
//echo $sql;
    $rs=$db->conn->query($sql);
    $resp = "";//<tr class='listado5'>";
    while (!$rs->EOF) {
	$codi = $rs->fields["ARCH_CODI"];
	$nomb = $rs->fields["ARCH_NOMBRE"];
	if ($rs->fields["ARCH_ESTADO"]==0) $estado="Inactivo"; else $estado="Activo";
	$nombre2 = "$nombre - $nomb";
	$selec = "$codi,'$nomb', $nivel_act, '$nom_nivel' ,'$nombre2'";
	$tamanio = ($nivel_act+1)*3;

	$resp .= "<tr><td class='listado2' align='right' width='".$tamanio."%'>";
	if ($nivel>1)
	    $resp .= "<a href='#' onClick=\"MostrarFila('tr".$id."_arch_$codi');\">
			<img src='$ruta_raiz/imagenes/add.gif' border='0' alt='' height='15px' width='15px'></a>";
	$resp .= "</td>";
	$resp .= "<td class='listado2' width='". ($tamanio1-$tamanio) ."%'>$nomb</td>";
	if ($campo=="T")
	    $resp .= "<td class='listado2' width='15%'>".$estado."</td>";
	$resp .= "<td class='listado2' width='15%'>".strtolower($nom_nivel)."</td>";
	if ($accion=="S") {
	    if ($nivel<=1)
	    	$resp .= "<td class='listado2' width='15%'><a class='grid' href='#' onClick=\"SeleccionarArchivo($selec)\">Seleccionar</a></td>";
	    else
	    	$resp .= "<td class='listado2' width='15%'>&nbsp;</td>";
	}
	$resp .= "</tr>";
	if ($nivel>1) {
	    $resp .= "<tr name='tr".$id."_arch_$codi' id='tr".$id."_arch_$codi' $ver_fila><td colspan='6'><table border='0' width='100%'>";
	    $resp .= ArbolSeleccionarArchivo($codi, $nivel_act+1, $dependencia,$nombre2,$db,$ruta_raiz,$accion,$campo,$campo_valor,$id, $ocultar);
	    $resp .= "</table></td></tr>";
	}
	$rs->MoveNext();
    }
    return $resp;
  }

/* FUNCION RECURSIVA QUE PERMITE OBTENER LA DIRECCION FISICA COMPLETA DE UN EXPEDIENTE
	$item	:	Codigo del item fisico
	$db	:	Coneccion con la BDD
	$campo	:	"C" Despliega los Codigos, "N" Despliega los nombres, "S" Despliega las siglas
	$separador:	Despliega un separador especifico, util para desplegar en tablas
*/

  function ActivarArchivo($item, $campo, $db)
  {

    if ($item==0) return;
    $sql = "arch_estado=1"; 
    if ($campo=="O") $sql = "arch_ocupado=0";
    $sql = "update archivo set $sql where arch_codi=$item";
    $db->conn->Execute($sql);
    $sql = "select arch_padre from archivo where arch_codi=$item";
    $rs=$db->conn->query($sql);
    $codigo=$rs->fields["ARCH_PADRE"];
    ActivarArchivo($codigo, $campo, $db);
    return;
  }

  function DesactivarArchivo($item, $campo, $db)
  {

    if ($item==0) return;
    $sql = "arch_estado=1"; 
    if ($campo=="O") $sql = "arch_ocupado=0";
    $sql = "select count(arch_codi) as total from archivo where $sql and arch_padre=$item";
    $rs=$db->conn->query($sql);
    $total = $rs->fields["TOTAL"];
    if ($total == 0) {
	$fecha = $db->conn->sysTimeStamp;
    	$sql = "arch_estado=0";
    	if ($campo=="O") $sql = "arch_ocupado=1";
    	$sql = "update archivo set $sql where arch_codi=$item";
    	$db->conn->Execute($sql);
    } else
	return;
    $sql = "select arch_padre from archivo where arch_codi=$item";
    $rs=$db->conn->query($sql);
    $codigo=$rs->fields["ARCH_PADRE"];
    DesactivarArchivo($codigo, $campo, $db);
    return;
  }

?>
