<?
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
*	Santiago Cordovilla     SC			14-01-2009
*
*	Comentado por		Iniciales		Fecha (dd/mm/aaaa)
*	Sylvia Velasco		SV			14-01-2009
**/

/** 
* Clase que maneja el Registro de los documentos
**/

class Radicacion
{

    /**
    * VARIABLES DE DATOS PARA LOS RADICADOS (Todas son opcionales)
    **/

    var $db;		//Conexion con la BDD
    var $db2;
    var $transaccion;	//Define si el commit o rollback de la transaccion se lo manejara localmente o desde la función que instancia la clase
    var $flagRadiTexto;	//Bandera: O si el documento es borrador, 1 si es un documento final
    var $radiNumeText;	//Numero del documento
    var $radiTextTemp;	//Numero del documentomientras es temporal
    var $radiNumeTemp;	//Numero del radicado temporal del cual se genera el nuevo documento
    var $radiCuentai;	//No de referencia
    var $radiFechOfic;	//Fecha de salida del documento
    var $radiFechFirma;	//Fecha de firma digital
    var $radiNumeDeri;	//Radicado padre (responder documento)
    var $radiNumeAsoc;	//Radicado padre (documentos asociados)
    var $radiExtArch;	//extension del archivo en caso de q este llegue por Web Services
    var $radiPath;		//direccion del archivo
    var $radiAsunto;
    var $radiResumen;       //Campo en donde se guardan las notas añadidas al documento
    var $radiUsuaRadi;
    var $radiUsuaActu;
    var $radiInstActu;
    var $radiUsuaAnte;
    var $radiUsuaDest;
    var $radiUsuaFirma;
    var $radiUsuaRem;
    var $radiDescAnex;
    var $radiTexto;		//Id del texto en la tabla radi_texto, en el caso de q este se haya registrado anteriormente
    var $radiEstado;
    var $radiCCA;
    var $radiTipo;		//Tipo de documento (Oficio, Memo, etc.)
    var $textTexto;		//Texto del documento
    var $radiFlagImprimir;	//Indica si el documento deberá ser impreso en el archivo (1=Si - 0=No)
    var $radiSeguridad;	//Indica si el documento es público o reservado
    var $usar_plantilla;	//Indica si el documento es público o reservado
    var $ajust_texto;	//Ajustar el texto en la vista previa aumenta o comprime texto
    var $radi_tipo_impresion; //Tipo de impresion para destinatarios cuando el destino es una lista
    var $radi_lista_dest;   //Codigo de la lista destino
    var $cod_codi;  //Tipificacion del documento por institucion
    var $cat_codi; //Categoría del documento
    var $ocultar_recorrido; //Oculta el recorrido del documento luego de la firma
    var $usua_redirigido; //Redirecciona el documento directamente a otro usuario que no sea el destinatario
    var $radi_imagen; //Redirecciona el documento directamente a otro usuario que no sea el destinatario

    /**
    * Constructor de la clase Radicación
    * @param $db Conexion con la BDD.
    **/
    function Radicacion($db)
    {
        $this->db=$db;
        $this->db2=$db;
        $this->transaccion=0;
    }


