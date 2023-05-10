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
*       David Gamboa            DG                      16-06-2011
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
*	Sylvia Velasco		SV			14-01-2009
**/


/** 
* Función para consulta de datos del radicado para al generación del documento .PDF.
* @param $verrad número del radicado en la base radi_nume_radi.
* @param $creapdf para al generación del .PDF
* @param $ruta_raiz parametro que recibe la ruta raiz del proyecto.
* @param $NoLlamada
* @param &$db2 conexion a la base de datos.
**/
function GenerarPDF($verrad_ori,$creapdf,$ruta_raiz,$NoLlamada=0,$db2=null,$volverGenerar=0)
{
//    include "$ruta_raiz/include/barcode/index.php";
    if (substr($verrad_ori, -1) == "2") return "";
    if ($NoLlamada==0) {
        require_once "$ruta_raiz/include/db/ConnectionHandler.php";
        $db2 = new ConnectionHandler($ruta_raiz);
    }
    $db=$db2;
    include_once "$ruta_raiz/class_control/class_gen.php";
    include_once "$ruta_raiz/obtenerdatos.php";

    // se incluyo para que guarde los archivos siempre que se firmen
    $registro_ori = ObtenerDatosRadicado($verrad_ori,$db2);
    if (trim($registro_ori["radi_path"])!="") return $registro_ori["radi_path"];    
    /** GENERACION DE LA CABECERA DEL DOCUMENTO PDF **/
    $verrad = $registro_ori["radi_nume_temp"];
    //norma 2410
    $asunto =$registro_ori["radi_asunto"];
    if (substr($verrad, -1) == "2") return "";
    $registro = ObtenerDatosRadicado($verrad,$db2);
    $registroOpcImpr = ObtenerDatosOpcImpresion($registro["radi_nume_temp"],$db2);
    $institucion = ObtenerDatosInstitucion($registro["inst_actu"],$db2);
    $gen_fecha = new CLASS_GEN();
    
    //IMPRIMIR REFERENCIA SEGUN NORMA: 2410    
    if ($registro["radi_referencia"]!=''){//descripcion del tipo de la referencia
        $referencia = $registro["radi_referencia"];
        $referencia_desc_tipo = mostrar_tipo_referencia($db,$referencia)." Nro: ";
    }
    //Opciones de Impresiòn
    //Si el título fue modificado
    if($registroOpcImpr["TITULO_NATURAL"]!="")
        $tituloN=$registroOpcImpr["TITULO_NATURAL"];

    //Cargo cabecera del documento
    if($registro['radi_tipo']==1 || $registro['radi_tipo']==6){//Oficio
        if($registroOpcImpr["CARGO_CABECERA"]!="")
            $cargoCabec=$registroOpcImpr["CARGO_CABECERA"];
    }   
    else
        $cargoCabec="";
    
    //Si alado del destinatario hay información adicinal
    if($registroOpcImpr["FIRMANTES"]!="" and $registroOpcImpr['MOSTRAR_PARA']==0)
        $firmantes = $registroOpcImpr["FIRMANTES"];
    else
        $firmantes="";
    //Si alado de la institución hay información adicinal
    if($registroOpcImpr["EXT_INSTITUCION"]!="" and $registroOpcImpr['MOSTRAR_PARA']==0)
        $extInstituto = $registroOpcImpr["EXT_INSTITUCION"];
    else{      
        if($registro['radi_tipo']==6)
        $extInstituto=$registroOpcImpr["EXT_INSTITUCION"];
        else
            $extInstituto="";
    }
    //opcion impresion nota
    if($registroOpcImpr["OPC_IMP_TIPO_NOTA"]!="")
            $opc_imp_nota=$registroOpcImpr["OPC_IMP_TIPO_NOTA"];
    //opcion fecha justificar
    if($registroOpcImpr["OPC_JUSTIFICAR_FECHA"]!="")
            $opc_imp_just_fecha=$registroOpcImpr["OPC_JUSTIFICAR_FECHA"];
    if ($registroOpcImpr["DESTINO_DESTINATARIO"]!="")
        $destino_destinatario=$registroOpcImpr["DESTINO_DESTINATARIO"];

        
    /** Si el documento se encuantra en estado de En Elaboración y tiene por numero de doumento un número temporal
    * los datos de los usuarios ya sea remitente, destinatario o con copia toma la ultima actualización de 
    * la información.
    * Caso contrario si el documento ya tiene un numero defitivo de documento los datos del usuarios remitente,
    * destinatario, con copia se obtienen tal cual fueron enviados en ese momento, no seran suseptibles a cambios-
    **/
    
    if($registro["estado"]=="1" or $registro["estado"]=="7" )
    {
    	
        $date =  date("m/d/Y");
    	$fecha = $gen_fecha->traducefecha($date);
        $usuarem = ObtenerDatosUsuario(str_replace("-","",$registro["usua_rem"]),$db2);
        $area = ObtenerDatosDependencia($usuarem["depe_codi"],$db2);
        $ciudadUsua = ObtenerCiudadUsua(' usuarios left outer join ciudad on ciu_codi = id ', ' usua_codi = '.$usuarem['usua_codi'], $db2);
        if(trim($ciudadUsua['nombre']) != '')
            $area['ciudad'] = $ciudadUsua['nombre'];
        
        $usuadest = ListaUsuarios($registro["usua_dest"],$db2,$registro['radi_tipo'],$registro['radi_tipo_impresion'],$registro['radi_lista_dest'],"$extInstituto","$tituloN","$firmantes","$cargoCabec",$registro['radi_tipo']);
        if ($registro['radi_tipo']==6 and $registroOpcImpr['OPC_IMP_TIPO_NOTA']==3){
            $instidest = ObtenerDatosUsuario(str_replace("-","",$registro["usua_dest"]),$db2);
            $institucionOpciones = $registroOpcImpr['EXT_INSTITUCION'];
            $institucionNota = $instidest['institucion'];
            if ($institucionNota==$institucionOpciones)
                $institucionNota = $institucionNota;
            else
                $institucionNota=$institucionNota." ".$institucionOpciones;
        }
        //$cca = ListaUsuarios($registro["cca"],$db2); 
        if ($registro['radi_tipo']==3)            
            $cca = ListaUsuarios($registro["cca"], $db2, $registro['radi_tipo'], '1'); 
        else
        $cca = ListaUsuarios($registro["cca"], $db2, $formato = 1, '1'); //requermiento 69
    }
    else
    {
        $fecha = $gen_fecha->traducefecha($registro["radi_fecha"]);
        $usuarem = ListaUsuariosRadicado($registro["radi_nume_temp"], $db2, $formato=0, '1','1');
        $area['ciudad'] = $usuarem["ciudad"];
        $area['plantilla'] = $usuarem["plantilla"];
        $usuadest = ListaUsuariosRadicado($registro["radi_nume_temp"], $db2, $registro['radi_tipo'], '2', $registro["radi_tipo_impresion"],"$extInstituto","$tituloN","$firmantes",$registro['radi_tipo'],$cargoCabec);
        if ($registro['radi_tipo']==6 and $registroOpcImpr['OPC_IMP_TIPO_NOTA']==3){
            $instidest = ObtenerDatosUsuario(str_replace("-","",$registro["usua_dest"]),$db2);
            $institucionOpciones = $registroOpcImpr['EXT_INSTITUCION'];
            $institucionNota = $instidest['institucion'];
            if ($institucionNota==$institucionOpciones)
                $institucionNota = $institucionNota;
            else
                $institucionNota=$institucionNota." ".$institucionOpciones;
        }
        //$cca = ListaUsuariosRadicado($verrad, $db2, $formato=0, '3');
        $cca = ListaUsuariosRadicado($registro["radi_nume_temp"], $db2, $formato = 1, '3', '1'); //requermiento 69
    }

    //Fecha de documento para enviar a html_a_pdf
    if(trim($area['ciudad'])!='')
            $fecha = trim($area['ciudad']).", $fecha";
    else
            $fecha = "Quito, $fecha";
    
    // Obtener datos de la institucion para imprimir despedida para oficios
    $datInstitucion = ObtenerDatosInstitucion($usuarem['inst_codi'],$db);

    /** CONSULTA DE ANEXOS Y DESCRIPCION DE LOS MISMOS ASOCIADOS AL DOCUMENTO **/
    $isql = "select anex_nombre, anex_desc from anexos
		where anex_radi_nume=$verrad and anex_borrado='N'
		order by anex_codigo";

    $rs=$db2->conn->query($isql);
    $Anexos = "";
    while(!$rs->EOF)
    {
//        if(trim($rs->fields["ANEX_DESC"])!='')               
//            $Anexos .= " - ".htmlentities($rs->fields["ANEX_DESC"],ENT_COMPAT,'UTF-8')."<br>&nbsp;<br><&nbsp;<br>";
//                else
                    $Anexos .= " - ".$rs->fields["ANEX_NOMBRE"]."&nbsp<br>";
        $rs->MoveNext();
    }	/** fin del while **/

    /** RESPONSABLE DE AREA **/
    $Responsables="";
    if($_SESSION["perm_ocultar_sumilla"]!=1){//inicia session
        $isql="select depe_codi from usuarios where usua_codi=".str_replace("-","",$registro["usua_rem"])."";
        $rs=$db2->conn->query($isql);
        while(!$rs->EOF)
        {
            $area_resp=trim($rs->fields["DEPE_CODI"]);
            $rs->MoveNext();
        }
        if($area_resp!=""){
            $isql="select usua_sumilla from usuarios where depe_codi=$area_resp and usua_responsable_area=1";
            $rs=$db2->conn->query($isql);
            while (!$rs->EOF){
                $Responsables.=trim($rs->fields["USUA_SUMILLA"]);
                $rs->MoveNext();
            }
        }
    
        /** RESPONSABLES DE LA ELABORACION DEL DOCUMENTO **/
        $isql = "select distinct h.radi_nume_radi, h.usua_codi_ori, u.usua_nomb, u.usua_apellido, max(h.hist_fech) as hist_fech,u.usua_sumilla,u.usua_responsable_area
                    from hist_eventos h left outer join usuarios u on u.usua_codi=h.usua_codi_ori
                    where sgd_ttr_codigo in (66,2,11,65,9) and h.radi_nume_radi=$verrad and h.usua_codi_ori<>".str_replace("-","",$registro["usua_rem"])."
                    group by radi_nume_radi, usua_codi_ori, usua_nomb, usua_apellido,u.usua_sumilla,u.usua_responsable_area order by hist_fech asc";

            $rs=$db2->conn->query($isql);
        //$Responsables="";
        while(!$rs->EOF)
        {

            if($rs->fields["USUA_SUMILLA"]==''){
                $iniciales = substr(trim($rs->fields["USUA_NOMB"]),0,1).substr(trim($rs->fields["USUA_APELLIDO"]),0,1);
                if ($Responsables=="")
                    $Responsables .= strtolower($iniciales);//strtoupper
                else
                    $Responsables .= "/".strtolower($iniciales);
            }else{//Obtiene sumilla del campo USUA_SUMILLA
                if($rs->fields["USUA_RESPONSABLE_AREA"]==1)
                    $usua_sumilla=strtolower($rs->fields["USUA_SUMILLA"]);
                else
                    $usua_sumilla=$rs->fields["USUA_SUMILLA"];

                if ($Responsables=="")
                    $Responsables .= $usua_sumilla;
                else
                    $Responsables .= "/".$usua_sumilla;
            }
            $rs->MoveNext();
        }	/** fin while responsables **/
    }//fin session
    /**
     * Buscar los codigos de los destinatarios y añade una bandera en caso de ciudadano
     **/
    $EsCiudadano=0;
    $destinos= explode("-",$registro["usua_dest"]);
    foreach ($destinos as $tmp)
    {
        if (trim($tmp)!="") {
            $sql="select ciu_nombre from ciudadano where ciu_codigo=".$tmp;
            //echo $sql;
            $rs=$db2->conn->query($sql);
            if($rs->fields["CIU_NOMBRE"]!=''){
                $EsCiudadano="1";
            }
        }
    }

    /** 
    * SI el documento fue firmado electronicamente en la parte de atentamente mostrara el siguiente dialogo:
    * Documento firmado electrónicamente.
    * Caso contrario debera firmarlo manualmente.
    **/
    $firma_digital = "&nbsp;<br/>&nbsp;<br/>";
    
    if ($creapdf=="si"){
        $firma_digital = "&nbsp;<br/>Documento firmado electrónicamente";
    }
    else
    {
        if(trim($usuarem['usua_firma_path'])!="" and $volverGenerar == 1)
        {
            // Obtener firma digitalizada del firmante
            $firma_digital = "<img src = '".$usuarem["usua_firma_path"]."'> ";
        }
    }

    // Buscamos el texto del documento
    $sql = "select text_texto from radi_texto where text_codi=".$registro["radi_codi_texto"];
    $rs = $db->query($sql);
    $registro['radi_texto'] = stripcslashes($rs->fields["TEXT_TEXTO"]);

    include "$ruta_raiz/plantillas/Memos.php";

    /** CREACION DEL DOCUMENTO PDF **/
    //    require_once("generar_pdf.php");
    //    $pdf = generar_pdf($doc_pdf,$area["plantilla"].".pdf",$ruta_raiz);
    include "$ruta_raiz/config.php";
    require_once("$ruta_raiz/interconexion/generar_pdf.php");
    $usua_remitente = ObtenerDatosUsuario(str_replace("-","",$registro["usua_rem"]),$db2);
    $plantilla = "";
    if ($registro["usar_plantilla"]==1) {
        $plantilla = "$ruta_raiz/bodega/plantillas/".$area["plantilla"].".pdf";
        if ($usua_remitente["tipo_usuario"]==2)
            $plantilla = "$ruta_raiz/bodega/plantillas_ciudadanos/".$_SESSION["usua_codi"].".pdf";
        if (!is_file($plantilla)) $plantilla = "";
    }

    //Para determinar tipo de documento
    $rs_tiporad = $db2->conn->query("select trad_descr from tiporad where trad_codigo=".$registro["radi_tipo"]);
    $numDocumento = $rs_tiporad->fields["TRAD_DESCR"]." Nro. ".$registro["radi_nume_text"];
    if (($registro["radi_tipo"] == '1' and $registroOpcImpr["OCULTAR_NUME_RADI"]==1) or ($registro["radi_tipo"]==7 and $usua_remitente["tipo_usuario"]==2))//Oculta
        $numDocumento="";

    $formato_pagina = "V"; //Estilo de documento normal, segun Norma INEN
    if ($registroOpcImpr["LETRA_ITALICA"]==1) $formato_pagina = "I"; // Documento en letra cursiva
    $pdf = ws_generar_pdf($doc_pdf, $plantilla, $servidor_pdf, $registro["estado"], $numDocumento, $fecha, $registro["ajust_texto"], $formato_pagina);

    if ($creapdf=="si") {
        return $pdf;
/*    	$nombarch = "/". substr($verrad,0,4)."/".substr($verrad,4,3)."/".$verrad.".pdf";
    	file_put_contents("$ruta_raiz/bodega".$nombarch, $dompdf->output());
    	$sql = "UPDATE RADICADO SET RADI_PATH='$nombarch' where radi_nume_radi=$verrad";
    	$rs = $db2->query($sql);	*/
    } else {
        if ($pdf == "0") return "0"; //validar que se genere el pdf
        $nombarch = "/tmp/$verrad_ori.pdf";
        if ($registro_ori["estado"]=="0" or $registro_ori["estado"]=="2" or $registro_ori["estado"]=="3" or
            $registro_ori["estado"]=="5" or $registro_ori["estado"]=="6") {
            if (trim($registro_ori["radi_path"])=="" and $registro_ori["estado"]!="3") {
                $nombarch = "/" . substr($verrad_ori,0,4) .
                            "/" . substr($verrad_ori,4,6) .
                            "/$verrad_ori.pdf";
            } else {
                $sql = "select radi_nume_radi from radicado where coalesce(radi_path,'')='' and esta_codi in (0,2,5,6)
                        and radi_nume_temp=$verrad";
                $rs = $db2->conn->query($sql);
                if (!$rs->EOF) {
                    $nombarch = "/" . substr($rs->fields["RADI_NUME_RADI"],0,4) . 
                                "/" . substr($rs->fields["RADI_NUME_RADI"],4,6) . 
                                "/" . $rs->fields["RADI_NUME_RADI"] . ".pdf";
                }
            }
       	    $sql = "update radicado set radi_path=E'$nombarch' where coalesce(radi_path,'')='' and esta_codi in (0,2,5,6)
                    and radi_nume_temp=" . $registro["radi_nume_temp"];
            $db2->conn->Execute($sql);
        } 
        file_put_contents("$ruta_raiz/bodega$nombarch", $pdf);
        return $nombarch;
    }
	return "ok";
}

