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
/*************************************************************************************************
**  En esta pagina se  define la opciones de impresiònm para oficios             		**
**												**
**  PARAMETROS:											**
**    	$accion		Accion a realizar: Nuevo - Responder - Editar				**
**    	$ent		(opcional, nuevo) si es un documento de entrada=2 o de salida=1 	**
**    	$nurad		(opcional, edicion o modificacion) es el numero de radicado		**
**    	$textrad	(opcional, edicion o modificacion) es el texto del radicado		**
**												**
**  INCLUDES:											**
**	../include/db/ConnectionHandler.php	Maneja las conexiones con la BDD		**
**	secciones_tipos_doc.php			Funcion Javascript que define q campos se 	**
**						mostraran en la pagina				**
**												**
**												**
**												**
**												**
**                                                                                              **
**                                                                                              **
**************************************************************************************************/

session_start();
$ruta_raiz = "..";
include_once "$ruta_raiz/rec_session.php";
include_once "$ruta_raiz/obtenerdatos.php";
include_once "$ruta_raiz/funciones_interfaz.php";
require_once "$ruta_raiz/tx/tx_actualiza_opcion_imp.php";

//Variables
$verrad = limpiar_numero($_POST['verRadicado']);
$raditipo = limpiar_sql($_POST['radiTipoDoc']);
$codiDestinatario = limpiar_sql($_POST['codiDest']);
$cmb_tipo_imp=limpiar_sql($_POST['cmb_tipo_imp']);
$accion=limpiar_sql($_POST['accion']);
$numDest = 1;

if(strpos($codiDestinatario, "--") !== false)
    $numDest = 0;

/*  require_once "$ruta_raiz/include/db/ConnectionHandler.php";
  $db2 = new ConnectionHandler($ruta_raiz);*/

$registro = ObtenerDatosRadicado($verrad,$db);
//echo "destinatario--$codiDestinatario";

if (($raditipo==1 or $raditipo==6) and $numDest == 1)
    $usuadest = ObtenerDatosUsuario(str_replace("-","",$codiDestinatario),$db);

/*else
    $usuadest = ObtenerDatosUsuario(str_replace("-","",$registro["usua_rem"]),$db);*/

//Obtengo Datos Iniciales
$chk_cabecera="0";
$chkPiePag = "checked";
$chk_izquierda="0";
$idSFrase=0;
$sinTit=0;
$sinCargo=0;
$cargo="";

$titulo = 'Sin título';
$cargo='Sin Puesto Cabecera';
$direccion = 'Sin dirección';
$institucion = 'Sin institución';
/*
 * Por David Gamboa
 * Solo para oficios debe ser la leyenda. En su despacho.
 */
if ($raditipo==1) //tipo de documento es oficio
    $saludo = "En su Despacho";
elseif ($raditipo==6)
    $saludo = "Ciudad.-";
else
    $saludo = "Presente.";
$despedida = "Atentamente,";
$frasedespedida = "Sin frase de despedida";
$chkOculta = "0";
$chkJustificaFir = "0";
$chkOcultaFrase = "0";
$chkOcultaAtentamente = "0";
$modifica="";


if($usuadest) {
    $idUser=$usuadest['usua_codi'];
    $cargo=$usuadest['cargo'];
    $ci=$usuadest['cedula'];
    $inst_codidest=$usuadest['inst_codi'];
    if(trim($usuadest['titulo'])!='')
        $titulo = $usuadest['titulo'];
    $nombre = $usuadest['nombre'];
    //$cargo = $usuadest['cargo'];//cargo_cabecera
    $tipoUser = $usuadest['tipo_usuario'];
    
    if($tipoUser==1 ) {
        if(trim($usuadest['cargo_cabecera'])!='')
            $cargo = $usuadest['cargo_cabecera'];
    } elseif($tipoUser==2 ){
        if(trim($usuadest['cargo'])!='')
           $cargo = $usuadest['cargo'];
    }

    if(trim($usuadest['direccion'])!="")
        $direccion = $usuadest['direccion'];
    if(trim($usuadest["institucion"])!='')
        $institucion = $usuadest["institucion"];
}