    /**
    * FUNCION QUE INSERTA UN RADICADO NUEVO
    * @param $tpRad Tipo de radicado o documento
    * @param $Dependencia Area
    * @param &$noRadText Numero del radicado texto en base de datos radi_nume_text
    **/
    function newRadicado($tpRad, $Dependencia, &$noRadText)
    {
        if ($this->transaccion==0) $this->db->conn->BeginTrans();
        $secNew=$this->db->nextId("sec_radi_nume_radi");
        if($secNew==0) {
            die("<hr><b><font color=red><center>Lo sentimos, existieron errores al generar la secuencia.</center></font></b><hr>");
        }

        //Generamos el numero del radicado
        $newRadicado = date("Y") . str_pad($Dependencia,6,"0", STR_PAD_LEFT) . str_pad($secNew,9,"0", STR_PAD_LEFT) . $tpRad;
        if(!$this->radiNumeTemp) $this->radiNumeTemp = $newRadicado;
        $formatoRadicado = $this->radiNumeTemp;
        if ($this->flagRadiTexto==2) {//Si es un documento importado al sistema y que debe enviarse a varias instituciones
            $formatoRadicado = $newRadicado;
            $this->flagRadiTexto=1;
        }

        if ($this->flagRadiTexto==1 and trim($this->radiNumeText)=="") {
            $noRadText = $this->GenerarTextRadicado($formatoRadicado, $this->radiTipo, "N");
        } else {
            if (trim($this->radiNumeText)=="")
                $noRadText = $this->GenerarTextRadicado($formatoRadicado, $this->radiTipo, "T");
            else
                $noRadText = trim($this->radiNumeText);
        }
        //echo die("<hr><b><font color=red><center>NUMRAD $newRadicado <br>TEXTRAD $noRadText</center></font></b><hr>");
        if ($this->flagRadiTexto==1 and trim($this->radiNumeText)!="") $this->radiTextTemp = $this->radiNumeText; //Si es un documento hijo externo
        if(!$this->radiTextTemp) $this->radiTextTemp = $noRadText;
        if(!$this->radiNumeText) $this->radiNumeText = $noRadText;
        if(!$this->radiNumeDeri) $this->radiNumeDeri = "null";
        if(!$this->radiNumeAsoc) $this->radiNumeAsoc = $this->radiNumeDeri;
        if(!$this->radiSeguridad) $this->radiSeguridad = "0";

        if($this->radiExtArch) {
            $this->radiPath = "/".substr($this->radiNumeTemp,0,4)."/".substr($this->radiNumeTemp,4,6)."/".$this->radiNumeTemp.".".$this->radiExtArch;
            if (substr($this->radiExtArch,-3)=="p7m" and !$this->radiFechFirma)
                $this->radiFechFirma = $this->db->conn->sysTimeStamp;
        }
        if(!$this->radiTexto) 	 $this->radiTexto = "0";
        if(!$this->radiFlagImprimir) $this->radiFlagImprimir = "0";
        if(!$this->radiFechOfic) $this->radiFechOfic = date("Y-m-d H:i:s");

        $recordR["RADI_NUME_RADI"]	= $newRadicado;
        $recordR["RADI_NUME_TEMP"]	= $this->radiNumeTemp;
        $recordR["RADI_NUME_TEXT"] 	= $this->db->conn->qstr($this->radiNumeText);
        $recordR["RADI_TEXT_TEMP"] 	= $this->db->conn->qstr($this->radiTextTemp);
        $recordR["RADI_FECH_RADI"]	= $this->db->conn->sysTimeStamp;
        $recordR["RADI_FECH_OFIC"]	= $this->db->conn->DBDate($this->radiFechOfic);
        if($this->radiFechFirma) 	$recordR["RADI_FECH_FIRMA"] = $this->radiFechFirma;
        $recordR["RADI_NUME_DERI"]	= $this->radiNumeDeri;
        $recordR["RADI_NUME_ASOC"]	= $this->radiNumeAsoc;
        if($this->radiPath) 	$recordR["RADI_PATH"] = $this->db->conn->qstr($this->radiPath);
        $recordR["ESTA_CODI"] 		= $this->radiEstado;
        $recordR["RADI_USUA_RADI"]	= $this->radiUsuaRadi;
        $recordR["RADI_USUA_ACTU"]	= $this->radiUsuaActu;
        $recordR["RADI_INST_ACTU"]	= $this->radiInstActu;
        $recordR["RADI_USUA_ANTE"]	= $this->radiUsuaAnte;
        if($this->radiUsuaFirma) 	$recordR["RADI_NOMB_USUA_FIRMA"] = $this->db->conn->qstr($this->radiUsuaFirma);
        $recordR["RADI_TIPO"]	= $this->radiTipo;
        $recordR["RADI_FLAG_IMPR"]	= $this->radiFlagImprimir;
        $recordR["RADI_CUENTAI"] 	= $this->db->conn->qstr(substr($this->radiCuentai,0,50));
        $recordR["RADI_USUA_REM"] 	= $this->db->conn->qstr(str_replace("---","-",$this->radiUsuaRem));
        $recordR["RADI_USUA_DEST"] 	= $this->db->conn->qstr(str_replace("---","-",$this->radiUsuaDest));
        $recordR["RADI_ASUNTO"]	= $this->db->conn->qstr(substr($this->radiAsunto,0,350));
        //Para guardar las notas añadidas en el docuemento heredado
        $recordR["RADI_RESUMEN"]	= $this->db->conn->qstr(substr($this->radiResumen,0,1000));
        $recordR["RADI_PERMISO"]	= $this->radiSeguridad;
        if (!$this->ocultar_recorrido) $this->ocultar_recorrido="0";
        $recordR["RADI_OCULTAR_RECORRIDO"]	= $this->ocultar_recorrido;
        if (!$this->usua_redirigido) $this->usua_redirigido="0";
        $recordR["RADI_USUA_REDIRIGIDO"]	= $this->usua_redirigido;
        if (!$this->usar_plantilla) $this->usar_plantilla = 1;
        $recordR["USAR_PLANTILLA"]	= $this->usar_plantilla;
        $recordR["AJUST_TEXTO"]	= $this->ajust_texto;
        if (trim($this->radi_tipo_impresion) != "999")
            $recordR["RADI_TIPO_IMPRESION"]	= $this->db->conn->qstr($this->radi_tipo_impresion);
        else
            $recordR["RADI_TIPO_IMPRESION"]	= $this->db->conn->qstr("1");
        $recordR["RADI_LISTA_DEST"]	= $this->db->conn->qstr($this->radi_lista_dest);
        if($this->radiCCA)		$recordR["RADI_CCA"] = $this->db->conn->qstr(str_replace("---","-",$this->radiCCA));
        $recordR["RADI_DESC_ANEX"]	= $this->db->conn->qstr(substr($this->radiDescAnex,0,100));
        $recordR["COD_CODI"] = 0 + $this->cod_codi;
        $recordR["CAT_CODI"] = 0 + $this->cat_codi;
        if ($this->radiTexto!="0") 	$recordR["RADI_TEXTO"] = $this->radiTexto;
        if(trim($this->radi_imagen)!="") $recordR["RADI_IMAGEN"] = $this->db->conn->qstr($this->radi_imagen);

        $insertSQL = $this->db->conn->Replace("RADICADO", $recordR, "", false,false, true, false);
        if($insertSQL) {
            if ($this->radiTexto=="0") $insertSQL=$this->updateTextoRadicado($newRadicado);
        }
        if(!$insertSQL) {
            echo "<hr><b><font color=red>Error no se inserto el registro<br>SQL: ".$this->db->conn->querySql."</font></b><hr>";
            $newRadicado = null;
            if ($this->transaccion==0) $this->db->conn->RollbackTrans();
                else return 0;
        } else {
            if ($this->transaccion==0) $this->db->conn->CommitTrans();
        }
        //die("");
        return $newRadicado;
    }

