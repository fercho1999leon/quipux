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

$ruta_raiz="../..";

//Archivo que permite buscar el destinatario y remitente para los documentos
session_start();
include_once "$ruta_raiz/rec_session.php";

include_once("$ruta_raiz/obtenerdatos.php");
include_once "$ruta_raiz/funciones.php";

if (!$buscar_inst) $buscar_inst="0";
if (!$buscar_depe) $buscar_depe="0";
if (!$lista_usr) $lista_usr="0";
$usuarios_lista = "";

?>

<table class=borde_tab width="100%" cellpadding="0" cellspacing="4">
    <tr class="grisCCCCCC" align="center">
        
        <td width="20%" class="titulos5">Subrogante</td>
        <td width="20%" class="titulos5">Instituci&oacute;n</td>
        <td width="5%"  class="titulos5">T&iacute;tulo</td>
        <td width="10%"  class="titulos5"><?=$descCargo?></td>
        <td width="10%" class="titulos5">&Aacute;rea</td>
        <td width="5%"  class="titulos5">E-mail</td>
        <td width="18%" class="titulos5">Periodo</td>
        <td width="10%" class="titulos5">Subrogado</td>
        <td width="5%" class="titulos5">Acción</td>
    </tr>
<?
//$buscar_nom = trim(limpiar_sql($buscar_nom));
$cedulaFinal = str_replace(" ", '', $buscar_nom);
$buscar_nom = trim(limpiar_sql($buscar_nom));

