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

$ruta_raiz = "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_buscar_usuario_de_para!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_buscar_usuario_de_para);

include_once("$ruta_raiz/obtenerdatos.php");
include_once "usuarios_lista_modificada.php";

if ($documento_us1!='' and $lista_destino!=''){
$usuarios_eliminados = eliminadosDeLista($lista_destino,$documento_us1,$db,0);
$usuarios_eliminados = substr($usuarios_eliminados,1);
}

?>

<table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
    <tr>
        <td colspan="7" align="right">
            <input type="button" name="btn_borrarPara" value="Borrar Para" onClick='borrarTodos("D");' class="botones_azul" title="Borrar Para"/>
            <!--<a class='vinculos' href='#' onclick="borrarTodos('D')"><font size=2>Borrar Para</font></a>-->
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type="button" name="btn_borrarCopia" value="Borrar Copia a" onClick='borrarTodos("C");' class="botones_azul" title="Borrar Copia a"/>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <!--<a class='vinculos' href='#' onclick="borrarTodos('C')"><font size=2>Borrar Copia a</font></a>-->
        </td>
    </tr>
    <tr align="center" >
        <td width='6%' >&nbsp;</td>
        <td width='2%'  class=titulos5 >Tipo</td>
        <td width='20%' class=titulos5 >Nombre</td>
        <td width='20%' class=titulos5 >T&iacute;tulo</td>
        <td width='22%' class=titulos5 ><?=$descCargo ?></td>
        <td width='22%' class=titulos5 ><?=$descEmpresa ?></td>
        <td width='8%'  class=titulos5>Acci&oacute;n</td>
    </tr>


<?php
    $flag=1;
    $flagM=1;
    for($j=0;$j<3;$j++) {
        if ($j==0) { 	$cca = explode("-",$documento_us1);     $nom="Para:";    $tip="D";	}
      	if ($j==1) { 	$cca = explode("-",$documento_us2);     $nom="De:";      $tip="R";	}
      	if ($j==2) { 	$cca = explode("-",$concopiaa);		$nom="Copia a:"; $tip="C";	}
        for($i=0;$i<=count($cca)+1;$i++)
        {//for
            $tmp = $cca[$i];
            if (trim($tmp)!=""){//temp
                $usr = ObtenerDatosUsuario(trim($tmp),$db);
                //$boton="<a class=vinculos href=javascript:borrarCCA(".$usr["usua_codi"].",'$tip')>Borrar</a>";
                $boton = "<input class='botones_azul' title='Borrar' type='button' value='Borrar' onClick=\"borrarCCA(".$usr["usua_codi"].",'$tip');\">";
                if ($usr["tipo_usuario"]==1) {
                    $tipo_usr = "<i>(Serv.)</i>";
                } else {
                    $tipo_usr = "<i>(Ciu.)</i>";
                    if (($_SESSION["usua_admin_sistema"]==1 or $_SESSION["usua_perm_ciudadano"]==1) and $usr["inst_codi"]==0)
                        $usr["nombre"] = "<a href=\"javascript:crear_ciudadano('".$usr["usua_codi"]."');\" style='color:black;' title='Editar ciudadano'>".$usr["nombre"]."</a>";
                }
                if ($j===1 and $_SESSION["tipo_usuario"]==2) $boton = "";
                 if ($usr["usua_estado"]==0){
                  $color="#F7BE81";
                  $inactivo="<b>(Inactivo)</b>";
                 }else {
                    
                          $color="";
                          $inactivo="";
                        
                   
                  }
                   if (($lista_destino!='' || $documento_us1!='') and ($j==0 || $j==2)){
                    if ($usuarios_eliminados!='')//pintar eliminados de lista
                      foreach (explode(',',$usuarios_eliminados) as $usr_el) {                          
                          if ($usr_el == $tmp)
                              if ($color =="" and $inactivo==''){
                              $color="#F5DA81";
                              $inactivo ="<b>(No está en lista(s))</b>";
                              }
                          
                      }
                  }
                  
                 if ($usr["usua_estado"]==0){//para inactivos
                     $html_incopia.= "<tr onmouseover=\"this.style.background='#e3e8ec'\" onmouseout=\"this.style.background='white', this.style.color='black'\">
                        <td bgcolor='white'><font size=1>".$nom."</font></td>
                        <td bgcolor='white'><font size=1>$tipo_usr</font></td>
                        <td bgcolor='$color'><font size=1>".$inactivo." ".$usr["nombre"]."</font></td>
                        <td bgcolor='$color'><font size=1>".$usr["titulo"]."</font></td>
                        <td bgcolor='$color'><font size=1>".$usr["cargo"]."</font></td>
                        <td bgcolor='$color'><font size=1>".$usr["institucion"]."</font></td>
                        <td bgcolor='white'><font size=1><center>".$boton."</center></font></td>
                    </tr>";
                 }else{
                     $html_copia.= "<tr onmouseover=\"this.style.background='#e3e8ec'\" onmouseout=\"this.style.background='white', this.style.color='black'\">
                        <td bgcolor='white'><font size=1>".$nom."</font></td>
                        <td bgcolor='white'><font size=1>".$tipo_usr."</font></td>
                        <td bgcolor='$color'><font size=1>".$inactivo." ".$usr["nombre"]."</font></td>
                        <td bgcolor='$color'><font size=1>".$usr["titulo"]."</font></td>
                        <td bgcolor='$color'><font size=1>".$usr["cargo"]."</font></td>
                        <td bgcolor='$color'><font size=1>".$usr["institucion"]."</font></td>
                        <td bgcolor='white'><font size=1><center>".$boton."</center></font></td>
                    </tr>";
                 }
                $nom = "";                
                if($j==0 || $j==2){
                    if($usr["inst_codi"]!=$_SESSION["inst_codi"]){
                        if ($flag!=0)
                            $flag=1;
                           if($j==0) //Memorando: Alerta sólo para destinatarios del "Para"
                               $flagM = 1;
                        }
                    else{                       
                        $flag=0;
                        if($j==0) //Memorando: Alerta sólo para destinatarios del "Para"
                            $flagM = 0;
                    }
                }
            }//temp
        }//for
    }
    echo $html_incopia.$html_copia;
    echo "<input type='hidden' name='flag_inst' id='flag_inst' value='$flag'>";
    echo "<input type='hidden' name='flag_inst_m' id='flag_inst_m' value='$flagM'>";
?>
</table>