    /**
    * FUNCION QUE ACTUALIZA UN RADICADO
    * @param $radicado Numero de radicado en base de datos radi_nume_radi
    * @param $radPathUpdate
    **/
    function updateRadicado($radicado, $radPathUpdate = null)
    {
        //echo "<br> Q tiene:".$this->radiTipo;
        $recordR["RADI_FECH_OFIC"]	= $this->db->conn->DBDate($this->radiFechOfic);
        $recordR["RADI_NUME_RADI"] 	= $radicado;
        $recordR["RADI_TIPO"]		= $this->radiTipo;
        $recordR["RADI_CUENTAI"] 	= $this->db->conn->qstr(substr($this->radiCuentai,0,50));
        $recordR["RADI_USUA_REM"] 	= $this->db->conn->qstr(str_replace("---","-",$this->radiUsuaRem));
        $recordR["RADI_USUA_DEST"] 	= $this->db->conn->qstr(str_replace("---","-",$this->radiUsuaDest));
        $recordR["RADI_ASUNTO"]		= $this->db->conn->qstr(substr($this->radiAsunto,0,350));
        //$recordR["RADI_RESUMEN"] 	= $this->db->conn->qstr($this->radiResumen);
        $recordR["RADI_CCA"] 		= $this->db->conn->qstr(str_replace("---","-",$this->radiCCA));
        $recordR["RADI_DESC_ANEX"]	= $this->db->conn->qstr(substr($this->radiDescAnex,0,100));
        $recordR["USAR_PLANTILLA"]	= $this->usar_plantilla;
        if (!$this->ocultar_recorrido) $this->ocultar_recorrido="0";
        $recordR["RADI_OCULTAR_RECORRIDO"]	= $this->ocultar_recorrido;
        if (!$this->usua_redirigido) $this->usua_redirigido="0";
        $recordR["RADI_USUA_REDIRIGIDO"]	= $this->usua_redirigido;
        $recordR["AJUST_TEXTO"]	= $this->ajust_texto;
        if (trim($this->radi_tipo_impresion) != "999")
            $recordR["RADI_TIPO_IMPRESION"]	= $this->db->conn->qstr($this->radi_tipo_impresion);
        $recordR["COD_CODI"] = 0 + $this->cod_codi;
        $recordR["CAT_CODI"] = 0 + $this->cat_codi;
        $recordR["RADI_LISTA_DEST"]	= $this->db->conn->qstr($this->radi_lista_dest);
        if ($this->radiTexto!=0) $recordR["RADI_TEXTO"]	= $this->radiTexto;

        /** Linea para realizar radicacion Web de archivos pdf **/
        if(!empty($radPathUpdate) && $radPathUpdate != ""){
            $archivoPath = explode(".", $radPathUpdate);
            /** Sacando la extension del archivo **/
            $extension = array_pop($archivoPath);
            if($extension == "pdf") $recordR["radi_path"] = "'" . $radPathUpdate . "'";
        }
        $insertSQL = $this->db->conn->Replace("RADICADO", $recordR, "RADI_NUME_RADI", false,false, true,false);/** true al final para ver la cadena del insert **/
        if ($this->radiTexto==0) $this->updateTextoRadicado($radicado);
        return $insertSQL;
    }

