<?php
/*	
* Modificado por		Iniciales		Fecha (dd/mm/aaaa)
*	DAVID GAMBOA    	SC			12/11/2011
* 
*/
$ruta_raiz = "../..";

$ruta_raiz = "../..";
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post


session_start();
if($_SESSION["usua_admin_sistema"]!=1) die("");
include_once "$ruta_raiz/rec_session.php";

if (isset($_GET)){  
    $dependencia = 0+$_GET['depe_codi'];
    
    if (trim($dependencia)==0){
       echo "<table class='borde_tab' width='100%'><tr><td class='listado2'><font size='2' color='red'><center>Seleccione el Área</center></font></td></tr></table>";
    }else{
    $permisos = 0 + $_GET['codPermiso'];
    
    if ($radio_filtro=='NO')
        $filtro=" not ";
    else
        $filtro="";
    $sql = "select u.usua_codi,u.usua_nomb || ' ' ||u.usua_apellido as usua_nombre";
    $sql.= " from usuarios u where ";
    if ($permisos!=0)

            $sql.= " usua_codi $filtro in (select usua_codi from permiso_usuario where id_permiso in ($permisos))";
    if ($permisos!=0)
    $sql.= " and depe_codi = $dependencia";
    else
        $sql.= " depe_codi = $dependencia";
    $sql.= " and usua_esta=1 order by 1";
    
    $rs = $db->conn->query($sql);
    $i=0;
    ?>
<!--    <input type="button" name="btn_accion" class="botones_2" value="&gt;" onclick="usuarios_todos();" title="Seleccionar Todos."/>-->
    <table class="borde_tab" width="100%">
        <a href="javascript:;" onclick="usuarios_todos();">
                    <img alt="Seleccionar toda la lista" src="<?=$ruta_raiz?>/iconos/flechadesc.gif"/></a><font size="1">Seleccionar Todos</font>
        <tr><td class="titulos1"><center>SELECCIONE USUARIOS</center></td></tr>
       
        <?php
        while (!$rs->EOF) {
            $ucod=$rs->fields['USUA_CODI'];
//            echo "<option id='usuario_cod' onclick='ver_nombre($ucod,1)' value='".$rs->fields['USUA_CODI']."'>".$rs->fields['USUA_NOMBRE']."</option>" ;
            
             echo "<tr id='tr_usr_disponibles_$ucod' class='listado2' onclick='ver_nombre($ucod,1)'>
                        <td title='".$columnas_desc[$col]."'>".$rs->fields['USUA_NOMBRE']."</td>
                      </tr>";
             $rs->MoveNext();
             $i++;
             
             $todosseleccionados = $todosseleccionados.",".$ucod; 
             
    }
    ?> 
    <input type='hidden' name='todos_usuarios' id='todos_usuarios' value="<?=$todosseleccionados?>"/>
    <input type='hidden' name='sel_todos_usuarios' id='sel_todos_usuarios' value="<?=$todosseleccionados?>"/>
    </table>
    <?php }?>
<?php
}?>