/** 
* Función que lista a los usuarios destinatario y con copia cuamdo el Documento esta en estado de En Elaboración
* @param $usuario lista de usuarios destinatario y con copia
* @param $db conexión a la base de datos
* @param $formato envio de tipo de documento
* @param $tipo_impresion tipo de impresion que el usuario desea para su documento pdf
* @param $lista_dest si el destinatario pertenece a una lista envia el codigo de la lista
**/
function ListaUsuarios($usuario, $db, $formato=0, $tipo_impresion="", $lista_dest="",$extInstituto="",$tituloN="", $firmantes="",$cargoCabec="",$tipodocu="")
{
   
    if ($formato==6 or $formato==7) $formato=1; //Mostrar con formato de oficio Notas Consulares y Cartas Ciudadanos
    $cadena = "";
        // Si es tipo 3 la impresion independientemente del tipo de documento se obtiene el nombre de la lista
    if($tipo_impresion=="3")
    {
        if(trim($lista_dest)!="")
        {
            
            //var $codList = new Array();
            $codList = split("-",$lista_dest);
            $cadena .= "<b>";
            if(sizeof($codList)>2)
            {               
                for($j=1;$j<sizeof($codList)-2;$j+=2)
                {
                    $datosLista = ObtenerDatosLista($codList[$j],$db);
                    $cadena .= strtoulcase($datosLista['nombre']) . '<br/>';
                }
                $datosLista = ObtenerDatosLista($codList[$j],$db);
                $cadena .= strtoulcase($datosLista['nombre'])."</b><br/>";
            }
            else
            {                
                $datosLista = ObtenerDatosLista($codList[$j],$db);
                $cadena .= strtoulcase($datosLista['nombre'])."</b><br/>";
            }
        }
    }
    else
    {        
      //cuenta cuantos destinatarios son
      //sirve para validar los espacios entre ciudad
      $cuenta = str_replace('--', ',', $usuario);
      $cuenta = count(explode(',', $cuenta));
        foreach (explode('-',$usuario) as $usua_codi) {
            if (trim($usua_codi!="")) {
                $usr = ObtenerDatosUsuario($usua_codi,$db);
                //definir cargo
              if ($cuenta>1) {
                   $cargo=$usr["cargo"];
              } else {
                if($tipodocu==1 || $tipodocu==6) {//Oficio
                    if(trim($cargoCabec)!="")
                        $cargo=$cargoCabec;
                    elseif (trim($usr["cargo_cabecera"])!="")
                        $cargo=$usr["cargo_cabecera"];
                    else
                        $cargo=$usr["cargo"];
                } else {//Otro Doc
                    $cargo=$usr["cargo"];                    
                }
              }
                if ($formato==1)
                {
                   
                   if($tipo_impresion=="1"){ //titulo,nombre, cargo,institucion   
                      //Requierimiento para inen 2049
                       if ($tipodocu==3)//memorando
                           $titulo=$usr["abr_titulo"];//se imprime la abr
                       else
                            $titulo=$usr["titulo"];                       
                        
                       if($tituloN!="")
                            $cadena.=strtoulcase($tituloN)."<br/>";
                        else
                            $cadena.=strtoulcase($titulo)."<br/>";
                        $cadena .= ($usr["nombre"]." $firmantes")."<br/><b>".
                                   ($cargo)."<br/></b>";
                        if ($tipodocu==1 || $tipodocu==6){
                        if ($cuenta==1)
                        $cadena.= "<b>".strtoupper2($usr["institucion"]." $extInstituto")."</b>";
                        else
                            $cadena.= "<b>".strtoupper2($usr["institucion"]." $extInstituto")."</b>&nbsp;<br/>&nbsp;<br/>";                            
                        }
                         
                        
                    }
                    if($tipo_impresion=="2")//cargo,institucion
                    {                    
                        $cadena .= ($cargo)."<br/>";
                        if (trim($extInstituto)!=''){
                            if ($cuenta==1)                                
                                $cadena.="<b>".strtoupper2($usr["institucion"]." $extInstituto,")."</b>";
                            else
                                $cadena.="<b>".strtoupper2($usr["institucion"]." $extInstituto,")."</b>&nbsp;<br/>&nbsp;<br/>";
                        }else{
                             if ($cuenta==1) 
                                $cadena.="<b>".strtoupper2($usr["institucion"]." $extInstituto")."</b>";
                             else
                                 $cadena.="<b>".strtoupper2($usr["institucion"]." $extInstituto")."</b>&nbsp;<br/>&nbsp;<br/>";
                        }
                    }
                    if($tipo_impresion=="4"){ //titulo,nombre,cargo  
                       
                        if($tituloN!="")
                            $cadena.=strtoulcase($tituloN)."<br/>";
                        else{
                            $titulo=$usr["titulo"];
                            $cadena.=strtoulcase($titulo)."<br/>";
                        }                          

                        $cadena .= ($usr["nombre"]." $firmantes")."<br/><b>";
                        if ($cuenta==1)
                        $cadena .= ($cargo)."</b><br/>";
                        else
                            $cadena .= ($cargo)."</b>&nbsp;<br/>&nbsp;<br/>";
                    }
                    if($tipo_impresion=="5"){//titulo,nombre,institucion
                        if($tituloN!="")
                            $cadena.=strtoulcase($tituloN)."<br/>";
                        else{
                            $titulo=$usr["titulo"];
                            $cadena.=strtoulcase($titulo)."<br/>";
                        }

                        $cadena .= ($usr["nombre"]." $firmantes")."<br/>";
                        if ($cuenta==1) 
                        $cadena.= "<b>".strtoupper2($usr["institucion"]." $extInstituto")."</b>";
                        else
                            $cadena.= "<b>".strtoupper2($usr["institucion"]." $extInstituto")."</b>&nbsp;<br/>&nbsp;<br/>";
                    }
                    if($tipo_impresion=="6"){//titulo,cargo,institucion
                        if($tituloN!="")
                            $cadena.=strtoulcase($tituloN)."<br/>";
                        else{
                             $titulo=$usr["titulo"];
                            $cadena.=strtoulcase($titulo)."<br/>";
                        }
                        $cadena .= "<b>".($cargo)."<br/></b>";
                        if ($tipodocu==1 || $tipodocu==6){
                            if ($cuenta==1){ 
                                $cadena.= "<b>".strtoupper2($usr["institucion"]." $extInstituto")."</b>";
                            }
                            else
                                $cadena.= "<b>".strtoupper2($usr["institucion"]." $extInstituto")."</b>&nbsp;<br/>&nbsp;<br/>";
                        }
                    }
                }//fin formato 1
                else
                {
                    if($tipo_impresion=="1"){//titulo,nombre,cargo,institucion
                        $cadena.= strtoulcase($usr["abr_titulo"]." ".$usr["nombre"])."<b><br/>";
                        //$cadena.= strtoupper2($usr["institucion"]." $extInstituto")."</b><br/>";
                        $cadena.= ($cargo)."</b>&nbsp;<br/>&nbsp;<br/>";
                        
                    }
                    elseif($tipo_impresion=="2")//cargo,institucion
                        $cadena .= "<b>".($cargo)."</b>";
                    elseif($tipo_impresion=="6"){//titulo,cargo,institucion
                       if($tituloN!="")
                            $cadena.=strtoulcase($tituloN)."<br/>";
                        else{
                             $titulo=$usr["titulo"];
                            $cadena.=strtoulcase($titulo)."<br/>";
                        }
                        $cadena .= "<b>".($cargo)."</b><br/>";
                        if ($tipodocu==1 || $tipodocu==6){
                            if ($cuenta==1){ 
                            $cadena.= strtoupper2($usr["institucion"]." $extInstituto")."</b>";                       
                            }
                            else
                                $cadena.= strtoupper2($usr["institucion"]." $extInstituto")."</b>&nbsp;<br/>&nbsp;<br/>";
                        }
                    }else{//para que no imprima vacio
                        $cadena.= strtoulcase($usr["abr_titulo"]." ".$usr["nombre"])."<b><br/>";
                        //$cadena.= strtoupper2($usr["institucion"]." $extInstituto")."</b><br/>";
                        $cadena.= ($cargo)."</b>&nbsp;<br/>&nbsp;<br/>";
                    }
                    
                }
            }
           //if ($cadena!='')
             //  $cadena.="<p></p>"; 
        }//for
    }
    
    return $cadena;
    
    
}