    /**
    * FUNCION QUE ACTUALIZA EL TEXTO DE UN RADICADO
    * Actualizacion en la tabla radi_text
    * @param $radicado recibe el numero de radicado en la base radi_nume_radi
    **/
    function updateTextoRadicado($radicado)
    {
        //Inserta el texto en un documento
        $insertSQL = 1; // Variable de validacion
        //Verificamos si el texto fue modificado antes de crear un nuevo registro
        $sql = "select text_codi, text_texto from radi_texto where radi_nume_radi=$radicado";
        $rs = $this->db->conn->query($sql);
        $secNew = 0;
        while (!$rs->EOF and $secNew==0) {
            if (trim($rs->fields["TEXT_TEXTO"])==trim($this->textTexto))
            $secNew = $rs->fields["TEXT_CODI"];
            $rs->MoveNext();
        }
        // Insertamos el texto en la BDD
        if ($secNew == 0) {
            $secNew = $this->db->nextId("sec_radi_texto");
            $recordT["TEXT_CODI"]	= $secNew;
            $recordT["RADI_NUME_RADI"] 	= $radicado;
            $recordT["TEXT_FECHA"] 	= $this->db->conn->sysTimeStamp;
            $recordT["TEXT_TEXTO"] 	= $this->db->conn->qstr($this->textTexto);
            $insertSQL = $this->db->conn->Replace("RADI_TEXTO", $recordT, "TEXT_CODI", false,false, true,false);
            if (!$insertSQL) $secNew = 0;
        }

        // Cambiamos la referencia en Radicado
        if($insertSQL) {
            $recordR["RADI_NUME_RADI"] 	= $radicado;
            $recordR["RADI_TEXTO"]	= $secNew;
            $insertSQL = $this->db->conn->Replace("RADICADO", $recordR, "RADI_NUME_RADI", false,false, false,false);/** true al final para ver la cadena del update **/
        }
        return $secNew;
    }

