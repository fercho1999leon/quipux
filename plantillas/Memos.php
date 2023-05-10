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
/**
*	Autor			Iniciales		Fecha (dd/mm/aaaa)
*	Mauricio Haro		MH
*
*	Modificado por		Iniciales		Fecha (dd/mm/aaaa)
*	Santiago Cordovilla	SC			19-12-2008
*
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
*	Santiago Cordovilla	SC			19-12-2008
*	Sylvia Velasco		SV			14-01-2009
*       Modificado por          Notas Consulares
*       David Gamboa            DG                      08-06-2011 
**/

$firma='';
$td=$registro['radi_tipo'];

$inicio = '
<html>
<head>
<title>'.$registro['radi_nume_text'].'</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body style="text-align: justify;">';

$texto = $registro['radi_texto'];
$texto = str_replace("<br","&nbsp<br",$texto);
$texto = str_replace("<BR","&nbsp<BR",$texto);
$texto = str_replace("text-align: left;","text-align: justify;",$texto);
$texto = str_replace("text-align:left;","text-align: justify;",$texto);


//TITULO ASUNTO
if ($registro['radi_tipo']==3) 
    $titulo_asunto = "ASUNTO: "; 
else 
    $titulo_asunto = "Asunto: ";
if ($registroOpcImpr["OCULTAR_ASUNTO"]==0)
    $asunto = "<br><br><b>$titulo_asunto</b> $asunto<br>&nbsp;<br>";
else
    $asunto="";
/** Creación de la plantilla dependiendo del tipo de documento **/

switch ($td){
    case "6":	//Notas consulares
            $institucionNota=strtoupper2($institucionNota);
            $institucionNota = "<br><b>$institucionNota</b><br>"; 
            if ($opc_imp_nota==3 || $opc_imp_nota==""){//si es verbal
                $cuerpo .="<br/>
                $texto";
                
            }else{//si no imprime los tados del destinatario
              if ($opc_imp_nota==1){//diplomatica                
                $cuerpo .="<br/>
                <br>&nbsp;<br>
                $usuadest<br/><br>
                $destinoD<br/><br>&nbsp;<br>
                $texto";
              }elseif($opc_imp_nota==2){                  
                    $destinatarioNota2="$usuadest<br/><br>$destinoD<br/>";                    
                    $cuerpo .="<br/>$texto";
              }
            }
            break;
    case "7":	//Cartas ciudadanos
    case "1":	//OFICIOS
//    if($registroOpcImpr["MOSTRAR_PARA"]==1){
//            $destinoD="";
//    }
//    else {
//        if($registroOpcImpr["DESTINO_DESTINATARIO"]!=""){
//            $destinoD1=$registroOpcImpr["DESTINO_DESTINATARIO"];
//            $destinoD=trim($destinoD1)."<br/>&nbsp;<br/>&nbsp;<br/>";
//        }else
//            $destinoD="En su Despacho.<br/>&nbsp;<br/>&nbsp;<br/>";
//    }

    if($registroOpcImpr["MOSTRAR_PARA"]==1){
        $cuerpo .="<br/>$texto";
        $cuerpo1 .="<br/>&nbsp&nbsp&nbsp;<br/>$usuadest<br/>
        $destinoD";
    }else{
           if($registroOpcImpr["DESTINO_DESTINATARIO"]!=""){
            $destinoD1=$registroOpcImpr["DESTINO_DESTINATARIO"];
            $destinoD=trim($destinoD1)."<br/>&nbsp;<br/>&nbsp;<br/>";
        }else
            $destinoD="En su Despacho.<br/>&nbsp;<br/>&nbsp;<br/>";
           $cuerpo1="";
           $cuerpo .="<br/>
           $asunto<br>&nbsp;<br>
           $usuadest<br/><br>
           $destinoD<br/>        
           $texto";
    }
	break;

    case "3":		//MEMORANDO	
    $cuerpo = "<table border='0' cellpadding='0' cellspacing='0' width='800'>
	    <tr><td width='120'>&nbsp;</td><td width='680'>&nbsp;</td></tr>";
          
	    $cuerpo.="<tr height='33'>
		<td valign='top'><b>PARA:</b></td>
		<td valign='top'>$usuadest</td>
	    </tr>";
           
	    $cuerpo.="<tr height='20'>
		<td valign='top'><b>ASUNTO:</b></td>
		<td valign='top'>".htmlentities($registro["radi_asunto"],ENT_COMPAT,'UTF-8')."</td>
	    </tr>
	</table>
		$texto";
	break;
    case "5":		//ACUERDOS    
	$cuerpo = "<center>".($usuarem["nombre"]) .
              "</center><br><center>".($usuarem["cargo"])."</center>
              <br/>&nbsp;<br/>$texto";
	$firma = "<br>&nbsp;<br/>&nbsp;<br/>&nbsp;<br/>&nbsp;<br/>&nbsp;<br/><i><b>
              <h6><font color='blue'>$firma_digital</font></h6></b></i>
              <br/><center>".($usuarem["nombre"])."</center>";
	break;
    case "8":		//RESOLUCIONES    
        $instResolucion=strtr($usuarem['institucion'],"abcdefghijklmnopqrstuvwxyz"."áéíóúñäëïöü","ABCDEFGHIJKLMNOPQRSTUVWXYZ"."ÁÉÍÓÚÑÄËÏÖÜ"); 
	$cuerpo = "<br/>&nbsp;<br/><center>".$instResolucion."</center>
              <br/>&nbsp;<br/>$texto";
        
	$firma_resolucion="<br>&nbsp;<br>&nbsp;<br><br><center>".$usuarem["abr_titulo"]." ".$usuarem["nombre"]."<br>
            ".($usuarem["cargo"])."</center><br>";              
	break;
    case "9":		//PROVIDENCIAS
	$cuerpo = "<br>$texto ";
        $registroOpcImpr["JUSTIFICAR_FIRMA"] = 1;
        $registroOpcImpr["OCULTAR_ATENTAMENTE"] = 1;
	break;
    default:		//CIRCULARES
	$cuerpo = "<br/>";
    if($registro['radi_tipo_impresion']==3)
        $cuerpo .= "$usuadest<br/>&nbsp;<br/>";
        $cuerpo .= "$asunto<br/>&nbsp;<br/>";
	$cuerpo .="$texto ";
	break;
} //fin switch



