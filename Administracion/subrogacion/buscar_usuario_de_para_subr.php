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
require_once("$ruta_raiz/funciones.php"); //para traer funciones p_get y p_post
p_register_globals($_POST);

session_start();
include_once "$ruta_raiz/rec_session.php";

include_once("$ruta_raiz/obtenerdatos.php");
include_once "$ruta_raiz/funciones.php";


?>

<body bgcolor="#FFFFFF">
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
    $flag=0;
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
                echo "<tr onmouseover=\"this.style.background='#e3e8ec'\" onmouseout=\"this.style.background='white', this.style.color='black'\">
                        <td><font size=1>".$nom."</font></td>
                        <td><font size=1>$tipo_usr</font></td>
                        <td><font size=1>".$usr["nombre"]."</font></td>
                        <td><font size=1>".$usr["titulo"]."</font></td>
                        <td><font size=1>".$usr["cargo"]."</font></td>
                        <td><font size=1>".$usr["institucion"]."</font></td>
                        <td><font size=1><center>".$boton."</center></font></td>
                    </tr>";
                $nom = "";
                if($j==0 && $usr["inst_codi"]!=$_SESSION["inst_codi"])
                    $flag=1;
            }//temp
        }//for
    }
    echo "<input type='hidden' name='flag_inst' id='flag_inst' value='$flag'>";
?>
</table>
</body>
</html>