if(trim($usuadest["depe_codi"]) != '')
{
    $area = ObtenerDatosDependencia($usuadest["depe_codi"],$db);
    $ciudadUsua = ObtenerCiudadUsua(' usuarios left outer join ciudad on ciu_codi = id ', ' usua_codi = '.$usuadest['usua_codi'], $db);
    if(trim($ciudadUsua['nombre']) != '')
        $area['ciudad'] = $ciudadUsua['nombre'];
}

// Obtener datos de la institucion para frase de despedida
$datInstitucion = ObtenerDatosInstitucion($_SESSION['inst_codi'],$db);

if($frasedespedida=="Sin frase de despedida" and $datInstitucion['fraseDespedida']!="")
    $frasedespedida = $datInstitucion['fraseDespedida'];

//Verifico Existencia obtener datos de opciones de impresion
if ($accion!='Copiar')
 $rsOpcImprBusq = ObtenerDatosOpcImpresion($verrad,$db);
$opcTitulo = $titulo;
//CARGO
$opcCargo=$cargo;
$opcDireccion = $direccion;
$opcSaludo = $saludo;
$opcDespedida = $despedida;
$opcFrasedespedida = $frasedespedida;

//if ($codiDestinatario==$registro["usua_dest"]) 
//    $rsOpcImprBusq = ObtenerDatosOpcImpresion($verrad,$db);
//else
//    $rsOpcImprBusq = ObtenerDatosOpcImpresion(0,$db);

    if ($rsOpcImprBusq['RADI_NUME_RADI'] !=""){
        $modifica = "M";
        if(trim($rsOpcImprBusq['TITULO_NATURAL'])!="")
            $opcTitulo = $rsOpcImprBusq['TITULO_NATURAL'];
        //CARGO
        if($tipoUser==1 ){//Funcionario
            if(trim($rsOpcImprBusq['CARGO_CABECERA'])!="")
                $opcCargo = $rsOpcImprBusq['CARGO_CABECERA'];
        }else
            $opcCargo =$cargo;


        if($rsOpcImprBusq['FIRMANTES']!="")
            $opcFirmantes = $rsOpcImprBusq['FIRMANTES'];
        if($rsOpcImprBusq['EXT_INSTITUCION']!="")
            $opcExtInstitucion = $rsOpcImprBusq['EXT_INSTITUCION'];
        if($rsOpcImprBusq['DESTINO_DESTINATARIO']!="")
            $opcSaludo=$rsOpcImprBusq['DESTINO_DESTINATARIO'];
        if($rsOpcImprBusq['FRASE_DESPEDIDA']!="")
            $opcDespedida=$rsOpcImprBusq['FRASE_DESPEDIDA'];
        if($rsOpcImprBusq['FRASE_REMITENTE']!="")
            $opcFrasedespedida=$rsOpcImprBusq['FRASE_REMITENTE'];
        if($rsOpcImprBusq['OCULTAR_NUME_RADI']==1)
            $chkOculta="checked";
        if($rsOpcImprBusq['MOSTRAR_PARA']==1)
            $chk_cabecera = $rsOpcImprBusq['MOSTRAR_PARA'];
        //echo $chk_cabecera;
        if($rsOpcImprBusq['JUSTIFICAR_FIRMA']==1)
            $chkJustificaFir="checked";        
        if($rsOpcImprBusq["OCULTA_FRASE_REMITENTE"]==1)
            $chkOcultaFrase="checked";
        //rescato la opcion de impresion nota
        $opc_tipo_nota = $rsOpcImprBusq["OPC_IMP_TIPO_NOTA"];
        $chkJustificaFech=$rsOpcImprBusq["OPC_JUSTIFICAR_FECHA"];
        //ocultar asunto
         if($rsOpcImprBusq['OCULTAR_ASUNTO']==1)
            $chkAsunto="checked";
        //ocultar asunto
         if($rsOpcImprBusq['LETRA_ITALICA']==1)
            $chkItalica="checked";
         //ocultar Atentamente
        
         if($rsOpcImprBusq["OCULTAR_ATENTAMENTE"]==1)
            $chkAtentamente="checked";
         //ocultar anexo
          if($rsOpcImprBusq["OCULTAR_ANEXO"]==1)
            $chkAnexo="checked";
          //ocultar referencia
           if($rsOpcImprBusq["OCULTAR_REFERENCIA"]==1)
            $chkReferencia="checked";
           //ocultar sumilla
           if($rsOpcImprBusq["OCULTAR_SUMILLAS"]==1)
            $chkSumillas="checked";
    }