//Generación del Pie del documento dependiendo del tipo de documento

if($firma=='')
{
    //Generación del Atentamente con los datos del Remitente
    //Validaciòn opciones impresiòn
    //Saludo
    
    if($registroOpcImpr["FRASE_DESPEDIDA"]!=""){
        if ($registroOpcImpr["OCULTAR_ATENTAMENTE"]==0)
         $fraseDespedida=$registroOpcImpr["FRASE_DESPEDIDA"];
    }
    else{
       if ($registro['radi_tipo']!=6)
           if ($registroOpcImpr["OCULTAR_ATENTAMENTE"]==0)
             $fraseDespedida="Atentamente,";
    }

    if($registroOpcImpr["OCULTA_FRASE_DESPEDIDA"]==1 or $td!=1)
        $fraseRemit="";
    else if($registroOpcImpr["FRASE_REMITENTE"]!="" and $registroOpcImpr["FRASE_REMITENTE"]!="Sin frase de despedida"){
            $frase=strtoupper2($registroOpcImpr["FRASE_REMITENTE"]);
            $fraseRemit="$frase<br/>";
        }else if($datInstitucion['fraseDespedida']!='')
            $fraseRemit = $datInstitucion['fraseDespedida'];
        else
            $fraseRemit="";

   $abr_titulo = str_replace(array("Sr. ", "Sra. ", "Srita. ", "Sres. "), array("","","",""), strtoulcase($usuarem["abr_titulo"]));

   if($registroOpcImpr["JUSTIFICAR_FIRMA"]==1){
        $firma = "<center>&nbsp;<br/>$fraseDespedida<br/><strong>$fraseRemit</strong>&nbsp;<br/><i><b><h6><font color='blue'>$firma_digital</font></h6></b></i><br/>".
        (strtoulcase($abr_titulo).' '.$usuarem["nombre"]).'<br/><b>'.($usuarem["cargo"]).
        "</b><br></center>";
   }
   else {
            $firma = "&nbsp;<br/>$fraseDespedida<br/><strong>$fraseRemit</strong>&nbsp;<br/><i><b><h6><font color='blue'>$firma_digital</font></h6></b></i><br/>".
            (strtoulcase($abr_titulo).' '.$usuarem["nombre"]).'<br/><b>'.strtoupper2($usuarem["cargo"]).
            "</b><br>";
       }
    
////ALINEACION DE DATOS(REFERENCIA, ANEXOS, COPIA A,RESPONSABLES)
$datosFinales="<table border='0' cellpadding='0' cellspacing='0' width='100%'>";
$saltoLinea = "<tr><td width='100%' colspan='2'><font size=2>&nbsp;</font></td></tr>";
$espaciohtml="";

//INICIO REFERENCIA---------------------------------------------------------------
if ($referencia!='')
    if ($registroOpcImpr["OCULTAR_REFERENCIA"]==0){
$datosFinales.= "<tr><td width='100%'><font size=2>Referencias: </font></td></tr>
           <tr><td width='100%'>
                <font size=2> $espaciohtml- $referencia_desc_tipo ".$referencia."</font>
                </td>
           </tr>";
    }
$datosFinales.=$saltoLinea;
//FIN REFERENCIA------------------------------------------------------------------
     
//INICIO ANEXOS-------------------------------------------------------------------
if ($registro['radi_tipo']!=6){//si no es nota
    if ($registroOpcImpr["OCULTAR_ANEXO"]==0){
        $descanexo=htmlentities($registro["radi_desc_anexos"],ENT_COMPAT,'UTF-8');
	if (trim($registro["radi_desc_anexos"])!="" or trim($Anexos)!="")
	{
           //Por Inen se cambia la palabra Adjunto por Anexo           
            if ($descanexo!=''){
            $datosFinales.="<tr><td width='100%'><font size=2>Anexos: $descanexo&nbsp;</font></td></tr>
                    
                    <tr><td width='100%' align='left'><font size=2>".$espaciohtml.
                    "</font></td></tr>";
            }else{            
            $datosFinales .="<tr><tdwidth='100%'><font size=2>Anexos: </font></td></tr>
                     <tr><td width='100%' align='left'><font size=2></font></td></tr>";            
            }
            if(trim($Anexos)!='<br>')
                $datosFinales .= "
                    <tr><td width='100%' align='left'><font size=2>$espaciohtml".$Anexos."</font></td></tr>";
           
        }
    }
}//fin si no es nota
//FIN ANEXOS-----------------------------------------------------------------

//INICIO COPIA A-------------------------------------------------------------
if (trim($cca)!="")//inicio copia a
            $datosFinales.="<tr><td width='100%'>
                                <font size=2>Copia:</font>
                                </td>
                            </tr>
                            <tr><td width='100%' align='left'>
                                <font size=2>$cca</font>
                                </td>
                            </tr>";
            //$datosFinales.=$saltoLinea;
//FIN ALINEACION DE TEXTOS----------------------------------------------------

//FIN COPIA A-----------------------------------------------------------------
$datosFinales.="</table>";
}
//en el documento de nota, no se envia la fecha, se envia dentro del texto.
//Generacion de la fecha depende solo si el documento es nota