$buscar_car = trim(limpiar_sql($buscar_car));
$arr_buscar = explode(" ", $buscar_nom);
$cuenta = count(explode(' ', $buscar_nom));
//echo $cuenta."<br>";
$i=1;
foreach ($arr_buscar as $tmp) {
    if ($tmp != "") {       
        $subrogante.= " ( translate(UPPER(usr1.usua_nombre),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') 
LIKE translate(upper('%$tmp%'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') ) or ";
    }
   
}
$cedulaSubnte = " ( translate(UPPER(usr1.usua_cedula),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') 
LIKE translate(upper('%$cedulaFinal%'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') ) or ";
////FILTRO 2
foreach ($arr_buscar as $tmp) {
    if ($tmp != "") {   
        if ($i<$cuenta)
        $subrogado.= "  ( translate(UPPER(usr2.usua_nombre),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') 
LIKE translate(upper('%$tmp%'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') ) or ";
        else
            $subrogado.= "  ( translate(UPPER(usr2.usua_nombre),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') 
LIKE translate(upper('%$tmp%'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') ) ";
    }
     $i++;
}
$cedulaSubdo = "or ( translate(UPPER(usr2.usua_cedula),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') 
LIKE translate(upper('%$cedulaFinal%'),'ÁÉÍÓÚÀÈÌÒÙÄËÏÖÜÑ','AEIOUAEIOUAEIOUN') ) ";
$sql="";
$likeif = $subrogante.$cedulaSubnte.$subrogado.$cedulaSubdo;
if (($buscar_nom!="" or $buscar_car!="" or $buscar_inst!="0" or $buscar_depe!="0")) {

    $sql = "select ug1.usua_fecha_inicio,ug1.usua_fecha_fin
            --subrogante
            ,ug1.usua_subrogante,usr1.usua_nombre as subrogante
            ,usr1.inst_nombre,usr1.depe_nomb,usr1.usua_titulo, usr1.usua_cargo
            , usr1.inst_sigla as instsubro,usr1.usua_email
            --subrogado
            ,usr2.usua_codi as usua_subrogado, usr2.usua_nombre as subrogado
            ,usr2.inst_nombre,usr2.depe_nomb,usr2.usua_titulo, usr2.usua_cargo
            ,ug1.usua_subrogacion_codi, usr2.inst_sigla as instsubre 
            from usuarios_subrogacion ug1 
            left outer join usuario usr1 on usr1.usua_codi = ug1.usua_subrogante
            left outer join usuario usr2 on usr2.usua_codi = ug1.usua_subrogado
            where ug1.usua_visible = 1
            and usr1.usua_codi <> 0 ";
    if ($buscar_nom!='')
    $sql.= " and ( $subrogante $cedulaSubnte $subrogado $cedulaSubdo)";
    
    //PARA GENERAR BUSQUEDA SIN ORDEN DE NOMBRE
    if ($buscar_car!='')
    $sql .= ' and ' . buscar_cadena($buscar_car, "usr1.usua_cargo");
    if ($buscar_inst != "0" && ($buscar_tipo == 1 || $buscar_tipo == 0)) {
        $sql .= " and usr1.inst_codi=$buscar_inst";
    }
    $depe_codi_admin = obtenerAreasAdmin($_SESSION["usua_codi"],$_SESSION["inst_codi"],$_SESSION["usua_admin_sistema"],$db);
    
    if ($buscar_depe != 0) 
        $sql .= " and (usr1.depe_codi=$buscar_depe or usr2.depe_codi=$buscar_depe)";
    else
        if ($depe_codi_admin!=0)
        $sql .= " and (usr1.depe_codi in ($depe_codi_admin) or usr2.depe_codi in ($depe_codi_admin))";
    $sql .= " order by usr1.depe_codi,usr1.usua_nombre";//comentado por David Gamboa, requerimiento quitar el limit
    //$sql .= " order by u.usua_nombre asc limit 300 offset 0";
//echo $sql;
}else{
    echo "<tr><td colspan=12><font color='red'><center>Ingrese Cédula o Nombre</center></font></td>";
}

if ($sql!="") {
    $rs=$db->query($sql);
    $i=0;
    
  
    while(!$rs->EOF)
    {
        $codigo = trim($rs->fields["USUA_SUBROGANTE"]);
        $codigo_subrogado = trim($rs->fields["USUA_SUBROGADO"]);
        
        
        
           
?>
    <tr onmouseover="this.style.background='#e3e8ec'" onmouseout="this.style.background='white', this.style.color='black'">
        <?php if ($dependencia_cod!=$dependencia_color and $i!=0){?>
            <tr bgcolor="#E2E7EB"><td colspan="10"><hr></td></tr>
        <?php }?>
        
        <td><font size=1><?=substr($rs->fields["SUBROGANTE"],0,120)."/".$rs->fields["INSTSUBRO"]; ?></font></td>
        <td><font size=1><?=substr($rs->fields["INST_NOMBRE"],0,100) ?></font></td>
        <td><font size=1><?=substr($rs->fields["USUA_TITULO"],0,70) ?></font></td>
        <td><font size=1><?=$rs->fields["USUA_CARGO"] ?> </font></td>
        <td><font size=1><?=$rs->fields["DEPE_NOMB"] ?></font></td>
        <td><font size=1><?=$rs->fields["USUA_EMAIL"] ?></font></td>
        <td><font size=1><?php echo "Desde: ".substr($rs->fields["USUA_FECHA_INICIO"],0,16)."<br>Hasta : ".substr($rs->fields["USUA_FECHA_FIN"],0,16); ?></font></td>
        <td><font size=1><?=substr($rs->fields["SUBROGADO"],0,120)."/".$rs->fields["INSTSUBRE"];?></font></td>
        <td width="6%" align="center" valign="middle" ><font size=1>
         <input class='botones_azul' title='Desactivar' type='button' value='Desactivar' onClick="desactivar('<?=$codigo?>','<?=$codigo_subrogado?>');"></font>
        </td>
                 
    </tr>
  <?
        $i++;
        $dependencia_color = trim($rs->fields["DEPE_CODI"]);
        $rs->MoveNext();
    }
}
?>

</table>
<textarea id="usuarios_lista" name="usuarios_lista" style="display: none" cols="1" rows="1"><?=$usuarios_lista?></textarea>
