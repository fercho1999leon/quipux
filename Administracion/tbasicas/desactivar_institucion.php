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
$ruta_raiz = "../..";
session_start();
include_once "$ruta_raiz/rec_session.php";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post

if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

$mensajeUsr="";
$html="";
?>
<script>
function regresar(){
    window.location="adm_instituciones.php";
}
</script>
<?php
if (isset($_GET["cod_inst_des"])){
    $id_institucion = 0+limpiar_numero($_GET["cod_inst_des"]);    
    $sql="select inst_nombre,inst_sigla,inst_ruc
    from institucion where inst_codi = $id_institucion";
    $rs_inst = $db->conn->query($sql);
    $nombreInst = $rs_inst->fields["INST_NOMBRE"];
    $siglaInst = $rs_inst->fields["INST_SIGLA"];
    $rucInst = $rs_inst->fields["INST_RUC"];
    if (!$rs_inst->EOF){
    //desactivar
    $sqlup = "update institucion set inst_estado = 0 where inst_codi = $id_institucion";
    if ($_SESSION["usua_codi"]==0)
    $ok=$db->conn->query($sqlup);
    if ($ok==1){
    $html.="<br><br><center><table width='60%' class='borde_tab' align='center'>
        <tr><td class='listado2' align='center'>
        La Institución $nombreInst ha sido desactivada</td></tr>";
    }else{
        $html.="<br><br><center><table width='60%' class='borde_tab' align='center'>
        <tr><td class='listado2' align='center'>
        Hubo problemas al desactivar la institución o su Usuario no tiene permisos para realizar esta
        acción.</td></tr>";
    }    
    $html.="<tr><td class='listado2' align='center'>
            <input  name='btn_accion' type='button' class='botones' value='Regresar' onclick='regresar()'/>
            </td></tr>";
    $html.="</tr></table></center>";
    }
    echo $html;
}else{
    echo "<br><br><center><table width='60%' class='borde_tab' align='center'>
        <tr><td class='listado2' align='center'>Hubo problemas al seleccionar la Institución, intente nuevamente</td></tr>
        <tr><td class='listado2' align='center'>
            <input  name='btn_accion' type='button' class='botones' value='Regresar' onclick='regresar()'/>
            </td></tr>
        </table></center>";
}

 ?>
</html>