$fecha_j = $fecha; 
$just_fecha = str_replace("<br","&nbsp<br",$fecha_j);
$just_fecha = str_replace("<BR","&nbsp<BR",$fecha_j);
$just_fecha = str_replace("text-align: right;","text-align: right;",$fecha_j);
$just_fecha = str_replace("text-align: right;","text-align: right;",$fecha_j);
$just_fecha="<p align='center'><font size=3><b>$just_fecha</b></font></p>";
$justFecha = "<table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr><td valign='top' width='10%'></td>
                    <td width='90%' align='right' valign='top'>".$just_fecha."<br>";
$justFecha .= "</td></tr>";
$justFecha .= "</table>";
$fin = '
</body>
</html>
';

$aumenta3L.="<br>&nbsp;<br>";
    
$Responsables = "<font size=2><br>&nbsp;<br>$Responsables</font>";
if ($Anex=="" and $CC=="") $Responsables = "<br>&nbsp;<br>".$Responsables;

$cuerpo = '<br><font size=3>'.$cuerpo."</font>";

$saludo_exterior="<br><b>A la Honorable,</b><br>";
//---------------------------------------GENERACION DEL DOCUMENTO---------------------------
// Union de las partes para la generación del documento
if ($registro['radi_tipo']==6){//documento tipo nota    
    
  if($opc_imp_nota==3){//verbal      
      $tituloOpc = "<br><b>$tituloN</b><br>";
      $destino_destinatario = "<br></br>".$destino_destinatario;
     if($opc_imp_just_fecha==1){//pie
            $fecha='';
            //$doc_pdf = $inicio.$cuerpo.$justFecha.$aumenta3L.$impRef.$Anex.$tituloOpc.$institucionNota.$destino_destinatario.$CC.$Responsables.$fin;
            $doc_pdf = $inicio.$cuerpo.$justFecha.$aumenta3L.$tituloOpc.$institucionNota.$destino_destinatario.$datosFinales.$fin;
    }
  }elseif($opc_imp_nota==1)//diplomatica
      //$doc_pdf = $inicio.$cuerpo.$firma.$impRef.$Anex.$cuerpo1.$CC.$Responsables.$fin;
      $doc_pdf = $inicio.$cuerpo.$firma.$datosFinales.$fin;
      else//reversal
    //$doc_pdf = $inicio.$cuerpo.$firma.$aumenta3L.$impRef.$Anex.$cuerpo1.$destinatarioNota2.$destino_destinatario.$CC.$Responsables.$fin;
          $doc_pdf = $inicio.$cuerpo.$firma.$aumenta3L.$destinatarioNota2.$destino_destinatario.$datosFinales.$fin;
   
}
else{
    
    if ($registro['radi_tipo']==1)//solo si es oficio añade asunto
        $doc_pdf = $inicio.$cuerpo.$firma.$cuerpo1.$datosFinales.$Responsables.$fin;
    elseif($registro['radi_tipo']==9)//providencia
        $doc_pdf = $inicio.$cuerpo.$firma.$datosFinales.$Responsables.$fin;
    elseif($registro['radi_tipo']==8)//resolucion
        $doc_pdf = $inicio.$cuerpo.$firma_resolucion.$datosFinales.$Responsables.$fin;
    else// si es memorando con asunto
        $doc_pdf = $inicio.$cuerpo.$firma.$datosFinales.$Responsables.$fin;
}
?>