/** 
* Función que lista a los usuarios destinatario y con copia cuamdo el Documento ya no esta en estado de 
* En Elaboración
* @param $radicado numero de documento en la tabla radi_nume_radi
* @param $db conexión a la base de datos
* @param $formato envio de tipo de documento
* @param $tipo tipo de usuario 1 si es remitente, 2 si es destinatario y 3 si es con copia.
* @param $tipo_impresion tipo de impresion que el usuario desea para su documento pdf
**/
function ListaUsuariosRadicado($radicado, $db, $formato=0, $tipo, $tipo_impresion="",$extInstituto="",$tituloN="", $firmantes="",$tipodocu="",$cargoCabec="")
{
    if ($formato==6 or $formato==7) $formato=1; //Mostrar con formato de oficio Notas Consulares y Cartas Ciudadanos
    $cadena = "";

    $sql = "select usua_nombre, usua_apellido, usua_abr_titulo, usua_titulo, usua_cargo, usua_institucion,
                   usua_ciudad, usua_area_codi, lista_nombre, usua_firma_path
            from usuarios_radicado
            where radi_nume_radi=$radicado and radi_usua_tipo=$tipo
            order by usua_radi_codi ";
    //usua_cargo
    $rsUR=$db->conn->query($sql);
    // Si es tipo 3 la impresion independientemente del tipo de documento se obtiene el nombre de la lista
    if($tipo_impresion=="3")
    {
        $cadena .= "<b>".strtoulcase($rsUR->fields["LISTA_NOMBRE"])."</b><br/>";
        $cadena = str_replace(", ", "<br>", $cadena);
        //echo $rsUR->fields["LISTA_NOMBRE"];
    }
    else
    {
        while(!$rsUR->EOF)
        {
            if ($formato==1)
            {
                //Si el cargo fue cambiado en opciones de impresión muestra el cargo modificado
                if($cargoCabec!="")
                    $cargoUR = $cargoCabec;
                else
                    $cargoUR = $rsUR->fields["USUA_CARGO"];
                if($tipo_impresion=="1")
                {
                    if($tituloN!="")
                        $cadena.=strtoulcase($tituloN)."<br/>";
                    else
                        $cadena.=strtoulcase($rsUR->fields["USUA_TITULO"])."<br/>";

                    $cadena .= ($rsUR->fields["USUA_NOMBRE"]." ".$rsUR->fields["USUA_APELLIDO"])." $firmantes<br/><b>".
                               ($cargoUR)."<br/>".
                               strtoupper2($rsUR->fields["USUA_INSTITUCION"]." $extInstituto")."</b><br/>";
                }
                if($tipo_impresion=="2")
                    $cadena .= ($cargoUR)."<br/>".
                               "<b>".strtoupper2($rsUR->fields["USUA_INSTITUCION"]." $extInstituto,")."</b><br/>";
                if($tipo_impresion=="4")
                {
                    if($tituloN!="")
                        $cadena.=strtoulcase($tituloN)."<br/>";
                    else
                        $cadena.=strtoulcase($rsUR->fields["USUA_TITULO"])."<br/>";

                    $cadena .= strtoulcase($rsUR->fields["USUA_NOMBRE"]." ".$rsUR->fields["USUA_APELLIDO"])." $firmantes<br/><b>".
                               ($cargoUR)."</b><br/>";
                }
                if($tipo_impresion=="5")
                {
                    if($tituloN!="")
                        $cadena.=strtoulcase($tituloN)."<br/>";
                    else
                        $cadena.=strtoulcase($rsUR->fields["USUA_TITULO"])."<br/>";

                    $cadena .= ($rsUR->fields["USUA_NOMBRE"]." ".$rsUR->fields["USUA_APELLIDO"])." $firmantes<br/><b>".
                               strtoupper2($rsUR->fields["USUA_INSTITUCION"]." $extInstituto")."</b><br/>";
                }
                if($tipo_impresion=="6")
                {
                    if($tituloN!="")
                        $cadena.=strtoulcase($tituloN)."<br/>";
                    else
                        $cadena.=strtoulcase($rsUR->fields["USUA_TITULO"])."<br/>";

                    $cadena .= ($cargoUR)."<br/><b>".
                               strtoupper2($rsUR->fields["USUA_INSTITUCION"]." $extInstituto")."</b><br/>";
                }
            }
            else
            {
                if($tipo_impresion=="1")
                    $cadena .= ($rsUR->fields["USUA_ABR_TITULO"]." ".$rsUR->fields["USUA_NOMBRE"]." ".$rsUR->fields["USUA_APELLIDO"]).
                               "<br/><b>".($rsUR->fields["USUA_CARGO"])."</b><br/>";
                if($tipo_impresion=="2")
                    $cadena .= "<b>".($rsUR->fields["USUA_CARGO"])."</b><br/>";
            }
            if($tipo==1)
            {
                $vector["nombre"] =$rsUR->fields["USUA_NOMBRE"]." ".$rsUR->fields["USUA_APELLIDO"];
                $vector["institucion"] =$rsUR->fields["USUA_INSTITUCION"];
                $vector["cargo"] = ($rsUR->fields["USUA_CARGO"]);
                $vector["abr_titulo"] =$rsUR->fields["USUA_ABR_TITULO"];
                $vector["ciudad"] =$rsUR->fields["USUA_CIUDAD"];
                $vector["usua_firma_path"] =$rsUR->fields["USUA_FIRMA_PATH"];
                $sql = "select coalesce(depe_plantilla,depe_codi) as \"plantilla\" from dependencia where depe_codi=".$rsUR->fields["USUA_AREA_CODI"];
                $rs=$db->conn->query($sql);
                $vector["plantilla"] = $rs->fields["PLANTILLA"];
            }
            $rsUR->MoveNext();
            //if ($tipo!=6 || $tipo!=1)
            //$cadena.="<br>&nbsp;<br>";
        }
    }
    if($tipo==1)
        return $vector;
    else
    	return $cadena;
}