    /**
    * FUNCION QUE GENRA EL TEXTO DEL RADICADO SEGUN EL FORMATO QUE SE DIO EN LA NUMERACION DE LOS DOCUMENTOS
    * @param $numrad numero de radicado radi_nume_radi
    * @param $radi_tipo tipo de radicado o documento
    * @param $TipRad si el documento es temporal
    **/
    function GenerarTextRadicado($numrad, $radi_tipo, $TipRad="T")
    {
        //$anio = substr($numrad,0,4);
        $anio = date("Y");
        //$depe = substr($numrad,4,6);
        if (isset($_SESSION["depe_codi"]))
            $depe = $_SESSION["depe_codi"];
        else
            $depe = 0+substr($numrad,4,6);

        $secu = 0+substr($numrad,10,9);
        //$tipo = substr($numrad,19,1);
        $form = "inst-dep-anio-secuencial-tipodoc";

        if ($TipRad=="N") {	/** Si es el número real del radicado **/
            /** Verificamos si la numeración pertenece a otra area **/
            $sql = "select depe_numeracion from formato_numeracion where fn_tiporad=$radi_tipo and depe_codi= $depe";
            $rs = $this->db2->conn->query($sql);
            if (trim($rs->fields['DEPE_NUMERACION'])!="")
                $depe = $rs->fields['DEPE_NUMERACION'];

            /* Obtenemos los datos para armar el codigo del documento*/
            $sql = "select fn_abr_texto, fn_formato, fn_caracter, fn_num_consec, fn_num_anio, fn_contador from formato_numeracion where fn_tiporad=$radi_tipo and depe_codi=$depe for update";
            $rs = $this->db2->conn->query($sql);
            if ($rs->EOF) { /*Si no estaba definido un formato ponemos uno por defecto */
                $rs_tr = $this->db2->conn->Execute("select trad_abreviatura from tiporad where trad_codigo=$radi_tipo");
                $tipodoc = trim($rs_tr->fields["TRAD_ABREVIATURA"]);
                $sql = "insert into formato_numeracion (fn_abr_texto, fn_formato, depe_codi, depe_numeracion, fn_tiporad)
                            values ('$tipodoc', 'inst-dep-anio-secuencial-tipodoc', $depe, $depe, $radi_tipo)";
                $rs = $this->db2->conn->Execute($sql);
                $sql = "select fn_abr_texto, fn_formato, fn_caracter, fn_num_consec, fn_num_anio, fn_contador from formato_numeracion
                            where fn_tiporad=$radi_tipo and depe_codi=$depe for update";
                $rs = $this->db2->conn->query($sql);
            }
            $sql_update = "";
            $form = $rs->fields["FN_FORMATO"];
            $tipodoc = $rs->fields["FN_ABR_TEXTO"];
            if (trim($form)=="") { /*Si no estaba definido un formato ponemos uno por defecto */
                $form = "inst-dep-anio-secuencial-tipodoc";
                $sql_update .= ", fn_formato='$form' ";
                if (trim($tipodoc)=="") {
                    $rs_tr = $this->db2->conn->Execute("select trad_abreviatura from tiporad where trad_codigo=$radi_tipo");
                    $tipodoc = trim($rs_tr->fields["TRAD_ABREVIATURA"]);
                    $sql_update .= ", fn_abr_texto='$tipodoc' ";
                }
            }
            $separador = $rs->fields["FN_CARACTER"];
            $num_consec = $rs->fields["FN_NUM_CONSEC"];
            $num_anio = $rs->fields["FN_NUM_ANIO"];
            $secu = 1 + $rs->fields['FN_CONTADOR'];
            $sql = "UPDATE formato_numeracion SET fn_contador=$secu $sql_update WHERE fn_tiporad=$radi_tipo and depe_codi=$depe";
            $this->db2->conn->Execute($sql); /** Ejecuta la busqueda **/
        } else {	/** Si es una mascara temporal **/
            $sql = "select secuencia from radicado_sec_temp where depe_codi=$depe for update";
            $rs = $this->db2->conn->query($sql);
            if ($rs->EOF) {
                $this->db2->conn->query("insert into radicado_sec_temp (depe_codi,secuencia) values ($depe,1)");
                $secu = 1;
            } else {
                $secu = 1 + $rs->fields['SECUENCIA'];
                $this->db2->conn->query("update radicado_sec_temp set secuencia=$secu where depe_codi=$depe");
            }

            $separador = "-";
            $num_consec = 1;
            $num_anio = 4;
            $tipodoc = "TEMP";
        }

        $sql = "select a.dep_sigla, b.inst_sigla from dependencia a left outer join institucion b on a.inst_adscrita=b.inst_codi where a.depe_codi=$depe";
        $rs = $this->db2->conn->query($sql); # Ejecuta la busqueda
        $inst = $rs->fields["INST_SIGLA"];
        $dep = $rs->fields["DEP_SIGLA"];
        $texto = "";

        if (trim($form)=="") {
            $texto=$numrad;
        } else {
            $secuencial = str_pad($secu,$num_consec,"0", STR_PAD_LEFT);
            $anio = substr($anio,-$num_anio);
            $formato=explode("-",$form);

            for($i=0;$i<=count($formato)-1;$i++) {
                if (trim($formato[$i])!="") {
                    if (trim($texto)=="")
                        $texto = ${$formato[$i]};
                    else {
                        if (trim(${$formato[$i]})!="")
                            $texto .= $separador.${$formato[$i]};
                    }
                }
            }
        }
        return strtoupper($texto);
    }
} /** Fin de Class Radicacion **/
?>