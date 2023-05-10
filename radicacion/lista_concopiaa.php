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
$ruta_raiz = isset($ruta_raiz) ? $ruta_raiz : "..";
session_start();
include_once "$ruta_raiz/rec_session.php";
if (isset ($replicacion) && $replicacion && $config_db_replica_lista_concopia!="") $db = new ConnectionHandler($ruta_raiz,$config_db_replica_lista_concopia);

include_once "$ruta_raiz/funciones_interfaz.php";
echo "<html>".html_head();

include("$ruta_raiz/obtenerdatos.php");
include_once "usuarios_lista_modificada.php";
   //Se incluyo por register globals.
  $concopiaa  = $_GET['concopiaa'] ;  
  $documento_us2  = $_GET['documento_us2'] ; 
  $documento_us1  = $_GET['documento_us1'] ;   
  $radi_lista_nombre = $_GET['radi_lista_nombre'];
  $ent = $_GET['ent'];
  $lista_destino=$_GET['radi_lista_dest'];
  if ($lista_destino!='' and $documento_us1!=''){
    $usuarios_eliminados = eliminadosDeLista($lista_destino,$documento_us1,$db,0);
    $usuarios_eliminados = substr($usuarios_eliminados,1);
  }
?>
<script type="text/Javascript">
    function crear_ciudadano(usuario) {
        accion = '&accion=1';
        if ((usuario||'0')!='0')
            accion = '&accion=2&ciu_codigo='+usuario;
        var x = (screen.width - 1100) / 2;
        var y = (screen.height - 550) / 2;
        windowprops = "top=0,left=0,location=no,status=no, menubar=no,scrollbars=yes, resizable=yes,width=1100,height=550";
        //url = '../Administracion/usuarios/adm_usuario_ext.php?cerrar=Si'+accion;
        url = '../Administracion/ciudadanos/adm_usuario_ext.php?cerrar=Si'+accion;
        preview = window.open(url , "Crear_Usuario_Externo", windowprops);
        preview.moveTo(x, y);
        preview.focus();
        return;
    }
    function refrescar_pagina() {
        //alert ('ok');
    }    
</script>
<body>

<table  width="100%" border="0" cellpadding="0" cellspacing="0">
    <? 
    
    /*echo "RADI: ".$radi_lista_nombre;
    $radi_lista_nombre='';
    echo "RAD:".$radi_lista_nombre;*/
    if ($ent==1 and trim($radi_lista_nombre)!='') {
    ?>
    <tr>
    <td align="left" class="listado1_ver">Listas Seleccionadas: </td>
    <td colspan="4">
        <input type="text" class="select" name="radi_lista_nombre" id="radi_lista_nombre" value="<?=$radi_lista_nombre?>" size="126" readonly>
    </td>
    </tr>
    <? } ?>
    <tr align="center" > 
	<td width='10%'></td>
        <td width='3%' CLASS=titulos2 >&nbsp;</td>
	<td width='22%' CLASS=titulos2 >Nombre</td>
	<td width='22%' CLASS=titulos2 >T&iacute;tulo</td>
	<td width='22%' CLASS=titulos2 >Puesto</td>
	<td width='21%' CLASS=titulos2 ><?=$descEmpresa?></td>
        
    </tr>

    <?
        for($j=0;$j<3;$j++) {
            if ($j==0) { 	$cca = explode("-",$documento_us1);	$nom="Para:";	$tip="D";	}
            if ($j==1) { 	$cca = explode("-",$documento_us2);	$nom="De:";	$tip="R";	}
            if ($j==2) { 	$cca = explode("-",$concopiaa);		$nom="Copia a:";		$tip="C";	}
            for($i=0;$i<=count($cca)+1;$i++)
            {
                $tmp = $cca[$i];
                if (trim($tmp)!=""){
                $usr = ObtenerDatosUsuario(limpiar_numero(trim($tmp)),$db);
                if ($usr["tipo_usuario"]==1) {
                    $tipo_usr = "<i>(Serv.)</i>";
                } else {
                    $tipo_usr = "<i>(Ciu.)</i>";
                    if (($_SESSION["usua_admin_sistema"]==1 or $_SESSION["usua_perm_ciudadano"]==1) and $usr["inst_codi"]==0)
                        $usr["nombre"] = "<a href=\"javascript:crear_ciudadano('".$usr["usua_codi"]."');\" style='color:black;' title='Editar ciudadano'>".$usr["nombre"]."</a>";
                }
                //$color="#FFFFFF";
              if ($usr["usua_estado"]==0){
                  $color="#F7BE81";
                  $inactivo = "(Inactivo)";
              }
              else{ $color="";
              $inactivo = "";              
              }
               if (($lista_destino!='' || $documento_us1!='') and ($j==0 || $j==2)){
               if ($usuarios_eliminados!='')//pintar eliminados de lista
                      foreach (explode(',',$usuarios_eliminados) as $usr_el) {                          
                          if ($usr_el == $tmp)
                              if ($color=="" and $inactivo==''){
                              $color="#F5DA81";
                              $inactivo ="<b>(No está en lista(s))</b>";
                              }
                          
                      }
               }
              if ($usr["usua_estado"]==0)
                  $html_incopia.="<tr height='15' onmouseover=\"this.style.background='#e3e8ec'\" onmouseout=\"this.style.background='white', this.style.color='black'\">
                        <td bgcolor='white'><font size=1>$nom</font></td>
                        <td bgcolor='white'><font size=1>$tipo_usr</font></td>
                        <td bgcolor='$color'><font size=1><b>".$inactivo."</b> ".$usr["nombre"]."</font></td>
                        <td bgcolor='$color'><font size=1>".$usr["titulo"]."</font></td>
                        <td bgcolor='$color'><font size=1>".$usr["cargo"]."</font></td>
                        <td bgcolor='$color'><font size=1>".$usr["institucion"]."</font></td>                           
                      </tr>";
              else{
               $html_copia.="<tr height='15' onmouseover=\"this.style.background='#e3e8ec'\" onmouseout=\"this.style.background='white', this.style.color='black'\">
                        <td bgcolor='white'><font size=1><b>$nom</b></font></td>
                        <td bgcolor='white'><font size=1>$tipo_usr</font></td>
                        <td bgcolor='$color'><font size=1><b>".$inactivo."</b> ".$usr["nombre"]."</font></td>
                        <td bgcolor='$color'><font size=1>".$usr["titulo"]."</font></td>
                        <td bgcolor='$color'><font size=1>".$usr["cargo"]."</font></td>
                        <td bgcolor='$color'><font size=1>".$usr["institucion"]."</font></td>                           
                      </tr>";
                $nom = "";
                
                }
               }
            }
            
        }
       echo $html_incopia.$html_copia;
    ?>
</table>
</body>