/**
* Función para cambio de texto de minusculas a mayusculas y reemplazo de vocales con tílde por vocales sin tílde.
* @param $cadena cadena testo a ser transformada.
**/
function strtoupper2($cadena) {
    $cadena = strtoupper($cadena);
    $ori  = array ("á", "é", "í", "ó", "ú", "à", "è", "ì", "ò", "ù", "ä", "ë", "ï", "ö", "ü", "ñ", "&AMP;", "&QUOT;");
    $dest = array ("Á", "É", "Í", "Ó", "Ú", "Á", "É", "Í", "Ó", "Ú", "Ä", "Ë", "Ï", "Ö", "Ü", "Ñ", "&", '"');
    $cadena = str_replace($ori, $dest, $cadena);
    $cadena = htmlentities($cadena, ENT_COMPAT, 'UTF-8');
    $ori  = array ("&AMP;", "&QUOT;");
    $dest = array ("&", '"');
    $cadena = str_replace($ori, $dest, $cadena);
    return $cadena;
}

function strtoulcase($cadena) {
    $cadena = ucwords($cadena);
    $ori  = array(" De ", " Del ", " La ", " Las ", " Lo ", " Los ", " En ", " Y ", " E ", "Ff.Aa.");
    $dest = array(" de ", " del ", " la ", " las ", " lo ", " los ", " en ", " y ", " e ", "FF.AA.");
    $cadena = str_replace($ori, $dest, $cadena);
    $cadena = htmlentities($cadena, ENT_COMPAT, 'UTF-8');
    $ori  = array ("&amp;", "&quot;");
    $dest = array ("&", '"');
    $cadena = str_replace($ori, $dest, $cadena);
    $ori  = array ("&Amp;", "&Quot;");
    $dest = array ("&amp;", "&quot;");
    $cadena = str_replace($ori, $dest, $cadena);
    return $cadena;
}
function mostrar_tipo_referencia($db,$referencia){
    $sql = "select trad_descr from radicado r 
    left outer join tiporad td on r.radi_tipo = td.trad_codigo
    where radi_nume_text = '".$referencia."'";    
    $rs=$db->conn->query($sql);
    while(!$rs->EOF)
    {
      $referencia_tipo=$rs->fields["TRAD_DESCR"];
      $rs->MoveNext();
    }
    return $referencia_tipo;
}
?>