if ($raditipo==6){
    if ($opc_tipo_nota=='' || $opc_tipo_nota==0){
        $opc_tipo_nota = 3;

    }
    if ($opc_tipo_nota==3)
    $chk_cabecera = 0;
//else
//    $chk_cabecera = 1;
}

if ($chkJustificaFech=='' || $chkJustificaFech==2 || $chkJustificaFech==3)
    $chkJustificaFech=0;
if ($raditipo!=6){
    if($chk_cabecera==1)
        $visible = ' style="visibility:hidden" ';
    else
        $visible = "";
}else
    $visible="";

echo "<html>".html_head();
?>

<input type="hidden" id="cargoDest" name="cargoDest" value="<?=$cargo?>">
<input type="hidden" id="tituloDest" name="tituloDest" value="<?=$opcTitulo?>">
<input type="hidden" id="tipoNota" name="tipoNota" value="<?=$opc_tipo_nota?>">

<br/><br/>
<table>
    <tr><td width="100%">
            <?php if ($raditipo==6){ ?>
    <input type="radio" name="radio_nota" id="radio_nota" value ="3" onclick="verOpcionNota(3); histop('k',this,<?="'".$opc_tipo_nota."'"?>);" <?php if ($opc_tipo_nota==3){ ?> checked<?php }?>><?php echo desc_tipo_nota(3);?>
    <input type="radio" name="radio_nota" id="radio_nota" value ="1" onclick="verOpcionNota(1); histop('k',this,<?="'".$opc_tipo_nota."'"?>);" <?php if ($opc_tipo_nota==1){ ?> checked<?php }?>><?php echo desc_tipo_nota(1);?>
    <input type="radio" name="radio_nota" id="radio_nota" value ="2" onclick="verOpcionNota(2); histop('k',this,<?="'".$opc_tipo_nota."'"?>);" <?php if ($opc_tipo_nota==2){ ?> checked<?php }?>><?php echo desc_tipo_nota(2);?>
                <?php }?>
        </td></tr>
</table>
<table border ="1" width="100%" cellpadding="10" >
    <tr><td width="100%">
