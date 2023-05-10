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
?>
<script>
function desactivar(){
    cod_inst_des = document.getElementById("cod_inst_desac").value;
    if (cod_inst_des>0)
    window.location="desactivar_institucion.php?cod_inst_des="+cod_inst_des;
}
</script>
<?php
$mensajeUsr="";
if (isset($_GET["id_institucion"])){
    $id_institucion = 0+limpiar_numero($_GET["id_institucion"]);    
    $sql="select inst_nombre,inst_sigla,inst_ruc
    from institucion where inst_codi = $id_institucion";
    $rs_inst = $db->conn->query($sql);
    $nombreInst = $rs_inst->fields["INST_NOMBRE"];
    $siglaInst = $rs_inst->fields["INST_SIGLA"];
    $rucInst = $rs_inst->fields["INST_RUC"];
    //Informacion de la institucion
    $html="<br><br><center>
          <table width='60%' class='borde_tab' align='center'>
          <tr><td class='titulos2' align='center'>Institución: </td>
          <td class='listado2' align='center'>$nombreInst</td></tr>
          <tr><td class='titulos2' align='center'>Sigla: </td>
          <td class='listado2' align='center'>$siglaInst</td></tr>
          <tr><td class='titulos2' align='center'>Ruc: </td>
          <td class='listado2' align='center'>$rucInst</td></tr>
          ";
    
    //Procedo a mostrar el numero de usuarios activos de la institucion
    $sql = "select count(1) as contadorusr from usuario where inst_codi = $id_institucion and usua_esta =1";
    $rs=$db->conn->query($sql);
    $contadorUsr = $rs->fields["CONTADORUSR"];    
    if ($contadorUsr == 0){
        $html.= "<tr><td><input type='hidden' name='cod_inst_desac' id='cod_inst_desac' value='$id_institucion'/></td></tr>
        <tr><td colspan='2' class='listado2' align='center'>
        <b>Existe</b> <font color='blue' size='2'>$contadorUsr</font> <b>Usuarios por Desactivar
        </b></td></tr>";
        
        $html.= "<tr><td colspan='2' class='listado2' align='center'>";
        if ($_SESSION["usua_codi"]==0)
        $html.="<input  name='btn_accion' type='button' class='botones_largo' value='Desactivar' onclick='desactivar()'/>";
        $html.="<input  name='btn_accion' type='button' class='botones_largo' value='Regresar' onclick='history.back()'/>
            </td></tr>";
    }else{
        $html.="<tr><td colspan='2' class='listado2' align='center'>
        <b>No se puede desactivar esta institución, existen <b><font color='blue' size='2'>$contadorUsr</font> <b>usuarios activos<b> 
        </b></td></tr>
        <tr><td class='listado2' align='center' colspan='2'>
            <input  name='btn_accion' type='button' class='botones' value='Regresar' onclick='history.back()'/>
            </td></tr>";
    }
    $html.="</table></center>";
    echo $html;
}else{
    echo "<br><br><center><table width='60%' class='borde_tab' align='center'>
        <tr><td class='listado2' align='center'>Hubo problemas al seleccionar la Institución, intente nuevamente</td></tr>
        <tr><td class='listado2' align='center'>
            <input  name='btn_accion' type='button' class='botones' value='Regresar' onclick='history.back()'/>
            </td></tr>
        </table></center>";
}

 ?>
</html>