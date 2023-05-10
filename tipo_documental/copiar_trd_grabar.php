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

$ruta_raiz = "..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals(array());

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

$record = array();

$mensaje = "";
if ($txt_area_destino=="0" or $txt_area_origen=="0") 
    $mensaje = "Por favor seleccione las &aacute;reas origen y destino para copiar las $descTRDpl";

//$rs = $db->conn->query("select depe_codi, count(1) as num from trd_nivel where depe_codi=$txt_area_origen group by 1");
//$num_area_origen =  0 + $rs->fields["NUM"];
//if (!$rs or $num_area_origen==0)
//    $mensaje = "El &aacute;rea origen no tiene creada una estructura de $descTRDpl";
//
//$rs = $db->conn->query("select depe_codi, count(1) as num from trd_nivel where depe_codi=$txt_area_destino group by 1");
//$num_area_destino =  0 + $rs->fields["NUM"];
//if ($num_area_origen>0 and $num_area_destino>0 and $num_area_origen<>$num_area_destino) $mensaje = "No se puede realizar esta acción. Los niveles del área origen son diferentes a los niveles del área destino";
//
//if ($mensaje == "") {
//    if ($num_area_destino == 0) {
//        $sql = "select * from trd_nivel where depe_codi=$txt_area_origen";
//        $rs = $db->conn->query($sql);
//        while (!$rs->EOF) {
//            unset ($record);
//            $record["TRD_CODI"]  = $rs->fields["TRD_CODI"];
//            $record["DEPE_CODI"] = $txt_area_destino;
//            $record["TRD_NOMBRE"] = $db->conn->qstr($rs->fields["TRD_NOMBRE"]);
//            $record["TRD_DESCRIPCION"] = $db->conn->qstr($rs->fields["TRD_DESCRIPCION"]);
//            $ok = $db->conn->Replace("TRD_NIVEL", $record, "", false,false,true,false);
//            $rs->MoveNext();
//        }
//    }
//    CopiarTRD(0,0);
//    $mensaje = "Las $descTRDpl fueron copiadas correctamente";
//}

if ($mensaje == "") {
    CopiarTRD(0,0);
    $mensaje = "Las $descTRDpl fueron copiadas correctamente";
}

function CopiarTRD($trd_padre_origen, $trd_padre_destino) {
    global $db, $txt_area_destino, $txt_area_origen, $record;

    $sql = "select * from trd where trd_padre=$trd_padre_origen";
    if ($trd_padre_origen == 0) $sql .= " and depe_codi=$txt_area_origen";
    $rs = $db->conn->query($sql);
    if (!$rs or $rs->EOF) return;

    while (!$rs->EOF) {
        unset ($record);
        $record["TRD_CODI"] = $db->nextId("sec_trd");
        $record["TRD_PADRE"] = $trd_padre_destino;
        $record["TRD_NOMBRE"] = $db->conn->qstr($rs->fields["TRD_NOMBRE"]);
        $record["DEPE_CODI"] = $txt_area_destino;
        $record["TRD_ESTADO"] = $rs->fields["TRD_ESTADO"];
        $record["TRD_ARCH_GESTION"] = $rs->fields["TRD_ARCH_GESTION"];
        $record["TRD_ARCH_CENTRAL"] = $rs->fields["TRD_ARCH_CENTRAL"];
        $record["TRD_FECHA_DESDE"] = "'".$rs->fields["TRD_FECHA_DESDE"]."'::timestamp";
        $record["TRD_NIVEL"] = $rs->fields["TRD_NIVEL"];
        $ok = $db->conn->Replace("TRD", $record, "", false,false,true,false);
        CopiarTRD($rs->fields["TRD_CODI"], $record["TRD_CODI"]);
        $rs->MoveNext();
    }
    return;
}

?>

<body>
    <center>
        <br><br><br>
        <table width="40%" border="2" align="center" class="t_bordeGris">
            <tr>
                <td width="100%" height="30" class="listado2">
                    <span class=etexto><center><b><br><?=$mensaje?><br>&nbsp;</b></center></span>
                    <br>
                    <center><input type="button" name="btn_aceptar" value="Aceptar" class="botones" onClick="window.location='./menu_trd.php';"></center>
                    <br>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>