<!--            <input type="text" id="codiOpcImp" name="codiOpcImp" value="<?=$rsOpcImprBusq['OPC_IMP_CODI']?>">-->
            <input type="hidden" id="txt_direccion" name="txt_direccion" value="<?=$direccion?>">
            <input type="hidden" id="txt_tipouser" name="txt_tipouser" value="<?=$tipoUser?>"/>
            <input type="hidden" id="idUser" name="idUser" value="<?=$idUser?>"/>
            <input type="hidden" id="ci" name="ci" value="<?=$ci?>"/>
            <input type="hidden" id="inst_codidest" name="inst_codidest" value="<?=$inst_codidest?>"/>

        <br>
    <div id="div_opciones_generales" >
         <?php 
         //--
         if ($raditipo!=6){ ?><fieldset  class="borde_tab">
           <legend>OPCIONES GENERALES DEL DOCUMENTO</legend>
         <?php }
         //--?>
            <table width="100%"  align="center" border="0" cellspacing="0" cellpadding="3" rules="rows" >
                <tr <?php if ($raditipo!=1 and $raditipo!='' || $raditipo==6) echo "style='display: none;'"?>>
                    <td colspan="2">
                        <input type="checkbox" name="chk_documento" id="chk_documento" value="1"  <?php echo $chkOculta?> onclick="histop('a',this,<?="'".$chkOculta."'"?>);"/>
                        Ocultar N&uacute;mero de Oficio
                    </td>
                </tr>
                <tr <?php if ($raditipo==6) echo "style='display: none;'"?>>
                    <td colspan="2" <?php if ($raditipo==6) echo "style='display: none;'"?>>
                        <input type="checkbox" name="chk_asunto" id="chk_asunto" value="1"  <?php echo $chkAsunto?> onclick="histop('o',this,<?="'".$chkAsunto."'"?>);"/>
                        Ocultar Asunto
                    </td>
                </tr>
                <tr <?php if ($raditipo==6) echo "style='display: none;'"?>>
                    <td colspan="2">
                        <input type="checkbox" name="chk_italica" id="chk_italica" value="1" <?php echo $chkItalica?> onclick="histop('li',this,<?="'".$chkItalica."'"?>);"/>
                        Imprimir documento en letra it&aacute;lica
                    </td>
                </tr>  
                
                 <tr id="div_fecha_documento" <?php if ($raditipo!=6) echo 'style="display:none"'; elseif ($opc_tipo_nota==3) echo 'style="display:"'; else echo 'style="display:none"'; ?>>
                    <td class="listado1_ver" width="13%">Ubicar fecha en:</td>
                    <td>
                      <?php echo '<input type="radio" name="radio_just_fecha" id="radio_just_fecha" value="1" ';                      
                      echo 'checked';
                      echo '>Pie de Página';
                      ?>
                    </td>
                </tr>
                 <tr <?php if ($raditipo==6) echo "style='display: none;'"?>>
                    <td colspan="2">
                        <input type="checkbox" name="chk_referencia" id="chk_referencia" value="1" <?php echo $chkReferencia?> onclick="histop('re',this,<?="'".$chkReferencia."'"?>);"/>
                        Ocultar Referencia
                    </td>
                </tr>
                <tr <?php if ($raditipo==6) echo "style='display: none;'"?>>
                    <td colspan="2">
                        <input type="checkbox" name="chk_anexo" id="chk_anexo" value="1" <?php echo $chkAnexo?> onclick="histop('ax',this,<?="'".$chkAnexo."'"?>);"/>
                        Ocultar Anexos
                    </td>
                </tr>
                <tr <?php if ($raditipo==6) echo "style='display: none;'"?>>
                    <td colspan="2" <?php if ($raditipo==6) echo "style='display: none;'"?>>
                        <input type="checkbox" name="chk_sumillas" id="chk_sumillas" value="1"  <?php echo $chkSumillas?> />
                        Ocultar Sumilla
                    </td>
                </tr>
            </table>
          <?php if ($raditipo!=6){ ?></fieldset> <?php } ?>
    </div>
             
    <br>    
        <fieldset style="display:<?=$_POST['verDest']?>" class="borde_tab" id="D">
            <legend>INFORMACIÓN DESTINATARIO</legend>
            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="3" rules="rows">            
              
            <tr>
                <td class="listado1_ver" width="13%">Ubicar destinatario en:</td>
                <td width="20%">
                    <!-- Codigo del usuario destino del documento -->
                    <input id="usuaCodi" name="usuaCodi" type="hidden" value="<?=$usuadest['usua_codi']?>"/>
                    
                    <?php
                    //echo $chk_cabecera;
                     if ($raditipo==6){
                            if ($chk_cabecera==0){
                                $valueCabecera = "Cabecera";
                                echo '<input type="radio" name="radio_cabecera" id="radio_cabecera" value="0" checked/> ' ;
                            }
                            else{
                                $valueCabecera = "Pie de Página";
                                 echo '<input type="radio" name="radio_cabecera" id="radio_cabecera" value="1" checked/> ' ;
                            }

                            
                            echo '<input type="text" id="txt_pie_cabecera" name="txt_pie_cabecera" class="text_transparente" value="'.$valueCabecera.'" readonly>';
                     }else{
                         
                         
                         echo '<input type="hidden" id="txt_pie_cabecera" name="txt_pie_cabecera" value="">';
                         $funcionjava = 'histop(\'b\',this,'.$chk_cabecera.');"';
                         
                         echo '<input type="radio" name="radio_cabecera" id="radio_cabecera" value="0" onclick="muestraBtnRemitente(0); '.$funcionjava;
                      if ($chk_cabecera == "0") echo $chkPiePag;
                      echo '>Cabecera';?>
                      <?php echo '<input type="radio" name="radio_cabecera" id="radio_cabecera" value="1" onclick="muestraBtnRemitente(1); '.$funcionjava;
                      if ($chk_cabecera == "1") echo $chkPiePag;
                      echo '>Pie de Página';
                     }
                      ?>
                                       
                </td>
                <td></td>
            </tr>            
            <tr>
                <td></td>
                <td class="listado1_ver" align="center"><font size="2">Datos de Destinatario</font></td>
                
                <td class="listado1_ver" align="center"><font size="2">Datos a Imprimir  <?php if ($raditipo==6) echo "en Nota"; else echo "en el Oficio";?></font></td>
                
            </tr>
            <tr id="T" <?php if ($cmb_tipo_imp==2) echo "style='display:none'"; ?>>
                <td class="listado1_ver" width="13%">Título:</td>
                <td width="25%">
                   <?php
                       if($titulo=='Sin título'){
                          $sinTit=1;
                       }
                       $long4=(strlen($titulo));
                       
                   ?>
                   <input type="text" id="txt_titulo" name="txt_titulo" class="text_transparente" size="<?=$long4?>" value="<?=$titulo?>" readonly/>
                </td>
                <td width="30%">
                    <?php if (isset($_POST['blanquear_cambiar_titulo'])){//siempre es uno                        
                       $opcTitulo=$titulo;
                    }//else//if($raditipo==6 and ($opc_tipo_nota==3 || $opc_tipo_nota==''))
                    
                    if ($raditipo==6)  
                        if (trim($opcTitulo)=='' || trim($opcTitulo)=='Sin título' || ($opcTitulo==$titulo)) 
                            if ($opc_tipo_nota==3 || $opc_tipo_nota=='')
                           $opcTitulo="A la Honorable,";                         
                    ?>
                    <input type="text" id="txt_opcTitulo" name="txt_opcTitulo" size="40" class="text_transparente" value="<?=$opcTitulo?>" onblur="deshabilitaObj('tit'); copia(); histop('c',this,<?="'".$opcTitulo."'"?>);" readonly/>
                </td>
                <td width="2%">
                    <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" onclick='habilitaObj("tit");' name="Image1" align="middle" border="0" title="Modifica el título del destinatario"/>
                </td>
                
                <td width="30%"></td>
            </tr>
           <tr id="N" <?php if ($cmb_tipo_imp==2 || $cmb_tipo_imp==6) echo "style='display:none'"; ?>>
                <td class="listado1_ver">Nombre:</td>
                <td class="caja_textoSinBorde"><?php echo $nombre;?></td>
                <td class="caja_textoSinBorde">
                    <?=$nombre?>&nbsp;<input id="txt_opcFirmantes" name="txt_opcFirmantes" type="text" <?=$visible?> class="text_transparente" value="<?=$opcFirmantes?>" onblur="deshabilitaObj('firm');" onkeypress="histop('d',this,'');" readonly/>
                    <input id="txt_nombre_ciu" name="txt_nombre_ciu" type="hidden" value="<?=$nombre?>"/>
                </td>                
                <td width="2%">
                    <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" onclick='habilitaObj("firm");' <?=$visible?> name="ImgFirm" id="ImgFirm" align="middle" border="0" title="Añadir texto al destinatario">
                </td>                
                <td>
                <?php 
                //usuario con firma                
                if (isset($_POST['editarCiudadano']))                    
                    $editarCiudadano = $_POST['editarCiudadano'];                
                if ($editarCiudadano==1){
                    if ($inst_codidest==0){
                        if($tipoUser!='1' and $_SESSION["usua_perm_ciudadano"]==1) 
                            echo '<a href="#" class="vinculos" onclick="editarCiudadano();">Editar Ciudadano</a>';
                    }                
                } ?>
                </td>
                
            </tr>
            <tr id="trCargo" <?php if ($cmb_tipo_imp==5) echo "style='display:none'"; ?>>
                <td class="listado1_ver">Puesto:</td>
                <td width="25%">
                   <?php                   
                       if($cargo=='Sin Puesto Cabecera'){
                          $sinCargo=1;
                       }

                       $long5=(strlen($cargo));
                   ?>
                    <input type="text" id="txt_Cargo" name="txt_Cargo" class="text_transparente" size="<?=$long5?>" value="<?=$cargo?>" readonly/>
                </td>
                <td width="30%">
                     <?php if (isset($_POST['blanquear_cambiar_titulo'])){//siempre es uno                        
                          $opcCargo=$cargo;
                    } ?>
                    <input type="text" id="txt_opcCargo" name="txt_opcCargo" size="40" class="text_transparente" value="<?=$opcCargo?>" onblur="deshabilitaObj('carg'); histop('e',this,<?="'".$opcCargo."'"?>);" readonly/>
                </td>
                <td width="2%">
                    <?php if($tipoUser==1 ){//Funcionario?>
                    <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" onclick='habilitaObj("carg");' name="Image1" align="middle" border="0" title="Modifica el Puesto de la cabecera del documento.">
                    <?}?>
                </td>
                <td width="30%"></td>
            </tr>
            <tr id="trInstitucion" <?php if ($cmb_tipo_imp==4) echo "style='display:none'"; ?>>
                <td class="listado1_ver" >Institución:</td>
                <td><?php
                    $long=(strlen($institucion));                    
                    ?>
                    <input type="text" name="txt_institucion" class="text_transparente" id="txt_institucion" size="<?=$long?>" value="<?=$institucion?>" />
                </td>
                <td class="listado1">
                    <font size="1"><?=$institucion?>&nbsp;</font><input type="text" size="30" class="text_transparente" <?=$visible?> name="txt_ext_institucion" id="txt_ext_institucion" value="<?=$opcExtInstitucion?>" onblur="deshabilitaObj('ins')" readonly/>
                </td>
                <td>
                    <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" name="ImgInst" id="ImgInst" <?=$visible?> align="middle" border="0" title="Añade frase a la institución" onclick="habilitaObj('ins')">
                </td>
                
            </tr>
            </table>
            </fieldset>
        <br>
        <fieldset  class="borde_tab">
            <legend>SALUDO</legend>
            <table>
                <tr>
                    <td class="listado1_ver" width="13%">Saludo:</td>
                    <td width="25%">
                       <input type="text" id="txt_saludo" name="txt_saludo" class="text_transparente" value="<?=$saludo?>" readonly/>
                    </td>
                    <td width="30%">
                        <input type="text" id="txt_opcSaludo" name="txt_opcSaludo" class="text_transparente" size="30" value="<?=$opcSaludo?>" onblur="deshabilitaObj('sal'); histop('f',this,<?="'".$opcSaludo."'"?>);" readonly>
                    </td>
                    <td>
                        <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" name="Image1" align="middle" border="0" title="Modifica el saludo" onclick="habilitaObj('sal')">
                    
                        <img src="<?=$ruta_raiz?>/imagenes/internas/application_home.png" name="Image1" align="middle" border="0" title="Añade la ciudad ó dirección del funcionario ó ciudadano (Destinatario)" onclick="habilitaObj('dir')">
                    </td>
                                   
                </tr>
            </table>
           
        </fieldset>
        
        <br>
        
        <div id ="div_despedida_firmante" <?php if($opc_tipo_nota=='') echo "style='display:block'"; elseif ($opc_tipo_nota==3 && ($raditipo==6)) echo "style='display:none'";?>>
        <fieldset class="borde_tab">
        <legend>DESPEDIDA FIRMANTE</legend>
        <table width="100%" align="center" border="0" cellspacing="0" cellpadding="3" rules="rows">
            <tr>
                <td class="listado1_ver" width="13%" >Ubicar firma:</td>
                <td align="left" colspan="2">
                    <?php 
                    
                    if ($rsOpcImprBusq['JUSTIFICAR_FIRMA']=='')
                        $rsOpcImprBusq['JUSTIFICAR_FIRMA']=0;
                    $funcionjava = 'onclick="histop(\'g\',this,'.$rsOpcImprBusq['JUSTIFICAR_FIRMA'].');"';
                    if ($raditipo==6){
                        
                        $chkJustificaFir=1;//centro
                                
                                $valueJustifi = "Centro";

                            echo '<input type="radio" name="radio_justifi" id="radio_justifi" value="'.$chkJustificaFir.'" checked> ' ;
                            echo '<input type="text" id="txt_firma_justifi" name="txt_firma_justifi" class="text_transparente" value="'.$valueJustifi.'" readonly >';
                    }else{
                     if ($rsOpcImprBusq['JUSTIFICAR_FIRMA']==0){
                      echo '<input type="radio" name="radio_justifi" '.$funcionjava.' id="radio_justifi" value="0" checked>Izquierda';
                      echo '<input type="radio" name="radio_justifi" '.$funcionjava.' id="radio_justifi" value="1" '.$chkJustificaFir.'>Centro';
                     }else{
                      echo '<input type="radio" name="radio_justifi" '.$funcionjava.' id="radio_justifi" value="0" '.$chkJustificaFir.'>Izquierda';
                      echo '<input type="radio" name="radio_justifi" '.$funcionjava.' id="radio_justifi" value="1" checked>Centro';
                     }
                    }?>
                </td>
            </tr>
             <?php 
             
             if ($raditipo!=6){
                 ?>            
            <tr>
                <td class="listado1_ver" width="13%">Despedida:</td>
                <td width="25%">
                   <input type="text" id="txt_despedida" name="txt_despedida" class="text_transparente" value="<?=$despedida?>" readonly/>
                </td>
                <td colspan="2" ><?$long1=(strlen($opcDespedida));?>
                    <input type="text" id="txt_opcDespedida" name="txt_opcDespedida" size="30" class="text_transparente" size="<?=$long1?>" value="<?=$opcDespedida?>" onblur="deshabilitaObj('des'); histop('h',this,<?="'".$opcDespedida."'"?>);" readonly/>
                </td>
                <td width="2%">
                    <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" name="Image1" align="middle" border="0"   title="Modifica la despedida del documento" onclick="habilitaObj('des')"/>
                </td>                
                
                <td class="listado1_ver" width="25%">Ocultar Atentamente: <input type="checkbox" name="chk_OcultaAtentamente" id="chk_OcultaAtentamente" value="1" <?echo $chkAtentamente?> onclick="histop('j',this,<?="'".$chk_OcultaAtentamente."'"?>);"> </td>
            </tr>
            <tr>
                <td width="13%" class="listado1_ver">Frase despedida:</td>
                 <td width="25%">
                    <? $long2=(strlen($opcFrasedespedida)); ?>
                   <input type="text" id="txt_frasedespedida" name="txt_frasedespedida" class="text_transparente" size="<?=$long2?>" value="<?=$frasedespedida?>" readonly/>
                </td>
                <td colspan="2">
                    <?
                    if($opcFrasedespedida=="")                        
                        $sinFrase = 1;                    
                    $long2=(strlen($opcFrasedespedida));
                    ?>
                    <input type="text" id="txt_opcFrasedespedida" name="txt_opcFrasedespedida" class="text_transparente" size="<?=$long2?>" value="<?=$opcFrasedespedida?>" onblur="deshabilitaObj('fra'); histop('i',this,<?="'".$opcFrasedespedida."'"?>);" readonly/>
                </td>                
                <td width="2%">
                    <img src="<?=$ruta_raiz?>/imagenes/internas/pencil_add.png" name="Image1" align="middle" border="0" title="Añade una frase a la despedida" onclick="habilitaObj('fra');"/>
                </td>               
                <td class="listado1_ver" width="25%">Ocultar frase despedida: <input type="checkbox" name="chk_opcFraseDespedida" id="chk_opcFraseDespedida" value="1" <?echo $chkOcultaFrase?> onclick="histop('j',this,<?="'".$chkOcultaFrase."'"?>);"> </td>
             </tr>
             <?php } ?>
        </table>
        </fieldset>
        </div>
        
        <br>
    </td></tr>
</table>
</body>
</html>
